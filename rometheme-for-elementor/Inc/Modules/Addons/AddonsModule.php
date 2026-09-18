<?php

namespace RTMKit\Modules\Addons;

use RTMKit\Modules\Helper\APIHandlerV2 as APIHandler;
use RTMKit\Modules\Addons\AddonsApi;

class AddonsModule
{
    /**
     * How long a failed lookup is remembered, so a hub that is down does not
     * make every admin request wait for the HTTP timeout.
     */
    private const FAILURE_CACHE = 5 * MINUTE_IN_SECONDS;

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
        $cached = get_transient('rtmkit_addons_list');

        if (is_array($cached)) {
            return $cached;
        }

        $addons = $this->fetch_addons_list();

        if (null === $addons) {
            set_transient('rtmkit_addons_list', [], self::FAILURE_CACHE);
            return [];
        }

        set_transient('rtmkit_addons_list', $addons, DAY_IN_SECONDS);

        return $addons;
    }

    /**
     * The catalogue as last cached, without asking the hub. For callers that
     * must never wait on the network, such as hooks that run while a plugin is
     * being installed or deleted.
     */
    public function cached_addons_list(): array
    {
        $cached = get_transient('rtmkit_addons_list');

        return is_array($cached) ? $cached : [];
    }

    /**
     * Ask the hub for the addon catalogue.
     *
     * @return array|null Null when the hub is unreachable or answers with
     *                    something that is not a list of addons.
     */
    public function fetch_addons_list(): ?array
    {
        $response = APIHandler::instance()->remote('addons/list', [], null, false, true);

        return $this->normalize_addons_list($response);
    }

    /**
     * @param mixed $response
     * @return array|null
     */
    public function normalize_addons_list($response): ?array
    {
        if (is_wp_error($response) || !is_array($response)) {
            return null;
        }

        // The endpoint answers with the list directly or wrapped in `data`.
        if (isset($response['data']) && is_array($response['data'])) {
            $response = $response['data'];
        }

        // An error body decodes to an array too, so make sure every entry
        // actually looks like an addon row before trusting it.
        foreach ($response as $addon) {
            if (!is_array($addon)) {
                return null;
            }
        }

        return array_values($response);
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

    public function get_addons_available(): array
    {
        $payload = get_option('rtm_connector_last_payload', false);
        $features = is_array($payload) ? ($payload['payload']['features'] ?? null) : null;

        if (!is_array($features)) {
            return [];
        }

        return array_column($features, 'pillar');
    }
}
