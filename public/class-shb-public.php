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
                SHB_Payments::handle_payment_success($booking_id, $session_id);
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
        
        $booking_id = intval($_GET['shb_download_calendar']);
        if ($booking_id) {
            SHB_Calendar::download_ics($booking_id);
        }
    }
}
