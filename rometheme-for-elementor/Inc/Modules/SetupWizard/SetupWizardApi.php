<?php

namespace RTMKit\Modules\SetupWizard;

class SetupWizardApi
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
        \RTMKit\Modules\Update\UpdateAPI::instance()->init();
        if (wp_doing_ajax()) {
            add_action('wp_ajax_plugin_status_check', [$this, 'plugin_status_check']);
            add_action('wp_ajax_newsletter_subscribe', [$this, 'newsletter_subscribe']);
            add_action('wp_ajax_rtm_wizard_finish', [$this, 'rtm_wizard_finish']);
        }
    }

    public function plugin_status_check()
    {
        $plugin_slug = $_POST['plugin_slug'];
        check_ajax_referer('rtmkit_wizard_nonce', 'nonce');
        $plugins = \RTMKit\Modules\Update\UpdateModule::instance()->get_plugins();
        if ($plugin_slug === 'rtmkitpro' && file_exists(WP_PLUGIN_DIR . '/romethemekit-pro/RomeTheme_pro.php')) {
                $proCurrentVersion = get_plugin_data(WP_PLUGIN_DIR . '/romethemekit-pro/RomeTheme_pro.php')['Version'] ?? null;
                // $isProActive = \RTMKitPro\Modules\Licenses\LicenseStorage::instance()->isLicenseActive() ?? false;

                if ($proCurrentVersion) {
                    $pluginProInfo = \RTMKit\Modules\Update\UpdateModule::instance()->get_plugin_info('rtmkitpro');
                    $proVersion = $pluginProInfo ? $pluginProInfo->version : null;
                    $proMinVersion = $plugins['rtmkitpro']['min_version'] ?? null;
                    $update = version_compare($proCurrentVersion, $proMinVersion, '<') ? true : false;
                    wp_send_json_success([
                        'is_active' => true,
                        'pro_version' => $proVersion,
                        'pro_current_version' => $proCurrentVersion,
                        'pro_min_version' => $proMinVersion,
                        'update_required' => $update,
                        'is_installed' => true,
                    ]);
                }
            // }
            // wp_send_json_success([
            //     'is_active' => false,
            //     'is_installed' => false,
            // ]);
        } elseif ($plugin_slug === 'rtmform') {

            $formInfo = \RTMKit\Modules\Update\UpdateModule::instance()->get_plugin_info('rtmform');
            $formVersion = $formInfo ? $formInfo->version : null;
            $formCurrentVersion = get_plugin_data(WP_PLUGIN_DIR . '/romethemeform/rometheme-form.php')['Version'] ?? null;
            $formMinVersion = $plugins['rtmform']['min_version'] ?? null;
            if ($formCurrentVersion) {
                $update = version_compare($formCurrentVersion, $formMinVersion, '<') ? true : false;
                wp_send_json_success([
                    'form_version' => $formVersion,
                    'form_current_version' => $formCurrentVersion,
                    'form_min_version' => $formMinVersion,
                    'update_required' => $update,
                    'is_installed' => true,
                ]);
            }

            wp_send_json_success([
                'is_installed' => false,
            ]);
        }
    }

    public function newsletter_subscribe()
    {
        check_ajax_referer('rtmkit_wizard_nonce', 'nonce');
        $email = sanitize_text_field($_POST['email']);
        $url = "https://www.rometheme.net/wp-content/plugins/newsletter-api/add.php?nk=c49c06ac22bb6f00df99f832bbd597b2eddc4cc2&ne=" . $email . "&nn=" . $email;
        if(file_exists(WP_PLUGIN_DIR . '/romethemekit-pro/RomeTheme_pro.php')) {
            $url .= "&nl=2";
        } else {
            $url .= "&nl=3";
        }
        $res = wp_remote_post($url);
        if (!is_wp_error($res)) {
            wp_send_json_success("OK");
        }
    }

    public function rtm_wizard_finish()
    {
        check_ajax_referer('rtmkit_wizard_nonce', 'nonce');

        $finish = update_option('rtmkit_wizard_setup_complete_2.0', 'completed');

        if ($finish) {
            delete_option('rtmkit_redirect_wizard');
            wp_send_json_success('success');
        } else {
            wp_send_json_error('failed');
        }
    }
}
