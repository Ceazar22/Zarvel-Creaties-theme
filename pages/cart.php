<?php
defined('ABSPATH') || exit;

get_header();

$cart_items = array();
$cart_count = 0;
$cart_subtotal = 0;
$cart_total = 0;
$cart_shipping_label = '&mdash;';
$cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart');
$checkout_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout');

if (function_exists('WC') && WC()->cart) {
    if (function_exists('zarvel_refresh_cart_totals')) {
        zarvel_refresh_cart_totals();
    }

    $cart_items = WC()->cart->get_cart();
    $cart_count = WC()->cart->get_cart_contents_count();
    $cart_subtotal = (float) WC()->cart->get_subtotal();
    $cart_total = (float) WC()->cart->get_total('edit');
    $cart_shipping_label = function_exists('zarvel_get_cart_shipping_label') ? zarvel_get_cart_shipping_label() : $cart_shipping_label;
}
?>

<main class="zc-cart-page">
  <section class="zc-cart-hero">
    <div class="zc-cart-container">
      <p>Your cart</p>
      <h1>Review your custom products.</h1>
    </div>
  </section>

  <section class="zc-cart-shell">
    <div class="zc-cart-container zc-cart-grid">
      <div class="zc-cart-list">
        <?php if (!empty($cart_items)) : ?>
          <?php foreach ($cart_items as $cart_item_key => $cart_item) : ?>
            <?php
            $product = isset($cart_item['data']) ? $cart_item['data'] : null;

            if (!$product || !$product->exists()) {
                continue;
            }

            $quantity = isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 1;
            $decrease_url = wp_nonce_url(
                add_query_arg(
                    array(
                        'zc_cart_item' => rawurlencode($cart_item_key),
                        'zc_qty'       => max(0, $quantity - 1),
                    )
                ),
                'zc_sidecart_qty_' . $cart_item_key
            );
            $increase_url = wp_nonce_url(
                add_query_arg(
                    array(
                        'zc_cart_item' => rawurlencode($cart_item_key),
                        'zc_qty'       => $quantity + 1,
                    )
                ),
                'zc_sidecart_qty_' . $cart_item_key
            );
            $remove_url = function_exists('wc_get_cart_remove_url') ? wc_get_cart_remove_url($cart_item_key) : $cart_url;
            ?>
            <article class="zc-cart-item">
              <a class="zc-cart-item__image" href="<?php echo esc_url($product->get_permalink()); ?>">
                <?php echo wp_kses_post($product->get_image('woocommerce_thumbnail')); ?>
              </a>

              <div class="zc-cart-item__body">
                <h2><?php echo esc_html($product->get_name()); ?></h2>

                <?php if (!empty($cart_item['zc_design_request'])) : ?>
                  <p>Design note: Customer wants a design.</p>
                <?php elseif (!empty($cart_item['zc_custom_design'])) : ?>
                  <p>Custom design: <?php echo esc_html(!empty($cart_item['zc_design_title']) ? $cart_item['zc_design_title'] : 'Untitled Design'); ?></p>
                <?php endif; ?>

                <div class="zc-cart-item__controls">
                  <div class="zc-cart-qty">
                    <a href="<?php echo esc_url($decrease_url); ?>" aria-label="Decrease quantity">−</a>
                    <b><?php echo esc_html($quantity); ?></b>
                    <a href="<?php echo esc_url($increase_url); ?>" aria-label="Increase quantity">+</a>
                  </div>

                  <a class="zc-cart-remove" href="<?php echo esc_url($remove_url); ?>">Remove</a>
                </div>
              </div>

              <strong><?php echo wp_kses_post(WC()->cart->get_product_price($product)); ?></strong>
            </article>
          <?php endforeach; ?>
        <?php else : ?>
          <div class="zc-cart-empty">
            <h2>Your cart is empty.</h2>
            <p>Start with a blank product or pick something from the shop.</p>
            <a href="<?php echo esc_url(home_url('/shop')); ?>">Shop Products</a>
          </div>
        <?php endif; ?>
      </div>

      <aside class="zc-cart-summary">
        <h2>Summary</h2>

        <div>
          <span>Subtotal (<?php echo esc_html($cart_count); ?>)</span>
          <strong><?php echo wp_kses_post(function_exists('wc_price') ? wc_price($cart_subtotal) : '$' . number_format($cart_subtotal, 2)); ?></strong>
        </div>

        <div>
          <span>Shipping</span>
          <strong><?php echo wp_kses_post($cart_shipping_label); ?></strong>
        </div>

        <hr>

        <div class="zc-cart-summary__total">
          <span>Total</span>
          <strong><?php echo wp_kses_post(function_exists('zarvel_get_cart_total_html') ? zarvel_get_cart_total_html() : (function_exists('wc_price') ? wc_price($cart_total ?: $cart_subtotal) : '$' . number_format($cart_total ?: $cart_subtotal, 2))); ?></strong>
        </div>

        <a class="zc-cart-checkout" href="<?php echo esc_url($checkout_url); ?>">Checkout</a>
        <a class="zc-cart-continue" href="<?php echo esc_url(home_url('/shop')); ?>">Continue Shopping</a>
      </aside>
    </div>
  </section>
