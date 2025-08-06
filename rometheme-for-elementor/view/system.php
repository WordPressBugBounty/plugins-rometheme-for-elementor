<div class="spacer-2"></div>

<?php
$wp_info = [
    'WordPress_version' => get_bloginfo('version'),
    'WordPress_language' => get_bloginfo('language'),
    'WordPress_theme' => [
        'Name' => wp_get_theme()->Name,
        'Author' => wp_get_theme()->Author,
        'Version' => wp_get_theme()->Version,
    ],
    'Site_url' => get_site_url(), // Menambahkan URL situs
    'Max_upload_size' => wp_max_upload_size(), // Menambahkan ukuran maksimum unggahan
    'Permalink_structure' => get_option('permalink_structure'), // Menambahkan struktur permalink
    'Time_zone' => get_option('timezone_string'), // Menambahkan zona waktu
    'WP_multisite' => (is_multisite()) ? 'Yes' : 'No', // Menambahkan info apakah WordPress berjalan dalam mode multisite atau tidak
    'Active_plugins' => get_option('active_plugins'),
    // Informasi tambahan yang mungkin Anda perlukan
];

$php_info = [
    'PHP_version' => phpversion(),
    'PHP_OS' => PHP_OS,
    'PHP_memory_limit' => ini_get('memory_limit'),
    'PHP_max_execution_time' => ini_get('max_execution_time'),
    'server_software' => $_SERVER['SERVER_SOFTWARE'],
    'max_input_vars' => ini_get('max_input_vars'),
    'post_max_size' =>  ini_get('post_max_size')
];

global $wpdb;

$mysql_info_cached = wp_cache_get('mysql_info_cached');

if (false === $mysql_info_cached) {
    // Jika data tidak ada di cache, ambil dari database dan simpan ke cache
    $query = "SELECT version() as version, @@version_comment as comment";
    $mysql_info = $wpdb->get_results($query, ARRAY_A);

    // Simpan data ke cache
    wp_cache_set('mysql_info_cached', $mysql_info);

    // Gunakan data yang diambil dari database
    $mysql_info_cached = $mysql_info;
}

$mysql_version = $wpdb->db_version();

$mysql_comment_v = $mysql_info_cached[0]['comment'];

$uploads_dir = wp_upload_dir();
$upload_path = $uploads_dir['basedir'];
$is_writable = is_writable($upload_path) ? 'Writeable' : 'Not Writeable';

require_once(RomeTheme::plugin_dir() . 'view/header.php');


$active_theme = wp_get_theme();
$theme_name = $active_theme->get('Name');
$theme_version = $active_theme->get('Version');
$theme_author = $active_theme->get('Author');
$active_plugins = get_option('active_plugins');
?>

