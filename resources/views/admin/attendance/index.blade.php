@extends('admin.layouts.master')

@section('main_content')
<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                <h5 class="card-title mb-0">
                    <i class="fa fa-calendar mr-2"></i>
                    Attendance - {{ $monthName }}
                </h5>
                <div class="d-flex align-items-center flex-wrap">
                    <!-- Month Navigation -->
                    <div class="btn-group mr-2">
                        <a href="{{ route('admin.attendance.index', ['year' => $year, 'month' => $month - 1]) }}" 
                           class="btn btn-sm btn-outline-secondary">
                            <i class="fa fa-chevron-left"></i>
                        </a>
                        <a href="{{ route('admin.attendance.index', ['year' => Carbon\Carbon::now()->year, 'month' => Carbon\Carbon::now()->month]) }}" 
                           class="btn btn-sm btn-outline-primary">
                            Month
                        </a>
                        <a href="{{ route('admin.attendance.index', ['year' => $year, 'month' => $month + 1]) }}" 
                           class="btn btn-sm btn-outline-secondary">
                            <i class="fa fa-chevron-right"></i>
                        </a>
                    </div>
                    
                    <button class="btn btn-primary btn-sm" onclick="openAttendanceModal('{{ Carbon\Carbon::now()->format('Y-m-d') }}')">
                        <i class="fa fa-plus"></i> Mark Today
                    </button>
                </div>
            </div>
            
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm" id="attendanceTable">
                        <thead class="thead-dark">
                            <tr>
                                <th style="min-width: 150px; position: sticky; left: 0; background: #343a40; z-index: 10;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>User</span>
                                        <button class="btn btn-sm btn-outline-light" onclick="openAttendanceModal()">
                                            <i class="fa fa-calendar-plus"></i>
                                        </button>
                                    </div>
                                </th>
                                @for($day = 1; $day <= $daysInMonth; $day++)
                                    @php
                                        $date = Carbon\Carbon::create($year, $month, $day);
                                        $isFriday = $date->isFriday();
                                        $isToday = $date->isToday();
                                        $dayOfWeek = $date->format('D');
                                    @endphp
                                    <th class="text-center {{ $isFriday ? 'table-secondary' : '' }} {{ $isToday ? 'table-primary' : '' }}" 
                                        style="min-width: 45px; cursor: pointer;"
                                        onclick="openAttendanceModal('{{ $date->format('Y-m-d') }}')"
                                        title="Click to mark attendance for {{ $date->format('d M Y') }}">
                                        <div>
                                            <div>{{ $day }}</div>
                                            <small class="text-muted">{{ $dayOfWeek }}</small>
                                            @if($isFriday)
                                                <i class="fa fa-moon-o d-block text-secondary" title="Friday"></i>
                                            @endif
                                        </div>
                                    </th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($attendanceMatrix as $userData)
                                <tr>
                                    <td style="position: sticky; left: 0; background: white; z-index: 5;">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-info text-white mr-2" 
                                                 style="width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px;">
                                                {{ strtoupper(substr($userData['name'], 0, 2)) }}
                                            </div>
                                            <div>
                                                <div class="font-weight-bold">{{ $userData['name'] }}</div>
                                                <small class="text-muted">{{ $userData['email'] }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    @for($day = 1; $day <= $daysInMonth; $day++)
                                        @php
                                            $date = Carbon\Carbon::create($year, $month, $day);
                                            $isFriday = $date->isFriday();
                                            $dayData = $userData['days'][$day] ?? null;
                                            $status = $dayData ? $dayData['status'] : null;
                                            $inTime = $dayData ? $dayData['in_time'] : null;
                                            $onLeave = $dayData ? $dayData['on_leave'] : false;
                                            $isHoliday = $dayData ? $dayData['is_govt_holiday'] : false;
                                            $attendanceId = $dayData ? $dayData['id'] : null;
                                        @endphp
                                        <td class="text-center attendance-cell {{ $isFriday ? 'bg-light' : '' }} {{ $isHoliday ? 'bg-warning bg-opacity-25' : '' }}"
                                            data-user-id="{{ $userData['id'] }}"
                                            data-date="{{ $date->format('Y-m-d') }}"
                                            data-attendance-id="{{ $attendanceId }}"
                                            onclick="openAttendanceModal('{{ $date->format('Y-m-d') }}')"
                                            style="cursor: pointer;">
                                            @if($status)
                                                @if($status == 'leave')
                                                    <span class="badge badge-dark" title="On Leave">L</span>
                                                @elseif($status == 'holiday')
                                                    <span class="badge badge-primary" title="Holiday">H</span>
                                                @elseif($status == 'friday')
                                                    <span class="badge badge-secondary" title="Friday">F</span>
                                                @elseif($status == 'present')
                                                    <span class="badge badge-success" title="Present: {{ $inTime }}">
                                                        <i class="fa fa-check"></i> {{ $inTime ? \Carbon\Carbon::parse($inTime)->format('h:i A') : '' }}
                                                    </span>
                                                @elseif($status == 'late')
                                                    <span class="badge badge-warning" title="Late: {{ $inTime }}">
                                                        <i class="fa fa-clock-o"></i> {{ $inTime ? \Carbon\Carbon::parse($inTime)->format('h:i A') : '' }}
                                                    </span>
                                        
                                                @elseif($status == 'absent')
                                                    <span class="badge badge-danger" title="Absent">A</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <span class="badge badge-success">P = Present</span>
                        <span class="badge badge-warning">L = Late</span>
                        <span class="badge badge-danger">A = Absent</span>
                        <span class="badge badge-dark">Lv = Leave</span>
                        <span class="badge badge-primary">H = Holiday</span>
                        <span class="badge badge-secondary">F = Friday</span>
                    </div>
                   
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Attendance Modal - Bootstrap 5 -->
<div class="modal fade" id="attendanceModal" tabindex="-1" aria-labelledby="attendanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceModalLabel">
                    <i class="fa fa-calendar-check mr-2"></i>
                    Mark Attendance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalBody">
                <div class="text-center py-4">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Loading...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="deleteDateAttendance()">
                    <i class="fa fa-trash"></i> Delete All
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveBtn" onclick="saveAttendance()">
                    <i class="fa fa-save"></i> Save All
                </button>
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

.attendance-cell {
    transition: background-color 0.2s ease;
    min-height: 50px;
}

.attendance-cell:hover {
    background-color: #f0f0f0 !important;
}

#attendanceTable thead th {
    position: sticky;
    top: 0;
    z-index: 8;
    background: #343a40;
    color: white;
}

