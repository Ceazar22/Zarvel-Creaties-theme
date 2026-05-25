<?php
defined('ABSPATH') || exit;

$request_status = isset($_GET['request_status'])
  ? sanitize_text_field(wp_unslash($_GET['request_status']))
  : '';
?>

<section class="zc-website-request">
  <div class="zc-website-request__container">
    <div class="zc-website-request__header">
      <span>Website project</span>
      <h2>Tell us about the site you need</h2>
    </div>

    <?php if ($request_status === 'success') : ?>
      <div class="zc-form-message zc-form-message--success">
        Your website request was sent successfully. We’ll get back to you soon.
      </div>
    <?php elseif ($request_status === 'failed') : ?>
      <div class="zc-form-message zc-form-message--error">
        Message failed to send. Please try again.
      </div>
    <?php elseif ($request_status === 'missing_fields') : ?>
      <div class="zc-form-message zc-form-message--error">
        Please fill in all required fields.
      </div>
    <?php elseif ($request_status === 'invalid_email') : ?>
      <div class="zc-form-message zc-form-message--error">
        Please enter a valid email address.
      </div>
    <?php elseif ($request_status === 'too_many_requests') : ?>
      <div class="zc-form-message zc-form-message--error">
        Please wait a minute before sending another request.
      </div>
    <?php elseif ($request_status === 'security_error') : ?>
      <div class="zc-form-message zc-form-message--error">
        Security check failed. Refresh the page and try again.
      </div>
    <?php elseif ($request_status === 'spam') : ?>
      <div class="zc-form-message zc-form-message--error">
        Submission blocked. Please try again.
      </div>
    <?php endif; ?>

    <form class="zc-website-form" method="post" action="<?php echo esc_url(add_query_arg('service', 'website', home_url('/contact/'))); ?>">
      <?php wp_nonce_field('zarvel_website_form_action', 'zarvel_website_nonce'); ?>

      <input type="hidden" name="zarvel_website_form_submit" value="1">

      <div style="position:absolute; left:-9999px; opacity:0; pointer-events:none;">
        <label for="website_url">Website</label>
        <input type="text" id="website_url" name="website_url" value="">
      </div>

      <div class="zc-website-form__grid">
        <div class="zc-website-form__panel">
          <div class="zc-form-field">
            <label for="zc_site_full_name">Full Name <span>*</span></label>
            <input id="zc_site_full_name" type="text" name="full_name" placeholder="Enter your full name" required>
          </div>

          <div class="zc-form-field">
            <label for="zc_site_email">Email Address <span>*</span></label>
            <input id="zc_site_email" type="email" name="email" placeholder="Enter your email address" required>
          </div>

          <div class="zc-form-field">
            <label for="zc_site_phone">Phone Number Optional</label>
            <input id="zc_site_phone" type="text" name="phone" placeholder="Enter your phone number">
          </div>

          <div class="zc-form-field">
            <label for="zc_business_name">Business / Brand Name</label>
            <input id="zc_business_name" type="text" name="business_name" placeholder="Example: Zarvel Creatives">
          </div>

          <div class="zc-form-field">
            <label for="zc_current_website">Current Website</label>
            <input id="zc_current_website" type="url" name="current_website" placeholder="https://example.com">
          </div>
        </div>

        <div class="zc-website-form__panel">
          <div class="zc-form-field">
            <label for="zc_website_type">Website Type <span>*</span></label>
            <select id="zc_website_type" name="website_type" required>
              <option value="">Select website type</option>
              <option value="Shopify store">Shopify store</option>
              <option value="WordPress website">WordPress website</option>
              <option value="WooCommerce store">WooCommerce store</option>
              <option value="Landing page">Landing page</option>
              <option value="Service business website">Service business website</option>
              <option value="Website redesign">Website redesign</option>
              <option value="Not sure yet">Not sure yet</option>
            </select>
          </div>

          <div class="zc-form-field">
            <label for="zc_platform">Preferred Platform</label>
            <select id="zc_platform" name="platform">
              <option value="">Select platform</option>
              <option value="Shopify">Shopify</option>
              <option value="WordPress">WordPress</option>
              <option value="WooCommerce">WooCommerce</option>
              <option value="No preference">No preference</option>
            </select>
          </div>

          <div class="zc-form-field">
            <label for="zc_budget">Budget Range</label>
            <select id="zc_budget" name="budget">
              <option value="">Select budget</option>
              <option value="Under $500">Under $500</option>
              <option value="$500 - $1,500">$500 - $1,500</option>
              <option value="$1,500 - $3,000">$1,500 - $3,000</option>
              <option value="$3,000+">$3,000+</option>
              <option value="Need guidance">Need guidance</option>
            </select>
          </div>

          <div class="zc-form-field">
            <label for="zc_timeline">Timeline</label>
            <select id="zc_timeline" name="timeline">
              <option value="">Select timeline</option>
              <option value="ASAP">ASAP</option>
              <option value="2 - 4 weeks">2 - 4 weeks</option>
              <option value="1 - 2 months">1 - 2 months</option>
              <option value="Flexible">Flexible</option>
            </select>
          </div>
        </div>
      </div>

      <div class="zc-website-form__features">
        <label><input type="checkbox" name="features[]" value="Online store"> Online store</label>
        <label><input type="checkbox" name="features[]" value="Product pages"> Product pages</label>
        <label><input type="checkbox" name="features[]" value="Booking or contact form"> Booking or contact form</label>
        <label><input type="checkbox" name="features[]" value="Payments"> Payments</label>
        <label><input type="checkbox" name="features[]" value="Email signup"> Email signup</label>
        <label><input type="checkbox" name="features[]" value="SEO setup"> SEO setup</label>
      </div>

      <div class="zc-form-field">
        <label for="zc_project_notes">Website Goals / Project Details <span>*</span></label>
        <textarea id="zc_project_notes" name="project_notes" rows="7" placeholder="Tell us what you want the website to do, pages you need, examples you like, products/services you sell, and anything important..." required></textarea>
      </div>

      <div class="zc-website-form__footer">
        <button type="submit">Submit Website Request</button>
        <p>We respect your privacy. Your information will only be used to process your request.</p>
      </div>
    </form>
  </div>
