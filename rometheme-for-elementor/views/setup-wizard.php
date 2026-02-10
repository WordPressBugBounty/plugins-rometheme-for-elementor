<?php
$current_step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$proCurrentVersion = get_plugin_data(WP_PLUGIN_DIR . '/romethemekit-pro/RomeTheme_pro.php')['Version'] ?? null;
$formCurrentVersion = get_plugin_data(WP_PLUGIN_DIR . '/romethemeform/rometheme-form.php')['Version'] ?? null;
$showModules = [
    "header_footer" => [
        "type" => "free",
        "description" => "Create stunning headers and footers in minutes. Fast, easy, and professional."
    ],
    "template" => [
        "type" => "free",
        "description" => "Explore our documentation to help guided about our product and installation."
    ],
    "rtmicon" => [
        "type" => "free",
        "description" => "1000+ Crafted icons to impress, simplify, and transform your interface."
    ],
    "single_post" => [
        "type" => "pro",
        "description" => "Boost engagement with every post, showcase articles in a clean, stunning layout."
    ],
    "error_404" => [
        "type" => "pro",
        "description" => "Oops! Lost a page? Our fully custom, engaging 404 Error page has you covered."
    ],
    "search" => [
        "type" => "pro",
        "description" => "Smart, powerful search to deliver results instantly and enhance user experience."
    ],
    "stickycontent" => [
        "type" => "pro",
        "description" => "Explore our documentation to help guided about our product and installation."
    ],
    "glasseffect" => [
        "type" => "free",
        "description" => "Build your website with glass effect to smooth blur and soft transparency."
    ]
];
$allModules = \RTMKit\Modules\Storage::instance()->get_all_modules();
if (is_user_logged_in()) {
    // Get the current user object
    $current_user = wp_get_current_user();

    // Ensure the user object is valid
    if ($current_user instanceof WP_User) {
        // Access the user's email address
        $user_email = $current_user->user_email;
    }
}

$isProActive = (class_exists('RTMKitPro\Modules\Licenses\LicenseStorage') && \RTMKitPro\Modules\Licenses\LicenseStorage::instance()->isLicenseActive()) ? true : false;
?>

