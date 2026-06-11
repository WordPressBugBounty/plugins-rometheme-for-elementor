<?php

namespace RTMKit\Elements;

use RTMKit\Modules\Helper\Pro_Widget_Base;

if (!defined('ABSPATH')) {
    exit; 
}

class DynamicWidgetSinglePro extends Pro_Widget_Base
{

    private $widget_key;
    private $widget_data;
    private static $registered_widgets = [];

    public function __construct(array $data = [], array $args = null)
    {
        parent::__construct($data, $args);

        if (isset($args['widget_key']) && isset($args['widget_data'])) {
            $this->widget_key  = $args['widget_key'];
            $this->widget_data = $args['widget_data'];

            self::$registered_widgets[$this->get_name()] = true;
        }
    }

    public function show_in_panel()
    {
        if ($this->is_pro_active()) {
            return false;
        }

        $widget_name = $this->get_name();

        if (isset(self::$registered_widgets[$widget_name])) {
            return false;
        }

        self::$registered_widgets[$widget_name] = true;
        return true;
    }

    public function get_name()
    {
        if (isset($this->widget_data['widget_name']) && !empty($this->widget_data['widget_name'])) {
            return $this->widget_data['widget_name'];
        }

        return 'rtm-' . $this->widget_key;
    }

    public function get_title()
    {
        return isset($this->widget_data['name']) ? $this->widget_data['name'] : 'Pro Widget';
    }

    public function get_icon()
    {
        $icon = isset($this->widget_data['icon']) ? $this->widget_data['icon'] : 'eicon-pro-icon';
        return 'rkit-widget-icon ' . $icon;
    }

    public function get_categories()
    {
        return ['romethemepro_post'];
    }

    public function get_keywords()
    {
        return [$this->get_title(), 'pro', 'rtm','single'];
    }

    public function get_custom_help_url()
    {
        return isset($this->widget_data['docsURL']) ? $this->widget_data['docsURL'] : '';
    }

    protected function register_controls()
    {
    
    }
}
