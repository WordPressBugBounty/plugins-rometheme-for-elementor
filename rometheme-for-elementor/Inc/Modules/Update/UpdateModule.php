<?php

namespace RTMKit\Modules\Update;

use stdClass;

class UpdateModule
{
    protected static $instance;
    private $plugins = [
        'rtmkit' => [
            'file' => 'rometheme-for-elementor/RomeTheme.php',
            'class' => 'RomeTheme',
            'min_version' => '2.0.0'
        ],
        'rtmform' => [
            'file' => 'romethemeform/rometheme-form.php',
            'class' => 'RomeTheme',
            'min_version' => '1.2.5'
        ],
        'rtmkitpro' => [
            'file' => 'romethemekit-pro/RomeTheme_pro.php',
            'class' => 'RomethemePro',
            'min_version' => '1.2.0'
        ]
    ];
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        \RTMKit\Modules\Update\UpdateAPI::instance()->init();
    }

    public function get_plugin_info($plugin)
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // ini WAJIB

        $pluginData = get_plugin_data(WP_PLUGIN_DIR . '/' . $this->plugins[$plugin]['file']);

        $pluginInfo = ($plugin === 'rtmkitpro') ? $this->get_pluginpro_info() : plugins_api('plugin_information', ['slug' => $pluginData['TextDomain']]);

        return $pluginInfo;
    }

    public function get_pluginpro_info()
    {
        // if (!class_exists('RTMKitPro\Modules\Licenses\LicenseApi') || !\RTMKitPro\Modules\Licenses\LicenseStorage::instance()->isLicenseActive()) {
        //     return null;
        // }

        $license = get_option('rtmpro-license-key' , false);
        $activation = get_option('rtmpro-license-activation ', false);

        if($license == false || $activation == false) {
            return null;
        }

        $endpoint =  '/wp-json/rtm-core/auth/v1/licenses/' . $license;
        $apiHandler = \RTMKit\Modules\Helper\APIHandler::instance();
        $json = $apiHandler->remote($endpoint, [], null, true, true);

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

    public function get_plugin_file($plugin)
    {
        return $this->plugins[$plugin]['file'];
    }

    public function update_is_available(): bool
    {

        foreach ($this->plugins as $plugin => $data) {
            if (file_exists(WP_PLUGIN_DIR . '/' . $data['file'])) {
                $pluginInfo = $this->get_plugin_info($plugin);
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

    public function get_plugins()
    {
        return $this->plugins;
    }
}
