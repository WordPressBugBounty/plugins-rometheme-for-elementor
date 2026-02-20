<?php

use function ElementorDeps\str_contains;

$upload_dir = wp_upload_dir();
$rtmTemplateDir = $upload_dir['basedir'] . '/rometheme_template';
$rtmTemplateUrl = $upload_dir['baseurl'] . '/rometheme_template';
$isProActive = class_exists('\RTMKitPro\Core\Plugin') && \RTMKitPro\Modules\Licenses\LicenseStorage::instance()->isLicenseActive();
?>


<?php if (isset($_POST['installed_template'])) :
    $hashId = sanitize_text_field($_POST['installed_template']);
    $imported = get_option('rtm_import_template_' . $hashId, []);
    $manifest = json_decode(file_get_contents($rtmTemplateDir . '/' . $hashId . '/manifest.json'));

    $templateModule = \RTMKit\Modules\Templatekits\TemplatekitModule::instance();
    $template_id = $templateModule->get_installed_template_id($hashId);
    $missing_plugin = [];
    if (!empty($manifest->required_plugins)) {
        $missing_plugin = $templateModule->missing_plugins($manifest->required_plugins);
    }

    $previewImg = $rtmTemplateUrl . '/' . $hashId . '/' . $manifest->templates[0]->screenshot;
