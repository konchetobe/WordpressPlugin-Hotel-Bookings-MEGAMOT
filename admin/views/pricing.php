<div class="wrap shb-admin-wrap">
    <h1><?php _e('Pricing Rules', 'sanctuary-hotel-booking'); ?></h1>
    
    <p><?php _e('Create pricing rules for seasonal rates, weekends, and special offers.', 'sanctuary-hotel-booking'); ?></p>
    
    <button type="button" class="button button-primary" id="shb-add-rule">
        <span class="dashicons dashicons-plus-alt"></span>
        <?php _e('Add New Rule', 'sanctuary-hotel-booking'); ?>
    </button>
    
    <div id="shb-rule-form-modal" class="shb-modal" style="display: none;">
        <div class="shb-modal-content">
            <span class="shb-modal-close">&times;</span>
            <h2 id="shb-rule-form-title"><?php _e('Add Pricing Rule', 'sanctuary-hotel-booking'); ?></h2>
            <form id="shb-rule-form">
                <input type="hidden" name="rule_id" id="rule_id" value="">
                
                <p>
                    <label for="rule_name"><?php _e('Rule Name', 'sanctuary-hotel-booking'); ?></label>
                    <input type="text" name="name" id="rule_name" required class="regular-text">
                </p>
                
                <p>
                    <label for="rule_type"><?php _e('Rule Type', 'sanctuary-hotel-booking'); ?></label>
                    <select name="rule_type" id="rule_type" required>
                        <option value="seasonal"><?php _e('Seasonal', 'sanctuary-hotel-booking'); ?></option>
                        <option value="weekend"><?php _e('Weekend', 'sanctuary-hotel-booking'); ?></option>
                        <option value="early_bird"><?php _e('Early Bird', 'sanctuary-hotel-booking'); ?></option>
                        <option value="last_minute"><?php _e('Last Minute', 'sanctuary-hotel-booking'); ?></option>
                    </select>
                </p>
                
                <p>
                    <label for="rule_room_type"><?php _e('Room Type (optional)', 'sanctuary-hotel-booking'); ?></label>
                    <select name="room_type" id="rule_room_type">
                        <option value=""><?php _e('All Rooms', 'sanctuary-hotel-booking'); ?></option>
                        <option value="standard"><?php _e('Standard', 'sanctuary-hotel-booking'); ?></option>
                        <option value="deluxe"><?php _e('Deluxe', 'sanctuary-hotel-booking'); ?></option>
                        <option value="suite"><?php _e('Suite', 'sanctuary-hotel-booking'); ?></option>
                    </select>
                </p>
                
                <p>
                    <label for="rule_start_date"><?php _e('Start Date (optional)', 'sanctuary-hotel-booking'); ?></label>
                    <input type="date" name="start_date" id="rule_start_date">
                </p>
                
                <p>
                    <label for="rule_end_date"><?php _e('End Date (optional)', 'sanctuary-hotel-booking'); ?></label>
                    <input type="date" name="end_date" id="rule_end_date">
                </p>
                
                <p>
                    <label for="rule_multiplier"><?php _e('Price Multiplier', 'sanctuary-hotel-booking'); ?></label>
                    <input type="number" name="multiplier" id="rule_multiplier" step="0.01" min="0.1" max="5" value="1.00" required>
                    <span class="description"><?php _e('1.0 = no change, 1.2 = +20%, 0.8 = -20%', 'sanctuary-hotel-booking'); ?></span>
                </p>
                
                <p>
                    <label for="rule_is_active">
                        <input type="checkbox" name="is_active" id="rule_is_active" value="1" checked>
                        <?php _e('Active', 'sanctuary-hotel-booking'); ?>
                    </label>
                </p>
                
                <p>
                    <button type="submit" class="button button-primary"><?php _e('Save Rule', 'sanctuary-hotel-booking'); ?></button>
                </p>
            </form>
        </div>
    </div>
    
    <?php if (empty($rules)) : ?>
        <p><?php _e('No pricing rules yet.', 'sanctuary-hotel-booking'); ?></p>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
            <thead>
                <tr>
                    <th><?php _e('Name', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Type', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Room Type', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Date Range', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Multiplier', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Status', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Actions', 'sanctuary-hotel-booking'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rules as $rule) : ?>
                    <tr data-rule-id="<?php echo esc_attr($rule['id']); ?>">
                        <td><strong><?php echo esc_html($rule['name']); ?></strong></td>
                        <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $rule['rule_type']))); ?></td>
                        <td><?php echo $rule['room_type'] ? esc_html(ucfirst($rule['room_type'])) : __('All', 'sanctuary-hotel-booking'); ?></td>
                        <td>
                            <?php if ($rule['start_date'] && $rule['end_date']) : ?>
                                <?php echo esc_html($rule['start_date'] . ' - ' . $rule['end_date']); ?>
                            <?php else : ?>
                                <?php _e('Always', 'sanctuary-hotel-booking'); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $multiplier = floatval($rule['multiplier']);
                            $percentage = ($multiplier - 1) * 100;
                            $class = $percentage >= 0 ? 'shb-increase' : 'shb-decrease';
                            $sign = $percentage >= 0 ? '+' : '';
                            echo '<span class="' . esc_attr($class) . '">' . $sign . number_format($percentage, 0) . '%</span>';
                            ?>
                        </td>
                        <td>
                            <span class="shb-status shb-status-<?php echo $rule['is_active'] ? 'active' : 'inactive'; ?>">
                                <?php echo $rule['is_active'] ? __('Active', 'sanctuary-hotel-booking') : __('Inactive', 'sanctuary-hotel-booking'); ?>
                            </span>
                        </td>
                        <td>
                            <button type="button" class="button button-small shb-edit-rule" 
                                    data-rule='<?php echo esc_attr(json_encode($rule)); ?>'>
                                <?php _e('Edit', 'sanctuary-hotel-booking'); ?>
                            </button>
                            <button type="button" class="button button-small shb-delete-rule" 
                                    data-rule-id="<?php echo esc_attr($rule['id']); ?>">
                                <?php _e('Delete', 'sanctuary-hotel-booking'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
