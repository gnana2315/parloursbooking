<?php

namespace App\Http\Controllers\admin;
use App\Models\vendors;
use App\Models\User;
use App\Models\serviceCategory;
use App\Models\userLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
}
