<?php

/**
 * Plugin Name:       RTMKit Addons for Elementor
 * Description:      The best toolkit solution for Elementor. Enjoy advanced addons, theme builders, forms, icons, and ready-made templates to create stunning websites quickly and effortlessly.
 * Version:           2.1.9
 * Author:            Rometheme
 * Author URI: 	  	  https://rometheme.net/
 * License: 		   GPLv2 or later
 * License URI: 		  https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins: elementor 
 * Elementor tested up to: 3.35.0
 * Elementor Pro tested up to: 3.35.0
 * Text Domain:      rometheme-for-elementor
 * The best toolkit solution for Elementor. Enjoy advanced addons, theme builders, forms, icons, and ready-made templates to create stunning websites quickly and effortlessly.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly 
}
defined('RTM_KIT_VERSION') || define('RTM_KIT_VERSION', '2.1.9');
defined('RTM_KIT_DIR') || define('RTM_KIT_DIR', plugin_dir_path(__FILE__));
defined('RTM_KIT_URL') || define('RTM_KIT_URL', plugin_dir_url(__FILE__));
defined('RTM_KIT_FILE') || define('RTM_KIT_FILE', __FILE__);
defined('RTM_KIT_PRO_MIN_VERSION') || define('RTM_KIT_PRO_MIN_VERSION', '1.2.0');
defined('RTMHUB_API_URL') || define('RTMHUB_API_URL', 'https://api.rometheme.net/');
defined('RTMHUB_MY_URL') || define('RTMHUB_MY_URL', 'https://my.rometheme.net/');
defined('RTMHUB_LOGIN_URL') || define('RTMHUB_LOGIN_URL', 'https://login.rometheme.net/');


// Load Composer autoloader
if (!file_exists(RTM_KIT_DIR . 'vendor/autoload.php')) {
    wp_die('Please run <code>composer install</code> in the plugin directory to install dependencies.');
}
require_once RTM_KIT_DIR . 'vendor/autoload.php';

/**
 * Initialize the plugin.
 */

add_action('plugins_loaded', function () {
    \RTMKit\Core\Plugin::instance()->pro_version_compatible_check();
}, 1);

require_once RTM_KIT_DIR . 'Inc/Core/Plugin.php';
register_activation_hook(RTM_KIT_FILE, function () {
    \RTMKit\Core\Plugin::instance()->rtm_handle_install_upgrade();
    \RTMKit\Modules\Telemetry\TelemetryModule::on_activate();
});

// WP-Cron tidak akan memanggil plugin ini lagi setelah dinonaktifkan, jadi
// event terakhirnya harus dikirim di request yang sama.
register_deactivation_hook(RTM_KIT_FILE, function () {
    \RTMKit\Modules\Telemetry\TelemetryModule::on_deactivate();
});

if (!function_exists('rtmkit_upgrade_url')) {
    /**
     * URL tombol "Upgrade to Pro", lewat pencatat klik di RTMHub.
     *
     * @param string $placement Nama tombol, mis. `templatekit.import_modal`.
     * @param string $to        Halaman tujuan: `rtmkit`, `store`, atau `main`.
     */
    function rtmkit_upgrade_url(string $placement, string $to = 'rtmkit'): string
    {
        return \RTMKit\Modules\Telemetry\UpgradeLink::url($placement, $to);
    }
}
// \RTMKit\Core\Plugin::instance()->before_plugin_load();


add_action('init', function () {
    if (class_exists('RTMKit\Core\Plugin')) {
        (\RTMKit\Core\Plugin::instance())->init();
        do_action('rtmkit_loaded');
    } else {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>RTMKit failed to load. Please reinstall the plugin.</p></div>';
        });
    }
});
