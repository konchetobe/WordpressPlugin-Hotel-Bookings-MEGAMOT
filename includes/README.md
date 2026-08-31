# /includes/ - Core Classes

This directory contains the core PHP classes that power the plugin.

## Quick Reference

| File | Class | Purpose |
|------|-------|---------|
| `class-shb-ajax.php` | `SHB_Ajax` | All AJAX endpoint handlers |
| `class-shb-availability.php` | `SHB_Availability` | Room availability checks (blocks + room nights) |
| `class-shb-blocks.php` | `SHB_Blocks` | Gutenberg block registration |
| `class-shb-booking.php` | `SHB_Booking` | Booking CRUD, transactional creation |
| `class-shb-calendar.php` | `SHB_Calendar` | ICS generation (location-aware) |
| `class-shb-database.php` | `SHB_Database` | Schema + versioned migrations |
| `class-shb-location.php` | `SHB_Location` | Location CRUD, default location |
| `class-shb-migration.php` | `SHB_Migration` | Multi-location data migration |
| `class-shb-payments.php` | `SHB_Payments` | Stripe integration + webhook |
| `class-shb-pricing.php` | `SHB_Pricing` | Per-night scoped price calculations |
| `class-shb-roles.php` | `SHB_Roles` | Location manager role + caps |
| `class-shb-room.php` | `SHB_Room` | Room CRUD (location-aware) |
| `class-shb-room-nights.php` | `SHB_Room_Nights` | Per-night allocation table |
| `class-shb-shortcodes.php` | `SHB_Shortcodes` | Shortcode handlers (location-aware) |
| `class-shb-update-checker.php` | `SHB_Update_Checker` | GitHub Release update integration |

## Class Relationships

```
User Request
     │
     ▼
SHB_Ajax ───────────────────────────────────────┐
     │                                           │
     ├─▶ SHB_Room (get room data + location)    │
     │                                           │
     ├─▶ SHB_Availability (check dates)         │
     │        │                                  │
     │        └─▶ shb_availability_blocks       │
     │        └─▶ SHB_Room_Nights (authoritative│
     │                 shb_room_nights table)   │
     │        └─▶ shb_booking posts (legacy)    │
     │                                           │
     ├─▶ SHB_Pricing (per-night scoped price)   │
     │                                           │
     ├─▶ SHB_Booking (transactional create)     │
     │        │                                  │
     │        ├─▶ SHB_Room_Nights::claim_nights │
     │        └─▶ emails + ICS (location-aware) │
     │                                           │
     └─▶ SHB_Payments (process payment)         │
              │                                  │
              ├─▶ Stripe Checkout + hold        │
              └─▶ Stripe webhook (confirms)     │
                                                  │
Response ◀──────────────────────────────────────┘
```

`SHB_Update_Checker` is loaded only for wp-admin and WP-Cron. It registers
the bundled Plugin Update Checker against GitHub Releases and requires a
packaged release ZIP. The release ZIP is produced by
`.github/workflows/release.yml`.

## Data Model

```
shb_location (CPT)
    1 ─── * shb_room (CPT, _shb_location_id)
                 1 ─── * shb_booking (CPT, snapshots)
                 1 ─── * wp_shb_room_nights (active inventory claims)
```

- `shb_room_nights` has `UNIQUE(room_id, stay_date)` — double bookings are
  impossible at the database level.
- Bookings carry immutable `_shb_location_id`, `_shb_location_name`, and
  `_shb_price_snapshot` so history stays correct after renames/re-pricing.
- `shb_pricing_rules` supports `location_id`, `room_id`, `room_type_term_id`
  scope columns; precedence is room → room type @ location → location → global.

## Migrations

`SHB_Database::get_migrations()` maps DB version → callable. `maybe_upgrade()`
runs any migrations newer than the stored `shb_db_version`. The 1.4.0-beta.1
migration (`SHB_Migration::run`) creates the Default location, attaches rooms,
migrates room types to taxonomy terms, snapshots bookings, and backfills
`shb_room_nights`. It never deletes data; issues are listed on the
**Hotel Booking → Migration** page.

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

### Add a new migration
1. Add an entry to `SHB_Database::get_migrations()` (version → callable)
2. Create the migration method/class
3. Bump `SHB_DB_VERSION` to match
