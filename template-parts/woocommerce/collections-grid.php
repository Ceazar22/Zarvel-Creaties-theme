<?php
defined('ABSPATH') || exit;

$zc_shop_url = home_url('/shop/');
$zc_customize_url = home_url('/customize/');

$zc_sort = isset($_GET['sort']) ? sanitize_text_field(wp_unslash($_GET['sort'])) : 'popular';
$zc_page = isset($_GET['zc_page']) ? max(1, absint($_GET['zc_page'])) : 1;
$zc_per_page = 12;

$zc_categories = get_terms([
  'taxonomy'   => 'product_cat',
  'hide_empty' => false,
]);

if (is_wp_error($zc_categories) || empty($zc_categories)) {
  return;
}

$zc_categories = array_filter($zc_categories, function ($category) {
  return !empty($category->slug) && $category->slug !== 'uncategorized';
});

if (empty($zc_categories)) {
  return;
}

if (!function_exists('zc_collections_get_first_product_image')) {
  function zc_collections_get_first_product_image($category) {
    if (!$category || empty($category->term_id)) {
      return '';
    }

    $product_ids = get_posts([
      'post_type'      => 'product',
      'post_status'    => 'publish',
      'posts_per_page' => 1,
      'fields'         => 'ids',
      'tax_query'      => [
        [
          'taxonomy'         => 'product_cat',
          'field'            => 'term_id',
          'terms'            => [$category->term_id],
          'include_children' => true,
        ],
      ],
    ]);

    if (empty($product_ids)) {
      return '';
    }

    $product = wc_get_product($product_ids[0]);

    if (!$product || !$product->get_image_id()) {
      return '';
    }

    return wp_get_attachment_image_url($product->get_image_id(), 'large');
  }
}

if (!function_exists('zc_collections_get_category_image')) {
  function zc_collections_get_category_image($category) {
    if (!$category || empty($category->term_id)) {
      return wc_placeholder_img_src('large');
    }

    $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);

    if ($thumbnail_id) {
      $image = wp_get_attachment_image_url($thumbnail_id, 'large');

      if ($image) {
        return $image;
      }
    }

    $first_product_image = zc_collections_get_first_product_image($category);

    if ($first_product_image) {
      return $first_product_image;
    }

    return wc_placeholder_img_src('large');
  }
}

$zc_categories = array_values($zc_categories);

switch ($zc_sort) {
  case 'name':
    usort($zc_categories, function ($a, $b) {
      return strcmp($a->name, $b->name);
    });
    break;

  case 'items-low':
    usort($zc_categories, function ($a, $b) {
      return (int) $a->count <=> (int) $b->count;
    });
    break;

  case 'items-high':
  case 'popular':
  default:
    usort($zc_categories, function ($a, $b) {
      return (int) $b->count <=> (int) $a->count;
    });
    break;
}

$zc_total_categories = count($zc_categories);
$zc_total_pages = max(1, (int) ceil($zc_total_categories / $zc_per_page));

if ($zc_page > $zc_total_pages) {
  $zc_page = $zc_total_pages;
}

$zc_offset = ($zc_page - 1) * $zc_per_page;
$zc_visible_categories = array_slice($zc_categories, $zc_offset, $zc_per_page);

$zc_showing_start = $zc_total_categories > 0 ? $zc_offset + 1 : 0;
$zc_showing_end = min($zc_offset + $zc_per_page, $zc_total_categories);
?>

