<?php
defined('ABSPATH') || exit;
?>

<section class="zc-customize-builder">
  <div class="zc-customize-builder__container">

    <div class="zc-customize-step zc-customize-step--form">
      <h2 class="zc-customize-step__title">
        Fill Out Your Design Form
      </h2>

      <?php
      $request_status = isset($_GET['request_status'])
        ? sanitize_text_field(wp_unslash($_GET['request_status']))
        : '';

      $selected_product_id = isset($_GET['zc_product_id']) ? absint($_GET['zc_product_id']) : 0;
      $selected_product_name = '';
      $selected_product_type = '';

      if ($selected_product_id && function_exists('wc_get_product')) {
        $selected_product = wc_get_product($selected_product_id);

        if ($selected_product) {
          $selected_product_name = $selected_product->get_name();
          $selected_product_key = sanitize_title($selected_product_name);

          if (strpos($selected_product_key, 'airpod') !== false) {
            $selected_product_type = 'airpods';
          } elseif (strpos($selected_product_key, 'cap') !== false) {
            $selected_product_type = 'cap';
          } elseif (strpos($selected_product_key, 'sweatshirt') !== false || strpos($selected_product_key, 'sweater') !== false) {
            $selected_product_type = 'sweatshirt';
          } elseif (strpos($selected_product_key, 'hoodie') !== false) {
            $selected_product_type = 'hoodie';
          } elseif (strpos($selected_product_key, 'mug') !== false) {
            $selected_product_type = 'mug';
          } elseif (strpos($selected_product_key, 't-shirt') !== false || strpos($selected_product_key, 'shirt') !== false) {
            $selected_product_type = 't-shirt';
          }
        }
      }
      ?>

      <?php if ($request_status === 'success') : ?>
        <div class="zc-form-message zc-form-message--success">
          Your design form was sent successfully. We’ll get back to you soon.
        </div>
      <?php elseif ($request_status === 'failed') : ?>
        <div class="zc-form-message zc-form-message--error">
          Message failed to send. Please try again.
        </div>
      <?php elseif ($request_status === 'cart_error') : ?>
        <div class="zc-form-message zc-form-message--error">
          Your design form was saved, but the selected product could not be added to cart. Return to the product and choose its options again.
        </div>
      <?php elseif ($request_status === 'missing_fields') : ?>
        <div class="zc-form-message zc-form-message--error">
          Please fill in all required fields.
        </div>
      <?php elseif ($request_status === 'invalid_email') : ?>
        <div class="zc-form-message zc-form-message--error">
          Please enter a valid email address.
        </div>
      <?php elseif ($request_status === 'invalid_product') : ?>
        <div class="zc-form-message zc-form-message--error">
          Invalid product selected. Please choose a valid product type.
        </div>
      <?php elseif ($request_status === 'invalid_logo_status') : ?>
        <div class="zc-form-message zc-form-message--error">
          Invalid design option selected. Please choose one of the design options.
        </div>
      <?php elseif ($request_status === 'too_many_requests') : ?>
        <div class="zc-form-message zc-form-message--error">
          Please wait a minute before sending another request.
        </div>
      <?php elseif ($request_status === 'file_too_large') : ?>
        <div class="zc-form-message zc-form-message--error">
          File is too large. Maximum file size is 20MB.
        </div>
      <?php elseif ($request_status === 'upload_error') : ?>
        <div class="zc-form-message zc-form-message--error">
          File upload failed. Please upload JPG, PNG, or PDF only.
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

      <form class="zc-design-request-form" method="post" action="<?php echo esc_url(home_url('/customize/')); ?>" enctype="multipart/form-data">
        <?php wp_nonce_field('zarvel_customize_form_action', 'zarvel_customize_nonce'); ?>

        <input type="hidden" name="zarvel_customize_form_submit" value="1">
        <input type="hidden" name="zc_product_id" value="<?php echo esc_attr($selected_product_id); ?>">

        <div style="position:absolute; left:-9999px; opacity:0; pointer-events:none;">
          <label for="website_url">Website</label>
          <input type="text" id="website_url" name="website_url" value="">
        </div>

        <div class="zc-design-request-form__grid">

          <div class="zc-design-request-form__left">

            <div class="zc-form-field">
              <label for="zc_full_name">Full Name <span>*</span></label>
              <input id="zc_full_name" type="text" name="full_name" placeholder="Enter your full name" required>
            </div>

            <div class="zc-form-field">
              <label for="zc_email">Email Address <span>*</span></label>
              <input id="zc_email" type="email" name="email" placeholder="Enter your email address" required>
            </div>

            <div class="zc-form-field">
              <label for="zc_phone">Phone Number Optional</label>
              <input id="zc_phone" type="text" name="phone" placeholder="Enter your phone number">
            </div>

            <?php if ($selected_product_name) : ?>
              <div class="zc-form-field">
                <label for="zc_selected_product">Selected Product</label>
                <input id="zc_selected_product" type="text" value="<?php echo esc_attr($selected_product_name); ?>" readonly>
              </div>
            <?php endif; ?>

            <div class="zc-form-field">
              <label for="zc_product_type">Product Type <span>*</span></label>
              <select id="zc_product_type" name="product_type" required>
                <option value="">Select product type</option>
                <option value="airpods" <?php selected($selected_product_type, 'airpods'); ?>>AirPods</option>
                <option value="cap" <?php selected($selected_product_type, 'cap'); ?>>Cap</option>
                <option value="t-shirt" <?php selected($selected_product_type, 't-shirt'); ?>>T-Shirt</option>
                <option value="hoodie" <?php selected($selected_product_type, 'hoodie'); ?>>Hoodie</option>
                <option value="sweatshirt" <?php selected($selected_product_type, 'sweatshirt'); ?>>Sweatshirt</option>
                <option value="mug" <?php selected($selected_product_type, 'mug'); ?>>Mug</option>
                <option value="tote-bag" <?php selected($selected_product_type, 'tote-bag'); ?>>Tote Bag</option>
                <option value="phone-case" <?php selected($selected_product_type, 'phone-case'); ?>>Phone Case</option>
                <option value="other" <?php selected($selected_product_type, 'other'); ?>>Other</option>
              </select>
            </div>

            <div class="zc-form-field">
              <label for="zc_print_location">Print Placement <span>*</span></label>
              <select id="zc_print_location" name="print_location" required>
                <option value="">Select print placement</option>
                <option value="front">Front</option>
                <option value="back">Back</option>
                <option value="front-and-back">Front and Back</option>
                <option value="left-chest">Left Chest</option>
                <option value="sleeve">Sleeve</option>
                <option value="other">Other / Not Sure</option>
              </select>
            </div>

            <div class="zc-form-field">
              <label>What are we working with? <span>*</span></label>

              <div class="zc-logo-option-grid">
                <label class="zc-logo-option">
                  <input type="radio" name="logo_status" value="has-logo" required>
                  <span>
                    <strong>I already have a logo/design</strong>
                    <small>I’ll upload the file for you to use.</small>
                  </span>
                </label>

                <label class="zc-logo-option">
                  <input type="radio" name="logo_status" value="needs-design" required>
                  <span>
                    <strong>I need you to create the design</strong>
                    <small>I’ll explain the idea and style I want.</small>
                  </span>
                </label>

                <label class="zc-logo-option">
                  <input type="radio" name="logo_status" value="has-idea-only" required>
                  <span>
                    <strong>I have an idea, but no final file</strong>
                    <small>I need help turning the idea into artwork.</small>
                  </span>
                </label>
              </div>
            </div>

          </div>

          <div class="zc-design-request-form__right">

            <div class="zc-form-field">
              <label for="zc_upload_file">Upload Logo / Artwork</label>

              <label class="zc-upload-box" for="zc_upload_file">
                <input id="zc_upload_file" type="file" name="upload_file" accept=".jpg,.jpeg,.png,.pdf">

                <span class="zc-upload-box__icon">
                  <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 16V4"></path>
                    <path d="M7 9l5-5 5 5"></path>
                    <path d="M5 20h14"></path>
                  </svg>
                </span>

                <strong>Drag and drop your file here</strong>
                <em>or</em>
                <span class="zc-upload-box__button">Choose File</span>
                <small id="zcUploadFileName">Supports: PNG, JPG, PDF. Max 20MB</small>
              </label>
            </div>

            <div class="zc-form-field">
              <label for="zc_design_text">Text To Include</label>
              <input id="zc_design_text" type="text" name="design_text" placeholder="Enter the exact words, names, or slogan for the design">
            </div>

            <div class="zc-form-field">
              <label for="zc_preferred_colors">Preferred Colors</label>
              <input id="zc_preferred_colors" type="text" name="preferred_colors" placeholder="Example: black shirt, white print, orange accent">
            </div>

            <div class="zc-form-field">
              <label for="zc_design_notes">Design Instructions <span>*</span></label>
              <textarea id="zc_design_notes" name="design_notes" rows="7" placeholder="Tell us about your design idea, placement, colors, text, style, or anything important..." required></textarea>
            </div>

          </div>

        </div>

        <div class="zc-design-request-form__footer">
          <button type="submit" class="zc-design-request-form__submit">
            Submit Design Form
          </button>

          <p>
            We respect your privacy. Your information will only be used to process your request.
          </p>
        </div>

      </form>
    </div>

  </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const fileInput = document.querySelector('#zc_upload_file');
  const fileName = document.querySelector('#zcUploadFileName');

  if (fileInput && fileName) {
    fileInput.addEventListener('change', function () {
      if (fileInput.files && fileInput.files.length > 0) {
        fileName.textContent = fileInput.files[0].name;
      } else {
        fileName.textContent = 'Supports: PNG, JPG, PDF. Max 20MB';
      }
    });
  }
});
</script>

