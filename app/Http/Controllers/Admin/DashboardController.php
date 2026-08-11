<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function dashboard()
{
    $user = auth()->user();
    
    // Fix: Proper permission checking
    $hasFullAccess = false;
    
    // Check if user has admin role or view dashboard permission
    if ($user->hasRole('admin')) {
        $hasFullAccess = true;
    } elseif ($user->hasPermissionTo('view dashboard')) {
        $hasFullAccess = true;
    }
    
    // If no full access, show user-specific dashboard
    if (!$hasFullAccess) {
        return $this->userDashboard($user);
    }
    
    // Date filters
    $today = Carbon::today();
    $startOfMonth = Carbon::now()->startOfMonth();
    $couriers = ['Pathao', 'Steadfast', 'SA', 'SUNDORBAN', 'JANONI', 'REDEX'];
    $todayData = [];
    $monthData = [];

    // Process each courier
    foreach ($couriers as $courier) {
        // Today's data for this courier
        $todayQuery = Invoice::where('courier_name', $courier)
            ->where('status', 'confirmed')
            ->whereDate('invoice_date', $today);
        
        $todayCount = $todayQuery->count();
        
        // Only add if there are invoices
        if ($todayCount > 0) {
            $todayData[$courier] = [
                'invoices' => $todayCount,
                'revenue' => $todayQuery->sum('total'),
                'paid' => $todayQuery->sum('paid_amount'),
                'subtotal' => $todayQuery->sum('subtotal'),
                'delivery' => $todayQuery->sum('delivery_charge'),
                'quantity' => $todayQuery->with('items')->get()->sum(function($invoice) {
                    return $invoice->items->sum('quantity');
                })
            ];
        }
            $totalInvoices = Invoice::where('status', 'confirmed')->count();

        // Month data for this courier
        $monthQuery = Invoice::where('courier_name', $courier)
            ->where('status', 'confirmed')
            ->where('invoice_date', '>=', $startOfMonth);
        
        $monthCount = $monthQuery->count();
        
        // Only add if there are invoices
        if ($monthCount > 0) {
            $monthData[$courier] = [
                'invoices' => $monthCount,
                'revenue' => $monthQuery->sum('total'),
                'paid' => $monthQuery->sum('paid_amount'),
                'subtotal' => $monthQuery->sum('subtotal'),
                'delivery' => $monthQuery->sum('delivery_charge'),
                'quantity' => $monthQuery->with('items')->get()->sum(function($invoice) {
                    return $invoice->items->sum('quantity');
                })
            ];
        }
    }
    
    // In-house data - Today
    $todayInhouseQuery = Invoice::where('is_inhouse_sale', true)
        ->where('status', 'confirmed')
        ->whereDate('invoice_date', $today);
    
    $todayInhouseCount = $todayInhouseQuery->count();
    $todayInhouse = [
        'invoices' => $todayInhouseCount,
        'revenue' => $todayInhouseQuery->sum('total'),
        'paid' => $todayInhouseQuery->sum('paid_amount'),
        'subtotal' => $todayInhouseQuery->sum('subtotal'),
        'delivery' => $todayInhouseQuery->sum('delivery_charge'),
        'quantity' => $todayInhouseQuery->with('items')->get()->sum(function($invoice) {
            return $invoice->items->sum('quantity');
        })
    ];
    
    // In-house data - Month
    $monthInhouseQuery = Invoice::where('is_inhouse_sale', true)
        ->where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfMonth);
    
    $monthInhouseCount = $monthInhouseQuery->count();
    $monthInhouse = [
        'invoices' => $monthInhouseCount,
        'revenue' => $monthInhouseQuery->sum('total'),
        'paid' => $monthInhouseQuery->sum('paid_amount'),
        'subtotal' => $monthInhouseQuery->sum('subtotal'),
        'delivery' => $monthInhouseQuery->sum('delivery_charge'),
        'quantity' => $monthInhouseQuery->with('items')->get()->sum(function($invoice) {
            return $invoice->items->sum('quantity');
        })
    ];
$itemTotalsSql = 'SELECT invoice_id, SUM(quantity) as quantity FROM invoice_items GROUP BY invoice_id';

