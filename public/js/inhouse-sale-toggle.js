const InhouseSaleToggle = {
    init: function() {
        $(document).on('change', '#isInhouseSale', () => this.apply());
        this.apply();
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
            $('#deliveryZoneSelect').val('');
            $('#deliveryCharge').val(0).trigger('input');
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