{{-- resources/views/admin/attendance/create.blade.php --}}

@extends('admin.layouts.master')

@section('main_content')

<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fa fa-calendar-check mr-2"></i>
                    Mark Attendance - {{ $today->format('d M, Y') }}
                    @if($isFriday)
                        <span class="badge badge-warning ml-2">Friday</span>
                    @endif
                </h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.attendance.store') }}" method="POST" id="attendanceForm">
                    @csrf
                    
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="attendance_date">Attendance Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       class="form-control @error('attendance_date') is-invalid @enderror" 
                                       id="attendance_date" 
                                       name="attendance_date" 
                                       value="{{ old('attendance_date', $today->format('Y-m-d')) }}" 
                                       required>
                                @error('attendance_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Apply to All</label>
                                <div class="d-flex">
                                    <div class="custom-control custom-checkbox mr-3">
                                        <input type="checkbox" class="custom-control-input" id="is_friday" name="is_friday" value="1"
                                               {{ old('is_friday', $isFriday) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_friday">
                                            <span class="badge badge-secondary">Friday</span>
                                        </label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_govt_holiday" name="is_govt_holiday" value="1"
                                               {{ old('is_govt_holiday') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_govt_holiday">
                                            <span class="badge badge-primary">Govt. Holiday</span>
                                        </label>
                                    </div>
                                </div>
                                <small class="text-muted">These will apply to all staff members</small>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="attendanceTable">
                            <thead class="thead-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Staff Name</th>
                                    <th>Designation</th>
                                    <th width="120">In Time</th>
                                    <th width="120">Out Time</th>
                                    <th width="120">On Leave</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($staffMembers as $index => $staff)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <input type="hidden" name="staff_data[{{ $index }}][staff_id]" value="{{ $staff->id }}">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-circle bg-info text-white mr-2" 
                                                     style="width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                                                    {{ strtoupper(substr($staff->name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <strong>{{ $staff->name }}</strong>
                                                    @if($staff->email)
                                                        <br><small class="text-muted">{{ $staff->email }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-info">{{ $staff->designation }}</span></td>
                                        <td>
                                            <input type="time" 
                                                   class="form-control form-control-sm time-input" 
                                                   name="staff_data[{{ $index }}][in_time]" 
                                                   value="{{ old('staff_data.' . $index . '.in_time') }}">
                                        </td>
                                        <td>
                                            <input type="time" 
                                                   class="form-control form-control-sm time-input" 
                                                   name="staff_data[{{ $index }}][out_time]" 
                                                   value="{{ old('staff_data.' . $index . '.out_time') }}">
                                        </td>
                                        <td class="text-center">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" 
                                                       class="custom-control-input leave-checkbox" 
                                                       id="leave_{{ $staff->id }}" 
                                                       name="staff_on_leave[]" 
                                                       value="{{ $staff->id }}"
                                                       {{ old('staff_on_leave') && in_array($staff->id, old('staff_on_leave')) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="leave_{{ $staff->id }}">Leave</label>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" 
                                                   class="form-control form-control-sm" 
                                                   name="staff_data[{{ $index }}][note]" 
                                                   placeholder="Note (optional)"
                                                   value="{{ old('staff_data.' . $index . '.note') }}">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">
                                            <div class="py-3">
                                                <i class="fa fa-users fa-2x text-muted mb-2"></i>
                                                <h6>No active staff members found</h6>
                                                <p class="text-muted mb-0">Please add staff members first.</p>
                                                <a href="{{ route('admin.staff.create') }}" class="btn btn-primary btn-sm mt-2">
                                                    <i class="fa fa-plus"></i> Add Staff
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group mt-4">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fa fa-save"></i> Save Attendance
                        </button>
                        <a href="{{ route('admin.attendance.index') }}" class="btn btn-secondary">
                            <i class="fa fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-weight: bold;
        flex-shrink: 0;
    }
    
    .time-input {
        width: 120px !important;
    }
    
    #attendanceTable td {
        vertical-align: middle;
    }
</style>

<script>
$(document).ready(function() {
    // When leave checkbox is checked, clear and disable time inputs
    $('.leave-checkbox').on('change', function() {
        const row = $(this).closest('tr');
        if ($(this).is(':checked')) {
            row.find('.time-input').val('').prop('disabled', true);
        } else {
            row.find('.time-input').prop('disabled', false);
        }
    });

    // Initialize leave checkboxes state
    $('.leave-checkbox:checked').each(function() {
        $(this).closest('tr').find('.time-input').prop('disabled', true);
    });

    // Validate form before submit
    $('#attendanceForm').on('submit', function(e) {
        let hasError = false;
        let errorMessages = [];
        
        // Check if attendance_date is set
        if (!$('#attendance_date').val()) {
            hasError = true;
            errorMessages.push('Please select an attendance date.');
        }
        
        if (hasError) {
            e.preventDefault();
            alert(errorMessages.join('\n'));
        }
    });
});
</script>

@endsection