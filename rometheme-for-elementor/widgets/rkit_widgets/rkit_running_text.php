<?php

class Rkit_RunningText extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'rkit-running_text';
    }
    public function get_title()
    {
        return \RomethemeKit\RkitWidgets::listWidgets()['runningtext']['name'];
    }

    public function get_icon()
    {
        $icon = 'rkit-widget-icon ' . \RomethemeKit\RkitWidgets::listWidgets()['runningtext']['icon'];
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
        return 'https://support.rometheme.net/docs/romethemekit/widgets/how-to-use-ezd_ampersand-customize-text-marquee-widget/';
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
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__('Rometheme Text Running', 'rometheme-for-elementor'),
                'placeholder' => esc_html__('Type your Text here', 'rometheme-for-elementor'),
            ]
        );

        // $repeater->add_responsive_control('item_types', [
        //     'label' => esc_html__('Item Type', 'rometheme-for-elementor'),
        //     'type' => \Elementor\Controls_Manager::SELECT,
        //     'options' => [ 
        //         'icon' => esc_html__('Icon', 'rometheme-for-elementor'), 
        //         'image' => esc_html__('Image', 'rometheme-for-elementor'),
        //     ],   
        //     'default' => 'icon' 
        // ]);

        $repeater->add_control(
            'item_type',
            [
                'label' => esc_html__('Item Type', 'textdomain'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'None' => [
                        'title' => esc_html__('None', 'textdomain'),
                        'icon' => 'eicon-ban',
                    ],
                    'icon' => [
                        'title' => esc_html__('Icon', 'textdomain'),
                        'icon' => 'eicon-info',
                    ],
                    'image' => [
                        'title' => esc_html__('Image', 'textdomain'),
                        'icon' => 'eicon-image',
                    ],
                ],
               'default' => 'none' ,
                'description' => esc_html__('Choose Image or Icon', 'text-domain'),
            ]
        );

        $repeater->add_control(
            'item_icon',
            [
                'label' => esc_html__('Icon', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'condition' => [
                    'item_type' => 'icon',
                ]
            ]
        );

        $repeater->add_control(
            'item_image',
            [
                'label' => esc_html__( 'Choose Image', 'textdomain' ),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'item_type' => 'image',
                ]
            ]
        );

        $this->add_control('item_text', [
            'label' => esc_html('Item'),
            'type' => \Elementor\Controls_Manager::REPEATER,
            'fields' => $repeater->get_controls(),
            'default' => [
                [
                    'text' => esc_html('Welcome to our website!')
                ],
                [
                    'text' => esc_html('Thank you for choosing us!')
                ],
                [
                    'text' => esc_html('Stay tuned for more updates')
                ]
            ],
            'title_field' => '{{{ text }}}'
        ]);

        $this->add_control(
            'speed_control',
            [
                'label' => esc_html__('Speed', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'default' => [
                    'size' => 7
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-marquee-container' => '--speed:{{SIZE}}'
                ]
            ]
        );

        $this->add_control(
            'direction',
            [
                'label' => esc_html__('Direction', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'normal' => [
                        'title' => esc_html__('Left', 'rometheme-for-elementor'),
                        'icon' => 'eicon-arrow-left',
                    ],
                    'reverse' => [
                        'title' => esc_html__('Right', 'rometheme-for-elementor'),
                        'icon' => 'eicon-arrow-right',
                    ],
                ],
                'default' => 'normal',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .rkit-marquee-content.rkit-marquee'  => 'animation-direction:{{VALUE}}'
                ]
            ]
        );

        $this->add_control(
            'pause_on_hover',
            [
                'label' => esc_html__('Pause On Hover', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'rometheme-for-elementor'),
                'label_off' => esc_html__('No', 'rometheme-for-elementor'),
                'return_value' => 'pause-hover',
                'default' => 'pause-hover',
            ]
        );


        $this->add_control('html_tag', [
            'label' => esc_html('Tag'),
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

        $this->end_controls_section();

        $this->start_controls_section('container_style', ['label' => esc_html('Container'), 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'background_container',
                'types' => ['classic', 'gradient', 'video'],
                'selector' => '{{WRAPPER}} .rkit-text-marquee',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow_container',
                'selector' => '{{WRAPPER}} .rkit-text-marquee',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'border_container',
                'selector' => '{{WRAPPER}} .rkit-text-marquee',
            ]
        );

        $this->add_responsive_control(
            'padding_container',
            [
                'label' => esc_html__('Padding', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-text-marquee' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'border_radius_container',
            [
                'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-text-marquee' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'item_spacing',
            [
                'label' => esc_html__('Spacing', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 5,
                    ]
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-marquee-container' => '--gap : {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('content_style', [
            'label' => esc_html('Content'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'background_content',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-marquee-item-content',
            ]
        );

        $this->add_responsive_control(
            'content_padding',
            [
                'label' => esc_html__('Padding', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-marquee-item-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_radius',
            [
                'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-marquee-item-content' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'border_content',
                'selector' => '{{WRAPPER}} .rkit-marquee-item-content',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow_content',
                'selector' => '{{WRAPPER}} .rkit-marquee-item-content',
            ]
        );

        $this->end_controls_section();


        $this->start_controls_section('text_style', ['label' => esc_html('Text'), 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'text_typography',
                'selector' => '{{WRAPPER}} .rkit-running-text__text',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'running_text_color',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-running-text__text',
                'fields_options' => [
                    'background' => [
                        'label' => esc_html('Text Color')
                    ]
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'text_shadow',
                'selector' => '{{WRAPPER}} .rkit-running-text__text',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'text_stroke',
                'selector' => '{{WRAPPER}} .rkit-running-text__text',
            ]
        );

        $this->add_control(
            'icons_options',
            [
                'label' => esc_html__('Icons', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );


      


        $this->add_control(
            'running_icon_color',
            [
                'label' => esc_html__('Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-running-text__icon' => 'color: {{VALUE}} ; fill : {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => esc_html__('Size', 'rometheme-for-elementor'),
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
                    '{{WRAPPER}} .rkit-running-text__icon' => 'font-size: {{SIZE}}{{UNIT}}; width:{{SIZE}}{{UNIT}} ; height:{{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->add_control(
            'images_options',
            [
                'label' => esc_html__('Image', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before', 
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Css_Filter::get_type(),
            [
                'label' => esc_html__('Image Filter', 'textdomain'),
                'name' => 'custom_css_filters_tm',
                'selector' => '{{WRAPPER}} .image-running img', 
            ]
        );

        
    $this->add_responsive_control('img-aspect-ratio-tm', [
        'label' => esc_html__('Image Aspect Ratio', 'rometheme-for-elementor'),
        'type' => \Elementor\Controls_Manager::SELECT,
        'options' => [
            'auto' => esc_html__('none', 'rometheme-for-elementor'),
            '1/1' => esc_html__('1 : 1', 'rometheme-for-elementor'),
            '3/2' => esc_html__('3 : 2', 'rometheme-for-elementor'),
            '5/4' => esc_html__('5 : 4', 'rometheme-for-elementor'),
            '16/9' => esc_html__('16 : 9', 'rometheme-for-elementor'),
            '9/16' => esc_html__('9 : 16', 'rometheme-for-elementor'),

        ],  
        'selectors' => [
            '{{WRAPPER}} .image-running img' => 'aspect-ratio:{{VALUE}}'
        ],
        'default' => '1/1', 
    ]);

    $this->add_responsive_control('object_fit_tm', [
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
            '{{WRAPPER}} .image-running img' => 'object-fit:{{VALUE}}'
        ],
        'default' => 'cover', 
    ]);

    $this->add_group_control(
        \Elementor\Group_Control_Box_Shadow::get_type(),
        [
            'name' => 'image_running_box_shadow',
            'label' => esc_html__('Box Shadow', 'rometheme-for-elementor'),
            'selector' => '{{WRAPPER}} .image-running',
        ]
    );

    $this->add_responsive_control(
        'image_size_tm',
        [
            'label' => esc_html__('Size', 'rometheme-for-elementor'),
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
                '{{WRAPPER}} .image-running' => 'width:{{SIZE}}{{UNIT}}; height:auto;',
            ], 
        ]
    );

    $this->add_control(
        'image_con_radius',
        [
            'label' => esc_html__('Border Radius', 'textdomain'),
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
                '{{WRAPPER}} .image-running img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]
    );


    $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'image_container_border',
                'label' => esc_html__('Border', 'rometheme-for-elementor'),
                'selector' => '{{WRAPPER}} .image-running img',
            ]
        );
    


    $this->add_control(
        'content_options',
        [
            'label' => esc_html__('Settings', 'rometheme-for-elementor'),
            'type' => \Elementor\Controls_Manager::HEADING,
            'separator' => 'before', 
        ]
    );

    $this->add_responsive_control(
        'icon_image_spacing',
        [
            'label' => esc_html__('Spacing', 'rometheme-for-elementor'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px', '%', 'em', 'rem'],
            'selectors' => [
                '{{WRAPPER}} .rkit-marquee-item-content' => 'gap: {{SIZE}}{{UNIT}};',
            ],
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
?>

        <div class="rkit-text-marquee">
            <div class="rkit-marquee-container">
                <div class="rkit-marquee-content <?php echo esc_attr($settings['pause_on_hover']) ?>">
                    <?php foreach ($settings['item_text']  as $text) : ?>
                        <div class="rkit-marquee-item elementor-repeater-item-<?php echo esc_attr($text['_id']) ?>">
                            <<?php echo esc_attr($html_tag) ?> class="rkit-marquee-item-content">
                      
                                <?php \Elementor\Icons_Manager::render_icon($text['item_icon'], ['aria-hidden' => 'true', 'class' => 'rkit-running-text__icon']); ?>
                               
                                <?php if(!$text['item_image']) : ?>
                                    <div style="width: 0px; height:0px;"> </div>
                                <?php else :?>
                                <div class="image-running" >
                                    <?php
                                        $this->add_render_attribute('item_image', 'src', $text['item_image']['url']);
                                        $this->add_render_attribute('item_image', 'alt', \Elementor\Control_Media::get_image_alt($text['item_image']));
                attachment:             $this->add_render_attribute('item_image', 'title', \Elementor\Control_Media::get_image_title($text['item_image']));
                                        echo \Elementor\Group_Control_Image_Size::get_attachment_image_html($text, 'thumbnail', 'item_image');
                                    ?>
                                </div>
                                <?php endif; ?>
                               

                                <div class="rkit-running-text__text">
                                    <?php echo esc_html($text['text']) ?>
                                </div>
                                
                            </d<?php echo esc_attr($html_tag) ?>>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
<?php
    }
}
