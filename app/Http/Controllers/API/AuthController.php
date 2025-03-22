<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Validator;

class AuthController extends Controller
{
    //vendor registration
    public function vendorRegisteration(Request $request){
        $request->validate(
            [
                'usertype' => 'required',
                'phone_no' => 'required|unique:users,pbu_mobileno',
                'password' => 'required|min:8',
            ],
            [
                'usertype.required' => 'User Type undefined',
                'phone_no.required' => 'Phone No Required',
                'phone_no.unique' => 'Phone No already in use. Please try another one.',
                'password.required' => 'Phone No Required',
                'password.min' => 'Password length will be minimum 8 characters',
            ]
        );
        // $validator = Validator::make($request->all(), [
        //     'usertype' => 'required',
        //     'phone_no' => 'required|unique:users,pbu_mobileno',
        //     'password' => 'required|min:8',
        // ],
        // [
        //     'usertype.required' => 'User Type undefined',
        //     'phone_no.required' => 'Phone No Required',
        //     'phone_no.unique' => 'Phone No already in use. Please try another one.',
        //     'password.required' => 'Phone No Required',
        //     'password.min' => 'assword length will be minimum 8 characters',
        // ]);

        // if($validator->fails()){
        //     return $this->sendError('Validation Error.', $validator->errors());
        // }
        $verification_code = $this->generateVendorVerificationCode();
        $user = User::create([
            'pbu_usertype' => $request->usertype,
            'pbu_mobileno' => $request->phone_no,
            'password' => Hash::make($request->password),
            'pbu_name' => $request->phone_no,
            'pbu_verification_token' => $verification_code,
            'pbu_verification_code_generated_time' => date('Y-m-d H:i:s'),
            'pbu_status' => 0
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            //'message' => 'Customer registered successfully',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);

    }

    public function generateVendorVerificationCode(){
        $timestamp = now()->timestamp; // Current timestamp
        $code = strtoupper(substr(md5($timestamp), 0, 6));

        return $code;
    }

    public function verifyVendorVerificationCode(){

    }
}
