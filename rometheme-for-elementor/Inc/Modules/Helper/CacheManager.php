<?php

namespace RTMKit\Modules\Helper;

if (!defined('ABSPATH')) exit;

/**
 * Cache Manager for RTMKit
 * Centralized caching system using WordPress transients
 */
class CacheManager
{
    private static $instance = null;
    private static $debug_mode = false;
    
    // Cache keys
    const CACHE_MODULES = 'rtm_cache_modules';
    const CACHE_MODULES_WITH_EXT = 'rtm_cache_modules_with_ext';
    const CACHE_PRO_MODULES = 'rtm_cache_pro_modules';
    const CACHE_EXTENSIONS = 'rtm_cache_extensions';
    const CACHE_WIDGET_DATA = 'rtm_cache_widget_data_';
    const CACHE_BANNER = 'rtm_cache_banner';
    const CACHE_ASSET_MANIFEST = 'rtm_cache_asset_manifest';
    
    // Default TTLs (in seconds)
    const TTL_METADATA = 15 * MINUTE_IN_SECONDS;      // 15 minutes
    const TTL_EXTENSIONS = 15 * MINUTE_IN_SECONDS;    // 15 minutes
    const TTL_BANNER = 1 * HOUR_IN_SECONDS;           // 1 hour
    const TTL_ASSETS = 12 * HOUR_IN_SECONDS;          // 12 hours

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Enable debug mode for cache logging
     */
    public static function enable_debug()
    {
        self::$debug_mode = true;
    }

    /**
     * Disable debug mode
     */
    public static function disable_debug()
    {
        self::$debug_mode = false;
    }

    /**
     * Log cache activity
     */
    private function log_cache_activity($action, $key, $details = '')
    {
        if (!self::$debug_mode) {
            return;
        }

        $message = "[RTMKit Cache] $action - Key: $key";
        if ($details) {
            $message .= " - $details";
        }
        error_log($message);
    }

    /**
     * Get cached value or execute callback
     */
    public function get($key, $callback, $ttl = 0)
    {
        // Try to get from cache first
        $cached = get_transient($key);
        
        if ($cached !== false) {
            $this->log_cache_activity('CACHE HIT', $key, 'TTL: ' . $ttl);
            return $cached;
        }

        $this->log_cache_activity('CACHE MISS', $key, 'Executing callback');

        // Execute callback if not cached
        $value = call_user_func($callback);

        // Store in cache - allow empty arrays and other falsy values except null
        if ($value !== null) {
            $result = set_transient($key, $value, $ttl);
            $status = $result ? 'SET SUCCESS' : 'SET FAILED';
            $this->log_cache_activity($status, $key, 'TTL: ' . $ttl . ' seconds');
        } else {
            $this->log_cache_activity('SKIP CACHE', $key, 'Value is null');
        }

        return $value;
    }

    /**
     * Set cache value
     */
    public function set($key, $value, $ttl = 0)
    {
        if ($value !== null) {
            $result = set_transient($key, $value, $ttl);
            $status = $result ? 'SET SUCCESS' : 'SET FAILED';
            $this->log_cache_activity($status, $key, 'TTL: ' . $ttl . ' seconds');
            return $result;
        }
        $this->log_cache_activity('SKIP CACHE', $key, 'Value is null');
        return false;
    }

    /**
     * Delete cache by key
     */
    public function delete($key)
    {
        delete_transient($key);
    }

