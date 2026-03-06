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
    $startOfWeek = Carbon::now()->startOfWeek();
    $startOfMonth = Carbon::now()->startOfMonth();
    $startOfYear = Carbon::now()->startOfYear();
    
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
    
    // Total counts (only confirmed invoices)
    $totalInvoices = Invoice::where('status', 'confirmed')->count();
    $totalCustomers = Customer::count();
    $totalUsers = User::count();
    $totalRevenue = Invoice::where('status', 'confirmed')->sum('total');
    $totalPaidAmount = Invoice::where('status', 'confirmed')->sum('paid_amount');
    $totalDueAmount = Invoice::where('status', 'confirmed')->sum('due_amount');
    $totalSubtotal = Invoice::where('status', 'confirmed')->sum('subtotal');
    $totalDelivery = Invoice::where('status', 'confirmed')->sum('delivery_charge');
    $totalQuantity = Invoice::where('status', 'confirmed')
        ->with('items')
        ->get()
        ->sum(function($invoice) {
            return $invoice->items->sum('quantity');
        });
    
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
    $todayDue = Invoice::where('status', 'confirmed')
        ->whereDate('invoice_date', $today)
        ->sum('due_amount');
    $todaySubtotal = Invoice::where('status', 'confirmed')
        ->whereDate('invoice_date', $today)
        ->sum('subtotal');
    $todayDelivery = Invoice::where('status', 'confirmed')
        ->whereDate('invoice_date', $today)
        ->sum('delivery_charge');
    $todayQuantity = Invoice::where('status', 'confirmed')
        ->whereDate('invoice_date', $today)
        ->with('items')
        ->get()
        ->sum(function($invoice) {
            return $invoice->items->sum('quantity');
        });
    
    // This Week counts (only confirmed invoices)
    $weekInvoices = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfWeek)
        ->count();
    $weekRevenue = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfWeek)
        ->sum('total');
    $weekPaid = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfWeek)
        ->sum('paid_amount');
    $weekDue = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfWeek)
        ->sum('due_amount');
    $weekSubtotal = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfWeek)
        ->sum('subtotal');
    $weekDelivery = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfWeek)
        ->sum('delivery_charge');
    $weekQuantity = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfWeek)
        ->with('items')
        ->get()
        ->sum(function($invoice) {
            return $invoice->items->sum('quantity');
        });
    
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
    
    // This Year counts (only confirmed invoices)
    $yearlyInvoices = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfYear)
        ->count();
    $yearlyRevenue = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfYear)
        ->sum('total');
    $yearlyPaid = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfYear)
        ->sum('paid_amount');
    $yearlyDue = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfYear)
        ->sum('due_amount');
    $yearlySubtotal = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfYear)
        ->sum('subtotal');
    $yearlyDelivery = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfYear)
        ->sum('delivery_charge');
    $yearlyQuantity = Invoice::where('status', 'confirmed')
        ->where('invoice_date', '>=', $startOfYear)
        ->with('items')
        ->get()
        ->sum(function($invoice) {
            return $invoice->items->sum('quantity');
        });
    
    // Status counts (include all statuses for reference)
    $invoiceStatusCounts = Invoice::select('status', DB::raw('count(*) as count'))
        ->groupBy('status')
        ->pluck('count', 'status');
        
    $paymentStatusCounts = Invoice::select('payment_status', DB::raw('count(*) as count'))
        ->groupBy('payment_status')
        ->pluck('count', 'payment_status');
    
    // Recent invoices (only confirmed)
    $recentInvoices = Invoice::with(['customer', 'creator'])
        ->where('status', 'confirmed')
        ->latest()
        ->limit(10)
        ->get();
    
    // Top customers by total spent (only confirmed invoices)
    $topCustomers = Customer::select([
            'customers.*',
            DB::raw('(SELECT COUNT(*) FROM invoices WHERE invoices.customer_id = customers.id AND invoices.status = "confirmed") as invoices_count'),
            DB::raw('(SELECT COALESCE(SUM(total), 0) FROM invoices WHERE invoices.customer_id = customers.id AND invoices.status = "confirmed") as invoices_sum_total'),
            DB::raw('(SELECT COALESCE(SUM(paid_amount), 0) FROM invoices WHERE invoices.customer_id = customers.id AND invoices.status = "confirmed") as invoices_sum_paid'),
            DB::raw('(SELECT COALESCE(SUM(due_amount), 0) FROM invoices WHERE invoices.customer_id = customers.id AND invoices.status = "confirmed") as invoices_sum_due'),
            DB::raw('(SELECT COALESCE(SUM(subtotal), 0) FROM invoices WHERE invoices.customer_id = customers.id AND invoices.status = "confirmed") as invoices_sum_subtotal'),
            DB::raw('(SELECT COALESCE(SUM(delivery_charge), 0) FROM invoices WHERE invoices.customer_id = customers.id AND invoices.status = "confirmed") as invoices_sum_delivery'),
            DB::raw('(SELECT COALESCE(SUM(quantity), 0) FROM invoice_items WHERE invoice_items.invoice_id IN (SELECT id FROM invoices WHERE invoices.customer_id = customers.id AND invoices.status = "confirmed")) as total_quantity')
        ])
        ->orderBy('invoices_sum_total', 'desc')
        ->limit(5)
        ->get();
    
     // Top customers by total spent (only confirmed invoices)
    $topCustomers = Customer::select([
            'customers.*',
            DB::raw('(SELECT COUNT(*) FROM invoices WHERE invoices.customer_id = customers.id AND invoices.status = "confirmed") as invoices_count'),
            DB::raw('(SELECT COALESCE(SUM(total), 0) FROM invoices WHERE invoices.customer_id = customers.id AND invoices.status = "confirmed") as invoices_sum_total'),
            DB::raw('(SELECT COALESCE(SUM(paid_amount), 0) FROM invoices WHERE invoices.customer_id = customers.id AND invoices.status = "confirmed") as invoices_sum_paid'),
            DB::raw('(SELECT COALESCE(SUM(due_amount), 0) FROM invoices WHERE invoices.customer_id = customers.id AND invoices.status = "confirmed") as invoices_sum_due'),
            DB::raw('(SELECT COALESCE(SUM(subtotal), 0) FROM invoices WHERE invoices.customer_id = customers.id AND invoices.status = "confirmed") as invoices_sum_subtotal'),
            DB::raw('(SELECT COALESCE(SUM(delivery_charge), 0) FROM invoices WHERE invoices.customer_id = customers.id AND invoices.status = "confirmed") as invoices_sum_delivery'),
            DB::raw('(SELECT COALESCE(SUM(quantity), 0) FROM invoice_items WHERE invoice_items.invoice_id IN (SELECT id FROM invoices WHERE invoices.customer_id = customers.id AND invoices.status = "confirmed")) as total_quantity')
        ])
        ->orderBy('invoices_sum_total', 'desc')
        ->limit(5)
        ->get();
    
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
    return view('admin.dashboard', compact(
        'totalInvoices',
        'totalCustomers',
        'totalUsers',
        'totalRevenue',
        'totalPaidAmount',
        'totalDueAmount',
        'totalSubtotal',
        'totalDelivery',
        'totalQuantity',
        'todayInvoices',
        'todayRevenue',
        'todayPaid',
        'todayDue',
        'todaySubtotal',
        'todayDelivery',
        'todayQuantity',
        'weekInvoices',
        'weekRevenue',
        'weekPaid',
        'weekDue',
        'weekSubtotal',
        'weekDelivery',
        'weekQuantity',
        'monthlyInvoices',
        'monthlyRevenue',
        'monthlyPaid',
        'monthlyDue',
        'monthlySubtotal',
        'monthlyDelivery',
        'monthlyQuantity',
        'yearlyInvoices',
        'yearlyRevenue',
        'yearlyPaid',
        'yearlyDue',
        'yearlySubtotal',
        'yearlyDelivery',
        'yearlyQuantity',
        'invoiceStatusCounts',
        'paymentStatusCounts',
        'recentInvoices',
        'topCustomers',
        'topCreators',
        'monthlyStats',
        'last10Days',
        'hasFullAccess'
    ));
}
    
    /**
     * User-specific dashboard showing only their own performance
     */
   
