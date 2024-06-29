<?php

namespace App\Http\Controllers\admin;
use App\Models\serviceCategory;
use App\Models\serviceType;
use App\Models\userLogs;
use App\Models\seo_key_words;

use App\Http\Controllers\Controller;
use Redirect,Response;
use Illuminate\Http\Request;

class configController extends Controller
{
    protected $serviceCategory;
    protected $serviceType;
    protected $userLogs;
    protected $seo_key_words;

    public function __construct(serviceCategory $serviceCategory, serviceType $serviceType, userLogs $userLogs, seo_key_words $seo_key_words)
    {
        $this->serviceCategory = $serviceCategory;
        $this->serviceType = $serviceType;
        $this->userLogs = $userLogs;
        $this->seo_key_words = $seo_key_words;
    }

    public function index()
    {

    }

    // ------------------------------------- Service Categories ---------------------------------//
    public function viewServiceCategories()
    {
        $allServicesCategories = $this->getAllServiceCategoryList();

        // user log record
        $log_message = auth()->user()->pbu_name.' accessed to the Service Catogeries';
        $userlog_data = [
            'pbu_id' => auth()->user()->pbu_id,
            'pbul_description' => $log_message,
            'pbul_time' => date('Y-m-d H:i:s'),
            'pbul_status' => '1'
        ];
        $this->userLogs->create($userlog_data);

        return view('pages.admin.servicecategory')->with('serviceCategories', $allServicesCategories);
    }

    public function insertServiceCategory(Request $request)
    {
        // dd($request);
        $request->validate(
            [
                'newServiceCategory' => 'required|unique:servicecategory,pbsc_name',
            ],
            [
                'newServiceCategory.required' => 'Service Category Required',
                'newServiceCategory.unique' => 'This service category already registered.'
            ]
        );
        
        // service category data construction
        $servicecat_data = [
            'pbsc_name' => $request->input('newServiceCategory'),
            'pbsc_status' => '1'
        ];

        $serviceCategoryInsert = $this->serviceCategory->create($servicecat_data);

        if($serviceCategoryInsert){
            // user log record
            $log_message = auth()->user()->pbu_name.' Insert the '.$serviceCategoryInsert->pbsc_name.' as a Service Catogery';
            $userlog_data = [
                'pbu_id' => auth()->user()->pbu_id,
                'pbul_description' => $log_message,
                'pbul_time' => date('Y-m-d H:i:s'),
                'pbul_status' => '1'
            ];
            $this->userLogs->create($userlog_data);
            return redirect('/serviceCategoriesView')->with('success', 'Service Category has been saved successfully.');
        }else{
            return redirect('/serviceCategoriesView')->with('error', 'Service Category not saved at this time!');
        }
    }

    public function getAllServiceCategoryList()
    {
        $getAllServiceCategoryData = $this->serviceCategory->where('pbsc_status', '=', '1')
                                          ->get();
        
        return $getAllServiceCategoryData;
    }

    public function getServiceCategory($id)
    {
        $data = $this->serviceCategory->find($id);

        // user log record
        $log_message = auth()->user()->pbu_name.' accessed the '.$data->pbsc_name;
        $userlog_data = [
            'pbu_id' => auth()->user()->pbu_id,
            'pbul_description' => $log_message,
            'pbul_time' => date('Y-m-d H:i:s'),
            'pbul_status' => '1'
        ];
        $this->userLogs->create($userlog_data);

        return response()->json($data);
    }

    public function updateServiceCategory(Request $request)
    {
        $request->validate(
            [
                'editServiceCatID' => 'required',
                'editServiceCategory' => 'required|unique:servicecategory,pbsc_name',
            ],
            [
                'editServiceCatID.required' => 'Service Category not found',
                'editServiceCategory.required' => 'Service Category Required',
                'editServiceCategory.unique' => 'This service category already registered.'
            ]
        );
        
        // service category data construction
        $servicecat_data = [
            'pbsc_name' => $request->input('editServiceCategory'),
        ];

        $id = $request->input('editServiceCatID');
        $sCatItem = $this->serviceCategory->findOrFail($id);
        $serviceCategoryUpdate = $sCatItem->update($servicecat_data);

        if($serviceCategoryUpdate){
            // user log record
            $log_message = auth()->user()->pbu_name.' updated the '.$serviceCategoryInsert->pbsc_name;
            $userlog_data = [
                'pbu_id' => auth()->user()->pbu_id,
                'pbul_description' => $log_message,
                'pbul_time' => date('Y-m-d H:i:s'),
                'pbul_status' => '1'
            ];
            $this->userLogs->create($userlog_data);
            return redirect('/serviceCategoriesView')->with('success', 'Service Category has been updated successfully.');
        }else{
            return redirect('/serviceCategoriesView')->with('error', 'Service Category not updated at this time!');
        }
    }    

