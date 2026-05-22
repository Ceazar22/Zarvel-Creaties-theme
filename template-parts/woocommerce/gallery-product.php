<?php
defined('ABSPATH') || exit;

global $product;

if (!is_a($product, 'WC_Product')) {
  $product = wc_get_product(get_the_ID());
}

if (!$product) {
  return;
}

wp_enqueue_script('wc-add-to-cart-variation');

if (!function_exists('zc_sp_normalize_key')) {
  function zc_sp_normalize_key($value) {
    $value = strtolower(remove_accents((string) $value));
    $value = str_replace(['-', '_'], ' ', $value);
    $value = preg_replace('/[^a-z0-9]+/', ' ', $value);
    $value = preg_replace('/\s+/', ' ', $value);
    return trim($value);
  }
}

if (!function_exists('zc_sp_is_color_attr')) {
  function zc_sp_is_color_attr($attribute_name) {
    $label = wc_attribute_label($attribute_name);
    $haystack = zc_sp_normalize_key($attribute_name . ' ' . $label);

    return strpos($haystack, 'color') !== false || strpos($haystack, 'colour') !== false;
  }
}

if (!function_exists('zc_sp_get_attribute_option_label')) {
  function zc_sp_get_attribute_option_label($attribute_name, $option) {
    if (taxonomy_exists($attribute_name)) {
      $term = get_term_by('slug', $option, $attribute_name);

      if ($term && !is_wp_error($term)) {
        return $term->name;
      }
    }

    return $option;
  }
}

if (!function_exists('zc_sp_get_image_object')) {
  function zc_sp_get_image_object($image_id, $fallback_alt = '') {
    if (!$image_id) {
      return null;
    }

    $large = wp_get_attachment_image_url($image_id, 'large');
    $thumb = wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail');
    $full  = wp_get_attachment_image_url($image_id, 'full');

    if (!$large) {
      return null;
    }

    $alt      = get_post_meta($image_id, '_wp_attachment_image_alt', true);
    $title    = get_the_title($image_id);
    $file_url = wp_get_attachment_url($image_id);
    $filename = $file_url ? wp_basename(parse_url($file_url, PHP_URL_PATH)) : '';

    $haystack = zc_sp_normalize_key($alt . ' ' . $title . ' ' . $filename);

    return [
      'id'       => (int) $image_id,
      'large'    => $large,
      'thumb'    => $thumb ?: $large,
      'full'     => $full ?: $large,
      'alt'      => $alt ?: $fallback_alt,
      'title'    => $title,
      'filename' => $filename,
      'haystack' => $haystack,
    ];
  }
}

if (!function_exists('zc_sp_add_unique_image')) {
  function zc_sp_add_unique_image(&$images, $image_obj) {
    if (!$image_obj || empty($image_obj['id'])) {
      return;
    }

    foreach ($images as $existing) {
      if (!empty($existing['id']) && (int) $existing['id'] === (int) $image_obj['id']) {
        return;
      }
    }

    $images[] = $image_obj;
  }
}

$main_image_id = $product->get_image_id();
$gallery_ids   = $product->get_gallery_image_ids();

$all_image_ids = array_filter(array_unique(array_merge([$main_image_id], $gallery_ids)));

$main_image_url = $main_image_id
  ? wp_get_attachment_image_url($main_image_id, 'large')
  : wc_placeholder_img_src('woocommerce_single');

$main_image_alt = $main_image_id
  ? get_post_meta($main_image_id, '_wp_attachment_image_alt', true)
  : $product->get_name();

$all_image_objects = [];

foreach ($all_image_ids as $image_id) {
  $image_obj = zc_sp_get_image_object($image_id, $product->get_name());

  if ($image_obj) {
    $all_image_objects[] = $image_obj;
  }
}

$variation_attributes = $product->is_type('variable') ? $product->get_variation_attributes() : [];
$color_attribute_name = '';
$color_options_raw    = [];

foreach ($variation_attributes as $attribute_name => $options) {
  if (zc_sp_is_color_attr($attribute_name)) {
    $color_attribute_name = $attribute_name;
    $color_options_raw    = $options;
    break;
  }
}

$color_options = [];

if ($color_attribute_name && !empty($color_options_raw)) {
  foreach ($color_options_raw as $option) {
    $label = zc_sp_get_attribute_option_label($color_attribute_name, $option);

    $value_key = zc_sp_normalize_key($option);
    $label_key = zc_sp_normalize_key($label);

    $keys = array_filter(array_unique([$value_key, $label_key]));

    $color_options[] = [
      'value'       => $option,
      'label'       => $label,
      'keys'        => $keys,
      'primary_key' => $value_key ?: $label_key,
    ];
  }
}

$zc_color_gallery_map = [];

foreach ($color_options as $color_option) {
  $primary_key = $color_option['primary_key'];

  if (!$primary_key) {
    continue;
  }

  $zc_color_gallery_map[$primary_key] = [];

  foreach ($all_image_objects as $image_obj) {
    foreach ($color_option['keys'] as $key) {
      $compact_haystack = str_replace(' ', '', $image_obj['haystack']);
      $compact_key      = str_replace(' ', '', $key);

      if (
        $key &&
        (
          strpos($image_obj['haystack'], $key) !== false ||
          strpos($compact_haystack, $compact_key) !== false
        )
      ) {
        zc_sp_add_unique_image($zc_color_gallery_map[$primary_key], $image_obj);
        break;
      }
    }
  }
}

if ($product->is_type('variable') && $color_attribute_name) {
  $variation_ids = $product->get_children();

  foreach ($variation_ids as $variation_id) {
    $variation = wc_get_product($variation_id);

    if (!$variation) {
      continue;
    }

    $variation_attrs = $variation->get_variation_attributes();
    $variation_color = '';

    foreach ($variation_attrs as $attr_key => $attr_value) {
      $clean_attr_key = str_replace('attribute_', '', $attr_key);

      if ($clean_attr_key === $color_attribute_name || zc_sp_is_color_attr($clean_attr_key)) {
        $variation_color = $attr_value;
        break;
      }
    }

    if (!$variation_color) {
      continue;
    }

    $variation_color_key = zc_sp_normalize_key($variation_color);
    $variation_image_id  = $variation->get_image_id();

    if (!$variation_image_id) {
      continue;
    }

    $variation_image_obj = zc_sp_get_image_object($variation_image_id, $product->get_name());

    foreach ($color_options as $color_option) {
      $primary_key = $color_option['primary_key'];

      if (!$primary_key) {
        continue;
      }

      if (in_array($variation_color_key, $color_option['keys'], true)) {
        zc_sp_add_unique_image($zc_color_gallery_map[$primary_key], $variation_image_obj);
      }
    }
  }
}

foreach ($zc_color_gallery_map as $key => $images) {
  $zc_color_gallery_map[$key] = array_slice($images, 0, 2);
}

foreach ($color_options as $color_option) {
  $primary_key = $color_option['primary_key'];

  if (!$primary_key || !isset($zc_color_gallery_map[$primary_key])) {
    continue;
  }

  foreach ($color_option['keys'] as $alias_key) {
    if ($alias_key) {
      $zc_color_gallery_map[$alias_key] = $zc_color_gallery_map[$primary_key];
    }
  }
}

$default_attributes = $product->get_default_attributes();
$initial_color_key  = '';

if ($color_attribute_name && !empty($default_attributes[$color_attribute_name])) {
  $initial_color_key = zc_sp_normalize_key($default_attributes[$color_attribute_name]);
}

$initial_gallery_images = [];

if ($initial_color_key && !empty($zc_color_gallery_map[$initial_color_key])) {
  $initial_gallery_images = $zc_color_gallery_map[$initial_color_key];
}

if (empty($initial_gallery_images)) {
  $fallback_image_obj = zc_sp_get_image_object($main_image_id, $product->get_name());

  if ($fallback_image_obj) {
    $initial_gallery_images = [$fallback_image_obj];
  }
}

