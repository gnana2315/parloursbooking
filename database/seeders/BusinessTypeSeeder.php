<?php

namespace Database\Seeders;
use App\Models\businessType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BusinessTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        businessType::create(
            [
                'pbbt_name' => 'Business',
                'pbbt_icon' => null,
                'pbbt_description' => null,
                'pbbt_status' => '1'
            ]
        );
        
        businessType::create(
            [
                'pbbt_name' => 'Therapist',
                'pbbt_icon' => null,
                'pbbt_description' => null,
                'pbbt_status' => '1'
            ]
        );
    }
}
