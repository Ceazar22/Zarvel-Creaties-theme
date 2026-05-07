<?php
defined('ABSPATH') || exit;

/**
 * Zarvel Creative functions.php
 * Main theme loader + small hardening layer.
 */

/**
 * Safely load theme files.
 */
function zarvel_require_file($relative_path) {
    $file = get_theme_file_path($relative_path);

    if (file_exists($file) && is_readable($file)) {
        require_once $file;
    }
}

/**
 * Theme includes
 */
zarvel_require_file('/inc/theme-setup.php');
zarvel_require_file('/inc/smtp.php');
zarvel_require_file('/inc/customize-form-handler.php');
zarvel_require_file('/inc/template-router.php');
zarvel_require_file('/inc/security-hardening.php');

/**
 * Get real WooCommerce product categories for theme navigation.
 */
function zarvel_get_shop_categories($limit = 0) {
    if (!taxonomy_exists('product_cat')) {
        return array();
    }

    $terms = get_terms(array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'parent'     => 0,
        'orderby'    => 'menu_order',
        'order'      => 'ASC',
        'exclude'    => array((int) get_option('default_product_cat')),
    ));

    if (is_wp_error($terms) || empty($terms)) {
        return array();
    }

    $terms = array_values(array_filter($terms, function ($term) {
        return !empty($term->slug) && $term->slug !== 'uncategorized';
    }));

    if ($limit > 0) {
        $terms = array_slice($terms, 0, $limit);
    }

    return $terms;
}

/**
 * Shipping rule:
 * - United States: free shipping.
 * - Outside United States: do not override Printful/WooCommerce live shipping rates.
 */
function zarvel_is_us_shipping_destination($package) {
    $country = '';

    if (!empty($package['destination']['country'])) {
        $country = $package['destination']['country'];
    } elseif (function_exists('WC') && WC()->customer) {
        $country = WC()->customer->get_shipping_country() ?: WC()->customer->get_billing_country();
    }

    return strtoupper((string) $country) === 'US';
}

add_filter('woocommerce_package_rates', function ($rates, $package) {
    if (!is_array($rates)) {
        return $rates;
    }

    if (zarvel_is_us_shipping_destination($package)) {
        $free_rate_id = 'zarvel_free_us_shipping';

        return array(
            $free_rate_id => new WC_Shipping_Rate(
                $free_rate_id,
                __('Free Shipping (USA)', 'zarvel-creative'),
                0,
                array(),
                'zarvel_free_us_shipping'
            ),
        );
    }

    foreach ($rates as $rate_id => $rate) {
        if (is_object($rate) && method_exists($rate, 'get_method_id') && $rate->get_method_id() === 'free_shipping') {
            unset($rates[$rate_id]);
        }

        if ($rate_id === 'zarvel_free_us_shipping') {
            unset($rates[$rate_id]);
        }
    }

    return $rates;
}, 100, 2);

/**
 * Side cart quantity controls.
 */
add_action('template_redirect', function () {
    if (
        is_admin() ||
        !function_exists('WC') ||
        !WC()->cart ||
        empty($_GET['zc_cart_item']) ||
        !isset($_GET['zc_qty'])
    ) {
        return;
    }

    $cart_item_key = sanitize_text_field(wp_unslash($_GET['zc_cart_item']));
    $quantity = max(0, absint(wp_unslash($_GET['zc_qty'])));
    $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

    if (!wp_verify_nonce($nonce, 'zc_sidecart_qty_' . $cart_item_key)) {
        return;
    }

    if (!isset(WC()->cart->cart_contents[$cart_item_key])) {
        return;
    }

    WC()->cart->set_quantity($cart_item_key, $quantity, true);

    $redirect_url = remove_query_arg(array('zc_cart_item', 'zc_qty', '_wpnonce'));
    wp_safe_redirect($redirect_url ?: home_url('/'));
    exit;
});

/**
 * Add customizer design to WooCommerce cart.
 */
