<?php

namespace RTMKit\Modules\Addons;

use RTMKit\Modules\Helper\APIHandlerV2 as APIHandler;

class AddonsApi
{
    private static $instance;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        if (wp_doing_ajax()) {
            add_action('wp_ajax_rtmkit_addons_install', [$this, 'install_addon']);
            add_action('wp_ajax_rtmkit_addon_delete', [$this, 'delete_addon']);
            add_action('wp_ajax_rtmkit_addon_activate', [$this, 'activate_addon']);
            add_action('wp_ajax_sync_addons', [$this, 'sync_addons']);
        }
    }

    public function install_addon()
    {
        if (!current_user_can('install_plugins')) {
            wp_send_json_error([
                'message' => __('You do not have permission to install plugins.', 'rtmkit'),
            ], 403);
        }

        check_ajax_referer('rtmkit_nonce', 'nonce');

        $addonSlug = sanitize_text_field($_POST['addon'] ?? '');

        $this->download_and_activate_addons($addonSlug);
    }

    private function find_plugin_file(string $slug): ?string
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $plugins = get_plugins('/' . $slug);

        foreach ($plugins as $file => $headers) {
            return $slug . '/' . $file;
        }

        return null;
    }

    public function download_and_activate_addons(string $addonSlug, $version = null)
    {
        if (empty($addonSlug)) {
            wp_send_json_error([
                'message' => __('Addon slug is required.', 'rtmkit'),
            ], 400);
        }
        $tempFile = null;

        $versionToDowload = ($version == null) ? 'latest' : $version;
        try {

            // Create temporary file
            $tempFile = wp_tempnam($addonSlug . '.zip');

            if (!$tempFile) {
                throw new \Exception(__('Unable to create temporary file.', 'rtmkit'));
            }

            // Download addon package
            $response = \RTMKit\Modules\Helper\APIHandlerV2::instance()->remote(
                "/repos/download/{$addonSlug}/" . $versionToDowload,
                [
                    'stream'   => true,
                    'filename' => $tempFile,
                    'timeout'  => 300,
                ]
            );

            if (is_wp_error($response)) {
                throw new \Exception($response->get_error_message());
            }

            $statusCode = wp_remote_retrieve_response_code($response);

            if ($statusCode !== 200) {
                throw new \Exception(sprintf(
                    __('Download failed (HTTP %d).', 'rtmkit'),
                    $statusCode
                ));
            }

            if (!file_exists($tempFile) || filesize($tempFile) === 0) {
                throw new \Exception(__('Downloaded package is empty.', 'rtmkit'));
            }

            // Load WP installer
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

            WP_Filesystem();

            $skin = new \Automatic_Upgrader_Skin();
            $upgrader = new \Plugin_Upgrader($skin);

            $installed = $upgrader->install($tempFile);

            if (is_wp_error($installed)) {
                throw new \Exception($installed->get_error_message());
            }

            if (!$installed) {

                $messages = method_exists($skin, 'get_upgrade_messages')
                    ? implode("\n", $skin->get_upgrade_messages())
                    : __('Unknown installation error.', 'rtmkit');

                throw new \Exception($messages);
            }

            // Cari plugin utama hasil install
            $pluginFile = $this->find_plugin_file($addonSlug);

            wp_send_json_success([
                'message' => __('Addon installed successfully.', 'rtmkit'),
                'plugin'  => $pluginFile,
            ]);
        } catch (\Exception $e) {

            wp_send_json_error([
                'message' => $e->getMessage(),
            ], 500);
        } finally {

            if ($tempFile && file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
    }

    public function activate_addon()
    {
        if (!current_user_can('install_plugins')) {
            wp_send_json_error([
                'message' => __('You do not have permission to install plugins.', 'rtmkit'),
            ], 403);
        }

        check_ajax_referer('rtmkit_nonce', 'nonce');

        $addon = sanitize_text_field($_POST['addon'] ?? '');

        try {
            $activate = activate_plugin($addon);
            wp_send_json_success();
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function delete_addon()
    {
        if (!current_user_can('install_plugins')) {
            wp_send_json_error([
                'message' => __('You do not have permission to install plugins.', 'rtmkit'),
            ], 403);
        }

        check_ajax_referer('rtmkit_nonce', 'nonce');

        $addon = sanitize_text_field($_POST['addon'] ?? '');

        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        include_once ABSPATH . 'wp-admin/includes/file.php';
        include_once ABSPATH . 'wp-admin/includes/misc.php';

        $plugin = $addon . '/' . $addon . '.php';

        // Deactivate jika masih aktif
        if (is_plugin_active($plugin)) {
            deactivate_plugins($plugin);
        }

        // Inisialisasi Filesystem
        WP_Filesystem();

        // Hapus plugin
        $result = delete_plugins([$plugin]);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        wp_send_json_success();
    }

    public function sync_addons()
    {

        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => __('You do not have permission', 'rtmkit'),
            ], 403);
        }

        check_ajax_referer('rtmkit_nonce', 'nonce');
        $addons = APIHandler::instance()->remote('addons/list', [], null, false, true);

        error_log('Sync Addons Response: ' . print_r($addons, true));

        delete_transient('rtmkit_addons_list');
        $transient = set_transient('rtmkit_addons_list', $addons, DAY_IN_SECONDS);

        if ($transient) {
            wp_send_json_success($addons);
        }
        wp_send_json_error();
    }
}
