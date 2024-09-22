<?php

class Rkit_RunningText extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'rkit-running_text';
    }
    public function get_title()
    {
        return 'Text Marquee';
    }

    public function get_icon()
    {
        $icon = 'rkit-widget-icon '. \RomethemeKit\RkitWidgets::listWidgets()['runningtext']['icon'];
        return $icon;
    }

    public function get_keywords()
    {
        return ['rometheme', 'text', 'running', 'text running', 'running text'];
    }

    public function get_categories()
    {
        return ['romethemekit_widgets'];
    }
    
    function get_custom_help_url()
    {
        return 'https://rometheme.net/docs/how-to-use-customize-text-marquee/';
    }

    public function get_style_depends()
    {
        return ['rkit-running_text-style'];
    }

    public function get_script_depends()
    {
        return ['running-text-script'];
    }
    protected function register_controls()
    {
        $this->start_controls_section('content_section', ['label' => esc_html('Content'), 'tab' => \Elementor\Controls_Manager::TAB_CONTENT]);

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'text',
            [
                'label' => esc_html__('Text', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::WYSIWYG,
                'default' => esc_html__('Rometheme Text Running', 'rometheme-for-elementor'),
                'placeholder' => esc_html__('Type your Text here', 'rometheme-for-elementor'),
            ]
        );

        $repeater->add_control(
            'item_icon',
            [
                'label' => esc_html__('Icon', 'textdomain'),
                'type' => \Elementor\Controls_Manager::ICONS,
            ]
        );

        $repeater->add_control(
            'item_color',
            [
                'label' => esc_html('Text Color'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} {{CURRENT_ITEM}} ' => 'color:{{VALUE}}'
                ]
            ]
        );

        $this->add_control('item_text', [
            'label' => esc_html('Item'),
            'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $repeater->get_controls(),
            'default' => [
                [
                    'text' => esc_html('Example Text 1')
                ],
                [
                    'text' => esc_html('Example Text 2')
                ],
            ]
        ]);

        $this->add_control(
            'speed_control',
            [
                'label' => esc_html__('Speed
                ', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'default' => [
                    'size' => 10
                ]
            ]
        );

        $this->add_control(
            'direction',
            [
                'label' => esc_html__('Direction', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'rometheme-for-elementor'),
                        'icon' => 'eicon-arrow-left',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'rometheme-for-elementor'),
                        'icon' => 'eicon-arrow-right',
                    ],
                ],
                'default' => 'left',
                'toggle' => true,
            ]
        );


        $this->end_controls_section();

        $this->start_controls_section('container_style', ['label' => esc_html('Container'), 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'background_container',
                'types' => ['classic', 'gradient', 'video'],
                'selector' => '{{WRAPPER}} .running-text-container',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow_container',
                'selector' => '{{WRAPPER}} .running-text-container',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'border_container',
                'selector' => '{{WRAPPER}} .running-text-container',
            ]
        );

        $this->add_control(
            'padding_container',
            [
                'label' => esc_html__('Padding', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .running-text-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'border_radius_container',
            [
                'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .running-text-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->end_controls_section();

        $this->start_controls_section('text_style', ['label' => esc_html('Text'), 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'text_typography',
                'selector' => '{{WRAPPER}} .rkit-running-text',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'text_shadow',
                'selector' => '{{WRAPPER}} .rkit-running-text',
            ]
        );

        $this->add_control(
            'running_text_color',
            [
                'label' => esc_html__('Text Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-running-text' => 'color: {{VALUE}}',
                ],
            ]
        );

        $this->add_responsive_control(
			'text_margin',
			[
				'label' => esc_html__( 'Text Margin', 'textdomain' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .rkit-running-text__text' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

        $this->add_responsive_control(
			'icon_margin',
			[
				'label' => esc_html__( 'Icon Margin', 'textdomain' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem' ],
				'selectors' => [
					'{{WRAPPER}} .rkit-running-text__icon' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

        $this->add_responsive_control(
			'icon_size',
			[
				'label' => esc_html__( 'Icon Size', 'textdomain' ),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 5,
					],
					'%' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .rkit-running-text__icon' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);


        $this->end_controls_section();
    }
    protected function render()
    {
        $settings = $this->get_settings_for_display();

?>
        <div class="running-text-container">
            <marquee direction="<?php echo esc_attr($settings['direction']) ?>" scrollamount="<?php echo esc_attr($settings['speed_control']['size']) ?>" >
                <div class="rkit-running-text">
                    <?php foreach ($settings['item_text']  as $text) :
                        echo '<div class="rkit-running-text-item elementor-repeater-item-' . esc_attr($text['_id']) . '">';
                        \Elementor\Icons_Manager::render_icon($text['item_icon'], ['aria-hidden' => 'true' , 'class' => 'rkit-running-text__icon']);
                        echo '<div class="rkit-running-text__text">'.wp_kses_post($text['text']) . '</div>';
                        echo '</div>';
                    endforeach;
                    ?>
                </div>
            </marquee>
        </div>
<?php
    }
}
