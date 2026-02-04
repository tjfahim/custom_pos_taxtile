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
    <div class="card-body" id="customerSection">
        <div class="row">
            <div class="col-md-12 mb-3">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#customerModal">
                    <i class="fa fa-search"></i> Select Customer
                </button>
                <input type="hidden" name="customer_id" id="customerId">
                <div class="mt-2">
                    <small id="selectedCustomer" class="text-muted">No customer selected. Enter phone number to auto-detect or select manually.</small>
                </div>
            </div>
        </div>
  
                            <div class="row">
                                    <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Recipient Phone <span class="text-danger">*</span></label>
                                        <input type="text" name="recipient_phone" id="recipientPhone" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Recipient Name <span class="text-danger">*</span></label>
                                        <input type="text" name="recipient_name" id="recipientName" class="form-control" required>
                                    </div>
                                </div>
                            
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Secondary Phone</label>
                                        <input type="text" name="recipient_secondary_phone" id="recipientPhone2" class="form-control">
                                    </div>
                                </div>
                                 <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Merchant Order Id <span class="text-danger"></span></label>
                                        <input type="text" name="merchant_order_id" id="merchant_order_id" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Address <span class="text-danger">*</span></label>
                                        <textarea name="recipient_address" id="recipientAddress" rows="2" class="form-control" required></textarea>
                                    </div>
                                </div>
                               <div class="col-md-12">
    <div class="col-md-12">
    <div class="card mb-3">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="fa fa-map-marker-alt"></i> Delivery Area</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <!-- City Selection -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label>City <span class="text-danger">*</span></label>
                        <select class="form-control select2-search" id="deliveryCitySelect" name="delivery_city">
                            <option value="">-- Select City --</option>
                        </select>
                        <div class="mt-1" id="cityLoading" style="display: none;">
                            <small class="text-primary">
                                <i class="fa fa-spinner fa-spin"></i> Loading cities...
                            </small>
                        </div>
                        <button type="button" id="refreshCities" class="btn btn-sm btn-link mt-1 p-0">
                            <i class="fa fa-redo"></i> Refresh Cities
                        </button>
                    </div>
                </div>
                
                <!-- Zone Selection -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Zone</label>
                        <select class="form-control select2-search" id="deliveryZoneSelect" name="delivery_zone" disabled>
                            <option value="">-- Select Zone --</option>
                        </select>
                        <div class="mt-1" id="zoneLoading" style="display: none;">
                            <small class="text-primary">
                                <i class="fa fa-spinner fa-spin"></i> Loading zones...
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Area Selection -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Area <span class="text-danger">*</span></label>
                        <select class="form-control select2-search" id="deliveryAreaSelect" name="delivery_area_id" disabled>
                            <option value="">-- Select Area --</option>
                        </select>
                        <div class="mt-1" id="areaLoading" style="display: none;">
                            <small class="text-primary">
                                <i class="fa fa-spinner fa-spin"></i> Loading areas...
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Manual Delivery Area Input (as fallback) -->
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Delivery Area <span class="text-danger">*</span></label>
                        <input type="text" name="delivery_area" id="deliveryArea" class="form-control" 
                               placeholder="City, Zone, Area (auto-filled based on selection)" required readonly>
                        <small class="text-muted">This field will auto-fill when you select city, zone, and area above</small>
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
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Store Location</label>
                                <select name="store_location" class="form-control" required>
                                    <option value="Faisal Textile FB">Faisal Textile FB</option>
                                    <option value="Faisal Textile Dhanmondi">Faisal Textile Dhanmondi</option>
                                    <option value="Faisal Textile">Faisal Textile</option>
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
                            <button type="button" class="btn btn-success btn-lg" onclick="saveAndPrint()">
                                <i class="fa fa-save"></i> Save & Print Invoice
                            </button>
                            <button type="button" class="btn btn-secondary btn-lg" onclick="resetForm()">
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


<link href="{{ asset('css/pos-invoice.css') }}" rel="stylesheet">

<!-- Load all JavaScript modules in correct order -->
<script src="{{ asset('js/invoice-pos-items.js') }}"></script>
<script src="{{ asset('js/invoice-pos-calculations.js') }}"></script>
<script src="{{ asset('js/invoice-pos-payments.js') }}"></script>
<script src="{{ asset('js/invoice-pos-delivery.js') }}"></script>
<script src="{{ asset('js/fraud-check.js') }}"></script>
<script src="{{ asset('js/customer-auto.js') }}"></script>
<script src="{{ asset('js/invoice-pos-form-handler.js') }}"></script>
<script src="{{ asset('js/invoice-pos-core.js') }}"></script>

<!-- Global helper functions for inline onclick handlers -->
<script>
    // Make these functions globally available
    function togglePaymentDetails() {
        if (typeof InvoicePOS !== 'undefined' && InvoicePOS.Payments) {
            InvoicePOS.Payments.togglePaymentDetails();
        }
    }
    
    function printInvoice() {
        if (typeof InvoicePOS !== 'undefined' && InvoicePOS.FormHandler) {
            InvoicePOS.FormHandler.printInvoice();
        }
    }
    
    function createNewInvoice() {
        if (typeof InvoicePOS !== 'undefined' && InvoicePOS.FormHandler) {
            InvoicePOS.FormHandler.createNewInvoice();
        }
    }
    
    function saveAndPrint() {
        if (typeof InvoicePOS !== 'undefined' && InvoicePOS.FormHandler) {
            InvoicePOS.FormHandler.saveAndPrint();
        } else {
            console.error('InvoicePOS modules not loaded');
            alert('System not ready. Please refresh the page.');
        }
    }
    
    function resetForm() {
        if (typeof InvoicePOS !== 'undefined' && InvoicePOS.FormHandler) {
            InvoicePOS.FormHandler.resetForm();
        }
    }
</script>

@endsection