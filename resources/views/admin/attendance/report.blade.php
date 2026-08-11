{{-- resources/views/admin/attendance/report.blade.php --}}

@extends('admin.layouts.master')

@section('main_content')

<div class="content mt-3">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fa fa-chart-bar mr-2"></i>
                    Attendance Report
                </h5>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form action="{{ route('admin.attendance.report') }}" method="GET" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Start Date</label>
                                <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>End Date</label>
                                <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Staff Member</label>
                                <select class="form-control" name="staff_id">
                                    <option value="">All Staff</option>
                                    @foreach($staffList as $staff)
                                        <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>
                                            {{ $staff->name }} - {{ $staff->designation }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fa fa-filter"></i> Generate Report
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Summary Cards -->
                @if(!empty($summary))
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h6>Summary Statistics</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Staff</th>
                                        <th>Total Days</th>
                                        <th>Present</th>
                                        <th>Absent</th>
                                        <th>Late</th>
                                        <th>Half Day</th>
                                        <th>Leave</th>
                                        <th>Holiday</th>
                                        <th>Friday</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($summary as $staffId => $data)
                                        <tr>
                                            <td><strong>{{ $data['name'] }}</strong><br>
                                                <small class="text-muted">{{ $data['designation'] }}</small>
                                            </td>
                                            <td>{{ $data['total_days'] }}</td>
                                            <td><span class="badge badge-success">{{ $data['present'] }}</span></td>
                                            <td><span class="badge badge-danger">{{ $data['absent'] }}</span></td>
                                            <td><span class="badge badge-warning">{{ $data['late'] }}</span></td>
                                            <td><span class="badge badge-info">{{ $data['half_day'] }}</span></td>
                                            <td><span class="badge badge-dark">{{ $data['leave'] }}</span></td>
                                            <td><span class="badge badge-primary">{{ $data['holiday'] }}</span></td>
                                            <td><span class="badge badge-secondary">{{ $data['friday'] }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Detailed Records -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Staff</th>
                                <th>Date</th>
                                <th>In Time</th>
                                <th>Out Time</th>
                                <th>Status</th>
                                <th>Special</th>
                                <th>Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $attendance)
                                <tr>
                                    <td>
                                        <strong>{{ $attendance->staff->name ?? 'N/A' }}</strong>
                                    </td>
                                    <td>{{ $attendance->attendance_date->format('d M, Y') }}</td>
                                    <td>{{ $attendance->formatted_in_time }}</td>
                                    <td>{{ $attendance->formatted_out_time }}</td>
                                    <td>{!! $attendance->status_badge !!}</td>
                                    <td>
                                        @if($attendance->is_friday)
                                            <span class="badge badge-secondary">Fri</span>
                                        @endif
                                        @if($attendance->is_govt_holiday)
                                            <span class="badge badge-primary">Holiday</span>
                                        @endif
                                        @if($attendance->on_leave)
                                            <span class="badge badge-dark">Leave</span>
                                        @endif
                                    </td>
                                    <td>{{ $attendance->note ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">
                                        <div class="py-4">
                                            <i class="fa fa-calendar fa-2x text-muted mb-2"></i>
                                            <h6>No attendance records found</h6>
                                            <p class="text-muted">Try adjusting your filter criteria.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection