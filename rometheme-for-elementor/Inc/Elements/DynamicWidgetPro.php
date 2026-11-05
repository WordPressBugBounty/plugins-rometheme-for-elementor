<?php

namespace RTMKit\Elements;

use RTMKit\Modules\Helper\Pro_Widget_Base;

if (!defined('ABSPATH')) {
    exit; 
}

class DynamicWidgetPro extends Pro_Widget_Base
{

    private $widget_key;
    private $widget_data;

    public function __construct(array $data = [], array $args = null)
    {
        parent::__construct($data, $args);

        if (isset($args['widget_key']) && isset($args['widget_data'])) {
            $this->widget_key  = $args['widget_key'];
            $this->widget_data = $args['widget_data'];
        }
    }

    public function show_in_panel()
    {
        // Hanya didaftarkan selama addon-nya mati, dan tetap tampil walau lisensi
        // aktif supaya pengguna tahu widget apa saja yang dimiliki RTMKit.
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
        // rkit-widget-locked: penanda gembok di panel editor (panel.css).
        // rkit-lock-rtmform: terkunci karena plugin RTMForm, bukan lisensi (rtmwp.js).
        $lock = ($this->widget_data['lock'] ?? '') === 'rtmform' ? ' rkit-lock-rtmform' : '';
        return 'rkit-widget-icon rkit-widget-locked' . $lock . ' ' . $icon;
    }

    /**
     * Halaman untuk mengaktifkan RTMForm, atau memasangnya kalau belum ada.
     */
    public static function rtmform_url()
    {
        return file_exists(WP_PLUGIN_DIR . '/romethemeform/rometheme-form.php')
            ? admin_url('plugins.php?plugin_status=inactive')
            : admin_url('plugin-install.php?s=romethemeform&tab=search&type=term');
    }

    protected function render_pro_banner($widget_label)
    {
        if (($this->widget_data['lock'] ?? '') !== 'rtmform') {
            parent::render_pro_banner($widget_label);
            return;
        }

        $installed = file_exists(WP_PLUGIN_DIR . '/romethemeform/rometheme-form.php');
        ?>
        <div class="rkit-pro-banner-wrapper" style="border: 2px dashed #00cea6; padding: 30px; text-align: center; background: #f9f8ff; border-radius: 8px;">
            <h4 style="margin: 0 0 10px 0; color: #23282d; font-size: 24px;">
                <?php echo esc_html(sprintf(__('%s needs the RTMForm plugin', 'rometheme-for-elementor'), $widget_label)); ?>
            </h4>
            <p style="margin: 0 0 20px 0; color: #646970; font-size: 14px;">
                <?php echo esc_html($installed
                    ? __('RTMForm is installed but not active. Activate it to build forms with this widget.', 'rometheme-for-elementor')
                    : __('Install the free RTMForm plugin to build forms with this widget.', 'rometheme-for-elementor')); ?>
            </p>
            <a href="<?php echo esc_url(self::rtmform_url()); ?>" target="_blank" rel="noopener" class="elementor-button" style="background: #00cea6; color: #fff; padding: 10px 20px; border-radius: 4px; text-decoration: none; font-weight: 600;">
                <?php echo esc_html($installed ? __('Activate RTMForm', 'rometheme-for-elementor') : __('Install RTMForm', 'rometheme-for-elementor')); ?>
            </a>
        </div>
        <?php
    }

    public function get_categories()
    {
        // Kategori yang sama dengan widget aslinya saat addon aktif (lihat
        // WidgetModule::ADDON_CATEGORIES); RTM Pro Widgets hanya cadangan.
        if (!empty($this->widget_data['elementor_category'])) {
            return (array) $this->widget_data['elementor_category'];
        }

        return ['romethemekit_widgets_pro'];
    }

    public function get_keywords()
    {
        return [$this->get_title(), 'pro', 'rtm'];
    }

    public function get_custom_help_url()
    {
        return isset($this->widget_data['docsURL']) ? $this->widget_data['docsURL'] : '';
    }

    protected function register_controls()
    {
    
    }
}
