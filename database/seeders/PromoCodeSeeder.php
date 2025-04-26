<?php

namespace Database\Seeders;

use App\Models\promoCode;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PromoCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        promoCode::create([
            'pbpc_vid' => 1,
            'pbpc_name' => 'Test Promo Code',
            'pbpc_code' => 'TESTCODE',
            'pbpc_discount_type' => 'percentage',
            'pbpc_value' => 10,
            'pbpc_discount' => 10,
            'pbpc_max_discount' => 100,
            'pbpc_start_date' => now(),
            'pbpc_days' => 30,
            'pbpc_end_date' => now()->addDays(30),
            'pbpc_min_booking_amount' => 100,
            'pbpc_uses_count' => 0,
            'pbpc_description' => 'This is a test promo code.',
            'pbpc_image' => 'public\images\promo_code_dummy.jpg',
            'pbpc_status' => 1,
        ]);
    }
}
