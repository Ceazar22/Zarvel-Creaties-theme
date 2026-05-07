<?php
defined('ABSPATH') || exit;

$path = function_exists('zarvel_get_current_path') ? zarvel_get_current_path() : '';

$pages = array(
    'about' => array(
        'eyebrow' => 'Company',
        'title' => 'About Printly',
        'intro' => 'We make custom products feel simple, polished, and personal from first idea to final delivery.',
        'sections' => array(
            array('title' => 'What we do', 'body' => 'Printly helps customers create apparel, accessories, and everyday products with custom artwork, text, and brand designs.'),
            array('title' => 'How we work', 'body' => 'Each order is produced on demand, checked for print placement, and prepared with practical support if something needs attention.'),
            array('title' => 'Why it matters', 'body' => 'Custom products should feel easy to make and good enough to wear, gift, sell, or use every day.'),
        ),
    ),
    'shipping-policy' => array(
        'eyebrow' => 'Support',
        'title' => 'Shipping Policy',
        'intro' => 'Clear shipping expectations for custom, made-on-demand products.',
        'sections' => array(
            array('title' => 'Processing', 'body' => 'Most custom orders need production time before shipment. Processing time can vary by product type, order size, and artwork review.'),
            array('title' => 'Delivery', 'body' => 'Shipping rates and timelines are calculated at checkout when available. Delivery estimates start after production is complete.'),
            array('title' => 'Tracking', 'body' => 'When tracking is available, it will be sent to the customer email used during checkout.'),
        ),
    ),
    'refund-policy' => array(
        'eyebrow' => 'Support',
        'title' => 'Refund Policy',
        'intro' => 'Because custom products are made for each order, refunds are handled carefully and fairly.',
        'sections' => array(
            array('title' => 'Custom orders', 'body' => 'Personalized items usually cannot be returned for buyer preference, incorrect size selection, or design choices approved by the customer.'),
            array('title' => 'Damaged or incorrect items', 'body' => 'If an item arrives damaged, misprinted, or different from the confirmed order, contact support with photos and order details.'),
            array('title' => 'Review window', 'body' => 'Please report order issues as soon as possible so the team can review the case and provide the best next step.'),
        ),
    ),
    'faqs' => array(
        'eyebrow' => 'Support',
        'title' => 'FAQs',
        'intro' => 'Quick answers for custom product orders.',
        'sections' => array(
            array('title' => 'Can I upload my own design?', 'body' => 'Yes. Use the design studio to add images, text, shapes, and product-specific customization details.'),
            array('title' => 'Can I edit after ordering?', 'body' => 'Contact support immediately. Once production starts, design changes may no longer be possible.'),
            array('title' => 'Do colors match exactly?', 'body' => 'Screen colors can differ from printed output. Product material, ink, and lighting can affect the final appearance.'),
        ),
    ),
    'size-guide' => array(
        'eyebrow' => 'Support',
        'title' => 'Size Guide',
        'intro' => 'Use product sizing carefully before placing a made-on-demand order.',
        'sections' => array(
            array('title' => 'Apparel fit', 'body' => 'Check each product description for fit notes. When between sizes, compare a garment you already own with the product measurements.'),
            array('title' => 'Print area', 'body' => 'The red boundary in the customizer shows the printable region. Artwork must stay inside that area.'),
            array('title' => 'Need help?', 'body' => 'Send support the product name and your measurements if you need guidance before ordering.'),
        ),
    ),
    'track-order' => array(
        'eyebrow' => 'Support',
        'title' => 'Track Your Order',
        'intro' => 'Use your confirmation email and tracking details to follow your shipment.',
        'sections' => array(
            array('title' => 'Order confirmation', 'body' => 'After checkout, you should receive an order confirmation at the email address provided.'),
            array('title' => 'Tracking updates', 'body' => 'Tracking is sent once the order has completed production and the carrier has received shipment details.'),
            array('title' => 'Missing tracking', 'body' => 'If tracking has not arrived after the expected production window, contact support with your order number.'),
        ),
    ),
    'our-process' => array(
        'eyebrow' => 'Company',
        'title' => 'Our Process',
        'intro' => 'A clean custom order flow from product selection to delivery.',
        'sections' => array(
            array('title' => 'Choose', 'body' => 'Pick a product, size, color, and variant from the shop.'),
            array('title' => 'Customize', 'body' => 'Open the design studio, place your artwork, and keep it inside the printable boundary.'),
            array('title' => 'Produce', 'body' => 'After checkout, the order moves into made-on-demand production and fulfillment.'),
        ),
    ),
    'careers' => array(
        'eyebrow' => 'Company',
        'title' => 'Careers',
        'intro' => 'We are building a practical, creative custom product experience.',
        'sections' => array(
            array('title' => 'Current openings', 'body' => 'There are no public openings listed right now.'),
            array('title' => 'Creative operators', 'body' => 'We value people who care about detail, customer experience, production quality, and good systems.'),
            array('title' => 'Get in touch', 'body' => 'For future opportunities, send a short introduction through the contact page.'),
        ),
    ),
    'blog' => array(
        'eyebrow' => 'Company',
        'title' => 'Blog',
        'intro' => 'Ideas for custom apparel, gifts, small brands, and product design.',
        'sections' => array(
            array('title' => 'Design tips', 'body' => 'Keep artwork high contrast, readable, and sized for the product print area.'),
            array('title' => 'Product ideas', 'body' => 'T-shirts, hoodies, mugs, caps, and cases are useful starting points for personal or brand merch.'),
            array('title' => 'Coming soon', 'body' => 'More guides and behind-the-scenes production notes will be added here.'),
        ),
    ),
    'terms-of-service' => array(
        'eyebrow' => 'Legal',
        'title' => 'Terms of Service',
        'intro' => 'Basic terms for using this website and placing custom product orders.',
        'sections' => array(
            array('title' => 'Customer content', 'body' => 'Customers are responsible for having the rights to upload and print the artwork, text, logos, or images they submit.'),
            array('title' => 'Order accuracy', 'body' => 'Please review product options, quantities, shipping details, and design placement before checkout.'),
            array('title' => 'Service changes', 'body' => 'Product availability, pricing, and fulfillment timelines may change as supplier and production conditions change.'),
        ),
    ),
    'privacy-policy' => array(
        'eyebrow' => 'Legal',
        'title' => 'Privacy Policy',
        'intro' => 'A plain-language overview of how customer information is used for orders and support.',
        'sections' => array(
            array('title' => 'Information collected', 'body' => 'Checkout details, contact information, order contents, and uploaded design data may be used to process custom orders.'),
            array('title' => 'How it is used', 'body' => 'Information is used for fulfillment, payment processing, customer support, and order communication.'),
            array('title' => 'Support requests', 'body' => 'When contacting support, include only the details needed to resolve your issue.'),
        ),
    ),
);

