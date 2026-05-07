<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<header class="zc-header">

  <!-- Top Black Bar -->
  <div class="zc-topbar">
    <div class="zc-container zc-topbar__inner">

      <div class="zc-topbar__left">
        <span>✪ High Quality Prints</span>
        <span>◎ Made on Demand</span>
        <span>◉ Fast & Reliable Shipping</span>
      </div>

      <div class="zc-topbar__right">
        <span>Need help?</span>
        <a href="mailto:support@printly.com">support@printly.com</a>
        <a href="tel:+639123456789">+63 912 345 6789</a>
      </div>

    </div>
  </div>

  <!-- Main Header -->
  <div class="zc-main-header">
    <div class="zc-container zc-main-header__inner">

      <!-- Logo -->
      <a href="<?php echo esc_url(home_url('/')); ?>" class="zc-logo">
        <span class="zc-logo__main">PRINTLY</span>
        <span class="zc-logo__sub">CUSTOM. YOUR WAY.</span>
      </a>

      <!-- Mobile Toggle -->
      <button class="zc-mobile-toggle" type="button" aria-label="Open menu">
        <span></span>
        <span></span>
        <span></span>
      </button>

      <!-- Navigation -->
      <nav class="zc-nav">

        <div class="zc-nav-item zc-has-dropdown">
          <a href="<?php echo esc_url(home_url('/shop')); ?>" class="zc-nav-link">
            SHOP
            <svg class="zc-chevron" viewBox="0 0 24 24" width="13" height="13">
              <path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </a>

          <div class="zc-dropdown">
            <?php
            $zc_nav_categories = function_exists('zarvel_get_shop_categories')
              ? zarvel_get_shop_categories(4)
              : array();
            ?>

            <?php if (!empty($zc_nav_categories)) : ?>
              <?php foreach ($zc_nav_categories as $zc_nav_category) : ?>
                <?php $zc_nav_category_url = get_term_link($zc_nav_category); ?>
                <?php if (!is_wp_error($zc_nav_category_url)) : ?>
                  <a href="<?php echo esc_url($zc_nav_category_url); ?>">
                    <?php echo esc_html($zc_nav_category->name); ?>
                  </a>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php endif; ?>

            <a href="<?php echo esc_url(home_url('/shop')); ?>">All Products</a>
          </div>
        </div>

        <a href="<?php echo esc_url(home_url('/our-services')); ?>" class="zc-nav-link">OUR SERVICES</a>
        <a href="<?php echo esc_url(home_url('/about-us')); ?>" class="zc-nav-link">ABOUT</a>
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="zc-nav-link">CONTACT</a>

      </nav>

      <!-- Right Icons -->
      <div class="zc-header-actions">

        <a href="<?php echo esc_url(home_url('/?s=')); ?>" class="zc-icon-link" data-zc-search-open aria-label="Search">
          <svg viewBox="0 0 24 24" width="22" height="22">
            <path d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
          </svg>
        </a>

        <a href="<?php echo esc_url(home_url('/my-account')); ?>" class="zc-icon-link" aria-label="Account">
          <svg viewBox="0 0 24 24" width="22" height="22">
            <path d="M20 21a8 8 0 0 0-16 0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <circle cx="12" cy="7" r="4" fill="none" stroke="currentColor" stroke-width="2"/>
          </svg>
        </a>

        <a href="<?php echo esc_url(function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart')); ?>" class="zc-cart-link" data-zc-sidecart-open aria-label="Cart">
          <svg viewBox="0 0 24 24" width="23" height="23">
            <path d="M6 6h15l-2 9H8L6 6z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
            <path d="M6 6 5 2H2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            <circle cx="9" cy="21" r="1"/>
            <circle cx="18" cy="21" r="1"/>
          </svg>

          <span class="zc-cart-count">
            <?php
              if (function_exists('WC') && WC()->cart) {
                echo esc_html(WC()->cart->get_cart_contents_count());
              } else {
                echo '0';
              }
            ?>
          </span>
        </a>

        <a href="<?php echo esc_url(home_url('/customize')); ?>" class="zc-customize-btn">
          START CUSTOMIZING
        </a>

      </div>

    </div>
  </div>

</header>

