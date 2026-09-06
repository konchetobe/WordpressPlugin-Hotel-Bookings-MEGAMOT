# Function Signatures (Skeleton)

This file contains only function signatures/interfaces - no implementation details.
Use this to understand how to call functions without reading full source code.

---

## SHB_Room_Type (includes/class-shb-room-type.php)

```php
class SHB_Room_Type {
    // Term meta keys that hold the defaults template for a room type
    public static function get_meta_keys(): array

    // Map of form field name => meta key for the defaults editor
    public static function get_field_map(): array

    // Register REST-visible room post meta and room type term meta (init, p0)
    public static function register_meta(): void

    // Hook term lifecycle (delete cleans up defaults meta)
    public static function init(): void

    // Get all room types (terms) with their defaults meta
    public static function get_room_types(): array

    // Get a single room type by term ID or slug
    public static function get_room_type(int|string $term_id_or_slug): ?array

    // Format a room type term into an array with its defaults meta
    public static function format_room_type(WP_Term $term): array

    // Stored defaults as room-field values, or null when none saved
    public static function get_defaults(int|string $term_id_or_slug): ?array

    // Save defaults from an admin form submission; returns term ID or WP_Error
    public static function save_defaults(int $term_id, array $data): int|WP_Error

    // Delete the defaults template for a term (term-delete hook)
    public static function delete_defaults(int $term_id): void

    // Sanitizer registry for room meta keys shared by save paths
    public static function get_room_meta_registry(): array
}
```

**Defaults meta keys (term meta on `shb_room_type`):**
`_shb_type_base_price, _shb_type_max_guests, _shb_type_bed_type, _shb_type_room_size,
_shb_type_floor, _shb_type_amenities, _shb_type_min_nights, _shb_type_max_nights,
_shb_type_cancellation_policy`.

---

## SHB_Location (includes/class-shb-location.php)

```php
class SHB_Location {
    // Get all locations
    public static function get_locations(array $args = []): array
    
    // Get single location
    public static function get_location(int $location_id): ?array
    
    // Format location data
    public static function format_location(WP_Post|int $post): ?array
    
    // Get (and lazily create) the Default location ID
    public static function get_default_location_id(): int
    
    // Get the owning location ID for a room
    public static function get_location_for_room(int $room_id): int
    
    // Count rooms belonging to a location
    public static function get_room_count(int $location_id): int
    
    // Locations a user is allowed to manage (all for admins)
    public static function get_locations_for_user(int $user_id = 0): array
    
    // Whether the current user can manage a location
    public static function user_can_manage(int $location_id = 0): bool
    
    // Active locations (frontend selectors)
    public static function get_active_locations(): array
}
```

`get_locations_for_user()` returns all locations for admins and only the
locations in `shb_assigned_location_ids` for location managers. `user_can_manage()`
is used by admin pages and admin AJAX handlers to scope manager access.

**Location Array Structure:**
```php
[
    'id' => int,
    'name' => string,
    'description' => string,
    'excerpt' => string,
    'address' => string,
    'city' => string,
    'country' => string,
    'phone' => string,
    'email' => string,
    'timezone' => string (e.g. 'Europe/London'),
    'currency' => string (e.g. 'USD'),
    'currency_symbol' => string (e.g. '$'),
    'check_in_time' => string (e.g. '14:00'),
    'check_out_time' => string (e.g. '11:00'),
    'is_active' => bool,
    'image' => string (URL),
    'permalink' => string (URL),
]
```

Meta keys: `_shb_address, _shb_city, _shb_country, _shb_phone, _shb_email,
_shb_timezone, _shb_currency, _shb_currency_symbol, _shb_check_in_time,
_shb_check_out_time, _shb_is_active, _shb_is_default`.

---

## SHB_Migration (includes/class-shb-migration.php)

```php
class SHB_Migration {
    // Run the 1.4.0-beta.1 migration (idempotent)
    public static function run(): void
    
    // Whether this migration has already run
    public static function has_run(): bool
    
    // Get the stored migration report
    public static function get_report(): array
}
```

