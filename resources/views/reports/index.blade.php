@extends('admin.layouts.master')

@section('main_content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4">Invoice Reports</h1>
        @if(isset($invoices) && $invoices->count() > 0)
        <div>
             @can('export reports')
            <form action="{{ route('admin.reports.export-csv') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="from_date" value="{{ $fromDate->format('Y-m-d') }}">
                <input type="hidden" name="to_date" value="{{ $toDate->format('Y-m-d') }}">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-download"></i> Export CSV
                </button>
            </form>
            <form action="{{ route('admin.reports.print') }}" method="POST" class="d-inline" target="_blank">
                @csrf
                <input type="hidden" name="from_date" value="{{ $fromDate->format('Y-m-d') }}">
                <input type="hidden" name="to_date" value="{{ $toDate->format('Y-m-d') }}">
                <button type="submit" class="btn btn-info">
                    <i class="fas fa-print"></i> Print Report
                </button>
            </form>
            @endcan
        </div>
        @endif
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Filter Report
        </div>
        <div class="card-body">
            <form action="{{ route('admin.reports.generate') }}" method="POST" id="reportForm">
                @csrf
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <label for="from_date" class="form-label">From Date</label>
                        <input type="date" class="form-control @error('from_date') is-invalid @enderror" 
                               id="from_date" name="from_date" 
                               value="{{ old('from_date', $fromDate->format('Y-m-d')) }}" 
                               required>
                        @error('from_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-5 mb-3">
                        <label for="to_date" class="form-label">To Date</label>
                        <input type="date" class="form-control @error('to_date') is-invalid @enderror" 
                               id="to_date" name="to_date" 
                               value="{{ old('to_date', $toDate->format('Y-m-d')) }}" 
                               required>
                        @error('to_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Generate Report
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(isset($invoices))
        @if($invoices->count() > 0)
            <!-- Summary Cards -->
            <div class="row">
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-primary text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-light">Total Invoices</h6>
                                    <h3 class="mb-0">{{ number_format($summary['total_invoices']) }}</h3>
                                </div>
                                <i class="fas fa-file-invoice fa-2x opacity-50"></i>
                            </div>
                        </div>
                        <div class="card-footer  bg-dark d-flex align-items-center justify-content-between">
                            <span>Period: {{ $fromDate->format('d M Y') }} - {{ $toDate->format('d M Y') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-success text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-light">Total Income</h6>
                                    <h3 class="mb-0">৳{{ number_format($summary['total_paid'], 2) }}</h3>
                                </div>
                                <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                            </div>
                            <small>Collected Amount</small>
                        </div>
                        <div class="card-footer  bg-dark">
                            Subtotal: ৳{{ number_format($summary['total_subtotal'], 2) }}
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-warning text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-light">Total Due</h6>
                                    <h3 class="mb-0">৳{{ number_format($summary['total_due'], 2) }}</h3>
                                </div>
                                <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                            </div>
                        </div>
                        <div class="card-footer bg-dark">
                            Uncollected Amount
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card bg-info text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-light">Total Quantity</h6>
                                    <h3 class="mb-0">{{ number_format($summary['total_quantity'] ?? 0) }}</h3>
                                </div>
                                <i class="fas fa-boxes fa-2x opacity-50"></i>
                            </div>
                        </div>
                        <div class="card-footer  bg-dark">
                            Total Weight: {{ number_format($summary['total_weight']) }}g
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="row mb-4">
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-chart-pie me-1"></i>
                            Financial Summary
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="50%">Total Subtotal (Product Value)</th>
                                        <td class="text-end">৳{{ number_format($summary['total_subtotal'], 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Total Delivery Charge</th>
                                        <td class="text-end">৳{{ number_format($summary['total_delivery_charge'], 2) }}</td>
                                    </tr>
                                    <tr class="table-primary">
                                        <th>Total Invoice Amount</th>
                                        <td class="text-end fw-bold">৳{{ number_format($summary['total_amount'], 2) }}</td>
                                    </tr>
                                    <tr class="table-success">
                                        <th>Total Paid Amount (Income)</th>
                                        <td class="text-end fw-bold">৳{{ number_format($summary['total_paid'], 2) }}</td>
                                    </tr>
                                    <tr class="table-warning">
                                        <th>Total Due Amount (Receivable)</th>
                                        <td class="text-end fw-bold">৳{{ number_format($summary['total_due'], 2) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Enhanced Payment Status Stats with Quantity -->
                <div class="col-xl-6">
                    <div class="card h-100">
                        <div class="card-header">
                            <i class="fas fa-credit-card me-1"></i>
                            Payment Status Breakdown
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="p-3 border rounded bg-success bg-opacity-10 h-100">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <span class="badge bg-success">Paid</span>
                                            <h5 class="text-success mb-0">{{ $paymentStatusStats['paid']['count'] }}</h5>
                                        </div>
                                        <small class="text-muted">Invoices</small>
                                        <hr class="my-2">
                                        <div class="mt-2">
                                            <div><strong>Quantity:</strong> {{ number_format($paymentStatusStats['paid']['quantity']) }}</div>
                                            <div><strong>Total:</strong> ৳{{ number_format($paymentStatusStats['paid']['total'], 2) }}</div>
                                            <div class="text-success"><strong>Paid:</strong> ৳{{ number_format($paymentStatusStats['paid']['paid'], 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="p-3 border rounded bg-warning bg-opacity-10 h-100">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <span class="badge bg-warning">Partial</span>
                                            <h5 class="text-warning mb-0">{{ $paymentStatusStats['partial']['count'] }}</h5>
                                        </div>
                                        <small class="text-muted">Invoices</small>
                                        <hr class="my-2">
                                        <div class="mt-2">
                                            <div><strong>Quantity:</strong> {{ number_format($paymentStatusStats['partial']['quantity']) }}</div>
                                            <div><strong>Total:</strong> ৳{{ number_format($paymentStatusStats['partial']['total'], 2) }}</div>
                                            <div class="text-success"><strong>Paid:</strong> ৳{{ number_format($paymentStatusStats['partial']['paid'], 2) }}</div>
                                            <div class="text-danger"><strong>Due:</strong> ৳{{ number_format($paymentStatusStats['partial']['due'], 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="p-3 border rounded bg-danger bg-opacity-10 h-100">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <span class="badge bg-danger">Unpaid</span>
                                            <h5 class="text-danger mb-0">{{ $paymentStatusStats['unpaid']['count'] }}</h5>
                                        </div>
                                        <small class="text-muted">Invoices</small>
                                        <hr class="my-2">
                                        <div class="mt-2">
                                            <div><strong>Quantity:</strong> {{ number_format($paymentStatusStats['unpaid']['quantity']) }}</div>
                                            <div><strong>Total:</strong> ৳{{ number_format($paymentStatusStats['unpaid']['total'], 2) }}</div>
                                            <div class="text-danger"><strong>Due:</strong> ৳{{ number_format($paymentStatusStats['unpaid']['due'], 2) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Invoice Status with Quantity -->
                            <div class="mt-4">
                                <h6>Invoice Status <span class="badge bg-secondary">{{ $invoices->count() }} Total</span></h6>
                                <div class="table-responsive mt-2">
                                    <table class="table table-sm table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Status</th>
                                                <th class="text-center">Invoices</th>
                                                <th class="text-center">Quantity</th>
                                                <th class="text-end">Total</th>
                                                <th class="text-end">Paid</th>
                                                <th class="text-end">Due</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($invoiceStatusStats as $status => $stats)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-{{ 
                                                        $status == 'delivered' ? 'success' : 
                                                        ($status == 'pending' ? 'warning' : 
                                                        ($status == 'cancelled' ? 'danger' : 'secondary')) 
                                                    }}">
                                                        {{ ucfirst($status) }}
                                                    </span>
                                                </td>
                                                <td class="text-center">{{ $stats['count'] }}</td>
                                                <td class="text-center">{{ number_format($stats['quantity']) }}</td>
                                                <td class="text-end">৳{{ number_format($stats['total'], 2) }}</td>
                                                <td class="text-end text-success">৳{{ number_format($stats['paid'], 2) }}</td>
                                                <td class="text-end text-danger">৳{{ number_format($stats['due'], 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daily Summary with Quantity -->
            @if($dailySummary->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-calendar-alt me-1"></i>
                    Daily Summary
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th class="text-center">Invoices</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Total Amount</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dailySummary as $day)
                                <tr>
                                    <td>{{ $day['date'] }}</td>
                                    <td class="text-center">{{ $day['count'] }}</td>
                                    <td class="text-center">{{ number_format($day['quantity'] ?? 0) }}</td>
                                    <td class="text-end">৳{{ number_format($day['total'], 2) }}</td>
                                    <td class="text-end text-success">৳{{ number_format($day['paid'], 2) }}</td>
                                    <td class="text-end text-danger">৳{{ number_format($day['due'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- User Performance Summary -->
            @if(isset($createdByStats) && $createdByStats->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-users me-1"></i>
                    User Performance Summary (Order Creators)
                </div>
                <div class="card-body">
                    <!-- User Summary Totals -->
                    @if(isset($userSummary))
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <div class="row">
                                    <div class="col-md-2 col-sm-6">
                                        <small>Total Creators</small>
                                        <h5>{{ $userSummary['total_creators'] }}</h5>
                                    </div>
                                    <div class="col-md-2 col-sm-6">
                                        <small>Orders Created</small>
                                        <h5>{{ number_format($userSummary['total_orders_created']) }}</h5>
                                    </div>
                                    <div class="col-md-2 col-sm-6">
                                        <small>Total Quantity</small>
                                        <h5>{{ number_format($userSummary['total_quantity_created']) }}</h5>
                                    </div>
                                    <div class="col-md-2 col-sm-6">
                                        <small>Subtotal</small>
                                        <h5>৳{{ number_format($userSummary['total_subtotal_created'], 2) }}</h5>
                                    </div>
                                    <div class="col-md-2 col-sm-6">
                                        <small>Delivery Charge</small>
                                        <h5>৳{{ number_format($userSummary['total_delivery_created'], 2) }}</h5>
                                    </div>
                                    <div class="col-md-2 col-sm-6">
                                        <small>Total Amount</small>
                                        <h5>৳{{ number_format($userSummary['total_amount_created'], 2) }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>User Name</th>
                                    <th>Email</th>
                                    <th class="text-center">Orders</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-end">Delivery</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Due</th>
                                    <th class="text-end">Avg Order</th>
                                    <th class="text-center">Avg Qty/Order</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($createdByStats as $index => $user)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $user['name'] }}</strong>
                                    </td>
                                    <td>{{ $user['email'] }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $user['count'] }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ number_format($user['quantity']) }}</span>
                                    </td>
                                    <td class="text-end">৳{{ number_format($user['subtotal'], 2) }}</td>
                                    <td class="text-end">৳{{ number_format($user['delivery_charge'], 2) }}</td>
                                    <td class="text-end">৳{{ number_format($user['total'], 2) }}</td>
                                    <td class="text-end text-success">৳{{ number_format($user['paid'], 2) }}</td>
                                    <td class="text-end text-danger">৳{{ number_format($user['due'], 2) }}</td>
                                    <td class="text-end">৳{{ number_format($user['avg_order_value'], 2) }}</td>
                                    <td class="text-center">{{ number_format($user['avg_quantity_per_order'], 1) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            @if(isset($userSummary))
                            <tfoot class="table-secondary fw-bold">
                                <tr>
                                    <th colspan="3" class="text-end">Totals:</th>
                                    <th class="text-center">{{ number_format($userSummary['total_orders_created']) }}</th>
                                    <th class="text-center">{{ number_format($userSummary['total_quantity_created']) }}</th>
                                    <th class="text-end">৳{{ number_format($userSummary['total_subtotal_created'], 2) }}</th>
                                    <th class="text-end">৳{{ number_format($userSummary['total_delivery_created'], 2) }}</th>
                                    <th class="text-end">৳{{ number_format($userSummary['total_amount_created'], 2) }}</th>
                                    <th class="text-end text-success">৳{{ number_format($userSummary['total_paid_created'], 2) }}</th>
                                    <th class="text-end text-danger">৳{{ number_format($userSummary['total_due_created'], 2) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Top Customers with Quantity -->
            @if($topCustomers->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-crown me-1"></i>
                    Top 10 Customers by Value
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Customer Name</th>
                                    <th>Phone</th>
                                    <th class="text-center">Invoices</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Total Spent</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topCustomers as $index => $customer)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $customer['name'] }}</td>
                                    <td>{{ $customer['phone'] }}</td>
                                    <td class="text-center">{{ $customer['invoice_count'] }}</td>
                                    <td class="text-center">{{ number_format($customer['quantity'] ?? 0) }}</td>
                                    <td class="text-end">৳{{ number_format($customer['total_spent'], 2) }}</td>
                                    <td class="text-end text-success">৳{{ number_format($customer['total_paid'], 2) }}</td>
                                    <td class="text-end text-danger">৳{{ number_format($customer['total_due'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

          <!-- Delivery Area Breakdown with Enhanced Metrics -->
@if($deliveryAreaStats->count() > 0)
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-map-marker-alt me-1"></i>
                Delivery Area Summary
                <span class="badge bg-secondary ms-2">{{ $deliveryAreaStats->count() }} total areas</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover" id="deliveryAreaTable">
                        <thead class="table-light">
                            <tr>
                                <th>Area</th>
                                <th class="text-center">Orders</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Delivery Charge</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deliveryAreaStats as $area => $stats)
                            <tr>
                                <td>{{ $area ?? 'N/A' }}</td>
                                <td class="text-center">{{ $stats['count'] }}</td>
                                <td class="text-center">{{ number_format($stats['quantity'] ?? 0) }}</td>
                                <td class="text-end">৳{{ number_format($stats['delivery_charge'], 2) }}</td>
                                <td class="text-end">৳{{ number_format($stats['total'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($deliveryAreaStats->count() > 10)
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <span class="text-muted">
                            Showing <span id="areaStartRecord">1</span> 
                            to <span id="areaEndRecord">10</span> 
                            of <span id="areaTotalRecords">{{ $deliveryAreaStats->count() }}</span> areas
                        </span>
                    </div>
                    <nav aria-label="Delivery area pagination">
                        <ul class="pagination pagination-sm mb-0" id="areaPagination">
                            <li class="page-item" id="areaPrevPage">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                            </li>
                            <li class="page-item active" aria-current="page">
                                <span class="page-link">1</span>
                            </li>
                            <li class="page-item" id="areaNextPage">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

            <!-- Invoice Details Table -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    Invoice Details ({{ $invoices->count() }} records)
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="invoiceTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Recipient</th>
                                    <th>Area</th>
                                    <th>Items (Qty)</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-end">Delivery</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Due</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoices as $invoice)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="text-decoration-none">
                                            {{ $invoice->invoice_number }}
                                        </a>
                                    </td>
                                    <td>{{ $invoice->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        {{ $invoice->customer->name ?? $invoice->recipient_name }}
                                        <br>
                                        <small>{{ $invoice->customer->phone_number_1 ?? $invoice->recipient_phone }}</small>
                                    </td>
                                    <td>
                                        {{ $invoice->recipient_name }}
                                        <br>
                                        <small>{{ $invoice->recipient_phone }}</small>
                                    </td>
                                    <td>{{ $invoice->delivery_area }}</td>
                                    <td>
                                        @foreach($invoice->items as $item)
                                            <div>{{ $item->item_name }} (x{{ $item->quantity }})</div>
                                        @endforeach
                                        <span class="badge bg-info mt-1">
                                            Total: {{ $invoice->items->sum('quantity') }} items
                                        </span>
                                    </td>
                                    <td class="text-end">৳{{ number_format($invoice->subtotal, 2) }}</td>
                                    <td class="text-end">৳{{ number_format($invoice->delivery_charge, 2) }}</td>
                                    <td class="text-end">৳{{ number_format($invoice->total, 2) }}</td>
                                    <td class="text-end text-success">৳{{ number_format($invoice->paid_amount, 2) }}</td>
                                    <td class="text-end text-danger">৳{{ number_format($invoice->due_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $invoice->payment_status == 'paid' ? 'success' : 
                                            ($invoice->payment_status == 'partial' ? 'warning' : 'danger') 
                                        }}">
                                            {{ ucfirst($invoice->payment_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ 
                                            $invoice->status == 'delivered' ? 'success' : 
                                            ($invoice->status == 'pending' ? 'warning' : 
                                            ($invoice->status == 'cancelled' ? 'danger' : 'secondary')) 
                                        }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $invoice->creator->name ?? 'N/A' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-secondary fw-bold">
                                <tr>
                                    <td colspan="6" class="text-end">Totals:</td>
                                    <td class="text-end">৳{{ number_format($summary['total_subtotal'], 2) }}</td>
                                    <td class="text-end">৳{{ number_format($summary['total_delivery_charge'], 2) }}</td>
                                    <td class="text-end">৳{{ number_format($summary['total_amount'], 2) }}</td>
                                    <td class="text-end text-success">৳{{ number_format($summary['total_paid'], 2) }}</td>
                                    <td class="text-end text-danger">৳{{ number_format($summary['total_due'], 2) }}</td>
                                    <td colspan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No invoices found for the selected date range ({{ $fromDate->format('d M Y') }} - {{ $toDate->format('d M Y') }}).
            </div>
        @endif
    @else
        <div class="alert alert-primary">
            <i class="fas fa-info-circle"></i> Please select a date range and click "Generate Report" to view the invoice report.
        </div>
    @endif
</div>

    <script>
$(document).ready(function() {
    // Destroy existing DataTable instance if it exists before reinitializing
    if ($.fn.DataTable && $('#invoiceTable').length) {
        if ($.fn.DataTable.isDataTable('#invoiceTable')) {
            $('#invoiceTable').DataTable().destroy();
        }
        
        $('#invoiceTable').DataTable({
            pageLength: 25,
            ordering: true,
            responsive: true,
            order: [[1, 'desc']],
            destroy: true // This also helps prevent reinitialization errors
        });
    }

    // Date validation
    $('#reportForm').submit(function(e) {
        var fromDate = new Date($('#from_date').val());
        var toDate = new Date($('#to_date').val());
        
        if (toDate < fromDate) {
            e.preventDefault();
            alert('To date must be greater than or equal to from date');
        }
    });
    
    // Delivery Area Pagination - Only run if table exists and not using DataTables
    @if(isset($deliveryAreaStats) && $deliveryAreaStats->count() > 10)
    // Check if we're not using DataTables for delivery area
    if (!$('#deliveryAreaTable').hasClass('dataTable')) {
        const rowsPerPage = 10;
        const $table = $('#deliveryAreaTable');
        const $rows = $table.find('tbody tr');
        const totalRows = $rows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        let currentPage = 1;
        
        // Hide all rows initially
        $rows.hide();
        
        // Function to update pagination UI
        function updatePaginationUI(page) {
            // Remove old page items except prev/next
            $('#areaPagination li:not(#areaPrevPage, #areaNextPage)').remove();
            
            // Generate page numbers
            let startPage = Math.max(1, page - 2);
            let endPage = Math.min(totalPages, page + 2);
            
            // First page
            if (startPage > 1) {
                $('#areaPagination #areaPrevPage').after(
                    '<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>'
                );
                if (startPage > 2) {
                    $('#areaPagination #areaPrevPage').after(
                        '<li class="page-item disabled"><span class="page-link">...</span></li>'
                    );
                }
            }
            
            // Page numbers
            for (let i = startPage; i <= endPage; i++) {
                const activeClass = i === page ? 'active' : '';
                const ariaCurrent = i === page ? 'page' : '';
                $('#areaPagination #areaPrevPage').after(
                    `<li class="page-item ${activeClass}" aria-current="${ariaCurrent}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>`
                );
            }
            
            // Last page
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    $('#areaPagination #areaPrevPage').after(
                        '<li class="page-item disabled"><span class="page-link">...</span></li>'
                    );
                }
                $('#areaPagination #areaPrevPage').after(
                    `<li class="page-item">
                        <a class="page-link" href="#" data-page="${totalPages}">${totalPages}</a>
                    </li>`
                );
            }
            
            // Update prev/next buttons state
            if (page === 1) {
                $('#areaPrevPage').addClass('disabled');
                $('#areaPrevPage a').attr('tabindex', '-1').attr('aria-disabled', 'true');
            } else {
                $('#areaPrevPage').removeClass('disabled');
                $('#areaPrevPage a').removeAttr('tabindex aria-disabled');
            }
            
            if (page === totalPages) {
                $('#areaNextPage').addClass('disabled');
                $('#areaNextPage a').attr('tabindex', '-1').attr('aria-disabled', 'true');
            } else {
                $('#areaNextPage').removeClass('disabled');
                $('#areaNextPage a').removeAttr('tabindex aria-disabled');
            }
        }
        
        // Display rows for current page
        function displayRows(page) {
            currentPage = page;
            const start = (page - 1) * rowsPerPage;
            const end = Math.min(start + rowsPerPage, totalRows);
            
            $rows.hide().slice(start, end).show();
            
            // Update pagination info
            $('#areaStartRecord').text(totalRows > 0 ? start + 1 : 0);
            $('#areaEndRecord').text(end);
            $('#areaTotalRecords').text(totalRows);
            
            // Update pagination UI
            updatePaginationUI(page);
        }
        
        // Initialize pagination
        if (totalRows > 0) {
            displayRows(1);
            
            // Pagination click handler
            $(document).off('click', '#areaPagination a.page-link').on('click', '#areaPagination a.page-link', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                if (page && !$(this).parent().hasClass('disabled')) {
                    displayRows(parseInt(page));
                }
            });
            
            // Previous button
            $('#areaPrevPage a').off('click').on('click', function(e) {
                e.preventDefault();
                if (currentPage > 1 && !$('#areaPrevPage').hasClass('disabled')) {
                    displayRows(currentPage - 1);
                }
            });
            
            // Next button
            $('#areaNextPage a').off('click').on('click', function(e) {
                e.preventDefault();
                if (currentPage < totalPages && !$('#areaNextPage').hasClass('disabled')) {
                    displayRows(currentPage + 1);
                }
            });
        }
    }
    @endif
});
</script>
@endsection