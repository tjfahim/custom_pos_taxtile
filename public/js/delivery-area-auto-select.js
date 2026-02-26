// delivery-area-auto-select.js
class DeliveryAreaAutoSelector {
    constructor() {
        this.cities = [];
        this.zones = [];
        this.currentCityId = null;
        this.currentZoneId = null;
        this.init();
    }

    init() {
        // Listen for customer auto-fill completion
        $(document).on('customerAutoFilled', (e, customer) => {
            if (customer && customer.delivery_area) {
                this.parseAndSelectDeliveryArea(customer.delivery_area);
            }
        });

        // Also check when customer data is loaded from modal
        $(document).on('customerSelected', (e, customer) => {
            if (customer && customer.delivery_area) {
                this.parseAndSelectDeliveryArea(customer.delivery_area);
            }
        });
    }

    async parseAndSelectDeliveryArea(deliveryArea) {
        if (!deliveryArea) return;

        console.log('Parsing delivery area:', deliveryArea);

        // Split the delivery area string (supports comma or space-separated)
        let parts = deliveryArea.split(',').map(part => part.trim());
        
        // If no commas, try splitting by spaces (for "City Zone" format)
        if (parts.length === 1 && deliveryArea.includes(' ')) {
            parts = deliveryArea.split(' ').map(part => part.trim());
        }

        // Remove empty parts
        parts = parts.filter(part => part.length > 0);

        console.log('Split parts:', parts);

        // Load cities first if not loaded
        await this.loadCities();

        if (parts.length >= 1) {
            // First part is city
            const cityName = parts[0];
            const city = this.findCity(cityName);
            
            if (city) {
                // Select the city
                await this.selectCity(city.city_id, city.city_name);
                
                // If we have a second part, it should be zone
                if (parts.length >= 2) {
                    const zoneName = parts[1];
                    
                    // Wait for zones to load and then select zone
                    setTimeout(() => {
                        this.selectZoneByName(zoneName);
                    }, 500); // Small delay to ensure zones are loaded
                }
            } else {
                // Try fuzzy matching if exact match not found
                const matchedCity = this.fuzzyFindCity(cityName);
                if (matchedCity) {
                    await this.selectCity(matchedCity.city_id, matchedCity.city_name);
                    
                    if (parts.length >= 2) {
                        const zoneName = parts[1];
                        setTimeout(() => {
                            this.selectZoneByName(zoneName);
                        }, 500);
                    }
                } else {
                    console.log('No matching city found for:', cityName);
                }
            }
        }
    }

    async loadCities() {
        return new Promise((resolve, reject) => {
            if (this.cities.length > 0) {
                resolve(this.cities);
                return;
            }

            $('#cityLoading').show();
            
            $.ajax({
                url: '/admin/pathao/cities',
                method: 'GET',
                success: (response) => {
                    $('#cityLoading').hide();
                    if (response.success && response.data && response.data.data) {
                        this.cities = response.data.data.data || [];
                        this.populateCitySelect(this.cities);
                        resolve(this.cities);
                    } else {
                        reject('Failed to load cities');
                    }
                },
                error: (xhr, status, error) => {
                    $('#cityLoading').hide();
                    console.error('Error loading cities:', error);
                    reject(error);
                }
            });
        });
    }

    populateCitySelect(cities) {
        const $citySelect = $('#deliveryCitySelect');
        $citySelect.empty().append('<option value="">-- Select City --</option>');
        
        cities.forEach(city => {
            $citySelect.append(`<option value="${city.city_id}">${city.city_name}</option>`);
        });
        
        // Reinitialize Select2 if used
        if ($citySelect.hasClass('select2-search')) {
            $citySelect.select2('destroy').select2();
        }
    }

    findCity(cityName) {
        return this.cities.find(city => 
            city.city_name.toLowerCase() === cityName.toLowerCase()
        );
    }

    fuzzyFindCity(cityName) {
        // Try to find a city that includes the search term or is similar
        const searchTerm = cityName.toLowerCase();
        
        // First try: city name starts with search term
        let match = this.cities.find(city => 
            city.city_name.toLowerCase().startsWith(searchTerm)
        );
        
        if (match) return match;
        
        // Second try: city name includes search term
        match = this.cities.find(city => 
            city.city_name.toLowerCase().includes(searchTerm)
        );
        
        if (match) return match;
        
        // Third try: search term includes city name (for short forms)
        match = this.cities.find(city => 
            searchTerm.includes(city.city_name.toLowerCase())
        );
        
        return match;
    }

    async selectCity(cityId, cityName) {
        return new Promise((resolve, reject) => {
            const $citySelect = $('#deliveryCitySelect');
            const $zoneSelect = $('#deliveryZoneSelect');
            const $areaSelect = $('#deliveryAreaSelect');
            
            // Set city value
            $citySelect.val(cityId).trigger('change');
            this.currentCityId = cityId;
            
            // Enable zone select and load zones
            $zoneSelect.prop('disabled', false).html('<option value="">-- Select Zone --</option>');
            $areaSelect.prop('disabled', true).html('<option value="">-- Select Area --</option>');
            
            // Show loading
            $('#zoneLoading').show();
            
            // Load zones for this city
            $.ajax({
                url: `/admin/pathao/zones/${cityId}`,
                method: 'GET',
                success: (response) => {
                    $('#zoneLoading').hide();
                    
                    if (response.success && response.data && response.data.data) {
                        const zones = response.data.data.data || [];
                        this.zones = zones;
                        
                        zones.forEach(zone => {
                            $zoneSelect.append(`<option value="${zone.zone_id}">${zone.zone_name}</option>`);
                        });
                        
                        // Reinitialize Select2
                        if ($zoneSelect.hasClass('select2-search')) {
                            $zoneSelect.select2('destroy').select2();
                        }
                        
                        console.log(`Loaded ${zones.length} zones for city:`, cityName);
                        resolve(zones);
                    }
                },
                error: (xhr, status, error) => {
                    $('#zoneLoading').hide();
                    console.error('Error loading zones:', error);
                    reject(error);
                }
            });
            
            // Update delivery area field with city name
            this.updateDeliveryAreaField(cityName);
        });
    }

    selectZoneByName(zoneName) {
        const $zoneSelect = $('#deliveryZoneSelect');
        const options = $zoneSelect.find('option');
        
        let matched = false;
        
        // Try exact match first
        options.each((index, option) => {
            if (option.text.toLowerCase() === zoneName.toLowerCase()) {
                $zoneSelect.val(option.value).trigger('change');
                console.log('Zone matched exactly:', option.text);
                matched = true;
                return false;
            }
        });
        
        if (!matched) {
            // Try fuzzy match
            options.each((index, option) => {
                if (option.text.toLowerCase().includes(zoneName.toLowerCase()) ||
                    zoneName.toLowerCase().includes(option.text.toLowerCase())) {
                    $zoneSelect.val(option.value).trigger('change');
                    console.log('Zone matched fuzzily:', option.text, 'for search:', zoneName);
                    matched = true;
                    return false;
                }
            });
        }
        
        if (!matched) {
            console.log('No matching zone found for:', zoneName);
            // Still update delivery area with the zone name if we have it
            const cityName = $('#deliveryCitySelect option:selected').text();
            if (cityName && cityName !== '-- Select City --') {
                this.updateDeliveryAreaField(`${cityName}, ${zoneName}`);
            }
        }
    }

    updateDeliveryAreaField(value) {
        $('#deliveryArea').val(value);
    }
}

// Initialize when document is ready
$(document).ready(() => {
    window.deliveryAreaAutoSelector = new DeliveryAreaAutoSelector();
});