<style>
.zc-customize-builder {
  padding: 42px 0 90px;
  background: #ffffff;
}

.zc-customize-builder__container {
  width: min(100% - 40px, 1120px);
  margin: 0 auto;
}

.zc-customize-step {
  margin-bottom: 30px;
}

.zc-customize-step__title {
  margin: 0 0 22px;
  color: #111111;
  font-size: 17px;
  line-height: 1;
  font-weight: 950;
  text-align: center;
  text-transform: uppercase;
  letter-spacing: -0.2px;
}

.zc-customize-step__title span {
  color: #111111;
}

.zc-customize-step--form {
  margin-bottom: 0;
}

.zc-form-message {
  max-width: 760px;
  margin: 0 auto 22px;
  padding: 14px 16px;
  border-radius: 10px;
  font-size: 14px;
  line-height: 1.4;
  font-weight: 800;
  text-align: center;
}

.zc-form-message--success {
  background: #ecfff1;
  color: #10752f;
  border: 1px solid #bdebc9;
}

.zc-form-message--error {
  background: #fff0ec;
  color: #b83212;
  border: 1px solid #ffcbbb;
}

.zc-design-request-form {
  padding: 30px;
  border: 1px solid #e9e9e9;
  border-radius: 14px;
  background: #ffffff;
}

