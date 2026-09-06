<?php
/**
 * Room Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Room
{

    /**
     * Get all active rooms
     */
    public static function get_rooms($args = array())
    {
        $defaults = array(
            'post_type' => 'shb_room',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_shb_is_active',
                    'value' => '1',
                    'compare' => '=',
                ),
            ),
        );

        $args = wp_parse_args($args, $defaults);

        // Support filtering by location.
        if (!empty($args['location_id'])) {
            $location_id = absint($args['location_id']);
            unset($args['location_id']);
            $args['meta_query'][] = array(
                'key' => '_shb_location_id',
                'value' => $location_id,
            );
        }

        // Support filtering by a set of locations.
        if (!empty($args['location_ids']) && is_array($args['location_ids'])) {
            $location_ids = array_map('absint', $args['location_ids']);
            unset($args['location_ids']);
            if (!empty($location_ids)) {
                $args['meta_query'][] = array(
                    'key' => '_shb_location_id',
                    'value' => $location_ids,
                    'compare' => 'IN',
                );
            }
        }

        // Support filtering by active status only. When $args['all'] is set,
        // remove the default active filter (admin screens pass meta_query = []).
        if (!empty($args['all'])) {
            unset($args['all']);
            $args['meta_query'] = isset($args['meta_query']) && is_array($args['meta_query'])
                ? array_values(array_filter($args['meta_query'], function ($mq) {
                    return !(is_array($mq) && ($mq['key'] ?? '') === '_shb_is_active');
                }))
                : array();
        }

        $rooms = get_posts($args);

        return array_map(array(__CLASS__, 'format_room'), $rooms);
    }

    /**
     * Get single room
     */
    public static function get_room($room_id)
    {
        $room = get_post($room_id);
        if (!$room || $room->post_type !== 'shb_room') {
            return null;
        }
        return self::format_room($room);
    }

    /**
     * Format room data
     */
    public static function format_room($post)
    {
        $room_id = is_object($post) ? $post->ID : $post;
        $post = get_post($room_id);

        $location_id = absint(get_post_meta($post->ID, '_shb_location_id', true));
        $location = $location_id ? SHB_Location::get_location($location_id) : null;

        // Canonical room type is the taxonomy term, with the legacy meta as
        // the fallback during the compatibility window.
        $terms = get_the_terms($post->ID, 'shb_room_type');
        $room_type = !empty($terms) ? $terms[0]->slug : '';
        if (!$room_type) {
            $room_type = get_post_meta($post->ID, '_shb_room_type', true) ?: 'standard';
        }

        return array(
            'id' => $post->ID,
            'name' => $post->post_title,
            'description' => $post->post_content,
            'excerpt' => $post->post_excerpt,
            'room_type' => $room_type,
            'location_id' => $location_id,
            'location_name' => $location ? $location['name'] : '',
            'location' => $location,
            'base_price' => floatval(get_post_meta($post->ID, '_shb_base_price', true)),
            'max_guests' => intval(get_post_meta($post->ID, '_shb_max_guests', true)) ?: 2,
            'amenities' => get_post_meta($post->ID, '_shb_amenities', true) ?: array(),
            'is_active' => get_post_meta($post->ID, '_shb_is_active', true) === '1',
            'bed_type' => get_post_meta($post->ID, '_shb_bed_type', true) ?: 'queen',
            'room_size' => intval(get_post_meta($post->ID, '_shb_room_size', true)),
            'floor' => intval(get_post_meta($post->ID, '_shb_floor', true)),
            'min_nights' => intval(get_post_meta($post->ID, '_shb_min_nights', true)) ?: 1,
            'max_nights' => intval(get_post_meta($post->ID, '_shb_max_nights', true)) ?: 30,
            'cancellation_policy' => get_post_meta($post->ID, '_shb_cancellation_policy', true) ?: 'flexible',
            'image' => get_the_post_thumbnail_url($post->ID, 'large') ?: SHB_PLUGIN_URL . 'assets/images/default-room.jpg',
            'gallery' => self::get_room_gallery($post->ID),
            'permalink' => get_permalink($post->ID),
        );
    }

    /**
     * Get room gallery images
     */
    public static function get_room_gallery($room_id)
    {
        $gallery_ids = get_post_meta($room_id, '_shb_gallery', true);
        if (empty($gallery_ids)) {
            return array();
        }

        $images = array();
        foreach ($gallery_ids as $id) {
            $images[] = wp_get_attachment_image_url($id, 'large');
        }
        return $images;
    }

    /**
     * Get active rooms by type (canonical taxonomy term, with legacy meta fallback)
     */
    public static function get_rooms_by_type($room_type)
    {
        $active_query = array(
            array(
                'key' => '_shb_is_active',
                'value' => '1',
            ),
        );

        $rooms = get_posts(array(
            'post_type' => 'shb_room',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'shb_room_type',
                    'field' => 'slug',
                    'terms' => sanitize_title($room_type),
                ),
            ),
            'meta_query' => $active_query,
        ));

        if (empty($rooms)) {
            // Legacy fallback for rooms that predate the taxonomy sync.
            $rooms = get_posts(array(
                'post_type' => 'shb_room',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'meta_query' => array(
                    $active_query[0],
                    array(
                        'key' => '_shb_room_type',
                        'value' => $room_type,
                    ),
                ),
            ));
        }

        return array_map(array(__CLASS__, 'format_room'), $rooms);
    }

    /**
     * Ensure a room's canonical shb_room_type term matches its stored type.
     * Returns the term object, or null when the room has no recognizable type.
     */
    public static function sync_room_type_term($post_id)
    {
        $post_id = absint($post_id);

        $terms = get_the_terms($post_id, 'shb_room_type');
        if (!empty($terms)) {
            return $terms[0];
        }

        $meta_type = get_post_meta($post_id, '_shb_room_type', true);
        if (!$meta_type) {
            return null;
        }

        $slug = sanitize_title($meta_type);
        $term = $slug ? get_term_by('slug', $slug, 'shb_room_type') : false;
        if (!$term) {
            $term = get_term_by('name', ucfirst($meta_type), 'shb_room_type');
        }
        if (!$term) {
            $inserted = wp_insert_term(ucfirst($meta_type), 'shb_room_type', array('slug' => $slug));
            if (is_wp_error($inserted)) {
                return null;
            }
            $term = get_term($inserted['term_id'], 'shb_room_type');
        }

        if ($term) {
            wp_set_object_terms($post_id, array((int) $term->term_id), 'shb_room_type', false);
        }

        return $term ? $term : null;
    }

    /**
     * Count active rooms per room type at a location.
     * Returns array of ['slug', 'name', 'count'] sorted by count (desc).
     */
    public static function get_room_type_counts_by_location($location_id)
    {
        $location_id = absint($location_id);
        if (!$location_id) {
            return array();
        }

        $rooms = get_posts(array(
            'post_type' => 'shb_room',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_shb_location_id',
                    'value' => $location_id,
                ),
                array(
                    'key' => '_shb_is_active',
                    'value' => '1',
                ),
            ),
        ));

        $counts = array();
        foreach ($rooms as $room_id) {
            // Canonical type is the term; fall back to legacy meta.
            $terms = get_the_terms($room_id, 'shb_room_type');
            if (!empty($terms) && !is_wp_error($terms)) {
                $slug = $terms[0]->slug;
                $name = $terms[0]->name;
            } else {
                $slug = get_post_meta($room_id, '_shb_room_type', true) ?: 'standard';
                $name = ucfirst($slug);
            }
            if (!isset($counts[$slug])) {
                $counts[$slug] = array('slug' => $slug, 'name' => $name, 'count' => 0);
            }
            $counts[$slug]['count']++;
        }

        usort($counts, function ($a, $b) {
            return $b['count'] - $a['count'];
        });

        return array_values($counts);
    }

    /**
     * Search available rooms
     */
    public static function search_available_rooms($check_in, $check_out, $guests = 1, $location_id = 0, $room_type = '')
    {
        $args = array();
        if ($location_id) {
            $args['location_id'] = absint($location_id);
        }

        $all_rooms = self::get_rooms($args);
        $available_rooms = array();

        foreach ($all_rooms as $room) {
            // Make sure the room has a term so scoped pricing can match it.
            self::sync_room_type_term($room['id']);

            // Filter by room type when requested (canonical term slug).
            if ($room_type !== '') {
                $terms = get_the_terms($room['id'], 'shb_room_type');
                $room_type_slug = !empty($terms) && !is_wp_error($terms) ? $terms[0]->slug : '';
                if (!$room_type_slug) {
                    $room_type_slug = get_post_meta($room['id'], '_shb_room_type', true) ?: '';
                }
                if ($room_type_slug !== $room_type) {
                    continue;
                }
            }

            if ($room['max_guests'] >= $guests) {
                $is_available = SHB_Availability::check_room_availability(
                    $room['id'],
                    $check_in,
                    $check_out
                );

                if ($is_available) {
                    $room['calculated_price'] = SHB_Pricing::get_price_breakdown(
                        $room['id'],
                        $check_in,
                        $check_out
                    );
                    $available_rooms[] = $room;
                }
            }
        }

        return $available_rooms;
    }
}
