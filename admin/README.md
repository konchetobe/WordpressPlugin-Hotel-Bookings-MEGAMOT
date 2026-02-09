# /admin/ - Admin Interface

This directory contains admin-only functionality.

## Files

| File | Purpose |
|------|---------|
| `class-shb-admin.php` | Admin menus, pages, dashboard |
| `class-shb-admin-settings.php` | Settings save/load logic |
| `views/*.php` | HTML templates for admin pages |

## Admin Pages

| Menu Slug | View File | Purpose |
|-----------|-----------|---------|
| `sanctuary-booking` | `views/dashboard.php` | Dashboard overview |
| `sanctuary-booking-bookings` | `views/bookings.php` | Booking management |
| `sanctuary-booking-rooms` | `views/rooms.php` | Room management |
| `sanctuary-booking-availability` | `views/availability.php` | Availability calendar |
| `sanctuary-booking-settings` | `views/settings.php` | Plugin settings |

## Settings Architecture

Settings are saved as WordPress options with `shb_` prefix.

```
SHB_Admin_Settings::save_settings()
    │
    ├─► Checkbox fields → sanitize as intval (0/1)
    ├─► Text fields → sanitize_text_field()
    ├─► Email fields → sanitize_email()
    ├─► Textarea fields → sanitize_textarea_field()
    └─► Color fields → sanitize_hex_color()
```

## Common Tasks

### Add new setting
1. Add field to `save_settings()` in `class-shb-admin-settings.php`
2. Add UI in `views/settings.php`
3. Add to appropriate settings section

### Add admin page
1. Register in `SHB_Admin::add_admin_menu()`
2. Create view file in `views/`
3. Create callback method in `SHB_Admin`
