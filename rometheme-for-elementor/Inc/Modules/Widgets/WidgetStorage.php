<?php

namespace RTMKit\Modules\Widgets;

if (!defined('ABSPATH'))
    exit;

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
            if (isset($widget['status']) && $widget['status']) {
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
        $widget_data = $this->get_widget_data();

        ksort($active_widgets);

        foreach ($active_widgets as $key => $widget) {

            if (isset($widget['type']) && $widget['type'] === 'pro') {
                continue;
            }

            if (isset($widget['type']) && $widget['type'] === 'form') {
                continue;
            }

            if (!isset($widget_data[$key])) {
                continue;
            }

            $className = ($widget_data[$key]['category'] === 'header')
                ? 'RTMKit\Widgets\\' . $widget_data[$key]['class_name']
                : 'RTMKit\Elements\\' . $widget_data[$key]['class_name'];

            $className = apply_filters('rtmkit_widget_class_name', $className, $key, $widget_data[$key]);

            $requiredPlugins = isset($widget_data[$key]['plugin_required']) ? $widget_data[$key]['plugin_required'] : null;

            if ($this->is_required_plugins_active($requiredPlugins) && class_exists($className)) {
                $widgets_manager->register(new $className());
            }
        }

        /**
         * Action setelah widget RTMKit diregistrasi ke Elementor.
         *
         * @since 1.0.0
         *
         * @param \Elementor\Widgets_Manager $widgets_manager Widget manager Elementor.
         */
        do_action('rtmkit_after_register_widgets', $widgets_manager);

        // Kalau Pro aktif DAN salah satu companion aktif, tidak perlu load
        // widget locked sama sekali di sini — tiap companion meregistrasi
        // widget aslinya sendiri.
        if (\RTMKit\Core\Plugin::instance()->pro_is_active() && $this->is_pro_with_companion_active()) {
            // Tetap lanjut ke bawah untuk menangani widget yang companion-nya
            // BELUM aktif (mixed state: salah satu companion aktif, satu lagi belum).
        }

        $json_path = RTM_KIT_DIR . 'metadata/rtmwp.json';
        if (file_exists($json_path)) {
            $pro_widgets = json_decode(file_get_contents($json_path), true);

            if (!empty($pro_widgets) && is_array($pro_widgets)) {
                foreach ($pro_widgets as $key => $data) {

                    if (!is_array($data))
                        continue;

                    $companion = isset($data['plugin_required']) ? $data['plugin_required'] : null;

                    // Skip: kalau Pro aktif DAN companion widget ini spesifik sudah aktif,
                    // widget aslinya akan diregistrasi oleh companion plugin itu sendiri,
                    // jadi jangan daftarkan versi locked-nya di sini.
                    if ($this->is_pro_with_companion_active($companion)) {
                        continue;
                    }

                    $widgets_manager->register(new \RTMKit\Elements\DynamicWidgetPro([], [
                        'widget_key' => $key,
                        'widget_data' => $data,
                        'is_pro_active' => false
                    ]));
                }
            }
        }

        $json_path = RTM_KIT_DIR . 'metadata/post_widget_pro.json';
        if (file_exists($json_path)) {
            $pro_widgets = json_decode(file_get_contents($json_path), true);

            if (!empty($pro_widgets) && is_array($pro_widgets)) {
                foreach ($pro_widgets as $key => $data) {

                    if (!is_array($data))
                        continue;

                    $companion = isset($data['plugin_required']) ? $data['plugin_required'] : null;

                    if ($this->is_pro_with_companion_active($companion)) {
                        continue;
                    }

                    $widgets_manager->register(new \RTMKit\Elements\DynamicWidgetSinglePro([], [
                        'widget_key' => $key,
                        'widget_data' => $data,
                        'is_pro_active' => false
                    ]));
                }
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
            return apply_filters('rtmkit_widget_data_free', $widgetsFree);
        } elseif ($type === 'pro') {
            return apply_filters('rtmkit_widget_data_pro', $widgetsPro);
        } elseif ($type === 'form') {
            return apply_filters('rtmkit_widget_data_form', $formWidgets);
        }

        $all_widgets = array_merge($widgetsFree, $widgetsPro ?? [], $formWidgets ?? []);

        return apply_filters('rtmkit_widget_data', $all_widgets, $type);
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
        check_ajax_referer('rtmkit_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access Denied.');
            wp_die();
        }

        $rawInput = file_get_contents('php://input');
        $dataJson = json_decode($rawInput, true);

        if (!is_array($dataJson)) {
            wp_send_json_error([
                'message' => 'Invalid JSON format',
                'raw' => $rawInput
            ], 400);
        }

        $plugin = sanitize_text_field($_GET['type']);

        $update = $this->save_widget_options($plugin, $dataJson);
        if ($update) {
            \RTMKit\Modules\Helper\CacheManager::instance()->clear_all();

            $message = sprintf(
                /* translators: %s: plugin name. */
                __('Widget options for %s have been successfully updated.', 'rometheme-for-elementor'),
                $this->get_plugin_name($plugin)
            );
            wp_send_json_success(['message' => $message]);
        } else {
            $message = sprintf(
                /* translators: %s: plugin name. */
                __('No changes were saved. %s Widget options are already current.', 'rometheme-for-elementor'),
                $this->get_plugin_name($plugin)
            );
            wp_send_json_error(['message' => $message]);
        }
    }

    private function save_widget_options($plugin, $data)
    {
        // Blok update ke option pro kalau Pro tidak aktif sama sekali.
        // Catatan: di sini kita cek generik (tanpa companion tertentu) karena
        // payload $data bisa berisi campuran widget dari companion berbeda;
        // masing-masing key sudah tervalidasi dari sisi UI (switcher hanya
        // menampilkan toggle untuk widget yang companion-nya aktif).
        if ($plugin === 'pro' && !\RTMKit\Core\Plugin::instance()->pro_is_active()) {
            return false;
        }

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

        $currentOptions = get_option($optionName, []);

        foreach ($data as $key => $value) {
            $widgetKey = sanitize_key($key);

            // Untuk widget pro, hanya izinkan update kalau companion
            // pemilik widget tersebut memang aktif.
            if ($plugin === 'pro') {
                $widgetMeta = $this->get_widget_data_by_key($widgetKey);
                $companion = isset($widgetMeta['plugin_required']) ? $widgetMeta['plugin_required'] : null;

                if (!$this->is_pro_with_companion_active($companion)) {
                    continue;
                }
            }

            $currentOptions[$widgetKey]['status'] = (bool) $value;
        }

        return update_option($optionName, $currentOptions);
    }

    public function get_widget_options()
    {
        $widgetsFree = get_option('rkit-widget-options', []);

        // Pro widgets tetap ditampilkan di Widget Switcher (locked/crown)
        // walau companion-nya belum aktif. Filtering "locked vs unlocked"
        // dilakukan di level render widget (Pro_Widget_Base), bukan di sini.
        if (\RTMKit\Core\Plugin::instance()->pro_is_active()) {
            $widgetsPro = get_option('rkit-widget-pro-options', []);
            $widgetsFree = array_merge($widgetsFree, $widgetsPro);
        }

        if (class_exists('RomethemeForm')) {
            $formWidgets = get_option('rform-widget-options', []);
            $widgetsFree = array_merge($widgetsFree, $formWidgets);
        }

        /**
         * Filter daftar widget options (status aktif) yang dibaca dari database.
         *
         * @since 1.0.0
         *
         * @param array $widgetsFree Daftar widget beserta status aktifnya.
         */
        return apply_filters('rtmkit_widget_options', $widgetsFree);
    }

    /**
     * Cek apakah Pro aktif DAN companion plugin terkait aktif.
     *
     * @param string|null $companion 'rtm-elements' | 'rtm-blog' | null.
     *                                 null = cek generik (salah satu companion aktif).
     */
    public function is_pro_with_companion_active($companion = null)
    {
        $isProActive = \RTMKit\Core\Plugin::instance()->pro_is_active();

        if (!$isProActive) {
            return false;
        }

        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        if ($companion === 'rtm-elements') {
            return is_plugin_active('rtm-elements/rtm-elements.php');
        }

        if ($companion === 'rtm-blog') {
            return is_plugin_active('rtm-blog/rtm-blog.php');
        }

        // Companion 'woocommerce' bukan bagian dari pengecekan ini —
        // widget woocommerce sudah digating terpisah lewat class_exists('WooCommerce').
        if ($companion !== null) {
            // Companion tidak dikenal / tidak butuh companion khusus
            return false;
        }

        return is_plugin_active('rtm-elements/rtm-elements.php')
            || is_plugin_active('rtm-blog/rtm-blog.php');
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
        check_ajax_referer('rtmkit_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Access Denied.');
            wp_die();
        }

        delete_option('rkit-widget-options');

        if (class_exists('RTMKitPro\Modules\Widgets\WidgetStorage')) {
            delete_option('rkit-widget-pro-options');
            \RTMKitPro\Modules\Widgets\WidgetStorage::instance()->update_option_pro();
        }

        if (\RTMKit\Core\Plugin::instance()->has_rtmform()) {
            delete_option('rform-widget-options');
        }

        $this->update_widgets_options();

        \RTMKit\Modules\Helper\CacheManager::instance()->clear_all();

        wp_send_json_success([
            'message' => __('All widgets have been reset to default settings.', 'rometheme-for-elementor')
        ]);
    }

    protected function is_required_plugins_active($required)
    {
        if (empty($required)) {
            return true; // tidak ada dependency, selalu boleh
        }

        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $required = (array) $required; // dukung string tunggal maupun array

        foreach ($required as $slug) {
            $pluginFile = $this->get_plugin_file_by_slug($slug);

            // Slug tidak dikenal di mapping → anggap tidak terpenuhi (fail-safe)
            if ($pluginFile === null || !is_plugin_active($pluginFile)) {
                return false;
            }
        }

        return true; // semua required plugin aktif (AND, bukan OR)
    }

    protected function get_plugin_file_by_slug($slug)
    {
        $map = [
            'woocommerce' => 'woocommerce/woocommerce.php',
            'rtm-elements' => 'rtm-elements/rtm-elements.php',
            'rtm-blog' => 'rtm-blog/rtm-blog.php',
        ];

        /**
         * Filter mapping slug plugin_required ke path file plugin.
         * Memungkinkan plugin lain (companion baru) mendaftarkan slug-nya sendiri.
         *
         * @since 1.0.0
         *
         * @param array $map Slug => plugin file path.
         */
        $map = apply_filters('rtmkit_plugin_required_map', $map);

        return isset($map[$slug]) ? $map[$slug] : null;
    }
}