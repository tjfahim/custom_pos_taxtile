// fraud-check-simple.js - Add customer status check
class FraudChecker {
    constructor() {
        this.currentPhone = null;
        this.currentData = null;
        this.customerStatus = null; // Add customer status tracking
        this.daysHistory = { today: false, yesterday: false, dayBefore: false };
        this.init();
    }

    init() {
        $('#recipientPhone').on('input', this.debounce(this.handlePhoneInput.bind(this), 800));
        // Add phone number validation on input
        $('#recipientPhone').on('keypress', this.validatePhoneKeyPress.bind(this));
        $('#recipientPhone').on('input', this.validatePhoneFormat.bind(this));
        // Add validation to form submission
        $('#posForm').on('submit', this.validateForm.bind(this));
    }
    
    // Allow only digits (0-9)
    validatePhoneKeyPress(e) {
        const charCode = e.which ? e.which : e.keyCode;
        // Allow only digits (0-9)
        if (charCode < 48 || charCode > 57) {
            e.preventDefault();
            this.showBriefMessage('Only digits allowed', 'warning');
            return false;
        }
        return true;
    }
    
    // Validate phone format and length
    validatePhoneFormat() {
        const phone = $('#recipientPhone').val();
        
        // Remove any non-digit characters
        const digitsOnly = phone.replace(/\D/g, '');
        
        // Update input with digits only
        if (phone !== digitsOnly) {
            $('#recipientPhone').val(digitsOnly);
        }
        
        // Check length
        if (digitsOnly.length > 11) {
            // Trim to 11 digits
            const trimmed = digitsOnly.substring(0, 11);
            $('#recipientPhone').val(trimmed);
            this.showBriefMessage('Maximum 11 digits allowed', 'warning');
        }
        
        // Show length counter
        this.updatePhoneLengthCounter(digitsOnly.length);
    }

    updatePhoneLengthCounter(length) {
        // Remove existing counter
        $('#phoneLengthCounter').remove();
        
        // Add counter after phone input
        const counter = $(`
            <small id="phoneLengthCounter" class="form-text ${length === 11 ? 'text-success' : 'text-muted'}">
                ${length}/11 digits
                ${length === 11 ? ' ✓ Valid' : length > 0 ? ' - Need 11 digits' : ''}
            </small>
        `).insertAfter($('#recipientPhone'));
    }

    handlePhoneInput() {
        const phone = $('#recipientPhone').val().trim().replace(/\D/g, '');
        this.currentPhone = phone;
        this.customerStatus = null; // Reset customer status
        this.daysHistory = { today: false, yesterday: false, dayBefore: false }; // Reset
        
        // Clear warnings if phone is empty
        if (!phone) {
            this.clearFraudDisplay();
            this.updatePhoneLengthCounter(0);
            return;
        }
        
        // Check if exactly 11 digits and valid Bangladeshi format
        if (phone.length === 11) {
            if (/^01[3-9]\d{8}$/.test(phone)) {
                this.checkCustomerStatus(phone);
            } else {
                this.showMessage('Invalid Bangladeshi mobile number format', 'warning');
                this.clearFraudDisplay();
            }
        } else if (phone.length > 0) {
            // Show message if not 11 digits
            this.showBriefMessage(`Need ${11 - phone.length} more digits`, 'info');
            this.clearFraudDisplay();
        }
    }

    showBriefMessage(message, type = 'info') {
        // Create a temporary notification
        const notification = $(`
            <div class="alert ${type === 'info' ? 'alert-info' : 'alert-warning'} 
                 alert-dismissible fade show phone-validation-notification" 
                 style="position: fixed; top: 70px; right: 20px; z-index: 9999; max-width: 300px;">
                <i class="fa fa-info-circle"></i> ${message}
            </div>
        `);
        
        // Remove any existing notifications
        $('.phone-validation-notification').remove();
        
        $('body').append(notification);
        
        // Auto-remove after 1.5 seconds
        setTimeout(() => {
            notification.alert('close');
        }, 1500);
    }