$monthlyStats = DB::table('invoices')
    ->leftJoin(DB::raw("({$itemTotalsSql}) as item_totals"), 'item_totals.invoice_id', '=', 'invoices.id')
    ->select(
        DB::raw('YEAR(invoices.invoice_date) as year'),
        DB::raw('MONTH(invoices.invoice_date) as month'),
        DB::raw('COUNT(DISTINCT invoices.id) as total_invoices'),
        DB::raw('SUM(invoices.total) as total_revenue'),
        DB::raw('SUM(invoices.paid_amount) as total_paid'),
        DB::raw('SUM(invoices.due_amount) as total_due'),
        DB::raw('SUM(invoices.subtotal) as total_subtotal'),
        DB::raw('SUM(invoices.delivery_charge) as total_delivery'),
        DB::raw('SUM(invoices.total_weight) as total_weight'),
        DB::raw('COALESCE(SUM(item_totals.quantity), 0) as total_quantity')
    )
    ->whereYear('invoices.invoice_date', Carbon::now()->year)
    ->where('invoices.status', 'confirmed')
    ->whereNull('invoices.deleted_at')
    ->groupBy(DB::raw('YEAR(invoices.invoice_date)'), DB::raw('MONTH(invoices.invoice_date)'))
    ->orderBy('year')
    ->orderBy('month')
    ->get()
    ->keyBy('month');

// For months with no data
foreach (range(1, 12) as $month) {
    if (!isset($monthlyStats[$month])) {
        $stats = new \stdClass();
        $stats->year = Carbon::now()->year;
        $stats->month = $month;
        $stats->total_invoices = 0;
        $stats->total_revenue = 0;
        $stats->total_paid = 0;
        $stats->total_due = 0;
        $stats->total_subtotal = 0;
        $stats->total_delivery = 0;
        $stats->total_weight = 0;
        $stats->total_quantity = 0;
        $monthlyStats[$month] = $stats;
    }
}
    
    $totalPaidAmount = Invoice::where('status', 'confirmed')->sum('paid_amount');
    $totalDueAmount = Invoice::where('status', 'confirmed')->sum('due_amount');
    $totalSubtotal = Invoice::where('status', 'confirmed')->sum('subtotal');
    $totalDelivery = Invoice::where('status', 'confirmed')->sum('delivery_charge');
    
    // Today's counts (only confirmed invoices)
    $todayInvoices = Invoice::where('status', 'confirmed')
        ->whereDate('invoice_date', $today)
        ->count();
    $todayRevenue = Invoice::where('status', 'confirmed')
        ->whereDate('invoice_date', $today)
        ->sum('total');
    $todayPaid = Invoice::where('status', 'confirmed')
        ->whereDate('invoice_date', $today)
        ->sum('paid_amount');
    $todayQuantity = Invoice::where('status', 'confirmed')
        ->whereDate('invoice_date', $today)
        ->with('items')
        ->get()
        ->sum(function($invoice) {
            return $invoice->items->sum('quantity');
        });
    $todayDue = Invoice::where('status', 'confirmed')
        ->whereDate('invoice_date', $today)
        ->sum('due_amount');
    $todaySubtotal = Invoice::where('status', 'confirmed')
        ->whereDate('invoice_date', $today)
        ->sum('subtotal');
    $todayDelivery = Invoice::where('status', 'confirmed')
        ->whereDate('invoice_date', $today)
        ->sum('delivery_charge');

    // Last 10 days daily breakdown (only confirmed invoices)
