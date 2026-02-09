// invoices-edit.js - JavaScript for invoice edit page

(function() {
    'use strict';
    
    class InvoiceEditor {
        constructor() {
            this.itemCounter = window.invoiceData ? window.invoiceData.itemCount : 0;
            this.newItemCounter = 0;
            this.init();
        }
        
        init() {
            $(document).ready(() => {
                this.bindEvents();
                this.calculateTotals();
                this.updateStatusStyle();
                this.formatPhoneNumber();
            });
        }
        
        bindEvents() {
            // Add item button
            $('#addItemBtn').on('click', () => this.addNewItem());
            
            // Delete item
            $(document).on('click', '.delete-item', (e) => this.deleteItem(e));
            
            // Calculate on input changes
            $(document).on('input', '.quantity, .unit-price, #delivery_charge', () => this.calculateTotals());
            
            // Auto-select on focus
            $(document).on('focus', 'input', function() {
                $(this).select();
            });
            
            // Status dropdown change
            $('#status').on('change', () => this.updateStatusStyle());
            
            // Phone number formatting
            $('#recipientPhone').on('input', () => this.formatPhoneNumber());
            
            // Form submission
            $('#editInvoiceForm').on('submit', (e) => this.validateForm(e));
        }
        
        calculateWeight(quantity) {
            return quantity * 500; // Returns weight in grams (0.5kg per item)
        }
        
        formatWeight(weightInGrams) {
            return (weightInGrams / 1000).toFixed(2) + ' kg';
        }
        
        calculateTotals() {
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
                $row.find('.total-price').val('৳' + total.toFixed(2));
                $row.find('.item-total').val(total);
                $row.find('.weight-display').val(this.formatWeight(weight));
                $row.find('.item-weight').val(weight);
                
                // Add to totals
                subtotal += total;
                totalQuantity += qty;
                totalWeight += weight;
            });
            
            const delivery = parseFloat($('#delivery_charge').val()) || 0;
            const grandTotal = subtotal + delivery;
            
            // Update display
            $('#total-quantity').text(totalQuantity);
            $('#total-weight').text(this.formatWeight(totalWeight));
            $('#subtotal').text(subtotal.toFixed(2));
            $('#delivery-display').text(delivery.toFixed(2));
            $('#grand-total').text(grandTotal.toFixed(2));
        }
        
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
                        <input type="text" class="form-control total-price" value="৳0.00" readonly>
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
            this.updateSerialNumbers();
            this.calculateTotals();
            
            // Scroll to new item
            $('html, body').animate({
                scrollTop: $('#itemsTableBody tr:last').offset().top - 100
            }, 300);
            
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
            
            // Add deletion animation
            $row.addClass('deleting');
            
            setTimeout(() => {
                if (isExisting) {
                    // Mark as deleted for server-side processing
                    $row.append(`<input type="hidden" name="deleted_items[]" value="${itemId}">`);
                }
                
                $row.remove();
                this.updateSerialNumbers();
                this.calculateTotals();
            }, 300);
        }
        
        updateSerialNumbers() {
            $('#itemsTableBody tr .serial').each((i, el) => {
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
            let phone = $('#recipientPhone').val();
            
            // Remove all non-numeric characters
            phone = phone.replace(/\D/g, '');
            
            // Format for Bangladesh numbers (assuming +88 or 01)
            if (phone.startsWith('88')) {
                phone = '0' + phone.substring(2);
            } else if (phone.startsWith('1') && phone.length === 10) {
                phone = '0' + phone;
            }
            
            $('#recipientPhone').val(phone);
        }
        
        validateForm(e) {
            let isValid = true;
            let errorMessages = [];
            
            // Validate phone number
            const phone = $('#recipientPhone').val();
            const phoneRegex = /^(?:\+88|01)?(?:\d{11}|\d{13})$/;
            if (phone && !phoneRegex.test(phone.replace(/\D/g, ''))) {
                isValid = false;
                errorMessages.push('Please enter a valid phone number.');
                $('#recipientPhone').addClass('is-invalid').focus();
            } else {
                $('#recipientPhone').removeClass('is-invalid');
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
            // Remove existing alerts
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
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);
        }
    }
    
    // Initialize the invoice editor
    window.InvoiceEditor = new InvoiceEditor();
})();