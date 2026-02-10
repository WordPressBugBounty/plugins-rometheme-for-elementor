<?php

namespace RTMKit\Elements;

class PieChart extends \Elementor\Widget_Base
{
    private function get_widget_data()
    {
        return \RTMkit\Modules\Widgets\WidgetStorage::instance()->get_widget_data_by_key('piechart');
    }

    public function get_name()
    {
        return 'rkit-pie-chart';
    }
    public function get_title()
    {
        return $this->get_widget_data()['name'];
    }

    public function get_icon()
    {
        $icon = 'rkit-widget-icon ' . $this->get_widget_data()['icon'];
        return $icon;
    }

    function get_custom_help_url()
    {
        return 'https://support.rometheme.net/docs/romethemekit/widgets/how-to-use-ezd_ampersand-customize-pie-chart-widget/';
    }

    public function get_categories()
    {
        return ['romethemekit_widgets'];
    }

    public function get_script_depends()
    {
        return ['rtmkit-element-pie_chart', 'rtmkit-lib-chart.min'];
    }

    public function get_style_depends()
    {
        return ['rtmkit-element-piechart'];
    }

    protected function register_controls()
    {
        $this->start_controls_section('content_section', [
            'label' => esc_html('Pie Chart'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT
        ]);

        $this->add_control('chart_type', [
            'label' => esc_html('Chart Type'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'pie' => esc_html('Pie'),
                'doughnut' => esc_html('Doughnut')
            ],
            'default' => 'pie',
        ]);

        $this->add_control('data_name', [
            'label' => esc_html('Data Name'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'placeholder' => esc_html('Input Your Data Name Here'),
            'default' => esc_html('Data Example')
        ]);

        $item = new \Elementor\Repeater();

        $item->add_control('item_label', [
            'label' => esc_html('Label'),
            'type' => \Elementor\Controls_Manager::TEXT
        ]);

        $item->add_control('item_value', [
            'label' => esc_html('Value'),
            'type' => \Elementor\Controls_Manager::NUMBER
        ]);

        $item->start_controls_tabs('item_tabs');

        $item->start_controls_tab('item_tab_normal', ['label' => esc_html('Normal')]);

        $item->add_control('item_bg_normal', [
            'label' => esc_html('Background'),
            'type' => \Elementor\Controls_Manager::COLOR
        ]);

        $item->add_control(
            'border-popover-toggle',
            [
                'type' => \Elementor\Controls_Manager::POPOVER_TOGGLE,
                'label' => esc_html__('Border', 'textdomain'),
                'label_off' => esc_html__('Default', 'textdomain'),
                'label_on' => esc_html__('Custom', 'textdomain'),
                'return_value' => 'yes',
            ]
        );

        $item->start_popover();

        $item->add_control(
            'border_width_normal',
            [
                'label' => esc_html__('Border Width', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
            ]
        );

        $item->add_control('item_border_color_normal', [
            'label' => esc_html('Border Color'),
            'type' => \Elementor\Controls_Manager::COLOR
        ]);

        $item->end_popover();

        $item->add_responsive_control('offset_normal', [
            'label' => esc_html('Offset'),
            'type' => \Elementor\Controls_Manager::SLIDER,
        ]);

        $item->end_controls_tab();

        $item->start_controls_tab('item_tab_hover', ['label' => esc_html('Hover')]);

        $item->add_control('item_bg_hover', [
            'label' => esc_html('Background'),
            'type' => \Elementor\Controls_Manager::COLOR
        ]);

        $item->add_control(
            'border-popover-toggle-hover',
            [
                'type' => \Elementor\Controls_Manager::POPOVER_TOGGLE,
                'label' => esc_html__('Border', 'textdomain'),
                'label_off' => esc_html__('Default', 'textdomain'),
                'label_on' => esc_html__('Custom', 'textdomain'),
                'return_value' => 'yes',
            ]
        );

        $item->start_popover();

        $item->add_responsive_control(
            'border_width_hover',
            [
                'label' => esc_html__('Border Width', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
            ]
        );

        $item->add_control('item_border_color_hover', [
            'label' => esc_html('Border Color'),
            'type' => \Elementor\Controls_Manager::COLOR
        ]);

        $item->end_popover();


        $item->add_responsive_control('offset_hover', [
            'label' => esc_html('Offset'),
            'type' => \Elementor\Controls_Manager::SLIDER,
        ]);


        $item->end_controls_tab();

        $item->end_controls_tabs();

        $this->add_control('item_list', [
            'label' => esc_html('Data List'),
            'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $item->get_controls(),
            'default' => [
                [
                    'item_label' => esc_html('Data 1'),
                    'item_value' => 50,
                    'item_bg_normal' => 'rgb(255, 99, 132)',
                    'item_bg_hover' => 'rgb(255, 99, 132)',
                ],
                [
                    'item_label' => esc_html('Data 2'),
                    'item_value' => 90,
                    'item_bg_normal' => 'rgb(54, 162, 235)',
                    'item_bg_hover' => 'rgb(54, 162, 235)',
                ],
                [
                    'item_label' => esc_html('Data 3'),
                    'item_value' => 100,
                    'item_bg_normal' => 'rgb(255, 205, 86)',
                    'item_bg_hover' => 'rgb(255, 205, 86)',
                ],
                [
                    'item_label' => esc_html('Data 4'),
                    'item_value' => 30,
                    'item_bg_normal' => '#91C8E4',
                    'item_bg_hover' => '#91C8E4',
                ],
                [
                    'item_label' => esc_html('Data 5'),
                    'item_value' => 120,
                    'item_bg_normal' => '#9DB2BF',
                    'item_bg_hover' => '#9DB2BF',
                ]
            ],
            'title_field' => '{{{ item_label }}}'
        ]);

        $this->end_controls_section();

        $this->start_controls_section('title_description', [
            'label' => esc_html('Title & Description'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT
        ]);

        $this->add_control(
            'show_title',
            [
                'label' => esc_html__('Show Title', 'textdomain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'textdomain'),
                'label_off' => esc_html__('Hide', 'textdomain'),
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        $this->add_control('title_text', [
            'label' => esc_html('Title'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => esc_html('Awesome Chart'),
            'condition' => [
                'show_title' => 'yes'
            ]
        ]);

        $this->add_control('title_tag', [
            'label' => esc_html('Title HTML Tag'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'h1' => 'H1',
                'h2' => 'H2',
                'h3' => 'H3',
                'h4' => 'H4',
                'h5' => 'H5',
                'h6' => 'H6',
                'div' => 'div',
                'span' => 'span',
                'p' => 'p',
            ],
            'default' => 'h3',
            'condition' => [
                'show_title' => 'yes'
            ]
        ]);

        $this->add_control(
            'show_description',
            [
                'label' => esc_html__('Show Description', 'textdomain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'textdomain'),
                'label_off' => esc_html__('Hide', 'textdomain'),
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        $this->add_control('description_text', [
            'label' => esc_html('Description'),
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => esc_html('Show data proportions clearly using colorful segments that make percentage comparisons simple, engaging, and easy to understand.'),
            'condition' => [
                'show_description' => 'yes'
            ]
        ]);

        $this->end_controls_section();


        $this->start_controls_section('pie_style', [
            'label' => esc_html('Chart'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        // $this->add_control(
        //     'custom_panel_notice',
        //     [
        //         'type' => \Elementor\Controls_Manager::NOTICE,
        //         'notice_type' => 'warning',
        //         'dismissible' => true,
        //         'heading' => esc_html__('Global Color Not Working', 'rometheme-for-elementor'),
        //         'content' => esc_html__('Global color is not working or has no effect on this element, possibly due to unsupported settings or limitations of the element.', 'rometheme-for-elementor'),
        //     ]
        // );

        $this->add_responsive_control(
            'pie_width',
            [
                'label' => esc_html__('Width', 'textdomain'),
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
                'default' => [
                    'unit' => '%',
                    'size' => 50,
                ],
                'selectors' => [
                    '{{WRAPPER}} .piechart-wrapper' => 'width: {{SIZE}}{{UNIT}};',
                ],
                'conditions' => [
                    'relation' => 'or',
                    'terms' => [
                        [
                            'name' => 'show_title',
                            'operator' => '===',
                            'value' => 'yes',
                        ],
                        [
                            'name' => 'show_description',
                            'operator' => '===',
                            'value' => 'yes',
                        ],
                    ],
                ],
            ]
        );

        $this->add_responsive_control('spacing', [
            'label' => esc_html('Spacing'),
            'type' => \Elementor\Controls_Manager::SLIDER
        ]);

        $this->add_responsive_control('rotation', [
            'label' => esc_html('Rotation'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'range' => [
                'px' => [
                    'min' => -100,
                    'max' => 100
                ]
            ]
        ]);

        $this->add_responsive_control('border_radius', [
            'label' => esc_html('Border Radius'),
            'type' => \Elementor\Controls_Manager::SLIDER
        ]);

        $this->end_controls_section();

        $this->start_controls_section('legend_style', [
            'label' => esc_html('Legend'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_control(
            'show_legend',
            [
                'label' => esc_html__('Show Legend', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'rometheme-for-elementor'),
                'label_off' => esc_html__('No', 'rometheme-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'legend-typography-popover-toggle',
            [
                'label' => esc_html__('Typhography', 'textdomain'),
                'type' => \Elementor\Controls_Manager::POPOVER_TOGGLE,
                'label_off' => esc_html__('Default', 'textdomain'),
                'label_on' => esc_html__('Custom', 'textdomain'),
                'return_value' => 'yes',
                'condition' => [
                    'show_legend' => 'yes'
                ]
            ]
        );

        $this->start_popover();

        $this->add_control(
            'legend_font_family',
            [
                'label' => esc_html__('Font Family', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::FONT,
                'default' => "'Open Sans', sans-serif",
                'condition' => [
                    'show_legend' => 'yes'
                ]
            ]
        );

        $this->add_responsive_control('legend_font_size', [
            'label'  => esc_html('Font Size'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'condition' => [
                'show_legend' => 'yes'
            ]
        ]);

        $this->add_control('legend_color', [
            'label'  => esc_html('Font Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#000000',
            'condition' => [
                'show_legend' => 'yes'
            ]
        ]);

        $this->add_control('legend_font_style', [
            'label'  => esc_html('Font Style'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'normal' => esc_html('Normal'),
                'italic' => esc_html('Italic'),
            ],
            'default'  => 'normal',
            'condition' => [
                'show_legend' => 'yes'
            ]
        ]);

        $this->add_control('legend_font_weight', [
            'label'  => esc_html('Font Weight'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                '100' => esc_html('100 (Thin)'),
                '200' => esc_html('200 (Extra Light)'),
                '300' => esc_html('300 (Light)'),
                '400' => esc_html('400 (Normal)'),
                '500' => esc_html('500 (Medium)'),
                '600' => esc_html('600 (Semi Bold)'),
                '700' => esc_html('700 (Bold)'),
                '800' => esc_html('800 (Extra Bold)'),
                '900' => esc_html('900 (Black)'),
            ],
            'default'  => '400',
            'condition' => [
                'show_legend' => 'yes'
            ]
        ]);

        $this->end_popover();

        $this->add_control('legend_position', [
            'label' => esc_html('Position'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'top' => esc_html('Top'),
                'left' => esc_html('Left'),
                'right' => esc_html('Right'),
                'bottom' => esc_html('Bottom'),
            ],
            'default'  => 'top',
            'condition'  => [
                'show_legend' => 'yes'
            ]
        ]);

        $this->end_controls_section();

        $this->start_controls_section('text_description_style', [
            'label' => esc_html('Title & Description'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            'conditions' => [
                'relation' => 'or',
                'terms' => [
                    [
                        'name' => 'show_title',
                        'operator' => '===',
                        'value' => 'yes',
                    ],
                    [
                        'name' => 'show_description',
                        'operator' => '===',
                        'value' => 'yes',
                    ],
                ],
            ],
        ]);

        $this->add_control(
            'wrapper_options',
            [
                'label' => esc_html__('Wrapper', 'textdomain'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'text_description_position',
            [
                'label' => esc_html__('Position', 'textdomain'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'column-reverse' => [
                        'title' => esc_html__('Top', 'textdomain'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'row-reverse' => [
                        'title' => esc_html__('Left', 'textdomain'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'row' => [
                        'title' => esc_html__('Right', 'textdomain'),
                        'icon' => 'eicon-h-align-right',
                    ],
                    'column' => [
                        'title' => esc_html__('Bottom', 'textdomain'),
                        'icon' => 'eicon-v-align-bottom',
                    ],
                ],
                'default' => 'row',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .rkit-piechart-container' => 'flex-direction: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'text_description_vertical_position',
            [
                'label' => esc_html__('Vertical Position', 'textdomain'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__('Top', 'textdomain'),
                        'icon' => 'eicon-align-start-v',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'textdomain'),
                        'icon' => 'eicon-align-center-v',
                    ],
                    'end' => [
                        'title' => esc_html__('Bottom', 'textdomain'),
                        'icon' => 'eicon-align-end-v',
                    ]
                ],
                'default' => 'center',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .rkit-piechart-container' => 'align-items: {{VALUE}};',
                ],
                'condition' => [
                    'text_description_position' => ['row-reverse', 'row']
                ],
            ]
        );

        $this->add_responsive_control(
            'margin_text_description',
            [
                'label' => esc_html__('Margin', 'textdomain'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-piechart-description-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'padding_text_description',
            [
                'label' => esc_html__('Padding', 'textdomain'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-piechart-description-container' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'radius_text_description',
            [
                'label' => esc_html__('Border Radius', 'textdomain'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-piechart-description-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('text_description_wrapper_tabs');

        $this->start_controls_tab(
            'text_description_wrapper_normal_tab',
            [
                'label' => esc_html__('Normal', 'textdomain'),
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'text_wrapper_background',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-piechart-description-container',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'text_description_border',
                'selector' => '{{WRAPPER}} .rkit-piechart-description-container',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'text_description_box_shadow',
                'selector' => '{{WRAPPER}} .rkit-piechart-description-container',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'text_description_wrapper_hover_tab',
            [
                'label' => esc_html__('Hover', 'textdomain'),
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'text_wrapper_background_hover',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-piechart-description-container:hover',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'text_description_border_hover',
                'selector' => '{{WRAPPER}} .rkit-piechart-description-container:hover',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'text_description_box_shadow_hover',
                'selector' => '{{WRAPPER}} .rkit-piechart-description-container:hover',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'title_options',
            [
                'label' => esc_html__('Title', 'textdomain'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'show_title' => 'yes'
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .rkit-piechart-title',
                'condition' => [
                    'show_title' => 'yes'
                ]
            ]
        );

        $this->start_controls_tabs('title_color_tabs', [
            'condition' => [
                'show_title' => 'yes'
            ]
        ]);

        $this->start_controls_tab(
            'title_color_normal_tab',
            [
                'label' => esc_html__('Normal', 'textdomain'),
            ]
        );

        $this->add_control('title_color_normal', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-piechart-title' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'text_stroke_normal',
                'selector' => '{{WRAPPER}} .rkit-piechart-title',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'text_shadow_normal',
                'selector' => '{{WRAPPER}} .rkit-piechart-title',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'title_color_hover_tab',
            [
                'label' => esc_html__('Hover', 'textdomain'),
            ]
        );

        $this->add_control('title_color_hover', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-piechart-description-container:hover .rkit-piechart-title' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'text_stroke_hover',
                'selector' => '{{WRAPPER}} .rkit-piechart-description-container:hover .rkit-piechart-title',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'text_shadow_hover',
                'selector' => '{{WRAPPER}} .rkit-piechart-description-container:hover .rkit-piechart-title',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'description_options',
            [
                'label' => esc_html__('Description', 'textdomain'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'show_description' => 'yes'
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'description_typography',
                'selector' => '{{WRAPPER}} .rkit-piechart-description',
                'condition' => [
                    'show_description' => 'yes'
                ]
            ]
        );

        $this->start_controls_tabs('description_color_tabs', [
            'condition' => [
                'show_description' => 'yes'
            ]
        ]);

        $this->start_controls_tab(
            'description_color_normal_tab',
            [
                'label' => esc_html__('Normal', 'textdomain'),
            ]
        );

        $this->add_control('description_color_normal', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-piechart-description' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'description_stroke_normal',
                'selector' => '{{WRAPPER}} .rkit-piechart-description',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'description_shadow_normal',
                'selector' => '{{WRAPPER}} .rkit-piechart-description',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'description_color_hover_tab',
            [
                'label' => esc_html__('Hover', 'textdomain'),
            ]
        );

        $this->add_control('description_color_hover', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-piechart-description-container:hover .rkit-piechart-description' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'description_stroke_hover',
                'selector' => '{{WRAPPER}} .rkit-piechart-description-container:hover .rkit-piechart-description',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'description_shadow_hover',
                'selector' => '{{WRAPPER}} .rkit-piechart-description-container:hover .rkit-piechart-description',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();


        $this->end_controls_section();
    }


    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $label = [];
        $value = [];
        $background = [];
        $backgroundHover = [];
        $borderColor = [];
        $hoverBorderColor = [];
        $offset = [];
        $hoverOffset = [];
        $borderWidth = [];
        $borderWidthHover = [];

        foreach ($settings['item_list'] as $item) {

            $backgroundNormal = $this->get_color_code($item, 'item_bg_normal');
            $bgHover = $this->get_color_code($item, 'item_bg_hover');
            $borderColorCode = $this->get_color_code($item, 'item_border_color_normal');
            $borderHoverColorCode = $this->get_color_code($item, 'item_border_color_hover');
            // echo $backgroundNormal;
            array_push($label, $item['item_label']);
            array_push($value, $item['item_value']);
            array_push($background, $backgroundNormal);
            array_push($backgroundHover, $bgHover);
            array_push($borderColor, $borderColorCode);
            array_push($hoverBorderColor, $borderHoverColorCode);
            array_push($offset, $item['offset_normal']['size']);
            array_push($hoverOffset, $item['offset_hover']['size']);
            array_push($borderWidth, $item['border_width_normal']['size']);
            array_push($borderWidthHover, $item['border_width_hover']['size']);
        }
        $datasets = [
            'label' => $settings['data_name'],
            'data' => $value,
            'borderRadius' => !empty($settings['border_radius']['size']) ? $settings['border_radius']['size'] : 0,
            'backgroundColor' => $background,
            'hoverBackgroundColor' => $backgroundHover,
            'borderColor' => $borderColor,
            'borderWidth' => $borderWidth,
            'hoverBorderColor' => $hoverBorderColor,
            'hoverBorderWidth' => $borderWidthHover,
            'offset' => $offset,
            'hoverOffset' => $hoverOffset,
            'spacing' => !empty($settings['spacing']['size']) ? $settings['spacing']['size'] : 0,
            'rotation' => !empty($settings['rotation']['size']) ? $settings['rotation']['size'] : 0,
        ];


        $legend = [
            'display' => ($settings['show_legend'] === 'yes') ? true : false,
            'position' => $settings['legend_position'],
            'labels' => [
                'color'  => $this->get_color_code($settings, 'legend_color'),
                'font' => [
                    'family' => $settings['legend_font_family'],
                    'size'  => !empty($settings['legend_font_size']['size']) ? $settings['legend_font_size']['size'] : 12,
                    'style' => $settings['legend_font_style'],
                    'weight' => $settings['legend_font_weight'],
                ]
            ]
        ];

        if ($settings['show_title'] === 'yes') {
            switch ($settings['title_tag']) {
                case 'h1':
                    $title_tag = 'h1';
                    break;
                case 'h2':
                    $title_tag = 'h2';
                    break;
                case 'h3':
                    $title_tag = 'h3';
                    break;
                case 'h4':
                    $title_tag = 'h4';
                    break;
                case 'h5':
                    $title_tag = 'h5';
                    break;
                case 'h6':
                    $title_tag = 'h6';
                    break;
                case 'div':
                    $title_tag = 'div';
                    break;
                case 'span':
                    $title_tag = 'span';
                    break;
                case 'p':
                    $title_tag = 'p';
                    break;
                default:
                    $title_tag = 'h3';
            }
        }


?>
        <div class="rkit-piechart-container">
            <div class="piechart-wrapper">
                <canvas id="pieChart" width="600" height="400" data-type="<?php echo esc_attr($settings['chart_type']) ?>" data-name="<?php echo esc_attr($settings['data_name']) ?>" data-label="<?php echo esc_attr(json_encode($label));  ?>" data-datasets="<?php echo esc_attr(json_encode($datasets)) ?>" data-legend="<?php echo esc_attr(json_encode($legend)) ?>"></canvas>
            </div>
            <?php if ($settings['show_title'] === 'yes' || $settings['show_description'] === 'yes') : ?>
                <div class="rkit-piechart-description-container">
                    <?php if ($settings['show_title'] === 'yes') : ?>
                        <<?php echo esc_html($title_tag) ?> class="rkit-piechart-title">
                            <?php echo esc_html($settings['title_text']); ?>
                        </<?php echo esc_html($title_tag) ?>>
                    <?php endif; ?>
                    <?php if ($settings['show_description'] === 'yes') : ?>
                        <div class="rkit-piechart-description">
                            <?php echo esc_html($settings['description_text']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
<?php
    }

    private function get_color_code($item, $control)
    {
        // echo json_encode($item['__globals__']);
        $globalValue = $item['__globals__'][$control] ?? null;
        $localValue  = $item[$control] ?? null;

        if (!empty($globalValue)) {
            $color = $this->get_global_color_code(
                str_replace('globals/colors?id=', '', $globalValue)
            );
        } else {
            $color = $localValue;
        }

        return $color;
    }

    private function get_global_color_code($global_id)
    {
        $kits = \Elementor\Plugin::$instance->kits_manager->get_active_kit();
        $global_colors = $kits->get_settings_for_display('system_colors');
        $custom_colors = $kits->get_settings_for_display('custom_colors');

        $all_global_colors = array_merge($global_colors, $custom_colors);

        if (empty($global_colors) || ! is_array($global_colors)) {
            return null;
        }

        foreach ($all_global_colors as $color) {
            if (isset($color['_id']) && $color['_id'] === $global_id) {
                return $color['color']; // hasilnya misal "#ff0000"
            }
        }

        return null;
    }
}
