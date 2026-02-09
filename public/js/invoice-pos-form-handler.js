// Invoice Form Handler Module - Handles form submission and validation
const InvoiceFormHandler = {
    
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
        $('#merchant_order_id').val(merchant_order_id);
        $('#recipientPhone').val(customerPhone);
        $('#recipientPhone2').val(customerPhone2);
        $('#recipientAddress').val(customerAddress);
        
        // Set delivery area from customer data
        if (customerArea) {
            $('#deliveryArea').val(customerArea);
            
            if (typeof InvoiceDelivery !== 'undefined') {
                InvoiceDelivery.setFromCustomer(customerArea);
            }
        }
        
        // Close modal after selection
        $('#customerModal').modal('hide');
        
        // Show success message
        this.showCustomerMessage('Customer selected successfully!', 'success');
    },
    
    showCustomerMessage: function(message, type = 'info') {
        // Remove any existing message
        $('#customerAlert').remove();
        
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'warning' ? 'alert-warning' : 'alert-info';
        
        const messageDiv = $(`
            <div id="customerAlert" class="alert ${alertClass} alert-dismissible fade show mt-2">
                ${message}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        `);
        
        $('#customerSection').append(messageDiv);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            $('#customerAlert').alert('close');
        }, 5000);
    },
    
    // NEW: Unified showMessage method
    showMessage: function(message, type = 'info', duration = 5000) {
        // Remove existing messages
        $('.form-message').remove();
        
        const alertClass = type === 'success' ? 'alert-success' : 
                          type === 'warning' ? 'alert-warning' : 
                          type === 'error' ? 'alert-danger' : 'alert-info';
        
        const icon = type === 'success' ? 'check-circle' : 
                    type === 'warning' ? 'exclamation-triangle' : 
                    type === 'error' ? 'exclamation-circle' : 'info-circle';
        
        const messageDiv = $(`
            <div class="alert ${alertClass} alert-dismissible fade show mt-2 form-message form-reset-success">
                <i class="fa fa-${icon}"></i> ${message}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        `);
        
        // Insert at the top of card body for better visibility
        $('.card-body').first().prepend(messageDiv);
        
        // Auto-remove after specified duration
        setTimeout(() => {
            $('.form-message').alert('close');
        }, duration);
    },
    
    saveAndPrint: function() {
        // Validate form
        if (!this.validateForm()) {
            return;
        }
        
        // Show loading
        this.showLoading();
        
        // Store form data BEFORE resetting
        const formData = new FormData(document.getElementById('posForm'));
        
        // Add delivery area selection data
        if (typeof InvoiceDelivery !== 'undefined') {
            if (InvoiceDelivery.selectedCity) {
                formData.append('delivery_city_id', InvoiceDelivery.selectedCity.id);
                formData.append('delivery_city_name', InvoiceDelivery.selectedCity.name);
            }
            
            if (InvoiceDelivery.selectedZone) {
                formData.append('delivery_zone_id', InvoiceDelivery.selectedZone.id);
                formData.append('delivery_zone_name', InvoiceDelivery.selectedZone.name);
            }
            
            if (InvoiceDelivery.selectedArea) {
                formData.append('delivery_area_id', InvoiceDelivery.selectedArea.id);
                formData.append('delivery_area_name', InvoiceDelivery.selectedArea.name);
            }
        }
        
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
            success: (response) => {
                InvoiceFormHandler.hideLoading();
                
                if (response.success) {
                    // Store invoice ID for reference
                    InvoicePOS.currentInvoiceId = response.invoice_id;
                    
                    // RESET FORM FIRST - BEFORE showing print
                    this.resetFormSilently();
                    
                    // Build the print URL
                    const baseUrl = response.print_url || `/invoices/${response.invoice_id}/print`;
                    const printUrl = baseUrl + '?autoprint=1&timestamp=' + Date.now();
                    
                    // Load invoice in iframe
                    const iframe = $('#invoiceIframe');
                    
                    // Remove any previous load handlers
                    iframe.off('load');
                    
                    // Add load handler
                    iframe.on('load', function() {
                        console.log('Iframe loaded successfully');
                        $('#printModal').modal({
                            backdrop: 'static',
                            keyboard: false
                        }).modal('show');
                        
                        // Auto-print after modal shows
                        setTimeout(() => {
                            if (iframe[0].contentWindow) {
                                iframe[0].contentWindow.print();
                            }
                        }, 1000);
                    });
                    
                    // Set iframe source
                    iframe.attr('src', printUrl);
                    
                    // Handle cached content
                    if (iframe[0].contentDocument && iframe[0].contentDocument.readyState === 'complete') {
                        $('#printModal').modal('show');
                        setTimeout(() => {
                            if (iframe[0].contentWindow) {
                                iframe[0].contentWindow.print();
                            }
                        }, 1000);
                    }
                    
                    // Show success message
                    this.showMessage('Invoice #' + response.invoice_id + ' saved successfully! Ready for next invoice.', 'success');
                    
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: (xhr, status, error) => {
                InvoiceFormHandler.hideLoading();
                
                let errorMessage = 'An error occurred. Please try again.';
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    } else if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                }
                
                InvoiceFormHandler.showErrorModal(errorMessage);
            }
        });
    },
    
    // Reset form without confirmation
    resetFormSilently: function() {
        // Reset the form
        $('#posForm')[0].reset();
        
        // Clear items table
        $('#itemsBody').empty();
        
        // Reset item counter and add one fresh row
        if (typeof InvoiceItems !== 'undefined') {
            InvoiceItems.itemCount = 0;
            InvoiceItems.addItemRow();
        }
        
        // Reset delivery area selections
        if (typeof InvoiceDelivery !== 'undefined') {
            InvoiceDelivery.selectedCity = null;
            InvoiceDelivery.selectedZone = null;
            InvoiceDelivery.selectedArea = null;
            
            // Reset select2 dropdowns properly
            if ($('#deliveryCitySelect').hasClass('select2-hidden-accessible')) {
                $('#deliveryCitySelect').val('').trigger('change');
            } else {
                $('#deliveryCitySelect').val('');
            }
            
            if ($('#deliveryZoneSelect').hasClass('select2-hidden-accessible')) {
                $('#deliveryZoneSelect').empty().append('<option value="">-- Select Zone --</option>').prop('disabled', true).trigger('change');
            } else {
                $('#deliveryZoneSelect').empty().append('<option value="">-- Select Zone --</option>').prop('disabled', true);
            }
            
            if ($('#deliveryAreaSelect').hasClass('select2-hidden-accessible')) {
                $('#deliveryAreaSelect').empty().append('<option value="">-- Select Area --</option>').prop('disabled', true).trigger('change');
            } else {
                $('#deliveryAreaSelect').empty().append('<option value="">-- Select Area --</option>').prop('disabled', true);
            }
        }
        
        // Reset specific fields to default values
        $('#deliveryCharge').val(150);
        $('#paidAmount').val(0);
        $('#paymentMethod').val('');
        $('#selectedCustomer').text('No customer selected').removeClass('text-success text-warning');
        $('#customerId').val('');
        $('#deliveryArea').val('');
        
        // Clear phone fields (important!)
        $('#recipientPhone').val('');
        $('#recipientPhone2').val('');
        
        // Reset special instructions to default
        $('textarea[name="special_instructions"]').val('Return korle delivery charge 150 tk niben ( আনুষাঙ্গিক কোনো ইসু থাকলে প্যানেলে মেসেজ দিবেন। নাম্বারে যোগাযোগ করার সময় - সকাল ১১.৩০ থেকে রাত ৯ টার মধ্যে)');
        
        // Clear notes
        $('textarea[name="notes"]').val('');
        
        // Hide payment details
        $('#bkashDetails').hide();
        $('#bankDetails').hide();
        
        // Reset store location to first option
        $('select[name="store_location"]').val('Faisal Textile FB');
        
        // Reset delivery type to default
        $('select[name="delivery_type"]').val('Parcel');
        
        // Clear any messages
        $('.form-message').remove();
        
        // Clear customer alerts
        $('#customerAlert').remove();
        
        // Recalculate totals
        if (typeof InvoiceCalculations !== 'undefined') {
            setTimeout(() => {
                InvoiceCalculations.calculateTotals();
            }, 100);
        }
        
        // Focus on first input field
        setTimeout(() => {
            $('#recipientName').focus();
        }, 500);
        
        return true;
    },
    
    // Reset form with confirmation
    resetForm: function() {
        if (confirm('Clear entire form? This will remove all items and reset all fields.')) {
            this.resetFormSilently();
        }
    },
    
    // Create new invoice (for New Invoice button)
    createNewInvoice: function() {
        // Close modal first
        $('#printModal').modal('hide');
        
        // Show success message
        this.showMessage('Ready for new invoice. Form has been reset.', 'success');
    },
    
    validateForm: function() {
        // Check recipient name
        if (!$('#recipientName').val().trim()) {
            alert('Please enter recipient name');
            $('#recipientName').focus();
            return false;
        }
        
        // Check recipient phone
        const phone = $('#recipientPhone').val().trim();
        if (!phone) {
            alert('Please enter recipient phone');
            $('#recipientPhone').focus();
            return false;
        }
        
        // Validate phone format (Bangladeshi mobile)
        const phoneRegex = /^01[3-9]\d{8}$/;
        const cleanPhone = phone.replace(/\D/g, '');
        if (!phoneRegex.test(cleanPhone)) {
            alert('Please enter a valid Bangladeshi mobile number (01XXXXXXXXX)');
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
    
    showLoading: function() {
        if (!$('#loadingOverlay').length) {
            $('body').append(`
                <div class="loading-overlay" id="loadingOverlay">
                    <div class="loading-spinner">
                        <i class="fa fa-spinner fa-spin fa-3x"></i>
                        <div class="mt-3">Saving Invoice...</div>
                    </div>
                </div>
            `);
        }
        $('#loadingOverlay').show();
    },
    
    hideLoading: function() {
        $('#loadingOverlay').hide();
    },
    
    checkCustomerByPhone: async function(phone) {
        try {
            const response = await fetch(`/check-customer-by-phone/${phone}`);
            if (!response.ok) throw new Error('Network response was not ok');
            return await response.json();
        } catch (error) {
            console.error('Error checking customer:', error);
            return { success: false, customer: null };
        }
    },
    
    showErrorModal: function(message) {
        // Remove existing error modal if any
        $('#errorModal').remove();
        
        // Create error modal
        const errorModal = $(`
            <div class="modal fade" id="errorModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="fa fa-exclamation-triangle"></i> Error
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-danger mb-0">
                                ${message}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        // Append to body and show
        $('body').append(errorModal);
        $('#errorModal').modal('show');
        
        // Remove modal from DOM after hiding
        $('#errorModal').on('hidden.bs.modal', function() {
            $(this).remove();
        });
    },
    
    printInvoice: function() {
        try {
            const iframe = $('#invoiceIframe')[0];
            
            if (!iframe || !iframe.contentWindow) {
                throw new Error('Iframe not ready');
            }
            
            // Check if content is loaded
            const isLoaded = iframe.contentDocument && 
                            iframe.contentDocument.readyState === 'complete';
            
            if (isLoaded) {
                // Print directly
                iframe.contentWindow.print();
            } else {
                // Wait for load
                $(iframe).on('load', function() {
                    iframe.contentWindow.print();
                });
                
                // Force reload if stuck
                setTimeout(() => {
                    if (iframe.contentDocument.readyState !== 'complete') {
                        iframe.contentWindow.location.reload();
                    }
                }, 3000);
            }
        } catch (error) {
            console.error('Print error:', error);
            
            // Try opening in new window
            const printUrl = $('#invoiceIframe').attr('src');
            if (printUrl) {
                const newWindow = window.open(printUrl + '?fallback=1', '_blank');
                if (newWindow) {
                    setTimeout(() => {
                        try {
                            newWindow.print();
                        } catch (e) {
                            console.log('Fallback print failed, just showing window');
                        }
                    }, 1000);
                }
            }
        }
    }
};