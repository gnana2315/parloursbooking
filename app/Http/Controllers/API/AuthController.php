<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\customer;
use App\Models\vendors;
use App\Models\notification;
use App\Services\DialogESMSService;
use App\Models\deviceToken;
use App\Services\FirebaseService;
use App\Services\OneSignalService;

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
    protected $smsService;

    public function __construct(DialogESMSService $smsService)
    {
        $this->smsService = $smsService;
    }
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
        User::where('pbu_mobileno', $request->phone_no)
            ->where('pbu_usertype', $request->user_type)
            ->where(function ($query) {
                $query->whereNull('pbu_mobileno_verified_at')
                    ->orWhere('password', NULL);
            })
            ->delete();
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
                'phone_no.unique' => 'Phone Number Already Registered. Please use forgot password or contact our hotline.',
            ]
        );

        $user = User::create([
            'pbu_usertype' => $request->user_type,
            'pbu_mobileno' => $request->phone_no,
            'pbu_name' => $request->phone_no,
            'pbu_status' => 1
        ]);
        //dd($user);
        $verfivation_code = $this->generateVerificationCode($user->pbu_id);

        $apiKey = config('dialogesms.api_key');
        $sender = config('dialogesms.sender');
        $message = "Your OTP code is {$verfivation_code}. It is valid for 10 minutes. Please do not share this code with anyone.";
        
        // Store OTP to DB/Cache if needed here
        $smsEnable = filter_var($request->header('SMS_ENABLE', true), FILTER_VALIDATE_BOOLEAN);
        if($smsEnable){
            $result = $this->smsService->sendMessage($apiKey, [$request->phone_no], $message, $sender);       
        }
        
        return response()->json([
            'message' => 'User registered successfully. Please check the OTP in you phone.',
            'data' => $user->pbu_id,
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
    
        $verfivation_code = $this->generateVerificationCode($request->user_id);
        
        $user = User::find($request->user_id);
        
        $apiKey = config('dialogesms.api_key');
        $sender = config('dialogesms.sender');
        $message = "Your OTP code is {$verfivation_code}. It is valid for 10 minutes. Please do not share this code with anyone.";

        // Store OTP to DB/Cache if needed here
        $result = $this->smsService->sendMessage($apiKey, [$user->pbu_mobileno], $message, $sender);
        // optionally send OTP via SMS or email
        return response()->json([
            'message' => 'OTP resent successfully.',
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
                'data' => $user->pbu_id,
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
     *              @OA\Property(property="profile_image", type="file", example="profile.png"),
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
        //, FirebaseService $firebase
        $user = User::find($request->user_id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        if($user->pbu_mobileno_verified_at == null){
            return response()->json(['message' => 'User Phone No not verfied yet.'], 500);
        }
        $userRegister = null;
        $user_data = null;
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

            // $userRegister = vendors::create([
            //     'pbv_vendortype' => $request->vendor_type,
            //     'pbv_first_name' => $request->first_name,
            //     'pbv_last_name' => $request->last_name,
            //     'pbv_address' => $request->address,
            //     'pbv_city' => $request->city,
            //     'pbv_gender' => $request->gender,
            //     'pbv_dob' => $request->dob,
            //     'pbv_contactno' => $user->pbu_mobileno,
            //     'pbv_accept_terms' => $request->accept_terms
            // ]);

            $user->update([
                'pbu_mobileno' => $user->pbu_mobileno,
                'pbu_first_name' => $request->first_name,
                'pbu_last_name' => $request->last_name,
                'pbu_address' => $request->address,
                'pbu_city' => $request->city,
                'pbu_gender' => $request->gender,
                'pbu_dob' => $request->dob,
            ]);

            $userRegister = vendors::create([
                'pbv_vendortype' => $request->vendor_type,
                'pbv_first_name' => $request->first_name,
                'pbv_last_name' => $request->last_name,
                'pbv_accept_terms' => $request->accept_terms
            ]);

            $user->update([
                'pbu_vid' => $userRegister->pbv_id
            ]);
            $user_data = $user;
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

            if ($request->hasFile('profile_image')) {
                $profile_image = $request->file('profile_image');
                $folder = 'uploads/customers/';
                $folderPath = public_path($folder);

                // Create the folder if it doesn't exist
                if (!File::exists($folderPath)) {
                    File::makeDirectory($folderPath, 0755, true);
                }

                $profile_image_filename = $user->pbu_id . '_' . time() . '_profile_image.' . $profile_image->getClientOriginalExtension();
                $profile_image->move($folderPath, $profile_image_filename);

                // Generate public URL path
                $publicPath = url($folder . '/' . $profile_image_filename);

                // Save public URL in DB
                $request->merge(['profile_image' => $publicPath]);
            }

            $userRegister = customer::create([
                'pbc_user_id' => $user->pbu_id,
                'pbc_first_name' => $request->first_name,
                'pbc_last_name' => $request->last_name,
                'pbc_address' => $request->address,
                'pbc_city' => $request->city,
                'pbc_sex' => $request->gender,
                'pbc_dob' => $request->dob,
                'pbc_contact_no' => $user->pbu_mobileno,
                'pbc_profile_image' => $publicPath,
                'pbc_accept_terms' => $request->accept_terms,
                'pbc_status' => 1
            ]);

            $user->update([
                'pbu_vid' => $userRegister->pbc_id
            ]);
            $user_data = $user;
        }

        $status_code = null;
        $message = "";

        if($userRegister){
            // $firebase->sendNotification($user->device_token, 'Welcome!', 'Your profile has been created.');

            // notification::create([
            //     'pbn_user_id' => $user->id,
            //     'pbn_title' => 'Welcome!',
            //     'pbn_message' => 'Your profile has been created.',
            // ]);
            $status_code = 200;
            $message = ($user->pbu_usertype == '1') ? "Vendor Registered Successfully" : "Customer Registered Successfully";
        }else{
            $status_code = 404;
            $message = ($user->pbu_usertype == '1') ? "Vendor not registered Successfully! Please try again later." : "Customer not registered Successfully! Please try again later.";
        }

        return response()->json([
            'message' => $message,
            'data' => $user_data,
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
            'data' => $user,
        ], $status_code);
    }

    /**
     * @OA\Post(
     *     path="/api/userLogin",
     *     summary="User login",
     *     description="Login user by phone number and password. Returns access token and user details.",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="user_type", type="string", example="customer(2)/vendor(1)"),
     *             @OA\Property(property="phone_no", type="string", example="1234567890"),
     *             @OA\Property(property="password", type="string", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="access_token", type="string", example="1|xyz123token"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Invalid credentials or user not verified",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid mobile number or password")
     *         )
     *     )
     * )
     */
    public function userLogin(Request $request, OneSignalService $oneSignalService){
        $request->validate(
            [
                'user_type' => 'required',
                'phone_no' => 'required|exists:users,pbu_mobileno',
                'password' => 'required',
            ],
            [
                'user_type.required' => 'User Type undefined',
                'phone_no.required' => 'Phone No Required',
                'phone_no.exists' => 'Phone No not in the system',
                'password.required' => 'Password Required',
            ]
        );

        // Find user by mobile number
        $user = User::where('pbu_mobileno', $request->phone_no)
                    ->where('pbu_usertype', $request->user_type)
                    ->where('pbu_status', 1)
                    ->first();        
        
        // Check if user exists and password is correct
        if (!$user || empty($user->password) || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid mobile number or password'], 500);
        }

        //Check if user verified the mobile no
        if($user->pbu_mobileno_verified_at == null){
            return response()->json(['message' => 'User not verfied yet.'], 500);
        }

        if($user->pbu_status == 0){
            return response()->json(['message' => 'Please create the User.'], 500);   
        }

        if($user->pbu_usertype == '1'){
            $loggedVendors = vendors::where('pbv_id', $user->pbu_vid)->first();
        }else if($user->pbu_usertype == '2'){            
            $loggedCustomers = customer::where('pbc_user_id', $user->pbu_id)->first();
        }

        $finalData = [
            'pbu_id' => $user->pbu_id,
            'pbu_usertype' => $user->pbu_usertype,
            'pbu_vid' => ($user->pbu_usertype == '1') ? $loggedVendors->pbv_id : $loggedCustomers->pbc_id,
            'pbu_personid' => null,
            'pbu_name' => $user->pbu_name,
            'pbu_email' => $user->pbu_email,
            'pbu_mobileno' => $user->pbu_mobileno,
            'pbu_verification_token' => $user->pbu_verification_token,
            'pbu_verification_token_expires_at' => $user->pbu_verification_token_expires_at,
            'pbu_email_verified_at' => $user->pbu_email_verified_at,
            'pbu_mobileno_verified_at' => $user->pbu_mobileno_verified_at,
            'pbu_first_name' => ($user->pbu_usertype == '1') ? $loggedVendors->pbv_first_name : $loggedCustomers->pbc_first_name,
            'pbu_last_name' => ($user->pbu_usertype == '1') ? $loggedVendors->pbv_last_name : $loggedCustomers->pbc_last_name,
            'pbu_dob' => ($user->pbu_usertype == '1') ? $loggedVendors->pbv_dob : $loggedCustomers->pbc_dob,
            'pbu_gender' => ($user->pbu_usertype == '1') ? $loggedVendors->pbv_gender : $loggedCustomers->pbc_sex,
            'pbu_address' => ($user->pbu_usertype == '1') ? $loggedVendors->pbv_address : $loggedCustomers->pbc_address,
            'pbu_city' => ($user->pbu_usertype == '1') ? $loggedVendors->pbv_city : $loggedCustomers->pbc_city,
            'pbu_accept_terms' => ($user->pbu_usertype == '1') ? $loggedVendors->pbv_accept_terms : $loggedCustomers->pbc_accept_terms,
            'pbu_status' => ($user->pbu_usertype == '1') ? $loggedVendors->pbv_status : $loggedCustomers->pbc_status,
            'created_at' => ($user->pbu_usertype == '1') ? $loggedVendors->created_at : $loggedCustomers->created_at,
            'updated_at' => ($user->pbu_usertype == '1') ? $loggedVendors->updated_at : $loggedCustomers->updated_at,
        ];
        // dd($finalData);
        
        // $checkUserDeviceToken = deviceToken::where('pbdt_user_id', $user->pbu_id);
        // dd($checkUserDeviceToken->pbdt_device_token);
        // if($checkUserDeviceToken->pbdt_device_token == null){
        //     deviceToken::create([
        //         'pbdt_user_id' => $user->pbu_id,
        //         'pbdt_device_token' => $request->device_token,
        //     ]);
        // }else{
        //     $oneSignalService->sendToUser($user->pbu_id, 'Welcome!', 'Your profile has been created.');

        //     notification::create([
        //         'pbn_user_id' => $user->pbu_id,
        //         'pbn_title' => 'Welcome!',
        //         'pbn_message' => 'Your profile has been created.',
        //     ]);
        // }
        $notification_title = 'Welcome!';
        $notification_message = 'Login Successfully!';

        $oneSignalService->sendToUser($user->pbu_id, $notification_title, $notification_message);

        notification::create([
            'pbn_user_id' => $user->pbu_id,
            'pbn_type' => 'specific',
            'pbn_title' => $notification_title,
            'pbn_message' => $notification_message,
            'pbn_is_read' => 0,
        ]);

        $token_text = $user->pbu_id.'_user_login_session';
        $token = $user->createToken($token_text)->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'access_token' => $token,
            'data' => $finalData
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
    public function userLogout(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated. Invalid or expired token.',
            ], 401);
        }

        $currentToken = $request->bearerToken(); // raw JWT/Personal Access Token

        if (!$currentToken) {
            return response()->json([
                'status'  => false,
                'message' => 'Token not provided',
            ], 400);
        }

        // Get token instance from DB (works if you’re using Sanctum / Passport)
        $token = $user->tokens()->where('id', $user->currentAccessToken()->id ?? null)->first();

        if (!$token) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid or expired token',
            ], 401);
        }

        // Optional: check expiry if you’re using `expires_at` field in tokens table
        if ($token->expires_at && now()->greaterThan($token->expires_at)) {
            return response()->json([
                'status'  => false,
                'message' => 'Token has expired',
            ], 401);
        }

        // ✅ Delete current token only
        $token->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Logged out successfully',
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

    public function userForgotPassword(Request $request){
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

        $verfivation_code = $this->generateVerificationCode($user->pbu_id); 

        $apiKey = config('dialogesms.api_key');
        $sender = config('dialogesms.sender');
        $message = "Your OTP code is {$verfivation_code}. It is valid for 10 minutes. Please do not share this code with anyone.";
        
        $smsEnable = filter_var($request->header('SMS_ENABLE', true), FILTER_VALIDATE_BOOLEAN);
        
        // Store OTP to DB/Cache if needed here
        if($smsEnable){
            $result = $this->smsService->sendMessage($apiKey, [$request->phone_no], $message, $sender);
        }

        return response()->json([
            'message' => 'Password Reset request accepted. Please check the OTP in you phone.',
            'data' => $user->pbu_id,
            'otp' => $verfivation_code,
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
                'user_id' => 'required',
                'password' => 'required|confirmed',
            ],
            [
                'user_id.required' => 'Invalid User ID',
                'password.required' => 'Password Required',
                'password.confirmed' => 'The password confirmation does not match.',
            ]
        );

        $user = User::where('pbu_id', $request->user_id)->first();

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

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        $userDetails = null;
        if($user->pbu_usertype == '1'){
            $userDetails = vendors::where('pbv_id', $user->pbu_vid)->first();
        }else if($user->pbu_usertype == '2'){            
            $userDetails = customer::where('pbc_user_id', $user->pbu_id)->first();
        }

        return response()->json([
            'message' => 'User Details',
            'data' => $userDetails
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/getVendor",
     *     summary="Get vendor details for the authenticated user",
     *     description="Returns the vendor details if the authenticated user is a vendor (pbu_usertype = 1)",
     *     operationId="getVendor",
     *     tags={"Vendor"},
     *     security={{"bearerAuth":{}}},
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Vendor Details",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Vendor Details"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 example={
     *                     "pbv_id": 1,
     *                     "pbv_name": "Glamour Salon",
     *                     "pbv_city": "Mumbai",
     *                     "pbv_type": "Salon",
     *                 }
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="Vendor not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Vendor not found")
     *         )
     *     )
     * )
    */
    public function getVendor(){
        $user = auth()->user();
        if($user->pbu_usertype != '1'){
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if(!$vendor){
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        return response()->json([
            'message' => 'Vendor Details',
            'data' => $vendor
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/userDelete",
     *     summary="Delete authenticated user",
     *     description="Marks the authenticated user as deleted by updating pbv_status and setting deleted_at.",
     *     operationId="deleteUserByID",
     *     tags={"User"},
     *     security={{"bearerAuth":{}}},
     * 
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
    */

    public function deleteUserByID(){
        // $user = auth()->user();
        // if (!$user) {
        //     return response()->json(['message' => 'User not found'], 404);
        // }

        // $user->update([
        //     'pbv_status' => 0,
        //     'deleted_at' => date('Y-m-d H:i:s')
        // ]);
        $id = '21';
        $user = User::find($id);

        if ($user) {
            $user->pbu_status = 0;
            $user->save();
            return response()->json(['message' => 'User deleted successfully'], 200);
        } else {
            return response()->json(['message' => 'User not found'], 404);
        }
        // auth()->logout();

        // return response()->json(['message' => 'User deleted successfully'], 200);
    }
}
