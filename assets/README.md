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

## JavaScript Architecture

### Frontend (public.js)
```javascript
// Main entry point
$(document).ready(function() {
    initDatePickers();      // Initialize date pickers
    initRoomSearch();       // Room search functionality
    initBookingForm();      // Booking form handling
});
```

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
