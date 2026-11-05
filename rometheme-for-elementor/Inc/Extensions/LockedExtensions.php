<?php

namespace RTMKit\Extensions;

if (! defined('ABSPATH')) exit;

/**
 * Section tiruan (terkunci) untuk ekstensi addon yang belum aktif, supaya
 * pengguna tahu ekstensi apa saja yang dimiliki RTMKit: Sticky Content dan
 * Custom Cursor (rtm-elements) di tab Advanced, serta Text / Element / Layout
 * Motion (rtm-motion). Isinya hanya pesan terkunci, bukan kontrol aslinya.
 */
class LockedExtensions
{
    const ELEMENTS = 'rtm-elements';
    const MOTION = 'rtm-motion';

    public function init()
    {
        $elements_locked = !\RTMKit\Modules\Widgets\WidgetStorage::instance()->is_pro_with_companion_active(self::ELEMENTS);
        $motion_locked = !$this->is_plugin_active(self::MOTION);

        if (!$elements_locked && !$motion_locked) {
            return;
        }

        add_action('elementor/element/container/section_layout/after_section_end', function ($container) use ($elements_locked, $motion_locked) {
            if ($motion_locked) {
                $this->add_locked_section($container, 'layout_motion', __('Layout Motion', 'rometheme-for-elementor'), self::MOTION, \Elementor\Controls_Manager::TAB_LAYOUT);
            }

            if ($elements_locked) {
                $this->add_elements_sections($container);
            }
        });

        add_action('elementor/element/common/_section_style/after_section_end', function ($widget) use ($elements_locked, $motion_locked) {
            if ($motion_locked) {
                $this->add_locked_section($widget, 'element_motion', __('Element Motion', 'rometheme-for-elementor'), self::MOTION);
            }

            if ($elements_locked) {
                $this->add_elements_sections($widget);
            }
        });

        if ($motion_locked) {
            // Text Motion hanya untuk widget teks, di tab Content seperti di rtm-motion.
            foreach (['heading' => 'section_title', 'text-editor' => 'section_editor'] as $name => $section) {
                add_action("elementor/element/{$name}/{$section}/after_section_end", function ($element) {
                    $this->add_locked_section($element, 'text_motion', __('Text Motion', 'rometheme-for-elementor'), self::MOTION, \Elementor\Controls_Manager::TAB_CONTENT);
                });
            }
        }
    }

    private function add_elements_sections($element)
    {
        $this->add_locked_section($element, 'sticky_content', __('Sticky Content', 'rometheme-for-elementor'), self::ELEMENTS);
        $this->add_locked_section($element, 'custom_cursor', __('Custom Cursor', 'rometheme-for-elementor'), self::ELEMENTS);
    }

    /**
     * Penjelasan singkat tiap ekstensi, supaya pengguna tahu apa yang didapat
     * setelah addon-nya diaktifkan.
     */
    private function get_description($id)
    {
        $descriptions = [
            'text_motion' => __('Animate headings and text word by word or letter by letter with GSAP.', 'rometheme-for-elementor'),
            'element_motion' => __('Add scroll, hover and click animations to any widget with GSAP.', 'rometheme-for-elementor'),
            'layout_motion' => __('Animate containers and their children as the page scrolls.', 'rometheme-for-elementor'),
            'sticky_content' => __('Keep any element pinned on screen while the visitor scrolls.', 'rometheme-for-elementor'),
            'custom_cursor' => __('Replace the mouse cursor with a custom one when hovering this element.', 'rometheme-for-elementor'),
        ];

        return $descriptions[$id] ?? '';
    }

    private function add_locked_section($element, $id, $label, $addon, $tab = \Elementor\Controls_Manager::TAB_ADVANCED)
    {
        $element->start_controls_section('rtmkit_locked_' . $id, [
            'label' => \RTMKit\Modules\Extensions\ExtensionStorage::get_logo() . esc_html($label) . ' <i class="eicon-lock" aria-hidden="true"></i>',
            'tab' => $tab,
        ]);

        $element->add_control('rtmkit_locked_' . $id . '_notice', [
            'type' => \Elementor\Controls_Manager::RAW_HTML,
            'raw' => $this->get_notice($id, $label, $addon),
        ]);

        $element->end_controls_section();
    }

    private function get_notice($id, $label, $addon)
    {
        $addon_name = \RTMKit\Modules\Addons\AddonsModule::get_addon_name($addon);
        $is_pro = \RTMKit\Core\Plugin::instance()->pro_is_active();

        if ($is_pro) {
            $steps = [
                /* translators: %s: addon name. */
                sprintf(__('Open RTMKit → Addons, then install & activate %s.', 'rometheme-for-elementor'), '<strong>' . esc_html($addon_name) . '</strong>'),
                __('Reload the editor to start using it.', 'rometheme-for-elementor'),
            ];
            $url = admin_url('admin.php?page=rtmkit&path=addons');
            $cta = __('Open Addons', 'rometheme-for-elementor');
            $icon = 'eicon-plug';
        } else {
            $steps = [
                __('Upgrade to RTMKit Pro.', 'rometheme-for-elementor'),
                /* translators: %s: addon name. */
                sprintf(__('Open RTMKit → Addons, then install & activate %s.', 'rometheme-for-elementor'), '<strong>' . esc_html($addon_name) . '</strong>'),
            ];
            $url = \RTMKit\Modules\Telemetry\UpgradeLink::url('editor.locked_extension.' . $addon);
            $cta = __('Upgrade Now', 'rometheme-for-elementor');
            $icon = 'eicon-upgrade-crown';
        }

        $description = $this->get_description($id);

        return sprintf(
            '<div class="rkit-locked-extension">'
                . '<div class="rkit-locked-extension__badge"><i class="eicon-lock" aria-hidden="true"></i> %1$s</div>'
                . '%2$s'
                . '<div class="rkit-locked-extension__requires">%3$s</div>'
                . '<ol class="rkit-locked-extension__steps"><li>%4$s</li></ol>'
                . '<a class="elementor-button go-pro" href="%5$s" target="_blank" rel="noopener"><i class="%6$s" aria-hidden="true"></i> %7$s</a>'
            . '</div>',
            /* translators: %s: addon name. */
            esc_html(sprintf(__('Requires %s', 'rometheme-for-elementor'), $addon_name)),
            $description ? '<p class="rkit-locked-extension__desc">' . esc_html($description) . '</p>' : '',
            /* translators: 1: extension name, 2: addon name. */
            sprintf(esc_html__('%1$s is part of the %2$s addon. To unlock it:', 'rometheme-for-elementor'), '<strong>' . esc_html($label) . '</strong>', '<strong>' . esc_html($addon_name) . '</strong>'),
            implode('</li><li>', $steps),
            esc_url($url),
            esc_attr($icon),
            esc_html($cta)
        );
    }

    private function is_plugin_active($slug)
    {
        if (!function_exists('is_plugin_active')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return is_plugin_active($slug . '/' . $slug . '.php');
    }
}
