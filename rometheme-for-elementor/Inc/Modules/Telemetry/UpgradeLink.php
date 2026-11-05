<?php

namespace RTMKit\Modules\Telemetry;

if (! defined('ABSPATH')) exit;

/**
 * Pembentuk URL tombol "Upgrade to Pro".
 *
 * Semua tombol upgrade langsung ke halaman pricing store (PRICING_URL).
 * Parameter placement dan tujuan tetap diterima supaya pemanggil, termasuk
 * plugin pendamping lewat rtmkit_upgrade_url(), tidak perlu diubah.
 */
class UpgradeLink
{
    public const PRICING_URL = 'https://store.rometheme.net/pricing';

    /** Halaman pricing RTMKit. */
    public const TO_RTMKIT = 'rtmkit';

    /** Halaman pricing store (addon dan kit berbayar). */
    public const TO_STORE = 'store';

    /** Halaman pricing utama rometheme.net. */
    public const TO_MAIN = 'main';

    /**
     * Langsung ke RTMHub, tanpa singgah di website. go/upgrade belum punya
     * tujuan RTMHub (semua `to` diteruskan ke pricing website), jadi URL ini
     * dibentuk di sini dan tidak tercatat sebagai klik.
     */
    public const TO_HUB = 'hub';

    /**
     * @param string $placement Nama tombol, mis. `templatekit.import_modal`.
     * @param string $to        Halaman tujuan: rtmkit, store, atau main.
     * @param string $plugin    Slug plugin yang memasang tombolnya.
     */
    public static function url(string $placement, string $to = self::TO_RTMKIT, string $plugin = TelemetryState::PLUGIN_RTMKIT): string
    {
        return self::PRICING_URL;
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
}
