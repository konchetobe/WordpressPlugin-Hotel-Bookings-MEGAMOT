<div class="wrap shb-admin-wrap">
    <h1>
        <?php _e('Rooms', 'sanctuary-hotel-booking'); ?>
        <?php if (current_user_can('manage_options') || current_user_can('shb_manage_rooms')): ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=shb-rooms&action=new')); ?>" class="page-title-action">
                <?php _e('Add New Room', 'sanctuary-hotel-booking'); ?>
            </a>
        <?php endif; ?>
    </h1>

    <p><?php _e('Manage the rooms available for booking. Each room belongs to one location and one room type.', 'sanctuary-hotel-booking'); ?></p>

    <?php if (!empty($locations) && count($locations) > 1): ?>
        <form method="get" action="" class="shb-filter-inline" style="margin-bottom:12px;">
            <input type="hidden" name="page" value="shb-rooms">
            <select name="shb_location_id">
                <option value="0"><?php _e('All Locations', 'sanctuary-hotel-booking'); ?></option>
                <?php foreach ($locations as $loc): ?>
                    <option value="<?php echo esc_attr($loc['id']); ?>" <?php selected($location_filter, $loc['id']); ?>>
                        <?php echo esc_html($loc['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="submit" class="button" value="<?php _e('Filter', 'sanctuary-hotel-booking'); ?>">
        </form>
    <?php endif; ?>

    <?php if (empty($rooms)): ?>
        <p><?php _e('No rooms yet. Create one to start taking bookings.', 'sanctuary-hotel-booking'); ?></p>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Room', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Type', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Location', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Price/Night', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Guests', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Status', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Actions', 'sanctuary-hotel-booking'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rooms as $room): ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=shb-rooms&action=edit&room_id=' . $room['id'])); ?>">
                                    <?php echo esc_html($room['name']); ?>
                                </a>
                            </strong>
                            <div class="shb-room-id"><code><?php echo esc_html($room['id']); ?></code></div>
                        </td>
                        <td><?php echo esc_html(ucfirst(str_replace('-', ' ', $room['room_type']))); ?></td>
                        <td>
                            <?php if ($room['location']): ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=shb-locations&action=edit&location_id=' . $room['location']['id'])); ?>">
                                    <?php echo esc_html($room['location']['name']); ?>
                                </a>
                            <?php else: ?>
                                <em><?php _e('None', 'sanctuary-hotel-booking'); ?></em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $symbol = !empty($room['location']['currency_symbol']) ? $room['location']['currency_symbol'] : get_option('shb_currency_symbol', '$');
                            echo esc_html($symbol . number_format($room['base_price'], 2));
                            ?>
                        </td>
                        <td><?php echo esc_html($room['max_guests']); ?></td>
                        <td>
                            <?php if ($room['is_active']): ?>
                                <span style="color: green;">&#x25CF;</span> <?php _e('Active', 'sanctuary-hotel-booking'); ?>
                            <?php else: ?>
                                <span style="color: red;">&#x25CF;</span> <?php _e('Inactive', 'sanctuary-hotel-booking'); ?>
                            <?php endif; ?>
                        </td>
                        <td class="shb-room-actions">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=shb-rooms&action=edit&room_id=' . $room['id'])); ?>" class="button button-small">
                                <?php _e('Edit', 'sanctuary-hotel-booking'); ?>
                            </a>
                            <?php if (!empty($room['permalink'])): ?>
                                <a href="<?php echo esc_url($room['permalink']); ?>" class="button button-small" target="_blank" rel="noopener">
                                    <?php _e('View Page', 'sanctuary-hotel-booking'); ?>
                                </a>
                            <?php endif; ?>
                            <?php if (current_user_can('manage_options')): ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=shb-rooms&action=delete&room_id=' . $room['id'])); ?>" class="button button-small shb-delete-link" onclick="return confirm('<?php echo esc_js(__('Delete this room permanently?', 'sanctuary-hotel-booking')); ?>');">
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
