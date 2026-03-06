<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        // Set default dates (last 30 days)
        $fromDate = Carbon::now()->subDays(30)->startOfDay();
        $toDate = Carbon::now()->endOfDay();
        
        return view('reports.index', compact('fromDate', 'toDate'));
    }

 public function generate(Request $request)
{
    $request->validate([
        'from_date' => 'required|date',
        'to_date' => 'required|date|after_or_equal:from_date',
    ]);

    $fromDate = Carbon::parse($request->from_date)->startOfDay();
    $toDate = Carbon::parse($request->to_date)->endOfDay();

    // Get filtered invoices with relationships - ONLY CONFIRMED and NOT DELETED
    $invoices = Invoice::with(['customer', 'creator', 'items'])
        ->whereBetween('created_at', [$fromDate, $toDate])
        ->where('status', 'confirmed') // Only confirmed invoices
        ->whereNull('deleted_at') // Exclude soft-deleted records
        ->orderBy('created_at', 'desc')
        ->get();

    // Calculate summary totals
    $summary = [
        'total_invoices' => $invoices->count(),
        'total_subtotal' => $invoices->sum('subtotal'),
        'total_delivery_charge' => $invoices->sum('delivery_charge'),
        'total_amount' => $invoices->sum('total'),
        'total_paid' => $invoices->sum('paid_amount'),
        'total_due' => $invoices->sum('due_amount'),
        'total_weight' => $invoices->sum('total_weight'),
        'total_quantity' => $invoices->sum(function($invoice) {
            return $invoice->items->sum('quantity');
        }),
    ];

    // Calculate income (total amount collected)
    $summary['total_income'] = $summary['total_paid'];

    // Payment status breakdown with quantity (only for confirmed invoices)
    $paidInvoices = $invoices->where('payment_status', 'paid');
    $partialInvoices = $invoices->where('payment_status', 'partial');
    $unpaidInvoices = $invoices->where('payment_status', 'unpaid');

    $paymentStatusStats = [
        'paid' => [
            'count' => $paidInvoices->count(),
            'total' => $paidInvoices->sum('total'),
            'paid' => $paidInvoices->sum('paid_amount'),
            'due' => $paidInvoices->sum('due_amount'),
            'quantity' => $paidInvoices->sum(function($invoice) {
                return $invoice->items->sum('quantity');
            }),
        ],
        'partial' => [
            'count' => $partialInvoices->count(),
            'total' => $partialInvoices->sum('total'),
            'paid' => $partialInvoices->sum('paid_amount'),
            'due' => $partialInvoices->sum('due_amount'),
            'quantity' => $partialInvoices->sum(function($invoice) {
                return $invoice->items->sum('quantity');
            }),
        ],
        'unpaid' => [
            'count' => $unpaidInvoices->count(),
            'total' => $unpaidInvoices->sum('total'),
            'paid' => $unpaidInvoices->sum('paid_amount'),
            'due' => $unpaidInvoices->sum('due_amount'),
            'quantity' => $unpaidInvoices->sum(function($invoice) {
                return $invoice->items->sum('quantity');
            }),
        ],
    ];

    // Invoice status breakdown (will only show 'confirmed' since we filtered)
    $invoiceStatusStats = $invoices->groupBy('status')
        ->map(function ($group) {
            return [
                'count' => $group->count(),
                'total' => $group->sum('total'),
                'paid' => $group->sum('paid_amount'),
                'due' => $group->sum('due_amount'),
                'quantity' => $group->sum(function($invoice) {
                    return $invoice->items->sum('quantity');
                }),
            ];
        });

    // Daily summary for chart (only confirmed invoices)
    $dailySummary = $invoices->groupBy(function ($invoice) {
        return $invoice->created_at->format('Y-m-d');
    })->map(function ($dayInvoices) {
        return [
            'date' => $dayInvoices->first()->created_at->format('M d, Y'),
            'count' => $dayInvoices->count(),
            'total' => $dayInvoices->sum('total'),
            'paid' => $dayInvoices->sum('paid_amount'),
            'due' => $dayInvoices->sum('due_amount'),
            'quantity' => $dayInvoices->sum(function($invoice) {
                return $invoice->items->sum('quantity');
            }),
        ];
    })->values();

    // Top customers by order value (only from confirmed invoices)
    $topCustomers = $invoices->groupBy('customer_id')
        ->map(function ($customerInvoices) {
            $customer = $customerInvoices->first()->customer;
            return [
                'name' => $customer ? $customer->name : 'N/A',
                'phone' => $customer ? $customer->phone_number_1 : 'N/A',
                'invoice_count' => $customerInvoices->count(),
                'total_spent' => $customerInvoices->sum('total'),
                'total_paid' => $customerInvoices->sum('paid_amount'),
                'total_due' => $customerInvoices->sum('due_amount'),
                'quantity' => $customerInvoices->sum(function($invoice) {
                    return $invoice->items->sum('quantity');
                }),
            ];
        })
        ->sortByDesc('total_spent')
        ->take(10)
        ->values();

    // Delivery area breakdown (only from confirmed invoices)
    $deliveryAreaStats = $invoices->groupBy('delivery_area')
        ->map(function ($areaInvoices) {
            return [
                'count' => $areaInvoices->count(),
                'total' => $areaInvoices->sum('total'),
                'delivery_charge' => $areaInvoices->sum('delivery_charge'),
                'paid' => $areaInvoices->sum('paid_amount'),
                'due' => $areaInvoices->sum('due_amount'),
                'quantity' => $areaInvoices->sum(function($invoice) {
                    return $invoice->items->sum('quantity');
                }),
            ];
        })
        ->sortByDesc('count');

    // Product type breakdown (only from confirmed invoices)
    $productTypeStats = $invoices->groupBy('product_type')
        ->map(function ($typeInvoices) {
            return [
                'count' => $typeInvoices->count(),
                'total' => $typeInvoices->sum('total'),
                'paid' => $typeInvoices->sum('paid_amount'),
                'due' => $typeInvoices->sum('due_amount'),
                'quantity' => $typeInvoices->sum(function($invoice) {
                    return $invoice->items->sum('quantity');
                }),
            ];
        });

    // ENHANCED: Created by (User) breakdown with detailed metrics (only from confirmed invoices)
    $createdByStats = $invoices->groupBy('created_by')
        ->map(function ($userInvoices) {
            $user = $userInvoices->first()->creator;
            return [
                'name' => $user ? $user->name : 'N/A',
                'email' => $user ? $user->email : 'N/A',
                'count' => $userInvoices->count(),
                'quantity' => $userInvoices->sum(function($invoice) {
                    return $invoice->items->sum('quantity');
                }),
                'subtotal' => $userInvoices->sum('subtotal'),
                'delivery_charge' => $userInvoices->sum('delivery_charge'),
                'total' => $userInvoices->sum('total'),
                'paid' => $userInvoices->sum('paid_amount'),
                'due' => $userInvoices->sum('due_amount'),
                'avg_order_value' => $userInvoices->avg('total'),
                'avg_quantity_per_order' => $userInvoices->average(function($invoice) {
                    return $invoice->items->sum('quantity');
                }),
            ];
        })
        ->sortByDesc('total')
        ->values();

    // User performance summary totals (only from confirmed invoices)
    $userSummary = [
        'total_creators' => $createdByStats->count(),
        'total_orders_created' => $createdByStats->sum('count'),
        'total_quantity_created' => $createdByStats->sum('quantity'),
        'total_subtotal_created' => $createdByStats->sum('subtotal'),
        'total_delivery_created' => $createdByStats->sum('delivery_charge'),
        'total_amount_created' => $createdByStats->sum('total'),
        'total_paid_created' => $createdByStats->sum('paid'),
        'total_due_created' => $createdByStats->sum('due'),
    ];

    // OPTIONAL: Add top creators specifically for confirmed invoices (like your original query)
  $topCreators = $invoices->groupBy('created_by')
    ->map(function ($creatorInvoices) {
        $user = $creatorInvoices->first()->creator;
        
        $totalQuantity = $creatorInvoices->sum(function($invoice) {
            return $invoice->items->sum('quantity');
        });
        
        return [
            'id' => $user ? $user->id : null,
            'name' => $user ? $user->name : 'N/A',
            'email' => $user ? $user->email : 'N/A',
            
            // All metrics are based on confirmed invoices in the date range
            'total_invoices' => $creatorInvoices->count(),
            'total_quantity' => $totalQuantity,
            'total_subtotal' => $creatorInvoices->sum('subtotal'),
            'total_delivery' => $creatorInvoices->sum('delivery_charge'),
            'total_amount' => $creatorInvoices->sum('total'),
            'total_paid' => $creatorInvoices->sum('paid_amount'),
            'total_due' => $creatorInvoices->sum('due_amount'),
            'avg_order_value' => $creatorInvoices->avg('total'),
            
            // Payment status counts
            'paid_invoices' => $creatorInvoices->where('payment_status', 'paid')->count(),
            'partial_invoices' => $creatorInvoices->where('payment_status', 'partial')->count(),
            'unpaid_invoices' => $creatorInvoices->where('payment_status', 'unpaid')->count(),
            
            // Collection rate for this creator in this date range
            'collection_rate' => $creatorInvoices->sum('total') > 0 
                ? round(($creatorInvoices->sum('paid_amount') / $creatorInvoices->sum('total')) * 100, 2) 
                : 0,
            
            // Additional useful metrics
            'first_confirmed' => $creatorInvoices->min('confirmed_at')?->format('Y-m-d'),
            'last_confirmed' => $creatorInvoices->max('confirmed_at')?->format('Y-m-d'),
        ];
    })
    ->filter(function ($creator) {
        return $creator['total_invoices'] > 0;
    })
    ->sortByDesc('total_amount')
    ->values();

$creatorSummary = [
    'total_creators' => $topCreators->count(),
    'total_invoices_created' => $topCreators->sum('total_invoices'),
    'total_quantity_created' => $topCreators->sum('total_quantity'),
    'total_amount_created' => $topCreators->sum('total_amount'),
    'total_paid_created' => $topCreators->sum('total_paid'),
    'total_due_created' => $topCreators->sum('total_due'),
    'total_subtotal_created' => $topCreators->sum('total_subtotal'),
    'total_delivery_created' => $topCreators->sum('total_delivery'),
    'avg_order_value_overall' => $topCreators->sum('total_invoices') > 0 
        ? round($topCreators->sum('total_amount') / $topCreators->sum('total_invoices'), 2) 
        : 0,
    'collection_rate_overall' => $topCreators->sum('total_amount') > 0 
        ? round(($topCreators->sum('total_paid') / $topCreators->sum('total_amount')) * 100, 2) 
        : 0,
    'top_creator' => $topCreators->isNotEmpty() ? $topCreators->first()['name'] : 'N/A',
    'top_creator_amount' => $topCreators->isNotEmpty() ? $topCreators->first()['total_amount'] : 0,
    'top_creator_invoices' => $topCreators->isNotEmpty() ? $topCreators->first()['total_invoices'] : 0,
    'from_date' => $request->from_date,
    'to_date' => $request->to_date,
];
    return view('reports.index', compact(
        'invoices',
        'summary',
        'fromDate',
        'toDate',
        'paymentStatusStats',
        'invoiceStatusStats',
        'dailySummary',
        'topCustomers',
        'deliveryAreaStats',
        'productTypeStats',
        'createdByStats',
        'userSummary',
        'topCreators',
        'creatorSummary'
    ));
}

    public function exportCsv(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();

        $invoices = Invoice::with(['customer', 'creator', 'items'])
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = "invoice_report_{$request->from_date}_to_{$request->to_date}.csv";
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function() use ($invoices) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Invoice Number',
                'Date',
                'Customer Name',
                'Customer Phone',
                'Recipient Name',
                'Recipient Phone',
                'Recipient Address',
                'Delivery Area',
                'Product Type',
                'Items',
                'Total Quantity',
                'Total Weight (g)',
                'Subtotal',
                'Delivery Charge',
                'Total',
                'Paid Amount',
                'Due Amount',
                'Payment Status',
                'Invoice Status',
                'Created By',
                'Created By Email',
                'Special Instructions',
                'Notes'
            ]);

            // Data rows
            foreach ($invoices as $invoice) {
                $itemsList = $invoice->items->map(function($item) {
                    return $item->item_name . ' (Qty: ' . $item->quantity . ', Price: ' . $item->unit_price . ')';
                })->implode('; ');

                $totalQuantity = $invoice->items->sum('quantity');

                fputcsv($file, [
                    $invoice->invoice_number,
                    $invoice->created_at->format('Y-m-d H:i'),
                    $invoice->customer->name ?? 'N/A',
                    $invoice->customer->phone_number_1 ?? 'N/A',
                    $invoice->recipient_name,
                    $invoice->recipient_phone,
                    $invoice->recipient_address,
                    $invoice->delivery_area,
                    $invoice->product_type ?? 'N/A',
                    $itemsList,
                    $totalQuantity,
                    $invoice->total_weight,
                    number_format($invoice->subtotal, 2),
                    number_format($invoice->delivery_charge, 2),
                    number_format($invoice->total, 2),
                    number_format($invoice->paid_amount, 2),
                    number_format($invoice->due_amount, 2),
                    ucfirst($invoice->payment_status),
                    ucfirst($invoice->status),
                    $invoice->creator->name ?? 'N/A',
                    $invoice->creator->email ?? 'N/A',
                    $invoice->special_instructions,
                    $invoice->notes
                ]);
            }

            // Summary row
            fputcsv($file, []); // Empty row
            fputcsv($file, ['SUMMARY']);
            fputcsv($file, [
                'Total Invoices',
                'Total Quantity',
                'Total Subtotal',
                'Total Delivery Charge',
                'Total Amount',
                'Total Paid',
                'Total Due'
            ]);
            fputcsv($file, [
                $invoices->count(),
                $invoices->sum(function($invoice) {
                    return $invoice->items->sum('quantity');
                }),
                number_format($invoices->sum('subtotal'), 2),
                number_format($invoices->sum('delivery_charge'), 2),
                number_format($invoices->sum('total'), 2),
                number_format($invoices->sum('paid_amount'), 2),
                number_format($invoices->sum('due_amount'), 2)
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $fromDate = Carbon::parse($request->from_date)->startOfDay();
        $toDate = Carbon::parse($request->to_date)->endOfDay();

        $invoices = Invoice::with(['customer', 'creator', 'items'])
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'total_invoices' => $invoices->count(),
            'total_subtotal' => $invoices->sum('subtotal'),
            'total_delivery_charge' => $invoices->sum('delivery_charge'),
            'total_amount' => $invoices->sum('total'),
            'total_paid' => $invoices->sum('paid_amount'),
            'total_due' => $invoices->sum('due_amount'),
            'total_quantity' => $invoices->sum(function($invoice) {
                return $invoice->items->sum('quantity');
            }),
        ];

        // User performance for print view
        $createdByStats = $invoices->groupBy('created_by')
            ->map(function ($userInvoices) {
                $user = $userInvoices->first()->creator;
                return [
                    'name' => $user ? $user->name : 'N/A',
                    'count' => $userInvoices->count(),
                    'quantity' => $userInvoices->sum(function($invoice) {
                        return $invoice->items->sum('quantity');
                    }),
                    'total' => $userInvoices->sum('total'),
                    'paid' => $userInvoices->sum('paid_amount'),
                    'due' => $userInvoices->sum('due_amount'),
                ];
            })
            ->sortByDesc('total')
            ->values();

        return view('reports.print', compact('invoices', 'summary', 'fromDate', 'toDate', 'createdByStats'));
    }
}