# Sanctuary Hotel Booking Plugin - Architecture Guide

This document provides a comprehensive overview of the plugin architecture to help developers and LLMs understand how the codebase works.

## 📁 Directory Structure

```
sanctuary-hotel-booking/
├── sanctuary-hotel-booking.php    # Main plugin file (entry point)
├── includes/                      # Core PHP classes
│   ├── class-shb-*.php           # Core functionality classes
├── admin/                         # Admin-only functionality
│   ├── class-shb-admin.php       # Admin menu & pages
│   ├── class-shb-admin-settings.php  # Settings management
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
└── blocks/                       # Gutenberg blocks
```

## 🏗️ Core Classes

### Entry Point
- **`sanctuary-hotel-booking.php`** - Main plugin file that initializes everything

### Data Management
| Class | File | Purpose |
|-------|------|---------|
| `SHB_Room` | `includes/class-shb-room.php` | Room CRUD operations, custom post type `shb_room` |
| `SHB_Booking` | `includes/class-shb-booking.php` | Booking CRUD, custom post type `shb_booking` |
| `SHB_Availability` | `includes/class-shb-availability.php` | Room availability checks, availability blocks |
| `SHB_Pricing` | `includes/class-shb-pricing.php` | Price calculations, seasonal pricing |

### Payment Processing
| Class | File | Purpose |
|-------|------|---------|
| `SHB_Payments` | `includes/class-shb-payments.php` | Stripe integration, payment transactions |

### Frontend Display
| Class | File | Purpose |
|-------|------|---------|
| `SHB_Shortcodes` | `includes/class-shb-shortcodes.php` | All shortcode handlers |
| `SHB_Blocks` | `includes/class-shb-blocks.php` | Gutenberg block registration |
| `SHB_Assets` | `includes/class-shb-assets.php` | CSS/JS enqueuing |

### Backend/API
| Class | File | Purpose |
|-------|------|---------|
| `SHB_Ajax` | `includes/class-shb-ajax.php` | All AJAX endpoint handlers |
| `SHB_Admin` | `admin/class-shb-admin.php` | Admin menus, dashboard |
| `SHB_Admin_Settings` | `admin/class-shb-admin-settings.php` | Plugin settings |

---

## 📊 Custom Post Types

### `shb_room` (Rooms)
Stores hotel room information.

**Meta Fields:**
| Meta Key | Type | Description |
|----------|------|-------------|
| `_shb_room_type` | string | single, double, suite, family |
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
| `_shb_check_in` | string | Check-in date (Y-m-d) |
| `_shb_check_out` | string | Check-out date (Y-m-d) |
| `_shb_guests` | int | Number of guests |
| `_shb_first_name` | string | Guest first name |
| `_shb_last_name` | string | Guest last name |
| `_shb_email` | string | Guest email |
| `_shb_phone` | string | Guest phone |
| `_shb_special_requests` | text | Special requests |
| `_shb_total_price` | float | Total booking price |
| `_shb_payment_method` | string | stripe, bank_transfer, paypal |
| `_shb_payment_status` | string | pending, paid, refunded |
| `_shb_booking_status` | string | pending, confirmed, cancelled, checked_in, checked_out |
| `_shb_booking_date` | datetime | When booking was created |
| `_shb_stripe_session_id` | string | Stripe checkout session ID |

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

## 🔌 AJAX Endpoints

All AJAX actions are handled in `SHB_Ajax` class. Action names use `shb_` prefix.

| Action | Access | Handler | Purpose |
|--------|--------|---------|---------|
| `shb_search_rooms` | public | `search_rooms()` | Search available rooms by date/guests |
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
| `[shb_room_search]` | `SHB_Shortcodes::room_search()` | Room search form with results |
| `[shb_room_list]` | `SHB_Shortcodes::room_list()` | Display all rooms |
| `[shb_booking_form]` | `SHB_Shortcodes::booking_form()` | Booking form (requires room_id param) |
| `[shb_booking_confirmation]` | `SHB_Shortcodes::booking_confirmation()` | Confirmation page |
| `[shb_my_bookings]` | `SHB_Shortcodes::my_bookings()` | Customer booking lookup |

**Example Usage:**
```
[shb_room_search]
[shb_room_list columns="3" show_search="true"]
[shb_booking_form room_id="123"]
```

---

## 💳 Payment Flow

### Stripe Card Payment
```
1. User fills booking form → selects "Card Payment"
2. Frontend JS → AJAX: shb_create_booking
3. Backend creates booking (status: pending)
4. Backend calls SHB_Payments::create_stripe_checkout()
   → Creates Stripe Checkout Session
   → Saves session_id to booking meta
5. Returns checkout_url to frontend
6. Frontend redirects to Stripe Checkout
7. User completes payment on Stripe
8. Stripe redirects to success_url with session_id
9. SHB_Payments::handle_payment_success()
   → Verifies with Stripe API
   → Updates booking: payment_status=paid, booking_status=confirmed
   → Sends confirmation email
10. User sees confirmation page
```

### Bank Transfer
```
1. User fills booking form → selects "Bank Transfer"
2. Frontend JS → AJAX: shb_create_booking
3. Backend creates booking (status: pending)
4. Returns bank details + booking_ref
5. Frontend redirects to confirmation page
6. Confirmation page shows:
   - Bank account details (from settings)
   - Booking reference (use as payment reference)
   - Amount to transfer
7. Admin manually marks as paid when transfer received
```

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

### Stripe Settings
| Option Key | Default | Description |
|------------|---------|-------------|
| `shb_stripe_enabled` | 1 | Enable Stripe payments |
| `shb_stripe_test_mode` | 1 | Use test/live keys |
| `shb_stripe_test_publishable_key` | - | Test publishable key |
| `shb_stripe_test_secret_key` | - | Test secret key |
| `shb_stripe_live_publishable_key` | - | Live publishable key |
| `shb_stripe_live_secret_key` | - | Live secret key |

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
User Input (dates, guests)
    ↓
SHB_Ajax::search_rooms()
    ↓
SHB_Availability::check_room_availability() for each room
    ↓
SHB_Pricing::calculate_total_price() for each available room
    ↓
Return room cards with prices
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
    ├─→ Validate required fields
    ├─→ SHB_Room::get_room() - verify room exists
    ├─→ SHB_Availability::check_room_availability() - verify available
    ├─→ SHB_Pricing::calculate_total_price()
    ├─→ SHB_Booking::create_booking() - creates post + meta
    └─→ Payment method routing:
        ├─→ stripe: SHB_Payments::create_stripe_checkout() → redirect
        └─→ bank_transfer: return bank details → confirmation page
```

### Availability Check Logic
```
SHB_Availability::check_room_availability(room_id, check_in, check_out)
    ↓
1. Query shb_availability posts for room (or all rooms if room_id=0)
   WHERE date overlaps with check_in/check_out
   → If found, room is NOT available
    ↓
2. Query shb_booking posts for room
   WHERE booking_status NOT IN (cancelled)
   AND date overlaps with check_in/check_out
   → If found, room is NOT available
    ↓
3. Return true (available) or false (not available)
```

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
| `sanctuary-booking` | Dashboard | Overview, stats, recent bookings |
| `sanctuary-booking-bookings` | Bookings | Booking list, calendar view |
| `sanctuary-booking-rooms` | Rooms | Room management |
| `sanctuary-booking-availability` | Availability | Availability calendar, blocks |
| `sanctuary-booking-settings` | Settings | Plugin configuration |

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