</section>

<style>
.zc-website-request {
  padding: 42px 0 90px;
  background: #ffffff;
}

.zc-website-request__container {
  width: min(100% - 40px, 1120px);
  margin: 0 auto;
}

.zc-website-request__header {
  margin-bottom: 22px;
  text-align: center;
}

.zc-website-request__header span {
  display: block;
  margin-bottom: 10px;
  color: #ff5b1a;
  font-size: 12px;
  font-weight: 950;
  text-transform: uppercase;
}

.zc-website-request__header h2 {
  margin: 0;
  color: #050505;
  font-size: clamp(28px, 4vw, 44px);
  line-height: 1;
  font-weight: 950;
  text-transform: uppercase;
}

.zc-website-form {
  padding: 30px;
  border: 1px solid #dedede;
  border-radius: 10px;
  background: #ffffff;
}

.zc-website-form__grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 26px;
}

.zc-form-field {
  margin-bottom: 16px;
}

.zc-form-field label {
  display: block;
  margin-bottom: 8px;
  color: #050505;
  font-size: 12px;
  font-weight: 900;
}

.zc-form-field label span {
  color: #ff5b1a;
}

.zc-form-field input,
.zc-form-field select,
.zc-form-field textarea {
  width: 100%;
  min-height: 44px;
  border: 1px solid #d8d8d8;
  border-radius: 6px;
  padding: 11px 13px;
  background: #ffffff;
  color: #111111;
  font: inherit;
  font-size: 13px;
  font-weight: 600;
}

.zc-form-field textarea {
  min-height: 150px;
  resize: vertical;
}

.zc-website-form__features {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
  margin: 4px 0 18px;
}

.zc-website-form__features label {
  min-height: 44px;
  border: 1px solid #d8d8d8;
  border-radius: 6px;
  padding: 11px 12px;
  display: flex;
  align-items: center;
  gap: 8px;
  color: #111111;
  font-size: 13px;
  font-weight: 800;
}

.zc-website-form__footer {
  display: flex;
  align-items: center;
  gap: 18px;
  margin-top: 20px;
}

.zc-website-form__footer button {
  min-height: 50px;
  border: 0;
  border-radius: 6px;
  padding: 0 28px;
  background: #ff5b1a;
  color: #ffffff;
  font-size: 13px;
  font-weight: 950;
  text-transform: uppercase;
  cursor: pointer;
}

.zc-website-form__footer p {
  margin: 0;
  color: #686868;
  font-size: 12px;
  line-height: 1.45;
  font-weight: 600;
}

.zc-form-message {
  margin: 0 0 18px;
  border-radius: 8px;
  padding: 14px 16px;
  font-size: 13px;
  font-weight: 800;
}

.zc-form-message--success {
  background: #e9f9ef;
  color: #176a33;
}

.zc-form-message--error {
  background: #fff0ec;
  color: #a52d13;
}

@media screen and (max-width: 768px) {
  .zc-website-request__container {
    width: min(100% - 30px, 1120px);
  }

  .zc-website-form {
    padding: 22px;
  }

  .zc-website-form__grid,
  .zc-website-form__features {
    grid-template-columns: 1fr;
  }

  .zc-website-form__footer {
    align-items: stretch;
    flex-direction: column;
  }
}
</style>
