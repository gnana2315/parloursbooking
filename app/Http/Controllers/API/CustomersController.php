<?php

namespace App\Http\Controllers\API;

use App\ApiResponseTrait;
use App\Models\User;
use App\Models\person;
use Validator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomersController extends Controller
{
    use ApiResponseTrait;

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'usertype' => 'required',
            'phone_no' => 'required',
            'password' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
        // $validator = Validator::make($request->all(), [
        //     'usertype' => 'required',
        //     'name' => 'required',
        //     'email' => 'required|email',
        //     'password' => 'required',
        //     'cpassword' => 'required|same:password',
        // ]);

        // if($validator->fails()){
        //     return $this->sendError('Validation Error.', $validator->errors());
        // }

        // $input = $request->all();
        // $registration_data['pbu_usertype'] = $input['usertype'];
        // $registration_data['pbu_name'] = $input['name'];
        // $registration_data['pbu_email'] = $input['email'];
        // $registration_data['pbu_name'] = $input['name'];
        // $registration_data['password'] = bcrypt($input['password']);
        // $user = User::create($registration_data);
        // $success['token'] = $user->createToken('MyApp')->plainTextToken;
        // $success['name'] = $user->name;

        return $this->sendResponse($success, 'User Registered Successfully!');
    }
}
