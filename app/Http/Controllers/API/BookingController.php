<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\customer;
use App\Models\vendors;
use App\Models\booking;
use App\Models\bookingDetail;
use App\Models\vendorStandardAvailability;
use App\Models\vendorSpecialCloses;
use App\Models\services;
use Validator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    /**
     * @OA\Get(
        *     path="/api/getBookingSlots",
        *     summary="Get available booking slots for a vendor",
        *     tags={"Booking"},
        *     security={{"bearerAuth":{}}},
        *     @OA\Parameter(
        *         name="vendor_id",
        *         in="query",
        *         required=true,
        *         description="ID of the vendor",
        *         @OA\Schema(type="integer", example=1)
        *     ),
        *     @OA\Parameter(
        *         name="booking_date",
        *         in="query",
        *         required=true,
        *         description="Date to check availability (format: YYYY-MM-DD)",
        *         @OA\Schema(type="string", format="date", example="2025-05-14")
        *     ),
        *     @OA\Parameter(
        *         name="service_total_duration",
        *         in="query",
        *         required=true,
        *         description="Duration of selected service in minutes",
        *         @OA\Schema(type="integer", example=120)
        *     ),
        *     @OA\Response(
        *         response=200,
        *         description="Successful response with available time slots",
        *         @OA\JsonContent(
        *             type="object",
        *             @OA\Property(property="status", type="boolean", example=true),
        *             @OA\Property(property="message", type="string", example="Available slots"),
        *             @OA\Property(
        *                 property="data",
        *                 type="array",
        *                 @OA\Items(
        *                     type="object",
        *                     @OA\Property(property="start", type="string", example="08:00:00"),
        *                     @OA\Property(property="end", type="string", example="10:00:00")
        *                 )
        *             )
        *         )
        *     ),
        *     @OA\Response(
        *         response=422,
        *         description="Validation Error",
        *         @OA\JsonContent(
        *             @OA\Property(property="message", type="string", example="The given data was invalid."),
        *             @OA\Property(
        *                 property="errors",
        *                 type="object"
        *             )
        *         )
        *     ),
        *     @OA\Response(
        *         response=401,
        *         description="Unauthorized"
        *     )
        * )
    */
    // public function getBookingSlots(Request $request){
    //     $user = auth()->user();  
        
    //     $availableSlots = [];
    //     $finalSlots = [];
        
    //     $vendorId = $request->query('vendor_id');
    //     $bookingDate = $request->query('booking_date');
    //     $serviceDuration = $request->query('service_total_duration');

    //     // Get vendor's standard availability for that day
    //     $availability = vendorStandardAvailability::where('pbvsa_vendor_id', $vendorId)
    //         ->where('pbvsa_day', date('l', strtotime($bookingDate)))
    //         ->first();
            
    //     if (!$availability || !$availability->pbvsa_is_open) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Vendor is closed on selected date',
    //             'data' => []
    //         ], 200);
    //     }

    //     $openTime = Carbon::createFromTimeString($availability->pbvsa_start_time);
    //     $closeTime = Carbon::createFromTimeString($availability->pbvsa_end_time);

    //     $existingBookings = booking::where('pbb_vendor_id', $vendorId)
    //         ->where('pbb_booking_date', $bookingDate)
    //         ->where('pbb_status', '=', 1)
    //         ->orderBy('pbb_booking_start_time')
    //         ->get();

    //     $availableSlots = [];
    //     $finalSlots = [];
    //     $currentStart = clone $openTime;

    //     if ($existingBookings->isEmpty()) {
    //         while ($currentStart->copy()->addMinutes($serviceDuration)->lte($closeTime)) {
    //             $finalSlots[] = [
    //                 'start' => $currentStart->format('H:i:s'),
    //                 'end' => (clone $currentStart)->addMinutes($serviceDuration)->format('H:i:s'),
    //             ];
    //             $currentStart->addMinutes($serviceDuration);
    //         }
    //     } else {
    //         foreach ($existingBookings as $booking) {
    //             $bookingStart = Carbon::createFromTimeString($booking->pbb_booking_start_time);
    //             $bookingEnd = Carbon::createFromTimeString($booking->pbb_booking_end_time);

    //             if ($currentStart->lt($bookingStart)) {
    //                 $availableSlots[] = [
    //                     'start' => clone $currentStart,
    //                     'end' => clone $bookingStart,
    //                 ];
    //             }

    //             if ($currentStart->lt($bookingEnd)) {
    //                 $currentStart = clone $bookingEnd;
    //             }
    //         }

    //         if ($currentStart->lt($closeTime)) {
    //             $availableSlots[] = [
    //                 'start' => clone $currentStart,
    //                 'end' => clone $closeTime,
    //             ];
    //         }

    //         // Slice each available gap into proper service duration slots
    //         foreach ($availableSlots as $slot) {
    //             $startTime = $slot['start'];
    //             $endTime = $slot['end'];

    //             while ($startTime->copy()->addMinutes($serviceDuration)->lte($endTime)) {
    //                 $finalSlots[] = [
    //                     'start' => $startTime->format('H:i:s'),
    //                     'end' => (clone $startTime)->addMinutes($serviceDuration)->format('H:i:s'),
    //                 ];
    //                 $startTime->addMinutes($serviceDuration);
    //             }
    //         }
    //     }

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Available slots',
    //         'data' => $finalSlots,
    //     ], 200);
    // }
    public function getBookingSlots(Request $request){
        $user = auth()->user();  
        
        $availableSlots = [];
        $finalSlots = [];
        
        $vendorId = $request->query('vendor_id');
        $bookingDate = $request->query('booking_date');
        // $serviceDuration = $request->query('service_total_duration');
        $service_ids = explode(',', $request->query('services'));

        Log::info('Vendor:', ['data' => $vendorId]);
        Log::info('Booking Date:', ['data' => $bookingDate]);
        Log::info('Services:', ['data' => $service_ids]);

        $serviceDuration = 0;
        foreach($service_ids as $service_id){
            $service_detail = services::where('pbs_id', $service_id)->first();
            if($service_detail){
                $serviceDuration += $service_detail->pbs_duration;
            }
        }

        $today = Carbon::today()->format('Y-m-d');
        $now = Carbon::now();

        if (Carbon::parse($bookingDate)->lt($today)) {
            Log::info('Booking date cannot be in the past');
            return response()->json([
                'status' => false,
                'message' => 'Booking date cannot be in the past',
                'data' => [],
            ], 400);
        }

        // Get vendor's standard availability
        $availability = vendorStandardAvailability::where('pbvsa_vendor_id', $vendorId)
            ->where('pbvsa_day', date('l', strtotime($bookingDate)))
            ->first();
            
        if (!$availability || !$availability->pbvsa_is_open) {
            Log::info('Vendor is closed on selected date');
            return response()->json([
                'status' => false,
                'message' => 'Vendor is closed on selected date',
                'data' => []
            ], 200);
        }

        $openTime = Carbon::createFromTimeString($availability->pbvsa_start_time)->setDateFrom(Carbon::parse($bookingDate));
        $closeTime = Carbon::createFromTimeString($availability->pbvsa_end_time)->setDateFrom(Carbon::parse($bookingDate));

        // ✅ Adjust open time if booking date is today
        if ($bookingDate === $today) {
            $bufferMinutes = 10;
            $adjustedNow = $now->copy()->addMinutes($bufferMinutes);
            if ($adjustedNow->gt($openTime)) {
                $openTime = $adjustedNow;
            }
        }

        if ($openTime->copy()->addMinutes($serviceDuration)->gt($closeTime)) {
            Log::info('No available time slot: The remaining time before closing is shorter than the service duration.');
            return response()->json([
                'status' => false,
                'message' => 'No available time slot: The remaining time before closing is shorter than the service duration.',
                'data' => []
            ], 200);
        }

        $existingBookings = booking::where('pbb_vendor_id', $vendorId)
            ->where('pbb_booking_date', $bookingDate)
            ->where('pbb_status', '=', 1)
            ->orderBy('pbb_booking_start_time')
            ->get();

        $currentStart = clone $openTime;

        if ($existingBookings->isEmpty()) {
            while ($currentStart->copy()->addMinutes($serviceDuration)->lte($closeTime)) {
                $finalSlots[] = [
                    'start' => $currentStart->format('H:i:s'),
                    'end' => (clone $currentStart)->addMinutes($serviceDuration)->format('H:i:s'),
                ];
                $currentStart->addMinutes($serviceDuration);
            }
        } else {
            foreach ($existingBookings as $booking) {
                $bookingStart = Carbon::createFromTimeString($booking->pbb_booking_start_time)->setDateFrom(Carbon::parse($bookingDate));
                $bookingEnd = Carbon::createFromTimeString($booking->pbb_booking_end_time)->setDateFrom(Carbon::parse($bookingDate));

                if ($currentStart->lt($bookingStart)) {
                    $availableSlots[] = [
                        'start' => clone $currentStart,
                        'end' => clone $bookingStart,
                    ];
                }

                if ($currentStart->lt($bookingEnd)) {
                    $currentStart = clone $bookingEnd;
                }
            }

            if ($currentStart->lt($closeTime)) {
                $availableSlots[] = [
                    'start' => clone $currentStart,
                    'end' => clone $closeTime,
                ];
            }

            foreach ($availableSlots as $slot) {
                $startTime = $slot['start'];
                $endTime = $slot['end'];

                while ($startTime->copy()->addMinutes($serviceDuration)->lte($endTime)) {
                    $finalSlots[] = [
                        'start' => $startTime->format('H:i:s'),
                        'end' => (clone $startTime)->addMinutes($serviceDuration)->format('H:i:s'),
                    ];
                    $startTime->addMinutes($serviceDuration);
                }
            }
        }
        Log::info('Available time slots:', $finalSlots);
        return response()->json([
            'status' => true,
            'message' => 'Available slots',
            'data' => $finalSlots,
        ], 200);
    }

