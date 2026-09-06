<?php
/**
 * Admin Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Admin {
    
    public static function init() {
        // Ensure plugin caps exist on every load (idempotent) so menus
        // remain visible for admins and location managers after upgrades.
        if (class_exists('SHB_Roles')) {
            SHB_Roles::register();
        }

        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        
        // Rooms are edited on the plugin-owned Rooms screen; the legacy
        // Gutenberg meta box and WP rooms list are no longer registered.
        
        // Fix parent menu highlight for taxonomy page
        add_filter('parent_file', array(__CLASS__, 'fix_taxonomy_parent_menu'));

        // Keep the taxonomy term screen highlighted under the Room Types screen.
        add_filter('submenu_file', array(__CLASS__, 'fix_room_types_submenu_file'));

        // Rooms are managed on the plugin-owned Rooms screen, not Gutenberg.
        add_action('admin_init', array(__CLASS__, 'redirect_room_post_new'));
        add_action('load-post.php', array(__CLASS__, 'redirect_room_post_edit'));
        add_filter('use_block_editor_for_post_type', array(__CLASS__, 'disable_block_editor_for_rooms'), 10, 2);
    }

    /**
     * Route "Add New Room" (post-new.php) to the plugin Rooms screen.
     */
    public static function redirect_room_post_new() {
        global $pagenow;
        if ($pagenow !== 'post-new.php') {
            return;
        }
        if (isset($_GET['post_type']) && $_GET['post_type'] === 'shb_room') {
            wp_safe_redirect(admin_url('admin.php?page=shb-rooms&action=new'));
            exit;
        }
    }

    /**
     * Route direct edits of shb_room posts (post.php) to the plugin Rooms screen.
     */
    public static function redirect_room_post_edit() {
        $post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
        $post = $post_id ? get_post($post_id) : null;
        if ($post && $post->post_type === 'shb_room') {
            wp_safe_redirect(admin_url('admin.php?page=shb-rooms&action=edit&room_id=' . $post->ID));
            exit;
        }
    }

    /**
     * Rooms are edited via the plugin Rooms screen; keep Gutenberg off them.
     */
    public static function disable_block_editor_for_rooms($enabled, $post_type) {
        if ($post_type === 'shb_room') {
            return false;
        }
        return $enabled;
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
     * Highlight the Room Types defaults screen when editing terms, so the
     * taxonomy screen always appears under the Room Types submenu.
     */
    public static function fix_room_types_submenu_file($submenu_file) {
        global $current_screen;
        if ($current_screen && $current_screen->taxonomy === 'shb_room_type') {
            return 'shb-room-types';
        }
        return $submenu_file;
    }
    
    /**
     * Add admin menu
     */
    public static function add_admin_menu() {
        // Main menu
        $main_cap = current_user_can('manage_options') || current_user_can('shb_manage_bookings') || current_user_can('shb_manage_rooms')
            ? 'shb_manage_bookings'
            : 'manage_options';

        add_menu_page(
            __('Hotel Booking', 'sanctuary-hotel-booking'),
            __('Hotel Booking', 'sanctuary-hotel-booking'),
            $main_cap,
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
            $main_cap,
            'sanctuary-hotel-booking',
            array(__CLASS__, 'dashboard_page')
        );

        // Rooms submenu: plugin-owned list + Room Setup screen.
        $rooms_cap = current_user_can('manage_options') ? 'manage_options' : 'shb_manage_rooms';
        add_submenu_page(
            'sanctuary-hotel-booking',
            __('Rooms', 'sanctuary-hotel-booking'),
            __('Rooms', 'sanctuary-hotel-booking'),
            $rooms_cap,
            'shb-rooms',
            array('SHB_Admin_Rooms', 'render_page')
        );
        
        // Room Types submenu: full manager (create/rename/delete + defaults).
        $room_types_cap = current_user_can('manage_options') ? 'manage_options' : 'shb_manage_rooms';
        add_submenu_page(
            'sanctuary-hotel-booking',
            __('Room Types', 'sanctuary-hotel-booking'),
            __('Room Types', 'sanctuary-hotel-booking'),
            $room_types_cap,
            'shb-room-types',
            array('SHB_Admin_Room_Types', 'render_page')
        );

        // Locations submenu: admins manage all; location managers manage the
        // assigned locations (edit/delete restricted inside the page).
        $locations_cap = current_user_can('manage_options') ? 'manage_options' : 'shb_manage_locations';
        add_submenu_page(
            'sanctuary-hotel-booking',
            __('Locations', 'sanctuary-hotel-booking'),
            __('Locations', 'sanctuary-hotel-booking'),
            $locations_cap,
            'shb-locations',
            array('SHB_Admin_Locations', 'render_page')
        );
        
        // Bookings submenu
        add_submenu_page(
            'sanctuary-hotel-booking',
            __('Bookings', 'sanctuary-hotel-booking'),
            __('Bookings', 'sanctuary-hotel-booking'),
            'shb_manage_bookings',
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

        // Migration Report submenu
        add_submenu_page(
            'sanctuary-hotel-booking',
            __('Migration', 'sanctuary-hotel-booking'),
            __('Migration', 'sanctuary-hotel-booking'),
            'manage_options',
            'shb-migration',
            array('SHB_Admin_Migration', 'render_page')
        );

        // Reports submenu
        add_submenu_page(
            'sanctuary-hotel-booking',
            __('Reports', 'sanctuary-hotel-booking'),
            __('Reports', 'sanctuary-hotel-booking'),
            'shb_view_reports',
            'shb-reports',
            array('SHB_Admin_Reports', 'render_page')
        );
    }
    
    /**
     * Consistent room color from ID
     */
    public static function get_room_color($post_id) {
        $colors = array('#3b82f6','#ef4444','#22c55e','#f59e0b','#8b5cf6','#ec4899','#14b8a6','#f97316','#6366f1','#06b6d4','#84cc16','#e11d48');
        return $colors[$post_id % count($colors)];
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
        if (!current_user_can('manage_options') && !current_user_can('shb_manage_rooms')) {
            wp_die(__('You do not have permission to manage availability.', 'sanctuary-hotel-booking'));
        }

        $is_admin = current_user_can('manage_options');
        $locations = $is_admin ? SHB_Location::get_locations() : SHB_Location::get_locations_for_user();

        $args = array();
        $location_filter = isset($_GET['location']) ? absint($_GET['location']) : 0;

        // Non-admins cannot filter outside their assigned locations.
        if (!$is_admin && $location_filter && !SHB_Location::user_can_manage($location_filter)) {
            $location_filter = 0;
        }

        if ($location_filter) {
            $args['location_id'] = $location_filter;
        } elseif (!$is_admin) {
            $allowed = wp_list_pluck($locations, 'id');
            $args['location_ids'] = $allowed;
        }

        $rooms = SHB_Room::get_rooms($args);

        // Scope the block list to the rooms visible to this user.
        $room_ids = wp_list_pluck($rooms, 'id');
        $blocks = $is_admin ? SHB_Availability::get_availability_blocks() : array();
        if (!$is_admin && !empty($room_ids)) {
            $blocks = SHB_Availability::get_availability_blocks_by_rooms($room_ids);
        }
        
        include SHB_PLUGIN_DIR . 'admin/views/availability.php';
    }
}
