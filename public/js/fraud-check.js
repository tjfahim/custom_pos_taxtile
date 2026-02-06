// fraud-check-simple.js
class FraudChecker {
    constructor() {
        this.currentPhone = null;
        this.currentData = null;
        this.daysHistory = { today: false, yesterday: false, dayBefore: false };
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
        this.daysHistory = { today: false, yesterday: false, dayBefore: false }; // Reset
        
        if (!/^01[3-9]\d{8}$/.test(phone)) {
            this.clearFraudDisplay();
            return;
        }
        
        // Check last 3 days invoices
        this.checkLastThreeDays(phone);
    }

    async checkLastThreeDays(phone) {
        this.showLoading('Checking recent orders...');
        
        try {
            // Check invoices for last 3 days
            const res = await fetch(`/admin/check-phone-last-days/${phone}?days=3`);
            const data = await res.json();
            
            if (data.error) throw new Error(data.error);
            
            // Update days history
            this.daysHistory = {
                today: data.today || false,
                yesterday: data.yesterday || false,
                dayBefore: data.day_before || false
            };
            
            // Display 3-day warning if any days have invoices
            if (data.today || data.yesterday || data.day_before) {
                this.displayThreeDaysWarning(data);
            } else {
                $('#threeDaysWarning').remove();
            }
            
            // Still check fraud history
            this.checkPhoneFraud(phone);
        } catch (error) {
            console.error('3-day check error:', error);
            // Still try fraud check if 3-day check fails
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
           
        } finally {
            this.hideLoading();
        }
    }

    displayThreeDaysWarning(data) {
        let container = $('#threeDaysWarning');
        if (!container.length) {
            container = $(`
                <div id="threeDaysWarning" class="mt-2 alert alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <div id="threeDaysContent" class="d-flex align-items-center"></div>
                </div>
            `).insertAfter($('#recipientPhone').closest('.form-group'));
        }
        
        // Determine overall alert level
        let alertLevel = 'info';
        let alertIcon = 'info-circle';
        
        if (data.today) {
            alertLevel = 'danger';
            alertIcon = 'ban';
        } else if (data.yesterday) {
            alertLevel = 'warning';
            alertIcon = 'exclamation-triangle';
        } else if (data.day_before) {
            alertLevel = 'info';
            alertIcon = 'clock';
        }
        
        // Update alert class
        container.removeClass('alert-danger alert-warning alert-info alert-success')
                .addClass(`alert-${alertLevel}`);
        
        const html = `
            <i class="fa fa-${alertIcon} mr-2 fa-lg"></i>
            <div class="flex-grow-1">
                <strong>Recent Orders Found:</strong>
                <div class="d-flex mt-1">
                    ${this.getDayBadge('Today', data.today_count || 0, data.today, data.today_invoices)}
                    ${this.getDayBadge('Yesterday', data.yesterday_count || 0, data.yesterday, data.yesterday_invoices)}
                    ${this.getDayBadge('2 Days Ago', data.day_before_count || 0, data.day_before, data.day_before_invoices)}
                </div>
                ${this.getWarningMessage(data)}
            </div>
        `;
        
        $('#threeDaysContent').html(html);
    }

    getDayBadge(day, count, hasInvoice, invoices = []) {
        let badgeColor = 'secondary';
        let badgeIcon = '';
        
        if (hasInvoice) {
            if (day === 'Today') {
                badgeColor = 'danger';
                badgeIcon = '<i class="fa fa-ban mr-1"></i>';
            } else if (day === 'Yesterday') {
                badgeColor = 'warning';
                badgeIcon = '<i class="fa fa-exclamation-triangle mr-1"></i>';
            } else {
                badgeColor = 'info';
                badgeIcon = '<i class="fa fa-clock mr-1"></i>';
            }
        }
        
        let tooltip = '';
        if (invoices && invoices.length > 0) {
            const invoiceList = invoices.map(inv => `#${inv.invoice_number} (${inv.total})`).join(', ');
            tooltip = `title="Invoices: ${invoiceList}" data-toggle="tooltip"`;
        }
        
        return `
            <div class="mr-3" ${tooltip}>
                <div class="badge badge-${badgeColor} badge-pill mb-1">
                    ${badgeIcon}${day}
                </div>
                <div class="text-center">
                    <small class="text-muted">${count} order${count !== 1 ? 's' : ''}</small>
                </div>
            </div>
        `;
    }

    getWarningMessage(data) {
        if (data.today) {
            return `
                <small class="d-block text-danger mt-1">
                    <i class="fa fa-times-circle"></i> Cannot create another invoice today.
                    ${data.today_count > 1 ? 'Multiple orders today!' : ''}
                </small>
            `;
        } else if (data.yesterday) {
            return `
                <small class="d-block text-warning mt-1">
                    <i class="fa fa-exclamation-circle"></i> Ordered yesterday - verify carefully.
                </small>
            `;
        } else if (data.day_before) {
            return `
                <small class="d-block text-info mt-1">
                    <i class="fa fa-info-circle"></i> Ordered 2 days ago - usual pattern.
                </small>
            `;
        }
        return '';
    }

    displayFraudResults(data) {
        let container = $('#fraudCheckContainer');
        if (!container.length) {
            container = $(`
                <div id="fraudCheckContainer" class="mt-2 p-2 bg-light rounded">
                    <div id="fraudCheckResults" class="small"></div>
                </div>
            `).insertAfter($('#recipientPhone').closest('.form-group'));
            
            // Move it after the 3-day warning if it exists
            const warning = $('#threeDaysWarning');
            if (warning.length) {
                container.insertAfter(warning);
            }
        }
        
        $('#fraudCheckResults').html(this.resultsHtml(data));
        
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
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
                    <span class="text-muted">Overall Success Rate:</span>
                    <span class="badge badge-${riskColor} ml-1">${rate}%</span>
                    <small class="text-muted ml-2">${total} total orders</small>
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
        $('#threeDaysWarning').remove();
        this.currentData = null;
        this.daysHistory = { today: false, yesterday: false, dayBefore: false };
    }

    validateForm(e) {
        if (this.daysHistory.today) {
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
        if (this.daysHistory.today) {
            return {
                canSubmit: false,
                message: 'This phone number has already placed an order today. Cannot create another invoice.'
            };
        } else if (this.daysHistory.yesterday) {
            return {
                canSubmit: true,
                message: 'Warning: This phone ordered yesterday. Please verify carefully.',
                warning: true
            };
        } else if (this.daysHistory.dayBefore) {
            return {
                canSubmit: true,
                message: 'Note: This phone ordered 2 days ago.',
                info: true
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