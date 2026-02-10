<?php

$widgetCategory = [
    'creative' => "Creative",
    'showcase' => "Showcase",
    'bussiness' => "Bussiness",
    'header' => "Header & Footer",
    'marketing' => 'Marketing',
    'infographic' => 'Infographic',
    'post' => 'Post'
];

if (class_exists('RomethemeForm')) {
    $widgetCategory['form'] = 'Form';
}

$widgetStorage = \RTMKit\Modules\Widgets\WidgetStorage::instance();
$widgetOptions = $widgetStorage->get_widget_options();
$totalWidgets = count($widgetStorage->get_widget_data());
$activeWidgets = count(array_filter($widgetOptions, function ($widget) {
    return $widget['status'] === true;
}));

$isProActive = class_exists('RTMKitPro\Modules\Licenses\LicenseStorage') && \RTMKitPro\Modules\Licenses\LicenseStorage::instance()->isLicenseActive();

?>

<div class="px-4 mb-5 scroll-behavior-smooth scrollspy" data-scrollspy="#widget-category" data-rootMargin="-30% 0px -70% 0px" tabindex="0">
    <div class="d-flex flex-column gap-3">
        <div class="d-flex justify-content-between align-items-start">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column">
                        <h1>Widgets Manager</h1>
                        <div class="d-flex gap-2">
                            <div class="d-flex bg-hover-color rounded-4 px-2 py-2 gap-2 align-items-center">
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10.0869 0.711914C4.9091 0.711914 0.711914 4.9091 0.711914 10.0869C0.711914 15.2647 4.9091 19.4619 10.0869 19.4619C15.2647 19.4619 19.4619 15.2647 19.4619 10.0869C19.4619 4.9091 15.2647 0.711914 10.0869 0.711914ZM14.4935 8.03816L9.27473 13.0382C9.15379 13.1538 8.99816 13.2119 8.84223 13.2119C8.69098 13.2119 8.53973 13.1572 8.42004 13.0479L5.69066 10.5485C5.43598 10.3154 5.41848 9.92004 5.6516 9.66535C5.88473 9.41098 6.28004 9.39316 6.53473 9.62629L8.83254 11.73L13.6285 7.13504C13.8779 6.89629 14.2735 6.90504 14.5122 7.1541C14.751 7.40348 14.7425 7.7991 14.4932 8.03785L14.4935 8.03816Z" fill="#B0B0B0" />
                                </svg>
                                <span class="text-color"><?php echo esc_html($totalWidgets) ?> Total Widgets</span>
                            </div>
                            <div class="d-flex bg-approved-color success-color rounded-4 px-2 py-2 gap-2 align-items-center">
                                <svg class="success-color" width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M10.0869 0.711914C4.9091 0.711914 0.711914 4.9091 0.711914 10.0869C0.711914 15.2647 4.9091 19.4619 10.0869 19.4619C15.2647 19.4619 19.4619 15.2647 19.4619 10.0869C19.4619 4.9091 15.2647 0.711914 10.0869 0.711914ZM14.4935 8.03816L9.27473 13.0382C9.15379 13.1538 8.99816 13.2119 8.84223 13.2119C8.69098 13.2119 8.53973 13.1572 8.42004 13.0479L5.69066 10.5485C5.43598 10.3154 5.41848 9.92004 5.6516 9.66535C5.88473 9.41098 6.28004 9.39316 6.53473 9.62629L8.83254 11.73L13.6285 7.13504C13.8779 6.89629 14.2735 6.90504 14.5122 7.1541C14.751 7.40348 14.7425 7.7991 14.4932 8.03785L14.4935 8.03816Z" fill="currentColor" />
                                </svg>
                                <span id="active-widgets" class="success-color"><?php echo esc_html($activeWidgets) ?> Active Widgets</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    License :
                    <span class="license-status">
                        <?php
                        if (class_exists('RTMKitPro\Core\Plugin') && \RTMKitPro\Modules\Licenses\LicenseStorage::instance()->isLicenseActive()) {
                            echo \RTMKitPro\Modules\Licenses\LicenseStorage::instance()->get_product_name();
                        } else {
                            echo 'Free';
                        }
                        ?>
                    </span>
                </div>
                <p class="m-0">Upgrade premium to unlock all features <a href="https://rometheme.net/plugins/rtmkit/pricing/" target="_blank">Upgrade Now</a></p>
            </div>
            <div class="mb-4">
                <button id="save-widgets" class="btn btn-accent px-4 py-3 gap-2">
                    <i class="rtmicon rtmicon-file-circle-check fs-5"></i>
                    Save Changes
                </button>
            </div>
        </div>
        <div class="divider"></div>
        <div class="position-sticky d-flex flex-column gap-3" style="top: 0rem;z-index: 1;background: var(--primary-color);">
            <div class="d-flex bg-secondary justify-content-between border rounded-4 py-2 px-2">
                <div class="d-flex">
                    <div class="d-flex align-items-center rounded-3 border p-2 gap-3">
                        <label class="switch gap-3">
                            <input id="enable-all" name="" class="switch-input" type="checkbox" value="true" hidden>
                            <span class="slider round"></span>
                            <span class="text-white me-2">Enable All</span>
                        </label>
                    </div>
                    <button id="reset-all-widgets" class="btn btn-link border-0 bg-transparent text-white gap-2">
                        Reset All
                    </button>
                </div>
                <div class="d-flex gap-3 align-items-stretch">
                    <div class="toggle-container border">
                        <div class="menu-switcher">
                            <button class="menu-switch btn active" data-type="all">All Widget</button>
                            <button class="menu-switch btn" data-type="free">Free Widget</button>
                            <button class="menu-switch btn" data-type="pro">Pro Widget</button>
                        </div>
                    </div>
                    <div class="search-container border gap-2">
                        <i class="rtmicon rtmicon-search search-icon"></i>
                        <input type="text" name="search" id="search-widget" class="search-input" placeholder="Search widget name...">
                    </div>
                </div>
            </div>
            <ul id="widget-category" class="nav scrollspy">
                <?php foreach ($widgetCategory as $k => $category) : ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#<?php echo esc_attr($k) ?>">
                            <?php echo esc_html($category) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="scrollspy-content gap-3" style="margin-bottom: 9rem;">
            <?php foreach ($widgetCategory as $k => $category) : ?>
                <div id="<?php echo esc_attr($k) ?>" class="card rounded-4  flex-column gap-3">
                    <div class="pb-4 pt-1 border-bottom d-flex align-items-center gap-3">
                        <i class="fa-solid fa-circle" style="font-size: 10px;"></i>
                        <h4 class="m-0"><?php echo esc_html($category) ?></h4>
                    </div>
                    <div class="row row-cols-3 g-3">
                        <?php
                        $widgets = $widgetStorage->get_widget_by_category($k);
                        foreach ($widgets as $key => $widget) :
                            if ($widget['type'] == 'pro') {
                                if ($isProActive) {
                                    $disabled = '';
                                } else {
                                    $disabled = 'disabled';
                                }
                            } else {
                                $disabled = '';
                            }
                            $status = $widgetOptions[$key]['status'] ? 'checked' : '';
                        ?>
                            <div class="col">
                                <div class="card widgets p-3" data-widget-type="<?php echo esc_attr($widget['type']) ?>">
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <div class="d-flex gap-3 align-items-center">
                                            <i class="<?php echo esc_attr($widget['icon']) ?> widget-icon"></i>
                                            <div class="d-flex flex-column gap-2">
                                                <h5 class="m-0 d-flex align-items-center lh-1 gap-2 position-relative">
                                                    <?php echo esc_attr($widget['name']) ?>
                                                    <?php if ($widget['type'] == 'pro'): ?>
                                                        <span class="badge pro position-absolute" style="left: 110%;">
                                                            <i class="fa-solid fa-crown"></i>
                                                            Pro</span>
                                                    <?php elseif (isset($widget['is_new']) and $widget['is_new']) : ?>
                                                        <span class="badge new position-absolute" style="left: 110%;">
                                                            <i class="fa-solid fa-gift"></i>
                                                            New</span>
                                                    <?php elseif (isset($widget['is_popular']) and $widget['is_popular']) : ?>
                                                        <span class="badge popular position-absolute" style="left: 110%;">
                                                            <i class="fa-solid fa-star"></i>
                                                            Popular</span>
                                                    <?php endif; ?>
                                                </h5>
                                                <div class="d-flex gap-2">
                                                    <a href="<?php echo esc_url($widget['docsURL']) ?>" target="_blank" class="link">Docs</a>
                                                    <a href="<?php echo esc_url($widget['previewURL']) ?>" target="_blank" class="link">Demo</a>
                                                    <a href="<?php echo esc_url($widget['videoURL']) ?>" target="_blank" class="link">Video</a>
                                                </div>
                                            </div>
                                        </div>
                                        <label id="<?php echo esc_attr($key) ?>" class="switch gap-3">
                                            <input name="<?php echo esc_attr($key) ?>"
                                                class="switch-input switch-status"
                                                type="checkbox" value="true"
                                                hidden
                                                <?php echo esc_attr($status) ?>
                                                <?php echo esc_attr($disabled) ?>>
                                            <span class="slider round">
                                                <?php if ($widget['type'] == 'pro' && ! $isProActive) : ?>
                                                    <i class="fa-solid fa-lock"></i>
                                                <?php endif; ?>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>