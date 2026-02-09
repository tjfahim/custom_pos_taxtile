@extends('admin.layouts.master')

@section('main_content')

<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1>Dashboard</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li class="active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <!-- Summary Cards -->
    <div class="row">
        <!-- Today's Summary -->
        <div class="col-xl-3 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div class="stat-widget-one">
                        <div class="stat-icon dib">
                            <i class="fa fa-calendar-day text-primary border-primary"></i>
                        </div>
                        <div class="stat-content dib">
                            <div class="stat-text">Today's Summary</div>
                            <div class="stat-digit">{{ $todayInvoices }}</div>
                            <div class="stat-text">Revenue: ৳{{ number_format($todayRevenue, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- This Week Summary -->
        <div class="col-xl-3 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div class="stat-widget-one">
                        <div class="stat-icon dib">
                            <i class="fa fa-calendar-week text-success border-success"></i>
                        </div>
                        <div class="stat-content dib">
                            <div class="stat-text">This Week</div>
                            <div class="stat-digit">{{ $weekInvoices }}</div>
                            <div class="stat-text">Revenue: ৳{{ number_format($weekRevenue, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- This Month Summary -->
        <div class="col-xl-3 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div class="stat-widget-one">
                        <div class="stat-icon dib">
                            <i class="fa fa-calendar-alt text-warning border-warning"></i>
                        </div>
                        <div class="stat-content dib">
                            <div class="stat-text">This Month</div>
                            <div class="stat-digit">{{ $monthlyInvoices }}</div>
                            <div class="stat-text">Revenue: ৳{{ number_format($monthlyRevenue, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Total Revenue Summary -->
        <div class="col-xl-3 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <div class="stat-widget-one">
                        <div class="stat-icon dib">
                            <i class="fa fa-money-bill text-danger border-danger"></i>
                        </div>
                        <div class="stat-content dib">
                            <div class="stat-text">Total Revenue</div>
                            <div class="stat-digit">৳{{ number_format($totalRevenue, 0) }}</div>
                            <div class="stat-text">Paid: ৳{{ number_format($totalPaidAmount, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Status Charts Row -->
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
    
    <!-- Tables Row -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <strong class="card-title">Recent Invoices</strong>
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
                                <th>Total Invoices</th>
                                <th>Total Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topCustomers as $customer)
                            <tr>
                                <td>{{ $customer->name }}</td>
                                <td>{{ $customer->phone_number_1 }}</td>
                                <td>{{ $customer->invoices_count ?? 0 }}</td>
                                <td>৳{{ number_format($customer->invoices_sum_total ?? 0, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Invoice Status Chart
    const invoiceStatusData = {
        @foreach($invoiceStatusCounts as $status => $count)
            "{{ $status }}": {{ $count }},
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
    
    // Payment Status Chart
    const paymentStatusData = {
        @foreach($paymentStatusCounts as $status => $count)
            "{{ $status }}": {{ $count }},
        @endforeach
    };
    
    new Chart(document.getElementById('paymentStatusChart'), {
        type: 'pie',
        data: {
            labels: Object.keys(paymentStatusData),
            datasets: [{
                data: Object.values(paymentStatusData),
                backgroundColor: ['#4bc0c0', '#ffce56', '#ff6384']
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
});
</script>

<style>
.stat-widget-one {
    padding: 20px 0;
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
    font-size: 24px;
    font-weight: 600;
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
</style>

@endsection