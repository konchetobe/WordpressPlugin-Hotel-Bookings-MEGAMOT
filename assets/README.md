# /assets/ - Static Assets

This directory contains CSS and JavaScript files.

## Structure

```
assets/
├── css/
│   ├── admin.css          # Admin panel styles
│   ├── public.css         # Frontend styles (main)
│   └── blocks-editor.css  # Gutenberg editor styles
└── js/
    ├── admin.js           # Admin JavaScript
    └── public.js          # Frontend JavaScript (main)
```

## CSS Architecture

### CSS Variables (public.css)
```css
:root {
    --shb-primary: #4a7c59;      /* Primary color */
    --shb-accent: #d4a574;       /* Accent color */
    --shb-text: #1a1a2e;         /* Main text */
    --shb-border: #e2e8f0;       /* Borders */
    --shb-bg: #f8fafc;           /* Background */
    --shb-radius: 12px;          /* Border radius */
    --shb-shadow: ...;           /* Box shadow */
}
```

### Key Component Classes
| Class | Component |
|-------|-----------|
| `.shb-room-card` | Room display card |
| `.shb-booking-form-wrap` | Booking form |
| `.shb-booking-confirmation` | Confirmation page |
| `.shb-button` | Button base class |
| `.shb-status-*` | Status badges |
| `.shb-room-location` | Location name on cards |
| `.shb-filter-chip` | Room type filter chip in search |
| `.shb-location-page` | Public location property page wrapper |

## JavaScript Architecture

### Frontend (public.js)
```javascript
// Main entry point
$(document).ready(function() {
    initDatePickers();      // Initialize date pickers
    initRoomSearch();       // Room search (location + room type chips; injects server-rendered card HTML)
    initBookingForm();      // Booking form handling
});
```

Search results are now **server-rendered**: `shb_search_rooms` returns card HTML
built from `templates/room-card.php` (plus the structured `rooms` array), and
`public.js` injects it. The legacy JS card builder remains only as a fallback.
Room type chips toggle a hidden `room_type` input sent with each search.

### Admin (admin.js)
- Pricing rules modal includes a scope selector (Global / Location / Room Type
  at Location / Room) with dependent dropdowns populated server-side.
- Room type defaults prefill: on the room editor, changing the type dropdown
  fetches `shb_admin_get_room_type_defaults` and fills empty fields (all fields
  for a brand-new room). Checkbox "Apply room type defaults to empty fields
  when the type changes" controls the behavior on type change.

### AJAX Pattern
```javascript
$.ajax({
    url: shb_ajax.ajax_url,
    type: 'POST',
    data: {
        action: 'shb_action_name',
        nonce: shb_ajax.nonce,
        // ... data
    },
    success: function(response) {
        if (response.success) {
            // Handle success
        }
    }
});
```

### Localized Variables (available in JS)
```javascript
shb_ajax = {
    ajax_url: '/wp-admin/admin-ajax.php',
    nonce: 'abc123...',
    confirmation_url: '/booking-confirmation/',
    currency_symbol: '€'
}
```

## Common Tasks

### Add CSS component
1. Add styles to `public.css`
2. Follow existing naming convention: `.shb-component-name`

### Add JavaScript functionality
1. Add to `public.js`
2. Initialize in `$(document).ready()`
3. Use `shb_ajax` for AJAX calls
