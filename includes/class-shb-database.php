<?php
/**
 * Database Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Database {
    
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Pricing Rules Table
        $table_pricing = $wpdb->prefix . 'shb_pricing_rules';
        $sql_pricing = "CREATE TABLE $table_pricing (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            rule_type varchar(50) NOT NULL,
            room_type varchar(50) DEFAULT NULL,
            start_date date DEFAULT NULL,
            end_date date DEFAULT NULL,
            multiplier decimal(5,2) NOT NULL DEFAULT 1.00,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Availability Blocks Table
        $table_availability = $wpdb->prefix . 'shb_availability_blocks';
        $sql_availability = "CREATE TABLE $table_availability (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            room_id bigint(20) NOT NULL,
            start_date date NOT NULL,
            end_date date NOT NULL,
            reason varchar(100) NOT NULL DEFAULT 'maintenance',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY room_id (room_id)
        ) $charset_collate;";
        
        // Payment Transactions Table
        $table_payments = $wpdb->prefix . 'shb_payment_transactions';
        $sql_payments = "CREATE TABLE $table_payments (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            booking_id bigint(20) NOT NULL,
            session_id varchar(255) NOT NULL,
            amount decimal(10,2) NOT NULL,
            currency varchar(10) NOT NULL DEFAULT 'USD',
            payment_method varchar(50) NOT NULL,
            payment_status varchar(50) NOT NULL DEFAULT 'pending',
            metadata text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY booking_id (booking_id),
            KEY session_id (session_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_pricing);
        dbDelta($sql_availability);
        dbDelta($sql_payments);
        
        // Insert default pricing rules
        self::insert_default_pricing_rules();
    }
    
    private static function insert_default_pricing_rules() {
        global $wpdb;
        $table = $wpdb->prefix . 'shb_pricing_rules';
        
        // Check if rules exist
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        if ($count > 0) {
            return;
        }
        
        // Insert default rules
        $wpdb->insert($table, array(
            'name' => 'Weekend Premium',
            'rule_type' => 'weekend',
            'multiplier' => 1.20,
            'is_active' => 1,
        ));
        
        $wpdb->insert($table, array(
            'name' => 'Early Bird Discount',
            'rule_type' => 'early_bird',
            'multiplier' => 0.90,
            'is_active' => 0,
        ));
    }
}
