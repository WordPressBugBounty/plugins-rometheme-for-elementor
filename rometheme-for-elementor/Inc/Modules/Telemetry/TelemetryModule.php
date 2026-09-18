<?php

namespace RTMKit\Modules\Telemetry;

if (! defined('ABSPATH')) exit;

/**
 * Laporan pemakaian RTMKit ke RTMHub (CRM → Analytics).
 *
 * Tidak ada satu pun laporan yang berangkat sebelum pemilik situs mencentang
 * izinnya — berlaku sama untuk pengguna gratis maupun yang akunnya terhubung ke
 * RTMHub. Mencabut izin tidak memutus koneksi akun: lisensi, addon, dan update
 * tetap jalan seperti biasa.
 *
 * Apa yang dikirim setelah diizinkan:
 *  - Situs yang connect: aktivasi, nonaktif, uninstall, install/uninstall
 *    addon RTM, unduhan template kit, dan laporan widget, bersama domain situs.
 *  - Situs yang belum connect: laporan widget saja, anonim — tanpa domain,
 *    tanpa IP, tanpa isi halaman.
 *
 * Kapan dikirim: beberapa menit setelah kejadiannya (event lain dalam jeda itu
 * ikut dalam kiriman yang sama), dan laporan widget minimal seminggu sekali
 * sebagai tanda situs masih aktif. Semua lewat WP-Cron, kecuali nonaktif dan
 * uninstall yang memang tidak punya kesempatan kedua. Kalau hub minta jeda
 * (429/5xx), data ditahan dan dikirim sekaligus setelah jeda lewat.
 */
class TelemetryModule
{
    /** Kirim antrean event: tiap jam, dan beberapa menit setelah event dicatat. */
    public const CRON_FLUSH = 'rtmkit_telemetry_flush';

    /** Mulai laporan widget, tiap minggu pada jam acak per situs. */
    public const CRON_WIDGET_REPORT = 'rtmkit_telemetry_widget_report';

    /** Laporan widget setelah halaman yang sudah terbit berubah. */
    public const CRON_WIDGET_CHANGED = 'rtmkit_telemetry_widget_changed';

    /** Putaran pemindaian berikutnya. */
    public const CRON_WIDGET_SCAN = 'rtmkit_telemetry_widget_scan';

    /** Percobaan kirim ulang laporan widget. */
    public const CRON_WIDGET_SEND = 'rtmkit_telemetry_widget_send';

    /** Sync sekali setelah update, untuk mengambil analytics_site_key. */
    public const CRON_BOOTSTRAP = 'rtmkit_telemetry_bootstrap';

    /** Timeout pengiriman biasa (cron). */
    private const TIMEOUT_BACKGROUND = 15;

    /** Timeout saat plugin dinonaktifkan: pengguna sedang menunggu halaman. */
    private const TIMEOUT_FOREGROUND = 5;

    /** Jeda sebelum antrean event dikirim; event lain dalam jeda ini ikut terkirim. */
    private const FLUSH_DELAY = 2 * MINUTE_IN_SECONDS;

    /** Kiriman (masing-masing ≤ 50 event) per putaran; sisanya di putaran berikutnya. */
    private const MAX_BATCHES = 4;

    /** Jeda laporan widget setelah halaman berubah; simpan lain dalam jeda ini ikut terhitung. */
    private const WIDGET_CHANGE_DELAY = 15 * MINUTE_IN_SECONDS;

    /**
     * Laporan widget pertama setelah izin diberikan. Acak supaya situs yang
     * mengizinkan bersamaan (mis. tepat setelah rilis) tidak melapor di menit
     * yang sama.
     */
    private const FIRST_REPORT_MIN_DELAY = 5 * MINUTE_IN_SECONDS;

    private const FIRST_REPORT_MAX_DELAY = HOUR_IN_SECONDS;

