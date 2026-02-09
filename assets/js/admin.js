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
            $modal.fadeIn(200);
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

    // Initialize on document ready
    $(document).ready(function() {
        initBookingStatusUpdate();
        initSendEmailButton();
        initPricingRulesModal();
        initAvailabilityModal();
        
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

})(jQuery);
