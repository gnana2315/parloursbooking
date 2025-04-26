<?php

namespace Database\Seeders;

use App\Models\banks;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        banks::create(
            [
                'pbb_name' => 'Amana Bank PLC',
                'pbb_status' => '1'
            ]
        );
        banks::create(
            [
                'pbb_name' => 'Bank of Ceylon (BOC)',
                'pbb_status' => '1'
            ]
        );
        banks::create(
            [
                'pbb_name' => 'Cargills Bank PLC',
                'pbb_status' => '1'
            ]
        );
        banks::create(
            [
                'pbb_name' => 'Commercial Bank of Ceylon PLC',
                'pbb_status' => '1'
            ]
        );
        banks::create(
            [
                'pbb_name' => 'DFCC Bank PLC',
                'pbb_status' => '1'
            ]
        );
        banks::create(
            [
                'pbb_name' => 'Habib Bank Ltd',
                'pbb_status' => '1'
            ]
        );
        banks::create(
            [
                'pbb_name' => 'Hatton National Bank PLC (HNB)',
                'pbb_status' => '1'
            ]
        );
        banks::create(
            [
                'pbb_name' => 'National Development Bank PLC (NDB)',
                'pbb_status' => '1'
            ]
        );
        banks::create(
            [
                'pbb_name' => 'Nations Trust Bank PLC (NTB)',
                'pbb_status' => '1'
            ]
        );
        banks::create(
            [
                'pbb_name' => 'Pan Asia Banking Corporation PLC',
                'pbb_status' => '1'
            ]
        );
        banks::create(
            [
                'pbb_name' => "People's Bank",
                'pbb_status' => '1'
            ]
        );
        banks::create(
            [
                'pbb_name' => 'Sampath Bank PLC',
                'pbb_status' => '1'
            ]
        );
        banks::create(
            [
                'pbb_name' => 'Seylan Bank PLC',
                'pbb_status' => '1'
            ]
        );
        banks::create(
            [
                'pbb_name' => 'Standard Chartered Bank',
                'pbb_status' => '1'
            ]
        );
        banks::create(
            [
                'pbb_name' => 'The Hongkong & Shanghai Banking Corporation Ltd (HSBC)',
                'pbb_status' => '1'
            ]
        );
        banks::create(
            [
                'pbb_name' => 'Union Bank of Colombo PLC',
                'pbb_status' => '1'
            ]
        );
        banks::create(
            [
                'pbb_name' => 'National Savings Bank PLC (NSB)',
                'pbb_status' => '1'
            ]
        );
    }
}