$page = isset($pages[$path]) ? $pages[$path] : $pages['about'];

get_header();
?>

<main class="zc-info-page">
  <section class="zc-info-hero">
    <div class="zc-info-container">
      <p><?php echo esc_html($page['eyebrow']); ?></p>
      <h1><?php echo esc_html($page['title']); ?></h1>
      <span><?php echo esc_html($page['intro']); ?></span>
    </div>
  </section>

  <section class="zc-info-content">
    <div class="zc-info-container zc-info-grid">
      <?php foreach ($page['sections'] as $section) : ?>
        <article class="zc-info-card">
          <h2><?php echo esc_html($section['title']); ?></h2>
          <p><?php echo esc_html($section['body']); ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </section>
</main>

<style>
.zc-info-page {
  background: #f7f7f7;
  color: #050505;
}

.zc-info-container {
  width: min(100% - 44px, 1160px);
  margin: 0 auto;
}

.zc-info-hero {
  background: #050505;
  color: #ffffff;
  padding: 62px 0 72px;
}

.zc-info-hero p {
  margin: 0 0 12px;
  color: #ff5b1a;
  font-size: 13px;
  font-weight: 950;
  text-transform: uppercase;
  letter-spacing: 0.8px;
}

.zc-info-hero h1 {
  margin: 0;
  max-width: 760px;
  font-size: clamp(40px, 7vw, 84px);
  line-height: 0.94;
  font-weight: 950;
}

.zc-info-hero span {
  display: block;
  margin-top: 20px;
  max-width: 580px;
  color: rgba(255, 255, 255, 0.76);
  font-size: 17px;
  line-height: 1.55;
  font-weight: 500;
}

.zc-info-content {
  padding: 34px 0 78px;
}

.zc-info-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 18px;
}

.zc-info-card {
  min-height: 240px;
  padding: 30px;
  border: 1px solid #e8e8e8;
  border-radius: 14px;
  background: #ffffff;
  box-shadow: 0 18px 48px rgba(0, 0, 0, 0.05);
}

.zc-info-card h2 {
  margin: 0 0 12px;
  font-size: 24px;
  line-height: 1.05;
  font-weight: 950;
}

.zc-info-card p {
  margin: 0;
  color: #5f5f5f;
  font-size: 15px;
  line-height: 1.65;
  font-weight: 500;
}

@media (max-width: 840px) {
  .zc-info-grid {
    grid-template-columns: 1fr;
  }

  .zc-info-card {
    min-height: 0;
  }
}

@media (max-width: 640px) {
  .zc-info-container {
    width: min(100% - 28px, 1160px);
  }

  .zc-info-card {
    padding: 24px;
  }
}
</style>

<?php
get_footer();
