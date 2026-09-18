<?php

namespace RTMKit\Modules\Telemetry;

use RTMKit\Modules\Connector\ConnectorStorage;

if (! defined('ABSPATH')) exit;

/**
 * Pengirim laporan ke RTMHub.
 *
 * Tidak lewat APIHandlerV2 karena handler itu hanya mengembalikan body yang
 * sudah di-decode, sedangkan seluruh aturan coba-lagi di sini bergantung pada
 * status HTTP-nya (401 sync ulang, 404 berhenti, 422 buang batch, 429 tunggu).
 * Header Authorization-nya dibentuk dari token yang sama.
 */
class TelemetryClient
{
    public const ENDPOINT_EVENTS           = 'connector/v1/telemetry/events';
    public const ENDPOINT_WIDGETS          = 'connector/v1/telemetry/widgets';
    public const ENDPOINT_ANONYMOUS_WIDGET = 'connector/v1/telemetry/anonymous/widgets';

    /** Alasan nonaktif yang diisi sendiri oleh pengguna saat menonaktifkan. */
    public const ENDPOINT_FEEDBACK = 'connector/v1/telemetry/feedback';

    /** Hasil pengiriman: lanjutkan seperti biasa. */
    public const RESULT_OK = 'ok';

    /** Payload ditolak; batch ini tidak akan pernah diterima, buang saja. */
    public const RESULT_REJECTED = 'rejected';

    /** Domain tidak dikenal hub; berhenti sampai situs connect ulang. */
    public const RESULT_UNKNOWN_SITE = 'unknown_site';

    /**
     * Gagal sementara (jaringan, 429, 5xx) atau sedang jeda. Pemanggil menahan
     * datanya dan mengirim ulang setelah TelemetryState::paused_until().
     */
    public const RESULT_RETRY = 'retry';

    /**
     * Jeda bertahap kalau hub tidak menyebut Retry-After — 502/504 dari proxy,
     * jaringan putus: 5 menit, 15 menit, 1 jam, 3 jam, lalu 6 jam seterusnya.
     */
    private const BACKOFF = [
        5 * MINUTE_IN_SECONDS,
        15 * MINUTE_IN_SECONDS,
        HOUR_IN_SECONDS,
        3 * HOUR_IN_SECONDS,
        6 * HOUR_IN_SECONDS,
    ];

    private const MIN_PAUSE = MINUTE_IN_SECONDS;

    private const MAX_PAUSE = 6 * HOUR_IN_SECONDS;

    /**
     * Tambahan acak di atas jeda. Batas total di hub memberi Retry-After yang
     * sama ke semua situs yang ditahan; tanpa ini mereka kembali di detik yang
     * sama dan langsung kena batas lagi.
     */
    private const JITTER = 5 * MINUTE_IN_SECONDS;

    private static $instance;

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Kirim satu laporan.
     *
     * @param array<string, mixed> $body
     *
     * @return array{result: string, code: int, message: string, data: array}
     */
    public function send(string $endpoint, array $body, bool $with_token = true, int $timeout = 15): array
    {
        $state = TelemetryState::instance();

        // Selama jeda tidak ada request sama sekali. Datanya tetap di antrean
        // pemanggil dan dikirim sekaligus setelah jeda lewat.
        if ($state->is_paused()) {
            return $this->result(self::RESULT_RETRY, 0, 'Paused until ' . gmdate('c', $state->paused_until()) . '.');
        }

        $token = $with_token ? $this->token() : '';

        if ($with_token && '' === $token) {
            return $this->result(self::RESULT_UNKNOWN_SITE, 0, 'Site is not connected.');
        }

        $response = $this->request($endpoint, $body, $token, $timeout);

        if ($with_token && ! is_wp_error($response) && 401 === (int) wp_remote_retrieve_response_code($response)) {
            // Token kedaluwarsa atau diganti: ambil yang baru lalu coba sekali lagi.
            $synced = ConnectorStorage::instance()->sync();

            if (is_wp_error($synced)) {
                return $this->retry_later(null, 401, $synced->get_error_message());
            }

            $response = $this->request($endpoint, $body, $this->token(), $timeout);
        }

        if (is_wp_error($response)) {
            return $this->retry_later(null, 0, $response->get_error_message());
        }

        $code    = (int) wp_remote_retrieve_response_code($response);
        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);
        $decoded = is_array($decoded) ? $decoded : [];

