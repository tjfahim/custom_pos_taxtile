<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
            
            Customer::create([
                'name' => 'Fahim islam',
                'full_address' => 'Kallayanpur',
                'phone_number_1' => '01798651200',
                'phone_number_2' => null,
                'note' => null,
                'status' => 'active',
            ]);
            Customer::create([
                'name' => 'Riad',
                'full_address' => 'Bogura',
                'phone_number_1' => '01629321145',
                'phone_number_2' => null,
                'note' => null,
                'status' => 'active',
            ]);
     
    }
}
