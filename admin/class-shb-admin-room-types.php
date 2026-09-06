<?php
/**
 * Admin Room Types Handler
 *
 * Renders the Room Types screen under Hotel Booking. The screen edits the
 * defaults template (term meta) that prefills new rooms of each type. Terms
 * themselves are still created/renamed/deleted on the taxonomy screen.
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

        // Handle save before rendering the edit screen.
        if (isset($_POST['shb_save_room_type_defaults'])) {
            if (!wp_verify_nonce($_POST['shb_room_type_defaults_nonce'] ?? '', 'shb_room_type_defaults_save')) {
                wp_die(__('Security check failed.', 'sanctuary-hotel-booking'));
            }

            $saved = SHB_Room_Type::save_defaults($type_id, $_POST);
            if (is_wp_error($saved)) {
                echo '<div class="notice notice-error"><p>' . esc_html($saved->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>' . __('Room type defaults saved.', 'sanctuary-hotel-booking') . '</p></div>';
            }
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
        include SHB_PLUGIN_DIR . 'admin/views/room-types.php';
    }
}
