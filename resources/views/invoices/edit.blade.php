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
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="customer_name">Customer Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('customer_name') is-invalid @enderror" 
                                               id="customer_name" name="customer_name" 
                                               value="{{ old('customer_name', $invoice->customer->name ?? '') }}" required>
                                        @error('customer_name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="customer_phone">Phone Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('customer_phone') is-invalid @enderror" 
                                               id="customer_phone" name="customer_phone" 
                                               value="{{ old('customer_phone', $invoice->customer->phone_number_1 ?? '') }}" required>
                                        @error('customer_phone')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Invoice Date</label>
                                        <input type="text" class="form-control-plaintext" 
                                               value="{{ $invoice->invoice_date->format('M d, Y') }}" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="customer_address">Address <span class="text-danger">*</span></label>
                                        <textarea class="form-control @error('customer_address') is-invalid @enderror" 
                                                  id="customer_address" name="customer_address" rows="3" required>{{ old('customer_address', $invoice->customer->full_address ?? '') }}</textarea>
                                        @error('customer_address')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <input type="hidden" name="customer_id" value="{{ $invoice->customer->id ?? '' }}">
                        </div>
                    </div>
                    
                    <!-- Invoice Settings -->
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
                            
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="special_instructions">Special Instructions</label>
                                        <textarea name="special_instructions" id="special_instructions" rows="2" 
                                                  class="form-control @error('special_instructions') is-invalid @enderror">{{ old('special_instructions', $invoice->special_instructions) }}</textarea>
                                        @error('special_instructions')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
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
                                    <tfoot class="bg-light" id="itemsTotalFoot">
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
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Return Items Table -->
                    <div class="card mb-4">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fa fa-undo text-danger"></i> Return Items</h6>
                            <div>
                                <div class="custom-control custom-switch mr-2 d-inline-block">
                                    <input type="checkbox" class="custom-control-input" id="hasReturnItems" name="has_return_items" value="1" 
                                           {{ $invoice->has_return_items ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="hasReturnItems">Has Return Items</label>
                                </div>
                                <button type="button" class="btn btn-danger btn-sm" id="addReturnItemBtn" style="{{ $invoice->has_return_items ? '' : 'display:none;' }}">
                                    <i class="fa fa-plus"></i> Add Return Item
                                </button>
                            </div>
                        </div>
                        <div class="card-body" id="returnItemsSection" style="{{ $invoice->has_return_items ? '' : 'display:none;' }}">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="returnItemsTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="20%">Item Name</th>
                                            <th width="10%">Quantity</th>
                                            <th width="15%">Unit Price (৳)</th>
                                            <th width="15%">Weight (kg)</th>
                                            <th width="15%">Total (৳)</th>
                                            <th width="15%">Return Reason</th>
                                            <th width="5%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="returnItemsTableBody">
                                        @php
                                            $returnItemCount = count($invoice->returnItems);
                                        @endphp
                                        @foreach($invoice->returnItems as $index => $returnItem)
                                        <tr data-return-item-id="{{ $returnItem->id }}" data-is-existing="true">
                                            <td class="serial">{{ $loop->iteration }}</td>
                                            <td>
                                                <input type="hidden" name="return_items[{{ $index }}][id]" value="{{ $returnItem->id }}">
                                                <input type="text" class="form-control return-item-name" 
                                                       name="return_items[{{ $index }}][item_name]" 
                                                       value="{{ old('return_items.'.$index.'.item_name', $returnItem->item_name ?? 'Three Piece') }}" required>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control return-quantity text-center" 
                                                       name="return_items[{{ $index }}][quantity]" 
                                                       value="{{ old('return_items.'.$index.'.quantity', $returnItem->quantity) }}" 
                                                       min="1" required>
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control return-unit-price text-right" 
                                                       name="return_items[{{ $index }}][unit_price]" 
                                                       value="{{ old('return_items.'.$index.'.unit_price', $returnItem->unit_price) }}" 
                                                       min="0" required>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control return-weight-display" 
                                                       value="{{ number_format($returnItem->weight / 1000, 2) }} kg" readonly>
                                                <input type="hidden" class="return-item-weight" 
                                                       name="return_items[{{ $index }}][weight]" 
                                                       value="{{ $returnItem->weight }}">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control return-total-price" 
                                                       value="৳{{ number_format($returnItem->total_price, 2) }}" readonly>
                                                <input type="hidden" class="return-item-total" 
                                                       value="{{ $returnItem->total_price }}">
                                            </td>
                                            <td>
                                                <select class="form-control return-reason" 
                                                        name="return_items[{{ $index }}][return_reason]">
                                                    <option value="">Select Reason</option>
                                                    <option value="damaged" {{ $returnItem->return_reason == 'damaged' ? 'selected' : '' }}>Damaged</option>
                                                    <option value="wrong_item" {{ $returnItem->return_reason == 'wrong_item' ? 'selected' : '' }}>Wrong Item</option>
                                                    <option value="customer_request" {{ $returnItem->return_reason == 'customer_request' ? 'selected' : '' }}>Customer Request</option>
                                                    <option value="quality_issue" {{ $returnItem->return_reason == 'quality_issue' ? 'selected' : '' }}>Quality Issue</option>
                                                    <option value="other" {{ $returnItem->return_reason == 'other' ? 'selected' : '' }}>Other</option>
                                                </select>
                                            </td>
                                            <td class="text-center">
                                                @if($returnItemCount > 1)
                                                <button type="button" class="btn btn-danger btn-sm delete-return-item">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-light" id="returnItemsTotalFoot">
                                        <tr>
                                            <td colspan="2" class="text-right font-weight-bold text-danger">Return Items Total:</td>
                                            <td class="text-center font-weight-bold text-danger">
                                                <span id="return-total-quantity">{{ $invoice->returnItems->sum('quantity') }}</span>
                                            </td>
                                            <td colspan="2"></td>
                                            <td colspan="2" class="font-weight-bold text-danger">
                                                ৳<span id="return-subtotal">{{ number_format($invoice->returnItems->sum('total_price'), 2) }}</span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Final Calculations Summary -->
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fa fa-calculator"></i> Final Calculation Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Items Summary -->
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="text-primary">Items Summary</h6>
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td>Total Items:</td>
                                                    <td class="text-right"><strong id="summary-total-items">0</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>Total Weight:</td>
                                                    <td class="text-right"><strong id="summary-total-weight">0.00 kg</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>Subtotal:</td>
                                                    <td class="text-right"><strong id="summary-subtotal">৳0.00</strong></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Returns Summary -->
                                <div class="col-md-4">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="text-danger">Returns Summary</h6>
                                            <table class="table table-sm table-borderless">
                                                <tr>
                                                    <td>Return Items:</td>
                                                    <td class="text-right"><strong id="summary-return-items">0</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>Return Amount:</td>
                                                    <td class="text-right"><strong id="summary-return-amount" class="text-danger">-৳0.00</strong></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Final Summary -->
                                <div class="col-md-4">
                                    <div class="card bg-success text-white">
                                        <div class="card-body">
                                            <h6>Final Summary</h6>
                                            <table class="table table-sm table-borderless text-white">
                                                <tr>
                                                    <td>Net Subtotal:</td>
                                                    <td class="text-right"><strong id="summary-net-subtotal">৳0.00</strong></td>
                                                </tr>
                                                <tr>
                                                    <td>Delivery Charge:</td>
                                                    <td class="text-right"><strong id="summary-delivery">৳0.00</strong></td>
                                                </tr>
                                                <tr class="border-top">
                                                    <td><strong>Grand Total:</strong></td>
                                                    <td class="text-right"><strong id="summary-grand-total" style="font-size: 18px;">৳0.00</strong></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
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

<script>
    window.invoiceData = {
        itemCount: {{ $itemCount }},
        returnItemCount: {{ $returnItemCount ?? 0 }},
        deliveryCharge: {{ $invoice->delivery_charge }},
        status: "{{ $invoice->status }}",
        subtotal: {{ $invoice->subtotal }},
        total: {{ $invoice->total }},
        hasReturnItems: {{ $invoice->has_return_items ? 'true' : 'false' }}
    };
</script>
@endsection