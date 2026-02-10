<?php

namespace RTMKit\Elements;

class Counter extends \Elementor\Widget_Base
{
    private function get_widget_data()
    {
        return \RTMkit\Modules\Widgets\WidgetStorage::instance()->get_widget_data_by_key('counter');
    }

    public function get_name()
    {
        return 'rkit-counter';
    }
    public function get_title()
    {
        return $this->get_widget_data()['name'];
    }

    public function get_keywords()
    {
        return ['rtm', 'counter'];
    }
    public function get_icon()
    {
        $icon = 'rkit-widget-icon ' . $this->get_widget_data()['icon'];
        return $icon;
    }

    function get_custom_help_url()
    {
        return 'https://support.rometheme.net/docs/romethemekit/widgets/how-to-use-ezd_ampersand-customize-counter-widget/';
    }
    public function get_categories()
    {
        return ['romethemekit_widgets'];
    }

    public function get_style_depends()
    {
        return ['rtmkit-element-counter' , 'rtmkit-lib-odometer.min'];
    }

    public function get_script_depends()
    {
        return ['rtmkit-element-counter' , 'rtmkit-lib-odometer.min'];
    }

    protected function register_controls()
    {
        $this->start_controls_section('content_section', [
            'label' => esc_html('Counter'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT
        ]);

        $this->add_control('title_text', [
            'type' => \Elementor\Controls_Manager::TEXT,
            'label' => esc_html('Title'),
            'placeholder' => esc_html('Input Your Counter Title Here'),
            'default' => esc_html('Trusted Client')
        ]);

        $this->add_control('title_tag', [
            'type' => \Elementor\Controls_Manager::SELECT,
            'label' => esc_html('Title Tag'),
            'options' => [
                'h1' => esc_html('H1'),
                'h2' => esc_html('H2'),
                'h3' => esc_html('H3'),
                'h4' => esc_html('H4'),
                'h5' => esc_html('H5'),
                'h6' => esc_html('H6'),
                'span' => esc_html('SPAN'),
                'div' => esc_html('DIV'),
            ],
            'default' => 'span',
            'condition' => [
                'title_text!' => ''
            ]
        ]);

        $this->add_control(
            'hr',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ]
        );

        $this->add_control('starting_number', [
            'type' => \Elementor\Controls_Manager::NUMBER,
            'label' => esc_html('Start Number'),
            'default' => 100,
        ]);

        $this->add_control('end_number', [
            'type' => \Elementor\Controls_Manager::NUMBER,
            'label' => esc_html('End Number'),
            'default' => 9999,
        ]);

        $this->add_control('format_number', [
            'type' => \Elementor\Controls_Manager::SELECT,
            'label' => esc_html('Format Number'),
            'options' => [
                'd' => esc_html('(12345678)'),
                '(,ddd)' => esc_html('(12,345,678)'),
                '(,ddd).dd' => esc_html('(12,345,678.09)'),
                '(.ddd),dd' => esc_html('(12.345.678,09)'),
                '( ddd),dd' => esc_html('(12 345 678,09)'),
                '(_ddd),dd' => esc_html('(12_345_678,09)'),
                "('ddd),dd" => esc_html("(12'345'678,09)"),
                '("ddd),dd' => esc_html('(12"345"678,09)'),
            ],
            'default' => '(,ddd)'
        ]);

        $this->add_control('prefix_number', [
            'type' => \Elementor\Controls_Manager::TEXT,
            'label' => esc_html('Prefix Number'),
            'placeholder' => esc_html('Input Your Prefix Number Here'),
            'default' => esc_html('+')
        ]);

        $this->add_control('suffix_number', [
            'type' => \Elementor\Controls_Manager::TEXT,
            'label' => esc_html('Suffix Number'),
            'placeholder' => esc_html('Input Your Prefix Number Here'),
            'default' => esc_html('K')
        ]);

        $this->add_control(
            'hr2',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ]
        );

