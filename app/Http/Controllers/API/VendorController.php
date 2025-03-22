<?php

namespace App\Http\Controllers\API;

// use App\ApiResponseTrait;
use App\Models\vendors;
use App\Models\person;
use App\Models\User;
use App\Models\userLogs;
use Validator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorController extends Controller
{
    // use ApiResponseTrait;

    protected $vendors;
    protected $person;
    protected $User;
    protected $userLogs;

    public function __construct(vendors $vendors, person $person, User $User, userLogs $userLogs)
    {
        $this->vendors = $vendors;
        $this->person = $person;
        $this->User = $User;
        $this->userLogs = $userLogs;
    }

    public function initialRegister(Request $request){
        // $validator = Validator::make($request->all(), [
        //     'mobile' => 'required',
        //     'password' => 'required',
        // ]);

        // if($validator->fails()){
        //     return $this->sendError('Validation Error.', $validator->errors());
        // }

        // $timestamp = now()->timestamp;
        // $code = strtoupper(substr(md5($timestamp), 0, 6));

        // $input = $request->all();
        // $registration_data['pbu_usertype'] = $input['usertype'];
        // $registration_data['pbu_mobileno'] = $input['mobile'];
        // $registration_data['pbu_name'] = $input['mobile'];
        // $registration_data['password'] = bcrypt($input['password']);
        // $registration_data['pbu_verification_token'] = $code;
        // $registration_data['pbu_status'] = $input['status'];
        // $user = User::create($registration_data);
        // $success['token'] = $user->createToken('MyApp')->plainTextToken;
        // $success['name'] = $user->pbu_name;

        // return $this->sendResponse($success, 'User Registered Successfully!');
    }

    public function checkVerification(Request $request){
        //6 Digit sms verification for mobile
    }

    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'mobile' => 'required',
            'password' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        if(Auth::attempt(['pbu_mobileno' => $request->mobile, 'password' => $request->password])){
            $user = Auth::user();
            $success['token'] = $user->createToken('MyApp')->plainTextToken;
            $success['name'] = $user->pbu_name;

            return $this->sendResponse($success, 'User Logged Successfully!');
        }else{            
            return $this->sendError('Unauthorized', ['error' => 'Unauthorized']);
        }
    }
}