    private static $instance;

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init()
    {
        $widgets = WidgetReporter::instance();

        add_action(self::CRON_FLUSH, [$this, 'flush']);
        add_action(self::CRON_WIDGET_REPORT, [$widgets, 'start']);
        add_action(self::CRON_WIDGET_CHANGED, [$widgets, 'start_after_change']);
        add_action(self::CRON_WIDGET_SCAN, [$widgets, 'run_chunk']);
        add_action(self::CRON_WIDGET_SEND, [$widgets, 'send_ready']);
        add_action(self::CRON_BOOTSTRAP, [$this, 'bootstrap_sync']);

        // Perubahan widget di halaman: data Elementor disimpan (editor, import
        // template kit, WP-CLI), post dihapus, atau post terbit/batal terbit.
        add_action('added_post_meta', [$widgets, 'on_meta_changed'], 10, 3);
        add_action('updated_post_meta', [$widgets, 'on_meta_changed'], 10, 3);
        add_action('deleted_post_meta', [$widgets, 'on_meta_changed'], 10, 3);
        add_action('transition_post_status', [$widgets, 'on_status_changed'], 10, 3);

        // Addon RTM Templates tidak punya modul telemetry sendiri: RTMKit sudah
        // aktif setiap kali addon itu di-aktif/nonaktifkan, jadi dicatat dari sini.
        add_action('activated_plugin', [$this, 'on_plugin_activated']);
        add_action('deactivated_plugin', [$this, 'on_plugin_deactivated']);

        // Install/uninstall addon RTM mana pun. Pemeriksaan tiap jam jalan
        // sebelum flush supaya event-nya ikut terkirim di putaran yang sama.
        $addons = AddonTracker::instance();

        add_action('upgrader_process_complete', [$addons, 'on_upgrader_complete'], 20, 2);
        add_action('deleted_plugin', [$addons, 'on_plugin_deleted'], 10, 2);
        add_action(self::CRON_FLUSH, [$addons, 'check_scheduled'], 5);

        // Connect ulang membatalkan status "domain tidak dikenal". Setelah
        // Disconnect Account option-nya sudah dihapus, jadi connect berikutnya
        // lewat add_option, bukan update_option.
        add_action('update_option_rtm_connector_last_payload', [TelemetryState::instance(), 'unblock']);
        add_action('add_option_rtm_connector_last_payload', [TelemetryState::instance(), 'unblock']);

        ConsentUi::instance()->init();
        DeactivationFeedback::instance()->init();

        add_action('admin_init', [$this, 'schedule_events']);
        add_action('wp', [$this, 'schedule_events']);
    }

    /* ── Penjadwalan ─────────────────────────────────────────────────── */

    public function schedule_events(): void
    {
        if (! wp_next_scheduled(self::CRON_FLUSH)) {
            wp_schedule_event(time() + MINUTE_IN_SECONDS, 'hourly', self::CRON_FLUSH);
        }

        if (! wp_next_scheduled(self::CRON_WIDGET_REPORT)) {
            // Jam acak per situs supaya laporan mingguan tidak datang serentak
            // ke hub setiap Senin pagi.
            wp_schedule_event(time() + wp_rand(HOUR_IN_SECONDS, WEEK_IN_SECONDS), 'weekly', self::CRON_WIDGET_REPORT);
        }
    }

    public static function unschedule_events(): void
    {
        foreach ([self::CRON_FLUSH, self::CRON_WIDGET_REPORT, self::CRON_WIDGET_CHANGED, self::CRON_WIDGET_SCAN, self::CRON_WIDGET_SEND, self::CRON_BOOTSTRAP] as $hook) {
            wp_clear_scheduled_hook($hook);
        }
    }

    /**
     * Kirim antrean event sebentar lagi, atau setelah jeda dari hub lewat.
     * Kalau sudah ada jadwal kirim yang lebih awal, event ini ikut di sana.
     */
    public static function schedule_flush(int $delay = self::FLUSH_DELAY): void
    {
        self::schedule_once(self::CRON_FLUSH, time() + $delay);
    }

