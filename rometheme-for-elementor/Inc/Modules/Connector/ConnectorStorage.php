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

        // Situs yang belum pernah connect tidak punya payload sama sekali, dan
        // exchange yang gagal menyimpan array kosong di option yang sama. Tanpa
        // penjagaan ini setiap pemanggilan memunculkan warning PHP.
        if (!is_array($payload) || empty($payload['access_token']) || !is_string($payload['access_token'])) {
            return '';
        }

        $parts = explode('|', $payload['access_token'], 2);

        return isset($parts[1]) ? $parts[1] : $parts[0];
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

    /**
     * Hapus semua data koneksi RTMHub. Setelah ini situs kembali seperti belum
     * pernah connect: tidak ada token, plan, maupun jadwal sync.
     */
    public function clear()
    {
        foreach (
            [
                'rtm_connector_last_payload',
                'rtm_connector_last_error',
                'rtm_connector_status',
                'rtm_connector_callback_token',
            ] as $option
        ) {
            delete_option($option);
        }

        delete_transient('rtmkit_connector_last_sync');
    }
}
