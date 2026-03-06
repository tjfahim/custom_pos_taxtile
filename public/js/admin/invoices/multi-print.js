/**
 * Multi-Select Print functionality for Invoices
 * Works with DataTable checkbox column
 */

(function($) {
    'use strict';

    const MultiPrint = {
        // Configuration
        config: {
            selectAllCheckbox: '#select-all-invoices',
            rowCheckbox: '.select-invoice',
            printSelectedBtn: '#print-selected-btn',
            printCounter: '#selected-count',
            tableId: '#invoicesTable',
            maxPrintLimit: 50
        },

        // State
        selectedInvoices: new Set(),
        dataTable: null,

        /**
         * Initialize
         */
        init: function() {
            this.cacheElements();
            this.getDataTableInstance();
            this.bindEvents();
            console.log('MultiPrint initialized');
        },

        /**
         * Cache DOM elements
         */
        cacheElements: function() {
            this.$selectAll = $(this.config.selectAllCheckbox);
            this.$printSelectedBtn = $(this.config.printSelectedBtn);
            this.$printCounter = $(this.config.printCounter);
        },

        /**
         * Get DataTable instance
         */
        getDataTableInstance: function() {
            if ($.fn.DataTable && $.fn.DataTable.isDataTable(this.config.tableId)) {
                this.dataTable = $(this.config.tableId).DataTable();
            }
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Select all checkbox
            $(document).on('change', this.config.selectAllCheckbox, (e) => this.handleSelectAll(e));
            
            // Individual row checkbox (event delegation)
            $(document).on('change', this.config.rowCheckbox, (e) => this.handleRowSelect(e));
            
            // Print button
            $(document).on('click', this.config.printSelectedBtn, (e) => {
                e.preventDefault();
                this.printSelected();
            });

            // DataTable draw event (page change, search, sort, etc.)
            $(this.config.tableId).on('draw.dt', () => {
                console.log('DataTable redrawn');
                // Update select all state based on current page
                setTimeout(() => {
                    this.updateSelectAllState();
                }, 50);
            });

            // Status update complete event
            $(document).on('status-update-complete', () => {
                console.log('Status update complete');
                // Clear selections on status update
                this.selectedInvoices.clear();
                this.updatePrintButtonState();
            });
        },

        /**
         * Handle select all
         */
        handleSelectAll: function(e) {
            const isChecked = $(e.target).prop('checked');
            
            // Get all checkboxes on current page
            const currentPageCheckboxes = $(this.config.rowCheckbox + ':visible');
            
            currentPageCheckboxes.each((_, checkbox) => {
                $(checkbox).prop('checked', isChecked);
                const invoiceId = $(checkbox).data('invoice-id');
                
                if (isChecked && invoiceId) {
                    this.selectedInvoices.add(invoiceId);
                }
            });

            if (!isChecked) {
                // Remove only visible ones from selected set
                currentPageCheckboxes.each((_, checkbox) => {
                    const invoiceId = $(checkbox).data('invoice-id');
                    this.selectedInvoices.delete(invoiceId);
                });
            }

            this.updatePrintButtonState();
        },

        /**
         * Handle row select
         */
        handleRowSelect: function(e) {
            const $checkbox = $(e.target);
            const invoiceId = $checkbox.data('invoice-id');
            const isChecked = $checkbox.prop('checked');

            if (isChecked && invoiceId) {
                this.selectedInvoices.add(invoiceId);
            } else if (invoiceId) {
                this.selectedInvoices.delete(invoiceId);
            }

            this.updateSelectAllState();
            this.updatePrintButtonState();
        },

        /**
         * Update select all checkbox state
         */
        updateSelectAllState: function() {
            if (!this.$selectAll.length) return;
            
            const visibleCheckboxes = $(this.config.rowCheckbox + ':visible');
            
            if (visibleCheckboxes.length === 0) {
                this.$selectAll.prop('checked', false).prop('indeterminate', false);
                return;
            }
            
            const checkedVisible = visibleCheckboxes.filter(':checked').length;
            
            if (checkedVisible === 0) {
                this.$selectAll.prop('checked', false).prop('indeterminate', false);
            } else if (checkedVisible === visibleCheckboxes.length) {
                this.$selectAll.prop('checked', true).prop('indeterminate', false);
            } else {
                this.$selectAll.prop('checked', false).prop('indeterminate', true);
            }
        },

        /**
         * Update print button state
         */
        updatePrintButtonState: function() {
            const count = this.selectedInvoices.size;
            
            if (count > 0) {
                this.$printSelectedBtn.prop('disabled', false);
                this.$printCounter.text(`(${count} selected)`).show();
            } else {
                this.$printSelectedBtn.prop('disabled', true);
                this.$printCounter.hide();
            }

            if (count > this.config.maxPrintLimit) {
                this.$printSelectedBtn.prop('disabled', true);
                this.showToast('warning', 'Too Many Selected', 
                    `Max ${this.config.maxPrintLimit} invoices allowed`);
            }
        },

        /**
         * Print selected invoices
         */
        printSelected: function() {
            const invoiceIds = Array.from(this.selectedInvoices);
            
            if (invoiceIds.length === 0) {
                this.showToast('warning', 'No Selection', 'Select at least one invoice');
                return;
            }

            if (invoiceIds.length > this.config.maxPrintLimit) {
                this.showToast('error', 'Too Many', `Max ${this.config.maxPrintLimit} invoices`);
                return;
            }

            // Disable button
            this.$printSelectedBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Printing...');

            // Show loading
            if (typeof window.showLoading === 'function') {
                window.showLoading();
            }

            // Open print window
            const ids = invoiceIds.join(',');
            const printUrl = `/admin/invoices/print-multiple/${ids}`;
            const printWindow = window.open(printUrl, '_blank');
            
            if (!printWindow) {
                this.showToast('error', 'Popup Blocked', 'Please allow popups');
                this.resetButton();
                return;
            }

            // Monitor print window
            const checkInterval = setInterval(() => {
                if (printWindow.closed) {
                    clearInterval(checkInterval);
                    this.resetButton();
                    
                    // Hide loading
                    if (typeof window.hideLoading === 'function') {
                        window.hideLoading();
                    }
                    
                    // Clear selection
                    this.clearSelection();
                    
                    this.showToast('success', 'Success', 
                        `Printing ${invoiceIds.length} invoice(s)`);
                }
            }, 500);
        },

        /**
         * Reset print button
         */
        resetButton: function() {
            this.$printSelectedBtn.html('<i class="fa fa-print"></i> Print Selected').prop('disabled', false);
            this.updatePrintButtonState();
            
            if (typeof window.hideLoading === 'function') {
                window.hideLoading();
            }
        },

        /**
         * Clear all selections
         */
        clearSelection: function() {
            this.selectedInvoices.clear();
            $(this.config.rowCheckbox).prop('checked', false);
            if (this.$selectAll.length) {
                this.$selectAll.prop('checked', false).prop('indeterminate', false);
            }
            this.updatePrintButtonState();
        },

        /**
         * Show toast
         */
        showToast: function(type, title, message) {
            if (typeof window.showToast === 'function') {
                window.showToast(type, title, message);
            } else {
                alert(`${title}: ${message}`);
            }
        }
    };

    // Initialize when ready
    $(document).ready(function() {
        setTimeout(() => {
            MultiPrint.init();
        }, 1000);
    });

    window.MultiPrint = MultiPrint;

})(jQuery);