**Report keys:** `version, run_at, total_rooms, rooms_attached, orphaned_rooms,
room_types_migrated, bookings_snapshotted, bookings_without_room,
invalid_date_bookings, nights_backfilled, overlapping_bookings`.

---

## SHB_Room_Nights (includes/class-shb-room-nights.php)

```php
class SHB_Room_Nights {
    // Table name (with prefix)
    public static function table(): string
    
    // Check whether a room is free for a stay
    public static function check_availability(
        int $room_id, string $check_in, string $check_out,
        ?int $exclude_booking_id = null
    ): bool
    
    // Claim one row per night (call inside a transaction)
    public static function claim_nights(
        int $room_id, int $location_id, int $booking_id,
        string $check_in, string $check_out, ?string $hold_expires_at = null
    ): true|WP_Error
    
    // Remove all nightly rows for a booking (cancellation)
    public static function release_nights(int $booking_id): int|false
    
    // Set/clear hold expiry on a booking's nights
    public static function set_hold_expiry(int $booking_id, ?string $hold_expires_at): int|false
    
    // Get the hold expiry for a booking
    public static function get_hold_expiry(int $booking_id): ?string
    
    // Free nights whose holds expired (cron) — returns nights freed
    public static function clear_expired_holds(): int
    
    // Occupied night rows for a date range, optionally by location
    public static function get_occupied_nights(
        string $start_date, string $end_date, int $location_id = 0
    ): array
}
```

---

## SHB_Roles (includes/class-shb-roles.php)

```php
class SHB_Roles {
    // Plugin capability list
    public static function get_capabilities(): array
    // Caps: shb_manage_locations, shb_manage_rooms, shb_manage_bookings, shb_view_reports
    
    // Register the location manager role + admin caps (idempotent; adds caps
    // to an existing role on upgrade)
    public static function register(): void
    
    // Remove plugin caps and role (deactivation)
    public static function unregister(): void
    
    // Whether a user can manage bookings/rooms for a location
    public static function user_can_manage_location(int $user_id, int $location_id): bool
}
```

The `shb_location_manager` role receives all four caps. Its access is scoped
to `shb_assigned_location_ids` (user meta); see the Location-Manager Scoping
section in `includes/README.md`.

---

## SHB_Room (includes/class-shb-room.php)

```php
class SHB_Room {
    // Get single room by ID
    public static function get_room(int $room_id): ?array
    
    // Get all rooms (supports location_id / location_ids filter, all flag)
    public static function get_rooms(array $args = []): array
    
    // Get rooms by type (taxonomy term, legacy meta fallback)
    public static function get_rooms_by_type(string $room_type): array
    
    // Ensure a room has a canonical shb_room_type term (from meta if needed)
    public static function sync_room_type_term(int $post_id): ?WP_Term
    
    // Count active rooms per room type at a location
    public static function get_room_type_counts_by_location(int $location_id): array
    // Returns: [['slug' => string, 'name' => string, 'count' => int], ...] sorted by count desc
    
    // Search available rooms (supports location_id and room_type filters)
    public static function search_available_rooms(
        string $check_in, string $check_out, int $guests = 1,
        int $location_id = 0, string $room_type = ''
    ): array
    
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
    'room_type' => string,      // canonical taxonomy slug; meta fallback
    'location_id' => int,
    'location_name' => string,
    'location' => ?array (SHB_Location format),
    'max_guests' => int,
    'base_price' => float,
    'size' => int,
    'amenities' => array,
]
```

`get_rooms` args: `location_id` (int) or `location_ids` (int[]) filter on
`_shb_location_id`; `all => true` removes the default active-room filter
(admin screens). `room_type` meta + term are kept in sync by the room save
handler and `sync_room_type_term()`.

---

## SHB_Booking (includes/class-shb-booking.php)