</main>

<style>
.zc-cart-page {
  background: #f7f7f7;
  color: #050505;
}

.zc-cart-container {
  width: min(100% - 44px, 1240px);
  margin: 0 auto;
}

.zc-cart-hero {
  background: #050505;
  color: #ffffff;
  padding: 58px 0;
}

.zc-cart-hero p {
  margin: 0 0 12px;
  color: #ff5b1a;
  font-size: 13px;
  font-weight: 950;
  text-transform: uppercase;
}

.zc-cart-hero h1 {
  margin: 0;
  max-width: 720px;
  font-size: clamp(38px, 6vw, 72px);
  line-height: 0.95;
  font-weight: 950;
}

.zc-cart-shell {
  padding: 34px 0 76px;
}

.zc-cart-grid {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 360px;
  gap: 24px;
  align-items: start;
}

.zc-cart-list,
.zc-cart-summary {
  background: #ffffff;
  border: 1px solid #e9e9e9;
  border-radius: 14px;
  box-shadow: 0 20px 58px rgba(0, 0, 0, 0.06);
}

.zc-cart-list {
  padding: 10px 28px;
}

.zc-cart-item {
  display: grid;
  grid-template-columns: 118px minmax(0, 1fr) auto;
  gap: 22px;
  align-items: center;
  padding: 24px 0;
  border-bottom: 1px solid #ededed;
}

.zc-cart-item:last-child {
  border-bottom: 0;
}

.zc-cart-item__image {
  width: 118px;
  height: 118px;
  border: 1px solid #e3e3e3;
  border-radius: 12px;
  background: #f8f8f8;
  display: flex;
  align-items: center;
  justify-content: center;
}

.zc-cart-item__image img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.zc-cart-item h2 {
  margin: 0;
  font-size: 22px;
  line-height: 1.2;
  font-weight: 950;
}

.zc-cart-item p {
  margin: 8px 0 0;
  color: #666666;
  font-size: 14px;
  font-weight: 600;
}

.zc-cart-item strong {
  color: #ff5b1a;
  font-size: 20px;
  font-weight: 950;
}

.zc-cart-item__controls {
  display: flex;
  align-items: center;
  gap: 16px;
  margin-top: 18px;
}

.zc-cart-qty {
  display: inline-grid;
  grid-template-columns: 38px 38px 38px;
  min-height: 42px;
  border: 1px solid #dcdcdc;
  border-radius: 999px;
  overflow: hidden;
}

.zc-cart-qty a,
.zc-cart-qty b {
  color: #050505;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  font-weight: 900;
}

.zc-cart-remove {
  color: #777777;
  font-size: 13px;
  font-weight: 900;
  text-transform: uppercase;
}

.zc-cart-summary {
  position: sticky;
  top: 118px;
  padding: 28px;
}

.zc-cart-summary h2 {
  margin: 0 0 24px;
  font-size: 30px;
  font-weight: 950;
  text-transform: uppercase;
}

.zc-cart-summary div {
  display: flex;
  justify-content: space-between;
  gap: 18px;
  margin-bottom: 16px;
  font-size: 15px;
  font-weight: 700;
}

.zc-cart-summary hr {
  border: 0;
  border-top: 1px solid #e5e5e5;
  margin: 18px 0;
}

.zc-cart-summary__total span,
.zc-cart-summary__total strong {
  font-size: 26px;
  font-weight: 950;
}

.zc-cart-summary__total strong {
  color: #ff5b1a;
}

.zc-cart-checkout,
.zc-cart-continue,
.zc-cart-empty a {
  min-height: 54px;
  border-radius: 8px;
  text-decoration: none;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 950;
  text-transform: uppercase;
}

.zc-cart-checkout,
.zc-cart-empty a {
  background: #ff5b1a;
  color: #ffffff;
}

.zc-cart-continue {
  margin-top: 12px;
  border: 1px solid #050505;
  color: #050505;
}

.zc-cart-empty {
  min-height: 360px;
  display: grid;
  place-items: center;
  text-align: center;
  padding: 44px 20px;
}

.zc-cart-empty h2 {
  margin: 0 0 8px;
  font-size: 32px;
  font-weight: 950;
}

.zc-cart-empty p {
  margin: 0 0 22px;
  color: #666666;
}

@media (max-width: 920px) {
  .zc-cart-grid {
    grid-template-columns: 1fr;
  }

  .zc-cart-summary {
    position: static;
  }
}

@media (max-width: 640px) {
  .zc-cart-container {
    width: min(100% - 28px, 1240px);
  }

  .zc-cart-item {
    grid-template-columns: 92px minmax(0, 1fr);
  }

  .zc-cart-item__image {
    width: 92px;
    height: 92px;
  }

  .zc-cart-item strong {
    grid-column: 2;
  }
}
</style>

<?php
get_footer();
