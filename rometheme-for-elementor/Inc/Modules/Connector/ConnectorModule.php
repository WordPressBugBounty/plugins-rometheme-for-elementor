<?php

namespace RTMKit\Modules\Connector;

use RTMKit\Modules\Helper\APIHandlerV2 as APIHandler;

class ConnectorModule
{
    private static $instance;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        $this->load();
    }

    public function load()
    {
        add_filter('rtmkit_menus', [$this, 'add_account_menu']);
        add_action('admin_post_rtmhub_callback', [$this, 'callback_handler']);
        add_action('admin_post_rtmhub_my_account', [$this, 'rtmhub_handle_my_account_redirect']);
        add_action('admin_notices', [$this, 'render_connector_notice']);

        if (wp_doing_ajax()) {
            add_action('wp_ajax_connect_sync', [$this, 'connect_sync']);
            add_action('wp_ajax_disconnect_account', [$this, 'disconnect_account']);
        }
    }

    public function render_connector_notice()
    {
        $error = get_option('rtm_connector_last_error');

        if (empty($error)) {
            return;
        }

        printf(
            '<div class="notice notice-error is-dismissible"><p>%s: %s</p></div>',
            esc_html__('RTMHub Connector', 'rtmkit'),
            esc_html($error)
        );

        delete_option('rtm_connector_last_error');
    }


    public function add_account_menu($menus)
    {
        $menus['settings']['account'] = [
            'title' => __('Account', 'rometheme-for-elementor'),
            'capability' => 'manage_options',
            'menu_slug' => 'rtmkit&path=account',
            'function' => [$this, 'rtmkit_root_page'],
            'render_view' => RTM_KIT_DIR . 'views/account.php',
        ];

        return $menus;
    }

    public function callback_handler()
    {
        if (empty($_GET['callback'])) {
            wp_die('Missing callback token.');
        }

        if (!current_user_can('manage_options')) {
            wp_die('access_denied');
        }

        $callback_token = sanitize_text_field(wp_unslash($_GET['callback']));
        $result = $this->exchange_callback_token($callback_token);

        if (is_wp_error($result)) {
            update_option('rtm_connector_last_error', $result->get_error_message());
            update_option('rtm_connector_last_payload', []);
            update_option('rtm_connector_status', [
                'connected' => false,
                'updated_at' => current_time('mysql'),
            ]);

            ?>
            <!doctype html>
            <script>
                window.opener.postMessage({
                    type: "rtmhub-connect-failed",
                    message: <?php echo wp_json_encode($result->get_error_message()); ?>
                },
                    <?php echo wp_json_encode(home_url()); ?>
                );

                window.close();
            </script>
            <?php
            exit;
        }

        update_option('rtm_connector_last_error', '');
        update_option('rtm_connector_last_payload', $result);
        update_option('rtm_connector_callback_token', $callback_token);
        update_option('rtm_connector_status', [
            'connected' => true,
            'updated_at' => current_time('mysql'),
            'domain' => $result['domain'] ?? '',
            'user_id' => $result['user_id'] ?? 0,
        ]);

        set_transient('rtmkit_connector_last_sync', current_time('timestamp'), WEEK_IN_SECONDS);

        ?>
        <!doctype html>
        <script>
            window.opener.postMessage({
                type: "rtmhub-connected"
            },
                <?php echo wp_json_encode(home_url()); ?>
            );

            window.close();
        </script>
        <?php

        exit;
    }

    private function exchange_callback_token(string $token)
    {
        $endpoint = trailingslashit(untrailingslashit(RTMHUB_API_URL)) . 'connector/v1/auth/callback/exchange';

        if (!filter_var($endpoint, FILTER_VALIDATE_URL)) {
            return new \WP_Error('rtm_invalid_endpoint', 'RTM Hub URL is invalid.');
        }

        $response = wp_remote_post($endpoint, [
            'timeout' => 30,
            'redirection' => 3,
            'httpversion' => '1.1',
            'blocking' => true,
            'sslverify' => true,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'body' => wp_json_encode([
                'token' => $token,
                'domain' => $this->get_current_domain(),
            ]),
        ]);

        // $response = APIHandler::instance()->remote(
        //     'connector/v1/auth/callback/exchange',
        //     [
        //         'timeout' => 30,
        //         'redirection' => 3,
        //         'httpversion' => '1.1',
        //         'blocking' => true,
        //         'sslverify' => true,
        //         'headers' => [
        //             'Content-Type' => 'application/json',
        //             'Accept' => 'application/json',
        //         ],
        //         'body' => wp_json_encode([
        //             'token' => $token,
        //             'domain' => $this->get_current_domain(),
        //         ]),
        //     ]
        // );

        if (is_wp_error($response)) {
            return new \WP_Error(
                'rtm_exchange_failed',
                sprintf('RTM Hub exchange failed: %s', $response->get_error_message())
            );
        }

        $code = (int) wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        if ($code < 200 || $code >= 300 || !is_array($decoded)) {
            $message = $decoded['message'] ?? 'Unable to exchange callback token.';
            return new \WP_Error('rtm_exchange_invalid', $message);
        }

        return $decoded;
    }

    public function get_current_domain(): string
    {
        $parts = wp_parse_url(home_url());
        $host = strtolower($parts['host'] ?? '');

        if ($host === '') {
            return '';
        }

        $path = trim($parts['path'] ?? '', '/');

        return $path === '' ? $host : $host . '/' . $path;
    }

    public function connect_sync()
    {
        if (!current_user_can('manage_options')) {
            wp_die('access_denied');
        }

        error_log('ConnectorModule::connect_sync called');
        check_ajax_referer('rtmkit_nonce', 'nonce');
        $sync = ConnectorStorage::instance()->sync();

        if ($sync) {
            wp_send_json_success($sync);
        }
    }

    public function disconnect_account()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => __('You do not have permission to perform this action.', 'rometheme-for-elementor'),
            ], 403);
        }

        check_ajax_referer('rtmkit_nonce', 'nonce');

        // Hapus domain situs ini dari akun di RTMHub dulu, selagi token-nya
        // masih ada. Data lokal tetap dihapus walau hub gagal dihubungi, supaya
        // pengguna tidak terjebak dalam keadaan connected.
        $remote = $this->remove_site_from_hub();

        ConnectorStorage::instance()->clear();

        if (is_wp_error($remote)) {
            wp_send_json_success([
                'warning' => sprintf(
                    /* translators: %s: error message from RTMHub. */
                    __('This site was disconnected, but its domain could not be removed from your RTMHub account: %s', 'rometheme-for-elementor'),
                    $remote->get_error_message()
                ),
            ]);
        }

        wp_send_json_success();
    }

    /**
     * Minta RTMHub menghapus domain situs ini dari akun yang terhubung.
     * Di hub: LicenseActivationController::deactivation, yang menghapus baris
     * site_domains milik pemilik token beserta user_plan_pillars-nya.
     *
     * @return true|\WP_Error
     */
    private function remove_site_from_hub()
    {
        $token = ConnectorStorage::instance()->get_access_token();

        if ('' === $token) {
            return true;
        }

        $endpoint = apply_filters('rtmkit_connector_disconnect_endpoint', 'connector/v1/licenses/client/deactivation');
        $url = untrailingslashit(RTMHUB_API_URL) . '/' . ltrim($endpoint, '/');

        $response = wp_remote_post($url, [
            'timeout' => 15,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'body' => wp_json_encode([
                'domain' => $this->get_current_domain(),
            ]),
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        $code = (int) wp_remote_retrieve_response_code($response);

        // 404: domain sudah tidak ada di hub, hasil akhirnya sama.
        if (($code >= 200 && $code < 300) || 404 === $code) {
            return true;
        }

        $decoded = json_decode(wp_remote_retrieve_body($response), true);
        $message = is_array($decoded) && !empty($decoded['message'])
            ? (string) $decoded['message']
            : sprintf('HTTP %d', $code);

        return new \WP_Error('rtmhub_disconnect_failed', $message);
    }

    public function rtmhub_request_sso_login_url()
    {
        $token = ConnectorStorage::instance()->get_access_token();

        if (empty($token)) {
            return new WP_Error(
                'rtmhub_no_token',
                __('RTMHub is not connected. Please reconnect.', 'rtmkit')
            );
        }

        $response = wp_remote_post(
            trailingslashit(RTMHUB_API_URL) . 'sso/token',
            array(
                'timeout' => 10,
                'headers' => array(
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ),
            )
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code === 401) {
            return new \WP_Error(
                'rtmhub_unauthorized',
                __('Connection to RTMHub is not valid. Please reconnect from the account page.', 'rtmkit')
            );
        }

        if ($status_code !== 200) {
            return new \WP_Error(
                'rtmhub_request_failed',
                sprintf(
                    __('Failed to contact RTMHub (status %d). Please try again later.', 'rtmkit'),
                    $status_code
                )
            );
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($body['login_url'])) {
            return new \WP_Error(
                'rtmhub_invalid_response',
                __('Invalid response from RTMHub.', 'rtmkit')
            );
        }

        return esc_url_raw($body['login_url']);
    }

    public function rtmhub_handle_my_account_redirect(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'rtmkit'));
        }

        $login_url = $this->rtmhub_request_sso_login_url();

        if (is_wp_error($login_url)) {
            set_transient(
                'rtmhub_sso_error_' . get_current_user_id(),
                $login_url->get_error_message(),
                30
            );

            wp_safe_redirect(admin_url('admin.php?page=rtmkit-connector&rtmhub_sso_error=1'));
            exit;
        }

        wp_redirect($login_url);
        exit;
    }
}
