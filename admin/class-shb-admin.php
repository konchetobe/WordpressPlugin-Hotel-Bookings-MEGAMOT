<?php
/**
 * Admin Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Admin {
    
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'));
        add_action('save_post_shb_room', array(__CLASS__, 'save_room_meta'), 10, 2);
        
        // Add custom columns to rooms list
        add_filter('manage_shb_room_posts_columns', array(__CLASS__, 'add_room_columns'));
        add_action('manage_shb_room_posts_custom_column', array(__CLASS__, 'render_room_columns'), 10, 2);
        
        // Fix parent menu highlight for taxonomy page
        add_filter('parent_file', array(__CLASS__, 'fix_taxonomy_parent_menu'));
    }
    
    /**
     * Fix parent menu highlight for Room Types taxonomy
     */
    public static function fix_taxonomy_parent_menu($parent_file) {
        global $current_screen;
        if ($current_screen && $current_screen->taxonomy === 'shb_room_type') {
            return 'sanctuary-hotel-booking';
        }
        return $parent_file;
    }
    
    /**
     * Add admin menu
     */
    public static function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('Hotel Booking', 'sanctuary-hotel-booking'),
            __('Hotel Booking', 'sanctuary-hotel-booking'),
            'manage_options',
            'sanctuary-hotel-booking',
            array(__CLASS__, 'dashboard_page'),
            'dashicons-calendar-alt',
            26
        );
        
        // Dashboard submenu (replaces auto-generated one)
        add_submenu_page(
            'sanctuary-hotel-booking',
            __('Dashboard', 'sanctuary-hotel-booking'),
            __('Dashboard', 'sanctuary-hotel-booking'),
            'manage_options',
            'sanctuary-hotel-booking',
            array(__CLASS__, 'dashboard_page')
        );
        
        // Room Types submenu (taxonomy)
        add_submenu_page(
            'sanctuary-hotel-booking',
            __('Room Types', 'sanctuary-hotel-booking'),
            __('Room Types', 'sanctuary-hotel-booking'),
            'manage_options',
            'edit-tags.php?taxonomy=shb_room_type&post_type=shb_room'
        );
        
        // Bookings submenu
        add_submenu_page(
            'sanctuary-hotel-booking',
            __('Bookings', 'sanctuary-hotel-booking'),
            __('Bookings', 'sanctuary-hotel-booking'),
            'manage_options',
            'shb-bookings',
            array('SHB_Admin_Bookings', 'render_page')
        );
        
        // Pricing Rules submenu
        add_submenu_page(
            'sanctuary-hotel-booking',
            __('Pricing Rules', 'sanctuary-hotel-booking'),
            __('Pricing Rules', 'sanctuary-hotel-booking'),
            'manage_options',
            'shb-pricing',
            array('SHB_Admin_Pricing', 'render_page')
        );
        
        // Availability submenu
        add_submenu_page(
            'sanctuary-hotel-booking',
            __('Availability', 'sanctuary-hotel-booking'),
            __('Availability', 'sanctuary-hotel-booking'),
            'manage_options',
            'shb-availability',
            array(__CLASS__, 'availability_page')
        );
        
        // Settings submenu
        add_submenu_page(
            'sanctuary-hotel-booking',
            __('Settings', 'sanctuary-hotel-booking'),
            __('Settings', 'sanctuary-hotel-booking'),
            'manage_options',
            'shb-settings',
            array('SHB_Admin_Settings', 'render_page')
        );
    }
    
    /**
     * Add custom columns to rooms list
     */
    public static function add_room_columns($columns) {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            if ($key === 'cb') {
                $new_columns[$key] = $value;
                $new_columns['room_color'] = '<span class="dashicons dashicons-marker" style="color:#94a3b8" title="' . __('Color Code', 'sanctuary-hotel-booking') . '"></span>';
            } elseif ($key === 'title') {
                $new_columns[$key] = $value;
                $new_columns['room_id_col'] = __('ID / Shortcode', 'sanctuary-hotel-booking');
                $new_columns['room_type'] = __('Type', 'sanctuary-hotel-booking');
                $new_columns['bed_type'] = __('Bed', 'sanctuary-hotel-booking');
                $new_columns['base_price'] = __('Price/Night', 'sanctuary-hotel-booking');
                $new_columns['max_guests'] = __('Max Guests', 'sanctuary-hotel-booking');
                $new_columns['status'] = __('Status', 'sanctuary-hotel-booking');
            } else {
                $new_columns[$key] = $value;
            }
        }
        return $new_columns;
    }
    
    /**
     * Consistent room color from ID
     */
    public static function get_room_color($post_id) {
        $colors = array('#3b82f6','#ef4444','#22c55e','#f59e0b','#8b5cf6','#ec4899','#14b8a6','#f97316','#6366f1','#06b6d4','#84cc16','#e11d48');
        return $colors[$post_id % count($colors)];
    }
    
    /**
     * Render custom columns
     */
    public static function render_room_columns($column, $post_id) {
        switch ($column) {
            case 'room_color':
                $color = self::get_room_color($post_id);
                echo '<span style="display:inline-block;width:14px;height:14px;border-radius:4px;background:' . esc_attr($color) . ';" title="' . esc_attr__('Calendar color for this room', 'sanctuary-hotel-booking') . '"></span>';
                break;
            case 'room_id_col':
                echo '<code style="background:#f1f5f9;padding:2px 8px;border-radius:4px;font-size:12px;user-select:all">' . esc_html($post_id) . '</code>';
                break;
            case 'room_type':
                $type = get_post_meta($post_id, '_shb_room_type', true);
                echo esc_html(ucfirst($type ?: 'standard'));
                break;
            case 'bed_type':
                $bed = get_post_meta($post_id, '_shb_bed_type', true);
                echo esc_html(ucfirst($bed ?: 'queen'));
                break;
            case 'base_price':
                $price = get_post_meta($post_id, '_shb_base_price', true);
                echo esc_html(get_option('shb_currency_symbol', '$') . number_format(floatval($price), 2));
                break;
            case 'max_guests':
                $guests = get_post_meta($post_id, '_shb_max_guests', true);
                echo esc_html($guests ?: '2');
                break;
            case 'status':
                $active = get_post_meta($post_id, '_shb_is_active', true);
                if ($active === '1' || $active === '') {
                    echo '<span style="color: green;">&#x25CF;</span> ' . __('Active', 'sanctuary-hotel-booking');
                } else {
                    echo '<span style="color: red;">&#x25CF;</span> ' . __('Inactive', 'sanctuary-hotel-booking');
                }
                break;
        }
    }
    
    /**
     * Dashboard page
     */
    public static function dashboard_page() {
        $rooms = SHB_Room::get_rooms(array('meta_query' => array()));
        $bookings = SHB_Booking::get_bookings(array('posts_per_page' => 10));
        
        $all_bookings = SHB_Booking::get_bookings();
        $stats = array(
            'total_rooms' => count($rooms),
            'total_bookings' => count($all_bookings),
            'pending_bookings' => 0,
            'confirmed_bookings' => 0,
            'total_revenue' => 0,
        );
        
        foreach ($all_bookings as $booking) {
            if (isset($booking['payment_status']) && $booking['payment_status'] === 'paid') {
                $stats['total_revenue'] += floatval($booking['total_price']);
            }
            if (isset($booking['booking_status'])) {
                if ($booking['booking_status'] === 'pending') {
                    $stats['pending_bookings']++;
                }
                if ($booking['booking_status'] === 'confirmed') {
                    $stats['confirmed_bookings']++;
                }
            }
        }
        
        include SHB_PLUGIN_DIR . 'admin/views/dashboard.php';
    }
    
    /**
     * Availability page
     */
    public static function availability_page() {
        $rooms = SHB_Room::get_rooms();
        $blocks = SHB_Availability::get_availability_blocks();
        
        include SHB_PLUGIN_DIR . 'admin/views/availability.php';
    }
    
    /**
     * Add meta boxes for rooms
     */
    public static function add_meta_boxes() {
        add_meta_box(
            'shb_room_details',
            __('Room Details', 'sanctuary-hotel-booking'),
            array(__CLASS__, 'render_room_meta_box'),
            'shb_room',
            'normal',
            'high'
        );
    }
    
    /**
     * Render room meta box
     */
    public static function render_room_meta_box($post) {
        wp_nonce_field('shb_room_meta_nonce_action', 'shb_room_meta_nonce');
        
        $room_type = get_post_meta($post->ID, '_shb_room_type', true) ?: 'standard';
        $base_price = get_post_meta($post->ID, '_shb_base_price', true) ?: '100';
        $max_guests = get_post_meta($post->ID, '_shb_max_guests', true) ?: '2';
        $amenities = get_post_meta($post->ID, '_shb_amenities', true);
        if (!is_array($amenities)) {
            $amenities = array();
        }
        $is_active = get_post_meta($post->ID, '_shb_is_active', true);
        if ($is_active === '') {
            $is_active = '1'; // Default to active
        }
        
        include SHB_PLUGIN_DIR . 'admin/views/room-meta-box.php';
    }
    
    /**
     * Save room meta
     */
    public static function save_room_meta($post_id, $post) {
        // Verify nonce
        if (!isset($_POST['shb_room_meta_nonce'])) {
            return;
        }
        
        if (!wp_verify_nonce($_POST['shb_room_meta_nonce'], 'shb_room_meta_nonce_action')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Check post type
        if ($post->post_type !== 'shb_room') {
            return;
        }
        
        // Save room type
        if (isset($_POST['shb_room_type'])) {
            update_post_meta($post_id, '_shb_room_type', sanitize_text_field($_POST['shb_room_type']));
        }
        
        // Save base price
        if (isset($_POST['shb_base_price'])) {
            $price = floatval($_POST['shb_base_price']);
            update_post_meta($post_id, '_shb_base_price', $price);
        }
        
        // Save max guests
        if (isset($_POST['shb_max_guests'])) {
            $guests = intval($_POST['shb_max_guests']);
            update_post_meta($post_id, '_shb_max_guests', $guests);
        }
        
        // Save amenities (checkbox + custom)
        $amenities = array();
        if (isset($_POST['shb_amenities']) && is_array($_POST['shb_amenities'])) {
            $amenities = array_map('sanitize_text_field', $_POST['shb_amenities']);
        }
        if (!empty($_POST['shb_custom_amenities'])) {
            $custom = array_map('trim', explode(',', sanitize_text_field($_POST['shb_custom_amenities'])));
            $custom = array_filter($custom);
            $amenities = array_merge($amenities, $custom);
        }
        update_post_meta($post_id, '_shb_amenities', array_unique($amenities));
        
        // Save active status
        $is_active = isset($_POST['shb_is_active']) ? '1' : '0';
        update_post_meta($post_id, '_shb_is_active', $is_active);
        
        // Save new fields
        $text_fields = array('shb_bed_type', 'shb_cancellation_policy');
        foreach ($text_fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
            }
        }
        
        $int_fields = array('shb_room_size', 'shb_floor', 'shb_min_nights', 'shb_max_nights');
        foreach ($int_fields as $field) {
            if (isset($_POST[$field])) {
                update_post_meta($post_id, '_' . $field, intval($_POST[$field]));
            }
        }
        
        if (isset($_POST['shb_room_notes'])) {
            update_post_meta($post_id, '_shb_room_notes', sanitize_textarea_field($_POST['shb_room_notes']));
        }
    }
}