<div class="zc-search-modal" data-zc-search-modal aria-hidden="true">
  <button class="zc-search-modal__overlay" type="button" data-zc-search-close aria-label="Close search"></button>

  <div class="zc-search-modal__panel" role="dialog" aria-modal="true" aria-label="Search products">
    <button class="zc-search-modal__close" type="button" data-zc-search-close aria-label="Close search">
      <span></span>
      <span></span>
    </button>

    <p>Search Printly</p>
    <h2>Find your next custom product.</h2>

    <form class="zc-search-modal__form" action="<?php echo esc_url(home_url('/')); ?>" method="get" role="search">
      <label class="screen-reader-text" for="zc-search-field">Search products</label>
      <input id="zc-search-field" type="search" name="s" placeholder="Search t-shirts, hoodies, mugs..." autocomplete="off" data-zc-search-field>
      <input type="hidden" name="post_type" value="product">
      <button type="submit">
        <svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
          <path d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
        Search
      </button>
    </form>

    <div class="zc-search-modal__quick">
      <span>Popular</span>
      <a href="<?php echo esc_url(home_url('/?s=t-shirt&post_type=product')); ?>">T-shirts</a>
      <a href="<?php echo esc_url(home_url('/?s=hoodie&post_type=product')); ?>">Hoodies</a>
      <a href="<?php echo esc_url(home_url('/?s=mug&post_type=product')); ?>">Mugs</a>
      <a href="<?php echo esc_url(home_url('/?s=cap&post_type=product')); ?>">Caps</a>
    </div>
  </div>
</div>

<?php
$zc_cart_count = 0;
$zc_cart_subtotal = 0;
$zc_cart_items = array();
$zc_cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/cart');
$zc_checkout_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/checkout');

if (function_exists('WC') && WC()->cart) {
  $zc_cart_count = WC()->cart->get_cart_contents_count();
  $zc_cart_subtotal = (float) WC()->cart->get_subtotal();
  $zc_cart_items = WC()->cart->get_cart();
}

$zc_cart_subtotal_html = function_exists('wc_price')
  ? wc_price($zc_cart_subtotal)
  : '$' . number_format($zc_cart_subtotal, 2);
?>

