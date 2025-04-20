<?php

namespace Database\Seeders;

use App\Models\serviceFor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceForSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {        
        serviceFor::create(
            [
                'pbsf_name' => 'Men',
                'pbsf_icon' => null,
                'pbsf_description' => null,
                'pbsf_status' => '1'
            ]
        );

        serviceFor::create(
            [
                'pbsf_name' => 'Women',
                'pbsf_icon' => null,
                'pbsf_description' => null,
                'pbsf_status' => '1'
            ]
        );

        serviceFor::create(
            [
                'pbsf_name' => 'Unisex',
                'pbsf_icon' => null,
                'pbsf_description' => null,
                'pbsf_status' => '1'
            ]
        );
    }
}
