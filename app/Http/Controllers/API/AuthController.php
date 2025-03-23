<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
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
     *      path="/api/vendorRegister",
     *      operationId="vendorRegisteration",
     *      tags={"Authentication"},
     *      summary="Registeration Vendor",
     *      description="Returns user OTP",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"usertype","phone_no","password"},
     *              @OA\Property(property="usertype", type="number", example="2"),
     *              @OA\Property(property="phone_no", type="number", example="0711234567"),
     *              @OA\Property(property="password", type="string", example="password123")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Vendor Registered Successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="token", type="string", example="generated_token_here")
     *          ),
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     * )
     */
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
        
        $user = User::create([
            'pbu_usertype' => $request->usertype,
            'pbu_mobileno' => $request->phone_no,
            'password' => Hash::make($request->password),
            'pbu_name' => $request->phone_no,
            'pbu_status' => 0
        ]);
        //dd($user);
        $this->generateVendorVerificationCode($user);
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Customer registered successfully. Please check the OTP in you phone.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'customer_id' => $user->pbu_id
        ], 201);

    }

    public function generateVendorVerificationCode($user){
        $verificationCode = Str::random(6); // Generate a 6-digit code
        $expiresAt = Carbon::now()->addMinutes(10); // Code expires in 10 minutes

        // Save the code and expiration time
        $user->update([
            'pbu_verification_token' => $verificationCode,
            'pbu_verification_token_expires_at' => $expiresAt,
        ]);
    }
/**
     * @OA\Post(
     *      path="/api/vendorMobileVerification",
     *      operationId="vendorMobileNoVerification",
     *      tags={"Authentication"},
     *      summary="Vendor mobile no Verification",
     *      description="Returns user token",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"customer_id","verification_code"},
     *              @OA\Property(property="customer_id", type="number", example="2"),
     *              @OA\Property(property="verification_code", type="number", example="OTG478")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Vendor Verified Successfully!",
     *          @OA\JsonContent(
     *              @OA\Property(property="token", type="string", example="generated_token_here")
     *          ),
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    public function verifyVendorVerificationCode(Request $request){
        $request->validate(
            [
                'customer_id' => 'required|exists:users,pbu_id',
                'verification_code' => 'required',
            ],
            [
                'customer_id.required' => 'Customer ID Invalid',
                'customer_id.exists' => 'Customer ID not in the system',
                'verification_code.required' => 'Verification code Required',
            ]
        );

        $user = User::find($request->customer_id);

        if ($user->pbu_verification_token === $request->verification_code) {
            if (Carbon::now()->gt($user->pbu_verification_token_expires_at)) {
                return response()->json([
                    'message' => 'Verification code has expired. Please request a new one',
                ], 422);
            }

            $user->update([
                'pbu_status' => 1,
                'pbu_verification_token' => null,
                'pbu_verification_token_expires_at' => null,
                'pbu_mobileno_verified_at' => date('Y-m-d H:i:s'),
            ]);

            // Generate a token for the user
            $token = $user->createToken('customer_mobile_verification')->plainTextToken;

            return response()->json([
                'message' => 'Mobile number verified successfully',
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        }        
    }

    /**
     * @OA\Post(
     *      path="/api/vendorlogin",
     *      operationId="loginVendor",
     *      tags={"Authentication"},
     *      summary="Login Vendor",
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

    public function vendorLogin(Request $request){
        $request->validate(
            [
                'phone_no' => 'required|exists:users,pbu_mobileno',
                'password' => 'required|min:8',
            ],
            [
                'phone_no.required' => 'Phone No Required',
                'phone_no.exists' => 'Phone No not in the system',
                'password.required' => 'Password Required',
                'password.min' => 'Password minimum length shild be 8 characters',
            ]
        );

        // Find user by mobile number
        $user = User::where('pbu_mobileno', $request->phone_no)->first();

        //Check if user verified the mobile no
        if($user->pbu_mobileno_verified_at == null){
            return response()->json(['error' => 'Customer Phone No not verfied yet.'], 401);
        }
        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid mobile number or password'], 401);
        }

        $token_text = $user->pbu_id.'_login_session';
        $token = $user->createToken($token_text)->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'customer_id' => $user->pbu_id
        ]);
    }

    public function vendorLogout(Request $request){
        $request->user()->tokens()->delete(); // Remove all tokens

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    public function vendorForgetPassword(Request $request){
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

        $this->generateVendorVerificationCode($user); 
        
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Password Reset request accepted. Please check the OTP in you phone.',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'customer_id' => $user->pbu_id
        ], 201);
    }

    public function vendorResetPassword(Request $request){
        $request->validate(
            [
                'customer_id' => 'required',
                'password' => 'required|min:8|confirmed',
            ],
            [
                'customer_id.required' => 'Invalid Customer ID',
                'password.required' => 'Password Required',
                'password.min' => 'Password minimum length shild be 8 characters',
                'password.confirmed' => 'The password confirmation does not match.',
            ]
        );

        $user = User::where('pbu_id', $request->customer_id)->first();

        $user->update([
            'password' => Hash::make($request->password),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return response()->json([
            'message' => 'Password resetted Successfully!. Please login with new password!'
        ]);
    }
}
