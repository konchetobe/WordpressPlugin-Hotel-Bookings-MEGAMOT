<div class="shb-room-list" data-testid="shb-room-list">
    <?php if (empty($rooms)) : ?>
        <div class="shb-empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            <h3><?php _e('No rooms available', 'sanctuary-hotel-booking'); ?></h3>
            <p><?php _e('Check back soon for new listings.', 'sanctuary-hotel-booking'); ?></p>
        </div>
    <?php else : ?>
        <div class="shb-rooms-grid shb-columns-<?php echo esc_attr($atts['columns']); ?>">
            <?php foreach ($rooms as $room) : 
                $bed_label = ucfirst(str_replace('_', ' ', $room['bed_type']));
                $amenity_icons = array_slice($room['amenities'], 0, 4);
            ?>
                <div class="shb-room-card" data-room-id="<?php echo esc_attr($room['id']); ?>" data-testid="shb-room-card-<?php echo esc_attr($room['id']); ?>">
                    <div class="shb-room-image">
                        <img src="<?php echo esc_url($room['image']); ?>" alt="<?php echo esc_attr($room['name']); ?>" loading="lazy">
                        <span class="shb-room-type-badge"><?php echo esc_html(ucfirst($room['room_type'])); ?></span>
                        <div class="shb-room-price-tag">
                            <span class="shb-price-amount"><?php echo esc_html(get_option('shb_currency_symbol', '$') . number_format($room['base_price'], 0)); ?></span>
                            <span class="shb-price-unit">/<?php _e('night', 'sanctuary-hotel-booking'); ?></span>
                        </div>
                    </div>
                    <div class="shb-room-content">
                        <h3 class="shb-room-title"><?php echo esc_html($room['name']); ?></h3>
                        
                        <div class="shb-room-specs">
                            <span class="shb-spec" title="<?php _e('Max Guests', 'sanctuary-hotel-booking'); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                                <?php echo esc_html($room['max_guests']); ?>
                            </span>
                            <span class="shb-spec" title="<?php _e('Bed Type', 'sanctuary-hotel-booking'); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 4v16"/><path d="M2 8h18a2 2 0 0 1 2 2v10"/><path d="M2 17h20"/><path d="M6 8v9"/></svg>
                                <?php echo esc_html($bed_label); ?>
                            </span>
                            <?php if ($room['room_size'] > 0) : ?>
                            <span class="shb-spec" title="<?php _e('Room Size', 'sanctuary-hotel-booking'); ?>">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                                <?php echo esc_html($room['room_size']); ?>m&sup2;
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (!empty($amenity_icons)) : ?>
                        <div class="shb-room-amenities-strip">
                            <?php foreach ($amenity_icons as $amenity) : ?>
                                <span class="shb-amenity-tag"><?php echo esc_html($amenity); ?></span>
                            <?php endforeach; ?>
                            <?php if (count($room['amenities']) > 4) : ?>
                                <span class="shb-amenity-more">+<?php echo count($room['amenities']) - 4; ?></span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <p class="shb-room-excerpt"><?php echo esc_html(wp_trim_words($room['description'], 15)); ?></p>
                        
                        <div class="shb-room-actions">
                            <a href="<?php echo esc_url($room['permalink']); ?>" class="shb-button shb-button-outline" data-testid="view-details-<?php echo esc_attr($room['id']); ?>">
                                <?php _e('Details', 'sanctuary-hotel-booking'); ?>
                            </a>
                            <a href="<?php echo esc_url(Sanctuary_Hotel_Booking::get_booking_url($room['id'])); ?>" class="shb-button shb-button-primary" data-testid="book-now-<?php echo esc_attr($room['id']); ?>">
                                <?php _e('Book Now', 'sanctuary-hotel-booking'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
