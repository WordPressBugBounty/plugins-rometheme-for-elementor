<?php

namespace RTMKit\Modules\Themebuilder;

if (! class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class Themebuilder_List_Table extends \WP_List_Table
{

    private $datas;

    public function __construct()
    {
        parent::__construct([
            'singular' => __('Themebuilder', 'rometheme-for-elementor'),
            'plural'   => __('Themebuilders', 'rometheme-for-elementor'),
            'ajax'     => false,
        ]);
    }

    public function get_views()
    {
        $args = [];

        if (isset($_POST['themebuilder']) && $_POST['themebuilder'] !== 'all') {
            $args['themebuilder'] = sanitize_text_field($_POST['themebuilder']);
        }

        if (isset($_POST['status'])) {
            $args['status'] = sanitize_text_field($_POST['status']);
        }

        $this->datas = \RTMKit\Modules\Themebuilder\ThemebuilderStorage::instance()
            ->get_themebuilder_data($args);
        $status  = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'all';
        $base_url = remove_query_arg(['status', 'paged']);

        $counts = \RTMKit\Modules\Themebuilder\ThemebuilderStorage::instance()
            ->get_themebuilder_count($args);

        $views = [];
        foreach ($counts as $key => $count) {
            $class = ($status === $key) ? 'current' : '';
            $url   = add_query_arg('status', $key, $base_url);

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
?>
        <table class="wp-list-table <?php echo implode(' ', $this->get_table_classes()); ?>">
            <?php $this->print_table_description(); ?>
            <thead>
                <tr>
                    <?php $this->print_column_headers(); ?>
                </tr>
            </thead>

            <tbody id="the-list"
                <?php
                if ($singular) {
                    echo " data-wp-lists='list:$singular'";
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
<?php
    }

    public function prepare_items()
    {
        $args = [];
        $pro_types = ['error_404', 'archive_post', 'search' , 'archive' , 'single_post' , 'search_results'];

        if (isset($_POST['status']) && $_POST['status'] !== 'all') {
            $args['status'] = sanitize_text_field($_POST['status']);
        }

        $this->datas = \RTMKit\Modules\Themebuilder\ThemebuilderStorage::instance()
            ->get_themebuilder_data($args);

        if (isset($_POST['themebuilder']) && $_POST['themebuilder'] !== 'all') {
            $args['themebuilder'] = sanitize_text_field($_POST['themebuilder']);
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
                $id_post   = intval(get_the_ID());
                $type      = get_post_meta($id_post, 'rometheme_template_type', true);
                $no++;
                if (in_array($type, $pro_types, true)) {
                    if (class_exists('\\RTMKitPro\\Core\\Plugin') && \RTMKitPro\Modules\Licenses\LicenseStorage::instance()->isLicenseActive()) {
                        //do nothing
                    } else {
                        continue;
                    }
                }
                if ($_POST['themebuilder'] === 'form') {
                    $entries = \RomethemeForm\Form\Form::count_entries($id_post);
                    $shortcode = get_post_meta($id_post, 'rtform_shortcode', true);
                    $status    = (get_post_status($id_post) === 'publish') ? 'published' : get_post_status($id_post);
                    $posts[] = [
                        'no' => $no,
                        'ID'       => $id_post,
                        'title'    => get_the_title(),
                        'author'   => get_the_author(),
                        'shortcode'     => $shortcode,
                        'entries'   => $entries,
                        'status'   => $status,
                        'date'     => get_the_date('Y/m/d H:i a'),
                    ];
                } else {
                    $active    = get_post_meta($id_post, 'rometheme_template_active', true);
                    $condition    = get_post_meta($id_post, 'rometheme_template_condition', true);
                    $status    = (get_post_status($id_post) === 'publish') ? 'published' : get_post_status($id_post);
                    $posts[] = [
                        'no' => $no,
                        'ID'       => $id_post,
                        'title'    => get_the_title(),
                        'author'   => get_the_author(),
                        'type'     => $type,
                        'active'   => $active,
                        'status'   => $status,
                        'display' => $condition,
                        'date'     => get_the_date('Y/m/d H:i a'),
                    ];
                }
            }
        }

        $columns  = $this->get_columns();
        $hidden   = [];

        $this->_column_headers = [$columns, $hidden];
        $this->items           = $posts;
    }

    public function get_columns()
    {
        if ($_POST['themebuilder'] === 'form') {
            $fields =  [
                'no' => __('No', 'rometheme-for-elementor'),
                'title'   => __('Title', 'rometheme-for-elementor'),
                'shortcode'  => __('Shortcode', 'rometheme-for-elementor'),
                'entries'    => __('Entries', 'rometheme-for-elementor'),
                'author'  => __('Author', 'rometheme-for-elementor'),
                'date'    => __('Date', 'rometheme-for-elementor'),
            ];
        } else {
            $fields =  [
                'no' => __('No', 'rometheme-for-elementor'),
                'title'   => __('Title', 'rometheme-for-elementor'),
                'type'  => __('Type', 'rometheme-for-elementor'),
                'display'    => __('Display', 'rometheme-for-elementor'),
                'author'  => __('Author', 'rometheme-for-elementor'),
                'date'    => __('Date', 'rometheme-for-elementor'),
            ];
        }

        return $fields;
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
        $status  = get_post_status($id_post);
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
            $edit_link      = get_edit_post_link($id_post, 'display');
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
            }

            $actions = [
                'edit'   => sprintf(
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

        if ($_POST['themebuilder'] === 'form') {
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
                'all'           => 'All Archives',
                'author'        => 'Author Archive',
                'search'        => 'Search Results',
                'post_archive'  => 'Post Archive',
                'categories'    => 'Categories',
                'tags'          => 'Tags',
            ],
            'singular' => [
                'all'           => 'All Singular',
                'front_page'    => 'Front Page',
                'posts'         => 'Posts',
                'post_category' => 'Post Category',
                'post_tag'      => 'Post Tag',
                'post_author'   => 'Post Author',
                'pages'         => 'Pages',
                'page_author'   => 'Page by Author',
                'author'        => 'By Author',
            ],
            'woocommerce' => [
                'shop'              => 'Shop Page',
                'product_archive'   => 'Product Archive',
                'single_product'    => 'Single Product',
                'product_categories' => 'Product Categories',
                'product_tags'      => 'Product Tags',
                'product_author'    => 'Product by Author',
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