    public function deleteServiceCategory($id)
    {               
        $sCatItem = $this->serviceCategory->find($id);

        $serviceCategoryDelete = $sCatItem->delete();

        if($serviceCategoryDelete){
            // user log record
            $log_message = auth()->user()->pbu_name.' deleted the '.$sCatItem->pbsc_name;
            $userlog_data = [
                'pbu_id' => auth()->user()->pbu_id,
                'pbul_description' => $log_message,
                'pbul_time' => date('Y-m-d H:i:s'),
                'pbul_status' => '1'
            ];
            $this->userLogs->create($userlog_data);
            return redirect('/serviceCategoriesView')->with('success', 'Service Category has been deleted successfully.');
        }else{
            return redirect('/serviceCategoriesView')->with('error', 'Service Category not deleted at this time!');
        }
    }
    
    // ------------------------------------- Service Types ---------------------------------//
    public function viewServiceTypes()
    {
        $allServicesTypes = $this->getAllServiceTypeList(); 
        
        // user log record
        $log_message = auth()->user()->pbu_name.' accessed to the Service Types';
        $userlog_data = [
            'pbu_id' => auth()->user()->pbu_id,
            'pbul_description' => $log_message,
            'pbul_time' => date('Y-m-d H:i:s'),
            'pbul_status' => '1'
        ];
        $this->userLogs->create($userlog_data);

        return view('pages.admin.servicetype')->with('serviceTypes', $allServicesTypes);
    }

    public function getAllServiceTypeList()
    {
        $getAllServiceTypeData = $this->serviceType->where('pbst_status', '=', '1')
                                          ->get();
        
        return $getAllServiceTypeData;
    }

    public function insertServiceType(Request $request)
    {
        // dd($request);
        $request->validate(
            [
                'newServiceType' => 'required|unique:servicetype,pbst_name',
            ],
            [
                'newServiceType.required' => 'Service Type Required',
                'newServiceType.unique' => 'This service type already registered.'
            ]
        );
        
        // service category data construction
        $servicetype_data = [
            'pbst_name' => $request->input('newServiceType'),
            'pbst_status' => '1'
        ];

        $serviceTypeInsert = $this->serviceType->create($servicetype_data);

        if($serviceTypeInsert){
            // user log record
            $log_message = auth()->user()->pbu_name.' Insert the '.$serviceTypeInsert->pbst_name.' as a Service Type';
            $userlog_data = [
                'pbu_id' => auth()->user()->pbu_id,
                'pbul_description' => $log_message,
                'pbul_time' => date('Y-m-d H:i:s'),
                'pbul_status' => '1'
            ];
            $this->userLogs->create($userlog_data);
            return redirect('/serviceTypesView')->with('success', 'Service Type has been saved successfully.');
        }else{
            return redirect('/serviceTypesView')->with('error', 'Service Type not saved at this time!');
        }
    }

    public function getServiceType($id)
    {
        $data = $this->serviceType->find($id);

        // user log record
        $log_message = auth()->user()->pbu_name.' accessed the '.$data->pbst_name;
        $userlog_data = [
            'pbu_id' => auth()->user()->pbu_id,
            'pbul_description' => $log_message,
            'pbul_time' => date('Y-m-d H:i:s'),
            'pbul_status' => '1'
        ];
        $this->userLogs->create($userlog_data);

        return response()->json($data);
    }

    public function updateServiceType(Request $request)
    {
        $request->validate(
            [
                'editServiceTypeID' => 'required',
                'editServiceType' => 'required|unique:servicetype,pbst_name',
            ],
            [
                'editServiceTypeID.required' => 'Service Type not found',
                'editServiceType.required' => 'Service Type Required',
                'editServiceType.unique' => 'This service type already registered.'
            ]
        );
        
        // service type data construction
        $servicetype_data = [
            'pbst_name' => $request->input('editServiceType'),
        ];

        $id = $request->input('editServiceTypeID');
        $sTypeItem = $this->serviceType->findOrFail($id);
        $serviceTypeUpdate = $sTypeItem->update($servicetype_data);

        if($serviceTypeUpdate){
            // user log record
            $log_message = auth()->user()->pbu_name.' updated the '.$sTypeItem->pbst_name;
            $userlog_data = [
                'pbu_id' => auth()->user()->pbu_id,
                'pbul_description' => $log_message,
                'pbul_time' => date('Y-m-d H:i:s'),
                'pbul_status' => '1'
            ];
            $this->userLogs->create($userlog_data);
            return redirect('/serviceTypesView')->with('success', 'Service Type has been updated successfully.');
        }else{
            return redirect('/serviceTypesView')->with('error', 'Service Type not updated at this time!');
        }
    }    

    public function deleteServiceType($id)
    {               
        $sTypeItem = $this->serviceType->find($id);

        $serviceTypeDelete = $sTypeItem->delete();

        if($serviceTypeDelete){
            // user log record
            $log_message = auth()->user()->pbu_name.' deleted the '.$sTypeItem->pbst_name;
            $userlog_data = [
                'pbu_id' => auth()->user()->pbu_id,
                'pbul_description' => $log_message,
                'pbul_time' => date('Y-m-d H:i:s'),
                'pbul_status' => '1'
            ];
            $this->userLogs->create($userlog_data);
            return redirect('/serviceTypesView')->with('success', 'Service Type has been deleted successfully.');
        }else{            
            return redirect('/serviceTypesView')->with('error', 'Service Type not deleted at this time!');
        }
    }

