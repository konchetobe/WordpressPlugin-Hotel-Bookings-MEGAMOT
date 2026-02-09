<?php
/**
 * Custom Post Types
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Post_Types {
    
    /**
     * Register Room and Booking post types
     */
    public static function register_post_types() {
        // Rooms Post Type
        register_post_type('shb_room', array(
            'labels' => array(
                'name' => __('Rooms', 'sanctuary-hotel-booking'),
                'singular_name' => __('Room', 'sanctuary-hotel-booking'),
                'add_new' => __('Add New', 'sanctuary-hotel-booking'),
                'add_new_item' => __('Add New Room', 'sanctuary-hotel-booking'),
                'edit_item' => __('Edit Room', 'sanctuary-hotel-booking'),
                'new_item' => __('New Room', 'sanctuary-hotel-booking'),
                'view_item' => __('View Room', 'sanctuary-hotel-booking'),
                'view_items' => __('View Rooms', 'sanctuary-hotel-booking'),
                'search_items' => __('Search Rooms', 'sanctuary-hotel-booking'),
                'not_found' => __('No rooms found', 'sanctuary-hotel-booking'),
                'not_found_in_trash' => __('No rooms found in trash', 'sanctuary-hotel-booking'),
                'all_items' => __('All Rooms', 'sanctuary-hotel-booking'),
                'menu_name' => __('Rooms', 'sanctuary-hotel-booking'),
            ),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => 'sanctuary-hotel-booking', // Show under Hotel Booking menu
            'query_var' => true,
            'rewrite' => array('slug' => 'rooms', 'with_front' => false),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
            'show_in_rest' => true,
        ));
        
        // Bookings Post Type
        register_post_type('shb_booking', array(
            'labels' => array(
                'name' => __('Bookings', 'sanctuary-hotel-booking'),
                'singular_name' => __('Booking', 'sanctuary-hotel-booking'),
                'add_new' => __('Add New', 'sanctuary-hotel-booking'),
                'add_new_item' => __('Add New Booking', 'sanctuary-hotel-booking'),
                'edit_item' => __('Edit Booking', 'sanctuary-hotel-booking'),
                'new_item' => __('New Booking', 'sanctuary-hotel-booking'),
                'view_item' => __('View Booking', 'sanctuary-hotel-booking'),
                'search_items' => __('Search Bookings', 'sanctuary-hotel-booking'),
                'not_found' => __('No bookings found', 'sanctuary-hotel-booking'),
                'not_found_in_trash' => __('No bookings found in trash', 'sanctuary-hotel-booking'),
                'all_items' => __('All Bookings', 'sanctuary-hotel-booking'),
                'menu_name' => __('Bookings', 'sanctuary-hotel-booking'),
            ),
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => false, // We'll add it via custom menu
            'query_var' => false,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => array('title'),
            'show_in_rest' => false,
        ));
    }
    
    /**
     * Register taxonomies
     */
    public static function register_taxonomies() {
        // Room Type Taxonomy
        register_taxonomy('shb_room_type', 'shb_room', array(
            'labels' => array(
                'name' => __('Room Types', 'sanctuary-hotel-booking'),
                'singular_name' => __('Room Type', 'sanctuary-hotel-booking'),
                'search_items' => __('Search Room Types', 'sanctuary-hotel-booking'),
                'all_items' => __('All Room Types', 'sanctuary-hotel-booking'),
                'parent_item' => __('Parent Room Type', 'sanctuary-hotel-booking'),
                'parent_item_colon' => __('Parent Room Type:', 'sanctuary-hotel-booking'),
                'edit_item' => __('Edit Room Type', 'sanctuary-hotel-booking'),
                'update_item' => __('Update Room Type', 'sanctuary-hotel-booking'),
                'add_new_item' => __('Add New Room Type', 'sanctuary-hotel-booking'),
                'new_item_name' => __('New Room Type Name', 'sanctuary-hotel-booking'),
                'menu_name' => __('Room Types', 'sanctuary-hotel-booking'),
            ),
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => false,
            'rewrite' => array('slug' => 'room-type', 'with_front' => false),
            'show_in_rest' => true,
        ));
        
        // Add default room types if they don't exist
        if (!term_exists('Standard', 'shb_room_type')) {
            wp_insert_term('Standard', 'shb_room_type', array(
                'description' => 'Standard room accommodation',
                'slug' => 'standard'
            ));
        }
        if (!term_exists('Deluxe', 'shb_room_type')) {
            wp_insert_term('Deluxe', 'shb_room_type', array(
                'description' => 'Deluxe room with premium amenities',
                'slug' => 'deluxe'
            ));
        }
        if (!term_exists('Suite', 'shb_room_type')) {
            wp_insert_term('Suite', 'shb_room_type', array(
                'description' => 'Luxury suite accommodation',
                'slug' => 'suite'
            ));
        }
    }
}
