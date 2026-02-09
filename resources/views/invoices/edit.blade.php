@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fa fa-edit"></i> Edit Invoice #{{ $invoice->invoice_number }}
                </h5>
                <div>
                    <a href="{{ route('admin.invoices.print', $invoice->id) }}" class="btn btn-info btn-sm mr-2">
                        <i class="fa fa-print"></i> Print
                    </a>
                    <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                
                <form action="{{ route('admin.invoices.update', $invoice->id) }}" method="POST" id="editInvoiceForm">
                    @csrf
                    @method('PUT')
                    
                    <!-- Customer Info -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa fa-user"></i> Customer Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Customer Name</label>
                                        <input type="text" class="form-control-plaintext" 
                                               value="{{ $invoice->customer->name }}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                               id="recipientPhone" name="phone" 
                                               value="{{ old('phone', $invoice->customer->phone_number_1) }}">
                                        @error('phone')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Invoice Date</label>
                                        <input type="text" class="form-control-plaintext" 
                                               value="{{ $invoice->invoice_date->format('M d, Y') }}" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label for="address">Address</label>
                                        <textarea class="form-control @error('recipient_address') is-invalid @enderror" 
                                                  id="recipient_address" name="recipient_address" rows="3">{{ old('recipient_address', $invoice->recipient_address) }}</textarea>
                                        @error('recipient_address')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Delivery Charge & Status -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa fa-cog"></i> Invoice Settings</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="delivery_charge">Delivery Charge (৳)</label>
                                        <input type="number" step="0.01" class="form-control @error('delivery_charge') is-invalid @enderror" 
                                               id="delivery_charge" name="delivery_charge" 
                                               value="{{ old('delivery_charge', $invoice->delivery_charge) }}" 
                                               required min="0">
                                        @error('delivery_charge')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="status">Invoice Status</label>
                                        <select class="form-control @error('status') is-invalid @enderror" 
                                                id="status" name="status" required>
                                            <option value="confirmed" {{ old('status', $invoice->status) == 'confirmed' ? 'selected' : '' }}>
                                                Confirmed
                                            </option>
                                            <option value="pending" {{ old('status', $invoice->status) == 'pending' ? 'selected' : '' }}>
                                                Pending
                                            </option>
                                            <option value="cancelled" {{ old('status', $invoice->status) == 'cancelled' ? 'selected' : '' }}>
                                                Cancelled
                                            </option>
                                        </select>
                                        @error('status')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="merchant_order_id">Merchant Order ID</label>
                                        <input type="text" class="form-control @error('merchant_order_id') is-invalid @enderror" 
                                               id="merchant_order_id" name="merchant_order_id" 
                                               value="{{ old('merchant_order_id', $invoice->merchant_order_id) }}">
                                        @error('merchant_order_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="notes">Notes</label>
                                        <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes', $invoice->notes) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Items Table -->
                    <div class="card mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fa fa-shopping-cart"></i> Edit Items</h6>
                            <button type="button" class="btn btn-success btn-sm" id="addItemBtn">
                                <i class="fa fa-plus"></i> Add Item
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="itemsTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="25%">Item Name</th>
                                            <th width="10%">Quantity</th>
                                            <th width="15%">Unit Price (৳)</th>
                                            <th width="15%">Weight (kg)</th>
                                            <th width="15%">Total (৳)</th>
                                            <th width="10%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="itemsTableBody">
                                        @php
                                            $itemCount = count($invoice->items);
                                        @endphp
                                        @foreach($invoice->items as $index => $item)
                                        <tr data-item-id="{{ $item->id }}" data-is-existing="true">
                                            <td class="serial">{{ $loop->iteration }}</td>
                                            <td>
                                                <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                                <input type="text" class="form-control @error('items.'.$index.'.item_name') is-invalid @enderror" 
                                                       name="items[{{ $index }}][item_name]" 
                                                       value="{{ old('items.'.$index.'.item_name', $item->item_name) }}" required>
                                                @error('items.'.$index.'.item_name')
                                                    <span class="invalid-feedback small">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="number" class="form-control quantity @error('items.'.$index.'.quantity') is-invalid @enderror" 
                                                       name="items[{{ $index }}][quantity]" 
                                                       value="{{ old('items.'.$index.'.quantity', $item->quantity) }}" required min="1">
                                                @error('items.'.$index.'.quantity')
                                                    <span class="invalid-feedback small">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control unit-price @error('items.'.$index.'.unit_price') is-invalid @enderror" 
                                                       name="items[{{ $index }}][unit_price]" 
                                                       value="{{ old('items.'.$index.'.unit_price', $item->unit_price) }}" required min="0">
                                                @error('items.'.$index.'.unit_price')
                                                    <span class="invalid-feedback small">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="text" class="form-control weight-display" 
                                                       value="{{ number_format($item->weight / 1000, 2) }} kg" 
                                                       readonly>
                                                <input type="hidden" class="item-weight" 
                                                       name="items[{{ $index }}][weight]" 
                                                       value="{{ $item->weight }}">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control total-price" 
                                                       value="৳{{ number_format($item->total_price, 2) }}" 
                                                       readonly>
                                                <input type="hidden" class="item-total" 
                                                       value="{{ $item->total_price }}">
                                            </td>
                                            <td class="text-center">
                                                @if($itemCount > 1)
                                                <button type="button" class="btn btn-danger btn-sm delete-item">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-light">
                                        <tr>
                                            <td colspan="2" class="text-right font-weight-bold">Total Quantity:</td>
                                            <td class="text-center font-weight-bold">
                                                <span id="total-quantity">{{ $invoice->items->sum('quantity') }}</span>
                                            </td>
                                            <td colspan="1" class="text-right font-weight-bold">Total Weight:</td>
                                            <td class="text-center font-weight-bold">
                                                <span id="total-weight">{{ number_format($invoice->items->sum('weight') / 1000, 2) }} kg</span>
                                            </td>
                                            <td colspan="2"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right font-weight-bold">Subtotal:</td>
                                            <td colspan="4" class="font-weight-bold">
                                                ৳<span id="subtotal">{{ number_format($invoice->subtotal, 2) }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-right font-weight-bold">Delivery Charge:</td>
                                            <td colspan="4" class="font-weight-bold">
                                                ৳<span id="delivery-display">{{ number_format($invoice->delivery_charge, 2) }}</span>
                                            </td>
                                        </tr>
                                        <tr class="table-primary">
                                            <td colspan="3" class="text-right font-weight-bold">Grand Total:</td>
                                            <td colspan="4" class="font-weight-bold">
                                                ৳<span id="grand-total">{{ number_format($invoice->total, 2) }}</span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Update Invoice
                        </button>
                        <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Include CSS -->
<link rel="stylesheet" href="{{ asset('css/invoices-edit.css') }}">

<!-- Include JavaScript -->
<script src="{{ asset('js/invoices-edit.js') }}"></script>
<script src="{{ asset('js/fraud-check.js') }}"></script>
<!-- Pass data to JavaScript -->
<script>
    window.invoiceData = {
        itemCount: {{ $itemCount }},
        deliveryCharge: {{ $invoice->delivery_charge }},
        status: "{{ $invoice->status }}",
        subtotal: {{ $invoice->subtotal }},
        total: {{ $invoice->total }}
    };
</script>
@endsection