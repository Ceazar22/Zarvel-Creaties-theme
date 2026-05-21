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
 * Keep storefront price formatting in US dollars.
 */
add_filter('woocommerce_currency', function ($currency) {
    return 'USD';
}, 20);

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
 * - Outside United States: keep Printful/WooCommerce live shipping rates.
 * - If no non-US rate is returned, add a fallback estimate so checkout never
 *   completes with accidental zero shipping.
 */
if (!defined('ZARVEL_INTERNATIONAL_SHIPPING_FALLBACK')) {
    define('ZARVEL_INTERNATIONAL_SHIPPING_FALLBACK', 299);
}

function zarvel_is_us_shipping_destination($package) {
    $country = zarvel_get_package_destination_country($package);

    return strtoupper((string) $country) === 'US';
}

function zarvel_get_package_destination_country($package) {
    if (!empty($package['destination']['country'])) {
        return strtoupper((string) $package['destination']['country']);
    }

    return zarvel_get_customer_shipping_country();
}

function zarvel_get_customer_shipping_country() {
    if (!function_exists('WC') || !WC()->customer) {
        return '';
    }

    return strtoupper((string) (WC()->customer->get_shipping_country() ?: WC()->customer->get_billing_country()));
}

function zarvel_get_cart_shipping_label() {
    if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) {
        return '&mdash;';
    }

    if (!WC()->cart->needs_shipping()) {
        return __('No shipping required', 'zarvel-creative');
    }

    $shipping_total = (float) WC()->cart->get_shipping_total() + (float) WC()->cart->get_shipping_tax();
    $country = zarvel_get_customer_shipping_country();

    if ($country === 'US') {
        return __('FREE', 'zarvel-creative');
    }

    if ($shipping_total > 0) {
        return function_exists('wc_price') ? wc_price($shipping_total) : number_format($shipping_total, 2);
    }

    return __('Calculated at checkout', 'zarvel-creative');
}

function zarvel_get_cart_total_html() {
    if (!function_exists('WC') || !WC()->cart) {
        return function_exists('wc_price') ? wc_price(0) : '$0.00';
    }

    $cart_total = (float) WC()->cart->get_total('edit');

    return function_exists('wc_price') ? wc_price($cart_total) : '$' . number_format($cart_total, 2);
}

function zarvel_refresh_cart_totals() {
    if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) {
        return;
    }

    WC()->cart->calculate_shipping();
    if (WC()->cart->is_empty()) {
        WC()->cart->calculate_totals();
    } else {
        zarvel_refresh_cart_totals();
    }
}

add_action('woocommerce_shipping_init', function () {
    if (!class_exists('WC_Shipping_Method') || class_exists('Zarvel_Theme_Shipping_Method')) {
        return;
    }

    class Zarvel_Theme_Shipping_Method extends WC_Shipping_Method {
        public function __construct($instance_id = 0) {
            $this->id = 'zarvel_theme_shipping';
            $this->instance_id = absint($instance_id);
            $this->method_title = __('Zarvel Theme Shipping', 'zarvel-creative');
            $this->method_description = __('Keeps WooCommerce shipping calculations active for theme-defined rates.', 'zarvel-creative');
            $this->enabled = 'yes';
            $this->title = __('Zarvel Shipping', 'zarvel-creative');
        }

        public function calculate_shipping($package = array()) {
            return;
        }
    }
});

add_filter('woocommerce_shipping_methods', function ($methods) {
    $methods['zarvel_theme_shipping'] = 'Zarvel_Theme_Shipping_Method';
    delete_transient('wc_shipping_method_count');

    return $methods;
});

add_filter('pre_transient_wc_shipping_method_count', function ($pre) {
    if (!class_exists('WC_Cache_Helper')) {
        return $pre;
    }

    return array(
        'version'  => WC_Cache_Helper::get_transient_version('shipping'),
        'legacy'   => 1,
        'enabled'  => 1,
        'disabled' => 0,
    );
});

add_filter('woocommerce_product_needs_shipping', function ($needs_shipping, $product) {
    if (
        is_object($product) &&
        method_exists($product, 'get_type') &&
        in_array($product->get_type(), array('simple', 'variable', 'variation'), true)
    ) {
        return true;
    }

    return $needs_shipping;
}, 20, 2);

add_filter('woocommerce_cart_needs_shipping', function ($needs_shipping) {
    if (function_exists('WC') && WC()->cart && !WC()->cart->is_empty()) {
        return true;
    }

    return $needs_shipping;
}, 20);

