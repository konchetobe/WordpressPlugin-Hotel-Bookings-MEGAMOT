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
</div>