.bg-opacity-25 {
    opacity: 0.25;
}

.table-responsive {
    max-height: 600px;
    overflow: auto;
}

.attendance-cell .badge {
    font-size: 11px;
    padding: 4px 8px;
    min-width: 30px;
}

/* Modal styles */
.modal-lg {
    max-width: 90%;
}

.attendance-modal-table td, 
.attendance-modal-table th {
    vertical-align: middle;
    padding: 8px 6px;
}

.attendance-modal-table .form-control-sm {
    padding: 2px 8px;
    font-size: 13px;
}

.custom-checkbox .custom-control-label {
    padding-top: 2px;
    font-size: 13px;
}
</style>

<script>
// Make sure jQuery is loaded
if (typeof jQuery === 'undefined') {
    console.error('jQuery is not loaded!');
}

let currentDate = null;

function openAttendanceModal(date) {
    if (!date) {
        date = '{{ Carbon\Carbon::now()->format('Y-m-d') }}';
    }
    currentDate = date;
    
    // Get the modal element
    const modalElement = document.getElementById('attendanceModal');
    if (!modalElement) {
        console.error('Modal element not found!');
        return;
    }
    
    // Show modal using Bootstrap 5
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Set loading state
    document.getElementById('modalBody').innerHTML = `
        <div class="text-center py-4">
            <i class="fa fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Loading attendance data...</p>
        </div>
    `;
    
    // Load data
    loadAttendanceData(date);
}
function loadAttendanceData(date) {
    currentDate = date;
    
    $.ajax({
        url: "{{ route('admin.attendance.get-date') }}",
        method: 'GET',
        data: { date: date },
        success: function(response) {
            if (response.success) {
                renderAttendanceModal(response, date);
            } else {
                document.getElementById('modalBody').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-triangle"></i> 
                        ${response.message || 'Failed to load attendance data'}
                    </div>
                `;
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            document.getElementById('modalBody').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-triangle"></i> 
                    Failed to load attendance data. Please try again.
                    <br><small>Error: ${error}</small>
                </div>
            `;
        }
    });
}
function renderAttendanceModal(data, date) {
    const isFriday = data.is_friday || false;
    const isHoliday = data.is_govt_holiday || false;
    const users = data.users || [];
    
    // Format date for display
    const displayDate = date ? new Date(date + 'T00:00:00') : new Date();
    const formattedDate = displayDate.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    let html = `
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="attendanceDateInput"><strong>Date:</strong></label>
                    <div class="input-group">
                        <input type="date" 
                               class="form-control" 
                               id="attendanceDateInput" 
                               value="${date || '{{ Carbon\Carbon::now()->format('Y-m-d') }}'}"
                               max="{{ Carbon\Carbon::now()->format('Y-m-d') }}">
                        <button class="btn btn-outline-primary" type="button" onclick="changeAttendanceDate()">
                            <i class="fa fa-refresh"></i> Change
                        </button>
                    </div>
                    <small class="text-muted">Select a date and click "Change" to load attendance for that day</small>
                </div>
            </div>
            <div class="col-md-8">
                <div class="d-flex flex-wrap align-items-center">
                    <div class="me-3 mb-2">
                        <strong>Today is: </strong>
                        <span class="badge bg-info">${formattedDate}</span>
                    </div>
                    <div class="form-check form-check-inline mb-2">
                        <input class="form-check-input" type="checkbox" id="isFriday" 
                               ${isFriday ? 'checked' : ''} ${isFriday ? 'disabled' : ''}>
                        <label class="form-check-label" for="isFriday">
                            <span class="badge badge-secondary">Friday</span>
                            ${isFriday ? '<span class="text-muted">(Auto-detected)</span>' : ''}
                        </label>
                    </div>
                    <div class="form-check form-check-inline mb-2">
                        <input class="form-check-input" type="checkbox" id="isHoliday" 
                               ${isHoliday ? 'checked' : ''}>
                        <label class="form-check-label" for="isHoliday">
                            <span class="badge badge-primary">Govt. Holiday</span>
                        </label>
                    </div>
                    ${isFriday ? '<span class="text-muted ms-2"><small>Friday is auto-detected and cannot be changed</small></span>' : ''}
                </div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-12">
                <button class="btn btn-sm btn-outline-secondary me-1" onclick="applyToAll()">
                    <i class="fa fa-copy"></i> Apply Leave to All
                </button>
                <button class="btn btn-sm btn-outline-success me-1" onclick="clearAllAttendance()">
                    <i class="fa fa-eraser"></i> Clear All
                </button>
                <button class="btn btn-sm btn-outline-info" onclick="loadTodayAttendance()">
                    <i class="fa fa-calendar"></i> Load Today
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered attendance-modal-table">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th style="min-width: 120px;">In Time</th>
                        <th style="min-width: 80px;">Leave</th>
                        <th>Note</th>
                        <th style="min-width: 100px;">Status</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    users.forEach((user, index) => {
        const inTime = user.in_time || '';
        const onLeave = user.on_leave || false;
        const note = user.note || '';
        const attendanceId = user.attendance_id || '';
        
        // Determine status display
        let statusText = 'Not marked';
        let statusClass = 'text-muted';
        if (isHoliday) {
            statusText = 'Holiday';
            statusClass = 'text-primary';
        } else if (isFriday) {
            statusText = 'Friday';
            statusClass = 'text-secondary';
        } else if (onLeave) {
            statusText = 'On Leave';
            statusClass = 'text-dark';
        } else if (inTime) {
            const time = new Date('1970-01-01T' + inTime + ':00');
            const cutoff = new Date('1970-01-01T09:30:00');
            if (time > cutoff) {
                statusText = 'Late';
                statusClass = 'text-warning';
            } else {
                statusText = 'Present';
                statusClass = 'text-success';
            }
        } else {
            statusText = 'Absent';
            statusClass = 'text-danger';
        }
        
        html += `
            <tr data-user-id="${user.id}">
                <td>${index + 1}</td>
                <td>
                    <input type="hidden" class="user-id-input" name="attendance_data[${index}][user_id]" value="${user.id}">
                    <input type="hidden" class="attendance-id-input" name="attendance_data[${index}][attendance_id]" value="${attendanceId}">
                    <div class="fw-bold">${user.name}</div>
                    <small class="text-muted">${user.email}</small>
                </td>
                <td>
                    <input type="time" 
                           class="form-control form-control-sm time-input" 
                           name="attendance_data[${index}][in_time]" 
                           value="${inTime}"
                           ${onLeave || isHoliday || isFriday ? 'disabled' : ''}>
                </td>
                <td class="text-center">
                    <div class="form-check">
                        <input class="form-check-input leave-checkbox" 
                               type="checkbox" 
                               id="leave_${index}" 
                               name="attendance_data[${index}][on_leave]" 
                               value="1"
                               ${onLeave ? 'checked' : ''}
                               ${isHoliday || isFriday ? 'disabled' : ''}>
                        <label class="form-check-label" for="leave_${index}">Leave</label>
                    </div>
                </td>
                <td>
                    <input type="text" 
                           class="form-control form-control-sm note-input" 
                           name="attendance_data[${index}][note]" 
                           value="${escapeHtml(note)}"
                           placeholder="Note">
                </td>
                <td>
                    <span class="badge bg-${statusClass.includes('success') ? 'success' : 
                                             statusClass.includes('warning') ? 'warning' : 
                                             statusClass.includes('danger') ? 'danger' : 
                                             statusClass.includes('primary') ? 'primary' : 
                                             statusClass.includes('dark') ? 'dark' : 'secondary'}">
                        ${statusText}
                    </span>
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    document.getElementById('modalBody').innerHTML = html;
    
    // Store current date in a data attribute
    document.getElementById('modalBody').dataset.currentDate = date || '{{ Carbon\Carbon::now()->format('Y-m-d') }}';
    
    // Bind events
    document.querySelectorAll('.leave-checkbox').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const row = this.closest('tr');
            const timeInput = row.querySelector('.time-input');
            if (this.checked) {
                timeInput.value = '';
                timeInput.disabled = true;
            } else {
                timeInput.disabled = false;
            }
            updateStatus(row);
        });
    });
    
    document.querySelectorAll('.time-input').forEach(function(input) {
        input.addEventListener('change', function() {
            const row = this.closest('tr');
            updateStatus(row);
        });
    });
    
    const holidayCheckbox = document.getElementById('isHoliday');
    if (holidayCheckbox) {
        holidayCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            document.querySelectorAll('.time-input').forEach(function(input) {
                input.disabled = isChecked;
            });
            document.querySelectorAll('.leave-checkbox').forEach(function(checkbox) {
                checkbox.disabled = isChecked;
            });
            if (isChecked) {
                document.querySelectorAll('.time-input').forEach(function(input) {
                    input.value = '';
                });
                document.querySelectorAll('.leave-checkbox').forEach(function(checkbox) {
                    checkbox.checked = false;
                });
            }
            document.querySelectorAll('tbody tr').forEach(function(row) {
                updateStatus(row);
            });
        });
    }
}
function loadTodayAttendance() {
    const today = '{{ Carbon\Carbon::now()->format('Y-m-d') }}';
    
    // Update date input if exists
    const dateInput = document.getElementById('attendanceDateInput');
    if (dateInput) {
        dateInput.value = today;
    }
    
    // Show loading
    document.getElementById('modalBody').innerHTML = `
        <div class="text-center py-4">
            <i class="fa fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Loading today's attendance data...</p>
        </div>
    `;
    
    // Load data for today
    loadAttendanceData(today);
}
function changeAttendanceDate() {
    const dateInput = document.getElementById('attendanceDateInput');
    if (!dateInput) return;
    
    const newDate = dateInput.value;
    if (!newDate) {
        alert('Please select a valid date.');
        return;
    }
    
    // Check if date is in the future
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const selectedDate = new Date(newDate + 'T00:00:00');
    
    if (selectedDate > today) {
        alert('You cannot mark attendance for future dates.');
        return;
    }
    
    // Show loading
    document.getElementById('modalBody').innerHTML = `
        <div class="text-center py-4">
            <i class="fa fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Loading attendance data for ${newDate}...</p>
        </div>
    `;
    
    // Load data for new date
    loadAttendanceData(newDate);
}
// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function updateStatus(row) {
    const inTime = row.querySelector('.time-input')?.value || '';
    const onLeave = row.querySelector('.leave-checkbox')?.checked || false;
    const isHoliday = document.getElementById('isHoliday')?.checked || false;
    const isFriday = document.getElementById('isFriday')?.checked || false;
    
    let statusText = 'Not marked';
    let statusClass = 'text-muted';
    
    if (isHoliday) {
        statusText = 'Holiday';
        statusClass = 'text-primary';
    } else if (isFriday) {
        statusText = 'Friday';
        statusClass = 'text-secondary';
    } else if (onLeave) {
        statusText = 'On Leave';
        statusClass = 'text-dark';
    } else if (inTime) {
        const time = new Date('1970-01-01T' + inTime + ':00');
        const cutoff = new Date('1970-01-01T09:30:00');
        if (time > cutoff) {
            statusText = 'Late';
            statusClass = 'text-warning';
        } else {
            statusText = 'Present';
            statusClass = 'text-success';
        }
    } else {
        statusText = 'Absent';
        statusClass = 'text-danger';
    }
    
    const badge = row.querySelector('td:last-child .badge');
    if (badge) {
        const badgeClass = statusClass.includes('success') ? 'success' : 
                           statusClass.includes('warning') ? 'warning' : 
                           statusClass.includes('danger') ? 'danger' : 
                           statusClass.includes('primary') ? 'primary' : 
                           statusClass.includes('dark') ? 'dark' : 'secondary';
        badge.className = 'badge bg-' + badgeClass;
        badge.textContent = statusText;
    }
}

