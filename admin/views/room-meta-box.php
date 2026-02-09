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
                <th scope="row">
                    <label for="shb_room_type"><?php _e('Room Type', 'sanctuary-hotel-booking'); ?></label>
                </th>
                <td>
                    <select name="shb_room_type" id="shb_room_type" class="regular-text">
                        <option value="standard" <?php selected($room_type, 'standard'); ?>><?php _e('Standard', 'sanctuary-hotel-booking'); ?></option>
                        <option value="deluxe" <?php selected($room_type, 'deluxe'); ?>><?php _e('Deluxe', 'sanctuary-hotel-booking'); ?></option>
                        <option value="suite" <?php selected($room_type, 'suite'); ?>><?php _e('Suite', 'sanctuary-hotel-booking'); ?></option>
                        <option value="family" <?php selected($room_type, 'family'); ?>><?php _e('Family', 'sanctuary-hotel-booking'); ?></option>
                        <option value="penthouse" <?php selected($room_type, 'penthouse'); ?>><?php _e('Penthouse', 'sanctuary-hotel-booking'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="shb_base_price"><?php _e('Base Price (per night)', 'sanctuary-hotel-booking'); ?></label>
                </th>
                <td>
                    <input type="number" name="shb_base_price" id="shb_base_price" 
                           value="<?php echo esc_attr($base_price); ?>" 
                           step="0.01" min="0" style="width: 150px;">
                    <span class="description"><?php echo esc_html(get_option('shb_currency_symbol', '$')); ?></span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="shb_max_guests"><?php _e('Maximum Guests', 'sanctuary-hotel-booking'); ?></label>
                </th>
                <td>
                    <select name="shb_max_guests" id="shb_max_guests">
                        <?php for ($i = 1; $i <= 10; $i++) : ?>
                            <option value="<?php echo $i; ?>" <?php selected($max_guests, $i); ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="shb_bed_type"><?php _e('Bed Type', 'sanctuary-hotel-booking'); ?></label>
                </th>
                <td>
                    <?php $bed_type = get_post_meta($post->ID, '_shb_bed_type', true) ?: 'queen'; ?>
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
                <th scope="row">
                    <label for="shb_room_size"><?php _e('Room Size (m²)', 'sanctuary-hotel-booking'); ?></label>
                </th>
                <td>
                    <?php $room_size = get_post_meta($post->ID, '_shb_room_size', true) ?: ''; ?>
                    <input type="number" name="shb_room_size" id="shb_room_size" 
                           value="<?php echo esc_attr($room_size); ?>" 
                           step="1" min="0" style="width: 100px;">
                    <span class="description">m²</span>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="shb_floor"><?php _e('Floor Number', 'sanctuary-hotel-booking'); ?></label>
                </th>
                <td>
                    <?php $floor = get_post_meta($post->ID, '_shb_floor', true) ?: ''; ?>
                    <input type="number" name="shb_floor" id="shb_floor" 
                           value="<?php echo esc_attr($floor); ?>" 
                           step="1" min="0" style="width: 80px;">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="shb_is_active"><?php _e('Status', 'sanctuary-hotel-booking'); ?></label>
                </th>
                <td>
                    <label>
                        <input type="checkbox" name="shb_is_active" id="shb_is_active" value="1" 
                               <?php checked($is_active, '1'); ?>>
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
                <th scope="row">
                    <label><?php _e('Amenities', 'sanctuary-hotel-booking'); ?></label>
                </th>
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
                    <?php foreach ($amenity_groups as $group_name => $group_amenities) : ?>
                        <div class="shb-amenity-group">
                            <strong class="shb-amenity-group-title"><?php echo esc_html($group_name); ?></strong>
                            <div class="shb-amenity-checkboxes">
                                <?php foreach ($group_amenities as $amenity) : ?>
                                    <label class="shb-amenity-label">
                                        <input type="checkbox" name="shb_amenities[]" value="<?php echo esc_attr($amenity); ?>"
                                               <?php checked(in_array($amenity, $amenities)); ?>>
                                        <?php echo esc_html($amenity); ?>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="shb-amenity-group" style="margin-top: 16px;">
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
                               value="<?php echo esc_attr(implode(', ', $custom)); ?>"
                               class="large-text" placeholder="<?php _e('e.g. Private Pool, Rooftop Access', 'sanctuary-hotel-booking'); ?>">
                    </div>
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Policies Tab -->
    <div class="shb-meta-panel" data-panel="policies">
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="shb_min_nights"><?php _e('Minimum Nights', 'sanctuary-hotel-booking'); ?></label>
                </th>
                <td>
                    <?php $min_nights = get_post_meta($post->ID, '_shb_min_nights', true) ?: '1'; ?>
                    <input type="number" name="shb_min_nights" id="shb_min_nights" 
                           value="<?php echo esc_attr($min_nights); ?>" 
                           min="1" step="1" style="width: 80px;">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="shb_max_nights"><?php _e('Maximum Nights', 'sanctuary-hotel-booking'); ?></label>
                </th>
                <td>
                    <?php $max_nights = get_post_meta($post->ID, '_shb_max_nights', true) ?: '30'; ?>
                    <input type="number" name="shb_max_nights" id="shb_max_nights" 
                           value="<?php echo esc_attr($max_nights); ?>" 
                           min="1" step="1" style="width: 80px;">
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="shb_cancellation_policy"><?php _e('Cancellation Policy', 'sanctuary-hotel-booking'); ?></label>
                </th>
                <td>
                    <?php $cancel_policy = get_post_meta($post->ID, '_shb_cancellation_policy', true) ?: 'flexible'; ?>
                    <select name="shb_cancellation_policy" id="shb_cancellation_policy">
                        <option value="flexible" <?php selected($cancel_policy, 'flexible'); ?>><?php _e('Flexible - Free cancellation up to 24h before', 'sanctuary-hotel-booking'); ?></option>
                        <option value="moderate" <?php selected($cancel_policy, 'moderate'); ?>><?php _e('Moderate - Free cancellation up to 5 days before', 'sanctuary-hotel-booking'); ?></option>
                        <option value="strict" <?php selected($cancel_policy, 'strict'); ?>><?php _e('Strict - 50% refund up to 7 days before', 'sanctuary-hotel-booking'); ?></option>
                        <option value="non_refundable" <?php selected($cancel_policy, 'non_refundable'); ?>><?php _e('Non-refundable', 'sanctuary-hotel-booking'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="shb_room_notes"><?php _e('Internal Notes', 'sanctuary-hotel-booking'); ?></label>
                </th>
                <td>
                    <?php $room_notes = get_post_meta($post->ID, '_shb_room_notes', true) ?: ''; ?>
                    <textarea name="shb_room_notes" id="shb_room_notes" rows="3" class="large-text"
                              placeholder="<?php _e('Notes visible only to admins...', 'sanctuary-hotel-booking'); ?>"><?php echo esc_textarea($room_notes); ?></textarea>
                </td>
            </tr>
        </table>
    </div>
</div>

<style>
.shb-room-meta-box .form-table th {
    width: 200px;
    padding: 15px 10px 15px 0;
}
.shb-room-meta-box .form-table td {
    padding: 15px 10px;
}
/* Tabs */
.shb-meta-tabs {
    display: flex;
    gap: 0;
    border-bottom: 2px solid #e5e7eb;
    margin-bottom: 20px;
}
.shb-meta-tab {
    padding: 10px 20px;
    background: transparent;
    border: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    transition: all 0.2s;
}
.shb-meta-tab:hover {
    color: #1e293b;
}
.shb-meta-tab.active {
    color: #2563eb;
    border-bottom-color: #2563eb;
}
.shb-meta-panel {
    display: none;
}
.shb-meta-panel.active {
    display: block;
}
/* Amenity Groups */
.shb-amenity-group {
    margin-bottom: 16px;
    padding-bottom: 16px;
    border-bottom: 1px solid #f1f5f9;
}
.shb-amenity-group:last-of-type {
    border-bottom: none;
}
.shb-amenity-group-title {
    display: block;
    margin-bottom: 10px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
}
.shb-amenity-checkboxes {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
}
.shb-amenity-label {
    font-weight: normal !important;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
}
.shb-amenity-label:hover {
    color: #2563eb;
}
@media (max-width: 782px) {
    .shb-amenity-checkboxes {
        grid-template-columns: repeat(2, 1fr);
    }
}
/* Action group */
.shb-action-group {
    display: flex;
    gap: 4px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.shb-meta-tab').on('click', function() {
        var tab = $(this).data('tab');
        $('.shb-meta-tab').removeClass('active');
        $(this).addClass('active');
        $('.shb-meta-panel').removeClass('active');
        $('.shb-meta-panel[data-panel="' + tab + '"]').addClass('active');
    });
});
</script>
