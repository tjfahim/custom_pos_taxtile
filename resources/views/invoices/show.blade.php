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
                  
                    <a href="{{ route('admin.invoices.print', $invoice->id) }}" class="btn btn-info btn-sm">
                        <i class="fa fa-print"></i> Print
                    </a>
                    <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <div class="card-body">

                {{-- ================= CUSTOMER + INVOICE INFO ================= --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2">Customer Info</h6>
                        <p class="mb-1"><strong>Name:</strong> {{ $invoice->customer->name ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Phone:</strong> {{ $invoice->recipient_phone ?? ($invoice->customer->phone_number_1 ?? 'N/A') }}</p>
                        @if($invoice->recipient_secondary_phone ?? $invoice->customer->phone_number_2 ?? null)
                            <p class="mb-1"><strong>Secondary Phone:</strong> {{ $invoice->recipient_secondary_phone ?? $invoice->customer->phone_number_2 }}</p>
                        @endif
                        <p class="mb-1"><strong>Address:</strong> {{ $invoice->recipient_address ?? ($invoice->customer->full_address ?? 'N/A') }}</p>
                        @if($invoice->delivery_area)
                            <p class="mb-1"><strong>Delivery Area:</strong> {{ $invoice->delivery_area }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="border-bottom pb-2">Invoice Info</h6>
                        <p class="mb-1"><strong>Date:</strong> {{ $invoice->invoice_date ? $invoice->invoice_date->format('M d, Y') : 'N/A' }}</p>
                        <p class="mb-1"><strong>Status:</strong>
                            <span class="badge badge-{{
                                $invoice->status == 'confirmed' ? 'success' :
                                ($invoice->status == 'pending' ? 'warning' : 'danger')
                            }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </p>
                        <p class="mb-1"><strong>Payment Status:</strong>
                            <span class="badge badge-{{
                                $invoice->payment_status == 'paid' ? 'success' :
                                ($invoice->payment_status == 'partial' ? 'warning' : 'danger')
                            }}">
                                {{ ucfirst($invoice->payment_status) }}
                            </span>
                        </p>
                        <p class="mb-1"><strong>Store Location:</strong> {{ $invoice->store_location ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Delivery Type:</strong> {{ $invoice->delivery_type ?? 'N/A' }}</p>
                        <p class="mb-1"><strong>Courier:</strong>
                            @if($invoice->is_inhouse_sale)
                                <span class="badge badge-primary">Inhouse</span>
                            @elseif($invoice->courier_name)
                                <span class="badge badge-info">{{ $invoice->courier_name }}</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </p>
                        @if($invoice->merchant_order_id)
                            <p class="mb-1"><strong>Merchant Order ID:</strong> {{ $invoice->merchant_order_id }}</p>
                        @endif
                        @if($invoice->product_type)
                            <p class="mb-1"><strong>Product Type:</strong> {{ $invoice->product_type }}</p>
                        @endif
                        <p class="mb-1"><strong>Wholesale:</strong> {{ $invoice->is_wholesale ? 'Yes' : 'No' }}</p>
                        <p class="mb-1"><strong>Inhouse Sale:</strong> {{ $invoice->is_inhouse_sale ? 'Yes' : 'No' }}</p>
                        @if($invoice->confirmed_at)
                            <p class="mb-1"><strong>Confirmed At:</strong> {{ $invoice->confirmed_at->format('M d, Y h:i A') }}</p>
                        @endif
                    </div>
                </div>

                {{-- ================= ITEMS TABLE ================= --}}
                <h6 class="border-bottom pb-2">Items</h6>
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
                                <td>
                                    {{ $item->item_name }}
                                    @if($item->description)
                                        <br><small class="text-muted">{{ $item->description }}</small>
                                    @endif
                                </td>
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

                {{-- ================= RETURN ITEMS TABLE ================= --}}
                @if($invoice->has_return_items && $invoice->returnItems->count() > 0)
                    <h6 class="text-danger border-bottom pb-2">
                        <i class="fa fa-undo"></i> Return Items
                    </h6>
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
                            @foreach($invoice->returnItems as $returnItem)
                                @php $returnTotalQuantity += $returnItem->quantity; @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        {{ $returnItem->item_name }}
                                        @if($returnItem->description)
                                            <br><small class="text-muted">{{ $returnItem->description }}</small>
                                        @endif
                                    </td>
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

                {{-- ================= PAYMENT SUMMARY ================= --}}
                <h6 class="border-bottom pb-2">Payment Summary</h6>
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
                            <tr>
                                <td><strong>Total Weight:</strong></td>
                                <td class="text-right">{{ $invoice->total_weight }}g</td>
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
                                <td><strong>Amount to Collect:</strong></td>
                                <td class="text-right">৳{{ number_format($invoice->amount_to_collect ?? 0, 0) }}</td>
                            </tr>
                            <tr>
                                <td><strong>Paid Amount:</strong></td>
                                <td class="text-right">৳{{ number_format($invoice->paid_amount, 0) }}</td>
                            </tr>
                            @if($invoice->paid_amount2)
                                <tr>
                                    <td><strong>Paid Amount 2:</strong></td>
                                    <td class="text-right">৳{{ number_format($invoice->paid_amount2, 0) }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td><strong>Due Amount:</strong></td>
                                <td class="text-right">
                                    <strong class="text-{{ $invoice->due_amount > 0 ? 'danger' : 'success' }}">
                                        ৳{{ number_format($invoice->due_amount, 0) }}
                                    </strong>
                                </td>
                            </tr>
                            @if($invoice->payment_method)
                                <tr>
                                    <td><strong>Payment Method:</strong></td>
                                    <td class="text-right">{{ ucfirst(str_replace('_', ' ', $invoice->payment_method)) }}</td>
                                </tr>
                            @endif
                            @if($invoice->payment_details)
                                <tr>
                                    <td><strong>Payment Details:</strong></td>
                                    <td class="text-right">{{ $invoice->payment_details }}</td>
                                </tr>
                            @endif
                            @if($invoice->payment_method2)
                                <tr>
                                    <td><strong>Payment Method 2:</strong></td>
                                    <td class="text-right">{{ ucfirst(str_replace('_', ' ', $invoice->payment_method2)) }}</td>
                                </tr>
                            @endif
                            @if($invoice->payment_details2)
                                <tr>
                                    <td><strong>Payment Details 2:</strong></td>
                                    <td class="text-right">{{ $invoice->payment_details2 }}</td>
                                </tr>
                            @endif
                            @if($invoice->payment_date)
                                <tr>
                                    <td><strong>Payment Date:</strong></td>
                                    <td class="text-right">{{ \Carbon\Carbon::parse($invoice->payment_date)->format('M d, Y h:i A') }}</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>

                {{-- ================= PATHao / DELIVERY IDS ================= --}}
                @if($invoice->pathao_city_id || $invoice->pathao_zone_id || $invoice->pathao_area_id)
                    <h6 class="border-bottom pb-2">Pathao Delivery Info</h6>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <p class="mb-1"><strong>City ID:</strong> {{ $invoice->pathao_city_id ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Zone ID:</strong> {{ $invoice->pathao_zone_id ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Area ID:</strong> {{ $invoice->pathao_area_id ?? 'N/A' }}</p>
                        </div>
                    </div>
                @endif

                {{-- ================= SPECIAL INSTRUCTIONS + NOTES ================= --}}
                @if($invoice->special_instructions || $invoice->notes)
                    <div class="row mb-4">
                        @if($invoice->special_instructions)
                            <div class="col-md-6">
                                <h6 class="border-bottom pb-2">Special Instructions</h6>
                                <p class="bg-light p-2 rounded">{{ $invoice->special_instructions }}</p>
                            </div>
                        @endif
                        @if($invoice->notes)
                            <div class="col-md-6">
                                <h6 class="border-bottom pb-2">Notes</h6>
                                <p class="bg-light p-2 rounded">{{ $invoice->notes }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- ================= META INFO ================= --}}
                <div class="mt-3 text-muted small border-top pt-2">
                    <p class="mb-1">
                        <strong>Created By:</strong> {{ $invoice->creator->name ?? 'N/A' }} |
                        <strong>Team Member:</strong> {{ $invoice->teamMember->name ?? 'N/A' }}
                    </p>
                    <p class="mb-1">
                        <strong>Created At:</strong> {{ $invoice->created_at ? $invoice->created_at->format('M d, Y h:i A') : 'N/A' }} |
                        <strong>Updated At:</strong> {{ $invoice->updated_at ? $invoice->updated_at->format('M d, Y h:i A') : 'N/A' }}
                    </p>
                    @if($invoice->deleted_at)
                        <p class="mb-1 text-danger">
                            <strong>Deleted At:</strong> {{ $invoice->deleted_at->format('M d, Y h:i A') }}
                        </p>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
@endsection