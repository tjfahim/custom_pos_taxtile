@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fa fa-user-tag"></i> Role Management
                </h5>
                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> Add New Role
                </a>
            </div>

            <div class="card-body p-2">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                <div class="table-responsive">
                    <table class="table table-sm table-hover" id="rolesTable">
                        <thead class="table-light">
                            <tr>
                                <th>SL</th>
                                <th>Role Name</th>
                                <th>Users Count</th>
                                <th>Permissions</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roles as $index => $role)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="badge bg-primary">{{ ucfirst($role->name) }}</span>
                                    @if(in_array($role->name, ['admin', 'manager', 'staff']))
                                        <span class="badge bg-secondary badge-sm ms-1">Default</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-info">{{ $role->users_count }}</span>
                                </td>
                                <td>
                                    @if($role->permissions->count() > 0)
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($role->permissions->take(3) as $permission)
                                                <span class="badge bg-secondary badge-sm">
                                                    {{ str_replace('_', ' ', $permission->name) }}
                                                </span>
                                            @endforeach
                                            @if($role->permissions->count() > 3)
                                                <span class="badge bg-light text-dark">
                                                    +{{ $role->permissions->count() - 3 }} more
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">No permissions</span>
                                    @endif
                                </td>
                                <td>{{ $role->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.roles.edit', $role->id) }}" 
                                           class="btn btn-warning" title="Edit">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        @if(!in_array($role->name, ['admin', 'manager', 'staff']))
                                        <form action="{{ route('admin.roles.destroy', $role->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" 
                                                    onclick="return confirm('Are you sure you want to delete this role?')"
                                                    title="Delete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge-sm {
    font-size: 0.7em;
    padding: 0.25em 0.6em;
}
</style>

<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#rolesTable').DataTable({
        order: [[0, 'asc']],
        pageLength: 20,
        responsive: true,
        language: {
            emptyTable: 'No roles found'
        }
    });
});
</script>
@endsection