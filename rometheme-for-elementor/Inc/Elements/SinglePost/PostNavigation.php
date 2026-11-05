<?php

namespace RTMKit\Elements\SinglePost;

defined('ABSPATH') || exit;

use WP_Query;

class PostNavigation extends SinglePostWidget
{
    public static function metadata(): array
    {
        return [
            'id' => 'rkit-post-navigation',
            'icon' => 'rtmicon rtmicon-post-navigation',
            'name' => esc_html('Post Navigation'),
            'widget_name' => 'rkit-post-navigation',
            'category' => self::CATEGORY,
            'type' => 'free',
            'badge' => esc_html('New'),
            'requires' => [],
            'tags' => ['rtm', 'navigation', 'post navigation'],
            'priority' => 10,
            'docsURL' => 'https://support.rometheme.net/docs/romethemekit/widgets/',
            'previewURL' => 'https://support.rometheme.net/docs/romethemekit/widgets/',
            'videoURL' => 'https://support.rometheme.net/docs/romethemekit/widgets/',
            'scripts' => [
                'css' => 'post-navigation.css',
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
        $this->start_controls_section(
            'content_section',
            [
                'label' => esc_html('Post Navigation'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_label',
            [
                'label' => esc_html__('Show Label', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'romethemekitpro'),
                'label_off' => esc_html__('No', 'romethemekitpro'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'prev_text',
            [
                'label' => esc_html('Previous Text'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html('Previous Post'),
                'condition' => [
                    'show_label' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'next_text',
            [
                'label' => esc_html('Next Text'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html('Next Post'),
                'condition' => [
                    'show_label' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'show_title',
            [
                'label' => esc_html__('Post Title', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'romethemekitpro'),
                'label_off' => esc_html__('No', 'romethemekitpro'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_responsive_control('truncate_title', [
            'label' => esc_html__('Title Line Clamp', 'rometheme-for-elementor'),
            'type' => \Elementor\Controls_Manager::NUMBER,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-navigation .rkit-post-navigation__title' => '--rkit-line-clamp-post-nav:{{VALUE}}'
            ],
            'condition' => [
                'show_title' => 'yes'
            ]
        ]);

        $this->add_control(
            'show_divider',
            [
                'label' => esc_html__('Divider', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'romethemekitpro'),
                'label_off' => esc_html__('No', 'romethemekitpro'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'icon_options',
            [
                'label' => esc_html__('Icon', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'show_icon',
            [
                'label' => esc_html__('Show Icon', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'romethemekitpro'),
                'label_off' => esc_html__('No', 'romethemekitpro'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'prev_icon',
            [
                'label' => esc_html__('Previous Icon', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'rtmicon rtmicon-chevron-left',
                    'library' => 'rtmicons',
                ],
                'condition' => [
                    'show_icon' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'next_icon',
            [
                'label' => esc_html__('Next Icon', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'rtmicon rtmicon-chevron-right',
                    'library' => 'rtmicons',
                ],
                'condition' => [
                    'show_icon' => 'yes'
                ]
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('label_style', [
            'label' => esc_html('Label'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => ['show_label' => 'yes']
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'label_typography',
                'selector' => '{{WRAPPER}} .rkit-post-navigation__text',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'label_shadow_normal',
                'selector' => '{{WRAPPER}} .rkit-post-navigation__text',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'label_stroke_normal',
                'selector' => '{{WRAPPER}} .rkit-post-navigation__text',
            ]
        );

        $this->start_controls_tabs('label_tabs');

        $this->start_controls_tab('label_tab_normal', ['label' => esc_html('Normal')]);

        $this->add_control('label_color_normal', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-navigation__text' => 'color:{{VALUE}}'
            ]
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('label_tab_hover', ['label' => esc_html('Hover')]);

        $this->add_control('label_color_hover', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-navigation__link:hover .rkit-post-navigation__text ' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_control(
            'transition_duration_label_pn',
            [
                'label' => esc_html__('Transition Duration (ms)', 'rtmkitpro'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'responsive_control' => true,
                'size_units' => ['ms', 's', 'custom'],
                'default' => [
                    'size' => 300,
                    'unit' => 'ms'
                ],
                'range' => [
                    'ms' => [
                        'min' => 0,
                        'max' => 10000,
                        'step' => 100,
                    ],
                    's' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-navigation__text ' => 'transition-duration: {{SIZE}}{{UNIT}};'
                ]
            ]
        );


        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section('title_style', [
            'label' => esc_html('Title'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [
                'show_title' => 'yes'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .rkit-post-navigation__title',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'title_shadow_normal',
                'selector' => '{{WRAPPER}} .rkit-post-navigation__title',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'title_stroke_normal',
                'selector' => '{{WRAPPER}} .rkit-post-navigation__title',
            ]
        );

        $this->start_controls_tabs('title_tabs');

        $this->start_controls_tab('title_tab_normal', [
            'label' => esc_html('Normal'),
        ]);

        $this->add_control('title_color_normal', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-navigation__title' => 'color:{{VALUE}}'
            ]
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('title_tab_hover', [
            'label' => esc_html('Hover'),
        ]);

        $this->add_control('title_color_hover', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-navigation__link:hover .rkit-post-navigation__title' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_control(
            'transition_duration_title_pn',
            [
                'label' => esc_html__('Transition Duration (ms)', 'rtmkitpro'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'responsive_control' => true,
                'size_units' => ['ms', 's', 'custom'],
                'default' => [
                    'size' => 300,
                    'unit' => 'ms'
                ],
                'range' => [
                    'ms' => [
                        'min' => 0,
                        'max' => 10000,
                        'step' => 100,
                    ],
                    's' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-navigation__title ' => 'transition-duration: {{SIZE}}{{UNIT}};'
                ]
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section('icon_style', [
            'label' => esc_html('Icon'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [
                'show_icon' => 'yes'
            ]
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
                    '{{WRAPPER}} .rkit-post-navigation__icon' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'icon_spacing',
            [
                'label' => esc_html('Spacing'),
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
                    '{{WRAPPER}} .rkit-post-navigation__link' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('icon_tabs');

        $this->start_controls_tab('icon_tab_normal', [
            'label' => esc_html('Normal')
        ]);

        $this->add_control('icon_color_normal', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-navigation__icon' => 'color:{{VALUE}}'
            ]
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab('icon_tab_hover', [
            'label' => esc_html('Hover')
        ]);

        $this->add_control('icon_color_hover', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-navigation__link:hover .rkit-post-navigation__icon' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_control(
            'transition_duration_icon_pn',
            [
                'label' => esc_html__('Transition Duration (ms)', 'rtmkitpro'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'responsive_control' => true,
                'size_units' => ['ms', 's', 'custom'],
                'range' => [
                    'ms' => [
                        'min' => 0,
                        'max' => 10000,
                        'step' => 100,
                    ],
                    's' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'size' => 300,
                    'unit' => 'ms'
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-navigation__icon' => 'transition-duration: {{SIZE}}{{UNIT}};'
                ]
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section('divider_style', [
            'label' => esc_html('Divider'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [
                'show_divider' => 'yes'
            ]
        ]);

        $this->add_control('divider_color', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-navigation__divider' => 'border-right-color:{{VALUE}}'
            ]
        ]);

        $this->add_responsive_control(
            'divider_size',
            [
                'label' => esc_html__('Size', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 5,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-navigation__divider' => 'border-right-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'divider_spacing',
            [
                'label' => esc_html__('Spacing', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 5,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-navigation__divider' => 'margin-block: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }


    function get_previous_post_custom($post_id)
    {
        global $wpdb;

        // Dapatkan post type dari post ID
        $post_type = get_post_type($post_id);

        // Query kustom untuk mendapatkan postingan sebelumnya
        $query = $wpdb->prepare("
        SELECT ID 
        FROM $wpdb->posts
        WHERE post_date < (SELECT post_date FROM $wpdb->posts WHERE ID = %d) 
        AND post_type = %s
        AND post_status = 'publish'
        ORDER BY post_date DESC 
        LIMIT 1
    ", $post_id, $post_type);

        $previous_post_id = $wpdb->get_var($query);

        if ($previous_post_id) {
            return $previous_post_id;
        }

        return null;
    }

    function get_next_post_custom($post_id)
    {
        global $wpdb;

        // Dapatkan post type dari post ID
        $post_type = get_post_type($post_id);

        // Query kustom untuk mendapatkan postingan berikutnya
        $query = $wpdb->prepare("
        SELECT ID 
        FROM $wpdb->posts
        WHERE post_date > (SELECT post_date FROM $wpdb->posts WHERE ID = %d) 
        AND post_type = %s
        AND post_status = 'publish'
        ORDER BY post_date ASC 
        LIMIT 1
    ", $post_id, $post_type);

        $next_post_id = $wpdb->get_var($query);

        if ($next_post_id) {
            return $next_post_id;
        }

        return null;
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
                    $random_post_query->the_post(); // Mengatur data posting global ke posting acak
                    $post_id = get_the_ID();
                }
                wp_reset_postdata();
            }
        } else {
            $obj = get_queried_object();
            $post_id = $obj->ID;
        }

        $nextPostID = $this->get_next_post_custom($post_id);
        $prevPostID = $this->get_previous_post_custom($post_id);
        $prevPost = ($prevPostID != null) ? get_post($prevPostID) : null;
        $nextPost = ($nextPostID != null) ? get_post($nextPostID) : null;
        $nextLink = ($nextPostID != null) ? get_permalink($nextPostID) : '';
        $prevLink = ($prevPostID != null) ? get_permalink($prevPostID) : '';

        // jika mau gunakan word crop
        // $next_title = empty($count_truncate_title) ? $next_title : wp_trim_words($next_title, $count_truncate_title);
        // $prev_title = empty($count_truncate_title) ? $prev_title : wp_trim_words($prev_title, $count_truncate_title);

?>
        <div class="rkit-post-navigation">
            <div class="rkit-previous-navigation">
                <?php if ($prevPostID != null) : ?>
                    <?php $prev_title =  get_the_title($prevPost->ID); ?>
                    <a href="<?php echo esc_url($prevLink) ?>" class="rkit-post-navigation__link">
                        <div class="rkit-post-navigation__icon">
                            <?php \Elementor\Icons_Manager::render_icon($settings['prev_icon'], ['aria-hidden' => 'true']); ?>
                        </div>
                        <div class="rkit-post-navigation__label">
                            <span class="rkit-post-navigation__text"><?php echo esc_html($settings['prev_text']) ?></span>
                            <?php if ($settings['show_title'] === 'yes') : ?>
                                <span class="rkit-post-navigation__title"><?php echo esc_html($prev_title) ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endif; ?>
            </div>
            <?php if ($settings['show_divider'] === 'yes') : ?>
                <div class="rkit-post-navigation__divider"></div>
            <?php endif; ?>
            <div class="rkit-next-navigation">
                <?php if ($nextPostID != null) : ?>
                    <?php $next_title = ($nextPost->ID != null) ? get_the_title($nextPost->ID) : null; ?>
                    <a href="<?php echo esc_url($nextLink) ?>" class="rkit-post-navigation__link">
                        <div class="rkit-post-navigation__label">
                            <span class="rkit-post-navigation__text"><?php echo esc_html($settings['next_text']) ?></span>
                            <?php if ($settings['show_title'] === 'yes') : ?>
                                <span class="rkit-post-navigation__title"><?php echo esc_html($next_title) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="rkit-post-navigation__icon">
                            <?php \Elementor\Icons_Manager::render_icon($settings['next_icon'], ['aria-hidden' => 'true']); ?>
                        </div>
                    </a>
                <?php endif; ?>
            </div>
        </div>
<?php
    }
}
