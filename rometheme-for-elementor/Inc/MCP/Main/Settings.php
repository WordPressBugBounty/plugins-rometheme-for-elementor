<?php
namespace RTMKit\MCP\Main;

final class Settings
{

    public static function init(): void
    {
        add_filter('rtmkit_menus', [self::class, 'add_to_menu']);
        add_action('admin_init', [self::class, 'register']);
    }

    public static function menu(): void
    {
        add_options_page('RTMKit MCP', 'RTMKit MCP', 'manage_options', 'rtmkit-mcp', [self::class, 'render']);
    }

    public static function register(): void
    {
        register_setting('rtmkit_mcp', RTMKIT_OPT_WRITE, [
            'type' => 'boolean',
            'default' => false,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]);

        add_settings_section('rtmkit_mcp_main', '', '__return_false', 'rtmkit-mcp');
        add_settings_field(RTMKIT_OPT_WRITE, 'Write Operations', [self::class, 'field_write'], 'rtmkit-mcp', 'rtmkit_mcp_main');
    }

    public static function field_write(): void
    {
        printf(
            '<label class="text-white"><input type="checkbox" name="%1$s" value="1" %2$s> Allow Write Operations</label>
			<p class="text-muted">Default disabled (read-only). Changes are still limited by the <code>edit_post</code> capability of the connected user.</p>',
            esc_attr(RTMKIT_OPT_WRITE),
            checked((bool) get_option(RTMKIT_OPT_WRITE), true, false)
        );
    }

    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $write_enabled = (bool) get_option(RTMKIT_OPT_WRITE);
        $endpoint = rest_url('mcp/mcp-adapter-default-server');
        ?>
        <div class="px-4 mb-5 rtm-mcp">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div class="d-flex flex-column gap-3">
                        <h1 class="d-flex align-items-center gap-3 m-0">
                            RTMKit MCP
                            <span class="badge new">Alpha</span>
                        </h1>
                        <div class="d-flex align-items-center gap-3">
                            License :
                            <span class="license-status"><?php echo esc_html(rtmkit_license_label()); ?></span>
                        </div>
                        <p class="m-0">Let AI assistants read and build Elementor pages through the Model Context Protocol. This feature is still in development, so results may be imperfect.</p>
                    </div>
                </div>
                <div class="divider"></div>

                <form method="post" action="options.php" class="d-flex flex-column gap-3">
                    <?php
                    settings_fields('rtmkit_mcp');
                    printf(
                        '<input type="hidden" name="_wp_http_referer" value="%s">',
                        esc_url(admin_url('admin.php?page=rtmkit&path=mcp'))
                    );
                    ?>
                    <div class="row row-cols-1 row-cols-xl-2 g-3">
                        <div class="col">
                            <div class="card rounded-4 flex-column gap-3 h-100">
                                <div class="pb-3 border-bottom d-flex align-items-center gap-3">
                                    <i class="rtmicon rtmicon-gear fs-4 accent-color"></i>
                                    <h4 class="m-0">Permissions</h4>
                                </div>
                                <div class="rounded-3 p-3 d-flex align-items-center justify-content-between gap-3 bg-hover-color">
                                    <div class="d-flex flex-column gap-1">
                                        <h6 class="m-0">Allow Write Operations</h6>
                                        <small class="text-muted-color">Off = read-only. When on, changes are still limited by the <code>edit_post</code> capability of the connected user.</small>
                                    </div>
                                    <label class="switch gap-3">
                                        <input type="checkbox" class="switch-input" name="<?php echo esc_attr(RTMKIT_OPT_WRITE); ?>" value="1" <?php checked($write_enabled); ?> hidden>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <div class="mt-auto">
                                    <button type="submit" class="btn btn-accent px-4 py-3 gap-2">
                                        <i class="rtmicon rtmicon-file-circle-check fs-5"></i>
                                        Save Changes
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card rounded-4 flex-column gap-3 h-100">
                                <div class="pb-3 border-bottom d-flex align-items-center gap-3">
                                    <i class="rtmicon rtmicon-link fs-4 accent-color"></i>
                                    <h4 class="m-0">Connection</h4>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <h6 class="m-0">MCP Endpoint</h6>
                                    <?php self::copy_field($endpoint); ?>
                                    <small class="text-muted-color">In Claude, open Settings &rarr; Connectors &rarr; Add custom connector and paste this URL. You will be asked to sign in to this site and approve access. Clients without OAuth support can use a WordPress Application Password instead.</small>
                                </div>
                                <div class="d-flex flex-column gap-2">
                                    <h6 class="m-0">Requirements</h6>
                                    <ul class="list-group list-group-flush">
                                        <?php foreach ([
                                            'WordPress 6.9+ (Abilities API)' => function_exists('wp_register_ability'),
                                            'Elementor' => did_action('elementor/loaded') > 0,
                                        ] as $label => $ok) : ?>
                                            <li class="list-group-item d-flex align-items-center gap-2 py-2">
                                                <span class="list-icon <?php echo $ok ? '' : 'list-icon-danger'; ?>">
                                                    <i class="rtmicon <?php echo $ok ? 'rtmicon-check' : 'rtmicon-xmark'; ?>"></i>
                                                </span>
                                                <?php echo esc_html($label); ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <?php self::render_connected_apps(); ?>
                <?php self::render_oauth_clients(); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Nilai yang perlu disalin user (URL/token), dengan tombol copy di ujung bar.
     * Klik ditangani di assets/js/scripts.js.
     */
    private static function copy_field(string $value): void
    {
        ?>
        <div class="rtm-mcp__copy">
            <code class="rtm-mcp__endpoint"><?php echo esc_html($value); ?></code>
            <button type="button" class="rtm-mcp__copy-btn" data-copy="<?php echo esc_attr($value); ?>" title="Copy" aria-label="Copy">
                <i class="rtmicon rtmicon-clipboard"></i>
            </button>
        </div>
        <?php
    }

