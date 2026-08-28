<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ReturnItem;
use App\Traits\TracksInvoiceEdits;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
class InvoiceController extends Controller
{
    use TracksInvoiceEdits;

    public function pos()
    {
        $customers = Customer::where('status', 'active')->latest()->get();
        return view('invoices.pos', compact('customers'));
    }
public function storePos(Request $request)
{
    $request->validate([
        'recipient_name' => 'required|string|max:255',
        'merchant_order_id' => 'nullable|string|max:255',
        'recipient_phone' => 'required|string|max:20',
        'recipient_address' => 'nullable|string',
        'delivery_area' => 'required_unless:is_inhouse_sale,1|nullable|string',
        'delivery_type' => 'required|string',
        'store_location' => 'required|string',
        'delivery_charge' => 'nullable|numeric|min:0',
        'amount_to_collect' => 'nullable|numeric|min:0',
        'status' => 'required|string',
        'paid_amount' => 'nullable|numeric|min:0',
        'payment_date' => 'nullable|date',
        'paid_amount2' => 'nullable|numeric|min:0',
        'payment_method2' => 'nullable|string|in:bkash,bkash_personal,bank_transfer,cash',
        'is_wholesale' => 'nullable|boolean',
        'is_inhouse_sale' => 'nullable|boolean',
        'courier_name' => 'nullable|string|in:Pathao,Steadfast,SA,SUNDORBAN,JANONI,REDEX,Exchange',
        'team_id' => 'nullable|exists:users,id',
        'items' => 'required|array|min:1',
        'items.*.item_name' => 'required|string',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.unit_price' => 'required|numeric|min:0',
        'items.*.weight' => 'nullable|integer|min:0',
        'has_return_items' => 'nullable|boolean',
        'return_items' => 'nullable|array',
        'return_items.*.item_name' => 'nullable|string',
        'return_items.*.quantity' => 'nullable|integer|min:1',
        'return_items.*.unit_price' => 'nullable|numeric|min:0',
        'return_items.*.return_reason' => 'nullable|string',
    ]);
 
    try {
        $isInhouseSale = $request->has('is_inhouse_sale') ? (bool)$request->is_inhouse_sale : false;
        $isWholesale = $request->has('is_wholesale') ? (bool)$request->is_wholesale : false;
        $hasReturnItems = $request->has('has_return_items') && ($request->has_return_items == '1' || $request->has_return_items === true);
 
        // Customer lookup/creation
        $customer = Customer::where('phone_number_1', $request->recipient_phone)
            ->orWhere('phone_number_2', $request->recipient_phone)
            ->first();
 
        if (!$customer) {
            $customer = Customer::create([
                'name' => $request->recipient_name,
                'full_address' => $request->recipient_address,
                'merchant_order_id' => $request->merchant_order_id,
                'phone_number_1' => $request->recipient_phone,
                'phone_number_2' => $request->recipient_secondary_phone,
                'delivery_area' => $request->delivery_area,
                'note' => $request->notes,
                'status' => 'active',
            ]);
        } else {
            $customer->update([
                'name' => $request->recipient_name,
                'full_address' => $request->recipient_address,
                'delivery_area' => $request->delivery_area,
                'note' => $request->notes,
            ]);
 
            if ($request->recipient_secondary_phone) {
                $customer->phone_number_2 = $request->recipient_secondary_phone;
                $customer->save();
            }
        }
 
        // Create invoice
        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'recipient_name' => $request->recipient_name,
            'merchant_order_id' => $request->merchant_order_id,
            'recipient_phone' => $request->recipient_phone,
            'recipient_secondary_phone' => $request->recipient_secondary_phone,
            'recipient_address' => $request->recipient_address,
            'delivery_area' => $isInhouseSale ? null : $request->delivery_area,
            'delivery_type' => $request->delivery_type,
            'store_location' => $request->store_location,
            'delivery_charge' => $isInhouseSale ? 0 : ($request->delivery_charge ?? 60),
            'special_instructions' => $request->special_instructions,
            'product_type' => $request->product_type,
            'amount_to_collect' => $request->amount_to_collect ?? 0,
 
            // First (always-available) payment
            'paid_amount' => $request->paid_amount ?? 0,
            'payment_method' => $request->payment_method,
            'payment_details' => $this->getPaymentDetails($request),
            'payment_date' => $request->payment_date
                ? \Carbon\Carbon::parse($request->payment_date)
                : now(),
 
            // Second payment — in-house sale only. Forced to empty for a
            // normal sale even if the form happened to submit values.
            'paid_amount2' => $isInhouseSale ? ($request->paid_amount2 ?? 0) : 0,
            'payment_method2' => $isInhouseSale ? $request->payment_method2 : null,
            'payment_details2' => $isInhouseSale ? $this->getPaymentDetails2($request) : null,
 
            'notes' => $request->notes,
            'pathao_city_id' => $isInhouseSale ? null : $request->delivery_city_id,
            'pathao_zone_id' => $isInhouseSale ? null : $request->delivery_zone_id,
            'pathao_area_id' => $isInhouseSale ? null : $request->delivery_area_id,
            'status' => $request->status,
            'invoice_date' => now(),
            'created_by' => auth()->id(),
            'confirmed_at' => now(),
            'has_return_items' => $hasReturnItems,
            'is_wholesale' => $isWholesale,
            'is_inhouse_sale' => $isInhouseSale,
            'courier_name' => $isInhouseSale ? 'Pathao' : ($request->courier_name ?? 'Pathao'),
            'team_id' => $request->team_id,
        ]);
 
        // Add invoice items
        foreach ($request->items as $item) {
            $totalPrice = $item['quantity'] * $item['unit_price'];
 
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_name' => $item['item_name'],
                'description' => $item['description'] ?? null,
                'quantity' => $item['quantity'],
                'weight' => $item['weight'] ?? 500,
                'unit_price' => $item['unit_price'],
                'total_price' => $totalPrice,
            ]);
        }
 
        // Add return items if has return
        if ($hasReturnItems && !empty($request->return_items)) {
            foreach ($request->return_items as $returnItem) {
                if (empty($returnItem['item_name'])) {
                    continue;
                }
 
                $totalPrice = ($returnItem['quantity'] ?? 1) * ($returnItem['unit_price'] ?? 0);
 
                ReturnItem::create([
                    'invoice_id' => $invoice->id,
                    'item_name' => $returnItem['item_name'],
                    'description' => $returnItem['description'] ?? null,
                    'quantity' => $returnItem['quantity'] ?? 1,
                    'weight' => $returnItem['weight'] ?? 500,
                    'unit_price' => $returnItem['unit_price'] ?? 0,
                    'total_price' => $totalPrice,
                    'return_reason' => $returnItem['return_reason'] ?? null,
                    'return_note' => $returnItem['return_note'] ?? null,
                ]);
            }
        }
 
        // Calculate totals
        $invoice->calculateTotals();
 
        // Reload items/returnItems so the 'create' history snapshot includes them
        $invoice->load(['items', 'returnItems']);
 
        // Determine if request is AJAX
        $isAjax = $request->ajax() || $request->wantsJson() || $request->has('is_ajax');
        $invoiceData = $this->getInvoiceDataForTracking($invoice);
        $this->trackEdit($invoice, [], $invoiceData, 'create');
if ($request->team_id) {
    session(['recent_team_member_id' => $request->team_id]);
}        if ($isAjax) {
            return response()->json([
                'success' => true,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'has_return_items' => $hasReturnItems,
                'print_url' => route('admin.invoices.print', $invoice->id),
                'message' => 'Invoice created successfully!'
            ]);
        }
 
        return redirect()->route('admin.invoices.print', $invoice->id)
            ->with('success', 'Invoice created successfully!');
 
    } catch (\Exception $e) {
 
        $isAjax = $request->ajax() || $request->wantsJson() || $request->has('is_ajax');
 
        if ($isAjax) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating invoice: ' . $e->getMessage()
            ], 500);
        }
 
        return back()->with('error', 'Error creating invoice: ' . $e->getMessage())->withInput();
    }
}
 
