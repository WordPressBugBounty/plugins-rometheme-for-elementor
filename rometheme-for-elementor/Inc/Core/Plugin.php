<?php

namespace RTMKit\Core;

/**
 * Plugin
 *
 * Main plugin class for RomeThemeKit.
 *
 * @package RTMKit\Core
 */

use Dom\Element;
use Exception;

class Plugin
{
    protected $modules;

    protected static $instance;

    /**
     * Get the singleton instance of the Plugin class.
     *
     * @return Plugin
     */
    public static function instance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        if (! $this->wizard_setup_check()) {
            (new \RTMKit\Modules\SetupWizard\SetupWizardModule())->init();
            return;
        }
        // Wizard sudah selesai → jalankan plugin normal
        $this->runner();
    }

    function redirect()
    {
        if (!is_admin()) {
            return;
        }

        if (!current_user_can('manage_options')) {
            return;
        }

        if (!isset($_GET['page'])) {
            return;
        }

        $page = sanitize_key($_GET['page']);

        // Daftar page admin plugin yang VALID
        $valid_pages = [
            'rtmkit',
            'rtmkit-setup-wizard',
        ];

        // 1️⃣ Kalau page tidak valid → redirect ke halaman utama plugin
        if (!in_array($page, $valid_pages, true)) {
            wp_safe_redirect(admin_url('admin.php?page=rtmkit'));
            exit;
        }

        // 2️⃣ Kalau wizard dibuka tapi sudah selesai → redirect
        if ($page === 'rtmkit-setup-wizard') {
            if (get_option('rtmkit_wizard_setup_complete_2.0', false) !== false) {
                wp_safe_redirect(admin_url('admin.php?page=rtmkit'));
                exit;
            }
            // wizard belum selesai → biarkan
            return;
        }

        $url = add_query_arg(
            'rtmkit_redirected',
            '1',
            admin_url('admin.php?page=rtmkit-setup-wizard')
        );



        if (!empty($_GET['page']) && ($_GET['page'] === 'rtm-update' || $_GET['page'] === 'rtmkit' || $_GET['page'] === 'romethemekit') && $this->wizard_setup_check() === false) {
            var_dump("here");
            $url = admin_url('admin.php?page=rtmkit-setup-wizard');
            wp_safe_redirect($url);
            exit;
        }

        if (!empty($_GET['page']) && $_GET['page'] === 'rtmkit-setup-wizard' && $this->wizard_setup_check() !== false) {
            wp_safe_redirect(admin_url('admin.php?page=rtmkit'));
            exit;
        }
    }

    public function before_plugin_load()
    {
        add_action('admin_page_access_denied', [$this, 'redirect']);
        add_action('upgrader_process_complete', function ($upgrader, $hook_extra) {

            if (
                empty($hook_extra['action']) ||
                empty($hook_extra['type']) ||
                $hook_extra['action'] !== 'update' ||
                $hook_extra['type'] !== 'plugin'
            ) {
                return;
            }

            if (
                empty($hook_extra['plugins']) ||
                !in_array(plugin_basename(RTM_KIT_FILE), $hook_extra['plugins'], true)
            ) {
                return;
            }

            $this->rtm_handle_install_upgrade();
        }, 10, 2);
        add_action('admin_init', function () {


            $saved = get_option('rtmkit_version');

            if ($saved !== RTM_KIT_VERSION) {
                \RTMKit\Core\Plugin::instance()->rtm_handle_install_upgrade();
            }


            if (get_option('rtmkit_redirect_wizard') === false) {
                return;
            }

            if (
                wp_doing_ajax() ||
                wp_doing_cron() ||
                defined('WP_CLI') ||
                is_network_admin() ||
                !current_user_can('manage_options')
            ) {
                return;
            }

            // hindari redirect loop
            if (!empty($_GET['rtmkit_redirected'])) {
                return;
            }

            // jangan redirect kalau sudah di wizard
            if (!empty($_GET['page']) && $_GET['page'] === 'rtmkit-setup-wizard' && $this->wizard_setup_check() === false) {
                return;
            }

            $url = add_query_arg(
                'rtmkit_redirected',
                '1',
                admin_url('admin.php?page=rtmkit-setup-wizard')
            );



            if (!empty($_GET['page']) && ($_GET['page'] === 'rtm-update' || $_GET['page'] === 'rtmkit' || $_GET['page'] === 'romethemekit') && $this->wizard_setup_check() === false) {
                $url = admin_url('admin.php?page=rtmkit-setup-wizard');
                wp_safe_redirect($url);
                exit;
            }

            if (!empty($_GET['page']) && $_GET['page'] === 'rtmkit-setup-wizard' && $this->wizard_setup_check() !== false) {
                wp_safe_redirect(admin_url('admin.php?page=rtmkit'));
                exit;
            }

            if ($this->wizard_setup_check() === false) {
                wp_safe_redirect($url);
                exit;
            }
        }, 0);
    }

    public function wizard_setup_check()
    {
        $setup_complete = get_option('rtmkit_wizard_setup_complete_2.0', false);
        return $setup_complete;
    }

    public function runner()
    {
        $this->modules = [
            'menu' => \RTMKit\Modules\Menu::class,
            'plugin_api' => PluginApi::class,
            'modules' => \RTMKit\Modules\Manager::class,
            'widget_module' => \RTMKit\Modules\Widgets\WidgetModule::class,
            'extensions' => \RTMKit\Modules\Extensions\ExtensionModule::class,
            'themebuilder' => \RTMKit\Modules\Themebuilder\ThemebuilderModule::class,
            'templatekits' => \RTMKit\Modules\Templatekits\TemplatekitModule::class,
            'icons' => \RTMKit\Modules\RTMIcons\RTMIconsModule::class,
            'submission' => \RTMKit\Modules\Submission\SubmissionModule::class,
            'update' => \RTMKit\Modules\Update\UpdateModule::class,
            'editor_canvas' => \RTMKit\Modules\Helper\EditorCanvas::class
        ];
        add_action('rtmkit_loaded', [$this, 'load']);
        add_action('admin_enqueue_scripts', function () {
            wp_enqueue_style('rtmkit-system-panel', RTM_KIT_URL . 'assets/css/panel_system.css', [], RTM_KIT_VERSION);
            wp_enqueue_script('rtmkit-system-panel', RTM_KIT_URL . 'assets/js/panel_system.js', ['jquery'], RTM_KIT_VERSION, true);
        });
        add_action('wp_enqueue_scripts', function () {
            wp_enqueue_style('rtmkit-animate-css', RTM_KIT_URL . 'assets/css/animate.min.css', [], RTM_KIT_VERSION);

            wp_enqueue_style('rtmkit-system-panel', RTM_KIT_URL . 'assets/css/panel_system.css', [], RTM_KIT_VERSION);
            wp_enqueue_script('rtmkit-system-panel', RTM_KIT_URL . 'assets/js/panel_system.js', ['jquery'], RTM_KIT_VERSION, true);
        });
    }

    /**
     * Load plugin.
     */

    public function load()
    {
        try {
            $this->loadModules();
            add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue_panel_styles']);
            add_filter('admin_footer_text', function ($text) {
                $screen = get_current_screen();

                if (!$screen) {
                    return $text;
                }

                // contoh: hanya pada page plugin
                if ($screen->id === 'toplevel_page_rtmkit') {
                    return '';
                }

                return $text;
            });

            add_filter('update_footer', function ($text) {
                $screen = get_current_screen();

                if ($screen && $screen->id === 'toplevel_page_rtmkit') {
                    return '';
                }

                return $text;
            }, 11);

            new \RTMKit\Modules\Helper\Banner();
        } catch (Exception $e) {
            // Handle exceptions if necessary
            error_log($e->getMessage());
        }
    }

    /**
     * Load modules.
     */
    protected function loadModules(): void
    {
        foreach ($this->modules as $module => $class) {
            (new $class())->init();
        }
    }

    /**
     * Check if the RomeThemeForm plugin is active.
     *
     * @return bool
     */
    public function has_rtmform(): bool
    {
        include_once(ABSPATH . 'wp-admin/includes/plugin.php');
        return is_plugin_active('romethemeform/rometheme-form.php');
    }

    public function enqueue_panel_styles()
    {
        if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            wp_enqueue_style('rtmkit-elementor-panel', RTM_KIT_URL . 'assets/css/panel.css', [], RTM_KIT_VERSION);
        }
    }

    public function pro_is_active(): bool
    {
        if (class_exists('RTMKitPro\Core\Plugin')) {
            return \RTMKitPro\Modules\Licenses\LicenseStorage::instance()->isLicenseActive();
        }

        return false;
    }

    public function rtm_handle_install_upgrade()
    {
        update_option('rtmkit_version', RTM_KIT_VERSION);

        $wizardComplete = get_option('rtmkit_wizard_setup_complete_2.0', false);

        if (!$wizardComplete) {
            add_option('rtmkit_redirect_wizard', true);
        }
    }

    public function pro_version_compatible_check()
    {
        $pro_plugin = 'romethemekit-pro/RomeTheme_pro.php';
        $pro_path   = WP_PLUGIN_DIR . '/' . $pro_plugin;

        if (file_exists($pro_path)) {

            if (!function_exists('get_plugin_data')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            $pro_version = get_plugin_data($pro_path, false, false);

            if (
                class_exists('\RTMKit\Modules\Update\UpdateModule') &&
                method_exists('\RTMKit\Modules\Update\UpdateModule', 'instance')
            ) {
                $plugins = \RTMKit\Modules\Update\UpdateModule::instance()->get_plugins();

                if (
                    isset($plugins['rtmkitpro']['min_version']) &&
                    version_compare($pro_version['Version'], $plugins['rtmkitpro']['min_version'], '<')
                ) {
                    deactivate_plugins($pro_plugin);
                }
            }
        }
    }
}
