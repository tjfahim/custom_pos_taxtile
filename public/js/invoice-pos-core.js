// Invoice POS Core Module - Main initialization and coordination
const InvoicePOS = {
    currentInvoiceId: null,
    
    // Module references (will be set after modules load)
    Items: null,
    Calculations: null,
    Payments: null,
    FormHandler: null,
    
    init: function() {
        // Initialize module references
        this.Items = InvoiceItems;
        this.Calculations = InvoiceCalculations;
        this.Payments = InvoicePayments;
        this.FormHandler = InvoiceFormHandler;
        
        // Add initial item row
        this.Items.addItemRow();
        this.Calculations.calculateTotals();
        this.bindEvents();
        
        console.log('Invoice POS initialized with modules:', {
            Items: !!this.Items,
            Calculations: !!this.Calculations,
            Payments: !!this.Payments,
            FormHandler: !!this.FormHandler
        });
    },
    
    bindEvents: function() {
        // Customer search
        if ($('#customerSearch').length) {
            $('#customerSearch').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('#customerTableBody .customer-row').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });
        }
        
        // Select customer from modal
        $('.select-customer').click(function() {
            InvoicePOS.FormHandler.selectCustomer($(this));
        });
        
        // Auto-calculate amount to collect when total changes
        $('#total').on('DOMSubtreeModified', function() {
            InvoicePOS.Calculations.updateAmountToCollect();
        });
        
        // Event Listeners
        $('#deliveryCharge').on('input', () => this.Calculations.calculateTotals());
        $('#paidAmount').on('input', () => this.Calculations.updateDueAmount());
        
        // Listen for payment method change
        $('#paymentMethod').on('change', function() {
            // If payment method is selected, ensure advance amount is not 0
            if ($(this).val() !== '' && $('#paidAmount').val() == 0) {
                $('#paidAmount').val('1').trigger('input');
            }
        });
    },
    
    // Convenience methods
    saveAndPrint: function() {
        if (this.FormHandler) {
            this.FormHandler.saveAndPrint();
            
        }
    },
    
    resetForm: function() {
        if (this.FormHandler) {
            this.FormHandler.resetForm();
        }
    }
};
function printInvoice() {
    if (typeof InvoicePOS !== 'undefined' && InvoicePOS.FormHandler) {
        InvoicePOS.FormHandler.printInvoice();
    }
}

function createNewInvoice() {
    if (typeof InvoicePOS !== 'undefined' && InvoicePOS.FormHandler) {
        // Close modal first, then reset
        $('#printModal').modal('hide');
        
        // Small delay to ensure modal is fully hidden
        setTimeout(() => {
            InvoicePOS.FormHandler.resetFormSilently();
        }, 300);
    }
}

function saveAndPrint() {
    if (typeof InvoicePOS !== 'undefined' && InvoicePOS.FormHandler) {
        InvoicePOS.FormHandler.saveAndPrint();
    } else {
        console.error('InvoicePOS modules not loaded');
        alert('System not ready. Please refresh the page.');
    }
}

function resetForm() {
    if (typeof InvoicePOS !== 'undefined' && InvoicePOS.FormHandler) {
        InvoicePOS.FormHandler.resetForm();
    }
}

// Initialize when document is ready and all modules are loaded
$(document).ready(function() {
    // Check if all modules are loaded
    if (typeof InvoiceItems !== 'undefined' && 
        typeof InvoiceCalculations !== 'undefined' && 
        typeof InvoicePayments !== 'undefined' && 
        typeof InvoiceFormHandler !== 'undefined') {
        
        InvoicePOS.init();
    } else {
        console.error('Some invoice POS modules are not loaded properly');
        // Try to initialize anyway after a short delay
        setTimeout(function() {
            if (typeof InvoiceItems !== 'undefined' && 
                typeof InvoiceCalculations !== 'undefined') {
                InvoicePOS.init();
            } else {
                alert('System modules not loaded properly. Please refresh the page.');
            }
        }, 1000);
    }
});