<?php
defined('ABSPATH') || exit;

get_header();
?>

<main class="zc-customize-page">
  <?php get_template_part('template-parts/customize-hero'); ?>
  <?php get_template_part('template-parts/customize-form'); ?>
  <?php get_template_part('template-parts/home-how-it-works'); ?>
  <?php get_template_part('template-parts/customize-faq'); ?>
</main>

<?php
get_footer();