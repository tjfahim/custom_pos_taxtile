// customer-auto.js
class CustomerAutoManager {
    constructor() {
        this.currentCustomer = null;
        this.isManualUpdate = false;
        this.isManualMerchantEntry = false;
        this.init();
    }

    init() {
        // Generate 4-digit merchant order ID on page load
        this.generate4DigitMerchantId();
        
        // Listen for phone input changes
        $('#recipientPhone').on('input', this.debounce(this.handlePhoneInput.bind(this), 800));
        
        // Listen for form field changes to update customer
        $('#recipientName, #merchant_order_id, #recipientAddress, #recipientPhone2, #deliveryArea').on('input change', () => {
            this.flagCustomerUpdate();
        });
        
        // Refresh 4-digit ID button
        $('#refreshMerchantId').on('click', () => {
            this.refresh4DigitMerchantId();
        });
        
        // Clear flags when customer is selected from modal
        $(document).on('click', '.select-customer', () => {
            this.isManualUpdate = false;
            this.currentCustomer = null;
        });
        
        // Reset when modal is opened
        $('#customerModal').on('show.bs.modal', () => {
            this.currentCustomer = null;
            this.isManualUpdate = false;
        });
        
        // Detect when user starts typing in merchant ID field
        $('#merchant_order_id').on('input', (e) => {
            const value = $(e.target).val().trim();
            // Mark as manual entry if user types anything
            if (value && !this.isManualMerchantEntry) {
                this.isManualMerchantEntry = true;
            }
        });
        
        // When merchant ID field loses focus and only has 4 digits, user can add text
        $('#merchant_order_id').on('blur', (e) => {
            const value = $(e.target).val().trim();
            // If field is empty or has only whitespace, generate new 4-digit
            if (!value) {
                this.generate4DigitMerchantId();
            }
        });
        
        // Also generate when form is reset (if you have form reset function)
        this.setupFormResetListener();
    }
    
    generate4DigitMerchantId() {
        // Generate random 4-digit number between 1000 and 9999
        const fourDigitId = Math.floor(1000 + Math.random() * 9000).toString();
        
        // Set the value
        $('#merchant_order_id').val(fourDigitId);
        
        // Reset manual entry flag since we're auto-generating
        this.isManualMerchantEntry = false;
        
        // Optional: Show brief notification
        this.showBriefMessage('Generated 4-digit ID: ' + fourDigitId, 'info');
    }
    
    refresh4DigitMerchantId() {
        const currentValue = $('#merchant_order_id').val().trim();
        
        // If user has manually added text after the digits, preserve it
        if (this.isManualMerchantEntry && currentValue && currentValue.length > 4) {
            // Extract existing text after digits
            const match = currentValue.match(/^(\d{4})(.*)$/);
            if (match) {
                // Generate new 4 digits and keep the user's added text
                const newFourDigits = Math.floor(1000 + Math.random() * 9000).toString();
                const userText = match[2].trim();
                const newValue = userText ? newFourDigits + ' ' + userText : newFourDigits;
                
                $('#merchant_order_id').val(newValue);
                this.showBriefMessage('Refreshed 4-digit ID: ' + newFourDigits, 'success');
            } else {
                // If pattern doesn't match, just generate new 4-digit
                this.generate4DigitMerchantId();
            }
        } else {
            // Just generate fresh 4-digit ID
            this.generate4DigitMerchantId();
        }
    }
    
    async handlePhoneInput() {
        const phone = $('#recipientPhone').val().trim().replace(/\D/g, '');
        
        // Validate phone format
        if (!this.validatePhoneFormat(phone)) {
            if (phone.length > 0) {
                this.showMessage('Please enter a valid Bangladeshi mobile number (01XXXXXXXXX)', 'warning');
            }
            return;
        }

        try {
            // Show loading
            this.showMessage('Checking customer database...', 'info');
            
            // Check if customer exists with this phone
            const customer = await this.checkLocalCustomer(phone);
            
            if (customer) {
                // Auto-fill customer information
                this.currentCustomer = customer;
                this.fillCustomerInfo(customer);
                
                if (!this.isManualUpdate) {
                    this.showMessage('Customer found! Information auto-filled. Changes will update customer record.', 'success');
                }
                
                // Update customer ID field
                $('#customerId').val(customer.id);
                $('#selectedCustomer').text(customer.name + ' (Existing Customer)');
                
            } else {
                this.currentCustomer = null;
                $('#customerId').val('');
                $('#selectedCustomer').text('New Customer - Will be created on save');
                
                // For new customers, check if we should generate merchant ID
                const currentMerchantId = $('#merchant_order_id').val().trim();
                if (!currentMerchantId || (!this.isManualMerchantEntry && currentMerchantId.length === 4)) {
                    this.generate4DigitMerchantId();
                }
                
                this.showMessage('New customer - will be created on save', 'info');
            }
        } catch (error) {
            console.error('Customer check error:', error);
            this.showMessage('Error checking customer. Please try again.', 'danger');
        }
    }

    validatePhoneFormat(phone) {
        return /^01[3-9]\d{8}$/.test(phone);
    }

