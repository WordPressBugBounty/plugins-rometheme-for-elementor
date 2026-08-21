<?php

namespace RTMKit\Modules\Addons;

use RTMKit\Modules\Helper\APIHandlerV2 as APIHandler;
use RTMKit\Modules\Addons\AddonsApi;

class AddonsModule
{
    private static $instance;

    public static function instance()
    {

        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function init()
    {
        add_filter('rtmkit_menus', [$this, 'add_to_menu']);
        AddonsApi::instance()->init();
    }

    public function get_addons_list(): array
    {
        $transient_addons_list = get_transient('rtmkit_addons_list');
        if ($transient_addons_list === false) {
            $addons = APIHandler::instance()->remote('addons/list', [], null, false, true);
            set_transient('rtmkit_addons_list', $addons, DAY_IN_SECONDS);
            return $addons;
        }
        return $transient_addons_list;
    }

    public function add_to_menu($menus)
    {
        $menus['features']['addons'] = [
            'title' => __('Addons', 'rometheme-for-elementor'),
            'capability' => 'manage_options',
            'menu_slug' => 'rtmkit&path=addons',
            'function' => [$this, 'rtmkit_root_page'],
            'render_view' => RTM_KIT_DIR . 'views/addons.php',
        ];

        return $menus;
    }

    public function get_addons_available()
    {
        $payload =  get_option('rtm_connector_last_payload', false);
        if ($payload == false) {
            return [];
        }
        $addons =  $payload['payload']['features'];
        $slugs = array_column($addons, 'pillar');
        return $slugs;
    }
}