add_filter('woocommerce_cart_shipping_packages', function ($packages) {
    if (!empty($packages) || !function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) {
        return $packages;
    }

    $cart_contents = WC()->cart->get_cart();

    if (empty($cart_contents)) {
        return $packages;
    }

    return array(
        array(
            'contents'        => $cart_contents,
            'contents_cost'   => WC()->cart->get_subtotal(),
            'applied_coupons' => WC()->cart->get_applied_coupons(),
            'user'            => array(
                'ID' => get_current_user_id(),
            ),
            'destination'     => array(
                'country'   => WC()->customer ? WC()->customer->get_shipping_country() ?: WC()->customer->get_billing_country() : '',
                'state'     => WC()->customer ? WC()->customer->get_shipping_state() ?: WC()->customer->get_billing_state() : '',
                'postcode'  => WC()->customer ? WC()->customer->get_shipping_postcode() ?: WC()->customer->get_billing_postcode() : '',
                'city'      => WC()->customer ? WC()->customer->get_shipping_city() ?: WC()->customer->get_billing_city() : '',
                'address'   => WC()->customer ? WC()->customer->get_shipping_address_1() ?: WC()->customer->get_billing_address_1() : '',
                'address_1' => WC()->customer ? WC()->customer->get_shipping_address_1() ?: WC()->customer->get_billing_address_1() : '',
                'address_2' => WC()->customer ? WC()->customer->get_shipping_address_2() ?: WC()->customer->get_billing_address_2() : '',
            ),
        ),
    );
}, 20);

add_filter('woocommerce_package_rates', function ($rates, $package) {
    if (!is_array($rates)) {
        return $rates;
    }

    $destination_country = zarvel_get_package_destination_country($package);

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

    if (!$destination_country) {
        return $rates;
    }

    $has_paid_shipping_rate = false;

    foreach ($rates as $rate) {
        if (is_object($rate) && method_exists($rate, 'get_cost') && (float) $rate->get_cost() > 0) {
            $has_paid_shipping_rate = true;
            break;
        }
    }

    if (!$has_paid_shipping_rate) {
        $fallback_rate_id = 'zarvel_international_printful_estimate';
        $rates[$fallback_rate_id] = new WC_Shipping_Rate(
            $fallback_rate_id,
            __('International Shipping (Printful estimate)', 'zarvel-creative'),
            (float) ZARVEL_INTERNATIONAL_SHIPPING_FALLBACK,
            array(),
            'zarvel_international_printful_estimate'
        );
    }

    return $rates;
}, 100, 2);

add_action('woocommerce_checkout_process', function () {
    if (!function_exists('WC') || !WC()->cart || !WC()->cart->needs_shipping()) {
        return;
    }

    $country = '';

    if (!empty($_POST['ship_to_different_address']) && !empty($_POST['shipping_country'])) {
        $country = sanitize_text_field(wp_unslash($_POST['shipping_country']));
    } elseif (!empty($_POST['billing_country'])) {
        $country = sanitize_text_field(wp_unslash($_POST['billing_country']));
    } else {
        $country = zarvel_get_customer_shipping_country();
    }

    $country = strtoupper((string) $country);

    if ($country && $country !== 'US' && (float) WC()->cart->get_shipping_total() <= 0) {
        wc_add_notice(
            __('International shipping must be calculated before placing your order. Please review your address and shipping method.', 'zarvel-creative'),
            'error'
        );
    }
});

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
 * JSON state for AJAX side cart quantity and remove controls.
 */
function zarvel_send_sidecart_state($cart_item_key = '') {
    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(array('message' => __('Cart is unavailable.', 'zarvel-creative')), 400);
    }

    WC()->cart->calculate_totals();

    $cart_item = $cart_item_key && isset(WC()->cart->cart_contents[$cart_item_key])
        ? WC()->cart->cart_contents[$cart_item_key]
        : array();
    $cart_count = WC()->cart->get_cart_contents_count();
    $cart_subtotal = (float) WC()->cart->get_subtotal();

    wp_send_json_success(array(
        'cart_count'      => $cart_count,
        'count_label'     => sprintf(
            /* translators: %s: cart item count. */
            _n('Subtotal (%s item)', 'Subtotal (%s items)', $cart_count, 'zarvel-creative'),
            $cart_count
        ),
        'subtotal_html'   => function_exists('wc_price') ? wc_price($cart_subtotal) : '$' . number_format($cart_subtotal, 2),
        'shipping_label'  => zarvel_get_cart_shipping_label(),
        'total_html'      => zarvel_get_cart_total_html(),
        'item_exists'     => !empty($cart_item),
        'item_quantity'   => !empty($cart_item['quantity']) ? (int) $cart_item['quantity'] : 0,
    ));
}

