// Invoice Calculations Module - Handles all calculations
const InvoiceCalculations = {
    
    calculateTotals: function() {
        let subtotal = 0;
        let totalQuantity = '';
        let totalWeightGrams = 0;
        let returnSubtotal = 0;
        let returnQuantity = 0;
        
        // Calculate subtotal, quantity, and weight from items
        $('.item-row').each(function() {
            const quantity = parseFloat($(this).find('.quantity').val()) || 0;
            const unitPrice = parseFloat($(this).find('.unit-price').val()) || 0;
            const weightGrams = parseFloat($(this).find('.item-weight').val()) || 0;
            const total = quantity * unitPrice;
            
            // Update item total display
            $(this).find('.total-price').text('৳' + total.toFixed(0));
            $(this).find('.total-price-hidden').val(total);
            
            subtotal += total;
            totalQuantity +='Total Items ' + (quantity);
            totalWeightGrams += (weightGrams * quantity);
        });
        
        // Calculate return items subtotal
        $('.return-row').each(function() {
            const quantity = parseFloat($(this).find('.return-quantity').val()) || 0;
            const unitPrice = parseFloat($(this).find('.return-unit-price').val()) || 0;
            const total = quantity * unitPrice;
            
            // Update return item total display
            $(this).find('.return-total-price').val('৳' + total.toFixed(0));
            $(this).find('.return-total-hidden').val(total);
            
            returnSubtotal += total;
            returnQuantity += quantity;
        });
        
        // Convert grams to kilograms (divide by 1000)
        const totalWeightKg = totalWeightGrams / 1000;
        
        // Get delivery charge
        const deliveryCharge = parseFloat($('#deliveryCharge').val()) || 0;
        
        // Calculate total (subtotal - returnSubtotal + delivery)
        const total = subtotal - returnSubtotal + deliveryCharge;
        
        // Get advance payment
        const advancePayment = parseFloat($('#paidAmount').val()) || 0;
        
        // Calculate due amount
        const dueAmount = Math.max(0, total - advancePayment);
        
        // Update display
        $('#subtotal').text('৳' + subtotal.toFixed(0));
        $('#returnSubtotal').text('৳' + returnSubtotal.toFixed(0));
        $('#deliveryAmount').text('৳' + deliveryCharge.toFixed(0));
        $('#total').text('৳' + total.toFixed(0));
        $('#totalQuantity').text(totalQuantity);
        $('#returnQuantity').text(returnQuantity);
        
        // Show/hide return items row in summary
        const returnSubtotalRow = $('#returnSubtotalRow');
        if (returnSubtotal > 0 || returnQuantity > 0) {
            returnSubtotalRow.show();
            $('#returnItemsCount').text(returnQuantity);
        } else {
            returnSubtotalRow.hide();
        }
        
        // Show/hide total weight row (in kg)
        const totalWeightRow = $('#totalWeightRow');
        const totalWeightDisplay = $('#totalWeight');
        if (totalWeightKg > 0) {
            totalWeightRow.show();
            totalWeightDisplay.text(totalWeightKg.toFixed(3) + ' kg');
        } else {
            totalWeightRow.hide();
        }
        
        // Update hidden inputs if they exist
        if ($('#subtotalInput').length) $('#subtotalInput').val(subtotal.toFixed(0));
        if ($('#totalInput').length) $('#totalInput').val(total.toFixed(0));
        if ($('#returnSubtotalInput').length) $('#returnSubtotalInput').val(returnSubtotal.toFixed(0));
        
        // Update advance payment display
        this.updateAdvanceDisplay(advancePayment, dueAmount);
    },
    
    updateAdvanceDisplay: function(advancePayment, dueAmount) {
        const advanceRow = $('#advancePaymentRow');
        const advanceAmount = $('#advanceAmount');
        
        if (advancePayment > 0) {
            // Show advance payment row
            advanceRow.show();
            advanceAmount.text('৳' + advancePayment.toFixed(0));
        } else {
            // Hide advance payment row
            advanceRow.hide();
        }
        
        // Update due amount
        $('#dueAmount').text('৳' + dueAmount.toFixed(0));
        if ($('#dueInput').length) $('#dueInput').val(dueAmount.toFixed(0));
    },
    
    updateDueAmount: function() {
        const total = parseFloat($('#total').text().replace('৳', '')) || 0;
        const advancePayment = parseFloat($('#paidAmount').val()) || 0;
        const dueAmount = Math.max(0, total - advancePayment);
        
        this.updateAdvanceDisplay(advancePayment, dueAmount);
    },
    
    // Calculate total weight in kg
    calculateTotalWeight: function() {
        let totalWeightGrams = 0;
        
        $('.item-row').each(function() {
            const quantity = parseFloat($(this).find('.quantity').val()) || 0;
            const weightGrams = parseFloat($(this).find('.item-weight').val()) || 0;
            totalWeightGrams += (weightGrams * quantity);
        });
        
        return totalWeightGrams / 1000; // Return in kg
    },
    
    // Calculate return items total
    calculateReturnTotal: function() {
        let returnTotal = 0;
        
        $('.return-row').each(function() {
            const quantity = parseFloat($(this).find('.return-quantity').val()) || 0;
            const unitPrice = parseFloat($(this).find('.return-unit-price').val()) || 0;
            returnTotal += quantity * unitPrice;
        });
        
        return returnTotal;
    }
};