        if ($code >= 200 && $code < 300) {
            $state->clear_pause();

            return $this->result(self::RESULT_OK, $code, '', $decoded);
        }

        if (404 === $code) {
            return $this->result(self::RESULT_UNKNOWN_SITE, $code, $this->message($decoded), $decoded);
        }

        if (422 === $code || 400 === $code) {
            return $this->result(self::RESULT_REJECTED, $code, $this->message($decoded), $decoded);
        }

        return $this->retry_later($response, $code, $this->message($decoded), $decoded);
    }

    /**
     * Hub sedang membatasi (429), deploy (503), mati (502/504), atau tidak bisa
     * dihubungi: berhenti mengirim sampai Retry-After lewat, atau selama jeda
     * bertahap kalau hub tidak menyebutkannya.
     *
     * @param array|null           $response
     * @param array<string, mixed> $data
     *
     * @return array{result: string, code: int, message: string, data: array}
     */
    private function retry_later($response, int $code, string $message, array $data = []): array
    {
        $state    = TelemetryState::instance();
        $failures = $state->retry_failures() + 1;
        $delay    = null === $response ? 0 : $this->retry_after($response);

        if ($delay <= 0) {
            $delay = self::BACKOFF[min($failures, count(self::BACKOFF)) - 1];
        }

        $delay = min(max($delay, self::MIN_PAUSE), self::MAX_PAUSE) + wp_rand(0, self::JITTER);

        $state->pause($delay, $failures);

        return $this->result(self::RESULT_RETRY, $code, $message, $data);
    }

    /**
     * Retry-After dalam detik. Header-nya boleh berupa angka detik atau tanggal HTTP.
     *
     * @param array $response
     */
    private function retry_after($response): int
    {
        $header = wp_remote_retrieve_header($response, 'retry-after');
        $header = trim((string) (is_array($header) ? reset($header) : $header));

        if ('' === $header) {
            return 0;
        }

        if (ctype_digit($header)) {
            return (int) $header;
        }

        $timestamp = strtotime($header);

        return false === $timestamp ? 0 : max(0, $timestamp - time());
    }

    /**
     * @param array<string, mixed> $body
     *
     * @return array|\WP_Error
     */
    private function request(string $endpoint, array $body, string $token, int $timeout)
    {
        $args = [
            'timeout'     => $timeout,
            'redirection' => 3,
            'httpversion' => '1.1',
            'blocking'    => true,
            'sslverify'   => true,
            'headers'     => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
            'body'        => wp_json_encode($body),
        ];

        if ('' !== $token) {
            $args['headers']['Authorization'] = 'Bearer ' . $token;
        }

        return wp_remote_post($this->url($endpoint), $args);
    }

    public function url(string $endpoint): string
    {
        return trailingslashit(untrailingslashit(RTMHUB_API_URL)) . ltrim($endpoint, '/');
    }

    /**
     * Token connector, tanpa memicu warning saat situs belum connect.
     */
    private function token(): string
    {
        $payload = TelemetryState::instance()->payload();

        if (empty($payload['access_token']) || ! is_string($payload['access_token'])) {
            return '';
        }

        $parts = explode('|', $payload['access_token'], 2);

        return isset($parts[1]) ? $parts[1] : $parts[0];
    }

    /**
     * @param array<string, mixed> $decoded
     */
    private function message(array $decoded): string
    {
        if (! empty($decoded['message']) && is_string($decoded['message'])) {
            return $decoded['message'];
        }

        if (! empty($decoded['errors']) && is_array($decoded['errors'])) {
            return wp_json_encode($decoded['errors']);
        }

        return '';
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array{result: string, code: int, message: string, data: array}
     */
    private function result(string $result, int $code, string $message, array $data = []): array
    {
        return [
            'result'  => $result,
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ];
    }
}
