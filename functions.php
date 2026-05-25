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

if (!defined('ZARVEL_DESIGN_DEPOSIT_AMOUNT')) {
    define('ZARVEL_DESIGN_DEPOSIT_AMOUNT', '15');
}

/**
 * Keep a checkout-ready design deposit service available for request follow-up.
 */
function zarvel_get_design_deposit_product_id() {
    if (!function_exists('wc_get_product_id_by_sku') || !class_exists('WC_Product_Simple')) {
        return 0;
    }

    $product_id = (int) wc_get_product_id_by_sku('zarvel-design-deposit');

    if ($product_id) {
        $deposit = wc_get_product($product_id);

        if ($deposit && (!$deposit->is_virtual() || !$deposit->is_sold_individually())) {
            $deposit->set_virtual(true);
            $deposit->set_sold_individually(true);
            $deposit->save();
        }

        return $product_id;
    }

    $deposit = new WC_Product_Simple();
    $deposit->set_name(__('Design Deposit', 'zarvel-creative'));
    $deposit->set_slug('design-deposit');
    $deposit->set_sku('zarvel-design-deposit');
    $deposit->set_status('publish');
    $deposit->set_catalog_visibility('hidden');
    $deposit->set_virtual(true);
    $deposit->set_sold_individually(true);
    $deposit->set_regular_price(ZARVEL_DESIGN_DEPOSIT_AMOUNT);
    $deposit->set_price(ZARVEL_DESIGN_DEPOSIT_AMOUNT);
    $deposit->set_short_description(__('Pays the design deposit so Zarvel Creatives can begin reviewing a custom design request.', 'zarvel-creative'));
    $deposit->set_description(__('This deposit starts the design review for a custom product request. Final product pricing is handled after the approved design and product details are ready.', 'zarvel-creative'));

    return (int) $deposit->save();
}

function zarvel_get_design_request_deposit_amount($design_request_id) {
    $fallback_amount = (float) ZARVEL_DESIGN_DEPOSIT_AMOUNT;
    $selected_variation_id = $design_request_id
        ? absint(get_post_meta($design_request_id, '_selected_variation_id', true))
        : 0;
    $selected_product_id = $design_request_id
        ? absint(get_post_meta($design_request_id, '_selected_product_id', true))
        : 0;
    $selected_product = function_exists('wc_get_product') && ($selected_variation_id || $selected_product_id)
        ? wc_get_product($selected_variation_id ?: $selected_product_id)
        : null;
    $selected_product_price = $selected_product ? (float) $selected_product->get_price() : 0;

    if ($selected_product_price <= 0) {
        return $fallback_amount;
    }

    return round($selected_product_price * 0.5, wc_get_price_decimals());
}

function zarvel_get_design_deposit_checkout_url($design_request_id = 0) {
    $product_id = zarvel_get_design_deposit_product_id();

    if (!$product_id) {
        return home_url('/contact/');
    }

    $checkout_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout/');

    $checkout_args = array(
        'add-to-cart' => $product_id,
    );

    if ($design_request_id && get_post_type($design_request_id) === ZARVEL_DESIGN_REQUEST_TYPE) {
        $checkout_args['zc_design_request'] = $design_request_id;
    }

    return add_query_arg($checkout_args, $checkout_url);
}

function zarvel_remove_design_deposits_from_cart() {
    if (!function_exists('WC') || !WC()->cart) {
        return;
    }

    $deposit_product_id = zarvel_get_design_deposit_product_id();

    if (!$deposit_product_id) {
        return;
    }

    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        if (!empty($cart_item['product_id']) && (int) $cart_item['product_id'] === $deposit_product_id) {
            WC()->cart->remove_cart_item($cart_item_key);
        }
    }
}

add_action('wp_loaded', function () {
    $add_to_cart_product_id = isset($_REQUEST['add-to-cart']) ? absint(wp_unslash($_REQUEST['add-to-cart'])) : 0;
    $design_request_id = isset($_REQUEST['zc_design_request']) ? absint(wp_unslash($_REQUEST['zc_design_request'])) : 0;

    if (
        !$add_to_cart_product_id ||
        !$design_request_id ||
        get_post_type($design_request_id) !== ZARVEL_DESIGN_REQUEST_TYPE ||
        $add_to_cart_product_id !== zarvel_get_design_deposit_product_id()
    ) {
        return;
    }

    zarvel_remove_design_deposits_from_cart();
}, 10);

