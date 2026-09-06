=== Sanctuary Hotel Booking ===
Contributors: sanctuaryhotels
Tags: hotel booking, reservation, accommodation, booking system, hotel management
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.4.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A comprehensive hotel/guest house booking reservation system with Stripe/PayPal payments and calendar event generation.

== Description ==

Sanctuary Hotel Booking is a powerful WordPress plugin that transforms your website into a fully functional hotel or guest house booking platform.

**Key Features:**

* **Room Management** - Create and manage multiple room types with images, amenities, and pricing
* **Availability Calendar** - Real-time availability checking prevents double bookings
* **Dynamic Pricing** - Set seasonal rates, weekend premiums, early bird discounts
* **Stripe Payments** - Accept credit cards, Apple Pay, and Google Pay
* **Calendar Events** - Generate .ics files for guests to add bookings to their calendars
* **Admin Dashboard** - View bookings, revenue stats, and manage your property
* **Shortcodes** - Easily embed booking forms and room lists anywhere on your site
* **Email Notifications** - Automatic confirmation emails for bookings

**Shortcodes:**

* `[shb_room_search]` - Display room search form with date picker
* `[shb_room_list]` - Display list of all available rooms
* `[shb_room_list type="deluxe" columns="3"]` - Display specific room types
* `[shb_booking_form room_id="123"]` - Display booking form for a specific room
* `[shb_booking_confirmation]` - Display booking confirmation page
* `[shb_my_bookings]` - Allow guests to view their bookings

== Installation ==

1. Upload the `sanctuary-hotel-booking` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Hotel Booking > Settings to configure your options
4. Add your Stripe API keys for payment processing
5. Create rooms via Hotel Booking > Rooms > Add New Room
6. Use shortcodes to display booking forms on your pages

== Frequently Asked Questions ==

= How do I set up Stripe payments? =

Go to Hotel Booking > Settings and enter your Stripe API keys. You can use test keys for development and switch to live keys when ready.

= Can I customize the booking form? =

Yes, the plugin uses template files that can be overridden in your theme. Copy templates from `/plugins/sanctuary-hotel-booking/templates/` to `/your-theme/sanctuary-hotel-booking/` and modify as needed.

= How do guests download calendar events? =

After completing a booking, guests see a "Add to Calendar" button that downloads an .ics file compatible with Google Calendar, Apple Calendar, and Outlook.

= Can I block dates for maintenance? =

Yes, go to Hotel Booking > Availability to create date blocks for any room.

== Screenshots ==

1. Room search and booking form
2. Room listing with pricing
3. Booking confirmation with calendar download
4. Admin dashboard with stats
5. Room management
6. Settings page

== Changelog ==

= 1.4.2 =
* **Room type defaults** — room types now carry a defaults template (base price, max guests, bed type, size, floor, amenities, night limits, cancellation policy) that prefills new rooms; rooms stay fully editable per unit.
* **Room Types screen** — new **Hotel Booking → Room Types** page to edit each type's defaults; the room editor's type dropdown is now driven by the real taxonomy (legacy types are preserved).
* **Location property hub** — the Locations list shows a per-type room breakdown with Rooms/View Page actions, and each location's edit page lists its rooms with an "Add Room here" link that preselects the location.
* **Safe location deletion** — deleting a location now requires an admin, asks for confirmation, and refuses while rooms or bookings still reference it.
* **Location property pages** — each location's public URL now renders a real property page with hero/contact details, room-type summary cards (count + from price), and an availability search scoped to that property.
* **Room type search filter** — the room search form gained room-type filter chips; search results are now server-rendered from a single shared card partial used by both lists and search.

= 1.4.1 =
* **Fix** — resolved a fatal error on the **Hotel Booking → Locations** admin page. The Locations controller class was never loaded, so opening the page crashed with "There has been a critical error on this website." The missing include is now loaded.

= 1.4.0 =
* Stable release of the multi-location booking system.
* **Fixes** — rooms created after migration now keep their room-type term in sync so scoped pricing always matches; pricing rules resolve deterministically (dated rules beat always-on rules within a scope).
* **Fixes** — booking links from room lists and search cards carry the room's location; the booking form falls back to the room's own location instead of erroring on a stale `location` parameter.
* **Fixes** — Stripe checkout uses the location's currency and rounds amounts correctly; a confirmed webhook re-claims nights if the hold cleanup freed them mid-payment.
* **Fixes** — location managers are now scoped to their assigned locations across the rooms list, bookings, availability, reports, and admin AJAX actions.
* **Fixes** — schema work (dbDelta) no longer runs on every request; the stored DB version is finalized after upgrading from 1.4.0-beta.1.

= 1.4.0-beta.1 =
* **Multiple locations** — new `shb_location` post type with address, contact, time zone, currency, and check-in/out times.
* **Default location migration** — attaches existing rooms and snapshots location data onto historical bookings; read-only migration report flags orphans, invalid dates, and overlapping legacy bookings.
* **Room-night allocation table** — per-night inventory with a unique `(room_id, stay_date)` index that makes double bookings impossible.
* **Transactional booking creation** — availability revalidation, nightly claims, price snapshots, and booking creation commit or roll back together.
* **Stripe webhook** — payment confirmation now happens only from a verified `checkout.session.completed` webhook; expiring 30-minute holds are cleaned up automatically.
* **Scoped pricing** — rules can target a global scope, location, room type at a location, or a specific room with per-night precedence.
* **Location manager role** — manage rooms/bookings only for assigned locations.
* **Reports** — occupancy and revenue per location and room.
* **Frontend location selector** — search form, room lists, booking forms, confirmation, emails, and calendar events are location-aware.
* **GitHub Actions release workflow** — `Bump Version & Release` builds a ZIP and publishes a GitHub Release that auto-updates the plugin.

= 1.3.1 =
* Added GitHub Release automatic updates using the bundled Plugin Update Checker.
* Optimized availability and pricing lookups and added schema versioning for new indexes.
* Hardened public booking input, Stripe callback ownership checks, calendar download links, and dynamic search-result rendering.

= 1.0.0 =
* Initial release
* Room management with custom post types
* Booking system with availability checking
* Stripe payment integration
* Dynamic pricing rules
* Calendar (.ics) file generation
* Admin dashboard with stats
* Email notifications

== Upgrade Notice ==

= 1.0.0 =
Initial release of Sanctuary Hotel Booking plugin.
