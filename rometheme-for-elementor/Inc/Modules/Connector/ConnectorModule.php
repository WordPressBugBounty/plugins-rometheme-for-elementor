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
        add_action( 'admin_post_rtmhub_my_account', [$this, 'rtmhub_handle_my_account_redirect'] );

        if (wp_doing_ajax()) {
            add_action('wp_ajax_connect_sync', [$this, 'connect_sync']);
        }
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
            return new \WP_Error('connect_failed', $result->get_error_message());
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

    private function get_current_domain(): string
    {
        $host = wp_parse_url(home_url(), PHP_URL_HOST);
        return strtolower((string) $host);
    }

    public function connect_sync()
    {
        if (!current_user_can('manage_options')) {
            wp_die('access_denied');
        }
        check_ajax_referer('rtmkit_nonce', 'nonce');
        $sync = ConnectorStorage::instance()->sync();

        if ($sync) {
            wp_send_json_success($sync);
        }
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