/**
 * Mirrors getPaymentDetails() but reads the "_2" suffixed fields for the
 * second, in-house-sale-only payment.
 */
private function getPaymentDetails2($request)
{
    switch ($request->payment_method2) {
        case 'bkash':
            return $request->bkash_transaction2;
        case 'bkash_personal':
            return $request->bkash_personal_transaction2;
        case 'bank_transfer':
            return $request->bank_transfer_details2;
        case 'cash':
            return $request->cash_amount2;
        default:
            return null;
    }
}

private function getPaymentDetails($request)
{
    switch ($request->payment_method) {
        case 'bkash':
            return $request->bkash_transaction; // Store full transaction ID for merchant bkash
        case 'bkash_personal':
            return $request->bkash_personal_transaction; // Store last 4 digits for personal bkash
        case 'bank_transfer':
            return $request->bank_transfer_details; // Store bank details
        case 'cash':
                return $request->cash_amount; 
        default:
            return null;
    }
}

    // Print invoice
    public function print($id)
    {
        $invoice = Invoice::with(['customer', 'items'])->findOrFail($id);
        return view('invoices.print', compact('invoice'));
    }
/**
 * Print multiple invoices
 */
public function printMultiple($ids)
{
    try {
        $invoiceIds = explode(',', $ids);
        
        $invoices = Invoice::with(['customer', 'items'])
            ->whereIn('id', $invoiceIds)
            ->get();
        
        if ($invoices->isEmpty()) {
            abort(404, 'No invoices found');
        }
        
        return view('invoices.print-multiple', compact('invoices'));
        
    } catch (\Exception $e) {
        \Log::error('Multi-print error: ' . $e->getMessage());
        abort(500, 'Failed to load invoices');
    }
}

public function index(Request $request)
{
    if ($request->ajax()) {
        \Log::info('AJAX Request received', $request->all());
        return $this->getDataTableData($request);
    }
    
    // Get counts for filter buttons (optimized)
    $counts = [
        'all' => Invoice::whereNull('deleted_at')->count(),
        'confirmed' => Invoice::whereNull('deleted_at')->where('status', 'confirmed')->count(),
        'pending' => Invoice::whereNull('deleted_at')->where('status', 'pending')->count(),
        'cancelled' => Invoice::whereNull('deleted_at')->where('status', 'cancelled')->count(),
    ];
    
    return view('invoices.index', compact('counts'));
}


private function getDataTableData(Request $request)
{
    try {
        $query = Invoice::with(['customer', 'creator'])
            ->whereNull('deleted_at'); // Exclude soft deleted
        
        // Apply status filter
        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }
        
        // DataTables parameters
        $start = $request->input('start', 0);
        $length = $request->input('length', 20);
        $orderColumnIndex = $request->input('order.0.column', 5); // Default to date column
        $orderDir = $request->input('order.0.dir', 'desc');
        $searchValue = $request->input('search.value', '');
        
        // Define sortable columns with proper field names
        $columns = [
            0 => 'id',
            1 => 'invoice_number',
            2 => 'customer_id',
            3 => 'recipient_phone',
            4 => 'merchant_order_id',
            5 => 'invoice_date',
            6 => 'total',
            7 => 'status',
            8 => 'payment_status',
            9 => 'created_by',
            10 => 'id',
        ];
        
        $orderColumn = $columns[$orderColumnIndex] ?? 'invoice_date';
        
        // Special handling for ordering - use created_at for latest records
        if ($orderColumn == 'invoice_date') {
            // Order by created_at DESC to get latest records first
            $orderColumn = 'created_at';
        }
        
        // Apply search
        if (!empty($searchValue)) {
            $query->where(function($q) use ($searchValue) {
                $q->where('invoice_number', 'LIKE', "%{$searchValue}%")
                  ->orWhere('merchant_order_id', 'LIKE', "%{$searchValue}%")
                  ->orWhere('recipient_name', 'LIKE', "%{$searchValue}%")
                  ->orWhere('recipient_phone', 'LIKE', "%{$searchValue}%")
                  ->orWhereHas('customer', function($customerQuery) use ($searchValue) {
                      $customerQuery->where('name', 'LIKE', "%{$searchValue}%")
                                   ->orWhere('phone_number_1', 'LIKE', "%{$searchValue}%");
                  });
            });
        }
        
        // Get total records count
        $totalRecords = Invoice::whereNull('deleted_at')->count();
        $filteredRecords = $query->count();
        
        // Get paginated data with proper ordering
        $invoices = $query->orderBy($orderColumn, $orderDir)
                          ->orderBy('id', 'desc') // Secondary order by ID for ties
                          ->skip($start)
                          ->take($length)
                          ->get();
        
        // Format data for DataTables
        $data = [];
        foreach ($invoices as $index => $invoice) {
            $row = [
                'DT_RowIndex' => $start + $index + 1,
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_name' => $invoice->customer->name ?? 'N/A',
                'customer_phone' => $invoice->customer->phone_number_1 ?? $invoice->recipient_phone,
                'merchant_order_id' => $invoice->merchant_order_id ?? 'N/A',
                'invoice_date' => $invoice->invoice_date->format('d M Y'),
                'total' => '৳' . number_format($invoice->total, 0),
                'status' => [
                    'value' => $invoice->status,
                    'badge' => $invoice->status == 'confirmed' ? 'success' : ($invoice->status == 'pending' ? 'warning' : 'danger'),
                    'text' => ucfirst($invoice->status)
                ],
                'payment_status' => [
                    'value' => $invoice->payment_status,
                    'badge' => $invoice->payment_status == 'paid' ? 'success' : ($invoice->payment_status == 'partial' ? 'warning' : 'danger'),
                    'text' => ucfirst($invoice->payment_status)
                ],
                'created_by' => $invoice->creator->name ?? 'N/A',
                'actions' => $this->getActionButtons($invoice),
                // Add these for debugging if needed
                'created_at' => $invoice->created_at ? $invoice->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $invoice->updated_at ? $invoice->updated_at->format('Y-m-d H:i:s') : null,
            ];
            $data[] = $row;
        }
        
        $response = [
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ];
        
        \Log::info('DataTable Response', [
            'draw' => $response['draw'],
            'total' => $totalRecords,
            'filtered' => $filteredRecords,
            'data_count' => count($data),
            'first_invoice' => count($data) > 0 ? $data[0]['invoice_number'] : null,
            'last_invoice' => count($data) > 0 ? $data[count($data)-1]['invoice_number'] : null
        ]);
        
        return response()->json($response);
        
    } catch (\Exception $e) {
        \Log::error('DataTable Error: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
        return response()->json([
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'data' => [],
            'error' => $e->getMessage()
        ], 500);
    }
}

private function getActionButtons($invoice)
{
    $buttons = '<div class="btn-group btn-group-sm" role="group">';
    
    if (auth()->user()->can('print invoices')) {
        $buttons .= '<a href="' . route('admin.invoices.print', $invoice->id) . '" class="btn btn-info" title="Print"><i class="fa fa-print"></i></a>';
    }
    
    if (auth()->user()->can('view invoices')) {
        $buttons .= '<a href="' . route('admin.invoices.show', $invoice->id) . '" class="btn btn-secondary" title="View"><i class="fa fa-eye"></i></a>';
    }
    
    if (auth()->user()->can('edit invoices')) {
        $buttons .= '<a href="' . route('admin.invoices.edit', $invoice->id) . '" class="btn btn-warning" title="Edit"><i class="fa fa-edit"></i></a>';
    }
    
    $buttons .= $this->getStatusButtons($invoice);
    
    if (auth()->user()->can('delete invoices')) {
        $buttons .= '<form action="' . route('admin.invoices.destroy', $invoice->id) . '" method="POST" class="d-inline">' .
                    csrf_field() .
                    method_field('DELETE') .
                    '<button type="submit" class="btn btn-danger" onclick="return confirm(\'Delete this invoice?\')" title="Delete">' .
                    '<i class="fa fa-trash"></i>' .
                    '</button>' .
                    '</form>';
    }
    
    $buttons .= '</div>';
    return $buttons;
}

