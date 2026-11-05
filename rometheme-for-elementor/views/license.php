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
                    <span class="license-status"><?php echo esc_html(rtmkit_license_label()); ?></span>
                </div>
                <?php if (!\RTMKit\Core\Plugin::instance()->pro_is_active()) : ?>
                    <p class="m-0">Upgrade premium to unlock all features <a href="<?php echo esc_url(rtmkit_upgrade_url('banner.license')); ?>" target="_blank">Upgrade Now</a></p>
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