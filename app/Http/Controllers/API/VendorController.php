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
use App\Models\services;
use Validator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorController extends Controller
{   
    /**
     * @OA\Post(
     *      path="/api/vendorRegister/{id}",
     *      operationId="vendorRegister",
     *      tags={"Vendor"},
     *      summary="Vendor Registration",
     *      description="",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={
     *                  "servicefor",
     *                  "business_type",
     *                  "business_category",
     *                  "business_name",
     *                  "address",
     *                  "city",
     *                  "longatitude",
     *                  "latitude",
     *                  "email",
     *                  "br_no",
     *                  "br_document",
     *                  "certification"
     *              },
     *              @OA\Property(property="servicefor", type="string", example="Men/Women/Unisex"),
     *              @OA\Property(property="business_type", type="string", example="Business/Therapist"),
     *              @OA\Property(property="business_category", type="string", example="saloon/parlour"),
     *              @OA\Property(property="business_name", type="string", example="Golden Saloon"),
     *              @OA\Property(property="address", type="string", example="No.7, Negombo Road, Wattala"),
     *              @OA\Property(property="city", type="string", example="Wattala"),
     *              @OA\Property(property="longatitude", type="string", example=""),
     *              @OA\Property(property="latitude", type="string", example=""),
     *              @OA\Property(property="email", type="email", example="goldensaloon@gmail.com"),
     *              @OA\Property(property="br_no/nic_no", type="string", example="br-1847/901234567"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Vendor Details saved Successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="token", type="string", example="generated_token_here")
     *          ),
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function vendorRegister(Request $request, $id){
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        if($request->business_type == '1'){
            $request->validate(
                [
                    'service_for' => 'required',
                    'business_type' => 'required',
                    'business_category' => 'required',
                    'business_name' => 'required|unique:vendor,pbv_business_name',
                    'address' => 'required',
                    'city' => 'required',
                    'longatitude' => 'required',
                    'latitude' => 'required',
                    'email' => 'email|unique:person,pbv_email',
                    'br_no' => 'required',
                ],
                [
                    'service_for.required' => 'Service for is required',
                    'business_type.required' => 'Business Type is required',
                    'business_category.required' => 'Business Category is required',
                    'business_name.required' => 'Parlour name is required',
                    'address.required' => 'Address is required',
                    'city.required' => 'City is required',
                    'longatitude.required' => 'Longatitude is required',
                    'latitude.required' => 'Latitude is required',
                    'email.email' => 'Email must be a valid email address',
                    'email.unique' => 'Email already exists',
                    'br_no.required' => 'BR number is required'
                ]
            );
        }else{
            $request->validate(
                [
                    'service_for' => 'required',
                    'business_type' => 'required',
                    'person_name' => 'required',
                    'address' => 'required',
                    'city' => 'required',
                    'email' => 'email|unique:person,pbv_email',
                    'nic_no' => 'required',
                ],
                [
                    'service_for.required' => 'Service for is required',
                    'business_type.required' => 'Business Type is required',
                    'person_name.required' => 'Therapist name is required',
                    'address.required' => 'Address is required',
                    'city.required' => 'City is required',
                    'email.email' => 'Email must be a valid email address',
                    'email.unique' => 'Email already exists',
                    'nic_no.required' => 'NIC number is required',
                ]
            );
        }

        $vendor = vendors::create([ 
            'pbv_servicefor' => $request->service_for,
            'pbv_businesstype' => $request->business_type,
            'pbv_business_name' => ($request->business_type == '1') ? $request->business_name : $request->person_name,
            'pbv_address' => $request->address,
            'pbv_city' => $request->city,
            'pbv_longatitude' => ($request->business_type == '1') ? $request->longatitude : null,
            'pbv_latitude' => ($request->business_type == '1') ? $request->latitude : null,
            'pbv_brno' => ($request->business_type == '1') ? $request->br_no : $request->nic_no,
            'pbv_email' => $request->email,
            'pbv_contactno' => $user->pbu_mobileno,
            'pbv_accept_terms' => 1,
            'pbv_status' => 0,
        ]);

        if($vendor){
            $user->update([
                'pbu_vid' => $vendor->pbv_id,
                'pbu_email' => $request->email,
            ]);
            
            $token_text = $request->business_type.'_vendor_details_registration_session';

            $message = 'Vendor Details saved successfully';
            $token = $user->createToken($token_text)->plainTextToken;
            $token_type = 'Bearer';
            $user_id = $user->pbu_id;
            $status = 201;
        }else{
            $message = 'Vendor Details failed to save';
            $token = null;
            $token_type = null;
            $user_id = $id;
            $status = 500;
        }

        return response()->json([
            'message' => $message,
            'access_token' => $token,
            'token_type' => $token_type,
            'user_id' => $user_id
        ], $status);
    }

    /**
     * @OA\Post(
     *      path="/api/vendorDocumentUpdate/{id}",
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
     *                  "nic_document",
     *                  "police_report",
     *              },
     *              @OA\Property(property="br_document", type="file", example=""),
     *              @OA\Property(property="certification", type="file", example=""),
     *              @OA\Property(property="nic_document", type="file", example=""),
     *              @OA\Property(property="police_report", type="file", example=""),
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
    public function vendorDocumentUpdate(Request $request, $id){
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }
        if($vendor->pbv_businesstype == '1'){
            $request->validate(
                [
                    'br_document' => 'mimes:jpg,jpeg,png,pdf|max:2048',
                    'certification' => 'mimes:jpg,jpeg,png,pdf|max:2048',
                ],
                [
                    'br_document.mimes' => 'BR document must be a file of type: jpg, jpeg, png, pdf',
                    'br_document.max' => 'BR document may not be greater than 2MB',
                    'certification.mimes' => 'Certification must be a file of type: jpg, jpeg, png, pdf',
                    'certification.max' => 'Certification may not be greater than 2MB'
                ]
            );
        }else{
            $request->validate(
                [
                    'nic_document' => 'mimes:jpg,jpeg,png,pdf|max:2048',
                    'certification' => 'mimes:jpg,jpeg,png,pdf|max:2048',
                    'police_report' => 'mimes:jpg,jpeg,png,pdf|max:2048',
                ],
                [
                    'nic_document.mimes' => 'BR document must be a file of type: jpg, jpeg, png, pdf',
                    'nic_document.max' => 'BR document may not be greater than 2MB',
                    'certification.mimes' => 'Certification must be a file of type: jpg, jpeg, png, pdf',
                    'certification.max' => 'Certification may not be greater than 2MB',
                    'police_report.mimes' => 'Police report must be a file of type: jpg, jpeg, png, pdf',
                    'police_report.max' => 'Polica Report may not be greater than 2MB',
                ]
            );
        }
        if ($request->hasFile('br_document')) {
            $br_document_file = $request->file('br_document');
            $br_document_filename = $vendor->pbv_business_name . '_' .time() . '_br_document.' . $br_document_file->getClientOriginalExtension();
            $br_document_file->move(public_path('uploads/vendors'), $br_document_filename);
            $request->merge(['br_document' => $br_document_filename]);
            $br_document_path = public_path('uploads/vendors') . '/' . $br_document_filename;
        }

        if ($request->hasFile('certification')) {
            $certification_document_file = $request->file('certification');
            $certification_document_filename = $vendor->pbv_business_name . '_' .time() . '_certification_document.' . $certification_document_file->getClientOriginalExtension();
            $certification_document_file->move(public_path('uploads/vendors'), $certification_document_filename);
            $request->merge(['certification' => $certification_document_filename]);
            $certification_document_path = public_path('uploads/vendors') . '/' . $certification_document_filename;
        }
        if ($request->hasFile('nic_document')) {
            $nic_document_file = $request->file('nic_document');
            $nic_document_filename = $vendor->pbv_business_name . '_' .time() . '_nic_document.' . $nic_document_file->getClientOriginalExtension();
            $nic_document_file->move(public_path('uploads/vendors'), $nic_document_filename);
            $request->merge(['nic_document' => $nic_document_filename]);
            $nic_document_path = public_path('uploads/vendors') . '/' . $nic_document_filename;
        }
        if ($request->hasFile('police_report')) {
            $police_report_file = $request->file('police_report');
            $police_report_filename = $vendor->pbv_business_name . '_' .time() . '_police_report.' . $police_report_file->getClientOriginalExtension();
            $police_report_file->move(public_path('uploads/vendors'), $police_report_filename);
            $request->merge(['police_report' => $police_report_filename]);
            $police_report_path = public_path('uploads/vendors') . '/' . $police_report_filename;
        }
        $vendor_document_update = $vendor->update([
            'pbv_parlourcertificate' => $certification_document_path,
            'pbv_brdoc' => ($request->pbv_businesstype == '1') ? $br_document_path : $nic_document_path,
            'pbv_police_report' => ($request->pbv_businesstype == '1') ? null : $police_report_path,
        ]);

        if($vendor_document_update){            
            $token_text = $vendor->pbv_business_name.'_vendor_document_update_session';

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
     *      path="/api/vendorConfig/{id}",
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
    public function vendorConfig(Request $request, $id){
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
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

        if($vendor){
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
     *      path="/api/vendorAvailability/{id}",
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
    public function vendorAvailability(Request $request, $id){
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
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
     *      path="/api/vendorSpecialCloses/{id}",
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

    public function vendorSpecialCloses(Request $request, $id){
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
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
     *      path="/api/addVendorServices/{id}",
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
     *              @OA\Property(property="service_duration", type="number", example="2 (hours)"),
     *              @OA\Property(property="service_image", type="file", example="service_image.png"),    
     *              @OA\Property(property="service_price", type="number", example="2000"),
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

    public function addVendorServices(Request $request, $id){
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
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
                'service_duration' => 'required|decimal',
                'service_image' => 'mimes:jpg,jpeg,png|max:2048',
                'service_price' => 'required|float',
            ],
            [
                'service_type.required' => 'Service type is required',
                'service_for.required' => 'Service for is required',
                'service_name.required' => 'Service name is required',
                'service_description.required' => 'Service description is required',
                'service_duration.required' => 'Service duration is required',
                'service_duration.decimal' => 'Service duration must be a decimal number',
                'service_image.mimes' => 'Service image must be a file of type: jpg, jpeg, png',
                'service_image.max' => 'Service image may not be greater than 2MB',
                'service_price.required' => 'Service price is required',
                'service_price.float' => 'Service price must be a number',
            ]
        );        
        
        if ($request->hasFile('service_image')) {
            $service_image_file = $request->file('service_image');
            $service_image_filename = $vendor->pbv_business_name . '_' .time() . '_business_logo.' . $service_image_file->getClientOriginalExtension();
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
}
