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

// Admin-only classes are not autoloaded outside is_admin() (WP-CLI eval runs
// in a front-end context), so load the ones the checks below use directly.
if (defined('WP_CLI') && WP_CLI) {
    $shb_plugin_dir = dirname(__DIR__);
    $shb_admin_files = array(
        'SHB_Admin_Locations' => 'admin/class-shb-admin-locations.php',
        'SHB_Admin_Rooms'     => 'admin/class-shb-admin-rooms.php',
    );
    foreach ($shb_admin_files as $shb_class => $shb_file) {
        if (!class_exists($shb_class, false)) {
            require_once $shb_plugin_dir . '/' . $shb_file;
        }
    }
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
// Pick an ACTIVE room (the first raw shb_room post may be an inactive leftover
// from earlier verify runs).
$test_room = get_posts(array(
    'post_type' => 'shb_room',
    'posts_per_page' => 1,
    'fields' => 'ids',
    'meta_key' => '_shb_is_active',
    'meta_value' => '1',
));
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
// WP-CLI runs as no user, so check an actual administrator account instead of
// the (anonymous) current user.
$shb_admin_check_user = get_current_user_id();
if (!$shb_admin_check_user) {
    $shb_admins = get_users(array('role' => 'administrator', 'number' => 1));
    if (!empty($shb_admins)) {
        $shb_admin_check_user = $shb_admins[0]->ID;
    }
}
shb_verify('administrator has shb caps', $shb_admin_check_user && user_can($shb_admin_check_user, 'shb_manage_bookings'));
shb_verify('location manager role exists', !is_null(get_role('shb_location_manager')));

// 9. Room type defaults round-trip (create/read/delete on a throwaway term).
$test_term = wp_insert_term('Verify Type ' . wp_rand(1000, 9999), 'shb_room_type');
if (!is_wp_error($test_term)) {
    $term_id = $test_term['term_id'];
    $saved = SHB_Room_Type::save_defaults($term_id, array(
        'type_base_price' => '99.5',
        'type_max_guests' => '3',
        'type_bed_type' => 'king',
        'type_room_size' => '24',
        'type_floor' => '2',
        'type_amenities' => array('WiFi', 'Balcony'),
        'type_min_nights' => '2',
        'type_max_nights' => '14',
        'type_cancellation_policy' => 'moderate',
    ));
    shb_verify('room type defaults saved', !is_wp_error($saved), is_wp_error($saved) ? $saved->get_error_message() : '');

    $defaults = SHB_Room_Type::get_defaults($term_id);
    shb_verify(
        'room type defaults readable',
        is_array($defaults) && isset($defaults['_shb_type_base_price']) && abs(floatval($defaults['_shb_type_base_price']) - 99.5) < 0.001,
        is_array($defaults) ? print_r($defaults, true) : 'null'
    );

    // Search-by-type only returns rooms of that type. Create a matching room.
    $term = get_term($term_id, 'shb_room_type');
    $term_slug = $term && !is_wp_error($term) ? $term->slug : 'verify-type-' . $term_id;
    $room_id = wp_insert_post(array(
        'post_title' => 'Verify Type Room',
        'post_type' => 'shb_room',
        'post_status' => 'publish',
    ));
    if ($room_id && !is_wp_error($room_id)) {
        $default_location = SHB_Location::get_default_location_id();
        update_post_meta($room_id, '_shb_location_id', $default_location);
        update_post_meta($room_id, '_shb_is_active', '1');
        update_post_meta($room_id, '_shb_room_type', $term_slug);
        wp_set_object_terms($room_id, array($term_id), 'shb_room_type', false);

        $check_in = gmdate('Y-m-d', strtotime('+45 days'));
        $check_out = gmdate('Y-m-d', strtotime('+46 days'));
        $results = SHB_Room::search_available_rooms($check_in, $check_out, 1, $default_location, $term_slug);
        shb_verify('search filters by room type', count($results) === 1 && $results[0]['id'] === $room_id, 'expected 1 result');

        $other = SHB_Room::search_available_rooms($check_in, $check_out, 1, $default_location, 'standard');
        $found_other = 0;
        foreach ($other as $r) {
            if ($r['id'] === $room_id) {
                $found_other++;
            }
        }
        shb_verify('search excludes wrong type', $found_other === 0, 'type mismatch leaked through');

        wp_delete_post($room_id, true);
    } else {
        echo "SKIP  room type search (could not create test room)\n";
    }

    wp_delete_term($term_id, 'shb_room_type');
    shb_verify('term delete cleans type meta', empty(get_term_meta($term_id, '_shb_type_base_price', true)));
} else {
    echo "SKIP  room type defaults (could not create test term)\n";
}

// 10. Location delete guard: refuses while a room exists.
$guard_location = wp_insert_post(array(
    'post_title' => 'Verify Guard Location',
    'post_type' => 'shb_location',
    'post_status' => 'publish',
));
if ($guard_location && !is_wp_error($guard_location)) {
    $guard_room = wp_insert_post(array(
        'post_title' => 'Verify Guard Room',
        'post_type' => 'shb_room',
        'post_status' => 'publish',
    ));
    if ($guard_room && !is_wp_error($guard_room)) {
        update_post_meta($guard_room, '_shb_location_id', $guard_location);
        $result = SHB_Admin_Locations::delete_location($guard_location);
        shb_verify('location delete refused with rooms', is_wp_error($result) && $result->get_error_code() === 'has_rooms');
        wp_delete_post($guard_room, true);
    }
    $result = SHB_Admin_Locations::delete_location($guard_location);
    shb_verify('empty location deletable', $result === true, is_wp_error($result) ? $result->get_error_message() : 'unexpected result');
} else {
    echo "SKIP  location delete guard (could not create test location)\n";
}

// 11. Room search filters (bed type + amenities ALL match) via SHB_Admin_Rooms save.
$filter_room_id = SHB_Admin_Rooms::save_room(array(
    'name' => 'Verify Filter Room',
    'description' => 'Filter test room',
    'shb_location_id' => SHB_Location::get_default_location_id(),
    'shb_room_type' => 'standard',
    'shb_base_price' => '90',
    'shb_max_guests' => '2',
    'shb_bed_type' => 'king',
    'shb_room_size' => '20',
    'shb_floor' => '1',
    'shb_is_active' => '1',
    'shb_amenities' => array('WiFi', 'Balcony'),
    'shb_min_nights' => '1',
    'shb_max_nights' => '30',
    'shb_cancellation_policy' => 'flexible',
));
if (!is_wp_error($filter_room_id)) {
    $check_in = gmdate('Y-m-d', strtotime('+60 days'));
    $check_out = gmdate('Y-m-d', strtotime('+61 days'));

    $bed_results = SHB_Room::search_available_rooms($check_in, $check_out, 1, 0, '', array('bed_type' => 'king'));
    $found = 0;
    foreach ($bed_results as $r) {
        if ($r['id'] === $filter_room_id) {
            $found++;
        }
    }
    shb_verify('search filters by bed type', $found === 1, 'king room missing');

    $amenity_results = SHB_Room::search_available_rooms($check_in, $check_out, 1, 0, '', array('amenities' => array('WiFi', 'Balcony')));
    $found = 0;
    foreach ($amenity_results as $r) {
        if ($r['id'] === $filter_room_id) {
            $found++;
        }
    }
    shb_verify('search filters by amenities (ALL match)', $found === 1, 'WiFi+Balcony room missing');

    $miss_results = SHB_Room::search_available_rooms($check_in, $check_out, 1, 0, '', array('amenities' => array('Jacuzzi')));
    $found = 0;
    foreach ($miss_results as $r) {
        if ($r['id'] === $filter_room_id) {
            $found++;
        }
    }
    shb_verify('search excludes rooms missing amenities', $found === 0, 'room leaked without Jacuzzi');

    // Room delete guard: refuse while an active booking exists.
    $booking_result = SHB_Booking::create_booking(array(
        'room_id' => $filter_room_id,
        'check_in' => gmdate('Y-m-d', strtotime('+75 days')),
        'check_out' => gmdate('Y-m-d', strtotime('+76 days')),
        'guests' => 1,
        'first_name' => 'Verify',
        'last_name' => 'DeleteGuard',
        'email' => 'verify-delete@example.test',
        'phone' => '000',
    ));
    if (!is_wp_error($booking_result)) {
        $del_result = SHB_Admin_Rooms::delete_room($filter_room_id);
        shb_verify('room delete refused with active booking', is_wp_error($del_result) && $del_result->get_error_code() === 'has_bookings');
        SHB_Booking::update_status($booking_result['booking_id'], 'cancelled');
        $del_result = SHB_Admin_Rooms::delete_room($filter_room_id);
        shb_verify('room deletable after booking cancelled', $del_result === true, is_wp_error($del_result) ? $del_result->get_error_message() : 'unexpected result');
    } else {
        echo "SKIP  room delete guard (could not create test booking)\n";
        wp_delete_post($filter_room_id, true);
    }
} else {
    echo "SKIP  room setup save + filters (" . $filter_room_id->get_error_message() . ")\n";
}

echo $failures === 0 ? "\nAll checks passed.\n" : "\n{$failures} check(s) failed.\n";
exit($failures === 0 ? 0 : 1);
