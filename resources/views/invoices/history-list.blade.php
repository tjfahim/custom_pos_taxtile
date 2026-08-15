@extends('admin.layouts.master')


@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>
                        <i class="fa fa-history text-primary"></i> 
                        Edit History
                        <small class="text-muted ms-2">
                            Total: {{ $invoices->total() }} invoices edited
                        </small>
                    </h2>
                    <p class="text-muted mb-0">
                        <i class="far fa-calendar-alt"></i> 
                        Showing invoices that have been edited at least once
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to Invoices
                    </a>
                 
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('admin.invoices.history.list') }}" method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search by invoice #, customer name or phone..."
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa fa-search"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Invoices List -->
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fa fa-list"></i> Invoice Edit History
                        </h5>
                        <span class="badge bg-primary">{{ $invoices->total() }} invoices</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($invoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 12%">Invoice #</th>
                                        <th style="width: 18%">Customer</th>
                                        <th style="width: 12%">Total Edits</th>
                                        <th style="width: 12%">Status Changes</th>
                                        <th style="width: 14%">Created By</th>
                                        <th style="width: 14%">Last Edited By</th>
                                        <th style="width: 13%">Last Edit Time</th>
                                        <th style="width: 5%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                        @php
                                            $stats = $statistics[$invoice->id] ?? null;
                                        @endphp
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.invoices.show', $invoice->id) }}" 
                                                   class="fw-bold text-primary">
                                                    #{{ $invoice->invoice_number }}
                                                </a>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-bold">{{ $invoice->recipient_name }}</div>
                                                    <small class="text-muted">
                                                        <i class="fa fa-phone"></i> {{ $invoice->recipient_phone }}
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary fs-6">
                                                    {{ $stats['total_edits'] ?? 0 }}
                                                </span>
                                            </td>
                                            <td>
                                                @if(($stats['status_changes'] ?? 0) > 0)
                                                    <span class="badge bg-warning">
                                                        {{ $stats['status_changes'] }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">0</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge rounded-circle bg-secondary me-1 p-2">
                                                        {{ substr($stats['created_by'] ?? 'U', 0, 1) }}
                                                    </span>
                                                    {{ $stats['created_by'] ?? 'Unknown' }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="badge rounded-circle bg-info me-1 p-2">
                                                        {{ substr($stats['last_editor'] ?? 'S', 0, 1) }}
                                                    </span>
                                                    {{ $stats['last_editor'] ?? 'System' }}
                                                </div>
                                            </td>
                                            <td>
                                                @if($stats['last_edit'] ?? false)
                                                    <div>
                                                        <span class="fw-bold">
                                                            {{ $stats['last_edit']->format('M d, Y') }}
                                                        </span>
                                                        <br>
                                                        <small class="text-muted">
                                                            {{ $stats['last_edit']->format('h:i A') }}
                                                            <span class="ms-1">
                                                                ({{ $stats['last_edit']->diffForHumans() }})
                                                            </span>
                                                        </small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Never</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.invoices.history.detail', $invoice->id) }}" 
                                                   class="btn btn-sm btn-primary"
                                                   data-bs-toggle="tooltip"
                                                   title="View full history">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa fa-history fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Edit History Found</h5>
                            <p class="text-muted">
                                No invoices have been edited yet. 
                                <a href="{{ route('admin.invoices.index') }}">Go to invoices</a>
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pagination -->
            @if($invoices->hasPages())
                <div class="mt-3">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
<style>
.table td {
    vertical-align: middle;
}

.badge-rounded-circle {
    width: 30px;
    height: 30px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}
</style>
