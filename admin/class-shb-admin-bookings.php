<?php
/**
 * Admin Bookings Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Admin_Bookings
{

    public static function render_page()
    {
        $is_admin = current_user_can('manage_options');
        if (!$is_admin && !current_user_can('shb_manage_bookings')) {
            wp_die(__('You do not have permission to view bookings.', 'sanctuary-hotel-booking'));
        }

        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'list';

        // Non-admins only see their assigned locations' bookings.
        $allowed_location_ids = null;
        if (!$is_admin) {
            $allowed_location_ids = wp_list_pluck(SHB_Location::get_locations_for_user(), 'id');
        }

        $location_filter = isset($_GET['location']) ? absint($_GET['location']) : 0;
        if (is_array($allowed_location_ids) && $location_filter && !in_array($location_filter, $allowed_location_ids, true)) {
            $location_filter = 0;
        }

        $args = array();
        if ($status_filter) {
            $args['meta_query'] = array(
                array(
                    'key' => '_shb_booking_status',
                    'value' => $status_filter,
                ),
            );
        }

        if ($location_filter) {
            $args['location_id'] = $location_filter;
        } elseif (is_array($allowed_location_ids) && !empty($allowed_location_ids)) {
            // Constrain to all assigned locations.
            $args['location_ids'] = $allowed_location_ids;
        } elseif (is_array($allowed_location_ids)) {
            // Manager with no assignments sees no bookings.
            $args['meta_query'][] = array('key' => '_shb_location_id', 'value' => -1);
        }

        $bookings = SHB_Booking::get_bookings($args);

        // Rooms for the calendar/legend are also scoped for non-admins.
        $room_args = array('meta_query' => array());
        if (!$is_admin && is_array($allowed_location_ids) && !empty($allowed_location_ids)) {
            $room_args = array('all' => 1, 'location_ids' => $allowed_location_ids);
        }
        $rooms = SHB_Room::get_rooms($room_args);

        $locations = $is_admin ? SHB_Location::get_locations() : SHB_Location::get_locations_for_user();

        // Build room color map
        $room_colors = array();
        foreach ($rooms as $room) {
            $room_colors[$room['id']] = SHB_Admin::get_room_color($room['id']);
        }

        // Fetch availability blocks for calendar display
        $availability_blocks = SHB_Availability::get_availability_blocks();

        include SHB_PLUGIN_DIR . 'admin/views/bookings.php';
    }
}
