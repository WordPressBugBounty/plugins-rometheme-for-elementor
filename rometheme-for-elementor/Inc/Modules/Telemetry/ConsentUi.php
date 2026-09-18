<?php

namespace RTMKit\Modules\Telemetry;

if (! defined('ABSPATH')) exit;

/**
 * Tempat pengguna memberi dan mencabut izin pengiriman data pemakaian.
 *
 * Satu kontrol yang sama untuk semua orang: gratis maupun yang akunnya
 * terhubung ke RTMHub. Koneksi akun mengurus lisensi, addon, dan update — itu
 * bukan persetujuan mengirim data pemakaian, jadi mencabut izin di sini tidak
 * pernah memutus koneksi siapa pun.
 *
 * Ditanya sekali lewat notice di halaman Widgets, lalu selamanya bisa diubah di
 * Global Kit Setup dan di halaman Account. Rinciannya selalu ada di balik
 * collapse di sebelah kontrolnya, supaya centangnya diberikan sesudah tahu apa
 * yang dikirim. Tidak pernah muncul per widget: pedoman wordpress.org no. 11
 * melarang plugin mengganggu di luar halamannya sendiri.
 */
class ConsentUi
{
    /** Pembeda id elemen saat kartu ini dirender lebih dari sekali per halaman. */
    private $sequence = 0;

    private static $instance;

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
        add_action('admin_notices', [$this, 'render_admin_notice']);

