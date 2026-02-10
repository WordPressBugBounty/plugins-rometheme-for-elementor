<?php

$active_themebuilder = \RTMKit\Modules\Themebuilder\ThemebuilderStorage::instance()->get_active_themebuilder();
$active_themebuilder_key = ['all' => ['name' => 'All']];

// Pecah khusus header & footer
if (isset($active_themebuilder['header_footer'])) {
    $active_themebuilder_key['header'] = ['name' => 'Header'];
    $active_themebuilder_key['footer'] = ['name' => 'Footer'];
    unset($active_themebuilder['header_footer']);
}

// Sisanya copy dengan ambil hanya 'name'
foreach ($active_themebuilder as $key => $item) {
    $active_themebuilder_key[$key] = ['name' => $item['name']];
}

if (class_exists('RomeThemeForm')) {
    $active_themebuilder_key['form'] = ['name' => 'Form '];
}

$active_tabs = (isset($_POST['themebuilder'])) ? sanitize_text_field($_POST['themebuilder']) : 'all';
$isLicenseActive = class_exists('RTMKitPro\Modules\Licenses\LicenseStorage') && (\RTMKitPro\Modules\Licenses\LicenseStorage::instance()->isLicenseActive());
$has_woocommerce = is_plugin_active('woocommerce/woocommerce.php');

?>

