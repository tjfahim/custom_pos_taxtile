// Auto-selects City + Zone while typing a free-hand address (new customers only)
const AddressCityZoneAutofill = {
    debounceTimer: null,
    lastValue: '',

    init: function() {
        $(document).on('input', '#recipientAddress', () => {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => this.handleAddressInput(), 900);
        });
    },

    handleAddressInput: function() {
        // Only run for NEW customers — existing customers already
        // get their delivery area from customer-auto.js
        if ($('#customerId').val()) return;

        const address = $('#recipientAddress').val().trim();
        if (address.length < 5 || address === this.lastValue) return;
        this.lastValue = address;

        $.ajax({
            url: '/admin/location/auto-submit',
            method: 'GET',
            data: { search: address },
            success: (response) => {
                if (response.success && response.city) {
                    this.applyCityAndZone(response.city, response.zone);
                }
            },
            error: (xhr) => console.error('Address autofill error:', xhr)
        });
    },

    applyCityAndZone: function(city, zone) {
        const $citySelect = $('#deliveryCitySelect');

        // City already correctly selected and no zone change needed
        if ($citySelect.val() == city.id && !zone) return;

        $citySelect.val(city.id).trigger('change'); // triggers InvoiceDelivery.loadZones

        if (zone) {
            $(document).one('deliveryZonesLoaded', (e, loadedCityId) => {
                if (loadedCityId == city.id) {
                    $('#deliveryZoneSelect').val(zone.id).trigger('change');
                }
            });
        }
    }
};

$(document).ready(() => AddressCityZoneAutofill.init());