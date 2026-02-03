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

    .fraud-check-alert {
        border-left: 4px solid;
        animation: fadeIn 0.5s ease-in;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .courier-card {
        transition: transform 0.3s ease;
    }
    .courier-card:hover {
        transform: translateY(-5px);
    }
    #advancePaymentRow td {
        color: #28a745 !important;
        font-weight: bold;
    }
    .text-success {
        color: #28a745 !important;
    }
    .customer-row.bg-success {
        background-color: #d4edda !important;
    }
    .customer-update-flag {
        color: #856404;
        background-color: #fff3cd;
        border: 1px solid #ffeaa7;
        padding: 5px;
        border-radius: 4px;
        font-size: 12px;
        margin-top: 5px;
    }
      .select2-container--bootstrap4 .select2-selection--single {
        height: calc(2.25rem + 2px);
        padding: .375rem .75rem;
        font-size: 1rem;
        line-height: 1.5;
        border: 1px solid #ced4da;
        border-radius: .25rem;
    }
    
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        padding-left: 0;
        color: #495057;
    }
    
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        height: calc(2.25rem + 2px);
    }
    
    .select2-container--bootstrap4 .select2-dropdown {
        border-color: #ced4da;
        border-radius: .25rem;
    }
    
    .select2-container--bootstrap4 .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da;
        border-radius: .25rem;
        padding: .375rem .75rem;
    }
    
    .select2-results__option {
        padding: 8px 12px;
    }
    
    .select2-results__option--highlighted {
        background-color: #007bff;
        color: white;
    }
    
    /* Ensure dropdown appears above modal */
    .select2-container {
        z-index: 1060 !important;
    }
    
    .modal-open .select2-container {
        z-index: 1061 !important;
    }
    .form-reset-success {
    background-color: #d4edda;
    color: #155724;
    padding: 10px;
    border-radius: 4px;
    margin-bottom: 15px;
    border-left: 4px solid #28a745;
    animation: slideIn 0.5s ease;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Print modal styling */
#iframe-container {
    position: relative;
    min-height: 500px;
}

#iframe-container.loading::after {
    content: 'Loading invoice for printing...';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 18px;
    color: #666;
}
.loading-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}

.loading-spinner {
    background: white;
    padding: 30px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.3);
    color: #333;
    font-size: 18px;
}

.loading-spinner i {
    margin-bottom: 15px;
}

.form-reset-success {
    animation: slideIn 0.5s ease;
    margin-bottom: 20px;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

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