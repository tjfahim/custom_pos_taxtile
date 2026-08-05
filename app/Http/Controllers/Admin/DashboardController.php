<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
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

    // Monthly stats for current year - FIXED GROUP BY ISSUE
    $monthlyStats = DB::table('invoices')
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
            DB::raw('COALESCE(SUM(items.quantity), 0) as total_quantity')
        )
        ->leftJoin('invoice_items as items', 'invoices.id', '=', 'items.invoice_id')
        ->whereYear('invoices.invoice_date', Carbon::now()->year)
        ->where('invoices.status', 'confirmed')
        ->whereNull('invoices.deleted_at')
        ->groupBy(DB::raw('YEAR(invoices.invoice_date)'), DB::raw('MONTH(invoices.invoice_date)'))
        ->orderBy('year')
        ->orderBy('month')
        ->get()
        ->keyBy('month');
    
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
        $dayInvoices = Invoice::where('status', 'confirmed')
            ->whereDate('invoice_date', $date)
            ->get();
        
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
            'quantity' => $dayInvoices->sum(function($invoice) {
                return $invoice->items->sum('quantity');
            }),
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
        'monthlySubtotal',
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
        'topCreatorsMonth'
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
    $totalRevenue = (clone $userInvoices)->sum('total');
    $totalPaid = (clone $userInvoices)->sum('paid_amount');
    $totalDue = (clone $userInvoices)->sum('due_amount');
    $totalSubtotal = (clone $userInvoices)->sum('subtotal');
    $totalDelivery = (clone $userInvoices)->sum('delivery_charge');
   
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
        'user',
        'totalRevenue',
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