    /**
     * Laporan widget setelah halaman berubah. Simpan berikutnya selama jeda ini
     * tidak menambah jadwal: satu laporan menampung semuanya.
     */
    public static function schedule_widget_change_report(): void
    {
        if (wp_next_scheduled(self::CRON_WIDGET_CHANGED)) {
            return;
        }

        wp_schedule_single_event(
            max(time() + self::WIDGET_CHANGE_DELAY, TelemetryState::instance()->paused_until()),
            self::CRON_WIDGET_CHANGED
        );
    }

    /**
     * Izin baru diberikan: laporan widget pertama tidak perlu menunggu jadwal
     * mingguan, yang bisa jatuh sampai seminggu lagi.
     */
    public static function schedule_first_widget_report(): void
    {
        self::schedule_once(self::CRON_WIDGET_REPORT, time() + wp_rand(self::FIRST_REPORT_MIN_DELAY, self::FIRST_REPORT_MAX_DELAY));
    }

    /**
     * Jadwalkan satu kali, tidak lebih awal dari akhir jeda, kecuali hook yang
     * sama memang sudah dijadwalkan lebih awal. Jadwal yang jatuh saat jeda
     * menjadwalkan dirinya lagi (lihat flush() dan WidgetReporter).
     */
    private static function schedule_once(string $hook, int $timestamp): void
    {
        $timestamp = max($timestamp, TelemetryState::instance()->paused_until());
        $next      = wp_next_scheduled($hook);

        if (false !== $next && $next <= $timestamp) {
            return;
        }

        wp_schedule_single_event($timestamp, $hook);
    }

    /**
     * Situs yang sudah connect sebelum fitur ini ada belum punya
     * `analytics_site_key` di payload-nya; sync berikutnya baru seminggu lagi.
     * Dipanggil saat plugin di-update supaya link upgrade langsung membawa
     * kode situs.
     */
    public static function schedule_bootstrap_sync(): void
    {
        if (! wp_next_scheduled(self::CRON_BOOTSTRAP)) {
            wp_schedule_single_event(time() + MINUTE_IN_SECONDS, self::CRON_BOOTSTRAP);
        }
    }

    public function bootstrap_sync(): void
    {
        $state = TelemetryState::instance();

        if (! $state->is_connected() || '' !== $state->site_key()) {
            return;
        }

        delete_transient('rtmkit_connector_last_sync');
        \RTMKit\Modules\Connector\ConnectorStorage::instance()->sync();
    }

    /* ── Event ───────────────────────────────────────────────────────── */

    /**
     * Aktivasi RTMKit. Dipanggil dari register_activation_hook: pada request
     * itu modul ini belum sempat memasang hook apa pun. Pengirimannya sudah
     * dijadwalkan oleh antrean, jadi layar "Plugin activated" tidak tertahan.
     */
    public static function on_activate(): void
    {
        TelemetryQueue::instance()->record(TelemetryQueue::EVENT_ACTIVATED, TelemetryState::PLUGIN_RTMKIT);

        self::schedule_bootstrap_sync();
    }

    /**
     * Nonaktif RTMKit. Setelah ini WP-Cron tidak akan memanggil kita lagi, jadi
     * antrean dikirim sekarang juga dengan timeout pendek. Kalau hub sedang
     * minta jeda, antreannya tetap tersimpan dan terkirim saat diaktifkan lagi.
     */
    public static function on_deactivate(): void
    {
        TelemetryQueue::instance()->record(TelemetryQueue::EVENT_DEACTIVATED, TelemetryState::PLUGIN_RTMKIT);

        self::instance()->flush(self::TIMEOUT_FOREGROUND, 1);
        self::unschedule_events();
    }

    public function on_plugin_activated($plugin): void
    {
        if (! $this->is_rtm_templates($plugin)) {
            return;
        }

        TelemetryQueue::instance()->record(TelemetryQueue::EVENT_ACTIVATED, TelemetryState::PLUGIN_TEMPLATES);
    }

    public function on_plugin_deactivated($plugin): void
    {
        if (! $this->is_rtm_templates($plugin)) {
            return;
        }

        TelemetryQueue::instance()->record(TelemetryQueue::EVENT_DEACTIVATED, TelemetryState::PLUGIN_TEMPLATES);

        // Nonaktif addon tidak mematikan cron RTMKit, tapi mengirim sekarang
        // membuat statusnya langsung terlihat di hub.
        $this->flush(self::TIMEOUT_FOREGROUND, 1);
    }

