<?php

namespace App\Http\Controllers\admin;
use App\Models\vendors;
use App\Models\User;
use App\Models\serviceCategory;
use App\Models\userLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PDF;
use Mail;
//use App\Mail\vendorRegistrationMail;
//use App\Notifications\VerifyEmailNotification;

class vendorsController extends Controller
{
    protected $vendors;
    protected $serviceCategory;
    protected $userLogs;
    protected $User;

    public function __construct(vendors $vendors, serviceCategory $serviceCategory, User $User, userLogs $userLogs)
    {
        $user = auth()->user();
        $this->vendors = $vendors;
        $this->serviceCategory = $serviceCategory;
        $this->userLogs = $userLogs;
        $this->User = $User;
    }

    public function index()
    {
        $vendors = $this->getAllVendorsList();

        //dd($vendors);
        // user log record
        $log_message = auth()->user()->pbu_name.' redirected to the Vendors List';
        $userlog_data = [
            'pbu_id' => auth()->user()->pbu_id,
            'pbul_description' => $log_message,
            'pbul_time' => date('Y-m-d H:i:s'),
            'pbul_status' => '1'
        ];
        $this->userLogs->create($userlog_data);

        return view('pages.admin.vendors')->with('vendors', $vendors);
    }

    public function getAllVendorsList()
    {
        $getAllVendorsData = $this->vendors->join('persons', 'persons.pbv_id', '=', 'vendor.pbv_id')
                                          ->join('users', 'users.pbu_personid', '=', 'persons.pbv_id')
                                          ->where('users.pbu_usertype', '=', '2')
                                          ->get();
        return $getAllVendorsData;
    }

    public function viewVendor($id)
    {
        $data = $this->vendors->select('vendor.*', 'servicecategory.pbsc_id','servicecategory.pbsc_name','persons.*','users.pbu_id')
                            ->join('servicecategory', 'servicecategory.pbsc_id', '=', 'vendor.pbv_servicetype')
                            ->join('persons', 'persons.pbv_id', '=', 'vendor.pbv_id')
                            ->join('users', 'users.pbu_personid', '=', 'persons.pbp_id')
                            ->where('vendor.pbv_id', '=', $id)
                            ->limit(1)
                            ->get();
        $vendorName = '';
        foreach($data as $vendor){
            $vendorName = $vendor->pbv_name;
        }
        // dd($data['pbv_name']);
        // user log record
        $log_message = 'User accessed the '.$vendorName.' data';
        $userlog_data = [
            'pbu_id' => auth()->user()->pbu_id,
            'pbul_description' => $log_message,
            'pbul_time' => date('Y-m-d H:i:s'),
            'pbul_status' => '1'
        ];
        $this->userLogs->create($userlog_data);        
        return view('pages.admin.viewvendors')->with('vendor', $data);
    }

    public function view_user_logs_by_user($id)
    {   
        $vendors_logs = $this->userLogs->select('*')
                                ->where('pbu_id', $id)
                                ->orderby('pbul_time', 'DESC');
    }

    public function activate_vendor(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,pbu_id'
        ]);
        $vendordata = $this->User->select('users.*','persons.*','vendor.*')
            ->join('persons', 'persons.pbv_id', '=', 'users.pbu_personid')
            ->join('vendor', 'vendor.pbv_id', '=', 'users.pbu_vid')
            ->where('users.pbu_id', '=', $request->id)
            ->limit(1)
            ->get();
        
        $email_pdf_data = [];
        foreach($vendordata as $vendor){
            $vendor_name = $vendor->pbp_intial.'. '.$vendor->pbp_firstname.' '.$vendor->pbp_lastname;
            
            $vendor_nic_status = (!empty($vendor->pbp_nic)) ? true : false;            
            $vendor_parlourcertificate_status = (!empty($vendor->pbv_parlourcertificate)) ? true : false;
            $vendor_br_status =  (!empty($vendor->pbv_brdoc)) ? true : false;
            $vendor_accept_term_status = ($vendor->pbv_accept_terms == '1') ? true : false;

            $vendor_id = strtotime($vendor->created_at).'_'.$vendor->pbu_vid;
            
            $email_pdf_data = [
                'vendor_name' => $vendor->pbv_name,
                'vendor_person_name' => $vendor_name,
                'vendor_address' => $vendor->pbv_address.' '.$vendor->pbv_city,
                'vendor_person_address' => $vendor->pbv_address,
                'vendor_contactno' => $vendor->pbv_contactno,
                'vendor_person_contactno' => $vendor->pbp_contactno,
                'vendor_email' => $vendor->pbv_email,
                'vendor_person_email' => $vendor->pbv_email,
                'date' => $vendor->created_at,
                'vendor_nicno' => $vendor->pbp_nicno,
                'vendor_service_type' => $vendor->pbv_servicetype,
                'vendor_logo' => $vendor->pbv_logo,
                'vendor_brno' => $vendor->pbv_brno,
                'vendor_nic_status' => $vendor_nic_status,
                'vendor_parlourcertificate_status' => $vendor_parlourcertificate_status,
                'vendor_br_status' => $vendor_br_status,
                'vendor_accept_term_status' => $vendor_accept_term_status,
                'vendor_id' => $vendor_id
            ];
        }

        $pdf = PDF::loadView('pages.admin.pdfs.vendor.contract', $email_pdf_data);
        //return $pdf->stream('example.pdf', ['Attachment' => false]);die;
        $pdf->save(storage_path('app/public/example.pdf'));

        // $userUpdate = $this->vendors->join('persons', 'persons.pbv_id', '=', 'vendor.pbv_id')
        //                             ->join('users', 'users.pbu_personid', '=', 'persons.pbp_id')
        //                             ->where('users.pbu_id', '=', $request->id)
        //                             ->update(['vendor.pbv_status' => '1', 'pbu_status' => '1']);

        // if($userUpdate){
        //     // generating official document

        //     // email vendor notification of confirmation
        //     $mail_data = [
        //         'email' => $vendordata->pbv_email,
        //         'businessname' => $vendordata->pbv_name,
        //         'name' => $vendordata->pbp_firstname.'.'.$vendordata->pbp_lastname
        //     ];
        //     Mail::to($vendordata->pbv_email,)->send(new vendorConfirmationMail($mail_data));

        //     // user log record
        //     $user = $this->User->find($request->id);
        //     $log_message = auth()->user()->pbu_name.' updated the '.$user->pbu_name.' user & vendor status';
        //     $userlog_data = [
        //         'pbu_id' => auth()->user()->pbu_id,
        //         'pbul_description' => $log_message,
        //         'pbul_time' => date('Y-m-d H:i:s'),
        //         'pbul_status' => '1'
        //     ];
        //     $this->userLogs->create($userlog_data);
        //     return response()->json(['success' => true, 'message' => 'Vendor activated successfully!']);
        // }else{
        //     return response()->json(['error' => true, 'message' => 'Vendor not activated this time!']);
        // }
    }
}
