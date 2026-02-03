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
                    <a href="{{ route('invoices.print', $invoice->id) }}" class="btn btn-info btn-sm">
                        <i class="fa fa-print"></i> Print
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Customer Info</h6>
                        <p><strong>Name:</strong> {{ $invoice->customer->name }}</p>
                        <p><strong>Phone:</strong> {{ $invoice->customer->phone_number_1 }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Invoice Info</h6>
                        <p><strong>Date:</strong> {{ $invoice->invoice_date->format('M d, Y') }}</p>
                        <p><strong>Status:</strong> 
                            <span class="badge badge-{{ 
                                $invoice->payment_status == 'paid' ? 'success' : 
                                ($invoice->payment_status == 'partial' ? 'warning' : 'danger') 
                            }}">
                                {{ ucfirst($invoice->payment_status) }}
                            </span>
                        </p>
                    </div>
                </div>

                <h6>Recipient Details</h6>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> {{ $invoice->recipient_name }}</p>
                        <p><strong>Merchant Order Id:</strong> {{ $invoice->merchant_order_id }}</p>
                        <p><strong>Phone:</strong> {{ $invoice->recipient_phone }}</p>
                        @if($invoice->recipient_secondary_phone)
                        <p><strong>Secondary Phone:</strong> {{ $invoice->recipient_secondary_phone }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <p><strong>Address:</strong> {{ $invoice->recipient_address }}</p>
                        <p><strong>Delivery Area:</strong> {{ $invoice->delivery_area }}</p>
                    </div>
                </div>

                <h6>Items</h6>
                <table class="table table-bordered mb-4">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Weight</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $item)
                        <tr>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->weight }}g</td>
                            <td>৳{{ number_format($item->unit_price, 2) }}</td>
                            <td>৳{{ number_format($item->total_price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right"><strong>Subtotal:</strong></td>
                            <td>৳{{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-right"><strong>Delivery Charge:</strong></td>
                            <td>৳{{ number_format($invoice->delivery_charge, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-right"><strong>Total:</strong></td>
                            <td>৳{{ number_format($invoice->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <div class="row">
                    <div class="col-md-6">
                        <h6>Payment Details</h6>
                        <p><strong>Paid:</strong> ৳{{ number_format($invoice->paid_amount, 2) }}</p>
                        <p><strong>Due:</strong> ৳{{ number_format($invoice->due_amount, 2) }}</p>
                        @if($invoice->payment_method)
                        <p><strong>Method:</strong> {{ ucfirst($invoice->payment_method) }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        @if($invoice->special_instructions)
                        <h6>Special Instructions</h6>
                        <p>{{ $invoice->special_instructions }}</p>
                        @endif
                        @if($invoice->notes)
                        <h6>Notes</h6>
                        <p>{{ $invoice->notes }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection