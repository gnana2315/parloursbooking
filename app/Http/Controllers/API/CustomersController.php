<?php

namespace App\Http\Controllers\API;

//use App\ApiResponseTrait;
use App\Models\User;
use App\Models\customer;
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

    // public function getBookingsByCustomerID(){
    //     $user = auth()->user();
    //     $customer = customer::where('pbc_user_id', $user->id)->first();
    //     if(!$customer){
    //         return response()->json([
    //             'message' => 'Customer not found',
    //         ], 404);
    //     }
    //     $bookings = $customer->bookings()->with('bookingDetails')->get();
    //     //dd($bookings);
    //     return response()->json([
    //         'message' => 'Bookings retrieved successfully',
    //         'bookings' => $bookings
    //     ], 200);
    // }

    /**
         * @OA\Post(
         *     path="/api/customer/favourite",
         *     summary="Add a vendor to customer's favourites",
         *     description="Adds a vendor ID to the logged-in customer's favourite_vendors column.",
         *     operationId="addCustomerFavourite",
         *     tags={"Customer"},
         *     security={{"bearerAuth":{}}},
         *     
         *     @OA\RequestBody(
         *         required=true,
         *         @OA\JsonContent(
         *             required={"favourite_id"},
         *             @OA\Property(property="favourite_id", type="integer", example=123)
         *         )
         *     ),
         *     
         *     @OA\Response(
         *         response=200,
         *         description="Favourite added successfully",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Favourite added successfully")
         *         )
         *     ),
         *     @OA\Response(
         *         response=404,
         *         description="Customer not found",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="Customer not found")
         *         )
         *     ),
         *     @OA\Response(
         *         response=422,
         *         description="Validation Error",
         *         @OA\JsonContent(
         *             @OA\Property(property="message", type="string", example="The given data was invalid."),
         *             @OA\Property(
         *                 property="errors",
         *                 type="object",
         *                 @OA\Property(
         *                     property="favourite_id",
         *                     type="array",
         *                     @OA\Items(type="string", example="The favourite id field is required.")
         *                 )
         *             )
         *         )
         *     )
         * )
     */
    public function addCustomerFavourite(Request $request){
        $user = auth()->user();
        $customer = customer::where('pbc_user_id', $user->pbu_id)->first();

        if(!$customer){
            return response()->json([
                'message' => 'Customer not found',
            ], 404);
        }        

        // Load current favourites or start fresh
        $favourites = $customer->favourite_vendors ?? [];

        // Avoid duplicates
        if (!in_array($request->favourite_id, $favourites)) {
            $favourites[] = $request->favourite_id;
            $customer->favourite_vendors = $favourites;
            $customer->save();
        }

        return response()->json([
            'message' => 'Favourite added successfully',
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/customer/favourite/remove",
     *     summary="Remove a vendor from customer's favourites",
     *     description="Removes a vendor ID from the logged-in customer's favourite_vendors column.",
     *     operationId="removeCustomerFavourite",
     *     tags={"Customer"},
     *     security={{"bearerAuth":{}}},
     *     
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"favourite_id"},
     *             @OA\Property(property="favourite_id", type="integer", example=123)
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Favourite removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Favourite removed successfully")
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
    public function removeCustomerFavourite(Request $request){
        $user = auth()->user();
        $customer = customer::where('pbc_user_id', $user->pbu_id)->first();

        if(!$customer){
            return response()->json([
                'message' => 'Customer not found',
            ], 404);
        }        

        // Load current favourites or start fresh
        $favourites = $customer->favourite_vendors ?? [];

        // Remove the favourite if it exists
        if (($key = array_search($request->favourite_id, $favourites)) !== false) {
            unset($favourites[$key]);
            $customer->favourite_vendors = array_values($favourites); // Re-index the array
            $customer->save();
        }

        return response()->json([
            'message' => 'Favourite removed successfully',
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

        $favourites = $customer->favourite_vendors ?? [];

        return response()->json([
            'message' => 'Favourites retrieved successfully',
            'data' => $favourites
        ], 200);
    }
}