/**
 * @OA\Post(
 *     path="/addOnlineBooking",
 *     summary="Add Online Booking",
 *     description="Allows a customer to create a new online booking with one or more services.",
 *     operationId="addOnlineBooking",
 *     tags={"Booking"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="vendor_id", type="integer", example=9),
 *             @OA\Property(property="promocode_id", type="integer", nullable=true, example=null),
 *             @OA\Property(
 *                 property="booking_details",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="service_id", type="integer", example=1),
 *                     @OA\Property(property="booking_date", type="string", format="date", example="2025-08-02"),
 *                     @OA\Property(property="booking_duration", type="string", format="time", example="01:30:00"),
 *                     @OA\Property(property="booking_start_time", type="string", format="time", example="10:00:00"),
 *                     @OA\Property(property="booking_end_time", type="string", format="time", example="11:30:00"),
 *                     @OA\Property(property="service_location", type="string", example="Home"),
 *                     @OA\Property(property="booking_for_someone", type="integer", enum={0,1}, example=0),
 *                     @OA\Property(property="someone_name", type="string", example="John Doe"),
 *                     @OA\Property(property="someone_contact_no", type="string", example="0771234567"),
 *                     @OA\Property(property="age", type="integer", example=25),
 *                     @OA\Property(property="gender", type="string", example="Male"),
 *                     @OA\Property(property="address", type="string", example="123 Street Name"),
 *                     @OA\Property(property="remarks", type="string", example="No special requests.")
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Booking created successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Booking added successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="booking_id", type="integer", example=101),
 *                 @OA\Property(property="booking_ref_no", type="string", example="BOONOLKIINNEG_64fcd541e0f59"),
 *                 @OA\Property(property="vendor_id", type="integer", example=9),
 *                 @OA\Property(property="total_amount", type="number", format="float", example=2000.00)
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Vendor or Customer not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Vendor not found")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=422,
 *         description="Validation error"
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Server error"
 *     )
 * )
 */


    public function addOnlineBooking(Request $request){
        $user = auth()->user();

        $request->validate(
            [
                'vendor_id' => 'required',
                'promocode_id' => 'nullable',
                'booking_details' => 'required',
                'booking_date' => 'required',
                'booking_duration' => 'required|date_format:H:i:s',
                'booking_start_time' => 'required|date_format:H:i:s',
                'booking_end_time' => 'required|date_format:H:i:s',
                'service_location' => 'required',
                // 'services.*.service_id' => 'required|integer',
            ],
            [
                'vendor_id.required' => 'Vendor ID is required',
                'promocode_id.required' => 'Promo code ID is required',
                'booking_details.required' => 'Booking details are required',
                'booking_date.required' => 'Booking date is required',
                'booking_duration.required' => 'Booking duration is required',
                'booking_start_time.required' => 'Booking start time is required',
                'booking_end_time.required' => 'Booking end time is required',
                'service_location.required' => 'Service location is required',
                // 'services.*.service_id.required' => 'Service ID is required',
                // 'services.*.service_id.integer' => 'Service ID must be an integer',
            ]
        );

        $vendor = vendors::find($request->vendor_id);
        if (!$vendor) {
            return response()->json([
                'status' => false,
                'message' => 'Vendor not found',
            ], 404);
        }
        $customer = customer::where('pbc_user_id', $user->pbu_id)->first();
        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        $booking_details_generated = [];
        if($request->booking_for_someone == 1){
            $booking_details_generated = [
                'name' => $request->someone_name,
                'contact_no' => $request->someone_contact_no,
                'age' => $request->age,
                'gender' => $request->gender,
                'address' => $request->address,
            ];
        }else{
            $booking_details_generated = [
                'remarks' => $request->remarks,
            ];
        }

        $addbooking = Booking::create([
            'pbb_vendor_id' => $request->vendor_id,
            'pbb_customer_id' => $customer->pbc_id,
            'pbb_promo_id' => $request->promocode_id,
            'pbb_booking_details' => json_encode($booking_details_generated),
            'pbb_booking_date' => $request->booking_date,
            'pbb_booking_duration' => $request->booking_duration,
            'pbb_booking_start_time' => $request->booking_start_time,
            'pbb_booking_end_time' => $request->booking_end_time,
            'pbb_ref_no' => uniqid('BOONOLKIINNEG_'),
            'pbb_type' => 'Online',
            'pbb_service_location' => $request->service_location,
            'pbb_contact_no' => ($request->booking_for_someone == 1) ? $request->someone_contact_no : $customer->customer_contact_no,
            'pbb_status' => 1
        ]);

        if($addbooking){
            $booking_details = $request->booking_details;
            $total_amount = 0;
            foreach($booking_details as $key => $value){
                $service = services::where('pbs_id', $value['service_id'])->first();
                if($service){
                    $total_amount += $service->pbs_price;
                    bookingDetail::create([
                        'pbbd_booking_id' => $addbooking->pbb_id,
                        'pbbd_service_id' => $value['service_id'],
                        'pbbd_employee_id' => null,
                        'pbbd_promo_id' => null,
                        'pbbd_amount' => $service->pbs_price,
                        'pbbd_discount' => 0,
                        'pbbd_total_amount' => $service->pbs_price,
                        'pbb_status' => 1
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Booking added successfully',
                'data' => [
                    'booking_id' => $addbooking->pbb_id,
                    'booking_ref_no' => $addbooking->pbb_ref_no,
                    'vendor_id' => $addbooking->pbb_vendor_id,
                    'total_amount' => $total_amount
                ]
            ], 200);
            $status_code = 200;
            $message = "Booking Added Successfully";
        }else{
            $status_code = 500;
            $message = "Unable to add the booking now. Please try again later";
        }

        return response()->json([
            'message' => $message,
        ], $status_code);
    }

    /**
     * @OA\Post(
     *     path="/api/addRating",
     *     summary="Add rating and review for a booking",
     *     tags={"Booking"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"booking_id", "rating"},
     *             @OA\Property(property="booking_id", type="integer", example=101),
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=4),
     *             @OA\Property(property="review", type="string", maxLength=500, example="Great service!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Rating added successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Rating added successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Unable to add rating"
     *     )
     * )
    */
    public function addRating(Request $request){
        $user = auth()->user();

        $request->validate(
            [
                'booking_id' => 'required|integer',
                'rating' => 'required|integer|min:1|max:5',
                'review' => 'nullable|string|max:500',
            ],
            [
                'booking_id.required' => 'Booking ID is required',
                'booking_id.integer' => 'Booking ID must be an integer',
                'rating.required' => 'Rating is required',
                'rating.integer' => 'Rating must be an integer',
                'rating.min' => 'Rating must be at least 1',
                'rating.max' => 'Rating cannot exceed 5',
                'review.max' => 'Review cannot exceed 500 characters',
            ]
        );

        $booking = booking::find($request->booking_id);
        if (!$booking) {
            return response()->json([
                'status' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        // Check if the user is authorized to rate this booking
        // if ($booking->pbb_customer_id !== $user->pbu_id) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Unauthorized to rate this booking',
        //     ], 403);
        // }

        $addRating = $booking->ratings()->create([
            'pbr_vendor_id' => $booking->pbb_vendor_id,
            'pbr_booking_id' => $request->booking_id,
            'pbr_customer_id' => $booking->pbb_customer_id,
            'pbr_rating' => $request->rating,
            'pbr_comments' => $request->review,
            'pbr_status' => 1,
        ]);

        if (!$addRating) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to add rating. Please try again later',
            ], 500);
        }else {
            // // Update the booking status to completed if not already done
            // if ($booking->pbb_status !== 2) { // Assuming 2 is the status for completed bookings
            //     $booking->update(['pbb_status' => 2]);
            // }
        }


        return response()->json([
            'status' => true,
            'message' => 'Rating added successfully',
        ], 200);
    }

    /**
 * @OA\Post(
 *     path="/addManualBooking",
 *     summary="Add Manual Booking",
 *     tags={"Booking"},
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"vendor_id", "booking_date", "booking_duration", "booking_start_time", "booking_end_time", "service_location", "services"},
 *             @OA\Property(property="vendor_id", type="integer", example=9),
 *             @OA\Property(property="booking_date", type="string", format="date", example="2025-08-10"),
 *             @OA\Property(property="booking_duration", type="string", format="time", example="01:00:00"),
 *             @OA\Property(property="booking_start_time", type="string", format="time", example="10:00:00"),
 *             @OA\Property(property="booking_end_time", type="string", format="time", example="11:00:00"),
 *             @OA\Property(property="service_location", type="string", example="Home"),
 *             @OA\Property(property="promocode_id", type="integer", example=2),
 *             @OA\Property(property="booking_for_someone", type="boolean", example=true),
 *             @OA\Property(property="someone_name", type="string", example="Jane Doe"),
 *             @OA\Property(property="someone_contact_no", type="string", example="0771234567"),
 *             @OA\Property(property="age", type="integer", example=28),
 *             @OA\Property(property="gender", type="string", example="Female"),
 *             @OA\Property(property="address", type="string", example="123 Street, City"),
 *             @OA\Property(property="remarks", type="string", example="Please be on time"),
 *             @OA\Property(
 *                 property="services",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="service_id", type="integer", example=3)
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Booking added successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Booking added successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="booking_id", type="integer", example=1),
 *                 @OA\Property(property="booking_ref_no", type="string", example="BMOAONKUIANLG_65bf2346dfab2"),
 *                 @OA\Property(property="vendor_id", type="integer", example=9),
 *                 @OA\Property(property="total_amount", type="number", format="float", example=1500.00)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Vendor not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Vendor not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Unable to add the booking now. Please try again later",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unable to add the booking now. Please try again later")
 *         )
 *     )
 * )
 */

    public function addManualBooking(Request $request){
        $user = auth()->user();
        
        $request->validate(
            [
                'booking_date' => 'required',
                'booking_duration' => 'required|date_format:H:i:s',
                'booking_start_time' => 'required|date_format:H:i:s',
                'booking_end_time' => 'required|date_format:H:i:s',
                'service_location' => 'required',
                'services.*.service_id' => 'required|integer',
            ],
            [
                'booking_date.required' => 'Booking date is required',
                'booking_duration.required' => 'Booking duration is required',
                'booking_start_time.required' => 'Booking start time is required',
                'booking_end_time.required' => 'Booking end time is required',
                'service_location.required' => 'Service location is required',
                'services.*.service_id.required' => 'Service ID is required',
                'services.*.service_id.integer' => 'Service ID must be an integer',
            ]
        );

        $vendor = vendors::find($request->vendor_id);
        if (!$vendor) {
            return response()->json([
                'status' => false,
                'message' => 'Vendor not found',
            ], 404);
        }

        $booking_details_generated = [];
        $booking_details_generated = [
            'name' => $request->someone_name,
            'contact_no' => $request->someone_contact_no,
            'age' => $request->age,
            'gender' => $request->gender,
            'address' => $request->address,
            'remarks' => $request->remarks,
        ];

        $addbooking = Booking::create([
            'pbb_vendor_id' => $request->vendor_id,
            'pbb_customer_id' => null,
            'pbb_promo_id' => $request->promocode_id,
            'pbb_booking_details' => json_encode($booking_details_generated),
            'pbb_booking_date' => $request->booking_date,
            'pbb_booking_duration' => $request->booking_duration,
            'pbb_booking_start_time' => $request->booking_start_time,
            'pbb_booking_end_time' => $request->booking_end_time,
            'pbb_ref_no' => uniqid('BMOAONKUIANLG_'),
            'pbb_type' => 'Manual',
            'pbb_service_location' => $request->service_location,
            'pbb_contact_no' => ($request->booking_for_someone == 1) ? $request->someone_contact_no : $customer->customer_contact_no,
            'pbb_status' => 1
        ]);

        if($addbooking){
            $booking_details = $request->services;
            $total_amount = 0;
            foreach($booking_details as $key => $value){
                $service = services::where('pbs_id', $value['service_id'])->first();
                if($service){
                    $total_amount += $service->pbs_price;
                    bookingDetail::create([
                        'pbbd_booking_id' => $addbooking->pbb_id,
                        'pbbd_service_id' => $value['service_id'],
                        'pbbd_employee_id' => null,
                        'pbbd_promo_id' => null,
                        'pbbd_amount' => $service->pbs_price,
                        'pbbd_discount' => 0,
                        'pbbd_total_amount' => $service->pbs_price,
                        'pbb_status' => 1
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Booking added successfully',
                'data' => [
                    'booking_id' => $addbooking->pbb_id,
                    'booking_ref_no' => $addbooking->pbb_ref_no,
                    'vendor_id' => $addbooking->pbb_vendor_id,
                    'total_amount' => $total_amount
                ]
            ], 200);
            $status_code = 200;
            $message = "Booking Added Successfully";
        }else{
            $status_code = 500;
            $message = "Unable to add the booking now. Please try again later";
        }

        return response()->json([
            'message' => $message,
        ], $status_code);
    }

    /**
     * @OA\Get(
     *     path="/getBookings",
     *     summary="Get all bookings for the authenticated vendor",
     *     description="Retrieves bookings including customer and limited service details.",
     *     tags={"Booking"},
     *     security={{"bearerAuth":{}}},
     *     
     *     @OA\Response(
     *         response=200,
     *         description="Bookings retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Bookings retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="pbb_id", type="integer", example=1),
     *                     @OA\Property(property="pbb_vendor_id", type="integer", example=9),
     *                     @OA\Property(property="pbb_booking_date", type="string", format="date", example="2025-08-02"),
     *                     @OA\Property(property="pbb_booking_duration", type="string", format="time", example="01:30:00"),
     *                     @OA\Property(property="pbb_booking_start_time", type="string", format="time", example="10:00:00"),
     *                     @OA\Property(property="pbb_booking_end_time", type="string", format="time", example="11:30:00"),
     *                     @OA\Property(property="pbb_service_location", type="string", example="In-Salon"),
     *                     @OA\Property(
     *                         property="customer",
     *                         type="object",
     *                         @OA\Property(property="pbc_id", type="integer", example=12),
     *                         @OA\Property(property="pbc_user_id", type="integer", example=21),
     *                         @OA\Property(property="pbc_name", type="string", example="John Doe"),
     *                         @OA\Property(property="customer_contact_no", type="string", example="9876543210")
     *                     ),
     *                     @OA\Property(
     *                         property="booking_details",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="pbbd_id", type="integer", example=100),
     *                             @OA\Property(
     *                                 property="services",
     *                                 type="object",
     *                                 @OA\Property(property="pbs_id", type="integer", example=55),
     *                                 @OA\Property(property="pbs_service_type", type="integer", example=1),
     *                                 @OA\Property(property="pbs_service_for", type="integer", example=2),
     *                                 @OA\Property(property="pbs_name", type="string", example="Haircut"),
     *                                 @OA\Property(property="pbs_price", type="number", format="float", example=1200),
     *                                 @OA\Property(property="pbs_duration", type="string", format="time", example="00:30:00")
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=404,
     *         description="No bookings found or vendor not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="No bookings found")
     *         )
     *     ),
     *     
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
 */

    public function getBookings()
    {
        $user = auth()->user();
        $vendor = vendors::where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $bookings = booking::where('pbb_vendor_id', $vendor->pbv_id)
            // ->with(['bookingDetails.services' => function ($q) {
            //     $q->select('pbs_price', 'pbs_duration');
            // }])
            ->orderBy('pbb_booking_date', 'desc')
            ->get();

        if ($bookings->isEmpty()) {
            return response()->json(['message' => 'No bookings found'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Bookings retrieved successfully',
            'data' => $bookings
        ], 200);
    }

    /**
 * @OA\Get(
 *     path="/api/bookings/{id}",
 *     summary="Get booking details by ID",
 *     description="Retrieves detailed information about a specific booking including customer details and services",
 *     tags={"Bookings"},
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the booking to retrieve",
 *         @OA\Schema(
 *             type="integer",
 *             example=123
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Booking details retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Booking Details retrieved successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="pbb_id", type="integer", example=123),
 *                     @OA\Property(property="pbb_booking_date", type="string", format="date-time", example="2023-10-25T12:00:00.000000Z"),
 *                     @OA\Property(property="pbb_status", type="string", example="confirmed"),
 *                     @OA\Property(property="pbb_total_amount", type="number", format="float", example=150.50),
 *                     @OA\Property(
 *                         property="customer",
 *                         type="object",
 *                         @OA\Property(property="pbc_id", type="integer", example=456),
 *                         @OA\Property(property="pbc_name", type="string", example="John Doe"),
 *                         @OA\Property(property="pbc_email", type="string", example="john@example.com"),
 *                         @OA\Property(property="pbc_phone", type="string", example="+1234567890")
 *                     ),
 *                     @OA\Property(
 *                         property="booking_details",
 *                         type="array",
 *                         @OA\Items(
 *                             type="object",
 *                             @OA\Property(property="pbbd_id", type="integer", example=789),
 *                             @OA\Property(property="pbbd_service_id", type="integer", example=101),
 *                             @OA\Property(property="pbbd_price", type="number", format="float", example=75.25),
 *                             @OA\Property(
 *                                 property="services",
 *                                 type="object",
 *                                 @OA\Property(property="pbs_id", type="integer", example=101),
 *                                 @OA\Property(property="pbs_service_type", type="string", example="hair"),
 *                                 @OA\Property(property="pbs_service_for", type="string", example="women"),
 *                                 @OA\Property(property="pbs_name", type="string", example="Hair Cut & Styling"),
 *                                 @OA\Property(property="pbs_price", type="number", format="float", example=75.25),
 *                                 @OA\Property(property="pbs_duration", type="integer", example=60)
 *                             )
 *                         )
 *                     ),
 *                     @OA\Property(property="pbb_created_at", type="string", format="date-time", example="2023-10-25T12:00:00.000000Z"),
 *                     @OA\Property(property="pbb_updated_at", type="string", format="date-time", example="2023-10-25T12:00:00.000000Z")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Booking not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="No bookings found")
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
    public function getBookingDetailsById($id)
    {
        $bookings = booking::where('pbb_id', $id)
            ->with(['customer', 'bookingDetails.services' => function ($q) {
                $q->select('pbs_id', 'pbs_service_type', 'pbs_service_for', 'pbs_name', 'pbs_price', 'pbs_duration');
            }])
            ->orderBy('pbb_booking_date', 'desc')
            ->get();

        if ($bookings->isEmpty()) {
            return response()->json(['message' => 'No bookings found'], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Booking Details retrieved successfully',
            'data' => $bookings
        ], 200);
    }
}