<?php
defined('ABSPATH') || exit;

/**
 * Zarvel theme security hardening.
 *
 * This is intentionally theme-layer hardening. Server rules, WAF, backups,
 * least-privilege hosting users, and strong WordPress credentials still matter.
 */

if (!defined('DISALLOW_FILE_EDIT')) {
    define('DISALLOW_FILE_EDIT', true);
}

if (!defined('ZARVEL_LOGIN_LIMIT_MAX_ATTEMPTS')) {
    define('ZARVEL_LOGIN_LIMIT_MAX_ATTEMPTS', 5);
}

if (!defined('ZARVEL_LOGIN_LIMIT_WINDOW')) {
    define('ZARVEL_LOGIN_LIMIT_WINDOW', 15 * MINUTE_IN_SECONDS);
}

/**
 * Add security headers that should not break WooCommerce checkout or the customizer.
 */
add_action('send_headers', function () {
    if (headers_sent()) {
        return;
    }

    header_remove('X-Powered-By');

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header('X-Permitted-Cross-Domain-Policies: none');

    if (is_ssl()) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
});

/**
 * Hide noisy WordPress generator/discovery output.
 */
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('template_redirect', 'wp_shortlink_header', 11);
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('template_redirect', 'rest_output_link_header', 11);
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'wp_oembed_add_host_js');

add_filter('the_generator', '__return_empty_string');

/**
 * Disable XML-RPC and pingback attack surface.
 */
add_filter('xmlrpc_enabled', '__return_false');
add_filter('xmlrpc_methods', function ($methods) {
    unset($methods['pingback.ping']);
    unset($methods['pingback.extensions.getPingbacks']);
    return $methods;
});
add_filter('wp_headers', function ($headers) {
    unset($headers['X-Pingback']);
    return $headers;
});

/**
 * Disable application passwords unless explicitly re-enabled in wp-config.php.
 */
add_filter('wp_is_application_passwords_available', function ($available) {
    if (defined('ZARVEL_ALLOW_APPLICATION_PASSWORDS') && ZARVEL_ALLOW_APPLICATION_PASSWORDS) {
        return $available;
    }

    return false;
});

/**
 * Make login errors generic so usernames are not confirmed by error text.
 */
add_filter('login_errors', function () {
    return __('Invalid login details.', 'zarvel-creative');
});

/**
 * Lightweight login rate limiting by IP address.
 */
function zarvel_security_get_request_ip() {
    $ip = isset($_SERVER['REMOTE_ADDR'])
        ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']))
        : 'unknown';

    return preg_replace('/[^a-fA-F0-9:\.]/', '', $ip);
}

function zarvel_security_login_limit_key() {
    return 'zarvel_login_limit_' . md5(zarvel_security_get_request_ip());
}

add_filter('authenticate', function ($user) {
    if (is_wp_error($user)) {
        return $user;
    }

    $attempts = (int) get_transient(zarvel_security_login_limit_key());

    if ($attempts >= (int) ZARVEL_LOGIN_LIMIT_MAX_ATTEMPTS) {
        return new WP_Error(
            'zarvel_login_rate_limited',
            __('Too many login attempts. Please try again later.', 'zarvel-creative')
        );
    }

    return $user;
}, 1);

add_action('wp_login_failed', function () {
    $key = zarvel_security_login_limit_key();
    $attempts = (int) get_transient($key);

    set_transient($key, $attempts + 1, (int) ZARVEL_LOGIN_LIMIT_WINDOW);
});

add_action('wp_login', function () {
    delete_transient(zarvel_security_login_limit_key());
});

/**
 * Stop common author/user enumeration paths.
 */
add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
        return;
    }

    if (is_author() || (isset($_GET['author']) && preg_match('/^\d+$/', (string) $_GET['author']))) {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
}, 1);

add_filter('rest_endpoints', function ($endpoints) {
    if (is_user_logged_in()) {
        return $endpoints;
    }

    foreach (array_keys($endpoints) as $route) {
        if (strpos($route, '/wp/v2/users') === 0) {
            unset($endpoints[$route]);
        }
    }

    return $endpoints;
});

add_filter('rest_authentication_errors', function ($result) {
    if (!empty($result) || is_user_logged_in()) {
        return $result;
    }

    $request_uri = isset($_SERVER['REQUEST_URI'])
        ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']))
        : '';

    if (preg_match('#/wp-json/wp/v2/users#i', $request_uri)) {
        return new WP_Error(
            'zarvel_rest_users_blocked',
            __('REST user access blocked.', 'zarvel-creative'),
            array('status' => 401)
        );
    }

    return $result;
});

/**
 * Limit uploads for non-admin users to safer file types.
 */
add_filter('upload_mimes', function ($mimes) {
    if (current_user_can('manage_options')) {
        return $mimes;
    }

    return array(
        'jpg|jpeg|jpe' => 'image/jpeg',
        'png'          => 'image/png',
        'gif'          => 'image/gif',
        'webp'         => 'image/webp',
        'pdf'          => 'application/pdf',
    );
});

/**
 * Extra file validation for uploads.
 */
add_filter('wp_handle_upload_prefilter', function ($file) {
    if (current_user_can('manage_options')) {
        return $file;
    }

    $max_file_size = 20 * 1024 * 1024;

    if (!empty($file['size']) && $file['size'] > $max_file_size) {
        $file['error'] = __('File is too large.', 'zarvel-creative');
        return $file;
    }

    $allowed_mimes = array(
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
    );

    $check = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
    $mime = isset($check['type']) ? $check['type'] : '';

    if (!$mime || !in_array($mime, $allowed_mimes, true)) {
        $file['error'] = __('This file type is not allowed.', 'zarvel-creative');
    }

    return $file;
});

/**
 * Disable comments and pingbacks site-wide for this commerce theme.
 */
add_action('init', function () {
    foreach (get_post_types(array('public' => true), 'names') as $post_type) {
        if (post_type_supports($post_type, 'comments')) {
            remove_post_type_support($post_type, 'comments');
        }

        if (post_type_supports($post_type, 'trackbacks')) {
            remove_post_type_support($post_type, 'trackbacks');
        }
    }
});

add_filter('comments_open', '__return_false', 20);
add_filter('pings_open', '__return_false', 20);

/**
 * Protect sensitive files if requested directly through WordPress routing.
 */
add_action('template_redirect', function () {
    $request_path = isset($_SERVER['REQUEST_URI'])
        ? trim((string) wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH), '/')
        : '';

    if (preg_match('#(^|/)(wp-config\.php|\.env|composer\.(json|lock)|package(-lock)?\.json|yarn\.lock)$#i', $request_path)) {
        status_header(403);
        exit;
    }
}, 0);
