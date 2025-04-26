<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call(AdminSeeder::class);
        $this->call(BankSeeder::class);
        $this->call(BusinessCategorySeeder::class);
        $this->call(PromoCodeSeeder::class);
        $this->call(ServiceForSeeder::class);
        $this->call(ServiceSeeder::class);
        $this->call(ServiceTypeSeeder::class);
        $this->call(VendorSeeder::class);
        $this->call(VendorTypeSeeder::class);
    }
}
