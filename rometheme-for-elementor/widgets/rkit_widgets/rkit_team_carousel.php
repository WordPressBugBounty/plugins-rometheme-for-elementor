<?php

class Rkit_Team_Carousel extends \Elementor\Widget_Base
{
    protected function getWidget()
    {
        return \RomethemeKit\RkitWidgets::listWidgets()['team_carousel'];
    }
    public function get_name()
    {
        return 'rtm-team_carousel';
    }
    public function get_title()
    {
        return $this->getWidget()['name'];
    }
    public function get_keywords()
    {
        return ['rtm', 'team', 'carousel'];
    }
    public function get_icon()
    {
        $icon = 'rkit-widget-icon ' . $this->getWidget()['icon'];
        return $icon;
    }

    function get_custom_help_url()
    {
        return  $this->getWidget()['docsURL'];
    }

    public function get_style_depends()
    {
        return ['rkit-team-carousel-style'];
    }
    public function get_categories()
    {
        return ['romethemekit_widgets'];
    }
    public function get_script_depends()
    {
        return ['rkit-team-carousel-script'];
    }
    protected function register_controls()
    {
        $this->start_controls_section('member_content_section', [
            'label' => esc_html__('Member Content', 'rometheme-for-elementor'),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $repeaterMemberContent = new \Elementor\Repeater();

        // === STYLE SETTINGS ===
        $repeaterMemberContent->add_control('select_style', [
            'label'   => esc_html__('Style', 'rometheme-for-elementor'),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'default'        => esc_html__('Default', 'rometheme-for-elementor'),
                'social_on_hover' => esc_html__('Social on Hover', 'rometheme-for-elementor'),
                'overlay'        => esc_html__('Overlay', 'rometheme-for-elementor'),
                'centered'       => esc_html__('Centered', 'rometheme-for-elementor'),
            ],
            'default' => 'default',
        ]);

        $repeaterMemberContent->add_control('pointer_effect', [
            'label'        => esc_html__('Pointer Effect', 'rometheme-for-elementor'),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'label_on'     => esc_html__('Yes', 'rometheme-for-elementor'),
            'label_off'    => esc_html__('No', 'rometheme-for-elementor'),
            'return_value' => 'pointer',
            'default'      => '',
        ]);

        $repeaterMemberContent->add_control('name_tag', [
            'label'   => esc_html__('HTML Tag', 'rometheme-for-elementor'),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'h1' => 'H1',
                'h2' => 'H2',
                'h3' => 'H3',
                'h4' => 'H4',
                'h5' => 'H5',
                'h6' => 'H6',
            ],
            'default' => 'h4',
        ]);

        $repeaterMemberContent->add_control('member_name', [
            'label'       => esc_html__('Member Name', 'rometheme-for-elementor'),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'default'     => esc_html__('Jon Doe', 'rometheme-for-elementor'),
        ]);

        $repeaterMemberContent->add_control('member_position', [
            'label'       => esc_html__('Job Title', 'rometheme-for-elementor'),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'default'     => esc_html__('Developer', 'rometheme-for-elementor'),
        ]);

        $repeaterMemberContent->add_control('member_description', [
            'label'       => esc_html__('Description', 'rometheme-for-elementor'),
            'type'        => \Elementor\Controls_Manager::TEXTAREA,
            'default'     => esc_html__('Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'rometheme-for-elementor'),
        ]);

        $repeaterMemberContent->add_control('member_image', [
            'label'   => esc_html__('Choose Member Image', 'rometheme-for-elementor'),
            'type'    => \Elementor\Controls_Manager::MEDIA,
            'default' => ['url' => \Elementor\Utils::get_placeholder_image_src()],
        ]);

        $repeaterMemberContent->add_group_control(
            \Elementor\Group_Control_Image_Size::get_type(),
            [
                'name'    => 'thumbnail',
                'default' => 'large',
                'exclude' => ['custom'],
            ]
        );

        // === SOCIAL MEDIA ===
        $repeaterMemberContent->add_control(
            'social_media_divider',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ]
        );

        $repeaterMemberContent->add_control('social_media_heading', [
            'label' => esc_html__('Social Media', 'rometheme-for-elementor'),
            'type'  => \Elementor\Controls_Manager::HEADING,
        ]);

        $repeaterMemberContent->add_control('social_media_select', [
            'label'    => esc_html__('Select Platforms', 'rometheme-for-elementor'),
            'type'     => \Elementor\Controls_Manager::SELECT2,
            'multiple' => true,
            'options'  => [
                'facebook'  => esc_html__('Facebook', 'rometheme-for-elementor'),
                'twitter'   => esc_html__('X / Twitter', 'rometheme-for-elementor'),
                'instagram' => esc_html__('Instagram', 'rometheme-for-elementor'),
                'tiktok'    => esc_html__('TikTok', 'rometheme-for-elementor'),
                'youtube'   => esc_html__('YouTube', 'rometheme-for-elementor'),
                'linkedin'  => esc_html__('LinkedIn', 'rometheme-for-elementor'),
                'github'    => esc_html__('GitHub', 'rometheme-for-elementor'),
                'behance'   => esc_html__('Behance', 'rometheme-for-elementor'),
                'dribbble'  => esc_html__('Dribbble', 'rometheme-for-elementor'),
            ],
            'default' => ['facebook', 'instagram'],
        ]);

        // === AUTO GENERATE ICON + URL FOR EACH SELECTED PLATFORM ===
        $social_platforms = [
            'facebook' => [
                'label' => 'Facebook',
                'icon' => ['value' => 'fab fa-facebook-f', 'library' => 'fa-brands'],
                'url'  => 'https://facebook.com',
            ],
            'twitter' => [
                'label' => 'Twitter / X',
                'icon' => ['value' => 'fab fa-x-twitter', 'library' => 'fa-brands'],
                'url'  => 'https://twitter.com',
            ],
            'instagram' => [
                'label' => 'Instagram',
                'icon' => ['value' => 'fab fa-instagram', 'library' => 'fa-brands'],
                'url'  => 'https://instagram.com',
            ],
            'tiktok' => [
                'label' => 'TikTok',
                'icon' => ['value' => 'fab fa-tiktok', 'library' => 'fa-brands'],
                'url'  => 'https://tiktok.com',
            ],
            'youtube' => [
                'label' => 'YouTube',
                'icon' => ['value' => 'fab fa-youtube', 'library' => 'fa-brands'],
                'url'  => 'https://youtube.com',
            ],
            'linkedin' => [
                'label' => 'LinkedIn',
                'icon' => ['value' => 'fab fa-linkedin-in', 'library' => 'fa-brands'],
                'url'  => 'https://linkedin.com',
            ],
            'github' => [
                'label' => 'GitHub',
                'icon' => ['value' => 'fab fa-github', 'library' => 'fa-brands'],
                'url'  => 'https://github.com',
            ],
            'behance' => [
                'label' => 'Behance',
                'icon' => ['value' => 'fab fa-behance', 'library' => 'fa-brands'],
                'url'  => 'https://behance.net',
            ],
            'dribbble' => [
                'label' => 'Dribbble',
                'icon' => ['value' => 'fab fa-dribbble', 'library' => 'fa-brands'],
                'url'  => 'https://dribbble.com',
            ],
        ];

        foreach ($social_platforms as $key => $data) {

            $repeaterMemberContent->add_control("{$key}_heading", [
                'label' => esc_html__($data['label'], 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'condition' => ['social_media_select' => $key],
            ]);

            $repeaterMemberContent->add_control("{$key}_icon", [
                'label' => sprintf(esc_html__('%s Icon', 'rometheme-for-elementor'), $data['label']),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => $data['icon'],
                'condition' => ['social_media_select' => $key],
            ]);

            $repeaterMemberContent->add_control("{$key}_url", [
                'label' => sprintf(esc_html__('%s URL', 'rometheme-for-elementor'), $data['label']),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_url($data['url']),
                'default' => ['url' => esc_url($data['url'])],
                'condition' => ['social_media_select' => $key],
            ]);

            $repeaterMemberContent->add_control("{$key}_divider", [
                'type' => \Elementor\Controls_Manager::DIVIDER,
                'condition' => ['social_media_select' => $key],
            ]);
        }

        // === ADD REPEATER TO MAIN CONTROL ===
        $this->add_control('list_member_content', [
            'label'       => esc_html__('Member Content', 'rometheme-for-elementor'),
            'type'        => \Elementor\Controls_Manager::REPEATER,
            'fields'      => $repeaterMemberContent->get_controls(),
            'title_field' => '{{{ member_name }}}',
            'default' => [
                [
                    'member_name' => esc_html__('Jon Doe', 'rometheme-for-elementor'),
                    'member_position' => esc_html__('Developer', 'rometheme-for-elementor'),
                    'member_description' => esc_html__('Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'rometheme-for-elementor'),
                    'member_image' => ['url' => \Elementor\Utils::get_placeholder_image_src()],
                    'select_style' => 'default',
                    'pointer_effect' => '',
                    'name_tag' => 'h4',
                    'social_media_select' => ['facebook', 'instagram'],
                    'facebook_icon' => ['value' => 'fab fa-facebook-f', 'library' => 'fa-brands'],
                    'facebook_url' => ['url' => 'https://facebook.com'],
                    'instagram_icon' => ['value' => 'fab fa-instagram', 'library' => 'fa-brands'],
                    'instagram_url' => ['url' => 'https://instagram.com'],
                ],
                [
                    'member_name' => esc_html__('Maria Doe', 'rometheme-for-elementor'),
                    'member_position' => esc_html__('Designer', 'rometheme-for-elementor'),
                    'member_description' => esc_html__('Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'rometheme-for-elementor'),
                    'member_image' => ['url' => \Elementor\Utils::get_placeholder_image_src()],
                    'select_style' => 'default',
                    'pointer_effect' => '',
                    'name_tag' => 'h4',
                    'social_media_select' => ['facebook', 'instagram'],
                    'facebook_icon' => ['value' => 'fab fa-facebook-f', 'library' => 'fa-brands'],
                    'facebook_url' => ['url' => 'https://facebook.com'],
                    'instagram_icon' => ['value' => 'fab fa-instagram', 'library' => 'fa-brands'],
                    'instagram_url' => ['url' => 'https://instagram.com'],
                ],
                [
                    'member_name' => esc_html__('John Doe', 'rometheme-for-elementor'),
                    'member_position' => esc_html__('Developer', 'rometheme-for-elementor'),
                    'member_description' => esc_html__('Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'rometheme-for-elementor'),
                    'member_image' => ['url' => \Elementor\Utils::get_placeholder_image_src()],
                    'select_style' => 'default',
                    'pointer_effect' => '',
                    'name_tag' => 'h4',
                    'social_media_select' => ['facebook', 'instagram'],
                    'facebook_icon' => ['value' => 'fab fa-facebook-f', 'library' => 'fa-brands'],
                    'facebook_url' => ['url' => 'https://facebook.com'],
                    'instagram_icon' => ['value' => 'fab fa-instagram', 'library' => 'fa-brands'],
                    'instagram_url' => ['url' => 'https://instagram.com'],
                ],
                [
                    'member_name' => esc_html__('Jane Doe', 'rometheme-for-elementor'),
                    'member_position' => esc_html__('Designer', 'rometheme-for-elementor'),
                    'member_description' => esc_html__('Lorem ipsum dolor sit amet, consectetur adipiscing elit.', 'rometheme-for-elementor'),
                    'member_image' => ['url' => \Elementor\Utils::get_placeholder_image_src()],
                    'select_style' => 'default',
                    'pointer_effect' => '',
                    'name_tag' => 'h4',
                    'social_media_select' => ['facebook', 'instagram'],
                    'facebook_icon' => ['value' => 'fab fa-facebook-f', 'library' => 'fa-brands'],
                    'facebook_url' => ['url' => 'https://facebook.com'],
                    'instagram_icon' => ['value' => 'fab fa-instagram', 'library' => 'fa-brands'],
                    'instagram_url' => ['url' => 'https://instagram.com'],
                ],
            ]
        ]);

        $this->end_controls_section();

        $this->start_controls_section('navigation_settings_team_carousel', [
            'label' => esc_html('Navigation'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control(
            'show_navigation_team_carousel',
            [
                'label' => esc_html__('Show Navigation', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'rometheme-for-elementor'),
                'label_off' => esc_html__('no', 'rometheme-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        // show animation swithcer control
        $this->add_control('show_hover_animation_team_carousel', [
            'label' => esc_html('Show Animation', 'rometheme-for-elementor'),
            'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => esc_html('Yes', 'rometheme-for-elementor'),
            'label_off' => esc_html('No', 'rometheme-for-elementor'),
            'return_value' => 'yes',
            'default' => 'yes',
            'condition' => [
                'show_navigation_team_carousel' => 'yes'
            ]
        ]);

        $this->add_control(
            'next_icon_team_carousel',
            [
                'label' => esc_html__('Next Icon', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'rtmicon rtmicon-chevron-right',
                    'library' => 'rtmicons',
                ],
                'condition' => [
                    'show_navigation_team_carousel' => 'yes'
                ]
            ]
        );

        $this->add_control(
            'prev_icon_team_carousel',
            [
                'label' => esc_html__('Prev Icon', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'rtmicon rtmicon-chevron-left',
                    'library' => 'rtmicons',
                ],
                'condition' => [
                    'show_navigation_team_carousel' => 'yes'
                ]
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('setttings_section', ['label' => esc_html('Settings'), 'tab' => \Elementor\Controls_Manager::TAB_CONTENT]);

        $this->add_responsive_control(
            'spacebetween',
            [
                'type' => \Elementor\Controls_Manager::SLIDER,
                'label' => esc_html__('Spacing', 'rometheme-for-elementor'),
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'devices' => ['desktop', 'tablet', 'mobile'],
                'default' => [
                    'size' => 30,
                    'unit' => 'px',
                ],
                'tablet_default' => [
                    'size' => 10,
                    'unit' => 'px',
                ],
                'mobile_default' => [
                    'size' => 10,
                    'unit' => 'px',
                ],
            ]
        );

        $this->add_responsive_control(
            'slide_to_show',
            [
                'label' => esc_html__('Slide To Show', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 100,
                'step' => 1,
                'desktop_default' => 3,
                'tablet_default' => 2,
                'mobile_default' => 1
            ]
        );

        $this->add_responsive_control(
            'slide_to_scroll',
            [
                'label' => esc_html__('Slide To Scroll', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 100,
                'step' => 1,
                'desktop_default' => 1,
                'tablet_default' => 1,
                'mobile_default' => 1
            ]
        );

        $this->add_control('speed', [
            'label' => esc_html('Speed'),
            'type' => \Elementor\Controls_Manager::NUMBER,
            'min' => 100,
            'max' => 5000,
            'step' => 100,
            'default' => 1000
        ]);

        $this->add_control(
            'autoplay',
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
            'show_dots',
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
            'pause_on_hover',
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
            'loop',
            [
                'label' => esc_html__('Loop', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'rometheme-for-elementor'),
                'label_off' => esc_html__('No', 'rometheme-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();


        $this->start_controls_section('wrapper_style', [
            'label' => esc_html('Wrapper'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_responsive_control(
            'pointer_size',
            [
                'label' => esc_html__('Pointer Size', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px',  'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 2,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-team-card.pointer' => '--pointer-size: {{SIZE}}{{UNIT}};',
                ],
                'description' => esc_html__('Visible only when Pointer Effect is enabled.', 'rometheme-for-elementor'),
                // 'condition' => [
                //     'pointer_effect' => 'pointer'
                // ]
            ]
        );

        $this->add_responsive_control(
            'box_padding',
            [
                'label' => esc_html__('Padding', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-team-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'description' => esc_html__('This setting is only available when Style is not set to Overlay.', 'rometheme-for-elementor'),
                // 'condition' => [
                //     'select_style!' => 'overlay'
                // ]
            ]
        );

        $this->start_controls_tabs('box_tabs');

        $this->start_controls_tab('box_tab_normal', ['label' => esc_html('Normal')]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'background_normal',
                'types' => ['classic', 'gradient', 'video'],
                'selector' => '{{WRAPPER}} .rkit-team-card',
                'description' => esc_html__('This setting is only available when Style is not set to Overlay.', 'rometheme-for-elementor'),
                // 'condition' => [
                //     'select_style!' => 'overlay'
                // ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'card_border',
                'selector' => '{{WRAPPER}} .rkit-team-card',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow_normal',
                'selector' => '{{WRAPPER}} .rkit-team-card',
            ]
        );

        $this->add_control(
            'pointer_options_normal',
            [
                'label' => esc_html__('Pointer', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                // 'condition' => [
                //     'pointer_effect' => 'pointer'
                // ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'pointer_background_normal',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-team-card.pointer::before',
                'fields_options' => [
                    'color' => [
                        'default' => '#00cea6'
                    ],
                    'background' => [
                        'default' => 'classic'
                    ]
                ],
                'description' => esc_html__('Visible only when Pointer Effect is enabled.', 'rometheme-for-elementor'),
                // 'condition' => [
                //     'pointer_effect' => 'pointer'
                // ]
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('box_tab_hover', ['label' => esc_html('Hover')]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'background_hover',
                'types' => ['classic', 'gradient', 'video'],
                'selector' => '{{WRAPPER}} .rkit-team-card:hover',
                'description' => esc_html__('This setting is only available when Style is not set to Overlay.', 'rometheme-for-elementor'),
                // 'condition' => [
                //     'select_style!' => 'overlay'
                // ]
            ]
        );

        $this->add_control(
            'card_border_hover_color',
            [
                'label' => esc_html__('Border Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-team-card:hover' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'card_border_border!' => ''
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow_hover',
                'selector' => '{{WRAPPER}} .rkit-team-card:hover',
            ]
        );

        $this->add_control(
            'overlay_bg_options',
            [
                'label' => esc_html__('Overlay', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                'description' => esc_html__('This setting is only available when Style is set to Overlay.', 'rometheme-for-elementor'),
                // 'condition' => [
                //     'select_style' => 'overlay'
                // ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'background_overlay',
                'types' => ['classic', 'gradient', 'video'],
                'selector' => '{{WRAPPER}} .rkit-team__overlay .rkit-team__detail::after',
                'description' => esc_html__('This setting is only available when Style is set to Overlay.', 'rometheme-for-elementor'),
                // 'condition' => [
                //     'select_style' => 'overlay'
                // ],
            ]
        );

        $this->add_responsive_control(
            'overlay_opacity',
            [
                'label' => esc_html__('Opacity', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                // 'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1,
                        'step' => 0.1,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-team__overlay .rkit-team__detail::after' => 'opacity: {{SIZE}};',
                ],
                'description' => esc_html__('This setting is only available when Style is set to Overlay.', 'rometheme-for-elementor'),
                // 'condition' => [
                //     'select_style' => 'overlay'
                // ],
            ]
        );

        $this->add_control(
            'pointer_options_hover',
            [
                'label' => esc_html__('Pointer', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                // 'condition' => [
                //     'pointer_effect' => 'pointer'
                // ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'pointer_background_hover',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-team-card.pointer::after',
                'fields_options' => [
                    'color' => [
                        'default' => '#F0ABFC'
                    ],
                    'background' => [
                        'default' => 'classic'
                    ]
                ],
                'description' => esc_html__('This setting is only available when Pointer Effect is set to enabled.', 'rometheme-for-elementor'),
                // 'condition' => [
                //     'pointer_effect' => 'pointer'
                // ]
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        // divider
        $this->add_control(
            'box_tab_hr',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER
            ]
        );

        $this->add_responsive_control(
            'card_border_radius',
            [
                'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-team-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();


        $this->start_controls_section('image_style', [
            'label' => esc_html('Member Image'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_responsive_control('profile_image_ratio', [
            'label' => esc_html('Member Image Ratio'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'auto' => esc_html('Auto'),
                '1/1' => esc_html('1/1'),
                '3/2' => esc_html('3/2'),
                '5/4' => esc_html('5/4'),
                '16/9' => esc_html('16/9'),
                '9/16' => esc_html('9/16'),
                '4/5' => esc_html('4/5'),
                '2/3' => esc_html('2/3'),
            ],
            // 'default' => 'auto',
            'selectors' => [
                '{{WRAPPER}} .rkit-team__img img' => 'aspect-ratio:{{VALUE}}'
            ],
            'description' => esc_html__('This setting is only available when Style is not set to Centered.', 'rometheme-for-elementor'),
            // 'condition' => [
            //     'select_style!' => 'centered'
            // ]
        ]);

        $this->add_control('image_hover_effect', [
            'label' => esc_html('Hover Effect'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                '' => esc_html('None'),
                'zoom-in' => esc_html('Zoom In'),
                'zoom-out' => esc_html('Zoom Out'),
                'move-left' => esc_html('Move Left'),
                'move-right' => esc_html('Move Right'),
                'move-up' => esc_html('Move Up'),
                'move-down' => esc_html('Move Down'),
            ],
            'default' => 'zoom-in'
        ]);

        $this->add_responsive_control(
            'profile_image_width',
            [
                'label' => esc_html__('Image Width', 'rometheme-for-elementor'),
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
                'default' => [
                    'unit' => '%',
                    'size' => 50,
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-team__centered .rkit-team__img' => 'width: {{SIZE}}{{UNIT}};',
                ],
                'description' => esc_html__('This setting is only available when Style is set to centered.', 'rometheme-for-elementor'),
                // 'condition' => [
                //     'select_style' => 'centered'
                // ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'image_border',
                'selector' => '{{WRAPPER}} .rkit-team__img',
            ]
        );

        $this->add_responsive_control(
            'image_radius',
            [
                'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-team__img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('content_style', [
            'label' => esc_html('Content'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_responsive_control(
            'content_align',
            [
                'label' => esc_html__('Alignment', 'rometheme-for-elementor'),
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
                'default' => 'center',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .rkit-team__detail' => 'text-align: {{VALUE}}; align-items:{{VALUE}}',
                    '{{WRAPPER}} .rkit-team__social' => 'justify-content:{{VALUE}}',
                ],
                'description' => esc_html__('This setting is only available when Style is not set to centered.', 'rometheme-for-elementor'),
                // 'condition' => [
                //     'select_style!' => 'centered'
                // ]
            ]
        );

        $this->add_responsive_control(
            'content_padding',
            [
                'label' => esc_html__('Padding', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-team__detail' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'content_margin',
            [
                'label' => esc_html__('Margin', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-team__detail' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'description' => esc_html__('This setting is only available when Style is not set to Overlay.', 'rometheme-for-elementor'),
                // 'condition' => [
                //     'select_style!' => 'overlay'
                // ]
            ]
        );

        // $this->add_control(
        //     'content_tab_hr',
        //     [
        //         'type' => \Elementor\Controls_Manager::DIVIDER,
        //     ]
        // );

        $this->start_controls_tabs('content_tabs');

        $this->start_controls_tab('name_tabs', ['label' => esc_html('Name')]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'name_typography',
                'selector' => '{{WRAPPER}} .rkit-team__name',
            ]
        );

        $this->add_responsive_control(
            'name_spacing',
            [
                'label' => esc_html__('Spacing', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 2,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-team__name' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('role_tabs', ['label' => esc_html('Job Title')]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'role_typography',
                'selector' => '{{WRAPPER}} .rkit-team__role',
            ]
        );

        $this->add_responsive_control(
            'role_spacing',
            [
                'label' => esc_html__('Spacing', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 2,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-team__role' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('description_tabs', [
            'label' => esc_html('Description'),
            // 'condition' => [
            //     'member_description!' => ''
            // ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'description_typography',
                'selector' => '{{WRAPPER}} .rkit-team__description',
                // 'condition' => [
                //     'member_description!' => ''
                // ]
            ]
        );

        $this->add_responsive_control(
            'description_spacing',
            [
                'label' => esc_html__('Spacing', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 2,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-team__description' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
                // 'condition' => [
                //     'member_description!' => ''
                // ]
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'content_tab_close_hr',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ]
        );

        $this->start_controls_tabs('content_color_tabs');

        $this->start_controls_tab('content_tab_normal', ['label' => esc_html('Normal')]);

        $this->add_control('name_color_normal', [
            'label' => esc_html('Name Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-team__name' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_control('role_color_normal', [
            'label' => esc_html('Member Position Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-team__role' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_control('description_color_normal', [
            'label' => esc_html('Description Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-team__description' => 'color:{{VALUE}}'
            ],
            // 'condition' => [
            //     'member_description!' => ''
            // ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'content_background_normal',
                'types' => ['classic', 'gradient', 'video'],
                'selector' => '{{WRAPPER}} .rkit-team__detail',
                'description' => esc_html__('This setting is only available when Style is not set to Overlay.', 'rometheme-for-elementor'),
                // 'condition' => [
                //     'select_style!' => 'overlay'
                // ]
            ]
        );


        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'content_border',
                'selector' => '{{WRAPPER}} .rkit-team__detail',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('content_tab_hover', ['label' => esc_html('Hover')]);

        $this->add_control('name_color_hover', [
            'label' => esc_html('Name Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-team-card:hover .rkit-team__name' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_control('role_color_hover', [
            'label' => esc_html('Member Position Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-team-card:hover .rkit-team__role' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_control('description_color_hover', [
            'label' => esc_html('Description Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-team-card:hover .rkit-team__description' => 'color:{{VALUE}}'
            ],
            // 'condition' => [
            //     'member_description!' => ''
            // ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'content_background_hover',
                'types' => ['classic', 'gradient', 'video'],
                'selector' => '{{WRAPPER}} .rkit-team-card:hover .rkit-team__detail',
                'description' => esc_html__('This setting is only available when Style is not set to Overlay.', 'rometheme-for-elementor'),
                // 'condition' => [
                //     'select_style!' => 'overlay'
                // ]
            ]
        );

        // border hover color
        $this->add_control(
            'content_border_hover_color',
            [
                'label' => esc_html__('Border Color', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .rkit-team-card:hover .rkit-team__detail' => 'border-color: {{VALUE}};',
                ],
                'description' => esc_html__('This setting is only available when Style is not set to Overlay.', 'rometheme-for-elementor'),
                'condition' => [
                    'content_border_border!' => '',
                //     'select_style!' => 'overlay'
                ]
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'content_tab_hr',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
                // 'condition' => [
                //     'select_style!' => 'overlay'
                // ]
            ]
        );

        $this->add_responsive_control(
            'content_radius',
            [
                'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-team__detail' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'description' => esc_html__('This setting is only available when Style is not set to Overlay.', 'rometheme-for-elementor'),
                // 'condition' => [
                //     'select_style!' => 'overlay'
                // ]
            ]
        );


        $this->end_controls_section();

        $this->start_controls_section('social_media_style', [
            'label' => esc_html('Social Media'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_responsive_control(
            'icon_size',
            [
                'label' => esc_html__('Icon Size', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'size' => 18,
                    'unit' => 'px'
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-team__social_icon' => 'font-size: {{SIZE}}{{UNIT}}; width:{{SIZE}}{{UNIT}};height:{{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'socmed_spacing',
            [
                'label' => esc_html__('Spacing', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 2,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-team__social' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'horizontal_social_align',
            [
                'label' => esc_html__('Social Alignment', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'rometheme-for-elementor'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'rometheme-for-elementor'),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'rometheme-for-elementor'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'default' => 'center',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .rkit-team__social' => 'justify-content: {{VALUE}};',
                ],
                'description' => esc_html__('This style setting only applies when the social media style is set to "On Hover".', 'rometheme-for-elementor'),
                'condition' => [
                    // 'select_style' => 'social_on_hover',
                    'social_media_position' => ['top', 'bottom']
                ]
            ]
        );

        $this->add_control(
            'social_media_position',
            [
                'label' => esc_html__('Social Media Position', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'rometheme-for-elementor'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'rometheme-for-elementor'),
                        'icon' => 'eicon-h-align-right',
                    ],
                    'top' => [
                        'title' => esc_html__('Top', 'rometheme-for-elementor'),
                        'icon' => 'eicon-v-align-top',
                    ],
                    'bottom' => [
                        'title' => esc_html__('Bottom', 'rometheme-for-elementor'),
                        'icon' => ' eicon-v-align-bottom',
                    ],

                ],
                'default' => 'left',
                'toggle' => true,
                'description' => esc_html__('This style setting only applies when the social media style is set to "On Hover".', 'rometheme-for-elementor'),
                // 'condition' => [
                //     'select_style' => 'social_on_hover',
                // ]
            ]
        );

        $this->add_control('socmed_color_select', [
            'label' => esc_html('Social Media Color'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'official' => esc_html('Official'),
                'custom' => esc_html('Custom')
            ],
            'default' => 'official'
        ]);

        $this->add_control('social_media_bg', [
            'label' => esc_html('Background Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-team__social' => 'background-color:{{VALUE}}'
            ],
            'default' => esc_html__('This style setting only applies when the social media style is set to "On Hover".', 'rometheme-for-elementor'),
            'condition' => [
                // 'select_style' => 'social_on_hover',
                'social_media_position' => ['top', 'bottom']
            ]
        ]);

        $this->add_responsive_control(
            'socmed_border_radius',
            [
                'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-team__social_item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'socmed_padding',
            [
                'label' => esc_html__('Padding', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-team__social_item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'socmed_margin',
            [
                'label' => esc_html__('Margin', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-team__social' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('social_media_tabs', ['condition' => ['socmed_color_select' => 'custom']]);

        $this->start_controls_tab('socmed_tab_normal', [
            'label' => esc_html('Normal')
        ]);

        $this->add_control('social_color_normal', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-team__social_icon' => 'color:{{VALUE}} ; fill:{{VALUE}}'
            ],
            'condition' => [
                'socmed_color_select' => 'custom'
            ]
        ]);
        $this->add_control('social_bg_normal', [
            'label' => esc_html('Background Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-team__social_item' => 'background-color:{{VALUE}}'
            ],
            'condition' => [
                'socmed_color_select' => 'custom'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'social_text_shadow_normal',
                'selector' => '{{WRAPPER}} .rkit-team__social_item',
                'condition' => [
                    'socmed_color_select' => 'custom'
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'social_border_normal',
                'selector' => '{{WRAPPER}} .rkit-team__social_item',
                'condition' => [
                    'socmed_color_select' => 'custom'
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'social_shadow_normal',
                'selector' => '{{WRAPPER}} .rkit-team__social_item',
                'condition' => [
                    'socmed_color_select' => 'custom'
                ]
            ]
        );


        $this->end_controls_tab();

        $this->start_controls_tab('socmed_tab_hover', ['label' => esc_html('Hover')]);

        $this->add_control('social_color_hover', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-team__social_item:hover .rkit-team__social_icon' => 'color:{{VALUE}} ; fill:{{VALUE}}'
            ],
            'condition' => [
                'socmed_color_select' => 'custom'
            ]
        ]);

        $this->add_control('social_bg_hover', [
            'label' => esc_html('Background Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-team__social_item:hover' => 'background-color:{{VALUE}}'
            ],
            'condition' => [
                'socmed_color_select' => 'custom'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'social_text_shadow_hover',
                'selector' => '{{WRAPPER}} .rkit-team__social_item:hover',
                'condition' => [
                    'socmed_color_select' => 'custom'
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'social_border_hover',
                'selector' => '{{WRAPPER}} .rkit-team__social_item:hover',
                'condition' => [
                    'socmed_color_select' => 'custom'
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'social_shadow_hover',
                'selector' => '{{WRAPPER}} .rkit-team__social_item:hover',
                'condition' => [
                    'socmed_color_select' => 'custom'
                ]
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section('dots_style',  [
            'label' => esc_html('Dots'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [
                'show_dots' => 'yes'
            ]
        ]);

        $this->add_responsive_control(
            'dot_align',
            [
                'label' => esc_html__('Alignment', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'rometheme-for-elementor'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'rometheme-for-elementor'),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'rometheme-for-elementor'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'default' => 'center',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .rkit-team-carousel-pagination' => 'justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dot_spacing',
            [
                'label' => esc_html__('Spacing', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 1000,
                        'step' => 5,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-team-carousel-pagination' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('dot_tab');

        $this->start_controls_tab('dot_tab_normal', ['label' => esc_html('Normal')]);

        $this->add_responsive_control(
            'dot_width',
            [
                'label' => esc_html__('Width', 'rometheme-for-elementor'),
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
                'default' => [
                    'unit' => 'px',
                    'size' => 8
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-pagination-bullet' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dot_height',
            [
                'label' => esc_html__('Height', 'rometheme-for-elementor'),
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
                'default' => [
                    'unit' => 'px',
                    'size' => 8
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-pagination-bullet' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'dot_background_normal',
                'types' => ['classic', 'gradient', 'video'],
                'selector' => '{{WRAPPER}} .rkit-pagination-bullet',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'dot_border_normal',
                'selector' => '{{WRAPPER}} .rkit-pagination-bullet',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'dot_box_shadow_normal',
                'selector' => '{{WRAPPER}} .rkit-pagination-bullet',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('dot_tab_hover', ['label' => esc_html('Hover')]);

        $this->add_responsive_control(
            'dot_width_hover',
            [
                'label' => esc_html__('Width', 'rometheme-for-elementor'),
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
                'default' => [
                    'unit' => 'px',
                    'size' => 8
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-pagination-bullet:hover' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dot_height_hover',
            [
                'label' => esc_html__('Height', 'rometheme-for-elementor'),
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
                'default' => [
                    'unit' => 'px',
                    'size' => 8
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-pagination-bullet:hover' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'dot_background_hover',
                'types' => ['classic', 'gradient', 'video'],
                'selector' => '{{WRAPPER}} .rkit-pagination-bullet:hover',
            ]
        );

        // $this->add_group_control(
        //     \Elementor\Group_Control_Border::get_type(),
        //     [
        //         'name' => 'dot_border_hover',
        //         'selector' => '{{WRAPPER}} .rkit-pagination-bullet:hover',
        //     ]
        // );

        $this->add_control('dot_border_color_hover', [
            'label' => esc_html('Border Color', 'rometheme-for-elementor'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-pagination-bullet:hover' => 'border-color: {{VALUE}};',
            ],
            'condition'=>[
                'dot_border_normal_border!'=>''
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'dot_box_shadow_hover',
                'selector' => '{{WRAPPER}} .rkit-pagination-bullet:hover',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('dot_tab_active', ['label' => esc_html('Active')]);

        $this->add_responsive_control(
            'dot_width_active',
            [
                'label' => esc_html__('Width', 'rometheme-for-elementor'),
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
                'default' => [
                    'unit' => 'px',
                    'size' => 24
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-pagination-bullet.rkit-pagination-bullet-active' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'dot_height_active',
            [
                'label' => esc_html__('Height', 'rometheme-for-elementor'),
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
                'default' => [
                    'unit' => 'px',
                    'size' => 8
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-pagination-bullet.rkit-pagination-bullet-active' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'dot_background_active',
                'types' => ['classic', 'gradient', 'video'],
                'selector' => '{{WRAPPER}} .rkit-pagination-bullet.rkit-pagination-bullet-active',
            ]
        );

        // $this->add_group_control(
        //     \Elementor\Group_Control_Border::get_type(),
        //     [
        //         'name' => 'dot_border_active',
        //         'selector' => '{{WRAPPER}} .rkit-pagination-bullet.rkit-pagination-bullet-active',
        //     ]
        // );

        $this->add_control('dot_border_color_active', [
            'label' => esc_html('Border Color', 'rometheme-for-elementor'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-pagination-bullet.rkit-pagination-bullet-active' => 'border-color: {{VALUE}};',
            ],
            'condition'=>[
                'dot_border_normal_border!'=>''
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'dot_box_shadow_active',
                'selector' => '{{WRAPPER}} .rkit-pagination-bullet.rkit-pagination-bullet-active',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'dot_divider',
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
                'default' => [
                    'top' => 6,
                    'right' => 6,
                    'bottom' => 6,
                    'left' => 6,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-pagination-bullet ' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('arrow_style', [
            'label' => esc_html('Navigation'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [
                'show_navigation_team_carousel' => 'yes'
            ]
        ]);

        $this->add_responsive_control(
            'nav_icon_size',
            [
                'label' => esc_html__('Icon Size', 'rometheme-for-elementor'),
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
                    '{{WRAPPER}} .rkit-team-carousel-navigation' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // navigation spacing control slider
        $this->add_responsive_control(
            'spacing_navigation_animation',
            [
                'label' => esc_html__('Navigation Spacing', 'rometheme-for-elementor'),
                'size_units' => ['px', '%', 'em', 'rem'],
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'description' => 'Adjust the distance of the navigation buttons from the left and right edges. Use negative values (e.g., -20px) to position the buttons outside the carousel area.',
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
                    'size' => -9
                ],
                'tablet_default' => [
                    'unit' => 'px',
                    'size' => 70
                ],
                'mobile_default' => [
                    'unit' => 'px',
                    'size' => 64
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-absolute-team-carousel-wrapper ' => 'left:{{SIZE}}{{UNIT}} !important; right:{{SIZE}}{{UNIT}} !important;',
                ],
                'condition' => [
                    'show_navigation_team_carousel' => 'yes',
                    'show_hover_animation_team_carousel' => 'yes'
                ]
            ]
        );


        $this->add_responsive_control(
            'navigation_spacing',
            [
                'label' => esc_html__('Navigation Spacing', 'rometheme-for-elementor'),
                'size_units' => ['px', '%', 'em', 'rem'],
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'description' => 'Adjust the distance of the navigation buttons from the left and right edges. Use negative values (e.g., -20px) to position the buttons outside the carousel area.',
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
                    'size' => -10
                ],
                'tablet_default' => [
                    'unit' => 'px',
                    'size' => 66
                ],
                'mobile_default' => [
                    'unit' => 'px',
                    'size' => 61
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-absolute-team-carousel-wrapper ' => 'left:{{SIZE}}{{UNIT}} !important; right:{{SIZE}}{{UNIT}} !important;',
                ],
                'condition' => [
                    'show_navigation_team_carousel' => 'yes',
                    'show_hover_animation_team_carousel!' => 'yes'
                ]
            ]
        );

        $this->add_responsive_control(
            'nav_padding',
            [
                'label' => esc_html__('Padding', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-team-carousel-navigation' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // $this->add_responsive_control(
        //     'nav_margin_next',
        //     [
        //         'label' => esc_html__('Margin Next Button', 'rometheme-for-elementor'),
        //         'type' => \Elementor\Controls_Manager::DIMENSIONS,
        //         'size_units' => ['px', '%', 'em', 'rem', 'custom'],
        //         'selectors' => [
        //             '{{WRAPPER}} .team-carousel-next-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        //         ],
        //     ]
        // );

        // $this->add_responsive_control(
        //     'nav_margin_prev',
        //     [
        //         'label' => esc_html__('Margin Previous Button', 'rometheme-for-elementor'),
        //         'type' => \Elementor\Controls_Manager::DIMENSIONS,
        //         'size_units' => ['px', '%', 'em', 'rem', 'custom'],
        //         'selectors' => [
        //             '{{WRAPPER}} .team-carousel-prev-wrapper' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        //         ],
        //     ]
        // );

        $this->start_controls_tabs('nav_tabs');

        $this->start_controls_tab('nav_tab_normal', ['label' => esc_html('Normal')]);

        $this->add_control('nav_icon_color_normal', [
            'label' => esc_html('Icon Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-team-carousel-navigation .navigation-icon' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'nav_background_normal',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-team-carousel-navigation',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'nav_border_normal',
                'selector' => '{{WRAPPER}} .rkit-team-carousel-navigation',
            ]
        );


        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'nav_box_shadow_normal',
                'selector' => '{{WRAPPER}} .rkit-team-carousel-navigation',
            ]
        );


        $this->end_controls_tab();

        $this->start_controls_tab('nav_tab_hover', ['label' => esc_html('Hover')]);

        $this->add_control('nav_icon_color_hover', [
            'label' => esc_html('Icon Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-team-carousel-navigation:hover .navigation-icon' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'nav_background_hover',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-team-carousel-navigation:hover',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                        'default' => '#00cea6',
                    ],
                ],
            ]
        );

        // $this->add_group_control(
        //     \Elementor\Group_Control_Border::get_type(),
        //     [
        //         'name' => 'nav_border_hover',
        //         'selector' => '{{WRAPPER}} .rkit-team-carousel-navigation:hover',
        //     ]
        // );

        $this->add_control('nav_border_color_hover', [
            'label' => esc_html('Border Color', 'rometheme-for-elementor'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-team-carousel-navigation:hover' => 'border-color: {{VALUE}};',
            ],
            'condition'=>[
                'nav_border_normal_border!'=>''
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'nav_box_shadow_hover',
                'selector' => '{{WRAPPER}} .rkit-team-carousel-navigation:hover',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
            'nav_divider',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
            ]
        );

        $this->add_responsive_control(
            'nav_radius',
            [
                'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'default' => [
                    'top' => 12,
                    'right' => 12,
                    'bottom' => 12,
                    'left' => 12,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-team-carousel-navigation' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }
    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $showAnimationNavigation = ($settings['show_hover_animation_team_carousel'] === 'yes') ? 'rkit-animation-hover-team-carousel-enabled' : 'rkit-animation-hover-team-carousel-disabled';
        $config = [
            'rtl'                => is_rtl(),
            'arrows'            => ($settings['show_navigation_team_carousel'] === 'yes') ? true : false,
            'dots'                => ($settings['show_dots'] === 'yes') ? true : false,
            'pauseOnHover'        => ($settings['pause_on_hover'] === 'yes') ? true : false,
            'autoplay'            => ($settings['autoplay'] === 'yes') ? true : false,
            'speed'                => $settings['speed'],
            'slidesPerGroup'    => !empty($settings['slide_to_scroll']) ? (int) $settings['slide_to_scroll'] : 1,
            'slidesPerView'        => !empty((int) $settings['slide_to_show']) ? (int) $settings['slide_to_show'] : 1,
            'loop'                => ($settings['loop'] === 'yes') ? true : false,
            'breakpoints'        => [
                320 => [
                    'slidesPerView'      => !empty($settings['slide_to_show_mobile']) ? $settings['slide_to_show_mobile'] : 1,
                    'slidesPerGroup'     => !empty($settings['slide_to_scroll_mobile']) ? $settings['slide_to_scroll_mobile'] : 1,
                    'spaceBetween'       => !empty($settings['spacebetween_mobile']['size']) ? $settings['spacebetween_mobile']['size']  : 10,
                ],
                768 => [
                    'slidesPerView'      => !empty($settings['slide_to_show_tablet']) ? $settings['slide_to_show_tablet'] : 2,
                    'slidesPerGroup'     => !empty($settings['slide_to_scroll_tablet']) ? $settings['slide_to_scroll_tablet'] : 1,
                    'spaceBetween'       => !empty($settings['spacebetween_tablet']['size']) ? $settings['spacebetween_tablet']['size'] : 10,
                ],
                1024 => [
                    'slidesPerView'      => !empty($settings['slide_to_show']) ? $settings['slide_to_show'] : 3,
                    'slidesPerGroup'     => !empty($settings['slide_to_scroll']) ? $settings['slide_to_scroll'] : 1,
                    'spaceBetween'        => !empty($settings['spacebetween']['size']) ? $settings['spacebetween']['size'] : 15,
                ]
            ],
        ];
        $membersContent = $settings['list_member_content'];
        if (empty($membersContent)) return;
?>
        <div class="rkit-container-team-carousel <?= $showAnimationNavigation ?>" data-config="<?php echo esc_attr(json_encode($config)) ?>">
            <div class="rkit-absolute-team-carousel-wrapper">
                <?php if ($settings['show_navigation_team_carousel'] === 'yes') : ?>
                    <div class="team-carousel-prev-wrapper">
                        <div class="rkit-team-carousel-navigation rkit-team-carousel-button-prev">
                            <?php \Elementor\Icons_Manager::render_icon($settings['prev_icon_team_carousel'], ['aria-hidden' => 'true', 'class' => 'navigation-icon']); ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($settings['show_navigation_team_carousel'] === 'yes') : ?>
                    <div class="team-carousel-next-wrapper">
                        <div class="rkit-team-carousel-navigation rkit-team-carousel-button-next">
                            <?php \Elementor\Icons_Manager::render_icon($settings['next_icon_team_carousel'], ['aria-hidden' => 'true', 'class' => 'navigation-icon']); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="team-carousel-card-container">
                <div class="swiper-wrapper">
                    <?php
                    foreach ($membersContent as $index => $content) :
                        $name_tag = in_array($content['name_tag'], ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']) ? $content['name_tag'] : 'h4';
                    ?>
                        <div class="swiper-slide rkit-team rkit-team__<?php echo ($content['select_style'] != 'social_on_hover') ? esc_attr($content['select_style']) : esc_attr($content['select_style'] . '_' . $settings['social_media_position']) ?>">
                            <div class="rkit-team-card <?php echo esc_attr($content['pointer_effect']); ?>">
                                <div class="rkit-team__img <?php echo esc_attr($settings['image_hover_effect']); ?>">
                                    <?php echo \Elementor\Group_Control_Image_Size::get_attachment_image_html($content, 'thumbnail', 'member_image'); ?>
                                </div>

                                <div class="rkit-team__detail">
                                    <<?php echo esc_html($name_tag); ?> class="rkit-team__name">
                                        <?php echo esc_html($content['member_name']); ?>
                                    </<?php echo esc_html($name_tag); ?>>

                                    <span class="rkit-team__role"><?php echo esc_html($content['member_position']); ?></span>
                                    <span class="rkit-team__description"><?php echo esc_html($content['member_description']); ?></span>

                                    <?php if ($content['select_style'] !== 'social_on_hover') : ?>
                                        <div class="rkit-team__social">
                                            <?php
                                            // ✅ Loop semua platform yang dipilih
                                            if (!empty($content['social_media_select']) && is_array($content['social_media_select'])) {
                                                foreach ($content['social_media_select'] as $platform) {
                                                    // ambil icon & url dinamis per platform
                                                    $icon_key = "{$platform}_icon";
                                                    $url_key = "{$platform}_url";

                                                    if (!empty($content[$icon_key])) {
                                                        echo '<div class="rkit-team__social_item ' . esc_attr($platform) . '">';
                                                        echo '<a href="' . esc_url($content[$url_key]['url'] ?? '#') . '" target="_blank">';
                                                        \Elementor\Icons_Manager::render_icon(
                                                            $content[$icon_key],
                                                            ['aria-hidden' => 'true', 'class' => 'rkit-team__social_icon']
                                                        );
                                                        echo '</a>';
                                                        echo '</div>';
                                                    }
                                                }
                                            }
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if ($content['select_style'] === 'social_on_hover') : ?>
                                    <div class="rkit-team__social">
                                        <?php
                                        if (!empty($content['social_media_select']) && is_array($content['social_media_select'])) {
                                            foreach ($content['social_media_select'] as $platform) {
                                                $icon_key = "{$platform}_icon";
                                                $url_key = "{$platform}_url";

                                                if (!empty($content[$icon_key])) {
                                                    echo '<div class="rkit-team__social_item ' . esc_attr($platform) . '">';
                                                    echo '<a href="' . esc_url($content[$url_key]['url'] ?? '#') . '" target="_blank">';
                                                    \Elementor\Icons_Manager::render_icon(
                                                        $content[$icon_key],
                                                        ['aria-hidden' => 'true', 'class' => 'rkit-team__social_icon']
                                                    );
                                                    echo '</a>';
                                                    echo '</div>';
                                                }
                                            }
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php
                    endforeach;
                    ?>
                </div>
                <!-- Add pagination -->
                <?php if ($settings['show_dots'] === 'yes') : ?>
                    <div class="rkit-team-carousel-pagination"></div>
                <?php endif; ?>
            </div>
        </div>
<?php
    }
}
