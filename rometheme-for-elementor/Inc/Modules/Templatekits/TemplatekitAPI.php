<?php

namespace RTMKit\Modules\Templatekits;

use WP;

class TemplatekitAPI
{
    private static ?TemplatekitAPI $instance = null;

    public static function instance(): TemplatekitAPI
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        if (wp_doing_ajax()) {
            add_action('wp_ajax_render_templates', [$this, 'render_templates']);
            add_action('wp_ajax_download_template', [$this, 'download_template']);
            add_action('wp_ajax_upload_template', [$this, 'upload_template']);
            add_action('wp_ajax_delete_template', [$this, 'delete_template']);
            add_action('wp_ajax_import_template', [$this, 'import_template']);
            add_action('wp_ajax_send_themeforest_stats', [$this, 'send_themeforest_stats']);
            add_action('wp_ajax_delete_installed_template', [$this, 'delete_installed_template']);
        }
    }

    public function render_templates()
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');
        if (isset($_POST['template'])) {
            $template = sanitize_text_field($_POST['template']);
            ob_start();
            $datas = $this->get_template_data($template);
            require_once RTM_KIT_DIR . 'views/' . $template . '_templates.php';
            $render = ob_get_clean();
            wp_send_json_success($render);
        } else {
            wp_send_json_error('Template not specified.');
            return;
        }
        wp_die();
    }

    private function get_template_data($template)
    {
        // Contoh data statis, ganti dengan data dinamis sesuai kebutuhan
        $endpoint = [
            'themeforest' => 'wp-json/public/templatekits/themeforest?',
            'templatekits' => 'wp-json/public/templatekits',
            'installed' => '',
        ];

        if ($template !== 'installed') {
            $api_handler = \RTMKit\Modules\Helper\APIHandler::instance();

            if ($template === 'templatekits') {
                $response = $api_handler->remote($endpoint[$template], [], null, true);
                $templates = $response;

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
                if (!empty($_POST['category']) && $_POST['category'] !== 'all') {
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
                        'has_installed' => \RTMKit\Modules\Templatekits\TemplatekitModule::instance()->has_installed($hash_id),
                        'installed' => \RTMKit\Modules\Templatekits\TemplatekitModule::instance()->has_installed($hash_id) ? $hash_id : null,
                    ];
                }

                return [
                    'data_template' => $data,
                    'pagination' => [
                        'current_page' => $paged,
                        'total_pages' => $total_pages,
                    ],
                    'template_url' => admin_url('admin.php?page=rtmkit-templates')
                ];
            } else {
                $paged = isset($_POST['paged']) ? max(1, intval($_POST['paged'])) : 1;
                $uri = $endpoint[$template] . '&paged=' . $paged;

                if (!empty($_POST['search'])) {
                    $search = urlencode(trim($_POST['search']));
                    $uri .= '&search=' . $search;
                }
                $response = $api_handler->remote($uri, [], null, true);
                return $response;
            }
        } else {
            $option = get_option('rtm_template_installed');
            if (!is_array($option)) {
                $option = [];
            }
            $templates = [];
            foreach ($option as $k => $v) {
                $templates[$k] = [
                    'id' => $v['template_id'],
                    'installed' => $v['template_id'],
                    'created' => $v['created'],
                ];
            }
            return [
                'data_template' => $templates,
                'template_url' => admin_url('admin.php?page=rtmkit-templates')
            ];
        }

        return $endpoint[$template] ?? 'Data tidak ditemukan';
    }

    public function _get_template_data($template)
    {
        return $this->get_template_data($template);
    }

    public function  get_template_categories()
    {
        $api_handler = \RTMKit\Modules\Helper\APIHandler::instance();
        $data = $api_handler->remote('wp-json/public/template_lib_cat');
        return $data['rtm_templatekit_category'] ?? 'Data tidak ditemukan';
    }

    public function download_template()
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_die();
        }

        $id = sanitize_text_field($_GET['template']);

        // $api_handler = \RTMKit\Modules\Helper\APIHandler::instance();
        $endpoint = 'wp-json/public/request-download/' . $id;

        $this->update_download($id);
        $this->template_extract($endpoint, $id);
    }

    function update_download($id)
    {
        if (empty($id)) {
            return;
        }
        $url = 'wp-json/public/updld?id=' . $id;
        $api_handler = \RTMKit\Modules\Helper\APIHandler::instance();
        $response = $api_handler->remote($url, [], null, false, false, 'POST');

        if (is_wp_error($response)) {
            error_log('Download update failed: ' . $response->get_error_message());
            return;
        }
        // Jika perlu, bisa ditambahkan logika pemrosesan hasil
        return $response;
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

        $api_handler = \RTMKit\Modules\Helper\APIHandler::instance();

        $tempFile = $tmp_dir . 'upload_' . $hashId . '.zip';
        $response = $api_handler->remote($url, [
            'timeout' => 300,
            'headers' => [
                'content-type' => 'application/zip'
            ]
        ], null, false, false);

        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        }

        $fileContent = $response;

        file_put_contents($tempFile, $fileContent);

        $extract = $this->template_extract_secure($tempFile, $id, true);

        if ($extract) {
            @unlink($tempFile);
            if ($return) {
                return true;
            } else {
                $hashId = wp_hash($id);
                wp_send_json_success(["message" => 'Template extracted successfully.', "template" => $hashId]);
            }
        } else {
            @unlink($tempFile);
            if ($return) {
                return false;
            } else {
                wp_send_json_error('Failed to extract template.');
            }
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

        $zip = new \ZipArchive();
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
        $option[$hashId] = ['template_id' => $id, 'created' => current_time('mysql')];
        update_option('rtm_template_installed', $option);

        return $return ? true : wp_send_json_success(['message' => 'success extract', 'template' => $hashId]);
    }

    function upload_template()
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');

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

    function delete_template()
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions', 403);
        }
        require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-base.php';
        require_once ABSPATH . '/wp-admin/includes/class-wp-filesystem-direct.php';
        $file_system_direct = new \WP_Filesystem_Direct(false);

        $template = $_GET['template'];

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

    function import_template()
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions', 403);
        }

        $step           = sanitize_text_field($_POST['step'] ?? 'init');
        $template       = sanitize_text_field($_POST['template']);
        $path           = sanitize_text_field($_POST['path']);
        $template_name  = sanitize_text_field($_POST['template_name']);
        $upload_dir     = wp_upload_dir();
        $template_dir   = $upload_dir['basedir'] . '/rometheme_template';
        $fullPath       = $template_dir . '/' . $template . '/' . $path;
        $transient_id   = 'rtm_import_progress_' . $template . '_' . $template_name;

        switch ($step) {
            // ------------------------------------------------
            // STEP 1: INIT
            // ------------------------------------------------
            case 'init':
                set_transient($transient_id, ['progress' => 0, 'message' => 'Initializing import...'], 60);
                wp_send_json_success(['next' => 'validate', 'progress' => 10]);
                break;

            // ------------------------------------------------
            // STEP 2: VALIDATE FILE
            // ------------------------------------------------
            case 'validate':
                if (!file_exists($fullPath)) {
                    set_transient($transient_id, ['progress' => 100, 'message' => 'File not found!'], 60);
                    wp_send_json_error('File tidak ditemukan');
                }

                $json_data = file_get_contents($fullPath);
                $template_data = json_decode($json_data, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    set_transient($transient_id, ['progress' => 100, 'message' => 'Invalid JSON file!'], 60);
                    wp_send_json_error('File JSON tidak valid.');
                }

                set_transient($transient_id, ['progress' => 25, 'message' => 'Validated successfully...'], 60);
                wp_send_json_success([
                    'next' => 'import',
                    'json_data' => base64_encode($json_data),
                    'progress' => 30
                ]);
                break;

            // ------------------------------------------------
            // STEP 3: IMPORT TO ELEMENTOR
            // ------------------------------------------------
            case 'import':
                $json_data = json_decode(base64_decode($_POST['json_data']), true);
                $local_source = \Elementor\Plugin::$instance->templates_manager->get_source('local');

                if (isset($_POST['import_without_images']) && $_POST['import_without_images'] === '1') {
                    $this->replace_images_with_placeholder($json_data);
                }

                $this->process_mask_images_recursively($json_data);

                $temp_template = wp_tempnam('temp_' . $template);
                file_put_contents($temp_template, json_encode($json_data));

                set_transient($transient_id, ['progress' => 40, 'message' => 'Importing template...'], 60);
                $result = $local_source->import_template(basename($temp_template), $temp_template);

                if (file_exists($temp_template)) unlink($temp_template);

                if (is_wp_error($result)) {
                    set_transient($transient_id, ['progress' => 100, 'message' => 'Failed to import.'], 60);
                    wp_send_json_error('Failed to import: ' . esc_html($result->get_error_message()));
                }

                $imported_template_id = $result[0]['template_id'] ?? null;
                if (!$imported_template_id) {
                    wp_send_json_error('Import failed: No template ID.');
                }

                set_transient($transient_id, ['progress' => 50, 'message' => 'Template imported.'], 60);
                wp_send_json_success([
                    'next' => 'metadata',
                    'template_id' => $imported_template_id,
                    'json_data' => base64_encode(json_encode($json_data)),
                    'result' => $result,
                    'progress' => 60
                ]);
                break;

            // ------------------------------------------------
            // STEP 4: UPDATE METADATA (GLOBAL STYLES / KIT)
            // ------------------------------------------------
            case 'metadata':
                $imported_template_id = intval($_POST['template_id']);
                $json_data = base64_decode($_POST['json_data']);
                $template_data = json_decode($json_data, true);

                if (!empty($template_data['metadata']['template_type']) && $template_data['metadata']['template_type'] === 'global-styles') {
                    update_post_meta($imported_template_id, '_elementor_edit_mode', 'builder');
                    update_post_meta($imported_template_id, '_elementor_template_type', 'kit');
                    update_option('elementor_active_kit', $imported_template_id);

                    wp_update_post([
                        'ID' => $imported_template_id,
                        'post_title' => 'Kit Styles: ' . sanitize_text_field(\RTMKit\Modules\Templatekits\TemplatekitModule::instance()->get_template_name($template)),
                    ]);
                }

                if (isset($_POST['import_as_page']) && $_POST['import_as_page'] === '1') {
                    if (!empty($template_data['metadata']['template_type'])) {
                        switch ($template_data['metadata']['template_type']) {
                            case 'single-page':
                                wp_update_post([
                                    'ID' => $imported_template_id,
                                    'post_type' => 'page'
                                ]);
                                break;
                            case 'single-post':
                                wp_update_post([
                                    'ID' => $imported_template_id,
                                    'post_type' => 'rometheme_template',
                                    'meta_input' => [
                                        'rometheme_template_type' => 'single_post',
                                        'rometheme_template_active' => 'true'
                                    ]
                                ]);
                                break;
                            case 'single-404':
                                if (class_exists('RTMKitPro\\Core\\Plugin') && \RTMKitPro\Modules\Licenses\LicenseStorage::instance()->isLicenseActive()) {
                                    wp_update_post([
                                        'ID' => $imported_template_id,
                                        'post_type' => 'rometheme_template',
                                        'meta_input' => [
                                            'rometheme_template_type' => 'error_404',
                                            'rometheme_template_active' => 'true'
                                        ]
                                    ]);
                                }
                                break;
                            case 'single-product':
                                break;
                            case 'archive-blog':
                                if (class_exists('RTMKitPro\\Core\\Plugin') && \RTMKitPro\Modules\Licenses\LicenseStorage::instance()->isLicenseActive()) {
                                    wp_update_post([
                                        'ID' => $imported_template_id,
                                        'post_type' => 'rometheme_template',
                                        'meta_input' => [
                                            'rometheme_template_type' => 'archive_post',
                                            'rometheme_template_active' => 'true'
                                        ]
                                    ]);
                                }
                                break;
                            case 'archive-search':
                                if (class_exists('RTMKitPro\\Core\\Plugin') && \RTMKitPro\Modules\Licenses\LicenseStorage::instance()->isLicenseActive()) {
                                    wp_update_post([
                                        'ID' => $imported_template_id,
                                        'post_type' => 'rometheme_template',
                                        'meta_input' => [
                                            'rometheme_template_type' => 'search',
                                            'rometheme_template_active' => 'true'
                                        ]
                                    ]);
                                }
                                break;
                            case 'section-header':
                                wp_update_post([
                                    'ID' => $imported_template_id,
                                    'post_type' => 'rometheme_template',
                                    'meta_input' => [
                                        'rometheme_template_type' => 'header',
                                        'rometheme_template_active' => 'true'
                                    ]
                                ]);
                                break;
                            case 'section-footer':
                                wp_update_post([
                                    'ID' => $imported_template_id,
                                    'post_type' => 'rometheme_template',
                                    'meta_input' => [
                                        'rometheme_template_type' => 'footer',
                                        'rometheme_template_active' => 'true'
                                    ]
                                ]);
                                break;
                            case 'section-other':
                                break;
                            default:
                                break;
                        }
                    }
                } else {
                    // nothing to do
                }

                // ✅ Tambahan yang sebelumnya ketinggalan
                set_transient($transient_id, ['progress' => 75, 'message' => 'Updating history...'], 60);
                $result = $_POST['result'] ?? [];
                if (is_string($result)) {
                    $result = json_decode(stripslashes($result), true);
                }

                // Simpan riwayat template import
                $history = get_option('rtm_import_template_' . $template, []);
                $key = html_entity_decode($result[0]['title'] ?? $template_name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $key = str_replace(["–", "—", "−"], "-", $key);
                $key = str_replace([' ', '_'], '_', $key);
                $key = strtolower($key);

                $history[$key] = $imported_template_id;
                update_option('rtm_import_template_' . $template, $history);

                // Tambahkan edit & delete URL
                $edit_url   = admin_url('post.php?post=' . $imported_template_id . '&action=elementor');
                $delete_url = get_delete_post_link($imported_template_id);

                set_transient($transient_id, [
                    'progress' => 90,
                    'message' => 'Template metadata & history saved.'
                ], 60);

                wp_send_json_success([
                    'next' => 'finish',
                    'template_id' => $imported_template_id,
                    'edit_url' => $edit_url,
                    'delete_url' => $delete_url,
                    'progress' => 90
                ]);
                break;

            // ------------------------------------------------
            // STEP 5: FINISH
            // ------------------------------------------------
            case 'finish':
                set_transient($transient_id, [
                    'progress' => 100,
                    'message' => 'Import completed successfully!'
                ], 60);

                // Bisa dihapus jika ingin progress hilang otomatis
                // delete_transient($transient_id);

                wp_send_json_success([
                    'done' => true,
                    'message' => 'Import finished!',
                    'progress' => 100
                ]);
                break;
        }

        wp_die();
    }

    public function delete_installed_template()
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Insufficient permissions', 403);
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

    private function replace_images_with_placeholder(&$data)
    {
        if (is_array($data)) {
            foreach ($data as $key => &$value) {
                if (is_array($value)) {
                    $this->replace_images_with_placeholder($value);
                } elseif (
                    is_string($value)
                    && preg_match('/\.(jpe?g|png|gif|webp|svg)(\?.*)?$/i', $value)
                    && strpos($value, 'http') === 0
                ) {
                    $value = ELEMENTOR_ASSETS_URL . '/images/placeholder.png';;
                }
            }
        }
    }

    private function process_mask_images_recursively(&$data)
    {
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        foreach ($data as &$item) {
            if (is_array($item)) {
                if (isset($item['_mask_image']['url'])) {
                    $url = $item['_mask_image']['url'];

                    $attachment_id = media_sideload_image($url, 0, null, 'id');

                    if (!is_wp_error($attachment_id)) {
                        $new_url = wp_get_attachment_url($attachment_id);

                        $item['_mask_image']['id'] = $attachment_id;
                        $item['_mask_image']['url'] = $new_url;
                        $item['_mask_image']['source'] = 'library';
                    }
                } else {
                    $this->process_mask_images_recursively($item);
                }
            }
        }
    }

    public function send_themeforest_stats()
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');
        $api_handler = \RTMKit\Modules\Helper\APIHandler::instance();

        if ($_POST['click'] === 'preview') {
            $endpoint = 'wp-json/public/themeforest_click_preview';
        } else {
            $endpoint = 'wp-json/public/themeforest_click_install';
        }

        $endpoint .= '?themeforest_id=' . intval($_POST['themeforest_id']);
        $endpoint .= '&name=' . sanitize_text_field($_POST['name']);
        $endpoint .= '&url=' . esc_url_raw($_POST['url']);
        $response = $api_handler->remote($endpoint, [], null, true, false, 'POST');
        wp_send_json_success($endpoint);
        wp_die();
    }
}
