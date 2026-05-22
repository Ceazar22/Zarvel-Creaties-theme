<section class="zc-hero-section">
  <div class="zc-hero-container">

    <div class="zc-hero-content">
      <p class="zc-hero-eyebrow">PRINT YOUR IDEA. WEAR YOUR STORY.</p>

      <h1 class="zc-hero-title">
        CUSTOM PRINTS <br>
        MADE FOR YOUR 1STYLE
      </h1>

      <p class="zc-hero-text">
        Create personalized shirts, hoodies, mugs, and more with high-quality prints made on demand.
      </p>

      <div class="zc-hero-buttons">
        <a href="<?php echo esc_url(home_url('/shop')); ?>" class="zc-hero-btn zc-hero-btn--orange">
          SHOP PRODUCTS
        </a>

        <a href="<?php echo esc_url(home_url('/customize')); ?>" class="zc-hero-btn zc-hero-btn--black">
          FILL OUT DESIGN FORM
          <span>→</span>
        </a>
      </div>

      <div class="zc-hero-features">
        <div class="zc-hero-feature">
          <span class="zc-hero-feature-icon">
            <svg viewBox="0 0 24 24">
              <path d="M12 2l7 4v6c0 5-3 9-7 10-4-1-7-5-7-10V6l7-4z"/>
              <path d="M9 12l2 2 4-5"/>
            </svg>
          </span>
          <span>Premium Quality<br>Prints</span>
        </div>

        <div class="zc-hero-feature">
          <span class="zc-hero-feature-icon">
            <svg viewBox="0 0 24 24">
              <path d="M12 2l7 4v6c0 5-3 9-7 10-4-1-7-5-7-10V6l7-4z"/>
              <path d="M8 12h8"/>
            </svg>
          </span>
          <span>No Minimum<br>Order</span>
        </div>

        <div class="zc-hero-feature">
          <span class="zc-hero-feature-icon">
            <svg viewBox="0 0 24 24">
              <path d="M3 7h11v9H3z"/>
              <path d="M14 10h4l3 3v3h-7z"/>
              <circle cx="7" cy="18" r="2"/>
              <circle cx="18" cy="18" r="2"/>
            </svg>
          </span>
          <span>Fast & Reliable<br>Shipping</span>
        </div>
      </div>
    </div>

    <div class="zc-hero-visual">

      <div class="zc-hero-brush"></div>

      <img
        class="zc-hero-product zc-hero-hoodie"
        src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/hero-hoodie.png'); ?>"
        alt="Black custom hoodie"
      >

      <img
        id="zcHeroTshirt"
        class="zc-hero-product zc-hero-tshirt"
        src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/hero-tshirt-white.png'); ?>"
        alt="Custom t-shirt"
      >

      <img
        class="zc-hero-product zc-hero-mug"
        src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/hero-mug.png'); ?>"
        alt="Custom printed mug"
      >

      <div class="zc-floating-card zc-floating-card--bottom">
        <div class="zc-floating-card-content">
          <h3>Custom T-Shirt</h3>
          <p>₱699.00</p>

          <div class="zc-color-dots">
            <button
              class="zc-dot zc-dot--white is-active"
              type="button"
              aria-label="White shirt"
              data-image="<?php echo esc_url(get_template_directory_uri() . '/assets/images/hero-tshirt-white.png'); ?>">
            </button>

            <button
              class="zc-dot zc-dot--red"
              type="button"
              aria-label="Red shirt"
              data-image="<?php echo esc_url(get_template_directory_uri() . '/assets/images/hero-tshirt-red.png'); ?>">
            </button>

            <button
              class="zc-dot zc-dot--mustard"
              type="button"
              aria-label="Mustard shirt"
              data-image="<?php echo esc_url(get_template_directory_uri() . '/assets/images/hero-tshirt-mustard.png'); ?>">
            </button>
          </div>
        </div>

        <span class="zc-floating-arrow">›</span>
      </div>

    </div>

  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const tshirtImage = document.querySelector('#zcHeroTshirt');
  const colorButtons = document.querySelectorAll('.zc-color-dots .zc-dot');

  if (!tshirtImage || !colorButtons.length) return;

  colorButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      const newImage = button.getAttribute('data-image');

      if (!newImage) return;

      tshirtImage.classList.add('is-changing');

      setTimeout(function () {
        tshirtImage.src = newImage;
        tshirtImage.classList.remove('is-changing');
      }, 150);

      colorButtons.forEach(function (item) {
        item.classList.remove('is-active');
      });

      button.classList.add('is-active');
    });
  });
});
</script>

