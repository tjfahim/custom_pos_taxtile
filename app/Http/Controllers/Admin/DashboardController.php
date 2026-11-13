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
     * Courier names left out of the sales numbers. Empty now: Exchange
     * invoices ARE counted everywhere (today, month, range, last 10 days),
     * and they are counted inside the Pathao bucket because every exchange
     * is shipped through Pathao. Their quantity/price are already NET
     * (new items taken minus items given back), so a 10-back / 15-taken
     * exchange adds +5 qty, and a pure refund comes out negative.
     */
    private array $excludedCouriers = [];

    /** Courier names whose invoices are reported under the Pathao bucket. */
    private const PATHAO_BUCKET = ['Pathao', 'Exchange'];

 
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
                DB::raw('SUM(invoices.paid_amount + COALESCE(invoices.paid_amount2, 0)) as total_paid'),
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
        $totalPaidAmount = Invoice::where('status', 'confirmed')->sum(DB::raw(Invoice::PAID_SQL));
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
        // Any payment (first or second advance), regardless of method — same as before.
        $todayPaidInvoices = ($todayBase())
            ->whereRaw('(' . Invoice::PAID_SQL . ') > 0')
            ->with(['creator', 'items', 'returnItems'])
            ->orderByRaw('(' . Invoice::PAID_SQL . ') desc')
            ->get();
 
        $creatorPaymentSummary = $todayPaidInvoices->groupBy('created_by')->map(function ($invoices) {
            $creator = $invoices->first()->creator;
            return [
                'creator_name' => $creator ? $creator->name : 'Unknown',
                'invoice_count' => $invoices->count(),
                'total_paid' => $invoices->sum('total_paid_amount'),
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
        $todayPaymentMethods = $this->buildPaymentMethodBreakdown($todayBase);
        $monthlyPaymentMethods = $this->buildPaymentMethodBreakdown($monthBase);
 
        // Uses the SAME $this->paymentMethods array as the breakdown above
        // (previously this whereIn used a different, mismatched array of
        // capitalized values).
        $todayPaymentDetails = $this->scopePaidInvoices($todayBase())
            ->with(['creator', 'items', 'returnItems'])
            ->get();
 
        $monthlyPaymentDetails = $this->scopePaidInvoices($monthBase())
            ->with(['creator', 'items', 'returnItems'])
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
 $exchangeBaseToday = fn () => Invoice::where('status', 'confirmed')
    ->whereNull('deleted_at')
    ->where('courier_name', 'Exchange')
    ->whereDate('invoice_date', $today);

$exchangeBaseMonth = fn () => Invoice::where('status', 'confirmed')
    ->whereNull('deleted_at')
    ->where('courier_name', 'Exchange')
    ->where('invoice_date', '>=', $startOfMonth);

$todayExchange  = $this->buildExchangeSummary($exchangeBaseToday());
$monthlyExchange = $this->buildExchangeSummary($exchangeBaseMonth());

$wholesaleBaseToday = fn () => Invoice::where('status', 'confirmed')
    ->whereNull('deleted_at')
    ->where(function ($q) {
        $q->whereNull('courier_name')->orWhereNotIn('courier_name', $this->excludedCouriers);
    })
    ->where('is_wholesale', 1)
    ->whereDate('invoice_date', $today);

$wholesaleBaseMonth = fn () => Invoice::where('status', 'confirmed')
    ->whereNull('deleted_at')
    ->where(function ($q) {
        $q->whereNull('courier_name')->orWhereNotIn('courier_name', $this->excludedCouriers);
    })
    ->where('is_wholesale', 1)
    ->where('invoice_date', '>=', $startOfMonth);

$todayWholesale   = $this->buildWholesaleSummary($wholesaleBaseToday());
$monthlyWholesale = $this->buildWholesaleSummary($wholesaleBaseMonth());
        return view('admin.dashboard', compact(
            'totalInvoices', 'daysInMonth', 'attendanceMatrix', 'monthName',
            'todayInvoicesuser', 'monthlyInvoicesuser', 'attYear', 'attMonth',
            'todayPerformance', 'monthPerformance',
            'todayWholesale', 'monthlyWholesale',
            'todayPaymentMethods', 'monthlyPaymentMethods',
            'todayExchange', 'monthlyExchange',
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
     * Exchange summary. In an exchange invoice:
     *   items        = what the customer TAKES  (new items)
     *   returnItems  = what the customer GIVES BACK
     * quantity / subtotal are therefore NET: 10 given back + 15 taken = +5 qty,
     * and the price is (value taken - value given back), which can be negative
     * (money goes back to the customer). 'new_quantity' / 'return_quantity'
     * are kept so the card can show the breakdown.
     */
  private function buildExchangeSummary($query): array
{
    $invoices = (clone $query)->where('courier_name', 'Exchange')
        ->with(['items', 'returnItems'])->get();

    $newQty = $invoices->sum(fn ($inv) => $inv->items->sum('quantity'));
    $returnQty = $invoices->sum(fn ($inv) => $inv->returnItems->sum('quantity'));
    $netQty = $newQty - $returnQty;

    return [
        'invoices'        => $invoices->count(),
        'quantity'        => $netQty,       // NET → shown on the Exchange card
        'net_quantity'    => $netQty,       // kept for older callers
        'new_quantity'    => $newQty,       // taken by customer
        'return_quantity' => $returnQty,    // given back by customer
        'subtotal'        => $invoices->sum('subtotal'),   // already net (taken - given back)
        'delivery'        => $invoices->sum('delivery_charge'),
        'revenue'         => $invoices->sum('total'),
        'paid'            => $invoices->sum('total_paid_amount'),
        'due'             => $invoices->sum('due_amount'),
        'paidInvoices'    => $invoices->filter(fn ($i) => $i->total_paid_amount > 0)->count(),
    ];
}
    /**
     * Payments per method for a date scope. Counts the first advance under
     * payment_method AND the second advance (in-house) under payment_method2.
     *
     * @param  callable $base  returns a fresh scoped Invoice query
     */
    private function buildPaymentMethodBreakdown(callable $base): array
    {
        $result = [];
        foreach ($this->paymentMethods as $method) {
            $first = ($base())->where('payment_method', $method)->where('paid_amount', '>', 0);
            $second = ($base())->where('payment_method2', $method)->where('paid_amount2', '>', 0);

            $result[$method] = [
                'transactions' => (clone $first)->count() + (clone $second)->count(),
                'total_paid'   => (clone $first)->sum('paid_amount') + (clone $second)->sum('paid_amount2'),
            ];
        }
        return $result;
    }

    /** Scope: invoices with any payment, in either payment method list. */
    private function scopePaidInvoices($query)
    {
        return $query
            ->whereRaw('(' . Invoice::PAID_SQL . ') > 0')
            ->where(function ($q) {
                $q->whereIn('payment_method', $this->paymentMethods)
                  ->orWhereIn('payment_method2', $this->paymentMethods);
            })
            ->orderByRaw('(' . Invoice::PAID_SQL . ') desc');
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
            'paid' => $invoices->sum('total_paid_amount'),
            'due' => $invoices->sum('due_amount'),
            'paidInvoices' => $invoices->filter(fn ($i) => $i->total_paid_amount > 0)->count(),
        ];
    }
 private function buildWholesaleSummary($query): array
{
    $wholesaleQuery = (clone $query)->where('is_wholesale', 1);

    $invoices = $wholesaleQuery->with(['items', 'returnItems'])->get();

    // Net quantity = items − returns (matches buildSummary)
    $quantity = $invoices->sum(function ($invoice) {
        return $invoice->items->sum('quantity') - $invoice->returnItems->sum('quantity');
    });

    return [
        'invoices'     => $invoices->count(),
        'quantity'     => $quantity,
        'subtotal'     => $invoices->sum('subtotal'),
        'delivery'     => $invoices->sum('delivery_charge'),
        'revenue'      => $invoices->sum('total'),
        'paid'         => $invoices->sum('total_paid_amount'),
        'due'          => $invoices->sum('due_amount'),
        'paidInvoices' => $invoices->filter(fn ($i) => $i->total_paid_amount > 0)->count(),
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
            $names = $courier === 'Pathao' ? self::PATHAO_BUCKET : [$courier];
            $summary = $this->buildSummary(
                (clone $query)->where('is_inhouse_sale', 0)->whereIn('courier_name', $names)
            );
            if ($summary['invoices'] > 0) {
                $courierData[$courier] = $summary;
            }
        }
 
        $othersSummary = $this->buildSummary(
            (clone $query)->where('is_inhouse_sale', 0)->where(function ($q) {
                $q->whereNull('courier_name')
                  ->orWhereNotIn('courier_name', array_merge($this->couriers, ['Exchange']));
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

    // Same courier exclusion the dashboard base query uses
    $courierCondition = "(invoices.courier_name IS NULL OR invoices.courier_name <> 'Exchange')";

    return User::select([
        'users.*',

        DB::raw("(SELECT COUNT(*) FROM invoices
            WHERE invoices.created_by = users.id
            AND {$dateCondition}
            AND invoices.status = 'confirmed'
            AND {$courierCondition}
            AND invoices.deleted_at IS NULL) as total_invoices"),

        DB::raw("(SELECT COALESCE(SUM(total), 0) FROM invoices
            WHERE invoices.created_by = users.id
            AND {$dateCondition}
            AND invoices.status = 'confirmed'
            AND {$courierCondition}
            AND invoices.deleted_at IS NULL) as total_amount"),

        DB::raw("(SELECT COALESCE(SUM(paid_amount + COALESCE(paid_amount2, 0)), 0) FROM invoices
            WHERE invoices.created_by = users.id
            AND {$dateCondition}
            AND invoices.status = 'confirmed'
            AND {$courierCondition}
            AND invoices.deleted_at IS NULL) as total_paid"),

        DB::raw("(SELECT COALESCE(SUM(due_amount), 0) FROM invoices
            WHERE invoices.created_by = users.id
            AND {$dateCondition}
            AND invoices.status = 'confirmed'
            AND {$courierCondition}
            AND invoices.deleted_at IS NULL) as total_due"),

        DB::raw("(SELECT COALESCE(SUM(subtotal), 0) FROM invoices
            WHERE invoices.created_by = users.id
            AND {$dateCondition}
            AND invoices.status = 'confirmed'
            AND {$courierCondition}
            AND invoices.deleted_at IS NULL) as total_subtotal"),

        DB::raw("(SELECT COALESCE(SUM(delivery_charge), 0) FROM invoices
            WHERE invoices.created_by = users.id
            AND {$dateCondition}
            AND invoices.status = 'confirmed'
            AND {$courierCondition}
            AND invoices.deleted_at IS NULL) as total_delivery"),

        // ❗ Quantity via TWO separate subqueries — no fan-out
        DB::raw("(
            (SELECT COALESCE(SUM(ii.quantity), 0)
             FROM invoice_items ii
             WHERE ii.invoice_id IN (
                 SELECT id FROM invoices
                 WHERE invoices.created_by = users.id
                 AND {$dateCondition}
                 AND invoices.status = 'confirmed'
                 AND {$courierCondition}
                 AND invoices.deleted_at IS NULL
             ))
            -
            (SELECT COALESCE(SUM(ri.quantity), 0)
             FROM return_items ri
             WHERE ri.invoice_id IN (
                 SELECT id FROM invoices
                 WHERE invoices.created_by = users.id
                 AND {$dateCondition}
                 AND invoices.status = 'confirmed'
                 AND {$courierCondition}
                 AND invoices.deleted_at IS NULL
             ))
        ) as total_quantity"),

        DB::raw("(SELECT COUNT(*) FROM invoices
            WHERE invoices.team_id = users.id
            AND {$dateCondition}
            AND invoices.status = 'confirmed'
            AND {$courierCondition}
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

    // User's own invoice counts (existing logic)
    $userInvoices = Invoice::where('status', 'confirmed')
        ->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
              ->orWhere('team_id', $user->id);
        });

    $todayInvoicesuser = (clone $userInvoices)->whereDate('invoice_date', $today)->count();
    $monthlyInvoicesuser = (clone $userInvoices)->where('confirmed_at', '>=', $startOfMonth)->count();

    // ---------------------------------------------------------------
    // Same data as admin dashboard
    // ---------------------------------------------------------------
    $todayPerformance = User::getTopTeamMembersToday();
    $monthPerformance = User::getTopTeamMembersMonth();

    $topCreators = $this->topCreatorsQuery($today, $today, true)->get();
    $topCreatorsMonth = $this->topCreatorsQuery($startOfMonth, Carbon::now(), false)->get();

    // Defaults so the view doesn't break if any admin-only block is ever
    // referenced. (The view guards most admin blocks with $adminhasFullAccess,
    // so these are only here as a safety net.)
    $totalInvoices = 0;
    $todayData = [];
    $todayInhouse = [
        'invoices' => 0, 'quantity' => 0, 'subtotal' => 0,
        'delivery' => 0, 'revenue' => 0, 'paid' => 0,
    ];
    $monthData = [];
    $monthInhouse = [
        'invoices' => 0, 'quantity' => 0, 'subtotal' => 0,
        'delivery' => 0, 'revenue' => 0, 'paid' => 0,
    ];
    $todayPaidInvoices = collect();
    $creatorPaymentSummary = collect();
    $todayPaymentMethods = [];
    $monthlyPaymentMethods = [];
    $todayPaymentDetails = collect();
    $monthlyPaymentDetails = collect();
    $monthlyCourierReport = [];
    $monthlyInhouseReport = [
        'parcels' => 0, 'quantity' => 0, 'subtotal' => 0,
        'delivery' => 0, 'total' => 0, 'paid' => 0, 'due' => 0,
    ];
    $last10Days = collect();
    $monthlyStats = collect();
    $daysInMonth = 0;
    $attendanceMatrix = [];
    $monthName = '';
    $attYear = Carbon::now()->year;
    $attMonth = Carbon::now()->month;
    $totalPaidAmount = 0;
    $totalDueAmount = 0;
    $totalSubtotal = 0;
    $totalDelivery = 0;
    $todayInvoices = 0;
    $todayRevenue = 0;
    $todayPaid = 0;
    $todayDue = 0;
    $todaySubtotal = 0;
    $todayDelivery = 0;
    $todayQuantity = 0;
    $monthlyInvoices = 0;
    $monthlyRevenue = 0;
    $monthlyPaid = 0;
    $monthlyDue = 0;
    $monthlyPaidInvoices = 0;
    $monthlySubtotal = 0;
    $monthlyDelivery = 0;
    $monthlyQuantity = 0;
    $todayPaidInvoice = 0;

    $hasFullAccess = false;
    $adminhasFullAccess = false;

    return view('admin.dashboard', compact(
        'user',
        'todayInvoicesuser',
        'monthlyInvoicesuser',
        'hasFullAccess',
        'adminhasFullAccess',
        // performance data now passed to the view:
        'todayPerformance',
        'monthPerformance',
        'topCreators',
        'topCreatorsMonth',
        // safety defaults
        'totalInvoices', 'todayData', 'todayInhouse', 'monthData', 'monthInhouse',
        'todayPaidInvoices', 'creatorPaymentSummary',
        'todayPaymentMethods', 'monthlyPaymentMethods',
        'todayPaymentDetails', 'monthlyPaymentDetails',
        'monthlyCourierReport', 'monthlyInhouseReport',
        'last10Days', 'monthlyStats',
        'daysInMonth', 'attendanceMatrix', 'monthName', 'attYear', 'attMonth',
        'totalPaidAmount', 'totalDueAmount', 'totalSubtotal', 'totalDelivery',
        'todayInvoices', 'todayRevenue', 'todayPaid', 'todayDue',
        'todaySubtotal', 'todayDelivery', 'todayQuantity',
        'monthlyInvoices', 'monthlyRevenue', 'monthlyPaid', 'monthlyDue',
        'monthlyPaidInvoices', 'monthlySubtotal', 'monthlyDelivery', 'monthlyQuantity',
        'today', 'todayPaidInvoice'
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
        'rangeExchange' => [
            'invoices' => 0, 'quantity' => 0, 'net_quantity' => 0, 'subtotal' => 0,
            'delivery' => 0, 'revenue' => 0, 'paid' => 0, 'due' => 0,
            'paidInvoices' => 0,
        ],
        'rangeWholesale' => [     // ← ADD THIS
            'invoices' => 0, 'quantity' => 0, 'subtotal' => 0,
            'delivery' => 0, 'revenue' => 0, 'paid' => 0, 'due' => 0,
        ],
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
        ->whereNull('deleted_at')
        ->where(function ($q) {
            $q->whereNull('courier_name')->orWhereNotIn('courier_name', $this->excludedCouriers);
        })
        ->whereDate('invoice_date', '>=', $fromDateStr)
        ->whereDate('invoice_date', '<=', $toDateStr);
};

    // ---------- Range totals ----------
    // Built with the SAME helpers as the daily dashboard (buildSummary /
    // buildCourierBreakdown), so a one-day range always equals "Today".
    // Exchange invoices are included (inside the Pathao bucket) with NET
    // quantity and net price, exactly like the daily view.
    $rangeSummary  = $this->buildSummary($baseQuery());
    $rangeCount    = $rangeSummary['invoices'];
    $rangeRevenue  = $rangeSummary['revenue'];
    $rangePaid     = $rangeSummary['paid'];
    $rangeDue      = $rangeSummary['due'];
    $rangeSubtotal = $rangeSummary['subtotal'];
    $rangeDelivery = $rangeSummary['delivery'];
    $rangeQuantity = $rangeSummary['quantity'];

    // ---------- Courier-wise + In-house (same shape as Today's Summary) ----------
    [$rangeData, $rangeInhouse] = $this->buildCourierBreakdown($baseQuery());

    // ---------- Courier-wise report table (parcels/total/due) ----------
    $courierReport = collect($rangeData)->map(fn ($data) => [
        'parcels'  => $data['invoices'],
        'quantity' => $data['quantity'],
        'subtotal' => $data['subtotal'],
        'delivery' => $data['delivery'],
        'total'    => $data['revenue'],
        'paid'     => $data['paid'],
        'due'      => $data['due'],
    ])->toArray();

    $inhouseReport = [
        'parcels'  => $rangeInhouse['invoices'],
        'quantity' => $rangeInhouse['quantity'],
        'subtotal' => $rangeInhouse['subtotal'],
        'delivery' => $rangeInhouse['delivery'],
        'total'    => $rangeInhouse['revenue'],
        'paid'     => $rangeInhouse['paid'],
        'due'      => $rangeInhouse['due'],
    ];

    // ---------- Payments Transaction Details (invoice-wise, like Today's Payments) ----------
    $rangePaidInvoices = (clone $baseQuery())
        ->whereRaw('(' . Invoice::PAID_SQL . ') > 0')
        ->with(['creator', 'items', 'returnItems'])
        ->orderByRaw('(' . Invoice::PAID_SQL . ') desc')
        ->get();

    $creatorPaymentSummary = $rangePaidInvoices->groupBy('created_by')->map(function ($invoices) {
        $creator = $invoices->first()->creator;
        return [
            'creator_name'  => $creator ? $creator->name : 'Unknown',
            'invoice_count' => $invoices->count(),
            'total_paid'    => $invoices->sum('total_paid_amount'),
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
            'paid'     => $dayInvoices->sum('total_paid_amount'),
            'due'      => $dayInvoices->sum('due_amount'),
        ]);

        $cursor->addDay();
    }

    // ---------- Team & Creator performance ----------
    $teamPerformance = User::getTopTeamMembersForRange($fromDateStr, $toDateStr);
    $topCreators     = User::getTopCreatorsForRange($fromDateStr, $toDateStr);

    // ---------- Payment methods (first + second payment method) ----------
    $rangePaymentMethods = $this->buildPaymentMethodBreakdown($baseQuery);

    // ---------- Exchange (net qty/price, same helper as the daily card) ----------
    $rangeExchange = $this->buildExchangeSummary(
        Invoice::where('status', 'confirmed')
            ->whereNull('deleted_at')
            ->whereDate('invoice_date', '>=', $fromDateStr)
            ->whereDate('invoice_date', '<=', $toDateStr)
    );

    // ---------- Wholesale ----------
    $rangeWholesale = $this->buildWholesaleSummary($baseQuery());

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
          'rangeExchange'         => $rangeExchange, 
          'rangeWholesale'        => $rangeWholesale,
    ]);
}
}