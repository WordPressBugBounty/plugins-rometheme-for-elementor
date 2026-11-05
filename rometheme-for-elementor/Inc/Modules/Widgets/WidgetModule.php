<?php

namespace RTMKit\Modules\Widgets;

if ( ! defined( 'ABSPATH' ) ) exit;

class WidgetModule
{
    /**
     * Kategori Elementor milik addon, untuk widget terkunci (elementor_category di
     * rtmwp.json) selama addon-nya mati. Semua kategori selalu ada supaya semua
     * widget RTM Woo / RTM Blog terlihat; kategori dengan tipe template dinaikkan
     * ke atas saat template RTMKit tipe itu yang sedang diedit.
     */
    const ADDON_CATEGORIES = [
        'rtm-blog' => ['RTM Blog Single', ['single_post']],
        'romethemepro_archive' => ['RTM Blog Archive', ['archive', 'archive_post', 'search']],
        'romethemepro_post_product' => ['RTM Woo', ['single_product', 'product_loop_item']],
        'romethemepro_archive_product' => ['RTM Woo Archive', ['archive_product']],
        'romethemepro_cart_page' => ['RTM Cart', ['cart_page']],
        'romethemepro_checkout_page' => ['RTM Checkout', ['checkout_page']],
        'romethemepro_my_account' => ['RTM My Account', ['my_account_page']],
        'romethemepro_my_account_login' => ['RTM My Account Login', ['my_account_login']],
        'romethemepro_thankyou' => ['RTM Order', ['thankyou_page']],
        'romethemepro_product_loop' => ['RTM Woo Loop', ['product_loop_item']],
        'rtmwoo_shop' => ['RTM Woo', null],
        'rtmwoo_header' => ['RTM Woo Header', null],
        // Sama dengan kategori milik rtm-form-pro: terpisah dari field RTMForm free.
        'rtm-form-pro' => ['RTM Form Pro', null],
        // Kategori RTMForm free, untuk widget RTMForm terkunci selama plugin-nya mati.
        'romethemeform_form_fields' => ['RTMForm', null],
        'rtm-font' => ['RTM Font', null],
    ];

