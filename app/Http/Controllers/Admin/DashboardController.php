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
/**
     * Couriers tracked individually on the dashboard. Any confirmed,
     * non-inhouse invoice whose courier_name is NOT in this list is
     * folded into an "Others" bucket, so the courier breakdown always
     * sums to exactly the same total as the overall today/month totals
     * and the Creators Performance table. (Previously, invoices with a
     * courier name outside this list were silently excluded from the
     * "Today's Summary" cards but still counted in Creators Performance
     * and Last 10 Days — that mismatch was the main source of the
     * different numbers you were seeing.)
     */
    private array $couriers = ['Pathao', 'Steadfast', 'SA', 'SUNDORBAN', 'JANONI', 'REDEX'];
 
    /**
     * Courier names that represent exchanges rather than real sales.
     * Invoices with these courier names are excluded from the today/
     * month revenue totals, the courier breakdown, and the Last 10 Days
     * table (an exchange isn't a sale, and often carries a negative or
     * net subtotal that would distort those numbers). They are
     * deliberately NOT excluded from Creators/Team Performance — a
     * creator still gets credit for having processed the order.
     */
    private array $excludedCouriers = ['Exchange'];
 
    /**
     * Single source of truth for payment method values. The old code
     * declared this twice with two different value sets (one lowercase
     * snake_case set used for the breakdown counts, one capitalized set
     * used for the "payment details" whereIn) — if your payment_method
     * column actually stores the lowercase values, the details tables
     * were filtering against values that don't exist and returning
     * nothing/wrong rows. Adjust this array to match what's actually
     * stored in your `invoices.payment_method` column.
     */
    private array $paymentMethods = ['bkash', 'bkash_personal', 'bank_transfer', 'cash'];
 
    public function dashboard()
    {
        $user = auth()->user();
 
        $hasFullAccess = false;
        $adminhasFullAccess = false;
 
        if ($user->hasRole('admin')) {
            $hasFullAccess = true;
            $adminhasFullAccess = true;
        } elseif ($user->hasPermissionTo('view dashboard')) {
            $hasFullAccess = true;
            $adminhasFullAccess = false;
        }
 
        if (!$hasFullAccess) {
            return $this->userDashboard($user);
        }
 
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
 
        // ---------------------------------------------------------------
        // Base, reusable query builders. EVERY block below is built from
        // these two closures so the numbers always reconcile with each
        // other. Each call returns a *fresh* builder instance.
        // ---------------------------------------------------------------
        $todayBase = fn () => Invoice::where('status', 'confirmed')
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('courier_name')->orWhereNotIn('courier_name', $this->excludedCouriers);
            })
            ->whereDate('invoice_date', $today);
 
        $monthBase = fn () => Invoice::where('status', 'confirmed')
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('courier_name')->orWhereNotIn('courier_name', $this->excludedCouriers);
            })
            ->where('invoice_date', '>=', $startOfMonth);
 
        $totalInvoices = Invoice::where('status', 'confirmed')->count();
 
        // ---------------- TODAY: courier + in-house breakdown -----------
        [$todayData, $todayInhouse] = $this->buildCourierBreakdown($todayBase());
 
        // ---------------- THIS MONTH: courier + in-house breakdown ------
        // (Fixes the old bug where this block sat outside the foreach
        // loop and only ever computed data for the last courier, REDEX.)
        [$monthData, $monthInhouse] = $this->buildCourierBreakdown($monthBase());
 
        // ---------------- Monthly stats (Jan-Dec) ------------------------
        $itemTotalsSql = 'SELECT invoice_id, SUM(quantity) as quantity FROM invoice_items GROUP BY invoice_id';
        $returnTotalsSql = 'SELECT invoice_id, SUM(quantity) as return_quantity FROM return_items GROUP BY invoice_id';
 
        $monthlyStats = DB::table('invoices')
            ->leftJoin(DB::raw("({$itemTotalsSql}) as item_totals"), 'item_totals.invoice_id', '=', 'invoices.id')
            ->leftJoin(DB::raw("({$returnTotalsSql}) as return_totals"), 'return_totals.invoice_id', '=', 'invoices.id')
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
                DB::raw('COALESCE(SUM(item_totals.quantity), 0) - COALESCE(SUM(return_totals.return_quantity), 0) as total_quantity')
            )
            ->whereYear('invoices.invoice_date', Carbon::now()->year)
            ->where('invoices.status', 'confirmed')
            ->whereNull('invoices.deleted_at')
            ->groupBy(DB::raw('YEAR(invoices.invoice_date)'), DB::raw('MONTH(invoices.invoice_date)'))
            ->orderBy('year')->orderBy('month')
            ->get()
            ->keyBy('month');
 
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
 
        // ---------------- All-time totals --------------------------------
        $totalPaidAmount = Invoice::where('status', 'confirmed')->sum('paid_amount');
        $totalDueAmount = Invoice::where('status', 'confirmed')->sum('due_amount');
        $totalSubtotal = Invoice::where('status', 'confirmed')->sum('subtotal');
        $totalDelivery = Invoice::where('status', 'confirmed')->sum('delivery_charge');
 
        // ---------------- Today / Month grand totals ----------------------
        // These now come from EXACTLY the same base query as the courier
        // breakdown and the "Others" bucket above, so
        // todayInvoices === sum(todayData invoices) + todayInhouse invoices
        // always holds true.
        $todaySummary = $this->buildSummary($todayBase());
        $todayInvoices = $todaySummary['invoices'];
        $todayRevenue = $todaySummary['revenue'];
        $todayPaid = $todaySummary['paid'];
        $todayPaidInvoice = $todaySummary['paidInvoices'];
        $todayQuantity = $todaySummary['quantity'];
        $todayDue = $todaySummary['due'];
        $todaySubtotal = $todaySummary['subtotal'];
        $todayDelivery = $todaySummary['delivery'];
 
        $monthSummary = $this->buildSummary($monthBase());
        $monthlyInvoices = $monthSummary['invoices'];
        $monthlyRevenue = $monthSummary['revenue'];
        $monthlyPaid = $monthSummary['paid'];
        $monthlyPaidInvoices = $monthSummary['paidInvoices'];
        $monthlyDue = $monthSummary['due'];
        $monthlySubtotal = $monthSummary['subtotal'];
        $monthlyDelivery = $monthSummary['delivery'];
        $monthlyQuantity = $monthSummary['quantity'];
 
        // ---------------- Last 10 days -------------------------------------
        // Now uses the exact same status/deleted_at/date filter as every
        // other "today" style block (previously this used a separate raw
        // join query with no is_inhouse_sale awareness, which is fine for
        // a grand total but was diverging from the other cards due to the
        // orphan-courier issue described above).
        $last10Days = collect();
        for ($i = 9; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $daySummary = $this->buildSummary(
                Invoice::where('status', 'confirmed')
                    ->whereNull('deleted_at')
                    ->where(function ($q) {
                        $q->whereNull('courier_name')->orWhereNotIn('courier_name', $this->excludedCouriers);
                    })
                    ->whereDate('invoice_date', $date)
            );
 
            $last10Days->push([
                'date' => $date->format('D, M d'),
                'day' => $date->format('d'),
                'full_date' => $date->format('Y-m-d'),
                'count' => $daySummary['invoices'],
                'revenue' => $daySummary['revenue'],
                'paid' => $daySummary['paid'],
                'due' => $daySummary['due'],
                'subtotal' => $daySummary['subtotal'],
                'delivery' => $daySummary['delivery'],
                'quantity' => $daySummary['quantity'],
            ]);
        }
 
        // ---------------- Creators performance (today / month) ------------
        // Both now come from the same underlying helper (only the date
        // condition differs), and both already correctly restrict to
        // is_inhouse_sale = 0 with no courier restriction — matching the
        // "Others"-inclusive courier breakdown above.
        $topCreators = $this->topCreatorsQuery($today, $today, true)->get();
        $topCreatorsMonth = $this->topCreatorsQuery($startOfMonth, Carbon::now(), false)->get();
 
        // ---------------- Today's paid invoices / creator summary ---------
        $todayPaidInvoices = ($todayBase())
            ->where('paid_amount', '>', 0)
            ->with(['creator', 'items', 'returnItems'])
            ->orderBy('paid_amount', 'desc')
            ->get();
 
        $creatorPaymentSummary = $todayPaidInvoices->groupBy('created_by')->map(function ($invoices) {
            $creator = $invoices->first()->creator;
            return [
                'creator_name' => $creator ? $creator->name : 'Unknown',
                'invoice_count' => $invoices->count(),
                'total_paid' => $invoices->sum('paid_amount'),
                'invoices' => $invoices,
            ];
        })->sortByDesc('total_paid');
 
        // ---------------- Monthly courier report ---------------------------
        // Reuses $monthData directly instead of re-querying with a
        // separate (previously inconsistent) set of filters.
        $monthlyCourierReport = collect($monthData)->map(fn ($data) => [
            'parcels' => $data['invoices'],
            'quantity' => $data['quantity'],
            'subtotal' => $data['subtotal'],
            'delivery' => $data['delivery'],
            'total' => $data['revenue'],
            'paid' => $data['paid'],
            'due' => $data['due'],
        ])->toArray();
 
        $monthlyInhouseReport = [
            'parcels' => $monthInhouse['invoices'],
            'quantity' => $monthInhouse['quantity'],
            'subtotal' => $monthInhouse['subtotal'],
            'delivery' => $monthInhouse['delivery'],
            'total' => $monthInhouse['revenue'],
            'paid' => $monthInhouse['paid'],
            'due' => $monthInhouse['due'],
        ];
 
        // ---------------- Payment method breakdown --------------------------
        $todayPaymentMethods = [];
        foreach ($this->paymentMethods as $method) {
            $query = ($todayBase())->where('payment_method', $method)->where('paid_amount', '>', 0);
            $todayPaymentMethods[$method] = [
                'transactions' => $query->count(),
                'total_paid' => $query->sum('paid_amount'),
            ];
        }
 
        $monthlyPaymentMethods = [];
        foreach ($this->paymentMethods as $method) {
            $query = ($monthBase())->where('payment_method', $method)->where('paid_amount', '>', 0);
            $monthlyPaymentMethods[$method] = [
                'transactions' => $query->count(),
                'total_paid' => $query->sum('paid_amount'),
            ];
        }
 
        // Uses the SAME $this->paymentMethods array as the breakdown above
        // (previously this whereIn used a different, mismatched array of
        // capitalized values).
        $todayPaymentDetails = ($todayBase())
            ->where('paid_amount', '>', 0)
            ->whereIn('payment_method', $this->paymentMethods)
            ->with(['creator', 'items', 'returnItems'])
            ->orderBy('paid_amount', 'desc')
            ->get();
 
        $monthlyPaymentDetails = ($monthBase())
            ->where('paid_amount', '>', 0)
            ->whereIn('payment_method', $this->paymentMethods)
            ->with(['creator', 'items', 'returnItems'])
            ->orderBy('paid_amount', 'desc')
            ->get();
 
        // ---------------- Team performance ------------------------------------
        $todayPerformance = User::getTopTeamMembersToday();
        $monthPerformance = User::getTopTeamMembersMonth();
 
        // ---------------- Attendance ---------------------------------------------
        $attendanceData = $this->getAttendanceMatrix();
        $attendanceMatrix = $attendanceData['matrix'];
        $daysInMonth = $attendanceData['daysInMonth'];
        $monthName = $attendanceData['monthName'];
        $attYear = $attendanceData['year'];
        $attMonth = $attendanceData['month'];
 
        $todayInvoicesuser = $todayInvoices;
        $monthlyInvoicesuser = $monthlyInvoices;
 
        return view('admin.dashboard', compact(
            'totalInvoices', 'daysInMonth', 'attendanceMatrix', 'monthName',
            'todayInvoicesuser', 'monthlyInvoicesuser', 'attYear', 'attMonth',
            'todayPerformance', 'monthPerformance',
            'todayPaymentMethods', 'monthlyPaymentMethods',
            'todayPaymentDetails', 'monthlyPaymentDetails',
            'monthlyCourierReport', 'monthlyInhouseReport',
            'todayPaidInvoices', 'creatorPaymentSummary',
            'totalPaidAmount', 'totalDueAmount', 'totalSubtotal', 'totalDelivery',
            'todayInvoices', 'todayRevenue', 'todayPaid', 'todayDue',
            'todaySubtotal', 'todayDelivery', 'todayQuantity',
            'monthlyInvoices', 'monthlyRevenue', 'monthlyPaid', 'monthlyDue',
            'monthlyPaidInvoices', 'monthlySubtotal', 'today', 'monthlyDelivery', 'monthlyQuantity',
            'topCreators', 'monthlyStats', 'last10Days',
            'hasFullAccess', 'adminhasFullAccess',
            'todayData', 'monthData', 'todayInhouse', 'monthInhouse',
            'todayPaidInvoice', 'topCreatorsMonth'
        ));
    }
 
    /**
     * Aggregate a confirmed-invoice query into the shared summary shape
     * used across every card/table on the dashboard. Quantity is always
     * items.quantity - returnItems.quantity, computed consistently.
     */
    private function buildSummary($query): array
    {
        $invoices = $query->with(['items', 'returnItems'])->get();
 
        $quantity = $invoices->sum(function ($invoice) {
            return $invoice->items->sum('quantity') - $invoice->returnItems->sum('quantity');
        });
 
        return [
            'invoices' => $invoices->count(),
            'quantity' => $quantity,
            'subtotal' => $invoices->sum('subtotal'),
            'delivery' => $invoices->sum('delivery_charge'),
            'revenue' => $invoices->sum('total'),
            'paid' => $invoices->sum('paid_amount'),
            'due' => $invoices->sum('due_amount'),
            'paidInvoices' => $invoices->where('paid_amount', '>', 0)->count(),
        ];
    }
 
    /**
     * Split a confirmed-invoice query (already scoped to a date range)
     * into [courier => summary, ...] plus an in-house summary. Any
     * courier not in $this->couriers is folded into an "Others" bucket
     * so that sum(courier breakdown) + in-house always equals the full
     * query's total — no more silently dropped invoices.
     *
     * @return array{0: array<string, array>, 1: array}
     */
    private function buildCourierBreakdown($query): array
    {
        $courierData = [];
 
        foreach ($this->couriers as $courier) {
            $summary = $this->buildSummary(
                (clone $query)->where('is_inhouse_sale', 0)->where('courier_name', $courier)
            );
            if ($summary['invoices'] > 0) {
                $courierData[$courier] = $summary;
            }
        }
 
        $othersSummary = $this->buildSummary(
            (clone $query)->where('is_inhouse_sale', 0)->where(function ($q) {
                $q->whereNull('courier_name')->orWhereNotIn('courier_name', $this->couriers);
            })
        );
        if ($othersSummary['invoices'] > 0) {
            $courierData['Others'] = $othersSummary;
        }
 
        $inhouseSummary = $this->buildSummary(
            (clone $query)->where('is_inhouse_sale', 1)
        );
 
        return [$courierData, $inhouseSummary];
    }
 
    /**
     * Shared "top creators" query for both the today and month views.
     * $isToday switches between DATE(invoice_date) = CURDATE() and an
     * invoice_date BETWEEN start/end range for the month view.
     */
    private function topCreatorsQuery($start, $end, bool $isToday)
    {
        $dateCondition = $isToday
            ? 'DATE(invoices.invoice_date) = CURDATE()'
            : 'invoices.invoice_date >= "' . $start->toDateString() . '" AND invoices.invoice_date <= "' . $end->toDateString() . '"';
 
        return User::select([
            'users.*',
            DB::raw("(SELECT COUNT(*) FROM invoices
                WHERE invoices.created_by = users.id
                AND {$dateCondition}
                AND invoices.status = 'confirmed'
                AND invoices.is_inhouse_sale = 0
                AND invoices.deleted_at IS NULL) as total_invoices"),
            DB::raw("(SELECT COALESCE(SUM(total), 0) FROM invoices
                WHERE invoices.created_by = users.id
                AND {$dateCondition}
                AND invoices.status = 'confirmed'
                AND invoices.is_inhouse_sale = 0
                AND invoices.deleted_at IS NULL) as total_amount"),
            DB::raw("(SELECT COALESCE(SUM(paid_amount), 0) FROM invoices
                WHERE invoices.created_by = users.id
                AND {$dateCondition}
                AND invoices.status = 'confirmed'
                AND invoices.is_inhouse_sale = 0
                AND invoices.deleted_at IS NULL) as total_paid"),
            DB::raw("(SELECT COALESCE(SUM(due_amount), 0) FROM invoices
                WHERE invoices.created_by = users.id
                AND {$dateCondition}
                AND invoices.status = 'confirmed'
                AND invoices.is_inhouse_sale = 0
                AND invoices.deleted_at IS NULL) as total_due"),
            DB::raw("(SELECT COALESCE(SUM(subtotal), 0) FROM invoices
                WHERE invoices.created_by = users.id
                AND {$dateCondition}
                AND invoices.status = 'confirmed'
                AND invoices.is_inhouse_sale = 0
                AND invoices.deleted_at IS NULL) as total_subtotal"),
            DB::raw("(SELECT COALESCE(SUM(delivery_charge), 0) FROM invoices
                WHERE invoices.created_by = users.id
                AND {$dateCondition}
                AND invoices.status = 'confirmed'
                AND invoices.is_inhouse_sale = 0
                AND invoices.deleted_at IS NULL) as total_delivery"),
            DB::raw("(SELECT COALESCE(SUM(items.quantity), 0) - COALESCE(SUM(returns.quantity), 0)
                FROM invoice_items items
                LEFT JOIN return_items returns ON returns.invoice_id = items.invoice_id
                WHERE items.invoice_id IN (
                    SELECT id FROM invoices
                    WHERE invoices.created_by = users.id
                    AND {$dateCondition}
                    AND invoices.status = 'confirmed'
                    AND invoices.is_inhouse_sale = 0
                    AND invoices.deleted_at IS NULL
                )) as total_quantity"),
            DB::raw("(SELECT COUNT(*) FROM invoices
                WHERE invoices.team_id = users.id
                AND {$dateCondition}
                AND invoices.status = 'confirmed'
                AND invoices.is_inhouse_sale = 0
                AND invoices.deleted_at IS NULL) as as_team_member"),
        ])
        ->having('total_invoices', '>', 0)
        ->orderBy('total_amount', 'desc');
    }
 
    /**
     * User-specific dashboard showing only their own performance
     */
    private function userDashboard($user)
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
 
        $userInvoices = Invoice::where('status', 'confirmed')
            ->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('team_id', $user->id);
            });
 
        $todayInvoicesuser = (clone $userInvoices)->whereDate('confirmed_at', $today)->count();
        $monthlyInvoicesuser = (clone $userInvoices)->where('confirmed_at', '>=', $startOfMonth)->count();
 
        $hasFullAccess = false;
        $adminhasFullAccess = false;
 
        return view('admin.dashboard', compact(
            'user',
            'todayInvoicesuser',
            'monthlyInvoicesuser',
            'hasFullAccess',
            'adminhasFullAccess'
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
public function showDashboard2()
{
    return view('admin.dashboard2', [
        'hasData'  => false,
        'fromDate' => null,
        'toDate'   => null,
    ]);
}

public function filterDashboard2(Request $request)
{
    $request->validate([
        'from_date' => 'required|date',
        'to_date'   => 'required|date|after_or_equal:from_date',
    ]);

    $fromDateStr = $request->from_date;
    $toDateStr   = $request->to_date;

    $fromDate = Carbon::parse($fromDateStr)->startOfDay();
    $toDate   = Carbon::parse($toDateStr)->endOfDay();

    $isSingleDay = $fromDateStr === $toDateStr;

    $couriers = ['Pathao', 'Steadfast', 'SA', 'SUNDORBAN', 'JANONI', 'REDEX'];

    $baseQuery = function () use ($fromDateStr, $toDateStr) {
        return Invoice::where('status', 'confirmed')
            ->whereDate('invoice_date', '>=', $fromDateStr)
            ->whereDate('invoice_date', '<=', $toDateStr);
    };

    // ---------- Range totals ----------
    $rangeCount    = (clone $baseQuery())->count();
    $rangeRevenue  = (clone $baseQuery())->sum('total');
    $rangePaid     = (clone $baseQuery())->sum('paid_amount');
    $rangeDue      = (clone $baseQuery())->sum('due_amount');
    $rangeSubtotal = (clone $baseQuery())->sum('subtotal');
    $rangeDelivery = (clone $baseQuery())->sum('delivery_charge');

    $rangeQuantity = (clone $baseQuery())
        ->with(['items', 'returnItems'])
        ->get()
        ->sum(fn($inv) => $inv->items->sum('quantity') - $inv->returnItems->sum('quantity'));

    // ---------- Courier-wise (colored card, like Today's Summary) ----------
    $rangeData = [];
    foreach ($couriers as $courier) {
        $invoices = (clone $baseQuery())
            ->where('courier_name', $courier)
            ->where('is_inhouse_sale', 0)
            ->with(['items', 'returnItems'])
            ->get();

        if ($invoices->count() > 0) {
            $rangeData[$courier] = [
                'invoices' => $invoices->count(),
                'revenue'  => $invoices->sum('total'),
                'paid'     => $invoices->sum('paid_amount'),
                'subtotal' => $invoices->sum('subtotal'),
                'delivery' => $invoices->sum('delivery_charge'),
                'quantity' => $invoices->sum(fn($inv) => $inv->items->sum('quantity') - $inv->returnItems->sum('quantity')),
            ];
        }
    }

    // ---------- In-house ----------
    $inhouseInvoices = (clone $baseQuery())
        ->where('is_inhouse_sale', true)
        ->with(['items', 'returnItems'])
        ->get();

    $rangeInhouse = [
        'invoices' => $inhouseInvoices->count(),
        'revenue'  => $inhouseInvoices->sum('total'),
        'paid'     => $inhouseInvoices->sum('paid_amount'),
        'subtotal' => $inhouseInvoices->sum('subtotal'),
        'delivery' => $inhouseInvoices->sum('delivery_charge'),
        'quantity' => $inhouseInvoices->sum(fn($inv) => $inv->items->sum('quantity') - $inv->returnItems->sum('quantity')),
    ];

    // ---------- Courier-wise report table (parcels/total/due) ----------
    $courierReport = [];
    foreach ($couriers as $courier) {
        $invoices = (clone $baseQuery())
            ->where('courier_name', $courier)
            ->with(['items', 'returnItems'])
            ->get();

        if ($invoices->count() > 0) {
            $courierReport[$courier] = [
                'parcels'  => $invoices->count(),
                'quantity' => $invoices->sum(fn($inv) => $inv->items->sum('quantity') - $inv->returnItems->sum('quantity')),
                'subtotal' => $invoices->sum('subtotal'),
                'delivery' => $invoices->sum('delivery_charge'),
                'total'    => $invoices->sum('total'),
                'paid'     => $invoices->sum('paid_amount'),
                'due'      => $invoices->sum('due_amount'),
            ];
        }
    }

    $inhouseReport = [
        'parcels'  => $inhouseInvoices->count(),
        'quantity' => $inhouseInvoices->sum(fn($inv) => $inv->items->sum('quantity') - $inv->returnItems->sum('quantity')),
        'subtotal' => $inhouseInvoices->sum('subtotal'),
        'delivery' => $inhouseInvoices->sum('delivery_charge'),
        'total'    => $inhouseInvoices->sum('total'),
        'paid'     => $inhouseInvoices->sum('paid_amount'),
        'due'      => $inhouseInvoices->sum('due_amount'),
    ];

    // ---------- Payments Transaction Details (invoice-wise, like Today's Payments) ----------
    $rangePaidInvoices = (clone $baseQuery())
        ->where('paid_amount', '>', 0)
        ->with(['creator', 'items', 'returnItems'])
        ->orderBy('paid_amount', 'desc')
        ->get();

    $creatorPaymentSummary = $rangePaidInvoices->groupBy('created_by')->map(function ($invoices) {
        $creator = $invoices->first()->creator;
        return [
            'creator_name'  => $creator ? $creator->name : 'Unknown',
            'invoice_count' => $invoices->count(),
            'total_paid'    => $invoices->sum('paid_amount'),
        ];
    })->sortByDesc('total_paid');

    // ---------- Day-wise breakdown ----------
    $dailyBreakdown = collect();
    $cursor = $fromDate->copy();
    while ($cursor->lte($toDate)) {
        $dayInvoices = Invoice::where('status', 'confirmed')
            ->whereDate('invoice_date', $cursor->toDateString())
            ->with(['items', 'returnItems'])
            ->get();

        $dailyBreakdown->push([
            'date'     => $cursor->format('D, M d Y'),
            'count'    => $dayInvoices->count(),
            'quantity' => $dayInvoices->sum(fn($inv) => $inv->items->sum('quantity') - $inv->returnItems->sum('quantity')),
            'subtotal' => $dayInvoices->sum('subtotal'),
            'delivery' => $dayInvoices->sum('delivery_charge'),
            'revenue'  => $dayInvoices->sum('total'),
            'paid'     => $dayInvoices->sum('paid_amount'),
            'due'      => $dayInvoices->sum('due_amount'),
        ]);

        $cursor->addDay();
    }

    // ---------- Team & Creator performance ----------
    $teamPerformance = User::getTopTeamMembersForRange($fromDateStr, $toDateStr);
    $topCreators     = User::getTopCreatorsForRange($fromDateStr, $toDateStr);

    // ---------- Payment methods ----------
    $paymentMethodKeys = ['bkash', 'bkash_personal', 'bank_transfer', 'cash'];
    $rangePaymentMethods = [];
    foreach ($paymentMethodKeys as $method) {
        $q = (clone $baseQuery())
            ->where('payment_method', $method)
            ->where('paid_amount', '>', 0);

        $rangePaymentMethods[$method] = [
            'transactions' => $q->count(),
            'total_paid'   => $q->sum('paid_amount'),
        ];
    }

    return view('admin.dashboard2', [
        'hasData'               => true,
        'isSingleDay'           => $isSingleDay,
        'fromDate'              => $fromDateStr,
        'toDate'                => $toDateStr,
        'rangeCount'            => $rangeCount,
        'rangeRevenue'          => $rangeRevenue,
        'rangePaid'             => $rangePaid,
        'rangeDue'              => $rangeDue,
        'rangeSubtotal'         => $rangeSubtotal,
        'rangeDelivery'         => $rangeDelivery,
        'rangeQuantity'         => $rangeQuantity,
        'rangeData'             => $rangeData,
        'rangeInhouse'          => $rangeInhouse,
        'courierReport'         => $courierReport,
        'inhouseReport'         => $inhouseReport,
        'rangePaidInvoices'     => $rangePaidInvoices,
        'creatorPaymentSummary' => $creatorPaymentSummary,
        'dailyBreakdown'        => $dailyBreakdown,
        'teamPerformance'       => $teamPerformance,
        'topCreators'           => $topCreators,
        'rangePaymentMethods'   => $rangePaymentMethods,
    ]);
}
}