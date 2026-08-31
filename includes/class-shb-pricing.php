<?php
/**
 * Pricing Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Pricing {

    /**
     * Active rules are immutable for the duration of a request unless this
     * class changes one of them.
     *
     * @var array|null
     */
    private static $active_rules = null;

    /**
     * Rule IDs applied during the last pricing calculation (for snapshots).
     *
     * @var array
     */
    private static $last_applied_rule_ids = array();
    
    /**
     * Calculate total price for a booking
     */
    public static function calculate_total_price($room_id, $check_in, $check_out) {
        $room = SHB_Room::get_room($room_id);
        if (!$room) {
            return 0;
        }
        
        $breakdown = self::get_price_breakdown($room_id, $check_in, $check_out);
        if (!$breakdown) {
            return 0;
        }
        
        return $breakdown['total'];
    }
    
    /**
     * Get the nightly multiplier for a single date, honoring scope precedence:
     * room override → room type at location → location-wide → global → 1.0.
     */
    public static function get_nightly_multiplier($room_id, $date, $room_type_term_id = 0) {
        global $wpdb;
        $table = $wpdb->prefix . 'shb_pricing_rules';
        
        $room = SHB_Room::get_room($room_id);
        if (!$room) {
            return 1.0;
        }
        
        $location_id = absint($room['location_id']);
        if (!$room_type_term_id) {
            $terms = get_the_terms($room_id, 'shb_room_type');
            $room_type_term_id = !empty($terms) ? intval($terms[0]->term_id) : 0;
        }
        
        if (null === self::$active_rules) {
            self::$active_rules = $wpdb->get_results(
                "SELECT * FROM $table WHERE is_active = 1",
                ARRAY_A
            );
        }

        $rules = self::$active_rules;
        
        // 1. Room override
        $multiplier = self::resolve_rules($rules, array('room_id' => $room_id), $date);
        if (null !== $multiplier) {
            return $multiplier;
        }
        
        // 2. Room type at the location
        if ($location_id && $room_type_term_id) {
            $multiplier = self::resolve_rules($rules, array('location_id' => $location_id, 'room_type_term_id' => $room_type_term_id), $date);
            if (null !== $multiplier) {
                return $multiplier;
            }
        }
        
        // 3. Location-wide rule
        if ($location_id) {
            $multiplier = self::resolve_rules($rules, array('location_id' => $location_id), $date);
            if (null !== $multiplier) {
                return $multiplier;
            }
        }
        
        // 4. Global rule (no scope columns set). Legacy room_type rules apply here.
        $multiplier = self::resolve_rules($rules, array(), $date, $room['room_type']);
        if (null !== $multiplier) {
            return $multiplier;
        }
        
        // 5. Room base price (no multiplier)
        return 1.0;
    }
    
    /**
     * Find the best matching rule for a scope and date.
     * Rules with date ranges must contain the date; rules without apply always.
     * Returns the multiplier or null when nothing matches.
     */
    private static function resolve_rules($rules, $scope, $date, $legacy_room_type = '') {
        $matched = null;

        foreach ($rules as $rule) {
            // Scope match.
            if (array_key_exists('room_id', $scope)) {
                if (absint($rule['room_id']) !== $scope['room_id']) {
                    continue;
                }
            } elseif (array_key_exists('room_type_term_id', $scope)) {
                if (
                    absint($rule['room_type_term_id']) !== $scope['room_type_term_id'] ||
                    absint($rule['location_id']) !== $scope['location_id']
                ) {
                    continue;
                }
            } elseif (array_key_exists('location_id', $scope)) {
                if (absint($rule['location_id']) !== $scope['location_id']) {
                    continue;
                }
            } else {
                // Global: must have no scope columns, but legacy room_type still filters.
                if (!empty($rule['location_id']) || !empty($rule['room_id']) || !empty($rule['room_type_term_id'])) {
                    continue;
                }
                if (!empty($rule['room_type']) && $rule['room_type'] !== $legacy_room_type) {
                    continue;
                }
            }

            // Date range must contain the date.
            if (!empty($rule['start_date']) && !empty($rule['end_date'])) {
                if ($date < $rule['start_date'] || $date > $rule['end_date']) {
                    continue;
                }
            }

            // First matching rule in precedence order wins; later rows can't override.
            if (null === $matched) {
                $matched = floatval($rule['multiplier']);
                self::$last_applied_rule_ids[] = intval($rule['id']);
            }
        }
        
        return $matched;
    }

    /**
     * Get rule IDs applied by the most recent price calculation.
     */
    public static function get_last_applied_rule_ids() {
        return array_values(array_unique(self::$last_applied_rule_ids));
    }

    /**
     * Reset the request-scoped applied-rule tracker (start of a calculation).
     */
    private static function reset_applied_rules() {
        self::$last_applied_rule_ids = array();
    }
    
    /**
     * Get pricing multiplier for a stay, averaged across nights.
     * Kept for backward compatibility; new code should use get_nightly_multiplier().
     */
    public static function get_pricing_multiplier($room_type, $check_in, $check_out, $room_id = 0) {
        if (!$room_id) {
            // Legacy call path: fall back to the old global + room-type behavior.
            global $wpdb;
            $table = $wpdb->prefix . 'shb_pricing_rules';
            
            if (null === self::$active_rules) {
                self::$active_rules = $wpdb->get_results(
                    "SELECT * FROM $table WHERE is_active = 1",
                    ARRAY_A
                );
            }
            
            $multiplier = 1.0;
            $check_in_date = new DateTime($check_in);
            $check_out_date = new DateTime($check_out);
            
            foreach (self::$active_rules as $rule) {
                if (!empty($rule['location_id']) || !empty($rule['room_id']) || !empty($rule['room_type_term_id'])) {
                    continue;
                }
                if (!empty($rule['room_type']) && $rule['room_type'] !== $room_type) {
                    continue;
                }
                if (!empty($rule['start_date']) && !empty($rule['end_date'])) {
                    $rule_start = new DateTime($rule['start_date']);
                    $rule_end = new DateTime($rule['end_date']);
                    if ($check_in_date < $rule_start || $check_out_date > $rule_end) {
                        continue;
                    }
                }
                $multiplier *= floatval($rule['multiplier']);
            }
            
            return $multiplier;
        }
        
        $nights = SHB_Booking::calculate_nights($check_in, $check_out);
        if ($nights <= 0) {
            return 1.0;
        }
        
        $total_multiplier = 1.0;
        $date = new DateTime($check_in);
        $end = new DateTime($check_out);
        for ($d = $date; $d < $end; $d = $d->modify('+1 day')) {
            $total_multiplier *= self::get_nightly_multiplier($room_id, $d->format('Y-m-d'));
        }
        
        return $total_multiplier;
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
            'location_id' => !empty($data['location_id']) ? absint($data['location_id']) : null,
            'room_id' => !empty($data['room_id']) ? absint($data['room_id']) : null,
            'room_type_term_id' => !empty($data['room_type_term_id']) ? absint($data['room_type_term_id']) : null,
            'start_date' => !empty($data['start_date']) ? sanitize_text_field($data['start_date']) : null,
            'end_date' => !empty($data['end_date']) ? sanitize_text_field($data['end_date']) : null,
            'multiplier' => floatval($data['multiplier']),
            'is_active' => isset($data['is_active']) ? intval($data['is_active']) : 1,
        ));
        
        if ($result === false) {
            return new WP_Error('db_error', __('Failed to create pricing rule', 'sanctuary-hotel-booking'));
        }
        
        self::$active_rules = null;

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
        if (isset($data['location_id'])) {
            $update_data['location_id'] = !empty($data['location_id']) ? absint($data['location_id']) : null;
        }
        if (isset($data['room_id'])) {
            $update_data['room_id'] = !empty($data['room_id']) ? absint($data['room_id']) : null;
        }
        if (isset($data['room_type_term_id'])) {
            $update_data['room_type_term_id'] = !empty($data['room_type_term_id']) ? absint($data['room_type_term_id']) : null;
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
        
        if ($result !== false) {
            self::$active_rules = null;
        }

        return $result !== false;
    }
    
    /**
     * Delete pricing rule
     */
    public static function delete_pricing_rule($rule_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'shb_pricing_rules';
        
        $result = $wpdb->delete($table, array('id' => $rule_id));

        if ($result !== false) {
            self::$active_rules = null;
        }

        return $result;
    }
    
    /**
     * Get price breakdown for a stay, computed per night with scope precedence.
     * Includes the applied rule IDs per night for the booking price snapshot.
     */
    public static function get_price_breakdown($room_id, $check_in, $check_out) {
        $room = SHB_Room::get_room($room_id);
        if (!$room) {
            return null;
        }
        
        $nights = SHB_Booking::calculate_nights($check_in, $check_out);
        if ($nights <= 0) {
            return null;
        }
        
        $base_price = $room['base_price'];
        $nightly_rates = array();
        $applied_rule_ids = array();
        $total_multiplier = 1.0;

        self::reset_applied_rules();

        $date = new DateTime($check_in);
        $end = new DateTime($check_out);
        for ($d = $date; $d < $end; $d = $d->modify('+1 day')) {
            $night = $d->format('Y-m-d');
            $multiplier = self::get_nightly_multiplier($room_id, $night);
            $nightly_rates[] = array(
                'date' => $night,
                'base_price' => $base_price,
                'multiplier' => $multiplier,
                'rate' => round($base_price * $multiplier, 2),
            );
            $total_multiplier *= $multiplier;
        }

        $applied_rule_ids = self::get_last_applied_rule_ids();

        $subtotal = $base_price * $nights;
        $total = 0;
        foreach ($nightly_rates as $rate) {
            $total += $rate['rate'];
        }
        $total = round($total, 2);

        return array(
            'base_price' => $base_price,
            'nights' => $nights,
            'subtotal' => $subtotal,
            'multiplier' => round($total_multiplier, 4),
            'adjustment' => ($total_multiplier - 1) * 100,
            'nightly_rates' => $nightly_rates,
            'applied_rule_ids' => $applied_rule_ids,
            'total' => $total,
        );
    }
}
