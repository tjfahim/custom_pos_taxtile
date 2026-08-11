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
                            <p class="mb-0 text-white">Here's your personal performance summary. You have created {{ $totalInvoices }} confirmed invoices in total.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@if($hasFullAccess)
    <!-- Summary Cards -->
    <div class="row">
        <!-- Today's Summary -->
        <div class="col-xl-6 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="stat-widget-one">
                        <div class="stat-icon dib">
                            <i class="fa fa-calendar-o text-primary border-primary"></i>
                        </div>
                        <div class="stat-content dib">
                            <div class="stat-text">Today's Summary</div>
                            <div class="stat-sub">Total Sell: {{ number_format(array_sum(array_column($todayData, 'revenue')) + $todayInhouse['revenue'], 0) }}  ({{ number_format(array_sum(array_column($todayData, 'quantity')) + $todayInhouse['quantity'], 0) }} )
                                | Total Parcel: {{ array_sum(array_column($todayData, 'invoices')) + $todayInhouse['invoices'] }}
                                | Price: ৳{{ number_format(array_sum(array_column($todayData, 'subtotal')) + $todayInhouse['subtotal'], 0) }} 
                                 | Delivery: ৳{{ number_format(array_sum(array_column($todayData, 'delivery')) + $todayInhouse['delivery'], 0) }} 
                                
                                | Paid: ৳{{ number_format(array_sum(array_column($todayData, 'paid')) + $todayInhouse['paid'], 0) }}</div>
                            
                            <hr>
                            
                            @foreach($todayData as $courier => $data)
                                <div class="stat-sub">
                                    <strong>{{ $courier }}</strong> --- 
                                    Qty: {{ number_format($data['quantity'], 0) }} | 
                                    Price: ৳{{ number_format($data['subtotal'] ?? 0, 0) }} | 
                                    Delivery: ৳{{ number_format($data['delivery'] ?? 0, 0) }} | 
                                    Revenue: ৳{{ number_format($data['revenue'], 0) }} | 
                                    Paid: ৳{{ number_format($data['paid'] ?? 0, 0) }} | 
                                    Parcels: {{ number_format($data['invoices'] ?? 0, 0) }}
                                </div>
                            @endforeach
                            
                            @if($todayInhouse['invoices'] > 0)
                            <hr>
                            <div class="stat-sub" style="color: #2ecc71; font-weight: bold;">
                                <strong>In House</strong> --- 
                                Qty: {{ number_format($todayInhouse['quantity'], 0) }} | 
                                Price: ৳{{ number_format($todayInhouse['subtotal'] ?? 0, 0) }} | 
                                Delivery: ৳{{ number_format($todayInhouse['delivery'] ?? 0, 0) }} | 
                                Revenue: ৳{{ number_format($todayInhouse['revenue'], 0) }} | 
                                Paid: ৳{{ number_format($todayInhouse['paid'] ?? 0, 0) }} | 
                                Parcels: {{ number_format($todayInhouse['invoices'] ?? 0, 0) }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- This Month's Summary -->
        <div class="col-xl-6 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="stat-widget-one">
                        <div class="stat-icon dib">
                            <i class="fa fa-calendar text-primary border-primary"></i>
                        </div>
                        <div class="stat-content dib">
                            <div class="stat-text">This Month's Summary</div>
                            <div class="stat-digit">{{ array_sum(array_column($monthData, 'invoices')) + $monthInhouse['invoices'] }}</div>
                            <div class="stat-sub">Total Qty: {{ number_format(array_sum(array_column($monthData, 'quantity')) + $monthInhouse['quantity'], 0) }} 
                                | Price: ৳{{ number_format(array_sum(array_column($monthData, 'subtotal')) + $monthInhouse['subtotal'], 0) }} 
                                | Delivery: ৳{{ number_format(array_sum(array_column($monthData, 'delivery')) + $monthInhouse['delivery'], 0) }} 
                                | Revenue: ৳{{ number_format(array_sum(array_column($monthData, 'revenue')) + $monthInhouse['revenue'], 0) }} 
                                | Paid: ৳{{ number_format(array_sum(array_column($monthData, 'paid')) + $monthInhouse['paid'], 0) }}</div>
                            
                            <hr>
                            
                            @foreach($monthData as $courier => $data)
                                <div class="stat-sub">
                                    <strong>{{ $courier }}</strong> --- 
                                    Qty: {{ number_format($data['quantity'], 0) }} | 
                                    Price: ৳{{ number_format($data['subtotal'] ?? 0, 0) }} | 
                                    Delivery: ৳{{ number_format($data['delivery'] ?? 0, 0) }} | 
                                    Revenue: ৳{{ number_format($data['revenue'], 0) }} | 
                                    Paid: ৳{{ number_format($data['paid'] ?? 0, 0) }} | 
                                    Parcels: {{ number_format($data['invoices'] ?? 0, 0) }}
                                </div>
                            @endforeach
                            
                            @if($monthInhouse['invoices'] > 0)
                            <hr>
                            <div class="stat-sub" style="color: #2ecc71; font-weight: bold;">
                                <strong>In House</strong> --- 
                                Qty: {{ number_format($monthInhouse['quantity'], 0) }} | 
                                Price: ৳{{ number_format($monthInhouse['subtotal'] ?? 0, 0) }} | 
                                Delivery: ৳{{ number_format($monthInhouse['delivery'] ?? 0, 0) }} | 
                                Revenue: ৳{{ number_format($monthInhouse['revenue'], 0) }} | 
                                Paid: ৳{{ number_format($monthInhouse['paid'] ?? 0, 0) }} | 
                                Parcels: {{ number_format($monthInhouse['invoices'] ?? 0, 0) }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($hasFullAccess)
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


    @if($hasFullAccess)
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
    @if($hasFullAccess)
    <!-- Attendance Summary Cards -->
    <div class="row">
        <!-- Today's Attendance Card -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <i class="fa fa-calendar-day mr-2"></i>
                    <strong>Today's Attendance - {{ $today->format('d M, Y') }}</strong>
                    @if($todayAttendanceSummary['is_friday'])
                        <span class="badge bg-warning text-dark ml-2">Friday</span>
                    @endif
                    <span class="float-right badge bg-light text-dark">
                        {{ $todayAttendanceSummary['attendance_percentage'] }}% Present
                    </span>
                </div>
                <div class="card-body">
                    <!-- Today's Stats with Staff Lists -->
                    <div class="row mb-3">
                        <div class="col-3">
                            <div class="text-center">
                                <h6 class="text-muted">Present</h6>
                                <h3 class="text-success">{{ $todayAttendanceSummary['present'] }}</h3>
                                @if(count($todayAttendanceSummary['present_staff']) > 0)
                                    <small class="text-muted">
                                        {{ implode(', ', array_slice($todayAttendanceSummary['present_staff'], 0, 3)) }}
                                        @if(count($todayAttendanceSummary['present_staff']) > 3)
                                            +{{ count($todayAttendanceSummary['present_staff']) - 3 }} more
                                        @endif
                                    </small>
                                @endif
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="text-center">
                                <h6 class="text-muted">Absent</h6>
                                <h3 class="text-danger">{{ $todayAttendanceSummary['absent'] }}</h3>
                                @if(count($todayAttendanceSummary['absent_staff']) > 0)
                                    <small class="text-muted">
                                        {{ implode(', ', array_slice($todayAttendanceSummary['absent_staff'], 0, 3)) }}
                                        @if(count($todayAttendanceSummary['absent_staff']) > 3)
                                            +{{ count($todayAttendanceSummary['absent_staff']) - 3 }} more
                                        @endif
                                    </small>
                                @endif
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="text-center">
                                <h6 class="text-muted">Late</h6>
                                <h3 class="text-warning">{{ $todayAttendanceSummary['late'] }}</h3>
                                @if(count($todayAttendanceSummary['late_staff']) > 0)
                                    <small class="text-muted">
                                        @foreach(array_slice($todayAttendanceSummary['late_staff'], 0, 3) as $lateStaff)
                                            {{ $lateStaff['name'] }} ({{ $lateStaff['late_minutes'] }}m)
                                            @if(!$loop->last), @endif
                                        @endforeach
                                        @if(count($todayAttendanceSummary['late_staff']) > 3)
                                            +{{ count($todayAttendanceSummary['late_staff']) - 3 }} more
                                        @endif
                                    </small>
                                @endif
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="text-center">
                                <h6 class="text-muted">On Leave</h6>
                                <h3 class="text-dark">{{ $todayAttendanceSummary['leave'] }}</h3>
                                @if(count($todayAttendanceSummary['leave_staff']) > 0)
                                    <small class="text-muted">
                                        {{ implode(', ', array_column(array_slice($todayAttendanceSummary['leave_staff'], 0, 3), 'name')) }}
                                        @if(count($todayAttendanceSummary['leave_staff']) > 3)
                                            +{{ count($todayAttendanceSummary['leave_staff']) - 3 }} more
                                        @endif
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Today's Full Attendance Table -->
                    <div class="table-responsive mt-3" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="table-dark" style="position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    <th>Staff</th>
                                    <th>In Time</th>
                                    <th>Out Time</th>
                                    <th>Late</th>
                                    <th>Status</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($todayAttendance as $staff)
                                    <tr class="{{ $staff['is_late'] ? 'table-warning' : '' }}">
                                        <td>
                                            <strong>{{ $staff['staff_name'] }}</strong>
                                            <br><small class="text-muted">{{ $staff['designation'] }}</small>
                                            @if($staff['on_leave'])
                                                <span class="badge badge-dark">Leave</span>
                                            @endif
                                            @if($staff['is_holiday'])
                                                <span class="badge badge-primary">Holiday</span>
                                            @endif
                                            @if($staff['is_friday'])
                                                <span class="badge badge-secondary">Fri</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($staff['in_time'] != '-')
                                                <span class="badge {{ $staff['is_late'] ? 'badge-warning' : 'badge-success' }}">
                                                    {{ $staff['in_time'] }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($staff['out_time'] != '-')
                                                <span class="badge badge-danger">{{ $staff['out_time'] }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($staff['is_late'])
                                                <span class="badge badge-warning">{{ $staff['late_display'] }}</span>
                                            @elseif($staff['in_time'] != '-')
                                                <span class="badge badge-success">On Time</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{!! $staff['status_badge'] !!}</td>
                                        <td>
                                            @if($staff['note'])
                                                <small>{{ $staff['note'] }}</small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No attendance records for today</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Attendance Card -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fa fa-calendar-alt mr-2"></i>
                    <strong>Monthly Attendance - {{ $monthlyAttendanceSummary['month_name'] }}</strong>
                    <span class="float-right badge bg-light text-dark">
                        {{ $monthlyAttendanceSummary['attendance_percentage'] }}% Overall
                    </span>
                </div>
                <div class="card-body">
                    <!-- Monthly Stats -->
                    <div class="row mb-3">
                        <div class="col-3">
                            <div class="text-center">
                                <h6 class="text-muted">Present</h6>
                                <h4 class="text-success">{{ $monthlyAttendanceSummary['total_present'] }}</h4>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="text-center">
                                <h6 class="text-muted">Absent</h6>
                                <h4 class="text-danger">{{ $monthlyAttendanceSummary['total_absent'] }}</h4>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="text-center">
                                <h6 class="text-muted">Late</h6>
                                <h4 class="text-warning">{{ $monthlyAttendanceSummary['total_late'] }}</h4>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="text-center">
                                <h6 class="text-muted">Leave</h6>
                                <h4 class="text-dark">{{ $monthlyAttendanceSummary['total_leave'] }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4">
                            <div class="text-center">
                                <h6 class="text-muted">Holiday</h6>
                                <h5 class="text-primary">{{ $monthlyAttendanceSummary['total_holiday'] }}</h5>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center">
                                <h6 class="text-muted">Friday</h6>
                                <h5 class="text-secondary">{{ $monthlyAttendanceSummary['total_friday'] }}</h5>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="text-center">
                                <h6 class="text-muted">Late Hours</h6>
                                <h5 class="text-danger">{{ $monthlyAttendanceSummary['total_late_hours'] }}h</h5>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Staff Performance List -->
                    <div class="mt-3">
                        <h6 class="text-primary"><i class="fa fa-users"></i> Staff Performance Summary</h6>
                        <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light" style="position: sticky; top: 0; z-index: 10;">
                                    <tr>
                                        <th>Staff</th>
                                        <th class="text-center">Days</th>
                                        <th class="text-center">Late</th>
                                        <th class="text-center">Leave</th>
                                        <th class="text-center">Late Min</th>
                                        <th class="text-center">%</th>
                                        <th class="text-center">Rating</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($monthlyAttendanceReport as $staff)
                                        <tr>
                                            <td>
                                                <strong>{{ $staff['staff_name'] }}</strong>
                                                <br><small class="text-muted">{{ $staff['designation'] }}</small>
                                            </td>
                                            <td class="text-center">{{ $staff['total_days'] }}</td>
                                            <td class="text-center">
                                                @if($staff['late'] > 0)
                                                    <span class="badge badge-warning">{{ $staff['late'] }}</span>
                                                @else
                                                    <span class="text-muted">0</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($staff['leave'] > 0)
                                                    <span class="badge badge-dark">{{ $staff['leave'] }}</span>
                                                @else
                                                    <span class="text-muted">0</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($staff['late_minutes'] > 0)
                                                    <span class="text-danger">{{ $staff['late_minutes'] }}m</span>
                                                @else
                                                    <span class="text-muted">0</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-{{ $staff['rating_class'] }}">
                                                    {{ $staff['attendance_percentage'] }}%
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-{{ $staff['rating_class'] }}">
                                                    {{ $staff['rating'] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Top & Poor Performers -->
                    <div class="row mt-2">
                        <div class="col-6">
                            <h6 class="text-success"><i class="fa fa-trophy"></i> Top Performers</h6>
                            @if($topPerformers->count() > 0)
                                @foreach($topPerformers as $performer)
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span>{{ $performer['staff_name'] }}</span>
                                        <span class="badge badge-success">{{ $performer['attendance_percentage'] }}%</span>
                                    </div>
                                @endforeach
                            @else
                                <small class="text-muted">No data available</small>
                            @endif
                        </div>
                        <div class="col-6">
                            <h6 class="text-danger"><i class="fa fa-exclamation-triangle"></i> Needs Improvement</h6>
                            @if($poorPerformers->count() > 0)
                                @foreach($poorPerformers as $performer)
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span>{{ $performer['staff_name'] }}</span>
                                        <span class="badge badge-danger">{{ $performer['attendance_percentage'] }}%</span>
                                    </div>
                                @endforeach
                            @else
                                <small class="text-muted">No data available</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Courier-wise Report (Your existing code) -->
    <div class="row mt-3">
        <div class="col-md-12">
            <!-- Your existing courier report here -->
        </div>
    </div>

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

 

    @if($hasFullAccess && isset($last10Days))
    <!-- Last 10 Days Breakdown - Updated from 7 to 10 days -->
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
                                    <th class="text-end">Revenue</th>
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

       <!-- Monthly Performance Chart (Jan - Dec) - Now shows confirmed invoices only -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">{{ $hasFullAccess ? 'Monthly Performance ' . date('Y') : 'My Monthly Performance (Confirmed) ' . date('Y') }}</strong>
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
   @if($hasFullAccess)
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
   @if($hasFullAccess)
    
      <!-- Monthly Courier-wise Report -->
<div class="row">
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



   @if($hasFullAccess)
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
 
    @if(!$hasFullAccess)
    <!-- User's Payment Status Summary -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <strong>My Payment Status (Confirmed Invoices)</strong>
                </div>
                <div class="card-body">
                    @if(isset($paymentStatusCounts) && count($paymentStatusCounts) > 0)
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <div class="p-3 border rounded bg-success bg-opacity-10">
                                <span class="badge bg-success">Paid</span>
                                <h4 class="mt-2">{{ $paymentStatusCounts['paid'] ?? 0 }}</h4>
                                <small>Invoices</small>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="p-3 border rounded bg-warning bg-opacity-10">
                                <span class="badge bg-warning">Partial</span>
                                <h4 class="mt-2">{{ $paymentStatusCounts['partial'] ?? 0 }}</h4>
                                <small>Invoices</small>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="p-3 border rounded bg-danger bg-opacity-10">
                                <span class="badge bg-danger">Unpaid</span>
                                <h4 class="mt-2">{{ $paymentStatusCounts['unpaid'] ?? 0 }}</h4>
                                <small>Invoices</small>
                            </div>
                        </div>
                    </div>
                    @else
                    <p class="text-center text-muted">No confirmed invoice data available</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <strong>My Financial Summary (Confirmed)</strong>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Total Price:</th>
                            <td class="text-end">৳{{ number_format($totalSubtotal ?? 0, 0) }}</td>
                        </tr>
                        <tr>
                            <th>Total Delivery:</th>
                            <td class="text-end">৳{{ number_format($totalDelivery ?? 0, 0) }}</td>
                        </tr>
                        <tr class="table-primary">
                            <th>Total Amount:</th>
                            <td class="text-end fw-bold">৳{{ number_format($totalRevenue ?? 0, 0) }}</td>
                        </tr>
                        <tr class="table-success">
                            <th>Total Paid:</th>
                            <td class="text-end fw-bold">৳{{ number_format($totalPaid ?? 0, 0) }}</td>
                        </tr>
                        <tr class="table-warning">
                            <th>Total Due:</th>
                            <td class="text-end fw-bold">৳{{ number_format($totalDue ?? 0, 0) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Status Charts Row (Only for Admin) -->
    @if($hasFullAccess && isset($invoiceStatusCounts))
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
    
    <!-- Tables Row -->
    <div class="row">
        @if($hasFullAccess)
        
        @else
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">My Performance Summary (Confirmed)</strong>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6">
                            <div class="text-center p-3">
                                <h5 class="text-primary">{{ number_format($totalInvoices) }}</h5>
                                <small>Total Invoices</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3">
                                <h5 class="text-success">{{ number_format($totalQuantity) }}</h5>
                                <small>Total Quantity</small>
                            </div>
                        </div>
            
                        <div class="col-6">
                            <div class="text-center p-3">
                                <h5 class="text-warning">{{ number_format($monthlyInvoices) }}</h5>
                                <small>This Month</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
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