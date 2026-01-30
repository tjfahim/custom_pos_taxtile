@extends('admin.layouts.master')

@section('main_content')
<div class="breadcrumbs">
    <div class="col-sm-4">
        <div class="page-header float-left">
            <div class="page-title">
                <h1><i class="fa fa-edit"></i> Edit Invoice</h1>
            </div>
        </div>
    </div>
    <div class="col-sm-8">
        <div class="page-header float-right">
            <div class="page-title">
                <ol class="breadcrumb text-right">
                    <li><a href="{{ route('invoices.index') }}">Invoices</a></li>
                    <li><a href="{{ route('invoices.show', $invoice->id) }}">View Invoice</a></li>
                    <li class="active">Edit Invoice</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <strong><i class="fa fa-pencil-alt"></i> Edit Invoice #{{ $invoice->invoice_number }}</strong>
            </div>
            <div class="card-body card-block">
                <form action="{{ route('invoices.update', $invoice->id) }}" method="POST" class="form-horizontal">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3">
                                        <i class="fa fa-info-circle"></i> Basic Information
                                    </h6>
                                    <div class="form-group">
                                        <label class="form-control-label">Customer *</label>
                                        <select name="customer_id" class="form-control" required>
                                            <option value="">Select Customer</option>
                                            @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" 
                                                    {{ $invoice->customer_id == $customer->id ? 'selected' : '' }}>
                                                {{ $customer->name }} - {{ $customer->phone_number_1 }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('customer_id')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-control-label">Invoice Date *</label>
                                        <input type="date" 
                                               name="invoice_date" 
                                               class="form-control" 
                                               value="{{ old('invoice_date', $invoice->invoice_date->format('Y-m-d')) }}" 
                                               required>
                                        @error('invoice_date')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <h6 class="card-title text-muted mb-3">
                                        <i class="fa fa-money-bill-wave"></i> Payment Information
                                    </h6>
                                    <div class="form-group">
                                        <label class="form-control-label">Paid Amount</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">৳</span>
                                            </div>
                                            <input type="number" 
                                                   name="paid_amount" 
                                                   class="form-control" 
                                                   value="{{ old('paid_amount', $invoice->paid_amount) }}" 
                                                   min="0" 
                                                   step="0.01">
                                        </div>
                                        @error('paid_amount')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Current Due: ৳{{ number_format($invoice->due_amount, 2) }}
                                        </small>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label class="form-control-label">Notes</label>
                                        <textarea name="notes" 
                                                  class="form-control" 
                                                  rows="3" 
                                                  placeholder="Any additional notes...">{{ old('notes', $invoice->notes) }}</textarea>
                                        @error('notes')
                                            <small class="form-text text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Invoice Items (Read Only) -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-white">
                                    <h6 class="mb-0"><i class="fa fa-list"></i> Invoice Items</h6>
                                    <small class="text-muted">Items cannot be edited here. Create a new invoice for changes.</small>
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
                                                    <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                                                    <td colspan="2" class="text-right">৳{{ number_format($invoice->subtotal, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" class="text-right"><strong>Total:</strong></td>
                                                    <td colspan="2" class="text-right">
                                                        <strong class="text-success">৳{{ number_format($invoice->total, 2) }}</strong>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-12 text-center">
                            <button type="submit" class="btn btn-success btn-lg mr-2">
                                <i class="fa fa-save"></i> Update Invoice
                            </button>
                            <a href="{{ route('invoices.show', $invoice->id) }}" 
                               class="btn btn-secondary btn-lg mr-2">
                                <i class="fa fa-times"></i> Cancel
                            </a>
                            <button type="button" 
                                    class="btn btn-danger btn-lg" 
                                    onclick="if(confirm('Are you sure you want to delete this invoice?')) { document.getElementById('delete-form').submit(); }">
                                <i class="fa fa-trash"></i> Delete Invoice
                            </button>
                        </div>
                    </div>
                </form>
                
                <!-- Delete Form -->
                <form id="delete-form" 
                      action="{{ route('invoices.destroy', $invoice->id) }}" 
                      method="POST" 
                      style="display: none;">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    .table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9ff;
    }
</style>
@endsection