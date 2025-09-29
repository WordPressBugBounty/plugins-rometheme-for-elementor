<?php

namespace RomethemeKit;

use ZipArchive;

use function ElementorDeps\DI\add;

class Template
{
    public $url;
    public $ck;
    public $cs;
    public function __construct()
    {
        $this->url = 'https://api.rometheme.pro';
        $this->ck = 'ck_p2ke51ckfmb42kefnw67krk93wwjawj6';
        $this->cs = 'cs_djg1rrp51rn6hvj5ck76x75u99ec8e19';
        add_action('wp_ajax_fetch_lib', [$this, 'fetch_lib']);
        add_action('admin_enqueue_scripts', [$this, 'register_scripts']);
        add_action('init', [$this, 'init_template_dir']);
        add_action('wp_ajax_download_template', [$this, 'download_template']);
        add_action('wp_ajax_import_rtm_template', [$this, 'import_rtm_template']);
        add_action('wp_ajax_delete_template', [$this, 'delete_template']);
        add_action('wp_ajax_delete_installed_template', [$this, 'delete_installed_template']);
        add_action('wp_ajax_get_import_progress', [$this, 'get_import_progress']);
        add_action('wp_ajax_get_installed_template', [$this, 'get_installed_template']);
        add_action('wp_ajax_get_template_content', [$this, 'get_template_content']);
        add_action('wp_ajax_install_requirements', [$this, 'install_requirements']);
        add_action('wp_ajax_template_category', [$this, 'template_category']);
        add_action('wp_ajax_get_installed_templates', [$this, 'get_installed_templates']);
        add_action('wp_ajax_rtm_handle_upload_template', [$this, 'rtm_handle_upload_template']);
        add_action('wp_ajax_fetch_envato_template', [$this, 'fetch_envato_template']);
    }

    public function init_template_dir()
    {
        // Path direktori yang ingin dibuat
        $upload_dir = wp_upload_dir();
        $custom_dir = $upload_dir['basedir'] . '/rometheme_template';

        // Cek apakah direktori sudah ada
        if (!file_exists($custom_dir)) {
            // Buat direktori
            if (wp_mkdir_p($custom_dir)) {
                // Atur izin direktori ke 0777
                chmod($custom_dir, 0777);
            }
        }
    }

