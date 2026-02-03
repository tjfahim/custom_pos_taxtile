// Delivery Area Module for POS System
const InvoiceDelivery = {
    selectedCity: null,
    selectedZone: null,
    selectedArea: null,
    
    init: function() {
        // Load cities on page load
        this.loadCities();
        
        // Setup event listeners
        this.setupEventListeners();
    },
    
    setupEventListeners: function() {
        // Refresh button for cities
        $(document).on('click', '#refreshCities', () => this.loadCities());
        
        // City selection
        $(document).on('change', '#deliveryCitySelect', function() {
            const cityId = $(this).val();
            if (cityId) {
                InvoiceDelivery.selectCity(cityId);
            } else {
                InvoiceDelivery.resetZonesAndAreas();
            }
        });
        
        // Zone selection
        $(document).on('change', '#deliveryZoneSelect', function() {
            const zoneId = $(this).val();
            if (zoneId) {
                InvoiceDelivery.selectZone(zoneId);
            } else {
                InvoiceDelivery.resetAreas();
            }
        });
        
        // Area selection
        $(document).on('change', '#deliveryAreaSelect', function() {
            const areaId = $(this).val();
            if (areaId) {
                InvoiceDelivery.selectArea(areaId);
            } else {
                InvoiceDelivery.selectedArea = null;
                InvoiceDelivery.updateDeliveryAreaField();
            }
        });
    },
    
    loadCities: function() {
        $('#cityLoading').show();
        $('#deliveryCitySelect').prop('disabled', true);
        $('#deliveryZoneSelect').prop('disabled', true);
        $('#deliveryAreaSelect').prop('disabled', true);
        
        $.ajax({
            url: '/admin/pathao/cities',
            type: 'GET',
            success: function(response) {
                const citiesData = response.data?.data?.data || response.data?.data || response.data || [];
                InvoiceDelivery.renderCities(citiesData);
            },
            error: function() {
                alert('Failed to load cities. Please try again.');
            },
            complete: function() {
                $('#cityLoading').hide();
                $('#deliveryCitySelect').prop('disabled', false);
            }
        });
    },
    
    renderCities: function(citiesData) {
        const select = $('#deliveryCitySelect');
        select.empty();
        
        // Add search option at the top
        select.append('<option value="">-- Select City --</option>');
        
        if (!citiesData.length) {
            select.append('<option value="" disabled>No cities available</option>');
            return;
        }
        
        // Sort cities alphabetically
        citiesData.sort((a, b) => a.city_name.localeCompare(b.city_name));
        
        citiesData.forEach(city => {
            select.append(`<option value="${city.city_id}">${city.city_name}</option>`);
        });
        
        // Initialize select2 for search functionality
        this.initializeSelect2('#deliveryCitySelect', 'Search city...');
    },
    
    selectCity: function(cityId) {
        const select = $('#deliveryCitySelect');
        const selectedOption = select.find('option:selected');
        
        if (selectedOption.val()) {
            this.selectedCity = {
                id: cityId,
                name: selectedOption.text()
            };
            this.loadZones(cityId);
            this.updateDeliveryAreaField();
        }
    },
    
    loadZones: function(cityId) {
        $('#zoneLoading').show();
        $('#deliveryZoneSelect').prop('disabled', true);
        $('#deliveryAreaSelect').prop('disabled', true);
        
        $.ajax({
            url: `/admin/pathao/zones/${cityId}`,
            type: 'GET',
            success: function(response) {
                const zonesData = response.data?.data?.data || response.data?.data || response.data || [];
                InvoiceDelivery.renderZones(zonesData);
            },
            error: function() {
                alert('Failed to load zones. Please try again.');
            },
            complete: function() {
                $('#zoneLoading').hide();
                $('#deliveryZoneSelect').prop('disabled', false);
            }
        });
    },
    
    renderZones: function(zonesData) {
        const select = $('#deliveryZoneSelect');
        select.empty();
        
        // Add search option at the top
        select.append('<option value="">-- Select Zone --</option>');
        
        if (!zonesData.length) {
            select.append('<option value="" disabled>No zones available</option>');
            return;
        }
        
        // Sort zones alphabetically
        zonesData.sort((a, b) => a.zone_name.localeCompare(b.zone_name));
        
        zonesData.forEach(zone => {
            select.append(`<option value="${zone.zone_id}">${zone.zone_name}</option>`);
        });
        
        // Initialize select2 for search functionality
        this.initializeSelect2('#deliveryZoneSelect', 'Search zone...');
    },
    
    selectZone: function(zoneId) {
        const select = $('#deliveryZoneSelect');
        const selectedOption = select.find('option:selected');
        
        if (selectedOption.val()) {
            this.selectedZone = {
                id: zoneId,
                name: selectedOption.text()
            };
            this.loadAreas(zoneId);
            this.updateDeliveryAreaField();
        }
    },
    
    loadAreas: function(zoneId) {
        $('#areaLoading').show();
        $('#deliveryAreaSelect').prop('disabled', true);
        
        $.ajax({
            url: `/admin/pathao/areas/${zoneId}`,
            type: 'GET',
            success: function(response) {
                const areasData = response.data?.data?.data || response.data?.data || response.data || [];
                InvoiceDelivery.renderAreas(areasData);
            },
            error: function() {
                alert('Failed to load areas. Please try again.');
            },
            complete: function() {
                $('#areaLoading').hide();
                $('#deliveryAreaSelect').prop('disabled', false);
            }
        });
    },
    
    renderAreas: function(areasData) {
        const select = $('#deliveryAreaSelect');
        select.empty();
        
        // Add search option at the top
        select.append('<option value="">-- Select Area --</option>');
        
        if (!areasData.length) {
            select.append('<option value="" disabled>No areas available</option>');
            return;
        }
        
        // Sort areas alphabetically
        areasData.sort((a, b) => a.area_name.localeCompare(b.area_name));
        
        areasData.forEach(area => {
            const deliveryText = area.home_delivery_available ? '✓ Delivery' : '✗ Delivery';
            const pickupText = area.pickup_available ? '✓ Pickup' : '✗ Pickup';
            const displayText = `${area.area_name} (${deliveryText}, ${pickupText})`;
            select.append(`<option value="${area.area_id}" data-area-name="${area.area_name}">${displayText}</option>`);
        });
        
        // Initialize select2 for search functionality
        this.initializeSelect2('#deliveryAreaSelect', 'Search area...');
    },
    
    selectArea: function(areaId) {
        const select = $('#deliveryAreaSelect');
        const selectedOption = select.find('option:selected');
        const areaName = selectedOption.data('area-name') || selectedOption.text().split(' (')[0];
        
        if (selectedOption.val()) {
            this.selectedArea = {
                id: areaId,
                name: areaName
            };
            this.updateDeliveryAreaField();
        }
    },
    
    updateDeliveryAreaField: function() {
        let deliveryAreaParts = [];
        
        if (this.selectedCity) {
            deliveryAreaParts.push(this.selectedCity.name);
        }
        
        if (this.selectedZone) {
            deliveryAreaParts.push(this.selectedZone.name);
        }
        
        if (this.selectedArea) {
            deliveryAreaParts.push(this.selectedArea.name);
        }
        
        // Join all parts with comma separation
        const deliveryArea = deliveryAreaParts.join(', ');
        $('#deliveryArea').val(deliveryArea);
    },
    
    resetZonesAndAreas: function() {
        this.selectedCity = null;
        this.selectedZone = null;
        this.selectedArea = null;
        
        $('#deliveryZoneSelect').empty().append('<option value="">-- Select Zone --</option>').prop('disabled', true);
        $('#deliveryAreaSelect').empty().append('<option value="">-- Select Area --</option>').prop('disabled', true);
        
        // Destroy select2 instances
        this.destroySelect2('#deliveryZoneSelect');
        this.destroySelect2('#deliveryAreaSelect');
        
        this.updateDeliveryAreaField();
    },
    
    resetAreas: function() {
        this.selectedZone = null;
        this.selectedArea = null;
        
        $('#deliveryAreaSelect').empty().append('<option value="">-- Select Area --</option>').prop('disabled', true);
        
        // Destroy select2 instance
        this.destroySelect2('#deliveryAreaSelect');
        
        this.updateDeliveryAreaField();
    },
    
    // Initialize select2 for searchable dropdowns
    initializeSelect2: function(selector, placeholder) {
        $(selector).select2({
            placeholder: placeholder,
            allowClear: true,
            dropdownParent: $(selector).closest('.modal, .card-body, body'),
            width: '100%',
            theme: 'bootstrap4',
            minimumResultsForSearch: 5,
            language: {
                noResults: function() {
                    return "No results found";
                },
                searching: function() {
                    return "Searching...";
                }
            }
        });
        
        // Ensure proper styling
        $(selector).on('select2:open', function() {
            $('.select2-search__field').attr('placeholder', placeholder);
        });
    },
    
    // Destroy select2 instance
    destroySelect2: function(selector) {
        if ($(selector).hasClass('select2-hidden-accessible')) {
            $(selector).select2('destroy');
        }
    },
    
    // Function to set delivery area from customer data
    setFromCustomer: function(customerArea) {
        if (!customerArea) return;
        
        // Set the delivery area field directly
        $('#deliveryArea').val(customerArea);
        
        // Try to parse and select from dropdowns if possible
        this.tryAutoSelectFromCustomerArea(customerArea);
    },
    
    tryAutoSelectFromCustomerArea: function(customerArea) {
        // This is a simple implementation - you might want to enhance it
        // based on your specific area format
        console.log('Attempting to auto-select area:', customerArea);
        
        // Clear previous selections
        this.resetZonesAndAreas();
        
        // Try to find and select the city first
        const citySelect = $('#deliveryCitySelect');
        const areaParts = customerArea.split(',').map(part => part.trim());
        
        if (areaParts.length > 0) {
            const possibleCity = areaParts[0];
            const cityOption = citySelect.find(`option:contains("${possibleCity}")`);
            
            if (cityOption.length > 0 && cityOption.val()) {
                citySelect.val(cityOption.val()).trigger('change');
                
                // If we have more parts, try to find zone
                if (areaParts.length > 1) {
                    setTimeout(() => {
                        const possibleZone = areaParts[1];
                        const zoneSelect = $('#deliveryZoneSelect');
                        const zoneOption = zoneSelect.find(`option:contains("${possibleZone}")`);
                        
                        if (zoneOption.length > 0 && zoneOption.val()) {
                            zoneSelect.val(zoneOption.val()).trigger('change');
                            
                            // If we have more parts, try to find area
                            if (areaParts.length > 2) {
                                setTimeout(() => {
                                    const possibleArea = areaParts[2].split('(')[0].trim();
                                    const areaSelect = $('#deliveryAreaSelect');
                                    const areaOption = areaSelect.find(`option[data-area-name="${possibleArea}"], option:contains("${possibleArea}")`);
                                    
                                    if (areaOption.length > 0 && areaOption.val()) {
                                        areaSelect.val(areaOption.val()).trigger('change');
                                    }
                                }, 1000);
                            }
                        }
                    }, 1000);
                }
            }
        }
    }
};

// Initialize when document is ready
$(document).ready(function() {
    // Check if select2 CSS is loaded
    if (!$.fn.select2) {
        console.warn('Select2 not loaded. Loading from CDN...');
        // Load select2 CSS
        $('head').append('<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />');
        $('head').append('<link href="https://cdnjs.cloudflare.com/ajax/libs/select2-bootstrap-theme/0.1.0-beta.10/select2-bootstrap.min.css" rel="stylesheet" />');
        
        // Load select2 JS
        $.getScript('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', function() {
            InvoiceDelivery.init();
        });
    } else {
        InvoiceDelivery.init();
    }
});