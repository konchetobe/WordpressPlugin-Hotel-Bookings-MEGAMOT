<?php
/**
 * Admin Rooms Handler
 *
 * Plugin-owned Rooms management: a list screen and a "Room Setup" form that
 * replaces the Gutenberg editor for shb_room posts. Mirrors the Locations
 * screen pattern (SHB_Admin_Locations).
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Admin_Rooms {

    public static function render_page() {
        $is_admin = current_user_can('manage_options');

        // Location managers can manage rooms only in their assigned locations.
        if (!$is_admin && !current_user_can('shb_manage_rooms')) {
            wp_die(__('You do not have permission to manage rooms.', 'sanctuary-hotel-booking'));
        }

        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $room_id = isset($_GET['room_id']) ? absint($_GET['room_id']) : 0;

        if (($action === 'edit' || $action === 'delete') && $room_id) {
            $room = SHB_Room::get_room($room_id);
            if (!$room) {
                echo '<div class="notice notice-error"><p>' . __('Room not found.', 'sanctuary-hotel-booking') . '</p></div>';
                $action = 'list';
            } elseif (!$is_admin && !SHB_Location::user_can_manage($room['location_id'])) {
                wp_die(__('You do not have permission to manage this room.', 'sanctuary-hotel-booking'));
            }
        }

        // Handle delete (form POST with nonce).
        if ($action === 'delete' && isset($_POST['shb_delete_room'])) {
            if (!wp_verify_nonce($_POST['shb_room_delete_nonce'] ?? '', 'shb_room_delete')) {
                wp_die(__('Security check failed.', 'sanctuary-hotel-booking'));
            }
            if (!$is_admin) {
                wp_die(__('You do not have permission to delete rooms.', 'sanctuary-hotel-booking'));
            }
            $deleted = self::delete_room($room_id);
            if (is_wp_error($deleted)) {
                echo '<div class="notice notice-error"><p>' . esc_html($deleted->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>' . __('Room deleted.', 'sanctuary-hotel-booking') . '</p></div>';
            }
            $action = 'list';
        } elseif ($action === 'delete' && $room_id) {
            self::render_delete_confirm($room_id);
            return;
        }

        if ($action === 'edit' || ($action === 'new' && $is_admin)) {
            self::render_edit_page($room_id);
            return;
        }

        // List: admins see all rooms; managers only their assigned locations.
        $args = array('all' => true);
        $locations = $is_admin ? SHB_Location::get_locations() : SHB_Location::get_locations_for_user();
        if (!$is_admin) {
            $args['location_ids'] = wp_list_pluck($locations, 'id');
        }
        $location_filter = isset($_GET['shb_location_id']) ? absint($_GET['shb_location_id']) : 0;
        if ($location_filter && ($is_admin || SHB_Location::user_can_manage($location_filter))) {
            $args['location_id'] = $location_filter;
        }
        $type_filter = isset($_GET['shb_room_type']) ? sanitize_title($_GET['shb_room_type']) : '';
        $rooms = SHB_Room::get_rooms($args);

        if ($type_filter) {
            $rooms = array_values(array_filter($rooms, function ($room) use ($type_filter) {
                return $room['room_type'] === $type_filter;
            }));
        }

        include SHB_PLUGIN_DIR . 'admin/views/rooms.php';
    }

    public static function render_edit_page($room_id = 0) {
        $room = $room_id ? SHB_Room::get_room($room_id) : null;

        // Handle save.
        if (isset($_POST['shb_save_room'])) {
            if (!wp_verify_nonce($_POST['shb_room_setup_nonce'] ?? '', 'shb_room_setup_save')) {
                wp_die(__('Security check failed.', 'sanctuary-hotel-booking'));
            }

            $target_id = absint($_POST['room_id'] ?? 0);
            if (!current_user_can('manage_options')) {
                if ($target_id) {
                    $existing = SHB_Room::get_room($target_id);
                    if (!$existing || !SHB_Location::user_can_manage($existing['location_id'])) {
                        wp_die(__('You do not have permission to manage this room.', 'sanctuary-hotel-booking'));
                    }
                } else {
                    wp_die(__('You do not have permission to create rooms.', 'sanctuary-hotel-booking'));
                }
            }

            $saved = self::save_room($_POST);
            if (is_wp_error($saved)) {
                echo '<div class="notice notice-error"><p>' . esc_html($saved->get_error_message()) . '</p></div>';
            } else {
                $room = SHB_Room::get_room($saved);
                echo '<div class="notice notice-success"><p>' . __('Room saved.', 'sanctuary-hotel-booking') . '</p></div>';
                $room_id = $saved;
            }
        }

        $room_types = SHB_Room_Type::get_room_types();
        $is_admin = current_user_can('manage_options');
        $locations = $is_admin ? SHB_Location::get_locations() : SHB_Location::get_locations_for_user();

        // Preselect a location when arriving from the Locations hub.
        $room = (array) $room;
        if (empty($room['location_id']) && isset($_GET['location_id'])) {
            $room['location_id'] = absint($_GET['location_id']);
        }
        if (!$room_id) {
            $room['id'] = 0;
        }

        include SHB_PLUGIN_DIR . 'admin/views/room-edit.php';
    }

    /**
     * Insert or update a room from the Room Setup form.
     */
    public static function save_room($data) {
        $name = sanitize_text_field($data['name'] ?? '');
        if (empty($name)) {
            return new WP_Error('missing_name', __('Room name is required.', 'sanctuary-hotel-booking'));
        }

        $room_id = absint($data['room_id'] ?? 0);
        $location_id = absint($data['shb_location_id'] ?? 0);
        if (!$location_id || !SHB_Location::get_location($location_id)) {
            return new WP_Error('missing_location', __('A valid location is required.', 'sanctuary-hotel-booking'));
        }

        $post_data = array(
            'post_title' => $name,
            'post_content' => wp_kses_post($data['description'] ?? ''),
            'post_type' => 'shb_room',
            'post_status' => 'publish',
        );

        $slug = sanitize_title($data['slug'] ?? '');
        if ($slug) {
            $post_data['post_name'] = $slug;
        }
        if ($room_id) {
            $post_data['ID'] = $room_id;
        }

        $result = wp_insert_post($post_data, true);
        if (is_wp_error($result)) {
            return $result;
        }

        // Location.
        update_post_meta($result, '_shb_location_id', $location_id);

        // Room type: keep legacy meta in sync with the canonical term.
        $room_type = sanitize_text_field($data['shb_room_type'] ?? '');
        if ($room_type) {
            update_post_meta($result, '_shb_room_type', $room_type);
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
                wp_set_object_terms($result, array((int) $term->term_id), 'shb_room_type', false);
            }
        }

        // Pricing / capacity.
        update_post_meta($result, '_shb_base_price', isset($data['shb_base_price']) ? floatval($data['shb_base_price']) : 0);
        update_post_meta($result, '_shb_max_guests', isset($data['shb_max_guests']) ? intval($data['shb_max_guests']) : 2);

        // Amenities (checkbox + custom).
        $amenities = array();
        if (isset($data['shb_amenities']) && is_array($data['shb_amenities'])) {
            $amenities = array_map('sanitize_text_field', $data['shb_amenities']);
        }
        if (!empty($data['shb_custom_amenities'])) {
            $custom = array_map('trim', explode(',', sanitize_text_field($data['shb_custom_amenities'])));
            $custom = array_filter($custom);
            $amenities = array_merge($amenities, $custom);
        }
        update_post_meta($result, '_shb_amenities', array_values(array_unique($amenities)));

        // Status.
        update_post_meta($result, '_shb_is_active', isset($data['shb_is_active']) ? '1' : '0');

        // Bed / policy / notes text fields.
        foreach (array('shb_bed_type', 'shb_cancellation_policy') as $field) {
            if (isset($data[$field])) {
                update_post_meta($result, '_' . $field, sanitize_text_field($data[$field]));
            }
        }

        // Integer fields.
        foreach (array('shb_room_size', 'shb_floor', 'shb_min_nights', 'shb_max_nights') as $field) {
            if (isset($data[$field])) {
                update_post_meta($result, '_' . $field, intval($data[$field]));
            }
        }

        // Notes.
        if (isset($data['shb_room_notes'])) {
            update_post_meta($result, '_shb_room_notes', sanitize_textarea_field($data['shb_room_notes']));
        }

        // Featured image + gallery.
        $thumbnail_id = absint($data['shb_thumbnail_id'] ?? 0);
        if ($thumbnail_id) {
            set_post_thumbnail($result, $thumbnail_id);
        } else {
            delete_post_thumbnail($result);
        }

        $gallery = array();
        if (!empty($data['shb_gallery_ids'])) {
            $gallery = array_values(array_filter(array_map('absint', explode(',', sanitize_text_field($data['shb_gallery_ids'])))));
        }
        update_post_meta($result, '_shb_gallery', $gallery);

        return $result;
    }

    /**
     * Delete a room. Refuses while any non-cancelled booking references it so
     * the financial/historical record and per-night allocation stay intact.
     */
    public static function delete_room($room_id) {
        $room_id = absint($room_id);
        $room = SHB_Room::get_room($room_id);
        if (!$room) {
            return new WP_Error('not_found', __('Room not found.', 'sanctuary-hotel-booking'));
        }

        $bookings = SHB_Booking::get_bookings_by_room($room_id);
        foreach ($bookings as $booking) {
            if ($booking['booking_status'] !== 'cancelled') {
                return new WP_Error(
                    'has_bookings',
                    __('Cannot delete this room: it has active bookings. Cancel them first.', 'sanctuary-hotel-booking')
                );
            }
        }

        $deleted = wp_delete_post($room_id, true);
        if (!$deleted) {
            return new WP_Error('delete_failed', __('Failed to delete the room.', 'sanctuary-hotel-booking'));
        }

        return true;
    }

    /**
     * Confirm screen shown before deleting a room.
     */
    public static function render_delete_confirm($room_id) {
        $room = SHB_Room::get_room($room_id);
        if (!$room) {
            echo '<div class="notice notice-error"><p>' . __('Room not found.', 'sanctuary-hotel-booking') . '</p></div>';
            return;
        }
        ?>
        <div class="wrap shb-admin-wrap">
            <h1><?php _e('Delete Room', 'sanctuary-hotel-booking'); ?></h1>
            <p>
                <?php
                printf(
                    /* translators: %s: room name. */
                    __('Are you sure you want to delete "%s"? This cannot be undone.', 'sanctuary-hotel-booking'),
                    esc_html($room['name'])
                );
                ?>
            </p>
            <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=shb-rooms&action=delete&room_id=' . $room_id)); ?>">
                <?php wp_nonce_field('shb_room_delete', 'shb_room_delete_nonce'); ?>
                <input type="hidden" name="shb_delete_room" value="1">
                <p>
                    <button type="submit" class="button button-secondary" onclick="return confirm('<?php echo esc_js(__('Delete this room permanently?', 'sanctuary-hotel-booking')); ?>');">
                        <?php _e('Delete Room', 'sanctuary-hotel-booking'); ?>
                    </button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=shb-rooms')); ?>" class="button button-primary">
                        <?php _e('Cancel', 'sanctuary-hotel-booking'); ?>
                    </a>
                </p>
            </form>
        </div>
        <?php
    }
}
