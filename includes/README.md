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
| `class-shb-room-type.php` | `SHB_Room_Type` | Room type defaults template (term meta) + meta registration |
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
  Within a scope, a dated rule beats an always-on rule; ties break by id ASC.
- The canonical room type is the `shb_room_type` term. `_shb_room_type` meta is
  kept in sync as the compatibility fallback. `SHB_Room::sync_room_type_term()`
  creates/matches the term from the meta (used by save, sample data, search,
  and the migration).
- Room types carry an optional **defaults template** in term meta
  (`_shb_type_*` keys, see `SHB_Room_Type`). The room meta box prefills a new
  room from the chosen type's defaults (AJAX
  `shb_admin_get_room_type_defaults`); existing rooms are never overwritten
  (only empty fields get filled on type change).

## Migrations

`SHB_Database::get_migrations()` maps DB version → callable. `maybe_upgrade()`
runs any migrations newer than the stored `shb_db_version` and only calls
`create_tables()` (dbDelta) when the stored version is behind, so schema work
is not repeated on every request. The 1.4.0-beta.1 migration
(`SHB_Migration::run`) creates the Default location, attaches rooms, migrates
room types to taxonomy terms, snapshots bookings, and backfills
`shb_room_nights`. It never deletes data; issues are listed on the
**Hotel Booking → Migration** page.

## Location-Manager Scoping

Non-admin users with the `shb_location_manager` role (caps
`shb_manage_locations`/`shb_manage_rooms`/`shb_manage_bookings`/`shb_view_reports`)
are restricted to their `shb_assigned_location_ids` user meta:

- The room meta box location dropdown, the rooms list filter, the bookings list
  and calendar, the availability page, and the reports page only show assigned
  locations.
- Admin AJAX actions (`admin_update_booking_status`,
  `admin_create_availability_block`, `admin_delete_availability_block`,
  `admin_send_booking_email`, `admin_get_dashboard_stats`) verify the target
  booking/room belongs to an assigned location.
- `SHB_Location::user_can_manage($location_id)` is the shared check.

## Common Tasks

### Add new AJAX endpoint
1. Add hooks in `SHB_Ajax::init()`
2. Create handler method
3. Use `check_ajax_referer()` for security
4. If non-admin roles can use it, scope data by `SHB_Location::user_can_manage()`

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
