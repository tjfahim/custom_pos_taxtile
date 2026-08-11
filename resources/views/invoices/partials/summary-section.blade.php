<!-- Summary Section -->
<div class="row mt-3">
    <div class="col-md-8">
        <!-- Delivery Charge -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">Delivery Charge</h6>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Delivery Charge (৳)</label>
                    <input type="number" name="delivery_charge" class="form-control" 
                           value="150" min="0" step="0.01" id="deliveryCharge" 
                           onchange="updateSpecialInstructions(); InvoiceCalculations.calculateTotals()"
                           oninput="updateSpecialInstructions()">
                </div>
            </div>
        </div>
            
        <div class="form-group">
            <label>Special Instructions</label>
            <textarea name="special_instructions" rows="2" class="form-control"></textarea>
        </div>
        <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" rows="2" class="form-control"></textarea>
        </div>
    </div>
    
    <!-- Invoice Summary -->
    <div class="col-md-4">
        <div class="card summary-card">
            <div class="card-body">
                <h6 class="card-title text-center">Invoice Summary</h6>
                <table class="table table-sm table-borderless">
                    <!-- Total Items Row -->
                    <tr id="totalQuantityRow">
                        <td>
                            <strong>Total Items:</strong>
                            <span class="badge badge-primary" id="totalQuantityDisplay">0</span>
                        </td>
                        <td class="text-right" id="subtotalDisplay">৳0</td>
                    </tr>
                    
                    <!-- Total Weight Row -->
                    <tr id="totalWeightRow" style="display: none;">
                        <td>Total Weight:</td>
                        <td class="text-right" id="totalWeight">0 kg</td>
                    </tr>
                    
                    <!-- Return Items Row -->
                    <tr id="returnSubtotalRow" style="display: none;" class="text-danger">
                        <td>
                            Return Items: 
                            <span class="badge badge-danger" id="returnItemsCount">0</span>
                        </td>
                        <td class="text-right" id="returnSubtotal">৳0</td>
                    </tr>
                    
                    <!-- Delivery Row -->
                    <tr>
                        <td>Delivery:</td>
                        <td class="text-right" id="deliveryAmount">৳150</td>
                    </tr>
                    
                    <!-- Total Row -->
                    <tr class="border-top">
                        <td><strong>Total:</strong></td>
                        <td class="text-right"><strong id="total">৳0</strong></td>
                    </tr>
                    
                    <!-- Advance Payment Row -->
                    <tr id="advancePaymentRow" style="display: none;">
                        <td>Advance:</td>
                        <td class="text-right text-success" id="advanceAmount">৳0</td>
                    </tr>
                    
                    <!-- Due Row -->
                    <tr class="border-top">
                        <td><strong>Due:</strong></td>
                        <td class="text-right"><strong id="dueAmount">৳0</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>