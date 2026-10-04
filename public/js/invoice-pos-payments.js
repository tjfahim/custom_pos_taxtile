const InvoicePayments = {

    togglePaymentDetails: function () {
        const method = $('#paymentMethod').val();

        $('#bkashDetails').hide();
        $('#bkashPersonalDetails').hide();
        $('#bankDetails').hide();
        $('#cashDetails').hide();

        $('[name="bkash_transaction"]').val('');
        $('[name="bkash_personal_transaction"]').val('');
        $('[name="bank_transfer_details"]').val('');
        $('[name="cash_amount"]').val('');

        if (method === 'bkash') {
            $('#bkashDetails').show();
        } else if (method === 'bkash_personal') {
            $('#bkashPersonalDetails').show();
        } else if (method === 'bank_transfer') {
            $('#bankDetails').show();
        } else if (method === 'cash') {
            $('#cashDetails').show();
        }

        this.updateRequiredState();
    },

    togglePaymentDetails2: function () {
        const method = $('#paymentMethod2').val();

        $('#bkashDetails2').hide();
        $('#bkashPersonalDetails2').hide();
        $('#bankDetails2').hide();
        $('#cashDetails2').hide();

        $('[name="bkash_transaction2"]').val('');
        $('[name="bkash_personal_transaction2"]').val('');
        $('[name="bank_transfer_details2"]').val('');
        $('[name="cash_amount2"]').val('');

        if (method === 'bkash') {
            $('#bkashDetails2').show();
        } else if (method === 'bkash_personal') {
            $('#bkashPersonalDetails2').show();
        } else if (method === 'bank_transfer') {
            $('#bankDetails2').show();
        } else if (method === 'cash') {
            $('#cashDetails2').show();
        }
    },

    /**
     * Required-field manager.
     * When ANY payment method is selected:
     *   - the visible detail input becomes required
     *   - the payment_date becomes required
     * When no method is selected:
     *   - nothing is required
     * Never touches hidden fields, so the browser won't complain about
     * "An invalid form control is not focusable".
     */
    updateRequiredState: function () {
        const method = $('#paymentMethod').val();

        // 1. Reset all first
        $('[name="bkash_transaction"], ' +
          '[name="bkash_personal_transaction"], ' +
          '[name="bank_transfer_details"], ' +
          '[name="cash_amount"]').prop('required', false);

        $('#paymentDate').prop('required', false);

        // 2. No method selected → nothing required
        if (!method) {
            return;
        }

        // 3. Method selected → payment date required
        $('#paymentDate').prop('required', true);

        // 4. And the matching detail field required
        if (method === 'bkash') {
            $('[name="bkash_transaction"]').prop('required', true);
        } else if (method === 'bkash_personal') {
            $('[name="bkash_personal_transaction"]').prop('required', true);
        } else if (method === 'bank_transfer') {
            $('[name="bank_transfer_details"]').prop('required', true);
        } else if (method === 'cash') {
            $('[name="cash_amount"]').prop('required', true);
        }
    }
};

window.togglePaymentDetails  = function () { InvoicePayments.togglePaymentDetails();  };
window.togglePaymentDetails2 = function () { InvoicePayments.togglePaymentDetails2(); };