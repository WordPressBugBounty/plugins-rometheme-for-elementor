<?php

namespace RTMKit\Elements\SinglePost;

defined('ABSPATH') || exit;

use WP_Query;
use Elementor\Repeater;

class PostInfo extends SinglePostWidget
{
    public static function metadata(): array
    {
        return [
            'id' => 'rtmpro-post-info',
            'icon' => 'rtmicon rtmicon-post-info',
            'name' => esc_html('Post Meta'),
            'widget_name' => 'rtmpro-post-info',
            'category' => self::CATEGORY,
            'type' => 'free',
            'badge' => esc_html('New'),
            'requires' => [],
            'tags' => ['rtm', 'info', 'post info'],
            'priority' => 10,
            'docsURL' => 'https://support.rometheme.net/docs/romethemekit/widgets/',
            'previewURL' => 'https://support.rometheme.net/docs/romethemekit/widgets/',
            'videoURL' => 'https://support.rometheme.net/docs/romethemekit/widgets/',
            'scripts' => [
                'css' => 'post-info.css',
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
            'label' => esc_html('Meta Data'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT
        ]);



        $items = new Repeater();

        $items->add_control('type_select', [
            'label' => esc_html('Type'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'Author' => esc_html('Author'),
                'Date' => esc_html('Date'),
                'Time' => esc_html('Time'),
                'Comment' => esc_html('Comments'),
                'Terms' => esc_html('Terms'),
                'Custom' => esc_html('Custom')
            ],
            'default' => 'Date'
        ]);

        $items->add_control('date_format_select', [
            'label' => esc_html('Date Format'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'F j, Y' => esc_html(date('F j, Y') . ' (F j, Y)'),
                'Y-m-d' => esc_html(date('Y-m-d') . ' (Y-m-d)'),
                'm/d/Y' => esc_html(date('m/d/Y') . ' (m/d/Y)'),
                'd/m/Y' => esc_html(date('d/m/Y') . ' (d/m/Y)'),
                'default' => esc_html('Default'),
                'custom' => esc_html('Custom')
            ],
            'default' => 'default',
            'condition' => [
                'type_select' => 'Date'
            ]
        ]);

        $items->add_control('date_format_custom', [
            'label' => esc_html('Custom Date Format'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => 'F j , Y',
            'description' => esc_html('Use the letters: l D d j S F m M n Y y'),
            'condition' => [
                'type_select' => 'Date',
                'date_format_select' => 'custom'
            ]
        ]);

        $items->add_control('time_format_select', [
            'label' => esc_html('Time Format'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'g:i a' => esc_html(date('g:i a') . ' (g:i a)'),
                'g:i A' => esc_html(date('g:i A') . ' (g:i A)'),
                'H:i' => esc_html(date('H:i') . ' (H:i)'),
                'default' => esc_html('Default'),
                'custom' => esc_html('Custom')
            ],
            'default' => 'default',
            'condition' => [
                'type_select' => 'Time'
            ]
        ]);

        $items->add_control('time_format_custom', [
            'label' => esc_html('Custom Time Format'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => 'g:i a',
            'description' => esc_html('Use the letters: g G H i a A'),
            'condition' => [
                'type_select' => 'Time',
                'time_format_select' => 'custom'
            ]
        ]);

        $items->add_control(
            'custom_comment',
            [
                'label' => esc_html__('Custom Format', 'romethemekit-pro'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'romethemekit-pro'),
                'label_off' => esc_html__('No', 'romethemekit-pro'),
                'return_value' => 'yes',
                'default' => 'no',
                'condition' => [
                    'type_select' => 'Comment'
                ]
            ]
        );

        $items->add_control('custom_no_comment', [
            'label' => esc_html('No Comments'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => 'No Comment',
            'condition' => [
                'type_select' => 'Comment',
                'custom_comment' => 'yes'
            ]
        ]);

        $items->add_control('custom_one_comment', [
            'label' => esc_html('One Comments'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => 'One Comment',
            'condition' => [
                'type_select' => 'Comment',
                'custom_comment' => 'yes'
            ]
        ]);

        $items->add_control('custom_format_comment', [
            'label' => esc_html('Comments'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => '%s Comments',
            'condition' => [
                'type_select' => 'Comment',
                'custom_comment' => 'yes'
            ]
        ]);

        $items->add_control(
            'taxonomi_select',
            [
                'label' => esc_html('Taxonomy'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'label_block' => true,
                'multiple' => false,
                'options' => [
                    'category'  => esc_html('Categories'),
                    'post_tag' => esc_html('Tags'),
                    'product_category' => esc_html('Product Category'),
                    'product_tag' => esc_html('Product Tag'),
                ],
                'condition' => [
                    'type_select' => 'Terms'
                ]
            ]
        );

        $items->add_control('custom_info', [
            'label' => esc_html('Custom'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'condition' => [
                'type_select' => 'Custom'
            ]
        ]);

        $items->add_control('text_before', [
            'label' => esc_html('Before'),
            'type' => \Elementor\Controls_Manager::TEXT
        ]);

        $items->add_control('avatar_switch', [
            'label' => esc_html('Avatar'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => esc_html__('Yes', 'romethemekit-pro'),
            'label_off' => esc_html__('No', 'romethemekit-pro'),
            'return_value' => 'yes',
            'default' => 'no',
            'condition' => [
                'type_select' => 'Author'
            ]
        ]);

        $items->add_control(
            'avatar_size',
            [
                'label' => esc_html__('Size', 'romethemekit-pro'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
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
                    'size' => 30,
                    'unit' => 'px'
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-info__item img' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'type_select' => 'Author',
                    'avatar_switch' => 'yes'
                ]
            ]
        );

        $items->add_control('link_switch', [
            'label' => esc_html('Link'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => esc_html__('Yes', 'romethemekit-pro'),
            'label_off' => esc_html__('No', 'romethemekit-pro'),
            'return_value' => 'yes',
            'default' => 'yes',
            'condition' => [
                'type_select!' => 'Time'
            ]
        ]);

        $items->add_control(
            'custom_link',
            [
                'label' => esc_html('Custom URL'),
                'type' => \Elementor\Controls_Manager::URL,
                'options' => ['url', 'is_external', 'nofollow'],
                'default' => [
                    'url' => '',
                    'is_external' => true,
                    'nofollow' => true,
                    // 'custom_attributes' => '',
                ],
                'label_block' => true,
                'condition' => [
                    'type_select' => 'Custom',
                    'link_switch' => 'yes'
                ]
            ]
        );

        $items->add_control('icon_select', [
            'label' => esc_html('Icon'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'none' => esc_html('None'),
                'default' => esc_html('Default'),
                'custom' => esc_html('Custom')
            ],
            'default' => 'default',
            'condition' => [
                'avatar_switch!' => 'yes'
            ]
        ]);

        $items->add_control(
            'choose_icon',
            [
                'label' => esc_html__('Choose Icon', 'romethemekit-pro'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'condition' => [
                    'icon_select' => 'custom',
                    'avatar_switch!' => 'yes'
                ]
            ]
        );

        $this->add_responsive_control(
            'layout_post_info',
            [
                'label' => esc_html('layout'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'inline' => [
                        'title' => esc_html('Inline'),
                        'icon' => 'eicon-ellipsis-h',
                    ],
                    'list' => [
                        'title' => esc_html('List'),
                        'icon' => 'eicon-editor-list-ul',
                    ],
                ],
                'default' => 'inline',
                'toggle' => true,
                'prefix_class' => 'rkit-post-info-%s-',
            ]
        );

        $this->add_control('item_lists', [
            'label' => esc_html('Post Info'),
            'type' =>  \Elementor\Controls_Manager::REPEATER,
            'fields' => $items->get_controls(),
            'default' => [
                [
                    'type_select' => 'Author',
                ],
                [
                    'type_select' => 'Date'
                ],
                [
                    'type_select' => 'Time'
                ],
                [
                    'type_select' => 'Comment'
                ]
            ],
            'title_field' => '{{{ type_select }}}',
        ]);

        $this->end_controls_section();

        $this->start_controls_section('list_info_style', ['label' => esc_html('List'), 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);

        $this->add_responsive_control(
            'layout_alignment',
            [
                'label' => esc_html__('Alignment', 'romethemekit-pro'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'start' => [
                        'title' => esc_html__('Left', 'romethemekit-pro'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'romethemekit-pro'),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'end' => [
                        'title' => esc_html__('Right', 'romethemekit-pro'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'default' => 'start',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-info' => '--alignment: {{VALUE}};'
                ],
            ]
        );

        $this->add_responsive_control(
            'list_gap',
            [
                'label' => esc_html('Space Between'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
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
                    '{{WRAPPER}} .rkit-post-info' => '--space-between: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'show_divider',
            [
                'label' => esc_html('Show Divider'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'romethemekit-pro'),
                'label_off' => esc_html__('No', 'romethemekit-pro'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $this->add_control('divider_style_select', [
            'label' => esc_html('Style'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'solid' => esc_html('Solid'),
                'dashed' => esc_html('Dashed'),
                'dotted' => esc_html('Dotted'),
                'double' => esc_html('Double'),
            ],
            'default' => 'solid',
            'selectors' => [
                '{{WRAPPER}} .rkit-post-info-divider' => '--divider-style:{{VALUE}};'
            ],
            'condition' => [
                'show_divider' => 'yes'
            ]
        ]);

        $this->add_control(
            'divider_weight',
            [
                'label' => esc_html__('Weight', 'romethemekit-pro'),
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
                    '{{WRAPPER}} .rkit-post-info-divider' => '--divider-width:{{SIZE}}{{UNIT}};'
                ],
                'condition' => [
                    'show_divider' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'divider_width',
            [
                'label' => esc_html__('Width', 'romethemekit-pro'),
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
                    // '{{WRAPPER}} .rkit-post-info.rkit-post-info-list.rkit-post-info-divider li:not(:last-child)::after' => 'width:{{SIZE}}{{UNIT}}'
                    '{{WRAPPER}} .rkit-post-info--list.rkit-post-info-divider li:not(:last-child)::after' => 'width:{{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .rkit-post-info--tablet-list.rkit-post-info-divider li:not(:last-child)::after' => 'width:{{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .rkit-post-info--mobile-list.rkit-post-info-divider li:not(:last-child)::after' => 'width:{{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'show_divider' => 'yes',
                    'layout_post_info' => 'rkit-post-info-list'
                ]
            ]
        );

        $this->add_control(
            'divider_height',
            [
                'label' => esc_html__('Height', 'romethemekit-pro'),
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
                    // '{{WRAPPER}} .rkit-post-info.rkit-post-info-inline.rkit-post-info-divider li:not(:last-child)::after' => 'height:{{SIZE}}{{UNIT}}'
                    '{{WRAPPER}} .rkit-post-info--inline .rkit-post-info-divider li:not(:last-child)::after' => 'height:{{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .rkit-post-info--tablet-inline .rkit-post-info-divider li:not(:last-child)::after' => 'height:{{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .rkit-post-info--mobile-inline .rkit-post-info-divider li:not(:last-child)::after' => 'height:{{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'show_divider' => 'yes',
                    'layout_post_info' => 'rkit-post-info-inline'
                ]
            ]
        );

        $this->add_control('divider_color', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-info-divider' => '--divider-color:{{VALUE}}'
            ],
            'condition' => [
                'show_divider' => 'yes',
            ]
        ]);

        $this->end_controls_section();

        $this->start_controls_section('icon_style', [
            'label' => esc_html('Icon'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => esc_html('Size'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
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
                    '{{WRAPPER}} .rkit-post-info__icon' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs(
            'post_info_icon_tabs'
        );

        $this->start_controls_tab(
            'style_normal_tab',
            [
                'label' => esc_html__('Normal', 'romethemekitpro'),
            ]
        );

        $this->add_control('icon_color', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-info__icon' => 'color:{{VALUE}}'
            ]
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab(
            'style_hover_tab',
            [
                'label' => esc_html__('Hover', 'romethemekitpro'),
            ]
        );

        $this->add_control('icon_color_hover', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} li:hover .rkit-post-info__item .rkit-post-info__icon' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_control(
            'icon_transition_duration',
            [
                'label' => esc_html__('Transition Duration', 'romethemekit-pro'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['s', 'ms', 'custom'],
                'range' => [
                    's' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                    'ms' => [
                        'min' => 0,
                        'max' => 5000,
                        'step' => 100,
                    ],
                ],
                'default' => [
                    'size' => 0.3,
                    'unit' => 's',
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-info__icon' => 'transition: all {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section('text_style', [
            'label' => esc_html('Text'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'text_typography',
                'selector' => '{{WRAPPER}} .rkit-post-info__item',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'text_shadow_normal',
                'selector' => '{{WRAPPER}} .rkit-post-info__item',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'text_stroke_normal',
                'selector' => '{{WRAPPER}} .rkit-post-info__item',
            ]
        );

        $this->start_controls_tabs('text_tabs');

        $this->start_controls_tab('text_tab_normal', ['label' => esc_html('Normal')]);

        $this->add_control('text_color_normal', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-info__item' => 'color:{{VALUE}}'
            ]
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('text_tab_hover', ['label' => esc_html('Hover')]);

        $this->add_control('text_color_hover', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-info__item:hover' => 'color:{{VALUE}}'
            ]
        ]);

        // $this->add_group_control(
        //     \Elementor\Group_Control_Text_Shadow::get_type(),
        //     [
        //         'name' => 'text_shadow_hover',
        //         'selector' => '{{WRAPPER}} .rkit-post-info__item:hover',
        //     ]
        // );

        // $this->add_group_control(
        //     \Elementor\Group_Control_Text_Stroke::get_type(),
        //     [
        //         'name' => 'text_stroke_hover',
        //         'selector' => '{{WRAPPER}} .rkit-post-info__item:hover',
        //     ]
        // );

        $this->add_control(
            'text_transition_post_info',
            [
                'label' => esc_html__('Transition Duration', 'romethemekit-pro'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['s', 'ms', 'custom'],
                'range' => [
                    's' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                    'ms' => [
                        'min' => 0,
                        'max' => 5000,
                        'step' => 100,
                    ],
                ],
                'default' => [
                    'size' => 0.3,
                    'unit' => 's',
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-info__item' => 'transition: all {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        if (defined('ELEMENTOR_PATH') && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
            // Query untuk mendapatkan satu posting secara acak
            $args = [
                'post_type'      => 'post', // Ganti dengan custom post type Anda jika diperlukan
                'posts_per_page' => 1,
                'orderby'        => 'rand'
            ];

            $random_post_query = new WP_Query($args);

            if ($random_post_query->have_posts()) {
                while ($random_post_query->have_posts()) {
                    $random_post_query->the_post();
                    $postid = get_the_ID();
                }
                wp_reset_postdata();
            }
        } else {
            $obj = get_queried_object();
            $postid = $obj->ID;
        }

        $post = get_post($postid);

        // $class = $settings['layout_post_info'];
        $divider = ($settings['show_divider'] === 'yes') ? 'rkit-post-info-divider' : '';

        echo sprintf('<ul class="rkit-post-info %s">', $divider);
        foreach ($settings['item_lists'] as $item) :
            echo '<li>';
            $this->render_item($item, $post, $settings);
            echo '</li>';
        endforeach;
        echo '</ul>';
    }

    protected function render_item($item, $post, $settings)
    {
        switch ($item['type_select']):
            case 'Author':
                $url = get_author_posts_url($post->post_author);
                if ($item['avatar_switch'] === 'yes') :
                    $avatarSize = $item['avatar_size']['size'] ?? null;
                    $iconHTML = get_avatar($post->post_author);
                else :
                    ob_start();
                    $icon = ($item['icon_select'] == 'default') ? ['value' => 'rtmicon rtmicon-user', 'library' => 'rtmicons'] : $item['choose_icon'];
                    \Elementor\Icons_Manager::render_icon($icon, ['aria-hidden' => 'true', 'class' => 'rkit-post-info__icon']);
                    $iconHTML = ob_get_clean();
                endif;
                $text = get_the_author_meta('display_name', $post->post_author);
                break;

            case 'Date':
                $publish_date = $post->post_date;

                $year  = date('Y', strtotime($publish_date));
                $month = date('m', strtotime($publish_date));
                $day   = date('d', strtotime($publish_date));

                $url = get_day_link($year, $month, $day);

                ob_start();
                $icon = ($item['icon_select'] == 'default') ? ['value' => 'rtmicon rtmicon-calendar', 'library' => 'rtmicons'] : $item['choose_icon'];
                \Elementor\Icons_Manager::render_icon($icon, ['aria-hidden' => 'true', 'class' => 'rkit-post-info__icon']);
                $iconHTML = ob_get_clean();

                if ($item['date_format_select'] == 'default') {
                    $text = date('F j, Y', strtotime($publish_date));
                } elseif ($item['date_format_select'] == 'custom') {
                    $text = date($item['date_format_custom'], strtotime($publish_date));
                } else {
                    $text = date($item['date_format_select'], strtotime($publish_date));
                }

                break;

            case 'Time':
                ob_start();
                $icon = ($item['icon_select'] == 'default') ? ['value' => 'rtmicon rtmicon-clock', 'library' => 'rtmicons'] : $item['choose_icon'];
                \Elementor\Icons_Manager::render_icon($icon, ['aria-hidden' => 'true', 'class' => 'rkit-post-info__icon']);
                $iconHTML = ob_get_clean();

                $publish_date = $post->post_date;

                if ($item['time_format_select'] == 'default') {
                    $text = date('g:i a', strtotime($publish_date));
                } elseif ($item['time_format_select'] == 'custom') {
                    $text = date($item['time_format_custom'], strtotime($publish_date));
                } else {
                    $text = date($item['time_format_select'], strtotime($publish_date));
                }
                break;

            case 'Comment':
                ob_start();
                $icon = ($item['icon_select'] == 'default') ? ['value' => 'rtmicon rtmicon-comments', 'library' => 'rtmiconsss'] : $item['choose_icon'];
                \Elementor\Icons_Manager::render_icon($icon, ['aria-hidden' => 'true', 'class' => 'rkit-post-info__icon']);
                $iconHTML = ob_get_clean();

                $commentCount = $post->comment_count;
                $comment_link = get_comments_link($post);
                $url = $comment_link;

                if ($item['custom_comment'] === 'yes') :
                    if ($commentCount == 0) {
                        $text = $item['custom_no_comment'];
                    } else if ($commentCount == 1) {
                        $text = $item['custom_one_comment'];
                    } else {
                        $text = sprintf($item['custom_format_comment'], $commentCount);
                    }
                else :
                    if ($commentCount == 0) {
                        $text = esc_html('No Comments');
                    } else {
                        $text = sprintf('%s Comments', $commentCount);
                    }
                endif;
                break;

            case 'Terms':
                ob_start();
                $icon = ($item['icon_select'] == 'default') ? ['value' => 'rtmicon rtmicon-tag', 'library' => 'rtmicons'] : $item['choose_icon'];
                \Elementor\Icons_Manager::render_icon($icon, ['aria-hidden' => 'true', 'class' => 'rkit-post-info__icon']);
                $iconHTML = ob_get_clean();

                $taxonomy = get_the_terms($post->ID, $item['taxonomi_select']);

                if (!empty($taxonomy) && !is_wp_error($taxonomy)) {
                    $url = '';
                    $terms_output = array();

                    foreach ($taxonomy as $term) {
                        $term_link = get_term_link($term);
                        if (!is_wp_error($term_link) and $item['link_switch'] == 'yes') {
                            $terms_output[] = '<a href="' . esc_url($term_link) . '">' . esc_html($term->name) . '</a>';
                        } else {
                            $terms_output[] = esc_html($term->name);
                        }
                    }

                    $text = implode(', ', $terms_output);
                } else {
                    $text = '';
                }
                break;

            case 'Custom':
                if (!empty($item['custom_link']['url'])) {
                    $this->add_link_attributes('custom_link' . $item['_id'], $item['custom_link']);
                }
                break;

        endswitch;

        if ($item['type_select'] == 'Custom') :
?>
            <a <?php $this->print_render_attribute_string('custom_link' . $item['_id']); ?> class="rkit-post-info__item">
                <?php
                $icon = ($item['icon_select'] == 'default') ? ['value' => 'eicon-info', 'library' => 'eicons'] : $item['choose_icon'];
                \Elementor\Icons_Manager::render_icon($icon, ['aria-hidden' => 'true', 'class' => 'rkit-post-info__icon']);
                ?>
                <span class="rkit-post-info__text"><?php echo esc_html($item['custom_info']) ?></span>
            </a>
<?php
        else :

            $html = ($item['link_switch'] == 'yes') ? ($item['type_select'] === 'Terms') ? '<div class="rkit-post-info__item">%s %s</div>' : '<a href="' . $url . '" class="rkit-post-info__item" >%s %s</a>' : '<span class="rkit-post-info__item">%s %s</span>';
            $textHTML = sprintf('<span class="rkit-post-info__text">%s %s</span>', $item['text_before'], $text);
            echo sprintf($html, $iconHTML, $textHTML);
        endif;
    }
}
