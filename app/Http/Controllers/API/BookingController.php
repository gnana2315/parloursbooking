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
    public function getBookingSlots(Request $request){
        $user = auth()->user();  
        
        $availableSlots = [];
        $finalSlots = [];
        
        $vendorId = $request->query('vendor_id');
        $bookingDate = $request->query('booking_date');
        $serviceDuration = $request->query('service_total_duration');

        // Get vendor's standard availability for that day
        $availability = vendorStandardAvailability::where('pbvsa_vendor_id', $vendorId)
            ->where('pbvsa_day', date('l', strtotime($bookingDate)))
            ->first();

        if (!$availability || !$availability->pbvsa_is_open) {
            return response()->json([
                'status' => false,
                'message' => 'Vendor is closed on selected date',
                'data' => []
            ], 200);
        }

        $openTime = Carbon::createFromTimeString($availability->pbvsa_start_time);
        $closeTime = Carbon::createFromTimeString($availability->pbvsa_end_time);

        $existingBookings = booking::where('pbb_vendor_id', $vendorId)
            ->where('pbb_booking_date', $bookingDate)
            ->where('pbb_status', '=', 1)
            ->orderBy('pbb_booking_start_time')
            ->get();

        $availableSlots = [];
        $finalSlots = [];
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
                $bookingStart = Carbon::createFromTimeString($booking->pbb_booking_start_time);
                $bookingEnd = Carbon::createFromTimeString($booking->pbb_booking_end_time);

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

            // Slice each available gap into proper service duration slots
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

        return response()->json([
            'status' => true,
            'message' => 'Available slots',
            'data' => $finalSlots,
        ], 200);
    }

    /**
     * @OA\Post(
     *      path="/api/addOnlineBooking",
     *      operationId="addOnlineBooking",
     *      tags={"Bookings"},
     *      security={{"bearerAuth": {}}},
     *      summary="Add Online Booking",
     *      description="Add Online Bookings",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"vendor_id", "promocode_id", "booking_details", "booking_date", "booking_duration", "booking_start_time", "booking_end_time", "service_location"},
     *              @OA\Property(property="vendor_id", type="number", example="1"),
     *              @OA\Property(property="booking_for_someone", type="number", example="0(myself)/1(someoneelse)"),
     *              @OA\Property(property="booking_details", type="string", example="name,contactno,remarks"),
     *              @OA\Property(property="booking_date", type="date", example="2025-04-29"),
     *              @OA\Property(property="service_total_duration", type="time", example="02:00:00"),
     *              @OA\Property(property="booking_start_time", type="time", example="08:00:00"),
     *              @OA\Property(property="booking_end_time", type="time", example="10:00:00"),
     *              @OA\Property(property="service_location", type="string", example="inhouse/other"),
     *          ),
     *      ),
     *      @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Unauthenticated")
     *         )
     *      )
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
                'services.*.service_id' => 'required|integer',
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
            'pbb_ref_no' => uniqid('BOOKING_'),
            'pbb_type' => 'Online',
            'pbb_service_location' => $request->service_location,
            'pbb_contact_no' => ($request->booking_for_someone == 1) ? $request->someone_contact_no : $customer->customer_contact_no,
            'pbb_status' => 1
        ]);

        if($addbooking){
            $booking_details = json_decode($request->booking_details, true);
            $total_amount = 0;
            foreach($booking_details as $key => $value){
                $service = services::where('pbs_id', $value['service_id'])->first();
                if($service){
                    $total_amount += $service->pbvs_price;
                    bookingDetail::create([
                        'pbbd_booking_id' => $addbooking->pbb_id,
                        'pbbd_service_id' => $value['service_id'],
                        'pbbd_employee_id' => null,
                        'pbbd_promo_id' => null,
                        'pbbd_amount' => $service->pbvs_price,
                        'pbbd_discount' => 0,
                        'pbbd_total_amount' => $service->pbvs_price,
                        'pbb_status' => 1
                    ]);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Booking added successfully',
                'data' => [
                    'booking_id' => $addbooking->pbb_id,
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
}