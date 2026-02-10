<?php

namespace RTMKit\Modules\RTMIcons;

class RTMIconsModule
{
    public function init()
    {
        $rtmicon = \RTMKit\Modules\Storage::instance()->get_active_modules(['key' => 'rtmicon']);
        add_action('elementor/editor/before_enqueue_styles', function () {
            wp_enqueue_style('rtmicons', RTM_KIT_URL . '/assets/css/rtmicons.css', '', RTM_KIT_VERSION);
        });
        if ($rtmicon) {
            add_action('elementor/icons_manager/additional_tabs', [$this, 'register_rtm_icon_tabs']);
        }
    }
    public function register_rtm_icon_tabs($font)
    {
        unset($font['rtmicons']);
        unset($font['rtmicons-thin']);
        $font_new['rtmicons'] = array(
            'name'          => 'rtmicons',
            'label'         => esc_html__('RTM Icon - Regular', 'rometheme-for-elementor'),
            'url'           => RTM_KIT_URL . 'assets/css/rtmicon-regular.css',
            'prefix'        => 'rtmicon-',
            'displayPrefix' => 'rtmicon',
            'labelIcon'     => 'rtmicon rtmicon-romethemekit',
            'ver'           => RTM_KIT_VERSION,
            'fetchJson'     => RTM_KIT_URL . 'assets/js/rtmicon.json',
            'native'        => true,
        );
        $font_new['rtmicons-thin'] = array(
            'name'          => 'rtmicons-thin',
            'label'         => esc_html__('RTM Icon - Thin', 'rometheme-for-elementor'),
            'url'           => RTM_KIT_URL . 'assets/css/rtmicon-thin.css',
            'prefix'        => 'rtmicon-',
            'displayPrefix' => 'rtmicon-thin',
            'labelIcon'     => 'rtmicon-thin rtmicon-romethemekit',
            'ver'           => RTM_KIT_VERSION,
            'fetchJson'     => RTM_KIT_URL . 'assets/js/rtmicon-thin.json',
            'native'        => true,
        );
        return array_merge($font, $font_new);
    }
}
