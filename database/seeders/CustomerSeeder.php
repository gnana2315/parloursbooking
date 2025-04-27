<?php

namespace Database\Seeders;

use App\Models\customer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        customer::create(
            [
                'pbc_user_id' => 6,
                'pbc_initial' => 'Mr',
                'pbc_first_name' => 'John',
                'pbc_last_name' => 'Peter',
                'pbc_dob' => '1996-04-03',
                'pbc_nic_no' => '9612345678V',
                'pbc_nic_document' => null,
                'pbc_sex' => 'Male',
                'pbc_address' => 'Colombo',
                'pbc_city' => 'Colombo',
                'pbc_email' => null,
                'pbc_contact_no' => '712345699',
                'pbc_accept_terms' => 1,
                'pbc_status' => 1,
            ]
        );
    }
}