$last10Days = collect();
for ($i = 9; $i >= 0; $i--) {
    $date = Carbon::now()->subDays($i);
    
    // Get invoices for this day
    $dayInvoices = Invoice::where('status', 'confirmed')
        ->whereDate('invoice_date', $date)
        ->get();
    
    // Calculate quantity separately to avoid duplication issues
    $quantity = DB::table('invoices')
        ->join('invoice_items as items', 'invoices.id', '=', 'items.invoice_id')
        ->where('invoices.status', 'confirmed')
        ->whereDate('invoices.invoice_date', $date)
        ->whereNull('invoices.deleted_at')
        ->sum('items.quantity');
    
    $last10Days->push([
        'date' => $date->format('D, M d'),
        'day' => $date->format('d'),
        'full_date' => $date->format('Y-m-d'),
        'count' => $dayInvoices->count(),
        'revenue' => $dayInvoices->sum('total'),
        'paid' => $dayInvoices->sum('paid_amount'),
        'due' => $dayInvoices->sum('due_amount'),
        'subtotal' => $dayInvoices->sum('subtotal'),
        'delivery' => $dayInvoices->sum('delivery_charge'),
        'quantity' => $quantity, // Use separately calculated quantity
    ]);
}
    // This Month counts (only confirmed invoices)
    $monthlyInvoices = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfMonth)
        ->count();
    $monthlyRevenue = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfMonth)
        ->sum('total');
    $monthlyPaid = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfMonth)
        ->sum('paid_amount');
    $monthlyDue = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfMonth)
        ->sum('due_amount');
    $monthlySubtotal = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfMonth)
        ->sum('subtotal');
    $monthlyDelivery = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfMonth)
        ->sum('delivery_charge');
    $monthlyQuantity = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfMonth)
        ->with('items')
        ->get()
        ->sum(function($invoice) {
            return $invoice->items->sum('quantity');
        });
    
    // User performance summary - NOW USING confirmed_at to credit the original creator
    $topCreators = User::select([
        'users.*',
        // Total invoices confirmed (by confirmation date - credit goes to original creator)
        DB::raw('(SELECT COUNT(*) FROM invoices WHERE invoices.created_by = users.id AND DATE(invoices.confirmed_at) = CURDATE() AND invoices.status = "confirmed" AND invoices.deleted_at IS NULL) as total_invoices'),
        
        // Total amount confirmed (by confirmation date)
        DB::raw('(SELECT COALESCE(SUM(total), 0) FROM invoices WHERE invoices.created_by = users.id AND DATE(invoices.confirmed_at) = CURDATE() AND invoices.status = "confirmed" AND invoices.deleted_at IS NULL) as total_amount'),
        
        // Total paid confirmed
        DB::raw('(SELECT COALESCE(SUM(paid_amount), 0) FROM invoices WHERE invoices.created_by = users.id AND DATE(invoices.confirmed_at) = CURDATE() AND invoices.status = "confirmed" AND invoices.deleted_at IS NULL) as total_paid'),
        
        // Total due confirmed
        DB::raw('(SELECT COALESCE(SUM(due_amount), 0) FROM invoices WHERE invoices.created_by = users.id AND DATE(invoices.confirmed_at) = CURDATE() AND invoices.status = "confirmed" AND invoices.deleted_at IS NULL) as total_due'),
        
        // Total subtotal confirmed
        DB::raw('(SELECT COALESCE(SUM(subtotal), 0) FROM invoices WHERE invoices.created_by = users.id AND DATE(invoices.confirmed_at) = CURDATE() AND invoices.status = "confirmed" AND invoices.deleted_at IS NULL) as total_subtotal'),
        
        // Total delivery confirmed
        DB::raw('(SELECT COALESCE(SUM(delivery_charge), 0) FROM invoices WHERE invoices.created_by = users.id AND DATE(invoices.confirmed_at) = CURDATE() AND invoices.status = "confirmed" AND invoices.deleted_at IS NULL) as total_delivery'),
        
        // Total quantity confirmed
        DB::raw('(SELECT COALESCE(SUM(quantity), 0) FROM invoice_items WHERE invoice_items.invoice_id IN (SELECT id FROM invoices WHERE invoices.created_by = users.id AND DATE(invoices.confirmed_at) = CURDATE() AND invoices.status = "confirmed" AND invoices.deleted_at IS NULL)) as total_quantity')
    ])
    ->having('total_invoices', '>', 0)
    ->orderBy('total_amount', 'desc')
    ->get();
    
    $topCreatorsMonth = User::select([
        'users.*',
        DB::raw('(SELECT COUNT(*) FROM invoices WHERE invoices.created_by = users.id AND invoices.invoice_date >= "' . $startOfMonth . '" AND invoices.status = "confirmed" AND invoices.deleted_at IS NULL) as total_invoices'),
        DB::raw('(SELECT COALESCE(SUM(total), 0) FROM invoices WHERE invoices.created_by = users.id AND invoices.invoice_date >= "' . $startOfMonth . '" AND invoices.status = "confirmed" AND invoices.deleted_at IS NULL) as total_amount'),
        DB::raw('(SELECT COALESCE(SUM(paid_amount), 0) FROM invoices WHERE invoices.created_by = users.id AND invoices.invoice_date >= "' . $startOfMonth . '" AND invoices.status = "confirmed" AND invoices.deleted_at IS NULL) as total_paid'),
        DB::raw('(SELECT COALESCE(SUM(due_amount), 0) FROM invoices WHERE invoices.created_by = users.id AND invoices.invoice_date >= "' . $startOfMonth . '" AND invoices.status = "confirmed" AND invoices.deleted_at IS NULL) as total_due'),
        DB::raw('(SELECT COALESCE(SUM(subtotal), 0) FROM invoices WHERE invoices.created_by = users.id AND invoices.invoice_date >= "' . $startOfMonth . '" AND invoices.status = "confirmed" AND invoices.deleted_at IS NULL) as total_subtotal'),
        DB::raw('(SELECT COALESCE(SUM(delivery_charge), 0) FROM invoices WHERE invoices.created_by = users.id AND invoices.invoice_date >= "' . $startOfMonth . '" AND invoices.status = "confirmed" AND invoices.deleted_at IS NULL) as total_delivery'),
        DB::raw('(SELECT COALESCE(SUM(quantity), 0) FROM invoice_items WHERE invoice_items.invoice_id IN (SELECT id FROM invoices WHERE invoices.created_by = users.id AND invoices.invoice_date >= "' . $startOfMonth . '" AND invoices.status = "confirmed" AND invoices.deleted_at IS NULL)) as total_quantity')
    ])
    ->having('total_invoices', '>', 0)
    ->orderBy('total_amount', 'desc')
    ->get();

    $todayPaidInvoices = Invoice::where('status', 'confirmed')
    ->whereDate('invoice_date', $today)
    ->where('paid_amount', '>', 0)
    ->with(['creator', 'items'])
    ->orderBy('paid_amount', 'desc')
    ->get();

