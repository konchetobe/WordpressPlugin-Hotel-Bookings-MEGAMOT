<?php
/**
 * Pricing Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Pricing {
    
    /**
     * Calculate total price for a booking
     */
    public static function calculate_total_price($room_id, $check_in, $check_out) {
        $room = SHB_Room::get_room($room_id);
        if (!$room) {
            return 0;
        }
        
        $base_price = $room['base_price'];
        $nights = SHB_Booking::calculate_nights($check_in, $check_out);
        
        if ($nights <= 0) {
            return 0;
        }
        
        // Get pricing multiplier
        $multiplier = self::get_pricing_multiplier($room['room_type'], $check_in, $check_out);
        
        $total = $base_price * $nights * $multiplier;
        
        return round($total, 2);
    }
    
    /**
     * Get pricing multiplier based on active rules
     */
    public static function get_pricing_multiplier($room_type, $check_in, $check_out) {
        global $wpdb;
        $table = $wpdb->prefix . 'shb_pricing_rules';
        
        $rules = $wpdb->get_results(
            "SELECT * FROM $table WHERE is_active = 1",
            ARRAY_A
        );
        
        $multiplier = 1.0;
        $check_in_date = new DateTime($check_in);
        $check_out_date = new DateTime($check_out);
        
        foreach ($rules as $rule) {
            // Check room type filter
            if (!empty($rule['room_type']) && $rule['room_type'] !== $room_type) {
                continue;
            }
            
            // Check date range
            if (!empty($rule['start_date']) && !empty($rule['end_date'])) {
                $rule_start = new DateTime($rule['start_date']);
                $rule_end = new DateTime($rule['end_date']);
                
                if ($check_in_date < $rule_start || $check_out_date > $rule_end) {
                    continue;
                }
            }
            
            // Apply multiplier
            $multiplier *= floatval($rule['multiplier']);
        }
        
        return $multiplier;
    }
    
    /**
     * Get all pricing rules
     */
    public static function get_pricing_rules() {
        global $wpdb;
        $table = $wpdb->prefix . 'shb_pricing_rules';
        
        return $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC", ARRAY_A);
    }
    
    /**
     * Get single pricing rule
     */
    public static function get_pricing_rule($rule_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'shb_pricing_rules';
        
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $rule_id),
            ARRAY_A
        );
    }
    
    /**
     * Create pricing rule
     */
    public static function create_pricing_rule($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'shb_pricing_rules';
        
        $result = $wpdb->insert($table, array(
            'name' => sanitize_text_field($data['name']),
            'rule_type' => sanitize_text_field($data['rule_type']),
            'room_type' => !empty($data['room_type']) ? sanitize_text_field($data['room_type']) : null,
            'start_date' => !empty($data['start_date']) ? sanitize_text_field($data['start_date']) : null,
            'end_date' => !empty($data['end_date']) ? sanitize_text_field($data['end_date']) : null,
            'multiplier' => floatval($data['multiplier']),
            'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 1,
        ));
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create pricing rule', 'sanctuary-hotel-booking'));
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Update pricing rule
     */
    public static function update_pricing_rule($rule_id, $data) {
        global $wpdb;
        $table = $wpdb->prefix . 'shb_pricing_rules';
        
        $update_data = array();
        
        if (isset($data['name'])) {
            $update_data['name'] = sanitize_text_field($data['name']);
        }
        if (isset($data['rule_type'])) {
            $update_data['rule_type'] = sanitize_text_field($data['rule_type']);
        }
        if (isset($data['room_type'])) {
            $update_data['room_type'] = !empty($data['room_type']) ? sanitize_text_field($data['room_type']) : null;
        }
        if (isset($data['start_date'])) {
            $update_data['start_date'] = !empty($data['start_date']) ? sanitize_text_field($data['start_date']) : null;
        }
        if (isset($data['end_date'])) {
            $update_data['end_date'] = !empty($data['end_date']) ? sanitize_text_field($data['end_date']) : null;
        }
        if (isset($data['multiplier'])) {
            $update_data['multiplier'] = floatval($data['multiplier']);
        }
        if (isset($data['is_active'])) {
            $update_data['is_active'] = intval($data['is_active']);
        }
        
        $result = $wpdb->update($table, $update_data, array('id' => $rule_id));
        
        return $result !== false;
    }
    
    /**
     * Delete pricing rule
     */
    public static function delete_pricing_rule($rule_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'shb_pricing_rules';
        
        return $wpdb->delete($table, array('id' => $rule_id));
    }
    
    /**
     * Get price breakdown
     */
    public static function get_price_breakdown($room_id, $check_in, $check_out) {
        $room = SHB_Room::get_room($room_id);
        if (!$room) {
            return null;
        }
        
        $nights = SHB_Booking::calculate_nights($check_in, $check_out);
        $multiplier = self::get_pricing_multiplier($room['room_type'], $check_in, $check_out);
        $subtotal = $room['base_price'] * $nights;
        $total = $subtotal * $multiplier;
        
        return array(
            'base_price' => $room['base_price'],
            'nights' => $nights,
            'subtotal' => $subtotal,
            'multiplier' => $multiplier,
            'adjustment' => ($multiplier - 1) * 100,
            'total' => round($total, 2),
        );
    }
}
