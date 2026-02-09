// Invoice Items Module - Handles item rows management
const InvoiceItems = {
    itemCount: 0,
    
    addItemRow: function() {
        this.itemCount++;
        const row = `
            <tr id="itemRow${this.itemCount}" class="item-row">
                <td>
                    <input type="text" name="items[${this.itemCount}][item_name]" 
                           class="form-control form-control-sm item-name" 
                           placeholder="Item name" value="Three Piece" required>
                </td>
                <td>
                    <input type="text" name="items[${this.itemCount}][description]" 
                           class="form-control form-control-sm description" 
                           placeholder="Description">
                </td>
                <td>
                    <input type="number" name="items[${this.itemCount}][weight]" 
                           class="form-control form-control-sm weight text-center" 
                           value="500" min="0" step="1"
                           onchange="InvoiceItems.updateItemTotal(${this.itemCount})">
                </td>
                <td>
                    <input type="number" name="items[${this.itemCount}][quantity]" 
                           class="form-control form-control-sm quantity text-center" 
                           value="1" min="1" step="1" required 
                           onchange="InvoiceItems.updateItemTotal(${this.itemCount})">
                </td>
                <td>
                    <input type="number" name="items[${this.itemCount}][unit_price]" 
                           class="form-control form-control-sm unit-price text-right" 
                           value="" min="0" step="0.01" required 
                           onchange="InvoiceItems.updateItemTotal(${this.itemCount})">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm total-price text-right" 
                           readonly value="৳0.00">
                    <input type="hidden" name="items[${this.itemCount}][total_price]" 
                           class="total-price-hidden" value="0">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger" 
                            onclick="InvoiceItems.removeItemRow(${this.itemCount})" 
                            ${this.itemCount === 1 ? 'disabled' : ''}>
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        $('#itemsBody').append(row);
        if (typeof InvoiceCalculations !== 'undefined') {
            InvoiceCalculations.calculateTotals();
        }
    },
    
    removeItemRow: function(rowId) {
        if (confirm('Remove this item?')) {
            $(`#itemRow${rowId}`).remove();
            if (typeof InvoiceCalculations !== 'undefined') {
                InvoiceCalculations.calculateTotals();
            }
        }
    },
    
    updateItemTotal: function(rowId) {
        const quantity = $(`#itemRow${rowId} .quantity`).val() || 0;
        const unitPrice = $(`#itemRow${rowId} .unit-price`).val() || 0;
        const total = quantity * unitPrice;
        
        $(`#itemRow${rowId} .total-price`).val('৳' + total.toFixed(2));
        $(`#itemRow${rowId} .total-price-hidden`).val(total);
        
        if (typeof InvoiceCalculations !== 'undefined') {
            InvoiceCalculations.calculateTotals();
        }
    }
};

// Make addItemRow globally accessible
window.addItemRow = function() {
    InvoiceItems.addItemRow();
};