.zc-design-request-form__grid {
  display: grid;
  grid-template-columns: 1fr 1.1fr;
  gap: 26px;
}

.zc-form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.zc-form-field {
  margin-bottom: 16px;
}

.zc-form-field label {
  display: block;
  margin-bottom: 8px;
  color: #111111;
  font-size: 11px;
  line-height: 1;
  font-weight: 950;
}

.zc-form-field label span {
  color: #ff5b1a;
}

.zc-form-field input,
.zc-form-field select,
.zc-form-field textarea {
  width: 100%;
  border: 1px solid #dddddd;
  border-radius: 7px;
  background: #ffffff;
  color: #111111;
  font-size: 13px;
  line-height: 1.2;
  font-weight: 650;
  outline: none;
  transition: 0.2s ease;
}

.zc-form-field input,
.zc-form-field select {
  height: 42px;
  padding: 0 12px;
}

.zc-form-field textarea {
  padding: 12px;
  resize: vertical;
}

.zc-form-field input:focus,
.zc-form-field select:focus,
.zc-form-field textarea:focus {
  border-color: #ff5b1a;
  box-shadow: 0 0 0 3px rgba(255, 91, 26, 0.08);
}

.zc-logo-option-grid {
  display: grid;
  gap: 10px;
}

.zc-logo-option {
  cursor: pointer;
}

