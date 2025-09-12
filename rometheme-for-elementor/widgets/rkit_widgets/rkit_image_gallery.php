<?php

class Rkit_image_gallery extends \Elementor\Widget_Base
{
    public function get_name()
    {
        return 'rkit_image_gallery';
    }
    public function get_title()
    {
        return \RomethemeKit\RkitWidgets::listWidgets()['image_gallery']['name'];
    }

    public function get_icon()
    {
        $icon = 'rkit-widget-icon ' . \RomethemeKit\RkitWidgets::listWidgets()['image_gallery']['icon'];
        return $icon;
    }
    public function get_keywords()
    {
        return ['rometheme', 'image', 'box', 'image-box', 'mansonry', ' image gallery'];
    }

    function get_custom_help_url()
    {
        return \RomethemeKit\RkitWidgets::listWidgets()['image_gallery']['docsURL'];
    }

    public function get_categories()
    {
        return ['romethemekit_widgets'];
    }

    public function get_style_depends()
    {
        return ['rkit-image_gallery-style'];
    }

    public function get_script_depends()
    {
        return ['rkit-image_gallery-script'];
    }
    protected function register_controls()
    {
        $this->start_controls_section('content_section', [
            'label' => esc_html('Layout'),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT
        ]);

        $this->add_control('layout_option', [
            'label' => esc_html('Layout'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                'masonry' => esc_html('Masonry'),
                'grid' => esc_html('Grid'),
            ],
            'default' => 'masonry'
        ]);

        $this->add_responsive_control('img-aspect-ratio-ig', [
            'label' => esc_html__('Image Aspect Ratio', 'rometheme-for-elementor'),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => [
                '1/1' => esc_html__('1 : 1', 'rometheme-for-elementor'),
                '3/2' => esc_html__('3 : 2', 'rometheme-for-elementor'),
                '2/3' => esc_html__('2 : 3', 'rometheme-for-elementor'),
                '5/4' => esc_html__('5 : 4', 'rometheme-for-elementor'),
                '4/5' => esc_html__('4 : 5', 'rometheme-for-elementor'),
                '16/9' => esc_html__('16 : 9', 'rometheme-for-elementor'),
                '9/16' => esc_html__('9 : 16', 'rometheme-for-elementor'),

            ],
            'default' => '1/1',
            'selectors' => [
                '{{WRAPPER}} .gallery_image, {{WRAPPER}} .gallery_image img' => 'aspect-ratio:{{VALUE}}'
            ],
            'condition' => [
                'layout_option' => 'grid',
            ]
        ]);

        $this->add_responsive_control(
            'column',
            [
                'label' => esc_html__('Column', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 100,
                'step' => 1,
                'desktop_default' => 3,
                'tablet_default' => 3,
                'mobile_default' => 1,
                'selectors' => [
                    '{{WRAPPER}} .masonry' => 'column-count: {{value}};',
                ],
                'condition' => [
                    'layout_option' => 'masonry',
                ]
            ]
        );

        $this->add_responsive_control(
            'column_grid',
            [
                'label' => __('Column', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 100,
                'default' => 3,
                'tablet_default' => 3,
                'mobile_default' => 1,
                'selectors' => [
                    '{{WRAPPER}} .grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ],
                'condition' => [
                    'layout_option' => 'grid',
                ]
            ]
        );

        $this->add_control(
            'lazy_load_ig',
            [
                'label' => esc_html__('Lazy Load', 'text-domain'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'text-domain'),
                'label_off' => esc_html__('No', 'text-domain'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );


        $this->add_control(
            'gallery',
            [
                'label' => esc_html__('Add Images', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::GALLERY,
                'show_label' => false,
                'default' => [],
            ]
        );

        // $this->add_group_control(
        //     \Elementor\Group_Control_Image_Size::get_type(),
        //     [
        //         'name' => 'thumbnail_ig',  
        //         'exclude' => ['custom'],
        //         'include' => [],
        //         'default' => 'large',
        //     ]
        // );


        $this->add_control(
            'hover_animation_ig',
            [
                'label' => esc_html__('Hover Image', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::HOVER_ANIMATION,
                'default' => 'grow',
            ]
        );

        $this->add_control(
            'lightbox',
            [
                'label' => esc_html__('Lightbox', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    'default' => esc_html('Default'),
                    'yes' => esc_html('Yes'),
                    'no' => esc_html('No'),
                ],
                'default' => 'default',
            ]
        );
        $this->end_controls_section();

        // style =====================================================================================================

        $this->start_controls_section('Container_style_section', [
            'label' => esc_html__('Container', 'rometheme-for-elementor'),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);


        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'gallery_image_border',
                'label' => esc_html__('Border  ', 'rometheme-for-elementor'),
                'selector' => '{{WRAPPER}} .brick',

            ]
        );

        $this->add_responsive_control(
            'gallery_image_radius',
            [
                'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem'],
                'selectors' => [
                    '{{WRAPPER}} .brick' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),

            [
                'name' => 'ig_backgroud',
                'label' => esc_html__('Container Background', 'rometheme-for-elementor'),
                'types' => ['classic', 'gradient'],
                'selector' => '{{WRAPPER}} .rkit-gallery-masonry-container'
            ]
        );

        $this->add_responsive_control(
            'spacebetween',
            [
                'label' => esc_html__('Spacing', 'rometheme-for-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 20
                ],
                'selectors' => [
                    '{{WRAPPER}} .rkit-gallery-masonry' => 'column-gap: {{SIZE}}{{UNIT}};', /* untuk jarak antar kolom */
                    '{{WRAPPER}} .brick' => 'margin-bottom: {{SIZE}}{{UNIT}};', /* untuk jarak vertikal antar elemen */
                ]

            ]
        );

        $this->end_controls_section();
    }
    protected function render()
    {
        $settings = $this->get_settings_for_display();

        if ($settings['lazy_load_ig'] == 'yes') {
            $lazy = 'skeleton';
        } else {
            $lazy = '';
        }

?>

        <div class="rkit-gallery-masonry-container">
            <div class="rkit-gallery-masonry <?php echo esc_attr($settings['layout_option']) ?>">
                <?php foreach ($settings['gallery'] as $image) :
                    $hover_animation_ig_class = !empty($settings['hover_animation_ig']) ? ' elementor-animation-' . $settings['hover_animation_ig'] : '';
                ?>
                    <div class="brick">
                        <div class="<?php echo esc_attr($lazy); ?>"></div>
                        <?php if ($settings['lightbox'] === 'default' || $settings['lightbox'] === 'yes') : ?>
                            <a data-elementor-open-lightbox="yes" data-elementor-lightbox-slideshow="gallery" href="<?php echo esc_attr($image['url']); ?>">
                            <?php endif; ?>
                            <img src="<?php echo esc_attr($image['url']); ?>"
                                class="gallery_image <?php echo esc_attr($hover_animation_ig_class); ?>"
                                alt="<?php echo !empty($image['alt']) ? esc_attr($image['alt']) : 'Gallery Image'; ?>"
                                loading="lazy"></img>
                            <?php if ($settings['lightbox'] === 'default' || $settings['lightbox'] === 'yes') : ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

            </div>
        </div>

<?php }
}
