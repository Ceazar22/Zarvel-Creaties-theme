<?php get_header(); ?>

<main class="zc-contact-page">
  <?php get_template_part('template-parts/contact-hero'); ?>
  <?php
  $zc_contact_service = isset($_GET['service'])
    ? sanitize_key(wp_unslash($_GET['service']))
    : '';

  if ($zc_contact_service === 'website') {
    get_template_part('template-parts/website-request-form');
  } else {
    get_template_part('template-parts/customize-form');
  }
  ?>
  <?php get_template_part('template-parts/home-how-it-works'); ?>
  <?php get_template_part('template-parts/testimonials'); ?>
</main>

<?php get_footer(); ?>