    showMessage(message, type = 'info') {
        // Remove existing message
        $('#phoneValidationMessage').remove();
        
        const alertClasses = {
            'success': 'alert-success',
            'warning': 'alert-warning',
            'danger': 'alert-danger',
            'info': 'alert-info'
        };
        
        const alertClass = alertClasses[type] || 'alert-info';
        
        const messageDiv = $(`
            <div id="phoneValidationMessage" class="alert ${alertClass} alert-dismissible fade show mt-2">
                <i class="fa fa-info-circle"></i> ${message}
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        `);
        
        // Insert after phone input group
        messageDiv.insertAfter($('#recipientPhone').closest('.form-group'));
        
        setTimeout(() => {
            $('#phoneValidationMessage').alert('close');
        }, 3000);
    }

    debounce(func, wait) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func(...args), wait);
        };
    }

    async checkCustomerStatus(phone) {
        this.showLoading('Checking customer status...');
        
        try {
            const res = await fetch(`/check-customer-status/${phone}`);
            const data = await res.json();
            
            if (data.error) throw new Error(data.error);
            
            this.customerStatus = data;
            
            // Display customer status warning if inactive
            if (data.status === 'inactive' || data.status === 'blocked') {
                this.displayCustomerStatusWarning(data);
            } else {
                $('#customerStatusWarning').remove();
            }
            
            // Then check last 3 days invoices
            this.checkLastThreeDays(phone);
        } catch (error) {
            console.error('Customer status check error:', error);
            // Continue with other checks even if status check fails
            this.checkLastThreeDays(phone);
        } finally {
            this.hideLoading();
        }
    }

    async checkLastThreeDays(phone) {
        this.showLoading('Checking recent orders...');
        
        try {
            // Check invoices for last 4 days
            const res = await fetch(`/check-phone-last-days/${phone}?days=`);
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
            const res = await fetch(`/check-phone-fraud/${phone}`);
            
            if (!res.ok) {
                throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            }
            
            const data = await res.json();
            
            if (!data || typeof data !== 'object') {
                throw new Error('Invalid response format');
            }
            
            this.currentData = data;
            this.displayFraudResults(this.currentData);
            $('#fraudCheckError').remove();
            
        } catch (error) {
            console.error('Fraud check error:', error);
            this.showError('Failed to check fraud history');
        } finally {
            this.hideLoading();
        }
    }

    displayCustomerStatusWarning(data) {
        let container = $('#customerStatusWarning');
        if (!container.length) {
            container = $(`
                <div id="customerStatusWarning" class="mt-2 alert alert-dismissible fade show bg-danger">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <div id="customerStatusContent" class="d-flex align-items-center"></div>
                </div>
            `).insertAfter($('#recipientPhone').closest('.form-group'));
        }
        
        let alertLevel = 'danger';
        let alertIcon = 'ban';
        let statusText = 'Blocked';
        
        if (data.status === 'inactive') {
            alertLevel = 'warning';
            alertIcon = 'exclamation-triangle';
            statusText = 'Inactive';
        }
        
        container.removeClass('alert-danger alert-warning alert-info alert-success')
                .addClass(`alert-${alertLevel}`);
        
        let notesHtml = '';
        if (data.note) {
            notesHtml = `
                <div class="mt-1">
                    <strong>Notes:</strong>
                    <small class="d-block text-muted">${data.note}</small>
                </div>
            `;
        }
        
        const html = `
            <i class="fa fa-${alertIcon} mr-2 fa-lg"></i>
            <div class="flex-grow-1">
                <strong>***** Customer ${statusText}:</strong>
                <div class="mt-1">
                    <span class="badge badge-${alertLevel}">${data.name || 'Unknown Customer'}</span>
                    <small class="text-muted ml-2">ID: ${data.id}</small>
                </div>
                ${notesHtml}
                <small class="d-block text-${alertLevel} mt-1">
                    <i class="fa fa-exclamation-circle"></i> This customer is ${data.status} by our system.
                </small>
            </div>
        `;
        
        $('#customerStatusContent').html(html);
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
            
            const statusWarning = $('#customerStatusWarning');
            if (statusWarning.length) {
                container.insertAfter(statusWarning);
            }
        }
        
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
                    ${this.getDayBadge('3 Days Ago', data.three_days_ago_count || 0, data.three_days_ago, data.three_days_ago_invoices)}
                      ${this.getDayBadge('4 Days Ago', data.four_days_ago_count || 0, data.four_days_ago, data.four_days_ago_invoices)}
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
            
            const lastWarning = $('#threeDaysWarning').length ? $('#threeDaysWarning') : $('#customerStatusWarning');
            if (lastWarning.length) {
                container.insertAfter(lastWarning);
            }
        }
        
        $('#fraudCheckResults').html(this.resultsHtml(data));
        
        // FIXED: Check if tooltip function exists before calling
        if (typeof $.fn.tooltip === 'function' && $('[data-toggle="tooltip"]').length) {
            $('[data-toggle="tooltip"]').tooltip();
        }
        
        if (data.reports && data.reports.length > 0) {
            this.displayFraudReports(data.reports);
        } else {
            $('#fraudReportsContainer').remove();
        }
    }

    displayFraudReports(reports) {
        let container = $('#fraudReportsContainer');
        if (!container.length) {
            container = $(`
                <div id="fraudReportsContainer" class="mt-2 alert alert-danger alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong><i class="fa fa-exclamation-triangle"></i> Fraud Reports Found:</strong>
                    <div id="fraudReportsList" class="mt-1"></div>
                </div>
            `).insertAfter($('#fraudCheckContainer'));
        }
        
        let reportsHtml = '<ul class="mb-0 pl-3">';
        reports.forEach(report => {
            reportsHtml += `
                <li class="mb-1">
                    <strong>${report.name || 'Unknown'}</strong> 
                    <small class="text-muted">(${report.courierName || 'Unknown Courier'})</small>
                    <br>
                    <small class="text-danger">${report.details || 'Fraud reported'}</small>
                    <br>
                    <small class="text-muted">Reported: ${new Date(report.created_at).toLocaleDateString()}</small>
                </li>
            `;
        });
        reportsHtml += '</ul>';
        
        $('#fraudReportsList').html(reportsHtml);
    }

    resultsHtml(data) {
        if (!data || typeof data !== 'object') {
            return `
                <div class="alert alert-warning alert-sm p-2 mb-0">
                    <i class="fa fa-exclamation-triangle mr-1"></i>
                    No fraud data available for this number.
                </div>
            `;
        }
        
        const total = data.total_parcels || 0;
        const delivered = data.total_delivered || 0;
        const cancelled = data.total_cancel || 0;
        const rate = total > 0 ? Math.round((delivered / total) * 100) : 0;
        const riskColor = rate >= 90 ? 'success' : rate >= 70 ? 'warning' : 'danger';
        
        let html = '';
        
        if (rate < 70 && total > 0) {
            html += `
                <div class="alert alert-warning alert-sm p-2 mb-2">
                    <i class="fa fa-exclamation-triangle mr-1"></i>
                    <strong>⚠️ HIGH RISK:</strong> Only ${rate}% success rate (${delivered}/${total}). Proceed with extreme caution!
                </div>
            `;
        } else if (rate < 85 && total > 0) {
            html += `
                <div class="alert alert-info alert-sm p-2 mb-2">
                    <i class="fa fa-info-circle mr-1"></i>
                    <strong>Medium Risk:</strong> ${rate}% success rate. Consider verifying carefully.
                </div>
            `;
        }
        
        if (total === 0 && Object.keys(data.apis || {}).length === 0) {
            html += `
                <div class="text-center text-muted py-2">
                    <i class="fa fa-info-circle"></i> No order history found for this number.
                </div>
            `;
            return html;
        }
        
        html += `
            <div class="row">
                <div class="col-12 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Overall Success Rate:</span>
                        <span class="badge badge-${riskColor} badge-lg">${rate}%</span>
                        <small class="text-muted">${total} total orders</small>
                    </div>
                    <div class="progress mt-1" style="height: 5px;">
                        <div class="progress-bar bg-${riskColor}" role="progressbar" 
                             style="width: ${rate}%" aria-valuenow="${rate}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <strong class="text-muted small">Courier Performance:</strong>
                    <div class="d-flex flex-wrap mt-1">
        `;

        if (data.apis && typeof data.apis === 'object') {
            const courierEntries = Object.entries(data.apis);
            
            if (courierEntries.length === 0) {
                html += `<div class="text-muted small">No courier data available</div>`;
            } else {
                courierEntries.forEach(([name, stats]) => {
                    if (!stats || typeof stats !== 'object') return;
                    
                    const t = stats.total_parcels || 0;
                    const d = stats.total_delivered_parcels || 0;
                    const c = stats.total_cancelled_parcels || 0;
                    const r = stats.success_ratio || (t > 0 ? Math.round((d / t) * 100) : 0);
                    const col = t === 0 ? 'secondary' : r >= 90 ? 'success' : r >= 70 ? 'warning' : 'danger';
                    const status = stats.status || (t > 0 ? 'active' : 'no_orders');
                    
                    if (status !== 'not_found') {
                        html += `
                            <div class="mr-3 mb-2">
                                <small class="text-muted d-block">${name}</small>
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-${col} badge-sm">${r}%</span>
                                    <small class="ml-1">${d}/${t}</small>
                                    ${c > 0 ? `<small class="text-danger ml-1">(${c}c)</small>` : ''}
                                </div>
                            </div>
                        `;
                    }
                });
            }
        } else {
            html += `<div class="text-muted small">No courier data available</div>`;
        }

        html += `
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fa fa-check-circle text-success"></i> ${delivered} delivered 
                            <i class="fa fa-times-circle text-danger ml-2"></i> ${cancelled} cancelled
                        </small>
                        <small class="text-${riskColor}">
                            ${rate >= 90 ? '✅ Low Risk' : rate >= 70 ? '⚠️ Medium Risk' : '❌ High Risk'}
                        </small>
                    </div>
                </div>
            </div>
        `;

        return html;
    }

    clearFraudDisplay() {
        $('#customerStatusWarning').remove();
        $('#fraudCheckContainer').remove();
        $('#threeDaysWarning').remove();
        $('#fraudReportsContainer').remove();
        this.currentData = null;
        this.customerStatus = null;
        this.daysHistory = { today: false, yesterday: false, dayBefore: false };
    }

    validateForm(e) {
        const phone = $('#recipientPhone').val().trim().replace(/\D/g, '');
        if (!phone) {
            e.preventDefault();
            e.stopPropagation();
            alert('Please enter a phone number');
            $('#recipientPhone').focus();
            return false;
        }
        
        if (phone.length !== 11) {
            e.preventDefault();
            e.stopPropagation();
            alert('Phone number must be exactly 11 digits');
            $('#recipientPhone').focus().select();
            return false;
        }
        
        if (!/^01[3-9]\d{8}$/.test(phone)) {
            e.preventDefault();
            e.stopPropagation();
            alert('Please enter a valid Bangladeshi mobile number (01XXXXXXXXX)');
            $('#recipientPhone').focus().select();
            return false;
        }
        
        if (this.customerStatus && (this.customerStatus.status === 'inactive' || this.customerStatus.status === 'blocked')) {
            e.preventDefault();
            e.stopPropagation();
            
            const statusText = this.customerStatus.status === 'blocked' ? 'BLOCKED' : 'INACTIVE';
            const alertMessage = this.customerStatus.status === 'blocked' 
                ? 'This customer is BLOCKED by our system. Cannot create invoice.'
                : 'This customer is marked as INACTIVE. Proceed with caution.';
            
            if (this.customerStatus.status === 'blocked') {
                alert(`CUSTOMER ${statusText}\n\n${alertMessage}\n\nNotes: ${this.customerStatus.notes || 'No notes available'}`);
                $('#recipientPhone').focus().select();
                return false;
            } else {
                const proceed = confirm(`CUSTOMER ${statusText}\n\n${alertMessage}\n\nNotes: ${this.customerStatus.notes || 'No notes available'}\n\nDo you want to proceed?`);
                if (!proceed) {
                    $('#recipientPhone').focus().select();
                    return false;
                }
            }
        }
        
        if (this.daysHistory.today) {
            e.preventDefault();
            e.stopPropagation();
            alert('This phone number has already placed an order today. Cannot create another invoice.');
            $('#recipientPhone').focus().select();
            return false;
        }
        
        if (this.currentData && this.currentData.reports && this.currentData.reports.length > 0) {
            e.preventDefault();
            e.stopPropagation();
            
            const reportCount = this.currentData.reports.length;
            const reportNames = this.currentData.reports.map(r => r.name).join(', ');
            
            const proceed = confirm(
                `⚠️ FRAUD ALERT ⚠️\n\n` +
                `This phone number has ${reportCount} fraud report(s) against it.\n` +
                `Reported by: ${reportNames}\n\n` +
                `Do you still want to proceed with this order?`
            );
            
            if (!proceed) {
                $('#recipientPhone').focus().select();
                return false;
            }
        }
        
        if (this.currentData) {
            const total = this.currentData.total_parcels || 0;
            const delivered = this.currentData.total_delivered || 0;
            const rate = total > 0 ? Math.round((delivered / total) * 100) : 100;
            
            if (rate < 70 && total > 0) {
                const proceed = confirm(
                    `⚠️ HIGH RISK CUSTOMER ⚠️\n\n` +
                    `This customer has only ${rate}% success rate (${delivered}/${total} delivered).\n` +
                    `High risk of fraud or return.\n\n` +
                    `Do you still want to proceed with this order?`
                );
                
                if (!proceed) {
                    $('#recipientPhone').focus().select();
                    return false;
                }
            }
        }
        
        return true;
    }

    canSubmitForm() {
        if (this.customerStatus) {
            if (this.customerStatus.status === 'blocked') {
                return {
                    canSubmit: false,
                    message: 'This customer is BLOCKED by our system. Cannot create invoice.',
                    blocked: true
                };
            } else if (this.customerStatus.status === 'inactive') {
                return {
                    canSubmit: true,
                    message: 'Warning: This customer is marked as INACTIVE.',
                    warning: true,
                    notes: this.customerStatus.notes
                };
            }
        }
        
        if (this.currentData && this.currentData.reports && this.currentData.reports.length > 0) {
            return {
                canSubmit: true,
                message: `Warning: ${this.currentData.reports.length} fraud report(s) found!`,
                warning: true,
                fraudReports: true
            };
        }
        
        if (this.currentData) {
            const total = this.currentData.total_parcels || 0;
            const delivered = this.currentData.total_delivered || 0;
            const rate = total > 0 ? Math.round((delivered / total) * 100) : 100;
            
            if (rate < 70 && total > 0) {
                return {
                    canSubmit: true,
                    message: `Warning: Only ${rate}% success rate. High risk customer!`,
                    warning: true,
                    highRisk: true
                };
            } else if (rate < 85 && total > 0) {
                return {
                    canSubmit: true,
                    message: `Note: ${rate}% success rate. Medium risk.`,
                    info: true
                };
            }
        }
        
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
        this.hideLoading();
        
        $(`<small id="fraudCheckLoading" class="text-primary ml-2">
            <i class="fa fa-spinner fa-spin fa-xs"></i> ${message}
        </small>`).insertAfter($('#recipientPhone'));
    }

    hideLoading() {
        $('#fraudCheckLoading').remove();
    }

    showError(message) {
        this.hideLoading();
        $('#fraudCheckError').remove();
        
        const errorDiv = $(`
            <div id="fraudCheckError" class="mt-2 alert alert-warning alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <small><i class="fa fa-exclamation-triangle"></i> ${message}</small>
            </div>
        `);
        
        const container = $('#fraudCheckContainer');
        if (container.length) {
            errorDiv.insertAfter(container);
        } else {
            errorDiv.insertAfter($('#recipientPhone').closest('.form-group'));
        }
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            errorDiv.alert('close');
        }, 3000);
    }
}

// Initialize
let fraudChecker = null;
$(document).ready(() => {
    fraudChecker = new FraudChecker();
});