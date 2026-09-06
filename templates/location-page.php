<?php
/**
 * Location property page content.
 *
 * Variables: $location (formatted location array), $rooms (rooms at this
 * location, all statuses — inactive filtered below), $room_types (per-type
 * summary with room count + min price), $currency_symbol.
 */
if (!defined('ABSPATH')) {
    exit;
}

$active_rooms = array_values(array_filter($rooms, function ($room) {
    return $room['is_active'];
}));
$currency_symbol = !empty($location['currency_symbol']) ? $location['currency_symbol'] : get_option('shb_currency_symbol', '$');
?>

<div class="shb-location-page" data-testid="shb-location-page" data-location-id="<?php echo esc_attr($location['id']); ?>">
    <?php if (!empty($location['image'])): ?>
        <div class="shb-location-hero">
            <img src="<?php echo esc_url($location['image']); ?>" alt="<?php echo esc_attr($location['name']); ?>" loading="lazy">
        </div>
    <?php endif; ?>

    <div class="shb-location-details">
        <div class="shb-location-address">
            <?php if (!empty($location['address'])): ?>
                <span><?php echo esc_html($location['address']); ?></span>
            <?php endif; ?>
            <?php
            $city_country = trim(($location['city'] ?? '') . ', ' . ($location['country'] ?? ''), ', ');
            if ($city_country): ?>
                <span><?php echo esc_html($city_country); ?></span>
            <?php endif; ?>
        </div>
        <?php if (!empty($location['phone']) || !empty($location['email'])): ?>
            <div class="shb-location-contact">
                <?php if (!empty($location['phone'])): ?>
                    <span class="shb-location-phone"><?php echo esc_html($location['phone']); ?></span>
                <?php endif; ?>
                <?php if (!empty($location['email'])): ?>
                    <a href="mailto:<?php echo esc_attr($location['email']); ?>"><?php echo esc_html($location['email']); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="shb-location-times">
            <?php _e('Check-in', 'sanctuary-hotel-booking'); ?> <?php echo esc_html($location['check_in_time']); ?>
            &middot;
            <?php _e('Check-out', 'sanctuary-hotel-booking'); ?> <?php echo esc_html($location['check_out_time']); ?>
        </div>
    </div>

    <?php if (empty($active_rooms)): ?>
        <div class="shb-empty-state">
            <h3><?php _e('No rooms available at this property yet', 'sanctuary-hotel-booking'); ?></h3>
            <p><?php _e('Check back soon for new listings.', 'sanctuary-hotel-booking'); ?></p>
        </div>
    <?php else: ?>
        <?php if (!empty($room_types)): ?>
            <div class="shb-location-room-types" data-testid="shb-location-room-types">
                <h2><?php _e('Rooms & Suites', 'sanctuary-hotel-booking'); ?></h2>
                <div class="shb-rooms-grid shb-columns-auto">
                    <?php foreach ($room_types as $type): ?>
                        <div class="shb-room-card shb-type-summary-card">
                            <?php if (!empty($type['image'])): ?>
                                <div class="shb-room-image">
                                    <img src="<?php echo esc_url($type['image']); ?>" alt="<?php echo esc_attr($type['name']); ?>" loading="lazy">
                                    <span class="shb-room-type-badge"><?php echo esc_html($type['name']); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="shb-room-content">
                                <h3 class="shb-room-title"><?php echo esc_html($type['name']); ?></h3>
                                <?php if (!empty($type['description'])): ?>
                                    <p class="shb-room-excerpt"><?php echo esc_html(wp_trim_words($type['description'], 15)); ?></p>
                                <?php endif; ?>
                                <div class="shb-room-actions">
                                    <a href="<?php echo esc_url(add_query_arg(array('location' => $location['id'], 'type' => $type['slug']), get_permalink())); ?>" class="shb-button shb-button-primary">
                                        <?php
                                        printf(
                                            /* translators: %s: number of rooms. */
                                            _n('%s room from %s', '%s rooms from %s', $type['count'], 'sanctuary-hotel-booking'),
                                            esc_html($type['count']),
                                            esc_html($currency_symbol . number_format($type['min_price'], 0))
                                        );
                                        ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="shb-location-search">
            <h2><?php _e('Check Availability', 'sanctuary-hotel-booking'); ?></h2>
            <?php
            // Room search scoped to this property; filter chips stay on.
            // $locations / $location_id / $room_type_filter / $all_room_types /
            // $filter_options are provided by SHB_Shortcodes::location_page_content().
            include SHB_PLUGIN_DIR . 'templates/room-search.php';
            ?>
        </div>
    <?php endif; ?>
</div>
