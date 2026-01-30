@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1><i class="fa fa-cash-register"></i> Point of Sale (POS)</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li class="active">POS System</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="row">
        <!-- Left Side - Items Entry -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fa fa-shopping-cart"></i> Faisal Textile
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="itemsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th width="30%">Item Name</th>
                                    <th width="25%">Description</th>
                                    <th width="15%">Quantity</th>
                                    <th width="15%">Unit Price</th>
                                    <th width="15%">Total</th>
                                    <th width="5%">Action</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <!-- Items will be added here -->
                            </tbody>
                            <!-- Summary Row -->
                            <tfoot id="summaryRow" style="display: none;">
                                <tr class="bg-light">
                                    <td colspan="3" class="text-right"><strong>Summary:</strong></td>
                                    <td colspan="3">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <small>Total: <span id="summaryTotal" class="text-success font-weight-bold">৳0.00</span></small>
                                            </div>
                                            <div class="col-md-4">
                                                <small>Paid: <span id="summaryPaid" class="text-info font-weight-bold">৳0.00</span></small>
                                            </div>
                                            <div class="col-md-4">
                                                <small>Due: <span id="summaryDue" class="text-danger font-weight-bold">৳0.00</span></small>
                                            </div>
                                        </div>
                                        <div class="row mt-1">
                                            <div class="col-md-12">
                                                <small>Status: <span id="summaryStatus" class="badge badge-danger">Unpaid</span></small>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    
                </div>
            </div>

        </div>

        <div class="col-lg-4">
            <!-- Customer Selection -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <i class="fa fa-user"></i> Customer Information
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Select Customer *</label>
                        <select class="form-control" id="customerSelect" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">
                                {{ $customer->name }} - {{ $customer->phone_number_1 }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Invoice Date</label>
                        <input type="date" class="form-control" id="invoiceDate" value="{{ $today }}">
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea class="form-control" id="notes" rows="2" placeholder="Any additional notes..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="card mt-3">
                <div class="card-header bg-warning">
                    <i class="fa fa-money-bill-wave"></i> Payment Information
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Paid Amount</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">৳</span>
                            </div>
                            <input type="number" class="form-control" id="paidAmount" value="0" min="0" step="0.01" placeholder="0.00">
                        </div>
                    </div>
                    
                    <!-- Auto-calculate buttons -->
                    <div class="btn-group d-flex mb-3" role="group">
                        <button type="button" class="btn btn-sm btn-outline-primary flex-fill" id="btnPayHalf">
                            Pay 50%
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success flex-fill" id="btnPayFull">
                            Pay Full
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill" id="btnClearPayment">
                            Clear
                        </button>
                    </div>
                    
                    <!-- Quick Status -->
                    <div class="alert" id="paymentStatusAlert" style="display: none;">
                        <strong>Status:</strong> <span id="paymentStatusText"></span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card mt-3">
                <div class="card-body text-center">
                    <button class="btn btn-danger btn-lg mr-2" id="clearBtn">
                        <i class="fa fa-trash"></i> Clear All
                    </button>
                    <button class="btn btn-success btn-lg" id="createInvoiceBtn">
                        <i class="fa fa-check"></i> Create Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for submission -->
<form id="invoiceForm" style="display: none;">
    @csrf
    <input type="hidden" name="customer_id" id="formCustomerId">
    <input type="hidden" name="invoice_date" id="formInvoiceDate">
    <input type="hidden" name="total" id="formTotal">
    <input type="hidden" name="paid_amount" id="formPaidAmount">
    <input type="hidden" name="notes" id="formNotes">
    <div id="itemsContainer"></div>
</form>

<style>
    #itemsTable tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .quantity-input, .price-input {
        width: 80px;
        text-align: center;
    }
    
    .total-cell {
        font-weight: bold;
        color: #28a745;
    }
    
    .card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .card-header {
        border-radius: 10px 10px 0 0 !important;
        font-weight: 600;
    }
    
    #summaryRow td {
        border-top: 2px solid #28a745 !important;
    }
    
    .item-row td {
        vertical-align: middle;
    }
    #addRowButtonRow {
    background-color: #f0f8ff !important;
    border-top: 2px dashed #007bff;
}

#addRowBtn {
    padding: 5px 20px;
    border-radius: 20px;
    transition: all 0.3s;
    border: 1px solid #28a745;
    color: #28a745;
    background-color: transparent;
}

#addRowBtn:hover {
    background-color: #28a745;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.2);
}

