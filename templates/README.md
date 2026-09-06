# /templates/ - Frontend Templates

This directory contains PHP template files for frontend display.

## Templates

| File | Shortcode | Variables Available |
|------|-----------|---------------------|
| `booking-form.php` | `[shb_booking_form]` | `$room`, `$user`, `$dates`, `$room_id`, `$check_in`, `$check_out`, `$guests`, `$prefill`, `$blocked_dates` |
| `booking-confirmation.php` | `[shb_booking_confirmation]` | `$booking`, `$location`, `$check_in_time`, `$check_out_time`, `$currency_symbol` |
| `room-card.php` | (shared partial) | `$room`, `$price`, `$currency_symbol`, `$dates`, `$atts` |
| `room-search.php` | `[shb_room_search]` | `$locations`, `$location`, `$location_id`, `$room_type_filter`, `$all_room_types` |
| `room-list.php` | `[shb_room_list]` | `$rooms`, `$atts` |
| `my-bookings.php` | `[shb_my_bookings]` | `$bookings` |
| `location-page.php` | single `shb_location` posts | `$location`, `$rooms`, `$room_types`, `$locations`, `$location_id`, `$room_type_filter`, `$all_room_types` |

`room-card.php` is the single source of truth for room card markup: used by
`room-list.php` and server-rendered by `SHB_Ajax::search_rooms()` for search
results. `room-search.php` renders a location selector when more than one
active location exists (a hidden field when scoped to one), plus room type
filter chips bound to the `room_type` search parameter.

`$booking` and each item in `$bookings` include `calendar_token`. Confirmation
pages require it as `booking_token`; calendar download links must send it as
`shb_calendar_token`. Do not expose ID-only or reference-only booking URLs.

Rooms include `location_id`, `location_name`, and a nested `location` array
(from `SHB_Location::format_location()`). `room-search.php` renders a location
selector when more than one active location exists; on location pages the
search is scoped via `$location_id`/`$locations`. Room type chips always
render from `$all_room_types` and preselect `$room_type_filter` (the `?type=`
query arg).

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
| `.shb-room-location` | Location name shown on cards/forms |

## Common Tasks

### Add form field
1. Add HTML in template file
2. Handle in `SHB_Ajax::create_booking()`
3. Save in `SHB_Booking::create_booking()`

### Modify confirmation display
1. Edit `booking-confirmation.php`
2. Add CSS in `assets/css/public.css`
3. Data comes from `SHB_Booking::format_booking()`
