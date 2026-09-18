<?php

namespace RTMKit\Modules\Telemetry;

if (! defined('ABSPATH')) exit;

/**
 * Tanya alasan sekali saat RTMKit dinonaktifkan, di halaman Plugins.
 *
 * Dua aturan yang tidak boleh dilanggar:
 *
 *  1. Menonaktifkan plugin tidak pernah tertahan. Apa pun yang terjadi pada
 *     jawaban — tidak diisi, gagal terkirim, hub mati — tautan Deactivate-nya
 *     tetap dijalankan. Menahan orang yang ingin mematikan plugin demi sebuah
 *     survei adalah hal yang paling tidak pantas dilakukan sebuah plugin.
 *
 *  2. Yang dikirim hanya yang diketik pengguna sendiri, dan hanya kalau ia
 *     menekan Submit. "Skip & Deactivate" tidak mengirim apa pun. Ini terpisah
 *     dari izin Usage Analytics: yang ini bukan pengamatan diam-diam, melainkan
 *     satu formulir yang diisi sadar — tapi karena itu juga, ia tidak boleh
 *     berangkat tanpa penekanan tombol.
 */
class DeactivationFeedback
{
    public const ACTION = 'rtmkit_deactivation_feedback';

    /** Batas komentar bebas; hub menolak yang lebih panjang. */
    private const MAX_COMMENT = 500;

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
        add_action('admin_footer-plugins.php', [$this, 'render_modal']);

