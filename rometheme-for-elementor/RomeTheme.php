<?php

/**
 * Plugin Name:       RTMKit Addons for Elementor
 * Description:      The best toolkit solution for Elementor. Enjoy advanced addons, theme builders, forms, icons, and ready-made templates to create stunning websites quickly and effortlessly.
 * Version:           2.0.0
 * Author:            Rometheme
 * Author URI: 	  	  https://rometheme.net/
 * License : 		  GPLv3 or later
 * Requires Plugins : elementor 
 * Elementor tested up to: 3.30.2
 * Elementor Pro tested up to: 3.30.0
 * Text Domain:      rometheme-for-elementor
 * The best toolkit solution for Elementor. Enjoy advanced addons, theme builders, forms, icons, and ready-made templates to create stunning websites quickly and effortlessly.
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly 
}
defined('RTM_KIT_VERSION') || define('RTM_KIT_VERSION', '2.0.0');
defined('RTM_KIT_DIR') || define('RTM_KIT_DIR', plugin_dir_path(__FILE__));
defined('RTM_KIT_URL') || define('RTM_KIT_URL', plugin_dir_url(__FILE__));
defined('RTM_KIT_FILE') || define('RTM_KIT_FILE', __FILE__);


// Load Composer autoloader
if (!file_exists(RTM_KIT_DIR . 'vendor/autoload.php')) {
    wp_die('Please run <code>composer install</code> in the plugin directory to install dependencies.');
}
require_once RTM_KIT_DIR . 'vendor/autoload.php';

/**
 * Initialize the plugin.
 */

if (class_exists('RTMKit\Core\Plugin')) {
    register_activation_hook(RTM_KIT_FILE, function () {
        \RTMKit\Core\Plugin::instance()->rtm_handle_install_upgrade();
    });
    \RTMKit\Core\Plugin::instance()->before_plugin_load();
}
add_action('init', function () {
    \RTMKit\Core\Plugin::instance()->pro_version_compatible_check();
}, 0);

add_action('init', function () {
    if (class_exists('RTMKit\Core\Plugin')) {
        (new RTMKit\Core\Plugin())->init();
        do_action('rtmkit_loaded');
    } else {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>RTMKit failed to load. Please reinstall the plugin.</p></div>';
        });
    }
});
