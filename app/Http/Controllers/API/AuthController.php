<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\customer;
use App\Models\vendors;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Validator;


/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Parlours Booking API",
 *      description="Swagger API Documentation in Parlours Booking",
 *      @OA\Contact(
 *          email="admin@example.com"
 *      ),
 * )
 */

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/userMobileRegister",
     *      operationId="userRegisterMobileNo",
     *      tags={"Authentication"},
     *      summary="Registeration User Mobile No",
     *      description="Returns user OTP",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"user_type","phone_no"},
     *              @OA\Property(property="user_type", type="number", example="2(Customer)/1(Vendor)"),
     *              @OA\Property(property="phone_no", type="number", example="0711234567"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User Mobile No Registered Successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="OTP", type="string", example="Send to mobile")
     *          ),
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    //user mobile verification
    public function userRegisterMobileNo(Request $request){
        $request->validate(
            [
                'user_type' => 'required',
                'phone_no' => [
                    'required',
                    'min:10',
                    Rule::unique('users', 'pbu_mobileno')->where(function ($query) use ($request) {
                        return $query->where('pbu_usertype', $request->user_type)
                            ->where('pbu_status', 1); 
                    }),
                ],
            ],
            [
                'user_type.required' => 'User Type undefined',
                'phone_no.required' => 'Invalid Phone Number.',
                'phone_no.min' => 'Invalid Phone Number. Phone Number Must have 10 Digits',
                'phone_no.unique' => 'Phone Number Already Registered. If you forgot password, please use forgot password, instead of Create new account.',
            ]
        );
        
        User::where('pbu_mobileno', $request->phone_no)
            ->where('pbu_usertype', $request->user_type)
            ->where('pbu_status', 0)
            ->delete();

        $user = User::create([
            'pbu_usertype' => $request->user_type,
            'pbu_mobileno' => $request->phone_no,
            'pbu_name' => $request->phone_no,
            'pbu_status' => 0
        ]);
        //dd($user);
        $verfivation_code = $this->generateVerificationCode($user->pbu_id);        

        return response()->json([
            'message' => 'User registered successfully. Please check the OTP in you phone.',
            'user_id' => $user->pbu_id,
            'OTP' => $verfivation_code,
        ], 200);
    }

    /**
     * @OA\Post(
     *      path="/api/resendOtp",
     *      operationId="resendOtp",
     *      tags={"Authentication"},
     *      summary="Resend OTP to User Mobile No",
     *      description="Returns user OTP",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"user_id"},
     *              @OA\Property(property="user_id", type="number", example=" "),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Resend OTP to User Mobile No ",
     *          @OA\JsonContent(
     *              @OA\Property(property="OTP", type="string", example="Send to mobile")
     *          ),
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function resendOtp(Request $request) {
    
        $otp = $this->generateVerificationCode($request->user_id);
    
        // optionally send OTP via SMS or email
        return response()->json([
            'message' => 'OTP resent successfully.',
            'otp' => $otp // remove this in production!
        ]);
    }

    public function generateVerificationCode($user_id){        
        $user = User::find($user_id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $verificationCode = random_int(100000, 999999); // Generate a 6-digit code
        $expiresAt = Carbon::now()->addMinutes(10); // Code expires in 10 minutes

        // Save the code and expiration time
        $user->update([
            'pbu_verification_token' => $verificationCode,
            'pbu_verification_token_expires_at' => $expiresAt,
        ]);

        return $verificationCode;
    }
    /**
     * @OA\Post(
     *      path="/api/userMobileVerification",
     *      operationId="verifyVerificationCode",
     *      tags={"Authentication"},
     *      summary="User mobile no Verification",
     *      description="Returns user token",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"user_id","verification_code"},
     *              @OA\Property(property="user_id", type="number", example="2"),
     *              @OA\Property(property="verification_code", type="number", example="OTG478")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User Verified Successfully!",
     *          @OA\JsonContent(
     *              @OA\Property(property="token", type="string", example="generated_token_here")
     *          ),
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function verifyVerificationCode(Request $request){
        $request->validate(
            [
                'user_id' => 'required|exists:users,pbu_id',
                'verification_code' => 'required',
            ],
            [
                'user_id.required' => 'User ID Invalid',
                'user_id.exists' => 'User ID not in the system',
                'verification_code.required' => 'Verification code Required',
            ]
        );

        $user = User::find($request->user_id);

        if ($user->pbu_verification_token === $request->verification_code) {
            if (Carbon::now()->gt($user->pbu_verification_token_expires_at)) {
                return response()->json([
                    'message' => 'Verification code has expired. Please request a new one',
                ], 422);
            }

            $user->update([
                'pbu_status' => 1,
                'pbu_verification_token' => null,
                'pbu_verification_token_expires_at' => Carbon::now()->addHours(2),
                'pbu_mobileno_verified_at' => date('Y-m-d H:i:s'),
            ]);

            // Generate a token for the user
            $token_text = $user->pbu_id.'_user_verification_session';
            $token = $user->createToken($token_text)->plainTextToken;

            return response()->json([
                'message' => 'Phone Number Validated Successfully',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user_id' => $user->pbu_id,
            ]);
        }else{
            return response()->json([
                'message' => 'Invalid verification code',
            ], 422);
        }        
    }

    /**
     * @OA\Post(
     *      path="/api/userRegistration",
     *      operationId="userRegistration",
     *      tags={"Authentication"},
     *      summary="User Registration",
     *      description="Register the user(Vendor/Customer) basic details",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"first_name", "last_name", "address", "city", "dob", "gender", "phone_no", "vendor_type", "accept_terms"},
     *              @OA\Property(property="user_id", type="number", example="2"),
     *              @OA\Property(property="first_name", type="string", example="John"),
     *              @OA\Property(property="last_name", type="string", example="Doe"),
     *              @OA\Property(property="address", type="string", example="123 Main St"),
     *              @OA\Property(property="city", type="string", example="New York"),
     *              @OA\Property(property="dob", type="string", format="date", example="1995-08-15"),
     *              @OA\Property(property="gender", type="string", example="male(1)/female(2)"),
     *              @OA\Property(property="phone_no", type="string", example="0711234567"),
     *              @OA\Property(
     *                  property="vendor_type", 
     *                  type="string", 
     *                  nullable=true,
     *                  description="Required only if user_type is 1 (Vendor)",
     *                  example="Grocery Store"
     *              ),
     *              @OA\Property(property="accept_terms", type="string", example="1"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User Registered Successfully!",
     *          @OA\JsonContent(
     *              @OA\Property(property="token", type="string", example="generated_token_here")
     *          ),
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function userRegistration(Request $request){
        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        if($user->pbu_mobileno_verified_at == null){
            return response()->json(['message' => 'User Phone No not verfied yet.'], 500);
        }
        $userRegister = null;
        if($user->pbu_usertype == '1'){
            $request->validate(
                [
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'address' => 'required',
                    'city' => 'required',
                    'dob' => 'required',
                    'gender' => 'required',
                    'vendor_type' => 'required',
                    'accept_terms' => 'required',
                ],
                [
                    'first_name.required' => 'Please enter your First Name',
                    'last_name.required' => 'Please enter your Last Name',
                    'address.required' => 'Please enter your Address',
                    'city.required' => 'Please enter your City',
                    'dob.required' => 'Please enter your Date of Birth',
                    'gender.required' => 'Please select your Gender',
                    'vendor_type.required' => 'Please select your Vendor Type',
                    'accept_terms.required' => 'Please accept the terms and conditions',
                ]
            );

            $userRegister = vendors::create([
                'pbv_vendortype' => $request->vendor_type,
                'pbv_first_name' => $request->first_name,
                'pbv_last_name' => $request->last_name,
                'pbv_address' => $request->address,
                'pbv_city' => $request->city,
                'pbv_gender' => $request->gender,
                'pbv_dob' => $request->dob,
                'pbv_accept_terms' => $request->accept_terms
            ]);

            $user->update([
                'pbu_vid' => $userRegister->pbv_id
            ]);
        }else{
            $request->validate(
                [
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'address' => 'required',
                    'city' => 'required',
                    'dob' => 'required',
                    'gender' => 'required',
                    'accept_terms' => 'required',
                ],
                [
                    'first_name.required' => 'Please enter your First Name',
                    'last_name.required' => 'Please enter your Last Name',
                    'address.required' => 'Please enter your Address',
                    'city.required' => 'Please enter your City',
                    'dob.required' => 'Please enter your Date of Birth',
                    'gender.required' => 'Please select your Gender',
                    'accept_terms.required' => 'Please accept the terms and conditions',
                ]
            );

            $userRegister = customer::create([
                'pbc_user_id' => $user->pbu_id,
                'pbc_first_name' => $request->first_name,
                'pbc_last_name' => $request->last_name,
                'pbc_address' => $request->address,
                'pbc_city' => $request->city,
                'pbc_sex' => $request->gender,
                'pbc_dob' => $request->dob,
                'pbc_accept_terms' => $request->accept_terms,
                'pbc_status' => 1
            ]);

            $user->update([
                'pbu_vid' => $userRegister->id
            ]);
        }

        $status_code = null;
        $message = "";

        if($userRegister){
            $status_code = 200;
            $message = ($user->pbu_usertype == '1') ? "Vendor Registered Successfully" : "Customer Registered Successfully";
        }else{
            $status_code = 404;
            $message = ($user->pbu_usertype == '1') ? "Vendor not registered Successfully! Please try again later." : "Customer not registered Successfully! Please try again later.";
        }

        return response()->json([
            'message' => $message,
            'user' => $user,
        ], $status_code);
    }

    /**
     * @OA\Post(
     *      path="/api/userSetNewPassword",
     *      operationId="userSetNewPassword",
     *      tags={"Authentication"},
     *      summary="User New Password Setup",
     *      description="Setup New password for registered user",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"password"},
     *              @OA\Property(property="password", type="string", example="password123")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="User New Password Set Successfully!",
     *          @OA\JsonContent(
     *              @OA\Property(property="token", type="string", example="generated_token_here")
     *          ),
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function userSetNewPassword(Request $request){
        $user = $request->user();
        $request->validate(
            [
                'password' => 'required',
            ],
            [
                'password.required' => 'Password is required',
            ]
        );

        $updateUserPassword = $user->update([
            'password' => Hash::make($request->password),
        ]);

        $status_code = null;
        $message = "";

        if($updateUserPassword){
            $status_code = 200;
            $message = "User New Password Updated Successfully!";
        }else{
            $status_code = 404;
            $message = "User New Password not updated Successfully! Please try again later.";
        }

        return response()->json([
            'message' => $message,
            'user' => $user,
        ], $status_code);
    }

    /**
     * @OA\Post(
     *      path="/api/userLogin",
     *      operationId="userLogin",
     *      tags={"Authentication"},
     *      summary="Login User",
     *      description="Returns user token",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"phone_no","password"},
     *              @OA\Property(property="phone_no", type="number", example="0711234567"),
     *              @OA\Property(property="password", type="string", example="password123")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful login",
     *          @OA\JsonContent(
     *              @OA\Property(property="token", type="string", example="generated_token_here")
     *          ),
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     * )
     */

    public function userLogin(Request $request){
        $request->validate(
            [
                'phone_no' => 'required|exists:users,pbu_mobileno',
                'password' => 'required',
            ],
            [
                'phone_no.required' => 'Phone No Required',
                'phone_no.exists' => 'Phone No not in the system',
                'password.required' => 'Password Required',
            ]
        );

        // Find user by mobile number
        $user = User::where('pbu_mobileno', $request->phone_no)->first();

        //Check if user verified the mobile no
        if($user->pbu_mobileno_verified_at == null){
            return response()->json(['message' => 'User not verfied yet.'], 500);
        }
        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid mobile number or password'], 500);
        }

        $token_text = $user->pbu_id.'_user_login_session';
        $token = $user->createToken($token_text)->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ], 200);
    }

    /**
     * @OA\Post(
     *      path="/api/userLogout",
     *      operationId="userLogout",
     *      tags={"Authentication"},
     *      security={{"bearerAuth": {}}},
     *      summary="User logout",
     *      description="Logs out the authenticated user by revoking the token.",
     *      @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Unauthenticated")
     *         )
     *      )
     * )
     */
    public function userLogout(Request $request){
        $request->user()->tokens()->delete(); // Remove all tokens

        return response()->json([
            'message' => 'Logged out successfully'
        ], 200);
    }    

    /**
     * @OA\Post(
     *      path="/api/userForgotPassword",
     *      operationId="userForgetPassword",
     *      tags={"Authentication"},
     *      summary="Fogot Password User",
     *      description="Returns user OTP",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"phone_no"},
     *              @OA\Property(property="phone_no", type="number", example="0711234567")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Password Reset request accepted. Please check the OTP in you phone.",
     *          @OA\JsonContent(
     *              @OA\Property(property="token", type="string", example="generated_token_here")
     *          ),
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     * )
     */

    public function userForgetPassword(Request $request){
        $request->validate(
            [
                'phone_no' => 'required|exists:users,pbu_mobileno',
            ],
            [
                'phone_no.required' => 'Phone No Required',
                'phone_no.exists' => 'Phone No not in the system',
            ]
        );
        
        // Find user by mobile number
        $user = User::where('pbu_mobileno', $request->phone_no)->first();

        $this->generateUserVerificationCode($user); 
        
        $token_text = $user->pbu_id.'_user_password_reset_session';
        $token = $user->createToken($token_text)->plainTextToken;

        return response()->json([
            'message' => 'Password Reset request accepted. Please check the OTP in you phone.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user_id' => $user->pbu_id
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/userResetPassword",
     *     summary="Reset Password",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "token", "password", "password_confirmation"},
     *             @OA\Property(property="user_id", type="string", example="2"),
     *             @OA\Property(property="token", type="string", example="random-generated-token"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Password reset successfully"),
     *     @OA\Response(response=400, description="Invalid token or Phone No"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */

    public function userResetPassword(Request $request){
        $request->validate(
            [
                'reset_token' => 'required',
                'user_id' => 'required',
                'password' => 'required|confirmed',
            ],
            [
                'reset_token.required' => 'Invalid Reset Token',
                'user_id.required' => 'Invalid User ID',
                'password.required' => 'Password Required',
                'password.confirmed' => 'The password confirmation does not match.',
            ]
        );

        $user = User::where('pbu_id', $request->customer_id)->first();

        $user->update([
            'password' => Hash::make($request->password),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $request->user()->tokens()->delete(); // Remove all tokens

        return response()->json([
            'message' => 'Password resetted Successfully!. Please login with new password!'
        ]);
    }

    /**
        * @OA\Get(
        *     path="/api/getUser",
        *     summary="Get authenticated user and their details",
        *     tags={"User"},
        *     security={{"bearerAuth":{}}},
        *     @OA\Response(
        *         response=200,
        *         description="User details retrieved successfully",
        *         @OA\JsonContent(
        *             @OA\Property(property="message", type="string", example="User Details"),
        *             @OA\Property(property="user", type="object",
        *                 @OA\Property(property="id", type="integer", example=1),
        *                 @OA\Property(property="pbu_usertype", type="string", example="1"),
        *                 @OA\Property(property="pbu_vid", type="integer", example=10)
        *             ),
        *             @OA\Property(property="userDetails", type="object",
        *                 @OA\Property(property="name", type="string", example="Vendor Name or Customer Name"),
        *                 @OA\Property(property="email", type="string", example="vendor@example.com")
        *             )
        *         )
        *     ),
        *     @OA\Response(
        *         response=401,
        *         description="Unauthenticated"
        *     )
        * )
    */

    public function getUser(){
        $user = auth()->user();

        return response()->json([
            'message' => 'User Details',
            'data' => $user
        ], 200);
    }   
}
