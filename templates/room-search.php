<div class="shb-room-search" data-testid="shb-room-search">
    <form class="shb-search-form" id="shb-search-form">
        <div class="shb-search-fields">
            <?php if (count($locations) > 1): ?>
                <div class="shb-field">
                    <label for="shb-location"><?php _e('Location', 'sanctuary-hotel-booking'); ?></label>
                    <select id="shb-location" name="location">
                        <option value=""><?php _e('All Locations', 'sanctuary-hotel-booking'); ?></option>
                        <?php foreach ($locations as $loc): ?>
                            <option value="<?php echo esc_attr($loc['id']); ?>" <?php selected($location_id, $loc['id']); ?>>
                                <?php echo esc_html($loc['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php elseif (!empty($location_id)): ?>
                <?php // Single-property context (e.g. a location page): scope silently. ?>
                <input type="hidden" id="shb-location" name="location" value="<?php echo esc_attr($location_id); ?>">
            <?php endif; ?>
            <div class="shb-field">
                <label for="shb-check-in"><?php _e('Check-in', 'sanctuary-hotel-booking'); ?></label>
                <input type="text" id="shb-check-in" name="check_in" class="shb-datepicker" 
                       placeholder="<?php _e('Select date', 'sanctuary-hotel-booking'); ?>" required readonly>
            </div>
            <div class="shb-field">
                <label for="shb-check-out"><?php _e('Check-out', 'sanctuary-hotel-booking'); ?></label>
                <input type="text" id="shb-check-out" name="check_out" class="shb-datepicker" 
                       placeholder="<?php _e('Select date', 'sanctuary-hotel-booking'); ?>" required readonly>
            </div>
            <div class="shb-field">
                <label for="shb-guests"><?php _e('Guests', 'sanctuary-hotel-booking'); ?></label>
                <select id="shb-guests" name="guests">
                    <?php for ($i = 1; $i <= 10; $i++) : ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?> <?php echo $i === 1 ? __('Guest', 'sanctuary-hotel-booking') : __('Guests', 'sanctuary-hotel-booking'); ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="shb-field shb-field-button">
                <button type="submit" class="shb-button shb-button-primary" data-testid="shb-search-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <?php _e('Search', 'sanctuary-hotel-booking'); ?>
                </button>
            </div>
        </div>

        <?php
        // Room type filter chips. On a location page the search is scoped to a
        // single property and $location_id is preset; the chips always render.
        $room_types = !empty($all_room_types) ? $all_room_types : SHB_Room_Type::get_room_types();
        ?>
        <?php if (!empty($room_types)): ?>
            <div class="shb-search-filters" id="shb-search-filters">
                <span class="shb-filter-label"><?php _e('Room type:', 'sanctuary-hotel-booking'); ?></span>
                <?php foreach ($room_types as $type): ?>
                    <?php
                    $chip_type = $type['slug'];
                    $is_active_chip = ($room_type_filter !== '') && ($room_type_filter === $chip_type);
                    ?>
                    <button type="button" class="shb-filter-chip<?php echo $is_active_chip ? ' active' : ''; ?>"
                            data-type="<?php echo esc_attr($chip_type); ?>">
                        <?php echo esc_html($type['name']); ?>
                    </button>
                <?php endforeach; ?>
            </div>
            <input type="hidden" id="shb-room-type" name="room_type"
                   value="<?php echo esc_attr($room_type_filter); ?>">
        <?php endif; ?>
    </form>
    
    <div id="shb-search-results" class="shb-search-results" style="display: none;" data-testid="shb-search-results">
        <div class="shb-results-header">
            <h3 id="shb-results-title"><?php _e('Available Rooms', 'sanctuary-hotel-booking'); ?></h3>
            <span id="shb-results-count" class="shb-results-count"></span>
        </div>
        <div class="shb-rooms-grid shb-columns-auto" id="shb-rooms-grid"></div>
    </div>
    
    <div id="shb-search-loading" class="shb-search-loading" style="display: none;">
        <div class="shb-spinner"></div>
        <p><?php _e('Searching available rooms...', 'sanctuary-hotel-booking'); ?></p>
    </div>
    
    <div id="shb-no-results" class="shb-no-results" style="display: none;">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><path d="M8 11h6"/></svg>
        <h3><?php _e('No rooms available', 'sanctuary-hotel-booking'); ?></h3>
        <p><?php _e('Try adjusting your dates or guest count.', 'sanctuary-hotel-booking'); ?></p>
    </div>
</div>