?>

    <?php if (count($missing_plugin) > 0) : ?>
        <div class="alert alert-warning" role="alert">
            <div class="d-flex flex-row justify-content-between align-items-center">
                <span>
                    <span class="fw-bold">Attention!</span> There are <?php echo esc_html(count($missing_plugin)) ?> requirements that need installing for this Template Kit to work correctly.
                </span>
                <button type="button" class="btn btn-warning btn-install-requirements" data-missing="<?php echo esc_attr(json_encode($missing_plugin)) ?>"><i class="fas fa-circle-info"></i> Install Requirements</button>
            </div>
        </div>
    <?php endif ?>

    <div class="row row-cols-lg-3 row-cols-md-2 row-cols-1 g-3">
        <?php foreach ($manifest->templates as $t) :
            $imgurl = $upload_dir['baseurl'] . '/rometheme_template/' . $hashId . '/' . $t->screenshot;
            $key = strtolower(str_replace(' ', '_', html_entity_decode($templateModule->normalize_dash_key($t->name))));
            $source = strtolower((string) ($t->source ?? ''));

            $source = (string) ($t->source ?? '');

            $type = strtolower(basename($source)) === 'global.json'
                ? 'global'
                : 'demo';

        ?>
            <div class="col">
                <div class="card rounded-4 shadow-sm p-3 h-100 gap-3">
                    <div class="overflow-hidden" style="aspect-ratio:3/2;">
                        <img class="img-fluid rounded-top" src="<?php echo esc_url($imgurl) ?>">
                    </div>
                    <div class="d-flex flex-column gap-3">
                        <div class="d-block">
                            <h5 class="text-truncate text-white m-0"><?php echo esc_html($t->name) ?></h5>
                        </div>
                        <div class="d-flex flex-row gap-2">
                            <?php if (isset($imported[$key])) : ?>
                                <a href="<?php echo esc_url(admin_url('post.php?post=' . $imported[$key] . '&action=elementor')) ?>" class="btn btn-secondary rounded-2 text-white text-nowrap">
                                    <i class="far fa-eye "></i>
                                    View Template</a>
                                <button class="btn btn-danger fw-light rounded-2 text-nowrap delete-installed-template" data-template-name="<?php echo esc_attr($t->name) ?>" data-template="<?php echo esc_attr($hashId) ?>" data-item-template="<?php echo esc_attr($imported[$key]) ?>">
                                    <i class="far fa-trash-can"></i>
                                    Delete</button>
                            <?php else : ?>
                                <button class="btn btn-secondary rounded-2 import-template text-nowrap"
                                    data-template-name="<?php echo esc_attr(str_replace(' ', '_', $t->name)) ?>"
                                    data-template="<?php echo esc_attr($hashId) ?>"
                                    data-path="<?php echo esc_attr($t->source) ?>"
                                    data-image-url="<?php echo esc_url($imgurl) ?>"
                                    data-template-type="<?php echo esc_attr($type) ?>">
                                    <i class="fas fa-plus"></i>Import
                                </button>
                                <a id="preview_template" target="_blank" href="<?php echo esc_url($t->preview_url) ?>" class="btn border-white text-white rounded-2"><i class="far fa-eye"></i>Preview</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Modal -->
    <div class="modal fade rtm-text-font" id="import-single" tabindex="-1" aria-labelledby="import-single-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow p-0">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="import-single-label">Import Template</h5>
                </div>

                <div class="d-flex flex-column gap-3 p-4">
                    <div class="overflow-hidden rounded-3" style="aspect-ratio: 16/9;">
                        <img src="" alt="" class="img-fluid rounded-3" id="template-screenshot">
                    </div>
                    <div class="rounded-3 p-3 d-flex flex-row gap-2 align-items-center justify-content-between" style="background-color: var(--hover-color);">
                        <h6 class="m-0">Import As Page</h6>
                        <?php if ($isProActive) : ?>
                            <label class="switch gap-3">
                                <input name="set-as-page"
                                    id="import-as-page"
                                    class="switch-input switch-status"
                                    type="checkbox" value="true"
                                    hidden>
                                <span class="slider round">

                                </span>
                            </label>
                        <?php else : ?>
                            <a href="http://localhost/wp.new/wp-admin/admin.php?page=rtmkit-upgrade-to-pro" class="btn btn-accent fw-bold" target="_blank">
                                <svg width="30" height="30" viewBox="0 0 24 29" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3.49698 9.00236L4.78398 19.9374H19.227L20.513 9.00236L16.503 11.6754L12.005 5.37836L7.50698 11.6754L3.49698 9.00236ZM2.80598 6.13736L7.00498 8.93736L11.191 3.07636C11.2835 2.94673 11.4056 2.84107 11.5472 2.76816C11.6888 2.69526 11.8457 2.65723 12.005 2.65723C12.1642 2.65723 12.3212 2.69526 12.4628 2.76816C12.6044 2.84107 12.7265 2.94673 12.819 3.07636L17.005 8.93636L21.205 6.13736C21.3639 6.03169 21.5497 5.97368 21.7404 5.97019C21.9312 5.96669 22.119 6.01785 22.2817 6.11762C22.4443 6.2174 22.575 6.36163 22.6584 6.53328C22.7417 6.70493 22.7742 6.89684 22.752 7.08636L21.11 21.0534C21.0816 21.2968 20.9647 21.5213 20.7817 21.6843C20.5986 21.8472 20.3621 21.9373 20.117 21.9374H3.89398C3.6489 21.9373 3.41236 21.8472 3.22931 21.6843C3.04625 21.5213 2.92941 21.2968 2.90098 21.0534L1.25798 7.08736C1.2354 6.89761 1.26767 6.70536 1.35095 6.53337C1.43424 6.36138 1.56506 6.21686 1.72792 6.11691C1.89079 6.01696 2.07889 5.96576 2.26995 5.96939C2.461 5.97301 2.64702 6.0313 2.80598 6.13736ZM12.006 15.9374C11.7433 15.9374 11.4833 15.8858 11.2406 15.7853C10.9979 15.6849 10.7774 15.5376 10.5916 15.3519C10.4059 15.1663 10.2585 14.9458 10.1579 14.7032C10.0573 14.4606 10.0055 14.2005 10.0055 13.9379C10.0054 13.6752 10.0571 13.4151 10.1575 13.1725C10.258 12.9298 10.4052 12.7093 10.5909 12.5235C10.7766 12.3377 10.997 12.1904 11.2397 12.0898C11.4823 11.9892 11.7423 11.9374 12.005 11.9374C12.5354 11.9374 13.0441 12.1481 13.4192 12.5231C13.7943 12.8982 14.005 13.4069 14.005 13.9374C14.005 14.4678 13.7943 14.9765 13.4192 15.3516C13.0441 15.7266 12.5364 15.9374 12.006 15.9374Z" fill="#121416"></path>
                                </svg>
                                Upgrade to Pro
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="rounded-3 p-3 d-flex flex-row gap-2 align-items-center justify-content-between" style="background-color: var(--hover-color);">
                        <h6 class="m-0">Import Without Image</h6>
                        <label class="switch gap-3">
                            <input name="without-image"
                                id="import-without-images"
                                class="switch-input switch-status"
                                type="checkbox" value="true"
                                hidden checked>
                            <span class="slider round">

                            </span>
                        </label>
                    </div>
                </div>

                <div class="modal-footer px-4 border-0 pb-4 pt-0 d-flex justify-content-between gap-2">
                    <div class="d-flex gap-2 align-items-center col" id="message" style="visibility: hidden;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="#F15C5C" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2.01709C6.477 2.01709 2 6.49409 2 12.0171C2 17.5401 6.477 22.0171 12 22.0171C17.523 22.0171 22 17.5401 22 12.0171C22 6.49409 17.523 2.01709 12 2.01709ZM17.646 17.6631C16.138 19.1711 14.133 20.0021 12 20.0021C9.867 20.0021 7.862 19.1711 6.354 17.6631C4.846 16.1551 4.015 14.1501 4.015 12.0171C4.015 9.88409 4.846 7.87909 6.354 6.37109C7.862 4.86309 9.867 4.03209 12 4.03209C14.133 4.03209 16.138 4.86309 17.646 6.37109C19.154 7.87909 19.985 9.88409 19.985 12.0171C19.985 14.1501 19.154 16.1551 17.646 17.6631Z" fill="#F15C5C" />
                            <path d="M12.4601 7.02295H11.5391C11.0671 7.02295 10.6931 7.41995 10.7201 7.89095L11.0491 13.5639C11.0741 13.9979 11.4331 14.3369 11.8681 14.3369H12.1301C12.5651 14.3369 12.9241 13.9979 12.9491 13.5639L13.2781 7.89095C13.3051 7.41995 12.9311 7.02295 12.4591 7.02295H12.4601Z" fill="#F15C5C" />
                            <path d="M11.9999 16.996C12.5506 16.996 12.9969 16.5496 12.9969 15.999C12.9969 15.4483 12.5506 15.002 11.9999 15.002C11.4493 15.002 11.0029 15.4483 11.0029 15.999C11.0029 16.5496 11.4493 16.996 11.9999 16.996Z" fill="#F15C5C" />
                        </svg>
                        <span class="lh-1">
                            <small>
                                Please do not refresh or close this page until the process is complete.
                            </small>
                        </span>
                    </div>
                    <div class="d-flex flex-row gap-2 col justify-content-end">
                        <button id="close-btn" type="button" class="btn btn-link bg-transparent border-0 text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                        <button id="start-import" class="btn btn-accent"> <svg width="18" height="18" viewBox="0 0 18 18" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.3837 9.26905C17.3933 9.2457 17.3989 9.22152 17.4059 9.19761C17.4118 9.17764 17.4194 9.15852 17.4236 9.13798C17.4321 9.09636 17.4357 9.05389 17.4366 9.0117C17.4366 9.00805 17.4377 9.00439 17.4377 9.00073C17.4377 8.95461 17.4329 8.9082 17.4239 8.86264C17.4197 8.84183 17.4118 8.82242 17.4059 8.80217C17.3989 8.77855 17.3936 8.75464 17.384 8.73158C17.3739 8.70739 17.3601 8.68517 17.3474 8.66239C17.3379 8.64523 17.3306 8.62723 17.3193 8.61064C17.2934 8.57183 17.2642 8.53583 17.2316 8.5032L10.9614 2.23245C10.6869 1.95767 10.2414 1.95767 9.96714 2.23245C9.69236 2.50695 9.69236 2.95217 9.96714 3.22667L15.0356 8.29536L1.26668 8.2827H1.26611C0.87827 8.2827 0.56327 8.59714 0.562988 8.98527C0.562707 9.37367 0.877145 9.68867 1.26555 9.68895L15.0389 9.70161L9.96743 14.7734C9.69264 15.0479 9.69264 15.4931 9.96743 15.7676C10.1047 15.9049 10.2847 15.9735 10.4647 15.9735C10.6447 15.9735 10.8247 15.9049 10.9619 15.7676L17.2318 9.4977C17.2647 9.46508 17.294 9.4288 17.3199 9.38998C17.3317 9.37255 17.3396 9.3537 17.3497 9.33542C17.3618 9.31348 17.375 9.29239 17.3846 9.26905H17.3837Z" fill="currentColor" />
                            </svg>
                            Import</button>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade rtm-text-font" id="import-all" tabindex="-1" aria-labelledby="import-all-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 shadow p-0">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="import-all-label">Import All Template</h5>
                </div>

                <div class="d-flex flex-column gap-3 p-4">
                    <div class="overflow-hidden rounded-3" style="aspect-ratio: 16/9;">
                        <img src="<?php echo esc_url($previewImg); ?>" alt="" class="img-fluid rounded-3" id="template-screenshot">
                    </div>
                    <select name="import-type" id="import-type" class="form-select p-3 rounded-3" style="background-color: var(--hover-color);">
                        <option value="all">Full Import</option>
                        <option value="content">Demo Content Only</option>
                        <option value="global-only">Global Style Only</option>
                    </select>
                    <div class="rounded-3 p-3 d-flex flex-row gap-2 align-items-center justify-content-between" style="background-color: var(--hover-color);">
                        <h6 class="m-0">Import As Page</h6>
                        <?php if ($isProActive) : ?>
                            <label class="switch gap-3">
                                <input name="set-as-page"
                                    id="import-as-page"
                                    class="switch-input switch-status"
                                    type="checkbox" value="true"
                                    hidden>
                                <span class="slider round">

                                </span>
                            </label>
                        <?php else : ?>
                            <a href="http://localhost/wp.new/wp-admin/admin.php?page=rtmkit-upgrade-to-pro" class="btn btn-accent fw-bold" target="_blank">
                                <svg width="30" height="30" viewBox="0 0 24 29" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3.49698 9.00236L4.78398 19.9374H19.227L20.513 9.00236L16.503 11.6754L12.005 5.37836L7.50698 11.6754L3.49698 9.00236ZM2.80598 6.13736L7.00498 8.93736L11.191 3.07636C11.2835 2.94673 11.4056 2.84107 11.5472 2.76816C11.6888 2.69526 11.8457 2.65723 12.005 2.65723C12.1642 2.65723 12.3212 2.69526 12.4628 2.76816C12.6044 2.84107 12.7265 2.94673 12.819 3.07636L17.005 8.93636L21.205 6.13736C21.3639 6.03169 21.5497 5.97368 21.7404 5.97019C21.9312 5.96669 22.119 6.01785 22.2817 6.11762C22.4443 6.2174 22.575 6.36163 22.6584 6.53328C22.7417 6.70493 22.7742 6.89684 22.752 7.08636L21.11 21.0534C21.0816 21.2968 20.9647 21.5213 20.7817 21.6843C20.5986 21.8472 20.3621 21.9373 20.117 21.9374H3.89398C3.6489 21.9373 3.41236 21.8472 3.22931 21.6843C3.04625 21.5213 2.92941 21.2968 2.90098 21.0534L1.25798 7.08736C1.2354 6.89761 1.26767 6.70536 1.35095 6.53337C1.43424 6.36138 1.56506 6.21686 1.72792 6.11691C1.89079 6.01696 2.07889 5.96576 2.26995 5.96939C2.461 5.97301 2.64702 6.0313 2.80598 6.13736ZM12.006 15.9374C11.7433 15.9374 11.4833 15.8858 11.2406 15.7853C10.9979 15.6849 10.7774 15.5376 10.5916 15.3519C10.4059 15.1663 10.2585 14.9458 10.1579 14.7032C10.0573 14.4606 10.0055 14.2005 10.0055 13.9379C10.0054 13.6752 10.0571 13.4151 10.1575 13.1725C10.258 12.9298 10.4052 12.7093 10.5909 12.5235C10.7766 12.3377 10.997 12.1904 11.2397 12.0898C11.4823 11.9892 11.7423 11.9374 12.005 11.9374C12.5354 11.9374 13.0441 12.1481 13.4192 12.5231C13.7943 12.8982 14.005 13.4069 14.005 13.9374C14.005 14.4678 13.7943 14.9765 13.4192 15.3516C13.0441 15.7266 12.5364 15.9374 12.006 15.9374Z" fill="#121416"></path>
                                </svg>
                                Upgrade to Pro
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="rounded-3 p-3 d-flex flex-row gap-2 align-items-center justify-content-between" style="background-color: var(--hover-color);">
                        <h6 class="m-0">Import Without Image</h6>
                        <label class="switch gap-3">
                            <input name="without-image"
                                id="import-without-images"
                                class="switch-input switch-status"
                                type="checkbox" value="true"
                                hidden checked>
                            <span class="slider round">

                            </span>
                        </label>
                    </div>
                </div>

                <div class="modal-footer px-4 border-0 pb-4 pt-0 d-flex justify-content-between gap-2">
                    <div class="d-flex gap-2 align-items-center col" id="message" style="visibility: hidden;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="#F15C5C" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2.01709C6.477 2.01709 2 6.49409 2 12.0171C2 17.5401 6.477 22.0171 12 22.0171C17.523 22.0171 22 17.5401 22 12.0171C22 6.49409 17.523 2.01709 12 2.01709ZM17.646 17.6631C16.138 19.1711 14.133 20.0021 12 20.0021C9.867 20.0021 7.862 19.1711 6.354 17.6631C4.846 16.1551 4.015 14.1501 4.015 12.0171C4.015 9.88409 4.846 7.87909 6.354 6.37109C7.862 4.86309 9.867 4.03209 12 4.03209C14.133 4.03209 16.138 4.86309 17.646 6.37109C19.154 7.87909 19.985 9.88409 19.985 12.0171C19.985 14.1501 19.154 16.1551 17.646 17.6631Z" fill="#F15C5C" />
                            <path d="M12.4601 7.02295H11.5391C11.0671 7.02295 10.6931 7.41995 10.7201 7.89095L11.0491 13.5639C11.0741 13.9979 11.4331 14.3369 11.8681 14.3369H12.1301C12.5651 14.3369 12.9241 13.9979 12.9491 13.5639L13.2781 7.89095C13.3051 7.41995 12.9311 7.02295 12.4591 7.02295H12.4601Z" fill="#F15C5C" />
                            <path d="M11.9999 16.996C12.5506 16.996 12.9969 16.5496 12.9969 15.999C12.9969 15.4483 12.5506 15.002 11.9999 15.002C11.4493 15.002 11.0029 15.4483 11.0029 15.999C11.0029 16.5496 11.4493 16.996 11.9999 16.996Z" fill="#F15C5C" />
                        </svg>
                        <span class="lh-1">
                            <small>
                                Please do not refresh or close this page until the process is complete.
                            </small>
                        </span>
                    </div>
                    <div class="d-flex flex-row gap-2 col justify-content-end">
                        <button id="close-btn" type="button" class="btn btn-link bg-transparent border-0 text-decoration-none" data-bs-dismiss="modal">Cancel</button>
                        <button id="start-import-all" class="btn btn-accent">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                                <path d="M17.3837 9.26905C17.3933 9.2457 17.3989 9.22152 17.4059 9.19761C17.4118 9.17764 17.4194 9.15852 17.4236 9.13798C17.4321 9.09636 17.4357 9.05389 17.4366 9.0117C17.4366 9.00805 17.4377 9.00439 17.4377 9.00073C17.4377 8.95461 17.4329 8.9082 17.4239 8.86264C17.4197 8.84183 17.4118 8.82242 17.4059 8.80217C17.3989 8.77855 17.3936 8.75464 17.384 8.73158C17.3739 8.70739 17.3601 8.68517 17.3474 8.66239C17.3379 8.64523 17.3306 8.62723 17.3193 8.61064C17.2934 8.57183 17.2642 8.53583 17.2316 8.5032L10.9614 2.23245C10.6869 1.95767 10.2414 1.95767 9.96714 2.23245C9.69236 2.50695 9.69236 2.95217 9.96714 3.22667L15.0356 8.29536L1.26668 8.2827H1.26611C0.87827 8.2827 0.56327 8.59714 0.562988 8.98527C0.562707 9.37367 0.877145 9.68867 1.26555 9.68895L15.0389 9.70161L9.96743 14.7734C9.69264 15.0479 9.69264 15.4931 9.96743 15.7676C10.1047 15.9049 10.2847 15.9735 10.4647 15.9735C10.6447 15.9735 10.8247 15.9049 10.9619 15.7676L17.2318 9.4977C17.2647 9.46508 17.294 9.4288 17.3199 9.38998C17.3317 9.37255 17.3396 9.3537 17.3497 9.33542C17.3618 9.31348 17.375 9.29239 17.3846 9.26905H17.3837Z" fill="currentColor" />
                            </svg>
                            Import</button>
                    </div>
                </div>
            </div>
        </div>
    </div>


