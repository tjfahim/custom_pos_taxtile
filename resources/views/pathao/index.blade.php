<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card {
            height: 500px;
            display: flex;
            flex-direction: column;
        }
        .card-body {
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .search-box {
            margin-bottom: 10px;
        }
        .data-list {
            flex: 1;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 10px;
        }
        .badge-available {
            font-size: 0.7rem;
        }
        .loading-spinner {
            text-align: center;
            padding: 20px;
        }
        .form-control {
            cursor: pointer;
        }
        .selected-option {
            background-color: #007bff;
            color: white;
        }
        .form-select {
            height: 45px;
            font-size: 0.95rem;
        }
        .option-badges {
            font-size: 0.75rem;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center mb-4">
            <i class="fas fa-truck me-2"></i>Pathao Courier Management
        </h1>

        <!-- Selected Options Display -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        Selected City
                    </div>
                    <div class="card-body">
                        <div id="selectedCityDisplay" class="text-center py-4">
                            <i class="fas fa-city fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No city selected</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        Selected Zone
                    </div>
                    <div class="card-body">
                        <div id="selectedZoneDisplay" class="text-center py-4">
                            <i class="fas fa-map fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No zone selected</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        Selected Area
                    </div>
                    <div class="card-body">
                        <div id="selectedAreaDisplay" class="text-center py-4">
                            <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No area selected</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selection Fields -->
        <div class="row">
            <!-- Cities Column -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Select City</span>
                        <button class="btn btn-sm btn-light" onclick="loadCities()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="search-box">
                            <input type="text" id="citySearch" class="form-control form-control-sm" 
                                   placeholder="Search cities..." onkeyup="filterSelect('city')">
                        </div>
                        <div class="loading-spinner" id="cityLoading">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                            <small class="ms-2">Loading cities...</small>
                        </div>
                        <select class="form-select" id="citySelect" size="10" onchange="selectCity(this.value)">
                            <option value="">-- Select a City --</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Zones Column -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        Select Zone
                    </div>
                    <div class="card-body">
                        <div class="search-box">
                            <input type="text" id="zoneSearch" class="form-control form-control-sm" 
                                   placeholder="Search zones..." onkeyup="filterSelect('zone')" disabled>
                        </div>
                        <div class="loading-spinner" id="zoneLoading" style="display: none;">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                            <small class="ms-2">Loading zones...</small>
                        </div>
                        <select class="form-select" id="zoneSelect" size="10" onchange="selectZone(this.value)" disabled>
                            <option value="">-- Select a Zone --</option>
                        </select>
                        <div class="alert alert-info mt-3 mb-0" id="zoneMessage">
                            Select a city first to load zones
                        </div>
                    </div>
                </div>
            </div>

            <!-- Areas Column -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        Select Area
                    </div>
                    <div class="card-body">
                        <div class="search-box">
                            <input type="text" id="areaSearch" class="form-control form-control-sm" 
                                   placeholder="Search areas..." onkeyup="filterSelect('area')" disabled>
                        </div>
                        <div class="loading-spinner" id="areaLoading" style="display: none;">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                            <small class="ms-2">Loading areas...</small>
                        </div>
                        <select class="form-select" id="areaSelect" size="10" onchange="selectArea(this.value)" disabled>
                            <option value="">-- Select an Area --</option>
                        </select>
                        <div class="alert alert-info mt-3 mb-0" id="areaMessage">
                            Select a zone first to load areas
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        let citiesData = [];
        let zonesData = [];
        let areasData = [];
        let selectedCity = null;
        let selectedZone = null;
        let selectedArea = null;

        // Load cities on page load
        $(document).ready(function() {
            loadCities();
        });

        // Load cities
        function loadCities() {
            $('#cityLoading').show();
            $('#citySelect').hide().prop('disabled', true);
            $('#zoneSelect').hide().prop('disabled', true);
            $('#zoneSearch').prop('disabled', true);
            $('#areaSelect').hide().prop('disabled', true);
            $('#areaSearch').prop('disabled', true);
            resetSelections();
            
            $.ajax({
                url: '/admin/pathao/cities',
                type: 'GET',
                success: function(response) {
                    citiesData = response.data?.data?.data || response.data?.data || response.data || [];
                    renderCities();
                },
                error: function() {
                    alert('Failed to load cities. Please try again.');
                },
                complete: function() {
                    $('#cityLoading').hide();
                    $('#citySelect').show().prop('disabled', false);
                }
            });
        }

        // Render cities in select
        function renderCities() {
            const select = $('#citySelect');
            select.empty();
            select.append('<option value="">-- Select a City --</option>');
            
            if (!citiesData.length) {
                select.append('<option value="" disabled>No cities available</option>');
                return;
            }

            citiesData.forEach(city => {
                select.append(`<option value="${city.city_id}">${city.city_name} (ID: ${city.city_id})</option>`);
            });
        }

        // Select city
        function selectCity(cityId) {
            if (!cityId) {
                resetZonesAndAreas();
                resetSelections();
                return;
            }
            
            const city = citiesData.find(c => c.city_id == cityId);
            if (city) {
                selectedCity = city;
                updateDisplay('city', city.city_name, city.city_id);
                loadZones(cityId);
            }
        }

        // Load zones
        function loadZones(cityId) {
            $('#zoneMessage').hide();
            $('#zoneLoading').show();
            $('#zoneSelect').hide().empty().prop('disabled', true);
            $('#zoneSearch').prop('disabled', true);
            resetZonesAndAreas();
            
            $.ajax({
                url: `/admin/pathao/zones/${cityId}`,
                type: 'GET',
                success: function(response) {
                    zonesData = response.data?.data?.data || response.data?.data || response.data || [];
                    renderZones();
                },
                error: function() {
                    alert('Failed to load zones. Please try again.');
                },
                complete: function() {
                    $('#zoneLoading').hide();
                    $('#zoneSelect').show().prop('disabled', false);
                    $('#zoneSearch').prop('disabled', false);
                }
            });
        }

        // Render zones in select
        function renderZones() {
            const select = $('#zoneSelect');
            select.empty();
            select.append('<option value="">-- Select a Zone --</option>');
            
            if (!zonesData.length) {
                select.append('<option value="" disabled>No zones available for this city</option>');
                return;
            }

            zonesData.forEach(zone => {
                select.append(`<option value="${zone.zone_id}">${zone.zone_name} (ID: ${zone.zone_id})</option>`);
            });
        }

        // Select zone
        function selectZone(zoneId) {
            if (!zoneId) {
                resetAreas();
                selectedZone = null;
                updateDisplay('zone', '', '');
                return;
            }
            
            const zone = zonesData.find(z => z.zone_id == zoneId);
            if (zone) {
                selectedZone = zone;
                updateDisplay('zone', zone.zone_name, zone.zone_id);
                loadAreas(zoneId);
            }
        }

        // Load areas
        function loadAreas(zoneId) {
            $('#areaMessage').hide();
            $('#areaLoading').show();
            $('#areaSelect').hide().empty().prop('disabled', true);
            $('#areaSearch').prop('disabled', true);
            resetAreas();
            
            $.ajax({
                url: `/admin/pathao/areas/${zoneId}`,
                type: 'GET',
                success: function(response) {
                    areasData = response.data?.data?.data || response.data?.data || response.data || [];
                    renderAreas();
                },
                error: function() {
                    alert('Failed to load areas. Please try again.');
                },
                complete: function() {
                    $('#areaLoading').hide();
                    $('#areaSelect').show().prop('disabled', false);
                    $('#areaSearch').prop('disabled', false);
                }
            });
        }

        // Render areas in select
        function renderAreas() {
            const select = $('#areaSelect');
            select.empty();
            select.append('<option value="">-- Select an Area --</option>');
            
            if (!areasData.length) {
                select.append('<option value="" disabled>No areas available for this zone</option>');
                return;
            }

            areasData.forEach(area => {
                const deliveryBadge = area.home_delivery_available ? 
                    '<span class="badge bg-success badge-available me-1">✓ Delivery</span>' : 
                    '<span class="badge bg-danger badge-available me-1">✗ Delivery</span>';
                
                const pickupBadge = area.pickup_available ? 
                    '<span class="badge bg-success badge-available">✓ Pickup</span>' : 
                    '<span class="badge bg-danger badge-available">✗ Pickup</span>';
                
                select.append(`<option value="${area.area_id}">${area.area_name} ${deliveryBadge} ${pickupBadge}</option>`);
            });
        }

        // Select area
        function selectArea(areaId) {
            if (!areaId) {
                selectedArea = null;
                updateDisplay('area', '', '');
                return;
            }
            
            const area = areasData.find(a => a.area_id == areaId);
            if (area) {
                selectedArea = area;
                const deliveryText = area.home_delivery_available ? '✓ Home Delivery Available' : '✗ No Home Delivery';
                const pickupText = area.pickup_available ? '✓ Pickup Available' : '✗ No Pickup';
                updateDisplay('area', area.area_name, area.area_id, `${deliveryText}<br>${pickupText}`);
            }
        }

        // Update display cards
        function updateDisplay(type, name, id, extraInfo = '') {
            const display = $(`#selected${type.charAt(0).toUpperCase() + type.slice(1)}Display`);
            if (name && id) {
                display.html(`
                    <h5 class="fw-bold">${name}</h5>
                    <p class="mb-2"><strong>ID:</strong> ${id}</p>
                    ${extraInfo ? `<div class="mt-3">${extraInfo}</div>` : ''}
                `);
            } else {
                const icons = {
                    'city': 'fa-city',
                    'zone': 'fa-map',
                    'area': 'fa-map-marker-alt'
                };
                display.html(`
                    <i class="fas ${icons[type]} fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No ${type} selected</p>
                `);
            }
        }

        // Reset functions
        function resetSelections() {
            selectedCity = null;
            selectedZone = null;
            selectedArea = null;
            updateDisplay('city', '', '');
            updateDisplay('zone', '', '');
            updateDisplay('area', '', '');
        }

        function resetZonesAndAreas() {
            $('#zoneSelect').val('').prop('disabled', true);
            $('#zoneSearch').val('').prop('disabled', true);
            $('#areaSelect').val('').prop('disabled', true);
            $('#areaSearch').val('').prop('disabled', true);
            $('#areaMessage').show();
            selectedZone = null;
            selectedArea = null;
            updateDisplay('zone', '', '');
            updateDisplay('area', '', '');
        }

        function resetAreas() {
            $('#areaSelect').val('').prop('disabled', true);
            $('#areaSearch').val('').prop('disabled', true);
            $('#areaMessage').show();
            selectedArea = null;
            updateDisplay('area', '', '');
        }

        // Search filter for select fields
        function filterSelect(type) {
            const searchTerm = $(`#${type}Search`).val().toLowerCase();
            const select = $(`#${type}Select`);
            
            if (!searchTerm) {
                select.find('option').show();
                return;
            }
            
            select.find('option').each(function() {
                const text = $(this).text().toLowerCase();
                $(this).toggle(text.includes(searchTerm));
            });
        }
    </script>
</body>
</html>