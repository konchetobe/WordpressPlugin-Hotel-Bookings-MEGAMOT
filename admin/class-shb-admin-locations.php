<?php
/**
 * Admin Locations Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Admin_Locations {

    public static function render_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have permission to manage locations.', 'sanctuary-hotel-booking'));
        }

        $action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : 'list';
        $location_id = isset($_GET['location_id']) ? absint($_GET['location_id']) : 0;

        if ($action === 'edit' || $action === 'new') {
            self::render_edit_page($location_id);
            return;
        }

        $locations = SHB_Location::get_locations();
        include SHB_PLUGIN_DIR . 'admin/views/locations.php';
    }

    public static function render_edit_page($location_id = 0) {
        $location = $location_id ? SHB_Location::get_location($location_id) : null;

        // Handle save.
        if (isset($_POST['shb_save_location'])) {
            if (!wp_verify_nonce($_POST['shb_location_nonce'] ?? '', 'shb_location_save')) {
                wp_die(__('Security check failed.', 'sanctuary-hotel-booking'));
            }

            $saved = self::save_location($_POST);
            if (is_wp_error($saved)) {
                echo '<div class="notice notice-error"><p>' . esc_html($saved->get_error_message()) . '</p></div>';
            } else {
                $location = SHB_Location::get_location($saved);
                echo '<div class="notice notice-success"><p>' . __('Location saved.', 'sanctuary-hotel-booking') . '</p></div>';
            }
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
