<?php


namespace RomethemeKit;


use Elementor\Plugin;
use RomethemePro;


class RkitToolTips
{


  public function __construct()
  {
    // Tambah control tooltip ke widget & container
    add_action('elementor/element/common/_section_style/after_section_end', array($this, 'add_widget_options'), 10);
    add_action('elementor/element/container/section_layout/after_section_end', array($this, 'add_container_options'), 10, 2);


    // Tambah tooltip saat render
    // add_action('elementor/frontend/container/before_render', [$this, 'render_container_tooltip']);
    // add_action('elementor/frontend/widget/before_render', [$this, 'render_widget_tooltip']);


    // Tambahkan CSS tooltip di frontend & editor
    add_action('elementor/frontend/after_enqueue_styles', [$this, 'enqueue_tooltip_styles']);
    add_action('elementor/editor/after_enqueue_styles', [$this, 'enqueue_tooltip_styles']);


    add_action('admin_enqueue_scripts', [$this, 'enqueue_tooltip_styles']);
  }

  public function enqueue_tooltip_styles()
  {
    wp_enqueue_style(
      'rkit-tooltips-style',
      \RomeTheme::plugin_url() . 'assets/css/tooltip.css',
      [],
      \RomeTheme::rt_version()
    );
  }

  public function add_container_options($container)
  {
    $container->start_controls_section('rtmkit_tool_tips_container', [
      'label' => esc_html__('RTMKit Tooltip'),
      'tab' => \Elementor\Controls_Manager::TAB_ADVANCED
    ]);

    $container->add_control(
      'rtm_tooltip_text_switch',
      [
        'label' => esc_html__('Enable Tooltip ?', 'rometheme-for-elementor'),
        'type' => \Elementor\Controls_Manager::SWITCHER,
        'label_on' => esc_html__('Yes', 'rometheme-for-elementor'),
        'label_off' => esc_html__('No', 'rometheme-for-elementor'),
        'return_value' => 'enabled',
        'default' => '',
        'prefix_class' => 'rtm-tooltip-'
      ]
    );


    $container->start_controls_tabs(
      'style_tabs',
      [
        'condition' => ['rtm_tooltip_text_switch' => 'enabled'],
      ]
    );

    $container->start_controls_tab(
      'style_content_tab',
      [
        'label' => esc_html__('Content', 'rometheme-for-elementor'),
      ]
    );

    $container->add_control('rtm_tooltip_text', [
      'label' => esc_html__('Tooltip Text', 'rometheme-for-elementor'),
      'type' => \Elementor\Controls_Manager::TEXT,
      // 'placeholder' => esc_html__('Input tooltips text', 'rometheme-for-elementor'),
      'condition' => ['rtm_tooltip_text_switch' => 'enabled'],
      'selectors' => [
        '{{WRAPPER}}.rtm-tooltip-enabled::after' => 'content: "{{VALUE}}"'
      ]
    ]);

    $container->add_responsive_control('rkit_tooltip_wrapper', [
      'label' => __('Wrapper Width', 'rometheme-for-elementor'),
      'type' => \Elementor\Controls_Manager::SLIDER,
      'size_units' => ['px'],
      'range' => [
        'px' => ['max' => 360],
      ],
      'selectors' => [
        '{{WRAPPER}}.rtm-tooltip-enabled::after' => 'width: {{SIZE}}{{UNIT}};',
      ],
      'condition' => ['rtm_tooltip_text_switch' => 'enabled'],
    ]);

    $container->add_responsive_control('rkit_x_offset', [
      'label' => __('X Offset', 'rometheme-for-elementor'),
      'type' => \Elementor\Controls_Manager::SLIDER,
      'size_units' => ['px', '%'],
      'range' => [
        '%' => ['min' => -1000, 'max' => 1000],
        'px' => ['min' => -1000, 'max' => 1000],
      ],
      'selectors' => [
        '{{WRAPPER}}.rtm-tooltip-enabled::after' => '--x: {{SIZE}}{{UNIT}};',
      ],
      'condition' => ['rtm_tooltip_text_switch' => 'enabled'],
    ]);

    $container->add_responsive_control('rkit_y_offset', [
      'label' => __('Y Offset', 'rometheme-for-elementor'),
      'type' => \Elementor\Controls_Manager::SLIDER,
      'size_units' => ['px', '%'],
      'range' => [
        '%' => ['min' => -1000, 'max' => 1000],
        'px' => ['min' => -1000, 'max' => 1000],
      ],
      'selectors' => [
        '{{WRAPPER}}.rtm-tooltip-enabled::after' => '--y: {{SIZE}}{{UNIT}};',
      ],
      'condition' => ['rtm_tooltip_text_switch' => 'enabled'],
    ]);

    $container->end_controls_tab();

    $container->start_controls_tab(
      'style_style_tab',
      [
        'label' => esc_html__('Style', 'rometheme-for-elementor'),
        'condition' => ['rtm_tooltip_text_switch' => 'enabled'],
      ]
    );

    $container->add_control(
      'tooltip_text_alignment',
      [
        'label' => esc_html__('Alignment', 'rometheme-for-elementor'),
        'type' => \Elementor\Controls_Manager::CHOOSE,
        'options' => [
          'start' => [
            'title' => esc_html__('Left', 'rometheme-for-elementor'),
            'icon' => 'eicon-order-start',
          ],
          'center' => [
            'title' => esc_html__('Center', 'rometheme-for-elementor'),
            'icon' => 'eicon-h-align-center',
          ],
          'end' => [
            'title' => esc_html__('Right', 'rometheme-for-elementor'),
            'icon' => 'eicon-order-end',
          ],
        ],
        'default' => 'center',
        'toggle' => true,
        'selectors' => [
          '{{WRAPPER}}.rtm-tooltip-enabled::after' => 'text-align: {{VALUE}};',
        ],
      ]
    );

    $container->add_group_control(
      \Elementor\Group_Control_Typography::get_type(),
      [
        'name' => 'tooltip_typography',
        'selector' => '{{WRAPPER}}.rtm-tooltip-enabled::after ',
      ]
    );

    $container->add_responsive_control('tooltip_color', [
      'label' => esc_html__('Text Color', 'rometheme-for-elementor'),
      'type' => \Elementor\Controls_Manager::COLOR,
      'selectors' => [
        '{{WRAPPER}}.rtm-tooltip-enabled::after' => 'color : {{VALUE}}'
      ],
    ]);

    // $container->add_control(
    //   'background_tooltip',
    //   [
    //     'label' => esc_html__('Background', 'rometheme-for-elementor'),
    //     'type' => \Elementor\Controls_Manager::HEADING,
    //     'separator' => 'before',
    //   ]
    // );

    $container->add_group_control(
      \Elementor\Group_Control_Background::get_type(),
      [
        'name' => 'background_container_all',
        'types' => ['classic', 'gradient'],
        'selector' => '{{WRAPPER}}.rtm-tooltip-enabled::after',
      ]
    );

    $container->add_group_control(
      \Elementor\Group_Control_Border::get_type(),
      [
        'name' => 'border_tooltips',
        'selector' => '{{WRAPPER}}.rtm-tooltip-enabled::after',
      ]
    );

    $container->add_responsive_control(
      'tooltip_radius',
      [
        'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
        'type' => \Elementor\Controls_Manager::DIMENSIONS,
        'size_units' => ['px', '%', 'em', 'rem'],
        'selectors' => [
          '{{WRAPPER}}.rtm-tooltip-enabled::after' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );

    $container->add_group_control(
      \Elementor\Group_Control_Box_Shadow::get_type(),
      [
        'name' => 'tooltip_box_shadow',
        'label' => __('Box Shadow', 'rometheme-for-elementor'),
        'selector' => '{{WRAPPER}}.rtm-tooltip-enabled::after',
      ]
    );

    $container->add_responsive_control(
      'tooltip_padding',
      [
        'label' => esc_html__('Padding', 'rometheme-for-elementor'),
        'type' => \Elementor\Controls_Manager::DIMENSIONS,
        'size_units' => ['px', '%', 'em', 'rem'],
        'selectors' => [
          '{{WRAPPER}}.rtm-tooltip-enabled::after' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );
    $container->end_controls_tab();
    $container->end_controls_tabs();
    $container->end_controls_section();
  }

  public function add_widget_options($widget)
  {
    $widget->start_controls_section('rtmkit_tool_tips_widget', [
      'label' => esc_html__('RTMKit Tooltip'),
      'tab' => \Elementor\Controls_Manager::TAB_ADVANCED
    ]);

    $widget->add_control(
      'rtm_tooltip_text_switch',
      [
        'label' => esc_html__('Enable Tooltip ?', 'rometheme-for-elementor'),
        'type' => \Elementor\Controls_Manager::SWITCHER,
        'label_on' => esc_html__('Yes', 'rometheme-for-elementor'),
        'label_off' => esc_html__('No', 'rometheme-for-elementor'),
        'return_value' => 'enabled',
        'default' => '',
        'prefix_class' => 'rtm-tooltip-'
      ]
    );

    $widget->start_controls_tabs(
      'style_tabs',
      ['condition' => ['rtm_tooltip_text_switch' => 'enabled'],]
    );

    $widget->start_controls_tab(
      'style_content_tab',
      [
        'label' => esc_html__('Content', 'rometheme-for-elementor'),

      ]
    );

    $widget->add_control('rtm_tooltip_text', [
      'label' => esc_html__('Tooltip Text', 'rometheme-for-elementor'),
      'type' => \Elementor\Controls_Manager::TEXT,
      // 'placeholder' => esc_html__('Input Tooltips Text', 'rometheme-for-elementor'),
      'condition' => ['rtm_tooltip_text_switch' => 'enabled'],
      'selectors' => [
        '{{WRAPPER}}.rtm-tooltip-enabled::after' => 'content: "{{VALUE}}"'
      ]
    ]);

    $widget->add_responsive_control('rkit_tooltip_wrapper', [
      'label' => __('Wrapper Width', 'rometheme-for-elementor'),
      'type' => \Elementor\Controls_Manager::SLIDER,
      'size_units' => ['px'],
      'range' => [
        'px' => ['max' => 360],
      ],
      'selectors' => [
        '{{WRAPPER}}.rtm-tooltip-enabled::after' => 'width: {{SIZE}}{{UNIT}};',
      ],
      'condition' => ['rtm_tooltip_text_switch' => 'enabled'],
    ]);

    $widget->add_responsive_control('rkit_x_offset', [
      'label' => __('X Offset', 'rometheme-for-elementor'),
      'type' => \Elementor\Controls_Manager::SLIDER,
      'size_units' => ['px', '%'],
      'range' => [
        '%' => ['min' => -1000, 'max' => 1000],
        'px' => ['min' => -1000, 'max' => 1000],
      ],
      'selectors' => [
        '{{WRAPPER}}.rtm-tooltip-enabled::after' => '--x: {{SIZE}}{{UNIT}};',
      ],
      'condition' => ['rtm_tooltip_text_switch' => 'enabled'],
    ]);

    $widget->add_responsive_control('rkit_y_offset', [
      'label' => __('Y Offset', 'rometheme-for-elementor'),
      'type' => \Elementor\Controls_Manager::SLIDER,
      'size_units' => ['px', '%'],
      'range' => [
        '%' => ['min' => -1000, 'max' => 1000],
        'px' => ['min' => -1000, 'max' => 1000],
      ],
      'selectors' => [
        '{{WRAPPER}}.rtm-tooltip-enabled::after' => '--y: {{SIZE}}{{UNIT}};',
      ],
      'condition' => ['rtm_tooltip_text_switch' => 'enabled'],
    ]);

    $widget->end_controls_tab();

    $widget->start_controls_tab(
      'style_style_tab',
      [
        'label' => esc_html__('Style', 'rometheme-for-elementor'),
      ]
    );

    $widget->add_control(
      'tooltip_text_alignment',
      [
        'label' => esc_html__('Alignment', 'rometheme-for-elementor'),
        'type' => \Elementor\Controls_Manager::CHOOSE,
        'options' => [
          'start' => [
            'title' => esc_html__('Left', 'rometheme-for-elementor'),
            'icon' => 'eicon-order-start',
          ],
          'center' => [
            'title' => esc_html__('Center', 'rometheme-for-elementor'),
            'icon' => 'eicon-h-align-center',
          ],
          'end' => [
            'title' => esc_html__('Right', 'rometheme-for-elementor'),
            'icon' => 'eicon-order-end',
          ],
        ],
        'default' => 'center',
        'toggle' => true,
        'selectors' => [
          '{{WRAPPER}}.rtm-tooltip-enabled::after' => 'text-align: {{VALUE}};',
        ],
      ]
    );

    $widget->add_group_control(
      \Elementor\Group_Control_Typography::get_type(),
      [
        'name' => 'tooltip_typography',
        'selector' => '{{WRAPPER}}.rtm-tooltip-enabled::after ',
      ]
    );

    $widget->add_responsive_control('tooltip_color', [
      'label' => esc_html__('Text Color', 'rometheme-for-elementor'),
      'type' => \Elementor\Controls_Manager::COLOR,
      'selectors' => [
        '{{WRAPPER}}.rtm-tooltip-enabled::after' => 'color : {{VALUE}}'
      ],
    ]);

    // $widget->add_control(
    //   'background_tooltip',
    //   [
    //     'label' => esc_html__('Background', 'rometheme-for-elementor'),
    //     'type' => \Elementor\Controls_Manager::HEADING,
    //     'separator' => 'before',
    //   ]
    // );

    $widget->add_group_control(
      \Elementor\Group_Control_Background::get_type(),
      [
        'name' => 'background_widget_all',
        'types' => ['classic', 'gradient'],
        'selector' => '{{WRAPPER}}.rtm-tooltip-enabled::after',
      ]
    );

    $widget->add_group_control(
      \Elementor\Group_Control_Border::get_type(),
      [
        'name' => 'border_tooltips',
        'selector' => '{{WRAPPER}}.rtm-tooltip-enabled::after',
      ]
    );

    $widget->add_responsive_control(
      'tooltip_radius',
      [
        'label' => esc_html__('Border Radius', 'rometheme-for-elementor'),
        'type' => \Elementor\Controls_Manager::DIMENSIONS,
        'size_units' => ['px', '%', 'em', 'rem'],
        'selectors' => [
          '{{WRAPPER}}.rtm-tooltip-enabled::after' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );


    $widget->add_group_control(
      \Elementor\Group_Control_Box_Shadow::get_type(),
      [
        'name' => 'tooltip_box_shadow',
        'label' => __('Box Shadow', 'rometheme-for-elementor'),
        'selector' => '{{WRAPPER}}.rtm-tooltip-enabled::after',
      ]
    );

    $widget->add_responsive_control(
      'tooltip_padding',
      [
        'label' => esc_html__('Padding', 'rometheme-for-elementor'),
        'type' => \Elementor\Controls_Manager::DIMENSIONS,
        'size_units' => ['px', '%', 'em', 'rem'],
        'selectors' => [
          '{{WRAPPER}}.rtm-tooltip-enabled::after' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
        ],
      ]
    );

    $widget->end_controls_tab();
    $widget->end_controls_tabs();
    $widget->end_controls_section();
  }
}