add_filter('woocommerce_add_cart_item_data', function ($cart_item_data, $product_id) {
    $design_request_id = isset($_GET['zc_design_request']) ? absint(wp_unslash($_GET['zc_design_request'])) : 0;

    if (
        !$design_request_id ||
        get_post_type($design_request_id) !== ZARVEL_DESIGN_REQUEST_TYPE ||
        $product_id !== zarvel_get_design_deposit_product_id()
    ) {
        return $cart_item_data;
    }

    $cart_item_data['zc_design_deposit_request_id'] = $design_request_id;
    $cart_item_data['zc_design_deposit_amount'] = zarvel_get_design_request_deposit_amount($design_request_id);

    return $cart_item_data;
}, 10, 2);

add_action('woocommerce_before_calculate_totals', function ($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    $deposit_product_id = zarvel_get_design_deposit_product_id();

    if (!$deposit_product_id || !$cart) {
        return;
    }

    $kept_deposit_key = '';

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        if (
            empty($cart_item['product_id']) ||
            (int) $cart_item['product_id'] !== $deposit_product_id ||
            empty($cart_item['data'])
        ) {
            continue;
        }

        if ($kept_deposit_key) {
            $cart->remove_cart_item($cart_item_key);
            continue;
        }

        $kept_deposit_key = $cart_item_key;
        $deposit_amount = zarvel_get_design_request_deposit_amount(
            !empty($cart_item['zc_design_deposit_request_id'])
                ? absint($cart_item['zc_design_deposit_request_id'])
                : 0
        );

        $cart_item['data']->set_price($deposit_amount);
    }
}, 20);

function zarvel_cart_has_design_deposit() {
    if (!function_exists('WC') || !WC()->cart) {
        return false;
    }

    $deposit_product_id = zarvel_get_design_deposit_product_id();

    if (!$deposit_product_id) {
        return false;
    }

    foreach (WC()->cart->get_cart() as $cart_item) {
        if (!empty($cart_item['product_id']) && (int) $cart_item['product_id'] === $deposit_product_id) {
            return true;
        }
    }

    return false;
}

function zarvel_cart_has_non_deposit_product() {
    if (!function_exists('WC') || !WC()->cart) {
        return false;
    }

    $deposit_product_id = zarvel_get_design_deposit_product_id();

    foreach (WC()->cart->get_cart() as $cart_item) {
        if (empty($cart_item['product_id']) || (int) $cart_item['product_id'] !== $deposit_product_id) {
            return true;
        }
    }

    return false;
}

function zarvel_is_local_storefront() {
    return
        (function_exists('wp_get_environment_type') && wp_get_environment_type() === 'local') ||
        strpos((string) home_url('/'), '.local') !== false ||
        strpos((string) home_url('/'), 'localhost') !== false;
}

function zarvel_order_has_design_deposit($order) {
    if (!$order || !is_a($order, 'WC_Order')) {
        return false;
    }

    $deposit_product_id = zarvel_get_design_deposit_product_id();

    foreach ($order->get_items() as $item) {
        if ((int) $item->get_product_id() === $deposit_product_id) {
            return true;
        }
    }

    return false;
}

add_filter('woocommerce_bacs_process_payment_order_status', function ($status, $order) {
    if (zarvel_is_local_storefront() && zarvel_order_has_design_deposit($order)) {
        return 'completed';
    }

    return $status;
}, 20, 2);

add_action('woocommerce_thankyou_bacs', function ($order_id) {
    if (!zarvel_is_local_storefront() || !function_exists('wc_get_order')) {
        return;
    }

    $order = wc_get_order($order_id);

    if ($order && !$order->is_paid() && zarvel_order_has_design_deposit($order)) {
        $order->payment_complete('local-design-deposit-test');
    }
}, 1);

function zarvel_get_private_product_customer_email($product_id) {
    return strtolower(sanitize_email((string) get_post_meta($product_id, '_zarvel_private_customer_email', true)));
}

function zarvel_is_private_product_customer($product_id) {
    $customer_email = zarvel_get_private_product_customer_email($product_id);
    $portal_email = function_exists('zarvel_portal_current_customer_email')
        ? zarvel_portal_current_customer_email()
        : '';

    if (!$customer_email || !$portal_email) {
        return false;
    }

    return strtolower($customer_email) === strtolower($portal_email);
}

/**
 * Assign a custom WooCommerce product to one account by email.
 */
