<?php
/**
 * Room Night Allocation
 *
 * The per-night inventory table makes double bookings impossible at the
 * database level via its UNIQUE(room_id, stay_date) index.
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Room_Nights {

    /**
     * Get the room nights table name.
     */
    public static function table() {
        global $wpdb;
        return $wpdb->prefix . 'shb_room_nights';
    }

    /**
     * Check whether a room is free for the whole stay.
     * Excludes nights claimed by $exclude_booking_id (e.g. rebooking).
     */
    public static function check_availability($room_id, $check_in, $check_out, $exclude_booking_id = null) {
        global $wpdb;
        $table = self::table();

        $sql = "SELECT COUNT(*) FROM $table
                WHERE room_id = %d
                AND stay_date >= %s AND stay_date < %s";

        $params = array($room_id, $check_in, $check_out);

        if ($exclude_booking_id) {
            $sql .= " AND booking_id != %d";
            $params[] = $exclude_booking_id;
        }

        $count = $wpdb->get_var($wpdb->prepare($sql, $params));

        return intval($count) === 0;
    }

    /**
     * Claim one row per night inside the caller's transaction.
     * Returns WP_Error on a duplicate (the unique index caught a race).
     */
    public static function claim_nights($room_id, $location_id, $booking_id, $check_in, $check_out, $hold_expires_at = null) {
        global $wpdb;
        $table = self::table();

        $date = new DateTimeImmutable($check_in);
        $end = new DateTimeImmutable($check_out);

        if ($end <= $date) {
            return new WP_Error('invalid_dates', __('Invalid stay dates.', 'sanctuary-hotel-booking'));
        }

        for ($d = $date; $d < $end; $d = $d->modify('+1 day')) {
            $result = $wpdb->insert($table, array(
                'room_id' => absint($room_id),
                'location_id' => absint($location_id),
                'booking_id' => absint($booking_id),
                'stay_date' => $d->format('Y-m-d'),
                'hold_expires_at' => $hold_expires_at,
            ));

            if ($result === false) {
                return new WP_Error(
                    'not_available',
                    __('Room is not available for the selected dates.', 'sanctuary-hotel-booking')
                );
            }
        }

        return true;
    }

    /**
     * Remove all nightly rows for a booking (cancellation).
     */
    public static function release_nights($booking_id) {
        global $wpdb;
        $table = self::table();

        return $wpdb->delete($table, array('booking_id' => absint($booking_id)));
    }

    /**
     * Update hold expiry for a booking's claimed nights.
     */
    public static function set_hold_expiry($booking_id, $hold_expires_at) {
        global $wpdb;
        $table = self::table();

        return $wpdb->update(
            $table,
            array('hold_expires_at' => $hold_expires_at),
            array('booking_id' => absint($booking_id))
        );
    }

    /**
     * Get the hold expiry for a booking from its claimed nights.
     */
    public static function get_hold_expiry($booking_id) {
        global $wpdb;
        $table = self::table();

        return $wpdb->get_var($wpdb->prepare(
            "SELECT hold_expires_at FROM $table WHERE booking_id = %d AND hold_expires_at IS NOT NULL LIMIT 1",
            $booking_id
        ));
    }

    /**
     * Release nights whose payment holds have expired and cancel their
     * bookings. Scheduled hourly. Returns the number of freed nights.
     */
    public static function clear_expired_holds() {
        global $wpdb;
        $table = self::table();

        $expired = $wpdb->get_col(
            "SELECT DISTINCT booking_id FROM $table
             WHERE hold_expires_at IS NOT NULL AND hold_expires_at < NOW()"
        );

        if (empty($expired)) {
            return 0;
        }

        $freed = 0;
        foreach ($expired as $booking_id) {
            // Only cancel bookings still awaiting payment.
            $status = get_post_meta($booking_id, '_shb_booking_status', true);
            $payment = get_post_meta($booking_id, '_shb_payment_status', true);

            if ($payment === 'paid') {
                continue;
            }

            $freed += intval($wpdb->delete($table, array('booking_id' => $booking_id)));

            if ($status !== 'cancelled') {
                update_post_meta($booking_id, '_shb_booking_status', 'cancelled');
                update_post_meta($booking_id, '_shb_payment_status', 'expired');
            }
        }

        return $freed;
    }

    /**
     * Get the occupied night rows for a date range, optionally filtered by location.
     */
    public static function get_occupied_nights($start_date, $end_date, $location_id = 0) {
        global $wpdb;
        $table = self::table();

        $sql = "SELECT * FROM $table WHERE stay_date >= %s AND stay_date < %s";
        $params = array($start_date, $end_date);

        if ($location_id) {
            $sql .= " AND location_id = %d";
            $params[] = absint($location_id);
        }

        $sql .= " ORDER BY stay_date ASC";

        return $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);
    }
}
