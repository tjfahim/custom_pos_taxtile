// Toggles delivery-area requirement and locks courier select
// when "In-house Sale" is checked.
const InhouseSaleToggle = {
    init: function() {
        $(document).on('change', '#isInhouseSale', () => this.apply());
        this.apply(); // run once on load in case of pre-checked state (e.g. after validation error)
    },

    apply: function() {
    const isInhouse = $('#isInhouseSale').is(':checked');

    if (isInhouse) {
        $('#deliveryCitySelect, #deliveryZoneSelect, #deliveryAreaSelect').prop('disabled', true);
        $('#deliveryArea').prop('required', false);
        $('#deliveryAreaCard').css('opacity', '0.5');

        if (typeof InvoiceDelivery !== 'undefined') {
            InvoiceDelivery.selectedCity = null;
            InvoiceDelivery.selectedZone = null;
            InvoiceDelivery.selectedArea = null;
        }
        $('#deliveryArea').val('');

        // NEW: clear the zone select's actual value so no stale zoneId
        // can be picked up by DeliveryChargeManager
        $('#deliveryZoneSelect').val('');

        $('#deliveryCharge').val(0).trigger('input');

        $('#courierName').val('Pathao').prop('disabled', true);

        // NEW: stop DeliveryChargeManager from re-fetching on this zone
        if (typeof DeliveryChargeManager !== 'undefined') {
            DeliveryChargeManager.reset();
        }

    } else {
        $('#deliveryAreaCard').css('opacity', '1');
        $('#deliveryArea').prop('required', true);
        $('#courierName').prop('disabled', false);

        $('#deliveryCitySelect').prop('disabled', false);
        if ($('#deliveryCitySelect').val()) {
            $('#deliveryZoneSelect').prop('disabled', false);
        }
        if ($('#deliveryZoneSelect').val()) {
            $('#deliveryAreaSelect').prop('disabled', false);
        }
    }
}
};

$(document).ready(() => InhouseSaleToggle.init());