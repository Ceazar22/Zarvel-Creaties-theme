<?php
defined('ABSPATH') || exit;

get_header();
?>

<main class="zc-services-page">
  <section class="zc-services-hero">
    <div class="zc-services-hero__container">

      <nav class="zc-services-hero__breadcrumb" aria-label="Breadcrumb">
        <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
        <span>›</span>
        <strong>Our Services</strong>
      </nav>

      <div class="zc-services-hero__content">
        <span class="zc-services-hero__kicker">What We Do</span>

        <h1 class="zc-services-hero__title">
          <span>Creative Services</span>
          <strong>Built To Launch</strong>
        </h1>

        <p class="zc-services-hero__text">
          We do more than print on demand. We help with custom products, graphic design,
          and website creation for Shopify, WordPress, and WooCommerce brands.
        </p>

        <div class="zc-services-hero__buttons">
          <a href="<?php echo esc_url(home_url('/contact')); ?>" class="zc-services-btn zc-services-btn--orange">
            Start a Project
          </a>

          <a href="<?php echo esc_url(home_url('/customize')); ?>" class="zc-services-btn zc-services-btn--dark">
            Send Design Request
            <span>→</span>
          </a>
        </div>
      </div>

    </div>
  </section>

  <section class="zc-services-list">
    <div class="zc-services-container">
      <div class="zc-services-heading">
        <span>Services</span>
        <h2>How we can help</h2>
        <p>
          Choose one service or combine them into one complete launch package.
        </p>
      </div>

      <div class="zc-services-grid">
        <article class="zc-service-card">
          <div class="zc-service-card__icon">
            <svg viewBox="0 0 64 64" aria-hidden="true">
              <path d="M20 18l8-5h8l8 5 8 5-6 10-6-3v22H24V30l-6 3-6-10 8-5z"/>
              <path d="M28 13c1 3 3 5 4 5s3-2 4-5"/>
            </svg>
          </div>

          <h3>Print On Demand</h3>
          <p>
            Custom products for brands, teams, events, gifts, and personal projects.
            We help prepare your artwork and product mockups for production.
          </p>

          <ul>
            <li>Apparel, mugs, caps, gifts, and accessories</li>
            <li>Artwork placement and product mockups</li>
            <li>Made-on-demand order support</li>
          </ul>

          <a href="<?php echo esc_url(home_url('/shop')); ?>">Browse Products</a>
        </article>

        <article class="zc-service-card">
          <div class="zc-service-card__icon">
            <svg viewBox="0 0 64 64" aria-hidden="true">
              <path d="M14 48l8-2 26-26-6-6-26 26-2 8z"/>
              <path d="M38 18l6 6"/>
              <path d="M24 50h28"/>
            </svg>
          </div>

          <h3>Graphic Design</h3>
          <p>
            Design support for logos, merch graphics, product artwork, social assets,
            and print-ready layouts that look clean across products and screens.
          </p>

          <ul>
            <li>Logo cleanup and vector-style redraws</li>
            <li>Merch graphics and campaign visuals</li>
            <li>Product-ready artwork preparation</li>
          </ul>

          <a href="<?php echo esc_url(home_url('/customize')); ?>">Request Design Help</a>
        </article>

        <article class="zc-service-card">
          <div class="zc-service-card__icon">
            <svg viewBox="0 0 64 64" aria-hidden="true">
              <rect x="10" y="14" width="44" height="34" rx="5"/>
              <path d="M10 24h44"/>
              <path d="M20 36h10"/>
              <path d="M36 36h10"/>
              <path d="M24 54h16"/>
              <path d="M32 48v6"/>
            </svg>
          </div>

          <h3>Website Creation</h3>
          <p>
            Shopify and WordPress websites for brands that need a polished store,
            landing page, service site, or WooCommerce product experience.
          </p>

          <ul>
            <li>Shopify storefront setup</li>
            <li>WordPress and WooCommerce builds</li>
            <li>Product, checkout, and service pages</li>
          </ul>

          <a href="<?php echo esc_url(home_url('/contact')); ?>">Build a Website</a>
        </article>
      </div>
    </div>
  </section>

  <section class="zc-services-process">
    <div class="zc-services-container">
      <div class="zc-services-process__inner">
        <div class="zc-services-process__intro">
          <span>Process</span>
          <h2>Simple from idea to launch</h2>
        </div>

        <div class="zc-services-steps">
          <article>
            <b>01</b>
            <h3>Tell us what you need</h3>
            <p>Send your product idea, design brief, website goal, budget, and timeline.</p>
          </article>

          <article>
            <b>02</b>
            <h3>We create the direction</h3>
            <p>We prepare the design, mockup, product setup, or website structure.</p>
          </article>

          <article>
            <b>03</b>
            <h3>Review and launch</h3>
            <p>We make final adjustments and help prepare the work for ordering or publishing.</p>
          </article>
        </div>
      </div>
    </div>
  </section>

  <section class="zc-services-cta">
    <div class="zc-services-container zc-services-cta__inner">
      <div>
        <span>Ready when you are</span>
        <h2>Need printing, graphics, or a website?</h2>
      </div>

      <a href="<?php echo esc_url(home_url('/contact')); ?>">Contact Us</a>
    </div>
  </section>
