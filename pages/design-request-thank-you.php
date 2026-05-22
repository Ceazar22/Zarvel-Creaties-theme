<?php
defined('ABSPATH') || exit;

$design_request_id = isset($_GET['design_request']) ? absint(wp_unslash($_GET['design_request'])) : 0;
$deposit_url = function_exists('zarvel_get_design_deposit_checkout_url')
    ? zarvel_get_design_deposit_checkout_url($design_request_id)
    : home_url('/checkout/');
$deposit_amount = function_exists('wc_price')
    ? wc_price((float) ZARVEL_DESIGN_DEPOSIT_AMOUNT)
    : '$' . number_format((float) ZARVEL_DESIGN_DEPOSIT_AMOUNT, 2);

get_header();
?>

<main class="zc-design-thanks-page">
  <section class="zc-design-thanks">
    <div class="zc-design-thanks__inner">
      <p>Design request received</p>
      <h1>Your notes and artwork are with us.</h1>
      <span>
        Pay the design deposit to start the review. We will use your request details to prepare the next custom product step.
      </span>

      <div class="zc-design-thanks__actions">
        <a href="<?php echo esc_url($deposit_url); ?>">
          Pay Design Deposit <?php echo esc_html(wp_strip_all_tags($deposit_amount)); ?>
        </a>
        <a href="<?php echo esc_url(home_url('/my-account/')); ?>">My Account</a>
      </div>
    </div>
  </section>

  <section class="zc-design-next">
    <div class="zc-design-next__inner">
      <article>
        <b>1</b>
        <h2>Deposit</h2>
        <p>The deposit confirms you want us to review and begin the custom design request.</p>
      </article>
      <article>
        <b>2</b>
        <h2>Proof</h2>
        <p>We prepare the design direction from your product choice, notes, and uploaded artwork.</p>
      </article>
      <article>
        <b>3</b>
        <h2>Private Product</h2>
        <p>Your approved custom product can be shared through your customer account for the next order step.</p>
      </article>
    </div>
  </section>
</main>

<style>
.zc-design-thanks-page {
  background: #f7f7f7;
  color: #050505;
}

.zc-design-thanks {
  padding: 76px 0 66px;
  background: #050505;
  color: #ffffff;
}

.zc-design-thanks__inner,
.zc-design-next__inner {
  width: min(100% - 44px, 1120px);
  margin: 0 auto;
}

.zc-design-thanks p {
  margin: 0 0 14px;
  color: #ff5b1a;
  font-size: 13px;
  line-height: 1;
  font-weight: 950;
  text-transform: uppercase;
}

.zc-design-thanks h1 {
  max-width: 820px;
  margin: 0;
  font-size: clamp(40px, 6.5vw, 78px);
  line-height: 0.96;
  font-weight: 950;
}

.zc-design-thanks span {
  display: block;
  max-width: 620px;
  margin-top: 20px;
  color: rgba(255, 255, 255, 0.8);
  font-size: 17px;
  line-height: 1.55;
  font-weight: 500;
}

.zc-design-thanks__actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 30px;
}

.zc-design-thanks__actions a {
  min-height: 54px;
  padding: 0 24px;
  border: 1px solid rgba(255, 255, 255, 0.25);
  border-radius: 7px;
  color: #ffffff;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 950;
  text-transform: uppercase;
}

.zc-design-thanks__actions a:first-child {
  border-color: #ff5b1a;
  background: #ff5b1a;
  gap: 6px;
  white-space: nowrap;
}

.zc-design-next {
  padding: 42px 0 82px;
}

.zc-design-next__inner {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 18px;
}

.zc-design-next article {
  padding: 28px;
  border: 1px solid #e7e7e7;
  border-radius: 8px;
  background: #ffffff;
}

.zc-design-next b {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: #050505;
  color: #ffffff;
  display: grid;
  place-items: center;
}

.zc-design-next h2 {
  margin: 18px 0 10px;
  font-size: 24px;
  line-height: 1;
  font-weight: 950;
}

.zc-design-next p {
  margin: 0;
  color: #555555;
  font-size: 15px;
  line-height: 1.55;
}

@media (max-width: 760px) {
  .zc-design-thanks {
    padding: 54px 0;
  }

  .zc-design-thanks__inner,
  .zc-design-next__inner {
    width: min(100% - 28px, 1120px);
  }

  .zc-design-next__inner {
    grid-template-columns: 1fr;
  }

  .zc-design-thanks__actions a {
    width: 100%;
  }
}
</style>

<?php
get_footer();