        if (wp_doing_ajax()) {
            add_action('wp_ajax_' . self::ACTION, [$this, 'submit']);
        }
    }

    /**
     * Pilihan alasan. Nilai kedua adalah placeholder kolom isian lanjutan;
     * null berarti alasan itu sudah cukup jelas tanpa penjelasan tambahan.
     *
     * @return array<string, array{0: string, 1: string|null}>
     */
    private function reasons(): array
    {
        return [
            'better_plugin'    => [__('I found a better plugin', 'rometheme-for-elementor'), __('Which one? It helps us more than you think.', 'rometheme-for-elementor')],
            'stopped_working'  => [__('The plugin suddenly stopped working', 'rometheme-for-elementor'), __('What stopped working?', 'rometheme-for-elementor')],
            'broke_site'       => [__('The plugin broke my site', 'rometheme-for-elementor'), __('What broke? Any error message helps.', 'rometheme-for-elementor')],
            'missing_feature'  => [__('It is missing a feature I need', 'rometheme-for-elementor'), __('Which feature?', 'rometheme-for-elementor')],
            'no_longer_needed' => [__('I no longer need the plugin', 'rometheme-for-elementor'), null],
            'temporary'        => [__('Temporary deactivation — I am troubleshooting something', 'rometheme-for-elementor'), null],
            'other'            => [__('Other', 'rometheme-for-elementor'), __('Please tell us more', 'rometheme-for-elementor')],
        ];
    }

    public function enqueue(): void
    {
        $screen = get_current_screen();

        if (! $screen || 'plugins' !== $screen->id || ! current_user_can('manage_options')) {
            return;
        }

        wp_enqueue_style(
            'rtmkit-deactivation',
            RTM_KIT_URL . 'assets/css/deactivation.css',
            [],
            RTM_KIT_VERSION
        );

        wp_enqueue_script(
            'rtmkit-deactivation',
            RTM_KIT_URL . 'assets/js/deactivation.js',
            [],
            RTM_KIT_VERSION,
            true
        );

        wp_localize_script('rtmkit-deactivation', 'rtmkitDeactivation', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('rtmkit_nonce'),
            'action'   => self::ACTION,
            // Baris RTMKit dikenali dari slug-nya, bukan dari teks tautan:
            // "Deactivate" berubah mengikuti bahasa situs.
            'slug'     => plugin_basename(RTM_KIT_FILE),
        ]);
    }

    /* ── Tampilan ────────────────────────────────────────────────────── */

    /**
     * Modal dicetak di kaki halaman Plugins dalam keadaan tersembunyi, lalu
     * dibuka oleh JS saat tautan Deactivate milik RTMKit diklik. Markup-nya
     * berdiri sendiri: Bootstrap hanya dimuat di halaman RTMKit, jadi di sini
     * tidak ada yang bisa diandalkan selain kelas tombol bawaan wp-admin.
     */
    public function render_modal(): void
    {
        if (! current_user_can('deactivate_plugins')) {
            return;
        }

        $connected = TelemetryState::instance()->is_connected();
        ?>
        <div class="rtmkit-deact" data-rtmkit-deact hidden>
            <div class="rtmkit-deact__backdrop" data-rtmkit-deact-cancel></div>

            <div class="rtmkit-deact__dialog" role="dialog" aria-modal="true" aria-labelledby="rtmkit-deact-title">
                <div class="rtmkit-deact__head">
                    <span class="rtmkit-deact__eyebrow"><?php esc_html_e('Quick feedback', 'rometheme-for-elementor'); ?></span>
                    <button type="button" class="rtmkit-deact__close" data-rtmkit-deact-cancel aria-label="<?php esc_attr_e('Close', 'rometheme-for-elementor'); ?>">&times;</button>
                </div>

                <div class="rtmkit-deact__body">
                    <h2 class="rtmkit-deact__title" id="rtmkit-deact-title">
                        <?php esc_html_e('If you have a moment, please let us know why you are deactivating:', 'rometheme-for-elementor'); ?>
                    </h2>

                    <ul class="rtmkit-deact__reasons">
                        <?php foreach ($this->reasons() as $key => $reason) : ?>
                            <li>
                                <label class="rtmkit-deact__reason">
                                    <input
                                        type="radio"
                                        name="rtmkit-deact-reason"
                                        value="<?php echo esc_attr($key); ?>"
                                        data-rtmkit-deact-reason
                                        <?php if (null !== $reason[1]) : ?>
                                            data-placeholder="<?php echo esc_attr($reason[1]); ?>"
                                        <?php endif; ?>
                                    >
                                    <span><?php echo esc_html($reason[0]); ?></span>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <p class="rtmkit-deact__detail" data-rtmkit-deact-detail hidden>
                        <input
                            type="text"
                            class="rtmkit-deact__input"
                            maxlength="<?php echo esc_attr((string) self::MAX_COMMENT); ?>"
                            data-rtmkit-deact-comment
                        >
                    </p>

                    <p class="rtmkit-deact__note">
                        <?php if ($connected) : ?>
                            <?php esc_html_e('Submitting sends your answer, the RTMKit version, and your WordPress and Elementor versions. This site is connected to your RTMHub account, so we will know which site it came from. Skip sends nothing at all.', 'rometheme-for-elementor'); ?>
                        <?php else : ?>
                            <?php esc_html_e('Submitting sends your answer, the RTMKit version, and your WordPress and Elementor versions — nothing that identifies your site. Skip sends nothing at all.', 'rometheme-for-elementor'); ?>
                        <?php endif; ?>
                    </p>
                </div>

                <div class="rtmkit-deact__foot">
                    <button type="button" class="button" data-rtmkit-deact-cancel>
                        <?php esc_html_e('Cancel', 'rometheme-for-elementor'); ?>
                    </button>
                    <button type="button" class="button" data-rtmkit-deact-skip>
                        <?php esc_html_e('Skip &amp; Deactivate', 'rometheme-for-elementor'); ?>
                    </button>
                    <button type="button" class="button button-primary" data-rtmkit-deact-submit disabled>
                        <?php esc_html_e('Submit &amp; Deactivate', 'rometheme-for-elementor'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    /* ── AJAX ────────────────────────────────────────────────────────── */

    /**
     * Selalu menjawab sukses. Sisi JS hanya menunggu jawaban ini untuk tahu
     * kapan boleh melanjutkan ke tautan Deactivate; gagal mengirim ke hub bukan
     * alasan untuk menahan orang di halaman ini.
     */
    public function submit(): void
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');

        if (! current_user_can('deactivate_plugins')) {
            wp_send_json_error(['message' => __('Access denied.', 'rometheme-for-elementor')], 403);
        }

        $reasons = $this->reasons();
        $reason  = isset($_POST['reason']) ? sanitize_key(wp_unslash($_POST['reason'])) : '';

        // Alasan di luar daftar berarti kiriman yang tidak berasal dari modal
        // ini; tidak ada yang layak diteruskan ke hub.
        if (! isset($reasons[$reason])) {
            wp_send_json_success(['sent' => false]);
        }

        $comment = isset($_POST['comment']) ? sanitize_textarea_field(wp_unslash($_POST['comment'])) : '';
        $comment = trim(mb_substr($comment, 0, self::MAX_COMMENT));

        wp_send_json_success(['sent' => $this->send($reason, $comment)]);
    }

    private function send(string $reason, string $comment): bool
    {
        $state     = TelemetryState::instance();
        $connected = $state->is_connected();

        $body = [
            'plugin'            => TelemetryState::PLUGIN_RTMKIT,
            'plugin_version'    => RTM_KIT_VERSION,
            'wp_version'        => get_bloginfo('version'),
            'elementor_version' => defined('ELEMENTOR_VERSION') ? ELEMENTOR_VERSION : null,
            'reason'            => $reason,
            'occurred_at'       => gmdate('c'),
        ];

        if ('' !== $comment) {
            $body['comment'] = $comment;
        }

        // Situs yang connect memang sudah dikenali lewat token-nya; menyertakan
        // domain membuat jawaban ini bisa ditindaklanjuti lewat akun yang sama.
        if ($connected) {
            $body['domain'] = $state->domain();
        }

        // Timeout pendek: pengguna sedang menunggu plugin-nya mati.
        $result = TelemetryClient::instance()->send(TelemetryClient::ENDPOINT_FEEDBACK, $body, $connected, 5);

        return TelemetryClient::RESULT_OK === $result['result'];
    }
}
