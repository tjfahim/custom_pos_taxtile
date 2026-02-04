<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Carbon\Carbon;
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
            'payment_details' => $request->bkash_transaction ?? $request->bank_transfer_details,
            'notes' => $request->notes,
            'pathao_city_id' => $request->delivery_city_id,
            'pathao_zone_id' => $request->delivery_zone_id,
            'pathao_area_id' => $request->delivery_area_id,
            'invoice_date' => now(),
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
                'print_url' => route('invoices.print', $invoice->id),
                'message' => 'Invoice created successfully!'
            ]);
        }

        return redirect()->route('invoices.print', $invoice->id)
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



    // Print invoice
    public function print($id)
    {
        $invoice = Invoice::with(['customer', 'items'])->findOrFail($id);
        return view('invoices.print', compact('invoice'));
    }

    // Index page with print only
    public function index()
    {
        $invoices = Invoice::with('customer')->latest()->paginate(20);
        return view('invoices.index', compact('invoices'));
    }

    // Show single invoice
    public function show($id)
    {
        $invoice = Invoice::with(['customer', 'items'])->findOrFail($id);
        return view('invoices.show', compact('invoice'));
    }

    // Delete invoice
    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();
        
        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully!');
    }

    
      public function downloadTodayCSV(Request $request)
{
    // Get today's date
    $today = Carbon::today()->toDateString();
    
    // Get all invoices for today
    $invoices = Invoice::whereDate('invoice_date', $today)
        ->with('customer', 'items')
        ->get();
    
    // Check if there are any invoices for today
    if ($invoices->isEmpty()) {
        return redirect()->back()->with('error', 'No invoices found for today.');
    }
    
   
    
 
    
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
        $totalWeight = $totalQuantity * 0.5; // Each item has 0.5 weight
        
        // Get item descriptions - combine all item names/descriptions
        $itemDescriptions = [];
        foreach ($invoice->items as $item) {
            $desc = $item->description ?: $item->item_name;
            if ($desc) {
                $itemDescriptions[] = $desc;
            }
        }
        
        // Combine item descriptions (use first item's description or default)
        $combinedItemDesc = !empty($itemDescriptions) 
            ? implode(', ', $itemDescriptions)
            : 'Invoice Items';
        
        // If there are multiple items, you might want to truncate or format differently
        if (count($itemDescriptions) > 1) {
            // Option 1: Show first item + "and X more"
            $combinedItemDesc = $itemDescriptions[0] . ' and ' . (count($itemDescriptions) - 1) . ' more items';
            
            // Option 2: Show count of items
            // $combinedItemDesc = count($itemDescriptions) . ' items';
            
            // Option 3: Keep all items combined (might be too long)
            // $combinedItemDesc = implode(', ', $itemDescriptions);
        }
        
        // Prepare ONE row per invoice
        $row = [
            'Parcel', // ItemType
            $invoice->store_location, // StoreName
            $invoice->merchant_order_id ?: '', // MerchantOrderId
            $invoice->recipient_name, // RecipientName(*)
            $invoice->recipient_phone, // RecipientPhone(*)
            $invoice->recipient_address, // RecipientAddress(*)
            $cityName, // RecipientCity(*)
            $zoneName, // RecipientZone(*)
            $areaName, // RecipientArea
            $invoice->due_amount, // AmountToCollect(*)
            $totalQuantity, // TOTAL ItemQuantity
            $totalWeight, // TOTAL ItemWeight
            $combinedItemDesc, // Combined ItemDesc
            $invoice->special_instructions // SpecialInstruction
        ];
        
        $csvData[] = $row;
    }
    
    // Generate CSV content with UTF-8 BOM for Excel compatibility
    $csvContent = "\xEF\xBB\xBF"; // UTF-8 BOM
    
    foreach ($csvData as $row) {
        $csvContent .= implode(',', array_map(function($value) {
            // Escape commas and quotes
            $value = str_replace('"', '""', $value);
            // Wrap in quotes if contains comma or double quote
            if (strpos($value, ',') !== false || strpos($value, '"') !== false) {
                $value = '"' . $value . '"';
            }
            return $value;
        }, $row)) . "\n";
    }
    
    // Generate filename
    $filename = 'today_invoices_' . $today . '.csv';
    
    // Return CSV download with UTF-8 charset
    return Response::make($csvContent, 200, [
        'Content-Type' => 'text/csv; charset=utf-8',
        'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        'Pragma' => 'no-cache',
        'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        'Expires' => '0'
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
}
