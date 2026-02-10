<?php

$template = isset($_POST['template']) ? sanitize_text_field($_POST['template']) : 'premium';
$freeTemplateCategories = \RTMKit\Modules\Templatekits\TemplatekitAPI::instance()->get_template_categories();

?>

<div class="px-4 mb-5 scroll-behavior-smooth scrollspy" data-scrollspy="#widget-category" data-rootMargin="-30% 0px -70% 0px" tabindex="0">
    <div class="d-flex flex-column gap-3">
        <div class="d-flex justify-content-between align-items-start">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column">
                        <h1>Template Kits</h1>
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
                <button id="add-themebuilder" class="btn btn-accent fw-bold px-4 py-3 gap-2">
                    <i class="rtmicon rtmicon-plus fs-5"></i>
                    Upgrade to Pro
                </button>
            </div>
        </div>
        <div class="divider"></div>
        <div class="position-sticky d-flex flex-column gap-3" style="top: 0rem;z-index: 1;background: var(--primary-color);">
            <div class="d-flex bg-secondary sticky-top justify-content-between border rounded-4 py-2 px-2">
                <div class="toggle-container template-tab shadow">
                    <div class="menu-switcher">
                        <button class="menu-switch btn <?php echo ($template === 'templatekits') ? esc_attr('active') : '' ?>" data-templates="templatekits">Template Kits</button>
                        <button class="menu-switch btn <?php echo ($template === 'themeforest') ? esc_attr('active') : '' ?>" data-templates="themeforest">Themeforest Template</button>
                        <button class="menu-switch btn <?php echo ($template === 'installed') ? esc_attr('active') : '' ?>" data-templates="installed">Installed Template</button>
                    </div>
                </div>
                <div class="d-flex flex-row gap-2">
                    <div id="free-categories" class="form-control align-items-center gap-2 category-dropdown">
                        <div class="d-flex gap-2 align-items-center h-100">
                            <svg width="18" height="13" viewBox="0 0 18 13" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <g clip-path="url(#clip0_1696_11503)">
                                    <path d="M10.4972 1.57343C9.96737 2.15266 9.42056 2.73066 8.88825 3.2933C8.33754 3.87541 7.76812 4.47737 7.21436 5.08348C6.95602 5.36624 6.81344 5.72957 6.81318 6.10578L6.80979 10.967C6.7032 10.8942 6.59698 10.8226 6.49166 10.7516C6.15132 10.5222 5.82706 10.3036 5.54644 10.0835C5.52849 9.42147 5.53221 8.7563 5.53611 8.05862C5.53979 7.40625 5.54351 6.73173 5.52781 6.05841C5.51914 5.68685 5.3719 5.3306 5.11284 5.05451L1.85104 1.57805L10.4972 1.57343M10.9854 0.0217285L1.32838 0.026888C0.17775 0.0787712 -0.426601 1.46714 0.346885 2.29229L3.91308 6.09322C3.94745 7.56583 3.88559 9.04332 3.94493 10.5135C3.99496 10.7687 4.14336 10.9714 4.3442 11.1392C5.0506 11.7294 5.937 12.2251 6.66241 12.8051C6.88326 12.9389 7.10803 12.9998 7.32013 12.9998C7.87633 12.9998 8.3449 12.5808 8.42423 11.9594L8.42828 6.1069C9.64052 4.78005 10.9068 3.49726 12.1063 2.16099C12.7605 1.23074 12.1323 0.0976051 10.9854 0.0217285Z" fill="currentColor" />
                                    <path d="M12.2483 5.43291L16.9155 5.42664C18.2523 5.50677 18.3798 7.24544 17.1501 7.56377C15.5379 7.62449 13.9145 7.57207 12.2983 7.59058C10.8987 7.49403 10.8985 5.56385 12.2483 5.43291Z" fill="currentColor" />
                                    <path d="M12.2483 10.8388L16.9155 10.8325C18.2523 10.9127 18.3798 12.6513 17.1501 12.9697C15.5379 13.0304 13.9145 12.978 12.2983 12.9965C10.8987 12.8999 10.8985 10.9697 12.2483 10.8388Z" fill="currentColor" />
                                    <path d="M14.5003 0.0269195C15.3342 -0.0441224 16.2916 0.0479542 17.1318 0.0476295C18.2794 0.280094 18.2801 1.90305 17.1506 2.15781C16.3151 2.1612 15.3814 2.24195 14.5503 2.18462C13.142 2.08746 13.1616 0.140969 14.5003 0.0269195Z" fill="currentColor" />
                                </g>
                                <defs>
                                    <clipPath id="clip0_1696_11503">
                                        <rect width="18" height="13" fill="currentColor" />
                                    </clipPath>
                                </defs>
                            </svg>

                        </div>
                        <span class="active-text pe-4">All Categories</span>
                        <div class="dropdown-icon">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="dropdown-menu p-2">
                            <div class="row row-cols-3 g-2">
                                <div class="col">
                                    <div class="dropdown-item rounded-1 category-item" data-category="all">
                                        All Categories
                                    </div>
                                </div>
                                <?php foreach ($freeTemplateCategories as $category) : ?>
                                    <div class="col">
                                        <div class="dropdown-item rounded-1 category-item" data-category="<?php echo esc_attr($category) ?>">
                                            <?php echo esc_html(ucwords(str_replace('-', ' ', $category))) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="search-container border gap-2">
                        <i class="rtmicon rtmicon-search search-icon"></i>
                        <input type="text" name="search" id="search-templates" class="search-input" placeholder="Search Template Kit...">
                    </div>
                    <div class="action-button-container gap-2 p-1" style="display: none;">
                        <button id="delete-all-installed-template" class="btn btn-danger rounded-2"><i class="far fa-trash-can"></i>Delete All Installed Template</button>
                        <button id="import-all-template" class="btn btn-secondary rounded-2"><i class="fas fa-cloud-arrow-down"></i>Import All Template</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="scrollspy-content gap-3" style="margin-bottom: 9rem;">
            <div id="template_container">

            </div>
        </div>
    </div>
</div>
</div>