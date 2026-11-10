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

        // Wholesale discount (0 unless Wholesale is ticked and a discount chosen)
        const netSubtotal = subtotal - returnSubtotal;
        const discountAmount = this.getDiscountAmount(netSubtotal);

        // Calculate total (subtotal - returnSubtotal - discount + delivery)
        const total = (netSubtotal - discountAmount) + deliveryCharge;

        // Get advance payment(s). paidAmount2 only exists/matters for
        // in-house sales — it's 0/hidden otherwise, so this is safe to
        // always include.
        const advancePayment = parseFloat($('#paidAmount').val()) || 0;
        const advancePayment2 = parseFloat($('#paidAmount2').val()) || 0;
        const totalAdvance = advancePayment + advancePayment2;

        // Calculate due amount against BOTH advance payments combined
        const dueAmount = Math.max(0, total - totalAdvance);

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

        // 3b. Wholesale discount
        if (discountAmount > 0) {
            $('#discountRow').show();
            $('#discountDisplay').text('৳' + discountAmount.toFixed(0));
        } else {
            $('#discountRow').hide();
        }
        $('#discountPreview').text('৳' + discountAmount.toFixed(0));

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

        // 7. Advance Payment(s) and Due
        this.updateAdvanceDisplay(advancePayment, advancePayment2, dueAmount);

        // 8. Update hidden inputs
        if ($('#subtotalInput').length) $('#subtotalInput').val(subtotal.toFixed(0));
        if ($('#totalInput').length) $('#totalInput').val(total.toFixed(0));
        if ($('#returnSubtotalInput').length) $('#returnSubtotalInput').val(returnSubtotal.toFixed(0));
        if ($('#dueInput').length) $('#dueInput').val(dueAmount.toFixed(0));

        this.updateExchangeInstructions();
    },

    // Discount in taka for the given base amount (items minus returns).
    // Mirrors Invoice::computeDiscountAmount() on the server.
    getDiscountAmount: function(baseAmount) {
        if (!$('#isWholesale').is(':checked')) return 0;

        const type = $('#discountType').val();
        const value = parseFloat($('#discountValue').val()) || 0;
        if (!type || value <= 0 || baseAmount <= 0) return 0;

        let discount = 0;
        if (type === 'percent') {
            discount = baseAmount * Math.min(value, 100) / 100;
        } else if (type === 'fixed') {
            discount = value;
        }
        return Math.min(Math.round(discount * 100) / 100, baseAmount);
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

    // Shows ONE combined "Advance" line = advancePayment + advancePayment2.
    // advancePayment2 is optional — undefined just treated as 0.
    updateAdvanceDisplay: function(advancePayment, advancePayment2, dueAmount) {
        advancePayment = advancePayment || 0;
        advancePayment2 = advancePayment2 || 0;
        const totalAdvance = advancePayment + advancePayment2;

        if (totalAdvance > 0) {
            $('#advancePaymentRow').show();
            $('#advanceAmount').text('৳' + totalAdvance.toFixed(0));
        } else {
            $('#advancePaymentRow').hide();
        }

        // No longer used — kept hidden if present in older markup.
        if ($('#advancePayment2Row').length) {
            $('#advancePayment2Row').hide();
        }

        $('#dueAmount').text('৳' + dueAmount.toFixed(0));
        if ($('#dueInput').length) $('#dueInput').val(dueAmount.toFixed(0));
    },

    updateDueAmount: function() {
        const total = parseFloat($('#total').text().replace('৳', '')) || 0;
        const advancePayment = parseFloat($('#paidAmount').val()) || 0;
        const advancePayment2 = parseFloat($('#paidAmount2').val()) || 0;
        const dueAmount = Math.max(0, total - (advancePayment + advancePayment2));
        this.updateAdvanceDisplay(advancePayment, advancePayment2, dueAmount);
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
        const newText = `Return korle delivery charge ${deliveryCharge} tk niben ( আনুষাঙ্গিক কোনো ইসু থাকলে প্যানেলে মেসেজ দিবেন। নাম্বারে যোগাযোগ করার সময় - সকাল ১১.৩০ থেকে রাত ৯ টার মধ্যে)`;
        specialInstructions.value = newText;
    }
}

// Whenever the delivery charge changes, update ONLY the charge number inside the
// special instructions (e.g. "delivery charge 150 tk" -> "delivery charge 100 tk").
// Any other text the user typed is left untouched.
function syncSpecialInstructionsCharge() {
    const chargeInput = document.getElementById('deliveryCharge');
    const specialInstructions = document.querySelector('textarea[name="special_instructions"]');
    if (!chargeInput || !specialInstructions) return;

    const parsed = parseFloat(chargeInput.value);
    const charge = isNaN(parsed) ? 0 : parsed;
    const pattern = /(delivery charge\s*)\d+(?:\.\d+)?(\s*tk)/i;

    if (pattern.test(specialInstructions.value)) {
        specialInstructions.value = specialInstructions.value.replace(pattern, (m, before, after) => before + charge + after);
    } else if (!specialInstructions.value.trim() && !window.__INVOICE_DATA__) {
        // Empty box on the create page → put the default note back
        updateSpecialInstructions();
    }
}

$(document).on('input change', '#deliveryCharge', function() {
    syncSpecialInstructionsCharge();
    // The edit page has no other listener for this, so keep Total/Due live here too
    if (typeof InvoiceCalculations !== 'undefined') {
        InvoiceCalculations.calculateTotals();
    }
});

// Keep Due live while the (first) advance amount is typed — the create page binds this
// in invoice-pos-core.js, the edit page does not load that file.
$(document).on('input', '#paidAmount', function() {
    if (typeof InvoiceCalculations !== 'undefined') {
        InvoiceCalculations.updateDueAmount();
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // On the edit page the saved special instructions must not be replaced by the default text
    if (!window.__INVOICE_DATA__) {
        updateSpecialInstructions();
    }
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

// NEW: recalculate whenever the second advance amount changes, so Due
// updates live the same way it already does for the first advance amount.
$(document).on('input', '#paidAmount2', function() {
    if (typeof InvoiceCalculations !== 'undefined') {
        InvoiceCalculations.updateDueAmount();
    }
});