<?php

$addonModule = \RTMKit\Modules\Addons\AddonsModule::instance();
$addon_list = $addonModule->get_addons_list();
$available_addons = $addonModule->get_addons_available();

usort($addon_list, function ($a, $b) {
    $aComingSoon = !empty($a['is_coming_soon']);
    $bComingSoon = !empty($b['is_coming_soon']);

    if ($aComingSoon === $bComingSoon) {
        return 0;
    }

    return $aComingSoon ? 1 : -1;
});

// Isi popup gear: modul addon (dipindah dari halaman Modules) dan halaman
// pengaturannya (mis. Smooth Scroll), hanya untuk addon yang aktif.
$isProActive = \RTMKit\Core\Plugin::instance()->pro_is_active();
$module_options = \RTMKit\Modules\Storage::instance()->get_module_option();
$menu_items = array_merge([], ...array_values(\RTMKit\Modules\Menu::instance()->get_menus()));
$addon_settings = [];

foreach ($addon_list as $addon) {
    $slug = $addon['slug'] ?? '';

    if ('' === $slug || !is_plugin_active($slug . '/' . $slug . '.php')) {
        continue;
    }

    $pages = [];
    foreach (\RTMKit\Modules\Addons\AddonsModule::ADDON_PAGES[$slug] ?? [] as $key) {
        if (isset($menu_items[$key]['menu_slug'])) {
            $pages[$key] = $menu_items[$key];
        }
    }

    $addon_settings[$slug] = [
        'modules' => $addonModule->get_addon_modules($slug),
        'pages' => $pages,
    ];
}
?>
<div class="px-4 mb-5 scroll-behavior-smooth scrollspy" data-scrollspy="#widget-category"
    data-rootMargin="-30% 0px -70% 0px" tabindex="0">
    <div class="d-flex flex-column gap-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex flex-column gap-2">
                <h1>RTMKit Addons</h1>
                <div class="d-flex align-items-center gap-3">
                    License :
                    <span class="license-status"><?php echo esc_html(rtmkit_license_label()); ?></span>
                    <?php if (\RTMKit\Core\Plugin::instance()->pro_is_active()): ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=rtmkit&path=updates')); ?>"
                            class="btn btn-outline-accent rounded-2 px-4 py-2 gap-2"
                            style="border:1px solid var(--accent-color);color:var(--accent-color)!important;">
                            <svg class="icon" width="14" height="14" viewBox="0 0 22 24" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_1696_11964)">
                                    <path d="M18.6321 1.20117V6.11315L13.4512 6.1131" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M3.68262 22.7988V17.8869H8.86359" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <path
                                        d="M20.4634 9.6886C20.6631 10.4482 20.7693 11.2445 20.7693 12.0649C20.7693 17.3308 16.3954 21.5997 10.9999 21.5997C8.08203 21.5997 5.46292 20.3511 3.67285 18.3716"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path
                                        d="M1.51966 14.3769C1.33076 13.6367 1.23047 12.8622 1.23047 12.0649C1.23047 6.79905 5.60433 2.53021 10.9998 2.53021C14.0768 2.53021 16.8217 3.91869 18.6124 6.08869"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </g>
                                <defs>
                                    <clipPath id="clip0_1696_11964">
                                        <rect width="22" height="24" fill="white" />
                                    </clipPath>
                                </defs>
                            </svg>
                            Check Updates
                        </a>
                    <?php endif; ?>
                </div>
                <?php if (!\RTMKit\Core\Plugin::instance()->pro_is_active()): ?>
                    <p class="m-0">Upgrade premium to unlock all features <a
                            href="<?php echo esc_url(rtmkit_upgrade_url('banner.addons')); ?>" target="_blank">Upgrade
                            Now</a></p>
                <?php endif; ?>
            </div>
            <div>
                <button class="btn btn-accent btn-update-plugin" id="sync-addons">
                    <svg class="icon" width="14" height="14" viewBox="0 0 22 24" fill="none"
                        xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_1696_11964)">
                            <path d="M18.6321 1.20117V6.11315L13.4512 6.1131" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M3.68262 22.7988V17.8869H8.86359" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                            <path
                                d="M20.4634 9.6886C20.6631 10.4482 20.7693 11.2445 20.7693 12.0649C20.7693 17.3308 16.3954 21.5997 10.9999 21.5997C8.08203 21.5997 5.46292 20.3511 3.67285 18.3716"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path
                                d="M1.51966 14.3769C1.33076 13.6367 1.23047 12.8622 1.23047 12.0649C1.23047 6.79905 5.60433 2.53021 10.9998 2.53021C14.0768 2.53021 16.8217 3.91869 18.6124 6.08869"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </g>
                        <defs>
                            <clipPath id="clip0_1696_11964">
                                <rect width="22" height="24" fill="white" />
                            </clipPath>
                        </defs>
                    </svg>
                    <span class="text text-black">Sync Now</span>
                </button>
            </div>
        </div>
        <div class="divider"></div>
        <div class="scrollspy-content gap-3 rtm-addons" style="margin-bottom:9rem;">
            <div class="card rounded-4 flex-column gap-3">
                <?php if (empty($addon_list)): ?>
                    <div class="d-flex flex-column gap-2 py-4 text-center">
                        <h5 class="m-0 fw-semibold">No addons to show</h5>
                        <p class="m-0">We could not reach the RTMHub addons service. Check your connection and press
                            <strong>Sync Now</strong> to try again.
                        </p>
                    </div>
                <?php endif; ?>
                <div class="row row-cols-xl-3 row-cols-2 g-3">
                    <?php foreach ($addon_list as $addon):
                        $name = $addon['name'] ?? '';
                        $slug = $addon['slug'] ?? '';
                        // Warna dari editor RTMHub ditulis sebagai rgb(...), yang dibuang
                        // wp_kses_post(); diubah ke hex supaya warnanya tetap terbawa.
                        $description = preg_replace_callback(
                            '/rgb\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)/i',
                            static function ($rgb) {
                                return sprintf('#%02x%02x%02x', min(255, (int) $rgb[1]), min(255, (int) $rgb[2]), min(255, (int) $rgb[3]));
                            },
                            (string) ($addon['description'] ?? '')
                        );

                        // Paragraf deskripsi yang diawali "*" adalah catatan kebutuhan
                        // (mis. "*Requires the WooCommerce plugin."). Ditampilkan terpisah:
                        // di dalam deskripsi yang dipotong 3 baris, catatan itu tidak terlihat.
                        $notes = [];
                        if (preg_match_all('#<p\b[^>]*>.*?</p>#is', $description, $paragraphs)) {
                            $description = '';
                            foreach ($paragraphs[0] as $paragraph) {
                                $text = trim(html_entity_decode(wp_strip_all_tags($paragraph), ENT_QUOTES, 'UTF-8'));
                                if (0 === strpos($text, '*')) {
                                    // HTML aslinya dipakai, supaya format dari RTMHub (merah, tebal, miring) tetap.
                                    $notes[] = $paragraph;
                                } elseif ('' !== $text) {
                                    $description .= $paragraph;
                                }
                            }
                        }

                        $image = $addon['media']['url'] ?? '';
                        $coming_soon = !empty($addon['is_coming_soon']);
                        $is_available = in_array($slug, $available_addons, true);
                        $is_installed = is_dir(WP_PLUGIN_DIR . '/' . $slug);
                        $is_active = $is_installed && is_plugin_active($slug . '/' . $slug . '.php');
                        ?>
                        <div class="cols">
                            <div class="card modules addon-card h-100 p-0 overflow-hidden<?php echo $is_active ? ' is-active' : ''; ?>">
                                <div class="d-flex flex-column h-100">
                                    <div class="position-relative addon-card__media">
                                        <img class="img-fluid" src="<?php echo esc_url($image); ?>"
                                            alt="<?php echo esc_attr($name); ?>" loading="lazy">
                                        <?php if ($coming_soon): ?>
                                            <span class="addon-card__status is-soon">Coming Soon</span>
                                        <?php elseif (!$is_available): ?>
                                            <span class="addon-card__status is-locked"><i class="fa-solid fa-lock"></i> Pro</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex p-3 flex-column gap-2 addon-card__body">
                                        <h5 class="m-0 fw-semibold addon-card__title">
                                            <?php echo esc_html($name); ?>
                                        </h5>
                                        <div class="addon-card__desc">
                                            <?php echo wp_kses_post($description); ?>
                                        </div>
                                        <?php foreach ($notes as $note) : ?>
                                            <div class="addon-card__note">
                                                <?php echo wp_kses_post($note); ?>
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="addon-card__action d-flex gap-2 mt-auto">
                                            <?php if (!$is_available || $coming_soon): ?>
                                                <a href="<?php echo esc_url(rtmkit_upgrade_url('addons.card', 'store')); ?>"
                                                    target="_blank" class="btn btn-secondary flex-grow-1 py-2 gap-2">
                                                    <i class="fa-solid fa-crown"></i>
                                                    Upgrade Plan
                                                </a>
                                            <?php elseif (!$is_installed): ?>
                                                <button data-addon="<?php echo esc_attr($slug); ?>"
                                                    class="btn btn-accent flex-grow-1 py-2 gap-2 install-addon">
                                                    <i class="fa-solid fa-download"></i>
                                                    Install
                                                </button>
                                            <?php else: ?>
                                                <label class="switch addon-card__toggle gap-2">
                                                    <input type="checkbox" class="switch-input addon-toggle" data-addon="<?php echo esc_attr($slug); ?>" hidden <?php checked($is_active); ?>>
                                                    <span class="slider round"></span>
                                                    <span class="addon-card__toggle-label"><?php echo $is_active ? 'Active' : 'Inactive'; ?></span>
                                                </label>
                                                <div class="d-flex gap-2 ms-auto">
                                                    <?php // Gear hanya untuk addon yang punya modul / halaman pengaturan. ?>
                                                    <?php if ($addonModule->addon_has_settings($slug)) : ?>
                                                    <button type="button" data-addon="<?php echo esc_attr($slug); ?>"
                                                        data-addon-name="<?php echo esc_attr($name); ?>"
                                                        title="<?php echo esc_attr($is_active ? $name . ' settings' : 'Activate ' . $name . ' to manage its modules'); ?>"
                                                        aria-label="<?php echo esc_attr($name); ?> settings"
                                                        class="btn btn-secondary addon-card__icon-btn addon-settings" <?php disabled(!$is_active); ?>>
                                                        <i class="fa-solid fa-gear"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                    <button type="button" data-addon="<?php echo esc_attr($slug); ?>"
                                                        data-addon-name="<?php echo esc_attr($name); ?>"
                                                        title="Delete <?php echo esc_attr($name); ?>"
                                                        aria-label="Delete <?php echo esc_attr($name); ?>"
                                                        class="btn btn-secondary addon-card__icon-btn addon-card__delete delete-addon">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addonSettingsModal" tabindex="-1" aria-labelledby="addonSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 shadow p-0">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="addonSettingsModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: invert(1) grayscale(1);"></button>
            </div>
            <div class="modal-body p-4">
                <?php foreach ($addon_settings as $slug => $settings) : ?>
                    <div class="addon-settings-panel d-flex flex-column gap-3" data-addon="<?php echo esc_attr($slug); ?>" hidden>
                        <?php if ($settings['modules']) : ?>
                            <div class="d-flex flex-column gap-2">
                                <h6 class="m-0 addon-settings-panel__heading">Modules</h6>
                                <?php foreach ($settings['modules'] as $key => $module) :
                                    $locked = ($module['type'] ?? '') === 'pro' && !$isProActive;
                                    $checked = !$locked && !empty($module_options[$key]['status']);
                                    ?>
                                    <div class="addon-settings-row" data-module-type="<?php echo esc_attr($module['type'] ?? 'free'); ?>">
                                        <i class="<?php echo esc_attr($module['icon'] ?? ''); ?> addon-settings-row__icon"></i>
                                        <span class="flex-grow-1"><?php echo esc_html($module['name'] ?? $key); ?></span>
                                        <label class="switch">
                                            <input name="<?php echo esc_attr($key); ?>" class="switch-input switch-status" type="checkbox" value="true" hidden <?php checked($checked); ?> <?php disabled($locked); ?>>
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php foreach ($settings['pages'] as $page) : ?>
                            <div class="d-flex flex-column gap-2">
                                <h6 class="m-0 addon-settings-panel__heading"><?php echo esc_html($page['title']); ?></h6>
                                <?php if (!empty($page['render_view']) && is_file($page['render_view'])) : ?>
                                    <?php // Form pengaturan addon langsung di popup; scope terpisah supaya variabel view tidak bentrok. ?>
                                    <div class="addon-settings-page">
                                        <?php (static function ($file) { include $file; })($page['render_view']); ?>
                                    </div>
                                <?php else : ?>
                                    <div class="addon-settings-row">
                                        <i class="fa-solid fa-sliders addon-settings-row__icon"></i>
                                        <span class="flex-grow-1"><?php echo esc_html($page['title']); ?></span>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=' . $page['menu_slug'])); ?>" class="btn btn-secondary btn-sm px-3">Open</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!$settings['modules'] && !$settings['pages']) : ?>
                            <p class="m-0 text-center addon-settings-panel__empty">This addon has no modules to configure.</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                <button type="button" id="save-addon-modules" class="btn btn-accent px-4">Save Changes</button>
            </div>
        </div>
    </div>
</div>