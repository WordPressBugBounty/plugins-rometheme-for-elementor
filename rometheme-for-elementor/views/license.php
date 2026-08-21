<div class="px-4 mb-5 scroll-behavior-smooth" data-scrollspy="#widget-category" data-rootMargin="-30% 0px -70% 0px" tabindex="0">
    <div class="d-flex flex-column gap-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column">
                        <h1>License Manager</h1>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3">
                    License :
                    <span class="license-status">
                        <?php
                        if (class_exists('RTMKitPro\Core\Plugin') && \RTMKitPro\Modules\Licenses\LicenseStorage::instance()->isLicenseActive()) {
                            echo esc_html(\RTMKitPro\Modules\Licenses\LicenseStorage::instance()->get_product_name());
                        } elseif (\RTMKit\Core\Plugin::instance()->is_linked_account()) {
                            echo esc_html(\RTMKit\Core\Plugin::instance()->get_linked_account_info('payload.plan'));
                        } else {
                            echo 'Free';
                        }
                        ?>
                    </span>
                </div>
                <?php if (!\RTMKit\Core\Plugin::instance()->pro_is_active()) : ?>
                    <p class="m-0">Upgrade premium to unlock all features <a href="https://rometheme.net/plugins/rtmkit/pricing/" target="_blank">Upgrade Now</a></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="divider"></div>
        <div id="license-content" class="scrollspy-content gap-3" style="margin-bottom: 9rem;">
            <?php

            if (!defined('ABSPATH')) {
                exit; // Exit if accessed directly.
            }

            if (class_exists('RTMKitPro\Core\Plugin')) {
                require_once RTMPRO_PLUGIN_DIR . '/views/license.php';
            }
            ?>
        </div>
    </div>
</div>