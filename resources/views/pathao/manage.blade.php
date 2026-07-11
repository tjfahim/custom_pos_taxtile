@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fa fa-map-marker"></i> Pathao Location Management
                </h5>
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Success/Error Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fa fa-cloud-download"></i> Sync Pathao Data
                                </h5>
                                <p class="card-text text-light">
                                    Fetch all cities, zones, and areas from Pathao API and store in database.
                                </p>
                                <button type="button" id="syncBtn" class="btn btn-light text-primary" onclick="syncLocations()">
                                    <i class="fa fa-refresh"></i> Sync Now
                                </button>
                                <span id="syncStatus" class="ml-3 text-light"></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fa fa-search"></i> Search Locations
                                </h5>
                                <p class="card-text text-light">
                                    Search by city, zone, area name or ID. Supports fuzzy matching!
                                </p>
                                <div class="input-group">
                                    <input type="text" id="searchInput" class="form-control" placeholder="e.g. Dhaka, Alia, Bagerhat, 52, 156..." />
                                    <div class="input-group-append">
                                        <button class="btn btn-light" type="button" onclick="searchLocations()">
                                            <i class="fa fa-search"></i> Search
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h3 class="text-primary" id="cityCount">0</h3>
                                <p class="text-muted mb-0">Cities</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h3 class="text-success" id="zoneCount">0</h3>
                                <p class="text-muted mb-0">Zones</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body text-center">
                                <h3 class="text-info" id="areaCount">0</h3>
                                <p class="text-muted mb-0">Areas</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Search Results -->
                <div id="searchResults" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5>
                            <i class="fa fa-search"></i> Search Results
                            <span id="resultSummary" class="badge badge-info ml-2"></span>
                        </h5>
                        <div>
                            <button class="btn btn-sm btn-secondary" onclick="clearSearch()">
                                <i class="fa fa-times"></i> Clear
                            </button>
                        </div>
                    </div>
                    <div id="resultsContainer"></div>
                </div>

                <!-- Loading Spinner -->
                <div id="loadingSpinner" class="text-center" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Processing...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Toastr CSS and JS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
// Toastr configuration
toastr.options = {
    "closeButton": true,
    "progressBar": true,
    "positionClass": "toast-top-right",
    "timeOut": "5000"
};

// Load statistics on page load
$(document).ready(function() {
    loadStatistics();
});

function loadStatistics() {
    $.ajax({
        url: '{{ route("admin.pathao.statistics") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                $('#cityCount').text(response.data.cities);
                $('#zoneCount').text(response.data.zones);
                $('#areaCount').text(response.data.areas);
            }
        },
        error: function() {
            console.error('Failed to load statistics');
        }
    });
}

// ============ SYNC FUNCTIONS ============

function syncLocations() {
    if (!confirm('This will fetch all Pathao locations from API. Continue?')) {
        return;
    }
    
    $('#syncBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Starting sync...');
    $('#syncStatus').text('Starting sync in background...');
    
    $.ajax({
        url: '{{ route("admin.pathao.store-all-direct") }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                $('#syncStatus').html('<i class="fa fa-check"></i> Sync started! Check logs for progress.');
                toastr.success('Sync started in background! Check logs for progress.');
                loadStatistics();
            } else {
                $('#syncStatus').html('<i class="fa fa-exclamation-circle"></i> Sync failed');
                toastr.error(response.message || 'Sync failed');
                $('#syncBtn').prop('disabled', false).html('<i class="fa fa-refresh"></i> Sync Now');
            }
        },
        error: function(xhr) {
            var message = 'Sync failed: ' + (xhr.responseJSON?.message || 'Unknown error');
            $('#syncStatus').html('<i class="fa fa-exclamation-circle"></i> ' + message);
            toastr.error(message);
            $('#syncBtn').prop('disabled', false).html('<i class="fa fa-refresh"></i> Sync Now');
        }
    });
}

// ============ SEARCH FUNCTIONS ============

