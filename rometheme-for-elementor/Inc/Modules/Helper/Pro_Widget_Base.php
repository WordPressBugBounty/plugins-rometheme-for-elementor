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
        return class_exists('RTMKitPro\Core\Plugin');
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
        <div class="rkit-pro-banner-wrapper" style="border: 2px dashed #00cea6; padding: 30px; text-align: center; background: #f9f8ff; border-radius: 8px;">
            <div class="rkit-pro-banner-icon" style="font-size: 40px; margin-bottom: 15px;">
                <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M26.571 23.429V15.857C26.571 15.434 26.657 14.997 26.814 14.608C26.986 14.202 27.23 13.837 27.521 13.539C27.836 13.231 28.201 12.988 28.592 12.822C28.996 12.659 29.433 12.572 29.856 12.572H34.142C34.565 12.572 35.002 12.659 35.392 12.816C35.797 12.988 36.164 13.231 36.462 13.519C36.77 13.836 37.013 14.203 37.178 14.592C37.342 14.996 37.428 15.434 37.428 15.857V23.429H42.714C44.67 23.429 46.429 23.814 48 24.522V13.714C48 7.324 43 2.181 36.591 2.004L27.715 2C21.256 2 16.001 7.255 16.001 13.714V24.522C17.572 23.813 19.331 23.429 21.287 23.429H26.573H26.571Z" fill="#00CEA6" />
                    <path d="M42.714 25.429H21.285C13.639 25.429 9.42798 31.923 9.42798 43.714C9.42798 56.531 16.178 62 31.999 62C47.82 62 54.57 56.531 54.57 43.714C54.57 31.923 50.36 25.429 42.714 25.429ZM37.907 47.919C38.131 48.527 38.201 49.165 38.114 49.809C38.028 50.437 37.793 51.045 37.433 51.569C37.058 52.11 36.557 52.548 35.98 52.842C35.434 53.13 34.794 53.285 34.143 53.285H29.857C29.206 53.285 28.566 53.13 28.008 52.836C27.443 52.548 26.942 52.109 26.57 51.572C26.208 51.045 25.972 50.436 25.886 49.809C25.798 49.164 25.868 48.527 26.095 47.911L26.556 46.624C25.286 45.259 24.57 43.452 24.57 41.571C24.57 37.474 27.903 34.142 31.999 34.142C36.095 34.142 39.428 37.474 39.428 41.571C39.428 43.452 38.712 45.259 37.442 46.624L37.906 47.919H37.907Z" fill="#00CEA6" />
                </svg>
            </div>
            <h4 style="margin: 0 0 10px 0; color: #23282d;">
                <?php
                /* translators: %s: Widget label. */
                 printf(esc_html__('%s is a Premium Widget', 'rometheme-for-elementor'), esc_html($widget_label)); ?>
            </h4>
            <p style="margin: 0 0 20px 0; color: #646970; font-size: 14px;">
                <?php esc_html_e('Unlock this widget and get access to advanced features to power up your website.', 'rometheme-for-elementor'); ?>
            </p>
            <a href="https://rometheme.net/plugins/rtmkit/pricing/" target="_blank" class="rkit-btn-go-pro" style="background: #00cea6; color: #fff; padding: 10px 20px; border-radius: 4px; text-decoration: none; font-weight: bold; display: inline-block;">
                <?php esc_html_e('Upgrade to Pro', 'rometheme-for-elementor'); ?>
            </a>
        </div>
<?php
    }
}
