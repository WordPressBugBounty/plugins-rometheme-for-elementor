<?php
namespace RTMKit\MCP\Main;

use WP_Error;

final class Writer
{

    private const MAX_SNAPSHOTS = 10;

    /* ---------------------------------------------------------------- registrasi */

    public static function register(): void
    {
        $can = static fn($in) => current_user_can('edit_post', (int) ($in['post_id'] ?? 0));
        // $meta = static fn(array $a) => ['annotations' => $a, 'show_in_rest' => true, 'mcp' => ['public' => true]];
        $meta = static fn( array $a ) => [ 'annotations' => $a, 'show_in_rest' => false, 'mcp' => [ 'public' => true ] ];
        $dry = ['type' => 'boolean', 'default' => false, 'description' => 'true = preview only, nothing is saved.'];
        // Disebut sebagai string JSON, bukan object bebas: beberapa klien (mis.
        // Gemini) menolak schema object tanpa properties. Object "settings" tetap
        // diterima oleh settings_input().
        $settings = ['type' => 'string', 'description' => 'Widget settings as a JSON object string, e.g. {"title":"Hello"}. Keys come from rtmkit-get-widget-schema.'];
        $pid = ['type' => 'integer', 'description' => 'WordPress page or post ID (the page must be edited with Elementor).'];

        wp_register_ability('rtmkit/add-widget', [
            'label' => 'Tambah widget RTMKit',
            'category' => 'rtmkit',
            'description' => 'Add an RTMKit widget to an Elementor page (post_id) and save it. "widget" is a slug from rtmkit-list-widgets; "settings" uses keys from rtmkit-get-widget-schema and is validated. Without parent_id the widget is placed in a new container at the end of the page (or at "position"); with parent_id (a container ID from rtmkit-get-page-structure) it is added inside that container. Set dry_run to preview without saving.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['post_id', 'widget'],
                'properties' => [
                    'post_id' => $pid,
                    'widget' => ['type' => 'string', 'description' => 'Widget slug from rtmkit-list-widgets.'],
                    'settings_json' => $settings,
                    'parent_id' => ['type' => 'string', 'description' => 'Optional container element ID to add the widget into.'],
                    'position' => ['type' => 'integer', 'description' => 'Optional zero-based position.'],
                    'dry_run' => $dry,
                ]
            ],
            'execute_callback' => [self::class, 'add_widget'],
            'permission_callback' => $can,
            'meta' => $meta(['readonly' => false, 'destructive' => false]),
        ]);

