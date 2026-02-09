/**
 * Sanctuary Hotel Booking - Gutenberg Blocks
 * Registers custom blocks for the hotel booking system
 */

(function(wp) {
    const { registerBlockType } = wp.blocks;
    const { createElement: el, Fragment } = wp.element;
    const { InspectorControls } = wp.blockEditor;
    const { PanelBody, SelectControl, RangeControl, Placeholder } = wp.components;
    const { __ } = wp.i18n;
    
    // Get plugin data passed from PHP
    const { rooms, roomTypes, pluginUrl } = window.shbBlockData || { rooms: [], roomTypes: [], pluginUrl: '' };
    
    // Block category
    wp.blocks.updateCategory && wp.blocks.updateCategory('widgets', { icon: 'calendar-alt' });
    
    // =============================================
    // Room Search Block
    // =============================================
    registerBlockType('sanctuary-hotel-booking/room-search', {
        title: __('Room Search', 'sanctuary-hotel-booking'),
        description: __('Display a room search form with date picker', 'sanctuary-hotel-booking'),
        icon: 'search',
        category: 'widgets',
        keywords: [__('hotel'), __('search'), __('booking'), __('room')],
        attributes: {
            style: {
                type: 'string',
                default: 'default'
            }
        },
        
        edit: function(props) {
            const { attributes, setAttributes } = props;
            
            return el(Fragment, {},
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Settings', 'sanctuary-hotel-booking'), initialOpen: true },
                        el(SelectControl, {
                            label: __('Style', 'sanctuary-hotel-booking'),
                            value: attributes.style,
                            options: [
                                { label: __('Default', 'sanctuary-hotel-booking'), value: 'default' },
                                { label: __('Compact', 'sanctuary-hotel-booking'), value: 'compact' },
                                { label: __('Horizontal', 'sanctuary-hotel-booking'), value: 'horizontal' }
                            ],
                            onChange: function(value) { setAttributes({ style: value }); }
                        })
                    )
                ),
                el(Placeholder, {
                    icon: 'search',
                    label: __('Room Search', 'sanctuary-hotel-booking'),
                    instructions: __('Displays a search form where guests can select dates and find available rooms.', 'sanctuary-hotel-booking')
                },
                    el('div', { className: 'shb-block-preview' },
                        el('div', { className: 'shb-block-preview-fields' },
                            el('span', { className: 'shb-preview-field' }, '📅 ' + __('Check-in', 'sanctuary-hotel-booking')),
                            el('span', { className: 'shb-preview-field' }, '📅 ' + __('Check-out', 'sanctuary-hotel-booking')),
                            el('span', { className: 'shb-preview-field' }, '👥 ' + __('Guests', 'sanctuary-hotel-booking')),
                            el('span', { className: 'shb-preview-button' }, __('Search Rooms', 'sanctuary-hotel-booking'))
                        )
                    )
                )
            );
        },
        
        save: function() {
            return null; // Dynamic block, rendered on server
        }
    });
    
    // =============================================
    // Room List Block
    // =============================================
    registerBlockType('sanctuary-hotel-booking/room-list', {
        title: __('Room List', 'sanctuary-hotel-booking'),
        description: __('Display a list of available rooms', 'sanctuary-hotel-booking'),
        icon: 'grid-view',
        category: 'widgets',
        keywords: [__('hotel'), __('rooms'), __('list'), __('accommodation')],
        attributes: {
            columns: {
                type: 'number',
                default: 3
            },
            type: {
                type: 'string',
                default: ''
            },
            limit: {
                type: 'number',
                default: -1
            }
        },
        
        edit: function(props) {
            const { attributes, setAttributes } = props;
            
            // Build room type options
            const typeOptions = [{ label: __('All Types', 'sanctuary-hotel-booking'), value: '' }];
            roomTypes.forEach(function(type) {
                typeOptions.push({
                    label: type.charAt(0).toUpperCase() + type.slice(1),
                    value: type
                });
            });
            
            return el(Fragment, {},
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Display Settings', 'sanctuary-hotel-booking'), initialOpen: true },
                        el(RangeControl, {
                            label: __('Columns', 'sanctuary-hotel-booking'),
                            value: attributes.columns,
                            onChange: function(value) { setAttributes({ columns: value }); },
                            min: 1,
                            max: 4
                        }),
                        el(SelectControl, {
                            label: __('Room Type', 'sanctuary-hotel-booking'),
                            value: attributes.type,
                            options: typeOptions,
                            onChange: function(value) { setAttributes({ type: value }); }
                        }),
                        el(RangeControl, {
                            label: __('Limit', 'sanctuary-hotel-booking'),
                            help: __('Number of rooms to show (-1 for all)', 'sanctuary-hotel-booking'),
                            value: attributes.limit,
                            onChange: function(value) { setAttributes({ limit: value }); },
                            min: -1,
                            max: 20
                        })
                    )
                ),
                el(Placeholder, {
                    icon: 'grid-view',
                    label: __('Room List', 'sanctuary-hotel-booking'),
                    instructions: __('Displays a grid of available rooms with pricing and details.', 'sanctuary-hotel-booking')
                },
                    el('div', { className: 'shb-block-preview shb-room-list-preview' },
                        el('div', { className: 'shb-preview-info' },
                            el('strong', {}, attributes.columns + ' ' + __('columns', 'sanctuary-hotel-booking')),
                            attributes.type && el('span', {}, ' • ' + __('Type:', 'sanctuary-hotel-booking') + ' ' + attributes.type),
                            attributes.limit > 0 && el('span', {}, ' • ' + __('Limit:', 'sanctuary-hotel-booking') + ' ' + attributes.limit)
                        ),
                        el('div', { 
                            className: 'shb-preview-grid',
                            style: { 
                                display: 'grid', 
                                gridTemplateColumns: 'repeat(' + Math.min(attributes.columns, 3) + ', 1fr)',
                                gap: '10px',
                                marginTop: '10px'
                            }
                        },
                            el('div', { className: 'shb-preview-room-card' }, '🛏️ ' + __('Room 1', 'sanctuary-hotel-booking')),
                            el('div', { className: 'shb-preview-room-card' }, '🛏️ ' + __('Room 2', 'sanctuary-hotel-booking')),
                            el('div', { className: 'shb-preview-room-card' }, '🛏️ ' + __('Room 3', 'sanctuary-hotel-booking'))
                        )
                    )
                )
            );
        },
        
        save: function() {
            return null;
        }
    });
    
    // =============================================
    // Booking Form Block
    // =============================================
    registerBlockType('sanctuary-hotel-booking/booking-form', {
        title: __('Booking Form', 'sanctuary-hotel-booking'),
        description: __('Display the booking form for reservations', 'sanctuary-hotel-booking'),
        icon: 'calendar-alt',
        category: 'widgets',
        keywords: [__('hotel'), __('booking'), __('reservation'), __('form')],
        attributes: {
            roomId: {
                type: 'number',
                default: 0
            }
        },
        
        edit: function(props) {
            const { attributes, setAttributes } = props;
            
            // Build room options
            const roomOptions = [{ label: __('Select from URL parameter', 'sanctuary-hotel-booking'), value: 0 }];
            rooms.forEach(function(room) {
                roomOptions.push({
                    label: room.name + ' (' + room.type + ')',
                    value: room.id
                });
            });
            
            const selectedRoom = rooms.find(function(r) { return r.id === attributes.roomId; });
            
            return el(Fragment, {},
                el(InspectorControls, {},
                    el(PanelBody, { title: __('Room Selection', 'sanctuary-hotel-booking'), initialOpen: true },
                        el(SelectControl, {
                            label: __('Pre-select Room', 'sanctuary-hotel-booking'),
                            help: __('Leave as "Select from URL parameter" to use the room_id from the page URL', 'sanctuary-hotel-booking'),
                            value: attributes.roomId,
                            options: roomOptions,
                            onChange: function(value) { setAttributes({ roomId: parseInt(value, 10) }); }
                        })
                    )
                ),
                el(Placeholder, {
                    icon: 'calendar-alt',
                    label: __('Booking Form', 'sanctuary-hotel-booking'),
                    instructions: selectedRoom 
                        ? __('Pre-selected room:', 'sanctuary-hotel-booking') + ' ' + selectedRoom.name
                        : __('The booking form allows guests to complete their reservation.', 'sanctuary-hotel-booking')
                },
                    el('div', { className: 'shb-block-preview' },
                        el('div', { className: 'shb-block-preview-fields' },
                            el('span', { className: 'shb-preview-field' }, '👤 ' + __('Guest Details', 'sanctuary-hotel-booking')),
                            el('span', { className: 'shb-preview-field' }, '📅 ' + __('Dates', 'sanctuary-hotel-booking')),
                            el('span', { className: 'shb-preview-field' }, '💳 ' + __('Payment', 'sanctuary-hotel-booking')),
                            el('span', { className: 'shb-preview-button' }, __('Complete Booking', 'sanctuary-hotel-booking'))
                        )
                    )
                )
            );
        },
        
        save: function() {
            return null;
        }
    });
    
    // =============================================
    // Booking Confirmation Block
    // =============================================
    registerBlockType('sanctuary-hotel-booking/booking-confirmation', {
        title: __('Booking Confirmation', 'sanctuary-hotel-booking'),
        description: __('Display booking confirmation details after payment', 'sanctuary-hotel-booking'),
        icon: 'yes-alt',
        category: 'widgets',
        keywords: [__('hotel'), __('confirmation'), __('receipt'), __('booking')],
        attributes: {},
        
        edit: function() {
            return el(Placeholder, {
                icon: 'yes-alt',
                label: __('Booking Confirmation', 'sanctuary-hotel-booking'),
                instructions: __('This block displays the booking confirmation page with reservation details, payment receipt, and calendar download option.', 'sanctuary-hotel-booking')
            },
                el('div', { className: 'shb-block-preview shb-confirmation-preview' },
                    el('div', { className: 'shb-preview-success' },
                        '✅ ' + __('Booking Confirmed!', 'sanctuary-hotel-booking')
                    ),
                    el('div', { className: 'shb-preview-details' },
                        el('span', {}, '📋 ' + __('Booking Reference', 'sanctuary-hotel-booking')),
                        el('span', {}, '🛏️ ' + __('Room Details', 'sanctuary-hotel-booking')),
                        el('span', {}, '📅 ' + __('Add to Calendar', 'sanctuary-hotel-booking'))
                    )
                )
            );
        },
        
        save: function() {
            return null;
        }
    });
    
    // =============================================
    // My Bookings Block
    // =============================================
    registerBlockType('sanctuary-hotel-booking/my-bookings', {
        title: __('My Bookings', 'sanctuary-hotel-booking'),
        description: __('Display list of guest bookings (requires login)', 'sanctuary-hotel-booking'),
        icon: 'list-view',
        category: 'widgets',
        keywords: [__('hotel'), __('bookings'), __('reservations'), __('account')],
        attributes: {},
        
        edit: function() {
            return el(Placeholder, {
                icon: 'list-view',
                label: __('My Bookings', 'sanctuary-hotel-booking'),
                instructions: __('Displays a list of the logged-in user\'s bookings. Guests can view their reservation history and manage upcoming stays.', 'sanctuary-hotel-booking')
            },
                el('div', { className: 'shb-block-preview shb-my-bookings-preview' },
                    el('table', { className: 'shb-preview-table' },
                        el('thead', {},
                            el('tr', {},
                                el('th', {}, __('Reference', 'sanctuary-hotel-booking')),
                                el('th', {}, __('Room', 'sanctuary-hotel-booking')),
                                el('th', {}, __('Dates', 'sanctuary-hotel-booking')),
                                el('th', {}, __('Status', 'sanctuary-hotel-booking'))
                            )
                        ),
                        el('tbody', {},
                            el('tr', {},
                                el('td', {}, 'SHB-XXXXX'),
                                el('td', {}, __('Sample Room', 'sanctuary-hotel-booking')),
                                el('td', {}, 'mm/dd - mm/dd'),
                                el('td', {}, '✓ ' + __('Confirmed', 'sanctuary-hotel-booking'))
                            )
                        )
                    )
                )
            );
        },
        
        save: function() {
            return null;
        }
    });
    
})(window.wp);
