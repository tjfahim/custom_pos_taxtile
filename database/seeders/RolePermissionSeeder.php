<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Clear all existing roles and permissions
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create permissions
        $permissions = [
            // Invoice permissions
            'view invoices',
            'create invoices',
            'edit invoices',
            'delete invoices',
            'print invoices',
            
            // Customer permissions
            'view customers',
            'create customers',
            'edit customers',
            'delete customers',
            
            // Report permissions
            'view reports',
            'export reports',
            
            // User & Role permissions (admin only)
            'manage users',
            'manage roles',
            'manage settings',
            
            // Additional permissions you might want
            'view dashboard',
            'export data',
            'manage system',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Create ONLY admin role
        $admin = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $admin->givePermissionTo(Permission::all()); // Admin gets all permissions

        // Optional: If you want to create a staff role later, you can add it here
        // For now, we'll keep only admin
        
        // Assign admin role to users with role = 1
        $users = \App\Models\User::all();
        foreach ($users as $user) {
            if ($user->role == 1) { // 1 is admin in your old system
                // Remove any existing roles
                $user->roles()->detach();
                // Assign admin role
                $user->assignRole('admin');
                echo "Assigned admin role to: " . $user->email . " (ID: {$user->id})\n";
            } else {
                // Remove all roles from non-admin users
                $user->roles()->detach();
                echo "Removed roles from: " . $user->email . " (ID: {$user->id})\n";
            }
        }

        // Clear cache again
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        echo "\nSeeder completed successfully!\n";
        echo "Only 'admin' role has been created with all permissions.\n";
        echo "All other roles have been removed.\n";
    }
}