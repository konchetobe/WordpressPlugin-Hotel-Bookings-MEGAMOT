<?php
/**
 * Shortcodes Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Shortcodes
{

    public static function init()
    {
        add_shortcode('shb_booking_form', array(__CLASS__, 'booking_form'));
        add_shortcode('shb_room_list', array(__CLASS__, 'room_list'));
        add_shortcode('shb_room_search', array(__CLASS__, 'room_search'));
        add_shortcode('shb_booking_confirmation', array(__CLASS__, 'booking_confirmation'));
        add_shortcode('shb_my_bookings', array(__CLASS__, 'my_bookings'));

        // Public property pages for locations + rich single-room pages.
        add_filter('the_content', array(__CLASS__, 'location_page_content'));
        add_filter('the_content', array(__CLASS__, 'room_page_content'));
    }

    /**
     * Render a rich property page on single shb_room posts (replaces the raw
     * post content with the structured room layout + booking form).
     */
    public static function room_page_content($content)
    {
        if (!is_singular('shb_room') || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        $room = SHB_Room::get_room(get_the_ID());
        if (!$room) {
            return $content;
        }

        $gallery_ids = (array) get_post_meta($room['id'], '_shb_gallery', true);
        $currency = !empty($room['location']['currency_symbol']) ? $room['location']['currency_symbol'] : get_option('shb_currency_symbol', '$');

        ob_start();
        include SHB_PLUGIN_DIR . 'templates/room-page.php';
        return ob_get_clean();
    }

    /**
     * Render a location property page on single shb_location posts.
     */
    public static function location_page_content($content)
    {
        if (!is_singular('shb_location') || !in_the_loop() || !is_main_query()) {
            return $content;
        }

        $location = SHB_Location::get_location(get_the_ID());
        if (!$location) {
            return $content;
        }

        $rooms = SHB_Room::get_rooms(array('location_id' => $location['id']));

        // Per-room-type summary of active rooms at this location.
        $type_map = array();
        foreach (SHB_Room_Type::get_room_types() as $type) {
            $type_map[$type['slug']] = $type;
        }

        $room_types = array();
        foreach ($rooms as $room) {
            if (!$room['is_active']) {
                continue;
            }
            $slug = $room['room_type'];
            if (!isset($room_types[$slug])) {
                $type_data = isset($type_map[$slug]) ? $type_map[$slug] : array('name' => ucfirst($slug), 'description' => '');
                $room_types[$slug] = array(
                    'slug' => $slug,
                    'name' => $type_data['name'],
                    'description' => isset($type_data['description']) ? $type_data['description'] : '',
                    'count' => 0,
                    'min_price' => PHP_FLOAT_MAX,
                    'image' => '',
                );
            }
            $room_types[$slug]['count']++;
            $room_types[$slug]['min_price'] = min($room_types[$slug]['min_price'], $room['base_price']);
            if (empty($room_types[$slug]['image']) && !empty($room['image'])) {
                $room_types[$slug]['image'] = $room['image'];
            }
        }

        foreach ($room_types as $slug => $type) {
            if ($type['min_price'] === PHP_FLOAT_MAX) {
                $room_types[$slug]['min_price'] = 0;
            }
        }
        $room_types = array_values($room_types);

        // Search scoped to this location, honoring a ?type= deep link.
        $location_id = $location['id'];
        $room_type_filter = isset($_GET['type']) ? sanitize_title($_GET['type']) : '';
        $all_room_types = SHB_Room_Type::get_room_types();
        $filter_options = SHB_Room::get_search_filter_options($location_id);

        ob_start();
        $locations = array($location);
        include SHB_PLUGIN_DIR . 'templates/location-page.php';
        $property_content = ob_get_clean();

        return $content . $property_content;
    }

    /**
     * Room Search Shortcode [shb_room_search]
     */
    public static function room_search($atts)
    {
        $atts = shortcode_atts(array(
            'style' => 'default',
            'location' => '',
        ), $atts);

        $locations = SHB_Location::get_active_locations();
        $location = absint($atts['location']);
        if (!$location && isset($_GET['location'])) {
            $location = absint($_GET['location']);
        }

        // Honor the default-location behavior for shortcodes without a location.
        if (!$location && get_option('shb_default_location_behavior', 'all') === 'default') {
            $default_id = SHB_Location::get_default_location_id();
            if ($default_id) {
                $location = $default_id;
            }
        }

        // Template variables used by room-search.php: a scoped location ID,
        // the requested room type (query arg or preset), and the chip lists.
        $location_id = $location;
        $room_type_filter = isset($_GET['type']) ? sanitize_title($_GET['type']) : '';
        $all_room_types = SHB_Room_Type::get_room_types();
        $filter_options = SHB_Room::get_search_filter_options($location_id);

        ob_start();
        include SHB_PLUGIN_DIR . 'templates/room-search.php';
        return ob_get_clean();
    }

    /**
     * Room List Shortcode [shb_room_list columns="3" type="suite" limit="6" location="12"]
     */
    public static function room_list($atts)
    {
        $atts = shortcode_atts(array(
            'type' => '',
            'limit' => -1,
            'columns' => 3,
            'location' => '',
        ), $atts);

        $args = array(
            'posts_per_page' => intval($atts['limit']),
        );

        if (!empty($atts['location'])) {
            $args['location_id'] = absint($atts['location']);
        } elseif (get_option('shb_default_location_behavior', 'all') === 'default') {
            $default_id = SHB_Location::get_default_location_id();
            if ($default_id) {
                $args['location_id'] = $default_id;
            }
        }

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
    public static function booking_form($atts)
    {
        $atts = shortcode_atts(array(
            'room_id' => 0,
            'location' => '',
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

        $location = absint($atts['location']);
        if (!$location && isset($_GET['location'])) {
            $location = absint($_GET['location']);
        }

        // A room belongs to exactly one location. When a location is passed but
        // does not match, fall back to the room's own location instead of
        // erroring — deep links from mixed-location lists can carry a stale one.
        if ($room['location_id'] && $location && $location !== $room['location_id']) {
            $location = $room['location_id'];
        }
        if (!$location && $room['location_id']) {
            $location = $room['location_id'];
        }

        // Location-specific currency for the summary (falls back to global).
        $location_data = $location ? SHB_Location::get_location($location) : null;
        $currency_symbol = $location_data ? $location_data['currency_symbol'] : get_option('shb_currency_symbol', '$');

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

        // Get blocked dates for this room (availability blocks + existing bookings)
        $blocked_dates = array();

        // Get availability blocks
        $blocks = SHB_Availability::get_availability_blocks($room_id);
        foreach ($blocks as $block) {
            $blocked_dates[] = array(
                'start' => $block['start_date'],
                'end' => $block['end_date'],
                'type' => 'blocked'
            );
        }

        // Get existing bookings (non-cancelled)
        $bookings = SHB_Booking::get_bookings_by_room($room_id);
        foreach ($bookings as $booking) {
            if ($booking['booking_status'] !== 'cancelled') {
                $blocked_dates[] = array(
                    'start' => $booking['check_in'],
                    'end' => $booking['check_out'],
                    'type' => 'booked'
                );
            }
        }

        ob_start();
        include SHB_PLUGIN_DIR . 'templates/booking-form.php';
        return ob_get_clean();
    }

    /**
     * Booking Confirmation Shortcode [shb_booking_confirmation]
     */
    public static function booking_confirmation($atts)
    {
        $booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
        $booking_ref = isset($_GET['booking_ref']) ? sanitize_text_field($_GET['booking_ref']) : '';
        $booking_token = isset($_GET['booking_token']) ? sanitize_text_field(wp_unslash($_GET['booking_token'])) : '';

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

        if (empty($booking_token) || !hash_equals($booking['calendar_token'], $booking_token)) {
            return '<p class="shb-error">' . __('Booking not found.', 'sanctuary-hotel-booking') . '</p>';
        }

        if (isset($_GET['session_id'])) {
            SHB_Payments::handle_payment_success($booking['id'], sanitize_text_field($_GET['session_id']));
            $booking = SHB_Booking::get_booking($booking['id']);
        }

        // Location-specific times and currency for the confirmation page.
        $location = $booking['location_id'] ? SHB_Location::get_location($booking['location_id']) : null;
        $check_in_time = $location ? $location['check_in_time'] : get_option('shb_check_in_time', '14:00');
        $check_out_time = $location ? $location['check_out_time'] : get_option('shb_check_out_time', '11:00');
        $currency_symbol = $location ? $location['currency_symbol'] : get_option('shb_currency_symbol', '$');

        ob_start();
        include SHB_PLUGIN_DIR . 'templates/booking-confirmation.php';
        return ob_get_clean();
    }

    /**
     * My Bookings Shortcode [shb_my_bookings]
     * Requires login — auto-uses the logged-in user's email
     */
    public static function my_bookings($atts)
    {
        // Require login
        if (!is_user_logged_in()) {
            ob_start();
            ?>
            <div class="shb-my-bookings" data-testid="shb-my-bookings">
                <div class="shb-lookup-card">
                    <div class="shb-lookup-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.5">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
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