<div class="d-flex flex-column gap-3 me-3  mb-3 rtm-container rounded-2 rtm-bg-gradient-1" style="margin-top: -8rem;">
    <div class="px-5 rounded-3">
        <div class="spacer"></div>
        <div class="row row-cols-xl-2 row-cols-1 rtm-text-font px-4 py-5">
            <div class="col col-xl-8">
                <div class="d-flex flex-column gap-4 px-4">
                    <div>
                        <span class="accent-color">System Status Healthcheck</span>
                        <div class="d-flex flex-row gap-3 align-items-center ">
                            <h1 class="text-white text-nowrap m-0">
                                Hello , <span class="fw-bold"><?php echo ucwords(get_userdata(get_current_user_id())->user_login) ?></span>
                            </h1>
                            <div class="rtm-divider rounded-pill"></div>
                        </div>
                        <h1 class="text-white m-0">
                            Let's Check Your System Status Here
                        </h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="row row-cols-xl-2 row-cols-1 rtm-text-font px-4 mb-5">
            <div class="col col-xl-7">
                <div class="d-flex flex-column gap-3">
                    <div class="rounded rtm-border bg-gradient-1">
                        <div class="rtm-border-bottom p-3">
                            <h5 class="text-white m-0 fw-light">Server Status</h5>
                        </div>
                        <div class="p-3">
                            <table class="rtm-table table-system fw-light">
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
                                        <td class="description"><?php echo esc_html($mysql_comment_v . ' v.' . $mysql_version) ?></td>
                                        <td class="icon-status"><i class="d-block <?php
                                                                                    if (strpos(strtolower($mysql_comment_v), 'mysql') !== false) {
                                                                                        echo (version_compare($mysql_version, '5.6.0') != -1) ? esc_attr('valid-color far fa-circle-check') : esc_attr('invalid-color far fa-circle-xmark');
                                                                                    } else if (strpos(strtolower($mysql_comment_v), 'mariadb') !== false) {
                                                                                        echo (version_compare($mysql_version, '10.0.0') != -1) ? esc_attr('valid-color far fa-circle-check') : esc_attr('invalid-color far fa-circle-xmark');
                                                                                    }

                                                                                    ?>"></i></td>
                                    </tr>
                                    <tr>
                                        <td scope="row">PHP Version</td>
                                        <td class="description"><?php echo esc_html($php_info['PHP_version']) ?></td>
                                        <td class="icon-status"><i class="d-block <?php echo (version_compare($php_info['PHP_version'], '7.3.0') != -1) ? esc_attr('valid-color far fa-circle-check') : esc_attr('invalid-color far fa-circle-xmark') ?>"></i></td>
                                    </tr>
                                    <tr>
                                        <td scope="row">PHP Memory Limit</td>
                                        <td class="description"><?php echo esc_html($php_info['PHP_memory_limit']) ?></td>
                                        <td class="icon-status"><i class="d-block <?php echo (intval($php_info['PHP_memory_limit']) >= 256) ?  esc_attr('valid-color far fa-circle-check') : esc_attr('invalid-color far fa-circle-xmark')  ?>"></i></td>
                                    </tr>
                                    <tr>
                                        <td scope="row">PHP Max Input Vars</td>
                                        <td class="description"><?php echo esc_html($php_info['max_input_vars']) ?></td>
                                        <td class="icon-status"><i class="d-block <?php echo (intval($php_info['max_input_vars']) >= 1000) ?  esc_attr('valid-color far fa-circle-check') : esc_attr('invalid-color far fa-circle-xmark')  ?>"></i></td>
                                    </tr>
                                    <tr>
                                        <td scope="row">PHP Max Post Size</td>
                                        <td class="description"><?php echo esc_html($php_info['post_max_size']) ?></td>
                                        <td class="icon-status"><i class="d-block <?php echo (intval($php_info['post_max_size']) >= 40) ?  esc_attr('valid-color far fa-circle-check') : esc_attr('invalid-color far fa-circle-xmark')  ?>"></i></td>
                                    </tr>
                                    <tr>
                                        <td scope="row">GD Installed</td>
                                        <td class="description"><?php echo extension_loaded('gd') ? esc_html('Yes') : esc_html('No') ?></td>
                                        <td class="icon-status"><i class="d-block <?php echo (extension_loaded('gd')) ?  esc_attr('valid-color far fa-circle-check') : esc_attr('invalid-color far fa-circle-xmark')  ?>"></i></td>
                                    </tr>
                                    <tr>
                                        <td scope="row">ZIP Installed</td>
                                        <td class="description"><?php echo extension_loaded('zip') ? esc_html('Yes') : esc_html('No') ?></td>
                                        <td class="icon-status"><i class="d-block <?php echo (extension_loaded('zip')) ?  esc_attr('valid-color far fa-circle-check') : esc_attr('invalid-color far fa-circle-xmark')  ?>"></i></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="rounded rtm-border bg-gradient-1">
                        <div class="rtm-border-bottom p-3">
                            <h5 class="text-white m-0 fw-light">WordPress Status</h5>
                        </div>
                        <div class="p-3">
                            <table class="rtm-table table-system fw-light">
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
                                        <td class="description"><?php echo esc_html($wp_info['WordPress_version']) ?></td>
                                        <td class="icon-status"><i class="d-block <?php echo (version_compare($wp_info['WordPress_version'], '6.0.0') != -1) ? esc_attr('valid-color far fa-circle-check') : esc_attr('invalid-color far fa-circle-xmark') ?>"></i></td>
                                    </tr>
                                    <tr>
                                        <td scope="row">Memory Limit</td>
                                        <td><?php echo esc_html(WP_MEMORY_LIMIT) ?></td>
                                        <td class="icon-status"><i class="d-block <?php echo ((intval(WP_MEMORY_LIMIT)) >= 256) ?  esc_attr('valid-color far fa-circle-check') : esc_attr('invalid-color far fa-circle-xmark')  ?>"></i></td>
                                    </tr>
                                    <tr>
                                        <td scope="row">Max Memory Limit</td>
                                        <td class="description"><?php echo esc_html($php_info['PHP_memory_limit']) ?></td>
                                        <td class="icon-status"><i class="d-block <?php echo (intval($php_info['PHP_memory_limit']) >= 256) ?  esc_attr('valid-color far fa-circle-check') : esc_attr('invalid-color far fa-circle-xmark')  ?>"></i></td>
                                    </tr>
                                    <tr>
                                        <td scope="row">Max Upload Size</td>
                                        <td><?php echo esc_html(size_format($wp_info['Max_upload_size'])) ?></td>
                                        <td class="icon-status"><i class="d-block <?php echo (($wp_info['Max_upload_size'] / (1024 * 1024)) >= 40) ?  esc_attr('valid-color far fa-circle-check') : esc_attr('invalid-color far fa-circle-xmark')  ?>"></i></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="rounded rtm-border bg-gradient-1">
                        <div class="rtm-border-bottom p-3">
                            <h5 class="text-white m-0 fw-light">Theme</h5>
                        </div>
                        <div class="p-3">
                            <table class="rtm-table table-system fw-light">
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
                    </div>
                    <div class="rounded rtm-border bg-gradient-1">
                        <div class="rtm-border-bottom p-3">
                            <h5 class="text-white m-0 fw-light">Active Plugin</h5>
                        </div>
                        <div class="p-3">
                            <table class="rtm-table table-system fw-light">
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
            </div>
            <div class="col col-xl-5">
                <div class="d-flex flex-column gap-3">
                    <div class="p-5 d-flex flex-column-reverse gap-3 rounded-3 text-white rtm-text-font rtm-bg-gradient-1">
                        <div class="d-flex flex-column gap-3 text-align-center align-items-center">
                            <?php if (class_exists('RomethemePro\RproLicense')) : if (RomethemePro\RproLicense::get_subs_status() == 'active') : ?>
                                    <h4>Now you are using license <?php echo esc_html(\RomethemePro\RproLicense::get_product_name()) ?></h4>
                                    <div class="rtm-divider"></div>
                                    <div class="spacer-2"></div>
                            <?php endif;
                            endif; ?>
                            <h4>Upgrade Now !</h4>
                            <p class="text text-center">Unlock more features and a longer usage period and can be used on unlimited websites.</p>
                            <div>
                                <a href="https://rometheme.net/plugins/rtmkit/pricing/" target="_blank" class="btn btn-gradient-accent rounded-pill">Upgrade Now</a>
                            </div>
                        </div>
                        <img src="<?php echo esc_url(\RomeTheme::plugin_url() . 'view/images/box-rtmkit.png') ?>" alt="" class="img-fluid">
                    </div>
                    <div class="p-5 d-flex flex-column gap-3 rounded-3 text-white rtm-text-font rtm-bg-gradient-1 h-100">
                        <h4>Let’s Connected with Us !</h4>
                        <p class="m-0 p-0">Get information about updates, tips & tricks, New Offers, from our various social channels</p>
                        <div class="rtm-divider rounded-pill" style="width: 80%;"></div>
                        <div class="d-flex flex-column gap-2">
                            <h4>Social Media Channel</h4>
                            <ul class="rtm-social-container p-0 gap-2 m-0">
                                <li><a href="https://www.facebook.com/groups/rometheme/" target="_blank" class="social-item rounded-2"><i class="fa-brands fa-facebook-f"></i></a></li>
                                <li><a href="https://www.instagram.com/rtmkit/" target="_blank" class="social-item rounded-2"><i class="fa-brands fa-instagram"></i></a></li>
                                <li><a href="https://twitter.com/rometheme" target="_blank" class="social-item rounded-2"><i class="fa-brands fa-x-twitter"></i></a></li>
                                <li><a href="https://www.youtube.com/channel/UCB1RCmPjzvFyWNN28rtwheQ" target="_blank" class="social-item rounded-2"><i class="fa-brands fa-youtube"></i></a></li>
                                <li><a href="https://dribbble.com/rometheme" target="_blank" class="social-item rounded-2"><i class="fa-brands fa-dribbble"></i></a></li>
                                <li><a href="https://www.behance.net/Rometheme" target="_blank" class="social-item rounded-2"><i class="fa-brands fa-behance"></i></a></li>
                            </ul>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <h4>Knowledge Base</h4>
                            <a href="https://support.rometheme.net/docs/romethemekit/widgets" target="_blank" class="d-flex flex-row align-items-center gap-3 social-link">
                                <div class="social-item rounded-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="19" viewBox="0 0 28 19" fill="currentColor">
                                        <path d="M14.7 17.4005C15.8349 16.7622 16.9797 16.3022 18.1342 16.0206C19.2887 15.739 20.4773 15.5973 21.7 15.5954C22.54 15.5954 23.2951 15.6479 23.9652 15.7531C24.6353 15.8582 25.2266 15.9971 25.739 16.1699C25.9537 16.26 26.1511 16.2464 26.3312 16.129C26.5113 16.0117 26.6009 15.8268 26.6 15.5743V2.88084C26.6 2.71844 26.5552 2.57388 26.4656 2.44716C26.376 2.32138 26.2234 2.21343 26.0078 2.12331C25.2378 1.84828 24.5373 1.66054 23.9064 1.5601C23.2755 1.45966 22.54 1.4085 21.7 1.40663C20.4773 1.40663 19.2579 1.58779 18.0418 1.95013C16.8247 2.3134 15.7108 2.85784 14.7 3.58344V17.4005ZM14 19C13.7807 19 13.5791 18.9723 13.3952 18.9169C13.2113 18.8615 13.0359 18.7911 12.8688 18.7057C11.8515 18.1472 10.7921 17.7239 9.6908 17.4357C8.58947 17.1475 7.4592 17.0044 6.3 17.0062C5.572 17.0062 4.85613 17.0672 4.1524 17.1893C3.4496 17.3122 2.76547 17.5094 2.1 17.7806C1.59227 17.9843 1.11533 17.9229 0.6692 17.5962C0.223067 17.2695 0 16.8175 0 16.2403V2.63161C0 2.28336 0.0909999 1.95998 0.273 1.66148C0.455 1.36298 0.710267 1.15553 1.0388 1.03913C1.86667 0.679611 2.72347 0.416778 3.6092 0.25063C4.49493 0.084482 5.39187 0.000938689 6.3 0C7.672 0 9.00947 0.198533 10.3124 0.595599C11.6172 0.992664 12.8464 1.56996 14 2.32748C15.1545 1.56996 16.3837 0.992664 17.6876 0.595599C18.9915 0.198533 20.3289 0 21.7 0C22.6081 0 23.5051 0.0835433 24.3908 0.25063C25.2775 0.416778 26.1343 0.679611 26.9612 1.03913C27.2897 1.15646 27.545 1.36392 27.727 1.66148C27.909 1.95904 28 2.28242 28 2.63161V16.2403C28 16.8166 27.7592 17.2592 27.2776 17.568C26.7951 17.8759 26.2817 17.928 25.7376 17.7243C25.0908 17.4709 24.4291 17.2878 23.7524 17.1752C23.0767 17.0616 22.3925 17.0048 21.7 17.0048C20.5427 17.0048 19.4129 17.1484 18.3106 17.4357C17.2083 17.7229 16.1485 18.1458 15.1312 18.7043C14.9632 18.7916 14.7877 18.8625 14.6048 18.9169C14.4209 18.9723 14.2193 19 14 19ZM16.639 5.61242C16.639 5.50916 16.6745 5.40497 16.7454 5.29984C16.8163 5.1947 16.9003 5.11585 16.9974 5.06329C17.7095 4.7385 18.4623 4.48975 19.2556 4.31703C20.0489 4.14619 20.8637 4.06077 21.7 4.06077C22.148 4.06077 22.5727 4.08564 22.974 4.13539C23.3753 4.18514 23.7888 4.25695 24.2144 4.35082C24.3348 4.37898 24.4393 4.44235 24.528 4.54091C24.6176 4.63853 24.6624 4.75915 24.6624 4.90277C24.6624 5.13932 24.5924 5.3111 24.4524 5.41811C24.3124 5.52512 24.1248 5.54953 23.8896 5.49133C23.5443 5.42093 23.191 5.37259 22.8298 5.3463C22.4677 5.32002 22.0911 5.30688 21.7 5.30688C20.9496 5.30688 20.2155 5.37916 19.4978 5.52372C18.7791 5.66827 18.0978 5.87009 17.4538 6.12917C17.2139 6.2221 17.0179 6.21975 16.8658 6.12213C16.7137 6.02451 16.6381 5.8546 16.639 5.61242ZM16.639 13.2482C16.639 13.1449 16.6745 13.0365 16.7454 12.9229C16.8163 12.8075 16.9003 12.7239 16.9974 12.6723C17.6741 12.3475 18.4268 12.1035 19.2556 11.9401C20.0844 11.7777 20.8992 11.6965 21.7 11.6965C22.148 11.6965 22.5727 11.7214 22.974 11.7712C23.3753 11.8209 23.7888 11.8927 24.2144 11.9866C24.3348 12.0147 24.4393 12.0781 24.528 12.1767C24.6176 12.2743 24.6624 12.3949 24.6624 12.5385C24.6624 12.7751 24.5924 12.9469 24.4524 13.0539C24.3124 13.1609 24.1248 13.1853 23.8896 13.1271C23.5443 13.0567 23.191 13.0084 22.8298 12.9821C22.4677 12.9558 22.0911 12.9426 21.7 12.9426C20.9683 12.9426 20.2477 13.0177 19.5384 13.1679C18.8291 13.3191 18.1524 13.5335 17.5084 13.8114C17.2676 13.9222 17.0623 13.9208 16.8924 13.8072C16.7225 13.6936 16.6381 13.5073 16.639 13.2482ZM16.639 9.45776C16.639 9.3545 16.6745 9.25031 16.7454 9.14518C16.8163 9.04004 16.9003 8.96119 16.9974 8.90863C17.7095 8.5829 18.4623 8.33415 19.2556 8.16237C20.0489 7.99153 20.8637 7.90611 21.7 7.90611C22.148 7.90611 22.5727 7.93098 22.974 7.98073C23.3753 8.03048 23.7888 8.10229 24.2144 8.19616C24.3348 8.22432 24.4393 8.28721 24.528 8.38484C24.6176 8.48246 24.6624 8.60355 24.6624 8.74811C24.6624 8.98466 24.5924 9.15644 24.4524 9.26345C24.3124 9.37046 24.1248 9.3944 23.8896 9.33526C23.5443 9.26486 23.191 9.21699 22.8298 9.19164C22.4677 9.16536 22.0911 9.15222 21.7 9.15222C20.9496 9.15222 20.2155 9.2245 19.4978 9.36905C18.7791 9.51267 18.0978 9.71449 17.4538 9.97451C17.2139 10.0665 17.0179 10.0642 16.8658 9.96747C16.7146 9.86891 16.639 9.699 16.639 9.45776Z" fill="currentColor" />
                                    </svg>
                                </div>
                                <span>Check Documentation</span>
                            </a>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <h4>Premium Support</h4>
                            <a href="mailto:cs.rometheme@gmail.com" class="d-flex flex-row align-items-center gap-3 social-link">
                                <div class="social-item rounded-2">
                                    <i class="fa-solid fa-envelope"></i>
                                </div>
                                <span>cs.rometheme@gmail.com</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>