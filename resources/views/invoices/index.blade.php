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
                         
                    <a href="{{ route('admin.invoices.download-today-csv') }}" class="btn btn-info btn-sm mr-2">
                        <i class="fa fa-download"></i> Today's CSV
                    </a>
   @endcan
                    <a href="{{ route('admin.invoices.pos') }}" class="btn btn-primary btn-sm">
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
                                <th>Payment</th>
                          @if(auth()->user()->hasRole('admin'))
                <th>Created By</th>
            @endif
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $index => $invoice)
                            <tr data-status="{{ $invoice->status }}" data-id="{{ $invoice->id }}">
                                <td>{{ $loop->iteration }}</td>
                                <td class="invoice-number">{{ $invoice->invoice_number }}</td>
                                <td>{{ $invoice->customer->name }}</td>
                                <td>{{ $invoice->customer->phone_number_1 }}</td>
                                <td>{{ $invoice->merchant_order_id }}</td>
                                <td class="invoice-date">{{ $invoice->invoice_date->format('d M Y') }}</td>
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
                                    <span class="badge badge-{{ 
                                        $invoice->payment_status == 'paid' ? 'success' : 
                                        ($invoice->payment_status == 'partial' ? 'warning' : 'danger') 
                                    }}">
                                        {{ ucfirst($invoice->payment_status) }}
                                    </span>
                                </td>
                @if(auth()->user()->hasRole('admin'))
                <td>{{ $invoice->creator->name ?? 'N/A' }}</td>
            @endif
                               
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                             @can('print invoices')
                                        <a href="{{ route('admin.invoices.print', $invoice->id) }}" 
                                           class="btn btn-info" title="Print">
                                            <i class="fa fa-print"></i>
                                        </a>
                                                 @endcan
                                             @can('view invoices')
                                        <a href="{{ route('admin.invoices.show', $invoice->id) }}" 
                                           class="btn btn-secondary" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                                 @endcan
                                        @can('edit invoices')
                                        <a href="{{ route('admin.invoices.edit', $invoice->id) }}" 
                                           class="btn btn-warning" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @endcan
                                        
                                        <!-- Status Update Buttons -->
                                        @if($invoice->status == 'pending')
                                        <button type="button" 
                                                class="btn btn-success btn-status-update" 
                                                title="Confirm Invoice"
                                                data-invoice-id="{{ $invoice->id }}"
                                                data-target-status="confirmed">
                                            <i class="fa fa-check"></i>
                                        </button>
                                      
                                        @endif
                                        
                                        @if($invoice->status == 'confirmed')
                                        <button type="button" 
                                                class="btn btn-primary btn-status-update" 
                                                title="Mark as Pending"
                                                data-invoice-id="{{ $invoice->id }}"
                                                data-target-status="pending">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        @endif
                                        
                                        
                                        @can('delete invoices')
                                        <form action="{{ route('admin.invoices.destroy', $invoice->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" 
                                                    onclick="return confirm('Delete this invoice?')"
                                                    title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
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
        order: [[1, 'desc']],
        pageLength: 20,
        lengthMenu: [[20, 50, 100, 200, 500], [20, 50, 100, 200, 500]],
        responsive: true,
        columnDefs: [
            { orderable: false, targets: [0, 8] },
            { searchable: false, targets: [0, 8] }
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
    
    // Status filter buttons
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
    
    function updateSerialNumbers() {
        $('#invoicesTable tbody tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }
    
    table.on('draw', function() {
        updateSerialNumbers();
    });
    
    // Initialize tooltips
    $('[title]').tooltip();
});

// Status update functionality
$(document).on('click', '.btn-status-update', function(e) {
    e.preventDefault();
    
    const button = $(this);
    const invoiceId = button.data('invoice-id');
    const targetStatus = button.data('target-status');
    const row = button.closest('tr');
    const currentStatus = row.data('status');
    const invoiceNumber = row.find('.invoice-number').text();
    
    // Define status mapping for messages
    const statusMessages = {
        'confirmed': 'Confirm',
        'pending': 'Mark as Pending',
    };
    
    // Define button colors for different actions
    const buttonColors = {
        'pending->confirmed': 'success',
        'confirmed->pending': 'info',
    };
    
    const actionKey = currentStatus + '->' + targetStatus;
    const buttonColor = buttonColors[actionKey] || 'primary';
    
    // Disable button and show loading
    const originalHtml = button.html();
    button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
    
 
    // Send AJAX request
    $.ajax({
        url: `/admin/invoices/${invoiceId}/status`,
        type: 'PATCH',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            status: targetStatus
        },
        success: function(response) {
            if (response.success) {
                // Show success toast
                showToast('success', 'Success', response.message);
                
                // Update row data attribute
                row.data('status', targetStatus);
                
              
                 const statusBadge = row.find('td:nth-child(8) .badge'); // Status column
        statusBadge.removeClass('badge-success badge-warning badge-danger');

        if (targetStatus === 'confirmed') {
            statusBadge.addClass('badge-success');
        } else if (targetStatus === 'pending') {
            statusBadge.addClass('badge-warning');
        } else {
            statusBadge.addClass('badge-danger');
        }
        statusBadge.text(response.data.status_text);
              
                
                if (response.data.invoice_number) {
            row.find('.invoice-number').text(response.data.invoice_number);
        }
        if (response.data.invoice_date) {
            row.find('.invoice-date').text(response.data.invoice_date);
        }
                updateStatusButtons(row, targetStatus);
        
        // Update status filter counts
        updateStatusCounts();
        
        // Re-initialize tooltips for new buttons
        $('[title]').tooltip();
    } else {
        showToast('error', 'Error', response.message || 'Failed to update status');
        button.prop('disabled', false).html(originalHtml);
    }
        },
        error: function(xhr) {
            const errorMsg = xhr.responseJSON?.message || 'Server error occurred';
            showToast('error', 'Error', errorMsg);
            button.prop('disabled', false).html(originalHtml);
        }
    });
});

