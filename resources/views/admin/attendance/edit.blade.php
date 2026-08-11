{{-- resources/views/admin/attendance/edit.blade.php --}}

@extends('admin.layouts.master')

@section('main_content')

<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fa fa-edit mr-2"></i>
                    Edit Attendance Records - {{ $today->format('d M, Y') }}
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

                <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="POST" id="attendanceForm">
                    @csrf
                    @method('PUT')
                    
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="attendance_date">Attendance Date</label>
                                <input type="date" 
                                       class="form-control" 
                                       id="attendance_date" 
                                       value="{{ $attendance->attendance_date->format('Y-m-d') }}" 
                                       disabled>
                                <small class="text-muted">Date cannot be changed</small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Apply to All</label>
                                <div class="d-flex">
                                    <div class="custom-control custom-checkbox mr-3">
                                        <input type="checkbox" class="custom-control-input" id="is_friday" name="is_friday" value="1"
                                               {{ old('is_friday', $attendance->is_friday) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_friday">
                                            <span class="badge badge-secondary">Friday</span>
                                        </label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_govt_holiday" name="is_govt_holiday" value="1"
                                               {{ old('is_govt_holiday', $attendance->is_govt_holiday) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_govt_holiday">
                                            <span class="badge badge-primary">Govt. Holiday</span>
                                        </label>
                                    </div>
                                </div>
                                <small class="text-muted">These will apply to all staff members for this date</small>
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
                                    @php
                                        // Get existing attendance for this staff on this date
                                        $existingAttendance = $allAttendances->get($staff->id);
                                        $inTime = $existingAttendance ? $existingAttendance->in_time : null;
                                        $outTime = $existingAttendance ? $existingAttendance->out_time : null;
                                        $onLeave = $existingAttendance ? $existingAttendance->on_leave : false;
                                        $note = $existingAttendance ? $existingAttendance->note : null;
                                        $isEditingCurrent = $existingAttendance && $existingAttendance->id == $attendance->id;
                                    @endphp
                                    <tr class="{{ $isEditingCurrent ? 'table-primary' : '' }}">
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
                                                    @if($isEditingCurrent)
                                                        <br><small class="text-primary"><i class="fa fa-edit"></i> Editing</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="badge badge-info">{{ $staff->designation }}</span></td>
                                        <td>
                                            <input type="time" 
                                                   class="form-control form-control-sm time-input" 
                                                   name="staff_data[{{ $index }}][in_time]" 
                                                   value="{{ old('staff_data.' . $index . '.in_time', $inTime ? Carbon\Carbon::parse($inTime)->format('H:i') : '') }}"
                                                   {{ $onLeave ? 'disabled' : '' }}>
                                        </td>
                                        <td>
                                            <input type="time" 
                                                   class="form-control form-control-sm time-input" 
                                                   name="staff_data[{{ $index }}][out_time]" 
                                                   value="{{ old('staff_data.' . $index . '.out_time', $outTime ? Carbon\Carbon::parse($outTime)->format('H:i') : '') }}"
                                                   {{ $onLeave ? 'disabled' : '' }}>
                                        </td>
                                        <td class="text-center">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" 
                                                       class="custom-control-input leave-checkbox" 
                                                       id="leave_{{ $staff->id }}" 
                                                       name="staff_on_leave[]" 
                                                       value="{{ $staff->id }}"
                                                       {{ (old('staff_on_leave') && in_array($staff->id, old('staff_on_leave'))) || (!$errors->any() && $onLeave) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="leave_{{ $staff->id }}">Leave</label>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" 
                                                   class="form-control form-control-sm" 
                                                   name="staff_data[{{ $index }}][note]" 
                                                   placeholder="Note (optional)"
                                                   value="{{ old('staff_data.' . $index . '.note', $note) }}">
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
                            <i class="fa fa-save"></i> Update Attendance
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
    
    .table-primary {
        background-color: #cce5ff !important;
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

    // Form submission
    $('#attendanceForm').on('submit', function(e) {
        let hasError = false;
        let errorMessages = [];
        
        // Validate that at least one staff is selected
        let hasStaffData = false;
        $('input[name^="staff_data"]').each(function() {
            if ($(this).val()) {
                hasStaffData = true;
            }
        });
        
        if (!hasStaffData) {
            hasError = true;
            errorMessages.push('No staff data found.');
        }
        
        if (hasError) {
            e.preventDefault();
            alert(errorMessages.join('\n'));
        }
    });
});
</script>

@endsection