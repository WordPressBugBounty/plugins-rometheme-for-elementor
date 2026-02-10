<?php

namespace RTMKit\Modules\Themebuilder;

class ThemebuilderModule
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

        add_action('init', [$this, 'register_themebuilder_post_type'], 99);
        // add_action('admin_init', [$this, 'register_themebuilder_post_type']);
        \RTMKit\Modules\Themebuilder\ThemebuilderAPI::instance()->init();
        add_filter('single_template', array($this, 'load_canvas_template'));
        $this->load_themebuilder();
    }

    public function register_themebuilder_post_type()
    {
        $labels = array(
            'name'               => esc_html__('Rometheme Templates', 'rometheme-for-elementor'),
            'singular_name'      => esc_html__('Templates', 'rometheme-for-elementor'),
            'menu_name'          => esc_html__('Header Footer', 'rometheme-for-elementor'),
            'name_admin_bar'     => esc_html__('Header Footer', 'rometheme-for-elementor'),
            'add_new'            => esc_html__('Add New', 'rometheme-for-elementor'),
            'add_new_item'       => esc_html__('Add New Template', 'rometheme-for-elementor'),
            'new_item'           => esc_html__('New Template', 'rometheme-for-elementor'),
            'edit_item'          => esc_html__('Edit Template', 'rometheme-for-elementor'),
            'view_item'          => esc_html__('View Template', 'rometheme-for-elementor'),
            'all_items'          => esc_html__('All Templates', 'rometheme-for-elementor'),
            'search_items'       => esc_html__('Search Templates', 'rometheme-for-elementor'),
            'parent_item_colon'  => esc_html__('Parent Templates:', 'rometheme-for-elementor'),
            'not_found'          => esc_html__('No Templates found.', 'rometheme-for-elementor'),
            'not_found_in_trash' => esc_html__('No Templates found in Trash.', 'rometheme-for-elementor'),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'rewrite'             => false,
            'show_ui'             => true,
            'show_in_menu'        => false,
            'show_in_nav_menus'   => false,
            'exclude_from_search' => true,
            'capability_type'     => 'page',
            'map_meta_cap' => true,
            'hierarchical'        => false,
            'show_in_rest' => true,
            'supports'            => array('title', 'thumbnail', 'elementor'),
        );
        register_post_type('rometheme_template', $args);
    }

    function render_themebuilder_table_page()
    {
        $themebuilderTable = new Themebuilder_List_Table();
        $themebuilderTable->prepare_items();
?>
        <div class="card rounded-4">
            <div id="rtmtable">
                <form method="post">
                    <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>" />
                    <div class="d-flex flex-row justify-content-between mb-3">
                        <?php
                        $themebuilderTable->views();
                        $themebuilderTable->render_custom_pagination();
                        ?>
                    </div>
                    <?php
                    $themebuilderTable->display();
                    ?>
                </form>
            </div>
            <div class="mt-4">
                <span>Explore our <a href="https://support.rometheme.net/docs/romethemekit/" target="_blank">documentation</a> or <a href="https://www.youtube.com/@Rometheme_Studio" target="_blank">playlist</a> for full video tutorials.</span>
            </div>
        </div>
<?php
    }

    function load_canvas_template($single_template)
    {

        global $post;

        if ('rometheme_template' == $post->post_type) {

            $elementor_2_0_canvas = ELEMENTOR_PATH . '/modules/page-templates/templates/canvas.php';

            if (file_exists($elementor_2_0_canvas)) {
                return $elementor_2_0_canvas;
            } else {
                return ELEMENTOR_PATH . '/includes/page-templates/canvas.php';
            }
        }

        return $single_template;
    }

    public function check_condition($conditions)
    {
        if (empty($conditions)) {
            return true;
        }

        // Pastikan struktur include/exclude ada
        $includes = isset($conditions['include']) ? (array) $conditions['include'] : [];
        $excludes = isset($conditions['exclude']) ? (array) $conditions['exclude'] : [];

        // 🔹 Helper function: cek apakah kondisi cocok
        $match_condition = function ($cond) {

            if (empty($cond['page'])) {
                return false;
            }

            $page = $cond['page'];
            $sub  = isset($cond['sub']) ? $cond['sub'] : [];

            // === 🔸 ENTIRE SITE ===
            if ($page === 'entire') {
                return true;
            }

            // === 🔸 ARCHIVES ===
            if ($page === 'archives' && (is_archive() || is_home())) {
                if (empty($sub)) return true;

                foreach ($sub as $key => $val) {
                    switch ($key) {
                        case 'all':
                            return true;
                        case 'author':
                            if (is_author($val)) return true;
                            break;
                        case 'search':
                            if (is_search()) return true;
                            break;
                        case 'post_archive':
                            if (is_home()) return true;
                            break;
                        case 'categories':
                            if (is_category($val)) return true;
                            break;
                        case 'tags':
                            if (is_tag($val)) return true;
                            break;
                    }
                }
            }

            // === 🔸 SINGULAR ===
            if ($page === 'singular' && is_singular()) {
                if (empty($sub)) return true;

                foreach ($sub as $key => $val) {
                    switch ($key) {
                        case 'all':
                            return true;
                        case 'front_page':
                            if (is_front_page() || (is_home() && !is_paged())) return true;
                            break;

                        case 'posts':
                            if (get_post_type() === 'post' && (empty($val) || get_the_ID() == $val)) return true;
                            break;
                        case 'pages':
                            if (get_post_type() === 'page' && (empty($val) || get_the_ID() == $val)) return true;
                            break;
                        case 'post_category':
                            if (is_single() && has_category($val)) return true;
                            break;
                        case 'post_tag':
                            if (is_single() && has_tag($val)) return true;
                            break;
                        case 'post_author':
                            if (is_single() && get_post_field('post_author', get_the_ID()) == $val) return true;
                            break;
                        case 'page_author':
                            if (is_page() && get_post_field('post_author', get_the_ID()) == $val) return true;
                            break;
                        case 'author':
                            if (is_author($val)) return true;
                            break;
                    }
                }
            }

            // === 🔸 WOO ===
            if ($page === 'woocommerce' && function_exists('is_woocommerce')) {
                if (empty($sub)) return is_woocommerce();

                foreach ($sub as $key => $val) {
                    switch ($key) {
                        case 'shop':
                            if (is_shop()) return true;
                            break;
                        case 'product_archive':
                            if (is_post_type_archive('product')) return true;
                            break;
                        case 'single_product':
                            if (is_product() && (empty($val) || get_the_ID() == $val)) return true;
                            break;
                        case 'product_categories':
                            if (is_product_category($val)) return true;
                            break;
                        case 'product_tags':
                            if (is_product_tag($val)) return true;
                            break;
                        case 'product_author':
                            if (is_singular('product') && get_post_field('post_author', get_the_ID()) == $val) return true;
                            break;
                    }
                }
            }
            // === 🔸 404 ===
            if ($page === 'error_404' && is_404()) {
                return true;
            }

            return false;
        };
        // 🔹 Jika ada kondisi EXCLUDE yang cocok → langsung FALSE
        foreach ($excludes as $cond) {
            if ($match_condition($cond)) {
                return false;
            }
        }
        // 🔹 Jika ada kondisi INCLUDE yang cocok → TRUE
        foreach ($includes as $cond) {
            if ($match_condition($cond)) {
                return true;
            }
        }
        return true;
    }

    public function load_themebuilder()
    {
        $active_themebuilder = \RTMKit\Modules\Themebuilder\ThemebuilderStorage::instance()->get_active_themebuilder();

        if (empty($active_themebuilder)) {
            return;
        }

        foreach ($active_themebuilder as $themebuilder) {
            if ($themebuilder['type'] === 'pro') {
                continue;
            } else {
                $class = $themebuilder['class'];
                if (class_exists($class)) {
                    $instance = $class::instance();
                    if (method_exists($instance, 'init')) {
                        $instance->init();
                    }
                }
            }
        }
    }
}
