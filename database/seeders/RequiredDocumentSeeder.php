<?php

namespace Database\Seeders;

use App\Models\required_document;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RequiredDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        required_document::create(
            [
                'pbrd_vendor_type' => '1',
                'pbrd_name' => 'certificate/licenceofparlour',
                'pbrd_label' => 'Certificate/Licence of Parlour',
                'pbrd_is_single' => '1',
                'pbrd_required' => '1',
                'pbrd_status' => '1'
            ],
        );
        required_document::create(
            [
                'pbrd_vendor_type' => '1',
                'pbrd_name' => 'businessregistration',
                'pbrd_label' => 'Business Registration',
                'pbrd_is_single' => '1',
                'pbrd_required' => '1',
                'pbrd_status' => '1'
            ],
        );
        required_document::create(
            [
                'pbrd_vendor_type' => '1',
                'pbrd_name' => 'addressproof',
                'pbrd_label' => 'Address Proof',
                'pbrd_is_single' => '1',
                'pbrd_required' => '1',
                'pbrd_status' => '1'
            ],
        );
        required_document::create(
            [
                'pbrd_vendor_type' => '1',
                'pbrd_name' => 'nicfront',
                'pbrd_label' => 'NIC - Front',
                'pbrd_is_single' => '1',
                'pbrd_required' => '1',
                'pbrd_status' => '1'
            ],
        );
        required_document::create(
            [
                'pbrd_vendor_type' => '1',
                'pbrd_name' => 'nicback',
                'pbrd_label' => 'NIC - Back',
                'pbrd_is_single' => '1',
                'pbrd_required' => '1',
                'pbrd_status' => '1'
            ],
        );
        required_document::create(
            [
                'pbrd_vendor_type' => '1',
                'pbrd_name' => 'businesslogo',
                'pbrd_label' => 'Business Logo',
                'pbrd_is_single' => '1',
                'pbrd_required' => '1',
                'pbrd_status' => '1'
            ],
        );
        required_document::create(
            [
                'pbrd_vendor_type' => '1',
                'pbrd_name' => 'photoofparlours',
                'pbrd_label' => 'Photo of Parlours',
                'pbrd_is_single' => '0',
                'pbrd_required' => '1',
                'pbrd_status' => '1'
            ],
        );
        required_document::create(
            [
                'pbrd_vendor_type' => '2',
                'pbrd_name' => 'policeclearance',
                'pbrd_label' => 'Police Clearance',
                'pbrd_is_single' => '1',
                'pbrd_required' => '1',
                'pbrd_status' => '1'
            ],
        );
        required_document::create(
            [
                'pbrd_vendor_type' => '2',
                'pbrd_name' => 'workexperience',
                'pbrd_label' => 'Work Experience',
                'pbrd_is_single' => '0',
                'pbrd_required' => '0',
                'pbrd_status' => '1'
            ],
        );
        required_document::create(
            [
                'pbrd_vendor_type' => '2',
                'pbrd_name' => 'certificates',
                'pbrd_label' => 'Certificates',
                'pbrd_is_single' => '0',
                'pbrd_required' => '0',
                'pbrd_status' => '1'
            ],
        );
        required_document::create(
            [
                'pbrd_vendor_type' => '2',
                'pbrd_name' => 'photographofuser',
                'pbrd_label' => 'Photograph of User',
                'pbrd_is_single' => '1',
                'pbrd_required' => '1',
                'pbrd_status' => '1'
            ],
        );
        required_document::create(
            [
                'pbrd_vendor_type' => '2',
                'pbrd_name' => 'nicfront',
                'pbrd_label' => 'NIC - Front',
                'pbrd_is_single' => '1',
                'pbrd_required' => '1',
                'pbrd_status' => '1'
            ],
        );
        required_document::create(
            [
                'pbrd_vendor_type' => '2',
                'pbrd_name' => 'nicback',
                'pbrd_label' => 'NIC - Back',
                'pbrd_is_single' => '1',
                'pbrd_required' => '1',
                'pbrd_status' => '1'
            ],
        );
        required_document::create(
            [
                'pbrd_vendor_type' => '2',
                'pbrd_name' => 'coverphoto',
                'pbrd_label' => 'Cover Photo',
                'pbrd_is_single' => '1',
                'pbrd_required' => '1',
                'pbrd_status' => '1'
            ]
        );
    }
}