<?php

namespace RTMKit\Modules\Helper;

if (!defined('ABSPATH')) exit;

/**
 * Cache Debugger - Diagnostic tool untuk troubleshoot cache issues
 */
class CacheDebugger
{
    private static $instance = null;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Generate full diagnostic report
     */
    public function generate_report()
    {
        $cache_manager = CacheManager::instance();
        
        $report = [
            'timestamp' => current_time('mysql'),
            'wordpress_version' => get_bloginfo('version'),
            'php_version' => phpversion(),
            'wp_using_transients' => $this->detect_transient_storage(),
            'cache_diagnostics' => $cache_manager->get_cache_diagnostics(),
            'specific_cache_status' => [
                'extensions' => $cache_manager->get_cache_status('rtm_cache_extensions'),
                'modules' => $cache_manager->get_cache_status('rtm_cache_modules'),
                'pro_modules' => $cache_manager->get_cache_status('rtm_cache_pro_modules'),
            ],
            'extension_loading_test' => $this->test_extension_loading(),
            'manual_cache_test' => $this->manual_cache_test(),
        ];

        return $report;
    }

    /**
     * Detect how WordPress stores transients
     */
    private function detect_transient_storage()
    {
        global $wpdb;

        // Check if using object cache (Redis, Memcached, etc)
        if (wp_using_ext_object_cache()) {
            return [
                'type' => 'External Object Cache (Redis/Memcached)',
                'implementation' => $GLOBALS['wp_object_cache']->cache_hits ?? 'Unknown',
                'note' => 'Transients stored in external cache, not in wp_options'
            ];
        }

        // Default: stored in wp_options
        return [
            'type' => 'Database (wp_options table)',
            'note' => 'Transients stored as options with autoload=no'
        ];
    }

    /**
     * Test actual extension loading with cache
     */
    private function test_extension_loading()
    {
        $cache_manager = CacheManager::instance();
        
        // Enable debug temporarily
        $cache_manager::enable_debug();

        $result = [
            'step_1_get_extensions' => 'Running...',
            'step_2_check_cache' => 'Running...',
            'step_3_second_call' => 'Running...',
        ];

        // First call - should cache
        $extensions = $cache_manager->get_extensions_cached();
        $result['step_1_get_extensions'] = [
            'count' => count($extensions),
            'first_item' => $extensions[0] ?? 'N/A',
            'success' => count($extensions) > 0 ? 'YES' : 'NO'
        ];

        // Check if actually cached
        $cache_status = $cache_manager->get_cache_status('rtm_cache_extensions');
        $result['step_2_check_cache'] = $cache_status;

        // Second call - should hit cache
        $extensions_2 = $cache_manager->get_extensions_cached();
        $result['step_3_second_call'] = [
            'count' => count($extensions_2),
            'same_as_first' => $extensions === $extensions_2 ? 'YES' : 'NO'
        ];

        $cache_manager::disable_debug();

        return $result;
    }

    /**
     * Manual test - directly set and get transient
     */
    private function manual_cache_test()
    {
        $test_key = 'rtm_debug_test_' . time();
        $test_value = [
            'debug' => true,
            'extensions' => ['ext1', 'ext2', 'ext3'],
            'timestamp' => current_time('mysql')
        ];

        // Step 1: Set transient
        $set_result = set_transient($test_key, $test_value, 1 * HOUR_IN_SECONDS);
        
        // Step 2: Get immediately
        $get_result = get_transient($test_key);

        // Step 3: Check in database
        global $wpdb;
        $db_check = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
                '_transient_' . $test_key
            )
        );

        // Step 4: Clean up
        delete_transient($test_key);

        return [
            'test_key' => $test_key,
            'set_result' => $set_result ? 'SUCCESS' : 'FAILED',
            'get_result' => $get_result !== false ? 'SUCCESS' : 'FAILED',
            'value_matches' => $get_result === $test_value ? 'YES' : 'NO',
            'found_in_database' => $db_check ? 'YES' : 'NO',
            'db_entry' => $db_check ? [
                'option_name' => $db_check->option_name,
                'option_value_length' => strlen($db_check->option_value)
            ] : 'N/A'
        ];
    }

    /**
     * Output report as formatted HTML for admin
     */
    public function display_report_html()
    {
        $report = $this->generate_report();
        
        echo '<div style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ddd; font-family: monospace;">';
        echo '<h2>RTMKit Cache Diagnostic Report</h2>';
        echo '<pre>' . wp_json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</pre>';
        echo '</div>';
    }

    /**
     * Output report as JSON
     */
    public function get_report_json()
    {
        return wp_json_encode($this->generate_report(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Clear and optimize cache (tombol Clear Cache di Welcome & WP-CLI):
     * hapus cache RTMKit, buang transient kedaluwarsa, minta Elementor membuat
     * ulang file CSS-nya, lalu bangun ulang cache RTMKit.
     */
    public function clear_all_cache()
    {
        $cache = CacheManager::instance();
        $cache->clear_all();

        // Transient kedaluwarsa tidak dihapus WordPress sampai dibaca lagi.
        if (function_exists('delete_expired_transients')) {
            delete_expired_transients(true);
        }

        // File CSS Elementor (per halaman & global) dibuat ulang saat dibuka berikutnya.
        if (class_exists('\Elementor\Plugin') && isset(\Elementor\Plugin::$instance->files_manager)) {
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        }

        $cache->warm();

        return [
            'status' => 'SUCCESS',
            'message' => __('Cache cleared and optimized.', 'rometheme-for-elementor'),
            'timestamp' => current_time('mysql')
        ];
    }
}
