<?php
class Rkit_woo_product_carousel extends \Elementor\Widget_Base
{

    public function get_name()
    {
        return 'rkit-woo_product_carousel';
    }

    public function get_title()
    {
        return \RomethemeKit\RkitWidgets::listWidgets()['woo_product_carousel']['name'];
    }

    public function get_icon()
    {
        $icon = 'rkit-widget-icon ' . \RomethemeKit\RkitWidgets::listWidgets()['woo_product_carousel']['icon'];
        return $icon;
    }


    public function get_categories()
    {
        return ['romethemekit_widgets'];
    }

    public function get_style_depends()
    {
        return ['rkit-woo-product-carousel-style', 'rkit-swiper'];
    }

    public function get_script_depends()
    {
        return ['woo-product-script'];
    }
    public function get_keywords()
    {
        return ['product', 'carousel', 'rometheme'];
    }

    public function get_custom_help_url()
    {
        return \RomethemeKit\RkitWidgets::listWidgets()['woo_product_carousel']['docsURL'];
    }

    public function rkit_get_product_categories_plain($product, $separator = ', ')
    {
        if (isset($product) && $product instanceof WC_Product) {
            return wc_get_product_category_list(
                $product->get_id(),
                $separator
            );
        }
        return '';
    }

    public function custom_rating_html($html, $rating, $count)
    {
        // Ubah lebar bintang berdasarkan rating
        $width = ($rating / 5) * 100;
        $html = '<div class="star-rating"><span style="width:' . esc_attr($width) . '%;"></span></div>';
        return $html;
    }
    private function get_product_categories()
    {
        $categories = get_terms('product_cat', ['hide_empty' => false]);
        $options = [];
        if (!empty($categories) && !is_wp_error($categories)) {
            foreach ($categories as $category) {
                $options[$category->slug] = $category->name;
            }
        }
        return $options;
    }


