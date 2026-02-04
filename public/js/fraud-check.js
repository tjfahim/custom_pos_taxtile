// fraud-check-simple.js
class FraudChecker {
    constructor() {
        this.currentPhone = null;
        this.currentData = null;
        this.hasInvoiceToday = false;
        this.init();
    }

    init() {
        $('#recipientPhone').on('input', this.debounce(this.handlePhoneInput.bind(this), 800));
        
        // Add validation to form submission
        $('#posForm').on('submit', this.validateForm.bind(this));
    }

    debounce(func, wait) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func(...args), wait);
        };
    }

    handlePhoneInput() {
        const phone = $('#recipientPhone').val().trim().replace(/\D/g, '');
        this.currentPhone = phone;
        this.hasInvoiceToday = false; // Reset flag
        
        if (!/^01[3-9]\d{8}$/.test(phone)) {
            this.clearFraudDisplay();
            return;
        }
        
        // First check if this phone has an invoice today
        this.checkTodayInvoice(phone);
    }

    async checkTodayInvoice(phone) {
        this.showLoading('Checking for today\'s orders...');
        
        try {
            // Check if phone has invoice today
            const todayRes = await fetch(`/check-phone-today/${phone}`);
            const todayData = await todayRes.json();
            
            if (todayData.error) throw new Error(todayData.error);
            
            this.hasInvoiceToday = todayData.has_invoice_today;
            
            if (this.hasInvoiceToday) {
                // Show warning about today's invoice
                this.displayTodayInvoiceWarning(todayData);
                
                // Still check fraud history
                this.checkPhoneFraud(phone);
            } else {
                // No invoice today, proceed with fraud check
                this.checkPhoneFraud(phone);
            }
        } catch (error) {
            console.error('Today invoice check error:', error);
            // Still try fraud check if today check fails
            this.checkPhoneFraud(phone);
        } finally {
            this.hideLoading();
        }
    }

    async checkPhoneFraud(phone) {
        this.showLoading('Checking fraud history...');
        
        try {
            const res = await fetch(`/admin/check-phone/${phone}`);
            const data = await res.json();
            
            if (data.error) throw new Error(data.error);
            
            this.currentData = data;
            this.displayFraudResults(this.currentData);
        } catch (error) {
            console.error('Fraud check error:', error);
            this.showError('Error loading fraud data');
        } finally {
            this.hideLoading();
        }
    }

    displayTodayInvoiceWarning(todayData) {
        let container = $('#todayInvoiceWarning');
        if (!container.length) {
            container = $(`
                <div id="todayInvoiceWarning" class="mt-2 alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <div id="todayWarningContent"></div>
                </div>
            `).insertAfter($('#recipientPhone').closest('.form-group'));
        }
        
        const html = `
            <div class="d-flex align-items-center">
                <i class="fa fa-ban mr-2"></i>
                <div>
                    <strong>BLOCKED:</strong> This phone number has already placed an order today.
                    
                    ${todayData.last_invoice ? 
                        `<small class="d-block text-muted mt-1">Last order: ${todayData.last_invoice}</small>` : 
                        ''}
                    <small class="d-block text-danger mt-1">
                        <i class="fa fa-exclamation-circle"></i> Cannot create another invoice with this phone today.
                    </small>
                </div>
            </div>
        `;
        
        $('#todayWarningContent').html(html);
    }

    displayFraudResults(data) {
        let container = $('#fraudCheckContainer');
        if (!container.length) {
            container = $(`
                <div id="fraudCheckContainer" class="mt-2 p-2 bg-light rounded">
                    <div id="fraudCheckResults" class="small"></div>
                </div>
            `).insertAfter($('#recipientPhone').closest('.form-group'));
        }
        
        $('#fraudCheckResults').html(this.resultsHtml(data));
    }

    resultsHtml(data) {
        const total = data.total_parcels || 0;
        const delivered = data.total_delivered || 0;
        const cancelled = data.total_cancel || 0;
        const rate = total > 0 ? Math.round((delivered / total) * 100) : 0;
        const riskColor = rate >= 90 ? 'success' : rate >= 70 ? 'warning' : 'danger';
        
        let html = '';
        
        // Show high-risk warning if fraud rate is low
        if (rate < 70 && total > 0) {
            html += `
                <div class="alert alert-warning alert-sm p-2 mb-2">
                    <i class="fa fa-exclamation-triangle mr-1"></i>
                    <strong>High Risk:</strong> ${rate}% success rate. Proceed with caution.
                </div>
            `;
        }
        
        html += `
            <div class="row">
                <div class="col-12 mb-1">
                    <span class="text-muted">Fraud History:</span>
                    <span class="badge badge-${riskColor} ml-1">${rate}%</span>
                    <small class="text-muted ml-2">${total} orders</small>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="d-flex flex-wrap">
        `;

        // Show all couriers in a compact format
        if (data.apis) {
            Object.entries(data.apis).forEach(([name, stats]) => {
                const t = stats.total_parcels || 0;
                const d = stats.total_delivered_parcels || 0;
                const c = stats.total_cancelled_parcels || 0;
                const r = t > 0 ? Math.round((d / t) * 100) : 0;
                const col = t === 0 ? 'secondary' : r >= 90 ? 'success' : r >= 70 ? 'warning' : 'danger';
                
                html += `
                    <div class="mr-3 mb-1">
                        <small class="text-muted">${name}</small>
                        <div class="d-flex align-items-center">
                            <span class="badge badge-${col} badge-sm">${r}%</span>
                            <small class="ml-1">${d}/${t}</small>
                            ${c > 0 ? `<small class="text-danger ml-1">(${c}c)</small>` : ''}
                        </div>
                    </div>
                `;
            });
        }

        html += `
                    </div>
                </div>
            </div>
            <div class="row mt-1">
                <div class="col-12">
                    <small class="text-muted">
                        ${delivered} delivered, ${cancelled} cancelled
                        ${rate >= 90 ? '✅' : rate >= 70 ? '⚠️' : '❌'}
                    </small>
                </div>
            </div>
        `;

        return html;
    }

    clearFraudDisplay() {
        $('#fraudCheckContainer').remove();
        $('#todayInvoiceWarning').remove();
        this.currentData = null;
        this.hasInvoiceToday = false;
    }

    validateForm(e) {
        if (this.hasInvoiceToday) {
            e.preventDefault();
            e.stopPropagation();
            
            // Show alert
            alert('This phone number has already placed an order today. Cannot create another invoice.');
            
            // Focus on phone field
            $('#recipientPhone').focus().select();
            return false;
        }
        return true;
    }

    // Add method to check before form submission
    canSubmitForm() {
        if (this.hasInvoiceToday) {
            return {
                canSubmit: false,
                message: 'This phone number has already placed an order today. Cannot create another invoice.'
            };
        }
        return { canSubmit: true };
    }

    showLoading(message = 'Loading...') {
        this.hideLoading(); // Remove existing loading
        
        $(`<small id="fraudCheckLoading" class="text-primary ml-2">
            <i class="fa fa-spinner fa-spin fa-xs"></i> ${message}
        </small>`).insertAfter($('#recipientPhone'));
    }

    hideLoading() {
        $('#fraudCheckLoading').remove();
    }

    showError(message) {
        this.hideLoading();
        
        let container = $('#fraudCheckError');
        if (!container.length) {
            container = $(`
                <div id="fraudCheckError" class="mt-2 alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <small>${message}</small>
                </div>
            `).insertAfter($('#recipientPhone').closest('.form-group'));
        }
    }
}

// Initialize
let fraudChecker = null;
$(document).ready(() => {
    fraudChecker = new FraudChecker();
});