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
            ->whereNull('invoices.deleted_at')
            ->groupBy(DB::raw('YEAR(invoices.invoice_date)'), DB::raw('MONTH(invoices.invoice_date)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->keyBy('month');
        
        // Total counts
        $totalInvoices = Invoice::count();
        $totalCustomers = Customer::count();
        $totalUsers = User::count();
        $totalRevenue = Invoice::sum('total');
        $totalPaidAmount = Invoice::sum('paid_amount');
        $totalDueAmount = Invoice::sum('due_amount');
        $totalSubtotal = Invoice::sum('subtotal');
        $totalDelivery = Invoice::sum('delivery_charge');
        $totalQuantity = Invoice::with('items')
            ->get()
            ->sum(function($invoice) {
                return $invoice->items->sum('quantity');
            });
        
        // Today's counts
        $todayInvoices = Invoice::whereDate('invoice_date', $today)->count();
        $todayRevenue = Invoice::whereDate('invoice_date', $today)->sum('total');
        $todayPaid = Invoice::whereDate('invoice_date', $today)->sum('paid_amount');
        $todayDue = Invoice::whereDate('invoice_date', $today)->sum('due_amount');
        $todaySubtotal = Invoice::whereDate('invoice_date', $today)->sum('subtotal');
        $todayDelivery = Invoice::whereDate('invoice_date', $today)->sum('delivery_charge');
        $todayQuantity = Invoice::whereDate('invoice_date', $today)
            ->with('items')
            ->get()
            ->sum(function($invoice) {
                return $invoice->items->sum('quantity');
            });
        
        // This Week counts
        $weekInvoices = Invoice::where('invoice_date', '>=', $startOfWeek)->count();
        $weekRevenue = Invoice::where('invoice_date', '>=', $startOfWeek)->sum('total');
        $weekPaid = Invoice::where('invoice_date', '>=', $startOfWeek)->sum('paid_amount');
        $weekDue = Invoice::where('invoice_date', '>=', $startOfWeek)->sum('due_amount');
        $weekSubtotal = Invoice::where('invoice_date', '>=', $startOfWeek)->sum('subtotal');
        $weekDelivery = Invoice::where('invoice_date', '>=', $startOfWeek)->sum('delivery_charge');
        $weekQuantity = Invoice::where('invoice_date', '>=', $startOfWeek)
            ->with('items')
            ->get()
            ->sum(function($invoice) {
                return $invoice->items->sum('quantity');
            });
        
        // Last 7 days daily breakdown
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dayInvoices = Invoice::whereDate('invoice_date', $date)->get();
            
            $last7Days->push([
                'date' => $date->format('D, M d'),
                'day' => $date->format('d'),
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
        
        // This Month counts
        $monthlyInvoices = Invoice::where('invoice_date', '>=', $startOfMonth)->count();
        $monthlyRevenue = Invoice::where('invoice_date', '>=', $startOfMonth)->sum('total');
        $monthlyPaid = Invoice::where('invoice_date', '>=', $startOfMonth)->sum('paid_amount');
        $monthlyDue = Invoice::where('invoice_date', '>=', $startOfMonth)->sum('due_amount');
        $monthlySubtotal = Invoice::where('invoice_date', '>=', $startOfMonth)->sum('subtotal');
        $monthlyDelivery = Invoice::where('invoice_date', '>=', $startOfMonth)->sum('delivery_charge');
        $monthlyQuantity = Invoice::where('invoice_date', '>=', $startOfMonth)
            ->with('items')
            ->get()
            ->sum(function($invoice) {
                return $invoice->items->sum('quantity');
            });
        
        // This Year counts
        $yearlyInvoices = Invoice::where('invoice_date', '>=', $startOfYear)->count();
        $yearlyRevenue = Invoice::where('invoice_date', '>=', $startOfYear)->sum('total');
        $yearlyPaid = Invoice::where('invoice_date', '>=', $startOfYear)->sum('paid_amount');
        $yearlyDue = Invoice::where('invoice_date', '>=', $startOfYear)->sum('due_amount');
        $yearlySubtotal = Invoice::where('invoice_date', '>=', $startOfYear)->sum('subtotal');
        $yearlyDelivery = Invoice::where('invoice_date', '>=', $startOfYear)->sum('delivery_charge');
        $yearlyQuantity = Invoice::where('invoice_date', '>=', $startOfYear)
            ->with('items')
            ->get()
            ->sum(function($invoice) {
                return $invoice->items->sum('quantity');
            });
        
        // Status counts
        $invoiceStatusCounts = Invoice::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');
            
        $paymentStatusCounts = Invoice::select('payment_status', DB::raw('count(*) as count'))
            ->groupBy('payment_status')
            ->pluck('count', 'payment_status');
        
        // Recent invoices
        $recentInvoices = Invoice::with(['customer', 'creator'])
            ->latest()
            ->limit(10)
            ->get();
        
        // Top customers by total spent
        $topCustomers = Customer::select([
                'customers.*',
                DB::raw('(SELECT COUNT(*) FROM invoices WHERE invoices.customer_id = customers.id) as invoices_count'),
                DB::raw('(SELECT COALESCE(SUM(total), 0) FROM invoices WHERE invoices.customer_id = customers.id) as invoices_sum_total'),
                DB::raw('(SELECT COALESCE(SUM(paid_amount), 0) FROM invoices WHERE invoices.customer_id = customers.id) as invoices_sum_paid'),
                DB::raw('(SELECT COALESCE(SUM(due_amount), 0) FROM invoices WHERE invoices.customer_id = customers.id) as invoices_sum_due'),
                DB::raw('(SELECT COALESCE(SUM(subtotal), 0) FROM invoices WHERE invoices.customer_id = customers.id) as invoices_sum_subtotal'),
                DB::raw('(SELECT COALESCE(SUM(delivery_charge), 0) FROM invoices WHERE invoices.customer_id = customers.id) as invoices_sum_delivery'),
                DB::raw('(SELECT COALESCE(SUM(quantity), 0) FROM invoice_items WHERE invoice_items.invoice_id IN (SELECT id FROM invoices WHERE invoices.customer_id = customers.id)) as total_quantity')
            ])
            ->orderBy('invoices_sum_total', 'desc')
            ->limit(5)
            ->get();
        
        // User performance summary (Top 5 creators)
        $topCreators = User::select([
                'users.*',
                DB::raw('(SELECT COUNT(*) FROM invoices WHERE invoices.created_by = users.id) as total_invoices'),
                DB::raw('(SELECT COALESCE(SUM(total), 0) FROM invoices WHERE invoices.created_by = users.id) as total_amount'),
                DB::raw('(SELECT COALESCE(SUM(paid_amount), 0) FROM invoices WHERE invoices.created_by = users.id) as total_paid'),
                DB::raw('(SELECT COALESCE(SUM(due_amount), 0) FROM invoices WHERE invoices.created_by = users.id) as total_due'),
                DB::raw('(SELECT COALESCE(SUM(subtotal), 0) FROM invoices WHERE invoices.created_by = users.id) as total_subtotal'),
                DB::raw('(SELECT COALESCE(SUM(delivery_charge), 0) FROM invoices WHERE invoices.created_by = users.id) as total_delivery'),
                DB::raw('(SELECT COALESCE(SUM(quantity), 0) FROM invoice_items WHERE invoice_items.invoice_id IN (SELECT id FROM invoices WHERE invoices.created_by = users.id)) as total_quantity')
            ])
            ->having('total_invoices', '>', 0)
            ->orderBy('total_amount', 'desc')
            ->limit(5)
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
            'last7Days',
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
        
        // Get user's invoices
        $userInvoices = Invoice::where('created_by', $user->id);
        
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
        
        // Today's stats
        $todayInvoices = (clone $userInvoices)->whereDate('invoice_date', $today)->count();
        $todayRevenue = (clone $userInvoices)->whereDate('invoice_date', $today)->sum('total');
        $todayPaid = (clone $userInvoices)->whereDate('invoice_date', $today)->sum('paid_amount');
        $todayDue = (clone $userInvoices)->whereDate('invoice_date', $today)->sum('due_amount');
        $todaySubtotal = (clone $userInvoices)->whereDate('invoice_date', $today)->sum('subtotal');
        $todayDelivery = (clone $userInvoices)->whereDate('invoice_date', $today)->sum('delivery_charge');
        $todayQuantity = (clone $userInvoices)
            ->whereDate('invoice_date', $today)
            ->with('items')
            ->get()
            ->sum(function($invoice) {
                return $invoice->items->sum('quantity');
            });
        
        // This Week stats
        $weekInvoices = (clone $userInvoices)->where('invoice_date', '>=', $startOfWeek)->count();
        $weekRevenue = (clone $userInvoices)->where('invoice_date', '>=', $startOfWeek)->sum('total');
        $weekPaid = (clone $userInvoices)->where('invoice_date', '>=', $startOfWeek)->sum('paid_amount');
        $weekDue = (clone $userInvoices)->where('invoice_date', '>=', $startOfWeek)->sum('due_amount');
        $weekSubtotal = (clone $userInvoices)->where('invoice_date', '>=', $startOfWeek)->sum('subtotal');
        $weekDelivery = (clone $userInvoices)->where('invoice_date', '>=', $startOfWeek)->sum('delivery_charge');
        $weekQuantity = (clone $userInvoices)
            ->where('invoice_date', '>=', $startOfWeek)
            ->with('items')
            ->get()
            ->sum(function($invoice) {
                return $invoice->items->sum('quantity');
            });
        
        // This Month stats
        $monthlyInvoices = (clone $userInvoices)->where('invoice_date', '>=', $startOfMonth)->count();
        $monthlyRevenue = (clone $userInvoices)->where('invoice_date', '>=', $startOfMonth)->sum('total');
        $monthlyPaid = (clone $userInvoices)->where('invoice_date', '>=', $startOfMonth)->sum('paid_amount');
        $monthlyDue = (clone $userInvoices)->where('invoice_date', '>=', $startOfMonth)->sum('due_amount');
        $monthlySubtotal = (clone $userInvoices)->where('invoice_date', '>=', $startOfMonth)->sum('subtotal');
        $monthlyDelivery = (clone $userInvoices)->where('invoice_date', '>=', $startOfMonth)->sum('delivery_charge');
        $monthlyQuantity = (clone $userInvoices)
            ->where('invoice_date', '>=', $startOfMonth)
            ->with('items')
            ->get()
            ->sum(function($invoice) {
                return $invoice->items->sum('quantity');
            });
        
        // This Year stats
        $yearlyInvoices = (clone $userInvoices)->where('invoice_date', '>=', $startOfYear)->count();
        $yearlyRevenue = (clone $userInvoices)->where('invoice_date', '>=', $startOfYear)->sum('total');
        $yearlyPaid = (clone $userInvoices)->where('invoice_date', '>=', $startOfYear)->sum('paid_amount');
        $yearlyDue = (clone $userInvoices)->where('invoice_date', '>=', $startOfYear)->sum('due_amount');
        $yearlySubtotal = (clone $userInvoices)->where('invoice_date', '>=', $startOfYear)->sum('subtotal');
        $yearlyDelivery = (clone $userInvoices)->where('invoice_date', '>=', $startOfYear)->sum('delivery_charge');
        $yearlyQuantity = (clone $userInvoices)
            ->where('invoice_date', '>=', $startOfYear)
            ->with('items')
            ->get()
            ->sum(function($invoice) {
                return $invoice->items->sum('quantity');
            });
        
        // Monthly breakdown for current year - FIXED GROUP BY ISSUE
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
                DB::raw('COALESCE(SUM(items.quantity), 0) as total_quantity')
            )
            ->leftJoin('invoice_items as items', 'invoices.id', '=', 'items.invoice_id')
            ->where('invoices.created_by', $user->id)
            ->whereYear('invoices.invoice_date', Carbon::now()->year)
            ->whereNull('invoices.deleted_at')
            ->groupBy(DB::raw('YEAR(invoices.invoice_date)'), DB::raw('MONTH(invoices.invoice_date)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get()
            ->keyBy('month');
        
        // Recent invoices (user's only)
        $recentInvoices = (clone $userInvoices)
            ->with(['customer'])
            ->latest()
            ->limit(10)
            ->get();
        
        // Payment status counts for user
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