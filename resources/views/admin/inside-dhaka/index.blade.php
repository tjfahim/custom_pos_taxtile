@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fa fa-map-marker"></i> Inside Dhaka Management
                </h5>
                <div>
                    <a href="{{ route('admin.inside-dhaka.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Add Zone
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <div class="card-body p-2">
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

                <div class="table-responsive">
                    <table class="table table-sm table-hover" id="insideDhakaTable">
                        <thead class="table-light">
                            <tr>
                                <th>SL</th>
                                <th>Zone</th>
                                <th>City</th>
                                <th>Status</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($insideDhaka as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm me-2">
                                            <div class="avatar-title bg-info rounded-circle text-white">
                                                <i class="fa fa-map-marker"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <strong>{{ $item->zone->zone_name ?? 'N/A' }}</strong>
                                            @if($item->is_active)
                                                <span class="badge bg-success badge-sm">Active</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        {{ $item->zone->city->city_name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    @if($item->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $item->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.inside-dhaka.edit', $item->id) }}" 
                                           class="btn btn-warning" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="{{ route('admin.inside-dhaka.toggle-status', $item->id) }}" 
                                           class="btn btn-{{ $item->is_active ? 'secondary' : 'success' }}" 
                                           title="{{ $item->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fa fa-{{ $item->is_active ? 'pause' : 'play' }}"></i>
                                        </a>
                                        <form action="{{ route('admin.inside-dhaka.destroy', $item->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" 
                                                    onclick="return confirm('Are you sure you want to remove this zone?')"
                                                    title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No zones found in Inside Dhaka list.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                  
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}
.badge-sm {
    font-size: 10px;
    padding: 2px 6px;
}
.table-light {
    background-color: #f8f9fa;
}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#insideDhakaTable').DataTable({
        order: [[0, 'asc']],
        pageLength: 20,
        responsive: true,
        language: {
            emptyTable: 'No zones found in Inside Dhaka list',
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ zones",
            infoEmpty: "Showing 0 to 0 of 0 zones",
            infoFiltered: "(filtered from _MAX_ total zones)"
        },
        dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>tip',
        drawCallback: function() {
            // Remove DataTable pagination since we have Laravel pagination
            $('#insideDhakaTable_paginate').remove();
        }
    });
});
</script>
@endsection