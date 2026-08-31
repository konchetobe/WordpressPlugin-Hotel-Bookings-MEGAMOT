# Sanctuary Hotel Booking Plugin - Architecture Guide

> ⚠️ **AI AGENTS**: Before modifying code, read [`AGENTS.md`](./AGENTS.md) for documentation update requirements.

This document provides a comprehensive overview of the plugin architecture to help developers and LLMs understand how the codebase works.

## 📚 Documentation Index

| File | Purpose | When to Read |
|------|---------|--------------|
| `AGENTS.md` | Documentation update rules | Before making ANY changes |
| `ARCHITECTURE.md` | Full architecture guide | Understanding the system |
| `PROJECT_STRUCTURE.md` | File tree & quick reference | Finding the right file |
| `SKELETON.md` | Function signatures only | Calling functions |
| `MULTI_LOCATION_PLAN.md` | Phased multiple-location design | Planning the location feature |
| `*/README.md` | Module-specific docs | Working in specific directory |

## 📁 Directory Structure

```
sanctuary-hotel-booking/
├── sanctuary-hotel-booking.php    # Main plugin file (entry point)
├── includes/                      # Core PHP classes
│   ├── class-shb-*.php           # Core functionality classes
├── admin/                         # Admin-only functionality
│   ├── class-shb-admin.php       # Admin menu & pages
│   ├── class-shb-admin-settings.php  # Settings management
│   ├── class-shb-admin-locations.php # Location CRUD
│   ├── class-shb-admin-migration.php # Migration report
│   ├── class-shb-admin-reports.php   # Occupancy/revenue reports
│   └── views/                    # Admin HTML templates
├── templates/                     # Frontend HTML templates
│   ├── booking-form.php          # Booking form template
│   ├── booking-confirmation.php  # Confirmation page
│   ├── room-card.php             # Single room display
│   └── ...
├── assets/
│   ├── css/                      # Stylesheets
│   │   ├── admin.css             # Admin styles
│   │   └── public.css            # Frontend styles
│   └── js/                       # JavaScript
│       ├── admin.js              # Admin functionality
│       └── public.js             # Frontend functionality
├── lib/
│   └── plugin-update-checker/    # Bundled GitHub Release updater dependency
├── tests/                        # Manual verification (README + verify.php)
└── .github/workflows/release.yml # Bump version + release workflow
```

## 🏗️ Core Classes

### Entry Point
- **`sanctuary-hotel-booking.php`** - Main plugin file that initializes everything

### Data Management
| Class | File | Purpose |
|-------|------|---------|
| `SHB_Room` | `includes/class-shb-room.php` | Room CRUD operations, custom post type `shb_room`, location-aware |
| `SHB_Booking` | `includes/class-shb-booking.php` | Booking CRUD, transactional creation, custom post type `shb_booking` |
| `SHB_Location` | `includes/class-shb-location.php` | Location CRUD, custom post type `shb_location`, default location |
| `SHB_Room_Nights` | `includes/class-shb-room-nights.php` | Per-night allocation table (double-booking safety, holds) |
| `SHB_Availability` | `includes/class-shb-availability.php` | Room availability checks, availability blocks |
| `SHB_Pricing` | `includes/class-shb-pricing.php` | Price calculations, scoped per-night pricing rules |
| `SHB_Database` | `includes/class-shb-database.php` | Plugin table schema and versioned migrations |
| `SHB_Migration` | `includes/class-shb-migration.php` | Multi-location data migration (1.4.0-beta.1) |
| `SHB_Roles` | `includes/class-shb-roles.php` | Location manager role and capabilities |

### Payment Processing
| Class | File | Purpose |
|-------|------|---------|
| `SHB_Payments` | `includes/class-shb-payments.php` | Stripe integration, payment transactions, webhook |

### Frontend Display
| Class | File | Purpose |
|-------|------|---------|
| `SHB_Shortcodes` | `includes/class-shb-shortcodes.php` | All shortcode handlers (location-aware) |
| `SHB_Blocks` | `includes/class-shb-blocks.php` | Gutenberg block registration |
| `SHB_Assets` | `includes/class-shb-assets.php` | CSS/JS enqueuing |

