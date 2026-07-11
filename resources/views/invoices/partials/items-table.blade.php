<!-- Items Section -->
<div class="card mb-3">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fa fa-box"></i> Items</h6>
        <button type="button" class="btn btn-sm btn-primary" onclick="InvoiceItems.addItemRow()">
    <i class="fa fa-plus"></i> Add Item
</button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0" id="itemsTable">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 25%">Item Name</th>
                        <th style="width: 20%">Description</th>
                        <th style="width: 80px">Weight (g)</th>
                        <th style="width: 80px">Qty</th>
                        <th style="width: 100px">Unit Price</th>
                        <th style="width: 100px">Total</th>
                        <th style="width: 50px">Action</th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    <!-- Items will be added here by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Return Items Section -->
<div class="card mb-3">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fa fa-undo"></i> Return Items</h6>
        <div>
            <div class="custom-control custom-switch mr-2 d-inline-block">
                <input type="checkbox" class="custom-control-input" id="hasReturnItems" name="has_return_items" value="1">
                <label class="custom-control-label" for="hasReturnItems">Has Return Items</label>
            </div>
            <button type="button" class="btn btn-sm btn-warning" onclick="ReturnItems.addReturnRow()" id="addReturnBtn" style="display: none;">
                <i class="fa fa-plus"></i> Add Return Item
            </button>
        </div>
    </div>
    <div class="card-body p-0" id="returnItemsSection" style="display: none;">
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0" id="returnItemsTable">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 20%">Item Name</th>
                        <th style="width: 15%">Description</th>
                        <th style="width: 80px">Weight (g)</th>
                        <th style="width: 80px">Qty</th>
                        <th style="width: 100px">Unit Price</th>
                        <th style="width: 100px">Total</th>
                        <th style="width: 15%">Return Reason</th>
                        <th style="width: 50px">Action</th>
                    </tr>
                </thead>
                <tbody id="returnItemsBody">
                    <!-- Return items will be added here by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</div>