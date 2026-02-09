# Function Signatures (Skeleton)

This file contains only function signatures/interfaces - no implementation details.
Use this to understand how to call functions without reading full source code.

---

## SHB_Room (includes/class-shb-room.php)

```php
class SHB_Room {
    // Get single room by ID
    public static function get_room(int $room_id): ?array
    
    // Get all rooms
    public static function get_rooms(array $args = []): array
    
    // Create new room
    public static function create_room(array $data): int|WP_Error
    
    // Update room
    public static function update_room(int $room_id, array $data): bool|WP_Error
    
    // Delete room
    public static function delete_room(int $room_id): bool
    
    // Format room data from post
    public static function format_room(WP_Post|int $post): array
}
```

**Room Array Structure:**
```php
[
    'id' => int,
    'name' => string,
    'description' => string,
    'excerpt' => string,
    'image' => string (URL),
    'gallery' => array,
    'room_type' => string,
    'max_guests' => int,
    'base_price' => float,
    'size' => int,
    'amenities' => array,
]
```

---

## SHB_Booking (includes/class-shb-booking.php)

```php
class SHB_Booking {
    // Create new booking
    public static function create_booking(array $data): array|WP_Error
    // Returns: ['booking_id' => int, 'booking_ref' => string, 'total_price' => float]
    
    // Get booking by ID
    public static function get_booking(int $booking_id): ?array
    
    // Get booking by reference
    public static function get_booking_by_ref(string $booking_ref): ?array
    
    // Get bookings list
    public static function get_bookings(array $args = []): array
    
    // Update booking status
    public static function update_status(int $booking_id, string $status): bool
    // Status: pending, confirmed, cancelled, checked_in, checked_out
    
    // Update payment status
    public static function update_payment_status(int $booking_id, string $status): bool
    // Status: pending, paid, refunded
    
    // Format booking data
    public static function format_booking(WP_Post|int $post): array
    
    // Send confirmation email
    public static function send_confirmation_email(int $booking_id): bool
}
```

**Booking Data Input:**
```php
[
    'room_id' => int,
    'check_in' => string (Y-m-d),
    'check_out' => string (Y-m-d),
    'guests' => int,
    'first_name' => string,
    'last_name' => string,
    'email' => string,
    'phone' => string,
    'special_requests' => string,
    'payment_method' => string,
]
```

---

## SHB_Availability (includes/class-shb-availability.php)

```php
class SHB_Availability {
    // Check if room is available for dates
    public static function check_room_availability(
        int $room_id, 
        string $check_in, 
        string $check_out
    ): bool
    
    // Get all available rooms for dates
    public static function get_available_rooms(
        string $check_in, 
        string $check_out, 
        int $guests = 1
    ): array
    
    // Create availability block (closure/maintenance)
    public static function create_block(array $data): int|WP_Error
    // Data: room_id, start_date, end_date, reason
    
    // Delete availability block
    public static function delete_block(int $block_id): bool
    
    // Get blocks for date range
    public static function get_blocks(
        string $start_date, 
        string $end_date, 
        int $room_id = 0
    ): array
}
```

---

## SHB_Pricing (includes/class-shb-pricing.php)

```php
class SHB_Pricing {
    // Calculate total price for stay
    public static function calculate_total_price(
        int $room_id, 
        string $check_in, 
        string $check_out
    ): float
    
    // Get price per night for date
    public static function get_nightly_price(
        int $room_id, 
        string $date
    ): float
    
    // Get price breakdown
    public static function get_price_breakdown(
        int $room_id, 
        string $check_in, 
        string $check_out
    ): array
    // Returns: ['nights' => int, 'per_night' => float, 'subtotal' => float, 'total' => float]
}
```

---

## SHB_Payments (includes/class-shb-payments.php)

```php
class SHB_Payments {
    // Create Stripe checkout session
    public static function create_stripe_checkout(int $booking_id): array|WP_Error
    // Returns: ['checkout_url' => string, 'session_id' => string]
    
    // Verify Stripe payment
    public static function verify_stripe_payment(string $session_id): array|WP_Error
    
    // Handle successful payment
    public static function handle_payment_success(int $booking_id): bool
    
    // Get Stripe secret key (respects test mode)
    private static function get_stripe_secret_key(): string
}
```

---

## SHB_Ajax (includes/class-shb-ajax.php)

```php
class SHB_Ajax {
    // All methods are static and called via WordPress AJAX hooks
    
    // PUBLIC ENDPOINTS (no login required)
    public static function search_rooms(): void      // action: shb_search_rooms
    public static function check_availability(): void // action: shb_check_availability
    public static function get_room_prices(): void   // action: shb_get_room_prices
    public static function create_booking(): void    // action: shb_create_booking
    public static function process_payment(): void   // action: shb_process_payment
    public static function get_blocked_dates(): void // action: shb_get_blocked_dates
    
    // ADMIN ENDPOINTS (requires manage_options)
    public static function admin_get_booking(): void      // action: shb_get_booking
    public static function admin_update_status(): void    // action: shb_update_booking_status
    public static function admin_create_room(): void      // action: shb_create_room
    public static function admin_delete_room(): void      // action: shb_delete_room
    public static function get_bookings_calendar(): void  // action: shb_get_bookings_calendar
}
```

**Standard AJAX Response:**
```php
// Success
wp_send_json_success(['key' => 'value']);
// Returns: {"success": true, "data": {"key": "value"}}

// Error
wp_send_json_error(['message' => 'Error description']);
// Returns: {"success": false, "data": {"message": "Error description"}}
```

---

## SHB_Shortcodes (includes/class-shb-shortcodes.php)

```php
class SHB_Shortcodes {
    // Room search with results
    public static function room_search(array $atts): string
    // Shortcode: [shb_room_search]
    
    // Room list display
    public static function room_list(array $atts): string
    // Shortcode: [shb_room_list columns="3" show_search="true"]
    
    // Booking form
    public static function booking_form(array $atts): string
    // Shortcode: [shb_booking_form room_id="123"]
    // Required: room_id via attribute or URL param
    
    // Booking confirmation
    public static function booking_confirmation(array $atts): string
    // Shortcode: [shb_booking_confirmation]
    // Reads: booking_ref from URL param
    
    // Customer booking lookup
    public static function my_bookings(array $atts): string
    // Shortcode: [shb_my_bookings]
}
```

---

## SHB_Emails (includes/class-shb-emails.php)

```php
class SHB_Emails {
    // Send booking confirmation to guest
    public static function send_booking_confirmation(int $booking_id): bool
    
    // Send notification to admin
    public static function send_admin_notification(int $booking_id): bool
    
    // Send cancellation notice
    public static function send_cancellation_notice(int $booking_id): bool
    
    // Generate ICS calendar attachment
    public static function generate_ics(int $booking_id): string
}
```

---

## WordPress Options (Settings)

```php
// Get setting with default
get_option('shb_currency', 'EUR');
get_option('shb_stripe_enabled', '1');
get_option('shb_bank_transfer_enabled', '0');

// All settings use 'shb_' prefix
// See CLAUDE.md for full settings list
```
