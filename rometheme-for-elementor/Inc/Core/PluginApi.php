<?php

namespace RTMKit\Core;

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
        $path = sanitize_text_field($_POST['path']);
        $menus = \RTMKit\Modules\Menu::instance()->get_menu_by_path($_POST['path']);

        if (!isset($_POST['path'])) {
            wp_send_json_error('Path not specified.');
            return;
        }

        if (isset($menus['render_view']) && file_exists($menus['render_view'])) {
            $file = $menus['render_view'];
        } else {
            wp_send_json_error('View file not found for the specified path.');
            return;
        }
        ob_start();
        require_once $file;
        $content = ob_get_clean();
        wp_send_json_success($content);
    }

    public function set_global_site()
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access Denied.');
            wp_die();
        }

        $idKit = sanitize_text_field($_POST['idKit']);
        $update = update_option('elementor_active_kit', $idKit);
        if ($update) {
            wp_send_json_success('Global Site Settings updated successfully.');
        } else {
            wp_send_json_error('No changes were made.');
        }
    }
}
