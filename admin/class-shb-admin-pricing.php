<?php
/**
 * Admin Pricing Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Admin_Pricing {
    
    public static function render_page() {
        $rules = SHB_Pricing::get_pricing_rules();
        $locations = SHB_Location::get_locations();
        $rooms = SHB_Room::get_rooms(array('meta_query' => array()));
        $room_types = get_terms(array(
            'taxonomy' => 'shb_room_type',
            'hide_empty' => false,
        ));
        
        include SHB_PLUGIN_DIR . 'admin/views/pricing.php';
    }
}
