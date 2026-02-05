@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fa fa-edit"></i> Edit Invoice #{{ $invoice->invoice_number }}
                </h5>
                <div>
                    <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to View
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('invoices.update', $invoice->id) }}" method="POST" id="editInvoiceForm">
                    @csrf
                    @method('PUT')
                    
                    <!-- Customer Info (Readonly) -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa fa-user"></i> Customer Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-muted">Customer Name</label>
                                        <input type="text" class="form-control-plaintext" 
                                               value="{{ $invoice->customer->name }}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label class="text-muted">Phone</label>
                                        <input type="text" class="form-control-plaintext" 
                                               value="{{ $invoice->customer->phone_number_1 }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="text-muted">Invoice Date</label>
                                        <input type="text" class="form-control-plaintext" 
                                               value="{{ $invoice->invoice_date->format('M d, Y') }}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label class="text-muted">Payment Status</label>
                                        <div>
                                            <span class="badge badge-{{ 
                                                $invoice->payment_status == 'paid' ? 'success' : 
                                                ($invoice->payment_status == 'partial' ? 'warning' : 'danger') 
                                            }}">
                                                {{ ucfirst($invoice->payment_status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Delivery Charge -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa fa-truck"></i> Delivery Charge</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="delivery_charge" class="font-weight-bold">Delivery Charge (৳)</label>
                                        <input type="number" step="0.01" class="form-control" 
                                               id="delivery_charge" name="delivery_charge" 
                                               value="{{ old('delivery_charge', $invoice->delivery_charge) }}" 
                                               required min="0">
                                        @error('delivery_charge')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="notes" class="font-weight-bold">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" 
                                                  rows="2">{{ old('notes', $invoice->notes) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Items Table -->
                    <div class="card mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fa fa-shopping-cart"></i> Edit Items</h6>
                            <div class="text-muted">
                                Current Total: <strong>৳{{ number_format($invoice->total, 2) }}</strong>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="itemsTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="40%">Item Name</th>
                                            <th width="15%">Quantity</th>
                                            <th width="20%">Unit Price (৳)</th>
                                            <th width="20%">Total (৳)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoice->items as $index => $item)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <input type="hidden" name="items[{{ $index }}][id]" 
                                                       value="{{ $item->id }}">
                                                <input type="text" class="form-control" 
                                                       name="items[{{ $index }}][item_name]" 
                                                       value="{{ old('items.'.$index.'.item_name', $item->item_name) }}" 
                                                       required>
                                                @error('items.'.$index.'.item_name')
                                                    <span class="text-danger small">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="number" class="form-control quantity" 
                                                       name="items[{{ $index }}][quantity]" 
                                                       value="{{ old('items.'.$index.'.quantity', $item->quantity) }}" 
                                                       required min="1" data-index="{{ $index }}">
                                                @error('items.'.$index.'.quantity')
                                                    <span class="text-danger small">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control unit-price" 
                                                       name="items[{{ $index }}][unit_price]" 
                                                       value="{{ old('items.'.$index.'.unit_price', $item->unit_price) }}" 
                                                       required min="0" data-index="{{ $index }}">
                                                @error('items.'.$index.'.unit_price')
                                                    <span class="text-danger small">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="text" class="form-control total-price" 
                                                       value="৳{{ number_format($item->total_price, 2) }}" 
                                                       readonly>
                                                <input type="hidden" class="total-price-hidden" 
                                                       value="{{ $item->total_price }}">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <!-- Added Total Quantity Row -->
                                        <tr class="bg-light">
                                            <td colspan="2" class="text-right font-weight-bold">Total Quantity:</td>
                                            <td colspan="1" class="font-weight-bold text-center">
                                                <span id="total-quantity">{{ $invoice->items->sum('quantity') }}</span>
                                            </td>
                                            <td colspan="2"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="text-right font-weight-bold">Subtotal:</td>
                                            <td colspan="3" class="font-weight-bold">
                                                ৳<span id="subtotal">{{ number_format($invoice->subtotal, 2) }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2" class="text-right font-weight-bold">Delivery Charge:</td>
                                            <td colspan="3" class="font-weight-bold">
                                                ৳<span id="delivery-display">{{ number_format($invoice->delivery_charge, 2) }}</span>
                                            </td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td colspan="2" class="text-right font-weight-bold">Grand Total:</td>
                                            <td colspan="3" class="font-weight-bold">
                                                ৳<span id="grand-total">{{ number_format($invoice->total, 2) }}</span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Update Invoice
                        </button>
                        <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
$(document).ready(function() {
    // Initialize totals on page load
    initializeTotals();
    
    // Calculate item total when quantity or price changes
    $(document).on('input', '.quantity, .unit-price', function() {
        calculateItemTotal($(this));
        calculateAllTotals();
    });
    
    // Update totals when delivery charge changes
    $('#delivery_charge').on('input', function() {
        const deliveryCharge = parseFloat($(this).val()) || 0;
        $('#delivery-display').text(deliveryCharge.toFixed(2));
        calculateGrandTotal();
    });
    
    function initializeTotals() {
        // Calculate each item's total on page load
        $('.quantity').each(function() {
            calculateItemTotal($(this));
        });
        calculateAllTotals();
    }
    
    function calculateItemTotal(element) {
        const index = element.data('index');
        const quantity = parseFloat($('input[name="items[' + index + '][quantity]"]').val()) || 0;
        const unitPrice = parseFloat($('input[name="items[' + index + '][unit_price]"]').val()) || 0;
        const total = quantity * unitPrice;
        
        // Update total display
        element.closest('tr').find('.total-price').val('৳' + total.toFixed(2));
        element.closest('tr').find('.total-price-hidden').val(total);
    }
    
    function calculateAllTotals() {
        let subtotal = 0;
        let totalQuantity = 0;
        
        // Calculate item subtotal and total quantity
        $('tbody tr').each(function() {
            const quantity = parseFloat($(this).find('.quantity').val()) || 0;
            const unitPrice = parseFloat($(this).find('.unit-price').val()) || 0;
            const itemTotal = quantity * unitPrice;
            
            subtotal += itemTotal;
            totalQuantity += quantity;
            
            // Update hidden total for this row
            $(this).find('.total-price-hidden').val(itemTotal);
        });
        
        // Update display
        $('#total-quantity').text(totalQuantity);
        $('#subtotal').text(subtotal.toFixed(2));
        calculateGrandTotal();
    }
    
    function calculateGrandTotal() {
        const subtotal = parseFloat($('#subtotal').text()) || 0;
        const deliveryCharge = parseFloat($('#delivery_charge').val()) || 0;
        const grandTotal = subtotal + deliveryCharge;
        
        $('#grand-total').text(grandTotal.toFixed(2));
    }
    
    // Form validation
    $('#editInvoiceForm').on('submit', function(e) {
        let valid = true;
        let errorFields = [];
        
        // Check all required fields
        $('input[required]').each(function() {
            if (!$(this).val().trim()) {
                valid = false;
                $(this).addClass('is-invalid');
                errorFields.push($(this).attr('name'));
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        // Check for valid numbers
        $('.quantity, .unit-price').each(function() {
            const value = parseFloat($(this).val());
            if (isNaN(value) || value < 0) {
                valid = false;
                $(this).addClass('is-invalid');
                errorFields.push($(this).attr('name'));
            }
        });
        
        if (!valid) {
            e.preventDefault();
            let errorMessage = 'Please fix the following errors:\n';
            errorFields.forEach(field => {
                errorMessage += '- ' + field.replace('items[', 'Item ').replace('][', ' ').replace(']', '') + '\n';
            });
            alert(errorMessage);
            return false;
        }
        
        // Confirm update
        return confirm('Are you sure you want to update this invoice?\n\nNew Totals:\n- Total Quantity: ' + $('#total-quantity').text() + '\n- Subtotal: ৳' + $('#subtotal').text() + '\n- Grand Total: ৳' + $('#grand-total').text());
    });
    
    // Auto-select text on focus for easier editing
    $('input[type="number"], input[type="text"]').on('focus', function() {
        $(this).select();
    });
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl+Enter to submit form
        if (e.ctrlKey && e.keyCode === 13) {
            $('#editInvoiceForm').submit();
        }
        
        // Esc key to go back
        if (e.keyCode === 27) {
            window.location.href = "{{ route('invoices.show', $invoice->id) }}";
        }
    });
});
</script>
<style>
.form-control-plaintext {
    background-color: #f8f9fa;
    padding: 0.375rem 0.75rem;
    border: 1px solid #e9ecef;
    border-radius: 0.25rem;
}

.table th {
    background-color: #f8f9fa;
}

.total-price {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #28a745;
}

.table-primary {
    background-color: #e3f2fd;
}

#itemsTable input.form-control {
    min-width: 80px;
    transition: all 0.2s;
}

#itemsTable input.form-control:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    border-color: #80bdff;
}

#total-quantity {
    font-size: 1.1em;
    color: #dc3545;
    font-weight: bold;
}

#grand-total {
    font-size: 1.2em;
    color: #28a745;
}

/* Responsive table */
@media (max-width: 768px) {
    #itemsTable {
        font-size: 14px;
    }
    
    #itemsTable input.form-control {
        padding: 0.25rem;
        font-size: 14px;
    }
    
    .card-header {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    .card-header .btn {
        margin-top: 10px;
        align-self: flex-end;
    }
}
</style>
@endsection