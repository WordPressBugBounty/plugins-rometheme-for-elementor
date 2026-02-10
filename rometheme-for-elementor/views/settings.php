<?php
$args = array(
    'post_type' => 'elementor_library', // Post type
    'posts_per_page' => -1, // Get all posts
    'meta_query' => array(
        array(
            'key' => '_elementor_template_type',
            'value' => 'kit',
            'compare' => '='
        )
    )
);

$globalSettings = new WP_Query($args);
?>
<div class="px-4 mb-5 scroll-behavior-smooth scrollspy" data-scrollspy="#widget-category" data-rootMargin="-30% 0px -70% 0px" tabindex="0">
    <div class="d-flex flex-column gap-3">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column">
                        <h1>Global Kit Setup</h1>
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
        <div class="scrollspy-content gap-3" style="margin-bottom: 9rem;">
            <div class="card rounded-4  flex-column gap-3">
                <div class="pb-3 pt-1 border-bottom d-flex flex-column gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <h4 class="m-0">Global Kit Style</h4>
                    </div>
                    <span>
                        Adjust your website settings to achieve the best performance. Customizing will enhance your site's efficiency & provide an improved user experience.
                    </span>
                </div>

                <div class="d-flex gap-3">
                    <select name="global-kit-style" id="global-kit-style" class="form-control form-select w-50">
                        <?php while ($globalSettings->have_posts()) : $globalSettings->the_post(); ?>
                            <option value="<?php echo esc_attr(get_the_ID()) ?>" <?php echo (get_option('elementor_active_kit') == get_the_ID()) ? esc_attr('selected') : '' ?>><?php echo esc_html(the_title()) ?></option>
                        <?php endwhile;
                        wp_reset_postdata();
                        ?>
                    </select>
                    <button class="btn btn-secondary rounded-2 text-nowrap gap-2 save-global-site" id="save-global-site">
                        <svg class="icon-loading" width="20" height="20" viewBox="0 0 22 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <g clip-path="url(#clip0_1696_11964)">
                                <path d="M18.6321 1.20117V6.11315L13.4512 6.1131" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M3.68262 22.7988V17.8869H8.86359" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M20.4634 9.6886C20.6631 10.4482 20.7693 11.2445 20.7693 12.0649C20.7693 17.3308 16.3954 21.5997 10.9999 21.5997C8.08203 21.5997 5.46292 20.3511 3.67285 18.3716" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M1.51966 14.3769C1.33076 13.6367 1.23047 12.8622 1.23047 12.0649C1.23047 6.79905 5.60433 2.53021 10.9998 2.53021C14.0768 2.53021 16.8217 3.91869 18.6124 6.08869" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </g>
                            <defs>
                                <clipPath id="clip0_1696_11964">
                                    <rect width="22" height="24" fill="white" />
                                </clipPath>
                            </defs>
                        </svg>
                        <i class="fa-solid fa-check icon-btn"></i>
                        <span class="text-white">Save Changes</span></button>
                </div>
                <div class="d-flex gap-2 align-items-center">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="#F15C5C" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2.01709C6.477 2.01709 2 6.49409 2 12.0171C2 17.5401 6.477 22.0171 12 22.0171C17.523 22.0171 22 17.5401 22 12.0171C22 6.49409 17.523 2.01709 12 2.01709ZM17.646 17.6631C16.138 19.1711 14.133 20.0021 12 20.0021C9.867 20.0021 7.862 19.1711 6.354 17.6631C4.846 16.1551 4.015 14.1501 4.015 12.0171C4.015 9.88409 4.846 7.87909 6.354 6.37109C7.862 4.86309 9.867 4.03209 12 4.03209C14.133 4.03209 16.138 4.86309 17.646 6.37109C19.154 7.87909 19.985 9.88409 19.985 12.0171C19.985 14.1501 19.154 16.1551 17.646 17.6631Z" fill="#F15C5C" />
                        <path d="M12.4601 7.02295H11.5391C11.0671 7.02295 10.6931 7.41995 10.7201 7.89095L11.0491 13.5639C11.0741 13.9979 11.4331 14.3369 11.8681 14.3369H12.1301C12.5651 14.3369 12.9241 13.9979 12.9491 13.5639L13.2781 7.89095C13.3051 7.41995 12.9311 7.02295 12.4591 7.02295H12.4601Z" fill="#F15C5C" />
                        <path d="M11.9999 16.996C12.5506 16.996 12.9969 16.5496 12.9969 15.999C12.9969 15.4483 12.5506 15.002 11.9999 15.002C11.4493 15.002 11.0029 15.4483 11.0029 15.999C11.0029 16.5496 11.4493 16.996 11.9999 16.996Z" fill="#F15C5C" />
                    </svg>
                    <span class="lh-1">
                        <small >
                            When used, the "Global Site Settings" that some Template Kits contain can affect your entire website.<br>
                            The Elementor Hamburger Menu » Site Settings is where you can change the Site Settings. The global site settings that are applied to your website can be altered below.
                        </small>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>