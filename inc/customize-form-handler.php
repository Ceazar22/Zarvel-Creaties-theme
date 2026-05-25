<?php
defined('ABSPATH') || exit;

if (!defined('ZARVEL_DESIGN_REQUEST_TYPE')) {
    define('ZARVEL_DESIGN_REQUEST_TYPE', 'zarvel_design_req');
}

/**
 * Save a local copy so LocalWP submissions still work when wp_mail is not configured.
 */
function zarvel_save_customize_form_submission($subject, $message, $attachments = array()) {
    $upload_dir = wp_upload_dir();

    if (!empty($upload_dir['error']) || empty($upload_dir['basedir'])) {
        return false;
    }

    $request_dir = trailingslashit($upload_dir['basedir']) . 'zarvel-design-requests';

    if (!wp_mkdir_p($request_dir)) {
        return false;
    }

    $filename = sanitize_file_name(gmdate('Y-m-d-His') . '-' . $subject . '.txt');
    $file_path = trailingslashit($request_dir) . $filename;

    $local_message = $message;

    if (!empty($attachments)) {
        $local_message .= "\nUploaded Files\n";
        $local_message .= "--------------\n";

        foreach ($attachments as $attachment) {
            $local_message .= $attachment . "\n";
        }
    }

    return (bool) file_put_contents($file_path, $local_message);
}

/**
 * Keep design requests visible in wp-admin even when server email is blocked.
 */
function zarvel_register_design_request_post_type() {
    register_post_type(ZARVEL_DESIGN_REQUEST_TYPE, array(
        'labels' => array(
            'name'          => 'Design Requests',
            'singular_name' => 'Design Request',
            'menu_name'     => 'Design Requests',
        ),
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-feedback',
        'supports'     => array('title', 'editor'),
        'capability_type' => 'post',
    ));
}
add_action('init', 'zarvel_register_design_request_post_type');

function zarvel_design_request_payment_badge($request_id) {
    $order_id = absint(get_post_meta($request_id, '_design_deposit_order_id', true));

    if (!$order_id || !function_exists('wc_get_order')) {
        return array(
            'label' => 'Not Paid',
            'class' => 'is-unpaid',
            'order' => null,
        );
    }

    $order = wc_get_order($order_id);

    if (!$order) {
        return array(
            'label' => 'Not Paid',
            'class' => 'is-unpaid',
            'order' => null,
        );
    }

    if ($order->is_paid()) {
        return array(
            'label' => 'Paid',
            'class' => 'is-paid',
            'order' => $order,
        );
    }

    return array(
        'label' => 'Awaiting Payment',
        'class' => 'is-pending',
        'order' => $order,
    );
}

add_filter('manage_' . ZARVEL_DESIGN_REQUEST_TYPE . '_posts_columns', function ($columns) {
    $payment_columns = array();

    foreach ($columns as $key => $label) {
        $payment_columns[$key] = $label;

        if ($key === 'title') {
            $payment_columns['zarvel_deposit_payment'] = 'Deposit Payment';
        }
    }

    $payment_columns['zarvel_remove_request'] = 'Remove';

    return $payment_columns;
});

add_action('manage_' . ZARVEL_DESIGN_REQUEST_TYPE . '_posts_custom_column', function ($column, $request_id) {
    if ($column === 'zarvel_remove_request') {
        $remove_url = get_delete_post_link($request_id);

        if ($remove_url && current_user_can('delete_post', $request_id)) {
            ?>
            <a class="button-link-delete zarvel-design-remove" href="<?php echo esc_url($remove_url); ?>">
                <?php esc_html_e('Remove', 'zarvel-creative'); ?>
            </a>
            <?php
        }

        return;
    }

    if ($column !== 'zarvel_deposit_payment') {
        return;
    }

    $payment = zarvel_design_request_payment_badge($request_id);
    $order = $payment['order'];
    ?>
    <span class="zarvel-design-payment <?php echo esc_attr($payment['class']); ?>">
        <?php echo esc_html($payment['label']); ?>
    </span>
    <?php if ($order && current_user_can('edit_shop_orders')) : ?>
        <div>
            <a href="<?php echo esc_url($order->get_edit_order_url()); ?>">
                <?php echo esc_html('#' . $order->get_order_number()); ?>
            </a>
        </div>
    <?php endif; ?>
    <?php
}, 10, 2);