function searchLocations() {
    var searchTerm = $('#searchInput').val().trim();
    
    if (!searchTerm) {
        toastr.warning('Please enter a search term');
        return;
    }
    
    $('#loadingSpinner').show();
    $('#searchResults').hide();
    
    $.ajax({
        url: '{{ route("admin.pathao.search") }}',
        type: 'GET',
        data: { search: searchTerm },
        success: function(response) {
            if (response.success) {
                displayAdvancedResults(response.data);
            } else {
                toastr.error(response.message || 'Search failed');
            }
        },
        error: function(xhr) {
            var message = 'Search failed: ' + (xhr.responseJSON?.message || 'Unknown error');
            toastr.error(message);
            console.error('Search error:', xhr);
        },
        complete: function() {
            $('#loadingSpinner').hide();
        }
    });
}
function displayAdvancedResults(data) {
    var results = data.results || [];
    var summary = data.summary || { total: 0, exact: 0, high: 0, medium: 0 };
    
    if (results.length === 0) {
        $('#searchResults').show();
        $('#resultSummary').text('No results found');
        $('#resultsContainer').html(`
            <div class="alert alert-warning">
                <i class="fa fa-info-circle"></i> 
                No results found for your search. Try with different keywords.
            </div>
        `);
        return;
    }
    
    $('#searchResults').show();
    $('#resultSummary').text(`${summary.total} results found (Exact: ${summary.exact}, High: ${summary.high}, Medium: ${summary.medium})`);
    
    var html = '';
    
    // Group by match type
    var exactResults = results.filter(r => r.match_percent === 100);
    var highResults = results.filter(r => r.match_percent >= 80 && r.match_percent < 100);
    var mediumResults = results.filter(r => r.match_percent < 80);
    
    // Display Exact Matches
    if (exactResults.length > 0) {
        html += '<div class="card mb-3 border-success">';
        html += '<div class="card-header bg-success text-white">';
        html += '<i class="fa fa-check-circle"></i> Exact Matches (' + exactResults.length + ')';
        html += '</div>';
        html += '<div class="card-body">';
        exactResults.forEach(function(item) {
            html += getResultCard(item);
        });
        html += '</div>';
        html += '</div>';
    }
    
    // Display High Matches
    if (highResults.length > 0) {
        html += '<div class="card mb-3 border-primary">';
        html += '<div class="card-header bg-primary text-white">';
        html += '<i class="fa fa-star"></i> High Matches (' + highResults.length + ')';
        html += '</div>';
        html += '<div class="card-body">';
        highResults.forEach(function(item) {
            html += getResultCard(item);
        });
        html += '</div>';
        html += '</div>';
    }
    
    // Display Medium Matches
    if (mediumResults.length > 0) {
        html += '<div class="card mb-3 border-warning">';
        html += '<div class="card-header bg-warning text-dark">';
        html += '<i class="fa fa-search"></i> Related Matches (' + mediumResults.length + ')';
        html += '</div>';
        html += '<div class="card-body">';
        mediumResults.forEach(function(item) {
            html += getResultCard(item);
        });
        html += '</div>';
        html += '</div>';
    }
    
    $('#resultsContainer').html(html);
    
    // Scroll to results
    $('html, body').animate({
        scrollTop: $('#searchResults').offset().top - 100
    }, 500);
}

