<?php
// app/Http/Controllers/Admin/StaffController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class StaffController extends Controller
{
    /**
     * Display a listing of the staff.
     */
    public function index(Request $request)
    {
        try {
            $search = $request->get('search');
            
            $staff = Staff::search($search)
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);
            
            if ($request->ajax()) {
                return view('admin.staff.partials.table', compact('staff'))->render();
            }
            
            return view('admin.staff.index', compact('staff'));
            
        } catch (\Exception $e) {
            Log::error('Staff index error: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['error' => 'Failed to load staff data'], 500);
            }
            return back()->with('error', 'Failed to load staff data');
        }
    }

    /**
     * Show the form for creating a new staff.
     */
    public function create()
    {
        return view('admin.staff.create');
    }

    /**
     * Store a newly created staff in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'join_at' => 'nullable|date',
            'status' => 'required|in:active,inactive,on_leave',
            'salary' => 'nullable|numeric|min:0',
            'email' => 'nullable|email|unique:staff,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                            ->withErrors($validator)
                            ->withInput();
        }

        try {
            $staff = Staff::create($request->all());
            
            return redirect()->route('admin.staff.index')
                           ->with('success', 'Staff member created successfully!');
                           
        } catch (\Exception $e) {
            Log::error('Staff creation error: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'Failed to create staff member. Please try again.')
                           ->withInput();
        }
    }

    /**
     * Show the form for editing the specified staff.
     */
    public function edit($id)
    {
        try {
            $staff = Staff::findOrFail($id);
            return view('admin.staff.edit', compact('staff'));
        } catch (\Exception $e) {
            Log::error('Staff edit error: ' . $e->getMessage());
            return redirect()->route('admin.staff.index')
                           ->with('error', 'Staff member not found.');
        }
    }

    /**
     * Update the specified staff in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $staff = Staff::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'designation' => 'nullable|string|max:255',
                'join_at' => 'nullable|date',
                'status' => 'required|in:active,inactive,on_leave',
                'salary' => 'nullable|numeric|min:0',
                'email' => 'nullable|email|unique:staff,email,' . $id,
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                                ->withErrors($validator)
                                ->withInput();
            }

            $staff->update($request->all());
            
            return redirect()->route('admin.staff.index')
                           ->with('success', 'Staff member updated successfully!');
                           
        } catch (\Exception $e) {
            Log::error('Staff update error: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'Failed to update staff member. Please try again.')
                           ->withInput();
        }
    }

    /**
     * Remove the specified staff from storage.
     */
    public function destroy($id)
    {
        try {
            $staff = Staff::findOrFail($id);
            $staffName = $staff->name;
            $staff->delete();
            
            return redirect()->route('admin.staff.index')
                           ->with('success', "Staff member '{$staffName}' deleted successfully!");
                           
        } catch (\Exception $e) {
            Log::error('Staff deletion error: ' . $e->getMessage());
            return redirect()->route('admin.staff.index')
                           ->with('error', 'Failed to delete staff member. Please try again.');
        }
    }

    /**
     * Update staff status (AJAX)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $staff = Staff::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:active,inactive,on_leave'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status value'
                ], 422);
            }

            $staff->update(['status' => $request->status]);
            
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'status_badge' => $staff->status_badge
            ]);
            
        } catch (\Exception $e) {
            Log::error('Status update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }
}