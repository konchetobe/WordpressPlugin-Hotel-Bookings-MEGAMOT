<div class="wrap shb-admin-wrap">
    <h1><?php _e('Settings', 'sanctuary-hotel-booking'); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('shb_settings', 'shb_settings_nonce'); ?>
        
        <!-- General Settings -->
        <div class="shb-settings-section">
            <h2><?php _e('General Settings', 'sanctuary-hotel-booking'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="shb_currency"><?php _e('Currency', 'sanctuary-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <select name="shb_currency" id="shb_currency">
                            <option value="USD" <?php selected(get_option('shb_currency', 'USD'), 'USD'); ?>>USD - US Dollar</option>
                            <option value="EUR" <?php selected(get_option('shb_currency'), 'EUR'); ?>>EUR - Euro</option>
                            <option value="GBP" <?php selected(get_option('shb_currency'), 'GBP'); ?>>GBP - British Pound</option>
                            <option value="CAD" <?php selected(get_option('shb_currency'), 'CAD'); ?>>CAD - Canadian Dollar</option>
                            <option value="AUD" <?php selected(get_option('shb_currency'), 'AUD'); ?>>AUD - Australian Dollar</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="shb_currency_symbol"><?php _e('Currency Symbol', 'sanctuary-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="shb_currency_symbol" id="shb_currency_symbol" 
                               value="<?php echo esc_attr(get_option('shb_currency_symbol', '$')); ?>" class="small-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="shb_check_in_time"><?php _e('Check-in Time', 'sanctuary-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="time" name="shb_check_in_time" id="shb_check_in_time" 
                               value="<?php echo esc_attr(get_option('shb_check_in_time', '14:00')); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="shb_check_out_time"><?php _e('Check-out Time', 'sanctuary-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="time" name="shb_check_out_time" id="shb_check_out_time" 
                               value="<?php echo esc_attr(get_option('shb_check_out_time', '11:00')); ?>">
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Stripe Settings -->
        <div class="shb-settings-section">
            <h2><?php _e('Stripe Settings', 'sanctuary-hotel-booking'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="shb_stripe_enabled"><?php _e('Enable Stripe', 'sanctuary-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="shb_stripe_enabled" id="shb_stripe_enabled" value="1" 
                               <?php checked(get_option('shb_stripe_enabled', '1'), '1'); ?>>
                        <p class="description"><?php _e('Accept card payments via Stripe (supports Apple Pay & Google Pay)', 'sanctuary-hotel-booking'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="shb_stripe_test_mode"><?php _e('Test Mode', 'sanctuary-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="shb_stripe_test_mode" id="shb_stripe_test_mode" value="1" 
                               <?php checked(get_option('shb_stripe_test_mode', '1'), '1'); ?>>
                        <p class="description"><?php _e('Use Stripe test keys for testing', 'sanctuary-hotel-booking'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="shb_stripe_test_publishable_key"><?php _e('Test Publishable Key', 'sanctuary-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="shb_stripe_test_publishable_key" id="shb_stripe_test_publishable_key" 
                               value="<?php echo esc_attr(get_option('shb_stripe_test_publishable_key', '')); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="shb_stripe_test_secret_key"><?php _e('Test Secret Key', 'sanctuary-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="password" name="shb_stripe_test_secret_key" id="shb_stripe_test_secret_key" 
                               value="<?php echo esc_attr(get_option('shb_stripe_test_secret_key', '')); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="shb_stripe_live_publishable_key"><?php _e('Live Publishable Key', 'sanctuary-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="shb_stripe_live_publishable_key" id="shb_stripe_live_publishable_key" 
                               value="<?php echo esc_attr(get_option('shb_stripe_live_publishable_key', '')); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="shb_stripe_live_secret_key"><?php _e('Live Secret Key', 'sanctuary-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="password" name="shb_stripe_live_secret_key" id="shb_stripe_live_secret_key" 
                               value="<?php echo esc_attr(get_option('shb_stripe_live_secret_key', '')); ?>" class="regular-text">
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- PayPal Settings -->
        <div class="shb-settings-section">
            <h2><?php _e('PayPal Settings', 'sanctuary-hotel-booking'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="shb_paypal_enabled"><?php _e('Enable PayPal', 'sanctuary-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="shb_paypal_enabled" id="shb_paypal_enabled" value="1" 
                               <?php checked(get_option('shb_paypal_enabled', '0'), '1'); ?>>
                        <p class="description"><?php _e('PayPal integration (coming soon)', 'sanctuary-hotel-booking'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Email Settings -->
        <div class="shb-settings-section">
            <h2><?php _e('Email Notifications', 'sanctuary-hotel-booking'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="shb_email_notifications"><?php _e('Enable Notifications', 'sanctuary-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="shb_email_notifications" id="shb_email_notifications" value="1" 
                               <?php checked(get_option('shb_email_notifications', '1'), '1'); ?>>
                        <p class="description"><?php _e('Send email notifications for bookings', 'sanctuary-hotel-booking'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="shb_admin_email"><?php _e('Admin Email', 'sanctuary-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="email" name="shb_admin_email" id="shb_admin_email" 
                               value="<?php echo esc_attr(get_option('shb_admin_email', get_option('admin_email'))); ?>" class="regular-text">
                        <p class="description"><?php _e('Email address to receive booking notifications', 'sanctuary-hotel-booking'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="shb_attach_ics"><?php _e('Attach Calendar (.ics)', 'sanctuary-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="shb_attach_ics" id="shb_attach_ics" value="1" 
                               <?php checked(get_option('shb_attach_ics', '1'), '1'); ?>>
                        <p class="description"><?php _e('Attach .ics calendar file to confirmation emails', 'sanctuary-hotel-booking'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Shortcode Appearance -->
        <div class="shb-settings-section">
            <h2><?php _e('Shortcode Appearance', 'sanctuary-hotel-booking'); ?></h2>
            <p class="description" style="margin-bottom: 16px;">
                <?php _e('Customize the look of your booking shortcodes on the frontend.', 'sanctuary-hotel-booking'); ?>
            </p>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="shb_primary_color"><?php _e('Primary Color', 'sanctuary-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="color" name="shb_primary_color" id="shb_primary_color" 
                               value="<?php echo esc_attr(get_option('shb_primary_color', '#4a7c59')); ?>">
                        <span class="description"><?php _e('Buttons, accents, links', 'sanctuary-hotel-booking'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="shb_accent_color"><?php _e('Accent Color', 'sanctuary-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="color" name="shb_accent_color" id="shb_accent_color" 
                               value="<?php echo esc_attr(get_option('shb_accent_color', '#d4a574')); ?>">
                        <span class="description"><?php _e('Badges, highlights', 'sanctuary-hotel-booking'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="shb_card_style"><?php _e('Room Card Style', 'sanctuary-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <select name="shb_card_style" id="shb_card_style">
                            <option value="default" <?php selected(get_option('shb_card_style', 'default'), 'default'); ?>><?php _e('Default (Rounded Cards)', 'sanctuary-hotel-booking'); ?></option>
                            <option value="flat" <?php selected(get_option('shb_card_style'), 'flat'); ?>><?php _e('Flat (No Shadow)', 'sanctuary-hotel-booking'); ?></option>
                            <option value="bordered" <?php selected(get_option('shb_card_style'), 'bordered'); ?>><?php _e('Bordered', 'sanctuary-hotel-booking'); ?></option>
                            <option value="minimal" <?php selected(get_option('shb_card_style'), 'minimal'); ?>><?php _e('Minimal', 'sanctuary-hotel-booking'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="shb_button_style"><?php _e('Button Style', 'sanctuary-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <select name="shb_button_style" id="shb_button_style">
                            <option value="rounded" <?php selected(get_option('shb_button_style', 'rounded'), 'rounded'); ?>><?php _e('Rounded (Pill)', 'sanctuary-hotel-booking'); ?></option>
                            <option value="square" <?php selected(get_option('shb_button_style'), 'square'); ?>><?php _e('Square', 'sanctuary-hotel-booking'); ?></option>
                            <option value="soft" <?php selected(get_option('shb_button_style'), 'soft'); ?>><?php _e('Soft (Slightly Rounded)', 'sanctuary-hotel-booking'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="shb_font_family"><?php _e('Font Override', 'sanctuary-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="shb_font_family" id="shb_font_family" 
                               value="<?php echo esc_attr(get_option('shb_font_family', '')); ?>" class="regular-text"
                               placeholder="<?php _e('Leave blank to inherit from theme', 'sanctuary-hotel-booking'); ?>">
                        <p class="description"><?php _e('e.g. "Playfair Display", serif', 'sanctuary-hotel-booking'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="shb_custom_css"><?php _e('Custom CSS', 'sanctuary-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <textarea name="shb_custom_css" id="shb_custom_css" rows="6" class="large-text code"
                                  placeholder="<?php _e('.shb-room-card { /* your styles */ }', 'sanctuary-hotel-booking'); ?>"><?php echo esc_textarea(get_option('shb_custom_css', '')); ?></textarea>
                        <p class="description"><?php _e('Add custom CSS to override shortcode styles.', 'sanctuary-hotel-booking'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Shortcode Reference & Previews -->
        <div class="shb-settings-section">
            <h2><?php _e('Shortcodes Reference', 'sanctuary-hotel-booking'); ?></h2>
            <p class="description" style="margin-bottom: 16px;">
                <?php _e('Click each shortcode to see its preview, usage, and CSS selectors for customization.', 'sanctuary-hotel-booking'); ?>
            </p>
            
            <div class="shb-accordion-wrap">
                
                <!-- [shb_room_search] -->
                <div class="shb-accordion-item">
                    <button type="button" class="shb-accordion-toggle" data-target="sc-room-search">
                        <span class="shb-sc-badge"><code>[shb_room_search]</code> <button type="button" class="shb-copy-btn" data-copy="[shb_room_search]"><span class="dashicons dashicons-admin-page" style="font-size:12px;width:12px;height:12px"></span> Copy</button></span>
                        <span class="shb-sc-desc"><?php _e('Search form with date picker + available rooms results', 'sanctuary-hotel-booking'); ?></span>
                        <span class="shb-accordion-arrow dashicons dashicons-arrow-down-alt2"></span>
                    </button>
                    <div class="shb-accordion-body" id="sc-room-search">
                        <div class="shb-sc-columns">
                            <div class="shb-sc-preview">
                                <h4><?php _e('Preview', 'sanctuary-hotel-booking'); ?></h4>
                                <div class="shb-sc-preview-box">
                                    <div style="background:#fff;border-radius:12px;padding:8px;box-shadow:0 1px 3px rgba(0,0,0,.06)">
                                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:8px;align-items:end">
                                            <div><div style="font-size:9px;color:#94a3b8;text-transform:uppercase;margin-bottom:2px;padding-left:8px">CHECK-IN</div><div style="background:#f8fafc;padding:10px;border-radius:6px;font-size:12px;color:#64748b">Select date</div></div>
                                            <div><div style="font-size:9px;color:#94a3b8;text-transform:uppercase;margin-bottom:2px;padding-left:8px">CHECK-OUT</div><div style="background:#f8fafc;padding:10px;border-radius:6px;font-size:12px;color:#64748b">Select date</div></div>
                                            <div><div style="font-size:9px;color:#94a3b8;text-transform:uppercase;margin-bottom:2px;padding-left:8px">GUESTS</div><div style="background:#f8fafc;padding:10px;border-radius:6px;font-size:12px;color:#1a1a2e">1 Guest</div></div>
                                            <div><div style="background:#4a7c59;color:#fff;padding:10px 18px;border-radius:50px;font-size:12px;font-weight:600;text-align:center">Search</div></div>
                                        </div>
                                    </div>
                                    <div style="margin-top:16px;display:grid;grid-template-columns:1fr 1fr;gap:12px">
                                        <div style="background:#fff;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden"><div style="background:#e2e8f0;height:80px;position:relative"><span style="position:absolute;top:6px;left:6px;background:rgba(255,255,255,.9);padding:2px 8px;border-radius:10px;font-size:9px">Standard</span></div><div style="padding:10px"><div style="font-size:12px;font-weight:600">Room Name</div><div style="font-size:10px;color:#64748b;margin-top:4px">2 guests &middot; Queen</div></div></div>
                                        <div style="background:#fff;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden"><div style="background:#e2e8f0;height:80px;position:relative"><span style="position:absolute;top:6px;left:6px;background:rgba(255,255,255,.9);padding:2px 8px;border-radius:10px;font-size:9px">Deluxe</span></div><div style="padding:10px"><div style="font-size:12px;font-weight:600">Room Name</div><div style="font-size:10px;color:#64748b;margin-top:4px">4 guests &middot; King</div></div></div>
                                    </div>
                                </div>
                            </div>
                            <div class="shb-sc-selectors">
                                <h4><?php _e('CSS Selectors', 'sanctuary-hotel-booking'); ?></h4>
                                <div class="shb-code-block"><code>.shb-room-search</code> &mdash; <?php _e('Container', 'sanctuary-hotel-booking'); ?>
<code>.shb-search-form</code> &mdash; <?php _e('Search form wrapper', 'sanctuary-hotel-booking'); ?>
<code>.shb-search-fields</code> &mdash; <?php _e('Fields grid', 'sanctuary-hotel-booking'); ?>
<code>.shb-field</code> &mdash; <?php _e('Individual field', 'sanctuary-hotel-booking'); ?>
<code>.shb-field label</code> &mdash; <?php _e('Field label', 'sanctuary-hotel-booking'); ?>
<code>.shb-field input, .shb-field select</code> &mdash; <?php _e('Field inputs', 'sanctuary-hotel-booking'); ?>
<code>.shb-field-button</code> &mdash; <?php _e('Search button field', 'sanctuary-hotel-booking'); ?>
<code>.shb-search-results</code> &mdash; <?php _e('Results container', 'sanctuary-hotel-booking'); ?>
<code>.shb-results-header</code> &mdash; <?php _e('Results header', 'sanctuary-hotel-booking'); ?>
<code>.shb-results-count</code> &mdash; <?php _e('Results count badge', 'sanctuary-hotel-booking'); ?>
<code>.shb-search-loading</code> &mdash; <?php _e('Loading state', 'sanctuary-hotel-booking'); ?>
<code>.shb-spinner</code> &mdash; <?php _e('Loading spinner', 'sanctuary-hotel-booking'); ?>
<code>.shb-no-results</code> &mdash; <?php _e('No results state', 'sanctuary-hotel-booking'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- [shb_room_list] -->
                <div class="shb-accordion-item">
                    <button type="button" class="shb-accordion-toggle" data-target="sc-room-list">
                        <span class="shb-sc-badge"><code>[shb_room_list]</code> <button type="button" class="shb-copy-btn" data-copy="[shb_room_list]"><span class="dashicons dashicons-admin-page" style="font-size:12px;width:12px;height:12px"></span> Copy</button></span>
                        <span class="shb-sc-desc"><?php _e('Room cards grid with amenities, pricing, and booking links', 'sanctuary-hotel-booking'); ?></span>
                        <span class="shb-accordion-arrow dashicons dashicons-arrow-down-alt2"></span>
                    </button>
                    <div class="shb-accordion-body" id="sc-room-list">
                        <div class="shb-sc-usage">
                            <h4><?php _e('Usage', 'sanctuary-hotel-booking'); ?></h4>
                            <table class="shb-sc-attrs">
                                <tr><td><code>columns</code></td><td><?php _e('Grid columns: 2, 3, or 4 (default: 3)', 'sanctuary-hotel-booking'); ?></td></tr>
                                <tr><td><code>type</code></td><td><?php _e('Filter by room type slug: standard, deluxe, suite', 'sanctuary-hotel-booking'); ?></td></tr>
                                <tr><td><code>limit</code></td><td><?php _e('Max rooms to show (default: all)', 'sanctuary-hotel-booking'); ?></td></tr>
                            </table>
                            <p style="margin-top:8px"><code>[shb_room_list columns="2" type="suite" limit="4"]</code> <button type="button" class="shb-copy-btn" data-copy='[shb_room_list columns="2" type="suite" limit="4"]'><span class="dashicons dashicons-admin-page" style="font-size:12px;width:12px;height:12px"></span> Copy</button></p>
                        </div>
                        <div class="shb-sc-columns">
                            <div class="shb-sc-preview">
                                <h4><?php _e('Preview', 'sanctuary-hotel-booking'); ?></h4>
                                <div class="shb-sc-preview-box">
                                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px">
                                        <?php for ($i = 0; $i < 3; $i++) : ?>
                                        <div style="background:#fff;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden">
                                            <div style="background:linear-gradient(135deg,#e2e8f0,#cbd5e1);height:70px;position:relative">
                                                <span style="position:absolute;top:5px;left:5px;background:rgba(255,255,255,.9);padding:2px 7px;border-radius:8px;font-size:8px;font-weight:600"><?php echo array('Standard', 'Deluxe', 'Suite')[$i]; ?></span>
                                                <span style="position:absolute;bottom:5px;right:5px;background:#4a7c59;color:#fff;padding:3px 8px;border-radius:6px;font-size:10px;font-weight:700">$<?php echo array('120', '180', '350')[$i]; ?></span>
                                            </div>
                                            <div style="padding:8px">
                                                <div style="font-size:11px;font-weight:600;margin-bottom:4px"><?php echo array('Standard Room', 'Deluxe Room', 'Premium Suite')[$i]; ?></div>
                                                <div style="display:flex;gap:6px;margin-bottom:4px"><span style="font-size:8px;color:#64748b">2 guests</span><span style="font-size:8px;color:#64748b">Queen</span></div>
                                                <div style="display:flex;gap:3px;margin-bottom:6px"><span style="background:#f8fafc;padding:1px 5px;border-radius:3px;font-size:7px;color:#64748b">WiFi</span><span style="background:#f8fafc;padding:1px 5px;border-radius:3px;font-size:7px;color:#64748b">TV</span><span style="background:#d4a574;color:#fff;padding:1px 5px;border-radius:3px;font-size:7px">+3</span></div>
                                                <div style="display:flex;gap:4px"><span style="border:1px solid #4a7c59;color:#4a7c59;padding:3px 8px;border-radius:10px;font-size:8px;flex:1;text-align:center">Details</span><span style="background:#4a7c59;color:#fff;padding:3px 8px;border-radius:10px;font-size:8px;flex:1;text-align:center">Book</span></div>
                                            </div>
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="shb-sc-selectors">
                                <h4><?php _e('CSS Selectors', 'sanctuary-hotel-booking'); ?></h4>
                                <div class="shb-code-block"><code>.shb-room-list</code> &mdash; <?php _e('Container', 'sanctuary-hotel-booking'); ?>
<code>.shb-rooms-grid</code> &mdash; <?php _e('Grid wrapper', 'sanctuary-hotel-booking'); ?>
<code>.shb-columns-2, .shb-columns-3, .shb-columns-4</code> &mdash; <?php _e('Column variants', 'sanctuary-hotel-booking'); ?>
<code>.shb-room-card</code> &mdash; <?php _e('Individual card', 'sanctuary-hotel-booking'); ?>
<code>.shb-room-image</code> &mdash; <?php _e('Card image container', 'sanctuary-hotel-booking'); ?>
<code>.shb-room-image img</code> &mdash; <?php _e('Card image', 'sanctuary-hotel-booking'); ?>
<code>.shb-room-type-badge</code> &mdash; <?php _e('Type badge overlay', 'sanctuary-hotel-booking'); ?>
<code>.shb-room-price-tag</code> &mdash; <?php _e('Price overlay', 'sanctuary-hotel-booking'); ?>
<code>.shb-price-amount</code> &mdash; <?php _e('Price number', 'sanctuary-hotel-booking'); ?>
<code>.shb-room-content</code> &mdash; <?php _e('Card body', 'sanctuary-hotel-booking'); ?>
<code>.shb-room-title</code> &mdash; <?php _e('Room name', 'sanctuary-hotel-booking'); ?>
<code>.shb-room-specs</code> &mdash; <?php _e('Specs row (guests, bed, size)', 'sanctuary-hotel-booking'); ?>
<code>.shb-spec</code> &mdash; <?php _e('Individual spec', 'sanctuary-hotel-booking'); ?>
<code>.shb-room-amenities-strip</code> &mdash; <?php _e('Amenity tags row', 'sanctuary-hotel-booking'); ?>
<code>.shb-amenity-tag</code> &mdash; <?php _e('Amenity tag', 'sanctuary-hotel-booking'); ?>
<code>.shb-amenity-more</code> &mdash; <?php _e('+N more badge', 'sanctuary-hotel-booking'); ?>
<code>.shb-room-excerpt</code> &mdash; <?php _e('Description', 'sanctuary-hotel-booking'); ?>
<code>.shb-room-actions</code> &mdash; <?php _e('Action buttons row', 'sanctuary-hotel-booking'); ?>
<code>.shb-empty-state</code> &mdash; <?php _e('No rooms state', 'sanctuary-hotel-booking'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- [shb_booking_form] -->
                <div class="shb-accordion-item">
                    <button type="button" class="shb-accordion-toggle" data-target="sc-booking-form">
                        <span class="shb-sc-badge"><code>[shb_booking_form]</code> <button type="button" class="shb-copy-btn" data-copy="[shb_booking_form]"><span class="dashicons dashicons-admin-page" style="font-size:12px;width:12px;height:12px"></span> Copy</button></span>
                        <span class="shb-sc-desc"><?php _e('Full booking form — auto-created on a "Book a Room" page. Used by Book Now buttons.', 'sanctuary-hotel-booking'); ?></span>
                        <span class="shb-accordion-arrow dashicons dashicons-arrow-down-alt2"></span>
                    </button>
                    <div class="shb-accordion-body" id="sc-booking-form">
                        <div class="shb-sc-usage">
                            <h4><?php _e('Usage', 'sanctuary-hotel-booking'); ?></h4>
                            <table class="shb-sc-attrs">
                                <tr><td><code>room_id</code></td><td><?php _e('Room post ID (also reads from ?room_id= URL parameter)', 'sanctuary-hotel-booking'); ?></td></tr>
                            </table>
                            <p style="margin-top:8px"><code>[shb_booking_form room_id="42"]</code> <button type="button" class="shb-copy-btn" data-copy='[shb_booking_form room_id="42"]'><span class="dashicons dashicons-admin-page" style="font-size:12px;width:12px;height:12px"></span> Copy</button></p>
                        </div>
                        <div class="shb-sc-columns">
                            <div class="shb-sc-preview">
                                <h4><?php _e('Preview', 'sanctuary-hotel-booking'); ?></h4>
                                <div class="shb-sc-preview-box">
                                    <div style="display:grid;grid-template-columns:1fr 140px;gap:12px">
                                        <div>
                                            <div style="font-size:14px;font-weight:700;margin-bottom:10px">Complete Your Booking</div>
                                            <div style="background:#fff;border-radius:8px;padding:10px;box-shadow:0 1px 3px rgba(0,0,0,.06);margin-bottom:8px"><div style="font-size:10px;font-weight:600;margin-bottom:6px">Stay Details</div><div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px"><div style="background:#f8fafc;padding:6px;border-radius:4px;font-size:8px;color:#64748b">Check-in</div><div style="background:#f8fafc;padding:6px;border-radius:4px;font-size:8px;color:#64748b">Check-out</div><div style="background:#f8fafc;padding:6px;border-radius:4px;font-size:8px;color:#64748b">Guests</div></div></div>
                                            <div style="background:#fff;border-radius:8px;padding:10px;box-shadow:0 1px 3px rgba(0,0,0,.06);margin-bottom:8px"><div style="font-size:10px;font-weight:600;margin-bottom:6px">Guest Information</div><div style="display:grid;grid-template-columns:1fr 1fr;gap:6px"><div style="background:#f8fafc;padding:6px;border-radius:4px;font-size:8px;color:#64748b">First Name</div><div style="background:#f8fafc;padding:6px;border-radius:4px;font-size:8px;color:#64748b">Last Name</div><div style="background:#f8fafc;padding:6px;border-radius:4px;font-size:8px;color:#64748b">Email</div><div style="background:#f8fafc;padding:6px;border-radius:4px;font-size:8px;color:#64748b">Phone</div></div></div>
                                            <div style="background:#4a7c59;color:#fff;padding:8px;border-radius:50px;font-size:10px;font-weight:600;text-align:center">Proceed to Payment</div>
                                        </div>
                                        <div style="background:#fff;border-radius:8px;padding:10px;box-shadow:0 1px 3px rgba(0,0,0,.06)">
                                            <div style="font-size:10px;font-weight:600;margin-bottom:8px">Summary</div>
                                            <div style="background:#e2e8f0;height:40px;border-radius:4px;margin-bottom:6px"></div>
                                            <div style="display:flex;justify-content:space-between;font-size:8px;color:#64748b;margin-bottom:4px"><span>$120 x 3 nights</span><span>$360</span></div>
                                            <div style="display:flex;justify-content:space-between;font-size:10px;font-weight:700;border-top:1px solid #e2e8f0;padding-top:6px;margin-top:4px"><span>Total</span><span>$360.00</span></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="shb-sc-selectors">
                                <h4><?php _e('CSS Selectors', 'sanctuary-hotel-booking'); ?></h4>
                                <div class="shb-code-block"><code>.shb-booking-form-wrap</code> &mdash; <?php _e('Container', 'sanctuary-hotel-booking'); ?>
<code>.shb-booking-grid</code> &mdash; <?php _e('Two-column layout', 'sanctuary-hotel-booking'); ?>
<code>.shb-booking-form-section</code> &mdash; <?php _e('Form column', 'sanctuary-hotel-booking'); ?>
<code>.shb-form-section</code> &mdash; <?php _e('Form section card', 'sanctuary-hotel-booking'); ?>
<code>.shb-form-row</code> &mdash; <?php _e('Form fields row', 'sanctuary-hotel-booking'); ?>
<code>.shb-form-field</code> &mdash; <?php _e('Single field', 'sanctuary-hotel-booking'); ?>
<code>.shb-form-field label</code> &mdash; <?php _e('Field label', 'sanctuary-hotel-booking'); ?>
<code>.shb-form-field input/select/textarea</code> &mdash; <?php _e('Field inputs', 'sanctuary-hotel-booking'); ?>
<code>.shb-payment-options</code> &mdash; <?php _e('Payment methods list', 'sanctuary-hotel-booking'); ?>
<code>.shb-payment-option</code> &mdash; <?php _e('Payment option card', 'sanctuary-hotel-booking'); ?>
<code>.shb-booking-summary-section</code> &mdash; <?php _e('Summary column', 'sanctuary-hotel-booking'); ?>
<code>.shb-booking-summary</code> &mdash; <?php _e('Sticky summary card', 'sanctuary-hotel-booking'); ?>
<code>.shb-summary-room</code> &mdash; <?php _e('Room info in summary', 'sanctuary-hotel-booking'); ?>
<code>.shb-summary-row</code> &mdash; <?php _e('Price row', 'sanctuary-hotel-booking'); ?>
<code>.shb-summary-row.shb-total</code> &mdash; <?php _e('Total row', 'sanctuary-hotel-booking'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- [shb_booking_confirmation] -->
                <div class="shb-accordion-item">
                    <button type="button" class="shb-accordion-toggle" data-target="sc-confirmation">
                        <span class="shb-sc-badge"><code>[shb_booking_confirmation]</code> <button type="button" class="shb-copy-btn" data-copy="[shb_booking_confirmation]"><span class="dashicons dashicons-admin-page" style="font-size:12px;width:12px;height:12px"></span> Copy</button></span>
                        <span class="shb-sc-desc"><?php _e('Post-booking confirmation page with details and calendar download', 'sanctuary-hotel-booking'); ?></span>
                        <span class="shb-accordion-arrow dashicons dashicons-arrow-down-alt2"></span>
                    </button>
                    <div class="shb-accordion-body" id="sc-confirmation">
                        <div class="shb-sc-usage">
                            <h4><?php _e('Usage', 'sanctuary-hotel-booking'); ?></h4>
                            <p><?php _e('Reads booking_ref or booking_id from URL parameters automatically.', 'sanctuary-hotel-booking'); ?></p>
                        </div>
                        <div class="shb-sc-columns">
                            <div class="shb-sc-preview">
                                <h4><?php _e('Preview', 'sanctuary-hotel-booking'); ?></h4>
                                <div class="shb-sc-preview-box" style="text-align:center">
                                    <div style="background:#fff;border-radius:10px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,.06);margin-bottom:10px">
                                        <div style="color:#16a34a;font-size:28px;margin-bottom:6px">&#10003;</div>
                                        <div style="font-size:14px;font-weight:700">Booking Confirmed!</div>
                                        <div style="font-size:10px;color:#64748b">Your reservation has been processed.</div>
                                    </div>
                                    <div style="background:#fff;border-radius:10px;padding:14px;box-shadow:0 1px 3px rgba(0,0,0,.06);text-align:left;margin-bottom:10px">
                                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
                                            <div style="background:#f8fafc;padding:8px;border-radius:6px"><div style="font-size:7px;color:#94a3b8;text-transform:uppercase">Reference</div><div style="font-size:10px;font-weight:600;font-family:monospace">SHB-A1B2C3</div></div>
                                            <div style="background:#f8fafc;padding:8px;border-radius:6px"><div style="font-size:7px;color:#94a3b8;text-transform:uppercase">Room</div><div style="font-size:10px;font-weight:600">Deluxe Room</div></div>
                                            <div style="background:#f8fafc;padding:8px;border-radius:6px"><div style="font-size:7px;color:#94a3b8;text-transform:uppercase">Check-in</div><div style="font-size:10px;font-weight:600">2025-01-15</div></div>
                                            <div style="background:linear-gradient(135deg,rgba(74,124,89,.08),rgba(74,124,89,.03));padding:8px;border-radius:6px"><div style="font-size:7px;color:#94a3b8;text-transform:uppercase">Total</div><div style="font-size:12px;font-weight:700;color:#4a7c59">$540.00</div></div>
                                        </div>
                                    </div>
                                    <div style="display:flex;gap:6px;justify-content:center"><span style="border:1px solid #4a7c59;color:#4a7c59;padding:5px 14px;border-radius:50px;font-size:9px;font-weight:600">Add to Calendar</span><span style="background:#4a7c59;color:#fff;padding:5px 14px;border-radius:50px;font-size:9px;font-weight:600">Return Home</span></div>
                                </div>
                            </div>
                            <div class="shb-sc-selectors">
                                <h4><?php _e('CSS Selectors', 'sanctuary-hotel-booking'); ?></h4>
                                <div class="shb-code-block"><code>.shb-booking-confirmation</code> &mdash; <?php _e('Container', 'sanctuary-hotel-booking'); ?>
<code>.shb-confirmation-card</code> &mdash; <?php _e('Top status card', 'sanctuary-hotel-booking'); ?>
<code>.shb-confirmation-icon</code> &mdash; <?php _e('Status icon', 'sanctuary-hotel-booking'); ?>
<code>.shb-confirmation-icon.shb-success</code> &mdash; <?php _e('Success state', 'sanctuary-hotel-booking'); ?>
<code>.shb-confirmation-icon.shb-pending</code> &mdash; <?php _e('Pending state', 'sanctuary-hotel-booking'); ?>
<code>.shb-booking-details</code> &mdash; <?php _e('Details card', 'sanctuary-hotel-booking'); ?>
<code>.shb-detail-header</code> &mdash; <?php _e('Details header', 'sanctuary-hotel-booking'); ?>
<code>.shb-details-grid</code> &mdash; <?php _e('Details grid', 'sanctuary-hotel-booking'); ?>
<code>.shb-detail-item</code> &mdash; <?php _e('Detail item', 'sanctuary-hotel-booking'); ?>
<code>.shb-detail-highlight</code> &mdash; <?php _e('Highlighted item (total)', 'sanctuary-hotel-booking'); ?>
<code>.shb-detail-label</code> &mdash; <?php _e('Detail label', 'sanctuary-hotel-booking'); ?>
<code>.shb-detail-value</code> &mdash; <?php _e('Detail value', 'sanctuary-hotel-booking'); ?>
<code>.shb-booking-ref</code> &mdash; <?php _e('Reference number', 'sanctuary-hotel-booking'); ?>
<code>.shb-total-paid</code> &mdash; <?php _e('Total amount', 'sanctuary-hotel-booking'); ?>
<code>.shb-confirmation-actions</code> &mdash; <?php _e('Action buttons', 'sanctuary-hotel-booking'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- [shb_my_bookings] -->
                <div class="shb-accordion-item">
                    <button type="button" class="shb-accordion-toggle" data-target="sc-my-bookings">
                        <span class="shb-sc-badge"><code>[shb_my_bookings]</code> <button type="button" class="shb-copy-btn" data-copy="[shb_my_bookings]"><span class="dashicons dashicons-admin-page" style="font-size:12px;width:12px;height:12px"></span> Copy</button></span>
                        <span class="shb-sc-desc"><?php _e('Guest booking lookup by email with booking cards', 'sanctuary-hotel-booking'); ?></span>
                        <span class="shb-accordion-arrow dashicons dashicons-arrow-down-alt2"></span>
                    </button>
                    <div class="shb-accordion-body" id="sc-my-bookings">
                        <div class="shb-sc-columns">
                            <div class="shb-sc-preview">
                                <h4><?php _e('Preview', 'sanctuary-hotel-booking'); ?></h4>
                                <div class="shb-sc-preview-box">
                                    <div style="font-size:14px;font-weight:700;margin-bottom:10px">My Bookings</div>
                                    <div style="background:#fff;border-radius:10px;padding:16px;box-shadow:0 1px 3px rgba(0,0,0,.06);text-align:center;margin-bottom:10px">
                                        <div style="color:#94a3b8;font-size:20px;margin-bottom:6px">&#x1F50D;</div>
                                        <div style="font-size:11px;color:#64748b;margin-bottom:10px">Enter your email to view bookings</div>
                                        <div style="display:flex;gap:6px;max-width:300px;margin:0 auto"><div style="flex:1;background:#f8fafc;padding:8px;border-radius:6px;border:1px solid #e2e8f0;font-size:10px;color:#94a3b8">your@email.com</div><div style="background:#4a7c59;color:#fff;padding:8px 14px;border-radius:50px;font-size:10px;font-weight:600;white-space:nowrap">Find</div></div>
                                    </div>
                                    <div style="background:#fff;border-radius:10px;padding:12px;box-shadow:0 1px 3px rgba(0,0,0,.06);margin-bottom:6px">
                                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px"><div style="display:flex;align-items:center;gap:6px"><span style="font-family:monospace;font-size:10px;font-weight:600">SHB-X1Y2Z3</span><span style="background:#d1fae5;color:#065f46;padding:1px 6px;border-radius:8px;font-size:8px;font-weight:600">Confirmed</span></div><span style="font-size:12px;font-weight:700;color:#4a7c59">$360.00</span></div>
                                        <div style="font-size:9px;color:#64748b">Deluxe Room &middot; Jan 15 &mdash; Jan 18</div>
                                    </div>
                                </div>
                            </div>
                            <div class="shb-sc-selectors">
                                <h4><?php _e('CSS Selectors', 'sanctuary-hotel-booking'); ?></h4>
                                <div class="shb-code-block"><code>.shb-my-bookings</code> &mdash; <?php _e('Container', 'sanctuary-hotel-booking'); ?>
<code>.shb-lookup-card</code> &mdash; <?php _e('Email lookup card', 'sanctuary-hotel-booking'); ?>
<code>.shb-lookup-form</code> &mdash; <?php _e('Lookup form', 'sanctuary-hotel-booking'); ?>
<code>.shb-lookup-row</code> &mdash; <?php _e('Input + button row', 'sanctuary-hotel-booking'); ?>
<code>.shb-bookings-list</code> &mdash; <?php _e('Bookings list', 'sanctuary-hotel-booking'); ?>
<code>.shb-booking-item</code> &mdash; <?php _e('Booking card', 'sanctuary-hotel-booking'); ?>
<code>.shb-booking-item-header</code> &mdash; <?php _e('Card header (ref + price)', 'sanctuary-hotel-booking'); ?>
<code>.shb-booking-item-ref</code> &mdash; <?php _e('Ref + status group', 'sanctuary-hotel-booking'); ?>
<code>.shb-booking-item-price</code> &mdash; <?php _e('Price', 'sanctuary-hotel-booking'); ?>
<code>.shb-booking-item-body</code> &mdash; <?php _e('Card body details', 'sanctuary-hotel-booking'); ?>
<code>.shb-booking-item-detail</code> &mdash; <?php _e('Detail line', 'sanctuary-hotel-booking'); ?>
<code>.shb-booking-item-actions</code> &mdash; <?php _e('Action buttons', 'sanctuary-hotel-booking'); ?>
<code>.shb-status</code> &mdash; <?php _e('Status badge', 'sanctuary-hotel-booking'); ?>
<code>.shb-status-confirmed</code> &mdash; <?php _e('Confirmed status', 'sanctuary-hotel-booking'); ?>
<code>.shb-status-pending</code> &mdash; <?php _e('Pending status', 'sanctuary-hotel-booking'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div><!-- .shb-accordion-wrap -->
        </div>
        
        <p class="submit">
            <input type="submit" name="shb_save_settings" class="button button-primary" 
                   value="<?php _e('Save Settings', 'sanctuary-hotel-booking'); ?>">
        </p>
    </form>
</div>