// Group by creator for summary
$creatorPaymentSummary = $todayPaidInvoices->groupBy('created_by')->map(function($invoices, $creatorId) {
    $creator = $invoices->first()->creator;
    return [
        'creator_name' => $creator ? $creator->name : 'Unknown',
        'invoice_count' => $invoices->count(),
        'total_paid' => $invoices->sum('paid_amount'),
        'invoices' => $invoices
    ];
})->sortByDesc('total_paid');



$monthlyCourierReport = [];

foreach ($couriers as $courier) {
    $query = Invoice::where('courier_name', $courier)
        ->where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfMonth)
        ->where('invoice_date', '<=', Carbon::now());
    
    $invoices = $query->get();
    
    $monthlyCourierReport[$courier] = [
        'parcels' => $invoices->count(),
        'quantity' => $invoices->sum(function($invoice) {
            return $invoice->items->sum('quantity');
        }),
        'subtotal' => $invoices->sum('subtotal'),
        'delivery' => $invoices->sum('delivery_charge'),
        'total' => $invoices->sum('total'),
        'paid' => $invoices->sum('paid_amount'),
        'due' => $invoices->sum('due_amount'),
    ];
}

// In-house monthly report
$inhouseQuery = Invoice::where('is_inhouse_sale', true)
    ->where('status', 'confirmed')
    ->where('invoice_date', '>=', $startOfMonth)
    ->where('invoice_date', '<=', Carbon::now());

$inhouseInvoices = $inhouseQuery->get();

$monthlyInhouseReport = [
    'parcels' => $inhouseInvoices->count(),
    'quantity' => $inhouseInvoices->sum(function($invoice) {
        return $invoice->items->sum('quantity');
    }),
    'subtotal' => $inhouseInvoices->sum('subtotal'),
    'delivery' => $inhouseInvoices->sum('delivery_charge'),
    'total' => $inhouseInvoices->sum('total'),
    'paid' => $inhouseInvoices->sum('paid_amount'),
    'due' => $inhouseInvoices->sum('due_amount'),
];
$paymentMethods = ['bkash', 'bkash_personal', 'bank_transfer', 'cash'];
$todayPaymentMethods = [];
foreach ($paymentMethods as $method) {
    $query = Invoice::where('status', 'confirmed')
        ->whereDate('invoice_date', $today)
        ->where('payment_method', $method)
        ->where('paid_amount', '>', 0);
    
    $todayPaymentMethods[$method] = [
        'transactions' => $query->count(),
        'total_paid' => $query->sum('paid_amount'),
    ];
}

