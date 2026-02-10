<?php

namespace RTMKit\Themebuilder;

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

    public function init()
    {
        add_filter('template_include', [$this, 'single_post_template']);
        add_action('rmtkit/render_single_template', [$this, 'render_single_template']);
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
