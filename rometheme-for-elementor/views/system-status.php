<?php
$system_info = \RTMKit\Modules\SystemInfo::instance()->get_system_info();
$php_info = $system_info['php'];
$wp_info = $system_info['wordpress'];
$db_info = $system_info['database'];
$uploads_dir = wp_upload_dir();
$upload_path = $uploads_dir['basedir'];
$is_writable = is_writable($upload_path) ? 'Writeable' : 'Not Writeable';

$active_theme = wp_get_theme();
$theme_name = $active_theme->get('Name');
$theme_version = $active_theme->get('Version');
$theme_author = $active_theme->get('Author');

$active_plugins = get_option('active_plugins');
?>

<div class="px-4 mb-5 scroll-behavior-smooth scrollspy" data-scrollspy="#system-category" data-rootMargin="-30% 0px -70% 0px" tabindex="0">
    <div class="d-flex flex-column gap-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column">
                        <h1>Check System Status</h1>
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
        </div>
        <div class="divider"></div>
        <div class="position-sticky d-flex flex-column gap-3" style="top: 0rem;z-index: 1;">
            <ul id="system-category" class="nav nav-item-pill bg-secondary rounded-3 scrollspy border shadow card py-2 px-3 flex-row">
                <li class="nav-item m-0">
                    <a class="nav-link" href="#server-status">
                        Server Status
                    </a>
                </li>
                <li class="nav-item m-0">
                    <a class="nav-link" href="#wordpress-info">
                        Wordpress Info
                    </a>
                </li>
                <li class="nav-item m-0">
                    <a class="nav-link" href="#theme-info">
                        Theme Active
                    </a>
                </li>
                <li class="nav-item m-0">
                    <a class="nav-link" href="#plugin-info">
                        Plugin Active
                    </a>
                </li>
            </ul>
        </div>
        <div id="system-status-content" class="scrollspy-content gap-3" style="margin-bottom: 9rem;">
            <div class="row row-cols-2">
                <div class="col col-lg-8">
                    <div class="d-flex flex-column gap-3">
                        <div id="server-status" class="card">
                            <div class="border-bottom pb-3">
                                <h5 class="text-white m-0 fw-light">Server Status</h5>
                            </div>
                            <table class="rtm-table fw-light">
                                <tbody>
                                    <tr>
                                        <td scope="row">Operating System</td>
                                        <td class="description" colspan="2"><?php echo esc_html($php_info['PHP_OS']) ?></td>
                                    </tr>
                                    <tr>
                                        <td scope="row">Software</td>
                                        <td class="description" colspan="2"><?php echo esc_html($php_info['server_software']) ?></td>
                                    </tr>
                                    <tr>
                                        <td scope="row">Write Permissions</td>
                                        <td class="description" colspan="2"><?php echo esc_html($is_writable); ?></td>
                                    </tr>
                                    <tr>
                                        <td scope="row">MySQL Version</td>
                                        <td class="description">
                                            <div class="d-flex w-100 flex-row justify-content-between align-items-center">
                                                <?php echo esc_html($db_info['sql_name_server'] . ' v.' . $db_info['sql_version']) ?>
                                                <?php if ((strpos(strtolower($db_info['sql_name_server']), 'mysql') !== false && version_compare($db_info['sql_version'], '5.6.0') == -1) || (strpos(strtolower($db_info['sql_name_server']), 'mariadb') !== false && version_compare($db_info['sql_version'], '10.0.0') == -1)) : ?>
                                                    <div class="d-flex bg-danger danger-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="danger-color" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                                                            <path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM167 167c9.4-9.4 24.6-9.4 33.9 0l55 55 55-55c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-55 55 55 55c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-55-55-55 55c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l55-55-55-55c-9.4-9.4-9.4-24.6 0-33.9z" />
                                                        </svg>
                                                        <span class="danger-color">Incompatible</span>
                                                    </div>
                                                <?php else : ?>
                                                    <div class="d-flex bg-approved-color success-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg class="success-color" width="14" height="14" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M10.0869 0.711914C4.9091 0.711914 0.711914 4.9091 0.711914 10.0869C0.711914 15.2647 4.9091 19.4619 10.0869 19.4619C15.2647 19.4619 19.4619 15.2647 19.4619 10.0869C19.4619 4.9091 15.2647 0.711914 10.0869 0.711914ZM14.4935 8.03816L9.27473 13.0382C9.15379 13.1538 8.99816 13.2119 8.84223 13.2119C8.69098 13.2119 8.53973 13.1572 8.42004 13.0479L5.69066 10.5485C5.43598 10.3154 5.41848 9.92004 5.6516 9.66535C5.88473 9.41098 6.28004 9.39316 6.53473 9.62629L8.83254 11.73L13.6285 7.13504C13.8779 6.89629 14.2735 6.90504 14.5122 7.1541C14.751 7.40348 14.7425 7.7991 14.4932 8.03785L14.4935 8.03816Z" fill="currentColor" />
                                                        </svg>
                                                        <span class="success-color">Compatible</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td scope="row">PHP Version</td>
                                        <td class="description">
                                            <div class="d-flex w-100 flex-row justify-content-between align-items-center">
                                                <?php echo esc_html($php_info['PHP_version']) ?>
                                                <?php if (version_compare($php_info['PHP_version'], '7.3.0') == -1) : ?>
                                                    <div class="d-flex bg-danger danger-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="danger-color" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                                                            <path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM167 167c9.4-9.4 24.6-9.4 33.9 0l55 55 55-55c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-55 55 55 55c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-55-55-55 55c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l55-55-55-55c-9.4-9.4-9.4-24.6 0-33.9z" />
                                                        </svg>
                                                        <span class="danger-color">Incompatible</span>
                                                    </div>
                                                <?php else : ?>
                                                    <div class="d-flex bg-approved-color success-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg class="success-color" width="14" height="14" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M10.0869 0.711914C4.9091 0.711914 0.711914 4.9091 0.711914 10.0869C0.711914 15.2647 4.9091 19.4619 10.0869 19.4619C15.2647 19.4619 19.4619 15.2647 19.4619 10.0869C19.4619 4.9091 15.2647 0.711914 10.0869 0.711914ZM14.4935 8.03816L9.27473 13.0382C9.15379 13.1538 8.99816 13.2119 8.84223 13.2119C8.69098 13.2119 8.53973 13.1572 8.42004 13.0479L5.69066 10.5485C5.43598 10.3154 5.41848 9.92004 5.6516 9.66535C5.88473 9.41098 6.28004 9.39316 6.53473 9.62629L8.83254 11.73L13.6285 7.13504C13.8779 6.89629 14.2735 6.90504 14.5122 7.1541C14.751 7.40348 14.7425 7.7991 14.4932 8.03785L14.4935 8.03816Z" fill="currentColor" />
                                                        </svg>
                                                        <span id="active-widgets" class="success-color">Compatible</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td scope="row">PHP Memory Limit</td>
                                        <td class="description">
                                            <div class="d-flex w-100 flex-row justify-content-between align-items-center">
                                                <?php echo esc_html($php_info['PHP_memory_limit']) ?>
                                                <?php if (intval($php_info['PHP_memory_limit']) < 256) : ?>
                                                    <div class="d-flex bg-danger danger-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="danger-color" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                                                            <path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM167 167c9.4-9.4 24.6-9.4 33.9 0l55 55 55-55c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-55 55 55 55c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-55-55-55 55c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l55-55-55-55c-9.4-9.4-9.4-24.6 0-33.9z" />
                                                        </svg>
                                                        <span class="danger-color">Incompatible</span>
                                                    </div> <?php else : ?>
                                                    <div class="d-flex bg-approved-color success-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg class="success-color" width="14" height="14" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M10.0869 0.711914C4.9091 0.711914 0.711914 4.9091 0.711914 10.0869C0.711914 15.2647 4.9091 19.4619 10.0869 19.4619C15.2647 19.4619 19.4619 15.2647 19.4619 10.0869C19.4619 4.9091 15.2647 0.711914 10.0869 0.711914ZM14.4935 8.03816L9.27473 13.0382C9.15379 13.1538 8.99816 13.2119 8.84223 13.2119C8.69098 13.2119 8.53973 13.1572 8.42004 13.0479L5.69066 10.5485C5.43598 10.3154 5.41848 9.92004 5.6516 9.66535C5.88473 9.41098 6.28004 9.39316 6.53473 9.62629L8.83254 11.73L13.6285 7.13504C13.8779 6.89629 14.2735 6.90504 14.5122 7.1541C14.751 7.40348 14.7425 7.7991 14.4932 8.03785L14.4935 8.03816Z" fill="currentColor" />
                                                        </svg>
                                                        <span class="success-color">Compatible</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td scope="row">PHP Max Input Vars</td>
                                        <td class="description">
                                            <div class="d-flex w-100 flex-row justify-content-between align-items-center">
                                                <?php echo esc_html($php_info['max_input_vars']) ?>
                                                <?php if (intval($php_info['max_input_vars']) < 1000) : ?>
                                                    <div class="d-flex bg-danger danger-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="danger-color" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                                                            <path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM167 167c9.4-9.4 24.6-9.4 33.9 0l55 55 55-55c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-55 55 55 55c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-55-55-55 55c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l55-55-55-55c-9.4-9.4-9.4-24.6 0-33.9z" />
                                                        </svg>
                                                        <span class="danger-color">Incompatible</span>
                                                    </div>
                                                <?php else : ?>
                                                    <div class="d-flex bg-approved-color success-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg class="success-color" width="14" height="14" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M10.0869 0.711914C4.9091 0.711914 0.711914 4.9091 0.711914 10.0869C0.711914 15.2647 4.9091 19.4619 10.0869 19.4619C15.2647 19.4619 19.4619 15.2647 19.4619 10.0869C19.4619 4.9091 15.2647 0.711914 10.0869 0.711914ZM14.4935 8.03816L9.27473 13.0382C9.15379 13.1538 8.99816 13.2119 8.84223 13.2119C8.69098 13.2119 8.53973 13.1572 8.42004 13.0479L5.69066 10.5485C5.43598 10.3154 5.41848 9.92004 5.6516 9.66535C5.88473 9.41098 6.28004 9.39316 6.53473 9.62629L8.83254 11.73L13.6285 7.13504C13.8779 6.89629 14.2735 6.90504 14.5122 7.1541C14.751 7.40348 14.7425 7.7991 14.4932 8.03785L14.4935 8.03816Z" fill="currentColor" />
                                                        </svg>
                                                        <span class="success-color">Compatible</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td scope="row">PHP Max Post Size</td>
                                        <td class="description">
                                            <div class="d-flex w-100 flex-row justify-content-between align-items-center">
                                                <?php echo esc_html($php_info['post_max_size']) ?>
                                                <?php if (intval($php_info['post_max_size']) < 40) : ?>
                                                    <div class="d-flex bg-danger danger-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="danger-color" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                                                            <path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM167 167c9.4-9.4 24.6-9.4 33.9 0l55 55 55-55c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-55 55 55 55c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-55-55-55 55c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l55-55-55-55c-9.4-9.4-9.4-24.6 0-33.9z" />
                                                        </svg>
                                                        <span class="danger-color">Incompatible</span>
                                                    </div>
                                                <?php else : ?>
                                                    <div class="d-flex bg-approved-color success-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg class="success-color" width="14" height="14" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M10.0869 0.711914C4.9091 0.711914 0.711914 4.9091 0.711914 10.0869C0.711914 15.2647 4.9091 19.4619 10.0869 19.4619C15.2647 19.4619 19.4619 15.2647 19.4619 10.0869C19.4619 4.9091 15.2647 0.711914 10.0869 0.711914ZM14.4935 8.03816L9.27473 13.0382C9.15379 13.1538 8.99816 13.2119 8.84223 13.2119C8.69098 13.2119 8.53973 13.1572 8.42004 13.0479L5.69066 10.5485C5.43598 10.3154 5.41848 9.92004 5.6516 9.66535C5.88473 9.41098 6.28004 9.39316 6.53473 9.62629L8.83254 11.73L13.6285 7.13504C13.8779 6.89629 14.2735 6.90504 14.5122 7.1541C14.751 7.40348 14.7425 7.7991 14.4932 8.03785L14.4935 8.03816Z" fill="currentColor" />
                                                        </svg>
                                                        <span class="success-color">Compatible</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td scope="row">GD Installed</td>
                                        <td class="description">
                                            <div class="d-flex w-100 flex-row justify-content-between align-items-center">
                                                <?php echo extension_loaded('gd') ? esc_html('Yes') : esc_html('No') ?>
                                                <?php if (!extension_loaded('gd')) : ?>
                                                    <div class="d-flex bg-danger danger-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="danger-color" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                                                            <path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM167 167c9.4-9.4 24.6-9.4 33.9 0l55 55 55-55c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-55 55 55 55c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-55-55-55 55c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l55-55-55-55c-9.4-9.4-9.4-24.6 0-33.9z" />
                                                        </svg>
                                                        <span class="danger-color">Incompatible</span>
                                                    </div>
                                                <?php else : ?>
                                                    <div class="d-flex bg-approved-color success-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg class="success-color" width="14" height="14" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M10.0869 0.711914C4.9091 0.711914 0.711914 4.9091 0.711914 10.0869C0.711914 15.2647 4.9091 19.4619 10.0869 19.4619C15.2647 19.4619 19.4619 15.2647 19.4619 10.0869C19.4619 4.9091 15.2647 0.711914 10.0869 0.711914ZM14.4935 8.03816L9.27473 13.0382C9.15379 13.1538 8.99816 13.2119 8.84223 13.2119C8.69098 13.2119 8.53973 13.1572 8.42004 13.0479L5.69066 10.5485C5.43598 10.3154 5.41848 9.92004 5.6516 9.66535C5.88473 9.41098 6.28004 9.39316 6.53473 9.62629L8.83254 11.73L13.6285 7.13504C13.8779 6.89629 14.2735 6.90504 14.5122 7.1541C14.751 7.40348 14.7425 7.7991 14.4932 8.03785L14.4935 8.03816Z" fill="currentColor" />
                                                        </svg>
                                                        <span class="success-color">Compatible</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td scope="row">ZIP Installed</td>
                                        <td class="description">
                                            <div class="d-flex w-100 flex-row justify-content-between align-items-center">
                                                <?php echo extension_loaded('zip') ? esc_html('Yes') : esc_html('No') ?>
                                                <?php if (!extension_loaded('zip')) : ?>
                                                    <div class="d-flex bg-danger danger-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="danger-color" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                                                            <path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM167 167c9.4-9.4 24.6-9.4 33.9 0l55 55 55-55c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-55 55 55 55c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-55-55-55 55c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l55-55-55-55c-9.4-9.4-9.4-24.6 0-33.9z" />
                                                        </svg>
                                                        <span class="danger-color">Incompatible</span>
                                                    </div>
                                                <?php else : ?>
                                                    <div class="d-flex bg-approved-color success-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg class="success-color" width="14" height="14" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M10.0869 0.711914C4.9091 0.711914 0.711914 4.9091 0.711914 10.0869C0.711914 15.2647 4.9091 19.4619 10.0869 19.4619C15.2647 19.4619 19.4619 15.2647 19.4619 10.0869C19.4619 4.9091 15.2647 0.711914 10.0869 0.711914ZM14.4935 8.03816L9.27473 13.0382C9.15379 13.1538 8.99816 13.2119 8.84223 13.2119C8.69098 13.2119 8.53973 13.1572 8.42004 13.0479L5.69066 10.5485C5.43598 10.3154 5.41848 9.92004 5.6516 9.66535C5.88473 9.41098 6.28004 9.39316 6.53473 9.62629L8.83254 11.73L13.6285 7.13504C13.8779 6.89629 14.2735 6.90504 14.5122 7.1541C14.751 7.40348 14.7425 7.7991 14.4932 8.03785L14.4935 8.03816Z" fill="currentColor" />
                                                        </svg>
                                                        <span class="success-color">Compatible</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="wordpress-info" class="card">
                            <div class="border-bottom pb-3">
                                <h5 class="text-white m-0 fw-light">Wordpres Info</h5>
                            </div>
                            <table class="rtm-table fw-light">
                                <tbody>
                                    <tr>
                                        <td scope="row">Site URL</td>
                                        <td class="description" colspan="2"><?php echo esc_html($wp_info['Site_url']) ?></td>
                                    </tr>
                                    <tr>
                                        <td scope="row">Language</td>
                                        <td class="description" colspan="2"><?php echo esc_html($wp_info['WordPress_language']) ?></td>
                                    </tr>
                                    <tr>
                                        <td scope="row">Time Zone</td>
                                        <td class="description" colspan="2"><?php echo esc_html($wp_info['Time_zone']) ?></td>
                                    </tr>
                                    <tr>
                                        <td scope="row">WP Multisite</td>
                                        <td class="description" colspan="2"><?php echo esc_html($wp_info['WP_multisite']) ?></td>
                                    </tr>
                                    <tr>
                                        <td scope="row">WordPress Version</td>
                                        <td class="description">
                                            <div class="d-flex w-100 flex-row justify-content-between align-items-center">
                                                <?php echo esc_html($wp_info['WordPress_version']) ?>
                                                <?php if (version_compare($wp_info['WordPress_version'], '6.0.0') == -1) : ?>
                                                    <div class="d-flex bg-danger danger-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="danger-color" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                                                            <path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM167 167c9.4-9.4 24.6-9.4 33.9 0l55 55 55-55c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-55 55 55 55c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-55-55-55 55c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l55-55-55-55c-9.4-9.4-9.4-24.6 0-33.9z" />
                                                        </svg>
                                                        <span class="danger-color">Incompatible</span>
                                                    </div>
                                                <?php else : ?>
                                                    <div class="d-flex bg-approved-color success-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg class="success-color" width="14" height="14" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M10.0869 0.711914C4.9091 0.711914 0.711914 4.9091 0.711914 10.0869C0.711914 15.2647 4.9091 19.4619 10.0869 19.4619C15.2647 19.4619 19.4619 15.2647 19.4619 10.0869C19.4619 4.9091 15.2647 0.711914 10.0869 0.711914ZM14.4935 8.03816L9.27473 13.0382C9.15379 13.1538 8.99816 13.2119 8.84223 13.2119C8.69098 13.2119 8.53973 13.1572 8.42004 13.0479L5.69066 10.5485C5.43598 10.3154 5.41848 9.92004 5.6516 9.66535C5.88473 9.41098 6.28004 9.39316 6.53473 9.62629L8.83254 11.73L13.6285 7.13504C13.8779 6.89629 14.2735 6.90504 14.5122 7.1541C14.751 7.40348 14.7425 7.7991 14.4932 8.03785L14.4935 8.03816Z" fill="currentColor" />
                                                        </svg>
                                                        <span class="success-color">Compatible</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td scope="row">Memory Limit</td>
                                        <td class="description">
                                            <div class="d-flex w-100 flex-row justify-content-between align-items-center">
                                                <?php echo esc_html(WP_MEMORY_LIMIT) ?>
                                                <?php if (intval(WP_MEMORY_LIMIT) < 256) : ?>
                                                    <div class="d-flex bg-danger danger-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="danger-color" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                                                            <path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM167 167c9.4-9.4 24.6-9.4 33.9 0l55 55 55-55c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-55 55 55 55c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-55-55-55 55c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l55-55-55-55c-9.4-9.4-9.4-24.6 0-33.9z" />
                                                        </svg>
                                                        <span class="danger-color">Incompatible</span>
                                                    </div> <?php else : ?>
                                                    <div class="d-flex bg-approved-color success-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg class="success-color" width="14" height="14" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M10.0869 0.711914C4.9091 0.711914 0.711914 4.9091 0.711914 10.0869C0.711914 15.2647 4.9091 19.4619 10.0869 19.4619C15.2647 19.4619 19.4619 15.2647 19.4619 10.0869C19.4619 4.9091 15.2647 0.711914 10.0869 0.711914ZM14.4935 8.03816L9.27473 13.0382C9.15379 13.1538 8.99816 13.2119 8.84223 13.2119C8.69098 13.2119 8.53973 13.1572 8.42004 13.0479L5.69066 10.5485C5.43598 10.3154 5.41848 9.92004 5.6516 9.66535C5.88473 9.41098 6.28004 9.39316 6.53473 9.62629L8.83254 11.73L13.6285 7.13504C13.8779 6.89629 14.2735 6.90504 14.5122 7.1541C14.751 7.40348 14.7425 7.7991 14.4932 8.03785L14.4935 8.03816Z" fill="currentColor" />
                                                        </svg>
                                                        <span class="success-color">Compatible</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td scope="row">Max Memory Limit</td>
                                        <td class="description">
                                            <div class="d-flex w-100 flex-row justify-content-between align-items-center">
                                                <?php echo esc_html($wp_info['memory_limit']) ?>
                                                <?php if (intval($wp_info['memory_limit']) < 256) : ?>
                                                    <div class="d-flex bg-danger danger-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="danger-color" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                                                            <path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM167 167c9.4-9.4 24.6-9.4 33.9 0l55 55 55-55c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-55 55 55 55c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-55-55-55 55c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l55-55-55-55c-9.4-9.4-9.4-24.6 0-33.9z" />
                                                        </svg>
                                                        <span class="danger-color">Incompatible</span>
                                                    </div>
                                                <?php else : ?>
                                                    <div class="d-flex bg-approved-color success-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg class="success-color" width="14" height="14" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M10.0869 0.711914C4.9091 0.711914 0.711914 4.9091 0.711914 10.0869C0.711914 15.2647 4.9091 19.4619 10.0869 19.4619C15.2647 19.4619 19.4619 15.2647 19.4619 10.0869C19.4619 4.9091 15.2647 0.711914 10.0869 0.711914ZM14.4935 8.03816L9.27473 13.0382C9.15379 13.1538 8.99816 13.2119 8.84223 13.2119C8.69098 13.2119 8.53973 13.1572 8.42004 13.0479L5.69066 10.5485C5.43598 10.3154 5.41848 9.92004 5.6516 9.66535C5.88473 9.41098 6.28004 9.39316 6.53473 9.62629L8.83254 11.73L13.6285 7.13504C13.8779 6.89629 14.2735 6.90504 14.5122 7.1541C14.751 7.40348 14.7425 7.7991 14.4932 8.03785L14.4935 8.03816Z" fill="currentColor" />
                                                        </svg>
                                                        <span class="success-color">Compatible</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td scope="row">Max Upload Size</td>
                                        <td>
                                            <div class="d-flex w-100 flex-row justify-content-between align-items-center">
                                                <?php echo esc_html(size_format($wp_info['Max_upload_size'])) ?>
                                                <?php if (($wp_info['Max_upload_size'] / (1024 * 1024)) < 40) : ?>
                                                    <div class="d-flex bg-danger danger-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" class="danger-color" viewBox="0 0 512 512"><!--!Font Awesome Free v7.1.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.-->
                                                            <path d="M256 512a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM167 167c9.4-9.4 24.6-9.4 33.9 0l55 55 55-55c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-55 55 55 55c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-55-55-55 55c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l55-55-55-55c-9.4-9.4-9.4-24.6 0-33.9z" />
                                                        </svg>
                                                        <span class="danger-color">Incompatible</span>
                                                    </div>
                                                <?php else : ?>
                                                    <div class="d-flex bg-approved-color success-color rounded-4 px-2 py-1 gap-2 align-items-center">
                                                        <svg class="success-color" width="14" height="14" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M10.0869 0.711914C4.9091 0.711914 0.711914 4.9091 0.711914 10.0869C0.711914 15.2647 4.9091 19.4619 10.0869 19.4619C15.2647 19.4619 19.4619 15.2647 19.4619 10.0869C19.4619 4.9091 15.2647 0.711914 10.0869 0.711914ZM14.4935 8.03816L9.27473 13.0382C9.15379 13.1538 8.99816 13.2119 8.84223 13.2119C8.69098 13.2119 8.53973 13.1572 8.42004 13.0479L5.69066 10.5485C5.43598 10.3154 5.41848 9.92004 5.6516 9.66535C5.88473 9.41098 6.28004 9.39316 6.53473 9.62629L8.83254 11.73L13.6285 7.13504C13.8779 6.89629 14.2735 6.90504 14.5122 7.1541C14.751 7.40348 14.7425 7.7991 14.4932 8.03785L14.4935 8.03816Z" fill="currentColor" />
                                                        </svg>
                                                        <span class="success-color">Compatible</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="theme-info" class="card">
                            <div class="border-bottom pb-3">
                                <h5 class="text-white m-0 fw-light">Theme Active</h5>
                            </div>
                            <table class="rtm-table fw-light">
                                <tbody>
                                    <tr>
                                        <td scope="row">Name</td>
                                        <td class="description"><?php echo esc_html($theme_name) ?></td>
                                    </tr>
                                    <tr>
                                        <td scope="row">Version</td>
                                        <td class="description"><?php echo esc_html($theme_version) ?></td>
                                    </tr>
                                    <tr>
                                        <td scope="row">Author</td>
                                        <td class="description"><?php echo esc_html($theme_author) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="plugin-info" class="card">
                            <div class="border-bottom pb-3">
                                <h5 class="text-white m-0 fw-light">Plugin Active</h5>
                            </div>
                            <table class="rtm-table fw-light">
                                <tbody>
                                    <?php foreach ($active_plugins as $plugin) :
                                        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin);
                                        $plugin_name = $plugin_data['Name'];
                                        $plugin_version = $plugin_data['Version'];
                                        $plugin_author = $plugin_data['Author'];
                                    ?>
                                        <tr>
                                            <td scope="row"><?php echo esc_html($plugin_name) ?> - <?php echo esc_html($plugin_version) ?></td>
                                            <td class="description"> By <?php echo wp_kses_post($plugin_author) ?></td>

                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col col-lg-4">
                    <div class="card rounded-4 flex-column justify-content-between gap-3 " style="position: sticky; top: 4rem;">
                        <div class="d-flex flex-column gap-3">
                            <h4 class="fw-bold">Community & Support</h4>
                            <a href="https://www.facebook.com/groups/rometheme" target="_blank" class="social-container d-flex flex-row gap-3 align-items-center">
                                <div class="social-icon">
                                    <i class="rtmicon rtmicon-facebook"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <h5 class="m-0">Facebook Community</h5>
                                    <span>/groups/rometheme</span>
                                </div>
                            </a>
                            <a href="mailto:cs.rometheme@gmail.com" class="social-container d-flex flex-row gap-3 align-items-center">
                                <div class="social-icon">
                                    <i class="rtmicon rtmicon-envelope"></i>
                                </div>
                                <div class="d-flex flex-column">
                                    <h5 class="m-0">Mail</h5>
                                    <span>cs.rometheme@gmail.com</span>
                                </div>
                            </a>
                        </div>

                        <div class="d-flex flex-column gap-3 mt-3">
                            <h4 class="fw-bold">We’re active on</h4>
                            <div class="d-flex justify-content-start gap-2">
                                <a href="https://www.instagram.com/rometheme_studio" target="_blank" class="social-container d-flex flex-row gap-3 align-items-center">
                                    <div class="social-icon">
                                        <i class="rtmicon rtmicon-instagram"></i>
                                    </div>
                                </a>
                                <a href="https://www.instagram.com/rometheme_studio" target="_blank" class="social-container d-flex flex-row gap-3 align-items-center">
                                    <div class="social-icon">
                                        <i class="rtmicon rtmicon-x-twitter"></i>
                                    </div>
                                </a>
                                <a href="https://www.youtube.com/channel/UCB1RCmPjzvFyWNN28rtwheQ" target="_blank" class="social-container d-flex flex-row gap-3 align-items-center">
                                    <div class="social-icon">
                                        <i class="rtmicon rtmicon-youtube"></i>
                                    </div>
                                </a>
                                <a href="https://dribbble.com/rometheme" target="_blank" class="social-container d-flex flex-row gap-3 align-items-center">
                                    <div class="social-icon">
                                        <i class="rtmicon rtmicon-dribbble"></i>
                                    </div>
                                </a>
                                <a href="https://www.behance.net/Rometheme" target="_blank" class="social-container d-flex flex-row gap-3 align-items-center">
                                    <div class="social-icon">
                                        <i class="rtmicon rtmicon-behance"></i>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>