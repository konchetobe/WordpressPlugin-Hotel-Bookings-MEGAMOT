<?php
/**
 * Admin Locations Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Admin_Locations {

    public static function render_page() {
        $is_admin = current_user_can('manage_options');

        // Location managers can view their assigned locations but not create
        // new ones or edit unassigned ones.
        if (!$is_admin && !current_user_can('shb_manage_locations')) {
            wp_die(__('You do not have permission to manage locations.', 'sanctuary-hotel-booking'));
        }

        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $location_id = isset($_GET['location_id']) ? absint($_GET['location_id']) : 0;

        // Only administrators may delete a location.
        if ($action === 'delete' && !$is_admin) {
            wp_die(__('You do not have permission to delete locations.', 'sanctuary-hotel-booking'));
        }

        if (($action === 'edit' || $action === 'new' || $action === 'delete') && !$is_admin && !SHB_Location::user_can_manage($location_id)) {
            wp_die(__('You do not have permission to manage this location.', 'sanctuary-hotel-booking'));
        }

        // Handle delete request (form POST with nonce, or GET confirm).
        if ($action === 'delete') {
            if (isset($_POST['shb_delete_location'])) {
                if (!wp_verify_nonce($_POST['shb_location_delete_nonce'] ?? '', 'shb_location_delete')) {
                    wp_die(__('Security check failed.', 'sanctuary-hotel-booking'));
                }
                $deleted = self::delete_location($location_id);
                if (is_wp_error($deleted)) {
                    echo '<div class="notice notice-error"><p>' . esc_html($deleted->get_error_message()) . '</p></div>';
                } else {
                    echo '<div class="notice notice-success"><p>' . __('Location deleted.', 'sanctuary-hotel-booking') . '</p></div>';
                    $action = 'list';
                }
            } else {
                // Show a confirm screen when arriving without a POST.
                self::render_delete_confirm($location_id);
                return;
            }
        }

        if ($action === 'edit' || $action === 'new') {
            self::render_edit_page($location_id);
            return;
        }

        $locations = $is_admin ? SHB_Location::get_locations() : SHB_Location::get_locations_for_user();
        include SHB_PLUGIN_DIR . 'admin/views/locations.php';
    }

    /**
     * Refuse deletion while rooms or bookings reference the location, so the
     * historical/financial record and per-night allocation stay consistent.
     */
    public static function delete_location($location_id) {
        $location_id = absint($location_id);
        $location = SHB_Location::get_location($location_id);
        if (!$location) {
            return new WP_Error('not_found', __('Location not found.', 'sanctuary-hotel-booking'));
        }

        $room_count = SHB_Location::get_room_count($location_id);
        if ($room_count > 0) {
            return new WP_Error(
                'has_rooms',
                sprintf(
                    /* translators: %d: number of rooms. */
                    __('Cannot delete this location: reassign or delete its %d room(s) first.', 'sanctuary-hotel-booking'),
                    $room_count
                )
            );
        }

        $bookings = get_posts(array(
            'post_type' => 'shb_booking',
            'post_status' => 'any',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_key' => '_shb_location_id',
            'meta_value' => $location_id,
        ));

        if (!empty($bookings)) {
            return new WP_Error(
                'has_bookings',
                __('Cannot delete this location: it is referenced by existing bookings (historical record).', 'sanctuary-hotel-booking')
            );
        }

        // Rooms and bookings are gone: remove the post and any default pointer.
        $deleted = wp_delete_post($location_id, true);
        if (!$deleted) {
            return new WP_Error('delete_failed', __('Failed to delete the location.', 'sanctuary-hotel-booking'));
        }

        if ((int) get_option('shb_default_location_id', 0) === $location_id) {
            delete_option('shb_default_location_id');
        }

        return true;
    }

    /**
     * Confirm screen shown before deleting an empty location.
     */
    public static function render_delete_confirm($location_id) {
        $location = SHB_Location::get_location($location_id);
        if (!$location) {
            echo '<div class="notice notice-error"><p>' . __('Location not found.', 'sanctuary-hotel-booking') . '</p></div>';
            return;
        }
        ?>
        <div class="wrap shb-admin-wrap">
            <h1><?php _e('Delete Location', 'sanctuary-hotel-booking'); ?></h1>
            <p>
                <?php
                printf(
                    /* translators: %s: location name. */
                    __('Are you sure you want to delete "%s"? This cannot be undone.', 'sanctuary-hotel-booking'),
                    esc_html($location['name'])
                );
                ?>
            </p>
            <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=shb-locations&action=delete&location_id=' . $location_id)); ?>">
                <?php wp_nonce_field('shb_location_delete', 'shb_location_delete_nonce'); ?>
                <input type="hidden" name="shb_delete_location" value="1">
                <p>
                    <button type="submit" class="button button-secondary" onclick="return confirm('<?php echo esc_js(__('Delete this location permanently?', 'sanctuary-hotel-booking')); ?>');">
                        <?php _e('Delete Location', 'sanctuary-hotel-booking'); ?>
                    </button>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=shb-locations')); ?>" class="button button-primary">
                        <?php _e('Cancel', 'sanctuary-hotel-booking'); ?>
                    </a>
                </p>
            </form>
        </div>
        <?php
    }

    public static function render_edit_page($location_id = 0) {
        $location = $location_id ? SHB_Location::get_location($location_id) : null;

        // Handle save.
        if (isset($_POST['shb_save_location'])) {
            if (!wp_verify_nonce($_POST['shb_location_nonce'] ?? '', 'shb_location_save')) {
                wp_die(__('Security check failed.', 'sanctuary-hotel-booking'));
            }

            // Location managers may only save locations assigned to them.
            $target_id = absint($_POST['location_id'] ?? 0);
            if (!current_user_can('manage_options') && !SHB_Location::user_can_manage($target_id)) {
                wp_die(__('You do not have permission to manage this location.', 'sanctuary-hotel-booking'));
            }

            $saved = self::save_location($_POST);
            if (is_wp_error($saved)) {
                echo '<div class="notice notice-error"><p>' . esc_html($saved->get_error_message()) . '</p></div>';
            } else {
                $location = SHB_Location::get_location($saved);
                echo '<div class="notice notice-success"><p>' . __('Location saved.', 'sanctuary-hotel-booking') . '</p></div>';
            }
        }

        // Hub data: the rooms at this location (all statuses so managers can
        // see inactive units too) plus the room-type summary.
        $location_rooms = array();
        $location_type_counts = array();
        if ($location) {
            $location_rooms = SHB_Room::get_rooms(array(
                'location_id' => $location['id'],
                'all' => true,
            ));
            $location_type_counts = SHB_Room::get_room_type_counts_by_location($location['id']);
        }

        include SHB_PLUGIN_DIR . 'admin/views/location-edit.php';
    }

    /**
     * Insert or update a location from form data.
     */
    public static function save_location($data) {
        $name = sanitize_text_field($data['name'] ?? '');
        if (empty($name)) {
            return new WP_Error('missing_name', __('Location name is required.', 'sanctuary-hotel-booking'));
        }

        $location_id = absint($data['location_id'] ?? 0);

        $post_data = array(
            'post_title' => $name,
            'post_content' => wp_kses_post($data['description'] ?? ''),
            'post_type' => 'shb_location',
            'post_status' => 'publish',
        );

        if ($location_id) {
            $post_data['ID'] = $location_id;
        }

        $result = wp_insert_post($post_data, true);
        if (is_wp_error($result)) {
            return $result;
        }

        $meta = array(
            '_shb_address' => sanitize_text_field($data['address'] ?? ''),
            '_shb_city' => sanitize_text_field($data['city'] ?? ''),
            '_shb_country' => sanitize_text_field($data['country'] ?? ''),
            '_shb_phone' => sanitize_text_field($data['phone'] ?? ''),
            '_shb_email' => sanitize_email($data['email'] ?? ''),
            '_shb_timezone' => sanitize_text_field($data['timezone'] ?? wp_timezone_string()),
            '_shb_currency' => sanitize_text_field($data['currency'] ?? 'USD'),
            '_shb_currency_symbol' => sanitize_text_field($data['currency_symbol'] ?? '$'),
            '_shb_check_in_time' => sanitize_text_field($data['check_in_time'] ?? '14:00'),
            '_shb_check_out_time' => sanitize_text_field($data['check_out_time'] ?? '11:00'),
            '_shb_is_active' => isset($data['is_active']) ? '1' : '0',
        );

        foreach ($meta as $key => $value) {
            update_post_meta($result, $key, $value);
        }

        // First location created becomes the default.
        if (!get_option('shb_default_location_id', 0)) {
            update_option('shb_default_location_id', $result);
            update_post_meta($result, '_shb_is_default', '1');
        }

        return $result;
    }
}
