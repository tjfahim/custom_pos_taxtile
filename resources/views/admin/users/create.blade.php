@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fa fa-user-plus"></i> Create New User
                </h5>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.users.store') }}" method="POST">
                    @csrf
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-12">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirm Password *</label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" required>
                        </div>
                        
                        <!-- Status Field -->
                        <div class="col-md-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" 
                                       id="status" name="status" value="1" 
                                       {{ old('status', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="status">
                                    <strong>Active Status</strong>
                                    <small class="text-muted d-block">User will be able to login if active</small>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Team Members Selection -->
                        <div class="col-md-12">
                            <label class="form-label">Team Members</label>
                            <div class="border rounded p-3">
                                <small class="text-muted d-block mb-2">Select users to add as your team members</small>
                                @foreach($allUsers as $user)
                                <div class="form-check mb-2">
                                    <input class="form-check-input team-member-checkbox" 
                                           type="checkbox" 
                                           name="team_members[]" 
                                           value="{{ $user->id }}" 
                                           id="team_{{ $user->id }}"
                                           {{ in_array($user->id, old('team_members', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="team_{{ $user->id }}">
                                        <strong>{{ $user->name }}</strong>
                                        <small class="text-muted">({{ $user->email }})</small>
                                        @if($user->id == auth()->id())
                                            <span class="badge bg-info">You</span>
                                        @endif
                                    </label>
                                </div>
                                @endforeach
                                @error('team_members')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Default Team Mate Selection -->
                        <div class="col-md-12" id="default-team-mate-section">
                            <label for="default_team_mate" class="form-label">Default Team Mate</label>
                            <select class="form-select @error('default_team_mate') is-invalid @enderror" 
                                    id="default_team_mate" name="default_team_mate">
                                <option value="">Select default team mate</option>
                                @foreach($allUsers as $user)
                                    <option value="{{ $user->id }}" 
                                        {{ old('default_team_mate') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Default team mate must be selected from your team members</small>
                            @error('default_team_mate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Roles Selection -->
                        <div class="col-md-12">
                            <label class="form-label">Assign Roles *</label>
                            <div class="border rounded p-3">
                                @foreach($roles as $role)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" 
                                           name="roles[]" value="{{ $role->name }}" 
                                           id="role_{{ $role->id }}"
                                           {{ in_array($role->name, old('roles', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="role_{{ $role->id }}">
                                        <span class="badge bg-primary">{{ ucfirst($role->name) }}</span>
                                    </label>
                                </div>
                                @endforeach
                                @error('roles')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Create User
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const teamCheckboxes = document.querySelectorAll('.team-member-checkbox');
    const defaultTeamMateSelect = document.getElementById('default_team_mate');
    
    // Filter default team mate options based on selected team members
    function updateDefaultTeamMateOptions() {
        const selectedTeamMembers = new Set();
        teamCheckboxes.forEach(checkbox => {
            if (checkbox.checked) {
                selectedTeamMembers.add(checkbox.value);
            }
        });
        
        const options = defaultTeamMateSelect.querySelectorAll('option');
        options.forEach(option => {
            if (option.value === '') {
                option.style.display = ''; // Keep the empty option
                return;
            }
            if (selectedTeamMembers.has(option.value)) {
                option.style.display = '';
                option.disabled = false;
            } else {
                option.style.display = 'none';
                option.disabled = true;
                if (option.selected) {
                    defaultTeamMateSelect.value = '';
                }
            }
        });
    }
    
    // Add change event listeners to team checkboxes
    teamCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateDefaultTeamMateOptions);
    });
    
    // Initial update
    updateDefaultTeamMateOptions();
});
</script>
@endsection