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
     * Get cached value or execute callback
     */
    public function get($key, $callback, $ttl = 0)
    {
        // Try to get from cache first
        $cached = get_transient($key);
        
        if ($cached !== false) {
            return $cached;
        }

        // Execute callback if not cached
        $value = call_user_func($callback);

        // Store in cache if callback returned something
        if ($value !== null && $value !== false) {
            set_transient($key, $value, $ttl);
        }

        return $value;
    }

    /**
     * Set cache value
     */
    public function set($key, $value, $ttl = 0)
    {
        if ($value !== null && $value !== false) {
            set_transient($key, $value, $ttl);
            return true;
        }
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
}
