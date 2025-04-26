<?php

namespace Database\Seeders;

use App\Models\vendors;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        vendors::create([
            'pbv_tenentid' => 1,
            'pbv_servicefor' => 1,
            'pbv_vendortype' => 1,
            'pbv_business_category' => 1,
            'pbv_business_name' => 'ABC Salon',
            'pbv_documents' => 'document.pdf',
            'pbv_brno' => 'BR123456',
            'pbv_email' => null,
            'pbv_contactno' => '1234567890',
            'pbv_address' => '123 Main St',
            'pbv_city' => 'New York',
            'pbv_longatitude' => '40.7128',
            'pbv_latitude' => '-74.0060',
            'pbv_accept_terms' => 1,
            'pbv_status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        vendors::create([
            'pbv_tenentid' => 1,
            'pbv_servicefor' => 2,
            'pbv_vendortype' => 2,
            'pbv_business_category' => 2,
            'pbv_business_name' => 'XYZ Spa',
            'pbv_documents' => 'document.pdf',
            'pbv_brno' => 'BR654321',
            'pbv_email' => null,
            'pbv_contactno' => '0987654321',
            'pbv_address' => '456 Elm St',
            'pbv_city' => 'Los Angeles',
            'pbv_longatitude' => '34.0522',
            'pbv_latitude' => '-118.2437',
            'pbv_accept_terms' => 1,
            'pbv_status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
        vendors::create([
            'pbv_tenentid' => 1,
            'pbv_servicefor' => 3,
            'pbv_vendortype' => 3,
            'pbv_business_category' => 3,
            'pbv_business_name' => 'LMN Fitness',
            'pbv_documents' => 'document.pdf',
            'pbv_brno' => 'BR789012',
            'pbv_email' => null,
            'pbv_contactno' => '1122334455',
            'pbv_address' => '789 Oak St',
            'pbv_city' => 'Chicago',
            'pbv_longatitude' => '41.8781',
            'pbv_latitude' => '-87.6298',
            'pbv_accept_terms' => 1,
            'pbv_status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);
    }
}
