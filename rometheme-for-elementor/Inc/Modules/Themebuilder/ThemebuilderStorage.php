<?php

namespace RTMKit\Modules\Themebuilder;

class ThemebuilderStorage
{
    private static ?ThemebuilderStorage $instance = null;

    private $themebuilder;

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

    /**
     * Field tambahan per tipe template, tampil di modal Add / Edit Theme Builder
     * dan disimpan sebagai post meta template.
     *
     * Format: key => [
     *   'label'       => string,
     *   'type'        => 'text' | 'url',
     *   'meta_key'    => string,
     *   'types'       => string[]  tipe template yang memakai field ini,
     *   'placeholder' => string (opsional),
     *   'description' => string (opsional),
     *   'column'      => bool     tampilkan sebagai kolom di tabel template,
     * ]
     */
    public function get_extra_fields(): array
    {
        $fields = apply_filters('rtmkit_themebuilder_fields', []);

        return array_filter(is_array($fields) ? $fields : [], function ($field) {
            return is_array($field) && !empty($field['meta_key']) && !empty($field['types']);
        });
    }

    /** Field tambahan yang berlaku untuk satu tipe template. */
    public function get_extra_fields_for_type($type): array
    {
        return array_filter($this->get_extra_fields(), function ($field) use ($type) {
            return in_array($type, (array) $field['types'], true);
        });
    }

    /** Sanitasi nilai field tambahan sesuai jenisnya. */
    public function sanitize_extra_field(array $field, $value): string
    {
        $value = is_string($value) ? wp_unslash($value) : '';

        return ($field['type'] ?? 'text') === 'url' ? esc_url_raw(trim($value)) : sanitize_text_field($value);
    }

    public function get_themebuilder()
    {
        if (!isset($this->themebuilder)) {
            $this->themebuilder = apply_filters('rtmkit_themebuilder', $this->default_themebuilder());
        }
        return $this->themebuilder;
    }

    public function default_themebuilder()
    {
        return [
            'header' => [
                'name' => 'header',
                'title' => 'Header'
            ],
            'footer' => [
                'name' => 'footer',
                'title' => 'Footer'
            ],
            // Single Post termasuk versi free (widgetnya ada di Inc/Elements/SinglePost).
            'single_post' => [
                'name' => 'single_post',
                'title' => 'Single Post'
            ]
        ];
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

        if (isset($arg['themebuilder']) && is_array($arg['themebuilder'])) {
            // Tab "All" di halaman builder. 'archive' adalah nama lama archive_post.
            $types = $arg['themebuilder'];
            if (in_array('archive_post', $types, true)) {
                $types[] = 'archive';
            }

            $args['meta_query']['meta_value'] = [
                'key' => 'rometheme_template_type',
                'value' => $types,
                'compare' => 'IN'
            ];
        } else if (isset($arg['themebuilder'])) {

            if ($arg['themebuilder'] == 'form') {
                $args['post_type'] = 'romethemeform_form';
            } else if ($arg['themebuilder'] == 'archive_post') {
                $args['meta_query']['meta_value'] = [
                    'key' => 'rometheme_template_type',
                    'value' => ['archive_post', 'archive'],
                    'compare' => 'IN'
                ];
            } else {
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
                'compare' => is_array($args['themebuilder']) ? 'IN' : '=',
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

    public function get_themebuilder_template($themebuilder)
    {
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

    public function get_themebuilder_content($post_id)
    {
        if (empty($post_id)) {
            return '';
        }
        $is_edit = \Elementor\Plugin::$instance->editor->is_edit_mode();
        $content = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display($post_id, $is_edit);
        return $content;
    }
}