<div class="zc-sidecart" data-zc-sidecart aria-hidden="true">
  <button class="zc-sidecart__overlay" type="button" data-zc-sidecart-close aria-label="Close cart"></button>

  <aside class="zc-sidecart__panel" role="dialog" aria-modal="true" aria-label="Shopping cart">
    <div class="zc-sidecart__header">
      <h2>Your Cart</h2>
      <span class="zc-sidecart__count"><?php echo esc_html($zc_cart_count); ?></span>
      <button class="zc-sidecart__close" type="button" data-zc-sidecart-close aria-label="Close cart">
        <span></span>
        <span></span>
      </button>
    </div>

    <div class="zc-sidecart__shipping">
      <div class="zc-sidecart__ship-icon">
        <svg viewBox="0 0 24 24" width="30" height="30" aria-hidden="true">
          <path d="M3 7h11v9H3z" fill="none" stroke="currentColor" stroke-width="1.8"/>
          <path d="M14 10h4l3 3v3h-7z" fill="none" stroke="currentColor" stroke-width="1.8"/>
          <circle cx="7" cy="18" r="2" fill="none" stroke="currentColor" stroke-width="1.8"/>
          <circle cx="18" cy="18" r="2" fill="none" stroke="currentColor" stroke-width="1.8"/>
        </svg>
      </div>
      <div class="zc-sidecart__ship-copy">
        <strong><span>FREE</span> shipping in the USA</strong>
        <div class="zc-sidecart__progress">
          <span style="width: 100%;"></span>
        </div>
        <p>
          International shipping is calculated at checkout using Printful rates.
        </p>
      </div>
    </div>

    <div class="zc-sidecart__items">
      <?php if (!empty($zc_cart_items)) : ?>
        <?php foreach ($zc_cart_items as $cart_item_key => $cart_item) : ?>
          <?php
          $zc_product = isset($cart_item['data']) ? $cart_item['data'] : null;

          if (!$zc_product || !$zc_product->exists()) {
            continue;
          }

          $zc_product_name = $zc_product->get_name();
          $zc_product_permalink = $zc_product->is_visible() ? $zc_product->get_permalink($cart_item) : '';
          $zc_product_price = WC()->cart->get_product_price($zc_product);
          $zc_product_image = $zc_product->get_image('woocommerce_thumbnail');
          $zc_quantity = isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 1;
          $zc_remove_url = function_exists('wc_get_cart_remove_url') ? wc_get_cart_remove_url($cart_item_key) : $zc_cart_url;
          $zc_decrease_url = wp_nonce_url(
            add_query_arg(
              array(
                'zc_cart_item' => rawurlencode($cart_item_key),
                'zc_qty'       => max(0, $zc_quantity - 1),
              )
            ),
            'zc_sidecart_qty_' . $cart_item_key
          );
          $zc_increase_url = wp_nonce_url(
            add_query_arg(
              array(
                'zc_cart_item' => rawurlencode($cart_item_key),
                'zc_qty'       => $zc_quantity + 1,
              )
            ),
            'zc_sidecart_qty_' . $cart_item_key
          );
          $zc_variation_rows = array();

          if (!empty($cart_item['variation']) && is_array($cart_item['variation'])) {
            foreach ($cart_item['variation'] as $attribute_name => $attribute_value) {
              if (!$attribute_value) {
                continue;
              }

              $attribute_taxonomy = str_replace('attribute_', '', $attribute_name);
              $attribute_label = wc_attribute_label($attribute_taxonomy);
              $attribute_display = $attribute_value;

              if (taxonomy_exists($attribute_taxonomy)) {
                $term = get_term_by('slug', $attribute_value, $attribute_taxonomy);
                if ($term && !is_wp_error($term)) {
                  $attribute_display = $term->name;
                }
              }

              $zc_variation_rows[] = $attribute_label . ': ' . $attribute_display;
            }
          }

          if (!empty($cart_item['zc_custom_design'])) {
            $zc_variation_rows[] = 'Custom design: ' . (!empty($cart_item['zc_design_title']) ? $cart_item['zc_design_title'] : 'Untitled Design');

            if (!empty($cart_item['zc_imprint'])) {
              $zc_variation_rows[] = 'Imprint: ' . $cart_item['zc_imprint'];
            }

            if (!empty($cart_item['zc_imprint_size'])) {
              $zc_variation_rows[] = 'Imprint size: ' . $cart_item['zc_imprint_size'];
            }
          }
          ?>
          <article class="zc-sidecart__item">
            <a class="zc-sidecart__thumb" href="<?php echo esc_url($zc_product_permalink ?: $zc_cart_url); ?>">
              <?php echo wp_kses_post($zc_product_image); ?>
            </a>

            <div class="zc-sidecart__item-main">
              <div class="zc-sidecart__item-top">
                <h3>
                  <a href="<?php echo esc_url($zc_product_permalink ?: $zc_cart_url); ?>">
                    <?php echo esc_html($zc_product_name); ?>
                  </a>
                </h3>
                <strong><?php echo wp_kses_post($zc_product_price); ?></strong>
              </div>

              <?php if (!empty($zc_variation_rows)) : ?>
                <div class="zc-sidecart__meta">
                  <?php foreach ($zc_variation_rows as $variation_row) : ?>
                    <span><?php echo esc_html($variation_row); ?></span>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <div class="zc-sidecart__item-bottom">
                <div class="zc-sidecart__qty" aria-label="Quantity">
                  <a href="<?php echo esc_url($zc_decrease_url); ?>" aria-label="Decrease quantity">−</a>
                  <b><?php echo esc_html($zc_quantity); ?></b>
                  <a href="<?php echo esc_url($zc_increase_url); ?>" aria-label="Increase quantity">+</a>
                </div>

                <a class="zc-sidecart__remove" href="<?php echo esc_url($zc_remove_url); ?>" aria-label="Remove <?php echo esc_attr($zc_product_name); ?>">
                  <svg viewBox="0 0 24 24" width="24" height="24" aria-hidden="true">
                    <path d="M4 7h16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M9 7V5h6v2" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M7 7l1 14h8l1-14" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M10 11v6M14 11v6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                  </svg>
                </a>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      <?php else : ?>
        <div class="zc-sidecart__empty">
          <h3>Your cart is empty.</h3>
          <p>Add a custom product to get started.</p>
          <a href="<?php echo esc_url(home_url('/shop')); ?>">Shop Products</a>
        </div>
      <?php endif; ?>
    </div>

    <div class="zc-sidecart__summary">
      <div>
        <span>Subtotal (<?php echo esc_html($zc_cart_count); ?> item<?php echo $zc_cart_count === 1 ? '' : 's'; ?>)</span>
        <strong><?php echo wp_kses_post($zc_cart_subtotal_html); ?></strong>
      </div>
      <div>
        <span>Shipping</span>
        <strong class="zc-sidecart__free">US free / Intl calculated</strong>
      </div>
      <hr>
      <div class="zc-sidecart__total">
        <span>Total</span>
        <strong><?php echo wp_kses_post($zc_cart_subtotal_html); ?></strong>
      </div>

      <a class="zc-sidecart__view-cart" href="<?php echo esc_url($zc_cart_url); ?>">View Cart</a>
      <a class="zc-sidecart__checkout" href="<?php echo esc_url($zc_checkout_url); ?>">
        Checkout
        <span>→</span>
      </a>
    </div>

    <div class="zc-sidecart__trust">
      <div>
        <span>▱</span>
        <p><b>Secure Checkout</b><br>SSL Encrypted</p>
      </div>
      <div>
        <span>◎</span>
        <p><b>Satisfaction Guarantee</b><br>30-Day Returns</p>
      </div>
      <div>
        <span>♧</span>
        <p><b>Customer Support</b><br>We're Here to Help</p>
      </div>
    </div>
  </aside>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const toggle = document.querySelector('.zc-mobile-toggle');
  const nav = document.querySelector('.zc-nav');
  const actions = document.querySelector('.zc-header-actions');
  const cartTriggers = document.querySelectorAll('[data-zc-sidecart-open]');
  const sidecart = document.querySelector('[data-zc-sidecart]');
  const sidecartCloseButtons = document.querySelectorAll('[data-zc-sidecart-close]');
  const searchTriggers = document.querySelectorAll('[data-zc-search-open]');
  const searchModal = document.querySelector('[data-zc-search-modal]');
  const searchCloseButtons = document.querySelectorAll('[data-zc-search-close]');
  const searchField = document.querySelector('[data-zc-search-field]');

  if (toggle && nav && actions) {
    toggle.addEventListener('click', function () {
      toggle.classList.toggle('is-active');
      nav.classList.toggle('is-open');
      actions.classList.toggle('is-open');
    });
  }

  function openSidecart() {
    if (!sidecart) return;
    sidecart.classList.add('is-open');
    sidecart.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.add('zc-sidecart-lock');
  }

  function closeSidecart() {
    if (!sidecart) return;
    sidecart.classList.remove('is-open');
    sidecart.setAttribute('aria-hidden', 'true');
    document.documentElement.classList.remove('zc-sidecart-lock');
  }

  function openSearchModal() {
    if (!searchModal) return;
    searchModal.classList.add('is-open');
    searchModal.setAttribute('aria-hidden', 'false');
    document.documentElement.classList.add('zc-search-lock');

    window.setTimeout(function () {
      if (searchField) {
        searchField.focus();
      }
    }, 80);
  }

  function closeSearchModal() {
    if (!searchModal) return;
    searchModal.classList.remove('is-open');
    searchModal.setAttribute('aria-hidden', 'true');
    document.documentElement.classList.remove('zc-search-lock');
  }

  cartTriggers.forEach(function (trigger) {
    trigger.addEventListener('click', function (event) {
      event.preventDefault();
      openSidecart();
    });
  });

  sidecartCloseButtons.forEach(function (button) {
    button.addEventListener('click', closeSidecart);
  });

  searchTriggers.forEach(function (trigger) {
    trigger.addEventListener('click', function (event) {
      event.preventDefault();
      openSearchModal();
    });
  });

  searchCloseButtons.forEach(function (button) {
    button.addEventListener('click', closeSearchModal);
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closeSidecart();
      closeSearchModal();
    }
  });

  if (new URLSearchParams(window.location.search).get('zc_open_cart') === '1') {
    openSidecart();

    if (window.history && window.history.replaceState) {
      const cleanUrl = new URL(window.location.href);
      cleanUrl.searchParams.delete('zc_open_cart');
      window.history.replaceState({}, document.title, cleanUrl.toString());
    }
  }
});
</script>

