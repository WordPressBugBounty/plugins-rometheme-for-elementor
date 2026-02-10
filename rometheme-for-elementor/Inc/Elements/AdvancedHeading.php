<?php

namespace RTMKit\Elements;

class AdvancedHeading extends \Elementor\Widget_Base
{
    private function get_widget_data()
    {
        return \RTMkit\Modules\Widgets\WidgetStorage::instance()->get_widget_data_by_key('advancedheading');
    }

    public function get_name()
    {
        return 'rkit_advanced_heading';
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

    public function get_keywords()
    {
        return ['rometheme', 'heading', 'animation', 'advanced', 'animation text', ' heading'];
    }

    function get_custom_help_url()
    {
        return 'https://support.rometheme.net/docs/romethemekit/widgets/how-to-use-ezd_ampersand-customize-advanced-heading-widget/';
    }

    public function get_categories()
    {
        return ['romethemekit_widgets'];
    }

    public function get_style_depends()
    {
        return ['rtmkit-element-advanced_heading'];
    }
    protected function is_dynamic_content(): bool
    {
        return false;
    }
    protected function register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html__('Content', 'rometheme-kit'),
            ]
        );

        $this->add_control(
            'text',
            [
                'label' => esc_html__('Text', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'rows' => 10,
                'default' => esc_html__('Example {{Headline Text}} for this {{Faster}} Page', 'rometheme-for-elementor'),
                'placeholder' => esc_html__('Type your text here', 'rometheme-for-elementor'),
                'description' => esc_html('The {{ }} symbols are used to indicate that the text will be given animation effects. If there are multiple texts, separate them with commas inside the {{ }}.')
            ]
        );

        $this->add_control('html_tag', [
            'label' => esc_html('HTML Tag'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'h1' => esc_html('H1'),
                'h2' => esc_html('H2'),
                'h3' => esc_html('H3'),
                'h4' => esc_html('H4'),
                'h5' => esc_html('H5'),
                'h6' => esc_html('H6'),
            ],
            'default' => 'h1'
        ]);

        $this->add_control(
            '_link',
            [
                'label' => esc_html__('Link', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-link.com', 'rometheme-for-elementor'),
                'options' => ['url', 'is_external', 'nofollow'],
                'default' => [
                    'url' => '',
                    'is_external' => true,
                    'nofollow' => true,
                ],
                'label_block' => true,
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('background_text', [
            'label' => esc_html('Background Text'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT
        ]);

        $this->add_control(
            'show_background_text',
            [
                'label' => esc_html__('Use Background Text', 'textdomain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'textdomain'),
                'label_off' => esc_html__('No', 'textdomain'),
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        $this->add_control('background_text_heading', [
            'type' => \Elementor\Controls_Manager::TEXT,
            'label' => esc_html('Text'),
            'default' => esc_html('Awesome Heading'),
            'condition' => [
                'show_background_text' => 'yes'
            ]
        ]);

        $this->end_controls_section();

        $this->start_controls_section(
            'wrapper_style',
            [
                'label' => esc_html__('Wrapper', 'rometheme-kit'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'alignment',
            [
                'label' => esc_html__('Alignment', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'rometheme-kit'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'rometheme-kit'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'rometheme-kit'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'left',
                'selectors' => [
                    '{{WRAPPER}} .rkit-advanced-heading-wrapper' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'cont_advanced',
                'types' => ['classic', 'gradient',],
                'selector' => '{{WRAPPER}} .rkit-advanced-heading-wrapper',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'container_box_shadow_ah',
                'label' => __('Box Shadow', 'rometheme-for-elementor'),
                'selector' => '{{WRAPPER}} .rkit-advanced-heading-wrapper',
                'description' => esc_html__('Put 0 for no box shadow ', 'text-domain'),
            ]
        );

        $this->add_responsive_control(
            'cont_radius',
            [
                'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-advanced-heading-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'cont_advanced_padding',
            [
                'label' => esc_html__('Padding', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-advanced-heading-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'heading_style_section',
            [
                'label' => esc_html__('Text', 'rometheme-kit'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );


        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'wrap_head_typography',
                'selector' => '{{WRAPPER}} .rkit-advanced-heading , {{WRAPPER}} .rkit-advanced-heading  .text ',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'wrap_head_text_stroke',
                'selector' => '{{WRAPPER}} .rkit-advanced-heading .text',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'wrap_head_text_shadow',
                'selector' => '{{WRAPPER}} .rkit-advanced-heading .text',
            ]
        );

        $this->add_control(
            'animation_heading_gradient',
            [
                'label' => esc_html__('Animation Gradient', 'textdomain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'textdomain'),
                'label_off' => esc_html__('No', 'textdomain'),
                'return_value' => 'enabled',
                'default' => '',
                'prefix_class' => 'animation-heading-gradient-'
            ]
        );

        $this->add_control(
            'heading_animation_speed',
            [
                'type' => \Elementor\Controls_Manager::SLIDER,
                'label' => esc_html__('Animation Speed', 'textdomain'),
                'size_units' => ['s', 'ms'],
                'range' => [
                    's' => [
                        'min' => 1,
                        'max' => 10,
                        'step' => 0.5
                    ],
                    'ms' => [
                        'min' => 1,
                        'max' => 10000,
                        'step' => 100
                    ],
                ],
                'default' => [
                    'size' => 3,
                    'unit' => 's'
                ],
                'selectors' => [
                    '{{WRAPPER}}.animation-heading-gradient-enabled .text' => 'animation-duration:{{SIZE}}{{UNIT}}'
                ],
                'condition' => [
                    'animation_heading_gradient' => 'enabled'
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'wrap_headingtext_background',
                'types' => ['classic', 'gradient'],
                'exclude' => ['image'],
                'selector' => '{{WRAPPER}} .rkit-advanced-heading .text',
                'fields_options' => [
                    'background' => [
                        'label' => esc_html('Text Color')
                    ],
                    'color' => [
                        'selectors' => [
                            '{{SELECTOR}}' => 'background-color: {{VALUE}}; text-decoration-color: {{VALUE}};',
                        ],
                    ],
                    'color_b' => [
                        'selectors' => [
                            '{{SELECTOR}}' => 'text-decoration-color: {{VALUE}};',
                        ],
                    ]
                ],
                'condition' => [
                    'animation_heading_gradient!' => 'enabled'
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'wrap_headtext_background_gradient',
                'types' => ['gradient'],
                'exclude' => ['image'],
                'selector' => '{{WRAPPER}} .rkit-advanced-heading .text',
                'fields_options' => [
                    'background' => [
                        'label' => esc_html('Text Color')
                    ],
                    'color' => [
                        'selectors' => [
                            '{{SELECTOR}}' => 'background-color: {{VALUE}}; text-decoration-color: {{VALUE}};',
                        ],
                    ],
                    'color_b' => [
                        'selectors' => [
                            '{{SELECTOR}}' => 'text-decoration-color: {{VALUE}};',
                        ],
                    ]
                ],
                'condition' => [
                    'animation_heading_gradient' => 'enabled'
                ]
            ]
        );

        $this->add_control('text_blend_mode', [
            'label' => esc_html('Blend Mode'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'normal' => esc_html('Normal'),
                'multiply' => esc_html('Multiply'),
                'screen' => esc_html('Screen'),
                'overlay' => esc_html('Overlay'),
                'darken' => esc_html('Darken'),
                'lighten' => esc_html('Lighten'),
                'color-dodge' => esc_html('Color Dodge'),
                'saturation' => esc_html('Saturation'),
                'color' => esc_html('Color'),
                'difference' => esc_html('Difference'),
                'exclusion' => esc_html('Exclusion'),
                'hue' => esc_html('Hue'),
                'luminosity' => esc_html('Luminosity'),
            ],
            'default' => 'normal',
            'selectors' => [
                '{{WRAPPER}} .rkit-advanced-heading .text' => 'mix-blend-mode:{{VALUE}}'
            ]
        ]);

        $this->add_control(
            'bgtextcolor',
            [
                'label' => esc_html__('Container', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'bg_text_container',
                'types' => ['classic', 'gradient',],
                'selector' => '{{WRAPPER}} .text-container',
                'fields_options' => [
                    'background' => [
                        'label' => esc_html('Background Color'),
                    ]
                ]
            ]
        );

        $this->add_responsive_control(
            'text_container_radius',
            [
                'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .text-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'wrap_headtext_padding',
            [
                'label' => esc_html__('Padding', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-advanced-heading .text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'wrap_headtext_margin',
            [
                'label' => esc_html__('Margin', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-advanced-heading .text-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('headline_text_style', [
            'label' => esc_html('Headline'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_control(
            'textcolorhead',
            [
                'label' => esc_html__('Text', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'head_typography',
                'selector' => '{{WRAPPER}} .headline-text',
            ]
        );

        $this->add_control(
            'animation_headline_gradient',
            [
                'label' => esc_html__('Animation Gradient', 'textdomain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'textdomain'),
                'label_off' => esc_html__('No', 'textdomain'),
                'return_value' => 'enabled',
                'default' => '',
                'prefix_class' => 'animation-headline-gradient-'
            ]
        );

        $this->add_control(
            'headline_animation_speed',
            [
                'type' => \Elementor\Controls_Manager::SLIDER,
                'label' => esc_html__('Animation Speed', 'textdomain'),
                'size_units' => ['s', 'ms'],
                'range' => [
                    's' => [
                        'min' => 1,
                        'max' => 10,
                        'step' => 0.5
                    ],
                    'ms' => [
                        'min' => 1,
                        'max' => 10000,
                        'step' => 100
                    ],
                ],
                'default' => [
                    'size' => 3,
                    'unit' => 's'
                ],
                'selectors' => [
                    '{{WRAPPER}}.animation-headline-gradient-enabled .headline-text' => 'animation-duration:{{SIZE}}{{UNIT}}'
                ],
                'condition' => [
                    'animation_headline_gradient' => 'enabled'
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'headline_background',
                'types' => ['classic', 'gradient',],
                'selector' => '{{WRAPPER}} .headline-text',
                'exclude' => ['image'],
                'fields_options' => [
                    'background' => [
                        'label' => esc_html('Color')
                    ],
                    'color' => [
                        'selectors' => [
                            '{{SELECTOR}}' => 'background-color: {{VALUE}}; text-decoration-color: {{VALUE}};',
                        ],
                    ],
                    'color_b' => [
                        'selectors' => [
                            '{{SELECTOR}}' => 'text-decoration-color: {{VALUE}};',
                        ],
                    ]
                ],
                'condition' => [
                    'animation_headline_gradient!' => 'enabled'
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'headline_gradient',
                'types' => ['gradient',],
                'selector' => '{{WRAPPER}} .headline-text',
                'exclude' => ['image'],
                'fields_options' => [
                    'background' => [
                        'label' => esc_html('Color')
                    ],
                    'color' => [
                        'selectors' => [
                            '{{SELECTOR}}' => 'background-color: {{VALUE}}; text-decoration-color: {{VALUE}};',
                        ],
                    ],
                    'color_b' => [
                        'selectors' => [
                            '{{SELECTOR}}' => 'text-decoration-color: {{VALUE}};',
                        ],
                    ]
                ],
                'condition' => [
                    'animation_headline_gradient' => 'enabled'
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'head_text_stroke',
                'selector' => '{{WRAPPER}} .headline-text',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'head_text_shadow',
                'selector' => '{{WRAPPER}} .headline-text',
            ]
        );

        $this->add_control('headline_blend_mode', [
            'label' => esc_html('Blend Mode'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'normal' => esc_html('Normal'),
                'multiply' => esc_html('Multiply'),
                'screen' => esc_html('Screen'),
                'overlay' => esc_html('Overlay'),
                'darken' => esc_html('Darken'),
                'lighten' => esc_html('Lighten'),
                'color-dodge' => esc_html('Color Dodge'),
                'saturation' => esc_html('Saturation'),
                'color' => esc_html('Color'),
                'difference' => esc_html('Difference'),
                'exclusion' => esc_html('Exclusion'),
                'hue' => esc_html('Hue'),
                'luminosity' => esc_html('Luminosity'),
            ],
            'default' => 'normal',
            'selectors' => [
                '{{WRAPPER}} .rkit-advanced-heading .headline-text' => 'mix-blend-mode:{{VALUE}}'
            ]
        ]);

        $this->add_control(
            'bgtextcolorhead',
            [
                'label' => esc_html__('Container Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'bg_head',
                'types' => ['classic', 'gradient',],
                'selector' => '{{WRAPPER}} .headline-container',
                'fields_options' => [
                    'background' => [
                        'label' => esc_html('Background Color'),
                    ]
                ]
            ]
        );

        $this->add_responsive_control(
            'head_radius',
            [
                'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .headline-container' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'headtext_padding',
            [
                'label' => esc_html__('Padding', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .headline-text' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'headtext_margin',
            [
                'label' => esc_html__('Margin', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .headline-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('background_text_style', [
            'label' => esc_html('Background Text'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [
                'show_background_text' => 'yes'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'bg_text_typography',
                'selector' => '{{WRAPPER}} .rkit-advanced-heading-wrapper::before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'bg_text_color',
                'types' => ['classic', 'gradient',],
                'selector' => '{{WRAPPER}} .rkit-advanced-heading-wrapper::before',
                'exclude' => ['image'],
                'fields_options' => [
                    'background' => [
                        'label' => esc_html('Color')
                    ],
                    'color' => [
                        'selectors' => [
                            '{{SELECTOR}}' => 'background-color: {{VALUE}}; text-decoration-color: {{VALUE}};',
                        ],
                    ],
                    'color_b' => [
                        'selectors' => [
                            '{{SELECTOR}}' => 'text-decoration-color: {{VALUE}};',
                        ],
                    ]
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'bg_text_shadow',
                'selector' => '{{WRAPPER}} .rkit-advanced-heading-wrapper::before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'bg_text_stroke',
                'selector' => '{{WRAPPER}} .rkit-advanced-heading-wrapper::before',
            ]
        );

        $this->add_control(
            'hr_1',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ]
        );

        $this->add_responsive_control(
            'x_offset',
            [
                'label' => esc_html__('Horizontal Offset', 'textdomain'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => -500,
                        'max' => 500,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => -100,
                        'max' => 100,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => -50,
                        'max' => 50,
                        'step' => 1,
                    ],
                    'rem' => [
                        'min' => -50,
                        'max' => 50,
                        'step' => 1,
                    ],

                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-advanced-heading-wrapper::before' => 'left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'y_offset',
            [
                'label' => esc_html__('Vertical Offset', 'textdomain'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => -500,
                        'max' => 500,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => -100,
                        'max' => 100,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => -50,
                        'max' => 50,
                        'step' => 1,
                    ],
                    'rem' => [
                        'min' => -50,
                        'max' => 50,
                        'step' => 1,
                    ],

                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-advanced-heading-wrapper::before' => 'top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'bg_text_rotate',
            [
                'label' => esc_html__('Rotate (degrees)', 'textdomain'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => -180,
                        'max' => 180,
                        'step' => 1,
                    ]
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-advanced-heading-wrapper::before' => 'transform: rotate({{SIZE}}deg);',
                ],
            ]
        );

        $this->add_control(
            'hr_2',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ]
        );

        $this->add_control('bg_text_blend_mode', [
            'label' => esc_html('Blend Mode'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'normal' => esc_html('Normal'),
                'multiply' => esc_html('Multiply'),
                'screen' => esc_html('Screen'),
                'overlay' => esc_html('Overlay'),
                'darken' => esc_html('Darken'),
                'lighten' => esc_html('Lighten'),
                'color-dodge' => esc_html('Color Dodge'),
                'saturation' => esc_html('Saturation'),
                'color' => esc_html('Color'),
                'difference' => esc_html('Difference'),
                'exclusion' => esc_html('Exclusion'),
                'hue' => esc_html('Hue'),
                'luminosity' => esc_html('Luminosity'),
            ],
            'default' => 'normal',
            'selectors' => [
                '{{WRAPPER}} .rkit-advanced-heading-wrapper::before' => 'mix-blend-mode:{{VALUE}}'
            ]
        ]);

        $this->add_control(
            'bg_text_index',
            [
                'label' => esc_html__('Z-Index', 'textdomain'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => -10,
                'max' => 1000,
                'step' => 1,
                'selectors' => [
                    '{{WRAPPER}} .rkit-advanced-heading-wrapper::before' => 'z-index: {{VALUE}}'
                ]
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        switch ($settings['html_tag']) {
            case 'h1':
                $html_tag = 'h1';
                break;
            case 'h2':
                $html_tag = 'h2';
                break;
            case 'h3':
                $html_tag = 'h3';
                break;
            case 'h4':
                $html_tag = 'h4';
                break;
            case 'h5':
                $html_tag = 'h5';
                break;
            case 'h6':
                $html_tag = 'h6';
                break;
            default:
                $html_tag = 'h1';
                break;
        }

        $link_start = '';
        $link_end = '';

        if (!empty($settings['_link']['url'])) {
            $link_attributes = '';
            if ($settings['_link']['is_external']) {
                $link_attributes .= ' target="_blank"';
            }
            if ($settings['_link']['nofollow']) {
                $link_attributes .= ' rel="nofollow"';
            }
            $link_start = '<a href="' . esc_url($settings['_link']['url']) . '"' . $link_attributes . '>';
            $link_end = '</a>';
        }

        $text = esc_html($settings['text']);
        $animated_text = preg_replace_callback(
            '/\{\{(.*?)\}\}|([^{]+)/',
            function ($matches) {
                if (!empty($matches[1])) {
                    // bagian {{...}}
                    $items = array_map('trim', explode(',', $matches[1]));
                    $animated_parts = '';
                    foreach ($items as $item) {
                        $animated_parts .= '<span class="headline-text">' . esc_html($item) . '</span>';
                    }
                    return '<span class="headline-container">' . $animated_parts . '</span>';
                } elseif (!empty($matches[2])) {
                    // bagian di luar {{...}}
                    return '<span class="text-container"><span class="text">' . esc_html($matches[2]) . '</span></span>';
                }
                return '';
            },
            $text
        );

        $bgText = ($settings['show_background_text'] === 'yes') ? 'data-background-text="' . $settings['background_text_heading'] . '"' : '';

        echo sprintf('<div class="rkit-advanced-heading-wrapper" %s>', $bgText);
        echo '<' . esc_html($html_tag) . ' class="rkit-advanced-heading">' . $link_start . wp_kses_post($animated_text) . $link_end . '</' . esc_html($html_tag) . '>';
        echo '</div>';
    }
}
