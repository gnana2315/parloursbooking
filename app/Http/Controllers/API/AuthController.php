<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
     *      path="/api/userRegister",
     *      operationId="userRegisteration",
     *      tags={"Authentication"},
     *      summary="Registeration User",
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
     *          description="User Registered Successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="OTP", type="string", example="Send to mobile")
     *          ),
     *      ),
     *      @OA\Response(response=401, description="Unauthorized"),
     * )
     */
    //user registration
    public function userRegisteration(Request $request){
        $request->validate(
            [
                'usertype' => 'required',
                'phone_no' => [
                    'required',
                    'min:10',
                    Rule::unique('users', 'pbu_mobileno')->where(function ($query) use ($request) {
                        $query->where('pbu_usertype', $request->usertype); // Check for user type
                    }),
                ],
                'password' => 'required|min:8',
            ],
            [
                'usertype.required' => 'User Type undefined',
                'phone_no.required' => 'Invalid Phone Number. Phone Number cannot be empty',
                'phone_no.min' => 'Invalid Phone Number. Phone Number Must have 10 Digits',
                'phone_no.unique' => 'Phone Number Already Registered. If you forgot password, please use forgot password, instead of Create new account.',
                'password.required' => 'Invalid Password. Password cannot be empty',
                'password.min' => 'Invalid Password. Password Must have 8 characters',
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
        $this->generateVerificationCode($user);
        

        return response()->json([
            'message' => 'User registered successfully. Please check the OTP in you phone.',
            'user_id' => $user->pbu_id
        ], 201);
    }

    public function generateVerificationCode($user){
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
                'pbu_verification_token_expires_at' => null,
                'pbu_mobileno_verified_at' => date('Y-m-d H:i:s'),
            ]);

            // Generate a token for the user
            $token_text = $user->pbu_id.'_user_verification_session';
            $token = $user->createToken($token_text)->plainTextToken;

            return response()->json([
                'message' => 'Phone Number Validated Successfully',
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
        }        
    }

    /**
     * @OA\Post(
     *      path="/api/userLogin",
     *      operationId="loginUser",
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
            return response()->json(['error' => 'User Phone No not verfied yet.'], 401);
        }
        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid mobile number or password'], 401);
        }

        $token_text = $user->pbu_id.'_user_login_session';
        $token = $user->createToken($token_text)->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'customer_id' => $user->pbu_id
        ]);
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
        ]);
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
                'password' => 'required|min:8|confirmed',
            ],
            [
                'reset_token.required' => 'Invalid Reset Token',
                'user_id.required' => 'Invalid User ID',
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

        $request->user()->tokens()->delete(); // Remove all tokens

        return response()->json([
            'message' => 'Password resetted Successfully!. Please login with new password!'
        ]);
    }
}
