<?php

namespace App\Http\Controllers;

use App\Models\InsideDhaka;
use App\Models\PathaoCity;
use App\Models\PathaoZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InsideDhakaController extends Controller
{
    /**
     * Display a listing of the inside dhaka zones.
     */
    public function index()
    {
        $insideDhaka = InsideDhaka::with('zone.city')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.inside-dhaka.index', compact('insideDhaka'));
    }

    /**
     * Show the form for creating a new inside dhaka zone.
     */
    public function create(Request $request)
    {
        $cities = PathaoCity::orderBy('city_name')->get();
        $addedZones = InsideDhaka::pluck('zone_id')->toArray();
        $zones = collect();
        
        // Only get zones for city_id = 1 (Dhaka)
        $zones = PathaoZone::where('city_id', '1')
            ->orderBy('zone_name')
            ->get();
        
        // Get selected city_id from request or default to 1
        $selectedCityId = $request->has('city_id') ? $request->city_id : '1';
        
        return view('admin.inside-dhaka.create', compact('cities', 'zones', 'addedZones', 'selectedCityId'));
    }

    /**
     * Store a newly created inside dhaka zone in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'zone_id' => 'required|string|exists:pathao_zones,zone_id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if zone already exists
        $exists = InsideDhaka::where('zone_id', $request->zone_id)->exists();
        if ($exists) {
            return redirect()->back()
                ->with('error', 'This zone is already added to Inside Dhaka list!')
                ->withInput();
        }

        InsideDhaka::create([
            'zone_id' => $request->zone_id,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.inside-dhaka.index')
            ->with('success', 'Zone added to Inside Dhaka successfully!');
    }

    /**
     * Show the form for editing the specified inside dhaka zone.
     */
    public function edit($id)
    {
        $insideDhaka = InsideDhaka::with('zone.city')->findOrFail($id);
        $cities = PathaoCity::orderBy('city_name')->get();
        
        // Only get zones for city_id = 1 (Dhaka)
        $zones = PathaoZone::where('city_id', '1')
            ->orderBy('zone_name')
            ->get();
        
        $addedZones = InsideDhaka::where('id', '!=', $id)->pluck('zone_id')->toArray();
        $selectedCityId = '1'; // Default to Dhaka
        
        return view('admin.inside-dhaka.edit', compact('insideDhaka', 'cities', 'zones', 'addedZones', 'selectedCityId'));
    }

    /**
     * Update the specified inside dhaka zone in storage.
     */
    public function update(Request $request, $id)
    {
        $insideDhaka = InsideDhaka::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'zone_id' => 'required|string|exists:pathao_zones,zone_id',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if zone already exists (excluding current)
        $exists = InsideDhaka::where('zone_id', $request->zone_id)
            ->where('id', '!=', $id)
            ->exists();
            
        if ($exists) {
            return redirect()->back()
                ->with('error', 'This zone is already added to Inside Dhaka list!')
                ->withInput();
        }

        $insideDhaka->update([
            'zone_id' => $request->zone_id,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('admin.inside-dhaka.index')
            ->with('success', 'Inside Dhaka zone updated successfully!');
    }

    /**
     * Remove the specified inside dhaka zone from storage.
     */
    public function destroy($id)
    {
        $insideDhaka = InsideDhaka::findOrFail($id);
        $insideDhaka->delete();

        return redirect()->route('admin.inside-dhaka.index')
            ->with('success', 'Zone removed from Inside Dhaka successfully!');
    }

    /**
     * Toggle active status
     */
    public function toggleStatus($id)
    {
        $insideDhaka = InsideDhaka::findOrFail($id);
        $insideDhaka->toggleStatus();

        return redirect()->back()
            ->with('success', 'Status updated successfully!');
    }

    /**
     * API: Get all active inside dhaka zones
     */
    public function getActiveZones()
    {
        $zones = InsideDhaka::getActiveZones();
        
        $formattedZones = $zones->map(function($zone) {
            return [
                'id' => $zone->id,
                'zone_id' => $zone->zone_id,
                'zone_name' => $zone->zone->zone_name ?? 'Unknown',
                'city_name' => $zone->zone->city->city_name ?? 'Unknown',
                'city_id' => $zone->zone->city->city_id ?? null,
                'is_active' => $zone->is_active,
                'display_name' => $zone->display_name,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formattedZones
        ]);
    }

    /**
     * API: Check if zone is inside Dhaka
     */
    public function checkZone($zoneId)
    {
        $isInside = InsideDhaka::isInsideDhaka($zoneId);

        return response()->json([
            'success' => true,
            'data' => [
                'zone_id' => $zoneId,
                'is_inside_dhaka' => $isInside
            ]
        ]);
    }
}