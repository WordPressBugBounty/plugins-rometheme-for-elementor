<?php

namespace RTMKit\Commands;

if (!defined('ABSPATH')) exit;

/**
 * WP-CLI commands for RTMKit translation tooling.
 *
 * Usage:
 *   wp rtmkit i18n wpml-config
 *   wp rtmkit i18n wpml-config --dry-run
 *   wp rtmkit i18n wpml-config --prefix=rkit,rtm
 */
class I18nCommand
{
    /**
     * Generate wpml-config.xml from the widgets Elementor has registered.
     *
     * ## OPTIONS
     *
     * [--plugins=<slugs>]
     * : Comma-separated plugin slugs to catalogue. Defaults to every active
     * plugin authored by Rometheme.
     *
     * [--dry-run]
     * : Report what would be written without touching any file.
     *
     * ## EXAMPLES
     *
     *     wp rtmkit i18n wpml-config
     *     wp rtmkit i18n wpml-config --dry-run
     *
     * @when after_wp_load
     */
    public function wpml_config($args, $assoc_args)
    {
        if (!class_exists('WP_CLI')) {
            echo "WP-CLI not available\n";
            return;
        }

        if (!class_exists('\Elementor\Plugin')) {
            \WP_CLI::error('Elementor is not loaded.');
            return;
        }

        $dry_run   = !empty($assoc_args['dry-run']);
        $generator = new \RTMKit\Modules\Helper\WpmlConfig();

        $slugs = isset($assoc_args['plugins'])
            ? array_filter(array_map('trim', explode(',', $assoc_args['plugins'])))
            : $generator->family_slugs();

        if (empty($slugs)) {
            \WP_CLI::error('No Rometheme plugins detected. Pass --plugins=slug1,slug2');
            return;
        }

        \WP_CLI::log('Plugins: ' . implode(', ', $slugs));

        $collected = $generator->collect($slugs);

        if (empty($collected['plugins'])) {
            \WP_CLI::warning('No matching widgets with translatable controls found.');
            return;
        }

        foreach ($collected['plugins'] as $plugin_dir => $widget_fields) {
            \WP_CLI::log(sprintf(
                '%s: %d widget(s), %d translatable field(s)',
                basename($plugin_dir),
                count($widget_fields),
                $generator->count_fields($widget_fields)
            ));

            if ($dry_run) {
                continue;
            }

            $path = rtrim($plugin_dir, '/\\') . '/wpml-config.xml';

            if (file_put_contents($path, $generator->build_xml($widget_fields)) === false) {
                \WP_CLI::warning("Could not write {$path}");
                continue;
            }

            \WP_CLI::log('  wrote ' . $path);
        }

        if (!empty($collected['skipped'])) {
            \WP_CLI::warning(sprintf(
                '%d widget(s) skipped, no plugin directory resolved: %s',
                count($collected['skipped']),
                implode(', ', $collected['skipped'])
            ));
        }

        \WP_CLI::success($dry_run ? 'Dry run complete.' : 'wpml-config.xml generated.');
    }
}
