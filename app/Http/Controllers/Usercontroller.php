<?php

namespace App\Http\Controllers;
use App\Models\vendors;
use App\Models\person;
use App\Models\User;
use App\Models\userLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Hash;
use Session;
use Auth;
use Mail;
use App\Mail\vendorRegistrationMail;

class Usercontroller extends Controller
{
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
    //registration page load
    public function index(){     
        return view('pages.join');
    }

    public function register(Request $request){
        // dd($request);
        $businessLogoName = '';
        $businessParlourCertificate = '';
        $businessRegistrationCertificate = '';

        $request->validate(
            [
                'userreg_businesstype' => 'required',
                'userreg_businessname' => 'required',
                'userreg_businesslogo' => 'image|mimes:png,jpg|max:2048',
                'userreg_businessdoc' => 'mimes:pdf,jpg',
                'userreg_businessregno' => 'required|unique:vendor,pbv_brno',
                'userreg_businessregdoc' => 'required|mimes:pdf,jpg',
                'userreg_businessregemail' => 'required|email',
                'userreg_businessregaddressline1' => 'required',
                'userreg_businessregaddresscity' => 'required',
                'userreg_businessregcontactno' => 'required|max:10',
                'userreg_businessownerfirstname' => 'required',
                'userreg_businessownerlastname' => 'required',
                'userreg_businessownernicno' => 'required',
                'userreg_businessowneraddressline1' => 'required',
                'userreg_businessownercity' => 'required',
                'userreg_businessownercontactno' => 'required|max:10',
                'userreg_businessowneremail' => 'required|email|unique:persons,pbp_email',
                'userreg_businessusername' => 'required|unique:users,pbu_name',
                'userreg_businessuserpassword' => 'required|min:8',
            ],
            [
                'userreg_businesstype.required' => 'Business Type Required',
                'userreg_businessname.required' => 'Business Name Required',
                'userreg_businesslogo.image' => 'Please check the Logo file format.',
                'userreg_businesslogo.mime' => 'Logo file can be only JPG or PNG.',
                'userreg_businesslogo.max' => 'Logo image exceed 2MB',
                'userreg_businessdoc.mime' => 'Please upload PDF or JPG format',
                'userreg_businessregno.required' => 'Business Registration No Required',
                'userreg_businessregno.unique' => 'This Business already registered.',
                'userreg_businessregdoc.required' => 'This Business Document Required.',
                'userreg_businessregdoc.mime' => 'Please upload PDF or JPG format.',
                'userreg_businessregemail.required' => 'Business Email Required',
                'userreg_businessregemail.email' => 'Please enter valid Business Email',
                'userreg_businessregaddressline1.required' => 'Business Address Line 1 Required',
                'userreg_businessregaddresscity.required' => 'Business Address City Required',
                'userreg_businessregcontactno.required' => 'Business Contact No Required',
                'userreg_businessregcontactno.max' => 'Business Contact No only limited to 10 Digits',
                'userreg_businessownerfirstname.required' => 'Business Owner First Name Required',
                'userreg_businessownerlastname.required' => 'Business Owner Last Name Required',
                'userreg_businessownernicno.required' => 'Business Owner NIC No Required',
                'userreg_businessowneraddressline1.required' => 'Business Owner Address Line 1 Required',
                'userreg_businessownercity.required' => 'Business Owner Address City Required',
                'userreg_businessownercontactno.required' => 'Business Owner Contact No Required',
                'userreg_businessownercontactno.max' => 'Business Owner Contact No only limited to 10 Digits',
                'userreg_businessowneremail.required' => 'Business Owner Email Required',
                'userreg_businessowneremail.unique' => 'This Business Owner Email already registered.',
                'userreg_businessusername.required' => 'Business Owner User Name Required',
                'userreg_businessusername.unique' => 'This User Name already used. Please try different User Name.',
                'userreg_businessuserpassword.required' => 'Business Owner Password Required',
                'userreg_businessuserpassword.min' => 'Password length will be minimum 8 characters',
            ]
        );
        
        //upload folder name
        $documentFolderName = preg_replace('/\s+/', '', $request->input('userreg_businessname'));

        //dd($request->file('userreg_businesslogo')->extension());
        //logo nameing function
        if ($request->hasFile('userreg_businesslogo')) {            
            $businessLogoName = $documentFolderName.'_logo_'.time().'.'.$request->file('userreg_businesslogo')->extension(); 
        }

        //parlour certificate nameing function
        if ($request->hasFile('userreg_businessdoc')) {            
            $businessParlourCertificate = $documentFolderName.'_parlourcertificate_'.time().'.'.$request->file('userreg_businessdoc')->extension(); 
        }

        //BR certificate nameing function
        if ($request->hasFile('userreg_businessregdoc')) {            
            $businessRegistrationCertificate = $documentFolderName.'_BRCertificate_'.time().'.'.$request->file('userreg_businessregdoc')->extension(); 
        }
        
        //vendor address constructor
        if($request->input('userreg_businessregaddressline2') != ''){
            $businessAddress = $request->input('userreg_businessregaddressline1').','.$request->input('userreg_businessregaddressline2').','.$request->input('userreg_businessregaddresscity').'.';
        }else{
            $businessAddress = $request->input('userreg_businessregaddressline1').','.$request->input('userreg_businessregaddresscity').'.';
        }
        // vendor data construction
        $vendor_data = [
            'pbv_servicetype' => $request->input('userreg_businesstype'),
            'pbv_name' => $request->input('userreg_businessname'),
            'pbv_logo' => $businessLogoName,
            'pbv_parlourcertificate' => $businessParlourCertificate,
            'pbv_brno' => $request->input('userreg_businessregno'),
            'pbv_brdoc' => $businessRegistrationCertificate,
            'pbv_email' => $request->input('userreg_businessregemail'),
            'pbv_contactno' => $request->input('userreg_businessregcontactno'),
            'pbv_address' => $businessAddress,
            'pbv_city' => $request->input('userreg_businessregaddresscity'),
            'pbp_status' => '0'
        ];
        
        // dd($vendor_data);
        $vendorInsert = $this->vendors->create($vendor_data);
        if($vendorInsert){
            
            // logo & document upload
            $request->userreg_businesslogo->move(public_path('vendors/'.$documentFolderName.'/'), $businessLogoName);
            $request->userreg_businessdoc->move(public_path('vendors/'.$documentFolderName.'/'), $businessParlourCertificate);
            $request->userreg_businessregdoc->move(public_path('vendors/'.$documentFolderName.'/'), $businessRegistrationCertificate);

            $businessOwnerAddress = '';
            //person address constructor
            if($request->input('userreg_businessowneraddressline2') != ''){
                $businessOwnerAddress = $request->input('userreg_businessowneraddressline1').','.$request->input('userreg_businessowneraddressline2').','.$request->input('userreg_businessownercity').'.';
            }else{
                $businessOwnerAddress = $request->input('userreg_businessowneraddressline1').','.$request->input('userreg_businessownercity').'.';
            }

            //ownerNIC function
            if ($request->hasFile('userreg_businessownernic')) {            
                $businessOwnerNIC = $documentFolderName.'_ownerNIC_'.time().'.'.$request->file('userreg_businessownernic')->extension(); 
            }

            // person data construction
            $person_data = [
                'pbv_id' => $vendorInsert->pbv_id,
                'pbp_intial' => $request->input('userreg_businessownertitle'),
                'pbp_firstname' => $request->input('userreg_businessownerfirstname'),
                'pbp_lastname' => $request->input('userreg_businessownerlastname'),
                'pbp_nicno' => $request->input('userreg_businessownernicno'),
                'pbp_nic' => $businessOwnerNIC,
                'pbp_contactno' => $request->input('userreg_businessownercontactno'),
                'pbp_email' => $request->input('userreg_businessowneremail'),
                'pbp_address' => $businessOwnerAddress,
                'pbp_status' => '1'
            ];

            $personInsert = $this->person->create($person_data);
            
            if($personInsert){
                // Owner NIC document upload
                $request->userreg_businessownernic->move(public_path('vendors/'.$documentFolderName.'/'), $businessOwnerNIC);
            
                // user data construction
                $user_data = [
                    'pbu_usertype' => $request->input('userreg_businessusertype'),
                    'pbu_personid' => $personInsert->id,
                    'pbu_name' => $request->input('userreg_businessusername'),
                    'pbu_email' => $request->input('userreg_businessowneremail'),
                    // 'pbu_password' => Hash::make($request->input('userreg_businessuserpassword')),
                    'password' => $request->input('userreg_businessuserpassword'),
                    'pbu_status' => '1'
                ];
                // dd($user_data);
                $userInsert = $this->User->create($user_data);
                
                if($userInsert){
                    // construct userlog content
                    $log_message = 'Vendor registered and created the profile.';
                    $userlog_data = [
                        'pbu_id' => $userInsert->pbu_id,
                        'pbul_description' => $log_message,
                        'pbul_time' => date('Y-m-d H:i:s'),
                        'pbul_status' => '1'
                    ];

                    $this->userLogs->create($userlog_data);
                    //construct email content
                    $mail_data = [
                        'email' => $request->input('userreg_businessowneremail'),
                        'businessname' => $request->input('userreg_businessname'),
                        'name' => $request->input('userreg_businessownertitle').'.'.$request->input('userreg_businessownerfirstname')
                    ];
                    
                    //$this->vendorRegistrationEmail($mail_data);
                    Mail::to($request->input('userreg_businessowneremail'))->send(new vendorRegistrationMail($mail_data));

                    return redirect('/join-with-us')->with('success', 'Vendor has been registered successfully.');
                }else{
                    return redirect('/join-with-us')->with('failed', 'Error!, Vendor has been not registered.');    
                }
            }else{
                return redirect('/join-with-us')->with('failed', 'Error!, Vendor has been not registered.');
            }
        }else{
            return redirect('/join-with-us')->with('failed', 'Error!, Vendor has been not registered.');
        }
    }

