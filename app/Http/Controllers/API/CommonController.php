<?php

namespace App\Http\Controllers\API;
use App\Models\vendors;
use App\Models\services;
use App\Models\vendorType;
use App\Models\serviceType;
use App\Models\serviceFor;
use App\Models\banks;
use App\Models\businessCategory;
use App\Models\vendorSpecialCloses;
use App\Models\promoCode;
use App\Models\cities;
use App\Models\deviceToken;
use App\Models\requiredDocument;
use App\Services\DialogESMSService;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
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
            $vendors = vendors::join('vendor_config', 'vendor_config.pbvc_vendorid', '=', 'vendor.pbv_id')
            ->join('vendor_standard_availability', 'vendor_standard_availability.pbvsa_vendor_id', '=', 'vendor.pbv_id')
            ->join('cities', 'cities.pbc_cid', '=', 'vendor.pbv_city')
            // ->join('ratings', 'ratings.pbr_vendor_id', '=', 'vendor.pbv_id', 'left')
            ->select(
                'vendor.*',
                'vendor_config.pbvc_display_name',
                'vendor_config.pbvc_logo',
                'vendor_standard_availability.pbvsa_start_time',
                'vendor_standard_availability.pbvsa_end_time',
                'vendor_standard_availability.pbvsa_day',
                'vendor_standard_availability.pbvsa_is_open',
                'cities.pbc_cityname',
                // DB::raw('AVG(pb_ratings.pbr_rating) as average_rating')
            )
            ->where([
                ['pbv_status', '=', 1],
                ['pbv_vendortype', '=', $vendor_type_id],
            ])
            // ->groupBy('vendor.pbv_id')
            ->get();
            
            if(!empty($vendors)){
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
         *     path="/api/searchVendors",
         *     summary="Search Vendors",
         *     description="Search for vendors based on keyword, service, location, category, and other filters.",
         *     tags={"Vendor"},
         *     @OA\Parameter(
         *         name="search",
         *         in="query",
         *         description="Search by business name or service name",
         *         required=false,
         *         @OA\Schema(type="string", example="Salon")
         *     ),
         *     @OA\Parameter(
         *         name="city",
         *         in="query",
         *         description="Filter by City",
         *         required=false,
         *         @OA\Schema(type="integer", example=3)
         *     ),
         *     @OA\Parameter(
         *         name="businessCategory",
         *         in="query",
         *         description="Filter by business category ID",
         *         required=false,
         *         @OA\Schema(type="integer", example=3)
         *     ),
         *     @OA\Parameter(
         *         name="serviceFor",
         *         in="query",
         *         description="Filter by service for (e.g., men/women)",
         *         required=false,
         *         @OA\Schema(type="string", example="men")
         *     ),
         *     @OA\Parameter(
         *         name="sort",
         *         in="query",
         *         description="Sort by price (price_asc or price_desc)",
         *         required=false,
         *         @OA\Schema(type="string", enum={"price_asc", "price_desc"})
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Vendors fetched successfully",
         *         @OA\JsonContent(
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(
         *                 property="data",
         *                 type="object",
         *                 description="Paginated vendor data"
         *             )
         *         )
         *     ),
         *     @OA\Response(
         *         response=404,
         *         description="No vendors found",
         *         @OA\JsonContent(
         *             @OA\Property(property="success", type="boolean", example=false),
         *             @OA\Property(property="data", type="string", example=null),
         *             @OA\Property(property="message", type="string", example="Business Type list is empty.")
         *         )
         *     )
         * )
    */

    public function searchVendors(Request $request){
        $query = vendors::query();
        $query = vendors::query()
            ->where(function ($q) use ($request) {
                if ($request->filled('search')) {
                    $q->where('pbv_business_name', 'like', '%' . $request->search . '%')
                    // ->orWhere('pbv_city', 'like', '%' . $request->search . '%')
                    ->orWhereHas('services', function ($q2) use ($request) {
                        $q2->where('pbs_name', 'like', '%' . $request->search . '%');
                    });
                }
            });

        // ✅ Eager load only matching services
        if ($request->filled('search')) {
            $query->with(['services' => function ($q) use ($request) {
                $q->where('pbs_name', 'like', '%' . $request->search . '%');
            }]);
        } else {
            $query->with('services');
        }
    
        // Basic filters
        // if ($request->filled('radius') && $request->filled('latitude') && $request->filled('longitude')) {
        //     $lat = $request->latitude;
        //     $lng = $request->longitude;
        //     $radius = $request->radius;
        //     $query->selectRaw("(
        //         6371 * acos(
        //             cos(radians(?)) *
        //             cos(radians(pbv_latitude)) *
        //             cos(radians(pbv_longatitude) - radians(?)) +
        //             sin(radians(?)) *
        //             sin(radians(pbv_latitude))
        //         )
        //     ) AS distance", [$lat, $lng, $lat])
        //     ->having('distance', '<=', $radius)
        //     ->orderBy('distance')
        //     ->get();
        // }
        
        // Location filters
        if ($request->filled('city')) {
            $query->join('cities', 'cities.pbc_cid', '=', 'vendor.pbv_city')->where('pbv_city', $request->city);
        }

        // Filter by vendorType
        // if ($request->filled('vendorType')) {
        //     $query->where('pbv_vendortype', $request->vendorType);
        // }

        // Filter by businessCategory
        if ($request->filled('businessCategory')) {
            $query->where('pbv_business_category', $request->businessCategory);
        }

        // Filter by serviceFor
        if ($request->filled('serviceFor')) {
            $query->where('pbv_servicefor', $request->serviceFor);
        }
        // Sort
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderBy('pbs_price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('pbs_price', 'desc');
                    break;
                // case 'rating_asc':
                //     $query->orderBy('rating', 'asc');
                //     break;
                // case 'rating_desc':
                //     $query->orderBy('rating', 'desc');
                //     break;
            }
        }

        
        //var_dump($query);die();
        // Pagination
        $perPage = $request->get('per_page', 15);
        $vendors = $query->paginate($perPage);
        
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
     *     path="/api/getServices/{vendor_id}",
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
        $services = services::join('service_for', 'service_for.pbsf_id', '=', 'services.pbs_service_for')        
        ->where([
            ['pbs_status', '=', 1],
            ['pbs_vendor_id', '=', $vendor_id],
        ])        
        ->get();
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
    
    /**
     * @OA\Get(
     *     path="/api/getVendorsSpecificClosings",
     *     summary="Get Vendor's Special Closing Dates",
     *     description="Returns the vendor closing dates and when customer booking those dates want to be disable",
     *     operationId="getVendorsSpecificClosings",
     *     tags={"Vendor"},
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
     *         description="No Data found",
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
     *                 example="No Data found"
     *             )
     *         )
     *     )
     * )
     */
    public function getVendorsSpecificClosings(){
        $user= auth()->user();

        $closing_data = vendorSpecialCloses::where('pbvsc_status', '=', 1)->get();

        if ($closing_data->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Closing dates not not found'
            ], 404);
        }
        return response()->json([
            'success' => true,
            'data' => $closing_data
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/getAllPromoCodes",
     *     summary="Get all active promo codes",
     *     tags={"Promo Codes"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="pbpc_id", type="integer", example=1),
     *                     @OA\Property(property="pbpc_name", type="string", example="Test Promo Code"),
     *                     @OA\Property(property="pbpc_code", type="string", example="TEST1234"),
     *                     @OA\Property(property="pbpc_discount_type", type="string", example="percentage"),
     *                     @OA\Property(property="pbpc_value", type="number", format="float", example=10),
     *                     @OA\Property(property="pbpc_discount", type="string", example="10.00"),
     *                     @OA\Property(property="pbpc_max_discount", type="number", format="float", example=100),
     *                     @OA\Property(property="pbpc_start_date", type="string", format="date", example="2025-06-01"),
     *                     @OA\Property(property="pbpc_days", type="integer", example=30),
     *                     @OA\Property(property="pbpc_end_date", type="string", format="date", example="2025-06-30"),
     *                     @OA\Property(property="pbpc_min_booking_amount", type="number", format="float", example=100),
     *                     @OA\Property(property="pbpc_uses_count", type="integer", example=0),
     *                     @OA\Property(property="pbpc_description", type="string", example="This is a test promo."),
     *                     @OA\Property(property="pbpc_image", type="string", example="/promo/image.jpg"),
     *                     @OA\Property(property="pbpc_status", type="integer", example=1),
     *                     @OA\Property(property="pbpc_promo_types", type="string", example="global"),
     *                     @OA\Property(property="pbpc_vendor_ids", type="array", @OA\Items(type="integer")),
     *                     @OA\Property(property="pbpc_service_ids", type="array", @OA\Items(type="integer")),
     *                     @OA\Property(property="pbpc_vendor_service_map", type="array", @OA\Items(type="integer")),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time"),
     *                     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true)
     *                 )
     *             )
     *         )
     *     )
     * )
     */

    public function getAllPromoCodes(){

        $promos = promoCode::where('pbpc_status', 1)->get()->toArray();

        // $organized = [
        //     'global' => [],
        //     'vendor' => [],
        //     'service' => [],
        //     'vendor_service' => [],
        // ];

        // foreach ($promos as $promo) {
        //     switch ($promo['pbpc_promo_types']) {
        //         case 'global':
        //             $organized['global'][] = $promo;
        //             break;

        //         case 'vendor':
        //             if (!empty($promo['pbpc_vendor_ids'])) {
        //                 foreach ($promo['pbpc_vendor_ids'] as $vendorId) {
        //                     $organized['vendor'][$vendorId][] = $promo;
        //                 }
        //             }
        //             break;

        //         case 'service':
        //             if (!empty($promo['pbpc_service_ids'])) {
        //                 foreach ($promo['pbpc_service_ids'] as $serviceId) {
        //                     $organized['service'][$serviceId][] = $promo;
        //                 }
        //             }
        //             break;

        //         case 'vendor_service':
        //             if (!empty($promo['pbpc_vendor_service_map'])) {
        //                 foreach ($promo['pbpc_vendor_service_map'] as $vsId) {
        //                     $vendorId = $this->getVendorIdByVendorServiceId($vsId); // You need to implement this
        //                     if ($vendorId) {
        //                         $organized['vendor_service'][$vendorId]['services'][] = $vsId;
        //                         $organized['vendor_service'][$vendorId]['promos'][] = $promo;
        //                     }
        //                 }
        //             }
        //             break;
        //     }
        // }

        // // Debug output
        // print_r($organized);
        // die();       
        return response()->json([
            'success' => true,
            'data' => $promos
        ], 200);
    }

    /**
         * @OA\Get(
         *     path="/api/cities",
         *     summary="Get active cities",
         *     description="Returns a list of active cities where `pbc_cstatus` is 1",
         *     operationId="getCities",
         *     tags={"Common"},
         *     security={{"bearerAuth":{}}},
         * 
         *     @OA\Response(
         *         response=200,
         *         description="Successful retrieval of active cities",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=true),
         *             @OA\Property(
         *                 property="data",
         *                 type="array",
         *                 @OA\Items(
         *                     type="object",
         *                     @OA\Property(property="id", type="integer", example=1),
         *                     @OA\Property(property="pbc_cityname", type="string", example="Colombo"),
         *                     @OA\Property(property="pbc_cstatus", type="integer", example=1)
         *                 )
         *             )
         *         )
         *     ),
         * 
         *     @OA\Response(
         *         response=404,
         *         description="Cities not found",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="success", type="boolean", example=false),
         *             @OA\Property(property="message", type="string", example="Cities are not found")
         *         )
         *     )
         * )
     */
    public function getCities(){
        $user= auth()->user();

        $cities = cities::where('pbc_cstatus', '=', 1)->get();
        if ($cities->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cities are not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $cities
        ], 200);
    }

    public function getVendorIdByVendorServiceId($vsId)
    {
        $vendorService = services::find($vsId);
        return $vendorService ? $vendorService->pbs_vendor_id : null;
    }

    /**
 * @OA\Post(
 *     path="/api/storeDeviceToken",
 *     summary="Store or update device token",
 *     description="Stores the device token for the authenticated user to use for push notifications.",
 *     operationId="storeDeviceToken",
 *     tags={"Common"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"device_token"},
 *             @OA\Property(property="device_token", type="string", maxLength=255, example="abcdef123456")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Device token stored successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Device token stored successfully")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(
 *                 property="errors",
 *                 type="object",
 *                 @OA\Property(
 *                     property="device_token",
 *                     type="array",
 *                     @OA\Items(type="string", example="The device token field is required.")
 *                 )
 *             )
 *         )
 *     )
 * )
 */
    public function storeDeviceToken(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string|max:255',
        ]);

        $user = auth()->user();
        $deviceToken = deviceToken::updateOrCreate(
            ['pbdt_user_id' => $user->pbu_id],
            ['pbdt_device_token' => $request->device_token]
        );

        return response()->json([
            'success' => true,
            'message' => 'Device token stored successfully',
        ], 200);
    }

    public function testNotification()
    {
        $user = auth()->user();
        $checkUserDeviceToken = deviceToken::where('pbdt_user_id', $user->pbu_id)->first();
        //dd($checkUserDeviceToken->pbdt_device_token);

        $oneSignalService->sendToUser($checkUserDeviceToken->pbdt_device_token, 'Welcome!', 'Your profile has been created.');

        notification::create([
            'pbn_user_id' => $user->pbu_id,
            'pbn_title' => 'Welcome!',
            'pbn_message' => 'Your profile has been created.',
        ]);
    }

    public function notificationlist()
    {
        $user = auth()->user();
        // $notifications = notification::where('pbn_user_id', $user->pbu_id)->orderBy('created_at', 'desc')->get();
        $notifications = [
            [
                "id" => 1,
                "title" => "Booking Confirmed",
                "message" => "Your booking at Glow Beauty Parlour is confirmed for 20 June at 3:00 PM.",
                "type" => "booking",
                "is_read" => false,
                "created_at" => "2025-06-17 10:30:00"
            ],
            [
                "id" => 2,
                "title" => "New Promo Code",
                "message" => "Get 15% off on your next booking with code GLOW15!",
                "type" => "promo",
                "is_read" => false,
                "created_at" => "2025-06-16 08:45:00"
            ],
            [
                "id" => 3,
                "title" => "Profile Updated",
                "message" => "Your profile information has been successfully updated.",
                "type" => "profile",
                "is_read" => true,
                "created_at" => "2025-06-15 14:20:00"
            ]
        ];
        $notificationlist = collect($notifications);
        if ($notificationlist->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No notifications found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $notificationlist
        ], 200);
    }

    public function sendOTP(Request $request, DialogESMSService $smsService)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        $apiKey = config('dialogesms.api_key');
        $sender = config('dialogesms.sender');
        $otp = rand(100000, 999999);
        $message = "Your OTP code is {$otp}";

        // Store OTP to DB/Cache if needed here

        $result = $smsService->sendMessage($apiKey, [$request->phone], $message, $sender);

        return response()->json([
            'status' => $result === "Success",
            'message' => $result
        ]);
    }

    /**
 * @OA\Get(
 *     path="/api/required-documents/{vendor_type_id}",
 *     summary="Get Required Documents by Vendor Type",
 *     description="Fetch all required documents for a given vendor type.",
 *     operationId="getRequiredDocuments",
 *     tags={"Common"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\Parameter(
 *         name="vendor_type_id",
 *         in="path",
 *         required=true,
 *         description="Vendor type ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="List of required documents",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="pbrd_vendortype", type="integer", example=1),
 *                     @OA\Property(property="pbrd_name", type="string", example="businessregistration"),
 *                     @OA\Property(property="pbrd_label", type="string", example="Business Registration"),
 *                     @OA\Property(property="pbrd_is_single", type="integer", example=1),
 *                     @OA\Property(property="pbrd_required", type="boolean", example=true),
 *                     @OA\Property(property="pbrd_status", type="integer", example=1),
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=400,
 *         description="Vendor type ID is required",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Vendor type ID is required")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="No required documents found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="No required documents found for this vendor type")
 *         )
 *     )
 * )
 */
    public function getRequiredDocuments(){
        $user = auth()->user();

        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();

        if(!$vendor){
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found for the user'
            ], 404);
        }

        $vendor_type_id = $vendor->pbv_vendortype;

        if($vendor_type_id){
            $documents = requiredDocument::where('pbrd_vendor_type', $vendor_type_id)
                                            ->where('pbrd_status', 1)
                                            ->get();

            return response()->json([
                'success' => true,
                'data' => $documents
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Vendor type ID is required'
        ], 400);
    }
}
