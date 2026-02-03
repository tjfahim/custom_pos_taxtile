// invoice-pos-calculations.js
const InvoiceCalculations = {
    
    calculateTotals: function() {
        let subtotal = 0;
        let totalQuantity = 0;
        let totalWeightGrams = 0;
        
        // Calculate subtotal, quantity, and weight from items
        $('.item-row').each(function() {
            const quantity = parseFloat($(this).find('.quantity').val()) || 0;
            const unitPrice = parseFloat($(this).find('.unit-price').val()) || 0;
            const weightGrams = parseFloat($(this).find('.item-weight').val()) || 0;
            const total = quantity * unitPrice;
            
            // Update item total display
            $(this).find('.total-price').text('৳' + total.toFixed(2));
            $(this).find('.total-price-hidden').val(total);
            
            subtotal += total;
            totalQuantity += quantity;
            totalWeightGrams += (weightGrams * quantity);
        });
        
        // Convert grams to kilograms (divide by 1000)
        const totalWeightKg = totalWeightGrams / 1000;
        
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
        $('#totalQuantity').text(totalQuantity);
        
        // Show/hide total weight row (in kg)
        const totalWeightRow = $('#totalWeightRow');
        const totalWeightDisplay = $('#totalWeight');
        if (totalWeightKg > 0) {
            totalWeightRow.show();
            totalWeightDisplay.text(totalWeightKg.toFixed(3) + ' kg'); // 3 decimal places for grams conversion
        } else {
            totalWeightRow.hide();
        }
        
        // Update hidden inputs if they exist
        if ($('#subtotalInput').length) $('#subtotalInput').val(subtotal.toFixed(2));
        if ($('#totalInput').length) $('#totalInput').val(total.toFixed(2));
        
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
        if ($('#dueInput').length) $('#dueInput').val(dueAmount.toFixed(2));
    },
    
    updateDueAmount: function() {
        const total = parseFloat($('#total').text().replace('৳', '')) || 0;
        const advancePayment = parseFloat($('#paidAmount').val()) || 0;
        const dueAmount = Math.max(0, total - advancePayment);
        
        this.updateAdvanceDisplay(advancePayment, dueAmount);
    },
    
    // New: Calculate total weight in kg
    calculateTotalWeight: function() {
        let totalWeightGrams = 0;
        
        $('.item-row').each(function() {
            const quantity = parseFloat($(this).find('.quantity').val()) || 0;
            const weightGrams = parseFloat($(this).find('.item-weight').val()) || 0;
            totalWeightGrams += (weightGrams * quantity);
        });
        
        return totalWeightGrams / 1000; // Return in kg
    }
};