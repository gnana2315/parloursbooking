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
use App\Models\paymentTransection;
use App\Models\vendorPayouts;
use App\Models\vendorPayoutItems;
use App\Models\vendorPayoutHistory;
use App\Models\notification;

use Validator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\deviceToken;
use App\Services\FirebaseService;
use App\Services\OneSignalService;

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
 *     path="/api/addOnlineBooking",
 *     tags={"Bookings"},
 *     summary="Add Online Booking",
 *     description="Create a new online booking for a vendor with services.",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={
 *                 "vendor_id","booking_details","booking_date",
 *                 "booking_start_time","booking_end_time","service_location"
 *             },
 *             @OA\Property(property="vendor_id", type="integer", example=12),
 *             @OA\Property(property="promocode_id", type="integer", nullable=true, example=null),
 *             @OA\Property(property="booking_details", type="string", example="Haircut and Facial"),
 *             @OA\Property(property="booking_date", type="string", format="date", example="2025-09-20"),
 *             @OA\Property(property="booking_start_time", type="string", format="time", example="10:00:00"),
 *             @OA\Property(property="booking_end_time", type="string", format="time", example="11:30:00"),
 *             @OA\Property(property="service_location", type="string", example="Salon Branch - Main Street"),
 *             @OA\Property(property="booking_for_someone", type="integer", example=1, description="1 if booking for someone else"),
 *             @OA\Property(property="someone_name", type="string", example="John Doe"),
 *             @OA\Property(property="someone_contact_no", type="string", example="9876543210"),
 *             @OA\Property(property="age", type="integer", example=28),
 *             @OA\Property(property="gender", type="string", example="Male"),
 *             @OA\Property(property="address", type="string", example="123, Elm Street, Colombo"),
 *             @OA\Property(property="remarks", type="string", example="Please prepare hair products in advance."),
 *             @OA\Property(
 *                 property="services",
 *                 type="array",
 *                 @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="service_id", type="integer", example=5)
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Booking added successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Booking added successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="booking_id", type="integer", example=101),
 *                 @OA\Property(property="booking_ref_no", type="string", example="BOONOLKIINNEG_650f3f5d9c9a1"),
 *                 @OA\Property(property="vendor_id", type="integer", example=12),
 *                 @OA\Property(property="total_amount", type="number", format="float", example=1500)
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Vendor or Customer not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Vendor not found")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Unable to add the booking",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Unable to add the booking now. Please try again later")
 *         )
 *     )
 * )
 */

    public function addOnlineBooking(Request $request, OneSignalService $oneSignalService){
        $user = auth()->user();

        $request->validate(
            [
                'vendor_id' => 'required',
                'booking_details.*.service_id' => 'required|integer',
                'booking_date' => 'required',
                'booking_start_time' => 'required|date_format:H:i:s',
                'booking_end_time' => 'required|date_format:H:i:s',
                'service_location' => 'required',
            ],
            [
                'vendor_id.required' => 'Vendor ID is required',
                'booking_details.*.service_id.required' => 'Service ID is required',
                'booking_details.*.service_id.integer' => 'Service ID must be an integer',
                'booking_date.required' => 'Booking date is required',
                'booking_start_time.required' => 'Booking start time is required',
                'booking_end_time.required' => 'Booking end time is required',
                'service_location.required' => 'Service location is required',
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

        $booking_details_generated = [
            'name' => $request->someone_name,
            'contact_no' => $request->someone_contact_no,
            'age' => $request->age,
            'gender' => $request->gender,
            'address' => $request->address,
            'remarks' => $request->remarks,
        ];

        $booking_details = $request->booking_details;
        $total_amount = 0.00;
        $total_duration = 0; // in minutes

        foreach ($booking_details as $value) {
            $service = services::where('pbs_id', $value['service_id'])->first();
            if ($service) {
                $price = floatval(str_replace(',', '', $service->pbs_price));
                $total_amount += $price;
                $total_duration += $service->pbs_duration; // ✅ add service duration (minutes)
            }
        }

        // ✅ Convert total duration to HH:MM:SS
        $hours = floor($total_duration / 60);
        $remainingMinutes = $total_duration % 60;
        $duration = sprintf('%02d:%02d:00', $hours, $remainingMinutes);

        $addbooking = Booking::create([
            'pbb_vendor_id' => $request->vendor_id,
            'pbb_customer_id' => $customer->pbc_id,
            'pbb_promo_id' => $request->promocode_id,
            'pbb_booking_details' => json_encode($booking_details_generated),
            'pbb_booking_date' => $request->booking_date,
            'pbb_booking_duration' => $duration,
            'pbb_booking_start_time' => $request->booking_start_time,
            'pbb_booking_end_time' => $request->booking_end_time,
            'pbb_ref_no' => uniqid('BOONOLKIINNEG_'),
            'pbb_type' => 'Online',
            'pbb_service_location' => $request->service_location,
            'pbb_total_amount' => $total_amount,
            'pbb_discounts' => 0,
            'pbb_contact_no' => ($request->booking_for_someone == 1) ? $request->someone_contact_no : $customer->customer_contact_no,
            'pbb_status' => 1
        ]);

        if ($addbooking) {
            foreach ($booking_details as $value) {
                $service = services::where('pbs_id', $value['service_id'])->first();
                $price = floatval(str_replace(',', '', $service->pbs_price));
                if ($service) {
                    bookingDetail::create([
                        'pbbd_booking_id' => $addbooking->pbb_id,
                        'pbbd_service_id' => $value['service_id'],
                        'pbbd_employee_id' => null,
                        'pbbd_promo_id' => null,
                        'pbbd_amount' => $price,
                        'pbbd_discount' => 0,
                        'pbbd_total_amount' => $price,
                        'pbb_status' => 1
                    ]);
                }
            }

            $vendors_user_id = User::where('pbu_vid', $request->vendor_id)->first();
            dd($vendors_user_id);
            if(!empty($vendors_user_id)){
                return response()->json([
                    'status' => false,
                    'message' => 'Vendor User Not Found',
                ], 404);
            }
            // $checkUserDeviceToken = deviceToken::where('pbdt_user_id', $vendors_user_id->pbu_id)->first();
            $notification_title = 'Booking Confirmed!';
            $notification_message = 'Booking added successfully!. Your booking reference no:'. $addbooking->pbb_ref_no;

            $oneSignalService->sendToUser($vendors_user_id->pbu_id, $notification_title, $notification_message);

            notification::create([
                'pbn_user_id' => $user->pbu_id,
                'pbn_type' => 'specific',
                'pbn_title' => $notification_title,
                'pbn_message' => $notification_message,
                'pbn_is_read' => 0,
            ]);

            // ✅ Add Payment Transaction
            $platform_fee_percentage = 10; // example: 10% commission
            $platform_fee = ($total_amount * $platform_fee_percentage) / 100;
            $vendor_amount = $total_amount - $platform_fee;

            $payment = paymentTransection::create([
                'pbpt_transaction_id'   => uniqid('TXN_'), // unique transaction ID
                'pbpt_booking_id'       => $addbooking->pbb_id,
                'pbpt_vendor_id'        => $request->vendor_id,
                'pbpt_customer_id'      => $customer->pbc_id,
                'pbpt_payment_method'   => 'Online', // fallback
                'pbpt_total_amount'     => $total_amount,
                'pbpt_discount_amount'  => 0, // you can add logic if promo applied
                'pbpt_final_amount'     => $total_amount,
                'pbpt_platform_fee'     => $platform_fee,
                'pbpt_vendor_amount'    => $vendor_amount,
                'pbpt_payment_response' => null, // store gateway response if online
                'pbpt_payment_ref_no'   => uniqid('PAYREF_'),
                'pbpt_description'      => 'Payment for booking #' . $addbooking->pbb_ref_no,
                'pbpt_status'           => 1, // 1 = success, 0 = pending, etc.
                'pbpt_remarks'          => 'Auto-generated payment record'
            ]);

            $vendorPayout = vendorPayouts::firstOrCreate(
                ['pbvp_vendor_id' => $request->vendor_id],
                ['pbvp_total_earned' => 0, 'pbvp_total_paid' => 0, 'pbvp_total_due' => 0]
            );

            $vendorPayout->increment('pbvp_total_earned', $vendor_amount);
            $vendorPayout->increment('pbvp_total_due', $vendor_amount);

            $payoutItem = vendorPayoutItems::create([
                'pbvpi_payout_id'   => $vendorPayout->pbvp_id,
                'pbvpi_booking_id'  => $addbooking->pbb_id,
                'pbvpi_payment_id'  => $payment->pbpt_id,
                'pbvpi_amount'      => $total_amount,
                'pbvpi_platform_fee'=> $platform_fee,
                'pbvpi_vendor_amount'=> $vendor_amount,
                'pbvpi_status'      => '0'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Booking added successfully',
                'data' => [
                    'booking_id' => $addbooking->pbb_id,
                    'booking_ref_no' => $addbooking->pbb_ref_no,
                    'vendor_id' => $addbooking->pbb_vendor_id,
                    'total_amount' => $total_amount,
                ]
            ], 200);
        }

        // vendorPayoutHistory::create([
        //     'pbvph_vendor_id' => $request->vendor_id,
        //     'pbvph_amount' => $vendor_amount,
        //     'pbvph_method' => 'system',
        //     'pbvph_reference' => $payment->pbpt_transaction_id,
        //     'pbvph_description' => 'Booking #' . $addbooking->pbb_ref_no . ' recorded as pending payout',
        //     'pbvph_status' => '0'
        // ]);

        return response()->json([
            'message' => "Unable to add the booking now. Please try again later",
        ], 500);       
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
 *     path="/api/addManualBooking",
 *     tags={"Booking"},
 *     summary="Add a manual booking",
 *     description="Create a new manual booking for a vendor, including services, customer details, and booking information.",
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             required={"vendor_id", "booking_date", "booking_start_time", "booking_end_time", "service_location", "services"},
 *             @OA\Property(property="vendor_id", type="integer", example=3, description="ID of the vendor"),
 *             @OA\Property(property="promocode_id", type="integer", nullable=true, example=null, description="Promo code ID (optional)"),
 *             @OA\Property(property="booking_date", type="string", format="date", example="2025-09-20", description="Date of booking"),
 *             @OA\Property(property="booking_start_time", type="string", format="time", example="10:30:00", description="Booking start time"),
 *             @OA\Property(property="booking_end_time", type="string", format="time", example="11:30:00", description="Booking end time"),
 *             @OA\Property(property="service_location", type="string", example="In Parlour", description="Service location"),
 *             @OA\Property(property="booking_for_someone", type="integer", example=1, description="1 if booking for someone else, 0 if self"),
 *             @OA\Property(property="someone_name", type="string", example="John Doe", description="Name of the person for whom booking is made"),
 *             @OA\Property(property="someone_contact_no", type="string", example="+919876543210", description="Contact number of the person"),
 *             @OA\Property(property="age", type="integer", example=28, description="Age of the person"),
 *             @OA\Property(property="gender", type="string", example="Male", description="Gender of the person"),
 *             @OA\Property(property="address", type="string", example="123 Main Street, Colombo", description="Address of the person"),
 *             @OA\Property(property="remarks", type="string", example="Please handle carefully", description="Additional remarks"),
 *             @OA\Property(
 *                 property="services",
 *                 type="array",
 *                 description="List of services for the booking",
 *                 @OA\Items(
 *                     type="object",
 *                     required={"service_id"},
 *                     @OA\Property(property="service_id", type="integer", example=12, description="ID of the service"),
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Booking added successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Booking added successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="booking_id", type="integer", example=101),
 *                 @OA\Property(property="booking_ref_no", type="string", example="BMOAONKUIANLG_66fabc1234abcd"),
 *                 @OA\Property(property="vendor_id", type="integer", example=3),
 *                 @OA\Property(property="total_amount", type="number", format="float", example=1500.00)
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Vendor not found",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="Vendor not found")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Unable to add booking",
 *         @OA\JsonContent(
 *             type="object",
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
                'booking_start_time' => 'required|date_format:H:i:s',
                'booking_end_time' => 'required|date_format:H:i:s',
                'service_location' => 'required',
                'services.*.service_id' => 'required|integer',
            ],
            [
                'booking_date.required' => 'Booking date is required',
                'booking_start_time.required' => 'Booking start time is required',
                'booking_end_time.required' => 'Booking end time is required',
                'service_location.required' => 'Service location is required',
                'services.*.service_id.required' => 'Service ID is required',
                'services.*.service_id.integer' => 'Service ID must be an integer',
            ]
        );

        $vendor = vendors::find($user->pbu_vid);
        if (!$vendor) {
            return response()->json([
                'status' => false,
                'message' => 'Vendor not found',
            ], 404);
        }

        $booking_details_generated = [
            'name' => $request->someone_name,
            'contact_no' => $request->someone_contact_no,
            'age' => $request->age,
            'gender' => $request->gender,
            'address' => $request->address,
            'remarks' => $request->remarks,
        ];

        $booking_details = $request->services;
        $total_amount = 0;
        $total_duration = 0; // in minutes

        foreach ($booking_details as $value) {
            $service = services::where('pbs_id', $value['service_id'])->first();
            if ($service) {
                $price = floatval(str_replace(',', '', $service->pbs_price));
                $total_amount += $price;
                $total_duration += $service->pbs_duration; // ✅ add service duration (minutes)
            }
        }

        // ✅ Convert total duration to HH:MM:SS
        $hours = floor($total_duration / 60);
        $remainingMinutes = $total_duration % 60;
        $duration = sprintf('%02d:%02d:00', $hours, $remainingMinutes);

        $addbooking = Booking::create([
            'pbb_vendor_id' => $vendor->pbv_id,
            'pbb_customer_id' => null,
            'pbb_promo_id' => $request->promocode_id,
            'pbb_booking_details' => json_encode($booking_details_generated),
            'pbb_booking_date' => $request->booking_date,
            'pbb_booking_duration' => $duration,
            'pbb_booking_start_time' => $request->booking_start_time,
            'pbb_booking_end_time' => $request->booking_end_time,
            'pbb_ref_no' => uniqid('BMOAONKUIANLG_'),
            'pbb_type' => 'Manual',
            'pbb_service_location' => $request->service_location,
            'pbb_contact_no' => ($request->booking_for_someone == 1) ? $request->someone_contact_no : $customer->customer_contact_no,
            'pbb_status' => 1
        ]);

        if ($addbooking) {
            foreach ($booking_details as $value) {
                $service = services::where('pbs_id', $value['service_id'])->first();
                $price = floatval(str_replace(',', '', $service->pbs_price));
                if ($service) {
                    bookingDetail::create([
                        'pbbd_booking_id' => $addbooking->pbb_id,
                        'pbbd_service_id' => $value['service_id'],
                        'pbbd_employee_id' => null,
                        'pbbd_promo_id' => null,
                        'pbbd_amount' => $price,
                        'pbbd_discount' => 0,
                        'pbbd_total_amount' => $price,
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
                    'total_amount' => $total_amount,
                ]
            ], 200);
        }

        return response()->json([
            'message' => "Unable to add the booking now. Please try again later",
        ], 500);

        
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
            ->with(['customer', 'bookingDetails.services' ])
            ->orderBy('pbb_booking_date', 'desc')
            ->get();

        if ($bookings->isEmpty()) {
            return response()->json(['message' => 'No bookings found'], 404);
        }

        $generated_bookings = $bookings->map(function ($booking) {
            $customer = $booking->customer;
            
            if ($customer) {
                // Customer exists — calculate age and other details
                $birthDate = $customer->pbc_dob ? Carbon::parse($customer->pbc_dob) : null;
                $age = $birthDate ? Carbon::now()->diffInYears($birthDate) : '-';

                $bookingDetails = [
                    'name' => trim(($customer->pbc_first_name ?? '') . ' ' . ($customer->pbc_last_name ?? '')),
                    'contact_no' => $customer->pbc_contact_no ?? '-',
                    'age' => $age,
                    'gender' => ($customer->pbc_sex == 1) ? 'Male' : 'Female',
                    'address' => trim(($customer->pbc_address ?? '') . ' ' . ($customer->pbc_city ?? '')),
                ];
            } else {
                // Manual booking (no linked customer)
                $bookingDetails = [
                    'name' => $booking->pbb_customer_name ?? 'Walk-in Customer',
                    'contact_no' => $booking->pbb_contact_no ?? '-',
                    'age' => '-',
                    'gender' => '-',
                    'address' => $booking->pbb_customer_address ?? '-',
                ];
            }

            // Convert services to string array
            $serviceNames = $booking->bookingDetails->map(function ($detail) {
                return $detail->services->pbs_name ?? null;
            })->filter()->values()->toArray();
            
            return [
                'pbb_id' => $booking->pbb_id,
                'pbb_promo_id' => $booking->pbb_promo_id,
                'pbb_booking_details' => $bookingDetails,
                'pbb_booking_date' => $booking->pbb_booking_date,
                'pbb_booking_duration' => $booking->pbb_booking_duration,
                'pbb_booking_start_time' => $booking->pbb_booking_start_time,
                'pbb_booking_end_time' => $booking->pbb_booking_end_time,
                'pbb_ref_no' => $booking->pbb_ref_no,
                'pbb_type' => $booking->pbb_type,
                'pbb_service_location' => $booking->pbb_service_location,
                'pbb_contact_no' => $booking->pbb_contact_no,
                'pbb_status' => $booking->pbb_status,
                'created_at' => $booking->created_at,
                'updated_at' => $booking->updated_at,
                'deleted_at' => $booking->deleted_at,
                'pbb_remarks' => $booking->pbb_remarks,
                'pbbd_total_amount' => $booking->bookingDetails->sum('pbbd_amount'),
                'services' => $serviceNames,
            ];
        }); 

        return response()->json([
            'status' => true,
            'message' => 'Bookings retrieved successfully',
            'data' => $generated_bookings
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
        $bookings = booking::with([
            'customer',
            'bookingDetails.services',
        ])->find($id);

        if (!$bookings) {
            return response()->json(['message' => 'No bookings found'], 404);
        }

        $customer = $bookings->customer;
            
        if ($customer) {
            // Customer exists — calculate age and other details
            $birthDate = $customer->pbc_dob ? Carbon::parse($customer->pbc_dob) : null;
            $age = $birthDate ? Carbon::now()->diffInYears($birthDate) : '-';

            $bookingDetails = [
                'name' => trim(($customer->pbc_first_name ?? '') . ' ' . ($customer->pbc_last_name ?? '')),
                'contact_no' => $customer->pbc_contact_no ?? '-',
                'age' => $age,
                'gender' => ($customer->pbc_sex == 1) ? 'Male' : 'Female',
                'address' => trim(($customer->pbc_address ?? '') . ' ' . ($customer->pbc_city ?? '')),
            ];
        } else {
            // Manual booking (no linked customer)
            $bookingDetails = [
                'name' => $booking->pbb_customer_name ?? 'Walk-in Customer',
                'contact_no' => $booking->pbb_contact_no ?? '-',
                'age' => '-',
                'gender' => '-',
                'address' => $booking->pbb_customer_address ?? '-',
            ];
        }
        //dd($bookings->customer);
        // age calculation
        // $birthDate = Carbon::parse($bookings->customer->pbc_dob);
        // $today = Carbon::now();
        // $age = $today->diffInYears($birthDate);

        // $bookingDetails = [
        //     'name' => $bookings->customer->pbc_first_name . ' ' . $bookings->customer->pbc_last_name,
        //     'contact_no' => $bookings->customer->pbc_contact_no,
        //     'age' => $age,
        //     'gender' => ($bookings->customer->pbc_sex == 1) ? 'Male' : 'Female',
        //     'address' => $bookings->customer->pbc_address . ' ' . $bookings->customer->pbc_city
        // ];    
        
        $booking_details = [
            'pbb_id' => $bookings->pbb_id,
            'pbb_promo_id' => $bookings->pbb_promo_id,
            'pbb_booking_details' => $bookingDetails,
            'pbb_booking_date' => $bookings->pbb_booking_date,
            'pbb_booking_duration' => $bookings->pbb_booking_duration,
            'pbb_booking_start_time' => $bookings->pbb_booking_start_time,
            'pbb_booking_end_time' => $bookings->pbb_booking_end_time,
            'pbb_ref_no' => $bookings->pbb_ref_no,
            'pbb_type' => $bookings->pbb_type,
            'pbb_service_location' => $bookings->pbb_service_location,
            'pbb_contact_no' => $bookings->pbb_contact_no,
            'pbb_status' => $bookings->pbb_status,
            'created_at' => $bookings->created_at,
            'updated_at' => $bookings->updated_at,
            'deleted_at' => $bookings->deleted_at,
            'pbb_remarks' => $bookings->pbb_remarks,
            'pbbd_total_amount' => $bookings->bookingDetails->sum('pbbd_amount'),
            'services' => $bookings->bookingDetails->map(function ($detail) {
                return [
                    'pbbd_id' => $detail->pbbd_id,
                    'pbs_id' => $detail->services->pbs_id,
                    'pbs_name' => $detail->services->pbs_name,
                    'pbs_price' => $detail->services->pbs_price,
                    'pbs_duration' => $detail->services->pbs_duration,
                    'pbs_image' => $detail->services->pbs_image,
                ];
            }),
        ];       

        return response()->json([
            'status' => true,
            'message' => 'Booking Details retrieved successfully',
            'data' => $booking_details
        ], 200);
    }

    public function vendorPayouts()
    {
        $user = auth()->user();

        $request->validate([
            'payout_item_ids' => 'required|array', // e.g., [1, 2, 3]
            'payout_item_ids.*' => 'integer|exists:vendor_payout_items,pbvpi_id',
            'paying_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|string',
        ]);

        $vendor = vendors::with('booking')->where('pbv_id', $user->pbu_vid)->first();
        if (!$vendor) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $payouts = vendorPayouts::where('pbvp_vendor_id', $vendor->pbv_id)->first();
        if (!$payouts) {
            return response()->json(['message' => 'No payout records found'], 404);
        }

        $amountToPay = $request->paying_amount;
        $payment_moethod = $request->payment_method; // e.g., 'bank', 'paypal', etc.
        $selectedItemIds = $request->payout_item_ids;

        $selectedItems = vendorPayoutItems::whereIn('pbvpi_id', $selectedItemIds)
                                            ->where('pbvpi_status', '0')
                                            ->get();

        $totalSelectedAmount = $selectedItems->sum('pbvpi_vendor_amount');

        // ✅ Validation: the amount paid must not exceed due total
        if ($amountToPay > $vendorPayout->pbvp_total_due) {
            return response()->json(['message' => 'Paying amount exceeds total due'], 422);
        }

        if ($totalSelectedAmount <= 0) {
            return response()->json(['message' => 'No valid payout items found to process'], 404);
        }

        if ($amountToPay > 0) {
            $payoutHistory = vendorPayoutHistory::create([
                'pbvph_vendor_id' => $vendorId,
                'pbvph_amount' => $amountToPay,
                'pbvph_method' => $payment_moethod,
                'pbvph_reference' => 'PAYOUT_' . uniqid(),
                'pbvph_description' => 'Vendor payout processed',
                'pbvph_status' => '1'
            ]);

            // Update vendor payout totals
            $vendorPayout->increment('pbvp_total_paid', $amountToPay);
            $vendorPayout->decrement('pbvp_total_due', $amountToPay);

            // Mark related payout items as paid
            vendorPayoutItems::whereIn('pbvpi_id', $selectedItemIds)
                ->update(
                    [
                        'pbvpi_status' => '1',
                        'updated_at' => now(),
                        'pbvpi_payout_history_id' => $payoutHistory->pbvph_id
                    ]
                );
        }

        $payoutItems = vendorPayoutItems::where('pbvpi_payout_id', $payouts->pbvp_id)
            ->with(['booking', 'payment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Payout records retrieved successfully',
            'data' => [
                'total_earned' => $payouts->pbvp_total_earned,
                'total_paid' => $payouts->pbvp_total_paid,
                'total_due' => $payouts->pbvp_total_due,
                'payout_items' => $payoutItems
            ]
        ], 200);
    }
}