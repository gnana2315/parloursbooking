<?php

namespace Database\Seeders;

use App\Models\vendorType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VendorTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        vendorType::create(
            [
                'pbvt_name' => 'Business',
                'pbvt_icon' => null,
                'pbvt_description' => null,
                'pbvt_status' => '1'
            ]
        );
        
        vendorType::create(
            [
                'pbvt_name' => 'Therapist',
                'pbvt_icon' => null,
                'pbvt_description' => null,
                'pbvt_status' => '1'
            ]
        );
    }
}
