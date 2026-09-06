<?php
/**
 * Room card partial — single source of truth for room card markup.
 *
 * Variables:
 *   $room            Formatted room array (SHB_Room::format_room output).
 *   $price           Optional price breakdown for a date range
 *                    (SHB_Pricing::get_price_breakdown output).
 *   $currency_symbol Optional currency symbol; falls back to location/global.
 *   $atts            Optional shortcode attributes (columns, etc.).
 */
if (!defined('ABSPATH')) {
    exit;
}

$room_id = isset($room['id']) ? $room['id'] : 0;
if (!isset($dates) || !is_array($dates)) {
    $dates = array();
}
$currency_symbol = !empty($currency_symbol)
    ? $currency_symbol
    : (!empty($room['location']['currency_symbol']) ? $room['location']['currency_symbol'] : get_option('shb_currency_symbol', '$'));

$price_amount = isset($price['total']) ? $price['total'] : (isset($room['base_price']) ? $room['base_price'] : 0);
$price_unit = isset($price['total']) ? __('total', 'sanctuary-hotel-booking') : __('night', 'sanctuary-hotel-booking');

$bed_label = !empty($room['bed_type']) ? ucfirst(str_replace('_', ' ', $room['bed_type'])) : '';
$amenity_icons = !empty($room['amenities']) ? array_slice($room['amenities'], 0, 4) : array();
$type_name = !empty($room['room_type']) ? ucfirst(str_replace('-', ' ', $room['room_type'])) : '';
?>

<div class="shb-room-card" data-room-id="<?php echo esc_attr($room_id); ?>" data-testid="shb-room-card-<?php echo esc_attr($room_id); ?>">
    <div class="shb-room-image">
        <img src="<?php echo esc_url($room['image']); ?>" alt="<?php echo esc_attr($room['name']); ?>" loading="lazy">
        <?php if ($type_name): ?>
            <span class="shb-room-type-badge"><?php echo esc_html($type_name); ?></span>
        <?php endif; ?>
        <div class="shb-room-price-tag">
            <span class="shb-price-amount"><?php echo esc_html($currency_symbol . number_format(floatval($price_amount), 0)); ?></span>
            <span class="shb-price-unit">/<?php echo esc_html($price_unit); ?></span>
        </div>
    </div>
    <div class="shb-room-content">
        <h3 class="shb-room-title"><?php echo esc_html($room['name']); ?></h3>
        <?php if (!empty($room['location_name'])): ?>
            <span class="shb-room-location"><?php echo esc_html($room['location_name']); ?></span>
        <?php endif; ?>

        <div class="shb-room-specs">
            <span class="shb-spec" title="<?php _e('Max Guests', 'sanctuary-hotel-booking'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                <?php echo esc_html($room['max_guests']); ?>
            </span>
            <?php if ($bed_label): ?>
            <span class="shb-spec" title="<?php _e('Bed Type', 'sanctuary-hotel-booking'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 4v16"/><path d="M2 8h18a2 2 0 0 1 2 2v10"/><path d="M2 17h20"/><path d="M6 8v9"/></svg>
                <?php echo esc_html($bed_label); ?>
            </span>
            <?php endif; ?>
            <?php if (!empty($room['room_size'])): ?>
            <span class="shb-spec" title="<?php _e('Room Size', 'sanctuary-hotel-booking'); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
                <?php echo esc_html($room['room_size']); ?>m&sup2;
            </span>
            <?php endif; ?>
        </div>

        <?php if (!empty($amenity_icons)): ?>
        <div class="shb-room-amenities-strip">
            <?php foreach ($amenity_icons as $amenity): ?>
                <span class="shb-amenity-tag"><?php echo esc_html($amenity); ?></span>
            <?php endforeach; ?>
            <?php if (count($room['amenities']) > 4): ?>
                <span class="shb-amenity-more">+<?php echo count($room['amenities']) - 4; ?></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($room['description'])): ?>
            <p class="shb-room-excerpt"><?php echo esc_html(wp_trim_words($room['description'], 15)); ?></p>
        <?php endif; ?>

        <div class="shb-room-actions">
            <?php
            $book_args = array('location' => $room['location_id']);
            if (!empty($dates['check_in']) && !empty($dates['check_out'])) {
                $book_args['check_in'] = $dates['check_in'];
                $book_args['check_out'] = $dates['check_out'];
                if (!empty($dates['guests'])) {
                    $book_args['guests'] = $dates['guests'];
                }
            }
            ?>
            <a href="<?php echo esc_url($room['permalink']); ?>" class="shb-button shb-button-outline" data-testid="view-details-<?php echo esc_attr($room_id); ?>">
                <?php _e('Details', 'sanctuary-hotel-booking'); ?>
            </a>
            <a href="<?php echo esc_url(Sanctuary_Hotel_Booking::get_booking_url($room_id, $book_args)); ?>" class="shb-button shb-button-primary" data-testid="book-now-<?php echo esc_attr($room_id); ?>">
                <?php _e('Book Now', 'sanctuary-hotel-booking'); ?>
            </a>
        </div>
    </div>
</div>
