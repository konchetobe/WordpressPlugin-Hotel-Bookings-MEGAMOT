/**
 * Sanctuary Hotel Booking - Public JavaScript v1.1
 */

(function ($) {
    'use strict';

    // Date pickers
    function initDatePickers() {
        var today = new Date();
        var tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);

        $('.shb-datepicker').each(function () {
            var $input = $(this);
            $input.datepicker({
                dateFormat: 'yy-mm-dd',
                minDate: today,
                showOtherMonths: true,
                selectOtherMonths: true,
                onSelect: function () {
                    $(this).trigger('change');
                }
            });
        });
    }

    // Room search
    function initRoomSearch() {
        var $form = $('#shb-search-form');
        if (!$form.length) return;

        $form.on('submit', function (e) {
            e.preventDefault();

            var checkIn = $('#shb-check-in').val();
            var checkOut = $('#shb-check-out').val();
            var guests = $('#shb-guests').val();

            if (!checkIn || !checkOut) {
                alert('Please select check-in and check-out dates.');
                return;
            }
            if (checkIn >= checkOut) {
                alert('Check-out must be after check-in.');
                return;
            }

            // Show loading
            $('#shb-search-results, #shb-no-results').hide();
            $('#shb-search-loading').show();

            $.ajax({
                url: shb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'shb_search_rooms',
                    nonce: shb_ajax.nonce,
                    check_in: checkIn,
                    check_out: checkOut,
                    guests: guests
                },
                success: function (response) {
                    $('#shb-search-loading').hide();
                    if (response.success && response.data.rooms.length > 0) {
                        var rooms = response.data.rooms;
                        var $grid = $('#shb-rooms-grid').empty();
                        for (var i = 0; i < rooms.length; i++) {
                            $grid.append(renderRoomCard(rooms[i], checkIn, checkOut));
                        }
                        $('#shb-results-count').text(rooms.length + ' room' + (rooms.length > 1 ? 's' : '') + ' found');
                        $('#shb-search-results').fadeIn(300);
                    } else {
                        $('#shb-no-results').fadeIn(300);
                    }
                },
                error: function () {
                    $('#shb-search-loading').hide();
                    alert('Something went wrong. Please try again.');
                }
            });
        });
    }

    // Render a single room card
    function renderRoomCard(room, checkIn, checkOut) {
        var currency = shb_ajax.currency_symbol;
        var price = room.calculated_price ? room.calculated_price.total : room.base_price;
        var priceLabel = room.calculated_price ? '/total' : '/night';
        var bedLabel = room.bed_type ? room.bed_type.charAt(0).toUpperCase() + room.bed_type.slice(1) : '';
        var desc = room.description ? room.description.substring(0, 80) : '';
        if (desc.length >= 80) desc += '...';

        // Build specs
        var specs = '<div class="shb-room-specs">';
        specs += '<span class="shb-spec"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg> ' + room.max_guests + '</span>';
        if (bedLabel) {
            specs += '<span class="shb-spec"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 4v16"/><path d="M2 8h18a2 2 0 0 1 2 2v10"/><path d="M2 17h20"/><path d="M6 8v9"/></svg> ' + bedLabel + '</span>';
        }
        if (room.room_size > 0) {
            specs += '<span class="shb-spec"><svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/></svg> ' + room.room_size + 'm&sup2;</span>';
        }
        specs += '</div>';

        // Build amenity tags
        var amenities = '';
        if (room.amenities && room.amenities.length > 0) {
            amenities = '<div class="shb-room-amenities-strip">';
            var show = Math.min(room.amenities.length, 3);
            for (var i = 0; i < show; i++) {
                amenities += '<span class="shb-amenity-tag">' + room.amenities[i] + '</span>';
            }
            if (room.amenities.length > 3) {
                amenities += '<span class="shb-amenity-more">+' + (room.amenities.length - 3) + '</span>';
            }
            amenities += '</div>';
        }

        var bookUrl = shb_ajax.booking_url || room.permalink;
        var sep = bookUrl.indexOf('?') > -1 ? '&' : '?';
        bookUrl += sep + 'room_id=' + room.id;
        if (checkIn) bookUrl += '&check_in=' + checkIn;
        if (checkOut) bookUrl += '&check_out=' + checkOut;

        return '<div class="shb-room-card" data-room-id="' + room.id + '">' +
            '<div class="shb-room-image">' +
            '<img src="' + room.image + '" alt="' + room.name + '" loading="lazy">' +
            '<span class="shb-room-type-badge">' + room.room_type.charAt(0).toUpperCase() + room.room_type.slice(1) + '</span>' +
            '<div class="shb-room-price-tag"><span class="shb-price-amount">' + currency + parseFloat(price).toFixed(0) + '</span><span class="shb-price-unit">' + priceLabel + '</span></div>' +
            '</div>' +
            '<div class="shb-room-content">' +
            '<h3 class="shb-room-title">' + room.name + '</h3>' +
            specs +
            amenities +
            '<p class="shb-room-excerpt">' + desc + '</p>' +
            '<div class="shb-room-actions">' +
            '<a href="' + room.permalink + '" class="shb-button shb-button-outline">Details</a>' +
            '<a href="' + bookUrl + '" class="shb-button shb-button-primary">Book Now</a>' +
            '</div>' +
            '</div>' +
            '</div>';
    }

    // Update price breakdown in booking form
    function updatePriceBreakdown() {
        var $form = $('#shb-booking-form');
        if (!$form.length) return;

        var checkIn = $form.find('[name="check_in"]').val();
        var checkOut = $form.find('[name="check_out"]').val();
        var roomId = $form.find('[name="room_id"]').val();

        if (!checkIn || !checkOut || !roomId) return;
        if (checkIn >= checkOut) return;

        $.ajax({
            url: shb_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'shb_calculate_price',
                nonce: shb_ajax.nonce,
                room_id: roomId,
                check_in: checkIn,
                check_out: checkOut
            },
            success: function (response) {
                if (response.success) {
                    var d = response.data;
                    var currency = shb_ajax.currency_symbol;
                    $('#nights-count').text(d.nights);
                    $('#subtotal-price').text(currency + parseFloat(d.subtotal).toFixed(2));
                    $('#total-price').text(currency + parseFloat(d.total).toFixed(2));

                    // adjustment is already a percentage value (e.g. 10 = 10%)
                    if (d.adjustment && d.adjustment !== 0) {
                        var sign = d.adjustment > 0 ? '+' : '';
                        $('#adjustment-percent').text(sign + parseFloat(d.adjustment).toFixed(0) + '%');
                        $('.shb-adjustment').show();
                    } else {
                        $('.shb-adjustment').hide();
                    }
                }
            }
        });
    }

    // Booking form submission
    function initBookingForm() {
        var $form = $('#shb-booking-form');
        if (!$form.length) return;

        // Update price when dates change
        $form.find('.shb-datepicker').on('change', function () {
            updatePriceBreakdown();
        });

        // Initial price calculation if dates are pre-filled
        if ($form.find('[name="check_in"]').val() && $form.find('[name="check_out"]').val()) {
            updatePriceBreakdown();
        }

        $form.on('submit', function (e) {
            e.preventDefault();

            var $btn = $('#shb-submit-booking');
            $btn.prop('disabled', true).text('Processing...');

            $.ajax({
                url: shb_ajax.ajax_url,
                type: 'POST',
                data: $form.serialize() + '&action=shb_create_booking&nonce=' + shb_ajax.nonce,
                success: function (response) {
                    if (response.success) {
                        if (response.data.redirect) {
                            // Stripe/PayPal - redirect to payment checkout
                            window.location.href = response.data.redirect;
                        } else if (response.data.payment_method === 'bank_transfer') {
                            // Bank transfer - redirect to confirmation with bank details
                            var confUrl = shb_ajax.confirmation_url || '?';
                            var sep = confUrl.indexOf('?') > -1 ? '&' : '?';
                            var params = 'booking_ref=' + response.data.booking_ref;
                            params += '&payment_method=bank_transfer';
                            window.location.href = confUrl + sep + params;
                        } else if (response.data.booking_ref) {
                            // Other payment methods - go to confirmation
                            var confUrl = shb_ajax.confirmation_url || '?';
                            var sep = confUrl.indexOf('?') > -1 ? '&' : '?';
                            window.location.href = confUrl + sep + 'booking_ref=' + response.data.booking_ref;
                        }
                    } else {
                        alert(response.data.message || 'Booking failed. Please try again.');
                        $btn.prop('disabled', false).text('Proceed to Payment');
                    }
                },
                error: function () {
                    alert('Something went wrong. Please try again.');
                    $btn.prop('disabled', false).text('Proceed to Payment');
                }
            });
        });
    }

    // Init everything
    $(document).ready(function () {
        initDatePickers();
        initRoomSearch();
        initBookingForm();
    });

})(jQuery);
