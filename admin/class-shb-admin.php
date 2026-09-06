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
        add_action('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'));
        add_action('save_post_shb_room', array(__CLASS__, 'save_room_meta'), 10, 2);
        
        // Add custom columns to rooms list
        add_filter('manage_shb_room_posts_columns', array(__CLASS__, 'add_room_columns'));
        add_action('manage_shb_room_posts_custom_column', array(__CLASS__, 'render_room_columns'), 10, 2);
        
        // Room list location filter
        add_action('restrict_manage_posts', array(__CLASS__, 'add_room_location_filter'), 10, 2);
        add_filter('parse_query', array(__CLASS__, 'apply_room_location_filter'));
        
        // Fix parent menu highlight for taxonomy page
        add_filter('parent_file', array(__CLASS__, 'fix_taxonomy_parent_menu'));

        // Keep the taxonomy term screen highlighted under the Room Types screen.
        add_filter('submenu_file', array(__CLASS__, 'fix_room_types_submenu_file'));
    }

    /**
     * Add a location filter dropdown above the rooms list.
     */
    public static function add_room_location_filter($post_type, $which) {
        if ($post_type !== 'shb_room') {
            return;
        }

        // Non-admins only see the locations they manage.
        $locations = current_user_can('manage_options')
            ? SHB_Location::get_locations()
            : SHB_Location::get_locations_for_user();

        if (empty($locations)) {
            return;
        }

        $current = isset($_GET['shb_location_id']) ? absint($_GET['shb_location_id']) : 0;

        echo '<select name="shb_location_id">';
        echo '<option value="0">' . esc_html__('All Locations', 'sanctuary-hotel-booking') . '</option>';
        foreach ($locations as $location) {
            printf(
                '<option value="%d" %s>%s</option>',
                esc_attr($location['id']),
                selected($current, $location['id'], false),
                esc_html($location['name'])
            );
        }
        echo '</select>';
    }

    /**
     * Apply the location filter to the rooms list query, and constrain
     * non-admins to their assigned locations.
     */
    public static function apply_room_location_filter($query) {
        global $pagenow;

        if (!is_admin() || $pagenow !== 'edit.php') {
            return $query;
        }

        if ($query->get('post_type') !== 'shb_room') {
            return $query;
        }

        $allowed = null;
        if (!current_user_can('manage_options') && current_user_can('shb_manage_rooms')) {
            $allowed = SHB_Location::get_locations_for_user();
            $allowed = wp_list_pluck($allowed, 'id');
        }

        $location_id = isset($_GET['shb_location_id']) ? absint($_GET['shb_location_id']) : 0;

        // Non-admins cannot filter to a location outside their assignment.
        if (is_array($allowed) && $location_id && !in_array($location_id, $allowed, true)) {
            $location_id = 0;
        }

        $meta_query = $query->get('meta_query');
        if (!is_array($meta_query)) {
            $meta_query = array();
        }

        if ($location_id) {
            $meta_query[] = array(
                'key' => '_shb_location_id',
                'value' => $location_id,
            );
        } elseif (is_array($allowed) && !empty($allowed)) {
            $meta_query[] = array(
                'key' => '_shb_location_id',
                'value' => $allowed,
                'compare' => 'IN',
            );
        } elseif (is_array($allowed)) {
            // Manager with no assignments sees no rooms.
            $meta_query[] = array(
                'key' => '_shb_location_id',
                'value' => -1,
            );
        }

        $query->set('meta_query', $meta_query);

        return $query;
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
        
        // Room Types submenu: a defaults editor screen, plus a link to the raw
        // taxonomy for term creation/renaming.
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
                $new_columns['location'] = __('Location', 'sanctuary-hotel-booking');
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
            case 'location':
                $location_id = absint(get_post_meta($post_id, '_shb_location_id', true));
                $location = $location_id ? SHB_Location::get_location($location_id) : null;
                if ($location) {
                    echo '<a href="' . esc_url(admin_url('admin.php?page=shb-locations&action=edit&location_id=' . $location['id'])) . '">';
                    echo esc_html($location['name']);
                    echo '</a>';
                } else {
                    echo '<em>' . __('None', 'sanctuary-hotel-booking') . '</em>';
                }
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
        
        $room_type = get_post_meta($post->ID, '_shb_room_type', true) ?: '';
        $base_price = get_post_meta($post->ID, '_shb_base_price', true) ?: '';
        $max_guests = get_post_meta($post->ID, '_shb_max_guests', true) ?: '';
        $amenities = get_post_meta($post->ID, '_shb_amenities', true);
        if (!is_array($amenities)) {
            $amenities = array();
        }
        $is_active = get_post_meta($post->ID, '_shb_is_active', true);
        if ($is_active === '') {
            $is_active = '1'; // Default to active
        }
        $location_id = absint(get_post_meta($post->ID, '_shb_location_id', true));

        // Preselect a location when arriving from the Locations hub.
        if (!$location_id && isset($_GET['location_id'])) {
            $location_id = absint($_GET['location_id']);
        }

        // Non-admins only see locations they manage (admins see all).
        $locations = current_user_can('manage_options')
            ? SHB_Location::get_locations()
            : SHB_Location::get_locations_for_user();

        // Room types (canonical taxonomy) + the stored defaults template for
        // the room's current type, so the meta box can offer one-click prefill.
        $room_types = SHB_Room_Type::get_room_types();
        $room_type_defaults = SHB_Room_Type::get_defaults($room_type);

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
        
        // Save room type: keep the legacy meta in sync with the canonical
        // shb_room_type term so scoped pricing always matches.
        if (isset($_POST['shb_room_type'])) {
            $room_type = sanitize_text_field($_POST['shb_room_type']);
            update_post_meta($post_id, '_shb_room_type', $room_type);

            $term = get_term_by('slug', sanitize_title($room_type), 'shb_room_type');
            if (!$term) {
                $term = get_term_by('name', ucfirst($room_type), 'shb_room_type');
            }
            if (!$term) {
                $inserted = wp_insert_term(ucfirst($room_type), 'shb_room_type', array('slug' => sanitize_title($room_type)));
                if (!is_wp_error($inserted)) {
                    $term = get_term($inserted['term_id'], 'shb_room_type');
                }
            }
            if ($term) {
                wp_set_object_terms($post_id, array((int) $term->term_id), 'shb_room_type', false);
            }
        }

        // Save location (required). Non-admins may only assign rooms to a
        // location they manage, and may only edit rooms in an assigned location.
        if (isset($_POST['shb_location_id'])) {
            $location_id = absint($_POST['shb_location_id']);
            if (!current_user_can('manage_options')) {
                $room_location = absint(get_post_meta($post_id, '_shb_location_id', true));
                if ($room_location && !SHB_Location::user_can_manage($room_location)) {
                    return;
                }
                if ($location_id && !SHB_Location::user_can_manage($location_id)) {
                    return;
                }
            }
            if ($location_id && SHB_Location::get_location($location_id)) {
                update_post_meta($post_id, '_shb_location_id', $location_id);
            }
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
