<?php

namespace RTMKit\Elements\SinglePost;

defined('ABSPATH') || exit;

use WP_Query;

class PostComment extends SinglePostWidget
{
    public static function metadata(): array
    {
        return [
            'id' => 'rtm-post-comment',
            'icon' => 'rtmicon rtmicon-post-comment',
            'name' => esc_html('Post Comment'),
            'widget_name' => 'rtm-post-comment',
            'category' => self::CATEGORY,
            'type' => 'free',
            'badge' => esc_html('New'),
            'requires' => [],
            'tags' => ['rtm', 'comment', 'post comment'],
            'priority' => 10,
            'docsURL' => 'https://support.rometheme.net/docs/romethemekit/widgets/',
            'previewURL' => 'https://support.rometheme.net/docs/romethemekit/widgets/',
            'videoURL' => 'https://support.rometheme.net/docs/romethemekit/widgets/',
            'scripts' => [
                'css' => 'post-comment.css',
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
            'label' => esc_html('Post Comment'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT
        ]);

        $this->add_control(
            'show_comment',
            [
                'label' => esc_html__('Show Comment', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'romethemekitpro'),
                'label_off' => esc_html__('No', 'romethemekitpro'),
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        $this->add_control('title_text', [
            'label' => esc_html('Title'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => esc_html('Leave Comment')
        ]);

        $this->add_control('comment_notes', [
            'label' => esc_html('Comment Notes'),
            'type' => \Elementor\Controls_Manager::TEXTAREA,
            'default' => esc_html('Your email address will not be published. Required fields are marked *.'),
            'description' => esc_html('Note: This text will only appear when the user is not logged in.')
        ]);

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
            'comments_options',
            [
                'label' => esc_html__('Comments', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'height_comment',
            [
                'label' => esc_html__('Height', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
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
                    'size' => 200,
                    'unit' => 'px'
                ],
                'selectors' => [
                    '{{WRAPPER}}  .rkit-post-comment textarea' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control('comment_label', [
            'label' => esc_html('Label'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => esc_html('Comment'),
            'condition' => [
                'show_label' => 'yes'
            ]
        ]);

        $this->add_control('comment_placeholder', [
            'label' => esc_html('Placeholder'),
            'type' => \Elementor\Controls_Manager::TEXT
        ]);

        $this->add_control(
            'submit_options',
            [
                'label' => esc_html__('Submit', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );


        $this->add_control(
            'show_submit_icon',
            [
                'label' => esc_html__('Add Icon ?', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'romethemekitpro'),
                'label_off' => esc_html__('No', 'romethemekitpro'),
                'return_value' => 'yes',
                'default' => '',
            ]
        );

        $this->add_control(
            'submit_icon',
            [
                'label' => esc_html__('Icon', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'condition' => [
                    'show_submit_icon' => 'yes'
                ]
            ]
        );

        $this->add_control('submit_icon_position', [
            'label' => esc_html('Icon Position'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'row' => esc_html('Before Text'),
                'row-reverse' => esc_html('After Text'),
            ],
            'default' => 'row',
            'selectors' => [
                '{{WRAPPER}} .rkit-post-comment .rkit-post-comment__submit' => 'flex-direction : {{VALUE}}'
            ],
            'condition' => [
                'show_submit_icon' => 'yes'
            ]
        ]);

        $this->add_control('submit_text', [
            'label' => esc_html('Submit Text'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => esc_html('Post Comment')
        ]);

        $this->end_controls_section();

        $this->start_controls_section('other_field', [
            'label' => esc_html('Other Fields'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT
        ]);


        $this->add_control(
            'important_note',
            [
                'type' => \Elementor\Controls_Manager::ALERT,
                'alert_type' => 'info',
                'heading' => esc_html__('Important Note', 'romethemekitpro'),
                'content' => esc_html__('The fields below will appear when the user is not logged in.', 'romethemekitpro'),
            ]
        );


        $this->add_control(
            'author_options',
            [
                'label' => esc_html('Author'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control('author_label', [
            'label' => esc_html('Label'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => esc_html('Name')
        ]);

        $this->add_control('author_placeholder', [
            'label' => esc_html('Placheholder'),
            'type' => \Elementor\Controls_Manager::TEXT
        ]);

        $this->add_control(
            'email_options',
            [
                'label' => esc_html('Email'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control('email_label', [
            'label' => esc_html('Label'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => esc_html('Email')
        ]);

        $this->add_control('email_placeholder', [
            'label' => esc_html('Placheholder'),
            'type' => \Elementor\Controls_Manager::TEXT
        ]);

        $this->add_control(
            'website_options',
            [
                'label' => esc_html('Website'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control('website_label', [
            'label' => esc_html('Label'),
            'type' => \Elementor\Controls_Manager::TEXT,
            'default' => esc_html('Website')
        ]);

        $this->add_control('website_placeholder', [
            'label' => esc_html('Placheholder'),
            'type' => \Elementor\Controls_Manager::TEXT
        ]);

        $this->end_controls_section();

        $this->start_controls_section('style_section', [
            'label' => esc_html('Post Comment'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_control(
            'title_options',
            [
                'label' => esc_html__('Title', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .comment-reply-title',
            ]
        );

        $this->add_control('title_color', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .comment-reply-title' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'title_shadow',
                'selector' => '{{WRAPPER}} .comment-reply-title',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'text_stroke',
                'selector' => '{{WRAPPER}} .comment-reply-title',
            ]
        );

        $this->add_control(
            'comment_note_options',
            [
                'label' => esc_html__('Comment Note', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'comment_note_typography',
                'selector' => '{{WRAPPER}} .comment-notes , .logged-in-as',
            ]
        );

        $this->add_control('comment_note_color', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .comment-notes , .logged-in-as' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'comment_note_shadow',
                'selector' => '{{WRAPPER}} .comment-notes , .logged-in-as',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'comment_note_stroke',
                'selector' => '{{WRAPPER}} .comment-notes , .logged-in-as',
            ]
        );

        $this->end_controls_section();

        // Heading Image
        // Image Size
        // Border 
        // Border Rdius exclude color
        // Tab Normal Hover
        // Border Color
        // Box shadow

        // Heading Username
        // Typography
        // Tabs Normal Hover
        // Color

        // Headinbg Message
        // Typography
        // Tab Normal Hover 
        // Color

        // Heading Reply Button
        // Control Choose label Alignment
        // Typography
        // Background
        // Tab Normal Hover
        // Color
        // Border Color

        $this->start_controls_section('comment_list_style', [
            'label' => esc_html__('Comment List', 'rometheme-kit'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [
                'show_comment' => 'yes'
            ]
        ]);

        $this->add_control(
            'comment_item_bg_color',
            [
                'label' => esc_html__('Background Color', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .comment-body' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        // $this->add_responsive_control(
        //     'comment_item_gap',
        //     [
        //         'label' => esc_html__('Gap Between Comments', 'rometheme-kit'),
        //         'type' => \Elementor\Controls_Manager::SLIDER,
        //         'size_units' => ['px', 'em', 'rem'],
        //         'range' => [
        //             'px' => ['min' => 0, 'max' => 100],
        //         ],
        //         'selectors' => [
        //             // '{{WRAPPER}} .rkit-post-comment .comment-list li.comment:not(:last-child)' => 'margin-bottom: {{SIZE}}{{UNIT}};',
        //             // '{{WRAPPER}} .rkit-post-comment .comment-list .children' => 'margin-top: {{SIZE}}{{UNIT}};',
        //             '{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-body' => '--padding-inline-start: {{SIZE}}{{UNIT}};',
        //         ],
        //     ]
        // );

        $this->add_responsive_control(
            'comment_item_padding',
            [
                'label' => esc_html__('Padding', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'comment_item_border',
                'selector' => '{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-body',
            ]
        );

        $this->add_responsive_control(
            'comment_item_border_radius',
            [
                'label' => esc_html__('Border Radius', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-comment .comment-body' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // --- Heading Image ---
        $this->add_control(
            'heading_image',
            [
                'label' => esc_html__('Image', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'avatar_size',
            [
                'label' => esc_html__('Image Size', 'rometheme-kit'),
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
                'selectors' => [
                    '{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-author img.avatar' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                    // '{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-body' => '--image-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'avatar_border',
                'selector' => '{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-author img.avatar',
                'exclude' => ['color'], // Sesuai permintaan exclude color
            ]
        );

        $this->add_responsive_control(
            'avatar_border_radius',
            [
                'label' => esc_html__('Border Radius', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-author img.avatar' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'avatar_margin',
            [
                'label' => esc_html__('Margin', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-author img.avatar' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('avatar_tabs');
        $this->start_controls_tab('avatar_normal', ['label' => esc_html__('Normal', 'rometheme-kit')]);

        $this->add_control(
            'avatar_border_color',
            [
                'label' => esc_html__('Border Color', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => ['{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-author img.avatar' => 'border-color: {{VALUE}};'],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'avatar_box_shadow',
                'selector' => '{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-author img.avatar',
            ]
        );
        $this->end_controls_tab();

        $this->start_controls_tab('avatar_hover', ['label' => esc_html__('Hover', 'rometheme-kit')]);
        $this->add_control(
            'avatar_border_color_hover',
            [
                'label' => esc_html__('Border Color', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => ['{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-author img.avatar:hover' => 'border-color: {{VALUE}};'],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'avatar_box_shadow_hover',
                'selector' => '{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-author img.avatar:hover',
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();

        // --- Heading Username ---
        $this->add_control(
            'heading_username',
            [
                'label' => esc_html__('Username', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'username_typography',
                'selector' => '{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-author .fn',
            ]
        );

        $this->start_controls_tabs('username_tabs');
        $this->start_controls_tab('username_normal', ['label' => esc_html__('Normal', 'rometheme-kit')]);
        $this->add_control(
            'username_color',
            [
                'label' => esc_html__('Color', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => ['{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-author .fn' => 'color: {{VALUE}};'],
            ]
        );
        $this->end_controls_tab();

        $this->start_controls_tab('username_hover', ['label' => esc_html__('Hover', 'rometheme-kit')]);
        $this->add_control(
            'username_color_hover',
            [
                'label' => esc_html__('Color', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => ['{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-author .fn:hover' => 'color: {{VALUE}};'],
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();

        // --- Heading Meta (Date/Time) ---
        $this->add_control(
            'heading_meta',
            [
                'label' => esc_html__('Meta', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name'     => 'meta_typography',
                'selector' => '{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-metadata a, {{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-metadata time',
            ]
        );

        $this->start_controls_tabs('meta_tabs');

        $this->start_controls_tab('meta_normal', ['label' => esc_html__('Normal', 'rometheme-kit')]);
        $this->add_control(
            'meta_color',
            [
                'label'     => esc_html__('Text Color', 'rometheme-kit'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-metadata a' => 'color: {{VALUE}};',
                    '{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-metadata time' => 'color: {{VALUE}};'
                ],
            ]
        );
        $this->end_controls_tab();

        $this->start_controls_tab('meta_hover', ['label' => esc_html__('Hover', 'rometheme-kit')]);
        $this->add_control(
            'meta_color_hover',
            [
                'label'     => esc_html__('Text Color', 'rometheme-kit'),
                'type'      => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-metadata a:hover' => 'color: {{VALUE}};',
                    '{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-metadata time:hover' => 'color: {{VALUE}};'
                ],
            ]
        );
        $this->end_controls_tab();

        $this->end_controls_tabs();

        // --- Heading Message ---
        $this->add_control(
            'heading_message',
            [
                'label' => esc_html__('Message', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'message_typography',
                'selector' => '{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-content p',
            ]
        );

        $this->start_controls_tabs('message_tabs');
        $this->start_controls_tab('message_normal', ['label' => esc_html__('Normal', 'rometheme-kit')]);
        $this->add_control(
            'message_color',
            [
                'label' => esc_html__('Color', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => ['{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-content p' => 'color: {{VALUE}};'],
            ]
        );
        $this->end_controls_tab();

        $this->start_controls_tab('message_hover', ['label' => esc_html__('Hover', 'rometheme-kit')]);
        $this->add_control(
            'message_color_hover',
            [
                'label' => esc_html__('Color', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => ['{{WRAPPER}} #comments.comments-area .rkit-post-comment .comment-content p:hover' => 'color: {{VALUE}};'],
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();

        // --- Heading Reply Button ---
        $this->add_control(
            'heading_reply',
            [
                'label' => esc_html__('Reply Button', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_responsive_control(
            'reply_alignment',
            [
                'label' => esc_html__('Alignment', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => ['title' => esc_html__('Left', 'rometheme-kit'), 'icon' => 'eicon-text-align-left'],
                    'center' => ['title' => esc_html__('Center', 'rometheme-kit'), 'icon' => 'eicon-text-align-center'],
                    'right' => ['title' => esc_html__('Right', 'rometheme-kit'), 'icon' => 'eicon-text-align-right'],
                ],
                'selectors' => [
                    '{{WRAPPER}} #comments.comments-area .rkit-post-comment .reply' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'reply_typography',
                'selector' => '{{WRAPPER}} #comments.comments-area .rkit-post-comment .reply .comment-reply-link',
            ]
        );


        $this->add_responsive_control(
            'padding_reply_button',
            [
                'label' => esc_html__('Padding', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} #comments.comments-area .rkit-post-comment .reply .comment-reply-link' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('reply_tabs');
        $this->start_controls_tab('reply_normal', ['label' => esc_html__('Normal', 'rometheme-kit')]);

        $this->add_control(
            'reply_color',
            [
                'label' => esc_html__('Color', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => ['{{WRAPPER}} #comments.comments-area .rkit-post-comment .reply .comment-reply-link' => 'color: {{VALUE}};'],
            ]
        );

        $this->add_control(
            'reply_bg_color',
            [
                'label' => esc_html__('Background Color', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => ['{{WRAPPER}} #comments.comments-area .rkit-post-comment .reply .comment-reply-link' => 'background-color: {{VALUE}};'],
            ]
        );

        $this->add_control(
            'reply_border_color',
            [
                'label' => esc_html__('Border Color', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => ['{{WRAPPER}} #comments.comments-area .rkit-post-comment .reply .comment-reply-link' => 'border-color: {{VALUE}};'],
            ]
        );
        $this->end_controls_tab();

        $this->start_controls_tab('reply_hover', ['label' => esc_html__('Hover', 'rometheme-kit')]);

        $this->add_control(
            'reply_color_hover',
            [
                'label' => esc_html__('Color', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => ['{{WRAPPER}} #comments.comments-area .rkit-post-comment .reply .comment-reply-link:hover' => 'color: {{VALUE}};'],
            ]
        );

        $this->add_control(
            'reply_bg_color_hover',
            [
                'label' => esc_html__('Background Color', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => ['{{WRAPPER}} #comments.comments-area .rkit-post-comment .reply .comment-reply-link:hover' => 'background-color: {{VALUE}};'],
            ]
        );

        $this->add_control(
            'reply_border_color_hover',
            [
                'label' => esc_html__('Border Color', 'rometheme-kit'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => ['{{WRAPPER}} #comments.comments-area .rkit-post-comment .reply .comment-reply-link:hover' => 'border-color: {{VALUE}};'],
            ]
        );
        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->end_controls_section();


        $this->start_controls_section('label_style', [
            'label' => esc_html('Label'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [
                'show_label' => 'yes'
            ]
        ]);

        //      $this->add_control(
        //     'required_label_heading',
        //     [
        //         'label' => esc_html__('Field', 'textdomain'),
        //         'type' => \Elementor\Controls_Manager::HEADING,
        //         'separator' => 'after',
        //     ]
        // );


        $this->add_control(
            'required_label_heading',
            [
                'label' => esc_html__('Required', 'textdomain'),
                'type' => \Elementor\Controls_Manager::HEADING,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'required_typography',
                'selector' => '{{WRAPPER}} .rkit-post-comment .required',
            ]
        );

        $this->add_control('required_color', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-comment .required' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_control(
            'required_gap',
            [
                'label' => esc_html__('Spacing', 'textdomain'),
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
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-comment label'  => 'display:inline-flex; gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'end_field_label_heading',
            [
                'type' => \Elementor\Controls_Manager::HEADING,
                'label' => esc_html__('All Fields', 'textdomain'),
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'label_typography',
                'selector' => '{{WRAPPER}} .rkit-post-comment label',
            ]
        );

        $this->add_control('label_color', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-comment label' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'label_shadow',
                'selector' => '{{WRAPPER}} .rkit-post-comment label',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'label_stroke',
                'selector' => '{{WRAPPER}} .rkit-post-comment label',
            ]
        );

        $this->add_responsive_control(
            'label_margin',
            [
                'label' => esc_html__('Margin', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-comment label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
        $this->start_controls_section('input_style', [
            'label' => esc_html('Input'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_control(
            'comment_heading',
            [
                'label' => esc_html__('Comment', 'textdomain'),
                'type' => \Elementor\Controls_Manager::HEADING,
            ]
        );

        $this->add_responsive_control(
            'input_radius',
            [
                'label' => esc_html__('Border Radius', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-comment textarea ' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'comment_margin',
            [
                'label' => esc_html__('Margin', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-comment .comment-form-comment' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'other_fields_heading',
            [
                'label' => esc_html__('Other Fields', 'textdomain'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );


        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'input_typography',
                'selector' => '{{WRAPPER}} .rkit-post-comment input, .rkit-post-comment textarea ',
            ]
        );

        $this->add_control(
            'spacing_other_input',
            [
                'label' => esc_html__('Spacing', 'romethemekitpro'),
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
                    'size' => 12,
                ],
                'selectors' => [
                    '{{WRAPPER}} p.comment-form-author,
                    p.comment-form-email,
                    p.comment-form-url,
                    p.comment-form-cookies-consent
                    ' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'input_radius_other_field',
            [
                'label' => esc_html__('Border Radius', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-comment input ' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->add_control(
            'other_fields_end_heading',
            [
                'label' => esc_html__('', 'textdomain'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->start_controls_tabs('input_tab');

        $this->start_controls_tab('input_tab_normal', ['label' => esc_html('Normal')]);

        $this->add_control('input_color_normal', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-comment input, .rkit-post-comment textarea' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'input_background_normal',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-post-comment input, .rkit-post-comment textarea ',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'border_input_normal',
                'selector' => '{{WRAPPER}} .rkit-post-comment input, .rkit-post-comment textarea',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'input_box_shadow_normal',
                'selector' => '{{WRAPPER}} .rkit-post-comment input, .rkit-post-comment textarea',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('input_tab_focus', ['label' => esc_html('Focus')]);

        $this->add_control('input_color_focus', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-comment input:focus, .rkit-post-comment textarea:focus' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'input_background_focus',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-post-comment input:focus, .rkit-post-comment textarea:focus ',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'border_input_focus',
                'selector' => '{{WRAPPER}} .rkit-post-comment input:focus, .rkit-post-comment textarea:focus',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'input_box_shadow_focus',
                'selector' => '{{WRAPPER}} .rkit-post-comment input:focus, .rkit-post-comment textarea:focus',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('input_tab_placeholder', ['label' => esc_html('Placeholder')]);

        $this->add_control('input_placeholder_color', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-comment input::placeholder, .rkit-post-comment textarea::placeholder' => 'color:{{VALUE}}'
            ]
        ]);


        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section('gdpr_style', [
            'label' => esc_html('Gdpr'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'input_typography_gdpr',
                'label' => esc_html__('Typography', 'romethemekitpro'),
                'selector' => '{{WRAPPER}} .rkit-post-comment .comment-form-cookies-consent label ',
            ]
        );
        $this->add_responsive_control(
            'gdpr_margin',
            [
                'label' => esc_html__('Margin', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 8,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .comment-form-cookies-consent label' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );


        $this->end_controls_section();
        $this->start_controls_section('submit_style', [
            'label' => esc_html('Submit Button'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_control(
            'heading_icon',
            [
                'label' => esc_html__('Icon', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'condition' => [
                    'show_submit_icon' => 'yes'
                ]
            ]
        );
        $this->add_control(
            'icon_size_submit_button',
            [
                'label' => esc_html__('Icon Size', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 5,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'condition' => [
                    'show_submit_icon' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .form-submit button.rkit-post-comment__submit svg' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'gap_icon_submit_button',
            [
                'label' => esc_html__('Icon Spacing', 'romethemekitpro'),
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
                'condition' => [
                    'show_submit_icon' => 'yes'
                ],
                'selectors' => [
                    '{{WRAPPER}} .form-submit button.rkit-post-comment__submit' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'padding_bg_icon',
            [
                'label' => esc_html__('Padding', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .form-submit .rkit-post-comment__submit .bg-icon' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'show_submit_icon' => 'yes'
                ],
            ]
        );

        $this->add_responsive_control(
            'border_radius_bg_icon',
            [
                'label' => esc_html__('Border Radius', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .form-submit .rkit-post-comment__submit .bg-icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'show_submit_icon' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'divider_icon',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
                'condition' => [
                    'show_submit_icon' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'divider_heading_submit_button',
            [
                'label' => esc_html__('Button', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::HEADING,
            ]
        );


        $this->add_responsive_control(
            'submit_align',
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
                'default' => 'start',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-comment .form-submit ' => 'justify-content: {{VALUE}};',
                ],
            ]
        );


        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'submit_typography',
                'selector' => '{{WRAPPER}} .rkit-post-comment .rkit-post-comment__submit',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'border_submit_normal',
                'selector' => '{{WRAPPER}} .rkit-post-comment .rkit-post-comment__submit',
                'exclude' => ['color']
            ]
        );

        $this->add_responsive_control(
            'submit_radius',
            [
                'label' => esc_html__('Border Radius', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-comment .rkit-post-comment__submit' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'submit_padding',
            [
                'label' => esc_html__('Padding', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-comment .rkit-post-comment__submit' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->start_controls_tabs('submit_tabs');

        $this->start_controls_tab('submit_tab_normal', ['label' => esc_html('Normal')]);

        $this->add_control(
            'heading_icon_normal',
            [
                'label' => esc_html__('Icon', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'condition' => [
                    'show_submit_icon' => 'yes'
                ]
            ]
        );

        $this->add_control('icon_color_submit_button_normal', [
            'label' => esc_html('Icon Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'condition' => [
                'show_submit_icon' => 'yes'
            ],
            'selectors' => [
                '{{WRAPPER}} .rkit-post-comment .rkit-post-comment__submit svg' => 'color:{{VALUE}}; fill:{{VALUE}};',
                '{{WRAPPER}} .rkit-post-comment .rkit-post-comment__submit i' => 'color:{{VALUE}}; fill:{{VALUE}};'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'icon_bgcolor_submit_button_normal',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-post-comment .rkit-post-comment__submit .bg-icon',
                'condition' => [
                    'show_submit_icon' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'divider_icon_normal',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
                'condition' => [
                    'show_submit_icon' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'heading_button_submit_normal',
            [
                'label' => esc_html__('Button', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::HEADING,
            ]
        );

        $this->add_control('submit_color_normal', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-comment .rkit-post-comment__submit' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'background_submit_normal',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-post-comment .rkit-post-comment__submit',
            ]
        );

        $this->add_control(
            'border_submit_normal_color',
            [
                'label' => esc_html__('Border Color', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'condition' => [
                    'border_submit_normal_border!' => ['', 'none'],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-comment .rkit-post-comment__submit' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow_submit_normal',
                'selector' => '{{WRAPPER}} .rkit-post-comment .rkit-post-comment__submit',
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('submit_tab_hover', ['label' => esc_html('Hover')]);

        $this->add_control(
            'heading_icon_hover',
            [
                'label' => esc_html__('Icon', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'condition' => [
                    'show_submit_icon' => 'yes'
                ]
            ]
        );

        $this->add_control('icon_color_submit_button_hover', [
            'label' => esc_html('Icon Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'condition' => [
                'show_submit_icon' => 'yes'
            ],
            'selectors' => [
                '{{WRAPPER}} .rkit-post-comment .rkit-post-comment__submit:hover svg' => 'color:{{VALUE}}; fill:{{VALUE}};',
                '{{WRAPPER}} .rkit-post-comment .rkit-post-comment__submit:hover i' => 'color:{{VALUE}}; fill:{{VALUE}};'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'icon_bgcolor_submit_button_hover',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-post-comment .rkit-post-comment__submit:hover .bg-icon',
                'condition' => [
                    'show_submit_icon' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'divider_icon_hover',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
                'condition' => [
                    'show_submit_icon' => 'yes'
                ],
            ]
        );

        $this->add_control(
            'heading_button_submit_hover',
            [
                'label' => esc_html__('Button', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::HEADING,
            ]
        );

        $this->add_control('submit_color_hover', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-post-comment .rkit-post-comment__submit:hover' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            [
                'name' => 'background_submit_hover',
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-post-comment .rkit-post-comment__submit:hover',
            ]
        );

        $this->add_control(
            'border_submit_hover_color',
            [
                'label' => esc_html__('Border Color', 'romethemekitpro'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'condition' => [
                    'border_submit_normal_border!' => ['', 'none'],
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-post-comment .rkit-post-comment__submit:hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow_submit_hover',
                'selector' => '{{WRAPPER}} .rkit-post-comment .rkit-post-comment__submit:hover',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();
        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();

        $labelComment = ($settings['show_label'] === 'yes') ? '<label for="comment">' . $settings['comment_label'] . '<span class="required">*</span></label>' : '';
        $inputComment = sprintf('<textarea id="comment" name="comment" placeholder="%s" required></textarea>', $settings['comment_placeholder']);
        $comment_field = sprintf('<p class="comment-form-comment">%s %s</p>', $labelComment, $inputComment);
        $comment_notes = $settings['comment_notes'];

        $authorLabel = ($settings['show_label'] === 'yes') ? sprintf('<label for="author">%s<span class="required">*</span></label>', $settings['author_label']) : '';
        $authorField = sprintf('<p class="comment-form-author">%s<input id="author" name="author" type="text" placeholder="%s" required /></p>', $authorLabel, $settings['author_placeholder']);

        $emailLabel = ($settings['show_label'] === 'yes') ? sprintf('<label for="email">%s <span class="required">*</span></label>', $settings['email_label']) : '';
        $emailField = sprintf('<p class="comment-form-email">%s<input id="email" name="email" type="email" value="" placeholder="%s" required /></p>', $emailLabel, $settings['email_placeholder']);

        $webLabel = ($settings['show_label'] === 'yes') ? sprintf('<label for="url">%s</label>', $settings['website_label']) : '';
        $webField = sprintf('<p class="comment-form-url">%s<input id="url" name="url" type="url" value="" placeholder="%s" /></p>', $webLabel, $settings['website_placeholder']);

        $fields = [
            'author' => $authorField,
            'email'  => $emailField,
            'url'    => $webField,
        ];

        $submit_icon_html = '';
        if ('yes' === $settings['show_submit_icon']) {
            $submit_icon_html = '<span class="bg-icon">' . \Elementor\Icons_Manager::try_get_icon_html($settings['submit_icon'], ['aria-hidden' => 'true']) . '</span>';
        }

        $comment_form_args = [
            'title_reply' => esc_html($settings['title_text']),
            'title_reply_to' => __('Reply for %s'),
            'cancel_reply_link'    => __('Cancel Reply'),
            'label_submit'         => __($settings['submit_text']),
            'comment_notes_before' => '<p class="comment-notes">' . $comment_notes . '</p>',
            'comment_notes_after'  => '',
            'comment_field'        => $comment_field,
            'fields' => $fields,
            'submit_button'        => '<button type="submit" class="submit rkit-post-comment__submit">' . $submit_icon_html . esc_html($settings['submit_text']) . '</button>',
        ];

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
                    $post_id = get_the_ID();
                }
                wp_reset_postdata();
            }
        } else {
            $obj = get_queried_object();
            $post_id = $obj->ID;
        }


        echo '<div id="comments" class="comments-area">';
        echo '<div class="rkit-post-comment">';
        if ($settings['show_comment'] == 'yes') {
            $this->render_comment($post_id);
        }
        comment_form($comment_form_args, $post_id);
        echo '</div>';
        echo '</div>';
    }

    protected function render_comment($post_id)
    {
        // Pastikan bahwa post_id adalah integer
        $post_id = (int)$post_id;

        // Memastikan post_id valid dan mendapatkan postingan
        if (!$post_id || !$post = get_post($post_id)) {
            echo '<p>' . __('Invalid post ID.', 'rometheme-for-elementor') . '</p>';
            return;
        }

        // Memastikan bahwa komentar diizinkan untuk postingan ini
        if (!comments_open($post_id) && get_comments_number($post_id) == 0) {
            echo '<p class="no-comments">' . __('Comments are closed for this post.', 'twentythirteen') . '</p>';
            return;
        }

        // Mengambil komentar untuk post_id yang diberikan
        $comments = get_comments(array(
            'post_id' => $post_id,
            'status'  => 'approve', // Hanya komentar yang disetujui
            'order'   => 'ASC',     // Urutan komentar (misalnya, 'ASC' untuk ascending)
        ));

        if (!empty($comments)) : ?>
            <ol class="comment-list">
                <?php
                wp_list_comments(array(
                    'style'       => 'ol',
                    'short_ping'  => true,
                    'avatar_size' => 50,
                    'reply_text'  => __('Reply', 'twentythirteen'),
                    'max_depth'   => get_option('thread_comments_depth'),
                ), $comments);
                ?>
            </ol><!-- .comment-list -->

            <?php if (get_comment_pages_count($comments) > 1 && get_option('page_comments')) : ?>
                <nav class="navigation comment-navigation" role="navigation">
                    <h1 class="screen-reader-text section-heading"><?php _e('Comment navigation', 'twentythirteen'); ?></h1>
                    <div class="nav-previous"><?php previous_comments_link(__('&larr; Older Comments', 'twentythirteen')); ?></div>
                    <div class="nav-next"><?php next_comments_link(__('Newer Comments &rarr;', 'twentythirteen')); ?></div>
                </nav><!-- .comment-navigation -->
            <?php endif; // Check for comment navigation 
            ?>

<?php endif; // End if not empty($comments) 

    }
}
