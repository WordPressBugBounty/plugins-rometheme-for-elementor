<?php
/**
 * Plugin Name: RTMKit MCP
 * Description: Mengekspos widget RTMKit sebagai ability WordPress/MCP.
 * Version: 0.1.0
 * Requires at least: 6.9
 * Requires PHP: 8.0
 */
namespace RTMKit\MCP;

use RTMKit\MCP\Main\Abilities;
use RTMKit\MCP\Main\Editor;
use RTMKit\MCP\Main\Settings;
use RTMKit\MCP\Main\Writer;

class RtmkitMcp
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
        \WP\MCP\Core\McpAdapter::instance();
        Settings::init();
        add_filter('mcp_adapter_default_server_config', [Abilities::class, 'expose_as_tools']);        // Ability hanya didaftarkan jika Abilities API tersedia.
        if (function_exists('wp_register_ability')) {
            add_action('wp_abilities_api_categories_init', [Abilities::class, 'register_category']);
            add_action('wp_abilities_api_init', static function () {
                Abilities::register_read();
                if (get_option(RTMKIT_OPT_WRITE)) {
                    Writer::register();   // sebelumnya: Abilities::register_write()
                }
            });
        }

        // Editor hook hanya relevan jika Elementor aktif.
        add_action('elementor/loaded', [Editor::class, 'init']);

        // Notice dependensi.
        add_action('admin_notices', static function () {
            if (!current_user_can('manage_options')) {
                return;
            }
            $missing = [];
            if (!function_exists('wp_register_ability')) {
                $missing[] = 'WordPress 6.9+ (Abilities API)';
            }
            if (!did_action('elementor/loaded')) {
                $missing[] = 'Elementor';
            }
            if (!did_action('rtmkit_loaded')) {
                $missing[] = 'RTMKit core';
            }
            if ($missing) {
                printf('<div class="notice notice-warning"><p>RTMKit MCP membutuhkan: %s.</p></div>', esc_html(implode(', ', $missing)));
            }
        });

        add_action('elementor/editor/before_enqueue_scripts', function () {
            wp_enqueue_script('rtmkit-mcp-angie', plugins_url('build/angie.js', __FILE__), [], '0.1.0', true);
            wp_add_inline_script('rtmkit-mcp-angie', 'window.RTMKitMCP=' . wp_json_encode([
                'root' => esc_url_raw(rest_url('wp-abilities/v1/abilities/')),
                'nonce' => wp_create_nonce('wp_rest'),
            ]) . ';', 'before');
        });
    }
}
