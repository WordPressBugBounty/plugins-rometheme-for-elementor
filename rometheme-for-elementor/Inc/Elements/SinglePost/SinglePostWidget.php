<?php

namespace RTMKit\Elements\SinglePost;

defined('ABSPATH') || exit;

use Elementor\Widget_Base;

/**
 * Dasar widget Single Post versi free. Widget-nya berasal dari rtm-blog dan
 * memakai nama widget yang sama, jadi template yang dibuat dengan versi free
 * tetap utuh saat rtm-blog aktif (rtm-blog meregistrasi ulang nama yang sama
 * sesudah RTMKit dan menggantikannya).
 */
abstract class SinglePostWidget extends Widget_Base
{
    const CATEGORY = 'romethemekit_single_post';

    /**
     * @return array{id: string, name: string, widget_name: string, icon?: string, tags?: string[], docsURL?: string, scripts?: array{css?: string}}
     */
    abstract public static function metadata(): array;

    public function get_name(): string
    {
        return static::metadata()['widget_name'];
    }

    public function get_title(): string
    {
        return static::metadata()['name'];
    }

    public function get_icon(): string
    {
        return !empty(static::metadata()['icon']) ? 'rkit-widget-icon ' . static::metadata()['icon'] : 'eicon-apps';
    }

    public function get_categories(): array
    {
        return [self::CATEGORY];
    }

    public function get_keywords(): array
    {
        return static::metadata()['tags'] ?? [];
    }

    public function get_custom_help_url()
    {
        return static::metadata()['docsURL'] ?? '';
    }

    public function get_style_depends(): array
    {
        $css = static::metadata()['scripts']['css'] ?? '';

        if ('' === $css) {
            return [];
        }

        $handle = 'rtmkit-sp-' . static::metadata()['id'] . '-css';

        // Didaftarkan saat dibutuhkan: Elementor meminta dependensi ini ketika
        // widget dirender, baik di frontend maupun di preview editor.
        if (!wp_style_is($handle, 'registered')) {
            wp_register_style($handle, RTM_KIT_URL . 'assets/css/single-post/' . $css, [], RTM_KIT_VERSION);
        }

        return [$handle];
    }

    public function widgetSlug(): string
    {
        return $this->get_name();
    }
}
