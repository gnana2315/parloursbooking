<?php

namespace Database\Seeders;

use App\Models\services;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        services::create([
            'pbs_vendor_id' => 1,
            'pbs_service_type' => 1,
            'pbs_service_for' => 1,
            'pbs_name' => 'Hair Cut',
            'pbs_description' => 'A stylish haircut to enhance your look.',
            'pbs_duration' => 30,
            'pbs_image' => 'haircut.jpg',
            'pbs_price' => 20.00,
            'pbs_employees' => 2,
            'pbs_status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        services::create([
            'pbs_vendor_id' => 1,
            'pbs_service_type' => 2,
            'pbs_service_for' => 1,
            'pbs_name' => 'Facial',
            'pbs_description' => 'A rejuvenating facial treatment.',
            'pbs_duration' => 45,
            'pbs_image' => 'facial.jpg',
            'pbs_price' => 50.00,
            'pbs_employees' => 3,
            'pbs_status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