add_action('add_meta_boxes_product', function () {
    add_meta_box(
        'zarvel-private-customer-product',
        __('Zarvel Private Customer Product', 'zarvel-creative'),
        function ($post) {
            $customer_email = zarvel_get_private_product_customer_email($post->ID);
            wp_nonce_field('zarvel_save_private_customer_product', 'zarvel_private_customer_product_nonce');
            ?>
            <p>
                <label for="zarvel-private-customer-email">
                    <?php esc_html_e('Customer account email', 'zarvel-creative'); ?>
                </label>
            </p>
            <input
                id="zarvel-private-customer-email"
                class="widefat"
                type="email"
                name="zarvel_private_customer_email"
                value="<?php echo esc_attr($customer_email); ?>"
                placeholder="customer@example.com"
            >
            <p class="description">
                <?php esc_html_e('When this is set, only that logged-in customer and shop admins can open or buy this product.', 'zarvel-creative'); ?>
            </p>
            <?php
        },
        'product',
        'side',
        'default'
    );
});

add_action('save_post_product', function ($post_id) {
    if (
        empty($_POST['zarvel_private_customer_product_nonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['zarvel_private_customer_product_nonce'])),
            'zarvel_save_private_customer_product'
        ) ||
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
        !current_user_can('edit_post', $post_id)
    ) {
        return;
    }

    $customer_email = isset($_POST['zarvel_private_customer_email'])
        ? strtolower(sanitize_email(wp_unslash($_POST['zarvel_private_customer_email'])))
        : '';

    if ($customer_email) {
        update_post_meta($post_id, '_zarvel_private_customer_email', $customer_email);
        return;
    }

    delete_post_meta($post_id, '_zarvel_private_customer_email');
});

add_filter('woocommerce_product_is_visible', function ($visible, $product_id) {
    return zarvel_get_private_product_customer_email($product_id) ? false : $visible;
}, 20, 2);

add_action('template_redirect', function () {
    if (!function_exists('is_product') || !is_product() || current_user_can('manage_woocommerce')) {
        return;
    }

    $product_id = get_queried_object_id();

    if (!$product_id || !zarvel_get_private_product_customer_email($product_id)) {
        return;
    }

    if (zarvel_is_private_product_customer($product_id)) {
        return;
    }

    if (!function_exists('zarvel_portal_current_customer_email') || !zarvel_portal_current_customer_email()) {
        wp_safe_redirect(add_query_arg('redirect_to', get_permalink($product_id), home_url('/my-account/')));
        exit;
    }

    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    nocache_headers();
});

add_filter('woocommerce_add_to_cart_validation', function ($passed, $product_id) {
    if (
        current_user_can('manage_woocommerce') ||
        !zarvel_get_private_product_customer_email($product_id) ||
        zarvel_is_private_product_customer($product_id)
    ) {
        return $passed;
    }

    wc_add_notice(__('This custom product is available only in the assigned customer account.', 'zarvel-creative'), 'error');

    return false;
}, 20, 2);

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
 * - If no non-US rate is returned, add a quantity-based fallback estimate so
 *   checkout never completes with accidental zero shipping.
 */
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

function zarvel_package_has_paid_shipping_rate($rates) {
    if (!is_array($rates)) {
        return false;
    }

    foreach ($rates as $rate) {
        if (is_object($rate) && method_exists($rate, 'get_cost') && (float) $rate->get_cost() > 0) {
            return true;
        }
    }

    return false;
}

function zarvel_cart_has_paid_shipping_rate() {
    if (!function_exists('WC') || !WC()->shipping()) {
        return false;
    }

    foreach (WC()->shipping()->get_packages() as $package) {
        if (!empty($package['rates']) && zarvel_package_has_paid_shipping_rate($package['rates'])) {
            return true;
        }
    }

    return false;
}

function zarvel_get_package_item_count($package) {
    if (empty($package['contents']) || !is_array($package['contents'])) {
        return 1;
    }

    $count = 0;

    foreach ($package['contents'] as $cart_item) {
        $product = isset($cart_item['data']) ? $cart_item['data'] : null;

        if (
            is_object($product) &&
            method_exists($product, 'needs_shipping') &&
            !$product->needs_shipping()
        ) {
            continue;
        }

        $count += !empty($cart_item['quantity']) ? max(1, (int) $cart_item['quantity']) : 1;
    }

    return max(1, $count);
}

function zarvel_get_international_shipping_fallback($country, $package) {
    $country = strtoupper((string) $country);
    $item_count = zarvel_get_package_item_count($package);
    $additional_count = max(0, $item_count - 1);
    $rates = zarvel_get_printful_region_shipping_rates($country);

    return (float) $rates['base'] + ($additional_count * (float) $rates['additional']);
}

function zarvel_get_printful_region_shipping_rates($country) {
    $country = strtoupper((string) $country);

    $regions = array(
        'canada' => array(
            'countries'  => array('CA'),
            'base'       => 8.29,
            'additional' => 1.95,
        ),
        'uk' => array(
            'countries'  => array('GB'),
            'base'       => 4.59,
            'additional' => 1.50,
        ),
        'efta' => array(
            'countries'  => array('CH', 'IS', 'LI', 'NO'),
            'base'       => 7.99,
            'additional' => 1.95,
        ),
        'europe' => array(
            'countries'  => array(
                'AD', 'AL', 'AT', 'BA', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE',
                'ES', 'FI', 'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV',
                'MC', 'MD', 'ME', 'MK', 'MT', 'NL', 'PL', 'PT', 'RO', 'RS', 'SE',
                'SI', 'SK', 'SM', 'TR', 'UA', 'VA',
            ),
            'base'       => 4.79,
            'additional' => 1.45,
        ),
        'australia_new_zealand' => array(
            'countries'  => array('AU', 'NZ'),
            'base'       => 7.19,
            'additional' => 1.30,
        ),
        'japan' => array(
            'countries'  => array('JP'),
            'base'       => 4.39,
            'additional' => 1.50,
        ),
        'brazil' => array(
            'countries'  => array('BR'),
            'base'       => 4.49,
            'additional' => 2.50,
        ),
        'worldwide' => array(
            'countries'  => array(),
            'base'       => 11.99,
            'additional' => 6.00,
        ),
    );

    foreach ($regions as $region) {
        if (in_array($country, $region['countries'], true)) {
            return array(
                'base'       => $region['base'],
                'additional' => $region['additional'],
            );
        }
    }

    return array(
        'base'       => $regions['worldwide']['base'],
        'additional' => $regions['worldwide']['additional'],
    );
}

function zarvel_refresh_cart_totals() {
    if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) {
        return;
    }

    WC()->cart->calculate_shipping();
    WC()->cart->calculate_totals();
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
        method_exists($product, 'get_id') &&
        (int) $product->get_id() === zarvel_get_design_deposit_product_id()
    ) {
        return false;
    }

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
        if (zarvel_cart_has_design_deposit() && !zarvel_cart_has_non_deposit_product()) {
            return false;
        }

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

    if (!zarvel_package_has_paid_shipping_rate($rates)) {
        $fallback_rate_id = 'zarvel_international_printful_estimate';
        $rates[$fallback_rate_id] = new WC_Shipping_Rate(
            $fallback_rate_id,
            __('Standard International Shipping', 'zarvel-creative'),
            zarvel_get_international_shipping_fallback($destination_country, $package),
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

    if ($country && WC()->customer) {
        WC()->customer->set_shipping_country($country);
    }

    WC()->cart->calculate_shipping();
    WC()->cart->calculate_totals();

    if (
        $country &&
        $country !== 'US' &&
        (float) WC()->cart->get_shipping_total() <= 0 &&
        !zarvel_cart_has_paid_shipping_rate()
    ) {
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
    if (!empty($values['zc_design_deposit_request_id'])) {
        $item->add_meta_data('_zc_design_request_id', absint($values['zc_design_deposit_request_id']), true);
        $item->add_meta_data(
            __('Design request', 'zarvel-creative'),
            '#' . absint($values['zc_design_deposit_request_id'])
        );
    }

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

/**
 * Link a paid design deposit back to its saved request and checkout account.
 */
add_action('woocommerce_checkout_order_processed', function ($order_id, $posted_data, $order) {
    if (!$order || !is_a($order, 'WC_Order')) {
        return;
    }

    foreach ($order->get_items() as $item) {
        $design_request_id = absint($item->get_meta('_zc_design_request_id', true));

        if (!$design_request_id) {
            $request_meta = (string) $item->get_meta(__('Design request', 'zarvel-creative'), true);
            $design_request_id = absint(ltrim($request_meta, '#'));
        }

        if (!$design_request_id || get_post_type($design_request_id) !== ZARVEL_DESIGN_REQUEST_TYPE) {
            continue;
        }

        update_post_meta($design_request_id, '_design_deposit_order_id', $order_id);
        update_post_meta($design_request_id, '_customer_user_id', $order->get_customer_id());
        update_post_meta($design_request_id, '_customer_email', sanitize_email($order->get_billing_email()));
    }
}, 20, 3);
