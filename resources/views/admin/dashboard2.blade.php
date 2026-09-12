@extends('admin.layouts.master')

@section('main_content')

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title"><h1>Dashboard - Date Range Report</h1></div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right"><li class="active">Date Filter Report</li></ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">

    <!-- Filter Form -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fa fa-filter"></i> <strong>Select Date Range</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.dashboard2.filter') }}" method="POST" class="row align-items-end">
                        @csrf
                        <div class="col-md-4 mb-2">
                            <label>From Date</label>
                            <input type="date" name="from_date" class="form-control"
                                   value="{{ old('from_date', $fromDate ?? '') }}" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label>To Date</label>
                            <input type="date" name="to_date" class="form-control"
                                   value="{{ old('to_date', $toDate ?? '') }}" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Filter</button>
                            <a href="{{ route('admin.dashboard2') }}" class="btn btn-secondary"><i class="fa fa-times"></i> Reset</a>
                        </div>
                    </form>
                    @error('from_date') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                    @error('to_date') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    @if(!empty($hasData))

    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                <i class="fa fa-calendar"></i>
                @if($isSingleDay)
                    Showing data for <strong>{{ \Carbon\Carbon::parse($fromDate)->format('d M, Y') }}</strong> (Single Day)
                @else
                    Showing data from <strong>{{ \Carbon\Carbon::parse($fromDate)->format('d M, Y') }}</strong>
                    to <strong>{{ \Carbon\Carbon::parse($toDate)->format('d M, Y') }}</strong>
                @endif
            </div>
        </div>
    </div>

    <!-- Summary Card (Courier Colored - like Today's Summary) -->
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="stat-widget-one">
                        <div class="stat-content dib" style="width:100%;">
                            <div class="stat-text" style="font-size:22px; font-weight:800; color:#222; margin-bottom:8px;">
                                Range Summary
                            </div>

                            <div class="stat-sub" style="font-size:17px; font-weight:700; line-height:1.9; color:#222;">
                                <span style="color:#000;">Qty: <strong style="font-size:20px;">{{ number_format($rangeQuantity) }}</strong></span>
                                <span style="margin-left:10px; color:#555;">|</span>
                                <span style="color:#8e44ad;">Price: <strong style="font-size:20px;">৳{{ number_format($rangeSubtotal, 0) }}</strong></span>
                                <span style="margin-left:10px; color:#555;">|</span>
                                <span style="color:#e67e22;">Delivery: <strong style="font-size:20px;">৳{{ number_format($rangeDelivery, 0) }}</strong></span>
                                <span style="margin-left:10px; color:#555;">|</span>
                                <span>Total: <strong style="font-size:20px;">৳{{ number_format($rangeRevenue, 0) }}</strong></span>
                                <span style="margin-left:10px; color:#555;">|</span>
                                <span style="color:#27ae60;">Paid: <strong style="font-size:20px;">৳{{ number_format($rangePaid, 0) }}</strong></span>
                                <span style="margin-left:10px; color:#555;">|</span>
                                <span style="color:#34495e;">Parcel: <strong style="font-size:20px;">{{ number_format($rangeCount) }}</strong></span>
                            </div>

                            <hr style="margin:15px 0; border-top:2px solid #eee;">

                            @foreach($rangeData as $courier => $data)
                                @php
                                    $courierLower = strtolower($courier);
                                    if (strpos($courierLower, 'pathao') !== false) {
                                        $courierColor = '#e74c3c'; $courierBg = '#fff1f0';
                                    } elseif (strpos($courierLower, 'redx') !== false) {
                                        $courierColor = '#c0392b'; $courierBg = '#fff5f5';
                                    } else {
                                        $courierColor = '#3498db'; $courierBg = '#f0f8ff';
                                    }
                                @endphp
                                <div class="stat-sub" style="font-size:17px; font-weight:700; line-height:2; margin-bottom:8px; padding:8px 12px; background:{{ $courierBg }}; border-left:5px solid {{ $courierColor }}; border-radius:4px;">
                                    <strong style="color:{{ $courierColor }}; font-size:20px; font-weight:900;">{{ $courier }}</strong>
                                    <span style="color:#777;"> --- </span>
                                    <span>Qty: <strong style="font-size:19px;">{{ number_format($data['quantity'], 0) }}</strong></span>
                                    <span style="color:#aaa;"> | </span>
                                    <span style="color:#8e44ad;">Price: <strong style="font-size:19px;">৳{{ number_format($data['subtotal'], 0) }}</strong></span>
                                    <span style="color:#aaa;"> | </span>
                                    <span style="color:#e67e22;">Delivery: <strong style="font-size:19px;">৳{{ number_format($data['delivery'], 0) }}</strong></span>
                                    <span style="color:#aaa;"> | </span>
                                    <span style="color:#2c3e50;">Total: <strong style="font-size:20px;">৳{{ number_format($data['revenue'], 0) }}</strong></span>
                                    <span style="color:#aaa;"> | </span>
                                    <span style="color:#27ae60;">Paid: <strong style="font-size:19px;">৳{{ number_format($data['paid'], 0) }}</strong></span>
                                    <span style="color:#aaa;"> | </span>
                                    <span style="color:#34495e;">Parcels: <strong style="font-size:19px;">{{ number_format($data['invoices'], 0) }}</strong></span>
                                </div>
                            @endforeach

                            @if($rangeInhouse['invoices'] > 0)
                                <hr style="margin:12px 0; border-top:2px solid #eee;">
                                <div class="stat-sub" style="font-size:17px; font-weight:700; line-height:2; padding:8px 12px; background:#edfff4; border-left:5px solid #2ecc71; border-radius:4px;">
                                    <strong style="color:#27ae60; font-size:20px; font-weight:900;">In House</strong>
                                    <span style="color:#777;"> --- </span>
                                    <span>Qty: <strong style="font-size:19px;">{{ number_format($rangeInhouse['quantity'], 0) }}</strong></span>
                                    <span style="color:#aaa;"> | </span>
                                    <span style="color:#8e44ad;">Price: <strong style="font-size:19px;">৳{{ number_format($rangeInhouse['subtotal'], 0) }}</strong></span>
                                    <span style="color:#aaa;"> | </span>
                                    <span style="color:#e67e22;">Delivery: <strong style="font-size:19px;">৳{{ number_format($rangeInhouse['delivery'], 0) }}</strong></span>
                                    <span style="color:#aaa;"> | </span>
                                    <span style="color:#27ae60;">Total: <strong style="font-size:20px;">৳{{ number_format($rangeInhouse['revenue'], 0) }}</strong></span>
                                    <span style="color:#aaa;"> | </span>
                                    <span style="color:#16a085;">Paid: <strong style="font-size:19px;">৳{{ number_format($rangeInhouse['paid'], 0) }}</strong></span>
                                    <span style="color:#aaa;"> | </span>
                                    <span style="color:#34495e;">Parcels: <strong style="font-size:19px;">{{ number_format($rangeInhouse['invoices'], 0) }}</strong></span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Transaction Details -->
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-credit-card text-success"></i> Payments Transaction Details (Range)
                    <span class="badge bg-success float-right">{{ $rangePaidInvoices->count() }} payments</span>
                </div>
                <div class="card-body">
                    @if($creatorPaymentSummary->count() > 0)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <strong><i class="fa fa-users"></i> Payment Summary by Creator:</strong>
                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <strong>Total Payments:</strong>
                                        <span class="badge bg-primary">{{ $rangePaidInvoices->count() }}</span>
                                    </div>
                                    @foreach($creatorPaymentSummary as $summary)
                                    <div class="col-md-3">
                                        <strong>{{ $summary['creator_name'] }}:</strong>
                                        <span class="badge bg-success">{{ $summary['invoice_count'] }}</span>
                                        <span class="text-muted">(৳{{ number_format($summary['total_paid'], 0) }})</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div style="max-height: 450px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Invoice</th><th>Phone</th><th>Created By</th><th>Merchant Id</th>
                                    <th class="text-end">Price</th><th class="text-end">Delivery</th>
                                    <th class="text-end">Total</th><th class="text-end">Paid</th>
                                    <th class="text-end">Due</th><th class="text-center">Method</th><th class="text-center">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rangePaidInvoices->take(50) as $invoice)
                                <tr>
                                    <td><strong>#{{ $invoice->invoice_number ?? $invoice->id }}</strong></td>
                                    <td><strong>{{ $invoice->recipient_phone }}</strong></td>
                                    <td><span class="badge bg-info">{{ $invoice->creator ? $invoice->creator->name : 'N/A' }}</span></td>
                                    <td><span style="font-size:12px;">{{ $invoice->merchant_order_id ?? 'N/A' }}</span></td>
                                    <td class="text-end">৳{{ number_format($invoice->subtotal, 0) }}</td>
                                    <td class="text-end">৳{{ number_format($invoice->delivery_charge, 0) }}</td>
                                    <td class="text-end fw-bold">৳{{ number_format($invoice->total, 0) }}</td>
                                    <td class="text-end text-success fw-bold">৳{{ number_format($invoice->paid_amount, 0) }}</td>
                                    <td class="text-end text-danger">৳{{ number_format($invoice->due_amount, 0) }}</td>
                                    <td class="text-center">
                                        @if($invoice->payment_method)
                                            <span class="badge" style="background:#3498db;color:#fff;font-size:10px;">{{ $invoice->payment_method }}</span>
                                        @else <span class="text-muted">-</span> @endif
                                    </td>
                                    <td class="text-center">
                                        @if($invoice->payment_details)
                                            <span style="font-size:11px; color:#666;">{{ $invoice->payment_details }}</span>
                                        @else <span class="text-muted">-</span> @endif
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="11" class="text-center text-muted py-3">
                                    <i class="fa fa-inbox" style="font-size:20px; display:block; margin-bottom:5px;"></i>
                                    No payments in this range
                                </td></tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr style="border-top:2px solid #e9ecef; font-weight:bold; background:#f8f9fa;">
                                    <td colspan="4" class="text-end">TOTAL:</td>
                                    <td class="text-end">৳{{ number_format($rangePaidInvoices->sum('subtotal'), 0) }}</td>
                                    <td class="text-end">৳{{ number_format($rangePaidInvoices->sum('delivery_charge'), 0) }}</td>
                                    <td class="text-end">৳{{ number_format($rangePaidInvoices->sum('total'), 0) }}</td>
                                    <td class="text-end text-success">৳{{ number_format($rangePaidInvoices->sum('paid_amount'), 0) }}</td>
                                    <td class="text-end text-danger">৳{{ number_format($rangePaidInvoices->sum('due_amount'), 0) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @if($rangePaidInvoices->count() > 50)
                        <div class="text-center text-muted mt-2" style="font-size:12px;">
                            <i class="fa fa-chevron-down"></i> Showing 50 of {{ $rangePaidInvoices->count() }} payments
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Team Performance -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fa fa-users me-1"></i><strong>Team Members Performance</strong>
                    <span class="float-right badge bg-light text-dark">Range Performance</span>
                </div>
                <div class="card-body">
                    @if($teamPerformance->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th><th>Team Member</th><th>Email</th><th class="text-center">Memo</th>
                                    <th class="text-center">Quantity</th><th class="text-end">Price</th>
                                    <th class="text-end">Delivery</th><th class="text-end">Total</th>
                                    <th class="text-end">Paid</th><th class="text-end">Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($teamPerformance as $index => $member)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $member->name }}</strong>
                                        @if($index == 0)<span class="badge bg-warning ms-1"><i class="fa fa-trophy"></i> Top</span>@endif
                                    </td>
                                    <td>{{ $member->email }}</td>
                                    <td class="text-center"><span class="badge bg-primary">{{ $member->total_invoices }}</span></td>
                                    <td class="text-center"><span class="badge bg-info">{{ number_format($member->total_quantity) }}</span></td>
                                    <td class="text-end">৳{{ number_format($member->total_subtotal, 0) }}</td>
                                    <td class="text-end">৳{{ number_format($member->total_delivery, 0) }}</td>
                                    <td class="text-end fw-bold">৳{{ number_format($member->total_amount, 0) }}</td>
                                    <td class="text-end text-success">৳{{ number_format($member->total_paid, 0) }}</td>
                                    <td class="text-end text-danger">৳{{ number_format($member->total_due, 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-4"><i class="fa fa-info-circle fa-3x text-muted"></i><p class="text-muted mt-2">No team activity in this range</p></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Creator Performance -->
    @if($topCreators->count() > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fa fa-users me-1"></i><strong>Creators Performance</strong>
                    <span class="float-right badge bg-light text-dark">Range Performance</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th><th>Creator Name</th><th>Email</th><th class="text-center">Memo</th>
                                    <th class="text-center">Quantity</th><th class="text-end">Price</th>
                                    <th class="text-end">Delivery</th><th class="text-end">Total</th>
                                    <th class="text-end">Paid</th><th class="text-end">Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topCreators as $index => $creator)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $creator->name }}</strong></td>
                                    <td>{{ $creator->email }}</td>
                                    <td class="text-center"><span class="badge bg-primary">{{ $creator->total_invoices }}</span></td>
                                    <td class="text-center"><span class="badge bg-info">{{ number_format($creator->total_quantity) }}</span></td>
                                    <td class="text-end">৳{{ number_format($creator->total_subtotal, 0) }}</td>
                                    <td class="text-end">৳{{ number_format($creator->total_delivery, 0) }}</td>
                                    <td class="text-end">৳{{ number_format($creator->total_amount, 0) }}</td>
                                    <td class="text-end text-success">৳{{ number_format($creator->total_paid, 0) }}</td>
                                    <td class="text-end text-danger">৳{{ number_format($creator->total_due, 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Payment Methods -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fa fa-credit-card"></i> <strong>Payment Methods (Range)</strong>
                    <span class="float-right badge bg-light text-dark">
                        Total: {{ array_sum(array_column($rangePaymentMethods, 'transactions')) }} Transactions
                    </span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr><th>Payment Method</th><th class="text-center">Transactions</th><th class="text-end">Total Amount</th></tr>
                            </thead>
                            <tbody>
                                @php $methodLabels = ['bkash'=>'bKash (Merchant)','bkash_personal'=>'bKash (Personal)','bank_transfer'=>'Bank Transfer','cash'=>'Cash']; @endphp
                                @foreach($rangePaymentMethods as $method => $data)
                                    @if($data['transactions'] > 0)
                                    <tr>
                                        <td><span class="badge" style="background:#3498db;color:#fff;padding:6px 10px;">{{ $methodLabels[$method] ?? ucfirst(str_replace('_',' ',$method)) }}</span></td>
                                        <td class="text-center"><span class="badge bg-primary">{{ $data['transactions'] }}</span></td>
                                        <td class="text-end text-success fw-bold">৳{{ number_format($data['total_paid'], 0) }}</td>
                                    </tr>
                                    @endif
                                @endforeach
                                @if(array_sum(array_column($rangePaymentMethods, 'transactions')) == 0)
                                <tr><td colspan="3" class="text-center text-muted py-3"><i class="fa fa-inbox"></i> No payments in this range</td></tr>
                                @endif
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <th>TOTAL</th>
                                    <th class="text-center">{{ array_sum(array_column($rangePaymentMethods, 'transactions')) }}</th>
                                    <th class="text-end">৳{{ number_format(array_sum(array_column($rangePaymentMethods, 'total_paid')), 0) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Courier-wise Report -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fa fa-truck me-1"></i>
                    <strong>Courier-wise Report</strong>
                    <span class="float-right badge bg-light text-dark">
                        Total: {{ array_sum(array_column($courierReport, 'parcels')) + $inhouseReport['parcels'] }} Parcels
                    </span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th><th>Courier Name</th><th class="text-center">Parcels</th>
                                    <th class="text-center">Quantity</th><th class="text-end">Price</th>
                                    <th class="text-end">Delivery</th><th class="text-end">Total</th>
                                    <th class="text-end">Paid</th><th class="text-end">Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $counter = 1; @endphp
                                @foreach($courierReport as $courier => $data)
                                    @if($data['parcels'] > 0)
                                    <tr>
                                        <td>{{ $counter++ }}</td>
                                        <td><strong>{{ $courier }}</strong></td>
                                        <td class="text-center"><span class="badge bg-primary">{{ $data['parcels'] }}</span></td>
                                        <td class="text-center"><span class="badge bg-info">{{ number_format($data['quantity']) }}</span></td>
                                        <td class="text-end">৳{{ number_format($data['subtotal'], 0) }}</td>
                                        <td class="text-end">৳{{ number_format($data['delivery'], 0) }}</td>
                                        <td class="text-end fw-bold">৳{{ number_format($data['total'], 0) }}</td>
                                        <td class="text-end text-success fw-bold">৳{{ number_format($data['paid'], 0) }}</td>
                                        <td class="text-end text-danger fw-bold">৳{{ number_format($data['due'], 0) }}</td>
                                    </tr>
                                    @endif
                                @endforeach

                                @if($inhouseReport['parcels'] > 0)
                                <tr style="background-color:#f0f8ff; border-top:2px solid #007bff;">
                                    <td>{{ $counter++ }}</td>
                                    <td><strong style="color:#2ecc71;">🏠 In House</strong></td>
                                    <td class="text-center"><span class="badge bg-success">{{ $inhouseReport['parcels'] }}</span></td>
                                    <td class="text-center"><span class="badge bg-info">{{ number_format($inhouseReport['quantity']) }}</span></td>
                                    <td class="text-end">৳{{ number_format($inhouseReport['subtotal'], 0) }}</td>
                                    <td class="text-end">৳{{ number_format($inhouseReport['delivery'], 0) }}</td>
                                    <td class="text-end fw-bold">৳{{ number_format($inhouseReport['total'], 0) }}</td>
                                    <td class="text-end text-success fw-bold">৳{{ number_format($inhouseReport['paid'], 0) }}</td>
                                    <td class="text-end text-danger fw-bold">৳{{ number_format($inhouseReport['due'], 0) }}</td>
                                </tr>
                                @endif
                            </tbody>
                            <tfoot class="table-secondary">
                                @php
                                    $totalParcels = array_sum(array_column($courierReport,'parcels')) + $inhouseReport['parcels'];
                                    $totalQuantity = array_sum(array_column($courierReport,'quantity')) + $inhouseReport['quantity'];
                                    $totalSubtotal = array_sum(array_column($courierReport,'subtotal')) + $inhouseReport['subtotal'];
                                    $totalDelivery = array_sum(array_column($courierReport,'delivery')) + $inhouseReport['delivery'];
                                    $totalTotal = array_sum(array_column($courierReport,'total')) + $inhouseReport['total'];
                                    $totalPaid = array_sum(array_column($courierReport,'paid')) + $inhouseReport['paid'];
                                    $totalDue = array_sum(array_column($courierReport,'due')) + $inhouseReport['due'];
                                @endphp
                                <tr>
                                    <th colspan="2" class="text-end">GRAND TOTAL</th>
                                    <th class="text-center">{{ $totalParcels }}</th>
                                    <th class="text-center">{{ number_format($totalQuantity) }}</th>
                                    <th class="text-end">৳{{ number_format($totalSubtotal, 0) }}</th>
                                    <th class="text-end">৳{{ number_format($totalDelivery, 0) }}</th>
                                    <th class="text-end">৳{{ number_format($totalTotal, 0) }}</th>
                                    <th class="text-end text-success">৳{{ number_format($totalPaid, 0) }}</th>
                                    <th class="text-end text-danger">৳{{ number_format($totalDue, 0) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Day-wise Breakdown -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <strong>Day-wise Breakdown</strong>
                    <span class="float-right badge bg-info">{{ $dailyBreakdown->count() }} Day(s)</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th><th class="text-center">Invoices</th><th class="text-center">Quantity</th>
                                    <th class="text-end">Price</th><th class="text-end">Delivery</th>
                                    <th class="text-end">Total</th><th class="text-end">Paid</th><th class="text-end">Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dailyBreakdown as $day)
                                <tr>
                                    <td>{{ $day['date'] }}</td>
                                    <td class="text-center">{{ $day['count'] }}</td>
                                    <td class="text-center">{{ number_format($day['quantity']) }}</td>
                                    <td class="text-end">৳{{ number_format($day['subtotal'], 0) }}</td>
                                    <td class="text-end">৳{{ number_format($day['delivery'], 0) }}</td>
                                    <td class="text-end">৳{{ number_format($day['revenue'], 0) }}</td>
                                    <td class="text-end text-success">৳{{ number_format($day['paid'], 0) }}</td>
                                    <td class="text-end text-danger">৳{{ number_format($day['due'], 0) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <th>Total</th>
                                    <th class="text-center">{{ $dailyBreakdown->sum('count') }}</th>
                                    <th class="text-center">{{ number_format($dailyBreakdown->sum('quantity')) }}</th>
                                    <th class="text-end">৳{{ number_format($dailyBreakdown->sum('subtotal'), 0) }}</th>
                                    <th class="text-end">৳{{ number_format($dailyBreakdown->sum('delivery'), 0) }}</th>
                                    <th class="text-end">৳{{ number_format($dailyBreakdown->sum('revenue'), 0) }}</th>
                                    <th class="text-end text-success">৳{{ number_format($dailyBreakdown->sum('paid'), 0) }}</th>
                                    <th class="text-end text-danger">৳{{ number_format($dailyBreakdown->sum('due'), 0) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @else
        <div class="row">
            <div class="col-md-12">
                <div class="text-center text-muted py-5">
                    <i class="fa fa-calendar fa-3x"></i>
                    <p class="mt-2">Select a date range above and click Filter to see the report.</p>
                </div>
            </div>
        </div>
    @endif

</div>

<style>
    .stat-widget-one { padding: 15px 0; }
    .stat-widget-one .stat-content { display: inline-block; vertical-align: middle; width:100%; }
    .stat-widget-one .stat-text { font-size: 14px; color: #868e96; margin-bottom: 5px; }
    .stat-widget-one .stat-sub { font-size: 12px; font-weight: 500; color: #333; }
    .card { border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.08); margin-bottom: 30px; }
    .card-header { border-bottom: 1px solid #eee; background: #fff; }
    .table td { vertical-align: middle; }
</style>

@endsection