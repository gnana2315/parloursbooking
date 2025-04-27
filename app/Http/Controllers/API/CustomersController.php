<?php

namespace App\Http\Controllers\API;

//use App\ApiResponseTrait;
use App\Models\User;
use App\Models\customer;
use Validator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomersController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/customerRegister/{id}",
     *      operationId="customerDetailRegister",
     *      tags={"Customer"},
     *      summary="Customer Detail Registration",
     *      description="",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"intial","first_name","last_name","email","nic_no","nic_document"},
     *              @OA\Property(property="intial", type="string", example="Mr/Mrs"),
     *              @OA\Property(property="first_name", type="string", example="John"),
     *              @OA\Property(property="last_name", type="string", example="Wick"),
     *              @OA\Property(property="dob", type="date", example="1990-02-12"),
     *              @OA\Property(property="email", type="email", example="John@gmail.com"),
     *              @OA\Property(property="nic_no", type="string", example="941234587V"),
     *              @OA\Property(property="nic_document", type="file", example="nic.jpg"),
     *              @OA\Property(property="sex", type="text", example="male/female"),
     *              @OA\Property(property="address", type="text", example="address"),
     *              @OA\Property(property="city", type="text", example="city")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Customer Details saved Successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="token", type="string", example="generated_token_here")
     *          ),
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     * )
     */

    public function register(Request $request){
        $user = auth()->user();

        $request->validate(
            [
                'intial' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'dob' => 'required',
                'nic_no' => 'required',
                'nic_document' => 'mimes:jpg,jpeg,png,pdf|max:2048',
                'email' => 'email|unique:customer,pbc_email',
            ],
            [
                'intial.required' => 'Customer Initial Required',
                'first_name.required' => 'Customer First Name Required',
                'last_name.required' => 'Customer Last Name Required',
                'email.email' => 'Customer Email Not Valid',
                'email.unique' => 'Customer Email already in use',
                'nic_no.required' => 'Customer NIC No Required',
                'nic_document.mimes' => 'Customer NIC Document must be a file of type: jpg, jpeg, png, pdf',
                'nic_document.max' => 'Customer NIC Document may not be greater than 2MB',
            ]
        );
        if ($request->hasFile('nic_document')) {
            $file = $request->file('nic_document');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/customer_nic'), $filename);
            $request->merge(['nic_document' => $filename]);
            $nic_doc_path = public_path('uploads/customer_nic') . '/' . $filename;
        }
        $customer = customer::create([
            'pbc_user_id' => $id,
            'pbc_intial' => $request->intial,
            'pbc_first_name' => $request->first_name,
            'pbc_last_name' => $request->last_name,
            'pbc_dob' => $request->last_name,
            'pbc_nic_no' => $request->nic_no,
            'pbc_nic_document' => $nic_doc_path,
            'pbc_sex' => $request->sex,
            'pbc_address' => $request->address,
            'pbc_city' => $request->city,
            'pbc_email' => $request->city,
            'pbc_contact_no' => $user->pbu_mobileno,
            'pbc_status' => 1,
        ]);

        if($customer){
            $message = 'Customer Details saved successfully';
            $status = 200;
        }else{
            $message = 'Customer Details failed to save';
            $status = 500;
        }

        return response()->json([
            'message' => $message,
            'user' => $user
        ], $status);
    }
}
