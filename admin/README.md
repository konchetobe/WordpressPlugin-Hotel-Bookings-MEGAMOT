# /admin/ - Admin Interface

This directory contains admin-only functionality.

## Files

| File | Purpose |
|------|---------|
| `class-shb-admin.php` | Admin menus, dashboard, Gutenberg-off redirects for rooms |
| `class-shb-admin-bookings.php` | Bookings list/calendar page |
| `class-shb-admin-locations.php` | Location list/add/edit, per-location room hub, delete guard |
| `class-shb-admin-migration.php` | Read-only migration report |
| `class-shb-admin-pricing.php` | Scoped pricing rules page |
| `class-shb-admin-reports.php` | Occupancy/revenue reports |
| `class-shb-admin-room-types.php` | Room Types manager (create/rename/delete + defaults) |
| `class-shb-admin-rooms.php` | Rooms list + Room Setup screen (media uploader, all room fields) |
| `class-shb-admin-settings.php` | Settings save/load logic |
| `views/*.php` | HTML templates for admin pages |

## Admin Pages

| Menu Slug | View File | Purpose |
|-----------|-----------|---------|
| `sanctuary-hotel-booking` | `views/dashboard.php` | Dashboard overview |
| `shb-rooms` | `views/rooms.php`, `views/room-edit.php` | Rooms list + Room Setup (replaces Gutenberg) |
| `shb-room-types` | `views/room-types.php` | Room Types manager (create/rename/delete + defaults) |
| `shb-locations` | `views/locations.php`, `views/location-edit.php` | Location management + room hub |
| `shb-bookings` | `views/bookings.php` | Booking management (location filter) |
| `shb-pricing` | `views/pricing.php` | Scoped pricing rules |
| `shb-availability` | `views/availability.php` | Availability calendar (location filter) |
| `shb-reports` | `views/reports.php` | Occupancy + revenue reports |
| `shb-migration` | `views/migration.php` | Migration report |
| `shb-settings` | `views/settings.php` | Plugin settings |

Rooms are edited on the plugin-owned **Room Setup** screen — the Gutenberg
editor is disabled for `shb_room` (post.php/post-new.php redirect here).
Deleting a room is admin-only and refused while it has non-cancelled bookings.
Room Types: terms and their defaults are managed in one screen; deleting a
type is refused while rooms use it.

## Roles & Capabilities

- `shb_location_manager` role: `shb_manage_rooms`, `shb_manage_bookings`,
  `shb_view_reports`.
- Administrators receive all `shb_*` caps (`shb_manage_locations` is
  admin-only in practice).
- Assignment: user meta `shb_assigned_location_ids` (array of location IDs).
- Menus and list filters respect these caps; location managers only see their
  assigned locations' rooms/bookings.

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

Notable options: `shb_default_location_behavior` (`all`|`default`),
`shb_stripe_test_webhook_secret`, `shb_stripe_live_webhook_secret`.

## Common Tasks

### Add new setting
1. Add field to `save_settings()` in `class-shb-admin-settings.php`
2. Add UI in `views/settings.php`
3. Add to appropriate settings section

### Add admin page
1. Register in `SHB_Admin::add_admin_menu()`
2. Create view file in `views/`
3. Create callback method in the corresponding admin class
