@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fa fa-cash-register"></i> POS - Create Invoice
                </h5>
            </div>
            <div class="card-body">
                <form id="posForm" action="{{ route('invoices.store-pos') }}" method="POST">
                    @csrf
                    
                    <!-- Customer Section -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fa fa-user"></i> Customer Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#customerModal">
                                        <i class="fa fa-search"></i> Select Customer
                                    </button>
                                    <input type="hidden" name="customer_id" id="customerId" required>
                                    <small class="text-muted ml-2" id="selectedCustomer">No customer selected</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Recipient Name <span class="text-danger">*</span></label>
                                        <input type="text" name="recipient_name" id="recipientName" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Recipient Phone <span class="text-danger">*</span></label>
                                        <input type="text" name="recipient_phone" id="recipientPhone" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Secondary Phone</label>
                                        <input type="text" name="recipient_secondary_phone" id="recipientPhone2" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Address <span class="text-danger">*</span></label>
                                        <textarea name="recipient_address" id="recipientAddress" rows="2" class="form-control" required></textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Delivery Area <span class="text-danger">*</span></label>
                                        <input type="text" name="delivery_area" id="deliveryArea" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Store & Delivery Info -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Store Location</label>
                                <select name="store_location" class="form-control" required>
                                    <option value="Dhanmondi">Dhanmondi</option>
                                    <option value="Chawk Bazar">Chawk Bazar</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Delivery Type <span class="text-danger">*</span></label>
                                <select name="delivery_type" class="form-control" required>
                                    <option value="Parcel">Parcel</option>
                                    <option value="Express">Express</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    @include('invoices.partials.items-table')
                    @include('invoices.partials.payment-section')
                    @include('invoices.partials.summary-section')

                    <!-- Form Actions -->
                    <div class="row mt-3">
                        <div class="col-md-12 text-right">
                            <button type="button" class="btn btn-success btn-lg" onclick="InvoicePOS.saveAndPrint()">
                                <i class="fa fa-save"></i> Save & Print Invoice
                            </button>
                            <button type="button" class="btn btn-secondary btn-lg" onclick="InvoicePOS.resetForm()">
                                Clear Form
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Include Modals -->
@include('invoices.partials.customer-select')
@include('invoices.partials.print-modal')


<style>
    .customer-row:hover { background-color: #f8f9fa; cursor: pointer; }
    #itemsTable input { border: 1px solid #dee2e6; }
    #itemsTable input:focus { background: #fff; border-color: #80bdff; box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25); }
    .summary-card { border: 2px solid #007bff; }
    .item-row:hover { background-color: #f8f9ff; }
    .loading-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        justify-content: center;
        align-items: center;
    }
    .loading-spinner {
        color: white;
        font-size: 24px;
    }
</style>
<script src="{{ asset('js/invoice-pos.js') }}"></script>

<script>
    function togglePaymentDetails() {
        InvoicePOS.togglePaymentDetails();
    }
    
    function printInvoice() {
        InvoicePOS.printInvoice();
    }
    
    function createNewInvoice() {
        InvoicePOS.createNewInvoice();
    }
</script>
@endsection