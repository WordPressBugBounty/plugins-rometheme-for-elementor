<?php

namespace RTMKit\Modules\Telemetry;

if (! defined('ABSPATH')) exit;

/**
 * Pembentuk URL tombol "Upgrade to Pro".
 *
 * Semua tombol menunjuk ke `go/upgrade` di RTMHub, yang mencatat kliknya lalu
 * meneruskan ke halaman pricing tujuan. Redirect-nya selalu terjadi, jadi
 * pencatatan yang gagal tidak pernah menahan orang yang mau membeli.
 */
class UpgradeLink
{
    /** Halaman pricing RTMKit. */
    public const TO_RTMKIT = 'rtmkit';

    /** Halaman pricing store (addon dan kit berbayar). */
    public const TO_STORE = 'store';

    /** Halaman pricing utama rometheme.net. */
    public const TO_MAIN = 'main';

    /**
     * @param string $placement Nama tombol, mis. `templatekit.import_modal`.
     * @param string $to        Halaman tujuan: rtmkit, store, atau main.
     * @param string $plugin    Slug plugin yang memasang tombolnya.
     */
    public static function url(string $placement, string $to = self::TO_RTMKIT, string $plugin = TelemetryState::PLUGIN_RTMKIT): string
    {
        $args = [
            'p'      => self::placement($placement),
            'to'     => in_array($to, [self::TO_RTMKIT, self::TO_STORE, self::TO_MAIN], true) ? $to : self::TO_RTMKIT,
            'plugin' => $plugin,
            'v'      => RTM_KIT_VERSION,
        ];

        // Kode situs hanya ada kalau situs connect. Tanpa itu kliknya tetap
        // terhitung, hanya tidak tertaut ke pelanggan mana pun.
        $site_key = TelemetryState::instance()->site_key();

        if ('' !== $site_key) {
            $args['s'] = $site_key;
        }

        return trailingslashit(untrailingslashit(RTMHUB_API_URL)) . 'go/upgrade?' . http_build_query($args);
    }

    /**
     * Daftar URL yang dipakai tombol upgrade di sisi JavaScript, untuk
     * di-localize ke script yang bersangkutan.
     *
     * @param array<string, array{0: string, 1: string}> $placements
     *
     * @return array<string, string>
     */
    public static function urls(array $placements): array
    {
        $urls = [];

        foreach ($placements as $handle => $definition) {
            $urls[$handle] = self::url($definition[0], isset($definition[1]) ? $definition[1] : self::TO_RTMKIT);
        }

        return $urls;
    }

    /**
     * Hub hanya menerima huruf kecil, angka, titik, garis bawah, dan strip,
     * maksimal 60 karakter; selain itu kliknya tercatat sebagai `unknown`.
     */
    private static function placement(string $placement): string
    {
        $placement = strtolower(trim($placement));
        $placement = preg_replace('/[^a-z0-9_.\-]/', '', $placement);

        return substr((string) $placement, 0, 60);
    }
}
