<?php

$addonModule = \RTMKit\Modules\Addons\AddonsModule::instance();
$addon_list        = $addonModule->get_addons_list();
$available_addons  = $addonModule->get_addons_available();
?>
<div class="px-4 mb-5 scroll-behavior-smooth scrollspy"
    data-scrollspy="#widget-category"
    data-rootMargin="-30% 0px -70% 0px"
    tabindex="0">
    <div class="d-flex flex-column gap-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex flex-column gap-2">
                <h1>RTMKit Addons</h1>
                <p class="m-0">
                    Upgrade premium to unlock all features
                    <a href="https://rometheme.net/plugins/rtmkit/pricing/" target="_blank">
                        Upgrade Now
                    </a>
                </p>
            </div>
            <div>
                <button class="btn btn-accent btn-update-plugin" id="sync-addons">
                    <svg class="icon" width="14" height="14" viewBox="0 0 22 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <g clip-path="url(#clip0_1696_11964)">
                            <path d="M18.6321 1.20117V6.11315L13.4512 6.1131" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M3.68262 22.7988V17.8869H8.86359" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M20.4634 9.6886C20.6631 10.4482 20.7693 11.2445 20.7693 12.0649C20.7693 17.3308 16.3954 21.5997 10.9999 21.5997C8.08203 21.5997 5.46292 20.3511 3.67285 18.3716" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M1.51966 14.3769C1.33076 13.6367 1.23047 12.8622 1.23047 12.0649C1.23047 6.79905 5.60433 2.53021 10.9998 2.53021C14.0768 2.53021 16.8217 3.91869 18.6124 6.08869" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
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
        <div class="scrollspy-content gap-3" style="margin-bottom:9rem;">
            <div class="card rounded-4 flex-column gap-3">
                <div class="row row-cols-xl-3 row-cols-2 g-3">
                    <?php foreach ($addon_list as $addon) :
                        $name         = $addon['name'] ?? '';
                        $slug         = $addon['slug'] ?? '';
                        $description  = $addon['description'] ?? '';
                        $image        = $addon['media']['url'] ?? '';
                        $coming_soon  = !empty($addon['is_coming_soon']);
                        $is_available = in_array($slug, $available_addons, true);
                        $is_installed = is_dir(WP_PLUGIN_DIR . '/' . $slug);
                        $is_active    = $is_installed && is_plugin_active($slug . '/' . $slug . '.php');
                    ?>
                        <div class="cols">
                            <div class="card modules h-100 p-0 overflow-hidden">
                                <div class="d-flex flex-column h-100 justify-content-between">
                                    <div class="position-relative">
                                        <img
                                            class="img-fluid"
                                            src="<?php echo esc_url($image); ?>"
                                            alt="<?php echo esc_attr($name); ?>">
                                        <?php if ($coming_soon) : ?>
                                            <div class="position-absolute top-0 end-0 me-3 mt-3 d-flex justify-content-center align-items-center bg-dark bg-opacity-50">
                                                <span class="badge bg-accent rounded-2 p-2 text-black">
                                                    Coming Soon
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex p-3 flex-column gap-2">
                                        <div class="d-flex flex-column gap-1 mb-3">
                                            <h5 class="m-0 fw-semibold">
                                                <?php echo esc_html($name); ?>
                                            </h5>
                                            <p class="lh-1">
                                                <?php echo esc_html($description); ?>
                                            </p>
                                        </div>
                                        <?php if (!$is_available || $coming_soon) : ?>
                                            <a href="https://store.rometheme.net/pricing" target="_blank" class="btn btn-secondary rounded-2 px-4 py-2 gap-2">
                                                <i class="fa-solid fa-lock"></i>
                                                Upgrade Plan
                                            </a>
                                        <?php elseif (!$is_installed) : ?>
                                            <button
                                                data-addon="<?php echo esc_attr($slug); ?>"
                                                class="btn btn-accent rounded-2 px-4 py-2 gap-2 install-addon">
                                                <i class="fa-solid fa-download"></i>
                                                Install
                                            </button>
                                        <?php elseif ($is_active) : ?>
                                            <div class="d-flex justify-content-between align-items-center gap-2">
                                                <button
                                                    class="btn btn-outline-accent rounded-2 px-4 py-2 gap-2"
                                                    style="border:1px solid var(--accent-color);color:var(--accent-color)!important;"
                                                    disabled>
                                                    <i class="fa-solid fa-check"></i>
                                                    Activated
                                                </button>
                                                <button
                                                    data-addon="<?php echo esc_attr($slug); ?>"
                                                    class="btn btn-outline-accent rounded-2 p-2 delete-addon text-danger">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        <?php else : ?>
                                            <div class="d-flex justify-content-between align-items-center gap-2">
                                                <button
                                                    data-addon="<?php echo esc_attr($slug); ?>"
                                                    class="btn btn-accent rounded-2 px-4 py-2 gap-2 activate-addon">
                                                    <i class="fa-solid fa-check"></i>
                                                    Activate Now
                                                </button>
                                                <button
                                                    data-addon="<?php echo esc_attr($slug); ?>"
                                                    class="btn btn-outline-accent rounded-2 p-2 delete-addon text-danger">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </div>
                                        <?php endif; ?>
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
