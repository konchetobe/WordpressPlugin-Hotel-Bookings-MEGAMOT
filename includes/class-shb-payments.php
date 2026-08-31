<?php
/**
 * Payment Handler (Stripe & PayPal)
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Payments {

    /** How long a pending-payment hold lasts before the nightly rows are freed. */
    const HOLD_MINUTES = 30;

    /** REST namespace for payment webhooks. */
    const REST_NAMESPACE = 'sanctuary-hotel-booking/v1';

    /**
     * Register the Stripe webhook REST route.
     */
    public static function register_rest_routes() {
        register_rest_route(self::REST_NAMESPACE, '/stripe-webhook', array(
            'methods' => 'POST',
            'callback' => array(__CLASS__, 'handle_stripe_webhook'),
            'permission_callback' => '__return_true', // Signature-verified below.
        ));
    }
    
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
        
        // Set an expiring hold on the claimed nights (30 minutes).
        $hold_expires_at = gmdate('Y-m-d H:i:s', time() + self::HOLD_MINUTES * MINUTE_IN_SECONDS);
        update_post_meta($booking_id, '_shb_hold_expires_at', $hold_expires_at);
        SHB_Room_Nights::set_hold_expiry($booking_id, $hold_expires_at);
        
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
        
        $response = wp_remote_get('https://api.stripe.com/v1/checkout/sessions/' . rawurlencode($session_id), array(
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
     * Handle the Stripe webhook. This is the source of truth for payments;
     * redirect callbacks only surface the current status for UX.
     */
    public static function handle_stripe_webhook($request) {
        $payload = $request->get_body();
        $signature = $request->get_header('stripe_signature');

        if (empty($payload) || empty($signature)) {
            return new WP_REST_Response(array('error' => 'missing payload or signature'), 400);
        }

        $event = self::verify_webhook_signature($payload, $signature);
        if (is_wp_error($event)) {
            return new WP_REST_Response(array('error' => $event->get_error_message()), 400);
        }

        if ($event['type'] !== 'checkout.session.completed') {
            return new WP_REST_Response(array('received' => $event['type']), 200);
        }

        $session = $event['data']['object'] ?? array();
        $booking_id = absint($session['metadata']['booking_id'] ?? 0);
        $session_id = sanitize_text_field($session['id'] ?? '');

        if (!$booking_id || !$session_id) {
            return new WP_REST_Response(array('error' => 'session metadata missing booking_id'), 400);
        }

        if (($session['payment_status'] ?? '') === 'paid') {
            $result = self::confirm_from_webhook($booking_id, $session_id);
            if (is_wp_error($result)) {
                return new WP_REST_Response(array('error' => $result->get_error_message()), 400);
            }
        }

        return new WP_REST_Response(array('received' => true), 200);
    }

    /**
     * Verify the Stripe webhook signature (t=...,v1= HMAC-SHA256).
     */
    private static function verify_webhook_signature($payload, $header) {
        $test_mode = get_option('shb_stripe_test_mode', '1') === '1';
        $secret = $test_mode
            ? get_option('shb_stripe_test_webhook_secret', '')
            : get_option('shb_stripe_live_webhook_secret', '');

        if (empty($secret)) {
            return new WP_Error('no_webhook_secret', 'Stripe webhook secret not configured');
        }

        $parts = explode(',', $header);
        $timestamp = 0;
        $signatures = array();

        foreach ($parts as $part) {
            $kv = explode('=', trim($part), 2);
            if (count($kv) !== 2) {
                continue;
            }
            if ($kv[0] === 't') {
                $timestamp = intval($kv[1]);
            } elseif ($kv[0] === 'v1') {
                $signatures[] = $kv[1];
            }
        }

        if (!$timestamp || empty($signatures)) {
            return new WP_Error('invalid_signature', 'Malformed Stripe signature header');
        }

        // Reject signatures older than 5 minutes.
        if (abs(time() - $timestamp) > 5 * MINUTE_IN_SECONDS) {
            return new WP_Error('stale_signature', 'Stripe signature timestamp too old');
        }

        $signed_payload = $timestamp . '.' . $payload;
        $expected = hash_hmac('sha256', $signed_payload, $secret);

        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) {
                $event = json_decode($payload, true);
                if (is_array($event) && !empty($event['type'])) {
                    return $event;
                }
                return new WP_Error('invalid_payload', 'Payload is not a valid Stripe event');
            }
        }

        return new WP_Error('invalid_signature', 'Stripe signature verification failed');
    }

    /**
     * Confirm a booking from a verified webhook event.
     * Clears the hold and marks the booking paid/confirmed.
     */
    private static function confirm_from_webhook($booking_id, $session_id) {
        $booking = SHB_Booking::get_booking($booking_id);
        if (!$booking) {
            return new WP_Error('invalid_booking', 'Booking not found');
        }

        if (empty($booking['stripe_session_id']) || !hash_equals($booking['stripe_session_id'], $session_id)) {
            return new WP_Error('invalid_session', 'Payment session does not belong to this booking');
        }

        if ($booking['payment_status'] === 'paid') {
            return true;
        }

        // Clear the hold: the nights are now paid for.
        delete_post_meta($booking_id, '_shb_hold_expires_at');
        SHB_Room_Nights::set_hold_expiry($booking_id, null);

        SHB_Booking::update_payment_status($booking_id, 'paid');
        self::update_payment_transaction($session_id, 'paid');

        return true;
    }

    /**
     * Handle successful payment (redirect callback UX only).
     * The webhook remains the source of truth for confirming the booking.
     */
    public static function handle_payment_success($booking_id, $session_id) {
        $booking_id = absint($booking_id);
        $booking = SHB_Booking::get_booking($booking_id);

        if (!$booking) {
            return new WP_Error('invalid_booking', __('Booking not found', 'sanctuary-hotel-booking'));
        }

        if (empty($booking['stripe_session_id']) || !hash_equals($booking['stripe_session_id'], $session_id)) {
            return new WP_Error('invalid_session', __('Payment session does not belong to this booking', 'sanctuary-hotel-booking'));
        }

        // Verify payment with Stripe.
        $verification = self::verify_stripe_payment($session_id);
        
        if (is_wp_error($verification)) {
            return $verification;
        }
        
        if ((int) $verification['booking_id'] !== $booking_id) {
            return new WP_Error('session_mismatch', __('Payment session metadata does not match this booking', 'sanctuary-hotel-booking'));
        }

        if ($verification['payment_status'] === 'paid') {
            if ($booking['payment_status'] === 'paid') {
                return true;
            }

            // Reflect the paid status in the payment transaction if the
            // webhook hasn't arrived yet, but do NOT confirm the booking here.
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
