<?php

namespace RTMKit\Modules\Helper;

class APIHandler
{
    private static ?APIHandler $instance = null;

    private $url = 'https://api.rometheme.pro/';

    public static function instance(): APIHandler
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function remote($endpoint,  $args = [], $url = null, $useToken = false, $json = true, $method = 'GET')
    {
        if (!isset($args['headers'])) {
            $args['headers'] = [
                'Accept' => 'application/json',
            ];
        }

        if (!isset($args['httpversion'])) {
            $args['httpversion'] = '1.1';
        }
        if (!isset($args['timeout'])) {
            $args['timeout'] = 15;
        }
        if (!isset($args['sslverify'])) {
            $args['sslverify'] = true;
        }

        if ($url === null) {
            $url = $this->url;
        }

        if ($useToken) {
            $token = $this->get_token_access();
            if ($token) {
                $args['headers']['Authorization'] = 'Bearer ' . $token;
            }
        }

        if (strtoupper($method) === 'POST') {
            $response = wp_remote_post($url . $endpoint, $args);
        } else {
            $response = wp_remote_get($url . $endpoint, $args);
        }
        if ($useToken) {
            // Token expired or invalid, try to get a new one
            if (is_wp_error($response) || wp_remote_retrieve_response_code($response) === 401) {
                // delete_user_meta(get_current_user_id(), 'rtm_token_access');
                $token = $this->refresh_token_access();
                if ($token) {
                    $args['headers']['Authorization'] = 'Bearer ' . $token;
                    $response = wp_remote_get($url . $endpoint, $args);
                }
            }
        }

        if (is_wp_error($response)) {
            wp_send_json_error('Error: ' . $response->get_error_message());
        }
        $body = wp_remote_retrieve_body($response);
        if (!$json) {
            return $body;
        }
        return json_decode($body, true);
    }

    public function get_token_access()
    {
        $userToken = get_user_meta(get_current_user_id(), 'rtm_token_access', true);

        if ($userToken) {
            return $userToken;
        } else {
            $encodeUserMail = \RTMKit\Modules\Helper\Encoding::instance()->encodeSHA1(wp_get_current_user()->user_email);
            $data = [
                "client_signed" => $encodeUserMail,
            ];
            $args = [
                'body' => json_encode($data),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'method' => 'POST',
            ];
            $response = $this->remote('wp-json/auth/register-client', $args);
            if ($response && isset($response['token'])) {
                update_user_meta(get_current_user_id(), 'rtm_token_access', $response['token']['_access']);
                update_user_meta(get_current_user_id(), 'rtm_token_refresh', $response['token']['_refresh']);
                return $response['token']['_access'];
            }
        }
    }

    public function refresh_token_access()
    {
        $refreshToken = get_user_meta(get_current_user_id(), 'rtm_token_refresh', true);

        $data = [
            "t_refresh" => $refreshToken,
        ];
        $args = [
            'body' => json_encode($data),
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'method' => 'POST',
        ];
        $response = $this->remote('wp-json/auth/refresh-token', $args);
        if ($response && isset($response['token'])) {
            update_user_meta(get_current_user_id(), 'rtm_token_access', $response['token']['_access']);
            update_user_meta(get_current_user_id(), 'rtm_token_refresh', $response['token']['_refresh']);
            return $response['token']['_access'];
        }
    }
}