private function getStatusButtons($invoice)
{
    $buttons = '';
    
    if ($invoice->status == 'pending') {
        $buttons .= '<button type="button" class="btn btn-success btn-status-update" title="Confirm Invoice" data-invoice-id="' . $invoice->id . '" data-target-status="confirmed"><i class="fa fa-check"></i></button>';
    }
    
    if ($invoice->status == 'confirmed') {
        $buttons .= '<button type="button" class="btn btn-primary btn-status-update" title="Mark as Pending" data-invoice-id="' . $invoice->id . '" data-target-status="pending"><i class="fa fa-check"></i></button>';
    }
    
    return $buttons;
}

    // Show single invoice
    public function show($id)
    {
        $invoice = Invoice::with(['customer', 'items'])->findOrFail($id);
        return view('invoices.show', compact('invoice'));
    }

   public function edit($id)
    {
        $invoice = Invoice::with(['customer', 'items', 'returnItems'])->findOrFail($id);
        return view('invoices.edit', compact('invoice'));
    }

/**
 * Display history as a list with filtering
 */   
public function historyList(Request $request)
{
    // Get all invoices that have edit history (at least 2 edits)
    $invoices = Invoice::with(['editHistories' => function($query) {
        $query->orderBy('created_at', 'desc');
    }, 'editHistories.user'])
    ->whereHas('editHistories') // Only invoices with edit history
    ->withCount(['editHistories as total_edits'])
    ->having('total_edits', '>=', 2) // Only invoices with 2 or more edits
    ->orderBy('updated_at', 'desc')
    ->paginate(20);

    // Get statistics for each invoice
    $statistics = [];
    foreach ($invoices as $invoice) {
        $statistics[$invoice->id] = [
            'total_edits' => $invoice->total_edits,
            'unique_editors' => $invoice->editHistories->unique('user_id')->count(),
            'status_changes' => $invoice->editHistories->where('action_type', 'status_change')->count(),
            'last_edit' => $invoice->editHistories->first()?->created_at,
            'last_editor' => $invoice->editHistories->first()?->user_name ?? 'System',
            'created_by' => $invoice->editHistories->where('action_type', 'create')->first()?->user_name ?? 'Unknown',
        ];
    }

    // Filter by search
    if ($request->has('search') && !empty($request->search)) {
        $search = $request->search;
        $invoices = $invoices->filter(function($invoice) use ($search) {
            return stripos($invoice->invoice_number, $search) !== false ||
                   stripos($invoice->recipient_name, $search) !== false ||
                   stripos($invoice->recipient_phone, $search) !== false;
        });
    }

    return view('invoices.history-list', compact('invoices', 'statistics'));
}
    /**
     * Detailed history for a single invoice
     */
    public function historyDetail($id)
    {
        $invoice = Invoice::with([
            'editHistories' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'editHistories.user'
        ])->findOrFail($id);

        // Check if invoice has edit history
        if ($invoice->editHistories->count() === 0) {
            return redirect()->route('admin.invoices.show', $invoice->id)
                ->with('warning', 'This invoice has no edit history.');
        }

        // Get statistics
        $statistics = [
            'total_edits' => $invoice->editHistories->count(),
            'status_changes' => $invoice->editHistories->where('action_type', 'status_change')->count(),
            'created_by' => $invoice->editHistories->where('action_type', 'create')->first()?->user_name ?? 'Unknown',
            'last_edited' => $invoice->editHistories->first()?->created_at,
            'last_editor' => $invoice->editHistories->first()?->user_name ?? 'System',
            'unique_editors' => $invoice->editHistories->unique('user_id')->count(),
            'created_at' => $invoice->created_at,
            'updated_at' => $invoice->updated_at,
        ];

        return view('invoices.history-detail', compact('invoice', 'statistics'));
    }
    public function updateold(Request $request, $id)
    {
        $invoice = Invoice::with(['items', 'returnItems'])->findOrFail($id);
        $oldData = $this->getInvoiceDataForTracking($invoice);
        $oldItems = $invoice->items->toArray();
        $oldData['items'] = $oldItems;
        // Manual validation to handle dynamic array indices
        $validated = $request->validate([
            'delivery_charge' => 'required|numeric|min:0',
            'status' => 'required|string|in:confirmed,pending,cancelled',
            // Add customer validation
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:500',
            'special_instructions' => 'nullable|string|max:1000',
            // Return items validation
                    'team_id' => 'nullable|exists:users,id', // Add validation

            'has_return_items' => 'nullable|boolean',
            'return_items' => 'nullable|array',
            'return_items.*.item_name' => 'nullable|string',
            'return_items.*.quantity' => 'nullable|integer|min:1',
            'return_items.*.unit_price' => 'nullable|numeric|min:0',
            'return_items.*.return_reason' => 'nullable|string',
        ]);
        
        // Validate items manually to handle dynamic keys
        $items = $request->items;
        if (empty($items) || !is_array($items)) {
            return back()->with('error', 'At least one item is required.');
        }
        
        foreach ($items as $key => $item) {
            if (empty($item['item_name'])) {
                return back()->with('error', "Item name is required for all items.");
            }
            if (empty($item['quantity']) || $item['quantity'] < 1) {
                return back()->with('error', "Valid quantity (minimum 1) is required for all items.");
            }
            if (empty($item['unit_price']) || $item['unit_price'] < 0) {
                return back()->with('error', "Valid unit price is required for all items.");
            }
        }
        
        try {
            DB::beginTransaction();
            
            // 1. Update Customer Information
            $customer = $invoice->customer;
            if ($customer) {
                $customer->update([
                    'name' => $request->customer_name,
                    'phone_number_1' => $request->customer_phone,
                    'full_address' => $request->customer_address,
                ]);
            } else {
                // If no customer exists, create one (fallback)
                $customer = Customer::create([
                    'name' => $request->customer_name,
                    'phone_number_1' => $request->customer_phone,
                    'full_address' => $request->customer_address,
                    'status' => 'active',
                ]);
                $invoice->customer_id = $customer->id;
                $invoice->save();
            }
            
            // 2. Check if has return items
            $hasReturnItems = $request->has_return_items == '1' || $request->has_return_items === true;
            
            // 3. Prepare invoice data for update
            $invoiceData = [
                'delivery_charge' => $request->delivery_charge,
                'status' => $request->status,
                'merchant_order_id' => $request->merchant_order_id,
                'notes' => $request->notes ?? $invoice->notes,
                'special_instructions' => $request->special_instructions ?? $invoice->special_instructions,
                'has_return_items' => $hasReturnItems,
                'recipient_name' => $request->customer_name,
                'recipient_phone' => $request->customer_phone,
                'recipient_address' => $request->customer_address,
                'team_id' => $request->team_id,
            ];
            
            // 4. Update invoice_date only if status has changed
            $oldStatus = $invoice->status;
            $newStatus = $request->status;
            
            if ($oldStatus !== $newStatus) {
                $invoiceData['invoice_date'] = now();
                
                if ($newStatus === 'confirmed') {
                    $invoiceData['confirmed_at'] = now();
                }
            }
            
            // 5. Update invoice
            $invoice->update($invoiceData);
            
            // 6. Update Items
            $existingIds = $invoice->items->pluck('id')->toArray();
            $updatedIds = [];
            $subtotal = 0;
            
            foreach ($items as $itemData) {
                $itemId = $itemData['id'] ?? null;
                $weight = ($itemData['quantity'] * 500);
                $totalPrice = $itemData['quantity'] * $itemData['unit_price'];
                $subtotal += $totalPrice;
                
                if ($itemId && str_starts_with($itemId, 'new_')) {
                    $item = InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'item_name' => $itemData['item_name'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'total_price' => $totalPrice,
                        'weight' => $weight,
                    ]);
                    $updatedIds[] = $item->id;
                } elseif ($itemId && is_numeric($itemId)) {
                    $item = InvoiceItem::find($itemId);
                    if ($item && $item->invoice_id == $invoice->id) {
                        $item->update([
                            'item_name' => $itemData['item_name'],
                            'quantity' => $itemData['quantity'],
                            'unit_price' => $itemData['unit_price'],
                            'total_price' => $totalPrice,
                            'weight' => $weight,
                        ]);
                        $updatedIds[] = $item->id;
                    }
                }
            }
            
            // Delete items that were removed
            if ($request->has('deleted_items')) {
                InvoiceItem::whereIn('id', $request->deleted_items)->delete();
            }
            
            $itemsToDelete = array_diff($existingIds, $updatedIds);
            if (!empty($itemsToDelete)) {
                InvoiceItem::whereIn('id', $itemsToDelete)->delete();
            }
            
            // 7. Update Return Items
            $existingReturnIds = $invoice->returnItems->pluck('id')->toArray();
            $updatedReturnIds = [];
            $returnSubtotal = 0;
            
            if ($hasReturnItems && !empty($request->return_items)) {
                foreach ($request->return_items as $returnItemData) {
                    // Skip if item_name is empty
                    if (empty($returnItemData['item_name'])) {
                        continue;
                    }
                    
                    $returnItemId = $returnItemData['id'] ?? null;
                    $returnWeight = ($returnItemData['quantity'] ?? 1) * 500;
                    $returnTotalPrice = ($returnItemData['quantity'] ?? 1) * ($returnItemData['unit_price'] ?? 0);
                    $returnSubtotal += $returnTotalPrice;
                    
                    if ($returnItemId && str_starts_with($returnItemId, 'new_')) {
                        $returnItem = ReturnItem::create([
                            'invoice_id' => $invoice->id,
                            'item_name' => $returnItemData['item_name'],
                            'description' => $returnItemData['description'] ?? null,
                            'quantity' => $returnItemData['quantity'] ?? 1,
                            'weight' => $returnWeight,
                            'unit_price' => $returnItemData['unit_price'] ?? 0,
                            'total_price' => $returnTotalPrice,
                            'return_reason' => $returnItemData['return_reason'] ?? null,
                            'return_note' => $returnItemData['return_note'] ?? null,
                        ]);
                        $updatedReturnIds[] = $returnItem->id;
                    } elseif ($returnItemId && is_numeric($returnItemId)) {
                        $returnItem = ReturnItem::find($returnItemId);
                        if ($returnItem && $returnItem->invoice_id == $invoice->id) {
                            $returnItem->update([
                                'item_name' => $returnItemData['item_name'],
                                'description' => $returnItemData['description'] ?? null,
                                'quantity' => $returnItemData['quantity'] ?? 1,
                                'weight' => $returnWeight,
                                'unit_price' => $returnItemData['unit_price'] ?? 0,
                                'total_price' => $returnTotalPrice,
                                'return_reason' => $returnItemData['return_reason'] ?? null,
                                'return_note' => $returnItemData['return_note'] ?? null,
                            ]);
                            $updatedReturnIds[] = $returnItem->id;
                        }
                    }
                }
            }
            
            // Delete return items that were removed
            if ($request->has('deleted_return_items')) {
                ReturnItem::whereIn('id', $request->deleted_return_items)->delete();
            }
            
            $returnItemsToDelete = array_diff($existingReturnIds, $updatedReturnIds);
            if (!empty($returnItemsToDelete)) {
                ReturnItem::whereIn('id', $returnItemsToDelete)->delete();
            }
            
            // 8. Calculate final totals (subtotal - return subtotal)
            $finalSubtotal = $subtotal - $returnSubtotal;
            $deliveryCharge = $request->delivery_charge;
            $total = $finalSubtotal + $deliveryCharge;
            
            // Update invoice totals
            $invoice->update([
                'subtotal' => $finalSubtotal,
                'total' => $total,
            ]);
            
            // Update due_amount if needed
            if ($invoice->payment_status !== 'paid') {
                $amountDue = $total - $invoice->paid_amount;
                $invoice->update(['due_amount' => $amountDue]);
            }
            
            DB::commit();
            
            $statusMessage = '';
            if ($oldStatus !== $newStatus) {
                $statusMessage = " Status changed from '{$oldStatus}' to '{$newStatus}' and invoice date updated.";
            }
            
            return redirect()->route('admin.invoices.show', $invoice->id)
                ->with('success', 'Invoice and customer updated successfully!' . $statusMessage);
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Invoice update error: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'Failed to update invoice: ' . $e->getMessage());
        }
    }
    public function update(Request $request, $id)
{
    $invoice = Invoice::with(['items', 'returnItems'])->findOrFail($id);
    $oldData = $this->getInvoiceDataForTracking($invoice);
 
    $validated = $request->validate([
        // Recipient / customer
        'recipient_name' => 'required|string|max:255',
        'recipient_phone' => 'required|string|max:20',
        'recipient_secondary_phone' => 'nullable|string|max:20',
        'recipient_address' => 'nullable|string|max:500',
        'merchant_order_id' => 'nullable|string|max:255',
 
        // Delivery
        'delivery_area' => 'required_unless:is_inhouse_sale,1|nullable|string',
        'delivery_city' => 'nullable|string',
        'delivery_zone' => 'nullable|string',
        'delivery_area_id' => 'nullable|string',
        'delivery_type' => 'required|string',
        'store_location' => 'required|string',
        'delivery_charge' => 'required|numeric|min:0',
        'courier_name' => 'nullable|string|in:Pathao,Steadfast,SA,SUNDORBAN,JANONI,REDEX,Exchange',
 
        // Flags
        'is_wholesale' => 'nullable|boolean',
        'is_inhouse_sale' => 'nullable|boolean',
        'team_id' => 'nullable|exists:users,id',
        'status' => 'required|string|in:confirmed,pending,cancelled',
 
        // Payment
        'payment_method' => 'nullable|string|in:bkash,bkash_personal,bank_transfer,cash',
        'paid_amount' => 'nullable|numeric|min:0',
        'amount_to_collect' => 'nullable|numeric|min:0',
 
        // Notes
        'special_instructions' => 'nullable|string|max:1000',
        'notes' => 'nullable|string',
 
        // Return items
        'has_return_items' => 'nullable|boolean',
        'return_items' => 'nullable|array',
        'return_items.*.item_name' => 'nullable|string',
        'return_items.*.quantity' => 'nullable|integer|min:1',
        'return_items.*.unit_price' => 'nullable|numeric|min:0',
        'return_items.*.return_reason' => 'nullable|string',
    ]);
 
    $items = $request->items;
    if (empty($items) || !is_array($items)) {
        return back()->with('error', 'At least one item is required.')->withInput();
    }
 
    foreach ($items as $item) {
        if (empty($item['item_name'])) {
            return back()->with('error', 'Item name is required for all items.')->withInput();
        }
        if (empty($item['quantity']) || $item['quantity'] < 1) {
            return back()->with('error', 'Valid quantity (minimum 1) is required for all items.')->withInput();
        }
        if (!isset($item['unit_price']) || $item['unit_price'] < 0) {
            return back()->with('error', 'Valid unit price is required for all items.')->withInput();
        }
    }
 
    try {
        DB::beginTransaction();
 
        $isInhouseSale = $request->has('is_inhouse_sale') ? (bool) $request->is_inhouse_sale : false;
        $isWholesale = $request->has('is_wholesale') ? (bool) $request->is_wholesale : false;
        $hasReturnItems = $request->has_return_items == '1' || $request->has_return_items === true;
 
        // 1. Update / create linked Customer
        $customer = $invoice->customer;
        if ($customer) {
            $customer->update([
                'name' => $request->recipient_name,
                'phone_number_1' => $request->recipient_phone,
                'phone_number_2' => $request->recipient_secondary_phone,
                'full_address' => $request->recipient_address,
                'delivery_area' => $isInhouseSale ? $customer->delivery_area : $request->delivery_area,
                'note' => $request->notes,
            ]);
        } else {
            $customer = Customer::create([
                'name' => $request->recipient_name,
                'phone_number_1' => $request->recipient_phone,
                'phone_number_2' => $request->recipient_secondary_phone,
                'full_address' => $request->recipient_address,
                'delivery_area' => $isInhouseSale ? null : $request->delivery_area,
                'status' => 'active',
            ]);
            $invoice->customer_id = $customer->id;
        }
 
        // 2. Prepare invoice data for update
        $invoiceData = [
            'recipient_name' => $request->recipient_name,
            'recipient_phone' => $request->recipient_phone,
            'recipient_secondary_phone' => $request->recipient_secondary_phone,
            'recipient_address' => $request->recipient_address,
            'merchant_order_id' => $request->merchant_order_id,
            'delivery_area' => $isInhouseSale ? null : $request->delivery_area,
            'delivery_type' => $request->delivery_type,
            'store_location' => $request->store_location,
            'delivery_charge' => $isInhouseSale ? 0 : $request->delivery_charge,
            'pathao_city_id' => $isInhouseSale ? null : $request->delivery_city,
            'pathao_zone_id' => $isInhouseSale ? null : $request->delivery_zone,
            'pathao_area_id' => $isInhouseSale ? null : $request->delivery_area_id,
            'courier_name' => $isInhouseSale ? 'Pathao' : ($request->courier_name ?? 'Pathao'),
            'is_wholesale' => $isWholesale,
            'is_inhouse_sale' => $isInhouseSale,
            'team_id' => $request->team_id,
            'status' => $request->status,
            'payment_method' => $request->payment_method,
            'payment_details' => $this->getPaymentDetails($request),
            'paid_amount' => $request->paid_amount ?? 0,
            'amount_to_collect' => $request->amount_to_collect ?? 0,
            'special_instructions' => $request->special_instructions,
            'notes' => $request->notes,
            'has_return_items' => $hasReturnItems,
        ];
 
        // 3. Update invoice_date / confirmed_at only if status actually changed
        $oldStatus = $invoice->status;
        $newStatus = $request->status;
 if ($request->team_id) {
    session(['recent_team_member_id' => $request->team_id]);
}
        if ($oldStatus !== $newStatus) {
            $invoiceData['invoice_date'] = now();
 
            if ($newStatus === 'confirmed') {
                $invoiceData['confirmed_at'] = now();
            } elseif ($newStatus === 'pending') {
                $invoiceData['confirmed_at'] = null;
            }
        }
 
        $invoice->update($invoiceData);
 
        // 4. Update items
        $existingIds = $invoice->items->pluck('id')->toArray();
        $updatedIds = [];
        $subtotal = 0;
 
        foreach ($items as $itemData) {
            $itemId = $itemData['id'] ?? null;
            $weight = $itemData['weight'] ?? 500;
            $totalPrice = $itemData['quantity'] * $itemData['unit_price'];
            $subtotal += $totalPrice;
 
            if ($itemId && str_starts_with($itemId, 'new_')) {
                $item = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_name' => $itemData['item_name'],
                    'description' => $itemData['description'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $totalPrice,
                    'weight' => $weight,
                ]);
                $updatedIds[] = $item->id;
            } elseif ($itemId && is_numeric($itemId)) {
                $item = InvoiceItem::find($itemId);
                if ($item && $item->invoice_id == $invoice->id) {
                    $item->update([
                        'item_name' => $itemData['item_name'],
                        'description' => $itemData['description'] ?? null,
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'total_price' => $totalPrice,
                        'weight' => $weight,
                    ]);
                    $updatedIds[] = $item->id;
                }
            }
        }
 
        if ($request->has('deleted_items')) {
            InvoiceItem::whereIn('id', $request->deleted_items)->delete();
        }
        $itemsToDelete = array_diff($existingIds, $updatedIds);
        if (!empty($itemsToDelete)) {
            InvoiceItem::whereIn('id', $itemsToDelete)->delete();
        }
 
        // 5. Update return items
        $existingReturnIds = $invoice->returnItems->pluck('id')->toArray();
        $updatedReturnIds = [];
        $returnSubtotal = 0;
 
        if ($hasReturnItems && !empty($request->return_items)) {
            foreach ($request->return_items as $returnItemData) {
                if (empty($returnItemData['item_name'])) {
                    continue;
                }
 
                $returnItemId = $returnItemData['id'] ?? null;
                $returnWeight = $returnItemData['weight'] ?? 500;
                $returnTotalPrice = ($returnItemData['quantity'] ?? 1) * ($returnItemData['unit_price'] ?? 0);
                $returnSubtotal += $returnTotalPrice;
 
                if ($returnItemId && str_starts_with($returnItemId, 'new_')) {
                    $returnItem = ReturnItem::create([
                        'invoice_id' => $invoice->id,
                        'item_name' => $returnItemData['item_name'],
                        'description' => $returnItemData['description'] ?? null,
                        'quantity' => $returnItemData['quantity'] ?? 1,
                        'weight' => $returnWeight,
                        'unit_price' => $returnItemData['unit_price'] ?? 0,
                        'total_price' => $returnTotalPrice,
                        'return_reason' => $returnItemData['return_reason'] ?? null,
                        'return_note' => $returnItemData['return_note'] ?? null,
                    ]);
                    $updatedReturnIds[] = $returnItem->id;
                } elseif ($returnItemId && is_numeric($returnItemId)) {
                    $returnItem = ReturnItem::find($returnItemId);
                    if ($returnItem && $returnItem->invoice_id == $invoice->id) {
                        $returnItem->update([
                            'item_name' => $returnItemData['item_name'],
                            'description' => $returnItemData['description'] ?? null,
                            'quantity' => $returnItemData['quantity'] ?? 1,
                            'weight' => $returnWeight,
                            'unit_price' => $returnItemData['unit_price'] ?? 0,
                            'total_price' => $returnTotalPrice,
                            'return_reason' => $returnItemData['return_reason'] ?? null,
                            'return_note' => $returnItemData['return_note'] ?? null,
                        ]);
                        $updatedReturnIds[] = $returnItem->id;
                    }
                }
            }
        }
 
        if ($request->has('deleted_return_items')) {
            ReturnItem::whereIn('id', $request->deleted_return_items)->delete();
        }
        $returnItemsToDelete = array_diff($existingReturnIds, $updatedReturnIds);
        if (!empty($returnItemsToDelete)) {
            ReturnItem::whereIn('id', $returnItemsToDelete)->delete();
        }
 
        // 6. If return items were unchecked entirely, drop any that remain
        if (!$hasReturnItems && !empty($existingReturnIds)) {
            ReturnItem::whereIn('id', $existingReturnIds)->delete();
            $returnSubtotal = 0;
        }
 
        // 7. Recalculate totals, payment status
        $finalSubtotal = $subtotal - $returnSubtotal;
        $deliveryCharge = $isInhouseSale ? 0 : $request->delivery_charge;
        $total = $finalSubtotal + $deliveryCharge;
        $paidAmount = $request->paid_amount ?? 0;
        $dueAmount = max(0, $total - $paidAmount);
 
        $paymentStatus = 'unpaid';
        if ($dueAmount <= 0) {
            $paymentStatus = 'paid';
        } elseif ($paidAmount > 0) {
            $paymentStatus = 'partial';
        }
 
        $invoice->update([
            'subtotal' => $finalSubtotal,
            'total' => $total,
            'due_amount' => $dueAmount,
            'payment_status' => $paymentStatus,
        ]);
 
        // 8. Reload relations so tracking sees the final item/return-item
        // state (items were mutated directly via InvoiceItem::/ReturnItem::,
        // not through $invoice->items, so the relation is stale otherwise).
        $invoice->load(['items', 'returnItems']);
 
        $newData = $this->getInvoiceDataForTracking($invoice);
        $actionType = ($oldData['status'] !== $newData['status']) ? 'status_change' : 'update';
        $changedFields = $this->trackEdit($invoice, $oldData, $newData, $actionType);
        $isAjax = $request->ajax() || $request->wantsJson() || $request->has('is_ajax');
        $invoiceData = $this->getInvoiceDataForTracking($invoice);
        $this->trackEdit($invoice, [], $invoiceData, 'create');
        DB::commit();
 
        $statusMessage = '';
        if ($oldData['status'] !== $newData['status']) {
            $statusMessage = " Status changed from '{$oldData['status']}' to '{$newData['status']}'.";
        }
 
        $scalarChangeCount = count(array_diff_key(
            $changedFields,
            array_flip(['items_changes', 'return_items_changes'])
        ));
        if ($scalarChangeCount > 0) {
            $statusMessage .= " {$scalarChangeCount} field(s) were updated.";
        }
        if (!empty($changedFields['items_changes'])) {
            $statusMessage .= " Items changed.";
        }
        if (!empty($changedFields['return_items_changes'])) {
            $statusMessage .= " Return items changed.";
        }
 
        return redirect()->route('admin.invoices.show', $invoice->id)
            ->with('success', 'Invoice updated successfully!' . $statusMessage);
 
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Invoice update error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
 
        return back()->withInput()
            ->with('error', 'Failed to update invoice: ' . $e->getMessage());
    }
}
    // Delete invoice
    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();
        
        return redirect()->route('admin.invoices.index')
            ->with('success', 'Invoice deleted successfully!');
    }
    public function downloadTodayCSV(Request $request)
    {
        // Get today's date
        $today = Carbon::today()->toDateString();
        
        // Get only CONFIRMED invoices for today with sorting by invoice number
        $invoices = Invoice::whereDate('updated_at', $today)
        ->where('courier_name', 'Pathao')
        ->where('status', 'confirmed')
            ->with('customer', 'items')
            ->orderBy('invoice_number', 'asc') // Add this line for sorting
            ->get();
        
        
        // Check if there are any confirmed invoices for today
        if ($invoices->isEmpty()) {
            return redirect()->back()->with('error', 'No confirmed invoices found for today.');
        }
        
    
        
        foreach ($invoices as $invoice) {
            // Only process confirmed invoices (additional safety check)
            if ($invoice->status !== 'confirmed') {
                continue;
            }
            
            // Parse delivery_area field to extract city, zone, area
            $cityName = '';
            $zoneName = '';
            $areaName = '';
            
            if (!empty($invoice->delivery_area)) {
                $parts = array_map('trim', explode(',', $invoice->delivery_area));
                
                // Get city (first part)
                if (isset($parts[0])) {
                    $cityName = $parts[0];
                }
                
                // Get zone (second part)
                if (isset($parts[1])) {
                    $zoneName = $parts[1];
                }
                
                // Get area (third part and beyond, join back)
                if (count($parts) >= 3) {
                    $areaParts = array_slice($parts, 2);
                    $areaName = implode(', ', $areaParts);
                }
            }
            
            // If we have Pathao IDs, use those instead (higher priority)
            if ($invoice->pathaoCity) {
                $cityName = $invoice->pathaoCity->city_name;
            }
            if ($invoice->pathaoZone) {
                $zoneName = '';
            }
            if ($invoice->pathaoArea) {
                $areaName = '';
            }
            
            // Clean up any trailing commas from area
            $areaName = trim($areaName, ', ');
            
            // Calculate TOTAL quantity and weight for ALL items in this invoice
            $totalQuantity = $invoice->items->sum('quantity');
            $totalWeight = $totalQuantity * 0.5;
            
            // Get item names only (NO descriptions)
            $itemNames = [];
            foreach ($invoice->items as $item) {
                if ($item->item_name) {
                    $itemNames[] = $item->item_name;
                }
            }
            
            // Combine item names (without descriptions)
            $itemDesc = '';
            if (!empty($itemNames)) {
                if (count($itemNames) == 1) {
                    $itemDesc = $itemNames[0];
                } else {
                    $itemDesc = $itemNames[0];
                }
            } else {
                $itemDesc = 'Items';
            }
            
            // Clean fields that might contain newlines
            $cleanAddress = str_replace(["\r", "\n"], ', ', $invoice->recipient_address);
            $cleanAddress = trim(preg_replace('/\s+/', ' ', $cleanAddress));
            
            $cleanInstructions = str_replace(["\r", "\n"], ', ', $invoice->special_instructions);
            $cleanInstructions = trim(preg_replace('/\s+/', ' ', $cleanInstructions));
            
            // Prepare ONE row per invoice
            $row = [
                'Parcel',
                $invoice->store_location,
                $invoice->merchant_order_id ?: '',
                $invoice->recipient_name,
                $invoice->recipient_phone,
                $cleanAddress,
                $cityName,
                $zoneName,
                $areaName,
                $invoice->due_amount,
                $totalQuantity,
                $totalWeight,
                $itemDesc,
                $cleanInstructions
            ];
            
            $csvData[] = $row;
        }
        
        // Generate CSV using fputcsv for proper formatting
        $filename = 'today_invoices_' . $today . '.csv';
        
        return response()->streamDownload(function() use ($csvData) {
            $handle = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel
            fwrite($handle, "\xEF\xBB\xBF");
            
            foreach ($csvData as $row) {
                fputcsv($handle, $row);
            }
            
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
  
     public function downloadTodayCSVExchange(Request $request)
    {
        // Get today's date
        $today = Carbon::today()->toDateString();
        
        // Get only CONFIRMED invoices for today with sorting by invoice number
        $invoices = Invoice::whereDate('updated_at', $today)
        ->where('courier_name', 'Exchange')
        ->where('status', 'confirmed')
            ->with('customer', 'items')
            ->orderBy('invoice_number', 'asc') // Add this line for sorting
            ->get();
        
        
        // Check if there are any confirmed invoices for today
        if ($invoices->isEmpty()) {
            return redirect()->back()->with('error', 'No confirmed invoices found for today.');
        }
        
    
        
        foreach ($invoices as $invoice) {
            // Only process confirmed invoices (additional safety check)
            if ($invoice->status !== 'confirmed') {
                continue;
            }
            
            // Parse delivery_area field to extract city, zone, area
            $cityName = '';
            $zoneName = '';
            $areaName = '';
            
            if (!empty($invoice->delivery_area)) {
                $parts = array_map('trim', explode(',', $invoice->delivery_area));
                
                // Get city (first part)
                if (isset($parts[0])) {
                    $cityName = $parts[0];
                }
                
                // Get zone (second part)
                if (isset($parts[1])) {
                    $zoneName = $parts[1];
                }
                
                // Get area (third part and beyond, join back)
                if (count($parts) >= 3) {
                    $areaParts = array_slice($parts, 2);
                    $areaName = implode(', ', $areaParts);
                }
            }
            
            // If we have Pathao IDs, use those instead (higher priority)
            if ($invoice->pathaoCity) {
                $cityName = $invoice->pathaoCity->city_name;
            }
            if ($invoice->pathaoZone) {
                $zoneName = '';
            }
            if ($invoice->pathaoArea) {
                $areaName = '';
            }
            
            // Clean up any trailing commas from area
            $areaName = trim($areaName, ', ');
            
            // Calculate TOTAL quantity and weight for ALL items in this invoice
            $totalQuantity = $invoice->items->sum('quantity');
            $totalWeight = $totalQuantity * 0.5;
            
            // Get item names only (NO descriptions)
            $itemNames = [];
            foreach ($invoice->items as $item) {
                if ($item->item_name) {
                    $itemNames[] = $item->item_name;
                }
            }
            
            // Combine item names (without descriptions)
            $itemDesc = '';
            if (!empty($itemNames)) {
                if (count($itemNames) == 1) {
                    $itemDesc = $itemNames[0];
                } else {
                    $itemDesc = $itemNames[0];
                }
            } else {
                $itemDesc = 'Items';
            }
            
            // Clean fields that might contain newlines
            $cleanAddress = str_replace(["\r", "\n"], ', ', $invoice->recipient_address);
            $cleanAddress = trim(preg_replace('/\s+/', ' ', $cleanAddress));
            
            $cleanInstructions = str_replace(["\r", "\n"], ', ', $invoice->special_instructions);
            $cleanInstructions = trim(preg_replace('/\s+/', ' ', $cleanInstructions));
            
            // Prepare ONE row per invoice
            $row = [
                'Parcel',
                $invoice->store_location,
                $invoice->merchant_order_id ?: '',
                $invoice->recipient_name,
                $invoice->recipient_phone,
                $cleanAddress,
                $cityName,
                $zoneName,
                $areaName,
                $invoice->due_amount,
                $totalQuantity,
                $totalWeight,
                $itemDesc,
                $cleanInstructions
            ];
            
            $csvData[] = $row;
        }
        
        // Generate CSV using fputcsv for proper formatting
        $filename = 'today_invoices_' . $today . '.csv';
        
        return response()->streamDownload(function() use ($csvData) {
            $handle = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel
            fwrite($handle, "\xEF\xBB\xBF");
            
            foreach ($csvData as $row) {
                fputcsv($handle, $row);
            }
            
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

public function downloadCustomCSV(Request $request)
{
    try {
        // Validate input
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);
        
        // Parse date and times
        $date = Carbon::parse($request->date);
        $startTime = Carbon::parse($request->date . ' ' . $request->start_time);
        $endTime = Carbon::parse($request->date . ' ' . $request->end_time);
        
        // Get confirmed invoices for the selected time range
        // IMPORTANT: Remove the non-existent relationships (pathaoCity, pathaoZone, pathaoArea)
        $invoices = Invoice::whereBetween('updated_at', [$startTime, $endTime])
            ->where('status', 'confirmed')
                    ->where('courier_name', 'Pathao')

            ->whereNull('deleted_at')
            ->with(['customer', 'items']) // Only load existing relationships
            ->orderBy('invoice_number', 'asc')
            ->get();
        
        // Check if there are any invoices
        if ($invoices->isEmpty()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No confirmed invoices found for the selected time range.'
                ], 404);
            }
            return redirect()->back()->with('error', 'No confirmed invoices found for the selected time range.');
        }
        
        // Generate CSV filename
        $filename = 'invoices_' . $date->format('Y-m-d') . 
                    '_' . str_replace(':', '-', $request->start_time) . 
                    '_to_' . str_replace(':', '-', $request->end_time) . 
                    '.csv';
        
        // Return the CSV as a download response
        return $this->generateCSVResponse($invoices, $filename);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        }
        return redirect()->back()
            ->withErrors($e->validator)
            ->withInput()
            ->with('error', 'Validation failed: ' . $e->getMessage());
    } catch (\Exception $e) {
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate CSV: ' . $e->getMessage()
            ], 500);
        }
        return redirect()->back()->with('error', 'Failed to generate CSV: ' . $e->getMessage());
    }
}
private function generateCSVResponse($invoices, $filename)
{
    $csvData = [];
    
    foreach ($invoices as $invoice) {
        // Parse delivery_area field to extract city, zone, area
        $cityName = '';
        $zoneName = '';
        $areaName = '';
        
        if (!empty($invoice->delivery_area)) {
            $parts = array_map('trim', explode(',', $invoice->delivery_area));
            
            // Get city (first part)
            if (isset($parts[0])) {
                $cityName = $parts[0];
            }
            
            // Get zone (second part)
            if (isset($parts[1])) {
                $zoneName = $parts[1];
            }
            
            // Get area (third part and beyond, join back)
            if (count($parts) >= 3) {
                $areaParts = array_slice($parts, 2);
                $areaName = implode(', ', $areaParts);
            }
        }
        
        // Remove Pathao relationship code since it doesn't exist
        
        // Clean up any trailing commas from area
        $areaName = trim($areaName, ', ');
        
        // Calculate TOTAL quantity and weight for ALL items in this invoice
        $totalQuantity = $invoice->items->sum('quantity');
        $totalWeight = $totalQuantity * 0.5;
        
        // Get item names only (NO descriptions)
        $itemNames = [];
        foreach ($invoice->items as $item) {
            if ($item->item_name) {
                $itemNames[] = $item->item_name;
            }
        }
        
        // Combine item names (without descriptions)
        $itemDesc = '';
        if (!empty($itemNames)) {
            if (count($itemNames) == 1) {
                $itemDesc = $itemNames[0];
            } else {
                $itemDesc = $itemNames[0];
            }
        } else {
            $itemDesc = 'Items';
        }
        
        // Clean fields that might contain newlines
        $cleanAddress = str_replace(["\r", "\n"], ', ', $invoice->recipient_address);
        $cleanAddress = trim(preg_replace('/\s+/', ' ', $cleanAddress));
        
        $cleanInstructions = str_replace(["\r", "\n"], ', ', $invoice->special_instructions);
        $cleanInstructions = trim(preg_replace('/\s+/', ' ', $cleanInstructions));
        
        // Prepare ONE row per invoice
        $row = [
            'Parcel',
            $invoice->store_location,
            $invoice->merchant_order_id ?: '',
            $invoice->recipient_name,
            $invoice->recipient_phone,
            $cleanAddress,
            $cityName,
            $zoneName,
            $areaName,
            $invoice->due_amount,
            $totalQuantity,
            $totalWeight,
            $itemDesc,
            $cleanInstructions
        ];
        
        $csvData[] = $row;
    }
    
    return response()->streamDownload(function() use ($csvData) {
        $handle = fopen('php://output', 'w');
        
        // Add UTF-8 BOM for Excel
        fwrite($handle, "\xEF\xBB\xBF");
        
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        
        fclose($handle);
    }, $filename, [
        'Content-Type' => 'text/csv; charset=utf-8',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
    ]);
}


