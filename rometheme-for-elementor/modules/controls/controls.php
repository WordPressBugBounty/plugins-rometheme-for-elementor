<?php

namespace RomethemeKit;

use Elementor\Group_Control_Typography;

class Controls
{
    public function __construct()
    {
        add_action('elementor/controls/register', [$this, 'register_controls']);
    }

    function register_controls($controls_manager)
    {
        require \RomeTheme::module_dir() . 'controls/control/promotion.php';
        $controls_manager->register(new \RkitPromotionControl());
    }
}
