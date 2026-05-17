<?php
defined('ABSPATH') || exit;

$services_url = home_url('/our-services/');
$contact_url  = home_url('/contact/');
?>

<section class="zc-about-hero">
  <div class="zc-about-hero__container">

    <nav class="zc-about-hero__breadcrumb" aria-label="Breadcrumb">
      <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
      <span>›</span>
      <strong>About Us</strong>
    </nav>

    <div class="zc-about-hero__content">
      <span class="zc-about-hero__kicker">Our Story</span>

      <h1 class="zc-about-hero__title">
        <span>Creative Ideas</span>
        <strong>Made Real</strong>
      </h1>

      <p class="zc-about-hero__text">
        Zarvel Creatives helps people turn rough ideas into custom products, clean graphics,
        and websites built with Shopify, WordPress, and WooCommerce.
      </p>

      <div class="zc-about-hero__buttons">
        <a href="<?php echo esc_url($services_url); ?>" class="zc-about-hero__btn zc-about-hero__btn--orange">
          Our Services
        </a>

        <a href="<?php echo esc_url($contact_url); ?>" class="zc-about-hero__btn zc-about-hero__btn--dark">
          Start a Project
          <span>→</span>
        </a>
      </div>
    </div>

  </div>
</section>

<style>
.zc-about-hero {
  width: 100%;
  padding: 38px 0 64px;
  background:
    radial-gradient(circle at 88% 18%, rgba(255, 91, 26, 0.1), transparent 30%),
    linear-gradient(90deg, #ffffff 0%, #fff8f4 100%);
  border-top: 1px solid #efebe7;
  border-bottom: 1px solid #efebe7;
}

.zc-about-hero__container {
  width: min(100% - 40px, 1280px);
  margin: 0 auto;
}

.zc-about-hero__breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 20px;
  color: #8a8a8a;
  font-size: 12px;
  line-height: 1.2;
  font-weight: 700;
}

.zc-about-hero__breadcrumb a {
  color: #8a8a8a;
  text-decoration: none;
}

.zc-about-hero__breadcrumb a:hover {
  color: #ff5b1a;
}

.zc-about-hero__breadcrumb span {
  color: #b8b8b8;
}

.zc-about-hero__breadcrumb strong {
  color: #111111;
  font-weight: 800;
}

.zc-about-hero__content {
  max-width: 820px;
  margin: 0 auto;
  text-align: center;
}

.zc-about-hero__kicker {
  display: inline-block;
  margin-bottom: 14px;
  color: #ff5b1a;
  font-size: 12px;
  line-height: 1;
  font-weight: 950;
  text-transform: uppercase;
}

.zc-about-hero__title {
  margin: 0;
  color: #111111;
  font-size: clamp(48px, 7vw, 88px);
  line-height: 0.95;
  font-weight: 950;
  letter-spacing: -2px;
  text-transform: uppercase;
}

.zc-about-hero__title span,
.zc-about-hero__title strong {
  display: block;
}

.zc-about-hero__title strong {
  color: #ff5b1a;
  font-weight: 950;
}

.zc-about-hero__text {
  max-width: 660px;
  margin: 22px auto 0;
  color: #333333;
  font-size: 17px;
  line-height: 1.6;
  font-weight: 650;
}

.zc-about-hero__buttons {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  flex-wrap: wrap;
  margin-top: 28px;
}

.zc-about-hero__btn {
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

.zc-about-hero__btn--orange {
  background: #ff5b1a;
}

.zc-about-hero__btn--orange:hover {
  background: #111111;
}

.zc-about-hero__btn--dark {
  background: #111111;
}

.zc-about-hero__btn--dark:hover {
  background: #ff5b1a;
}

@media screen and (max-width: 768px) {
  .zc-about-hero {
    padding: 30px 0 56px;
  }

  .zc-about-hero__container {
    width: min(100% - 30px, 1280px);
  }

  .zc-about-hero__breadcrumb {
    margin-bottom: 26px;
  }

  .zc-about-hero__title {
    font-size: 44px;
    letter-spacing: -1px;
  }

  .zc-about-hero__text {
    font-size: 15px;
  }
}

@media screen and (max-width: 480px) {
  .zc-about-hero__title {
    font-size: 36px;
  }

  .zc-about-hero__buttons {
    flex-direction: column;
    align-items: stretch;
  }

  .zc-about-hero__btn {
    width: 100%;
  }
}
</style>