    /**
     * Clear all RTMKit cache
     */
    public function clear_all()
    {
        delete_transient(self::CACHE_MODULES);
        delete_transient(self::CACHE_MODULES_WITH_EXT);
        delete_transient(self::CACHE_PRO_MODULES);
        delete_transient(self::CACHE_EXTENSIONS);
        delete_transient(self::CACHE_BANNER);
        delete_transient(self::CACHE_ASSET_MANIFEST);
        
        // Clear all widget data caches
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '%' . self::CACHE_WIDGET_DATA . '%'
            )
        );
    }

    /**
     * Load modules from file with cache
     */
    public function get_modules_cached()
    {
        return $this->get(
            self::CACHE_MODULES,
            function () {
                $modules = [];
                $modules_file = RTM_KIT_DIR . 'metadata/modules.json';
                $extensions_file = RTM_KIT_DIR . 'metadata/extensions.json';

                if (file_exists($modules_file) && file_exists($extensions_file)) {
                    $modules_json = file_get_contents($modules_file);
                    $extensions_json = file_get_contents($extensions_file);
                    $main_modules = json_decode($modules_json, true);
                    $extensions_modules = json_decode($extensions_json, true);
                    $modules = array_merge($main_modules, $extensions_modules);
                }

                return $modules;
            },
            self::TTL_METADATA
        );
    }

    /**
     * Load extensions from file with cache
     */
    public function get_extensions_cached()
    {
        return $this->get(
            self::CACHE_EXTENSIONS,
            function () {
                $extensionsFileJson = file_get_contents(RTM_KIT_DIR . '/metadata/extensions.json');
                $extensionsProFileJson = file_exists(RTM_KIT_DIR . '/metadata/extensions-pro.json')
                    ? file_get_contents(RTM_KIT_DIR . '/metadata/extensions-pro.json')
                    : '{}';

                $extensions = json_decode($extensionsFileJson, true);
                $extensionsPro = json_decode($extensionsProFileJson, true);

                // Merge arrays without overwriting duplicate keys
                $merged = array_merge($extensions, array_diff_key($extensionsPro, $extensions));

                // Sort by name
                uasort($merged, function ($a, $b) {
                    return strcasecmp($a['name'], $b['name']);
                });

                return $merged;
            },
            self::TTL_EXTENSIONS
        );
    }

    /**
     * Load pro modules from file with cache
     */
    public function get_pro_modules_cached()
    {
        return $this->get(
            self::CACHE_PRO_MODULES,
            function () {
                $pro_modules_file = RTM_KIT_DIR . 'metadata/pro_modules.json';
                if (file_exists($pro_modules_file)) {
                    $pro_modules_json = file_get_contents($pro_modules_file);
                    return json_decode($pro_modules_json, true);
                }
                return [];
            },
            self::TTL_METADATA
        );
    }

    /**
     * Load widget data with cache
     */
    public function get_widget_data_cached($type = 'free')
    {
        $cache_key = self::CACHE_WIDGET_DATA . $type;

        return $this->get(
            $cache_key,
            function () use ($type) {
                $json_path = ($type === 'pro')
                    ? RTM_KIT_DIR . 'metadata/rtmwp.json'
                    : RTM_KIT_DIR . 'metadata/widgets.json';

                if (!file_exists($json_path)) {
                    return [];
                }

                return json_decode(file_get_contents($json_path), true);
            },
            self::TTL_METADATA
        );
    }

    /**
     * Get asset manifest with cache
     */
    public function get_asset_manifest_cached($directory)
    {
        $cache_key = self::CACHE_ASSET_MANIFEST . '_' . md5($directory);

        return $this->get(
            $cache_key,
            function () use ($directory) {
                if (!is_dir($directory)) {
                    return [];
                }

                $files = [];
                $items = @scandir($directory);

                if (is_array($items)) {
                    foreach ($items as $item) {
                        if ($item !== '.' && $item !== '..') {
                            $files[] = $item;
                        }
                    }
                }

                return $files;
            },
            self::TTL_ASSETS
        );
    }

    /**
     * Get diagnostic info about cache storage
     */
    public function get_cache_diagnostics()
    {
        global $wpdb;

        $diagnostics = [
            'transient_storage' => $this->check_transient_storage(),
            'rtm_transients_count' => $this->count_rtm_transients(),
            'all_rtm_cache_keys' => $this->get_all_rtm_cache_keys(),
            'sample_transient_test' => $this->test_set_transient(),
            'debug_mode' => self::$debug_mode ? 'ENABLED' : 'DISABLED'
        ];

        return $diagnostics;
    }

    /**
     * Check if transient storage is working
     */
    private function check_transient_storage()
    {
        $test_key = 'rtm_test_transient_' . time();
        $test_value = ['test' => 'value', 'timestamp' => current_time('mysql')];

        // Try to set
        $set_result = set_transient($test_key, $test_value, 5 * MINUTE_IN_SECONDS);
        
        // Try to get
        $get_result = get_transient($test_key);

        // Clean up
        delete_transient($test_key);

        return [
            'set_result' => $set_result,
            'get_result' => $get_result === false ? 'FAILED' : 'SUCCESS',
            'value_matches' => $get_result === $test_value ? 'YES' : 'NO'
        ];
    }

    /**
     * Count RTM transients in database
     */
    private function count_rtm_transients()
    {
        global $wpdb;

        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
                '%rtm_cache%'
            )
        );

        return intval($count);
    }

    /**
     * Get all RTM cache keys from database
     */
    private function get_all_rtm_cache_keys()
    {
        global $wpdb;

        $results = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s ORDER BY option_id DESC LIMIT 20",
                '%rtm_cache%'
            )
        );

        return $results ?: [];
    }

    /**
     * Test setting and getting a transient
     */
    private function test_set_transient()
    {
        $test_key = 'rtm_test_' . uniqid();
        $test_data = [
            'test' => true,
            'timestamp' => current_time('mysql'),
            'value' => 'Test Value ' . rand(1000, 9999)
        ];

        // Set
        $set_result = set_transient($test_key, $test_data, 10 * MINUTE_IN_SECONDS);
        $this->log_cache_activity('TEST SET', $test_key, 'Result: ' . ($set_result ? 'SUCCESS' : 'FAILED'));

        // Get immediately
        $get_result = get_transient($test_key);
        $this->log_cache_activity('TEST GET', $test_key, 'Result: ' . ($get_result !== false ? 'SUCCESS' : 'FAILED'));

        // Verify
        $verified = $get_result === $test_data ? 'YES' : 'NO';

        // Clean up
        delete_transient($test_key);

        return [
            'set' => $set_result ? 'SUCCESS' : 'FAILED',
            'get' => $get_result !== false ? 'SUCCESS' : 'FAILED',
            'verified' => $verified,
            'test_key' => $test_key
        ];
    }

    /**
     * Get cache status for specific key
     */
    public function get_cache_status($key)
    {
        $value = get_transient($key);
        
        return [
            'key' => $key,
            'exists' => $value !== false ? 'YES' : 'NO',
            'value' => $value !== false ? 'Cached' : 'Not in cache',
            'type' => $value !== false ? gettype($value) : 'N/A'
        ];
    }
}
