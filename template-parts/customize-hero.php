<?php
defined('ABSPATH') || exit;
?>

<section class="zc-customize-hero">
  <div class="zc-customize-hero__container">

    <nav class="zc-customize-hero__breadcrumb" aria-label="Breadcrumb">
      <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
      <span>›</span>
      <strong>Design Form</strong>
    </nav>

    <div class="zc-customize-hero__content">
      <h1 class="zc-customize-hero__title">
        <span>Tell Us</span>
        <strong>Your Design</strong>
      </h1>

      <p class="zc-customize-hero__text">
        Fill out the form with your product, artwork, colors, placement, and design notes
        so we can prepare exactly what you have in mind.
      </p>
    </div>

  </div>
</section>

<style>
.zc-customize-hero {
  width: 100%;
  padding: 38px 0 56px;
  background:
    radial-gradient(circle at 50% 20%, rgba(255, 91, 26, 0.08), transparent 34%),
    linear-gradient(90deg, #ffffff 0%, #fff8f4 100%);
  border-top: 1px solid #efebe7;
  border-bottom: 1px solid #efebe7;
}

.zc-customize-hero__container {
  width: min(100% - 40px, 1280px);
  margin: 0 auto;
}

.zc-customize-hero__breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 14px;
  color: #8a8a8a;
  font-size: 12px;
  line-height: 1.2;
  font-weight: 700;
}

.zc-customize-hero__breadcrumb a {
  color: #8a8a8a;
  text-decoration: none;
}

.zc-customize-hero__breadcrumb a:hover {
  color: #ff5b1a;
}

.zc-customize-hero__breadcrumb span {
  color: #b8b8b8;
}

.zc-customize-hero__breadcrumb strong {
  color: #111111;
  font-weight: 800;
}

.zc-customize-hero__content {
  max-width: 760px;
  margin: 0 auto;
  text-align: center;
}

.zc-customize-hero__title {
  margin: 0;
  color: #111111;
  font-size: clamp(48px, 7vw, 88px);
  line-height: 0.95;
  font-weight: 950;
  text-transform: uppercase;
  letter-spacing: -2px;
}

.zc-customize-hero__title span,
.zc-customize-hero__title strong {
  display: block;
}

.zc-customize-hero__title span {
  color: #111111;
}

.zc-customize-hero__title strong {
  color: #ff5b1a;
  font-weight: 950;
}

.zc-customize-hero__text {
  max-width: 610px;
  margin: 22px auto 0;
  color: #333333;
  font-size: 17px;
  line-height: 1.55;
  font-weight: 650;
}

@media screen and (max-width: 768px) {
  .zc-customize-hero {
    padding: 30px 0 44px;
  }

  .zc-customize-hero__container {
    width: min(100% - 30px, 1280px);
  }

  .zc-customize-hero__breadcrumb {
    margin-bottom: 20px;
  }

  .zc-customize-hero__title {
    font-size: 46px;
    letter-spacing: -1px;
  }

  .zc-customize-hero__text {
    font-size: 15px;
  }
}

@media screen and (max-width: 480px) {
  .zc-customize-hero__title {
    font-size: 38px;
  }

  .zc-customize-hero__text {
    font-size: 14px;
  }
}
</style>