// Function to update status buttons based on current status
function updateStatusButtons(row, status) {
    const buttonsCell = row.find('td:last-child .btn-group');
    
    // Remove existing status buttons (keep Print, View, Edit, Delete)
    buttonsCell.find('.btn-status-update').remove();
    
    // Add appropriate buttons based on new status
    if (status === 'pending') {
        buttonsCell.append(`
            <button type="button" 
                    class="btn btn-success btn-status-update" 
                    title="Confirm Invoice"
                    data-invoice-id="${row.data('id')}"
                    data-target-status="confirmed">
                <i class="fa fa-check"></i>
            </button>
            
        `);
    } else if (status === 'confirmed') {
        buttonsCell.append(`
            <button type="button" 
                    class="btn btn-primary btn-status-update" 
                    title="Mark as Pending"
                    data-invoice-id="${row.data('id')}"
                    data-target-status="pending">
                <i class="fa fa-check"></i>
            </button>
        `);
    } else if (status === 'cancelled') {
        buttonsCell.append(`
            <button type="button" 
                    class="btn btn-info btn-status-update" 
                    title="Mark as Pending"
                    data-invoice-id="${row.data('id')}"
                    data-target-status="pending">
                <i class="fa fa-redo"></i>
            </button>
        `);
    }
}

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

// Toast notification function
function showToast(type, title, message) {
    let toastContainer = $('.toast-container');
    if (toastContainer.length === 0) {
        $('body').append('<div class="toast-container position-fixed top-0 end-0 p-3"></div>');
        toastContainer = $('.toast-container');
    }
    
    const toastId = 'toast-' + Date.now();
    
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
    
    setTimeout(() => {
        toastElement.remove();
    }, 5000);
    
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

.toast-container {
    z-index: 9999;
    top: 20px !important;
    right: 20px !important;
}

.toast {
    min-width: 300px;
    margin-bottom: 10px;
    box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
    animation: slideInRight 0.3s ease-out;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 0.375rem;
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