</main>

<style>
.zc-services-page {
  background: #ffffff;
  color: #111111;
}

.zc-services-container,
.zc-services-hero__container {
  width: min(100% - 40px, 1280px);
  margin: 0 auto;
}

.zc-services-hero {
  width: 100%;
  padding: 38px 0 64px;
  background:
    radial-gradient(circle at 88% 18%, rgba(255, 91, 26, 0.1), transparent 30%),
    linear-gradient(90deg, #ffffff 0%, #fff8f4 100%);
  border-top: 1px solid #efebe7;
  border-bottom: 1px solid #efebe7;
}

.zc-services-hero__breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 28px;
  color: #8a8a8a;
  font-size: 12px;
  line-height: 1.2;
  font-weight: 700;
}

.zc-services-hero__breadcrumb a {
  color: #8a8a8a;
  text-decoration: none;
}

.zc-services-hero__breadcrumb a:hover {
  color: #ff5b1a;
}

.zc-services-hero__breadcrumb span {
  color: #b8b8b8;
}

.zc-services-hero__breadcrumb strong {
  color: #111111;
  font-weight: 800;
}

.zc-services-hero__content {
  max-width: 800px;
  margin: 0 auto;
  text-align: center;
}

.zc-services-hero__kicker,
.zc-services-heading span,
.zc-services-process__intro span,
.zc-services-cta span {
  display: inline-block;
  margin-bottom: 14px;
  color: #ff5b1a;
  font-size: 12px;
  line-height: 1;
  font-weight: 950;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.zc-services-hero__title {
  margin: 0;
  color: #111111;
  font-size: clamp(48px, 7vw, 88px);
  line-height: 0.95;
  font-weight: 950;
  text-transform: uppercase;
  letter-spacing: -2px;
}

.zc-services-hero__title span,
.zc-services-hero__title strong {
  display: block;
}

.zc-services-hero__title strong {
  color: #ff5b1a;
  font-weight: 950;
}

.zc-services-hero__text {
  max-width: 650px;
  margin: 22px auto 0;
  color: #333333;
  font-size: 17px;
  line-height: 1.6;
  font-weight: 650;
}

.zc-services-hero__buttons {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  flex-wrap: wrap;
  margin-top: 28px;
}

.zc-services-btn,
.zc-service-card a,
.zc-services-cta a {
  min-height: 46px;
  padding: 0 22px;
  border-radius: 6px;
  color: #ffffff;
  font-size: 12px;
  line-height: 1;
  font-weight: 950;
  text-transform: uppercase;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  transition: 0.2s ease;
}

.zc-services-btn--orange,
.zc-service-card a,
.zc-services-cta a {
  background: #ff5b1a;
}

.zc-services-btn--orange:hover,
.zc-service-card a:hover,
.zc-services-cta a:hover {
  background: #111111;
  color: #ffffff;
}

.zc-services-btn--dark {
  background: #111111;
}

.zc-services-btn--dark:hover {
  background: #ff5b1a;
}

.zc-services-list {
  padding: 58px 0 36px;
  background: #ffffff;
}

.zc-services-heading {
  max-width: 680px;
  margin: 0 auto 32px;
  text-align: center;
}

.zc-services-heading h2,
.zc-services-process__intro h2,
.zc-services-cta h2 {
  margin: 0;
  color: #111111;
  font-size: clamp(36px, 5vw, 58px);
  line-height: 0.95;
  font-weight: 950;
  text-transform: uppercase;
  letter-spacing: -1.4px;
}

.zc-services-heading p {
  max-width: 520px;
  margin: 16px auto 0;
  color: #555555;
  font-size: 15px;
  line-height: 1.65;
  font-weight: 600;
}

.zc-services-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 22px;
}

