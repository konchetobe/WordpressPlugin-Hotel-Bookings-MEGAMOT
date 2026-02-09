=== Sanctuary Hotel Booking ===
Contributors: sanctuaryhotels
Tags: hotel booking, reservation, accommodation, booking system, hotel management
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.3.0
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
