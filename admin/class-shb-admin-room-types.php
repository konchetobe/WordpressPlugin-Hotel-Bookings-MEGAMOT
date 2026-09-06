<?php
/**
 * Admin Room Types Handler
 *
 * Full Room Types manager under Hotel Booking: create/rename/delete types AND
 * edit each type's defaults template (term meta) that prefills new rooms.
 * Terms are managed here; the raw taxonomy screen is no longer surfaced.
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Admin_Room_Types {

    public static function render_page() {
        if (!current_user_can('manage_options') && !current_user_can('shb_manage_rooms')) {
            wp_die(__('You do not have permission to manage room types.', 'sanctuary-hotel-booking'));
        }

        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $type_id = isset($_GET['type_id']) ? absint($_GET['type_id']) : 0;

        // Create a new type term.
        if (isset($_POST['shb_add_room_type'])) {
            if (!wp_verify_nonce($_POST['shb_room_type_nonce'] ?? '', 'shb_room_type_manage')) {
                wp_die(__('Security check failed.', 'sanctuary-hotel-booking'));
            }
            $created = self::create_type($_POST);
            if (is_wp_error($created)) {
                echo '<div class="notice notice-error"><p>' . esc_html($created->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>' . __('Room type created.', 'sanctuary-hotel-booking') . '</p></div>';
            }
        }

        // Update type (rename + defaults) or delete.
        if ($action === 'edit' && isset($_POST['shb_save_room_type'])) {
            if (!wp_verify_nonce($_POST['shb_room_type_nonce'] ?? '', 'shb_room_type_manage')) {
                wp_die(__('Security check failed.', 'sanctuary-hotel-booking'));
            }
            $saved = self::save_type($type_id, $_POST);
            if (is_wp_error($saved)) {
                echo '<div class="notice notice-error"><p>' . esc_html($saved->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>' . __('Room type saved.', 'sanctuary-hotel-booking') . '</p></div>';
            }
        }

        if ($action === 'delete' && isset($_POST['shb_delete_room_type'])) {
            if (!wp_verify_nonce($_POST['shb_room_type_nonce'] ?? '', 'shb_room_type_manage')) {
                wp_die(__('Security check failed.', 'sanctuary-hotel-booking'));
            }
            $deleted = self::delete_type($type_id);
            if (is_wp_error($deleted)) {
                echo '<div class="notice notice-error"><p>' . esc_html($deleted->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>' . __('Room type deleted.', 'sanctuary-hotel-booking') . '</p></div>';
            }
            $action = 'list';
        }

        if ($action === 'edit' && $type_id) {
            $room_type = SHB_Room_Type::get_room_type($type_id);
            if (!$room_type) {
                echo '<div class="notice notice-error"><p>' . __('Room type not found.', 'sanctuary-hotel-booking') . '</p></div>';
                $room_type = null;
            }
            include SHB_PLUGIN_DIR . 'admin/views/room-types.php';
            return;
        }

        $room_types = SHB_Room_Type::get_room_types();

        // Add a per-type room count for the list.
        foreach ($room_types as &$type) {
            $type['room_count'] = self::count_rooms_by_type($type['term_id']);
        }
        unset($type);

        include SHB_PLUGIN_DIR . 'admin/views/room-types.php';
    }

    /**
     * Create a new room type term from the add form.
     */
    public static function create_type($data) {
        $name = sanitize_text_field($data['type_name'] ?? '');
        if (empty($name)) {
            return new WP_Error('missing_name', __('Room type name is required.', 'sanctuary-hotel-booking'));
        }

        $args = array();
        $slug = sanitize_title($data['type_slug'] ?? '');
        if ($slug) {
            $args['slug'] = $slug;
        }
        $description = sanitize_textarea_field($data['type_description'] ?? '');
        if ($description) {
            $args['description'] = $description;
        }

        $inserted = wp_insert_term($name, 'shb_room_type', $args);
        if (is_wp_error($inserted)) {
            return $inserted;
        }

        return $inserted['term_id'];
    }

    /**
     * Update a type's name/slug/description and its defaults template.
     */
    public static function save_type($type_id, $data) {
        $type_id = absint($type_id);
        $term = get_term($type_id, 'shb_room_type');
        if (!$term || is_wp_error($term)) {
            return new WP_Error('invalid_term', __('Room type not found.', 'sanctuary-hotel-booking'));
        }

        $name = sanitize_text_field($data['type_name'] ?? '');
        if (empty($name)) {
            return new WP_Error('missing_name', __('Room type name is required.', 'sanctuary-hotel-booking'));
        }

        $args = array('name' => $name);
        $slug = sanitize_title($data['type_slug'] ?? '');
        if ($slug) {
            $args['slug'] = $slug;
        }
        $description = sanitize_textarea_field($data['type_description'] ?? '');
        if ($description !== $term->description) {
            $args['description'] = $description;
        }

        $updated = wp_update_term($type_id, 'shb_room_type', $args);
        if (is_wp_error($updated)) {
            return $updated;
        }

        // Save the defaults template (same term meta as before).
        $defaults_saved = SHB_Room_Type::save_defaults($type_id, $data);
        if (is_wp_error($defaults_saved)) {
            return $defaults_saved;
        }

        return $type_id;
    }

    /**
     * Delete a type. Refuses while rooms still carry the term.
     */
    public static function delete_type($type_id) {
        $type_id = absint($type_id);
        $term = get_term($type_id, 'shb_room_type');
        if (!$term || is_wp_error($term)) {
            return new WP_Error('invalid_term', __('Room type not found.', 'sanctuary-hotel-booking'));
        }

        $count = self::count_rooms_by_type($type_id);
        if ($count > 0) {
            return new WP_Error(
                'has_rooms',
                sprintf(
                    /* translators: %d: number of rooms. */
                    __('Cannot delete this room type: %d room(s) still use it. Reassign them first.', 'sanctuary-hotel-booking'),
                    $count
                )
            );
        }

        $deleted = wp_delete_term($type_id, 'shb_room_type');
        if (is_wp_error($deleted)) {
            return $deleted;
        }

        // Defaults meta cleanup happens via the delete_shb_room_type hook.
        return true;
    }

    /**
     * Count rooms that carry a given room type term.
     */
    public static function count_rooms_by_type($term_id) {
        $objects = get_objects_in_term($term_id, 'shb_room_type');
        return count($objects);
    }
}
