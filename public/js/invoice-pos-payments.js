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
        
        // NEW
        this.updateRequiredState();
    },
    
    // NEW: marks the currently-visible payment detail field as required
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