<?php else : ?>
    <div class="row row-cols-lg-3 row-cols-md-2 row-cols-1 g-3">
        <div class="col">
            <div class="drop-zone h-100 card rounded-4 shadow-sm p-3 d-flex justify-content-center align-items-center p-5">
                <input type="file" class="drop-zone__input" id="upload-template" accept=".zip" />
                <div class="drop-zone__prompt text-center gap-3">
                    <i class="rtmicon rtmicon-upload display-4 mb-3"></i>
                    <span>Drop Template Kit ZIP here or click to upload</span>
                </div>
            </div>
        </div>
        <?php foreach ($datas['data_template'] as $data) : 
            $manifest = json_decode(file_get_contents($rtmTemplateDir . '/' . $data['hash_id'] . '/manifest.json'));
            $imgurl = $rtmTemplateUrl . '/' . $data['hash_id'] . '/' . $manifest->templates[0]->screenshot;
        ?>
            <div class="col">
                <div class="card rounded-4 shadow-sm p-3 h-100 gap-3">
                    <div class="position-relative">
                        <img src="<?php echo esc_url($imgurl) ?>" class="card-img-top rounded-2" alt="<?php echo esc_attr($manifest->title) ?>" loading="lazy">
                    </div>
                    <div class="card-body d-flex flex-column gap-3 h-100 justify-content-between p-0">
                        <h5 class="card-title"><?php echo esc_html($manifest->title) ?></h5>
                        <div class="d-flex flex-row gap-2">
                            <button class="btn btn-secondary rounded-2 view-template" data-template="<?php echo esc_attr($data['hash_id']) ?>"><i class="far fa-eye"></i>View Kit</button>
                            <button class="btn btn-danger rounded-2 delete-template" data-template="<?php echo esc_attr($data['hash_id']) ?>"><i class="far fa-trash-can"></i>Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>