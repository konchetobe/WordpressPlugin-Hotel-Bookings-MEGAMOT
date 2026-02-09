<?php
/**
 * Availability Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Availability {
    
    /**
     * Check if room is available for given dates
     */
    public static function check_room_availability($room_id, $check_in, $check_out, $exclude_booking_id = null) {
        // Check availability blocks
        if (self::has_availability_block($room_id, $check_in, $check_out)) {
            return false;
        }
        
        // Check existing bookings
        if (self::has_conflicting_booking($room_id, $check_in, $check_out, $exclude_booking_id)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check for availability blocks
     */
    public static function has_availability_block($room_id, $check_in, $check_out) {
        global $wpdb;
        $table = $wpdb->prefix . 'shb_availability_blocks';
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table 
             WHERE room_id = %d 
             AND NOT (end_date <= %s OR start_date >= %s)",
            $room_id,
            $check_in,
            $check_out
        ));
        
        return intval($count) > 0;
    }
    
    /**
     * Check for conflicting bookings
     */
    public static function has_conflicting_booking($room_id, $check_in, $check_out, $exclude_booking_id = null) {
        $meta_query = array(
            'relation' => 'AND',
            array(
                'key' => '_shb_room_id',
                'value' => $room_id,
            ),
            array(
                'key' => '_shb_booking_status',
                'value' => 'cancelled',
                'compare' => '!=',
            ),
        );
        
        $bookings = get_posts(array(
            'post_type' => 'shb_booking',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => $meta_query,
            'exclude' => $exclude_booking_id ? array($exclude_booking_id) : array(),
        ));
        
        $check_in_date = new DateTime($check_in);
        $check_out_date = new DateTime($check_out);
        
        foreach ($bookings as $booking) {
            $booking_check_in = new DateTime(get_post_meta($booking->ID, '_shb_check_in', true));
            $booking_check_out = new DateTime(get_post_meta($booking->ID, '_shb_check_out', true));
            
            // Check for overlap
            if (!($check_out_date <= $booking_check_in || $check_in_date >= $booking_check_out)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get availability blocks for a room
     */
    public static function get_availability_blocks($room_id = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'shb_availability_blocks';
        
        if ($room_id) {
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table WHERE room_id = %d ORDER BY start_date ASC",
                $room_id
            ), ARRAY_A);
        } else {
            $results = $wpdb->get_results(
                "SELECT * FROM $table ORDER BY start_date ASC",
                ARRAY_A
            );
        }
        
        return $results;
    }
    
    /**
     * Create availability block
     */
    public static function create_availability_block($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'shb_availability_blocks';
        
        $result = $wpdb->insert($table, array(
            'room_id' => intval($data['room_id']),
            'start_date' => sanitize_text_field($data['start_date']),
            'end_date' => sanitize_text_field($data['end_date']),
            'reason' => sanitize_text_field($data['reason'] ?? 'maintenance'),
        ));
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create availability block', 'sanctuary-hotel-booking'));
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Delete availability block
     */
    public static function delete_availability_block($block_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'shb_availability_blocks';
        
        return $wpdb->delete($table, array('id' => $block_id));
    }
    
    /**
     * Get blocked dates for a room (for calendar display)
     */
    public static function get_blocked_dates($room_id, $start_date, $end_date) {
        $blocked_dates = array();
        
        // Get availability blocks
        global $wpdb;
        $table = $wpdb->prefix . 'shb_availability_blocks';
        
        $blocks = $wpdb->get_results($wpdb->prepare(
            "SELECT start_date, end_date FROM $table 
             WHERE room_id = %d 
             AND start_date <= %s 
             AND end_date >= %s",
            $room_id,
            $end_date,
            $start_date
        ), ARRAY_A);
        
        foreach ($blocks as $block) {
            $blocked_dates[] = array(
                'start' => $block['start_date'],
                'end' => $block['end_date'],
                'type' => 'blocked',
            );
        }
        
        // Get bookings
        $bookings = SHB_Booking::get_bookings_by_room($room_id);
        foreach ($bookings as $booking) {
            if ($booking['booking_status'] !== 'cancelled') {
                $blocked_dates[] = array(
                    'start' => $booking['check_in'],
                    'end' => $booking['check_out'],
                    'type' => 'booked',
                );
            }
        }
        
        return $blocked_dates;
    }
}
