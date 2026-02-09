# /templates/ - Frontend Templates

This directory contains PHP template files for frontend display.

## Templates

| File | Shortcode | Variables Available |
|------|-----------|---------------------|
| `booking-form.php` | `[shb_booking_form]` | `$room`, `$user`, `$dates` |
| `booking-confirmation.php` | `[shb_booking_confirmation]` | `$booking` |
| `room-card.php` | (partial) | `$room`, `$dates`, `$price` |
| `room-search.php` | `[shb_room_search]` | - |
| `my-bookings.php` | `[shb_my_bookings]` | `$bookings` |

## Template Loading

Templates are loaded by shortcode handlers in `SHB_Shortcodes`:

```php
// In class-shb-shortcodes.php
ob_start();
include SHB_PLUGIN_DIR . 'templates/booking-form.php';
return ob_get_clean();
```

## Key CSS Classes

| Class | Purpose |
|-------|---------|
| `.shb-booking-form-wrap` | Main booking form container |
| `.shb-form-section` | Section with card styling |
| `.shb-form-row` | Two-column form row |
| `.shb-form-field` | Single form field wrapper |
| `.shb-button-primary` | Primary action button |
| `.shb-payment-option` | Payment method radio option |
| `.shb-booking-confirmation` | Confirmation page container |
| `.shb-bank-transfer-details` | Bank transfer info card |

## Common Tasks

### Add form field
1. Add HTML in template file
2. Handle in `SHB_Ajax::create_booking()`
3. Save in `SHB_Booking::create_booking()`

### Modify confirmation display
1. Edit `booking-confirmation.php`
2. Add CSS in `assets/css/public.css`
3. Data comes from `SHB_Booking::format_booking()`
