<div class="wrap shb-admin-wrap">
    <h1><?php _e('Migration Report', 'sanctuary-hotel-booking'); ?></h1>

    <p>
        <?php _e('The multi-location migration attaches existing rooms to a Default location, migrates room types to taxonomy terms, snapshots location data onto historical bookings, and backfills the nightly allocation table.', 'sanctuary-hotel-booking'); ?>
    </p>

    <?php if (!$has_run): ?>
        <div class="notice notice-warning">
            <p>
                <?php _e('The multi-location migration has not run yet. It runs automatically on the next plugin load after the update.', 'sanctuary-hotel-booking'); ?>
            </p>
        </div>
    <?php elseif (empty($report)): ?>
        <div class="notice notice-info">
            <p><?php _e('No migration report is available.', 'sanctuary-hotel-booking'); ?></p>
        </div>
    <?php else: ?>

        <?php if (!empty($report['error'])): ?>
            <div class="notice notice-error">
                <p><?php echo esc_html($report['error']); ?></p>
            </div>
        <?php endif; ?>

        <h2><?php _e('Summary', 'sanctuary-hotel-booking'); ?></h2>
        <table class="wp-list-table widefat fixed striped" style="max-width: 600px;">
            <tbody>
                <tr>
                    <th><?php _e('Migration version', 'sanctuary-hotel-booking'); ?></th>
                    <td><?php echo esc_html($report['version'] ?? ''); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Run at', 'sanctuary-hotel-booking'); ?></th>
                    <td><?php echo esc_html($report['run_at'] ?? ''); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Total rooms', 'sanctuary-hotel-booking'); ?></th>
                    <td><?php echo esc_html($report['total_rooms'] ?? 0); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Rooms attached to Default location', 'sanctuary-hotel-booking'); ?></th>
                    <td><?php echo esc_html($report['rooms_attached'] ?? 0); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Room types migrated to taxonomy terms', 'sanctuary-hotel-booking'); ?></th>
                    <td><?php echo esc_html($report['room_types_migrated'] ?? 0); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Bookings snapshotted', 'sanctuary-hotel-booking'); ?></th>
                    <td><?php echo esc_html($report['bookings_snapshotted'] ?? 0); ?></td>
                </tr>
                <tr>
                    <th><?php _e('Nights backfilled', 'sanctuary-hotel-booking'); ?></th>
                    <td><?php echo esc_html($report['nights_backfilled'] ?? 0); ?></td>
                </tr>
            </tbody>
        </table>

        <?php $orphaned = $report['orphaned_rooms'] ?? array(); ?>
        <h2><?php _e('Orphaned rooms', 'sanctuary-hotel-booking'); ?>
            <span class="count">(<?php echo count($orphaned); ?>)</span></h2>
        <?php if (empty($orphaned)): ?>
            <p><?php _e('None — every room already had a valid location.', 'sanctuary-hotel-booking'); ?></p>
        <?php else: ?>
            <p><?php _e('These rooms had no location and were attached to the Default location:', 'sanctuary-hotel-booking'); ?></p>
            <ul>
                <?php foreach ($orphaned as $room_id): ?>
                    <li>
                        <a href="<?php echo esc_url(get_edit_post_link($room_id)); ?>">
                            <?php echo esc_html(get_the_title($room_id) . ' (#' . $room_id . ')'); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php $invalid = $report['invalid_date_bookings'] ?? array(); ?>
        <h2><?php _e('Bookings with invalid dates', 'sanctuary-hotel-booking'); ?>
            <span class="count">(<?php echo count($invalid); ?>)</span></h2>
        <?php if (empty($invalid)): ?>
            <p><?php _e('None.', 'sanctuary-hotel-booking'); ?></p>
        <?php else: ?>
            <p><?php _e('These bookings have unparseable or reversed dates and were not backfilled:', 'sanctuary-hotel-booking'); ?></p>
            <ul>
                <?php foreach ($invalid as $item): ?>
                    <li>
                        <a href="<?php echo esc_url(get_edit_post_link($item['booking_id'])); ?>">
                            <?php echo esc_html('#' . $item['booking_id']); ?>
                        </a>
                        (<?php echo esc_html($item['check_in'] . ' → ' . $item['check_out']); ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php $overlaps = $report['overlapping_bookings'] ?? array(); ?>
        <h2><?php _e('Overlapping legacy bookings', 'sanctuary-hotel-booking'); ?>
            <span class="count">(<?php echo count($overlaps); ?>)</span></h2>
        <?php if (empty($overlaps)): ?>
            <p><?php _e('None — no existing bookings share a room and night.', 'sanctuary-hotel-booking'); ?></p>
        <?php else: ?>
            <p><?php _e('These existing bookings overlap on a room/night. The first booking kept the nightly row; the later one was flagged for manual review:', 'sanctuary-hotel-booking'); ?></p>
            <ul>
                <?php foreach ($overlaps as $item): ?>
                    <li>
                        <?php
                        $room = SHB_Room::get_room($item['room_id']);
                        echo esc_html(($room ? $room['name'] : '#' . $item['room_id']) . ' — ' . $item['stay_date'] . ' — booking #' . $item['booking_id']);
                        ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <p class="description">
            <?php _e('This report is read-only. Nothing was deleted or automatically resolved.', 'sanctuary-hotel-booking'); ?>
        </p>

    <?php endif; ?>
</div>
