<div class="shb-booking-form-wrap" data-testid="shb-booking-form">
    <div class="shb-booking-grid">
        <!-- Booking Form -->
        <div class="shb-booking-form-section">
            <h2><?php _e('Complete Your Booking', 'sanctuary-hotel-booking'); ?></h2>
            
            <form id="shb-booking-form" class="shb-form">
                <input type="hidden" name="room_id" value="<?php echo esc_attr($room_id); ?>">
                
                <!-- Dates -->
                <div class="shb-form-section">
                    <h3><?php _e('Stay Details', 'sanctuary-hotel-booking'); ?></h3>
                    <div class="shb-form-row shb-form-row-3">
                        <div class="shb-form-field">
                            <label for="booking-check-in"><?php _e('Check-in', 'sanctuary-hotel-booking'); ?></label>
                            <input type="text" id="booking-check-in" name="check_in" class="shb-datepicker" 
                                   value="<?php echo esc_attr($check_in); ?>" required readonly data-testid="booking-check-in">
                        </div>
                        <div class="shb-form-field">
                            <label for="booking-check-out"><?php _e('Check-out', 'sanctuary-hotel-booking'); ?></label>
                            <input type="text" id="booking-check-out" name="check_out" class="shb-datepicker" 
                                   value="<?php echo esc_attr($check_out); ?>" required readonly data-testid="booking-check-out">
                        </div>
                        <div class="shb-form-field">
                            <label for="booking-guests"><?php _e('Guests', 'sanctuary-hotel-booking'); ?></label>
                            <select id="booking-guests" name="guests" data-testid="booking-guests">
                                <?php for ($i = 1; $i <= $room['max_guests']; $i++) : ?>
                                    <option value="<?php echo $i; ?>" <?php selected($guests, $i); ?>>
                                        <?php echo $i; ?> <?php echo $i === 1 ? __('Guest', 'sanctuary-hotel-booking') : __('Guests', 'sanctuary-hotel-booking'); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Guest Information -->
                <div class="shb-form-section">
                    <h3><?php _e('Guest Information', 'sanctuary-hotel-booking'); ?></h3>
                    <div class="shb-form-row">
                        <div class="shb-form-field">
                            <label for="first_name"><?php _e('First Name', 'sanctuary-hotel-booking'); ?> *</label>
                            <input type="text" id="first_name" name="first_name" required data-testid="booking-first-name"
                                   value="<?php echo esc_attr($prefill['first_name']); ?>">
                        </div>
                        <div class="shb-form-field">
                            <label for="last_name"><?php _e('Last Name', 'sanctuary-hotel-booking'); ?> *</label>
                            <input type="text" id="last_name" name="last_name" required data-testid="booking-last-name"
                                   value="<?php echo esc_attr($prefill['last_name']); ?>">
                        </div>
                    </div>
                    <div class="shb-form-row">
                        <div class="shb-form-field">
                            <label for="email"><?php _e('Email', 'sanctuary-hotel-booking'); ?> *</label>
                            <input type="email" id="email" name="email" required data-testid="booking-email"
                                   value="<?php echo esc_attr($prefill['email']); ?>"
                                   <?php echo $prefill['email'] ? 'readonly style="background:#f1f5f9;cursor:not-allowed"' : ''; ?>>
                            <?php if ($prefill['email']) : ?>
                                <small style="color:#64748b;font-size:11px;margin-top:2px"><?php _e('Using your account email', 'sanctuary-hotel-booking'); ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="shb-form-field">
                            <label for="phone"><?php _e('Phone', 'sanctuary-hotel-booking'); ?> *</label>
                            <input type="tel" id="phone" name="phone" required data-testid="booking-phone">
                        </div>
                    </div>
                    <div class="shb-form-field shb-form-field-full">
                        <label for="special_requests"><?php _e('Special Requests', 'sanctuary-hotel-booking'); ?></label>
                        <textarea id="special_requests" name="special_requests" rows="3" 
                                  placeholder="<?php _e('Any special requests or preferences...', 'sanctuary-hotel-booking'); ?>" data-testid="booking-requests"></textarea>
                    </div>
                </div>
                
                <!-- Payment Method -->
                <div class="shb-form-section">
                    <h3><?php _e('Payment Method', 'sanctuary-hotel-booking'); ?></h3>
                    <div class="shb-payment-options">
                        <?php if (get_option('shb_stripe_enabled', '1') === '1') : ?>
                            <label class="shb-payment-option">
                                <input type="radio" name="payment_method" value="stripe" checked>
                                <span class="shb-payment-label">
                                    <strong><?php _e('Card Payment (Stripe)', 'sanctuary-hotel-booking'); ?></strong>
                                    <small><?php _e('Credit/Debit Card, Apple Pay, Google Pay', 'sanctuary-hotel-booking'); ?></small>
                                </span>
                            </label>
                        <?php endif; ?>
                        
                        <?php if (get_option('shb_paypal_enabled', '0') === '1') : ?>
                            <label class="shb-payment-option">
                                <input type="radio" name="payment_method" value="paypal">
                                <span class="shb-payment-label">
                                    <strong><?php _e('PayPal', 'sanctuary-hotel-booking'); ?></strong>
                                    <small><?php _e('Pay with your PayPal account', 'sanctuary-hotel-booking'); ?></small>
                                </span>
                            </label>
                        <?php endif; ?>
                    </div>
                </div>
                
                <button type="submit" class="shb-button shb-button-primary shb-button-large" id="shb-submit-booking" data-testid="submit-booking-btn">
                    <?php _e('Proceed to Payment', 'sanctuary-hotel-booking'); ?>
                </button>
            </form>
        </div>
        
        <!-- Booking Summary Sidebar -->
        <div class="shb-booking-summary-section">
            <div class="shb-booking-summary" data-testid="booking-summary">
                <h3><?php _e('Booking Summary', 'sanctuary-hotel-booking'); ?></h3>
                
                <div class="shb-summary-room">
                    <img src="<?php echo esc_url($room['image']); ?>" alt="<?php echo esc_attr($room['name']); ?>">
                    <div class="shb-summary-room-info">
                        <h4><?php echo esc_html($room['name']); ?></h4>
                        <span class="shb-room-type-badge shb-badge-sm"><?php echo esc_html(ucfirst($room['room_type'])); ?></span>
                    </div>
                </div>
                
                <div class="shb-summary-specs">
                    <span><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg> <?php _e('Up to', 'sanctuary-hotel-booking'); ?> <?php echo esc_html($room['max_guests']); ?> <?php _e('guests', 'sanctuary-hotel-booking'); ?></span>
                    <span><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 4v16"/><path d="M2 8h18a2 2 0 0 1 2 2v10"/><path d="M2 17h20"/><path d="M6 8v9"/></svg> <?php echo esc_html(ucfirst($room['bed_type'])); ?> <?php _e('bed', 'sanctuary-hotel-booking'); ?></span>
                </div>
                
                <div class="shb-summary-details" id="shb-price-breakdown">
                    <div class="shb-summary-row">
                        <span><?php echo esc_html(get_option('shb_currency_symbol', '$') . number_format($room['base_price'], 2)); ?> &times; <span id="nights-count">0</span> <?php _e('nights', 'sanctuary-hotel-booking'); ?></span>
                        <span id="subtotal-price"><?php echo esc_html(get_option('shb_currency_symbol', '$')); ?>0.00</span>
                    </div>
                    <div class="shb-summary-row shb-adjustment" style="display: none;">
                        <span><?php _e('Price adjustment', 'sanctuary-hotel-booking'); ?></span>
                        <span id="adjustment-percent">0%</span>
                    </div>
                    <div class="shb-summary-row shb-total">
                        <span><?php _e('Total', 'sanctuary-hotel-booking'); ?></span>
                        <span id="total-price"><?php echo esc_html(get_option('shb_currency_symbol', '$')); ?>0.00</span>
                    </div>
                </div>
                
                <p class="shb-summary-note"><?php _e("You won't be charged yet", 'sanctuary-hotel-booking'); ?></p>
            </div>
        </div>
    </div>
</div>
