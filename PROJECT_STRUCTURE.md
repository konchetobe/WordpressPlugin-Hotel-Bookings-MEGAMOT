# Project Structure

```
sanctuary-hotel-booking/
├── sanctuary-hotel-booking.php   # Main plugin entry point
├── CLAUDE.md                     # AI architecture guide
├── SKELETON.md                   # Function signatures (interfaces only)
├── .aiignore                     # Files AI should skip
│
├── includes/                     # Core PHP classes
│   ├── README.md                 # Module documentation
│   ├── class-shb-ajax.php        # AJAX handlers
│   ├── class-shb-availability.php # Availability logic
│   ├── class-shb-assets.php      # CSS/JS enqueuing
│   ├── class-shb-blocks.php      # Gutenberg blocks
│   ├── class-shb-booking.php     # Booking CRUD
│   ├── class-shb-emails.php      # Email sending
│   ├── class-shb-payments.php    # Payment processing
│   ├── class-shb-pricing.php     # Price calculations
│   ├── class-shb-room.php        # Room CRUD
│   └── class-shb-shortcodes.php  # Shortcode handlers
│
├── admin/                        # Admin-only code
│   ├── README.md                 # Module documentation
│   ├── class-shb-admin.php       # Admin menus/pages
│   ├── class-shb-admin-settings.php # Settings management
│   └── views/                    # Admin HTML templates
│       ├── dashboard.php
│       ├── settings.php
│       ├── bookings.php
│       └── rooms.php
│
├── templates/                    # Frontend templates
│   ├── README.md                 # Module documentation
│   ├── booking-form.php          # Booking form
│   ├── booking-confirmation.php  # Confirmation page
│   ├── room-card.php             # Room display card
│   ├── room-search.php           # Search form
│   └── my-bookings.php           # Customer bookings lookup
│
├── assets/                       # Static assets
│   ├── README.md                 # Module documentation
│   ├── css/
│   │   ├── admin.css             # Admin styles
│   │   ├── public.css            # Frontend styles
│   │   └── blocks-editor.css     # Block editor styles
│   └── js/
│       ├── admin.js              # Admin JavaScript
│       └── public.js             # Frontend JavaScript
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
| Change room fields | `includes/class-shb-room.php` |
| Update payment flow | `includes/class-shb-payments.php` |
| Edit booking form UI | `templates/booking-form.php` |
| Change admin settings | `admin/class-shb-admin-settings.php` |
| Modify frontend styles | `assets/css/public.css` |
| Update frontend JS | `assets/js/public.js` |