    public function load_login()
    {
        return view('pages.login');
    }

    public function userLogin(Request $request)
    {
        $request->validate(
            [                
                'pbu_email' => 'required',
                'password' => 'required'
            ],
            [
                'pbu_useremail.required' => 'User Email Required',
                'password.required' => 'User Password Required'
            ]
        );
        $userDetails = $this->User->get();
        $userType= "";
        foreach($userDetails as $userDetail){
            if($userDetail->pbu_email == $request->input('pbu_email')){                
                $userType = ($userDetail->pbu_usertype);
                $userID = ($userDetail->pbu_id);
            }
        }
        $credentials = $request->only('pbu_email', 'password');
        if (auth()->attempt($credentials)) {

            // user log record
            $log_message = 'Vendor logged into the profile.';
            $userlog_data = [
                'pbu_id' => $userID,
                'pbul_description' => $log_message,
                'pbul_time' => date('Y-m-d H:i:s'),
                'pbul_status' => '1'
            ];

            $this->userLogs->create($userlog_data);
            if($userType == '0'){
                if (!auth()->user()->pbu_email_verified_at) {
                    return redirect('/login')->with('failed', 'Error!, You are not authorized.');
                }else{
                    return redirect('/superdashboard')->with('Success', 'Success!, You are authorized.');
                }
            }else if($userType == '1'){ 
                if (!auth()->user()->pbu_email_verified_at) {
                    return redirect('/login')->with('failed', 'Error!, You are not authorized.');
                }else{
                    return redirect('/dashboard')->with('Success', 'Success!, You are authorized.');
                }
            }else if($userType == '2'){
                if (!auth()->user()->pbu_email_verified_at) {
                    return redirect('/login')->with('failed', 'Error!, You are not authorized.');
                }else{
                    return redirect('/userdashboard')->with('Success', 'Success!, You are authorized.');
                }

            }
        }else{            
            return redirect('/login')->with('failed', 'Error!, Invalid Login');
        }
        //dd(Auth::user());
    }

    public function logout()
    {
        // user log record
        $log_message = 'Vendor logged out from the profile.';
        $userlog_data = [
            'pbu_id' => auth()->user()->pbu_id,
            'pbul_description' => $log_message,
            'pbul_time' => date('Y-m-d H:i:s'),
            'pbul_status' => '1'
        ];
        $this->userLogs->create($userlog_data);
        
        auth()->logout(); 
        return redirect('/login');
    }
    // public function vendorRegistrationEmail($data)
    // {
    //     // dd($data);
    //     // $content = [
    //     //     'subject' => 'Vendor Registration of '.$data->businessname,',',
    //     //     'body' => 'This is the email body of how to send email from laravel 10 with mailtrap.'
    //     // ];

    //     Mail::to('gnana2315@gmail.com')->send(new vendorRegistrationMail($data));

    //     return "Email has been sent.";
    // }
}
