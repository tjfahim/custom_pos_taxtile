// invoice-pos-calculations.js
const InvoiceCalculations = {
    
    calculateTotals: function() {
        let subtotal = 0;
        
        // Calculate subtotal from items
        $('.total-price-hidden').each(function() {
            subtotal += parseFloat($(this).val()) || 0;
        });
        
        // Get delivery charge
        const deliveryCharge = parseFloat($('#deliveryCharge').val()) || 0;
        
        // Calculate total
        const total = subtotal + deliveryCharge;
        
        // Get advance payment
        const advancePayment = parseFloat($('#paidAmount').val()) || 0;
        
        // Calculate due amount
        const dueAmount = Math.max(0, total - advancePayment);
        
        // Update display
        $('#subtotal').text('৳' + subtotal.toFixed(2));
        $('#deliveryAmount').text('৳' + deliveryCharge.toFixed(2));
        $('#total').text('৳' + total.toFixed(2));
        
        // Update hidden inputs if they exist
        $('#subtotalInput').val(subtotal.toFixed(2));
        $('#totalInput').val(total.toFixed(2));
        
        // Update advance payment display
        this.updateAdvanceDisplay(advancePayment, dueAmount);
    },
    
    updateAdvanceDisplay: function(advancePayment, dueAmount) {
        const advanceRow = $('#advancePaymentRow');
        const advanceAmount = $('#advanceAmount');
        
        if (advancePayment > 0) {
            // Show advance payment row
            advanceRow.show();
            advanceAmount.text('৳' + advancePayment.toFixed(2));
        } else {
            // Hide advance payment row
            advanceRow.hide();
        }
        
        // Update due amount
        $('#dueAmount').text('৳' + dueAmount.toFixed(2));
        $('#dueInput').val(dueAmount.toFixed(2));
    },
    
    updateDueAmount: function() {
        const total = parseFloat($('#total').text().replace('৳', '')) || 0;
        const advancePayment = parseFloat($('#paidAmount').val()) || 0;
        const dueAmount = Math.max(0, total - advancePayment);
        
        this.updateAdvanceDisplay(advancePayment, dueAmount);
    },
    
    // Add this function to update amount to collect (if you have this field)
    updateAmountToCollect: function() {
        const total = parseFloat($('#total').text().replace('৳', '')) || 0;
        const amountToCollect = parseFloat($('#amountToCollect').val()) || total;
        const dueAmount = Math.max(0, total - amountToCollect);
        
        // If using amountToCollect instead of paidAmount
        if ($('#amountToCollect').length) {
            this.updateAdvanceDisplay(amountToCollect, dueAmount);
        }
    }
};