<?php

namespace RTMKit\Modules\Telemetry;

if (! defined('ABSPATH')) exit;

/**
 * Satu pintu untuk semua keadaan telemetry: status connect, kode situs, izin
 * pengguna, dan identitas anonim situs yang belum connect.
 *
 * Tidak ada kelas lain yang boleh membaca option ini langsung, supaya nama
 * option dan aturan "kapan boleh melapor" cuma hidup di satu tempat.
 */
class TelemetryState
{
    /** Antrean event yang belum terkirim ke hub. */
    public const OPTION_QUEUE = 'rtmkit_telemetry_queue';

    /** Izin kirim data pemakaian: '' (belum ditanya), 'granted', atau 'denied'. */
    public const OPTION_CONSENT = 'rtmkit_telemetry_consent';

    /** install_id anonim + hash home_url tempat id itu dibuat. */
    public const OPTION_INSTALL = 'rtmkit_telemetry_install';

    /** Catatan pengiriman terakhir: blokir 404, error terakhir, dsb. */
    public const OPTION_RUNTIME = 'rtmkit_telemetry_runtime';

    /** Kemajuan pemindaian widget yang sedang berjalan. */
    public const OPTION_SCAN = 'rtmkit_telemetry_scan';

    /** Cache pemetaan nama widget Elementor ke metadata RTMKit. */
    public const OPTION_WIDGET_MAP = 'rtmkit_telemetry_widget_map';

    /** Addon RTM yang terpasang saat terakhir diperiksa, untuk menghitung install/uninstall. */
    public const OPTION_ADDONS = 'rtmkit_telemetry_addons';

    /** Pemberitahuan izin di seluruh wp-admin sudah ditutup. */
    public const OPTION_ADMIN_NOTICE_DISMISSED = 'rtmkit_telemetry_admin_notice_dismissed';

    /**
     * Hub minta berhenti dulu (429, 5xx, tidak bisa dihubungi): isinya timestamp
     * kapan boleh mengirim lagi. Sengaja transient — kalau hilang lebih awal
     * (cache dibersihkan), akibatnya cuma satu request yang ditolak lagi. Data
     * yang ditahan tetap di option, bukan di sini.
     */
    public const TRANSIENT_PAUSE = 'rtmkit_telemetry_paused_until';

    public const CONSENT_GRANTED = 'granted';
    public const CONSENT_DENIED  = 'denied';

    public const PLUGIN_RTMKIT    = 'rometheme-for-elementor';
    public const PLUGIN_TEMPLATES = 'rtm-templates';

    private static $instance;

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Payload koneksi RTMHub apa adanya. Option ini juga dipakai kode lain
     * (ConnectorStorage, Plugin::pro_is_active) dan bisa berisi array kosong
     * kalau koneksi terakhir gagal.
     */
    public function payload(): array
    {
        $payload = get_option('rtm_connector_last_payload');

        return is_array($payload) ? $payload : [];
    }

    /**
     * Situs dianggap connect hanya kalau payload benar-benar membawa token.
     * Cek `option !== false` saja tidak cukup: ConnectorModule menulis array
     * kosong ke option yang sama saat exchange gagal.
     */
    public function is_connected(): bool
    {
        $payload = $this->payload();

        return ! empty($payload['access_token']) && is_string($payload['access_token']);
    }

    /**
     * Kode situs dari hub, dipakai sebagai parameter `s` di link upgrade.
     * Situs yang connect sebelum fitur ini ada baru mendapatkannya setelah
     * sync berikutnya.
     */
    public function site_key(): string
    {
        $payload = $this->payload();

        return isset($payload['analytics_site_key']) && is_string($payload['analytics_site_key'])
            ? $payload['analytics_site_key']
            : '';
    }

    public function domain(): string
    {
        return strtolower((string) wp_parse_url(home_url(), PHP_URL_HOST));
    }