    public function fetch_lib()
    {
        if (!isset($_POST['wpnonce']) || !wp_verify_nonce($_POST['wpnonce'], 'rtm_template_nonce')) {
            wp_send_json_error('Access Denied');
            wp_die();
        }

        $args = [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'httpversion' => '1.1',
            'timeout' => 15,
            'sslverify' => false, // Ubah ke true di production jika SSL valid
            'auth' => [$this->ck, $this->cs],
        ];

        // Gunakan wp_remote_get untuk mengambil data dari REST API
        $response = wp_remote_get($this->url . '/wp-json/public/template_lib', $args);

        if (is_wp_error($response)) {
            wp_send_json_error('Error: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $templates = json_decode($body, true);

        if (!is_array($templates)) {
            wp_send_json_error('Invalid response format');
        }

        // Filtering berdasarkan pencarian
        if (!empty($_POST['search'])) {
            $search = strtolower(trim($_POST['search']));
            $templates = array_filter($templates, function ($item) use ($search) {
                return stripos($item['name'], $search) !== false ||
                    stripos($item['category'], $search) !== false ||
                    stripos($item['type'], $search) !== false;
            });
        }

        // Filtering berdasarkan kategori
        if (!empty($_POST['category'])) {
            $category = $_POST['category'];
            $templates = array_filter($templates, function ($item) use ($category) {
                return stripos($item['category'], $category) !== false;
            });
        }

        // Pisahkan free dan pro
        $free = [];
        $pro = [];
        foreach ($templates as $k => $v) {
            if ($v['type'] === 'free') {
                $free[$k] = $v;
            } else {
                $pro[$k] = $v;
            }
        }

        $sortData = array_merge($free, $pro);

        // Pagination
        $paged = isset($_POST['paged']) ? max(1, intval($_POST['paged'])) : 1;
        $per_page = 24;
        $total_items = count($sortData);
        $total_pages = ceil($total_items / $per_page);
        $offset = ($paged - 1) * $per_page;
        $paged_data = array_slice($sortData, $offset, $per_page);

        // Persiapkan data
        $data = [];
        foreach ($paged_data as $k => $v) {
            $hash_id = wp_hash($v['id']);
            $data[$k] = [
                'id' => $v['id'],
                'name' => $v['name'],
                'category' => $v['category'],
                'type' => $v['type'],
                'preview_url' => $v['preview_url'],
                'image_preview' => $v['image_preview'],
                'downloads' => $v['downloads'],
                'has_installed' => $this->has_installed($hash_id),
                'installed' => $this->has_installed($hash_id) ? $hash_id : null,
            ];
        }

        wp_send_json_success([
            'data_template' => $data,
            'pagination' => [
                'current_page' => $paged,
                'total_pages' => $total_pages,
            ],
            'template_url' => admin_url('admin.php?page=rtmkit-templates')
        ]);
    }

    public function fetch_envato_template()
    {
        if (!isset($_POST['wpnonce']) || !wp_verify_nonce($_POST['wpnonce'], 'rtm_template_nonce')) {
            wp_send_json_error('Access Denied');
            wp_die();
        }

        $paged = isset($_POST['paged']) ? max(1, intval($_POST['paged'])) : 1;

        $url = 'https://api.envato.com/v1/discovery/search/search/item?site=themeforest.net&username=rometheme&category=template-kits&sort_by=date&sort_by=date&sort_direction=desc&page_size=24&page=' . $paged;

        if (isset($_POST['search']) && !empty($_POST['search'])) {
            $url .= '&term=' . sanitize_text_field($_POST['search']);
        }

        $args = [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer 0Qi0Q396nMlr64ysVwF5lg6yP9RcdWjf',
            ],
            'httpversion' => '1.1',
            'timeout' => 15,
            'sslverify' => true, // Ubah ke true di production jika SSL valid
        ];

        // Gunakan wp_remote_get untuk mengambil data dari REST API
        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            wp_send_json_error('Error: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!is_array($data)) {
            wp_send_json_error('Invalid response format');
        }

        wp_send_json_success($data);
    }

    public function register_scripts()
    {
        $screen = get_current_screen();
        $nonce = wp_create_nonce('rtm_template_nonce');
        if ($screen->id == 'romethemekit_page_rtmkit-templates' || $screen->id == 'rtmkit_page_rtmkit-templates') {
            wp_enqueue_script('template-scripts', \Rometheme::module_url() . 'template/assets/js/template.js');
            wp_localize_script('template-scripts', 'rometheme_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => $nonce
            ]);
        }
    }

