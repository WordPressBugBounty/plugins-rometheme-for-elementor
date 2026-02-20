<?php

namespace RTMKit\Modules;

class Menu
{
    /**
     * Initialize the menu module.
     */

    protected static $instance;

    protected $menus;

    public static function instance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_bar_menu', [$this, 'top_bar_menu'], 100);
        add_action('admin_enqueue_scripts', [$this, 'feature_drawer_script']);
        add_action('admin_footer', [$this, 'feature_drawer']);
        add_action('wp_enqueue_scripts', [$this, 'feature_drawer_script']);
        add_action('wp_footer', [$this, 'feature_drawer']);
    }

    protected function default_menus(): array
    {
        $menus = [
            'get_started' => [
                'dashboard' => [
                    'title' => __('Welcome', 'rometheme-for-elementor'),
                    'capability' => 'manage_options',
                    'menu_slug' => 'rtmkit',
                    'function' => [$this, 'rtmkit_root_page'],
                    'icon_url' => RTM_KIT_URL . 'assets/images/romethemekit.svg',
                    'position' => 20,
                ],
            ],

            'features' => [
                'widgets' => [
                    'title' => __('Widgets', 'rometheme-for-elementor'),
                    'capability' => 'manage_options',
                    'menu_slug' => 'rtmkit&path=widgets',
                    'function' => [$this, 'rtmkit_root_page'],
                ],
                'modules' => [
                    'title' => __('Modules', 'rometheme-for-elementor'),
                    'capability' => 'manage_options',
                    'menu_slug' => 'rtmkit&path=modules',
                    'function' => [$this, 'rtmkit_root_page'],
                ],
                'themebuilder' => [
                    'title' => __('Theme Builder', 'rometheme-for-elementor'),
                    'capability' => 'manage_options',
                    'menu_slug' => 'rtmkit&path=themebuilder',
                    'function' => [$this, 'rtmkit_root_page'],
                ],
                'templates' => [
                    'title' => __('Template Kits', 'rometheme-for-elementor'),
                    'capability' => 'manage_options',
                    'menu_slug' => 'rtmkit&path=templates',
                    'function' => [$this, 'rtmkit_root_page'],
                ],
            ],

            'settings' => [
                'settings' => [
                    'title' => __('Global Kit Setup', 'rometheme-for-elementor'),
                    'capability' => 'manage_options',
                    'menu_slug' => 'rtmkit&path=settings',
                    'function' => [$this, 'rtmkit_root_page'],
                ],
                'updates' => [
                    'title' => __('Version Controls', 'rometheme-for-elementor'),
                    'capability' => 'manage_options',
                    'menu_slug' => 'rtmkit&path=updates',
                    'function' => [$this, 'rtmkit_root_page'],
                ],
            ],

            'information' => [
                'submission' => [
                    'title' => __('Submission', 'rometheme-for-elementor'),
                    'capability' => 'manage_options',
                    'menu_slug' => 'rtmkit&path=submission',
                    'function' => [$this, 'rtmkit_root_page'],
                ],
                'system-status' => [
                    'title' => __('System Info', 'rometheme-for-elementor'),
                    'capability' => 'manage_options',
                    'menu_slug' => 'rtmkit&path=system-status',
                    'function' => [$this, 'rtmkit_root_page'],
                ],
                'documentation' => [
                    'title' => __('Help & Center', 'rometheme-for-elementor'),
                    'capability' => 'read',
                    'menu_slug' => 'rtmkit&path=help',
                    'function' => [$this, 'rtmkit_root_page'],
                ],
            ],
        ];

        return $menus;
    }

    /**
     * Register the plugin menu in the WordPress admin.
     */
    public function register_menu()
    {
        $this->menus = $this->get_menus();
        $sidebar_menu = ['dashboard', 'widgets', 'themebuilder', 'templates', 'license', 'submission'];
        add_menu_page(
            __('RTMKit Dashboard', 'rometheme-for-elementor'),
            __('RTMKit', 'rometheme-for-elementor'),
            'manage_options',
            'rtmkit',
            [$this, 'rtmkit_root_page'],
            RTM_KIT_URL . 'assets/images/romethemekit.svg',
            20
        );
        foreach ($this->menus as $key => $menu) {
            foreach ($menu as $sub_key => $sub_menu) {
                if (!isset($sub_menu['target']) || $sub_menu['target'] !== '_blank') {
                    if (in_array($sub_key, $sidebar_menu)) {
                        add_submenu_page(
                            'rtmkit',
                            esc_html($sub_menu['title']),
                            esc_html($sub_menu['title']),
                            $sub_menu['capability'] ?? 'manage_options',
                            $sub_menu['menu_slug'],
                            $sub_menu['function'] ?? [$this, 'rtmkit_root_page'],
                            $sub_menu['position'] ?? null
                        );
                    }
                }
            }
        }
        if (!class_exists('RTMKitPro\Core\Plugin') || !\RTMKitPro\Modules\Licenses\LicenseStorage::instance()->isLicenseActive()) {
            add_submenu_page(
                'rtmkit',
                'Upgrade to Pro',
                '<svg width="20" height="20" viewBox="0 0 24 29" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M3.49698 9.00236L4.78398 19.9374H19.227L20.513 9.00236L16.503 11.6754L12.005 5.37836L7.50698 11.6754L3.49698 9.00236ZM2.80598 6.13736L7.00498 8.93736L11.191 3.07636C11.2835 2.94673 11.4056 2.84107 11.5472 2.76816C11.6888 2.69526 11.8457 2.65723 12.005 2.65723C12.1642 2.65723 12.3212 2.69526 12.4628 2.76816C12.6044 2.84107 12.7265 2.94673 12.819 3.07636L17.005 8.93636L21.205 6.13736C21.3639 6.03169 21.5497 5.97368 21.7404 5.97019C21.9312 5.96669 22.119 6.01785 22.2817 6.11762C22.4443 6.2174 22.575 6.36163 22.6584 6.53328C22.7417 6.70493 22.7742 6.89684 22.752 7.08636L21.11 21.0534C21.0816 21.2968 20.9647 21.5213 20.7817 21.6843C20.5986 21.8472 20.3621 21.9373 20.117 21.9374H3.89398C3.6489 21.9373 3.41236 21.8472 3.22931 21.6843C3.04625 21.5213 2.92941 21.2968 2.90098 21.0534L1.25798 7.08736C1.2354 6.89761 1.26767 6.70536 1.35095 6.53337C1.43424 6.36138 1.56506 6.21686 1.72792 6.11691C1.89079 6.01696 2.07889 5.96576 2.26995 5.96939C2.461 5.97301 2.64702 6.0313 2.80598 6.13736ZM12.006 15.9374C11.7433 15.9374 11.4833 15.8858 11.2406 15.7853C10.9979 15.6849 10.7774 15.5376 10.5916 15.3519C10.4059 15.1663 10.2585 14.9458 10.1579 14.7032C10.0573 14.4606 10.0055 14.2005 10.0055 13.9379C10.0054 13.6752 10.0571 13.4151 10.1575 13.1725C10.258 12.9298 10.4052 12.7093 10.5909 12.5235C10.7766 12.3377 10.997 12.1904 11.2397 12.0898C11.4823 11.9892 11.7423 11.9374 12.005 11.9374C12.5354 11.9374 13.0441 12.1481 13.4192 12.5231C13.7943 12.8982 14.005 13.4069 14.005 13.9374C14.005 14.4678 13.7943 14.9765 13.4192 15.3516C13.0441 15.7266 12.5364 15.9374 12.006 15.9374Z" fill="#121416"></path>
            </svg> Upgrade to Pro',
                'manage_options',
                'rtmkit-upgrade-to-pro',
                function () {
                    wp_redirect('https://rometheme.net/plugins/rtmkit/pricing/');
                    exit;
                },
                null // posisi atas
            );
        }
    }

    /**
     * Render the root page.
     */
    public function rtmkit_root_page()
    {
        echo '<div id="rtmkit-root" class="rtmkit-wrapper"></div>';
    }

    /**
     * Enqueue scripts and styles for the plugin.
     */
    public function enqueue_scripts()
    {
        $screen = get_current_screen();
        if (str_contains($screen->id, 'rtmkit')) {
            wp_enqueue_style('rtmkit-style', RTM_KIT_URL . 'assets/css/rtmkit.min.css', [], RTM_KIT_VERSION);
            wp_enqueue_style('select2.min.css', RTM_KIT_URL . 'assets/css/select2.min.css', [], RTM_KIT_VERSION);
            wp_enqueue_script('select2.min.js', RTM_KIT_URL . 'assets/js/select2.min.js', ['jquery'], RTM_KIT_VERSION, false);
            wp_enqueue_script('bootstrap-js', RTM_KIT_URL . 'assets/js/bootstrap/bootstrap.min.js', [], RTM_KIT_VERSION, false);
            wp_enqueue_script('scripts-js', RTM_KIT_URL . 'assets/js/scripts.js', ['jquery'], RTM_KIT_VERSION, true);
            wp_enqueue_script('scrollspy-js', RTM_KIT_URL . 'assets/js/Scrollspy.js', [], RTM_KIT_VERSION, true);
            wp_enqueue_script('rtmkit-admin-js', RTM_KIT_URL . 'build/index.js', ['wp-element'], RTM_KIT_VERSION);
            wp_localize_script('rtmkit-admin-js', 'rtmkit_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('rtmkit_nonce'),
                'pluginUrl' => RTM_KIT_URL
            ]);
        }
    }

    public function get_menus()
    {
        if (!isset($this->menus)) {
            $this->menus = apply_filters('rtmkit_menus', $this->default_menus());
        }
        return $this->menus;
    }

    public function top_bar_menu($wp_admin_bar)
    {
        // Parent
        $wp_admin_bar->add_node([
            'id' => 'rtmkit_menu_bar',
            'title' => '<span>RTMkit</span>',
            'href' => false,
        ]);

        // Submenu
        $wp_admin_bar->add_node([
            'id' => 'rtmkit_widgets',
            'parent' => 'rtmkit_menu_bar',
            'title' => 'Widgets',
            'href' => admin_url('admin.php?page=rtmkit&path=widgets'),
        ]);

        $wp_admin_bar->add_node([
            'id' => 'rtmkit_themebuilder',
            'parent' => 'rtmkit_menu_bar',
            'title' => 'Theme Builder',
            'href' => admin_url('admin.php?page=rtmkit&path=themebuilder'),
        ]);

        $wp_admin_bar->add_node([
            'id' => 'rtmkit_templates',
            'parent' => 'rtmkit_menu_bar',
            'title' => 'Templates Kits',
            'href' => admin_url('admin.php?page=rtmkit&path=templates'),
        ]);

        $wp_admin_bar->add_node([
            'id' => 'rtmkit_submission',
            'parent' => 'rtmkit_menu_bar',
            'title' => 'Submission',
            'href' => admin_url('admin.php?page=rtmkit&path=submission'),
        ]);

        $wp_admin_bar->add_node([
            'id' => 'rtmkit_adminbar_divider',
            'parent' => 'rtmkit_menu_bar',
            'meta' => [
                'class' => 'rtmkit-adminbar-divider'
            ]
        ]);

        $wp_admin_bar->add_node([
            'id' => 'rtmkit_documentation',
            'parent' => 'rtmkit_menu_bar',
            'title' => 'Documentation',
            'href' => 'https://support.rometheme.net/docs/romethemekit/',
            'meta' => [
                'target' => '_blank',
            ]
        ]);

        $wp_admin_bar->add_node([
            'id' => 'rtmkit_rate_us',
            'parent' => 'rtmkit_menu_bar',
            'title' => '
            <span class="rate-us-title">
                Rate Us
                <span class="rate-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M309.5-18.9c-4.1-8-12.4-13.1-21.4-13.1s-17.3 5.1-21.4 13.1L193.1 125.3 33.2 150.7c-8.9 1.4-16.3 7.7-19.1 16.3s-.5 18 5.8 24.4l114.4 114.5-25.2 159.9c-1.4 8.9 2.3 17.9 9.6 23.2s16.9 6.1 25 2L288.1 417.6 432.4 491c8 4.1 17.7 3.3 25-2s11-14.2 9.6-23.2L441.7 305.9 556.1 191.4c6.4-6.4 8.6-15.8 5.8-24.4s-10.1-14.9-19.1-16.3L383 125.3 309.5-18.9z"/></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M309.5-18.9c-4.1-8-12.4-13.1-21.4-13.1s-17.3 5.1-21.4 13.1L193.1 125.3 33.2 150.7c-8.9 1.4-16.3 7.7-19.1 16.3s-.5 18 5.8 24.4l114.4 114.5-25.2 159.9c-1.4 8.9 2.3 17.9 9.6 23.2s16.9 6.1 25 2L288.1 417.6 432.4 491c8 4.1 17.7 3.3 25-2s11-14.2 9.6-23.2L441.7 305.9 556.1 191.4c6.4-6.4 8.6-15.8 5.8-24.4s-10.1-14.9-19.1-16.3L383 125.3 309.5-18.9z"/></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M309.5-18.9c-4.1-8-12.4-13.1-21.4-13.1s-17.3 5.1-21.4 13.1L193.1 125.3 33.2 150.7c-8.9 1.4-16.3 7.7-19.1 16.3s-.5 18 5.8 24.4l114.4 114.5-25.2 159.9c-1.4 8.9 2.3 17.9 9.6 23.2s16.9 6.1 25 2L288.1 417.6 432.4 491c8 4.1 17.7 3.3 25-2s11-14.2 9.6-23.2L441.7 305.9 556.1 191.4c6.4-6.4 8.6-15.8 5.8-24.4s-10.1-14.9-19.1-16.3L383 125.3 309.5-18.9z"/></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M309.5-18.9c-4.1-8-12.4-13.1-21.4-13.1s-17.3 5.1-21.4 13.1L193.1 125.3 33.2 150.7c-8.9 1.4-16.3 7.7-19.1 16.3s-.5 18 5.8 24.4l114.4 114.5-25.2 159.9c-1.4 8.9 2.3 17.9 9.6 23.2s16.9 6.1 25 2L288.1 417.6 432.4 491c8 4.1 17.7 3.3 25-2s11-14.2 9.6-23.2L441.7 305.9 556.1 191.4c6.4-6.4 8.6-15.8 5.8-24.4s-10.1-14.9-19.1-16.3L383 125.3 309.5-18.9z"/></svg>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><path d="M309.5-18.9c-4.1-8-12.4-13.1-21.4-13.1s-17.3 5.1-21.4 13.1L193.1 125.3 33.2 150.7c-8.9 1.4-16.3 7.7-19.1 16.3s-.5 18 5.8 24.4l114.4 114.5-25.2 159.9c-1.4 8.9 2.3 17.9 9.6 23.2s16.9 6.1 25 2L288.1 417.6 432.4 491c8 4.1 17.7 3.3 25-2s11-14.2 9.6-23.2L441.7 305.9 556.1 191.4c6.4-6.4 8.6-15.8 5.8-24.4s-10.1-14.9-19.1-16.3L383 125.3 309.5-18.9z"/></svg>
                </span>
            </span>
            ',
            'href' => 'https://wordpress.org/plugins/rometheme-for-elementor/#reviews',
            'meta' => [
                'target' => '_blank',
                'class' => 'rtmkit-rate-us'
            ]
        ]);

        $wp_admin_bar->add_node([
            'id' => 'rtmkit_upgrade',
            'parent' => 'rtmkit_menu_bar',
            'title' => '<svg width="20" height="20" viewBox="0 0 24 29" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M3.49698 9.00236L4.78398 19.9374H19.227L20.513 9.00236L16.503 11.6754L12.005 5.37836L7.50698 11.6754L3.49698 9.00236ZM2.80598 6.13736L7.00498 8.93736L11.191 3.07636C11.2835 2.94673 11.4056 2.84107 11.5472 2.76816C11.6888 2.69526 11.8457 2.65723 12.005 2.65723C12.1642 2.65723 12.3212 2.69526 12.4628 2.76816C12.6044 2.84107 12.7265 2.94673 12.819 3.07636L17.005 8.93636L21.205 6.13736C21.3639 6.03169 21.5497 5.97368 21.7404 5.97019C21.9312 5.96669 22.119 6.01785 22.2817 6.11762C22.4443 6.2174 22.575 6.36163 22.6584 6.53328C22.7417 6.70493 22.7742 6.89684 22.752 7.08636L21.11 21.0534C21.0816 21.2968 20.9647 21.5213 20.7817 21.6843C20.5986 21.8472 20.3621 21.9373 20.117 21.9374H3.89398C3.6489 21.9373 3.41236 21.8472 3.22931 21.6843C3.04625 21.5213 2.92941 21.2968 2.90098 21.0534L1.25798 7.08736C1.2354 6.89761 1.26767 6.70536 1.35095 6.53337C1.43424 6.36138 1.56506 6.21686 1.72792 6.11691C1.89079 6.01696 2.07889 5.96576 2.26995 5.96939C2.461 5.97301 2.64702 6.0313 2.80598 6.13736ZM12.006 15.9374C11.7433 15.9374 11.4833 15.8858 11.2406 15.7853C10.9979 15.6849 10.7774 15.5376 10.5916 15.3519C10.4059 15.1663 10.2585 14.9458 10.1579 14.7032C10.0573 14.4606 10.0055 14.2005 10.0055 13.9379C10.0054 13.6752 10.0571 13.4151 10.1575 13.1725C10.258 12.9298 10.4052 12.7093 10.5909 12.5235C10.7766 12.3377 10.997 12.1904 11.2397 12.0898C11.4823 11.9892 11.7423 11.9374 12.005 11.9374C12.5354 11.9374 13.0441 12.1481 13.4192 12.5231C13.7943 12.8982 14.005 13.4069 14.005 13.9374C14.005 14.4678 13.7943 14.9765 13.4192 15.3516C13.0441 15.7266 12.5364 15.9374 12.006 15.9374Z" fill="#121416"></path>
            </svg>' . 'Upgrade to Pro',
            'href' => 'https://rometheme.net/plugins/rtmkit/pricing/',
            'meta' => [
                'target' => '_blank',
                'class' => 'rtmkit-upgrade',
            ],
        ]);

        // What's new
        $wp_admin_bar->add_node([
            'id' => 'rtmkit_whats_new_bar',
            'title' => '<span>What\'s New</span>',
            'href' => false,
            'parent' => 'top-secondary',
            'meta' => [
                'class' => 'custom-drawer-trigger'
            ]
        ]);
    }

    public function feature_drawer()
    {
        if (current_user_can('edit_posts')):
?>
            <div id="custom-drawer-overlay"></div>

            <div id="custom-admin-drawer">
                <div class="drawer-header">
                    What's new on RTMKit 2.0
                </div>
                <div class="drawer-content">
                    <div class="content-info">
                        <div style="position: relative; display: inline-block;">
                            <a href="https://www.youtube.com/watch?v=-jRhhPu6w2M" target="_blank">
                                <img src="https://img.youtube.com/vi/-jRhhPu6w2M/hqdefault.jpg" style="width: 100%" alt="Video">
                                <!-- Simple Play Button Overlay -->
                                <div
                                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 40px; color: white; background: rgba(0,0,0,0.5); padding: 10px 20px; border-radius: 10px;">
                                    ▶</div>
                            </a>
                        </div>
                        <h4>Version 2.0.0 – February 10, 2026</h4>
                        <ul>
                            <li>New: Improve the user experience by updating the visual interface.</li>
                            <li>Improved widget controls and UI consistency</li>
                            <li>Enhanced responsive and editor preview behavior</li>
                            <li>Fixed various widget, style, and control issues</li>
                            <li>Resolved minor rendering and PHP warnings</li>
                            <li>Performance and asset loading improvements</li>
                            <li>Internal refactor for better stability and compatibility</li>
                            <li>Security Update</li>
                        </ul>
                    </div>
                    <div class="content-info">
                        <h4>Resolve error after upgrade to 2.0</h4>
                        <ul style="list-style: none; padding-left: 0; display: flex; flex-direction: column; gap: 24px;">
                            <li style="margin:0;">
                                <div style="width: 100%; display: flex ; justify-content:space-between; align-items:center;">
                                    RTMkit
                                    <a class="link-btn-accent" href="https://support.rometheme.net/docs/romethemekit/migration-v-2-0-0/"
                                        target="_blank">Read This Guide</a>
                                </div>
                            </li>
                            <li style="margin:0;">
                                <div style="width: 100%; display: flex ; justify-content:space-between; align-items:center;">
                                    RTMkit PRO
                                    <a class="link-btn-accent" href="https://support.rometheme.net/docs/romethemekit-pro-for-elementor/migration-v-2-0-0/"
                                        target="_blank">Read This Guide</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
<?php
        endif;
    }

    public function feature_drawer_script()
    {
        if (current_user_can('edit_posts')) {
            wp_enqueue_style('rtmkit-new-features', RTM_KIT_URL . 'assets/css/rtmkit-new-feature.css', [], RTM_KIT_VERSION);
            wp_enqueue_script('rtmkit-new-features', RTM_KIT_URL . 'assets/js/rtmkit-new-feature.js', [], RTM_KIT_VERSION, true);
        }
    }
}
