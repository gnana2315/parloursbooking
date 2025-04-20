<?php

namespace Database\Seeders;

use App\Models\serviceType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {    
        serviceType::create(
            [
                'pbst_name' => 'Hair Cut',
                'pbst_icon' => null,
                'pbst_description' => null,
                'pbst_status' => '1'
            ]
        ); 
        serviceType::create(
            [
                'pbst_name' => 'Facial',
                'pbst_icon' => null,
                'pbst_description' => null,
                'pbst_status' => '1'
            ]
        );
    }
}
