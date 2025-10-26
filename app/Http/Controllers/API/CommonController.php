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
use App\Models\vendorDocuments;
use App\Models\booking;
use App\Models\paymentTransection;
use App\Models\notification;
use App\Models\vendorPayouts;
use App\Services\DialogESMSService;
use App\Services\OneSignalService;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class CommonController extends Controller
{
    /**
 * @OA\Get(
 *     path="/api/vendors/{service_for_id}",
 *     summary="Get vendors by service for",
 *     description="Returns list of active vendors for the specified service for",
 *     operationId="getVendors",
 *     tags={"Common"},
 *     @OA\Parameter(
 *         name="service_for_id",
 *         in="path",
 *         description="ID of service for to filter vendors",
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
    public function getVendors($service_for_id){
        Log::info('getVendors Requests:', ['Requests' => $service_for_id]);
        try {
            $vendors = vendors::with(['vendorDocuments', 'city', 'ratings'])
                    ->where([
                        ['pbv_status', '=', 2],
                        ['pbv_servicefor', '=', $service_for_id],
                    ])
                    ->get()
                    ->map(function ($vendor) {
                        // --- 1️⃣ Extract documents ---
                        $logo = $vendor->vendorDocuments->firstWhere('pbvd_required_document_id', 6);

                        // Get all parlour images (multiple)
                        $parlour_images = $vendor->vendorDocuments
                            ->where('pbvd_required_document_id', 7)
                            ->pluck('pbvd_document_url')
                            ->values(); // reindex array (0,1,2,...)

                        $city_id = $vendor->pbv_city;

                        // --- 2️⃣ Assign logo image if available ---
                        $vendor->pbv_logo_image = $logo ? $logo->pbvd_document_url : null;

                        // --- 3️⃣ Assign all parlour images as array ---
                        $vendor->pbv_images = $parlour_images->isNotEmpty() ? $parlour_images : null;

                        // --- 4️⃣ Assign city name ---
                        if ($city_id && $vendor->city) {
                            $vendor->pbc_cityname = $vendor->city->pbc_cityname;
                        }

                        // --- 5️⃣ Calculate average rating or null ---
                        $vendor->rating = $vendor->ratings->isNotEmpty()
                            ? round($vendor->ratings->avg('pbr_rating'), 1)
                            : null;

                        // --- 6️⃣ Format created_at / updated_at ---
                        $vendor->created_at = Carbon::parse($vendor->created_at)->format('d M Y h:i');
                        $vendor->updated_at = Carbon::parse($vendor->updated_at)->format('d M Y h:i');

                        // --- 7️⃣ Clean up ---
                        unset($vendor->vendorDocuments, $vendor->city, $vendor->ratings);

                        return $vendor;
                    });

            // $vendors = vendors::join('cities', 'cities.pbc_cid', '=', 'vendor.pbv_city')
            // //::join('vendor_config', 'vendor_config.pbvc_vendorid', '=', 'vendor.pbv_id')
            // // ->join('vendor_standard_availability', 'vendor_standard_availability.pbvsa_vendor_id', '=', 'vendor.pbv_id')
            // // ->join('ratings', 'ratings.pbr_vendor_id', '=', 'vendor.pbv_id', 'left')
            // ->select(
            //     'vendor.*',
            //     //'vendor_config.pbvc_display_name',
            //     //'vendor_config.pbvc_logo',
            //     // 'vendor_standard_availability.pbvsa_start_time',
            //     // 'vendor_standard_availability.pbvsa_end_time',
            //     // 'vendor_standard_availability.pbvsa_day',
            //     // 'vendor_standard_availability.pbvsa_is_open',
            //     'cities.pbc_cityname',
            //     // DB::raw('AVG(pb_ratings.pbr_rating) as average_rating')
            // )
            // ->where([
            //     ['pbv_status', '=', 2],
            //     ['pbv_servicefor', '=', $service_for_id],
            // ])
            // ->get();
            Log::info('getVendors Response:', ['Response' => $vendors]);
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
        Log::info('searchVendors Requests:', ['Requests' => $request->all()]);
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
        
        Log::info('getVendorTypes Response:', ['Response' => $vendorTypes]);
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
        Log::info('getServiceTypes Response:', ['Response' => $serviceTypes]);
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
        Log::info('getServiceFor Response:', ['Requests' => $serviceFor]);
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
        Log::info('getServicesByVendor Requests:', ['vendor_id' => $vendor_id]);
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
        Log::info('getServicesByVendor Response:', ['Response' => $services]);
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
        Log::info('getBankList Response:', ['Response' => $banklists]);
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

        $serviceFor = serviceFor::where('pbsf_status', 1)
                                ->get()
                                ->map(function ($item) {
                                return [
                                    'pbbc_id' => $item->pbsf_id,
                                    'pbbc_name' => $item->pbsf_name,
                                    'pbbc_description' => $item->pbsf_description,
                                    'pbbc_image' => $item->pbsf_icon,
                                    'pbbc_status' => $item->pbsf_status,
                                    'created_at' => $item->created_at,
                                    'updated_at' => $item->updated_at,
                                ];
                            });

        if ($serviceFor->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No service for found'
            ], 404);
        }
        Log::info('getBusinessCategory Response:', ['Response' => $serviceFor]);
        return response()->json([
            'success' => true,
            'data' => $serviceFor
        ], 200);
        // $businesscategorylists = businessCategory::where('pbbc_status', '=', 1)->get();

        // if ($businesscategorylists->isEmpty()) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Business Categories are not found'
        //     ], 404);
        // }
        // return response()->json([
        //     'success' => true,
        //     'data' => $businesscategorylists
        // ], 200);
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

        Log::info('getAllPromoCodes Response:', ['Response' => $promos]);
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
        Log::info('getCities Response:', ['Response' => $cities]);
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

    public function notificationlist()
    {
        $user = auth()->user();
        $notifications = notification::where(function ($q) use ($user) {
                            $q->where('pbn_user_id', $user->pbu_id)
                            ->orWhere('pbn_type', 'general');
                        })
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->map(function ($item) {
                            return [
                                'id'         => $item->pbn_id,
                                'title'      => $item->pbn_title,
                                'message'    => $item->pbn_message,
                                'type'       => $item->pbn_type,
                                'is_read'    => (bool) $item->pbn_is_read,
                                'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                            ];
                        });
        
        // $notifications = [
        //     [
        //         "id" => 1,
        //         "title" => "Booking Confirmed",
        //         "message" => "Your booking at Glow Beauty Parlour is confirmed for 20 June at 3:00 PM.",
        //         "type" => "booking",
        //         "is_read" => false,
        //         "created_at" => "2025-06-17 10:30:00"
        //     ],
        //     [
        //         "id" => 2,
        //         "title" => "New Promo Code",
        //         "message" => "Get 15% off on your next booking with code GLOW15!",
        //         "type" => "promo",
        //         "is_read" => false,
        //         "created_at" => "2025-06-16 08:45:00"
        //     ],
        //     [
        //         "id" => 3,
        //         "title" => "Profile Updated",
        //         "message" => "Your profile information has been successfully updated.",
        //         "type" => "profile",
        //         "is_read" => true,
        //         "created_at" => "2025-06-15 14:20:00"
        //     ]
        // ];
        $notificationlist = collect($notifications);
        if ($notificationlist->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No notifications found'
            ], 404);
        }
        Log::info('notificationlist Response:', ['Response' => $notificationlist]);
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
 *     path="/api/required-documents",
 *     summary="Get required documents for vendor",
 *     description="Retrieves all required documents for the authenticated vendor including uploaded files and upload constraints",
 *     tags={"Vendor Documents"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=10),
 *                     @OA\Property(property="name", type="string", example="certificates"),
 *                     @OA\Property(property="label", type="string", example="Certificates"),
 *                     @OA\Property(property="is_single", type="boolean", example=false),
 *                     @OA\Property(property="required", type="boolean", example=false),
 *                     @OA\Property(property="status", type="string", example="present", enum={"present", "missing"}),
 *                     @OA\Property(property="items_count", type="integer", example=7),
 *                     @OA\Property(
 *                         property="items",
 *                         type="array",
 *                         maxItems=3,
 *                         @OA\Items(
 *                             type="object",
 *                             @OA\Property(property="itemId", type="integer", example=901),
 *                             @OA\Property(property="file_name", type="string", example="cert_2024.pdf"),
 *                             @OA\Property(property="file_url", type="string", format="uri", example="https://api.example.com/storage/cert_2024.pdf"),
 *                             @OA\Property(property="mime", type="string", example="application/pdf"),
 *                             @OA\Property(property="size", type="integer", example=345678),
 *                             @OA\Property(property="uploaded_at", type="string", format="date-time", example="2025-09-14T10:22:11Z")
 *                         )
 *                     ),
 *                     @OA\Property(
 *                         property="constraints",
 *                         type="object",
 *                         @OA\Property(
 *                             property="allowed_types",
 *                             type="array",
 *                             @OA\Items(type="string", example="image/*")
 *                         ),
 *                         @OA\Property(property="max_size_mb", type="integer", example=2),
 *                         @OA\Property(property="max_files", type="integer", example=5)
 *                     ),
 *                     @OA\Property(property="preview_limit", type="integer", example=3)
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request - Vendor type ID is required",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Vendor type ID is required")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Vendor not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Vendor not found for the user")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal server error",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Server error")
 *         )
 *     )
 * )
 */
    // public function getRequiredDocuments(){
    //     $user = auth()->user();

    //     $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();

    //     if(!$vendor){
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Vendor not found for the user'
    //         ], 404);
    //     }

    //     $vendor_type_id = $vendor->pbv_vendortype;

    //     if($vendor_type_id){
    //         $documents = requiredDocument::where('pbrd_vendor_type', $vendor_type_id)
    //                                     ->get();

    //         $requiredDocuments = [];

    //         foreach($documents as $doc){
    //             $check_document = vendorDocuments::where('pbvd_vendor_id', $user->pbu_vid)
    //                                     ->where('pbvd_required_document_id', $doc->pbrd_id)
    //                                     ->first();

    //             $status_text = $check_document ? $check_document->pbvd_document_status : 0;
    //             // $status_text = $this->getDocumentStatusText($status);

    //             // Create document array with uploaded status
    //             $documentData = [
    //                 'id' => $doc->pbrd_id,
    //                 'name' => $doc->pbrd_name,
    //                 'label' => $doc->pbrd_label,
    //                 'is_single' => $doc->pbrd_is_single,
    //                 'required' => $doc->pbrd_required,
    //                 'document_status' => $status_text,
    //             ];

    //             // Add file information if document exists
    //             if ($check_document) {
    //                 $documentData['file_name'] = $check_document->pbvd_document_name;
    //                 $documentData['file_path'] = $check_document->pbvd_document_url;
    //                 $documentData['uploaded_at'] = $check_document->created_at;
    //             }

    //             $requiredDocuments[] = $documentData;
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'data' => $requiredDocuments
    //         ], 200);
    //     }

    //     return response()->json([
    //         'success' => false,
    //         'message' => 'Vendor type ID is required'
    //     ], 400);
    // }
    public function getRequiredDocuments()
    {
        $user = auth()->user();

        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();

        if (!$vendor) {
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found for the user'
            ], 404);
        }

        $vendor_type_id = $vendor->pbv_vendortype;

        if ($vendor_type_id) {
            $documents = requiredDocument::where('pbrd_vendor_type', $vendor_type_id)
                ->get();

            $requiredDocuments = [];

            foreach ($documents as $doc) {
                // Get all uploaded documents for this requirement
                $uploadedDocuments = vendorDocuments::where('pbvd_vendor_id', $user->pbu_vid)
                    ->where('pbvd_required_document_id', $doc->pbrd_id)
                    ->orderBy('created_at', 'desc')
                    ->get();                

                $previewItems = $uploadedDocuments->map(function ($document) {
                    return [
                        'itemId' => $document->pbvd_id,
                        'file_name' => $document->pbvd_document_name,
                        'file_url' => $document->pbvd_document_url,
                        'mime' => $this->getMimeTypeFromFileName($document->pbvd_document_name),
                        'size' => $this->getFileSize($document->pbvd_document_url), // You might need to implement this
                        'uploaded_at' => $document->created_at->toIso8601String(),
                    ];
                });


                // Parse constraints from your database or use defaults
                $constraints = [
                    'allowed_types' => $doc->pbrd_allowed_types ? json_decode($doc->pbrd_allowed_types, true) : ['image/*', 'application/pdf'],
                    'max_size_mb' => $doc->pbrd_max_size ?? 2,
                    'max_files' => $doc->pbrd_max_files ?? (($doc->pbrd_is_single = 1) ? 1 : 5)
                ];

                $documentData = [
                    'id' => $doc->pbrd_id,
                    'name' => $doc->pbrd_name,
                    'label' => $doc->pbrd_label,
                    'is_single' => (bool)$doc->pbrd_is_single,
                    'required' => (bool)$doc->pbrd_required,
                    'status' => ($uploadedDocuments->isNotEmpty()) ? (string)$uploadedDocuments->first()->pbvd_document_status : '0',
                    'items' => $previewItems->toArray(),
                    // 'items_href' => "/api/vendor-docs/{$doc->pbrd_id}/items",
                    'constraints' => $constraints,
                ];

                $requiredDocuments[] = $documentData;
            }
            Log::info('requiredDocuments Response:', ['Response' => $requiredDocuments]);
            return response()->json([
                'success' => true,
                'data' => $requiredDocuments
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Vendor type ID is required'
        ], 400);
    }

    // Helper method to get MIME type from filename
    private function getMimeTypeFromFileName($filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    // Helper method to get file size (you might need to implement this based on your storage)
    private function getFileSize($fileUrl)
    {
        // If files are stored locally
        if (strpos($fileUrl, asset('storage/')) === 0) {
            $relativePath = str_replace(asset('storage/'), '', $fileUrl);
            $filePath = storage_path('app/public/' . $relativePath);
            
            if (file_exists($filePath)) {
                return filesize($filePath);
            }
        }
        
        // Default size or implement remote file size detection
        return 0;
    }

    /**
 * @OA\Get(
 *     path="/api/getStaticsByVendor",
 *     summary="Get vendor dashboard statistics",
 *     description="Retrieves comprehensive financial and booking statistics for the authenticated vendor user",
 *     operationId="getStaticsByVendor",
 *     tags={"Common"},
 *     security={{"bearerAuth": {}}},
 *     
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="bookingsCount", type="integer", example=23, description="Total number of bookings"),
 *                 @OA\Property(property="earnedAmount", type="string", example="1,575.00", description="Total earned amount (formatted currency)"),
 *                 @OA\Property(property="paidAmount", type="string", example="645.00", description="Total paid amount (formatted currency)"),
 *                 @OA\Property(property="pendingAmount", type="string", example="930.00", description="Total pending amount (formatted currency)")
 *             )
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=404,
 *         description="Vendor not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Vendor not found for the user")
 *         )
 *     ),
 *     
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */
    public function getStaticsByVendor()
    {
        $user = auth()->user();

        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();

        if(!$vendor){
            return response()->json([
                'success' => false,
                'message' => 'Vendor not found for the user'
            ], 404);
        }

        $bookingsCount = booking::where('pbb_vendor_id', $vendor->pbv_id)->count();
        // $bookingsCount = 23;

        $earnedAmount = booking::where('pbb_vendor_id', $vendor->pbv_id)
            ->sum('pbb_total_amount');

        // $earnedAmount = 1575;
        $earnedAmount_formatted_currency = number_format($earnedAmount, 2, '.', ',');

        $paidAmount = vendorPayouts::where('pbvp_vendor_id', $vendor->pbv_id)->sum('pbvp_total_paid');

        // $paidAmount = 645;
        // if($paidAmount->isEmpty()){
        //     $paidAmount_formatted_currency = '0.00';
        // }else{
        //     // $paidAmount_formatted_currency = number_format($paidAmount->pbvp_total_paid, 2, '.', ',');
        //     $paidAmount_formatted_currency = $paidAmount->pbvp_total_paid;
        // }

        $pendingAmount = vendorPayouts::where('pbvp_vendor_id', $vendor->pbv_id)->sum('pbvp_total_due');

        // $pendingAmount = 930;
        // $pendingAmount_formatted_currency = number_format(($pendingAmount->pbvp_total_due != null) ? $pendingAmount->pbvp_total_due : 0, 2, '.', ',');
        Log::info('bookingsCount Response:', ['Response' => $bookingsCount]);
        Log::info('earnedAmount Response:', ['Response' => $earnedAmount_formatted_currency]);
        Log::info('paidAmount Response:', ['Response' => $paidAmount]);
        Log::info('pendingAmount Response:', ['Response' => $pendingAmount]);
        return response()->json([
            'success' => true,
            'data' => [
                'bookingsCount' => $bookingsCount,
                'earnedAmount' => $earnedAmount_formatted_currency,
                'paidAmount' => $paidAmount,
                'pendingAmount' => $pendingAmount
            ]
        ], 200);
    }

    public function testNotification(Request $request, OneSignalService $oneSignalService){
        $user = auth()->user();

        $request->validate(
            [
                'id' => 'required|integer|exists:users,pbu_id',
                'title' => 'required|string|max:255',
                'message' => 'required|string',
            ],
            [
                'id.required' => 'User ID is required.',
                'id.integer' => 'User ID must be an integer.',
                'id.exists' => 'User ID does not exist.',
                'title.required' => 'Title is required.',
                'title.string' => 'Title must be a string.',
                'title.max' => 'Title may not be greater than 255 characters.',
                'message.required' => 'Message is required.',
                'message.string' => 'Message must be a string.',
            ]
        );

        $notification = $oneSignalService->sendToUser(
            $request->id,
            $request->title,
            $request->message
        );
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . env('ONESIGNAL_REST_API_KEY'),
            'Content-Type' => 'application/json'
        ])->post('https://onesignal.com/api/v1/notifications', [
            'app_id' => env('ONESIGNAL_APP_ID'),
            'include_player_ids' => [$playerId],
            'headings' => ['en' => 'Test Notification'],
            'contents' => ['en' => 'Hello World!'],
        ]);

        // Log the response body to see OneSignal’s error message
        Log::info('OneSignal Response:', ['body' => $response->body(), 'status' => $response->status()]);

        dd($notification);
        return response()->json([
            'success' => true,
            'message' => 'Test notification sent.'
        ], 200);
    }

    // Helper method to get status text
    private function getDocumentStatusText($status)
    {
        switch ($status) {
            case 1:
                return 'pending';
            case 2:
                return 'rejected';
            case 3:
                return 'confirmed';
            case null:
                return 'not_uploaded';
            default:
                return 'unknown';
        }
    }
}
