<!-- Print Invoice Modal -->
<div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-labelledby="printModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="printModalLabel">
                    <i class="fa fa-print"></i> Print Invoice
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <iframe id="invoiceIframe" src="" style="width: 100%; height: 500px; border: none;"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-success" onclick="printInvoice()">
                    <i class="fa fa-print"></i> Print Now
                </button>
                <button type="button" class="btn btn-primary" onclick="createNewInvoice()">
                    <i class="fa fa-plus"></i> New Invoice
                </button>
            </div>
        </div>
    </div>
</div>