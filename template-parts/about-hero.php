<?php
defined('ABSPATH') || exit;

$customize_url = home_url('/customize/');
$shop_url      = home_url('/shop/');
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
        We’re Here To Help<br>
        You Wear Your Story.
      </h1>

      <p class="zc-about-hero__text">
        Printly was built for creators, dreamers, and doers who want more than just ordinary.
        We turn ideas into high-quality prints you’ll love.
      </p>

      <div class="zc-about-hero__buttons">
        <a href="<?php echo esc_url($customize_url); ?>" class="zc-about-hero__btn zc-about-hero__btn--orange">
          Start Customizing
        </a>

        <a href="<?php echo esc_url($shop_url); ?>" class="zc-about-hero__btn zc-about-hero__btn--dark">
          Shop Products
          <span>→</span>
        </a>
      </div>
    </div>

  </div>
</section>

<style>
.zc-about-hero {
  width: 100%;
  padding: 38px 0 72px;
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
  margin-bottom: 34px;
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
  max-width: 590px;
}

.zc-about-hero__kicker {
  display: inline-block;
  margin-bottom: 18px;
  color: #ff5b1a;
  font-size: 12px;
  line-height: 1;
  font-weight: 950;
  text-transform: uppercase;
}

.zc-about-hero__title {
  margin: 0;
  color: #111111;
  font-size: clamp(42px, 5.8vw, 76px);
  line-height: 0.92;
  font-weight: 950;
  letter-spacing: -1.8px;
  text-transform: uppercase;
}

.zc-about-hero__text {
  max-width: 470px;
  margin: 24px 0 26px;
  color: #333333;
  font-size: 17px;
  line-height: 1.6;
  font-weight: 650;
}

.zc-about-hero__buttons {
  display: flex;
  align-items: center;
  gap: 14px;
  flex-wrap: wrap;
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