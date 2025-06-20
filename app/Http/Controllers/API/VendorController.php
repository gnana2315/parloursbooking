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
     *                  "service_for",
     *                  "vendor_for",
     *                  "business_category",
     *                  "business_name",
     *                  "business_name",
     *                  "address",
     *                  "city",
     *                  "longatitude",
     *                  "latitude",
     *                  "email",
     *                  "br_no/nic_no",
     *                  "short_description",
     *              },
     *              @OA\Property(property="service_for", type="numeric", example="Men(1)/Women(2)/Unisex(3)"),
     *              @OA\Property(property="business_category", type="numeric", example="Saloon(1)/Parlour(2)/Nail Art(3)"),
     *              @OA\Property(property="business_name", type="string", example="Golden Saloon"),
     *              @OA\Property(
     *                  property="person_initial", 
     *                  type="string", 
     *                  nullable=true,
     *                  description="Required only if vendortype is 2 (Therapist)",
     *                  example="Mr/Mrs/Ms"
     *              ),
     *              @OA\Property(
     *                  property="person_firstname", 
     *                  type="string", 
     *                  nullable=true,
     *                  description="Required only if vendortype is 2 (Therapist)",
     *                  example="John"
     *              ),
     *              @OA\Property(
     *                  property="person_lastname", 
     *                  type="string", 
     *                  nullable=true,
     *                  description="Required only if vendortype is 2 (Therapist)",
     *                  example="Peter"
     *              ),
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
                    'service_for' => 'required',
                    'business_category' => 'required',
                    'business_name' => 'required|unique:vendor,pbv_business_name',
                    'address' => 'required',
                    'city' => 'required',
                    'longatitude' => 'required',
                    'latitude' => 'required',
                    'email' => 'email|unique:vendor,pbv_email',
                    'br_no' => 'required'
                ],
                [
                    'service_for.required' => 'Service for is required',
                    'business_category.required' => 'Business Category is required',
                    'business_name.required' => 'Parlour name is required',
                    'business_name.unique' => 'The name already in Use',
                    'address.required' => 'Address is required',
                    'city.required' => 'City is required',
                    'longatitude.required' => 'Location is required',
                    'latitude.required' => 'Location is required',
                    'email.email' => 'Email must be a valid email address',
                    'email.unique' => 'Email already exists',
                    'br_no.required' => 'BR No is required'
                ]
            );
        }else if ($vendor->pbv_vendortype == '2'){
            $request->validate(
                [
                    'service_for' => 'required',
                    'business_category' => 'required',
                    'person_initial' => 'required',
                    'person_firstname' => 'required',
                    'person_lastname' => 'required',
                    'address' => 'required',
                    'city' => 'required',
                    'email' => 'email|unique:vendor,pbv_email',
                    'nic_no' => 'required'
                ],
                [
                    'service_for.required' => 'Service for is required',
                    'business_category.required' => 'Business Category is required',
                    'person_initial.required' => 'Therapist Initial is required',
                    'person_firstname.required' => 'Therapist Firstname is required',
                    'person_lastname.required' => 'Therapist Lastname is required',
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

        $therapist_name = $request->person_initial . '. ' .$request->person_firstname. ' ' .$request->person_lastname;
        $vendorsUpdate = $vendor->update([ 
            'pbv_servicefor' => $request->service_for,
            'pbv_tenentid' => 1,
            'pbv_business_category' => $request->business_category,
            'pbv_business_name' => ($request->business_type == '1') ? $request->business_name : $therapist_name,
            'pbv_brno' => ($request->business_type == '1') ? $request->br_no : $request->nic_no,
            'pbv_address' => $request->address,
            'pbv_city' => $request->city,
            'pbv_longatitude' => ($request->business_type == '1') ? $request->longatitude : null,
            'pbv_latitude' => ($request->business_type == '1') ? $request->latitude : null,
            'pbv_email' => $request->email,
            'pbv_contactno' => $user->pbu_mobileno,
            'pbv_accept_terms' => 1,
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
            'user' => $user
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
     *                  "address_proof",
     *                  "nic_document",
     *                  "police_report",
     *                  "experience_letter",
     *                  "other",
     *              },
     *              @OA\Property(property="br_document", type="file", example=""),
     *              @OA\Property(property="certification", type="file", example=""),
     *              @OA\Property(property="address_proof", type="file", example=""),
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
     *                  "branch",
     *                  "accountno",
     *              },
     *              @OA\Property(property="bankname", type="text", example="HNB"),
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
                'branch' => 'required',
                'accountno' => 'required|numeric',
                
            ],
            [
                'bankname.required' => 'Please select the Bank',
                'branch.required' => 'Please enter the Branch name',
                'accountno.required' => 'Please enter the bank Account No',
                'accountno.numeric' => 'Bank no must be Numeric',
            ]
        );

        $vendorBankInfoUpdate = vendorBankInfo::create([
            'pbvb_vendorid' => $vendor->pbv_id,
            'pbvb_bankname' => $request->bankname,
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
            ]);
        }

        return response()->json([
            'message' => 'Vendor Availability updated successfully'
        ], 200);
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
            $service_image_filename = $vendor->pbv_business_name . '_' .time() . '_service.' . $service_image_file->getClientOriginalExtension();
            $service_image_file->move(public_path('uploads/services/'.$vendor->pbv_business_name), $service_image_filename);
            $request->merge(['service_image' => $service_image_filename]);
            $service_image_path = public_path('uploads/services/'.$vendor->pbv_business_name) . '/' . $service_image_filename;
        }   

        $added_vendor_service = services::create([
            'pbs_vendor_id' => $vendor->pbv_id,
            'pbs_service_type' => $request->service_type,
            'pbs_service_for' => $request->service_for,
            'pbs_name' => $request->service_name,
            'pbs_description' => $request->service_description,
            'pbs_duration' => $request->service_duration,
            'pbs_image' => $service_image_path,
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
                // ->join('services', 'services.pbs_vendor_id', '=', 'vendor.pbv_id')
                ->select(
                    'vendor.*',
                    'vendor_config.*',
                    'vendor_standard_availability.*',
                    'cities.*',
                    // 'services.*'
                )
                ->where('pbv_id', $vendor_id)
                ->where('vendor.pbv_status', 1)
                ->get();        
        
        if (!$vendor_results || $vendor_results->isEmpty()) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }
        // print_r('<pre>');
        // print_r($vendor_results);die();
        // $vendors = $vendor_results->first();
        
        // $final_vendors = [
        //     'id' => $vendors->pbv_id,
        //     'business_name' => $vendors->pbv_business_name,
        //     'city' => $vendors->pbv_city,
        //     'lat' => $vendors->pbv_lat,
        //     'lon' => $vendors->pbv_lon,
        //     'contact_no' => $vendors->pbv_contact_no,
        //     'logo' => $vendors->pbvc_logo,
        //     'status' => $vendors->pbv_status,
        //     'services' => $vendor_results->map(function ($vendor) {
        //         return [
        //             'id' => $vendor->pbs_id,
        //             'name' => $vendor->pbs_name,
        //             'description' => $vendor->pbs_description,
        //             'duration_category' => $vendor->pbs_duration_cetegory,                    
        //             'image' => $vendor->pbs_image,
        //             'emloyee' => $vendor->pbs_employees,
        //             'duration' => $vendor->pbs_duration,
        //             'price' => (float) $vendor->pbs_price,
        //             'type' => $vendor->pbs_service_type,
        //             "status" => $vendor->pbs_status
        //         ];
        //     })->values()->all()
        // ];

        // Add favorite flag
        $customer = customer::where('pbc_user_id', $user->pbu_id)->first();
        $favourites = $customer->pbc_fav ?? [];
        
        $vendor_results['isFav'] = in_array($vendor_results->first()->pbv_id, $favourites);

        return response()->json([
            'success' => true,
            'data' => $vendor_results
        ], 200);
    }
}
