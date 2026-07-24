// Fetches and applies the correct delivery charge whenever the zone
// (manual select, customer auto-fill, or address auto-fill) or the
// item quantity changes.
const DeliveryChargeManager = {
    debounceTimer: null,
    lastZoneId: null,
    lastQuantity: null,

    init: function() {
        // Fires on ANY change to #deliveryZoneSelect — covers manual
        // selection, DeliveryAreaAutoSelector (customer), and
        // AddressCityZoneAutofill (typed address), since all of them
        // do $('#deliveryZoneSelect').val(x).trigger('change').
        $(document).on('change', '#deliveryZoneSelect', () => {
            const zoneId = $('#deliveryZoneSelect').val();
            if (zoneId) this.refresh(zoneId);
        });

        // Quantity changed on an existing item row
        $(document).on('change', '.quantity', () => this.refreshDebounced());

        // Item added / removed (see itemsChanged trigger below)
        $(document).on('itemsChanged', () => this.refreshDebounced());
    },

 refreshDebounced: function() {
    clearTimeout(this.debounceTimer);
    this.debounceTimer = setTimeout(() => {
        // NEW: same guard here
        if ($('#isInhouseSale').is(':checked')) return;

        const zoneId = $('#deliveryZoneSelect').val();
        if (zoneId) this.refresh(zoneId);
    }, 400);
},

    getTotalQuantity: function() {
        let total = 0;
        $('.item-row .quantity').each(function() {
            total += parseInt($(this).val()) || 0;
        });
        return total || 1;
    },

   refresh: function(zoneId) {
    // NEW: never touch delivery charge during an in-house sale
    if ($('#isInhouseSale').is(':checked')) return;

    const totalQuantity = this.getTotalQuantity();

    if (zoneId == this.lastZoneId && totalQuantity === this.lastQuantity) return;

    this.lastZoneId = zoneId;
    this.lastQuantity = totalQuantity;

    $.ajax({
        url: `/admin/get-delivery-charge/${zoneId}/${totalQuantity}`,
        method: 'GET',
        success: (response) => {
            if (response.success && response.data) {
                this.applyCharge(response.data.delivery_charge);
            }
        },
        error: (xhr) => console.error('Delivery charge fetch error:', xhr)
    });
},

    applyCharge: function(charge) {
        $('#deliveryCharge').val(charge);

        // Keep the special-instructions note's charge number in sync,
        // but only if the note still matches the default template —
        // never overwrite text the user manually edited.
        const $notes = $('textarea[name="special_instructions"]');
        const currentText = $notes.val() || '';
        const genericPattern = /Return korle delivery charge \d+(\.\d+)? tk niben/;

        if (!currentText.trim() || genericPattern.test(currentText)) {
            $notes.val(
                `Return korle delivery charge ${charge} tk niben ( আনুষাঙ্গিক কোনো ইসু থাকলে প্যানেলে মেসেজ দিবেন। নাম্বারে যোগাযোগ করার সময় - সকাল ১১.৩০ থেকে রাত ৯ টার মধ্যে)`
            );
        }

        // Recalculate subtotal/total/due with the new delivery charge
        if (typeof InvoiceCalculations !== 'undefined') {
            InvoiceCalculations.calculateTotals();
        }
    },

    reset: function() {
        this.lastZoneId = null;
        this.lastQuantity = null;
    }
};

$(document).ready(() => DeliveryChargeManager.init());
window.DeliveryChargeManager = DeliveryChargeManager;