<?php
$page = $_GET['page'];
?>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');

    .divider {
        position: relative;
        background-color: #00cea6;
        height: 35px;
        width: 2px;
    }

    body {
        background-color: #f0f0f1;
    }

    .spacer {
        width: 100%;
        height: 8rem;
    }

    p.text {
        color: #aeaeae;
    }

    ul.list {
        margin: 0;
        padding: 0;
    }
</style>

<div id="header-dashboard" class="rtm-container header-sticky px-5 me-3">
    <div class="d-flex flex-row gap-4 align-items-center rtm-text-font my-3 m-0 glass-effect px-4 py-3 rounded-3">
        <div class="d-flex flex-row align-items-center gap-3">
            <img src="<?php echo esc_attr(\RomeTheme::plugin_url() . 'view/images/RTMKitNew.png') ?>" alt="rtm-logo" width="120">
            <span class="rtm-version">v.<?php echo esc_html(\RomeTheme::rt_version()) ?></span>
        </div>
        <ul class="nav nav-underline">
            <li class="nav-item m-0">
                <a href="<?php echo esc_url(admin_url('admin.php?page=romethemekit')) ?>" class="nav-link <?php echo ($page == 'romethemekit') ? esc_attr('active') : '' ?>">Welcome</a>
            </li>
            <li class="nav-item m-0">
                <a href="<?php echo esc_url(admin_url('admin.php?page=rkit-widgets')) ?>" class="nav-link <?php echo ($page == 'rkit-widgets') ? esc_attr('active') : '' ?>">Widgets</a>
            </li>
            <li class="nav-item m-0">
                <a href="<?php echo esc_url(admin_url('admin.php?page=themebuilder')) ?>" class="nav-link <?php echo ($page == 'themebuilder') ? esc_attr('active') : '' ?>">Theme Builder</a>
            </li>
            <li class="nav-item m-0">
                <a href="<?php echo esc_url(admin_url('admin.php?page=rtmkit-templates')) ?>" class="nav-link <?php echo ($page == 'rtmkit-templates') ? esc_attr('active') : '' ?>">Template Kits</a>
            </li>
            <li class="nav-item m-0">
                <a href="<?php echo esc_url(admin_url('admin.php?page=rkit-system-status')) ?>" class="nav-link <?php echo ($page == 'rkit-system-status') ? esc_attr('active') : '' ?>">System Status</a>
            </li>
            <li class="nav-item m-0">
                <a href="<?php echo esc_url(admin_url('admin.php?page=rtm-settings')) ?>" class="nav-link <?php echo ($page == 'rtm-settings') ? esc_attr('active') : '' ?>">Settings</a>
            </li>
        </ul>
        <div class="d-flex justify-content-end gap-3 flex-grow-1">
            <button class="btn btn-neumorphism align-self-center rounded-3 p-2 position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="21" viewBox="0 0 20 21" fill="none">
                    <g clip-path="url(#clip0_117_1042)">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M16.5416 7.54859C16.6241 7.96876 16.6665 8.39884 16.6665 8.83329V16.0558L16.9998 16.5C17.0463 16.5619 17.0745 16.6355 17.0815 16.7125C17.0884 16.7896 17.0738 16.8671 17.0392 16.9363C17.0046 17.0055 16.9514 17.0637 16.8856 17.1044C16.8197 17.1451 16.7439 17.1666 16.6665 17.1666H3.33317C3.25579 17.1666 3.17994 17.1451 3.11412 17.1044C3.04829 17.0637 2.9951 17.0055 2.96049 16.9363C2.92589 16.8671 2.91124 16.7896 2.91819 16.7125C2.92514 16.6355 2.95341 16.5619 2.99984 16.5L3.33317 16.0558V8.83329C3.33317 7.06518 4.03555 5.36949 5.28579 4.11925C6.53604 2.86901 8.23173 2.16663 9.99984 2.16663C10.6148 2.16663 11.221 2.2516 11.8028 2.41504C11.4033 2.83295 11.1063 3.34966 10.9522 3.92481C10.6406 3.86435 10.3218 3.83329 9.99984 3.83329C8.67376 3.83329 7.40199 4.36008 6.4643 5.29776C5.52662 6.23544 4.99984 7.50721 4.99984 8.83329V15.5H14.9998V8.83329C14.9998 8.64604 14.9893 8.45988 14.9687 8.27558C15.5578 8.16752 16.0953 7.91196 16.5416 7.54859ZM11.473 19.4731C11.8637 19.0824 12.0832 18.5525 12.0832 18H7.9165C7.9165 18.5525 8.136 19.0824 8.5267 19.4731C8.9174 19.8638 9.4473 20.0833 9.99984 20.0833C10.5524 20.0833 11.0823 19.8638 11.473 19.4731Z" fill="#0A0A0A" />
                        <circle cx="14.0833" cy="4.58333" r="2.08333" fill="#FC3838" />
                    </g>
                    <defs>
                        <clipPath id="clip0_117_1042">
                            <rect width="20" height="20" fill="white" transform="translate(0 0.5)" />
                        </clipPath>
                    </defs>
                </svg>
            </button>
            <?php if (!class_exists('RomethemePro')) : ?>
                <a href="https://rometheme.net/plugins/rtmkit/pricing/" target="_blank" class="btn btn-gradient-accent rounded-pill text-nowrap d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="17" viewBox="0 0 18 17" fill="currentColor">
                        <path d="M1.91415 6.22084L2.98665 15.3333H15.0225L16.0942 6.22084L12.7525 8.44834L9.00415 3.20084L5.25582 8.44834L1.91415 6.22084ZM1.33832 3.83334L4.83748 6.16667L8.32582 1.28251C8.4029 1.17448 8.50468 1.08643 8.62266 1.02568C8.74065 0.964923 8.87144 0.933228 9.00415 0.933228C9.13686 0.933228 9.26765 0.964923 9.38564 1.02568C9.50363 1.08643 9.6054 1.17448 9.68248 1.28251L13.1708 6.16584L16.6708 3.83334C16.8032 3.74528 16.958 3.69694 17.117 3.69403C17.276 3.69111 17.4325 3.73374 17.5681 3.81689C17.7036 3.90004 17.8125 4.02023 17.882 4.16327C17.9514 4.30632 17.9785 4.46624 17.96 4.62417L16.5917 16.2633C16.568 16.4662 16.4706 16.6533 16.318 16.7891C16.1655 16.9249 15.9684 17 15.7642 17H2.24498C2.04075 17 1.84364 16.9249 1.69109 16.7891C1.53854 16.6533 1.44118 16.4662 1.41748 16.2633L0.0483181 4.62501C0.0295031 4.46688 0.056389 4.30667 0.125793 4.16335C0.195198 4.02002 0.304214 3.89959 0.439937 3.8163C0.57566 3.733 0.732407 3.69034 0.891622 3.69336C1.05084 3.69638 1.20585 3.74496 1.33832 3.83334ZM9.00498 12C8.78611 12.0001 8.56938 11.957 8.36715 11.8733C8.16492 11.7896 7.98115 11.6669 7.82635 11.5121C7.67155 11.3574 7.54874 11.1737 7.46493 10.9715C7.38112 10.7693 7.33796 10.5526 7.3379 10.3338C7.33785 10.1149 7.3809 9.89815 7.46461 9.69592C7.54832 9.49369 7.67104 9.30993 7.82576 9.15512C7.98049 9.00032 8.16419 8.87751 8.36638 8.7937C8.56857 8.70989 8.78528 8.66673 9.00415 8.66667C9.44618 8.66667 9.8701 8.84227 10.1827 9.15483C10.4952 9.46739 10.6708 9.89131 10.6708 10.3333C10.6708 10.7754 10.4952 11.1993 10.1827 11.5119C9.8701 11.8244 9.44701 12 9.00498 12Z" fill="currentColor" />
                    </svg>Get Pro Version</a>

            <?php endif; ?>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end text-white rtm-text-font rtm-bg-gradient-1" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel" style="z-index: 999999;">
    <div class="offcanvas-header border-bottom border-secondary">
        <h5 class="offcanvas-title" id="offcanvasRightLabel">What's New</h5>
        <button type="button" class="btn btn-dark" data-bs-dismiss="offcanvas" aria-label="Close">
            <i class="fas fa-xmark"></i>
        </button>
    </div>
    <div class="offcanvas-body">
        <div class="d-flex flex-column gap-3">
            <div class="d-flex flex-column text-white">
                Free Version
                <div class="d-flex flex-row align-items-center gap-2">
                    <span class="rtm-version px-4 py-2 fs-6">V.<?php echo esc_html(\RomeTheme::rt_version()) ?></span>
                    <h6 class="m-0">May 9, 2025</h6>
                </div>
                <ul class="list text-white ms-4 py-3" style="font-size:14px">
                    <li><strong>Added</strong>: New extension "RTM Duplicator"</li>
                    <li><strong>Added</strong>: New extension "RTM Tooltips"</li>
                    <li><strong>Added</strong>: New extension "RTM Wrapper Link"</li>

                    <li><strong>Fixed</strong>: Issue on alignment mobile devices in the "Header Info" widget</li>
                    <li><strong>Fixed</strong>: Issue on responsive and fullwidth in the "Woo Product list" widget (style 2)</li>
                    <li><strong>Fixed</strong>: Issue on animation not working in the "Image Accordion" widget</li>
                    <li><strong>Fixed</strong>: Issue on image not show with 4 columns in the "Image Gallery" widget</li>
                    <li><strong>Fixed</strong>: Issue on responsive view in the "Image Comparison" widget</li>
                    <li><strong>Fixed</strong>: Issue on Box Shadow radius in the "Card Carousel" widget</li>
                    <li><strong>Fixed</strong>: Issue on Featured Image in the "Post Grid" widget</li>
                    <li><strong>Fixed</strong>: Issue on icon missing close in the "Header Offcanvas" widget</li>
                    <li><strong>Fixed</strong>: Issue with vertical layout option not displaying view content saved template correctly in the "Advanced Tabs" widget</li>

                    <li><strong>Improved</strong>: Added border style and color options to the button in the "Search" widget</li>
                    <li><strong>Improved</strong>: Responsive color control options for the tab menu style in the "Nav Menu" widget</li>
                    <li><strong>Improved</strong>: Added number prefix in the "Counter" widget</li>
                    <li><strong>Improved</strong>: Added border radius for time style and position in the "Countdown" widget</li>
                    <li><strong>Improved</strong>: Added width and height control for image in the "Image Marquee" widget</li>
                    <li><strong>Improved</strong>: Added alignment, typography options for category in the "Post List" widget</li>
                    <li><strong>Improved</strong>: Added border color options in the "Pie Chart" widget</li>
                    <li><strong>Improved</strong>: Added border radius options in the "Header Info" widget</li>
                </ul>
            </div>
            <div class="d-flex flex-column text-white">
                Pro Version
                <div class="d-flex flex-row align-items-center gap-2">
                    <span class="rtm-version px-4 py-2 fs-6">V.1.0.2</span>
                    <h6 class="m-0">March 25, 2025</h6>
                </div>
                <ul class="list text-white ms-4 py-3" style="font-size:14px">
                    <li><strong>New Widget</strong>: Textual Showcase</li>
                    <li><strong>New Widget</strong>: Image Marquee</li>
                    <li><strong>New Widget</strong>: Advanced Toggle</li>
                    <li><strong>New Widget</strong>: Interactive Link</li>
                    <li><strong>Improved</strong>: Pricing List</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="offcanvas-footer">
        <div class="p-4">
            <a href="https://rometheme.net/plugins/rtmkit/releases/" target="__blank" class="btn btn-gradient-accent rounded-pill">Full Changelog<i class="rtmicon rtmicon-arrow-up-right ms-2"></i></a>
        </div>
    </div>
</div>