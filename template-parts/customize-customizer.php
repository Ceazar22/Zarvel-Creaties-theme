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

      foreach ($variation->get_variation_attributes() as $attribute_name => $attribute_value) {
        $taxonomy_name = str_replace('attribute_', '', $attribute_name);
        $clean_name = wc_attribute_label($taxonomy_name);
        $clean_value = $attribute_value;

        if (taxonomy_exists($taxonomy_name)) {
          $term = get_term_by('slug', $attribute_value, $taxonomy_name);
          if ($term && !is_wp_error($term)) {
            $clean_value = $term->name;
          }
        }

        $attributes[$attribute_name] = $attribute_value;
        $attributes[$taxonomy_name] = $attribute_value;
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
      min-height: 100vh;
      padding: 0;
      background: #f5f5f5;
    }

    .zc-product-customizer * {
      box-sizing: border-box;
    }

    .zc-product-customizer__inner {
      width: 100%;
      min-height: 100vh;
      margin: 0;
      display: grid;
      grid-template-columns: 390px minmax(0, 1fr);
      gap: 0;
      align-items: stretch;
    }

    .zc-product-customizer__panel,
    .zc-product-customizer__preview {
      background: #fff;
      border: 0;
      border-radius: 0;
      box-shadow: none;
    }

    .zc-product-customizer__panel {
      height: 100vh;
      overflow: auto;
      border-right: 1px solid rgba(0, 0, 0, 0.08);
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

    .zc-product-customizer__range-row {
      display: grid;
      grid-template-columns: 1fr auto;
      gap: 10px;
      align-items: center;
    }

    .zc-product-customizer__range {
      width: 100%;
      accent-color: #111;
    }

    .zc-product-customizer__value {
      min-width: 58px;
      text-align: right;
      font-size: 13px;
      font-weight: 800;
      color: #111;
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
      min-height: 100vh;
      padding: 22px;
      display: flex;
      flex-direction: column;
      gap: 18px;
      overflow: hidden;
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
      min-height: 0;
      overflow: hidden;
    }

    .zc-product-customizer__canvas-stage {
      position: relative;
      width: min(100%, calc(100vh - 190px), 980px);
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

      .zc-product-customizer__panel {
        height: auto;
        max-height: none;
        border-right: 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
      }

      .zc-product-customizer__preview {
        min-height: 70vh;
      }

      .zc-product-customizer__canvas-stage {
        width: min(100%, 86vh);
      }
    }

    @media (max-width: 640px) {
      .zc-product-customizer {
        padding: 0;
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

            <div class="zc-product-customizer__group" style="margin-top: 18px;">
              <label class="zc-product-customizer__label" for="ZcCustomizerImageSize">Image size</label>
              <div class="zc-product-customizer__range-row">
                <input
                  id="ZcCustomizerImageSize"
                  class="zc-product-customizer__range"
                  type="range"
                  min="40"
                  max="420"
                  step="5"
                  value="180"
                  data-image-size
                >
                <span class="zc-product-customizer__value" data-image-size-label>180px</span>
              </div>
            </div>

            <div class="zc-product-customizer__format-row">
              <button type="button" class="zc-product-customizer__small-btn" data-image-size-step="-20">Smaller</button>
              <button type="button" class="zc-product-customizer__small-btn" data-image-size-step="20">Larger</button>
            </div>
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
      let catalog = [];

      try {
        catalog = JSON.parse(catalogScript ? catalogScript.textContent || '[]' : '[]');
      } catch (error) {
        catalog = [];
      }

      const ajaxUrl = inner.dataset.ajaxUrl;
      const nonce = inner.dataset.nonce;
      const currencySymbol = inner.dataset.currencySymbol || '$';
      const zcRequestParams = new URLSearchParams(window.location.search);
      const zcInitialProductId = zcRequestParams.get('zc_product_id') || '';
      const zcInitialVariationId = zcRequestParams.get('zc_variation_id') || '';
      const zcInitialQuantity = zcRequestParams.get('quantity') || '1';

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
      const imageSize = root.querySelector('[data-image-size]');
      const imageSizeLabel = root.querySelector('[data-image-size-label]');

      let selectedProduct = null;
      let selectedVariant = null;
      let selectedObject = null;
      let isDragging = false;
      let dragOffsetX = 0;
      let dragOffsetY = 0;
      let productPreviewImage = null;
      let productPreviewSrc = '';

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

      function getTrimmedImageSource(image) {
        const trimCanvas = document.createElement('canvas');
        const trimCtx = trimCanvas.getContext('2d');

        trimCanvas.width = image.naturalWidth;
        trimCanvas.height = image.naturalHeight;
        trimCtx.drawImage(image, 0, 0);

        const imageData = trimCtx.getImageData(0, 0, trimCanvas.width, trimCanvas.height);
        const data = imageData.data;
        let minX = trimCanvas.width;
        let minY = trimCanvas.height;
        let maxX = 0;
        let maxY = 0;
        let foundPixel = false;

        for (let y = 0; y < trimCanvas.height; y += 1) {
          for (let x = 0; x < trimCanvas.width; x += 1) {
            const index = (y * trimCanvas.width + x) * 4;
            const red = data[index];
            const green = data[index + 1];
            const blue = data[index + 2];
            const alpha = data[index + 3];
            const isVisible = alpha > 18;
            const isAlmostWhite = red > 245 && green > 245 && blue > 245;

            if (isVisible && !isAlmostWhite) {
              foundPixel = true;
              minX = Math.min(minX, x);
              minY = Math.min(minY, y);
              maxX = Math.max(maxX, x);
              maxY = Math.max(maxY, y);
            }
          }
        }

        if (!foundPixel) {
          return {
            src: image.src,
            width: image.naturalWidth,
            height: image.naturalHeight,
          };
        }

        const padding = 4;
        minX = Math.max(0, minX - padding);
        minY = Math.max(0, minY - padding);
        maxX = Math.min(trimCanvas.width - 1, maxX + padding);
        maxY = Math.min(trimCanvas.height - 1, maxY + padding);

        const cropWidth = maxX - minX + 1;
        const cropHeight = maxY - minY + 1;
        const croppedCanvas = document.createElement('canvas');
        const croppedCtx = croppedCanvas.getContext('2d');

        croppedCanvas.width = cropWidth;
        croppedCanvas.height = cropHeight;
        croppedCtx.drawImage(
          trimCanvas,
          minX,
          minY,
          cropWidth,
          cropHeight,
          0,
          0,
          cropWidth,
          cropHeight
        );

        return {
          src: croppedCanvas.toDataURL('image/png'),
          width: cropWidth,
          height: cropHeight,
        };
      }

      function setImageControlsFromObject(object) {
        if (!imageSize || !imageSizeLabel) return;

        const sizeValue = object && object.type === 'image'
          ? Math.round(Number(object.width) || 180)
          : Number(imageSize.value || 180);

        imageSize.value = String(Math.max(Number(imageSize.min), Math.min(Number(imageSize.max), sizeValue)));
        imageSizeLabel.textContent = imageSize.value + 'px';
      }

      function resizeSelectedImage(size) {
        if (!selectedObject || selectedObject.type !== 'image') {
          showNotice('Select an uploaded image first.', 'error');
          setImageControlsFromObject(null);
          return;
        }

        const printableArea = getPrintableArea();
        const aspectRatio = selectedObject.aspectRatio || 1;
        const maxPrintableWidth = Math.min(
          Number(imageSize.max) || 420,
          printableArea.width,
          printableArea.height * aspectRatio
        );
        const nextWidth = Math.max(
          Number(imageSize.min) || 40,
          Math.min(maxPrintableWidth, Number(size) || selectedObject.width || 180)
        );

        selectedObject.width = nextWidth;
        selectedObject.height = nextWidth / aspectRatio;
        clampObjectToPrintableArea(selectedObject);

        setImageControlsFromObject(selectedObject);
        clearNotice();
        draw();
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
          updateProductPreview();
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
        updateProductPreview();
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
        updateProductPreview();
        updatePrice();
        updateAddButton();
      }

      function getVariationAttributeMatch() {
        if (!selectedProduct || !Array.isArray(selectedProduct.variants)) {
          return null;
        }

        const requestedAttributes = {};

        zcRequestParams.forEach((value, key) => {
          if (key.indexOf('attribute_') === 0 && value) {
            requestedAttributes[key.replace(/^attribute_/, '')] = value;
          }
        });

        const requestedKeys = Object.keys(requestedAttributes);

        if (!requestedKeys.length) {
          return null;
        }

        return selectedProduct.variants.find(variant => {
          const attributes = variant.attributes || {};

          return requestedKeys.every(key => {
            const requestedValue = String(requestedAttributes[key]).toLowerCase();

            return Object.keys(attributes).some(attributeKey => {
              const normalizedKey = String(attributeKey).toLowerCase().replace(/^attribute_/, '');
              const normalizedValue = String(attributes[attributeKey]).toLowerCase();

              return normalizedKey === key.toLowerCase() && normalizedValue === requestedValue;
            });
          });
        }) || null;
      }

      function preloadProductFromUrl() {
        if (!zcInitialProductId || !productSelect) return;

        selectedProduct = getProductById(zcInitialProductId);

        if (!selectedProduct) {
          showNotice('Product was passed to the design studio, but it was not found in the customizer catalog.', 'error');
          return;
        }

        productSelect.value = zcInitialProductId;
        renderVariants();

        if (zcInitialVariationId && variantSelect) {
          variantSelect.value = zcInitialVariationId;
          selectVariant(zcInitialVariationId);
        }

        if (!selectedVariant) {
          const matchedVariant = getVariationAttributeMatch();

          if (matchedVariant && variantSelect) {
            variantSelect.value = matchedVariant.id;
            selectVariant(matchedVariant.id);
          }
        }

        if (zcInitialVariationId && !selectedVariant) {
          showNotice('Product loaded, but the selected variation was not found. Please choose a variant.', 'error');
          return;
        }

        showNotice('Product loaded. Finish your design, then add it to cart.', 'success');
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
        clearNotice();

        const value = String(textInput.value || '').trim();

        if (!value) {
          showNotice('Add some text first.', 'error');
          return;
        }

        const textObject = {
          id: Date.now(),
          type: 'text',
          text: value,
          x: 300,
          y: 300,
          color: textColor.value,
          outline: textOutline.value,
          fontSize: Number(fontSize.value) || 42,
          bold: state.bold,
          italic: state.italic,
          underline: state.underline,
        };

        state.objects.push(textObject);
        selectedObject = textObject;
        clampObjectToPrintableArea(textObject);

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
            const trimmedImageData = getTrimmedImageSource(image);
            const trimmedImage = new Image();

            trimmedImage.onload = () => {
              const initialWidth = Number(imageSize && imageSize.value) || 180;
              const aspectRatio = trimmedImage.naturalWidth && trimmedImage.naturalHeight
                ? trimmedImage.naturalWidth / trimmedImage.naturalHeight
                : 1;
              const imageObject = {
                id: Date.now(),
                type: 'image',
                src: trimmedImageData.src,
                x: 300,
                y: 300,
                width: initialWidth,
                height: initialWidth / aspectRatio,
                aspectRatio,
                image: trimmedImage,
              };

              state.objects.push(imageObject);
              selectedObject = imageObject;
              clampObjectToPrintableArea(imageObject);
              setImageControlsFromObject(imageObject);

              draw();
              updatePrice();
            };

            trimmedImage.src = trimmedImageData.src;
          };

          image.src = reader.result;
        };

        reader.readAsDataURL(file);
      });

      if (imageSize) {
        imageSize.addEventListener('input', () => {
          resizeSelectedImage(imageSize.value);
        });
      }

      root.querySelectorAll('[data-image-size-step]').forEach(button => {
        button.addEventListener('click', () => {
          const step = Number(button.dataset.imageSizeStep) || 0;
          const currentSize = selectedObject && selectedObject.type === 'image'
            ? selectedObject.width
            : Number(imageSize && imageSize.value) || 180;

          resizeSelectedImage(currentSize + step);
        });
      });

      root.querySelectorAll('[data-add-clipart]').forEach(button => {
        button.addEventListener('click', () => {
          const clipartObject = {
            id: Date.now(),
            type: 'clipart',
            text: button.dataset.addClipart,
            x: 300,
            y: 300,
            color: '#111111',
            fontSize: 76,
          };

          state.objects.push(clipartObject);
          selectedObject = clipartObject;
          clampObjectToPrintableArea(clipartObject);

          draw();
          updatePrice();
        });
      });

      root.querySelectorAll('[data-add-shape]').forEach(button => {
        button.addEventListener('click', () => {
          const shapeObject = {
            id: Date.now(),
            type: 'shape',
            shape: button.dataset.addShape,
            x: 300,
            y: 300,
            width: 130,
            height: 130,
            color: '#111111',
          };

          state.objects.push(shapeObject);
          selectedObject = shapeObject;
          clampObjectToPrintableArea(shapeObject);

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
        setImageControlsFromObject(null);
        draw();
        updatePrice();
      });

      root.querySelector('[data-clear-canvas]').addEventListener('click', () => {
        state.objects = [];
        selectedObject = null;
        setImageControlsFromObject(null);
        draw();
        updatePrice();
      });

      function addRoundedRectPath(x, y, width, height, radius) {
        const safeRadius = Math.min(radius, width / 2, height / 2);

        ctx.moveTo(x + safeRadius, y);
        ctx.lineTo(x + width - safeRadius, y);
        ctx.quadraticCurveTo(x + width, y, x + width, y + safeRadius);
        ctx.lineTo(x + width, y + height - safeRadius);
        ctx.quadraticCurveTo(x + width, y + height, x + width - safeRadius, y + height);
        ctx.lineTo(x + safeRadius, y + height);
        ctx.quadraticCurveTo(x, y + height, x, y + height - safeRadius);
        ctx.lineTo(x, y + safeRadius);
        ctx.quadraticCurveTo(x, y, x + safeRadius, y);
      }

      function drawSafeArea() {
        ctx.save();

        ctx.strokeStyle = 'rgba(218, 0, 4, 0.5)';
        ctx.lineWidth = 2;
        ctx.setLineDash([8, 8]);
        ctx.beginPath();
        ctx.arc(300, 300, 255, 0, Math.PI * 2);
        ctx.stroke();

        ctx.restore();
      }

      function getProductPreviewSrc() {
        if (selectedVariant && selectedVariant.image) {
          return selectedVariant.image;
        }

        if (selectedProduct && selectedProduct.image) {
          return selectedProduct.image;
        }

        return '';
      }

      function getProductPreviewLabel() {
        return [
          selectedProduct ? selectedProduct.title : '',
          selectedVariant ? selectedVariant.title : '',
        ].join(' ').toLowerCase();
      }

      function getPrintableArea() {
        return { x: 45, y: 45, width: 510, height: 510 };
      }

      function clampObjectToPrintableArea(object) {
        const bounds = getObjectBounds(object);
        const area = getPrintableArea();

        if (!bounds) return;

        if (bounds.width > area.width) {
          object.x = area.x + area.width / 2;
        } else if (bounds.x < area.x) {
          object.x += area.x - bounds.x;
        } else if (bounds.x + bounds.width > area.x + area.width) {
          object.x -= (bounds.x + bounds.width) - (area.x + area.width);
        }

        const nextBounds = getObjectBounds(object);
        if (!nextBounds) return;

        if (nextBounds.height > area.height) {
          object.y = area.y + area.height / 2;
        } else if (nextBounds.y < area.y) {
          object.y += area.y - nextBounds.y;
        } else if (nextBounds.y + nextBounds.height > area.y + area.height) {
          object.y -= (nextBounds.y + nextBounds.height) - (area.y + area.height);
        }
      }

      function updateProductPreview() {
        const nextSrc = getProductPreviewSrc();

        if (!nextSrc) {
          productPreviewImage = null;
          productPreviewSrc = '';
          draw();
          return;
        }

        if (nextSrc === productPreviewSrc && productPreviewImage) {
          draw();
          return;
        }

        productPreviewSrc = nextSrc;
        productPreviewImage = new Image();
        productPreviewImage.crossOrigin = 'anonymous';
        productPreviewImage.onload = draw;
        productPreviewImage.onerror = () => {
          productPreviewImage = null;
          draw();
        };
        productPreviewImage.src = nextSrc;
      }

      function drawImageContain(image, x, y, width, height) {
        const ratio = Math.min(width / image.naturalWidth, height / image.naturalHeight);
        const drawWidth = image.naturalWidth * ratio;
        const drawHeight = image.naturalHeight * ratio;

        ctx.drawImage(
          image,
          x + (width - drawWidth) / 2,
          y + (height - drawHeight) / 2,
          drawWidth,
          drawHeight
        );
      }

      function drawTshirtFallback() {
        ctx.save();
        ctx.fillStyle = '#ffffff';
        ctx.strokeStyle = 'rgba(0, 0, 0, 0.16)';
        ctx.lineWidth = 3;

        ctx.beginPath();
        ctx.moveTo(205, 130);
        ctx.lineTo(255, 105);
        ctx.quadraticCurveTo(300, 140, 345, 105);
        ctx.lineTo(395, 130);
        ctx.lineTo(470, 220);
        ctx.lineTo(415, 270);
        ctx.lineTo(375, 235);
        ctx.lineTo(375, 495);
        ctx.lineTo(225, 495);
        ctx.lineTo(225, 235);
        ctx.lineTo(185, 270);
        ctx.lineTo(130, 220);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();
        ctx.restore();
      }

      function drawAirpodsFallback() {
        ctx.save();
        ctx.fillStyle = '#ffffff';
        ctx.strokeStyle = 'rgba(0, 0, 0, 0.16)';
        ctx.lineWidth = 3;

        ctx.beginPath();
        addRoundedRectPath(180, 250, 240, 160, 46);
        ctx.fill();
        ctx.stroke();

        ctx.beginPath();
        ctx.moveTo(180, 305);
        ctx.lineTo(420, 305);
        ctx.stroke();

        ctx.beginPath();
        ctx.arc(300, 338, 8, 0, Math.PI * 2);
        ctx.fillStyle = '#f2f2f2';
        ctx.fill();
        ctx.stroke();
        ctx.restore();
      }

      function drawDefaultProductFallback() {
        ctx.save();
        ctx.fillStyle = '#ffffff';
        ctx.strokeStyle = 'rgba(0, 0, 0, 0.16)';
        ctx.lineWidth = 3;
        ctx.beginPath();
        addRoundedRectPath(160, 140, 280, 360, 26);
        ctx.fill();
        ctx.stroke();
        ctx.restore();
      }

      function drawProductBase() {
        ctx.save();
        ctx.fillStyle = '#ffffff';
        ctx.beginPath();
        ctx.arc(300, 300, 298, 0, Math.PI * 2);
        ctx.fill();
        ctx.restore();
      }

      function clipToPrintableArea() {
        const printableArea = getPrintableArea();

        ctx.beginPath();
        addRoundedRectPath(
          printableArea.x,
          printableArea.y,
          printableArea.width,
          printableArea.height,
          18
        );
        ctx.clip();
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

      function getTextLines(object) {
        const lines = String(object.text || '')
          .replace(/\r\n/g, '\n')
          .replace(/\r/g, '\n')
          .split('\n')
          .map(line => line.trim())
          .filter(Boolean);

        return lines.length ? lines : ['Text'];
      }

      function getCanvasFont(object) {
        const fontStyle = object.italic ? 'italic' : 'normal';
        const fontWeight = object.bold ? '800' : '400';
        const fontSizeValue = Number(object.fontSize) || 42;

        return `${fontStyle} ${fontWeight} ${fontSizeValue}px Arial, Helvetica, sans-serif`;
      }

      function drawTextLikeObject(object) {
        const lines = getTextLines(object);
        const fontSizeValue = Number(object.fontSize) || 42;
        const lineHeight = fontSizeValue * 1.18;
        const startY = object.y - ((lines.length - 1) * lineHeight) / 2;

        ctx.font = getCanvasFont(object);
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.lineJoin = 'round';

        lines.forEach((line, index) => {
          const lineY = startY + index * lineHeight;

          if (object.outline) {
            ctx.strokeStyle = object.outline;
            ctx.lineWidth = 4;
            ctx.strokeText(line, object.x, lineY);
          }

          ctx.fillStyle = object.color || '#111111';
          ctx.fillText(line, object.x, lineY);

          if (object.underline) {
            const metrics = ctx.measureText(line);
            const width = metrics.width;
            ctx.beginPath();
            ctx.moveTo(object.x - width / 2, lineY + fontSizeValue / 2);
            ctx.lineTo(object.x + width / 2, lineY + fontSizeValue / 2);
            ctx.strokeStyle = object.color || '#111111';
            ctx.lineWidth = 2;
            ctx.stroke();
          }
        });
      }

      function drawRawObject(object) {
        if (object.type === 'text' || object.type === 'clipart') {
          drawTextLikeObject(object);
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
      }

      function drawPrintedObject(object) {
        ctx.save();
        clipToPrintableArea();
        ctx.globalAlpha = object.type === 'image' ? 1 : 0.96;
        ctx.globalCompositeOperation = object.type === 'image' ? 'source-over' : 'multiply';
        ctx.shadowColor = 'rgba(0, 0, 0, 0.16)';
        ctx.shadowBlur = 0.8;
        ctx.shadowOffsetX = 0;
        ctx.shadowOffsetY = 0.8;
        drawRawObject(object);
        ctx.restore();
      }

      function drawFabricTextureOverDesign(object) {
        const bounds = getObjectBounds(object);
        if (!bounds) return;

        ctx.save();
        clipToPrintableArea();
        ctx.globalCompositeOperation = 'multiply';
        ctx.globalAlpha = 0.12;
        ctx.strokeStyle = '#6f604d';
        ctx.lineWidth = 0.7;

        const startX = Math.max(getPrintableArea().x, bounds.x) - 20;
        const endX = Math.min(getPrintableArea().x + getPrintableArea().width, bounds.x + bounds.width) + 20;
        const startY = Math.max(getPrintableArea().y, bounds.y) - 20;
        const endY = Math.min(getPrintableArea().y + getPrintableArea().height, bounds.y + bounds.height) + 20;

        for (let x = startX; x <= endX; x += 5) {
          ctx.beginPath();
          ctx.moveTo(x, startY);
          ctx.lineTo(x + 28, endY);
          ctx.stroke();
        }

        ctx.restore();
      }

      function drawObject(object) {
        drawPrintedObject(object);
        drawFabricTextureOverDesign(object);
        drawSelection(object);
      }

      function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        ctx.save();
        ctx.fillStyle = '#f8f8f8';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.restore();

        drawProductBase();
        drawSafeArea();

        state.objects.forEach(drawObject);
      }

      function getObjectBounds(object) {
        if (object.type === 'text' || object.type === 'clipart') {
          const lines = getTextLines(object);
          const fontSizeValue = Number(object.fontSize) || 42;
          const lineHeight = fontSizeValue * 1.18;
          let width = 30;

          ctx.save();
          ctx.font = getCanvasFont(object);
          lines.forEach(line => {
            width = Math.max(width, ctx.measureText(line).width);
          });
          ctx.restore();

          return {
            x: object.x - width / 2,
            y: object.y - (lines.length * lineHeight) / 2,
            width,
            height: lines.length * lineHeight,
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
        setImageControlsFromObject(selectedObject);

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
        clampObjectToPrintableArea(selectedObject);

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
        setImageControlsFromObject(selectedObject);

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
        clampObjectToPrintableArea(selectedObject);

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
          setImageControlsFromObject(null);
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
        formData.append('variation_id', selectedProduct.type === 'variable' ? selectedVariant.id : '');
        formData.append('quantity', zcInitialQuantity || '1');
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
      preloadProductFromUrl();
    })();
  </script>
</section>
