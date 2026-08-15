@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fa fa-edit"></i> Edit Invoice - {{ $invoice->invoice_number }}
                </h5>
                <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa fa-arrow-left"></i> Back to Invoice
                </a>
            </div>
            <div class="card-body">

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Please fix the following:</strong>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form id="editInvoiceForm" action="{{ route('admin.invoices.update', $invoice->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Customer Section -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa fa-user"></i> Customer Information</h6>
                        </div>
                        <div class="card-body" id="customerSection">
                            <input type="hidden" name="customer_id" id="customerId" value="{{ $invoice->customer_id }}">

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <small id="selectedCustomer" class="text-muted">
                                        {{ $invoice->customer ? $invoice->customer->name . ' (Existing Customer)' : 'No customer linked' }}
                                    </small>
                                </div>

                                <div class="col-md-8 mb-3">
                                    <div class="d-flex flex-wrap align-items-center justify-content-md-end" style="gap: 1.25rem;">
                                        <div class="form-group mb-0" style="min-width: 190px;">
                                            <label class="mb-0 small text-muted">Assign to Team Member</label>
                                            <select name="team_id" id="teamMemberSelect" class="form-control">
                                                <option value="">-- Select Team Member --</option>
                                                @php
                                                    $currentUser = auth()->user();
                                                    $teamMembers = $currentUser->teamMembers()->with('defaultTeamMate')->get();
                                                @endphp
                                                @foreach($teamMembers as $member)
                                                    <option value="{{ $member->id }}" {{ old('team_id', $invoice->team_id) == $member->id ? 'selected' : '' }}>
                                                        {{ $member->name }} ({{ $member->email }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_wholesale" id="isWholesale" value="1"
                                                   {{ old('is_wholesale', $invoice->is_wholesale) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="isWholesale">Wholesale</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_inhouse_sale" id="isInhouseSale" value="1"
                                                   {{ old('is_inhouse_sale', $invoice->is_inhouse_sale) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="isInhouseSale">In-house Sale</label>
                                        </div>
                                        <div class="form-group mb-0" style="min-width: 190px;">
                                            <label class="mb-0 small text-muted">Courier</label>
                                            <select name="courier_name" id="courierName" class="form-control form-control-sm">
                                                @foreach(['Pathao','Steadfast','SA','SUNDORBAN','JANONI','REDEX','Exchange'] as $courier)
                                                    <option value="{{ $courier }}" {{ old('courier_name', $invoice->courier_name) == $courier ? 'selected' : '' }}>
                                                        {{ $courier }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Recipient Phone <span class="text-danger">*</span></label>
                                        <input type="text" name="recipient_phone" id="recipientPhone" class="form-control"
                                               value="{{ old('recipient_phone', $invoice->recipient_phone) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Recipient Name <span class="text-danger">*</span></label>
                                        <input type="text" name="recipient_name" id="recipientName" class="form-control"
                                               value="{{ old('recipient_name', $invoice->recipient_name) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Secondary Phone</label>
                                        <input type="text" name="recipient_secondary_phone" id="recipientPhone2" class="form-control"
                                               value="{{ old('recipient_secondary_phone', $invoice->recipient_secondary_phone) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Merchant Order ID</label>
                                        <input type="text" name="merchant_order_id" id="merchant_order_id" class="form-control"
                                               value="{{ old('merchant_order_id', $invoice->merchant_order_id) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Address</label>
                                        <textarea name="recipient_address" id="recipientAddress" rows="2" class="form-control">{{ old('recipient_address', $invoice->recipient_address) }}</textarea>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="card mb-3" id="deliveryAreaCard">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fa fa-map-marker-alt"></i> Delivery Area</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>City <span class="text-danger">*</span></label>
                                                        <select class="form-control select2-search" id="deliveryCitySelect" name="delivery_city">
                                                            <option value="">-- Select City --</option>
                                                        </select>
                                                        <div class="mt-1" id="cityLoading" style="display: none;">
                                                            <small class="text-primary"><i class="fa fa-spinner fa-spin"></i> Loading cities...</small>
                                                        </div>
                                                        <button type="button" id="refreshCities" class="btn btn-sm btn-link mt-1 p-0">
                                                            <i class="fa fa-redo"></i> Refresh Cities
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Zone <span class="text-danger">*</span></label>
                                                        <select class="form-control select2-search" id="deliveryZoneSelect" name="delivery_zone" disabled>
                                                            <option value="">-- Select Zone --</option>
                                                        </select>
                                                        <div class="mt-1" id="zoneLoading" style="display: none;">
                                                            <small class="text-primary"><i class="fa fa-spinner fa-spin"></i> Loading zones...</small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Area</label>
                                                        <select class="form-control select2-search" id="deliveryAreaSelect" name="delivery_area_id" disabled>
                                                            <option value="">-- Select Area --</option>
                                                        </select>
                                                        <div class="mt-1" id="areaLoading" style="display: none;">
                                                            <small class="text-primary"><i class="fa fa-spinner fa-spin"></i> Loading areas...</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label>Delivery Area <span class="text-danger">*</span></label>
                                                        <input type="text" name="delivery_area" id="deliveryArea" class="form-control"
                                                               value="{{ old('delivery_area', $invoice->delivery_area) }}"
                                                               placeholder="City, Zone, Area (auto-filled based on selection)" readonly>
                                                        <small class="text-muted">This field auto-fills when you select city, zone, and area above.</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Store & Delivery Info -->
                    <div class="row mb-3 pl-3 pr-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Store Location</label>
                                <select name="store_location" class="form-control" required>
                                    @foreach(['Faisal Textile FB','Faisal Textile Dhanmondi','Faisal Textile'] as $store)
                                        <option value="{{ $store }}" {{ old('store_location', $invoice->store_location) == $store ? 'selected' : '' }}>
                                            {{ $store }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Delivery Type <span class="text-danger">*</span></label>
                                <select name="delivery_type" class="form-control" required>
                                    @foreach(['Parcel','Express'] as $type)
                                        <option value="{{ $type }}" {{ old('delivery_type', $invoice->delivery_type) == $type ? 'selected' : '' }}>
                                            {{ $type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label>Status</label>
                                <select name="status" class="form-control" id="invoiceStatus">
                                    <option value="confirmed" {{ old('status', $invoice->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="pending" {{ old('status', $invoice->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="cancelled" {{ old('status', $invoice->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Items -->
                    <div class="card mb-3">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fa fa-box"></i> Items</h6>
                            <button type="button" class="btn btn-sm btn-primary" onclick="InvoiceEditItems.addItemRow()">
                                <i class="fa fa-plus"></i> Add Item
                            </button>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="min-width:160px;">Item Name</th>
                                        <th>Description</th>
                                        <th style="width:100px;">Weight (g)</th>
                                        <th style="width:90px;">Qty</th>
                                        <th style="width:120px;">Unit Price</th>
                                        <th style="width:110px;">Total</th>
                                        <th style="width:60px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Return Items -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <div class="form-check">
                              <input class="form-check-input" type="checkbox" name="has_return_items" id="hasReturnItems" value="1"
       {{ old('has_return_items', $invoice->has_return_items) ? 'checked' : '' }}>
                                <label class="form-check-label" for="hasReturnItems">
                                    <i class="fa fa-undo"></i> Has Return Items
                                </label>
                            </div>
                        </div>
                        <div class="card-body table-responsive" id="returnItemsSection" style="{{ $invoice->has_return_items ? '' : 'display:none;' }}">
                            <button type="button" id="addReturnBtn" class="btn btn-sm btn-secondary mb-2"
                                    style="{{ $invoice->has_return_items ? '' : 'display:none;' }}"
                                    onclick="ReturnItemsEdit.addReturnRow()">
                                <i class="fa fa-plus"></i> Add Return Item
                            </button>
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="min-width:160px;">Item Name</th>
                                        <th>Description</th>
                                        <th style="width:100px;">Weight (g)</th>
                                        <th style="width:90px;">Qty</th>
                                        <th style="width:120px;">Unit Price</th>
                                        <th style="width:110px;">Total</th>
                                        <th style="width:150px;">Return Reason</th>
                                        <th style="width:60px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="returnItemsBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payment Section -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa fa-money-bill"></i> Payment</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Payment Method</label>
                                        <select name="payment_method" id="paymentMethod" class="form-control" onchange="togglePaymentDetails()">
                                            <option value="">-- None --</option>
                                            <option value="bkash" {{ old('payment_method', $invoice->payment_method) == 'bkash' ? 'selected' : '' }}>bKash (Merchant)</option>
                                            <option value="bkash_personal" {{ old('payment_method', $invoice->payment_method) == 'bkash_personal' ? 'selected' : '' }}>bKash (Personal)</option>
                                            <option value="bank_transfer" {{ old('payment_method', $invoice->payment_method) == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                            <option value="cash" {{ old('payment_method', $invoice->payment_method) == 'cash' ? 'selected' : '' }}>Cash</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Advance / Paid Amount</label>
                                        <input type="number" step="0.01" min="0" name="paid_amount" id="paidAmount" class="form-control"
                                               value="{{ old('paid_amount', $invoice->paid_amount) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Amount to Collect</label>
                                        <input type="number" step="0.01" min="0" name="amount_to_collect" id="amountToCollect" class="form-control"
                                               value="{{ old('amount_to_collect', $invoice->amount_to_collect) }}">
                                    </div>
                                </div>

                                <div class="col-md-6" id="bkashDetails" style="display:none;">
                                    <div class="form-group">
                                        <label>bKash Transaction ID</label>
                                        <input type="text" name="bkash_transaction" class="form-control"
                                               value="{{ old('bkash_transaction', $invoice->payment_method == 'bkash' ? $invoice->payment_details : '') }}">
                                    </div>
                                </div>
                                <div class="col-md-6" id="bkashPersonalDetails" style="display:none;">
                                    <div class="form-group">
                                        <label>bKash Personal Transaction ID</label>
                                        <input type="text" name="bkash_personal_transaction" class="form-control"
                                               value="{{ old('bkash_personal_transaction', $invoice->payment_method == 'bkash_personal' ? $invoice->payment_details : '') }}">
                                    </div>
                                </div>
                                <div class="col-md-6" id="bankDetails" style="display:none;">
                                    <div class="form-group">
                                        <label>Bank Transfer Details</label>
                                        <input type="text" name="bank_transfer_details" class="form-control"
                                               value="{{ old('bank_transfer_details', $invoice->payment_method == 'bank_transfer' ? $invoice->payment_details : '') }}">
                                    </div>
                                </div>
                                <div class="col-md-6" id="cashDetails" style="display:none;">
                                    <div class="form-group">
                                        <label>Cash Amount</label>
                                        <input type="text" name="cash_amount" class="form-control"
                                               value="{{ old('cash_amount', $invoice->payment_method == 'cash' ? $invoice->payment_details : '') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notes / Special Instructions -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Special Instructions</label>
                                <textarea name="special_instructions" rows="2" class="form-control">{{ old('special_instructions', $invoice->special_instructions) }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Notes</label>
                                <textarea name="notes" rows="2" class="form-control">{{ old('notes', $invoice->notes) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa fa-calculator"></i> Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Delivery Charge <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" name="delivery_charge" id="deliveryCharge"
                                               class="form-control" value="{{ old('delivery_charge', $invoice->delivery_charge) }}" required>
                                    </div>
                                    <table class="table table-sm mb-0">
                                        <tr>
                                            <td>Total Quantity</td>
                                            <td class="text-right"><span id="totalQuantityDisplay">0</span></td>
                                        </tr>
                                        <tr>
                                            <td>Subtotal</td>
                                            <td class="text-right"><span id="subtotalDisplay">৳0</span></td>
                                        </tr>
                                        <tr id="returnSubtotalRow" style="display:none;">
                                            <td>Return Items (<span id="returnItemsCount">0</span>)</td>
                                            <td class="text-right">- <span id="returnSubtotal">৳0</span></td>
                                        </tr>
                                        <tr>
                                            <td>Delivery Charge</td>
                                            <td class="text-right"><span id="deliveryAmount">৳0</span></td>
                                        </tr>
                                        <tr id="totalWeightRow" style="display:none;">
                                            <td>Total Weight</td>
                                            <td class="text-right"><span id="totalWeight">0 kg</span></td>
                                        </tr>
                                        <tr class="font-weight-bold">
                                            <td>Total</td>
                                            <td class="text-right"><span id="total">৳0</span></td>
                                        </tr>
                                        <tr id="advancePaymentRow" style="display:none;">
                                            <td>Advance Paid</td>
                                            <td class="text-right"><span id="advanceAmount">৳0</span></td>
                                        </tr>
                                        <tr class="font-weight-bold text-danger">
                                            <td>Due</td>
                                            <td class="text-right"><span id="dueAmount">৳0</span></td>
                                        </tr>
                                    </table>
                                    <input type="hidden" id="subtotalInput">
                                    <input type="hidden" id="totalInput">
                                    <input type="hidden" id="returnSubtotalInput">
                                    <input type="hidden" id="dueInput">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 text-right">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fa fa-save"></i> Update Invoice
                            </button>
                            <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="btn btn-secondary btn-lg">
                                Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<link href="{{ asset('css/pos-invoice.css') }}" rel="stylesheet">

@php
    $invoiceData = [
        'id' => $invoice->id,
        'customer_id' => $invoice->customer_id,
        'pathao_city_id' => $invoice->pathao_city_id,
        'pathao_zone_id' => $invoice->pathao_zone_id,
        'pathao_area_id' => $invoice->pathao_area_id,
        'delivery_charge' => $invoice->delivery_charge,
        'is_inhouse_sale' => (bool) $invoice->is_inhouse_sale,
        'payment_method' => $invoice->payment_method,
        'items' => $invoice->items->map(function ($item) {
            return [
                'id' => $item->id,
                'item_name' => $item->item_name,
                'description' => $item->description,
                'weight' => $item->weight,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ];
        })->values(),
        'return_items' => $invoice->returnItems->map(function ($ri) {
            return [
                'id' => $ri->id,
                'item_name' => $ri->item_name,
                'description' => $ri->description,
                'weight' => $ri->weight,
                'quantity' => $ri->quantity,
                'unit_price' => $ri->unit_price,
                'return_reason' => $ri->return_reason,
                'return_note' => $ri->return_note,
            ];
        })->values(),
        'has_return_items' => (bool) $invoice->has_return_items,
    ];
@endphp
<script>
    window.__INVOICE_DATA__ = @json($invoiceData);
</script>

<!-- Reused unchanged from POS -->
<script src="{{ asset('js/invoice-pos-calculations.js') }}"></script>
<script src="{{ asset('js/invoice-pos-payments.js') }}"></script>
<script src="{{ asset('js/invoice-pos-delivery.js') }}"></script>
<script src="{{ asset('js/inhouse-sale-toggle.js') }}"></script>
<script src="{{ asset('js/delivery-charge-calculator.js') }}"></script>

<!-- Edit-specific versions (track DB ids for update/delete) -->
<script src="{{ asset('js/invoice-edit-items.js') }}"></script>
<script src="{{ asset('js/invoice-edit-return-items.js') }}"></script>
<script src="{{ asset('js/invoice-edit-init.js') }}"></script>

@endsection