private function userDashboard($user)
{
    // Date filters
    $today = Carbon::today();
    $startOfWeek = Carbon::now()->startOfWeek();
    $startOfMonth = Carbon::now()->startOfMonth();
    $startOfYear = Carbon::now()->startOfYear();
    
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
    
    // This Week stats - Use confirmed_at for date filtering
    $weekInvoices = (clone $userInvoices)->where('confirmed_at', '>=', $startOfWeek)->count();
    $weekRevenue = (clone $userInvoices)->where('confirmed_at', '>=', $startOfWeek)->sum('total');
    $weekPaid = (clone $userInvoices)->where('confirmed_at', '>=', $startOfWeek)->sum('paid_amount');
    $weekDue = (clone $userInvoices)->where('confirmed_at', '>=', $startOfWeek)->sum('due_amount');
    $weekSubtotal = (clone $userInvoices)->where('confirmed_at', '>=', $startOfWeek)->sum('subtotal');
    $weekDelivery = (clone $userInvoices)->where('confirmed_at', '>=', $startOfWeek)->sum('delivery_charge');
    $weekQuantity = (clone $userInvoices)
        ->where('confirmed_at', '>=', $startOfWeek)
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
    
    // This Year stats - Use confirmed_at for date filtering
    $yearlyInvoices = (clone $userInvoices)->where('confirmed_at', '>=', $startOfYear)->count();
    $yearlyRevenue = (clone $userInvoices)->where('confirmed_at', '>=', $startOfYear)->sum('total');
    $yearlyPaid = (clone $userInvoices)->where('confirmed_at', '>=', $startOfYear)->sum('paid_amount');
    $yearlyDue = (clone $userInvoices)->where('confirmed_at', '>=', $startOfYear)->sum('due_amount');
    $yearlySubtotal = (clone $userInvoices)->where('confirmed_at', '>=', $startOfYear)->sum('subtotal');
    $yearlyDelivery = (clone $userInvoices)->where('confirmed_at', '>=', $startOfYear)->sum('delivery_charge');
    $yearlyQuantity = (clone $userInvoices)
        ->where('confirmed_at', '>=', $startOfYear)
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
    
    // Recent invoices (user's only) - ordered by confirmed_at
    $recentInvoices = (clone $userInvoices)
        ->with(['customer'])
        ->orderBy('confirmed_at', 'desc')
        ->limit(10)
        ->get();
    
    // Payment status counts for user (only confirmed)
    $paymentStatusCounts = (clone $userInvoices)
        ->select('payment_status', DB::raw('count(*) as count'))
        ->groupBy('payment_status')
        ->pluck('count', 'payment_status');
    
    // Set hasFullAccess to false
    $hasFullAccess = false;
    
    return view('admin.dashboard', compact(
        'user',
        'totalInvoices',
        'totalRevenue',
        'totalPaid',
        'totalDue',
        'totalSubtotal',
        'totalDelivery',
        'totalQuantity',
        'todayInvoices',
        'todayRevenue',
        'todayPaid',
        'todayDue',
        'todaySubtotal',
        'todayDelivery',
        'todayQuantity',
        'weekInvoices',
        'weekRevenue',
        'weekPaid',
        'weekDue',
        'weekSubtotal',
        'weekDelivery',
        'weekQuantity',
        'monthlyInvoices',
        'monthlyRevenue',
        'monthlyPaid',
        'monthlyDue',
        'monthlySubtotal',
        'monthlyDelivery',
        'monthlyQuantity',
        'yearlyInvoices',
        'yearlyRevenue',
        'yearlyPaid',
        'yearlyDue',
        'yearlySubtotal',
        'yearlyDelivery',
        'yearlyQuantity',
        'monthlyStats',
        'recentInvoices',
        'paymentStatusCounts',
        'hasFullAccess'
    ));
}
}