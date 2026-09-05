<?php
/**
 * Database Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Database {

    /**
     * Registry of versioned migrations: DB version => callable.
     *
     * @return array
     */
    public static function get_migrations() {
        return array(
            '1.4.0-beta.1' => array('SHB_Migration', 'run'),
        );
    }

    /**
     * Apply schema changes when the plugin version changes.
     * create_tables()/dbDelta only runs when shb_db_version is behind — it is
     * idempotent but touches the information schema, so avoid it per request.
     */
    public static function maybe_upgrade() {
        $current_version = get_option('shb_db_version', '');

        if (version_compare($current_version, SHB_DB_VERSION, '<')) {
            // Fresh installs run the base schema; upgrades run dbDelta too.
            self::create_tables();
        }

        // Run any pending migrations in version order.
        $migrations = self::get_migrations();
        uksort($migrations, 'version_compare');

        foreach ($migrations as $version => $callback) {
            if (version_compare($version, $current_version, '>')) {
                if (is_callable($callback)) {
                    call_user_func($callback);
                }
                update_option('shb_db_version', $version);
                $current_version = $version;
            }
        }

        // Finalize: a site that ran the 1.4.0-beta.1 migration stays behind
        // after a stable bump (version_compare sees -beta.1 < stable). Store the
        // plugin DB version so schema work does not repeat on every request.
        if (version_compare($current_version, SHB_DB_VERSION, '<')) {
            update_option('shb_db_version', SHB_DB_VERSION);
        }
    }

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
            location_id bigint(20) DEFAULT NULL,
            room_id bigint(20) DEFAULT NULL,
            room_type_term_id bigint(20) DEFAULT NULL,
            start_date date DEFAULT NULL,
            end_date date DEFAULT NULL,
            multiplier decimal(5,2) NOT NULL DEFAULT 1.00,
            is_active tinyint(1) NOT NULL DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY active_type_dates (is_active, room_type, start_date, end_date),
            KEY scope_active (location_id, room_id, room_type_term_id, is_active)
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
            KEY room_id (room_id),
            KEY room_dates (room_id, start_date, end_date)
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

        // Room Nights Allocation Table (one row per occupied night)
        $table_nights = $wpdb->prefix . 'shb_room_nights';
        $sql_nights = "CREATE TABLE $table_nights (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            room_id bigint(20) unsigned NOT NULL,
            location_id bigint(20) unsigned NOT NULL DEFAULT 0,
            booking_id bigint(20) unsigned NOT NULL,
            stay_date date NOT NULL,
            hold_expires_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY room_stay (room_id, stay_date),
            KEY location_stay (location_id, stay_date),
            KEY booking_id (booking_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_pricing);
        dbDelta($sql_availability);
        dbDelta($sql_payments);
        dbDelta($sql_nights);

        update_option('shb_db_version', SHB_DB_VERSION);

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
