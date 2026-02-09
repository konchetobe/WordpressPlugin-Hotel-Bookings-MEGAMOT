<div class="wrap shb-admin-wrap">
    <h1><?php _e('Hotel Booking Dashboard', 'sanctuary-hotel-booking'); ?></h1>
    
    <!-- Stats Cards -->
    <div class="shb-stats-grid">
        <div class="shb-stat-card">
            <div class="shb-stat-icon shb-stat-rooms">
                <span class="dashicons dashicons-admin-home"></span>
            </div>
            <div class="shb-stat-content">
                <span class="shb-stat-value"><?php echo esc_html($stats['total_rooms']); ?></span>
                <span class="shb-stat-label"><?php _e('Total Rooms', 'sanctuary-hotel-booking'); ?></span>
            </div>
        </div>
        
        <div class="shb-stat-card">
            <div class="shb-stat-icon shb-stat-bookings">
                <span class="dashicons dashicons-calendar-alt"></span>
            </div>
            <div class="shb-stat-content">
                <span class="shb-stat-value"><?php echo esc_html($stats['total_bookings']); ?></span>
                <span class="shb-stat-label"><?php _e('Total Bookings', 'sanctuary-hotel-booking'); ?></span>
            </div>
        </div>
        
        <div class="shb-stat-card">
            <div class="shb-stat-icon shb-stat-revenue">
                <span class="dashicons dashicons-chart-area"></span>
            </div>
            <div class="shb-stat-content">
                <span class="shb-stat-value"><?php echo esc_html(get_option('shb_currency_symbol', '$') . number_format($stats['total_revenue'], 2)); ?></span>
                <span class="shb-stat-label"><?php _e('Total Revenue', 'sanctuary-hotel-booking'); ?></span>
            </div>
        </div>
        
        <div class="shb-stat-card">
            <div class="shb-stat-icon shb-stat-pending">
                <span class="dashicons dashicons-clock"></span>
            </div>
            <div class="shb-stat-content">
                <span class="shb-stat-value"><?php echo esc_html($stats['pending_bookings']); ?></span>
                <span class="shb-stat-label"><?php _e('Pending Bookings', 'sanctuary-hotel-booking'); ?></span>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="shb-quick-actions">
        <h2><?php _e('Quick Actions', 'sanctuary-hotel-booking'); ?></h2>
        <div class="shb-action-buttons">
            <a href="<?php echo admin_url('post-new.php?post_type=shb_room'); ?>" class="button button-primary">
                <span class="dashicons dashicons-plus-alt"></span>
                <?php _e('Add New Room', 'sanctuary-hotel-booking'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=shb-bookings'); ?>" class="button">
                <span class="dashicons dashicons-list-view"></span>
                <?php _e('View Bookings', 'sanctuary-hotel-booking'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=shb-settings'); ?>" class="button">
                <span class="dashicons dashicons-admin-settings"></span>
                <?php _e('Settings', 'sanctuary-hotel-booking'); ?>
            </a>
        </div>
    </div>
    
    <!-- Recent Bookings -->
    <div class="shb-recent-bookings">
        <h2><?php _e('Recent Bookings', 'sanctuary-hotel-booking'); ?></h2>
        <?php if (empty($bookings)) : ?>
            <p><?php _e('No bookings yet.', 'sanctuary-hotel-booking'); ?></p>
        <?php else : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Reference', 'sanctuary-hotel-booking'); ?></th>
                        <th><?php _e('Guest', 'sanctuary-hotel-booking'); ?></th>
                        <th><?php _e('Room', 'sanctuary-hotel-booking'); ?></th>
                        <th><?php _e('Dates', 'sanctuary-hotel-booking'); ?></th>
                        <th><?php _e('Total', 'sanctuary-hotel-booking'); ?></th>
                        <th><?php _e('Status', 'sanctuary-hotel-booking'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking) : ?>
                        <tr>
                            <td><strong><?php echo esc_html($booking['booking_ref']); ?></strong></td>
                            <td><?php echo esc_html($booking['first_name'] . ' ' . $booking['last_name']); ?></td>
                            <td><?php echo esc_html($booking['room_name']); ?></td>
                            <td><?php echo esc_html($booking['check_in'] . ' - ' . $booking['check_out']); ?></td>
                            <td><?php echo esc_html(get_option('shb_currency_symbol', '$') . number_format($booking['total_price'], 2)); ?></td>
                            <td>
                                <span class="shb-status shb-status-<?php echo esc_attr($booking['booking_status']); ?>">
                                    <?php echo esc_html(ucfirst($booking['booking_status'])); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