        if (wp_doing_ajax()) {
            add_action('wp_ajax_rtmkit_telemetry_consent', [$this, 'save_consent']);
            add_action('wp_ajax_rtmkit_telemetry_dismiss_admin_notice', [$this, 'dismiss_admin_notice']);
        }
    }

    public function enqueue(): void
    {
        $screen    = get_current_screen();
        $on_rtmkit = $screen && str_contains($screen->id, 'rtmkit');

        // Di luar halaman RTMKit skrip ini hanya dibutuhkan untuk menyimpan
        // penutupan pemberitahuan izin; kalau tidak ada yang ditampilkan,
        // halaman lain tidak perlu ikut memuatnya.
        if (! $on_rtmkit && ! $this->should_show_admin_notice()) {
            return;
        }

        if ($on_rtmkit) {
            wp_enqueue_style(
                'rtmkit-telemetry',
                RTM_KIT_URL . 'assets/css/telemetry.css',
                ['rtmkit-style'],
                RTM_KIT_VERSION
            );
        }

        wp_enqueue_script(
            'rtmkit-telemetry',
            RTM_KIT_URL . 'assets/js/telemetry.js',
            [],
            RTM_KIT_VERSION,
            true
        );

        wp_localize_script('rtmkit-telemetry', 'rtmkitTelemetry', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('rtmkit_nonce'),
        ]);
    }

    /* ── AJAX ────────────────────────────────────────────────────────── */

    public function save_consent(): void
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');

        if (! current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Access denied.', 'rometheme-for-elementor')], 403);
        }

        $granted = isset($_POST['consent']) && '1' === sanitize_text_field(wp_unslash($_POST['consent']));

        $state = TelemetryState::instance();

        // Menolak berarti berhenti sekarang juga: antrean yang belum terkirim
        // dan identitas anonimnya ikut dibuang di dalam set_consent().
        $state->set_consent($granted);

        if ($granted) {
            TelemetryModule::schedule_first_widget_report();
        }

        wp_send_json_success([
            'consent' => $state->consent(),
            'message' => $granted
                ? __('Thanks — usage analytics is on. You can turn it off here anytime.', 'rometheme-for-elementor')
                : __('Usage analytics is off. RTMKit will not send any usage data from this site.', 'rometheme-for-elementor'),
        ]);
    }

    /**
     * Pemberitahuan di wp-admin ditutup lewat tombol silang bawaan WordPress.
     * Tanpa ini penutupannya cuma bertahan sampai halaman dimuat ulang.
     */
    public function dismiss_admin_notice(): void
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');

        if (! current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Access denied.', 'rometheme-for-elementor')], 403);
        }

        update_option(TelemetryState::OPTION_ADMIN_NOTICE_DISMISSED, 1, false);

        wp_send_json_success();
    }

    /* ── Tampilan ────────────────────────────────────────────────────── */

    public function should_show_notice(): bool
    {
        if (! current_user_can('manage_options')) {
            return false;
        }

        return TelemetryState::instance()->should_ask_consent();
    }

    /**
     * Pemberitahuan di seluruh wp-admin bahwa ada izin yang menunggu dijawab.
     *
     * Muncul sekali dan bisa ditutup permanen: izin yang belum dijawab berarti
     * tidak ada data yang dikirim, jadi menutupnya tanpa menjawab sama sekali
     * tidak merugikan pemilik situs. Tidak ditampilkan di layar yang sudah
     * memuat kontrol izinnya sendiri — menanyakan hal yang sama dua kali dalam
     * satu halaman cuma membuat orang mengabaikan keduanya.
     */
    public function should_show_admin_notice(): bool
    {
        if (! current_user_can('manage_options') || is_network_admin()) {
            return false;
        }

        if (get_option(TelemetryState::OPTION_ADMIN_NOTICE_DISMISSED)) {
            return false;
        }

        if ($this->has_inline_consent()) {
            return false;
        }

        return TelemetryState::instance()->should_ask_consent();
    }

    /**
     * Halaman yang sedang dibuka sudah memuat sakelar izinnya sendiri: panel di
     * Account, atau kartu tanya di Widgets selama kartu itu belum ditutup.
     */
    private function has_inline_consent(): bool
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- hanya membaca halaman mana yang sedang dibuka.
        if (! isset($_GET['page']) || 'rtmkit' !== sanitize_text_field(wp_unslash($_GET['page']))) {
            return false;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- sama, tidak ada aksi yang dijalankan.
        $path = isset($_GET['path']) ? sanitize_text_field(wp_unslash($_GET['path'])) : '';

        if ('account' === $path) {
            return true;
        }

        return 'widgets' === $path && $this->should_show_notice();
    }

    /**
     * Pakai markup notice bawaan WordPress, bukan kartu RTMKit: ini muncul di
     * halaman milik orang lain, jadi ia harus terlihat seperti pemberitahuan
     * WordPress biasa dan tidak membawa stylesheet panel ke mana-mana.
     */
    public function render_admin_notice(): void
    {
        if (! $this->should_show_admin_notice()) {
            return;
        }
        ?>
        <?php /* data-…-notice: hapus kartunya begitu dijawab. data-…-admin-notice:
                 simpan penutupan lewat tombol silang bawaan WordPress. */ ?>
        <div
            class="notice notice-info is-dismissible"
            data-rtmkit-telemetry-notice
            data-rtmkit-telemetry-admin-notice
            data-rtmkit-telemetry-scope
        >
            <p>
                <strong><?php esc_html_e('RTMKit needs your consent before it sends any usage data', 'rometheme-for-elementor'); ?></strong>
            </p>
            <p>
                <?php esc_html_e('Usage analytics is off, so RTMKit is sending nothing from this site. It stays off until you allow it — connecting an RTMHub account does not turn it on, and turning it off later never disconnects your account.', 'rometheme-for-elementor'); ?>
            </p>
            <p>
                <button type="button" class="button button-primary" data-rtmkit-telemetry-consent="1">
                    <?php esc_html_e('Allow', 'rometheme-for-elementor'); ?>
                </button>
                <button type="button" class="button" data-rtmkit-telemetry-consent="0">
                    <?php esc_html_e('No thanks', 'rometheme-for-elementor'); ?>
                </button>
                <?php /* Aksi ketiga, jadi tautan biasa: tiga tombol berjajar
                         membuat notice ini berat dan tidak jelas mana yang
                         utama. Pola yang sama dipakai notice inti WordPress. */ ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=rtmkit&path=account') . '#rtmkit-usage-analytics'); ?>">
                    <?php esc_html_e('View details', 'rometheme-for-elementor'); ?>
                </a>
                <span class="rtmkit-telemetry-feedback" role="status" aria-live="polite"></span>
            </p>
        </div>
        <?php
    }

    /**
     * Notice sekali-tanya di halaman Widgets. Muncul untuk semua orang; selama
     * belum dijawab, tidak ada laporan yang dikirim.
     */
    public function render_notice(): void
    {
        if (! $this->should_show_notice()) {
            return;
        }
        ?>
        <div class="card rounded-4 flex-column gap-3 rtmkit-consent rtmkit-telemetry-notice" data-rtmkit-telemetry-notice data-rtmkit-telemetry-scope>
            <div class="d-flex flex-column gap-2">
                <h4 class="m-0"><?php esc_html_e('Help us improve RTMKit', 'rometheme-for-elementor'); ?></h4>
                <span>
                    <?php esc_html_e('May RTMKit send us usage analytics — which RTMKit widgets, template kits, and add-ons this site uses? It is entirely optional, and nothing is sent unless you say yes.', 'rometheme-for-elementor'); ?>
                </span>
            </div>

            <?php $this->render_details(); ?>

            <div class="d-flex gap-2 align-items-center flex-wrap">
                <button type="button" class="btn btn-accent rounded-2" data-rtmkit-telemetry-consent="1">
                    <?php esc_html_e('Allow', 'rometheme-for-elementor'); ?>
                </button>
                <button type="button" class="btn btn-secondary rounded-2" data-rtmkit-telemetry-consent="0">
                    <?php esc_html_e('No thanks', 'rometheme-for-elementor'); ?>
                </button>
                <span class="rtmkit-telemetry-feedback" role="status" aria-live="polite"></span>
            </div>
        </div>
        <?php
    }

    /**
     * Panel di halaman Account — satu-satunya tempat pilihan ini diubah, jadi
     * id-nya dipakai sebagai target tautan dari mana pun. Diletakkan tepat di
     * sebelah status koneksi supaya jelas keduanya hal yang berbeda: mencabut
     * izin di sini tidak memutus akun.
     */
    public function render_account_panel(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $connected = TelemetryState::instance()->is_connected();
        ?>
        <div class="d-flex flex-column gap-3 border p-3 rounded-3 rtmkit-consent" id="rtmkit-usage-analytics" data-rtmkit-telemetry-scope>
            <div class="rtmkit-consent__heading">
                <h4 class="m-0"><?php esc_html_e('Usage Analytics', 'rometheme-for-elementor'); ?></h4>
                <?php $this->render_status(); ?>
            </div>
            <span class="lh-base">
                <?php if ($connected) : ?>
                    <?php esc_html_e('Usage analytics is separate from your account connection. Your connection keeps your license, add-ons, and updates in sync; usage analytics only runs while the switch below is on, and turning it off never disconnects this site.', 'rometheme-for-elementor'); ?>
                <?php else : ?>
                    <?php esc_html_e('Usage analytics is separate from connecting an account. It only runs while the switch below is on, and it stays off until you turn it on — connected or not.', 'rometheme-for-elementor'); ?>
                <?php endif; ?>
            </span>

            <?php
            $this->render_toggle();

            // Tautan "ubah izin" menunjuk ke panel ini; di panel ini sendiri
            // tautan itu cuma menunjuk balik ke dirinya.
            $this->render_details(false);
            ?>
        </div>
        <?php
    }

    /* ── Bagian yang dipakai ulang ───────────────────────────────────── */

    /**
     * Pil status. Belum dijawab tampil sebagai "Off", karena memang itu yang
     * sedang terjadi: tidak ada yang dikirim.
     */
    private function render_status(): void
    {
        $on = TelemetryState::instance()->has_consent();
        ?>
        <span class="rtmkit-consent__status<?php echo $on ? ' rtmkit-consent__status--on' : ''; ?>">
            <?php echo $on ? esc_html__('On', 'rometheme-for-elementor') : esc_html__('Off', 'rometheme-for-elementor'); ?>
        </span>
        <?php
    }

    /**
     * Sakelar izinnya sendiri. Belum dijawab dihitung sebagai belum diizinkan:
     * centangnya harus diberikan, tidak pernah tercentang lebih dulu.
     *
     * Label menyebut "anonymous" hanya kalau memang anonim. Situs yang connect
     * mengirim alamat situsnya, jadi menjanjikan anonim di sana keliru.
     */
    private function render_toggle(): void
    {
        $state = TelemetryState::instance();
        $id    = 'rtmkit-telemetry-consent-' . (++$this->sequence);
        $last  = $state->last_reported_at();
        ?>
        <div class="rtmkit-consent__switch">
            <?php /* Tanpa for=: input-nya sudah di dalam label, dan pasangan
                     for + input bersarang adalah pola yang bisa menghasilkan
                     dua kali aktivasi dari satu klik. Semua .switch lain di
                     panel ini juga mengandalkan penyarangan saja. */ ?>
            <label class="switch gap-3">
                <input
                    type="checkbox"
                    id="<?php echo esc_attr($id); ?>"
                    class="switch-input"
                    data-rtmkit-telemetry-toggle
                    <?php checked($state->has_consent()); ?>
                >
                <span class="slider round"></span>
                <span class="rtmkit-consent__switch-text">
                    <span class="rtmkit-consent__switch-label">
                        <?php
                        echo $state->is_connected()
                            ? esc_html__('Allow usage analytics for this site', 'rometheme-for-elementor')
                            : esc_html__('Allow anonymous usage analytics', 'rometheme-for-elementor');
                        ?>
                    </span>
                    <span class="rtmkit-consent__switch-hint">
                        <?php if ($state->has_consent() && $last > 0) : ?>
                            <?php
                            printf(
                                /* translators: %s: human readable time difference, e.g. "2 days". */
                                esc_html__('Last report sent %s ago.', 'rometheme-for-elementor'),
                                esc_html(human_time_diff($last))
                            );
                            ?>
                        <?php elseif ($state->has_consent()) : ?>
                            <?php esc_html_e('No report sent yet.', 'rometheme-for-elementor'); ?>
                        <?php else : ?>
                            <?php esc_html_e('Off — nothing is being sent from this site.', 'rometheme-for-elementor'); ?>
                        <?php endif; ?>
                    </span>
                </span>
            </label>
            <span class="rtmkit-telemetry-feedback" role="status" aria-live="polite"></span>
        </div>
        <?php
    }

    /**
     * Rincian di balik collapse: apa yang dikirim kalau diizinkan, dan apa yang
     * tidak pernah dikirim sama sekali. id-nya dibuat per render karena satu
     * halaman bisa memuat notice dan kartunya sekaligus.
     *
     * @param bool $with_change_link Sertakan tautan ke panel Account. Dimatikan
     *                               saat dirender di panel itu sendiri.
     */
    private function render_details(bool $with_change_link = true): void
    {
        $id        = 'rtmkit-telemetry-details-' . (++$this->sequence);
        $connected = TelemetryState::instance()->is_connected();

        // Host dibaca dari konstanta, bukan ditulis tetap: instalasi dev
        // menimpa RTMHUB_API_URL dan panel ini harus ikut jujur.
        $host = (string) wp_parse_url(RTMHUB_API_URL, PHP_URL_HOST);
        ?>
        <div class="d-flex flex-column">
            <button
                type="button"
                class="rtmkit-consent__disclose"
                data-bs-toggle="collapse"
                data-bs-target="#<?php echo esc_attr($id); ?>"
                aria-expanded="false"
                aria-controls="<?php echo esc_attr($id); ?>"
            >
                <span><?php esc_html_e('What exactly is collected?', 'rometheme-for-elementor'); ?></span>
                <span class="rtmkit-consent__chevron" aria-hidden="true"></span>
            </button>

            <div class="collapse" id="<?php echo esc_attr($id); ?>">
                <div class="rtmkit-consent__panel">
                    <div class="rtmkit-consent__grid">
                        <div>
                            <h5 class="rtmkit-consent__panel-title"><?php esc_html_e('While it is on, RTMKit sends', 'rometheme-for-elementor'); ?></h5>
                            <ul class="rtmkit-consent__list rtmkit-consent__list--sends">
                                <li><?php esc_html_e('Which RTMKit widgets your published pages use, and how many of each — names and counts only. Sent when your published pages change, and at least once a week.', 'rometheme-for-elementor'); ?></li>
                                <?php if ($connected) : ?>
                                    <li><?php esc_html_e('Which template kits you download through the plugin.', 'rometheme-for-elementor'); ?></li>
                                    <li><?php esc_html_e('When RTM add-ons are installed or removed, and when RTMKit or its add-ons are activated, deactivated, or uninstalled.', 'rometheme-for-elementor'); ?></li>
                                <?php endif; ?>
                                <li><?php esc_html_e('Your RTMKit, WordPress, and Elementor versions, and whether a Pro license is active.', 'rometheme-for-elementor'); ?></li>
                                <?php if ($connected) : ?>
                                    <li><?php esc_html_e('This site address, so the report is attached to your RTMHub account.', 'rometheme-for-elementor'); ?></li>
                                <?php else : ?>
                                    <li><?php esc_html_e('A random installation ID generated on this site — not your site address, and not tied to any account.', 'rometheme-for-elementor'); ?></li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <div>
                            <h5 class="rtmkit-consent__panel-title"><?php esc_html_e('RTMKit never sends', 'rometheme-for-elementor'); ?></h5>
                            <ul class="rtmkit-consent__list rtmkit-consent__list--never">
                                <li><?php esc_html_e('Names or email addresses of your visitors or customers', 'rometheme-for-elementor'); ?></li>
                                <li><?php esc_html_e('Form submissions collected on your site', 'rometheme-for-elementor'); ?></li>
                                <li><?php esc_html_e('Order or payment information', 'rometheme-for-elementor'); ?></li>
                                <li><?php esc_html_e('What your visitors browse, or anything about who they are', 'rometheme-for-elementor'); ?></li>
                                <li><?php esc_html_e('Your WordPress users, their accounts, or their passwords', 'rometheme-for-elementor'); ?></li>
                                <li><?php esc_html_e('The content of your pages, posts, or media', 'rometheme-for-elementor'); ?></li>
                            </ul>
                        </div>
                    </div>

                    <div class="rtmkit-consent__meta">
                        <span><?php esc_html_e('Kept for 12 months.', 'rometheme-for-elementor'); ?></span>
                        <a href="https://rometheme.net/privacy-policy/" target="_blank" rel="noopener noreferrer">
                            <?php esc_html_e('Privacy policy', 'rometheme-for-elementor'); ?>
                        </a>
                        <?php if ($with_change_link) : ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=rtmkit&path=account') . '#rtmkit-usage-analytics'); ?>">
                                <?php esc_html_e('Change or withdraw consent in RTMKit → Account', 'rometheme-for-elementor'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}
