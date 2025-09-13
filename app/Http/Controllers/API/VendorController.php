<?php

namespace App\Http\Controllers\API;

// use App\ApiResponseTrait;
use App\Models\vendors;
use App\Models\person;
use App\Models\User;
use App\Models\userLogs;
use App\Models\vendorConfig;
use App\Models\vendorStandardAvailability;
use App\Models\vendorSpecialCloses;
use App\Models\vendorBankInfo;
use App\Models\services;
use App\Models\customer;
use App\Models\requiredDocument;
use App\Models\vendorDocuments;
use Validator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class VendorController extends Controller
{   
    public function vendorRegister(Request $request){
        $user = auth()->user();        
        
        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if($vendor->pbv_vendortype == '1'){
            $request->validate(
                [
                    'business_name' => 'required',
                    'address' => 'required',
                    'city' => 'required',
                    'longatitude' => 'required',
                    'latitude' => 'required',
                    'email' => 'email|unique:vendor,pbv_email'
                    // 'br_no' => 'required'
                ],
                [
                    'business_name.required' => 'Parlour name is required',
                    'address.required' => 'Address is required',
                    'city.required' => 'City is required',
                    'longatitude.required' => 'Location is required',
                    'latitude.required' => 'Location is required',
                    'email.email' => 'Email must be a valid email address',
                    'email.unique' => 'Email already exists'
                    // 'br_no.required' => 'BR No is required'
                ]
            );
            $request->br_no = null;
        }else if ($vendor->pbv_vendortype == '2'){
            $request->validate(
                [
                    'business_category' => 'required',
                    'address' => 'required',
                    'city' => 'required',
                    'email' => 'email|unique:vendor,pbv_email',
                    'nic_no' => 'required'
                ],
                [
                    'business_category.required' => 'Business Category is required',
                    'address.required' => 'Address is required',
                    'city.required' => 'City is required',
                    'email.email' => 'Email must be a valid email address',
                    'email.unique' => 'Email already exists',
                    'nic_no.required' => 'NIC No is required'
                ]
            );
        }else{
            return response()->json(['message' => 'Invalid vendor type'], 400);
        }

        $therapist_name = $user->pbu_first_name . ' ' .$user->pbu_last_name;
        $vendorsUpdate = $vendor->update([ 
            'pbv_tenentid' => 1,
            'pbv_business_name' => ($request->business_type == '1') ? $request->business_name : $therapist_name,
            'pbv_display_name' => ($request->business_type == '1') ? $request->display_name : $therapist_name,
            'pbv_brno' => ($request->business_type == '1') ? $request->br_no : $request->nic_no,
            'pbv_address' => $request->address,
            'pbv_city' => $request->city,
            'pbv_longatitude' => ($request->business_type == '1') ? $request->longatitude : null,
            'pbv_latitude' => ($request->business_type == '1') ? $request->latitude : null,
            'pbv_email' => $request->email,
            'pbv_contactno' => $user->pbu_mobileno,
            'pbv_accept_terms' => 1,
            'pbv_staff_count' => ($request->business_type == '1') ? $request->staff_no : 1,
            'pbv_status' => 0,
        ]);

        if($vendorsUpdate){
            $user->update([
                'pbu_email' => $request->email,
            ]);
            
            $message = 'Vendor Details saved successfully'; 
            $status = 200;
        }else{
            $message = 'Vendor Details failed to save';
            $status = 500;
        }

        return response()->json([
            'message' => $message,
            'data' => $user
        ], $status);
    }

    /**
 * @OA\Post(
 *     path="/api/businessVendorRegister",
 *     summary="Register or update a business vendor",
 *     description="This API allows an authenticated vendor of type `1` (Parlour) to register/update their business details",
 *     tags={"Vendor"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"business_name", "address", "city", "longatitude", "latitude", "email"},
 *             @OA\Property(property="business_name", type="string", example="Beauty Parlour"),
 *             @OA\Property(property="display_name", type="string", example="Parlour Deluxe"),
 *             @OA\Property(property="short_description", type="string", example="A premium beauty parlour offering bridal makeup"),
 *             @OA\Property(property="br_no", type="string", example=null, nullable=true, description="Business Registration No (optional for parlour)"),
 *             @OA\Property(property="address", type="string", example="123 Main Street"),
 *             @OA\Property(property="city", type="string", example="Colombo"),
 *             @OA\Property(property="longatitude", type="number", format="float", example=79.8612),
 *             @OA\Property(property="latitude", type="number", format="float", example=6.9271),
 *             @OA\Property(property="email", type="string", format="email", example="parlour@example.com"),
 *             @OA\Property(property="staff_no", type="integer", example=5)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Vendor details saved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Vendor Details saved successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=10),
 *                 @OA\Property(property="pbu_email", type="string", example="parlour@example.com"),
 *                 @OA\Property(property="pbu_mobileno", type="string", example="+94712345678")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid vendor type or validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Invalid vendor type")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to save vendor details",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Vendor Details failed to save")
 *         )
 *     )
 * )
 */
    public function businessVendorRegister(Request $request){
        $user = auth()->user();        
        
        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if($vendor->pbv_vendortype == '1'){
            $request->validate(
                [
                    'business_name' => 'required',
                    'address' => 'required',
                    'city' => 'required',
                    'longatitude' => 'required',
                    'latitude' => 'required',
                    'email' => 'email|unique:vendor,pbv_email'
                    // 'br_no' => 'required'
                ],
                [
                    'business_name.required' => 'Parlour name is required',
                    'address.required' => 'Address is required',
                    'city.required' => 'City is required',
                    'longatitude.required' => 'Location is required',
                    'latitude.required' => 'Location is required',
                    'email.email' => 'Email must be a valid email address',
                    'email.unique' => 'Email already exists'
                    // 'br_no.required' => 'BR No is required'
                ]
            );
            $request->br_no = null;
        }else{
            return response()->json(['message' => 'Invalid vendor type'], 400);
        }

        $vendorsUpdate = $vendor->update([ 
            'pbv_tenentid' => 1,
            'pbv_business_name' => $request->business_name,
            'pbv_display_name' => !empty($request->display_name) ? $request->display_name : $request->business_name,
            'pbv_short_description' => $request->short_description,
            'pbv_brno' => $request->br_no,
            'pbv_address' => $request->address,
            'pbv_city' => $request->city,
            'pbv_longatitude' => $request->longatitude,
            'pbv_latitude' => $request->latitude,
            'pbv_email' => $request->email,
            'pbv_contactno' => $user->pbu_mobileno,
            'pbv_accept_terms' => 1,
            'pbv_staff_count' => $request->staff_no ?? 1,
            'pbv_status' => 0,
        ]);

        if($vendorsUpdate){
            $user->update([
                'pbu_email' => $request->email,
            ]);
            
            $message = 'Vendor Details saved successfully'; 
            $status = 200;
        }else{
            $message = 'Vendor Details failed to save';
            $status = 500;
        }

        return response()->json([
            'message' => $message,
            'data' => $user
        ], $status);
    }

    /**
 * @OA\Post(
 *     path="/api/therapistVendorRegister",
 *     summary="Register or update therapist vendor",
 *     description="This endpoint allows an authenticated vendor of type `2` (Therapist) to register or update their vendor profile.",
 *     tags={"Vendor"},
 *     security={{"bearerAuth":{}}},
 * 
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"address","city","service_area","email","contact_no","nic_no"},
 *             @OA\Property(property="display_name", type="string", example="Therapist John"),
 *             @OA\Property(property="short_bio", type="string", example="Certified therapist with 5 years of experience."),
 *             @OA\Property(property="nic_no", type="string", example="901234567V"),
 *             @OA\Property(property="address", type="string", example="45 Park Lane"),
 *             @OA\Property(property="city", type="string", example="Kandy"),
 *             @OA\Property(property="service_area", type="string", example="Colombo, Kandy"),
 *             @OA\Property(property="email", type="string", format="email", example="therapist@example.com"),
 *             @OA\Property(property="contact_no", type="string", example="+94712345678")
 *         )
 *     ),
 * 
 *     @OA\Response(
 *         response=200,
 *         description="Therapist vendor details saved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Vendor Details saved successfully"),
 *             @OA\Property(property="data", type="object",
 *                 @OA\Property(property="id", type="integer", example=15),
 *                 @OA\Property(property="pbu_first_name", type="string", example="John"),
 *                 @OA\Property(property="pbu_last_name", type="string", example="Doe"),
 *                 @OA\Property(property="pbu_email", type="string", example="therapist@example.com"),
 *                 @OA\Property(property="pbu_mobileno", type="string", example="+94712345678")
 *             )
 *         )
 *     ),
 * 
 *     @OA\Response(
 *         response=400,
 *         description="Invalid vendor type or validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Invalid vendor type")
 *         )
 *     ),
 * 
 *     @OA\Response(
 *         response=500,
 *         description="Failed to save therapist vendor details",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Vendor Details failed to save")
 *         )
 *     )
 * )
 */
    public function therapistVendorRegister(Request $request){
        $user = auth()->user();        
        
        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        
        if ($vendor->pbv_vendortype == '2'){
            $request->validate(
                [
                    'address' => 'required',
                    'city' => 'required',
                    'service_area' => 'required',
                    'email' => 'email|unique:vendor,pbv_email',
                    'contact_no' => 'required',
                    'nic_no' => 'required'
                ],
                [
                    'address.required' => 'Address is required',
                    'city.required' => 'City is required',
                    'service_area.required' => 'Service area is required',
                    'email.email' => 'Email must be a valid email address',
                    'email.unique' => 'Email already exists',
                    'contact_no.required' => 'Contact No is required',
                    'nic_no.required' => 'NIC No is required'
                ]
            );
        }else{
            return response()->json(['message' => 'Invalid vendor type'], 400);
        }

        $therapist_name = $user->pbu_first_name . ' ' .$user->pbu_last_name;
        $vendorsUpdate = $vendor->update([ 
            'pbv_tenentid' => 1,
            'pbv_business_name' => $therapist_name,
            'pbv_display_name' => $request->display_name ?? $therapist_name,
            'pbv_short_description' => $request->short_bio ?? null,
            'pbv_brno' => $request->nic_no,
            'pbv_address' => $request->address,
            'pbv_city' => $request->city,
            'pbv_therapist_service_area' => $request->service_area ?? null,
            'pbv_longatitude' => null,
            'pbv_latitude' => null,
            'pbv_email' => $request->email,
            'pbv_contactno' => $request->contact_no ?? $user->pbu_mobileno,
            'pbv_accept_terms' => 1,
            'pbv_staff_count' => 1,
            'pbv_status' => 0,
        ]);

        if($vendorsUpdate){
            $user->update([
                'pbu_email' => $request->email,
            ]);
            
            $message = 'Vendor Details saved successfully'; 
            $status = 200;
        }else{
            $message = 'Vendor Details failed to save';
            $status = 500;
        }

        return response()->json([
            'message' => $message,
            'data' => $user
        ], $status);
    }

    /**
 * @OA\Post(
 *     path="/api/vendorDocumentUpdate",
 *     summary="Upload or update vendor documents",
 *     description="Allows a vendor to upload/update required documents. Files are stored and document records are updated.",
 *     operationId="vendorDocumentUpdate",
 *     tags={"Vendor"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(
 *                     property="vendor_document",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(
 *                             property="document_id",
 *                             type="integer",
 *                             example=1,
 *                             description="Required document ID (from required_documents table)"
 *                         ),
 *                         @OA\Property(
 *                             property="document",
 *                             type="string",
 *                             format="binary",
 *                             description="File upload (jpg, jpeg, png, pdf)"
 *                         )
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Documents uploaded successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Documents uploaded successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid request (validation failed)",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Validation error")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Vendor not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Vendor not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Something went wrong")
 *         )
 *     )
 * )
 */
    public function vendorDocumentUpdate(Request $request){
        $user = auth()->user();

        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $request->validate(
            [
                'vendor_document' => 'required|array',
                'vendor_document.*.document_id' => 'required|integer|exists:required_document,pbrd_id',
                'vendor_document.*.document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048'
            ],
            [
                'vendor_document.*.document.required' => 'Document is required',
                'vendor_document.*.document.file' => 'Document must be a file',
                'vendor_document.*.document.mimes' => 'Document must be a file of type: jpg, jpeg, png, pdf',
                'vendor_document.*.document.max' => 'Document may not be greater than 2MB',
            ]
        );

        foreach ($request->vendor_document as $doc) {

            $file = $doc['document'];

            // generate unique filename
            $fileName = time().'_'.$file->getClientOriginalName();

            // store file (change 'public' to 's3' if using AWS S3)
            $filePath = $file->storeAs('uploads/vendors/'.$vendor->pbv_id, $fileName, 'public');

            // full url for access (public disk: storage/app/public/uploads/...)
            $fileUrl = Storage::disk('public')->url($filePath);

            vendorDocuments::updateOrCreate(
                [
                    'pbvd_vendor_id' => $vendor->pbv_id,
                    'pbvd_required_document_id' => $doc['document_id'],
                ],
                [
                    'pbvd_document_name' => $fileName,
                    'pbvd_document_url' => $fileUrl,
                    'pbvd_document_status' => 1
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Documents uploaded successfully'
        ], 200);
    }

    public function vendorDocumentUpdate_v1(Request $request){
        $user = auth()->user();

        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $check_document_upload = vendorDocuments::where([['pbvd_vendor_id', '=', $user->pbu_vid], ['pbvd_required_document_id', '=', $request->document_id]])->first();

        if(!$check_document_upload){
            return response()->json(['message' => 'This document already uploaded'], 404);
        }
        
        $request->validate(
            [
                'document_id' => 'required|integer|exists:required_document,pbrd_id',
                'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048'
            ],
            [
                'document_id.required' => 'Document ID is required',
                'document_id.integer' => 'Document ID must be an integer',
                'document_id.exists' => 'Document ID does not exist',
                'document.required' => 'Document is required',
                'document.file' => 'Document must be a file',
                'document.mimes' => 'Document must be a file of type: jpg, jpeg, png, pdf',
                'document.max' => 'Document may not be greater than 2MB',
            ]
        );

        $file = $request->file('document');

        // generate unique filename
        $fileName = time().'_'.$file->getClientOriginalName();

        // store file (change 'public' to 's3' if using AWS S3)
        $filePath = $file->storeAs('uploads/vendors/'.$vendor->pbv_id, $fileName, 'public');

        // full url for access (public disk: storage/app/public/uploads/...)
        $fileUrl = Storage::disk('public')->url($filePath);

        vendorDocuments::updateOrCreate(
            [
                'pbvd_vendor_id' => $vendor->pbv_id,
                'pbvd_required_document_id' => $request->document_id,
            ],
            [
                'pbvd_document_name' => $fileName,
                'pbvd_document_url' => $fileUrl,
                'pbvd_document_status' => 1
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Documents uploaded successfully'
        ], 200);
    }
    // public function vendorDocumentUpdate(Request $request){

    //     $user = auth()->user();

    //     $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
    //     if (!$vendor) {
    //         return response()->json(['message' => 'Vendor not found'], 404);
    //     }
        
    //     $document_data = [];
        
    //     if($vendor->pbv_vendortype == '1'){
    //         $request->validate(
    //             [
    //                 'certificatelicenceofparlour' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
    //                 'businessregistration' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
    //                 'addressproof' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
    //                 'nicfront' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
    //                 'nicback' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
    //                 'businesslogo' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
    //                 'photoofparlours' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
    //             ],
    //             [
    //                 'certificatelicenceofparlour.required' => 'Certificate/Licence of Parlour document is required',
    //                 'certificatelicenceofparlour.mimes' => 'Certificate/Licence of Parlour document must be a file of type: jpg, jpeg, png, pdf',
    //                 'certificatelicenceofparlour.max' => 'Certificate/Licence of Parlour document may not be greater than 2MB',
    //                 'businessregistration.required' => 'BR document is required',
    //                 'businessregistration.mimes' => 'BR document must be a file of type: jpg, jpeg, png, pdf',
    //                 'businessregistration.max' => 'BR document may not be greater than 2MB',
    //                 'addressproof.required' => 'Address Proof document is required',
    //                 'addressproof.mimes' => 'Address Proof document must be a file of type: jpg, jpeg, png, pdf',
    //                 'addressproof.max' => 'Address Proof document may not be greater than 2MB',
    //                 'nicfront.required' => 'NIC Front document is required',
    //                 'nicfront.mimes' => 'NIC Front document must be a file of type: jpg, jpeg, png, pdf',
    //                 'nicfront.max' => 'NIC Front document may not be greater than 2MB',
    //                 'nicback.required' => 'NIC Back document is required',
    //                 'nicback.mimes' => 'NIC Back document must be a file of type: jpg, jpeg, png, pdf',
    //                 'nicback.max' => 'NIC Back document may not be greater than 2MB',
    //                 'businesslogo.required' => 'Business Logo document is required',
    //                 'businesslogo.mimes' => 'Business Logo document must be a file of type: jpg, jpeg, png, pdf',
    //                 'businesslogo.max' => 'Business Logo document may not be greater than 2MB',
    //                 'photoofparlours.required' => 'Photo of Parlours document is required',
    //                 'photoofparlours.mimes' => 'Photo of Parlours document must be a file of type: jpg, jpeg, png, pdf',
    //                 'photoofparlours.max' => 'Photo of Parlours document may not be greater than 2MB',
    //             ]
    //         );
    //     }else{
    //         $request->validate(
    //             [
    //                 'policeclearance' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
    //                 'workexperience' => 'mimes:jpg,jpeg,png,pdf|max:2048',
    //                 'certificates' => 'mimes:jpg,jpeg,png,pdf|max:2048',
    //                 'photographofuser' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
    //                 'nicfront' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
    //                 'nicback' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
    //                 'coverphoto' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
    //             ],
    //             [
    //                 'policeclearance.required' => 'Police Clearance document is required',
    //                 'policeclearance.mimes' => 'Police Clearance document must be a file of type: jpg, jpeg, png, pdf',
    //                 'policeclearance.max' => 'Police Clearance document may not be greater than 2MB',
    //                 'workexperience.mimes' => 'Work Experience document must be a file of type: jpg, jpeg, png, pdf',
    //                 'workexperience.max' => 'Work Experience document may not be greater than 2MB',
    //                 'certificates.mimes' => 'Certificates document must be a file of type: jpg, jpeg, png, pdf',
    //                 'certificates.max' => 'Certificates document may not be greater than 2MB',
    //                 'photographofuser.required' => 'Photograph of User document is required',
    //                 'photographofuser.mimes' => 'Photograph of User document must be a file of type: jpg, jpeg, png, pdf',
    //                 'photographofuser.max' => 'Photograph of User document may not be greater than 2MB',
    //                 'nicfront.required' => 'NIC Front document is required',
    //                 'nicfront.mimes' => 'NIC Front document must be a file of type: jpg, jpeg, png, pdf',
    //                 'nicfront.max' => 'NIC Front document may not be greater than 2MB',
    //                 'nicback.required' => 'NIC Back document is required',
    //                 'nicback.mimes' => 'NIC Back document must be a file of type: jpg, jpeg, png, pdf',
    //                 'nicback.max' => 'NIC Back document may not be greater than 2MB',
    //                 'coverphoto.required' => 'Cover Photo document is required',
    //                 'coverphoto.mimes' => 'Cover Photo document must be a file of type: jpg, jpeg, png, pdf',
    //                 'coverphoto.max' => 'Cover Photo document may not be greater than 2MB',
    //             ]
    //         );
    //     }
    //     if ($request->hasFile('businessregistration')) {
    //         $businessregistration_file = $request->file('businessregistration');
    //         $businessregistration_filename = $vendor->pbv_business_name . '_' .time() . '_businessregistration.' . $businessregistration_file->getClientOriginalExtension();
    //         $businessregistration_file->move(public_path('uploads/vendors'), $businessregistration_filename);
    //         // $path = $businessregistration_file->storeAs('vendors', $businessregistration_filename, 's3');
    //         // $url = Storage::disk('s3')->url($path);

    //         $request->merge(['businessregistration' => $businessregistration_filename]);
    //         $businessregistration_path = public_path('uploads/vendors') . '/' . $businessregistration_filename;

    //         $document_data[] = [
    //             'businessregistration' => [
    //                 'name' => $businessregistration_filename,
    //                 'path' => $businessregistration_path,
    //             ],
    //         ];
    //     }

    //     if ($request->hasFile('certificatelicenceofparlour')) {
    //         $certificatelicenceofparlour_file = $request->file('certificatelicenceofparlour');
    //         $certificatelicenceofparlour_filename = $vendor->pbv_business_name . '_' .time() . '_certificatelicenceofparlour.' . $certificatelicenceofparlour_file->getClientOriginalExtension();
    //         $certificatelicenceofparlour_file->move(public_path('uploads/vendors'), $certificatelicenceofparlour_filename);
    //         $request->merge(['certificatelicenceofparlour' => $certificatelicenceofparlour_filename]);
    //         $certificatelicenceofparlour_path = public_path('uploads/vendors') . '/' . $certificatelicenceofparlour_filename;
    //         $document_data[] = [
    //             'certificatelicenceofparlour' => [
    //                 'name' => $certificatelicenceofparlour_filename,
    //                 'path' => $certificatelicenceofparlour_path,
    //             ],
    //         ];
    //     }

    //     if ($request->hasFile('addressproof')) {
    //         $addressproof_file = $request->file('addressproof');
    //         $addressproof_filename = $vendor->pbv_business_name . '_' .time() . '_addressproof.' . $addressproof_file->getClientOriginalExtension();
    //         $addressproof_file->move(public_path('uploads/vendors'), $addressproof_filename);
    //         $request->merge(['addressproof' => $addressproof_filename]);
    //         $addressproof_path = public_path('uploads/vendors') . '/' . $addressproof_filename;
    //         $document_data[] = [
    //             'addressproof' => [
    //                 'name' => $addressproof_filename,
    //                 'path' => $addressproof_path,
    //             ],
    //         ];
    //     }

    //     if ($request->hasFile('nicfront')) {
    //         $nic_front_file = $request->file('nicfront');
    //         $nic_front_filename = $vendor->pbv_business_name . '_' .time() . '_nic_front.' . $nic_front_file->getClientOriginalExtension();
    //         $nic_front_file->move(public_path('uploads/vendors'), $nic_front_filename);
    //         $request->merge(['nicfront' => $nic_front_filename]);
    //         $nic_front_path = public_path('uploads/vendors') . '/' . $nic_front_filename;
    //         $document_data[] = [
    //             'nicfront' => [
    //                 'name' => $nic_front_filename,
    //                 'path' => $nic_front_path,
    //             ],
    //         ];
    //     }

    //     if ($request->hasFile('nicback')) {
    //         $nic_back_file = $request->file('nicback');
    //         $nic_back_filename = $vendor->pbv_business_name . '_' .time() . '_nic_back.' . $nic_back_file->getClientOriginalExtension();
    //         $nic_back_file->move(public_path('uploads/vendors'), $nic_back_filename);
    //         $request->merge(['nicback' => $nic_back_filename]);
    //         $nic_back_path = public_path('uploads/vendors') . '/' . $nic_back_filename;
    //         $document_data[] = [
    //             'nicback' => [
    //                 'name' => $nic_back_filename,
    //                 'path' => $nic_back_path,
    //             ],
    //         ];
    //     }

    //     if ($request->hasFile('businesslogo')) {
    //         $business_logo = $request->file('businesslogo');
    //         $business_logo_name = $vendor->pbv_business_name . '_' .time() . '_business_logo.' . $business_logo->getClientOriginalExtension();
    //         $business_logo->move(public_path('uploads/vendors'), $business_logo_name);
    //         $request->merge(['businesslogo' => $business_logo_name]);
    //         $business_logo_path = public_path('uploads/vendors') . '/' . $business_logo_name;
    //         $document_data[] = [
    //             'businesslogo' => [
    //                 'name' => $business_logo_name,
    //                 'path' => $business_logo_path,
    //             ],
    //         ];
    //     }

    //     if ($request->hasFile('photoofparlours')) {
    //         foreach ($request->file('photoofparlours') as $index => $document) {
    //             $photoofparlours_filename = $vendor->pbv_business_name . '_' .time() . '_photoofparlours_' . $index . '.' . $document->getClientOriginalExtension();
    //             $document->move(public_path('uploads/vendors'), $photoofparlours_filename);
    //             $request->merge(['photoofparlours' => $photoofparlours_filename]);
    //             $photoofparlours_path = public_path('uploads/vendors') . '/' . $photoofparlours_filename;
    //             $document_data[] = [
    //                 'photoofparlours' => [
    //                     'name' => $photoofparlours_filename,
    //                     'path' => $photoofparlours_path,
    //                 ],
    //             ];
    //         }
    //     }
    //     $vendor_document_update = $vendor->update([
    //         'pbv_documents' => json_encode($document_data),
    //     ]);

    //     if($vendor_document_update){
    //         $message = 'Vendor Document saved successfully';
    //         $status = 200;
    //     }else{
    //         $message = 'Vendor Document failed to save';
    //         $status = 500;
    //     }

    //     return response()->json([
    //         'message' => $message
    //     ], $status);
    // }

    public function getVendorDocuments(){
        $user = auth()->user();

        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $documents = json_decode($vendor->pbv_documents, true);
        if (!$documents) {
            return response()->json(['message' => 'No documents found'], 404);
        }
        $document_paths = [];
        foreach ($documents as $document) {
            foreach ($document as $key => $value) {
                $document_paths[$key] = [
                    'name' => $value['name'],
                    'path' => asset('uploads/vendors/' . $value['name']),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $document_paths,
        ], 200);
    }

    /**
     * @OA\Post(
     *      path="/api/vendorBankUpdate",
     *      operationId="vendorBankUpdate",
     *      tags={"Vendor"},
     *      summary="Vendor Bank Details",
     *      description="",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={
     *                  "bankid",
     *                  "branch",
     *                  "branch_code",
     *                  "account_holder_name",
     *                  "accountno",
     *              },
     *              @OA\Property(property="bankid", type="text", example="1(HNB)/2(BOC)"),
     *              @OA\Property(property="branch", type="text", example="Colombo"),
     *              @OA\Property(property="branch_code", type="text", example="021"),
     *              @OA\Property(property="account_holder_name", type="text", example="John"),
     *              @OA\Property(property="accountno", type="text", example="2"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Vendor Configuration saved Successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="token", type="string", example="generated_token_here")
     *          ),
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function vendorBankUpdate(Request $request){
        $user = auth()->user();

        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }
        $request->validate(
            [
                'bankid' => 'required',
                'branch' => 'required',
                'branch_code' => 'required',
                'account_holder_name' => 'required',
                'accountno' => 'required|numeric',
                
            ],
            [
                'bankid.required' => 'Please select the Bank',
                'branch.required' => 'Please enter the Branch name',
                'branch_code.required' => 'Please enter the Branch Code',
                'account_holder_name' => 'Please enter the account holder name',
                'accountno.required' => 'Please enter the bank Account No',
                'accountno.numeric' => 'Bank no must be Numeric',
            ]
        );

        $vendorBankInfoUpdate = vendorBankInfo::create([
            'pbvb_vendorid' => $vendor->pbv_id,
            'pbvb_bankname' => $request->bankid,
            'pbvb_branch' => $request->branch,
            'pbvb_branch_code' => $request->branch_code,
            'pbvb_holder_name' => $request->account_holder_name,
            'pbvb_accountno' => $request->accountno,
            'pbvb_is_active' => 1,
            'pbvb_status' => 0
        ]);

        if($vendorBankInfoUpdate){
            $status_code = 200;
            $message = "Vendor Bank Info Updated Successfully";
        }else{
            $status_code = 500;
            $message = "Vendor Bank Info not updated Successfully";
        }

        return response()->json([
            'message' => $message,
        ], $status_code);
    }
    /**
     * @OA\Post(
     *      path="/api/vendorConfig",
     *      operationId="vendorConfig",
     *      tags={"Vendor"},
     *      summary="Vendor Configuration",
     *      description="",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={
     *                  "display_name",
     *                  "business_logo",
     *                  "service_at_time",
     *              },
     *              @OA\Property(property="display_name", type="text", example="CJ Saloon"),
     *              @OA\Property(property="business_logo", type="file", example="logo.png"),
     *              @OA\Property(property="service_at_time", type="file", example="2"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Vendor Configuration saved Successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="token", type="string", example="generated_token_here")
     *          ),
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function vendorConfig(Request $request){
        $user = auth()->user();
        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }
        $business_logo_path = '';

        $request->validate(
            [
                'display_name' => 'required',
                'business_logo' => 'required|mimes:jpg,jpeg,png|max:2048',
                'service_at_time' => 'required',
                
            ],
            [
                'display_name.required' => 'Display name is required',
                'business_logo.required' => 'Business logo is required',
                'business_logo.mimes' => 'Business logo must be a file of type: jpg, jpeg, png',
                'business_logo.max' => 'Business logo may not be greater than 2MB',
                'service_at_time.required' => 'Service capacity is required',
            ]
        );
        
        if ($request->hasFile('business_logo')) {
            $business_logo_file = $request->file('business_logo');
            $business_logo_filename = $vendor->pbv_business_name . '_' .time() . '_business_logo.' . $business_logo_file->getClientOriginalExtension();
            $business_logo_file->move(public_path('uploads/vendors'), $business_logo_filename);
            $request->merge(['business_logo' => $business_logo_filename]);
            $business_logo_path = public_path('uploads/vendors') . '/' . $business_logo_filename;
        }   

        $vendorConfig = vendorConfig::create([
            'pbvc_vendorid' => $vendor->pbv_id,
            'pbvc_display_name' => $request->display_name,
            'pbvc_logo' => $business_logo_path,
            'pbvc_service_at_time' => $request->service_at_time,
        ]);

        if($vendorConfig){
            $message = 'Vendor Configuration updated successfully';
            $status = 200;
        }else{
            $message = 'Vendor Configuration failed to update';
            $status = 500;
        }

        return response()->json([
            'message' => $message
        ], $status);
    }

    /**
     * @OA\Post(
     *      path="/api/vendorAvailability",
     *      operationId="vendorAvailability",
     *      tags={"Vendor"},
     *      summary="Vendor Standard Availability",
     *      description="",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={
     *                  "day",
     *                  "start_time",
     *                  "end_time",
     *                  "is_open",   
     *              },
     *              @OA\Property(property="day", type="text", example="Monday"),
     *              @OA\Property(property="start_time", type="text", example="08:00"),
     *              @OA\Property(property="end_time", type="text", example="17:00"),
     *             @OA\Property(property="is_open", type="boolean", example="1"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Vendor Standard availabilities saved Successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="token", type="string", example="generated_token_here")
     *          ),
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function vendorAvailability(Request $request){
        $user = auth()->user();
        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }
        $request->validate(
            [
                '*.day' => 'required:in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                '*.start_time' => 'required_if:*.is_open,true|date_format:H:i',
                '*.end_time' => 'required_if:*.is_open,true|date_format:H:i|after:*.start_time',
                '*.is_open' => 'required|boolean',
            ],
            [
                '*.day.required' => 'Day is required',
                '*.day.in' => 'Day must be one of the following: Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday',
                '*.start_time.required_if' => 'Start time is required if is_open is true',
                '*.end_time.required_if' => 'End time is required if is_open is true',
                '*.end_time.after' => 'End time must be after start time',
                '*.is_open.required' => 'Is open is required',
                '*.is_open.boolean' => 'Is open must be true or false', 
            ]
        );

        vendorStandardAvailability::where('pbvsa_vendor_id', $vendor->pbv_id)->delete();
        foreach($request->all() as $availability){
            vendorStandardAvailability::create([
                'pbvsa_vendor_id' => $vendor->pbv_id,
                'pbvsa_day' => $availability['day'],
                'pbvsa_start_time' => $availability['start_time'],
                'pbvsa_end_time' => $availability['end_time'],
                'pbvsa_is_open' => $availability['is_open'],
                'pbvsa_status' => 1
            ]);
        }

        return response()->json([
            'message' => 'Vendor Availability updated successfully'
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/getVendorAvailability",
     *     summary="Get vendor availability",
     *     description="Fetches the standard availability details for the authenticated vendor.",
     *     operationId="getVendorAvailability",
     *     tags={"Vendor"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Availability fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="pbvsa_vendor_id", type="integer"),
     *                 @OA\Property(property="day", type="string"),
     *                 @OA\Property(property="start_time", type="string", format="time"),
     *                 @OA\Property(property="end_time", type="string", format="time"),
     *                 @OA\Property(property="is_open", type="integer")
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Vendor not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Vendor not found")
     *         )
     *     ),
     * )
     */

    public function getVendorAvailability(){
        $user = auth()->user();

        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }
        $availabilities = vendorStandardAvailability::where('pbvsa_vendor_id', $vendor->pbv_id)->get();
        return response()->json(['data' => $availabilities], 200);
    }

    /**
     * @OA\Post(
     *      path="/api/vendorSpecialCloses",
     *      operationId="vendorSpecialCloses",
     *      tags={"Vendor"},
     *      summary="Vendor Special Closings",
     *      description="",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={
     *                  "day",
     *                  "start_time",
     *                  "end_time",
     *                  "full_day_closed", 
     *              },
     *              @OA\Property(property="day", type="date", example="2025-10-22"),
     *              @OA\Property(property="start_time", type="text", example="08:00"),
     *              @OA\Property(property="end_time", type="text", example="17:00"),
     *              @OA\Property(property="full_day_closed", type="boolean", example="1"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Vendor Standard availabilities saved Successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="token", type="string", example="generated_token_here")
     *          ),
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     * )
     */

    public function vendorSpecialCloses(Request $request){
        $user = auth()->user();
        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }
        $request->validate(
            [
                '*.day' => 'required',
                '*.full_day_closed' => 'required|boolean',
                '*.from_time' => 'required_if:*.full_day_closed,false|date_format:H:i',
                '*.to_time' => 'required_if:*.full_day_closed,false|date_format:H:i|after:*.from_time',
            ],
            [
                '*.day.required' => 'Day is required',
                '*.full_day_closed.required' => 'Full day closed is required',
                '*.full_day_closed.boolean' => 'Full day closed must be true or false',
                '*.from_time.required_if' => 'From time is required if full day closed is false',
                '*.to_time.required_if' => 'To time is required if full day closed is false',
                '*.to_time.after' => 'To time must be after from time',
            ]
        );

        foreach($request->all() as $special_close){
            vendorSpecialCloses::create([
                'pbvsc_vendor_id' => $vendor->pbv_id,
                'pbvsc_day' => $special_close['day'],
                'pbvsc_full_day_closed' => $special_close['full_day_closed'],
                'pbvsc_from_time' => ($special_close['full_day_closed'] == 0) ? $special_close['from_time'] : null,
                'pbvsc_to_time' => ($special_close['full_day_closed'] == 0) ? $special_close['to_time'] : null,
                'pbvsc_status' => 0
            ]);
        }

        return response()->json([
            'message' => 'Vendor Special Closes requested successfully! We will confirm after validate with your exisiting bookings.'
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/getVendorsSpecificClosings",
     *     summary="Get Vendor Specific Closings",
     *     description="Fetches the specific closing dates for the authenticated vendor.",
     *     operationId="getVendorsSpecificClosings",
     *     tags={"Vendor"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved vendor closings",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="pbvsc_id", type="integer", example=1),
     *                     @OA\Property(property="pbvsc_vendor_id", type="integer", example=9),
     *                     @OA\Property(property="pbvsc_date", type="string", format="date", example="2025-09-15"),
     *                     @OA\Property(property="pbvsc_reason", type="string", example="Holiday"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-09-01T10:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-09-01T10:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Vendor not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Vendor not found")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
 */
    public function getVendorsSpecificClosings(){
        $user = auth()->user();

        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();

        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $availabilities = vendorSpecialCloses::where('pbvsc_vendor_id', $vendor->pbv_id)->get();
        
        return response()->json([
            'success' => true,
            'data' => $availabilities
        ], 200);
    }

    /**
     * @OA\Post(
     *      path="/api/addVendorServices",
     *      operationId="addVendorServices",
     *      tags={"Vendor"},
     *      summary="Add Vendor Services",
     *      description="",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={
     *                 "service_type",
     *                 "service_for",
     *                 "service_name",
     *                 "service_description",
     *                 "service_duration",
     *                 "service_image",
     *                 "service_price",
     *              },
     *              @OA\Property(property="service_type", type="number", example="1"),
     *              @OA\Property(property="service_for", type="number", example="2"),
     *              @OA\Property(property="service_name", type="text", example="Normal Hair Cut"),
     *              @OA\Property(property="service_description", type="text", example="Description"),
     *              @OA\Property(property="service_duration", type="number", example="90 (in minutes)"),
     *              @OA\Property(property="service_image", type="file", example="service_image.png"),    
     *              @OA\Property(property="service_price", type="number", example="2000.00"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Vendor Services saved Successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="token", type="string", example="generated_token_here")
     *          ),
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     * )
     */

    public function addVendorServices(Request $request){
        $user = auth()->user();
        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }
        $service_image_path = '';
        $service_image_filename = '';
        $request->validate(
            [
                'service_type' => 'required',
                'service_for' => 'required',
                'service_name' => 'required',
                'service_description' => 'required',
                'service_duration' => 'required',
                'service_image' => 'mimes:jpg,jpeg,png|max:2048',
                'service_price' => 'required|numeric',
            ],
            [
                'service_type.required' => 'Service type is required',
                'service_for.required' => 'Service for is required',
                'service_name.required' => 'Service name is required',
                'service_description.required' => 'Service description is required',
                'service_duration.required' => 'Service duration is required',
                'service_image.mimes' => 'Service image must be a file of type: jpg, jpeg, png',
                'service_image.max' => 'Service image may not be greater than 2MB',
                'service_price.required' => 'Service price is required',
                'service_price.numeric' => 'Service price must be a number',
            ]
        );        
        
        if ($request->hasFile('service_image')) {
            $service_image_file = $request->file('service_image');
            $folder = 'uploads/services/' . $vendor->pbv_business_name;
            $folderPath = public_path($folder);

            // Create the folder if it doesn't exist
            if (!File::exists($folderPath)) {
                File::makeDirectory($folderPath, 0755, true);
            }

            $service_image_filename = $vendor->pbv_business_name . '_' . time() . '_service.' . $service_image_file->getClientOriginalExtension();
            $service_image_file->move($folderPath, $service_image_filename);

            // Generate public URL path
            $publicPath = url($folder . '/' . $service_image_filename);

            // Save public URL in DB
            $request->merge(['service_image' => $publicPath]);
        }
        // if ($request->hasFile('service_image')) {
        //     $service_image_file = $request->file('service_image');
        //     $service_image_filename = $vendor->pbv_business_name . '_' .time() . '_service.' . $service_image_file->getClientOriginalExtension();
        //     $service_image_file->move(public_path('uploads/services/'.$vendor->pbv_business_name), $service_image_filename);
        //     $request->merge(['service_image' => $service_image_filename]);
        //     $service_image_path = public_path('uploads/services/'.$vendor->pbv_business_name) . '/' . $service_image_filename;
        // }   

        $added_vendor_service = services::create([
            'pbs_vendor_id' => $vendor->pbv_id,
            'pbs_service_type' => $request->service_type,
            'pbs_service_for' => $request->service_for,
            'pbs_name' => $request->service_name,
            'pbs_description' => $request->service_description,
            'pbs_duration' => $request->service_duration,
            'pbs_duration_cetegory' => '0',
            'pbs_image' => $publicPath,
            'pbs_price' => $request->service_price,
            'pbs_employees' => null,
            'pbs_status' => 1
        ]);
        
        if($added_vendor_service){
            $message = 'Vendor Service updated successfully';
            $status = 200;
        }else{
            $message = 'Vendor Service failed to update';
            $status = 500;
        }

        return response()->json([
            'message' => $message
        ], $status);
    }

    /**
        * @OA\Get(
        *     path="/api/vendor/{vendor_id}",
        *     summary="Get Vendor Details by ID",
        *     description="Fetches vendor information along with configuration and services for a given vendor ID.",
        *     tags={"Vendor"},
        *     security={{"sanctum":{}}},
        *     @OA\Parameter(
        *         name="vendor_id",
        *         in="path",
        *         description="ID of the vendor to fetch",
        *         required=true,
        *         @OA\Schema(type="integer", example=1)
        *     ),
        *     @OA\Response(
        *         response=200,
        *         description="Vendor details retrieved successfully",
        *         @OA\JsonContent(
        *             @OA\Property(property="success", type="boolean", example=true),
        *             @OA\Property(property="data", type="object")
        *         )
        *     ),
        *     @OA\Response(
        *         response=404,
        *         description="Vendor not found",
        *         @OA\JsonContent(
        *             @OA\Property(property="message", type="string", example="Vendor not found")
        *         )
        *     )
        * )
    */

    public function getVendorByID($vendor_id){
        $user = auth()->user();
        $vendor_results = vendors::join('vendor_config', 'vendor_config.pbvc_vendorid', '=', 'vendor.pbv_id', 'left')
                ->join('vendor_standard_availability', 'vendor_standard_availability.pbvsa_vendor_id', '=', 'vendor.pbv_id', 'left')
                ->join('cities', 'cities.pbc_cid', '=', 'vendor.pbv_city', 'left')
                // ->join('ratings', 'ratings.pbr_vendor_id', '=', 'vendor.pbv_id', 'left')
                ->select(
                    'vendor.*',
                    'vendor_config.*',
                    'vendor_standard_availability.*',
                    'cities.*',
                    // 'ratings.*',
                    // DB::raw('AVG(pb_ratings.pbr_rating) as average_rating')
                )
                ->where([
                    ['vendor.pbv_id', $vendor_id], ['vendor.pbv_status', 1]
                ])
                // ->groupBy('vendor.pbv_id')
                ->get(); 
        // $vendor_results = vendors::with(['config', 'city', 'availability']) // Eager load everything
        //     ->where('pbv_id', $vendor_id)
        //     ->where('pbv_status', 1)
        //     ->first();      
        // dd($vendor_results);die();
        if (!$vendor_results || $vendor_results->isEmpty()) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }
        // print_r('<pre>');
        // print_r($vendor_results);die();
        $vendor = $vendor_results->first();
        // dd($vendor->pbv_images);die();

        $availability = $vendor_results->map(function ($item) {
            return [
                'day' => $item->pbvsa_day,
                'start_time' => $item->pbvsa_start_time,
                'end_time' => $item->pbvsa_end_time,
                'is_open' => $item->pbvsa_is_open,
            ];
        })->toArray();        
        
        // Add favorite flag
        $customer = customer::where('pbc_user_id', $user->pbu_id)->first();
        $favourites = $customer->pbc_fav ?? [];

        $isFav = in_array($vendor_results->first()->pbv_id, $favourites);

        $final_vendors = [
            'id' => $vendor->pbv_id,
            'tenentid' => $vendor->pbv_tenentid,
            'servicefor' => $vendor->pbv_servicefor,
            'vendortype' => $vendor->pbv_vendortype,
            'business_name' => $vendor->pbv_business_name,
            'brno' => $vendor->pbv_brno,
            'email' => $vendor->pbv_email,
            'contact_no' => $vendor->pbv_contactno,
            'address' => $vendor->pbv_address,
            'city' => $vendor->pbc_cityname,
            'longatitude' => $vendor->pbv_longatitude,
            'latitude' => $vendor->pbv_latitude,
            'status' => $vendor->pbv_status,
            'created_at' => $vendor->pbv_created_at,
            'display_name' => $vendor->pbvc_display_name,
            'logo' => $vendor->pbvc_logo,
            'service_at_time' => $vendor->pbvc_service_at_time,
            'availability' => $this->groupAvailability($availability),
            'images' => $vendor->pbv_images,
            'rating' => 3,
            'isFav' => $isFav
        ];

        return response()->json([
            'success' => true,
            'data' => $final_vendors
        ], 200);
    }

    /**
 * @OA\Get(
 *     path="/getThisWeekEarningsByVendor",
 *     summary="Get this week's earnings for the authenticated vendor",
 *     tags={"Transections"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Earnings retrieved successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="date", type="string", format="date", example="2025-08-02"),
 *                     @OA\Property(property="booking_ref_no", type="string", example="PBV-123456"),
 *                     @OA\Property(property="amount", type="number", format="float", example=15000.00),
 *                     @OA\Property(property="status", type="string", example="Completed")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Vendor not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Vendor not found")
 *         )
 *     )
 * )
 */

    public function getThisWeekEarningsByVendor(){
        $user = auth()->user();
        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        // $startOfWeek = now()->startOfWeek();
        // $endOfWeek = now()->endOfWeek();

        // $earnings = bookings::where('pbv_vendor_id', $vendor->pbv_id)
        //     ->whereBetween('pb_bk_date', [$startOfWeek, $endOfWeek])
        //     ->sum('pb_bk_total');

        // return response()->json([
        //     'success' => true,
        //     'data' => [
        //         'earnings' => $earnings,
        //         'week_start' => $startOfWeek->format('Y-m-d'),
        //         'week_end' => $endOfWeek->format('Y-m-d')
        //     ]
        // ], 200);
        $earnings = [
            [
                'date' => now()->format('Y-m-d'),
                'booking_ref_no' => 'PBV-123456',
                'amount' => 15000.00,
                'status' => 'Completed',
            ],
            [
                'date' => now()->format('Y-m-d'),
                'booking_ref_no' => 'PBV-4646',
                'amount' => 7000.00,
                'status' => 'Completed',
            ],
            [
                'date' => now()->format('Y-m-d'),
                'booking_ref_no' => 'PBV-7890',
                'amount' => 12000.00,
                'status' => 'Completed',
            ],
            [
                'date' => now()->format('Y-m-d'),
                'booking_ref_no' => 'PBV-4567',
                'amount' => 8000.00,
                'status' => 'Completed',
            ],
            [
                'date' => now()->format('Y-m-d'),
                'booking_ref_no' => 'PBV-8901',
                'amount' => 5000.00,
                'status' => 'Completed',
            ]
        ];
        return response()->json([
            'success' => true,
            'data' => $earnings
        ], 200);
    }


    /**
 * @OA\Get(
 *     path="/getPayoutHistoryByVendor",
 *     summary="Get payouts for the authenticated vendor",
 *     tags={"Transections"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Payouts retrieved successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="date", type="string", format="date", example="2025-08-02"),
 *                     @OA\Property(property="booking_ref_no", type="string", example="PBV-123456"),
 *                     @OA\Property(property="amount", type="number", format="float", example=15000.00)
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Vendor not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Vendor not found")
 *         )
 *     )
 * )
 */
    public function getPayoutHistoryByVendor(){
        $user = auth()->user();
        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        // $startOfWeek = now()->startOfWeek();
        // $endOfWeek = now()->endOfWeek();

        // $earnings = bookings::where('pbv_vendor_id', $vendor->pbv_id)
        //     ->whereBetween('pb_bk_date', [$startOfWeek, $endOfWeek])
        //     ->sum('pb_bk_total');

        // return response()->json([
        //     'success' => true,
        //     'data' => [
        //         'earnings' => $earnings,
        //         'week_start' => $startOfWeek->format('Y-m-d'),
        //         'week_end' => $endOfWeek->format('Y-m-d')
        //     ]
        // ], 200);
        $payouts = [
            [
                'date' => now()->format('Y-m-d'),
                'booking_ref_no' => 'PBV-123456',
                'amount' => 15000.00
            ],
            [
                'date' => now()->format('Y-m-d'),
                'booking_ref_no' => 'PBV-4646',
                'amount' => 7000.00
            ],
            [
                'date' => now()->format('Y-m-d'),
                'booking_ref_no' => 'PBV-7890',
                'amount' => 12000.00
            ],
            [
                'date' => now()->format('Y-m-d'),
                'booking_ref_no' => 'PBV-4567',
                'amount' => 8000.00
            ],
            [
                'date' => now()->format('Y-m-d'),
                'booking_ref_no' => 'PBV-8901',
                'amount' => 5000.00
            ]
        ];
        return response()->json([
            'success' => true,
            'data' => $payouts
        ], 200);
    }
/**
 * @OA\Get(
 *     path="/getAllEarningsByVendor",
 *     summary="Get all earnings for the authenticated vendor",
 *     tags={"Transections"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="All Earnings retrieved successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="date", type="string", format="date", example="2025-08-02"),
 *                     @OA\Property(property="booking_ref_no", type="string", example="PBV-123456"),
 *                     @OA\Property(property="amount", type="number", format="float", example=15000.00),
 *                     @OA\Property(property="status", type="string", example="Completed")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Vendor not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Vendor not found")
 *         )
 *     )
 * )
 */
    public function getAllEarningsByVendor(){
        $user = auth()->user();
        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        // $startOfWeek = now()->startOfWeek();
        // $endOfWeek = now()->endOfWeek();

        // $earnings = bookings::where('pbv_vendor_id', $vendor->pbv_id)
        //     ->whereBetween('pb_bk_date', [$startOfWeek, $endOfWeek])
        //     ->sum('pb_bk_total');

        // return response()->json([
        //     'success' => true,
        //     'data' => [
        //         'earnings' => $earnings,
        //         'week_start' => $startOfWeek->format('Y-m-d'),
        //         'week_end' => $endOfWeek->format('Y-m-d')
        //     ]
        // ], 200);
        $allEarnings = [
            [
                'date' => now()->format('Y-m-d'),
                'booking_ref_no' => 'PBV-123456',
                'amount' => 15000.00,
                'status' => 'Paid',
            ],
            [
                'date' => now()->format('Y-m-d'),
                'booking_ref_no' => 'PBV-4646',
                'amount' => 7000.00,
                'status' => 'Unpaid',
            ],
            [
                'date' => now()->format('Y-m-d'),
                'booking_ref_no' => 'PBV-7890',
                'amount' => 12000.00,
                'status' => 'Unpaid',
            ],
            [
                'date' => now()->format('Y-m-d'),
                'booking_ref_no' => 'PBV-4567',
                'amount' => 8000.00,
                'status' => 'Paid',
            ],
            [
                'date' => now()->format('Y-m-d'),
                'booking_ref_no' => 'PBV-8901',
                'amount' => 5000.00,
                'status' => 'Paid',
            ]
        ];
        return response()->json([
            'success' => true,
            'data' => $allEarnings
        ], 200);
    }
    /**
 * @OA\Get(
 *     path="/getIncentivesByVendor",
 *     summary="Get Incentives for the authenticated vendor",
 *     tags={"Transections"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="All Incentives retrieved successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="incentive_name", type="string", example="Referral Bonus"),
 *                     @OA\Property(property="incentive_description", type="string", example="Bonus for referring new customers"),
 *                     @OA\Property(property="incentive_amount", type="number", format="float", example=5000.00),
 *                     @OA\Property(property="incentive_target", type="integer", example=10),
 *                     @OA\Property(property="incentive_start_date", type="string", format="date", example="2025-08-02"),
 *                     @OA\Property(property="incentive_end_date", type="string", format="date", example="2025-09-01"),
 *                     @OA\Property(
 *                         property="incentive_vendor_details",
 *                         type="object",
 *                         @OA\Property(property="incentive_achieved", type="integer", example=10),
 *                         @OA\Property(property="incentive_remaining", type="integer", example=0),
 *                     ),
 *                     @OA\Property(
 *                         property="incentive_transections",
 *                         type="array",
 *                         @OA\Items(
 *                             type="object",
 *                             @OA\Property(property="date", type="string", format="date", example="2025-08-02"),
 *                             @OA\Property(property="amount", type="number", format="float", example=5000.00),
 *                         )
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Vendor not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Vendor not found")
 *         )
 *     )
 * )
 */
    public function getIncentivesByVendor(){
        $user = auth()->user();
        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $incentives = [
            [
                'incentive_name' => 'Referral Bonus',
                'incentive_description' => 'Bonus for referring new customers',
                'incentive_amount' => 5000.00,
                'incentive_target' => 10,
                'incentive_start_date' => now()->format('Y-m-d'),
                'incentive_end_date' => now()->addDays(30)->format('Y-m-d'),
                'incentive_vendor_details' => [
                    'incentive_achieved' => 10,
                    'incentive_remaining' => 0,
                ],
                'incentive_transections' => [
                    [
                        'date' => now()->format('Y-m-d'),
                        'amount' => 5000.00,
                    ],
                ],
            ],            
            [
                'incentive_name' => 'Weekly Booking Target',
                'incentive_description' => 'Bonus for achieving weekly booking targets',
                'incentive_amount' => 2000.00,
                'incentive_target' => 10,
                'incentive_start_date' => now()->format('Y-m-d'),
                'incentive_end_date' => now()->addDays(30)->format('Y-m-d'),
                'incentive_vendor_details' => [
                    'incentive_achieved' => 3,
                    'incentive_remaining' => 7,
                ],
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $incentives
        ], 200);
    }

    /**
 * @OA\Get(
 *     path="/getToBePaidByVendor",
 *     summary="Get all earnings for the authenticated vendor",
 *     tags={"Transections"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="All To Be Paid retrieved successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="date", type="string", format="date", example="2025-08-02"),
 *                     @OA\Property(property="booking_ref_no", type="string", example="PBV-123456"),
 *                     @OA\Property(property="amount", type="number", format="float", example=15000.00),
 *                     @OA\Property(property="status", type="string", example="Completed")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Vendor not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Vendor not found")
 *         )
 *     )
 * )
 */
    public function getToBePaidByVendor(){
        $user = auth()->user();
        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        // $startOfWeek = now()->startOfWeek();
        // $endOfWeek = now()->endOfWeek();

        // $earnings = bookings::where('pbv_vendor_id', $vendor->pbv_id)
        //     ->whereBetween('pb_bk_date', [$startOfWeek, $endOfWeek])
        //     ->sum('pb_bk_total');

        // return response()->json([
        //     'success' => true,
        //     'data' => [
        //         'earnings' => $earnings,
        //         'week_start' => $startOfWeek->format('Y-m-d'),
        //         'week_end' => $endOfWeek->format('Y-m-d')
        //     ]
        // ], 200);
        $toBePaidList = [
            [
                'date' => now()->format('Y-m-d'),
                'booking_ref_no' => 'PBV-123456',
                'amount' => 1500.00,
            ],
            [
                'date' => now()->format('Y-m-d'),
                'booking_ref_no' => 'PBV-4646',
                'amount' => 700.00,
            ],
            [
                'date' => now()->format('Y-m-d'),
                'booking_ref_no' => 'PBV-7890',
                'amount' => 1200.00,
            ],
            [
                'date' => now()->format('Y-m-d'),
                'booking_ref_no' => 'PBV-4567',
                'amount' => 800.00,
            ],
            [
                'date' => now()->format('Y-m-d'),
                'booking_ref_no' => 'PBV-8901',
                'amount' => 500.00,
            ]
        ];
        return response()->json([
            'success' => true,
            'data' => $toBePaidList
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/getVendorBankDetails",
     *     summary="Get vendor bank details",
     *     description="Fetches the authenticated vendor's bank details.",
     *     operationId="getVendorBankDetails",
     *     tags={"Vendor"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Vendor bank details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="pbvb_id", type="integer", example=1),
     *                 @OA\Property(property="pbvb_vendorid", type="integer", example=10),
     *                 @OA\Property(property="pbvb_bank_name", type="string", example="ABC Bank"),
     *                 @OA\Property(property="pbvb_account_no", type="string", example="1234567890"),
     *                 @OA\Property(property="pbvb_ifsc_code", type="string", example="ABC0001234"),
     *                 @OA\Property(property="pbvb_branch", type="string", example="Colombo Main"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-26 10:30:00"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-26 10:30:00")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Vendor or bank details not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Vendor not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Something went wrong")
     *         )
     *     )
     * )
     */
    public function getVendorBankDetails(){
        $user = auth()->user();
        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $vendor_bank_info = vendorBankInfo::where('pbvb_vendorid', $vendor->pbv_id)->first();
        
        if (!$vendor_bank_info) {
            return response()->json(['message' => 'Vendor bank information not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $vendor_bank_info
        ], 200);
    }

    /**
 * @OA\Get(
 *     path="/api/getVendorDetails",
 *     summary="Get vendor details",
 *     description="Fetches the authenticated vendor's details.",
 *     operationId="getVendorDetails",
 *     tags={"Vendor"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="Vendor details retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="pbv_id", type="integer", example=1),
 *                 @OA\Property(property="pbv_business_name", type="string", example="TJS Beauty Parlour"),
 *                 @OA\Property(property="pbv_email", type="string", example="vendor@example.com"),
 *                 @OA\Property(property="pbv_contactno", type="string", example="+94771234567"),
 *                 @OA\Property(property="pbv_address", type="string", example="123 Main Street, Colombo"),
 *                 @OA\Property(property="pbv_business_category", type="integer", example=2),
 *                 @OA\Property(property="pbv_vendortype", type="integer", example=1),
 *                 @OA\Property(property="pbv_status", type="integer", example=1),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-08-26 10:30:00"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-08-26 10:30:00")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Vendor not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Vendor not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Something went wrong")
 *         )
 *     )
 * )
 */
    public function getVendorDetails(){
        $user = auth()->user();
        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $vendor
        ], 200);
    }

    function groupAvailability(array $availability) {
        $grouped = [];
        $tempGroup = null;

        foreach ($availability as $slot) {
            if ($slot['is_open'] != 1) continue;

            $key = $slot['start_time'] . '-' . $slot['end_time'];

            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }

            $grouped[$key][] = $slot['day'];
        }

        $result = [];

        foreach ($grouped as $time => $days) {
            $dayGroups = [];
            $startDay = $days[0];
            $prevDay = $startDay;

            for ($i = 1; $i < count($days); $i++) {
                if ((strtotime($days[$i]) - strtotime($prevDay)) === 86400) {
                    $prevDay = $days[$i];
                } else {
                    $dayGroups[] = $startDay === $prevDay ? $startDay : "$startDay - $prevDay";
                    $startDay = $days[$i];
                    $prevDay = $days[$i];
                }
            }
            $dayGroups[] = $startDay === $prevDay ? $startDay : "$startDay - $prevDay";

            foreach ($dayGroups as $group) {
                [$start, $end] = explode('-', $time);
                $result[] = [
                    'days' => $group,
                    'time' => "$start to $end"
                ];
            }
        }

        return $result;
    }
}
