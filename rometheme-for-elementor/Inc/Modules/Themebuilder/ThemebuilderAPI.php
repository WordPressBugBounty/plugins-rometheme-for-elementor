<?php

namespace RTMKit\Modules\Themebuilder;

class ThemebuilderAPI
{
    private static $instance = null;

    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        if (wp_doing_ajax()) {
            add_action('wp_ajax_get_themebuilder_table', [$this, 'get_themebuilder_table']);
            add_action('wp_ajax_add_themebuilder', [$this, 'add_themebuilder']);
            add_action('wp_ajax_edit_themebuilder', [$this, 'edit_themebuilder']);
            add_action('wp_ajax_install_requirements', [$this, 'install_requirements']);
        }
    }

    public function get_themebuilder_table()
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die('Access Denied');
        }
        if (!file_exists(RTM_KIT_DIR . 'views/themebuilder-content.php')) {
            wp_send_json_error('Sidebar view file not found.');
            return;
        }
        ob_start();
        require_once RTM_KIT_DIR . 'views/themebuilder-content.php';
        $content = ob_get_clean();
        wp_send_json_success($content);
    }

    public function add_themebuilder()
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');

        if (! current_user_can('publish_posts')) {
            wp_send_json_error('access denied', 403);
        }

        $data = [
            'post_author' => get_current_user_id(),
            'post_title' => sanitize_text_field($_POST['title']),
            'post_type' => 'rometheme_template',
            'post_status' => 'publish'
        ];

        $jsonConditions = stripslashes($_POST['conditions']);
        $condition = json_decode($jsonConditions, true);
        $post_id = wp_insert_post($data);
        $active = (!isset($_POST['active']) || sanitize_text_field($_POST['active']) == null) ? 'false' : 'true';

        if ($post_id) {
            add_post_meta($post_id, 'rometheme_template_type', sanitize_text_field($_POST['type']));
            add_post_meta($post_id, 'rometheme_template_active', $active);

            if (!empty($condition)) {
                add_post_meta($post_id, 'rometheme_template_condition', $condition);
            }

            if ($_POST['type'] == '404') {
                add_post_meta($post_id, '_elementor_template_type', 'error-404');
            } else {
                add_post_meta($post_id, '_elementor_template_type', $_POST['type']);
            }

            // Menambahkan respon JSON jika penyisipan posting berhasil
            wp_send_json_success('success');
        } else {
            // Menambahkan respon JSON jika penyisipan posting gagal
            wp_send_json_error('Failed Save Template');
        }
    }

    public function edit_themebuilder()
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');

        if (! current_user_can('edit_posts')) {
            wp_send_json_error('access denied', 403);
        }

        $post_id = intval($_POST['themebuilder_id']);
        $data = [
            'ID' => $post_id,
            'post_title' => sanitize_text_field($_POST['title']),
            'post_status' => 'publish'
        ];

        $jsonConditions = stripslashes($_POST['conditions']);
        $condition = json_decode($jsonConditions, true);
        $update_post = wp_update_post($data);

        $active = (!isset($_POST['active']) || sanitize_text_field($_POST['active']) == null ||  sanitize_text_field($_POST['active']) == 'null') ? 'false' : 'true';

        if ($update_post) {
            update_post_meta($post_id, 'rometheme_template_type', sanitize_text_field($_POST['type']));
            update_post_meta($post_id, 'rometheme_template_active', $active);

            if (!empty($condition)) {
                update_post_meta($post_id, 'rometheme_template_condition', $condition);
            } else {
                delete_post_meta($post_id, 'rometheme_template_condition');
            }

            if ($_POST['type'] == '404') {
                update_post_meta($post_id, '_elementor_template_type', 'error-404');
            } else {
                update_post_meta($post_id, '_elementor_template_type', $_POST['type']);
            }

            // Menambahkan respon JSON jika penyisipan posting berhasil
            wp_send_json_success('success');
        } else {
            // Menambahkan respon JSON jika penyisipan posting gagal
            wp_send_json_error('Failed Save Template');
        }
    }

    public function install_requirements()
    {
        if (!current_user_can('manage_options')) {
            wp_die();
        }

         check_ajax_referer('rtmkit_nonce', 'nonce');

        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        include_once ABSPATH . 'wp-admin/includes/file.php';
        include_once ABSPATH . 'wp-admin/includes/misc.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $plugin = $_POST['plugin'];
        $plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
        $plugin_slug = dirname($plugin);

        if (file_exists($plugin_file)) {
            // Activate the plugin if already installed but inactive
            ob_start();
            activate_plugin($plugin);
            ob_clean();
            ob_end_clean();
            wp_send_json_success("Install and Activate Successfully");
        } else {
            ob_start();
            $plugin_download_url = "https://downloads.wordpress.org/plugin/{$plugin_slug}.latest-stable.zip"; // Adjust URL structure
            $upgrader = new \Plugin_Upgrader();
            $result = $upgrader->install($plugin_download_url);

            if (is_wp_error($result)) {
                wp_send_json_error();
            }
            $activate_result = activate_plugin($plugin);
            if (is_wp_error($activate_result)) {
                wp_send_json_error('Plugin installed but failed to activate: ' . $activate_result->get_error_message());
            }

            wp_send_json_success('Plugin installed and activated successfully.');
        }
    }
}