public function checkPhoneToday($phone)
{
    try {
        // Clean phone number
        $cleanPhone = preg_replace('/\D/', '', $phone);
        
        // Get today's date
        $today = Carbon::today()->toDateString();
        
        // Check if phone has any invoices today
        $invoicesToday = Invoice::whereDate('invoice_date', $today)
            ->where(function($query) use ($cleanPhone) {
                $query->where('recipient_phone', 'like', '%' . $cleanPhone . '%')
                      ->orWhere('recipient_secondary_phone', 'like', '%' . $cleanPhone . '%');
            })
            ->with('customer')
            ->get();
        
        $hasInvoiceToday = $invoicesToday->count() > 0;
        
        return response()->json([
            'success' => true,
            'has_invoice_today' => $hasInvoiceToday,
            'invoice_count' => $invoicesToday->count(),
            'invoices' => $invoicesToday->map(function($invoice) {
                return [
                    'invoice_number' => $invoice->invoice_number,
                    'recipient_name' => $invoice->recipient_name,
                    'amount' => $invoice->due_amount,
                    'created_at' => $invoice->created_at->format('h:i A'),
                ];
            }),
            'last_invoice' => $invoicesToday->count() > 0 
                ? $invoicesToday->first()->invoice_number . ' at ' . $invoicesToday->first()->created_at->format('h:i A')
                : null,
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Check phone today error: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'error' => 'Error checking today\'s invoices'
        ], 500);
    }
}


