# /includes/ - Core Classes

This directory contains the core PHP classes that power the plugin.

## Quick Reference

| File | Class | Purpose |
|------|-------|---------|
| `class-shb-ajax.php` | `SHB_Ajax` | All AJAX endpoint handlers |
| `class-shb-availability.php` | `SHB_Availability` | Room availability checks |
| `class-shb-assets.php` | `SHB_Assets` | Enqueue CSS/JS |
| `class-shb-blocks.php` | `SHB_Blocks` | Gutenberg block registration |
| `class-shb-booking.php` | `SHB_Booking` | Booking CRUD operations |
| `class-shb-emails.php` | `SHB_Emails` | Email sending |
| `class-shb-payments.php` | `SHB_Payments` | Payment gateway integration |
| `class-shb-pricing.php` | `SHB_Pricing` | Price calculations |
| `class-shb-room.php` | `SHB_Room` | Room CRUD operations |
| `class-shb-shortcodes.php` | `SHB_Shortcodes` | Shortcode handlers |

## Class Relationships

```
User Request
     │
     ▼
SHB_Ajax ───────────────────────────────────────┐
     │                                           │
     ├─▶ SHB_Room (get room data)               │
     │                                           │
     ├─▶ SHB_Availability (check dates)         │
     │        │                                  │
     │        └─▶ queries shb_booking posts     │
     │        └─▶ queries shb_availability posts│
     │                                           │
     ├─▶ SHB_Pricing (calculate price)          │
     │                                           │
     ├─▶ SHB_Booking (create/update booking)    │
     │        │                                  │
     │        └─▶ SHB_Emails (send confirmation)│
     │                                           │
     └─▶ SHB_Payments (process payment)         │
              │                                  │
              └─▶ Stripe API / Bank Transfer    │
                                                 │
Response ◀──────────────────────────────────────┘
```

## Common Tasks

### Add new AJAX endpoint
1. Add hooks in `SHB_Ajax::init()`
2. Create handler method
3. Use `check_ajax_referer()` for security

### Modify booking fields
1. Update `SHB_Booking::create_booking()` to save new meta
2. Update `SHB_Booking::format_booking()` to return new field

### Add payment method
1. Add settings in `SHB_Admin_Settings`
2. Handle in `SHB_Ajax::create_booking()`
3. Add processing in `SHB_Payments`
