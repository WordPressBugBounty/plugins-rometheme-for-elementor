<?php
if (!defined('ABSPATH'))
    exit;
?>
<div class="row row-cols-lg-3 row-cols-md-2 row-cols-1 g-3">
    <?php foreach ($datas['matches'] as $data): ?>
        <div class="col">
            <div class="card rounded-4 shadow-sm p-3 h-100 gap-3">
                <div class="position-relative">
                    <img src="<?php echo esc_url($data['previews']['landscape_preview']['landscape_url']) ?>"
                        class="card-img-top rounded-2" alt="<?php echo esc_attr($data['name']) ?>" loading="lazy">
                </div>
                <div class="card-body d-flex flex-column gap-3 h-100 justify-content-between p-0">
                    <h5 class="card-title"><?php echo esc_html($data['name']) ?></h5>
                    <div class="d-flex flex-row justify-content-between align-items-center">
                        <div class="d-flex flex-row gap-2">

                            <a href="<?php echo esc_url($data['url']) ?>" target="_blank"
                                class="btn btn-secondary rounded-2 text-white btn-install-click"
                                data-template-id="<?php echo esc_attr($data['id']) ?>"
                                data-template-name="<?php echo esc_attr($data['name']) ?>">
                                <i class="fas fa-plus"></i>
                                Install
                            </a>
                            <a href="<?php echo esc_url($data['previews']['live_site']['url']) ?>" target="_blank"
                                class="btn btn-link rounded-2 btn-preview-click"
                                data-template-id="<?php echo esc_attr($data['id']) ?>"
                                data-template-name="<?php echo esc_attr($data['name']) ?>">
                                <i class="fas fa-eye"></i>
                                Preview
                            </a>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <i class="fas fa-download"></i>
                            <span class="fs-6"><?php echo esc_html($data['number_of_sales']) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php
$total_pages = ceil(intval($datas['total_hits']) / 24);
$current_page = max(1, intval($_POST['paged'] ?? 1));
$window = 2; // jumlah halaman kiri-kanan current
if (\RTMKit\Core\Plugin::instance()->pro_is_active()) :

?>

<div class="d-flex justify-content-center align-items-center w-100 mt-4">
    <nav aria-label="Page navigation">
        <ul class="pagination">

            <!-- Previous -->
            <li class="page-item <?php echo $current_page == 1 ? 'disabled' : '' ?>">
                <button class="page-link" data-paged="<?php echo esc_attr($current_page - 1) ?>">&laquo;</button>
            </li>

            <?php
            // Page 1
            if ($current_page > ($window + 2)) {
                echo '<li class="page-item"><button class="page-link" data-paged="1">1</button></li>';
                echo '<li class="page-item disabled"><button class="page-link">...</button></li>';
            }

            // Middle pages
            for ($i = max(1, $current_page - $window); $i <= min($total_pages, $current_page + $window); $i++) {
                $active = $i == $current_page ? 'active' : '';
                echo '<li class="page-item ' . esc_attr($active) . '">
                        <button class="page-link" data-paged="' . esc_attr($i) . '">'
                    . esc_html($i) .
                    '</button>
                    </li>';
            }

            // Last page
            if ($current_page < ($total_pages - $window - 1)) {
                echo '<li class="page-item disabled"><button class="page-link">...</button></li>';

                echo '<li class="page-item">
            <button class="page-link" data-paged="' . esc_attr($total_pages) . '">'
                    . esc_html($total_pages) .
                    '</button>
          </li>';
            }
            ?>

            <!-- Next -->
            <li class="page-item <?php echo $current_page == $total_pages ? 'disabled' : '' ?>">
                <button class="page-link" data-paged="<?php echo esc_attr($current_page + 1) ?>">&raquo;</button>
            </li>

        </ul>
    </nav>
</div>


<?php

endif;

