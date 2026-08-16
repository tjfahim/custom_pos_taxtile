const InvoicePayments = {

    togglePaymentDetails: function() {
        const method = $('#paymentMethod').val();

        $('#bkashDetails').hide();
        $('#bkashPersonalDetails').hide();
        $('#bankDetails').hide();
        $('#cashDetails').hide();

        $('[name="bkash_transaction"]').val('');
        $('[name="bkash_personal_transaction"]').val('');
        $('[name="bank_transfer_details"]').val('');
        $('[name="cash_amount"]').val('');
        $('[name="amount_receiver_name"]').val('');

        if (method === 'bkash') {
            $('#bkashDetails').show();
        } else if (method === 'bkash_personal') {
            $('#bkashPersonalDetails').show();
        } else if (method === 'bank_transfer') {
            $('#bankDetails').show();
        } else if (method === 'cash') {
            $('#cashDetails').show();
        }

        this.updateRequiredState();
    },

    // NEW: same idea as togglePaymentDetails() but for the second,
    // in-house-sale-only payment method. Never marks anything required —
    // the whole second payment is optional even when in-house is checked.
    togglePaymentDetails2: function() {
        const method = $('#paymentMethod2').val();

        $('#bkashDetails2').hide();
        $('#bkashPersonalDetails2').hide();
        $('#bankDetails2').hide();
        $('#cashDetails2').hide();

        $('[name="bkash_transaction2"]').val('');
        $('[name="bkash_personal_transaction2"]').val('');
        $('[name="bank_transfer_details2"]').val('');
        $('[name="cash_amount2"]').val('');

        if (method === 'bkash') {
            $('#bkashDetails2').show();
        } else if (method === 'bkash_personal') {
            $('#bkashPersonalDetails2').show();
        } else if (method === 'bank_transfer') {
            $('#bankDetails2').show();
        } else if (method === 'cash') {
            $('#cashDetails2').show();
        }
    },

    // marks the currently-visible (first) payment detail field as required
    // only when In-house Sale is checked. Doesn't touch values/visibility.
    updateRequiredState: function() {
        const isInhouse = $('#isInhouseSale').is(':checked');
        const method = $('#paymentMethod').val();

        $('[name="bkash_transaction"], [name="bkash_personal_transaction"], ' +
          '[name="bank_transfer_details"], [name="cash_amount"]').prop('required', false);

        if (!isInhouse) return;

        if (method === 'bkash') {
            $('[name="bkash_transaction"]').prop('required', true);
        } else if (method === 'bkash_personal') {
            $('[name="bkash_personal_transaction"]').prop('required', true);
        } else if (method === 'bank_transfer') {
            $('[name="bank_transfer_details"]').prop('required', true);
        } else if (method === 'cash') {
            $('[name="cash_amount"]').prop('required', true);
        }
    }
};

window.togglePaymentDetails = function() {
    InvoicePayments.togglePaymentDetails();
};

window.togglePaymentDetails2 = function() {
    InvoicePayments.togglePaymentDetails2();
};