#addRowBtn:active {
    transform: translateY(0);
}
</style>
<script>
    $(document).ready(function() {
        let items = [];
        let MAX_ROWS = 10;
    
        // Initialize with 10 empty rows
        initializeEmptyRows();
    
        // Payment amount input
        $('#paidAmount').on('input', calculateTotals);
    
        // Payment quick buttons
        $('#btnPayHalf').click(function() {
            const total = calculateItemsTotal();
            $('#paidAmount').val((total * 0.5).toFixed(2)).trigger('input');
        });
    
        $('#btnPayFull').click(function() {
            const total = calculateItemsTotal();
            $('#paidAmount').val(total.toFixed(2)).trigger('input');
        });
    
        $('#btnClearPayment').click(function() {
            $('#paidAmount').val('0').trigger('input');
        });
    
        // Clear all button
        $('#clearBtn').click(function() {
            if (confirm('Clear all items and reset form?')) {
                clearAll();
            }
        });
    
        // Create invoice button
        $('#createInvoiceBtn').click(function() {
            if (validateForm()) {
                prepareFormData();
                submitInvoice();
            }
        });
    
        // Add row button (extend rows)
        $(document).on('click', '#addRowBtn', function() {
            addNewRow();
        });
    
        // Load SweetAlert for better alerts
        if (typeof Swal === 'undefined') {
            $('head').append('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\/script>');
        }
    
        // Functions
        function initializeEmptyRows() {
            items = [];
            for (let i = 0; i < MAX_ROWS; i++) {
                items.push({
                    id: i,
                    name: '',
                    description: '',
                    quantity: 1,
                    price: 0,
                    total: 0,
                    isFilled: false
                });
            }
            renderItems();
        }
    
        function addNewRow() {
            // Add new empty row
            items.push({
                id: items.length,
                name: '',
                description: '',
                quantity: 1,
                price: 0,
                total: 0,
                isFilled: false
            });
            MAX_ROWS = items.length; // Update MAX_ROWS
            renderItems();
            
            // Scroll to the new row
            $('html, body').animate({
                scrollTop: $('#itemsBody tr:last').offset().top - 100
            }, 500);
            
            // Focus on the new row's item name input
            setTimeout(() => {
                $('#itemsBody tr:last .item-name-input').focus();
            }, 100);
        }
    
        function renderItems() {
            $('#itemsBody').empty();
            let hasItems = false;
            
            items.forEach((item, index) => {
                const row = `
                    <tr class="item-row" data-index="${index}">
                        <td>
                            <input type="text" class="form-control form-control-sm item-name-input" 
                                   placeholder="Item name" data-index="${index}" value="${item.name}">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm description-input" 
                                   placeholder="Description" data-index="${index}" value="${item.description || ''}">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm quantity-input" 
                                   value="${item.quantity}" min="1" data-index="${index}">
                        </td>
                        <td>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">৳</span>
                                </div>
                                <input type="number" class="form-control price-input" 
                                       value="${item.price > 0 ? item.price.toFixed(2) : ''}" 
                                       min="0" step="0.01" placeholder="0.00" data-index="${index}">
                            </div>
                        </td>
                        <td class="total-cell">${item.total > 0 ? '৳' + item.total.toFixed(2) : '৳0.00'}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-danger clear-row-btn" data-index="${index}" title="Clear row">
                                <i class="fa fa-times"></i>
                            </button>
                        </td>
                    </tr>
                `;
                $('#itemsBody').append(row);
                
                // Check if this row has data
                if (item.name.trim() !== '' || item.price > 0) {
                    hasItems = true;
                }
            });
    
            // Add + button row at the bottom
            $('#itemsBody').append(`
                <tr id="addRowButtonRow">
                    <td colspan="6" class="text-center py-2">
                        <button id="addRowBtn" class="btn btn-sm btn-outline-success">
                            <i class="fa fa-plus"></i> Add More Rows
                        </button>
                    </td>
                </tr>
            `);
    
            // Show/hide summary row
            if (hasItems) {
                $('#summaryRow').show();
            } else {
                $('#summaryRow').hide();
            }
    
            // Add event listeners for typing
            setupEventListeners();
        }
    
        function setupEventListeners() {
            // Real-time updates as user types
            $('.item-name-input, .description-input, .quantity-input, .price-input').on('input', function() {
                const index = $(this).data('index');
                updateItem(index);
            });
    
            // Tab key navigation
            $(document).on('keydown', '.item-name-input, .description-input, .quantity-input, .price-input', function(e) {
                if (e.keyCode === 9) { // Tab key
                    e.preventDefault();
                    const currentIndex = $(this).data('index');
                    const inputs = $('.item-name-input, .description-input, .quantity-input, .price-input');
                    const currentInputIndex = inputs.index(this);
                    
                    if (currentInputIndex < inputs.length - 1) {
                        inputs.eq(currentInputIndex + 1).focus();
                    } else {
                        // If at last input, focus on paid amount
                        $('#paidAmount').focus();
                    }
                }
                
                if (e.keyCode === 13) { // Enter key
                    e.preventDefault();
                    const currentIndex = $(this).data('index');
                    const inputs = $('.item-name-input, .description-input, .quantity-input, .price-input');
                    const currentInputIndex = inputs.index(this);
                    
                    // Move to next row's item name if available
                    if (currentInputIndex < inputs.length - 4) { // 4 inputs per row
                        inputs.eq(currentInputIndex + 4).focus();
                    }
                }
            });
    
            // Clear row button
            $(document).on('click', '.clear-row-btn', function(e) {
                e.stopPropagation();
                const index = $(this).data('index');
                clearRow(index);
            });
        }
    
        function updateItem(index) {
            const name = $(`.item-name-input[data-index="${index}"]`).val().trim();
            const description = $(`.description-input[data-index="${index}"]`).val().trim();
            const quantity = parseInt($(`.quantity-input[data-index="${index}"]`).val()) || 1;
            const price = parseFloat($(`.price-input[data-index="${index}"]`).val()) || 0;
            const total = quantity * price;
            
            items[index] = {
                id: index,
                name: name,
                description: description,
                quantity: quantity,
                price: price,
                total: total,
                isFilled: (name !== '' && price > 0)
            };
            
            $(`tr[data-index="${index}"] .total-cell`).text(total > 0 ? '৳' + total.toFixed(2) : '৳0.00');
            calculateTotals();
        }
    
        function clearRow(index) {
            if (confirm('Clear this row?')) {
                items[index] = {
                    id: index,
                    name: '',
                    description: '',
                    quantity: 1,
                    price: 0,
                    total: 0,
                    isFilled: false
                };
                renderItems();
                calculateTotals();
            }
        }
    
        function calculateItemsTotal() {
            return items.filter(item => item.isFilled)
                        .reduce((sum, item) => sum + item.total, 0);
        }
    
        function getFilledItems() {
            return items.filter(item => item.isFilled);
        }
    
        function calculateTotals() {
            const total = calculateItemsTotal();
            const paid = parseFloat($('#paidAmount').val()) || 0;
            const due = total - paid;
            
            // Determine status
            let status = 'unpaid';
            let statusClass = 'danger';
            let alertClass = 'alert-danger';
            
            if (due <= 0) {
                status = 'paid';
                statusClass = 'success';
                alertClass = 'alert-success';
            } else if (paid > 0) {
                status = 'partial';
                statusClass = 'warning';
                alertClass = 'alert-warning';
            }
    
            // Update summary row
            $('#summaryTotal').text('৳' + total.toFixed(2));
            $('#summaryPaid').text('৳' + paid.toFixed(2));
            $('#summaryDue').text('৳' + due.toFixed(2));
            $('#summaryStatus').removeClass('badge-success badge-warning badge-danger')
                              .addClass('badge-' + statusClass)
                              .text(status.charAt(0).toUpperCase() + status.slice(1));
    
            // Update payment status alert
            $('#paymentStatusAlert').removeClass('alert-success alert-warning alert-danger')
                                   .addClass(alertClass)
                                   .show();
            $('#paymentStatusText').text(status.charAt(0).toUpperCase() + status.slice(1));
    
            // Update form fields
            $('#formTotal').val(total);
            $('#formPaidAmount').val(paid);
            
            // Show/hide summary row based on whether there are items
            const hasItems = getFilledItems().length > 0;
            if (hasItems) {
                $('#summaryRow').show();
            } else {
                $('#summaryRow').hide();
            }
        }
    
        function clearAll() {
            items = [];
            MAX_ROWS = 10; // Reset to 10 rows
            for (let i = 0; i < MAX_ROWS; i++) {
                items.push({
                    id: i,
                    name: '',
                    description: '',
                    quantity: 1,
                    price: 0,
                    total: 0,
                    isFilled: false
                });
            }
            $('#itemsBody').empty();
            $('#customerSelect').val('');
            $('#paidAmount').val('0');
            $('#notes').val('');
            renderItems();
            calculateTotals();
            $('#paymentStatusAlert').hide();
            $('#summaryRow').hide();
        }
    
        function validateForm() {
            // Validate customer
            if ($('#customerSelect').val() === '') {
                alert('Please select a customer.');
                $('#customerSelect').focus();
                return false;
            }
    
            // Validate items
            const filledItems = getFilledItems();
            if (filledItems.length === 0) {
                alert('Please add at least one item to the cart.');
                $('#itemsBody .item-name-input:first').focus();
                return false;
            }
    
            // Validate each filled item
            for (const item of filledItems) {
                if (!item.name.trim()) {
                    alert('Please enter item name for all items.');
                    $(`.item-name-input[data-index="${item.id}"]`).focus();
                    return false;
                }
                if (item.price <= 0) {
                    alert('Please enter valid price for all items.');
                    $(`.price-input[data-index="${item.id}"]`).focus();
                    return false;
                }
            }
    
            // Validate total
            const total = calculateItemsTotal();
            if (total <= 0) {
                alert('Total amount must be greater than zero.');
                return false;
            }
    
            // Validate payment (paid cannot be more than total)
            const paid = parseFloat($('#paidAmount').val()) || 0;
            if (paid > total) {
                if (!confirm('Paid amount is greater than total. Continue anyway?')) {
                    return false;
                }
            }
    
            return true;
        }
    
        function prepareFormData() {
            $('#formCustomerId').val($('#customerSelect').val());
            $('#formInvoiceDate').val($('#invoiceDate').val());
            $('#formNotes').val($('#notes').val());
    
            // Add only filled items to hidden form
            $('#itemsContainer').empty();
            const filledItems = getFilledItems();
            filledItems.forEach((item, index) => {
                $('#itemsContainer').append(`
                    <input type="hidden" name="items[${index}][item_name]" value="${item.name}">
                    <input type="hidden" name="items[${index}][description]" value="${item.description || ''}">
                    <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
                    <input type="hidden" name="items[${index}][unit_price]" value="${item.price}">
                `);
            });
        }
    
        function submitInvoice() {
            $('#createInvoiceBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
    
            $.ajax({
                url: '{{ route("pos.store") }}',
                method: 'POST',
                data: $('#invoiceForm').serialize(),
                success: function(response) {
                    if (response.success) {
                        // Show success message with print option
                        Swal.fire({
                            title: 'Success!',
                            html: `Invoice <strong>#${response.invoice_number}</strong> created successfully!`,
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: 'Print Invoice',
                            cancelButtonText: 'Continue',
                            showDenyButton: true,
                            denyButtonText: 'View Invoice'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Open print window
                                window.open('/pos/print/' + response.invoice_id, '_blank');
                                // Clear form and reload
                                clearAll();
                            } else if (result.isDenied) {
                                // Redirect to view invoice
                                window.location.href = response.redirect_url;
                            } else {
                                // Just clear form and reload
                                clearAll();
                            }
                        });
                    } else {
                        alert('Error: ' + response.message);
                        $('#createInvoiceBtn').prop('disabled', false).html('<i class="fa fa-check"></i> Create Invoice');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Error creating invoice. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    alert(errorMessage);
                    $('#createInvoiceBtn').prop('disabled', false).html('<i class="fa fa-check"></i> Create Invoice');
                }
            });
        }
    
        // Add CSS for better UX
        $('<style>')
            .prop('type', 'text/css')
            .html(`
                .item-row:hover {
                    background-color: #f8f9fa;
                }
                .item-name-input:focus, .price-input:focus {
                    border-color: #007bff;
                    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
                }
                .total-cell {
                    font-weight: bold;
                    color: #28a745;
                    vertical-align: middle;
                }
                .clear-row-btn {
                    opacity: 0.6;
                    transition: opacity 0.2s;
                }
                .clear-row-btn:hover {
                    opacity: 1;
                }
                #summaryRow {
                    background-color: #f8f9fa;
                    font-weight: bold;
                }
                #addRowButtonRow {
                    background-color: #f0f8ff !important;
                }
                #addRowBtn {
                    transition: all 0.3s;
                }
                #addRowBtn:hover {
                    transform: scale(1.05);
                    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                }
            `)
            .appendTo('head');
    
        // Focus on first item name input on page load
        setTimeout(() => {
            $('#itemsBody .item-name-input:first').focus();
        }, 100);
    
        // Initial calculation
        calculateTotals();
    });
    </script>
@endsection