if (empty($initial_gallery_images) && $main_image_url) {
  $initial_gallery_images = [[
    'large' => $main_image_url,
    'thumb' => $main_image_url,
    'full'  => $main_image_url,
    'alt'   => $main_image_alt ?: $product->get_name(),
  ]];
}

$initial_main_image = !empty($initial_gallery_images[0]['large'])
  ? $initial_gallery_images[0]['large']
  : $main_image_url;

$initial_main_alt = !empty($initial_gallery_images[0]['alt'])
  ? $initial_gallery_images[0]['alt']
  : ($main_image_alt ?: $product->get_name());

$average_rating = $product->get_average_rating();
$review_count   = $product->get_review_count();

$button_text_filter = function () {
  return 'FILL OUT DESIGN FORM';
};

$zc_design_form_url = home_url('/customize/');
$zc_modal_product_key = sanitize_title($product->get_name());
$zc_modal_product_type = 'other';

if (strpos($zc_modal_product_key, 'airpod') !== false) {
  $zc_modal_product_type = 'airpods';
} elseif (strpos($zc_modal_product_key, 'cap') !== false) {
  $zc_modal_product_type = 'cap';
} elseif (strpos($zc_modal_product_key, 'sweatshirt') !== false || strpos($zc_modal_product_key, 'sweater') !== false) {
  $zc_modal_product_type = 'sweatshirt';
} elseif (strpos($zc_modal_product_key, 'hoodie') !== false) {
  $zc_modal_product_type = 'hoodie';
} elseif (strpos($zc_modal_product_key, 'mug') !== false) {
  $zc_modal_product_type = 'mug';
} elseif (strpos($zc_modal_product_key, 't-shirt') !== false || strpos($zc_modal_product_key, 'shirt') !== false) {
  $zc_modal_product_type = 't-shirt';
}
?>

<section class="zc-product-section">
  <div class="zc-product-container">

    <div class="zc-product-breadcrumb">
      <?php
      woocommerce_breadcrumb([
        'delimiter'   => '<span>/</span>',
        'wrap_before' => '<nav class="woocommerce-breadcrumb">',
        'wrap_after'  => '</nav>',
        'before'      => '',
        'after'       => '',
        'home'        => 'Home',
      ]);
      ?>
    </div>

    <div class="zc-product-layout">

      <div class="zc-product-gallery">

        <div class="zc-product-thumbs" id="zcProductThumbs">
          <?php foreach ($initial_gallery_images as $index => $image) : ?>
            <button
              class="zc-product-thumb <?php echo $index === 0 ? 'is-active' : ''; ?>"
              type="button"
              data-large="<?php echo esc_url($image['large']); ?>"
              data-alt="<?php echo esc_attr($image['alt']); ?>"
            >
              <img
                src="<?php echo esc_url($image['thumb']); ?>"
                alt="<?php echo esc_attr($image['alt']); ?>"
              >
            </button>
          <?php endforeach; ?>
        </div>

        <div class="zc-product-main-image-wrap">
          <button class="zc-product-wishlist" type="button" aria-label="Add to wishlist">
            <svg viewBox="0 0 24 24">
              <path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"/>
            </svg>
          </button>

          <img
            id="zcMainProductImage"
            class="zc-product-main-image"
            src="<?php echo esc_url($initial_main_image); ?>"
            alt="<?php echo esc_attr($initial_main_alt); ?>"
          >

          <button class="zc-product-zoom" type="button" aria-label="Zoom image">
            <svg viewBox="0 0 24 24">
              <path d="M21 21l-4.35-4.35"></path>
              <circle cx="11" cy="11" r="7"></circle>
              <path d="M11 8v6"></path>
              <path d="M8 11h6"></path>
            </svg>
          </button>
        </div>

      </div>

      <div class="zc-product-summary">

        <h1 class="zc-product-title">
          <?php echo esc_html($product->get_name()); ?>
        </h1>

        <div class="zc-product-rating-row">
          <div class="zc-product-stars">
            <?php
            if ($average_rating > 0) {
              echo wp_kses_post(wc_get_rating_html($average_rating, $review_count));
            } else {
              echo '<span class="zc-empty-stars">★★★★★</span>';
            }
            ?>
          </div>

          <a href="#reviews" class="zc-product-review-link">
            (<?php echo esc_html($review_count); ?> review<?php echo $review_count == 1 ? '' : 's'; ?>)
          </a>
        </div>

        <div class="zc-product-price">
          <?php echo wp_kses_post($product->get_price_html()); ?>
        </div>

        <div class="zc-product-short-desc">
          <?php
          if ($product->get_short_description()) {
            echo wp_kses_post(wpautop($product->get_short_description()));
          } else {
            echo '<p>Send us your logo or idea and we’ll handle the design for you. You’ll get a digital proof before we print.</p>';
          }
          ?>
        </div>

        <div class="zc-product-benefits">
          <div class="zc-product-benefit">
            <svg viewBox="0 0 24 24">
              <path d="M12 2l7 4v6c0 5-3 9-7 10-4-1-7-5-7-10V6l7-4z"></path>
              <path d="M9 12l2 2 4-5"></path>
            </svg>
            <span>Premium<br>Print Quality</span>
          </div>

          <div class="zc-product-benefit">
            <svg viewBox="0 0 24 24">
              <rect x="4" y="5" width="16" height="14" rx="2"></rect>
              <path d="M8 14l3-3 3 3 2-2 4 4"></path>
              <circle cx="9" cy="9" r="1"></circle>
            </svg>
            <span>Free Mockup<br>By Email</span>
          </div>

          <div class="zc-product-benefit">
            <svg viewBox="0 0 24 24">
              <path d="M3 7h11v9H3z"></path>
              <path d="M14 10h4l3 3v3h-7z"></path>
              <circle cx="7" cy="18" r="2"></circle>
              <circle cx="18" cy="18" r="2"></circle>
            </svg>
            <span>Fast<br>Production</span>
          </div>

          <div class="zc-product-benefit">
            <svg viewBox="0 0 24 24">
              <path d="M12 3l8 4v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7l8-4z"></path>
              <path d="M8 12h8"></path>
            </svg>
            <span>No Minimum<br>Order</span>
          </div>
        </div>

        <div class="zc-product-form-area" data-zc-product-form-area>
          <?php
          add_filter('woocommerce_product_single_add_to_cart_text', $button_text_filter);
          woocommerce_template_single_add_to_cart();
          remove_filter('woocommerce_product_single_add_to_cart_text', $button_text_filter);
          ?>

          <button type="button" class="zc-shop-blank-btn" data-zc-shop-blank-btn>
            ADD TO CART
          </button>
        </div>

        <div class="zc-product-trust-row">
          <span>100% Satisfaction Guarantee</span>
          <span>Secure Checkout</span>
          <span>Trusted by 10,000+ Customers</span>
        </div>

      </div>

    </div>

  </div>
</section>

<?php
$zc_portal_design_email = function_exists('zarvel_portal_current_customer_email')
  ? zarvel_portal_current_customer_email()
  : '';