function zarvel_customizer_add_to_cart_ajax() {
    if (!function_exists('WC')) {
        wp_send_json_error(array('message' => 'WooCommerce is unavailable.'), 400);
    }

    if (!WC()->cart && function_exists('wc_load_cart')) {
        wc_load_cart();
    }

    if (!WC()->cart) {
        wp_send_json_error(array('message' => 'WooCommerce cart is unavailable.'), 400);
    }

    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';

    if (!wp_verify_nonce($nonce, 'zc_customizer_add_to_cart')) {
        wp_send_json_error(array('message' => 'Security check failed. Please refresh and try again.'), 403);
    }

    $product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
    $variation_id = isset($_POST['variation_id']) ? absint(wp_unslash($_POST['variation_id'])) : 0;
    $quantity = isset($_POST['quantity']) ? max(1, absint(wp_unslash($_POST['quantity']))) : 1;
    $design_title = isset($_POST['design_title'])
        ? sanitize_text_field(wp_unslash($_POST['design_title']))
        : __('Untitled Design', 'zarvel-creative');
    $imprint = isset($_POST['imprint']) ? sanitize_text_field(wp_unslash($_POST['imprint'])) : '';
    $imprint_size = isset($_POST['imprint_size']) ? sanitize_text_field(wp_unslash($_POST['imprint_size'])) : '';

    $product = wc_get_product($product_id);

    if (!$product) {
        wp_send_json_error(array('message' => 'Product was not found.'), 404);
    }

    $variation_attributes = array();

    if ($variation_id) {
        $variation = wc_get_product($variation_id);

        if (!$variation || !$variation instanceof WC_Product_Variation) {
            wp_send_json_error(array('message' => 'Selected variation was not found.'), 404);
        }

        $variation_attributes = $variation->get_variation_attributes();
    }

    $cart_item_data = array(
        'zc_custom_design' => true,
        'zc_design_title'  => $design_title,
        'zc_imprint'       => $imprint,
        'zc_imprint_size'  => $imprint_size,
        'zc_design_key'    => md5(wp_json_encode(array(
            'product_id'    => $product_id,
            'variation_id'  => $variation_id,
            'design_title'  => $design_title,
            'imprint'       => $imprint,
            'imprint_size'  => $imprint_size,
            'time'          => microtime(true),
        ))),
    );

    $added_key = WC()->cart->add_to_cart(
        $product_id,
        $quantity,
        $variation_id,
        $variation_attributes,
        $cart_item_data
    );

    if (!$added_key) {
        wp_send_json_error(array('message' => 'Could not add custom design to cart.'), 400);
    }

    WC()->cart->calculate_totals();

    $redirect_url = wp_get_referer() ?: home_url('/');
    $redirect_url = add_query_arg('zc_open_cart', '1', $redirect_url);

    wp_send_json_success(array(
        'cart_url' => $redirect_url,
        'count'    => WC()->cart->get_cart_contents_count(),
    ));
}
add_action('wp_ajax_zc_customizer_add_to_cart', 'zarvel_customizer_add_to_cart_ajax');
add_action('wp_ajax_nopriv_zc_customizer_add_to_cart', 'zarvel_customizer_add_to_cart_ajax');

/**
 * Persist custom design summary on checkout/order line items.
 */
add_action('woocommerce_checkout_create_order_line_item', function ($item, $cart_item_key, $values) {
    if (empty($values['zc_custom_design'])) {
        return;
    }

    if (!empty($values['zc_design_title'])) {
        $item->add_meta_data(__('Design title', 'zarvel-creative'), $values['zc_design_title']);
    }

    if (!empty($values['zc_imprint'])) {
        $item->add_meta_data(__('Imprint', 'zarvel-creative'), $values['zc_imprint']);
    }

    if (!empty($values['zc_imprint_size'])) {
        $item->add_meta_data(__('Imprint size', 'zarvel-creative'), $values['zc_imprint_size']);
    }
}, 10, 3);
