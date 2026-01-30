<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@admin.com',
            'role' => 2,
            'email_verified_at' => now(),
            'password' => Hash::make('admin@admin.com'),
            'status' => true,
        ]);

        // Create regular user
        User::create([
            'name' => 'Regular User',
            'email' => 'user@user.com',
            'role' => 1,
            'email_verified_at' => now(),
            'password' => Hash::make('user@user.com'),
            'status' => true,
        ]);

        // Create inactive user
        User::create([
            'name' => 'John Doe',
            'email' => 'user@user2.com',
            'role' => 1,
            'email_verified_at' => null,
            'password' => Hash::make('user@user2.com'),
            'status' => false,
        ]);
    }
}
