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
                           value="150" min="0" step="0.01" id="deliveryCharge" onchange="InvoiceCalculations.calculateTotals()">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label>Special Instructions</label>
            <textarea name="special_instructions" rows="2" class="form-control" value="">Return korle delivery charge 150 tk niben ( আনুষাঙ্গিক কোনো ইসু থাকলে প্যানেলে মেসেজ দিবেন। নাম্বারে যোগাযোগ করার সময় - সকাল ১১.৩০ থেকে রাত ৯ টার মধ্যে)</textarea>
        </div>
        <div class="form-group">
            <label>Notes</label>
            <textarea name="notes" rows="2" class="form-control"></textarea>
        </div>
        
    </div>
    
    <!-- In your summary-section.blade.php -->
<div class="col-md-4">
    <div class="card summary-card">
        <div class="card-body">
            <h6 class="card-title text-center">Invoice Summary</h6>
            <table class="table table-sm table-borderless">
                <!-- Total Quantity Row -->
                <tr id="totalQuantityRow">
                    <td>Total Items:</td>
                    <td class="text-right" id="totalQuantity">0</td>
                </tr>
                <!-- Total Weight Row (in kg) -->
                <tr id="totalWeightRow" style="display: none;">
                    <td>Total Weight:</td>
                    <td class="text-right" id="totalWeight">0.000 kg</td>
                </tr>
                <tr class="border-top">
                    <td>Subtotal:</td>
                    <td class="text-right" id="subtotal">৳0.00</td>
                </tr>
                <tr>
                    <td>Delivery:</td>
                    <td class="text-right" id="deliveryAmount">৳60.00</td>
                </tr>
                <tr class="border-top">
                    <td><strong>Total:</strong></td>
                    <td class="text-right"><strong id="total">৳0.00</strong></td>
                </tr>
                
                <!-- Advance Payment Row (Hidden by default) -->
                <tr id="advancePaymentRow" style="display: none;">
                    <td>Advance:</td>
                    <td class="text-right text-success" id="advanceAmount">৳0.00</td>
                </tr>
                
                <tr class="border-top">
                    <td><strong>Due:</strong></td>
                    <td class="text-right"><strong id="dueAmount">৳0.00</strong></td>
                </tr>
            </table>
        </div>
    </div>
</div>
</div>