<?php

namespace RTMKit\Modules\SetupWizard;

class SetupWizardModule
{
    public function init()
    {
        add_action('admin_menu', [$this, 'register_wizard_menu']);
        add_action('admin_init', [$this, 'maybe_redirect_to_dashboard']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_wizard_scripts']);
        \RTMKit\Modules\SetupWizard\SetupWizardApi::instance()->init();
        \RTMKit\Modules\Manager::instance()->init();
    }

    public function enqueue_wizard_scripts()
    {
        $screen = get_current_screen();
        if ($screen && $screen->id === 'toplevel_page_rtmkit-setup-wizard') {
            wp_enqueue_script(
                'rtmkit-setup-wizard',
                RTM_KIT_URL . 'assets/js/setup-wizard.js',
                ['jquery'],
                RTM_KIT_VERSION,
                true
            );
            wp_enqueue_style(
                'rtmkit-setup-wizard',
                RTM_KIT_URL . 'assets/css/setup-wizard.css',
                [],
                RTM_KIT_VERSION
            );
            wp_enqueue_style('bootstrap-css', RTM_KIT_URL . 'assets/css/bootstrap.min.css', [], '4.6.0');
            wp_enqueue_style('fontawesome-css', RTM_KIT_URL . 'assets/css/fontawesome/all.min.css', [], '6.7.2');
            wp_localize_script('rtmkit-setup-wizard', 'rtmkitWizard', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('rtmkit_wizard_nonce'),
                'rtmkit_nonce' => wp_create_nonce('rtmkit_nonce')
            ]);
        }
    }

    public function register_wizard_menu()
    {
        add_menu_page(
            __('RTMKit Dashboard', 'rometheme-for-elementor'),
            __('RTMKit', 'rometheme-for-elementor'),
            'manage_options',
            'rtmkit-setup-wizard',
            [$this, 'render_wizard_page'],
            RTM_KIT_URL . 'assets/images/romethemekit.svg',
            20
        );
    }

    public function render_wizard_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to access this page.');
        }

        include RTM_KIT_DIR . 'views/setup-wizard.php';
    }



    function maybe_redirect_to_dashboard()
    {

        if (! is_admin()) {
            return;
        }

        if (! current_user_can('manage_options')) {
            return;
        }

        // Jangan redirect kalau sudah di halaman wizard
        if (isset($_GET['page']) && $_GET['page'] === 'rtmkit-setup-wizard') {
            if (get_option('rtmkit_wizard_setup_complete_2.0', false)) {
                wp_safe_redirect(
                    admin_url('admin.php?page=rtmkit')
                );
            } else {
                return;
            }
        }
        return;
    }
}
