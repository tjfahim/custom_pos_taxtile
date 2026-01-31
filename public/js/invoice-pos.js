// Invoice POS JavaScript Module
const InvoicePOS = {
    itemCount: 0,
    currentInvoiceId: null,
    
    init: function() {
        this.addItemRow();
        this.calculateTotals();
        this.bindEvents();
    },
    
    bindEvents: function() {
        // Customer search
        $('#customerSearch').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#customerTableBody .customer-row').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
        
        // Select customer from modal
        $('.select-customer').click(function() {
            InvoicePOS.selectCustomer($(this));
        });
        
        // Auto-calculate amount to collect when total changes
        $('#total').on('DOMSubtreeModified', function() {
            InvoicePOS.updateAmountToCollect();
        });
        
        // Event Listeners
        $('#deliveryCharge').on('input', () => this.calculateTotals());
        $('#amountToCollect').on('input', () => this.updateDueAmount());
        $('#paidAmount').on('input', () => this.updateDueAmount());
    },
    
    selectCustomer: function(button) {
        var row = button.closest('.customer-row');
        var customerId = row.data('customer-id');
        var customerName = row.data('customer-name');
        var customerPhone = row.data('customer-phone');
        var customerPhone2 = row.data('customer-phone2');
        var customerAddress = row.data('customer-address');
        var customerArea = row.data('customer-area');

        // Set form values
        $('#customerId').val(customerId);
        $('#selectedCustomer').text(customerName + ' (ID: ' + customerId + ')');
        $('#recipientName').val(customerName);
        $('#recipientPhone').val(customerPhone);
        $('#recipientPhone2').val(customerPhone2);
        $('#recipientAddress').val(customerAddress);
        if (customerArea) {
            $('#deliveryArea').val(customerArea);
        }
    },
    
    addItemRow: function() {
        this.itemCount++;
        const row = `
            <tr id="itemRow${this.itemCount}" class="item-row">
                <td>
                    <input type="text" name="items[${this.itemCount}][item_name]" 
                           class="form-control form-control-sm item-name" 
                           placeholder="Item name" required>
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
                           onchange="InvoicePOS.updateItemTotal(${this.itemCount})">
                </td>
                <td>
                    <input type="number" name="items[${this.itemCount}][quantity]" 
                           class="form-control form-control-sm quantity text-center" 
                           value="1" min="1" step="1" required 
                           onchange="InvoicePOS.updateItemTotal(${this.itemCount})">
                </td>
                <td>
                    <input type="number" name="items[${this.itemCount}][unit_price]" 
                           class="form-control form-control-sm unit-price text-right" 
                           value="0" min="0" step="0.01" required 
                           onchange="InvoicePOS.updateItemTotal(${this.itemCount})">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm total-price text-right" 
                           readonly value="৳0.00">
                    <input type="hidden" name="items[${this.itemCount}][total_price]" 
                           class="total-price-hidden" value="0">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-danger" 
                            onclick="InvoicePOS.removeItemRow(${this.itemCount})" 
                            ${this.itemCount === 1 ? 'disabled' : ''}>
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>`;
        $('#itemsBody').append(row);
        this.calculateTotals();
    },
    
    removeItemRow: function(rowId) {
        if (confirm('Remove this item?')) {
            $(`#itemRow${rowId}`).remove();
            this.calculateTotals();
        }
    },
    
    updateItemTotal: function(rowId) {
        const quantity = $(`#itemRow${rowId} .quantity`).val() || 0;
        const unitPrice = $(`#itemRow${rowId} .unit-price`).val() || 0;
        const total = quantity * unitPrice;
        
        $(`#itemRow${rowId} .total-price`).val('৳' + total.toFixed(2));
        $(`#itemRow${rowId} .total-price-hidden`).val(total);
        
        this.calculateTotals();
    },
    
    calculateTotals: function() {
        let subtotal = 0;
        let totalWeight = 0;
        
        // Calculate from items
        $('.total-price-hidden').each(function() {
            subtotal += parseFloat($(this).val()) || 0;
        });
        
        $('.weight').each(function() {
            totalWeight += parseInt($(this).val()) || 0;
        });
        
        // Get delivery charge (default 60)
        const deliveryCharge = parseFloat($('#deliveryCharge').val()) || 60;
        const total = subtotal + deliveryCharge;
        
        // Update display
        $('#subtotal').text('৳' + subtotal.toFixed(2));
        $('#deliveryAmount').text('৳' + deliveryCharge.toFixed(2));
        $('#total').text('৳' + total.toFixed(2));
        
        // Auto-update amount to collect
        this.updateAmountToCollect();
        this.updateDueAmount();
    },
    
    updateAmountToCollect: function() {
        const total = parseFloat($('#total').text().replace('৳', '')) || 0;
        $('#amountToCollect').val(total.toFixed(2));
    },
    
    updateDueAmount: function() {
        const total = parseFloat($('#total').text().replace('৳', '')) || 0;
        const paid = parseFloat($('#paidAmount').val()) || 0;
        const amountToCollect = parseFloat($('#amountToCollect').val()) || total;
        
        // Due = (Total - Advance Paid) OR use custom amount to collect
        const due = amountToCollect - paid;
        
        $('#dueAmount').text('৳' + due.toFixed(2));
    },
    
    togglePaymentDetails: function() {
        const method = $('#paymentMethod').val();
        
        // Hide all details first
        $('#bkashDetails').hide();
        $('#bankDetails').hide();
        
        // Clear payment details
        $('[name="payment_details"]').val('');
        
        // Show relevant section
        if (method === 'bkash') {
            $('#bkashDetails').show();
        } else if (method === 'bank_transfer') {
            $('#bankDetails').show();
        }
    },
    
    saveAndPrint: function() {
        // Validate form
        if (!this.validateForm()) {
            return;
        }
        
        // Show loading
        this.showLoading();
        
        // Prepare form data
        const formData = new FormData(document.getElementById('posForm'));
        
        // Add AJAX indicator
        formData.append('is_ajax', '1');
        
        // Send AJAX request
        $.ajax({
            url: $('#posForm').attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                InvoicePOS.hideLoading();
                console.log('Response:', response);
                
                if (response.success) {
                    InvoicePOS.currentInvoiceId = response.invoice_id;
                    console.log('Invoice ID:', InvoicePOS.currentInvoiceId);
                    
                    // Build the print URL
                    const printUrl = response.print_url || `/invoices/${response.invoice_id}/print`;
                    console.log('Print URL:', printUrl);
                    
                    // Load invoice in iframe
                    const iframe = $('#invoiceIframe');
                    iframe.attr('src', printUrl);
                    
                    // Remove any previous load handlers
                    iframe.off('load');
                    
                    // Show print modal after iframe loads
                    iframe.on('load', function() {
                        console.log('Iframe loaded successfully');
                        $('#printModal').modal('show');
                        
                        // Auto-print after 1 second
                        setTimeout(() => {
                            InvoicePOS.printInvoice();
                        }, 1000);
                    });
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                InvoicePOS.hideLoading();
                console.log('AJAX Error:', xhr.responseText);
                let errorMessage = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join('\n');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                alert(errorMessage);
            }
        });
    },
    
    validateForm: function() {
        // Check customer selected
        if (!$('#customerId').val()) {
            alert('Please select a customer');
            return false;
        }
        
        // Check recipient name
        if (!$('#recipientName').val().trim()) {
            alert('Please enter recipient name');
            $('#recipientName').focus();
            return false;
        }
        
        // Check recipient phone
        if (!$('#recipientPhone').val().trim()) {
            alert('Please enter recipient phone');
            $('#recipientPhone').focus();
            return false;
        }
        
        // Check address
        if (!$('#recipientAddress').val().trim()) {
            alert('Please enter address');
            $('#recipientAddress').focus();
            return false;
        }
        
        // Check delivery area
        if (!$('#deliveryArea').val().trim()) {
            alert('Please enter delivery area');
            $('#deliveryArea').focus();
            return false;
        }
        
        // Check at least one item
        if ($('#itemsBody tr').length === 0) {
            alert('Please add at least one item');
            return false;
        }
        
        // Check all items have names
        let validItems = true;
        $('.item-name').each(function() {
            if (!$(this).val().trim()) {
                validItems = false;
                $(this).focus();
                return false;
            }
        });
        
        if (!validItems) {
            alert('Please enter item name for all items');
            return false;
        }
        
        // Check all items have unit price
        let validPrices = true;
        $('.unit-price').each(function() {
            const price = parseFloat($(this).val()) || 0;
            if (price <= 0) {
                validPrices = false;
                $(this).focus();
                return false;
            }
        });
        
        if (!validPrices) {
            alert('Please enter valid unit price (greater than 0) for all items');
            return false;
        }
        
        return true;
    },
    
    printInvoice: function() {
        try {
            const iframe = document.getElementById('invoiceIframe');
            if (iframe && iframe.contentWindow) {
                iframe.contentWindow.print();
            } else {
                console.error('Iframe not found or not loaded');
                // Fallback to direct printing
                window.open($('#invoiceIframe').attr('src'), '_blank').print();
            }
        } catch (error) {
            console.error('Print error:', error);
            // Fallback: redirect to print page
            window.open($('#invoiceIframe').attr('src'), '_blank');
        }
    },
    
    createNewInvoice: function() {
        // Close modal
        $('#printModal').modal('hide');
        
        // Reset form
        this.resetForm();
        
        // Show success message
        alert('Invoice created successfully! You can now create a new invoice.');
    },
    
    resetForm: function() {
        if (confirm('Clear entire form? This will remove all items.')) {
            $('#posForm')[0].reset();
            $('#itemsBody').empty();
            this.itemCount = 0;
            this.addItemRow();
            $('#deliveryCharge').val(60); // Reset delivery charge to default
            this.calculateTotals();
            $('#selectedCustomer').text('No customer selected');
            $('#customerId').val('');
            $('#bkashDetails').hide();
            $('#bankDetails').hide();
        }
    },
    
    showLoading: function() {
        if (!$('#loadingOverlay').length) {
            $('body').append(`
                <div class="loading-overlay" id="loadingOverlay">
                    <div class="loading-spinner">
                        <i class="fa fa-spinner fa-spin"></i> Saving Invoice...
                    </div>
                </div>
            `);
        }
        $('#loadingOverlay').show();
    },
    
    hideLoading: function() {
        $('#loadingOverlay').hide();
    }
};

// Initialize when document is ready
$(document).ready(function() {
    InvoicePOS.init();
});