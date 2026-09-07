<?php

namespace RTMKit\Modules\Helper;

use RTMKit\Modules\Connector\ConnectorStorage;

class APIHandlerV2
{
    private static ?APIHandlerV2 $instance = null;
    private string $url = RTMHUB_API_URL;

    public static function instance(): APIHandlerV2
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Send HTTP request.
     *
     * @param string      $endpoint
     * @param array       $args
     * @param string|null $url
     * @param bool        $useToken
     * @param bool        $json
     * @param string      $method
     *
     * @return mixed
     */
    public function remote(
        string $endpoint,
        array $args = [],
        ?string $url = null,
        bool $useToken = true,
        bool $json = true,
        string $method = 'GET'
    ) {
        $url ??= $this->url;

        $defaults = [
            'method'      => strtoupper($method),
            'timeout'     => 30,
            'redirection' => 5,
            'httpversion' => '1.1',
            'sslverify'   => true,
            'headers'     => [
                'Accept' => 'application/json',
            ],
        ];

        $args = wp_parse_args($args, $defaults);

        // Merge headers
        $args['headers'] = array_merge(
            $defaults['headers'],
            $args['headers'] ?? []
        );

        // Authorization
        if ($useToken) {
            $token = ConnectorStorage::instance()->get_access_token();

            if (!empty($token)) {
                $args['headers']['Authorization'] = 'Bearer ' . $token;
            }
        }

        $requestUrl = untrailingslashit($url) . '/' . ltrim($endpoint, '/');

        $response = wp_remote_request($requestUrl, $args);

        // Return WP_Error
        if (is_wp_error($response)) {
            return $response;
        }

        /**
         * Jika menggunakan stream,
         * WordPress sudah menyimpan file ke filename.
         * Kembalikan response apa adanya.
         */
        if (!empty($args['stream'])) {
            return $response;
        }

        // Return raw response jika diminta
        if (!$json) {
            return wp_remote_retrieve_body($response);
        }

        // Decode JSON
        $body = wp_remote_retrieve_body($response);

        $decoded = json_decode($body, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        return $body;
    }
}
