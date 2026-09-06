# Project Structure

```
sanctuary-hotel-booking/
├── sanctuary-hotel-booking.php   # Main plugin entry point
├── ARCHITECTURE.md               # AI architecture guide
├── SKELETON.md                   # Function signatures (interfaces only)
├── MULTI_LOCATION_PLAN.md         # Phased multiple-location/room implementation plan
├── .aiignore                     # Files AI should skip
│
├── .github/
│   └── workflows/
│       └── release.yml           # Bump version + build ZIP + publish GitHub Release
│
├── includes/                     # Core PHP classes
│   ├── README.md                 # Module documentation
│   ├── class-shb-ajax.php        # AJAX handlers
│   ├── class-shb-availability.php # Availability logic
│   ├── class-shb-blocks.php      # Gutenberg blocks
│   ├── class-shb-booking.php     # Booking CRUD (transactional, room nights)
│   ├── class-shb-calendar.php    # ICS generation (location-aware)
│   ├── class-shb-database.php    # Schema + versioned migrations
│   ├── class-shb-location.php    # Location CRUD (shb_location CPT)
│   ├── class-shb-migration.php   # Multi-location data migration
│   ├── class-shb-payments.php    # Payment processing + Stripe webhook
│   ├── class-shb-pricing.php     # Price calculations (scoped per-night)
│   ├── class-shb-roles.php       # Location manager role + caps
│   ├── class-shb-room.php        # Room CRUD (location-aware)
│   ├── class-shb-room-nights.php # Per-night allocation table service
│   ├── class-shb-room-type.php   # Room type defaults template (term meta)
│   ├── class-shb-shortcodes.php  # Shortcode handlers (location-aware)
│   └── class-shb-update-checker.php # GitHub Release update integration
│
├── lib/
│   └── plugin-update-checker/     # Bundled GitHub Release updater dependency
│
├── admin/                        # Admin-only code
│   ├── README.md                 # Module documentation
│   ├── class-shb-admin.php       # Admin menus/pages, Gutenberg-off redirects
│   ├── class-shb-admin-bookings.php # Bookings page
│   ├── class-shb-admin-locations.php # Locations CRUD + hub + delete guard
│   ├── class-shb-admin-migration.php # Migration report
│   ├── class-shb-admin-pricing.php   # Pricing rules page
│   ├── class-shb-admin-reports.php   # Occupancy/revenue reports
│   ├── class-shb-admin-rooms.php     # Rooms list + Room Setup screen
│   ├── class-shb-admin-room-types.php # Room Types manager (create/rename/delete + defaults)
│   ├── class-shb-admin-settings.php # Settings management
│   └── views/                    # Admin HTML templates
│       ├── availability.php
│       ├── bookings.php
│       ├── dashboard.php
│       ├── location-edit.php
│       ├── locations.php
│       ├── migration.php
│       ├── pricing.php
│       ├── reports.php
│       ├── room-edit.php         # Room Setup form (plugin-owned, media uploader)
│       ├── rooms.php             # Rooms list
│       ├── room-types.php
│       └── settings.php
│
├── templates/                    # Frontend templates
│   ├── README.md                 # Module documentation
│   ├── booking-form.php          # Booking form (location-aware)
│   ├── booking-confirmation.php  # Confirmation page (location-aware)
│   ├── location-page.php         # Public location property page content
│   ├── room-card.php             # Canonical room card partial (shared)
│   ├── room-list.php             # Room list (location-aware)
│   ├── room-page.php             # Rich public single-room page
│   ├── room-search.php           # Search form (location, room type, bed, amenities)
│   └── my-bookings.php           # Customer bookings lookup
│
├── assets/                       # Static assets
│   ├── README.md                 # Module documentation
│   ├── css/
│   │   ├── admin.css             # Admin styles
│   │   ├── public.css            # Frontend styles
│   │   └── blocks-editor.css     # Block editor styles
│   └── js/
│       ├── admin.js              # Admin JavaScript (scoped pricing UI)
│       └── public.js             # Frontend JavaScript (location search)
│
├── tests/                        # Manual verification
│   ├── README.md                 # Test checklist
│   └── verify.php                # WP-CLI sanity checks (wp eval-file)
│
├── blocks/                       # Gutenberg block assets
│   └── [block-specific files]
│
└── languages/                    # Translation files
    └── sanctuary-hotel-booking.pot
```

## Quick Reference

| Need to... | Look in... |
|------------|------------|
| Add AJAX endpoint | `includes/class-shb-ajax.php` |
| Modify booking logic | `includes/class-shb-booking.php` |
| Change room fields | `includes/class-shb-room.php`, `admin/class-shb-admin-rooms.php` (Room Setup) |
| Manage room type defaults | `includes/class-shb-room-type.php`, `admin/class-shb-admin-room-types.php` |
| Update payment flow | `includes/class-shb-payments.php` |
| Manage locations | `includes/class-shb-location.php`, `admin/class-shb-admin-locations.php` |
| Allocation / double-booking safety | `includes/class-shb-room-nights.php` |
| Run the migration | `includes/class-shb-migration.php` |
| Release a new version | `.github/workflows/release.yml` (dispatch via API — see ARCHITECTURE.md "Releasing") |
| Edit booking form UI | `templates/booking-form.php` |
| Change admin settings | `admin/class-shb-admin-settings.php` |
| Modify frontend styles | `assets/css/public.css` |
| Update frontend JS | `assets/js/public.js` |
