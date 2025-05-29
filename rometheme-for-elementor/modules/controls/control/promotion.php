<?php

class RkitPromotionControl extends \Elementor\Base_Data_Control
{

    const TYPE = 'rkit_promotion_control';

    public function get_type()
    {
        return static::TYPE;
    }

    public function enqueue()
    {
        wp_register_style('rkit-promotion-style', \Rometheme::module_url() . 'controls/assets/css/promotion.scss', [], \RomeTheme::rt_version());
        wp_enqueue_style('rkit-promotion-style');

        wp_register_script('rkit-promotion-script', \RomeTheme::module_url() . 'controls/assets/js/promotion.js', ['jquery'], \RomeTheme::rt_version());
        wp_enqueue_script('rkit-promotion-script');
    }

    public function content_template()
    {
?>
        <div data-promotion="{{{ data.name }}}" class="elementor-label-inline e-control-promotion__wrapper rkit-promotion-wrapper">
            <div class="elementor-control-content">
                <div class="elementor-control-field">
                    <# if ( data.label ) {#>
						<label for="<?php $this->print_control_uid(); ?>" class="elementor-control-title">{{{ data.label }}}</label>
					<# } #>
					<span class="e-control-promotion__lock-wrapper rkit-promotion-controls-icon">
						<i class="eicon-pro-icon"></i>
					</span>
					<div class="elementor-control-input-wrapper">
						<i class="eicon-lock"></i>
					</div>
					<div class="e-promotion-react-wrapper" data-promotion="{{{ data.promotion_title }}}" data-promotion-description="{{{data.promotion_description}}}"></div>
				</div>
			</div>
		</div>
		<?php
    }
}
