<?php
defined('ABSPATH') || exit;

get_header();
?>

<main class="zc-account-page">
  <section class="zc-account-hero">
    <div class="zc-account-container">
      <p>Account</p>
      <h1>Your private custom portal.</h1>
      <span>Log in to see design requests and custom products shared only with your Zarvel account.</span>
    </div>
  </section>

  <section class="zc-account-shell">
    <div class="zc-account-container">
      <div class="zc-account-card">
        <?php if (shortcode_exists('zarvel_customer_portal')) : ?>
          <?php echo do_shortcode('[zarvel_customer_portal]'); ?>
        <?php else : ?>
          <p>The Zarvel customer portal plugin must be active.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<style>
.zc-account-page {
  background: #f7f7f7;
  color: #050505;
}

.zc-account-container {
  width: min(100% - 44px, 1160px);
  margin: 0 auto;
}

.zc-account-hero {
  background: #050505;
  color: #ffffff;
  padding: 58px 0 64px;
}

.zc-account-hero p {
  margin: 0 0 12px;
  color: #ff5b1a;
  font-size: 13px;
  font-weight: 950;
  text-transform: uppercase;
}

.zc-account-hero h1 {
  margin: 0;
  max-width: 720px;
  font-size: clamp(38px, 6vw, 72px);
  line-height: 0.95;
  font-weight: 950;
}

.zc-account-hero span {
  display: block;
  margin-top: 18px;
  max-width: 540px;
  color: rgba(255, 255, 255, 0.76);
  font-size: 16px;
  line-height: 1.55;
  font-weight: 500;
}

.zc-account-shell {
  padding: 34px 0 76px;
}

.zc-account-card {
  background: #ffffff;
  border: 1px solid #e8e8e8;
  border-radius: 14px;
  box-shadow: 0 22px 60px rgba(0, 0, 0, 0.06);
  padding: 30px;
}

@media (max-width: 640px) {
  .zc-account-container {
    width: min(100% - 28px, 1160px);
  }

  .zc-account-card {
    padding: 20px;
  }
}
</style>

<?php
get_footer();
