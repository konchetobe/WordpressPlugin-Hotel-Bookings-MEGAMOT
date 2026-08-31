# Multiple Locations and Rooms Plan

## Objective

Support many independently managed hotel locations, each with physical rooms,
location-specific rules, availability, pricing, and booking operations. The
current single-property behaviour and historical bookings must remain valid.

## Decisions

| Concern | Design |
|---|---|
| Location | A `shb_location` custom post type with address, contact, time zone, check-in/out times, currency, and status metadata. |
| Room | Keep `shb_room` as one bookable physical room. Store its owner in `_shb_location_id`; a room cannot belong to multiple locations. |
| Room type | Use the existing `shb_room_type` taxonomy as the canonical type. Migrate the legacy `_shb_room_type` value to a term and preserve the meta during the compatibility window. |
| Booking history | Keep `shb_booking` posts and add immutable location ID/name snapshots. This keeps emails, exports, and historical reports accurate when a room or location is renamed. |
| Inventory control | Add a dedicated per-night allocation table. Its unique `(room_id, stay_date)` index makes double bookings impossible at the database level. |
| Pricing scope | Add global, location, room-type, and room scopes. Resolve them from least to most specific and store the resolved price snapshot on the booking. |

## Target Data Model

```text
shb_location (CPT)
    1 ─── * shb_room (CPT, _shb_location_id)
                 1 ─── * shb_booking (CPT, snapshots)
                 1 ─── * wp_shb_room_nights (active inventory claims)
```

### New room and booking metadata

| Entity | Key | Purpose |
|---|---|---|
| Room | `_shb_location_id` | Owning location post ID. |
| Booking | `_shb_location_id` | Location at the time of booking. |
| Booking | `_shb_location_name` | Human-readable immutable snapshot. |
| Booking | `_shb_price_snapshot` | Calculated nightly rates, rules, taxes, and total used at checkout. |
| Booking | `_shb_hold_expires_at` | Expiry for unpaid online-payment reservation holds. |

### New tables

`{$wpdb->prefix}shb_room_nights`

| Column | Notes |
|---|---|
| `id` | Primary key. |
| `room_id`, `location_id`, `booking_id` | Foreign-reference values (WordPress does not enforce FK constraints). |
| `stay_date` | One row per occupied night. |
| `hold_expires_at` | Set only for a pending payment hold. |
| `created_at` | Audit data. |

Indexes: `UNIQUE(room_id, stay_date)`, `KEY(location_id, stay_date)`, and
`KEY(booking_id)`. A cancellation removes its active nightly rows; the booking
post remains the financial/audit record.

Extend existing `shb_pricing_rules` with nullable `location_id`, `room_id`,
and `room_type_term_id` columns plus indexes for active rules and dates. A
rule without scope remains global.

## Delivery Phases

> Status: **all phases implemented in 1.4.0-beta.1**. See `tests/README.md`
> for the manual verification checklist and `tests/verify.php` for WP-CLI
> sanity checks. The GitHub release is cut via `.github/workflows/release.yml`.

1. Foundation and migration ✅
   - Add a versioned schema migration framework and the location CPT.
   - Create one **Default location**, attach all existing rooms, migrate room
     types to taxonomy terms, and snapshot the location onto all bookings.
   - Produce an admin migration report for orphaned rooms, missing locations,
     invalid dates, and already-overlapping legacy bookings. Do not auto-delete
     or silently resolve those records.
   - Implemented: `SHB_Database::get_migrations()`, `SHB_Location`,
     `SHB_Migration`, `shb_location` CPT, **Hotel Booking → Migration** page,
     `wp_shb_room_nights` table.

2. Admin management ✅
   - Add Locations list/add/edit pages, a required location selector on rooms,
     location filters for rooms/bookings, a per-location availability calendar,
     and scoped pricing controls.
   - Add roles/capabilities so a location manager can administer only assigned
     locations; retain `manage_options` for global configuration.
   - Implemented: `SHB_Admin_Locations` + views, room meta location selector,
     Location column + list filter on rooms/bookings, availability filter,
     scoped pricing UI (Global/Location/Room Type at Location/Room),
     `SHB_Roles` with `shb_location_manager` + `shb_assigned_location_ids`.

3. Transactional booking flow ✅
   - Replace post-meta availability scans with room-night allocation queries.
   - Within a database transaction: revalidate dates and capacity, insert each
     night, create the booking, and save all snapshots. Roll back on any error.
   - Create expiring holds for Stripe checkout and clear stale holds through
     scheduled cleanup. Confirm payment only from a verified Stripe webhook;
     callback URLs are not a source of truth.
   - Implemented: `SHB_Room_Nights` (claim/release/holds/cron
     `shb_cleanup_expired_holds`), transactional `SHB_Booking::create_booking()`,
     `_shb_price_snapshot`, Stripe webhook at
     `POST /wp-json/sanctuary-hotel-booking/v1/stripe-webhook` with HMAC
     signature verification.

4. Frontend and API ✅
   - Add a location selector (or preselect from a location page), pass the
     selected location through searches, and show location-specific details in
     cards, confirmation, emails, and ICS files.
   - Add location filters to Gutenberg blocks/shortcodes and preserve existing
     shortcodes by defaulting them to all active locations or the Default
     location, according to the chosen migration setting.
   - Implemented: location selector in `[shb_room_search]`, `location` attribute
     on `[shb_room_list]`/`[shb_booking_form]` and the blocks, location-aware
     emails/ICS/confirmation, `shb_default_location_behavior` setting.

5. Reporting, tests, and rollout ✅
   - Add occupancy/revenue reports grouped by location and timezone-correct
     date ranges.
   - Test cross-location room names, overlapping stays, hold expiry, scope
     precedence, role isolation, migration rollback, and a no-location legacy
     booking.
   - Take a database backup before migration and retain an opt-in compatibility
     period for legacy room-type meta and location defaults.
   - Implemented: **Hotel Booking → Reports** (occupancy + revenue by
     location), `tests/README.md` checklist, `tests/verify.php` WP-CLI script.
     Legacy `_shb_room_type` meta and global pricing rules remain supported
     (compatibility window).

## Pricing Precedence

For one stay date, apply the most-specific matching rule in this order:

1. Room override
2. Room type at the location
3. Location-wide rule
4. Global rule
5. Room base price

The rule IDs and resulting nightly rate must be stored in the booking snapshot,
not recalculated when an administrator changes future pricing.

## Acceptance Criteria

- An administrator can create at least two locations and place rooms under each.
- A booking at Location A never blocks a same-named room at Location B.
- Two simultaneous booking attempts for the same room/night result in one
  confirmed claim and one availability error.
- Room/location names, pricing, and policies shown for old bookings remain
  historically correct after later edits.
- All legacy rooms and bookings are assigned to the Default location unless
  explicitly mapped during migration.
