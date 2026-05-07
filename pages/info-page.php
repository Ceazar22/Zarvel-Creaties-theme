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
        'intro' => 'Free shipping is available for United States orders. International shipping is calculated at checkout using Printful-supported rates when available.',
        'sections' => array(
            array('title' => 'Free USA shipping', 'body' => 'Orders shipped to addresses in the United States receive free shipping at checkout. Production time still applies before the order ships.'),
            array('title' => 'International shipping', 'body' => 'For addresses outside the United States, shipping is calculated at checkout based on destination, product type, package size, and available Printful shipping methods.'),
            array('title' => 'Tracking', 'body' => 'When tracking is available, it will be sent to the customer email used during checkout after the order has been fulfilled and handed to the carrier.'),
        ),
    ),
    'return-policy' => array(
        'eyebrow' => 'Support',
        'title' => 'Return Policy',
        'intro' => 'Our products are custom made on demand, so returns are handled differently from regular retail items.',
        'sections' => array(
            array('title' => 'Custom orders', 'body' => 'Because each product is made for the customer, we do not accept returns or exchanges for wrong size, wrong color, buyer preference, or change of mind.'),
            array('title' => 'Damaged or defective items', 'body' => 'If your item arrives damaged, misprinted, defective, or affected by a manufacturing issue, contact us with your order number and clear photos within 30 days of delivery.'),
            array('title' => 'Resolution', 'body' => 'Approved quality claims may be resolved with a replacement, reprint, or refund depending on the issue and Printful fulfillment review. You usually do not need to return the damaged product.'),
        ),
    ),
    'refund-policy' => array(
        'eyebrow' => 'Support',
        'title' => 'Return Policy',
        'intro' => 'Our products are custom made on demand, so returns are handled differently from regular retail items.',
        'sections' => array(
            array('title' => 'Custom orders', 'body' => 'Because each product is made for the customer, we do not accept returns or exchanges for wrong size, wrong color, buyer preference, or change of mind.'),
            array('title' => 'Damaged or defective items', 'body' => 'If your item arrives damaged, misprinted, defective, or affected by a manufacturing issue, contact us with your order number and clear photos within 30 days of delivery.'),
            array('title' => 'Resolution', 'body' => 'Approved quality claims may be resolved with a replacement, reprint, or refund depending on the issue and Printful fulfillment review. You usually do not need to return the damaged product.'),
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
        'title' => 'Terms and Conditions',
        'intro' => 'These terms explain how custom product, design, and website service orders work through Zarvel Creatives.',
        'sections' => array(
            array('title' => 'Customer content', 'body' => 'Customers are responsible for having the rights to upload, print, publish, or use the artwork, text, logos, images, and brand assets they submit.'),
            array('title' => 'Custom product orders', 'body' => 'Please review product options, quantities, sizes, colors, shipping address, and design placement before checkout. Made-on-demand products may not be returnable for preference or selection mistakes.'),
            array('title' => 'Services and availability', 'body' => 'Product availability, pricing, shipping methods, production timelines, design services, Shopify work, WordPress work, and WooCommerce work may change based on supplier, platform, and project requirements.'),
        ),
    ),
    'privacy-policy' => array(
        'eyebrow' => 'Legal',
        'title' => 'Privacy Policy',
        'intro' => 'A plain-language overview of how customer information is used for orders, fulfillment, support, and service requests.',
        'sections' => array(
            array('title' => 'Information collected', 'body' => 'We may collect checkout details, contact information, shipping address, order contents, uploaded design files, project notes, and messages submitted through forms.'),
            array('title' => 'How it is used', 'body' => 'Information is used to process payments, fulfill orders, calculate shipping, send order updates, provide customer support, and complete requested design or website services.'),
            array('title' => 'Service providers', 'body' => 'Order and fulfillment information may be shared with service providers such as WooCommerce, payment processors, Printful, shipping carriers, email providers, Shopify, or WordPress-related tools when needed to deliver the service.'),
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
