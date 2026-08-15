// invoice-edit-return-items.js
const ReturnItemsEdit = {
    itemCount: 0,
    deletedIds: [],
    suppressAutoAdd: false, // set true while invoice-edit-init.js is pre-populating rows

    init: function() {
        $('#hasReturnItems').on('change', function() {
            if ($(this).is(':checked')) {
                $('#returnItemsSection').show();
                $('#addReturnBtn').show();
                if (ReturnItemsEdit.itemCount === 0 && !ReturnItemsEdit.suppressAutoAdd) {
                    ReturnItemsEdit.addReturnRow();
                }
            } else {
                // Track any existing rows as deleted before wiping them
                $('#returnItemsBody tr').each(function() {
                    const idVal = $(this).find('input[name*="[id]"]').val();
                    if (idVal && !String(idVal).startsWith('new_') && !isNaN(idVal)) {
                        ReturnItemsEdit.deletedIds.push(idVal);
                    }
                });

                $('#returnItemsSection').hide();
                $('#addReturnBtn').hide();
                $('#returnItemsBody').empty();
                ReturnItemsEdit.itemCount = 0;

                if (typeof InvoiceCalculations !== 'undefined') {
                    InvoiceCalculations.calculateTotals();
                }
            }
        });
    },

    addReturnRow: function(data = {}) {
        this.itemCount++;
        const rowId = this.itemCount;
        const idValue = data.id ? data.id : 'new_' + rowId;
        const itemName = data.item_name || 'Three Piece';
        const description = data.description || '';
        const weight = data.weight || 500;
        const quantity = data.quantity || 1;
        const unitPrice = data.unit_price !== undefined ? data.unit_price : '';
        const reason = data.return_reason || '';
        const total = quantity * (unitPrice || 0);

        const reasons = {
            damaged: 'Damaged',
            wrong_item: 'Wrong Item',
            customer_request: 'Customer Request',
            quality_issue: 'Quality Issue',
            other: 'Other'
        };
        let options = '<option value="">Select Reason</option>';
        Object.keys(reasons).forEach(key => {
            options += `<option value="${key}" ${reason === key ? 'selected' : ''}>${reasons[key]}</option>`;
        });

        const row = `
            <tr id="returnRow${rowId}" class="return-row">
                <td>
                    <input type="hidden" name="return_items[${rowId}][id]" value="${idValue}">
                    <input type="text" name="return_items[${rowId}][item_name]"
                           class="form-control form-control-sm return-item-name"
                           placeholder="Item name" value="${itemName}" required>
                </td>
                <td>
                    <input type="text" name="return_items[${rowId}][description]"
                           class="form-control form-control-sm return-description"
                           placeholder="Description" value="${description}">
                </td>
                <td>
                    <input type="number" name="return_items[${rowId}][weight]"
                           class="form-control form-control-sm return-weight text-center"
                           value="${weight}" min="0"
                           onchange="ReturnItemsEdit.updateReturnTotal(${rowId})">
                </td>
                <td>
                    <input type="number" name="return_items[${rowId}][quantity]"
                           class="form-control form-control-sm return-quantity text-center"
                           value="${quantity}" min="1" required
                           onchange="ReturnItemsEdit.updateReturnTotal(${rowId})">
                </td>
                <td>
                    <input type="number" name="return_items[${rowId}][unit_price]"
                           class="form-control form-control-sm return-unit-price text-right"
                           value="${unitPrice}" min="0" required
                           onchange="ReturnItemsEdit.updateReturnTotal(${rowId})">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm return-total-price text-right"
                           readonly value="৳${total.toFixed(0)}">
                    <input type="hidden" name="return_items[${rowId}][total_price]"
                           class="return-total-hidden" value="${total}">
                </td>
                <td>
                    <select name="return_items[${rowId}][return_reason]"
                            class="form-control form-control-sm return-reason">
                        ${options}
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger"
                            onclick="ReturnItemsEdit.removeReturnRow(${rowId}, '${idValue}')">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        $('#returnItemsBody').append(row);

        if (typeof InvoiceCalculations !== 'undefined') {
            InvoiceCalculations.calculateTotals();
        }
    },

    removeReturnRow: function(rowId, idValue) {
        if (idValue && !String(idValue).startsWith('new_') && !isNaN(idValue)) {
            this.deletedIds.push(idValue);
        }
        $(`#returnRow${rowId}`).remove();

        if ($('#returnItemsBody tr').length === 0) {
            this.addReturnRow();
        } else if (typeof InvoiceCalculations !== 'undefined') {
            InvoiceCalculations.calculateTotals();
        }
    },

    updateReturnTotal: function(rowId) {
        const quantity = $(`#returnRow${rowId} .return-quantity`).val() || 0;
        const unitPrice = $(`#returnRow${rowId} .return-unit-price`).val() || 0;
        const total = quantity * unitPrice;

        $(`#returnRow${rowId} .return-total-price`).val('৳' + total.toFixed(0));
        $(`#returnRow${rowId} .return-total-hidden`).val(total);

        if (typeof InvoiceCalculations !== 'undefined') {
            InvoiceCalculations.calculateTotals();
        }
    },

    getDeletedIdsInputs: function() {
        return this.deletedIds
            .map(id => `<input type="hidden" name="deleted_return_items[]" value="${id}">`)
            .join('');
    }
};

$(document).ready(function() {
    ReturnItemsEdit.init();
});

window.ReturnItemsEdit = ReturnItemsEdit;