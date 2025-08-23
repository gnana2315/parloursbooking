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
use Validator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class VendorController extends Controller
{   
    /**
     * @OA\Post(
     *      path="/api/vendorRegister",
     *      operationId="vendorRegister",
     *      tags={"Vendor"},
     *      summary="Vendor Info Registration",
     *      description="",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={
     *                  "business_category",
     *                  "business_name",
     *                  "display_name",
     *                  "address",
     *                  "city",
     *                  "longatitude",
     *                  "latitude",
     *                  "email",
     *                  "br_no/nic_no",
     *                  "short_description",
     *                  "staff_no"
     *              }, 
     *              @OA\Property(property="business_name", type="string", example="Golden Saloon"),
     *              @OA\Property(property="display_name", type="string", example="Golden"),
     *              @OA\Property(property="address", type="string", example="No.7, Negombo Road, Wattala"),
     *              @OA\Property(property="city", type="string", example="Wattala"),
     *              @OA\Property(
     *                  property="longatitude", 
     *                  type="string", 
     *                  nullable=true,
     *                  description="Required only if vendortype is 1 (Parlour)",
     *                  example=""
     *              ),
     *              @OA\Property(
     *                  property="latitude", 
     *                  type="string", 
     *                  nullable=true,
     *                  description="Required only if vendortype is 1 (Parlour)",
     *                  example=""
     *              ),
     *              @OA\Property(property="email", type="email", example="goldensaloon@gmail.com"),
     *              @OA\Property(
     *                  property="br_no", 
     *                  type="string", 
     *                  nullable=true,
     *                  description="Required only if vendortype is 1 (Parlour)",
     *                  example=""
     *              ),
     *              @OA\Property(
     *                  property="nic_no", 
     *                  type="string", 
     *                  nullable=true,
     *                  description="Required only if vendortype is 2 (Therapist)",
     *                  example=""
     *              ),
     *              @OA\Property(property="short_description", type="string", example=""),
     *              @OA\Property(property="staff_no", type="string", example=""),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Vendor Info Details saved Successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="token", type="string", example="generated_token_here")
     *          ),
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     * )
     */
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
     *      path="/api/vendorDocumentUpdate",
     *      operationId="vendorDocumentUpdate",
     *      tags={"Vendor"},
     *      summary="Vendor Document Update",
     *      description="",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={
     *                  "br_document",
     *                  "certification",
     *                  "address_proof_document",
     *                  "nic_document",
     *                  "police_report",
     *                  "experience_letter",
     *                  "other",
     *              },
     *              @OA\Property(property="br_document", type="file", example=""),
     *              @OA\Property(property="certification", type="file", example=""),
     *              @OA\Property(property="address_proof_document", type="file", example=""),
     *              @OA\Property(property="nic_document", type="file", example=""),
     *              @OA\Property(property="police_report", type="file", example=""),
     *              @OA\Property(property="experience_letter", type="file", example=""),
     *              @OA\Property(property="other", type="file", description="If upload any other document user need to add a name for the each document"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Vendor Document saved Successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="token", type="string", example="generated_token_here")
     *          ),
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     * )
     */

    public function vendorDocumentUpdate(Request $request){

        $user = auth()->user();

        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }
        $document_data = [];
        if($vendor->pbv_vendortype == '1'){
            $request->validate(
                [
                    'br_document' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
                    'nic_document' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
                    'address_proof_document' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
                    'other_document' => 'mimes:jpg,jpeg,png,pdf|max:2048',
                ],
                [
                    'br_document.required' => 'BR document is required',
                    'br_document.mimes' => 'BR document must be a file of type: jpg, jpeg, png, pdf',
                    'br_document.max' => 'BR document may not be greater than 2MB',
                    'nic_document.required' => 'BR document is required',
                    'nic_document.mimes' => 'NIC document must be a file of type: jpg, jpeg, png, pdf',
                    'nic_document.max' => 'NIC document may not be greater than 2MB',
                    'address_proof_document.required' => 'Address Proof document is required',
                    'address_proof_document.mimes' => 'Address Proof document must be a file of type: jpg, jpeg, png, pdf',
                    'address_proof_document.max' => 'Address Proof document may not be greater than 2MB',
                    'other_document.mimes' => 'Other document must be a file of type: jpg, jpeg, png, pdf',
                    'other_document.max' => 'Other document may not be greater than 2MB',
                ]
            );
        }else{
            $request->validate(
                [
                    'nic_document' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
                    'certification' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
                    'profile_image' => 'required|mimes:jpg,jpeg,png,pdf|max:2048',
                    'other_document' => 'mimes:jpg,jpeg,png,pdf|max:2048',
                ],
                [
                    'nic_document.required' => 'NIC document is required',
                    'nic_document.mimes' => 'NIC document must be a file of type: jpg, jpeg, png, pdf',
                    'nic_document.max' => 'NIC document may not be greater than 2MB',
                    'certification.required' => 'Certification is required',                    
                    'certification.mimes' => 'Certification must be a file of type: jpg, jpeg, png, pdf',
                    'certification.max' => 'Certification may not be greater than 2MB',
                    'profile_image.required' => 'Profile image is required',
                    'profile_image.mimes' => 'Profile image must be a file of type: jpg, jpeg, png, pdf',
                    'profile_image.max' => 'Profile image may not be greater than 2MB',
                    'other_document.mimes' => 'Other document must be a file of type: jpg, jpeg, png, pdf',
                    'other_document.max' => 'Other document may not be greater than 2MB',
                ]
            );
        }
        if ($request->hasFile('br_document')) {
            $br_document_file = $request->file('br_document');
            $br_document_filename = $vendor->pbv_business_name . '_' .time() . '_br_document.' . $br_document_file->getClientOriginalExtension();
            $br_document_file->move(public_path('uploads/vendors'), $br_document_filename);
            // $path = $br_document_file->storeAs('vendors', $br_document_filename, 's3');
            // $url = Storage::disk('s3')->url($path);

            $request->merge(['br_document' => $br_document_filename]);
            $br_document_path = public_path('uploads/vendors') . '/' . $br_document_filename;

            $document_data[] = [
                'br_document' => [
                    'name' => $br_document_filename,
                    'path' => $br_document_path,
                ],
            ];
        }

        if ($request->hasFile('certification')) {
            $certification_document_file = $request->file('certification');
            $certification_document_filename = $vendor->pbv_business_name . '_' .time() . '_certification_document.' . $certification_document_file->getClientOriginalExtension();
            $certification_document_file->move(public_path('uploads/vendors'), $certification_document_filename);
            $request->merge(['certification' => $certification_document_filename]);
            $certification_document_path = public_path('uploads/vendors') . '/' . $certification_document_filename;
            $document_data[] = [
                'certification_document' => [
                    'name' => $certification_document_filename,
                    'path' => $certification_document_path,
                ],
            ];
        }

        if ($request->hasFile('address_proof_document')) {
            $address_proof_document_file = $request->file('address_proof_document');
            $address_proof_document_filename = $vendor->pbv_business_name . '_' .time() . '_address_proof_document_document.' . $address_proof_document_file->getClientOriginalExtension();
            $address_proof_document_file->move(public_path('uploads/vendors'), $address_proof_document_filename);
            $request->merge(['address_proof_document' => $address_proof_document_filename]);
            $address_proof_document_path = public_path('uploads/vendors') . '/' . $address_proof_document_filename;
            $document_data[] = [
                'address_proof_document' => [
                    'name' => $address_proof_document_filename,
                    'path' => $address_proof_document_path,
                ],
            ];
        }

        if ($request->hasFile('nic_document')) {
            $nic_document_file = $request->file('nic_document');
            $nic_document_filename = $vendor->pbv_business_name . '_' .time() . '_nic_document.' . $nic_document_file->getClientOriginalExtension();
            $nic_document_file->move(public_path('uploads/vendors'), $nic_document_filename);
            $request->merge(['nic_document' => $nic_document_filename]);
            $nic_document_path = public_path('uploads/vendors') . '/' . $nic_document_filename;
            $document_data[] = [
                'nic_document' => [
                    'name' => $nic_document_filename,
                    'path' => $nic_document_path,
                ],
            ];
        }

        if ($request->hasFile('profile_image')) {
            $profile_image = $request->file('profile_image');
            $profile_imagename = $vendor->pbv_business_name . '_' .time() . '_profile_image.' . $profile_image->getClientOriginalExtension();
            $profile_image->move(public_path('uploads/vendors'), $profile_imagename);
            $request->merge(['nic_document' => $profile_imagename]);
            $profile_image_path = public_path('uploads/vendors') . '/' . $profile_imagename;
            $document_data[] = [
                'profile_image' => [
                    'name' => $profile_imagename,
                    'path' => $profile_image_path,
                ],
            ];
        }

        if ($request->hasFile('other_document')) {
            foreach ($request->file('other_document') as $index => $document) {
                $other_document_filename = $vendor->pbv_business_name . '_' .time() . '_other_document_' . $index . '.' . $document->getClientOriginalExtension();
                $document->move(public_path('uploads/vendors'), $other_document_filename);
                $request->merge(['other_document' => $other_document_filename]);
                $other_document_path = public_path('uploads/vendors') . '/' . $other_document_filename;
                $document_data[] = [
                    'other_document' => [
                        'name' => $other_document_filename,
                        'path' => $other_document_path,
                    ],
                ];
            }
        }
        $vendor_document_update = $vendor->update([
            'pbv_documents' => json_encode($document_data),
        ]);

        if($vendor_document_update){
            $message = 'Vendor Document saved successfully';
            $status = 200;
        }else{
            $message = 'Vendor Document failed to save';
            $status = 500;
        }

        return response()->json([
            'message' => $message
        ], $status);
    }

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
     *                  "bankname",
     *                  "account_holder_name",
     *                  "branch",
     *                  "accountno",
     *              },
     *              @OA\Property(property="bankname", type="text", example="HNB"),
     *              @OA\Property(property="account_holder_name", type="text", example="John"),
     *              @OA\Property(property="branch", type="text", example="logo.png"),
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
                'bankname' => 'required',
                'account_holder_name' => 'required',
                'branch' => 'required',
                'accountno' => 'required|numeric',
                
            ],
            [
                'bankname.required' => 'Please select the Bank',
                'account_holder_name' => 'Please enter the account holder name',
                'branch.required' => 'Please enter the Branch name',
                'accountno.required' => 'Please enter the bank Account No',
                'accountno.numeric' => 'Bank no must be Numeric',
            ]
        );

        $vendorBankInfoUpdate = vendorBankInfo::create([
            'pbvb_vendorid' => $vendor->pbv_id,
            'pbvb_bankname' => $request->bankname,
            'pbvb_holder_name' => $request->account_holder_name,
            'pbvb_branch' => $request->branch,
            'pbvb_accountno' => $request->accountno,
            'pbvb_status' => 1
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
     *     path="/getVendorAvailability",
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
     *              @OA\Property(property="day", type="text", example="Monday"),
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
                '*.day' => 'required:in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                '*.full_day_closed' => 'required|boolean',
                '*.from_time' => 'required_if:*.full_day_closed,false|date_format:H:i',
                '*.to_time' => 'required_if:*.full_day_closed,false|date_format:H:i|after:*.from_time',
            ],
            [
                '*.day.required' => 'Day is required',
                '*.day.in' => 'Day must be one of the following: Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday',
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
        $vendor_results = vendors::join('vendor_config', 'vendor_config.pbvc_vendorid', '=', 'vendor.pbv_id')
                ->join('vendor_standard_availability', 'vendor_standard_availability.pbvsa_vendor_id', '=', 'vendor.pbv_id')
                ->join('cities', 'cities.pbc_cid', '=', 'vendor.pbv_city')
                // ->join('ratings', 'ratings.pbr_vendor_id', '=', 'vendor.pbv_id', 'left')
                ->select(
                    'vendor.*',
                    'vendor_config.*',
                    'vendor_standard_availability.*',
                    'cities.*',
                    // 'ratings.*',
                    // DB::raw('AVG(pb_ratings.pbr_rating) as average_rating')
                )
                ->where('pbv_id', $vendor_id)
                ->where('vendor.pbv_status', 1)
                // ->groupBy('vendor.pbv_id')
                ->get();        
        
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
