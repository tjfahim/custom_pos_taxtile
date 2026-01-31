@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fa fa-file-invoice"></i> Invoices
                </h5>
                <a href="{{ route('invoices.pos') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> Create Invoice
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Recipient</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                            <tr>
                                <td>{{ $invoice->invoice_number }}</td>
                                <td>{{ $invoice->customer->name }}</td>
                                <td>{{ $invoice->recipient_name }}</td>
                                <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                                <td>${{ number_format($invoice->total, 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ 
                                        $invoice->payment_status == 'paid' ? 'success' : 
                                        ($invoice->payment_status == 'partial' ? 'warning' : 'danger') 
                                    }}">
                                        {{ ucfirst($invoice->payment_status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('invoices.print', $invoice->id) }}" 
                                       class="btn btn-sm btn-info" title="Print">
                                        <i class="fa fa-print"></i> Print
                                    </a>
                                    <a href="{{ route('invoices.show', $invoice->id) }}" 
                                       class="btn btn-sm btn-secondary" title="View">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <form action="{{ route('invoices.destroy', $invoice->id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm('Delete this invoice?')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    No invoices found
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $invoices->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection