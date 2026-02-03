// fraud-check-simple.js
class FraudChecker {
    constructor() {
        this.currentPhone = null;
        this.currentData = null;
        this.init();
    }

    init() {
        $('#recipientPhone').on('input', this.debounce(this.handlePhoneInput.bind(this), 800));
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
        
        if (!/^01[3-9]\d{8}$/.test(phone)) {
            this.clearFraudDisplay();
            return;
        }
        this.checkPhoneFraud(phone);
    }

    async checkPhoneFraud(phone) {
        this.showLoading();
        try {
            const res = await fetch(`/admin/check-phone/${phone}`);
            const data = await res.json();
            if (data.error) throw new Error(data.error);
            this.currentData = data;
            this.displayFraudResults(this.currentData);
        } catch (error) {
            console.error('Fraud check error:', error);
        } finally {
            this.hideLoading();
        }
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
        
        let html = `
            <div class="row">
                <div class="col-12 mb-1">
                    <span class="text-muted">Fraud Check:</span>
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
        this.currentData = null;
    }

    showLoading() {
        if (!$('#fraudCheckLoading').length) {
            $(`<small id="fraudCheckLoading" class="text-primary ml-2">
                <i class="fa fa-spinner fa-spin fa-xs"></i>
            </small>`).insertAfter($('#recipientPhone'));
        }
    }

    hideLoading() {
        $('#fraudCheckLoading').remove();
    }
}

// Initialize
$(document).ready(() => new FraudChecker());