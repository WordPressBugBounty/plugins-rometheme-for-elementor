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

    /**
     * Ability RTMKit didaftarkan sebagai tool MCP langsung (rtmkit-add-widget,
     * dst.). Tanpa ini klien hanya melihat tool generik discover/execute-ability,
     * dan banyak model (mis. Gemini) tidak memakainya sama sekali.
     */
    public static function expose_as_tools($config)
    {
        if (!function_exists('wp_get_abilities')) {
            return $config;
        }

        $names = [];
        foreach (wp_get_abilities() as $ability) {
            if (0 === strpos($ability->get_name(), 'rtmkit/')) {
                $names[] = $ability->get_name();
            }
        }

        $config['server_name'] = 'RTMKit for Elementor';
        $config['server_description'] = 'Build and edit Elementor pages on this WordPress site with RTMKit widgets. Workflow: rtmkit-list-widgets, then rtmkit-get-widget-schema for each widget you use, then rtmkit-get-page-structure on the target page, then add or update widgets. Every change creates a snapshot that rtmkit-restore-snapshot can undo.';
        $config['tools'] = array_values(array_unique(array_merge((array) ($config['tools'] ?? []), $names)));

        return $config;
    }

    /**
     * structuredContent MCP harus berupa object, tetapi adapter MCP (termasuk
     * salinan lama bawaan WooCommerce yang bisa termuat lebih dulu) mengirim hasil
     * ability apa adanya; klien seperti Claude lalu menandai tool sebagai gagal.
     * Daftar dibungkus {key: [...]} hanya untuk request MCP, sehingga REST
     * Abilities (dipakai build/angie.js) tetap menerima array.
     */
    public static function for_mcp(array $list, string $key)
    {
        return OAuth::is_mcp_request() ? [$key => $list] : $list;
    }

    public static function register_read(): void
    {
        $meta = ['annotations' => ['readonly' => true], 'show_in_rest' => true, 'mcp' => ['public' => true]];

        wp_register_ability('rtmkit/list-widgets', [
            'label' => 'Daftar widget RTMKit',
            'description' => 'List the RTMKit Elementor widgets available on this WordPress site (slug, title, category). Call this first when the user wants to build or edit a page, then use the slug with rtmkit-get-widget-schema and rtmkit-add-widget. Optional "category" filters the list.',
            'category' => 'rtmkit',
            'input_schema' => [
                'type' => 'object',
                'properties' => ['category' => ['type' => 'string', 'description' => 'Optional widget category to filter by.']],
            ],
            'execute_callback' => fn($in = []) => self::for_mcp(self::list_widgets((array) $in), 'widgets'),
            'permission_callback' => fn() => current_user_can('edit_posts'),
            'meta' => $meta,
        ]);

        wp_register_ability('rtmkit/get-widget-schema', [
            'label' => 'Skema setting widget',
            'description' => 'Get the valid settings (Elementor controls: key, type, options, default) of one RTMKit widget by its slug. Always call this before rtmkit-add-widget or rtmkit-update-widget-settings and only use keys returned here.',
            'category' => 'rtmkit',
            'input_schema' => [
                'type' => 'object',
                'required' => ['widget'],
                'properties' => ['widget' => ['type' => 'string', 'description' => 'Widget slug from rtmkit-list-widgets.']],
            ],
            'execute_callback' => static function ($in) {
                $controls = self::controls(sanitize_key($in['widget']));
                return is_wp_error($controls) || $controls ? $controls : self::for_mcp([], 'controls');
            },
            'permission_callback' => fn() => current_user_can('edit_posts'),
            'meta' => $meta,
        ]);

        wp_register_ability('rtmkit/get-page-structure', [
            'label' => 'Struktur halaman Elementor',
            'description' => 'Get the element tree (containers and widgets with their element IDs, without settings) of an Elementor page or post by post_id. Use the returned IDs as parent_id or element_id in other RTMKit tools. The page must already be edited with Elementor.',
            'category' => 'rtmkit',
            'input_schema' => [
                'type' => 'object',
                'required' => ['post_id'],
                'properties' => ['post_id' => ['type' => 'integer', 'description' => 'WordPress page or post ID.']],
            ],
            'execute_callback' => static function ($in) {
                $tree = self::page_structure($in);
                return is_wp_error($tree) ? $tree : self::for_mcp($tree, 'elements');
            },
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