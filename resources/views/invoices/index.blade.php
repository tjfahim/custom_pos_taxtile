@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fa fa-file-invoice"></i> Invoices
                </h5>
                <div>
                    <a href="{{ route('invoices.download-today-csv') }}" class="btn btn-info btn-sm mr-2">
                        <i class="fa fa-download"></i> Today's CSV
                    </a>
                    <a href="{{ route('invoices.pos') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Create Invoice
                    </a>
                </div>
            </div>
            
            <!-- Status Filter Buttons -->
            <div class="card-header py-2">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary active" data-status="">
                        All <span class="badge badge-light">{{ $invoices->count() }}</span>
                    </button>
                    <button type="button" class="btn btn-outline-success" data-status="confirmed">
                        <i class="fa fa-check-circle"></i> Confirmed 
                        <span class="badge badge-light">{{ $invoices->where('status', 'confirmed')->count() }}</span>
                    </button>
                    <button type="button" class="btn btn-outline-warning" data-status="pending">
                        <i class="fa fa-clock"></i> Pending 
                        <span class="badge badge-light">{{ $invoices->where('status', 'pending')->count() }}</span>
                    </button>
                    <button type="button" class="btn btn-outline-danger" data-status="cancelled">
                        <i class="fa fa-times-circle"></i> Cancelled 
                        <span class="badge badge-light">{{ $invoices->where('status', 'cancelled')->count() }}</span>
                    </button>
                </div>
            </div>

            <div class="card-body p-2">
                <div class="table-responsive">
                    <table id="invoicesTable" class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Merchant ID</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $index => $invoice)
                            <tr data-status="{{ $invoice->status }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $invoice->invoice_number }}</td>
                                <td>{{ $invoice->customer->name }}</td>
                                <td>{{ $invoice->customer->phone_number_1 }}</td>
                                <td>{{ $invoice->merchant_order_id }}</td>
                                <td>{{ $invoice->invoice_date->format('d M') }}</td>
                                <td>৳{{ number_format($invoice->total, 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ 
                                        $invoice->status == 'confirmed' ? 'success' : 
                                        ($invoice->status == 'pending' ? 'warning' : 'danger') 
                                    }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('invoices.print', $invoice->id) }}" 
                                           class="btn btn-info" title="Print">
                                            <i class="fa fa-print"></i>
                                        </a>
                                        <a href="{{ route('invoices.show', $invoice->id) }}" 
                                           class="btn btn-secondary" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('invoices.edit', $invoice->id) }}" 
                                           class="btn btn-warning" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        
                                        <!-- Status Update Button (only for pending invoices) -->
                                        @if($invoice->status == 'pending')
                                        <button type="button" 
                                                class="btn btn-success btn-status-update" 
                                                title="Confirm Invoice"
                                                data-invoice-id="{{ $invoice->id }}"
                                                data-invoice-number="{{ $invoice->invoice_number }}">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        @endif
                                        
                                        <form action="{{ route('invoices.destroy', $invoice->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" 
                                                    onclick="return confirm('Delete this invoice?')"
                                                    title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#invoicesTable').DataTable({
        order: [[1, 'desc']], // Sort by Invoice # (2nd column) descending
        pageLength: 20,
        lengthMenu: [[20, 50, 100, 200, 500], [20, 50, 100, 200, 500]],
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [0, 8] }, // SL and Actions not sortable
            { searchable: false, targets: [0, 8] } // SL and Actions not searchable
        ],
        language: {
            emptyTable: 'No invoices found',
            info: 'Showing _START_ to _END_ of _TOTAL_ invoices',
            infoEmpty: 'Showing 0 to 0 of 0 invoices',
            infoFiltered: '(filtered from _MAX_ total invoices)',
            lengthMenu: 'Show _MENU_ invoices',
            search: 'Search:',
            zeroRecords: 'No matching invoices found',
            paginate: {
                first: 'First',
                last: 'Last',
                next: 'Next',
                previous: 'Previous'
            }
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
    });
    
    // Status filter buttons functionality
    $('.btn-group button[data-status]').on('click', function() {
        const status = $(this).data('status');
        
        // Update button states
        $('.btn-group button').removeClass('active');
        $(this).addClass('active');
        
        // Apply filter
        if (status) {
            table.column(7).search(status).draw();
        } else {
            table.column(7).search('').draw();
        }
        
        // Update SL numbers
        updateSerialNumbers();
    });
    
    // Function to update serial numbers
    function updateSerialNumbers() {
        $('#invoicesTable tbody tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }
    
    // Update serial numbers when table is redrawn
    table.on('draw', function() {
        updateSerialNumbers();
    });
    
    // Initialize tooltips
    $('[title]').tooltip();
});

