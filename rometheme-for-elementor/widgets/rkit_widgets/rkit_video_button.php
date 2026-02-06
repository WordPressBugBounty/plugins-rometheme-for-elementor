<?php

class Rkit_Video_Button extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'rtm_video_button';
    }

    public function get_title()
    {
        return \RomethemeKit\RkitWidgets::listWidgets()['video_button']['name'];
    }

    public function get_icon()
    {
        $icon = 'rkit-widget-icon ' . \RomethemeKit\RkitWidgets::listWidgets()['video_button']['icon'];
        return $icon;
    }

    public function get_keywords()
    {
        return ['rtm', 'animated', 'button', 'video'];
    }

    function get_custom_help_url()
    {
        return \RomethemeKit\RkitWidgets::listWidgets()['video_button']['docsURL'];
    }

    public function get_categories()
    {
        return ['romethemekit_widgets'];
    }

    public function get_style_depends()
    {
        return ['rkit-video-button-style', 'rkit-glightbox-style'];
    }

    public function get_script_depends()
    {
        return ['rkit-glightbox-script', 'rkit-video_button-script'];
    }

    protected function register_controls()
    {

        $this->start_controls_section('section_content_button', [
            'label' => esc_html('Button'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT
        ]);

        $this->add_control(
            'button_content',
            [
                'label' => esc_html__('Button', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'rtmicon rtmicon-play',
                    'library' => 'rtmicons',
                ],
            ]
        );

        $this->add_control(
            'video_type',
            [
                'label' => esc_html__('Video Type', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'youtube',
                'options' => [
                    'self_hosted' => esc_html__('Self Hosted', 'rometheme-for-elementor'),
                    'youtube' => esc_html__('Youtube', 'rometheme-for-elementor'),
                    'vimeo'  => esc_html__('Vimeo', 'rometheme-for-elementor'),
                ]
            ]
        );

        $this->add_control(
            'video',
            [
                'label' => esc_html__('Choose Video File', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'media_types' => ['video'],
                'default' => [
                    'url' => \Elementor\Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'video_type' => 'self_hosted'
                ]
            ]
        );

        $this->add_control(
            'link_youtube',
            [
                'label' => esc_html__('Link Youtube', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('https://youtu.be/j4AC-g6CPxg', 'rometheme-for-elementor'),
                'placeholder' => esc_html__('Paste link here', 'rometheme-for-elementor'),
                'show_label' => true,
                'condition' => [
                    'video_type' => 'youtube'
                ]
            ]
        );

        $this->add_control(
            'link_vimeo',
            [
                'label' => esc_html__('Link Vimeo', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('', 'rometheme-for-elementor'),
                'placeholder' => esc_html__('Paste link here', 'rometheme-for-elementor'),
                'show_label' => true,
                'condition' => [
                    'video_type' => 'vimeo'
                ]
            ]
        );

        $this->add_control(
            'auto_play',
            [
                'label' => esc_html__('Auto Play', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'rometheme-for-elementor'),
                'label_off' => esc_html__('Hide', 'rometheme-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'muted',
            [
                'label' => esc_html__('Mute', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'rometheme-for-elementor'),
                'label_off' => esc_html__('Hide', 'rometheme-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'loop',
            [
                'label' => esc_html__('Loop', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'rometheme-for-elementor'),
                'label_off' => esc_html__('Hide', 'rometheme-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'player_control',
            [
                'label' => esc_html__('Player Control', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'rometheme-for-elementor'),
                'label_off' => esc_html__('Hide', 'rometheme-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('section_content_pulsing', [
            'label' => esc_html('Pulse Effect'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT
        ]);

        $this->add_control(
            'show_animation_pulsing',
            [
                'label' => esc_html__('Show Animation Pulse', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'rometheme-for-elementor'),
                'label_off' => esc_html__('Hide', 'rometheme-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('section_style_container_icon', [
            'label' => esc_html('Button'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_control(
            'style_button_options',
            [
                'label' => esc_html__('Button', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'alignment_video_button',
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
                'default' => 'start',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .rkit-video-button-wrapper' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control('width_background_button', [
            'label' => esc_html__('Width', 'rometheme-for-elementor'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range' => [
                'px' => [
                    'min' => 42,
                    'max' => 425,
                    'step' => 1,
                ]
            ],
            'devices' => ['desktop', 'tablet', 'mobile'],
            'default' => [
                'size' => 90,
                'unit' => 'px'
            ],
            'tablet_default' => [
                'size' => 90,
                'unit' => 'px'
            ],
            'mobile_default' => [
                'size' => 90,
                'unit' => 'px'
            ],
            'selectors' => [
                '{{WRAPPER}} .rkit-animated-icon-container ' => 'width:{{SIZE}}{{UNIT}} ; height: {{SIZE}}{{UNIT}};',
                '{{WRAPPER}} .rkit-video-button-container' => 'width:{{SIZE}}{{UNIT}} ; height: {{SIZE}}{{UNIT}};'
            ],
        ]);


        // $this->start_controls_tabs(
        //     'style_hover_tabs'
        // );

        // $this->start_controls_tab(
        //     'tab_normal',
        //     [
        //         'label' => esc_html__('Normal', 'rometheme-for-elementor'),
        //     ]
        // );

        // $this->end_controls_tab();

        // $this->start_controls_tab(
        //     'tab_hover',
        //     [
        //         'label' => esc_html__('Hover', 'rometheme-for-elementor'),
        //     ]
        // );



        // $this->end_controls_tab();

        // $this->end_controls_tabs();

        $this->add_control(
            'style_icon_options',
            [
                'label' => esc_html__('Icon', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control('width_icon', [
            'label' => esc_html__('Size', 'rometheme-for-elementor'),
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
            'devices' => ['desktop', 'tablet', 'mobile'],
            'default' => [
                'size' => 24,
                'unit' => 'px'
            ],
            'tablet_default' => [
                'size' => 24,
                'unit' => 'px'
            ],
            'mobile_default' => [
                'size' => 24,
                'unit' => 'px'
            ],
            'selectors' => [
                '{{WRAPPER}} .rkit-animated-icon-container .rkit-vb-icon' => 'font-size: {{SIZE}}{{UNIT}}; width:{{SIZE}}{{UNIT}} ; height: {{SIZE}}{{UNIT}};'
            ]
        ]);

        $this->add_control('rotate_icon', [
            'label' => esc_html__('Rotate Icon', 'rometheme-for-elementor'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px'],
            'range' => [
                'px' => [
                    'min' => 0,
                    'max' => 360,
                    'step' => 1,
                ],
            ],
            'selectors' => [
                '{{WRAPPER}} .rkit-animated-icon-container .rkit-vb-icon' => 'rotate:{{SIZE}}deg;'
            ]
        ]);

        $this->start_controls_tabs(
            'style_hover_tabs_icon'
        );

        $this->start_controls_tab(
            'tab_normal_icon',
            [
                'label' => esc_html__('Normal', 'rometheme-for-elementor'),
            ]
        );

        $this->add_control('color_button', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-animated-icon-container .rkit-vb-icon' => 'color:{{VALUE}};fill:{{VALUE}};',
            ],
            'default' => '#161719'
        ]);

        $this->add_control('background_button_icon', [
            'label' => esc_html('Background Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-animated-icon-container' => 'background-color:{{VALUE}};',
            ],
            'default' => '#00cea6'
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'border_icon',
                'selector' => '{{WRAPPER}} .rkit-animated-icon-container',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow',
                'selector' => '{{WRAPPER}} .rkit-animated-icon-container',
            ]
        );

        $this->add_control(
            'box_shadow_note',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<strong>Note:</strong> Enabling the <em>Box Shadow</em> effect is not recommended when the <em>Pulse Effect</em> is active, as it may cause visual conflicts.',
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
            ]
        );


        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_hover_icon',
            [
                'label' => esc_html__('Hover', 'rometheme-for-elementor'),
            ]
        );

        $this->add_control('color_button_hover', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-animated-icon-container:hover .rkit-vb-icon' => 'color:{{VALUE}};fill:{{VALUE}};',
            ],
            'default' => '#161719'
        ]);

        $this->add_control('background_button_icon_hover', [
            'label' => esc_html('Background Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-animated-icon-container:hover' => 'background-color:{{VALUE}};',
            ],
            'default' => '#00cea6'
        ]);

        // $this->add_group_control(
        //     \Elementor\Group_Control_Border::get_type(),
        //     [
        //         'name' => 'border_icon_hover',
        //         'selector' => '{{WRAPPER}} .rkit-animated-icon-container:hover',
        //     ]
        // );

        $this->add_control('border_color_icon_hover', [
            'label' => esc_html__('Border Color', 'rometheme-for-elementor'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-animated-icon-container:hover' => 'border-color: {{VALUE}};',
            ],
            'condition'=>[
                'border_icon_border!'=>''
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow_hover',
                'selector' => '{{WRAPPER}} .rkit-animated-icon-container:hover',
            ]
        );

        $this->add_control(
            'box_shadow_note_hover',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<strong>Note:</strong> Enabling the <em>Box Shadow</em> effect is not recommended when the <em>Pulse Effect</em> is active, as it may cause visual conflicts.',
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-warning',
            ]
        );

        $this->add_control(
            'hover_animation_style',
            [
                'label'   => esc_html__('Hover Animation Style', 'rometheme-for-elementor'),
                'type'    => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    ''              => esc_html__('None', 'rometheme-for-elementor'),
                    'grow'          => esc_html__('Grow', 'rometheme-for-elementor'),
                    'shrink'        => esc_html__('Shrink', 'rometheme-for-elementor'),
                    'pulse'         => esc_html__('Pulse', 'rometheme-for-elementor'),
                    'wobble'        => esc_html__('Wobble', 'rometheme-for-elementor'),
                    'skew'          => esc_html__('Skew', 'rometheme-for-elementor'),
                    'rotate'        => esc_html__('Rotate', 'rometheme-for-elementor'),
                    'slide-up'      => esc_html__('Slide Up', 'rometheme-for-elementor'),
                    'slide-down'    => esc_html__('Slide Down', 'rometheme-for-elementor'),
                    'slide-left'    => esc_html__('Slide Left', 'rometheme-for-elementor'),
                    'slide-right'   => esc_html__('Slide Right', 'rometheme-for-elementor'),
                    'bounce'        => esc_html__('Bounce', 'rometheme-for-elementor'),
                    'flip'          => esc_html__('Flip', 'rometheme-for-elementor'),
                    'flip-x'        => esc_html__('Flip X', 'rometheme-for-elementor'),
                    'flip-y'        => esc_html__('Flip Y', 'rometheme-for-elementor'),
                    'zoom-in'       => esc_html__('Zoom In', 'rometheme-for-elementor'),
                    'zoom-out'      => esc_html__('Zoom Out', 'rometheme-for-elementor'),
                    'float'         => esc_html__('Float', 'rometheme-for-elementor'),
                    'tada'          => esc_html__('Tada', 'rometheme-for-elementor'),
                    'shake'         => esc_html__('Shake', 'rometheme-for-elementor'),
                    'rubber-band'   => esc_html__('Rubber Band', 'rometheme-for-elementor'),
                ],
                'default' => '',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section('section_style_efek', [
            'label' => esc_html('Effect'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [
                'show_animation_pulsing' => 'yes'
            ]
        ]);

        $this->add_control('width_pulse_animated', [
            'label' => esc_html__('Width Animated Pulse', 'rometheme-for-elementor'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px', 'em', 'rem'],
            'range' => [
                'px' => [
                    'min' => 0,
                    'max' => 100,
                    'step' => 1,
                ]
            ],
            'default' => [
                'size' => 32,
                'unit' => 'px'
            ],
            'condition' => [
                'show_animation_pulsing' => 'yes'
            ]
        ]);

        $this->add_control('duration_animation', [
            'label' => esc_html__('Duration Animated Pulse (s)', 'rometheme-for-elementor'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['s'],
            'range' => [
                's' => [
                    'min' => 0,
                    'max' => 5,
                    'step' => 0.5,
                ],
            ],
            'default' => [
                'size' => 1.5,
                'unit' => 's'
            ],
            'condition' => [
                'show_animation_pulsing' => 'yes'
            ]
        ]);

        $this->start_controls_tabs(
            'style_tabs_effects'
        );

        $this->start_controls_tab(
            'style_normal_tab_effects',
            [
                'label' => esc_html__('Normal', 'rometheme-for-elementor'),
            ]
        );

        $this->add_control('background_color_icon_animated_pulse', [
            'label' => esc_html('Background Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#00CEA699',
            'selectors' => [
                '{{WRAPPER}} .rkit-animated-icon-container' => '--pulse-color:{{VALUE}};',
            ],
            'condition' => [
                'show_animation_pulsing' => 'yes'
            ]

        ]);

        $this->end_controls_tab();

        $this->start_controls_tab(
            'style_hover_tab_effects',
            [
                'label' => esc_html__('Hover', 'rometheme-for-elementor'),
            ]
        );

        $this->add_control('background_color_icon_animated_pulse_hover', [
            'label' => esc_html('Background Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '#00CEA699',
            'selectors' => [
                '{{WRAPPER}} .rkit-animated-icon-container:hover' => '--pulse-color:{{VALUE}};',
            ],
            'condition' => [
                'show_animation_pulsing' => 'yes'
            ]

        ]);

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $showAnimationBackgroundIcon = isset($settings['show_animation_pulsing']) && $settings['show_animation_pulsing'] === 'yes' ? 'rkit-animation-shadowPulse' : '';
        $linkButton = ''; // Initialize with an empty string

        if ($settings['video_type'] === 'self_hosted') {
            $linkButton = $settings['video']['url'];
        } elseif ($settings['video_type'] === 'youtube') {
            $linkButton = $settings['link_youtube'];
        } elseif ($settings['video_type'] === 'vimeo') {
            $linkButton = $settings['link_vimeo'];
        }

        $pulseWidth = ($settings['width_pulse_animated']['size'] ?? 0) . ($settings['width_pulse_animated']['unit'] ?? 'px');
        // $bgIconColor = $settings['background_color_icon_animated_pulse'];
        $durationAnimation =  isset($settings['duration_animation']['size']) ? $settings['duration_animation']['size'] . 's' : '1.5s';
        $hoverAnimation = $settings['hover_animation_style'];
        $autoPlay = $settings['auto_play'] === 'yes';
        $muted = $settings['muted'] === 'yes';
        $loop = $settings['loop'] === 'yes';
        $playerControl = $settings['player_control'] === 'yes';
        $poster = !empty($settings['image_poster']['url']) ? $settings['image_poster']['url'] : '';
?>
        <div class="rkit-video-button-wrapper">
            <div class="rkit-video-button-container <?= $hoverAnimation ?> ">
                <svg viewBox="0 0 100 100" class="rkit-video-button " width="100%" height=" 100%">
                    <defs>
                        <path id="circlePath" d="M50,50 m-35,0 a35,35 0 1,1 70,0 a35,35 0 1,1 -70,0" />
                    </defs>
                    <text class="rkit-animated-text">
                    </text>
                </svg>
                <a class="rkit-animated-icon-container glightbox <?= $showAnimationBackgroundIcon ?>"
                    style="--pulse-width:<?= $pulseWidth ?>; --duration-pulse:<?= $durationAnimation ?>;"
                    href="<?= esc_url($linkButton) ?>"
                    id="video-button"
                    data-type="video"
                    data-autoplay="<?= esc_attr($autoPlay ? 'yes' : 'no') ?>"
                    data-muted="<?= esc_attr($muted ? 'yes' : 'no') ?>"
                    data-loop="<?= esc_attr($loop ? 'yes' : 'no') ?>"
                    data-player-control="<?= esc_attr($playerControl ? 'yes' : 'no') ?>">
                    <?php
                    \Elementor\Icons_Manager::render_icon($settings['button_content'], [
                        'aria-hidden' => 'true',
                        'class' => 'rkit-vb-icon'
                    ]);
                    ?>
                </a>
            </div>
        </div>
<?php
    }
}