<div class="px-4 mb-5 scroll-behavior-smooth">
    <div class="d-flex flex-column gap-3">
        <div class="d-flex justify-content-between align-items-start">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column">
                        <h1>Theme Builder</h1>
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
            <div class="mb-4">
                <button id="add-themebuilder" class="btn btn-accent fw-bold px-4 py-3 gap-2">
                    <i class="rtmicon rtmicon-plus fs-5"></i>
                    Add New Template
                </button>
            </div>
        </div>
        <div class="divider"></div>
        <div class="position-sticky d-flex flex-column gap-3" style="top: 0rem;z-index: 1;background: var(--primary-color);">
            <div class="d-flex bg-secondary sticky-top justify-content-between border rounded-4 py-2 px-2">
                <div class="themebuilder tabs">
                    <nav class="tab-nav gap-2">
                        <?php foreach ($active_themebuilder_key as $key => $item):
                            if ($active_themebuilder[$key]['type'] === 'pro' && ! $isLicenseActive) {
                                continue;
                            }
                        ?>
                             <?php if(!stripos($item['name'], 'shop')): ?>
                                <button class="nav-link rounded-3 <?php echo ($active_tabs == $key) ? 'active' : '' ?>" data-tabs="<?php echo esc_attr($key) ?>">
                                    <?php echo esc_html($item['name']) . (isset($item['type']) && $item['type'] === 'pro' ? ' <span class="badge bg-danger">Pro</span>' : ''); ?>
                                </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </nav>
                </div>
            </div>

            <div class="scrollspy-content gap-3" style="margin-bottom: 9rem;">
                <div id="themebuilder-tab-content" class="themebuilder tab-content">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade rtm-text-font" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow p-0">
            <form id="add-new-post" method="POST" class="themebuilder-forms">
                <input id="action" name="action" type="text" value="add_themebuilder" hidden>
                <div class="modal-header px-5">
                    <div class="d-flex flex-row align-items-center justify-content-center w-100">
                        <div class="border py-2 border-start-0 border-top-0 border-bottom-0 px-4">
                            <img src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/rtmkit.png'); ?>" width="125" alt="RTMKit Logo">
                        </div>
                        <div class="px-4">
                            <h4 class="m-0">Theme Builder</h4>
                        </div>
                    </div>
                </div>
                <div class="modal-body overflow-auto d-flex flex-column gap-3 px-4 py-4">
                    <ul class="nav nav-underline mb-3 p-2 border" id="pills-tab" role="tablist">
                        <li class="nav-item col m-0" role="presentation">
                            <button class="nav-link active w-100" id="general-tab" data-bs-toggle="pill" data-bs-target="#general" type="button" role="tab" aria-controls="general" aria-selected="true">General</button>
                        </li>
                        <li class="nav-item col m-0" role="presentation">
                            <button class="nav-link w-100" id="condition-tab" data-bs-toggle="pill" data-bs-target="#condition" type="button" role="tab" aria-controls="condition" aria-selected="false">Condition</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab" tabindex="0">
                            <div class="mb-3">
                                <label for="inputTitle" class="form-label">Theme Builder Title</label>
                                <input name="title" type="text" class="form-control" id="inputTitle">
                            </div>
                            <div class="mb-3">
                                <label for="inputType" class="form-label">Type</label>
                                <select name="type" class="form-select select-type" id="inputType">
                                    <option value="header"><?php esc_html_e('Header', 'rometheme-for-elementor') ?></option>
                                    <option value="footer"><?php esc_html_e('Footer', 'rometheme-for-elementor') ?></option>
                                    <option value="error_404"><?php esc_html_e('404 Page', 'rometheme-for-elementor') ?></option>
                                    <option value="single_post"><?php esc_html_e('Single Post', 'rometheme-for-elementor') ?></option>
                                    <option value="archive_post"><?php esc_html_e('Archive Post', 'rometheme-for-elementor') ?></option>
                                    <option value="search"><?php esc_html_e('Search Result', 'rometheme-for-elementor') ?></option>
                                    <?php if($has_woocommerce && $isLicenseActive): ?>
                                        <option value="archive_product"><?php esc_html_e('Archive Product', 'rometheme-for-elementor') ?></option>
                                        <option value="single_product"><?php esc_html_e('Single Product', 'rometheme-for-elementor') ?></option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="d-flex flex-column gap-3 option">
                                <div class="d-flex flex-row align-items-center justify-content-between gap-3">
                                    <label class="fw-semibold form-label">Status
                                        <p class="m-0 fst-italic text-secondary fw-normal"><small>Enabling or Disabling Templates</small></p>
                                    </label>
                                    <label class="switch">
                                        <input name="active" id="active" class="switch-input" type="checkbox" value="true" checked>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="condition" role="tabpanel" aria-labelledby="condition-tab" tabindex="0">
                            <div class="condition-container">
                                <div class="d-flex justify-content-between align-content-center gap-3">
                                    <div class="d-flex flex-column">
                                        <h6 class="text-white">Status</h6>
                                        <p class="m-0"><small>Set the conditions that determine where your template is used.</small></p>
                                    </div>
                                    <div>
                                        <button class="btn btn-secondary px-4 py-3 text-nowrap add-condition text-decoration-none">
                                            <i class="rtmicon rtmicon-plus fs-5"></i>
                                            Add Condition
                                        </button>
                                    </div>
                                </div>
                                <div class="conditions d-flex flex-column py-3">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer px-4 border-0 pb-4 pt-0">
                    <button id="add-submit-btn" class="col btn btn-accent py-3 rounded-3 fw-bold">Save
                        changes</button>
                    <button id="close-btn" type="button" class="col btn btn-link px-4 py-3 text-decoration-none fw-bold" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade rtm-text-font" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow p-0">
            <form id="add-new-post" method="POST" class="themebuilder-forms">
                <input id="action" name="action" type="text" value="edit_themebuilder" hidden>
                <input id="themebuilder_id" name="themebuilder_id" type="number" hidden>
                <div class="modal-header px-5">
                    <div class="d-flex flex-row align-items-center justify-content-center w-100">
                        <div class="border py-2 border-start-0 border-top-0 border-bottom-0 px-4">
                            <img src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/rtmkit.png'); ?>" width="125" alt="RTMKit Logo">
                        </div>
                        <div class="px-4">
                            <h4 class="m-0">Theme Builder</h4>
                        </div>
                    </div>
                </div>
                <div class="modal-body overflow-auto d-flex flex-column gap-3 px-4 py-4">
                    <ul class="nav nav-underline mb-3 p-2 border" id="pills-tab" role="tablist">
                        <li class="nav-item col m-0" role="presentation">
                            <button class="nav-link active w-100" id="edit-general-tab" data-bs-toggle="pill" data-bs-target="#edit-general" type="button" role="tab" aria-controls="general" aria-selected="true">General</button>
                        </li>
                        <li class="nav-item col m-0" role="presentation">
                            <button class="nav-link w-100" id="edit-condition-tab" data-bs-toggle="pill" data-bs-target="#edit-condition" type="button" role="tab" aria-controls="condition" aria-selected="false">Condition</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="edit-general" role="tabpanel" aria-labelledby="general-tab" tabindex="0">
                            <div class="mb-3">
                                <label for="inputTitle" class="form-label">Theme Builder Title</label>
                                <input name="title" type="text" class="form-control" id="inputTitle">
                            </div>
                            <div class="mb-3">
                                <label for="inputType" class="form-label">Type</label>
                                <select name="type" class="form-select select-type" id="inputType">
                                    <option value="header"><?php esc_html_e('Header', 'rometheme-for-elementor') ?></option>
                                    <option value="footer"><?php esc_html_e('Footer', 'rometheme-for-elementor') ?></option>
                                    <option value="error_404"><?php esc_html_e('404 Page', 'rometheme-for-elementor') ?></option>
                                    <option value="single_post"><?php esc_html_e('Single Post', 'rometheme-for-elementor') ?></option>
                                    <option value="archive_post"><?php esc_html_e('Archive Post', 'rometheme-for-elementor') ?></option>
                                    <option value="search"><?php esc_html_e('Search Result', 'rometheme-for-elementor') ?></option>
                                     <?php if($has_woocommerce && $isLicenseActive): ?>
                                        <option value="archive_product"><?php esc_html_e('Archive Product', 'rometheme-for-elementor') ?></option>
                                        <option value="single_product"><?php esc_html_e('Single Product', 'rometheme-for-elementor') ?></option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="d-flex flex-column gap-3 option">
                                <div class="d-flex flex-row align-items-center justify-content-between gap-3">
                                    <label class="fw-semibold form-label">Status
                                        <p class="m-0 fst-italic text-secondary fw-normal"><small>Enabling or Disabling Templates</small></p>
                                    </label>
                                    <label class="switch">
                                        <input name="active" id="active" class="switch-input" type="checkbox" value="true" checked>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="edit-condition" role="tabpanel" aria-labelledby="condition-tab" tabindex="0">
                            <div class="condition-container">
                                <div class="d-flex justify-content-between align-content-center gap-3">
                                    <div class="d-flex flex-column">
                                        <h6 class="text-white">Status</h6>
                                        <p class="m-0"><small>Set the conditions that determine where your template is used.</small></p>
                                    </div>
                                    <div>
                                        <button class="btn btn-secondary px-4 py-3 text-nowrap add-condition text-decoration-none">
                                            <i class="rtmicon rtmicon-plus fs-5"></i>
                                            Add Condition
                                        </button>
                                    </div>
                                </div>
                                <div class="conditions d-flex flex-column py-3">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer px-4 border-0 pb-4 pt-0">
                    <button id="add-submit-btn" class="col btn btn-accent py-3 rounded-3 fw-bold">Save
                        changes</button>
                    <button id="close-btn" type="button" class="col btn btn-link px-4 py-3 text-decoration-none fw-bold" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php if (class_exists('RomethemeForm')) : ?>
    <div class="modal fade" id="formModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" style="z-index:99999">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form class="w-100" id="rtform-add-form" method="post">
                <div class="modal-content">
                    <div class="modal-header px-5">
                        <div class="d-flex flex-row align-items-center justify-content-center w-100">
                            <div class="border py-2 border-start-0 border-top-0 border-bottom-0 px-4">
                                <img src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/rtmkit.png'); ?>" width="125" alt="RTMKit Logo">
                            </div>
                            <div class="px-4">
                                <h4 class="m-0">Theme Builder</h4>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="height: auto !important;">
                        <input id="action" name="action" type="text" value="rtformnewform" hidden>
                        <nav>
                            <ul class="nav nav-underline mb-3" id="nav-tab" role="tablist">
                                <li class="nav-item col">
                                    <button class="nav-link active w-100" id="nav-general-tab" data-bs-toggle="tab" data-bs-target="#nav-general" type="button" role="tab" aria-controls="nav-general" aria-selected="true">General</button>
                                </li>
                                <li class="nav-item col">
                                    <button class="nav-link w-100" id="nav-confirmation-tab" data-bs-toggle="tab" data-bs-target="#nav-confirmation" type="button" role="tab" aria-controls="nav-confirmation" aria-selected="false">Confirmation</button>
                                </li>
                                <li class="nav-item col">
                                    <button class="nav-link w-100" id="nav-notification-tab" data-bs-toggle="tab" data-bs-target="#nav-notification" type="button" role="tab" aria-controls="nav-notification" aria-selected="false">Notification</button>
                                </li>
                            </ul>
                        </nav>
                        <div class="tab-content p-3" id="nav-tabContent">
                            <div class="tab-pane fade show active" id="nav-general" role="tabpanel" aria-labelledby="nav-general-tab" tabindex="0">
                                <label for="form-name">Form Name</label>
                                <input type="text" name="form-name" id="form-name" class="form-control p-2" placeholder="Enter Form Name">
                                <h5 class="my-3">Settings</h5>
                                <hr>
                                <div class="mb-3">
                                    <label for="success-message" class="form-label">Success Message</label>
                                    <input type="text" class="form-control p-2" id="success-message" name="success-message" value="Thank you! Form submitted successfully.">
                                </div>
                                <div class="mb-3">
                                    <label for="entry-name" class="form-label">Entry Title</label>
                                    <input type="text" class="form-control p-2" id="entry-name" name="entry-name" value="Entry #">
                                    <p class="fw-light fst-italic text">To set a custom entry title, enclose the input name in {{ }}.</p>
                                </div>
                                <div class="d-flex flex-row justify-content-between align-items-center mb-3">
                                    <span>
                                        <p class="m-0">Require Login</p>
                                        <p class="fw-light fst-italic text">Without login, user can't submit the form.</p>
                                    </span>
                                    <label class="switch">
                                        <input name="require-login" id="switch" class="switch-input" type="checkbox" value="true">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="nav-confirmation" role="tabpanel" aria-labelledby="nav-confirmation-tab" tabindex="0">
                                <div class="d-flex flex-row justify-content-between align-items-center">
                                    <span>
                                        <h5 class="m-0">Confirmation mail to user</h5>
                                    </span>
                                    <label class="switch">
                                        <input name="confirmation" id="switch_confirmation" class="switch-input" type="checkbox" value="true">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <p class="fw-light fst-italic text">Want to send a submission copy to user by email? <strong>Active this one. <br />The form must have at least one Email widget and it should be required.</strong></p>
                                <div id="confirmation_form" class="mt-3">
                                    <div class="mb-3">
                                        <label for="email_subject" class="form-label">Email Subject</label>
                                        <input type="text" class="form-control p-2" name="email_subject" id="email_subject" placeholder="Enter Email Subject Here">
                                    </div>
                                    <div class="mb-3">
                                        <label for="email_subject" class="form-label">Email From</label>
                                        <input type="email" class="form-control p-2" name="email_from" id="email_from" placeholder="mail@example.com">
                                    </div>
                                    <div class="mb-3">
                                        <label for="email_subject" class="form-label">Email Reply To</label>
                                        <input type="text" class="form-control p-2" name="email_replyto" id="email_replyto" placeholder="mail@example.com">
                                    </div>
                                    <div class="mb-3">
                                        <label for="thks_mssg" class="form-label">Thankyou Message</label>
                                        <textarea class="form-control" id="thks_msg" name="tks_msg" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="nav-notification" role="tabpanel" aria-labelledby="nav-notification-tab" tabindex="0">
                                <div class="d-flex flex-row justify-content-between align-items-center">
                                    <span>
                                        <h5 class="m-0">Notification mail to Admin</h5>
                                    </span>
                                    <label class="switch">
                                        <input name="notification" id="switch_notification" class="switch-input" type="checkbox" value="true">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <p class="fw-light fst-italic text">Want to send a submission copy to admin by email? <strong>Active this one.</strong></p>
                                <div id="notification_form" class="mt-3">
                                    <div class="mb-3">
                                        <label for="notif_subject" class="form-label">Email Subject</label>
                                        <input type="text" class="form-control p-2" name="notif_subject" id="notif_subject" placeholder="Enter Email Subject Here">
                                    </div>
                                    <div class="mb-3">
                                        <label for="notif_email_to" class="form-label">Email From</label>
                                        <input type="email" class="form-control p-2" name="notif_email_from" id="notif_email_from" placeholder="mail@example.com">
                                    </div>
                                    <div class="mb-3">
                                        <label for="notif_email_to" class="form-label">Email To</label>
                                        <input type="text" class="form-control p-2" name="notif_email_to" id="notif_email_to" placeholder="mail@example.com">
                                        <p class="fw-light fst-italic text">Enter admin email where you want to send mail. <strong>for multiple email addresses please use "," separator.</strong></p>
                                    </div>
                                    <div class="mb-3">
                                        <label for="thks_mssg" class="form-label">Admin Note</label>
                                        <textarea class="form-control" id="adm_msg" name="adm_msg" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer px-4 border-0 pb-4 pt-0">
                        <button id="rform-save-button" type="button" class="col btn btn-accent py-3 rounded-3 fw-bold rform-save-btn">Save & Edit</button>
                        <button id="close-btn" type="button" class="col btn btn-link px-4 py-3 text-decoration-none fw-bold" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="formUpdate" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="updateLabel" aria-hidden="true" style="z-index: 99999;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form class="w-100" id="rtform-update-form" method="post">
                <div class="modal-content">
                    <div class="modal-header px-5">
                        <div class="d-flex flex-row align-items-center justify-content-center w-100">
                            <div class="border py-2 border-start-0 border-top-0 border-bottom-0 px-4">
                                <img src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/rtmkit.png'); ?>" width="125" alt="RTMKit Logo">
                            </div>
                            <div class="px-4">
                                <h4 class="m-0">Theme Builder</h4>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="height: auto !important;">
                        <input id="action" name="action" type="text" value="rtformupdate" hidden>
                        <input type="text" name="id" id="id" hidden>
                        <nav>
                            <ul class="nav nav-underline mb-3" id="nav-tab" role="tablist">
                                <li class="nav-item col">
                                    <button class="nav-link active w-100" id="nav-general-tab" data-bs-toggle="tab" data-bs-target="#nav-update-general" type="button" role="tab" aria-controls="nav-general" aria-selected="true">General</button>
                                </li>
                                <li class="nav-item col">
                                    <button class="nav-link w-100" id="nav-confirmation-tab" data-bs-toggle="tab" data-bs-target="#nav-update-confirmation" type="button" role="tab" aria-controls="nav-confirmation" aria-selected="false">Confirmation</button>
                                </li>
                                <li class="nav-item col">
                                    <button class="nav-link w-100" id="nav-notification-tab" data-bs-toggle="tab" data-bs-target="#nav-update-notification" type="button" role="tab" aria-controls="nav-notification" aria-selected="false">Notification</button>
                                </li>
                            </ul>
                        </nav>
                        <div class="tab-content p-3" id="nav-tabContent">
                            <div class="tab-pane fade show active" id="nav-update-general" role="tabpanel" aria-labelledby="nav-general-tab" tabindex="0">
                                <label for="form-name">Form Name</label>
                                <input type="text" name="form-name" id="form-name" class="form-control p-2" placeholder="Enter Form Name">
                                <h5 class="my-3">Settings</h5>
                                <hr>
                                <div class="mb-3">
                                    <label for="success-message" class="form-label">Success Message</label>
                                    <input type="text" class="form-control p-2" id="success-message" name="success-message" value="Thank you! Form submitted successfully.">
                                </div>
                                <div class="mb-3">
                                    <label for="entry-name" class="form-label">Entry Title</label>
                                    <input type="text" class="form-control p-2" id="entry-name" name="entry-name" value="Entry #">
                                    <p class="fw-light fst-italic text">To set a custom entry title, enclose the input name in {{ }}.</p>
                                </div>
                                <div class="d-flex flex-row justify-content-between align-items-center mb-3">
                                    <span>
                                        <p class="m-0">Require Login</p>
                                        <p class="fw-light fst-italic text">Without login, user can't submit the form.</p>
                                    </span>
                                    <label class="switch">
                                        <input name="require-login" id="switch" class="switch-input" type="checkbox" value="true">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="nav-update-confirmation" role="tabpanel" aria-labelledby="nav-confirmation-tab" tabindex="0">
                                <div class="d-flex flex-row justify-content-between align-items-center mb-3">
                                    <span>
                                        <h5 class="m-0">Confirmation mail to user</h5>
                                    </span>
                                    <label class="switch">
                                        <input name="confirmation" id="switch_confirmation" class="switch-input" type="checkbox" value="true">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <p class="fw-light fst-italic text">Want to send a submission copy to user by email? <strong>Active this one.The form must have at least one Email widget and it should be required.</strong></p>
                                <div id="confirmation_form">
                                    <div class="mb-3">
                                        <label for="email_subject" class="form-label">Email Subject</label>
                                        <input type="text" class="form-control p-2" name="email_subject" id="update_email_subject" placeholder="Enter Email Subject Here">
                                    </div>
                                    <div class="mb-3">
                                        <label for="email_subject" class="form-label">Email From</label>
                                        <input type="email" class="form-control p-2" name="email_from" id="update_email_from" placeholder="mail@example.com">
                                    </div>
                                    <div class="mb-3">
                                        <label for="email_subject" class="form-label">Email Reply To</label>
                                        <input type="text" class="form-control p-2" name="email_replyto" id="update_email_replyto" placeholder="mail@example.com">
                                    </div>
                                    <div class="mb-3">
                                        <label for="thks_mssg" class="form-label">Thankyou Message</label>
                                        <textarea class="form-control" id="update_thks_msg" name="tks_msg" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="nav-update-notification" role="tabpanel" aria-labelledby="nav-notification-tab" tabindex="0">
                                <div class="d-flex flex-row justify-content-between align-items-center mb-3">
                                    <span>
                                        <h5 class="m-0">Notification mail to Admin</h5>
                                    </span>
                                    <label class="switch">
                                        <input name="notification" id="switch_notification" class="switch-input" type="checkbox" value="true">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <p class="fw-light fst-italic text">Want to send a submission copy to admin by email? <strong>Active this one.</strong></p>
                                <div id="notification_form">
                                    <div class="mb-3">
                                        <label for="notif_subject" class="form-label">Email Subject</label>
                                        <input type="text" class="form-control p-2" name="notif_subject" id="update_notif_subject" placeholder="Enter Email Subject Here">
                                    </div>
                                    <div class="mb-3">
                                        <label for="notif_email_from" class="form-label">Email From</label>
                                        <input type="email" class="form-control p-2" name="notif_email_from" id="update_notif_email_from" placeholder="mail@example.com">
                                    </div>
                                    <div class="mb-3">
                                        <label for="notif_email_to" class="form-label">Email To</label>
                                        <input type="text" class="form-control p-2" name="notif_email_to" id="update_notif_email_to" placeholder="mail@example.com">
                                        <span class="fw-light fst-italic text">Enter admin email where you want to send mail. <strong>for multiple email addresses please use "," separator.</strong></span>
                                    </div>
                                    <div class="mb-3">
                                        <label for="thks_mssg" class="form-label">Admin Note</label>
                                        <textarea class="form-control" id="update_adm_msg" name="adm_msg" rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer px-4 border-0 pb-4 pt-0">
                        <button id="rform-save-button" type="button" class="col btn btn-accent py-3 rounded-3 fw-bold rform-save-update-btn">Save Changes</button>
                        <button id="close-btn" type="button" class="col btn btn-link px-4 py-3 text-decoration-none fw-bold" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="rform-editor-modal" class="rform-modal">
        <div class="rform-modal-content">
            <div class="elementor-editor-header-iframe">
                <div class="rform-editor-header gap-2">
                    <svg id="esTFm6Uueg21" xmlns="http://www.w3.org/2000/svg" width="25" height="25" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 492.94 492.94" shape-rendering="geometricPrecision" text-rendering="geometricPrecision" project-id="f39061fa0d7140c0b843c54bc4fc263e" export-id="8175fbc5b63142aeb21ae9d901b96505" cached="false">
                        <g transform="matrix(1.59639 0 0 1.59639-132.842546-145.601744)">
                            <rect width="82.32" height="82.32" rx="0" ry="0" transform="translate(123.22 294.99)" fill="#00cea6" stroke-width="0"></rect>
                            <g>
                                <polygon points="342.61,268.16 316.74,293.64 261.59,238.49 287.45,212.63 342.61,268.16" opacity="0.6" fill="#00cea6" stroke-width="0"></polygon>
                                <polygon points="400.1,377.31 288.12,377.31 270.64,359.83 260.83,350.02 205.69,294.88 123.22,212.41 123.22,100.44 123.43,100.65 400.06,377.27 400.1,377.31" fill="#00cea6" stroke-width="0"></polygon>
                            </g>
                            <path d="M395.54,206.04c2.63,2.62,2.61,6.89-.03,9.49l-18.16,17.89-.21.21-34.52,34.53-11.88,11.33l3.92-3.74c4.36-4.16,4.45-11.1.18-15.37L197.92,123.48h114.16c.53,0,1.04.21,1.41.58l82.04,81.98h.01Z" fill="#00cea6" stroke-width="0"></path>
                        </g>
                    </svg>
                    <strong>RTMForm</strong>
                </div>
                <button id="rform-save-editor-btn" class="btn btn-accent px-3 py-2 m-1 elementor-modal-iframe-btn-control fw-bold">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.5003 20.0498H6.00827C5.98827 20.0498 5.97827 20.0418 5.97427 20.0378V4.00883C5.97427 4.00883 5.98827 3.99683 6.00827 3.99683H13.0273V7.91083C13.0273 8.44883 13.4633 8.88483 14.0013 8.88483H18.0273V10.5238C18.0273 11.0618 18.4633 11.4978 19.0013 11.4978C19.5393 11.4978 19.9753 11.0618 19.9753 10.5238V7.91083C19.9753 7.91083 19.9753 7.90683 19.9753 7.90483C19.9753 7.88383 19.9733 7.86383 19.9723 7.84283C19.9723 7.83083 19.9723 7.81983 19.9703 7.80783C19.9683 7.78883 19.9653 7.77083 19.9623 7.75183C19.9603 7.73883 19.9583 7.72483 19.9563 7.71183C19.9533 7.69783 19.9493 7.68483 19.9463 7.67183C19.9373 7.63783 19.9273 7.60483 19.9153 7.57283C19.9103 7.55883 19.9063 7.54483 19.9003 7.53083C19.9003 7.53083 19.9003 7.52983 19.8993 7.52883C19.8783 7.47983 19.8523 7.43283 19.8233 7.38683C19.8183 7.37983 19.8153 7.37183 19.8103 7.36483C19.8023 7.35283 19.7923 7.34083 19.7833 7.32883C19.7733 7.31483 19.7633 7.30083 19.7523 7.28783C19.7443 7.27883 19.7353 7.26983 19.7273 7.26083C19.7143 7.24583 19.7003 7.23083 19.6863 7.21683C19.6843 7.21483 19.6833 7.21383 19.6823 7.21183L14.7943 2.45583C14.6173 2.20983 14.3293 2.04883 14.0033 2.04883C13.9993 2.04883 13.9953 2.04883 13.9923 2.04883C13.9883 2.04883 13.9843 2.04883 13.9803 2.04883H6.01127C4.91827 2.04883 4.03027 2.92583 4.03027 4.00283V20.0418C4.03027 21.1198 4.91927 21.9958 6.01127 21.9958H11.5033C12.0413 21.9958 12.4773 21.5598 12.4773 21.0218C12.4773 20.4838 12.0413 20.0478 11.5033 20.0478L11.5003 20.0498ZM14.9743 5.35183L16.6033 6.93683H14.9743V5.35183Z" fill="currentColor" />
                        <path d="M17.0005 12.0029C14.2325 12.0029 11.9805 14.2549 11.9805 17.0229C11.9805 19.7909 14.2325 22.0429 17.0005 22.0429C19.7685 22.0429 22.0205 19.7909 22.0205 17.0229C22.0205 14.2549 19.7685 12.0029 17.0005 12.0029ZM19.6275 15.0169C19.8565 15.2379 19.9035 15.5769 19.7645 15.8459C19.7345 15.9049 19.6945 15.9609 19.6455 16.0109L16.7555 19.0109C16.6245 19.1469 16.4445 19.2249 16.2555 19.2269H16.2485C16.0615 19.2269 15.8835 19.1529 15.7515 19.0209L14.3635 17.6339C14.0895 17.3589 14.0895 16.9139 14.3635 16.6389C14.6385 16.3639 15.0835 16.3639 15.3585 16.6389L16.2395 17.5199L18.6325 15.0359C18.7475 14.9159 18.8945 14.8469 19.0475 14.8269C19.2515 14.7999 19.4665 14.8629 19.6265 15.0169H19.6275Z" fill="currentColor" />
                    </svg>

                    <?php echo esc_html__('SAVE & CLOSE', 'romethemeform') ?></button>
            </div>
            <div class="elementor-editor-container">
                <iframe class="ifr-editor" id="rform-elementor-editor" src="" frameborder="0"></iframe>
            </div>
        </div>
    </div>
    <style>
        .rform-modal {
            display: none;
            /* Hidden by default */
            position: fixed;
            /* Stay in place */
            z-index: 99999;
            /* Sit on top */
            left: 0;
            top: 0;
            width: 100%;
            /* Full width */
            height: 100%;
            /* Full height */
            overflow: auto;
            /* Enable scroll if needed */
            background-color: rgb(0, 0, 0);
            /* Fallback color */
            background-color: rgba(0, 0, 0, 0.6);
            /* Black w/ opacity */
        }

        .rform-modal-content {
            display: flex;
            gap: 5px;
            flex-direction: column;
            background-color: var(--secondary-color);
            border: solid 1px var(--border-color);
            margin: auto;
            /* 15% from the top and centered */
            width: 80%;
            /* Could be more or less, depending on screen size */
            height: 90%;
            box-shadow: 0px 0px 49px -19px rgba(0, 0, 0, 0.82);
            -webkit-box-shadow: 0px 0px 49px -19px rgba(0, 0, 0, 0.82);
            -moz-box-shadow: 0px 0px 49px -19px rgba(0, 0, 0, 0.82);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .ifr-editor {
            height: 100%;
            width: 100%;
        }

        .ifr-editor[src] {
            background-color: #34383c;
        }

        /* The Close Button */
        .close {
            color: rgb(255, 255, 255);
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .elementor-editor-container {
            width: 100%;
            height: 100%;
        }

        .flex-direction-col {
            display: flex;
            flex-direction: column;
        }

        .elementor-modal-iframe-btn-control {
            padding: 15px;
        }

        .elementor-editor-header-iframe {
            display: flex;
            justify-content: space-between;
            padding: 5px;
        }

        .edit-form-wrapper {
            padding: 5px;
            display: flex;
            justify-content: center;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        .rform-editor-header {
            display: flex;
            flex-direction: row;
            gap: 1rem;
            align-items: center;
            padding-inline: 1rem;
        }

        .rform-editor-header>strong {
            font-size: 1rem;
            color: white;
        }
    </style>
<?php endif; ?>