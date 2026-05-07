<?php
defined('ABSPATH') || exit;

get_header();
?>

<main class="zc-account-page">
  <section class="zc-account-hero">
    <div class="zc-account-container">
      <p>Account</p>
      <h1>Your orders and details.</h1>
      <span>Sign in to check previous orders, update addresses, and manage your customer profile.</span>
    </div>
  </section>

  <section class="zc-account-shell">
    <div class="zc-account-container">
      <div class="zc-account-card">
        <?php echo do_shortcode('[woocommerce_my_account]'); ?>
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

.zc-account-card .woocommerce {
  font-family: inherit;
}

.zc-account-card .woocommerce-MyAccount-navigation ul {
  list-style: none;
  margin: 0 0 26px;
  padding: 0;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.zc-account-card .woocommerce-MyAccount-navigation a {
  min-height: 42px;
  border: 1px solid #dfdfdf;
  border-radius: 999px;
  padding: 0 16px;
  color: #050505;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  font-size: 13px;
  font-weight: 900;
  text-transform: uppercase;
}

.zc-account-card .woocommerce-MyAccount-navigation .is-active a {
  border-color: #050505;
  background: #050505;
  color: #ffffff;
}

.zc-account-card input.input-text,
.zc-account-card textarea,
.zc-account-card select {
  min-height: 48px;
  border: 1px solid #dcdcdc;
  border-radius: 8px;
  padding: 12px 14px;
}

.zc-account-card .button {
  min-height: 50px;
  border: 0;
  border-radius: 8px;
  padding: 0 24px;
  background: #ff5b1a;
  color: #ffffff;
  font-size: 14px;
  font-weight: 950;
  text-transform: uppercase;
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
