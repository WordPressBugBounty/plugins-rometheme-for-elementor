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
?>
<div class="px-4 mb-5 scroll-behavior-smooth scrollspy" data-scrollspy="#widget-category"
    data-rootMargin="-30% 0px -70% 0px" tabindex="0">
    <div class="d-flex flex-column gap-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex flex-column gap-2">
                <h1>RTMKit Addons</h1>
                <div class="d-flex align-items-center gap-3">
                    License :
                    <span class="license-status">
                        <?php
                        if (class_exists('RTMKitPro\Modules\Licenses\LicenseStorage') && \RTMKitPro\Modules\Licenses\LicenseStorage::instance()->isLicenseActive()) {
                            echo esc_html(\RTMKitPro\Modules\Licenses\LicenseStorage::instance()->get_product_name());
                        } elseif (\RTMKit\Core\Plugin::instance()->is_linked_account()) {
                            echo esc_html(\RTMKit\Core\Plugin::instance()->get_linked_account_info('payload.plan'));
                        } else {
                            echo 'Free';
                        }
                        ?>
                    </span>
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
                <div class="row row-cols-xl-4 row-cols-2 g-3">
                    <?php foreach ($addon_list as $addon):
                        $name = $addon['name'] ?? '';
                        $slug = $addon['slug'] ?? '';
                        $description = $addon['description'] ?? '';
                        $image = $addon['media']['url'] ?? '';
                        $coming_soon = !empty($addon['is_coming_soon']);
                        $is_available = in_array($slug, $available_addons, true);
                        $is_installed = is_dir(WP_PLUGIN_DIR . '/' . $slug);
                        $is_active = $is_installed && is_plugin_active($slug . '/' . $slug . '.php');
                        ?>
                        <div class="cols">
                            <div class="card modules addon-card h-100 p-0 overflow-hidden">
                                <div class="d-flex flex-column h-100">
                                    <div class="position-relative addon-card__media">
                                        <img class="img-fluid" src="<?php echo esc_url($image); ?>"
                                            alt="<?php echo esc_attr($name); ?>">
                                        <?php if ($coming_soon): ?>
                                            <div
                                                class="position-absolute top-0 end-0 me-3 mt-3 d-flex justify-content-center align-items-center bg-dark bg-opacity-50">
                                                <span class="badge bg-accent rounded-2 p-2 text-black">
                                                    Coming Soon
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex p-3 flex-column gap-2 addon-card__body">
                                        <div class="d-flex flex-column gap-1 addon-card__text">
                                            <h5 class="m-0 fw-semibold addon-card__title">
                                                <?php echo esc_html($name); ?>
                                            </h5>
                                            <p class="addon-card__desc">
                                                <?php echo $description; ?>
                                            </p>
                                        </div>
                                        <div class="addon-card__action mt-auto">
                                            <?php if (!$is_available || $coming_soon): ?>
                                                <a href="<?php echo esc_url(rtmkit_upgrade_url('addons.card', 'store')); ?>"
                                                    target="_blank" class="btn btn-secondary rounded-2 px-4 py-2 gap-2">
                                                    <i class="fa-solid fa-lock"></i>
                                                    Upgrade Plan
                                                </a>
                                            <?php elseif (!$is_installed): ?>
                                                <button data-addon="<?php echo esc_attr($slug); ?>"
                                                    class="btn btn-accent rounded-2 px-4 py-2 gap-2 install-addon">
                                                    <i class="fa-solid fa-download"></i>
                                                    Install
                                                </button>
                                            <?php elseif ($is_active): ?>
                                                <div class="d-flex justify-content-between align-items-center gap-2">
                                                    <button class="btn btn-outline-accent rounded-2 px-4 py-2 gap-2"
                                                        style="border:1px solid var(--accent-color);color:var(--accent-color)!important;"
                                                        disabled>
                                                        <i class="fa-solid fa-check"></i>
                                                        Activated
                                                    </button>
                                                    <button data-addon="<?php echo esc_attr($slug); ?>"
                                                        title="Delete <?php echo esc_attr($name); ?>"
                                                        class="btn btn-accent rounded-2 p-2 delete-addon text-dark">
                                                        <svg width="14px" height="14px" viewBox="0 0 24 24" fill="none"
                                                            xmlns="http://www.w3.org/2000/svg" color="#000000">
                                                            <path
                                                                d="M20 9L18.005 20.3463C17.8369 21.3026 17.0062 22 16.0353 22H7.96474C6.99379 22 6.1631 21.3026 5.99496 20.3463L4 9"
                                                                stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                                                                stroke-linejoin="round"></path>
                                                            <path
                                                                d="M21 6L15.375 6M3 6L8.625 6M8.625 6V4C8.625 2.89543 9.52043 2 10.625 2H13.375C14.4796 2 15.375 2.89543 15.375 4V6M8.625 6L15.375 6"
                                                                stroke="#000000" stroke-width="1.5" stroke-linecap="round"
                                                                stroke-linejoin="round"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <div class="d-flex justify-content-between align-items-center gap-2">
                                                    <button data-addon="<?php echo esc_attr($slug); ?>"
                                                        class="btn btn-accent rounded-2 px-4 py-2 gap-2 activate-addon">
                                                        <i class="fa-solid fa-check"></i>
                                                        Activate Now
                                                    </button>
                                                    <button data-addon="<?php echo esc_attr($slug); ?>"
                                                        class="btn btn-outline-accent rounded-2 p-2 delete-addon text-danger">
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