function getResultCard(item) {
    var badgeColor = 'secondary';
    var icon = 'fa-map-marker';
    
    if (item.type === 'city') {
        badgeColor = 'primary';
        icon = 'fa-building';
    } else if (item.type === 'zone') {
        badgeColor = 'success';
        icon = 'fa-map';
    } else if (item.type === 'area') {
        badgeColor = 'info';
        icon = 'fa-location-arrow';
    }
    
    // Match percentage badge
    var matchBadge = '';
    if (item.match_percent === 100) {
        matchBadge = '<span class="badge badge-success ml-2">100% Match</span>';
    } else if (item.match_percent >= 80) {
        matchBadge = '<span class="badge badge-primary ml-2">' + item.match_percent + '% Match</span>';
    } else if (item.match_percent >= 60) {
        matchBadge = '<span class="badge badge-warning ml-2">' + item.match_percent + '% Match</span>';
    }
    
    // Delivery/Pickup info for areas
    var deliveryInfo = '';
    if (item.type === 'area') {
        var deliveryIcon = item.home_delivery_available ? '✅' : '❌';
        var pickupIcon = item.pickup_available ? '✅' : '❌';
        deliveryInfo = '<br><small>Delivery: ' + deliveryIcon + ' | Pickup: ' + pickupIcon + '</small>';
    }
    
    // Build hierarchy display
    var hierarchyHtml = '';
    var h = item.hierarchy || {};
    
    if (h.city) {
        hierarchyHtml += '<span class="badge badge-primary">🏙️ ' + h.city + '</span>';
    }
    if (h.zone) {
        hierarchyHtml += ' <i class="fa fa-arrow-right text-muted"></i> ';
        hierarchyHtml += '<span class="badge badge-success">📍 ' + h.zone + '</span>';
    }
    if (h.area) {
        hierarchyHtml += ' <i class="fa fa-arrow-right text-muted"></i> ';
        hierarchyHtml += '<span class="badge badge-info">📍 ' + h.area + '</span>';
    }
    
    return `
        <div class="col-md-12 mb-3">
            <div class="card result-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="card-title">
                                <span class="badge badge-${badgeColor}">
                                    <i class="fa ${icon}"></i> ${item.type.charAt(0).toUpperCase() + item.type.slice(1)}
                                </span>
                                ${matchBadge}
                            </h6>
                            <p class="card-text">
                                <strong>${item.name}</strong>
                                ${deliveryInfo}
                            </p>
                            <div class="hierarchy-display">
                                <i class="fa fa-sitemap text-muted"></i> 
                                ${hierarchyHtml}
                            </div>
                            <div class="small text-muted mt-2">
                                <span class="badge badge-light">ID: ${item.id}</span>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <button class="btn btn-sm btn-outline-primary" onclick="selectLocation('${item.id}', '${item.type}')">
                                <i class="fa fa-check"></i> Select
                            </button>
                            ${item.type === 'area' ? `
                                <button class="btn btn-sm btn-outline-info" onclick="showHierarchy('${item.id}', '${item.type}')">
                                    <i class="fa fa-sitemap"></i> View Path
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function selectLocation(id, type) {
    toastr.success('Selected ' + type + ' ID: ' + id);
    console.log('Selected:', { id: id, type: type });
}

function showHierarchy(id, type) {
    if (type !== 'area') {
        toastr.info('Full hierarchy is available for areas only');
        return;
    }
    
    $.ajax({
        url: '{{ route("admin.pathao.hierarchy", "") }}/' + id,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                var data = response.data;
                toastr.info('Full Path: ' + data.full_address);
                
                // Show in alert for demo
                alert(
                    '📍 Location Hierarchy\n\n' +
                    'City: ' + (data.city?.city_name || 'N/A') + '\n' +
                    'Zone: ' + (data.zone?.zone_name || 'N/A') + '\n' +
                    'Area: ' + data.area.area_name + '\n\n' +
                    'Full Address: ' + data.full_address
                );
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('Failed to fetch hierarchy');
        }
    });
}

function clearSearch() {
    $('#searchInput').val('');
    $('#searchResults').hide();
    $('#resultsContainer').html('');
    $('#resultSummary').text('');
}

// Handle Enter key in search input
$(document).ready(function() {
    $('#searchInput').keypress(function(e) {
        if (e.which === 13) {
            searchLocations();
        }
    });
});
</script>

<style>
    .badge {
        font-size: 13px;
        padding: 8px 12px;
        display: inline-block;
    }
    .card-header i {
        margin-right: 8px;
    }
    #searchResults {
        margin-top: 20px;
    }
    #syncStatus {
        font-size: 14px;
        margin-left: 10px;
    }
    .result-card {
        transition: all 0.3s ease;
    }
    .result-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .match-percent {
        font-weight: bold;
    }
    .match-100 { color: #28a745; }
    .match-80 { color: #007bff; }
    .match-60 { color: #ffc107; }
</style>
@endsection