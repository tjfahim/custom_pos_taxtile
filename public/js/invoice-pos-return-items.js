// Return Items Module - Handles return items management
const ReturnItems = {
    itemCount: 0,
    
    init: function() {
        // Toggle return items section
        $('#hasReturnItems').on('change', function() {
            if ($(this).is(':checked')) {
                $('#returnItemsSection').show();
                $('#addReturnBtn').show();
                if (ReturnItems.itemCount === 0) {
                    ReturnItems.addReturnRow();
                }
            } else {
                $('#returnItemsSection').hide();
                $('#addReturnBtn').hide();
                $('#returnItemsBody').empty();
                ReturnItems.itemCount = 0;
                // Recalculate totals
                if (typeof InvoiceCalculations !== 'undefined') {
                    InvoiceCalculations.calculateTotals();
                }
            }
        });
    },
       addReturnRow: function() {
        this.itemCount++;
        const row = `
            <tr id="returnRow${this.itemCount}" class="return-row">
                <td>
                    <input type="text" name="return_items[${this.itemCount}][item_name]" 
                           class="form-control form-control-sm return-item-name" 
                           placeholder="Item name" value="Three Piece" required>
                </td>
                <td>
                    <input type="text" name="return_items[${this.itemCount}][description]" 
                           class="form-control form-control-sm return-description" 
                           placeholder="Description">
                </td>
                <td>
                    <input type="number" name="return_items[${this.itemCount}][weight]" 
                           class="form-control form-control-sm return-weight text-center" 
                           value="500" min="0" step="1"
                           onchange="ReturnItems.updateReturnTotal(${this.itemCount})">
                </td>
                <td>
                    <input type="number" name="return_items[${this.itemCount}][quantity]" 
                           class="form-control form-control-sm return-quantity text-center" 
                           value="1" min="1" step="1" required 
                           onchange="ReturnItems.updateReturnTotal(${this.itemCount})">
                </td>
                <td>
                    <input type="number" name="return_items[${this.itemCount}][unit_price]" 
                           class="form-control form-control-sm return-unit-price text-right" 
                           value="" min="0" step="0.01" required 
                           onchange="ReturnItems.updateReturnTotal(${this.itemCount})">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm return-total-price text-right" 
                           readonly value="৳0.00">
                    <input type="hidden" name="return_items[${this.itemCount}][total_price]" 
                           class="return-total-hidden" value="0">
                </td>
                <td>
                    <select name="return_items[${this.itemCount}][return_reason]" 
                            class="form-control form-control-sm return-reason">
                        <option value="">Select Reason</option>
                        <option value="damaged">Damaged</option>
                        <option value="wrong_item">Wrong Item</option>
                        <option value="customer_request">Customer Request</option>
                        <option value="quality_issue">Quality Issue</option>
                        <option value="other">Other</option>
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger" 
                            onclick="ReturnItems.removeReturnRow(${this.itemCount})" 
                            ${this.itemCount === 1 ? 'disabled' : ''}>
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        $('#returnItemsBody').append(row);
        
        // Recalculate totals after adding row
        if (typeof InvoiceCalculations !== 'undefined') {
            InvoiceCalculations.calculateTotals();
        }
    },
    
    removeReturnRow: function(rowId) {
        $(`#returnRow${rowId}`).remove();
        if ($('#returnItemsBody tr').length === 0) {
            // Auto add one row if empty
            this.addReturnRow();
        } else {
            // Recalculate totals
            if (typeof InvoiceCalculations !== 'undefined') {
                InvoiceCalculations.calculateTotals();
            }
        }
    },
    
    updateReturnTotal: function(rowId) {
        const quantity = $(`#returnRow${rowId} .return-quantity`).val() || 0;
        const unitPrice = $(`#returnRow${rowId} .return-unit-price`).val() || 0;
        const total = quantity * unitPrice;
        
        $(`#returnRow${rowId} .return-total-price`).val('৳' + total.toFixed(2));
        $(`#returnRow${rowId} .return-total-hidden`).val(total);
        
        // Recalculate totals
        if (typeof InvoiceCalculations !== 'undefined') {
            InvoiceCalculations.calculateTotals();
        }
    },
    
    getReturnData: function() {
        const returnItems = [];
        $('#returnItemsBody tr').each(function() {
            const row = $(this);
            returnItems.push({
                item_name: row.find('.return-item-name').val(),
                description: row.find('.return-description').val(),
                weight: row.find('.return-weight').val() || 500,
                quantity: row.find('.return-quantity').val() || 1,
                unit_price: row.find('.return-unit-price').val() || 0,
                total_price: row.find('.return-total-hidden').val() || 0,
                return_reason: row.find('.return-reason').val(),
            });
        });
        return returnItems;
    },
    
    reset: function() {
        $('#returnItemsBody').empty();
        this.itemCount = 0;
        $('#hasReturnItems').prop('checked', false);
        $('#returnItemsSection').hide();
        $('#addReturnBtn').hide();
        
        // Recalculate totals
        if (typeof InvoiceCalculations !== 'undefined') {
            InvoiceCalculations.calculateTotals();
        }
    }
};

// Initialize on document ready
$(document).ready(function() {
    ReturnItems.init();
});

// Make globally accessible
window.ReturnItems = ReturnItems;