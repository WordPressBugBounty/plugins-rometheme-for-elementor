<?php

class RkitBackToTop extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'rkit-back-to-top';
    }

    public function get_title()
    {
        return \RomethemeKit\RkitWidgets::listWidgets()['backtotop']['name'];
    }

    public function get_icon()
    {
        $icon = 'rkit-widget-icon ' . \RomethemeKit\RkitWidgets::listWidgets()['backtotop']['icon'];
        return $icon;
    }

    public function get_categories()
    {
        return ['romethemekit_widgets'];
    }

    public function get_keywords()
    {
        return ['back', 'top', 'rometheme'];
    }

    function get_custom_help_url()
    {
        return \RomethemeKit\RkitWidgets::listWidgets()['backtotop']['docsURL'];
    }

    public function get_style_depends()
    {
        return ['rkit-back-to-top-style'];
    }

    public function get_script_depends()
    {
        return ['rkit-back-to-top-script'];
    }

    protected function register_controls()
    {
        $this->start_controls_section('content_section', [
            'label' => esc_html('Content'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT
        ]);

        $this->add_control('select_style', [
            'label' => esc_html('Style'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'icon' => esc_html('Icon Only'),
                'text' => esc_html('Text Only'),
                'progress' => esc_html('Progress Indicator'),
            ],
            'default' => 'icon',
        ]);

        $this->add_control(
            'choose_style',
            [
                'label' => esc_html__('Display Type', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'text' => [
                        'title' => esc_html__('Text', 'rometheme-for-elementor'),
                        'icon' => 'eicon-t-letter-bold',
                    ],
                    'icon' => [
                        'title' => esc_html__('Icon', 'rometheme-for-elementor'),
                        'icon' => 'eicon-star',
                    ],
                ],
                'default' => 'icon',
                'toggle' => true,
                'condition' => [
                    'select_style' => 'progress'
                ]
            ]
        );

        $this->add_control(
            'backtop_text',
            [
                'label' => esc_html__('Button Text', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Top', 'rometheme-for-elementor'),
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'name' => 'select_style',
                            'operator' => '===',
                            'value' => 'text'
                        ],
                        [
                            'name' => 'choose_style',
                            'operator' => '===',
                            'value' => 'text'
                        ]
                    ]
                ]
            ]
        );

        $this->add_control(
            'backtop_icon',
            [
                'label' => esc_html__('Icon', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'rtmicon rtmicon-arrow-up',
                    'library' => 'rtmicons',
                ],
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'name' => 'select_style',
                            'operator' => '===',
                            'value' => 'icon'
                        ],
                        [
                            'name' => 'choose_style',
                            'operator' => '===',
                            'value' => 'icon'
                        ]
                    ]
                ]
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('setting_section', [
            'label' => esc_html('Settings'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT
        ]);

        $this->add_control(
            'hide_button_on_scroll',
            [
                'label' => esc_html__('Hide Button on Scroll', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'rometheme-for-elementor'),
                'label_off' => esc_html__('No', 'rometheme-for-elementor'),
                'return_value' => 'hide-button-on-scroll',
                'default' => '',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('button_style', [
            'label' => esc_html('Button Style'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_responsive_control(
			'button_align',
			[
				'label' => esc_html__( 'Alignment', 'rometheme-for-elementor' ),
				'type' => \Elementor\Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => esc_html__( 'Left', 'rometheme-for-elementor' ),
						'icon' => 'eicon-h-align-left',
					],
					'center' => [
						'title' => esc_html__( 'Center', 'rometheme-for-elementor' ),
						'icon' => 'eicon-h-align-center',
					],
					'right' => [
						'title' => esc_html__( 'Right', 'rometheme-for-elementor' ),
						'icon' => 'eicon-h-align-right',
					],
				],
				'default' => 'left',
				'toggle' => true,
				'selectors' => [
					'{{WRAPPER}} .rkit-back-to-top-container' => 'justify-content: {{VALUE}};',
				],
			]
		);

        $this->add_responsive_control(
            'button_width',
            [
                'label' => esc_html__('Button Width', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
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
                    '{{WRAPPER}} .rkit-back-to-top-button' => 'width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'select_style!' => 'progress'
                ]
            ]
        );

        $this->add_responsive_control(
            'button_height',
            [
                'label' => esc_html__('Button Height', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
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
                    '{{WRAPPER}} .rkit-back-to-top-button' => 'height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'select_style!' => 'progress'
                ]
            ]
        );

        $this->add_responsive_control(
            'button_size',
            [
                'label' => esc_html__('Button Size', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
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
                    '{{WRAPPER}} .rkit-back-to-top-button , {{WRAPPER}} .scroll-progress' => 'width: {{SIZE}}{{UNIT}}; height :{{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'select_style' => 'progress'
                ]
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => esc_html__('Icon Size', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
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
                    '{{WRAPPER}} .back-to-top-text i.rkit-back-to-top-icon' => 'font-size :{{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .back-to-top-text svg.rkit-back-to-top-icon' => 'width: {{SIZE}}{{UNIT}}; height :{{SIZE}}{{UNIT}};',
                ],
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'name' => 'select_style',
                            'operator' => '===',
                            'value' => 'icon'
                        ],
                        [
                            'name' => 'choose_style',
                            'operator' => '===',
                            'value' => 'icon'
                        ]
                    ]
                ]
            ]
        );

          $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'text_typography',
                'selector' => '{{WRAPPER}} .back-to-top-text',
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'name' => 'select_style',
                            'operator' => '===',
                            'value' => 'text'
                        ],
                        [
                            'name' => 'choose_style',
                            'operator' => '===',
                            'value' => 'text'
                        ]
                    ]
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'border_back_button',
                'selector' => '{{WRAPPER}} .rkit-back-to-top-button',
                'condition' => [
                    'select_style!' => 'progress'
                ]
            ]
        );

        $this->add_responsive_control(
            'border-radius',
            [
                'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-back-to-top-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'select_style!' => 'progress'
                ]
            ]
        );

        $this->add_control('foreground_color', [
            'label' => esc_html('Line Foreground Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'description' => esc_html('Global Color will not affect it.'),
            'condition' => [
                'select_style' => 'progress'
            ]
        ]);

        $this->add_control('background_color', [
            'label' => esc_html('Line Background Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'description' => esc_html('Global Color will not affect it.'),
            'condition' => [
                'select_style' => 'progress'
            ]
        ]);

        $this->start_controls_tabs('button_tabs');

        $this->start_controls_tab('button_tab_normal', ['label' => esc_html('Normal')]);

        $this->add_control('text_color_normal', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .back-to-top-text' => 'color:{{VALUE}}',
                '{{WRAPPER}} .back-to-top-text i.rkit-back-to-top-icon' => 'color:{{VALUE}}',
                '{{WRAPPER}} .back-to-top-text svg.rkit-back-to-top-icon' => 'fill:{{VALUE}}',
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'button_background_color_normal',
                'types' => ['classic', 'gradient'],
                'exclude' => ['image'],
                'selector' => '{{WRAPPER}} .rkit-back-to-top-button',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'button_box_shadow_normal',
                'selector' => '{{WRAPPER}} .rkit-back-to-top-button',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('button_tab_hover', ['label' => esc_html('Hover')]);

        $this->add_control('text_color_hover', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-back-to-top-button:hover .back-to-top-text' => 'color:{{VALUE}}',
                '{{WRAPPER}} .rkit-back-to-top-button:hover .back-to-top-text i.rkit-back-to-top-icon' => 'color:{{VALUE}}',
                '{{WRAPPER}} .rkit-back-to-top-button:hover .back-to-top-text svg.rkit-back-to-top-icon' => 'fill:{{VALUE}}',
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'button_background_color_hover',
                'types' => ['classic', 'gradient'],
                'exclude' => ['image'],
                'selector' => '{{WRAPPER}} .rkit-back-to-top-button:hover',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'button_box_shadow_hover',
                'selector' => '{{WRAPPER}} .rkit-back-to-top-button:hover',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $dataSettings = [
            'style' => $settings['select_style'],
            'foreground' => ($settings['foreground_color']) ? $settings['foreground_color'] : '#00cea6',
            'background' => ($settings['background_color']) ? $settings['background_color'] : '#ddd',
            'show_on_scroll' => true
        ];

?>
        <div class="rkit-back-to-top-container <?php echo esc_attr($settings['select_style']) ?>" data-settings="<?php echo esc_attr(wp_json_encode($dataSettings)) ?>">
            <div class="rkit-back-to-top-button <?php echo esc_attr($settings['hide_button_on_scroll']) ?>">
                <?php if ($settings['select_style'] == 'progress') : ?>
                    <canvas class="scroll-progress" width="210" height="210"></canvas>
                <?php endif; ?>
                <span class="back-to-top-text">
                    <?php
                    if ($settings['select_style'] == 'text') {
                        echo esc_html($settings['backtop_text']);
                    } else {
                        if ($settings['choose_style'] == 'text') {
                            echo esc_html($settings['backtop_text']);
                        } else {
                            \Elementor\Icons_Manager::render_icon($settings['backtop_icon'], ['aria-hidden' => 'true', 'class' => 'rkit-back-to-top-icon']);
                        }
                    }
                    ?>
                </span>
            </div>
        </div>
<?php
    }
}
