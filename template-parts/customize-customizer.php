<?php
defined('ABSPATH') || exit;

if (!class_exists('WooCommerce')) {
  ?>
  <section class="zc-product-customizer">
    <div class="zc-product-customizer__inner">
      <p>WooCommerce is required for this customizer.</p>
    </div>
  </section>
  <?php
  return;
}

$products = wc_get_products([
  'status'  => 'publish',
  'limit'   => 250,
  'type'    => ['simple', 'variable'],
  'orderby' => 'title',
  'order'   => 'ASC',
]);

$product_catalog = [];

foreach ($products as $product) {
  if (!$product instanceof WC_Product) {
    continue;
  }

  $product_data = [
    'id'       => $product->get_id(),
    'title'    => $product->get_name(),
    'type'     => $product->get_type(),
    'image'    => wp_get_attachment_image_url($product->get_image_id(), 'large'),
    'price'    => (float) wc_get_price_to_display($product),
    'variants' => [],
  ];

  if ($product->is_type('variable')) {
    $variation_ids = $product->get_children();

    foreach ($variation_ids as $variation_id) {
      $variation = wc_get_product($variation_id);

      if (!$variation instanceof WC_Product_Variation) {
        continue;
      }

      $attributes = [];
      $color_value = '';

      foreach ($variation->get_attributes() as $attribute_name => $attribute_value) {
        $clean_name = wc_attribute_label(str_replace('attribute_', '', $attribute_name));
        $clean_value = $attribute_value;

        if (taxonomy_exists(str_replace('attribute_', '', $attribute_name))) {
          $term = get_term_by('slug', $attribute_value, str_replace('attribute_', '', $attribute_name));
          if ($term && !is_wp_error($term)) {
            $clean_value = $term->name;
          }
        }

        $attributes[$clean_name] = $clean_value;

        if (
          stripos($clean_name, 'color') !== false ||
          stripos($clean_name, 'colour') !== false
        ) {
          $color_value = $clean_value;
        }
      }

      $product_data['variants'][] = [
        'id'         => $variation->get_id(),
        'title'      => wc_get_formatted_variation($variation, true, false, true),
        'price'      => (float) wc_get_price_to_display($variation),
        'available'  => $variation->is_purchasable() && $variation->is_in_stock(),
        'attributes' => $attributes,
        'color'      => $color_value,
        'image'      => wp_get_attachment_image_url($variation->get_image_id(), 'large'),
      ];
    }
  } else {
    $product_data['variants'][] = [
      'id'         => $product->get_id(),
      'title'      => $product->get_name(),
      'price'      => (float) wc_get_price_to_display($product),
      'available'  => $product->is_purchasable() && $product->is_in_stock(),
      'attributes' => [],
      'color'      => '',
      'image'      => wp_get_attachment_image_url($product->get_image_id(), 'large'),
    ];
  }

  $product_catalog[] = $product_data;
}

$ajax_url = admin_url('admin-ajax.php');
$nonce = wp_create_nonce('zc_customizer_add_to_cart');
$currency_symbol = get_woocommerce_currency_symbol();
?>