$zc_portal_design_auth_enabled = function_exists('zarvel_portal_current_customer_email');
?>
<div class="zc-design-modal" data-zc-design-modal aria-hidden="true">
  <button class="zc-design-modal__overlay" type="button" data-zc-design-modal-close aria-label="Close design form"></button>

  <div class="zc-design-modal__panel" role="dialog" aria-modal="true" aria-labelledby="zcDesignModalTitle">
    <div class="zc-design-modal__header">
      <div>
        <p>Design Request</p>
        <h2 id="zcDesignModalTitle">Tell Us Your Design</h2>
      </div>

      <button class="zc-design-modal__close" type="button" data-zc-design-modal-close aria-label="Close design form">
        <span></span>
        <span></span>
      </button>
    </div>

    <form class="zc-design-modal__form" method="post" action="<?php echo esc_url($zc_design_form_url); ?>" enctype="multipart/form-data">
      <?php wp_nonce_field('zarvel_customize_form_action', 'zarvel_customize_nonce'); ?>

      <input type="hidden" name="zarvel_customize_form_submit" value="1">
      <input type="hidden" name="zc_product_form_submission" value="1">
      <input type="hidden" name="zc_product_id" value="<?php echo esc_attr($product->get_id()); ?>">
      <input type="hidden" name="zc_variation_id" data-zc-design-variation-id value="">
      <input type="hidden" name="selected_options" data-zc-design-selected-options value="">

      <div class="zc-design-modal__selected">
        <span>Product</span>
        <strong><?php echo esc_html($product->get_name()); ?></strong>
        <em data-zc-design-selected-summary></em>
      </div>

      <div class="zc-design-modal__grid">
        <label>
          <span>Full Name *</span>
          <input type="text" name="full_name" placeholder="Enter your full name" required>
        </label>

        <label>
          <span>Email Address *</span>
          <input type="email" name="email" placeholder="name@gmail.com" value="<?php echo esc_attr($zc_portal_design_email); ?>" <?php echo $zc_portal_design_email ? 'readonly' : ''; ?> required>
        </label>

        <label>
          <span>Phone Number</span>
          <input type="text" name="phone" placeholder="Enter your phone number">
        </label>

        <label>
          <span>Print Placement *</span>
          <select name="print_location" data-zc-placement-select required>
            <option value="">Select print placement</option>
            <option value="front" data-extra-cost="0" data-extra-label="No extra placement cost">Front</option>
            <option value="back" data-extra-cost="5.95" data-extra-label="Estimated extra Printful placement cost">Back (+$5.95)</option>
            <option value="front-and-back" data-extra-cost="5.95" data-extra-label="Estimated extra Printful placement cost">Front and Back (+$5.95)</option>
            <option value="left-chest" data-extra-cost="0" data-extra-label="No extra placement cost">Left Chest</option>
            <option value="sleeve" data-extra-cost="2.95" data-extra-label="Estimated extra Printful placement cost">Sleeve (+$2.95)</option>
            <option value="other" data-extra-cost="quote" data-extra-label="Quote required for custom placement">Other / Not Sure</option>
          </select>
        </label>

        <label>
          <span>Upload Logo / Artwork</span>
          <input type="file" name="upload_file" accept=".jpg,.jpeg,.png,.pdf">
        </label>
      </div>

      <input type="hidden" name="product_type" value="<?php echo esc_attr($zc_modal_product_type); ?>">
      <input type="hidden" name="design_text" value="">
      <input type="hidden" name="preferred_colors" value="">
      <input type="hidden" name="print_location_extra_cost" data-zc-placement-extra-cost value="0">
      <input type="hidden" name="print_location_extra_label" data-zc-placement-extra-label value="No extra placement cost">
      <input type="hidden" name="logo_status" value="has-logo">

      <div class="zc-design-modal__placement-cost" data-zc-placement-cost-note>
        Front or left chest placement has no extra placement cost. Back and sleeve placements may increase the Printful production cost.
      </div>

      <label class="zc-design-modal__notes">
        <span>Design Instructions *</span>
        <textarea name="design_notes" rows="5" placeholder="Tell us about your design idea, placement, style, and anything important..." required></textarea>
      </label>

      <?php if ($zc_portal_design_auth_enabled) : ?>
        <div class="zc-design-modal__auth <?php echo $zc_portal_design_email ? 'is-verified' : ''; ?>" data-zc-design-auth>
          <?php if ($zc_portal_design_email) : ?>
            <strong data-zc-design-auth-message>Logged in as <?php echo esc_html($zc_portal_design_email); ?>.</strong>
          <?php else : ?>
            <strong data-zc-design-auth-message>Confirm your Gmail to send this design request.</strong>
          <?php endif; ?>
          <label data-zc-design-code-wrap hidden>
            <span>Gmail Code</span>
            <input type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" data-zc-design-code placeholder="6-digit code">
          </label>
        </div>
      <?php endif; ?>

      <button type="submit" class="zc-design-modal__submit">Submit Design Form</button>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const mainImage = document.querySelector('#zcMainProductImage');
  const thumbsWrap = document.querySelector('#zcProductThumbs');

  const variationGalleryMap = <?php echo wp_json_encode($zc_color_gallery_map); ?>;
  const defaultGallery = <?php echo wp_json_encode($initial_gallery_images); ?>;
  const zcDesignFormUrl = <?php echo wp_json_encode($zc_design_form_url); ?>;
  const zcCurrentProductId = <?php echo wp_json_encode($product->get_id()); ?>;
  const zcPortalDesignAuth = <?php echo wp_json_encode(array(
    'enabled' => $zc_portal_design_auth_enabled,
    'loggedEmail' => $zc_portal_design_email,
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('zarvel_portal_design_modal'),
  )); ?>;
  let zcVariationGalleryWasRendered = false;

  function normalizeColorName(colorName) {
    return String(colorName || '')
      .toLowerCase()
      .trim()
      .replace(/[-_]+/g, ' ')
      .replace(/[^a-z0-9]+/g, ' ')
      .replace(/\s+/g, ' ')
      .trim();
  }

  function updateMainImage(src, alt) {
    if (!mainImage || !src) return;

    mainImage.classList.add('is-changing');

    setTimeout(function () {
      mainImage.src = src;
      mainImage.alt = alt || '';
      mainImage.classList.remove('is-changing');
    }, 120);
  }

  function bindThumbClicks() {
    const thumbs = document.querySelectorAll('.zc-product-thumb[data-large]');

    thumbs.forEach(function (thumb) {
      thumb.addEventListener('click', function () {
        const large = thumb.getAttribute('data-large');
        const alt = thumb.getAttribute('data-alt') || '';

        updateMainImage(large, alt);

        thumbs.forEach(function (item) {
          item.classList.remove('is-active');
        });

        thumb.classList.add('is-active');
      });
    });
  }

  function renderGallery(images) {
    if (!thumbsWrap || !Array.isArray(images) || !images.length) return false;

    thumbsWrap.innerHTML = '';

    images.forEach(function (image, index) {
      const button = document.createElement('button');
      button.className = 'zc-product-thumb' + (index === 0 ? ' is-active' : '');
      button.type = 'button';
      button.setAttribute('data-large', image.large || image.full || '');
      button.setAttribute('data-alt', image.alt || '');

      const img = document.createElement('img');
      img.src = image.thumb || image.large || image.full || '';
      img.alt = image.alt || '';

      button.appendChild(img);
      thumbsWrap.appendChild(button);
    });

    bindThumbClicks();

    const firstImage = images[0];

    if (firstImage) {
      updateMainImage(firstImage.large || firstImage.full, firstImage.alt || '');
    }

    return true;
  }

  function getSelectedOptionText(select) {
    if (!select) return '';

    const selectedOption = select.options[select.selectedIndex];

    return selectedOption ? selectedOption.textContent : '';
  }

  function findColorSelect() {
    const selects = document.querySelectorAll('.variations select');

    for (const select of selects) {
      const row = select.closest('tr');
      const labelText = row ? (row.querySelector('label')?.textContent || '') : '';
      const haystack = normalizeColorName(select.name + ' ' + select.id + ' ' + labelText);

      if (haystack.includes('color') || haystack.includes('colour')) {
        return select;
      }
    }

    return null;
  }

  function setGalleryByColor(value, label) {
    const key1 = normalizeColorName(value);
    const key2 = normalizeColorName(label);

    const images = variationGalleryMap[key1] || variationGalleryMap[key2];

    if (images && images.length) {
      return renderGallery(images);
    }

    return false;
  }

  function getVariationImageObject(variation) {
    if (!variation || !variation.image) return null;

    const imageSrc = variation.image.full_src || variation.image.src || '';

    if (!imageSrc) return null;

    return {
      large: imageSrc,
      thumb: variation.image.thumb_src || variation.image.src || imageSrc,
      full: imageSrc,
      alt: variation.image.alt || ''
    };
  }

  function renderSelectedVariationGallery(variation) {
    const variationImage = getVariationImageObject(variation);

    if (!variationImage) return false;

    const galleryImages = [variationImage];
    const seen = new Set([
      variationImage.large,
      variationImage.full,
      variationImage.thumb
    ].filter(Boolean));

    const colorSelect = findColorSelect();

    if (colorSelect && colorSelect.value) {
      const key1 = normalizeColorName(colorSelect.value);
      const key2 = normalizeColorName(getSelectedOptionText(colorSelect));
      const colorImages = variationGalleryMap[key1] || variationGalleryMap[key2] || [];

      colorImages.forEach(function (image) {
        const imageKey = image.large || image.full || image.thumb || '';

        if (!imageKey || seen.has(imageKey)) return;

        galleryImages.push(image);
        seen.add(imageKey);
      });
    }

    const rendered = renderGallery(galleryImages);

    if (rendered) {
      zcVariationGalleryWasRendered = true;
    }

    return rendered;
  }

  function getCurrentVariationFromForm(form) {
    if (!form || !window.jQuery) return null;

    const variations = jQuery(form).data('product_variations') || [];

    if (!Array.isArray(variations) || !variations.length) return null;

    const selectedAttributes = {};

    form.querySelectorAll('.variations select').forEach(function (select) {
      selectedAttributes[select.name] = select.value || '';
    });

    return variations.find(function (variation) {
      if (!variation || !variation.attributes) return false;

      return Object.keys(variation.attributes).every(function (attributeName) {
        const expectedValue = variation.attributes[attributeName] || '';
        const selectedValue = selectedAttributes[attributeName] || '';

        return expectedValue === '' || expectedValue === selectedValue;
      });
    }) || null;
  }

  function renderCurrentSelectedVariation(form) {
    const variation = getCurrentVariationFromForm(form);

    return renderSelectedVariationGallery(variation);
  }

  bindThumbClicks();

  const variationSelects = document.querySelectorAll('.variations select');

  variationSelects.forEach(function (select) {
    if (select.dataset.zcButtonsReady === 'true') return;

    const wrapper = document.createElement('div');
    wrapper.className = 'zc-variation-buttons';

    const labelText = select.closest('tr')?.querySelector('label')?.textContent || '';

    const isColor =
      normalizeColorName(labelText).includes('color') ||
      normalizeColorName(labelText).includes('colour') ||
      normalizeColorName(select.name).includes('color') ||
      normalizeColorName(select.id).includes('color');

    Array.from(select.options).forEach(function (option) {
      if (!option.value) return;

      const button = document.createElement('button');
      button.type = 'button';
      button.className = isColor ? 'zc-variation-btn zc-variation-btn--color' : 'zc-variation-btn';
      button.dataset.value = option.value;

      if (isColor) {
        const dot = document.createElement('span');
        dot.className = 'zc-color-swatch';
        dot.style.background = getColorValue(option.textContent || option.value);
        button.appendChild(dot);
        button.setAttribute('aria-label', option.textContent || option.value);
        button.setAttribute('title', option.textContent || option.value);
      } else {
        button.textContent = option.textContent;
      }

      button.addEventListener('click', function () {
        select.value = option.value;
        select.dispatchEvent(new Event('change', { bubbles: true }));

        wrapper.querySelectorAll('.zc-variation-btn').forEach(function (btn) {
          btn.classList.remove('is-active');
        });

        button.classList.add('is-active');

        if (window.jQuery) {
          const form = select.closest('form.variations_form');

          if (form) {
            window.setTimeout(function () {
              renderCurrentSelectedVariation(form);
            }, 20);
          }
        }
      });

      wrapper.appendChild(button);
    });

    select.insertAdjacentElement('afterend', wrapper);
    select.style.display = 'none';
    select.dataset.zcButtonsReady = 'true';

    function syncActiveButton() {
      const currentValue = select.value;

      wrapper.querySelectorAll('.zc-variation-btn').forEach(function (button) {
        button.classList.toggle('is-active', button.dataset.value === currentValue);
      });
    }

    select.addEventListener('change', syncActiveButton);
    syncActiveButton();
  });

  if (window.jQuery) {
    jQuery(function ($) {
      $('.variations_form').on('found_variation show_variation', function (event, variation) {
        if (renderSelectedVariationGallery(variation)) return;

        const colorSelect = findColorSelect();

        if (colorSelect && colorSelect.value) {
          setGalleryByColor(colorSelect.value, getSelectedOptionText(colorSelect));
        }
      });

      $('.variations_form').on('reset_data', function () {
        zcVariationGalleryWasRendered = false;
        renderGallery(defaultGallery);
      });

      setTimeout(function () {
        $('.variations_form').each(function () {
          renderCurrentSelectedVariation(this);
        });

        if (zcVariationGalleryWasRendered) return;

        const colorSelect = findColorSelect();

        if (colorSelect && colorSelect.value) {
          setGalleryByColor(colorSelect.value, getSelectedOptionText(colorSelect));
        } else {
          renderGallery(defaultGallery);
        }

        $('.variations_form').trigger('check_variations');
      }, 300);
    });
  }

  initZcQuantityAndButtons();
  initZcSendDesignRequestToForm();

  function initZcSendDesignRequestToForm() {
    const formArea = document.querySelector('[data-zc-product-form-area]');
    const designModal = document.querySelector('[data-zc-design-modal]');
    const designRequestForm = designModal ? designModal.querySelector('.zc-design-modal__form') : null;
    const designModalSummary = document.querySelector('[data-zc-design-selected-summary]');
    const designModalOptionsInput = document.querySelector('[data-zc-design-selected-options]');
    const designModalVariationInput = document.querySelector('[data-zc-design-variation-id]');
    const designAuth = document.querySelector('[data-zc-design-auth]');
    const designAuthMessage = document.querySelector('[data-zc-design-auth-message]');
    const designAuthCodeWrap = document.querySelector('[data-zc-design-code-wrap]');
    const designAuthCodeInput = document.querySelector('[data-zc-design-code]');
    const designModalCloseButtons = document.querySelectorAll('[data-zc-design-modal-close]');
    const placementSelect = document.querySelector('[data-zc-placement-select]');
    const placementCostNote = document.querySelector('[data-zc-placement-cost-note]');
    const placementExtraCostInput = document.querySelector('[data-zc-placement-extra-cost]');
    const placementExtraLabelInput = document.querySelector('[data-zc-placement-extra-label]');
    let designAuthCodeEmail = '';
    let designAuthCodeSent = false;
    let designAuthVerified = !!(zcPortalDesignAuth && zcPortalDesignAuth.loggedEmail);

    if (!formArea) return;

    function keepDesignRequestButtonsClickable() {
      formArea.querySelectorAll('.single_add_to_cart_button').forEach(function (button) {
        button.disabled = false;
        button.removeAttribute('disabled');
        button.setAttribute('aria-disabled', 'false');
      });
    }

    function getProductIdFromForm(form) {
      const productIdInput = form.querySelector('input[name="product_id"]');
      const addToCartInput = form.querySelector('input[name="add-to-cart"], button[name="add-to-cart"]');

      return (productIdInput && productIdInput.value) ||
        (addToCartInput && addToCartInput.value) ||
        zcCurrentProductId ||
        '';
    }

    function getVariationIdFromForm(form) {
      const variationIdInput = form.querySelector('input[name="variation_id"]');
      return variationIdInput && variationIdInput.value ? variationIdInput.value : '';
    }

    function getQuantityFromForm(form) {
      const quantityInput = form.querySelector('input.qty[name="quantity"], input.qty');
      return quantityInput && quantityInput.value ? quantityInput.value : '1';
    }

    function getMissingVariationLabels(form) {
      const missing = [];

      form.querySelectorAll('.variations select').forEach(function (select) {
        if (select.value) return;

        const row = select.closest('tr');
        const label = row ? row.querySelector('label') : null;
        const labelText = label ? label.textContent.trim() : 'option';

        missing.push(labelText.replace(':', ''));
      });

      return missing;
    }

    function buildDesignFormUrl(form) {
      const productId = getProductIdFromForm(form);
      const variationId = getVariationIdFromForm(form);
      const quantity = getQuantityFromForm(form);
      const url = new URL(zcDesignFormUrl, window.location.origin);

      url.searchParams.set('zc_product_id', productId);
      url.searchParams.set('quantity', quantity);

      if (variationId && variationId !== '0') {
        url.searchParams.set('zc_variation_id', variationId);
      }

      form.querySelectorAll('.variations select').forEach(function (select) {
        if (!select.name || !select.value) return;
        url.searchParams.set(select.name, select.value);
      });

      return url.toString();
    }

    function getSelectedOptionsText(form) {
      const selected = [];

      form.querySelectorAll('.variations select').forEach(function (select) {
        if (!select.value) return;

        const row = select.closest('tr');
        const label = row ? row.querySelector('label') : null;
        const labelText = label ? label.textContent.trim().replace(':', '') : select.name;
        const optionText = getSelectedOptionText(select) || select.value;

        selected.push(labelText + ': ' + optionText);
      });

      const quantity = getQuantityFromForm(form);

      if (quantity) {
        selected.push('Quantity: ' + quantity);
      }

      return selected.join(' | ');
    }

    function openDesignModal(form) {
      if (!form) return;

      const isVariableForm = form.classList.contains('variations_form');
      const missingVariationLabels = getMissingVariationLabels(form);
      const variationId = getVariationIdFromForm(form);

      if (isVariableForm && (missingVariationLabels.length || !variationId || variationId === '0')) {
        if (window.jQuery) {
          jQuery(form).trigger('check_variations');
        }

        alert('Please select ' + (missingVariationLabels.join(', ') || 'all product options') + ' first.');
        return;
      }

      const selectedOptionsText = getSelectedOptionsText(form);

      if (designModalOptionsInput) {
        designModalOptionsInput.value = selectedOptionsText;
      }

      if (designModalVariationInput) {
        designModalVariationInput.value = variationId && variationId !== '0' ? variationId : '';
      }

      if (designModalSummary) {
        designModalSummary.textContent = selectedOptionsText;
      }

      if (!designModal) return;

      designModal.classList.add('is-open');
      designModal.setAttribute('aria-hidden', 'false');
      document.documentElement.classList.add('zc-design-modal-lock');

      const firstInput = designModal.querySelector('input[name="full_name"]');

      if (firstInput) {
        window.setTimeout(function () {
          firstInput.focus();
        }, 80);
      }
    }

    function closeDesignModal() {
      if (!designModal) return;

      designModal.classList.remove('is-open');
      designModal.setAttribute('aria-hidden', 'true');
      document.documentElement.classList.remove('zc-design-modal-lock');
    }

    function updatePlacementCostNote() {
      if (!placementSelect) return;

      const selectedOption = placementSelect.options[placementSelect.selectedIndex];
      const extraCost = selectedOption ? selectedOption.dataset.extraCost || '0' : '0';
      const extraLabel = selectedOption ? selectedOption.dataset.extraLabel || 'No extra placement cost' : 'No extra placement cost';
      let note = 'Front or left chest placement has no extra placement cost. Back and sleeve placements may increase the Printful production cost.';

      if (extraCost === 'quote') {
        note = 'Custom placement needs a quote before production.';
      } else if (parseFloat(extraCost) > 0) {
        note = extraLabel + ': +$' + parseFloat(extraCost).toFixed(2);
      } else if (placementSelect.value) {
        note = extraLabel + '.';
      }

      if (placementCostNote) {
        placementCostNote.textContent = note;
      }

      if (placementExtraCostInput) {
        placementExtraCostInput.value = extraCost;
      }

      if (placementExtraLabelInput) {
        placementExtraLabelInput.value = extraLabel;
      }
    }

    function setDesignAuthMessage(message, state) {
      if (!designAuthMessage) return;

      designAuthMessage.textContent = message;

      if (designAuth) {
        designAuth.classList.remove('is-error', 'is-pending', 'is-verified');
        if (state) designAuth.classList.add(state);
      }
    }

    function setDesignSubmitBusy(button, busy) {
      if (!button) return;

      button.disabled = busy;
      button.setAttribute('aria-busy', busy ? 'true' : 'false');
    }

    function callDesignAuth(action, email, code) {
      const body = new FormData();
      body.append('action', action);
      body.append('nonce', zcPortalDesignAuth.nonce);
      body.append('email', email);

      if (code) {
        body.append('code', code);
      }

      return fetch(zcPortalDesignAuth.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: body
      }).then(function (response) {
        return response.json().then(function (data) {
          if (!response.ok || !data.success) {
            throw new Error(data && data.data && data.data.message ? data.data.message : 'Please try again.');
          }

          return data.data || {};
        });
      });
    }

    if (designRequestForm && zcPortalDesignAuth && zcPortalDesignAuth.enabled) {
      const emailInput = designRequestForm.querySelector('input[name="email"]');
      const submitButton = designRequestForm.querySelector('.zc-design-modal__submit');

      if (emailInput && !designAuthVerified) {
        emailInput.addEventListener('input', function () {
          if (designAuthCodeEmail === emailInput.value.trim().toLowerCase()) return;

          designAuthCodeSent = false;
          designAuthCodeEmail = '';

          if (designAuthCodeWrap) designAuthCodeWrap.hidden = true;
          if (designAuthCodeInput) designAuthCodeInput.value = '';
          if (submitButton) submitButton.textContent = 'Submit Design Form';
        });
      }

      designRequestForm.addEventListener('submit', function (event) {
        if (designAuthVerified) {
          return;
        }

        event.preventDefault();

        if (!designRequestForm.reportValidity()) {
          return;
        }

        const email = emailInput ? emailInput.value.trim().toLowerCase() : '';

        setDesignSubmitBusy(submitButton, true);

        if (!designAuthCodeSent || designAuthCodeEmail !== email) {
          callDesignAuth('zarvel_portal_modal_send_code', email)
            .then(function (data) {
              designAuthCodeSent = true;
              designAuthCodeEmail = email;
              if (designAuthCodeWrap) designAuthCodeWrap.hidden = false;
              if (submitButton) submitButton.textContent = 'Verify Code And Submit';
              setDesignAuthMessage(data.message || 'Code sent. Check your Gmail.', 'is-pending');
              if (designAuthCodeInput) designAuthCodeInput.focus();
            })
            .catch(function (error) {
              setDesignAuthMessage(error.message, 'is-error');
            })
            .finally(function () {
              setDesignSubmitBusy(submitButton, false);
            });
          return;
        }

        const code = designAuthCodeInput ? designAuthCodeInput.value.trim() : '';

        if (!/^[0-9]{6}$/.test(code)) {
          setDesignAuthMessage('Enter the 6-digit Gmail code.', 'is-error');
          if (designAuthCodeInput) designAuthCodeInput.focus();
          setDesignSubmitBusy(submitButton, false);
          return;
        }

        callDesignAuth('zarvel_portal_modal_verify_code', email, code)
          .then(function (data) {
            designAuthVerified = true;
            if (emailInput) {
              emailInput.value = data.email || email;
              emailInput.readOnly = true;
            }
            setDesignAuthMessage(data.message || 'You are logged in.', 'is-verified');
            designRequestForm.submit();
          })
          .catch(function (error) {
            setDesignAuthMessage(error.message, 'is-error');
            setDesignSubmitBusy(submitButton, false);
          });
      });
    }

    keepDesignRequestButtonsClickable();

    designModalCloseButtons.forEach(function (button) {
      button.addEventListener('click', closeDesignModal);
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && designModal && designModal.classList.contains('is-open')) {
        closeDesignModal();
      }
    });

    if (placementSelect) {
      placementSelect.addEventListener('change', updatePlacementCostNote);
      updatePlacementCostNote();
    }

    formArea.addEventListener('click', function (event) {
      const button = event.target.closest('.single_add_to_cart_button');

      if (!button) return;

      const form = button.closest('form.cart');

      if (!form) return;

      event.preventDefault();
      event.stopPropagation();
      event.stopImmediatePropagation();

      openDesignModal(form);
    }, true);

    formArea.addEventListener('submit', function (event) {
      const form = event.target.closest('form.cart');

      if (!form) return;

      event.preventDefault();
      event.stopPropagation();
      event.stopImmediatePropagation();

      openDesignModal(form);
    }, true);

    if (window.jQuery) {
      jQuery(function ($) {
        $('.variations_form').on('show_variation found_variation reset_data woocommerce_variation_has_changed check_variations', function () {
          setTimeout(keepDesignRequestButtonsClickable, 50);
        });
      });
    }
  }

  function initZcQuantityAndButtons() {
    const formArea = document.querySelector('[data-zc-product-form-area]');
    const shopBlankBtn = document.querySelector('[data-zc-shop-blank-btn]');

    if (!formArea || !shopBlankBtn) return;

    function moveShopButton() {
      const addToCartRow =
        formArea.querySelector('.woocommerce-variation-add-to-cart') ||
        formArea.querySelector('form.cart:not(.variations_form)');

      if (!addToCartRow) return;

      if (!addToCartRow.querySelector('[data-zc-shop-blank-btn]')) {
        addToCartRow.appendChild(shopBlankBtn);
      }
    }

    function setupQuantityButtons() {
      const quantities = formArea.querySelectorAll('.quantity');

      quantities.forEach(function (quantity) {
        const input = quantity.querySelector('input.qty');

        if (!input || quantity.classList.contains('zc-qty-ready')) return;

        quantity.classList.add('zc-qty-ready');

        const minusBtn = document.createElement('button');
        minusBtn.type = 'button';
        minusBtn.className = 'zc-qty-btn zc-qty-btn--minus';
        minusBtn.setAttribute('aria-label', 'Decrease quantity');
        minusBtn.textContent = '−';

        const plusBtn = document.createElement('button');
        plusBtn.type = 'button';
        plusBtn.className = 'zc-qty-btn zc-qty-btn--plus';
        plusBtn.setAttribute('aria-label', 'Increase quantity');
        plusBtn.textContent = '+';

        input.insertAdjacentElement('beforebegin', minusBtn);
        input.insertAdjacentElement('afterend', plusBtn);

        minusBtn.addEventListener('click', function () {
          const currentValue = parseFloat(input.value) || 1;
          const min = parseFloat(input.getAttribute('min')) || 1;
          const step = parseFloat(input.getAttribute('step')) || 1;
          const newValue = Math.max(min, currentValue - step);

          input.value = newValue;
          input.dispatchEvent(new Event('change', { bubbles: true }));
        });

        plusBtn.addEventListener('click', function () {
          const currentValue = parseFloat(input.value) || 1;
          const max = parseFloat(input.getAttribute('max'));
          const step = parseFloat(input.getAttribute('step')) || 1;
          let newValue = currentValue + step;

          if (!Number.isNaN(max) && max > 0) {
            newValue = Math.min(max, newValue);
          }

          input.value = newValue;
          input.dispatchEvent(new Event('change', { bubbles: true }));
        });
      });
    }

    function getMissingVariationLabels(form) {
      const missing = [];

      form.querySelectorAll('.variations select').forEach(function (select) {
        if (select.value) return;

        const row = select.closest('tr');
        const label = row ? row.querySelector('label') : null;
        const labelText = label ? label.textContent.trim() : 'option';

        missing.push(labelText.replace(':', ''));
      });

      return missing;
    }

    function submitSelectedProductToCart() {
      const form =
        shopBlankBtn.closest('form.cart') ||
        formArea.querySelector('form.cart');

      if (!form) return;

      const variationIdInput = form.querySelector('input[name="variation_id"]');
      const variationId = variationIdInput && variationIdInput.value ? variationIdInput.value : '';
      const missingVariationLabels = getMissingVariationLabels(form);

      if (form.classList.contains('variations_form') && (missingVariationLabels.length || !variationId || variationId === '0')) {
        if (window.jQuery) {
          jQuery(form).trigger('check_variations');
        }

        alert('Please select ' + (missingVariationLabels.join(', ') || 'all product options') + ' first.');
        return;
      }

      HTMLFormElement.prototype.submit.call(form);
    }

    moveShopButton();
    setupQuantityButtons();

    shopBlankBtn.addEventListener('click', function (event) {
      event.preventDefault();
      submitSelectedProductToCart();
    });

    if (window.jQuery) {
      jQuery(function ($) {
        $('.variations_form').on('show_variation found_variation reset_data woocommerce_variation_has_changed', function () {
          setTimeout(function () {
            moveShopButton();
            setupQuantityButtons();
          }, 50);
        });
      });
    }
  }

  function getColorValue(colorName) {
    const key = normalizeColorName(colorName);

    const map = {
      black: '#111111',
      white: '#ffffff',
      red: '#c92828',
      blue: '#4f7fa8',
      navy: '#111d35',
      gray: '#b8b8b8',
      grey: '#b8b8b8',
      'sport grey': '#b8b8b8',
      'sport gray': '#b8b8b8',
      ash: '#d8d8d8',
      beige: '#d6c0a2',
      brown: '#8b5a35',
      orange: '#ff5b1a',
      mustard: '#d69b22',
      yellow: '#f2c94c',
      pink: '#f3a9c4',
      'light pink': '#f6c6d8',
      'dusty pink': '#d99aaa',
      green: '#5f8f60',
      purple: '#7d5ba6'
    };

    if (map[key]) return map[key];

    if (key.includes('pink')) return '#f3a9c4';
    if (key.includes('black')) return '#111111';
    if (key.includes('white')) return '#ffffff';
    if (key.includes('grey') || key.includes('gray')) return '#b8b8b8';
    if (key.includes('red')) return '#c92828';
    if (key.includes('blue')) return '#4f7fa8';
    if (key.includes('green')) return '#5f8f60';
    if (key.includes('yellow')) return '#f2c94c';
    if (key.includes('orange')) return '#ff5b1a';
    if (key.includes('mustard')) return '#d69b22';

    return '#d9d9d9';
  }
});
</script>

