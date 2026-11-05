<?php

namespace RTMKit\Modules\Widgets;

if (!defined('ABSPATH'))
    exit;

class WidgetStorage
{
    const DEMO_URL_ELEMENTS = 'https://rometheme.net/plugins/elements/';
    const DEMO_URL_WOO = 'https://rometheme.net/plugins/rtm-woo/';
    const DEMO_URL_FORM = 'https://rometheme.net/plugins/rtm-form/';

    /**
     * Halaman demo yang ada di rometheme.net/plugins/elements/. Widget lain
     * diarahkan ke halaman daftarnya.
     */
    const DEMO_SLUGS = [
        'accordion', 'advanced-button', 'advanced-cards', 'advanced-categories', 'advanced-heading',
        'advanced-hero-slider', 'advanced-tabs', 'advanced-timeline', 'advanced-toggle', 'animated-circle-button',
        'animated-heading', 'audio-player', 'back-to-top', 'background-switcher', 'bar-chart', 'blockquote',
        'breadcrumb', 'call-to-action', 'card-carousel', 'client-carousel', 'client-grid', 'countdown', 'counter',
        'dual-button', 'filterable-gallery', 'header-info', 'hero-slider', 'image-accordion', 'image-box',
        'image-comparison', 'image-gallery', 'image-hotspot', 'image-marquee', 'image-stack-info',
        'interactive-link', 'interactive-showcase', 'line-chart', 'lottie', 'media-on-hover', 'mega-menu',
        'modal-box', 'nav-menu', 'offcanvas', 'pie-chart', 'post-block', 'post-carousel', 'post-grid', 'post-list',
        'price-list', 'pricing-image-box', 'pricing-table', 'progress-bar', 'search', 'site-logo', 'social-icons',
        'social-share', 'sub-menu', 'team', 'team-carousel', 'testimonial-carousel', 'text-marquee',
        'video-button', 'woo-product-carousel', 'woo-product-grid',
    ];

    /**
     * Widget yang nama tampilannya beda dengan slug halaman demonya.
     */
    const DEMO_SLUG_ALIASES = [
        'offcanvas' => 'offcanvas',
        'pricelist' => 'price-list',
        'interactivelinks' => 'interactive-link',
        'advanced_card' => 'advanced-cards',
    ];