    protected function register_controls()
    {

        $this->start_controls_section('pwg_layout', [
            'label' => esc_html('Layout'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT
        ]);

        $this->add_control('option_style', [
            'label' => esc_html('Style'),
            'type' => \Elementor\Controls_Manager::HIDDEN,
            // 'options' => [
            //     '-pro' => esc_html('Style 1'),
            //     '' => esc_html('Style 2')
            //     // '-prem' => esc_html('Style 3'), 

            // ],
            'default' => ''
        ]);

        $this->add_control('title_wpc_tag', [
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
            'default' => 'h3'
        ]);

        // layout 

        $this->add_responsive_control(
            'titlle_direction',
            [
                'label' => esc_html__('Direction', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'column' => [
                        'title' => esc_html__('Column', 'rometheme-for-elementor'),
                        'icon' => 'eicon-arrow-down',
                    ],
                    'row' => [
                        'title' => esc_html__('Row', 'rometheme-for-elementor'),
                        'icon' => 'eicon-arrow-right',
                    ],
                    'column-reverse' => [
                        'title' => esc_html__('Column Reverse', 'rometheme-for-elementor'),
                        'icon' => 'eicon-arrow-up',
                    ],

                ],
                'default' => 'row',
                'toggle' => true,
                'render_type' => 'template',
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-details-wpc-pro, {{WRAPPER}} .rkit-product-details-wpc' => 'flex-direction: {{VALUE}};',
                ],
            ]
        );



        // layout





        $this->add_control(
            'show_category',
            [
                'label' => esc_html__('Show Category', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'rometheme-for-elementor'),
                'label_off' => esc_html__('Hide', 'rometheme-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'option_style' => [''],
                ]
            ]
        );

        $this->add_control(
            'show_rating',
            [
                'label' => esc_html__('Show Rating', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'rometheme-for-elementor'),
                'label_off' => esc_html__('Hide', 'rometheme-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_sale',
            [
                'label' => esc_html__('Show Ribbon', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'rometheme-for-elementor'),
                'label_off' => esc_html__('Hide', 'rometheme-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();


        $this->start_controls_section('pwg_general', [
            'label' => esc_html('General'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT
        ]);

        $this->add_control(
            'product_count',
            [
                'label' => __('Number of Products', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 3,
            ]
        );

        // $this->add_control(
        //     'product_column',
        //     [
        //         'label' => __('Column', 'rometheme-for-elementor'),
        //         'description' => esc_html__('*Setting for desktop Only', 'text-domain'),
        //         'type' => \Elementor\Controls_Manager::NUMBER,
        //         'min' => 1,
        //         'max' => 8, 
        //         'default' => 3,
        //         'selectors' => [
        //             '{{WRAPPER}} .rkit-product-carousel-wpc' => 'carousel-template-columns: repeat({{VALUE}}, 1fr) !important; ', 
        //         ], 
        //     ]
        // );


        // $this->add_control(
        //     'product_columna',
        //     [
        //         'label' => __('Column', 'rometheme-for-elementor'),
        //         'description' => esc_html__('*Setting for desktop Only', 'text-domain'),
        //         'type' => \Elementor\Controls_Manager::NUMBER,
        //         'min' => 1,
        //         'max' => 8,
        //         'default' => 3,
        //         'selectors' => [
        //             '{{WRAPPER}} .rkit-product-carousel-wpc' => 'carousel-template-columns: repeat({{VALUE}}, 1fr);',
        //         ],
        //     ]
        // );




        // $this->add_responsive_control(
        //     'card_spacing',
        //     [
        //         'label' => esc_html__('Items Spacing', 'rometheme-for-elementor'),
        //         'type' => \Elementor\Controls_Manager::SLIDER,
        //         'size_units' => ['px', '%', 'em', 'rem'],
        //         'devices' => ['desktop', 'tablet', 'mobile'],
        //         'range' => [
        //             'px' => [
        //                 'min' => 0,
        //                 'max' => 500,
        //                 'step' => 2,
        //             ],
        //             '%' => [
        //                 'min' => 10,
        //                 'max' => 100,
        //             ],
        //         ],
        //         'selectors' => [
        //             ' {{WRAPPER}} .rkit-product-carousel-wpc-prem, {{WRAPPER}} .rkit-product-carousel-wpc-pro, {{WRAPPER}} .rkit-product-carousel-wpc' => 'gap: {{SIZE}}{{UNIT}};',
        //         ],
        //     ]
        // );

        $this->end_controls_section();

        $this->start_controls_section(
            'query_section',
            [
                'label' => __('Query', 'rometheme-for-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );



        $this->add_control(
            'product_categories',
            [
                'label' => __('Select Categories', 'text-domain'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_product_categories(),
                'multiple' => true,
                'label_block' => true,
            ]
        );

        $this->add_control('order_by_wpc', [
            'label' => esc_html__('Order By', 'rometheme-for-elementor'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                '' => esc_html__('Default', 'rometheme-for-elementor'),
                'date' => esc_html__('Date', 'rometheme-for-elementor'),
                'title' => esc_html__('Title', 'rometheme-for-elementor'),
            ],
            'default' => '',
        ]);

        $this->add_control('order_wpc', [
            'label' => esc_html__('Order', 'rometheme-for-elementor'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                '' => esc_html__('Default', 'rometheme-for-elementor'),
                'ASC' => esc_html__('ASC', 'rometheme-for-elementor'),
                'DESC' => esc_html__('DESC', 'rometheme-for-elementor'),
            ],
            'default' => '',
        ]);

        $this->add_control('truncate-content', [
            'label' => esc_html__('Crop Description Word', 'rometheme-for-elementor'),
            'description' => esc_html__('Recomendation, use 10 - 15 word Only.', 'text-domain'),
            'type' => \Elementor\Controls_Manager::NUMBER,
            'default' => 10,
        ]);

        $this->end_controls_section();


        $this->start_controls_section('content_button_wgp', [
            'label' => esc_html__('Button'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT
        ]);

        $this->add_control(
            'show_button_icon_wgp',
            [
                'label' => esc_html__('Show Button Icon', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'rometheme-for-elementor'),
                'label_off' => esc_html__('Hide', 'rometheme-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
                // 'condition' => [
                //     'option_style' => ['-pro'],
                // ]
            ]
        );

        $this->add_control(
            'show_button_icon_wgp_prem',
            [
                'label' => esc_html__('Show Button Icon', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'rometheme-for-elementor'),
                'label_off' => esc_html__('Hide', 'rometheme-for-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
                'condition' => [
                    'option_style' => ['-prem'],
                ]
            ]
        );

        $this->add_control(
            'button_icon_wgp',
            [
                'label' => esc_html__('Button Icon', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'label_block' => true,
                'default' => [
                    'value' => 'rtmicon rtmicon-shopping-cart',
                    'library' => 'rtmicons',
                ],
                'condition' => [
                    'show_button_icon_wgp' => 'yes',
                    // 'option_style' => ['-pro'],
                ]
            ]
        );

        $this->add_control(
            'button_icon_wgp_prem',
            [
                'label' => esc_html__('Button Icon', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'label_block' => true,
                'default' => [
                    'value' => 'rtmicon rtmicon-shopping-cart',
                    'library' => 'rtmicons',
                ],
                'condition' => [
                    'show_button_icon_wgp_prem' => 'yes',
                    'option_style' => ['-prem'],
                ]
            ]
        );

        $this->add_control(
            'button_icon_position_wgp',
            [
                'label' => esc_html__('Icon Position', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'row-reverse' => [
                        'title' => esc_html__('Before Text', 'rometheme-for-elementor'),
                        'icon' => 'eicon-order-start',
                    ],
                    'row' =>  [
                        'title' => esc_html__('After Text', 'rometheme-for-elementor'),
                        'icon' => 'eicon-order-end',
                    ],
                ],
                'default' => 'row-reverse',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .rkit-addcart-button-wpc' => 'flex-direction : {{VALUE}}'
                ],
                'condition' => [
                    'show_button_icon_wgp' => 'yes',
                    // 'option_style' => ['-pro'],
                ]
            ]
        );

        $this->add_control(
            'button_icon_position_wgp_prem',
            [
                'label' => esc_html__('Position', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'before' => [
                        'title' => esc_html__('Before Text', 'rometheme-for-elementor'),
                        'icon' => 'eicon-order-start',
                    ],
                    'after' =>  [
                        'title' => esc_html__('After Text', 'rometheme-for-elementor'),
                        'icon' => 'eicon-order-end',
                    ],
                ],
                'default' => 'after',
                'toggle' => true,
                'condition' => [
                    'show_button_icon_wgp' => 'yes',
                    'option_style' => ['-prem'],
                ]
            ]
        );

        $this->add_control(
            'button_function',
            [
                'label' => esc_html('Button Link'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'view_detail' => esc_html('View Detail'),
                    'add_to_cart' => esc_html('Add To Cart'),
                ],
                'default' => 'add_to_cart'
            ]
        );

        $this->add_control(
            'button_text_wpc',
            [
                'label' => esc_html__('Button Text', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Add To Cart', 'rometheme-for-elementor'),
            ]
        );

        $this->end_controls_section();

        // Navigation Section
        $this->start_controls_section(
            'navigation_section',
            [
                'label' => esc_html__('Navigation', 'rometheme-for-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_navigation',
            [
                'label' => esc_html__('Show Navigation', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'rometheme-for-elementor'),
                'label_off' => esc_html__('Hide', 'rometheme-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        // Show Animation Switcher
        $this->add_control(
            'show_animation',
            [
                'label' => esc_html__('Show Animation', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('On', 'rometheme-for-elementor'),
                'label_off' => esc_html__('Off', 'rometheme-for-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
                'condition' => [
                    'show_navigation' => 'yes',
                ],
            ]
        );

        // Next Icon Control
        $this->add_control(
            'next_icon',
            [
                'label' => esc_html__('Next Icon', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'eicon-chevron-right',
                    'library' => 'elementor',
                ],
                'condition' => [
                    'show_navigation' => 'yes',
                ],
            ]
        );

        // Previous Icon Control
        $this->add_control(
            'prev_icon',
            [
                'label' => esc_html__('Previous Icon', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'eicon-chevron-left',
                    'library' => 'elementor',
                ],
                'condition' => [
                    'show_navigation' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // add tab content label settings
        $this->start_controls_section(
            'tab_content_settings_section',
            [
                'label' => esc_html__('Settings', 'rometheme-for-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        $this->add_control(
            'autoplay_woo_product_carousel',
            [
                'label' => esc_html__('Autoplay', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'rometheme-for-elementor'),
                'label_off' => esc_html__('No', 'rometheme-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_dots_woo_product_carousel',
            [
                'label' => esc_html__('Show Dots', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'rometheme-for-elementor'),
                'label_off' => esc_html__('No', 'rometheme-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'dots_position_woo_product_carousel',
            [
                'label' => esc_html__('Dots Position', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'top' => esc_html__('Top', 'rometheme-for-elementor'),
                    'bottom' => esc_html__('Bottom', 'rometheme-for-elementor'),
                ],
                'condition' => [
                    'show_dots_woo_product_carousel' => 'yes',
                ],
                'default' => 'bottom',
            ]
        );

        $this->add_control(
            'pause_on_hover_woo_product_carousel',
            [
                'label' => esc_html__('Pause On Hover', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'rometheme-for-elementor'),
                'label_off' => esc_html__('No', 'rometheme-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'loop_woo_product_carousel',
            [
                'label' => esc_html__('Loop', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'rometheme-for-elementor'),
                'label_off' => esc_html__('No', 'rometheme-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_responsive_control(
            'slide_to_show_woo_product_carousel',
            [
                'label' => esc_html__('Slide To Show', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'devices' => ['desktop', 'tablet', 'mobile'],
                'default' => [
                    'size' => 3,
                ],
                'tablet_default' => [
                    'size' => 2,
                ],
                'mobile_default' => [
                    'size' => 1,
                ],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 12,
                        'step' => 1,
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'slide_to_scroll_woo_product_carousel',
            [
                'label' => esc_html__('Slide To Scroll', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'devices' => ['desktop', 'tablet', 'mobile'],
                'default' => [
                    'size' => 1,
                ],
                'tablet_default' => [
                    'size' => 1,
                ],
                'mobile_default' => [
                    'size' => 1,
                ],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 10,
                        'step' => 1,
                    ],
                ],
            ]
        );

        $this->add_responsive_control(
            'speed_woo_product_carousel',
            [
                'label' => esc_html__('Speed (ms)', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'devices' => ['desktop', 'tablet', 'mobile'],
                'description' => esc_html__('Set duration in milliseconds. For example: 1000 = 1 second.', 'rometheme-for-elementor'),
                'default' => [
                    'size' => 1000,
                    'unit' => 'ms',
                ],
                'range' => [
                    'ms' => [
                        'min' => 0,
                        'max' => 10000,
                        'step' => 100,
                    ],
                ],
            ]
        );

        $this->end_controls_section();



        // style --------------------------------------------------------------------------------------------

        $this->start_controls_section('Container_style_section', [
            'label' => esc_html__('Container', 'rometheme-for-elementor'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_responsive_control(
            'spacebetween_woo_pc',
            [
                'label' => esc_html__('Gap', 'rometheme-for-elementor'),
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
                    'unit' => 'px',
                    'size' => 16
                ],
                'frontend_available' => true,
            ]
        );

        $this->add_responsive_control(
            'con_padding',
            [
                'label' => esc_html__('Padding', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                // 'selectors' => [
                //     '{{WRAPPER}}  .rkit-product-card-wpc, {{WRAPPER}}  .rkit-product-card-wpc-pro, 
                // {{WRAPPER}} .rkit-product-card-wpc-prem, {{WRAPPER}} .rkit-product-details-wpc, {{WRAPPER}} .rkit-product-details-prem, {{WRAPPER}} .rkit-product-details-pro' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                // ],
                'selectors' => [
                    '.rkit-woo-pc-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
                ]
            ]
        );

        // Tabs for Normal and Hover
        $this->start_controls_tabs('container_style_tabs_wpc');

        // Normal Tab
        $this->start_controls_tab('container_normal_tab_wpc', [
            'label' => esc_html__('Normal', 'rometheme-for-elementor'),
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'container_background_wpc',
                'label' => esc_html__('Background', 'rometheme-for-elementor'),
                'types' => ['classic', 'gradient'],
                // 'selector' => '{{WRAPPER}} .rkit-product-card-wpc, {{WRAPPER}} .rkit-product-card-wpc-pro, {{WRAPPER}} .rkit-product-card-wpc-prem',
                'selector' => '{{WRAPPER}} .rkit-woo-pc-card',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'container_border_wpc',
                'label' => esc_html__('Border', 'rometheme-for-elementor'),
                // 'selector' => '{{WRAPPER}} .rkit-product-card-wpc, {{WRAPPER}}  .rkit-product-card-wpc-pro, {{WRAPPER}}  .rkit-product-card-wpc-prem',
                'selector' => '{{WRAPPER}} .rkit-woo-pc-card',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'container_box_shadow_wpc',
                'label' => esc_html__('Box Shadow', 'rometheme-for-elementor'),
                // 'selector' => '{{WRAPPER}} .rkit-product-card-wpc, {{WRAPPER}}  .rkit-product-card-wpc-pro, {{WRAPPER}}  .rkit-product-card-wpc-prem',
                'selector' => '{{WRAPPER}} .rkit-woo-pc-card',
                'fields_options' => [
                    'box_shadow_type' => [
                        'default' => 'yes', // aktifkan shadow
                    ],
                    'box_shadow' => [
                        'default' => [
                            'horizontal' => 0,
                            'vertical'   => 0,
                            'blur'       => 10,
                            'spread'     => 0,
                            'color'      => 'rgba(0, 0, 0, 0.5)',
                        ],
                    ],
                ],
            ]
        );


        $this->end_controls_tab(); // End Normal Tab

        // Hover Tab
        $this->start_controls_tab('container_hover_tab_wpc', [
            'label' => esc_html__('Hover', 'rometheme-for-elementor'),
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'container_background_hover_wpc',
                'label' => esc_html__('Background', 'rometheme-for-elementor'),
                'types' => ['classic', 'gradient'],
                // 'selector' => '
                // {{WRAPPER}} .rkit-product-card-wpc:hover, 
                // {{WRAPPER}}  .rkit-product-card-wpc-pro:hover, 
                // {{WRAPPER}}  .rkit-product-card-wpc-prem:hover',
                'selector' => '{{WRAPPER}} .rkit-woo-pc-card:hover'
            ]
        );

        $this->add_control(
            'container_border_color_hover',
            [
                'label' => esc_html__('Border Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                // 'selectors' => [
                //     ' {{WRAPPER}} .rkit-product-card-wpc:hover,
                // {{WRAPPER}}  .rkit-product-card-wpc-pro:hover, 
                // {{WRAPPER}}  .rkit-product-card-wpc-prem:hover' => 'border-color: {{VALUE}}',
                // ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-woo-pc-card:hover' => 'border-color: {{VALUE}};',
                ],
                'condition'=>[
                    'container_border_wpc_border!'=>''
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'container_box_shadow_hover_wpc',
                'label' => esc_html__('Box Shadow', 'rometheme-for-elementor'),
                //     'selector' => '{{WRAPPER}} .rkit-product-card-wpc:hover,
                //  {{WRAPPER}}  .rkit-product-card-wpc-pro:hover,  {{WRAPPER}}  .rkit-product-card-wpc-prem:hover',
                'selector' => '{{WRAPPER}} .rkit-woo-pc-card:hover',
            ]
        );


        $this->end_controls_tab(); // End Hover Tab

        $this->end_controls_tabs(); // End Tabs


        $this->add_control(
            'container_border_radius_divider',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ]
        );

        $this->add_responsive_control(
            'con_radius',
            [
                'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'default' => [
                    'top' => 8,
                    'right' => 8,
                    'bottom' => 8,
                    'left' => 8,
                    'unit' => 'px',
                ],
                // 'selectors' => [
                //     '{{WRAPPER}} .rkit-product-card-wpc, {{WRAPPER}}  .rkit-product-card-wpc-pro,{{WRAPPER}}  .rkit-product-card-wpc-prem' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                // ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-woo-pc-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();


        // image style

        $this->start_controls_section('image_style_section_wpc', [
            'label' => esc_html__('Image', 'rometheme-for-elementor'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_responsive_control('image_aspect_ratio_wpc', [
            'label' => esc_html__('Image Aspect Ratio', 'rometheme-for-elementor'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                '1/1' => esc_html__('1 : 1', 'rometheme-for-elementor'),
                '3/2' => esc_html__('3 : 2', 'rometheme-for-elementor'),
                '2/3' => esc_html__('2 : 3', 'rometheme-for-elementor'),
                '5/4' => esc_html__('5 : 4', 'rometheme-for-elementor'),
                '4/5' => esc_html__('4 : 5', 'rometheme-for-elementor'),
                '16/9' => esc_html__('16 : 9', 'rometheme-for-elementor'),
                '9/16' => esc_html__('9 : 16', 'rometheme-for-elementor'),

            ],
            'default' => '1/1',
            'selectors' => [
                '{{WRAPPER}} .rkit-product-image-wpc, .rkit-product-image-wpc img,
                {{WRAPPER}} .rkit-product-image-wpc-pro, .rkit-product-image-wpc-pro img,
                {{WRAPPER}} .rkit-product-image-wpc-prem, .rkit-product-image-wpc-prem img'

                => 'aspect-ratio:{{VALUE}};'
            ]
        ]);

        $this->add_responsive_control(
            'image_padding',
            [
                'label' => esc_html__('Padding', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-image-wpc ,
                    {{WRAPPER}} .rkit-product-image-wpc-pro, 
                    {{WRAPPER}} .rkit-product-image-wpc-prem' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('image_tab');

        $this->start_controls_tab('image_tab_normal', ['label' => esc_html('Normal')]);
        $this->add_group_control(
            \Elementor\Group_Control_Css_Filter::get_type(),
            [
                'name' => 'product_img_filters',
                'selector' => '{{WRAPPER}} .rkit-product-image-wpc img',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'image_border',
                'label' => esc_html__('Border  ', 'rometheme-for-elementor'),
                'selector' => '
                {{WRAPPER}} .rkit-product-image-container'

            ]
        );
        $this->end_controls_tab();


        $this->start_controls_tab('image_tab_hover', ['label' => esc_html('Hover')]);

        $this->add_group_control(
            \Elementor\Group_Control_Css_Filter::get_type(),
            [
                'name' => 'product_img_filters_hover',
                'selector' => '{{WRAPPER}} .rkit-product-image-wpc:hover img',
            ]
        );

        $this->add_control(
            'image_border_color_hover',
            [
                'label' => esc_html__('Border Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-image-wpc:hover .rkit-product-image-container' => 'border-color: {{VALUE}}',
                ],
                'condition'=>[
                    'image_border_border!'=>''
                ]
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_control(
            'image_border_radius_divider',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ]
        );

        $this->add_responsive_control(
            'image_radius',
            [
                'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-image-container ,
                    {{WRAPPER}} .rkit-product-image-wpc , .rkit-product-image-wpc, 
                    {{WRAPPER}} .rkit-product-image-wpc-pro, .rkit-product-image-wpc-pro, 
                    {{WRAPPER}} .rkit-product-image-wpc-prem, .rkit-product-image-wpc-prem' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // section content title
        // title product style
        $this->start_controls_section('content_style_wpc', [
            'label' => esc_html__('Content', 'rometheme-for-elementor'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);

        $this->add_control(
            'padding_content',
            [
                'label' => esc_html__('Padding', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-details-wpc' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'margin_content',
            [
                'label' => esc_html__('Margin', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-details-wpc' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'divider_list_title_1_wpc',
            [
                'label' => esc_html__('Title', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography_wpc',
                'label' => esc_html__('Typography', 'rometheme-for-elementor'),

                'selector' => '{{WRAPPER}} .rkit-product-title-wpc, {{WRAPPER}} .rkit-product-title-wpc-pro, {{WRAPPER}} .rkit-product-title-wpc-prem',

            ]
        );

        $this->add_responsive_control(
            'title_container_width',
            [
                'label' => esc_html__('Width', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 500,
                        'step' => 2,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 40,
                    'unit' => '%',
                ],
                'condition' => [
                    'option_style' => ['-pro', '-prem'],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-info-wpc-pro,
                    {{WRAPPER}} .rkit-product-title-wpc-prem,'
                    => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'border_bottom_color',
            [
                'label' => __('Title Line Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-title-wpc-prem::after' => 'border-bottom-color: {{VALUE}};',
                ],
                'condition' => [
                    'option_style' => ['-prem'],
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'back_title_background_normal',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-product-card-wpc-prem:hover .rkit-product-details-wpc-prem::before ',
                'condition' => [
                    'option_style' => ['-prem'],
                ],
                'fields_options' => [
                    'background' => [
                        'label' => esc_html('Section Title Background')
                    ]
                ],
            ]
        );

        $this->add_responsive_control(
            'opacity_detail_wpc',
            [
                'label' => esc_html__('Opacity', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['%'],
                'range' => [
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'condition' => [
                    'option_style' => ['-prem'],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-details-wpc-prem::before '
                    => 'opacity: {{SIZE}}%;',
                ],
            ]
        );

        $this->add_control(
            'divider_list_desc_price_wpc',
            [
                'label' => esc_html__('Description', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'desc_typography_wpc',
                'label' => esc_html__('Typography', 'rometheme-for-elementor'),
                'selector' => '{{WRAPPER}} .rkit-product-text-desc-wpc-pro, {{WRAPPER}} .rkit-product-text-desc-wpc-prem',

            ]
        );

        $this->add_control(
            'divider_category_wpc',
            [
                'label' => esc_html__('Category', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'category_typography_wpc',
                'label' => esc_html__('Typography', 'rometheme-for-elementor'),
                'selector' => '{{WRAPPER}} .rkit-product-category-wpc, {{WRAPPER}} .rkit-product-category-wpc-pro, {{WRAPPER}} .rkit-product-category-wpc-prem',
                'condition' => [
                    'option_style!' => ['-pro', '-prem'],
                ],
            ]
        );

        $this->add_control(
            'category_color_wpc',
            [
                'label' => esc_html__('Text Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-category-wpc a, {{WRAPPER}} .rkit-product-category-wpc-pro a, {{WRAPPER}} .rkit-product-category-wpc-prem a' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'option_style!' => ['-pro', '-prem'],
                ],
            ]
        );

        $this->add_control(
            'divider_list_title_price_wpc',
            [
                'label' => esc_html__('Price Reguler', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'price_typography_wpc',
                'label' => esc_html__('Typography', 'rometheme-for-elementor'),

                'selector' => '{{WRAPPER}} .rkit-product-price-reguler-wpc span, {{WRAPPER}} .rkit-product-price-reguler-wpc-pro span, {{WRAPPER}} .rkit-product-price-reguler-wpc-prem span',

            ]
        );

        $this->add_control(
            'price_color_wpc',
            [
                'label' => esc_html__('Text Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-price-reguler-wpc span, {{WRAPPER}} .rkit-product-price-reguler-wpc-pro span, {{WRAPPER}} .rkit-product-price-reguler-wpc-prem span' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'divider_list_title_sale_price_wpc',
            [
                'label' => esc_html__('Sale Price', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        // sale price
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'sale_price_typography_wpc',
                'label' => esc_html__('Typography', 'rometheme-for-elementor'),

                'selector' => '{{WRAPPER}} .rkit-product-sale-price-wpc, {{WRAPPER}} .rkit-product-sale-price-wpc-pro, {{WRAPPER}} .rkit-product-sale-price-wpc-prem',

            ]
        );

        $this->add_control(
            'sale_price_color_wpc',
            [
                'label' => esc_html__('Text Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-sale-price-wpc, {{WRAPPER}} .rkit-product-sale-price-wpc-pro, {{WRAPPER}} .rkit-product-sale-price-wpc-prem' => 'color: {{VALUE}};',
                ],
            ]
        );


        $this->add_control(
            'divider_list_title_sale_pricereguler_wpc',
            [
                'label' => esc_html__('Reguler Sale Price', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        // sale price reguler
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'sale_pricereguler_typography_wpc',
                'label' => esc_html__('Typography', 'rometheme-for-elementor'),

                'selector' => '{{WRAPPER}} .rkit-product-sale-price-reguler-wpc, {{WRAPPER}} .rkit-product-sale-price-reguler-wpc-pro, {{WRAPPER}} .rkit-product-sale-price-reguler-wpc-prem',

            ]
        );

        $this->add_control(
            'sale_pricereguler_color_wpc',
            [
                'label' => esc_html__('Text Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-sale-price-reguler-wpc, {{WRAPPER}} .rkit-product-sale-price-reguler-wpc-pro, {{WRAPPER}} .rkit-product-sale-price-reguler-wpc-prem' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'rating_haading',
            [
                'label' => esc_html__('Rating', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );


        $this->add_responsive_control(
            'rating_size_wpc',
            [
                'label' => esc_html__('Rating Size', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 500,
                        'step' => 2,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .star-rating-wpc ' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'rating_color_wpc',
            [
                'label' => esc_html__('Rating Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .star-rating-wpc span' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'no_rating_color_wpc',
            [
                'label' => esc_html__('No Rating Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .star-rating-wpc' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->start_controls_tabs('title_tab');

        $this->start_controls_tab('title_tab_normal', ['label' => esc_html('Normal')]);

        $this->add_control(
            'title_color_wpc',
            [
                'label' => esc_html__('Title Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-title-wpc, {{WRAPPER}} .rkit-product-title-wpc-pro, {{WRAPPER}} .rkit-product-title-wpc-prem' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'desc_color_wpc',
            [
                'label' => esc_html__('Desc Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-text-desc-wpc-pro, {{WRAPPER}} .rkit-product-text-desc-wpc-prem ' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->end_controls_tab();

        $this->start_controls_tab('title_tab_hover', ['label' => esc_html('Hover')]);

        // $this->add_group_control(
        //     \Elementor\Group_Control_Typography::get_type(),
        //     [
        //         'name' => 'desc_typography_wpc_hover',
        //         'label' => esc_html__('Desc Typography', 'rometheme-for-elementor'),

        //         'selector' => '{{WRAPPER}} .rkit-product-text-desc-wpc-pro:hover, {{WRAPPER}} .rkit-product-text-desc-wpc-prem:hover',

        //     ]
        // );

        $this->add_control(
            'title_color_wpc_hover',
            [
                'label' => esc_html__('Title Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-title-wpc:hover, {{WRAPPER}} .rkit-product-title-wpc-pro:hover, {{WRAPPER}} .rkit-product-title-wpc-prem:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'desc_color_wpc_hover',
            [
                'label' => esc_html__('Desc Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-text-desc-wpc-pro:hover, {{WRAPPER}} .rkit-product-text-desc-wpc-prem:hover ' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();


        $this->end_controls_section();

        //section category style
        // $this->start_controls_section('category_text_style_wpc', [
        //     'label' => esc_html__('Category', 'rometheme-for-elementor'),
        //     'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        //     'condition' => [
        //         'option_style!' => ['-pro', '-prem'],
        //     ],
        // ]);

        // $this->add_group_control(
        //     \Elementor\Group_Control_Typography::get_type(),
        //     [
        //         'name' => 'category_typography_wpc',
        //         'label' => esc_html__('Typography', 'rometheme-for-elementor'),
        //         'selector' => '{{WRAPPER}} .rkit-product-category-wpc, {{WRAPPER}} .rkit-product-category-wpc-pro, {{WRAPPER}} .rkit-product-category-wpc-prem',
        //         'condition' => [
        //             'option_style!' => ['-pro', '-prem'],
        //         ],
        //     ]
        // );

        // $this->add_control(
        //     'category_color_wpc',
        //     [
        //         'label' => esc_html__('Text Color', 'rometheme-for-elementor'),
        //         'type' => \Elementor\Controls_Manager::COLOR,
        //         'selectors' => [
        //             '{{WRAPPER}} .rkit-product-category-wpc a, {{WRAPPER}} .rkit-product-category-wpc-pro a, {{WRAPPER}} .rkit-product-category-wpc-prem a' => 'color: {{VALUE}};',
        //         ],
        //         'condition' => [
        //             'option_style!' => ['-pro', '-prem'],
        //         ],
        //     ]
        // );

        // $this->end_controls_section();



        // price regular
        // $this->start_controls_section('price_text_style_wpc', [
        //     'label' => esc_html__('Price', 'rometheme-for-elementor'),
        //     'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        // ]);
        // $this->add_control(
        //     'divider_list_title_price_wpc',
        //     [
        //         'label' => esc_html__('Price Reguler', 'rometheme-for-elementor'),
        //         'type' => \Elementor\Controls_Manager::HEADING,
        //         'separator' => 'before',
        //     ]
        // );
        // $this->add_group_control(
        //     \Elementor\Group_Control_Typography::get_type(),
        //     [
        //         'name' => 'price_typography_wpc',
        //         'label' => esc_html__('Typography', 'rometheme-for-elementor'),

        //         'selector' => '{{WRAPPER}} .rkit-product-price-reguler-wpc span, {{WRAPPER}} .rkit-product-price-reguler-wpc-pro span, {{WRAPPER}} .rkit-product-price-reguler-wpc-prem span',

        //     ]
        // );

        // $this->add_control(
        //     'price_color_wpc',
        //     [
        //         'label' => esc_html__('Text Color', 'rometheme-for-elementor'),
        //         'type' => \Elementor\Controls_Manager::COLOR,
        //         'selectors' => [
        //             '{{WRAPPER}} .rkit-product-price-reguler-wpc span, {{WRAPPER}} .rkit-product-price-reguler-wpc-pro span, {{WRAPPER}} .rkit-product-price-reguler-wpc-prem span' => 'color: {{VALUE}};',
        //         ],
        //     ]
        // );

        // $this->add_control(
        //     'divider_list_title_sale_price_wpc',
        //     [
        //         'label' => esc_html__('Sale Price', 'rometheme-for-elementor'),
        //         'type' => \Elementor\Controls_Manager::HEADING,
        //         'separator' => 'before',
        //     ]
        // );

        // // sale price
        // $this->add_group_control(
        //     \Elementor\Group_Control_Typography::get_type(),
        //     [
        //         'name' => 'sale_price_typography_wpc',
        //         'label' => esc_html__('Typography', 'rometheme-for-elementor'),

        //         'selector' => '{{WRAPPER}} .rkit-product-sale-price-wpc, {{WRAPPER}} .rkit-product-sale-price-wpc-pro, {{WRAPPER}} .rkit-product-sale-price-wpc-prem',

        //     ]
        // );

        // $this->add_control(
        //     'sale_price_color_wpc',
        //     [
        //         'label' => esc_html__('Text Color', 'rometheme-for-elementor'),
        //         'type' => \Elementor\Controls_Manager::COLOR,
        //         'selectors' => [
        //             '{{WRAPPER}} .rkit-product-sale-price-wpc, {{WRAPPER}} .rkit-product-sale-price-wpc-pro, {{WRAPPER}} .rkit-product-sale-price-wpc-prem' => 'color: {{VALUE}};',
        //         ],
        //     ]
        // );


        // $this->add_control(
        //     'divider_list_title_sale_pricereguler_wpc',
        //     [
        //         'label' => esc_html__('Reguler Sale Price', 'rometheme-for-elementor'),
        //         'type' => \Elementor\Controls_Manager::HEADING,
        //         'separator' => 'before',
        //     ]
        // );

        // // sale price reguler
        // $this->add_group_control(
        //     \Elementor\Group_Control_Typography::get_type(),
        //     [
        //         'name' => 'sale_pricereguler_typography_wpc',
        //         'label' => esc_html__('Typography', 'rometheme-for-elementor'),

        //         'selector' => '{{WRAPPER}} .rkit-product-sale-price-reguler-wpc, {{WRAPPER}} .rkit-product-sale-price-reguler-wpc-pro, {{WRAPPER}} .rkit-product-sale-price-reguler-wpc-prem',

        //     ]
        // );

        // $this->add_control(
        //     'sale_pricereguler_color_wpc',
        //     [
        //         'label' => esc_html__('Text Color', 'rometheme-for-elementor'),
        //         'type' => \Elementor\Controls_Manager::COLOR,
        //         'selectors' => [
        //             '{{WRAPPER}} .rkit-product-sale-price-reguler-wpc, {{WRAPPER}} .rkit-product-sale-price-reguler-wpc-pro, {{WRAPPER}} .rkit-product-sale-price-reguler-wpc-prem' => 'color: {{VALUE}};',
        //         ],
        //     ]
        // );

        // $this->end_controls_section();

        //section rating style
        // $this->start_controls_section('rating_text_style_wpc', [
        //     'label' => esc_html__('Rating', 'rometheme-for-elementor'),
        //     'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        // ]);

        // $this->add_responsive_control(
        //     'rating_size_wpc',
        //     [
        //         'label' => esc_html__('Rating Size', 'rometheme-for-elementor'),
        //         'type' => \Elementor\Controls_Manager::SLIDER,
        //         'size_units' => ['px', '%', 'em', 'rem'],
        //         'range' => [
        //             'px' => [
        //                 'min' => 0,
        //                 'max' => 500,
        //                 'step' => 2,
        //             ],
        //             '%' => [
        //                 'min' => 10,
        //                 'max' => 100,
        //             ],
        //         ],
        //         'selectors' => [
        //             '{{WRAPPER}} .star-rating-wpc ' => 'font-size: {{SIZE}}{{UNIT}};',
        //         ],
        //     ]
        // );

        // $this->add_control(
        //     'rating_color_wpc',
        //     [
        //         'label' => esc_html__('Rating Color', 'rometheme-for-elementor'),
        //         'type' => \Elementor\Controls_Manager::COLOR,
        //         'selectors' => [
        //             '{{WRAPPER}} .star-rating-wpc span' => 'color: {{VALUE}};',
        //         ],
        //     ]
        // );

        // $this->add_control(
        //     'no_rating_color_wpc',
        //     [
        //         'label' => esc_html__('No Rating Color', 'rometheme-for-elementor'),
        //         'type' => \Elementor\Controls_Manager::COLOR,
        //         'selectors' => [
        //             '{{WRAPPER}} .star-rating-wpc' => 'color: {{VALUE}};',
        //         ],
        //     ]
        // );

        // $this->end_controls_section();

        // Style Section for Button
        $this->start_controls_section('button_style_section', [
            'label' => esc_html__('Button', 'rometheme-for-elementor'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);


        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'label' => esc_html__('Typography', 'rometheme-for-elementor'),
                'selector' => '{{WRAPPER}} .rkit-addcart-button-wpc-pro, {{WRAPPER}} .rkit-addcart-button-wpc,  {{WRAPPER}} .rkit-addcart-button-wpc-prem',
            ]
        );

        $this->add_responsive_control(
            'icon_size_wpc',
            [
                'label' => esc_html__('Icon Size', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 500,
                        'step' => 2,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-icon-readmore-wpc ' => 'font-size: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'show_button_icon_wgp' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'button_content_align',
            [
                'label' => esc_html__('Content Alignment', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__('Left', 'rometheme-for-elementor'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'rometheme-for-elementor'),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'end' => [
                        'title' => esc_html__('Right', 'rometheme-for-elementor'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'selectors' => [
                    ' {{WRAPPER}} .rkit-addcart-button-wpc-pro, {{WRAPPER}} .rkit-addcart-button-wpc,  {{WRAPPER}} .rkit-addcart-button-wpc-prem' => 'justify-content: {{VALUE}};',
                ],
                'default' => 'center',
                'condition' => [
                    'option_style' => ['-pro'],
                ]
            ]
        );

        $this->add_control(
            'gradient_color_one',
            [
                'label' => __('Border Gradient Color 1', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#00cea6',
                'selectors' => [
                    '{{WRAPPER}} .gradient-border' => '--gradient-color-one: {{VALUE}};',
                ],
                'condition' => [
                    'option_style' => ['-pro'],
                ]
            ]
        );

        // Kontrol warna kedua
        $this->add_control(
            'gradient_color_two',
            [
                'label' => __('Border Gradient Color 2', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#535353',
                'selectors' => [
                    '{{WRAPPER}} .gradient-border' => '--gradient-color-two: {{VALUE}};',
                ],
                'condition' => [
                    'option_style' => ['-pro'],
                ]
            ]
        );

        // $this->add_control(
        //     'more_options_icon_button_back',
        //     [
        //         'label' => esc_html__('Button Container Background', 'rometheme-for-elementor'),
        //         'type' => \Elementor\Controls_Manager::HEADING,
        //         'separator' => 'before',
        //     ]
        // );

        $this->add_responsive_control(
            'button_spacing',
            [
                'label' => esc_html__('Icon Spacing', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 500,
                        'step' => 2,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    ' {{WRAPPER}} .rkit-addcart-button-wpc-pro, {{WRAPPER}} .rkit-addcart-button-wpc, {{WRAPPER}} .rkit-addcart-button-wpc-prem' => 'gap: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'show_button_icon_wgp' => 'yes',
                ]
            ]
        );

        $this->add_responsive_control(
            'button_padding',
            [
                'label' => esc_html__('Padding', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'default' => [
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-addcart-button-wpc-pro, {{WRAPPER}} .gradient-border, {{WRAPPER}} .rkit-addcart-button-wpc,  {{WRAPPER}} .rkit-addcart-button-wpc-prem' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // /wkwkwkw
        $this->start_controls_tabs('button_tab');

        $this->start_controls_tab('button_tab_normal', ['label' => esc_html('Normal')]);

        $this->add_responsive_control(
            'button-size-pb',
            [
                'label' => esc_html__('Size', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 500,
                        'step' => 2,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 50,
                    'unit' => '%',
                ],
                'selectors' => [
                    '{{{WRAPPER}} .rkit-addcart-wrap-button-wpc-pro' => 'width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'option_style' => '-pro',
                ],
            ]
        );

        $this->add_control('button_text_color_normal', [
            'label' => esc_html('Text Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-addcart-button-wpc-pro, {{WRAPPER}} .rkit-addcart-button-wpc, {{WRAPPER}} .rkit-addcart-button-wpc-prem' => 'color : {{VALUE}}'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'border_bottom_btn_background_normall',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-addcart-button-wpc span::after ',
                'condition' => [
                    'option_style' => [''],
                ],
                'fields_options' => [
                    'background' => [
                        'label' => esc_html('Line Button Color')
                    ]
                ],

            ]
        );

        $this->add_control('button_icon_color_normal', [
            'label' => esc_html('Icon Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-icon-readmore-wpc' => 'color : {{VALUE}}'
            ]
        ]);

        // $this->add_control(
        //     'btn_bg_options_normal',
        //     [
        //         'label' => esc_html__('Button Background', 'rometheme-for-elementor'),
        //         'type' => \Elementor\Controls_Manager::HEADING,
        //         'separator' => 'before',
        //     ]
        // );


        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'btn_background_normall',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-addcart-button-wpc-pro, {{WRAPPER}} .rkit-addcart-button-wpc, {{WRAPPER}} .rkit-addcart-button-wpc-prem',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'woo_product_border',
                'selector' => '{{WRAPPER}} .rkit-addcart-button-wpc-pro, {{WRAPPER}} .gradient-border, {{WRAPPER}} .rkit-addcart-button-wpc,  {{WRAPPER}} .rkit-addcart-button-wpc-prem',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'btn_box_shadow_normal',
                'selector' => '{{WRAPPER}} .rkit-addcart-button-wpc-pro, {{WRAPPER}} .rkit-addcart-button-wpc, {{WRAPPER}} .rkit-addcart-button-wpc-prem',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('button_tab_hover', ['label' => esc_html('Hover')]);

        $this->add_control('button_text_color_hover', [
            'label' => esc_html('Text Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} a.rkit-addcart-button-wpc-pro:hover, 
        {{WRAPPER}} a.rkit-addcart-button-wpc-prem:hover, 
        {{WRAPPER}} a.rkit-addcart-button-wpc:hover ' => 'color : {{VALUE}}'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'border_bottom_btn_background_nhover',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-addcart-button-wpc:hover span::after ',
                'condition' => [
                    'option_style' => [''],
                ],
                'fields_options' => [
                    'background' => [
                        'label' => esc_html('Line Button Color')
                    ]
                ],
            ]
        );

        $this->add_control('button_icon_color_hover', [
            'label' => esc_html('Icon Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} a:hover .rkit-icon-readmore-wpc ' => 'color : {{VALUE}}'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'btn_background_hover',
                'types' => ['classic', 'gradient'],
                'selector' => ' {{WRAPPER}} a.rkit-addcart-button-wpc-prem:hover, {{WRAPPER}} a.rkit-addcart-button-wpc-pro:hover , {{WRAPPER}} a.rkit-addcart-button-wpc:hover',
            ]
        );

        $this->add_control(
            'btn_border_color_hover',
            [
                'label' => esc_html__('Border Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-addcart-button-wpc-pro:hover, {{WRAPPER}} .gradient-border:hover, {{WRAPPER}} .rkit-addcart-button-wpc:hover,  {{WRAPPER}} .rkit-addcart-button-wpc-prem:hover' => 'border-color: {{VALUE}}',
                ],
                'condition'=>[
                    'woo_product_border_border!'=>''
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'btn_box_shadow_hover',
                'selector' => '{{WRAPPER}} .rkit-addcart-button-wpc-pro:hover, {{WRAPPER}} .rkit-addcart-button-wpc:hover, {{WRAPPER}} .rkit-addcart-button-wpc-prem:hover',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'button_border_radius_divider',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ]
        );

        $this->add_responsive_control(
            'button_border_radius_normal',
            [
                'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-addcart-button-wpc-pro, {{WRAPPER}} .gradient-border, {{WRAPPER}} .rkit-addcart-button-wpc,  {{WRAPPER}} .rkit-addcart-button-wpc-prem' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();



        //ribbon
        $this->start_controls_section(
            'ribbon_style_section_wpc',
            [
                'label' => __(' Ribbon Style', 'rometheme-for-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'ribbon_position',
            [
                'label' => esc_html__('Position', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'rometheme-for-elementor'),
                        'icon' => 'eicon-order-start',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'rometheme-for-elementor'),
                        'icon' => 'eicon-order-end',
                    ],
                ],
                'default' => 'right',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-ribbon' => '{{VALUE}}: 0;',
                ],
            ]
        );

        $this->add_responsive_control(
            'ribbon_vertical_distance_wpc',
            [
                'label' => esc_html__('Vertical Distance', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 2,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-ribbon' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'ribbon_horizontal_distance_wpc_left',
            [
                'label' => esc_html__('Horizontal Distance', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 2,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-ribbon' => 'margin-left: {{SIZE}}{{UNIT}}; margin-right: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'ribbon_position' => 'left',
                ]
            ]
        );

        $this->add_responsive_control(
            'ribbon_horizontal_distance_wpc_right',
            [
                'label' => esc_html__('Horizontal Distance', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 2,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-ribbon' => 'margin-right: {{SIZE}}{{UNIT}}; margin-left: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'ribbon_position' => 'right',
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'ribbon_typography_wpc',
                'label' => __('Typography', 'rometheme-for-elementor'),
                'selector' => '{{WRAPPER}} .rkit-product-ribbon',
            ]
        );

        $this->add_control(
            'ribbon_text_color_wpc',
            [
                'label' => __('Text Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-ribbon' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'more_options_rib_wpc',
            [
                'label' => esc_html__('Background Ribbon', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),

            [
                'name' => 'ribbom_backgroud_wpc',
                'label' => esc_html__('Ribbon Background', 'rometheme-for-elementor'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-product-ribbon',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'ribbon_border_wpc',
                'label' => esc_html__('Border  ', 'rometheme-for-elementor'),
                'selector' => '{{WRAPPER}} .rkit-product-ribbon',
            ]
        );

        $this->add_responsive_control(
            'ribbon_radius_wpc',
            [
                'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-product-ribbon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'ribbon_box_shadow_wpc',
                'label' => __('Box Shadow', 'rometheme-for-elementor'),
                'selector' => '{{WRAPPER}} .rkit-product-ribbon',
            ]
        );

        $this->add_responsive_control(
            'ribbon_padding_wpc',
            [
                'label' => esc_html__('Padding', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}}  .rkit-product-ribbon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'ribbon_margin_wpc',
            [
                'label' => esc_html__('Margin', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}}  .rkit-product-ribbon' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Dots Style Section
        $this->start_controls_section(
            'dots_style_section_woo_product_carousel',
            [
                'label' => esc_html__('Dots', 'rometheme-for-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_dots_woo_product_carousel' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'dot_notice_woo_product_carousel',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<strong>Note:</strong> Enable "Show Dots" in the setting section content to see the effect of this setting.',
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );

        $this->add_control(
            'dot_alignment_woo_product_carousel',
            [
                'label' => esc_html__('Alignment', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => esc_html__('Left', 'rometheme-for-elementor'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'rometheme-for-elementor'),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'flex-end' => [
                        'title' => esc_html__('Right', 'rometheme-for-elementor'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .rkit-woo-product-carousel-pagination' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dot_spacing_woo_product_carousel',
            [
                'label' => esc_html__('Spacing', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'default' => [
                    'size' => 8,
                    'unit' => 'px'
                ],
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
                    '{{WRAPPER}} .rkit-woo-product-carousel-pagination' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dot_margin_woo_product_carousel',
            [
                'label' => esc_html__('Margin', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-woo-product-carousel-pagination' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('dot_tabs_woo_product_carousel');

        // Normal Tab
        $this->start_controls_tab('dot_tab_normal_woo_product_carousel', [
            'label' => esc_html__('Normal', 'rometheme-for-elementor'),
        ]);

        $this->add_responsive_control(
            'dot_width_normal_woo_product_carousel',
            [
                'label' => esc_html__('Width', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'default' => [
                    'unit' => 'px',
                    'size' => 8,
                ],
                'range' => [
                    'px' => ['min' => 0, 'max' => 100, 'step' => 1],
                    '%'  => ['min' => 0, 'max' => 100],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-woo-product-carousel-pagination .rkit-woo-carousel-bullet' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dot_height_normal_woo_product_carousel',
            [
                'label' => esc_html__('Height', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'default' => [
                    'unit' => 'px',
                    'size' => 8,
                ],
                'range' => [
                    'px' => ['min' => 0, 'max' => 100, 'step' => 1],
                    '%'  => ['min' => 0, 'max' => 100],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-woo-product-carousel-pagination .rkit-woo-carousel-bullet' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'dot_background_normal_woo_product_carousel',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-woo-product-carousel-pagination .rkit-woo-carousel-bullet',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'dot_border_normal_woo_product_carousel',
                'selector' => '{{WRAPPER}} .rkit-woo-product-carousel-pagination .rkit-woo-carousel-bullet',
            ]
        );

        $this->add_responsive_control(
            'dot_border_width_normal_woo_product_carousel',
            [
                'label' => esc_html__('Border Width', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => ['min' => 0, 'max' => 20],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-woo-product-carousel-pagination .rkit-woo-carousel-bullet' => 'border-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        // Hover Tab
        $this->start_controls_tab('dot_tab_hover_woo_product_carousel', [
            'label' => esc_html__('Hover', 'rometheme-for-elementor'),
        ]);

        $this->add_responsive_control(
            'dot_width_hover_woo_product_carousel',
            [
                'label' => esc_html__('Width', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'default' => [
                    'unit' => 'px',
                    'size' => 8,
                ],
                'range' => [
                    'px' => ['min' => 0, 'max' => 100, 'step' => 1],
                    '%'  => ['min' => 0, 'max' => 100],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-woo-product-carousel-pagination .rkit-woo-carousel-bullet:hover' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dot_height_hover_woo_product_carousel',
            [
                'label' => esc_html__('Height', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'default' => [
                    'unit' => 'px',
                    'size' => 8,
                ],
                'range' => [
                    'px' => ['min' => 0, 'max' => 100, 'step' => 1],
                    '%'  => ['min' => 0, 'max' => 100],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-woo-product-carousel-pagination .rkit-woo-carousel-bullet:hover' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'dot_background_hover_woo_product_carousel',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-woo-product-carousel-pagination .rkit-woo-carousel-bullet:hover',
            ]
        );

        $this->add_control(
            'dot_border_hover_woo_product_carousel',
            [
                'label' => esc_html__('Border Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-woo-product-carousel-pagination .rkit-woo-carousel-bullet:hover' => 'border-color: {{VALUE}};',
                ],
                'condition'=>[
                    'dot_border_normal_woo_product_carousel_border!'=>''
                ]
            ]
        );

        $this->end_controls_tab();

        // Active Tab
        $this->start_controls_tab('dot_tab_active_woo_product_carousel', [
            'label' => esc_html__('Active', 'rometheme-for-elementor'),
        ]);

        $this->add_responsive_control(
            'dot_width_active_woo_product_carousel',
            [
                'label' => esc_html__('Width', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'default' => [
                    'unit' => 'px',
                    'size' => 24,
                ],
                'range' => [
                    'px' => ['min' => 0, 'max' => 100, 'step' => 1],
                    '%'  => ['min' => 0, 'max' => 100],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-woo-product-carousel-pagination .rkit-woo-carousel-bullet.rkit-woo-carousel-bullet-active' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dot_height_active_woo_product_carousel',
            [
                'label' => esc_html__('Height', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'default' => [
                    'unit' => 'px',
                    'size' => 8,
                ],
                'range' => [
                    'px' => ['min' => 0, 'max' => 100, 'step' => 1],
                    '%'  => ['min' => 0, 'max' => 100],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-woo-product-carousel-pagination .rkit-woo-carousel-bullet.rkit-woo-carousel-bullet-active' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'dot_background_active_woo_product_carousel',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-woo-product-carousel-pagination .rkit-woo-carousel-bullet.rkit-woo-carousel-bullet-active',
            ]
        );

        $this->add_control(
            'dot_border_active_woo_product_carousel',
            [
                'label' => esc_html__('Border Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-woo-product-carousel-pagination .rkit-woo-carousel-bullet.rkit-woo-carousel-bullet-active' => 'border-color: {{VALUE}};',
                ],
                'condition'=>[
                    'dot_border_normal_woo_product_carousel_border!'=>''
                ]
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'divider_border_radius_woo_product_carousel',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ]
        );

        $this->add_responsive_control(
            'dot_radius_woo_product_carousel',
            [
                'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'default' => [
                    'top' => 12,
                    'bottom' => 12,
                    'left' => 12,
                    'right' => 12,
                    'unit' => 'px'
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-woo-product-carousel-pagination .rkit-woo-carousel-bullet' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Navigation Style Section for Woo Product Carousel
        $this->start_controls_section(
            'navigation_style_section_woo_product_carousel',
            [
                'label' => esc_html__('Navigation', 'rometheme-for-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_navigation' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'navigation_notice_woo_product_carousel',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<strong>Note:</strong> Enable "Show Navigation" in the navigation section content to see the effect of this setting.',
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );

        $this->add_responsive_control(
            'nav_icon_size_woo_product_carousel',
            [
                'label' => esc_html__('Icon Size', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
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
                'default' => [
                    'unit' => 'px',
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-swiper-button-prev-woo-product-carousel, {{WRAPPER}} .rkit-swiper-button-next-woo-product-carousel' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'show_navigation' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'navigation_spacing_woo_product_carousel',
            [
                'label' => esc_html__('Navigation Spacing', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'description' => esc_html__('Adjust the distance of the navigation buttons from the left and right edges. Use negative values (e.g., -20px) to position the buttons outside the carousel area.', 'rometheme-for-elementor'),
                'range' => [
                    'px' => [
                        'min' => -100,
                        'max' => 100,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => -100,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'devices' => ['desktop', 'tablet', 'mobile'],
                'default' => [
                    'unit' => 'px',
                    'size' => -8,
                ],
                'tablet_default' => [
                    'unit' => 'px',
                    'size' => 66,
                ],
                'mobile_default' => [
                    'unit' => 'px',
                    'size' => 61,
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-flex-absolute-woo-product-carousel' => 'left:{{SIZE}}{{UNIT}} !important; right:{{SIZE}}{{UNIT}} !important;',
                ],
                'condition' => [
                    'show_navigation' => 'yes',
                ],
            ]
        );

        $this->add_responsive_control(
            'padding_nav_icon_woo_product_carousel',
            [
                'label' => esc_html__('Padding', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'default' => [
                    'top' => 12,
                    'right' => 12,
                    'bottom' => 12,
                    'left' => 12,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-swiper-button-prev-woo-product-carousel' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .rkit-swiper-button-next-woo-product-carousel' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'show_navigation' => 'yes',
                ],
            ]
        );

        $this->start_controls_tabs('navigation_tabs_woo_product_carousel');

        // Normal Tab
        $this->start_controls_tab('navigation_tab_normal_woo_product_carousel', [
            'label' => esc_html__('Normal', 'rometheme-for-elementor'),
        ]);

        $this->add_control(
            'navigation_color_icon_woo_product_carousel',
            [
                'label' => esc_html__('Icon Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-swiper-button-prev-woo-product-carousel, {{WRAPPER}} .rkit-swiper-button-next-woo-product-carousel' => 'color: {{VALUE}}; fill: {{VALUE}};',
                ],
                'default' => '#1F1F1F',
                'condition' => [
                    'show_navigation' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'navigation_background_color_woo_product_carousel',
            [
                'label' => esc_html__('Background Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-swiper-button-prev-woo-product-carousel, {{WRAPPER}} .rkit-swiper-button-next-woo-product-carousel' => 'background-color: {{VALUE}};',
                ],
                'default' => '#F7F7F7',
                'condition' => [
                    'show_navigation' => 'yes',
                ],
            ]
        );

        $this->end_controls_tab();

        // Hover Tab
        $this->start_controls_tab('navigation_tab_hover_woo_product_carousel', [
            'label' => esc_html__('Hover', 'rometheme-for-elementor'),
        ]);

        $this->add_control(
            'navigation_color_icon_hover_woo_product_carousel',
            [
                'label' => esc_html__('Icon Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-swiper-button-prev-woo-product-carousel:hover, {{WRAPPER}} .rkit-swiper-button-next-woo-product-carousel:hover' => 'color: {{VALUE}}; fill: {{VALUE}};',
                ],
                'default' => '#00CEA6',
                'condition' => [
                    'show_navigation' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'navigation_background_color_hover_woo_product_carousel',
            [
                'label' => esc_html__('Background Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-swiper-button-prev-woo-product-carousel:hover, {{WRAPPER}} .rkit-swiper-button-next-woo-product-carousel:hover' => 'background-color: {{VALUE}};',
                ],
                'default' => '#00CEA6',
                'condition' => [
                    'show_navigation' => 'yes',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'divider_border_radius_nav_woo_product_carousel',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ]
        );

        $this->add_responsive_control(
            'border_radius_nav_icon_woo_product_carousel',
            [
                'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'default' => [
                    'top' => 6,
                    'right' => 6,
                    'bottom' => 6,
                    'left' => 6,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-swiper-button-prev-woo-product-carousel, {{WRAPPER}} .rkit-swiper-button-next-woo-product-carousel' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'show_navigation' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $direction = $settings['titlle_direction'] ?? 'row';
        $direction_tablet = $settings['titlle_direction_tablet'] ?? $direction;
        $direction_mobile = $settings['titlle_direction_mobile'] ?? $direction;
        $product_count = $settings['product_count'];
        $product_count = $settings['product_count'];
        add_filter('woocommerce_product_get_rating_html', [$this, 'custom_rating_html'], 10, 3);

        $args = [
            'post_type' => 'product',
            'posts_per_page' => $product_count,
            'order' => $settings['order_wpc'],
            'orderby' => $settings['order_by_wpc'],
            'tax_query' => [],
        ];

        if (!empty($settings['product_categories'])) {
            $args['tax_query'][] = [
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $settings['product_categories'],
            ];
        }

        $products = new \WP_Query($args);

        $showAnimationNavigation = ($settings['show_animation'] == 'yes') ? 'rkit-animation-hover-woo-product-carousel-enabled' : 'rkit-animation-hover-woo-product-carousel-disabled';

        $config = [
            'rtl' => is_rtl(),
            'arrows' => ($settings['show_navigation'] === 'yes') ? true : false,
            'dots' => ($settings['show_dots_woo_product_carousel'] === 'yes') ? true : false,
            'pauseOnHover' => ($settings['pause_on_hover_woo_product_carousel'] === 'yes') ? true : false,
            'autoplay' => ($settings['autoplay_woo_product_carousel'] === 'yes') ? true : false,
            'speed' => !empty($settings['speed_woo_product_carousel']['size']) ? $settings['speed_woo_product_carousel']['size'] : 1000,
            'slidesPerGroup' => !empty($settings['slide_to_scroll_woo_product_carousel']) ? $settings['slide_to_scroll_woo_product_carousel'] : 1,
            'slidesPerView' => !empty($settings['slide_to_show_woo_product_carousel']['size']) ? $settings['slide_to_show_woo_product_carousel']['size'] : 1,
            'loop' => ($settings['loop_woo_product_carousel'] === 'yes') ? true : false,
            'breakpoints' => [
                319 => [
                    'slidesPerView' => !empty($settings['slide_to_show_woo_product_carousel_mobile']['size']) ? $settings['slide_to_show_woo_product_carousel_mobile']['size'] : 1,
                    'slidesPerGroup' => !empty($settings['slide_to_scroll_woo_product_carousel_mobile']['size']) ? $settings['slide_to_scroll_woo_product_carousel_mobile']['size'] : 1,
                    'spaceBetween' => isset($settings['spacebetween_woo_pc_mobile']['size']) && !empty($settings['spacebetween_woo_pc_mobile']['size']) ? $settings['spacebetween_woo_pc_mobile']['size'] : 10,
                ],
                767 => [
                    'slidesPerView' => !empty($settings['slide_to_show_woo_product_carousel_tablet']['size']) ? $settings['slide_to_show_woo_product_carousel_tablet']['size'] : 2,
                    'slidesPerGroup' => !empty($settings['slide_to_scroll_woo_product_carousel_tablet']['size']) ? $settings['slide_to_scroll_woo_product_carousel_tablet']['size'] : 1,
                    'spaceBetween' => isset($settings['spacebetween_woo_pc_tablet']['size']) && !empty($settings['spacebetween_woo_pc_tablet']['size']) ? $settings['spacebetween_woo_pc_tablet']['size'] : 10,
                ],
                1025 => [
                    'slidesPerView' => !empty($settings['slide_to_show_woo_product_carousel']['size']) ? $settings['slide_to_show_woo_product_carousel']['size'] : 4,
                    'slidesPerGroup' => !empty($settings['slide_to_scroll_woo_product_carousel']['size']) ? $settings['slide_to_scroll_woo_product_carousel']['size'] : 1,
                    'spaceBetween' => isset($settings['spacebetween_woo_pc']['size']) && !empty($settings['spacebetween_woo_pc']['size']) ? $settings['spacebetween_woo_pc']['size'] : 10,
                ],
            ],
        ];

?>
        <div class="rkit-woo-product-carousel-container <?= $showAnimationNavigation ?>">
            <div class=" swiper" data-config="<?php echo esc_attr(json_encode($config)); ?>">
                <?php if ($settings['show_dots_woo_product_carousel'] === 'yes' && $settings['dots_position_woo_product_carousel'] === 'top'): ?>
                    <div class="rkit-woo-product-carousel-pagination"></div>
                <?php endif; ?>
                <div class="swiper-wrapper <?php echo (!empty($settings['option_style']) ? ' ' . esc_attr('rkit-product-carousel-wpc' . $settings['option_style']) : ''); ?> ">
                    <?php
                    if ($products->have_posts()) {
                        while ($products->have_posts()) : $products->the_post();
                            $btnLink = ($settings['button_function'] == 'add_to_cart') ? wc_get_cart_url() . '?add-to-cart=' . get_the_ID() : get_the_permalink();

                    ?>
                            <div class="swiper-slide rkit-woo-pc-card">
                                <div class="rkit-product-image-wpc">
                                    <?php if (wc_get_product()->is_on_sale()) : ?>
                                        <div class="rkit-product-ribbon rkit-product-ribbon-wpc">Sale</div>
                                    <?php endif; ?>
                                    <div class="rkit-product-image-container">
                                        <?php echo woocommerce_get_product_thumbnail(); ?>
                                    </div>
                                    <div class="rkit-addcart-wrap-button-wpc">
                                        <a class="rkit-addcart-button-wpc" href="<?php echo esc_url($btnLink); ?>">
                                            <span> <?php echo esc_html__($settings['button_text_wpc'], 'rometheme-for-elementor') ?> </span>
                                            <?php
                                            \Elementor\Icons_Manager::render_icon($settings['button_icon_wgp'], ['aria-hidden' => 'true', 'class' => 'rkit-icon-readmore-wpc']);
                                            ?>
                                        </a>
                                    </div>
                                </div>
                                <div class="rkit-product-details-wpc">
                                    <div class="rkit-product-info-wpc">
                                        <a href="<?php the_permalink(); ?>">
                                            <h3 class="rkit-product-title-wpc"><?php the_title(); ?></h3>
                                        </a>
                                        <p class="rkit-product-category-wpc"><?php echo wc_get_product_category_list(get_the_ID()); ?></p>
                                    </div>
                                    <div class="rkit-product-feat-wpc">
                                        <div class="if-sale-price-wpc">
                                            <span class="rkit-product-sale-price-reguler-wpc">
                                                <?php echo wc_price(wc_get_product()->get_regular_price())  ?>
                                            </span>
                                            <?php if (wc_get_product()->is_on_sale()) : ?>
                                                <span class="rkit-product-sale-price-wpc">
                                                    <?php echo wc_price(wc_get_product()->get_sale_price()); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php
                                        $product = wc_get_product(get_the_ID());
                                        $average = $product->get_average_rating();
                                        ?>
                                        <div class="rkit-product-rating-wpc 
                                        <?= ($direction_tablet === 'row') ? 'align-end-tablet' : '' ?> 
                                        <?= ($direction === 'row') ? 'align-end' : '' ?> 
                                        <?= ($direction_mobile === 'row') ? 'align-end-mobile' : '' ?>
                                        ">
                                            <div class="star-rating-wpc">
                                                <?php if ($average > 0) : ?>
                                                    <span style="width: <?php echo esc_attr(($average / 5) * 100); ?>%;">★★★★★</span>
                                                <?php endif; ?>
                                                ★★★★★
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php
                        endwhile;
                    } else {
                        echo '<div>No products found</div>';
                    }
                    wp_reset_postdata();
                    remove_filter('woocommerce_product_get_rating_html', [$this, 'custom_rating_html'], 10);
                    ?>
                </div>
                <?php if ($settings['show_dots_woo_product_carousel'] === 'yes' && $settings['dots_position_woo_product_carousel'] === 'bottom'): ?>
                    <div class="rkit-woo-product-carousel-pagination"></div>
                <?php endif; ?>
            </div>
            <div class="rkit-flex-absolute-woo-product-carousel">
                <?php if ($settings['show_navigation'] === 'yes') : ?>
                    <div class="rkit-swiper-button-prev-woo-product-carousel"><?php \Elementor\Icons_Manager::render_icon($settings['prev_icon'], ['aria-hidden' => 'true']); ?></div>
                    <div class="rkit-swiper-button-next-woo-product-carousel"><?php \Elementor\Icons_Manager::render_icon($settings['next_icon'], ['aria-hidden' => 'true']); ?></div>
                <?php endif; ?>
            </div>
        </div>
<?php
    }

    // Metode custom_rating_html() dan lainnya tetap sama seperti kode asli Anda
}
?>