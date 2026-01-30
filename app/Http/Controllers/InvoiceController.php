<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    
    public function index(Request $request)
{
    $query = Invoice::with('customer')->latest();
    
    // Search functionality
    if ($request->has('search')) {
        $search = $request->input('search');
        $query->where('invoice_number', 'like', "%{$search}%")
              ->orWhereHas('customer', function($q) use ($search) {
                  $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_number_1', 'like', "%{$search}%");
              });
    }
    
    $invoices = $query->paginate(25);
    
    return view('invoices.index', compact('invoices'));
}
    /**
     * Display the specified invoice.
     */
    public function show($id)
    {
        $invoice = Invoice::with(['customer', 'items'])->findOrFail($id);
        
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice.
     */
    public function edit($id)
    {
        $invoice = Invoice::with(['customer', 'items'])->findOrFail($id);
        $customers = Customer::where('status', 'active')->get();
        
        return view('invoices.edit', compact('invoice', 'customers'));
    }

    /**
     * Update the specified invoice in storage.
     */
    public function update(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_date' => 'required|date',
            'paid_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $invoice->update($validated);
        $invoice->calculateTotals();

        return redirect()->route('invoices.show', $invoice->id)
            ->with('success', 'Invoice updated successfully!');
    }

    /**
     * Remove the specified invoice from storage.
     */
    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully!');
    }

    /**
     * Add payment to invoice
     */
    public function addPayment(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
        ]);

        $invoice->update([
            'paid_amount' => $invoice->paid_amount + $validated['amount'],
        ]);
        
        $invoice->calculateTotals();

        return redirect()->back()
            ->with('success', 'Payment added successfully!');
    }
 
}
