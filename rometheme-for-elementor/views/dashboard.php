<?php
$whats_new = [
    'themebuilder' => [
        'title' => 'Theme Builder',
        'description' => 'Customize product, archive, cart, checkout, post, thankyou, myaccount page.',
    ],
    'widgets' => [
        'title' => 'New Widgets',
        'description' => 'Customize product, archive, cart, checkout, post, thankyou, myaccount page.',
    ],
    'extension' => [
        'title' => 'New Extensions',
        'description' => 'Customize product, archive, cart, checkout, post, thankyou, myaccount page.',
    ]
];

$sysInfo = \RTMKit\Modules\SystemInfo::instance()->get_system_info();

$systems = [
    'elementor' => [
        'title' => 'Plugin Required',
        'desc' => 'Elementor',
        'value' => $sysInfo['elementor']
    ],
    'min_php' => [
        'title' => 'Minimum PHP',
        'desc' => $sysInfo['php']['PHP_version'],
        'value' => version_compare($sysInfo['php']['PHP_version'], '7.4.5') != -1
    ],
    'post_size' => [
        'title' => 'Max. Post Size',
        'desc' => $sysInfo['php']['post_max_size'],
        'value' => intval($php_info['post_max_size']) <= 40
    ],
    'wp_memory_limit' => [
        'title' => 'WP Memory Limit',
        'desc' => $sysInfo['wordpress']['memory_limit'],
        'value' => $sysInfo['wordpress']['memory_limit'] <= 256
    ]
];

$current_user = wp_get_current_user();
?>

