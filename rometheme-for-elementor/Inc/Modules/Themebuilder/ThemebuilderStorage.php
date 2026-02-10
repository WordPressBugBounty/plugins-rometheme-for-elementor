<?php

namespace RTMKit\Modules\Themebuilder;

class ThemebuilderStorage
{
    private static ?ThemebuilderStorage $instance = null;

    public static function instance(): ThemebuilderStorage
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        if (wp_doing_ajax()) {
            
        }
    }

    public function get_active_themebuilder()
    {
        $active_themebuilder = \RTMKit\Modules\Storage::instance()->get_active_modules(['category' => 'themebuilder']);
        return $active_themebuilder;
    }

    public function get_themebuilder_data(array $arg = [])
    {
        $args  = [
            'post_type' => 'rometheme_template',
            'posts_per_page' => -1,
        ];

        if (isset($arg['themebuilder'])) {

            if ($arg['themebuilder'] == 'form') {
                $args['post_type'] = 'romethemeform_form';
            } 
            else if($arg['themebuilder'] == 'archive_post'){
                $args['meta_query']['meta_value'] = [
                    'key' => 'rometheme_template_type',
                    'value' => ['archive_post', 'archive'],
                    'compare' => 'IN'
                ];
            }
            else {
                $args['meta_query']['meta_value'] = [
                    'key' => 'rometheme_template_type',
                    'value' => $arg['themebuilder'],
                    'compare' => '='
                ];
            }
        }

        if (isset($arg['status'])) {
            $args['post_status'] = $arg['status'];
        }

        $post_type = new \WP_Query($args);
        return $post_type;
    }

    public function get_themebuilder_count($args = [])
    {
        $base_args = [
            'post_type'      => 'rometheme_template',
            'posts_per_page' => -1,
            'fields'         => 'ids', // biar enteng
        ];

        // filter type
        if (isset($args['themebuilder'])) {
            $base_args['meta_query'][] = [
                'key'     => 'rometheme_template_type',
                'value'   => $args['themebuilder'],
                'compare' => '=',
            ];
        }

        $counts = [];

        foreach (['all', 'publish', 'draft', 'trash'] as $status) {
            $query_args = $base_args;

            if ($status !== 'all') {
                $query_args['post_status'] = $status;
            } else {
                $query_args['post_status'] = ['publish', 'draft', 'trash'];
            }

            $q = new \WP_Query($query_args);
            $counts[$status] = $q->found_posts;
        }

        return $counts;
    }

    public function get_restore_post_link($post_id)
    {
        $nonce = wp_create_nonce('untrash-post_' . $post_id);
        $url = wp_nonce_url(admin_url('post.php?post=' . $post_id . '&action=untrash'), 'untrash-post_' . $post_id, '_wpnonce');
        return $url;
    }

    public function get_delete_permanent_link($post_id)
    {
        $nonce = wp_create_nonce('delete-post_' . $post_id);
        $url = wp_nonce_url(admin_url('post.php?post=' . $post_id . '&action=delete'), 'delete-post_' . $post_id, '_wpnonce');
        return $url;
    }

    public function get_themebuilder_template($themebuilder) {
        $args = [
            'post_type' => 'rometheme_template',
            'post_status' => 'publish',
            'meta_query' => [
                'relations' => 'AND',
                [
                    'key' => 'rometheme_template_type',
                    'value' => $themebuilder,
                ],
                [
                    'key' => 'rometheme_template_active',
                    'value' => 'true'
                ]
            ]
        ];
        $template = get_posts($args);
        return $template;
    }

    public function get_themebuilder_content($post_id) {
        if (empty($post_id)) {
            return '';
        }
        $is_edit = \Elementor\Plugin::$instance->editor->is_edit_mode();
        $content = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($post_id , $is_edit);
        return $content;
    }
}
