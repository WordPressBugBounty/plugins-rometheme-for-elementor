<?php
namespace RTMKit\Elements\SinglePost;

defined('ABSPATH') || exit;

use WP_Query;

class PostContent extends SinglePostWidget
{
    public static function metadata(): array
    {
        return [
            'id' => 'rtm-post-content',
            'icon' => 'rtmicon rtmicon-post-content',
            'name' => esc_html('Post Content'),
            'widget_name' => 'rtm-post-content',
            'category' => self::CATEGORY,
            'type' => 'free',
            'badge' => esc_html('New'),
            'requires' => [],
            'tags' => ['rtm', 'content', 'post content'],
            'priority' => 10,
            'docsURL' => 'https://support.rometheme.net/docs/romethemekit/widgets/',
            'previewURL' => 'https://support.rometheme.net/docs/romethemekit/widgets/',
            'videoURL' => 'https://support.rometheme.net/docs/romethemekit/widgets/',
            'scripts' => [
                'css' => '',
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
        $this->start_controls_section('content_style', [
            'label' => esc_html('Post Content'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_responsive_control(
            'text_align',
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
                    'justify' => [
                        'title' => esc_html__('Right', 'romethemekitpro'),
                        'icon' => 'eicon-text-align-justify',
                    ]
                ],
                'default' => 'left',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-content' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'content_typography_post_content',
                'selector' => '{{WRAPPER}} .rkit-post-content',
            ]
        );

        $this->add_control('text-color', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-content' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'text_shadow',
                'selector' => '{{WRAPPER}} .rkit-post-content',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'text_stroke',
                'selector' => '{{WRAPPER}} .rkit-post-content',
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        if (defined('ELEMENTOR_PATH') && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
            // Query untuk mendapatkan satu posting secara acak
            $args = [
                'post_type' => 'post', // Ganti dengan custom post type Anda jika diperlukan
                'posts_per_page' => 1,
                'orderby' => 'rand'
            ];

            $random_post_query = new WP_Query($args);

            if ($random_post_query->have_posts()) {
                while ($random_post_query->have_posts()) {
                    $random_post_query->the_post();
                    echo '<div class="rkit-post-content">';
                    the_content();
                    echo '</div>';
                }
                wp_reset_postdata();
            }
        } else {
            $obj = get_queried_object();
            echo '<div class="rkit-post-content">';
            echo get_post_field('post_content', $obj->ID);
            echo '</div>';
        }
    }

}