    /**
     * Data situs yang menyertai setiap laporan. Hub memakainya untuk mengenali
     * situs dan mengisi kolom versi; tidak ada isi halaman atau data pribadi.
     *
     * @return array<string, mixed>
     */
    public function site_context(): array
    {
        return [
            'domain'            => $this->domain(),
            'wp_version'        => get_bloginfo('version'),
            'elementor_version' => defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : null,
            'pro_active'        => \RTMKit\Core\Plugin::instance()->pro_is_active(),
        ];
    }

    /* ── Izin pengguna ───────────────────────────────────────────────── */

    public function consent(): string
    {
        $consent = get_option(self::OPTION_CONSENT, '');

        return in_array($consent, [self::CONSENT_GRANTED, self::CONSENT_DENIED], true) ? $consent : '';
    }

    public function has_answered_consent(): bool
    {
        return '' !== $this->consent();
    }

    public function has_consent(): bool
    {
        return self::CONSENT_GRANTED === $this->consent();
    }

    public function set_consent(bool $granted): void
    {
        update_option(
            self::OPTION_CONSENT,
            $granted ? self::CONSENT_GRANTED : self::CONSENT_DENIED,
            false
        );

        if ($granted && ! $this->is_connected()) {
            // Buat identitasnya sekarang supaya laporan pertama tidak perlu
            // menunggu pemindaian untuk punya install_id.
            $this->install_id();
        }

        if (! $granted) {
            $this->purge();
        }
    }

    /**
     * Semua situs ditanya, connect atau tidak. Menghubungkan akun RTMHub
     * mengurus lisensi, addon, dan update; itu bukan persetujuan mengirim data
     * pemakaian. Izinnya ditanya terpisah supaya pelanggan berbayar bisa
     * mencabutnya tanpa memutus koneksi akunnya.
     */
    public function should_ask_consent(): bool
    {
        return ! $this->has_answered_consent();
    }

    /**
     * Gerbang tunggal sebelum apa pun meninggalkan situs ini. Dipakai antrean
     * event, flush cron, dan laporan widget; tidak ada jalur keluar lain.
     */
    public function can_report(): bool
    {
        if (! $this->has_consent()) {
            return false;
        }

        // `blocked` hanya berlaku untuk situs yang connect: hub menolak
        // domainnya sampai situs itu connect ulang.
        return ! $this->is_connected() || ! $this->is_blocked();
    }

    /**
     * Izin dicabut: buang semua yang masih menunggu kirim, beserta identitas
     * anonimnya. Berhenti harus berarti berhenti, bukan berhenti setelah
     * antrean terakhir habis.
     */
    public function purge(): void
    {
        TelemetryQueue::instance()->clear();
        WidgetReporter::instance()->reset();

        $this->forget_install();
    }

    /* ── Identitas anonim ────────────────────────────────────────────── */

    /**
     * UUID acak per instalasi. Dibuat ulang kalau home_url berubah, supaya
     * situs yang dikloning ke staging tidak dihitung sebagai situs yang sama.
     */
    public function install_id(): string
    {
        $stored = get_option(self::OPTION_INSTALL, []);
        $home   = $this->home_hash();

        if (is_array($stored) && ! empty($stored['id']) && isset($stored['home']) && hash_equals((string) $stored['home'], $home)) {
            return (string) $stored['id'];
        }

        $id = wp_generate_uuid4();

        update_option(self::OPTION_INSTALL, ['id' => $id, 'home' => $home], false);

        // install_id lama sudah tidak berlaku; laporan berikutnya tidak lagi
        // mengklaim laporan anonim milik situs asal.
        $this->forget_reported_install_id();

        return $id;
    }

    /**
     * install_id yang pernah dipakai melapor anonim. Dikirim sekali saat situs
     * sudah connect supaya hub membuang laporan anonim duplikatnya.
     */
    public function reported_install_id(): string
    {
        $runtime = $this->runtime();

        return isset($runtime['reported_install_id']) ? (string) $runtime['reported_install_id'] : '';
    }

