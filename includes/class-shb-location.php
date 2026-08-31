<?php
/**
 * Location Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Location {

    /** @var array|null Cache of formatted locations keyed by ID. */
    private static $cache = null;

    /**
     * Get all locations.
     */
    public static function get_locations($args = array()) {
        $defaults = array(
            'post_type' => 'shb_location',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'suppress_filters' => false,
        );

        $args = wp_parse_args($args, $defaults);
        $locations = get_posts($args);

        return array_map(array(__CLASS__, 'format_location'), $locations);
    }

    /**
     * Get a single location.
     */
    public static function get_location($location_id) {
        $location_id = absint($location_id);
        if (!$location_id) {
            return null;
        }

        $post = get_post($location_id);
        if (!$post || $post->post_type !== 'shb_location') {
            return null;
        }

        return self::format_location($post);
    }

    /**
     * Format location data.
     */
    public static function format_location($post) {
        $location_id = is_object($post) ? $post->ID : $post;
        $post = get_post($location_id);
        if (!$post) {
            return null;
        }

        $timezone = get_post_meta($location_id, '_shb_timezone', true) ?: wp_timezone_string();

        return array(
            'id' => $post->ID,
            'name' => $post->post_title,
            'description' => $post->post_content,
            'excerpt' => $post->post_excerpt,
            'address' => get_post_meta($location_id, '_shb_address', true) ?: '',
            'city' => get_post_meta($location_id, '_shb_city', true) ?: '',
            'country' => get_post_meta($location_id, '_shb_country', true) ?: '',
            'phone' => get_post_meta($location_id, '_shb_phone', true) ?: '',
            'email' => get_post_meta($location_id, '_shb_email', true) ?: '',
            'timezone' => $timezone,
            'currency' => get_post_meta($location_id, '_shb_currency', true) ?: get_option('shb_currency', 'USD'),
            'currency_symbol' => get_post_meta($location_id, '_shb_currency_symbol', true) ?: get_option('shb_currency_symbol', '$'),
            'check_in_time' => get_post_meta($location_id, '_shb_check_in_time', true) ?: get_option('shb_check_in_time', '14:00'),
            'check_out_time' => get_post_meta($location_id, '_shb_check_out_time', true) ?: get_option('shb_check_out_time', '11:00'),
            'is_active' => get_post_meta($location_id, '_shb_is_active', true) !== '0',
            'image' => get_the_post_thumbnail_url($location_id, 'large') ?: '',
            'permalink' => get_permalink($location_id),
        );
    }

    /**
     * Get the Default location ID, creating it on first call if missing.
     */
    public static function get_default_location_id() {
        $default_id = absint(get_option('shb_default_location_id', 0));

        if ($default_id && self::get_location($default_id)) {
            return $default_id;
        }

        // Try to find an existing "Default" location before creating a new one.
        $existing = get_posts(array(
            'post_type' => 'shb_location',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'meta_key' => '_shb_is_default',
            'meta_value' => '1',
            'fields' => 'ids',
        ));

        if (!empty($existing)) {
            update_option('shb_default_location_id', $existing[0]);
            return $existing[0];
        }

        $site_name = get_bloginfo('name');
        $post_id = wp_insert_post(array(
            'post_title' => $site_name ? $site_name . ' (Default)' : __('Default Location', 'sanctuary-hotel-booking'),
            'post_type' => 'shb_location',
            'post_status' => 'publish',
        ));

        if (is_wp_error($post_id) || !$post_id) {
            return 0;
        }

        update_post_meta($post_id, '_shb_timezone', wp_timezone_string());
        update_post_meta($post_id, '_shb_currency', get_option('shb_currency', 'USD'));
        update_post_meta($post_id, '_shb_currency_symbol', get_option('shb_currency_symbol', '$'));
        update_post_meta($post_id, '_shb_check_in_time', get_option('shb_check_in_time', '14:00'));
        update_post_meta($post_id, '_shb_check_out_time', get_option('shb_check_out_time', '11:00'));
        update_post_meta($post_id, '_shb_is_default', '1');
        update_post_meta($post_id, '_shb_is_active', '1');

        update_option('shb_default_location_id', $post_id);

        return $post_id;
    }

    /**
     * Get the owning location ID for a room.
     */
    public static function get_location_for_room($room_id) {
        $location_id = absint(get_post_meta($room_id, '_shb_location_id', true));
        if (!$location_id) {
            return 0;
        }
        return $location_id;
    }

    /**
     * Count rooms belonging to a location.
     */
    public static function get_room_count($location_id) {
        $rooms = get_posts(array(
            'post_type' => 'shb_room',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_key' => '_shb_location_id',
            'meta_value' => absint($location_id),
        ));

        return count($rooms);
    }

    /**
     * Locations a user is allowed to manage (all for admins).
     */
    public static function get_locations_for_user($user_id = 0) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        if (!$user_id) {
            return array();
        }

        if (user_can($user_id, 'manage_options')) {
            return self::get_locations();
        }

        $assigned = get_user_meta($user_id, 'shb_assigned_location_ids', true);
        if (!is_array($assigned)) {
            return array();
        }

        $locations = array();
        foreach ($assigned as $location_id) {
            $location = self::get_location($location_id);
            if ($location) {
                $locations[] = $location;
            }
        }

        return $locations;
    }

    /**
     * Whether the current user can manage a specific location.
     */
    public static function user_can_manage($location_id = 0) {
        if (current_user_can('manage_options')) {
            return true;
        }

        if (!current_user_can('shb_manage_locations') && !current_user_can('shb_manage_rooms') && !current_user_can('shb_manage_bookings')) {
            return false;
        }

        if (!$location_id) {
            return true;
        }

        $assigned = get_user_meta(get_current_user_id(), 'shb_assigned_location_ids', true);
        if (!is_array($assigned)) {
            return false;
        }

        return in_array(absint($location_id), $assigned, true);
    }

    /**
     * Get active locations (for frontend selectors).
     */
    public static function get_active_locations() {
        $locations = self::get_locations();
        return array_values(array_filter($locations, function ($location) {
            return $location['is_active'];
        }));
    }

    /**
     * Get location meta keys list for reuse.
     */
    public static function get_meta_keys() {
        return array(
            '_shb_address',
            '_shb_city',
            '_shb_country',
            '_shb_phone',
            '_shb_email',
            '_shb_timezone',
            '_shb_currency',
            '_shb_currency_symbol',
            '_shb_check_in_time',
            '_shb_check_out_time',
            '_shb_is_active',
            '_shb_is_default',
        );
    }
}
