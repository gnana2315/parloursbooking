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
                'pbu_vid' => '0',
                'pbu_personid' => '0',
                'pbu_name' => 'SuperAdmin',
                'pbu_email' => 'parloursbooking@gmail.com',
                'pbu_mobileno' => null,
                'pbu_verification_token' => $timestamp,
                'pbu_verification_token_expires_at' => null,
                'pbu_email_verified_at' => $timestamp,
                'pbu_mobileno_verified_at' => null,
                'password' => 'SuperAdmin',
                'pbu_first_name' => 'Super',
                'pbu_last_name' => 'Admin',
                'pbu_dob' => null,
                'pbu_gender' => null,
                'pbu_address' => null,
                'pbu_city' => null,
                'pbu_accept_terms' => '1',
                'pbu_status' => '1',
                'remember_token' => $timestamp
            ]
        );
        
        User::create(
            [
                'pbu_usertype' => '1',
                'pbu_vid' => '1',
                'pbu_personid' => '1',
                'pbu_name' => 'Admin',
                'pbu_email' => 'info@parloursbooking.com',
                'pbu_mobileno' => null,
                'pbu_verification_token' => $timestamp,
                'pbu_verification_token_expires_at' => null,
                'pbu_email_verified_at' => $timestamp,
                'pbu_mobileno_verified_at' => null,
                'password' => 'Admin',
                'pbu_first_name' => 'Admin',
                'pbu_last_name' => 'Admin',
                'pbu_dob' => null,
                'pbu_gender' => null,
                'pbu_address' => null,
                'pbu_city' => null,
                'pbu_accept_terms' => '1',
                'pbu_status' => '1',
                'remember_token' => $timestamp
            ]
        );
        
        User::create(
            [
                'pbu_usertype' => '2',
                'pbu_vid' => '1',
                'pbu_personid' => '1',
                'pbu_name' => 'Vendor',
                'pbu_email' => null,
                'pbu_mobileno' => '0712345676',
                'pbu_verification_token' => $timestamp,
                'pbu_verification_token_expires_at' => null,
                'pbu_email_verified_at' => $timestamp,
                'pbu_mobileno_verified_at' => null,
                'password' => 'Vendor',
                'pbu_first_name' => 'Vendor',
                'pbu_last_name' => 'Vendor',
                'pbu_dob' => null,
                'pbu_gender' => null,
                'pbu_address' => null,
                'pbu_city' => null,
                'pbu_accept_terms' => '1',
                'pbu_status' => '1',
                'remember_token' => $timestamp
            ]
        );

        User::create(
            [
                'pbu_usertype' => '2',
                'pbu_vid' => '2',
                'pbu_personid' => '2',
                'pbu_name' => 'Vendor',
                'pbu_email' => null,
                'pbu_mobileno' => '0712345677',
                'pbu_verification_token' => $timestamp,
                'pbu_verification_token_expires_at' => null,
                'pbu_email_verified_at' => $timestamp,
                'pbu_mobileno_verified_at' => null,
                'password' => 'Vendor',
                'pbu_first_name' => 'Vendor',
                'pbu_last_name' => 'Vendor',
                'pbu_dob' => null,
                'pbu_gender' => null,
                'pbu_address' => null,
                'pbu_city' => null,
                'pbu_accept_terms' => '1',
                'pbu_status' => '1',
                'remember_token' => $timestamp
            ]
        );

        User::create(
            [
                'pbu_usertype' => '2',
                'pbu_vid' => '3',
                'pbu_personid' => '3',
                'pbu_name' => 'Vendor',
                'pbu_email' => null,
                'pbu_mobileno' => '0712345679',
                'pbu_verification_token' => $timestamp,
                'pbu_verification_token_expires_at' => null,
                'pbu_email_verified_at' => $timestamp,
                'pbu_mobileno_verified_at' => null,
                'password' => 'Vendor',
                'pbu_first_name' => 'Vendor',
                'pbu_last_name' => 'Vendor',
                'pbu_dob' => null,
                'pbu_gender' => null,
                'pbu_address' => null,
                'pbu_city' => null,
                'pbu_accept_terms' => '1',
                'pbu_status' => '1',
                'remember_token' => $timestamp
            ]
        );
        
        User::create(
            [
                'pbu_usertype' => '3',
                'pbu_vid' => '1',
                'pbu_personid' => '1',
                'pbu_name' => 'Customer',
                'pbu_email' => null,
                'pbu_mobileno' => '0712345699',
                'pbu_verification_token' => $timestamp,
                'pbu_verification_token_expires_at' => null,
                'pbu_email_verified_at' => $timestamp,
                'pbu_mobileno_verified_at' => null,
                'password' => 'customer',
                'pbu_first_name' => 'Customer',
                'pbu_last_name' => 'Customer',
                'pbu_dob' => null,
                'pbu_gender' => null,
                'pbu_address' => null,
                'pbu_city' => null,
                'pbu_accept_terms' => '1',
                'pbu_status' => '1',
                'remember_token' => $timestamp
            ]
        );
    }
}
