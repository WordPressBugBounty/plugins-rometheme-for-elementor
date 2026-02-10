<?php

namespace RTMKit\Modules;

class SystemInfo
{
    protected static $instance;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get_system_info()
    {
        $wp_info = [
            'WordPress_version' => get_bloginfo('version'),
            'WordPress_language' => get_bloginfo('language'),
            'Site_url' => get_site_url(), // Menambahkan URL situs
            'Max_upload_size' => wp_max_upload_size(), // Menambahkan ukuran maksimum unggahan
            'Permalink_structure' => get_option('permalink_structure'), // Menambahkan struktur permalink
            'Time_zone' => get_option('timezone_string'), // Menambahkan zona waktu
            'WP_multisite' => (is_multisite()) ? 'Yes' : 'No', // Menambahkan info apakah WordPress berjalan dalam mode multisite atau tidak
            'Active_plugins' => get_option('active_plugins'),
            // Informasi tambahan yang mungkin Anda perlukan
        ];

        $php_info = [
            'PHP_version' => phpversion(),
            'PHP_OS' => PHP_OS,
            'PHP_memory_limit' => ini_get('memory_limit'),
            'PHP_max_execution_time' => ini_get('max_execution_time'),
            'server_software' => $_SERVER['SERVER_SOFTWARE'],
            'max_input_vars' => ini_get('max_input_vars'),
            'post_max_size' =>  ini_get('post_max_size')
        ];

        include_once ABSPATH . 'wp-admin/includes/plugin.php';

        $elementorFile = WP_PLUGIN_DIR . '/elementor/elementor.php';
        $elementorExist = file_exists($elementorFile) && is_plugin_active('elementor/elementor.php');

        global $wpdb;

        $mysql_info_cached = wp_cache_get('mysql_info_cached');

        if (false === $mysql_info_cached) {
            // Jika data tidak ada di cache, ambil dari database dan simpan ke cache
            $query = "SELECT version() as version, @@version_comment as comment";
            $mysql_info = $wpdb->get_results($query, ARRAY_A);

            // Simpan data ke cache
            wp_cache_set('mysql_info_cached', $mysql_info);

            // Gunakan data yang diambil dari database
            $mysql_info_cached = $mysql_info;
        }

        $mysql_version = $wpdb->db_version();

        $mysql_comment_v = $mysql_info_cached[0]['comment'];

        $uploads_dir = wp_upload_dir();
        $upload_path = $uploads_dir['basedir'];
        $is_writable = is_writable($upload_path) ? 'Writeable' : 'Not Writeable';

        $active_theme = wp_get_theme();
        $theme_name = $active_theme->get('Name');
        $theme_version = $active_theme->get('Version');
        $theme_author = $active_theme->get('Author');
        $active_plugins = get_option('active_plugins');

        $info = [
            "wordpress" => $wp_info,
            "php" => $php_info,
            "active_plugins" => $active_plugins,
            "active_themes" => [
                'name' => $theme_name,
                'version' => $theme_version,
                'author' => $theme_author
            ],
            "database" => [
                "sql_name_server" => $mysql_comment_v,
                "sql_version" => $mysql_version
            ]
        ];

        $info['php']['write_permission'] = $is_writable;
        $info['wordpress']['memory_limit'] = WP_MEMORY_LIMIT;
        $info['elementor'] = $elementorExist;
        return $info;
    }
}
