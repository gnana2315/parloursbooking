<?php

namespace App\Http\Controllers\admin;
use App\Models\vendors;
use App\Models\User;
use App\Models\serviceCategory;
use App\Models\requiredDocument;
use App\Models\vendorDocuments;
use App\Models\vendorBankInfo;
use App\Models\vendorStandardAvailability;;
use App\Models\services;
use App\Services\AuditLogService;
use App\Models\serviceFor;
use App\Models\serviceType;
use App\Models\banks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PDF;
use Mail;
use Illuminate\Support\Facades\Storage;
//use App\Mail\vendorRegistrationMail;
//use App\Notifications\VerifyEmailNotification;

class VendorsController extends Controller
{
    protected $vendors;
    protected $serviceCategory;
    protected $auditLogService;
    protected $User;

    public function __construct(vendors $vendors, serviceCategory $serviceCategory, User $User, AuditLogService $auditLogService)
    {
        $user = auth()->user();
        $this->vendors = $vendors;
        $this->serviceCategory = $serviceCategory;
        $this->auditLogService = $auditLogService;
        $this->User = $User;
    }

    public function index()
    {
        $user = auth()->user();
        $vendors = $this->getAllVendorsList();
        $serviceForList = serviceFor::where('pbsf_status', 1)->orderBy('pbsf_name')->get();

        return view('pages.admin.vendors', compact('vendors', 'serviceForList'));
    }

    public function getAllVendorsList()
    {
        $getAllVendorsData = vendors::with(['vendorType', 'serviceFor', 'user'])->latest()->get();
        return $getAllVendorsData;
    }

    public function viewVendor($id)
    {
        $vendor = vendors::with([
            'vendorType',
            'serviceFor',
            'User',
            'vendorDocuments',
            'bankInfo.bank',
            'services' => function($query) {
                $query->with([
                    'serviceType',  // Service type for each service
                    'serviceFor'    // Service for each service
                ]);
            }
        ])->where('pbv_id', $id)->first();
        $banklist = banks::where('pbb_status', 1)->orderBy('pbb_name')->get();
        $requiredDocuments = requiredDocument::where('pbrd_vendor_type', $vendor->pbv_vendortype)
                            ->orderBy('pbrd_id')
                            ->get();
        $serviceForList = serviceFor::where('pbsf_status', 1)->orderBy('pbsf_name')->get();
        $serviceTypeList = serviceType::where('pbst_status', 1)->orderBy('pbst_name')->get();
        // user log record
        if (!$vendor) {
            $log_message = 'Vendor not found (ID: '.$id.')';
            $this->auditLogService->log($log_message, $vendor, [], []);
            return response()->json(['success' => false, 'message' => 'Vendor not found'], 404);
        }else{
            $log_message = 'Vendor data retrieved successfully (ID: '.$vendor->pbv_id.')';
            $this->auditLogService->log($log_message, $vendor, [], []);
            // dd($vendor);
            return view('pages.admin.viewvendors')->with([
                'vendor' => $vendor,
                'banklist' => $banklist,
                'requiredDocuments' => $requiredDocuments,
                'serviceForList' => $serviceForList,
                'serviceTypeList' => $serviceTypeList
            ]);
        }
    }

