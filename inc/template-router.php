<?php
defined('ABSPATH') || exit;

/**
 * Get current URL path.
 */
function zarvel_get_current_path() {
    $request_uri = isset($_SERVER['REQUEST_URI'])
        ? wp_unslash($_SERVER['REQUEST_URI'])
        : '';

    $request_path = trim((string) wp_parse_url($request_uri, PHP_URL_PATH), '/');
    $home_path    = trim((string) wp_parse_url(home_url('/'), PHP_URL_PATH), '/');

    if ($home_path && strpos($request_path, $home_path) === 0) {
        $request_path = trim(substr($request_path, strlen($home_path)), '/');
    }

    return $request_path;
}

/**
 * Find first existing template.
 */
function zarvel_find_existing_template($template_paths) {
    foreach ($template_paths as $template_path) {
        if (file_exists($template_path)) {
            return $template_path;
        }
    }

    return '';
}

/**
 * Prepare custom routed theme page.
 *
 * Important:
 * Do NOT set is_page or is_singular here.
 * These custom pages are not real WordPress posts.
 * Setting is_page=true without a post object causes body_class()
 * warnings inside wp-includes/post-template.php.
 */
function zarvel_prepare_theme_only_page() {
    global $wp_query;

    if ($wp_query) {
        $wp_query->is_404 = false;
    }

    status_header(200);
    nocache_headers();
}

/**
 * Custom template router.
 */
function zarvel_custom_template_router($template) {
    if (is_admin()) {
        return $template;
    }

    $theme_dir = get_template_directory();

    $front_page_template       = $theme_dir . '/pages/front-page.php';
    $single_product_template   = $theme_dir . '/pages/single-product.php';
    $product_category_template = $theme_dir . '/pages/product-category.php';
    $customize_template        = $theme_dir . '/pages/customize.php';
    $services_template         = $theme_dir . '/pages/our-services.php';
    $about_template            = $theme_dir . '/pages/about-us.php';
    $contact_template          = $theme_dir . '/pages/contact.php';
    $shop_template             = $theme_dir . '/pages/shop.php';
    $cart_template             = $theme_dir . '/pages/cart.php';
    $checkout_template         = $theme_dir . '/pages/checkout.php';
    $account_template          = $theme_dir . '/pages/my-account.php';
    $info_template             = $theme_dir . '/pages/info-page.php';

    /**
     * Design Studio template.
     *
     * Use this file if possible:
     * /pages/page-design-studio.php
     */
    $design_studio_template = zarvel_find_existing_template([
        $theme_dir . '/pages/page-design-studio.php',
        $theme_dir . '/pages/design-studio.php',
        $theme_dir . '/page-design-studio.php',
        $theme_dir . '/page-designstudio.php',
    ]);

    $current_path = zarvel_get_current_path();

    /**
     * Theme-only Customize page.
     * URL: /customize/
     */
    if ($current_path === 'customize' && file_exists($customize_template)) {
        zarvel_prepare_theme_only_page();
        return $customize_template;
    }

    /**
     * Theme-only Our Services page.
     * URL: /our-services/
     */
    if ($current_path === 'our-services' && file_exists($services_template)) {
        zarvel_prepare_theme_only_page();
        return $services_template;
    }

    /**
     * Theme-only Design Studio page.
     *
     * Main URL:
     * /page-design-studio/
     *
     * Also supports:
     * /design-studio/
     * /page-designstudio/
     */
    if (
        in_array($current_path, ['page-design-studio', 'design-studio', 'page-designstudio'], true) &&
        $design_studio_template
    ) {
        zarvel_prepare_theme_only_page();
        return $design_studio_template;
    }

    /**
     * Theme-only About page.
     * URL: /about-us/
     */
    if ($current_path === 'about-us' && file_exists($about_template)) {
        zarvel_prepare_theme_only_page();
        return $about_template;
    }

    /**
     * Theme-only Contact page.
     * URL: /contact/
     */
    if ($current_path === 'contact' && file_exists($contact_template)) {
        zarvel_prepare_theme_only_page();
        return $contact_template;
    }

    /**
     * Theme-only Shop page.
     * URL: /shop/
     */
    if ($current_path === 'shop' && file_exists($shop_template)) {
        zarvel_prepare_theme_only_page();
        return $shop_template;
    }

    /**
     * Theme-only WooCommerce utility pages.
     */
    if ($current_path === 'cart' && file_exists($cart_template)) {
        zarvel_prepare_theme_only_page();
        return $cart_template;
    }

    if (
        ($current_path === 'checkout' || strpos($current_path, 'checkout/') === 0) &&
        file_exists($checkout_template)
    ) {
        zarvel_prepare_theme_only_page();
        return $checkout_template;
    }

    if (
        ($current_path === 'my-account' || strpos($current_path, 'my-account/') === 0) &&
        file_exists($account_template)
    ) {
        zarvel_prepare_theme_only_page();
        return $account_template;
    }

    /**
     * Theme-only static support/company pages.
     */
    if (
        in_array(
            $current_path,
            [
                'about',
                'shipping-policy',
                'refund-policy',
                'faqs',
                'size-guide',
                'track-order',
                'our-process',
                'careers',
                'blog',
                'terms-of-service',
                'privacy-policy',
            ],
            true
        ) &&
        file_exists($info_template)
    ) {
        zarvel_prepare_theme_only_page();
        return $info_template;
    }

    /**
     * Homepage.
     */
    if (is_front_page() && file_exists($front_page_template)) {
        return $front_page_template;
    }

    /**
     * Single WooCommerce product page.
     */
    if (
        ((function_exists('is_product') && is_product()) || is_singular('product')) &&
        file_exists($single_product_template)
    ) {
        return $single_product_template;
    }

    /**
     * Single product category page.
     */
    if (
        function_exists('is_product_category') &&
        is_product_category() &&
        file_exists($product_category_template)
    ) {
        return $product_category_template;
    }

    return $template;
}
add_filter('template_include', 'zarvel_custom_template_router', 99);
