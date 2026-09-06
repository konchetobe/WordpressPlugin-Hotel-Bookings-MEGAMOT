<div class="wrap shb-admin-wrap">
    <h1>
        <?php echo $room_id ? __('Edit Room', 'sanctuary-hotel-booking') : __('Add New Room', 'sanctuary-hotel-booking'); ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=shb-rooms')); ?>" class="page-title-action">
            &larr; <?php _e('Back to Rooms', 'sanctuary-hotel-booking'); ?>
        </a>
    </h1>

    <?php
    $r = $room ? $room : array();
    $name = $r['name'] ?? '';
    $description = $r['description'] ?? '';
    $room_type = $r['room_type'] ?? '';
    $location_id = $r['location_id'] ?? 0;
    $base_price = $r['base_price'] ?? '';
    $max_guests = $r['max_guests'] ?? '';
    $amenities = !empty($r['amenities']) ? $r['amenities'] : array();
    $is_active = isset($r['is_active']) ? ($r['is_active'] ? '1' : '0') : '1';
    $bed_type = $r['bed_type'] ?? 'queen';
    $room_size = $r['room_size'] ?? '';
    $floor = $r['floor'] ?? '';
    $min_nights = $r['min_nights'] ?? '1';
    $max_nights = $r['max_nights'] ?? '30';
    $cancel_policy = $r['cancellation_policy'] ?? 'flexible';
    $room_notes = get_post_meta($room_id, '_shb_room_notes', true) ?: '';
    $thumbnail_id = $room_id ? get_post_thumbnail_id($room_id) : 0;
    $gallery_ids = $room_id ? (array) get_post_meta($room_id, '_shb_gallery', true) : array();
    $slug = $room_id ? get_post_field('post_name', $room_id) : '';
    ?>

    <form method="post" action="" id="shb-room-setup-form">
        <?php wp_nonce_field('shb_room_setup_save', 'shb_room_setup_nonce'); ?>
        <input type="hidden" name="shb_save_room" value="1">
        <input type="hidden" name="room_id" id="post_ID" value="<?php echo esc_attr($room_id); ?>">

        <div class="shb-room-meta-box">
            <div class="shb-meta-tabs">
                <button type="button" class="shb-meta-tab active" data-tab="general"><?php _e('General', 'sanctuary-hotel-booking'); ?></button>
                <button type="button" class="shb-meta-tab" data-tab="details"><?php _e('Details & Amenities', 'sanctuary-hotel-booking'); ?></button>
                <button type="button" class="shb-meta-tab" data-tab="policies"><?php _e('Policies', 'sanctuary-hotel-booking'); ?></button>
            </div>

            <!-- General Tab -->
            <div class="shb-meta-panel active" data-panel="general">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="shb_room_name"><?php _e('Room Name', 'sanctuary-hotel-booking'); ?> *</label></th>
                        <td>
                            <input type="text" name="name" id="shb_room_name" class="large-text" required
                                   value="<?php echo esc_attr($name); ?>" placeholder="<?php _e('e.g. Ocean View King Room', 'sanctuary-hotel-booking'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="shb_room_slug"><?php _e('Slug', 'sanctuary-hotel-booking'); ?></label></th>
                        <td>
                            <input type="text" name="slug" id="shb_room_slug" class="regular-text"
                                   value="<?php echo esc_attr($slug); ?>" placeholder="<?php _e('Auto from name when empty', 'sanctuary-hotel-booking'); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="shb_room_description"><?php _e('Description', 'sanctuary-hotel-booking'); ?></label></th>
                        <td>
                            <textarea name="description" id="shb_room_description" rows="6" class="large-text"
                                      placeholder="<?php _e('Describe the room — shown on its public page.', 'sanctuary-hotel-booking'); ?>"><?php echo esc_textarea($description); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Photos', 'sanctuary-hotel-booking'); ?></th>
                        <td>
                            <div class="shb-media-section">
                                <div class="shb-media-field">
                                    <strong><?php _e('Featured Image', 'sanctuary-hotel-booking'); ?></strong>
                                    <div class="shb-thumb-preview" id="shb-thumbnail-preview">
                                        <?php if ($thumbnail_id): ?>
                                            <?php echo wp_get_attachment_image($thumbnail_id, 'medium'); ?>
                                        <?php endif; ?>
                                    </div>
                                    <input type="hidden" name="shb_thumbnail_id" id="shb_thumbnail_id" value="<?php echo esc_attr($thumbnail_id); ?>">
                                    <p>
                                        <button type="button" class="button shb-media-upload" data-target="shb_thumbnail_id" data-preview="shb-thumbnail-preview" data-multiple="0">
                                            <?php _e('Select Image', 'sanctuary-hotel-booking'); ?>
                                        </button>
                                        <button type="button" class="button shb-media-remove" data-target="shb_thumbnail_id" data-preview="shb-thumbnail-preview">
                                            <?php _e('Remove', 'sanctuary-hotel-booking'); ?>
                                        </button>
                                    </p>
                                </div>
                                <div class="shb-media-field">
                                    <strong><?php _e('Gallery', 'sanctuary-hotel-booking'); ?></strong>
                                    <div class="shb-gallery-preview" id="shb-gallery-preview">
                                        <?php foreach ($gallery_ids as $gid): ?>
                                            <?php echo wp_get_attachment_image($gid, 'thumbnail'); ?>
                                        <?php endforeach; ?>
                                    </div>
                                    <input type="hidden" name="shb_gallery_ids" id="shb_gallery_ids"
                                           value="<?php echo esc_attr(implode(',', array_filter(array_map('absint', $gallery_ids)))); ?>">
                                    <p class="description"><?php _e('Additional photos shown in the room gallery.', 'sanctuary-hotel-booking'); ?></p>
                                    <p>
                                        <button type="button" class="button shb-media-upload" data-target="shb_gallery_ids" data-preview="shb-gallery-preview" data-multiple="1">
                                            <?php _e('Select Gallery Images', 'sanctuary-hotel-booking'); ?>
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="shb_location_id"><?php _e('Location', 'sanctuary-hotel-booking'); ?> *</label>
                        </th>
                        <td>
                            <select name="shb_location_id" id="shb_location_id" class="regular-text" required>
                                <option value=""><?php _e('Select Location', 'sanctuary-hotel-booking'); ?></option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?php echo esc_attr($loc['id']); ?>" <?php selected($location_id, $loc['id']); ?>>
                                        <?php echo esc_html($loc['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($locations)): ?>
                                <p class="description">
                                    <?php _e('No locations exist yet.', 'sanctuary-hotel-booking'); ?>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=shb-locations&action=new')); ?>">
                                        <?php _e('Create one first', 'sanctuary-hotel-booking'); ?>
                                    </a>.
                                </p>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="shb_room_type"><?php _e('Room Type', 'sanctuary-hotel-booking'); ?></label></th>
                        <td>
                            <select name="shb_room_type" id="shb_room_type" class="regular-text">
                                <?php
                                $type_slugs = wp_list_pluck($room_types, 'slug');
                                $has_current = in_array($room_type, $type_slugs, true);
                                ?>
                                <?php if (!$has_current && $room_type): ?>
                                    <option value="<?php echo esc_attr($room_type); ?>" selected>
                                        <?php echo esc_html(ucfirst($room_type)); ?> (<?php _e('legacy', 'sanctuary-hotel-booking'); ?>)
                                    </option>
                                <?php endif; ?>
                                <option value="" <?php selected($room_type, ''); ?>><?php _e('— Select type —', 'sanctuary-hotel-booking'); ?></option>
                                <?php foreach ($room_types as $type): ?>
                                    <option value="<?php echo esc_attr($type['slug']); ?>" <?php selected($room_type, $type['slug']); ?>>
                                        <?php echo esc_html($type['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description" style="margin-top:6px;">
                                <label>
                                    <input type="checkbox" id="shb_apply_type_defaults" checked>
                                    <?php _e('Apply room type defaults to empty fields when the type changes', 'sanctuary-hotel-booking'); ?>
                                </label>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="shb_base_price"><?php _e('Base Price (per night)', 'sanctuary-hotel-booking'); ?></label></th>
                        <td>
                            <input type="number" name="shb_base_price" id="shb_base_price"
                                   value="<?php echo esc_attr($base_price); ?>" step="0.01" min="0" style="width:150px;">
                            <span class="description"><?php echo esc_html(get_option('shb_currency_symbol', '$')); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="shb_max_guests"><?php _e('Maximum Guests', 'sanctuary-hotel-booking'); ?></label></th>
                        <td>
                            <select name="shb_max_guests" id="shb_max_guests">
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php selected($max_guests, $i); ?>><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="shb_bed_type"><?php _e('Bed Type', 'sanctuary-hotel-booking'); ?></label></th>
                        <td>
                            <select name="shb_bed_type" id="shb_bed_type">
                                <option value="single" <?php selected($bed_type, 'single'); ?>><?php _e('Single', 'sanctuary-hotel-booking'); ?></option>
                                <option value="double" <?php selected($bed_type, 'double'); ?>><?php _e('Double', 'sanctuary-hotel-booking'); ?></option>
                                <option value="queen" <?php selected($bed_type, 'queen'); ?>><?php _e('Queen', 'sanctuary-hotel-booking'); ?></option>
                                <option value="king" <?php selected($bed_type, 'king'); ?>><?php _e('King', 'sanctuary-hotel-booking'); ?></option>
                                <option value="twin" <?php selected($bed_type, 'twin'); ?>><?php _e('Twin Beds', 'sanctuary-hotel-booking'); ?></option>
                                <option value="bunk" <?php selected($bed_type, 'bunk'); ?>><?php _e('Bunk Beds', 'sanctuary-hotel-booking'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="shb_room_size"><?php _e('Room Size (m²)', 'sanctuary-hotel-booking'); ?></label></th>
                        <td>
                            <input type="number" name="shb_room_size" id="shb_room_size" value="<?php echo esc_attr($room_size); ?>" step="1" min="0" style="width:100px;">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="shb_floor"><?php _e('Floor Number', 'sanctuary-hotel-booking'); ?></label></th>
                        <td>
                            <input type="number" name="shb_floor" id="shb_floor" value="<?php echo esc_attr($floor); ?>" step="1" min="0" style="width:80px;">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="shb_is_active"><?php _e('Status', 'sanctuary-hotel-booking'); ?></label></th>
                        <td>
                            <label>
                                <input type="checkbox" name="shb_is_active" id="shb_is_active" value="1" <?php checked($is_active, '1'); ?>>
                                <?php _e('Active (available for booking)', 'sanctuary-hotel-booking'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Details & Amenities Tab -->
            <div class="shb-meta-panel" data-panel="details">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label><?php _e('Amenities', 'sanctuary-hotel-booking'); ?></label></th>
                        <td>
                            <?php
                            $amenity_groups = array(
                                __('Room Basics', 'sanctuary-hotel-booking') => array(
                                    'WiFi', 'TV', 'Smart TV', 'Air Conditioning', 'Heating', 'Ceiling Fan',
                                ),
                                __('Bathroom', 'sanctuary-hotel-booking') => array(
                                    'Private Bathroom', 'Bathtub', 'Shower', 'Jacuzzi', 'Hairdryer', 'Toiletries',
                                ),
                                __('Kitchen & Dining', 'sanctuary-hotel-booking') => array(
                                    'Coffee Maker', 'Mini Fridge', 'Mini Bar', 'Full Kitchen', 'Microwave', 'Kettle',
                                ),
                                __('Comfort & Work', 'sanctuary-hotel-booking') => array(
                                    'Workspace', 'Safe', 'Iron', 'Wardrobe', 'Sofa', 'Fireplace',
                                ),
                                __('Views & Outdoor', 'sanctuary-hotel-booking') => array(
                                    'Balcony', 'Terrace', 'Ocean View', 'Mountain View', 'Garden View', 'City View',
                                ),
                                __('Services & Facilities', 'sanctuary-hotel-booking') => array(
                                    'Room Service', 'Breakfast Included', 'Pool Access', 'Gym Access', 'Spa Access', 'Parking',
                                ),
                            );
                            ?>
                            <?php foreach ($amenity_groups as $group_name => $group_amenities): ?>
                                <div class="shb-amenity-group">
                                    <strong class="shb-amenity-group-title"><?php echo esc_html($group_name); ?></strong>
                                    <div class="shb-amenity-checkboxes">
                                        <?php foreach ($group_amenities as $amenity): ?>
                                            <label class="shb-amenity-label">
                                                <input type="checkbox" name="shb_amenities[]" value="<?php echo esc_attr($amenity); ?>"
                                                       <?php checked(in_array($amenity, $amenities)); ?>>
                                                <?php echo esc_html($amenity); ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <div class="shb-amenity-group" style="margin-top:16px;">
                                <strong class="shb-amenity-group-title"><?php _e('Custom Amenities', 'sanctuary-hotel-booking'); ?></strong>
                                <p class="description"><?php _e('Add additional amenities (comma-separated)', 'sanctuary-hotel-booking'); ?></p>
                                <?php
                                $all_known = array();
                                foreach ($amenity_groups as $group) {
                                    $all_known = array_merge($all_known, $group);
                                }
                                $custom = array_diff($amenities, $all_known);
                                ?>
                                <input type="text" name="shb_custom_amenities" id="shb_custom_amenities"
                                       value="<?php echo esc_attr(implode(', ', $custom)); ?>" class="large-text"
                                       placeholder="<?php _e('e.g. Private Pool, Rooftop Access', 'sanctuary-hotel-booking'); ?>">
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Policies Tab -->
            <div class="shb-meta-panel" data-panel="policies">
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="shb_min_nights"><?php _e('Minimum Nights', 'sanctuary-hotel-booking'); ?></label></th>
                        <td>
                            <input type="number" name="shb_min_nights" id="shb_min_nights" value="<?php echo esc_attr($min_nights); ?>" min="1" step="1" style="width:80px;">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="shb_max_nights"><?php _e('Maximum Nights', 'sanctuary-hotel-booking'); ?></label></th>
                        <td>
                            <input type="number" name="shb_max_nights" id="shb_max_nights" value="<?php echo esc_attr($max_nights); ?>" min="1" step="1" style="width:80px;">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="shb_cancellation_policy"><?php _e('Cancellation Policy', 'sanctuary-hotel-booking'); ?></label></th>
                        <td>
                            <select name="shb_cancellation_policy" id="shb_cancellation_policy">
                                <option value="flexible" <?php selected($cancel_policy, 'flexible'); ?>><?php _e('Flexible - Free cancellation up to 24h before', 'sanctuary-hotel-booking'); ?></option>
                                <option value="moderate" <?php selected($cancel_policy, 'moderate'); ?>><?php _e('Moderate - Free cancellation up to 5 days before', 'sanctuary-hotel-booking'); ?></option>
                                <option value="strict" <?php selected($cancel_policy, 'strict'); ?>><?php _e('Strict - 50% refund up to 7 days before', 'sanctuary-hotel-booking'); ?></option>
                                <option value="non_refundable" <?php selected($cancel_policy, 'non_refundable'); ?>><?php _e('Non-refundable', 'sanctuary-hotel-booking'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="shb_room_notes"><?php _e('Internal Notes', 'sanctuary-hotel-booking'); ?></label></th>
                        <td>
                            <textarea name="shb_room_notes" id="shb_room_notes" rows="3" class="large-text"
                                      placeholder="<?php _e('Notes visible only to admins...', 'sanctuary-hotel-booking'); ?>"><?php echo esc_textarea($room_notes); ?></textarea>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <p class="submit">
            <button type="submit" class="button button-primary button-large"><?php _e('Save Room', 'sanctuary-hotel-booking'); ?></button>
            <?php if ($room_id && !empty($room['permalink'])): ?>
                <a href="<?php echo esc_url($room['permalink']); ?>" class="button button-large" target="_blank" rel="noopener">
                    <?php _e('View Room Page', 'sanctuary-hotel-booking'); ?>
                </a>
            <?php endif; ?>
        </p>
    </form>
</div>
