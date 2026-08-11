{{-- resources/views/admin/attendance/index.blade.php --}}

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
                    <i class="fa fa-calendar-check mr-2"></i>
                    Attendance Management
                </h5>
                <div class="d-flex align-items-center">
                    <!-- Date Filter -->
                    <div class="mr-3">
                        <input type="date" 
                               id="dateFilter" 
                               class="form-control form-control-sm" 
                               value="{{ request('date', $date ?? date('Y-m-d')) }}">
                    </div>
                    <!-- Search Box -->
                    <div class="mr-3">
                        <input type="text" 
                               id="searchInput" 
                               class="form-control form-control-sm" 
                               placeholder="Search staff..."
                               value="{{ request('search') }}"
                               style="width: 200px;">
                    </div>
                    <a href="{{ route('admin.attendance.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Mark Attendance
                    </a>
                </div>
            </div>

            <div class="card-body" id="attendanceTableContainer">
                @include('admin.attendance.partials.table')
            </div>
        </div>
    </div>
</div>

<style>
    .table tbody tr:hover {
        background-color: #f5f5f5;
    }
    
    #attendanceTable th,
    #attendanceTable td {
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

    // Date filter change
    $('#dateFilter').on('change', function() {
        const date = $(this).val();
        const search = $('#searchInput').val();
        performFilter(date, search);
    });

    // Live search functionality
    $('#searchInput').on('keyup', function() {
        clearTimeout(searchTimer);
        const searchValue = $(this).val();
        const date = $('#dateFilter').val();
        
        if (currentRequest) {
            currentRequest.abort();
        }
        
        $(this).addClass('search-loading-active');
        
        searchTimer = setTimeout(function() {
            performFilter(date, searchValue);
        }, 500);
    });

    function performFilter(date, search) {
        // Update URL
        const url = new URL(window.location);
        if (date) {
            url.searchParams.set('date', date);
        } else {
            url.searchParams.delete('date');
        }
        if (search) {
            url.searchParams.set('search', search);
        } else {
            url.searchParams.delete('search');
        }
        window.history.pushState({}, '', url);

        // Show loading
        $('#attendanceTableContainer').append('<div class="text-center py-4" id="loadingIndicator"><i class="fa fa-spinner fa-spin fa-2x"></i><p>Loading...</p></div>');
        
        currentRequest = $.ajax({
            url: window.location.pathname,
            method: 'GET',
            data: { date: date, search: search },
            success: function(response) {
                $('#attendanceTableContainer').html(response);
                $('[title]').tooltip('dispose').tooltip();
                currentRequest = null;
            },
            error: function(xhr) {
                if (xhr.statusText !== 'abort') {
                    console.error('Filter failed:', xhr);
                }
            },
            complete: function() {
                $('#loadingIndicator').remove();
                $('#searchInput').removeClass('search-loading-active');
            }
        });
    }

    // Handle browser back/forward
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const date = urlParams.get('date') || '{{ $date ?? date("Y-m-d") }}';
        const search = urlParams.get('search') || '';
        $('#dateFilter').val(date);
        $('#searchInput').val(search);
        performFilter(date, search);
    });

    // Initialize tooltips
    $('[title]').tooltip();
});
</script>

@endsection