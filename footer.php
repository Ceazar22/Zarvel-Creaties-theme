<footer class="site-footer">

  <div class="site-footer__container">

    <div class="site-footer__top">

      <!-- Brand -->
      <div class="site-footer__brand">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="site-footer__logo">
          <span class="site-footer__logo-main">PRINTLY</span>
          <span class="site-footer__logo-sub">CUSTOM. YOUR WAY.</span>
        </a>

        <p>
          We help you create custom products that express your style, ideas, and brand.
        </p>

        <div class="site-footer__socials">
          <a href="#" aria-label="Facebook">
            <svg viewBox="0 0 24 24">
              <path d="M14 8h3V4h-3c-3 0-5 2-5 5v3H6v4h3v8h4v-8h3l1-4h-4V9c0-.6.4-1 1-1z"/>
            </svg>
          </a>

          <a href="#" aria-label="Instagram">
            <svg viewBox="0 0 24 24">
              <rect x="4" y="4" width="16" height="16" rx="5"/>
              <circle cx="12" cy="12" r="4"/>
              <circle cx="17" cy="7" r="1"/>
            </svg>
          </a>

          <a href="#" aria-label="TikTok">
            <svg viewBox="0 0 24 24">
              <path d="M14 3v11a4 4 0 1 1-4-4"/>
              <path d="M14 3c1 4 3 6 7 6"/>
            </svg>
          </a>

          <a href="#" aria-label="YouTube">
            <svg viewBox="0 0 24 24">
              <path d="M22 12s0-4-1-5c-.5-.7-1.2-1-2-1-3-.3-7-.3-7-.3s-4 0-7 .3c-.8 0-1.5.3-2 1-1 1-1 5-1 5s0 4 1 5c.5.7 1.2 1 2 1 3 .3 7 .3 7 .3s4 0 7-.3c.8 0 1.5-.3 2-1 1-1 1-5 1-5z"/>
              <path d="M10 9l5 3-5 3V9z"/>
            </svg>
          </a>
        </div>
      </div>

      <!-- Shop -->
      <div class="site-footer__column">
        <h3>SHOP</h3>

        <ul>
          <?php
          $zc_footer_categories = function_exists('zarvel_get_shop_categories')
            ? zarvel_get_shop_categories(6)
            : array();
          ?>

          <?php if (!empty($zc_footer_categories)) : ?>
            <?php foreach ($zc_footer_categories as $zc_footer_category) : ?>
              <?php $zc_footer_category_url = get_term_link($zc_footer_category); ?>
              <?php if (!is_wp_error($zc_footer_category_url)) : ?>
                <li>
                  <a href="<?php echo esc_url($zc_footer_category_url); ?>">
                    <?php echo esc_html($zc_footer_category->name); ?>
                  </a>
                </li>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php else : ?>
            <li><a href="<?php echo esc_url(home_url('/shop')); ?>">All Products</a></li>
          <?php endif; ?>
        </ul>
      </div>

      <!-- Support -->
      <div class="site-footer__column">
        <h3>SUPPORT</h3>

        <ul>
          <li><a href="<?php echo esc_url(home_url('/contact')); ?>">Contact Us</a></li>
          <li><a href="<?php echo esc_url(home_url('/shipping-policy')); ?>">Shipping Policy</a></li>
          <li><a href="<?php echo esc_url(home_url('/return-policy')); ?>">Return Policy</a></li>
          <li><a href="<?php echo esc_url(home_url('/faqs')); ?>">FAQs</a></li>
          <li><a href="<?php echo esc_url(home_url('/size-guide')); ?>">Size Guide</a></li>
          <li><a href="<?php echo esc_url(home_url('/track-order')); ?>">Track Your Order</a></li>
        </ul>
      </div>

      <!-- Company -->
      <div class="site-footer__column">
        <h3>COMPANY</h3>

        <ul>
          <li><a href="<?php echo esc_url(home_url('/about-us')); ?>">About Us</a></li>
          <li><a href="<?php echo esc_url(home_url('/our-services')); ?>">Our Services</a></li>
          <li><a href="<?php echo esc_url(home_url('/our-process')); ?>">Our Process</a></li>
          <li><a href="<?php echo esc_url(home_url('/careers')); ?>">Careers</a></li>
          <li><a href="<?php echo esc_url(home_url('/blog')); ?>">Blog</a></li>
          <li><a href="<?php echo esc_url(home_url('/terms-of-service')); ?>">Terms of Service</a></li>
          <li><a href="<?php echo esc_url(home_url('/privacy-policy')); ?>">Privacy Policy</a></li>
        </ul>
      </div>

      <!-- Newsletter -->
      <div class="site-footer__newsletter">
        <h3>NEWSLETTER</h3>

        <p>
          Get updates on new products and exclusive offers.
        </p>

        <form class="site-footer__form" action="#" method="post">
          <label class="screen-reader-text" for="footer-email">Email address</label>

          <input
            id="footer-email"
            type="email"
            name="email"
            placeholder="Enter your email"
            required
          >

          <button type="submit">
            SUBSCRIBE
          </button>
        </form>
      </div>

    </div>

    <div class="site-footer__bottom">
      <p>&copy; <?php echo date('Y'); ?> Zarvel Creative. All rights reserved.</p>
    </div>

  </div>

