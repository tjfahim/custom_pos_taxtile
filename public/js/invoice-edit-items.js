// invoice-edit-items.js
// Same row markup/classes as invoice-pos-items.js so InvoiceCalculations works
// unchanged, but every row carries an items[x][id] field so the controller's
// update() method knows whether to create, update, or leave a row alone.
const InvoiceEditItems = {
    itemCount: 0,
    deletedIds: [],

    addItemRow: function(data = {}) {
        this.itemCount++;
        const rowId = this.itemCount;
        const idValue = data.id ? data.id : 'new_' + rowId;
        const itemName = data.item_name || 'Three Piece';
        const description = data.description || '';
        const weight = data.weight || 500;
        const quantity = data.quantity || 1;
        const unitPrice = data.unit_price !== undefined ? data.unit_price : '';
        const total = quantity * (unitPrice || 0);

        const row = `
            <tr id="itemRow${rowId}" class="item-row">
                <td>
                    <input type="hidden" name="items[${rowId}][id]" value="${idValue}">
                    <input type="text" name="items[${rowId}][item_name]"
                           class="form-control form-control-sm item-name"
                           placeholder="Item name" value="${itemName}" required>
                </td>
                <td>
                    <input type="text" name="items[${rowId}][description]"
                           class="form-control form-control-sm description"
                           placeholder="Description" value="${description}">
                </td>
                <td>
                    <input type="number" name="items[${rowId}][weight]"
                           class="form-control form-control-sm weight text-center"
                           value="${weight}" min="0" step="1"
                           onchange="InvoiceEditItems.updateItemTotal(${rowId})">
                </td>
                <td>
                    <input type="number" name="items[${rowId}][quantity]"
                           class="form-control form-control-sm quantity text-center"
                           value="${quantity}" min="1" step="1" required
                           onchange="InvoiceEditItems.updateItemTotal(${rowId})"
                           onfocus="this.select()">
                </td>
                <td>
                    <input type="number" name="items[${rowId}][unit_price]"
                           class="form-control form-control-sm unit-price text-right"
                           value="${unitPrice}" min="0" required
                           onchange="InvoiceEditItems.updateItemTotal(${rowId})">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm total-price text-right"
                           readonly value="৳${total.toFixed(0)}">
                    <input type="hidden" name="items[${rowId}][total_price]"
                           class="total-price-hidden" value="${total}">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger"
                            onclick="InvoiceEditItems.removeItemRow(${rowId}, '${idValue}')">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        $('#itemsBody').append(row);

        if (typeof InvoiceCalculations !== 'undefined') {
            InvoiceCalculations.calculateTotals();
        }
        $(document).trigger('itemsChanged');
    },

    removeItemRow: function(rowId, idValue) {
        if (idValue && !String(idValue).startsWith('new_') && !isNaN(idValue)) {
            this.deletedIds.push(idValue);
        }
        $(`#itemRow${rowId}`).remove();

        if ($('#itemsBody tr').length === 0) {
            this.addItemRow();
        } else if (typeof InvoiceCalculations !== 'undefined') {
            InvoiceCalculations.calculateTotals();
        }
        $(document).trigger('itemsChanged');
    },

    updateItemTotal: function(rowId) {
        const quantity = $(`#itemRow${rowId} .quantity`).val() || 0;
        const unitPrice = $(`#itemRow${rowId} .unit-price`).val() || 0;
        const total = quantity * unitPrice;

        $(`#itemRow${rowId} .total-price`).val('৳' + total.toFixed(0));
        $(`#itemRow${rowId} .total-price-hidden`).val(total);

        if (typeof InvoiceCalculations !== 'undefined') {
            InvoiceCalculations.calculateTotals();
        }
    },

    // Called on submit — appends deleted_items[] hidden inputs
    getDeletedIdsInputs: function() {
        return this.deletedIds
            .map(id => `<input type="hidden" name="deleted_items[]" value="${id}">`)
            .join('');
    }
};

window.InvoiceEditItems = InvoiceEditItems;

// Keep the "Add Item" inline onclick (used by the pos partial) working
// if it's ever reused verbatim on this page.
window.addItemRow = function() {
    InvoiceEditItems.addItemRow();
};