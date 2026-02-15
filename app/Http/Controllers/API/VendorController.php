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
use App\Models\paymentTransection;
use App\Models\booking;
use App\Models\vendorPayouts;
use App\Models\vendorPayoutItems;

use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class VendorController extends Controller
{   
    public function vendorRegister(Request $request){
        Log::info('Vendor Register Requests:', ['Requests' => $request->all()]);
        $user = auth()->user();        
        
        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if($vendor->pbv_vendortype == '1'){
            $request->validate(
                [
                    'business_name' => 'required',
                    'address' => 'required',
                    'city_id' => 'required',
                    'longatitude' => 'required',
                    'latitude' => 'required',
                    'email' => 'email|unique:vendor,pbv_email'
                    // 'br_no' => 'required'
                ],
                [
                    'business_name.required' => 'Parlour name is required',
                    'address.required' => 'Address is required',
                    'city_id.required' => 'City is required',
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
                    'city_id' => 'required',
                    'email' => 'email|unique:vendor,pbv_email',
                    'nic_no' => 'required'
                ],
                [
                    'business_category.required' => 'Business Category is required',
                    'address.required' => 'Address is required',
                    'city_id.required' => 'City is required',
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
            'pbv_city' => $request->city_id,
            'pbv_longatitude' => ($request->business_type == '1') ? $request->longatitude : null,
            'pbv_latitude' => ($request->business_type == '1') ? $request->latitude : null,
            'pbv_email' => $request->email,
            'pbv_contactno' => $user->pbu_mobileno,
            'pbv_accept_terms' => 1,
            'pbv_staff_count' => ($request->business_type == '1') ? $request->staff_no : 1,
            'pbv_status' => 1,
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
        Log::info('Vendor Register Response:', ['Response' => $user]);
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
 *             required={"business_name", "address", "city_id", "longatitude", "latitude", "email", "contact_no"},
 *             @OA\Property(property="business_name", type="string", example="Beauty Parlour"),
 *             @OA\Property(property="display_name", type="string", example="Parlour Deluxe"),
 *             @OA\Property(property="short_description", type="string", example="A premium beauty parlour offering bridal makeup"),
 *             @OA\Property(property="br_no", type="string", example=null, nullable=true, description="Business Registration No (optional for parlour)"),
 *             @OA\Property(property="address", type="string", example="123 Main Street"),
 *             @OA\Property(property="city_id", type="string", example="Colombo"),
 *             @OA\Property(property="longatitude", type="number", format="float", example=79.8612),
 *             @OA\Property(property="latitude", type="number", format="float", example=6.9271),
 *             @OA\Property(property="email", type="string", format="email", example="parlour@example.com"),
 *             @OA\Property(property="contact_no", type="string", example="+94712345678"),
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
        Log::info('Step 1: Business Register Requests:', ['data' => $request->all()]);

        $user = auth()->user();
        Log::info('Step 2: Authenticated User:', ['user_id' => $user->id ?? null]);
        
        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        Log::info('Step 3: Vendor Retrieved:', ['vendor' => $vendor]);
        
        if (!$vendor) {
            Log::warning('Step 3.1: Vendor not found', ['pbu_vid' => $user->pbu_vid]);
            return response()->json(['message' => 'Vendor not found'], 404);
        }
        
        // if ($vendor->pbv_vendortype != '1') {
        //     Log::warning('Step 3.2: Invalid vendor type', [
        //         'vendor_id' => $vendor->pbv_id,
        //         'pbv_vendortype' => $vendor->pbv_vendortype
        //     ]);
        //     return response()->json(['message' => 'Invalid vendor type'], 400);
        // }
        
        // if(empty($request->email)){
        //     Log::warning('Step 3.3: Email is empty', ['request_data' => $request->all()]);
        //     return response()->json(['message' => 'Email is required'], 400);
        // }else{
        //     if($vendor->pbv_email && $vendor->pbv_email == $request->email){
        //         Log::warning('Step 3.4: Email mismatch', [
        //             'vendor_email' => $vendor->pbv_email,
        //             'request_email' => $request->email
        //         ]);
        //         return response()->json(['message' => 'Email already exists'], 400);
        //     }
        // }

        // $lat = trim($request->latitude);
        // $lng = trim($request->longatitude);

        // if ($lat === '' || $lng === '' || floatval($lat) == 0.0 || floatval($lng) == 0.0) {
        //     Log::warning('Step 3.5: Invalid location coordinates', [
        //         'latitude' => $request->latitude,
        //         'longatitude' => $request->longatitude
        //     ]);
        //     return response()->json(['message' => 'Location is required'], 404);
        // }

        // $contact_no = $request->contact_no;
        // if(empty($contact_no)){
        //     Log::info('Step 3.6: Contact No is empty, using user mobile no', ['contact_no' => $contact_no]);
        //     return response()->json(['message' => 'Contact No is required'], 400);
        // }else{
        //     if($vendor->pbv_contactno && $vendor->pbv_contactno == $request->contact_no){
        //         Log::warning('Step 3.4: Contact No mismatch', [
        //             'vendor_contactno' => $vendor->pbv_contactno,
        //             'request_contact_no' => $request->contact_no
        //         ]);
        //         return response()->json(['message' => 'Contact No already exists'], 400);
        //     }
        // }

        // $br_no = $request->br_no;
        // if(empty($br_no)){
        //     Log::info('Step 3.7: BR No is empty', ['br_no' => $br_no]);
        //     return response()->json(['message' => 'BR No is required'], 400);
        // }else{
        //     if($vendor->pbv_brno && $vendor->pbv_brno == $request->br_no){
        //         Log::warning('Step 3.8: BR No mismatch', [
        //             'vendor_brno' => $vendor->pbv_brno,
        //             'request_br_no' => $request->br_no
        //         ]);
        //         return response()->json(['message' => 'BR No already exists'], 400);
        //     }
        // }

        Log::info('Step 4: Starting validation...');
        // $request->validate(
        //     [
        //         'business_name' => 'required',
        //         'address' => 'required',
        //         'city_id' => 'required',
        //         'longatitude' => 'required',
        //         'latitude' => 'required',
        //         'email' => 'email|unique:vendor,pbv_email',
        //     ],
        //     [
        //         'business_name.required' => 'Parlour name is required',
        //         'address.required' => 'Address is required',
        //         'city_id.required' => 'City is required',
        //         'longatitude.required' => 'Location is required',
        //         'latitude.required' => 'Location is required',
        //         'email.email' => 'Email must be a valid email address',
        //         'email.unique' => 'Email already exists'
        //     ]
        // );
        try {
            $validated = $request->validate(
                [
                    'business_name' => 'required',
                    'address' => 'required',
                    'city_id' => 'required',
                    'longatitude' => 'required',
                    'latitude' => 'required',
                    'email' => 'required|email|unique:vendor,pbv_email',
                    'contact_no' => 'required|unique:vendor,pbv_contactno',
                    'br_no' => 'required|unique:vendor,pbv_brno'
                ],                
                [
                    'business_name.required' => 'Parlour name is required',
                    'address.required' => 'Address is required',
                    'city_id.required' => 'City is required',
                    'longatitude.required' => 'Location is required',
                    'latitude.required' => 'Location is required',
                    'email.required' => 'Email is required',
                    'email.email' => 'Email must be a valid email address',
                    'email.unique' => 'Email already exists',
                    'contact_no.required' => 'Contact No is required',
                    'contact_no.unique' => 'Contact No already exists',
                    'br_no.required' => 'BR No is required',
                    'br_no.unique' => 'BR No already exists'
                ]
            );
            Log::info('Step 4.1: Validation successful', ['validated_data' => $validated]);
        } catch (ValidationException $e) {
            $firstError = collect($e->errors())->flatten()->first();
            Log::error('Step 4.2: Validation failed', ['message' => $firstError]);
            return response()->json([
                'message' => $firstError,
                'data' => $user
            ], 500);
        }

        Log::info('Step 5: Attempting vendor update...');
        $vendorsUpdate = $vendor->update([ 
            'pbv_tenentid' => 1,
            'pbv_business_name' => $request->business_name,
            'pbv_display_name' => !empty($request->display_name) ? $request->display_name : $request->business_name,
            'pbv_short_description' => $request->short_description,
            'pbv_brno' => $request->br_no ?? null,
            'pbv_address' => $request->address,
            'pbv_city' => $request->city_id,
            'pbv_longatitude' => $request->longatitude,
            'pbv_latitude' => $request->latitude,
            'pbv_email' => $request->email,
            'pbv_contactno' => $request->phone_no ? $request->phone_no : $user->pbu_mobileno,
            'pbv_accept_terms' => 1,
            'pbv_staff_count' => $request->staff_no ?? 1,
            'pbv_status' => 1,
        ]);
        Log::info('Step 6: Vendor update result', ['success' => $vendorsUpdate]);

        if($vendorsUpdate){
            // $user->update([
            //     'pbu_email' => $request->email,
            // ]);
            
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
 *             required={"address","city_id","service_area","email","contact_no","nic_no"},
 *             @OA\Property(property="display_name", type="string", example="Therapist John"),
 *             @OA\Property(property="short_bio", type="string", example="Certified therapist with 5 years of experience."),
 *             @OA\Property(property="nic_no", type="string", example="901234567V"),
 *             @OA\Property(property="address", type="string", example="45 Park Lane"),
 *             @OA\Property(property="city_id", type="string", example="Kandy"),
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
        Log::info('Therapist Register Requests:', ['Requests' => $request->all()]);
        $user = auth()->user();        
        
        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        if ($vendor->pbv_vendortype != '2') {
            return response()->json(['message' => 'Invalid vendor type'], 400);
        }
        
        if(empty($request->email)){
            Log::warning('Step 3.3: Email is empty', ['request_data' => $request->all()]);
            return response()->json(['message' => 'Email is required'], 400);
        }else{
            if($vendor->pbv_email && $vendor->pbv_email == $request->email){
                Log::warning('Step 3.4: Email mismatch', [
                    'vendor_email' => $vendor->pbv_email,
                    'request_email' => $request->email
                ]);
                return response()->json(['message' => 'Email already exists'], 400);
            }
        }

        $lat = trim($request->latitude);
        $lng = trim($request->longatitude);

        if ($lat === '' || $lng === '' || floatval($lat) == 0.0 || floatval($lng) == 0.0) {
            Log::warning('Step 3.5: Invalid location coordinates', [
                'latitude' => $request->latitude,
                'longatitude' => $request->longatitude
            ]);
            return response()->json(['message' => 'Location is required'], 400);
        }
        Log::info('Step 4: Starting validation...');

        $request->validate(
            [
                'address' => 'required',
                'city_id' => 'required',
                'service_area' => 'required',
                'email' => 'required|email|unique:vendor,pbv_email',
                'contact_no' => 'required|unique:vendor,pbv_contactno',
                'nic_no' => 'required',
            ],
            [
                'address.required' => 'Address is required',
                'city_id.required' => 'City is required',
                'service_area.required' => 'Service area is required',
                'email.email' => 'Email must be a valid email address',
                'email.required' => 'Email is required',
                'email.unique' => 'Email already exists',
                'contact_no.required' => 'Contact No is required',
                'contact_no.unique' => 'Contact No already exists',
                'nic_no.required' => 'NIC No is required',
            ]
        );

        $therapist_name = $user->pbu_first_name . ' ' .$user->pbu_last_name;
        $vendorsUpdate = $vendor->update([ 
            'pbv_tenentid' => 1,
            'pbv_business_name' => $therapist_name,
            'pbv_display_name' => $request->display_name ?? $therapist_name,
            'pbv_short_description' => $request->short_bio ?? null,
            'pbv_brno' => $request->nic_no,
            'pbv_address' => $request->address,
            'pbv_city' => $request->city_id,
            'pbv_therapist_service_area' => $request->service_area ?? null,
            'pbv_longatitude' => null,
            'pbv_latitude' => null,
            'pbv_email' => $request->email,
            'pbv_contactno' => $request->contact_no ? $request->contact_no : $user->pbu_mobileno,
            'pbv_accept_terms' => 1,
            'pbv_staff_count' => 1,
            'pbv_status' => 1,
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
        Log::info('vendorDocumentUpdate Requests:', ['Requests' => $request->all()]);
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
        Log::info('vendorDocumentUpdate_v1 Requests:', ['Requests' => $request->all()]);
        $user = auth()->user();

        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $vendorTypeRanges = [
            1 => [1, 7],   // Business Vendor
            2 => [8, 14],  // Therapist Vendor
        ];

        if (!array_key_exists($vendor->pbv_vendortype, $vendorTypeRanges)) {
            return response()->json(['message' => 'Invalid vendor type'], 400);
        }

        list($minId, $maxId) = $vendorTypeRanges[$vendor->pbv_vendortype];

        if ($request->document_id < $minId || $request->document_id > $maxId) {
            return response()->json(['message' => 'This document does not belong to your vendor type'], 403);
        }
        
        $documents = requiredDocument::where('pbrd_vendor_type', $vendor->pbv_vendortype)->get();
        
        $request->validate(
            [
                'document_id' => 'required|integer|exists:required_document,pbrd_id',
                'document' => 'required',
                'document.*' => 'file|mimes:jpg,jpeg,png,pdf|max:2048'
            ],
            [
                'document_id.required' => 'Document ID is required',
                'document_id.integer' => 'Document ID must be an integer',
                'document_id.exists' => 'Document ID does not exist',
                'document.required' => 'Document is required',
                'document.*.file' => 'Document must be a file',
                'document.*.mimes' => 'Document must be a file of type: jpg, jpeg, png, pdf',
                'document.*.max' => 'Document may not be greater than 2MB',
            ]
        );

        $documents = requiredDocument::where('pbrd_id', $request->document_id)->first();

        if($request->hasFile('document')){
            $files = $request->file('document');
            
            if(!is_array($files)){
                $files = [$files];
            }

            foreach ($files as $file) {
                if ($file->isValid()) {
                    // generate unique filename
                    $fileName = time().'_'.$file->getClientOriginalName();

                    // store file (change 'public' to 's3' if using AWS S3)
                    $filePath = $file->storeAs('uploads/vendors/'.$vendor->pbv_id, $fileName, 'public');

                    // full url for access (public disk: storage/app/public/uploads/...)
                    $fileUrl = Storage::disk('public')->url($filePath);
                    // $fileUrl = asset('storage/' . $filePath);
                    // dd($documents->pbrd_is_single);
                    if($documents->pbrd_is_single){
                        $document_update = vendorDocuments::updateOrCreate(
                            [
                                'pbvd_vendor_id' => $vendor->pbv_id,
                                'pbvd_required_document_id' => $request->document_id,
                            ],
                            [
                                'pbvd_document_name' => $fileName,
                                'pbvd_document_url' => $fileUrl,
                                'pbvd_document_status' => '1'
                            ]
                        );
                        //dd($document_update);
                    }else{
                        vendorDocuments::updateOrCreate(
                            [
                                'pbvd_vendor_id' => $vendor->pbv_id,
                                'pbvd_required_document_id' => $request->document_id,
                                'pbvd_document_name' => $fileName,
                            ],
                            [
                                'pbvd_document_url' => $fileUrl,
                                'pbvd_document_status' => '1'
                            ]
                        );
                    }
                }else{
                    return response()->json(['message' => 'Invalid file upload'], 400);
                }
            }
        } else {
            return response()->json(['message' => 'No document uploaded'], 400);
        }   

        return response()->json([
            'success' => true,
            'message' => 'Documents uploaded successfully'
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/deleteVendorDocument",
     *     summary="Delete vendor document",
     *     description="Deletes a vendor document and removes the file from storage. Vendor is identified from the authenticated user.",
     *     operationId="deleteVendorDocument",
     *     tags={"Vendor"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"document_id"},
     *             @OA\Property(
     *                 property="document_id",
     *                 type="integer",
     *                 example=12,
     *                 description="Vendor document ID"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Document deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Document deleted successfully")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Vendor or document not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Document not found")
     *         )
     *     )
     * )
     */

    public function deleteVendorDocument(Request $request){
        Log::info('deleteVendorDocument Requests:', ['document_id' => $request->document_id]);
        $user = auth()->user();

        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $vendorDocument = vendorDocuments::where('pbvd_id', $request->document_id)
            ->where('pbvd_vendor_id', $vendor->pbv_id)
            ->first();

        if (!$vendorDocument) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        // Delete the file from storage
        $filePath = str_replace('/storage/', '', parse_url($vendorDocument->pbvd_document_url, PHP_URL_PATH));
        Storage::disk('public')->delete($filePath);

        // Delete the database record
        $vendorDocument->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document deleted successfully'
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
        Log::info('getVendorDocuments Response:', ['Response' => $document_paths]);

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
        Log::info('vendorBankUpdate Requests:', ['Requests' => $request->all()]);
        $user = auth()->user();

        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }
        $request->validate(
            [
                'bankid' => 'required',
                'branch' => 'required',
                'account_holder_name' => 'required',
                'accountno' => 'required|numeric',                
            ],
            [
                'bankid.required' => 'Please select the Bank',
                'branch.required' => 'Please enter the Branch name',
                'account_holder_name' => 'Please enter the account holder name',
                'accountno.required' => 'Please enter the bank Account No',
                'accountno.numeric' => 'Bank no must be Numeric',
            ]
        );
        
        $vendorBankInfoUpdate = vendorBankInfo::updateOrCreate(            
            ['pbvb_vendorid' => $vendor->pbv_id],
            [
                'pbvb_vendorid' => $vendor->pbv_id,
                'pbvb_bankname' => $request->bankid,
                'pbvb_branch' => $request->branch,
                'pbvb_branch_code' => $request->branch_code,
                'pbvb_holder_name' => $request->account_holder_name,
                'pbvb_accountno' => $request->accountno,
                'pbvb_is_active' => 1,
                'pbvb_status' => 0
            ]
        );

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
        Log::info('vendorConfig Requests:', ['Requests' => $request->all()]);
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
        Log::info('vendorAvailability Requests:', ['Requests' => $request->all()]);
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
        Log::info('vendorSpecialCloses Requests:', ['Requests' => $request->all()]);

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
            $day = Carbon::parse($special_close['day'])->toDateString();;
            $fullDayClosed = $special_close['full_day_closed'];
            $fromTime = $fullDayClosed ? '00:00:00' : $special_close['from_time'];
            $toTime = $fullDayClosed ? '23:59:59' : $special_close['to_time'];

            Log::info('Checking Booking Conflicts:', [
                'vendor_id' => $vendor->pbv_id,
                'day' => $day,
                'fromTime' => $fromTime,
                'toTime' => $toTime
            ]);

            // 🧠 Check if there are any confirmed bookings for that vendor on the same day
            $conflictingBookings = booking::where('pbb_vendor_id', $vendor->pbv_id)
                ->whereDate('pbb_booking_date', $day)
                ->where(function ($query) use ($fromTime, $toTime) {
                    $query->where(function ($q) use ($fromTime, $toTime) {
                        $q->whereTime('pbb_booking_start_time', '<', $toTime)
                        ->whereTime('pbb_booking_end_time', '>', $fromTime);
                    });
                })
                ->where('pbb_status', 1)
                ->get();

            Log::info('conflictingBookings Response:', [
                'count' => $conflictingBookings->count(),
                'bookings' => $conflictingBookings->map(function ($b) {
                    return [
                        'ref_no' => $b->pbb_ref_no,
                        'start' => $b->pbb_booking_start_time,
                        'end' => $b->pbb_booking_end_time
                    ];
                })
            ]);

            if ($conflictingBookings->isNotEmpty()) {
                return response()->json([
                    'message' => 'Cannot close this time. Bookings already exist on this day.'
                ], 500);
            }

            // Check for duplicate special closing
            $duplicateQuery = vendorSpecialCloses::where('pbvsc_vendor_id', $vendor->pbv_id)
                ->where('pbvsc_day', $special_close['day']);

            if ($special_close['full_day_closed']) {
                // For full day closings, just check if any full day closing exists for that day
                $duplicateQuery->where('pbvsc_full_day_closed', 1);
            } else {
                // For partial closings, check if same time slot already exists
                $duplicateQuery->where('pbvsc_full_day_closed', 0)
                    ->where('pbvsc_from_time', $special_close['from_time'])
                    ->where('pbvsc_to_time', $special_close['to_time']);
            }

            if ($duplicateQuery->exists()) {
                return response()->json([
                    'message' => 'Special closing already exists for this date and time.'
                ], 422);
            }

            vendorSpecialCloses::create([
                'pbvsc_vendor_id' => $vendor->pbv_id,
                'pbvsc_day' => $special_close['day'],
                'pbvsc_full_day_closed' => $special_close['full_day_closed'],
                'pbvsc_from_time' => ($special_close['full_day_closed'] == 0) ? $special_close['from_time'] : null,
                'pbvsc_to_time' => ($special_close['full_day_closed'] == 0) ? $special_close['to_time'] : null,
                'pbvsc_status' => 1
            ]);
        }

        return response()->json([
            'message' => 'Vendor Special Closes added successfully! Customer cannot book during these times.'
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
        Log::info('addVendorServices Requests:', ['Requests' => $request->all()]);
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
            $folder = 'uploads/services/' . $vendor->pbv_id;
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
            'pbs_status' => 0
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
 *     path="/api/vendor/profile",
 *     summary="Get authenticated vendor's profile",
 *     description="Retrieves detailed information about the currently authenticated vendor including availability, documents, and configuration",
 *     tags={"Vendor"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=123),
 *                 @OA\Property(property="tenentid", type="integer", example=1),
 *                 @OA\Property(property="servicefor", type="string", example="salon"),
 *                 @OA\Property(property="vendortype", type="string", example="premium"),
 *                 @OA\Property(property="business_name", type="string", example="Luxury Salon"),
 *                 @OA\Property(property="brno", type="string", example="BR123456"),
 *                 @OA\Property(property="email", type="string", example="info@luxurysalon.com"),
 *                 @OA\Property(property="contact_no", type="string", example="+1234567890"),
 *                 @OA\Property(property="address", type="string", example="123 Main Street"),
 *                 @OA\Property(property="city", type="string", example="New York"),
 *                 @OA\Property(property="longatitude", type="string", example="-73.935242"),
 *                 @OA\Property(property="latitude", type="string", example="40.730610"),
 *                 @OA\Property(property="status", type="integer", example=1),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-25T12:00:00.000000Z"),
 *                 @OA\Property(property="display_name", type="string", example="Luxury Beauty Salon"),
 *                 @OA\Property(property="logo", type="string", format="uri", example="https://api.example.com/storage/logo.png"),
 *                 @OA\Property(property="service_at_time", type="integer", example=5),
 *                 @OA\Property(
 *                     property="availability",
 *                     type="array",
 *                     description="Grouped availability information",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="day", type="string", example="Monday"),
 *                         @OA\Property(property="time_slots", type="array", @OA\Items(type="string", example="09:00-18:00")),
 *                         @OA\Property(property="is_open", type="boolean", example=true)
 *                     )
 *                 ),
 *                 @OA\Property(
 *                     property="images",
 *                     type="array",
 *                     @OA\Items(
 *                         type="string",
 *                         format="uri",
 *                         example="https://api.example.com/storage/image1.jpg"
 *                     )
 *                 ),
 *                 @OA\Property(property="rating", type="number", format="float", example=4.5),
 *                 @OA\Property(property="isFav", type="boolean", example=false)
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
 *         response=401,
 *         description="Unauthorized - User not authenticated or not a vendor",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden - User doesn't have vendor access",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Access denied")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Server error")
 *         )
 *     )
 * )
 */
    public function getVendor(){
        $user = auth()->user();
        // $vendor_results = vendors::join('vendor_standard_availability', 'vendor_standard_availability.pbvsa_vendor_id', '=', 'vendor.pbv_id', 'left')
        //         ->join('cities', 'cities.pbc_cid', '=', 'vendor.pbv_city', 'left')
        //         ->join('vendor_documents', 'vendor_documents.pbvd_vendor_id', '=', 'vendor.pbv_id', 'left')
        //         // ->join('ratings', 'ratings.pbr_vendor_id', '=', 'vendor.pbv_id', 'left')
        //         ->select(
        //             'vendor.*',
        //             'vendor_standard_availability.*',
        //             'cities.*',
        //             'vendor_documents.*'
        //             // 'ratings.*',
        //             // DB::raw('AVG(pb_ratings.pbr_rating) as average_rating')
        //         )
        //         ->where([
        //             ['vendor.pbv_id', $vendor_id], ['vendor.pbv_status', 2]
        //         ])
        // 
        // $vendor_results = vendors::with(['city', 'availability', 'vendorDocuments'])
        $vendor_results = vendors::with(['city', 'availability', 'vendorDocuments'])
            ->where('pbv_id', $user->pbu_vid)
            ->first();
            
        if (!$vendor_results) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        if ($vendor_results->pbv_status == 0 || $vendor_results->pbv_status == null) {
            return response()->json(['message' => 'You did not enter the parlour info'], 404);
        }

        // Process availability - $vendor_results is a single object, not a collection
        $availability = $vendor_results->availability->map(function ($item) {
            $dayLabel = $item->pbvsa_day;
            $formatTime = function ($t) {
                if ($t === null || $t === '') {
                    return null;
                }

                // If already a time string like "09:30" or "09:30:00", Carbon::parse will handle it.
                try {
                    return Carbon::parse($t)->format('H:i');
                } catch (\Exception $e) {
                    // Fallback: return original string (safer than throwing)
                    return (string)$t;
                }
            };

            $start = $formatTime($item->pbvsa_start_time);
            $end   = $formatTime($item->pbvsa_end_time);

            // If both exist show "HH:MM - HH:MM", else fallback
            $timeString = ($start && $end) ? $start . ' - ' . $end : ($start ?: ($end ?: null));

            return [
                'day' => $dayLabel,                 // e.g. "Monday" or numeric index — unchanged
                'start_time' => $start,            // e.g. "09:30"
                'end_time' => $end,                // e.g. "17:00"
                'is_open' => (bool) $item->pbvsa_is_open,
                'time' => $timeString,             // helper field if needed
            ];
            // return [
            //     'day' => $item->pbvsa_day,
            //     'start_time' => $item->pbvsa_start_time,
            //     'end_time' => $item->pbvsa_end_time,
            //     'is_open' => $item->pbvsa_is_open,
            // ];
        })->toArray();

        // Get logo from documents where required_document_id = 6
        $vendorDocuments = $vendor_results->vendorDocuments;
        $logoDocument = $vendorDocuments->firstWhere('pbvd_required_document_id', 6);
        $logoUrl = $logoDocument ? $logoDocument->pbvd_document_url : null;

        // Fallback to config logo if document not found
        if (!$logoUrl && $vendor_results->config) {
            $logoUrl = $vendor_results->config->pbvc_logo ?? null;
        }

        // ✅ Images (example: document types 7–10 are image types, adjust IDs as needed)
        $imageDocuments = $vendorDocuments->where('pbvd_required_document_id', 7);
        $images = $imageDocuments->pluck('pbvd_document_url')->toArray();

        // Add favorite flag
        $customer = customer::where('pbc_user_id', $user->pbu_id)->first();
        $favourites = $customer->pbc_fav ?? [];

        // Check if this vendor's ID is in favorites
        $isFav = in_array($vendor_results->pbv_id, $favourites);

        $final_vendors = [
            'id' => $vendor_results->pbv_id,
            'tenentid' => $vendor_results->pbv_tenentid,
            'servicefor' => $vendor_results->pbv_servicefor,
            'vendortype' => $vendor_results->pbv_vendortype,
            'business_name' => $vendor_results->pbv_business_name,
            'brno' => $vendor_results->pbv_brno,
            'email' => $vendor_results->pbv_email,
            'contact_no' => $vendor_results->pbv_contactno,
            'address' => $vendor_results->pbv_address,
            'city' => $vendor_results->city->pbc_cityname ?? null, // Access through city relationship
            'longatitude' => $vendor_results->pbv_longatitude,
            'latitude' => $vendor_results->pbv_latitude,
            'status' => $vendor_results->pbv_status,
            'description' => $vendor_results->pbv_short_description,
            'created_at' => $vendor_results->created_at,
            'display_name' => $vendor_results->pbv_display_name,
            'logo' => $logoUrl, // Use the document URL or fallback
            'profile_image' => $vendor_results->pbv_profile_image,
            'service_at_time' => $vendor_results->pbv_staff_count,
            'availability' => $this->groupAvailability($availability),            
            'images' => !empty($images)
                        ? $images
                        : (is_string($vendor_results->pbv_images)
                            ? json_decode($vendor_results->pbv_images, true)
                            : (is_array($vendor_results->pbv_images)
                                ? $vendor_results->pbv_images
                                : [])),
            'rating' => 3,
            //'isFav' => $isFav
        ];
        Log::info('getVendorfromvendorapp Response:', ['Response' => $final_vendors]);

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
        Log::info('getThisWeekEarningsByVendor Vendor Response:', ['Response' => $vendor]);
        $startOfWeek = now()->startOfWeek()->toDateString();;
        $endOfWeek = now()->endOfWeek()->toDateString();;

        Log::info('startOfWeek Response:', ['startOfWeek' => $startOfWeek, 'endOfWeek' => $endOfWeek]);

        $earnings = paymentTransection::with(['booking'])
            ->where('pbpt_vendor_id', $vendor->pbv_id)
            ->whereHas('booking', function ($q) use ($startOfWeek, $endOfWeek) {
                $q->whereBetween('pbb_booking_date', [$startOfWeek, $endOfWeek]);
            })
            ->get()
            ->map(function ($transaction) {
                // $totalAmount = $transaction->payoutItems->sum('pbvpi_vendor_amount');
                // $isPaid = $transaction->payoutItems->every(fn($item) => $item->pbvpi_status == 1);
                $status = '';
                if($transaction->pbpt_status == 0){
                    $status = 'Unpaid';
                }else if($transaction->pbpt_status == 1){
                    $status = 'Paid';
                }else if($transaction->pbpt_status == 2){
                    $status = 'Refunded';
                }else if($transaction->pbpt_status == 3){
                    $status = 'Declined';
                }else{
                    $status = 'Unknown';
                }

                return [
                    'date' => $transaction->created_at->format('Y-m-d'),
                    'booking_ref_no' => $transaction->booking->pbb_ref_no,
                    'amount' => $transaction->pbpt_total_amount,
                    'status' => $status,
                ];
            })->toArray();

        Log::info('getThisWeekEarningsByVendor Response:', ['Response' => $earnings]);
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

        $payouts = paymentTransection::with(['booking','payoutItems'])
            ->where('pbpt_vendor_id', $vendor->pbv_id) 
            ->whereHas('payoutItems', function ($query) {
                $query->where('pbvpi_status', 1);
            })
            ->get()
            ->map(function ($transaction) {
                return [
                    'date' => $transaction->created_at->format('Y-m-d'),
                    'booking_ref_no' => $transaction->booking->pbb_ref_no,
                    'amount' => $transaction->payoutItems->pbvpi_vendor_amount,
                ];
            })->toArray();

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
        // $payouts = [
        //     [
        //         'date' => now()->format('Y-m-d'),
        //         'booking_ref_no' => 'PBV-123456',
        //         'amount' => 15000.00
        //     ],
        //     [
        //         'date' => now()->format('Y-m-d'),
        //         'booking_ref_no' => 'PBV-4646',
        //         'amount' => 7000.00
        //     ],
        //     [
        //         'date' => now()->format('Y-m-d'),
        //         'booking_ref_no' => 'PBV-7890',
        //         'amount' => 12000.00
        //     ],
        //     [
        //         'date' => now()->format('Y-m-d'),
        //         'booking_ref_no' => 'PBV-4567',
        //         'amount' => 8000.00
        //     ],
        //     [
        //         'date' => now()->format('Y-m-d'),
        //         'booking_ref_no' => 'PBV-8901',
        //         'amount' => 5000.00
        //     ]
        // ];
        Log::info('payouts Response:', ['Response' => $payouts]);
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

        $allEarnings = paymentTransection::with(['booking','payoutItems'])
            ->where('pbpt_vendor_id', $vendor->pbv_id)
            ->get()
            ->map(function ($transaction) {                
                $totalAmount = $transaction->payoutItems->sum('pbvpi_vendor_amount');
                $isPaid = $transaction->payoutItems->every(fn($item) => $item->pbvpi_status == 1);

                return [
                    'date' => $transaction->created_at->format('Y-m-d'),
                    'booking_ref_no' => $transaction->booking->pbb_ref_no ?? null,
                    'amount' => $totalAmount,
                    'status' => $isPaid ? 'Paid' : 'Unpaid',
                ];
            })->toArray();
            
        Log::info('allEarnings Response:', ['Response' => $allEarnings]);

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
        Log::info('incentives Response:', ['Response' => $incentives]);

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

        $toBePaidList = paymentTransection::with(['booking','payoutItems'])
            ->where('pbpt_vendor_id', $vendor->pbv_id)            
            ->whereHas('payoutItems', function ($query) {
                $query->where('pbvpi_status', 1);
            })
            ->get()
            ->map(function ($transaction) {
                return [
                    'date' => $transaction->created_at->format('Y-m-d'),
                    'booking_ref_no' => $transaction->booking->pbb_ref_no,
                    'amount' => $transaction->payoutItems->pbvpi_vendor_amount,
                ];
            })->toArray();
        
        // $toBePaidList = [
        //     [
        //         'date' => now()->format('Y-m-d'),
        //         'booking_ref_no' => 'PBV-123456',
        //         'amount' => 1500.00,
        //     ],
        //     [
        //         'date' => now()->format('Y-m-d'),
        //         'booking_ref_no' => 'PBV-4646',
        //         'amount' => 700.00,
        //     ],
        //     [
        //         'date' => now()->format('Y-m-d'),
        //         'booking_ref_no' => 'PBV-7890',
        //         'amount' => 1200.00,
        //     ],
        //     [
        //         'date' => now()->format('Y-m-d'),
        //         'booking_ref_no' => 'PBV-4567',
        //         'amount' => 800.00,
        //     ],
        //     [
        //         'date' => now()->format('Y-m-d'),
        //         'booking_ref_no' => 'PBV-8901',
        //         'amount' => 500.00,
        //     ]
        // ];
        Log::info('toBePaidList Response:', ['Response' => $toBePaidList]);

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
        Log::info('getVendorBankDetails Response:', ['Response' => $vendor_bank_info]);

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
        Log::info('getVendorDetails Response:', ['Response' => $vendor]);

        return response()->json([
            'success' => true,
            'data' => $vendor
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/vendorDetailStatus",
     *     summary="Get Vendor Detail Completion Status",
     *     description="This API returns the status of vendor profile completion including vendor info, required documents, and bank details.",
     *     operationId="getVendorDetailStatus",
     *     tags={"Vendor"},
     *     security={{"bearerAuth": {}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with vendor detail status",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="vendor_info_status", type="boolean", example=true, description="True if all required vendor info fields are filled"),
     *                 @OA\Property(property="vendor_documents_status", type="boolean", example=false, description="True if all required documents are uploaded"),
     *                 @OA\Property(property="vendor_bankdetails_status", type="boolean", example=true, description="True if all bank details are provided")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Invalid vendor type",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid vendor type")
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
     *         response=401,
     *         description="Unauthorized - JWT token missing or invalid",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function getVendorDetailStatus(){
        $user = auth()->user();
        $vendor = vendors::find($user->pbu_vid);        

        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $vendorInfoFields = [];
        $documentIds = requiredDocument::where('pbrd_vendor_type', $vendor->pbv_vendortype)
            ->pluck('pbrd_id')
            ->toArray();        

        if ($vendor->pbv_vendortype == '1') {
            $vendorInfoFields = ['pbv_business_name', 'pbv_address', 'pbv_city', 'pbv_longatitude', 'pbv_latitude', 'pbv_email'];
        } elseif ($vendor->pbv_vendortype == '2') {
            $vendorInfoFields = ['pbv_business_category', 'pbv_address', 'pbv_city', 'pbv_email', 'pbv_brno'];
        } else {
            return response()->json(['message' => 'Invalid vendor type'], 400);
        }

        $allHaveValues = collect($vendorInfoFields)->every(function ($field) use ($vendor) {
            return !is_null($vendor->$field);
        });

        // $documents = vendorDocuments::where('pbvd_vendor_id', $vendor->pbv_id)
        //     ->whereIn('pbvd_required_document_id', $documentIds)
        //     ->get();
        
        // $allDocumentsUploaded = (count($documentIds) > 0)
        //                         ? (count($documents) === count($documentIds))
        //                         : true;
        $documents = vendorDocuments::where('pbvd_vendor_id', $vendor->pbv_id)
                                    ->whereIn('pbvd_required_document_id', $documentIds)
                                    ->get(['pbvd_required_document_id', 'updated_at']);

        $uploadedIds = $documents->pluck('pbvd_required_document_id')->toArray();

        $allDocumentsUploaded = empty($documentIds)
            ? true
            : !array_diff($documentIds, $uploadedIds);

        $latestDocumentUpdate = $documents->isNotEmpty()
            ? $documents->max('updated_at')
            : null;

        $bankDetails = vendorBankInfo::where('pbvb_vendorid', $vendor->pbv_id)->first();
        
        $allBankDetailsFilled = $bankDetails
                                && !empty($bankDetails->pbvb_bankname)
                                && !empty($bankDetails->pbvb_holder_name)
                                && !empty($bankDetails->pbvb_branch)
                                && !empty($bankDetails->pbvb_accountno);                                

        $vendorDetailStatus = [
            [
                'type' => 'vendor_info_status',
                'status' => $allHaveValues,
                'updated_at' => $vendor->updated_at ? $vendor->updated_at->format('Y-m-d H:i:s') : null,
            ],
            [
                'type' => 'vendor_documents_status',
                'status' => $allDocumentsUploaded,
                'updated_at' => $latestDocumentUpdate ? \Carbon\Carbon::parse($latestDocumentUpdate)->format('Y-m-d H:i:s') : null,
            ],
            [
                'type' => 'vendor_bankdetails_status',
                'status' => $allBankDetailsFilled,
                'updated_at' => $bankDetails?->updated_at ? $bankDetails->updated_at->format('Y-m-d H:i:s') : null,
            ]
        ];

        Log::info('vendorDetailStatus Response:', ['Response' => $vendorDetailStatus]);
        return response()->json([
            'success' => true,
            'data' => $vendorDetailStatus
        ], 200);
    }

    public function getVendorDetailStatus_v1(){
        $user = auth()->user();
        $vendor = vendors::find($user->pbu_vid);        

        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $vendorInfoFields = [];
        $documentIds = requiredDocument::where('pbrd_vendor_type', $vendor->pbv_vendortype)
            ->pluck('pbrd_id')
            ->toArray();        

        if ($vendor->pbv_vendortype == '1') {
            $vendorInfoFields = ['pbv_business_name', 'pbv_address', 'pbv_city', 'pbv_longatitude', 'pbv_latitude', 'pbv_email'];
        } elseif ($vendor->pbv_vendortype == '2') {
            $vendorInfoFields = ['pbv_business_category', 'pbv_address', 'pbv_city', 'pbv_email', 'pbv_brno'];
        } else {
            return response()->json(['message' => 'Invalid vendor type'], 400);
        }

        $allHaveValues = collect($vendorInfoFields)->every(function ($field) use ($vendor) {
            return !is_null($vendor->$field);
        });
        
        // $documents = vendorDocuments::where('pbvd_vendor_id', $vendor->pbv_id)
        //     ->whereIn('pbvd_required_document_id', $documentIds)
        //     ->get();
        
        // $allDocumentsUploaded = (count($documentIds) > 0)
        //                         ? (count($documents) === count($documentIds))
        //                         : true;
        $documents = vendorDocuments::where('pbvd_vendor_id', $vendor->pbv_id)
                                    ->whereIn('pbvd_required_document_id', $documentIds)
                                    ->get(['pbvd_required_document_id', 'updated_at']);

        $uploadedIds = $documents->pluck('pbvd_required_document_id')->toArray();

        // Check if all required document IDs are present in uploaded list
        $allDocumentsUploaded = empty($documentIds)
                                ? true
                                : !array_diff($documentIds, $uploadedIds);

        $latestDocumentUpdate = $documents->isNotEmpty()
            ? $documents->max('updated_at')
            : null;

        $bankDetails = vendorBankInfo::where('pbvb_vendorid', $vendor->pbv_id)->first();

        $allBankDetailsFilled = $bankDetails
                                && !empty($bankDetails->pbvb_bankname)
                                && !empty($bankDetails->pbvb_holder_name)
                                && !empty($bankDetails->pbvb_branch)
                                && !empty($bankDetails->pbvb_accountno); 
        
        $vendorAvailability = vendorStandardAvailability::where('pbvsa_vendor_id', $vendor->pbv_id)
                                                        ->whereIn('pbvsa_day', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])
                                                        ->get(['pbvsa_day', 'updated_at']); // 👈 keep as Collection

        $requiredDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        // Extract only the day names for comparison
        $availableDays = $vendorAvailability->pluck('pbvsa_day')->toArray();

        // Check if all weekdays are available
        $hasWeekdayAvailability = !array_diff($requiredDays, $availableDays);

        // Get latest updated_at
        $latestAvailabilityUpdate = $vendorAvailability->isNotEmpty()
            ? $vendorAvailability->max('updated_at')
            : null;

        // check if any service are present in vendor services
        $vendorServices = services::where('pbs_vendor_id', $vendor->pbv_id)
                                        ->get(['pbs_id', 'updated_at']);        

        $hasServices = !empty($vendorServices) ? true : false;

        $vendorDetailStatus = [
            [
                'type' => 'vendor_info_status',
                'status' => $allHaveValues,
                'updated_at' => $vendor->updated_at ? $vendor->updated_at->format('Y-m-d H:i:s') : null,
            ],
            [
                'type' => 'vendor_documents_status',
                'status' => $allDocumentsUploaded,
                'updated_at' => $latestDocumentUpdate ? \Carbon\Carbon::parse($latestDocumentUpdate)->format('Y-m-d H:i:s') : null,
            ],
            [
                'type' => 'vendor_bankdetails_status',
                'status' => $allBankDetailsFilled,
                'updated_at' => $bankDetails?->updated_at ? $bankDetails->updated_at->format('Y-m-d H:i:s') : null,
            ],
            [
                'type' => 'vendor_weekday_availability_status',
                'status' => $hasWeekdayAvailability,
                'updated_at' => $latestAvailabilityUpdate ? \Carbon\Carbon::parse($latestAvailabilityUpdate)->format('Y-m-d H:i:s') : null,
            ],
            [
                'type' => 'vendor_services_status',
                'status' => $hasServices,
                'updated_at' => !empty($vendorServices) ? $vendorServices->max('updated_at')->format('Y-m-d H:i:s') : null,
            ],
        ];

        Log::info('vendorDetailStatus_v1 Response:', ['Response' => $vendorDetailStatus]);
        return response()->json([
            'success' => true,
            'data' => $vendorDetailStatus
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
