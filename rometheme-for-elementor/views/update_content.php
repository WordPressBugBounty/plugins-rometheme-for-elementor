<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if (! function_exists('get_plugin_data')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

$updateModule = \RTMKit\Modules\Update\UpdateModule::instance();
$plugins = $updateModule->get_plugins();
$addons = \RTMKit\Modules\Addons\AddonsModule::instance()->get_addons_list();
$addonAvailable = \RTMKit\Modules\Addons\AddonsModule::instance()->get_addons_available();
$isProActive = \RTMKit\Core\Plugin::instance()->pro_is_active();

$isRTMEssential = defined('RTM_ESSENTIAL') ? RTM_ESSENTIAL : null;

$status_labels = [
    'update' => __('Update Available', 'rometheme-for-elementor'),
    'active' => __('Active', 'rometheme-for-elementor'),
    'inactive' => __('Inactive', 'rometheme-for-elementor'),
    'not-installed' => __('Not Installed', 'rometheme-for-elementor'),
];

$normalize_version = static function ($value) {
    return ltrim(trim((string) $value), 'vV');
};

/*
 * Plugin RTMKit (free / Pro lama): update ke versi terbaru, atau rollback ke
 * versi yang dipilih.
 */
$core_cards = [];
foreach ($plugins as $plugin => $data) {
    $plugin_info = $updateModule->get_plugin_info($plugin);
    $versions = isset($plugin_info->versions) ? (array) $plugin_info->versions : [];

    if ($plugin === 'rtmkitpro') {
        if (!$isProActive || $isRTMEssential !== null) {
            continue;
        }

        $plugin_name = 'RTMKit Pro';
        $current_version = RTMPRO_PLUGIN_VERSION;
    } else {
        // Nama di wordpress.org memuat tagline ("RTMKit for Elementor – Modular ..."),
        // di kartu cukup nama produknya.
        $plugin_name = trim(preg_split('/\s+[–—-]\s+/u', html_entity_decode($plugin_info->name, ENT_QUOTES, 'UTF-8'))[0]);
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $data['file']);
        $current_version = $plugin_data['Version'] ?? null;
        unset($versions['trunk']);
    }

    $core_cards[] = [
        'plugin' => $plugin,
        'name' => $plugin_name,
        'current' => $current_version,
        'latest' => $plugin_info->version,
        'versions' => array_keys($versions),
        'min_version' => $data['min_version'],
        'update' => version_compare($current_version, $plugin_info->version, '<'),
    ];
}

/*
 * Addon sesuai plan: install, update ke rilis terbaru, atau rollback.
 */
$addon_cards = [];
if ($isProActive) {
    foreach ($addons as $addon) {
        $slug = $addon['slug'] ?? '';

        if (empty($slug) || !in_array($slug, $addonAvailable, true)) {
            continue;
        }

        $installed = is_dir(WP_PLUGIN_DIR . '/' . $slug);
        $current_version = '';
        if ($installed) {
            $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $slug . '/' . $slug . '.php', false, false);
            $current_version = $plugin_data['Version'] ?? '';
        }

        // Daftar versi dari semua rilis; rilis pertama adalah yang terbaru.
        $release_versions = [];
        foreach ((array) $updateModule->get_addon_releases($slug) as $release) {
            if (!is_array($release)) {
                continue;
            }

            $versions = $release['version_available'] ?? [];
            if (empty($versions)) {
                $versions = [$release['tag_name'] ?? $release['name'] ?? $release['version'] ?? $release['title'] ?? ''];
            }

            foreach ((array) $versions as $version) {
                if ('' !== $version) {
                    $release_versions[] = $version;
                }
            }
        }

        $latest_version = $release_versions[0] ?? '';
        $update = '' !== $normalize_version($latest_version)
            && ('' === $normalize_version($current_version) || version_compare($normalize_version($current_version), $normalize_version($latest_version), '<'));

        if (!$installed) {
            $status = 'not-installed';
        } elseif ($update) {
            $status = 'update';
        } else {
            $status = is_plugin_active($slug . '/' . $slug . '.php') ? 'active' : 'inactive';
        }

        $addon_cards[] = [
            'slug' => $slug,
            'name' => $addon['name'] ?? $slug,
            'current' => $current_version,
            'latest' => $latest_version,
            'versions' => $release_versions,
            'installed' => $installed,
            'update' => $update,
            'status' => $status,
        ];
    }
}

