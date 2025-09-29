<?php

namespace RomethemeKit;

class RkitWrapperLink
{

    public function __construct()
    {
        // Tambah opsi di widget dan container
        add_action('elementor/element/common/_section_style/after_section_end', [$this, 'add_widget_options'], 10); 
        add_action('elementor/element/container/section_layout/after_section_end', [$this, 'add_container_options'], 10, 2);

        // Filter render content untuk container & widget
        add_filter('elementor/frontend/container/render_content', [$this, 'wrap_container_content'], 10, 2);
        add_filter('elementor/widget/render_content', [$this, 'wrap_widget_content'], 10, 2);
    }

    /**
     * Tambah controls ke Container
     */
    public function add_container_options($container)
    {
        $container->start_controls_section('rtmkit_wrapper_link_container', [
            'label' => esc_html__('RTMkit Wrapper Link', 'rometheme-for-elementor'),
            'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED
        ]);

        $container->add_control(
            'rtm_wrapper_link_enabled',
            [
                'label'        => esc_html__('Enable Wrapper Link ?', 'rometheme-for-elementor'),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('yes', 'rometheme-for-elementor'),
                'label_off'    => esc_html__('no', 'rometheme-for-elementor'),
                'return_value' => 'enabled',
                'default'      => '',
            ]
        );

        $container->add_control(
            'rtm_wrapper_link',
            [
                'label'       => esc_html__('Wrapper Link', 'rometheme-for-elementor'),
                'type'        => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-link.com', 'rometheme-for-elementor'),
                'options'     => ['url', 'is_external', 'nofollow'],
                'default'     => [
                    'url'        => '',
                    'is_external'=> true,
                    'nofollow'   => true,
                ],
                'label_block' => true,
                'condition'   => ['rtm_wrapper_link_enabled' => 'enabled'],
            ]
        );

        $container->end_controls_section();
    }

    /**
     * Tambah controls ke Widget
     */
    public function add_widget_options($widgets) 
    {
        $widgets->start_controls_section('rtmkit_wrapper_link_container', [
            'label' => esc_html__('RTMkit Wrapper Link', 'rometheme-for-elementor'),
            'tab'   => \Elementor\Controls_Manager::TAB_ADVANCED  
        ]);

        $widgets->add_control(
            'rtm_wrapper_link_enabled',
            [
                'label'        => esc_html__('Enable Wrapper Link ?', 'rometheme-for-elementor'),
                'type'         => \Elementor\Controls_Manager::SWITCHER,
                'label_on'     => esc_html__('yes', 'rometheme-for-elementor'),
                'label_off'    => esc_html__('no', 'rometheme-for-elementor'),
                'return_value' => 'enabled',
                'default'      => '',
            ]
        );

        $widgets->add_control(
            'rtm_wrapper_link',
            [
                'label'       => esc_html__('Wrapper Link', 'rometheme-for-elementor'),
                'type'        => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-link.com', 'rometheme-for-elementor'),
                'options'     => ['url', 'is_external', 'nofollow'],
                'default'     => [
                    'url'        => '',
                    'is_external'=> true,
                    'nofollow'   => true,
                ],
                'label_block' => true,
                'condition'   => ['rtm_wrapper_link_enabled' => 'enabled'],
            ]
        );

        $widgets->end_controls_section();
    }

    /**
     * Filter render untuk Container
     */
    public function wrap_container_content($content, $container) 
    {
        $settings = $container->get_settings_for_display();

        if (!empty($settings['rtm_wrapper_link']['url']) && $settings['rtm_wrapper_link_enabled'] === 'enabled') {
            $url      = esc_url($settings['rtm_wrapper_link']['url']);
            $target   = !empty($settings['rtm_wrapper_link']['is_external']) ? ' target="_blank"' : '';
            $nofollow = !empty($settings['rtm_wrapper_link']['nofollow']) ? ' rel="nofollow"' : '';

            $content = '<a class="rtmkit-wrapper-link" href="' . $url . '"' . $target . $nofollow . '>' . $content . '</a>';
        }

        return $content;
    }

    /**
     * Filter render untuk Widget
     */
    public function wrap_widget_content($content, $widget) 
    {
        $settings = $widget->get_settings_for_display();

        if (!empty($settings['rtm_wrapper_link']['url']) && $settings['rtm_wrapper_link_enabled'] === 'enabled') {
            $url      = esc_url($settings['rtm_wrapper_link']['url']);
            $target   = !empty($settings['rtm_wrapper_link']['is_external']) ? ' target="_blank"' : '';
            $nofollow = !empty($settings['rtm_wrapper_link']['nofollow']) ? ' rel="nofollow"' : '';

            $content = '<a class="rtmkit-wrapper-link" href="' . $url . '"' . $target . $nofollow . '>' . $content . '</a>';
        }

        return $content;
    }
}
