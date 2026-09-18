<?php

namespace RTMKit\Modules\Telemetry;

use RTMKit\Modules\Addons\AddonsModule;

if (! defined('ABSPATH')) exit;

/**
 * Install dan uninstall addon RTM (katalog addon di RTMHub).
 *
 * Dicatat dari selisih daftar addon yang terpasang, bukan dari hook per aksi.
 * Update dan rollback di halaman Addons menghapus folder addon lalu memasangnya
 * lagi, dan WordPress menjalankan keduanya sebagai "install"; yang dibandingkan
 * di sini hanya keadaan sebelum dan sesudahnya, jadi update tidak terhitung
 * sebagai install baru. Addon yang dipasang atau dihapus di luar WordPress
 * (FTP, deploy) ikut terhitung pada pemeriksaan tiap jam.
 *
 * Daftarnya tetap diperbarui walau izin belum diberikan: begitu izin diberikan,
 * perubahan dari masa sebelumnya tidak ikut terkirim sebagai event baru.
 */
class AddonTracker
{
    private static $instance;

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * WordPress selesai memasang atau memperbarui plugin, dari halaman Addons,
     * halaman Plugins, maupun WP-CLI.
     *
     * @param mixed $upgrader
     * @param mixed $hook_extra
     */
    public function on_upgrader_complete($upgrader, $hook_extra): void
    {
        if (is_array($hook_extra) && isset($hook_extra['type']) && 'plugin' === $hook_extra['type']) {
            $this->check(false);
        }
    }

    /**
     * @param string $plugin_file
     * @param bool   $deleted
     */
    public function on_plugin_deleted($plugin_file, $deleted): void
    {
        if ($deleted) {
            $this->check(false);
        }
    }

    /**
     * Pemeriksaan tiap jam. Hanya di sini katalog boleh diambil dari hub, dan
     * hanya kalau hasilnya memang akan dilaporkan.
     */
    public function check_scheduled(): void
    {
        $state = TelemetryState::instance();

        $this->check($state->is_connected() && $state->can_report());
    }

    public function check(bool $may_fetch_catalog): void
    {
        $stored    = $this->stored();
        $known     = array_values(array_unique(array_merge($stored['known'], $this->catalog_slugs($may_fetch_catalog))));
        $installed = $this->installed($known);

        foreach ($installed as $slug => $version) {
            // Addon yang baru masuk daftar — pemeriksaan pertama, atau baru
            // ditambahkan ke katalog — cuma dicatat keadaannya: tidak ada yang
            // tahu kapan ia dipasang.
            if (in_array($slug, $stored['known'], true) && ! array_key_exists($slug, $stored['installed'])) {
                $this->record(TelemetryQueue::EVENT_ADDON_INSTALLED, $slug, $version);
            }
        }

        foreach ($stored['installed'] as $slug => $version) {
            if (! array_key_exists($slug, $installed)) {
                $this->record(TelemetryQueue::EVENT_ADDON_UNINSTALLED, $slug, $version);
            }
        }

        $next = ['known' => $known, 'installed' => $installed];

        if ($next !== $stored) {
            update_option(TelemetryState::OPTION_ADDONS, $next, false);
        }
    }

    private function record(string $event, string $slug, string $version): void
    {
        $extra = ['subject_key' => $slug];

        if ('' !== $version) {
            $extra['meta'] = ['addon_version' => $version];
        }

        TelemetryQueue::instance()->record($event, TelemetryState::PLUGIN_RTMKIT, $extra);
    }

    /**
     * @return array{known: array<int, string>, installed: array<string, string>}
     */
    private function stored(): array
    {
        $stored = get_option(TelemetryState::OPTION_ADDONS, []);

        return [
            'known'     => isset($stored['known']) && is_array($stored['known']) ? array_values(array_filter($stored['known'], 'is_string')) : [],
            'installed' => isset($stored['installed']) && is_array($stored['installed']) ? array_map('strval', $stored['installed']) : [],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function catalog_slugs(bool $may_fetch): array
    {
        $module = AddonsModule::instance();
        $addons = $may_fetch ? $module->get_addons_list() : $module->cached_addons_list();
        $slugs  = [];

        foreach ($addons as $addon) {
            if (is_array($addon) && isset($addon['slug']) && is_string($addon['slug']) && preg_match('/^[a-z0-9-]{1,100}$/', $addon['slug'])) {
                $slugs[] = $addon['slug'];
            }
        }

        return $slugs;
    }

    /**
     * Addon yang terpasang, dengan versinya. Lokasinya sama dengan yang dipakai
     * halaman Addons: `<slug>/<slug>.php`.
     *
     * @param array<int, string> $slugs
     *
     * @return array<string, string>
     */
    private function installed(array $slugs): array
    {
        $installed = [];

        foreach ($slugs as $slug) {
            if (file_exists(WP_PLUGIN_DIR . '/' . $slug . '/' . $slug . '.php')) {
                $installed[$slug] = TelemetryQueue::instance()->installed_plugin_version($slug);
            }
        }

        return $installed;
    }
}
