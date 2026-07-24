@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    Invoice #{{ $invoice->invoice_number }}
                </h5>
                <div>
                    <a href="{{ route('admin.invoices.edit', $invoice->id) }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-edit"></i> Edit
                    </a>
                    <a href="{{ route('admin.invoices.print', $invoice->id) }}" class="btn btn-info btn-sm">
                        <i class="fa fa-print"></i> Print
                    </a>
                    <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Customer Info</h6>
                        <p><strong>Name:</strong> {{ $invoice->customer->name }}</p>
                        <p><strong>Phone:</strong> {{ $invoice->customer->phone_number_1 }}</p>
                        @if($invoice->customer->phone_number_2)
                        <p><strong>Secondary Phone:</strong> {{ $invoice->customer->phone_number_2 }}</p>
                        @endif
                        <p><strong>Address:</strong> {{ $invoice->customer->full_address }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Invoice Info</h6>
                        <p><strong>Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</p>
                        <p><strong>Status:</strong> 
                            <span class="badge badge-{{ 
                                $invoice->status == 'confirmed' ? 'success' : 
                                ($invoice->status == 'pending' ? 'warning' : 'danger') 
                            }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </p>
                        <p><strong>Payment Status:</strong> 
                            <span class="badge badge-{{ 
                                $invoice->payment_status == 'paid' ? 'success' : 
                                ($invoice->payment_status == 'partial' ? 'warning' : 'danger') 
                            }}">
                                {{ ucfirst($invoice->payment_status) }}
                            </span>
                        </p>
                        @if($invoice->merchant_order_id)
                        <p><strong>Merchant Order ID:</strong> {{ $invoice->merchant_order_id }}</p>
                        @endif
                    </div>
                </div>

                
                <!-- Items Table -->
                <h6>Items</h6>
                <table class="table table-bordered mb-4">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Weight</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $itemTotalQuantity = 0; @endphp
                        @foreach($invoice->items as $index => $item)
                        @php $itemTotalQuantity += $item->quantity; @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->item_name }} @if($item->description) <br><small class="text-muted">{{ $item->description }}</small> @endif</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->weight }}g</td>
                            <td>৳{{ number_format($item->unit_price, 0) }}</td>
                            <td>৳{{ number_format($item->total_price, 0) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-right font-weight-bold">Total Quantity:</td>
                            <td colspan="4">{{ $itemTotalQuantity }}</td>
                        </tr>
                    </tfoot>
                </table>

                <!-- Return Items Table (if exists) -->
                @if($invoice->has_return_items && $invoice->returnItems->count() > 0)
                <h6 class="text-danger"><i class="fa fa-undo"></i> Return Items</h6>
                <table class="table table-bordered mb-4">
                    <thead class="thead-light" style="background: #ffe6e6;">
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Weight</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $returnTotalQuantity = 0; @endphp
                        @foreach($invoice->returnItems as $index => $returnItem)
                        @php $returnTotalQuantity += $returnItem->quantity; @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $returnItem->item_name }} @if($returnItem->description) <br><small class="text-muted">{{ $returnItem->description }}</small> @endif</td>
                            <td>{{ $returnItem->quantity }}</td>
                            <td>{{ $returnItem->weight }}g</td>
                            <td>৳{{ number_format($returnItem->unit_price, 0) }}</td>
                            <td class="text-danger">-৳{{ number_format($returnItem->total_price, 0) }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $returnItem->return_reason ?? 'N/A')) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2" class="text-right font-weight-bold text-danger">Return Quantity:</td>
                            <td colspan="5" class="text-danger">{{ $returnTotalQuantity }}</td>
                        </tr>
                        <tr>
                            <td colspan="6" class="text-right font-weight-bold text-danger">Total Return Amount:</td>
                            <td class="text-danger">-৳{{ number_format($invoice->returnItems->sum('total_price'), 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
                @endif

                <!-- Summary Section -->
                <h6>Payment Summary</h6>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <td><strong>Items Subtotal:</strong></td>
                                <td class="text-right">৳{{ number_format($invoice->items->sum('total_price'), 0) }}</td>
                            </tr>
                            @if($invoice->has_return_items && $invoice->returnItems->count() > 0)
                            <tr class="text-danger">
                                <td><strong>Less Returns:</strong></td>
                                <td class="text-right">-৳{{ number_format($invoice->returnItems->sum('total_price'), 0) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Net Subtotal:</strong></td>
                                <td class="text-right">৳{{ number_format($invoice->subtotal, 0) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Delivery Charge:</strong></td>
                                <td class="text-right">৳{{ number_format($invoice->delivery_charge, 0) }}</td>
                            </tr>
                            <tr class="table-primary">
                                <td><strong>Grand Total:</strong></td>
                                <td class="text-right"><strong>৳{{ number_format($invoice->total, 0) }}</strong></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <td><strong>Paid Amount:</strong></td>
                                <td class="text-right">৳{{ number_format($invoice->paid_amount, 0) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Due Amount:</strong></td>
                                <td class="text-right"><strong class="text-{{ $invoice->due_amount > 0 ? 'danger' : 'success' }}">৳{{ number_format($invoice->due_amount, 0) }}</strong></td>
                            </tr>
                            @if($invoice->payment_method)
                            <tr>
                                <td><strong>Payment Method:</strong></td>
                                <td class="text-right">{{ ucfirst(str_replace('_', ' ', $invoice->payment_method)) }}</td>
                            </tr>
                            @endif
                            @if($invoice->payment_details)
                            <tr>
                                <td><strong>Transaction ID:</strong></td>
                                <td class="text-right">{{ $invoice->payment_details }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="row">
                    <div class="col-md-6">
                        @if($invoice->special_instructions)
                        <h6>Special Instructions</h6>
                        <p class="bg-light p-2 rounded">{{ $invoice->special_instructions }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if($invoice->notes)
                        <h6>Notes</h6>
                        <p class="bg-light p-2 rounded">{{ $invoice->notes }}</p>
                        @endif
                    </div>
                </div>

                <!-- Created By -->
                <div class="mt-3 text-muted small">
                    <p>Created by: {{ $invoice->creator->name ?? 'N/A' }} | Created at: {{ $invoice->created_at->format('M d, Y h:i A') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection