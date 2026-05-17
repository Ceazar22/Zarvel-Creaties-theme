<?php
defined('ABSPATH') || exit;

$zc_customize_url = home_url('/customize/');
?>

<section class="zc-collections-idea-banner">
  <div class="zc-collections-idea-banner__container">

    <div class="zc-collections-idea-banner__content">
      <div class="zc-collections-idea-banner__text">
        <h2>HAVE A UNIQUE IDEA?</h2>

        <p>
          We’ll turn your design into shirts, mugs, hoodies, and prints made just the way you imagined.
        </p>
      </div>

      <a href="<?php echo esc_url($zc_customize_url); ?>" class="zc-collections-idea-banner__button">
        Fill Out Design Form
        <span>→</span>
      </a>
    </div>

  </div>
</section>

<style>
.zc-collections-idea-banner {
  padding: 10px 0 70px;
  background: #ffffff;
}

.zc-collections-idea-banner__container {
  width: min(100% - 40px, 1280px);
  margin: 0 auto;
}

.zc-collections-idea-banner__content {
  min-height: 86px;
  padding: 18px 26px;
  border-radius: 8px;
  background:
    linear-gradient(90deg, rgba(0, 0, 0, 0.94), rgba(0, 0, 0, 0.82)),
    repeating-linear-gradient(
      135deg,
      rgba(255, 255, 255, 0.06) 0,
      rgba(255, 255, 255, 0.06) 1px,
      transparent 1px,
      transparent 9px
    );
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  overflow: hidden;
  position: relative;
}

.zc-collections-idea-banner__content::before {
  content: "";
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 18% 50%, rgba(255, 91, 26, 0.18), transparent 24%),
    radial-gradient(circle at 82% 50%, rgba(255, 255, 255, 0.08), transparent 28%);
  pointer-events: none;
}

.zc-collections-idea-banner__text,
.zc-collections-idea-banner__button {
  position: relative;
  z-index: 2;
}

.zc-collections-idea-banner__text h2 {
  margin: 0 0 5px;
  color: #ffffff;
  font-size: 24px;
  line-height: 1;
  font-weight: 950;
  text-transform: uppercase;
  letter-spacing: -0.3px;
}

.zc-collections-idea-banner__text p {
  max-width: 560px;
  margin: 0;
  color: rgba(255, 255, 255, 0.78);
  font-size: 13px;
  line-height: 1.45;
  font-weight: 650;
}

.zc-collections-idea-banner__button {
  min-height: 42px;
  padding: 0 18px;
  border-radius: 6px;
  background: #ff5b1a;
  color: #ffffff;
  text-decoration: none;
  font-size: 11px;
  line-height: 1;
  font-weight: 950;
  text-transform: uppercase;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  white-space: nowrap;
  transition: 0.2s ease;
}

.zc-collections-idea-banner__button:hover {
  background: #ffffff;
  color: #111111;
}

.zc-collections-idea-banner__button span {
  font-size: 15px;
  line-height: 1;
}

@media screen and (max-width: 768px) {
  .zc-collections-idea-banner {
    padding: 0 0 55px;
  }

  .zc-collections-idea-banner__container {
    width: min(100% - 30px, 1280px);
  }

  .zc-collections-idea-banner__content {
    min-height: auto;
    padding: 22px 18px;
    flex-direction: column;
    align-items: flex-start;
  }

  .zc-collections-idea-banner__text h2 {
    font-size: 22px;
  }

  .zc-collections-idea-banner__button {
    width: 100%;
  }
}
</style>
