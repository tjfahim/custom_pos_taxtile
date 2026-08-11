// Invoice Calculations Module - Handles all calculations
const InvoiceCalculations = {
    
    calculateTotals: function() {
        let subtotal = 0;
        let totalQuantity = 0;
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
            totalQuantity += quantity;
            totalWeightGrams += (weightGrams * quantity);
        });
        
        // Calculate return items subtotal
        $('.return-row').each(function() {
            const quantity = parseFloat($(this).find('.return-quantity').val()) || 0;
            const unitPrice = parseFloat($(this).find('.return-unit-price').val()) || 0;
            const total = quantity * unitPrice;
            
            // Update return item total display
            $(this).find('.return-total-price').text('৳' + total.toFixed(0));
            $(this).find('.return-total-hidden').val(total);
            
            returnSubtotal += total;
            returnQuantity += quantity;
        });
        
        // Convert grams to kilograms
        const totalWeightKg = totalWeightGrams / 1000;
        
        // Get delivery charge
        const deliveryCharge = parseFloat($('#deliveryCharge').val()) || 0;
        
        // Calculate total (subtotal - returnSubtotal + delivery)
        const total = (subtotal - returnSubtotal) + deliveryCharge;
        
        // Get advance payment
        const advancePayment = parseFloat($('#paidAmount').val()) || 0;
        
        // Calculate due amount
        const dueAmount = Math.max(0, total - advancePayment);
        
        // --- UPDATE DISPLAY ---
        
        // 1. Total Quantity
        $('#totalQuantityDisplay').text(totalQuantity);
        
        // 2. Subtotal
        $('#subtotalDisplay').text('৳' + subtotal.toFixed(0));
        
        // 3. Return Items
        if (returnQuantity > 0 || returnSubtotal > 0) {
            $('#returnSubtotalRow').show();
            $('#returnItemsCount').text(returnQuantity);
            $('#returnSubtotal').text('৳' + returnSubtotal.toFixed(0));
        } else {
            $('#returnSubtotalRow').hide();
        }
        
        // 4. Delivery Amount
        $('#deliveryAmount').text('৳' + deliveryCharge.toFixed(0));
        
        // 5. Total
        $('#total').text('৳' + total.toFixed(0));
        
        // 6. Total Weight
        if (totalWeightKg > 0) {
            $('#totalWeightRow').show();
            $('#totalWeight').text(totalWeightKg.toFixed(3) + ' kg');
        } else {
            $('#totalWeightRow').hide();
        }
        
        // 7. Advance Payment and Due
        this.updateAdvanceDisplay(advancePayment, dueAmount);
        
        // 8. Update hidden inputs
        if ($('#subtotalInput').length) $('#subtotalInput').val(subtotal.toFixed(0));
        if ($('#totalInput').length) $('#totalInput').val(total.toFixed(0));
        if ($('#returnSubtotalInput').length) $('#returnSubtotalInput').val(returnSubtotal.toFixed(0));
        if ($('#dueInput').length) $('#dueInput').val(dueAmount.toFixed(0));

          this.updateExchangeInstructions();
    },
      updateExchangeInstructions: function() {
        const courier = $('#courierName').val();
        if (courier !== 'Exchange') return; // no need to change anything otherwise
        
        let totalReturnQty = 0;
        let totalReturnPrice = 0;
        
        $('.return-row').each(function() {
            const quantity = parseFloat($(this).find('.return-quantity').val()) || 0;
            const unitPrice = parseFloat($(this).find('.return-unit-price').val()) || 0;
            totalReturnQty += quantity;
            totalReturnPrice += quantity * unitPrice;
        });
        
        const exchangeText = `${totalReturnQty} টি থ্রি পিস এক্সচেঞ্জ করবেন এক্সচেঞ্জ থ্রি পিস এর দাম  ${totalReturnPrice.toFixed(0)} টাকা`;
        
        $('textarea[name="special_instructions"]').val(exchangeText);
    },
    updateAdvanceDisplay: function(advancePayment, dueAmount) {
        if (advancePayment > 0) {
            $('#advancePaymentRow').show();
            $('#advanceAmount').text('৳' + advancePayment.toFixed(0));
        } else {
            $('#advancePaymentRow').hide();
        }
        
        $('#dueAmount').text('৳' + dueAmount.toFixed(0));
        if ($('#dueInput').length) $('#dueInput').val(dueAmount.toFixed(0));
    },
    
    updateDueAmount: function() {
        const total = parseFloat($('#total').text().replace('৳', '')) || 0;
        const advancePayment = parseFloat($('#paidAmount').val()) || 0;
        const dueAmount = Math.max(0, total - advancePayment);
        this.updateAdvanceDisplay(advancePayment, dueAmount);
    },
    
    calculateTotalWeight: function() {
        let totalWeightGrams = 0;
        $('.item-row').each(function() {
            const quantity = parseFloat($(this).find('.quantity').val()) || 0;
            const weightGrams = parseFloat($(this).find('.item-weight').val()) || 0;
            totalWeightGrams += (weightGrams * quantity);
        });
        return totalWeightGrams / 1000;
    },
    
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

// Update special instructions
function updateSpecialInstructions() {
    const deliveryCharge = document.getElementById('deliveryCharge').value;
    const specialInstructions = document.querySelector('textarea[name="special_instructions"]');
    
    if (specialInstructions) {
        const newText = `Return korle delivery charge ${deliveryCharge} tk niben ( আনুষাঙ্গিক কোনো ইসু থাকলে প্যানেলে মেসেজ দিবেন। নাম্বারে যোগাযোগ করার সময় - সকাল ১১.৩০ থেকে রাত ৯ টার মধ্যে)`;
        specialInstructions.value = newText;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSpecialInstructions();
    // Calculate initial totals
    if (typeof InvoiceCalculations !== 'undefined') {
        InvoiceCalculations.calculateTotals();
                InvoiceCalculations.updateExchangeInstructions();

    }
});
$(document).on('change', '#courierName', function() {
    const courier = $(this).val();
    
    if (courier === 'Exchange') {
        if (typeof InvoiceCalculations !== 'undefined') {
            InvoiceCalculations.updateExchangeInstructions();
        }
    } else {
        // Switched away from Exchange — restore the normal default text
        if (typeof updateSpecialInstructions === 'function') {
            updateSpecialInstructions();
        }
    }
});
// Recalculate when return items change
$(document).on('change', '.return-quantity, .return-unit-price', function() {
    if (typeof InvoiceCalculations !== 'undefined') {
        InvoiceCalculations.calculateTotals();
    }
});