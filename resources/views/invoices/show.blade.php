@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1><i class="fa fa-eye"></i> Invoice Details</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('invoices.index') }}">Invoices</a></li>
                    <li class="active">View Invoice</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
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
                <strong class="card-title">
                    <i class="fa fa-file-invoice"></i> Invoice #{{ $invoice->invoice_number }}
                </strong>
                <div>
                    <a href="{{ route('pos.print', $invoice->id) }}" 
                       target="_blank" 
                       class="btn btn-secondary btn-sm mr-2">
                        <i class="fa fa-print"></i> Print
                    </a>
                    <a href="{{ route('invoices.edit', $invoice->id) }}" 
                       class="btn btn-warning btn-sm mr-2">
                        <i class="fa fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('invoices.index') }}" 
                       class="btn btn-primary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>

            <div class="card-body">
                <!-- Invoice Header -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-3">
                                    <i class="fa fa-building"></i> Company Information
                                </h6>
                                <h5 class="mb-1">Your Company Name</h5>
                                <p class="mb-1">Company Address Line 1</p>
                                <p class="mb-1">Company Address Line 2</p>
                                <p class="mb-0">Phone: +880 XXXX-XXXXXX | Email: info@company.com</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-3">
                                    <i class="fa fa-info-circle"></i> Invoice Information
                                </h6>
                                <table class="table table-sm table-borderless mb-0">
                                    <tr>
                                        <td width="40%"><strong>Invoice No:</strong></td>
                                        <td>{{ $invoice->invoice_number }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Date:</strong></td>
                                        <td>{{ $invoice->invoice_date->format('F d, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Status:</strong></td>
                                        <td>
                                            <span class="badge badge-{{ $invoice->payment_status == 'paid' ? 'success' : ($invoice->payment_status == 'partial' ? 'warning' : 'danger') }}">
                                                {{ ucfirst($invoice->payment_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Created:</strong></td>
                                        <td>{{ $invoice->created_at->format('M d, Y h:i A') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-muted mb-3">
                                    <i class="fa fa-user"></i> Customer Information
                                </h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <p class="mb-2"><strong>Name:</strong><br>{{ $invoice->customer->name }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-2"><strong>Primary Phone:</strong><br>{{ $invoice->customer->phone_number_1 }}</p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-2"><strong>Address:</strong><br>{{ $invoice->customer->full_address }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="fa fa-list"></i> Invoice Items</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th width="5%" class="text-center">#</th>
                                                <th width="45%">Item Description</th>
                                                <th width="15%" class="text-center">Quantity</th>
                                                <th width="15%" class="text-right">Unit Price</th>
                                                <th width="20%" class="text-right">Total Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($invoice->items as $item)
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td>
                                                    <strong>{{ $item->item_name }}</strong>
                                                    @if($item->description)
                                                    <br><small class="text-muted">{{ $item->description }}</small>
                                                    @endif
                                                </td>
                                                <td class="text-center">{{ $item->quantity }}</td>
                                                <td class="text-right">৳{{ number_format($item->unit_price, 2) }}</td>
                                                <td class="text-right">৳{{ number_format($item->total_price, 2) }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-light">
                                            <tr>
                                                <td colspan="3" rowspan="3">
                                                    @if($invoice->notes)
                                                    <div class="p-2">
                                                        <strong>Notes:</strong><br>
                                                        <small class="text-muted">{{ $invoice->notes }}</small>
                                                    </div>
                                                    @endif
                                                </td>
                                                <td class="text-right"><strong>Subtotal:</strong></td>
                                                <td class="text-right">৳{{ number_format($invoice->subtotal, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><strong>Total:</strong></td>
                                                <td class="text-right">
                                                    <strong class="text-success">৳{{ number_format($invoice->total, 2) }}</strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="text-right"><strong>Paid Amount:</strong></td>
                                                <td class="text-right">
                                                    <strong class="text-info">৳{{ number_format($invoice->paid_amount, 2) }}</strong>
                                                </td>
                                            </tr>
                                            <tr class="bg-warning-light">
                                                <td colspan="4" class="text-right"><strong>Due Amount:</strong></td>
                                                <td class="text-right">
                                                    <strong class="text-danger">৳{{ number_format($invoice->due_amount, 2) }}</strong>
                                                </td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment History -->
                @if($invoice->payment_status != 'paid')
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="fa fa-money-bill-wave"></i> Add Payment</h6>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('invoices.add-payment', $invoice->id) }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Amount to Pay</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text">৳</span>
                                                    </div>
                                                    <input type="number" 
                                                           name="amount" 
                                                           class="form-control" 
                                                           min="0" 
                                                           max="{{ $invoice->due_amount }}" 
                                                           step="0.01"
                                                           value="{{ $invoice->due_amount }}" 
                                                           required>
                                                </div>
                                                <small class="form-text text-muted">
                                                    Maximum: ৳{{ number_format($invoice->due_amount, 2) }}
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Payment Date</label>
                                                <input type="date" 
                                                       name="payment_date" 
                                                       class="form-control" 
                                                       value="{{ date('Y-m-d') }}" 
                                                       required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="submit" class="btn btn-success btn-block">
                                                    <i class="fa fa-check"></i> Add Payment
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="row">
                    <div class="col-12 text-center">
                        <a href="{{ route('pos.print', $invoice->id) }}" 
                           target="_blank" 
                           class="btn btn-primary btn-lg mr-2">
                            <i class="fa fa-print"></i> Print Invoice
                        </a>
                        <a href="{{ route('invoices.edit', $invoice->id) }}" 
                           class="btn btn-warning btn-lg mr-2">
                            <i class="fa fa-edit"></i> Edit Invoice
                        </a>
                        <a href="{{ route('invoices.index') }}" 
                           class="btn btn-secondary btn-lg">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-warning-light {
        background-color: #fff3cd !important;
    }
    
    .bg-light {
        background-color: #f8f9fa !important;
    }
    
    .card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .table thead th {
        border-bottom: 2px solid #dee2e6;
    }
    
    .table tfoot td {
        font-weight: bold;
        border-top: 2px solid #dee2e6;
    }
</style>
@endsection