<?php
/**
 * Sanity checks for the multi-location feature set.
 *
 * Usage (WP-CLI):
 *   wp eval-file tests/verify.php
 *
 * Prints PASS/FAIL lines and exits non-zero on failure.
 */

if (!defined('ABSPATH') && !defined('WP_CLI')) {
    exit;
}

$failures = 0;

function shb_verify($label, $ok, $detail = '') {
    global $failures;
    if ($ok) {
        echo "PASS  {$label}\n";
    } else {
        echo "FAIL  {$label}" . ($detail ? " — {$detail}" : '') . "\n";
        $failures++;
    }
}

// 1. Room nights table exists.
global $wpdb;
$table = $wpdb->prefix . 'shb_room_nights';
$exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table;
shb_verify('room_nights table exists', $exists);

// 2. Pricing rules table has the scope columns.
$pricing = $wpdb->prefix . 'shb_pricing_rules';
$cols = $wpdb->get_col("SHOW COLUMNS FROM {$pricing}");
shb_verify('pricing_rules.location_id', in_array('location_id', $cols, true));
shb_verify('pricing_rules.room_id', in_array('room_id', $cols, true));
shb_verify('pricing_rules.room_type_term_id', in_array('room_type_term_id', $cols, true));

// 3. Default location exists.
$default_id = SHB_Location::get_default_location_id();
shb_verify('default location exists', $default_id > 0);

// 4. Every room has a location.
$rooms = get_posts(array('post_type' => 'shb_room', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids'));
$rooms_without_location = 0;
foreach ($rooms as $room_id) {
    if (!absint(get_post_meta($room_id, '_shb_location_id', true))) {
        $rooms_without_location++;
    }
}
shb_verify('all rooms have a location', $rooms_without_location === 0, "{$rooms_without_location} without");

// 5. Room nights backfilled for active bookings.
$bookings = get_posts(array('post_type' => 'shb_booking', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids'));
$missing_nights = 0;
$checked = 0;
foreach ($bookings as $booking_id) {
    if (get_post_meta($booking_id, '_shb_booking_status', true) === 'cancelled') {
        continue;
    }
    $nights = SHB_Booking::calculate_nights(
        get_post_meta($booking_id, '_shb_check_in', true),
        get_post_meta($booking_id, '_shb_check_out', true)
    );
    if ($nights < 1) {
        continue;
    }
    $checked++;
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE booking_id = %d",
        $booking_id
    ));
    if (intval($count) !== $nights) {
        $missing_nights++;
    }
}
shb_verify('active bookings have matching night rows', $missing_nights === 0, "{$missing_nights} mismatched of {$checked}");

// 6. Booking snapshots present.
$snapshots_missing = 0;
foreach ($bookings as $booking_id) {
    if (!get_post_meta($booking_id, '_shb_location_id', true) || !get_post_meta($booking_id, '_shb_location_name', true)) {
        $snapshots_missing++;
    }
}
shb_verify('bookings have location snapshots', $snapshots_missing === 0, "{$snapshots_missing} missing");

// 7. Booking creation writes nights.
$test_room = get_posts(array('post_type' => 'shb_room', 'posts_per_page' => 1, 'fields' => 'ids'));
if (!empty($test_room)) {
    $room = SHB_Room::get_room($test_room[0]);
    $check_in = gmdate('Y-m-d', strtotime('+30 days'));
    $check_out = gmdate('Y-m-d', strtotime('+33 days'));
    $result = SHB_Booking::create_booking(array(
        'room_id' => $room['id'],
        'check_in' => $check_in,
        'check_out' => $check_out,
        'guests' => 1,
        'first_name' => 'Verify',
        'last_name' => 'Tester',
        'email' => 'verify@example.test',
        'phone' => '000',
    ));

    if (is_wp_error($result)) {
        shb_verify('test booking created', false, $result->get_error_message());
    } else {
        $night_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE booking_id = %d",
            $result['booking_id']
        ));
        shb_verify('test booking claims 3 nights', intval($night_count) === 3, "got {$night_count}");

        // Cancel frees the nights.
        SHB_Booking::update_status($result['booking_id'], 'cancelled');
        $after_cancel = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table} WHERE booking_id = %d",
            $result['booking_id']
        ));
        shb_verify('cancellation frees nights', intval($after_cancel) === 0, "got {$after_cancel}");
    }
} else {
    echo "SKIP  test booking (no rooms present)\n";
}

// 8. Roles registered.
shb_verify('administrator has shb caps', user_can(wp_get_current_user(), 'shb_manage_bookings'));
shb_verify('location manager role exists', !is_null(get_role('shb_location_manager')));

echo $failures === 0 ? "\nAll checks passed.\n" : "\n{$failures} check(s) failed.\n";
exit($failures === 0 ? 0 : 1);
