<?php

namespace RTMKit\Modules\Helper;

if (! defined('ABSPATH')) exit;

/**
 * Konten remote untuk halaman Dashboard: daftar "What's New" dari RSS blog
 * Rometheme dan daftar WordPress theme Rometheme untuk carousel Recommended Themes.
 *
 * Keduanya di-cache di transient, termasuk saat gagal (disimpan sebagai array
 * kosong), supaya dashboard tidak menunggu request yang sama di setiap load
 * ketika rometheme.net sedang tidak bisa dijangkau.
 */
class DashboardFeed
{
    const CHANGELOG_URL = 'https://rometheme.net/changelog/';
    const BLOG_FEED_URL = 'https://rometheme.net/blog/feed/';
    const THEMES_API_URL = 'https://rometheme.net/wp-json/wc/store/v1/products';
    const THEMES_CATEGORY = 'wordpress-themes';
    const THEMES_URL = 'https://rometheme.net/wordpress-themes/';

    const CACHE_TTL = 12 * HOUR_IN_SECONDS;
    const FAILED_CACHE_TTL = 3 * HOUR_IN_SECONDS;

    protected static $instance;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Artikel terbaru dari RSS blog, hanya judul dan deskripsi (tanpa gambar).
     *
     * @return array<int, array{title: string, description: string, link: string}>
     */
    public function get_whats_new($limit = 3)
    {
        $cache_key = 'rtmkit_dashboard_whats_new_' . $limit;
        $items = get_transient($cache_key);

        if (false !== $items) {
            return $items;
        }

        $items = [];

        include_once ABSPATH . WPINC . '/feed.php';
        $feed = fetch_feed(apply_filters('rtmkit_dashboard_blog_feed', self::BLOG_FEED_URL));

        if (! is_wp_error($feed)) {
            foreach ($feed->get_items(0, $limit) as $item) {
                // Deskripsi feed bisa membawa <img>/<figure>; yang ditampilkan hanya teksnya.
                $description = wp_strip_all_tags((string) $item->get_description(), true);

                $items[] = [
                    'title' => wp_strip_all_tags((string) $item->get_title()),
                    'description' => wp_trim_words(html_entity_decode($description, ENT_QUOTES, 'UTF-8'), 24),
                    'link' => (string) $item->get_permalink(),
                ];
            }
        }

        set_transient($cache_key, $items, $items ? self::CACHE_TTL : self::FAILED_CACHE_TTL);

        return $items;
    }

    /**
     * Seluruh produk kategori WordPress Themes di rometheme.net.
     *
     * @return array<int, array{name: string, subtitle: string, link: string, image: string}>
     */
    public function get_themes()
    {
        $cache_key = 'rtmkit_dashboard_themes';
        $themes = get_transient($cache_key);

        if (false !== $themes) {
            return $themes;
        }

        $themes = [];

        $response = wp_remote_get(add_query_arg([
            'category' => self::THEMES_CATEGORY,
            'per_page' => 50,
        ], self::THEMES_API_URL), ['timeout' => 10]);

        if (! is_wp_error($response) && 200 === wp_remote_retrieve_response_code($response)) {
            $products = json_decode(wp_remote_retrieve_body($response), true);

            foreach ((array) $products as $product) {
                if (empty($product['permalink'])) {
                    continue;
                }

                // "Luminex – Multipurpose IT Business ..." → nama "Luminex", sisanya jadi subjudul.
                $title = html_entity_decode(wp_strip_all_tags($product['name'] ?? ''), ENT_QUOTES, 'UTF-8');
                $parts = preg_split('/\s+[\x{2013}\x{2014}-]\s+/u', $title, 2);
                $image = $product['images'][0] ?? [];

                $themes[] = [
                    'name' => $parts[0],
                    'subtitle' => $parts[1] ?? '',
                    'link' => $product['permalink'],
                    'image' => $image['thumbnail'] ?? ($image['src'] ?? ''),
                ];
            }
        }

        set_transient($cache_key, $themes, $themes ? self::CACHE_TTL : self::FAILED_CACHE_TTL);

        return $themes;
    }
}
