<?php

namespace RTMKit\Widgets;
class SiteLogo extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'site-logo';
    }

    public function get_title()
    {
        return 'Site Logo';
    }

    public function get_icon()
    {
        return 'rkit-widget-icon rtmicon rtmicon-site-logo';
    }

    function get_custom_help_url()
    {
        return 'https://support.rometheme.net/docs/romethemekit/widgets/how-to-use-ezd_ampersand-customize-site-logo-widget-ezd_ampersand-favicon-logo/';
    }


    public function get_categories()
    {
        return ['romethemekit_header_footer'];
    }

    public function get_keywords()
    {
        return ['site', 'logo', 'rometheme'];
    }

    public function get_style_depends()
    {
        return ['rtmkit-widget-site_logo'];
    }

    protected function register_controls()
    {
        $this->start_controls_section('content-section', [
            'label' => esc_html__('Image', 'rometheme-for-elementor'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
        ]);

        $this->add_control(
            'logo_type',
            [
                'label' => esc_html__('Logo Type', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'custom' => esc_html__('Custom Logo', 'rometheme-for-elementor'),
                    'site_logo' => esc_html__('Site Logo', 'rometheme-for-elementor'),
                ],
                'default' => 'site_logo',
            ]
        );

        $this->add_control(
			'custom_panel_alert',
			[
				'type' => \Elementor\Controls_Manager::ALERT,
				'alert_type' => 'info',
				'content' => esc_html__( 'You can set a global site logo in Elementor: Site Settings > Site Identity.', 'rometheme-for-elementor' ),
                'condition' => [
                    'logo_type' => 'site_logo'
                ]
			]
		);

        $this->add_control(
            'image',
            [
                'label' => esc_html__('Choose Image', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::MEDIA,
                'default' => [
                    'id' => (get_theme_mod('custom_logo')) ? attachment_url_to_postid(wp_get_attachment_url(get_theme_mod('custom_logo'))) : '',
                    'url' => (get_theme_mod('custom_logo')) ? wp_get_attachment_url(get_theme_mod('custom_logo')) : \Elementor\Utils::get_placeholder_image_src(),
                ],
                'condition' => [
                    'logo_type' => 'custom'
                ]
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Image_Size::get_type(),
            [
                'name' => 'thumbnail', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `thumbnail_size` and `thumbnail_custom_dimension`.
                'exclude' => [],
                'include' => [],
                'default' => 'medium',
            ]
        );


        $this->add_control(
            'website_link',
            [
                'label' => esc_html__('Link', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::URL,
                'placeholder' => esc_html__('https://your-link.com', 'rometheme-for-elementor'),
                'dynamic' => [
                    'active' => true,
                ],
                'options' => ['url', 'is_external', 'nofollow'],
                'default' => [
                    'url' => '',
                    'is_external' => true,
                    'nofollow' => true,
                    // 'custom_attributes' => '',
                ],
                'label_block' => true,
            ]
        );

        $this->add_control(
            'show_caption',
            [
                'label' => esc_html__('Show Caption', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'rometheme-for-elementor'),
                'label_off' => esc_html__('Hide', 'rometheme-for-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section('style-setting', [
            'label' => esc_html__('Style Setting', 'rometheme-for-elementor'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Css_Filter::get_type(),
            [
                'name' => 'custom_css_filters',
                'selector' => '{{WRAPPER}} .rkit-image > img',
            ]
        );

        $this->add_responsive_control(
            'text_align',
            [
                'label' => esc_html__('Alignment', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'devices' => ['desktop', 'tablet', 'mobile'],
                'options' => [
                    'start' => [
                        'title' => esc_html__('Left', 'rometheme-for-elementor'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'rometheme-for-elementor'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'end' => [
                        'title' => esc_html__('Right', 'rometheme-for-elementor'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'toggle' => true,
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .rkit-image-container .rkit-image' => 'align-items: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control('rkit-image-maxwidth', [
            'label' => esc_html__('Max Width', 'rometheme-for-elementor'),
            'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px', '%', 'em', 'rem'],
            'range' => [
                'px' => ['min' => 0, 'max' => 1000, 'step' => 1],
                '%' => ['min' => 0, 'max' => 100],
                'em' => ['min' => 0, 'max' => 50],
                'rem' => ['min' => 0, 'max' => 50],
            ],
            'selectors' => [
                '{{WRAPPER}} .rkit-image > img' => 'max-width: {{SIZE}}{{UNIT}}'
            ]
        ]);

        $this->end_controls_section();

        $this->start_controls_section('caption_settings', [
            'label' => esc_html('Caption'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            'condition' => [
                'show_caption' => 'yes'
            ]
        ]);

        $this->add_control(
            'caption_align',
            [
                'label' => esc_html__('Alignment', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'rometheme-for-elementor'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'rometheme-for-elementor'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'rometheme-for-elementor'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'toggle' => true,
                'selectors' => [
                    '{{WRAPPER}} .rkit-image .site-caption' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'caption_typography',
                'selector' => '{{WRAPPER}} .rkit-image .site-caption',
            ]
        );

        $this->add_control('caption_color', [
            'label' => esc_html('Color'),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .rkit-image .site-caption' => 'color:{{VALUE}}'
            ]
        ]);

        $this->add_group_control(
            \Elementor\Group_Control_Text_Shadow::get_type(),
            [
                'name' => 'caption_shadow',
                'selector' => '{{WRAPPER}} .rkit-image .site-caption',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Text_Stroke::get_type(),
            [
                'name' => 'caption_stroke',
                'selector' => '{{WRAPPER}} .rkit-image .site-caption',
            ]
        );

        $this->end_controls_section();
    }
    protected function render()
    {
        $settings = $this->get_settings_for_display();
        if (!empty($settings['website_link']['url'])) {
            $this->add_link_attributes('website_link', $settings['website_link']);
        }
        // Get the caption of the image
        $image_caption = !empty($image_id) ? wp_get_attachment_caption($image_id) : '';
        if($settings['logo_type'] == 'site_logo'){
            $image_id = (get_theme_mod('custom_logo')) ? attachment_url_to_postid(wp_get_attachment_url(get_theme_mod('custom_logo'))) : '';
            
        } else {
            $image_id = $settings['image']['id'];
        }
        if($settings['thumbnail_size'] != 'custom'){
            $image_size = $settings['thumbnail_size'];
        } else {
            $image_size = [
                $settings['thumbnail_custom_dimension']['width'],
                $settings['thumbnail_custom_dimension']['height']
            ];
        }
?>
        <div class="rkit-image-container">
            <a class="rkit-image" <?php $this->print_render_attribute_string('website_link') ?> style="text-decoration: none; border-bottom:none">
                <?php echo wp_get_attachment_image( $image_id, $image_size ); ?>
                <?php if ($settings['show_caption'] === 'yes') : ?>
                    <div class="site-caption"><?php echo $image_caption ?> </div>
                <?php endif; ?>
            </a>
        </div>
<?php
    }
}
