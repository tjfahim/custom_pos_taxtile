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
                            <p class="mb-0 text-white">Here's your personal performance summary. You have created {{ $totalInvoices }} invoices in total.</p>
                        </div>
                        <div>
                         
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Summary Cards -->
    <div class="row">
        <!-- Today's Summary -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="stat-widget-one">
                        <div class="stat-icon dib">
                            <i class="	fa fa-calendar-o text-primary border-primary"></i>
                        </div>
                        <div class="stat-content dib">
                            <div class="stat-text">Today</div>
                            <div class="stat-digit">{{ $todayInvoices }}</div>
                            <div class="stat-sub">Revenue: ৳{{ number_format($todayRevenue, 2) }}</div>
                            <small class="text-success">Paid: ৳{{ number_format($todayPaid ?? 0, 2) }}</small><br>
                            <small class="text-danger">Due: ৳{{ number_format($todayDue ?? 0, 2) }}</small>
                            @if(isset($todayQuantity))
                            <small class="d-block text-muted">Qty: {{ number_format($todayQuantity) }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- This Week Summary (Last 7 Days) -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="stat-widget-one">
                        <div class="stat-icon dib">
                            <i class="fa fa-calendar text-success border-success"></i>
                        </div>
                        <div class="stat-content dib">
                            <div class="stat-text">This Week (7 days)</div>
                            <div class="stat-digit">{{ $weekInvoices }}</div>
                            <div class="stat-sub">Revenue: ৳{{ number_format($weekRevenue, 2) }}</div>
                            <small class="text-success">Paid: ৳{{ number_format($weekPaid ?? 0, 2) }}</small><br>
                            <small class="text-danger">Due: ৳{{ number_format($weekDue ?? 0, 2) }}</small>
                            @if(isset($weekQuantity))
                            <small class="d-block text-muted">Qty: {{ number_format($weekQuantity) }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- This Month Summary -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="stat-widget-one">
                        <div class="stat-icon dib">
                            <i class="fa fa-calendar-check-o text-warning border-warning"></i>
                        </div>
                        <div class="stat-content dib">
                            <div class="stat-text">This Month</div>
                            <div class="stat-digit">{{ $monthlyInvoices }}</div>
                            <div class="stat-sub">Revenue: ৳{{ number_format($monthlyRevenue, 2) }}</div>
                            <small class="text-success">Paid: ৳{{ number_format($monthlyPaid ?? 0, 2) }}</small><br>
                            <small class="text-danger">Due: ৳{{ number_format($monthlyDue ?? 0, 2) }}</small>
                            @if(isset($monthlyQuantity))
                            <small class="d-block text-muted">Qty: {{ number_format($monthlyQuantity) }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Summary -->
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="stat-widget-one">
                        <div class="stat-icon dib">
                            <i class="fa fa-money text-danger border-danger"></i>
                        </div>
                        <div class="stat-content dib">
                            <div class="stat-text">Total {{ $hasFullAccess ? 'Revenue' : 'My Performance' }}</div>
                            <div class="stat-digit">{{ $totalInvoices }}</div>
                            <div class="stat-sub">Amount: ৳{{ number_format($totalRevenue ?? $totalRevenue, 2) }}</div>
                            <small class="text-success">Paid: ৳{{ number_format($totalPaidAmount ?? $totalPaid ?? 0, 2) }}</small><br>
                            <small class="text-danger">Due: ৳{{ number_format($totalDueAmount ?? $totalDue ?? 0, 2) }}</small>
                            @if(isset($totalQuantity))
                            <small class="d-block text-muted">Total Qty: {{ number_format($totalQuantity) }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($hasFullAccess)
    <!-- Additional Stats Row for Admin -->
    <div class="row">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="stat-widget-one">
                        <div class="stat-icon dib">
                            <i class="fa fa-users text-info border-info"></i>
                        </div>
                        <div class="stat-content dib">
                            <div class="stat-text">Total Customers</div>
                            <div class="stat-digit">{{ number_format($totalCustomers) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="stat-widget-one">
                        <div class="stat-icon dib">
                            <i class="fa fa fa-file-archive-o text-secondary border-secondary"></i>
                        </div>
                        <div class="stat-content dib">
                            <div class="stat-text">Total Invoices</div>
                            <div class="stat-digit">{{ number_format($totalInvoices) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="stat-widget-one">
                        <div class="stat-icon dib">
                            <i class="fa fa fa-window-maximize text-primary border-primary"></i>
                        </div>
                        <div class="stat-content dib">
                            <div class="stat-text">Total Quantity</div>
                            <div class="stat-digit">{{ number_format($totalQuantity) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="card">
                <div class="card-body">
                    <div class="stat-widget-one">
                        <div class="stat-icon dib">
                            <i class="fa fa-truck text-warning border-warning"></i>
                        </div>
                        <div class="stat-content dib">
                            <div class="stat-text">This Year</div>
                            <div class="stat-digit">{{ number_format($yearlyInvoices) }}</div>
                            <div class="stat-sub">৳{{ number_format($yearlyRevenue, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Monthly Performance Chart (Jan - Dec) -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">{{ $hasFullAccess ? 'Monthly Performance ' . date('Y') : 'My Monthly Performance ' . date('Y') }}</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-primary">
                                <tr>
                                    <th>Month</th>
                                    <th class="text-center">Invoices</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-end">Delivery</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Due</th>
                                    <th class="text-center">Collection %</th>
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
                                        $collectionRate = $stats && $stats->total_revenue > 0 ? ($stats->total_paid / $stats->total_revenue) * 100 : 0;
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $monthName }}</strong></td>
                                        <td class="text-center">{{ $stats ? number_format($stats->total_invoices) : '0' }}</td>
                                        <td class="text-center">{{ $stats ? number_format($stats->total_quantity ?? 0) : '0' }}</td>
                                        <td class="text-end">৳{{ $stats ? number_format($stats->total_subtotal ?? 0, 2) : '0.00' }}</td>
                                        <td class="text-end">৳{{ $stats ? number_format($stats->total_delivery ?? 0, 2) : '0.00' }}</td>
                                        <td class="text-end">৳{{ $stats ? number_format($stats->total_revenue, 2) : '0.00' }}</td>
                                        <td class="text-end text-success">৳{{ $stats ? number_format($stats->total_paid, 2) : '0.00' }}</td>
                                        <td class="text-end text-danger">৳{{ $stats ? number_format($stats->total_due, 2) : '0.00' }}</td>
                                        <td class="text-center">
                                            @if($stats)
                                                <span class="badge bg-{{ $collectionRate >= 80 ? 'success' : ($collectionRate >= 50 ? 'warning' : 'danger') }}">
                                                    {{ number_format($collectionRate, 1) }}%
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">0%</span>
                                            @endif
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

    @if($hasFullAccess && isset($last7Days))
    <!-- Last 7 Days Breakdown -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">Last 7 Days Performance</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th class="text-center">Invoices</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Revenue</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($last7Days as $day)
                                <tr>
                                    <td>{{ $day['date'] }}</td>
                                    <td class="text-center">{{ $day['count'] }}</td>
                                    <td class="text-center">{{ number_format($day['quantity']) }}</td>
                                    <td class="text-end">৳{{ number_format($day['revenue'], 2) }}</td>
                                    <td class="text-end text-success">৳{{ number_format($day['paid'], 2) }}</td>
                                    <td class="text-end text-danger">৳{{ number_format($day['due'], 2) }}</td>
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

    @if($hasFullAccess)
    <!-- Top Creators Performance -->
    @if(isset($topCreators) && $topCreators->count() > 0)
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="fa fa-users me-1"></i>
                    <strong>Top 5 Creators by Performance</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Creator Name</th>
                                    <th>Email</th>
                                    <th class="text-center">Orders</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-end">Delivery</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Paid</th>
                                    <th class="text-end">Due</th>
                                    <th class="text-center">Collection %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topCreators as $index => $creator)
                                @php
                                    $collectionRate = $creator->total_amount > 0 ? ($creator->total_paid / $creator->total_amount) * 100 : 0;
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $creator->name }}</strong></td>
                                    <td>{{ $creator->email }}</td>
                                    <td class="text-center"><span class="badge bg-primary">{{ $creator->total_invoices }}</span></td>
                                    <td class="text-center"><span class="badge bg-info">{{ number_format($creator->total_quantity) }}</span></td>
                                    <td class="text-end">৳{{ number_format($creator->total_subtotal, 2) }}</td>
                                    <td class="text-end">৳{{ number_format($creator->total_delivery, 2) }}</td>
                                    <td class="text-end">৳{{ number_format($creator->total_amount, 2) }}</td>
                                    <td class="text-end text-success">৳{{ number_format($creator->total_paid, 2) }}</td>
                                    <td class="text-end text-danger">৳{{ number_format($creator->total_due, 2) }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $collectionRate >= 80 ? 'success' : ($collectionRate >= 50 ? 'warning' : 'danger') }}">
                                            {{ number_format($collectionRate, 1) }}%
                                        </span>
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
    @endif
    @endif

    @if(!$hasFullAccess)
    <!-- User's Payment Status Summary -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <strong>My Payment Status</strong>
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
                    <p class="text-center text-muted">No invoice data available</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <strong>My Financial Summary</strong>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Total Subtotal:</th>
                            <td class="text-end">৳{{ number_format($totalSubtotal ?? 0, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Total Delivery:</th>
                            <td class="text-end">৳{{ number_format($totalDelivery ?? 0, 2) }}</td>
                        </tr>
                        <tr class="table-primary">
                            <th>Total Amount:</th>
                            <td class="text-end fw-bold">৳{{ number_format($totalRevenue ?? 0, 2) }}</td>
                        </tr>
                        <tr class="table-success">
                            <th>Total Paid:</th>
                            <td class="text-end fw-bold">৳{{ number_format($totalPaid ?? 0, 2) }}</td>
                        </tr>
                        <tr class="table-warning">
                            <th>Total Due:</th>
                            <td class="text-end fw-bold">৳{{ number_format($totalDue ?? 0, 2) }}</td>
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
                    <h4 class="mb-3">Invoice Status</h4>
                    <canvas id="invoiceStatusChart" height="150"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-3">Payment Status</h4>
                    <canvas id="paymentStatusChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Tables Row -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">{{ $hasFullAccess ? 'Recent Invoices' : 'My Recent Invoices' }}</strong>
                    <a href="{{ route('admin.invoices.index') }}" class="float-right btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentInvoices as $invoice)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.invoices.show', $invoice->id) }}">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td>{{ $invoice->customer->name ?? 'N/A' }}</td>
                                <td>৳{{ number_format($invoice->total, 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ $invoice->payment_status == 'paid' ? 'success' : ($invoice->payment_status == 'partial' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($invoice->payment_status) }}
                                    </span>
                                </td>
                                <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        @if($hasFullAccess)
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">Top Customers</strong>
                    <a href="{{ route('admin.customers.index') }}" class="float-right btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Invoices</th>
                                <th>Quantity</th>
                                <th>Total Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topCustomers as $customer)
                            <tr>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->phone_number_1 }}</td>
                                <td class="text-center">{{ $customer->invoices_count ?? 0 }}</td>
                                <td class="text-center">{{ number_format($customer->total_quantity ?? 0) }}</td>
                                <td class="text-end">৳{{ number_format($customer->invoices_sum_total ?? 0, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @else
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">My Performance Summary</strong>
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
                                <h5 class="text-info">{{ number_format($weekInvoices) }}</h5>
                                <small>This Week</small>
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

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@if($hasFullAccess)
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Invoice Status Chart
    @if(isset($invoiceStatusCounts) && count($invoiceStatusCounts) > 0)
    const invoiceStatusData = {
        @foreach($invoiceStatusCounts as $status => $count)
            "{{ $status ?: 'Unknown' }}": {{ $count }},
        @endforeach
    };
    
    new Chart(document.getElementById('invoiceStatusChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(invoiceStatusData),
            datasets: [{
                data: Object.values(invoiceStatusData),
                backgroundColor: ['#36a2eb', '#ff6384', '#ffce56', '#4bc0c0', '#9966ff']
            }]
        },
        options: {
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    @endif
    
    // Payment Status Chart
    @if(isset($paymentStatusCounts) && count($paymentStatusCounts) > 0)
    const paymentStatusData = {
        @foreach($paymentStatusCounts as $status => $count)
            "{{ $status ?: 'Unknown' }}": {{ $count }},
        @endforeach
    };
    
    new Chart(document.getElementById('paymentStatusChart'), {
        type: 'pie',
        data: {
            labels: Object.keys(paymentStatusData),
            datasets: [{
                data: Object.values(paymentStatusData),
                backgroundColor: ['#4bc0c0', '#ffce56', '#ff6384', '#36a2eb']
            }]
        },
        options: {
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    @endif
});
</script>
@endif

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