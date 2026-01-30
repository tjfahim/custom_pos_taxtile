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
        $faker = Faker::create();

        // Create 12 customers with Faker
        for ($i = 1; $i <= 12; $i++) {
            $status = $faker->randomElement(['active', 'inactive']);
            $hasSecondaryPhone = $faker->boolean(60); // 60% chance
            $hasNote = $faker->boolean(70); // 70% chance
            
            Customer::create([
                'name' => $faker->name(),
                'full_address' => $faker->address(),
                'phone_number_1' => $faker->phoneNumber(),
                'phone_number_2' => $hasSecondaryPhone ? $faker->phoneNumber() : null,
                'note' => $hasNote ? $faker->sentence($faker->numberBetween(5, 15)) : null,
                'status' => $status,
                'created_at' => $faker->dateTimeBetween('-2 years', 'now'),
                'updated_at' => $faker->dateTimeBetween('-1 year', 'now'),
            ]);
        }

        $this->command->info('✅ 12 random customers created using Faker!');
    }
}
