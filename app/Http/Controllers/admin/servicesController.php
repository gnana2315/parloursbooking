<?php

namespace App\Http\Controllers\admin;
use App\Models\vendors;
use App\Models\serviceCategory;
use App\Models\services;
use App\Models\serviceType;
use App\Models\userLogs;

use App\Http\Controllers\Controller;
use Redirect,Response;
use Illuminate\Http\Request;

class servicesController extends Controller
{
    protected $vendors;
    protected $services;
    protected $serviceType;
    protected $serviceCategory;
    protected $userLogs;

    public function __construct(vendors $vendors, services $services, serviceType $serviceType, serviceCategory $serviceCategory, userLogs $userLogs)
    {
        $user = auth()->user();
        $this->vendors = $vendors;
        $this->services = $services;
        $this->serviceCategory = $serviceCategory;
        $this->serviceType = $serviceType;
        $this->userLogs = $userLogs;
    }

    public function index()
    {
        $data['services'] = $this->getAllServicesList();
        $data['serviceCategories'] = $this->getAllServiceCategoryList();
        $data['serviceTypes'] = $this->getAllServiceTypeList();

        // user log record
        $log_message = auth()->user()->pbu_name.' redirected to the Service List';
        $userlog_data = [
            'pbu_id' => auth()->user()->pbu_id,
            'pbul_description' => $log_message,
            'pbul_time' => date('Y-m-d H:i:s'),
            'pbul_status' => '1'
        ];
        $this->userLogs->create($userlog_data);

        return view('pages.admin.services')->with('data', $data);
    }

    public function getAllServicesList()
    {
        $getAllServicesData = $this->services->select('services.*','servicecategory.*', 'servicetype.*')
                                            ->join('persons', 'persons.pbv_id', '=', 'services.pbs_vendor_id')
                                            ->join('users', 'users.pbu_personid', '=', 'persons.pbp_id')
                                            ->join('servicecategory', 'servicecategory.pbsc_id', '=', 'services.pbs_servicefor_id')
                                            ->join('servicetype', 'servicetype.pbst_id', '=', 'services.pbs_category_id')
                                            ->where('services.pbs_status', '=', '1')
                                            ->where('users.pbu_id', '=', auth()->user()->pbu_id)
                                            ->get();
        
        return $getAllServicesData;
    }

    public function getAllServiceCategoryList()
    {
        $getAllServiceCategoryData = $this->serviceCategory->where('pbsc_status', '=', '1')
                                          ->get();
        
        return $getAllServiceCategoryData;
    }

    public function getAllServiceTypeList()
    {
        $getAllServiceTypeData = $this->serviceType->where('pbst_status', '=', '1')
                                          ->get();
        
        return $getAllServiceTypeData;
    }

    public function insertService(Request $request)
    {
        $request->validate(
            [
                'newServiceCategory' => 'required',
                'newServiceType' => 'required',
                'newServiceName' => 'required',
                'newServicePrice' => 'required|numeric',
            ],
            [
                'newServiceCategory.required' => 'Service Category Required',
                'newServiceType.required' => 'Service Type Required',
                'newServiceName.required' => 'Service Name Required',
                'newServicePrice.required' => 'Service Price Required',
                'newServicePrice.numeric' => 'Service Price will be numeric',
            ]
        );

        $getvendorData = $this->vendors->select('vendor.pbv_id','persons.pbv_id','persons.pbp_id','users.pbu_id','users.pbu_personid')
                                ->join('persons', 'persons.pbv_id', '=', 'vendor.pbv_id')
                                ->join('users', 'users.pbu_personid', '=', 'persons.pbp_id')
                                ->where('users.pbu_id', '=', auth()->user()->pbu_id)
                                ->get();
        $vendorID = '';
        foreach($getvendorData as $vendor){
            $vendorID = $vendor->pbv_id;
        }
        
        // service data construction
        $service_data = [
            'pbs_vendor_id' => $vendorID,
            'pbs_servicefor_id' => $request->input('newServiceCategory'),
            'pbs_category_id' => $request->input('newServiceType'),
            'pbs_name' => $request->input('newServiceName'),
            'pbs_description' => $request->input('newServiceDes'),
            'pbs_charges' => $request->input('newServicePrice'),
            'pbs_status' => '1'
        ];

        $serviceInsert = $this->services->create($service_data);

        if($serviceInsert){
            // user log record
            $log_message = auth()->user()->pbu_name.' inserted a service to the Service List';
            $userlog_data = [
                'pbu_id' => auth()->user()->pbu_id,
                'pbul_description' => $log_message,
                'pbul_time' => date('Y-m-d H:i:s'),
                'pbul_status' => '1'
            ];
            $this->userLogs->create($userlog_data);

            return redirect('/userservices')->with('success', 'Service has been saved successfully.');
        }else{
            return redirect('/userservices')->with('error', 'Service not saved at this time!');
        }
    }