<section class="zc-product-customizer" data-zc-customizer>
  <style>
    .zc-product-customizer {
      padding: 60px 20px;
      background: #f7f4ef;
    }

    .zc-product-customizer * {
      box-sizing: border-box;
    }

    .zc-product-customizer__inner {
      max-width: 1320px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 420px minmax(0, 1fr);
      gap: 28px;
      align-items: start;
    }

    .zc-product-customizer__panel,
    .zc-product-customizer__preview {
      background: #fff;
      border: 1px solid rgba(0, 0, 0, 0.08);
      border-radius: 24px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
    }

    .zc-product-customizer__panel {
      overflow: hidden;
    }

    .zc-product-customizer__header {
      padding: 22px;
      border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    }

    .zc-product-customizer__title-input {
      width: 100%;
      border: 0;
      outline: 0;
      background: transparent;
      font-size: 22px;
      font-weight: 700;
      color: #161616;
    }

    .zc-product-customizer__body {
      padding: 22px;
    }

    .zc-product-customizer__group {
      margin-bottom: 18px;
    }

    .zc-product-customizer__label {
      display: block;
      margin-bottom: 8px;
      font-size: 13px;
      font-weight: 700;
      color: #222;
    }

    .zc-product-customizer__input,
    .zc-product-customizer__textarea {
      width: 100%;
      border: 1px solid rgba(0, 0, 0, 0.14);
      border-radius: 14px;
      background: #fff;
      padding: 13px 14px;
      font-size: 14px;
      color: #161616;
      outline: none;
    }

    .zc-product-customizer__textarea {
      min-height: 92px;
      resize: vertical;
    }

    .zc-product-customizer__input:focus,
    .zc-product-customizer__textarea:focus {
      border-color: #111;
    }

    .zc-product-customizer__tabs,
    .zc-product-customizer__tools,
    .zc-product-customizer__format-row {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }

    .zc-product-customizer__tab,
    .zc-product-customizer__tool,
    .zc-product-customizer__small-btn {
      border: 1px solid rgba(0, 0, 0, 0.14);
      background: #fff;
      color: #111;
      border-radius: 999px;
      padding: 10px 14px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      transition: 0.2s ease;
    }

    .zc-product-customizer__tab.is-active,
    .zc-product-customizer__tool.is-active,
    .zc-product-customizer__small-btn.is-active {
      background: #111;
      color: #fff;
      border-color: #111;
    }

    .zc-product-customizer__mode-panel[hidden],
    .zc-product-customizer__tool-panel[hidden] {
      display: none !important;
    }

    .zc-product-customizer__swatches {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }

    .zc-product-customizer__swatch {
      min-width: 42px;
      height: 42px;
      border-radius: 999px;
      border: 1px solid rgba(0, 0, 0, 0.14);
      background: #fff;
      cursor: pointer;
      font-size: 11px;
      font-weight: 700;
      padding: 0 10px;
    }

    .zc-product-customizer__swatch.is-active {
      outline: 3px solid #111;
      outline-offset: 2px;
    }

    .zc-product-customizer__colors {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }

    .zc-product-customizer__color-control {
      display: flex;
      align-items: center;
      gap: 10px;
      border: 1px solid rgba(0, 0, 0, 0.14);
      border-radius: 14px;
      padding: 10px;
    }

    .zc-product-customizer__color-control input {
      width: 42px;
      height: 38px;
      border: 0;
      padding: 0;
      background: transparent;
    }

    .zc-product-customizer__upload {
      display: block;
      border: 2px dashed rgba(0, 0, 0, 0.18);
      border-radius: 18px;
      padding: 22px;
      text-align: center;
      cursor: pointer;
      background: #fafafa;
    }

    .zc-product-customizer__upload input {
      display: none;
    }

    .zc-product-customizer__clipart-grid,
    .zc-product-customizer__shape-grid,
    .zc-product-customizer__template-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 10px;
    }

    .zc-product-customizer__asset-btn {
      border: 1px solid rgba(0, 0, 0, 0.14);
      background: #fff;
      border-radius: 14px;
      min-height: 72px;
      cursor: pointer;
      font-weight: 800;
    }

    .zc-product-customizer__preview {
      min-height: 720px;
      padding: 22px;
      display: flex;
      flex-direction: column;
      gap: 18px;
    }

    .zc-product-customizer__safe-banner {
      display: flex;
      align-items: center;
      gap: 10px;
      color: #b10000;
      background: #fff5f5;
      border: 1px solid rgba(177, 0, 0, 0.16);
      border-radius: 14px;
      padding: 12px 14px;
      font-size: 13px;
      font-weight: 700;
    }

    .zc-product-customizer__canvas-wrap {
      flex: 1;
      display: grid;
      place-items: center;
      background:
        linear-gradient(45deg, rgba(0,0,0,.035) 25%, transparent 25%),
        linear-gradient(-45deg, rgba(0,0,0,.035) 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, rgba(0,0,0,.035) 75%),
        linear-gradient(-45deg, transparent 75%, rgba(0,0,0,.035) 75%);
      background-size: 24px 24px;
      background-position: 0 0, 0 12px, 12px -12px, -12px 0px;
      border-radius: 24px;
      padding: 24px;
      min-height: 560px;
    }

    .zc-product-customizer__canvas-stage {
      position: relative;
      width: min(100%, 600px);
      aspect-ratio: 1 / 1;
    }

    .zc-product-customizer__canvas {
      width: 100%;
      height: 100%;
      display: block;
      background: #fff;
      border-radius: 999px;
      box-shadow: inset 0 0 0 2px rgba(0, 0, 0, 0.08), 0 18px 40px rgba(0, 0, 0, 0.12);
      cursor: grab;
    }

    .zc-product-customizer__canvas:active {
      cursor: grabbing;
    }

    .zc-product-customizer__canvas-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      justify-content: center;
    }

    .zc-product-customizer__footer {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      border-top: 1px solid rgba(0, 0, 0, 0.08);
      padding-top: 18px;
    }

    .zc-product-customizer__price {
      font-size: 20px;
      font-weight: 900;
      color: #111;
    }

    .zc-product-customizer__add {
      border: 0;
      background: #111;
      color: #fff;
      padding: 15px 24px;
      border-radius: 999px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 900;
    }

    .zc-product-customizer__add:disabled {
      opacity: 0.55;
      cursor: not-allowed;
    }

    .zc-product-customizer__notice {
      display: none;
      padding: 12px 14px;
      border-radius: 14px;
      font-size: 13px;
      font-weight: 700;
    }

    .zc-product-customizer__notice.is-active {
      display: block;
    }

    .zc-product-customizer__notice.is-error {
      background: #fff2f2;
      color: #a40000;
    }

    .zc-product-customizer__notice.is-success {
      background: #f0fff4;
      color: #116329;
    }

    @media (max-width: 989px) {
      .zc-product-customizer__inner {
        grid-template-columns: 1fr;
      }

      .zc-product-customizer__preview {
        min-height: auto;
      }
    }

    @media (max-width: 640px) {
      .zc-product-customizer {
        padding: 36px 14px;
      }

      .zc-product-customizer__header,
      .zc-product-customizer__body,
      .zc-product-customizer__preview {
        padding: 16px;
      }

      .zc-product-customizer__colors {
        grid-template-columns: 1fr;
      }

      .zc-product-customizer__footer {
        align-items: stretch;
        flex-direction: column;
      }

      .zc-product-customizer__add {
        width: 100%;
      }
    }
  </style>

  <script type="application/json" data-zc-product-catalog>
    <?php echo wp_json_encode($product_catalog); ?>
  </script>

  <div
    class="zc-product-customizer__inner"
    data-ajax-url="<?php echo esc_url($ajax_url); ?>"
    data-nonce="<?php echo esc_attr($nonce); ?>"
    data-currency-symbol="<?php echo esc_attr($currency_symbol); ?>"
  >
    <div class="zc-product-customizer__panel">
      <div class="zc-product-customizer__header">
        <input
          type="text"
          class="zc-product-customizer__title-input"
          value="Untitled Design"
          data-design-title
          aria-label="Design title"
        >
      </div>

      <div class="zc-product-customizer__body">
        <div class="zc-product-customizer__notice" data-zc-notice></div>

        <div class="zc-product-customizer__tabs" role="tablist">
          <button type="button" class="zc-product-customizer__tab is-active" data-mode="editor">Editor</button>
          <button type="button" class="zc-product-customizer__tab" data-mode="templates">Templates</button>
          <button type="button" class="zc-product-customizer__tab" data-mode="drafts">Drafts</button>
        </div>

        <div class="zc-product-customizer__mode-panel" data-mode-panel="editor">
          <div class="zc-product-customizer__group" style="margin-top: 20px;">
            <label class="zc-product-customizer__label" for="ZcCustomizerProduct">Product</label>
            <select id="ZcCustomizerProduct" class="zc-product-customizer__input" data-product-select>
              <option value="">Select product</option>
              <?php foreach ($product_catalog as $catalog_product) : ?>
                <option value="<?php echo esc_attr($catalog_product['id']); ?>">
                  <?php echo esc_html($catalog_product['title']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="zc-product-customizer__group" data-variant-wrap hidden>
            <label class="zc-product-customizer__label" for="ZcCustomizerVariant">Variant</label>
            <select id="ZcCustomizerVariant" class="zc-product-customizer__input" data-variant-select>
              <option value="">Select variant</option>
            </select>
          </div>

          <div class="zc-product-customizer__group" data-color-wrap hidden>
            <label class="zc-product-customizer__label">
              Color: <span data-selected-color>—</span>
            </label>
            <div class="zc-product-customizer__swatches" data-color-swatches></div>
          </div>

          <div class="zc-product-customizer__group">
            <label class="zc-product-customizer__label" for="ZcCustomizerImprint">Imprint</label>
            <select id="ZcCustomizerImprint" class="zc-product-customizer__input" data-imprint>
              <option value="">Select imprint</option>
              <option value="Logo">Logo</option>
              <option value="Text">Text</option>
              <option value="Full Custom">Full Custom</option>
            </select>
          </div>

          <div class="zc-product-customizer__group">
            <label class="zc-product-customizer__label" for="ZcCustomizerImprintSize">Imprint size</label>
            <select id="ZcCustomizerImprintSize" class="zc-product-customizer__input" data-imprint-size>
              <option value="Other">Other</option>
              <option value='6"'>6"</option>
              <option value='8"'>8"</option>
              <option value='10"'>10"</option>
              <option value='12"'>12"</option>
              <option value='19"'>19"</option>
            </select>
          </div>

          <div class="zc-product-customizer__group">
            <span class="zc-product-customizer__label">Insert</span>
            <div class="zc-product-customizer__tools">
              <button type="button" class="zc-product-customizer__tool is-active" data-tool="text">Text</button>
              <button type="button" class="zc-product-customizer__tool" data-tool="image">Image</button>
              <button type="button" class="zc-product-customizer__tool" data-tool="clipart">Clipart</button>
              <button type="button" class="zc-product-customizer__tool" data-tool="shapes">Shapes</button>
            </div>
          </div>

          <div class="zc-product-customizer__tool-panel" data-tool-panel="text">
            <div class="zc-product-customizer__group">
              <label class="zc-product-customizer__label" for="ZcCustomizerText">Text</label>
              <textarea
                id="ZcCustomizerText"
                class="zc-product-customizer__textarea"
                data-text-input
                placeholder="Type your text here"
              ></textarea>
            </div>

            <div class="zc-product-customizer__group">
              <button type="button" class="zc-product-customizer__small-btn" data-add-text>Add Text</button>
            </div>

            <div class="zc-product-customizer__colors">
              <div class="zc-product-customizer__group">
                <label class="zc-product-customizer__label">Fill</label>
                <label class="zc-product-customizer__color-control">
                  <input type="color" value="#000000" data-text-color>
                  <span data-text-color-label>#000000</span>
                </label>
              </div>

              <div class="zc-product-customizer__group">
                <label class="zc-product-customizer__label">Outline</label>
                <label class="zc-product-customizer__color-control">
                  <input type="color" value="#ffffff" data-text-outline>
                  <span data-text-outline-label>#FFFFFF</span>
                </label>
              </div>
            </div>

            <div class="zc-product-customizer__group">
              <label class="zc-product-customizer__label" for="ZcCustomizerFontSize">Font size</label>
              <select id="ZcCustomizerFontSize" class="zc-product-customizer__input" data-font-size>
                <?php for ($i = 12; $i <= 120; $i += 2) : ?>
                  <option value="<?php echo esc_attr($i); ?>" <?php selected($i, 42); ?>>
                    <?php echo esc_html($i); ?>px
                  </option>
                <?php endfor; ?>
              </select>
            </div>

            <div class="zc-product-customizer__format-row">
              <button type="button" class="zc-product-customizer__small-btn" data-style-bold>B</button>
              <button type="button" class="zc-product-customizer__small-btn" data-style-italic>I</button>
              <button type="button" class="zc-product-customizer__small-btn" data-style-underline>U</button>
            </div>
          </div>

          <div class="zc-product-customizer__tool-panel" data-tool-panel="image" hidden>
            <label class="zc-product-customizer__upload">
              <strong>Upload image</strong>
              <br>
              <span>PNG, JPG, WebP</span>
              <input type="file" accept="image/*" data-image-upload>
            </label>
          </div>

          <div class="zc-product-customizer__tool-panel" data-tool-panel="clipart" hidden>
            <div class="zc-product-customizer__clipart-grid">
              <button type="button" class="zc-product-customizer__asset-btn" data-add-clipart="★">★</button>
              <button type="button" class="zc-product-customizer__asset-btn" data-add-clipart="♥">♥</button>
              <button type="button" class="zc-product-customizer__asset-btn" data-add-clipart="⚡">⚡</button>
              <button type="button" class="zc-product-customizer__asset-btn" data-add-clipart="☀">☀</button>
              <button type="button" class="zc-product-customizer__asset-btn" data-add-clipart="✦">✦</button>
              <button type="button" class="zc-product-customizer__asset-btn" data-add-clipart="✓">✓</button>
            </div>
          </div>

          <div class="zc-product-customizer__tool-panel" data-tool-panel="shapes" hidden>
            <div class="zc-product-customizer__shape-grid">
              <button type="button" class="zc-product-customizer__asset-btn" data-add-shape="circle">Circle</button>
              <button type="button" class="zc-product-customizer__asset-btn" data-add-shape="square">Square</button>
              <button type="button" class="zc-product-customizer__asset-btn" data-add-shape="triangle">Triangle</button>
            </div>
          </div>
        </div>

        <div class="zc-product-customizer__mode-panel" data-mode-panel="templates" hidden>
          <div style="margin-top: 20px;" class="zc-product-customizer__template-grid">
            <button type="button" class="zc-product-customizer__asset-btn" data-template="center-logo">Center Logo</button>
            <button type="button" class="zc-product-customizer__asset-btn" data-template="big-text">Big Text</button>
            <button type="button" class="zc-product-customizer__asset-btn" data-template="badge">Badge</button>
          </div>
        </div>

        <div class="zc-product-customizer__mode-panel" data-mode-panel="drafts" hidden>
          <p style="margin-top: 20px;">Draft saving can be added next using user meta or a custom database table.</p>
        </div>
      </div>
    </div>

    <div class="zc-product-customizer__preview">
      <div class="zc-product-customizer__safe-banner">
        ⚠ Can't print up to the end of the edge
      </div>

      <div class="zc-product-customizer__canvas-wrap">
        <div class="zc-product-customizer__canvas-stage">
          <canvas
            class="zc-product-customizer__canvas"
            width="600"
            height="600"
            data-canvas
            tabindex="0"
            aria-label="Customizer canvas"
          ></canvas>
        </div>
      </div>

      <div class="zc-product-customizer__canvas-actions">
        <button type="button" class="zc-product-customizer__small-btn" data-delete-selected>Delete Selected</button>
        <button type="button" class="zc-product-customizer__small-btn" data-clear-canvas>Clear Canvas</button>
      </div>

      <div class="zc-product-customizer__footer">
        <div class="zc-product-customizer__price">
          Total:
          <span data-total-price><?php echo esc_html($currency_symbol); ?>0.00</span>
        </div>

        <button type="button" class="zc-product-customizer__add" data-add-to-cart disabled>
          Add Custom Design to Cart
        </button>
      </div>
    </div>
  </div>

  <script>
    (() => {
      const root = document.querySelector('[data-zc-customizer]');
      if (!root) return;

      const inner = root.querySelector('.zc-product-customizer__inner');
      const catalogScript = root.querySelector('[data-zc-product-catalog]');
      const catalog = JSON.parse(catalogScript.textContent || '[]');

      const ajaxUrl = inner.dataset.ajaxUrl;
      const nonce = inner.dataset.nonce;
      const currencySymbol = inner.dataset.currencySymbol || '$';

      const productSelect = root.querySelector('[data-product-select]');
      const variantWrap = root.querySelector('[data-variant-wrap]');
      const variantSelect = root.querySelector('[data-variant-select]');
      const colorWrap = root.querySelector('[data-color-wrap]');
      const colorSwatches = root.querySelector('[data-color-swatches]');
      const selectedColor = root.querySelector('[data-selected-color]');
      const addToCartBtn = root.querySelector('[data-add-to-cart]');
      const totalPrice = root.querySelector('[data-total-price]');
      const notice = root.querySelector('[data-zc-notice]');

      const designTitle = root.querySelector('[data-design-title]');
      const imprint = root.querySelector('[data-imprint]');
      const imprintSize = root.querySelector('[data-imprint-size]');

      const canvas = root.querySelector('[data-canvas]');
      const ctx = canvas.getContext('2d');

      let selectedProduct = null;
      let selectedVariant = null;
      let selectedObject = null;
      let isDragging = false;
      let dragOffsetX = 0;
      let dragOffsetY = 0;

      const state = {
        objects: [],
        bold: false,
        italic: false,
        underline: false,
      };

      function money(value) {
        return currencySymbol + Number(value || 0).toFixed(2);
      }

      function showNotice(message, type = 'success') {
        notice.textContent = message;
        notice.className = 'zc-product-customizer__notice is-active';
        notice.classList.add(type === 'error' ? 'is-error' : 'is-success');
      }

      function clearNotice() {
        notice.textContent = '';
        notice.className = 'zc-product-customizer__notice';
      }

      function getProductById(id) {
        return catalog.find(product => String(product.id) === String(id));
      }

      function getVariantById(id) {
        if (!selectedProduct) return null;
        return selectedProduct.variants.find(variant => String(variant.id) === String(id));
      }

      function updatePrice() {
        if (!selectedVariant) {
          totalPrice.textContent = money(0);
          return;
        }

        let extra = 0;

        state.objects.forEach(object => {
          if (object.type === 'text') extra += 0;
          if (object.type === 'image') extra += 0;
          if (object.type === 'clipart') extra += 0;
          if (object.type === 'shape') extra += 0;
        });

        totalPrice.textContent = money(Number(selectedVariant.price || 0) + extra);
      }

      function updateAddButton() {
        addToCartBtn.disabled = !(selectedProduct && selectedVariant && selectedVariant.available);
      }

      function renderVariants() {
        variantSelect.innerHTML = '<option value="">Select variant</option>';
        colorSwatches.innerHTML = '';
        selectedColor.textContent = '—';

        if (!selectedProduct) {
          variantWrap.hidden = true;
          colorWrap.hidden = true;
          selectedVariant = null;
          updatePrice();
          updateAddButton();
          return;
        }

        const variants = selectedProduct.variants || [];

        if (variants.length > 1) {
          variantWrap.hidden = false;
        } else {
          variantWrap.hidden = true;
        }

        let hasColor = false;

        variants.forEach(variant => {
          const option = document.createElement('option');
          option.value = variant.id;
          option.textContent = variant.title || selectedProduct.title;

          if (!variant.available) {
            option.disabled = true;
            option.textContent += ' - Out of stock';
          }

          variantSelect.appendChild(option);

          if (variant.color) {
            hasColor = true;

            const swatch = document.createElement('button');
            swatch.type = 'button';
            swatch.className = 'zc-product-customizer__swatch';
            swatch.textContent = variant.color;
            swatch.dataset.variantId = variant.id;
            swatch.setAttribute('aria-label', variant.color);

            swatch.addEventListener('click', () => {
              variantSelect.value = variant.id;
              selectVariant(variant.id);
            });

            colorSwatches.appendChild(swatch);
          }
        });

        colorWrap.hidden = !hasColor;

        if (variants.length === 1) {
          selectedVariant = variants[0];
          variantSelect.value = selectedVariant.id;
        } else {
          selectedVariant = null;
        }

        updateSwatches();
        updatePrice();
        updateAddButton();
      }

      function updateSwatches() {
        root.querySelectorAll('[data-variant-id]').forEach(button => {
          button.classList.toggle(
            'is-active',
            selectedVariant && String(button.dataset.variantId) === String(selectedVariant.id)
          );
        });

        selectedColor.textContent = selectedVariant && selectedVariant.color ? selectedVariant.color : '—';
      }

      function selectVariant(variantId) {
        selectedVariant = getVariantById(variantId);
        updateSwatches();
        updatePrice();
        updateAddButton();
      }

      productSelect.addEventListener('change', () => {
        clearNotice();
        selectedProduct = getProductById(productSelect.value);
        selectedVariant = null;
        renderVariants();
      });

      variantSelect.addEventListener('change', () => {
        clearNotice();
        selectVariant(variantSelect.value);
      });

      root.querySelectorAll('[data-mode]').forEach(button => {
        button.addEventListener('click', () => {
          const mode = button.dataset.mode;

          root.querySelectorAll('[data-mode]').forEach(tab => {
            tab.classList.toggle('is-active', tab === button);
          });

          root.querySelectorAll('[data-mode-panel]').forEach(panel => {
            panel.hidden = panel.dataset.modePanel !== mode;
          });
        });
      });

      root.querySelectorAll('[data-tool]').forEach(button => {
        button.addEventListener('click', () => {
          const tool = button.dataset.tool;

          root.querySelectorAll('[data-tool]').forEach(toolButton => {
            toolButton.classList.toggle('is-active', toolButton === button);
          });

          root.querySelectorAll('[data-tool-panel]').forEach(panel => {
            panel.hidden = panel.dataset.toolPanel !== tool;
          });
        });
      });

      const textColor = root.querySelector('[data-text-color]');
      const textOutline = root.querySelector('[data-text-outline]');
      const textColorLabel = root.querySelector('[data-text-color-label]');
      const textOutlineLabel = root.querySelector('[data-text-outline-label]');
      const fontSize = root.querySelector('[data-font-size]');
      const textInput = root.querySelector('[data-text-input]');

      textColor.addEventListener('input', () => {
        textColorLabel.textContent = textColor.value.toUpperCase();
      });

      textOutline.addEventListener('input', () => {
        textOutlineLabel.textContent = textOutline.value.toUpperCase();
      });

      root.querySelector('[data-style-bold]').addEventListener('click', event => {
        state.bold = !state.bold;
        event.currentTarget.classList.toggle('is-active', state.bold);
      });

      root.querySelector('[data-style-italic]').addEventListener('click', event => {
        state.italic = !state.italic;
        event.currentTarget.classList.toggle('is-active', state.italic);
      });

      root.querySelector('[data-style-underline]').addEventListener('click', event => {
        state.underline = !state.underline;
        event.currentTarget.classList.toggle('is-active', state.underline);
      });

      root.querySelector('[data-add-text]').addEventListener('click', () => {
        const value = textInput.value.trim();

        if (!value) {
          showNotice('Add some text first.', 'error');
          return;
        }

        state.objects.push({
          id: Date.now(),
          type: 'text',
          text: value,
          x: 300,
          y: 300,
          color: textColor.value,
          outline: textOutline.value,
          fontSize: Number(fontSize.value || 42),
          bold: state.bold,
          italic: state.italic,
          underline: state.underline,
        });

        textInput.value = '';
        draw();
        updatePrice();
      });

      root.querySelector('[data-image-upload]').addEventListener('change', event => {
        const file = event.target.files[0];

        if (!file) return;

        const reader = new FileReader();

        reader.onload = () => {
          const image = new Image();

          image.onload = () => {
            state.objects.push({
              id: Date.now(),
              type: 'image',
              src: reader.result,
              x: 300,
              y: 300,
              width: 180,
              height: 180,
              image,
            });

            draw();
            updatePrice();
          };

          image.src = reader.result;
        };

        reader.readAsDataURL(file);
      });

      root.querySelectorAll('[data-add-clipart]').forEach(button => {
        button.addEventListener('click', () => {
          state.objects.push({
            id: Date.now(),
            type: 'clipart',
            text: button.dataset.addClipart,
            x: 300,
            y: 300,
            color: '#111111',
            fontSize: 76,
          });

          draw();
          updatePrice();
        });
      });

      root.querySelectorAll('[data-add-shape]').forEach(button => {
        button.addEventListener('click', () => {
          state.objects.push({
            id: Date.now(),
            type: 'shape',
            shape: button.dataset.addShape,
            x: 300,
            y: 300,
            width: 130,
            height: 130,
            color: '#111111',
          });

          draw();
          updatePrice();
        });
      });

      root.querySelectorAll('[data-template]').forEach(button => {
        button.addEventListener('click', () => {
          const template = button.dataset.template;

          state.objects = [];

          if (template === 'center-logo') {
            state.objects.push({
              id: Date.now(),
              type: 'clipart',
              text: '★',
              x: 300,
              y: 250,
              color: '#111111',
              fontSize: 92,
            });
          }

          if (template === 'big-text') {
            state.objects.push({
              id: Date.now(),
              type: 'text',
              text: 'ZARVEL',
              x: 300,
              y: 300,
              color: '#111111',
              outline: '#ffffff',
              fontSize: 62,
              bold: true,
              italic: false,
              underline: false,
            });
          }

          if (template === 'badge') {
            state.objects.push({
              id: Date.now(),
              type: 'shape',
              shape: 'circle',
              x: 300,
              y: 300,
              width: 220,
              height: 220,
              color: '#111111',
            });

            state.objects.push({
              id: Date.now() + 1,
              type: 'text',
              text: 'CUSTOM',
              x: 300,
              y: 300,
              color: '#ffffff',
              outline: '#111111',
              fontSize: 38,
              bold: true,
              italic: false,
              underline: false,
            });
          }

          draw();
          updatePrice();
        });
      });

      root.querySelector('[data-delete-selected]').addEventListener('click', () => {
        if (!selectedObject) return;

        state.objects = state.objects.filter(object => object !== selectedObject);
        selectedObject = null;
        draw();
        updatePrice();
      });

      root.querySelector('[data-clear-canvas]').addEventListener('click', () => {
        state.objects = [];
        selectedObject = null;
        draw();
        updatePrice();
      });

      function drawSafeArea() {
        ctx.save();

        ctx.beginPath();
        ctx.arc(300, 300, 255, 0, Math.PI * 2);
        ctx.strokeStyle = 'rgba(218, 0, 4, 0.5)';
        ctx.lineWidth = 2;
        ctx.setLineDash([8, 8]);
        ctx.stroke();

        ctx.restore();
      }

      function drawSelection(object) {
        if (object !== selectedObject) return;

        const bounds = getObjectBounds(object);
        if (!bounds) return;

        ctx.save();
        ctx.strokeStyle = '#111';
        ctx.lineWidth = 2;
        ctx.setLineDash([6, 4]);
        ctx.strokeRect(bounds.x, bounds.y, bounds.width, bounds.height);
        ctx.restore();
      }

      function drawObject(object) {
        ctx.save();

        if (object.type === 'text' || object.type === 'clipart') {
          const fontStyle = object.italic ? 'italic ' : '';
          const fontWeight = object.bold ? '800 ' : '400 ';
          ctx.font = `${fontStyle}${fontWeight}${object.fontSize}px Arial, sans-serif`;
          ctx.textAlign = 'center';
          ctx.textBaseline = 'middle';

          if (object.outline) {
            ctx.strokeStyle = object.outline;
            ctx.lineWidth = 4;
            ctx.strokeText(object.text, object.x, object.y);
          }

          ctx.fillStyle = object.color || '#111';
          ctx.fillText(object.text, object.x, object.y);

          if (object.underline) {
            const metrics = ctx.measureText(object.text);
            const width = metrics.width;
            ctx.beginPath();
            ctx.moveTo(object.x - width / 2, object.y + object.fontSize / 2);
            ctx.lineTo(object.x + width / 2, object.y + object.fontSize / 2);
            ctx.strokeStyle = object.color || '#111';
            ctx.lineWidth = 2;
            ctx.stroke();
          }
        }

        if (object.type === 'image' && object.image) {
          ctx.drawImage(
            object.image,
            object.x - object.width / 2,
            object.y - object.height / 2,
            object.width,
            object.height
          );
        }

        if (object.type === 'shape') {
          ctx.fillStyle = object.color || '#111';

          if (object.shape === 'circle') {
            ctx.beginPath();
            ctx.arc(object.x, object.y, object.width / 2, 0, Math.PI * 2);
            ctx.fill();
          }

          if (object.shape === 'square') {
            ctx.fillRect(
              object.x - object.width / 2,
              object.y - object.height / 2,
              object.width,
              object.height
            );
          }

          if (object.shape === 'triangle') {
            ctx.beginPath();
            ctx.moveTo(object.x, object.y - object.height / 2);
            ctx.lineTo(object.x - object.width / 2, object.y + object.height / 2);
            ctx.lineTo(object.x + object.width / 2, object.y + object.height / 2);
            ctx.closePath();
            ctx.fill();
          }
        }

        ctx.restore();

        drawSelection(object);
      }

      function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        ctx.save();
        ctx.fillStyle = '#ffffff';
        ctx.beginPath();
        ctx.arc(300, 300, 298, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();

        drawSafeArea();

        state.objects.forEach(drawObject);
      }

      function getObjectBounds(object) {
        if (object.type === 'text' || object.type === 'clipart') {
          ctx.save();
          const fontStyle = object.italic ? 'italic ' : '';
          const fontWeight = object.bold ? '800 ' : '400 ';
          ctx.font = `${fontStyle}${fontWeight}${object.fontSize}px Arial, sans-serif`;
          const width = Math.max(ctx.measureText(object.text).width, 30);
          ctx.restore();

          return {
            x: object.x - width / 2,
            y: object.y - object.fontSize / 2,
            width,
            height: object.fontSize,
          };
        }

        if (object.type === 'image' || object.type === 'shape') {
          return {
            x: object.x - object.width / 2,
            y: object.y - object.height / 2,
            width: object.width,
            height: object.height,
          };
        }

        return null;
      }

      function getCanvasPoint(event) {
        const rect = canvas.getBoundingClientRect();
        const scaleX = canvas.width / rect.width;
        const scaleY = canvas.height / rect.height;

        return {
          x: (event.clientX - rect.left) * scaleX,
          y: (event.clientY - rect.top) * scaleY,
        };
      }

      function hitTest(point) {
        for (let i = state.objects.length - 1; i >= 0; i--) {
          const object = state.objects[i];
          const bounds = getObjectBounds(object);

          if (!bounds) continue;

          if (
            point.x >= bounds.x &&
            point.x <= bounds.x + bounds.width &&
            point.y >= bounds.y &&
            point.y <= bounds.y + bounds.height
          ) {
            return object;
          }
        }

        return null;
      }

      canvas.addEventListener('mousedown', event => {
        const point = getCanvasPoint(event);
        selectedObject = hitTest(point);

        if (selectedObject) {
          isDragging = true;
          dragOffsetX = point.x - selectedObject.x;
          dragOffsetY = point.y - selectedObject.y;
        }

        draw();
      });

      canvas.addEventListener('mousemove', event => {
        if (!isDragging || !selectedObject) return;

        const point = getCanvasPoint(event);

        selectedObject.x = point.x - dragOffsetX;
        selectedObject.y = point.y - dragOffsetY;

        draw();
      });

      document.addEventListener('mouseup', () => {
        isDragging = false;
      });

      canvas.addEventListener('touchstart', event => {
        const touch = event.touches[0];
        if (!touch) return;

        const point = getCanvasPoint(touch);
        selectedObject = hitTest(point);

        if (selectedObject) {
          isDragging = true;
          dragOffsetX = point.x - selectedObject.x;
          dragOffsetY = point.y - selectedObject.y;
        }

        draw();
      }, { passive: true });

      canvas.addEventListener('touchmove', event => {
        const touch = event.touches[0];
        if (!touch || !isDragging || !selectedObject) return;

        const point = getCanvasPoint(touch);

        selectedObject.x = point.x - dragOffsetX;
        selectedObject.y = point.y - dragOffsetY;

        draw();
      }, { passive: true });

      document.addEventListener('touchend', () => {
        isDragging = false;
      });

      document.addEventListener('keydown', event => {
        if ((event.key === 'Delete' || event.key === 'Backspace') && selectedObject) {
          const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';

          if (activeTag === 'input' || activeTag === 'textarea' || activeTag === 'select') {
            return;
          }

          state.objects = state.objects.filter(object => object !== selectedObject);
          selectedObject = null;
          draw();
          updatePrice();
        }
      });

      function getSerializableObjects() {
        return state.objects.map(object => {
          const clean = { ...object };
          delete clean.image;
          return clean;
        });
      }

      addToCartBtn.addEventListener('click', async () => {
        clearNotice();

        if (!selectedProduct || !selectedVariant) {
          showNotice('Select a product and variant first.', 'error');
          return;
        }

        addToCartBtn.disabled = true;
        addToCartBtn.textContent = 'Adding...';

        const formData = new FormData();
        formData.append('action', 'zc_customizer_add_to_cart');
        formData.append('nonce', nonce);
        formData.append('product_id', selectedProduct.id);
        formData.append('variation_id', selectedVariant.id);
        formData.append('quantity', '1');
        formData.append('design_title', designTitle.value || 'Untitled Design');
        formData.append('imprint', imprint.value || '');
        formData.append('imprint_size', imprintSize.value || '');
        formData.append('customizer_json', JSON.stringify({
          product: selectedProduct,
          variant: selectedVariant,
          designTitle: designTitle.value || 'Untitled Design',
          imprint: imprint.value || '',
          imprintSize: imprintSize.value || '',
          objects: getSerializableObjects(),
        }));

        try {
          const response = await fetch(ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
          });

          const result = await response.json();

          if (!result.success) {
            throw new Error(result.data && result.data.message ? result.data.message : 'Could not add to cart.');
          }

          showNotice('Custom design added to cart.', 'success');

          if (result.data && result.data.cart_url) {
            window.location.href = result.data.cart_url;
          }
        } catch (error) {
          showNotice(error.message, 'error');
          addToCartBtn.disabled = false;
          addToCartBtn.textContent = 'Add Custom Design to Cart';
        }
      });

      draw();
      updatePrice();
      updateAddButton();
    })();
  </script>
</section>