// invoices-edit.js - JavaScript for invoice edit page with separated calculations

(function() {
    'use strict';
    
    class InvoiceEditor {
        constructor() {
            this.itemCounter = window.invoiceData ? window.invoiceData.itemCount : 0;
            this.returnItemCounter = window.invoiceData ? window.invoiceData.returnItemCount : 0;
            this.newItemCounter = 0;
            this.newReturnItemCounter = 0;
            this.init();
        }
        
        init() {
            $(document).ready(() => {
                this.bindEvents();
                this.updateAllCalculations();
                this.updateStatusStyle();
                this.formatPhoneNumber();
                
                // Handle return items visibility on load
                if (window.invoiceData && window.invoiceData.hasReturnItems) {
                    this.initReturnItems();
                }
            });
        }
        
        bindEvents() {
            // Add item button
            $('#addItemBtn').on('click', () => this.addNewItem());
            
            // Delete item
            $(document).on('click', '.delete-item', (e) => this.deleteItem(e));
            
            // Add return item button
            $('#addReturnItemBtn').on('click', () => this.addNewReturnItem());
            
            // Delete return item
            $(document).on('click', '.delete-return-item', (e) => this.deleteReturnItem(e));
            
            // Toggle return items
            $('#hasReturnItems').on('change', () => this.toggleReturnItems());
            
            // Calculate on input changes
            $(document).on('input', '.quantity, .unit-price, .return-quantity, .return-unit-price, #delivery_charge', () => this.updateAllCalculations());
            
            // Auto-select on focus
            $(document).on('focus', 'input', function() {
                $(this).select();
            });
            
            // Status dropdown change
            $('#status').on('change', () => this.updateStatusStyle());
            
            // Phone number formatting
            $('#customer_phone').on('input', () => this.formatPhoneNumber());
            
            // Form submission
            $('#editInvoiceForm').on('submit', (e) => this.validateForm(e));
        }
        
        initReturnItems() {
            $('#returnItemsSection').show();
            $('#addReturnItemBtn').show();
            if ($('#returnItemsTableBody tr').length === 0) {
                this.addNewReturnItem();
            }
        }
        
        toggleReturnItems() {
            if ($('#hasReturnItems').is(':checked')) {
                $('#returnItemsSection').show();
                $('#addReturnItemBtn').show();
                if ($('#returnItemsTableBody tr').length === 0) {
                    this.addNewReturnItem();
                }
            } else {
                $('#returnItemsSection').hide();
                $('#addReturnItemBtn').hide();
                $('#returnItemsTableBody').empty();
                this.returnItemCounter = 0;
                this.newReturnItemCounter = 0;
                this.updateAllCalculations();
            }
        }
        
        calculateWeight(quantity) {
            return quantity * 500; // Returns weight in grams (0.5kg per item)
        }
        
        formatWeight(weightInGrams) {
            return (weightInGrams / 1000).toFixed(2) + ' kg';
        }
        
        // ====== SEPARATED CALCULATION METHODS ======
        
        // 1. Calculate Items Section
        calculateItems() {
            let subtotal = 0;
            let totalQuantity = 0;
            let totalWeight = 0;
            
            $('#itemsTableBody tr').each((index, row) => {
                const $row = $(row);
                const qty = parseFloat($row.find('.quantity').val()) || 0;
                const price = parseFloat($row.find('.unit-price').val()) || 0;
                const total = qty * price;
                const weight = this.calculateWeight(qty);
                
                // Update row values
                $row.find('.total-price').val('৳' + total.toFixed(0));
                $row.find('.item-total').val(total);
                $row.find('.weight-display').val(this.formatWeight(weight));
                $row.find('.item-weight').val(weight);
                
                // Add to totals
                subtotal += total;
                totalQuantity += qty;
                totalWeight += weight;
            });
            
            return {
                subtotal: subtotal,
                totalQuantity: totalQuantity,
                totalWeight: totalWeight,
                totalWeightFormatted: this.formatWeight(totalWeight)
            };
        }
        
        // 2. Calculate Return Items Section
        calculateReturnItems() {
            let returnSubtotal = 0;
            let returnQuantity = 0;
            
            $('#returnItemsTableBody tr').each((index, row) => {
                const $row = $(row);
                const qty = parseFloat($row.find('.return-quantity').val()) || 0;
                const price = parseFloat($row.find('.return-unit-price').val()) || 0;
                const total = qty * price;
                const weight = this.calculateWeight(qty);
                
                // Update row values
                $row.find('.return-total-price').val('৳' + total.toFixed(0));
                $row.find('.return-item-total').val(total);
                $row.find('.return-weight-display').val(this.formatWeight(weight));
                $row.find('.return-item-weight').val(weight);
                
                // Add to return totals
                returnSubtotal += total;
                returnQuantity += qty;
            });
            
            return {
                returnSubtotal: returnSubtotal,
                returnQuantity: returnQuantity
            };
        }
        
        // 3. Calculate Final Totals
        calculateFinalTotals(itemsData, returnData) {
            const finalSubtotal = itemsData.subtotal - returnData.returnSubtotal;
            const delivery = parseFloat($('#delivery_charge').val()) || 0;
            const grandTotal = finalSubtotal + delivery;
            
            return {
                finalSubtotal: finalSubtotal,
                delivery: delivery,
                grandTotal: grandTotal
            };
        }
        
        // 4. Update All Displays
        updateAllCalculations() {
            // Step 1: Calculate items
            const itemsData = this.calculateItems();
            
            // Step 2: Calculate return items
            const returnData = this.calculateReturnItems();
            
            // Step 3: Calculate final totals
            const finalData = this.calculateFinalTotals(itemsData, returnData);
            
            // Step 4: Update displays
            this.updateItemsDisplay(itemsData);
            this.updateReturnDisplay(returnData);
            this.updateSummaryDisplay(itemsData, returnData, finalData);
        }
        
        // 5. Update Items Display
        updateItemsDisplay(itemsData) {
            $('#total-quantity').text(itemsData.totalQuantity);
            $('#total-weight').text(itemsData.totalWeightFormatted);
            $('#subtotal').text(itemsData.subtotal.toFixed(0));
            
            // Update summary section
            $('#summary-total-items').text(itemsData.totalQuantity);
            $('#summary-total-weight').text(itemsData.totalWeightFormatted);
            $('#summary-subtotal').text('৳' + itemsData.subtotal.toFixed(0));
        }
        
        // 6. Update Return Display
        updateReturnDisplay(returnData) {
            $('#return-total-quantity').text(returnData.returnQuantity);
            $('#return-subtotal').text(returnData.returnSubtotal.toFixed(0));
            
            // Update summary section
            $('#summary-return-items').text(returnData.returnQuantity);
            $('#summary-return-amount').text('-৳' + returnData.returnSubtotal.toFixed(0));
        }
        
        // 7. Update Summary Display
        updateSummaryDisplay(itemsData, returnData, finalData) {
            $('#summary-net-subtotal').text('৳' + finalData.finalSubtotal.toFixed(0));
            $('#summary-delivery').text('৳' + finalData.delivery.toFixed(0));
            $('#summary-grand-total').text('৳' + finalData.grandTotal.toFixed(0));
            
            // Update items table footer
            $('#delivery-display').text(finalData.delivery.toFixed(0));
            $('#grand-total').text(finalData.grandTotal.toFixed(0));
        }
        
        // ====== END OF SEPARATED CALCULATIONS ======
        
        addNewItem() {
            const newIndex = 'new_' + this.newItemCounter;
            const row = `
                <tr data-item-id="${newIndex}" data-is-existing="false">
                    <td class="serial"></td>
                    <td>
                        <input type="hidden" name="items[${newIndex}][id]" value="${newIndex}">
                        <input type="text" class="form-control" name="items[${newIndex}][item_name]" value="Three Piece" required>
                    </td>
                    <td>
                        <input type="number" class="form-control quantity" name="items[${newIndex}][quantity]" value="1" required min="1">
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control unit-price" name="items[${newIndex}][unit_price]" value="0" required min="0">
                    </td>
                    <td>
                        <input type="text" class="form-control weight-display" value="0.50 kg" readonly>
                        <input type="hidden" class="item-weight" name="items[${newIndex}][weight]" value="500">
                    </td>
                    <td>
                        <input type="text" class="form-control total-price" value="৳0" readonly>
                        <input type="hidden" class="item-total" value="0">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm delete-item">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            
            $('#itemsTableBody').append(row);
            this.newItemCounter++;
            this.updateSerialNumbers('#itemsTableBody');
            this.updateAllCalculations();
            
            // Focus on new item name
            $('#itemsTableBody tr:last').find('input[name*="item_name"]').focus();
        }
        
        deleteItem(e) {
            if ($('#itemsTableBody tr').length <= 1) {
                this.showAlert('error', 'Invoice must have at least one item.');
                return;
            }
            
            const $row = $(e.currentTarget).closest('tr');
            const isExisting = $row.data('is-existing');
            const itemId = $row.data('item-id');
            
            if (isExisting) {
                // Mark as deleted for server-side processing
                $row.append(`<input type="hidden" name="deleted_items[]" value="${itemId}">`);
            }
            
            $row.remove();
            this.updateSerialNumbers('#itemsTableBody');
            this.updateAllCalculations();
        }
        
        addNewReturnItem() {
            const newIndex = 'new_return_' + this.newReturnItemCounter;
            const row = `
                <tr data-return-item-id="${newIndex}" data-is-existing="false">
                    <td class="serial"></td>
                    <td>
                        <input type="hidden" name="return_items[${newIndex}][id]" value="${newIndex}">
                        <input type="text" class="form-control return-item-name" 
                               name="return_items[${newIndex}][item_name]" 
                               placeholder="Item name" required>
                    </td>
                    <td>
                        <input type="number" class="form-control return-quantity text-center" 
                               name="return_items[${newIndex}][quantity]" 
                               value="1" min="1" required>
                    </td>
                    <td>
                        <input type="number" step="0.01" class="form-control return-unit-price text-right" 
                               name="return_items[${newIndex}][unit_price]" 
                               value="0" min="0" required>
                    </td>
                    <td>
                        <input type="text" class="form-control return-weight-display" 
                               value="0.50 kg" readonly>
                        <input type="hidden" class="return-item-weight" 
                               name="return_items[${newIndex}][weight]" 
                               value="500">
                    </td>
                    <td>
                        <input type="text" class="form-control return-total-price" 
                               value="৳0" readonly>
                        <input type="hidden" class="return-item-total" 
                               value="0">
                    </td>
                    <td>
                        <select class="form-control return-reason" 
                                name="return_items[${newIndex}][return_reason]">
                            <option value="">Select Reason</option>
                            <option value="damaged">Damaged</option>
                            <option value="wrong_item">Wrong Item</option>
                            <option value="customer_request">Customer Request</option>
                            <option value="quality_issue">Quality Issue</option>
                            <option value="other">Other</option>
                        </select>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm delete-return-item">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            
            $('#returnItemsTableBody').append(row);
            this.newReturnItemCounter++;
            this.updateSerialNumbers('#returnItemsTableBody');
            this.updateAllCalculations();
        }
        
        deleteReturnItem(e) {
            if ($('#returnItemsTableBody tr').length <= 1) {
                // If only one row, clear it instead of deleting
                const $row = $(e.currentTarget).closest('tr');
                $row.find('input').val('');
                $row.find('select').val('');
                this.updateAllCalculations();
                return;
            }
            
            const $row = $(e.currentTarget).closest('tr');
            const isExisting = $row.data('is-existing');
            const itemId = $row.data('return-item-id');
            
            if (isExisting) {
                // Mark as deleted for server-side processing
                $row.append(`<input type="hidden" name="deleted_return_items[]" value="${itemId}">`);
            }
            
            $row.remove();
            this.updateSerialNumbers('#returnItemsTableBody');
            this.updateAllCalculations();
        }
        
        updateSerialNumbers(tableBody) {
            $(tableBody + ' tr .serial').each((i, el) => {
                $(el).text(i + 1);
            });
        }
        
        updateStatusStyle() {
            const status = $('#status').val();
            $('#status').removeClass('status-confirmed status-pending status-cancelled');
            
            switch(status) {
                case 'confirmed':
                    $('#status').addClass('status-confirmed');
                    break;
                case 'pending':
                    $('#status').addClass('status-pending');
                    break;
                case 'cancelled':
                    $('#status').addClass('status-cancelled');
                    break;
            }
        }
        
        formatPhoneNumber() {
            let phone = $('#customer_phone').val();
            phone = phone.replace(/\D/g, '');
            if (phone.startsWith('88')) {
                phone = '0' + phone.substring(2);
            } else if (phone.startsWith('1') && phone.length === 10) {
                phone = '0' + phone;
            }
            $('#customer_phone').val(phone);
        }
        
        validateForm(e) {
            let isValid = true;
            let errorMessages = [];
            
            // Validate phone number
            const phone = $('#customer_phone').val();
            const phoneRegex = /^(?:\+88|01)?(?:\d{11}|\d{13})$/;
            if (phone && !phoneRegex.test(phone.replace(/\D/g, ''))) {
                isValid = false;
                errorMessages.push('Please enter a valid phone number.');
                $('#customer_phone').addClass('is-invalid').focus();
            } else {
                $('#customer_phone').removeClass('is-invalid');
            }
            
            // Validate items
            $('.unit-price').each((index, input) => {
                const price = parseFloat($(input).val());
                if (price <= 0) {
                    isValid = false;
                    if (!errorMessages.includes('Unit price must be greater than zero.')) {
                        errorMessages.push('Unit price must be greater than zero.');
                    }
                    $(input).addClass('is-invalid');
                } else {
                    $(input).removeClass('is-invalid');
                }
            });
            
            $('.quantity').each((index, input) => {
                const qty = parseFloat($(input).val());
                if (qty < 1) {
                    isValid = false;
                    if (!errorMessages.includes('Quantity must be at least 1.')) {
                        errorMessages.push('Quantity must be at least 1.');
                    }
                    $(input).addClass('is-invalid');
                } else {
                    $(input).removeClass('is-invalid');
                }
            });
            
            // Validate delivery charge
            const delivery = parseFloat($('#delivery_charge').val());
            if (isNaN(delivery) || delivery < 0) {
                isValid = false;
                errorMessages.push('Delivery charge must be a valid positive number.');
                $('#delivery_charge').addClass('is-invalid');
            } else {
                $('#delivery_charge').removeClass('is-invalid');
            }
            
            if (!isValid) {
                e.preventDefault();
                this.showAlert('error', errorMessages.join('<br>'));
                return false;
            }
            
            // Show loading state
            const $submitBtn = $(e.target).find('button[type="submit"]');
            $submitBtn.prop('disabled', true)
                      .html('<i class="fa fa-spinner fa-spin"></i> Updating...');
            
            return true;
        }
        
        showAlert(type, message) {
            $('.alert-dismissible').alert('close');
            
            const alertClass = type === 'error' ? 'alert-danger' : 'alert-warning';
            const icon = type === 'error' ? 'fa-exclamation-circle' : 'fa-exclamation-triangle';
            
            const alert = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="fa ${icon} mr-2"></i>
                    ${message}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>`;
            
            $('.card-body').prepend(alert);
            
            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);
        }
    }
    
    // Initialize the invoice editor
    window.InvoiceEditor = new InvoiceEditor();
})();