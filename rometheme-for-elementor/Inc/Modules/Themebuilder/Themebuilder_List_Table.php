<?php

namespace RTMKit\Modules\Themebuilder;

if (! defined('ABSPATH')) exit;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Themebuilder_List_Table extends \WP_List_Table
{

    private $datas;

    public function __construct()
    {
        parent::__construct([
            'singular' => __('Themebuilder', 'rometheme-for-elementor'),
            'plural' => __('Themebuilders', 'rometheme-for-elementor'),
            'ajax' => false,
        ]);
    }

    /**
     * Tipe yang ditampilkan: tab yang dipilih, atau untuk tab "All" di halaman
     * builder (mis. Blog Builder) hanya tipe-tipe halaman itu, yang dikirim JS
     * sebagai themebuilder_scope. Null berarti semua tipe.
     *
     * @return string|string[]|null
     */
    private function requested_types()
    {
        $type = isset($_POST['themebuilder']) ? sanitize_text_field(wp_unslash($_POST['themebuilder'])) : 'all';

        if ($type !== 'all') {
            return $type;
        }

        $scope = isset($_POST['themebuilder_scope'])
            ? array_map('sanitize_key', explode(',', wp_unslash($_POST['themebuilder_scope'])))
            : [];
        $scope = array_values(array_diff(array_filter($scope), ['all', 'form']));

        return $scope ?: null;
    }

    public function get_views()
    {
        $args = [];

        $types = $this->requested_types();
        if ($types !== null) {
            $args['themebuilder'] = $types;
        }

        if (isset($_POST['status'])) {
            $args['status'] = sanitize_text_field($_POST['status']);
        }

        $this->datas = \RTMKit\Modules\Themebuilder\ThemebuilderStorage::instance()
            ->get_themebuilder_data($args);
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'all';
        $base_url = remove_query_arg(['status', 'paged']);

        $counts = \RTMKit\Modules\Themebuilder\ThemebuilderStorage::instance()
            ->get_themebuilder_count($args);

        $views = [];
        foreach ($counts as $key => $count) {
            $class = ($status === $key) ? 'current' : '';
            $url = add_query_arg('status', $key, $base_url);

            $views[$key] = sprintf(
                '<a  class="link table-link %s" data-status="%s">%s <span class="count">(%d)</span></a>',
                // esc_url($url),
                $class,
                $key,
                ucfirst($key),
                $count
            );
        }

        return $views;
    }

    public function render_custom_pagination()
    {
        $this->pagination('top'); // atau 'bottom'
    }

    public function display()
    {
        $singular = $this->_args['singular'];
        $this->screen->render_screen_reader_content('heading_list');
        $this->display_tablenav('top');
?>
        <table class="wp-list-table <?php echo esc_attr(implode(' ', $this->get_table_classes())); ?>">
            <?php $this->print_table_description(); ?>
            <thead>
                <tr>
                    <?php $this->print_column_headers(); ?>
                </tr>
            </thead>

            <tbody id="the-list" <?php
                                    if ($singular) {
                                        // echo " data-wp-lists='list:$singular'";
                                        echo ' data-wp-lists="list:' . esc_attr($singular) . '"';
                                    }
                                    ?>>
                <?php $this->display_rows_or_placeholder(); ?>
            </tbody>

            <tfoot>
                <tr>
                    <?php $this->print_column_headers(false); ?>
                </tr>
            </tfoot>

        </table>

        <?php $this->display_tablenav('bottom'); ?>
<?php
    }

    public function prepare_items()
    {
        $this->process_bulk_action();

        $args = [];
        $woo_types = ['single_product', 'archive_product'];
        $has_woocommerce = is_plugin_active('woocommerce/woocommerce.php');

        $pro_types = ['error_404', 'archive_post', 'search', 'archive', 'search_results'];

        if (isset($_POST['status']) && $_POST['status'] !== 'all') {
            $args['status'] = sanitize_text_field($_POST['status']);
        }

        $this->datas = \RTMKit\Modules\Themebuilder\ThemebuilderStorage::instance()
            ->get_themebuilder_data($args);

        $types = $this->requested_types();
        if ($types !== null) {
            $args['themebuilder'] = $types;
        }

        if (isset($_POST['status'])) {
            $args['status'] = sanitize_text_field($_POST['status']);
        }

        $this->datas = \RTMKit\Modules\Themebuilder\ThemebuilderStorage::instance()
            ->get_themebuilder_data($args);

        $posts = [];
        if ($this->datas->have_posts()) {
            $no = 0;
            while ($this->datas->have_posts()) {
                $this->datas->the_post();
                $id_post = intval(get_the_ID());
                $type = get_post_meta($id_post, 'rometheme_template_type', true);
                $no++;
                if (in_array($type, $pro_types, true)) {
                    if (\RTMKit\Core\Plugin::instance()->pro_is_active()) {
                        //do nothing
                    } else {
                        continue;
                    }
                }
                if (in_array($type, $woo_types, true)) {
                    if (
                        \RTMKit\Core\Plugin::instance()->pro_is_active() &&
                        $has_woocommerce
                    ) {
                        //do nothing
                    } else {
                        continue;
                    }
                }
                if ($_POST['themebuilder'] === 'form') {
                    $entries = \RomethemeForm\Form\Form::count_entries($id_post);
                    $shortcode = get_post_meta($id_post, 'rtform_shortcode', true);
                    $status = (get_post_status($id_post) === 'publish') ? 'published' : get_post_status($id_post);
                    $posts[] = [
                        // 'no' => $no,
                        'ID' => $id_post,
                        'title' => get_the_title(),
                        'author' => get_the_author(),
                        'shortcode' => $shortcode,
                        'entries' => $entries,
                        'status' => $status,
                        'date' => get_the_date('Y/m/d H:i a'),
                    ];
                } else {
                    $active = get_post_meta($id_post, 'rometheme_template_active', true);
                    $condition = get_post_meta($id_post, 'rometheme_template_condition', true);
                    $status = (get_post_status($id_post) === 'publish') ? 'published' : get_post_status($id_post);
                    $posts[] = [
                        // 'no' => $no,
                        'ID' => $id_post,
                        'title' => get_the_title(),
                        'author' => get_the_author(),
                        'type' => $type,
                        'active' => $active,
                        'status' => $status,
                        'display' => $condition,
                        'date' => get_the_date('Y/m/d H:i a'),
                    ];
                }
            }
        }

        $columns = $this->get_columns();
        $hidden = [];

        $this->_column_headers = [$columns, $hidden];
        $this->items = $posts;
    }

    public function get_columns()
    {
        if ($_POST['themebuilder'] === 'form') {
            $fields = [
                'cb' => '<input type="checkbox" />',
                // 'no' => __('No', 'rometheme-for-elementor'),
                'title' => __('Title', 'rometheme-for-elementor'),
                'shortcode' => __('Shortcode', 'rometheme-for-elementor'),
                'entries' => __('Entries', 'rometheme-for-elementor'),
                'author' => __('Author', 'rometheme-for-elementor'),
                'date' => __('Date', 'rometheme-for-elementor'),
            ];
        } else {
            $fields = [
                'cb' => '<input type="checkbox" />',
                // 'no' => __('No', 'rometheme-for-elementor'),
                'title' => __('Title', 'rometheme-for-elementor'),
                'type' => __('Type', 'rometheme-for-elementor'),
                'display' => __('Display', 'rometheme-for-elementor'),
            ];

            // Kolom dari field tambahan (rtmkit_themebuilder_fields) milik tipe di tab ini.
            $fields += $this->get_extra_columns();

            $fields += [
                'author' => __('Author', 'rometheme-for-elementor'),
                'date' => __('Date', 'rometheme-for-elementor'),
            ];
        }

        return $fields;
    }

    /** Field tambahan yang tampil sebagai kolom untuk tab (tipe) yang sedang dibuka. */
    private function get_extra_column_fields(): array
    {
        $type = isset($_POST['themebuilder']) ? sanitize_text_field(wp_unslash($_POST['themebuilder'])) : 'all';

        if ($type === 'all') {
            return [];
        }

        return array_filter(
            \RTMKit\Modules\Themebuilder\ThemebuilderStorage::instance()->get_extra_fields_for_type($type),
            function ($field) {
                return !empty($field['column']);
            }
        );
    }

    private function get_extra_columns(): array
    {
        $columns = [];

        foreach ($this->get_extra_column_fields() as $key => $field) {
            $columns['extra_' . $key] = $field['label'] ?? $key;
        }

        return $columns;
    }


    /**
     * Get available bulk actions.
     *
     * @return array<string, string>
     */
    protected function get_bulk_actions()
    {
        $status = isset($_POST['status'])
            ? sanitize_key(wp_unslash($_POST['status']))
            : 'all';

        if ($status === 'trash') {
            return [
                'restore' => __('Restore', 'rometheme-for-elementor'),
                'delete'  => __('Delete Permanently', 'rometheme-for-elementor'),
            ];
        }

        return [
            'publish' => __('Publish', 'rometheme-for-elementor'),
            'draft'   => __('Move to Draft', 'rometheme-for-elementor'),
            'trash'   => __('Move to Trash', 'rometheme-for-elementor'),
        ];
    }

    /**
     * Render checkbox for selecting a template.
     *
     * @param array $item Current row item.
     * @return string
     */
    protected function column_cb($item)
    {
        $post_id = absint($item['ID']);

        return sprintf(
            '<input type="checkbox" name="themebuilder_ids[]" value="%d" />',
            $post_id
        );
    }


    /**
     * Process selected template bulk actions.
     *
     * @return void
     */
    public function process_bulk_action()
    {
        error_log('initialize');

        // if (
        //     'POST' !== strtoupper($_SERVER['REQUEST_METHOD'] ?? '')
        //     || empty($_POST['themebuilder_ids'])
        // ) {
        //     return;
        // }

        error_log('[RTMForm Pro] after : check request Method');

        $action = $this->current_action();

        error_log('[RTMForm Pro] action : ' . $action);

        $allowed_actions = [
            'publish',
            'draft',
            'trash',
            'restore',
            'delete',
        ];

        if (!in_array($action, $allowed_actions, true)) {
            return;
        }

        error_log('[RTMForm Pro] before : check nonce');

        check_admin_referer('_wpnonce');

        error_log('[RTMForm Pro] after : check nonce');


        $ids = array_unique(array_filter(array_map(
            'absint',
            (array) wp_unslash($_POST['themebuilder_ids'])
        )));

        $processed = 0;

        foreach ($ids as $post_id) {
            $post = get_post($post_id);

            error_log('action : ' . $action . ' for ' . $post_id);

            if (!$post || $post->post_type !== 'rometheme_template' || $post->post_type !==  'romethemeform_form') {
                continue;
            }

            switch ($action) {
                case 'publish':
                case 'draft':
                    if (
                        $post->post_status === 'trash'
                        || !current_user_can('edit_post', $post_id)
                    ) {
                        continue 2;
                    }

                    $result = wp_update_post([
                        'ID'          => $post_id,
                        'post_status' => $action === 'publish'
                            ? 'publish'
                            : 'draft',
                    ], true);

                    if (!is_wp_error($result) && $result) {
                        $processed++;
                    }
                    break;

                case 'trash':
                    if (
                        $post->post_status === 'trash'
                        || !current_user_can('delete_post', $post_id)
                    ) {
                        continue 2;
                    }

                    if (wp_trash_post($post_id)) {
                        $processed++;
                    }
                    break;

                case 'restore':
                    if (
                        $post->post_status !== 'trash'
                        || !current_user_can('delete_post', $post_id)
                    ) {
                        continue 2;
                    }

                    if (wp_untrash_post($post_id)) {
                        $processed++;
                    }
                    break;

                case 'delete':
                    if (
                        $post->post_status !== 'trash'
                        || !current_user_can('delete_post', $post_id)
                    ) {
                        continue 2;
                    }

                    if (wp_delete_post($post_id, true)) {
                        $processed++;
                    }
                    break;
            }
        }

        $redirect_url = remove_query_arg([
            'bulk_action',
            'bulk_count',
        ]);

        wp_safe_redirect(add_query_arg([
            'bulk_action' => $action,
            'bulk_count'  => $processed,
        ], $redirect_url));

        exit;
    }


    protected function get_table_classes()
    {
        return [
            'rtm-table'
        ];
    }

    protected function column_no($item)
    {
        return intval($item['no']);
    }
    protected function column_title($item)
    {
        $id_post = $item['ID'];
        $status = get_post_status($id_post);
        $data = [];
        if ($status === 'trash') {
            $restoreURL = \RTMKit\Modules\Themebuilder\ThemebuilderStorage::instance()->get_restore_post_link($id_post);
            $deleteURL = \RTMKit\Modules\Themebuilder\ThemebuilderStorage::instance()->get_delete_permanent_link($id_post);
            $actions = [
                'restore' => sprintf(
                    '<a class="link action-link" href="%s">%s</a>',
                    esc_url($restoreURL),
                    __('Restore', 'rometheme-for-elementor')
                ),
                'delete' => sprintf(
                    '<a href="%s" class="link-danger">%s</a>',
                    esc_url($deleteURL), // otomatis jadi "Delete Permanently"
                    __('Delete Permanently', 'rometheme-for-elementor')
                ),
            ];
        } else {
            // ✅ Kalau status bukan Trash → Edit / Elementor / Trash
            $edit_link = get_edit_post_link($id_post, 'display');
            $edit_elementor = str_replace('action=edit', 'action=elementor', $edit_link);

            if ($_POST['themebuilder'] === 'form') {
                $data['id'] = $id_post;
                $data['title'] = $item['title'];
                $data['success_msg'] = get_post_meta($id_post, 'rtform_form_success_message', true);
                $data['restricted'] = get_post_meta($id_post, "rtform_form_restricted", true);
                $data['confirmation'] = get_post_meta($id_post, 'rtform_email_confirmation', true);
                $data['notification'] = get_post_meta($id_post, 'rtform_email_notification', true);
                $data['entry_title'] = get_post_meta($id_post, "rtform_form_entry_title", true);
            } else {
                $data['id'] = $id_post;
                $data['title'] = $item['title'];
                $data['type'] = $item['type'];
                $data['active'] = $item['active'];
                $data['conditions'] = $item['display'];

                // Nilai field tambahan, untuk mengisi modal Edit.
                $data['meta'] = [];
                foreach (\RTMKit\Modules\Themebuilder\ThemebuilderStorage::instance()->get_extra_fields_for_type($item['type']) as $key => $field) {
                    $data['meta'][$key] = (string) get_post_meta($id_post, $field['meta_key'], true);
                }
            }

            $actions = [
                'edit' => sprintf(
                    '<a class="link action-link %sedit-link" href="%s" data="%s">%s</a>',
                    ($_POST['themebuilder'] === 'form') ? esc_attr('form-') : '',
                    esc_url($edit_link),
                    esc_attr(json_encode($data)),
                    __('Edit', 'rometheme-for-elementor')
                ),
                'elementor' => sprintf(
                    '<a class="link action-link" href="%s">%s</a>',
                    esc_url($edit_elementor),
                    __('Edit with Elementor', 'rometheme-for-elementor')
                ),
                'delete' => sprintf(
                    '<a href="%s" class="link-danger">%s</a>',
                    esc_url(get_delete_post_link($id_post)), // otomatis jadi "Trash"
                    __('Trash', 'rometheme-for-elementor')
                ),
            ];
        }

        if (isset($_POST['themebuilder']) && $_POST['themebuilder'] === 'form') {
            $badge = '<span class="badge rounded-pill text-bg-success mx-2">Active</span>';
        } else {
            $badge = ($item['active'] === 'true')
                ? '<span class="badge rounded-pill text-bg-success mx-2">Active</span>'
                : '<span class="badge rounded-pill text-bg-secondary mx-2">Inactive</span>';
        }

        return sprintf(
            '<div class="d-flex flex-row align-items-center"><h5 class="mb-0">%1$s</h5> %2$s </div> %3$s',
            esc_html($item['title']),
            $badge,
            $this->row_actions($actions, true)
        );
    }


    protected function column_default($item, $column_name)
    {
        if (strpos($column_name, 'extra_') === 0) {
            $field = $this->get_extra_column_fields()[substr($column_name, 6)] ?? null;
            $value = $field ? (string) get_post_meta($item['ID'], $field['meta_key'], true) : '';

            if ($value === '') {
                return '<span class="text-secondary">&mdash;</span>';
            }

            return ($field['type'] ?? 'text') === 'url'
                ? sprintf('<a href="%1$s" target="_blank" rel="noopener noreferrer" class="link text-break">%2$s</a>', esc_url($value), esc_html($value))
                : esc_html($value);
        }

        switch ($column_name) {
            case 'no':
                return $item['no'];

            case 'author':
                return esc_html($item['author']);

            case 'type':
                return esc_html(ucwords(str_replace('_', ' ', $item['type'])));

            case 'display':
                $text = $this->format_display_conditions_simple($item['display']);
                $spans = str_replace(',', '<br>', $text);
                return isset($item['display']) ? wp_kses_post($spans) : '';

            case 'date':
                return '<div class="d-flex flex-column">
                        <div>' . esc_html(ucwords($item['status'])) . '</div>
                        <div>' . esc_html($item['date']) . '</div>
                    </div>';
            case 'shortcode':
                return esc_html($item['shortcode']);
            case 'entries':
                $entries = $item['entries'];
                return '
                <div class="d-flex flex-row gap-3 align-items-center">
                    <button class="btn bg-gradient px-3 py-2 rounded-2" data-view-entries="' . esc_attr($item['ID']) . '">' . $entries . '</button>
                    <a href="" class="export-to-csv" data-form-id="' . esc_attr($item['ID']) . '" data-form-name="' . esc_attr($item['title']) . '">Export to CSV</a>
                </div>
                ';
            default:
                return print_r($item, true);
        }
    }

    function get_condition_mapping()
    {
        return [
            'all' => 'Entire Site',
            'entire' => 'Entire Site',
            'front_page' => 'Front Page',
            'error_404' => '404 Page',
            'archives' => [
                'all' => 'All Archives',
                'author' => 'Author Archive',
                'search' => 'Search Results',
                'post_archive' => 'Post Archive',
                'categories' => 'Categories',
                'tags' => 'Tags',
            ],
            'singular' => [
                'all' => 'All Singular',
                'front_page' => 'Front Page',
                'posts' => 'Posts',
                'post_category' => 'Post Category',
                'post_tag' => 'Post Tag',
                'post_author' => 'Post Author',
                'pages' => 'Pages',
                'page_author' => 'Page by Author',
                'author' => 'By Author',
            ],
            'woocommerce' => [
                'shop' => 'Shop Page',
                'product_archive' => 'Product Archive',
                'single_product' => 'Single Product',
                'product_categories' => 'Product Categories',
                'product_tags' => 'Product Tags',
                'product_author' => 'Product by Author',
            ],
        ];
    }

    function format_display_conditions_simple($conditions_json)
    {
        $conditions = $conditions_json;
        $map = $this->get_condition_mapping();

        if (empty($conditions) || !is_array($conditions)) {
            return 'Entire Site';
        }

        $labels = [];

        if (!empty($conditions['include'])) {
            foreach ($conditions['include'] as $rule) {
                if (is_array($rule)) {
                    if (isset($rule['sub']) && is_array($rule['sub'])) {
                        foreach ($rule['sub'] as $k => $r) {
                            if (!empty($map[$rule['page']][$k])) {
                                $labels[] = $map[$rule['page']][$k];
                            }
                        }
                    } elseif (!empty($map[$rule['page']])) {
                        $labels[] = $map[$rule['page']];
                    }
                } elseif (!empty($map[$rule])) {
                    $labels[] = $map[$rule];
                }
            }
        }

        // Jika tidak ada label sama sekali, tampilkan default
        if (empty($labels)) {
            return 'Entire Site';
        }

        return implode(', ', array_unique($labels));
    }
}