    public function getService($id)
    {
        $data = $this->services->select('services.*','servicecategory.*', 'servicetype.*')
                                ->join('persons', 'persons.pbv_id', '=', 'services.pbs_vendor_id')
                                ->join('users', 'users.pbu_personid', '=', 'persons.pbp_id')
                                ->join('servicecategory', 'servicecategory.pbsc_id', '=', 'services.pbs_servicefor_id')
                                ->join('servicetype', 'servicetype.pbst_id', '=', 'services.pbs_category_id')
                                ->find($id);

        // user log record
        $log_message = auth()->user()->pbu_name.' accessed the '.$data->pbs_name;
        $userlog_data = [
            'pbu_id' => auth()->user()->pbu_id,
            'pbul_description' => $log_message,
            'pbul_time' => date('Y-m-d H:i:s'),
            'pbul_status' => '1'
        ];
        $this->userLogs->create($userlog_data);

        return response()->json($data);
    }

    public function updateService(Request $request)
    {
        $request->validate(
            [
                'editServiceCategory' => 'required',
                'editServiceType' => 'required',
                'editServiceName' => 'required',
                'editServicePrice' => 'required|numeric',
            ],
            [
                'editServiceCategory.required' => 'Service Category Required',
                'editServiceType.required' => 'Service Type Required',
                'editServiceName.required' => 'Service Name Required',
                'editServicePrice.required' => 'Service Price Required',
                'editServicePrice.numeric' => 'Service Price will be numeric',
            ]
        );
        
        $getvendorData = $this->vendors->select('vendor.pbv_id','persons.pbv_id','persons.pbp_id','users.pbu_id','users.pbu_personid')
                                ->join('persons', 'persons.pbv_id', '=', 'vendor.pbv_id')
                                ->join('users', 'users.pbu_personid', '=', 'persons.pbp_id')
                                ->where('users.pbu_id', '=', auth()->user()->pbu_id)
                                ->get();
        $vendorID = '';
        foreach($getvendorData as $vendor){
            $vendorID = $vendor->pbv_id;
        }

        // service data construction
        $service_data = [
            'pbs_vendor_id' => $vendorID,
            'pbs_servicefor_id' => $request->input('editServiceCategory'),
            'pbs_category_id' => $request->input('editServiceType'),
            'pbs_name' => $request->input('editServiceName'),
            'pbs_description' => $request->input('editServiceDes'),
            'pbs_charges' => $request->input('editServicePrice')
        ];

        $id = $request->input('editServiceID');
        $serviceItem = $this->services->findOrFail($id);
        $serviceUpdate = $serviceItem->update($service_data);

        if($serviceUpdate){
            // user log record
            $log_message = auth()->user()->pbu_name.' updated the '.$serviceItem->pbs_name;
            $userlog_data = [
                'pbu_id' => auth()->user()->pbu_id,
                'pbul_description' => $log_message,
                'pbul_time' => date('Y-m-d H:i:s'),
                'pbul_status' => '1'
            ];
            $this->userLogs->create($userlog_data);
            return redirect('/userservices')->with('success', 'Service has been updated successfully.');
        }else{
            return redirect('/userservices')->with('error', 'Service not updated at this time!');
        }
    }

    public function deleteService($id)
    {               
        $serviceItem = $this->services->find($id);

        $serviceDelete = $serviceItem->delete();

        if($serviceDelete){
            // user log record
            $log_message = auth()->user()->pbu_name.' deleted the '.$serviceItem->pbs_name;
            $userlog_data = [
                'pbu_id' => auth()->user()->pbu_id,
                'pbul_description' => $log_message,
                'pbul_time' => date('Y-m-d H:i:s'),
                'pbul_status' => '1'
            ];
            $this->userLogs->create($userlog_data);
            return redirect('/userservices')->with('success', 'Service has been deleted successfully.');
        }else{
            return redirect('/userservices')->with('error', 'Service not deleted at this time!');
        }
    }
}
