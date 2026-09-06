<div class="wrap shb-admin-wrap">
    <?php if (!empty($room_type) && $action === 'edit'): ?>
        <h1>
            <?php esc_html_e('Edit Room Type', 'sanctuary-hotel-booking'); ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=shb-room-types')); ?>" class="page-title-action">
                &larr; <?php _e('Back to Room Types', 'sanctuary-hotel-booking'); ?>
            </a>
        </h1>

        <?php
        $meta = $room_type['meta'];
        $type_name = $room_type['name'];
        $type_slug = $room_type['slug'];
        $type_desc = $room_type['description'];
        $type_base_price = $meta['_shb_type_base_price'] ?? '';
        $type_max_guests = $meta['_shb_type_max_guests'] ?? '';
        $type_bed_type = $meta['_shb_type_bed_type'] ?? '';
        $type_room_size = $meta['_shb_type_room_size'] ?? '';
        $type_floor = $meta['_shb_type_floor'] ?? '';
        $type_amenities = is_array($meta['_shb_type_amenities'] ?? null) ? $meta['_shb_type_amenities'] : array();
        $type_min_nights = $meta['_shb_type_min_nights'] ?? '';
        $type_max_nights = $meta['_shb_type_max_nights'] ?? '';
        $type_cancellation_policy = $meta['_shb_type_cancellation_policy'] ?? '';
        ?>

        <form method="post" action="">
            <?php wp_nonce_field('shb_room_type_manage', 'shb_room_type_nonce'); ?>
            <input type="hidden" name="shb_save_room_type" value="1">
            <input type="hidden" name="type_id" value="<?php echo esc_attr($room_type['term_id']); ?>">

            <h2><?php _e('Type Details', 'sanctuary-hotel-booking'); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="type_name"><?php _e('Name', 'sanctuary-hotel-booking'); ?> *</label></th>
                    <td>
                        <input type="text" name="type_name" id="type_name" class="regular-text" required value="<?php echo esc_attr($type_name); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="type_slug"><?php _e('Slug', 'sanctuary-hotel-booking'); ?></label></th>
                    <td>
                        <input type="text" name="type_slug" id="type_slug" class="regular-text" value="<?php echo esc_attr($type_slug); ?>" placeholder="<?php _e('Auto from name when empty', 'sanctuary-hotel-booking'); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="type_description"><?php _e('Description', 'sanctuary-hotel-booking'); ?></label></th>
                    <td>
                        <textarea name="type_description" id="type_description" rows="3" class="large-text"><?php echo esc_textarea($type_desc); ?></textarea>
                    </td>
                </tr>
            </table>

            <h2><?php _e('Room Defaults', 'sanctuary-hotel-booking'); ?></h2>
            <p><?php _e('Defaults prefill new rooms of this type. Each room can still override any field after creation.', 'sanctuary-hotel-booking'); ?></p>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="type_base_price"><?php _e('Default Base Price (per night)', 'sanctuary-hotel-booking'); ?></label></th>
                    <td>
                        <input type="number" name="type_base_price" id="type_base_price"
                               value="<?php echo esc_attr($type_base_price); ?>" step="0.01" min="0" style="width:150px;">
                        <span class="description"><?php echo esc_html(get_option('shb_currency_symbol', '$')); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="type_max_guests"><?php _e('Default Max Guests', 'sanctuary-hotel-booking'); ?></label></th>
                    <td>
                        <select name="type_max_guests" id="type_max_guests">
                            <option value="" <?php selected($type_max_guests, ''); ?>><?php _e('— None —', 'sanctuary-hotel-booking'); ?></option>
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php selected((int) $type_max_guests, $i); ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="type_bed_type"><?php _e('Default Bed Type', 'sanctuary-hotel-booking'); ?></label></th>
                    <td>
                        <select name="type_bed_type" id="type_bed_type">
                            <option value="" <?php selected($type_bed_type, ''); ?>><?php _e('— None —', 'sanctuary-hotel-booking'); ?></option>
                            <option value="single" <?php selected($type_bed_type, 'single'); ?>><?php _e('Single', 'sanctuary-hotel-booking'); ?></option>
                            <option value="double" <?php selected($type_bed_type, 'double'); ?>><?php _e('Double', 'sanctuary-hotel-booking'); ?></option>
                            <option value="queen" <?php selected($type_bed_type, 'queen'); ?>><?php _e('Queen', 'sanctuary-hotel-booking'); ?></option>
                            <option value="king" <?php selected($type_bed_type, 'king'); ?>><?php _e('King', 'sanctuary-hotel-booking'); ?></option>
                            <option value="twin" <?php selected($type_bed_type, 'twin'); ?>><?php _e('Twin Beds', 'sanctuary-hotel-booking'); ?></option>
                            <option value="bunk" <?php selected($type_bed_type, 'bunk'); ?>><?php _e('Bunk Beds', 'sanctuary-hotel-booking'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="type_room_size"><?php _e('Default Room Size (m²)', 'sanctuary-hotel-booking'); ?></label></th>
                    <td>
                        <input type="number" name="type_room_size" id="type_room_size" value="<?php echo esc_attr($type_room_size); ?>" step="1" min="0" style="width:100px;">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="type_floor"><?php _e('Default Floor Number', 'sanctuary-hotel-booking'); ?></label></th>
                    <td>
                        <input type="number" name="type_floor" id="type_floor" value="<?php echo esc_attr($type_floor); ?>" step="1" min="0" style="width:100px;">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label><?php _e('Default Amenities', 'sanctuary-hotel-booking'); ?></label></th>
                    <td>
                        <?php
                        $amenity_groups = array(
                            __('Room Basics', 'sanctuary-hotel-booking') => array('WiFi', 'TV', 'Smart TV', 'Air Conditioning', 'Heating', 'Ceiling Fan'),
                            __('Bathroom', 'sanctuary-hotel-booking') => array('Private Bathroom', 'Bathtub', 'Shower', 'Jacuzzi', 'Hairdryer', 'Toiletries'),
                            __('Kitchen & Dining', 'sanctuary-hotel-booking') => array('Coffee Maker', 'Mini Fridge', 'Mini Bar', 'Full Kitchen', 'Microwave', 'Kettle'),
                            __('Comfort & Work', 'sanctuary-hotel-booking') => array('Workspace', 'Safe', 'Iron', 'Wardrobe', 'Sofa', 'Fireplace'),
                            __('Views & Outdoor', 'sanctuary-hotel-booking') => array('Balcony', 'Terrace', 'Ocean View', 'Mountain View', 'Garden View', 'City View'),
                            __('Services & Facilities', 'sanctuary-hotel-booking') => array('Room Service', 'Breakfast Included', 'Pool Access', 'Gym Access', 'Spa Access', 'Parking'),
                        );
                        $all_known = array();
                        foreach ($amenity_groups as $group) {
                            $all_known = array_merge($all_known, $group);
                        }
                        $custom = array_diff($type_amenities, $all_known);
                        ?>
                        <?php foreach ($amenity_groups as $group_name => $group_amenities): ?>
                            <div class="shb-amenity-group">
                                <strong class="shb-amenity-group-title"><?php echo esc_html($group_name); ?></strong>
                                <div class="shb-amenity-checkboxes">
                                    <?php foreach ($group_amenities as $amenity): ?>
                                        <label class="shb-amenity-label">
                                            <input type="checkbox" name="type_amenities[]" value="<?php echo esc_attr($amenity); ?>" <?php checked(in_array($amenity, $type_amenities)); ?>>
                                            <?php echo esc_html($amenity); ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="shb-amenity-group" style="margin-top:16px;">
                            <strong class="shb-amenity-group-title"><?php _e('Custom Amenities', 'sanctuary-hotel-booking'); ?></strong>
                            <input type="text" name="type_custom_amenities" id="type_custom_amenities"
                                   value="<?php echo esc_attr(implode(', ', $custom)); ?>" class="large-text"
                                   placeholder="<?php _e('e.g. Private Pool, Rooftop Access', 'sanctuary-hotel-booking'); ?>">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="type_min_nights"><?php _e('Default Minimum Nights', 'sanctuary-hotel-booking'); ?></label></th>
                    <td>
                        <input type="number" name="type_min_nights" id="type_min_nights" value="<?php echo esc_attr($type_min_nights); ?>" min="1" step="1" style="width:80px;">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="type_max_nights"><?php _e('Default Maximum Nights', 'sanctuary-hotel-booking'); ?></label></th>
                    <td>
                        <input type="number" name="type_max_nights" id="type_max_nights" value="<?php echo esc_attr($type_max_nights); ?>" min="1" step="1" style="width:80px;">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="type_cancellation_policy"><?php _e('Default Cancellation Policy', 'sanctuary-hotel-booking'); ?></label></th>
                    <td>
                        <select name="type_cancellation_policy" id="type_cancellation_policy">
                            <option value="" <?php selected($type_cancellation_policy, ''); ?>><?php _e('— None —', 'sanctuary-hotel-booking'); ?></option>
                            <option value="flexible" <?php selected($type_cancellation_policy, 'flexible'); ?>><?php _e('Flexible - Free cancellation up to 24h before', 'sanctuary-hotel-booking'); ?></option>
                            <option value="moderate" <?php selected($type_cancellation_policy, 'moderate'); ?>><?php _e('Moderate - Free cancellation up to 5 days before', 'sanctuary-hotel-booking'); ?></option>
                            <option value="strict" <?php selected($type_cancellation_policy, 'strict'); ?>><?php _e('Strict - 50% refund up to 7 days before', 'sanctuary-hotel-booking'); ?></option>
                            <option value="non_refundable" <?php selected($type_cancellation_policy, 'non_refundable'); ?>><?php _e('Non-refundable', 'sanctuary-hotel-booking'); ?></option>
                        </select>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary"><?php _e('Save Room Type', 'sanctuary-hotel-booking'); ?></button>
            </p>
        </form>
    <?php else: ?>
        <h1><?php _e('Room Types', 'sanctuary-hotel-booking'); ?></h1>
        <p><?php _e('Create and manage room types. Each type carries defaults that prefill new rooms.', 'sanctuary-hotel-booking'); ?></p>

        <form method="post" action="" style="margin-bottom:20px;" class="shb-add-type-form">
            <?php wp_nonce_field('shb_room_type_manage', 'shb_room_type_nonce'); ?>
            <input type="hidden" name="shb_add_room_type" value="1">
            <h2 style="margin-top:0;"><?php _e('Add New Type', 'sanctuary-hotel-booking'); ?></h2>
            <p>
                <input type="text" name="type_name" placeholder="<?php esc_attr_e('Type name (e.g. Family Suite)', 'sanctuary-hotel-booking'); ?>" class="regular-text" required>
                <input type="text" name="type_slug" placeholder="<?php esc_attr_e('slug (optional)', 'sanctuary-hotel-booking'); ?>" class="regular-text">
            </p>
            <p>
                <textarea name="type_description" rows="2" class="large-text" placeholder="<?php esc_attr_e('Short description (optional)', 'sanctuary-hotel-booking'); ?>"></textarea>
            </p>
            <button type="submit" class="button button-primary"><?php _e('Add Room Type', 'sanctuary-hotel-booking'); ?></button>
        </form>

        <?php if (empty($room_types)): ?>
            <p><?php _e('No room types yet. Add one above.', 'sanctuary-hotel-booking'); ?></p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Name', 'sanctuary-hotel-booking'); ?></th>
                        <th><?php _e('Slug', 'sanctuary-hotel-booking'); ?></th>
                        <th><?php _e('Rooms', 'sanctuary-hotel-booking'); ?></th>
                        <th><?php _e('Default Price', 'sanctuary-hotel-booking'); ?></th>
                        <th><?php _e('Default Guests', 'sanctuary-hotel-booking'); ?></th>
                        <th><?php _e('Actions', 'sanctuary-hotel-booking'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($room_types as $type): ?>
                        <tr>
                            <td>
                                <strong>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=shb-room-types&action=edit&type_id=' . $type['term_id'])); ?>">
                                        <?php echo esc_html($type['name']); ?>
                                    </a>
                                </strong>
                                <?php if ($type['description']): ?>
                                    <div class="shb-type-desc"><?php echo esc_html($type['description']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td><code><?php echo esc_html($type['slug']); ?></code></td>
                            <td><?php echo esc_html($type['room_count'] ?? 0); ?></td>
                            <td>
                                <?php
                                $price = $type['meta']['_shb_type_base_price'] ?? '';
                                echo $price !== '' ? esc_html(get_option('shb_currency_symbol', '$') . number_format(floatval($price), 2)) : '&mdash;';
                                ?>
                            </td>
                            <td>
                                <?php
                                $guests = $type['meta']['_shb_type_max_guests'] ?? '';
                                echo $guests !== '' ? esc_html($guests) : '&mdash;';
                                ?>
                            </td>
                            <td class="shb-type-actions">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=shb-room-types&action=edit&type_id=' . $type['term_id'])); ?>" class="button button-small">
                                    <?php _e('Edit', 'sanctuary-hotel-booking'); ?>
                                </a>
                                <?php if (empty($type['room_count'])): ?>
                                    <form method="post" action="" style="display:inline-block;margin:0;" onsubmit="return confirm('<?php echo esc_js(__('Delete this room type permanently?', 'sanctuary-hotel-booking')); ?>');">
                                        <?php wp_nonce_field('shb_room_type_manage', 'shb_room_type_nonce'); ?>
                                        <input type="hidden" name="shb_delete_room_type" value="1">
                                        <input type="hidden" name="type_id" value="<?php echo esc_attr($type['term_id']); ?>">
                                        <button type="submit" class="button button-small shb-delete-link"><?php _e('Delete', 'sanctuary-hotel-booking'); ?></button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>
</div>
