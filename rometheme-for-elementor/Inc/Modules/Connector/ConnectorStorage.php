<?php

namespace RTMKit\Modules\Connector;

use RTMKit\Modules\Helper\APIHandlerV2 as ApiHandler;

class ConnectorStorage
{
    private static $instance;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get_payload()
    {
        return get_option('rtm_connector_last_payload');
    }

    public function get_access_token()
    {
        $payload = $this->get_payload();
        $access_token = explode('|', $payload['access_token'], 2)[1];
        return $access_token;
    }

    public function sync_period()
    {
        if (get_transient('rtmkit_connector_last_sync')) {
            return;
        }
        $sync = $this->sync();
    }

    public function sync()
    {
        $host = wp_parse_url(home_url(), PHP_URL_HOST);
        $domain = strtolower((string) $host);
        $data = ApiHandler::instance()->remote('connector/v1/licenses/sync?domain=' . $domain);
        if (is_wp_error($data)) {
            update_option('rtm_connector_last_error', $data->get_error_message());
            return new \WP_Error('Failed Synchronize', $data->get_error_message());
        }

        if (!isset($data['payload'])) {
            update_option('rtm_connector_last_error', $data);
            return new \WP_Error($data);
        }
        update_option('rtm_connector_last_payload', $data);
        delete_transient('rtmkit_connector_last_sync');
        set_transient('rtmkit_connector_last_sync', current_time('timestamp'), WEEK_IN_SECONDS);
        return $data;
    }
}
