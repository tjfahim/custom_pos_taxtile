// Invoice Payments Module - Handles payment methods
const InvoicePayments = {
    
    togglePaymentDetails: function() {
        const method = $('#paymentMethod').val();
        
        // Hide all payment detail sections
        $('#bkashDetails').hide();
        $('#bkashPersonalDetails').hide();
        $('#bankDetails').hide();
        
        // Clear all payment detail fields
        $('[name="bkash_transaction"]').val('');
        $('[name="bkash_personal_transaction"]').val('');
        $('[name="bank_transfer_details"]').val('');
        
        // Show relevant section based on payment method
        if (method === 'bkash') {
            $('#bkashDetails').show();
        } else if (method === 'bkash_personal') {
            $('#bkashPersonalDetails').show();
        } else if (method === 'bank_transfer') {
            $('#bankDetails').show();
        }
    }
};

// Make togglePaymentDetails globally accessible
window.togglePaymentDetails = function() {
    InvoicePayments.togglePaymentDetails();
};