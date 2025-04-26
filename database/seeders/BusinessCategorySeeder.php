<?php

namespace Database\Seeders;

use App\Models\businessCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BusinessCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    { 
        businessCategory::create(
            [
                'pbbc_name' => 'Saloon',
                'pbbc_description' => 'Some text here',
                'pbbc_image' => 'public\images\furniture.png',
                'pbbc_status' => '1'
            ]
        );
        businessCategory::create(
            [
                'pbbc_name' => 'Beauty Parlour',
                'pbbc_description' => 'Some text here',
                'pbbc_image' => 'public\images\brush.png',
                'pbbc_status' => '1'
            ]
        );
        businessCategory::create(
            [
                'pbbc_name' => 'Nail Art',
                'pbbc_description' => 'Some text here',
                'pbbc_image' => 'public\images\manicure.png',
                'pbbc_status' => '1'
            ]
        );
    }
}