// Status update functionality - Direct action without confirmation
$(document).on('click', '.btn-status-update', function(e) {
    e.preventDefault();
    
    const button = $(this);
    const invoiceId = button.data('invoice-id');
    const invoiceNumber = button.data('invoice-number');
    
    // Disable button and show loading immediately
    button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    
    // Show processing toast
    showToast('info', 'Processing', `Confirming invoice #${invoiceNumber}...`);
    
    // Send AJAX request
    $.ajax({
        url: `/admin/invoices/${invoiceId}/status`,
        type: 'PATCH',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            status: 'confirmed'
        },
        success: function(response) {
            if (response.success) {
                // Show success toast
                showToast('success', 'Success', response.message);
                
                // Update UI without reload
                const row = button.closest('tr');
                
                // Update status badge
                row.find('.badge')
                    .removeClass('badge-warning')
                    .addClass('badge-success')
                    .text('Confirmed');
                
                // Update the status data attribute
                row.data('status', 'confirmed');
                
                // Remove the status update button
                button.remove();
                
                // Update status filter counts
                updateStatusCounts();
            } else {
                showToast('error', 'Error', response.message || 'Failed to update status');
                button.prop('disabled', false).html('<i class="fa fa-check"></i>');
            }
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Server error occurred';
            showToast('error', 'Error', errorMsg);
            button.prop('disabled', false).html('<i class="fa fa-check"></i>');
        }
    });
});

// Function to update status filter counts
function updateStatusCounts() {
    const tableRows = $('#invoicesTable tbody tr');
    
    // Count all statuses
    const allCount = tableRows.length;
    const confirmedCount = tableRows.filter('[data-status="confirmed"]').length;
    const pendingCount = tableRows.filter('[data-status="pending"]').length;
    const cancelledCount = tableRows.filter('[data-status="cancelled"]').length;
    
    // Update button badges
    $('.btn-group button[data-status=""] .badge').text(allCount);
    $('.btn-group button[data-status="confirmed"] .badge').text(confirmedCount);
    $('.btn-group button[data-status="pending"] .badge').text(pendingCount);
    $('.btn-group button[data-status="cancelled"] .badge').text(cancelledCount);
}

// Simple toast notification function (top-right position)
function showToast(type, title, message) {
    // Create toast container if it doesn't exist
    let toastContainer = $('.toast-container');
    if (toastContainer.length === 0) {
        $('body').append('<div class="toast-container position-fixed top-0 end-0 p-3"></div>');
        toastContainer = $('.toast-container');
    }
    
    const toastId = 'toast-' + Date.now();
    
    // Determine icon based on type
    let icon = 'info-circle';
    if (type === 'success') icon = 'check-circle';
    if (type === 'error') icon = 'exclamation-circle';
    if (type === 'info') icon = 'info-circle';
    
    const toastHtml = `
        <div id="${toastId}" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header bg-${type} text-white">
                <i class="fa fa-${icon} me-2"></i>
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `;
    
    const toastElement = $(toastHtml).appendTo(toastContainer);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toastElement.remove();
    }, 5000);
    
    // Add click handler for close button
    toastElement.find('.btn-close').on('click', function() {
        toastElement.remove();
    });
}
</script>

<style>
.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.02);
}
.badge-success { background-color: #28a745; }
.badge-warning { background-color: #ffc107; color: #212529; }
.badge-danger { background-color: #dc3545; }

.btn-group-sm > .btn.active {
    border-color: #007bff;
    background-color: #007bff;
    color: white;
}
.btn-group-sm > .btn.active .badge {
    background-color: white !important;
    color: #007bff !important;
}
.table>:not(caption)>*>* {
    padding: 0.1rem .1rem !important;
}
.toast-body {
    color: #000000 !important;
    background-color: white;
}

.toast-body.text-dark {
    color: #212529 !important;
}
/* Toast styles */
.toast-container {
    z-index: 9999;
    top: 20px !important;
    right: 20px !important;
    bottom: auto !important;
}

.toast {
    min-width: 300px;
    margin-bottom: 10px;
    box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
    animation: slideInRight 0.3s ease-out;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 0.375rem;
}

.toast.show {
    opacity: 1;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Toast background colors */
.bg-success {
    background-color: #28a745 !important;
}
.bg-error {
    background-color: #dc3545 !important;
}
.bg-info {
    background-color: #17a2b8 !important;
}
</style>
@endsection