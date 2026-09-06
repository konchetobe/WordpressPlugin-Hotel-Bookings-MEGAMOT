/**
 * Sanctuary Hotel Booking - Admin JavaScript
 */

(function($) {
    'use strict';

    // Booking status update
    function initBookingStatusUpdate() {
        $(document).on('change', '.shb-status-select', function() {
            var $select = $(this);
            var bookingId = $select.data('booking-id');
            var status = $select.val();
            
            $.ajax({
                url: shb_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'shb_admin_update_booking_status',
                    nonce: shb_admin.nonce,
                    booking_id: bookingId,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        var $row = $select.closest('tr');
                        $row.css('background-color', '#d1fae5');
                        setTimeout(function() {
                            $row.css('background-color', '');
                        }, 1500);
                    } else {
                        alert(response.data.message || 'Failed to update status');
                    }
                },
                error: function() {
                    alert('An error occurred');
                }
            });
        });
    }

    // Send booking email
    function initSendEmailButton() {
        $(document).on('click', '.shb-send-email', function() {
            var $button = $(this);
            var bookingId = $button.data('booking-id');
            
            if (!confirm('Send booking details email to the guest?')) return;
            
            $button.prop('disabled', true);
            var originalHtml = $button.html();
            $button.html('<span class="dashicons dashicons-update-alt" style="font-size:14px;width:14px;height:14px;vertical-align:middle;animation:rotation 1s infinite linear;"></span> Sending...');
            
            $.ajax({
                url: shb_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'shb_admin_send_booking_email',
                    nonce: shb_admin.nonce,
                    booking_id: bookingId
                },
                success: function(response) {
                    if (response.success) {
                        $button.html('<span class="dashicons dashicons-yes" style="font-size:14px;width:14px;height:14px;vertical-align:middle;color:green;"></span> Sent!');
                        setTimeout(function() {
                            $button.html(originalHtml).prop('disabled', false);
                        }, 2000);
                    } else {
                        alert(response.data.message || 'Failed to send email');
                        $button.html(originalHtml).prop('disabled', false);
                    }
                },
                error: function() {
                    alert('An error occurred sending the email');
                    $button.html(originalHtml).prop('disabled', false);
                }
            });
        });
    }

    // Pricing rules modal
    function initPricingRulesModal() {
        var $modal = $('#shb-rule-form-modal');
        var $form = $('#shb-rule-form');
        
        if (!$modal.length) return;
        
        // Open modal for new rule
        $(document).on('click', '#shb-add-rule', function() {
            $form[0].reset();
            $('#rule_id').val('');
            $('#shb-rule-form-title').text('Add Pricing Rule');
            $('#rule_scope').val('');
            $('#rule_scope_location_wrap, #rule_scope_room_type_wrap, #rule_scope_room_wrap').hide();
            $modal.fadeIn(200);
        });
        
        // Scope selector toggles the dependent dropdowns
        $(document).on('change', '#rule_scope', function() {
            var scope = $(this).val();
            $('#rule_scope_location_wrap, #rule_scope_room_type_wrap, #rule_scope_room_wrap').hide();
            if (scope === 'location' || scope === 'room_type') {
                $('#rule_scope_location_wrap').show();
            }
            if (scope === 'room_type') {
                $('#rule_scope_room_type_wrap').show();
            }
            if (scope === 'room') {
                $('#rule_scope_room_wrap').show();
            }
        });
        
        // Open modal for edit
        $(document).on('click', '.shb-edit-rule', function() {
            var rule = $(this).data('rule');
            if (typeof rule === 'string') {
                try { rule = JSON.parse(rule); } catch(e) { return; }
            }
            $('#rule_id').val(rule.id);
            $('#rule_name').val(rule.name);
            $('#rule_type').val(rule.rule_type);
            $('#rule_room_type').val(rule.room_type || '');
            $('#rule_start_date').val(rule.start_date || '');
            $('#rule_end_date').val(rule.end_date || '');
            $('#rule_multiplier').val(rule.multiplier);
            $('#rule_is_active').prop('checked', rule.is_active == 1);
            
            var scope = '';
            if (rule.room_id) { scope = 'room'; }
            else if (rule.room_type_term_id) { scope = 'room_type'; }
            else if (rule.location_id) { scope = 'location'; }
            $('#rule_scope').val(scope).trigger('change');
            $('#rule_location_id').val(rule.location_id || '');
            $('#rule_room_type_term').val(rule.room_type_term_id || '');
            $('#rule_room_id').val(rule.room_id || '');
            
            $('#shb-rule-form-title').text('Edit Pricing Rule');
            $modal.fadeIn(200);
        });
        
        // Close modal
        $(document).on('click', '.shb-modal-close', function() {
            $(this).closest('.shb-modal').fadeOut(200);
        });
        
        $modal.on('click', function(e) {
            if (e.target === this) {
                $modal.fadeOut(200);
            }
        });
        
        // Save rule
        $form.on('submit', function(e) {
            e.preventDefault();
            
            var $button = $form.find('button[type="submit"]');
            $button.prop('disabled', true).text('Saving...');
            
            $.ajax({
                url: shb_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'shb_admin_save_pricing_rule',
                    nonce: shb_admin.nonce,
                    rule_id: $('#rule_id').val(),
                    name: $('#rule_name').val(),
                    rule_type: $('#rule_type').val(),
                    room_type: $('#rule_room_type').val(),
                    location_id: $('#rule_location_id').val(),
                    room_id: $('#rule_room_id').val(),
                    room_type_term_id: $('#rule_room_type_term').val(),
                    start_date: $('#rule_start_date').val(),
                    end_date: $('#rule_end_date').val(),
                    multiplier: $('#rule_multiplier').val(),
                    is_active: $('#rule_is_active').is(':checked') ? 1 : 0
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message || 'Failed to save rule');
                        $button.prop('disabled', false).text('Save Rule');
                    }
                },
                error: function() {
                    alert('An error occurred');
                    $button.prop('disabled', false).text('Save Rule');
                }
            });
        });
        
        // Delete rule
        $(document).on('click', '.shb-delete-rule', function() {
            if (!confirm('Are you sure you want to delete this rule?')) return;
            
            var ruleId = $(this).data('rule-id');
            var $row = $(this).closest('tr');
            
            $.ajax({
                url: shb_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'shb_admin_delete_pricing_rule',
                    nonce: shb_admin.nonce,
                    rule_id: ruleId
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        alert(response.data.message || 'Failed to delete rule');
                    }
                },
                error: function() {
                    alert('An error occurred');
                }
            });
        });
    }

    // Availability blocks modal
    function initAvailabilityModal() {
        var $modal = $('#shb-block-form-modal');
        var $form = $('#shb-block-form');
        
        if (!$modal.length) return;
        
        // Open modal
        $(document).on('click', '#shb-add-block', function() {
            $form[0].reset();
            $modal.fadeIn(200);
        });
        
        // Close modal
        $(document).on('click', '.shb-modal-close', function() {
            $(this).closest('.shb-modal').fadeOut(200);
        });
        
        $modal.on('click', function(e) {
            if (e.target === this) {
                $modal.fadeOut(200);
            }
        });
        
        // Save block
        $form.on('submit', function(e) {
            e.preventDefault();
            
            var $button = $form.find('button[type="submit"]');
            $button.prop('disabled', true).text('Saving...');
            
            $.ajax({
                url: shb_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'shb_admin_create_availability_block',
                    nonce: shb_admin.nonce,
                    room_id: $('#block_room_id').val(),
                    start_date: $('#block_start_date').val(),
                    end_date: $('#block_end_date').val(),
                    reason: $('#block_reason').val()
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message || 'Failed to create block');
                        $button.prop('disabled', false).text('Block Dates');
                    }
                },
                error: function() {
                    alert('An error occurred');
                    $button.prop('disabled', false).text('Block Dates');
                }
            });
        });
        
        // Delete block
        $(document).on('click', '.shb-delete-block', function() {
            if (!confirm('Are you sure you want to remove this block?')) return;
            
            var blockId = $(this).data('block-id');
            var $row = $(this).closest('tr');
            
            $.ajax({
                url: shb_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'shb_admin_delete_availability_block',
                    nonce: shb_admin.nonce,
                    block_id: blockId
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        alert(response.data.message || 'Failed to remove block');
                    }
                },
                error: function() {
                    alert('An error occurred');
                }
            });
        });
    }

    // Room type defaults prefill in the room meta box
    function initRoomTypeDefaultsPrefill() {
        var $typeSelect = $('#shb_room_type');
        if (!$typeSelect.length) return;

        // A brand-new room has no positive post ID yet.
        function isNewRoom() {
            var id = parseInt($('#post_ID').val(), 10);
            return !(id > 0);
        }

        // Fill a text/number/select field only when it is empty, unless we are
        // prefilling a brand-new room (no post ID yet) — then use the defaults
        // as initial values.
        function applyDefault($field, value) {
            var newRoom = isNewRoom();
            if (value === '' || value === null || value === undefined) return;
            if (newRoom) {
                if ($field.is(':checkbox')) {
                    $field.prop('checked', value === 1 || value === '1' || value === true);
                } else if ($field.is(':radio')) {
                    $field.filter('[value="' + $('<span>').text(value).html() + '"]').prop('checked', true);
                } else if ($field.is('select')) {
                    $field.val(String(value));
                } else {
                    $field.val(value);
                }
                return;
            }
            // Existing room: never clobber manually-entered values.
            if ($field.is(':checkbox')) {
                if (!$field.prop('checked') && (value === 1 || value === '1' || value === true)) {
                    $field.prop('checked', true);
                }
            } else if ($field.is('select')) {
                if (!$field.val()) {
                    $field.val(String(value));
                }
            } else if (!$field.val()) {
                $field.val(value);
            }
        }

        function applyDefaultsToFields(defaults) {
            if (!defaults) return;
            var map = {
                'type_base_price': { selector: '#shb_base_price', key: '_shb_type_base_price' },
                'type_max_guests': { selector: '#shb_max_guests', key: '_shb_type_max_guests' },
                'type_bed_type': { selector: '#shb_bed_type', key: '_shb_type_bed_type' },
                'type_room_size': { selector: '#shb_room_size', key: '_shb_type_room_size' },
                'type_floor': { selector: '#shb_floor', key: '_shb_type_floor' },
                'type_min_nights': { selector: '#shb_min_nights', key: '_shb_type_min_nights' },
                'type_max_nights': { selector: '#shb_max_nights', key: '_shb_type_max_nights' },
                'type_cancellation_policy': { selector: '#shb_cancellation_policy', key: '_shb_type_cancellation_policy' }
            };
            $.each(map, function (name, cfg) {
                var value = defaults[cfg.key];
                if (value === undefined || value === null) return;
                var $field = $(cfg.selector);
                if ($field.length) applyDefault($field, value);
            });

            // Amenities are checkboxes; check those included in the defaults.
            if (defaults._shb_type_amenities && defaults._shb_type_amenities.length) {
                $('input[name="shb_amenities[]"]').each(function () {
                    var $cb = $(this);
                    var inDefaults = defaults._shb_type_amenities.indexOf($cb.val()) !== -1;
                    if (inDefaults) {
                        $cb.prop('checked', true);
                    } else if (isNewRoom()) {
                        $cb.prop('checked', false);
                    }
                });
            }
        }

        // Apply the current type's defaults on load for a new room so the form
        // opens pre-filled; existing rooms are left untouched.
        if (isNewRoom() && $typeSelect.val()) {
            $.ajax({
                url: shb_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'shb_admin_get_room_type_defaults',
                    nonce: shb_admin.nonce,
                    room_type: $typeSelect.val()
                },
                success: function (response) {
                    if (response.success && response.data && response.data.defaults) {
                        applyDefaultsToFields(response.data.defaults);
                    }
                }
            });
        }

        $typeSelect.on('change', function () {
            if (!$('#shb_apply_type_defaults').is(':checked')) return;
            var roomType = $(this).val();
            if (!roomType) return;
            $.ajax({
                url: shb_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'shb_admin_get_room_type_defaults',
                    nonce: shb_admin.nonce,
                    room_type: roomType
                },
                success: function (response) {
                    if (response.success && response.data && response.data.defaults) {
                        applyDefaultsToFields(response.data.defaults);
                    }
                }
            });
        });
    }

    // Initialize on document ready
    $(document).ready(function() {
        initBookingStatusUpdate();
        initSendEmailButton();
        initPricingRulesModal();
        initAvailabilityModal();
        initRoomTypeDefaultsPrefill();
        
        // Accordion toggle for shortcode reference
        $(document).on('click', '.shb-accordion-toggle', function() {
            var $item = $(this).closest('.shb-accordion-item');
            $item.toggleClass('open');
        });
        
        // Copy to clipboard
        $(document).on('click', '.shb-copy-btn', function(e) {
            e.stopPropagation();
            var $btn = $(this);
            var text = $btn.data('copy');
            
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() {
                    showCopied($btn);
                });
            } else {
                var $temp = $('<textarea>');
                $('body').append($temp);
                $temp.val(text).select();
                document.execCommand('copy');
                $temp.remove();
                showCopied($btn);
            }
        });
        
        function showCopied($btn) {
            var original = $btn.html();
            $btn.addClass('copied').html('<span class="dashicons dashicons-yes" style="font-size:12px;width:12px;height:12px"></span> Copied!');
            setTimeout(function() {
                $btn.removeClass('copied').html(original);
            }, 1500);
        }
    });

    // Room Setup screen: tab switching + WP media uploader for images/gallery
    function initRoomSetup() {
        // Tab switching
        $('.shb-meta-tab').on('click', function() {
            var tab = $(this).data('tab');
            $('.shb-meta-tab').removeClass('active');
            $(this).addClass('active');
            $('.shb-meta-panel').removeClass('active');
            $('.shb-meta-panel[data-panel="' + tab + '"]').addClass('active');
        });

        // Render preview thumbnails from attachment objects.
        function renderPreviews($preview, attachments) {
            var html = '';
            attachments.forEach(function(att) {
                var a = att.attributes || {};
                var url = a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url;
                html += '<img src="' + url + '" alt="">';
            });
            $preview.html(html);
        }

        // Fetch attachments by ID list and render them into a preview container.
        function fetchAndRender($preview, ids, done) {
            var loaded = [];
            var remaining = ids.length;
            if (!remaining) { $preview.empty(); if (done) done(); return; }
            ids.forEach(function(id) {
                var att = wp.media.attachment(id);
                att.fetch().done(function() {
                    loaded.push(att);
                    remaining--;
                    if (remaining <= 0) {
                        renderPreviews($preview, loaded);
                        if (done) done();
                    }
                });
            });
        }

        // Collect currently previewed attachment IDs for a multi-select field.
        function currentGalleryIds() {
            var raw = $('#shb_gallery_ids').val();
            if (!raw) return [];
            return raw.split(',').filter(function(v) { return v !== ''; });
        }

        // Media uploader (featured image = single, gallery = multiple)
        $(document).on('click', '.shb-media-upload', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var targetId = $btn.data('target');
            var previewId = $btn.data('preview');
            var multiple = $btn.data('multiple') == 1;

            var frame = wp.media({
                title: multiple ? (shb_admin.media_gallery_title || 'Select Gallery Images') : (shb_admin.media_image_title || 'Select Image'),
                button: { text: shb_admin.media_button || 'Select' },
                multiple: multiple
            });

            frame.on('select', function() {
                var selection = frame.state().get('selection');
                var attachments = [];
                selection.each(function(att) { attachments.push(att); });

                var $preview = $('#' + previewId);

                if (multiple) {
                    // Merge with existing selections, de-duplicate.
                    var merged = currentGalleryIds();
                    attachments.forEach(function(att) {
                        var id = String(att.id);
                        if (merged.indexOf(id) === -1) merged.push(id);
                    });
                    $('#shb_gallery_ids').val(merged.join(','));
                    fetchAndRender($preview, merged);
                } else {
                    $('#' + targetId).val(attachments.length ? attachments[0].id : '');
                    renderPreviews($preview, attachments);
                }
            });

            frame.open();
        });

        // Remove image/gallery
        $(document).on('click', '.shb-media-remove', function(e) {
            e.preventDefault();
            var targetId = $(this).data('target');
            var previewId = $(this).data('preview');
            if (targetId === 'shb_gallery_ids') {
                $('#shb_gallery_ids').val('');
            } else {
                $('#' + targetId).val('');
            }
            $('#' + previewId).empty();
        });
    }

    // Initialize the Room Setup screen (media uploader + tabs) on document ready
    $(document).ready(function() {
        initRoomSetup();
    });

})(jQuery);
