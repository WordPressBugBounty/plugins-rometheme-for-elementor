<?php
$plugins = \RTMKit\Modules\Update\UpdateModule::instance()->get_plugins();
$isProActive = class_exists(\RTMKitPro\Modules\Licenses\LicenseStorage::class)
    && \RTMKitPro\Modules\Licenses\LicenseStorage::instance()->isLicenseActive();
?>

<div class="card rounded-4  flex-column gap-3">
    <div class="pb-3 pt-1 border-bottom d-flex flex-column gap-3">
        <div class="d-flex align-items-center gap-3">
            <h4 class="m-0">Update to New Version or Rollback to Previous Version</h4>
        </div>
        <span>
            Experiencing an issue with RTMkit version <?php echo esc_html(RTM_KIT_VERSION) ?> ? Rollback to a previous version before the issue appeared.
        </span>
    </div>

    <div class="d-flex gap-3 flex-column">
        <ul class="list-group list-group-flush w-100">
            <?php foreach ($plugins as $plugin => $data) :
                $file = $data['file'];
                $plugin_info = \RTMKit\Modules\Update\UpdateModule::instance()->get_plugin_info($plugin);
                $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $file);
                $versions = $plugin_info->versions;
                if ($plugin === 'rtmkitpro') {
                    if (!$isProActive) {
                        continue;
                    }
                    $plugin_name = 'RTMKit Pro';
                    $plugin_current_version = RTMPRO_PLUGIN_VERSION;
                } else {
                    $plugin_name = $plugin_info->name;

                    if (! function_exists('get_plugin_data')) {
                        require_once ABSPATH . 'wp-admin/includes/plugin.php';
                    }
                    $plugin_current_version = $plugin_data['Version'] ?? null;
                    unset($versions['trunk']);
                }
                // var_dump($plugin_info->version);
                $updateAvailable = version_compare($plugin_current_version, $plugin_info->version, '<');
                $length = count($versions);
            ?>
                <li class="list-group-item">
                    <div class="d-flex flex-column gap-3">
                        <div class="d-flex flex-row gap-3">
                            <div class="col w-auto d-flex flex-row align-items-center bg-hover-color rounded-3 p-3">
                                <div class="col-6">
                                    <div class="d-flex flex-row align-items-center">
                                        <div class="col-6">
                                            <h6 class="m-0"><?php echo esc_html($plugin_name) ?></h6>
                                        </div>
                                        <div class="col-6">
                                            <span>
                                                Version <?php echo esc_html($plugin_current_version) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="d-flex flex-row justify-content-end align-items-center w-100">
                                        <?php if ($updateAvailable) : ?>
                                            <span class="accent-color">Version <?php echo esc_html($plugin_info->version) ?> is available</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <button class="btn <?php echo ($updateAvailable) ? esc_attr('btn-accent btn-update-plugin') : esc_attr('btn-disabled') ?> text-nowrap col-2" <?php echo ($updateAvailable) ? '' : 'disabled' ?>
                                data-plugin="<?php echo esc_attr($plugin) ?>"
                                data-plugin-name="<?php echo esc_attr($plugin_name) ?>"
                                data-plugin-version="<?php echo esc_attr($plugin_info->version) ?>">
                                <?php if ($updateAvailable) : ?>
                                    <svg class="icon" width="20" height="20" viewBox="0 0 22 24" fill="none" xmlns="http://www.w3.org/2000/svg">
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
                                    <span class="text-black">Update</span>
                                <?php else : ?>
                                    <i class="fa-solid fa-check fs-5"></i>Up to Date
                                <?php endif; ?>
                            </button>
                        </div>
                        <div class="d-flex flex-row gap-3" data-rollback-plugin="<?php echo esc_attr($plugin) ?>" data-plugin-name="<?php echo esc_attr($plugin_name) ?>">
                            <div class="col w-auto d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0">Rollback to Previous Version</h6>
                                <select name="versions" class="form-select w-auto ps-4 pe-5">
                                    <?php $index = 0;
                                    foreach ($versions as $version => $url) : $index++; ?>
                                        <option value="<?php echo esc_attr($version) ?>"
                                            <?php echo ($index === $length) ? esc_attr('selected') : '' ?>
                                            <?php echo (version_compare($version, $data['min_version'], '<')) ? esc_attr('disabled') : '' ?>>Version <?php echo esc_html($version) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button class="btn btn-secondary btn-reinstall-plugin text-nowrap col-2">
                                <svg class="icon" width="20" height="20" viewBox="0 0 22 24" fill="none" xmlns="http://www.w3.org/2000/svg">
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
                                <span style="color: currentColor;">Reinstall</span>
                            </button>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>