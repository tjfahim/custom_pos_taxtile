<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function dashboard()
    {
        // Date filters
        $today = now()->format('Y-m-d');
        $startOfWeek = now()->startOfWeek()->format('Y-m-d');
        $startOfMonth = now()->startOfMonth()->format('Y-m-d');
        
        // Total counts
        $totalInvoices = Invoice::count();
        $totalCustomers = Customer::count();
        
        // Today's counts
        $todayInvoices = Invoice::whereDate('invoice_date', $today)->count();
        $todayRevenue = Invoice::whereDate('invoice_date', $today)->sum('total');
        
        // This Week counts
        $weekInvoices = Invoice::where('invoice_date', '>=', $startOfWeek)->count();
        $weekRevenue = Invoice::where('invoice_date', '>=', $startOfWeek)->sum('total');
        
        // This Month counts
        $monthlyInvoices = Invoice::where('invoice_date', '>=', $startOfMonth)->count();
        $monthlyRevenue = Invoice::where('invoice_date', '>=', $startOfMonth)->sum('total');
        
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
                DB::raw('(SELECT COALESCE(SUM(total), 0) FROM invoices WHERE invoices.customer_id = customers.id) as invoices_sum_total')
            ])
            ->orderBy('invoices_sum_total', 'desc')
            ->limit(5)
            ->get();
        
        // Calculate totals for amounts
        $totalRevenue = Invoice::sum('total');
        $totalPaidAmount = Invoice::sum('paid_amount');
        $totalDueAmount = Invoice::sum('due_amount');
        
        return view('admin.dashboard', compact(
            'totalInvoices',
            'totalCustomers',
            'todayInvoices',
            'todayRevenue',
            'weekInvoices',
            'weekRevenue',
            'monthlyInvoices',
            'monthlyRevenue',
            'invoiceStatusCounts',
            'paymentStatusCounts',
            'recentInvoices',
            'topCustomers',
            'totalRevenue',
            'totalPaidAmount',
            'totalDueAmount'
        ));
    }
}