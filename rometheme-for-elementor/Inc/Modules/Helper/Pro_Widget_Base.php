<?php

namespace RTMKit\Modules\Helper;

use Elementor\Widget_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Pengganti terkunci untuk widget addon (rtm-elements, rtm-woo, ...). Hanya
 * didaftarkan selama addon pemilik widget belum aktif, jadi selalu terkunci:
 * tanpa lisensi ajakannya upgrade, dengan lisensi ajakannya mengaktifkan addon.
 */
abstract class Pro_Widget_Base extends Widget_Base
{

    /**
     * Cek apakah lisensi Pro aktif
     */
    protected function is_pro_active()
    {
        return \RTMKit\Core\Plugin::instance()->pro_is_active();
    }

    /**
     * Method Render Utama milik Elementor
     */
    protected function render()
    {
        // Banner hanya untuk editor; pengunjung situs tidak perlu melihat promosi
        // di halaman yang masih memakai widget dari addon yang dimatikan.
        $elementor = \Elementor\Plugin::$instance;
        if (!$elementor->editor->is_edit_mode() && !$elementor->preview->is_preview_mode()) {
            return;
        }

        $this->render_pro_banner($this->get_title());
    }

    public function is_editable()
    {
        return false;
    }

    public function get_custom_setting_args()
    {
        return [
            'is_pro' => true,
            'access' => 'pro',
            // Ini yang bikin Elementor nge-block drag-and-drop selama terkunci
        ];
    }

    /**
     * Template Banner Promosi Pro
     */
    protected function render_pro_banner($widget_label)
    {
        if ($this->is_pro_active()) {
            /* translators: %s: Widget label. */
            $heading = sprintf(__('%s needs an RTMKit Addon', 'rometheme-for-elementor'), $widget_label);
            $description = __('This widget comes from an RTMKit addon that is not active on this site yet. Activate it from the Addons page to use it.', 'rometheme-for-elementor');
            $cta_url = admin_url('admin.php?page=rtmkit&path=addons');
            $cta_text = __('Activate Addon', 'rometheme-for-elementor');
        } else {
            /* translators: %s: Widget label. */
            $heading = sprintf(__('%s is a Premium Widget', 'rometheme-for-elementor'), $widget_label);
            $description = __('Unlock this widget and get access to advanced features to power up your website.', 'rometheme-for-elementor');
            $cta_url = \RTMKit\Modules\Telemetry\UpgradeLink::url('pro_widget.editor', 'hub');
            $cta_text = __('Upgrade to Pro', 'rometheme-for-elementor');
        }
        ?>
        <div class="rkit-pro-banner-wrapper"
            style="border: 2px dashed #00cea6; padding: 30px; text-align: center; background: #f9f8ff; border-radius: 8px;">
            <div class="rkit-pro-banner-icon" style="font-size: 40px; margin-bottom: 15px;">
                <img src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/pro-features.png'); ?>" alt="Pro Icon" style="width: 280px; height: auto;">
            </div>
            <h4 style="margin: 0 0 10px 0; color: #23282d; font-size: 28px;">
                <?php echo esc_html($heading); ?>
            </h4>
            <p style="margin: 0 0 20px 0; color: #646970; font-size: 14px;">
                <?php echo esc_html($description); ?>
            </p>
            <a href="<?php echo esc_url($cta_url); ?>" target="_blank" class="rkit-btn-go-pro"
                style="background: #00cea6;color: #23282d;padding: 10px 20px;border-radius: 4px;text-decoration: none;font-weight: bold;display: inline-flex;gap: 5px;font-size: 14px;">
                <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                    color="#000000">
                    <path d="M19.2 17L21 7L14.7 10L12 7L9.3 10L3 7L4.8 17H19.2Z" stroke="#000000" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <?php echo esc_html($cta_text); ?>
            </a>
        </div>
        <?php
    }
}