// This Month's Payment Method Breakdown
$monthlyPaymentMethods = [];
foreach ($paymentMethods as $method) {
    $query = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfMonth)
        ->where('invoice_date', '<=', Carbon::now())
        ->where('payment_method', $method)
        ->where('paid_amount', '>', 0);
    
    $monthlyPaymentMethods[$method] = [
        'transactions' => $query->count(),
        'total_paid' => $query->sum('paid_amount'),
    ];
}

// Today's Payment Details (for detailed view)
$todayPaymentDetails = Invoice::where('status', 'confirmed')
    ->whereDate('invoice_date', $today)
    ->where('paid_amount', '>', 0)
    ->whereIn('payment_method', $paymentMethods)
    ->with('creator')
    ->orderBy('paid_amount', 'desc')
    ->get();
 
       $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $couriers = ['Pathao', 'Steadfast', 'SA', 'SUNDORBAN', 'JANONI', 'REDEX'];
        $paymentMethods = ['Cash', 'Bank', 'Mobile Banking', 'Rocket', 'bKash', 'Nagad'];

        // ... [Your existing invoice queries here] ...

        // ==================== ATTENDANCE DATA ====================
        
        // TODAY'S ATTENDANCE WITH DETAILS
        $staffMembers = Staff::active()->orderBy('name')->get();
        $todayTotalStaff = $staffMembers->count();
        
        $todayAttendance = [];
        $todayPresent = 0;
        $todayAbsent = 0;
        $todayLate = 0;
        $todayLeave = 0;
        $todayHoliday = 0;
        $todayFriday = 0;
        $todayLateStaff = [];
        $todayLeaveStaff = [];
        $todayPresentStaff = [];
        $todayAbsentStaff = [];

        $lateThreshold = Carbon::parse('11:00:00');

        foreach ($staffMembers as $staff) {
            $attendance = Attendance::where('staff_id', $staff->id)
                ->whereDate('attendance_date', $today)
                ->first();

            $inTime = $attendance ? $attendance->in_time : null;
            $outTime = $attendance ? $attendance->out_time : null;
            $status = $attendance ? $attendance->status : 'absent';
            $isLate = false;
            $lateMinutes = 0;
            $lateTimeDisplay = '-';

            // Check if late (after 11:00 AM)
            if ($inTime) {
                $inTimeParsed = Carbon::parse($inTime);
                if ($inTimeParsed->gt($lateThreshold)) {
                    $isLate = true;
                    $lateMinutes = $inTimeParsed->diffInMinutes($lateThreshold);
                    $lateTimeDisplay = $lateMinutes . ' min late';
                } else {
                    $lateTimeDisplay = 'On Time';
                }
            }

            // Count statistics and categorize staff
            if ($status == 'present' || $status == 'late') {
                $todayPresent++;
                if ($status == 'late' || $isLate) {
                    $todayLate++;
                    $todayLateStaff[] = [
                        'name' => $staff->name,
                        'designation' => $staff->designation,
                        'late_minutes' => $lateMinutes,
                        'in_time' => $inTime ? Carbon::parse($inTime)->format('h:i A') : '-',
                    ];
                } else {
                    $todayPresentStaff[] = $staff->name;
                }
            } elseif ($status == 'absent') {
                $todayAbsent++;
                $todayAbsentStaff[] = $staff->name;
            } elseif ($status == 'leave') {
                $todayLeave++;
                $todayLeaveStaff[] = [
                    'name' => $staff->name,
                    'designation' => $staff->designation,
                    'note' => $attendance ? $attendance->note : 'On Leave',
                ];
            } elseif ($status == 'holiday') {
                $todayHoliday++;
            } elseif ($status == 'friday') {
                $todayFriday++;
            }

            // Prepare attendance data for each staff
            $todayAttendance[] = [
                'staff_name' => $staff->name,
                'designation' => $staff->designation,
                'in_time' => $inTime ? Carbon::parse($inTime)->format('h:i A') : '-',
                'in_time_raw' => $inTime,
                'out_time' => $outTime ? Carbon::parse($outTime)->format('h:i A') : '-',
                'out_time_raw' => $outTime,
                'status' => $status,
                'status_badge' => $attendance ? $attendance->status_badge : '<span class="badge badge-danger">Absent</span>',
                'is_late' => $isLate,
                'late_minutes' => $lateMinutes,
                'late_display' => $lateTimeDisplay,
                'is_friday' => $attendance ? $attendance->is_friday : false,
                'is_holiday' => $attendance ? $attendance->is_govt_holiday : false,
                'on_leave' => $attendance ? $attendance->on_leave : false,
                'note' => $attendance ? $attendance->note : null,
                'has_attendance' => $attendance ? true : false,
            ];
        }

        // Today's Attendance Summary
        $todayAttendanceSummary = [
            'total_staff' => $todayTotalStaff,
            'present' => $todayPresent,
            'absent' => $todayAbsent,
            'late' => $todayLate,
            'leave' => $todayLeave,
            'holiday' => $todayHoliday,
            'friday' => $todayFriday,
            'attendance_percentage' => $todayTotalStaff > 0 ? round(($todayPresent / $todayTotalStaff) * 100, 2) : 0,
            'is_friday' => $today->isFriday(),
            'late_staff' => $todayLateStaff,
            'leave_staff' => $todayLeaveStaff,
            'absent_staff' => $todayAbsentStaff,
            'present_staff' => $todayPresentStaff,
        ];

        // MONTHLY ATTENDANCE REPORT WITH DETAILED STATS
        $monthlyAttendanceReport = [];
        $monthlyTotalPresent = 0;
        $monthlyTotalAbsent = 0;
        $monthlyTotalLate = 0;
        $monthlyTotalLeave = 0;
        $monthlyTotalHoliday = 0;
        $monthlyTotalFriday = 0;
        $monthlyWorkingDays = 0;
        $monthlyLateMinutes = 0;
        $monthlyLateStaff = [];
        $monthlyLeaveStaff = [];

        foreach ($staffMembers as $staff) {
            $attendances = Attendance::where('staff_id', $staff->id)
                ->whereBetween('attendance_date', [$startOfMonth, Carbon::now()])
                ->get();

            $totalDays = $attendances->count();
            $presentDays = $attendances->where('status', 'present')->count() + $attendances->where('status', 'late')->count();
            $absentDays = $attendances->where('status', 'absent')->count();
            $lateDays = $attendances->where('status', 'late')->count();
            $leaveDays = $attendances->where('status', 'leave')->count();
            $holidayDays = $attendances->where('status', 'holiday')->count();
            $fridayDays = $attendances->where('status', 'friday')->count();

            // Calculate total late minutes
            $staffLateMinutes = 0;
            $lateThreshold = Carbon::parse('11:00:00');
            foreach ($attendances as $attendance) {
                if ($attendance->in_time) {
                    $inTime = Carbon::parse($attendance->in_time);
                    if ($inTime->gt($lateThreshold)) {
                        $staffLateMinutes += $inTime->diffInMinutes($lateThreshold);
                    }
                }
            }

            $attendancePercentage = $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0;

            // Determine performance rating
            $rating = 'Good';
            $ratingClass = 'success';
            if ($attendancePercentage >= 95) {
                $rating = 'Excellent';
                $ratingClass = 'success';
            } elseif ($attendancePercentage >= 85) {
                $rating = 'Good';
                $ratingClass = 'primary';
            } elseif ($attendancePercentage >= 75) {
                $rating = 'Average';
                $ratingClass = 'warning';
            } elseif ($attendancePercentage >= 60) {
                $rating = 'Poor';
                $ratingClass = 'danger';
            } else {
                $rating = 'Very Poor';
                $ratingClass = 'danger';
            }

            $monthlyAttendanceReport[] = [
                'staff_name' => $staff->name,
                'designation' => $staff->designation,
                'total_days' => $totalDays,
                'present' => $presentDays,
                'absent' => $absentDays,
                'late' => $lateDays,
                'leave' => $leaveDays,
                'holiday' => $holidayDays,
                'friday' => $fridayDays,
                'late_minutes' => $staffLateMinutes,
                'late_hours' => round($staffLateMinutes / 60, 2),
                'attendance_percentage' => $attendancePercentage,
                'rating' => $rating,
                'rating_class' => $ratingClass,
            ];

            // Accumulate totals
            $monthlyTotalPresent += $presentDays;
            $monthlyTotalAbsent += $absentDays;
            $monthlyTotalLate += $lateDays;
            $monthlyTotalLeave += $leaveDays;
            $monthlyTotalHoliday += $holidayDays;
            $monthlyTotalFriday += $fridayDays;
            $monthlyWorkingDays += $totalDays;
            $monthlyLateMinutes += $staffLateMinutes;
        }

        // Monthly Attendance Summary
        $monthlyAttendanceSummary = [
            'total_staff' => $todayTotalStaff,
            'total_working_days' => $monthlyWorkingDays,
            'total_present' => $monthlyTotalPresent,
            'total_absent' => $monthlyTotalAbsent,
            'total_late' => $monthlyTotalLate,
            'total_leave' => $monthlyTotalLeave,
            'total_holiday' => $monthlyTotalHoliday,
            'total_friday' => $monthlyTotalFriday,
            'total_late_minutes' => $monthlyLateMinutes,
            'total_late_hours' => round($monthlyLateMinutes / 60, 2),
            'attendance_percentage' => $monthlyWorkingDays > 0 ? round(($monthlyTotalPresent / $monthlyWorkingDays) * 100, 2) : 0,
            'month_name' => $startOfMonth->format('F Y'),
        ];

        // Get top 5 performers and bottom 5
        $topPerformers = collect($monthlyAttendanceReport)
            ->sortByDesc('attendance_percentage')
            ->take(5)
            ->values();

        $poorPerformers = collect($monthlyAttendanceReport)
            ->sortBy('attendance_percentage')
            ->take(5)
            ->values();
