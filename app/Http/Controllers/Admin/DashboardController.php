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
    $adminhasFullAccess = false;
    
    // Check if user has admin role or view dashboard permission
    if ($user->hasRole('admin')) {
        $hasFullAccess = true;
        $adminhasFullAccess = true;
    } elseif ($user->hasPermissionTo('view dashboard')) {
        $hasFullAccess = true;
        $adminhasFullAccess = false;
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

// Get total invoices once (moved outside the loop)
$totalInvoices = Invoice::where('status', 'confirmed')->count();

// Process each courier
foreach ($couriers as $courier) {
    // TODAY'S DATA
    // Create a fresh base query for today
    $todayBaseQuery = Invoice::where('courier_name', $courier)
        ->where('status', 'confirmed')
        ->whereDate('invoice_date', $today);
    
    $todayCount = $todayBaseQuery->count();
    
    // Only add if there are invoices
    if ($todayCount > 0) {
        // Create fresh queries for each aggregate to avoid cross-contamination
        $todayData[$courier] = [
            'invoices' => $todayCount,
            'revenue' => Invoice::where('courier_name', $courier)
                ->where('status', 'confirmed')
                ->whereDate('invoice_date', $today)
                ->sum('total'),
            'paid' => Invoice::where('courier_name', $courier)
                ->where('status', 'confirmed')
                ->whereDate('invoice_date', $today)
                ->sum('paid_amount'),
            'paidInvoices' => Invoice::where('courier_name', $courier)
                ->where('status', 'confirmed')
                ->whereDate('invoice_date', $today)
                ->where('paid_amount', '>', 0)
                ->count(),
            'subtotal' => Invoice::where('courier_name', $courier)
                ->where('status', 'confirmed')
                ->whereDate('invoice_date', $today)
                ->sum('subtotal'),
            'delivery' => Invoice::where('courier_name', $courier)
                ->where('status', 'confirmed')
                ->whereDate('invoice_date', $today)
                ->sum('delivery_charge'),
            'quantity' => Invoice::where('courier_name', $courier)
                ->where('status', 'confirmed')
                ->whereDate('invoice_date', $today)
                ->with('items')
                ->get()
                ->sum(function($invoice) {
                    return $invoice->items->sum('quantity');
                })
        ];
    }

    // MONTH DATA
    // Create a fresh base query for the month
    $monthBaseQuery = Invoice::where('courier_name', $courier)
        ->where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfMonth);
    
    $monthCount = $monthBaseQuery->count();
    
    // Only add if there are invoices
    if ($monthCount > 0) {
        // Create fresh queries for each aggregate to avoid cross-contamination
        $monthData[$courier] = [
            'invoices' => $monthCount,
            'revenue' => Invoice::where('courier_name', $courier)
                ->where('status', 'confirmed')
                ->where('invoice_date', '>=', $startOfMonth)
                ->sum('total'),
            'paid' => Invoice::where('courier_name', $courier)
                ->where('status', 'confirmed')
                ->where('invoice_date', '>=', $startOfMonth)
                ->sum('paid_amount'),
            'paidInvoices' => Invoice::where('courier_name', $courier)
                ->where('status', 'confirmed')
                ->where('invoice_date', '>=', $startOfMonth)
                ->where('paid_amount', '>', 0)
                ->count(),
            'subtotal' => Invoice::where('courier_name', $courier)
                ->where('status', 'confirmed')
                ->where('invoice_date', '>=', $startOfMonth)
                ->sum('subtotal'),
            'delivery' => Invoice::where('courier_name', $courier)
                ->where('status', 'confirmed')
                ->where('invoice_date', '>=', $startOfMonth)
                ->sum('delivery_charge'),
            'quantity' => Invoice::where('courier_name', $courier)
                ->where('status', 'confirmed')
                ->where('invoice_date', '>=', $startOfMonth)
                ->with('items')
                ->get()
                ->sum(function($invoice) {
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
        'paidInvoices' => $todayInhouseQuery->where('paid_amount', '>', 0)->count(),
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
        'paidInvoices' => $monthInhouseQuery->where('paid_amount', '>', 0)->count(),
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
    $todayPaidInvoice = Invoice::where('status', 'confirmed')
        ->whereDate('invoice_date', $today)
        ->where('paid_amount', '>', 0)
        ->count();
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
    $monthlyPaidInvoices = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfMonth)
        ->where('paid_amount', '>', 0)
        ->count();
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

      
       
// This Month's Payment Details
$monthlyPaymentDetails = Invoice::where('status', 'confirmed')
    ->where('invoice_date', '>=', $startOfMonth)
    ->where('invoice_date', '<=', Carbon::now())
    ->where('paid_amount', '>', 0)
    ->whereIn('payment_method', $paymentMethods)
    ->with('creator')
    ->orderBy('paid_amount', 'desc')
    ->get();

            $todayPerformance = User::getTopTeamMembersToday();
        
        $monthPerformance = User::getTopTeamMembersMonth();

$attendanceData = $this->getAttendanceMatrix();
$attendanceMatrix = $attendanceData['matrix'];
$daysInMonth = $attendanceData['daysInMonth'];
$monthName = $attendanceData['monthName'];
$attYear = $attendanceData['year'];
$attMonth = $attendanceData['month'];

    return view('admin.dashboard', compact(
                'totalInvoices',
                    'daysInMonth',
                        'attendanceMatrix',
    'monthName',
    'attYear',
    'attMonth',
'todayPerformance',
'monthPerformance',
          'todayPaymentMethods',
    'monthlyPaymentMethods',
    'todayPaymentDetails',
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
        'monthlyPaidInvoices',
        'monthlySubtotal',
        'today',
        'monthlyDelivery',
        'monthlyQuantity',
        'topCreators',
        'monthlyStats',
        'last10Days',
        'hasFullAccess',
        'adminhasFullAccess',
        'todayData',
        'monthData',
        'todayInhouse',
        'monthInhouse',
        'todayPaidInvoice',
        'topCreatorsMonth',
       
           
    ));
}
    



/**
 * Get simple monthly attendance data for dashboard
 */
private function getAttendanceMatrix()
{
    $year = Carbon::now()->year;
    $month = Carbon::now()->month;
    
    // Get all users
    $users = User::orderBy('name')->get();
    
    // Get attendance for the month
    $attendances = Attendance::with('user')
        ->whereYear('attendance_date', $year)
        ->whereMonth('attendance_date', $month)
        ->get()
        ->groupBy(function($attendance) {
            return $attendance->user_id . '_' . $attendance->attendance_date->format('Y-m-d');
        });
    
    // Get days in month
    $daysInMonth = Carbon::create($year, $month)->daysInMonth;
    $monthName = Carbon::create($year, $month)->format('F Y');
    
    // Create a matrix of attendance data
    $attendanceMatrix = [];
    foreach ($users as $user) {
        // Count late and absent days for this user
        $lateCount = 0;
        $absentCount = 0;
        
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'days' => [],
            'late_count' => 0,
            'absent_count' => 0,
        ];
        
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($year, $month, $day)->format('Y-m-d');
            $key = $user->id . '_' . $date;
            
            if (isset($attendances[$key])) {
                $attendance = $attendances[$key]->first();
                $status = $attendance->status;
                
                // Count late and absent (excluding leave, holiday, friday)
                if ($status == 'late') {
                    $lateCount++;
                } elseif ($status == 'absent') {
                    $absentCount++;
                }
                
                $userData['days'][$day] = [
                    'id' => $attendance->id,
                    'date' => $date,
                    'in_time' => $attendance->in_time ? Carbon::parse($attendance->in_time)->format('H:i') : null,
                    'status' => $status,
                    'is_friday' => $attendance->is_friday,
                    'is_govt_holiday' => $attendance->is_govt_holiday,
                    'on_leave' => $attendance->on_leave,
                    'note' => $attendance->note,
                ];
            } else {
                $userData['days'][$day] = null;
            }
        }
        
        // Add counts to user data
        $userData['late_count'] = $lateCount;
        $userData['absent_count'] = $absentCount;
        
        $attendanceMatrix[] = $userData;
    }
    
    return [
        'matrix' => $attendanceMatrix,
        'daysInMonth' => $daysInMonth,
        'monthName' => $monthName,
        'year' => $year,
        'month' => $month,
    ];
}
    /**
     * User-specific dashboard showing only their own performance
     */
   private function userDashboard($user)
{
    $today = Carbon::today();
    $startOfMonth = Carbon::now()->startOfMonth();

    /*
    |--------------------------------------------------------------------------
    | Current authenticated user's invoices
    |--------------------------------------------------------------------------
    | Only confirmed invoices where:
    | - user created the invoice
    | OR
    | - invoice is assigned to this user as team member
    |--------------------------------------------------------------------------
    */
    $userInvoices = Invoice::where('status', 'confirmed')
        ->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
              ->orWhere('team_id', $user->id);
        });

    /*
    |--------------------------------------------------------------------------
    | TODAY
    |--------------------------------------------------------------------------
    */
    $todayInvoicesQuery = (clone $userInvoices)
        ->whereDate('confirmed_at', $today);

    $todayInvoices = (clone $todayInvoicesQuery)->count();

    $todayRevenue = (clone $todayInvoicesQuery)->sum('total');

    $todayPaid = (clone $todayInvoicesQuery)->sum('paid_amount');

    $todayDue = (clone $todayInvoicesQuery)->sum('due_amount');

    $todaySubtotal = (clone $todayInvoicesQuery)->sum('subtotal');

    $todayDelivery = (clone $todayInvoicesQuery)->sum('delivery_charge');

    $todayQuantity = (clone $todayInvoicesQuery)
        ->with('items')
        ->get()
        ->sum(function ($invoice) {
            return $invoice->items->sum('quantity');
        });


    /*
    |--------------------------------------------------------------------------
    | THIS MONTH
    |--------------------------------------------------------------------------
    */
    $monthlyInvoicesQuery = (clone $userInvoices)
        ->where('confirmed_at', '>=', $startOfMonth);

    $monthlyInvoices = (clone $monthlyInvoicesQuery)->count();

    $monthlyRevenue = (clone $monthlyInvoicesQuery)->sum('total');

    $monthlyPaid = (clone $monthlyInvoicesQuery)->sum('paid_amount');

    $monthlyDue = (clone $monthlyInvoicesQuery)->sum('due_amount');

    $monthlySubtotal = (clone $monthlyInvoicesQuery)->sum('subtotal');

    $monthlyDelivery = (clone $monthlyInvoicesQuery)->sum('delivery_charge');

    $monthlyQuantity = (clone $monthlyInvoicesQuery)
        ->with('items')
        ->get()
        ->sum(function ($invoice) {
            return $invoice->items->sum('quantity');
        });


    /*
    |--------------------------------------------------------------------------
    | User access flags
    |--------------------------------------------------------------------------
    */
    $hasFullAccess = false;
    $adminhasFullAccess = false;


    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    return view('admin.dashboard', compact(
        'user',

        // Today
        'todayInvoices',
        'todayRevenue',
        'todayPaid',
        'todayDue',
        'todaySubtotal',
        'todayDelivery',
        'todayQuantity',

        // This month
        'monthlyInvoices',
        'monthlyRevenue',
        'monthlyPaid',
        'monthlyDue',
        'monthlySubtotal',
        'monthlyDelivery',
        'monthlyQuantity',

        // Access
        'hasFullAccess',
        'adminhasFullAccess'
    ));
}
}