        wp_register_ability('rtmkit/update-widget-settings', [
            'label' => 'Ubah setting widget RTMKit',
            'category' => 'rtmkit',
            'description' => 'Change settings of an existing RTMKit widget on an Elementor page (texts, colors, links, etc.). Only the given keys change; others stay as they are. element_id comes from rtmkit-get-page-structure; keys come from rtmkit-get-widget-schema.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['post_id', 'element_id', 'settings_json'],
                'properties' => [
                    'post_id' => $pid,
                    'element_id' => ['type' => 'string', 'description' => 'Widget element ID from rtmkit-get-page-structure.'],
                    'settings_json' => $settings,
                    'dry_run' => $dry,
                ]
            ],
            'execute_callback' => [self::class, 'update_settings'],
            'permission_callback' => $can,
            'meta' => $meta(['readonly' => false, 'destructive' => false, 'idempotent' => true]),
        ]);

        wp_register_ability('rtmkit/move-widget', [
            'label' => 'Pindahkan widget RTMKit',
            'category' => 'rtmkit',
            'description' => 'Move an RTMKit widget on an Elementor page into another container (parent_id) and/or to another position inside it.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['post_id', 'element_id', 'parent_id'],
                'properties' => [
                    'post_id' => $pid,
                    'element_id' => ['type' => 'string'],
                    'parent_id' => ['type' => 'string'],
                    'position' => ['type' => 'integer'],
                    'dry_run' => $dry,
                ]
            ],
            'execute_callback' => [self::class, 'move_widget'],
            'permission_callback' => $can,
            'meta' => $meta(['readonly' => false, 'destructive' => false]),
        ]);

        wp_register_ability('rtmkit/remove-widget', [
            'label' => 'Hapus widget RTMKit',
            'category' => 'rtmkit',
            'description' => 'Remove one RTMKit widget (not a container) from an Elementor page. A snapshot is created first, so it can be undone with rtmkit-restore-snapshot.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['post_id', 'element_id'],
                'properties' => [
                    'post_id' => $pid,
                    'element_id' => ['type' => 'string'],
                    'dry_run' => $dry,
                ]
            ],
            'execute_callback' => [self::class, 'remove_widget'],
            'permission_callback' => $can,
            'meta' => $meta(['readonly' => false, 'destructive' => true]),
        ]);

        wp_register_ability('rtmkit/list-snapshots', [
            'label' => 'Daftar snapshot',
            'category' => 'rtmkit',
            'description' => 'List the undo snapshots of an Elementor page, newest first. Every change made with RTMKit tools creates one.',
            'input_schema' => ['type' => 'object', 'required' => ['post_id'], 'properties' => ['post_id' => $pid]],
            'execute_callback' => static fn($in) => Abilities::for_mcp(self::index((int) $in['post_id']), 'snapshots'),
            'permission_callback' => $can,
            'meta' => $meta(['readonly' => true]),
        ]);

        wp_register_ability('rtmkit/restore-snapshot', [
            'label' => 'Pulihkan snapshot',
            'category' => 'rtmkit',
            'description' => 'Undo: restore an Elementor page to a snapshot (default: the latest one). The current state is snapshotted first, so a restore can be undone too.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['post_id'],
                'properties' => [
                    'post_id' => $pid,
                    'snapshot_id' => ['type' => 'string'],
                    'dry_run' => $dry,
                ]
            ],
            'execute_callback' => [self::class, 'restore'],
            'permission_callback' => $can,
            'meta' => $meta(['readonly' => false, 'destructive' => true]),
        ]);

        wp_register_ability('rtmkit/validate-settings', [
            'label' => 'Validasi setting widget',
            'category' => 'rtmkit',
            'description' => 'Check RTMKit widget settings without saving anything. settings_json is the settings object encoded as a JSON string; returns the cleaned settings or the validation errors.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['widget', 'settings_json'],
                'properties' => [
                    'widget' => ['type' => 'string'],
                    'settings_json' => ['type' => 'string'],
                ]
            ],
            'execute_callback' => [Writer::class, 'validate_for'],
            'permission_callback' => fn() => current_user_can('edit_posts'),
            'meta' => $meta(['readonly' => true]),
        ]);
    }

    /* ----------------------------------------------------------------- operasi */

    public static function add_widget($in)
    {
        $slug = sanitize_key($in['widget']);
        if (!self::is_rtmkit($slug)) {
            return new WP_Error('rtmkit_unavailable', "Widget RTMKit tidak tersedia: $slug");
        }
        $controls = self::widget_controls($slug);
        if (is_wp_error($controls)) {
            return $controls;
        }
        $incoming = self::settings_input($in);
        if (is_wp_error($incoming)) {
            return $incoming;
        }
        $clean = self::validate($controls, $incoming);
        if (is_wp_error($clean)) {
            return $clean;
        }

        $el = [
            'id' => \Elementor\Utils::generate_random_string(),
            'elType' => 'widget',
            'widgetType' => $slug,
            'settings' => $clean,
            'elements' => [],
        ];
        $parent = (string) ($in['parent_id'] ?? '');
        $pos = isset($in['position']) ? (int) $in['position'] : null;

        return self::mutate((int) $in['post_id'], static function (array &$tree) use ($el, $parent, $pos) {
            if ($parent === '') {
                $at = $pos === null ? count($tree) : max(0, min($pos, count($tree)));
                array_splice($tree, $at, 0, [
                    [
                        'id' => \Elementor\Utils::generate_random_string(),
                        'elType' => 'container',
                        'settings' => [],
                        'elements' => [$el],
                        'isInner' => false,
                    ]
                ]);
            } elseif (!self::attach($tree, $parent, $el, $pos)) {
                return new WP_Error('rtmkit_bad_parent', 'parent_id tidak ditemukan atau bukan container.');
            }
            return ['element_id' => $el['id'], 'widget' => $el['widgetType'], 'settings' => $el['settings']];
        }, 'add-widget', ['widget' => $slug], !empty($in['dry_run']));
    }

    public static function update_settings($in)
    {
        $id = sanitize_text_field($in['element_id']);
        $incoming = self::settings_input($in);
        if (is_wp_error($incoming)) {
            return $incoming;
        }

        return self::mutate((int) $in['post_id'], static function (array &$tree) use ($id, $incoming) {
            $node = self::own_widget($tree, $id);
            if (is_wp_error($node)) {
                return $node;
            }
            $controls = self::widget_controls($node['widgetType']);
            if (is_wp_error($controls)) {
                return $controls;
            }
            $clean = self::validate($controls, $incoming);
            if (is_wp_error($clean)) {
                return $clean;
            }

            self::patch($tree, $id, static function (array &$n) use ($clean) {
                $n['settings'] = array_replace((array) ($n['settings'] ?? []), $clean);
            });
            return ['element_id' => $id, 'changed' => $clean];
        }, 'update-widget-settings', ['element' => $id], !empty($in['dry_run']));
    }

    public static function move_widget($in)
    {
        $id = sanitize_text_field($in['element_id']);
        $parent = sanitize_text_field($in['parent_id']);
        $pos = isset($in['position']) ? (int) $in['position'] : null;

        return self::mutate((int) $in['post_id'], static function (array &$tree) use ($id, $parent, $pos) {
            $node = self::own_widget($tree, $id);
            if (is_wp_error($node)) {
                return $node;
            }
            $el = self::detach($tree, $id);
            if (!$el || !self::attach($tree, $parent, $el, $pos)) {
                return new WP_Error('rtmkit_bad_parent', 'Tujuan tidak ditemukan atau bukan container.');
            }
            return ['element_id' => $id, 'parent_id' => $parent, 'position' => $pos];
        }, 'move-widget', ['element' => $id], !empty($in['dry_run']));
    }

    public static function remove_widget($in)
    {
        $id = sanitize_text_field($in['element_id']);

        return self::mutate((int) $in['post_id'], static function (array &$tree) use ($id) {
            $node = self::own_widget($tree, $id);
            if (is_wp_error($node)) {
                return $node;
            }
            self::detach($tree, $id);
            return ['removed' => $id, 'widget' => $node['widgetType']];
        }, 'remove-widget', ['element' => $id], !empty($in['dry_run']));
    }

    public static function restore($in)
    {
        $post_id = (int) $in['post_id'];
        $index = self::index($post_id);
        if (!$index) {
            return new WP_Error('rtmkit_no_snapshot', 'Belum ada snapshot untuk halaman ini.');
        }

        $ids = wp_list_pluck($index, 'id');
        $sid = isset($in['snapshot_id']) ? (string) $in['snapshot_id'] : end($ids);
        if (!in_array($sid, $ids, true)) {
            return new WP_Error('rtmkit_no_snapshot', "Snapshot tidak ditemukan: $sid");
        }

        $tree = json_decode((string) get_post_meta($post_id, "_rtmkit_mcp_snap_$sid", true), true);
        if (!is_array($tree)) {
            return new WP_Error('rtmkit_bad_snapshot', 'Data snapshot rusak.');
        }
        if (!empty($in['dry_run'])) {
            return ['dry_run' => true, 'would_restore' => $sid];
        }

        $doc = self::document($post_id);
        if (is_wp_error($doc)) {
            return $doc;
        }
        $pre = self::snapshot($post_id, $doc->get_elements_data(), 'pre-restore'); // restore pun bisa dibatalkan
        if (!$doc->save(['elements' => $tree])) {
            return new WP_Error('rtmkit_save_failed', 'Gagal menyimpan.');
        }
        self::audit($post_id, 'restore-snapshot', ['snapshot' => $sid]);
        return ['ok' => true, 'restored' => $sid, 'previous_state_snapshot' => $pre];
    }

    /* -------------------------------------------------------- alur mutasi umum */

    private static function mutate(int $post_id, callable $fn, string $op, array $ctx, bool $dry)
    {
        $doc = self::document($post_id);
        if (is_wp_error($doc)) {
            return $doc;
        }

        $before = $doc->get_elements_data();
        $tree = $before;
        $res = $fn($tree);
        if (is_wp_error($res)) {
            return $res;
        }
        if ($dry) {
            return ['dry_run' => true, 'preview' => $res];
        }

        $snap = self::snapshot($post_id, $before, $op);
        if (!$doc->save(['elements' => $tree])) {
            return new WP_Error('rtmkit_save_failed', 'Gagal menyimpan. Snapshot: ' . $snap);
        }
        self::audit($post_id, $op, $ctx);
        return ['ok' => true, 'snapshot' => $snap] + $res;
    }

    private static function document(int $post_id)
    {
        $doc = \Elementor\Plugin::$instance->documents->get($post_id);
        if (!$doc || !$doc->is_built_with_elementor()) {
            return new WP_Error('rtmkit_not_elementor', 'Halaman bukan Elementor.');
        }
        return $doc;
    }

    /** Hanya widget RTMKit yang boleh diubah, dipindah, atau dihapus. */
    private static function own_widget(array $tree, string $id)
    {
        $node = self::peek($tree, $id);
        if (!$node || ($node['elType'] ?? '') !== 'widget') {
            return new WP_Error('rtmkit_not_widget', 'element_id tidak ditemukan atau bukan widget.');
        }
        if (!self::is_rtmkit((string) ($node['widgetType'] ?? ''))) {
            return new WP_Error('rtmkit_foreign_widget', 'Hanya widget RTMKit yang dapat diubah lewat tool ini.');
        }
        return $node;
    }

    private static function is_rtmkit(string $slug): bool
    {
        return in_array($slug, wp_list_pluck(Abilities::list_widgets(), 'slug'), true);
    }

    private static function widget_controls(string $slug)
    {
        $w = \Elementor\Plugin::$instance->widgets_manager->get_widget_types($slug);
        return $w ? $w->get_controls() : new WP_Error('rtmkit_unknown_widget', "Widget tidak ditemukan: $slug");
    }

    /* -------------------------------------------------------------- operasi pohon */

    private static function peek(array $nodes, string $id): ?array
    {
        foreach ($nodes as $n) {
            if (($n['id'] ?? '') === $id) {
                return $n;
            }
            if (!empty($n['elements']) && ($f = self::peek($n['elements'], $id))) {
                return $f;
            }
        }
        return null;
    }

    private static function patch(array &$nodes, string $id, callable $fn): bool
    {
        foreach ($nodes as &$n) {
            if (($n['id'] ?? '') === $id) {
                $fn($n);
                return true;
            }
            if (!empty($n['elements']) && self::patch($n['elements'], $id, $fn)) {
                return true;
            }
        }
        return false;
    }

    private static function attach(array &$nodes, string $parent_id, array $el, ?int $pos): bool
    {
        $ok = false;
        $found = self::patch($nodes, $parent_id, static function (array &$p) use ($el, $pos, &$ok) {
            if (($p['elType'] ?? '') !== 'container') {
                return;
            }
            $kids = $p['elements'] ?? [];
            $at = $pos === null ? count($kids) : max(0, min($pos, count($kids)));
            array_splice($kids, $at, 0, [$el]);
            $p['elements'] = $kids;
            $ok = true;
        });
        return $found && $ok;
    }

    private static function detach(array &$nodes, string $id): ?array
    {
        foreach ($nodes as $i => &$n) {
            if (($n['id'] ?? '') === $id) {
                $out = $n;
                unset($n);
                array_splice($nodes, $i, 1);
                return $out;
            }
            if (!empty($n['elements']) && ($out = self::detach($n['elements'], $id))) {
                return $out;
            }
        }
        return null;
    }

    /* ------------------------------------------------------------------ validasi */

    private static function validate(array $controls, array $settings)
    {
        $clean = [];
        $errors = [];
        foreach ($settings as $key => $value) {
            if (!isset($controls[$key])) {
                $errors[] = "Setting tidak dikenal: $key";
                continue;
            }
            $r = self::coerce($controls[$key], $value);
            if (is_wp_error($r)) {
                $errors[] = "$key: " . $r->get_error_message();
                continue;
            }
            $clean[$key] = $r;
        }
        return $errors
            ? new WP_Error('rtmkit_invalid_settings', implode('; ', $errors), ['errors' => $errors])
            : $clean;
    }

    private static function coerce(array $c, $v)
    {
        $custom = apply_filters('rtmkit_mcp/coerce_control', null, $c, $v); // titik ekstensi tipe baru
        if ($custom !== null) {
            return $custom;
        }

        $type = $c['type'] ?? '';
        $bad = static fn(string $m) => new WP_Error('rtmkit_bad_value', $m);

        switch ($type) {
            case 'text':
                return is_string($v) ? sanitize_text_field($v) : $bad('harus string');
            case 'textarea':
                return is_string($v) ? sanitize_textarea_field($v) : $bad('harus string');
            case 'wysiwyg':
                return is_string($v) ? wp_kses_post($v) : $bad('harus string');

            case 'number':
                if (!is_numeric($v)) {
                    return $bad('harus angka');
                }
                if (isset($c['min']) && $v < $c['min']) {
                    return $bad("minimal {$c['min']}");
                }
                if (isset($c['max']) && $v > $c['max']) {
                    return $bad("maksimal {$c['max']}");
                }
                return $v + 0;

            case 'slider':
                $units = $c['size_units'] ?? ['px'];
                if (is_numeric($v)) {
                    $v = ['unit' => $units[0] ?? 'px', 'size' => $v];
                }
                if (!is_array($v) || !isset($v['size']) || !is_numeric($v['size'])) {
                    return $bad('harus {size, unit}');
                }
                $unit = $v['unit'] ?? ($units[0] ?? 'px');
                if (!in_array($unit, $units, true)) {
                    return $bad('unit harus salah satu dari: ' . implode(', ', $units));
                }
                return ['unit' => $unit, 'size' => $v['size'] + 0, 'sizes' => []];

            case 'select':
            case 'select2':
            case 'choose':
                $allowed = array_map('strval', array_keys($c['options'] ?? []));
                if (!empty($c['multiple'])) {
                    $list = (array) $v;
                    return array_diff(array_map('strval', $list), $allowed)
                        ? $bad('nilai harus dari: ' . implode(', ', $allowed)) : array_values($list);
                }
                return (is_scalar($v) && in_array((string) $v, $allowed, true))
                    ? (string) $v : $bad('nilai harus salah satu dari: ' . implode(', ', $allowed));

            case 'switcher':
                $on = $c['return_value'] ?? 'yes';
                if (in_array($v, [true, 1, '1', 'yes', 'on', $on], true)) {
                    return $on;
                }
                if (in_array($v, [false, 0, '0', '', 'no', 'off', null], true)) {
                    return '';
                }
                return $bad('harus boolean');

            case 'color':
                return (is_string($v) && preg_match('/^(#[0-9a-f]{3,8}|(rgb|hsl)a?\([^)]*\)|var\(--[\w-]+\))$/i', $v))
                    ? $v : $bad('warna tidak valid (hex, rgb[a], hsl[a], atau var(--x))');

            case 'url':
                $arr = is_array($v) ? $v : ['url' => $v];
                if (!isset($arr['url']) || !is_string($arr['url'])) {
                    return $bad('harus string URL atau {url}');
                }
                $url = esc_url_raw($arr['url']);
                if ($arr['url'] !== '' && $url === '') {
                    return $bad('URL tidak valid');
                }
                return [
                    'url' => $url,
                    'is_external' => !empty($arr['is_external']) ? 'on' : '',
                    'nofollow' => !empty($arr['nofollow']) ? 'on' : '',
                    'custom_attributes' => '',
                ];

            case 'media':
                $arr = is_array($v) ? $v : ['url' => $v];
                if (!isset($arr['url']) || !is_string($arr['url'])) {
                    return $bad('harus string URL atau {url, id}');
                }
                return ['url' => esc_url_raw($arr['url']), 'id' => (int) ($arr['id'] ?? 0)];

            case 'dimensions':
                if (!is_array($v)) {
                    return $bad('harus {top,right,bottom,left,unit}');
                }
                $units = $c['size_units'] ?? ['px'];
                $unit = $v['unit'] ?? ($units[0] ?? 'px');
                if (!in_array($unit, $units, true)) {
                    return $bad('unit harus salah satu dari: ' . implode(', ', $units));
                }
                $out = ['unit' => $unit, 'isLinked' => !empty($v['isLinked'])];
                foreach (['top', 'right', 'bottom', 'left'] as $side) {
                    $s = $v[$side] ?? '';
                    if ($s !== '' && !is_numeric($s)) {
                        return $bad("$side harus angka");
                    }
                    $out[$side] = (string) $s;
                }
                return $out;

            case 'repeater':
                if (!is_array($v) || !array_is_list($v)) {
                    return $bad('harus array item');
                }
                if (count($v) > 50) {
                    return $bad('maksimal 50 item');
                }
                $fields = [];
                foreach ((array) ($c['fields'] ?? []) as $k => $f) {
                    $fields[$f['name'] ?? $k] = $f;
                }
                $items = [];
                foreach ($v as $i => $item) {
                    if (!is_array($item)) {
                        return $bad("item #$i harus objek");
                    }
                    $id = isset($item['_id']) && is_string($item['_id']) ? sanitize_key($item['_id']) : \Elementor\Utils::generate_random_string();
                    unset($item['_id']);
                    $clean = self::validate($fields, $item);
                    if (is_wp_error($clean)) {
                        return $bad("item #$i: " . $clean->get_error_message());
                    }
                    $items[] = ['_id' => $id] + $clean;
                }
                return $items;
        }

        return $bad("tipe control '$type' belum didukung"); // default: tolak, bukan loloskan
    }

    /* ------------------------------------------------------- snapshot dan audit */

    private static function index(int $post_id): array
    {
        $idx = get_post_meta($post_id, '_rtmkit_mcp_snapshots', true);
        return is_array($idx) ? $idx : [];
    }

    private static function snapshot(int $post_id, array $tree, string $op): string
    {
        $id = gmdate('Ymd-His') . '-' . wp_generate_password(4, false);
        $index = self::index($post_id);

        update_post_meta($post_id, "_rtmkit_mcp_snap_$id", wp_slash(wp_json_encode($tree)));
        $index[] = ['id' => $id, 'op' => $op, 'time' => time(), 'user' => get_current_user_id()];
        while (count($index) > self::MAX_SNAPSHOTS) {
            $old = array_shift($index);
            delete_post_meta($post_id, '_rtmkit_mcp_snap_' . $old['id']);
        }
        update_post_meta($post_id, '_rtmkit_mcp_snapshots', $index);
        return $id;
    }

    private static function audit(int $post_id, string $op, array $ctx): void
    {
        $log = get_option('rtmkit_mcp_audit', []);
        $log = is_array($log) ? $log : [];
        $log[] = ['t' => time(), 'user' => get_current_user_id(), 'post' => $post_id, 'op' => $op, 'ctx' => $ctx];
        update_option('rtmkit_mcp_audit', array_slice($log, -200), false);
    }

    /**
     * Setting dari input tool: settings_json (string JSON) atau settings (object,
     * untuk klien yang memakai schema lama).
     */
    private static function settings_input($in)
    {
        if (isset($in['settings_json']) && '' !== trim((string) $in['settings_json'])) {
            $raw = json_decode((string) $in['settings_json'], true);
            return is_array($raw) ? $raw : new WP_Error('rtmkit_bad_json', 'settings_json is not a valid JSON object.');
        }

        return (array) ($in['settings'] ?? []);
    }

    public static function validate_for($in)
    {
        $slug = sanitize_key($in['widget'] ?? '');
        if (!self::is_rtmkit($slug)) {
            return new WP_Error('rtmkit_unavailable', "Widget RTMKit tidak tersedia: $slug");
        }
        $controls = self::widget_controls($slug);
        if (is_wp_error($controls)) {
            return $controls;
        }
        $raw = json_decode((string) ($in['settings_json'] ?? '{}'), true);
        if (!is_array($raw)) {
            return new WP_Error('rtmkit_bad_json', 'settings_json bukan JSON objek yang valid.');
        }
        $clean = self::validate($controls, $raw);
        return is_wp_error($clean) ? $clean : ['settings' => $clean];
    }
}