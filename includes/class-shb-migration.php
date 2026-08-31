<?php
/**
 * Migration Handler
 *
 * Phase 1 of the multi-location plan: creates the Default location, attaches
 * all existing rooms, migrates legacy room-type meta to taxonomy terms, snapshots
 * location data onto historical bookings, and backfills the room-nights table.
 *
 * This migration never deletes or silently resolves data. Anything it cannot
 * confidently fix is recorded in the migration report for manual review.
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Migration {

    /**
     * Run the 1.4.0-beta.1 migration. Idempotent.
     */
    public static function run() {
        // Fresh installs may have no rooms yet; ensure schema exists regardless.
        SHB_Database::create_tables();

        $report = array(
            'version' => '1.4.0-beta.1',
            'run_at' => current_time('mysql'),
            'total_rooms' => 0,
            'rooms_attached' => 0,
            'orphaned_rooms' => array(),
            'room_types_migrated' => 0,
            'bookings_snapshotted' => 0,
            'bookings_without_room' => 0,
            'invalid_date_bookings' => array(),
            'nights_backfilled' => 0,
            'overlapping_bookings' => array(),
        );

        $default_location_id = self::ensure_default_location();
        if (!$default_location_id) {
            $report['error'] = __('Failed to create the Default location.', 'sanctuary-hotel-booking');
            update_option('shb_migration_report', $report);
            return;
        }

        self::migrate_rooms($default_location_id, $report);
        self::migrate_bookings($default_location_id, $report);

        update_option('shb_migration_report', $report);
    }

    /**
     * Whether this migration has already run.
     */
    public static function has_run() {
        $db_version = get_option('shb_db_version', '');
        return $db_version !== '' && version_compare($db_version, '1.4.0-beta.1', '>=');
    }

    /**
     * Get the stored migration report.
     */
    public static function get_report() {
        return get_option('shb_migration_report', array());
    }

    /**
     * Create the Default location from global settings.
     */
    private static function ensure_default_location() {
        return SHB_Location::get_default_location_id();
    }

    /**
     * Attach all rooms to the Default location and migrate room types to terms.
     */
    private static function migrate_rooms($default_location_id, &$report) {
        $rooms = get_posts(array(
            'post_type' => 'shb_room',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'suppress_filters' => false,
        ));

        $report['total_rooms'] = count($rooms);

        foreach ($rooms as $room_id) {
            $location_id = absint(get_post_meta($room_id, '_shb_location_id', true));

            if (!$location_id || !SHB_Location::get_location($location_id)) {
                $report['orphaned_rooms'][] = $room_id;
                update_post_meta($room_id, '_shb_location_id', $default_location_id);
                $report['rooms_attached']++;
            }

            // Migrate legacy room-type meta to the canonical taxonomy term.
            $legacy_type = get_post_meta($room_id, '_shb_room_type', true);
            if ($legacy_type) {
                $term = self::ensure_room_type_term($legacy_type);
                if ($term) {
                    wp_set_object_terms($room_id, array($term->term_id), 'shb_room_type', false);
                    $report['room_types_migrated']++;
                }
            }
        }
    }

    /**
     * Ensure a room-type term exists for a legacy value, returning the term.
     */
    private static function ensure_room_type_term($room_type) {
        $slug = sanitize_title($room_type);
        if (!$slug) {
            return null;
        }

        $term = get_term_by('slug', $slug, 'shb_room_type');
        if ($term) {
            return $term;
        }

        // Legacy values like "standard" should match the seeded "Standard" term.
        $existing = get_term_by('name', ucfirst($room_type), 'shb_room_type');
        if ($existing) {
            return $existing;
        }

        $inserted = wp_insert_term(ucfirst($room_type), 'shb_room_type', array(
            'slug' => $slug,
        ));

        if (is_wp_error($inserted)) {
            return null;
        }

        return get_term($inserted['term_id'], 'shb_room_type');
    }

    /**
     * Snapshot location data onto bookings and backfill room nights.
     */
    private static function migrate_bookings($default_location_id, &$report) {
        global $wpdb;
        $table = $wpdb->prefix . 'shb_room_nights';

        $bookings = get_posts(array(
            'post_type' => 'shb_booking',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'suppress_filters' => false,
        ));

        foreach ($bookings as $booking_id) {
            $room_id = absint(get_post_meta($booking_id, '_shb_room_id', true));
            $check_in = get_post_meta($booking_id, '_shb_check_in', true);
            $check_out = get_post_meta($booking_id, '_shb_check_out', true);
            $status = get_post_meta($booking_id, '_shb_booking_status', true);

            $location_id = 0;
            if ($room_id) {
                $location_id = absint(get_post_meta($room_id, '_shb_location_id', true));
            }

            if (!$location_id) {
                $report['bookings_without_room']++;
                $location_id = $default_location_id;
            }

            $location = SHB_Location::get_location($location_id);
            $location_name = $location ? $location['name'] : get_the_title($default_location_id);

            update_post_meta($booking_id, '_shb_location_id', $location_id);
            update_post_meta($booking_id, '_shb_location_name', $location_name);
            $report['bookings_snapshotted']++;

            // Backfill nightly rows for active bookings.
            $nights = SHB_Booking::calculate_nights($check_in, $check_out);
            if ($nights < 1) {
                $report['invalid_date_bookings'][] = array(
                    'booking_id' => $booking_id,
                    'check_in' => $check_in,
                    'check_out' => $check_out,
                );
                continue;
            }

            if ($status === 'cancelled') {
                continue;
            }

            $hold_expires = get_post_meta($booking_id, '_shb_hold_expires_at', true);
            $hold_expires = $hold_expires ? $hold_expires : null;

            // Detect nights already claimed by another active booking before inserting.
            $existing = $wpdb->get_col($wpdb->prepare(
                "SELECT stay_date FROM $table WHERE room_id = %d AND stay_date >= %s AND stay_date < %s",
                $room_id,
                $check_in,
                $check_out
            ));
            $existing_dates = array_flip($existing);

            $inserted = 0;
            $date = new DateTimeImmutable($check_in);
            $end = new DateTimeImmutable($check_out);
            for ($d = $date; $d < $end; $d = $d->modify('+1 day')) {
                $stay_date = $d->format('Y-m-d');

                if (isset($existing_dates[$stay_date])) {
                    $report['overlapping_bookings'][] = array(
                        'booking_id' => $booking_id,
                        'room_id' => $room_id,
                        'stay_date' => $stay_date,
                    );
                    continue;
                }

                $result = $wpdb->insert($table, array(
                    'room_id' => $room_id,
                    'location_id' => $location_id,
                    'booking_id' => $booking_id,
                    'stay_date' => $stay_date,
                    'hold_expires_at' => $hold_expires,
                ));

                if ($result !== false) {
                    $inserted++;
                }
            }

            $report['nights_backfilled'] += $inserted;
        }
    }
}
