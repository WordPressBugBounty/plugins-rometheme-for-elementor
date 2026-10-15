<?php
namespace RTMKit\MCP\Main;

final class Abilities
{

    public static function register_category(): void
    {
        wp_register_ability_category('rtmkit', [
            'label' => 'RTMKit',
            'description' => 'Widget dan halaman RTMKit',
        ]);
    }

    public static function register_read(): void
    {
        $meta = ['annotations' => ['readonly' => true], 'show_in_rest' => true, 'mcp' => ['public' => true]];

        wp_register_ability('rtmkit/list-widgets', [
            'label' => 'Daftar widget RTMKit',
            'description' => 'Widget RTMKit yang tersedia (lisensi valid dan aktif).',
            'category' => 'rtmkit',
            'input_schema' => [
                'type' => 'object',
                'default' => [],
                'properties' => ['category' => ['type' => 'string']],
            ],
            'output_schema' => ['type' => 'array'],
            'execute_callback' => [self::class, 'list_widgets'],
            'permission_callback' => fn() => current_user_can('edit_posts'),
            'meta' => $meta,
        ]);

        wp_register_ability('rtmkit/get-widget-schema', [
            'label' => 'Skema setting widget',
            'description' => 'Control yang valid untuk satu widget, diambil dari Elementor.',
            'category' => 'rtmkit',
            'input_schema' => [
                'type' => 'object',
                'required' => ['widget'],
                'properties' => ['widget' => ['type' => 'string']],
            ],
            'execute_callback' => fn($in) => self::controls(sanitize_key($in['widget'])),
            'permission_callback' => fn() => current_user_can('edit_posts'),
            'meta' => $meta,
        ]);

        wp_register_ability('rtmkit/get-page-structure', [
            'label' => 'Struktur halaman Elementor',
            'description' => 'Pohon elemen halaman (tanpa setting, hemat token).',
            'category' => 'rtmkit',
            'input_schema' => [
                'type' => 'object',
                'required' => ['post_id'],
                'properties' => ['post_id' => ['type' => 'integer']],
            ],
            'execute_callback' => [self::class, 'page_structure'],
            'permission_callback' => fn($in) => current_user_can('edit_post', (int) ($in['post_id'] ?? 0)),
            'meta' => $meta,
        ]);
    }

    /** Katalog dari JSON, difilter dengan gerbang yang sama seperti WidgetLoader. */
    public static function list_widgets($in = [])
    {
        $cat = $in['category'] ?? null;
        $meta = self::catalog_by_class();
        $out = [];

        foreach (\Elementor\Plugin::$instance->widgets_manager->get_widget_types() as $name => $widget) {
            $class = get_class($widget);
            if (!str_starts_with($class, 'RTMKit\\')) {
                continue;
            } // hanya widget RTMKit

            $short = substr(strrchr($class, '\\'), 1);
            $m = $meta[$short] ?? [];
            if ($cat && ($m['category'] ?? '') !== $cat) {
                continue;
            }

            $out[] = [
                'slug' => $name,                 // nama widget Elementor, dipakai ability lain
                'title' => $widget->get_title(),
                'category' => $m['category'] ?? null,
                'type' => $m['type'] ?? null,
            ];
        }
        return $out;
    }

    /** Metadata katalog, dikunci oleh class_name. */
    private static function catalog_by_class(): array
    {
        $path = apply_filters('rtmkit_mcp/catalog_path', RTMKIT_PATH . 'widgets.json'); // ADAPTASI
        $all = json_decode((string) @file_get_contents($path), true) ?: [];
        $by = [];
        foreach ($all as $w) {
            if (!empty($w['class_name'])) {
                $by[$w['class_name']] = $w;
            }
        }
        return $by;
    }

    /** Skema diambil dari instance widget, bukan ditulis manual. */
    public static function controls(string $slug)
    {
        $widget = \Elementor\Plugin::$instance->widgets_manager->get_widget_types($slug);
        if (!$widget) {
            return new \WP_Error('rtmkit_unknown_widget', "Widget tidak ditemukan: $slug");
        }
        $out = [];
        foreach ($widget->get_controls() as $id => $c) {
            if (in_array($c['type'], ['section', 'tab'], true) || str_starts_with($id, '_')) {
                continue;
            }
            $out[$id] = array_filter([
                'type' => $c['type'],
                'label' => $c['label'] ?? null,
                'default' => $c['default'] ?? null,
                'options' => isset($c['options']) ? array_keys($c['options']) : null,
            ], fn($v) => $v !== null);
        }
        return $out;
    }

    public static function page_structure($in)
    {
        $doc = \Elementor\Plugin::$instance->documents->get((int) $in['post_id']);
        if (!$doc || !$doc->is_built_with_elementor()) {
            return new \WP_Error('rtmkit_not_elementor', 'Halaman bukan Elementor.');
        }
        $walk = function (array $nodes) use (&$walk) {
            return array_map(fn($n) => array_filter([
                'id' => $n['id'],
                'elType' => $n['elType'],
                'widget' => $n['widgetType'] ?? null,
                'children' => $walk($n['elements'] ?? []) ?: null,
            ]), $nodes);
        };
        return $walk($doc->get_elements_data());
    }
}