$addon_counts = [
    'all' => count($addon_cards),
    'update' => count(array_filter($addon_cards, fn($card) => $card['update'] && $card['installed'])),
    'installed' => count(array_filter($addon_cards, fn($card) => $card['installed'])),
    'not-installed' => count(array_filter($addon_cards, fn($card) => !$card['installed'])),
];

// Kosong sampai rilis addon pernah diambil sejak waktunya mulai dicatat.
$checked_at = (int) get_option('rtmkit_addon_releases_checked_at', 0);
$last_checked = '';
if ($checked_at && time() - $checked_at < MINUTE_IN_SECONDS) {
    $last_checked = __('just now', 'rometheme-for-elementor');
} elseif ($checked_at) {
    /* translators: %s: human-readable time difference. */
    $last_checked = sprintf(__('%s ago', 'rometheme-for-elementor'), human_time_diff($checked_at));
}

$render_versions = static function ($current, $latest) {
    ?>
    <div class="vc-card__versions">
        <span><?php esc_html_e('Current', 'rometheme-for-elementor'); ?> <strong><?php echo esc_html($current ?: '-'); ?></strong></span>
        <span><?php esc_html_e('Latest', 'rometheme-for-elementor'); ?> <strong><?php echo esc_html($latest ?: '-'); ?></strong></span>
    </div>
    <?php
};
?>
<div class="card rounded-4 flex-column gap-3">
    <div class="vc-section-head">
        <div class="d-flex flex-column gap-1">
            <h5 class="m-0">RTMKit</h5>
            <span class="vc-muted">
                <?php
                /* translators: %s: RTMKit version. */
                printf(esc_html__('Experiencing an issue with RTMKit %s? Rollback to a previous version before the issue appeared.', 'rometheme-for-elementor'), esc_html(RTM_KIT_VERSION));
                ?>
            </span>
        </div>
    </div>

    <div class="row row-cols-xl-3 row-cols-md-2 row-cols-1 g-3">
        <?php foreach ($core_cards as $card) :
            $status = $card['update'] ? 'update' : 'active';
            ?>
            <div class="col vc-item" data-name="<?php echo esc_attr(strtolower($card['name'])); ?>" data-status="<?php echo esc_attr($status); ?>">
                <div class="card vc-card h-100" data-rollback-plugin="<?php echo esc_attr($card['plugin']); ?>" data-plugin-name="<?php echo esc_attr($card['name']); ?>">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <h6 class="m-0"><?php echo esc_html($card['name']); ?></h6>
                        <span class="vc-badge is-<?php echo esc_attr($status); ?>"><?php echo esc_html($status_labels[$status]); ?></span>
                    </div>
                    <?php $render_versions($card['current'], $card['latest']); ?>
                    <div class="vc-card__actions">
                        <select name="versions" class="form-select">
                            <?php foreach ($card['versions'] as $version) : ?>
                                <option value="<?php echo esc_attr($version); ?>"
                                    <?php selected($version, $card['current']); ?>
                                    <?php disabled(version_compare($version, $card['min_version'], '<')); ?>><?php echo esc_html($version); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($card['update']) : ?>
                            <button class="btn btn-accent btn-update-plugin"
                                data-plugin="<?php echo esc_attr($card['plugin']); ?>"
                                data-plugin-name="<?php echo esc_attr($card['name']); ?>"
                                data-plugin-version="<?php echo esc_attr($card['latest']); ?>">
                                <i class="fa-solid fa-arrow-up icon"></i>
                                <span><?php esc_html_e('Update', 'rometheme-for-elementor'); ?></span>
                            </button>
                        <?php else : ?>
                            <button class="btn btn-secondary btn-reinstall-plugin">
                                <i class="fa-solid fa-rotate-left icon"></i>
                                <span><?php esc_html_e('Rollback', 'rometheme-for-elementor'); ?></span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <p class="vc-empty m-0" hidden><?php esc_html_e('No plugins match your filter.', 'rometheme-for-elementor'); ?></p>
</div>

