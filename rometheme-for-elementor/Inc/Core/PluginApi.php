<?php

namespace RTMKit\Core;

if (! defined('ABSPATH')) exit;

class PluginApi
{
    /**
     * Get the singleton instance of the PluginApi class.
     *
     * @return PluginApi
     */
    public static function instance(): self
    {
        static $instance = null;

        if (null === $instance) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Initialize the plugin API.
     */
    public function init()
    {
        if (wp_doing_ajax()) {
            add_action('wp_ajax_get_sidebar_content', [$this, 'get_sidebar_content']);
            add_action('wp_ajax_get_content', [$this, 'get_content']);
            add_action('wp_ajax_set_global_site', [$this, 'set_global_site']);
            add_action('wp_ajax_rtmkit_cache_diagnostic', [$this, 'cache_diagnostic']);
            add_action('wp_ajax_rtmkit_cache_clear', [$this, 'cache_clear']);
        }
    }

    public function get_sidebar_content()
    {
        // Load the sidebar view file

        check_ajax_referer('rtmkit_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access Denied.');
            wp_die();
        }

        if (!file_exists(RTM_KIT_DIR . 'views/sidebar.php')) {
            wp_send_json_error('Sidebar view file not found.');
            return;
        }
        ob_start();
        require_once RTM_KIT_DIR . 'views/sidebar.php';
        $content = ob_get_clean();
        wp_send_json_success($content);
    }

    public function get_content()
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access Denied.');
            wp_die();
        }

        $path = isset($_POST['path'])
            ? sanitize_key(wp_unslash($_POST['path']))
            : '';

        if ('' === $path) {
            wp_send_json_error('Path not specified.');
            wp_die();
        }

        $menu = \RTMKit\Modules\Menu::instance()->get_menu_by_path($path);

        // Menu addon yang terkunci (lihat 'locked' di sidebar): dibuka lewat URL
        // langsung, tampilkan layar terkunci alih-alih halaman addon.
        if (is_array($menu) && !empty($menu['locked'])) {
            $locked_menu = $menu;
            ob_start();
            require RTM_KIT_DIR . 'views/locked-feature.php';
            wp_send_json_success(ob_get_clean());
            wp_die();
        }

        if (!is_array($menu) || empty($menu['render_view'])) {
            wp_send_json_error('View file not found for the specified path.');
            wp_die();
        }

        $render_view = $menu['render_view'];
        $is_callback = is_callable($render_view);

        if (!$is_callback && (!is_string($render_view) || !is_file($render_view))) {
            wp_send_json_error('View file not found for the specified path.');
            wp_die();
        }

        // $cache_path = $this->get_content_cache_path($path, $file);
        // if ($cache_path && file_exists($cache_path) && (time() - filemtime($cache_path) < 12 * HOUR_IN_SECONDS)) {
        //     $cached_content = file_get_contents($cache_path);
        //     if (false !== $cached_content) {
        //         wp_send_json_success($cached_content);
        //         wp_die();
        //     }
        // }

        // Item menu yang sedang dibuka, untuk view yang dipakai beberapa menu
        // sekaligus (mis. themebuilder.php dengan 'themebuilder_scope').
        $rtmkit_menu = $menu;
        ob_start();
        if ($is_callback) {
            $rendered_content = call_user_func($render_view);
            if (is_string($rendered_content)) {
                echo $rendered_content;
            }
        } else {
            require $render_view;
        }
        $content = ob_get_clean();

        wp_send_json_success($content);
    }

    private function get_content_cache_path(string $path, string $file): string
    {
        $uploads = wp_upload_dir();
        if (!empty($uploads['error']) || empty($uploads['basedir'])) {
            return '';
        }

        $cache_dir = trailingslashit($uploads['basedir']) . 'rtmkit-cache/content';
        return trailingslashit($cache_dir) . md5($path . '|' . $file) . '.html';
    }

    public function set_global_site()
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access Denied.');
            wp_die();
        }

        // $idKit = sanitize_text_field($_POST['idKit']);
        $idKit = isset($_POST['idKit'])
            ? sanitize_text_field(wp_unslash($_POST['idKit']))
            : '';

        $update = update_option('elementor_active_kit', $idKit);
        if ($update) {
            wp_send_json_success('Global Site Settings updated successfully.');
        } else {
            wp_send_json_error('No changes were made.');
        }
    }

    /**
     * AJAX endpoint for cache diagnostics
     */
    public function cache_diagnostic()
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Access Denied.']);
            wp_die();
        }

        try {
            $debugger = \RTMKit\Modules\Helper\CacheDebugger::instance();
            $report = $debugger->generate_report();

            wp_send_json_success([
                'status' => 'success',
                'report' => $report
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'Error generating diagnostic report',
                'error' => $e->getMessage()
            ]);
        }
        wp_die();
    }

    /**
     * AJAX endpoint to clear cache
     */
    public function cache_clear()
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Access Denied.']);
            wp_die();
        }

        try {
            $debugger = \RTMKit\Modules\Helper\CacheDebugger::instance();
            $result = $debugger->clear_all_cache();

            wp_send_json_success($result);
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => 'Error clearing cache',
                'error' => $e->getMessage()
            ]);
        }
        wp_die();
    }
}
