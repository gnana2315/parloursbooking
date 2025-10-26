<?php

namespace App\Http\Controllers\API;

//use App\ApiResponseTrait;
use App\Models\User;
use App\Models\customer;
use App\Models\vendors;
use Validator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CustomersController extends Controller
{
    public function register($id, Request $request){
        Log::info('Register Requests:', ['Requests' => $request->all()]);
        Log::info('Register id:', ['id' => $id]);
        $user = auth()->user();

        $request->validate(
            [
                'intial' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'dob' => 'required',
                'nic_no' => 'required',
                'email' => 'email|unique:customer,pbc_email',
            ],
            [
                'intial.required' => 'Customer Initial Required',
                'first_name.required' => 'Customer First Name Required',
                'last_name.required' => 'Customer Last Name Required',
                'email.email' => 'Customer Email Not Valid',
                'email.unique' => 'Customer Email already in use',
                'nic_no.required' => 'Customer NIC No Required',
            ]
        );

        $customer = customer::create([
            'pbc_user_id' => $id,
            'pbc_intial' => $request->intial,
            'pbc_first_name' => $request->first_name,
            'pbc_last_name' => $request->last_name,
            'pbc_dob' => $request->last_name,
            'pbc_nic_no' => $request->nic_no,
            'pbc_sex' => $request->sex,
            'pbc_address' => $request->address,
            'pbc_city' => $request->city,
            'pbc_email' => $request->city,
            'pbc_contact_no' => $user->pbu_mobileno,
            'pbc_status' => 1,
            'pbc_accept_terms' => $request->accept_terms,
        ]);

        if($customer){
            $message = 'Customer Details saved successfully';
            $status = 200;
        }else{
            $message = 'Customer Details failed to save';
            $status = 500;
        }
        Log::info('Register Response:', ['Response' => $user]);

        return response()->json([
            'message' => $message,
            'data' => $user
        ], $status);
    }

    /**
         * @OA\Post(
         *     path="/api/customer/favourite",
         *     summary="Add or remove an item from the customer's favourites",
         *     operationId="addRemoveCustomerFavourite",
         *     tags={"Customer"},
         *     security={{"bearerAuth":{}}},
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"favourite_id", "isFav"},
         *             @OA\Property(property="favourite_id", type="integer", example=123, description="ID of the item to be added or removed from favourites"),
         *             @OA\Property(property="isFav", type="boolean", example=true, description="Set to true to add to favourites, false to remove")
         *         )
         *     ),
         *     @OA\Response(
         *         response=200,
         *         description="Favourite updated successfully",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Favourite updated successfully")
         *         )
         *     ),
         *     @OA\Response(
         *         response=404,
         *         description="Customer not found",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Customer not found")
         *         )
         *     )
         * )
     */
    public function addRemoveCustomerFavourite(Request $request){
        Log::info('addRemoveCustomerFavourite Requests:', ['Requests' => $request->all()]);
        $user = auth()->user();

        $request->validate(
            [
                'favourite_id' => 'required',
                'isFav' => 'required',
            ],
            [
                'favourite_id.required' => 'Favourite ID Required',
                'isFav.required' => 'Favourite Status Required',
            ]
        );

        $customer = customer::where('pbc_user_id', $user->pbu_id)->first();
        // dd(auth()->id());
        if(!$customer){
            return response()->json([
                'message' => 'Customer not found',
            ], 404);
        }  

        // Load current favourites or start fresh
        $favourites = $customer->pbc_fav ?? [];
        $message = '';

        if ($request->isFav) {
            if (!in_array($request->favourite_id, $favourites)) {
                $favourites[] = $request->favourite_id;
            }
            $message = "Favourite added successfully";
        } else {
            if (($key = array_search($request->favourite_id, $favourites)) !== false) {
                unset($favourites[$key]);
                $favourites = array_values($favourites); // Re-index
                $message = "Favourite removed successfully";
            }else {
                $message = "Favourite ID not found in favourites";
            }
        }

        // Save updated favourites
        $customer->pbc_fav = $favourites;
        $customer->save();

        return response()->json([
            'message' => $message,
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/customer/favourites",
     *     summary="Get customer's favourites",
     *     description="Retrieves the list of vendor IDs that the logged-in customer has marked as favourites.",
     *     operationId="getCustomerFavourites",
     *     tags={"Customer"},
     *     security={{"bearerAuth":{}}},
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Favourites retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Favourites retrieved successfully"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Customer not found")
     *         )
     *     )
     * )
    */
    public function getCustomerFavourites(){
        $user = auth()->user();
        $customer = customer::where('pbc_user_id', $user->pbu_id)->first();

        if(!$customer){
            return response()->json([
                'message' => 'Customer not found',
            ], 404);
        }

        $favourites = $customer->pbc_fav ?? [];

        // Fetch vendor details for the favourites
        $vendors = vendors::whereIn('pbv_id', $favourites)
                    ->where('pbv_status', 2)
                    ->get();

        Log::info('getCustomerFavourites Response:', ['Response' => $vendors]);
        return response()->json([
            'message' => 'Favourites retrieved successfully',
            'data' => $vendors
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/customer/bookings",
     *     summary="Get bookings by the authenticated customer",
     *     description="Returns a list of bookings for the authenticated customer, including booking details.",
     *     operationId="getBookingsByCustomerID",
     *     tags={"Customer"},
     *     security={{"bearerAuth":{}}},
     * 
     *     @OA\Response(
     *         response=200,
     *         description="Bookings retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Bookings retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=101),
     *                     @OA\Property(property="booking_customer_id", type="integer", example=5),
     *                     @OA\Property(property="booking_date", type="string", format="date", example="2025-06-01"),
     *                     @OA\Property(property="status", type="string", example="confirmed"),
     *                     @OA\Property(
     *                         property="booking_details",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=201),
     *                             @OA\Property(property="service_id", type="integer", example=3),
     *                             @OA\Property(property="employee_id", type="integer", example=12),
     *                             @OA\Property(property="start_time", type="string", format="date-time", example="2025-06-01T10:00:00Z"),
     *                             @OA\Property(property="end_time", type="string", format="date-time", example="2025-06-01T10:30:00Z")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     * 
     *     @OA\Response(
     *         response=404,
     *         description="Customer not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Customer not found")
     *         )
     *     )
     * )
 */

    public function getBookingsByCustomerID(){
        $user = auth()->user();
        $customer = customer::where('pbc_user_id', $user->pbu_id)->first();
        
        if(!$customer){
            return response()->json([
                'message' => 'Customer not found',
            ], 404);
        }

        $bookings = $customer->bookings()->with(['vendors','bookingDetails.services'])->get();

        $bookingsWithTotal = $bookings->map(function ($booking) {
            $total = $booking->bookingDetails->sum('pbbd_total_amount');
            return [
                'booking_id' => $booking->pbb_id,
                'booking_ref_no' => $booking->pbb_ref_no,
                'vendor' => $booking->vendors->pbv_business_name ?? null,
                'booking_date' => $booking->pbb_booking_date,
                'booking_start_time' => $booking->pbb_booking_start_time,
                'booking_end_time' => $booking->pbb_booking_end_time,
                'total_amount' => $total,
                'status' => $booking->pbb_status,
                'services' => $booking->bookingDetails->map(function ($detail) {
                    return [
                        'service_name' => $detail->services->pbs_name ?? null,
                        'amount' => $detail->pbbd_total_amount,
                    ];
                }),
            ];
        });
        Log::info('bookingsWithTotal Response:', ['Response' => $bookingsWithTotal]);
        return response()->json([
            'message' => 'Bookings retrieved successfully',
            'data' => $bookingsWithTotal
        ], 200);
    }

    /**
         * @OA\Get(
         *     path="/api/customer",
         *     summary="Get logged-in customer details",
         *     description="Returns the customer record linked to the authenticated user",
         *     operationId="getCustomer",
         *     tags={"Customer"},
         *     security={{"bearerAuth":{}}},
         *     @OA\Response(
         *         response=200,
         *         description="Customer Details",
         *         @OA\JsonContent(
         *             type="object",
         *             @OA\Property(property="message", type="string", example="Customer Details"),
         *             @OA\Property(
         *                 property="data",
         *                 type="object",
         *                 @OA\Property(property="pbc_id", type="integer", example=1),
         *                 @OA\Property(property="pbc_user_id", type="integer", example=5),
         *                 @OA\Property(property="pbc_name", type="string", example="John Doe"),
         *                 @OA\Property(property="pbc_contact", type="string", example="0771234567"),
         *                 @OA\Property(property="created_at", type="string", example="2025-06-21T12:00:00.000000Z"),
         *                 @OA\Property(property="updated_at", type="string", example="2025-06-21T12:00:00.000000Z")
         *             )
         *         )
         *     ),
         *     @OA\Response(
         *         response=401,
         *         description="Unauthenticated"
         *     )
         * )
     */
    public function getCustomer(){
        $user = auth()->user();

        $customer = customer::where('pbc_user_id', $user->pbu_id)->first();
        Log::info('getCustomer Response:', ['Response' => $customer]);
        return response()->json([
            'message' => 'Customer Details',
            'data' => $customer
        ], 200);
    }

    /**
 * @OA\Get(
 *     path="/api/vendors/{vendor_id}",
 *     summary="Get vendor details by ID",
 *     description="Retrieves detailed information about a specific vendor including availability, documents, and favorite status",
 *     tags={"Customer"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="vendor_id",
 *         in="path",
 *         required=true,
 *         description="ID of the vendor to retrieve",
 *         @OA\Schema(
 *             type="integer",
 *             example=123
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="success", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=123),
 *                 @OA\Property(property="tenentid", type="integer", example=1),
 *                 @OA\Property(property="servicefor", type="string", example="salon"),
 *                 @OA\Property(property="vendortype", type="string", example="premium"),
 *                 @OA\Property(property="business_name", type="string", example="Luxury Salon"),
 *                 @OA\Property(property="brno", type="string", example="BR123456"),
 *                 @OA\Property(property="email", type="string", example="info@luxurysalon.com"),
 *                 @OA\Property(property="contact_no", type="string", example="+1234567890"),
 *                 @OA\Property(property="address", type="string", example="123 Main Street"),
 *                 @OA\Property(property="city", type="string", example="New York"),
 *                 @OA\Property(property="longatitude", type="string", example="-73.935242"),
 *                 @OA\Property(property="latitude", type="string", example="40.730610"),
 *                 @OA\Property(property="status", type="integer", example=1),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2023-10-25T12:00:00.000000Z"),
 *                 @OA\Property(property="display_name", type="string", example="Luxury Beauty Salon"),
 *                 @OA\Property(property="logo", type="string", format="uri", example="https://api.example.com/storage/logo.png"),
 *                 @OA\Property(property="service_at_time", type="integer", example=5),
 *                 @OA\Property(
 *                     property="availability",
 *                     type="array",
 *                     @OA\Items(
 *                         type="object",
 *                         @OA\Property(property="day", type="string", example="Monday"),
 *                         @OA\Property(property="start_time", type="string", example="09:00:00"),
 *                         @OA\Property(property="end_time", type="string", example="18:00:00"),
 *                         @OA\Property(property="is_open", type="boolean", example=true)
 *                     )
 *                 ),
 *                 @OA\Property(
 *                     property="images",
 *                     type="array",
 *                     @OA\Items(type="string", format="uri")
 *                 ),
 *                 @OA\Property(property="rating", type="number", format="float", example=4.5),
 *                 @OA\Property(property="isFav", type="boolean", example=true)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Vendor not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Vendor not found")
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
    public function getVendorByID($vendor_id)
    {
        $user = auth()->user();
        // $vendor_results = vendors::join('vendor_standard_availability', 'vendor_standard_availability.pbvsa_vendor_id', '=', 'vendor.pbv_id', 'left')
        //         ->join('cities', 'cities.pbc_cid', '=', 'vendor.pbv_city', 'left')
        //         ->join('vendor_documents', 'vendor_documents.pbvd_vendor_id', '=', 'vendor.pbv_id', 'left')
        //         // ->join('ratings', 'ratings.pbr_vendor_id', '=', 'vendor.pbv_id', 'left')
        //         ->select(
        //             'vendor.*',
        //             'vendor_standard_availability.*',
        //             'cities.*',
        //             'vendor_documents.*'
        //             // 'ratings.*',
        //             // DB::raw('AVG(pb_ratings.pbr_rating) as average_rating')
        //         )
        //         ->where([
        //             ['vendor.pbv_id', $vendor_id], ['vendor.pbv_status', 2]
        //         ])
        //         ->get(); 
        $vendor_results = vendors::with(['city', 'availability', 'vendorDocuments', 'ratings'])
            ->where('pbv_id', $vendor_id)
            ->where('pbv_status', 2)
            ->first();  
        //dd($vendor_results);
        if (!$vendor_results) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        // Process availability - $vendor_results is a single object, not a collection
        $availability = $vendor_results->availability->map(function ($item) {
            $dayLabel = $item->pbvsa_day;
            $formatTime = function ($t) {
                if ($t === null || $t === '') {
                    return null;
                }

                // If already a time string like "09:30" or "09:30:00", Carbon::parse will handle it.
                try {
                    return Carbon::parse($t)->format('H:i');
                } catch (\Exception $e) {
                    // Fallback: return original string (safer than throwing)
                    return (string)$t;
                }
            };

            $start = $formatTime($item->pbvsa_start_time);
            $end   = $formatTime($item->pbvsa_end_time);

            // If both exist show "HH:MM - HH:MM", else fallback
            $timeString = ($start && $end) ? $start . ' - ' . $end : ($start ?: ($end ?: null));

            return [
                'day' => $dayLabel,                 // e.g. "Monday" or numeric index — unchanged
                'start_time' => $start,            // e.g. "09:30"
                'end_time' => $end,                // e.g. "17:00"
                'is_open' => (bool) $item->pbvsa_is_open,
                'time' => $timeString,             // helper field if needed
            ];
            // return [
            //     'day' => $item->pbvsa_day,
            //     'start_time' => $item->pbvsa_start_time,
            //     'end_time' => $item->pbvsa_end_time,
            //     'is_open' => $item->pbvsa_is_open,
            // ];
        })->toArray();

        // Get logo from documents where required_document_id = 6
        $vendorDocuments = $vendor_results->vendorDocuments;
        $logoDocument = $vendorDocuments->firstWhere('pbvd_required_document_id', 6);
        $logoUrl = $logoDocument ? $logoDocument->pbvd_document_url : null;

        // Fallback to config logo if document not found
        if (!$logoUrl && $vendor_results->config) {
            $logoUrl = $vendor_results->config->pbvc_logo ?? null;
        }

        // ✅ Images (example: document types 7–10 are image types, adjust IDs as needed)
        $imageDocuments = $vendorDocuments->where('pbvd_required_document_id', 7);
        $images = $imageDocuments->pluck('pbvd_document_url')->toArray();

        // Add favorite flag
        $customer = customer::where('pbc_user_id', $user->pbu_id)->first();
        $favourites = $customer->pbc_fav ?? [];

        // Check if this vendor's ID is in favorites
        $isFav = in_array($vendor_results->pbv_id, $favourites);

        $final_vendors = [
            'id' => $vendor_results->pbv_id,
            'tenentid' => $vendor_results->pbv_tenentid,
            'servicefor' => $vendor_results->pbv_servicefor,
            'vendortype' => $vendor_results->pbv_vendortype,
            'business_name' => $vendor_results->pbv_business_name,
            'description' => $vendor_results->pbv_short_description,
            'brno' => $vendor_results->pbv_brno,
            'email' => $vendor_results->pbv_email,
            'contact_no' => $vendor_results->pbv_contactno,
            'address' => $vendor_results->pbv_address,
            'city' => $vendor_results->city->pbc_cityname ?? null, // Access through city relationship
            'longatitude' => $vendor_results->pbv_longatitude,
            'latitude' => $vendor_results->pbv_latitude,
            'status' => $vendor_results->pbv_status,
            'description' => $vendor_results->pbv_short_description,
            'created_at' => $vendor_results->created_at,
            'display_name' => $vendor_results->pbv_display_name,
            'logo' => $logoUrl, // Use the document URL or fallback
            'service_at_time' => $vendor_results->pbv_staff_count,
            'availability' => $this->groupAvailability($availability),
            'images' => !empty($images)
                        ? $images
                        : (is_string($vendor_results->pbv_images)
                            ? json_decode($vendor_results->pbv_images, true)
                            : (is_array($vendor_results->pbv_images)
                                ? $vendor_results->pbv_images
                                : [])),
            'rating' => $vendor_results->ratings->isNotEmpty() ? round($vendor_results->ratings->avg('pbr_rating'), 1) : null,
            'isFav' => $isFav
        ];
        Log::info('getVendorByID Response:', ['Response' => $final_vendors]);
        return response()->json([
            'success' => true,
            'data' => $final_vendors
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }

    function groupAvailability(array $availability) {
        $grouped = [];
        $tempGroup = null;

        foreach ($availability as $slot) {
            if ($slot['is_open'] != 1) continue;

            $key = $slot['start_time'] . '-' . $slot['end_time'];

            if (!isset($grouped[$key])) {
                $grouped[$key] = [];
            }

            $grouped[$key][] = $slot['day'];
        }

        $result = [];

        foreach ($grouped as $time => $days) {
            $dayGroups = [];
            $startDay = $days[0];
            $prevDay = $startDay;

            for ($i = 1; $i < count($days); $i++) {
                if ((strtotime($days[$i]) - strtotime($prevDay)) === 86400) {
                    $prevDay = $days[$i];
                } else {
                    $dayGroups[] = $startDay === $prevDay ? $startDay : "$startDay - $prevDay";
                    $startDay = $days[$i];
                    $prevDay = $days[$i];
                }
            }
            $dayGroups[] = $startDay === $prevDay ? $startDay : "$startDay - $prevDay";

            foreach ($dayGroups as $group) {
                [$start, $end] = explode('-', $time);
                $result[] = [
                    'days' => $group,
                    'time' => "$start to $end"
                ];
            }
        }

        return $result;
    }
}