<style>
.zc-single-product-page {
  background: #ffffff;
}

.zc-product-section {
  padding: 24px 0 80px;
}

.zc-product-container {
  width: min(100% - 40px, 1280px);
  margin: 0 auto;
}

.zc-product-breadcrumb {
  margin-bottom: 18px;
}

.zc-product-breadcrumb .woocommerce-breadcrumb {
  margin: 0;
  color: #777777;
  font-size: 12px;
  font-weight: 600;
}

.zc-product-breadcrumb .woocommerce-breadcrumb a {
  color: #777777;
  text-decoration: none;
}

.zc-product-breadcrumb .woocommerce-breadcrumb a:hover {
  color: #ff5b1a;
}

.zc-product-breadcrumb .woocommerce-breadcrumb span {
  margin: 0 7px;
  color: #aaaaaa;
}

.zc-product-layout {
  display: grid;
  grid-template-columns: minmax(0, 1.08fr) minmax(0, 1fr);
  gap: 54px;
  align-items: start;
}

.zc-product-layout > *,
.zc-product-gallery,
.zc-product-summary {
  min-width: 0;
}

.zc-product-gallery {
  display: grid;
  grid-template-columns: 88px minmax(0, 1fr);
  gap: 18px;
  align-items: start;
}

.zc-product-thumbs {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.zc-product-thumb {
  width: 88px;
  height: 108px;
  padding: 6px;
  border-radius: 10px;
  border: 2px solid transparent;
  background: #f7f7f7;
  cursor: pointer;
  transition: 0.2s ease;
}

.zc-product-thumb.is-active {
  border-color: #ff5b1a;
}

.zc-product-thumb img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
}

