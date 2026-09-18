<?php

namespace RTMKit\Modules\Telemetry;

if (! defined('ABSPATH')) exit;

/**
 * Laporan widget RTMKit yang dipakai di halaman terbit situs.
 *
 * Kapan dikirim:
 *  - 15 menit setelah halaman terbit berubah. Laporan yang isinya sama dengan
 *    laporan terakhir tidak dikirim.
 *  - Seminggu sekali apa pun yang terjadi, sebagai tanda situs masih aktif.
 *  - Sebentar setelah izin diberikan.
 *
 * Hitungan widget disimpan per post (META_COUNTS) dan dibuang setiap kali data
 * Elementor post itu berubah. Laporan tinggal menjumlahkan hitungan tersimpan;
 * yang dibaca dari `_elementor_data` — bisa ratusan kilobyte per halaman — hanya
 * post yang berubah sejak laporan sebelumnya. Itu pun bertahap, sejumlah kecil
 * post per putaran, supaya situs besar tidak kehabisan memori.
 */
class WidgetReporter
{
    /** Hitungan widget per post: ['v' => versi RTMKit, 'c' => [nama widget => jumlah]]. */
    public const META_COUNTS = '_rtmkit_widget_counts';

    /** Baris post per query saat menjumlahkan hitungan tersimpan. */
    private const ROWS_PER_QUERY = 500;

    /** Post yang dihitung ulang dari `_elementor_data` per putaran. */
    private const DECODES_PER_CHUNK = 50;

    /** Query per putaran, supaya satu request cron tidak berjalan terlalu lama. */
    private const QUERIES_PER_CHUNK = 10;

    /** Jeda antar putaran, supaya cron situs kecil tidak dipenuhi tugas ini. */
    private const CHUNK_DELAY = 2 * MINUTE_IN_SECONDS;

    /** Batas widget per laporan, sama dengan batas hub. */
    private const MAX_WIDGETS = 500;

    /**
     * Laporan yang tertahan (hub minta jeda) lebih lama dari ini dibuang; laporan
     * berikutnya membawa data yang lebih baru. Juga batas umur laporan terakhir
     * yang boleh dianggap "sama" sehingga laporan perubahan dilewati.
     */
    private const MAX_REPORT_AGE = WEEK_IN_SECONDS;

    private static $instance;

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /* ── Mulai laporan ───────────────────────────────────────────────── */

    /**
     * Laporan mingguan, atau laporan pertama setelah izin: selalu dikirim.
     */
    public function start(): void
    {
        $this->begin(false);
    }

    /**
     * Laporan setelah halaman berubah: tidak dikirim kalau isinya sama.
     */
    public function start_after_change(): void
    {
        $this->begin(true);
    }

    private function begin(bool $skip_unchanged): void
    {
        if (! $this->should_report()) {
            return;
        }

        // Pemindaian yang sedang berjalan atau laporan yang masih tertahan
        // diganti dengan data terbaru, tapi laporan wajib tetap wajib.
        $current = $this->scan();

        if (! empty($current) && empty($current['skip_unchanged'])) {
            $skip_unchanged = false;
        }

        update_option(
            TelemetryState::OPTION_SCAN,
            [
                'cursor'         => 0,
                'counts'         => [],
                'started'        => time(),
                'skip_unchanged' => $skip_unchanged,
            ],
            false
        );

        $this->run_chunk();
    }

    /* ── Perubahan halaman ───────────────────────────────────────────── */

    /**
     * Data Elementor sebuah post ditulis, diubah, atau dihapus.
     *
     * @param int|int[] $meta_id
     * @param int       $object_id
     * @param string    $meta_key
     */
    public function on_meta_changed($meta_id, $object_id, $meta_key): void
    {
        if ('_elementor_data' !== $meta_key) {
            return;
        }

        $post_id = (int) $object_id;

        // Revisi dan autosave menyimpan salinan data Elementor; yang dihitung
        // hanya post aslinya.
        if ($post_id < 1 || wp_is_post_revision($post_id)) {
            return;
        }

        // Hitungan lama tidak berlaku lagi, ada izin atau tidak: kalau izin
        // diberikan nanti, post ini dihitung ulang dari data terbarunya.
        delete_post_meta($post_id, self::META_COUNTS);

        if ('publish' === get_post_status($post_id) && $this->should_report()) {
            TelemetryModule::schedule_widget_change_report();
        }
    }