<style>
/* ================================
   Zarvel / Printly Header
================================ */

* {
  box-sizing: border-box;
}

body {
  margin: 0;
}

/* Sticky Header */
.zc-header {
  position: sticky;
  top: 0;
  z-index: 99999;
  background: #ffffff;
}

/* Fix sticky header when logged into WordPress */
body.admin-bar .zc-header {
  top: 32px;
}

@media screen and (max-width: 782px) {
  body.admin-bar .zc-header {
    top: 46px;
  }
}

.zc-container {
  width: min(100% - 40px, 1280px);
  margin: 0 auto;
}

/* Top Bar */
.zc-topbar {
  background: #050505;
  color: #ffffff;
  font-size: 12px;
  line-height: 1;
}

.zc-topbar__inner {
  min-height: 28px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
}

.zc-topbar__left,
.zc-topbar__right {
  display: flex;
  align-items: center;
  gap: 34px;
}

.zc-topbar a {
  color: #ffffff;
  text-decoration: none;
}

.zc-topbar a:hover {
  color: #ff6a21;
}

/* Main Header */
.zc-main-header {
  background: #ffffff;
  border-bottom: 1px solid #eeeeee;
}

.zc-main-header__inner {
  min-height: 76px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 32px;
}

/* Logo */
.zc-logo {
  color: #111111;
  text-decoration: none;
  display: flex;
  flex-direction: column;
  line-height: 1;
  flex-shrink: 0;
}

.zc-logo__main {
  font-size: 28px;
  font-weight: 900;
  letter-spacing: -1.5px;
}

.zc-logo__sub {
  margin-top: 6px;
  font-size: 8px;
  font-weight: 800;
  letter-spacing: 1.3px;
}

/* Navigation */
.zc-nav {
  display: flex;
  align-items: center;
  gap: 36px;
}

.zc-nav-link {
  color: #111111;
  text-decoration: none;
  font-size: 13px;
  font-weight: 800;
  letter-spacing: 0.2px;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  line-height: 1;
}

.zc-nav-link:hover {
  color: #ff6a21;
}

.zc-chevron {
  display: block;
  margin-top: 1px;
  transition: transform 0.2s ease;
}

