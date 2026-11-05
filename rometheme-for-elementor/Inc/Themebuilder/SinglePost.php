<?php

namespace RTMKit\Themebuilder;

if ( ! defined( 'ABSPATH' ) ) exit;

class SinglePost
{
    public static function instance()
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * Widget Single Post versi free. Urutannya sesuai alur halaman artikel.
     */
    const WIDGETS = [
        \RTMKit\Elements\SinglePost\PostTitle::class,
        \RTMKit\Elements\SinglePost\PostImage::class,
        \RTMKit\Elements\SinglePost\PostContent::class,
        \RTMKit\Elements\SinglePost\PostInfo::class,
        \RTMKit\Elements\SinglePost\PostComment::class,
        \RTMKit\Elements\SinglePost\PostNavigation::class,
    ];

    public function init()
    {
        add_filter('template_include', [$this, 'single_post_template']);
        add_action('rmtkit/render_single_template', [$this, 'render_single_template']);
        add_action('elementor/elements/categories_registered', [$this, 'register_category']);
        // Prioritas 5: rtm-blog (prioritas default) meregistrasi ulang nama yang
        // sama sesudahnya, jadi versi Pro-nya yang dipakai saat rtm-blog aktif.
        add_action('elementor/widgets/register', [$this, 'register_widgets'], 5);
    }

    public function register_category($elements_manager)
    {
        $elements_manager->add_category(\RTMKit\Elements\SinglePost\SinglePostWidget::CATEGORY, [
            'title' => esc_html__('RTM Single Post', 'rometheme-for-elementor'),
        ]);
    }

    public function register_widgets($widgets_manager)
    {
        foreach (self::WIDGETS as $class) {
            $widgets_manager->register(new $class());
        }
    }

    public function single_post_template($template)
    {
        if (is_single()) {
            // Check if Elementor is active and if we're in Elementor editor
            if (defined('ELEMENTOR_PATH') && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
                return $template; // Return the original template if we're in Elementor editor
            }

            $custom_template_posts = \RTMKit\Modules\Themebuilder\ThemebuilderStorage::instance()->get_themebuilder_template('single_post');

            // Check if custom single template exists
            if (!empty($custom_template_posts)) {
                $single_post_template_path = RTM_KIT_DIR . 'Inc/Themebuilder/templates/single-post-template.php';

                // Check if the custom single template file exists
                if (file_exists($single_post_template_path)) {
                    return $single_post_template_path; // Return the custom single template file
                }
            }
        }


        // Return the original template if no custom single template is found
        return $template;
    }

    public static function render_single_template()
    {
        $tmp = \RTMKit\Modules\Themebuilder\ThemebuilderStorage::instance()
            ->get_themebuilder_template('single_post');

        // Pastikan hasilnya adalah objek post valid
        if ($tmp) {
            foreach ($tmp as $post) {
                // Setup post data
                setup_postdata($post);

                // Ambil ID template
                $id = $post->ID;

                // Dapatkan konten dari Elementor
                $elementor_content = \RTMKit\Modules\Themebuilder\ThemebuilderStorage::instance()->get_themebuilder_content($id);

                // Filter sementara untuk menimpa konten single post
                add_filter('the_content', function ($content) use ($elementor_content) {
                    return $elementor_content;
                });
            }
            // Reset post data after rendering
            wp_reset_postdata();
        } else {
            echo '<p>No Template Found.</p>';
        }
    }
}
