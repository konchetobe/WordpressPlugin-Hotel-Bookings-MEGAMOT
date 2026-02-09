<?php
/**
 * Room Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Room {
    
    /**
     * Get all active rooms
     */
    public static function get_rooms($args = array()) {
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
        $rooms = get_posts($args);
        
        return array_map(array(__CLASS__, 'format_room'), $rooms);
    }
    
    /**
     * Get single room
     */
    public static function get_room($room_id) {
        $room = get_post($room_id);
        if (!$room || $room->post_type !== 'shb_room') {
            return null;
        }
        return self::format_room($room);
    }
    
    /**
     * Format room data
     */
    public static function format_room($post) {
        $room_id = is_object($post) ? $post->ID : $post;
        $post = get_post($room_id);
        
        return array(
            'id' => $post->ID,
            'name' => $post->post_title,
            'description' => $post->post_content,
            'excerpt' => $post->post_excerpt,
            'room_type' => get_post_meta($post->ID, '_shb_room_type', true) ?: 'standard',
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
    public static function get_room_gallery($room_id) {
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
     * Get rooms by type
     */
    public static function get_rooms_by_type($room_type) {
        return self::get_rooms(array(
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_shb_is_active',
                    'value' => '1',
                ),
                array(
                    'key' => '_shb_room_type',
                    'value' => $room_type,
                ),
            ),
        ));
    }
    
    /**
     * Search available rooms
     */
    public static function search_available_rooms($check_in, $check_out, $guests = 1) {
        $all_rooms = self::get_rooms();
        $available_rooms = array();
        
        foreach ($all_rooms as $room) {
            if ($room['max_guests'] >= $guests) {
                $is_available = SHB_Availability::check_room_availability(
                    $room['id'],
                    $check_in,
                    $check_out
                );
                
                if ($is_available) {
                    $room['calculated_price'] = SHB_Pricing::calculate_total_price(
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
