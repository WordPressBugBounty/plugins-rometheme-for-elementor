<?php

namespace RTMKit\Modules\Telemetry;

if (! defined('ABSPATH')) exit;

/**
 * Antrean event yang menunggu dikirim ke hub.
 *
 * Event tidak pernah dikirim saat halaman admin sedang dimuat: kalau hub mati,
 * setiap klik di wp-admin akan ikut menunggu timeout HTTP. Yang mengirim adalah
 * WP-Cron, beberapa menit setelah event pertama dicatat (event lain dalam jeda
 * itu ikut terkirim bersama), kecuali dua kejadian yang memang tidak punya
 * "nanti": nonaktif dan uninstall.
 *
 * Antrean ini juga tempat data ditahan selama hub minta jeda, jadi disimpan di
 * option — transient bisa dibuang cache kapan saja.
 */
class TelemetryQueue
{
    /** Batas satu kiriman ke hub. */
    public const MAX_EVENTS = 50;

    /** Batas antrean, untuk menampung event selama jeda yang panjang. */
    public const MAX_QUEUE = 200;

    public const EVENT_ACTIVATED   = 'plugin_activated';
    public const EVENT_DEACTIVATED = 'plugin_deactivated';
    public const EVENT_UNINSTALLED = 'plugin_uninstalled';
    public const EVENT_KIT_DOWNLOAD = 'kit_downloaded';

    /** Addon RTM dipasang atau dihapus; slug addon di subject_key. */
    public const EVENT_ADDON_INSTALLED   = 'addon_installed';
    public const EVENT_ADDON_UNINSTALLED = 'addon_uninstalled';

    private static $instance;

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Catat satu event. Event hanya dikirim oleh situs yang connect dan sudah
     * memberi izin; sisanya tidak perlu menumpuk antrean yang tidak akan pernah
     * terkirim.
     *
     * @param array<string, mixed> $extra Field tambahan, mis. subject_key.
     */
    public function record(string $event, string $plugin, array $extra = []): void
    {
        $state = TelemetryState::instance();

        if (! $state->is_connected() || ! $state->can_report()) {
            return;
        }

        $entry = array_merge(
            [
                'event'          => $event,
                'plugin'         => $plugin,
                'plugin_version' => $this->plugin_version($plugin),
                'occurred_at'    => gmdate('c'),
            ],
            $extra
        );

        $queue   = $this->all();
        $queue[] = $entry;

        // Kalau antrean penuh, yang paling lama yang dibuang: event terbaru
        // lebih berguna daripada event sebulan lalu yang akan ditolak hub.
        if (count($queue) > self::MAX_QUEUE) {
            $queue = array_slice($queue, -self::MAX_QUEUE);
        }

        $this->replace($queue);

        TelemetryModule::schedule_flush();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $queue = get_option(TelemetryState::OPTION_QUEUE, []);

        return is_array($queue) ? array_values(array_filter($queue, 'is_array')) : [];
    }

    public function count(): int
    {
        return count($this->all());
    }

    public function clear(): void
    {
        delete_option(TelemetryState::OPTION_QUEUE);
    }

    /**
     * @param array<int, array<string, mixed>> $queue
     */
    public function replace(array $queue): void
    {
        if (empty($queue)) {
            $this->clear();

            return;
        }

        update_option(TelemetryState::OPTION_QUEUE, array_values($queue), false);
    }

    /**
     * Buang event yang sudah lewat batas umur hub (30 hari), supaya antrean
     * situs yang lama offline tidak selalu ditolak seluruhnya.
     *
     * @param array<int, array<string, mixed>> $queue
     *
     * @return array<int, array<string, mixed>>
     */
    public function drop_expired(array $queue): array
    {
        $cutoff = time() - (30 * DAY_IN_SECONDS);

        return array_values(array_filter($queue, static function ($entry) use ($cutoff) {
            if (empty($entry['occurred_at'])) {
                return true;
            }

            $timestamp = strtotime((string) $entry['occurred_at']);

            return false === $timestamp || $timestamp >= $cutoff;
        }));
    }

    private function plugin_version(string $plugin): string
    {
        if (TelemetryState::PLUGIN_RTMKIT === $plugin) {
            return RTM_KIT_VERSION;
        }

        return $this->installed_plugin_version($plugin);
    }

    /**
     * Versi addon dibaca dari header file utamanya. Dipanggil hanya saat ada
     * event addon atau pemeriksaan AddonTracker, jadi biaya get_plugin_data
     * tidak kena di request biasa.
     */
    public function installed_plugin_version(string $slug): string
    {
        $file = WP_PLUGIN_DIR . '/' . $slug . '/' . $slug . '.php';

        if (! file_exists($file)) {
            return '';
        }

        if (! function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $data = get_plugin_data($file, false, false);

        return isset($data['Version']) ? (string) $data['Version'] : '';
    }
}