if (!\RTMKit\Core\Plugin::instance()->pro_is_active()) {
    $img_placeholder = RTM_KIT_URL . 'assets/images/tkit-pro.webp';
    ?>
    <div class="d-flex justify-content-center align-items-center w-100 h-100 position-relative overflow-hidden"
        style="min-height: 80vh; background-image: linear-gradient(to top, rgba(0, 0, 0, 0.85) 0%, rgba(0, 0, 0, 0.35) 35%, rgba(0, 0, 0, 0) 70%), url('<?php echo esc_url($img_placeholder) ?>'); background-size: cover; background-position: center top; background-repeat: no-repeat;">
        <div class="card rounded-4 text-center d-flex flex-column justify-content-center align-items-center gap-3 p-4 h-100 position-relative"
            style="margin-top: -150px; max-width: 350px; background-image: url(http://localhost/rtmshowcase/wp-content/plugins/rometheme-for-elementor/assets/images/banner-bg.png); background-size: cover; background-position: center; background-repeat: no-repeat; z-index: 1;">
            <h4 class="accent-color fs-6">Get More Premium Template</h4>
            <span class="text-white px-2">
                Unlock the full power of your workflow. Access 70+ widgets, 25+ templates, 9 theme builder features, 1200+
                icons, extensions, and priority support. Only in RTMkit Pro.
            </span>
            <a href="https://rometheme.net/plugins/rtmkit/pricing/" target="_blank"
                class="btn btn-accent fw-bold align-items-center gap-2">
                <svg width="30" height="30" viewBox="0 0 24 29" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M3.49698 9.00236L4.78398 19.9374H19.227L20.513 9.00236L16.503 11.6754L12.005 5.37836L7.50698 11.6754L3.49698 9.00236ZM2.80598 6.13736L7.00498 8.93736L11.191 3.07636C11.2835 2.94673 11.4056 2.84107 11.5472 2.76816C11.6888 2.69526 11.8457 2.65723 12.005 2.65723C12.1642 2.65723 12.3212 2.69526 12.4628 2.76816C12.6044 2.84107 12.7265 2.94673 12.819 3.07636L17.005 8.93636L21.205 6.13736C21.3639 6.03169 21.5497 5.97368 21.7404 5.97019C21.9312 5.96669 22.119 6.01785 22.2817 6.11762C22.4443 6.2174 22.575 6.36163 22.6584 6.53328C22.7417 6.70493 22.7742 6.89684 22.752 7.08636L21.11 21.0534C21.0816 21.2968 20.9647 21.5213 20.7817 21.6843C20.5986 21.8472 20.3621 21.9373 20.117 21.9374H3.89398C3.6489 21.9373 3.41236 21.8472 3.22931 21.6843C3.04625 21.5213 2.92941 21.2968 2.90098 21.0534L1.25798 7.08736C1.2354 6.89761 1.26767 6.70536 1.35095 6.53337C1.43424 6.36138 1.56506 6.21686 1.72792 6.11691C1.89079 6.01696 2.07889 5.96576 2.26995 5.96939C2.461 5.97301 2.64702 6.0313 2.80598 6.13736ZM12.006 15.9374C11.7433 15.9374 11.4833 15.8858 11.2406 15.7853C10.9979 15.6849 10.7774 15.5376 10.5916 15.3519C10.4059 15.1663 10.2585 14.9458 10.1579 14.7032C10.0573 14.4606 10.0055 14.2005 10.0055 13.9379C10.0054 13.6752 10.0571 13.4151 10.1575 13.1725C10.258 12.9298 10.4052 12.7093 10.5909 12.5235C10.7766 12.3377 10.997 12.1904 11.2397 12.0898C11.4823 11.9892 11.7423 11.9374 12.005 11.9374C12.5354 11.9374 13.0441 12.1481 13.4192 12.5231C13.7943 12.8982 14.005 13.4069 14.005 13.9374C14.005 14.4678 13.7943 14.9765 13.4192 15.3516C13.0441 15.7266 12.5364 15.9374 12.006 15.9374Z"
                        fill="#121416"></path>
                </svg>
                Upgrade to Pro
            </a>
        </div>
    </div>
    <?php
}
