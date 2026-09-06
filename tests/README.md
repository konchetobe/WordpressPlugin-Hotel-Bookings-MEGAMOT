# Manual Test Checklist

The plugin has no automated test framework. Use this checklist to verify the
multi-location feature set before releasing. Each item maps to an acceptance
criterion in `MULTI_LOCATION_PLAN.md`.

## Prerequisites

- A WordPress install with the plugin active.
- `WP_DEBUG` enabled for migration/error logging.

## Migration (Phase 1)

1. Upgrade from a previous version (or run the migration by bumping
   `shb_db_version` below `1.4.0-beta.1`).
2. Open **Hotel Booking → Migration**.
   - [ ] Report shows total rooms, rooms attached, room types migrated,
         bookings snapshotted, nights backfilled.
   - [ ] Orphaned rooms are listed and were attached to the Default location.
   - [ ] Overlapping legacy bookings are flagged (not deleted).
   - [ ] Invalid-date bookings are flagged (not backfilled).
3. Verify `wp_shb_room_nights` contains one row per occupied night for every
   non-cancelled booking.
4. Verify every `shb_booking` has `_shb_location_id` and `_shb_location_name`.

## Cross-location room names (acceptance criterion 2)

1. Create two locations (Hotel A, Hotel B).
2. Create a room named "Ocean View" in each.
3. Book "Ocean View" at Hotel A.
4. Search Hotel B for the same dates.
   - [ ] "Ocean View" at Hotel B is still available.

## Double-booking protection (acceptance criterion 3)

1. Find a room and a free date range.
2. Fire two simultaneous `shb_create_booking` requests for the same room/dates.
   - [ ] Exactly one succeeds; the other returns "Room is not available".
   - [ ] `wp_shb_room_nights` has exactly one row per night.

## Historical correctness (acceptance criterion 4)

1. Create a booking; note its price and location name.
2. Rename the room and the location; change the price.
3. Re-open the old booking.
   - [ ] `_shb_room_name`, `_shb_location_name`, and `_shb_price_snapshot`
         still show the original values.

## Legacy data (acceptance criterion 5)

1. On a fresh install, create rooms without locations (pre-migration state).
2. Run the migration.
   - [ ] All rooms are attached to the Default location.
   - [ ] Nothing was deleted.

## Hold expiry

1. Start a Stripe checkout (test mode) for a room.
   - [ ] `wp_shb_room_nights` rows have `hold_expires_at` set (~30 min).
2. Wait past expiry (or run `wp cron event run shb_cleanup_expired_holds`).
   - [ ] Nights are freed; booking status becomes `cancelled`.
   - [ ] The room is bookable again for those dates.

## Stripe webhook

1. In Stripe test mode, configure the webhook endpoint
   `https://yoursite/wp-json/sanctuary-hotel-booking/v1/stripe-webhook`
   with the `checkout.session.completed` event and the signing secret saved in
   settings.
2. Complete a test payment.
   - [ ] The booking becomes `paid`/`confirmed` via the webhook.
   - [ ] The hold is cleared.
   - [ ] A forged/unsigned webhook request returns 400 and does not confirm.

## Roles (Phase 2)

1. Create a user with the **Location Manager** role; assign locations via
   `shb_assigned_location_ids` user meta.
   - [ ] They see the Hotel Booking menu but not global Settings/Locations.
   - [ ] They can manage rooms/bookings only for assigned locations.

## Pricing precedence

1. Create rules: global +20%, location +10%, room-type-at-location +5%, room +2%.
2. Search a room in that location.
   - [ ] The room override (+2%) wins over the rest.
3. Remove the room rule; the room-type rule applies.
   - [ ] Precedence order holds: room → room type @ location → location → global → base.

## Reports (Phase 5)

1. Create bookings at two locations with different dates.
2. Open **Hotel Booking → Reports**, filter by date range and location.
   - [ ] Occupancy nights and rates are per room/location.
   - [ ] Revenue is per location and timezone-correct.

## Room types as defaults (Phase A)

1. Open **Hotel Booking → Room Types** → **Edit Defaults** on a type; save
   price/guests/bed/amenities/etc.
2. Add a new room (`post-new.php?post_type=shb_room`), pick that room type.
   - [ ] Empty fields prefill with the defaults.
3. Change an existing room's type.
   - [ ] Only empty fields are filled; manually set values are untouched.
4. Search on the frontend and toggle the room type chips.
   - [ ] Results only include rooms of the selected type.
5. Create two rooms of the same type at two locations.
   - [ ] They remain independent bookable units.

## Locations hub & delete guard (Phase B)

1. Open **Hotel Booking → Locations**.
   - [ ] Rooms column shows the count and a per-type breakdown; clicking it
         opens the rooms list filtered to that location.
2. Open **Manage** on a location.
   - [ ] "Rooms at this location" lists its units with an "Add Room here"
         button that preselects the location.
3. With rooms assigned: the Delete action is hidden/disabled.
4. After deleting all rooms (with no bookings referencing it), delete the
   location from the confirm screen.
   - [ ] It is removed and `shb_default_location_id` is cleared if it pointed
         there.

## Location property pages (Phase C)

1. Visit a location's public URL (`/locations/<slug>/`).
   - [ ] Hero + contact details render.
   - [ ] Room-type summary cards show count + from-price; the link keeps
         `?location=<id>&type=<slug>` on the page.
   - [ ] The availability search is scoped to that property.
2. Visit the site's search page with more than one active location.
   - [ ] The location selector and room type chips both filter results.
   - [ ] Cards render identically to `[shb_room_list]` (shared partial).
