<?php
// app/Http/Controllers/Admin/AttendanceController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display monthly attendance view
     */
    public function index(Request $request)
    {
        try {
            $year = $request->get('year', Carbon::now()->year);
            $month = $request->get('month', Carbon::now()->month);
            
            // Get all active users
            $users = User::orderBy('name')->get();
            
            // Get attendance for the month
            $attendances = Attendance::with('user')
                ->forMonth($year, $month)
                ->get()
                ->groupBy(function($attendance) {
                    return $attendance->user_id . '_' . $attendance->attendance_date->format('Y-m-d');
                });
            
            // Get days in month
            $daysInMonth = Carbon::create($year, $month)->daysInMonth;
            $firstDayOfMonth = Carbon::create($year, $month, 1);
            $monthName = $firstDayOfMonth->format('F Y');
            
            // Create a matrix of attendance data
            $attendanceMatrix = [];
            foreach ($users as $user) {
                $userData = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'days' => []
                ];
                
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $date = Carbon::create($year, $month, $day)->format('Y-m-d');
                    $key = $user->id . '_' . $date;
                    
                    if (isset($attendances[$key])) {
                        $attendance = $attendances[$key]->first();
                        $userData['days'][$day] = [
                            'id' => $attendance->id,
                            'date' => $date,
                            'in_time' => $attendance->in_time ? Carbon::parse($attendance->in_time)->format('H:i') : null,
                            'status' => $attendance->status,
                            'is_friday' => $attendance->is_friday,
                            'is_govt_holiday' => $attendance->is_govt_holiday,
                            'on_leave' => $attendance->on_leave,
                            'note' => $attendance->note,
                        ];
                    } else {
                        $userData['days'][$day] = null;
                    }
                }
                
                $attendanceMatrix[] = $userData;
            }
            
            // Get holidays (days that are Friday or Govt Holiday for all users)
            $holidays = [];
            $fridays = [];
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::create($year, $month, $day);
                if ($date->isFriday()) {
                    $fridays[] = $day;
                }
            }
            
            return view('admin.attendance.index', compact(
                'attendanceMatrix',
                'users',
                'year',
                'month',
                'daysInMonth',
                'monthName',
                'fridays',
                'holidays'
            ));
            
        } catch (\Exception $e) {
            Log::error('Attendance index error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load attendance data');
        }
    }

    /**
     * Get attendance data for a specific date (for modal)
     */
    public function getDateAttendance(Request $request)
    {
        try {
            $date = $request->get('date', Carbon::today()->format('Y-m-d'));
            $users = User::orderBy('name')->get();
            
            $attendances = Attendance::with('user')
                ->whereDate('attendance_date', $date)
                ->get()
                ->keyBy('user_id');
            
            $isFriday = Carbon::parse($date)->isFriday();
            
            // Check if any attendance has is_govt_holiday = true for this date
            $isHoliday = $attendances->contains(function($attendance) {
                return $attendance->is_govt_holiday == true;
            });
            
            return response()->json([
                'success' => true,
                'date' => $date,
                'is_friday' => $isFriday,
                'is_govt_holiday' => $isHoliday,
                'attendances' => $attendances,
                'users' => $users->map(function($user) use ($attendances) {
                    $attendance = $attendances->get($user->id);
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'in_time' => $attendance ? ($attendance->in_time ? Carbon::parse($attendance->in_time)->format('H:i') : null) : null,
                        'on_leave' => $attendance ? $attendance->on_leave : false,
                        'note' => $attendance ? $attendance->note : null,
                        'attendance_id' => $attendance ? $attendance->id : null,
                    ];
                })
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get date attendance error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load attendance data'
            ], 500);
        }
    }

    /**
     * Store or update attendance for a date
     */
    public function storeOrUpdate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'attendance_date' => 'required|date',
                'is_friday' => 'nullable|in:0,1',
                'is_govt_holiday' => 'nullable|in:0,1',
                'attendance_data' => 'required|array',
                'attendance_data.*.user_id' => 'required|exists:users,id',
                'attendance_data.*.in_time' => 'nullable',
                'attendance_data.*.on_leave' => 'nullable|in:0,1',
                'attendance_data.*.note' => 'nullable|string|max:500',
                'attendance_data.*.attendance_id' => 'nullable|exists:attendances,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $attendanceDate = $request->attendance_date;
            $isFriday = $request->has('is_friday') && $request->input('is_friday') == '1';
            $isHoliday = $request->has('is_govt_holiday') && $request->input('is_govt_holiday') == '1';
            
            $savedCount = 0;
            $errors = [];

            foreach ($request->attendance_data as $data) {
                try {
                    $userId = $data['user_id'];
                    $inTime = $data['in_time'] ?? null;
                    $onLeave = isset($data['on_leave']) && $data['on_leave'] == '1';
                    $note = $data['note'] ?? null;
                    $attendanceId = $data['attendance_id'] ?? null;
                    
                    // Determine status
                    $status = Attendance::getStatusFromTimes(
                        $inTime, 
                        null, 
                        $isFriday, 
                        $isHoliday, 
                        $onLeave
                    );

                    if ($attendanceId) {
                        // Update existing
                        $attendance = Attendance::find($attendanceId);
                        if ($attendance) {
                            $attendance->update([
                                'in_time' => $inTime,
                                'on_leave' => $onLeave,
                                'is_friday' => $isFriday,
                                'is_govt_holiday' => $isHoliday,
                                'note' => $note,
                                'status' => $status
                            ]);
                            $savedCount++;
                        }
                    } else {
                        // Create new
                        Attendance::create([
                            'user_id' => $userId,
                            'attendance_date' => $attendanceDate,
                            'in_time' => $inTime,
                            'out_time' => null,
                            'is_friday' => $isFriday,
                            'is_govt_holiday' => $isHoliday,
                            'on_leave' => $onLeave,
                            'note' => $note,
                            'status' => $status
                        ]);
                        $savedCount++;
                    }
                    
                } catch (\Exception $e) {
                    $user = User::find($userId);
                    $errors[] = "Failed for " . ($user ? $user->name : "User #{$userId}");
                    Log::error('Attendance save error for user ' . $userId . ': ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Attendance saved successfully for {$savedCount} users.",
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            Log::error('Attendance store/update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save attendance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete attendance record
     */
    public function destroy($id)
    {
        try {
            $attendance = Attendance::findOrFail($id);
            $attendance->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Attendance record deleted successfully!'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Attendance deletion error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attendance record.'
            ], 500);
        }
    }

    /**
     * Delete all attendance for a specific date
     */
    public function deleteDate(Request $request)
    {
        try {
            $date = $request->get('date');
            if (!$date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Date is required'
                ], 400);
            }
            
            $deleted = Attendance::whereDate('attendance_date', $date)->delete();
            
            return response()->json([
                'success' => true,
                'message' => "Deleted {$deleted} attendance records for {$date}"
            ]);
            
        } catch (\Exception $e) {
            Log::error('Delete date attendance error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attendance records.'
            ], 500);
        }
    }
}