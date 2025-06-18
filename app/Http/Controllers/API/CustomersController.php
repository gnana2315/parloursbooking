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

class CustomersController extends Controller
{
    public function register($id, Request $request){
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

        return response()->json([
            'message' => $message,
            'user' => $user
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
        $user = auth()->user();
        $customer = customer::where('pbc_user_id', auth()->id())->first();
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
                    ->select('pbv_id', 'pbv_servicefor', 'pbv_business_name', 'pbv_address', 'pbv_city')
                    ->get();

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
        //dd($bookings);
        return response()->json([
            'message' => 'Bookings retrieved successfully',
            'data' => $bookings
        ], 200);
    }
}