<section class="zc-custom-design-section">
  <div class="zc-custom-design-container">

    <div class="zc-custom-design-card">

      <div class="zc-custom-design-content">
        <h2>HAVE YOUR OWN DESIGN?</h2>

        <p>
          Upload your artwork and turn it into amazing products. Perfect for personal use,
          gifts, events, brands, and businesses.
        </p>

        <a href="<?php echo esc_url(home_url('/customize')); ?>" class="zc-custom-design-btn">
          FILL OUT DESIGN FORM
          <span>→</span>
        </a>
      </div>

      <div class="zc-custom-upload-area">
        <div class="zc-custom-upload-box">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 16V4"></path>
            <path d="M7 9l5-5 5 5"></path>
            <path d="M5 16v3h14v-3"></path>
          </svg>
        </div>

        <div class="zc-custom-upload-text">
          <h3>Send us your logo</h3>
          <p>Tell us the product, placement, colors, and artwork you want.</p>
        </div>
      </div>

    </div>

  </div>
</section>

<style>
.zc-custom-design-section {
  padding: 20px 0 75px;
  background: #ffffff;
  overflow: hidden;
}

.zc-custom-design-container {
  width: min(100% - 40px, 1280px);
  margin: 0 auto;
}

.zc-custom-design-card {
  position: relative;
  overflow: hidden;
  min-height: 235px;
  border-radius: 14px;
  background:
    radial-gradient(circle at 78% 28%, rgba(255, 255, 255, 0.12), transparent 26%),
    linear-gradient(90deg, #050505 0%, #101010 50%, #181818 100%);
  display: grid;
  grid-template-columns: 48% 52%;
  align-items: center;
  gap: 40px;
  padding: 42px 52px;
  isolation: isolate;
}

.zc-custom-design-card::before {
  content: "";
  position: absolute;
  inset: 0;
  background:
    repeating-linear-gradient(
      135deg,
      rgba(255, 255, 255, 0.045) 0 2px,
      rgba(255, 255, 255, 0) 2px 9px
    );
  opacity: 0.65;
  z-index: -2;
}

.zc-custom-design-card::after {
  content: "";
  position: absolute;
  inset: 0;
  background:
    linear-gradient(
      90deg,
      rgba(0, 0, 0, 0.78) 0%,
      rgba(0, 0, 0, 0.35) 54%,
      rgba(0, 0, 0, 0.62) 100%
    );
  z-index: -1;
}

.zc-custom-design-content {
  position: relative;
  z-index: 3;
}

.zc-custom-design-content h2 {
  margin: 0 0 14px;
  color: #ffffff;
  font-size: clamp(32px, 4vw, 54px);
  line-height: 0.92;
  font-weight: 950;
  text-transform: uppercase;
  letter-spacing: -1px;
}

.zc-custom-design-content p {
  max-width: 480px;
  margin: 0;
  color: rgba(255, 255, 255, 0.92);
  font-size: 15px;
  line-height: 1.5;
  font-weight: 600;
}

.zc-custom-design-btn {
  width: fit-content;
  margin-top: 26px;
  min-height: 48px;
  padding: 0 24px;
  border-radius: 7px;
  background: #ff5b1a;
  color: #ffffff;
  text-decoration: none;
  font-size: 13px;
  font-weight: 950;
  text-transform: uppercase;
  display: inline-flex;
  align-items: center;
  gap: 12px;
  transition: 0.2s ease;
}

.zc-custom-design-btn:hover {
  background: #ffffff;
  color: #111111;
  transform: translateY(-2px);
}

.zc-custom-upload-area {
  position: relative;
  z-index: 3;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 26px;
  min-height: 150px;
}

.zc-custom-upload-box {
  width: 88px;
  height: 88px;
  border: 2px dashed rgba(255, 255, 255, 0.72);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.zc-custom-upload-box svg {
  width: 38px;
  height: 38px;
  fill: none;
  stroke: #ffffff;
  stroke-width: 2;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.zc-custom-upload-text {
  max-width: 280px;
}

.zc-custom-upload-text h3 {
  margin: 0 0 8px;
  color: #ffffff;
  font-size: 24px;
  line-height: 1;
  font-weight: 950;
  text-transform: uppercase;
}

.zc-custom-upload-text p {
  margin: 0;
  color: rgba(255, 255, 255, 0.78);
  font-size: 14px;
  line-height: 1.45;
  font-weight: 600;
}

/* 1024 */
@media screen and (max-width: 1024px) {
  .zc-custom-design-card {
    grid-template-columns: 1fr;
    gap: 32px;
    padding: 40px 36px;
  }

  .zc-custom-design-content {
    max-width: 560px;
  }

  .zc-custom-upload-area {
    justify-content: flex-start;
  }
}

/* 768 */
@media screen and (max-width: 768px) {
  .zc-custom-design-section {
    padding: 15px 0 55px;
  }

  .zc-custom-design-container {
    width: min(100% - 30px, 1280px);
  }

  .zc-custom-design-card {
    padding: 34px 24px;
  }

  .zc-custom-design-content h2 {
    font-size: 34px;
  }

  .zc-custom-design-content p {
    font-size: 13px;
  }

  .zc-custom-upload-area {
    align-items: flex-start;
    gap: 18px;
  }

  .zc-custom-upload-box {
    width: 68px;
    height: 68px;
  }

  .zc-custom-upload-box svg {
    width: 30px;
    height: 30px;
  }

  .zc-custom-upload-text h3 {
    font-size: 18px;
  }

  .zc-custom-upload-text p {
    font-size: 13px;
  }
}

/* 480 */
@media screen and (max-width: 480px) {
  .zc-custom-design-card {
    padding: 30px 20px;
  }

  .zc-custom-design-btn {
    width: 100%;
    justify-content: center;
  }

  .zc-custom-upload-area {
    flex-direction: column;
  }

  .zc-custom-upload-text {
    max-width: 100%;
  }
}
</style>
