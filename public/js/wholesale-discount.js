// Wholesale Discount Module
// Shows the discount card only while "Wholesale" is ticked and keeps the two
// hidden inputs that are actually submitted (discount_type / discount_value) in
// sync with the visible controls. Used by both the POS create page and the edit page.
const WholesaleDiscount = {

    init: function () {
        $(document).on('change', '#isWholesale', () => this.apply());
        $(document).on('change', '#discountPreset', () => this.syncFromControls());
        $(document).on('input change', '#discountFixedAmount', () => this.syncFromControls());
        this.apply();
    },

    // Show/hide the card according to the Wholesale checkbox
    apply: function () {
        const on = $('#isWholesale').is(':checked');
        $('#wholesaleDiscountSection').toggle(on);

        if (!on) {
            // Not wholesale → no discount, and never submit stale values
            $('#discountPreset').val('');
            $('#discountFixedAmount').val('');
        }
        this.syncFromControls();
    },

    syncFromControls: function () {
        const on = $('#isWholesale').is(':checked');
        const preset = $('#discountPreset').val() || '';
        let type = '';
        let value = 0;

        if (on && preset.charAt(0) === 'p') {
            type = 'percent';
            value = parseFloat(preset.slice(1)) || 0;
        } else if (on && preset === 'fixed') {
            type = 'fixed';
            value = parseFloat($('#discountFixedAmount').val()) || 0;
        }

        $('#discountFixedWrap').toggle(on && preset === 'fixed');
        $('#discountType').val(type);
        $('#discountValue').val(value);

        if (typeof InvoiceCalculations !== 'undefined') {
            InvoiceCalculations.calculateTotals();
        }
    },

    // Used by the edit page to restore a saved discount
    load: function (type, value) {
        value = parseFloat(value) || 0;

        if (type === 'percent' && value > 0) {
            const key = 'p' + value;
            if (!$('#discountPreset option[value="' + key + '"]').length) {
                // A percentage that is not one of the presets (e.g. saved earlier) — keep it selectable
                $('#discountPreset option[value="fixed"]').before(
                    $('<option>', { value: key, text: value + '%' })
                );
            }
            $('#discountPreset').val(key);
        } else if (type === 'fixed' && value > 0) {
            $('#discountPreset').val('fixed');
            $('#discountFixedAmount').val(value);
        } else {
            $('#discountPreset').val('');
            $('#discountFixedAmount').val('');
        }
        this.syncFromControls();
    }
};

window.WholesaleDiscount = WholesaleDiscount;
$(document).ready(() => WholesaleDiscount.init());
