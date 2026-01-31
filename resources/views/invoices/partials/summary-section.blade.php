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
                           value="60" min="0" step="0.01" id="deliveryCharge">
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
    
    <div class="col-md-4">
        <div class="card summary-card">
            <div class="card-body">
                <h6 class="card-title text-center">Invoice Summary</h6>
                <table class="table table-sm table-borderless">
                    <tr>
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
                    <tr>
                        <td>Amount to Collect:</td>
                        <td class="text-right">
                            <input type="number" name="amount_to_collect" 
                                   class="form-control form-control-sm text-right" 
                                   value="0" min="0" step="0.01" id="amountToCollect"
                                   onchange="updateDueAmount()">
                        </td>
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