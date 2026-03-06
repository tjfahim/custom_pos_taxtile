<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
class InvoiceController extends Controller
{
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
        'recipient_address' => 'required|string',
        'delivery_area' => 'required|string',
        'delivery_type' => 'required|string',
        'store_location' => 'required|string',
        'delivery_charge' => 'nullable|numeric|min:0',
        'amount_to_collect' => 'nullable|numeric|min:0',
         'status' => 'required|string',
        'paid_amount' => 'nullable|numeric|min:0',
        'items' => 'required|array|min:1',
        'items.*.item_name' => 'required|string',
        'items.*.quantity' => 'required|integer|min:1',
        'items.*.unit_price' => 'required|numeric|min:0',
        'items.*.weight' => 'nullable|integer|min:0',
    ]);

    try {
        // Check if customer exists by phone number
        $customer = Customer::where('phone_number_1', $request->recipient_phone)
            ->orWhere('phone_number_2', $request->recipient_phone)
            ->first();

        // If customer doesn't exist, create new one
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
            // Update existing customer with new information
            $customer->update([
                'name' => $request->recipient_name,
                'full_address' => $request->recipient_address,
                'delivery_area' => $request->delivery_area,
                'note' => $request->notes,
            ]);
            
            // Update secondary phone if provided
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
            'delivery_area' => $request->delivery_area,
            'delivery_type' => $request->delivery_type,
            'store_location' => $request->store_location,
            'delivery_charge' => $request->delivery_charge ?? 60,
            'special_instructions' => $request->special_instructions,
            'product_type' => $request->product_type,
            'amount_to_collect' => $request->amount_to_collect ?? 0,
            'paid_amount' => $request->paid_amount ?? 0,
            'payment_method' => $request->payment_method,
            'payment_details' => $this->getPaymentDetails($request),
            'notes' => $request->notes,
            'pathao_city_id' => $request->delivery_city_id,
            'pathao_zone_id' => $request->delivery_zone_id,
            'pathao_area_id' => $request->delivery_area_id,
            'status' => $request->status,
            'invoice_date' => now(),
            'created_by' => auth()->id(),
            'confirmed_at' => now()
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

        // Calculate totals
        $invoice->calculateTotals();

        // Determine if request is AJAX
        $isAjax = $request->ajax() || $request->wantsJson() || $request->has('is_ajax');
        
        if ($isAjax) {
            return response()->json([
                'success' => true,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'print_url' => route('admin.invoices.print', $invoice->id),
                'message' => 'Invoice created successfully!'
            ]);
        }

        return redirect()->route('admin.invoices.print', $invoice->id)
            ->with('success', 'Invoice created successfully!');
            
    } catch (\Exception $e) {
        \Log::error('Invoice creation error: ' . $e->getMessage());
        
        $isAjax = $request->ajax() || $request->wantsJson() || $request->has('is_ajax');
        
        if ($isAjax) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating invoice: ' . $e->getMessage()
            ], 500);
        }
        
        return back()->with('error', 'Error creating invoice: ' . $e->getMessage());
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
    // app/Http/Controllers/InvoiceController.php

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
                'total' => '৳' . number_format($invoice->total, 2),
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
    $invoice = Invoice::with(['customer', 'items'])->findOrFail($id);
    return view('invoices.edit', compact('invoice'));
}

public function update(Request $request, $id)
{
    $invoice = Invoice::with('items')->findOrFail($id);
    
    // Manual validation to handle dynamic array indices
    $validated = $request->validate([
        'delivery_charge' => 'required|numeric|min:0',
        'status' => 'required|string|in:confirmed,pending,cancelled', 
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
        
        // First, update the invoice with delivery charge and status
        $invoice->update([
            'delivery_charge' => $request->delivery_charge,
            'status' => $request->status, // Add status update
            'merchant_order_id' => $request->merchant_order_id, 
            'notes' => $request->notes ?? $invoice->notes,
        ]);
        
        $existingIds = $invoice->items->pluck('id')->toArray();
        $updatedIds = [];
        
        // Reset totals before recalculating
        $subtotal = 0;
        
        // Process items - use foreach with $item to avoid array index issues
        foreach ($items as $itemData) {
            $itemId = $itemData['id'] ?? null;
            
            // Calculate weight: 0.5kg per item
            $weight = ($itemData['quantity'] * 500); // 500g = 0.5kg per item
            $totalPrice = $itemData['quantity'] * $itemData['unit_price'];
            
            // Add to subtotal
            $subtotal += $totalPrice;
            
            if ($itemId && str_starts_with($itemId, 'new_')) {
                // Create new item
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
                // Update existing item
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
        $itemsToDelete = array_diff($existingIds, $updatedIds);
        if (!empty($itemsToDelete)) {
            InvoiceItem::whereIn('id', $itemsToDelete)->delete();
        }
        
        // Manually update invoice totals to ensure they're correct
        $deliveryCharge = $request->delivery_charge;
        $total = $subtotal + $deliveryCharge;
        
        // Update invoice totals directly
        $invoice->update([
            'subtotal' => $subtotal,
            'total' => $total,
        ]);
        
        // Also update due_amount if needed
        if ($invoice->payment_status !== 'paid') {
            $amountDue = $total - $invoice->paid_amount;
            $invoice->update(['due_amount' => $amountDue]);
        }
        
        DB::commit();
        
        return back()->with('success', 'Invoice updated successfully!');
            
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Invoice update error: ' . $e->getMessage());
        
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
                $zoneName = $invoice->pathaoZone->zone_name;
            }
            if ($invoice->pathaoArea) {
                $areaName = $invoice->pathaoArea->area_name;
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
        \Log::error('CSV Download Error: ' . $e->getMessage());
        \Log::error($e->getTraceAsString());
        
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
        
        $days = request()->get('days', 3);
        
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
                'confirmed_at' => now()
            ];
            
            // Only assign new invoice number if not already assigned
            if (!$invoice->invoice_number) {
                // Generate invoice number using the model's method
                $updateData['invoice_number'] = Invoice::generateUniqueInvoiceNumber();
                $updateData['invoice_date'] = now();
            }
            // If invoice has a number but was created earlier, regenerate with today's date
            else if ($invoice->invoice_number && 
                     $invoice->created_at && 
                     !$invoice->created_at->isToday()) {
                // Keep the original invoice number in notes or archive it
                $originalNumber = $invoice->invoice_number;
                $updateData['invoice_number'] = Invoice::generateUniqueInvoiceNumber();
                $updateData['invoice_date'] = now();
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
            // When reverting to pending, we need to decide who gets credit?
            $invoice->update([
                'status' => 'pending',
                'confirmed_at' => now(),
            ]);
            
            $invoice->refresh();
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice status changed back to pending.',
                'data' => [
                    'status' => $invoice->status,
                    'status_text' => ucfirst($invoice->status),
                    'payment_status' => $invoice->payment_status,
                ]
            ]);
        }
        // Handle cancelled
        elseif ($newStatus === 'cancelled') {
            $invoice->update([
                'status' => 'cancelled',
                'confirmed_at' => now(),
            ]);
            
            $invoice->refresh();
            
            return response()->json([
                'success' => true,
                'message' => 'Invoice cancelled.',
                'data' => [
                    'status' => $invoice->status,
                    'status_text' => ucfirst($invoice->status),
                    'payment_status' => $invoice->payment_status,
                ]
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Status update not allowed',
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
