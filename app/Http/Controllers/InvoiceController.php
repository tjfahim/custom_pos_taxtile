<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function pos()
{
    $customers = Customer::where('status', 'active')->latest()->get();
    return view('invoices.pos', compact('customers'));
}
// In InvoiceController.php - update storePos method
public function storePos(Request $request)
{
    $request->validate([
        'customer_id' => 'required|exists:customers,id',
        'recipient_name' => 'required|string|max:255',
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
        // Create invoice
        $invoice = Invoice::create([
            'customer_id' => $request->customer_id,
            'recipient_name' => $request->recipient_name,
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
            'payment_details' => $request->payment_details,
            'notes' => $request->notes,
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
}
// DELETE ALL HTML CODE AFTER THIS LINE!