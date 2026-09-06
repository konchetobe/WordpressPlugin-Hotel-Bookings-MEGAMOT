<div class="shb-room-list" data-testid="shb-room-list">
    <?php if (empty($rooms)) : ?>
        <div class="shb-empty-state">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
            <h3><?php _e('No rooms available', 'sanctuary-hotel-booking'); ?></h3>
            <p><?php _e('Check back soon for new listings.', 'sanctuary-hotel-booking'); ?></p>
        </div>
    <?php else : ?>
        <div class="shb-rooms-grid shb-columns-<?php echo esc_attr($atts['columns']); ?>">
            <?php foreach ($rooms as $room) : ?>
                <?php include SHB_PLUGIN_DIR . 'templates/room-card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
