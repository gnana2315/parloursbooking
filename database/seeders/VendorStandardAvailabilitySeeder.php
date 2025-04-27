<?php

namespace Database\Seeders;
use App\Models\vendorStandardAvailability;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VendorStandardAvailabilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        vendorStandardAvailability::create(
            [
                'pbvsa_vendor_id' => 1,
                'pbvsa_day' => 'Monday',
                'pbvsa_start_time' => '08:00:00',
                'pbvsa_end_time' => '18:00:00',
                'pbvsa_is_open' => 1,
                'pbvsa_status' => 1,
            ]
        );
        vendorStandardAvailability::create(
            [
                'pbvsa_vendor_id' => 1,
                'pbvsa_day' => 'Tuesday',
                'pbvsa_start_time' => '08:00:00',
                'pbvsa_end_time' => '18:00:00',
                'pbvsa_is_open' => 1,
                'pbvsa_status' => 1,
            ]
        );
        vendorStandardAvailability::create(
            [
                'pbvsa_vendor_id' => 1,
                'pbvsa_day' => 'Wednesday',
                'pbvsa_start_time' => '08:00:00',
                'pbvsa_end_time' => '18:00:00',
                'pbvsa_is_open' => 1,
                'pbvsa_status' => 1,
            ]
        );
        vendorStandardAvailability::create(
            [
                'pbvsa_vendor_id' => 1,
                'pbvsa_day' => 'Thursday',
                'pbvsa_start_time' => '08:00:00',
                'pbvsa_end_time' => '18:00:00',
                'pbvsa_is_open' => 1,
                'pbvsa_status' => 1,
            ]
        );
        vendorStandardAvailability::create(
            [
                'pbvsa_vendor_id' => 1,
                'pbvsa_day' => 'Friday',
                'pbvsa_start_time' => '08:00:00',
                'pbvsa_end_time' => '18:00:00',
                'pbvsa_is_open' => 1,
                'pbvsa_status' => 1,
            ]
        );
    }
}