### Backend/API
| Class | File | Purpose |
|-------|------|---------|
| `SHB_Ajax` | `includes/class-shb-ajax.php` | All AJAX endpoint handlers |
| `SHB_Admin` | `admin/class-shb-admin.php` | Admin menus, dashboard |
| `SHB_Admin_Locations` | `admin/class-shb-admin-locations.php` | Location list/add/edit pages |
| `SHB_Admin_Migration` | `admin/class-shb-admin-migration.php` | Migration report page |
| `SHB_Admin_Reports` | `admin/class-shb-admin-reports.php` | Occupancy/revenue reports |
| `SHB_Admin_Settings` | `admin/class-shb-admin-settings.php` | Plugin settings |
| `SHB_Update_Checker` | `includes/class-shb-update-checker.php` | GitHub Release auto-updates in wp-admin/WP-Cron |

---

## 📊 Custom Post Types

### `shb_location` (Locations)
Stores property information. Each room belongs to exactly one location via
`_shb_location_id`.

**Meta Fields:**
| Meta Key | Type | Description |
|----------|------|-------------|
| `_shb_address` | string | Street address |
| `_shb_city` | string | City |
| `_shb_country` | string | Country |
| `_shb_phone` | string | Contact phone |
| `_shb_email` | string | Contact email |
| `_shb_timezone` | string | PHP time zone (e.g. Europe/London) |
| `_shb_currency` | string | ISO currency code |
| `_shb_currency_symbol` | string | Display symbol |
| `_shb_check_in_time` | string | e.g. 14:00 |
| `_shb_check_out_time` | string | e.g. 11:00 |
| `_shb_is_active` | string | 1/0 |
| `_shb_is_default` | string | 1 if the Default location |

The Default location ID is stored in option `shb_default_location_id` and is
lazily created by `SHB_Location::get_default_location_id()`.

### `shb_room` (Rooms)
Stores hotel room information.

**Meta Fields:**
| Meta Key | Type | Description |
|----------|------|-------------|
| `_shb_room_type` | string | Legacy room type (compatibility window; canonical is the `shb_room_type` term) |
| `_shb_location_id` | int | Owning location post ID |
| `_shb_max_guests` | int | Maximum occupancy |
| `_shb_base_price` | float | Base price per night |
| `_shb_amenities` | array | List of amenities |
| `_shb_gallery` | array | Gallery image IDs |
| `_shb_size` | int | Room size in sqm |

### `shb_booking` (Bookings)
Stores reservation data.

**Meta Fields:**
| Meta Key | Type | Description |
|----------|------|-------------|
| `_shb_booking_ref` | string | Unique reference (e.g., SHB-A1B2C3D4) |
| `_shb_room_id` | int | Room post ID |
| `_shb_room_name` | string | Room name (denormalized) |
| `_shb_location_id` | int | Location at booking time (immutable snapshot) |
| `_shb_location_name` | string | Location name at booking time (immutable snapshot) |
| `_shb_check_in` | string | Check-in date (Y-m-d) |
| `_shb_check_out` | string | Check-out date (Y-m-d) |
| `_shb_guests` | int | Number of guests |
| `_shb_first_name` | string | Guest first name |
| `_shb_last_name` | string | Guest last name |
| `_shb_email` | string | Guest email |
| `_shb_phone` | string | Guest phone |
| `_shb_special_requests` | text | Special requests |
| `_shb_total_price` | float | Total booking price |
| `_shb_price_snapshot` | array | Immutable nightly rates, applied rule IDs, taxes, total |
| `_shb_payment_method` | string | stripe, bank_transfer, paypal |
| `_shb_payment_status` | string | pending, paid, refunded, expired |
| `_shb_booking_status` | string | pending, confirmed, cancelled, checked_in, checked_out |
| `_shb_booking_date` | datetime | When booking was created |
| `_shb_stripe_session_id` | string | Stripe checkout session ID |
| `_shb_hold_expires_at` | datetime | Expiry for unpaid payment holds |
| `_shb_calendar_token` | string | Secret token required to view confirmation or download the booking ICS file |

### `shb_availability` (Availability Blocks)
Manual availability blocks (closures, maintenance, etc.)

**Meta Fields:**
| Meta Key | Type | Description |
|----------|------|-------------|
| `_shb_room_id` | int | Room ID (0 = all rooms) |
| `_shb_start_date` | string | Block start date |
| `_shb_end_date` | string | Block end date |
| `_shb_reason` | string | Reason for block |

---

## 🗄️ Database Tables

All tables use `{$wpdb->prefix}` and are created by `SHB_Database::create_tables()`
with `dbDelta`. Schema changes run through versioned migrations in
`SHB_Database::get_migrations()`.

### `shb_room_nights` (per-night allocation)
| Column | Type | Notes |
|--------|------|-------|
| `id` | BIGINT UNSIGNED AUTO_INCREMENT | PK |
| `room_id` | BIGINT UNSIGNED | |
| `location_id` | BIGINT UNSIGNED | |
| `booking_id` | BIGINT UNSIGNED | |
| `stay_date` | DATE | One row per occupied night |
| `hold_expires_at` | DATETIME NULL | Set while a payment hold is active |
| `created_at` | DATETIME | |

