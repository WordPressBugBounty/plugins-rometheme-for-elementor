<?php
namespace RTMKit\MCP\Main;

final class Settings
{

    public static function init(): void
    {
        add_filter('rtmkit_menus', [self::class, 'add_to_menu']);
        add_action('admin_init', [self::class, 'register']);
    }

    public static function menu(): void
    {
        add_options_page('RTMKit MCP', 'RTMKit MCP', 'manage_options', 'rtmkit-mcp', [self::class, 'render']);
    }

    public static function register(): void
    {
        register_setting('rtmkit_mcp', RTMKIT_OPT_WRITE, [
            'type' => 'boolean',
            'default' => false,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);

        add_settings_section('rtmkit_mcp_main', '', '__return_false', 'rtmkit-mcp');
        add_settings_field(RTMKIT_OPT_WRITE, 'Write Operations', [self::class, 'field_write'], 'rtmkit-mcp', 'rtmkit_mcp_main');
    }

    public static function field_write(): void
    {
        printf(
            '<label class="text-white"><input type="checkbox" name="%1$s" value="1" %2$s> Allow Write Operations</label>
			<p class="text-muted">Default disabled (read-only). Changes are still limited by the <code>edit_post</code> capability of the connected user.</p>',
            esc_attr(RTMKIT_OPT_WRITE),
            checked((bool) get_option(RTMKIT_OPT_WRITE), true, false)
        );
    }

    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        ?>
        <style>
            .form-table th, .form-wrap label  {
                color: #fff;
            }
        </style>
        <div class="px-4 mb-5 scroll-behavior-smooth scrollspy" data-scrollspy="#widget-category"
            data-rootMargin="-30% 0px -70% 0px" tabindex="0"
            style="--wp-admin-theme-color: #00cea6;--wp-admin-theme-color-darker-10: #00cea6; color: #fff;">
            <h1>RTMKit MCP</h1>
            <p class="text-danger">This feature is in alpha stage and still under development. Results may be imperfect or unexpected.</p>
            <form method="post" action="options.php">
                <?php settings_fields('rtmkit_mcp');
                printf(
                    '<input type="hidden" name="_wp_http_referer" value="%s">',
                    esc_url(admin_url('admin.php?page=rtmkit&path=mcp'))
                );
                do_settings_sections('rtmkit-mcp');
                submit_button() ?>
            </form>
        </div>
        <?php
    }

    public static function add_to_menu($menus)
    {
        $menus['settings']['mcp'] = [
            'title' => __('MCP', 'rometheme-for-elementor'),
            'capability' => 'manage_options',
            'menu_slug' => 'rtmkit&path=mcp',
            'function' => [self::class, 'rtmkit_root_page'],
            'render_view' => [self::class, 'render'],
        ];

        return $menus;
    }
}