    public function download_template()
    {
        if (!current_user_can('manage_options')) {
            wp_die();
        }

        if (!isset($_POST['wpnonce']) || !wp_verify_nonce($_POST['wpnonce'], 'rtm_template_nonce')) {
            wp_send_json_error('Access Denied');
            wp_die();
        }

        $id = sanitize_text_field($_POST['template']);

        $url = $this->url . '/wp-json/public/template_lib?id=' . urlencode($id);

        $response = wp_remote_get($url, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode("{$this->ck}:{$this->cs}"),
            ],
            'timeout' => 20,
            'sslverify' => false, // Ubah ke true di environment production
        ]);

        if (is_wp_error($response)) {
            wp_send_json_error('Error: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!isset($data['zip_url'], $data['id'])) {
            wp_send_json_error('Invalid data received from API.');
        }

        $zip_url = $data['zip_url'];

        // Update counter dan ekstrak template
        $this->update_download($id);
        $this->template_extract($zip_url, $data['id']);
    }


    public function import_rtm_template()
    {
        if (!current_user_can('manage_options')) {
            wp_die();
        }

        if (!isset($_POST['wpnonce']) || !wp_verify_nonce($_POST['wpnonce'], 'rtm_template_nonce')) {
            wp_send_json_error('Access Denied');
            wp_die();
        }

        // Ambil parameter yang diperlukan
        $template = sanitize_text_field($_POST['template']);
        $path = sanitize_text_field($_POST['path']);
        $template_name = sanitize_text_field($_POST['template_name']);
        $upload_dir = wp_upload_dir();
        $template_dir = $upload_dir['basedir'] . '/rometheme_template';
        $fullPath = $template_dir . '/' . $template . '/' . $path;

        $transient_id = 'rtm_import_progress_' . $template . '_' . $template_name;
        // Awal progres
        set_transient($transient_id, ['progress' => 0, 'message' => 'Initializing import...'], 60);

        // Validasi file JSON
        if (!file_exists($fullPath)) {
            set_transient($transient_id, ['progress' => 100, 'message' => 'File not found!'], 60);
            wp_send_json_error('File JSON tidak ditemukan.');
            return;
        }

        $json_data = file_get_contents($fullPath);
        $template_data = json_decode($json_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            set_transient('rtm_import_progress', ['progress' => 100, 'message' => 'Invalid JSON file.'], 60);
            wp_send_json_error('File JSON tidak valid.');
            return;
        }

        // Update progres
        set_transient($transient_id, ['progress' => 25, 'message' => 'Importing template...'], 60);

        // Akses Template Manager dan lakukan import
        $local_source = \Elementor\Plugin::$instance->templates_manager->get_source('local');
        $temp_template = wp_tempnam('temp_' . $template);
        // Update progres
        set_transient($transient_id, ['progress' => 40, 'message' => 'Importing template...'], 60);
        file_put_contents($temp_template, $json_data);
        $result = $local_source->import_template(basename($temp_template), $temp_template);

        if (file_exists($temp_template)) {
            unlink($temp_template);
        }

        if (is_wp_error($result)) {
            set_transient($transient_id, ['progress' => 100, 'message' => 'Failed to import template.'], 60);
            wp_send_json_error('Failed to import template: ' . esc_html($result->get_error_message()));
        }

        if ($result[0] && $result[0]['template_id']) {
            $imported_template_id = $result[0]['template_id'];
            set_transient($transient_id, ['progress' => 50, 'message' => 'Importing Template...'], 60);
            if ($template_data['metadata'] && ! empty($template_data['metadata']['template_type']) && 'global-styles' === $template_data['metadata']['template_type']) {
                // We set some metadata around the global template so Elementor can interpret them correctly:
                // From: wp-content/plugins/elementor/core/documents-manager.php:366
                update_post_meta($imported_template_id, '_elementor_edit_mode', 'builder');
                update_post_meta($imported_template_id, '_elementor_template_type', 'kit');
                // Set the global theme styles to this newly imported template:
                update_option('elementor_active_kit', $imported_template_id);

                // Update the kit styles title so we can display it nicely in the drop down settings UI.
                wp_update_post(
                    array(
                        'ID'         => $imported_template_id,
                        'post_title' => 'Kit Styles: ' . $this->get_template_name($template),
                    )
                );
            }

            set_transient($transient_id, ['progress' => 75, 'message' => 'Importing Template...'], 60);
            $history = get_option('rtm_import_template_' . $template, []);
            $key = html_entity_decode($result[0]['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $key = str_replace(["–", "—", "−"], "-", $key);
            $key = str_replace([' ', '_'], '_', $key);
            $key = strtolower($key);
            $history[$key] = $imported_template_id;
            update_option('rtm_import_template_' . $template, $history);
            $result[0]['edit_url'] = admin_url('post.php?post=' . $imported_template_id . '&action=elementor');
            $result[0]['delete_url'] = get_delete_post_link($imported_template_id);
        }
        delete_transient($transient_id);
        wp_send_json_success($result[0]);
    }

    public function get_import_progress()
    {
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        $template = sanitize_text_field($_POST['template']);
        $template_name = sanitize_text_field($_POST['template_name']);
        $transient_id = 'rtm_import_progress_' . $template . '_' . $template_name;

        $progress = get_transient($transient_id);
        if (!$progress) {
            wp_send_json_error(['progress' => 100, 'message' => 'No progress available.']);
        } else {
            wp_send_json_success($progress);
        }
    }

    public function get_installed_template()
    {
        if (!isset($_POST['wpnonce']) || !wp_verify_nonce($_POST['wpnonce'], 'rtm_template_nonce')) {
            wp_send_json_error('Access Denied');
            wp_die();
        }

        $hashId = $_POST['template'];

        $upload_dir = wp_upload_dir();
        $rtmTemplateDir = $upload_dir['basedir'] . '/rometheme_template';
        $imported = get_option('rtm_import_template_' . $hashId, []);
        $manifest = json_decode(file_get_contents($rtmTemplateDir . '/' . $hashId . '/manifest.json'), true);
        $rtmTemplateUrl = $upload_dir['baseurl'] . '/rometheme_template/' . $hashId;
        $manifest['path_url'] = $rtmTemplateUrl;

        $data = [
            "imported" => $imported,
            "manifest" => $manifest,
            "description" => $this->get_template_description($this->get_installed_template_id($hashId))
        ];
        wp_send_json_success($data);
    }

    public function get_installed_templates()
    {
        $templates = get_option('rtm_template_installed', []);
        $upload_dir = wp_upload_dir();
        $rtmTemplateDir = $upload_dir['basedir'] . '/rometheme_template';
        $data = [];

        foreach ($templates as $template => $v) {
            $id = $v['template_id'];
            $manifest = json_decode(file_get_contents($rtmTemplateDir . '/' . $template . '/manifest.json'));
            foreach ($manifest->templates as $i => $v) {
                if (stripos($v->name, 'home') !== false) {
                    $preview = $v->preview_url;
                }
            }
            $data[$template] = [
                'id' => $id,
                'name' => $manifest->title,
                'image_preview_url' =>  \RomethemeKit\Template::get_template_image_preview_url($id),
                'preview_url' => $preview
            ];
        }

        wp_send_json_success($data);
    }


    public function get_template_name($hashId)
    {
        $upload_dir = wp_upload_dir();
        $rtmTemplateDir = $upload_dir['basedir'] . '/rometheme_template';

        $manifest = json_decode(file_get_contents($rtmTemplateDir . '/' . $hashId . '/manifest.json'));

        return $manifest->title;
    }

    function template_extract($url, $id, $return = false)
    {
        $upload_dir = wp_upload_dir();
        $custom_dir = $upload_dir['basedir'] . '/rometheme_template';
        // $tempFile = wp_tempnam($url);

        $upload_dir = wp_upload_dir();
        // Direktori aman (bukan langsung ke uploads publik)
        $base_safe_dir = $upload_dir['basedir'] . '//rometheme_template/';
        $tmp_dir       = $base_safe_dir . 'tmp/';
        if (! file_exists($tmp_dir)) {
            wp_mkdir_p($tmp_dir);
            // cegah eksekusi file php di folder ini
            @file_put_contents(
                $base_safe_dir . '.htaccess',
                "Options -Indexes\n<FilesMatch \"\\.(php|phtml|phar)$\">\n  Deny from all\n</FilesMatch>\n"
            );
        }

        $hashId = wp_hash($id);
        // $targetDir = $custom_dir . '/' . $hashId;

        $tempFile = $tmp_dir . 'upload_' . $hashId . '.zip';
        $response = wp_remote_get($url, ['timeout' => 300]);

        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }

        $fileContent = wp_remote_retrieve_body($response);

        file_put_contents($tempFile, $fileContent);

        $this->template_extract_secure($tempFile, $id);
    }

    function update_download($id)
    {
        if (empty($id)) {
            return;
        }

        $url = $this->url . '/wp-json/public/updld?id=' . urlencode($id);

        $response = wp_remote_post($url, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode("{$this->ck}:{$this->cs}"),
            ],
            'timeout' => 15,
            'sslverify' => false, // Ganti ke true di production jika SSL valid
        ]);

        if (is_wp_error($response)) {
            error_log('Download update failed: ' . $response->get_error_message());
            return;
        }

        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        // Jika perlu, bisa ditambahkan logika pemrosesan hasil
        return $decoded;
    }


    function has_installed($hashId)
    {
        $option = get_option('rtm_template_installed');
        if (!is_array($option)) {
            return false;
        } else {
            return (array_key_exists($hashId, $option));
        }
    }

    public static function get_installed_template_id($template)
    {
        $installed_template = get_option('rtm_template_installed', []);

        foreach ($installed_template as $k => $v) {
            if ($k === $template) {
                return $v['template_id'];
            }
        }
    }

    public static function get_template_description($id)
    {
        $f = new self();
        return $f->_get_template_description($id);
    }

    public static function get_template_image_preview_url($id)
    {
        $f = new self();
        $res = $f->_get_template_item_data($id);
        return $res['preview_image_url'];
    }

    public function get_template_content()
    {
        if (!isset($_POST['wpnonce']) ||  ! check_ajax_referer('rtm_template_nonce', 'wpnonce')) {
            wp_send_json_error('Access Denied');
            wp_die();
        }

        $id = absint($_POST['template']);

        $elementorData = get_post_meta($id, '_elementor_data', true);

        $data = ['content' => json_decode($elementorData)];

        wp_send_json_success($data);
    }

    private function _get_template_item_data($id)
    {
        if (empty($id)) {
            return null;
        }

        $url = $this->url . '/wp-json/public/template_lib?id=' . urlencode($id);

        $response = wp_remote_get($url, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode("{$this->ck}:{$this->cs}"),
            ],
            'timeout' => 15,
            'sslverify' => false, // Set ke true di environment production
        ]);

        if (is_wp_error($response)) {
            error_log('Failed to fetch template item data: ' . $response->get_error_message());
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        if (!is_array($decoded)) {
            return null;
        }

        return $decoded;
    }


    private function _get_template_description($id)
    {
        $response = $this->_get_template_item_data($id);
        return $response['description'];
    }

    public static function get_template_category()
    {
        $f = new self();
        return $f->_get_template_category();
    }

    public function _get_template_category()
    {
        $url = $this->url . '/wp-json/public/template_lib_cat';

        $response = wp_remote_get($url, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode("{$this->ck}:{$this->cs}"),
            ],
            'timeout' => 15,
            'sslverify' => false, // Set ke true di production dengan SSL valid
        ]);

        if (is_wp_error($response)) {
            error_log('Failed to fetch template categories: ' . $response->get_error_message());
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        if (!is_array($decoded)) {
            return null;
        }

        return $decoded;
    }


    public function template_category()
    {
        if (!isset($_POST['wpnonce']) ||  !wp_verify_nonce($_POST['wpnonce'], 'rtm_template_nonce')) {
            wp_send_json_error('Access Denied');
            wp_die();
        }

        $cat = $this->get_template_category();
        if ($cat) {
            wp_send_json_success($cat);
        } else {
            wp_send_json_error();
        }
    }

    public function delete_template()
    {
        if (!current_user_can('manage_options')) {
            wp_die();
        }

        if (!isset($_POST['wpnonce']) || !wp_verify_nonce($_POST['wpnonce'], 'rtm_template_nonce')) {
            wp_send_json_error('Access Denied');
            wp_die();
        }

        require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-base.php';
        require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-direct.php';
        $file_system_direct = new \WP_Filesystem_Direct(false);

        $template = $_POST['template'];

        $upload_dir = wp_upload_dir();
        $custom_dir = $upload_dir['basedir'] . '/rometheme_template';
        $template_dir = $custom_dir . '/' . $template;
        if ($file_system_direct->rmdir($template_dir, true)) {
            $option = get_option('rtm_template_installed');

            unset($option[$template]);
            update_option('rtm_template_installed', $option);
            delete_option('rtm_import_template_' . $template);

            wp_send_json_success('Delete Success');
        } else {
            wp_send_json_error('Failed to Delete Template directory' . $template_dir);
        }
    }

    public function delete_installed_template()
    {
        if (!current_user_can('manage_options')) {
            wp_die();
        }

        if (!isset($_POST['wpnonce']) ||  !wp_verify_nonce($_POST['wpnonce'], 'rtm_template_nonce')) {
            wp_send_json_error('Access Denied');
            wp_die();
        }

        $id = $_POST['template_id'];
        $template = $_POST['template'];
        $op = get_option('rtm_import_template_' . $template, []);

        foreach ($op as $k => $v) {
            if ($id == $v) {
                $keyTemplate  = $k;
            }
        }

        $post_type = get_post_type($id);
        $type      = get_post_meta($id, '_elementor_template_type', true);

        if ($post_type === 'elementor_library' && $type === 'kit') {
            remove_all_actions('before_delete_post');
        }

        if (wp_delete_post($id, true)) {
            unset($op[$keyTemplate]);
            update_option('rtm_import_template_' . $template, $op);
            wp_send_json_success('success');
        }
    }

    public static function missing_plugins($required)
    {
        $missing = [];

        foreach ($required as $plugin) {
            if (!is_plugin_active($plugin->file)) {
                array_push($missing, $plugin);
            }
        }
        return $missing;
    }

    public function install_requirements()
    {
        if (!current_user_can('manage_options')) {
            wp_die();
        }

        if (!isset($_POST['wpnonce']) ||  !wp_verify_nonce($_POST['wpnonce'], 'rtm_template_nonce')) {
            wp_send_json_error('Access Denied');
            wp_die();
        }

        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        include_once ABSPATH . 'wp-admin/includes/file.php';
        include_once ABSPATH . 'wp-admin/includes/misc.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $plugin = $_POST['plugin'];
        $plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
        $plugin_slug = dirname($plugin);

        if (file_exists($plugin_file)) {
            // Activate the plugin if already installed but inactive
            ob_start();
            activate_plugin($plugin);
            ob_clean();
            ob_end_clean();
            wp_send_json_success("Install and Activate Successfully");
        } else {
            ob_start();
            $plugin_download_url = "https://downloads.wordpress.org/plugin/{$plugin_slug}.latest-stable.zip"; // Adjust URL structure
            $upgrader = new \Plugin_Upgrader();
            $result = $upgrader->install($plugin_download_url);

            if (is_wp_error($result)) {
                wp_send_json_error();
            }
            $activate_result = activate_plugin($plugin);
            if (is_wp_error($activate_result)) {
                wp_send_json_error('Plugin installed but failed to activate: ' . $activate_result->get_error_message());
            }

            wp_send_json_success('Plugin installed and activated successfully.');
        }
    }

    function rtm_handle_upload_template()
    {
        check_ajax_referer('rtm_template_nonce', 'nonce');

        if (empty($_FILES['file'])) {
            wp_send_json_error('No File Uploaded.');
        }

        if (! current_user_can('manage_options')) {
            wp_send_json_error(
                array('message' => 'Insufficient permissions'),
                403
            );
        }

        $file = $_FILES['file'];
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'zip') {
            wp_send_json_error('Only .zip file allowed.');
        }
        $upload_dir = wp_upload_dir();
        // Direktori aman (bukan langsung ke uploads publik)
        $base_safe_dir = $upload_dir['basedir'] . '//rometheme_template/';
        $tmp_dir       = $base_safe_dir . 'tmp/';
        if (! file_exists($tmp_dir)) {
            wp_mkdir_p($tmp_dir);
            // cegah eksekusi file php di folder ini
            @file_put_contents(
                $base_safe_dir . '.htaccess',
                "Options -Indexes\n<FilesMatch \"\\.(php|phtml|phar)$\">\n  Deny from all\n</FilesMatch>\n"
            );
        }

        // Buat nama unik untuk zip
        $unique       = wp_generate_password(12, false);
        $tmp_zip_path = $tmp_dir . 'upload_' . $unique . '.zip';

        // Simpan file upload ke lokasi aman
        if (! move_uploaded_file($file['tmp_name'], $tmp_zip_path)) {
            wp_send_json_error(
                array('message' => 'Failed save temporary file.'),
                500
            );
        }

        // Proses ekstraksi dengan fungsi aman
        $res = $this->template_extract_secure($tmp_zip_path, $file['name'], true);

        // Hapus zip sementara
        if (file_exists($tmp_zip_path)) {
            @unlink($tmp_zip_path);
        }

        if ($res) {
            wp_send_json_success('Template has been successfully extracted.');
        } else {
            wp_send_json_error('Failed extracting template.');
        }
    }

    /**
     * Secure extraction of a local ZIP file into a template directory.
     *
     * NOTE:
     * - This expects $zip_path to be a filesystem path (not a URL).
     * - It will reject zips containing path traversal, absolute paths, or disallowed extensions.
     * - Extracted files are stored under WP_CONTENT_DIR/rometheme_template/{hash}/
     *   and .htaccess is created to prevent PHP execution on Apache.
     *
     * @param string $zip_path Full filesystem path to the ZIP file.
     * @param string $id      Original identifier (used to generate hash).
     * @param bool   $return  If true, return boolean; otherwise send JSON and exit.
     * @return bool|void
     */
    function template_extract_secure($zip_path, $id, $return = false)
    {
        if (empty($zip_path) || ! file_exists($zip_path)) {
            return $return ? false : wp_send_json_error(['message' => 'Zip file not found.'], 400);
        }

        $zip_path = realpath($zip_path);
        if (strpos($zip_path, realpath(WP_CONTENT_DIR)) !== 0) {
            return $return ? false : wp_send_json_error(['message' => 'Invalid zip location.', 'path' => $zip_path], 403);
        }

        $upload_dir = wp_upload_dir();
        $hashId     = wp_hash($id);
        $base_dir   = $upload_dir['basedir'] . '/rometheme_template';
        $targetDir  = $base_dir . '/' . $hashId . '/';

        // buat base dir + proteksi root
        if (! file_exists($base_dir)) {
            wp_mkdir_p($base_dir);
            @file_put_contents($base_dir . '/.htaccess', "Options -Indexes\n<FilesMatch \"\\.(php|phtml|phar)$\">\n  Deny from all\n</FilesMatch>\n");
        }
        if (! file_exists($targetDir)) {
            wp_mkdir_p($targetDir);
            @file_put_contents($targetDir . 'index.html', '<!-- protected -->');
        }

        $zip = new ZipArchive();
        if ($zip->open($zip_path) !== true) {
            return $return ? false : wp_send_json_error(['message' => 'Invalid or corrupt zip.'], 400);
        }

        // whitelist extension
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'json', 'css', 'js', 'txt', 'html', 'htm', 'md'];

        // cek isi zip
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if (substr($entry, -1) === '/') continue;

            $normalized = str_replace('\\', '/', $entry);

            // block traversal / absolute
            if (strpos($normalized, '../') !== false || substr($normalized, 0, 1) === '/' || preg_match('/^[A-Za-z]:\\\\/', $entry)) {
                $zip->close();
                error_log("[rtm] rejected: traversal ($entry)");
                return $return ? false : wp_send_json_error(['message' => 'Zip contains invalid paths.'], 400);
            }

            $entry_ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
            if (empty($entry_ext) || in_array($entry_ext, ['php', 'phtml', 'phar', 'exe', 'sh', 'pl', 'cgi'], true)) {
                $zip->close();
                error_log("[rtm] rejected: bad extension ($entry)");
                return $return ? false : wp_send_json_error(['message' => 'Zip contains disallowed file types.'], 400);
            }

            if (! in_array($entry_ext, $allowed_ext, true)) {
                $zip->close();
                error_log("[rtm] rejected: unsupported ext $entry_ext ($entry)");
                return $return ? false : wp_send_json_error(['message' => "Unsupported file type: $entry_ext"], 400);
            }
        }

        // ekstrak dengan struktur folder asli
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if (substr($entry, -1) === '/') continue;

            $entry_ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
            if (! in_array($entry_ext, $allowed_ext, true)) continue;

            $normalized = str_replace('\\', '/', $entry);
            $safe_name  = sanitize_file_name(basename($normalized));
            $subdir     = dirname($normalized);

            $final_dir  = $targetDir . ($subdir !== '.' ? $subdir . '/' : '');
            wp_mkdir_p($final_dir);

            $target_path = $final_dir . $safe_name;

            $stream = $zip->getStream($entry);
            if ($stream === false) continue;

            $out = fopen($target_path, 'w');
            if ($out === false) {
                fclose($stream);
                continue;
            }

            while (! feof($stream)) fwrite($out, fread($stream, 8192));
            fclose($out);
            fclose($stream);

            // detect embedded php
            $head = @file_get_contents($target_path, false, null, 0, 512);
            if ($head !== false && stripos($head, '<?php') !== false) {
                @unlink($target_path);
                error_log("[rtm] removed suspicious file ($target_path)");
                continue;
            }

            // sanity check → skip mismatch untuk json agar manifest.json tidak kehapus
            $check = wp_check_filetype_and_ext($target_path, $safe_name);
            if ($entry_ext !== 'json' && $check && isset($check['ext']) && $check['ext'] !== $entry_ext) {
                @unlink($target_path);
                error_log("[rtm] removed mismatch file ($target_path)");
                continue;
            }
        }

        $zip->close();

        // update option
        $option = get_option('rtm_template_installed', []);
        if (! is_array($option)) $option = [];
        $option[$hashId] = ['template_id' => $hashId, 'created' => current_time('mysql')];
        update_option('rtm_template_installed', $option);

        return $return ? true : wp_send_json_success(['message' => 'success extract', 'template' => $hashId]);
    }

    public static function normalize_dash_key($key)
    {
        // Semua variasi dash jadi ASCII "-"
        $dashes = ["–", "—", "−", "‒", "‐", "-", "﹘", "﹣", "－"];
        $key = str_replace($dashes, "-", $key);

        // Hapus spasi aneh atau invisible character
        $key = preg_replace('/\p{Cf}+/u', '', $key);

        return $key;
    }
}