```php
class SHB_Booking {
    // Create new booking (transactional; claims room nights)
    public static function create_booking(array $data): array|WP_Error
    // Returns: ['booking_id' => int, 'booking_ref' => string, 'booking_token' => string, 'total_price' => float]
    
    // Get booking by ID
    public static function get_booking(int $booking_id): ?array
    
    // Get booking by reference
    public static function get_booking_by_ref(string $booking_ref): ?array
    
    // Get bookings list (supports location_id / location_ids filter)
    public static function get_bookings(array $args = []): array
    
    // Update booking status (cancelled frees room nights)
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

**Booking Creation Input:**
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

**Creation Result:**
```php
[
    'booking_id' => int,
    'booking_ref' => string,
    'booking_token' => string, // Authorizes confirmation and ICS links.
    'total_price' => float,
]
```

**Booking Array Structure (format_booking) additionally includes:**
```php
[
    'location_id' => int,
    'location_name' => string,
    'price_snapshot' => array, // base_price, nights, nightly_rates, applied_rule_ids, subtotal, taxes, total
    'hold_expires_at' => ?string,
]
```

---

## SHB_Availability (includes/class-shb-availability.php)

```php
class SHB_Availability {
    // Check if room is available for dates
    // (availability blocks + shb_room_nights + legacy fallback)
    public static function check_room_availability(
        int $room_id, string $check_in, string $check_out,
        ?int $exclude_booking_id = null
    ): bool
    
    // Get all available rooms for dates
    public static function get_available_rooms(
        string $check_in, string $check_out, int $guests = 1
    ): array
    
    // Get availability blocks (all, or for one room)
    public static function get_availability_blocks(int $room_id = null): array
    
    // Get availability blocks belonging to a set of rooms
    public static function get_availability_blocks_by_rooms(int[] $room_ids): array
    
    // Get a single availability block by ID
    public static function get_availability_block(int $block_id): ?array
    
    // Create availability block (closure/maintenance)
    public static function create_block(array $data): int|WP_Error
    // Data: room_id, start_date, end_date, reason
    
    // Delete availability block
    public static function delete_block(int $block_id): bool
    
    // Get blocks for date range
    public static function get_blocks(
        string $start_date, string $end_date, int $room_id = 0
    ): array
}
```

---

## SHB_Pricing (includes/class-shb-pricing.php)

```php
class SHB_Pricing {
    // Calculate total price for stay (per-night scoped)
    public static function calculate_total_price(
        int $room_id, string $check_in, string $check_out
    ): float
    
    // Get the nightly multiplier for one date with scope precedence:
    // room override → room type at location → location-wide → global → 1.0
    public static function get_nightly_multiplier(
        int $room_id, string $date, int $room_type_term_id = 0
    ): float
    // Within a scope: dated rules beat always-on rules; ties break by id ASC.
    
    // Legacy multiplier (average across nights); room_id enables per-night path
    public static function get_pricing_multiplier(
        string $room_type, string $check_in, string $check_out, int $room_id = 0
    ): float
    
    // Get price breakdown (per-night rates + applied rule IDs)
    public static function get_price_breakdown(
        int $room_id, string $check_in, string $check_out
    ): ?array
    // Returns: ['base_price', 'nights', 'subtotal', 'multiplier', 'adjustment',
    //           'nightly_rates', 'applied_rule_ids', 'total']
    
    // Get all pricing rules
    public static function get_pricing_rules(): array
    
    // Get single pricing rule
    public static function get_pricing_rule(int $rule_id): ?array
    
    // Create pricing rule (supports location_id, room_id, room_type_term_id)
    public static function create_pricing_rule(array $data): int|WP_Error
    
    // Update pricing rule
    public static function update_pricing_rule(int $rule_id, array $data): bool
    
    // Delete pricing rule
    public static function delete_pricing_rule(int $rule_id): int|false
}
```

**Pricing rule scope columns:** `location_id`, `room_id`, `room_type_term_id`
(nullable; a rule with none is global). `room_type` (legacy string) still works.

---

## SHB_Database (includes/class-shb-database.php)

```php
class SHB_Database {
    // Registry of versioned migrations: version => callable
    public static function get_migrations(): array

    // Apply schema updates once for each SHB_DB_VERSION
    public static function maybe_upgrade(): void

