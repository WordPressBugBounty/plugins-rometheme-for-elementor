<?php

/**
 * Layar untuk menu addon yang terkunci (item menu dengan 'locked' => true),
 * saat halamannya dibuka langsung lewat URL.
 *
 * @var array $locked_menu Item menu dari filter rtmkit_menus.
 */

if (!defined('ABSPATH')) {
    exit;
}

$locked_title = $locked_menu['title'] ?? __('This feature', 'rometheme-for-elementor');
$locked_description = $locked_menu['locked_description']
    ?? __('Upgrade your plan to unlock this feature.', 'rometheme-for-elementor');
?>

<div class="px-4 mb-5">
    <div class="d-flex flex-column gap-3">
        <h1><?php echo esc_html($locked_title); ?></h1>
        <div class="divider"></div>
        <div class="d-flex flex-column align-items-center text-center gap-3 py-5 px-4 border rounded-4 bg-secondary">
            <i class="fa-solid fa-lock fs-1 text-muted"></i>
            <h3 class="m-0">
                <?php
                /* translators: %s: feature name */
                echo esc_html(sprintf(__('%s is locked', 'rometheme-for-elementor'), $locked_title));
                ?>
            </h3>
            <p class="m-0" style="max-width: 520px;"><?php echo esc_html($locked_description); ?></p>
            <a href="<?php echo esc_url(rtmkit_upgrade_url('locked_feature.cta')); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-accent fw-bold px-4 py-3 d-inline-flex align-items-center gap-2">
                <i class="fa-solid fa-crown"></i>
                <?php esc_html_e('Upgrade to Pro', 'rometheme-for-elementor'); ?>
            </a>
        </div>
    </div>
</div>
