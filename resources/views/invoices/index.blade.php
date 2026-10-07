@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h5 class="card-title mb-0">
                    <i class="fa fa-file-invoice"></i> Invoices
                </h5>
                <div>
                    <a href="#" class="btn btn-purple btn-sm mr-2" id="print-selected-btn" disabled>
                        <i class="fa fa-print"></i> Print Selected <span id="selected-count" style="display: none;"></span>
                    </a>

                    <button type="button" class="btn btn-info btn-sm mr-2" data-toggle="modal" data-target="#timeRangeModal">
                        <i class="fa fa-clock-o"></i> Custom Time CSV
                    </button>

                    <div class="btn-group" role="group" aria-label="CSV Download Options">
                        <a href="{{ route('admin.invoices.download-today-csv-exchange') }}" class="btn btn-info btn-sm mr-2">
                            <i class="fa fa-download"></i> Today's CSV (Exchange)
                        </a>
                    </div>

                    <div class="btn-group" role="group" aria-label="CSV Download Options">
                        <a href="{{ route('admin.invoices.download-today-csv') }}" class="btn btn-info btn-sm mr-2">
                            <i class="fa fa-download"></i> Today's CSV (Pathao)
                        </a>
                    </div>

                    <a href="{{ route('admin.invoices.pos') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Create Invoice
                    </a>
                </div>
            </div>

            <!-- Time Range Selection Modal -->
            @include('admin.invoices.partials.time-range-modal')

            {{-- ======================== FILTER PANEL ======================== --}}
            <div class="card-header py-3 bg-light">
                <form id="invoice-filter-form" class="row g-2 align-items-end">

                    {{-- From Date --}}
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label mb-1 small text-muted">From Date</label>
                        <input type="date" name="from_date" id="filter-from-date"
                               class="form-control form-control-sm"
                               value="{{ request('from_date') }}">
                    </div>

                    {{-- To Date --}}
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label mb-1 small text-muted">To Date</label>
                        <input type="date" name="to_date" id="filter-to-date"
                               class="form-control form-control-sm"
                               value="{{ request('to_date') }}">
                    </div>

                    {{-- Team Member --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label mb-1 small text-muted">Team Member</label>
                        <select name="team_member_id" id="filter-team-member"
                                class="form-control form-control-sm">
                            <option value="">All Team Members</option>
                            @foreach($teamMembers as $member)
                                <option value="{{ $member->id }}"
                                    {{ request('team_member_id') == $member->id ? 'selected' : '' }}>
                                    {{ $member->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Courier --}}
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label mb-1 small text-muted">Courier</label>
                        <select name="courier_name" id="filter-courier"
                                class="form-control form-control-sm">
                            <option value="">All Couriers</option>
                            @foreach($courierNames as $courier)
                                <option value="{{ $courier }}"
                                    {{ request('courier_name') == $courier ? 'selected' : '' }}>
                                    {{ $courier }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Status --}}
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label mb-1 small text-muted">Status</label>
                        <select name="status" id="filter-status"
                                class="form-control form-control-sm">
                            <option value="">All Status</option>
                            <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="pending"   {{ request('status') == 'pending'   ? 'selected' : '' }}>Pending</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    {{-- Buttons --}}
                    <div class="col-md-1 col-sm-6">
                        <button type="submit" class="btn btn-primary btn-sm w-100" title="Apply filters">
                            <i class="fa fa-filter"></i>
                        </button>
                    </div>
                    <div class="col-md-1 col-sm-6">
                        <button type="button" id="filter-reset" class="btn btn-outline-secondary btn-sm w-100"
                                title="Reset filters">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </form>

                {{-- Status quick-filter badge buttons --}}
                <div class="mt-2 d-flex align-items-center flex-wrap">
                    <div class="btn-group btn-group-sm mr-2" role="group">
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
                    <small class="text-muted" id="active-filters-summary"></small>
                </div>
            </div>
            {{-- ======================== /FILTER PANEL ======================== --}}

            <div class="card-body p-2">
                <div class="table-responsive">
                    <table id="invoicesTable" class="table table-sm table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 30px;">
                                    <input type="checkbox" id="select-all-invoices" style="cursor: pointer;">
                                </th>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Merchant ID</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th class="text-center">Courier</th>   {{-- 👈 NEW --}}

                                @if(auth()->user()->hasRole('admin'))
                                    <th>Team Member</th>
                                @endif
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data loaded via AJAX -->
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

<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Include Modal Specific CSS / JS -->
<link rel="stylesheet" href="{{ asset('css/invoices.css') }}">
<script src="{{ asset('js/admin/invoices/time-range-modal.js') }}"></script>
<script src="{{ asset('js/admin/invoices/multi-print.js') }}"></script>

<script>
$(document).ready(function() {
    let table;
    let loadingTimeout;

    function showLoading() {
        clearTimeout(loadingTimeout);
        $('#loading-overlay').fadeIn(200);
    }

    function hideLoading() {
        $('#loading-overlay').fadeOut(200);
    }

    // Collect all current filter values
    function currentFilters() {
        return {
            from_date:      $('#filter-from-date').val() || '',
            to_date:        $('#filter-to-date').val() || '',
            team_member_id: $('#filter-team-member').val() || '',
            courier_name:   $('#filter-courier').val() || '',
            status:         $('#filter-status').val() || '',
        };
    }

    // Render "Active filters: ..." small text under the badge buttons
    function renderActiveFilters() {
        const f = currentFilters();
        const bits = [];
        if (f.from_date)      bits.push('From: ' + f.from_date);
        if (f.to_date)        bits.push('To: ' + f.to_date);
        if (f.team_member_id) bits.push('Member: ' + $('#filter-team-member option:selected').text().trim());
        if (f.courier_name)   bits.push('Courier: ' + f.courier_name);
        if (f.status)         bits.push('Status: ' + f.status);
        $('#active-filters-summary').text(bits.length ? 'Active: ' + bits.join(' | ') : '');
    }

    // Initialize DataTable
    table = $('#invoicesTable').DataTable({
        processing: false,
        serverSide: true,
        order: [[5, 'desc']],
        ajax: {
            url: "{{ route('admin.invoices.index') }}",
            type: 'GET',
            data: function(d) {
                Object.assign(d, currentFilters());
            },
            beforeSend: showLoading,
            complete: function() { setTimeout(hideLoading, 300); },
            error: function(xhr, error, thrown) {
                console.error('DataTable error:', {xhr: xhr, error: error, thrown: thrown});
                hideLoading();
                showToast('error', 'Error', 'Failed to load data');
            }
        },
        columns: [
            {
                data: 'id',
                name: 'id',
                orderable: false,
                searchable: false,
                render: function(data) {
                    return '<input type="checkbox" class="select-invoice" data-invoice-id="' + data + '">';
                }
            },
            { data: 'invoice_number', name: 'invoice_number' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'customer_phone', name: 'customer_phone' },
            { data: 'merchant_order_id', name: 'merchant_order_id' },
            { data: 'invoice_date', name: 'invoice_date' },
            { data: 'total', name: 'total' },
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
            {
    data: 'courier_label',
    name: 'courier_label',
    defaultContent: 'N/A',
    className: 'text-center',
    render: function(data) {
        if (!data || data === 'N/A') {
            return '<span class="text-muted">N/A</span>';
        }
        // Pick a colour per courier
        var colours = {
            'Pathao':    '#e74c3c',
            'Steadfast': '#27ae60',
            'SA':        '#16a085',
            'SUNDORBAN': '#8e44ad',
            'JANONI':    '#2c3e50',
            'REDEX':     '#c0392b',
            'Exchange':  '#f39c12',
            'Inhouse':   '#6f42c1'
        };
        var bg = colours[data] || '#17a2b8';
        return '<span class="badge" style="background:' + bg + ';color:#fff;font-size:11px;">' + data + '</span>';
    }
},
            @if(auth()->user()->hasRole('admin'))
            {
                data: 'team_member_name',
                name: 'team_member_name',
                defaultContent: 'N/A'
            },
            @endif
            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false
            }
        ],
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
        initComplete: function() {
            console.log('DataTable initialized successfully');
            hideLoading();
            $('[title]').tooltip();
            renderActiveFilters();
        },
        drawCallback: function() {
            hideLoading();
            $('[title]').tooltip();
        }
    });

    // ---- Form submit: apply all filters ----
    $('#invoice-filter-form').on('submit', function(e) {
        e.preventDefault();

        // keep the badge buttons in sync with the dropdown
        const status = $('#filter-status').val();
        $('.status-filter').removeClass('active')
            .filter('[data-status="' + status + '"]').addClass('active');

        renderActiveFilters();
        showLoading();
        table.ajax.reload(function() {
            setTimeout(hideLoading, 300);
        }, false);

        refreshCounts();
    });

    // ---- Reset button ----
    $('#filter-reset').on('click', function() {
        $('#invoice-filter-form')[0].reset();
        $('#filter-status').val('');
        $('.status-filter').removeClass('active')
            .filter('[data-status=""]').addClass('active');

        renderActiveFilters();
        showLoading();
        table.ajax.reload(function() {
            setTimeout(hideLoading, 300);
            refreshCounts();
        }, false);
    });

    // ---- Status badge buttons (shortcut) ----
    $('.status-filter').on('click', function() {
        const status = $(this).data('status') || '';

        $('.status-filter').removeClass('active');
        $(this).addClass('active');

        // sync the dropdown
        $('#filter-status').val(status);

        renderActiveFilters();
        showLoading();
        table.ajax.reload(function() {
            setTimeout(hideLoading, 300);
            refreshCounts();
        }, false);
    });

    // ---- Refresh the badge counts using current filters (ignore status) ----
    function refreshCounts() {
        $.ajax({
            url: "{{ route('admin.invoices.index') }}",
            type: 'GET',
            data: $.extend({ counts_only: true }, currentFilters(), { status: '' }),
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
    window.showToast = showToast;

    // Custom search with debounce
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

    // Status update buttons
    $(document).on('click', '.btn-status-update', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const button = $(this);
        const invoiceId = button.data('invoice-id');
        const targetStatus = button.data('target-status');

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

                    showLoading();
                    table.ajax.reload(function() {
                        setTimeout(hideLoading, 300);
                        refreshCounts();

                        if (response.data && response.data.invoice_number) {
                            showToast('info', 'Invoice Number', `New invoice number: ${response.data.invoice_number}`);
                        }

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

    // Delete confirmation
    $(document).on('submit', 'form.d-inline', function(e) {
        if (!confirm('Delete this invoice?')) {
            e.preventDefault();
            return false;
        }
    });

    console.log('Document ready — invoices filter panel ready');
});
</script>
@endsection