/* Dropdown */
.zc-nav-item {
  position: relative;
}

.zc-dropdown {
  position: absolute;
  top: calc(100% + 18px);
  left: 0;
  width: 200px;
  background: #ffffff;
  border: 1px solid #eeeeee;
  box-shadow: 0 18px 45px rgba(0, 0, 0, 0.14);
  border-radius: 12px;
  padding: 10px;
  opacity: 0;
  visibility: hidden;
  pointer-events: none;
  transform: translateY(14px) scale(0.98);
  transform-origin: top left;
  transition:
    opacity 0.22s ease,
    visibility 0.22s ease,
    transform 0.22s ease;
  z-index: 999999;
}

.zc-dropdown::before {
  content: "";
  position: absolute;
  top: -22px;
  left: 0;
  width: 100%;
  height: 22px;
}

.zc-dropdown a {
  display: block;
  color: #111111;
  text-decoration: none;
  font-size: 13px;
  font-weight: 700;
  padding: 12px 14px;
  border-radius: 8px;
  opacity: 0;
  transform: translateY(6px);
  transition:
    opacity 0.2s ease,
    transform 0.2s ease,
    background 0.2s ease,
    color 0.2s ease,
    padding-left 0.2s ease;
}

.zc-dropdown a:hover {
  background: #fff0e8;
  color: #ff6a21;
  padding-left: 18px;
}

.zc-has-dropdown:hover .zc-dropdown,
.zc-has-dropdown:focus-within .zc-dropdown {
  opacity: 1;
  visibility: visible;
  pointer-events: auto;
  transform: translateY(0) scale(1);
}

.zc-has-dropdown:hover .zc-dropdown a,
.zc-has-dropdown:focus-within .zc-dropdown a {
  opacity: 1;
  transform: translateY(0);
}

.zc-has-dropdown:hover .zc-dropdown a:nth-child(1) {
  transition-delay: 0.04s;
}

.zc-has-dropdown:hover .zc-dropdown a:nth-child(2) {
  transition-delay: 0.07s;
}

.zc-has-dropdown:hover .zc-dropdown a:nth-child(3) {
  transition-delay: 0.1s;
}

.zc-has-dropdown:hover .zc-dropdown a:nth-child(4) {
  transition-delay: 0.13s;
}

.zc-has-dropdown:hover .zc-dropdown a:nth-child(5) {
  transition-delay: 0.16s;
}

.zc-has-dropdown:hover .zc-chevron,
.zc-has-dropdown:focus-within .zc-chevron {
  transform: rotate(180deg);
}

/* Right Actions */
.zc-header-actions {
  display: flex;
  align-items: center;
  gap: 18px;
  flex-shrink: 0;
}

.zc-icon-link,
.zc-cart-link {
  color: #111111;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  position: relative;
}

.zc-icon-link:hover,
.zc-cart-link:hover {
  color: #ff6a21;
}

.zc-cart-count {
  position: absolute;
  top: -9px;
  right: -10px;
  min-width: 17px;
  height: 17px;
  padding: 0 5px;
  border-radius: 999px;
  background: #ff6a21;
  color: #ffffff;
  font-size: 10px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: center;
}

.zc-customize-btn {
  background: #ff6a21;
  color: #ffffff;
  text-decoration: none;
  border-radius: 6px;
  padding: 16px 28px;
  font-size: 12px;
  font-weight: 900;
  letter-spacing: 0.2px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: 0.2s ease;
  white-space: nowrap;
}

.zc-customize-btn:hover {
  background: #e95713;
  color: #ffffff;
}

/* Search Modal */
.zc-search-lock {
  overflow: hidden;
}