        $this->add_control(
            'counter_duration',
            [
                'label' => esc_html__('Animation Speed', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 5000,
                        'step' => 5,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 1000
                ]
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('icons_content', [
            'label' => esc_html('Icon'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT
        ]);

        $this->add_control(
            'show_icon',
            [
                'label' => esc_html__('Use Icon', 'textdomain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'textdomain'),
                'label_off' => esc_html__('No', 'textdomain'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'icon',
            [
                'label' => esc_html__('Icon', 'textdomain'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-users',
                    'library' => 'fa-solid',
                ],
                'condition' => [
                    'show_icon' => 'yes'
                ]
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('wrapper_style', [
            'label' => esc_html('Wrapper'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_responsive_control(
            'wrapper_position',
            [
                'label' => esc_html__('Position', 'textdomain'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__('Left', 'textdomain'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'textdomain'),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'end' => [
                        'title' => esc_html__('Right', 'textdomain'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'default' => 'center',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .rkit-counter' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'alignment',
            [
                'label' => esc_html__('Alignment', 'textdomain'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__('Left', 'textdomain'),
                        'icon' => 'eicon-align-start-h',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'textdomain'),
                        'icon' => 'eicon-align-center-h',
                    ],
                    'end' => [
                        'title' => esc_html__('Right', 'textdomain'),
                        'icon' => 'eicon-align-end-h',
                    ],
                ],
                'default' => 'center',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .rkit-counter-wrapper, {{WRAPPER}} .rkit-counter-container' => 'align-items:{{VALUE}}'
                ]
            ]
        );

        $this->add_control(
            'hr_dimension_wrapper',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ]
        );

        $this->add_responsive_control(
            'padding_wrapper',
            [
                'label' => esc_html__('Padding', 'textdomain'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-counter-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'margin_wrapper',
            [
                'label' => esc_html__('Margin', 'textdomain'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-counter-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'radius_wrapper',
            [
                'label' => esc_html__('Border Radius', 'textdomain'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-counter-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'hr_bg_wrapper',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'background_wrapper',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-counter-wrapper',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow_wrapper',
                'selector' => '{{WRAPPER}} .rkit-counter-wrapper',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'border_wrapper',
                'selector' => '{{WRAPPER}} .rkit-counter-wrapper',
            ]
        );

        $this->add_control(
            'wrapper_blur_effect',
            [
                'label' => esc_html__('Blur Effect', 'textdomain'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-counter-wrapper' => 'backdrop-filter: blur({{SIZE}}{{UNIT}});',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('counter_style', [
            'label' => esc_html('Counter'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'counter_typography',
                'selector' => '{{WRAPPER}} .odometer-container',
            ]
        );

        $this->add_control('counter_color', [
            'type' => \Elementor\Controls_Manager::COLOR,
            'label' => esc_html('Counter Color'),
            'selectors' => [
                '{{WRAPPER}} .odometer-container' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'counter_text_stroke',
                'selector' => '{{WRAPPER}} .odometer-container',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'counter_text_shadow',
                'selector' => '{{WRAPPER}} .odometer-container',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('counter_title_style', [
            'label' => esc_html('Title'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);


        $this->add_responsive_control(
            'title_position',
            [
                'label' => esc_html__('Position', 'textdomain'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'column-reverse' => [
                        'title' => esc_html__('Top', 'textdomain'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'column' => [
                        'title' => esc_html__('Bottom', 'textdomain'),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'default' => 'column',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .rkit-counter-container' => 'flex-direction: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'counter_title_typography',
                'selector' => '{{WRAPPER}} .counter-title-container .counter-title',
            ]
        );

        $this->add_control('counter_title_color', [
            'type' => \Elementor\Controls_Manager::COLOR,
            'label' => esc_html('Counter Color'),
            'selectors' => [
                '{{WRAPPER}} .counter-title-container .counter-title' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'counter_title_text_stroke',
                'selector' => '{{WRAPPER}} .counter-title-container .counter-title',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'counter_title_text_shadow',
                'selector' => '{{WRAPPER}} .counter-title-container .counter-title',
            ]
        );

        $this->add_responsive_control(
            'title_spacing',
            [
                'label' => esc_html__('Spacing', 'textdomain'),
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
                    '{{WRAPPER}} .rkit-counter-container' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('icon_style', [
            'label' => esc_html('Icon'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [
                'show_icon' => 'yes'
            ]
        ]);


        $this->add_responsive_control(
            'icon_position',
            [
                'label' => esc_html__('Position', 'textdomain'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'row' => [
                        'title' => esc_html__('Left', 'textdomain'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'column' => [
                        'title' => esc_html__('Top', 'textdomain'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'column-reverse' => [
                        'title' => esc_html__('Bottom', 'textdomain'),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                    'row-reverse' => [
                        'title' => esc_html__('Right', 'textdomain'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'default' => 'column',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .rkit-counter-wrapper' => 'flex-direction: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_align',
            [
                'label' => esc_html__('Vertical Alignment', 'textdomain'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__('Top', 'textdomain'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'textdomain'),
                        'icon' => 'eicon-v-align-middle',
                    ],
                    'end' => [
                        'title' => esc_html__('Bottom', 'textdomain'),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'default' => 'center',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .rkit-counter-wrapper' => 'align-items: {{VALUE}};',
                ],
                'condition' => [
                    'icon_position' => ['row', 'row-reverse']
                ]
            ]
        );

        $this->add_control('icon_color', [
            'type' => \Elementor\Controls_Manager::COLOR,
            'label' => esc_html('Icon Color'),
            'selectors' => [
                '{{WRAPPER}} i.counter-icon' => 'color : {{VALUE}}',
                '{{WRAPPER}} svg.counter-icon' => 'fill : {{VALUE}}',
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'icons_stroke',
                'label' => esc_html('Icon Stroke'),
                'selector' => '{{WRAPPER}} .counter-icon',
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => esc_html__('Icon Size', 'textdomain'),
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
                    '{{WRAPPER}} i.counter-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} svg.counter-icon' => 'width: {{SIZE}}{{UNIT}};height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'icon_spacing',
            [
                'label' => esc_html__('Icon Spacing', 'textdomain'),
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
                    '{{WRAPPER}} .rkit-counter-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'icon_wrapper_options',
            [
                'label' => esc_html__('Icons Wrapper', 'textdomain'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'icon_wrapper_background',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .counter-icon-container',
            ]
        );

        $this->add_responsive_control(
            'icon_wrapper_padding',
            [
                'label' => esc_html__('Padding', 'textdomain'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .counter-icon-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_wrapper_radius',
            [
                'label' => esc_html__('Border Radius', 'textdomain'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .counter-icon-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'icon_wrapper_border',
                'selector' => '{{WRAPPER}} .counter-icon-container',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'icon_wrapper_box_shadow',
                'selector' => '{{WRAPPER}} .counter-icon-container',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('prefix_style', [
            'label' => esc_html('Prefix Number'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [
                'prefix_number!' => ''
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'prefix_typography',
                'selector' => '{{WRAPPER}} .prefix_number',
            ]
        );

        $this->add_responsive_control(
            'prefix_vertical_align',
            [
                'label' => esc_html__('Vertical Alignment', 'textdomain'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'start' => esc_html__('Top', 'textdomain'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'textdomain'),
                        'icon' => 'eicon-v-align-middle',
                    ],
                    'end' => [
                        'title' => esc_html__('bottom', 'textdomain'),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'default' => 'center',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .prefix_number' => 'align-items: {{VALUE}};',
                ],
            ]
        );

        $this->add_control('prefix_number_color', [
            'type' => \Elementor\Controls_Manager::COLOR,
            'label' => esc_html('Color'),
            'selectors' => [
                '{{WRAPPER}} .prefix_number' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'prefix_number_stroke',
                'selector' => '{{WRAPPER}} .prefix_number',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'prefix_number_shadow',
                'selector' => '{{WRAPPER}} .prefix_number',
            ]
        );

        $this->add_responsive_control(
            'prefix_spacing',
            [
                'type' => \Elementor\Controls_Manager::SLIDER,
                'label' => esc_html__('Spacing', 'textdomain'),
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .prefix_number' => 'margin-right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('suffix_style', [
            'label' => esc_html('Suffix Number'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [
                'suffix_number!' => ''
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'suffix_typography',
                'selector' => '{{WRAPPER}} .suffix_number',
            ]
        );

        $this->add_responsive_control(
            'suffix_vertical_align',
            [
                'label' => esc_html__('Vertical Alignment', 'textdomain'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'start' => esc_html__('Top', 'textdomain'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'textdomain'),
                        'icon' => 'eicon-v-align-middle',
                    ],
                    'end' => [
                        'title' => esc_html__('bottom', 'textdomain'),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'default' => 'center',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .suffix_number' => 'align-items: {{VALUE}};',
                ],
            ]
        );

        $this->add_control('suffix_number_color', [
            'type' => \Elementor\Controls_Manager::COLOR,
            'label' => esc_html('Color'),
            'selectors' => [
                '{{WRAPPER}} .suffix_number' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'suffix_number_stroke',
                'selector' => '{{WRAPPER}} .suffix_number',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'suffix_number_shadow',
                'selector' => '{{WRAPPER}} .suffix_number',
            ]
        );

        $this->add_responsive_control(
            'suffix_spacing',
            [
                'type' => \Elementor\Controls_Manager::SLIDER,
                'label' => esc_html__('Spacing', 'textdomain'),
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .suffix_number' => 'margin-left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $startNumber = $settings['starting_number'];
        $endNumber = $settings['end_number'];
        $format = $settings['format_number'];
        $prefix = $settings['prefix_number'];
        $suffix = $settings['suffix_number'];
        $duration = $settings['counter_duration'];
        $title = $settings['title_text'];

        switch ($settings['title_tag']) {
            case 'h1':
                $tag = 'h1';
                break;
            case 'h2':
                $tag = 'h2';
                break;
            case 'h3':
                $tag = 'h3';
                break;
            case 'h4':
                $tag = 'h4';
                break;
            case 'h5':
                $tag = 'h5';
                break;
            case 'h6':
                $tag = 'h6';
                break;
            case 'span':
                $tag = 'span';
                break;
            case 'div':
                $tag = 'div';
                break;
            default:
                $tag = 'span';
                break;
        }

        $conf = [
            'start_number' => $startNumber,
            'end_number' => $endNumber,
            'duration' => $duration['size'],
            'format' => $format
        ];
?>

        <div class="rkit-counter">
            <div class="rkit-counter-wrapper">
                <?php if ($settings['show_icon'] === 'yes'): ?>
                    <div class="counter-icon-container">
                        <?php \Elementor\Icons_Manager::render_icon($settings['icon'], ['aria-hidden' => 'true', 'class' => 'counter-icon']); ?>
                    </div>
                <?php endif; ?>
                <div class="rkit-counter-container">
                    <div class="odometer-container">
                        <span class="prefix_number"><?php echo esc_html($prefix) ?></span>
                        <div class="odometer counter" data-config="<?php echo esc_attr(json_encode($conf)) ?>">
                            <?php echo esc_html($startNumber) ?>
                        </div>
                        <span class="suffix_number"><?php echo esc_html($suffix) ?></span>
                    </div>
                    <div class="counter-title-container">
                        <<?php echo esc_html($tag); ?> class="counter-title"><?php echo esc_html($title); ?></<?php echo esc_html($tag); ?>>
                    </div>
                </div>
            </div>
        </div>

<?php
    }
}