.zc-product-main-image-wrap {
  position: relative;
  min-height: 620px;
  border-radius: 14px;
  background: linear-gradient(135deg, #faf7f2 0%, #f7f7f7 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.zc-product-main-image {
  width: 92%;
  max-height: 580px;
  object-fit: contain;
  display: block;
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.zc-product-main-image.is-changing {
  opacity: 0;
  transform: scale(0.98);
}

.zc-product-wishlist,
.zc-product-zoom {
  position: absolute;
  z-index: 3;
  width: 38px;
  height: 38px;
  padding: 0;
  border: 0;
  border-radius: 50%;
  background: #ffffff;
  color: #111111;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.08);
  transition: 0.2s ease;
}

.zc-product-wishlist {
  top: 18px;
  right: 18px;
}

.zc-product-zoom {
  right: 18px;
  bottom: 18px;
}

.zc-product-wishlist:hover,
.zc-product-zoom:hover {
  background: #ff5b1a;
  color: #ffffff;
}

.zc-product-wishlist svg,
.zc-product-zoom svg {
  width: 19px;
  height: 19px;
  fill: none;
  stroke: currentColor;
  stroke-width: 2;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.zc-product-summary {
  padding-top: 6px;
}

.zc-product-title {
  margin: 0 0 12px;
  color: #111111;
  font-size: clamp(36px, 4vw, 58px);
  line-height: 0.92;
  font-weight: 950;
  text-transform: uppercase;
  letter-spacing: -1.5px;
}

.zc-product-rating-row {
  display: flex;
  align-items: center;
  gap: 9px;
  margin-bottom: 8px;
}

.zc-product-stars .star-rating {
  float: none;
  margin: 0;
  font-size: 13px;
  width: 5.4em;
  color: #ffb000;
}

.zc-empty-stars {
  color: #ffb000;
  font-size: 13px;
  letter-spacing: 1px;
}

.zc-product-review-link {
  color: #555555;
  font-size: 13px;
  font-weight: 700;
  text-decoration: none;
}

.zc-product-review-link:hover {
  color: #ff5b1a;
}

.zc-product-price {
  margin-bottom: 12px;
  color: #ff5b1a;
  font-size: 26px;
  line-height: 1;
  font-weight: 950;
}

.zc-product-price del {
  color: #999999;
  font-size: 17px;
  font-weight: 700;
  margin-right: 8px;
}

.zc-product-price ins {
  text-decoration: none;
}

.zc-product-short-desc {
  max-width: 590px;
  margin-bottom: 18px;
}

.zc-product-short-desc p {
  margin: 0;
  color: #333333;
  font-size: 15px;
  line-height: 1.5;
  font-weight: 600;
}

.zc-product-benefits {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 12px;
  margin: 20px 0 24px;
}

.zc-product-benefit {
  min-height: 76px;
  padding: 12px 10px;
  border-radius: 12px;
  background: #fafafa;
  border: 1px solid #eeeeee;
  text-align: center;
}

.zc-product-benefit svg {
  width: 23px;
  height: 23px;
  margin-bottom: 7px;
  fill: none;
  stroke: #111111;
  stroke-width: 1.9;
  stroke-linecap: round;
  stroke-linejoin: round;
}

.zc-product-benefit span {
  display: block;
  color: #111111;
  font-size: 11px;
  line-height: 1.2;
  font-weight: 850;
}

.zc-product-form-area {
  margin-top: 18px;
  max-width: 560px;
}

.zc-product-form-area form.cart {
  margin: 0;
}

.zc-product-form-area table.variations {
  width: 100%;
  margin: 0 0 18px;
  border: 0;
}

.zc-product-form-area table.variations tr {
  display: block;
  margin-bottom: 16px;
}

.zc-product-form-area table.variations th,
.zc-product-form-area table.variations td {
  display: block;
  padding: 0;
  border: 0;
  background: transparent;
  text-align: left;
}

.zc-product-form-area table.variations label {
  display: inline-flex;
  margin-bottom: 9px;
  color: #111111;
  font-size: 12px;
  line-height: 1;
  font-weight: 950;
  text-transform: uppercase;
}

.zc-product-form-area .reset_variations {
  display: inline-block;
  margin-top: 8px;
  color: #777777;
  font-size: 12px;
  font-weight: 700;
}

.zc-variation-buttons {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 9px;
}

.zc-variation-btn {
  min-width: 46px;
  height: 36px;
  padding: 0 15px;
  border: 1px solid #dddddd;
  border-radius: 8px;
  background: #ffffff;
  color: #111111;
  font-size: 12px;
  font-weight: 850;
  cursor: pointer;
  transition: 0.2s ease;
}

.zc-variation-btn:hover,
.zc-variation-btn.is-active {
  border-color: #ff5b1a;
  color: #ff5b1a;
  background: #fff4ed;
}

.zc-variation-btn--color {
  width: 34px;
  min-width: 34px;
  height: 34px;
  padding: 0;
  border-radius: 50%;
  background: #ffffff;
}

.zc-color-swatch {
  width: 22px;
  height: 22px;
  display: block;
  border-radius: 50%;
  border: 1px solid #dddddd;
  margin: 0 auto;
}

.zc-variation-btn--color.is-active {
  box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px #111111;
  border-color: transparent;
  background: #ffffff;
}

.zc-product-form-area .single_variation_wrap {
  margin-top: 8px;
}

.zc-product-form-area .woocommerce-variation {
  margin-bottom: 12px;
}

.zc-product-form-area .woocommerce-variation-price {
  color: #ff5b1a;
  font-size: 18px;
  font-weight: 950;
}

.zc-product-form-area .woocommerce-variation-add-to-cart,
.zc-product-form-area form.cart:not(.variations_form) {
  display: grid !important;
  grid-template-columns: 1fr 1fr;
  align-items: stretch;
  gap: 14px;
  width: 100%;
}

.zc-product-form-area .woocommerce-variation-add-to-cart::before,
.zc-product-form-area form.cart:not(.variations_form)::before {
  content: "QUANTITY:";
  grid-column: 1 / -1;
  color: #111111;
  font-size: 12px;
  line-height: 1;
  font-weight: 950;
  text-transform: uppercase;
  margin-bottom: -4px;
}

.zc-product-form-area .quantity {
  grid-column: 1 / -1;
  width: 112px;
  height: 38px;
  display: grid !important;
  grid-template-columns: 34px 44px 34px;
  align-items: center;
  border: 1px solid #dddddd;
  border-radius: 6px;
  overflow: hidden;
  background: #ffffff;
}

.zc-product-form-area .quantity input.qty {
  width: 44px;
  height: 38px;
  padding: 0;
  border: 0;
  outline: 0;
  text-align: center;
  color: #111111;
  font-size: 14px;
  font-weight: 850;
  appearance: textfield;
  -moz-appearance: textfield;
}

.zc-product-form-area .quantity input.qty::-webkit-inner-spin-button,
.zc-product-form-area .quantity input.qty::-webkit-outer-spin-button {
  margin: 0;
  appearance: none;
  -webkit-appearance: none;
}

.zc-qty-btn {
  width: 34px;
  height: 38px;
  padding: 0;
  border: 0;
  background: #ffffff;
  color: #111111;
  font-size: 18px;
  line-height: 1;
  font-weight: 850;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}

.zc-qty-btn:hover {
  background: #f5f5f5;
}

.zc-product-form-area .single_add_to_cart_button {
  grid-column: 1 / 2;
  width: 100%;
  min-height: 52px;
  padding: 0 24px !important;
  border-radius: 6px !important;
  background: #ff5b1a !important;
  color: #ffffff !important;
  border: 0 !important;
  font-size: 13px !important;
  font-weight: 950 !important;
  text-transform: uppercase;
  display: inline-flex !important;
  align-items: center;
  justify-content: center;
  gap: 16px;
  transition: 0.2s ease;
}

.zc-product-form-area .single_add_to_cart_button::after {
  content: "→";
  font-size: 18px;
  line-height: 1;
  font-weight: 900;
}

.zc-product-form-area .single_add_to_cart_button:hover {
  background: #111111 !important;
}

.zc-product-form-area .single_add_to_cart_button.disabled,
.zc-product-form-area .single_add_to_cart_button:disabled,
.zc-product-form-area .single_add_to_cart_button.wc-variation-selection-needed {
  opacity: 0.55;
  cursor: not-allowed;
}

.zc-shop-blank-btn {
  grid-column: 2 / 3;
  width: 100%;
  min-height: 52px;
  margin-top: 0;
  padding: 0 24px;
  border-radius: 6px;
  background: #111111;
  color: #ffffff;
  text-decoration: none;
  font-size: 13px;
  font-weight: 950;
  text-transform: uppercase;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: 0.2s ease;
}

.zc-shop-blank-btn:hover {
  background: #ff5b1a;
  color: #ffffff;
}

.zc-design-modal-lock {
  overflow: hidden;
}

.zc-design-modal {
  position: fixed;
  inset: 0;
  z-index: 100000;
  display: none;
}

.zc-design-modal.is-open {
  display: block;
}

.zc-design-modal__overlay {
  position: absolute;
  inset: 0;
  border: 0;
  background: rgba(0, 0, 0, 0.62);
  cursor: pointer;
}

.zc-design-modal__panel {
  position: relative;
  width: min(100% - 32px, 820px);
  max-height: min(90vh, 860px);
  margin: 5vh auto;
  padding: 24px;
  border-radius: 8px;
  background: #ffffff;
  overflow-y: auto;
  box-shadow: 0 26px 80px rgba(0, 0, 0, 0.28);
}

.zc-design-modal__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 18px;
  margin-bottom: 18px;
}

.zc-design-modal__header p {
  margin: 0 0 6px;
  color: #ff5b1a;
  font-size: 12px;
  line-height: 1;
  font-weight: 950;
  text-transform: uppercase;
}

.zc-design-modal__header h2 {
  margin: 0;
  color: #111111;
  font-size: 30px;
  line-height: 1;
  font-weight: 950;
  text-transform: uppercase;
}

.zc-design-modal__close {
  position: relative;
  width: 38px;
  height: 38px;
  border: 1px solid #eeeeee;
  border-radius: 50%;
  background: #ffffff;
  cursor: pointer;
  flex: 0 0 auto;
}

.zc-design-modal__close span {
  position: absolute;
  top: 18px;
  left: 10px;
  width: 16px;
  height: 2px;
  background: #111111;
}

.zc-design-modal__close span:first-child {
  transform: rotate(45deg);
}

.zc-design-modal__close span:last-child {
  transform: rotate(-45deg);
}

.zc-design-modal__selected {
  margin-bottom: 18px;
  padding: 14px 16px;
  border: 1px solid #eeeeee;
  border-radius: 6px;
  background: #fafafa;
  display: grid;
  gap: 5px;
}

.zc-design-modal__selected span,
.zc-design-modal__form label span,
.zc-design-modal__notes span {
  color: #111111;
  font-size: 12px;
  line-height: 1;
  font-weight: 950;
  text-transform: uppercase;
}

.zc-design-modal__selected strong {
  color: #111111;
  font-size: 16px;
  line-height: 1.2;
  font-weight: 900;
}

.zc-design-modal__selected em {
  color: #666666;
  font-size: 13px;
  line-height: 1.35;
  font-style: normal;
  font-weight: 650;
}

.zc-design-modal__grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.zc-design-modal__form label {
  display: grid;
  gap: 8px;
}

.zc-design-modal__form input,
.zc-design-modal__form select,
.zc-design-modal__form textarea {
  width: 100%;
  border: 1px solid #dddddd;
  border-radius: 6px;
  background: #ffffff;
  color: #111111;
  font-size: 14px;
  font-weight: 650;
  outline: 0;
}

.zc-design-modal__form input,
.zc-design-modal__form select {
  min-height: 44px;
  padding: 0 12px;
}

.zc-design-modal__form input[type="file"] {
  padding: 10px 12px;
  display: flex;
  align-items: center;
}

.zc-design-modal__form input[type="file"]::file-selector-button {
  min-height: 30px;
  margin-right: 12px;
  border: 0;
  border-radius: 5px;
  background: #111111;
  color: #ffffff;
  font-size: 12px;
  font-weight: 850;
  cursor: pointer;
}

.zc-design-modal__form textarea {
  min-height: 118px;
  padding: 12px;
  resize: vertical;
}

.zc-design-modal__form input:focus,
.zc-design-modal__form select:focus,
.zc-design-modal__form textarea:focus {
  border-color: #ff5b1a;
  box-shadow: 0 0 0 3px rgba(255, 91, 26, 0.12);
}

.zc-design-modal__notes {
  margin-top: 14px;
}

.zc-design-modal__placement-cost {
  margin-top: 14px;
  padding: 12px 14px;
  border-radius: 6px;
  background: #fff4ed;
  color: #8f330e;
  font-size: 13px;
  line-height: 1.4;
  font-weight: 750;
}

.zc-design-modal__auth {
  margin-top: 14px;
  padding: 14px;
  border: 1px solid #eeeeee;
  border-radius: 6px;
  background: #fafafa;
  display: grid;
  gap: 12px;
}

.zc-design-modal__auth strong {
  color: #111111;
  font-size: 14px;
  line-height: 1.4;
}

.zc-design-modal__auth.is-pending {
  border-color: #f0d38b;
  background: #fff8e8;
}

.zc-design-modal__auth.is-verified {
  border-color: #b9dfc4;
  background: #eefbf2;
}

.zc-design-modal__auth.is-error {
  border-color: #f2b7aa;
  background: #fff0ed;
}

.zc-design-modal__submit {
  width: 100%;
  min-height: 50px;
  margin-top: 16px;
  border: 0;
  border-radius: 6px;
  background: #ff5b1a;
  color: #ffffff;
  font-size: 13px;
  font-weight: 950;
  text-transform: uppercase;
  cursor: pointer;
}

.zc-design-modal__submit:disabled {
  opacity: 0.6;
  cursor: wait;
}

.zc-design-modal__submit:hover {
  background: #111111;
}

.zc-product-trust-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 18px;
  margin-top: 22px;
  color: #555555;
  font-size: 12px;
  font-weight: 750;
}

.zc-product-trust-row span {
  position: relative;
  padding-left: 18px;
}

.zc-product-trust-row span::before {
  content: "✓";
  position: absolute;
  left: 0;
  top: 0;
  color: #111111;
  font-weight: 950;
}

@media screen and (max-width: 1024px) {
  .zc-product-layout {
    grid-template-columns: 1fr;
    gap: 38px;
  }

  .zc-product-main-image-wrap {
    min-height: 560px;
  }

  .zc-product-benefits {
    grid-template-columns: repeat(4, minmax(0, 1fr));
  }
}

@media screen and (max-width: 768px) {
  .zc-product-section {
    padding: 18px 0 60px;
  }

  .zc-product-container {
    width: min(100% - 30px, 1280px);
  }

  .zc-product-gallery {
    grid-template-columns: 1fr;
  }

  .zc-product-thumbs {
    order: 2;
    flex-direction: row;
    overflow-x: auto;
    padding-bottom: 6px;
  }

  .zc-product-thumb {
    min-width: 78px;
    width: 78px;
    height: 92px;
  }

  .zc-product-main-image-wrap {
    min-height: 430px;
  }

  .zc-product-title {
    font-size: 38px;
  }

  .zc-product-benefits {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .zc-product-form-area .woocommerce-variation-add-to-cart,
  .zc-product-form-area form.cart:not(.variations_form) {
    grid-template-columns: 1fr;
  }

  .zc-product-form-area .single_add_to_cart_button,
  .zc-shop-blank-btn {
    grid-column: 1 / -1;
    width: 100%;
  }

  .zc-design-modal__panel {
    width: min(100% - 20px, 820px);
    max-height: 94vh;
    margin: 3vh auto;
    padding: 18px;
  }

  .zc-design-modal__grid {
    grid-template-columns: 1fr;
  }

  .zc-design-modal__header h2 {
    font-size: 24px;
  }
}

@media screen and (max-width: 480px) {
  .zc-product-main-image-wrap {
    min-height: 360px;
  }

  .zc-product-title {
    font-size: 32px;
  }

  .zc-product-benefits {
    grid-template-columns: 1fr 1fr;
    gap: 10px;
  }

  .zc-product-benefit {
    min-height: 70px;
  }

  .zc-product-trust-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 9px;
  }
}
</style>
