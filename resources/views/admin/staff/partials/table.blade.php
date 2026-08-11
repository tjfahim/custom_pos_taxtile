{{-- resources/views/admin/staff/partials/table.blade.php --}}

<div class="table-responsive">
    <table class="table table-hover" id="staffTable">
        <thead class="thead-light">
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Designation</th>
                <th>Join Date</th>
                <th>Status</th>
                <th>Salary</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($staff as $key => $member)
                <tr>
                    <td>{{ $staff->firstItem() + $key }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle bg-primary text-white mr-2" style="width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                {{ strtoupper(substr($member->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="font-weight-bold">{{ $member->name }}</div>
                                @if($member->email)
                                    <small class="text-muted">{{ $member->email }}</small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="badge badge-info">{{ $member->designation }}</span>
                    </td>
                    <td>
                        @if($member->join_at)
                            {{ $member->join_at->format('d M, Y') }}
                        @else
                            <span class="text-muted">Not set</span>
                        @endif
                    </td>
                    <td>
                        {!! $member->status_badge !!}
                    </td>
                    <td>
                        <strong>{{ $member->formatted_salary }}</strong>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="{{ route('admin.staff.edit', $member->id) }}" 
                               class="btn btn-info btn-sm" 
                               title="Edit Staff">
                                <i class="fa fa-edit"></i>
                            </a>
                            
                            <button type="button" 
                                    class="btn btn-danger btn-sm" 
                                    onclick="confirmDelete({{ $member->id }}, '{{ $member->name }}')"
                                    title="Delete Staff">
                                <i class="fa fa-trash"></i>
                            </button>
                            
                            <button type="button" 
                                    class="btn btn-warning btn-sm" 
                                    onclick="toggleStatus({{ $member->id }})"
                                    title="Change Status">
                                <i class="fa fa-exchange"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">
                        <div class="py-4">
                            <i class="fa fa-users fa-3x text-muted mb-3"></i>
                            <h5>No Staff Members Found</h5>
                            <p class="text-muted">Start by adding your first staff member.</p>
                            <a href="{{ route('admin.staff.create') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add Staff
                            </a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
<div class="mt-3 d-flex justify-content-between align-items-center">
    <div>
        Showing {{ $staff->firstItem() ?? 0 }} to {{ $staff->lastItem() ?? 0 }} of {{ $staff->total() }} staff members
    </div>
    <div>
        {{ $staff->appends(['search' => request('search')])->links() }}
    </div>
</div>

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
                <p>Are you sure you want to delete staff member <strong id="deleteStaffName"></strong>?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Staff</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Status Change Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Status</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="statusForm" action="" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select New Status</label>
                        <select name="status" class="form-control" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="on_leave">On Leave</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    $('#deleteStaffName').text(name);
    $('#deleteForm').attr('action', "{{ route('admin.staff.destroy', '') }}/" + id);
    $('#deleteModal').modal('show');
}

function toggleStatus(id) {
    $('#statusForm').attr('action', "{{ route('admin.staff.update-status', '') }}/" + id);
    $('#statusModal').modal('show');
}

// AJAX status update
$('#statusForm').on('submit', function(e) {
    e.preventDefault();
    const form = $(this);
    const url = form.attr('action');
    const data = form.serialize();
    
    $.ajax({
        url: url,
        method: 'POST',
        data: data,
        success: function(response) {
            if (response.success) {
                $('#statusModal').modal('hide');
                // Reload the table to reflect changes
                performSearch($('#searchInput').val());
                // Show success message
                showAlert('success', response.message);
            }
        },
        error: function(xhr) {
            showAlert('danger', xhr.responseJSON?.message || 'Failed to update status');
        }
    });
});

function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    `;
    $('.content').prepend(alertHtml);
    setTimeout(() => {
        $('.alert').alert('close');
    }, 5000);
}
</script>