function applyToAll() {
    if (confirm('Apply leave to all users?')) {
        document.querySelectorAll('.leave-checkbox').forEach(function(checkbox) {
            checkbox.checked = true;
            checkbox.dispatchEvent(new Event('change'));
        });
    }
}

function clearAllAttendance() {
    if (confirm('Clear all attendance data for this date?')) {
        document.querySelectorAll('.time-input').forEach(function(input) {
            input.value = '';
        });
        document.querySelectorAll('.leave-checkbox').forEach(function(checkbox) {
            checkbox.checked = false;
            checkbox.dispatchEvent(new Event('change'));
        });
    }
}
function saveAttendance() {
    // Get the current date from the modal body data attribute
    const modalBody = document.getElementById('modalBody');
    const date = modalBody?.dataset?.currentDate || currentDate;
    
    // Get all form data
    const attendanceData = [];
    
    // Get all rows from the table
    document.querySelectorAll('tbody tr').forEach(function(row) {
        const userId = row.querySelector('input[name$="[user_id]"]')?.value;
        const attendanceId = row.querySelector('input[name$="[attendance_id]"]')?.value || '';
        const inTime = row.querySelector('.time-input')?.value || '';
        const onLeave = row.querySelector('.leave-checkbox')?.checked ? 1 : 0;
        const note = row.querySelector('.note-input')?.value || '';
        
        // Only add if user_id exists
        if (userId) {
            attendanceData.push({
                user_id: userId,
                attendance_id: attendanceId,
                in_time: inTime,
                on_leave: onLeave,
                note: note
            });
        }
    });
    
    // Check if we have any data
    if (attendanceData.length === 0) {
        alert('No attendance data to save!');
        return;
    }
    
    const formData = {
        attendance_date: date,
        is_friday: document.getElementById('isFriday')?.checked ? 1 : 0,
        is_govt_holiday: document.getElementById('isHoliday')?.checked ? 1 : 0,
        attendance_data: attendanceData
    };
    
    // Log data for debugging
    console.log('Saving data:', formData);
    
    // Show loading
    const saveBtn = document.getElementById('saveBtn');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving...';
    }
    
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    
    $.ajax({
        url: "{{ route('admin.attendance.store-or-update') }}",
        method: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        success: function(response) {
            console.log('Success:', response);
            if (response.success) {
                alert(response.message || 'Attendance saved successfully!');
                const modal = bootstrap.Modal.getInstance(document.getElementById('attendanceModal'));
                if (modal) {
                    modal.hide();
                }
                location.reload();
            } else {
                alert('Error: ' + (response.message || 'Failed to save attendance'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error details:', {
                status: status,
                error: error,
                response: xhr.responseText
            });
            
            if (xhr.status === 422) {
                let errors = xhr.responseJSON?.errors || {};
                let errorMsg = 'Validation errors:\n';
                Object.keys(errors).forEach(key => {
                    errorMsg += '- ' + key + ': ' + (Array.isArray(errors[key]) ? errors[key].join(', ') : errors[key]) + '\n';
                });
                alert(errorMsg);
            } else {
                alert('Failed to save attendance. Please try again.\nError: ' + (xhr.responseJSON?.message || error));
            }
        },
        complete: function() {
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fa fa-save"></i> Save All';
            }
        }
    });
}

function deleteDateAttendance() {
    if (!currentDate) return;
    
    if (!confirm(`Delete ALL attendance records for ${currentDate}? This action cannot be undone!`)) {
        return;
    }
    
    $.ajax({
        url: "{{ route('admin.attendance.delete-date') }}",
        method: 'DELETE',
        data: { date: currentDate },
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        success: function(response) {
            if (response.success) {
                alert(response.message);
                const modal = bootstrap.Modal.getInstance(document.getElementById('attendanceModal'));
                modal.hide();
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('Failed to delete attendance records.');
        }
    });
}

// Make functions globally available
window.openAttendanceModal = openAttendanceModal;
window.saveAttendance = saveAttendance;
window.deleteDateAttendance = deleteDateAttendance;
window.applyToAll = applyToAll;
window.clearAllAttendance = clearAllAttendance;
window.renderAttendanceModal = renderAttendanceModal;
window.updateStatus = updateStatus;
window.changeAttendanceDate = changeAttendanceDate;
window.loadTodayAttendance = loadTodayAttendance;
window.loadAttendanceData = loadAttendanceData;
// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Attendance page loaded successfully');
});
</script>
@endsection