<section class="zc-collections-grid-section">
  <div class="zc-collections-grid-container">

    <aside class="zc-collections-sidebar">

      <div class="zc-collections-sidebar__title">
        Browse Collections
      </div>

      <ul class="zc-collections-sidebar__list">
        <li>
          <a href="<?php echo esc_url($zc_shop_url); ?>" class="is-active">
            <span>All Collections</span>
            <em><?php echo esc_html($zc_total_categories); ?></em>
          </a>
        </li>

        <?php foreach ($zc_categories as $zc_sidebar_category) : ?>
          <?php
          $zc_sidebar_link = get_term_link($zc_sidebar_category);

          if (is_wp_error($zc_sidebar_link)) {
            continue;
          }
          ?>

          <li>
            <a href="<?php echo esc_url($zc_sidebar_link); ?>">
              <span><?php echo esc_html($zc_sidebar_category->name); ?></span>
              <em><?php echo esc_html($zc_sidebar_category->count); ?></em>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>

      <div class="zc-collections-cta">
        <div class="zc-collections-cta__icon">
          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 3C15.2 5.7 17.1 8.7 17.1 12.1C17.1 15.4 14.8 18 12 18C9.2 18 6.9 15.4 6.9 12.1C6.9 8.7 8.8 5.7 12 3Z" stroke="currentColor" stroke-width="1.8"/>
            <path d="M12 18V21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            <path d="M8.5 21H15.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
          </svg>
        </div>

        <h3>Can’t Find What You’re Looking For?</h3>

        <p>
          Create your own custom print that’s 100% you.
        </p>

        <a href="<?php echo esc_url($zc_customize_url); ?>">
          Fill Out Form
          <span>+</span>
        </a>
      </div>

    </aside>

    <div class="zc-collections-main">

      <div class="zc-collections-toolbar">
        <p>
          Showing <?php echo esc_html($zc_showing_start); ?>–<?php echo esc_html($zc_showing_end); ?> of <?php echo esc_html($zc_total_categories); ?> collections
        </p>

        <form method="get" class="zc-collections-sort">
          <select name="sort" onchange="this.form.submit()">
            <option value="popular" <?php selected($zc_sort, 'popular'); ?>>Sort by: Popular</option>
            <option value="name" <?php selected($zc_sort, 'name'); ?>>Sort by: Name</option>
            <option value="items-high" <?php selected($zc_sort, 'items-high'); ?>>Sort by: Most Items</option>
            <option value="items-low" <?php selected($zc_sort, 'items-low'); ?>>Sort by: Fewest Items</option>
          </select>
        </form>
      </div>

      <div class="zc-collections-card-grid">

        <?php foreach ($zc_visible_categories as $zc_category) : ?>
          <?php
          $zc_category_link = get_term_link($zc_category);

          if (is_wp_error($zc_category_link)) {
            $zc_category_link = $zc_shop_url;
          }

          $zc_category_image = zc_collections_get_category_image($zc_category);
          ?>

          <article class="zc-collection-card">
            <a href="<?php echo esc_url($zc_category_link); ?>" class="zc-collection-card__image-wrap">
              <img
                src="<?php echo esc_url($zc_category_image); ?>"
                alt="<?php echo esc_attr($zc_category->name); ?>"
                class="zc-collection-card__image"
                loading="lazy"
              >
            </a>

            <div class="zc-collection-card__content">
              <div>
                <h3>
                  <a href="<?php echo esc_url($zc_category_link); ?>">
                    <?php echo esc_html($zc_category->name); ?>
                  </a>
                </h3>

                <p>
                  <?php echo esc_html($zc_category->count); ?> item<?php echo (int) $zc_category->count === 1 ? '' : 's'; ?>
                </p>
              </div>

              <a href="<?php echo esc_url($zc_category_link); ?>" class="zc-collection-card__link">
                Shop Now
                <span>+</span>
              </a>
            </div>
          </article>
        <?php endforeach; ?>

      </div>

      <?php if ($zc_total_pages > 1) : ?>
        <nav class="zc-collections-pagination" aria-label="Collections pagination">

          <?php
          $zc_base_args = $_GET;
          unset($zc_base_args['zc_page']);
          ?>

          <?php if ($zc_page > 1) : ?>
            <a href="<?php echo esc_url(add_query_arg(array_merge($zc_base_args, ['zc_page' => $zc_page - 1]), $zc_shop_url)); ?>" class="zc-page-arrow">
              ‹
            </a>
          <?php endif; ?>

          <?php for ($i = 1; $i <= $zc_total_pages; $i++) : ?>
            <a
              href="<?php echo esc_url(add_query_arg(array_merge($zc_base_args, ['zc_page' => $i]), $zc_shop_url)); ?>"
              class="<?php echo $i === $zc_page ? 'is-active' : ''; ?>"
            >
              <?php echo esc_html($i); ?>
            </a>
          <?php endfor; ?>

          <?php if ($zc_page < $zc_total_pages) : ?>
            <a href="<?php echo esc_url(add_query_arg(array_merge($zc_base_args, ['zc_page' => $zc_page + 1]), $zc_shop_url)); ?>" class="zc-page-arrow">
              ›
            </a>
          <?php endif; ?>

        </nav>
      <?php endif; ?>

    </div>

  </div>
</section>

<style>
.zc-collections-grid-section {
  padding: 40px 0 90px;
  background: #ffffff;
}

.zc-collections-grid-container {
  width: min(100% - 40px, 1280px);
  margin: 0 auto;
  display: grid;
  grid-template-columns: 240px 1fr;
  gap: 34px;
  align-items: start;
}

.zc-collections-sidebar {
  position: sticky;
  top: 120px;
}

.zc-collections-sidebar__title {
  margin-bottom: 22px;
  color: #111111;
  font-size: 13px;
  line-height: 1;
  font-weight: 950;
  text-transform: uppercase;
}

.zc-collections-sidebar__list {
  list-style: none;
  padding: 0;
  margin: 0 0 28px;
}

.zc-collections-sidebar__list li {
  margin-bottom: 10px;
}

.zc-collections-sidebar__list a {
  min-height: 32px;
  color: #222222;
  text-decoration: none;
  font-size: 13px;
  font-weight: 800;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  transition: 0.2s ease;
}

.zc-collections-sidebar__list a:hover,
.zc-collections-sidebar__list a.is-active {
  color: #ff5b1a;
}