.zc-search-modal {
  position: fixed;
  inset: 0;
  z-index: 1000001;
  pointer-events: none;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.zc-search-modal.is-open {
  opacity: 1;
  pointer-events: auto;
}

.zc-search-modal__overlay {
  position: absolute;
  inset: 0;
  border: 0;
  background: rgba(0, 0, 0, 0.42);
  backdrop-filter: blur(12px);
  cursor: pointer;
}

.zc-search-modal__panel {
  position: relative;
  width: min(100% - 36px, 760px);
  margin: 9vh auto 0;
  padding: 38px;
  border-radius: 16px;
  background: #ffffff;
  box-shadow: 0 30px 90px rgba(0, 0, 0, 0.28);
  transform: translateY(-18px);
  transition: transform 0.24s cubic-bezier(0.22, 1, 0.36, 1);
}

.zc-search-modal.is-open .zc-search-modal__panel {
  transform: translateY(0);
}

.zc-search-modal__close {
  position: absolute;
  top: 22px;
  right: 22px;
  width: 42px;
  height: 42px;
  border: 0;
  border-radius: 999px;
  background: transparent;
  cursor: pointer;
}

.zc-search-modal__close span {
  position: absolute;
  left: 10px;
  right: 10px;
  top: 20px;
  height: 2px;
  background: #111111;
}

.zc-search-modal__close span:first-child {
  transform: rotate(45deg);
}

.zc-search-modal__close span:last-child {
  transform: rotate(-45deg);
}

.zc-search-modal__panel p {
  margin: 0 0 10px;
  color: #ff5b1a;
  font-size: 13px;
  font-weight: 950;
  text-transform: uppercase;
  letter-spacing: 0.7px;
}

.zc-search-modal__panel h2 {
  max-width: 560px;
  margin: 0 0 26px;
  color: #050505;
  font-size: clamp(32px, 5vw, 56px);
  line-height: 0.96;
  font-weight: 950;
  letter-spacing: 0;
}

.zc-search-modal__form {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 150px;
  gap: 12px;
}

.zc-search-modal__form input[type="search"] {
  width: 100%;
  min-height: 58px;
  border: 1px solid #dedede;
  border-radius: 10px;
  padding: 0 18px;
  color: #111111;
  background: #f9f9f9;
  font-size: 16px;
  font-weight: 700;
  outline: none;
}

.zc-search-modal__form input[type="search"]:focus {
  border-color: #ff5b1a;
  background: #ffffff;
  box-shadow: 0 0 0 4px rgba(255, 91, 26, 0.12);
}

.zc-search-modal__form button {
  min-height: 58px;
  border: 0;
  border-radius: 10px;
  background: #ff5b1a;
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 950;
  text-transform: uppercase;
  cursor: pointer;
}

.zc-search-modal__quick {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px;
  margin-top: 22px;
}

.zc-search-modal__quick span {
  color: #666666;
  font-size: 13px;
  font-weight: 900;
  text-transform: uppercase;
}

.zc-search-modal__quick a {
  min-height: 38px;
  border: 1px solid #dddddd;
  border-radius: 999px;
  padding: 0 15px;
  color: #050505;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  font-size: 13px;
  font-weight: 900;
}

.zc-search-modal__quick a:hover {
  border-color: #050505;
  background: #050505;
  color: #ffffff;
}

/* Side Cart */
.zc-sidecart-lock {
  overflow: hidden;
}

.zc-sidecart {
  position: fixed;
  inset: 0;
  z-index: 1000000;
  pointer-events: none;
  opacity: 0;
  transition: opacity 0.2s ease;
}

.zc-sidecart.is-open {
  opacity: 1;
  pointer-events: auto;
}

.zc-sidecart__overlay {
  position: absolute;
  inset: 0;
  border: 0;
  background: rgba(0, 0, 0, 0.22);
  backdrop-filter: blur(10px);
  cursor: pointer;
}

.zc-sidecart__panel {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  width: min(100%, 690px);
  height: 100dvh;
  margin: 0;
  background: #ffffff;
  border-radius: 16px 0 0 16px;
  box-shadow: 0 28px 90px rgba(0, 0, 0, 0.26);
  padding: 44px 42px 28px;
  overflow: auto;
  transform: translateX(110%);
  transition: transform 0.32s cubic-bezier(0.22, 1, 0.36, 1);
  will-change: transform;
}

.zc-sidecart.is-open .zc-sidecart__panel {
  transform: translateX(0);
}

.zc-sidecart__header {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-bottom: 28px;
}

.zc-sidecart__header h2 {
  margin: 0;
  color: #050505;
  font-size: 34px;
  line-height: 1;
  font-weight: 950;
  text-transform: uppercase;
}

.zc-sidecart__count {
  min-width: 40px;
  height: 40px;
  border-radius: 999px;
  background: #ff5b1a;
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0 10px;
  font-size: 18px;
  font-weight: 950;
}

.zc-sidecart__close {
  width: 42px;
  height: 42px;
  margin-left: auto;
  padding: 0;
  border: 0;
  background: transparent;
  cursor: pointer;
  position: relative;
}

.zc-sidecart__close span {
  position: absolute;
  left: 7px;
  top: 20px;
  width: 28px;
  height: 2px;
  background: #111111;
  border-radius: 999px;
}

.zc-sidecart__close span:first-child {
  transform: rotate(45deg);
}

.zc-sidecart__close span:last-child {
  transform: rotate(-45deg);
}

.zc-sidecart__shipping,
.zc-sidecart__summary {
  background: #fff7f1;
  border-radius: 12px;
}

.zc-sidecart__shipping {
  display: grid;
  grid-template-columns: 78px minmax(0, 1fr);
  gap: 16px;
  padding: 28px 32px;
  margin-bottom: 34px;
}

.zc-sidecart__ship-icon {
  width: 58px;
  height: 58px;
  border-radius: 999px;
  background: #ffffff;
  border: 1px solid #ececec;
  display: grid;
  place-items: center;
  color: #111111;
}

.zc-sidecart__ship-copy strong {
  display: block;
  color: #111111;
  font-size: 18px;
  line-height: 1.35;
  font-weight: 850;
}

.zc-sidecart__ship-copy strong span,
.zc-sidecart__ship-copy p b,
.zc-sidecart__free,
.zc-sidecart__total strong {
  color: #ff5b1a;
}

.zc-sidecart__progress {
  height: 10px;
  margin: 18px 0 12px;
  border-radius: 999px;
  background: #e4e4e4;
  overflow: hidden;
}

.zc-sidecart__progress span {
  display: block;
  height: 100%;
  border-radius: inherit;
  background: #ff5b1a;
}

.zc-sidecart__ship-copy p {
  margin: 0;
  color: #555555;
  font-size: 18px;
}

.zc-sidecart__items {
  display: grid;
  gap: 0;
}

.zc-sidecart__item {
  display: grid;
  grid-template-columns: 154px minmax(0, 1fr);
  gap: 30px;
  padding: 0 0 28px;
  margin-bottom: 28px;
  border-bottom: 1px solid #e7e7e7;
}

.zc-sidecart__thumb {
  display: block;
  width: 154px;
  aspect-ratio: 1 / 1;
  border: 1px solid #e7e7e7;
  border-radius: 10px;
  overflow: hidden;
  background: #f8f8f8;
}

.zc-sidecart__thumb img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
}

