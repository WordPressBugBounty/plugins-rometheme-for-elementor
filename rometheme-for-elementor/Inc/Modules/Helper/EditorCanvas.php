<?php

namespace RTMKit\Modules\Helper;

class EditorCanvas
{
    private static $instance;
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        add_action('elementor/preview/enqueue_styles', [$this, 'preview_styles']);
        add_action('elementor/editor/after_enqueue_styles', [$this, 'editor_styles']);
        add_action('elementor/editor/before_enqueue_scripts', [$this, 'editor_scripts']);
        add_action('elementor/editor/footer', array($this, 'script_var'));
        if (wp_doing_ajax()) {
            add_action('wp_ajax_fetch_layout_lib', [$this, 'fetch_layout_lib']);
            add_action('wp_ajax_fetch_lib', [$this, 'fetch_lib']);
            add_action('wp_ajax_template_category', [$this, 'template_category']);
            add_action('wp_ajax_get_installed_templates', [$this, 'get_installed_templates']);
            add_action('wp_ajax_get_installed_template', [$this, 'get_installed_template']);
            add_action('wp_ajax_get_template_content', [$this, 'get_template_content']);
            add_action('wp_ajax_is_pro_active' , [$this , 'is_pro_active']);
        }
    }

    public function is_pro_active() {
        check_ajax_referer('rtmkit_nonce' , 'nonce');

        wp_send_json_success(\RTMKit\Core\Plugin::instance()->pro_is_active());
    }

    public function template_category()
    {
        wp_send_json_success(\RTMKit\Modules\Templatekits\TemplatekitAPI::instance()->get_template_categories());
    }

    public function fetch_lib()
    {
        wp_send_json_success(\RTMKit\Modules\Templatekits\TemplatekitAPI::instance()->_get_template_data('templatekits'));
    }

    public function get_installed_templates()
    {
        // SECURITY FIX: Add capability check to prevent IDOR vulnerability
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access Denied: Insufficient permissions');
            wp_die();
        }

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
                'image_preview_url' =>  \RTMKit\Modules\Templatekits\TemplatekitModule::instance()->get_template_image_preview_url($id),
                'preview_url' => $preview
            ];
        }

        wp_send_json_success($data);
    }

    public function get_installed_template()
    {
        // SECURITY FIX: Add capability check to prevent IDOR vulnerability
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access Denied: Insufficient permissions');
            wp_die();
        }

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
            "description" => \RTMKit\Modules\Templatekits\TemplatekitModule::instance()->get_template_description(\RTMKit\Modules\Templatekits\TemplatekitModule::instance()->get_installed_template_id($hashId))
        ];
        wp_send_json_success($data);
    }
    public function preview_styles()
    {
        wp_enqueue_style('rkit-preview-style', RTM_KIT_URL . 'assets/css/preview.css');
    }

    public function editor_styles()
    {
        wp_enqueue_style('rkit-library-style', RTM_KIT_URL . 'assets/css/style.css');
    }

    public function editor_scripts()
    {
        $template_nonce =  wp_create_nonce('rtm_template_nonce');
        wp_enqueue_script('rkit-js', RTM_KIT_URL . 'assets/js/rkit.js', [], RTM_KIT_VERSION, true);
        wp_enqueue_script('rkit-library-script', RTM_KIT_URL . 'assets/js/script.js', ['jquery'], RTM_KIT_VERSION);
        wp_localize_script('rkit-library-script', 'rkit_libs', [
            'logo_url' => RTM_KIT_URL . '/assets/images/romethemekit.svg',
            'ajax_url' => admin_url('admin-ajax.php'),
            'template_nonce' => $template_nonce,
            'rtmkit_nonce' => wp_create_nonce('rtmkit_nonce')
        ]);
    }

    public function script_var()
    {
?>

        <script type="text/javascript">
            var rkitLO = {
                "btnIcon": "<?php echo esc_url(RTM_KIT_URL . '/assets/images/romethemekit.svg'); ?>",
                "api_url": "<?php echo esc_url("https://api.rometheme.pro/") ?>",
                "default_tab": "template"
            };
        </script>

<?php
    }

    public function fetch_layout_lib()
    {

        if (!isset($_GET['wpnonce']) || !wp_verify_nonce($_GET['wpnonce'], 'rtm_template_nonce')) {
            wp_send_json_error('Access Denied');
            wp_die();
        }

        $url = "https://api.rometheme.pro/wp-json/public/get_layout_api/";
        $ck = 'ck_p2ke51ckfmb42kefnw67krk93wwjawj6';
        $cs = 'cs_djg1rrp51rn6hvj5ck76x75u99ec8e19';

        if (isset($_GET['id'])) {
            $url .= '?id=' . $_GET['id'];
        }

        $ch = curl_init();
        // Header untuk meminta respons JSON
        $headers = [
            'Accept: application/json'
        ];
        // Atur opsi cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$ck:$cs");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Eksekusi permintaan
        $response = json_decode(curl_exec($ch), true);

        if (curl_errno($ch)) {
            wp_send_json_error('Error:' . curl_error($ch));
        } else {
            wp_send_json($response);
        }
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
}
