<?php

namespace RomethemeKit;

use Plugin_Upgrader;
use RomeTheme;
use RomethemePro;
use stdClass;

class Update
{

    private $plugins;

    public function __construct()
    {
        $this->plugins = [
            'rtmkit' => [
                'file' => 'rometheme-for-elementor/RomeTheme.php',
                'class' => 'RomeTheme'
            ],
            'rtmform' => [
                'file' => 'romethemeform/rometheme-form.php',
                'class' => 'RomeTheme'
            ],
            'rtmkitpro' => [
                'file' => 'romethemekit-pro/RomeTheme_pro.php',
                'class' => 'RomethemePro'
            ]
        ];
        add_action('admin_enqueue_scripts', [$this, 'register_script']);
        add_action('wp_ajax_update_plugin', [$this, 'update_plugin']);
        add_action('wp_ajax_rollback_plugin', [$this, 'rollback_plugin']);
    }

    function register_script()
    {
        $screen = get_current_screen();
        $nonce = wp_create_nonce('rtmkit_update_nonce');

        if (str_contains($screen->id, 'rtmkit')) {
            wp_enqueue_script('update.js', \RomeTheme::module_url() . 'updates/scripts/update.js', ['jquery'], \RomeTheme::rt_version());
            wp_localize_script('update.js', 'rtmkit_update', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => $nonce
            ]);
        }
    }

    function update_plugin()
    {

        if (!current_user_can('manage_options')) {
            wp_die();
        }

        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rtmkit_update_nonce')) {
            wp_die();
        }

        $pluginInfo = $this->get_plugin_info($_POST['plugin']);

        $downloadURL = $pluginInfo->download_link;

        $file = $this->plugins[$_POST['plugin']]['file'];

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

        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'rtmkit_update_nonce')) {
            wp_die();
        }

        $pluginInfo = $this->get_plugin_info($_POST['plugin']);

        $file = $this->plugins[$_POST['plugin']]['file'];
        $versionLink = $pluginInfo->versions[$_POST['version']];

        $result = $this->install_plugin($file, $versionLink);

        if ($result) {
            wp_send_json_success();
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


    function get_plugin_info($plugin)
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // ini WAJIB

        $pluginData = get_plugin_data(WP_PLUGIN_DIR . '/' . $this->plugins[$plugin]['file']);

        $pluginInfo = ($plugin === 'rtmkitpro') ? self::get_pluginpro_info() : plugins_api('plugin_information', ['slug' => $pluginData['TextDomain']]);

        return $pluginInfo;
    }

    static function get_pluginpro_info()
    {
        if (!class_exists('RTMKitPro\Modules\Licenses\LicenseApi')) {
            return null;
        }

        $license = get_option('rtmpro-license-key');
        $activation = get_option('rtmpro-license-activation');

        $endpoint =  '/wp-json/lmfwc/v2/licenses/' . $license;

        $json = \RTMKitPro\Modules\Licenses\LicenseApi::instance()->_request($endpoint);

        $version = json_decode($json['data']['product']['version'], TRUE);
        if (!isset($version['current'], $version['versions'])) return null;

        $key = $license;
        $token = $activation['token'] ?? '';

        $versions = [];
        foreach ((array)$version['versions'] as $v => $link) {
            $versions[$v] = $link . '&key=' . $key . '&token=' . $token;
        }

        $info = new stdClass();
        $info->version = $version['current'];
        $info->versions = $versions;
        $info->download_link = $versions[$version['current']];


        return $info;
    }

    static function update_is_available(self $self = new self()): bool
    {

        foreach ($self->plugins as $plugin => $data) {
            if (file_exists(WP_PLUGIN_DIR . '/' . $data['file'])) {
                $pluginInfo = $self->get_plugin_info($plugin);
                $pluginData = get_plugin_data(WP_PLUGIN_DIR . '/' . $data['file']);
                $installedVersion = $pluginData['Version'];
                $remoteVersion = $pluginInfo->version ?? null;
                if (!empty($installedVersion)) {
                    $compare = version_compare($installedVersion, $remoteVersion, '<');

                    if ($compare) {
                        return true;
                    }
                    
                    return '';
                }
            }
        }

        return false;
    }
}
