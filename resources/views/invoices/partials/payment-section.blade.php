<!-- Payment Section -->
<div class="card mt-3">
    <div class="card-header bg-light">
        <h6 class="mb-0"><i class="fa fa-credit-card"></i> Advance Payment (Optional)</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>Advance Amount</label>
                    <input type="number" name="paid_amount"
                           class="form-control"
                           value="0" min="0" id="paidAmount"
                           onchange="InvoiceCalculations.updateDueAmount()">
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Payment Method</label>
                    <select name="payment_method" class="form-control" id="paymentMethod" onchange="InvoicePayments.togglePaymentDetails()">
                        <option value="">No Advance Payment</option>
                        <option value="bkash">Bkash (Merchant)</option>
                        <option value="bkash_personal">Bkash Personal</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Payment Date</label>
                    <input type="datetime-local" name="payment_date" id="paymentDate" class="form-control"
                           value="{{ old('payment_date', now()->format('Y-m-d\TH:i')) }}">
                </div>
            </div>
            <div class="col-md-3">
                <!-- Bkash Merchant Details -->
                <div class="form-group" id="bkashDetails" style="display: none;">
                    <label>Bkash Merchant Transaction</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">TrxID</span>
                        </div>
                        <input type="text" name="bkash_transaction" class="form-control"
                               placeholder="Bkash transaction ID" maxlength="20">
                    </div>
                </div>

                <!-- Bkash Personal Details -->
                <div class="form-group" id="bkashPersonalDetails" style="display: none;">
                    <label>Bkash Personal Transaction</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Personal</span>
                        </div>
                        <input type="text" name="bkash_personal_transaction" class="form-control"
                               placeholder="Personal Details" maxlength="20">
                    </div>
                </div>

                <!-- Bank Transfer Details -->
                <div class="form-group" id="bankDetails" style="display: none;">
                    <label>Bank Transfer Details</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Bank Name</span>
                        </div>
                        <input type="text" name="bank_transfer_details" class="form-control"
                               placeholder="Bank name & account details">
                    </div>
                </div>

                <!-- Cash Details -->
                <div id="cashDetails" style="display: none;">
                    <div class="form-group">
                        <label>Received By</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text"></span>
                            </div>
                            <input type="text" name="cash_amount" class="form-control"
                                   placeholder="Enter amount receiver name" min="0">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Second Payment Section — only shown/used for In-house Sale -->
<div class="card mt-3" id="secondPaymentSection" style="display: none;">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i class="fa fa-credit-card"></i> Second Payment
            <small class="text-muted">(In-house Sale, optional)</small>
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>2nd Advance Amount</label>
                    <input type="number" name="paid_amount2"
                           class="form-control"
                           value="0" min="0" id="paidAmount2"
                           onchange="InvoiceCalculations.updateDueAmount()">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>2nd Payment Method</label>
                    <select name="payment_method2" class="form-control" id="paymentMethod2" onchange="InvoicePayments.togglePaymentDetails2()">
                        <option value="">No 2nd Payment</option>
                        <option value="bkash">Bkash (Merchant)</option>
                        <option value="bkash_personal">Bkash Personal</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group" id="bkashDetails2" style="display: none;">
                    <label>Bkash Merchant Transaction (2nd)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">TrxID</span>
                        </div>
                        <input type="text" name="bkash_transaction2" class="form-control"
                               placeholder="Bkash transaction ID" maxlength="20">
                    </div>
                </div>

                <div class="form-group" id="bkashPersonalDetails2" style="display: none;">
                    <label>Bkash Personal Transaction (2nd)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Personal</span>
                        </div>
                        <input type="text" name="bkash_personal_transaction2" class="form-control"
                               placeholder="Personal Data" maxlength="20">
                    </div>
                </div>

                <div class="form-group" id="bankDetails2" style="display: none;">
                    <label>Bank Transfer Details (2nd)</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Bank Name</span>
                        </div>
                        <input type="text" name="bank_transfer_details2" class="form-control"
                               placeholder="Bank name & account details">
                    </div>
                </div>

                <div id="cashDetails2" style="display: none;">
                    <div class="form-group">
                        <label>Received By (2nd)</label>
                        <input type="text" name="cash_amount2" class="form-control"
                               placeholder="Enter amount receiver name">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>