    private static $instance;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        \RTMKit\Modules\Widgets\WidgetStorage::instance()->init();
        (new \RTMKit\Extensions\LockedExtensions())->init();
        add_action('elementor/elements/categories_registered', [$this, 'add_elementor_widget_categories']);
        add_action('elementor/elements/categories_registered', [$this, 'add_addon_categories']);
        add_action('elementor/elements/categories_registered', [$this, 'promote_header_footer_category'], PHP_INT_MAX - 1);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_widget_style'], 1);
        add_action('elementor/editor/before_enqueue_scripts', [$this, 'pro_js']);
        new \RTMKit\Modules\Helper\SavedTemplateEditor();
    }

    public function  add_elementor_widget_categories($elements_manager)
    {
        $elements_manager->add_category(
            'romethemekit_header_footer',
            [
                'title' => esc_html__('RTM Header & Footer', 'rometheme-for-elementor')
            ]
        );

        $elements_manager->add_category(
            'romethemekit_widgets',
            [
                'title' => esc_html__('RTM General', 'rometheme-for-elementor')
            ]
        );

        // Judulnya sama dengan yang dipakai rtm-elements saat aktif.
        $elements_manager->add_category(
            'rtm-elements',
            [
                'title' => esc_html__('RTM Elements', 'rometheme-for-elementor')
            ]
        );

        // Cadangan untuk widget Pro terkunci (DynamicWidgetPro) yang tidak punya
        // elementor_category. Tanpa kategori ini Elementor tidak menampilkannya.
        $elements_manager->add_category(
            'romethemekit_widgets_pro',
            [
                'title' => esc_html__('RTM Pro Widgets', 'rometheme-for-elementor')
            ]
        );
    }

    public function add_addon_categories($elements_manager)
    {
        if (!is_object($elements_manager)) {
            return;
        }

        $template_type = $this->get_editing_template_type();
        $promoted = [];

        foreach (self::ADDON_CATEGORIES as $category => [$title, $template_types]) {
            if ('romethemepro_archive' === $category && 'search' === $template_type) {
                $title = 'RTM Search Results';
            }

            // add_category() tidak menimpa kategori yang sudah didaftarkan addon aktif.
            $elements_manager->add_category($category, ['title' => $title]);

            if ($template_types !== null && in_array($template_type, $template_types, true)) {
                $promoted[] = $category;
            }
        }

        if ($promoted) {
            $this->promote_categories($elements_manager, $promoted);
        }
    }

    public function promote_header_footer_category($elements_manager)
    {
        if (!is_object($elements_manager) || !in_array($this->get_editing_template_type(), ['header', 'footer'], true)) {
            return;
        }

        $this->promote_categories($elements_manager, ['romethemekit_header_footer']);
    }

    /**
     * Naikkan kategori ke urutan teratas panel, dengan urutan $keys.
     */
    private function promote_categories($elements_manager, array $keys)
    {

        try {
            $property = new \ReflectionProperty($elements_manager, 'categories');
        } catch (\ReflectionException $e) {
            return;
        }

        $property->setAccessible(true);
        $categories = $property->getValue($elements_manager);

        if (!is_array($categories)) {
            return;
        }

        $promoted = array_intersect_key(array_flip($keys), $categories);
        if (!$promoted) {
            return;
        }

        $property->setValue($elements_manager, array_replace($promoted, $categories));
    }

    /**
     * Tipe template RTMKit (rometheme_template_type) yang sedang diedit, atau ''.
     */
    private function get_editing_template_type()
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        foreach (['post', 'editor_post_id', 'initial_document_id', 'post_id'] as $key) {
            if (!empty($_REQUEST[$key])) {
                $post_id = absint($_REQUEST[$key]);
                break;
            }
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        return !empty($post_id) ? (string) get_post_meta($post_id, 'rometheme_template_type', true) : '';
    }

    public function register_widget_scripts()
    {
        // OPTIMIZATION: Use cached file listings instead of scandir()
        $cache_manager = \RTMKit\Modules\Helper\CacheManager::instance();
        
        $widgets_css = $cache_manager->get_asset_manifest_cached(RTM_KIT_DIR . 'Inc/Widgets/assets/css');
        $widget_js = $cache_manager->get_asset_manifest_cached(RTM_KIT_DIR . 'Inc/Widgets/assets/js');
        
        foreach ($widgets_css as $css_file) {
            if (pathinfo($css_file, PATHINFO_EXTENSION) === 'css') {
                wp_enqueue_style(
                    'rtmkit-widget-' . pathinfo($css_file, PATHINFO_FILENAME),
                    RTM_KIT_URL . 'Inc/Widgets/assets/css/' . $css_file,
                    [],
                    RTM_KIT_VERSION
                );
            }
        }

        foreach ($widget_js as $js_file) {
            if (pathinfo($js_file, PATHINFO_EXTENSION) === 'js') {
                wp_enqueue_script(
                    'rtmkit-widget-' . pathinfo($js_file, PATHINFO_FILENAME),
                    RTM_KIT_URL . 'Inc/Widgets/assets/js/' . $js_file,
                    ['jquery'],
                    RTM_KIT_VERSION,
                    true
                );
            }
        }
    }

    /**
     * Versi aset widget: versi plugin + waktu ubah file, supaya browser langsung
     * memuat CSS/JS yang baru diubah tanpa menunggu versi plugin naik.
     */
    private function asset_version($path)
    {
        $mtime = @filemtime(RTM_KIT_DIR . 'Inc/Elements/assets/' . $path);

        return $mtime ? RTM_KIT_VERSION . '.' . $mtime : RTM_KIT_VERSION;
    }

    public function enqueue_widget_style()
    {
        // OPTIMIZATION: Use cached file listings instead of multiple scandir() calls
        $cache_manager = \RTMKit\Modules\Helper\CacheManager::instance();
        
        $element_css = $cache_manager->get_asset_manifest_cached(RTM_KIT_DIR . 'Inc/Elements/assets/css');
        $element_js = $cache_manager->get_asset_manifest_cached(RTM_KIT_DIR . 'Inc/Elements/assets/js');
        $lib_js = $cache_manager->get_asset_manifest_cached(RTM_KIT_DIR . 'Inc/Elements/assets/js/lib');
        $lib_css = $cache_manager->get_asset_manifest_cached(RTM_KIT_DIR . 'Inc/Elements/assets/css/lib');

        $this->register_widget_scripts();

        foreach ($element_css as $css_file) {
            if (pathinfo($css_file, PATHINFO_EXTENSION) === 'css') {
                wp_register_style(
                    'rtmkit-element-' . pathinfo($css_file, PATHINFO_FILENAME),
                    RTM_KIT_URL . 'Inc/Elements/assets/css/' . $css_file,
                    [],
                    $this->asset_version('css/' . $css_file)
                );
            }
        }

        foreach ($element_js as $js_file) {
            if (pathinfo($js_file, PATHINFO_EXTENSION) === 'js') {
                wp_enqueue_script(
                    'rtmkit-element-' . pathinfo($js_file, PATHINFO_FILENAME),
                    RTM_KIT_URL . 'Inc/Elements/assets/js/' . $js_file,
                    ['jquery'],
                    $this->asset_version('js/' . $js_file),
                    true
                );
            }
        }

        foreach ($lib_js as $js_file) {
            if (pathinfo($js_file, PATHINFO_EXTENSION) === 'js') {
                wp_enqueue_script(
                    'rtmkit-lib-' . pathinfo($js_file, PATHINFO_FILENAME),
                    RTM_KIT_URL . 'Inc/Elements/assets/js/lib/' . $js_file,
                    ['jquery'],
                    RTM_KIT_VERSION,
                    true
                );
            }
        }

        foreach ($lib_css as $css_file) {
            if (pathinfo($css_file, PATHINFO_EXTENSION) === 'css') {
                wp_register_style(
                    'rtmkit-lib-' . pathinfo($css_file, PATHINFO_FILENAME),
                    RTM_KIT_URL . 'Inc/Elements/assets/css/lib/' . $css_file,
                    [],
                    RTM_KIT_VERSION
                );
            }
        }
    }

    public function render_edit_template_button($template_id, $parent_id)
    {
        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
?>
            <a href="<?php echo esc_url(admin_url("post.php?post={$template_id}&action=elementor&parent_id={$parent_id}&ref=rtmkit")) ?>"
                class="rtmkit-edit-template-btn">
                Edit Saved Template <i class="eicon-edit" aria-hidden="true"></i>
            </a>
<?php
        }
    }

    function pro_js()
    {
        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            wp_enqueue_script('rtmprojs', RTM_KIT_URL . 'assets/js/rtmwp.js', ['jquery' , 'wp-i18n'], RTM_KIT_VERSION, true);
            wp_localize_script('rtmprojs', 'rtmpro', [
                'is_pro' =>  \RTMKit\Core\Plugin::instance()->pro_is_active() ? 'true' : 'false',
                'pricing_url' => \RTMKit\Modules\Telemetry\UpgradeLink::PRICING_URL,
                'rtmform_url' => \RTMKit\Elements\DynamicWidgetPro::rtmform_url(),
                'rtmform_installed' => file_exists(WP_PLUGIN_DIR . '/romethemeform/rometheme-form.php') ? 'true' : 'false',
            ]);
        }
    }
}