<div class="rtmkit-wizard">
    <div class="rtmkit-wizard-container d-flex flex-column gap-3">
        <div class="wizard-steps">
            <div class="wizard-step <?php echo $current_step >= 1 ? 'active' : ''; ?>" data-step="1">
                <div class="step-number">[01]</div>
                <div class="step-title">Getting Started</div>
            </div>
            <div class="wizard-step <?php echo $current_step >= 2 ? 'active' : ''; ?>" data-step="2">
                <div class="step-number">[02]</div>
                <div class="step-title">Plugin Check</div>
            </div>
            <div class="wizard-step <?php echo $current_step >= 3 ? 'active' : ''; ?>" data-step="3">
                <div class="step-number">[03]</div>
                <div class="step-title">Modules</div>
            </div>
            <div class="wizard-step <?php echo $current_step >= 4 ? 'active' : ''; ?>" data-step="4">
                <div class="step-number">[04]</div>
                <div class="step-title">Elementrue Theme</div>
            </div>
            <div class="wizard-step <?php echo $current_step >= 5 ? 'active' : ''; ?>" data-step="5">
                <div class="step-number">[05]</div>
                <div class="step-title">Get Update</div>
            </div>
            <div class="wizard-step <?php echo $current_step >= 6 ? 'active' : ''; ?>" data-step="6">
                <div class="step-number">[06]</div>
                <div class="step-title">Finalizing</div>
            </div>
        </div>
        <div class="wizard-content">
            <div id="getting-started" data-step-content="1" class="wizard-step-content <?php echo $current_step === 1 ? 'active' : ''; ?>">
                <div class="mb-5">
                    <div class="logo">
                        <div class="logo-image">
                            <img width="150" src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/rtmkit.png'); ?>" alt="RTMKit Logo">
                        </div>
                        <span class="rtmkit-version version">v.<?php echo esc_html(RTM_KIT_VERSION) ?></span>
                    </div>
                    <div class="row row-cols-lg-2 row-cols-1 h-100">
                        <div class="col">
                            <div class="d-flex flex-column gap-3 h-100 justify-content-end">
                                <span class="step-number-content">[&emsp;STEP 01&emsp;]</span>
                                <h1 class="text-uppercase">
                                    it's easy to <br>
                                    get started on rtmkit
                                </h1>
                                <p class="text-muted">
                                    Begin the setup process and configure <br> basic settings to get started with RTMkit.
                                </p>
                            </div>
                        </div>
                        <div class="col">
                            <ul class="list-group list-group-flush pe-5">
                                <li class="list-group-item">
                                    <div class="row row-cols-2">
                                        <div class="col-3">
                                            <img src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/getting-started.png'); ?>" alt="">
                                        </div>
                                        <div class="col-9">
                                            <div class="d-flex flex-column h-100 justify-content-center">
                                                <h5 class="mb-2">1. Get Started</h5>
                                                <p class="text-muted">
                                                    Begin the setup process and configure the basic settings to get started with RTMkit.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="row row-cols-2">
                                        <div class="col-3">
                                            <img src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/theme.png'); ?>" alt="">
                                        </div>
                                        <div class="col-9">
                                            <div class="d-flex flex-column h-100 justify-content-center">
                                                <h5 class="mb-2">2. Elementrue Theme</h5>
                                                <p class="text-muted">
                                                    Upgrade Your Website with Elementrue a
                                                    High-End WordPress Theme</p>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="row row-cols-2">
                                        <div class="col-3">
                                            <img src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/plugin-check.png'); ?>" alt="">
                                        </div>
                                        <div class="col-9">
                                            <div class="d-flex flex-column h-100 justify-content-center">
                                                <h5 class="mb-2">3. Plugin Check</h5>
                                                <p class="text-muted">
                                                    We’ll check your plugin version and compatibility to ensure everything works properly.</p>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="row row-cols-2">
                                        <div class="col-3">
                                            <img src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/finalizing.png'); ?>" alt="">
                                        </div>
                                        <div class="col-9">
                                            <div class="d-flex flex-column h-100 justify-content-center">
                                                <h5 class="mb-2">4. Finalizing</h5>
                                                <p class="text-muted">
                                                    Everything is set. Your plugin is ready and you can access the dashboard.</p>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                            </ul>
                        </div>
                    </div>
                </div>
                <div class="footer">
                    <button id="next-button" class="btn btn-gradient-accent" data-next="2">Next</button>
                </div>
            </div>
            <div id="plugin-check" data-step-content="2" class="wizard-step-content <?php echo $current_step === 2 ? 'active' : ''; ?>">
                <div class="mb-5">
                    <div class="logo">
                        <div class="logo-image">
                            <img width="150" src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/rtmkit.png'); ?>" alt="RTMKit Logo">
                        </div>
                        <span class="rtmkit-version">v.<?php echo esc_html(RTM_KIT_VERSION) ?></span>
                    </div>
                    <div class="d-flex flex-row w-100 align-items-start justify-content-center gap-4">
                        <span class="step-number-content">[&emsp;STEP 02&emsp;]</span>
                        <h1 class="text-uppercase">
                            Make sure your plugins <br> are compatible before <br> moving forward.
                        </h1>
                    </div>
                    <div class="row row-cols-2 mt-5">
                        <div class="col mt-5">
                            <div class="mt-5 d-flex gap-3">
                                <div class="d-flex gap-4">
                                    <div class="col">
                                        <div class="mb-4 rounded-4 bg-secondary p-3">
                                            <img src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/box-rtmkit.png'); ?>" alt="" class="img-fluid">
                                        </div>
                                    </div>
                                    <div class="col d-flex flex-column gap-3 justify-content-end">
                                        <div class="d-flex align-items-center gap-3">
                                            <h5 class="m-0 fw-normal"><span class="fw-semibold">RTM</span>kit Pro</h5>
                                            <span class="rtmkitpro-version version"><?php echo ($proCurrentVersion !== null && $proCurrentVersion !== '') ? esc_html('v.' . $proCurrentVersion) : 'Not installed'; ?></span>
                                        </div>
                                        <span class="text-muted">
                                            Exclusive widgets, theme tools, and features for professional websites.
                                        </span>
                                        <div id="rtmkit-pro-button">
                                            <div class="d-flex align-items-center gap-2 text-muted">
                                                <div class="spinner-border spinner-border-sm" role="status" style="--bs-spinner-border-width:0.1em">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <span id="rtmkitpro-status" class="">Version Checking...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-5 d-flex gap-3">
                                <div class="d-flex gap-4">
                                    <div class="col">
                                        <div class="mb-4 h-100 rounded-4 bg-secondary d-flex align-items-center justify-content-center">
                                            <img src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/form.png'); ?>" alt="" class="img-fluid w-100">
                                        </div>
                                    </div>
                                    <div class="col d-flex flex-column gap-3 justify-content-end">
                                        <div class="d-flex align-items-center gap-3">
                                            <h5 class="m-0 fw-normal"><span class="fw-semibold">RTM</span>Form</h5>
                                            <span class="rtmform-version version"><?php echo ($formCurrentVersion !== null && $formCurrentVersion !== '') ? esc_html('v.' . $formCurrentVersion) : 'Not installed'; ?></span>
                                        </div>
                                        <span class="text-muted">
                                            Friendly Elementor form builder for stylish, fully customizable forms.
                                        </span>
                                        <div id="rtmform-button">
                                            <div class="d-flex align-items-center gap-2 text-muted">
                                                <div class="spinner-border spinner-border-sm" role="status" style="--bs-spinner-border-width:0.1em">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <span id="rtmform-status" class="">Version Checking...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="footer gap-2">
                        <button id="next-button" class="btn btn-gradient-accent" disabled>
                            <div class="spinner-border spinner-border-sm" role="status"
                                style="--bs-spinner-border-width:0.1em"></div>
                            <span>Checking...</span>
                        </button>
                    </div>
                </div>
            </div>
            <div id="module-switcher" data-step-content="3" class="wizard-step-content <?php echo $current_step === 3 ? 'active' : ''; ?>">
                <div class="mb-5">
                    <div class="logo">
                        <div class="logo-image">
                            <img width="150" src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/rtmkit.png'); ?>" alt="RTMKit Logo">
                        </div>
                        <span class="rtmkit-version">v.<?php echo esc_html(RTM_KIT_VERSION) ?></span>
                    </div>
                    <div class="mt-4">
                        <div class="d-flex flex-row w-100 align-items-start justify-content-start gap-4">
                            <h1 class="text-uppercase">
                                <span class="step-number-content">[&emsp;STEP 03&emsp;]</span>
                                upgrade your website<br>
                                with a complete modules
                            </h1>
                        </div>
                        <div class="row row-cols-4 g-3">
                            <?php foreach ($showModules as $key => $val) :
                                $module = $allModules[$key];
                            ?>
                                <div class="col">
                                    <div class="d-flex flex-column gap-3 bg-secondary rounded-4 h-100 p-4">
                                        <label id="<?php echo esc_attr($key) ?>" class="switch gap-3" data-module-type="<?php echo esc_attr($module['type']) ?>">
                                            <input name="<?php echo esc_attr($key) ?>"
                                                class="switch-input switch-status"
                                                type="checkbox" value="true"
                                                <?php echo ($module['type'] == 'pro' && ! $isProActive) ? 'disabled' : 'checked' ?>
                                                hidden>
                                            <span class="slider round">
                                                <?php if ($module['type'] == 'pro' && ! $isProActive) : ?>
                                                    <i class="fa-solid fa-lock"></i>
                                                <?php endif; ?>
                                            </span>
                                        </label>
                                        <div class="d-flex align-items-center gap-3">
                                            <h6 class="fw-normal m-0"><?php echo esc_html($module['name']) ?></h6>
                                            <?php if ($module['type'] == 'pro'): ?>
                                                <span class="badge pro">
                                                    <i class="fa-solid fa-crown"></i>
                                                    Pro</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-muted">
                                            <?php echo esc_html($val['description']) ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="footer">
                    <button id="next-button" class="btn btn-link" data-next="2">Back</button>
                    <button id="next-button" class="btn btn-gradient-accent" data-action="save-module">Next</button>
                </div>
            </div>
            <div id="elementrue" data-step-content="4" class="wizard-step-content <?php echo $current_step === 4 ? 'active' : ''; ?>">
                <div class="mb-5">
                    <div class="row row-cols-lg-2 row-cols-1 h-100">
                        <div class="col">
                            <div class="d-flex flex-column gap-5">
                                <div class="logo">
                                    <div class="logo-image">
                                        <img width="150" src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/elementrue.png'); ?>" alt="elementrue Logo">
                                    </div>
                                </div>
                                <div class="d-flex flex-column gap-3 h-100">
                                    <h1 class="text-uppercase">
                                        official themes<br> support, by rtmkit
                                    </h1>
                                    <h5 class="border-bottom text-muted pb-2 fw-normal" style="width: fit-content;">Included with your purchase</h5>
                                    <ul class="p-0 m-0 d-flex flex-column gap-2">
                                        <li>
                                            <div class="d-flex gap-2 align-items-center">
                                                <i class="fa-regular fa-circle-check elementrue-color"></i>
                                                <span>50+ Niche Business WordPress Themes</span>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="d-flex gap-2 align-items-center">
                                                <i class="fa-regular fa-circle-check elementrue-color"></i>
                                                <span>320+ Premium Blocks & Layouts</span>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="d-flex gap-2 align-items-center">
                                                <i class="fa-regular fa-circle-check elementrue-color"></i>
                                                <span>Lifetime Access to RTMkit PRO</span>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="d-flex gap-2 align-items-center">
                                                <i class="fa-regular fa-circle-check elementrue-color"></i>
                                                <span>Lifetime Updates</span>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="d-flex gap-2 align-items-center">
                                                <i class="fa-regular fa-circle-check elementrue-color"></i>
                                                <span>6 Months of Dedicated Support</span>
                                            </div>
                                        </li>
                                    </ul>
                                    <div>
                                        <a href="https://elementrue.rometheme.net/" target="_blank" class="btn elementrue-btn" style="background-image: url(<?php echo esc_url(RTM_KIT_URL . 'assets/images/bg-button.png') ?>);">
                                            <img src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/elementrue-logo.png');  ?>" alt="" width="20">
                                            GET ELEMENTRUE
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <img src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/group.png'); ?>" class="img-fluid" alt="" style="
                                position: absolute; top: 0;bottom: 0;right: -23rem;height: 100%;object-fit: cover;width: 90%;
                            ">
                        </div>
                    </div>
                </div>
                <div class="footer">
                    <button id="next-button" class="btn btn-link" data-next="3">Back</button>
                    <button id="next-button" class="btn btn-gradient-accent" data-next="5">Next</button>
                </div>
            </div>
            <div id="subscribe" data-step-content="5" class="wizard-step-content <?php echo $current_step === 5 ? 'active' : ''; ?>">
                <div class="mb-5">
                    <div class="logo">
                        <div class="logo-image">
                            <img width="150" src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/rtmkit.png'); ?>" alt="RTMKit Logo">
                        </div>
                        <span class="rtmkit-version">v.<?php echo esc_html(RTM_KIT_VERSION) ?></span>
                    </div>
                    <div class="d-flex flex-row w-100 align-items-start justify-content-start gap-4 mt-5">
                        <span class="step-number-content">[&emsp;STEP 05&emsp;]</span>
                        <div class="d-flex flex-column gap-3 w-50">
                            <h1 class="text-uppercase">
                                Get the latest updates <br> and announcements.
                            </h1>
                            <h1 class="text-uppercase text-muted">
                                Subscribe to our <br> newsletter.
                            </h1>
                            <form id="subscribe-form" class="d-flex flex-column gap-3">
                                <div class="d-flex flex-row gap-3">
                                    <input type="email" name="email" id="email" class="form-control" placeholder="Your E-mail" value="<?php echo esc_attr($user_email) ?>">
                                    <button type="submit" class="btn btn-submit fw-semibold">Subscribe
                                        <svg width="19" height="16" viewBox="0 0 19 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M18.5669 7.2768L11.4731 0.183047C11.2294 -0.0610156 10.8331 -0.0610156 10.5894 0.183047C10.3453 0.427109 10.3453 0.822734 10.5894 1.0668L16.6153 7.09273L0.625625 7.07742H0.625C0.28 7.07742 0.0003125 7.3568 0 7.7018C0 8.0468 0.279375 8.32711 0.624375 8.32742L16.6169 8.34273L10.5891 14.3705C10.345 14.6146 10.345 15.0102 10.5891 15.2543C10.7109 15.3765 10.8709 15.4374 11.0309 15.4374C11.1909 15.4374 11.3509 15.3765 11.4728 15.2543L18.5666 8.16055C18.8106 7.91648 18.8106 7.52086 18.5666 7.2768H18.5669Z" fill="white" />
                                        </svg>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="footer">
                        <button id="next-button" class="btn btn-link" data-next="4">Back</button>
                        <button id="next-button" class="btn btn-gradient-accent" data-next="6">Next</button>
                    </div>
                </div>
            </div>
            <div id="finish" data-step-content="6" class="wizard-step-content <?php echo $current_step === 6 ? 'active' : ''; ?>">
                <div class="mb-5">
                    <div class="row row-cols-2 gx-5">
                        <div class="col">
                            <img src="<?php echo esc_url(RTM_KIT_URL . 'assets/images/content-wrapper.png'); ?>" alt="" class="img-fluid">
                        </div>
                        <div class="col">
                            <div class="d-flex flex-column  gap-3 h-100 justify-content-end ps-5">
                                <span class="step-number-content">[&emsp;STEP 06&emsp;]</span>
                                <h1 class="text-uppercase">
                                    finally, <br>
                                    Setup Completed
                                    Successfully.
                                </h1>
                                <h1 class="text-uppercase text-muted">
                                    Enjoy ! Your Plugin <br>
                                    Is Ready to Use
                                </h1>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="footer">
                    <button id="next-button" class="btn btn-link" data-next="5">Back</button>
                    <button id="next-button" class="btn btn-light" data-finish="true">Visit Dashboard</button>
                </div>
            </div>
        </div>
    </div>
</div>