    // public function view_user_logs_by_user($id)
    // {   
    //     $vendors_logs = $this->userLogs->select('*')
    //                             ->where('pbu_id', $id)
    //                             ->orderby('pbul_time', 'DESC');
    // }

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
        $pdf->save(storage_path('app/public/example.pdf'));
    }

    public function updateStatus(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'vendor_id' => 'required|exists:vendor,pbv_id',
            'status'  => 'required|in:0,1,2',
        ]);

        $vendor = vendors::where('pbv_id', $request->vendor_id)->first();

        $vendor_old_status = $vendor->pbv_status;
        if (!$vendor) {
            $log_message = 'Vendor not found (ID: '.$request->vendor_id.')';
            $this->auditLogService->log($log_message, $user, ['pbv_status' => $vendor_old_status], ['pbv_status' => $request->status]);
            return response()->json(['success' => false, 'message' => 'Vendor not found'], 404);
        }

        // Check completeness based on vendor type
        $vendorInfoFields = [];

        if ($vendor->pbv_vendortype == '1') {
            $vendorInfoFields = ['pbv_business_name', 'pbv_address', 'pbv_city', 'pbv_longatitude', 'pbv_latitude', 'pbv_email'];
        } elseif ($vendor->pbv_vendortype == '2') {
            $vendorInfoFields = ['pbv_business_category', 'pbv_address', 'pbv_city', 'pbv_email', 'pbv_brno'];
        } else {
            return response()->json(['message' => 'Invalid vendor type'], 400);
        }

        $allHaveValues = collect($vendorInfoFields)->every(function ($field) use ($vendor) {
            return !is_null($vendor->$field);
        });

        if (!$allHaveValues) {
            $log_message = 'Vendor information is incomplete (ID: '.$request->vendor_id.')';
            $this->auditLogService->log($log_message, $user, ['pbv_status' => $vendor_old_status], ['pbv_status' => $request->status]);
            return response()->json(['success' => false, 'message' => 'Vendor information is incomplete'], 400);
        }

        // Check required documents
        $documentIds = requiredDocument::where('pbrd_vendor_type', $vendor->pbv_vendortype)
            ->pluck('pbrd_id')
            ->toArray();        

        $documents = vendorDocuments::where('pbvd_vendor_id', $vendor->pbv_id)
                                    ->whereIn('pbvd_required_document_id', $documentIds)
                                    ->get(['pbvd_required_document_id', 'updated_at']);

        $uploadedIds = $documents->pluck('pbvd_required_document_id')->toArray();

        $allDocumentsUploaded = empty($documentIds)
                                ? true
                                : !array_diff($documentIds, $uploadedIds);
        
        $approvedIds = $documents->where('pbvd_document_status', 4)->pluck('pbvd_required_document_id')->toArray();

        $allApproved = empty($documentIds) ? true : !array_diff($documentIds, $approvedIds);

        if (!$allDocumentsUploaded || !$allApproved) {
            $log_message = 'Required documents are missing or not approved (ID: '.$request->vendor_id.')';
            $this->auditLogService->log($log_message, $user, ['pbv_status' => $vendor_old_status], ['pbv_status' => $request->status]);
            return response()->json(['success' => false, 'message' => 'Required documents are missing or not approved'], 400);
        }

        // Check bank details
        $bankDetails = vendorBankInfo::where('pbvb_vendorid', $vendor->pbv_id)->first();

        $allBankDetailsFilled = $bankDetails
                                && !empty($bankDetails->pbvb_bankname)
                                && !empty($bankDetails->pbvb_holder_name)
                                && !empty($bankDetails->pbvb_branch)
                                && !empty($bankDetails->pbvb_accountno);
        
        if (!$allBankDetailsFilled || $bankDetails->pbvb_status != 1) {
            $log_message = 'Bank details are incomplete or not approved (ID: '.$request->vendor_id.')';
            $this->auditLogService->log($log_message, $user, ['pbv_status' => $vendor_old_status], ['pbv_status' => $request->status]);
            return response()->json(['success' => false, 'message' => 'Bank details are incomplete or not approved'], 400);
        }
        
        // Check standard availability for weekdays
        $vendorAvailability = vendorStandardAvailability::where('pbvsa_vendor_id', $vendor->pbv_id)
                                                        ->whereIn('pbvsa_day', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])
                                                        ->get(['pbvsa_day', 'updated_at']);

        $requiredDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        $availableDays = $vendorAvailability->pluck('pbvsa_day')->toArray();

        $hasWeekdayAvailability = !array_diff($requiredDays, $availableDays);

        if (!$hasWeekdayAvailability) {
            $log_message = 'Standard availability for weekdays is incomplete (ID: '.$request->vendor_id.')';
            $this->auditLogService->log($log_message, $user, ['pbv_status' => $vendor_old_status], ['pbv_status' => $request->status]);
            return response()->json(['success' => false, 'message' => 'Standard availability for weekdays is incomplete'], 400);
        }

        // check if any service are present in vendor services
        $vendorServices = services::where('pbs_vendor_id', $vendor->pbv_id)
                                        ->get(['pbs_id', 'updated_at']);        

        $hasServices = !empty($vendorServices) ? true : false;

        if (!$hasServices) {
            $log_message = 'No services found for the vendor (ID: '.$request->vendor_id.')';
            $this->auditLogService->log($log_message, $user, ['pbv_status' => $vendor_old_status], ['pbv_status' => $request->status]);
            return response()->json(['success' => false, 'message' => 'No services found for the vendor'], 400);
        }

        $update = vendors::where('pbv_id', $request->vendor_id)->update(['pbv_status' => $request->status]);
        
        if(!$update){
            return response()->json(['success' => false], 500);
        }

        // user log record
        $user = auth()->user();
        $log_message = 'User updated the vendor (ID: '.$request->vendor_id.') status to '.$request->status;
        $this->auditLogService->log($log_message, $user, ['pbv_status' => $vendor_old_status], ['pbv_status' => $request->status]);

        return response()->json(['success' => true, 'message' => 'Vendor status updated successfully']);
    }

    public function updateServiceFor(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'vendor_id' => 'required|exists:vendor,pbv_id',
            'serviceFor'  => 'required|exists:service_for,pbsf_id',
        ]);

        $vendor = vendors::where('pbv_id', $request->vendor_id)->first();
        $vendor_old_service_for = $vendor->pbv_servicefor;

        if (!$vendor) {
            $log_message = 'Vendor not found (ID: '.$request->vendor_id.')';
            $this->auditLogService->log($log_message, $user, ['pbv_servicefor' => $vendor_old_service_for], ['pbv_servicefor' => $request->serviceFor]);
            return response()->json(['success' => false, 'message' => 'Vendor not found'], 404);
        }
        $update = vendors::where('pbv_id', $request->vendor_id)->update(['pbv_servicefor' => $request->serviceFor]);
        
        if(!$update){
            return response()->json(['success' => false], 500);
        }

        // user log record
        $user = auth()->user();
        $log_message = 'User updated the vendor (ID: '.$request->vendor_id.') Service For to '.$request->serviceFor;
        $this->auditLogService->log($log_message, $user, ['pbv_servicefor' => $vendor_old_service_for], ['pbv_servicefor' => $request->serviceFor]);

        return response()->json(['success' => true, 'message' => 'Vendor service for updated successfully']);
    }

    public function updateBankStatus(Request $request)
    {
        $user = auth()->user();
        try {
            $request->validate([
                'bank_info_id' => 'required|integer',
                'status' => 'required|in:0,1'
            ]);            
            
            $bankInfo = vendorBankInfo::with('vendor')->find($request->bank_info_id);
            $bankInfo_old_status = $bankInfo->pbvb_status;
            
            if (!$bankInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bank information not found'
                ], 404);
            }
            $vendor_status = $bankInfo->vendor->pbv_status;

            if($vendor_status == 2){
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update bank account status because the vendor is active'
                ], 400);
            }

            // Update the status
            $bankInfo->pbvb_status = $request->status;
            $saved = $bankInfo->save();
            
            if (!$saved) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update bank account status'
                ], 500);
            }

            // Log the activity (optional)
            $log_message = 'Updated bank account status for vendor ID: ' . $bankInfo->pbvb_vendorid . ' to ' . ($request->status == 1 ? 'Active' : 'Inactive');
            $this->auditLogService->log($log_message, $user, ['pbvb_status' => $bankInfo_old_status], ['pbvb_status' => $request->status]);
            
            return response()->json([
                'success' => true,
                'message' => 'Bank account status updated successfully',
                'status' => $request->status
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateBankInfo(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'bank_info_id' => 'required|integer',
            'bank_name' => 'required|integer',
            'account_holder' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'branch' => 'nullable|string|max:255',
        ]);
        
        $bankInfo = vendorBankInfo::find($request->bank_info_id);
        $old_bankInfo = $bankInfo->replicate();
        if (!$bankInfo) {
            return response()->json([
                'success' => false,
                'message' => 'Bank information not found'
            ], 404);
        }
        
        // Update bank information
        $bankInfo->pbvb_bankname = $request->bank_name;
        $bankInfo->pbvb_holder_name = $request->account_holder;
        $bankInfo->pbvb_accountno = $request->account_number;
        $bankInfo->pbvb_branch = $request->branch;
        
        if ($bankInfo->save()) {
            // Log the activity
            $log_message = 'Updated bank information for vendor ID: ' . $bankInfo->pbvb_vendorid;
            $this->auditLogService->log($log_message, $user, ['old_bank_info' => $old_bankInfo], ['new_bank_info' => $bankInfo]);
            
            return response()->json([
                'success' => true,
                'message' => 'Bank information updated successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update bank information'
            ], 500);
        }
    }

    public function approveDocument(Request $request)
    {
        $user = auth()->user();
        if (!$request->has('document_id') || !is_numeric($request->document_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid document ID'
            ], 400);
        }

        $document = vendorDocuments::with('vendor')->find($request->document_id);
        
        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);
        }

        $document_old_status = $document->pbvd_status;
        $vendor_status = $document->vendor->pbv_status;

        if($vendor_status == 2){
            return response()->json([
                'success' => false,
                'message' => 'Cannot update document status because the vendor is active'
            ], 400);
        }

        // Update the status
        $document->pbvd_document_status = '3';
        $saved = $document->save();
        
        if (!$saved) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update document status'
            ], 500);
        }

        // Log the activity (optional)
        $log_message = 'Approved document for vendor ID: ' . $document->pbvd_vendor_id;

        if (isset($this->auditLogService)) {
            $this->auditLogService->log($log_message, $user, ['pbvd_status' => $document_old_status], ['pbvd_status' => $request->status]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Document approved successfully',
            'status' => $request->status
        ]);
    }

    public function rejectDocument(Request $request)
    {
        $user = auth()->user();
        if (!$request->has('document_id') || !is_numeric($request->document_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid document ID'
            ], 400);
        }

        $document = vendorDocuments::with('vendor')->find($request->document_id);
        
        if (!$document) {
            return response()->json([
                'success' => false,
                'message' => 'Document not found'
            ], 404);
        }

        $document_old_status = $document->pbvd_status;
        $vendor_status = $document->vendor->pbv_status;

        if($vendor_status == 2){
            return response()->json([
                'success' => false,
                'message' => 'Cannot update document status because the vendor is active'
            ], 400);
        }

        // Update the status
        $document->pbvd_document_status = '4';
        $document->pbvd_document_extra = $request->rejection_reason ?? 'No reason provided';
        $saved = $document->save();
        
        if (!$saved) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update document status'
            ], 500);
        }

        // Log the activity (optional)
        $log_message = 'Rejected document for vendor ID: ' . $document->pbvd_vendor_id;

        if (isset($this->auditLogService)) {
            $this->auditLogService->log($log_message, $user, ['pbvd_status' => $document_old_status], ['pbvd_status' => $request->status, 'rejection_reason' => $request->rejection_reason]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Document rejected successfully',
            'status' => $request->status
        ]);
    }

    public function documentUpload(Request $request)
    {
        $user = auth()->user();
        $request->validate(
            [
                'vendor_id' => 'required|exists:vendor,pbv_id',
                'document_type_id' => 'required|integer|exists:required_document,pbrd_id',
                'document' => 'nullable|file|mimes:pdf,jpeg,png,jpg',
                'documents.*' => 'nullable|file|mimes:pdf,jpeg,png,jpg',
            ],
            [
                'vendor_id.required' => 'Vendor ID is required',
                'vendor_id.exists' => 'Vendor not found',
                'document_type_id.required' => 'Document type is required',
                'document_type_id.integer' => 'Document type ID must be an integer',
                'document_type_id.exists' => 'Document type not found',
                'document.file' => 'The uploaded file must be a valid file',
                'document.mimes' => 'The uploaded file must be a PDF, JPEG, PNG',
                'documents.*.file' => 'Each uploaded file must be a valid file',
                'documents.*.mimes' => 'Each uploaded file must be a PDF, JPEG, PNG',
            ]
        );

        $vendor = vendors::where('pbv_id', $request->vendor_id)->first();

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $filename = time().'_'.$file->getClientOriginalName();

            // store file (change 'public' to 's3' if using AWS S3)
            $filePath = $file->storeAs('uploads/vendors/'.$vendor->pbv_id, $filename, 'public');
            // full url for access (public disk: storage/app/public/uploads/...)
            $fileUrl = Storage::disk('public')->url($filePath);

            vendorDocuments::create([
                'pbvd_vendor_id' => $request->vendor_id,
                'pbvd_required_document_id' => $request->document_type_id,
                'pbvd_document_name' => $filename,
                'pbvd_document_url' => $fileUrl,
                'pbvd_document_status' => '1',
            ]);
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $file) {
                $filename = time() . '_' . $file->getClientOriginalName();

                $filePath = $file->storeAs('uploads/vendors/' . $vendor->pbv_id, $filename, 'public');
                $fileUrl = Storage::disk('public')->url($filePath);

                vendorDocuments::create([
                    'pbvd_vendor_id' => $request->vendor_id,
                    'pbvd_required_document_id' => $request->document_type_id,
                    'pbvd_document_name' => $filename,
                    'pbvd_document_url' => $fileUrl,
                    'pbvd_document_status' => '1',
                ]);
            }
        }
        
        $log_message = 'Uploaded document for vendor ID: ' . $request->vendor_id;
        if (isset($this->auditLogService)) {
            $this->auditLogService->log($log_message, $user, [], ['pbvd_document_path' => ""]);
        }

        return redirect()->route('vendor.view', ['id' => $request->vendor_id])
            ->with('success', 'Document uploaded successfully');
    }

    public function getVendorServiceById(Request $request){
        $user = auth()->user();

        $request->validate([
            'service_id' => 'required|integer|exists:services,pbs_id',
        ]);

        $service = services::with(['serviceType', 'serviceFor'])->find($request->service_id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $service
        ]);
    }

    public function updateVendorService(Request $request)
    {
        $user = auth()->user();

        $request->validate(
            [
                'edit_service_id' => 'nullable|integer|exists:services,pbs_id',
                'service_for' => 'required|integer|exists:service_for,pbsf_id',
                'service_type' => 'required|integer|exists:servicetype,pbst_id',
                'service_name' => 'required|string|max:255',
                'staff_count' => 'nullable|integer|min:0',
                'service_description' => 'nullable|string',
                'service_duration' => 'nullable|integer|min:0',
                'service_price' => 'nullable|numeric|min:0',
            ],
            [
                'edit_service_id.integer' => 'Service ID must be an integer',
                'edit_service_id.exists' => 'Service not found',
                'service_for.required' => 'Service For is required',
                'service_for.integer' => 'Service For must be an integer',
                'service_for.exists' => 'Service For not found',
                'service_type.required' => 'Service Type is required',
                'service_type.integer' => 'Service Type must be an integer',
                'service_type.exists' => 'Service Type not found',
                'service_name.required' => 'Service Name is required',
                'service_name.string' => 'Service Name must be a string',
                'service_name.max' => 'Service Name cannot exceed 255 characters',
                'staff_count.integer' => 'Staff Count must be an integer',
                'staff_count.min' => 'Staff Count cannot be negative',
                'service_description.string' => 'Service Description must be a string',
                'service_duration.integer' => 'Service Duration must be an integer',
                'service_duration.min' => 'Service Duration cannot be negative',
                'service_price.numeric' => 'Service Price must be a number',
                'service_price.min' => 'Service Price cannot be negative',
            ]
        );

        // 🔹 Check if updating or creating
        if ($request->edit_service_id) {

            // ✅ UPDATE
            $service = services::find($request->edit_service_id);

            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service not found'
                ], 404);
            }

            $old_service = $service->replicate();

        } else {

            // ✅ CREATE
            $service = new services();
            $old_service = null;
        }

        // 🔹 Assign values
        $service->pbs_service_for = $request->service_for;
        $service->pbs_service_type = $request->service_type;
        $service->pbs_name = $request->service_name;
        $service->pbs_employees = $request->staff_count;
        $service->pbs_description = $request->service_description;
        $service->pbs_duration = $request->service_duration;
        $service->pbs_duration_cetegory = 0;
        $service->pbs_price = $request->service_price;

        // ⚠️ IMPORTANT (for new service)
        if (!$request->edit_service_id) {
            $service->pbs_vendor_id = $request->vendor_id; // adjust if needed
            $service->pbs_status = 1; // default status (e.g. pending)
        }

        if ($service->save()) {

            $log_message = $request->edit_service_id
                ? 'Updated service (ID: ' . $service->pbs_id . ')'
                : 'Created new service (ID: ' . $service->pbs_id . ')';

            if (isset($this->auditLogService)) {
                $this->auditLogService->log(
                    $log_message,
                    $user,
                    ['old_service_info' => $old_service],
                    ['new_service_info' => $service]
                );
            }
            return redirect()->route('vendor.view', ['id' => $request->vendor_id])
            ->with('success', $request->edit_service_id
                    ? 'Service updated successfully'
                    : 'Service created successfully');
            // return response()->json([
            //     'success' => true,
            //     'message' => $request->edit_service_id
            //         ? 'Service updated successfully'
            //         : 'Service created successfully'
            // ]);
        }

        return redirect()->route('vendor.view', ['id' => $request->vendor_id])
            ->with('success', 'Failed to save service');
        // return response()->json([
        //     'success' => false,
        //     'message' => 'Failed to save service'
        // ], 500);
    }

    public function deleteVendorService(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'service_id' => 'required|integer|exists:services,pbs_id',
        ]);

        $service = services::find($request->service_id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }

        $old_service = $service->replicate();
        $service->pbs_status = 2; 

        if($service->save()) {
            $log_message = 'Deleted service (ID: ' . $service->pbs_id . ')';
            if (isset($this->auditLogService)) {
                $this->auditLogService->log($log_message, $user, ['deleted_service_info' => $old_service], []);
            }
            return response()->json([
                'success' => true,
                'message' => 'Service deleted successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to delete service'
        ], 500);
    }

    public function activateVendorService(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'service_id' => 'required|integer|exists:services,pbs_id',
        ]);

        $service = services::find($request->service_id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }

        $old_service = $service->replicate();
        $service->pbs_status = 1; 

        if($service->save()) {
            $log_message = 'Activated service (ID: ' . $service->pbs_id . ')';
            if (isset($this->auditLogService)) {
                $this->auditLogService->log($log_message, $user, ['old_service_info' => $old_service], ['new_service_info' => $service]);
            }
            return response()->json([
                'success' => true,
                'message' => 'Service activated successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to activate service'
        ], 500);
    }
}
