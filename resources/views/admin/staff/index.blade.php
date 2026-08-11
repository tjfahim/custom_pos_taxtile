{{-- resources/views/admin/staff/index.blade.php --}}

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
                    Staff Management
                </h5>
                <div class="d-flex align-items-center">
                    <!-- Search Box -->
                    <div class="mr-3">
                        <input type="text" 
                               id="searchInput" 
                               class="form-control form-control-sm" 
                               placeholder="Search staff..."
                               value="{{ request('search') }}"
                               style="width: 250px;">
                    </div>
                    <a href="{{ route('admin.staff.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Add Staff
                    </a>
                </div>
            </div>

            <div class="card-body" id="staffTableContainer">
                @include('admin.staff.partials.table')
            </div>
        </div>
    </div>
</div>

<style>
    .table tbody tr:hover {
        background-color: #f5f5f5;
    }
    
    #staffTable th,
    #staffTable td {
        vertical-align: middle;
        padding: 12px 8px;
    }
    
    .search-loading-active {
        background-image: url('data:image/gif;base64,R0lGODlhEAAQAPIAAP///wAAAMLCwkJCQgAAAGJiYoKCgpKSkvHx8bGxsQAAAAAAACH/C05FVFNDQVBFMi4wAwEAAAAh/hpDcmVhdGVkIHdpdGggYWpheGxvYWQuaW5mbwAh+QQJCgAAACwAAAAAEAAQAAADMwi63P8wykiNnD2N3HQDVGhxSx5i/kGsYVqAeKd6wzvo1x2pXbNl0cL4sI7z1yWhXwAh+QQJCgAAACwAAAAAEAAQAAADNAi63P8wYh0aJyHZJioRyuB3gpeEpCwu0U2SfU4Jg0ajTphGy91uC3CIPtKstGWnVQAh+QQJCgAAACwAAAAAEAAQAAADNgi63P8w2uZhyTg4hKrg4V0GEWqnWw0rCyKbS7I0C+5wq7ABcNO4A2jqRAXi8k2iVgAh+QQJCgAAACwAAAAAEAAQAAADNwi63P8w2w3krfDxWtYJp2YyZEWjEUMdGxApWzrxJtR2TxnLscpS9TZwxIPDKB9T5wAh+QQJCgAAACwAAAAAEAAQAAADNwi63P8wg4y5BfVTPrfMZx6ZGP1BlIxXH66TAnBw+MRjP6fw6jQ2lFhROyHBCw8d6wAh+QQJCgAAACwAAAAAEAAQAAADNwi63P8w2Zg7C4I4dCgc5pZ7sFhZ9IZhX0L4iIC5DuhLYMRwBkI2+WMKxRD0ul9pIgA7');
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 20px;
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
        }, 500);
    });

    function performSearch(search) {
        // Update URL without reloading page
        const url = new URL(window.location);
        if (search) {
            url.searchParams.set('search', search);
        } else {
            url.searchParams.delete('search');
        }
        window.history.pushState({}, '', url);

        // Show loading state
        $('#staffTableContainer').append('<div class="text-center py-4" id="loadingIndicator"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Searching...</p></div>');
        
        // Make AJAX request
        currentRequest = $.ajax({
            url: window.location.pathname,
            method: 'GET',
            data: { search: search },
            success: function(response) {
                $('#staffTableContainer').html(response);
                // Reinitialize tooltips
                $('[title]').tooltip('dispose').tooltip();
                currentRequest = null;
            },
            error: function(xhr) {
                if (xhr.statusText !== 'abort') {
                    console.error('Search failed:', xhr);
                    $('#staffTableContainer').append('<div class="alert alert-danger">Search failed. Please try again.</div>');
                }
            },
            complete: function() {
                $('#loadingIndicator').remove();
                $('#searchInput').removeClass('search-loading-active');
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

    // Initialize tooltips
    $('[title]').tooltip();
});
</script>

@endsection