<style>
/* ================================
   Homepage Hero Section
================================ */

.zc-hero-section {
  position: relative;
  overflow: hidden;
  background:
    radial-gradient(circle at 20% 20%, rgba(255, 106, 33, 0.08), transparent 28%),
    linear-gradient(90deg, #ffffff 0%, #fff8f3 48%, #f7f7f7 100%);
  min-height: calc(100vh - 104px);
  display: flex;
  align-items: center;
}

.zc-hero-container {
  width: min(100% - 40px, 1280px);
  margin: 0 auto;
  display: grid;
  grid-template-columns: 46% 54%;
  align-items: center;
  gap: 30px;
  padding: 70px 0;
}

.zc-hero-content {
  position: relative;
  z-index: 2;
}

.zc-hero-eyebrow {
  margin: 0 0 20px;
  color: #ff5b1a;
  font-size: 13px;
  font-weight: 900;
  letter-spacing: 0.3px;
}

.zc-hero-title {
  margin: 0;
  color: #050505;
  font-size: clamp(44px, 5vw, 78px);
  line-height: 0.95;
  font-weight: 950;
  letter-spacing: -2.5px;
  text-transform: uppercase;
}

.zc-hero-text {
  max-width: 530px;
  margin: 24px 0 0;
  color: #2b2b2b;
  font-size: 19px;
  line-height: 1.45;
  font-weight: 500;
}

.zc-hero-buttons {
  display: flex;
  align-items: center;
  gap: 18px;
  margin-top: 34px;
}

.zc-hero-btn {
  min-width: 188px;
  height: 58px;
  padding: 0 26px;
  border-radius: 6px;
  text-decoration: none;
  font-size: 13px;
  font-weight: 900;
  letter-spacing: 0.2px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  transition: 0.2s ease;
}

.zc-hero-btn--orange {
  background: #ff5b1a;
  color: #ffffff;
}

.zc-hero-btn--orange:hover {
  background: #e94d10;
  color: #ffffff;
  transform: translateY(-2px);
}

.zc-hero-btn--black {
  background: #050505;
  color: #ffffff;
}

.zc-hero-btn--black:hover {
  background: #222222;
  color: #ffffff;
  transform: translateY(-2px);
}

.zc-hero-features {
  display: flex;
  align-items: center;
  gap: 48px;
  margin-top: 46px;
}

.zc-hero-feature {
  display: flex;
  align-items: center;
  gap: 14px;
  color: #171717;
  font-size: 14px;
  font-weight: 600;
  line-height: 1.25;
}

.zc-hero-feature-icon {
  width: 28px;
  height: 28px;
  flex: 0 0 28px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.zc-hero-feature-icon svg {
  width: 100%;
  height: 100%;
  fill: none;
  stroke: currentColor;
  stroke-width: 1.8;
  stroke-linecap: round;
  stroke-linejoin: round;
}

/* Right Visual */
.zc-hero-visual {
  position: relative;
  min-height: 560px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.zc-hero-brush {
  position: absolute;
  top: 58px;
  right: 120px;
  width: 390px;
  height: 170px;
  background: #ff5b1a;
  transform: rotate(-12deg);
  clip-path: polygon(
    0 35%,
    12% 22%,
    24% 30%,
    36% 12%,
    50% 24%,
    68% 0,
    82% 18%,
    100% 8%,
    88% 52%,
    100% 74%,
    75% 66%,
    62% 100%,
    48% 72%,
    32% 92%,
    20% 70%,
    0 82%
  );
  z-index: 1;
}

.zc-hero-product {
  position: absolute;
  display: block;
  height: auto;
  object-fit: contain;
  filter: drop-shadow(0 24px 32px rgba(0, 0, 0, 0.16));
}

.zc-hero-hoodie {
  z-index: 3;
  width: 470px;
  left: 20px;
  bottom: 40px;
}

.zc-hero-tshirt {
  z-index: 4;
  width: 330px;
  right: 90px;
  bottom: 45px;
  transition: opacity 0.25s ease, transform 0.25s ease;
}

.zc-hero-tshirt.is-changing {
  opacity: 0;
  transform: scale(0.97);
}

.zc-hero-mug {
  z-index: 5;
  width: 170px;
  left: 0;
  bottom: 38px;
}

/* Floating Card */
.zc-floating-card {
  position: absolute;
  z-index: 8;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 16px 40px rgba(0, 0, 0, 0.13);
  display: flex;
  align-items: center;
  gap: 14px;
}

.zc-floating-card--bottom {
  right: -10px;
  bottom: 84px;
  width: 250px;
  min-height: 108px;
  padding: 20px 22px;
}

.zc-floating-card-content {
  flex: 1;
}

.zc-floating-card h3 {
  margin: 0 0 5px;
  color: #333333;
  font-size: 14px;
  font-weight: 900;
}

.zc-floating-card p {
  margin: 0;
  color: #333333;
  font-size: 13px;
  font-weight: 800;
}

.zc-floating-arrow {
  color: #999999;
  font-size: 24px;
  font-weight: 500;
}

.zc-color-dots {
  display: flex;
  align-items: center;
  gap: 9px;
  margin-top: 13px;
}

.zc-dot {
  width: 15px;
  height: 15px;
  border-radius: 50%;
  display: inline-block;
  border: 2px solid transparent;
  padding: 0;
  cursor: pointer;
  transition: 0.2s ease;
}

.zc-dot:hover {
  transform: scale(1.15);
}

.zc-dot.is-active {
  border-color: #111111;
  box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px #111111;
}

.zc-dot--white {
  background: #ffffff;
  border-color: #d9d9d9;
}

.zc-dot--red {
  background: #c92828;
}

.zc-dot--mustard {
  background: #d69b22;
}

/* Tablet */
@media screen and (max-width: 1024px) {
  .zc-hero-container {
    grid-template-columns: 1fr;
    gap: 40px;
    padding: 60px 0;
  }

  .zc-hero-content {
    text-align: center;
  }

  .zc-hero-text {
    margin-left: auto;
    margin-right: auto;
  }

  .zc-hero-buttons {
    justify-content: center;
  }

  .zc-hero-features {
    justify-content: center;
  }

  .zc-hero-visual {
    min-height: 520px;
    width: min(100%, 720px);
    margin: 0 auto;
  }

  .zc-hero-hoodie {
    width: 450px;
    left: 35px;
    bottom: 35px;
  }

  .zc-hero-tshirt {
    width: 310px;
    right: 70px;
    bottom: 40px;
  }

  .zc-hero-mug {
    width: 155px;
    left: 25px;
    bottom: 35px;
  }

  .zc-floating-card--bottom {
    right: 20px;
  }
}

/* Mobile */
@media screen and (max-width: 768px) {
  .zc-hero-section {
    min-height: auto;
  }

  .zc-hero-container {
    width: min(100% - 30px, 1280px);
    padding: 48px 0 56px;
  }

  .zc-hero-title {
    font-size: clamp(38px, 13vw, 62px);
    letter-spacing: -1.5px;
  }

  .zc-hero-text {
    font-size: 16px;
  }

  .zc-hero-buttons {
    flex-direction: column;
    gap: 12px;
  }

  .zc-hero-btn {
    width: 100%;
    min-width: 0;
  }

  .zc-hero-features {
    flex-direction: column;
    align-items: flex-start;
    width: fit-content;
    margin-left: auto;
    margin-right: auto;
    gap: 20px;
  }

  .zc-hero-visual {
    min-height: 390px;
    width: 100%;
  }

  .zc-hero-brush {
    width: 280px;
    height: 120px;
    top: 35px;
    right: 20px;
  }

  .zc-hero-hoodie {
    width: 330px;
    left: -10px;
    bottom: 30px;
  }

  .zc-hero-tshirt {
    width: 220px;
    right: 10px;
    bottom: 35px;
  }

  .zc-hero-mug {
    width: 115px;
    left: -5px;
    bottom: 28px;
  }

  .zc-floating-card--bottom {
    right: 0;
    bottom: 30px;
    width: 200px;
    min-height: 92px;
    padding: 14px;
  }

  .zc-floating-card h3 {
    font-size: 12px;
  }

  .zc-floating-card p {
    font-size: 12px;
  }

  .zc-dot {
    width: 12px;
    height: 12px;
  }
}

/* Small Mobile */
@media screen and (max-width: 480px) {
  .zc-hero-content {
    text-align: left;
  }

  .zc-hero-buttons {
    align-items: stretch;
  }

  .zc-hero-features {
    margin-left: 0;
  }

  .zc-hero-visual {
    min-height: 320px;
  }

  .zc-hero-hoodie {
    width: 260px;
    left: -30px;
    bottom: 35px;
  }

  .zc-hero-tshirt {
    width: 170px;
    right: -15px;
    bottom: 45px;
  }

  .zc-hero-mug {
    width: 95px;
    left: -20px;
    bottom: 35px;
  }

  .zc-floating-card--bottom {
    transform: scale(0.82);
    transform-origin: bottom right;
  }
}
</style>
