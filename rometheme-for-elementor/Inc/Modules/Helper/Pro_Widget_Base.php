<?php

namespace RTMKit\Modules\Helper;

use Elementor\Widget_Base;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

abstract class Pro_Widget_Base extends Widget_Base
{

    /**
     * Cek apakah versi Pro aktif
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
        // Jika Pro aktif, jalankan render asli dari class anak (Pro)
        if ($this->is_pro_active()) {
            $this->pro_render();
            return;
        }

        // Jika Pro TIDAK aktif, tampilkan Banner/Overlay Promosi
        $settings = $this->get_settings_for_display();
        $widget_label = $this->get_title();

        $this->render_pro_banner($widget_label);
    }

    /**
     * Tempat menaruh render asli di kelas Pro nantinya
     */
    protected function pro_render()
    {
        // Akan di-override oleh kelas Pro asli
    }

    public function is_editable()
    {
        return $this->is_pro_active();
    }

    public function get_custom_setting_args()
    {
        if (!$this->is_pro_active()) {
            return [
                'is_pro' => true,
                'access' => 'pro',
                // Ini yang bikin Elementor nge-block drag-and-drop pas Pro mati
            ];
        }
        return parent::get_custom_setting_args();
    }

    /**
     * Template Banner Promosi Pro
     */
    protected function render_pro_banner($widget_label)
    {
        ?>
        <div class="rkit-pro-banner-wrapper"
            style="border: 2px dashed #00cea6; padding: 30px; text-align: center; background: #f9f8ff; border-radius: 8px;">
            <div class="rkit-pro-banner-icon" style="font-size: 40px; margin-bottom: 15px;">
                <img src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/pro-features.png'); ?>" alt="Pro Icon" style="width: 280px; height: auto;">
            </div>
            <h4 style="margin: 0 0 10px 0; color: #23282d; font-size: 28px;">
                <?php
                /* translators: %s: Widget label. */
                printf(esc_html__('%s is a Premium Widget', 'rometheme-for-elementor'), esc_html($widget_label)); ?>
            </h4>
            <p style="margin: 0 0 20px 0; color: #646970; font-size: 14px;">
                <?php esc_html_e('Unlock this widget and get access to advanced features to power up your website.', 'rometheme-for-elementor'); ?>
            </p>
            <a href="https://store.rometheme.net/pricing/" target="_blank" class="rkit-btn-go-pro"
                style="background: #00cea6;color: #23282d;padding: 10px 20px;border-radius: 4px;text-decoration: none;font-weight: bold;display: inline-flex;gap: 5px;font-size: 14px;">
                <svg width="24px" height="24px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                    color="#000000">
                    <path d="M19.2 17L21 7L14.7 10L12 7L9.3 10L3 7L4.8 17H19.2Z" stroke="#000000" stroke-width="1.5"
                        stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <?php esc_html_e('Upgrade to Pro', 'rometheme-for-elementor'); ?>
            </a>
        </div>
        <?php
    }
}
