<?php

namespace App\Http\Controllers;

use App\Models\DeliveryCharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeliveryChargeController extends Controller
{
    /**
     * Display a listing of the delivery charges.
     */
    public function index()
    {
        $charges = DeliveryCharge::orderBy('from_range', 'asc')->get();
        return view('admin.delivery-charges.index', compact('charges'));
    }

    /**
     * Show the form for creating a new delivery charge.
     */
    public function create()
    {
        return view('admin.delivery-charges.create');
    }

    /**
     * Store a newly created delivery charge in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_range' => 'required|numeric|min:0',
            'to_range' => 'nullable|numeric|min:0|gt:from_range',
            'inside_dhaka_price' => 'required|numeric|min:0',
            'outside_dhaka_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check for overlapping ranges
        $overlap = DeliveryCharge::where(function($q) use ($request) {
                $q->whereBetween('from_range', [$request->from_range, $request->to_range ?? PHP_FLOAT_MAX])
                  ->orWhereBetween('to_range', [$request->from_range, $request->to_range ?? PHP_FLOAT_MAX])
                  ->orWhere(function($sub) use ($request) {
                      $sub->where('from_range', '<=', $request->from_range)
                          ->where(function($s) use ($request) {
                              $s->where('to_range', '>=', $request->to_range)
                                ->orWhereNull('to_range');
                          });
                  });
            })
            ->exists();

        if ($overlap) {
            return redirect()->back()
                ->with('error', 'This range overlaps with an existing delivery charge range!')
                ->withInput();
        }

        DeliveryCharge::create([
            'from_range' => $request->from_range,
            'to_range' => $request->to_range,
            'inside_dhaka_price' => $request->inside_dhaka_price,
            'outside_dhaka_price' => $request->outside_dhaka_price,
        ]);

        return redirect()->route('admin.delivery-charges.index')
            ->with('success', 'Delivery charge created successfully!');
    }

    /**
     * Show the form for editing the specified delivery charge.
     */
    public function edit($id)
    {
        $charge = DeliveryCharge::findOrFail($id);
        return view('admin.delivery-charges.edit', compact('charge'));
    }

    /**
     * Update the specified delivery charge in storage.
     */
    public function update(Request $request, $id)
    {
        $charge = DeliveryCharge::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'from_range' => 'required|numeric|min:0',
            'to_range' => 'nullable|numeric|min:0|gt:from_range',
            'inside_dhaka_price' => 'required|numeric|min:0',
            'outside_dhaka_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check for overlapping ranges (excluding current)
        $overlap = DeliveryCharge::where('id', '!=', $id)
            ->where(function($q) use ($request) {
                $q->whereBetween('from_range', [$request->from_range, $request->to_range ?? PHP_FLOAT_MAX])
                  ->orWhereBetween('to_range', [$request->from_range, $request->to_range ?? PHP_FLOAT_MAX])
                  ->orWhere(function($sub) use ($request) {
                      $sub->where('from_range', '<=', $request->from_range)
                          ->where(function($s) use ($request) {
                              $s->where('to_range', '>=', $request->to_range)
                                ->orWhereNull('to_range');
                          });
                  });
            })
            ->exists();

        if ($overlap) {
            return redirect()->back()
                ->with('error', 'This range overlaps with an existing delivery charge range!')
                ->withInput();
        }

        $charge->update([
            'from_range' => $request->from_range,
            'to_range' => $request->to_range,
            'inside_dhaka_price' => $request->inside_dhaka_price,
            'outside_dhaka_price' => $request->outside_dhaka_price,
        ]);

        return redirect()->route('admin.delivery-charges.index')
            ->with('success', 'Delivery charge updated successfully!');
    }

    /**
     * Remove the specified delivery charge from storage.
     */
    public function destroy($id)
    {
        $charge = DeliveryCharge::findOrFail($id);
        $charge->delete();

        return redirect()->route('admin.delivery-charges.index')
            ->with('success', 'Delivery charge deleted successfully!');
    }

    /**
     * API: Get all active delivery charges
     */
      public function getActiveCharges()
    {
        $charges = DeliveryCharge::getActiveCharges();
        
        // Transform data to remove .00
        $formattedCharges = $charges->map(function($charge) {
            return [
                'id' => $charge->id,
                'from_range' => intval($charge->from_range),
                'to_range' => $charge->to_range !== null ? intval($charge->to_range) : null,
                'inside_dhaka_price' => intval($charge->inside_dhaka_price),
                'outside_dhaka_price' => intval($charge->outside_dhaka_price),
                'range_display' => $charge->range_display,
                'created_at' => $charge->created_at,
                'updated_at' => $charge->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedCharges
        ]);
    }


 
}