public function checkPhoneLastDays($phone)
{
    try {
        $phone = preg_replace('/\D/', '', $phone);
        
        if (!preg_match('/^01[3-9]\d{8}$/', $phone)) {
            return response()->json(['error' => 'Invalid phone number'], 400);
        }
        
        $days = request()->get('days', 4);
        
        // Get today's date
        $today = now()->format('Y-m-d');
        $yesterday = now()->subDay()->format('Y-m-d');
        $dayBefore = now()->subDays(2)->format('Y-m-d');
        
        // Check invoices for each day
        $todayInvoices = Invoice::whereHas('customer', function($q) use ($phone) {
                $q->where('phone_number_1', $phone)
                  ->orWhere('phone_number_2', $phone);
            })
            ->whereDate('invoice_date', $today)
            ->get(['id', 'invoice_number', 'total', 'status']);
        
        $yesterdayInvoices = Invoice::whereHas('customer', function($q) use ($phone) {
                $q->where('phone_number_1', $phone)
                  ->orWhere('phone_number_2', $phone);
            })
            ->whereDate('invoice_date', $yesterday)
            ->get(['id', 'invoice_number', 'total', 'status']);
        
        $dayBeforeInvoices = Invoice::whereHas('customer', function($q) use ($phone) {
                $q->where('phone_number_1', $phone)
                  ->orWhere('phone_number_2', $phone);
            })
            ->whereDate('invoice_date', $dayBefore)
            ->get(['id', 'invoice_number', 'total', 'status']);
        
        return response()->json([
            'today' => $todayInvoices->count() > 0,
            'today_count' => $todayInvoices->count(),
            'today_invoices' => $todayInvoices,
            
            'yesterday' => $yesterdayInvoices->count() > 0,
            'yesterday_count' => $yesterdayInvoices->count(),
            'yesterday_invoices' => $yesterdayInvoices,
            
            'day_before' => $dayBeforeInvoices->count() > 0,
            'day_before_count' => $dayBeforeInvoices->count(),
            'day_before_invoices' => $dayBeforeInvoices,
            
            'total_last_3_days' => $todayInvoices->count() + $yesterdayInvoices->count() + $dayBeforeInvoices->count(),
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Check phone last days error: ' . $e->getMessage());
        return response()->json(['error' => 'Server error occurred'], 500);
    }
}


public function updateStatus(Request $request, $id)
{
    try {
        $invoice = Invoice::findOrFail($id);
        
        // Validate status
        $request->validate([
            'status' => 'required|in:confirmed,pending,cancelled',
        ]);
        
        $oldStatus = $invoice->status;
        $newStatus = $request->status;
        
        // Handle pending → confirmed
        if ($oldStatus === 'pending' && $newStatus === 'confirmed') {
            $updateData = [
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'invoice_date' => now(), // Always update invoice_date when confirming
            ];
            
            // Only assign new invoice number if not already assigned
            if (!$invoice->invoice_number) {
                // Generate invoice number using the model's method
                $updateData['invoice_number'] = Invoice::generateUniqueInvoiceNumber();
            }
            // If invoice has a number but was created earlier, regenerate with today's date
            else if ($invoice->invoice_number && 
                     $invoice->created_at && 
                     !$invoice->created_at->isToday()) {
                // Keep the original invoice number in notes or archive it
                $originalNumber = $invoice->invoice_number;
                $updateData['invoice_number'] = Invoice::generateUniqueInvoiceNumber();
                $updateData['notes'] = $invoice->notes . "\nOriginal invoice number: " . $originalNumber . " (converted on " . now()->format('Y-m-d H:i:s') . ")";
            }
            
            $invoice->update($updateData);
            
            // Refresh the invoice to get updated data
            $invoice->refresh();
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice confirmed successfully!',
                'data' => [
                    'status' => $invoice->status,
                    'status_text' => ucfirst($invoice->status),
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date' => $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : null,
                    'payment_status' => $invoice->payment_status,
                ]
            ]);
        }
        // Handle confirmed → pending
        elseif ($oldStatus === 'confirmed' && $newStatus === 'pending') {
            // When reverting to pending, update invoice_date to now
            $invoice->update([
                'status' => 'pending',
                'confirmed_at' => null, // Clear confirmed_at when reverting
                'invoice_date' => now(), // Update invoice_date when status changes
            ]);
            
            $invoice->refresh();
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice status changed back to pending.',
                'data' => [
                    'status' => $invoice->status,
                    'status_text' => ucfirst($invoice->status),
                    'invoice_date' => $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : null,
                    'payment_status' => $invoice->payment_status,
                ]
            ]);
        }
        // Handle pending → cancelled
        elseif ($oldStatus === 'pending' && $newStatus === 'cancelled') {
            $invoice->update([
                'status' => 'cancelled',
                'confirmed_at' => null, // Clear confirmed_at if it exists
                'invoice_date' => now(), // Update invoice_date when status changes
            ]);
            
            $invoice->refresh();
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice cancelled.',
                'data' => [
                    'status' => $invoice->status,
                    'status_text' => ucfirst($invoice->status),
                    'invoice_date' => $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : null,
                    'payment_status' => $invoice->payment_status,
                ]
            ]);
        }
        // Handle confirmed → cancelled
        elseif ($oldStatus === 'confirmed' && $newStatus === 'cancelled') {
            $invoice->update([
                'status' => 'cancelled',
                'confirmed_at' => now(), // Keep confirmed_at for audit
                'invoice_date' => now(), // Update invoice_date when status changes
            ]);
            
            $invoice->refresh();
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice cancelled.',
                'data' => [
                    'status' => $invoice->status,
                    'status_text' => ucfirst($invoice->status),
                    'invoice_date' => $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : null,
                    'payment_status' => $invoice->payment_status,
                ]
            ]);
        }
        // Handle cancelled → pending (reopen)
        elseif ($oldStatus === 'cancelled' && $newStatus === 'pending') {
            $invoice->update([
                'status' => 'pending',
                'confirmed_at' => null, // Clear confirmed_at when reopening
                'invoice_date' => now(), // Update invoice_date when status changes
            ]);
            
            $invoice->refresh();
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice reopened as pending.',
                'data' => [
                    'status' => $invoice->status,
                    'status_text' => ucfirst($invoice->status),
                    'invoice_date' => $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : null,
                    'payment_status' => $invoice->payment_status,
                ]
            ]);
        }
        // Handle cancelled → confirmed (reopen and confirm)
        elseif ($oldStatus === 'cancelled' && $newStatus === 'confirmed') {
            $updateData = [
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'invoice_date' => now(), // Always update invoice_date when confirming
            ];
            
            // Generate new invoice number for reopened invoice
            if (!$invoice->invoice_number) {
                $updateData['invoice_number'] = Invoice::generateUniqueInvoiceNumber();
            } else {
                // Keep original invoice number but add note about reopening
                $originalNumber = $invoice->invoice_number;
                $updateData['invoice_number'] = Invoice::generateUniqueInvoiceNumber();
                $updateData['notes'] = $invoice->notes . "\nReopened from cancelled: Original number " . $originalNumber . " (reopened on " . now()->format('Y-m-d H:i:s') . ")";
            }
            
            $invoice->update($updateData);
            
            $invoice->refresh();
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice reopened and confirmed successfully!',
                'data' => [
                    'status' => $invoice->status,
                    'status_text' => ucfirst($invoice->status),
                    'invoice_number' => $invoice->invoice_number,
                    'invoice_date' => $invoice->invoice_date ? $invoice->invoice_date->format('d M Y') : null,
                    'payment_status' => $invoice->payment_status,
                ]
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Status update not allowed for this transition.',
        ], 400);
        
    } catch (\Exception $e) {
        \Log::error('Invoice status update error: ' . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to update invoice status: ' . $e->getMessage(),
        ], 500);
    }
}

