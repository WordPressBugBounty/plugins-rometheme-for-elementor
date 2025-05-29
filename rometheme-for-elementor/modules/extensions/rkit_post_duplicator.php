<?php

namespace RomethemeKit;

if (! defined('ABSPATH')) exit;
class RkitDuplicator
{

    public function __construct()
    {
        add_action('admin_action_rkit_duplicate_post', [$this, 'duplicate_post']);
        add_filter('post_row_actions', [$this, 'add_duplicate_link'], 10, 2);
        add_filter('page_row_actions', [$this, 'add_duplicate_link'], 10, 2);
    }


    public function add_duplicate_link($actions, $post)
    {
        // Jangan tampilkan duplicator untuk Templates (elementor_library)
        if ($post->post_type === 'elementor_library') {
            return $actions;
        }

        if (current_user_can('edit_posts')) {
            $url = wp_nonce_url(
                admin_url('admin.php?action=rkit_duplicate_post&post=' . $post->ID),
                'rkit_duplicate_post_' . $post->ID
            );
            $actions['duplicate'] = '<a href="' . esc_url($url) . '" title="Duplicate this item">RTM Duplicator</a>';
        }
        return $actions;
    }

    public function duplicate_post()
    {
        if (! isset($_GET['post'], $_GET['_wpnonce'])) {
            wp_die('No post to duplicate has been supplied!');
        }

        $post_id = absint($_GET['post']);
        if (! current_user_can('edit_post', $post_id)) {
            wp_die('You are not allowed to duplicate this post.');
        }

        check_admin_referer('rkit_duplicate_post_' . $post_id);

        $post = get_post($post_id);

        $new_post = [
            'post_title'    => $post->post_title . ' (Copy)',
            'post_content'  => $post->post_content,
            'post_status'   => 'draft',
            'post_type'     => $post->post_type,
            'post_author'   => get_current_user_id(),
        ];

        $new_post_id = wp_insert_post($new_post);

        // Copy taxonomies
        $taxonomies = get_object_taxonomies($post->post_type);
        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_object_terms($post_id, $taxonomy, ['fields' => 'slugs']);
            wp_set_object_terms($new_post_id, $terms, $taxonomy, false);
        }

        // Copy post meta
        $meta_keys = get_post_custom_keys($post_id);
        if ($meta_keys) {
            foreach ($meta_keys as $meta_key) {
                $meta_values = get_post_meta($post_id, $meta_key, false);
                foreach ($meta_values as $meta_value) {
                    if ('_elementor_data' === $meta_key) {
                        $json = json_decode($meta_value, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $encoded = wp_slash(wp_json_encode($json));
                            update_post_meta($new_post_id, '_elementor_data', $encoded);
                        }
                    } else {
                        add_post_meta($new_post_id, $meta_key, maybe_unserialize($meta_value));
                    }
                }
            }
        }

        // Elementor flags
        // update_post_meta( $new_post_id, '_elementor_edit_mode', 'builder' );
        // update_post_meta( $new_post_id, '_wp_page_template', get_post_meta( $post_id, '_wp_page_template', true ) );

        // $template_type = get_post_meta( $post_id, '_elementor_template_type', true );
        // if ( $template_type ) {
        //     update_post_meta( $new_post_id, '_elementor_template_type', $template_type );
        // }

        // Rebuild Elementor Document
        if (class_exists('\Elementor\Plugin')) {
            $document = \Elementor\Plugin::$instance->documents->get($new_post_id);
            if ($document) {
                $document->save([
                    'settings' => $document->get_settings(),
                    'elements' => json_decode(get_post_meta($new_post_id, '_elementor_data', true), true),
                ]);
            }
        }

        // Redirect ke list, atau bisa ganti ke Elementor editor 
        wp_redirect(admin_url('edit.php?post_type=' . $post->post_type));
        exit;
    }
}