    async checkLocalCustomer(phone) {
        try {
            const response = await fetch(`/check-customer-by-phone/${phone}`);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success && data.customer) {
                return data.customer;
            }
            
            return null;
            
        } catch (error) {
            console.error('Error fetching customer:', error);
            return this.findCustomerInModal(phone);
        }
    }

    findCustomerInModal(phone) {
        const customerRow = $(`.customer-row[data-customer-phone="${phone}"]`);
        
        if (customerRow.length) {
            return {
                id: customerRow.data('customer-id'),
                name: customerRow.data('customer-name'),
                phone_number_1: customerRow.data('customer-phone'),
                phone_number_2: customerRow.data('customer-phone2'),
                merchant_order_id: customerRow.data('customer-merchant-id'),
                full_address: customerRow.data('customer-address'),
                delivery_area: customerRow.data('customer-area')
            };
        }
        
        return null;
    }

    fillCustomerInfo(customer) {
        // For merchant ID: only auto-fill if not manually entered
        const currentMerchantId = $('#merchant_order_id').val().trim();
        const shouldFillMerchantId = !this.isManualMerchantEntry || !currentMerchantId;
        
        if (shouldFillMerchantId && customer.merchant_order_id) {
            // Check if customer has a 4-digit ID or starts with 4 digits
            const customerId = customer.merchant_order_id.toString();
            
            if (/^\d{4}/.test(customerId)) {
                // Customer has ID starting with 4 digits
                $('#merchant_order_id').val(customerId);
                this.isManualMerchantEntry = false; // Reset since we're auto-filling
                this.showBriefMessage('Using customer\'s ID: ' + customerId, 'info');
            } else {
                // Customer has non-standard ID, generate new 4-digit
                this.generate4DigitMerchantId();
            }
        } else if (shouldFillMerchantId && !currentMerchantId) {
            // Generate new 4-digit ID
            this.generate4DigitMerchantId();
        }
        
        // Only auto-fill other fields if they're empty and not manually updated
        if (!this.isManualUpdate) {
            if (!$('#recipientName').val().trim()) {
                $('#recipientName').val(customer.name);
            }
            
            if (!$('#recipientAddress').val().trim()) {
                $('#recipientAddress').val(customer.full_address);
            }
            
            if (!$('#recipientPhone2').val().trim()) {
                $('#recipientPhone2').val(customer.phone_number_2 || '');
            }
            
            if (!$('#deliveryArea').val().trim()) {
                $('#deliveryArea').val(customer.delivery_area || '');
            }
        }
        
        // Update customer modal selection
        this.updateCustomerModal(customer);
    }

    updateCustomerModal(customer) {
        if (!customer || !customer.id) return;
        
        $('.customer-row').removeClass('bg-success text-white');
        $(`.customer-row[data-customer-id="${customer.id}"]`).addClass('bg-success text-white');
    }

    flagCustomerUpdate() {
        if (this.currentCustomer && !this.isManualUpdate) {
            this.isManualUpdate = true;
            $('#selectedCustomer').text(this.currentCustomer.name + ' (Will be updated on save)');
            $('#selectedCustomer').addClass('text-warning');
            
            this.showMessage('Customer information modified. Changes will update the existing customer record.', 'warning');
        } else if (!$('#customerId').val() && $('#recipientPhone').val().trim()) {
            $('#selectedCustomer').text('New Customer - Will be created on save');
            $('#selectedCustomer').removeClass('text-warning');
        }
    }
    
    setupFormResetListener() {
        // Listen for form reset events
        $(document).on('formReset', () => {
            this.generate4DigitMerchantId();
            this.isManualMerchantEntry = false;
            this.isManualUpdate = false;
            this.currentCustomer = null;
        });
        
        // If you have a reset form button, trigger the event
        $('[onclick*="resetForm"]').on('click', () => {
            setTimeout(() => {
                $(document).trigger('formReset');
            }, 100);
        });
    }

    showMessage(message, type = 'info') {
        $('#customerAutoMessage').remove();
        
        const alertClasses = {
            'success': 'alert-success',
            'warning': 'alert-warning',
            'danger': 'alert-danger',
            'info': 'alert-info'
        };
        
        const alertClass = alertClasses[type] || 'alert-info';
        
        const messageDiv = $(`
            <div id="customerAutoMessage" class="alert ${alertClass} alert-dismissible fade show mt-2">
                <i class="fa fa-info-circle"></i> ${message}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        `);
        
        $('#customerSection').append(messageDiv);
        
        setTimeout(() => {
            $('#customerAutoMessage').alert('close');
        }, 5000);
    }
    
    showBriefMessage(message, type = 'info') {
        // Create a temporary notification
        const notification = $(`
            <div class="alert ${type === 'info' ? 'alert-info' : 'alert-success'} 
                 alert-dismissible fade show merchant-id-notification" 
                 style="position: fixed; top: 70px; right: 20px; z-index: 9999; max-width: 300px;">
                <i class="fa fa-info-circle"></i> ${message}
            </div>
        `);
        
        // Remove any existing notifications
        $('.merchant-id-notification').remove();
        
        $('body').append(notification);
        
        // Auto-remove after 1.5 seconds
        setTimeout(() => {
            notification.alert('close');
        }, 1500);
    }

    debounce(func, wait) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func(...args), wait);
        };
    }
}

// Initialize on document ready
$(document).ready(() => {
    window.customerAutoManager = new CustomerAutoManager();
});

// Also add this function to your form handler to trigger merchant ID generation
function resetFormWithMerchantId() {
    // Your existing reset logic...
    
    // Generate new merchant ID
    if (window.customerAutoManager) {
        window.customerAutoManager.generate4DigitMerchantId();
    }
}