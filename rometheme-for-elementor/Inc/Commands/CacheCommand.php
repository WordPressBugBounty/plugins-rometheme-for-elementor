<?php

namespace RTMKit\Commands;

if (!defined('ABSPATH')) exit;

/**
 * WP-CLI command untuk RTMKit cache diagnostics
 * 
 * Usage:
 *   wp rtmkit cache diagnostic
 *   wp rtmkit cache clear
 *   wp rtmkit cache status
 */
class CacheCommand
{
    /**
     * Run cache diagnostics
     * 
     * ## EXAMPLES
     *
     *     wp rtmkit cache diagnostic
     *
     * @when after_wp_load
     */
    public function diagnostic($args, $assoc_args)
    {
        if (!class_exists('WP_CLI')) {
            echo "WP-CLI not available\n";
            return;
        }

        try {
            $debugger = \RTMKit\Modules\Helper\CacheDebugger::instance();
            $report = $debugger->generate_report();

            // Pretty print the report
            $this->print_report($report);
            
            \WP_CLI::success('Diagnostic report generated');
        } catch (\Exception $e) {
            \WP_CLI::error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Clear all RTMKit cache
     * 
     * ## EXAMPLES
     *
     *     wp rtmkit cache clear
     *
     * @when after_wp_load
     */
    public function clear($args, $assoc_args)
    {
        if (!class_exists('WP_CLI')) {
            echo "WP-CLI not available\n";
            return;
        }

        try {
            $debugger = \RTMKit\Modules\Helper\CacheDebugger::instance();
            $result = $debugger->clear_all_cache();

            \WP_CLI::success($result['message']);
        } catch (\Exception $e) {
            \WP_CLI::error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Get cache status
     * 
     * ## EXAMPLES
     *
     *     wp rtmkit cache status
     *
     * @when after_wp_load
     */
    public function status($args, $assoc_args)
    {
        if (!class_exists('WP_CLI')) {
            echo "WP-CLI not available\n";
            return;
        }

        try {
            $cache_manager = \RTMKit\Modules\Helper\CacheManager::instance();
            $diagnostics = $cache_manager->get_cache_diagnostics();

            \WP_CLI::line('');
            \WP_CLI::line('=== RTMKit Cache Status ===');
            \WP_CLI::line('');
            
            \WP_CLI::line('Transient Storage:');
            if ($diagnostics['transient_storage']['type'] === 'External Object Cache (Redis/Memcached)') {
                \WP_CLI::warning('  Type: ' . $diagnostics['transient_storage']['type']);
            } else {
                \WP_CLI::line('  Type: ' . $diagnostics['transient_storage']['type']);
            }
            
            \WP_CLI::line('');
            \WP_CLI::line('Cache Count: ' . $diagnostics['rtm_transients_count']);
            
            \WP_CLI::line('');
            \WP_CLI::line('RTM Cache Keys:');
            foreach ($diagnostics['all_rtm_cache_keys'] as $key) {
                \WP_CLI::line('  - ' . $key);
            }

            \WP_CLI::line('');
            \WP_CLI::line('Storage Test:');
            $test = $diagnostics['sample_transient_test'];
            \WP_CLI::line('  Set: ' . $test['set']);
            \WP_CLI::line('  Get: ' . $test['get']);
            \WP_CLI::line('  Verified: ' . $test['verified']);

            \WP_CLI::line('');
            \WP_CLI::success('Cache status retrieved');
        } catch (\Exception $e) {
            \WP_CLI::error('Error: ' . $e->getMessage());
        }
    }

    /**
     * Pretty print diagnostic report
     */
    private function print_report($report)
    {
        if (!class_exists('WP_CLI')) {
            echo json_encode($report, JSON_PRETTY_PRINT);
            return;
        }

        \WP_CLI::line('');
        \WP_CLI::line('=== RTMKit Cache Diagnostic Report ===');
        \WP_CLI::line('Timestamp: ' . $report['timestamp']);
        \WP_CLI::line('WordPress: ' . $report['wordpress_version']);
        \WP_CLI::line('PHP: ' . $report['php_version']);
        \WP_CLI::line('');

        \WP_CLI::line('--- Transient Storage ---');
        $storage = $report['wp_using_transients'];
        \WP_CLI::line('Type: ' . $storage['type']);
        if (isset($storage['note'])) {
            \WP_CLI::line('Note: ' . $storage['note']);
        }
        \WP_CLI::line('');

        \WP_CLI::line('--- Cache Diagnostics ---');
        $diag = $report['cache_diagnostics'];
        \WP_CLI::line('Transient Count: ' . $diag['rtm_transients_count']);
        \WP_CLI::line('');

        \WP_CLI::line('Storage Test:');
        $test = $diag['sample_transient_test'];
        \WP_CLI::line('  Set: ' . $test['set']);
        \WP_CLI::line('  Get: ' . $test['get']);
        \WP_CLI::line('  Verified: ' . $test['verified']);
        \WP_CLI::line('');

        \WP_CLI::line('--- Cache Keys in Database ---');
        foreach ($diag['all_rtm_cache_keys'] as $key) {
            \WP_CLI::line('  ' . $key);
        }
        \WP_CLI::line('');

        \WP_CLI::line('--- Specific Cache Status ---');
        foreach ($report['specific_cache_status'] as $name => $status) {
            \WP_CLI::line($name . ':');
            \WP_CLI::line('  Exists: ' . $status['exists']);
            \WP_CLI::line('  Type: ' . $status['type']);
        }
        \WP_CLI::line('');

        \WP_CLI::line('--- Extension Loading Test ---');
        $ext_test = $report['extension_loading_test'];
        \WP_CLI::line('Extensions Found: ' . $ext_test['step_1_get_extensions']['count']);
        \WP_CLI::line('Cache Hit: ' . $ext_test['step_3_second_call']['same_as_first']);
    }
}

// Register WP-CLI command if WP-CLI is available
if (defined('WP_CLI') && WP_CLI) {
    \WP_CLI::add_command('rtmkit cache', 'RTMKit\Commands\CacheCommand');
}
