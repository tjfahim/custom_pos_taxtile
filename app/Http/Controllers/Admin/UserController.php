<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('roles', 'teamMembers', 'defaultTeamMate')->latest()->paginate(20);
        $roles = Role::all();
        
        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        $allUsers = User::where('id', '!=', auth()->id())->get();
        
        return view('admin.users.create', compact('roles', 'allUsers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'status' => 'boolean',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
            'team_members' => 'array',
            'team_members.*' => 'exists:users,id',
            'default_team_mate' => 'nullable|exists:users,id',
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $request->boolean('status'),
        ]);

        // Assign roles
        $user->syncRoles($request->roles);

        // Add team members (excluding self)
        if ($request->has('team_members')) {
            $teamMembers = array_filter($request->team_members, function($id) use ($user) {
                return $id != $user->id;
            });
            $user->teamMembers()->sync($teamMembers);
        }

        // Set default team mate
        if ($request->filled('default_team_mate')) {
            // Check if default team mate is in team members
            $teamMemberIds = $user->teamMembers()->pluck('users.id')->toArray();
            if (in_array($request->default_team_mate, $teamMemberIds)) {
                $user->update(['default_team_mate_id' => $request->default_team_mate]);
            }
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $allUsers = User::where('id', '!=', $user->id)->get();
        $user->load('teamMembers', 'defaultTeamMate');
        
        return view('admin.users.edit', compact('user', 'roles', 'allUsers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id)
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'status' => 'boolean',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
            'team_members' => 'array',
            'team_members.*' => 'exists:users,id',
            'default_team_mate' => 'nullable|exists:users,id',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->boolean('status'),
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);
        $user->syncRoles($request->roles);

        // Update team members (excluding self)
        if ($request->has('team_members')) {
            $teamMembers = array_filter($request->team_members, function($id) use ($user) {
                return $id != $user->id;
            });
            $user->teamMembers()->sync($teamMembers);
        } else {
            $user->teamMembers()->sync([]);
        }

        // Update default team mate
        $teamMemberIds = $user->teamMembers()->pluck('users.id')->toArray();
        if ($request->filled('default_team_mate')) {
            if (in_array($request->default_team_mate, $teamMemberIds)) {
                $user->update(['default_team_mate_id' => $request->default_team_mate]);
            } else {
                $user->update(['default_team_mate_id' => null]);
            }
        } else {
            $user->update(['default_team_mate_id' => null]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account!');
        }

        // Detach from team relationships
        $user->teamMembers()->detach();
        $user->addedByUsers()->detach();
        $user->update(['default_team_mate_id' => null]);

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}