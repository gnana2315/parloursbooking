<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = time();
        User::create(
            [
                'pbu_usertype' => '0',
                'pbu_personid' => '0',
                'pbu_name' => 'SuperAdmin',
                'pbu_email' => 'parloursbooking@gmail.com',
                'pbu_email_verified_at' => $timestamp,
                'password' => 'SuperAdmin',
                'pbu_status' => '1',
                'remember_token' => $timestamp
            ]
        );
        
        User::create(
            [
                'pbu_usertype' => '1',
                'pbu_personid' => '1',
                'pbu_name' => 'Admin',
                'pbu_email' => 'info@parloursbooking.com',
                'pbu_email_verified_at' => $timestamp,
                'password' => 'Admin',
                'pbu_status' => '1',
                'remember_token' => $timestamp
            ]
        );
    }
}
