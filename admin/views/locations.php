<div class="wrap shb-admin-wrap">
    <h1>
        <?php _e('Locations', 'sanctuary-hotel-booking'); ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=shb-locations&action=new')); ?>" class="page-title-action">
            <?php _e('Add New', 'sanctuary-hotel-booking'); ?>
        </a>
    </h1>

    <p><?php _e('Manage the properties your rooms belong to. Each location has its own contact details, time zone, currency, and check-in/out times.', 'sanctuary-hotel-booking'); ?></p>

    <?php if (empty($locations)): ?>
        <p><?php _e('No locations yet. Create one to start assigning rooms.', 'sanctuary-hotel-booking'); ?></p>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Name', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Address', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Rooms', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Currency', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Status', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Actions', 'sanctuary-hotel-booking'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($locations as $location): ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=shb-locations&action=edit&location_id=' . $location['id'])); ?>">
                                    <?php echo esc_html($location['name']); ?>
                                </a>
                            </strong>
                            <?php if ((int) get_option('shb_default_location_id', 0) === $location['id']): ?>
                                <span class="shb-badge shb-badge-default"><?php _e('Default', 'sanctuary-hotel-booking'); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($location['permalink'])): ?>
                                <a href="<?php echo esc_url($location['permalink']); ?>" target="_blank" rel="noopener" class="shb-location-page-link" title="<?php _e('View property page', 'sanctuary-hotel-booking'); ?>">
                                    &#8599;
                                </a>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html(trim($location['address'] . ', ' . $location['city'], ', ')); ?></td>
                        <td>
                            <?php
                            $room_types = SHB_Room::get_room_type_counts_by_location($location['id']);
                            $room_total = SHB_Location::get_room_count($location['id']);
                            ?>
                            <?php if ($room_total > 0): ?>
                                <a href="<?php echo esc_url(admin_url('edit.php?post_type=shb_room&shb_location_id=' . $location['id'])); ?>">
                                    <?php echo esc_html($room_total); ?>
                                </a>
                                <?php if (!empty($room_types)): ?>
                                    <div class="shb-room-type-breakdown">
                                        <?php foreach ($room_types as $rt): ?>
                                            <span><?php echo esc_html($rt['count'] . ' ' . $rt['name']); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php echo esc_html($room_total); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($location['currency'] . ' ' . $location['currency_symbol']); ?></td>
                        <td>
                            <?php if ($location['is_active']): ?>
                                <span style="color: green;">&#x25CF;</span> <?php _e('Active', 'sanctuary-hotel-booking'); ?>
                            <?php else: ?>
                                <span style="color: red;">&#x25CF;</span> <?php _e('Inactive', 'sanctuary-hotel-booking'); ?>
                            <?php endif; ?>
                        </td>
                        <td class="shb-location-actions">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=shb-locations&action=edit&location_id=' . $location['id'])); ?>" class="button button-small">
                                <?php _e('Manage', 'sanctuary-hotel-booking'); ?>
                            </a>
                            <a href="<?php echo esc_url(admin_url('edit.php?post_type=shb_room&shb_location_id=' . $location['id'])); ?>" class="button button-small">
                                <?php _e('Rooms', 'sanctuary-hotel-booking'); ?>
                            </a>
                            <?php if ($location['is_active'] && !empty($location['permalink'])): ?>
                                <a href="<?php echo esc_url($location['permalink']); ?>" class="button button-small" target="_blank" rel="noopener">
                                    <?php _e('View Page', 'sanctuary-hotel-booking'); ?>
                                </a>
                            <?php endif; ?>
                            <?php if (current_user_can('manage_options') && SHB_Location::get_room_count($location['id']) === 0): ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=shb-locations&action=delete&location_id=' . $location['id'])); ?>" class="button button-small shb-delete-link" onclick="return confirm('<?php echo esc_js(__('Delete this location permanently?', 'sanctuary-hotel-booking')); ?>');">
                                    <?php _e('Delete', 'sanctuary-hotel-booking'); ?>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
