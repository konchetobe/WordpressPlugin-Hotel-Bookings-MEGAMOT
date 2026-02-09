<?php
/**
 * AJAX Handlers
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Ajax {
    
    public static function init() {
        // Public AJAX actions
        add_action('wp_ajax_shb_search_rooms', array(__CLASS__, 'search_rooms'));
        add_action('wp_ajax_nopriv_shb_search_rooms', array(__CLASS__, 'search_rooms'));
        
        add_action('wp_ajax_shb_check_availability', array(__CLASS__, 'check_availability'));
        add_action('wp_ajax_nopriv_shb_check_availability', array(__CLASS__, 'check_availability'));
        
        add_action('wp_ajax_shb_calculate_price', array(__CLASS__, 'calculate_price'));
        add_action('wp_ajax_nopriv_shb_calculate_price', array(__CLASS__, 'calculate_price'));
        
        add_action('wp_ajax_shb_create_booking', array(__CLASS__, 'create_booking'));
        add_action('wp_ajax_nopriv_shb_create_booking', array(__CLASS__, 'create_booking'));
        
        add_action('wp_ajax_shb_process_payment', array(__CLASS__, 'process_payment'));
        add_action('wp_ajax_nopriv_shb_process_payment', array(__CLASS__, 'process_payment'));
        
        add_action('wp_ajax_shb_download_calendar', array(__CLASS__, 'download_calendar'));
        add_action('wp_ajax_nopriv_shb_download_calendar', array(__CLASS__, 'download_calendar'));
        
        // Admin AJAX actions
        add_action('wp_ajax_shb_admin_update_booking_status', array(__CLASS__, 'admin_update_booking_status'));
        add_action('wp_ajax_shb_admin_get_dashboard_stats', array(__CLASS__, 'admin_get_dashboard_stats'));
        add_action('wp_ajax_shb_admin_save_pricing_rule', array(__CLASS__, 'admin_save_pricing_rule'));
        add_action('wp_ajax_shb_admin_delete_pricing_rule', array(__CLASS__, 'admin_delete_pricing_rule'));
        add_action('wp_ajax_shb_admin_create_availability_block', array(__CLASS__, 'admin_create_availability_block'));
        add_action('wp_ajax_shb_admin_delete_availability_block', array(__CLASS__, 'admin_delete_availability_block'));
        add_action('wp_ajax_shb_admin_send_booking_email', array(__CLASS__, 'admin_send_booking_email'));
    }
    
    /**
     * Search available rooms
     */
    public static function search_rooms() {
        check_ajax_referer('shb_nonce', 'nonce');
        
        $check_in = sanitize_text_field($_POST['check_in']);
        $check_out = sanitize_text_field($_POST['check_out']);
        $guests = intval($_POST['guests']);
        
        if (empty($check_in) || empty($check_out)) {
            wp_send_json_error(array('message' => __('Please select dates', 'sanctuary-hotel-booking')));
        }
        
        $rooms = SHB_Room::search_available_rooms($check_in, $check_out, $guests);
        
        wp_send_json_success(array('rooms' => $rooms));
    }
    
    /**
     * Check room availability
     */
    public static function check_availability() {
        check_ajax_referer('shb_nonce', 'nonce');
        
        $room_id = intval($_POST['room_id']);
        $check_in = sanitize_text_field($_POST['check_in']);
        $check_out = sanitize_text_field($_POST['check_out']);
        
        $available = SHB_Availability::check_room_availability($room_id, $check_in, $check_out);
        
        wp_send_json_success(array('available' => $available));
    }
    
    /**
     * Calculate price
     */
    public static function calculate_price() {
        check_ajax_referer('shb_nonce', 'nonce');
        
        $room_id = intval($_POST['room_id']);
        $check_in = sanitize_text_field($_POST['check_in']);
        $check_out = sanitize_text_field($_POST['check_out']);
        
        $breakdown = SHB_Pricing::get_price_breakdown($room_id, $check_in, $check_out);
        
        if (!$breakdown) {
            wp_send_json_error(array('message' => __('Unable to calculate price', 'sanctuary-hotel-booking')));
        }
        
        wp_send_json_success($breakdown);
    }
    
    /**
     * Create booking
     */
    public static function create_booking() {
        check_ajax_referer('shb_nonce', 'nonce');
        
        $data = array(
            'room_id' => intval($_POST['room_id']),
            'check_in' => sanitize_text_field($_POST['check_in']),
            'check_out' => sanitize_text_field($_POST['check_out']),
            'guests' => intval($_POST['guests']),
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'special_requests' => sanitize_textarea_field($_POST['special_requests'] ?? ''),
            'payment_method' => sanitize_text_field($_POST['payment_method'] ?? 'stripe'),
        );
        
        // Validate required fields
        $required = array('room_id', 'check_in', 'check_out', 'first_name', 'last_name', 'email', 'phone');
        foreach ($required as $field) {
            if (empty($data[$field])) {
                wp_send_json_error(array('message' => __('Please fill in all required fields', 'sanctuary-hotel-booking')));
            }
        }
        
        $result = SHB_Booking::create_booking($data);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * Process payment
     */
    public static function process_payment() {
        check_ajax_referer('shb_nonce', 'nonce');
        
        $booking_id = intval($_POST['booking_id']);
        $payment_method = sanitize_text_field($_POST['payment_method'] ?? 'stripe');
        
        if ($payment_method === 'stripe') {
            $result = SHB_Payments::create_stripe_checkout($booking_id);
        } elseif ($payment_method === 'paypal') {
            $result = SHB_Payments::create_paypal_checkout($booking_id);
        } else {
            wp_send_json_error(array('message' => __('Invalid payment method', 'sanctuary-hotel-booking')));
        }
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success($result);
    }
    
    /**
     * Download calendar
     */
    public static function download_calendar() {
        $booking_id = intval($_GET['booking_id']);
        
        if (!$booking_id) {
            wp_die(__('Invalid booking', 'sanctuary-hotel-booking'));
        }
        
        SHB_Calendar::download_ics($booking_id);
    }
    
    /**
     * Admin: Update booking status
     */
    public static function admin_update_booking_status() {
        check_ajax_referer('shb_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'sanctuary-hotel-booking')));
        }
        
        $booking_id = intval($_POST['booking_id']);
        $status = sanitize_text_field($_POST['status']);
        
        $result = SHB_Booking::update_status($booking_id, $status);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success(array('message' => __('Status updated', 'sanctuary-hotel-booking')));
    }
    
    /**
     * Admin: Get dashboard stats
     */
    public static function admin_get_dashboard_stats() {
        check_ajax_referer('shb_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'sanctuary-hotel-booking')));
        }
        
        $rooms = SHB_Room::get_rooms();
        $bookings = SHB_Booking::get_bookings();
        
        $total_revenue = 0;
        $pending_count = 0;
        $confirmed_count = 0;
        
        foreach ($bookings as $booking) {
            if ($booking['payment_status'] === 'paid') {
                $total_revenue += $booking['total_price'];
            }
            if ($booking['booking_status'] === 'pending') {
                $pending_count++;
            }
            if ($booking['booking_status'] === 'confirmed') {
                $confirmed_count++;
            }
        }
        
        wp_send_json_success(array(
            'total_rooms' => count($rooms),
            'total_bookings' => count($bookings),
            'pending_bookings' => $pending_count,
            'confirmed_bookings' => $confirmed_count,
            'total_revenue' => $total_revenue,
        ));
    }
    
    /**
     * Admin: Save pricing rule
     */
    public static function admin_save_pricing_rule() {
        check_ajax_referer('shb_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'sanctuary-hotel-booking')));
        }
        
        $rule_id = intval($_POST['rule_id'] ?? 0);
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'rule_type' => sanitize_text_field($_POST['rule_type']),
            'room_type' => sanitize_text_field($_POST['room_type'] ?? ''),
            'start_date' => sanitize_text_field($_POST['start_date'] ?? ''),
            'end_date' => sanitize_text_field($_POST['end_date'] ?? ''),
            'multiplier' => floatval($_POST['multiplier']),
            'is_active' => intval($_POST['is_active'] ?? 1),
        );
        
        if ($rule_id) {
            $result = SHB_Pricing::update_pricing_rule($rule_id, $data);
        } else {
            $result = SHB_Pricing::create_pricing_rule($data);
        }
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success(array('message' => __('Rule saved', 'sanctuary-hotel-booking')));
    }
    
    /**
     * Admin: Delete pricing rule
     */
    public static function admin_delete_pricing_rule() {
        check_ajax_referer('shb_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'sanctuary-hotel-booking')));
        }
        
        $rule_id = intval($_POST['rule_id']);
        SHB_Pricing::delete_pricing_rule($rule_id);
        
        wp_send_json_success(array('message' => __('Rule deleted', 'sanctuary-hotel-booking')));
    }
    
    /**
     * Admin: Create availability block
     */
    public static function admin_create_availability_block() {
        check_ajax_referer('shb_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'sanctuary-hotel-booking')));
        }
        
        $data = array(
            'room_id' => intval($_POST['room_id']),
            'start_date' => sanitize_text_field($_POST['start_date']),
            'end_date' => sanitize_text_field($_POST['end_date']),
            'reason' => sanitize_text_field($_POST['reason'] ?? 'maintenance'),
        );
        
        $result = SHB_Availability::create_availability_block($data);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success(array('message' => __('Block created', 'sanctuary-hotel-booking')));
    }
    
    /**
     * Admin: Delete availability block
     */
    public static function admin_delete_availability_block() {
        check_ajax_referer('shb_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'sanctuary-hotel-booking')));
        }
        
        $block_id = intval($_POST['block_id']);
        SHB_Availability::delete_availability_block($block_id);
        
        wp_send_json_success(array('message' => __('Block removed', 'sanctuary-hotel-booking')));
    }
    
    /**
     * Admin: Send booking details email
     */
    public static function admin_send_booking_email() {
        check_ajax_referer('shb_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized', 'sanctuary-hotel-booking')));
        }
        
        $booking_id = intval($_POST['booking_id']);
        $result = SHB_Booking::send_booking_details_email($booking_id);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success(array('message' => __('Email sent successfully', 'sanctuary-hotel-booking')));
    }
}