    /**
     * Widget Single Post yang punya versi free di RTMKit (Inc/Elements/SinglePost).
     * Saat rtm-blog aktif, rtm-blog mendaftarkan ulang key yang sama sebagai Pro.
     */
    const FREE_SINGLE_POST_KEYS = ['posttitle', 'postfeaturedimage', 'postcontent', 'postinfo', 'postcomment', 'postnavigation'];

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
        // Setelah companion (rtm-woo di prioritas 20) selesai meregistrasi widget-nya.
        add_action('elementor/widgets/register', [$this, 'unregister_disabled_pro_widgets'], 100);
        // Paling akhir: panel Elementor mengurutkan widget sesuai urutan registrasi,
        // jadi widget terkunci tampil di bawah widget yang bisa dipakai di kategorinya.
        add_action('elementor/widgets/register', [$this, 'register_locked_widgets'], 1000);
        $this->update_widgets_options();
    }

    /**
     * Companion seperti rtm-woo meregistrasi semua widget-nya tanpa membaca
     * status Widget Switcher, jadi widget yang dimatikan di sana tetap muncul
     * di editor. Di sini widget pro yang statusnya eksplisit false dicabut lagi.
     * Widget tanpa status tersimpan dianggap aktif, sama seperti rtm-elements.
     *
     * @param \Elementor\Widgets_Manager $widgets_manager
     */
    public function unregister_disabled_pro_widgets($widgets_manager)
    {
        if (!\RTMKit\Core\Plugin::instance()->pro_is_active()) {
            return;
        }

        $options = get_option('rkit-widget-pro-options', []);

        foreach ($this->get_widget_data('pro') as $key => $widget) {
            if (empty($widget['widget_name']) || !isset($options[$key]['status']) || $options[$key]['status']) {
                continue;
            }

            // Addon-nya mati: yang terdaftar adalah versi terkunci, dan itu harus
            // tetap tampil sebagai promosi meski toggle-nya pernah dimatikan.
            if (!$this->is_pro_with_companion_active($widget['plugin_required'] ?? null)) {
                continue;
            }

            if ($widgets_manager->get_widget_types($widget['widget_name'])) {
                $widgets_manager->unregister($widget['widget_name']);
            }
        }
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

        // Panel Elementor mengurutkan widget sesuai urutan registrasi: A–Z menurut nama.
        uksort($active_widgets, function ($a, $b) use ($widget_data) {
            return strcasecmp($widget_data[$a]['name'] ?? $a, $widget_data[$b]['name'] ?? $b);
        });

        foreach ($active_widgets as $key => $widget) {

            if (!isset($widget_data[$key])) {
                continue;
            }

            // Tipe widget harus dibaca dari metadata (rtmwp.json), bukan dari
            // $widget yang berasal dari option database, struktur option hanya
            // menyimpan ['status' => bool] sehingga key 'type' tidak pernah ada.
            if (isset($widget_data[$key]['type']) && $widget_data[$key]['type'] === 'pro') {
                continue;
            }

            if (isset($widget_data[$key]['type']) && $widget_data[$key]['type'] === 'form') {
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
    }

    /**
     * Versi terkunci widget addon yang addon-nya belum aktif.
     *
     * @param \Elementor\Widgets_Manager $widgets_manager
     */
    public function register_locked_widgets($widgets_manager)
    {
        $json_path = RTM_KIT_DIR . 'metadata/rtmwp.json';
        if (file_exists($json_path)) {
            $pro_widgets = json_decode(file_get_contents($json_path), true);

            if (!empty($pro_widgets) && is_array($pro_widgets)) {
                foreach ($pro_widgets as $key => $data) {

                    if (!is_array($data))
                        continue;

                    $companion = isset($data['plugin_required']) ? $data['plugin_required'] : null;

                    // Widget Form Pro terkunci hanya sebagai promosi selama RTMForm
                    // belum aktif; setelah RTMForm aktif widget-nya sudah tampil dari
                    // sana, jadi versi terkunci di sini membuatnya dobel.
                    if ('rtm-form-pro' === $companion && \RTMKit\Core\Plugin::instance()->has_rtmform()) {
                        continue;
                    }

                    // Skip: kalau Pro aktif DAN companion widget ini spesifik sudah aktif,
                    // widget aslinya akan diregistrasi oleh companion plugin itu sendiri,
                    // jadi jangan daftarkan versi locked-nya di sini.
                    if ($this->is_pro_with_companion_active($companion)) {
                        continue;
                    }

                    // Jangan timpa widget asli yang sudah terdaftar dengan nama yang sama,
                    // mis. widget Single Post versi free (prioritas 5).
                    if (!empty($data['widget_name']) && $widgets_manager->get_widget_types($data['widget_name'])) {
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

        // Widget RTMForm terkunci selama plugin RTMForm (free) belum terpasang /
        // aktif, supaya pengguna tahu widget form itu ada.
        if (!\RTMKit\Core\Plugin::instance()->has_rtmform() && !$widgets_manager->get_widget_types('rform')) {
            $widgets_manager->register(new \RTMKit\Elements\DynamicWidgetPro([], [
                'widget_key' => 'rform',
                'widget_data' => [
                    'name' => 'RTMForm',
                    'widget_name' => 'rform',
                    'icon' => 'rtmicon rtmicon-form',
                    'elementor_category' => 'romethemeform_form_fields',
                    'lock' => 'rtmform',
                    'docsURL' => self::DEMO_URL_FORM,
                ],
                'is_pro_active' => false
            ]));
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

                    // Jangan timpa widget asli yang sudah terdaftar dengan nama yang sama,
                    // mis. widget Single Post versi free (prioritas 5).
                    if (!empty($data['widget_name']) && $widgets_manager->get_widget_types($data['widget_name'])) {
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
                update_option('rform-widget-options', $this->get_widget_by_category('form'));
            } else {
                if (count($formWidgetOption) !== count($this->get_widget_by_category('form'))) {
                    update_option('rform-widget-options', $this->get_widget_by_category('form'));
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

        $allWidgetFree =  apply_filters('rtmkit_widget_data_free', $widgetsFree);
        $allWidgetPro = apply_filters('rtmkit_widget_data_pro', $widgetsPro);
        $allWidgetForm = apply_filters('rtmkit_widget_data_form', $formWidgets);

        if ($type === 'free') {
            return $allWidgetFree;
        } elseif ($type === 'pro') {
            return $allWidgetPro;
        } elseif ($type === 'form') {
            return $allWidgetForm;
        }

        $all_widgets = array_merge($allWidgetFree, $allWidgetPro ?? [], $allWidgetForm ?? []);

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
     * @param string|array|null $companion Isi plugin_required widget, mis. 'rtm-elements'
     *                                     atau ['rtm-woo', 'woocommerce'] (semua wajib aktif).
     *                                     null = cek generik (salah satu companion aktif).
     */
    public function is_pro_with_companion_active($companion = null)
    {
        $isProActive = \RTMKit\Core\Plugin::instance()->pro_is_active();

        if (!$isProActive) {
            return false;
        }

        if ($companion !== null) {
            return $this->is_required_plugins_active($companion);
        }

        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return is_plugin_active('rtm-elements/rtm-elements.php')
            || is_plugin_active('rtm-blog/rtm-blog.php');
    }

    /**
     * Alasan widget terkunci di Widget Switcher, atau null kalau bisa dipakai.
     * Widget addon tetap didaftar walau addon-nya mati, supaya pengguna tahu
     * widget apa saja yang dimiliki RTMKit.
     *
     * @return string|null 'upgrade' (widget Pro tanpa lisensi), 'rtmform' (widget
     *                     form tanpa RTMForm) atau 'addon' (addon / plugin yang
     *                     dibutuhkan belum aktif).
     */
    public function get_widget_lock(array $widget)
    {
        $type = $widget['type'] ?? 'free';

        if ($type === 'pro' && !\RTMKit\Core\Plugin::instance()->pro_is_active()) {
            return 'upgrade';
        }

        if (($widget['category'] ?? '') === 'form' && !class_exists('RomethemeForm')) {
            return 'rtmform';
        }

        if ($type === 'pro' && !empty($widget['plugin_required']) && !$this->is_required_plugins_active($widget['plugin_required'])) {
            return 'addon';
        }

        return null;
    }

    /**
     * Link Demo widget di Widget Switcher: widget Woo dan Form ke halaman
     * pluginnya, sisanya ke halaman widget di rometheme.net/plugins/elements/.
     */
    public function get_demo_url(string $key, array $widget): string
    {
        $category = $widget['category'] ?? '';

        if ($category === 'woocommerce') {
            return self::DEMO_URL_WOO;
        }

        if ($category === 'form') {
            return self::DEMO_URL_FORM;
        }

        $slug = self::DEMO_SLUG_ALIASES[$key] ?? sanitize_title($widget['name'] ?? '');

        return in_array($slug, self::DEMO_SLUGS, true) ? self::DEMO_URL_ELEMENTS . $slug . '/' : self::DEMO_URL_ELEMENTS;
    }

    /**
     * Widget Single Post bawaan RTMKit tetap free walau rtm-blog (yang
     * menandainya Pro) aktif, jadi tidak diberi label Pro.
     */
    public function is_free_single_post(string $key): bool
    {
        return in_array($key, self::FREE_SINGLE_POST_KEYS, true) && $this->is_required_plugins_active('rtm-blog');
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
            'rtm-woo' => 'rtm-woo/rtm-woo.php',
            'rtm-form-pro' => 'rtm-form-pro/rtm-form-pro.php',
            'rtm-mega-menu' => 'rtm-mega-menu/rtm-mega-menu.php',
            'rtm-font-showcase-pro' => 'rtm-font-showcase-pro/rtm-font-showcase-pro.php',
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