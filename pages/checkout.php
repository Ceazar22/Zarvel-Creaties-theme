<?php
defined('ABSPATH') || exit;

get_header();

$cart_count = 0;
$cart_subtotal = 0;
$cart_shipping_label = '&mdash;';
$cart_total_html = function_exists('wc_price') ? wc_price(0) : '$0.00';
$cart_items = array();
$checkout_ready = function_exists('WC') && WC()->cart && !WC()->cart->is_empty();

if (function_exists('WC') && WC()->cart) {
    if (function_exists('zarvel_refresh_cart_totals')) {
        zarvel_refresh_cart_totals();
    }

    $cart_count = WC()->cart->get_cart_contents_count();
    $cart_subtotal = (float) WC()->cart->get_subtotal();
    $cart_items = WC()->cart->get_cart();
    $cart_shipping_label = function_exists('zarvel_get_cart_shipping_label') ? zarvel_get_cart_shipping_label() : $cart_shipping_label;
    $cart_total_html = function_exists('zarvel_get_cart_total_html') ? zarvel_get_cart_total_html() : $cart_total_html;
}
?>

<main class="zc-checkout-page">
  <section class="zc-checkout-hero">
    <div class="zc-checkout-container">
      <p>Secure checkout</p>
      <h1>Finish your custom order.</h1>
      <span>Review your products, enter your details, and place your order securely.</span>
    </div>
  </section>

  <section class="zc-checkout-shell">
    <div class="zc-checkout-container zc-checkout-grid">
      <div class="zc-checkout-card zc-checkout-card--form">
        <?php if ($checkout_ready) : ?>
          <?php echo do_shortcode('[woocommerce_checkout]'); ?>
        <?php else : ?>
          <div class="zc-checkout-empty">
            <h2>Your cart is empty.</h2>
            <p>Add a custom product before checking out.</p>
            <a href="<?php echo esc_url(home_url('/shop')); ?>">Shop Products</a>
          </div>
        <?php endif; ?>
      </div>

      <aside class="zc-checkout-card zc-checkout-summary">
        <div class="zc-checkout-summary__head">
          <h2>Order Summary</h2>
          <span><?php echo esc_html($cart_count); ?></span>
        </div>

        <?php if (!empty($cart_items)) : ?>
          <div class="zc-checkout-items">
            <?php foreach ($cart_items as $cart_item) : ?>
              <?php
              $product = isset($cart_item['data']) ? $cart_item['data'] : null;

              if (!$product || !$product->exists()) {
                  continue;
              }

              $quantity = isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 1;
              ?>
              <article class="zc-checkout-item">
                <div class="zc-checkout-item__image">
                  <?php echo wp_kses_post($product->get_image('woocommerce_thumbnail')); ?>
                  <b><?php echo esc_html($quantity); ?></b>
                </div>

                <div>
                  <h3><?php echo esc_html($product->get_name()); ?></h3>
                  <?php if (!empty($cart_item['zc_design_request'])) : ?>
                    <p>Design help requested</p>
                  <?php elseif (!empty($cart_item['zc_custom_design'])) : ?>
                    <p>Custom design</p>
                  <?php endif; ?>
                </div>

                <strong><?php echo wp_kses_post(WC()->cart->get_product_price($product)); ?></strong>
              </article>
            <?php endforeach; ?>
          </div>
        <?php else : ?>
          <p class="zc-checkout-summary__empty">No products yet.</p>
        <?php endif; ?>

        <div class="zc-checkout-total">
          <div>
            <span>Subtotal</span>
            <strong><?php echo wp_kses_post(function_exists('wc_price') ? wc_price($cart_subtotal) : '$' . number_format($cart_subtotal, 2)); ?></strong>
          </div>
          <div>
            <span>Shipping</span>
            <strong><?php echo wp_kses_post($cart_shipping_label); ?></strong>
          </div>
          <hr>
          <div class="zc-checkout-total__final">
            <span>Total</span>
            <strong><?php echo wp_kses_post($cart_total_html); ?></strong>
          </div>
        </div>

        <div class="zc-checkout-trust">
          <span>SSL encrypted</span>
          <span>Made on demand</span>
          <span>30-day support</span>
        </div>
      </aside>
    </div>
  </section>
</main>

<style>
.zc-checkout-page {
  background: #f6f6f6;
  color: #050505;
}

.zc-checkout-container {
  width: min(100% - 44px, 1240px);
  margin: 0 auto;
}

.zc-checkout-hero {
  background: #050505;
  color: #ffffff;
  padding: 58px 0 64px;
}

.zc-checkout-hero p {
  margin: 0 0 12px;
  color: #ff5b1a;
  font-size: 13px;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.8px;
}

.zc-checkout-hero h1 {
  margin: 0;
  max-width: 680px;
  font-size: clamp(38px, 6vw, 72px);
  line-height: 0.95;
  font-weight: 950;
  letter-spacing: 0;
}

.zc-checkout-hero span {
  display: block;
  margin-top: 18px;
  max-width: 540px;
  color: rgba(255, 255, 255, 0.76);
  font-size: 16px;
  line-height: 1.55;
  font-weight: 500;
}