    /**
     * Client OAuth manual: untuk klien yang meminta Client ID dan Client Secret
     * karena pendaftaran otomatisnya gagal.
     */
    private static function render_oauth_clients(): void
    {
        $clients = OAuth::get_manual_clients();
        $new = OAuth::pull_new_client();
        ?>
        <div class="card rounded-4 flex-column gap-3">
            <div class="pb-3 border-bottom d-flex align-items-center gap-3">
                <i class="rtmicon rtmicon-key-left fs-4 accent-color"></i>
                <h4 class="m-0">OAuth Clients</h4>
            </div>
            <small class="text-muted-color">Use this only when a client such as Gemini asks for a Client ID and Client Secret. The Redirect URI is the client's callback address, not this site: in Gemini click <strong>Copy redirect URI</strong> in the connector dialog and paste it here. Then enter the generated Client ID and Client Secret back in the client. Claude does not need this.</small>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="d-flex align-items-center gap-2 flex-wrap">
                <input type="hidden" name="action" value="rtmkit_mcp_create_client">
                <?php wp_nonce_field('rtmkit_mcp_create_client'); ?>
                <input type="text" name="client_name" class="form-control rtm-mcp__token-name" placeholder="Client name, e.g. Gemini" maxlength="100">
                <input type="url" name="redirect_uri" class="form-control rtm-mcp__redirect-uri" placeholder="Redirect URI (https://...)" required>
                <button type="submit" class="btn btn-accent px-4 py-2 gap-2">
                    <i class="rtmicon rtmicon-key-right"></i>
                    Create Client
                </button>
            </form>

            <?php if (!empty($new['error'])) : ?>
                <small class="text-danger"><?php echo esc_html($new['error']); ?></small>
            <?php elseif ($new) : ?>
                <div class="rounded-3 p-3 d-flex flex-column gap-2 bg-hover-color">
                    <h6 class="m-0">Your new OAuth client</h6>
                    <small class="text-muted-color">Copy the secret now, it will not be shown again.</small>
                    <small class="text-muted-color">Client ID</small>
                    <?php self::copy_field($new['client_id']); ?>
                    <small class="text-muted-color">Client Secret</small>
                    <?php self::copy_field($new['client_secret']); ?>
                </div>
            <?php endif; ?>

            <?php if ($clients) : ?>
                <div class="divider"></div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($clients as $client_id => $client) : ?>
                        <li class="list-group-item d-flex align-items-center justify-content-between gap-3 py-3">
                            <div class="d-flex flex-column gap-1">
                                <h6 class="m-0"><?php echo esc_html($client['client_name']); ?></h6>
                                <small class="text-muted-color"><?php echo esc_html($client_id . ' · ' . implode(', ', $client['redirect_uris'])); ?></small>
                            </div>
                            <a href="<?php echo esc_url(OAuth::delete_client_url($client_id)); ?>" class="btn btn-secondary px-3 py-2" onclick="return confirm('Delete this client? Apps connected through it will be disconnected.');">Delete</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Daftar klien yang sudah disetujui lewat OAuth, dengan tombol cabut akses.
     */
    private static function render_connected_apps(): void
    {
        $grants = OAuth::get_grants();
        uasort($grants, fn($a, $b) => $b['created'] <=> $a['created']);
        $format = get_option('date_format') . ' ' . get_option('time_format');
        $new_token = OAuth::pull_new_token();
        ?>
        <div class="card rounded-4 flex-column gap-3">
            <div class="pb-3 border-bottom d-flex align-items-center gap-3">
                <i class="rtmicon rtmicon-shield-check fs-4 accent-color"></i>
                <h4 class="m-0">Connected Apps &amp; Access Tokens</h4>
            </div>

            <div class="d-flex flex-column gap-2">
                <h6 class="m-0">Generate Access Token</h6>
                <small class="text-muted-color">For clients that cannot sign in with OAuth. The token acts as your account on the MCP endpoint only, and stays valid until revoked.</small>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="d-flex align-items-center gap-2 flex-wrap">
                    <input type="hidden" name="action" value="rtmkit_mcp_create_token">
                    <?php wp_nonce_field('rtmkit_mcp_create_token'); ?>
                    <input type="text" name="token_name" class="form-control rtm-mcp__token-name" placeholder="Token name, e.g. Claude" maxlength="100">
                    <button type="submit" class="btn btn-accent px-4 py-2 gap-2">
                        <i class="rtmicon rtmicon-key-right"></i>
                        Generate Token
                    </button>
                </form>
            </div>

            <?php if ($new_token) : ?>
                <div class="rounded-3 p-3 d-flex flex-column gap-2 bg-hover-color">
                    <h6 class="m-0">Your new access token</h6>
                    <small class="text-muted-color">Copy it now, it will not be shown again. Paste the Connector URL into your MCP client, or send the token as an <code>Authorization: Bearer</code> header.</small>
                    <small class="text-muted-color">Connector URL</small>
                    <?php self::copy_field(OAuth::connector_url($new_token)); ?>
                    <small class="text-muted-color">Token</small>
                    <?php self::copy_field($new_token); ?>
                </div>
            <?php endif; ?>

            <div class="divider"></div>

            <?php if (!$grants) : ?>
                <small class="text-muted-color">No apps or access tokens yet. Apps appear here after a user approves access.</small>
            <?php else : ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($grants as $grant_id => $grant) :
                        $user = get_userdata($grant['user_id']);
                        ?>
                        <li class="list-group-item d-flex align-items-center justify-content-between gap-3 py-3">
                            <div class="d-flex flex-column gap-1">
                                <h6 class="m-0 d-flex align-items-center gap-2">
                                    <?php echo esc_html($grant['client_name']); ?>
                                    <?php if ('personal' === ($grant['type'] ?? '')) : ?>
                                        <span class="badge new">Access Token</span>
                                    <?php endif; ?>
                                </h6>
                                <small class="text-muted-color">
                                    <?php
                                    printf(
                                        'User: %1$s &middot; Connected: %2$s &middot; Last used: %3$s',
                                        esc_html($user ? $user->display_name : '#' . $grant['user_id']),
                                        esc_html(wp_date($format, $grant['created'])),
                                        esc_html($grant['last_used'] ? wp_date($format, $grant['last_used']) : '—')
                                    );
                                    ?>
                                </small>
                            </div>
                            <a href="<?php echo esc_url(OAuth::revoke_url($grant_id)); ?>" class="btn btn-secondary px-3 py-2" onclick="return confirm('Revoke access for this app?');">Revoke</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function add_to_menu($menus)
    {
        $menus['settings']['mcp'] = [
            'title' => __('MCP', 'rometheme-for-elementor'),
            'capability' => 'manage_options',
            'menu_slug' => 'rtmkit&path=mcp',
            'function' => [self::class, 'rtmkit_root_page'],
            'render_view' => [self::class, 'render'],
        ];

        return $menus;
    }
}