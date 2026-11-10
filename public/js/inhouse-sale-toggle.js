const InhouseSaleToggle = {
    init: function() {
        // userToggled = true only when the person actually clicks the checkbox
        $(document).on('change', '#isInhouseSale', () => this.apply(true));
        this.apply(false);
    },

    apply: function(userToggled) {
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
            $('#deliveryZoneSelect').val('');
            // In-house sales start at 0 delivery charge, but the amount stays editable
            // (0 or any amount). Only reset it when the checkbox is actually clicked, so
            // opening the edit page keeps the saved charge.
            if (userToggled) {
                $('#deliveryCharge').val(0).trigger('input');
            }
            $('#courierName').val('Pathao').prop('disabled', true);

            if (typeof DeliveryChargeManager !== 'undefined') {
                DeliveryChargeManager.reset();
            }

            // address optional, (first) payment method required
            $('#recipientAddress').prop('required', false);
            $('#paymentMethod').prop('required', true);

            // NEW: second payment block only exists for in-house sales
            $('#secondPaymentSection').show();

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

            // address required, payment method optional
            $('#recipientAddress').prop('required', true);
            $('#paymentMethod').prop('required', false);

            // Un-ticking in-house → go back to the normal delivery charge
            if (userToggled) {
                const zoneId = $('#deliveryZoneSelect').val();
                if (zoneId && typeof DeliveryChargeManager !== 'undefined') {
                    DeliveryChargeManager.reset();
                    DeliveryChargeManager.refresh(zoneId);
                } else {
                    $('#deliveryCharge').val(150).trigger('input');
                }
            }

            // NEW: hide + clear the second payment block whenever we're
            // not in an in-house sale, so stale values never get submitted.
            $('#secondPaymentSection').hide();
            $('#paidAmount2').val(0);
            $('#paymentMethod2').val('').trigger('change');
        }

        // keep the payment-detail field's required state in sync
        if (typeof InvoicePayments !== 'undefined') {
            InvoicePayments.updateRequiredState();
        }

        // recalculate totals since paidAmount2 may have just been reset
        if (typeof InvoiceCalculations !== 'undefined') {
            InvoiceCalculations.updateDueAmount();
        }
    }
};

$(document).ready(() => InhouseSaleToggle.init());