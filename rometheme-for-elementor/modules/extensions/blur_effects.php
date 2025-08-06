<?php

namespace RomethemeKit;

class BlurEffects
{

	public function __construct()
	{
		add_action('elementor/element/common/_section_style/after_section_end', array($this, 'add_widget_options'), 10);
		// add_action('elementor/element/column/section_advanced/after_section_end', array($this, 'add_column_options'), 10, 2);
		// add_action('elementor/element/section/section_advanced/after_section_end', array($this, 'add_section_options'), 10, 2);
		add_action('elementor/element/container/section_layout/after_section_end', array($this, 'add_container_options'), 10, 2);
	}

	public function add_container_options($container, $args)
	{
		$container->start_controls_section('rtmkit_blur_effects_container', [
			'label' => esc_html('RTMkit Glass Effect'),
			'tab' => \Elementor\Controls_Manager::TAB_ADVANCED
		]);

		$container->add_control(
			'use_blur_effects',
			[
				'label' => esc_html__('Enable Glass Effect ?', 'rometheme-for-elementor'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('yes', 'rometheme-for-elementor'),
				'label_off' => esc_html__('no', 'rometheme-for-elementor'),
				'return_value' => 'enabled',
				'default' => '',
				'prefix_class' => 'rtmkit-blur-effect-'
			]
		);

		$container->add_control(
			'rtmkit_blur_',
			[
				'label' => esc_html__('Blur (px)', 'rometheme-for-elementor'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 60,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 20,
				],
				'selectors' => [
					'{{WRAPPER}}.rtmkit-blur-effect-enabled' => 'backdrop-filter: blur({{SIZE}}{{UNIT}});',
				],
				'condition' => [
					'use_blur_effects' => 'enabled'
				]
			]
		);


		$container->end_controls_section();
	}

	public function add_widget_options($widgets) {
		$widgets->start_controls_section('rtmkit_blur_effects_container', [
			'label' => esc_html('RTMkit Glass Effect'),
			'tab' => \Elementor\Controls_Manager::TAB_ADVANCED
		]);

		$widgets->add_control(
			'use_blur_effects',
			[
				'label' => esc_html__('Enable Blur Effect ?', 'rometheme-for-elementor'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('yes', 'rometheme-for-elementor'),
				'label_off' => esc_html__('no', 'rometheme-for-elementor'),
				'return_value' => 'enabled',
				'default' => '',
				'prefix_class' => 'rtmkit-blur-effect-'
			]
		);

		$widgets->add_control(
			'rtmkit_blur_',
			[
				'label' => esc_html__('Blur (px)', 'rometheme-for-elementor'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 60,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 20,
				],
				'selectors' => [
					'{{WRAPPER}}.rtmkit-blur-effect-enabled ' => 'backdrop-filter: blur({{SIZE}}{{UNIT}});',
				],
				'condition' => [
					'use_blur_effects' => 'enabled'
				]
			]
		);


		$widgets->end_controls_section();
	}

}