Indexes: `UNIQUE(room_id, stay_date)` — makes double bookings impossible at the
database level — plus `KEY(location_id, stay_date)` and `KEY(booking_id)`.
Cancellation removes the booking's rows; the booking post remains the audit record.

### `shb_pricing_rules` (scoped)
Nullable scope columns were added: `location_id`, `room_id`, `room_type_term_id`.
A rule with no scope is global. Legacy `room_type` (string) rules still apply.

---

## 🔌 AJAX Endpoints

All AJAX actions are handled in `SHB_Ajax` class. Action names use `shb_` prefix.

| Action | Access | Handler | Purpose |
|--------|--------|---------|---------|
| `shb_search_rooms` | public | `search_rooms()` | Search available rooms by date/guests/location |
| `shb_check_availability` | public | `check_availability()` | Check single room availability |
| `shb_get_room_prices` | public | `get_room_prices()` | Get prices for date range |
| `shb_create_booking` | public | `create_booking()` | Create new booking |
| `shb_process_payment` | public | `process_payment()` | Handle payment callbacks |
| `shb_get_booking` | admin | `admin_get_booking()` | Get booking details |
| `shb_update_booking_status` | admin | `admin_update_status()` | Update booking status |
| `shb_get_bookings_calendar` | admin | `get_bookings_calendar()` | Get calendar data |
| `shb_create_room` | admin | `admin_create_room()` | Create new room |
| `shb_delete_room` | admin | `admin_delete_room()` | Delete room |
| `shb_create_availability_block` | admin | `create_availability_block()` | Create availability block |
| `shb_delete_availability_block` | admin | `delete_availability_block()` | Delete availability block |
| `shb_send_booking_email` | admin | `send_booking_email()` | Resend confirmation email |

**AJAX Request Format:**
```javascript
$.ajax({
    url: shb_ajax.ajax_url,  // /wp-admin/admin-ajax.php
    type: 'POST',
    data: {
        action: 'shb_action_name',
        nonce: shb_ajax.nonce,
        // ... other data
    }
});
```

---

## 🎨 Shortcodes

| Shortcode | Handler | Description |
|-----------|---------|-------------|
| `[shb_room_search]` | `SHB_Shortcodes::room_search()` | Room search form with results (optional `location` attr) |
| `[shb_room_list]` | `SHB_Shortcodes::room_list()` | Display all rooms (optional `location` attr) |
| `[shb_booking_form]` | `SHB_Shortcodes::booking_form()` | Booking form (requires room_id param, optional `location`) |
| `[shb_booking_confirmation]` | `SHB_Shortcodes::booking_confirmation()` | Confirmation page |
| `[shb_my_bookings]` | `SHB_Shortcodes::my_bookings()` | Customer booking lookup |

**Example Usage:**
```
[shb_room_search]
[shb_room_list columns="3" show_search="true"]
[shb_room_list columns="3" location="12"]
[shb_booking_form room_id="123" location="12"]
```

Shortcodes without a location honor `shb_default_location_behavior`
(`all` = every active location, `default` = Default location only).

---

## 💳 Payment Flow

### Stripe Card Payment
```
1. User fills booking form → selects "Card Payment"
2. Frontend JS → AJAX: shb_create_booking
3. Backend creates booking inside a transaction and claims room nights
   (status: pending, hold_expires_at = now + 30 min)
4. Backend calls SHB_Payments::create_stripe_checkout()
   → Creates Stripe Checkout Session
   → Saves session_id to booking meta + hold expiry
5. Returns checkout_url to frontend
6. Frontend redirects to Stripe Checkout
7. User completes payment on Stripe
8. Stripe POSTs checkout.session.completed to the webhook:
   POST /wp-json/sanctuary-hotel-booking/v1/stripe-webhook
   → Signature verified (HMAC-SHA256, whsec_* secret)
   → Session metadata must match the booking
   → payment_status=paid → clears hold, booking confirmed
9. User returns via redirect; confirmation page reflects the webhook state
   (the redirect itself never confirms the booking)
```

### Bank Transfer
```
1. User fills booking form → selects "Bank Transfer"
2. Frontend JS → AJAX: shb_create_booking
3. Backend creates booking (status: pending) + claims room nights
4. Returns bank details + booking_ref + booking_token
5. Frontend redirects to confirmation page
6. Confirmation page shows:
   - Bank account details (from settings)
   - Booking reference (use as payment reference)
   - Amount to transfer
7. Admin manually marks as paid when transfer received
```

