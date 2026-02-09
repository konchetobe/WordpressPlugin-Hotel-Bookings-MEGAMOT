<?php
/**
 * Shortcodes Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Shortcodes {
    
    public static function init() {
        add_shortcode('shb_booking_form', array(__CLASS__, 'booking_form'));
        add_shortcode('shb_room_list', array(__CLASS__, 'room_list'));
        add_shortcode('shb_room_search', array(__CLASS__, 'room_search'));
        add_shortcode('shb_booking_confirmation', array(__CLASS__, 'booking_confirmation'));
        add_shortcode('shb_my_bookings', array(__CLASS__, 'my_bookings'));
    }
    
    /**
     * Room Search Shortcode [shb_room_search]
     */
    public static function room_search($atts) {
        $atts = shortcode_atts(array(
            'style' => 'default',
        ), $atts);
        
        ob_start();
        include SHB_PLUGIN_DIR . 'templates/room-search.php';
        return ob_get_clean();
    }
    
    /**
     * Room List Shortcode [shb_room_list columns="3" type="suite" limit="6"]
     */
    public static function room_list($atts) {
        $atts = shortcode_atts(array(
            'type' => '',
            'limit' => -1,
            'columns' => 3,
        ), $atts);
        
        $args = array(
            'posts_per_page' => intval($atts['limit']),
        );
        
        if (!empty($atts['type'])) {
            $rooms = SHB_Room::get_rooms_by_type($atts['type']);
            if (empty($rooms)) {
                $rooms = SHB_Room::get_rooms(array(
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'shb_room_type',
                            'field' => 'slug',
                            'terms' => sanitize_text_field($atts['type']),
                        ),
                    ),
                    'posts_per_page' => intval($atts['limit']),
                ));
            }
        } else {
            $rooms = SHB_Room::get_rooms($args);
        }
        
        ob_start();
        include SHB_PLUGIN_DIR . 'templates/room-list.php';
        return ob_get_clean();
    }
    
    /**
     * Booking Form Shortcode [shb_booking_form room_id="123"]
     */
    public static function booking_form($atts) {
        $atts = shortcode_atts(array(
            'room_id' => 0,
        ), $atts);
        
        $room_id = intval($atts['room_id']);
        if (!$room_id) {
            $room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : 0;
        }
        
        if (!$room_id) {
            return '<p class="shb-error">' . __('Please select a room to book.', 'sanctuary-hotel-booking') . '</p>';
        }
        
        $room = SHB_Room::get_room($room_id);
        if (!$room) {
            return '<p class="shb-error">' . __('Room not found.', 'sanctuary-hotel-booking') . '</p>';
        }
        
        $check_in = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : '';
        $check_out = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : '';
        $guests = isset($_GET['guests']) ? intval($_GET['guests']) : 1;
        
        // Auto-fill from logged-in user
        $prefill = array(
            'first_name' => '',
            'last_name' => '',
            'email' => '',
        );
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            $prefill['first_name'] = $user->first_name;
            $prefill['last_name'] = $user->last_name;
            $prefill['email'] = $user->user_email;
        }
        
        ob_start();
        include SHB_PLUGIN_DIR . 'templates/booking-form.php';
        return ob_get_clean();
    }
    
    /**
     * Booking Confirmation Shortcode [shb_booking_confirmation]
     */
    public static function booking_confirmation($atts) {
        $booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
        $booking_ref = isset($_GET['booking_ref']) ? sanitize_text_field($_GET['booking_ref']) : '';
        
        if ($booking_ref) {
            $booking = SHB_Booking::get_booking_by_ref($booking_ref);
        } elseif ($booking_id) {
            $booking = SHB_Booking::get_booking($booking_id);
        } else {
            return '<p class="shb-error">' . __('Booking not found.', 'sanctuary-hotel-booking') . '</p>';
        }
        
        if (!$booking) {
            return '<p class="shb-error">' . __('Booking not found.', 'sanctuary-hotel-booking') . '</p>';
        }
        
        if (isset($_GET['session_id'])) {
            SHB_Payments::handle_payment_success($booking['id'], sanitize_text_field($_GET['session_id']));
            $booking = SHB_Booking::get_booking($booking['id']);
        }
        
        ob_start();
        include SHB_PLUGIN_DIR . 'templates/booking-confirmation.php';
        return ob_get_clean();
    }
    
    /**
     * My Bookings Shortcode [shb_my_bookings]
     * Requires login — auto-uses the logged-in user's email
     */
    public static function my_bookings($atts) {
        // Require login
        if (!is_user_logged_in()) {
            ob_start();
            ?>
            <div class="shb-my-bookings" data-testid="shb-my-bookings">
                <div class="shb-lookup-card">
                    <div class="shb-lookup-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <h3><?php _e('Login Required', 'sanctuary-hotel-booking'); ?></h3>
                    <p><?php _e('Please log in to view your bookings.', 'sanctuary-hotel-booking'); ?></p>
                    <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="shb-button shb-button-primary">
                        <?php _e('Log In', 'sanctuary-hotel-booking'); ?>
                    </a>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }
        
        // Get bookings for the logged-in user's email
        $user = wp_get_current_user();
        $email = $user->user_email;
        $bookings = array();
        
        $all_bookings = SHB_Booking::get_bookings();
        foreach ($all_bookings as $booking) {
            if (isset($booking['email']) && strtolower($booking['email']) === strtolower($email)) {
                $bookings[] = $booking;
            }
        }
        
        ob_start();
        include SHB_PLUGIN_DIR . 'templates/my-bookings.php';
        return ob_get_clean();
    }
}