.zc-logo-option input {
  position: absolute;
  opacity: 0;
  pointer-events: none;
}

.zc-logo-option span {
  min-height: 68px;
  padding: 14px 15px;
  border: 1px solid #dddddd;
  border-radius: 9px;
  background: #ffffff;
  display: flex;
  flex-direction: column;
  justify-content: center;
  gap: 5px;
  transition: 0.2s ease;
}

.zc-logo-option strong {
  color: #111111;
  font-size: 13px;
  line-height: 1.2;
  font-weight: 900;
}

.zc-logo-option small {
  color: #777777;
  font-size: 12px;
  line-height: 1.3;
  font-weight: 650;
}

.zc-logo-option input:checked + span,
.zc-logo-option span:hover {
  border-color: #ff5b1a;
  background: #fff6f1;
  box-shadow: 0 10px 24px rgba(255, 91, 26, 0.08);
}

.zc-upload-box {
  min-height: 178px;
  padding: 22px;
  border: 1px dashed #cccccc;
  border-radius: 10px;
  background: #fbfbfb;
  cursor: pointer;
  display: flex !important;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  transition: 0.2s ease;
}

.zc-upload-box:hover {
  border-color: #ff5b1a;
  background: #fff6f1;
}

.zc-upload-box input {
  display: none;
}

.zc-upload-box__icon {
  width: 36px;
  height: 36px;
  margin-bottom: 8px;
  color: #ff5b1a;
}

.zc-upload-box__icon svg {
  width: 100%;
  height: 100%;
  fill: none;
  stroke: currentColor;
  stroke-width: 1.8;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.zc-upload-box strong {
  color: #111111;
  font-size: 13px;
  line-height: 1.3;
  font-weight: 850;
}

.zc-upload-box em {
  margin: 5px 0;
  color: #777777;
  font-size: 12px;
  font-style: normal;
  font-weight: 700;
}

.zc-upload-box__button {
  min-height: 34px;
  padding: 0 16px;
  border-radius: 6px;
  background: #111111;
  color: #ffffff;
  font-size: 11px;
  font-weight: 950;
  text-transform: uppercase;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.zc-upload-box small {
  margin-top: 10px;
  color: #777777;
  font-size: 11px;
  line-height: 1.35;
  font-weight: 650;
}

.zc-design-request-form__footer {
  margin-top: 6px;
  text-align: center;
}

.zc-design-request-form__submit {
  width: min(100%, 360px);
  min-height: 44px;
  border: 0;
  border-radius: 6px;
  background: #ff5b1a;
  color: #ffffff;
  font-size: 12px;
  line-height: 1;
  font-weight: 950;
  text-transform: uppercase;
  cursor: pointer;
  transition: 0.2s ease;
}

.zc-design-request-form__submit:hover {
  background: #111111;
}

.zc-design-request-form__footer p {
  margin: 12px 0 0;
  color: #777777;
  font-size: 11px;
  line-height: 1.4;
  font-weight: 650;
}

@media screen and (max-width: 1024px) {
  .zc-design-request-form__grid {
    grid-template-columns: 1fr;
    gap: 4px;
  }
}

@media screen and (max-width: 768px) {
  .zc-customize-builder {
    padding: 34px 0 70px;
  }

  .zc-customize-builder__container {
    width: min(100% - 30px, 1120px);
  }

  .zc-design-request-form {
    padding: 20px 16px;
  }

  .zc-form-row {
    grid-template-columns: 1fr;
    gap: 0;
  }
}

@media screen and (max-width: 480px) {
  .zc-customize-step__title {
    font-size: 15px;
  }
}
</style>