### Hold cleanup
- Cron `shb_cleanup_expired_holds` (hourly) →
  `SHB_Room_Nights::clear_expired_holds()`.
- Frees nights where `hold_expires_at < NOW()` and cancels the pending booking.

---

## ⚙️ Plugin Settings

Settings are stored as WordPress options with `shb_` prefix.

### General Settings
| Option Key | Default | Description |
|------------|---------|-------------|
| `shb_currency` | EUR | Currency code |
| `shb_currency_symbol` | € | Currency symbol |
| `shb_check_in_time` | 14:00 | Default check-in time |
| `shb_check_out_time` | 11:00 | Default check-out time |
| `shb_default_location_behavior` | all | `all` or `default` — legacy shortcode behavior |

### Stripe Settings
| Option Key | Default | Description |
|------------|---------|-------------|
| `shb_stripe_enabled` | 1 | Enable Stripe payments |
| `shb_stripe_test_mode` | 1 | Use test/live keys |
| `shb_stripe_test_publishable_key` | - | Test publishable key |
| `shb_stripe_test_secret_key` | - | Test secret key |
| `shb_stripe_test_webhook_secret` | - | Test webhook signing secret (whsec_...) |
| `shb_stripe_live_publishable_key` | - | Live publishable key |
| `shb_stripe_live_secret_key` | - | Live secret key |
| `shb_stripe_live_webhook_secret` | - | Live webhook signing secret |

### Bank Transfer Settings
| Option Key | Default | Description |
|------------|---------|-------------|
| `shb_bank_transfer_enabled` | 0 | Enable bank transfer |
| `shb_bank_account_holder` | - | Account holder name |
| `shb_bank_iban` | - | Bank IBAN |
| `shb_bank_bic` | - | BIC/SWIFT code |
| `shb_bank_name` | - | Bank name |
| `shb_bank_instructions` | - | Custom payment instructions |

### Email Settings
| Option Key | Default | Description |
|------------|---------|-------------|
| `shb_email_notifications` | 1 | Enable email notifications |
| `shb_admin_email` | admin_email | Admin notification email |
| `shb_attach_ics` | 1 | Attach calendar file to emails |

### Appearance Settings
| Option Key | Default | Description |
|------------|---------|-------------|
| `shb_primary_color` | #4a7c59 | Primary brand color |
| `shb_accent_color` | #d4a574 | Accent color |
| `shb_card_style` | elevated | Card style (elevated/flat/bordered) |
| `shb_button_style` | rounded | Button style (rounded/pill/square) |

---

## 🔄 Key Data Flows

### Room Search Flow
```
User Input (dates, guests, optional location)
    ↓
SHB_Ajax::search_rooms()
    ↓
SHB_Room::search_available_rooms(check_in, check_out, guests, location_id)
    ↓
SHB_Availability::check_room_availability() for each room
    ├─ availability blocks table
    └─ shb_room_nights (authoritative) + legacy booking scan fallback
    ↓
SHB_Pricing::calculate_total_price() for each available room (per-night scoped)
    ↓
Return room cards with prices + location name
    ↓
Frontend renders room-card template
```

### Booking Creation Flow
```
Booking Form Submit
    ↓
Frontend validation
    ↓
AJAX: shb_create_booking
    ↓
SHB_Ajax::create_booking()
    └─▶ SHB_Booking::create_booking()  [inside a DB transaction]
         ├─ Validate room/dates/guests/night limits
         ├─ SHB_Room_Nights::check_availability() — revalidate inside txn
         ├─ SHB_Pricing::get_price_breakdown() → build immutable price snapshot
         ├─ wp_insert_post() + all meta (location snapshots + price snapshot)
         ├─ SHB_Room_Nights::claim_nights() — one row per night
         ├─ COMMIT (or ROLLBACK on any error)
         └─ Payment method routing:
             ├─ stripe: create checkout session → set hold → redirect
             └─ bank_transfer: return bank details → confirmation page
```

### Availability Check Logic
```
SHB_Availability::check_room_availability(room_id, check_in, check_out)
    ↓
1. Query shb_availability_blocks for the room
   WHERE date overlaps with check_in/check_out → if found, NOT available
    ↓
2. Query shb_room_nights (authoritative)
   SELECT COUNT(*) WHERE room_id = X AND stay_date >= check_in AND stay_date < check_out
   → if > 0, NOT available
    ↓
3. Legacy fallback: query shb_booking posts by meta (pre-migration edge case)
    ↓
4. Return true (available) or false (not available)
```