    /**
     * Post Elementor terbit, batal terbit, atau dibuang ke trash.
     *
     * @param string   $new_status
     * @param string   $old_status
     * @param \WP_Post $post
     */
    public function on_status_changed($new_status, $old_status, $post): void
    {
        if (('publish' === $new_status) === ('publish' === $old_status)) {
            return;
        }

        if (! $post instanceof \WP_Post || wp_is_post_revision($post) || ! $this->should_report()) {
            return;
        }

        global $wpdb;

        // Cek langsung satu baris; get_post_meta akan memuat seluruh meta post,
        // termasuk `_elementor_data` yang besar.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $uses_elementor = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT 1 FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = '_elementor_data' LIMIT 1",
                $post->ID
            )
        );

        if ($uses_elementor) {
            TelemetryModule::schedule_widget_change_report();
        }
    }

    /* ── Pemindaian ──────────────────────────────────────────────────── */

    /**
     * Satu putaran pemindaian. Menjadwalkan dirinya lagi kalau masih ada post.
     */
    public function run_chunk(): void
    {
        $scan = $this->scan();

        if (empty($scan) || isset($scan['payload'])) {
            return;
        }

        if (! $this->should_report()) {
            $this->reset();

            return;
        }

        global $wpdb;

        $counts  = is_array($scan['counts']) ? $scan['counts'] : [];
        $decoded = 0;

        for ($query = 0; $query < self::QUERIES_PER_CHUNK; ++$query) {
            // Yang diambil hanya hitungan tersimpan, bukan `_elementor_data`.
            // WP_Query akan memuat seluruh objek post dan seluruh meta-nya.
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT m.post_id, c.meta_value AS counts
                       FROM {$wpdb->postmeta} m
                 INNER JOIN {$wpdb->posts} p ON p.ID = m.post_id
                  LEFT JOIN {$wpdb->postmeta} c ON c.post_id = m.post_id AND c.meta_key = %s
                      WHERE m.meta_key = '_elementor_data'
                        AND p.post_status = 'publish'
                        AND m.post_id > %d
                   ORDER BY m.post_id ASC
                      LIMIT %d",
                    self::META_COUNTS,
                    (int) $scan['cursor'],
                    self::ROWS_PER_QUERY
                )
            );

            foreach ($rows as $row) {
                $post_id = (int) $row->post_id;

                // Meta ganda untuk post yang sama menghasilkan baris ganda.
                if ($post_id <= (int) $scan['cursor']) {
                    continue;
                }

                $post_counts = $this->cached_counts($row->counts);

                if (null === $post_counts) {
                    if ($decoded >= self::DECODES_PER_CHUNK) {
                        $this->continue_later($scan, $counts);

                        return;
                    }

                    $post_counts = $this->count_post($post_id);
                    ++$decoded;
                }

                $scan['cursor'] = $post_id;
                $this->add_counts($counts, $post_counts);
            }

            if (count($rows) < self::ROWS_PER_QUERY) {
                $scan['counts'] = $counts;
                $this->finish($scan);

                return;
            }
        }

        $this->continue_later($scan, $counts);
    }

    /**
     * @param array<string, mixed>                             $scan
     * @param array<string, array{instances: int, pages: int}> $counts
     */
    private function continue_later(array $scan, array $counts): void
    {
        $scan['counts'] = $counts;

        update_option(TelemetryState::OPTION_SCAN, $scan, false);

        wp_schedule_single_event(time() + self::CHUNK_DELAY, TelemetryModule::CRON_WIDGET_SCAN);
    }

    /**
     * Hitungan tersimpan satu post, atau null kalau belum ada / dibuat versi
     * RTMKit lain (daftar widget yang dihitung bisa berubah antar versi).
     *
     * @param string|null $raw
     *
     * @return array<string, int>|null
     */
    private function cached_counts($raw): ?array
    {
        if (null === $raw || '' === $raw) {
            return null;
        }

        $value = maybe_unserialize($raw);

        if (! is_array($value) || ! isset($value['v'], $value['c']) || RTM_KIT_VERSION !== $value['v'] || ! is_array($value['c'])) {
            return null;
        }

        return $value['c'];
    }

    /**
     * Hitung widget RTMKit di satu post dari `_elementor_data`, lalu simpan.
     *
     * @return array<string, int>
     */
    private function count_post(int $post_id): array
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $raw = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = '_elementor_data' LIMIT 1",
                $post_id
            )
        );

        $counts   = [];
        $elements = $this->decode_elements($raw);

        if (! empty($elements)) {
            $this->collect($elements, $counts);
        }

        update_post_meta($post_id, self::META_COUNTS, ['v' => RTM_KIT_VERSION, 'c' => $counts]);

        return $counts;
    }

    /**
     * @param array<string, array{instances: int, pages: int}> $counts
     * @param array<string, int>                               $post_counts
     */
    private function add_counts(array &$counts, array $post_counts): void
    {
        foreach ($post_counts as $name => $instances) {
            $instances = (int) $instances;

            if ($instances < 1) {
                continue;
            }

            if (! isset($counts[$name])) {
                $counts[$name] = ['instances' => 0, 'pages' => 0];
            }

            $counts[$name]['instances'] += $instances;
            ++$counts[$name]['pages'];
        }
    }

    /**
     * Pemindaian selesai: susun payload lalu kirim, kecuali isinya sama dengan
     * laporan terakhir yang diterima hub.
     *
     * @param array<string, mixed> $scan
     */
    private function finish(array $scan): void
    {
        $state   = TelemetryState::instance();
        $widgets = $this->build_widgets(is_array($scan['counts']) ? $scan['counts'] : []);

        // Mode kirim ikut di-hash: situs yang baru connect wajib melapor lewat
        // jalur connect walaupun widgetnya tidak berubah.
        $hash    = md5(($state->is_connected() ? 'connected' : 'anonymous') . wp_json_encode($widgets));
        $runtime = $state->runtime();

        if (
            ! empty($scan['skip_unchanged'])
            && isset($runtime['widgets_hash'])
            && hash_equals((string) $runtime['widgets_hash'], $hash)
            && $state->last_reported_at() > time() - self::MAX_REPORT_AGE
        ) {
            $this->reset();

            return;
        }

        $scan['payload'] = $widgets;
        $scan['hash']    = $hash;
        unset($scan['counts']);

        update_option(TelemetryState::OPTION_SCAN, $scan, false);

        $this->send_ready();
    }

    /* ── Pengiriman ──────────────────────────────────────────────────── */

    /**
     * Kirim laporan yang sudah siap. Dipanggil juga oleh cron kirim ulang.
     */
    public function send_ready(): void
    {
        $scan = $this->scan();

        if (empty($scan) || ! isset($scan['payload']) || ! is_array($scan['payload'])) {
            return;
        }

        if (! $this->should_report() || (int) ($scan['started'] ?? 0) < time() - self::MAX_REPORT_AGE) {
            $this->reset();

            return;
        }

        $state = TelemetryState::instance();

        // Hub minta jeda: laporan tetap tersimpan, kirim setelah jeda lewat.
        if ($state->is_paused()) {
            $this->schedule_send();

            return;
        }

        $result = $state->is_connected()
            ? $this->send_connected($scan['payload'])
            : $this->send_anonymous($scan['payload']);

        switch ($result['result']) {
            case TelemetryClient::RESULT_OK:
                $state->update_runtime([
                    'widgets_reported_at' => time(),
                    'widgets_hash'        => isset($scan['hash']) ? (string) $scan['hash'] : '',
                    'widgets_last_error'  => '',
                ]);
                $this->reset();
                break;

            case TelemetryClient::RESULT_RETRY:
                // Client sudah memasang jeda; laporannya menunggu, tidak dibuang.
                $state->update_runtime(['widgets_last_error' => $result['code'] . ' ' . $result['message']]);
                $this->schedule_send();
                break;

            case TelemetryClient::RESULT_UNKNOWN_SITE:
                if ($state->is_connected()) {
                    $state->block('widgets:404');
                }

                $state->update_runtime(['widgets_last_error' => $result['code'] . ' ' . $result['message']]);
                $this->reset();
                break;

            default:
                $state->update_runtime(['widgets_last_error' => $result['code'] . ' ' . $result['message']]);
                $this->reset();
                break;
        }
    }

    private function schedule_send(): void
    {
        if (wp_next_scheduled(TelemetryModule::CRON_WIDGET_SEND)) {
            return;
        }

        wp_schedule_single_event(
            max(time() + MINUTE_IN_SECONDS, TelemetryState::instance()->paused_until()),
            TelemetryModule::CRON_WIDGET_SEND
        );
    }

    /**
     * @param array<int, array<string, mixed>> $widgets
     *
     * @return array{result: string, code: int, message: string, data: array}
     */
    private function send_connected(array $widgets): array
    {
        $state = TelemetryState::instance();

        $body = array_merge(
            $state->site_context(),
            [
                'plugin_version' => RTM_KIT_VERSION,
                'widgets'        => $widgets,
            ]
        );

        // Situs ini pernah melapor anonim sebelum connect. Dengan menyebut
        // install_id lama, hub membuang laporan anonimnya supaya satu situs
        // tidak terhitung dua kali.
        $previous = $state->reported_install_id();

        if ('' !== $previous) {
            $body['install_id'] = $previous;
        }

        $result = TelemetryClient::instance()->send(TelemetryClient::ENDPOINT_WIDGETS, $body);

        if (TelemetryClient::RESULT_OK === $result['result'] && '' !== $previous) {
            $state->forget_reported_install_id();
        }

        return $result;
    }

    /**
     * @param array<int, array<string, mixed>> $widgets
     *
     * @return array{result: string, code: int, message: string, data: array}
     */
    private function send_anonymous(array $widgets): array
    {
        $state      = TelemetryState::instance();
        $install_id = $state->install_id();

        $body = [
            'install_id'        => $install_id,
            'plugin_version'    => RTM_KIT_VERSION,
            'wp_version'        => get_bloginfo('version'),
            'elementor_version' => defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : null,
            'pro_active'        => \RTMKit\Core\Plugin::instance()->pro_is_active(),
            'widgets'           => $widgets,
        ];

        $result = TelemetryClient::instance()->send(TelemetryClient::ENDPOINT_ANONYMOUS_WIDGET, $body, false);

        if (TelemetryClient::RESULT_OK === $result['result']) {
            $state->remember_reported_install_id($install_id);
        }

        return $result;
    }

    /**
     * Connect atau tidak, yang menentukan cuma izin pengguna.
     */
    public function should_report(): bool
    {
        return TelemetryState::instance()->can_report();
    }

    /**
     * @param array<string, array{instances: int, pages: int}> $counts
     *
     * @return array<int, array<string, mixed>>
     */
    private function build_widgets(array $counts): array
    {
        $map     = WidgetMap::instance();
        $widgets = [];

        foreach ($counts as $name => $count) {
            $instances = isset($count['instances']) ? (int) $count['instances'] : 0;

            if ($instances < 1) {
                continue;
            }

            // Hub menolak seluruh laporan kalau satu key saja tidak cocok pola.
            if (! preg_match('/^[A-Za-z0-9_.:\-]{1,120}$/', (string) $name)) {
                continue;
            }

            $described = $map->describe((string) $name);

            $widget = [
                'key'       => (string) $name,
                'instances' => $instances,
                'pages'     => isset($count['pages']) ? (int) $count['pages'] : 0,
            ];

            if ('' !== $described['label']) {
                $widget['label'] = $described['label'];
            }

            if ('' !== $described['type']) {
                $widget['type'] = $described['type'];
            }

            $widgets[] = $widget;
        }

        // Kalau situs memakai lebih banyak widget daripada yang muat dalam satu
        // laporan, yang paling sering dipakai yang dikirim. Urutan tetap untuk
        // jumlah yang sama, supaya hash laporan yang tidak berubah juga sama.
        usort($widgets, static function ($a, $b) {
            return [$b['instances'], $a['key']] <=> [$a['instances'], $b['key']];
        });

        return array_slice($widgets, 0, self::MAX_WIDGETS);
    }

    /**
     * Telusuri pohon elemen Elementor satu post dan hitung widget RTMKit.
     *
     * @param array<int, mixed>  $elements
     * @param array<string, int> $counts
     */
    private function collect(array $elements, array &$counts): void
    {
        foreach ($elements as $element) {
            if (! is_array($element)) {
                continue;
            }

            if (! empty($element['widgetType']) && is_string($element['widgetType'])) {
                $name = $element['widgetType'];

                if (WidgetMap::instance()->is_reportable($name)) {
                    $counts[$name] = (isset($counts[$name]) ? $counts[$name] : 0) + 1;
                }
            }

            if (! empty($element['elements']) && is_array($element['elements'])) {
                $this->collect($element['elements'], $counts);
            }
        }
    }

    /**
     * @return array<int, mixed>
     */
    private function decode_elements($raw): array
    {
        if (! is_string($raw) || '' === $raw) {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (! is_array($decoded)) {
            // Sebagian instalasi menyimpan nilai yang masih ter-escape.
            $decoded = json_decode(wp_unslash($raw), true);
        }

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function scan(): array
    {
        $scan = get_option(TelemetryState::OPTION_SCAN, []);

        return is_array($scan) && isset($scan['cursor']) ? $scan : [];
    }

    public function reset(): void
    {
        delete_option(TelemetryState::OPTION_SCAN);
    }
}
