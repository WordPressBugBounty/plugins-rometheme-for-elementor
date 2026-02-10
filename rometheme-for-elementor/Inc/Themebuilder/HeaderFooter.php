<?php

namespace RTMKit\Themebuilder;

class HeaderFooter
{
    private static $instance = null;
    private $header_template;
    private $footer_template;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        $this->header_template = \RTMKit\Modules\Themebuilder\ThemebuilderStorage::instance()->get_themebuilder_template('header');
        $this->footer_template = \RTMKit\Modules\Themebuilder\ThemebuilderStorage::instance()->get_themebuilder_template('footer');
        add_action('template_redirect', [$this, 'header_footer_template']);
    }

    public function header_footer_template($templates)
    {
        if ($this->header_template) {
            add_action('get_header', array($this, 'override_header_template'), 99);
            add_action('romethemekit_header', array($this, 'render_header'));
        }
        if ($this->footer_template) {
            add_action('get_footer', array($this, 'override_footer_template'), 99);
            add_action('romethemekit_footer', array($this, 'render_footer'));
        }

        return $templates;
    }

    public function override_header_template()
    {
        load_template(RTM_KIT_DIR . 'Inc/Themebuilder/templates/header_template.php');
        $templates   = array();
        $templates[] = 'header.php';
        remove_all_actions('wp_head');
        ob_start();
        locate_template($templates, true);
        ob_get_clean();
    }

    public function override_footer_template()
    {
        load_template(RTM_KIT_DIR . 'Inc/Themebuilder/templates/footer_template.php');
        $templates   = array();
        $templates[] = 'footer.php';
        remove_all_actions('wp_footer');
        ob_start();
        locate_template($templates, true);
        ob_get_clean();
    }

    public function render_header()
    {
        $headers = $this->header_template;
        if (!$headers) {
            return;
        }

        foreach ($headers as $header) {
            $header_id = $header->ID;
            $condition = get_post_meta($header_id, 'rometheme_template_condition', true) ?? true;
            if ($condition && \RTMKit\Modules\Themebuilder\ThemebuilderModule::instance()->check_condition($condition)) {
                $headerHTML = '<header itemtype="https://schema.org/WPHeader" itemscope="itemscope">%s</header>';
                $fullHeader =
                    sprintf(
                        $headerHTML,
                        \RTMKit\Modules\Themebuilder\ThemebuilderStorage::instance()->get_themebuilder_content($header_id)
                    );
                echo $fullHeader;
                break;
            }
        }
    }

    public function render_footer()
    {
        $footers = $this->footer_template;
        if (!$footers) {
            return;
        }

        foreach ($footers as $footer) {
            $footer_id = $footer->ID;
            $condition = get_post_meta($footer_id, 'rometheme_template_condition', true);

            if ($condition && \RTMKit\Modules\Themebuilder\ThemebuilderModule::instance()->check_condition($condition)) {
                $footerHTML = '<footer itemtype="https://schema.org/WPFooter" itemscope="itemscope">%s</footer>';
                $fullFooter =
                    sprintf(
                        $footerHTML,
                        \RTMKit\Modules\Themebuilder\ThemebuilderStorage::instance()->get_themebuilder_content($footer_id)
                    );
                echo $fullFooter;
                break;
            }
        }
    }
}
