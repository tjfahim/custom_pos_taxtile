@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fa fa-user-tag"></i> Edit Role: {{ $role->name }}
                </h5>
                <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Back to Roles
                </a>
            </div>

            <div class="card-body">
                @if(in_array($role->name, ['admin', 'manager', 'staff']))
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i>
                        <strong>Default Role:</strong> This is a default system role. Some restrictions may apply.
                    </div>
                @endif
                
                <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Role Name *</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $role->name) }}" 
                               {{ in_array($role->name, ['admin', 'manager', 'staff']) ? 'readonly' : 'required' }}>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if(in_array($role->name, ['admin', 'manager', 'staff']))
                            <small class="text-muted">Default role name cannot be changed</small>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Permissions</label>
                        <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                            @foreach($permissions as $module => $modulePermissions)
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <h6 class="text-primary mb-0">
                                        <i class="fa fa-folder me-1"></i> {{ ucfirst($module) }}
                                    </h6>
                                   
                                </div>
                                <div class="row">
                                    @foreach($modulePermissions as $permission)
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input permission-checkbox" 
                                                   type="checkbox" 
                                                   name="permissions[]" 
                                                   value="{{ $permission->id }}" 
                                                   id="permission_{{ $permission->id }}"
                                                   {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                {{ ucwords(str_replace('_', ' ', $permission->name)) }}
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <hr class="my-2">
                            </div>
                            @endforeach
                        </div>
                        @error('permissions')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Update Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.permission-checkbox:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>


@endsection