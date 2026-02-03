// customer-auto.js
class CustomerAutoManager {
    constructor() {
        this.currentCustomer = null;
        this.isManualUpdate = false;
        this.init();
    }

    init() {
        // Listen for phone input changes
        $('#recipientPhone').on('input', this.debounce(this.handlePhoneInput.bind(this), 800));
        
        // Listen for form field changes to update customer
        $('#recipientName, #recipientAddress, #recipientPhone2, #deliveryArea').on('input change', () => {
            this.flagCustomerUpdate();
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
            
            // Check if customer exists with this phone (local check)
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
            // Use existing route or create a simple one
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
            
            // Fallback: Check in modal data if available
            return this.findCustomerInModal(phone);
        }
    }

    findCustomerInModal(phone) {
        // Search in the customer modal table
        const customerRow = $(`.customer-row[data-customer-phone="${phone}"]`);
        
        if (customerRow.length) {
            return {
                id: customerRow.data('customer-id'),
                name: customerRow.data('customer-name'),
                phone_number_1: customerRow.data('customer-phone'),
                phone_number_2: customerRow.data('customer-phone2'),
                full_address: customerRow.data('customer-address'),
                delivery_area: customerRow.data('customer-area')
            };
        }
        
        return null;
    }

    fillCustomerInfo(customer) {
        // Only auto-fill if fields are empty
        if (!this.isManualUpdate) {
            // Check if fields are empty before auto-filling
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
        
        // Always update customer modal selection
        this.updateCustomerModal(customer);
    }

    updateCustomerModal(customer) {
        if (!customer || !customer.id) return;
        
        // Remove previous highlights
        $('.customer-row').removeClass('bg-success text-white');
        
        // Highlight current customer in modal
        $(`.customer-row[data-customer-id="${customer.id}"]`).addClass('bg-success text-white');
    }

    flagCustomerUpdate() {
        if (this.currentCustomer && !this.isManualUpdate) {
            this.isManualUpdate = true;
            $('#selectedCustomer').text(this.currentCustomer.name + ' (Will be updated on save)');
            $('#selectedCustomer').addClass('text-warning');
            
            this.showMessage('Customer information modified. Changes will update the existing customer record.', 'warning');
        } else if (!$('#customerId').val() && $('#recipientPhone').val().trim()) {
            // If no customer selected but phone entered, show message
            $('#selectedCustomer').text('New Customer - Will be created on save');
            $('#selectedCustomer').removeClass('text-warning');
        }
    }

    showMessage(message, type = 'info') {
        // Remove any existing message
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
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            $('#customerAutoMessage').alert('close');
        }, 5000);
    }

    debounce(func, wait) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func(...args), wait);
        };
    }
}

// Initialize
$(document).ready(() => new CustomerAutoManager());