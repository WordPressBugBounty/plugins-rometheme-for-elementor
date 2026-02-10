<?php

namespace RTMKit\Modules\Templatekits;

use RTMKit\Modules\Templatekits\TemplatekitAPI;

class TemplatekitModule
{
    private static ?TemplatekitModule $instance = null;

    public static function instance(): TemplatekitModule
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        $active_templatekit = \RTMKit\Modules\Storage::instance()->get_active_modules(['key' => 'template']);
        if ($active_templatekit) {
            TemplatekitAPI::instance()->init();
            add_action('init', [$this, 'init_template_dir']);
        }
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
    private function _get_template_item_data($id)
    {
        if (empty($id)) {
            return null;
        }
        $api_handler = \RTMKit\Modules\Helper\APIHandler::instance();
        $url = 'wp-json/public/template_lib?id=' . urlencode($id);

        $response = $api_handler->remote($url, [], null, true , true);
        return $response;
    }
    public function get_template_description($id)
    {
        return $this->_get_template_description($id);
    }

    public function get_template_image_preview_url($id)
    {
        $res = $this->_get_template_item_data($id);
        return $res['preview_image_url'];
    }

    public function get_template_content()
    {
        if (!isset($_POST['wpnonce']) ||  ! check_ajax_referer('rtm_template_nonce', 'wpnonce')) {
            wp_send_json_error('Access Denied');
            wp_die();
        }

        // SECURITY FIX: Add capability check to prevent IDOR vulnerability
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access Denied: Insufficient permissions');
            wp_die();
        }

        $id = absint($_POST['template']);

        $elementorData = get_post_meta($id, '_elementor_data', true);

        $data = ['content' => json_decode($elementorData)];

        wp_send_json_success($data);
    }

    private function _get_template_description($id)
    {
        $response = $this->_get_template_item_data($id);
        return $response['description'];
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

    public function get_installed_template_id($template)
    {
        $installed_template = get_option('rtm_template_installed', []);

        foreach ($installed_template as $k => $v) {
            if ($k === $template) {
                return $v['template_id'];
            }
        }
    }

    public function missing_plugins($required)
    {
        $missing = [];

        foreach ($required as $plugin) {
            if (!is_plugin_active($plugin->file)) {
                array_push($missing, $plugin);
            }
        }
        return $missing;
    }

    public function normalize_dash_key($key)
    {
        // Semua variasi dash jadi ASCII "-"
        $dashes = ["–", "—", "−", "‒", "‐", "-", "﹘", "﹣", "－"];
        $key = str_replace($dashes, "-", $key);

        // Hapus spasi aneh atau invisible character
        $key = preg_replace('/\p{Cf}+/u', '', $key);

        return $key;
    }

    public function get_template_name($hashId)
    {
        $upload_dir = wp_upload_dir();
        $rtmTemplateDir = $upload_dir['basedir'] . '/rometheme_template';

        $manifest = json_decode(file_get_contents($rtmTemplateDir . '/' . $hashId . '/manifest.json'));

        return $manifest->title;
    }
}