public function checkCustomerStatus($phone)
{
    try {
        // Clean the phone number
        $cleanPhone = preg_replace('/\D/', '', $phone);
        
        // Simple validation
        if (strlen($cleanPhone) < 11) {
            return response()->json([
                'found' => false,
                'message' => 'Invalid phone number length'
            ]);
        }
        
        // Try to find customer - using the actual column names from your model
        $customer = Customer::where('phone_number_1', $cleanPhone)
            ->orWhere('phone_number_2', $cleanPhone)
            ->first();
        
        if (!$customer) {
            return response()->json([
                'found' => false,
                'message' => 'No customer found with this phone number'
            ]);
        }
        
        // Determine status
        $status = $customer->status ?? 'active';
        $isBlocked = false;
        
        // Check status
        if (in_array(strtolower($status), ['inactive', 'blocked'])) {
            $isBlocked = true;
        }
        
        return response()->json([
            'found' => true,
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone_number_1,
            'status' => $status,
            'is_blocked' => $isBlocked,
            'note' => $customer->note, // Singular 'note' as per your model
            'created_at' => optional($customer->created_at)->format('Y-m-d'),
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Check customer status error: ' . $e->getMessage());
        
        return response()->json([
            'error' => 'Server error occurred',
            'message' => $e->getMessage(),
            'found' => false
        ], 500);
    }
}
}
