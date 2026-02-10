<?php

namespace RTMKit\Modules\Update;

use Plugin_Upgrader;

class UpdateAPI
{
    protected static $instance;

    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        if (wp_doing_ajax()) {
            add_action('wp_ajax_update_plugin', [$this, 'update_plugin']);
            add_action('wp_ajax_rollback_plugin', [$this, 'rollback_plugin']);
            add_action('wp_ajax_get_update_content', [$this, 'get_update_content']);
        }
    }

    function update_plugin()
    {

        if (!current_user_can('manage_options')) {
            wp_die();
        }

        check_ajax_referer('rtmkit_nonce', 'nonce');

        $pluginInfo = \RTMKit\Modules\Update\UpdateModule::instance()->get_plugin_info($_POST['plugin']);

        $downloadURL = $pluginInfo->download_link;

        $file = \RTMKit\Modules\Update\UpdateModule::instance()->get_plugin_file($_POST['plugin']);

        $result = $this->install_plugin($file, $downloadURL);

        if ($result) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error([$result, $file, $downloadURL]);
        }
    }

    function rollback_plugin()
    {
        if (!current_user_can('manage_options')) {
            wp_die();
        }

        check_ajax_referer('rtmkit_nonce', 'nonce');

        $pluginInfo = \RTMKit\Modules\Update\UpdateModule::instance()->get_plugin_info($_POST['plugin']);

        $file = \RTMKit\Modules\Update\UpdateModule::instance()->get_plugin_file($_POST['plugin']);
        $versionLink = $pluginInfo->versions[$_POST['version']];

        $result = $this->install_plugin($file, $versionLink);

        if ($result) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error();
        }
    }

    function install_plugin($plugin_file, $plugin_url)
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';

        list($plugin_folder, $plugin_main_file) = explode('/', $plugin_file, 2);

        $plugin_path = WP_PLUGIN_DIR . '/' . $plugin_folder;

        if (is_plugin_active($plugin_file)) {
            deactivate_plugins($plugin_file);
        }

        if (file_exists(WP_PLUGIN_DIR . '/' . $plugin_file)) {
            delete_plugins([$plugin_file]);
        }

        if (is_dir($plugin_path)) {
            global $wp_filesystem;
            if (empty($wp_filesystem)) {
                WP_Filesystem();
            }
            $wp_filesystem->delete($plugin_path, true);
        }

        // Tanpa ob_*, tanpa class global, pakai anonymous class
        $skin = new class extends \WP_Upgrader_Skin {
            public function feedback($string, ...$args) {}
            public function header() {}
            public function footer() {}
            public function error($errors) {}
            public function before() {}
            public function after() {}
        };

        $upgrader = new Plugin_Upgrader($skin);
        $result = $upgrader->install($plugin_url);

        if (is_wp_error($result)) {
            return $result->get_error_message();
        }

        $activate = activate_plugin($plugin_file);
        if (is_wp_error($activate)) {
            return $activate->get_error_message();
        }

        return true;
    }

    public function get_update_content()
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Access Denied');
        }

        ob_start();
        require RTM_KIT_DIR . 'views/update_content.php';
        $content = ob_get_clean();
        wp_send_json_success($content);
    }
}