.zc-sidecart__item-main {
  min-width: 0;
}

.zc-sidecart__item-top {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 16px;
  align-items: start;
}

.zc-sidecart__item-top h3 {
  margin: 8px 0 10px;
  color: #111111;
  font-size: 20px;
  line-height: 1.25;
  font-weight: 900;
}

.zc-sidecart__item-top h3 a {
  color: inherit;
  text-decoration: none;
}

.zc-sidecart__item-top strong {
  margin-top: 8px;
  color: #ff5b1a;
  font-size: 20px;
  font-weight: 950;
  white-space: nowrap;
}

.zc-sidecart__meta {
  display: grid;
  gap: 4px;
  margin-bottom: 18px;
  color: #666666;
  font-size: 17px;
  line-height: 1.35;
}

.zc-sidecart__item-bottom {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.zc-sidecart__qty {
  width: 150px;
  height: 46px;
  border: 1px solid #e4e4e4;
  border-radius: 14px;
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  align-items: center;
  justify-items: center;
  color: #111111;
  font-size: 18px;
  font-weight: 800;
}

.zc-sidecart__qty a {
  color: #111111;
  font-size: 24px;
  line-height: 1;
  text-decoration: none;
  width: 100%;
  height: 100%;
  display: grid;
  place-items: center;
}

.zc-sidecart__qty a:hover {
  color: #ff5b1a;
}

.zc-sidecart__remove {
  color: #8c8c8c;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
}

.zc-sidecart__remove:hover {
  color: #ff5b1a;
}

.zc-sidecart__summary {
  padding: 30px 30px 16px;
  margin-top: 12px;
}

.zc-sidecart__summary > div {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  color: #111111;
  font-size: 18px;
  line-height: 1.35;
  margin-bottom: 16px;
}

.zc-sidecart__summary hr {
  border: 0;
  border-top: 1px solid #dddddd;
  margin: 18px 0 20px;
}

.zc-sidecart__summary .zc-sidecart__total {
  margin-bottom: 22px;
}

.zc-sidecart__total span,
.zc-sidecart__total strong {
  font-size: 28px;
  font-weight: 950;
}

.zc-sidecart__view-cart,
.zc-sidecart__checkout,
.zc-sidecart__empty a {
  min-height: 60px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  text-transform: uppercase;
  font-size: 18px;
  font-weight: 950;
}

.zc-sidecart__view-cart {
  border: 1px solid #111111;
  color: #111111;
  background: #ffffff;
  margin-bottom: 14px;
}

.zc-sidecart__checkout,
.zc-sidecart__empty a {
  border: 0;
  color: #ffffff;
  background: #ff5b1a;
}

.zc-sidecart__checkout {
  justify-content: center;
  gap: 18px;
}

.zc-sidecart__checkout span {
  font-size: 30px;
  line-height: 1;
}

.zc-sidecart__trust {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 18px;
  padding: 30px 4px 4px;
}

.zc-sidecart__trust div {
  display: grid;
  grid-template-columns: 34px minmax(0, 1fr);
  gap: 10px;
  align-items: start;
}

.zc-sidecart__trust span {
  color: #111111;
  font-size: 28px;
  line-height: 1;
}

.zc-sidecart__trust p {
  margin: 0;
  color: #555555;
  font-size: 13px;
  line-height: 1.35;
}

.zc-sidecart__trust b {
  color: #111111;
}

.zc-sidecart__empty {
  padding: 36px 0;
  text-align: center;
}

.zc-sidecart__empty h3 {
  margin: 0 0 8px;
  font-size: 24px;
}

.zc-sidecart__empty p {
  margin: 0 0 20px;
  color: #666666;
}

/* Mobile Button */
.zc-mobile-toggle {
  display: none;
  width: 34px;
  height: 28px;
  padding: 0;
  background: transparent;
  border: 0;
  cursor: pointer;
}

.zc-mobile-toggle span {
  display: block;
  width: 100%;
  height: 3px;
  background: #111111;
  margin: 6px 0;
  border-radius: 999px;
  transition: 0.2s ease;
}

/* Hide START CUSTOMIZING from 769px to 1024px */
@media screen and (min-width: 769px) and (max-width: 1024px) {
  .zc-customize-btn {
    display: none;
  }

  .zc-nav {
    gap: 26px;
  }

  .zc-header-actions {
    gap: 16px;
  }

  .zc-main-header__inner {
    gap: 24px;
  }
}

/* Hamburger starts at 768px */
@media screen and (max-width: 768px) {
  .zc-container {
    width: min(100% - 30px, 1280px);
  }

  .zc-topbar__inner {
    min-height: auto;
    padding: 10px 0;
    flex-direction: column;
    gap: 8px;
    text-align: center;
  }

  .zc-topbar__left,
  .zc-topbar__right {
    flex-wrap: wrap;
    justify-content: center;
    gap: 12px;
  }

  .zc-main-header__inner {
    min-height: 68px;
    gap: 15px;
    flex-wrap: wrap;
  }

  .zc-logo__main {
    font-size: 24px;
  }

  .zc-mobile-toggle {
    display: block;
    margin-left: auto;
  }

  .zc-nav,
  .zc-header-actions {
    display: none;
    width: 100%;
  }

  .zc-nav.is-open {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0;
    padding: 18px 0 5px;
    border-top: 1px solid #eeeeee;
  }

  .zc-nav.is-open .zc-nav-link {
    width: 100%;
    padding: 14px 0;
    justify-content: space-between;
  }

  .zc-nav-item {
    width: 100%;
  }

  .zc-dropdown {
    position: static;
    width: 100%;
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
    transform: none;
    box-shadow: none;
    border: 0;
    border-radius: 0;
    padding: 0 0 8px 14px;
    display: block;
  }

  .zc-dropdown::before {
    display: none;
  }

  .zc-dropdown a {
    opacity: 1;
    transform: none;
    padding: 10px 0;
    font-size: 13px;
  }

  .zc-dropdown a:hover {
    padding-left: 0;
  }

  .zc-header-actions.is-open {
    display: flex;
    justify-content: flex-start;
    flex-wrap: wrap;
    padding: 10px 0 20px;
  }

  .zc-customize-btn {
    width: 100%;
    padding: 15px 20px;
  }

  .zc-mobile-toggle.is-active span:nth-child(1) {
    transform: translateY(9px) rotate(45deg);
  }

  .zc-mobile-toggle.is-active span:nth-child(2) {
    opacity: 0;
  }

  .zc-mobile-toggle.is-active span:nth-child(3) {
    transform: translateY(-9px) rotate(-45deg);
  }

  .zc-sidecart__panel {
    top: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: 100dvh;
    margin: 0;
    padding: 28px 18px 22px;
    border-radius: 0;
  }

  .zc-sidecart__header h2 {
    font-size: 28px;
  }

  .zc-sidecart__shipping {
    grid-template-columns: 52px minmax(0, 1fr);
    padding: 18px;
  }

  .zc-sidecart__ship-icon {
    width: 48px;
    height: 48px;
  }

  .zc-sidecart__item {
    grid-template-columns: 96px minmax(0, 1fr);
    gap: 16px;
  }

  .zc-sidecart__thumb {
    width: 96px;
  }

  .zc-sidecart__item-top {
    grid-template-columns: 1fr;
    gap: 4px;
  }

  .zc-sidecart__item-top h3,
  .zc-sidecart__item-top strong {
    margin-top: 0;
    font-size: 17px;
  }

  .zc-sidecart__meta {
    font-size: 14px;
  }

  .zc-sidecart__qty {
    width: 116px;
    height: 40px;
  }

  .zc-sidecart__summary {
    padding: 22px 18px 16px;
  }

  .zc-sidecart__trust {
    grid-template-columns: 1fr;
  }

  .zc-search-modal__panel {
    width: min(100% - 24px, 760px);
    margin-top: 72px;
    padding: 28px 18px 22px;
    border-radius: 12px;
  }

  .zc-search-modal__close {
    top: 14px;
    right: 14px;
  }

  .zc-search-modal__form {
    grid-template-columns: 1fr;
  }

  .zc-search-modal__form button {
    width: 100%;
  }
}
</style>
