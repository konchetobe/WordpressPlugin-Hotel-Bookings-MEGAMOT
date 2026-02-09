<?php
/**
 * Gutenberg Block Registration
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Blocks {
    
    public static function init() {
        add_action('init', array(__CLASS__, 'register_blocks'));
        add_action('enqueue_block_editor_assets', array(__CLASS__, 'enqueue_editor_assets'));
    }
    
    /**
     * Register all blocks
     */
    public static function register_blocks() {
        // Only proceed if Gutenberg is available
        if (!function_exists('register_block_type')) {
            return;
        }
        
        // Room Search block
        register_block_type('sanctuary-hotel-booking/room-search', array(
            'render_callback' => array('SHB_Shortcodes', 'room_search'),
            'attributes' => array(
                'style' => array('type' => 'string', 'default' => 'default'),
            ),
        ));
        
        // Room List block
        register_block_type('sanctuary-hotel-booking/room-list', array(
            'render_callback' => array(__CLASS__, 'render_room_list'),
            'attributes' => array(
                'columns' => array('type' => 'number', 'default' => 3),
                'type' => array('type' => 'string', 'default' => ''),
                'limit' => array('type' => 'number', 'default' => -1),
            ),
        ));
        
        // Booking Form block
        register_block_type('sanctuary-hotel-booking/booking-form', array(
            'render_callback' => array(__CLASS__, 'render_booking_form'),
            'attributes' => array(
                'roomId' => array('type' => 'number', 'default' => 0),
            ),
        ));
        
        // Booking Confirmation block
        register_block_type('sanctuary-hotel-booking/booking-confirmation', array(
            'render_callback' => array('SHB_Shortcodes', 'booking_confirmation'),
            'attributes' => array(),
        ));
        
        // My Bookings block
        register_block_type('sanctuary-hotel-booking/my-bookings', array(
            'render_callback' => array('SHB_Shortcodes', 'my_bookings'),
            'attributes' => array(),
        ));
    }
    
    /**
     * Render room list block (maps attributes to shortcode atts)
     */
    public static function render_room_list($attributes) {
        return SHB_Shortcodes::room_list(array(
            'columns' => isset($attributes['columns']) ? $attributes['columns'] : 3,
            'type' => isset($attributes['type']) ? $attributes['type'] : '',
            'limit' => isset($attributes['limit']) ? $attributes['limit'] : -1,
        ));
    }
    
    /**
     * Render booking form block
     */
    public static function render_booking_form($attributes) {
        return SHB_Shortcodes::booking_form(array(
            'room_id' => isset($attributes['roomId']) ? $attributes['roomId'] : 0,
        ));
    }
    
    /**
     * Enqueue editor assets
     */
    public static function enqueue_editor_assets() {
        // Get rooms for the editor
        $rooms_raw = SHB_Room::get_rooms(array('meta_query' => array()));
        $rooms_for_editor = array();
        foreach ($rooms_raw as $room) {
            $rooms_for_editor[] = array(
                'id' => $room['id'],
                'name' => $room['name'],
                'type' => $room['room_type'],
            );
        }
        
        wp_enqueue_script(
            'shb-blocks-editor',
            SHB_PLUGIN_URL . 'assets/js/blocks.js',
            array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n'),
            SHB_VERSION,
            true
        );
        
        wp_localize_script('shb-blocks-editor', 'shbBlockData', array(
            'rooms' => $rooms_for_editor,
            'roomTypes' => array('standard', 'deluxe', 'suite', 'family', 'penthouse'),
            'pluginUrl' => SHB_PLUGIN_URL,
        ));
        
        wp_enqueue_style(
            'shb-blocks-editor-css',
            SHB_PLUGIN_URL . 'assets/css/blocks-editor.css',
            array('wp-edit-blocks'),
            SHB_VERSION
        );
    }
}
