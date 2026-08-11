{{-- resources/views/admin/attendance/partials/table.blade.php --}}

<div class="table-responsive">
    <table class="table table-hover" id="attendanceTable">
        <thead class="thead-light">
            <tr>
                <th>#</th>
                <th>Staff</th>
                <th>Designation</th>
                <th>Date</th>
                <th>In Time</th>
                <th>Out Time</th>
                <th>Status</th>
                <th>Special</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $key => $attendance)
                <tr>
                    <td>{{ $attendances->firstItem() + $key }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle bg-info text-white mr-2" 
                                 style="width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                {{ strtoupper(substr($attendance->staff->name ?? 'N/A', 0, 2)) }}
                            </div>
                            <div>
                                <div class="font-weight-bold">{{ $attendance->staff->name ?? 'N/A' }}</div>
                                @if($attendance->staff->email ?? false)
                                    <small class="text-muted">{{ $attendance->staff->email }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-info">{{ $attendance->staff->designation ?? 'N/A' }}</span>
                    </td>
                    <td>
                        {{ $attendance->attendance_date->format('d M, Y') }}
                    </td>
                    <td>
                        @if($attendance->in_time)
                            <span class="badge badge-success">{{ $attendance->formatted_in_time }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($attendance->out_time)
                            <span class="badge badge-danger">{{ $attendance->formatted_out_time }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        {!! $attendance->status_badge !!}
                    </td>
                    <td>
                        @if($attendance->is_friday)
                            <span class="badge badge-secondary" title="Friday">📅 Fri</span>
                        @endif
                        @if($attendance->is_govt_holiday)
                            <span class="badge badge-primary" title="Government Holiday">🏛️ Holiday</span>
                        @endif
                        @if($attendance->on_leave)
                            <span class="badge badge-dark" title="On Leave">🏖️ Leave</span>
                        @endif
                        @if(!$attendance->is_friday && !$attendance->is_govt_holiday && !$attendance->on_leave)
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="{{ route('admin.attendance.edit', $attendance->id) }}" 
                               class="btn btn-info btn-sm" 
                               title="Edit Attendance">
                                <i class="fa fa-edit"></i>
                            </a>
                            
                            <button type="button" 
                                    class="btn btn-danger btn-sm" 
                                    onclick="confirmDelete({{ $attendance->id }}, '{{ $attendance->staff->name ?? 'Unknown' }}', '{{ $attendance->attendance_date->format('Y-m-d') }}')"
                                    title="Delete Attendance">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">
                        <div class="py-4">
                            <i class="fa fa-calendar fa-3x text-muted mb-3"></i>
                            <h5>No Attendance Records Found</h5>
                            <p class="text-muted">No attendance records for the selected date.</p>
                            <a href="{{ route('admin.attendance.create') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Mark Attendance
                            </a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($attendances->hasPages())
<div class="mt-3 d-flex justify-content-between align-items-center">
    <div>
        Showing {{ $attendances->firstItem() ?? 0 }} to {{ $attendances->lastItem() ?? 0 }} of {{ $attendances->total() }} records
    </div>
    <div>
        {{ $attendances->appends(['search' => request('search'), 'date' => request('date')])->links() }}
    </div>
</div>
@endif

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete attendance record for <strong id="deleteStaffName"></strong> on <strong id="deleteDate"></strong>?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Record</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name, date) {
    $('#deleteStaffName').text(name);
    $('#deleteDate').text(date);
    $('#deleteForm').attr('action', "{{ route('admin.attendance.destroy', '') }}/" + id);
    $('#deleteModal').modal('show');
}
</script>