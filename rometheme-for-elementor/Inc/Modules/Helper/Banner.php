<?php

namespace RTMKit\Modules\Helper;

if (!defined('ABSPATH'))
    exit;

class Banner
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'register']);
        add_action('wp_ajax_rtmkit_get_banner', [$this, 'ajax_get_banner']);
    }

    public function get_active_banner()
    {
        // OPTIMIZATION: Cache API responses for 1 hour
        $cache_manager = \RTMKit\Modules\Helper\CacheManager::instance();
        
        return $cache_manager->get(
            \RTMKit\Modules\Helper\CacheManager::CACHE_BANNER,
            function () {
                $api_handler = \RTMKit\Modules\Helper\APIHandler::instance();
                return $api_handler->remote('/wp-json/rtm/v1/banner?active', [], null, false, true, 'GET');
            },
            \RTMKit\Modules\Helper\CacheManager::TTL_BANNER
        );
    }

    /**
     * Dipanggil saat admin_init. Cuma nyiapin placeholder + enqueue script,
     * TIDAK langsung manggil API supaya gak blocking page load.
     */
    public function register()
    {
        if (!current_user_can('manage_options') && !current_user_can('edit_posts')) {
            return;
        }

        if ($this->is_excluded_screen()) {
            return;
        }

        add_action('admin_notices', [$this, 'render_placeholder']);
        add_action('admin_head', [$this, 'enqueue_scripts']);
    }

    public function render_placeholder()
    {
        if ($this->is_excluded_screen()) {
            return;
        }

        echo '<div id="rtmkit-banner-placeholder"></div>';
    }

    /**
     * Handler AJAX: ambil data banner dari API dan return HTML siap pakai.
     */
    public function ajax_get_banner()
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');

        if (!current_user_can('manage_options') && !current_user_can('edit_posts')) {
            wp_send_json_error();
        }

        $banner = $this->get_active_banner();

        if (is_wp_error($banner)) {
            wp_send_json_success([
                'type' => 'error',
                'html' => '',
            ]);
        }

        if (!$banner) {
            wp_send_json_success([
                'type' => 'empty',
                'html' => '',
            ]);
        }

        $html = '';
        foreach ($banner as $value) {
            $html .= wp_kses_post($value['content_html']);
        }

        wp_send_json_success([
            'type' => 'banner',
            'html' => $html,
        ]);
    }

    private function render_error_notice()
    {
        ob_start();
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <strong>RTMKit:</strong>
                <?php esc_html_e('Unable to connect to the Rometheme API server. Some features like template library and updates may be temporarily unavailable. Please try again later.', 'rometheme-for-elementor'); ?>
            </p>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Cek apakah halaman saat ini harus di-skip (post/page editor).
     */
    private function is_excluded_screen(): bool
    {
        $screen = get_current_screen();

        return (
            $screen
            && isset($screen->base)
            && in_array($screen->base, ['post', 'edit'], true)
        );
    }

    public function enqueue_scripts()
    {
        $ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce('rtmkit_nonce');

        echo "<script>
            document.addEventListener('DOMContentLoaded', function () {
                const placeholder = document.getElementById('rtmkit-banner-placeholder');
                if (!placeholder) return;

                const formData = new FormData();
                formData.append('action', 'rtmkit_get_banner');
                formData.append('nonce', '{$nonce}');

                fetch('{$ajax_url}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData,
                })
                .then(function (res) { return res.json(); })
                .then(function (res) {
                    if (!res || !res.success || !res.data || !res.data.html) return;

                    placeholder.innerHTML = res.data.html;

                    const dismissBtn = placeholder.querySelector('.rkit-notice-banner .dismiss-banner');
                    if (dismissBtn) {
                        dismissBtn.addEventListener('click', function (e) {
                            e.preventDefault();
                            const wrapper = e.currentTarget.closest('.rkit-notice-banner');
                            if (wrapper) wrapper.style.display = 'none';
                        });
                    }
                })
                .catch(function () {
                    // diam-diam gagal, gak perlu ganggu admin dengan error JS
                });
            });
        </script>";
    }
}