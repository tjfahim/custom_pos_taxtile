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
                    <a href="#" class="btn btn-purple btn-sm mr-2" id="print-selected-btn" disabled>
                        <i class="fa fa-print"></i> Print Selected <span id="selected-count" style="display: none;"></span>
                    </a>
                    <!-- Time Range Picker Button -->
                    <button type="button" class="btn btn-info btn-sm mr-2" data-toggle="modal" data-target="#timeRangeModal">
                        <i class="fa fa-clock-o"></i> Custom Time CSV
                    </button>
   
                  
                   
                    <div class="btn-group" role="group" aria-label="CSV Download Options">
                        <a href="{{ route('admin.invoices.download-today-csv') }}" class="btn btn-info btn-sm mr-2">
                            <i class="fa fa-download"></i> Today's CSV (All)
                        </a>
                    </div>
              
                    <a href="{{ route('admin.invoices.pos') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Create Invoice
                    </a>
                </div>
            </div>
            
            <!-- Time Range Selection Modal -->
            @include('admin.invoices.partials.time-range-modal')
            
            <!-- Status Filter Buttons -->
            <div class="card-header py-2">
                <div class="btn-group btn-group-sm" role="group">
                    <button type="button" class="btn btn-outline-secondary status-filter active" data-status="">
                        All <span class="badge badge-light" id="count-all">{{ $counts['all'] }}</span>
                    </button>
                    <button type="button" class="btn btn-outline-success status-filter" data-status="confirmed">
                        <i class="fa fa-check-circle"></i> Confirmed 
                        <span class="badge badge-light" id="count-confirmed">{{ $counts['confirmed'] }}</span>
                    </button>
                    <button type="button" class="btn btn-outline-warning status-filter" data-status="pending">
                        <i class="fa fa-clock"></i> Pending 
                        <span class="badge badge-light" id="count-pending">{{ $counts['pending'] }}</span>
                    </button>
                    <button type="button" class="btn btn-outline-danger status-filter" data-status="cancelled">
                        <i class="fa fa-times-circle"></i> Cancelled 
                        <span class="badge badge-light" id="count-cancelled">{{ $counts['cancelled'] }}</span>
                    </button>
                </div>
            </div>

            <div class="card-body p-2">
                <div class="table-responsive">
                    <table id="invoicesTable" class="table table-sm table-hover" style="width:100%">
                        <thead>
                            <tr>
                                  <th style="width: 30px;">
            <input type="checkbox" id="select-all-invoices" style="cursor: pointer;">
        </th>
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
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Loading Overlay -->
<div id="loading-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.7); z-index: 9998;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
</div>

<!-- Include CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Include Modal Specific CSS -->
<link rel="stylesheet" href="{{ asset('css/invoices.css') }}">

<!-- Include Modal Specific JavaScript -->
<script src="{{ asset('js/admin/invoices/time-range-modal.js') }}"></script>
<script src="{{ asset('js/admin/invoices/multi-print.js') }}"></script>

