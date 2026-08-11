<?php
// app/Http/Controllers/Admin/AttendanceController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendance.
     */
    public function index(Request $request)
    {
        try {
            $date = $request->get('date', Carbon::today()->toDateString());
            $search = $request->get('search');
            
            $attendances = Attendance::with('staff')
                ->withFilters([
                    'date' => $date,
                    'search' => $search
                ])
                ->orderBy('attendance_date', 'desc')
                ->paginate(15);
            
            if ($request->ajax()) {
                return view('admin.attendance.partials.table', compact('attendances', 'date'))->render();
            }
            
            return view('admin.attendance.index', compact('attendances', 'date'));
            
        } catch (\Exception $e) {
            Log::error('Attendance index error: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['error' => 'Failed to load attendance data'], 500);
            }
            return back()->with('error', 'Failed to load attendance data');
        }
    }

    /**
     * Show the form for creating attendance.
     */
    public function create()
    {
        try {
            // Get all active staff members
            $staffMembers = Staff::active()->orderBy('name')->get();
            
            // Get today's date
            $today = Carbon::today();
            $isFriday = $today->isFriday();
            
            return view('admin.attendance.create', compact('staffMembers', 'today', 'isFriday'));
        } catch (\Exception $e) {
            Log::error('Attendance create error: ' . $e->getMessage());
            return redirect()->route('admin.attendance.index')
                           ->with('error', 'Failed to load attendance creation form');
        }
    }

    /**
     * Store attendance records.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'attendance_date' => 'required|date',
                'staff_data' => 'required|array',
                'staff_data.*.staff_id' => 'required|exists:staff,id',
                'staff_data.*.in_time' => 'nullable|date_format:H:i',
                'staff_data.*.out_time' => 'nullable|date_format:H:i',
                'staff_data.*.note' => 'nullable|string|max:500',
                'is_friday' => 'nullable|in:0,1',
                'is_govt_holiday' => 'nullable|in:0,1',
                'staff_on_leave' => 'nullable|array',
                'staff_on_leave.*' => 'exists:staff,id'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                                ->withErrors($validator)
                                ->withInput();
            }

            $attendanceDate = $request->attendance_date;
            
            // Get checkbox values - properly check if they exist and are checked
            $isFriday = $request->has('is_friday') && $request->input('is_friday') == '1';
            $isHoliday = $request->has('is_govt_holiday') && $request->input('is_govt_holiday') == '1';
            $staffOnLeave = $request->staff_on_leave ?? [];

            // Check if attendance already exists for this date
            $existingAttendances = Attendance::whereDate('attendance_date', $attendanceDate)->count();
            if ($existingAttendances > 0) {
                return redirect()->back()
                               ->with('error', 'Attendance for this date has already been marked.')
                               ->withInput();
            }

            $createdCount = 0;
            $errors = [];

            foreach ($request->staff_data as $staffData) {
                try {
                    $staffId = $staffData['staff_id'];
                    $inTime = $staffData['in_time'] ?? null;
                    $outTime = $staffData['out_time'] ?? null;
                    $note = $staffData['note'] ?? null;
                    
                    // Check if this staff is on leave
                    $onLeave = in_array($staffId, $staffOnLeave);
                    
                    // Determine status
                    $status = Attendance::getStatusFromTimes(
                        $inTime, 
                        $outTime, 
                        $isFriday, 
                        $isHoliday, 
                        $onLeave
                    );

                    // Create attendance record
                    Attendance::create([
                        'staff_id' => $staffId,
                        'attendance_date' => $attendanceDate,
                        'in_time' => $inTime,
                        'out_time' => $outTime,
                        'is_friday' => $isFriday,
                        'is_govt_holiday' => $isHoliday,
                        'on_leave' => $onLeave,
                        'note' => $note,
                        'status' => $status
                    ]);
                    
                    $createdCount++;
                    
                } catch (\Exception $e) {
                    $staff = Staff::find($staffId);
                    $errors[] = "Failed to save attendance for " . ($staff ? $staff->name : "Staff #{$staffId}");
                    Log::error('Attendance creation error for staff ' . $staffId . ': ' . $e->getMessage());
                }
            }

            if ($createdCount > 0) {
                $message = "Attendance marked successfully for {$createdCount} staff members.";
                if (!empty($errors)) {
                    $message .= " However, some entries failed: " . implode(', ', $errors);
                }
                return redirect()->route('admin.attendance.index')
                               ->with('success', $message);
            } else {
                return redirect()->back()
                               ->with('error', 'Failed to mark attendance. Please try again.')
                               ->withInput();
            }

        } catch (\Exception $e) {
            Log::error('Attendance store error: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'Failed to mark attendance. Please try again.')
                           ->withInput();
        }
    }

    /**
     * Show the form for editing attendance.
     */
    public function edit($id)
    {
        try {
            // Get the specific attendance record
            $attendance = Attendance::with('staff')->findOrFail($id);
            
            // Get all active staff members
            $staffMembers = Staff::active()->orderBy('name')->get();
            
            // Get all attendance records for this date
            $attendanceDate = $attendance->attendance_date;
            $allAttendances = Attendance::with('staff')
                ->whereDate('attendance_date', $attendanceDate)
                ->get()
                ->keyBy('staff_id');
            
            $today = Carbon::parse($attendance->attendance_date);
            $isFriday = $today->isFriday();
            
            return view('admin.attendance.edit', compact('attendance', 'staffMembers', 'allAttendances', 'today', 'isFriday'));
            
        } catch (\Exception $e) {
            Log::error('Attendance edit error: ' . $e->getMessage());
            return redirect()->route('admin.attendance.index')
                           ->with('error', 'Attendance record not found.');
        }
    }

    /**
     * Update ALL attendance records for a specific date.
     */
    public function update(Request $request, $id)
    {
        try {
            // Get the current attendance record to know the date
            $currentAttendance = Attendance::findOrFail($id);
            $attendanceDate = $currentAttendance->attendance_date;
            
            $validator = Validator::make($request->all(), [
                'staff_data' => 'required|array',
                'staff_data.*.staff_id' => 'required|exists:staff,id',
                'staff_data.*.in_time' => 'nullable|date_format:H:i',
                'staff_data.*.out_time' => 'nullable|date_format:H:i',
                'staff_data.*.note' => 'nullable|string|max:500',
                'is_friday' => 'nullable|in:0,1',
                'is_govt_holiday' => 'nullable|in:0,1',
                'staff_on_leave' => 'nullable|array',
                'staff_on_leave.*' => 'exists:staff,id'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                                ->withErrors($validator)
                                ->withInput();
            }

            // Get checkbox values
            $isFriday = $request->has('is_friday') && $request->input('is_friday') == '1';
            $isHoliday = $request->has('is_govt_holiday') && $request->input('is_govt_holiday') == '1';
            $staffOnLeave = $request->staff_on_leave ?? [];

            $updatedCount = 0;
            $errors = [];

            // Get all existing attendance records for this date
            $existingAttendances = Attendance::whereDate('attendance_date', $attendanceDate)
                ->get()
                ->keyBy('staff_id');

            foreach ($request->staff_data as $staffData) {
                try {
                    $staffId = $staffData['staff_id'];
                    $inTime = $staffData['in_time'] ?? null;
                    $outTime = $staffData['out_time'] ?? null;
                    $note = $staffData['note'] ?? null;
                    
                    // Check if this staff is on leave
                    $onLeave = in_array($staffId, $staffOnLeave);
                    
                    // Determine status
                    $status = Attendance::getStatusFromTimes(
                        $inTime, 
                        $outTime, 
                        $isFriday, 
                        $isHoliday, 
                        $onLeave
                    );

                    // Check if attendance exists for this staff
                    if ($existingAttendances->has($staffId)) {
                        // Update existing record
                        $existingAttendances[$staffId]->update([
                            'in_time' => $inTime,
                            'out_time' => $outTime,
                            'is_friday' => $isFriday,
                            'is_govt_holiday' => $isHoliday,
                            'on_leave' => $onLeave,
                            'note' => $note,
                            'status' => $status
                        ]);
                    } else {
                        // Create new record if it doesn't exist
                        Attendance::create([
                            'staff_id' => $staffId,
                            'attendance_date' => $attendanceDate,
                            'in_time' => $inTime,
                            'out_time' => $outTime,
                            'is_friday' => $isFriday,
                            'is_govt_holiday' => $isHoliday,
                            'on_leave' => $onLeave,
                            'note' => $note,
                            'status' => $status
                        ]);
                    }
                    
                    $updatedCount++;
                    
                } catch (\Exception $e) {
                    $staff = Staff::find($staffId);
                    $errors[] = "Failed to update attendance for " . ($staff ? $staff->name : "Staff #{$staffId}");
                    Log::error('Attendance update error for staff ' . $staffId . ': ' . $e->getMessage());
                }
            }

            // Delete attendance records for staff that are no longer active or removed
            $submittedStaffIds = collect($request->staff_data)->pluck('staff_id')->toArray();
            $existingAttendances->each(function($attendance) use ($submittedStaffIds, &$errors) {
                if (!in_array($attendance->staff_id, $submittedStaffIds)) {
                    try {
                        $attendance->delete();
                    } catch (\Exception $e) {
                        $errors[] = "Failed to delete old attendance record for staff #{$attendance->staff_id}";
                        Log::error('Attendance deletion error for staff ' . $attendance->staff_id . ': ' . $e->getMessage());
                    }
                }
            });

            if ($updatedCount > 0) {
                $message = "Attendance updated successfully for {$updatedCount} staff members.";
                if (!empty($errors)) {
                    $message .= " However, some entries failed: " . implode(', ', $errors);
                }
                return redirect()->route('admin.attendance.index')
                               ->with('success', $message);
            } else {
                return redirect()->back()
                               ->with('error', 'Failed to update attendance. Please try again.')
                               ->withInput();
            }

        } catch (\Exception $e) {
            Log::error('Attendance update error: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'Failed to update attendance. Please try again.')
                           ->withInput();
        }
    }

    /**
     * Remove attendance record.
     */
    public function destroy($id)
    {
        try {
            $attendance = Attendance::findOrFail($id);
            $staffName = $attendance->staff->name ?? 'Unknown';
            $date = $attendance->attendance_date->format('Y-m-d');
            
            $attendance->delete();
            
            return redirect()->route('admin.attendance.index')
                           ->with('success', "Attendance record for {$staffName} on {$date} deleted successfully!");
                           
        } catch (\Exception $e) {
            Log::error('Attendance deletion error: ' . $e->getMessage());
            return redirect()->route('admin.attendance.index')
                           ->with('error', 'Failed to delete attendance record.');
        }
    }

    /**
     * Show attendance report.
     */
    public function report(Request $request)
    {
        try {
            $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
            $endDate = $request->get('end_date', Carbon::now()->toDateString());
            $staffId = $request->get('staff_id');
            
            $query = Attendance::with('staff')
                ->whereBetween('attendance_date', [$startDate, $endDate]);
            
            if ($staffId) {
                $query->where('staff_id', $staffId);
            }
            
            $attendances = $query->orderBy('attendance_date', 'desc')->get();
            
            // Group by staff
            $groupedData = $attendances->groupBy('staff_id');
            
            $staffList = Staff::active()->orderBy('name')->get();
            
            // Calculate summary statistics
            $summary = [];
            foreach ($groupedData as $staffId => $records) {
                $staff = Staff::find($staffId);
                if ($staff) {
                    $summary[$staffId] = [
                        'name' => $staff->name,
                        'designation' => $staff->designation,
                        'total_days' => $records->count(),
                        'present' => $records->where('status', 'present')->count(),
                        'absent' => $records->where('status', 'absent')->count(),
                        'late' => $records->where('status', 'late')->count(),
                        'half_day' => $records->where('status', 'half_day')->count(),
                        'leave' => $records->where('status', 'leave')->count(),
                        'holiday' => $records->where('status', 'holiday')->count(),
                        'friday' => $records->where('status', 'friday')->count(),
                    ];
                }
            }
            
            return view('admin.attendance.report', compact('attendances', 'groupedData', 'staffList', 'startDate', 'endDate', 'summary'));
            
        } catch (\Exception $e) {
            Log::error('Attendance report error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load attendance report.');
        }
    }
}