.zc-checkout-shell {
  padding: 34px 0 76px;
}

.zc-checkout-grid {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 390px;
  gap: 24px;
  align-items: start;
}

.zc-checkout-card {
  background: #ffffff;
  border: 1px solid #e8e8e8;
  border-radius: 14px;
  box-shadow: 0 22px 60px rgba(0, 0, 0, 0.06);
}

.zc-checkout-card--form {
  padding: 28px;
}

.zc-checkout-card--form .woocommerce {
  font-family: inherit;
}

.zc-checkout-card--form h3,
.zc-checkout-card--form #order_review_heading {
  margin: 0 0 18px;
  font-size: 22px;
  font-weight: 950;
  letter-spacing: 0;
}

.zc-checkout-card--form input.input-text,
.zc-checkout-card--form textarea,
.zc-checkout-card--form select {
  min-height: 48px;
  border: 1px solid #dcdcdc;
  border-radius: 8px;
  padding: 12px 14px;
  background: #ffffff;
  color: #111111;
}

.zc-checkout-card--form textarea {
  min-height: 110px;
}

.zc-checkout-card--form .button,
.zc-checkout-empty a {
  min-height: 54px;
  border: 0;
  border-radius: 8px;
  padding: 0 28px;
  background: #ff5b1a;
  color: #ffffff;
  font-size: 14px;
  font-weight: 950;
  text-transform: uppercase;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.zc-checkout-card--form #place_order {
  width: 100%;
  margin-top: 12px;
}

.zc-checkout-summary {
  position: sticky;
  top: 118px;
  padding: 28px;
}

.zc-checkout-summary__head {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 22px;
}

.zc-checkout-summary__head h2 {
  margin: 0;
  font-size: 28px;
  line-height: 1;
  font-weight: 950;
  text-transform: uppercase;
}

.zc-checkout-summary__head span {
  width: 34px;
  height: 34px;
  border-radius: 999px;
  background: #ff5b1a;
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-weight: 950;
}

.zc-checkout-items {
  display: grid;
  gap: 18px;
}

.zc-checkout-item {
  display: grid;
  grid-template-columns: 78px minmax(0, 1fr) auto;
  gap: 14px;
  align-items: center;
  padding-bottom: 18px;
  border-bottom: 1px solid #ededed;
}

.zc-checkout-item__image {
  position: relative;
  width: 78px;
  height: 78px;
  border: 1px solid #e2e2e2;
  border-radius: 10px;
  background: #f7f7f7;
  overflow: hidden;
}

.zc-checkout-item__image img {
  width: 100%;
  height: 100%;
  object-fit: contain;
}

.zc-checkout-item__image b {
  position: absolute;
  top: 6px;
  right: 6px;
  min-width: 22px;
  height: 22px;
  border-radius: 999px;
  background: #050505;
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
}

.zc-checkout-item h3 {
  margin: 0;
  font-size: 15px;
  line-height: 1.25;
  font-weight: 900;
}

.zc-checkout-item p,
.zc-checkout-summary__empty {
  margin: 5px 0 0;
  color: #666666;
  font-size: 13px;
  font-weight: 500;
}

.zc-checkout-item strong {
  color: #ff5b1a;
  font-size: 15px;
  font-weight: 950;
}

.zc-checkout-total {
  margin-top: 24px;
  padding: 22px;
  border-radius: 12px;
  background: #fff7f2;
}

.zc-checkout-total div {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 14px;
  font-size: 15px;
  font-weight: 700;
}

.zc-checkout-total hr {
  border: 0;
  border-top: 1px solid #e5d9d0;
  margin: 14px 0;
}

.zc-checkout-total__final span,
.zc-checkout-total__final strong {
  font-size: 24px;
  font-weight: 950;
}

.zc-checkout-total__final strong {
  color: #ff5b1a;
}

.zc-checkout-trust {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 10px;
  margin-top: 18px;
  color: #555555;
  font-size: 12px;
  line-height: 1.25;
  font-weight: 800;
}

.zc-checkout-empty {
  min-height: 360px;
  display: grid;
  place-items: center;
  text-align: center;
}

.zc-checkout-empty h2 {
  margin: 0 0 8px;
  font-size: 30px;
  font-weight: 950;
}

.zc-checkout-empty p {
  margin: 0 0 22px;
  color: #666666;
  font-size: 15px;
}

@media (max-width: 980px) {
  .zc-checkout-grid {
    grid-template-columns: 1fr;
  }

  .zc-checkout-summary {
    position: static;
  }
}

@media (max-width: 640px) {
  .zc-checkout-container {
    width: min(100% - 28px, 1240px);
  }

  .zc-checkout-card--form,
  .zc-checkout-summary {
    padding: 20px;
  }

  .zc-checkout-item {
    grid-template-columns: 68px minmax(0, 1fr);
  }

  .zc-checkout-item strong {
    grid-column: 2;
  }
}
</style>

<?php
get_footer();
