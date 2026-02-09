<?php
/**
 * Booking Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Booking {
    
    /**
     * Create a new booking
     */
    public static function create_booking($data) {
        $room = SHB_Room::get_room($data['room_id']);
        if (!$room) {
            return new WP_Error('invalid_room', __('Room not found', 'sanctuary-hotel-booking'));
        }
        
        // Check availability
        $is_available = SHB_Availability::check_room_availability(
            $data['room_id'],
            $data['check_in'],
            $data['check_out']
        );
        
        if (!$is_available) {
            return new WP_Error('not_available', __('Room is not available for selected dates', 'sanctuary-hotel-booking'));
        }
        
        // Calculate total price
        $total_price = SHB_Pricing::calculate_total_price(
            $data['room_id'],
            $data['check_in'],
            $data['check_out']
        );
        
        // Create booking post
        $booking_title = sprintf(
            '%s - %s %s (%s to %s)',
            $room['name'],
            $data['first_name'],
            $data['last_name'],
            $data['check_in'],
            $data['check_out']
        );
        
        $booking_id = wp_insert_post(array(
            'post_title' => $booking_title,
            'post_type' => 'shb_booking',
            'post_status' => 'publish',
        ));
        
        if (is_wp_error($booking_id)) {
            return $booking_id;
        }
        
        // Save booking meta
        update_post_meta($booking_id, '_shb_room_id', $data['room_id']);
        update_post_meta($booking_id, '_shb_room_name', $room['name']);
        update_post_meta($booking_id, '_shb_check_in', $data['check_in']);
        update_post_meta($booking_id, '_shb_check_out', $data['check_out']);
        update_post_meta($booking_id, '_shb_guests', $data['guests']);
        update_post_meta($booking_id, '_shb_first_name', sanitize_text_field($data['first_name']));
        update_post_meta($booking_id, '_shb_last_name', sanitize_text_field($data['last_name']));
        update_post_meta($booking_id, '_shb_email', sanitize_email($data['email']));
        update_post_meta($booking_id, '_shb_phone', sanitize_text_field($data['phone']));
        update_post_meta($booking_id, '_shb_special_requests', sanitize_textarea_field($data['special_requests'] ?? ''));
        update_post_meta($booking_id, '_shb_total_price', $total_price);
        update_post_meta($booking_id, '_shb_payment_method', sanitize_text_field($data['payment_method'] ?? 'stripe'));
        update_post_meta($booking_id, '_shb_payment_status', 'pending');
        update_post_meta($booking_id, '_shb_booking_status', 'pending');
        update_post_meta($booking_id, '_shb_booking_date', current_time('mysql'));
        
        // Generate unique booking reference
        $booking_ref = 'SHB-' . strtoupper(substr(md5($booking_id . time()), 0, 8));
        update_post_meta($booking_id, '_shb_booking_ref', $booking_ref);
        
        return array(
            'booking_id' => $booking_id,
            'booking_ref' => $booking_ref,
            'total_price' => $total_price,
        );
    }
    
    /**
     * Get booking by ID
     */
    public static function get_booking($booking_id) {
        $post = get_post($booking_id);
        if (!$post || $post->post_type !== 'shb_booking') {
            return null;
        }
        
        return self::format_booking($post);
    }
    
    /**
     * Get booking by reference
     */
    public static function get_booking_by_ref($booking_ref) {
        $bookings = get_posts(array(
            'post_type' => 'shb_booking',
            'meta_key' => '_shb_booking_ref',
            'meta_value' => $booking_ref,
            'posts_per_page' => 1,
        ));
        
        if (empty($bookings)) {
            return null;
        }
        
        return self::format_booking($bookings[0]);
    }
    
    /**
     * Format booking data
     */
    public static function format_booking($post) {
        $booking_id = is_object($post) ? $post->ID : $post;
        
        return array(
            'id' => $booking_id,
            'booking_ref' => get_post_meta($booking_id, '_shb_booking_ref', true),
            'room_id' => get_post_meta($booking_id, '_shb_room_id', true),
            'room_name' => get_post_meta($booking_id, '_shb_room_name', true),
            'check_in' => get_post_meta($booking_id, '_shb_check_in', true),
            'check_out' => get_post_meta($booking_id, '_shb_check_out', true),
            'guests' => get_post_meta($booking_id, '_shb_guests', true),
            'first_name' => get_post_meta($booking_id, '_shb_first_name', true),
            'last_name' => get_post_meta($booking_id, '_shb_last_name', true),
            'email' => get_post_meta($booking_id, '_shb_email', true),
            'phone' => get_post_meta($booking_id, '_shb_phone', true),
            'special_requests' => get_post_meta($booking_id, '_shb_special_requests', true),
            'total_price' => floatval(get_post_meta($booking_id, '_shb_total_price', true)),
            'payment_method' => get_post_meta($booking_id, '_shb_payment_method', true),
            'payment_status' => get_post_meta($booking_id, '_shb_payment_status', true),
            'booking_status' => get_post_meta($booking_id, '_shb_booking_status', true),
            'booking_date' => get_post_meta($booking_id, '_shb_booking_date', true),
            'stripe_session_id' => get_post_meta($booking_id, '_shb_stripe_session_id', true),
        );
    }
    
    /**
     * Update booking status
     */
    public static function update_status($booking_id, $status) {
        $valid_statuses = array('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled');
        
        if (!in_array($status, $valid_statuses)) {
            return new WP_Error('invalid_status', __('Invalid booking status', 'sanctuary-hotel-booking'));
        }
        
        update_post_meta($booking_id, '_shb_booking_status', $status);
        
        // Send email notification
        if ($status === 'confirmed') {
            self::send_confirmation_email($booking_id);
        }
        
        return true;
    }
    
    /**
     * Update payment status
     */
    public static function update_payment_status($booking_id, $status) {
        update_post_meta($booking_id, '_shb_payment_status', $status);
        
        // If payment is completed, confirm booking
        if ($status === 'paid') {
            self::update_status($booking_id, 'confirmed');
        }
        
        return true;
    }
    
    /**
     * Get all bookings
     */
    public static function get_bookings($args = array()) {
        $defaults = array(
            'post_type' => 'shb_booking',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        $args = wp_parse_args($args, $defaults);
        $bookings = get_posts($args);
        
        return array_map(array(__CLASS__, 'format_booking'), $bookings);
    }
    
    /**
     * Get bookings by room
     */
    public static function get_bookings_by_room($room_id, $status = null) {
        $meta_query = array(
            array(
                'key' => '_shb_room_id',
                'value' => $room_id,
            ),
        );
        
        if ($status) {
            $meta_query[] = array(
                'key' => '_shb_booking_status',
                'value' => $status,
            );
        }
        
        return self::get_bookings(array(
            'meta_query' => $meta_query,
        ));
    }
    
    /**
     * Send confirmation email with .ics attachment
     */
    public static function send_confirmation_email($booking_id) {
        if (get_option('shb_email_notifications') !== '1') {
            return;
        }
        
        $booking = self::get_booking($booking_id);
        if (!$booking) {
            return;
        }
        
        $currency = get_option('shb_currency_symbol', '$');
        $check_in_time = get_option('shb_check_in_time', '14:00');
        $check_out_time = get_option('shb_check_out_time', '11:00');
        $site_name = get_bloginfo('name');
        
        $to = $booking['email'];
        $subject = sprintf(__('Booking Confirmed - %s | %s', 'sanctuary-hotel-booking'), $booking['booking_ref'], $site_name);
        
        $message = sprintf(
            "Dear %s %s,\n\n" .
            "Great news! Your booking has been confirmed.\n\n" .
            "━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
            "BOOKING DETAILS\n" .
            "━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
            "Reference: %s\n" .
            "Room: %s\n" .
            "Check-in: %s at %s\n" .
            "Check-out: %s at %s\n" .
            "Guests: %d\n" .
            "Total Paid: %s%s\n\n" .
            "━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
            "%s\n\n" .
            "A calendar event (.ics) file is attached for your convenience.\n" .
            "Simply open it to add this stay to your calendar app.\n\n" .
            "If you have any questions, please contact us.\n\n" .
            "Best regards,\n%s",
            $booking['first_name'],
            $booking['last_name'],
            $booking['booking_ref'],
            $booking['room_name'],
            $booking['check_in'],
            $check_in_time,
            $booking['check_out'],
            $check_out_time,
            $booking['guests'],
            $currency,
            number_format($booking['total_price'], 2),
            $booking['special_requests'] ? "Special Requests: " . $booking['special_requests'] : '',
            $site_name
        );
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        $attachments = array();
        
        // Generate .ics file and attach it
        $ics_content = SHB_Calendar::generate_ics($booking_id);
        if ($ics_content) {
            $upload_dir = wp_upload_dir();
            $ics_dir = $upload_dir['basedir'] . '/shb-temp/';
            if (!file_exists($ics_dir)) {
                wp_mkdir_p($ics_dir);
            }
            $ics_file = $ics_dir . 'booking-' . $booking['booking_ref'] . '.ics';
            file_put_contents($ics_file, $ics_content);
            $attachments[] = $ics_file;
        }
        
        wp_mail($to, $subject, $message, $headers, $attachments);
        
        // Notify admin (without attachment)
        $admin_email = get_option('shb_admin_email', get_option('admin_email'));
        $admin_subject = sprintf('[New Booking] %s - %s %s', $booking['booking_ref'], $booking['first_name'], $booking['last_name']);
        wp_mail($admin_email, $admin_subject, $message, $headers);
        
        // Clean up temp .ics file
        if (!empty($ics_file) && file_exists($ics_file)) {
            @unlink($ics_file);
        }
    }
    
    /**
     * Send booking details email manually (resend)
     */
    public static function send_booking_details_email($booking_id) {
        $booking = self::get_booking($booking_id);
        if (!$booking) {
            return new WP_Error('invalid_booking', __('Booking not found', 'sanctuary-hotel-booking'));
        }
        
        $currency = get_option('shb_currency_symbol', '$');
        $check_in_time = get_option('shb_check_in_time', '14:00');
        $check_out_time = get_option('shb_check_out_time', '11:00');
        $site_name = get_bloginfo('name');
        $status_label = ucfirst(str_replace('_', ' ', $booking['booking_status']));
        
        $to = $booking['email'];
        $subject = sprintf(__('Your Booking Details - %s | %s', 'sanctuary-hotel-booking'), $booking['booking_ref'], $site_name);
        
        $message = sprintf(
            "Dear %s %s,\n\n" .
            "Here are your booking details.\n\n" .
            "━━━━━━━━━━━━━━━━━━━━━━━━━\n" .
            "BOOKING DETAILS\n" .
            "━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
            "Reference: %s\n" .
            "Status: %s\n" .
            "Room: %s\n" .
            "Check-in: %s at %s\n" .
            "Check-out: %s at %s\n" .
            "Guests: %d\n" .
            "Total: %s%s\n" .
            "Payment: %s\n\n" .
            "━━━━━━━━━━━━━━━━━━━━━━━━━\n\n" .
            "A calendar event (.ics) file is attached.\n\n" .
            "Best regards,\n%s",
            $booking['first_name'],
            $booking['last_name'],
            $booking['booking_ref'],
            $status_label,
            $booking['room_name'],
            $booking['check_in'],
            $check_in_time,
            $booking['check_out'],
            $check_out_time,
            $booking['guests'],
            $currency,
            number_format($booking['total_price'], 2),
            ucfirst($booking['payment_status']),
            $site_name
        );
        
        $headers = array('Content-Type: text/plain; charset=UTF-8');
        $attachments = array();
        
        $ics_content = SHB_Calendar::generate_ics($booking_id);
        if ($ics_content) {
            $upload_dir = wp_upload_dir();
            $ics_dir = $upload_dir['basedir'] . '/shb-temp/';
            if (!file_exists($ics_dir)) {
                wp_mkdir_p($ics_dir);
            }
            $ics_file = $ics_dir . 'booking-' . $booking['booking_ref'] . '.ics';
            file_put_contents($ics_file, $ics_content);
            $attachments[] = $ics_file;
        }
        
        $result = wp_mail($to, $subject, $message, $headers, $attachments);
        
        if (!empty($ics_file) && file_exists($ics_file)) {
            @unlink($ics_file);
        }
        
        return $result;
    }
    
    /**
     * Calculate nights between dates
     */
    public static function calculate_nights($check_in, $check_out) {
        $date1 = new DateTime($check_in);
        $date2 = new DateTime($check_out);
        $interval = $date1->diff($date2);
        return $interval->days;
    }
}
