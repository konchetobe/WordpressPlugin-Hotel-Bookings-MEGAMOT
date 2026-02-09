<div class="wrap shb-admin-wrap">
    <h1><?php _e('Availability Management', 'sanctuary-hotel-booking'); ?></h1>
    
    <p><?php _e('Block dates for maintenance, private events, or other reasons.', 'sanctuary-hotel-booking'); ?></p>
    
    <button type="button" class="button button-primary" id="shb-add-block">
        <span class="dashicons dashicons-calendar-alt"></span>
        <?php _e('Block Dates', 'sanctuary-hotel-booking'); ?>
    </button>
    
    <div id="shb-block-form-modal" class="shb-modal" style="display: none;">
        <div class="shb-modal-content">
            <span class="shb-modal-close">&times;</span>
            <h2><?php _e('Block Room Availability', 'sanctuary-hotel-booking'); ?></h2>
            <form id="shb-block-form">
                <p>
                    <label for="block_room_id"><?php _e('Room', 'sanctuary-hotel-booking'); ?></label>
                    <select name="room_id" id="block_room_id" required>
                        <option value=""><?php _e('Select Room', 'sanctuary-hotel-booking'); ?></option>
                        <?php foreach ($rooms as $room) : ?>
                            <option value="<?php echo esc_attr($room['id']); ?>">
                                <?php echo esc_html($room['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </p>
                
                <p>
                    <label for="block_start_date"><?php _e('Start Date', 'sanctuary-hotel-booking'); ?></label>
                    <input type="date" name="start_date" id="block_start_date" required>
                </p>
                
                <p>
                    <label for="block_end_date"><?php _e('End Date', 'sanctuary-hotel-booking'); ?></label>
                    <input type="date" name="end_date" id="block_end_date" required>
                </p>
                
                <p>
                    <label for="block_reason"><?php _e('Reason', 'sanctuary-hotel-booking'); ?></label>
                    <select name="reason" id="block_reason">
                        <option value="maintenance"><?php _e('Maintenance', 'sanctuary-hotel-booking'); ?></option>
                        <option value="private"><?php _e('Private Event', 'sanctuary-hotel-booking'); ?></option>
                        <option value="renovation"><?php _e('Renovation', 'sanctuary-hotel-booking'); ?></option>
                        <option value="other"><?php _e('Other', 'sanctuary-hotel-booking'); ?></option>
                    </select>
                </p>
                
                <p>
                    <button type="submit" class="button button-primary"><?php _e('Block Dates', 'sanctuary-hotel-booking'); ?></button>
                </p>
            </form>
        </div>
    </div>
    
    <?php if (empty($blocks)) : ?>
        <p><?php _e('No availability blocks. All rooms are available.', 'sanctuary-hotel-booking'); ?></p>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th><?php _e('Room', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Date Range', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Reason', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Created', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Actions', 'sanctuary-hotel-booking'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($blocks as $block) : 
                    $room = SHB_Room::get_room($block['room_id']);
                ?>
                    <tr data-block-id="<?php echo esc_attr($block['id']); ?>">
                        <td><?php echo $room ? esc_html($room['name']) : __('Unknown', 'sanctuary-hotel-booking'); ?></td>
                        <td><?php echo esc_html($block['start_date'] . ' - ' . $block['end_date']); ?></td>
                        <td><?php echo esc_html(ucfirst($block['reason'])); ?></td>
                        <td><?php echo esc_html($block['created_at']); ?></td>
                        <td>
                            <button type="button" class="button button-small shb-delete-block" 
                                    data-block-id="<?php echo esc_attr($block['id']); ?>">
                                <?php _e('Remove', 'sanctuary-hotel-booking'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
