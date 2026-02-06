<?php

use Elementor\Core\Editor\Data\Globals\Endpoints\Typography;

class Rkit_advanced_heading extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'rkit_advanced_heading';
    }
    public function get_title()
    {
        return 'Advanced Heading';
    }

    public function get_icon()
    {
        return 'rkit-widget-icon eicon-animated-headline';
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
        return ['rkit-advanced_heading-style'];
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

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'wrap_headtext_background',
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
                ]
            ]
        );

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

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'headtext_background',
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

        echo '<div class="rkit-advanced-heading-wrapper">';
        echo '<' . esc_html($html_tag) . ' class="rkit-advanced-heading">' . $link_start . wp_kses_post($animated_text) . $link_end . '</' . esc_html($html_tag) . '>';
        echo '</div>';
    }
}
