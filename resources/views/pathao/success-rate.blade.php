<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pathao Success Rate Checker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .card { max-width: 800px; margin: 0 auto; }
        .loader {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .rating-badge {
            font-size: 14px;
            padding: 6px 12px;
            border-radius: 20px;
        }
        .rating-excellent { background: #28a745; color: white; }
        .rating-good { background: #17a2b8; color: white; }
        .rating-average { background: #ffc107; color: black; }
        .rating-poor { background: #dc3545; color: white; }
    </style>
</head>
<body>
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Pathao Customer Success Rate Checker</h4>
        </div>
        <div class="card-body">
            <div class="mb-4">
                <label class="form-label fw-bold">Enter Phone Number</label>
                <input type="text" 
                       id="phone" 
                       class="form-control form-control-lg" 
                       placeholder="017XXXXXXXX"
                       maxlength="11"
                       autocomplete="off">
                <div class="form-text">Enter 11 digit Bangladeshi phone number (e.g., 01712345678)</div>
            </div>

            <!-- Loading Spinner -->
            <div id="loading" class="text-center" style="display: none;">
                <div class="loader"></div>
                <p class="mt-2">Fetching customer data from Pathao...</p>
            </div>

            <!-- Results Section -->
            <div id="result" style="display: none;">
                <div class="alert alert-success">
                    <h5 class="d-flex justify-content-between align-items-center">
                        <span>Customer Information</span>
                        <span id="customer-rating-badge" class="rating-badge">Rating</span>
                    </h5>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Phone Number:</strong>
                                <div id="phone-number" class="fw-bold"></div>
                            </div>
                            <div class="mb-3">
                                <strong>Customer Rating:</strong>
                                <div id="customer-rating-text" class="fw-bold"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>API Version:</strong>
                                <div id="api-version" class="text-muted"></div>
                            </div>
                            <div class="mb-3">
                                <strong>Address Book Entries:</strong>
                                <div id="address-book-count" class="text-muted"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <strong>Status Message:</strong>
                        <div id="status-message" class="text-success"></div>
                    </div>
                </div>
                
             
                <!-- Raw Data (for debugging) -->
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#rawData">
                        View Raw Data
                    </button>
                    <div class="collapse mt-2" id="rawData">
                        <div class="card card-body">
                            <pre id="raw-data" class="mb-0 small"></pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Error Section -->
            <div id="error" class="alert alert-danger mt-3" style="display: none;"></div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const phoneInput = document.getElementById('phone');
            const loading = document.getElementById('loading');
            const result = document.getElementById('result');
            const error = document.getElementById('error');
            
            let timeout;

            phoneInput.addEventListener('input', function() {
                const phone = phoneInput.value.replace(/\D/g, '');
                phoneInput.value = phone;
                
                // Clear previous timeout
                clearTimeout(timeout);
                
                // Hide previous results
                result.style.display = 'none';
                error.style.display = 'none';
                
                // Validate phone number
                if (!/^01[3-9]\d{8}$/.test(phone)) {
                    if (phone.length === 11) {
                        error.style.display = 'block';
                        error.textContent = 'Invalid phone number format. Must start with 013-019.';
                    }
                    return;
                }
                
                // Set new timeout for API call
                timeout = setTimeout(() => {
                    fetchSuccessRate(phone);
                }, 800);
            });
            
            async function fetchSuccessRate(phone) {
                loading.style.display = 'block';
                result.style.display = 'none';
                error.style.display = 'none';
                
                try {
                    const response = await fetch('/admin/pathao/user-success-rate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ phone: phone })
                    });
                    
                    const data = await response.json();
                    loading.style.display = 'none';
                    
                    if (data.success) {
                        displayResult(data.data, phone);
                    } else {
                        throw new Error(data.message || 'Unknown error');
                    }
                } catch (err) {
                    loading.style.display = 'none';
                    error.style.display = 'block';
                    error.textContent = 'Error: ' + err.message;
                    console.error('API Error:', err);
                }
            }
            
            function displayResult(data, phone) {
                // Format phone number for display
                const formattedPhone = phone.replace(/(\d{4})(\d{3})(\d{4})/, '$1 $2 $3');
                
                // Update basic information
                document.getElementById('phone-number').textContent = formattedPhone;
                
                // Extract customer rating from response
                const customerRating = data?.data?.customer_rating || 'unknown';
                const apiVersion = data?.data?.version || 'N/A';
                const addressBook = data?.data?.address_book || [];
                const showCount = data?.data?.show_count ? 'Yes' : 'No';
                const statusMessage = data?.message || 'N/A';
                const apiStatus = data?.status || 'N/A';
                
                // Format customer rating for display
                let ratingText = customerRating;
                let ratingClass = 'rating-average';
                
                switch(customerRating.toLowerCase()) {
                    case 'excellent_customer':
                        ratingText = 'Excellent Customer ★★★★★';
                        ratingClass = 'rating-excellent';
                        break;
                    case 'good_customer':
                        ratingText = 'Good Customer ★★★★';
                        ratingClass = 'rating-good';
                        break;
                    case 'average_customer':
                        ratingText = 'Average Customer ★★★';
                        ratingClass = 'rating-average';
                        break;
                    case 'poor_customer':
                        ratingText = 'Poor Customer ★★';
                        ratingClass = 'rating-poor';
                        break;
                    default:
                        ratingText = 'Not Rated';
                        ratingClass = 'rating-average';
                }
                
                // Update rating display
                document.getElementById('customer-rating-text').textContent = ratingText;
                document.getElementById('customer-rating-badge').textContent = ratingText.split(' ')[0];
                document.getElementById('customer-rating-badge').className = `rating-badge ${ratingClass}`;
                
                // Update other fields
                document.getElementById('api-version').textContent = apiVersion;
                document.getElementById('address-book-count').textContent = `${addressBook.length} saved address${addressBook.length !== 1 ? 'es' : ''}`;
                document.getElementById('show-count').textContent = showCount;
                document.getElementById('status-message').textContent = statusMessage;
                document.getElementById('api-status').textContent = apiStatus;
                document.getElementById('response-status').textContent = data.success ? 'Success' : 'Failed';
                
                // Show raw data
                document.getElementById('raw-data').textContent = JSON.stringify(data, null, 2);
                
                // Show result section
                result.style.display = 'block';
            }
            
            // Optional: Auto-focus on input
            phoneInput.focus();
        });
    </script>
</body>
</html>