.zc-service-card {
  min-height: 100%;
  padding: 30px;
  border: 1px solid #eeeeee;
  border-radius: 12px;
  background:
    radial-gradient(circle at 100% 0%, rgba(255, 91, 26, 0.08), transparent 30%),
    #ffffff;
  box-shadow: 0 12px 34px rgba(0, 0, 0, 0.04);
}

.zc-service-card__icon {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  background: #fff1e9;
  color: #ff5b1a;
  display: flex;
  align-items: center;
  justify-content: center;
}

.zc-service-card__icon svg {
  width: 42px;
  height: 42px;
  fill: none;
  stroke: currentColor;
  stroke-width: 2.4;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.zc-service-card h3 {
  margin: 24px 0 12px;
  color: #111111;
  font-size: 27px;
  line-height: 1;
  font-weight: 950;
  text-transform: uppercase;
  letter-spacing: -0.7px;
}

.zc-service-card p {
  margin: 0;
  color: #4f4f4f;
  font-size: 15px;
  line-height: 1.65;
  font-weight: 500;
}

.zc-service-card ul {
  margin: 22px 0 26px;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 10px;
}

.zc-service-card li {
  position: relative;
  padding-left: 20px;
  color: #222222;
  font-size: 14px;
  line-height: 1.35;
  font-weight: 850;
}

.zc-service-card li::before {
  content: "";
  position: absolute;
  left: 0;
  top: 7px;
  width: 8px;
  height: 8px;
  border-radius: 999px;
  background: #ff5b1a;
}

.zc-services-process {
  padding: 36px 0 58px;
  background: #ffffff;
}

.zc-services-process__inner {
  padding: 36px 48px;
  border-radius: 16px;
  background:
    radial-gradient(circle at 10% 20%, rgba(255, 91, 26, 0.07), transparent 28%),
    linear-gradient(90deg, #fffaf5 0%, #ffffff 48%, #fff7f1 100%);
  box-shadow: 0 10px 35px rgba(0, 0, 0, 0.04);
}

.zc-services-process__intro {
  max-width: 640px;
  margin: 0 auto 28px;
  text-align: center;
}

.zc-services-steps {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 18px;
}

.zc-services-steps article {
  padding: 24px;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 12px;
  background: #ffffff;
}

.zc-services-steps b {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #111111;
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  line-height: 1;
  font-weight: 950;
}

.zc-services-steps h3 {
  margin: 16px 0 8px;
  color: #111111;
  font-size: 20px;
  line-height: 1.05;
  font-weight: 950;
  text-transform: uppercase;
}

.zc-services-steps p {
  margin: 0;
  color: #555555;
  font-size: 14px;
  line-height: 1.6;
  font-weight: 550;
}

.zc-services-cta {
  padding: 54px 0 72px;
  background: #ffffff;
}

.zc-services-cta__inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  padding: 32px 38px;
  border-radius: 12px;
  background: #111111;
}

.zc-services-cta h2 {
  max-width: 640px;
  color: #ffffff;
}

@media screen and (max-width: 1024px) {
  .zc-services-grid,
  .zc-services-steps {
    grid-template-columns: 1fr;
  }
}

@media screen and (max-width: 768px) {
  .zc-services-container,
  .zc-services-hero__container {
    width: min(100% - 30px, 1280px);
  }

  .zc-services-hero {
    padding: 30px 0 50px;
  }

  .zc-services-hero__title {
    font-size: 44px;
    letter-spacing: -1px;
  }

  .zc-services-hero__text {
    font-size: 15px;
  }

  .zc-services-process__inner,
  .zc-service-card,
  .zc-services-cta__inner {
    padding: 24px;
  }

  .zc-services-cta__inner {
    display: block;
  }

  .zc-services-cta a {
    margin-top: 22px;
  }
}

@media screen and (max-width: 480px) {
  .zc-services-hero__title {
    font-size: 38px;
  }

  .zc-services-heading h2,
  .zc-services-process__intro h2,
  .zc-services-cta h2 {
    font-size: 34px;
  }
}
</style>

<?php
get_footer();