    private function is_rtm_templates($plugin): bool
    {
        return is_string($plugin) && 0 === strpos($plugin, TelemetryState::PLUGIN_TEMPLATES . '/');
    }

    /**
     * Satu template kit selesai diunduh dan diekstrak.
     *
     * @param string|int $kit_id ID kit di katalog rtmwp.com.
     */
    public static function record_kit_download($kit_id): void
    {
        $kit_id = (string) $kit_id;

        if ('' === $kit_id) {
            return;
        }

        TelemetryQueue::instance()->record(
            TelemetryQueue::EVENT_KIT_DOWNLOAD,
            TelemetryState::PLUGIN_RTMKIT,
            ['subject_key' => $kit_id]
        );
    }

    /* ── Pengiriman ──────────────────────────────────────────────────── */

    /**
     * Kirim antrean event ke hub.
     *
     * @param int $max_batches Kiriman per putaran. Saat pengguna menunggu
     *                         (nonaktif) cukup satu, supaya halamannya tidak
     *                         tertahan beberapa kali timeout.
     */
    public function flush(int $timeout = self::TIMEOUT_BACKGROUND, int $max_batches = self::MAX_BATCHES): void
    {
        $state = TelemetryState::instance();

        if (! $state->is_connected() || ! $state->can_report()) {
            return;
        }

        // Hub minta jeda: antrean tetap di tempatnya, kirim setelah jeda lewat.
        if ($state->is_paused()) {
            if (TelemetryQueue::instance()->count() > 0) {
                self::schedule_flush(0);
            }

            return;
        }

        $lock = 'rtmkit_telemetry_flushing';

        if (get_transient($lock)) {
            return;
        }

        set_transient($lock, 1, 5 * MINUTE_IN_SECONDS);

        try {
            $this->flush_queue($state, $timeout, max(1, $max_batches));
        } finally {
            delete_transient($lock);
        }
    }

    private function flush_queue(TelemetryState $state, int $timeout, int $max_batches): void
    {
        $queue_store = TelemetryQueue::instance();
        $stored      = $queue_store->all();
        $queue       = $queue_store->drop_expired($stored);

        if (count($queue) !== count($stored)) {
            $queue_store->replace($queue);
        }

        for ($sent = 0; ! empty($queue) && $sent < $max_batches; ++$sent) {
            $batch = array_slice($queue, 0, TelemetryQueue::MAX_EVENTS);
            $body  = array_merge($state->site_context(), ['events' => $batch]);

            $result = TelemetryClient::instance()->send(TelemetryClient::ENDPOINT_EVENTS, $body, true, $timeout);

            switch ($result['result']) {
                case TelemetryClient::RESULT_OK:
                    // Dibaca ulang: event baru bisa masuk ke ujung antrean
                    // selama request tadi berjalan.
                    $queue = array_slice($queue_store->all(), count($batch));
                    $queue_store->replace($queue);
                    $state->update_runtime(['events_sent_at' => time(), 'events_last_error' => '']);
                    break;

                case TelemetryClient::RESULT_REJECTED:
                    // Batch ini tidak akan pernah diterima apa pun yang terjadi.
                    $queue = array_slice($queue_store->all(), count($batch));
                    $queue_store->replace($queue);
                    $state->update_runtime(['events_last_error' => $result['code'] . ' ' . $result['message']]);
                    break;

                case TelemetryClient::RESULT_UNKNOWN_SITE:
                    $state->block('events:' . $result['code']);

                    return;

                default:
                    // Client sudah memasang jeda; seluruh antrean menunggu di sini.
                    $state->update_runtime(['events_last_error' => $result['code'] . ' ' . $result['message']]);
                    self::schedule_flush(0);

                    return;
            }
        }

        if (! empty($queue)) {
            self::schedule_flush(MINUTE_IN_SECONDS);
        }
    }
}
