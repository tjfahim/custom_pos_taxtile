<!-- Wholesale Discount — only visible while the "Wholesale" checkbox is ticked -->
<div class="card mt-3 mb-3" id="wholesaleDiscountSection" style="display: none;">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i class="fa fa-percent"></i> Wholesale Discount
            <small class="text-muted">(optional)</small>
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group mb-md-0">
                    <label>Discount</label>
                    <select id="discountPreset" class="form-control">
                        <option value="">No Discount</option>
                        <option value="p20">20%</option>
                        <option value="p30">30%</option>
                        <option value="p50">50%</option>
                        <option value="fixed">Fixed Amount (৳)</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4" id="discountFixedWrap" style="display: none;">
                <div class="form-group mb-md-0">
                    <label>Discount Amount (৳)</label>
                    <input type="number" id="discountFixedAmount" class="form-control" min="0" step="0.01" placeholder="e.g. 500">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-0">
                    <label>Discount Applied</label>
                    <div class="form-control-plaintext font-weight-bold text-danger" id="discountPreview">৳0</div>
                </div>
            </div>
        </div>
        <small class="text-muted">Percentage discounts are taken off the items total (after any return items). Delivery charge is not discounted.</small>

        <!-- These two are what actually get submitted -->
        <input type="hidden" name="discount_type" id="discountType" value="">
        <input type="hidden" name="discount_value" id="discountValue" value="0">
    </div>
</div>
