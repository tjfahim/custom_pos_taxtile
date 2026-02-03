// Invoice Payments Module - Handles payment methods
const InvoicePayments = {
    
    togglePaymentDetails: function() {
        const method = $('#paymentMethod').val();
        
        $('#bkashDetails').hide();
        $('#bankDetails').hide();
        
        $('[name="bkash_transaction"]').val('');
        $('[name="bank_transfer_details"]').val('');
        
        // Show relevant section
        if (method === 'bkash') {
            $('#bkashDetails').show();
        } else if (method === 'bank_transfer') {
            $('#bankDetails').show();
        }
    }
};

// Make togglePaymentDetails globally accessible
window.togglePaymentDetails = function() {
    InvoicePayments.togglePaymentDetails();
};