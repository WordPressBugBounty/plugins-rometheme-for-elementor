<?php

namespace RTMKit\MCP\Main;

if (!defined('ABSPATH')) exit;

/**
 * Server OAuth 2.1 untuk endpoint MCP (sesuai spesifikasi otorisasi MCP).
 *
 * Membuat situs ini bisa ditambahkan sebagai "custom connector" di Claude dan
 * klien MCP lain: klien menemukan server login lewat metadata .well-known,
 * mendaftar sendiri (Dynamic Client Registration), lalu user login ke wp-admin
 * dan menyetujui akses. Token hanya berlaku untuk route REST /mcp/.
 *
 * - Metadata   : /.well-known/oauth-protected-resource, /.well-known/oauth-authorization-server
 *                (plus salinan di REST karena situs di subfolder tidak memiliki /.well-known di root)
 * - Authorize  : wp-login.php?action=rtmkit_mcp_authorize
 * - Register, token, revoke : REST rtmkit/v1/oauth/*
 */
final class OAuth
{
    const REST_NAMESPACE = 'rtmkit/v1';
    const LOGIN_ACTION = 'rtmkit_mcp_authorize';
    const OPT_CLIENTS = 'rtmkit_mcp_oauth_clients';
    const OPT_GRANTS = 'rtmkit_mcp_oauth_grants';
    const SCOPE = 'mcp';
    const QUERY_PARAM = 'rtmkit_token';

    const CODE_TTL = 600;
    const ACCESS_TTL = HOUR_IN_SECONDS;
    const REFRESH_TTL = 30 * DAY_IN_SECONDS;
    const MAX_CLIENTS = 100;

    /** @var bool Klien mengirim Bearer token tapi tokennya tidak valid. */
    private static $invalid_token = false;

    /**
     * Dipanggil saat file plugin dimuat: filter determine_current_user harus
     * terpasang sebelum WordPress menentukan user (kses_init di hook init).
     */
    public static function init(): void
    {
        add_filter('determine_current_user', [self::class, 'authenticate'], 30);
        add_filter('application_password_is_api_request', [self::class, 'skip_app_password_on_oauth'], 20);
        add_filter('rest_post_dispatch', [self::class, 'add_challenge_header'], 10, 3);
        add_filter('rest_exposed_cors_headers', [self::class, 'expose_cors_headers']);
        add_action('rest_api_init', [self::class, 'register_routes']);
        add_action('init', [self::class, 'serve_well_known'], 0);
        add_action('login_form_' . self::LOGIN_ACTION, [self::class, 'authorize']);
        add_action('admin_post_rtmkit_mcp_revoke', [self::class, 'admin_revoke']);
        add_action('admin_post_rtmkit_mcp_create_token', [self::class, 'admin_create_token']);
        add_action('admin_post_rtmkit_mcp_create_client', [self::class, 'admin_create_client']);
        add_action('admin_post_rtmkit_mcp_delete_client', [self::class, 'admin_delete_client']);
    }

    // ---------------------------------------------------------------------
    // URL & metadata
    // ---------------------------------------------------------------------

    public static function resource_url(): string
    {
        return untrailingslashit(rest_url('mcp/mcp-adapter-default-server'));
    }

    public static function issuer(): string
    {
        return untrailingslashit(home_url());
    }

    private static function authorize_url(): string
    {
        return site_url('wp-login.php?action=' . self::LOGIN_ACTION, 'login_post');
    }

