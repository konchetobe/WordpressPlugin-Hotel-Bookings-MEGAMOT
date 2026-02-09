<div class="wrap shb-admin-wrap">
    <h1><?php _e('Bookings', 'sanctuary-hotel-booking'); ?></h1>
    
    <!-- View Toggle & Filters -->
    <div class="shb-bookings-toolbar">
        <div class="shb-view-toggle">
            <a href="<?php echo esc_url(add_query_arg('view', 'list', remove_query_arg('view'))); ?>" 
               class="button <?php echo $view !== 'calendar' ? 'button-primary' : ''; ?>">
                <span class="dashicons dashicons-list-view" style="vertical-align:middle;margin-top:-2px"></span>
                <?php _e('List', 'sanctuary-hotel-booking'); ?>
            </a>
            <a href="<?php echo esc_url(add_query_arg('view', 'calendar')); ?>" 
               class="button <?php echo $view === 'calendar' ? 'button-primary' : ''; ?>">
                <span class="dashicons dashicons-calendar-alt" style="vertical-align:middle;margin-top:-2px"></span>
                <?php _e('Calendar', 'sanctuary-hotel-booking'); ?>
            </a>
        </div>
        
        <?php if ($view !== 'calendar') : ?>
        <form method="get" action="" class="shb-filter-inline">
            <input type="hidden" name="page" value="shb-bookings">
            <select name="status">
                <option value=""><?php _e('All Statuses', 'sanctuary-hotel-booking'); ?></option>
                <option value="pending" <?php selected($status_filter, 'pending'); ?>><?php _e('Pending', 'sanctuary-hotel-booking'); ?></option>
                <option value="confirmed" <?php selected($status_filter, 'confirmed'); ?>><?php _e('Confirmed', 'sanctuary-hotel-booking'); ?></option>
                <option value="checked_in" <?php selected($status_filter, 'checked_in'); ?>><?php _e('Checked In', 'sanctuary-hotel-booking'); ?></option>
                <option value="checked_out" <?php selected($status_filter, 'checked_out'); ?>><?php _e('Checked Out', 'sanctuary-hotel-booking'); ?></option>
                <option value="cancelled" <?php selected($status_filter, 'cancelled'); ?>><?php _e('Cancelled', 'sanctuary-hotel-booking'); ?></option>
            </select>
            <input type="submit" class="button" value="<?php _e('Filter', 'sanctuary-hotel-booking'); ?>">
        </form>
        <?php endif; ?>
    </div>
    
    <?php if ($view === 'calendar') : ?>
    <!-- Calendar View -->
    <div class="shb-calendar-container" id="shb-calendar-container">
        <div class="shb-calendar-controls">
            <div class="shb-calendar-nav">
                <button type="button" class="button" id="shb-cal-prev">&laquo; <?php _e('Prev', 'sanctuary-hotel-booking'); ?></button>
                <h2 id="shb-cal-title" style="margin:0 16px;font-size:18px"></h2>
                <button type="button" class="button" id="shb-cal-next"><?php _e('Next', 'sanctuary-hotel-booking'); ?> &raquo;</button>
            </div>
            <div class="shb-calendar-filter">
                <select id="shb-cal-room-filter">
                    <option value="all"><?php _e('All Rooms', 'sanctuary-hotel-booking'); ?></option>
                    <?php foreach ($rooms as $room) : ?>
                        <option value="<?php echo esc_attr($room['id']); ?>">
                            <?php echo esc_html($room['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <!-- Room Legend -->
        <div class="shb-calendar-legend" id="shb-calendar-legend">
            <?php foreach ($rooms as $room) : ?>
                <span class="shb-legend-item" data-room-id="<?php echo esc_attr($room['id']); ?>">
                    <span class="shb-legend-dot" style="background:<?php echo esc_attr($room_colors[$room['id']]); ?>"></span>
                    <?php echo esc_html($room['name']); ?>
                </span>
            <?php endforeach; ?>
        </div>
        
        <div class="shb-calendar-grid" id="shb-calendar-grid"></div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        var currentYear = new Date().getFullYear();
        var currentMonth = new Date().getMonth();
        var allBookings = <?php echo json_encode(array_map(function($b) use ($room_colors) {
            return array(
                'id' => $b['id'],
                'ref' => $b['booking_ref'],
                'room_id' => $b['room_id'],
                'room_name' => $b['room_name'],
                'guest' => $b['first_name'] . ' ' . $b['last_name'],
                'check_in' => $b['check_in'],
                'check_out' => $b['check_out'],
                'status' => $b['booking_status'],
                'color' => isset($room_colors[$b['room_id']]) ? $room_colors[$b['room_id']] : '#94a3b8'
            );
        }, $bookings)); ?>;
        var monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        var dayNames = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
        
        function renderCalendar(year, month, roomFilter) {
            var firstDay = new Date(year, month, 1);
            var lastDay = new Date(year, month + 1, 0);
            var startDow = (firstDay.getDay() + 6) % 7; // Monday = 0
            var daysInMonth = lastDay.getDate();
            
            $('#shb-cal-title').text(monthNames[month] + ' ' + year);
            
            // Filter bookings
            var filtered = allBookings;
            if (roomFilter && roomFilter !== 'all') {
                filtered = allBookings.filter(function(b) { return b.room_id == roomFilter; });
            }
            
            // Update legend visibility
            if (roomFilter && roomFilter !== 'all') {
                $('.shb-legend-item').hide();
                $('.shb-legend-item[data-room-id="' + roomFilter + '"]').show();
            } else {
                $('.shb-legend-item').show();
            }
            
            var html = '<div class="shb-cal-header">';
            for (var d = 0; d < 7; d++) {
                html += '<div class="shb-cal-day-name">' + dayNames[d] + '</div>';
            }
            html += '</div><div class="shb-cal-body">';
            
            // Empty cells before first day
            for (var e = 0; e < startDow; e++) {
                html += '<div class="shb-cal-cell shb-cal-empty"></div>';
            }
            
            for (var day = 1; day <= daysInMonth; day++) {
                var dateStr = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
                var isToday = (dateStr === new Date().toISOString().split('T')[0]);
                
                html += '<div class="shb-cal-cell' + (isToday ? ' shb-cal-today' : '') + '" data-date="' + dateStr + '">';
                html += '<span class="shb-cal-date">' + day + '</span>';
                
                // Find bookings that overlap this day
                var dayBookings = filtered.filter(function(b) {
                    return dateStr >= b.check_in && dateStr < b.check_out;
                });
                
                if (dayBookings.length > 0) {
                    html += '<div class="shb-cal-events">';
                    for (var i = 0; i < Math.min(dayBookings.length, 3); i++) {
                        var b = dayBookings[i];
                        var isStart = (dateStr === b.check_in);
                        var label = isStart ? b.guest : '';
                        html += '<div class="shb-cal-event shb-cal-status-' + b.status + '" style="background:' + b.color + '" title="' + b.guest + ' (' + b.ref + ') - ' + b.room_name + ' [' + b.check_in + ' to ' + b.check_out + ']">';
                        html += label;
                        html += '</div>';
                    }
                    if (dayBookings.length > 3) {
                        html += '<div class="shb-cal-more">+' + (dayBookings.length - 3) + '</div>';
                    }
                    html += '</div>';
                }
                html += '</div>';
            }
            
            // Pad remaining cells
            var totalCells = startDow + daysInMonth;
            var remainder = totalCells % 7;
            if (remainder > 0) {
                for (var p = 0; p < (7 - remainder); p++) {
                    html += '<div class="shb-cal-cell shb-cal-empty"></div>';
                }
            }
            
            html += '</div>';
            $('#shb-calendar-grid').html(html);
        }
        
        renderCalendar(currentYear, currentMonth, 'all');
        
        $('#shb-cal-prev').on('click', function() {
            currentMonth--;
            if (currentMonth < 0) { currentMonth = 11; currentYear--; }
            renderCalendar(currentYear, currentMonth, $('#shb-cal-room-filter').val());
        });
        
        $('#shb-cal-next').on('click', function() {
            currentMonth++;
            if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            renderCalendar(currentYear, currentMonth, $('#shb-cal-room-filter').val());
        });
        
        $('#shb-cal-room-filter').on('change', function() {
            renderCalendar(currentYear, currentMonth, $(this).val());
        });
    });
    </script>
    
    <?php else : ?>
    <!-- List View -->
    <?php if (empty($bookings)) : ?>
        <p><?php _e('No bookings found.', 'sanctuary-hotel-booking'); ?></p>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width:30px"></th>
                    <th><?php _e('Reference', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Guest', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Room', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Dates', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Guests', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Total', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Payment', 'sanctuary-hotel-booking'); ?></th>
                    <th><?php _e('Status', 'sanctuary-hotel-booking'); ?></th>
                    <th style="width: 180px;"><?php _e('Actions', 'sanctuary-hotel-booking'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking) : 
                    $bcolor = isset($room_colors[$booking['room_id']]) ? $room_colors[$booking['room_id']] : '#94a3b8';
                ?>
                    <tr data-booking-id="<?php echo esc_attr($booking['id']); ?>">
                        <td><span style="display:inline-block;width:12px;height:12px;border-radius:3px;background:<?php echo esc_attr($bcolor); ?>"></span></td>
                        <td><strong><?php echo esc_html($booking['booking_ref']); ?></strong></td>
                        <td>
                            <?php echo esc_html($booking['first_name'] . ' ' . $booking['last_name']); ?><br>
                            <small><?php echo esc_html($booking['email']); ?></small>
                        </td>
                        <td><?php echo esc_html($booking['room_name']); ?></td>
                        <td>
                            <?php echo esc_html($booking['check_in']); ?><br>
                            <?php echo esc_html($booking['check_out']); ?>
                        </td>
                        <td><?php echo esc_html($booking['guests']); ?></td>
                        <td><?php echo esc_html(get_option('shb_currency_symbol', '$') . number_format($booking['total_price'], 2)); ?></td>
                        <td>
                            <span class="shb-status shb-status-<?php echo esc_attr($booking['payment_status']); ?>">
                                <?php echo esc_html(ucfirst($booking['payment_status'])); ?>
                            </span>
                        </td>
                        <td>
                            <select class="shb-status-select" data-booking-id="<?php echo esc_attr($booking['id']); ?>">
                                <option value="pending" <?php selected($booking['booking_status'], 'pending'); ?>><?php _e('Pending', 'sanctuary-hotel-booking'); ?></option>
                                <option value="confirmed" <?php selected($booking['booking_status'], 'confirmed'); ?>><?php _e('Confirmed', 'sanctuary-hotel-booking'); ?></option>
                                <option value="checked_in" <?php selected($booking['booking_status'], 'checked_in'); ?>><?php _e('Checked In', 'sanctuary-hotel-booking'); ?></option>
                                <option value="checked_out" <?php selected($booking['booking_status'], 'checked_out'); ?>><?php _e('Checked Out', 'sanctuary-hotel-booking'); ?></option>
                                <option value="cancelled" <?php selected($booking['booking_status'], 'cancelled'); ?>><?php _e('Cancelled', 'sanctuary-hotel-booking'); ?></option>
                            </select>
                        </td>
                        <td>
                            <div class="shb-action-group">
                                <button type="button" class="button button-small shb-send-email" 
                                        data-booking-id="<?php echo esc_attr($booking['id']); ?>"
                                        title="<?php _e('Send/Resend booking details via email', 'sanctuary-hotel-booking'); ?>">
                                    <span class="dashicons dashicons-email-alt" style="font-size:14px;width:14px;height:14px;vertical-align:middle"></span>
                                    <?php _e('Email', 'sanctuary-hotel-booking'); ?>
                                </button>
                                <a href="<?php echo esc_url(add_query_arg('shb_download_calendar', $booking['id'], home_url())); ?>" 
                                   class="button button-small" title="<?php _e('Download Calendar', 'sanctuary-hotel-booking'); ?>">
                                    <span class="dashicons dashicons-calendar" style="font-size:14px;width:14px;height:14px;vertical-align:middle"></span>
                                    <?php _e('.ics', 'sanctuary-hotel-booking'); ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <?php endif; ?>
</div>
