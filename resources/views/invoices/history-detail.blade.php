@extends('admin.layouts.master')

@section('main_content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2>
                        <i class="fas fa-history text-primary"></i>
                        Edit History Details
                        <small class="text-muted ms-2">
                            Invoice #{{ $invoice->invoice_number }}
                        </small>
                    </h2>
                    <p class="text-muted mb-0">
                        <i class="far fa-calendar-alt"></i>
                        Created: {{ $invoice->created_at->format('M d, Y h:i A') }}
                        @if($invoice->updated_at != $invoice->created_at)
                            | Last Updated: {{ $invoice->updated_at->format('M d, Y h:i A') }}
                        @endif
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.invoices.history.list') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to History List
                    </a>
                    <a href="{{ route('admin.invoices.show', $invoice->id) }}" class="btn btn-primary">
                        <i class="fas fa-file-invoice"></i> View Invoice
                    </a>
                    <a href="{{ route('admin.invoices.edit', $invoice->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Invoice
                    </a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <h6 class="text-white-50 mb-1">Total Edits</h6>
                            <h2 class="mb-0">{{ $statistics['total_edits'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <h6 class="text-white-50 mb-1">Status Changes</h6>
                            <h2 class="mb-0">{{ $statistics['status_changes'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <h6 class="text-white-50 mb-1">Created By</h6>
                            <h6 class="mb-0">{{ $statistics['created_by'] }}</h6>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <h6 class="text-white-50 mb-1">Unique Editors</h6>
                            <h2 class="mb-0">{{ $statistics['unique_editors'] }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-secondary text-white">
                        <div class="card-body text-center">
                            <h6 class="text-white-50 mb-1">Last Editor</h6>
                            <h6 class="mb-0">{{ $statistics['last_editor'] }}</h6>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card bg-dark text-white">
                        <div class="card-body text-center">
                            <h6 class="text-white-50 mb-1">Last Edited</h6>
                            <h6 class="mb-0">{{ $statistics['last_edited'] ? $statistics['last_edited']->diffForHumans() : 'Never' }}</h6>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Invoice Information -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Invoice Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="text-muted">Invoice Number</label>
                            <p class="fw-bold">#{{ $invoice->invoice_number }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted">Status</label>
                            <p>
                                <span class="badge bg-{{ $invoice->status == 'confirmed' ? 'success' :
                                                       ($invoice->status == 'pending' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted">Customer</label>
                            <p class="fw-bold">{{ $invoice->recipient_name }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted">Phone</label>
                            <p>{{ $invoice->recipient_phone }}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="text-muted">Delivery Type</label>
                            <p>{{ $invoice->delivery_type }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted">Courier</label>
                            <p>{{ $invoice->courier_name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted">Store Location</label>
                            <p>{{ $invoice->store_location }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted">Delivery Area</label>
                            <p>{{ $invoice->delivery_area ?? 'N/A' }}</p>
                        </div>
                    </div>
                    @if($invoice->is_wholesale || $invoice->is_inhouse_sale)
                    <div class="row">
                        <div class="col-md-12">
                            <div class="d-flex gap-3">
                                @if($invoice->is_wholesale)
                                    <span class="badge bg-purple">Wholesale</span>
                                @endif
                                @if($invoice->is_inhouse_sale)
                                    <span class="badge bg-orange">In-house Sale</span>
                                @endif
                                @if($invoice->has_return_items)
                                    <span class="badge bg-danger">Has Return Items</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Current Items -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-box text-primary"></i> Current Items
                            <span class="badge bg-secondary ms-2">{{ $invoice->items->count() }}</span>
                        </h5>
                        <span class="text-muted">
                            Total Qty: <strong>{{ $invoice->items->sum('quantity') }}</strong>
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 25%">Item Name</th>
                                    <th style="width: 20%">Description</th>
                                    <th style="width: 12%">Weight (g)</th>
                                    <th style="width: 10%">Qty</th>
                                    <th style="width: 15%">Unit Price</th>
                                    <th style="width: 13%">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->items as $index => $item)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td><strong>{{ $item->item_name }}</strong></td>
                                        <td>{{ $item->description ?? '-' }}</td>
                                        <td class="text-center">{{ $item->weight ?? 500 }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end">৳{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end fw-bold">৳{{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-3">No items found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="6" class="text-end fw-bold">Total Quantity:</td>
                                    <td class="text-end fw-bold">{{ $invoice->items->sum('quantity') }}</td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-end fw-bold">Subtotal:</td>
                                    <td class="text-end fw-bold text-primary">৳{{ number_format($invoice->items->sum('total_price'), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Return Items -->
            @if($invoice->returnItems->count() > 0)
            <div class="card mt-3">
                <div class="card-header bg-warning">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-undo text-warning"></i> Return Items
                            <span class="badge bg-secondary ms-2">{{ $invoice->returnItems->count() }}</span>
                        </h5>
                        <span class="text-muted">
                            Total Qty: <strong>{{ $invoice->returnItems->sum('quantity') }}</strong>
                        </span>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%">#</th>
                                    <th style="width: 20%">Item Name</th>
                                    <th style="width: 15%">Description</th>
                                    <th style="width: 10%">Weight (g)</th>
                                    <th style="width: 8%">Qty</th>
                                    <th style="width: 12%">Unit Price</th>
                                    <th style="width: 12%">Total</th>
                                    <th style="width: 18%">Return Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->returnItems as $index => $returnItem)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td><strong>{{ $returnItem->item_name }}</strong></td>
                                        <td>{{ $returnItem->description ?? '-' }}</td>
                                        <td class="text-center">{{ $returnItem->weight ?? 500 }}</td>
                                        <td class="text-center">{{ $returnItem->quantity }}</td>
                                        <td class="text-end">৳{{ number_format($returnItem->unit_price, 2) }}</td>
                                        <td class="text-end fw-bold text-danger">-৳{{ number_format($returnItem->total_price, 2) }}</td>
                                        <td>
                                            <span class="badge bg-warning">{{ $returnItem->return_reason ?? 'N/A' }}</span>
                                            @if($returnItem->return_note)
                                                <br><small class="text-muted">{{ $returnItem->return_note }}</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Total Return Quantity:</td>
                                    <td class="text-center fw-bold">{{ $invoice->returnItems->sum('quantity') }}</td>
                                    <td colspan="3"></td>
                                </tr>
                                <tr>
                                    <td colspan="6" class="text-end fw-bold">Return Subtotal:</td>
                                    <td class="text-end fw-bold text-danger">-৳{{ number_format($invoice->returnItems->sum('total_price'), 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Summary Section -->
            <div class="card mt-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator"></i> Invoice Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td><strong>Subtotal (Items):</strong></td>
                                    <td class="text-end">৳{{ number_format($invoice->items->sum('total_price'), 2) }}</td>
                                </tr>
                                @if($invoice->returnItems->count() > 0)
                                <tr>
                                    <td><strong>Return Items:</strong></td>
                                    <td class="text-end text-danger">- ৳{{ number_format($invoice->returnItems->sum('total_price'), 2) }}</td>
                                </tr>
                                <tr>
                                    <td><strong>After Returns:</strong></td>
                                    <td class="text-end">৳{{ number_format($invoice->subtotal, 2) }}</td>
                                </tr>
                                @else
                                <tr>
                                    <td><strong>Subtotal (After Returns):</strong></td>
                                    <td class="text-end">৳{{ number_format($invoice->subtotal, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td><strong>Delivery Charge:</strong></td>
                                    <td class="text-end">৳{{ number_format($invoice->delivery_charge, 2) }}</td>
                                </tr>
                                <tr class="bg-light">
                                    <td><strong>Total:</strong></td>
                                    <td class="text-end"><strong>৳{{ number_format($invoice->total, 2) }}</strong></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <td><strong>Paid Amount:</strong></td>
                                    <td class="text-end">৳{{ number_format($invoice->paid_amount, 2) }}</td>
                                </tr>
                                <tr class="bg-danger text-white">
                                    <td><strong>Due Amount:</strong></td>
                                    <td class="text-end"><strong>৳{{ number_format($invoice->due_amount, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Payment Status:</strong></td>
                                    <td class="text-end">
                                        <span class="badge bg-{{ $invoice->payment_status == 'paid' ? 'success' :
                                               ($invoice->payment_status == 'partial' ? 'warning' : 'danger') }}"
                                             style="font-size: 14px; padding: 8px 15px;">
                                            <i class="fas
                                                fa-{{ $invoice->payment_status == 'paid' ? 'check-circle' :
                                                   ($invoice->payment_status == 'partial' ? 'clock' : 'times-circle') }}">
                                            </i>
                                            {{ ucfirst($invoice->payment_status) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Payment Method:</strong></td>
                                    <td class="text-end">
                                        @if($invoice->payment_method)
                                            <span class="badge bg-info">{{ ucfirst($invoice->payment_method) }}</span>
                                            @if($invoice->payment_details)
                                                <br><small class="text-muted">{{ $invoice->payment_details }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="d-flex gap-3 flex-wrap">
                                <div class="bg-light p-2 rounded">
                                    <small class="text-muted">Total Items</small>
                                    <br>
                                    <strong>{{ $invoice->items->sum('quantity') }}</strong>
                                </div>
                                @if($invoice->returnItems->count() > 0)
                                <div class="bg-light p-2 rounded">
                                    <small class="text-muted">Return Items</small>
                                    <br>
                                    <strong class="text-danger">{{ $invoice->returnItems->sum('quantity') }}</strong>
                                </div>
                                @endif
                                <div class="bg-light p-2 rounded">
                                    <small class="text-muted">Total Weight</small>
                                    <br>
                                    <strong>{{ $invoice->total_weight ? number_format($invoice->total_weight / 1000, 2) : '0' }} kg</strong>
                                </div>
                                <div class="bg-light p-2 rounded">
                                    <small class="text-muted">Invoice Date</small>
                                    <br>
                                    <strong>{{ $invoice->invoice_date ? $invoice->invoice_date->format('M d, Y') : 'N/A' }}</strong>
                                </div>
                                <div class="bg-light p-2 rounded">
                                    <small class="text-muted">Confirmed At</small>
                                    <br>
                                    <strong>{{ $invoice->confirmed_at ? $invoice->confirmed_at->format('M d, Y h:i A') : 'N/A' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Full Edit History Timeline -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-stream"></i> All Edits
                        <span class="badge bg-secondary ms-2">{{ $invoice->editHistories->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($invoice->editHistories->count() > 0)
                        <div class="timeline">
                            @foreach($invoice->editHistories as $history)
                                <div class="timeline-item">
                                    <div class="timeline-marker
                                        @if($history->action_type == 'create') bg-success
                                        @elseif($history->action_type == 'status_change') bg-warning
                                        @else bg-primary
                                        @endif
                                    ">
                                        <i class="fas
                                            @if($history->action_type == 'create') fa-plus
                                            @elseif($history->action_type == 'status_change') fa-exchange-alt
                                            @else fa-edit
                                            @endif
                                        "></i>
                                    </div>

                                    <div class="timeline-content">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-header bg-light">
                                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                                    <div>
                                                        <h6 class="mb-0">
                                                            <i class="fas fa-user-circle text-primary"></i>
                                                            <strong>{{ $history->user_name ?? 'System' }}</strong>
                                                            <span class="text-muted">
                                                                {{ $history->action_type == 'create' ? 'created' :
                                                                   ($history->action_type == 'status_change' ? 'changed status of' : 'edited') }}
                                                                this invoice
                                                            </span>
                                                        </h6>
                                                    </div>
                                                    <div class="mt-1 mt-md-0">
                                                        <small class="text-muted">
                                                            <i class="far fa-clock"></i>
                                                            {{ $history->created_at->format('F d, Y h:i:s A') }}
                                                            <span class="ms-2 badge bg-light text-dark">
                                                                {{ $history->created_at->diffForHumans() }}
                                                            </span>
                                                        </small>
                                                        @if($history->ip_address)
                                                            <span class="badge bg-secondary ms-2">
                                                                <i class="fas fa-network-wired"></i>
                                                                {{ $history->ip_address }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card-body">
                                                @if($history->has_any_changes)

                                                    {{-- Scalar field changes (name, phone, delivery charge, etc) --}}
                                                    @if(count($history->formatted_changes) > 0)
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-hover mb-0">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th style="width: 25%">Field</th>
                                                                        <th style="width: 35%">Old Value</th>
                                                                        <th style="width: 35%">New Value</th>
                                                                        <th style="width: 5%">Status</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($history->formatted_changes as $change)
                                                                        <tr>
                                                                            <td>
                                                                                <strong>{{ $change['label'] }}</strong>
                                                                                @if(strpos($change['label'], 'Status') !== false)
                                                                                    <span class="badge bg-warning ms-1">Status</span>
                                                                                @endif
                                                                            </td>
                                                                            <td class="text-danger">
                                                                                @if($change['old'] && $change['old'] != '-')
                                                                                    <del>{{ $change['old'] }}</del>
                                                                                @else
                                                                                    <span class="text-muted">-</span>
                                                                                @endif
                                                                            </td>
                                                                            <td class="text-success">
                                                                                @if($change['new'] && $change['new'] != '-')
                                                                                    <ins>{{ $change['new'] }}</ins>
                                                                                @else
                                                                                    <span class="text-muted">-</span>
                                                                                @endif
                                                                            </td>
                                                                            <td>
                                                                                @if($change['old'] != $change['new'])
                                                                                    <span class="badge bg-success">Changed</span>
                                                                                @else
                                                                                    <span class="badge bg-secondary">Unchanged</span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif

                                                    {{-- Invoice item changes --}}
                                                    @if(!empty($history->item_changes))
                                                        <div class="{{ count($history->formatted_changes) > 0 ? 'mt-3' : '' }}">
                                                            <h6 class="text-primary mb-2">
                                                                <i class="fas fa-box"></i> Item Changes
                                                                <span class="badge bg-secondary">{{ count($history->item_changes) }}</span>
                                                            </h6>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-bordered mb-0">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th style="width: 12%">Change</th>
                                                                            <th style="width: 28%">Item</th>
                                                                            <th>Details</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($history->item_changes as $change)
                                                                            <tr>
                                                                                <td>
                                                                                    @if($change['type'] === 'added')
                                                                                        <span class="badge bg-success">Added</span>
                                                                                    @elseif($change['type'] === 'removed')
                                                                                        <span class="badge bg-danger">Removed</span>
                                                                                    @else
                                                                                        <span class="badge bg-warning">Updated</span>
                                                                                    @endif
                                                                                </td>
                                                                                <td><strong>{{ $change['item_name'] }}</strong></td>
                                                                                <td>
                                                                                    @if(in_array($change['type'], ['added', 'removed']))
                                                                                        Qty: {{ $change['quantity'] }} &times;
                                                                                        ৳{{ number_format($change['unit_price'], 2) }}
                                                                                        = <strong>৳{{ number_format($change['total_price'], 2) }}</strong>
                                                                                    @else
                                                                                        <ul class="mb-0 ps-3">
                                                                                            @foreach($change['changes'] as $field => $vals)
                                                                                                <li>
                                                                                                    <strong>{{ ucfirst(str_replace('_', ' ', $field)) }}:</strong>
                                                                                                    <span class="text-danger"><del>{{ $vals['old'] ?? '-' }}</del></span>
                                                                                                    &rarr;
                                                                                                    <span class="text-success"><ins>{{ $vals['new'] ?? '-' }}</ins></span>
                                                                                                </li>
                                                                                            @endforeach
                                                                                        </ul>
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    {{-- Return item changes --}}
                                                    @if(!empty($history->return_item_changes))
                                                        <div class="mt-3">
                                                            <h6 class="text-warning mb-2">
                                                                <i class="fas fa-undo"></i> Return Item Changes
                                                                <span class="badge bg-secondary">{{ count($history->return_item_changes) }}</span>
                                                            </h6>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-bordered mb-0">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th style="width: 12%">Change</th>
                                                                            <th style="width: 28%">Item</th>
                                                                            <th>Details</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($history->return_item_changes as $change)
                                                                            <tr>
                                                                                <td>
                                                                                    @if($change['type'] === 'added')
                                                                                        <span class="badge bg-success">Added</span>
                                                                                    @elseif($change['type'] === 'removed')
                                                                                        <span class="badge bg-danger">Removed</span>
                                                                                    @else
                                                                                        <span class="badge bg-warning">Updated</span>
                                                                                    @endif
                                                                                </td>
                                                                                <td><strong>{{ $change['item_name'] }}</strong></td>
                                                                                <td>
                                                                                    @if(in_array($change['type'], ['added', 'removed']))
                                                                                        Qty: {{ $change['quantity'] }} &times;
                                                                                        ৳{{ number_format($change['unit_price'], 2) }}
                                                                                        = <strong>৳{{ number_format($change['total_price'], 2) }}</strong>
                                                                                    @else
                                                                                        <ul class="mb-0 ps-3">
                                                                                            @foreach($change['changes'] as $field => $vals)
                                                                                                <li>
                                                                                                    <strong>{{ ucfirst(str_replace('_', ' ', $field)) }}:</strong>
                                                                                                    <span class="text-danger"><del>{{ $vals['old'] ?? '-' }}</del></span>
                                                                                                    &rarr;
                                                                                                    <span class="text-success"><ins>{{ $vals['new'] ?? '-' }}</ins></span>
                                                                                                </li>
                                                                                            @endforeach
                                                                                        </ul>
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    @endif

                                                @else
                                                    <p class="text-muted mb-0">
                                                        <i class="fas fa-info-circle"></i>
                                                        No detailed field changes recorded for this action.
                                                    </p>
                                                @endif

                                                @if($history->action_type == 'status_change' && isset($history->old_values['status']))
                                                    <div class="mt-3 p-2 bg-light rounded">
                                                        <small class="text-muted">Status Change:</small>
                                                        <span class="badge bg-{{ $history->old_values['status'] == 'confirmed' ? 'success' :
                                                               ($history->old_values['status'] == 'pending' ? 'warning' : 'danger') }} ms-1"
                                                             style="font-size: 12px; padding: 5px 12px;">
                                                            <i class="fas fa-{{ $history->old_values['status'] == 'confirmed' ? 'check' :
                                                               ($history->old_values['status'] == 'pending' ? 'clock' : 'times') }}">
                                                            </i>
                                                            {{ ucfirst($history->old_values['status'] ?? '') }}
                                                        </span>
                                                        <i class="fas fa-arrow-right mx-2 text-muted"></i>
                                                        <span class="badge bg-{{ $history->new_values['status'] == 'confirmed' ? 'success' :
                                                               ($history->new_values['status'] == 'pending' ? 'warning' : 'danger') }}"
                                                             style="font-size: 12px; padding: 5px 12px;">
                                                            <i class="fas fa-{{ $history->new_values['status'] == 'confirmed' ? 'check' :
                                                               ($history->new_values['status'] == 'pending' ? 'clock' : 'times') }}">
                                                            </i>
                                                            {{ ucfirst($history->new_values['status'] ?? '') }}
                                                        </span>
                                                        @if(isset($history->old_values['status']) && isset($history->new_values['status']) &&
                                                             $history->old_values['status'] != $history->new_values['status'])
                                                            <span class="badge bg-warning ms-2">Status Updated</span>
                                                        @endif
                                                    </div>
                                                @endif

                                                @if($history->action_type == 'create')
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            <i class="fas fa-info-circle"></i>
                                                            Invoice was created with initial values
                                                            @if(!empty($history->item_changes))
                                                                and {{ count($history->item_changes) }} item(s)
                                                            @endif
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No Edit History</h5>
                            <p class="text-muted">This invoice has not been edited yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Export Options -->
            @if($invoice->editHistories->count() > 0)
                <div class="mt-3 d-flex gap-2">
                    <button class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print History
                    </button>
                    <button class="btn btn-outline-success" onclick="exportHistory()">
                        <i class="fas fa-file-excel"></i> Export as CSV
                    </button>
                    <button class="btn btn-outline-info" onclick="exportJSON()">
                        <i class="fas fa-file-code"></i> Export as JSON
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 50px;
}

.timeline-item {
    position: relative;
    padding-bottom: 30px;
    padding-left: 20px;
    margin-left: 10px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -30px;
    top: 30px;
    bottom: 0;
    width: 2px;
    background: linear-gradient(to bottom, #e9ecef 0%, #dee2e6 100%);
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-marker {
    position: absolute;
    left: -40px;
    top: 20px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    z-index: 1;
}

.timeline-content .card {
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
    transition: box-shadow 0.3s ease;
}

.timeline-content .card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.bg-purple {
    background-color: #6f42c1;
    color: white;
}

.bg-orange {
    background-color: #fd7e14;
    color: white;
}

@media print {
    .btn, .no-print {
        display: none !important;
    }
    .timeline-content .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
    .container-fluid {
        max-width: 100% !important;
        padding: 0 !important;
    }
    .card {
        break-inside: avoid;
        page-break-inside: avoid;
    }
    .timeline-item {
        break-inside: avoid;
        page-break-inside: avoid;
    }
}
</style>

@push('scripts')
<script>
function exportHistory() {
    let csv = 'Invoice #,{{ $invoice->invoice_number }}\n';
    csv += 'Date,User,Action,Changes\n';

    @foreach($invoice->editHistories as $history)
        csv += `"{{ $history->created_at->format('M d, Y h:i A') }}","{{ $history->user_name ?? 'System' }}","{{ $history->action_type }}","`;

        @if(count($history->formatted_changes) > 0)
            @foreach($history->formatted_changes as $change)
                csv += `{{ $change['label'] }}: {{ $change['old'] ?? '-' }} -> {{ $change['new'] ?? '-' }}\\n`;
            @endforeach
        @endif
        @if(!empty($history->item_changes))
            @foreach($history->item_changes as $change)
                csv += `Item ({{ $change['type'] }}): {{ $change['item_name'] }}\\n`;
            @endforeach
        @endif
        @if(!empty($history->return_item_changes))
            @foreach($history->return_item_changes as $change)
                csv += `Return Item ({{ $change['type'] }}): {{ $change['item_name'] }}\\n`;
            @endforeach
        @endif
        @if(!$history->has_any_changes)
            csv += 'No changes';
        @endif

        csv += `"\n`;
    @endforeach

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `invoice_${'{{ $invoice->invoice_number }}'}_history.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

function exportJSON() {
    const data = {
        invoice: @json($invoice),
        statistics: @json($statistics),
        histories: @json($invoice->editHistories),
        items: @json($invoice->items),
        return_items: @json($invoice->returnItems)
    };

    const json = JSON.stringify(data, null, 2);
    const blob = new Blob([json], { type: 'application/json' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `invoice_${'{{ $invoice->invoice_number }}'}_history.json`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}
</script>
@endpush
@endsection