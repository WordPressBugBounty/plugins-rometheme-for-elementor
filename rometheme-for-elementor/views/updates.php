<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="px-4 mb-5 scroll-behavior-smooth scrollspy">
    <div class="d-flex flex-column gap-3">
        <div class="vc-header card rounded-4 flex-row align-items-center justify-content-between gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-3">
                <div class="vc-header__icon">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <div class="d-flex flex-column gap-1">
                    <h4 class="m-0">Version Control</h4>
                    <span class="vc-muted">Manage and control the version of your Rometheme plugins.</span>
                    <?php if (!\RTMKit\Core\Plugin::instance()->pro_is_active()) : ?>
                        <span class="vc-muted">Upgrade premium to unlock all features <a href="<?php echo esc_url(rtmkit_upgrade_url('banner.updates')); ?>" target="_blank">Upgrade Now</a></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="d-flex align-items-stretch gap-2">
                <div class="search-container border gap-2">
                    <i class="rtmicon rtmicon-search search-icon"></i>
                    <input type="text" id="vc-search" class="search-input" placeholder="Search plugins...">
                </div>
                <select id="vc-status" class="form-select w-auto">
                    <option value="all">All Status</option>
                    <option value="update">Update Available</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="not-installed">Not Installed</option>
                </select>
            </div>
        </div>
        <div id="update-content" class="scrollspy-content gap-3" style="margin-bottom: 9rem;">

        </div>
    </div>
</div>