add_action('admin_head-edit.php', function () {
    $screen = get_current_screen();

    if (!$screen || $screen->post_type !== ZARVEL_DESIGN_REQUEST_TYPE) {
        return;
    }
    ?>
    <style>
        .column-zarvel_deposit_payment { width: 180px; }
        .column-zarvel_remove_request { width: 100px; }
        .zarvel-design-payment { display: inline-flex; min-height: 24px; align-items: center; padding: 0 9px; border-radius: 999px; background: #f0f0f1; color: #1d2327; font-weight: 600; }
        .zarvel-design-payment.is-paid { background: #dff5e5; color: #075b24; }
        .zarvel-design-payment.is-pending { background: #fff1cf; color: #6f4b00; }
        .zarvel-design-payment.is-unpaid { background: #fde2e2; color: #8a1111; }
        .zarvel-design-remove { font-weight: 600; }
    </style>
    <?php
});

function zarvel_save_customize_form_admin_post($subject, $message, $fields = array(), $attachments = array()) {
    $post_id = wp_insert_post(array(
        'post_type'    => ZARVEL_DESIGN_REQUEST_TYPE,
        'post_status'  => 'private',
        'post_title'   => $subject,
        'post_content' => $message,
    ), true);

    if (is_wp_error($post_id) || !$post_id) {
        return 0;
    }

    foreach ($fields as $key => $value) {
        update_post_meta($post_id, '_' . sanitize_key($key), sanitize_text_field((string) $value));
    }

    if (!empty($attachments)) {
        update_post_meta($post_id, '_uploaded_files', array_map('sanitize_text_field', $attachments));
    }

    return (int) $post_id;
}

/**
 * Handle Design Details Form
 */
function zarvel_handle_customize_form() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    if (empty($_POST['zarvel_customize_form_submit'])) {
        return;
    }

    $redirect_url = home_url('/customize/');
    $is_product_form_submission = !empty($_POST['zc_product_form_submission']);

    /**
     * Nonce security check.
     */
    if (
        empty($_POST['zarvel_customize_nonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['zarvel_customize_nonce'])),
            'zarvel_customize_form_action'
        )
    ) {
        wp_safe_redirect(add_query_arg('request_status', 'security_error', $redirect_url));
        exit;
    }

    /**
     * Honeypot spam protection.
     * Real users should never fill this field.
     */
    if (!empty($_POST['website_url'])) {
        wp_safe_redirect(add_query_arg('request_status', 'spam', $redirect_url));
        exit;
    }

    /**
     * Basic rate limit by IP.
     */
    $user_ip = isset($_SERVER['REMOTE_ADDR'])
        ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']))
        : 'unknown';

    $is_local_site =
        (function_exists('wp_get_environment_type') && wp_get_environment_type() === 'local') ||
        strpos((string) home_url('/'), '.local') !== false ||
        strpos((string) home_url('/'), 'localhost') !== false;

    if (!$is_local_site) {
        $rate_limit_key = 'zarvel_customize_form_' . md5($user_ip);

        if (get_transient($rate_limit_key)) {
            wp_safe_redirect(add_query_arg('request_status', 'too_many_requests', $redirect_url));
            exit;
        }

        set_transient($rate_limit_key, true, 60);
    }

    /**
     * Sanitize normal fields.
     */
    $full_name    = isset($_POST['full_name']) ? sanitize_text_field(wp_unslash($_POST['full_name'])) : '';
    $email        = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $phone        = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
    $design_text  = isset($_POST['design_text']) ? sanitize_text_field(wp_unslash($_POST['design_text'])) : '';
    $preferred_colors = isset($_POST['preferred_colors']) ? sanitize_text_field(wp_unslash($_POST['preferred_colors'])) : '';
    $design_notes = isset($_POST['design_notes']) ? sanitize_textarea_field(wp_unslash($_POST['design_notes'])) : '';
    $selected_options = isset($_POST['selected_options']) ? sanitize_text_field(wp_unslash($_POST['selected_options'])) : '';
    $print_location_extra_cost = isset($_POST['print_location_extra_cost']) ? sanitize_text_field(wp_unslash($_POST['print_location_extra_cost'])) : '0';
    $print_location_extra_label = isset($_POST['print_location_extra_label']) ? sanitize_text_field(wp_unslash($_POST['print_location_extra_label'])) : '';
    $selected_product_id = isset($_POST['zc_product_id']) ? absint($_POST['zc_product_id']) : 0;
    $selected_variation_id = isset($_POST['zc_variation_id']) ? absint($_POST['zc_variation_id']) : 0;
    $selected_product_name = '';

    if ($selected_product_id && function_exists('wc_get_product')) {
        $selected_product = wc_get_product($selected_product_id);

        if ($selected_product) {
            $selected_product_name = $selected_product->get_name();
        }

        $selected_variation = $selected_variation_id ? wc_get_product($selected_variation_id) : null;

        if (
            !$selected_variation ||
            !$selected_variation->is_type('variation') ||
            (int) $selected_variation->get_parent_id() !== $selected_product_id
        ) {
            $selected_variation_id = 0;
        }
    }

    /**
     * Normalize product type.
     * This fixes values like:
     * T-Shirt, T Shirt, Phone Case, Tote Bag, etc.
     */
    $raw_product_type = isset($_POST['product_type'])
        ? sanitize_text_field(wp_unslash($_POST['product_type']))
        : '';

    $product_type = sanitize_title($raw_product_type);

    $product_aliases = array(
        'airpods'     => 'airpods',
        'airpod'      => 'airpods',
        'cap'         => 'cap',
        'tshirt'      => 't-shirt',
        't-shirt'     => 't-shirt',
        't-shirt-'    => 't-shirt',
        'hoodie'      => 'hoodie',
        'sweatshirt'  => 'sweatshirt',
        'sweater'     => 'sweatshirt',
        'mug'         => 'mug',
        'tote'        => 'tote-bag',
        'tote-bag'    => 'tote-bag',
        'phonecase'   => 'phone-case',
        'phone-case'  => 'phone-case',
        'other'       => 'other',
    );

    if (isset($product_aliases[$product_type])) {
        $product_type = $product_aliases[$product_type];
    }

    $raw_print_location = isset($_POST['print_location'])
        ? sanitize_text_field(wp_unslash($_POST['print_location']))
        : '';

    $print_location = sanitize_title($raw_print_location);

    $print_location_aliases = array(
        'front'          => 'front',
        'back'           => 'back',
        'front-back'     => 'front-and-back',
        'front-and-back' => 'front-and-back',
        'left-chest'     => 'left-chest',
        'sleeve'         => 'sleeve',
        'other'          => 'other',
        'not-sure'       => 'other',
    );

    if (isset($print_location_aliases[$print_location])) {
        $print_location = $print_location_aliases[$print_location];
    }

    /**
     * Normalize logo/design status.
     */
    $raw_logo_status = isset($_POST['logo_status'])
        ? sanitize_text_field(wp_unslash($_POST['logo_status']))
        : '';

    $logo_status = sanitize_title($raw_logo_status);

    $logo_status_aliases = array(
        'has-logo'       => 'has-logo',
        'has-design'     => 'has-logo',
        'already-have'   => 'has-logo',
        'needs-design'   => 'needs-design',
        'need-design'    => 'needs-design',
        'design-for-me'  => 'needs-design',
        'has-idea-only'  => 'has-idea-only',
        'idea-only'      => 'has-idea-only',
    );

    if (isset($logo_status_aliases[$logo_status])) {
        $logo_status = $logo_status_aliases[$logo_status];
    }

    /**
     * Required fields.
     */
    if (
        empty($full_name) ||
        empty($email) ||
        empty($product_type) ||
        empty($print_location) ||
        empty($logo_status) ||
        empty($design_notes)
    ) {
        wp_safe_redirect(add_query_arg('request_status', 'missing_fields', $redirect_url));
        exit;
    }

    /**
     * Validate email.
     */
    if (!is_email($email)) {
        wp_safe_redirect(add_query_arg('request_status', 'invalid_email', $redirect_url));
        exit;
    }

    if ($is_product_form_submission && function_exists('zarvel_portal_current_customer_email')) {
        $portal_email = strtolower(sanitize_email(zarvel_portal_current_customer_email()));

        if (!$portal_email || strtolower($email) !== $portal_email) {
            wp_safe_redirect(add_query_arg('request_status', 'login_required', $redirect_url));
            exit;
        }
    }

    /**
     * Allowed product types.
     */
    $allowed_product_types = array(
        'airpods',
        'cap',
        't-shirt',
        'hoodie',
        'sweatshirt',
        'mug',
        'tote-bag',
        'phone-case',
        'other',
    );

    if (!in_array($product_type, $allowed_product_types, true)) {
        wp_safe_redirect(add_query_arg('request_status', 'invalid_product', $redirect_url));
        exit;
    }

    $allowed_print_locations = array(
        'front',
        'back',
        'front-and-back',
        'left-chest',
        'sleeve',
        'other',
    );

    if (!in_array($print_location, $allowed_print_locations, true)) {
        wp_safe_redirect(add_query_arg('request_status', 'missing_fields', $redirect_url));
        exit;
    }

    /**
     * Allowed logo/design statuses.
     */
    $allowed_logo_statuses = array(
        'has-logo',
        'needs-design',
        'has-idea-only',
    );

    if (!in_array($logo_status, $allowed_logo_statuses, true)) {
        wp_safe_redirect(add_query_arg('request_status', 'invalid_logo_status', $redirect_url));
        exit;
    }

    /**
     * Human-readable labels for email.
     */
    $product_type_labels = array(
        'airpods'    => 'AirPods',
        'cap'        => 'Cap',
        't-shirt'    => 'T-Shirt',
        'hoodie'     => 'Hoodie',
        'sweatshirt' => 'Sweatshirt',
        'mug'        => 'Mug',
        'tote-bag'   => 'Tote Bag',
        'phone-case' => 'Phone Case',
        'other'      => 'Other',
    );

    $logo_status_labels = array(
        'has-logo'      => 'Customer already has a logo/design and may upload it.',
        'needs-design'  => 'Customer wants Zarvel Creatives to create the design/logo.',
        'has-idea-only' => 'Customer has an idea only and needs help turning it into a design.',
    );

    $print_location_labels = array(
        'front'          => 'Front',
        'back'           => 'Back',
        'front-and-back' => 'Front and Back',
        'left-chest'     => 'Left Chest',
        'sleeve'         => 'Sleeve',
        'other'          => 'Other / Not Sure',
    );

    $product_type_label = $product_type_labels[$product_type];
    $logo_status_label  = $logo_status_labels[$logo_status];
    $print_location_label = $print_location_labels[$print_location];

    /**
     * Email setup.
     */
    $recipient = 'bryanceazartabanas@gmail.com';
    $subject   = 'New Design Form Submission - ' . $full_name;

    $message  = "New design form submission\n";
    $message .= "==========================\n\n";

    $message .= "Customer Details\n";
    $message .= "----------------\n";
    $message .= "Full Name: {$full_name}\n";
    $message .= "Email: {$email}\n";
    $message .= "Phone: {$phone}\n\n";

    $message .= "Product Request\n";
    $message .= "---------------\n";
    if ($selected_product_name) {
        $message .= "Selected Product: {$selected_product_name} (#{$selected_product_id})\n";
    }
    if ($selected_options) {
        $message .= "Selected Options: {$selected_options}\n";
    }
    $message .= "Product Type: {$product_type_label}\n";
    $message .= "Print Placement: {$print_location_label}\n";
    if ($print_location_extra_cost === 'quote') {
        $message .= "Estimated Placement Extra Cost: Quote required\n";
    } elseif ((float) $print_location_extra_cost > 0) {
        $message .= "Estimated Placement Extra Cost: +$" . number_format((float) $print_location_extra_cost, 2) . "\n";
    } else {
        $message .= "Estimated Placement Extra Cost: $0.00\n";
    }
    if ($print_location_extra_label) {
        $message .= "Placement Cost Note: {$print_location_extra_label}\n";
    }
    $message .= "Logo / Design Status: {$logo_status_label}\n\n";

    $message .= "Design Details\n";
    $message .= "--------------\n";
    $message .= "Text To Include: {$design_text}\n";
    $message .= "Preferred Colors: {$preferred_colors}\n\n";

    $message .= "Design Instructions\n";
    $message .= "-------------------\n";
    $message .= "{$design_notes}\n\n";

    $message .= "Admin Note\n";
    $message .= "----------\n";

    if ($logo_status === 'needs-design') {
        $message .= "This customer wants Zarvel Creatives to design the logo/artwork.\n";
    } elseif ($logo_status === 'has-logo') {
        $message .= "This customer says they already have a logo/design. Check if a file is attached.\n";
    } elseif ($logo_status === 'has-idea-only') {
        $message .= "This customer has an idea but may need design help.\n";
    }

    $message .= "\nSent from: " . home_url('/customize/') . "\n";

    /**
     * Safe email headers.
     */
    $safe_reply_name = str_replace(array("\r", "\n"), '', $full_name);
    $safe_reply_mail = str_replace(array("\r", "\n"), '', $email);

    $site_host = (string) wp_parse_url(home_url('/'), PHP_URL_HOST);
    $site_host = preg_replace('/^www\./', '', $site_host);
    $from_email = $site_host ? 'wordpress@' . $site_host : get_option('admin_email');

    if (defined('ZARVEL_SMTP_FROM') && is_email(ZARVEL_SMTP_FROM)) {
        $from_email = ZARVEL_SMTP_FROM;
    }

    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: Zarvel Creatives <' . $from_email . '>',
        'Reply-To: ' . $safe_reply_name . ' <' . $safe_reply_mail . '>',
    );

    $attachments = array();

    /**
     * File upload.
     * Safe version: JPG, JPEG, PNG, PDF only.
     */
    if (!empty($_FILES['upload_file']['name'])) {
        $file = $_FILES['upload_file'];

        if (!isset($file['error']) || is_array($file['error'])) {
            wp_safe_redirect(add_query_arg('request_status', 'upload_error', $redirect_url));
            exit;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            wp_safe_redirect(add_query_arg('request_status', 'upload_error', $redirect_url));
            exit;
        }

        $max_file_size = 20 * 1024 * 1024;

        if (empty($file['size']) || $file['size'] > $max_file_size) {
            wp_safe_redirect(add_query_arg('request_status', 'file_too_large', $redirect_url));
            exit;
        }

        $allowed_mimes = array(
            'jpg|jpeg|jpe' => 'image/jpeg',
            'png'          => 'image/png',
            'pdf'          => 'application/pdf',
        );

        require_once ABSPATH . 'wp-admin/includes/file.php';

        $upload = wp_handle_upload($file, array(
            'test_form' => false,
            'mimes'     => $allowed_mimes,
        ));

        if (!empty($upload['error'])) {
            wp_safe_redirect(add_query_arg('request_status', 'upload_error', $redirect_url));
            exit;
        }

        if (!empty($upload['file']) && file_exists($upload['file'])) {
            $attachments[] = $upload['file'];
        }
    }

    /**
     * Send email.
     */
    $admin_post_id = zarvel_save_customize_form_admin_post($subject, $message, array(
        'full_name'             => $full_name,
        'email'                 => $email,
        'phone'                 => $phone,
        'selected_product_id'   => $selected_product_id,
        'selected_variation_id' => $selected_variation_id,
        'selected_product_name' => $selected_product_name,
        'selected_options'      => $selected_options,
        'product_type'          => $product_type_label,
        'print_location'        => $print_location_label,
        'print_location_extra_cost' => $print_location_extra_cost,
        'print_location_extra_label' => $print_location_extra_label,
        'logo_status'           => $logo_status_label,
        'design_text'           => $design_text,
        'preferred_colors'      => $preferred_colors,
        'design_notes'          => $design_notes,
    ), $attachments);

    $sent = wp_mail($recipient, $subject, $message, $headers, $attachments);
    $saved_locally = zarvel_save_customize_form_submission($subject, $message, $attachments);

    /**
     * Delete uploaded file after sending email only when no local saved copy needs it.
     */
    if ($sent && !$saved_locally && !empty($attachments)) {
        foreach ($attachments as $attachment) {
            if (file_exists($attachment)) {
                wp_delete_file($attachment);
            }
        }
    }

    if ($sent || $saved_locally || $admin_post_id) {
        if ($is_product_form_submission) {
            $thank_you_url = home_url('/design-request-thank-you/');

            if ($admin_post_id) {
                $thank_you_url = add_query_arg('design_request', $admin_post_id, $thank_you_url);
            }

            wp_safe_redirect($thank_you_url);
            exit;
        }

        wp_safe_redirect(add_query_arg('request_status', 'success', $redirect_url));
        exit;
    }

    wp_safe_redirect(add_query_arg('request_status', 'failed', $redirect_url));
    exit;
}
add_action('template_redirect', 'zarvel_handle_customize_form');

/**
 * Handle Website Project Form
 */
function zarvel_handle_website_request_form() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    if (empty($_POST['zarvel_website_form_submit'])) {
        return;
    }

    $redirect_url = add_query_arg('service', 'website', home_url('/contact/'));

    if (
        empty($_POST['zarvel_website_nonce']) ||
        !wp_verify_nonce(
            sanitize_text_field(wp_unslash($_POST['zarvel_website_nonce'])),
            'zarvel_website_form_action'
        )
    ) {
        wp_safe_redirect(add_query_arg('request_status', 'security_error', $redirect_url));
        exit;
    }

    if (!empty($_POST['website_url'])) {
        wp_safe_redirect(add_query_arg('request_status', 'spam', $redirect_url));
        exit;
    }

    $user_ip = isset($_SERVER['REMOTE_ADDR'])
        ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']))
        : 'unknown';

    $is_local_site =
        (function_exists('wp_get_environment_type') && wp_get_environment_type() === 'local') ||
        strpos((string) home_url('/'), '.local') !== false ||
        strpos((string) home_url('/'), 'localhost') !== false;

    if (!$is_local_site) {
        $rate_limit_key = 'zarvel_website_form_' . md5($user_ip);

        if (get_transient($rate_limit_key)) {
            wp_safe_redirect(add_query_arg('request_status', 'too_many_requests', $redirect_url));
            exit;
        }

        set_transient($rate_limit_key, true, 60);
    }

    $full_name = isset($_POST['full_name']) ? sanitize_text_field(wp_unslash($_POST['full_name'])) : '';
    $email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
    $business_name = isset($_POST['business_name']) ? sanitize_text_field(wp_unslash($_POST['business_name'])) : '';
    $current_website = isset($_POST['current_website']) ? esc_url_raw(wp_unslash($_POST['current_website'])) : '';
    $website_type = isset($_POST['website_type']) ? sanitize_text_field(wp_unslash($_POST['website_type'])) : '';
    $platform = isset($_POST['platform']) ? sanitize_text_field(wp_unslash($_POST['platform'])) : '';
    $budget = isset($_POST['budget']) ? sanitize_text_field(wp_unslash($_POST['budget'])) : '';
    $timeline = isset($_POST['timeline']) ? sanitize_text_field(wp_unslash($_POST['timeline'])) : '';
    $features = isset($_POST['features']) ? array_map('sanitize_text_field', wp_unslash((array) $_POST['features'])) : array();
    $project_notes = isset($_POST['project_notes']) ? sanitize_textarea_field(wp_unslash($_POST['project_notes'])) : '';

    if (empty($full_name) || empty($email) || empty($website_type) || empty($project_notes)) {
        wp_safe_redirect(add_query_arg('request_status', 'missing_fields', $redirect_url));
        exit;
    }

    if (!is_email($email)) {
        wp_safe_redirect(add_query_arg('request_status', 'invalid_email', $redirect_url));
        exit;
    }

    $recipient = 'bryanceazartabanas@gmail.com';
    $subject = 'New Website Project Request - ' . $full_name;

    $message = "New website project request\n";
    $message .= "===========================\n\n";
    $message .= "Customer Details\n";
    $message .= "----------------\n";
    $message .= "Full Name: {$full_name}\n";
    $message .= "Email: {$email}\n";
    $message .= "Phone: {$phone}\n";
    $message .= "Business / Brand: {$business_name}\n";
    $message .= "Current Website: {$current_website}\n\n";
    $message .= "Website Project\n";
    $message .= "---------------\n";
    $message .= "Website Type: {$website_type}\n";
    $message .= "Preferred Platform: {$platform}\n";
    $message .= "Budget: {$budget}\n";
    $message .= "Timeline: {$timeline}\n";
    $message .= "Features: " . (!empty($features) ? implode(', ', $features) : 'Not specified') . "\n\n";
    $message .= "Project Notes\n";
    $message .= "-------------\n";
    $message .= "{$project_notes}\n\n";
    $message .= "Sent from: " . $redirect_url . "\n";

    $safe_reply_name = str_replace(array("\r", "\n"), '', $full_name);
    $safe_reply_mail = str_replace(array("\r", "\n"), '', $email);

    $site_host = (string) wp_parse_url(home_url('/'), PHP_URL_HOST);
    $site_host = preg_replace('/^www\./', '', $site_host);
    $from_email = $site_host ? 'wordpress@' . $site_host : get_option('admin_email');

    if (defined('ZARVEL_SMTP_FROM') && is_email(ZARVEL_SMTP_FROM)) {
        $from_email = ZARVEL_SMTP_FROM;
    }

    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: Zarvel Creatives <' . $from_email . '>',
        'Reply-To: ' . $safe_reply_name . ' <' . $safe_reply_mail . '>',
    );

    $saved_locally = zarvel_save_customize_form_submission($subject, $message);
    $sent = wp_mail($recipient, $subject, $message, $headers);

    if ($sent || $saved_locally) {
        wp_safe_redirect(add_query_arg('request_status', 'success', $redirect_url));
        exit;
    }

    wp_safe_redirect(add_query_arg('request_status', 'failed', $redirect_url));
    exit;
}
add_action('template_redirect', 'zarvel_handle_website_request_form');
