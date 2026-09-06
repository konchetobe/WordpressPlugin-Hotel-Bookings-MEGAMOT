<?php
/**
 * Room Type Handler
 *
 * Room types are terms of the shb_room_type taxonomy. This class gives a
 * room type a "defaults template" (stored as term meta) that prefills the
 * per-room fields when an admin creates a new room of that type. Rooms stay
 * the bookable unit and may override every default.
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Room_Type {

    /**
     * Term meta keys that hold the defaults template for a room type.
     */
    public static function get_meta_keys() {
        return array(
            '_shb_type_base_price',
            '_shb_type_max_guests',
            '_shb_type_bed_type',
            '_shb_type_room_size',
            '_shb_type_floor',
            '_shb_type_amenities',
            '_shb_type_min_nights',
            '_shb_type_max_nights',
            '_shb_type_cancellation_policy',
        );
    }

    /**
     * Map of form field name => meta key for the defaults editor.
     * Single source of truth shared by the admin form and the AJAX handler.
     */
    public static function get_field_map() {
        return array(
            'type_base_price'          => '_shb_type_base_price',
            'type_max_guests'          => '_shb_type_max_guests',
            'type_bed_type'            => '_shb_type_bed_type',
            'type_room_size'           => '_shb_type_room_size',
            'type_floor'               => '_shb_type_floor',
            'type_amenities'           => '_shb_type_amenities',
            'type_min_nights'          => '_shb_type_min_nights',
            'type_max_nights'          => '_shb_type_max_nights',
            'type_cancellation_policy' => '_shb_type_cancellation_policy',
        );
    }

    /**
     * Register REST-visible post meta (rooms) and term meta (room types).
     * Runs on 'init' at priority 0 so meta exists before save handlers.
     */
    public static function register_meta() {
        $room_keys = array(
            '_shb_location_id'         => 'integer',
            '_shb_room_type'           => 'string',
            '_shb_base_price'          => 'number',
            '_shb_max_guests'          => 'integer',
            '_shb_amenities'           => 'array',
            '_shb_is_active'           => 'string',
            '_shb_bed_type'            => 'string',
            '_shb_room_size'           => 'integer',
            '_shb_floor'               => 'integer',
            '_shb_min_nights'          => 'integer',
            '_shb_max_nights'          => 'integer',
            '_shb_cancellation_policy' => 'string',
        );

        foreach ($room_keys as $key => $type) {
            register_post_meta(
                'shb_room',
                $key,
                array(
                    'show_in_rest' => true,
                    'single'       => true,
                    'type'         => $type,
                )
            );
        }

        foreach (self::get_meta_keys() as $key) {
            register_term_meta(
                'shb_room_type',
                $key,
                array(
                    'show_in_rest' => true,
                    'single'       => true,
                    'type'         => 'string',
                )
            );
        }
    }

    /**
     * Hook term lifecycle so defaults are cleaned up when a type is deleted.
     */
    public static function init() {
        add_action('delete_shb_room_type', array(__CLASS__, 'delete_defaults'));
    }

    /**
     * Get all room types (terms) with their defaults template.
     */
    public static function get_room_types() {
        $terms = get_terms(array(
            'taxonomy'   => 'shb_room_type',
            'hide_empty' => false,
        ));

        if (is_wp_error($terms) || empty($terms)) {
            return array();
        }

        return array_map(array(__CLASS__, 'format_room_type'), $terms);
    }

    /**
     * Get a single room type by term ID or slug.
     */
    public static function get_room_type($term_id_or_slug) {
        if (is_numeric($term_id_or_slug)) {
            $term = get_term(absint($term_id_or_slug), 'shb_room_type');
        } else {
            $term = get_term_by('slug', sanitize_title($term_id_or_slug), 'shb_room_type');
        }

        if (!$term || is_wp_error($term)) {
            return null;
        }

        return self::format_room_type($term);
    }

    /**
     * Format a room type term into an array with its defaults meta.
     */
    public static function format_room_type($term) {
        $meta = array();
        foreach (self::get_meta_keys() as $key) {
            $meta[$key] = get_term_meta($term->term_id, $key, true);
        }

        return array(
            'term_id'     => $term->term_id,
            'name'        => $term->name,
            'slug'        => $term->slug,
            'description' => $term->description,
            'meta'        => $meta,
        );
    }

    /**
     * Get the stored defaults for a room type as room-field values, or null
     * when the type has no defaults saved yet.
     */
    public static function get_defaults($term_id_or_slug) {
        $room_type = self::get_room_type($term_id_or_slug);
        if (!$room_type) {
            return null;
        }

        $defaults = array_filter($room_type['meta'], function ($value) {
            return $value !== '' && $value !== null && $value !== false;
        });

        return empty($defaults) ? null : $defaults;
    }

    /**
     * Save defaults from an admin form submission. Returns term ID or WP_Error.
     */
    public static function save_defaults($term_id, $data) {
        $term_id = absint($term_id);
        $term = get_term($term_id, 'shb_room_type');
        if (!$term || is_wp_error($term)) {
            return new WP_Error('invalid_term', __('Room type not found.', 'sanctuary-hotel-booking'));
        }

        $field_map = self::get_field_map();
        $base_price = isset($data['type_base_price']) && $data['type_base_price'] !== '' ? floatval($data['type_base_price']) : '';
        $max_guests = isset($data['type_max_guests']) && $data['type_max_guests'] !== '' ? intval($data['type_max_guests']) : '';
        $room_size = isset($data['type_room_size']) && $data['type_room_size'] !== '' ? intval($data['type_room_size']) : '';
        $floor = isset($data['type_floor']) && $data['type_floor'] !== '' ? intval($data['type_floor']) : '';
        $min_nights = isset($data['type_min_nights']) && $data['type_min_nights'] !== '' ? max(1, intval($data['type_min_nights'])) : '';
        $max_nights = '';
        if (isset($data['type_max_nights']) && $data['type_max_nights'] !== '') {
            $max_nights = min(365, max($min_nights !== '' ? $min_nights : 1, intval($data['type_max_nights'])));
        }

        $amenities = array();
        if (isset($data['type_amenities']) && is_array($data['type_amenities'])) {
            $amenities = array_values(array_unique(array_map('sanitize_text_field', $data['type_amenities'])));
        }
        if (!empty($data['type_custom_amenities'])) {
            $custom = array_map('trim', explode(',', sanitize_text_field($data['type_custom_amenities'])));
            $custom = array_filter($custom);
            $amenities = array_values(array_unique(array_merge($amenities, $custom)));
        }

        $values = array(
            '_shb_type_base_price'          => $base_price,
            '_shb_type_max_guests'          => $max_guests,
            '_shb_type_bed_type'            => isset($data['type_bed_type']) ? sanitize_text_field($data['type_bed_type']) : '',
            '_shb_type_room_size'           => $room_size,
            '_shb_type_floor'               => $floor,
            '_shb_type_amenities'           => $amenities,
            '_shb_type_min_nights'          => $min_nights,
            '_shb_type_max_nights'          => $max_nights,
            '_shb_type_cancellation_policy' => isset($data['type_cancellation_policy']) ? sanitize_text_field($data['type_cancellation_policy']) : '',
        );

        foreach ($values as $key => $value) {
            update_term_meta($term_id, $key, $value);
        }

        return $term_id;
    }

    /**
     * Delete the defaults template for a term (used when a type is deleted).
     */
    public static function delete_defaults($term_id) {
        foreach (self::get_meta_keys() as $key) {
            delete_term_meta($term_id, $key);
        }
    }

    /**
     * Sanitizer registry for room meta keys — shared by save paths.
     */
    public static function get_room_meta_registry() {
        return array(
            '_shb_base_price'          => 'floatval',
            '_shb_max_guests'          => 'intval',
            '_shb_bed_type'            => 'sanitize_text_field',
            '_shb_room_size'           => 'intval',
            '_shb_floor'               => 'intval',
            '_shb_min_nights'          => 'intval',
            '_shb_max_nights'          => 'intval',
            '_shb_cancellation_policy' => 'sanitize_text_field',
        );
    }
}
