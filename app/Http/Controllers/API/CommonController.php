<?php

namespace App\Http\Controllers\API;
use App\Models\vendors;
use App\Models\services;
use App\Models\businessType;
use App\Models\serviceType;
use App\Models\serviceFor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    /**
 * @OA\Get(
 *     path="/api/vendors/{business_type_id}",
 *     summary="Get vendors by business type",
 *     description="Returns list of active vendors for the specified business type",
 *     operationId="getVendors",
 *     tags={"Common"},
 *     @OA\Parameter(
 *         name="business_type_id",
 *         in="path",
 *         description="ID of business type to filter vendors",
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
    public function getVendors($business_type_id, $token = null){
        try {
            $vendors = vendors::where([
                ['pbv_status', '=', 1],
                ['pbv_businesstype', '=', $business_type_id],
            ])->get();
            
            return response()->json([
                'success' => true,
                'data' => $vendors
            ]);
            
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
    
        return response()->json([
            'success' => true,
            'data' => $vendors
        ]);
    }

    /**
 * @OA\Get(
 *     path="/api/businessTypes",
 *     summary="Get Business types for Radio",
 *     description="Returns list of business types",
 *     operationId="getBusinessTypes",
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
 *         description="Business Types not found",
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
 *                 example="Business Types not found"
 *             )
 *         )
 *     )
 * )
 */
    public function getBusinessTypes(){
        $businessTypes = businessType::where('pb_business_type_status', 1)->get();
        if ($businessTypes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No business types found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $businessTypes
        ]);
    }

    /**
 * @OA\Get(
 *     path="/api/serviceTypes",
 *     summary="Get all active service types",
 *     description="Returns a list of all active service types (where pbst_status = 1)",
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
        ]);
    }
/**
 * @OA\Get(
 *     path="/api/serviceFor",
 *     summary="Get all active service types",
 *     description="Returns a list of all active service types (where pbst_status = 1)",
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
        ]);
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
 *             ),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/Service")
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
        ]);
    }
}
