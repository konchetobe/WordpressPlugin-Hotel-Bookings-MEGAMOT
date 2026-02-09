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
        
        include SHB_PLUGIN_DIR . 'admin/views/pricing.php';
    }
}
