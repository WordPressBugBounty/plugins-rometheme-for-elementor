<?php

namespace RTMKit\Modules\Widgets;


class WidgetStorage
{
    private static $instance;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init(): void
    {
        if (wp_doing_ajax()) {
            add_action('wp_ajax_save_widget', [$this, 'save_widget']);
            add_action('wp_ajax_reset_all_widgets', [$this, 'reset_all_widgets']);
        }
        add_action('elementor/widgets/register', [$this, 'register_widget']);
        $this->update_widgets_options();
    }

    public function get_active_widgets($type = null)
    {
        $all_widgets = $this->get_widget_options();
        $active_widgets = [];

        foreach ($all_widgets as $key => $widget) {
            if ($widget['status']) {
                $active_widgets[$key] = $widget;
            }
        }

        if ($type) {
            $filtered = [];
            foreach ($active_widgets as $key => $widget) {
                $widgetData = $this->get_widget_data_by_key($key);
                if (isset($widgetData['type']) && $widgetData['type'] === $type) {
                    $filtered[$key] = $widget;
                }
            }
            return $filtered;
        }

        return $active_widgets;
    }

    public function register_widget($widgets_manager)
    {

        $widget_lists = get_option('rkit-widget-options');
        $active_widgets = $this->get_active_widgets();
        $widget_data = $this->get_widget_data('free');

        ksort($active_widgets);

        foreach ($active_widgets as $key => $widget) {

            if ($widget['type'] === 'pro') {
                continue;
            }

            if ($widget['type'] === 'form') {
                continue;
            }
            $className = ($widget_data[$key]['category'] === 'header') ? 'RTMKit\Widgets\\' . $widget_data[$key]['class_name'] : 'RTMKit\Elements\\' . $widget_data[$key]['class_name'];

            if (isset($widget_data[$key]['plugin_required']) && $widget_data[$key]['plugin_required'] === 'woocommerce') {
                if (class_exists('WooCommerce')) {
                    $widgets_manager->register(new $className());
                }
            } else {
                $widgets_manager->register(new $className());
            }
        }
    }

    protected function update_widgets_options()
    {
        $freeWidgetOption = get_option('rkit-widget-options');

        if ($freeWidgetOption == false) {
            update_option('rkit-widget-options', $this->get_widget_by_type('free'));
        } else {
            if (count($freeWidgetOption) !== count($this->get_widget_by_type('free'))) {
                update_option('rkit-widget-options', $this->get_widget_by_type('free'));
            }
        }

        if (\RTMKit\Core\Plugin::instance()->has_rtmform()) {
            $formWidgetOption = get_option('rform-widget-options');
            if ($formWidgetOption == false) {
                update_option('rform-widget-options', $this->get_widget_by_type('form'));
            } else {
                if (count($formWidgetOption) !== count($this->get_widget_by_type('form'))) {
                    update_option('rform-widget-options', $this->get_widget_by_type('form'));
                }
            }
        }
    }

    public function get_widget_data($type = null)
    {
        $widgetsFreeFileJson = RTM_KIT_DIR . '/metadata/widgets.json';
        $widgetsProFileJson = RTM_KIT_DIR . 'metadata/rtmwp.json';
        $formWidgetsFileJson = RTM_KIT_DIR . '/metadata/form_widgets.json';
        $formWidgets = file_exists($formWidgetsFileJson) ? json_decode(file_get_contents($formWidgetsFileJson), true) : [];
        $widgetsFree = file_exists($widgetsFreeFileJson) ? json_decode(file_get_contents($widgetsFreeFileJson), true) : [];
        $widgetsPro = file_exists($widgetsProFileJson) ? json_decode(file_get_contents($widgetsProFileJson), true) : [];

        if ($type === 'free') {
            return $widgetsFree;
        } elseif ($type === 'pro') {
            return $widgetsPro;
        } elseif ($type === 'form') {
            return $formWidgets;
        }

        return array_merge($widgetsFree, $widgetsPro, $formWidgets);
    }

    public function get_widget_data_by_key($key)
    {
        $data = $this->get_widget_data();
        return isset($data[$key]) ? $data[$key] : null;
    }

    protected function get_free_widget_metadata()
    {
        $widgetsFreeFileJson = RTM_KIT_DIR . '/metadata/widgets.json';
        $widgetsFree = file_exists($widgetsFreeFileJson) ? json_decode(file_get_contents($widgetsFreeFileJson), true) : [];
        return $widgetsFree;
    }

