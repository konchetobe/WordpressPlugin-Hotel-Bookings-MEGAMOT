<?php
/**
 * Public Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Public {
    
    public static function init() {
        // Handle payment callbacks
        add_action('template_redirect', array(__CLASS__, 'handle_payment_callback'));
        
        // Handle calendar download
        add_action('template_redirect', array(__CLASS__, 'handle_calendar_download'));
    }
    
    /**
     * Handle payment callbacks
     */
    public static function handle_payment_callback() {
        if (!isset($_GET['shb_action'])) {
            return;
        }
        
        $action = sanitize_text_field($_GET['shb_action']);
        
        if ($action === 'payment_success') {
            $booking_id = intval($_GET['booking_id']);
            $session_id = sanitize_text_field($_GET['session_id']);
            
            if ($booking_id && $session_id) {
                $result = SHB_Payments::handle_payment_success($booking_id, $session_id);

                if ($result === true) {
                    $booking = SHB_Booking::get_booking($booking_id);
                    $url = add_query_arg(
                        'booking_token',
                        $booking['calendar_token'],
                        Sanctuary_Hotel_Booking::get_confirmation_url($booking['booking_ref'])
                    );
                    wp_safe_redirect($url);
                    exit;
                }
            }
        }
    }
    
    /**
     * Handle calendar download
     */
    public static function handle_calendar_download() {
        if (!isset($_GET['shb_download_calendar'])) {
            return;
        }
        
        $booking_id = absint($_GET['shb_download_calendar']);
        $token = sanitize_text_field(wp_unslash($_GET['shb_calendar_token'] ?? ''));
        $booking = SHB_Booking::get_booking($booking_id);

        if (!$booking || empty($token) || !hash_equals($booking['calendar_token'], $token)) {
            wp_die(
                esc_html__('You do not have permission to download this calendar event.', 'sanctuary-hotel-booking'),
                esc_html__('Access denied', 'sanctuary-hotel-booking'),
                array('response' => 403)
            );
        }

        SHB_Calendar::download_ics($booking_id);
    }
}
