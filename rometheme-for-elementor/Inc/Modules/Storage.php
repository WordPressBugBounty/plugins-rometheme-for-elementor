<?php

namespace RTMKit\Modules;

class Storage
{
    protected static $instance;
    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        $this->update_options();
        if (wp_doing_ajax()) {
            add_action('wp_ajax_save_modules', [$this, 'save_modules']);
            add_action('wp_ajax_reset_modules', [$this, 'reset_modules']);
            add_action('wp_ajax_get_specific_posts', [$this, 'get_specific_posts']);
        }
    }

    private function update_options()
    {
        $options = get_option('rtm_modules');

        if ($options == false) {
            $dataJson = $this->get_modules_with_extension();
            update_option('rtm_modules', $dataJson);
        }
    }

    public function get_module_option()
    {
        $modules = get_option('rtm_modules', []);

        if (\RTMKit\Core\Plugin::instance()->pro_is_active()) {
            return array_merge($modules, get_option('rtm_modules_pro', []));
        }
        return $modules;
    }

    public function get_active_modules(array $args = [])
    {
        $module_options = $this->get_module_option(); // dari database (status aktif)
        $modules_meta   = $this->get_modules();       // dari file modules.json
        $active_modules = [];

        // default args
        $defaults = [
            'type'     => null,
            'category' => null,
            'key'      => null,   // filter by module key
            'search'   => null,   // keyword search
        ];
        $args = array_merge($defaults, $args);

        if (!is_array($modules_meta) || empty($modules_meta)) {
            return [];
        }

        foreach ($modules_meta as $key => $meta) {
            $module_option = isset($module_options[$key]) ? $module_options[$key] : [];

            // ambil status aktif dari database
            $is_active = isset($module_option['status']) && $module_option['status'];

            // filter tambahan
            $in_category = $args['category'] === null || (isset($meta['category']) && $meta['category'] === $args['category']);
            $in_type     = $args['type'] === null || (isset($meta['type']) && $meta['type'] === $args['type']);
            $in_key      = $args['key'] === null || $args['key'] === $key;
            $in_search   = true;

            // kalau ada search, cek ke semua string value dalam metadata
            if ($args['search'] !== null) {
                $in_search = false;
                foreach ($meta as $val) {
                    if (is_string($val) && stripos($val, $args['search']) !== false) {
                        $in_search = true;
                        break;
                    }
                }
            }

            if ($is_active && $in_category && $in_type && $in_key && $in_search) {
                // gabungkan metadata dari JSON + status dari database
                $active_modules[$key] = array_merge($meta, [
                    'status' => $is_active,
                ]);
            }
        }

        return $active_modules;
    }


    public function save_modules()
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }

        $rawInput = file_get_contents('php://input');
        $dataJson = json_decode($rawInput, true);
        $plugin = sanitize_text_field($_GET['type']);
        // Validasi format JSON
        if (!is_array($dataJson)) {
            wp_send_json_error([
                'message' => 'Invalid JSON format',
                'raw'     => $rawInput
            ], 400);
        }

        $update = $this->save_modules_options($plugin, $dataJson);

        if ($update) {
            $message = sprintf(
                __('Modules options for %s have been successfully updated.', 'rometheme-for-elementor'),
                ($plugin == 'free') ? 'RTMKit' : 'RTMKit Pro'
            );
            wp_send_json_success(['message' => $message]);
        } else {
            $message = sprintf(
                __('No changes were saved. %s Modules options are already current.', 'rometheme-for-elementor'),
                ($plugin == 'free') ? 'RTMKit' : 'RTMKit Pro'
            );
            wp_send_json_error([
                'message' => __($message, 'rometheme-for-elementor')
            ]);
        }
    }

    private function save_modules_options($plugin, $data)
    {
        $options = ($plugin == 'free') ? 'rtm_modules' : 'rtm_modules_pro';

        $currentOptions = get_option($options);

        foreach ($data as $key => $value) {
            $module = sanitize_key($key);
            $currentOptions[$module]['status'] = $value;
        }

        $update = update_option($options, $currentOptions);
        return $update;
    }

    public function reset_modules()
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'rometheme-for-elementor')], 403);
        }

        $error_message = __('Reset failed due to an unexpected error. Please try again, or contact support if the issue persists.', 'rometheme-for-elementor');

        $success = false;

        // Reset free modules
        if (delete_option('rtm_modules')) {
            $dataJson = $this->get_modules_with_extension();
            if (update_option('rtm_modules', $dataJson)) {
                $success = true;
            }
        }

        // Reset pro modules if RTMKitPro\Core\Plugin exists
        if (class_exists('\RTMKitPro\Core\Plugin')) {
            if (delete_option('rtm_modules_pro')) {
                $proDataJson = $this->get_pro_modules();
                if (update_option('rtm_modules_pro', $proDataJson)) {
                    $success = true;
                }
            }
        }

        if ($success) {
            wp_send_json_success([
                'message' => __('All Modules have been reset to default settings.', 'rometheme-for-elementor')
            ]);
        }

        wp_send_json_error(['message' => $error_message]);
    }

    public function get_modules()
    {
        $modules = [];

        // Load main modules
        $modules_file = RTM_KIT_DIR . 'metadata/modules.json';
        $extensions_file = RTM_KIT_DIR . 'metadata/extensions.json';
        if (file_exists($modules_file) && file_exists($extensions_file)) {
            $modules_json = file_get_contents($modules_file);
            $extensions_json = file_get_contents($extensions_file);
            $main_modules = json_decode($modules_json, true);
            $extensions_modules = json_decode($extensions_json, true);
            $modules = array_merge($main_modules, $extensions_modules);
        }
        return $modules;
    }

    public function get_modules_with_extension()
    {
        $modules = $this->get_modules();
        $extension_storage = \RTMKit\Modules\Extensions\ExtensionStorage::instance();
        $extensions = $extension_storage->get_extension();

        $modules = array_merge(
            $modules,
            array_diff_key($extensions, $modules)
        );

        return $modules;
    }

    public function get_pro_modules()
    {
        $pro_modules_file = RTM_KIT_DIR . 'metadata/pro_modules.json';
        if (file_exists($pro_modules_file)) {
            $pro_modules_json = file_get_contents($pro_modules_file);
            $pro_modules = json_decode($pro_modules_json, true);
            return $pro_modules;
        }
        return [];
    }

    public function get_all_modules()
    {
        $modules = $this->get_modules_with_extension();
        $pro_modules = $this->get_pro_modules();

        return array_merge($modules, $pro_modules);
    }

    public function get_specific_posts()
    {
        check_ajax_referer('rtmkit_nonce', 'nonce');

        $type    = isset($_POST['post_type']) ? sanitize_key($_POST['post_type']) : 'post';
        $search  = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $paged   = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;

        $results = [];

        switch ($type) {
            /**
             * 🔹 Post, Page, Product, CPT
             */
            case 'post':
            case 'page':
            case 'product':
            default:

                // 🟩 Jika ada post_id, ambil langsung
                if ($post_id) {
                    $post = get_post($post_id);
                    if ($post && $post->post_type === $type) {
                        $results[] = [
                            'id'   => $post->ID,
                            'text' => html_entity_decode(get_the_title($post)),
                        ];
                    }
                    wp_send_json_success($results);
                }

                // 🟦 Jika tidak ada post_id, lakukan pencarian normal
                $query = new \WP_Query([
                    'post_type'      => $type,
                    's'              => $search,
                    'posts_per_page' => 10,
                    'paged'          => $paged,
                ]);

                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        $results[] = [
                            'id'   => get_the_ID(),
                            'text' => html_entity_decode(get_the_title()),
                        ];
                    }
                    wp_reset_postdata();
                }
                break;

            /**
             * 🔹 Author
             */
            case 'author':
                if ($post_id) {
                    $user = get_user_by('id', $post_id);
                    if ($user) {
                        $results[] = [
                            'id'   => $user->ID,
                            'text' => $user->display_name,
                        ];
                    }
                    wp_send_json_success($results);
                }

                $args = [
                    'number'         => 10,
                    'search'         => '*' . esc_attr($search) . '*',
                    'search_columns' => ['user_login', 'user_nicename', 'display_name'],
                ];
                $users = get_users($args);
                foreach ($users as $user) {
                    $results[] = [
                        'id'   => $user->ID,
                        'text' => $user->display_name,
                    ];
                }
                break;

            /**
             * 🔹 Categories / Product Categories
             */
            case 'category':
            case 'product_cat':
                if ($post_id) {
                    $term = get_term($post_id, $type);
                    if ($term && !is_wp_error($term)) {
                        $results[] = [
                            'id'   => $term->term_id,
                            'text' => $term->name,
                        ];
                    }
                    wp_send_json_success($results);
                }

                $terms = get_terms([
                    'taxonomy'   => $type,
                    'hide_empty' => false,
                    'number'     => 10,
                    'search'     => $search,
                ]);
                if (!is_wp_error($terms)) {
                    foreach ($terms as $term) {
                        $results[] = [
                            'id'   => $term->term_id,
                            'text' => $term->name,
                        ];
                    }
                }
                break;

            /**
             * 🔹 Tags / Product Tags
             */
            case 'post_tag':
            case 'product_tag':
                if ($post_id) {
                    $term = get_term($post_id, $type);
                    if ($term && !is_wp_error($term)) {
                        $results[] = [
                            'id'   => $term->term_id,
                            'text' => $term->name,
                        ];
                    }
                    wp_send_json_success($results);
                }

                $terms = get_terms([
                    'taxonomy'   => $type,
                    'hide_empty' => false,
                    'number'     => 10,
                    'search'     => $search,
                ]);
                if (!is_wp_error($terms)) {
                    foreach ($terms as $term) {
                        $results[] = [
                            'id'   => $term->term_id,
                            'text' => $term->name,
                        ];
                    }
                }
                break;
        }

        wp_send_json_success($results);
    }
}
