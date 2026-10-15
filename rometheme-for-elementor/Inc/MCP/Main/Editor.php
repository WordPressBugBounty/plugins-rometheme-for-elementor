<?php
namespace RTMKit\MCP\Main;

final class Editor
{

    public static function init(): void
    {
        add_action('elementor/editor/before_enqueue_scripts', [self::class, 'enqueue']);
    }

    public static function enqueue(): void
    {
        $file = plugin_dir_path(__DIR__) . 'build/angie.js';
        if (!file_exists($file)) {
            return;
        } // Fase 3 belum dibuild

        wp_enqueue_script('rtmkit-mcp-angie', plugins_url('build/angie.js', __DIR__), [], filemtime($file), true);
        wp_add_inline_script('rtmkit-mcp-angie', 'window.RTMKitMCP=' . wp_json_encode([
            'root' => esc_url_raw(rest_url('wp-abilities/v1/abilities/')),
            'nonce' => wp_create_nonce('wp_rest'),
            'allowWrite' => (bool) get_option(RTMKIT_OPT_WRITE),
        ]) . ';', 'before');
    }
}