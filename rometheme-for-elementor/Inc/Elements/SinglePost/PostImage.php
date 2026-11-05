<?php

namespace RTMKit\Elements\SinglePost;

defined('ABSPATH') || exit;

use WP_Query;

class PostImage extends SinglePostWidget
{
    public static function metadata(): array
    {
        return [
            'id' => 'rtm-post-image',
            'icon' => 'rtmicon rtmicon-featured-image',
            'name' => esc_html('Featured Image'),
            'widget_name' => 'rtm-post-image',
            'category' => self::CATEGORY,
            'type' => 'free',
            'badge' => esc_html('New'),
            'requires' => [],
            'tags' => ['rtm', 'excerpt', 'post excerpt'],
            'priority' => 10,
            'docsURL' => 'https://support.rometheme.net/docs/romethemekit/widgets/',
            'previewURL' => 'https://support.rometheme.net/docs/romethemekit/widgets/',
            'videoURL' => 'https://support.rometheme.net/docs/romethemekit/widgets/',
            'scripts' => [
                'css' => 'post-image.css',
                'js' => ''
            ]
        ];
    }

    public function widgetSlug(): string
    {
        return $this->get_name();
    }

    protected function register_controls()
    {
        $this->start_controls_section('content_section', [
            'label' => esc_html('Post Image'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Image_Size::get_type(),
            [
                'name' => 'image_size', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `thumbnail_size` and `thumbnail_custom_dimension`.
                'exclude' => ['custom'],
                'include' => [],
                'default' => 'large',
            ]
        );

        $this->add_control('caption_select', [
            'label' => esc_html('Caption'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'none' => esc_html('None'),
                'caption' => esc_html('Attachment Caption'),
                'custom_caption' => esc_html('Custom Caption')
            ]
        ]);

        $this->add_control('prefix_caption', [
            'label' => esc_html('Prefix Caption'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'condition' => [
                'caption_select!' => 'none'
            ]
        ]);

        $this->add_control('custom_caption', [
            'label' => esc_html('Custom Caption'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'condition' => [
                'caption_select' => 'custom_caption'
            ]
        ]);

        $this->end_controls_section();

        $this->start_controls_section('post-image-style', [
            'label' => esc_html('Post Image'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_responsive_control(
            'image_align',
            [
                'label' => esc_html__('Alignment', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__('Left', 'romethemekitpro'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'romethemekitpro'),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'end' => [
                        'title' => esc_html__('Right', 'romethemekitpro'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'default' => 'center',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-image' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control('image_width', [
            'label' => esc_html('Width'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px', '%', 'em', 'rem', 'custom'],
            'range' => [
                'px' => [
                    'min' => 0,
                    'max' => 2000,
                    'step' => 1,
                ],
                '%' => [
                    'min' => 0,
                    'max' => 100,
                ],
                'em' => [
                    'min' => 0,
                    'max' => 200,
                ],
                'rem' => [
                    'min' => 0,
                    'max' => 200,
                ],
                'custom' => [
                    'min' => 0,
                    'max' => 2000,
                ],
            ],
            'default' => [
                'unit' => '%',
                'size' => 100,
            ],
            'selectors' => [
                '{{WRAPPER}} .rkit-post-image img' => 'width: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('image_max_width', [
            'label' => esc_html('Max Width'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px', '%', 'em', 'rem', 'custom'],
            'range' => [
                'px' => [
                    'min' => 0,
                    'max' => 2000,
                    'step' => 1,
                ],
                '%' => [
                    'min' => 0,
                    'max' => 100,
                ],
                'em' => [
                    'min' => 0,
                    'max' => 200,
                ],
                'rem' => [
                    'min' => 0,
                    'max' => 200,
                ],
                'custom' => [
                    'min' => 0,
                    'max' => 2000,
                ],
            ],
            'default' => [
                'unit' => 'px',
                'size' => 800,
            ],
            'selectors' => [
                '{{WRAPPER}} .rkit-post-image img' => 'max-width: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('image_height', [
            'label' => esc_html('Height'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px', 'vh'],
            'range' => [
                'px' => [
                    'min' => 0,
                    'max' => 2000,
                    'step' => 1,
                ],
                'vh' => [
                    'min' => 0,
                    'max' => 100,
                ]
            ],
            'default' => [
                'unit' => 'px',
                'size' => '',
            ],
            'selectors' => [
                '{{WRAPPER}} .rkit-post-image img' => 'height: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('image_object_fit', [
            'label' => esc_html('Object Fit'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                '' => esc_html('Default'),
                'fill' => esc_html('Fill'),
                'contain' => esc_html('Contain'),
                'cover' => esc_html('Cover'),
                'scale-down' => esc_html('Scale Down'),
            ],
            'default' => 'cover',
            'selectors' => [
                '{{WRAPPER}} .rkit-post-image img' => 'object-fit: {{VALUE}};',
            ],
            'conditions' => [
                'terms' => [
                    [
                        'name' => 'image_height[size]',
                        'operator' => '!=',
                        'value' => '',
                    ],
                ],
            ],
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'image_border',
                'selector' => '{{WRAPPER}} .rkit-post-image img',
                'exclude' => [
                    'color',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_radius',
            [
                'label' => esc_html__('Border Radius', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->add_responsive_control(
            'image_margin',
            [
                'label' => esc_html__('Margin', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-image img' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'image_padding',
            [
                'label' => esc_html__('Padding', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-image img' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->start_controls_tabs('image_effects');
        $this->start_controls_tab('image_normal', [
            'label' => esc_html('Normal')
        ]);
        $this->add_responsive_control('image_opacity', [
            'label' => esc_html('Opacity'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['%'],
            'range' => [
                '%' => [
                    'min' => 0,
                    'max' => 100,
                ],
            ],
            'default' => [
                'unit' => '%',
                'size' => 100,
            ],
            'selectors' => [
                '{{WRAPPER}} .rkit-post-image img' => 'opacity: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Css_Filter::get_type(),
            [
                'name' => 'image_css_filters',
                'selector' => '{{WRAPPER}} .rkit-post-image img',
            ]
        );

        $this->add_responsive_control(
            'image_border_color', // ID harus sama persis dengan default group
            [
                'label' => esc_html__('Border Color', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-image img' => 'border-color: {{VALUE}};',
                ],
                // Opsi tambahan agar muncul tepat di bawah border
                'condition' => [
                    'image_border_border!' => '', // Muncul hanya jika border style dipilih
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'image_box_shadow',
                'selector' => '{{WRAPPER}} .rkit-post-image img',
            ]
        );

        $this->end_controls_tab();
        $this->start_controls_tab('image_hover', [
            'label' => esc_html('Hover')
        ]);
        $this->add_responsive_control('image_opacity_hover', [
            'label' => esc_html('Opacity'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['%'],
            'range' => [
                '%' => [
                    'min' => 0,
                    'max' => 100,
                ],
            ],
            'default' => [
                'unit' => '%',
                'size' => 100,
            ],
            'selectors' => [
                '{{WRAPPER}} .rkit-post-image img:hover' => 'opacity: {{SIZE}}{{UNIT}};',
            ],
        ]);

        // hover css filter
        $this->add_group_control(
            \Elementor\Group_Control_Css_Filter::get_type(),
            [
                'name' => 'image_css_filters_hover',
                'selector' => '{{WRAPPER}} .rkit-post-image img:hover',
            ]
        );

        $this->add_responsive_control('image_border_color_hover', [
            'label' => esc_html('Border Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-image img:hover' => 'border-color:{{VALUE}}'
            ],
            'condition' => [
                'image_border_border!' => '',
            ],
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'image_box_shadow_hover',
                'selector' => '{{WRAPPER}} .rkit-post-image img:hover',
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section('caption_style', [
            'label' => esc_html('Caption'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [
                'caption_select!' => 'none'
            ]
        ]);

        $this->add_responsive_control(
            'caption_align',
            [
                'label' => esc_html__('Alignment', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'romethemekitpro'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'romethemekitpro'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'romethemekitpro'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-image__caption' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'caption_typography',
                'selector' => '{{WRAPPER}} .rkit-post-image__caption',
            ]
        );

        $this->add_control('caption_color', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-image__caption' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'caption_text_shadow',
                'selector' => '{{WRAPPER}} .rkit-post-image__caption',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'caption_text_stroke',
                'selector' => '{{WRAPPER}} .rkit-post-image__caption',
            ]
        );


        $this->add_responsive_control(
            'caption_margin',
            [
                'label' => esc_html__('Margin', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-image__caption' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    // protected function render()
    // {

    //     $settings = $this->get_settings_for_display();
    //     echo '<div class="post-image-container">';
    //     if (defined('ELEMENTOR_PATH') && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
    //         // Query untuk mendapatkan satu posting secara acak
    //         $args = [
    //             'post_type'      => 'post', // Ganti dengan custom post type Anda jika diperlukan
    //             'posts_per_page' => 1,
    //             'orderby'        => 'rand'
    //         ];

    //         $random_post_query = new WP_Query($args);

    //         if ($random_post_query->have_posts()) {
    //             while ($random_post_query->have_posts()) {
    //                 $random_post_query->the_post(); // Mengatur data posting global ke posting acak
    //                 echo '<div class="rkit-post-image">';
    //                 the_post_thumbnail($settings['image_size_size']);
    //                 echo '</div>';
    //                 if ($settings['caption_select'] != 'none') {
    //                     echo '<p class="rkit-post-image__caption">';
    //                     if ($settings['caption_select'] == 'caption') {
    //                         echo $settings['prefix_caption'];
    //                         the_post_thumbnail_caption();
    //                     } else {
    //                         echo $settings['prefix_caption'] . $settings['custom_caption'];
    //                     }
    //                     echo '</p>';
    //                 }
    //             }
    //             wp_reset_postdata();
    //         }
    //     } else {
    //         $obj = get_queried_object();
    //         echo '<div class="rkit-post-image">';
    //         echo get_the_post_thumbnail($obj->ID, $settings['image_size_size']);
    //         echo '</div>';
    //         if ($settings['caption_select'] != 'none') {
    //             echo '<p class="rkit-post-image__caption">';
    //             if ($settings['caption_select'] == 'caption') {
    //                 echo $settings['prefix_caption'];
    //                 echo get_the_post_thumbnail_caption($obj->ID);
    //             } else {
    //                 echo $settings['prefix_caption'] . $settings['custom_caption'];
    //             }
    //             echo '</p>';
    //         }
    //     }

    //     echo '</div>';
    // }

    protected function render()
    {

        $settings = $this->get_settings_for_display();
        echo '<div class="post-image-container">';

        $obj = get_queried_object();

        // Default fallback
        $post_type = 'post';

        // Kalau ada post type gunakan itu
        if ($obj && isset($obj->post_type)) {
            $post_type = $obj->post_type;
        }

        if (defined('ELEMENTOR_PATH') && \Elementor\Plugin::$instance->editor->is_edit_mode()) {

            $args = [
                'post_type' => $post_type,
                'posts_per_page' => 1,
                'orderby' => 'rand'
            ];

            $random_post_query = new WP_Query($args);

            if ($random_post_query->have_posts()) {
                while ($random_post_query->have_posts()) {
                    $random_post_query->the_post(); // Mengatur data posting global ke posting acak
                    echo '<div class="rkit-post-image">';

                    if (has_post_thumbnail()) {

                        the_post_thumbnail($settings['image_size_size']);
                    } else {

                        echo '<img src="' . \Elementor\Utils::get_placeholder_image_src() . '" alt="Placeholder">';
                    }

                    echo '</div>';
                    if ($settings['caption_select'] != 'none') {
                        echo '<p class="rkit-post-image__caption">';
                        if ($settings['caption_select'] == 'caption') {
                            echo $settings['prefix_caption'];
                            the_post_thumbnail_caption();
                        } else {
                            echo $settings['prefix_caption'] . $settings['custom_caption'];
                        }
                        echo '</p>';
                    }
                }
                wp_reset_postdata();
            }
        } else {
            $obj = get_queried_object();
            echo '<div class="rkit-post-image">';

            if (has_post_thumbnail($obj->ID)) {

                echo get_the_post_thumbnail(
                    $obj->ID,
                    $settings['image_size_size']
                );
            } else {

                echo '<img src="' . \Elementor\Utils::get_placeholder_image_src() . '" alt="Placeholder">';
            }

            echo '</div>';
            if ($settings['caption_select'] != 'none') {
                echo '<p class="rkit-post-image__caption">';
                if ($settings['caption_select'] == 'caption') {
                    echo $settings['prefix_caption'];
                    echo get_the_post_thumbnail_caption($obj->ID);
                } else {
                    echo $settings['prefix_caption'] . $settings['custom_caption'];
                }
                echo '</p>';
            }
        }
        echo '</div>';
    }
}