</footer>

<style>
.site-footer {
  background: #050505;
  color: #ffffff;
  padding: 48px 0 18px;
}

.site-footer__container {
  width: min(100% - 40px, 1280px);
  margin: 0 auto;
}

.site-footer__top {
  display: grid;
  grid-template-columns: 1.4fr 0.8fr 1fr 1fr 1.4fr;
  gap: 48px;
  padding-bottom: 34px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.18);
}

.site-footer__brand p,
.site-footer__newsletter p {
  margin: 14px 0 0;
  max-width: 260px;
  color: rgba(255, 255, 255, 0.78);
  font-size: 13px;
  line-height: 1.5;
  font-weight: 500;
}

.site-footer__logo {
  color: #ffffff;
  text-decoration: none;
  display: inline-flex;
  flex-direction: column;
  line-height: 1;
}

.site-footer__logo-main {
  font-size: 30px;
  font-weight: 950;
  letter-spacing: -1.5px;
}

.site-footer__logo-sub {
  margin-top: 5px;
  font-size: 8px;
  font-weight: 800;
  letter-spacing: 1.4px;
}

.site-footer__socials {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-top: 22px;
}

.site-footer__socials a {
  width: 24px;
  height: 24px;
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: 0.2s ease;
}

.site-footer__socials a:hover {
  color: #ff5b1a;
  transform: translateY(-2px);
}

.site-footer__socials svg {
  width: 19px;
  height: 19px;
  fill: none;
  stroke: currentColor;
  stroke-width: 2;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.site-footer__socials a:first-child svg {
  fill: currentColor;
  stroke: none;
}

.site-footer__column h3,
.site-footer__newsletter h3 {
  margin: 0 0 14px;
  color: #ffffff;
  font-size: 13px;
  line-height: 1;
  font-weight: 950;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}

.site-footer__column ul {
  list-style: none;
  margin: 0;
  padding: 0;
}

.site-footer__column li {
  margin-bottom: 9px;
}

.site-footer__column a {
  color: rgba(255, 255, 255, 0.76);
  text-decoration: none;
  font-size: 13px;
  line-height: 1.2;
  font-weight: 500;
  transition: 0.2s ease;
}

.site-footer__column a:hover {
  color: #ff5b1a;
}

.site-footer__form {
  display: flex;
  align-items: stretch;
  max-width: 320px;
  margin-top: 18px;
  border-radius: 6px;
  overflow: hidden;
  background: #ffffff;
}

.site-footer__form input {
  width: 100%;
  min-height: 44px;
  border: 0;
  outline: 0;
  padding: 0 14px;
  color: #111111;
  font-size: 12px;
  font-weight: 600;
}

.site-footer__form input::placeholder {
  color: #777777;
}

.site-footer__form button {
  border: 0;
  background: #ff5b1a;
  color: #ffffff;
  padding: 0 18px;
  font-size: 11px;
  font-weight: 950;
  cursor: pointer;
  transition: 0.2s ease;
}

.site-footer__form button:hover {
  background: #e94d10;
}

.site-footer__bottom {
  padding-top: 18px;
  text-align: center;
}

.site-footer__bottom p {
  margin: 0;
  color: rgba(255, 255, 255, 0.72);
  font-size: 12px;
  line-height: 1.4;
  font-weight: 500;
}

.screen-reader-text {
  position: absolute;
  width: 1px;
  height: 1px;
  overflow: hidden;
  clip: rect(1px, 1px, 1px, 1px);
  white-space: nowrap;
}

/* 1024 */
@media screen and (max-width: 1024px) {
  .site-footer__top {
    grid-template-columns: repeat(3, 1fr);
    gap: 36px;
  }

  .site-footer__newsletter {
    grid-column: span 2;
  }
}

/* 768 */
@media screen and (max-width: 768px) {
  .site-footer {
    padding: 42px 0 18px;
  }

  .site-footer__container {
    width: min(100% - 30px, 1280px);
  }

  .site-footer__top {
    grid-template-columns: repeat(2, 1fr);
    gap: 32px 24px;
  }

  .site-footer__brand,
  .site-footer__newsletter {
    grid-column: span 2;
  }

  .site-footer__brand p,
  .site-footer__newsletter p {
    max-width: 100%;
  }

  .site-footer__form {
    max-width: 100%;
  }
}

/* 480 */
@media screen and (max-width: 480px) {
  .site-footer__top {
    grid-template-columns: 1fr;
  }

  .site-footer__brand,
  .site-footer__newsletter {
    grid-column: span 1;
  }

  .site-footer__form {
    flex-direction: column;
  }

  .site-footer__form button {
    min-height: 42px;
  }
}
</style>

<?php wp_footer(); ?>
</body>
</html>
