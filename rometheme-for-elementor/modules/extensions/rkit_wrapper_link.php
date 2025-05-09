<?php

namespace RomethemeKit;

class RkitWrapperLink
{

	public function __construct()
	{
		add_action('elementor/element/common/_section_style/after_section_end', array($this, 'add_widget_options'), 10); 
		add_action('elementor/element/container/section_layout/after_section_end', array($this, 'add_container_options'), 10, 2);
	
    // Untuk Container (section atau flexbox)
    add_action('elementor/frontend/container/before_render', function($element) {
        $settings = $element->get_settings_for_display();

        if (!empty($settings['rtm_wrapper_link']['url'])) {
            $url = esc_url($settings['rtm_wrapper_link']['url']);
            $target = $settings['rtm_wrapper_link']['is_external'] ? ' target="_blank"' : '';
            $nofollow = $settings['rtm_wrapper_link']['nofollow'] ? ' rel="nofollow"' : '';
            echo '<a class="rtmkit-wrapper-link" href="' . $url . '"' . $target . $nofollow . '>';
        }
    });

    add_action('elementor/frontend/container/after_render', function($element) {
        $settings = $element->get_settings_for_display();

        if (!empty($settings['rtm_wrapper_link']['url'])) {
            echo '</a>';
        }
    });


    // Untuk Widget (jika kamu ingin wrap widget juga)
    add_action('elementor/frontend/widget/before_render', function($element) {
        $settings = $element->get_settings_for_display();

        if (!empty($settings['rtm_wrapper_link']['url'])) {
            $url = esc_url($settings['rtm_wrapper_link']['url']);
            $target = $settings['rtm_wrapper_link']['is_external'] ? ' target="_blank"' : '';
            $nofollow = $settings['rtm_wrapper_link']['nofollow'] ? ' rel="nofollow"' : '';
            echo '<a class="rtmkit-wrapper-link" href="' . $url . '"' . $target . $nofollow . '>';
        }
    });

    add_action('elementor/frontend/widget/after_render', function($element) {
        $settings = $element->get_settings_for_display();

        if (!empty($settings['rtm_wrapper_link']['url'])) {
            echo '</a>';
        }
    });

    
    }

	public function add_container_options($container)
	{
		$container->start_controls_section('rtmkit_wrapper_link_container', [
			'label' => esc_html('RTMkit Wrapper Link'),
			'tab' => \Elementor\Controls_Manager::TAB_ADVANCED
		]);


        $container->add_control(
			'rtm_wrapper_link_enabled',
			[
				'label' => esc_html__('Enable Wrapper Link ?', 'textdomain'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('yes', 'textdomain'),
				'label_off' => esc_html__('no', 'textdomain'),
				'return_value' => 'enabled',
				'default' => '', 
			]
		);


        $container->add_control(
            'rtm_wrapper_link',
            [
                'label' => esc_html__('Wrapper Link', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-link.com', 'rometheme-for-elementor'),
                'options' => ['url', 'is_external', 'nofollow'],
                'default' => [
                    'url' => '',
                    'is_external' => true,
                    'nofollow' => true,
                ],
                'label_block' => true,
                'condition' => ['rtm_wrapper_link_enabled' => 'enabled'],
            ]
        );
	

		$container->end_controls_section();

        

    }

	public function add_widget_options($widgets) {
        $widgets->start_controls_section('rtmkit_wrapper_link_container', [
			'label' => esc_html('RTMkit Wrapper Link'),
			'tab' => \Elementor\Controls_Manager::TAB_ADVANCED  
		]);

        $widgets->add_control(
			'rtm_wrapper_link_enabled',
			[
				'label' => esc_html__('Enable Wrapper Link ?', 'textdomain'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('yes', 'textdomain'),
				'label_off' => esc_html__('no', 'textdomain'),
				'return_value' => 'enabled',
				'default' => '', 
			]
		);

        $widgets->add_control(
            'rtm_wrapper_link',
            [
                'label' => esc_html__('Wrapper Link', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-link.com', 'rometheme-for-elementor'),
                'options' => ['url', 'is_external', 'nofollow'],
                'default' => [
                    'url' => '',
                    'is_external' => true,
                    'nofollow' => true,
                ],
                'label_block' => true,
                'condition' => ['rtm_wrapper_link_enabled' => 'enabled'],
            ]
        );

		$widgets->end_controls_section();
	}

 
    


}