### Pricing Precedence (per night)
For one stay date, the most-specific matching rule wins:

1. Room override (`room_id`)
2. Room type at the location (`location_id` + `room_type_term_id`)
3. Location-wide rule (`location_id`)
4. Global rule (no scope columns; legacy `room_type` string still filters)
5. Room base price (multiplier 1.0)

Rule IDs and nightly rates are stored in `_shb_price_snapshot` at booking time,
so historical bookings never change when pricing rules are edited later.

### Roles & Capabilities
- `shb_location_manager` role: `shb_manage_rooms`, `shb_manage_bookings`,
  `shb_view_reports` (no `manage_options`).
- Administrators get all `shb_*` caps.
- Assignment: user meta `shb_assigned_location_ids` (array of location IDs).
- `SHB_Location::user_can_manage($location_id)` checks `manage_options` first,
  then assigned locations. Menus and list filters respect these caps.

---

## 🗂️ Template Hierarchy

Templates are loaded from `/templates/` directory. They receive data via PHP variables.

| Template | Variables | Used By |
|----------|-----------|---------|
| `booking-form.php` | `$room`, `$user`, `$dates` | `[shb_booking_form]` |
| `booking-confirmation.php` | `$booking` | `[shb_booking_confirmation]` |
| `room-card.php` | `$room`, `$dates`, `$price` | Room search results |
| `room-search.php` | - | `[shb_room_search]` |
| `my-bookings.php` | `$bookings` | `[shb_my_bookings]` |

---

## 🎛️ Admin Pages

Admin pages are registered in `SHB_Admin::add_admin_menu()`.

| Menu Slug | Page | Description |
|-----------|------|-------------|
| `sanctuary-hotel-booking` | Dashboard | Overview, stats, recent bookings |
| `shb-bookings` | Bookings | Booking list, calendar view, location filter |
| `shb-locations` | Locations | Location list/add/edit |
| `shb-pricing` | Pricing Rules | Scoped pricing rules |
| `shb-availability` | Availability | Availability calendar, blocks, location filter |
| `shb-reports` | Reports | Occupancy + revenue by location |
| `shb-migration` | Migration | Read-only migration report |
| `shb-settings` | Settings | Plugin configuration |

---

## 🔐 Security

### Nonce Verification
All AJAX requests require nonce verification:
```php
check_ajax_referer('shb_nonce', 'nonce');
```

### Capability Checks
Admin-only endpoints verify user capabilities:
```php
if (!current_user_can('manage_options')) {
    wp_send_json_error(['message' => 'Unauthorized']);
}
```

### Data Sanitization
All input is sanitized before use:
```php
$email = sanitize_email($_POST['email']);
$name = sanitize_text_field($_POST['name']);
$text = sanitize_textarea_field($_POST['description']);
```

---

## 📧 Email System

Emails are sent via `SHB_Emails` class using `wp_mail()`.

| Email | Trigger | Recipients |
|-------|---------|------------|
| Booking Confirmation | Payment success | Guest |
| Admin Notification | New booking | Admin email |
| Booking Cancelled | Status → cancelled | Guest |
| Payment Reminder | Manual trigger | Guest |

---

## 🧪 Testing Considerations

### Key Test Scenarios
1. **Room Search**: Various date ranges, guest counts
2. **Availability**: Overlapping bookings, availability blocks
3. **Booking Creation**: Valid/invalid data, payment flows
4. **Payment**: Stripe success/cancel, bank transfer
5. **Admin**: CRUD operations, status updates

### Test Data
- Rooms are CPT `shb_room`
- Bookings are CPT `shb_booking`
- Settings are WP options with `shb_` prefix

---

## 🔧 Common Modifications

### Adding a New Payment Method
1. Add option in `SHB_Admin_Settings::save_settings()`
2. Add settings UI in `admin/views/settings.php`
3. Add payment option in `templates/booking-form.php`
4. Handle in `SHB_Ajax::create_booking()`
5. Add processing logic in `SHB_Payments`
6. Update confirmation template if needed

### Adding a Room Field
1. Add meta field in `SHB_Room::save_room_meta()`
2. Add to `SHB_Room::format_room()` for retrieval
3. Add UI in admin room editor
4. Display in room templates

### Adding an AJAX Endpoint
1. Add action hooks in `SHB_Ajax::init()`
2. Create handler method in `SHB_Ajax`
3. Use `check_ajax_referer()` for security
4. Return via `wp_send_json_success()` or `wp_send_json_error()`