// This Month's Payment Details
$monthlyPaymentDetails = Invoice::where('status', 'confirmed')
    ->where('invoice_date', '>=', $startOfMonth)
    ->where('invoice_date', '<=', Carbon::now())
    ->where('paid_amount', '>', 0)
    ->whereIn('payment_method', $paymentMethods)
    ->with('creator')
    ->orderBy('paid_amount', 'desc')
    ->get();

    return view('admin.dashboard', compact(
                'totalInvoices',

          'todayPaymentMethods',
    'monthlyPaymentMethods',
    'todayPaymentDetails',
    'poorPerformers',
    'monthlyPaymentDetails',
          'monthlyCourierReport',
    'monthlyInhouseReport',
        'todayPaidInvoices',
        'creatorPaymentSummary',
        'totalPaidAmount',
        'totalDueAmount',
        'totalSubtotal',
        'totalDelivery',
        'todayInvoices',
        'todayRevenue',
        'todayPaid',
        'todayDue',
        'todaySubtotal',
        'todayDelivery',
        'todayQuantity',
        'monthlyInvoices',
        'monthlyRevenue',
        'monthlyPaid',
        'monthlyDue',
        'monthlySubtotal',
        'today',
        'monthlyDelivery',
        'monthlyQuantity',
        'topCreators',
        'monthlyStats',
        'last10Days',
        'hasFullAccess',
        'todayData',
        'monthData',
        'todayInhouse',
        'monthInhouse',
        'topCreatorsMonth',
          'todayAttendance',
            'todayAttendanceSummary',
            'monthlyAttendanceReport',
            'monthlyAttendanceSummary',
            'topPerformers'
           
    ));
}
    
    /**
     * User-specific dashboard showing only their own performance
     */
   
