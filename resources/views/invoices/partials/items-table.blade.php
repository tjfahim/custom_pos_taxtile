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