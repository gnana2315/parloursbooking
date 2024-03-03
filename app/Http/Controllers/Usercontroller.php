<?php

namespace App\Http\Controllers;
use App\Models\vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class Usercontroller extends Controller
{
    protected $vendor;

    public function __construct(vendor $vendor)
    {
        $this->vendor = $vendor;
    }
    //registration page load
    public function index(){      
        return view('pages.join');
    }

    public function register(Request $request){
        // dd($$request);
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
                'userreg_businessuserpassword' => 'required|max:8',
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
                'userreg_businessuserpassword.max' => 'Password length will be minimum 8 characters',
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
        $vendorInsert = $this->vendor->create($vendor_data);
        if($vendorInsert){
            $request->userreg_businesslogo->move(public_path('vendors/'.$documentFolderName.'/'), $businessLogoName);
            $request->userreg_businessdoc->move(public_path('vendors/'.$documentFolderName.'/'), $businessParlourCertificate);
            $request->userreg_businessregdoc->move(public_path('vendors/'.$documentFolderName.'/'), $businessRegistrationCertificate);
            return redirect('/join-with-us')->with('success', 'Vendor has been registered successfully.');
        }else{
            return redirect('/join-with-us')->with('failed', 'Error!, Vendor has been not registered.');
        }
    }
}
