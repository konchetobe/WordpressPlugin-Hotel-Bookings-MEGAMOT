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
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'list';

        $args = array();
        if ($status_filter) {
            $args['meta_query'] = array(
                array(
                    'key' => '_shb_booking_status',
                    'value' => $status_filter,
                ),
            );
        }

        $bookings = SHB_Booking::get_bookings($args);
        $rooms = SHB_Room::get_rooms(array('meta_query' => array()));

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
