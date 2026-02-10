<?php

namespace RTMKit\Modules\Widgets;

class WidgetModule
{
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
        add_action('elementor/elements/categories_registered', [$this, 'add_elementor_widget_categories']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_widget_style']);
        new \RTMKit\Modules\Helper\SavedTemplateEditor();
    }

    public function  add_elementor_widget_categories($elements_manager)
    {
        $elements_manager->add_category(
            'romethemekit_header_footer',
            [
                'title' => esc_html__('RTMKit Header & Footer', 'rometheme-for-elementor')
            ]
        );

        $elements_manager->add_category(
            'romethemekit_widgets',
            [
                'title' => esc_html__('RTMKit General', 'rometheme-for-elementor')
            ]
        );

        $elements_manager->add_category(
            'romethemekit_widgets_pro',
            [
                'title' => esc_html__('RTMKit Pro', 'rometheme-for-elementor')
            ]
        );
    }

    public function enqueue_widget_style()
    {
        $widgets_css = scandir(RTM_KIT_DIR . 'Inc/Widgets/assets/css');
        $element_css = scandir(RTM_KIT_DIR . 'Inc/Elements/assets/css');
        $widget_js = scandir(RTM_KIT_DIR . 'Inc/Widgets/assets/js');
        $element_js = scandir(RTM_KIT_DIR . 'Inc/Elements/assets/js');
        $lib_js = scandir(RTM_KIT_DIR . 'Inc/Elements/assets/js/lib');
        $lib_css = scandir(RTM_KIT_DIR . 'Inc/Elements/assets/css/lib');

        foreach ($widgets_css as $css_file) {
            if (pathinfo($css_file, PATHINFO_EXTENSION) === 'css') {
                wp_register_style(
                    'rtmkit-widget-' . pathinfo($css_file, PATHINFO_FILENAME),
                    RTM_KIT_URL . 'Inc/Widgets/assets/css/' . $css_file,
                    [],
                    RTM_KIT_VERSION
                );
            }
        }
        foreach ($element_css as $css_file) {
            if (pathinfo($css_file, PATHINFO_EXTENSION) === 'css') {
                wp_register_style(
                    'rtmkit-element-' . pathinfo($css_file, PATHINFO_FILENAME),
                    RTM_KIT_URL . 'Inc/Elements/assets/css/' . $css_file,
                    [],
                    RTM_KIT_VERSION
                );
            }
        }
        foreach ($widget_js as $js_file) {
            if (pathinfo($js_file, PATHINFO_EXTENSION) === 'js') {
                wp_register_script(
                    'rtmkit-widget-' . pathinfo($js_file, PATHINFO_FILENAME),
                    RTM_KIT_URL . 'Inc/Widgets/assets/js/' . $js_file,
                    ['jquery'],
                    RTM_KIT_VERSION,
                    true
                );
            }
        }
        foreach ($element_js as $js_file) {
            if (pathinfo($js_file, PATHINFO_EXTENSION) === 'js') {
                wp_register_script(
                    'rtmkit-element-' . pathinfo($js_file, PATHINFO_FILENAME),
                    RTM_KIT_URL . 'Inc/Elements/assets/js/' . $js_file,
                    ['jquery'],
                    RTM_KIT_VERSION,
                    true
                );
            }
        }

        foreach ($lib_js as $js_file) {
            if (pathinfo($js_file, PATHINFO_EXTENSION) === 'js') {
                wp_register_script(
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
            <a href="<?php echo admin_url("post.php?post={$template_id}&action=elementor&parent_id={$parent_id}&ref=rtmkit") ?>"
                class="rtmkit-edit-template-btn">
                Edit Saved Template <i class="eicon-edit" aria-hidden="true"></i>
            </a>
<?php
        }
    }
}
