<?php
/**
 * Plugin Name: Sanctuary Hotel Booking
 * Plugin URI: https://example.com/sanctuary-hotel-booking
 * Description: A comprehensive hotel/guest house booking reservation system with Stripe/PayPal payments and calendar event generation.
 * Version: 1.3.0
 * Author: Sanctuary Hotels
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sanctuary-hotel-booking
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('SHB_VERSION', '1.3.0');
define('SHB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SHB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SHB_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main Plugin Class
 */
class Sanctuary_Hotel_Booking {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_dependencies();
        $this->init_hooks();
    }
    
    private function load_dependencies() {
        // Core includes
        require_once SHB_PLUGIN_DIR . 'includes/class-shb-post-types.php';
        require_once SHB_PLUGIN_DIR . 'includes/class-shb-database.php';
        require_once SHB_PLUGIN_DIR . 'includes/class-shb-room.php';
        require_once SHB_PLUGIN_DIR . 'includes/class-shb-booking.php';
        require_once SHB_PLUGIN_DIR . 'includes/class-shb-pricing.php';
        require_once SHB_PLUGIN_DIR . 'includes/class-shb-availability.php';
        require_once SHB_PLUGIN_DIR . 'includes/class-shb-calendar.php';
        require_once SHB_PLUGIN_DIR . 'includes/class-shb-payments.php';
        require_once SHB_PLUGIN_DIR . 'includes/class-shb-shortcodes.php';
        require_once SHB_PLUGIN_DIR . 'includes/class-shb-ajax.php';
        
        // Admin includes
        if (is_admin()) {
            require_once SHB_PLUGIN_DIR . 'admin/class-shb-admin.php';
            require_once SHB_PLUGIN_DIR . 'admin/class-shb-admin-settings.php';
            require_once SHB_PLUGIN_DIR . 'admin/class-shb-admin-bookings.php';
            require_once SHB_PLUGIN_DIR . 'admin/class-shb-admin-pricing.php';
        }
        
        // Public includes
        require_once SHB_PLUGIN_DIR . 'public/class-shb-public.php';
    }
    
    /**
     * Get the booking page URL
     */
    public static function get_booking_url($room_id = 0, $args = array()) {
        $page_id = get_option('shb_booking_page_id');
        $url = $page_id ? get_permalink($page_id) : home_url('/book/');
        
        if ($room_id) {
            $args['room_id'] = $room_id;
        }
        if (!empty($args)) {
            $url = add_query_arg($args, $url);
        }
        return $url;
    }
    
    /**
     * Get the confirmation page URL
     */
    public static function get_confirmation_url($booking_ref = '') {
        $page_id = get_option('shb_confirmation_page_id');
        $url = $page_id ? get_permalink($page_id) : home_url('/booking-confirmation/');
        if ($booking_ref) {
            $url = add_query_arg('booking_ref', $booking_ref, $url);
        }
        return $url;
    }
    
    private function init_hooks() {
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Register post types EARLY - priority 0
        add_action('init', array('SHB_Post_Types', 'register_post_types'), 0);
        add_action('init', array('SHB_Post_Types', 'register_taxonomies'), 0);
        
        // Initialize other components after post types
        add_action('init', array($this, 'init'), 10);
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    public function init() {
        // Initialize shortcodes
        SHB_Shortcodes::init();
        
        // Initialize AJAX handlers
        SHB_Ajax::init();
        
        // Initialize admin
        if (is_admin()) {
            SHB_Admin::init();
        }
        
        // Initialize public
        SHB_Public::init();
    }
    
    public function activate() {
        // Create database tables
        SHB_Database::create_tables();
        
        // Register post types first
        SHB_Post_Types::register_post_types();
        SHB_Post_Types::register_taxonomies();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set default options
        $this->set_default_options();
        
        // Create sample data
        $this->create_sample_data();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    private function set_default_options() {
        $defaults = array(
            'shb_currency' => 'USD',
            'shb_currency_symbol' => '$',
            'shb_check_in_time' => '14:00',
            'shb_check_out_time' => '11:00',
            'shb_stripe_enabled' => '1',
            'shb_stripe_test_mode' => '1',
            'shb_paypal_enabled' => '0',
            'shb_email_notifications' => '1',
            'shb_admin_email' => get_option('admin_email'),
        );
        
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                update_option($key, $value);
            }
        }
        
        // Create booking flow pages
        $this->create_booking_pages();
    }
    
    /**
     * Auto-create pages needed for booking flow
     */
    private function create_booking_pages() {
        $pages = array(
            'shb_booking_page_id' => array(
                'title' => 'Book a Room',
                'content' => '[shb_booking_form]',
                'slug' => 'book',
            ),
            'shb_confirmation_page_id' => array(
                'title' => 'Booking Confirmation',
                'content' => '[shb_booking_confirmation]',
                'slug' => 'booking-confirmation',
            ),
            'shb_my_bookings_page_id' => array(
                'title' => 'My Bookings',
                'content' => '[shb_my_bookings]',
                'slug' => 'my-bookings',
            ),
        );
        
        foreach ($pages as $option_key => $page_data) {
            $existing_id = get_option($option_key);
            if ($existing_id && get_post_status($existing_id) === 'publish') {
                continue;
            }
            
            $page_id = wp_insert_post(array(
                'post_title' => $page_data['title'],
                'post_content' => $page_data['content'],
                'post_name' => $page_data['slug'],
                'post_type' => 'page',
                'post_status' => 'publish',
            ));
            
            if ($page_id && !is_wp_error($page_id)) {
                update_option($option_key, $page_id);
            }
        }
    }
    
    private function create_sample_data() {
        // Check if sample data already exists
        $existing_rooms = get_posts(array(
            'post_type' => 'shb_room',
            'posts_per_page' => 1,
            'post_status' => 'any',
        ));
        
        if (!empty($existing_rooms)) {
            return;
        }
        
        // Create sample rooms
        $rooms = array(
            array(
                'title' => 'Cozy Standard Room',
                'description' => 'A comfortable room perfect for solo travelers or couples. Features a queen-size bed, modern amenities, and a peaceful atmosphere.',
                'room_type' => 'standard',
                'base_price' => 120.00,
                'max_guests' => 2,
                'amenities' => array('WiFi', 'TV', 'Air Conditioning', 'Mini Fridge', 'Coffee Maker'),
            ),
            array(
                'title' => 'Forest View Deluxe',
                'description' => 'Spacious deluxe room with stunning forest views. King-size bed, private balcony, and premium amenities for a memorable stay.',
                'room_type' => 'deluxe',
                'base_price' => 180.00,
                'max_guests' => 3,
                'amenities' => array('WiFi', 'TV', 'Air Conditioning', 'Mini Bar', 'Balcony', 'Room Service', 'Safe'),
            ),
            array(
                'title' => 'Executive Suite',
                'description' => 'Our finest accommodation featuring a separate living area, luxury bathroom, and panoramic views. Perfect for families or those seeking extra comfort.',
                'room_type' => 'suite',
                'base_price' => 320.00,
                'max_guests' => 4,
                'amenities' => array('WiFi', 'Smart TV', 'Air Conditioning', 'Full Kitchen', 'Jacuzzi', 'Balcony', 'Room Service', 'Safe', 'Workspace'),
            ),
        );
        
        foreach ($rooms as $room_data) {
            $post_id = wp_insert_post(array(
                'post_title' => $room_data['title'],
                'post_content' => $room_data['description'],
                'post_type' => 'shb_room',
                'post_status' => 'publish',
            ));
            
            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, '_shb_room_type', $room_data['room_type']);
                update_post_meta($post_id, '_shb_base_price', $room_data['base_price']);
                update_post_meta($post_id, '_shb_max_guests', $room_data['max_guests']);
                update_post_meta($post_id, '_shb_amenities', $room_data['amenities']);
                update_post_meta($post_id, '_shb_is_active', '1');
            }
        }
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('sanctuary-hotel-booking', false, dirname(SHB_PLUGIN_BASENAME) . '/languages');
    }
    
    public function enqueue_public_assets() {
        // CSS
        wp_enqueue_style(
            'shb-public-css',
            SHB_PLUGIN_URL . 'assets/css/public.css',
            array(),
            SHB_VERSION
        );
        
        // Dynamic styles from settings
        $primary = get_option('shb_primary_color', '#4a7c59');
        $accent = get_option('shb_accent_color', '#d4a574');
        $card_style = get_option('shb_card_style', 'default');
        $button_style = get_option('shb_button_style', 'rounded');
        $font_family = get_option('shb_font_family', '');
        $custom_css = get_option('shb_custom_css', '');
        
        // Darken primary color for hover
        $primary_dark = self::darken_color($primary, 15);
        
        $dynamic_css = ":root {";
        $dynamic_css .= "--shb-primary: {$primary};";
        $dynamic_css .= "--shb-primary-dark: {$primary_dark};";
        $dynamic_css .= "--shb-accent: {$accent};";
        $dynamic_css .= "}";
        
        if ($font_family) {
            $dynamic_css .= ".shb-room-search, .shb-room-list, .shb-booking-form-wrap, .shb-booking-confirmation, .shb-my-bookings { font-family: {$font_family}; }";
        }
        
        // Card styles
        if ($card_style === 'flat') {
            $dynamic_css .= ".shb-room-card { box-shadow: none; border: 1px solid var(--shb-border); }";
            $dynamic_css .= ".shb-room-card:hover { box-shadow: none; }";
        } elseif ($card_style === 'bordered') {
            $dynamic_css .= ".shb-room-card { box-shadow: none; border: 2px solid var(--shb-border); border-radius: 4px; }";
            $dynamic_css .= ".shb-room-image { border-radius: 0; }";
        } elseif ($card_style === 'minimal') {
            $dynamic_css .= ".shb-room-card { box-shadow: none; border: none; border-bottom: 1px solid var(--shb-border); border-radius: 0; }";
            $dynamic_css .= ".shb-room-card:hover { transform: none; }";
        }
        
        // Button styles
        if ($button_style === 'square') {
            $dynamic_css .= ".shb-button { border-radius: 4px; }";
        } elseif ($button_style === 'soft') {
            $dynamic_css .= ".shb-button { border-radius: 8px; }";
        }
        
        if ($custom_css) {
            $dynamic_css .= $custom_css;
        }
        
        wp_add_inline_style('shb-public-css', $dynamic_css);
        
        // jQuery UI Datepicker
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
        
        // JS
        wp_enqueue_script(
            'shb-public-js',
            SHB_PLUGIN_URL . 'assets/js/public.js',
            array('jquery', 'jquery-ui-datepicker'),
            SHB_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('shb-public-js', 'shb_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('shb_nonce'),
            'currency_symbol' => get_option('shb_currency_symbol', '$'),
            'check_in_time' => get_option('shb_check_in_time', '14:00'),
            'check_out_time' => get_option('shb_check_out_time', '11:00'),
            'booking_url' => self::get_booking_url(),
            'confirmation_url' => self::get_confirmation_url(),
        ));
    }
    
    /**
     * Darken a hex color by a percentage
     */
    private static function darken_color($hex, $percent) {
        $hex = ltrim($hex, '#');
        $r = max(0, hexdec(substr($hex, 0, 2)) - (hexdec(substr($hex, 0, 2)) * $percent / 100));
        $g = max(0, hexdec(substr($hex, 2, 2)) - (hexdec(substr($hex, 2, 2)) * $percent / 100));
        $b = max(0, hexdec(substr($hex, 4, 2)) - (hexdec(substr($hex, 4, 2)) * $percent / 100));
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
    
    public function enqueue_admin_assets($hook) {
        global $post_type;
        
        // Load on plugin pages and room edit pages
        $screen = get_current_screen();
        $load_assets = false;
        
        if ($screen) {
            // Match CPT screens (shb_room, shb_booking)
            if (strpos($screen->id, 'shb_') !== false || strpos($screen->id, 'shb-') !== false) {
                $load_assets = true;
            }
            // Match plugin admin pages (hook can be hotel-booking_page_shb-xxx or sanctuary-hotel-booking)
            if (strpos($hook, 'sanctuary-hotel') !== false || strpos($hook, 'hotel-booking') !== false) {
                $load_assets = true;
            }
            if ($post_type === 'shb_room' || $post_type === 'shb_booking') {
                $load_assets = true;
            }
        }
        
        if (!$load_assets) {
            return;
        }
        
        // CSS
        wp_enqueue_style(
            'shb-admin-css',
            SHB_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            SHB_VERSION
        );
        
        // Chart.js for reports
        wp_enqueue_script(
            'chartjs',
            'https://cdn.jsdelivr.net/npm/chart.js',
            array(),
            '4.4.0',
            true
        );
        
        // JS
        wp_enqueue_script(
            'shb-admin-js',
            SHB_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery', 'chartjs'),
            SHB_VERSION,
            true
        );
        
        // Localize script
        wp_localize_script('shb-admin-js', 'shb_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('shb_admin_nonce'),
        ));
    }
}

// Initialize plugin
function sanctuary_hotel_booking() {
    return Sanctuary_Hotel_Booking::get_instance();
}

// Start the plugin
sanctuary_hotel_booking();