    public static function protected_resource_metadata(): array
    {
        return [
            'resource' => self::resource_url(),
            'authorization_servers' => [self::issuer()],
            'bearer_methods_supported' => ['header'],
            'scopes_supported' => [self::SCOPE],
            'resource_name' => wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES) . ' (RTMKit MCP)',
        ];
    }

    public static function authorization_server_metadata(): array
    {
        return [
            'issuer' => self::issuer(),
            'authorization_endpoint' => self::authorize_url(),
            'token_endpoint' => rest_url(self::REST_NAMESPACE . '/oauth/token'),
            'registration_endpoint' => rest_url(self::REST_NAMESPACE . '/oauth/register'),
            'revocation_endpoint' => rest_url(self::REST_NAMESPACE . '/oauth/revoke'),
            'scopes_supported' => [self::SCOPE],
            'response_types_supported' => ['code'],
            'response_modes_supported' => ['query'],
            'grant_types_supported' => ['authorization_code', 'refresh_token'],
            'token_endpoint_auth_methods_supported' => ['none', 'client_secret_post', 'client_secret_basic'],
            'revocation_endpoint_auth_methods_supported' => ['none', 'client_secret_post', 'client_secret_basic'],
            'code_challenge_methods_supported' => ['S256'],
            'authorization_response_iss_parameter_supported' => true,
        ];
    }

    /**
     * Melayani /.well-known/* di bawah home URL. Untuk situs di root domain ini
     * juga mencakup /.well-known/oauth-authorization-server tanpa sufiks path.
     */
    public static function serve_well_known(): void
    {
        $path = (string) wp_parse_url(isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '', PHP_URL_PATH);

        if (!preg_match('#/\.well-known/(oauth-protected-resource|oauth-authorization-server|openid-configuration)(/|$)#', $path, $m)) {
            return;
        }

        $data = 'oauth-protected-resource' === $m[1]
            ? self::protected_resource_metadata()
            : self::authorization_server_metadata();

        status_header(200);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Cache-Control: public, max-age=3600');
        echo wp_json_encode($data, JSON_UNESCAPED_SLASHES);
        exit;
    }

    // ---------------------------------------------------------------------
    // Bearer token pada route /mcp/
    // ---------------------------------------------------------------------

    public static function authenticate($user_id)
    {
        if (!empty($user_id)) {
            return $user_id;
        }

        $token = self::request_token();
        if ('' === $token || !self::is_rest_request_to('mcp/')) {
            return $user_id;
        }

        $token_user = self::validate_access_token($token);
        if (!$token_user) {
            self::$invalid_token = true;
            return $user_id;
        }

        return $token_user;
    }

    /**
     * Header Basic di endpoint token berisi client_id:client_secret, bukan
     * Application Password; jangan biarkan WordPress menolaknya sebagai login gagal.
     */
    public static function skip_app_password_on_oauth($is_api_request)
    {
        return self::is_rest_request_to(self::REST_NAMESPACE . '/oauth/') ? false : $is_api_request;
    }

    /**
     * 401 dari endpoint MCP menyertakan alamat metadata supaya klien tahu
     * harus login ke mana (RFC 9728).
     */
    public static function add_challenge_header($response, $server, $request)
    {
        if (!$response instanceof \WP_HTTP_Response || 401 !== $response->get_status()) {
            return $response;
        }

        if (0 !== strpos((string) $request->get_route(), '/mcp/')) {
            return $response;
        }

        $challenge = sprintf(
            'Bearer resource_metadata="%s", scope="%s"',
            rest_url(self::REST_NAMESPACE . '/oauth/protected-resource'),
            self::SCOPE
        );
        if (self::$invalid_token) {
            $challenge .= ', error="invalid_token"';
        }

        $response->header('WWW-Authenticate', $challenge);
        return $response;
    }

    public static function expose_cors_headers($headers)
    {
        $headers[] = 'WWW-Authenticate';
        return $headers;
    }

    private static function validate_access_token(string $token)
    {
        list($grant_id, $secret) = array_pad(explode('.', $token, 2), 2, '');
        $grants = self::get_grants();

        if ('' === $secret || empty($grants[$grant_id])) {
            return 0;
        }

        $grant = $grants[$grant_id];
        if ($grant['access_expires'] < time() || !hash_equals($grant['access_hash'], self::hash($secret))) {
            return 0;
        }

        if (!get_userdata($grant['user_id'])) {
            return 0;
        }

        // Cukup untuk ditampilkan di Settings; tidak perlu menulis option tiap request.
        if (time() - (int) ($grant['last_used'] ?? 0) > 5 * MINUTE_IN_SECONDS) {
            $grants[$grant_id]['last_used'] = time();
            self::save_grants($grants);
        }

        return (int) $grant['user_id'];
    }

    // ---------------------------------------------------------------------
    // REST: register, token, revoke, metadata
    // ---------------------------------------------------------------------

    public static function register_routes(): void
    {
        $public = '__return_true';

        register_rest_route(self::REST_NAMESPACE, '/oauth/protected-resource', [
            'methods' => 'GET',
            'callback' => fn() => self::json(self::protected_resource_metadata()),
            'permission_callback' => $public,
        ]);
        register_rest_route(self::REST_NAMESPACE, '/oauth/authorization-server', [
            'methods' => 'GET',
            'callback' => fn() => self::json(self::authorization_server_metadata()),
            'permission_callback' => $public,
        ]);
        register_rest_route(self::REST_NAMESPACE, '/oauth/register', [
            'methods' => 'POST',
            'callback' => [self::class, 'register_client'],
            'permission_callback' => $public,
        ]);
        register_rest_route(self::REST_NAMESPACE, '/oauth/token', [
            'methods' => 'POST',
            'callback' => [self::class, 'token'],
            'permission_callback' => $public,
        ]);
        register_rest_route(self::REST_NAMESPACE, '/oauth/revoke', [
            'methods' => 'POST',
            'callback' => [self::class, 'revoke'],
            'permission_callback' => $public,
        ]);
    }

    /**
     * Dynamic Client Registration (RFC 7591).
     */
    public static function register_client(\WP_REST_Request $request)
    {
        $body = $request->get_json_params();
        if (!is_array($body)) {
            $body = $request->get_body_params();
        }

        $redirect_uris = isset($body['redirect_uris']) && is_array($body['redirect_uris']) ? array_values($body['redirect_uris']) : [];
        if (!$redirect_uris || count($redirect_uris) > 10) {
            return self::error('invalid_redirect_uri', 'redirect_uris is required.');
        }
        foreach ($redirect_uris as $uri) {
            if (!is_string($uri) || !self::is_valid_redirect_uri($uri)) {
                return self::error('invalid_redirect_uri', 'Unsupported redirect URI.');
            }
        }

        $auth_method = isset($body['token_endpoint_auth_method']) ? (string) $body['token_endpoint_auth_method'] : 'none';
        if (!in_array($auth_method, ['none', 'client_secret_post', 'client_secret_basic'], true)) {
            $auth_method = 'none';
        }

        $name = isset($body['client_name']) ? sanitize_text_field((string) $body['client_name']) : '';
        $client_id = 'rtmk_' . bin2hex(random_bytes(16));
        $client = [
            'client_name' => mb_substr($name !== '' ? $name : 'MCP Client', 0, 100),
            'redirect_uris' => $redirect_uris,
            'auth_method' => $auth_method,
            'secret_hash' => '',
            'created' => time(),
        ];

        $response = [
            'client_id' => $client_id,
            'client_id_issued_at' => $client['created'],
            'client_name' => $client['client_name'],
            'redirect_uris' => $redirect_uris,
            'grant_types' => ['authorization_code', 'refresh_token'],
            'response_types' => ['code'],
            'token_endpoint_auth_method' => $auth_method,
            'scope' => self::SCOPE,
        ];

        if ('none' !== $auth_method) {
            $secret = bin2hex(random_bytes(32));
            $client['secret_hash'] = self::hash($secret);
            $response['client_secret'] = $secret;
            $response['client_secret_expires_at'] = 0;
        }

        $clients = self::get_clients();
        $clients[$client_id] = $client;
        self::save_clients($clients);

        return self::json($response, 201);
    }

    public static function token(\WP_REST_Request $request)
    {
        $client_id = self::authenticate_client($request);
        if (is_wp_error($client_id)) {
            return self::error('invalid_client', $client_id->get_error_message(), 401);
        }

        $grant_type = (string) $request->get_param('grant_type');

        if ('authorization_code' === $grant_type) {
            return self::exchange_code($request, $client_id);
        }
        if ('refresh_token' === $grant_type) {
            return self::refresh($request, $client_id);
        }

        return self::error('unsupported_grant_type', 'Unsupported grant_type.');
    }

    private static function exchange_code(\WP_REST_Request $request, string $client_id)
    {
        $code = (string) $request->get_param('code');
        $key = 'rtmkit_mcp_code_' . self::hash($code);
        $data = $code !== '' ? get_transient($key) : false;

        // Kode hanya boleh dipakai sekali, berhasil ataupun gagal.
        delete_transient($key);

        if (!is_array($data) || $data['client_id'] !== $client_id) {
            return self::error('invalid_grant', 'Invalid or expired authorization code.');
        }
        if ((string) $request->get_param('redirect_uri') !== $data['redirect_uri']) {
            return self::error('invalid_grant', 'redirect_uri does not match.');
        }

        $verifier = (string) $request->get_param('code_verifier');
        if (!preg_match('/^[A-Za-z0-9\-._~]{43,128}$/', $verifier) || !hash_equals($data['code_challenge'], self::pkce_challenge($verifier))) {
            return self::error('invalid_grant', 'PKCE verification failed.');
        }

        $user = get_userdata($data['user_id']);
        if (!$user || !user_can($user, 'edit_posts')) {
            return self::error('invalid_grant', 'User is no longer allowed to connect.');
        }

        $clients = self::get_clients();
        $grant_id = bin2hex(random_bytes(8));
        $grants = self::get_grants();
        $grants[$grant_id] = [
            'user_id' => $user->ID,
            'client_id' => $client_id,
            'client_name' => $clients[$client_id]['client_name'] ?? 'MCP Client',
            'created' => time(),
            'last_used' => 0,
        ];

        return self::issue_tokens($grants, $grant_id);
    }

    private static function refresh(\WP_REST_Request $request, string $client_id)
    {
        list($grant_id, $secret) = array_pad(explode('.', (string) $request->get_param('refresh_token'), 2), 2, '');
        $grants = self::get_grants();
        $grant = $grants[$grant_id] ?? null;

        if (
            !$grant || '' === $secret
            || $grant['client_id'] !== $client_id
            || $grant['refresh_expires'] < time()
            || !hash_equals($grant['refresh_hash'], self::hash($secret))
        ) {
            return self::error('invalid_grant', 'Invalid or expired refresh token.');
        }

        $user = get_userdata($grant['user_id']);
        if (!$user || !user_can($user, 'edit_posts')) {
            unset($grants[$grant_id]);
            self::save_grants($grants);
            return self::error('invalid_grant', 'User is no longer allowed to connect.');
        }

        // Rotasi: refresh token lama langsung tidak berlaku.
        return self::issue_tokens($grants, $grant_id);
    }

    private static function issue_tokens(array $grants, string $grant_id)
    {
        $access = bin2hex(random_bytes(32));
        $refresh = bin2hex(random_bytes(32));

        $grants[$grant_id]['access_hash'] = self::hash($access);
        $grants[$grant_id]['access_expires'] = time() + self::ACCESS_TTL;
        $grants[$grant_id]['refresh_hash'] = self::hash($refresh);
        $grants[$grant_id]['refresh_expires'] = time() + self::REFRESH_TTL;
        self::save_grants($grants);

        return self::json([
            'access_token' => $grant_id . '.' . $access,
            'token_type' => 'Bearer',
            'expires_in' => self::ACCESS_TTL,
            'refresh_token' => $grant_id . '.' . $refresh,
            'scope' => self::SCOPE,
        ]);
    }

    /**
     * Token Revocation (RFC 7009): selalu 200, apa pun tokennya.
     */
    public static function revoke(\WP_REST_Request $request)
    {
        $client_id = self::authenticate_client($request);
        if (is_wp_error($client_id)) {
            return self::error('invalid_client', $client_id->get_error_message(), 401);
        }

        list($grant_id, $secret) = array_pad(explode('.', (string) $request->get_param('token'), 2), 2, '');
        $grants = self::get_grants();
        $grant = $grants[$grant_id] ?? null;

        if ($grant && $grant['client_id'] === $client_id && '' !== $secret) {
            $hash = self::hash($secret);
            if (hash_equals($grant['access_hash'], $hash) || hash_equals($grant['refresh_hash'], $hash)) {
                unset($grants[$grant_id]);
                self::save_grants($grants);
            }
        }

        return self::json([]);
    }

    /**
     * @return string|\WP_Error client_id yang sudah diverifikasi.
     */
    private static function authenticate_client(\WP_REST_Request $request)
    {
        $client_id = (string) $request->get_param('client_id');
        $secret = (string) $request->get_param('client_secret');

        $header = self::authorization_header();
        if (0 === stripos($header, 'Basic ')) {
            $decoded = base64_decode(substr($header, 6), true);
            if (false !== $decoded && false !== strpos($decoded, ':')) {
                list($client_id, $secret) = array_map('rawurldecode', explode(':', $decoded, 2));
            }
        }

        $client = self::get_clients()[$client_id] ?? null;
        if (!$client) {
            return new \WP_Error('invalid_client', 'Unknown client.');
        }
        if ('none' !== $client['auth_method'] && ('' === $secret || !hash_equals($client['secret_hash'], self::hash($secret)))) {
            return new \WP_Error('invalid_client', 'Client authentication failed.');
        }

        return $client_id;
    }

    // ---------------------------------------------------------------------
    // Authorization endpoint (halaman persetujuan di wp-login.php)
    // ---------------------------------------------------------------------

    public static function authorize(): void
    {
        $params = [];
        foreach (['response_type', 'client_id', 'redirect_uri', 'state', 'code_challenge', 'code_challenge_method', 'scope', 'resource'] as $key) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- parameter OAuth dari klien.
            $params[$key] = isset($_GET[$key]) ? (string) wp_unslash($_GET[$key]) : '';
        }

        $self_url = self::authorize_url() . '&' . http_build_query(array_filter($params, 'strlen'));
        $client = self::get_clients()[$params['client_id']] ?? null;

        $step = 'POST' === ($_SERVER['REQUEST_METHOD'] ?? '') ? (empty($_POST['approve']) ? 'deny' : 'allow') : 'open';
        self::add_log(
            'AUTHORIZE ' . $step,
            200,
            is_user_logged_in() ? 'cookie' : 'none',
            $client ? $client['client_name'] . ' → ' . (wp_parse_url($params['redirect_uri'], PHP_URL_HOST) ?: '?') : 'unknown client'
        );

        if (!$client) {
            self::render_error(__('This application is not registered. Remove the connector and add it again.', 'rometheme-for-elementor'));
        }

        if ('' === $params['redirect_uri'] && 1 === count($client['redirect_uris'])) {
            $params['redirect_uri'] = $client['redirect_uris'][0];
        }

        // Redirect URI yang tidak terdaftar tidak boleh dipakai, bahkan untuk melaporkan error.
        if (!in_array($params['redirect_uri'], $client['redirect_uris'], true)) {
            self::render_error(__('The redirect address does not match this application.', 'rometheme-for-elementor'));
        }

        if ('code' !== $params['response_type']) {
            self::redirect_with($params, ['error' => 'unsupported_response_type']);
        }
        if ('' === $params['code_challenge'] || 'S256' !== $params['code_challenge_method']) {
            self::redirect_with($params, ['error' => 'invalid_request', 'error_description' => 'PKCE with S256 is required.']);
        }

        if (!is_user_logged_in()) {
            wp_safe_redirect(wp_login_url($self_url));
            exit;
        }

        $user = wp_get_current_user();
        if (!user_can($user, 'edit_posts')) {
            self::render_error(__('Your account is not allowed to connect AI assistants to this site. An account that can edit posts is required.', 'rometheme-for-elementor'));
        }

        if ('POST' === ($_SERVER['REQUEST_METHOD'] ?? '')) {
            check_admin_referer('rtmkit_mcp_authorize_' . $params['client_id']);

            if (empty($_POST['approve'])) {
                self::redirect_with($params, ['error' => 'access_denied']);
            }

            $code = bin2hex(random_bytes(32));
            set_transient('rtmkit_mcp_code_' . self::hash($code), [
                'client_id' => $params['client_id'],
                'user_id' => $user->ID,
                'redirect_uri' => $params['redirect_uri'],
                'code_challenge' => $params['code_challenge'],
            ], self::CODE_TTL);

            self::redirect_with($params, ['code' => $code]);
        }

        self::render_consent($client, $params, $self_url, $user);
    }

    private static function render_consent(array $client, array $params, string $self_url, \WP_User $user): void
    {
        $redirect_host = wp_parse_url($params['redirect_uri'], PHP_URL_HOST) ?: $params['redirect_uri'];
        $abilities = [
            __('Read your Elementor pages and RTMKit widgets', 'rometheme-for-elementor'),
        ];
        if (get_option(RTMKIT_OPT_WRITE)) {
            $abilities[] = __('Create and edit Elementor pages with RTMKit widgets', 'rometheme-for-elementor');
        }

        login_header(__('Authorize MCP access', 'rometheme-for-elementor'));
        ?>
        <style>
            .rtmkit-oauth h2 { font-size: 18px; line-height: 1.4; margin: 0 0 12px; }
            .rtmkit-oauth ul { list-style: disc; margin: 0 0 16px 18px; }
            .rtmkit-oauth li { margin-bottom: 6px; }
            .rtmkit-oauth .rtmkit-oauth__meta { color: #646970; font-size: 12px; margin-bottom: 16px; }
            .rtmkit-oauth .submit { display: flex; gap: 8px; justify-content: flex-end; }
            .rtmkit-oauth .submit .button { float: none; }
        </style>
        <form class="rtmkit-oauth" method="post" action="<?php echo esc_url($self_url); ?>">
            <h2>
                <?php
                printf(
                    /* translators: 1: client name, 2: site name */
                    esc_html__('%1$s wants to connect to %2$s', 'rometheme-for-elementor'),
                    '<strong>' . esc_html($client['client_name']) . '</strong>',
                    '<strong>' . esc_html(get_bloginfo('name')) . '</strong>'
                );
                ?>
            </h2>
            <p><?php esc_html_e('It will be able to:', 'rometheme-for-elementor'); ?></p>
            <ul>
                <?php foreach ($abilities as $ability) : ?>
                    <li><?php echo esc_html($ability); ?></li>
                <?php endforeach; ?>
            </ul>
            <p class="rtmkit-oauth__meta">
                <?php
                printf(
                    /* translators: 1: user display name, 2: redirect host */
                    esc_html__('Actions run as %1$s and are limited to what this account can do. After approving you will be sent to %2$s.', 'rometheme-for-elementor'),
                    '<strong>' . esc_html($user->display_name) . '</strong>',
                    '<strong>' . esc_html($redirect_host) . '</strong>'
                );
                ?>
                <a href="<?php echo esc_url(wp_logout_url($self_url)); ?>"><?php esc_html_e('Not you?', 'rometheme-for-elementor'); ?></a>
            </p>
            <?php wp_nonce_field('rtmkit_mcp_authorize_' . $params['client_id']); ?>
            <p class="submit">
                <button type="submit" name="deny" value="1" class="button button-large"><?php esc_html_e('Deny', 'rometheme-for-elementor'); ?></button>
                <button type="submit" name="approve" value="1" class="button button-primary button-large"><?php esc_html_e('Allow', 'rometheme-for-elementor'); ?></button>
            </p>
        </form>
        <?php
        login_footer();
        exit;
    }

    private static function render_error(string $message): void
    {
        login_header(__('Authorization error', 'rometheme-for-elementor'), '', new \WP_Error('rtmkit_oauth', $message));
        login_footer();
        exit;
    }

    private static function redirect_with(array $params, array $args): void
    {
        $args['iss'] = self::issuer();
        if ('' !== $params['state']) {
            $args['state'] = $params['state'];
        }

        $separator = false === strpos($params['redirect_uri'], '?') ? '?' : '&';
        // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- redirect_uri sudah dicocokkan dengan data klien.
        wp_redirect($params['redirect_uri'] . $separator . http_build_query($args));
        exit;
    }

    // ---------------------------------------------------------------------
    // Admin: cabut akses
    // ---------------------------------------------------------------------

    public static function admin_revoke(): void
    {
        $grant_id = isset($_GET['grant']) ? sanitize_key(wp_unslash($_GET['grant'])) : '';

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Access denied.', 'rometheme-for-elementor'), 403);
        }
        check_admin_referer('rtmkit_mcp_revoke_' . $grant_id);

        $grants = self::get_grants();
        unset($grants[$grant_id]);
        self::save_grants($grants);

        wp_safe_redirect(admin_url('admin.php?page=rtmkit&path=mcp'));
        exit;
    }

    public static function admin_create_token(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Access denied.', 'rometheme-for-elementor'), 403);
        }
        check_admin_referer('rtmkit_mcp_create_token');

        $name = isset($_POST['token_name']) ? sanitize_text_field(wp_unslash($_POST['token_name'])) : '';
        $token = self::create_personal_token(get_current_user_id(), $name !== '' ? $name : 'Access token');

        // Token hanya ditampilkan sekali, di halaman berikutnya (lihat pull_new_token).
        set_transient('rtmkit_mcp_new_token_' . get_current_user_id(), $token, 5 * MINUTE_IN_SECONDS);

        wp_safe_redirect(admin_url('admin.php?page=rtmkit&path=mcp'));
        exit;
    }

    /**
     * Token pribadi tanpa alur OAuth: berlaku sampai dicabut, tanpa refresh token.
     */
    public static function create_personal_token(int $user_id, string $name): string
    {
        $grant_id = bin2hex(random_bytes(8));
        $secret = bin2hex(random_bytes(32));

        $grants = self::get_grants();
        $grants[$grant_id] = [
            'type' => 'personal',
            'user_id' => $user_id,
            'client_id' => '',
            'client_name' => mb_substr($name, 0, 100),
            'created' => time(),
            'last_used' => 0,
            'access_hash' => self::hash($secret),
            'access_expires' => PHP_INT_MAX,
            'refresh_hash' => '',
            'refresh_expires' => PHP_INT_MAX,
        ];
        self::save_grants($grants);

        return $grant_id . '.' . $secret;
    }

    public static function pull_new_token(): string
    {
        $key = 'rtmkit_mcp_new_token_' . get_current_user_id();
        $token = get_transient($key);
        delete_transient($key);

        return is_string($token) ? $token : '';
    }

    /**
     * Client OAuth yang dibuat admin, untuk klien yang meminta Client ID/Secret
     * karena pendaftaran otomatisnya gagal (mis. Gemini).
     */
    public static function admin_create_client(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Access denied.', 'rometheme-for-elementor'), 403);
        }
        check_admin_referer('rtmkit_mcp_create_client');

        $name = isset($_POST['client_name']) ? sanitize_text_field(wp_unslash($_POST['client_name'])) : '';
        $redirect_uri = isset($_POST['redirect_uri']) ? trim((string) wp_unslash($_POST['redirect_uri'])) : '';
        $key = 'rtmkit_mcp_new_client_' . get_current_user_id();

        if (!self::is_valid_redirect_uri($redirect_uri)) {
            set_transient($key, ['error' => __('Invalid redirect URI. Use the HTTPS address copied from your client.', 'rometheme-for-elementor')], 5 * MINUTE_IN_SECONDS);
        } elseif (strtolower((string) wp_parse_url($redirect_uri, PHP_URL_HOST)) === strtolower((string) wp_parse_url(home_url(), PHP_URL_HOST))) {
            // Kesalahan umum: mengisi alamat situs ini. Kode otorisasi harus kembali
            // ke klien (Gemini, dll.), bukan ke situs WordPress.
            set_transient($key, ['error' => __('The redirect URI must be the callback address of your AI client (for Gemini: click "Copy redirect URI" in its connector dialog), not this site\'s address.', 'rometheme-for-elementor')], 5 * MINUTE_IN_SECONDS);
        } else {
            $client_id = 'rtmk_' . bin2hex(random_bytes(16));
            $secret = bin2hex(random_bytes(32));

            $clients = self::get_clients();
            $clients[$client_id] = [
                'client_name' => mb_substr($name !== '' ? $name : 'MCP Client', 0, 100),
                'redirect_uris' => [$redirect_uri],
                'auth_method' => 'client_secret_post',
                'secret_hash' => self::hash($secret),
                'created' => time(),
                'manual' => true,
            ];
            self::save_clients($clients);

            set_transient($key, ['client_id' => $client_id, 'client_secret' => $secret], 5 * MINUTE_IN_SECONDS);
        }

        wp_safe_redirect(admin_url('admin.php?page=rtmkit&path=mcp'));
        exit;
    }

    public static function admin_delete_client(): void
    {
        $client_id = isset($_GET['client']) ? sanitize_key(wp_unslash($_GET['client'])) : '';

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Access denied.', 'rometheme-for-elementor'), 403);
        }
        check_admin_referer('rtmkit_mcp_delete_client_' . $client_id);

        $clients = self::get_clients();
        unset($clients[$client_id]);
        update_option(self::OPT_CLIENTS, $clients, false);

        // Koneksi yang dibuat lewat client ini ikut dicabut.
        $grants = array_filter(self::get_grants(), fn($g) => ($g['client_id'] ?? '') !== $client_id);
        self::save_grants($grants);

        wp_safe_redirect(admin_url('admin.php?page=rtmkit&path=mcp'));
        exit;
    }

    public static function pull_new_client(): array
    {
        $key = 'rtmkit_mcp_new_client_' . get_current_user_id();
        $data = get_transient($key);
        delete_transient($key);

        return is_array($data) ? $data : [];
    }

    public static function get_manual_clients(): array
    {
        return array_filter(self::get_clients(), fn($c) => !empty($c['manual']));
    }

    public static function delete_client_url(string $client_id): string
    {
        return wp_nonce_url(
            admin_url('admin-post.php?action=rtmkit_mcp_delete_client&client=' . rawurlencode($client_id)),
            'rtmkit_mcp_delete_client_' . $client_id
        );
    }

    public static function connector_url(string $token): string
    {
        return add_query_arg(self::QUERY_PARAM, $token, self::resource_url());
    }

    public static function revoke_url(string $grant_id): string
    {
        return wp_nonce_url(
            admin_url('admin-post.php?action=rtmkit_mcp_revoke&grant=' . rawurlencode($grant_id)),
            'rtmkit_mcp_revoke_' . $grant_id
        );
    }

    // ---------------------------------------------------------------------
    // Penyimpanan
    // ---------------------------------------------------------------------

    private static function get_clients(): array
    {
        $clients = get_option(self::OPT_CLIENTS, []);
        return is_array($clients) ? $clients : [];
    }

    /**
     * Registrasi bersifat publik, jadi jumlah klien dibatasi. Yang dibuang lebih
     * dulu adalah klien tertua yang tidak memiliki koneksi aktif.
     */
    private static function save_clients(array $clients): void
    {
        if (count($clients) > self::MAX_CLIENTS) {
            $in_use = array_flip(array_column(self::get_grants(), 'client_id'));
            uasort($clients, fn($a, $b) => $a['created'] <=> $b['created']);

            foreach (array_keys($clients) as $id) {
                if (count($clients) <= self::MAX_CLIENTS) {
                    break;
                }
                if (!isset($in_use[$id]) && empty($clients[$id]['manual'])) {
                    unset($clients[$id]);
                }
            }
        }

        update_option(self::OPT_CLIENTS, $clients, false);
    }

    public static function get_grants(): array
    {
        $grants = get_option(self::OPT_GRANTS, []);
        return is_array($grants) ? $grants : [];
    }

    private static function save_grants(array $grants): void
    {
        $now = time();
        $grants = array_filter($grants, fn($g) => ($g['refresh_expires'] ?? 0) > $now);
        update_option(self::OPT_GRANTS, $grants, false);
    }

    // ---------------------------------------------------------------------
    // Helper
    // ---------------------------------------------------------------------

    private static function is_valid_redirect_uri(string $uri): bool
    {
        $parts = wp_parse_url($uri);
        if (!$parts || empty($parts['scheme']) || isset($parts['fragment'])) {
            return false;
        }

        $scheme = strtolower($parts['scheme']);
        if ('https' === $scheme) {
            return !empty($parts['host']);
        }
        if ('http' === $scheme) {
            return in_array(strtolower($parts['host'] ?? ''), ['localhost', '127.0.0.1', '[::1]'], true);
        }

        // Skema khusus aplikasi desktop (mis. cursor://), kecuali yang berbahaya.
        return !in_array($scheme, ['javascript', 'data', 'vbscript', 'file'], true);
    }

    /**
     * Apakah request ini menuju route REST dengan awalan tertentu, baik lewat
     * pretty permalink (/wp-json/...) maupun ?rest_route=. Dipakai sebelum
     * REST server berjalan, jadi hanya membaca URL.
     */
    public static function is_mcp_request(): bool
    {
        return self::is_rest_request_to('mcp/');
    }

    private static function is_rest_request_to(string $route_prefix): bool
    {
        $route_prefix = '/' . ltrim($route_prefix, '/');
        $uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
        $path = (string) wp_parse_url($uri, PHP_URL_PATH);

        if (false !== strpos($path, '/' . trim(rest_get_url_prefix(), '/') . $route_prefix)) {
            return true;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $route = isset($_GET['rest_route']) ? '/' . ltrim((string) wp_unslash($_GET['rest_route']), '/') : '';
        return '' !== $route && 0 === strpos($route, $route_prefix);
    }

    /**
     * Token dari header Bearer, atau dari query ?rtmkit_token= untuk klien yang
     * hanya bisa diisi alamat URL (tanpa header maupun OAuth).
     */
    private static function request_token(): string
    {
        $header = self::authorization_header();
        if (0 === stripos($header, 'Bearer ')) {
            return trim(substr($header, 7));
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return isset($_GET[self::QUERY_PARAM]) ? sanitize_text_field(wp_unslash($_GET[self::QUERY_PARAM])) : '';
    }

    private static function authorization_header(): string
    {
        foreach (['HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION'] as $key) {
            if (!empty($_SERVER[$key])) {
                return trim((string) wp_unslash($_SERVER[$key]));
            }
        }

        if (function_exists('getallheaders')) {
            foreach ((array) getallheaders() as $name => $value) {
                if ('authorization' === strtolower($name)) {
                    return trim((string) $value);
                }
            }
        }

        return '';
    }

    private static function pkce_challenge(string $verifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }

    private static function hash(string $value): string
    {
        return hash('sha256', $value);
    }

    private static function json(array $data, int $status = 200): \WP_REST_Response
    {
        $response = new \WP_REST_Response((object) $data, $status);
        $response->header('Cache-Control', 'no-store');
        $response->header('Pragma', 'no-cache');
        return $response;
    }

    private static function error(string $code, string $description, int $status = 400): \WP_REST_Response
    {
        return self::json(['error' => $code, 'error_description' => $description], $status);
    }
}