<script>
$(document).ready(function() {
    let currentStatus = '';
    let table;
    let loadingTimeout;
    
    // Show loading overlay
    function showLoading() {
        clearTimeout(loadingTimeout);
        $('#loading-overlay').fadeIn(200);
    }
    
    // Hide loading overlay
    function hideLoading() {
        $('#loading-overlay').fadeOut(200);
    }
    
    // Initialize DataTable
    table = $('#invoicesTable').DataTable({
        processing: false,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.invoices.index') }}",
            type: 'GET',
            data: function(d) {
                d.status = currentStatus;
            },
            beforeSend: function() {
                showLoading();
            },
            complete: function() {
                setTimeout(hideLoading, 300);
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable error:', {xhr: xhr, error: error, thrown: thrown});
                hideLoading();
                showToast('error', 'Error', 'Failed to load data');
            }
        },
        columns: [
                { 
            data: 'id',  // We'll use id for checkbox
            name: 'id',
            orderable: false,
            searchable: false,
            render: function(data, type, row) {
                return '<input type="checkbox" class="select-invoice" data-invoice-id="' + data + '">';
            }
        },
            { 
                data: 'DT_RowIndex', 
                name: 'DT_RowIndex', 
                orderable: false, 
                searchable: false 
            },
            { 
                data: 'invoice_number', 
                name: 'invoice_number' 
            },
            { 
                data: 'customer_name', 
                name: 'customer_name' 
            },
            { 
                data: 'customer_phone', 
                name: 'customer_phone' 
            },
            { 
                data: 'merchant_order_id', 
                name: 'merchant_order_id' 
            },
            { 
                data: 'invoice_date', 
                name: 'invoice_date' 
            },
            { 
                data: 'total', 
                name: 'total' 
            },
            { 
                data: 'status', 
                name: 'status',
                render: function(data) {
                    if (data && data.badge) {
                        return '<span class="badge badge-' + data.badge + '">' + data.text + '</span>';
                    }
                    return '<span class="badge badge-secondary">Unknown</span>';
                }
            },
            { 
                data: 'payment_status', 
                name: 'payment_status',
                render: function(data) {
                    if (data && data.badge) {
                        return '<span class="badge badge-' + data.badge + '">' + data.text + '</span>';
                    }
                    return '<span class="badge badge-secondary">Unknown</span>';
                }
            },
            @if(auth()->user()->hasRole('admin'))
                { 
                    data: 'created_by', 
                    name: 'created_by' 
                },
            @endif
            { 
                data: 'actions', 
                name: 'actions',
                orderable: false,
                searchable: false
            }
        ],
        order: [[5, 'desc']],
        pageLength: 20,
        lengthMenu: [[20, 50, 100, 200, 500], [20, 50, 100, 200, 500]],
        responsive: true,
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
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>><"row"<"col-sm-12"tr>><"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        initComplete: function(settings, json) {
            console.log('DataTable initialized successfully');
            hideLoading();
            $('[title]').tooltip();
        },
        drawCallback: function() {
            hideLoading();
            $('[title]').tooltip();
        }
    });
    
    // Status filter buttons
    $('.status-filter').on('click', function() {
        const status = $(this).data('status');
        
        $('.status-filter').removeClass('active');
        $(this).addClass('active');
        
        currentStatus = status;
        showLoading();
        table.ajax.reload(function() {
            setTimeout(hideLoading, 300);
        }, false);
    });
    
    // Function to update status counts via AJAX
    function updateStatusCounts() {
        $.ajax({
            url: "{{ route('admin.invoices.index') }}",
            type: 'GET',
            data: { counts_only: true },
            success: function(response) {
                if (response.counts) {
                    $('#count-all').text(response.counts.all);
                    $('#count-confirmed').text(response.counts.confirmed);
                    $('#count-pending').text(response.counts.pending);
                    $('#count-cancelled').text(response.counts.cancelled);
                }
            }
        });
    }
    
    // Toast notification function
    function showToast(type, title, message) {
        let toastContainer = $('.toast-container');
        if (toastContainer.length === 0) {
            $('body').append('<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>');
            toastContainer = $('.toast-container');
        }
        
        const toastId = 'toast-' + Date.now();
        
        let icon = 'info-circle';
        if (type === 'success') icon = 'check-circle';
        if (type === 'error') icon = 'exclamation-circle';
        
        const bgColor = type === 'success' ? 'success' : (type === 'error' ? 'danger' : 'info');
        
        const toastHtml = `
            <div id="${toastId}" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-${bgColor} text-white">
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

    // Add custom search with debounce
    $('div.dataTables_filter input').unbind().bind('keyup', function(e) {
        if (e.keyCode == 13) {
            showLoading();
            table.search(this.value).draw();
        } else {
            clearTimeout($.data(this, 'timer'));
            $(this).data('timer', setTimeout(function() {
                showLoading();
                table.search($('div.dataTables_filter input').val()).draw();
            }, 500));
        }
    });
    
    // Event delegation for status update buttons - NO CONFIRMATION MODAL
    $(document).on('click', '.btn-status-update', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const button = $(this);
        const invoiceId = button.data('invoice-id');
        const targetStatus = button.data('target-status');
        
        // Store original button content
        const originalHtml = button.html();
        button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: `/admin/invoices/${invoiceId}/status`,
            type: 'PATCH',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                status: targetStatus
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', 'Success', response.message);
                    
                    // Show loading
                    showLoading();
                    
                    // Reload the table to reflect changes
                    table.ajax.reload(function() {
                        setTimeout(hideLoading, 300);
                        
                        // Update counts after table reload
                        updateStatusCounts();
                        
                      if (response.data && response.data.invoice_number) {
                showToast('info', 'Invoice Number', `New invoice number: ${response.data.invoice_number}`);
            }
            
            // TRIGGER THIS EVENT
            $(document).trigger('status-update-complete');
                    }, false);
                } else {
                    showToast('error', 'Error', response.message || 'Failed to update status');
                    button.prop('disabled', false).html(originalHtml);
                    hideLoading();
                }
            },
            error: function(xhr) {
                let errorMsg = 'Server error occurred';
                if (xhr.responseJSON) {
                    errorMsg = xhr.responseJSON.message || errorMsg;
                } else if (xhr.status === 404) {
                    errorMsg = 'Invoice not found';
                } else if (xhr.status === 403) {
                    errorMsg = 'You do not have permission to perform this action';
                } else if (xhr.status === 422) {
                    errorMsg = 'Validation error: ' + (xhr.responseJSON?.message || 'Invalid status');
                }
                
                showToast('error', 'Error', errorMsg);
                button.prop('disabled', false).html(originalHtml);
                hideLoading();
                
                console.error('Status update error:', xhr.responseJSON || xhr);
            }
        });
    });
    
    // Event delegation for delete forms (keeping confirmation for delete as it's destructive)
    $(document).on('submit', 'form.d-inline', function(e) {
        if (!confirm('Delete this invoice?')) {
            e.preventDefault();
            return false;
        }
    });
    
    console.log('Document ready, status button events delegated - no confirmation modals');
});
</script>
@endsection