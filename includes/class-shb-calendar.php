<?php
/**
 * Calendar (.ics) Generator
 */

if (!defined('ABSPATH')) {
    exit;
}

class SHB_Calendar {
    
    /**
     * Generate .ics file content for a booking
     */
    public static function generate_ics($booking_id) {
        $booking = SHB_Booking::get_booking($booking_id);
        if (!$booking) {
            return null;
        }
        
        $room = SHB_Room::get_room($booking['room_id']);
        $room_name = $room ? $room['name'] : $booking['room_name'];
        
        // Get check-in/out times
        $check_in_time = get_option('shb_check_in_time', '14:00');
        $check_out_time = get_option('shb_check_out_time', '11:00');
        
        // Build datetime strings
        $dtstart = self::format_ics_datetime($booking['check_in'], $check_in_time);
        $dtend = self::format_ics_datetime($booking['check_out'], $check_out_time);
        $dtstamp = gmdate('Ymd\THis\Z');
        
        // Build description
        $currency_symbol = get_option('shb_currency_symbol', '$');
        $description = self::build_description($booking, $room_name, $currency_symbol);
        
        // Build UID
        $uid = $booking['booking_ref'] . '@' . parse_url(home_url(), PHP_URL_HOST);
        
        // Build ICS content
        $ics = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Sanctuary Hotel Booking//EN\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= "UID:" . $uid . "\r\n";
        $ics .= "SUMMARY:Hotel Stay - " . $room_name . "\r\n";
        $ics .= "DTSTART:" . $dtstart . "\r\n";
        $ics .= "DTEND:" . $dtend . "\r\n";
        $ics .= "DTSTAMP:" . $dtstamp . "\r\n";
        $ics .= "DESCRIPTION:" . self::escape_ics_text($description) . "\r\n";
        $ics .= "LOCATION:" . self::escape_ics_text($room_name . " - Sanctuary Hotel") . "\r\n";
        $ics .= "STATUS:CONFIRMED\r\n";
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";
        
        return $ics;
    }
    
    /**
     * Format datetime for ICS
     */
    private static function format_ics_datetime($date, $time) {
        $datetime = new DateTime($date . ' ' . $time);
        return $datetime->format('Ymd\THis');
    }
    
    /**
     * Build description text
     */
    private static function build_description($booking, $room_name, $currency_symbol) {
        $lines = array(
            'Hotel Reservation Confirmation',
            '',
            'Booking Reference: ' . $booking['booking_ref'],
            'Room: ' . $room_name,
            'Check-in: ' . $booking['check_in'],
            'Check-out: ' . $booking['check_out'],
            'Guests: ' . $booking['guests'],
            'Total: ' . $currency_symbol . number_format($booking['total_price'], 2),
            '',
            'Guest: ' . $booking['first_name'] . ' ' . $booking['last_name'],
            'Email: ' . $booking['email'],
            '',
            'Special Requests: ' . ($booking['special_requests'] ?: 'None'),
        );
        
        return implode('\n', $lines);
    }
    
    /**
     * Escape text for ICS format
     */
    private static function escape_ics_text($text) {
        $text = str_replace(array("\r\n", "\r", "\n"), '\n', $text);
        $text = str_replace(array(',', ';', '\\'), array('\,', '\;', '\\\\'), $text);
        return $text;
    }
    
    /**
     * Output .ics file for download
     */
    public static function download_ics($booking_id) {
        $ics_content = self::generate_ics($booking_id);
        if (!$ics_content) {
            wp_die(__('Booking not found', 'sanctuary-hotel-booking'));
        }
        
        $booking = SHB_Booking::get_booking($booking_id);
        $filename = 'booking-' . $booking['booking_ref'] . '.ics';
        
        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($ics_content));
        header('Cache-Control: no-cache, must-revalidate');
        
        echo $ics_content;
        exit;
    }
}
