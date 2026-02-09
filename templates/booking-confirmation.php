<div class="shb-booking-confirmation" data-testid="shb-booking-confirmation">
    <div class="shb-confirmation-card">
        <?php if ($booking['payment_status'] === 'paid'): ?>
            <div class="shb-confirmation-icon shb-success">
                <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <h1><?php _e('Booking Confirmed!', 'sanctuary-hotel-booking'); ?></h1>
            <p class="shb-confirm-subtitle">
                <?php _e('Your reservation has been successfully processed.', 'sanctuary-hotel-booking'); ?></p>
        <?php else: ?>
            <div class="shb-confirmation-icon shb-pending">
                <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <h1><?php _e('Payment Pending', 'sanctuary-hotel-booking'); ?></h1>
            <p class="shb-confirm-subtitle">
                <?php _e('Your booking is awaiting payment confirmation.', 'sanctuary-hotel-booking'); ?></p>
        <?php endif; ?>
    </div>

    <div class="shb-booking-details">
        <div class="shb-detail-header">
            <h2><?php _e('Booking Details', 'sanctuary-hotel-booking'); ?></h2>
            <span class="shb-status shb-status-<?php echo esc_attr($booking['booking_status']); ?>">
                <?php echo esc_html(ucfirst(str_replace('_', ' ', $booking['booking_status']))); ?>
            </span>
        </div>

        <div class="shb-details-grid">
            <div class="shb-detail-item">
                <span class="shb-detail-label"><?php _e('Booking Reference', 'sanctuary-hotel-booking'); ?></span>
                <span class="shb-detail-value shb-booking-ref"><?php echo esc_html($booking['booking_ref']); ?></span>
            </div>
            <div class="shb-detail-item">
                <span class="shb-detail-label"><?php _e('Room', 'sanctuary-hotel-booking'); ?></span>
                <span class="shb-detail-value"><?php echo esc_html($booking['room_name']); ?></span>
            </div>
            <div class="shb-detail-item">
                <span class="shb-detail-label"><?php _e('Check-in', 'sanctuary-hotel-booking'); ?></span>
                <span class="shb-detail-value">
                    <?php echo esc_html($booking['check_in']); ?>
                    <small><?php _e('from', 'sanctuary-hotel-booking'); ?>
                        <?php echo esc_html(get_option('shb_check_in_time', '14:00')); ?></small>
                </span>
            </div>
            <div class="shb-detail-item">
                <span class="shb-detail-label"><?php _e('Check-out', 'sanctuary-hotel-booking'); ?></span>
                <span class="shb-detail-value">
                    <?php echo esc_html($booking['check_out']); ?>
                    <small><?php _e('until', 'sanctuary-hotel-booking'); ?>
                        <?php echo esc_html(get_option('shb_check_out_time', '11:00')); ?></small>
                </span>
            </div>
            <div class="shb-detail-item">
                <span class="shb-detail-label"><?php _e('Guest', 'sanctuary-hotel-booking'); ?></span>
                <span class="shb-detail-value">
                    <?php echo esc_html($booking['first_name'] . ' ' . $booking['last_name']); ?>
                    <small><?php echo esc_html($booking['guests']); ?>
                        <?php echo $booking['guests'] == 1 ? __('guest', 'sanctuary-hotel-booking') : __('guests', 'sanctuary-hotel-booking'); ?></small>
                </span>
            </div>
            <div class="shb-detail-item shb-detail-highlight">
                <span class="shb-detail-label"><?php _e('Total', 'sanctuary-hotel-booking'); ?></span>
                <span class="shb-detail-value shb-total-paid">
                    <?php echo esc_html(get_option('shb_currency_symbol', '$') . number_format($booking['total_price'], 2)); ?>
                </span>
            </div>
        </div>
    </div>

    <?php if ($booking['payment_method'] === 'bank_transfer' && $booking['payment_status'] !== 'paid'): ?>
        <div class="shb-bank-transfer-details">
            <div class="shb-detail-header">
                <h2><?php _e('Bank Transfer Details', 'sanctuary-hotel-booking'); ?></h2>
            </div>

            <div class="shb-bank-info-card">
                <p class="shb-bank-instructions">
                    <?php
                    $instructions = get_option('shb_bank_instructions', '');
                    if (empty($instructions)) {
                        _e('Please transfer the total amount to the following bank account. Use your booking reference as the payment reference. Your booking will be confirmed once we receive the payment.', 'sanctuary-hotel-booking');
                    } else {
                        echo esc_html($instructions);
                    }
                    ?>
                </p>

                <div class="shb-bank-details-grid">
                    <?php if ($account_holder = get_option('shb_bank_account_holder', '')): ?>
                        <div class="shb-bank-detail-item">
                            <span class="shb-bank-label"><?php _e('Account Holder', 'sanctuary-hotel-booking'); ?></span>
                            <span class="shb-bank-value"><?php echo esc_html($account_holder); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($iban = get_option('shb_bank_iban', '')): ?>
                        <div class="shb-bank-detail-item">
                            <span class="shb-bank-label"><?php _e('IBAN', 'sanctuary-hotel-booking'); ?></span>
                            <span class="shb-bank-value shb-iban"><?php echo esc_html($iban); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($bic = get_option('shb_bank_bic', '')): ?>
                        <div class="shb-bank-detail-item">
                            <span class="shb-bank-label"><?php _e('BIC/SWIFT', 'sanctuary-hotel-booking'); ?></span>
                            <span class="shb-bank-value"><?php echo esc_html($bic); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ($bank_name = get_option('shb_bank_name', '')): ?>
                        <div class="shb-bank-detail-item">
                            <span class="shb-bank-label"><?php _e('Bank Name', 'sanctuary-hotel-booking'); ?></span>
                            <span class="shb-bank-value"><?php echo esc_html($bank_name); ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="shb-bank-detail-item shb-bank-reference">
                        <span class="shb-bank-label"><?php _e('Payment Reference', 'sanctuary-hotel-booking'); ?></span>
                        <span class="shb-bank-value shb-payment-ref"><?php echo esc_html($booking['booking_ref']); ?></span>
                        <small><?php _e('Please include this reference in your bank transfer', 'sanctuary-hotel-booking'); ?></small>
                    </div>

                    <div class="shb-bank-detail-item shb-bank-amount">
                        <span class="shb-bank-label"><?php _e('Amount to Transfer', 'sanctuary-hotel-booking'); ?></span>
                        <span
                            class="shb-bank-value shb-amount-value"><?php echo esc_html(get_option('shb_currency_symbol', '$') . number_format($booking['total_price'], 2)); ?></span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="shb-confirmation-actions">
        <a href="<?php echo esc_url(add_query_arg('shb_download_calendar', $booking['id'], home_url())); ?>"
            class="shb-button shb-button-outline" data-testid="download-calendar-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            <?php _e('Add to Calendar', 'sanctuary-hotel-booking'); ?>
        </a>
        <a href="<?php echo esc_url(home_url()); ?>" class="shb-button shb-button-primary">
            <?php _e('Return to Home', 'sanctuary-hotel-booking'); ?>
        </a>
    </div>

    <p class="shb-confirmation-contact">
        <?php _e('Need help? Contact us at', 'sanctuary-hotel-booking'); ?>
        <a href="mailto:<?php echo esc_attr(get_option('shb_admin_email', get_option('admin_email'))); ?>">
            <?php echo esc_html(get_option('shb_admin_email', get_option('admin_email'))); ?>
        </a>
    </p>
</div>