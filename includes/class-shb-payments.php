<?php
/**
 * Payment Handler (Stripe & PayPal)
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Payments {
    
    /**
     * Create Stripe checkout session
     */
    public static function create_stripe_checkout($booking_id) {
        $booking = SHB_Booking::get_booking($booking_id);
        if (!$booking) {
            return new WP_Error('invalid_booking', __('Booking not found', 'sanctuary-hotel-booking'));
        }
        
        // Get Stripe keys
        $test_mode = get_option('shb_stripe_test_mode', '1') === '1';
        $secret_key = $test_mode 
            ? get_option('shb_stripe_test_secret_key', '') 
            : get_option('shb_stripe_live_secret_key', '');
        
        if (empty($secret_key)) {
            return new WP_Error('no_api_key', __('Stripe API key not configured', 'sanctuary-hotel-booking'));
        }
        
        // Build success and cancel URLs
        $success_url = add_query_arg(array(
            'shb_action' => 'payment_success',
            'booking_id' => $booking_id,
            'session_id' => '{CHECKOUT_SESSION_ID}',
        ), home_url());
        
        $cancel_url = add_query_arg(array(
            'shb_action' => 'payment_cancel',
            'booking_id' => $booking_id,
        ), home_url());
        
        // Create Stripe checkout session via API
        $response = wp_remote_post('https://api.stripe.com/v1/checkout/sessions', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $secret_key,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ),
            'body' => array(
                'payment_method_types[]' => 'card',
                'line_items[0][price_data][currency]' => strtolower(get_option('shb_currency', 'USD')),
                'line_items[0][price_data][product_data][name]' => sprintf(
                    __('Hotel Booking - %s', 'sanctuary-hotel-booking'),
                    $booking['room_name']
                ),
                'line_items[0][price_data][product_data][description]' => sprintf(
                    __('%s to %s (%d guests)', 'sanctuary-hotel-booking'),
                    $booking['check_in'],
                    $booking['check_out'],
                    $booking['guests']
                ),
                'line_items[0][price_data][unit_amount]' => intval($booking['total_price'] * 100),
                'line_items[0][quantity]' => 1,
                'mode' => 'payment',
                'success_url' => str_replace('{CHECKOUT_SESSION_ID}', '{CHECKOUT_SESSION_ID}', $success_url),
                'cancel_url' => $cancel_url,
                'customer_email' => $booking['email'],
                'metadata[booking_id]' => $booking_id,
                'metadata[booking_ref]' => $booking['booking_ref'],
            ),
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return new WP_Error('stripe_error', $body['error']['message']);
        }
        
        // Save session ID to booking
        update_post_meta($booking_id, '_shb_stripe_session_id', $body['id']);
        
        // Create payment transaction record
        self::create_payment_transaction($booking_id, array(
            'session_id' => $body['id'],
            'amount' => $booking['total_price'],
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
        ));
        
        return array(
            'checkout_url' => $body['url'],
            'session_id' => $body['id'],
        );
    }
    
    /**
     * Verify Stripe payment
     */
    public static function verify_stripe_payment($session_id) {
        $test_mode = get_option('shb_stripe_test_mode', '1') === '1';
        $secret_key = $test_mode 
            ? get_option('shb_stripe_test_secret_key', '') 
            : get_option('shb_stripe_live_secret_key', '');
        
        if (empty($secret_key)) {
            return new WP_Error('no_api_key', __('Stripe API key not configured', 'sanctuary-hotel-booking'));
        }
        
        $response = wp_remote_get('https://api.stripe.com/v1/checkout/sessions/' . $session_id, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $secret_key,
            ),
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (isset($body['error'])) {
            return new WP_Error('stripe_error', $body['error']['message']);
        }
        
        return array(
            'status' => $body['status'],
            'payment_status' => $body['payment_status'],
            'booking_id' => $body['metadata']['booking_id'] ?? null,
        );
    }
    
    /**
     * Handle successful payment
     */
    public static function handle_payment_success($booking_id, $session_id) {
        // Verify payment with Stripe
        $verification = self::verify_stripe_payment($session_id);
        
        if (is_wp_error($verification)) {
            return $verification;
        }
        
        if ($verification['payment_status'] === 'paid') {
            // Update booking payment status
            SHB_Booking::update_payment_status($booking_id, 'paid');
            
            // Update payment transaction
            self::update_payment_transaction($session_id, 'paid');
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Create payment transaction record
     */
    public static function create_payment_transaction($booking_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'shb_payment_transactions';
        
        return $wpdb->insert($table, array(
            'booking_id' => $booking_id,
            'session_id' => $data['session_id'],
            'amount' => $data['amount'],
            'currency' => get_option('shb_currency', 'USD'),
            'payment_method' => $data['payment_method'],
            'payment_status' => $data['payment_status'],
            'metadata' => json_encode($data),
        ));
    }
    
    /**
     * Update payment transaction status
     */
    public static function update_payment_transaction($session_id, $status) {
        global $wpdb;
        $table = $wpdb->prefix . 'shb_payment_transactions';
        
        return $wpdb->update(
            $table,
            array('payment_status' => $status),
            array('session_id' => $session_id)
        );
    }
    
    /**
     * Get PayPal checkout URL (placeholder for future implementation)
     */
    public static function create_paypal_checkout($booking_id) {
        return new WP_Error('not_implemented', __('PayPal integration coming soon', 'sanctuary-hotel-booking'));
    }
}
