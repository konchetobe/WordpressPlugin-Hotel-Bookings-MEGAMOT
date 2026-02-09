<div class="shb-my-bookings" data-testid="shb-my-bookings">
    <h2><?php _e('My Bookings', 'sanctuary-hotel-booking'); ?></h2>
    
    <?php if (empty($bookings)) : ?>
        <div class="shb-empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <h3><?php _e('No bookings yet', 'sanctuary-hotel-booking'); ?></h3>
            <p><?php _e('You haven\'t made any reservations yet.', 'sanctuary-hotel-booking'); ?></p>
            <?php $search_page = get_option('shb_booking_page_id'); ?>
            <a href="<?php echo esc_url(home_url()); ?>" class="shb-button shb-button-primary"><?php _e('Browse Rooms', 'sanctuary-hotel-booking'); ?></a>
        </div>
    <?php else : ?>
        <p class="shb-bookings-subtitle"><?php printf(__('Showing %d booking(s) for %s', 'sanctuary-hotel-booking'), count($bookings), '<strong>' . esc_html($email) . '</strong>'); ?></p>
        
        <div class="shb-bookings-list">
            <?php foreach ($bookings as $booking) : ?>
                <div class="shb-booking-item" data-testid="booking-item-<?php echo esc_attr($booking['booking_ref']); ?>">
                    <div class="shb-booking-item-header">
                        <div class="shb-booking-item-ref">
                            <span class="shb-booking-ref"><?php echo esc_html($booking['booking_ref']); ?></span>
                            <span class="shb-status shb-status-<?php echo esc_attr($booking['booking_status']); ?>">
                                <?php echo esc_html(ucfirst(str_replace('_', ' ', $booking['booking_status']))); ?>
                            </span>
                        </div>
                        <span class="shb-booking-item-price">
                            <?php echo esc_html(get_option('shb_currency_symbol', '$') . number_format($booking['total_price'], 2)); ?>
                        </span>
                    </div>
                    <div class="shb-booking-item-body">
                        <div class="shb-booking-item-detail">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            <strong><?php echo esc_html($booking['room_name']); ?></strong>
                        </div>
                        <div class="shb-booking-item-detail">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <?php echo esc_html($booking['check_in']); ?> &mdash; <?php echo esc_html($booking['check_out']); ?>
                        </div>
                        <div class="shb-booking-item-detail">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            <?php echo esc_html($booking['guests']); ?> <?php echo $booking['guests'] == 1 ? __('guest', 'sanctuary-hotel-booking') : __('guests', 'sanctuary-hotel-booking'); ?>
                        </div>
                    </div>
                    <div class="shb-booking-item-actions">
                        <a href="<?php echo esc_url(add_query_arg('shb_download_calendar', $booking['id'], home_url())); ?>" class="shb-button shb-button-outline shb-button-small">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <?php _e('Add to Calendar', 'sanctuary-hotel-booking'); ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
