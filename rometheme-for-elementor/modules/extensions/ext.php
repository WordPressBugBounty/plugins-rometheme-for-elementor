<?php


namespace RomethemeKit;

class RTMExtension
{
    public function __construct()
    {
        $this->update_ext_opt();
        add_action('wp_ajax_save_extensions', [$this, 'save_extensions']);
        add_action('rtmkit_register_extension', [$this, 'register_extension']);
        do_action('rtmkit_register_extension');
        // var_dump($this->get_extension());
    }

    public static function get_extension()
    {
        $extensionsFileJson     = file_get_contents(\RomeTheme::plugin_dir() . '/assets/js/extensions.json');
        $extensionsProFileJson  = file_exists(\RomeTheme::plugin_dir() . '/assets/js/extensions-pro.json')
            ? file_get_contents(\RomeTheme::plugin_dir() . '/assets/js/extensions-pro.json')
            : '{}';

        $extensions    = json_decode($extensionsFileJson, true);
        $extensionsPro = json_decode($extensionsProFileJson, true);

        // Gabungkan dua array tanpa overwrite jika key sama
        $merged = array_merge($extensions, array_diff_key($extensionsPro, $extensions));

        // Sorting berdasarkan nama
        uasort($merged, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return $merged;
    }


    private function update_ext_opt()
    {
        $ext = get_option('rtm_extensions');

        if ($ext == false) {
            add_option('rtm_extensions', $this->get_extension());
        }
    }

    public function register_extension()
    {
        $exts = get_option('rtm_extensions', []);

        $ext_dir = \Rometheme::module_dir() . 'extensions/';
        $files = scandir($ext_dir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'php' && $file !== 'ext.php') {
                require_once $ext_dir . $file;
                error_log("Loaded file: " . $file);
            }
        }

        foreach ($exts as $key => $ext) {
            if ($ext['status']) {
                $class = '\\RomethemeKit\\' . $ext['classname'];
                if (class_exists($class)) {
                    new $class();
                    error_log("Instantiated class: $class");
                } else {
                    error_log("Class not found: $class");
                }
            }
        }
    }

    function save_extensions()
    {
        $data = $_POST;
        $options = get_option('rtm_extensions');

        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'widget-options-nonce')) {
            wp_send_json_error('Invalid nonce.');
            wp_die();
        }

        if (!current_user_can('manage_options')) {
            wp_die();
        }

        unset($data['action']);
        unset($data['nonce']);

        foreach ($data as $key => $value) {
            $options[$key]['status'] = ($value == "true") ? true : false;
        }

        $update = update_option('rtm_extensions', $options);

        if ($update) {
            wp_send_json_success('success');
        } else {
            wp_send_json_error('errorrr');
        }
    }
}