    public function get_widget_category()
    {
        $data = $this->get_widget_data();
        $categories = [];

        foreach ($data as $widget) {
            if (isset($widget['category'])) {
                $categories[] = $widget['category'];
            }
        }

        return array_unique($categories);
    }

    public function get_widget_by_category(string $category)
    {
        $data = $this->get_widget_data();
        $filtered = [];

        foreach ($data as $key => $widget) {
            if (isset($widget['category']) && $widget['category'] === $category) {
                $filtered[$key] = $widget;
            }
        }
        return $filtered;
    }

    public function get_widget_by_type(string $type)
    {
        $data = $this->get_widget_data();
        $filtered = [];

        foreach ($data as $key => $widget) {
            if (isset($widget['type']) && $widget['type'] === $type) {
                $filtered[$key] = $widget;
            }
        }
        return $filtered;
    }

    public function save_widget()
    {
        // Cek keamanan nonce
        check_ajax_referer('rtmkit_nonce', 'nonce');

        // Ambil body JSON
        $rawInput = file_get_contents('php://input');
        $dataJson = json_decode($rawInput, true);

        // Validasi format JSON
        if (!is_array($dataJson)) {
            wp_send_json_error([
                'message' => 'Invalid JSON format',
                'raw'     => $rawInput
            ], 400);
        }

        $plugin = sanitize_text_field($_GET['type']);

        $update = $this->save_widget_options($plugin, $dataJson);
        if ($update) {
            $message = sprintf(
                __('Widget options for %s have been successfully updated.', 'rometheme-for-elementor'),
                $this->get_plugin_name($plugin)
            );
            wp_send_json_success(['message' => $message]);
        } else {
            $message = sprintf(
                __('No changes were saved. %s Widget options are already current.', 'rometheme-for-elementor'),
                $this->get_plugin_name($plugin)
            );
            wp_send_json_error([
                'message' => __($message, 'rometheme-for-elementor')
            ]);
        }

        // wp_send_json($dataJson);
    }

    private function save_widget_options($plugin, $data)
    {
        // Tentukan nama option
        switch ($plugin) {
            case 'form':
                $optionName = 'rform-widget-options';
                break;
            case 'pro':
                $optionName = 'rkit-widget-pro-options';
                break;
            case 'free':
                $optionName = 'rkit-widget-options';
                break;
            default:
                $optionName = 'rkit-widget-options';
        }

        // Ambil data lama
        $currentOptions = get_option($optionName, []);

        // Update status tiap widget
        foreach ($data as $key => $value) {
            $widgetKey = sanitize_key($key); // pastikan aman
            $currentOptions[$widgetKey]['status'] = (bool) $value; // simpan boolean
        }

        // Simpan ke DB
        return update_option($optionName, $currentOptions);
    }


    public function get_widget_options()
    {
        $widgetsFree = get_option('rkit-widget-options', []);

        if (class_exists('RTMKitPro\Modules\Licenses\LicenseStorage')) {
            if (\RTMKitPro\Modules\Licenses\LicenseStorage::instance()->isLicenseActive()) {
                $widgetsPro = get_option('rkit-widget-pro-options', []);
                $widgetsFree = array_merge($widgetsFree, $widgetsPro);
            }
        }

        if (class_exists('RomethemeForm')) {
            $formWidgets = get_option('rform-widget-options', []);
            $widgetsFree = array_merge($widgetsFree, $formWidgets);
        }
        return $widgetsFree;
    }

    private function get_plugin_name($plugin)
    {
        $plugin = sanitize_key($plugin);
        if ($plugin == 'form') {
            return 'RTMForm';
        } elseif ($plugin == 'pro') {
            return 'RTMKitPro';
        }
        return 'RTMKit';
    }

    public function reset_all_widgets()
    {
        // Cek keamanan nonce
        check_ajax_referer('rtmkit_nonce', 'nonce');

        delete_option('rkit-widget-options');

        if (class_exists('RTMKitPro\Modules\Widgets\WidgetStorage')) {
            delete_option('rkit-widget-pro-options');
            \RTMKitPro\Modules\Widgets\WidgetStorage::instance()->update_option_pro();
        }

        if (\RTMKit\Core\Plugin::instance()->has_rtmform()) {
            delete_option('rform-widget-options');
        }

        // Reset widget options
        $this->update_widgets_options();

        // Kirim respons sukses
        wp_send_json_success([
            'message' => __('All widgets have been reset to default settings.', 'rometheme-for-elementor')
        ]);
    }
}