    // ------------------------------------- SEO Words ---------------------------------//
    public function seoIndex()
    {
        $allSeoWords = $this->getAllSeoWords(); 
        
        // user log record
        $log_message = auth()->user()->pbu_name.' accessed to the SEO Words';
        $userlog_data = [
            'pbu_id' => auth()->user()->pbu_id,
            'pbul_description' => $log_message,
            'pbul_time' => date('Y-m-d H:i:s'),
            'pbul_status' => '1'
        ];
        $this->userLogs->create($userlog_data);

        return view('pages.admin.seowords')->with('seoWords', $allSeoWords);
    }

    public function getAllSeoWords()
    {
        $getAllSEOData = $this->seo_key_words->where('pbseo_status', '=', '1')
                                          ->get();
        
        return $getAllSEOData;
    }

    public function insertSEO(Request $request)
    {
        // dd($request);
        $request->validate(
            [
                'newSEOPage' => 'required|unique:seo_key_words,pbseo_page',
                'newSEOWords' => 'required',
            ],
            [
                'newSEOPage.required' => 'SEO Page Required',
                'newSEOPage.unique' => 'This page has active SEO already',
                'newSEOWords.required' => 'SEO Words cannot be empty'
            ]
        );
        
        // service category data construction
        $seowords_data = [
            'pbseo_page' => $request->input('newSEOPage'),
            'pbseo_words' => $request->input('newSEOWords'),
            'pbseo_status' => '1'
        ];

        $seoWordsInsert = $this->seo_key_words->create($seowords_data);       

        if($seoWordsInsert){ 
            // user log record
            $log_message = auth()->user()->pbu_name.' Insert the SEO word for '.$seoWordsInsert->pbseo_page;
            $userlog_data = [
                'pbu_id' => auth()->user()->pbu_id,
                'pbul_description' => $log_message,
                'pbul_time' => date('Y-m-d H:i:s'),
                'pbul_status' => '1'
            ];
            $this->userLogs->create($userlog_data);
            return redirect('/seoIndex')->with('success', 'SEO has been saved successfully.');
        }else{
            return redirect('/seoIndex')->with('error', 'SEO not saved at this time!');
        }
    }

    public function getSEOWords($id)
    {
        $data = $this->seo_key_words->find($id);

        // user log record
        $log_message = auth()->user()->pbu_name.' accessed the '.$data->pbseo_page.' page SEO Words';
        $userlog_data = [
            'pbu_id' => auth()->user()->pbu_id,
            'pbul_description' => $log_message,
            'pbul_time' => date('Y-m-d H:i:s'),
            'pbul_status' => '1'
        ];
        $this->userLogs->create($userlog_data);

        return response()->json($data);
    }

    public function updateSEOWords(Request $request)
    {
        $request->validate(
            [
                'editSEOWords' => 'required',
            ],
            [
                'editSEOWords.required' => 'SEO Words cannot be empty',
            ]
        );
        
        // service type data construction
        $seowords_data = [
            'pbseo_words' => $request->input('editSEOWords'),
        ];

        $id = $request->input('editSEOID');
        $seoWordItem = $this->seo_key_words->findOrFail($id);
        $seoWordUpdate = $seoWordItem->update($seowords_data);       

        if($seoWordUpdate){
             // user log record
            $log_message = auth()->user()->pbu_name.' updated the '.$seoWordItem->pbseo_page.' page SEO updated';
            $userlog_data = [
                'pbu_id' => auth()->user()->pbu_id,
                'pbul_description' => $log_message,
                'pbul_time' => date('Y-m-d H:i:s'),
                'pbul_status' => '1'
            ];
            $this->userLogs->create($userlog_data);
            return redirect('/seoIndex')->with('success', 'SEO has been updated successfully.');
        }else{
            return redirect('/seoIndex')->with('error', 'SEO not updated at this time!');
        }
    }

    public function deleteSEO($id)
    {               
        $seoItem = $this->seo_key_words->find($id);

        $seoDelete = $seoItem->delete();

        if($seoDelete){
            // user log record
            $log_message = auth()->user()->pbu_name.' deleted the '.$seoItem->pbseo_page.' page SEO';
            $userlog_data = [
                'pbu_id' => auth()->user()->pbu_id,
                'pbul_description' => $log_message,
                'pbul_time' => date('Y-m-d H:i:s'),
                'pbul_status' => '1'
            ];
            $this->userLogs->create($userlog_data);
            return redirect('/seoIndex')->with('success', 'SEO has been deleted successfully.');
        }else{            
            return redirect('/seoIndex')->with('error', 'SEO not deleted at this time!');
        }
    }
}