<?php if ($isProActive) : ?>
    <div class="card rounded-4 flex-column gap-3">
        <div class="vc-section-head">
            <div class="d-flex flex-column gap-1">
                <h5 class="m-0"><?php esc_html_e('Addon Updates', 'rometheme-for-elementor'); ?></h5>
                <span class="vc-muted"><?php esc_html_e('Latest update list for addons according to your plan.', 'rometheme-for-elementor'); ?></span>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <?php if ($last_checked) : ?>
                    <span class="vc-muted text-nowrap">
                        <i class="fa-solid fa-rotate me-1"></i>
                        <?php
                        /* translators: %s: time since the last check, e.g. "5 mins ago". */
                        printf(esc_html__('Last checked: %s', 'rometheme-for-elementor'), esc_html($last_checked));
                        ?>
                    </span>
                <?php endif; ?>
                <?php if ($addon_counts['update']) : ?>
                    <span class="vc-badge is-update">
                        <?php
                        /* translators: %d: number of addons with an update. */
                        printf(esc_html(_n('%d Update Available', '%d Updates Available', $addon_counts['update'], 'rometheme-for-elementor')), (int) $addon_counts['update']);
                        ?>
                    </span>
                <?php endif; ?>
                <button type="button" class="btn btn-accent btn-sync-addon-releases"><?php esc_html_e('Check Update', 'rometheme-for-elementor'); ?></button>
            </div>
        </div>

        <nav class="vc-tabs">
            <?php
            $tabs = [
                'all' => __('All Plugins', 'rometheme-for-elementor'),
                'update' => __('Updates Available', 'rometheme-for-elementor'),
                'installed' => __('Installed', 'rometheme-for-elementor'),
                'not-installed' => __('Not Installed', 'rometheme-for-elementor'),
            ];
            foreach ($tabs as $tab => $label) : ?>
                <button type="button" class="vc-tab<?php echo 'all' === $tab ? ' active' : ''; ?>" data-vc-tab="<?php echo esc_attr($tab); ?>">
                    <?php echo esc_html($label); ?> <span class="vc-tab__count"><?php echo (int) $addon_counts[$tab]; ?></span>
                </button>
            <?php endforeach; ?>
        </nav>

        <div class="row row-cols-xl-3 row-cols-md-2 row-cols-1 g-3">
            <?php foreach ($addon_cards as $card) : ?>
                <div class="col vc-item" data-name="<?php echo esc_attr(strtolower($card['name'])); ?>" data-status="<?php echo esc_attr($card['status']); ?>" data-installed="<?php echo $card['installed'] ? '1' : '0'; ?>">
                    <div class="card vc-card h-100">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <h6 class="m-0"><?php echo esc_html($card['name']); ?></h6>
                            <span class="vc-badge is-<?php echo esc_attr($card['status']); ?>"><?php echo esc_html($status_labels[$card['status']]); ?></span>
                        </div>
                        <?php $render_versions($card['current'], $card['latest']); ?>

                        <?php if (!$card['installed']) : ?>
                            <button class="btn btn-accent w-100 install-addon" data-addon="<?php echo esc_attr($card['slug']); ?>">
                                <i class="fa-solid fa-download"></i>
                                <?php esc_html_e('Install', 'rometheme-for-elementor'); ?>
                            </button>
                        <?php else : ?>
                            <div class="vc-card__actions">
                                <select class="form-select addon-release-select" data-addon="<?php echo esc_attr($card['slug']); ?>">
                                    <?php if ($card['versions']) : ?>
                                        <?php foreach ($card['versions'] as $version) : ?>
                                            <?php
                                            // Update: default ke versi terbaru; rollback: ke versi terpasang.
                                            $is_selected = $card['update'] ? $version === $card['latest'] : $version === $card['current'];
                                            ?>
                                            <option value="<?php echo esc_attr($version); ?>" <?php selected($is_selected); ?>><?php echo esc_html($version); ?></option>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <option value=""><?php esc_html_e('No releases found', 'rometheme-for-elementor'); ?></option>
                                    <?php endif; ?>
                                </select>
                                <?php if ($card['update']) : ?>
                                    <button class="btn btn-accent btn-addon-update" data-addon="<?php echo esc_attr($card['slug']); ?>" data-addon-name="<?php echo esc_attr($card['name']); ?>" data-addon-version="<?php echo esc_attr($card['latest']); ?>">
                                        <i class="fa-solid fa-arrow-up"></i>
                                        <?php esc_html_e('Update', 'rometheme-for-elementor'); ?>
                                    </button>
                                <?php else : ?>
                                    <button class="btn btn-secondary btn-addon-rollback" data-addon="<?php echo esc_attr($card['slug']); ?>" data-addon-name="<?php echo esc_attr($card['name']); ?>">
                                        <i class="fa-solid fa-rotate-left"></i>
                                        <?php esc_html_e('Rollback', 'rometheme-for-elementor'); ?>
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="vc-empty m-0" hidden><?php esc_html_e('No addons match your filter.', 'rometheme-for-elementor'); ?></p>
    </div>
<?php endif; ?>
