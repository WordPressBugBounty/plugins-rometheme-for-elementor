<?php

namespace RTMKit\Modules\Telemetry;

use RTMKit\Modules\Widgets\WidgetStorage;

if (! defined('ABSPATH')) exit;

/**
 * Pemetaan nama widget Elementor ke metadata RTMKit.
 *
 * Key di metadata (`offcanvas`) bukan nama yang tersimpan di `_elementor_data`
 * (`rkit-accordion`, `rkit_advanced_heading`, `rtm_blockquote`). Satu-satunya
 * sumber yang pasti adalah `get_name()` milik kelas widgetnya, jadi peta ini
 * dibangun dengan menginstansiasi kelas lalu disimpan sampai versi plugin
 * berubah.
 */
class WidgetMap
{
    /**
     * Jaring pengaman untuk widget RTMKit yang belum ada di metadata: dikirim
     * tanpa label dan tanpa tipe.
     *
     * Hanya `rkit`, bukan `rtm`. Prefix `rtm-` dipakai bersama oleh plugin lain
     * (Font Showcase, Mega Menu, Popup Builder), dan widget mereka bukan widget
     * RTMKit — kalau ikut terhitung, peringkat widget di hub jadi salah. Widget
     * RTMKit yang memang bernama `rtm-…` sudah tercakup lewat metadata.
     */
    private const FALLBACK_PREFIXES = ['rkit-', 'rkit_'];

    private static $instance;

    /** @var array<string, array{label: string, type: string}>|null */
    private $map;

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return array<string, array{label: string, type: string}>
     */
    public function map(): array
    {
        if (null !== $this->map) {
            return $this->map;
        }

        $cached = get_option(TelemetryState::OPTION_WIDGET_MAP, []);

        if (is_array($cached) && isset($cached['version'], $cached['map']) && RTM_KIT_VERSION === $cached['version'] && is_array($cached['map'])) {
            $this->map = $cached['map'];

            return $this->map;
        }

        $this->map = $this->build();

        // Peta dibangun dengan menginstansiasi kelas widget, yang butuh
        // Elementor sudah dimuat. Kalau hasilnya kosong, kemungkinan besar
        // bukan karena tidak ada widget — jangan simpan, supaya percobaan
        // berikutnya membangunnya lagi alih-alih memakai peta kosong sampai
        // versi plugin berganti.
        if (! empty($this->map)) {
            update_option(
                TelemetryState::OPTION_WIDGET_MAP,
                ['version' => RTM_KIT_VERSION, 'map' => $this->map],
                false
            );
        }

        return $this->map;
    }

    public function flush(): void
    {
        $this->map = null;
        delete_option(TelemetryState::OPTION_WIDGET_MAP);
    }

    /**
     * Apakah nama widget ini milik RTMKit dan boleh dilaporkan.
     */
    public function is_reportable(string $widget_name): bool
    {
        if (isset($this->map()[$widget_name])) {
            return true;
        }

        foreach (self::FALLBACK_PREFIXES as $prefix) {
            if (0 === strpos($widget_name, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Label dan tipe untuk satu nama widget. Widget yang lolos lewat prefix
     * dikirim tanpa label dan tanpa tipe; hub sudah menerima keduanya kosong.
     *
     * @return array{label: string, type: string}
     */
    public function describe(string $widget_name): array
    {
        $map = $this->map();

        return isset($map[$widget_name]) ? $map[$widget_name] : ['label' => '', 'type' => ''];
    }

    /**
     * @return array<string, array{label: string, type: string}>
     */
    private function build(): array
    {
        $map = [];

        foreach (WidgetStorage::instance()->get_widget_data() as $key => $meta) {
            if (! is_array($meta)) {
                continue;
            }

            $name = $this->resolve_widget_name($key, $meta);

            if ('' === $name) {
                continue;
            }

            $type = isset($meta['type']) && in_array($meta['type'], ['free', 'pro', 'form'], true)
                ? (string) $meta['type']
                : '';

            $map[$name] = [
                'label' => isset($meta['name']) ? (string) $meta['name'] : '',
                'type'  => $type,
            ];
        }

        /**
         * Filter peta nama widget Elementor ke metadata untuk laporan pemakaian.
         *
         * Addon yang meregistrasi widget sendiri bisa menambahkan namanya di
         * sini supaya ikut terhitung.
         *
         * @since 2.1.8
         *
         * @param array<string, array{label: string, type: string}> $map
         */
        return apply_filters('rtmkit_telemetry_widget_map', $map);
    }

    /**
     * @param array<string, mixed> $meta
     */
    private function resolve_widget_name(string $key, array $meta): string
    {
        // Widget pro dinamis sudah membawa nama Elementor-nya di metadata.
        if (! empty($meta['widget_name']) && is_string($meta['widget_name'])) {
            return $meta['widget_name'];
        }

        if (empty($meta['class_name']) || ! is_string($meta['class_name'])) {
            return '';
        }

        if (! class_exists('\Elementor\Widget_Base')) {
            return '';
        }

        foreach ($this->class_candidates($key, $meta) as $class) {
            if (! class_exists($class)) {
                continue;
            }

            try {
                $widget = new $class();

                if (! method_exists($widget, 'get_name')) {
                    continue;
                }

                $name = (string) $widget->get_name();

                if ('' !== $name) {
                    return $name;
                }
            } catch (\Throwable $e) {
                // Widget yang tidak bisa diinstansiasi di luar editor dilewati;
                // kalau dipakai di halaman, prefix fallback yang menangkapnya.
                continue;
            }
        }

        return '';
    }

    /**
     * @param array<string, mixed> $meta
     *
     * @return array<int, string>
     */
    private function class_candidates(string $key, array $meta): array
    {
        $class_name = (string) $meta['class_name'];
        $category   = isset($meta['category']) ? (string) $meta['category'] : '';

        $primary = ('header' === $category)
            ? 'RTMKit\Widgets\\' . $class_name
            : 'RTMKit\Elements\\' . $class_name;

        /** This filter is documented in Inc/Modules/Widgets/WidgetStorage.php */
        $primary = apply_filters('rtmkit_widget_class_name', $primary, $key, $meta);

        return array_unique([
            $primary,
            'RTMKit\Widgets\\' . $class_name,
            'RTMKit\Elements\\' . $class_name,
            'RTMKitPro\Widgets\\' . $class_name,
            'RTMKitPro\Elements\\' . $class_name,
            'RomethemeForm\Widgets\\' . $class_name,
            $class_name,
        ]);
    }
}
