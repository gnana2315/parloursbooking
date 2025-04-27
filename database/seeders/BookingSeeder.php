<?php

namespace Database\Seeders;
use App\Models\booking;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        booking::create(
            [
                'pbb_vendor_id' => 1,
                'pbb_customer_id' => 1,
                'pbb_promo_id' => null,
                'pbb_booking_details' => 'Test',
                'pbb_booking_date' => '2025-04-29',
                'pbb_booking_duration' => '02:00:00',
                'pbb_booking_start_time' => '08:00:00',
                'pbb_booking_end_time' => '10:00:00',
                'pbb_ref_no' => null,
                'pbb_type' => 'Online',
                'pbb_service_location' => null,
                'pbb_contact_no' => null,
                'pbb_status' => 1,
            ]
        );

        booking::create(
            [
                'pbb_vendor_id' => 1,
                'pbb_customer_id' => 1,
                'pbb_promo_id' => null,
                'pbb_booking_details' => 'Test 1',
                'pbb_booking_date' => '2025-04-29',
                'pbb_booking_duration' => '04:00:00',
                'pbb_booking_start_time' => '11:00:00',
                'pbb_booking_end_time' => '15:00:00',
                'pbb_ref_no' => null,
                'pbb_type' => 'Online',
                'pbb_service_location' => null,
                'pbb_contact_no' => null,
                'pbb_status' => 1,
            ]
        );
    }
}
