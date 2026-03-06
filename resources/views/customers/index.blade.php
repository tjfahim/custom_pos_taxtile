@extends('admin.layouts.master')

@section('main_content')

<div class="content mt-3">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="col-sm-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="col-sm-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    @endif

    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fa fa-users mr-2"></i>
                    Customer List
                </h5>
                <div class="d-flex align-items-center">
                    <!-- Search Box -->
                    <div class="mr-3">
                        <input type="text" 
                               id="searchInput" 
                               class="form-control form-control-sm" 
                               placeholder="Search customers..."
                               value="{{ request('search') }}"
                               style="width: 250px;">
                    </div>
                    @can('create customers')
                    <a href="{{ route('admin.customers.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Add Customer
                    </a>
                    @endcan
                </div>
            </div>

            <div class="card-body" id="customerTableContainer">
                @include('customers.partials.table')
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    
    .table tbody tr:hover {
        background-color: #f5f5f5;
    }
    
    #customerTable th,
    #customerTable td {
        vertical-align: middle;
        padding: 12px 8px;
    }
    
    /* Loading indicator */
    .search-loading {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        display: none;
    }
    
    .position-relative {
        position: relative;
    }
</style>

<script>
$(document).ready(function() {
    let searchTimer;
    let currentRequest = null;

    // Live search functionality
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimer);
        const searchValue = $(this).val();
        
        // Cancel previous request if it exists
        if (currentRequest) {
            currentRequest.abort();
        }
        
        // Add loading indicator
        $(this).addClass('search-loading-active');
        
        searchTimer = setTimeout(function() {
            performSearch(searchValue);
        }, 500); // Wait 500ms after user stops typing
    });

    function performSearch(search) {
        // Update URL without reloading page (optional)
        const url = new URL(window.location);
        if (search) {
            url.searchParams.set('search', search);
        } else {
            url.searchParams.delete('search');
        }
        window.history.pushState({}, '', url);

        // Show loading state
        $('#customerTableContainer').append('<div class="text-center py-4" id="loadingIndicator"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Searching...</p></div>');
        
        // Make AJAX request
        currentRequest = $.ajax({
            url: window.location.pathname,
            method: 'GET',
            data: { search: search },
            success: function(response) {
                $('#customerTableContainer').html(response);
                // Reinitialize tooltips
                $('[title]').tooltip('dispose').tooltip();
                currentRequest = null;
            },
            error: function(xhr) {
                if (xhr.statusText !== 'abort') {
                    console.error('Search failed:', xhr);
                    $('#customerTableContainer').append('<div class="alert alert-danger">Search failed. Please try again.</div>');
                }
            },
            complete: function() {
                $('#loadingIndicator').remove();
            }
        });
    }

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const search = urlParams.get('search') || '';
        $('#searchInput').val(search);
        performSearch(search);
    });
});
</script>

<!-- Initialize tooltips -->
<script>
$(document).ready(function() {
    $('[title]').tooltip();
});
</script>

@endsection