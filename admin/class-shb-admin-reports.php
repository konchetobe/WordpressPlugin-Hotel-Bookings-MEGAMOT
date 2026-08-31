<?php
/**
 * Admin Reports Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Admin_Reports {

    public static function render_page() {
        if (!current_user_can('shb_view_reports') && !current_user_can('manage_options')) {
            wp_die(__('You do not have permission to view reports.', 'sanctuary-hotel-booking'));
        }

        $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : gmdate('Y-m-d', strtotime('-30 days'));
        $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : gmdate('Y-m-d');
        $location_filter = isset($_GET['location']) ? absint($_GET['location']) : 0;

        if ($end_date < $start_date) {
            $end_date = $start_date;
        }

        $locations = SHB_Location::get_locations();
        $rooms = SHB_Room::get_rooms(array('meta_query' => array()));
        $room_map = array();
        foreach ($rooms as $room) {
            $room_map[$room['id']] = $room;
        }

        // Occupancy from the room-nights table.
        $occupied = SHB_Room_Nights::get_occupied_nights($start_date, $end_date, $location_filter);

        $occupancy_by_location = array();
        $occupancy_by_room = array();
        foreach ($occupied as $night) {
            $lid = intval($night['location_id']);
            $rid = intval($night['room_id']);
            if (!isset($occupancy_by_location[$lid])) {
                $occupancy_by_location[$lid] = 0;
            }
            $occupancy_by_location[$lid]++;

            $key = $lid . ':' . $rid;
            if (!isset($occupancy_by_room[$key])) {
                $occupancy_by_room[$key] = 0;
            }
            $occupancy_by_room[$key]++;
        }

        // Revenue from paid bookings.
        $revenue_by_location = array();
        $count_by_location = array();
        $bookings_args = array(
            'meta_query' => array(
                array(
                    'key' => '_shb_payment_status',
                    'value' => 'paid',
                ),
                array(
                    'key' => '_shb_booking_date',
                    'value' => $start_date,
                    'compare' => '>=',
                    'type' => 'DATE',
                ),
                array(
                    'key' => '_shb_booking_date',
                    'value' => $end_date . ' 23:59:59',
                    'compare' => '<=',
                    'type' => 'DATETIME',
                ),
            ),
        );
        if ($location_filter) {
            $bookings_args['location_id'] = $location_filter;
        }

        foreach (SHB_Booking::get_bookings($bookings_args) as $booking) {
            $lid = intval($booking['location_id']);
            if (!isset($revenue_by_location[$lid])) {
                $revenue_by_location[$lid] = 0;
                $count_by_location[$lid] = 0;
            }
            $revenue_by_location[$lid] += floatval($booking['total_price']);
            $count_by_location[$lid]++;
        }

        // Days in range for occupancy percentage (timezone-correct per location).
        $days_in_range = max(1, (new DateTime($end_date))->diff(new DateTime($start_date))->days + 1);

        include SHB_PLUGIN_DIR . 'admin/views/reports.php';
    }
}
