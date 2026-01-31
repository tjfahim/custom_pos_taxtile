<!-- Payment Section -->
<div class="card mt-3">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="fa fa-credit-card"></i> Advance Payment (Optional)</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Advance Amount</label>
                    <input type="number" name="paid_amount" 
                           class="form-control" 
                           value="0" min="0" step="0.01" id="paidAmount"
                           onchange="InvoicePOS.updateDueAmount()">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" class="form-control" id="paymentMethod" onchange="InvoicePOS.togglePaymentDetails()">
                        <option value="">No Advance Payment</option>
                        <option value="bkash">Bkash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group" id="bkashDetails" style="display: none;">
                    <label>Bkash Transaction Details</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Last 4 digits</span>
                        </div>
                        <input type="text" name="payment_details" class="form-control" 
                               placeholder="Last 4 digits of mobile number" maxlength="4">
                    </div>
                </div>
                <div class="form-group" id="bankDetails" style="display: none;">
                    <label>Bank Transfer Details</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Bank Name</span>
                        </div>
                        <input type="text" name="payment_details" class="form-control" 
                               placeholder="Bank name & account details">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>