<?php
/**
 * Rich public single-room page content.
 *
 * Variables:
 *   $room        Formatted room array (SHB_Room::format_room).
 *   $gallery_ids Attachment IDs for the gallery (may be empty).
 *   $currency    Currency symbol for this room's location.
 */
if (!defined('ABSPATH')) {
    exit;
}

$currency = !empty($currency) ? $currency : (!empty($room['location']['currency_symbol']) ? $room['location']['currency_symbol'] : get_option('shb_currency_symbol', '$'));
$gallery_ids = isset($gallery_ids) ? $gallery_ids : array();

// Order amenity chips using the same groups as the editor.
$amenity_group_order = array(
    __('Room Basics', 'sanctuary-hotel-booking') => array('WiFi', 'TV', 'Smart TV', 'Air Conditioning', 'Heating', 'Ceiling Fan'),
    __('Bathroom', 'sanctuary-hotel-booking') => array('Private Bathroom', 'Bathtub', 'Shower', 'Jacuzzi', 'Hairdryer', 'Toiletries'),
    __('Kitchen & Dining', 'sanctuary-hotel-booking') => array('Coffee Maker', 'Mini Fridge', 'Mini Bar', 'Full Kitchen', 'Microwave', 'Kettle'),
    __('Comfort & Work', 'sanctuary-hotel-booking') => array('Workspace', 'Safe', 'Iron', 'Wardrobe', 'Sofa', 'Fireplace'),
    __('Views & Outdoor', 'sanctuary-hotel-booking') => array('Balcony', 'Terrace', 'Ocean View', 'Mountain View', 'Garden View', 'City View'),
    __('Services & Facilities', 'sanctuary-hotel-booking') => array('Room Service', 'Breakfast Included', 'Pool Access', 'Gym Access', 'Spa Access', 'Parking'),
);
$amenity_groups = array();
$custom_amenities = array();
if (!empty($room['amenities'])) {
    foreach ($room['amenities'] as $amenity) {
        $found = false;
        foreach ($amenity_group_order as $group_name => $group_amenities) {
            if (in_array($amenity, $group_amenities, true)) {
                $amenity_groups[$group_name][] = $amenity;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $custom_amenities[] = $amenity;
        }
    }
}
$all_images = array();
if (!empty($room['image']) && strpos($room['image'], 'default-room.jpg') === false) {
    $all_images[] = $room['image'];
}
foreach ($gallery_ids as $gid) {
    $url = wp_get_attachment_image_url($gid, 'large');
    if ($url) {
        $all_images[] = $url;
    }
}
?>

<div class="shb-room-page" data-testid="shb-room-page">

    <?php if (!empty($all_images)): ?>
        <div class="shb-room-gallery" data-testid="shb-room-gallery">
            <div class="shb-room-gallery-main">
                <img src="<?php echo esc_url($all_images[0]); ?>" alt="<?php echo esc_attr($room['name']); ?>" id="shb-room-gallery-main-img">
            </div>
            <?php if (count($all_images) > 1): ?>
                <div class="shb-room-gallery-thumbs">
                    <?php foreach ($all_images as $index => $img): ?>
                        <button type="button" class="shb-gallery-thumb<?php echo $index === 0 ? ' active' : ''; ?>" data-src="<?php echo esc_url($img); ?>">
                            <img src="<?php echo esc_url($img); ?>" alt="" loading="lazy">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($room['description'])): ?>
        <div class="shb-room-section shb-room-description" data-testid="shb-room-description">
            <h2><?php _e('About this room', 'sanctuary-hotel-booking'); ?></h2>
            <div class="shb-room-description-content"><?php echo wp_kses_post($room['description']); ?></div>
        </div>
    <?php endif; ?>

    <div class="shb-room-section shb-room-specs">
        <h2><?php _e('At a glance', 'sanctuary-hotel-booking'); ?></h2>
        <div class="shb-room-specs-grid">
            <?php if (!empty($room['max_guests'])): ?>
                <div class="shb-room-spec-item">
                    <span class="shb-spec-icon">&#128101;</span>
                    <span class="shb-spec-label"><?php _e('Guests', 'sanctuary-hotel-booking'); ?></span>
                    <span class="shb-spec-value"><?php echo esc_html($room['max_guests']); ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($room['bed_type'])): ?>
                <div class="shb-room-spec-item">
                    <span class="shb-spec-icon">&#128716;</span>
                    <span class="shb-spec-label"><?php _e('Bed', 'sanctuary-hotel-booking'); ?></span>
                    <span class="shb-spec-value"><?php echo esc_html(ucfirst(str_replace('_', ' ', $room['bed_type']))); ?></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($room['room_size'])): ?>
                <div class="shb-room-spec-item">
                    <span class="shb-spec-icon">&#128207;</span>
                    <span class="shb-spec-label"><?php _e('Size', 'sanctuary-hotel-booking'); ?></span>
                    <span class="shb-spec-value"><?php echo esc_html($room['room_size']); ?> m&sup2;</span>
                </div>
            <?php endif; ?>
            <?php if (isset($room['floor']) && $room['floor'] !== ''): ?>
                <div class="shb-room-spec-item">
                    <span class="shb-spec-icon">&#127963;</span>
                    <span class="shb-spec-label"><?php _e('Floor', 'sanctuary-hotel-booking'); ?></span>
                    <span class="shb-spec-value"><?php echo esc_html($room['floor']); ?></span>
                </div>
            <?php endif; ?>
            <div class="shb-room-spec-item">
                <span class="shb-spec-icon">&#128176;</span>
                <span class="shb-spec-label"><?php _e('From', 'sanctuary-hotel-booking'); ?></span>
                <span class="shb-spec-value"><?php echo esc_html($currency . number_format($room['base_price'], 0)); ?>/<?php _e('night', 'sanctuary-hotel-booking'); ?></span>
            </div>
            <?php if (!empty($room['cancellation_policy'])): ?>
                <div class="shb-room-spec-item">
                    <span class="shb-spec-icon">&#128203;</span>
                    <span class="shb-spec-label"><?php _e('Cancellation', 'sanctuary-hotel-booking'); ?></span>
                    <span class="shb-spec-value"><?php echo esc_html(ucfirst(str_replace('_', ' ', $room['cancellation_policy']))); ?></span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($amenity_groups) || !empty($custom_amenities)): ?>
        <div class="shb-room-section shb-room-amenities" data-testid="shb-room-amenities">
            <h2><?php _e('Amenities', 'sanctuary-hotel-booking'); ?></h2>
            <?php foreach ($amenity_groups as $group_name => $group_amenities): ?>
                <div class="shb-amenity-block">
                    <h4><?php echo esc_html($group_name); ?></h4>
                    <div class="shb-amenity-tags">
                        <?php foreach ($group_amenities as $amenity): ?>
                            <span class="shb-amenity-tag"><?php echo esc_html($amenity); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (!empty($custom_amenities)): ?>
                <div class="shb-amenity-block">
                    <h4><?php _e('More features', 'sanctuary-hotel-booking'); ?></h4>
                    <div class="shb-amenity-tags">
                        <?php foreach ($custom_amenities as $amenity): ?>
                            <span class="shb-amenity-tag"><?php echo esc_html($amenity); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($room['location'])): ?>
        <div class="shb-room-section shb-room-location-card" data-testid="shb-room-location">
            <h2><?php _e('The property', 'sanctuary-hotel-booking'); ?></h2>
            <?php $loc = $room['location']; ?>
            <?php if (!empty($loc['image'])): ?>
                <img src="<?php echo esc_url($loc['image']); ?>" alt="<?php echo esc_attr($loc['name']); ?>" class="shb-room-location-img">
            <?php endif; ?>
            <div class="shb-room-location-info">
                <h3>
                    <?php if (!empty($loc['permalink'])): ?>
                        <a href="<?php echo esc_url($loc['permalink']); ?>"><?php echo esc_html($loc['name']); ?></a>
                    <?php else: ?>
                        <?php echo esc_html($loc['name']); ?>
                    <?php endif; ?>
                </h3>
                <?php if (!empty($loc['address']) || !empty($loc['city']) || !empty($loc['country'])): ?>
                    <p class="shb-room-location-address">
                        <?php
                        $addr = trim(($loc['address'] ?? '') . ', ' . trim(($loc['city'] ?? '') . ', ' . ($loc['country'] ?? ''), ', '), ', ');
                        echo esc_html($addr);
                        ?>
                    </p>
                <?php endif; ?>
                <?php if (!empty($loc['phone']) || !empty($loc['email'])): ?>
                    <p class="shb-room-location-contact">
                        <?php if (!empty($loc['phone'])): ?>
                            <span><?php echo esc_html($loc['phone']); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($loc['email'])): ?>
                            <a href="mailto:<?php echo esc_attr($loc['email']); ?>"><?php echo esc_html($loc['email']); ?></a>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
                <p class="shb-room-location-times">
                    <?php _e('Check-in', 'sanctuary-hotel-booking'); ?> <?php echo esc_html($loc['check_in_time']); ?>
                    &middot;
                    <?php _e('Check-out', 'sanctuary-hotel-booking'); ?> <?php echo esc_html($loc['check_out_time']); ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <div class="shb-room-section shb-room-booking-section" data-testid="shb-room-booking">
        <h2><?php _e('Book this room', 'sanctuary-hotel-booking'); ?></h2>
        <?php echo do_shortcode('[shb_booking_form room_id="' . absint($room['id']) . '"]'); ?>
    </div>
</div>
