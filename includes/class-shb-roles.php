<?php
/**
 * Roles and Capabilities
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Roles {

    /** Capabilities introduced by the plugin. */
    public static function get_capabilities() {
        return array(
            'shb_manage_locations',
            'shb_manage_rooms',
            'shb_manage_bookings',
            'shb_view_reports',
        );
    }

    /**
     * Register the location manager role and grant admins the plugin caps.
     * Called on activation.
     */
    public static function register() {
        $role = get_role('shb_location_manager');
        if (!$role) {
            add_role(
                'shb_location_manager',
                __('Location Manager', 'sanctuary-hotel-booking'),
                array(
                    'read' => true,
                    'shb_manage_locations' => true,
                    'shb_manage_rooms' => true,
                    'shb_manage_bookings' => true,
                    'shb_view_reports' => true,
                )
            );
        } else {
            // Ensure caps exist after upgrades for existing role installations.
            foreach (array('shb_manage_locations', 'shb_manage_rooms', 'shb_manage_bookings', 'shb_view_reports') as $cap) {
                $role->add_cap($cap);
            }
        }

        $admin = get_role('administrator');
        if ($admin) {
            foreach (self::get_capabilities() as $cap) {
                $admin->add_cap($cap);
            }
        }
    }

    /**
     * Remove plugin caps (rollback). Called on deactivation.
     */
    public static function unregister() {
        $admin = get_role('administrator');
        if ($admin) {
            foreach (self::get_capabilities() as $cap) {
                $admin->remove_cap($cap);
            }
        }

        $role = get_role('shb_location_manager');
        if ($role) {
            remove_role('shb_location_manager');
        }
    }

    /**
     * Whether a user can manage bookings/rooms for the given location.
     */
    public static function user_can_manage_location($user_id, $location_id) {
        if (user_can($user_id, 'manage_options')) {
            return true;
        }

        if (!user_can($user_id, 'shb_manage_rooms') && !user_can($user_id, 'shb_manage_bookings')) {
            return false;
        }

        $assigned = get_user_meta($user_id, 'shb_assigned_location_ids', true);
        if (!is_array($assigned)) {
            return false;
        }

        return in_array(absint($location_id), $assigned, true);
    }
}
