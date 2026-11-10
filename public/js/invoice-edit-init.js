// invoice-edit-init.js
// Populates the full POS-style edit form from window.__INVOICE_DATA__
// (set inline in edit.blade.php) once all the reused POS modules
// (InvoiceDelivery, InhouseSaleToggle, InvoicePayments, DeliveryChargeManager,
// InvoiceCalculations) plus the edit-specific item modules have loaded.

(function() {
    // Poll for a condition (used to wait on async-loaded <option> lists)
    // before running a callback. Gives up silently after maxTries.
    function waitFor(check, callback, maxTries = 40, interval = 250) {
        let tries = 0;
        const timer = setInterval(() => {
            tries++;
            if (check()) {
                clearInterval(timer);
                callback();
            } else if (tries >= maxTries) {
                clearInterval(timer);
                console.warn('invoice-edit-init: gave up waiting for condition after', tries, 'tries');
            }
        }, interval);
    }

    $(document).ready(function() {
        const invoice = window.__INVOICE_DATA__;
        if (!invoice) return;

        // ---- Items ----
        $('#itemsBody').empty();
        InvoiceEditItems.itemCount = 0;
        if (invoice.items && invoice.items.length) {
            invoice.items.forEach(item => InvoiceEditItems.addItemRow(item));
        } else {
            InvoiceEditItems.addItemRow();
        }

        // ---- Return items ----
        if (invoice.has_return_items && invoice.return_items && invoice.return_items.length) {
            ReturnItemsEdit.suppressAutoAdd = true;
            $('#hasReturnItems').prop('checked', true).trigger('change');
            ReturnItemsEdit.suppressAutoAdd = false;
            invoice.return_items.forEach(ri => ReturnItemsEdit.addReturnRow(ri));
        }

        // ---- In-house sale toggle (must run before delivery area logic,
        // since it disables/enables the city/zone/area selects) ----
        if (typeof InhouseSaleToggle !== 'undefined') {
            InhouseSaleToggle.apply();
        }

        // ---- Second advance payment (in-house sales only) ----
        if (invoice.is_inhouse_sale && typeof InvoicePayments !== 'undefined') {
            $('#paidAmount2').val(invoice.paid_amount2 || 0);
            if (invoice.payment_method2) {
                $('#paymentMethod2').val(invoice.payment_method2);
                // togglePaymentDetails2() clears the detail inputs, so refill afterwards
                InvoicePayments.togglePaymentDetails2();

                const detailSelectors2 = {
                    'bkash':          '[name="bkash_transaction2"]',
                    'bkash_personal': '[name="bkash_personal_transaction2"]',
                    'bank_transfer':  '[name="bank_transfer_details2"]',
                    'cash':           '[name="cash_amount2"]'
                };
                const sel2 = detailSelectors2[invoice.payment_method2];
                if (sel2 && invoice.payment_details2) {
                    $(sel2).val(invoice.payment_details2);
                }
            }
        }

        // ---- Wholesale discount ----
        if (typeof WholesaleDiscount !== 'undefined') {
            WholesaleDiscount.load(invoice.discount_type, invoice.discount_value);
        }

        // ---- Payment method detail fields ----
        if (typeof InvoicePayments !== 'undefined' && invoice.payment_method) {
            InvoicePayments.togglePaymentDetails();
        }

        // ---- Delivery area cascade (skip entirely for in-house sales) ----
        if (!invoice.is_inhouse_sale && invoice.pathao_city_id) {
            waitFor(
                () => $('#deliveryCitySelect option').length > 1,
                () => {
                    $('#deliveryCitySelect').val(invoice.pathao_city_id).trigger('change');

                    if (invoice.pathao_zone_id) {
                        waitFor(
                            () => $('#deliveryZoneSelect option').length > 1,
                            () => {
                                $('#deliveryZoneSelect').val(invoice.pathao_zone_id).trigger('change');

                                if (invoice.pathao_area_id) {
                                    waitFor(
                                        () => $('#deliveryAreaSelect option').length > 1,
                                        () => {
                                            $('#deliveryAreaSelect').val(invoice.pathao_area_id).trigger('change');
                                            finalizeDeliveryCharge();
                                        }
                                    );
                                } else {
                                    finalizeDeliveryCharge();
                                }
                            }
                        );
                    } else {
                        finalizeDeliveryCharge();
                    }
                }
            );
        } else {
            finalizeDeliveryCharge();
        }

        // DeliveryChargeManager auto-recalculates the delivery charge whenever
        // the zone select changes — which just happened above. Re-assert the
        // invoice's originally saved charge afterwards so we don't silently
        // overwrite a manually-set amount just by opening the edit page.
        function finalizeDeliveryCharge() {
            setTimeout(() => {
                // trigger('input') also refreshes the charge number inside Special Instructions
                $('#deliveryCharge').val(invoice.delivery_charge).trigger('input');
                if (typeof InvoiceCalculations !== 'undefined') {
                    InvoiceCalculations.calculateTotals();
                }
            }, 600);
        }
    });

    // Append deleted-row ids just before the form actually submits
    $(document).on('submit', '#editInvoiceForm', function() {
        $(this).find('.deleted-ids-container').remove();

        const container = $('<div class="deleted-ids-container" style="display:none"></div>');
        if (window.InvoiceEditItems) {
            container.append(InvoiceEditItems.getDeletedIdsInputs());
        }
        if (window.ReturnItemsEdit) {
            container.append(ReturnItemsEdit.getDeletedIdsInputs());
        }
        $(this).append(container);

        return true;
    });
})();