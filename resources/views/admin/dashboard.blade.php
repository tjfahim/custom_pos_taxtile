@extends('admin.layouts.master')

@section('main_content')

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>{{ $hasFullAccess ? 'Dashboard' : 'My Performance Dashboard' }}</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li class="active">{{ $hasFullAccess ? 'Dashboard' : 'My Dashboard' }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    
    @if(!$hasFullAccess)
    <!-- User Welcome Card -->
    <div class="row">
        <div class="col-md-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4><i class="fa fa-user-circle"></i> Welcome, {{ $user->name }}!</h4>
                            <p class="mb-0 text-white">Here's your personal performance summary. You have created {{ $totalInvoices ?? '0' }} confirmed invoices in total.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@if($adminhasFullAccess)
<!-- Summary Cards -->
<div class="row">
    <div class="col-xl-12 col-lg-12 col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="stat-widget-one">

                    <!-- Icon -->
                   

                    <div class="stat-content dib" style="width:calc(100% - 70px);">

                        <!-- Title -->
                        <div class="stat-text"
                             style="font-size:22px; font-weight:800; color:#222; margin-bottom:8px;">
                            Today's Summary
                        </div>

                        <!-- Total Summary -->
                        <div class="stat-sub"
                             style="font-size:17px; font-weight:700; line-height:1.9; color:#222;">

                            <span style="color:#000;">
                                Total Sell:
                                <strong style="font-size:20px;">
                                    {{ number_format(array_sum(array_column($todayData, 'revenue')) + $todayInhouse['revenue'], 0) }}
                                </strong>
                                (
                                <strong style="font-size:20px;">
                                    {{ number_format(array_sum(array_column($todayData, 'quantity')) + $todayInhouse['quantity'], 0) }}
                                </strong>
                                )
                            </span>

                            <span style="margin-left:10px; color:#555;">|</span>

                            <span style="color:#34495e;">
                                Total Parcel:
                                <strong style="font-size:20px;">
                                    {{ array_sum(array_column($todayData, 'invoices')) + $todayInhouse['invoices'] }}
                                </strong>
                            </span>

                            <span style="margin-left:10px; color:#555;">|</span>

                            <span style="color:#8e44ad;">
                                Price:
                                <strong style="font-size:20px;">
                                    ৳{{ number_format(array_sum(array_column($todayData, 'subtotal')) + $todayInhouse['subtotal'], 0) }}
                                </strong>
                            </span>

                            <span style="margin-left:10px; color:#555;">|</span>

                            <span style="color:#e67e22;">
                                Delivery:
                                <strong style="font-size:20px;">
                                    ৳{{ number_format(array_sum(array_column($todayData, 'delivery')) + $todayInhouse['delivery'], 0) }}
                                </strong>
                            </span>

                            <span style="margin-left:10px; color:#555;">|</span>

                            <span style="color:#27ae60;">
                                Paid:
                                <strong style="font-size:20px;">
                                    ৳{{ number_format(array_sum(array_column($todayData, 'paid')) + $todayInhouse['paid'], 0) }}
                                </strong>
                            </span>
                        </div>

                        <hr style="margin:15px 0; border-top:2px solid #eee;">

                        <!-- Courier Details -->
                        @foreach($todayData as $courier => $data)

                            @php
                                $courierLower = strtolower($courier);

                                if (strpos($courierLower, 'pathao') !== false) {
                                    $courierColor = '#e74c3c';
                                    $courierBg = '#fff1f0';
                                } elseif (strpos($courierLower, 'redx') !== false) {
                                    $courierColor = '#c0392b';
                                    $courierBg = '#fff5f5';
                                } else {
                                    $courierColor = '#3498db';
                                    $courierBg = '#f0f8ff';
                                }
                            @endphp

                            <div class="stat-sub"
                                 style="
                                    font-size:17px;
                                    font-weight:700;
                                    line-height:2;
                                    margin-bottom:8px;
                                    padding:8px 12px;
                                    background:{{ $courierBg }};
                                    border-left:5px solid {{ $courierColor }};
                                    border-radius:4px;
                                 ">

                                <strong style="
                                    color:{{ $courierColor }};
                                    font-size:20px;
                                    font-weight:900;
                                ">
                                    {{ $courier }}
                                </strong>

                                <span style="color:#777;"> --- </span>

                                <span style="color:#222;">
                                    Qty:
                                    <strong style="font-size:19px;">
                                        {{ number_format($data['quantity'], 0) }}
                                    </strong>
                                </span>

                                <span style="color:#aaa;"> | </span>

                                <span style="color:#8e44ad;">
                                    Price:
                                    <strong style="font-size:19px;">
                                        ৳{{ number_format($data['subtotal'] ?? 0, 0) }}
                                    </strong>
                                </span>

                                <span style="color:#aaa;"> | </span>

                                <span style="color:#e67e22;">
                                    Delivery:
                                    <strong style="font-size:19px;">
                                        ৳{{ number_format($data['delivery'] ?? 0, 0) }}
                                    </strong>
                                </span>

                                <span style="color:#aaa;"> | </span>

                                <span style="color:#2c3e50;">
                                    Total:
                                    <strong style="font-size:20px;">
                                        ৳{{ number_format($data['revenue'], 0) }}
                                    </strong>
                                </span>

                                <span style="color:#aaa;"> | </span>

                                <span style="color:#27ae60;">
                                    Paid:
                                    <strong style="font-size:19px;">
                                        ৳{{ number_format($data['paid'] ?? 0, 0) }}
                                    </strong>
                                </span>

                                <span style="color:#aaa;"> | </span>

                                <span style="color:#34495e;">
                                    Parcels:
                                    <strong style="font-size:19px;">
                                        {{ number_format($data['invoices'] ?? 0, 0) }}
                                    </strong>
                                </span>
                            </div>

                        @endforeach

                        <!-- In House -->
                        @if($todayInhouse['invoices'] > 0)

                            <hr style="margin:12px 0; border-top:2px solid #eee;">

                            <div class="stat-sub"
                                 style="
                                    font-size:17px;
                                    font-weight:700;
                                    line-height:2;
                                    padding:8px 12px;
                                    background:#edfff4;
                                    border-left:5px solid #2ecc71;
                                    border-radius:4px;
                                 ">

                                <strong style="
                                    color:#27ae60;
                                    font-size:20px;
                                    font-weight:900;
                                ">
                                    In House
                                </strong>

                                <span style="color:#777;"> --- </span>

                                <span style="color:#222;">
                                    Qty:
                                    <strong style="font-size:19px;">
                                        {{ number_format($todayInhouse['quantity'], 0) }}
                                    </strong>
                                </span>

                                <span style="color:#aaa;"> | </span>

                                <span style="color:#8e44ad;">
                                    Price:
                                    <strong style="font-size:19px;">
                                        ৳{{ number_format($todayInhouse['subtotal'] ?? 0, 0) }}
                                    </strong>
                                </span>

                                <span style="color:#aaa;"> | </span>

                                <span style="color:#e67e22;">
                                    Delivery:
                                    <strong style="font-size:19px;">
                                        ৳{{ number_format($todayInhouse['delivery'] ?? 0, 0) }}
                                    </strong>
                                </span>

                                <span style="color:#aaa;"> | </span>

                                <span style="color:#27ae60;">
                                    Total:
                                    <strong style="font-size:20px;">
                                        ৳{{ number_format($todayInhouse['revenue'], 0) }}
                                    </strong>
                                </span>

                                <span style="color:#aaa;"> | </span>

                                <span style="color:#16a085;">
                                    Paid:
                                    <strong style="font-size:19px;">
                                        ৳{{ number_format($todayInhouse['paid'] ?? 0, 0) }}
                                    </strong>
                                </span>

                                <span style="color:#aaa;"> | </span>

                                <span style="color:#34495e;">
                                    Parcels:
                                    <strong style="font-size:19px;">
                                        {{ number_format($todayInhouse['invoices'] ?? 0, 0) }}
                                    </strong>
                                </span>
                            </div>

                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

  

    @if($adminhasFullAccess)
    <!-- Today's Payments -->
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12">
            <div class="card">
                <div class="card-header">
                    <i class="fa fa-credit-card text-success"></i> Today's Payments Transaction Details
                    <span class="badge bg-success float-right">{{ $todayPaidInvoices->count() }} payments</span>
                </div>
                <div class="card-body">
                    <!-- Creator Summary -->
                    @if(isset($creatorPaymentSummary) && $creatorPaymentSummary->count() > 0)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <strong><i class="fa fa-users"></i> Payment Summary by Creator:</strong>
                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <strong>Total Payments:</strong> 
                                        <span class="badge bg-primary">{{ $todayPaidInvoices->count() }}</span>
                                    </div>
                                    @foreach($creatorPaymentSummary as $creatorId => $summary)
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
                                    <th>Invoice</th>
                                    <th>Phone</th>
                                    <th>Created By</th>
                                    <th>Merchant Id</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Delivery</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Due</th>
                                    <th class="text-center">Method</th>
                                    <th class="text-center">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($todayPaidInvoices->take(10) as $invoice)
                                <tr>
                                    <td>
                                        <strong>#{{ $invoice->invoice_number ?? $invoice->id }}</strong>
                                    </td>
                                    <td>
                                        <strong>{{ $invoice->recipient_phone }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $invoice->creator ? $invoice->creator->name : 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span style="font-size: 12px;">
                                            {{ $invoice->merchant_order_id ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="text-end">৳{{ number_format($invoice->subtotal, 0) }}</td>
                                    <td class="text-end">৳{{ number_format($invoice->delivery_charge, 0) }}</td>
                                    <td class="text-end fw-bold">৳{{ number_format($invoice->total, 0) }}</td>
                                    <td class="text-end text-success fw-bold">৳{{ number_format($invoice->paid_amount, 0) }}</td>
                                    <td class="text-end text-danger">৳{{ number_format($invoice->due_amount, 0) }}</td>
                                    <td class="text-center">
                                        @if($invoice->payment_method)
                                            <span class="badge" style="background: #3498db; color: #fff; font-size: 10px;">
                                                {{ $invoice->payment_method }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($invoice->payment_details)
                                            <span style="font-size: 11px; color: #666;">{{ $invoice->payment_details }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-3">
                                        <i class="fa fa-inbox" style="font-size: 20px; display: block; margin-bottom: 5px;"></i>
                                        No payments received today
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr style="border-top: 2px solid #e9ecef; font-weight: bold; background: #f8f9fa;">
                                    <td colspan="4" class="text-end">TOTAL:</td>
                                    <td class="text-end">৳{{ number_format($todayPaidInvoices->sum('subtotal'), 0) }}</td>
                                    <td class="text-end">৳{{ number_format($todayPaidInvoices->sum('delivery_charge'), 0) }}</td>
                                    <td class="text-end">৳{{ number_format($todayPaidInvoices->sum('total'), 0) }}</td>
                                    <td class="text-end text-success">৳{{ number_format($todayPaidInvoices->sum('paid_amount'), 0) }}</td>
                                    <td class="text-end text-danger">৳{{ number_format($todayPaidInvoices->sum('due_amount'), 0) }}</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @if($todayPaidInvoices->count() > 10)
                        <div class="text-center text-muted mt-2" style="font-size: 12px;">
                            <i class="fa fa-chevron-down"></i> Showing 10 of {{ $todayPaidInvoices->count() }} payments
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($adminhasFullAccess)
    <div class="row">
        <div class="col-md-12">
            <!-- Today's Team Performance -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fa fa-users me-1"></i>
                    <strong>Team Members Performance</strong>
                    <span class="float-right badge bg-light text-dark">Today's Performance</span>
                </div>
                <div class="card-body">
                    @if($todayPerformance->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Team Member</th>
                                        <th>Email</th>
                                        <th class="text-center">Memo</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Delivery</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">Paid</th>
                                        <th class="text-end">Due</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($todayPerformance as $index => $member)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $member->name }}</strong>
                                            @if($index == 0)
                                                <span class="badge bg-warning ms-1">
                                                    <i class="fa fa-trophy"></i> Top
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $member->email }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $member->total_invoices }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ number_format($member->total_quantity) }}</span>
                                        </td>
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
                        <div class="text-center py-4">
                            <i class="fa fa-info-circle fa-3x text-muted"></i>
                            <p class="text-muted mt-2">No team activity today</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
    @if($adminhasFullAccess)
    <!-- Payment Method Breakdown - Today & This Month -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <i class="fa fa-credit-card"></i>
                    <strong>Today's Payment Methods</strong>
                    <span class="float-right badge bg-light text-dark">
                        Total: {{ array_sum(array_column($todayPaymentMethods, 'transactions')) }} Transactions
                    </span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Payment Method</th>
                                    <th class="text-center">Transactions</th>
                                    <th class="text-end">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $methodLabels = [
                                    'bkash' => 'bKash (Merchant)',
                                    'bkash_personal' => 'bKash (Personal)',
                                    'bank_transfer' => 'Bank Transfer',
                                    'cash' => 'Cash'
                                ]; @endphp
                                
                                @foreach($todayPaymentMethods as $method => $data)
                                    @if($data['transactions'] > 0)
                                    <tr>
                                        <td>
                                            <span class="badge" style="background: #3498db; color: #fff; padding: 6px 10px;">
                                                {{ $methodLabels[$method] ?? ucfirst(str_replace('_', ' ', $method)) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $data['transactions'] }}</span>
                                        </td>
                                        <td class="text-end text-success fw-bold">
                                            ৳{{ number_format($data['total_paid'], 0) }}
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                                
                                @if(array_sum(array_column($todayPaymentMethods, 'transactions')) == 0)
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        <i class="fa fa-inbox"></i> No payments today
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <th>TOTAL</th>
                                    <th class="text-center">{{ array_sum(array_column($todayPaymentMethods, 'transactions')) }}</th>
                                    <th class="text-end">৳{{ number_format(array_sum(array_column($todayPaymentMethods, 'total_paid')), 0) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fa fa-calendar"></i>
                    <strong>This Month's Payment Methods</strong>
                    <span class="float-right badge bg-light text-dark">
                        Total: {{ array_sum(array_column($monthlyPaymentMethods, 'transactions')) }} Transactions
                    </span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Payment Method</th>
                                    <th class="text-center">Transactions</th>
                                    <th class="text-end">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $methodLabels = [
                                    'bkash' => 'bKash (Merchant)',
                                    'bkash_personal' => 'bKash (Personal)',
                                    'bank_transfer' => 'Bank Transfer',
                                    'cash' => 'Cash'
                                ]; @endphp
                                
                                @foreach($monthlyPaymentMethods as $method => $data)
                                    @if($data['transactions'] > 0)
                                    <tr>
                                        <td>
                                            <span class="badge" style="background: #3498db; color: #fff; padding: 6px 10px;">
                                                {{ $methodLabels[$method] ?? ucfirst(str_replace('_', ' ', $method)) }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $data['transactions'] }}</span>
                                        </td>
                                        <td class="text-end text-success fw-bold">
                                            ৳{{ number_format($data['total_paid'], 0) }}
                                        </td>
                                    </tr>
                                    @endif
                                @endforeach
                                
                                @if(array_sum(array_column($monthlyPaymentMethods, 'transactions')) == 0)
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">
                                        <i class="fa fa-inbox"></i> No payments this month
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                            <tfoot class="table-secondary">
                                <tr>
                                    <th>TOTAL</th>
                                    <th class="text-center">{{ array_sum(array_column($monthlyPaymentMethods, 'transactions')) }}</th>
                                    <th class="text-end">৳{{ number_format(array_sum(array_column($monthlyPaymentMethods, 'total_paid')), 0) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
 @if($adminhasFullAccess)
<!-- Monthly Attendance Calendar -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <strong class="card-title">
                    <i class="fa fa-calendar mr-2"></i>
                    Attendance - {{ $monthName }}
                </strong>
                <span class="float-right badge bg-info">
                    {{ isset($attendanceMatrix) && is_array($attendanceMatrix) ? count($attendanceMatrix) : 0 }} Staff Members
                </span>
            </div>
            <div class="card-body">
                @if(isset($attendanceMatrix) && is_array($attendanceMatrix) && count($attendanceMatrix) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm" id="attendanceTable">
                        <thead class="thead-light">
                            <tr>
                                <th style="min-width: 100px; position: sticky; left: 0; background: #343a40; z-index: 10;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>User</span>
                                    </div>
                                </th>
                                @for($day = 1; $day <= $daysInMonth; $day++)
                                    @php
                                        $date = Carbon\Carbon::create($attYear, $attMonth, $day);
                                        $isFriday = $date->isFriday();
                                        $isToday = $date->isToday();
                                        $dayOfWeek = $date->format('D');
                                    @endphp
                                    <th class="text-center {{ $isFriday ? 'table-secondary' : '' }} {{ $isToday ? 'table-primary' : '' }}" 
                                        style="min-width: 40px;">
                                        <div>
                                            <div>{{ $day }}</div>
                                          
                                        </div>
                                    </th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendanceMatrix as $userData)
                                <tr>
                                    <td style="position: sticky; left: 0; background: white; z-index: 5; min-width: 180px;">
    <div class="d-flex align-items-center">
        <div class="avatar-circle bg-info text-white mr-2" 
             style="width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; flex-shrink: 0;">
            {{ strtoupper(substr($userData['name'], 0, 2)) }}
        </div>
        <div>
            <div class="font-weight-bold">{{ $userData['name'] ?? 'Unknown' }}</div>
            <div style="font-size: 14px; line-height: 1.2;">
                @if($userData['late_count'] > 0 || $userData['absent_count'] > 0)
                    @if($userData['late_count'] > 0)
                        <span class="text-warning">Late: {{ $userData['late_count'] }}</span>
                    @endif
                    @if($userData['absent_count'] > 0)
                        @if($userData['late_count'] > 0)
                            <span class="text-muted"> | </span>
                        @endif
                        <span class="text-danger">Absent: {{ $userData['absent_count'] }}</span>
                    @endif
                @else
                    <span class="text-muted">No absences</span>
                @endif
            </div>
        </div>
    </div>
</td>
                                    
                                    @for($day = 1; $day <= $daysInMonth; $day++)
                                        @php
                                            $date = Carbon\Carbon::create($attYear, $attMonth, $day);
                                            $isFriday = $date->isFriday();
                                            $dayData = $userData['days'][$day] ?? null;
                                            $status = $dayData ? $dayData['status'] : null;
                                            $inTime = $dayData ? $dayData['in_time'] : null;
                                            $onLeave = $dayData ? $dayData['on_leave'] : false;
                                            $isHoliday = $dayData ? $dayData['is_govt_holiday'] : false;
                                            $attendanceId = $dayData ? $dayData['id'] : null;
                                        @endphp
                                        <td class="text-center attendance-cell {{ $isFriday ? 'bg-light' : '' }} {{ $isHoliday ? 'bg-warning bg-opacity-25' : '' }}"
                                            style="cursor: default;">
                                            @if($status)
                                                @if($status == 'leave')
                                                    <span class="badge badge-dark" title="On Leave">L</span>
                                                @elseif($status == 'holiday')
                                                    <span class="badge badge-primary" title="Holiday">H</span>
                                                @elseif($status == 'friday')
                                                    <span class="badge badge-secondary" title="Friday">F</span>
                                                @elseif($status == 'present')
                                                    <span class="badge badge-success" title="Present: {{ $inTime }}">
                                                        <i class="fa fa-check"></i> {{ $inTime ? Carbon\Carbon::parse($inTime)->format('h:i A') : '' }}
                                                    </span>
                                                @elseif($status == 'late')
                                                    <span class="badge badge-warning" title="Late: {{ $inTime }}">
                                                        <i class="fa fa-clock-o"></i> {{ $inTime ? Carbon\Carbon::parse($inTime)->format('h:i A') : '' }}
                                                    </span>
                                                @elseif($status == 'absent')
                                                    <span class="badge badge-danger" title="Absent">A</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <span class="badge badge-success">P = Present</span>
                    <span class="badge badge-warning">L = Late</span>
                    <span class="badge badge-danger">A = Absent</span>
                    <span class="badge badge-dark">Lv = Leave</span>
                    <span class="badge badge-primary">H = Holiday</span>
                    <span class="badge badge-secondary">F = Friday</span>
                    <span class="text-muted ml-3"><small>Late after 11:30 AM</small></span>
                </div>
                @else
                <div class="text-center py-4">
                    <i class="fa fa-inbox fa-3x text-muted"></i>
                    <p class="text-muted mt-2">No attendance data available for this month</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif
    @if(!$adminhasFullAccess)
 <div class="row">

    {{-- TODAY --}}
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <strong>
                    <i class="fa fa-calendar-day"></i>
                    Today's Summary
                </strong>
            </div>

            <div class="card-body">
                <div class="row">

                    <div class="col-6 mb-3">
                        <div class="text-center p-3 border rounded">
                            <h5 class="text-primary">
                                {{ number_format($todayInvoices ?? 0) }}
                            </h5>
                            <small>Invoices</small>
                        </div>
                    </div>

                    <div class="col-6 mb-3">
                        <div class="text-center p-3 border rounded">
                            <h5 class="text-success">
                                {{ number_format($todayQuantity ?? 0) }}
                            </h5>
                            <small>Quantity</small>
                        </div>
                    </div>

                    <div class="col-6 mb-3">
                        <div class="text-center p-3 border rounded">
                            <h5>
                                ৳{{ number_format($todaySubtotal ?? 0, 0) }}
                            </h5>
                            <small>Total Price</small>
                        </div>
                    </div>

                    <div class="col-6 mb-3">
                        <div class="text-center p-3 border rounded">
                            <h5 class="text-info">
                                ৳{{ number_format($todayDelivery ?? 0, 0) }}
                            </h5>
                            <small>Delivery</small>
                        </div>
                    </div>

                    <div class="col-12 mb-3">
                        <div class="text-center p-3 border rounded bg-light">
                            <h4 class="text-primary">
                                ৳{{ number_format($todayRevenue ?? 0, 0) }}
                            </h4>
                            <small>Total Amount</small>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="text-center p-3 border rounded">
                            <h5 class="text-success">
                                ৳{{ number_format($todayPaid ?? 0, 0) }}
                            </h5>
                            <small>Total Paid</small>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="text-center p-3 border rounded">
                            <h5 class="text-warning">
                                ৳{{ number_format($todayDue ?? 0, 0) }}
                            </h5>
                            <small>Total Due</small>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>


    {{-- THIS MONTH --}}
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <strong>
                    <i class="fa fa-calendar-alt"></i>
                    This Month's Summary
                </strong>
            </div>

            <div class="card-body">
                <div class="row">

                    <div class="col-6 mb-3">
                        <div class="text-center p-3 border rounded">
                            <h5 class="text-primary">
                                {{ number_format($monthlyInvoices ?? 0) }}
                            </h5>
                            <small>Invoices</small>
                        </div>
                    </div>

                    <div class="col-6 mb-3">
                        <div class="text-center p-3 border rounded">
                            <h5 class="text-success">
                                {{ number_format($monthlyQuantity ?? 0) }}
                            </h5>
                            <small>Quantity</small>
                        </div>
                    </div>

                    <div class="col-6 mb-3">
                        <div class="text-center p-3 border rounded">
                            <h5>
                                ৳{{ number_format($monthlySubtotal ?? 0, 0) }}
                            </h5>
                            <small>Total Price</small>
                        </div>
                    </div>

                    <div class="col-6 mb-3">
                        <div class="text-center p-3 border rounded">
                            <h5 class="text-info">
                                ৳{{ number_format($monthlyDelivery ?? 0, 0) }}
                            </h5>
                            <small>Delivery</small>
                        </div>
                    </div>

                    <div class="col-12 mb-3">
                        <div class="text-center p-3 border rounded bg-light">
                            <h4 class="text-success">
                                ৳{{ number_format($monthlyRevenue ?? 0, 0) }}
                            </h4>
                            <small>Total Amount</small>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="text-center p-3 border rounded">
                            <h5 class="text-success">
                                ৳{{ number_format($monthlyPaid ?? 0, 0) }}
                            </h5>
                            <small>Total Paid</small>
                        </div>
                    </div>

                    <div class="col-6">
                        <div class="text-center p-3 border rounded">
                            <h5 class="text-warning">
                                ৳{{ number_format($monthlyDue ?? 0, 0) }}
                            </h5>
                            <small>Total Due</small>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
    @endif

    <!-- Status Charts Row (Only for Admin) -->
    @if($adminhasFullAccess && isset($invoiceStatusCounts))
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Invoice Status (All Invoices)</h4>
                    <canvas id="invoiceStatusChart" height="150"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Payment Status (Confirmed Invoices)</h4>
                    <canvas id="paymentStatusChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif
    



    @if($adminhasFullAccess)
    @if(isset($topCreators) && $topCreators->count() > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fa fa-users me-1"></i>
                    <strong>Creators Performance </strong>
                    <span class="float-right badge bg-light text-dark">Today's Performance</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Creator Name</th>
                                    <th>Email</th>
                                    <th class="text-center">Memo</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Delivery</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Due</th>
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
    @endif

    @if($adminhasFullAccess && isset($last10Days))
    <!-- Last 10 Days Breakdown -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">Last 10 Days Performance </strong>
                    <span class="float-right badge bg-info">Paid vs Due Comparison</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th class="text-center">Invoices</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Delivery</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($last10Days as $day)
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
                            @php
                                $total10DaysRevenue = collect($last10Days)->sum('revenue');
                                $total10DaysPaid = collect($last10Days)->sum('paid');
                                $total10DaysDue = collect($last10Days)->sum('due');
                                $total10DaysSubtotal = collect($last10Days)->sum('subtotal');
                                $total10DaysDelivery = collect($last10Days)->sum('delivery');
                            @endphp
                            <tfoot class="table-info">
                                <tr>
                                    <th>10 Days Total</th>
                                    <th class="text-center">{{ collect($last10Days)->sum('count') }}</th>
                                    <th class="text-center">{{ number_format(collect($last10Days)->sum('quantity')) }}</th>
                                    <th class="text-end">৳{{ number_format($total10DaysSubtotal, 0) }}</th>
                                    <th class="text-end">৳{{ number_format($total10DaysDelivery, 0) }}</th>
                                    <th class="text-end">৳{{ number_format($total10DaysRevenue, 0) }}</th>
                                    <th class="text-end text-success">৳{{ number_format($total10DaysPaid, 0) }}</th>
                                    <th class="text-end text-danger">৳{{ number_format($total10DaysDue, 0) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @if($adminhasFullAccess)

    <!-- Monthly Performance Chart (Jan - Dec) -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">{{ $adminhasFullAccess ? 'Monthly Performance ' . date('Y') : 'My Monthly Performance (Confirmed) ' . date('Y') }}</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-primary">
                                <tr>
                                    <th>Month</th>
                                    <th class="text-center">Invoices</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Delivery</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                @endphp
                                @foreach($months as $index => $monthName)
                                    @php
                                        $monthNum = $index + 1;
                                        $stats = $monthlyStats[$monthNum] ?? null;
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $monthName }}</strong></td>
                                        <td class="text-center">{{ $stats ? number_format($stats->total_invoices) : '0' }}</td>
                                        <td class="text-center">{{ $stats ? number_format($stats->total_quantity ?? 0) : '0' }}</td>
                                        <td class="text-end">৳{{ $stats ? number_format($stats->total_subtotal ?? 0, 0) : '0' }}</td>
                                        <td class="text-end">৳{{ $stats ? number_format($stats->total_delivery ?? 0, 0) : '0' }}</td>
                                        <td class="text-end">৳{{ $stats ? number_format($stats->total_revenue, 0) : '0' }}</td>
                                        <td class="text-end text-success">৳{{ $stats ? number_format($stats->total_paid, 0) : '0' }}</td>
                                        <td class="text-end text-danger">৳{{ $stats ? number_format($stats->total_due, 0) : '0' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-secondary">
                                @php
                                    $yearTotal = collect($monthlyStats)->sum('total_revenue');
                                    $yearPaid = collect($monthlyStats)->sum('total_paid');
                                    $yearDue = collect($monthlyStats)->sum('total_due');
                                    $yearQuantity = collect($monthlyStats)->sum('total_quantity');
                                    $yearSubtotal = collect($monthlyStats)->sum('total_subtotal');
                                    $yearDelivery = collect($monthlyStats)->sum('total_delivery');
                                @endphp
                                <tr>
                                    <th>Year Total</th>
                                    <th class="text-center">{{ collect($monthlyStats)->sum('total_invoices') }}</th>
                                    <th class="text-center">{{ number_format($yearQuantity) }}</th>
                                    <th class="text-end">৳{{ number_format($yearSubtotal, 0) }}</th>
                                    <th class="text-end">৳{{ number_format($yearDelivery, 0) }}</th>
                                    <th class="text-end">৳{{ number_format($yearTotal, 0) }}</th>
                                    <th class="text-end text-success">৳{{ number_format($yearPaid, 0) }}</th>
                                    <th class="text-end text-danger">৳{{ number_format($yearDue, 0) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($adminhasFullAccess)
    @if(isset($topCreatorsMonth) && $topCreatorsMonth->count() > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fa fa-users me-1"></i>
                    <strong>Creators Performance Monthly</strong>
                    <span class="float-right badge bg-light text-dark">This Month Performance</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Creator Name</th>
                                    <th>Email</th>
                                    <th class="text-center">Memo</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Delivery</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topCreatorsMonth as $index => $creator)
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
    @endif

    @if($adminhasFullAccess)
    <div class="row">
        <div class="col-md-12">
            <!-- Monthly Team Performance -->
            <div class="card mt-4">
                <div class="card-header bg-success text-white">
                    <i class="fa fa-users me-1"></i>
                    <strong>Team Members Performance Monthly</strong>
                    <span class="float-right badge bg-light text-dark">This Month Performance</span>
                </div>
                <div class="card-body">
                    @if($monthPerformance->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Team Member</th>
                                        <th>Email</th>
                                        <th class="text-center">Memo</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Delivery</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">Paid</th>
                                        <th class="text-end">Due</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($monthPerformance as $index => $member)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $member->name }}</strong>
                                            @if($index == 0)
                                                <span class="badge bg-warning ms-1">
                                                    <i class="fa fa-trophy"></i> Top
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $member->email }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-primary">{{ $member->total_invoices }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ number_format($member->total_quantity) }}</span>
                                        </td>
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
                        <div class="text-center py-4">
                            <i class="fa fa-info-circle fa-3x text-muted"></i>
                            <p class="text-muted mt-2">No team activity this month</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
 
    @if($adminhasFullAccess)
    <!-- Monthly Courier-wise Report -->
    <div class="row mt-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fa fa-truck me-1"></i>
                    <strong>Monthly Courier-wise Report - {{ date('F Y') }}</strong>
                    <span class="float-right badge bg-light text-dark">Total: {{ array_sum(array_column($monthlyCourierReport, 'parcels')) + $monthlyInhouseReport['parcels'] }} Parcels</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Courier Name</th>
                                    <th class="text-center">Parcels</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Delivery</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $counter = 1; @endphp
                                
                                @foreach($monthlyCourierReport as $courier => $data)
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
                                
                                <!-- In-house Row -->
                                @if($monthlyInhouseReport['parcels'] > 0)
                                <tr style="background-color: #f0f8ff; border-top: 2px solid #007bff;">
                                    <td>{{ $counter++ }}</td>
                                    <td><strong style="color: #2ecc71;">🏠 In House</strong></td>
                                    <td class="text-center"><span class="badge bg-success">{{ $monthlyInhouseReport['parcels'] }}</span></td>
                                    <td class="text-center"><span class="badge bg-info">{{ number_format($monthlyInhouseReport['quantity']) }}</span></td>
                                    <td class="text-end">৳{{ number_format($monthlyInhouseReport['subtotal'], 0) }}</td>
                                    <td class="text-end">৳{{ number_format($monthlyInhouseReport['delivery'], 0) }}</td>
                                    <td class="text-end fw-bold">৳{{ number_format($monthlyInhouseReport['total'], 0) }}</td>
                                    <td class="text-end text-success fw-bold">৳{{ number_format($monthlyInhouseReport['paid'], 0) }}</td>
                                    <td class="text-end text-danger fw-bold">৳{{ number_format($monthlyInhouseReport['due'], 0) }}</td>
                                </tr>
                                @endif
                            </tbody>
                            <tfoot class="table-secondary">
                                @php
                                    $totalParcels = array_sum(array_column($monthlyCourierReport, 'parcels')) + $monthlyInhouseReport['parcels'];
                                    $totalQuantity = array_sum(array_column($monthlyCourierReport, 'quantity')) + $monthlyInhouseReport['quantity'];
                                    $totalSubtotal = array_sum(array_column($monthlyCourierReport, 'subtotal')) + $monthlyInhouseReport['subtotal'];
                                    $totalDelivery = array_sum(array_column($monthlyCourierReport, 'delivery')) + $monthlyInhouseReport['delivery'];
                                    $totalTotal = array_sum(array_column($monthlyCourierReport, 'total')) + $monthlyInhouseReport['total'];
                                    $totalPaid = array_sum(array_column($monthlyCourierReport, 'paid')) + $monthlyInhouseReport['paid'];
                                    $totalDue = array_sum(array_column($monthlyCourierReport, 'due')) + $monthlyInhouseReport['due'];
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
    @endif

</div>

<style>
    .stat-widget-one {
        padding: 15px 0;
    }
    .stat-widget-one .stat-icon {
        display: inline-block;
        width: 60px;
        height: 60px;
        line-height: 60px;
        text-align: center;
        border-radius: 50%;
        margin-right: 15px;
        font-size: 24px;
    }
    .stat-widget-one .stat-content {
        display: inline-block;
        vertical-align: middle;
    }
    .stat-widget-one .stat-text {
        font-size: 14px;
        color: #868e96;
        margin-bottom: 5px;
    }
    .stat-widget-one .stat-digit {
        font-size: 22px;
        font-weight: 600;
    }
    .stat-widget-one .stat-sub {
        font-size: 12px;
        font-weight: 500;
        color: #333;
    }
    .card {
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.08);
        margin-bottom: 30px;
    }
    .card-header {
        border-bottom: 1px solid #eee;
        background: #fff;
    }
    .badge-success {
        background-color: #28a745;
    }
    .badge-warning {
        background-color: #ffc107;
    }
    .badge-danger {
        background-color: #dc3545;
    }
    .badge-info {
        background-color: #17a2b8;
    }
    .table td {
        vertical-align: middle;
    }
</style>

@endsection