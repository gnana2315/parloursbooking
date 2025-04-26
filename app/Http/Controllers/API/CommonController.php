<?php

namespace App\Http\Controllers\API;
use App\Models\vendors;
use App\Models\services;
use App\Models\vendorType;
use App\Models\serviceType;
use App\Models\serviceFor;
use App\Models\promocode;
use App\Models\banks;
use App\Models\businessCategory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    /**
 * @OA\Get(
 *     path="/api/vendors/{vendor_type_id}",
 *     summary="Get vendors by vendor type",
 *     description="Returns list of active vendors for the specified vendor type",
 *     operationId="getVendors",
 *     tags={"Common"},
 *     @OA\Parameter(
 *         name="vendor_type_id",
 *         in="path",
 *         description="ID of vendor type to filter vendors",
 *         required=true,
 *         @OA\Schema(
 *             type="integer",
 *             format="int64"
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="success",
 *                 type="boolean",
 *                 example=true
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Vendors not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="success",
 *                 type="boolean",
 *                 example=false
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Vendors not found"
 *             )
 *         )
 *     )
 * )
 */
    public function getVendors($vendor_type_id, $token = null){
        try {
            $vendors = vendors::where([
                ['pbv_status', '=', 1],
                ['pbv_vendortype', '=', $vendor_type_id],
            ])->get();
            
            if(!empty($vendor)){
                return response()->json([
                    'success' => true,
                    'data' => $vendors
                ], 200);
            }else{
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => "Vendor Type list is empty."
                ], 404);
            }
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Vendors not found'
            ], 404);
        }
    }

    /**
 * @OA\Get(
 *     path="/api/vendors/search",
 *     summary="Search vendors with filters",
 *     description="Search vendors by name and/or city",
 *     operationId="searchVendors",
 *     tags={"Common"},
 *     @OA\Parameter(
 *         name="name",
 *         in="query",
 *         description="Vendor name to search for",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="city",
 *         in="query",
 *         description="City to filter vendors by",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="success",
 *                 type="boolean",
 *                 example=true
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="success",
 *                 type="boolean",
 *                 example=false
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Invalid parameters"
 *             )
 *         )
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */
    public function searchVendors(Request $request){        
        
        $query = vendors::query();
    
        // Basic filters
        if ($request->has('name')) {
            $query->join('pb_vendor_config', 'pb_vendor_config.pbvc_vendorid', '=', 'pb_vendor.pbv_id')->where('pbvc_display_name', 'like', '%' . $request->name . '%');
        }
        
        // Location filters
        if ($request->has('city')) {
            $query->where('pbv_city', $request->city);
        }
        
        // Rating filter
        // if ($request->has('min_rating')) {
        //     $query->join('pb_ratings', 'pb_ratings.pbr_vendor_id', '=', 'pb_vendor.pbv_id')->where('rating', '>=', $request->min_rating);
        // }
    
        // Sorting
        // $sortBy = $request->get('sort_by', 'name');
        // $sortOrder = $request->get('sort_order', 'asc');
        // $query->orderBy($sortBy, $sortOrder);
        
        // Pagination
        // $perPage = $request->get('per_page', 15);
        // $vendors = $query->paginate($perPage);
        
        if(!empty($vendors)){
            return response()->json([
                'success' => true,
                'data' => $vendors
            ], 200);
        }else{
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => "Business Type lis is empty."
            ], 404);
        }
    }

    /**
 * @OA\Get(
 *     path="/api/vendorTypes",
 *     summary="Get Vendor types for Radio",
 *     description="Returns list of vendor types (Parlour/Therapist)",
 *     operationId="getVendorTypes",
 *     tags={"Common"},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="success",
 *                 type="boolean",
 *                 example=true
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Vendor Types not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="success",
 *                 type="boolean",
 *                 example=false
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="Vendor Types are not found"
 *             )
 *         )
 *     )
 * )
 */
    public function getVendorTypes(){
        $vendorTypes = vendorType::where('pbvt_status', 1)->get();
        if ($vendorTypes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No vendor types found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $vendorTypes
        ], 200);
    }

    /**
 * @OA\Get(
 *     path="/api/serviceTypes",
 *     summary="Get all active service types",
 *     description="Returns a list of all active service types (where pbst_status = 1) (Example: Hair cut, facial)",
 *     operationId="getServiceTypes",
 *     tags={"Service Types"},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="success",
 *                 type="boolean",
 *                 example=true
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No service types found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="success",
 *                 type="boolean",
 *                 example=false
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="No service types found"
 *             )
 *         )
 *     )
 * )
 */
    public function getServiceTypes(){
        $serviceTypes = serviceType::where('pbst_status', 1)->get();
        if ($serviceTypes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No service types found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $serviceTypes
        ], 200);
    }
/**
 * @OA\Get(
 *     path="/api/serviceFor",
 *     summary="Get all active service types",
 *     description="Returns a list of all active service types (where pbst_status = 1)",
 *     operationId="getServiceFor",
 *     tags={"Service Types"},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="success",
 *                 type="boolean",
 *                 example=true
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="No service types found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(
 *                 property="success",
 *                 type="boolean",
 *                 example=false
 *             ),
 *             @OA\Property(
 *                 property="message",
 *                 type="string",
 *                 example="No service types found"
 *             )
 *         )
 *     )
 * )
 */
    public function getServiceFor(){
        $serviceFor = serviceFor::where('pbsf_status', 1)->get();

        if ($serviceFor->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No service for found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $serviceFor
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/getServices/{vendor_id}",
     *     summary="Get active services by vendor ID",
     *     description="Returns a list of active services (pbsv_status = 1) for a specific vendor",
     *     operationId="getServicesByVendor",
     *     tags={"Services"},
     *     @OA\Parameter(
     *         name="vendor_id",
     *         in="path",
     *         description="ID of the vendor",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No services found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="No services found"
     *             )
     *         )
     *     )
     * )
     */
    public function getServicesByVendor($vendor_id){
        $services = services::where([
            ['pbsv_status', '=', 1],
            ['pbsv_vendorid', '=', $vendor_id],
        ])->get();
        if ($services->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No services found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $services
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/getBankList",
     *     summary="Get Bank name list to add Vendor's Bank Details",
     *     description="Returns a list of Banks Name",
     *     operationId="getBankList",
     *     tags={"Common"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No Banks found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="No Banks found"
     *             )
     *         )
     *     )
     * )
     */
    public function getBankList(){
        $user= auth()->user();

        $banklists = banks::where('pbb_status', '=', 1)->get();

        if ($banklists->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Banks are not found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $banklists
        ], 200);
    }

    
    /**
     * @OA\Get(
     *     path="/api/getBusinessCategory",
     *     summary="Get Business category Details",
     *     description="Returns a list of Business Category (Saloon/Beauty Parlour)",
     *     operationId="getBusinessCategory",
     *     tags={"Common"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No Business Category found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="No usiness Category found"
     *             )
     *         )
     *     )
     * )
     */
    public function getBusinessCategory(){
        $user= auth()->user();

        $businesscategorylists = businessCategory::where('pbbc_status', '=', 1)->get();

        if ($businesscategorylists->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Business Categories are not found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $businesscategorylists
        ], 200);
    }
}
