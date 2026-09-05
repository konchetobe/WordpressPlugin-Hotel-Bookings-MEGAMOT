<div class="wrap shb-admin-wrap">
    <h1><?php _e('Reports', 'sanctuary-hotel-booking'); ?></h1>

    <form method="get" action="" class="shb-filter-inline" style="margin-bottom:20px;">
        <input type="hidden" name="page" value="shb-reports">
        <label for="start_date"><?php _e('From', 'sanctuary-hotel-booking'); ?></label>
        <input type="date" name="start_date" id="start_date" value="<?php echo esc_attr($start_date); ?>">
        <label for="end_date"><?php _e('To', 'sanctuary-hotel-booking'); ?></label>
        <input type="date" name="end_date" id="end_date" value="<?php echo esc_attr($end_date); ?>">
        <select name="location">
            <option value="0"><?php _e('All Locations', 'sanctuary-hotel-booking'); ?></option>
            <?php foreach ($locations as $loc): ?>
                <option value="<?php echo esc_attr($loc['id']); ?>" <?php selected($location_filter, $loc['id']); ?>>
                    <?php echo esc_html($loc['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <input type="submit" class="button" value="<?php _e('Run Report', 'sanctuary-hotel-booking'); ?>">
    </form>

    <?php if (empty($locations)): ?>
        <p><?php _e('Create a location before viewing reports.', 'sanctuary-hotel-booking'); ?></p>
        <?php return; ?>
    <?php endif; ?>

    <h2><?php _e('Occupancy', 'sanctuary-hotel-booking'); ?></h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Location', 'sanctuary-hotel-booking'); ?></th>
                <th><?php _e('Room', 'sanctuary-hotel-booking'); ?></th>
                <th><?php _e('Nights Sold', 'sanctuary-hotel-booking'); ?></th>
                <th><?php _e('Occupancy Rate', 'sanctuary-hotel-booking'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $any_rows = false;
            foreach ($locations as $loc):
                if ($location_filter && $location_filter !== $loc['id']) {
                    continue;
                }
                $loc_rooms = array_filter($room_map, function ($room) use ($loc) {
                    return (int) $room['location_id'] === (int) $loc['id'];
                });
                foreach ($loc_rooms as $room):
                    $any_rows = true;
                    $key = $loc['id'] . ':' . $room['id'];
                    $sold = $occupancy_by_room[$key] ?? 0;
                    $available = $days_in_range;
                    $rate = $available > 0 ? round($sold / $available * 100, 1) : 0;
                    ?>
                    <tr>
                        <td><?php echo esc_html($loc['name']); ?></td>
                        <td><?php echo esc_html($room['name']); ?></td>
                        <td><?php echo esc_html($sold); ?></td>
                        <td><?php echo esc_html($rate . '%'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
            <?php if (!$any_rows): ?>
                <tr>
                    <td colspan="4"><?php _e('No rooms in the selected range.', 'sanctuary-hotel-booking'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <h2 style="margin-top:32px;"><?php _e('Revenue', 'sanctuary-hotel-booking'); ?></h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Location', 'sanctuary-hotel-booking'); ?></th>
                <th><?php _e('Paid Bookings', 'sanctuary-hotel-booking'); ?></th>
                <th><?php _e('Revenue', 'sanctuary-hotel-booking'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total_revenue = 0;
            $any_revenue = false;
            foreach ($locations as $loc):
                if ($location_filter && $location_filter !== $loc['id']) {
                    continue;
                }
                $revenue = $revenue_by_location[$loc['id']] ?? 0;
                $total_revenue += $revenue;
                $any_revenue = true;
                $symbol = $loc['currency_symbol'];
                ?>
                <tr>
                    <td><?php echo esc_html($loc['name']); ?></td>
                    <td><?php echo esc_html($count_by_location[$loc['id']] ?? 0); ?></td>
                    <td><?php echo esc_html($symbol . number_format($revenue, 2)); ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if ($any_revenue): ?>
                <tr>
                    <th><?php _e('Total', 'sanctuary-hotel-booking'); ?></th>
                    <th></th>
                    <th>
                        <?php
                        // The total mixes locations; show the viewer's default symbol.
                        $total_symbol = !empty($locations[0]['currency_symbol']) ? $locations[0]['currency_symbol'] : get_option('shb_currency_symbol', '$');
                        echo esc_html($total_symbol . number_format($total_revenue, 2));
                        ?>
                    </th>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
