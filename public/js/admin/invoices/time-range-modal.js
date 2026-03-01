/**
 * Time Range Modal Functionality
 * Handles the custom time range selection and CSV download
 */

$(document).ready(function() {
    
    // Update preview when inputs change
    function updatePreview() {
        const date = $('#date').val();
        const startTime = $('#start_time').val();
        const endTime = $('#end_time').val();
        
        let startDisplay = startTime;
        let endDisplay = endTime;
        
        // Format time for display
        if (startTime) {
            const start = startTime.split(':');
            const startHour = parseInt(start[0]);
            const startMin = start[1];
            const startPeriod = startHour >= 12 ? 'PM' : 'AM';
            const startHour12 = startHour % 12 || 12;
            startDisplay = `${startHour12}:${startMin} ${startPeriod}`;
        }
        
        if (endTime) {
            const end = endTime.split(':');
            const endHour = parseInt(end[0]);
            const endMin = end[1];
            const endPeriod = endHour >= 12 ? 'PM' : 'AM';
            const endHour12 = endHour % 12 || 12;
            endDisplay = `${endHour12}:${endMin} ${endPeriod}`;
        }
        
        const formattedDate = new Date(date + 'T00:00:00').toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        $('#timeRangePreview').text(`Selected: ${formattedDate} from ${startDisplay} to ${endDisplay}`);
    }
    
    // Handle preset button clicks
    $('.preset-btn').click(function() {
        const start = $(this).data('start');
        const end = $(this).data('end');
        
        $('#start_time').val(start);
        $('#end_time').val(end);
        updatePreview();
    });
    
    // Update preview on input changes
    $('#date, #start_time, #end_time').on('change keyup', updatePreview);
    
    // Initial preview update
    updatePreview();
    
    // Show progress bar
    function showProgress(percent) {
        $('#downloadProgress').show();
        $('#downloadProgressBar').css('width', percent + '%');
    }
    
    // Hide progress bar
    function hideProgress() {
        $('#downloadProgress').hide();
        $('#downloadProgressBar').css('width', '0%');
    }
    
    // Reset button and progress
    function resetButtonAndProgress(button, originalText) {
        if (button && button.length) {
            button.prop('disabled', false).html(originalText);
        }
        hideProgress();
    }
    
    // Handle download button click
    $('#downloadBtn').click(function() {
        const date = $('#date').val();
        const startTime = $('#start_time').val();
        const endTime = $('#end_time').val();
        
        // Validate inputs
        if (!date || !startTime || !endTime) {
            showToast('error', 'Error', 'Please fill in all fields');
            return;
        }
        
        // Validate time range
        if (startTime >= endTime) {
            showToast('error', 'Error', 'End time must be after start time!');
            return;
        }
        
        const downloadBtn = $('#downloadBtn');
        const originalText = '<i class="fa fa-download"></i> Download CSV';
        const processingText = '<i class="fa fa-spinner fa-spin"></i> Processing...';
        
        downloadBtn.prop('disabled', true).html(processingText);
        showProgress(30);
        
        const token = $('meta[name="csrf-token"]').attr('content');
        
        // Make AJAX request
        $.ajax({
            url: $('#timeRangeForm').data('download-url'),
            type: 'GET',
            data: {
                date: date,
                start_time: startTime,
                end_time: endTime
            },
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'text/csv, application/json, */*'
            },
            xhrFields: {
                responseType: 'blob'
            },
            beforeSend: function() {
                showProgress(50);
            },
            success: function(response, status, xhr) {
                showProgress(100);
                
                // Check content type to determine if it's an error
                const contentType = xhr.getResponseHeader('content-type');
                
                if (contentType && contentType.includes('application/json')) {
                    // This is an error response - read it as text
                    const reader = new FileReader();
                    reader.onload = function() {
                        try {
                            const errorResponse = JSON.parse(reader.result);
                            console.error('Server error:', errorResponse);
                            
                            if (errorResponse.message) {
                                showToast('error', 'Error', errorResponse.message);
                            } else {
                                showToast('error', 'Error', 'No invoices found for the selected time range');
                            }
                        } catch (e) {
                            console.error('Failed to parse error response:', e);
                            showToast('error', 'Error', 'Server returned an error but could not parse it');
                        }
                        
                        // Reset button and progress
                        resetButtonAndProgress(downloadBtn, originalText);
                    };
                    reader.readAsText(response);
                } else if (contentType && contentType.includes('text/csv')) {
                    // This is a CSV file - trigger download
                    let filename = 'invoices.csv';
                    const contentDisposition = xhr.getResponseHeader('content-disposition');
                    if (contentDisposition) {
                        const filenameMatch = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                        if (filenameMatch && filenameMatch[1]) {
                            filename = filenameMatch[1].replace(/['"]/g, '');
                        }
                    }
                    
                    // Create download link
                    const url = window.URL.createObjectURL(response);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    
                    // Clean up
                    setTimeout(function() {
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);
                    }, 100);
                    
                    // Show success message
                    showToast('success', 'Success', 'Download started!');
                    
                    resetButtonAndProgress(downloadBtn, originalText);
                    $('#timeRangeModal .close').click();
        
                } else {
                    console.error('Unexpected content type:', contentType);
                    showToast('error', 'Error', 'Unexpected response from server');
                    resetButtonAndProgress(downloadBtn, originalText);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
                
                // Try to parse error message from response
                let errorMessage = 'Download failed';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        errorMessage = response.message || errorMessage;
                    } catch (e) {
                        errorMessage = xhr.statusText || 'Unknown error';
                    }
                } else {
                    errorMessage = xhr.statusText || 'Unknown error';
                }
                
                showToast('error', 'Error', errorMessage);
                
                // Reset button and progress
                resetButtonAndProgress(downloadBtn, originalText);
            }
        });
    });
    
    // Reset modal when closed
    $('#timeRangeModal').on('hidden.bs.modal', function() {
        hideProgress();
        $('#date').val($('#date').data('default-date'));
        $('#start_time').val('00:00');
        $('#end_time').val('23:59');
        updatePreview();
        
        // Reset download button when modal is closed
        const downloadBtn = $('#downloadBtn');
        const originalText = '<i class="fa fa-download"></i> Download CSV';
        downloadBtn.prop('disabled', false).html(originalText);
    });
    
    // Also reset when modal close button is clicked directly
    $('#timeRangeModal .close, #timeRangeModal .btn-secondary').on('click', function() {
        const downloadBtn = $('#downloadBtn');
        const originalText = '<i class="fa fa-download"></i> Download CSV';
        downloadBtn.prop('disabled', false).html(originalText);
        hideProgress();
    });
    
    // Toast notification function (duplicated from main file to make modal self-contained)
    function showToast(type, title, message) {
        let toastContainer = $('.toast-container');
        if (toastContainer.length === 0) {
            $('body').append('<div class="toast-container position-fixed top-0 end-0 p-3"></div>');
            toastContainer = $('.toast-container');
        }
        
        const toastId = 'toast-' + Date.now();
        
        let icon = 'info-circle';
        if (type === 'success') icon = 'check-circle';
        if (type === 'error') icon = 'exclamation-circle';
        
        const toastHtml = `
            <div id="${toastId}" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-${type} text-white">
                    <i class="fa fa-${icon} me-2"></i>
                    <strong class="me-auto">${title}</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
                <div class="toast-body">
                    ${message}
                </div>
            </div>
        `;
        
        const toastElement = $(toastHtml).appendTo(toastContainer);
        
        setTimeout(() => {
            toastElement.remove();
        }, 5000);
        
        toastElement.find('.btn-close').on('click', function() {
            toastElement.remove();
        });
    }
});