<div class="wrap shb-admin-wrap">
    <h1>
        <?php echo $location ? __('Edit Location', 'sanctuary-hotel-booking') : __('Add Location', 'sanctuary-hotel-booking'); ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=shb-locations')); ?>" class="page-title-action">
            &larr; <?php _e('Back to Locations', 'sanctuary-hotel-booking'); ?>
        </a>
    </h1>

    <form method="post" action="">
        <?php wp_nonce_field('shb_location_save', 'shb_location_nonce'); ?>
        <input type="hidden" name="shb_save_location" value="1">
        <input type="hidden" name="location_id" value="<?php echo esc_attr($location['id'] ?? 0); ?>">

        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="location_name"><?php _e('Name', 'sanctuary-hotel-booking'); ?> *</label>
                </th>
                <td>
                    <input type="text" name="name" id="location_name" class="regular-text" required
                           value="<?php echo esc_attr($location['name'] ?? ''); ?>">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="location_description"><?php _e('Description', 'sanctuary-hotel-booking'); ?></label>
                </th>
                <td>
                    <textarea name="description" id="location_description" rows="3" class="large-text"><?php echo esc_textarea($location['description'] ?? ''); ?></textarea>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Address', 'sanctuary-hotel-booking'); ?></th>
                <td>
                    <p>
                        <input type="text" name="address" class="large-text" placeholder="<?php esc_attr_e('Street address', 'sanctuary-hotel-booking'); ?>"
                               value="<?php echo esc_attr($location['address'] ?? ''); ?>">
                    </p>
                    <p>
                        <input type="text" name="city" class="regular-text" placeholder="<?php esc_attr_e('City', 'sanctuary-hotel-booking'); ?>"
                               value="<?php echo esc_attr($location['city'] ?? ''); ?>">
                        <input type="text" name="country" class="regular-text" placeholder="<?php esc_attr_e('Country', 'sanctuary-hotel-booking'); ?>"
                               value="<?php echo esc_attr($location['country'] ?? ''); ?>">
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Contact', 'sanctuary-hotel-booking'); ?></th>
                <td>
                    <p>
                        <input type="tel" name="phone" class="regular-text" placeholder="<?php esc_attr_e('Phone', 'sanctuary-hotel-booking'); ?>"
                               value="<?php echo esc_attr($location['phone'] ?? ''); ?>">
                    </p>
                    <p>
                        <input type="email" name="email" class="regular-text" placeholder="<?php esc_attr_e('Email', 'sanctuary-hotel-booking'); ?>"
                               value="<?php echo esc_attr($location['email'] ?? ''); ?>">
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="location_timezone"><?php _e('Time Zone', 'sanctuary-hotel-booking'); ?></label>
                </th>
                <td>
                    <select name="timezone" id="location_timezone" class="regular-text">
                        <?php echo wp_timezone_choice($location['timezone'] ?? wp_timezone_string()); ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Currency', 'sanctuary-hotel-booking'); ?></th>
                <td>
                    <p>
                        <input type="text" name="currency" class="small-text" placeholder="USD"
                               value="<?php echo esc_attr($location['currency'] ?? 'USD'); ?>">
                        <input type="text" name="currency_symbol" class="small-text" placeholder="$"
                               value="<?php echo esc_attr($location['currency_symbol'] ?? '$'); ?>">
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Check-in / Check-out', 'sanctuary-hotel-booking'); ?></th>
                <td>
                    <p>
                        <label for="location_check_in_time"><?php _e('Check-in', 'sanctuary-hotel-booking'); ?></label>
                        <input type="time" name="check_in_time" id="location_check_in_time"
                               value="<?php echo esc_attr($location['check_in_time'] ?? '14:00'); ?>">
                        <label for="location_check_out_time"><?php _e('Check-out', 'sanctuary-hotel-booking'); ?></label>
                        <input type="time" name="check_out_time" id="location_check_out_time"
                               value="<?php echo esc_attr($location['check_out_time'] ?? '11:00'); ?>">
                    </p>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Status', 'sanctuary-hotel-booking'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="is_active" value="1" <?php checked(($location['is_active'] ?? true), true); ?>>
                        <?php _e('Active (rooms here are available for booking)', 'sanctuary-hotel-booking'); ?>
                    </label>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" class="button button-primary"><?php _e('Save Location', 'sanctuary-hotel-booking'); ?></button>
        </p>
    </form>

    <?php if ($location): ?>
        <hr style="margin: 24px 0;">

        <div class="shb-location-hub">
            <h2><?php _e('Rooms at this Location', 'sanctuary-hotel-booking'); ?></h2>
            <p>
                <?php _e('This property has', 'sanctuary-hotel-booking'); ?>
                <strong><?php echo esc_html(count($location_rooms)); ?></strong>
                <?php _e('room(s) in total.', 'sanctuary-hotel-booking'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=shb-rooms&action=new&location_id=' . $location['id'])); ?>" class="button button-small">
                    <?php _e('Add Room here', 'sanctuary-hotel-booking'); ?>
                </a>
            </p>

            <?php if (!empty($location_type_counts)): ?>
                <div class="shb-hub-summary">
                    <?php foreach ($location_type_counts as $rt): ?>
                        <span class="shb-hub-chip">
                            <?php echo esc_html($rt['count'] . ' ' . $rt['name']); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($location_rooms)): ?>
                <p><?php _e('No rooms assigned to this location yet.', 'sanctuary-hotel-booking'); ?></p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Room', 'sanctuary-hotel-booking'); ?></th>
                            <th><?php _e('Type', 'sanctuary-hotel-booking'); ?></th>
                            <th><?php _e('Price/Night', 'sanctuary-hotel-booking'); ?></th>
                            <th><?php _e('Max Guests', 'sanctuary-hotel-booking'); ?></th>
                            <th><?php _e('Status', 'sanctuary-hotel-booking'); ?></th>
                            <th><?php _e('Actions', 'sanctuary-hotel-booking'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($location_rooms as $room): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=shb-rooms&action=edit&room_id=' . $room['id'])); ?>">
                                        <?php echo esc_html($room['name']); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html(ucfirst($room['room_type'])); ?></td>
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
                                <td>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=shb-rooms&action=edit&room_id=' . $room['id'])); ?>" class="button button-small">
                                        <?php _e('Edit Room', 'sanctuary-hotel-booking'); ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if (current_user_can('manage_options')): ?>
                <h2 style="margin-top:32px;"><?php _e('Danger Zone', 'sanctuary-hotel-booking'); ?></h2>
                <?php $room_count = SHB_Location::get_room_count($location['id']); ?>
                <?php if ($room_count > 0): ?>
                    <p class="description">
                        <?php _e('This location still has rooms assigned. Reassign or delete them before this location can be deleted.', 'sanctuary-hotel-booking'); ?>
                    </p>
                <?php else: ?>
                    <p class="description">
                        <?php _e('Delete this location permanently. Locations referenced by bookings cannot be deleted.', 'sanctuary-hotel-booking'); ?>
                    </p>
                    <p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=shb-locations&action=delete&location_id=' . $location['id'])); ?>" class="button button-secondary" style="color:#b32d2e;border-color:#b32d2e;">
                            <?php _e('Delete Location', 'sanctuary-hotel-booking'); ?>
                        </a>
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
