<?php
defined('ABSPATH') || exit;

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
    $selected_product_id = isset($_POST['zc_product_id']) ? absint($_POST['zc_product_id']) : 0;
    $selected_product_name = '';

    if ($selected_product_id && function_exists('wc_get_product')) {
        $selected_product = wc_get_product($selected_product_id);

        if ($selected_product) {
            $selected_product_name = $selected_product->get_name();
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
    $message .= "Product Type: {$product_type_label}\n";
    $message .= "Print Placement: {$print_location_label}\n";
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

    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'From: Zarvel Creatives <bryanceazartabanas@gmail.com>',
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

    if ($sent || $saved_locally) {
        wp_safe_redirect(add_query_arg('request_status', 'success', $redirect_url));
        exit;
    }

    wp_safe_redirect(add_query_arg('request_status', 'failed', $redirect_url));
    exit;
}
add_action('template_redirect', 'zarvel_handle_customize_form');
