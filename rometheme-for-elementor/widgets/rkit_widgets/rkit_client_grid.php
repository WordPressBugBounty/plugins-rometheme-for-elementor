<?php

class Rkit_Clientgrid extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'rkit-clientgrid';
    } 
    public function get_title()
    {
        return \RomethemeKit\RkitWidgets::listWidgets()['client_grid']['name'];
        
    }

    public function get_icon()
    {
        $icon = 'rkit-widget-icon '. \RomethemeKit\RkitWidgets::listWidgets()['client_grid']['icon'];
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
        return ['rkit-client_grid-style'];
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
                'label' => __( 'Column', 'rometheme-for-elementor' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                // 'default' => 3,
                'desktop_default' => 4,
                    'tablet_default' => 2,
                    'mobile_default' => 1,
                'selectors' => [
                    ' {{WRAPPER}} .client-grid-wrapper' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ],
            ]
        );

        $this->add_control(
            'hover_style',
            [
                'label' => esc_html__(' Hover Style', 'textdomain'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'default' => esc_html__('Default', 'textdomain'),
                    'horizontal' => esc_html__('Horizontal', 'textdomain'),
                    'vertical' => esc_html__('Vertical', 'textdomain'),
                    'fade_in'  => esc_html__('Overlay', 'textdomain'),
                ],
                'default' => 'default',
                'description' => esc_html__('Hover function ready to use except at default mode', 'text-domain'), 
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

        $this->add_responsive_control('img-aspect-ratio', [
            'label' => esc_html__('Image Aspect Ratio', 'rometheme-for-elementor'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                '1/1' => esc_html__('1 : 1', 'rometheme-for-elementor'),
                '3/2' => esc_html__('3 : 2', 'rometheme-for-elementor'),
                '5/4' => esc_html__('5 : 4', 'rometheme-for-elementor'),
                '16/9' => esc_html__('16 : 9', 'rometheme-for-elementor'),
                '9/16' => esc_html__('9 : 16', 'rometheme-for-elementor'),
            ], 
            'default' => '16/9',
            'selectors' => [
                '{{WRAPPER}} .clientslogo-image-full-cg, {{WRAPPER}} .clientslogo-image-full-cg img, {{WRAPPER}} .image-container-cg' => 'aspect-ratio:{{VALUE}}'
            ]
        ]);



        $card_list = new \Elementor\Repeater();       


        $card_list->add_control(
            'show_title',
            [
                'label' => esc_html__('Show title', 'text-domain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Hide', 'text-domain'),
                'label_off' => esc_html__('Show', 'text-domain'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );
        $card_list->add_control(
            'card_title',
            [
                'label' => esc_html__('Title', 'textdomain'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => esc_html__('Type your title here', 'textdomain'),
                'condition' => [
                    'show_title' => 'yes'
                ]
            ]
        );

        //hover 
        $card_list->add_control(
            'hover_text',
            [
                'label' => esc_html__('Hover Text', 'text-domain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'text-domain'),
                'label_off' => esc_html__('Hide', 'text-domain'),
                'return_value' => 'yes',
                'default' => 'no',
               
                
            ]
        );

        $card_list->add_control(
            'hover_image_choose',
            [
                'label' => esc_html__('Choose Hover', 'text-domain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'text-domain'),
                'label_off' => esc_html__('Hide', 'text-domain'),
                'return_value' => 'yes',
                'default' => 'yes',   
            ]
        );



        $card_list->add_control(
            'hover_title',
            [
                'label' => esc_html__('Hover Title', 'textdomain'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Hover Title', 'textdomain'),
                'condition' => [
                    'hover_text' => 'yes', 
                    //  'select_style_hover' => 'yes'
 
                ]

            ]
        );
        
        $card_list->add_control(
            'hover_description',
            [
                'label' => esc_html__('Hover Description', 'textdomain'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__('Hover Description', 'textdomain'),
                'condition' => [
                    'hover_text' => 'yes',
                    // 'select_style_hover' => 'yes'
                ],
                'description' => esc_html__('Short Description Only', 'text-domain'),
            ]
        );
    
      

  
        $card_list->add_control(
            'hover',
            [
                'label' => esc_html__('Choose Image Hover', 'textdomain'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
                 'condition' => [
                    'hover_text!' => 'yes', 
                    'hover_image_choose' => 'yes'
                ],
           
            ]
        );
        // end hover 

       
        
        $card_list->add_control(
            'image',
            [
                'label' => esc_html__('Choose Image', 'textdomain'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
            ]
        );

        $card_list->add_control(
            'card_link',
            [
                'label' => esc_html__('Link', 'textdomain'),
                'type' => \Elementor\Controls_Manager::URL,
                'options' => ['url', 'is_external', 'nofollow'],
                'label_block' => true,
            ]
        );

        $this->add_control(
            'list',
            [
                'label' => esc_html__('Repeater List', 'textdomain'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $card_list->get_controls(),
                'default' => [
                    [
                        'card_title' => esc_html__('Example Title 1', 'textdomain'),
                       
                    ],
                    [
                        'card_title' => esc_html__('Example Title 2', 'textdomain'),
                        
                    ],
                    [
                        'card_title' => esc_html__('Example Title 3', 'textdomain'),
                       
                    ],  
                    [
                        'card_title' => esc_html__('Example Title 4', 'textdomain'),
                       
                    ],  
                ],
                'title_field' => '{{{card_title}}}',
            ]
        );

        $this->end_controls_section();


        
    //container style
    $this->start_controls_section('Container_style_section', [
        'label' => esc_html__('Container', 'textdomain'),
        'tab' => \Elementor\Controls_Manager::TAB_STYLE,
    ]);

        $this->add_control(
            'Header_padding',
            [
                'label' => esc_html__('Padding', 'textdomain'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%','em','rem'],
               
                'selectors' => [
                    '{{WRAPPER}} .client-grid-wrapper' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        ); 

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'container_box_shadow',
                'label' => __('Container Box Shadow', 'plugin-name'),
                'selector' => '{{WRAPPER}} .client-grid-wrapper',
            ]
        );

        $this->add_control(
            'background_container',
            [
                'label' => esc_html__('Background', 'textdomain'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'background_container_all',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .client-grid-wrapper',
            ]
        );

    $this->end_controls_section();
  

    //image style
    $this->start_controls_section('image_style', [
        'label' => esc_html('Image'),
        'tab' => \Elementor\Controls_Manager::TAB_STYLE
    ]);
   
    $this->add_group_control(
        \Elementor\Group_Control_Border::get_type(),
        [
            'name' => 'image_border',
            'label' => esc_html__('Border  ', 'textdomain'),
            'selector' => '{{WRAPPER}} .image-container-cg '
        ]
    );

   

    $this->add_control(
        'image_space',
        [
            'label' => esc_html__( 'Spacing Image', 'textdomain' ),
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
                '{{WRAPPER}} .client-grid-wrapper' => 'gap: {{SIZE}}{{UNIT}};',
            ],
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

    
    $this->add_control(
        'padding',
        [
            'label' => esc_html__('Padding Image', 'textdomain'),
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
                '{{WRAPPER}} .image-container-cg .clientslogo-image-full-cg img, {{WRAPPER}} .hover-content-cg' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );  

    $this->add_control(
        'border_radius',
        [
            'label' => esc_html__('Border Radius Image', 'textdomain'),
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

    $this->add_group_control(
        \Elementor\Group_Control_Background::get_type(),
        [
            'name' => 'image_background',
            'label' => esc_html__('Background', 'rometheme-for-elementor'),
            'types' => ['classic', 'gradient'],
            'selector' => '{{WRAPPER}} .clientslogo-image-full-cg',
            'description' => esc_html__('Use For Transparent background & PNG format', 'text-domain'), 
        ]
    );
    $this->end_controls_section();

    // title style
    $this->start_controls_section(
        'style_section',
        [
            'label' => esc_html__('Section Title', 'text-domain'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            'description' => esc_html__('Use For Title section', 'text-domain'), 
        ]
    );

    

    $this->add_control(
        'title_color_external',
        [
            'label' => esc_html__('Title Color', 'text-domain'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                    '{{WRAPPER}} .card-title' => 'color: {{VALUE}};'
                ],
        ]
    );

    $this->add_group_control(
        \Elementor\Group_Control_Typography::get_type(),
        [
            'name' => 'title_typography',
            'label' => esc_html__('Title Typography', 'text-domain'),
            'selector' => '{{WRAPPER}} .card-title',
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


    $this->add_responsive_control(
        'title_align',
        [
            'label' => esc_html__('Title Alignment', 'text-domain'),
            'type' => \Elementor\Controls_Manager::CHOOSE,
            'options' => [
                'left' => [
                    'title' => esc_html__('Left', 'text-domain'),
                    'icon' => 'eicon-text-align-left',
                ],
                'center' => [
                    'title' => esc_html__('Center', 'text-domain'),
                    'icon' => 'eicon-text-align-center',
                ],
                'right' => [
                    'title' => esc_html__('Right', 'text-domain'),
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
            'label' => esc_html__('Spacing', 'textdomain'),
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
    $this->end_controls_section();

     // Style Section for Hover
     $this->start_controls_section('hover_style_section', [
        'label' => esc_html__('Hover Text Style', 'textdomain'),
        'tab' => \Elementor\Controls_Manager::TAB_STYLE,
    ]);

    // title
    $this->add_control(
        'title_hover_color',
        [
            'label' => esc_html__('Title Color', 'textdomain'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .hover-title-cg' => 'color: {{VALUE}};',
            ],
        ]
    );

    $this->add_group_control(
        \Elementor\Group_Control_Typography::get_type(),
        [
            'name' => 'title_hover_typography',
            'label' => esc_html__('Title Typography', 'textdomain'),
            'selector' => '{{WRAPPER}} .hover-title-cg',
            
        ]
    );

    $this->add_control(
            'title_hover_padding',
            [
                'label' => esc_html__('Title Padding', 'textdomain'),
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
                    '{{WRAPPER}} .hover-title-cg ' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'title_hover_align',
            [
                'label' => esc_html__('Title Alignment', 'textdomain'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__('Left', 'textdomain'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'textdomain'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'end' => [
                        'title' => esc_html__('Right', 'textdomain'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .hover-title-cg' => 'align-self: {{VALUE}}; text-align: {{VALUE}};',
                ],
                'default' => 'center',
            ]
        );

        // Description
        $this->add_control(
            'desc_hover_color',
            [
                'label' => esc_html__('Desc Color', 'textdomain'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .hover-description-cg' => 'color: {{VALUE}};',
                ],
            ]
        );
    
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'description_hover_typography',
                'label' => esc_html__('Desc Typography', 'textdomain'),
                'selector' => '{{WRAPPER}} .hover-description-cg',
                
            ]
        );
        $this->add_control(
                'desc_hover_padding',
                [
                    'label' => esc_html__('Desc Padding', 'textdomain'),
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
                        '{{WRAPPER}} .hover-description-cg ' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );
    
            $this->add_control(
                'desc_hover_align',
                [
                    'label' => esc_html__('Desc Alignment', 'textdomain'),
                    'type' => \Elementor\Controls_Manager::CHOOSE,
                    'options' => [
                        'start' => [
                            'title' => esc_html__('Left', 'textdomain'),
                            'icon' => 'eicon-text-align-left',
                        ],
                        'center' => [
                            'title' => esc_html__('Center', 'textdomain'),
                            'icon' => 'eicon-text-align-center',
                        ],
                        'end' => [
                            'title' => esc_html__('Right', 'textdomain'),
                            'icon' => 'eicon-text-align-right',
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .hover-description-cg' => 'align-self: {{VALUE}}; text-align: {{VALUE}};',
                    ],
                    'default' => 'center',
                ]
            );

            // content hover
            $this->add_group_control(
                \Elementor\Group_Control_Background::get_type(),
                [
                    'name' => 'background',
                    'types' => [ 'classic', 'gradient'],
                    'selector' => '{{WRAPPER}} .hover-content-cg',
                ]
            );

            $this->add_control(
                'text_hover_align',
                [
                    'label' => esc_html__('Vertical Alignment', 'textdomain'),
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
                    'label' => esc_html__('Spacing', 'textdomain'),
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
        
    $this->end_controls_section();

        

   
    
    }
    protected function render() {
        $settings = $this->get_settings_for_display();
    
        
    ?>
             <!-- main container -->   
             <div class="client-grid-container">
             <div class="client-grid-wrapper">
                   <?php  
                    foreach ($settings['list'] as $li) : 
                    $hoverText = $li['hover_text'] === 'yes';
                    $hoverTitle = $hoverText ? $li['hover_title'] : '';
                    $hoverDescription = $hoverText ? $li['hover_description'] : ''; 

                        if (!empty($li['card_link']['url'])) {
                            $this->add_link_attributes('card_link_' . $li['_id'], $li['card_link']);
                        }
                    ?>
                    
                        <div class="container-image-cg">
                            <div class="rkit-card-client "> 
                                <div class="image-container-cg <?php echo esc_attr($settings['hover_style']); ?>">
                                    <div class="clientslogo-image-full-cg image-default">
                                        <?php   

                                            $image_html_url = \Elementor\Group_Control_Image_Size::get_attachment_image_html($li, 'thumbnail', 'image');
                                            $image_html_url = str_replace('<img ', '<img class="image-cover" ', $image_html_url);
                                            echo $image_html_url;
                                        ?>
                                    </div>
                                    <?php if (!$hoverText or $settings['hover_style']=="default") : ?>
                                    <div class="clientslogo-image-full-cg image-hover-cg">
                                        <?php
                                            $this->add_render_attribute('hover', 'src', $li['hover']['url']);  
                                            $image_html_hover = \Elementor\Group_Control_Image_Size::get_attachment_image_html($li, 'thumbnail', 'hover');
                                            $image_html_hover = str_replace('<img ', '<img class="image-cover-hover " ', $image_html_hover);
                                            echo $image_html_hover;
                                        ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($hoverText) : ?>
                                    <div class="hover-content-cg  image-hover-cg">
                                        <span class="hover-title-cg"><?php echo esc_html($hoverTitle); ?></span>
                                        <span class="hover-description-cg"><?php echo esc_html($hoverDescription); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                               
                              <!--  -->
                            </div>
                            <?php if($li['show_title'] == 'yes') {?>
                                <div class="card-body">
                                    <div class="card-heading">
                                            <a <?php $this->print_render_attribute_string('card_link_' . $li['_id']) ?> > 
                                            <p   class="card-title">   <?php echo esc_html($li['card_title']) ?>     </p>
                                            </a>
                                    </div>
                                </div>
                            <?php } ?> 
                        </div>
                    <?php endforeach; ?>
                    </div>
                    </div>
               
      
    <?php
    }
    
}
