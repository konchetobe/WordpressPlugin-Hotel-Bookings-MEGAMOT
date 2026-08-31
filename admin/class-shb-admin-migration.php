<?php
/**
 * Admin Migration Report Handler
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Admin_Migration {

    public static function render_page() {
        $report = SHB_Migration::get_report();
        $has_run = SHB_Migration::has_run();

        include SHB_PLUGIN_DIR . 'admin/views/migration.php';
    }
}
