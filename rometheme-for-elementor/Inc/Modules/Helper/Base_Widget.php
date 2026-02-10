<?php
/**
 * Base_Widget
 *
 * Abstract base class for Elementor widgets in RomeThemeKit Pro.
 *
 * @package RTMKitPro\Helpers
 */

namespace RTMKit\Modules\Helper;

/**
 * Class Base_Widget
 *
 * Abstract base for custom Elementor widgets.
 */
abstract class Base_Widget extends \Elementor\Widget_Base {
    protected $widget_name;
    protected $widget_title;
    protected $widget_icon;
    protected $widget_categories;
    protected $widget_keywords;
    protected $widget_help_url;
    protected $widget_style_depends;
    protected $widget_script_depends;

    public function __construct($data = [], $args = null, $config = []) {
        parent::__construct($data, $args);

        $this->widget_name = $config['name'];
        $this->widget_title = $config['title'];
        $this->widget_icon = $config['icon'];
        $this->widget_categories = $config['categories'];
        $this->widget_keywords = $config['keywords'];
        $this->widget_help_url = $config['help_url'];
        $this->widget_style_depends = $config['style_depends'];
        $this->widget_script_depends = $config['script_depends'];

    }

    public function get_name() {
        return $this->widget_name;
    }

    public function get_title() {
        return $this->widget_title;
    }

    public function get_icon() {
        return $this->widget_icon;
    }

    public function get_categories() {
        return $this->widget_categories;
    }

    public function get_keywords() {
        return $this->widget_keywords;
    }

    public function get_custom_help_url() {
        return $this->widget_help_url;
    }

    public function get_style_depends() {
        return $this->widget_style_depends;
    }

    public function get_script_depends() {
        return $this->widget_script_depends;
    }
}