function zarvel_require_sidecart_ajax_cart() {
    if (!function_exists('WC')) {
        wp_send_json_error(array('message' => __('WooCommerce is unavailable.', 'zarvel-creative')), 400);
    }

    if (!WC()->cart && function_exists('wc_load_cart')) {
        wc_load_cart();
    }

    if (!WC()->cart) {
        wp_send_json_error(array('message' => __('Cart is unavailable.', 'zarvel-creative')), 400);
    }

    check_ajax_referer('zc_sidecart_ajax', 'nonce');
}

function zarvel_sidecart_quantity_ajax() {
    zarvel_require_sidecart_ajax_cart();

    $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field(wp_unslash($_POST['cart_item_key'])) : '';
    $operation = isset($_POST['operation']) ? sanitize_key(wp_unslash($_POST['operation'])) : '';

    if (!$cart_item_key || !isset(WC()->cart->cart_contents[$cart_item_key])) {
        wp_send_json_error(array('message' => __('Cart item was not found.', 'zarvel-creative')), 404);
    }

    $quantity = (int) WC()->cart->cart_contents[$cart_item_key]['quantity'];
    $quantity += $operation === 'increase' ? 1 : -1;

    WC()->cart->set_quantity($cart_item_key, max(0, $quantity), true);
    zarvel_send_sidecart_state($cart_item_key);
}
add_action('wp_ajax_zc_sidecart_quantity', 'zarvel_sidecart_quantity_ajax');
add_action('wp_ajax_nopriv_zc_sidecart_quantity', 'zarvel_sidecart_quantity_ajax');

function zarvel_sidecart_remove_ajax() {
    zarvel_require_sidecart_ajax_cart();

    $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field(wp_unslash($_POST['cart_item_key'])) : '';

    if ($cart_item_key && isset(WC()->cart->cart_contents[$cart_item_key])) {
        WC()->cart->remove_cart_item($cart_item_key);
    }

    zarvel_send_sidecart_state($cart_item_key);
}
add_action('wp_ajax_zc_sidecart_remove', 'zarvel_sidecart_remove_ajax');
add_action('wp_ajax_nopriv_zc_sidecart_remove', 'zarvel_sidecart_remove_ajax');

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

    if (empty($values['zc_design_request']) || empty($values['zc_design_details']) || !is_array($values['zc_design_details'])) {
        return;
    }

    $details = $values['zc_design_details'];
    $item->add_meta_data(__('Design help requested', 'zarvel-creative'), __('Yes', 'zarvel-creative'));

    $detail_labels = array(
        'full_name'                  => __('Design contact name', 'zarvel-creative'),
        'email'                      => __('Design contact email', 'zarvel-creative'),
        'phone'                      => __('Design contact phone', 'zarvel-creative'),
        'product_type'               => __('Requested product type', 'zarvel-creative'),
        'print_location'             => __('Print placement', 'zarvel-creative'),
        'print_location_extra_cost'  => __('Placement extra cost', 'zarvel-creative'),
        'print_location_extra_label' => __('Placement cost note', 'zarvel-creative'),
        'logo_status'                => __('Logo status', 'zarvel-creative'),
        'design_text'                => __('Design text', 'zarvel-creative'),
        'preferred_colors'           => __('Preferred colors', 'zarvel-creative'),
        'design_notes'               => __('Design instructions', 'zarvel-creative'),
        'selected_options'           => __('Selected options', 'zarvel-creative'),
    );

    foreach ($detail_labels as $detail_key => $detail_label) {
        if (!empty($details[$detail_key])) {
            $item->add_meta_data($detail_label, sanitize_textarea_field((string) $details[$detail_key]));
        }
    }

    if (!empty($details['uploaded_files']) && is_array($details['uploaded_files'])) {
        $item->add_meta_data(
            __('Uploaded design files', 'zarvel-creative'),
            implode("\n", array_map('sanitize_text_field', $details['uploaded_files']))
        );
    }
}, 10, 3);
