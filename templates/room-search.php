<div class="shb-room-search" data-testid="shb-room-search">
    <form class="shb-search-form" id="shb-search-form">
        <div class="shb-search-fields">
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
