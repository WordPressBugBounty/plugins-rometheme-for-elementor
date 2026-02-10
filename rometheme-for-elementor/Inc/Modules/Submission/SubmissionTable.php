<?php

namespace RTMKit\Modules\Submission;

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class SubmissionTable extends \WP_List_Table
{
    private $datas;

    public function __construct()
    {
        parent::__construct([
            'singular' => __('Submission', 'rometheme-for-elementor'),
            'plural'   => __('Submissions', 'rometheme-for-elementor'),
            'ajax'     => false,
        ]);
    }

    /**
     * Custom Table Wrapper (sama seperti Themebuilder)
     */
    public function display()
    {
        $singular = $this->_args['singular'];
        $this->screen->render_screen_reader_content('heading_list');
?>
        <table class="wp-list-table <?php echo implode(' ', $this->get_table_classes()); ?>">
            <thead>
                <tr><?php $this->print_column_headers(); ?></tr>
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
                <tr><?php $this->print_column_headers(false); ?></tr>
            </tfoot>
        </table>
<?php
    }

    /**
     * Prepare Data
     */
    public function prepare_items()
    {
        $paged    = isset($_POST['paged']) ? absint($_POST['paged']) : 1;
        $per_page = get_option('posts_per_page');

        $status = isset($_POST['status']) && $_POST['status'] === 'trash'
            ? 'trash'
            : ['publish', 'draft', 'pending', 'private'];


        $args = [
            'post_type'      => 'romethemeform_entry',
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'post_status'    => $status,
        ];


        if (!empty($_POST['rform_id'])) {
            $args['meta_query'] = [
                [
                    'key'   => 'rform-entri-form-id',
                    'value' => sanitize_text_field($_POST['rform_id']),
                ]
            ];
        }

        $this->datas = new \WP_Query($args);

        $posts = [];
        $no = 0;

        if ($this->datas->have_posts()) {
            while ($this->datas->have_posts()) {
                $this->datas->the_post();

                $id = get_the_ID();
                $no++;

                $posts[] = [
                    'no'       => $no,
                    'ID'       => $id,
                    'title'    => get_the_title(),
                    'form_id'  => get_post_meta($id, 'rform-entri-form-id', true),
                    'status'   => get_post_status($id),
                    'date'     => get_the_date('Y/m/d H:i a'),
                    'referal_page' => get_post_meta($id, 'rform-entri-referal', true)
                ];
            }
            wp_reset_postdata();
        }

        $columns = $this->get_columns();
        $hidden  = [];

        $this->_column_headers = [$columns, $hidden];
        $this->items = $posts;
    }

    /**
     * Columns
     */
    public function get_columns()
    {
        return [
            'no'      => __('No', 'rometheme-for-elementor'),
            'title'   => __('Title', 'rometheme-for-elementor'),
            'form_name' => __('Form Name', 'rometheme-for-elementor'),
            'referal'  => __('Referal Page', 'rometheme-for-elementor'),
            'date'    => __('Date', 'rometheme-for-elementor'),
        ];
    }

    protected function get_table_classes()
    {
        return ['rtm-table'];
    }

    protected function get_views()
    {
        $current = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'all';

        $counts = wp_count_posts('romethemeform_entry');
        $all_count   = count(get_posts(['post_type' => 'romethemeform_entry', 'post_per_page' => -1 ,  'post_status'    => ['publish', 'draft'],]));
        $trash_count = (int) $counts->trash;

        $base_url = admin_url('admin.php?page=rtm-submissions');

        $views = [];

        $views['all'] = sprintf(
            '<a href="%s" data-status="all" class="link table-link %s">%s <span class="count">(%d)</span></a>',
            esc_url($base_url),
            ($current === 'all' ? 'current' : ''),
            __('All', 'rometheme-for-elementor'),
            intval($all_count)
        );

        $views['trash'] = sprintf(
            '<a href="%s" data-status="trash" class="link table-link %s">%s <span class="count">(%d)</span></a>',
            esc_url(add_query_arg('status', 'trash', $base_url)),
            ($current === 'trash' ? 'current' : ''),
            __('Trash', 'rometheme-for-elementor'),
            intval($trash_count)
        );

        return $views;
    }


    protected function column_no($item)
    {
        return intval($item['no']);
    }

    /**
     * Title + Row Actions (disesuaikan Themebuilder)
     */
    protected function column_title($item)
    {
        $id     = $item['ID'];
        $status = get_post_status($id);

        if ($status === 'trash') {
            $actions = [
                'restore' => sprintf(
                    '<a class="link action-link" href="%s">%s</a>',
                    esc_url(wp_nonce_url(admin_url("post.php?action=untrash&post={$id}"), 'untrash-post_' . $id)),
                    __('Restore', 'rometheme-for-elementor')
                ),
                'delete' => sprintf(
                    '<a class="link-danger" href="%s">%s</a>',
                    esc_url(get_delete_post_link($id, '', true)),
                    __('Delete Permanently', 'rometheme-for-elementor')
                ),
            ];
        } else {
            $actions = [
                'view' => sprintf(
                    '<a class="link action-link" href="%s" data-entries-detail="%s">%s</a>',
                    esc_url(admin_url('admin.php?page=rtmkit&path=submission&entries_id=' . $id)),
                    esc_attr($id),
                    __('View', 'rometheme-for-elementor')
                ),
                'delete' => sprintf(
                    '<a class="link-danger" href="%s">%s</a>',
                    esc_url(get_delete_post_link($id)),
                    __('Trash', 'rometheme-for-elementor')
                ),
            ];
        }

        return sprintf(
            '%1$s %2$s',
            esc_html($item['title'] ?: '(no title)'),
            $this->row_actions($actions, true)
        );
    }

    protected function column_form_name($item)
    {
        return get_the_title($item['form_id']);
    }

    protected function column_referal($item)
    {
        $referalPageName = get_the_title($item['referal_page']);
        $referalPageURI = get_the_permalink($item['referal_page']);

        return '<a href="' . esc_url($referalPageURI) . '" >' . esc_html($referalPageName) . ' </a>';
    }

    /**
     * Default Column Renderer
     */
    protected function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'date':
                return '<div class="d-flex flex-column">
                            <div>' . esc_html(ucwords($item['status'])) . '</div>
                            <div>' . esc_html($item['date']) . '</div>
                        </div>';

            default:
                return '';
        }
    }
}