    // Create or update plugin-owned tables and indexes
    public static function create_tables(): void
}
```

**Tables:** `shb_pricing_rules`, `shb_availability_blocks`,
`shb_payment_transactions`, `shb_room_nights`
(`UNIQUE(room_id, stay_date)`, `KEY(location_id, stay_date)`, `KEY(booking_id)`).

---

## SHB_Update_Checker (includes/class-shb-update-checker.php)

```php
class SHB_Update_Checker {
    // Register GitHub Release update checks in wp-admin and WP-Cron
    public static function init(): void
}
```

---

## SHB_Payments (includes/class-shb-payments.php)

```php
class SHB_Payments {
    // Register the Stripe webhook REST route
    public static function register_rest_routes(): void
    // Route: POST /wp-json/sanctuary-hotel-booking/v1/stripe-webhook

    // Create Stripe checkout session (sets a 30-minute hold; uses the
    // booking's location currency)
    public static function create_stripe_checkout(int $booking_id): array|WP_Error
    // Returns: ['checkout_url' => string, 'session_id' => string]
    
    // Verify Stripe payment
    public static function verify_stripe_payment(string $session_id): array|WP_Error
    
    // Handle Stripe webhook (source of truth; confirms only on verified webhook)
    public static function handle_stripe_webhook(WP_REST_Request $request): WP_REST_Response
    // Re-claims nights if the hold cleanup freed them before payment completed
    
    // Handle successful payment (redirect UX only — does NOT confirm)
    public static function handle_payment_success(int $booking_id, string $session_id): bool|WP_Error
    
    // Get Stripe secret key (respects test mode)
    private static function get_stripe_secret_key(): string
}
```

Webhook settings: `shb_stripe_test_webhook_secret`, `shb_stripe_live_webhook_secret`.

---

## SHB_Room_Nights / Cron

- `shb_cleanup_expired_holds` (hourly) → `SHB_Room_Nights::clear_expired_holds()`

---

## SHB_Ajax (includes/class-shb-ajax.php)

```php
class SHB_Ajax {
    // All methods are static and called via WordPress AJAX hooks
    
    // PUBLIC ENDPOINTS (no login required)
    public static function search_rooms(): void      // action: shb_search_rooms (accepts location_id, room_type; returns html + rooms)
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
    public static function admin_get_room_type_defaults(): void // action: shb_admin_get_room_type_defaults
    // Capability: manage_options | shb_manage_rooms. Reads room_type slug;
    // returns { defaults: array|null }
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
    // Room search with results (location attr/selector + room type chips)
    public static function room_search(array $atts): string
    // Shortcode: [shb_room_search location="12"]
    
    // Room list display (location attr)
    public static function room_list(array $atts): string
    // Shortcode: [shb_room_list columns="3" show_search="true" location="12"]
    
    // Renders a location property page (appended to single shb_location content)
    public static function location_page_content(string $content): string
    // Filter: the_content (only on singular shb_location in the main loop)
    
    // Booking form (location attr; validates room belongs to it)
    public static function booking_form(array $atts): string
    // Shortcode: [shb_booking_form room_id="123" location="12"]
    
    // Booking confirmation
    public static function booking_confirmation(array $atts): string
    // Shortcode: [shb_booking_confirmation]
    // Reads: booking_ref and booking_token from URL params
    
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
// See ARCHITECTURE.md for full settings list
```

---

## SHB_Admin_Locations (admin/class-shb-admin-locations.php)

```php
class SHB_Admin_Locations {
    // Location list / add / edit / delete pages (action query arg routing)
    public static function render_page(): void

    // Edit (and create) page; loads rooms at the location for the hub view
    public static function render_edit_page(int $location_id = 0): void

    // Insert or update a location from form data; returns post ID or WP_Error
    public static function save_location(array $data): int|WP_Error

    // Delete an empty location (refuses while rooms or bookings reference it)
    public static function delete_location(int $location_id): true|WP_Error

    // Confirm screen shown before deletion
    public static function render_delete_confirm(int $location_id): void
}
```

---

## SHB_Admin_Room_Types (admin/class-shb-admin-room-types.php)

```php
class SHB_Admin_Room_Types {
    // Room Types defaults list + edit screen under Hotel Booking
    public static function render_page(): void
}
```

Nonce actions: `shb_location_save` (field `shb_location_nonce`),
`shb_location_delete` (field `shb_location_delete_nonce`),
`shb_room_type_defaults_save` (field `shb_room_type_defaults_nonce`).