.zc-collections-sidebar__list em {
  min-width: 32px;
  height: 24px;
  padding: 0 8px;
  border-radius: 999px;
  background: #f3f3f3;
  color: #777777;
  font-size: 11px;
  font-style: normal;
  font-weight: 900;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.zc-collections-sidebar__list a:hover em,
.zc-collections-sidebar__list a.is-active em {
  background: #fff1eb;
  color: #ff5b1a;
}

.zc-collections-cta {
  padding: 24px;
  border-radius: 14px;
  background: linear-gradient(135deg, #fff8f4 0%, #ffffff 100%);
  border: 1px solid #eeeeee;
}

.zc-collections-cta__icon {
  width: 42px;
  height: 42px;
  margin-bottom: 16px;
  color: #111111;
}

.zc-collections-cta__icon svg {
  width: 100%;
  height: 100%;
}

.zc-collections-cta h3 {
  margin: 0 0 10px;
  color: #111111;
  font-size: 22px;
  line-height: 1.05;
  font-weight: 950;
  text-transform: uppercase;
}

.zc-collections-cta p {
  margin: 0 0 18px;
  color: #555555;
  font-size: 13px;
  line-height: 1.5;
  font-weight: 700;
}

.zc-collections-cta a {
  min-height: 42px;
  padding: 0 16px;
  border-radius: 7px;
  background: #111111;
  color: #ffffff;
  text-decoration: none;
  font-size: 11px;
  font-weight: 950;
  text-transform: uppercase;
  display: inline-flex;
  align-items: center;
  gap: 12px;
  transition: 0.2s ease;
}

.zc-collections-cta a:hover {
  background: #ff5b1a;
}

.zc-collections-main {
  min-width: 0;
}

.zc-collections-toolbar {
  min-height: 40px;
  margin-bottom: 22px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
}

.zc-collections-toolbar p {
  margin: 0;
  color: #555555;
  font-size: 13px;
  font-weight: 800;
}

.zc-collections-sort select {
  height: 38px;
  min-width: 180px;
  padding: 0 36px 0 14px;
  border: 1px solid #dddddd;
  border-radius: 8px;
  background: #ffffff;
  color: #111111;
  font-size: 12px;
  font-weight: 850;
  outline: none;
}

.zc-collections-card-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 24px;
}

.zc-collection-card {
  min-width: 0;
  border-radius: 12px;
  background: #ffffff;
  border: 1px solid #eeeeee;
  overflow: hidden;
  transition: 0.25s ease;
}

.zc-collection-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 16px 28px rgba(0, 0, 0, 0.08);
}

.zc-collection-card__image-wrap {
  padding: 14px;
  background: #f7f7f7;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.zc-collection-card__image {
  width: 100%;
  height: 100%;
  object-fit: contain;
  display: block;
  transition: 0.25s ease;
}

.zc-collection-card:hover .zc-collection-card__image {
  transform: scale(1.04);
}

.zc-collection-card__content {
  padding: 15px 16px 16px;
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 14px;
}

.zc-collection-card__content h3 {
  margin: 0 0 6px;
  font-size: 15px;
  line-height: 1.2;
  font-weight: 950;
}

.zc-collection-card__content h3 a {
  color: #111111;
  text-decoration: none;
}

.zc-collection-card__content h3 a:hover {
  color: #ff5b1a;
}

.zc-collection-card__content p {
  margin: 0;
  color: #777777;
  font-size: 12px;
  font-weight: 800;
}

.zc-collection-card__link {
  color: #ff5b1a;
  font-size: 10px;
  line-height: 1;
  font-weight: 950;
  text-transform: uppercase;
  text-decoration: none;
  white-space: nowrap;
  display: inline-flex;
  align-items: center;
  gap: 7px;
}

.zc-collection-card__link:hover {
  color: #111111;
}

.zc-collections-pagination {
  margin-top: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
}

.zc-collections-pagination a {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #ffffff;
  border: 1px solid #eeeeee;
  color: #111111;
  font-size: 12px;
  font-weight: 950;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: 0.2s ease;
}

.zc-collections-pagination a:hover,
.zc-collections-pagination a.is-active {
  background: #111111;
  color: #ffffff;
}

.zc-collections-pagination .zc-page-arrow {
  color: #111111;
}

@media screen and (max-width: 1024px) {
  .zc-collections-grid-container {
    grid-template-columns: 210px 1fr;
    gap: 24px;
  }

  .zc-collections-card-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }


}

@media screen and (max-width: 768px) {
  .zc-collections-grid-section {
    padding: 34px 0 70px;
  }

  .zc-collections-grid-container {
    width: min(100% - 30px, 1280px);
    grid-template-columns: 1fr;
  }

  .zc-collections-sidebar {
    position: static;
  }

  .zc-collections-sidebar__list {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px 18px;
  }

  .zc-collections-sidebar__list li {
    margin-bottom: 0;
  }

  .zc-collections-toolbar {
    flex-direction: column;
    align-items: flex-start;
  }

  .zc-collections-sort,
  .zc-collections-sort select {
    width: 100%;
  }
}

@media screen and (max-width: 520px) {
  .zc-collections-sidebar__list {
    grid-template-columns: 1fr;
  }

  .zc-collections-card-grid {
    grid-template-columns: 1fr;
  }

}
</style>
