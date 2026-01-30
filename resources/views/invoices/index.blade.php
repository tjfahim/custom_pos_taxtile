@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1><i class="fa fa-file-invoice"></i> Invoice Management</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li class="active">Invoices</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <!-- Success Message -->
    @if(session('success'))
        <div class="col-sm-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <span class="badge badge-pill badge-success">Success</span>
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    @endif

    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong class="card-title"><i class="fa fa-list"></i> Invoice List</strong>
                <div>
                    <a href="{{ route('pos.index') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Create New Invoice
                    </a>
                </div>
            </div>

            <div class="card-body">
                <!-- Search Form -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <form action="{{ route('invoices.index') }}" method="GET" class="form-inline">
                            <div class="input-group w-100">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Search by invoice number or customer name..." 
                                       value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="fa fa-search"></i> Search
                                    </button>
                                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                                        <i class="fa fa-refresh"></i> Clear
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover" id="invoiceTable">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-center">#</th>
                                <th>Invoice Details</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th class="text-right">Amount</th>
                                <th>Payment Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    <strong class="d-block">{{ $invoice->invoice_number }}</strong>
                                    @if($invoice->notes)
                                    <small class="text-muted">{{ Str::limit($invoice->notes, 30) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $invoice->customer->name }}</strong><br>
                                    <small class="text-muted">{{ $invoice->customer->phone_number_1 }}</small>
                                </td>
                                <td>{{ $invoice->invoice_date->format('d M, Y') }}</td>
                                <td class="text-right">
                                    <strong class="text-success">৳{{ number_format($invoice->total, 2) }}</strong><br>
                                    <small class="text-muted">
                                        Paid: ৳{{ number_format($invoice->paid_amount, 2) }}<br>
                                        Due: ৳{{ number_format($invoice->due_amount, 2) }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $invoice->payment_status == 'paid' ? 'success' : ($invoice->payment_status == 'partial' ? 'warning' : 'danger') }} p-2">
                                        <i class="fa fa-{{ $invoice->payment_status == 'paid' ? 'check-circle' : ($invoice->payment_status == 'partial' ? 'exclamation-circle' : 'times-circle') }}"></i>
                                        {{ ucfirst($invoice->payment_status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('invoices.show', $invoice->id) }}" 
                                           class="btn btn-outline-info" title="View">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('pos.print', $invoice->id) }}" 
                                           target="_blank" 
                                           class="btn btn-outline-secondary" title="Print">
                                            <i class="fa fa-print"></i>
                                        </a>
                                        <a href="{{ route('invoices.edit', $invoice->id) }}" 
                                           class="btn btn-outline-warning" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <form action="{{ route('invoices.destroy', $invoice->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-outline-danger" 
                                                    title="Delete"
                                                    onclick="return confirm('Are you sure you want to delete this invoice?')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="fa fa-file-invoice fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No invoices found</h5>
                                        <p class="text-muted">Create your first invoice from POS system</p>
                                        <a href="{{ route('pos.index') }}" class="btn btn-primary mt-2">
                                            <i class="fa fa-plus"></i> Create First Invoice
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($invoices->count() > 0)
                        <tfoot>
                            <tr class="bg-light">
                                <td colspan="4" class="text-right"><strong>Totals:</strong></td>
                                <td class="text-right">
                                    <strong class="text-success">৳{{ number_format($invoices->sum('total'), 2) }}</strong><br>
                                    <small class="text-muted">
                                        Paid: ৳{{ number_format($invoices->sum('paid_amount'), 2) }}<br>
                                        Due: ৳{{ number_format($invoices->sum('due_amount'), 2) }}
                                    </small>
                                </td>
                                <td colspan="2">
                                    <div class="text-center">
                                        <small class="text-muted">
                                            Total Invoices: {{ $invoices->count() }}
                                        </small>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>

                <!-- Pagination -->
                @if($invoices->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} 
                        of {{ $invoices->total() }} invoices
                    </div>
                    <div>
                        {{ $invoices->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
    .table th {
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9ff;
        transition: all 0.2s ease;
    }
    
    .empty-state {
        padding: 3rem 1rem;
    }
    
    .badge-success {
        background-color: #d4edda !important;
        color: #155724 !important;
    }
    
    .badge-warning {
        background-color: #fff3cd !important;
        color: #856404 !important;
    }
    
    .badge-danger {
        background-color: #f8d7da !important;
        color: #721c24 !important;
    }
</style>

<script>
$(document).ready(function() {
    $('#invoiceTable').DataTable({
        "order": [[0, "desc"]],
        "columnDefs": [
            { "orderable": false, "targets": 6 }
        ],
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "responsive": true,
        "language": {
            "search": "_INPUT_",
            "searchPlaceholder": "Search invoices...",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ invoices",
            "infoEmpty": "Showing 0 to 0 of 0 invoices",
            "infoFiltered": "(filtered from _MAX_ total invoices)",
            "zeroRecords": "No matching invoices found",
            "paginate": {
                "first": "First",
                "last": "Last",
                "next": "Next",
                "previous": "Previous"
            }
        }
    });
});
</script>
@endsection