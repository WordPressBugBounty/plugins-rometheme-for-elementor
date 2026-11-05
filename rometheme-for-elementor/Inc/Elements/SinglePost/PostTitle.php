<?php

namespace RTMKit\Elements\SinglePost;

defined('ABSPATH') || exit;

use WP_Query;

class PostTitle extends SinglePostWidget
{
    public static function metadata(): array
    {
        return [
            'id' => 'rtmpro-post-title',
            'icon' => 'rtmicon rtmicon-post-title',
            'name' => esc_html('Post Title'),
            'widget_name' => 'rtmpro-post-title',
            'category' => self::CATEGORY,
            'type' => 'free',
            'badge' => esc_html('New'),
            'requires' => [],
            'tags' => ['rtm', 'title'],
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
        $this->start_controls_section('content_section', ['label' => esc_html("Title"), 'tab' => \Elementor\Controls_Manager::TAB_CONTENT]);

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

        $this->end_controls_section();

        $this->start_controls_section('style_section', [
            'label' => esc_html('Title'),
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
                ],
                'default' => 'left',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .rtm-post-title' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .rtm-post-title',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'title_shadow',
                'selector' => '{{WRAPPER}} .rtm-post-title',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'title_stroke',
                'selector' => '{{WRAPPER}} .rtm-post-title',
            ]
        );

        $this->add_control('text_blend_mode_post_title', [
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
                '{{WRAPPER}} .rtm-post-title' => 'mix-blend-mode:{{VALUE}}'
            ]
        ]);


        $this->start_controls_tabs(
            'style_tabs_text_color'
        );

        $this->start_controls_tab(
            'style_normal_tab_text_color',
            [
                'label' => esc_html__('Normal', 'romethemekitpro'),
            ]
        );

        $this->add_control('title_color', [
            'label' => esc_html('Text Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rtm-post-title' => 'color: {{VALUE}}'
            ]
        ]);

        $this->end_controls_tab();

        $this->start_controls_tab(
            'style_hover_tab_text_color',
            [
                'label' => esc_html__('Hover', 'romethemekitpro'),
            ]
        );

        $this->add_control('title_color_hover', [
            'label' => esc_html('Text Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rtm-post-title:hover' => 'color: {{VALUE}}'
            ]
        ]);

        $this->add_control('title_transition_duration', [
            'label' => esc_html('Transition Duration'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['ms', 's'],
            'range' => [
                'ms' => [
                    'min' => 0,
                    'max' => 5000,
                    'step' => 100
                ],
                's' => [
                    'min' => 0,
                    'max' => 5,
                    'step' => 0.1
                ],
            ],
            'default' => [
                'unit' => 's',
                'size' => 0.3,
            ],
            'selectors' => [
                '{{WRAPPER}} .rtm-post-title' => 'transition-duration: {{SIZE}}{{UNIT}}'
            ]
        ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

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
                    the_title('<' . $html_tag . ' class="rtm-post-title" >', '</ ' . $html_tag . ' >'); // Menggunakan the_title() untuk menampilkan judul
                }
                wp_reset_postdata();
            }
        } else {
            single_post_title('<' . $html_tag . ' class="rtm-post-title" >', '</ ' . $html_tag . ' >');
        }
    }
}