    public function remember_reported_install_id(string $id): void
    {
        $this->update_runtime(['reported_install_id' => $id]);
    }

    public function forget_reported_install_id(): void
    {
        $this->update_runtime(['reported_install_id' => '']);
    }

    private function home_hash(): string
    {
        return hash('sha256', untrailingslashit(home_url()));
    }

    /* ── Catatan pengiriman ──────────────────────────────────────────── */

    /**
     * @return array<string, mixed>
     */
    public function runtime(): array
    {
        $runtime = get_option(self::OPTION_RUNTIME, []);

        return is_array($runtime) ? $runtime : [];
    }

    /**
     * @param array<string, mixed> $values
     */
    public function update_runtime(array $values): void
    {
        update_option(self::OPTION_RUNTIME, array_merge($this->runtime(), $values), false);
    }

    /**
     * Kapan laporan widget terakhir benar-benar diterima hub. Ditampilkan di
     * kartu izin supaya pemilik situs bisa memeriksa sendiri, bukan cuma
     * percaya pada penjelasan kapan laporan dikirim.
     */
    public function last_reported_at(): int
    {
        $runtime = $this->runtime();

        return isset($runtime['widgets_reported_at']) ? (int) $runtime['widgets_reported_at'] : 0;
    }

    /**
     * Hub menjawab 404 kalau domain tidak terdaftar di akun pemilik token.
     * Selama ditandai begini plugin berhenti mengirim sampai situs connect ulang.
     */
    public function is_blocked(): bool
    {
        $runtime = $this->runtime();

        return ! empty($runtime['blocked']);
    }

    public function block(string $reason): void
    {
        $this->update_runtime(['blocked' => true, 'blocked_reason' => $reason, 'blocked_at' => time()]);
    }

    public function unblock(): void
    {
        $this->update_runtime(['blocked' => false, 'blocked_reason' => '', 'blocked_at' => 0]);
    }

    /* ── Jeda pengiriman ─────────────────────────────────────────────── */

    /**
     * Timestamp kapan boleh mengirim lagi, atau 0 kalau tidak sedang jeda.
     */
    public function paused_until(): int
    {
        $until = (int) get_transient(self::TRANSIENT_PAUSE);

        return $until > time() ? $until : 0;
    }

    public function is_paused(): bool
    {
        return $this->paused_until() > 0;
    }

    /**
     * @param int $seconds  Lama jeda, sudah termasuk jeda acak.
     * @param int $failures Kegagalan beruntun sejauh ini, untuk menaikkan jeda berikutnya.
     */
    public function pause(int $seconds, int $failures): int
    {
        $until = time() + $seconds;

        set_transient(self::TRANSIENT_PAUSE, $until, $seconds);
        $this->update_runtime(['retry_failures' => $failures, 'paused_until' => $until]);

        return $until;
    }

    public function retry_failures(): int
    {
        $runtime = $this->runtime();

        return isset($runtime['retry_failures']) ? (int) $runtime['retry_failures'] : 0;
    }

    /**
     * Hub menerima lagi. Hanya menulis option kalau memang ada yang direset,
     * supaya kiriman normal tidak menambah satu query tulis.
     */
    public function clear_pause(): void
    {
        if (0 === $this->retry_failures() && false === get_transient(self::TRANSIENT_PAUSE)) {
            return;
        }

        delete_transient(self::TRANSIENT_PAUSE);
        $this->update_runtime(['retry_failures' => 0, 'paused_until' => 0]);
    }

    /**
     * Buang identitas anonim situs, supaya laporan berikutnya tidak menyambung
     * ke laporan sebelumnya. Dipanggil lewat purge().
     */
    public function forget_install(): void
    {
        delete_option(self::OPTION_INSTALL);
        $this->forget_reported_install_id();
    }
}
