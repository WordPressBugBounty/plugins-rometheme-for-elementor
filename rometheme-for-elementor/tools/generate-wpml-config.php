<?php
/**
 * Generate wpml-config.xml without WP-CLI.
 *
 * Same generator the `wp rtmkit i18n wpml-config` command uses, driven from a
 * plain PHP bootstrap so the config can be rebuilt on machines that have PHP
 * but no WP-CLI.
 *
 *   php tools/generate-wpml-config.php [--dry-run] [--prefix=rkit,rtm]
 *
 * Widgets Elementor has not registered cannot be inspected, so any RTMKit
 * module switched off in the dashboard is reported as missing rather than
 * quietly omitted.
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "This script must be run from the command line.\n");
    exit(1);
}

$dry_run = false;
$slugs   = null;

foreach (array_slice($argv, 1) as $arg) {
    if ($arg === '--dry-run') {
        $dry_run = true;
    } elseif (strpos($arg, '--plugins=') === 0) {
        $slugs = array_filter(array_map('trim', explode(',', substr($arg, 10))));
    } else {
        fwrite(STDERR, "Unknown argument: {$arg}\n");
        exit(1);
    }
}

// Walk up from the plugin directory to find the WordPress root.
$wp_load = null;
for ($dir = __DIR__, $i = 0; $i < 8; $i++) {
    $dir = dirname($dir);
    if (file_exists($dir . '/wp-load.php')) {
        $wp_load = $dir . '/wp-load.php';
        break;
    }
}

if ($wp_load === null) {
    fwrite(STDERR, "Could not locate wp-load.php above " . __DIR__ . "\n");
    exit(1);
}

define('WP_USE_THEMES', false);
$_SERVER['HTTP_HOST']   = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/';

require_once $wp_load;

if (!class_exists('\Elementor\Plugin')) {
    fwrite(STDERR, "Elementor is not active.\n");
    exit(1);
}

if (!class_exists('\RTMKit\Modules\Helper\WpmlConfig')) {
    fwrite(STDERR, "RTMKit is not active.\n");
    exit(1);
}

$generator = new \RTMKit\Modules\Helper\WpmlConfig();

if ($slugs === null) {
    $slugs = $generator->family_slugs();
}

if (empty($slugs)) {
    fwrite(STDERR, "No Rometheme plugins detected. Pass --plugins=slug1,slug2\n");
    exit(1);
}

echo 'Plugins: ' . implode(', ', $slugs) . "\n\n";

$collected = $generator->collect($slugs);

if (empty($collected['plugins'])) {
    fwrite(STDERR, "No matching widgets with translatable controls found.\n");
    exit(1);
}

$total_widgets = 0;
$total_fields  = 0;

foreach ($collected['plugins'] as $plugin_dir => $widget_fields) {
    $fields = $generator->count_fields($widget_fields);
    $total_widgets += count($widget_fields);
    $total_fields  += $fields;

    printf(
        "%s: %d widget(s), %d translatable field(s)\n",
        basename($plugin_dir),
        count($widget_fields),
        $fields
    );

    if ($dry_run) {
        continue;
    }

    $path = rtrim($plugin_dir, '/\\') . '/wpml-config.xml';

    if (file_put_contents($path, $generator->build_xml($widget_fields)) === false) {
        fwrite(STDERR, "  could not write {$path}\n");
        continue;
    }

    echo "  wrote {$path}\n";
}

printf("\nTotal: %d widget(s), %d field(s)%s\n", $total_widgets, $total_fields, $dry_run ? ' (dry run)' : '');

if (!empty($collected['skipped'])) {
    printf("Skipped (no plugin directory resolved): %s\n", implode(', ', $collected['skipped']));
}