private function userDashboard($user)
{
    // Date filters
    $today = Carbon::today();
    $startOfMonth = Carbon::now()->startOfMonth();    
    // Get user's invoices (only confirmed ones)
    $userInvoices = Invoice::where('created_by', $user->id)->where('status', 'confirmed');
    // Total stats
        $totalInvoices = (clone $userInvoices)->count();

$totalRevenue = (clone $userInvoices)->sum('total');
    $totalPaid = (clone $userInvoices)->sum('paid_amount');
    $totalDue = (clone $userInvoices)->sum('due_amount');
    $totalSubtotal = (clone $userInvoices)->sum('subtotal');
    $totalDelivery = (clone $userInvoices)->sum('delivery_charge');
    $totalQuantity = (clone $userInvoices)
        ->with('items')
        ->get()
        ->sum(function($invoice) {
            return $invoice->items->sum('quantity');
        });
   
    // Today's stats - Use confirmed_at for date filtering
   
    // Today's stats - Use confirmed_at for date filtering
    $todayInvoices = (clone $userInvoices)->whereDate('confirmed_at', $today)->count();
    $todayRevenue = (clone $userInvoices)->whereDate('confirmed_at', $today)->sum('total');
    $todayPaid = (clone $userInvoices)->whereDate('confirmed_at', $today)->sum('paid_amount');
    $todayDue = (clone $userInvoices)->whereDate('confirmed_at', $today)->sum('due_amount');
    $todaySubtotal = (clone $userInvoices)->whereDate('confirmed_at', $today)->sum('subtotal');
    $todayDelivery = (clone $userInvoices)->whereDate('confirmed_at', $today)->sum('delivery_charge');
    $todayQuantity = (clone $userInvoices)
        ->whereDate('confirmed_at', $today)
        ->with('items')
        ->get()
        ->sum(function($invoice) {
            return $invoice->items->sum('quantity');
        });
    
    // This Month stats - Use confirmed_at for date filtering
    $monthlyInvoices = (clone $userInvoices)->where('confirmed_at', '>=', $startOfMonth)->count();
    $monthlyRevenue = (clone $userInvoices)->where('confirmed_at', '>=', $startOfMonth)->sum('total');
    $monthlyPaid = (clone $userInvoices)->where('confirmed_at', '>=', $startOfMonth)->sum('paid_amount');
    $monthlyDue = (clone $userInvoices)->where('confirmed_at', '>=', $startOfMonth)->sum('due_amount');
    $monthlySubtotal = (clone $userInvoices)->where('confirmed_at', '>=', $startOfMonth)->sum('subtotal');
    $monthlyDelivery = (clone $userInvoices)->where('confirmed_at', '>=', $startOfMonth)->sum('delivery_charge');
    $monthlyQuantity = (clone $userInvoices)
        ->where('confirmed_at', '>=', $startOfMonth)
        ->with('items')
        ->get()
        ->sum(function($invoice) {
            return $invoice->items->sum('quantity');
        });
    
    
    // Monthly breakdown for current year - Use confirmed_at
    $monthlyStats = DB::table('invoices')
        ->select(
            DB::raw('YEAR(invoices.confirmed_at) as year'),
            DB::raw('MONTH(invoices.confirmed_at) as month'),
            DB::raw('COUNT(DISTINCT invoices.id) as total_invoices'),
            DB::raw('SUM(invoices.total) as total_revenue'),
            DB::raw('SUM(invoices.paid_amount) as total_paid'),
            DB::raw('SUM(invoices.due_amount) as total_due'),
            DB::raw('SUM(invoices.subtotal) as total_subtotal'),
            DB::raw('SUM(invoices.delivery_charge) as total_delivery'),
            DB::raw('COALESCE(SUM(items.quantity), 0) as total_quantity')
        )
        ->leftJoin('invoice_items as items', 'invoices.id', '=', 'items.invoice_id')
        ->where('invoices.created_by', $user->id)
        ->where('invoices.status', 'confirmed')
        ->whereYear('invoices.confirmed_at', Carbon::now()->year)
        ->whereNull('invoices.deleted_at')
        ->groupBy(DB::raw('YEAR(invoices.confirmed_at)'), DB::raw('MONTH(invoices.confirmed_at)'))
        ->orderBy('year')
        ->orderBy('month')
        ->get()
        ->keyBy('month');
    
    
    
    // Payment status counts for user (only confirmed)
    $paymentStatusCounts = (clone $userInvoices)
        ->select('payment_status', DB::raw('count(*) as count'))
        ->groupBy('payment_status')
        ->pluck('count', 'payment_status');
    
    // Set hasFullAccess to false
    $hasFullAccess = false;
    
    return view('admin.dashboard', compact(
                'totalInvoices',

        'user',
        'totalRevenue',
        'totalQuantity',
        'totalPaid',
        'totalDue',
        'totalSubtotal',
        'totalDelivery',
        'todayInvoices',
        'todayRevenue',
        'todayPaid',
        'todayDue',
        'todaySubtotal',
        'todayDelivery',
        'todayQuantity',
        'monthlyInvoices',
        'monthlyRevenue',
        'monthlyPaid',
        'monthlyDue',
        'monthlySubtotal',
        'monthlyDelivery',
        'monthlyQuantity',
        'monthlyStats',
        'paymentStatusCounts',
        'hasFullAccess'
    ));
}
}