<div class="px-4" style="margin-bottom: 5rem;">
    <div class="d-flex flex-column gap-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex flex-column">
                <h1>Hello , <strong class="fw-bold"><?php echo esc_html($current_user->display_name); ?></strong></h1>
                <h1>Welcome to RTMKit Dashboard</h1>
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
        <div class="divider"></div>
        <div class="d-flex flex-column gap-3 mt-2">
            <div class="row row-cols-2 g-3">
                <div class="col-sm-7 col-md-7 col-lg-7 col-xl-7 col-8 col-xxl-8">
                    <div class="card rounded-4  flex-column justify-content-center gap-3  h-100">
                        <span class="accent-color">Everything you need in one place</span>
                        <h1 class="m-0 fw-light fs-2">The Ultimate Addons for Elementor-WordPress Website</h1>
                        <div class="divider"></div>
                        <p class="m-0">We're excited to help you supercharge your website-building experience. Effortlessly design stunning websites with our comprehensive range of free and premium widgets and features.</p>
                        <div class="d-flex flex-row gap-3 align-items-center">
                            <a href="https://support.rometheme.net/docs/romethemekit/" target="_blank" class="btn btn-secondary px-4 py-3">
                                <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20.7918 9.29825L17.8975 6.404V3.05188C17.8975 2.0585 17.0894 1.25 16.0956 1.25H4.85876C3.86538 1.25 3.05688 2.05812 3.05688 3.05188V19.1566C3.05688 20.1024 3.79563 20.8888 4.73876 20.9469C4.77588 20.9491 4.81301 20.9503 4.85088 20.9503H6.26688V21.9496C6.26688 22.9423 7.07426 23.7496 8.06688 23.7496H19.141C20.1344 23.7496 20.9429 22.9415 20.9429 21.9478V9.662C20.9429 9.52663 20.8878 9.39388 20.7918 9.29825ZM6.26688 6.38713V20.2006H4.85088C4.82876 20.2006 4.80738 20.1999 4.78526 20.1988C4.23663 20.165 3.80688 19.7071 3.80688 19.157V3.05188C3.80688 2.47213 4.27863 2 4.85876 2H16.096C16.6758 2 17.1479 2.47175 17.1479 3.05188V5.654L15.9464 4.4525C15.8508 4.3565 15.718 4.30175 15.5826 4.30175H8.35226C7.20251 4.30175 6.26688 5.23738 6.26688 6.38713ZM9.73338 8.17288C9.52638 8.17288 9.35838 8.00488 9.35838 7.79788C9.35838 7.59088 9.52638 7.42288 9.73338 7.42288H12.3816C12.5886 7.42288 12.7566 7.59088 12.7566 7.79788C12.7566 8.00488 12.5886 8.17288 12.3816 8.17288H9.73338ZM11.4291 11.0484C11.4291 11.2554 11.2611 11.4234 11.0541 11.4234H9.73376C9.52676 11.4234 9.35876 11.2554 9.35876 11.0484C9.35876 10.8414 9.52676 10.6734 9.73376 10.6734H11.0541C11.2611 10.6734 11.4291 10.8414 11.4291 11.0484ZM13.9758 20.7421H9.73376C9.52676 20.7421 9.35876 20.5741 9.35876 20.3671C9.35876 20.1601 9.52676 19.9921 9.73376 19.9921H13.9758C14.1828 19.9921 14.3508 20.1601 14.3508 20.3671C14.3508 20.5741 14.1828 20.7421 13.9758 20.7421ZM17.6136 20.51C17.5949 20.555 17.5649 20.5963 17.5311 20.6338C17.5124 20.6488 17.4936 20.6638 17.4749 20.6788C17.4524 20.6938 17.4336 20.705 17.4111 20.7125C17.3886 20.7238 17.3624 20.7313 17.3399 20.735C17.3174 20.7388 17.2911 20.7425 17.2649 20.7425C17.1674 20.7425 17.0699 20.7013 17.0024 20.6338C16.9649 20.5963 16.9386 20.555 16.9199 20.51C16.9011 20.465 16.8899 20.4163 16.8899 20.3675C16.8899 20.3188 16.9011 20.27 16.9199 20.225C16.9386 20.1763 16.9649 20.135 17.0024 20.1013C17.0886 20.015 17.2161 19.9775 17.3399 20C17.3624 20.0038 17.3886 20.0113 17.4111 20.0225C17.4336 20.03 17.4524 20.0413 17.4749 20.0563C17.4936 20.0675 17.5124 20.0863 17.5311 20.1013C17.5649 20.135 17.5949 20.1763 17.6136 20.225C17.6324 20.27 17.6399 20.3188 17.6399 20.3675C17.6399 20.4163 17.6324 20.465 17.6136 20.51ZM17.2664 17.5029H9.73338C9.52638 17.5029 9.35838 17.3349 9.35838 17.1279C9.35838 16.9209 9.52638 16.7529 9.73338 16.7529H17.2664C17.4734 16.7529 17.6414 16.9209 17.6414 17.1279C17.6414 17.3349 17.4734 17.5029 17.2664 17.5029ZM17.1513 9.22925C16.525 9.22925 16.0154 8.71963 16.0154 8.09338V5.58238L17.2574 6.82438L19.6623 9.22925H17.1513Z" fill="currentColor" />
                                </svg>
                                Read Documentation
                            </a>
                            <a href="https://www.youtube.com/@Rometheme_Studio/playlists" target="_blank" class="btn btn-secondary px-4 py-3">
                                <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M20.5898 10.199L7.37329 1.68876C6.53029 1.14651 5.45967 1.10751 4.58104 1.58713C3.70092 2.06676 3.15454 2.98738 3.15454 3.98976V21.0103C3.15454 22.0123 3.70092 22.9329 4.58104 23.4129C4.99054 23.6364 5.44167 23.7474 5.89167 23.7474C6.40804 23.7474 6.92329 23.6011 7.37329 23.3113L20.5898 14.801C21.3758 14.2948 21.8453 13.4345 21.8453 12.5C21.8453 11.5655 21.3758 10.7049 20.5898 10.199Z" fill="currentColor" />
                                </svg>
                                Watch a Guide
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-5 col-md-5 col-lg-5 col-xl-5 col-4 col-xxl-4">
                    <div class="card rounded-4  flex-column justify-content-center gap-3 p-2 h-100">
                        <a href="https://rometheme.net/plugins/rtmkit/" class="hover-shrink">
                            <img src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/homepage.png') ?>" alt="rtmkit" class="img-fluid">
                        </a>
                    </div>
                </div>
            </div>
            <div class="row row-cols-2 g-3">
                <div class="col-sm-5 col-md-5 col-lg-5 col-xl-5 col-4 col-xxl-4">
                    <div class="card rounded-4 flex-column justify-content-center gap-3 p-4 h-100">
                        <h4 class="fw-bold">What's New</h4>
                        <div class="overflow-y-auto h-100">
                            <ul class="list-group list-group-flush">
                                <?php foreach ($whats_new as $key => $item): ?>
                                    <li class="list-group-item">
                                        <div class="d-flex gap-2 ">
                                            <div class="list-icon">
                                                <i class="rtmicon rtmicon-check"></i>
                                            </div>
                                            <div class="d-flex flex-column gap-2">
                                                <h5 class="m-0 fw-bold"><?php echo esc_html($item['title']) ?></h5>
                                                <span>
                                                    <?php echo esc_html($item['description']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <a href="https://wordpress.org/plugins/rometheme-for-elementor/#developers" target="_blank" class="btn btn-secondary px-4 py-3">
                            <i class="rtmicon rtmicon-arrow-up-right"></i>
                            </i>
                            Changelog
                        </a>
                    </div>
                </div>
                <div class="col-sm-7 col-md-7 col-lg-7 col-xl-7 col-8 col-xxl-8">
                    <div class="d-flex flex-column gap-3">
                        <div class="card rounded-4 flex-column justify-content-center gap-3 pb-0 h-100">
                            <div class="row row-cols-2 align-items-center g-0">
                                <div class="col-5">
                                    <div class="d-flex flex-column gap-3 pb-4 pe-4">
                                        <h4 class="m-0 fw-bold">Master RTMKit Addons - Fast Easy!</h4>
                                        <p class="f-6">
                                            Get started with ease by watching our step-by-step beginner's tutorial on Elementor.
                                        </p>
                                        <a href="https://www.youtube.com/@Rometheme_Studio" target="_blank" class="btn btn-danger px-4 py-3">
                                            <svg width="16" height="16" viewBox="0 0 13 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M10.5633 4.9149L3.83466 0.582335C3.43116 0.32246 2.9386 0.304835 2.51766 0.534523C2.09635 0.764398 1.84473 1.18796 1.84473 1.66796V10.3329C1.84473 10.8129 2.09635 11.2365 2.51766 11.4663C2.71416 11.5734 2.92604 11.6268 3.13735 11.6268C3.37885 11.6268 3.61941 11.5571 3.83485 11.4185L10.5635 7.08596C10.9342 6.84727 11.1554 6.44152 11.1554 6.00052C11.1554 5.55952 10.934 5.15377 10.5635 4.91509L10.5633 4.9149ZM10.1572 6.45521L3.42854 10.7878C3.25979 10.8967 3.05335 10.9042 2.87673 10.8078C2.70029 10.7115 2.59473 10.5341 2.59473 10.3329V1.66796C2.59473 1.46677 2.7001 1.28921 2.87673 1.19302C2.95904 1.14802 3.04791 1.12571 3.13641 1.12571C3.23766 1.12571 3.33854 1.15496 3.42873 1.21309L10.1574 5.54565C10.315 5.64727 10.4054 5.81284 10.4054 6.00052C10.4054 6.18821 10.315 6.35377 10.1574 6.4554L10.1572 6.45521Z" fill="white" />
                                            </svg>
                                            Watch Now
                                        </a>
                                    </div>
                                </div>
                                <div class="col-7">
                                    <div class="yt-container overflow-hidden">
                                        <div class="thumbnail">
                                            <img src="https://i1.ytimg.com/vi/z27RYQVGaYE/maxresdefault.jpg" alt="" class="img-fluid">
                                            <div class="overlay">
                                                <a href="https://youtu.be/z27RYQVGaYE?feature=shared" target="_blank" class="fs-1">
                                                    <i class="fa-solid fa-circle-play"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row row-cols-2 g-3">
                            <div class="col">
                                <div class="card rounded-4 flex-column justify-content-center gap-3 p-4 h-100">
                                    <div class="d-flex justify-content-between">
                                        <h4 class="fw-bold">System Requirement</h4>
                                        <a href="admin.php?page=rtmkit&path=system-status" class="btn accent-color">
                                            See All
                                        </a>
                                    </div>
                                    <div class="overflow-y-auto h-100">
                                        <ul class="list-group list-group-flush">
                                            <?php foreach ($systems as $key => $item): ?>
                                                <li class="list-group-item m-0 px-1 py-2 lh-1">
                                                    <div class="row row-cols-3 align-items-center">
                                                        <div class="col-6">
                                                            <span class=""><?php echo esc_html($item['title']) ?></span>
                                                        </div>
                                                        <div class="col-1">
                                                            <div class="list-icon <?php echo (! $item['value']) ? esc_attr('list-icon-danger') : ''  ?>">
                                                                <i class="rtmicon <?php echo (!$item['value']) ? esc_attr('rtmicon-xmark fs-3') : esc_attr('rtmicon-check') ?>"></i>
                                                            </div>
                                                        </div>
                                                        <div class="col-5 ps-3">
                                                            <span class=""><?php echo esc_html($item['desc']) ?></span>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card rounded-4 flex-column justify-content-between gap-3  h-100">
                                    <h4 class="fw-bold">Request Feature</h4>
                                    <p>Please feel free to give any suggestions to include any features about RTMKit Addons for Elementor.</p>
                                    <a href="https://rometheme.net/support/" target="_blank" class="btn btn-secondary px-4 py-3">
                                        <i class="rtmicon rtmicon-plus"></i>
                                        Request Feature
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row row-cols-3 g-3">
                <div class="col">
                    <div class="card rounded-4 flex-column justify-content-between gap-3  h-100">
                        <h4 class="fw-bold">Recommended WP Themes</h4>
                        <a class="btn p-0" href="https://themeforest.net/item/luminex-multipurpose-it-business-tech-startup-wordpress-theme/61247551" target="_blank">
                            <img src="https://themes.rometheme.net/luminex/wp-content/uploads/sites/23/2026/01/luminex.png" alt="" class="img-fluid">
                        </a>
                        <a href="https://rometheme.net/wordpress-themes/" target="_blank" class="btn btn-secondary px-4 py-3">
                            <i class="rtmicon rtmicon-image-accordion fs-3"></i>
                            Browse WP Themes
                        </a>
                    </div>
                </div>
                <div class="col">
                    <div class="card rounded-4 flex-column justify-content-between gap-3  h-100">
                        <div>
                            <h4 class="fw-bold">Show your Love</h4>
                            <p>
                                Please take 2 minutes to review us and show some love.
                            </p>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <a href="https://wordpress.org/support/plugin/rometheme-for-elementor/reviews/#new-post" target="_blank" class="btn btn-secondary px-4 py-3">
                                <i class="fas fa-star"></i>
                                Rate RTMKit
                            </a>
                            <a href="https://wordpress.org/support/plugin/romethemeform/reviews/#new-post" target="_blank" class="btn btn-secondary px-4 py-3">
                                <i class="fas fa-star"></i>
                                Rate RTMForm
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="card rounded-4 flex-column justify-content-between gap-3 h-100">
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