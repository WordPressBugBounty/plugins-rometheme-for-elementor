<?php

namespace RTMKit\Elements;

class ClientGrid extends \Elementor\Widget_Base
{
    private function get_widget_data()
    {
        return \RTMkit\Modules\Widgets\WidgetStorage::instance()->get_widget_data_by_key('client_grid');
    }

    public function get_name()
    {
        return 'rkit-clientgrid';
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

    public function get_categories()
    {
        return ['romethemekit_widgets'];
    }

    public function get_keywords()
    {
        return ['grid', 'client',  'rometheme'];
    }

    function get_custom_help_url()
    {
        return 'https://support.rometheme.net/docs/romethemekit/widgets/';
    }

    public function get_style_depends()
    {
        return ['rtmkit-element-client_grid'];
    }

    protected function register_controls()
    {
        $this->start_controls_section('content_section', [
            'label' => esc_html('Content'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT
        ]);

        $this->add_responsive_control(
            'client_column',
            [
                'label' => __('Grid Number', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'devices' => ['desktop', 'tablet', 'mobile'],
                // 'default' => [

                'default' => [
                    'size' => 4, // Nilai default untuk desktop
                ],
                'tablet_default' => [
                    'size' => 2, // Nilai default untuk tablet
                ],
                'mobile_default' => [
                    'size' => 1, // Nilai default untuk mobile
                ],
                // ],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 12,
                        'step' => 1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .client-grid-wrapper' => 'grid-template-columns: repeat({{SIZE}} , 1fr)'
                ]
            ]
        );

        $this->add_control(
            'hover_style',
            [
                'label' => esc_html__(' Hover Style', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'default' => esc_html__('None', 'rometheme-for-elementor'),
                    'horizontal' => esc_html__('Horizontal', 'rometheme-for-elementor'),
                    'vertical' => esc_html__('Vertical', 'rometheme-for-elementor'),
                    'flip_effect'  => esc_html__('Flip', 'rometheme-for-elementor'),
                ],
                'default' => 'vertical',
                'description' => esc_html__('Hover function ready to use except at default mode', 'rometheme-for-elementor'),
            ]
        );


        $this->add_group_control(
            \Elementor\Group_Control_Image_Size::get_type(),
            [
                'name' => 'thumbnail',
                'exclude' => ['custom'],
                'include' => [],
                'default' => 'large',
                'condition' => [
                    'show_image_icon' => 'yes',
                ],
            ]
        );

        $card_list = new \Elementor\Repeater();

        $card_list->add_control(
            'show_title',
            [
                'label' => esc_html__('Show title', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Hide', 'rometheme-for-elementor'),
                'label_off' => esc_html__('Show', 'rometheme-for-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );
        $card_list->add_control(
            'card_title',
            [
                'label' => esc_html__('Title', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__('Type your title here', 'rometheme-for-elementor'),
                'condition' => [
                    'show_title' => 'yes'
                ]
            ]
        );



        $card_list->add_control(
            'image',
            [
                'label' => esc_html__('Choose Client Image', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $card_list->add_control(
            'choose_hover_style',
            [
                'label' => esc_html__('Hover Mode', 'textdomain'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'text' => [
                        'title' => esc_html__('Text', 'textdomain'),
                        'icon' => 'eicon-t-letter-bold',
                    ],
                    'image' => [
                        'title' => esc_html__('Image', 'textdomain'),
                        'icon' => 'eicon-e-image',
                    ],
                ],
                'default' => 'image',
                'toggle' => true,
            ]
        );

        //hover 
        $card_list->add_control(
            'hover_title',
            [
                'label' => esc_html__('Hover Title', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Awesome Partner', 'rometheme-for-elementor'),
                'condition' => [
                    'choose_hover_style' => 'text',
                    //  'select_style_hover' => 'yes'

                ]

            ]
        );
        $card_list->add_control(
            'hover_description',
            [
                'label' => esc_html__('Hover Description', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__('The Best Partners in the World.', 'rometheme-for-elementor'),
                'condition' => [
                    'choose_hover_style' => 'text',
                    // 'select_style_hover' => 'yes'
                ],
                'description' => esc_html__('Short Description Only', 'rometheme-for-elementor'),
            ]
        );


        $card_list->add_control(
            'hover',
            [
                'label' => esc_html__('Choose Image Hover', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'choose_hover_style' => 'image',
                ],

            ]
        );
        // end hover 

        $card_list->add_control(
            'card_link',
            [
                'label' => esc_html__('Link', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::URL,
                'options' => ['url', 'is_external', 'nofollow'],
                'label_block' => true,
            ]
        );

        $card_list->add_control(
            'show_icons',
            [
                'label' => esc_html__('Show Icon', 'textdomain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'textdomain'),
                'label_off' => esc_html__('No', 'textdomain'),
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        $card_list->add_control(
            'client_icon',
            [
                'label' => esc_html__('Client Icon', 'textdomain'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-star',
                    'library' => 'fa-solid',
                ],
                'condition' => [
                    'show_icons' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'list',
            [
                'label' => esc_html__('Repeater List', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $card_list->get_controls(),
                'default' => [
                    [
                        'card_title' => esc_html__('Example Title 1', 'rometheme-for-elementor'),

                    ],
                    [
                        'card_title' => esc_html__('Example Title 2', 'rometheme-for-elementor'),
                        'show_icons' => 'yes',

                    ],
                    [
                        'card_title' => esc_html__('Example Title 3', 'rometheme-for-elementor'),

                    ],
                    [
                        'card_title' => esc_html__('Example Title 4', 'rometheme-for-elementor'),

                    ],
                ],
                'title_field' => '{{{card_title}}}',
            ]
        );

        $this->end_controls_section();



        //container style
        $this->start_controls_section('Container_style_section', [
            'label' => esc_html__('Container', 'rometheme-for-elementor'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'background_container_all',
                'types' => ['classic', 'gradient'],
                'exclude' => ['image'],
                'selector' => '{{WRAPPER}} .client-grid-wrapper',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'container_box_shadow',
                'label' => __('Box Shadow', 'rometheme-for-elementor'),
                'selector' => '{{WRAPPER}} .client-grid-wrapper',
            ]
        );

        $this->add_responsive_control(
            'Header_padding',
            [
                'label' => esc_html__('Padding', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],

                'selectors' => [
                    '{{WRAPPER}} .client-grid-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'container_spacing_horizontal',
            [
                'label' => esc_html__('Horizontal Spacing', 'textdomain'),
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
                    '{{WRAPPER}} .client-grid-wrapper' => 'row-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'container_spacing_vertical',
            [
                'label' => esc_html__('Vertical Spacing', 'textdomain'),
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
                    '{{WRAPPER}} .client-grid-wrapper' => 'column-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();


        //image style
        $this->start_controls_section('image_style', [
            'label' => esc_html('Image'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_responsive_control(
            'image_height',
            [
                'label' => esc_html__('Height', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 500,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 120,
                    'unit' => 'px'
                ],
                'selectors' => [
                    '{{WRAPPER}} .image-container-cg ' => 'height:{{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .hover-content-cg ' => 'height:{{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_space',
            [
                'label' => esc_html__('Spacing', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .client-grid-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'image_background',
                'label' => esc_html__('Background', 'rometheme-for-elementor'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .clientslogo-image-full-cg',
                'exclude' => ['image'],
                'description' => esc_html__('Use For Transparent background & PNG format', 'rometheme-for-elementor'),
            ]
        );

        $this->add_responsive_control('object_fit', [
            'label' => esc_html__('Object Fit Image', 'rometheme-for-elementor'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'none' => esc_html__('None', 'rometheme-for-elementor'),
                'cover' => esc_html__('Cover', 'rometheme-for-elementor'),
                'contain' => esc_html__('Contain', 'rometheme-for-elementor'),
                'fill' => esc_html__('Fill', 'rometheme-for-elementor'),
                'scale-down' => esc_html__('Scale Down', 'rometheme-for-elementor'),

            ],
            'selectors' => [
                '{{WRAPPER}} .clientslogo-image-full-cg, {{WRAPPER}} .clientslogo-image-full-cg img, {{WRAPPER}} .image-container-cg'  => 'object-fit:{{VALUE}}'
            ],
            'default' => 'scale-down',
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'image_border',
                'label' => esc_html__('Border  ', 'rometheme-for-elementor'),
                'selector' => '{{WRAPPER}} .image-container-cg '
            ]
        );

        $this->add_responsive_control(
            'border_radius',
            [
                'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}}  .image-container-cg' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'padding',
            [
                'label' => esc_html__('Padding', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .image-container-cg ' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .image-container-cg .clientslogo-image-full-cg  ' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // title style
        $this->start_controls_section(
            'style_section',
            [
                'label' => esc_html__('Section Title', 'rometheme-for-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'description' => esc_html__('Use For Title section', 'rometheme-for-elementor'),
            ]
        );

        $this->add_control(
            'style_section_alert',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<strong>Note:</strong> Enable "Show Title" in the repeater to see the effect of this setting.',
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );

        $this->add_responsive_control(
            'title_align',
            [
                'label' => esc_html__('Alignment', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'rometheme-for-elementor'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'rometheme-for-elementor'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'rometheme-for-elementor'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .card-title' => 'text-align: {{VALUE}};',
                ],
            ]
        );


        $this->add_responsive_control(
            'spacebetween_title',
            [
                'label' => esc_html__('Spacing', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0
                ],
                'selectors' => [
                    '{{WRAPPER}} .card-heading' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => esc_html__('Typography', 'rometheme-for-elementor'),
                'selector' => '{{WRAPPER}} .card-title',
            ]
        );


        $this->add_control(
            'title_color_external',
            [
                'label' => esc_html__('Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .card-title' => 'color: {{VALUE}};'
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'title_stroke_normal',
                'selector' => '{{WRAPPER}} .card-title ',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'title_shadow_normal',
                'selector' => '{{WRAPPER}} .card-title',
            ]
        );

        $this->end_controls_section();

        // Style Section for Hover
        $this->start_controls_section('hover_style_section', [
            'label' => esc_html__('Hover Text Style', 'rometheme-for-elementor'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control(
            'hover_style_alert',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<strong>Note:</strong> Enable "Hover Text" in the repeater to see the effect of this setting.',
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );

        $this->add_responsive_control(
            'text_hover_align',
            [
                'label' => esc_html__('Vertical Alignment', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => esc_html__('Top', 'rometheme-for-elementor'),
                        'icon' => 'eicon-v-align-top',
                    ],

                    'center' => [
                        'title' => esc_html__('Center', 'rometheme-for-elementor'),
                        'icon' => 'eicon-v-align-middle',
                    ],
                    'flex-end' => [
                        'title' => esc_html__('Bottom', 'rometheme-for-elementor'),
                        'icon' => 'eicon-v-align-bottom',
                    ]
                ],
                'selectors' => [
                    '{{WRAPPER}} .hover-content-cg' => 'justify-content: {{VALUE}};',
                ],
                'default' => 'center',
            ]
        );

        $this->add_responsive_control(
            'spacebetween_hover_text',
            [
                'label' => esc_html__('Spacing', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 0
                ],
                'selectors' => [
                    '{{WRAPPER}} .hover-content-cg' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'background',
                'types' => ['classic', 'gradient'],
                'exclude' => ['image'],
                'selector' => '{{WRAPPER}} .hover-content-cg',
            ]
        );

        $this->add_responsive_control(
            'padding_container_text',
            [
                'label' => esc_html__('Padding', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'default' => [
                    'top' => 2,
                    'right' => 2,
                    'bottom' => 2,
                    'left' => 2,
                    'unit' => 'px',
                    'isLinked' => false,
                ],
                'selectors' => [
                    '{{WRAPPER}} .hover-content-cg ' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'hover_style_divider',
            [
                'label' => esc_html__('Title', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
            ]
        );

        $this->add_responsive_control(
            'title_hover_align',
            [
                'label' => esc_html__('Alignment', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__('Left', 'rometheme-for-elementor'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'rometheme-for-elementor'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'end' => [
                        'title' => esc_html__('Right', 'rometheme-for-elementor'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .hover-title-cg' => 'align-self: {{VALUE}}; text-align: {{VALUE}};',
                ],
                'default' => 'center',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_hover_typography',
                'label' => esc_html__('Typography', 'rometheme-for-elementor'),
                'selector' => '{{WRAPPER}} .hover-title-cg',

            ]
        );

        // title
        $this->add_control(
            'title_hover_color',
            [
                'label' => esc_html__('Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .hover-title-cg' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'hover_style_title_divider',
            [
                'label' => esc_html__('Description', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
            ]
        );

        // Description
        $this->add_responsive_control(
            'desc_hover_align',
            [
                'label' => esc_html__('Alignment', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__('Left', 'rometheme-for-elementor'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'rometheme-for-elementor'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'end' => [
                        'title' => esc_html__('Right', 'rometheme-for-elementor'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .hover-description-cg' => 'align-self: {{VALUE}}; text-align: {{VALUE}};',
                ],
                'default' => 'center',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'description_hover_typography',
                'label' => esc_html__('Typography', 'rometheme-for-elementor'),
                'selector' => '{{WRAPPER}} .hover-description-cg',

            ]
        );

        $this->add_control(
            'desc_hover_color',
            [
                'label' => esc_html__('Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .hover-description-cg' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('icon_style', [
            'label' => 'Icons',
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_control(
            'icons_style_alert',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<strong>Note:</strong> Set "Client Icon" in the repeater to see the effect of this setting.',
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );

        $this->add_control(
            'position_options',
            [
                'label' => esc_html__('Position', 'textdomain'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'icon_vertical_position',
            [
                'label' => esc_html__('Vertical Position', 'textdomain'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'top' => [
                        'title' => esc_html__('Top', 'textdomain'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'bottom' => [
                        'title' => esc_html__('Bottom', 'textdomain'),
                        'icon' => 'eicon-v-align-bottom',
                    ]
                ],
                'default' => 'top',
                'toggle' => true,
                'prefix_class' => 'client-icon-'
            ]
        );

        $this->add_control(
            'icon_horizontal_position',
            [
                'label' => esc_html__('Horizontal Position', 'textdomain'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'textdomain'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'textdomain'),
                        'icon' => 'eicon-h-align-right',
                    ]
                ],
                'default' => 'right',
                'toggle' => true,
                'prefix_class' => 'client-icon-'
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
                    '{{WRAPPER}}.client-icon-left .client-icons-wrapper' => 'left: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}}.client-icon-right .client-icons-wrapper' => 'right: {{SIZE}}{{UNIT}};',
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
                    '{{WRAPPER}}.client-icon-top .client-icons-wrapper' => 'top: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}}.client-icon-bottom .client-icons-wrapper' => 'bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'icon_options',
            [
                'label' => esc_html__('Icons', 'textdomain'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
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
                    '{{WRAPPER}} i.client-icon' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} svg.client-icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('icons_tab');

        $this->start_controls_tab('icons_tab_normal', ['label' => esc_html('Normal')]);
        $this->add_control(
            'icons_color',
            [
                'label' => esc_html__('Icon Color', 'textdomain'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} i.client-icon' => 'color: {{VALUE}}',
                    '{{WRAPPER}} svg.client-icon' => 'fill: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'icons_stroke',
                'selector' => '{{WRAPPER}} .client-icon',
            ]
        );
        $this->end_controls_tab();

        $this->start_controls_tab('icons_tab_hover', ['label' => esc_html('Hover')]);
        $this->add_control(
            'icons_color_hover',
            [
                'label' => esc_html__('Icon Color', 'textdomain'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-card-client:hover i.client-icon' => 'color: {{VALUE}}',
                    '{{WRAPPER}} .rkit-card-client:hover svg.client-icon' => 'fill: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'icons_stroke_hover',
                'selector' => '{{WRAPPER}} .rkit-card-client:hover .client-icon',
            ]
        );
        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'icon_wrapper_options',
            [
                'label' => esc_html__('Icon Wrapper', 'textdomain'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'icons_padding',
            [
                'label' => esc_html__('Padding', 'textdomain'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .client-icons-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icons_radius',
            [
                'label' => esc_html__('Border Radius', 'textdomain'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .client-icons-wrapper' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('icon_wrapper_tab');

        $this->start_controls_tab('icon_wrapper_tab_normal', ['label' => esc_html('Normal')]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'icons_background',
                'types' => ['classic', 'gradient'],
                'exclude' => ['image'],
                'selector' => '{{WRAPPER}} .client-icons-wrapper',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'icons_border',
                'selector' => '{{WRAPPER}} .client-icons-wrapper',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'icon_box_shadow',
                'selector' => '{{WRAPPER}} .client-icons-wrapper',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('icon_wrapper_tab_hover', ['label' => esc_html('Hover')]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'icons_background_hover',
                'types' => ['classic', 'gradient'],
                'exclude' => ['image'],
                'selector' => '{{WRAPPER}} .rkit-card-client:hover .client-icons-wrapper',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'icons_border_hover',
                'selector' => '{{WRAPPER}} .rkit-card-client:hover .client-icons-wrapper',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'icon_box_shadow_hover',
                'selector' => '{{WRAPPER}} .rkit-card-client:hover .client-icons-wrapper',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();


        $this->end_controls_section();
    }
    protected function render()
    {
        $settings = $this->get_settings_for_display();
?>

        <!-- main container -->
        <div class="client-grid-container">
            <div class="client-grid-wrapper">
                <?php
                foreach ($settings['list'] as $li) :
                    // $hoverText = !empty($li['hover_text']) && $li['hover_text'] === 'yes';
                    // $hoverImage = !empty($li['hover_image_choose']) && $li['hover_image_choose'] === 'yes';

                    $hoverTitle = !empty($li['hover_title']) ? $li['hover_title'] : '';
                    $hoverDescription = !empty($li['hover_description']) ? $li['hover_description'] : '';
                    $image_default = !empty($li['image']['url']) ? $li['image']['url'] : '';
                    $image_hover = !empty($li['hover']['url']) ? $li['hover']['url'] : '';
                    $hoverTextClass = ($li['choose_hover_style'] === 'text') ? 'hover_text' : '';

                    if (!empty($li['card_link']['url'])) {
                        $this->add_link_attributes('card_link_' . $li['_id'], $li['card_link']);
                    }
                ?>
                    <div class="container-image-cg">
                        <div class="rkit-card-client">
                            <div class="image-container-cg <?php echo esc_attr($settings['hover_style']); ?>">
                                <?php if ($li['show_icons'] === 'yes') : ?>
                                    <div class="client-icons-wrapper">
                                        <?php \Elementor\Icons_Manager::render_icon($li['client_icon'], ['aria-hidden' => 'true', 'class' => 'client-icon']); ?>
                                    </div>
                                <?php endif; ?>
                                <div class="clientslogo-image-full-cg  image-default">
                                    <?php if ($image_default) : ?>
                                        <img src="<?php echo esc_url($image_default); ?>" alt="" class="image-cover">
                                    <?php endif; ?>
                                </div>
                                <?php if ($li['choose_hover_style'] === 'image') : ?>
                                    <div class="clientslogo-image-full-cg image-hover-cg <?php echo esc_attr($hoverTextClass); ?>">
                                        <img src="<?php echo esc_url($image_hover); ?>" class="image-cover-hover" />
                                    </div>

                                <?php else : ?>
                                    <div class="hover-content-cg">
                                        <h4 class="hover-title-cg"><?php echo esc_html($hoverTitle); ?></h4>
                                        <p class="hover-description-cg"><?php echo esc_html($hoverDescription); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if (!empty($li['show_title']) && $li['show_title'] === 'yes') : ?>
                            <div class="card-body">
                                <div class="card-heading">
                                    <a <?php $this->print_render_attribute_string('card_link_' . $li['_id']); ?>>
                                        <p class="card-title"><?php echo !empty($li['card_title']) ? esc_html($li['card_title']) : ''; ?></p>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
<?php
    }
}
