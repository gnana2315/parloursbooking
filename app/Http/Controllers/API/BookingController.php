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
use App\Models\promoCode;

use Validator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\deviceToken;
use App\Services\FirebaseService;
use App\Services\OneSignalService;
use App\Services\DialogESMSService;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\PublicKeyLoader;
use App\Services\WebXPayService;

class BookingController extends Controller
{
    public function __construct(DialogESMSService $smsService)
    {
        $this->smsService = $smsService;
    }
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

        //Check Special Closes
        $specialClose = vendorSpecialCloses::where('pbvsc_vendor_id', $vendorId)
                        ->whereDate('pbvsc_day', $bookingDate)
                        ->where('pbvsc_status', 1)
                        ->first();

        if ($specialClose && $specialClose->pbvsc_full_day_closed) {
            Log::info('Vendor fully closed (special close)', ['date' => $bookingDate]);

            return response()->json([
                'status' => false,
                'message' => 'Vendor is closed on selected date',
                'data' => []
            ], 200);
        }

        $specialCloseFrom = null;
        $specialCloseTo = null;

        if ($specialClose && !$specialClose->pbvsc_full_day_closed) {
            $specialCloseFrom = Carbon::createFromTimeString($specialClose->pbvsc_from_time)
                ->setDateFrom(Carbon::parse($bookingDate));

            $specialCloseTo = Carbon::createFromTimeString($specialClose->pbvsc_to_time)
                ->setDateFrom(Carbon::parse($bookingDate));
        }

        // Get vendor's standard availability
        $availability = vendorStandardAvailability::where('pbvsa_vendor_id', $vendorId)
            ->where('pbvsa_day', date('l', strtotime($bookingDate)))
            ->where('pbvsa_is_open', 1)
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

        
        $booking_buffer = 10;

        // ✅ Adjust open time if booking date is today
        if ($bookingDate === $today) {
            $todayBufferMinutes = 10;
            $adjustedNow = $now->copy()->addMinutes($todayBufferMinutes);
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
                        'end' => (clone $bookingStart)->subMinutes($booking_buffer),
                    ];
                }

                if ($currentStart->lt($bookingEnd)) {
                    // $currentStart = clone $bookingEnd;
                    $currentStart = (clone $bookingEnd)->addMinutes($booking_buffer);
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

                // while ($startTime->copy()->addMinutes($serviceDuration)->lte($endTime)) {
                //     $finalSlots[] = [
                //         'start' => $startTime->format('H:i:s'),
                //         'end' => (clone $startTime)->addMinutes($serviceDuration)->format('H:i:s'),
                //     ];
                //     $startTime->addMinutes($serviceDuration);
                // }
                while ($startTime->copy()->addMinutes($serviceDuration)->lte($endTime)) {

                    $slotStart = clone $startTime;
                    $slotEnd   = (clone $startTime)->addMinutes($serviceDuration);

                    // 🚫 Skip slot if overlaps with special close
                    if ($specialCloseFrom && $specialCloseTo) {
                        if ($slotStart->lt($specialCloseTo) && $slotEnd->gt($specialCloseFrom)) {
                            $startTime->addMinutes($serviceDuration);
                            continue;
                        }
                    }

                    $finalSlots[] = [
                        'start' => $slotStart->format('H:i:s'),
                        'end'   => $slotEnd->format('H:i:s'),
                    ];

                    $startTime->addMinutes($serviceDuration);
                }
            }
        }
        Log::info('Available time slots:', ['Response' => $finalSlots]);
        return response()->json([
            'status' => true,
            'message' => 'Available slots',
            'data' => $finalSlots,
        ], 200);
    }

    /**
 * @OA\Get(
 *     path="/api/calculate-total",
 *     summary="Calculate total after applying promo code",
 *     description="Returns subtotal, discount, and final total for selected services, considering vendor/service-specific promo codes and minimum booking amount.",
 *     tags={"Booking"},
 *     @OA\Parameter(
 *         name="service_ids[]",
 *         in="query",
 *         required=false,
 *         description="List of service IDs",
 *         @OA\Schema(type="array", @OA\Items(type="integer"))
 *     ),
 *     @OA\Parameter(
 *         name="vendor_id",
 *         in="query",
 *         required=false,
 *         description="Vendor ID (required for vendor-specific promos)",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="promo_code",
 *         in="query",
 *         required=true,
 *         description="Optional promo code",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="booking_date",
 *         in="query",
 *         required=true,
 *         description="booking date",
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Total calculated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(property="message", type="string", example="Total calculated successfully"),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="sub_total", type="number", format="float", example=1500.00),
 *                 @OA\Property(property="discount", type="number", format="float", example=150.00),
 *                 @OA\Property(property="final_total", type="number", format="float", example=1350.00)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid input or promo code not applicable",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=false),
 *             @OA\Property(property="message", type="string", example="This promo code requires a minimum booking amount of 500.")
 *         )
 *     )
 * )
 */
    public function calculateTotal(Request $request)
    {
        // Decode service_ids if sent as JSON string
        if (is_string($request->query('service_ids'))) {
            $decoded = json_decode($request->query('service_ids'), true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $request->merge([
                    'service_ids' => $decoded
                ]);
            }
        }

        // For GET, fetch query params
        $vendor_id = $request->query('vendor_id');
        $promo_code = $request->query('promo_code');
        $booking_date = $request->query('booking_date');

        $request->merge([
            'vendor_id' => $vendor_id,
            'promo_code' => $promo_code,
            'booking_date' => $booking_date,
        ]);

        $request->validate([
            'service_ids' => 'required|array',
            'service_ids.*' => 'integer|exists:services,pbs_id',
            'vendor_id' => 'nullable|integer|exists:vendor,pbv_id',
            'promo_code' => 'nullable|string',
            'booking_date' => 'nullable|date',
        ]);

        // 1. Get services
        $services = services::whereIn('pbs_id', $request->service_ids)->get();

        if ($services->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No valid services found',
            ], 400);
        }

        // 2. Subtotal
        $subTotal = $services->sum(function($service) {
            return floatval(str_replace(',', '', $service->pbs_price));
        });

        $discountAmount = 0;
        $finalTotal = $subTotal;

        // 3. Apply Promo Code
        if ($request->promo_code) {
            $promo = promoCode::where('pbpc_code', $request->promo_code)
                ->where('pbpc_status', 1)
                ->first();

            if (!$promo) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid promo code',
                ], 400);
            }

            // 🔥 NEW — Check promo start and end date
            $bookingDate = $request->booking_date
                ? Carbon::parse($request->booking_date)->startOfDay()
                : now()->startOfDay();

            if ($promo->pbpc_start_date &&
                $bookingDate->lt(Carbon::parse($promo->pbpc_start_date)->startOfDay())) {

                return response()->json([
                    'status' => false,
                    'message' => 'This promo code is not active for the selected booking date.',
                ], 400);
            }

            if ($promo->pbpc_end_date &&
                $bookingDate->gt(Carbon::parse($promo->pbpc_end_date)->startOfDay())) {

                return response()->json([
                    'status' => false,
                    'message' => 'This promo code has expired for the selected booking date.',
                ], 400);
            }
            // 🔥 NEW END


            // Determine eligible services
            $eligibleServices = $services;

            // Vendor-wise promo
            if ($promo->type === 'vendor' && $request->vendor_id) {
                $promoVendorIds = explode(',', $promo->pbpc_vendor_ids); // comma-separated vendor IDs
                if (!in_array($request->vendor_id, $promoVendorIds)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'This promo code is not valid for this vendor.',
                    ], 400);
                }
                $eligibleServices = $services->filter(fn($s) => $s->pbs_vendor_id == $request->vendor_id);
            }

            // Service-wise promo
            if ($promo->pbpc_promo_types === 'service' && $promo->pbpc_service_ids) {
                $eligibleServiceIds = explode(',', $promo->pbpc_service_ids); // assuming comma separated
                $eligibleServices = $services->filter(fn($s) => in_array($s->pbs_id, $eligibleServiceIds));
            }

            // Vendor's service-wise promo
            if ($promo->pbpc_promo_types === 'vendor_service' && $request->vendor_id && $promo->pbpc_vendor_service_map) {
                $promoVendorIds = explode(',', $promo->pbpc_vendor_ids);
                if (!in_array($request->vendor_id, $promoVendorIds)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'This promo code is not valid for this vendor.',
                    ], 400);
                }
                $eligibleServiceIds = explode(',', $promo->pbpc_vendor_service_map);
                $eligibleServices = $services->filter(fn($s) => $s->pbs_vendor_id == $request->vendor_id && in_array($s->pbs_id, $eligibleServiceIds));
            }

            // Calculate discount on eligible services
            $eligibleTotal = $eligibleServices->sum(fn($s) => floatval(str_replace(',', '', $s->pbs_price)));

             // Check minimum booking amount
            if ($promo->pbpc_min_booking_amount && $eligibleTotal < $promo->pbpc_min_booking_amount) {
                return response()->json([
                    'status' => false,
                    'message' => "This promo code requires a minimum booking amount of {$promo->pbpc_min_booking_amount}.",
                ], 400);
            }

            if ($promo->pbpc_discount_type === 'percentage') {
                $discountAmount = ($eligibleTotal * $promo->pbpc_value) / 100;
            }

            if ($promo->pbpc_discount_type === 'fixed') {
                $discountAmount = min($promo->pbpc_value, $eligibleTotal); // cannot exceed eligible total
            }

            $finalTotal = max($subTotal - $discountAmount, 0);
        }

        return response()->json([
            'status' => true,
            'message' => 'Total calculated successfully',
            'data' => [
                'sub_total' => round($subTotal, 2),
                'discount' => round($discountAmount, 2),
                'final_total' => round($finalTotal, 2),
            ]
        ], 200);
    }

/**
 * @OA\Post(
 *     path="/api/addOnlineBooking",
 *     tags={"Booking"},
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
        Log::info('addOnlineBooking Requests:', ['Requests' => $request->all()]);
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

        // ✅ Prevent overlapping bookings (for same vendor)
        $overlappingBooking = Booking::where('pbb_vendor_id', $request->vendor_id)
            ->where('pbb_booking_date', $request->booking_date)
            ->whereIn('pbb_status', [1])
            ->where(function ($q) use ($request) {
                $q->whereBetween('pbb_booking_start_time', [$request->booking_start_time, $request->booking_end_time])
                ->orWhereBetween('pbb_booking_end_time', [$request->booking_start_time, $request->booking_end_time])
                ->orWhere(function ($q2) use ($request) {
                    $q2->where('pbb_booking_start_time', '<=', $request->booking_start_time)
                        ->where('pbb_booking_end_time', '>=', $request->booking_end_time);
                });
            })
            ->first();

        if ($overlappingBooking) {
            return response()->json([
                'status' => false,
                'message' => 'The selected time slot is already booked by another customer.('.$overlappingBooking->pbb_ref_no.')',
            ], 409);
        }

        try {
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
                'pbb_status' => 3
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->errorInfo[1] == 1062) { // duplicate entry
                return response()->json([
                    'status' => false,
                    'message' => 'This booking already exists or was just created.',
                ], 409);
            }
            throw $e;
        }        
        Log::info('addOnlineBooking Response:', ['Response' => $addbooking]);
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
            
            if(empty($vendors_user_id)){
                return response()->json([
                    'status' => false,
                    'message' => 'Vendor User Not Found',
                ], 404);
            }
            // $checkUserDeviceToken = deviceToken::where('pbdt_user_id', $vendors_user_id->pbu_id)->first();
            $notification_title = 'Booking Confirmed!';
            $notification_message = 'Booking added successfully!. Your booking reference no:'. $addbooking->pbb_ref_no;
            $booking_details_for_notification = [
                'booking_ref_no' => $addbooking->pbb_ref_no,
                'booking_date' => $addbooking->pbb_booking_date,
                'booking_start_time' => $addbooking->pbb_booking_start_time,
                'booking_end_time' => $addbooking->pbb_booking_end_time,
                'total_amount' => $addbooking->pbb_total_amount,
            ];
            Log::info('vendors_user_id Response:', ['Response' => $vendors_user_id->pbu_id]);
            $booking_notification = $oneSignalService->sendToUser($vendors_user_id->pbu_id, $notification_title, $notification_message, $booking_details_for_notification);
            Log::info('booking_notification Response:', ['Response' => $booking_notification]);
            if($booking_notification){
                notification::create([
                    'pbn_user_id' => $user->pbu_id,
                    'pbn_type' => 'specific',
                    'pbn_title' => $notification_title,
                    'pbn_message' => $notification_message,
                    'pbn_is_read' => 0,
                ]);
            }

            $sms_customer_name = $request->someone_name ? $request->someone_name : $customer->pbc_first_name;
            $sms_vendor_name = $vendor->pbv_business_name;
            $sms_booking_date = $addbooking->pbb_booking_date->format('d M Y');
            $sms_booking_start_time = $addbooking->pbb_booking_start_time->format('H:i A');
            $sms_booking_end_time = $addbooking->pbb_booking_end_time->format('H:i A');
            $sms_total_amount = $addbooking->pbb_total_amount;
            $sms_booking_ref_no = $addbooking->pbb_ref_no;
            $sms_phone_no = $request->phone_no ? $request->phone_no : $customer->pbc_contact_no;

            $apiKey = config('dialogesms.api_key');
            $sender = config('dialogesms.sender');
            // $message = "Hello {$sms_customer_name}, your booking at {$sms_vendor_name} has been confirmed!\n\n" .
            //             "Date: {$sms_booking_date}\n" .
            //             "Time: {$sms_booking_start_time} - {$sms_booking_end_time}\n" .
            //             "Total Amount: {$sms_total_amount}\n" .
            //             "Booking Ref: {$sms_booking_ref_no}\n\n" .
            //             "Thank you for choosing Parlours Booking!";
            
            $message = "Dear Customer,\n".
                        "Your booking is confirmed on {$sms_booking_date} at {$sms_booking_start_time} | Ref: {$sms_booking_ref_no}. Please arrive 10 mins early.\n" .
                        "Thank you for choosing Parlours Booking!";

            // Store OTP to DB/Cache if needed here
            //$smsEnable = filter_var($request->header('SMS_ENABLE', true), FILTER_VALIDATE_BOOLEAN);
            //if($smsEnable){
                $booking_sms_result = $this->smsService->sendMessage($apiKey, [$sms_phone_no], $message, $sender);       
            //}
            Log::info('booking_sms_result Response:', ['Response' => $booking_sms_result]);
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
            Log::info('payment transection Response:', ['Response' => $payment]);
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
            Log::info('vendor payouts Response:', ['Response' => $payoutItem]);
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

    public function addOnlineBooking_v1(
        Request $request,
        OneSignalService $oneSignalService,
    ) {
        Log::info('addOnlineBooking_v1 Requests:', ['Requests' => $request->all()]);
        
        $user = auth()->user();
        
        try {
            // 1️⃣ Validation
            $request->validate([
                'vendor_id' => 'required',
                'booking_details.*.service_id' => 'required|integer',
                'booking_date' => 'required',
                'booking_start_time' => 'required|date_format:H:i:s',
                'booking_end_time' => 'required|date_format:H:i:s',
                'service_location' => 'required',
            ]);

            // 2️⃣ Get vendor & customer
            $vendor = vendors::find($request->vendor_id);
            if (!$vendor) {
                return response()->json(['status' => false, 'message' => 'Vendor not found'], 404);
            }

            $customer = customer::where('pbc_user_id', $user->pbu_id)->first();
            if (!$customer) {
                return response()->json(['status' => false, 'message' => 'Customer not found'], 404);
            }

            // 3️⃣ Calculate total amount & duration
            $booking_details = $request->booking_details;
            $total_amount = 0;
            $total_duration = 0;

            foreach ($booking_details as $item) {
                $service = services::where('pbs_id', $item['service_id'])->first();
                if ($service) {
                    $price = floatval(str_replace(',', '', $service->pbs_price));
                    $total_amount += $price;
                    $total_duration += $service->pbs_duration;
                }
            }

            $hours = floor($total_duration / 60);
            $minutes = $total_duration % 60;
            $duration = sprintf('%02d:%02d:00', $hours, $minutes);

            // 4️⃣ Prevent overlapping bookings
            $overlappingBooking = Booking::where('pbb_vendor_id', $request->vendor_id)
                                ->where('pbb_booking_date', $request->booking_date)
                                ->whereIn('pbb_status', [1])
                                ->where(function ($q) use ($request) {
                                    $q->where('pbb_booking_start_time', '<', $request->booking_end_time)
                                    ->where('pbb_booking_end_time', '>', $request->booking_start_time);
                                })
                                ->first();

            if ($overlappingBooking) {
                return response()->json([
                    'status' => false,
                    'message' => 'The selected time slot is already booked by another customer. ('.$overlappingBooking->pbb_ref_no.')'
                ], 409);
            }

            // 5️⃣ Create booking
            $addbooking = Booking::create([
                'pbb_vendor_id' => $vendor->pbv_id,
                'pbb_customer_id' => $customer->pbc_id,
                'pbb_promo_id' => $request->promocode_id ?? null,
                'pbb_booking_details' => json_encode([
                    'name' => $request->someone_name,
                    'contact_no' => $request->someone_contact_no,
                    'age' => $request->age,
                    'gender' => $request->gender,
                    'address' => $request->address,
                ]),
                'pbb_booking_date' => $request->booking_date,
                'pbb_booking_duration' => $duration,
                'pbb_booking_start_time' => $request->booking_start_time,
                'pbb_booking_end_time' => $request->booking_end_time,
                'pbb_ref_no' => uniqid('BOONOLKIINNEG_'),
                'pbb_type' => 'Online',
                'pbb_service_location' => $request->service_location,
                'pbb_total_amount' => $total_amount,
                'pbb_discounts' => 0,
                'pbb_contact_no' => $request->booking_for_someone == 1 ? $request->someone_contact_no : $customer->pbc_contact_no,
                'pbb_status' => 3,
            ]);

            // 6️⃣ Create booking details
            foreach ($booking_details as $item) {
                $service = services::where('pbs_id', $item['service_id'])->first();
                if ($service) {
                    bookingDetail::create([
                        'pbbd_booking_id' => $addbooking->pbb_id,
                        'pbbd_service_id' => $service->pbs_id,
                        'pbbd_employee_id' => null,
                        'pbbd_promo_id' => null,
                        'pbbd_amount' => floatval(str_replace(',', '', $service->pbs_price)),
                        'pbbd_discount' => 0,
                        'pbbd_total_amount' => floatval(str_replace(',', '', $service->pbs_price)),
                        'pbb_status' => 1,
                    ]);
                }
            }

            // // 7️⃣ Notifications & SMS (Optional)
            // $vendors_user = User::where('pbu_vid', $request->vendor_id)->first();
            // if ($vendors_user) {
            //     $notification_title = 'Booking Confirmed!';
            //     $notification_message = 'Booking added successfully!. Your booking reference no:'. $addbooking->pbb_ref_no;
            //     $booking_details_for_notification = [
            //         'booking_ref_no' => $addbooking->pbb_ref_no,
            //         'booking_date' => $addbooking->pbb_booking_date,
            //         'booking_start_time' => $addbooking->pbb_booking_start_time,
            //         'booking_end_time' => $addbooking->pbb_booking_end_time,
            //         'total_amount' => $addbooking->pbb_total_amount,
            //     ];
            //     $booking_notification = $oneSignalService->sendToUser(
            //         $vendors_user->pbu_id,
            //         $notification_title,
            //         $notification_message,
            //         $booking_details_for_notification
            //     );

            //     Log::info('booking_notification Response:', ['Response' => $booking_notification]);
            //     if($booking_notification){
            //         notification::create([
            //             'pbn_user_id' => $user->pbu_id,
            //             'pbn_type' => 'specific',
            //             'pbn_title' => $notification_title,
            //             'pbn_message' => $notification_message,
            //             'pbn_is_read' => 0,
            //         ]);
            //     }
            // }

            // $sms_customer_name = $request->someone_name ? $request->someone_name : $customer->pbc_first_name;
            // $sms_vendor_name = $vendor->pbv_business_name;
            // $sms_booking_date = $addbooking->pbb_booking_date;
            // $sms_booking_start_time = $addbooking->pbb_booking_start_time;
            // $sms_booking_end_time = $addbooking->pbb_booking_end_time;
            // $sms_total_amount = $addbooking->pbb_total_amount;
            // $sms_booking_ref_no = $addbooking->pbb_ref_no;
            // $sms_phone_no = $request->phone_no ? $request->phone_no : $customer->pbc_contact_no;

            // $apiKey = config('dialogesms.api_key');
            // $sender = config('dialogesms.sender');

            // $message = "Hello {$sms_customer_name}, your booking at {$sms_vendor_name} has been confirmed!\n\n" .
            //             "Booking Ref: {$sms_booking_ref_no}\n\n" .
            //             "Thank you for choosing Parlours Booking!";
            
            // SMS sending (uncomment when needed)
            // $booking_sms_result = $this->smsService->sendMessage($apiKey, [$sms_phone_no], $message, $sender);
            // Log::info('booking_sms_result Response:', ['Response' => $booking_sms_result]);

            return response()->json([
                'status' => true,
                'message' => 'Booking created. Proceed to payment.',
                'data' => [
                    'booking_id' => $addbooking->pbb_id,
                    'booking_ref_no' => $addbooking->pbb_ref_no,
                    'payment_url' => url('/payments/webxpay/start?ref=' . $addbooking->pbb_ref_no),
                ]
            ], 200);

        } catch (\Throwable $e) {
            Log::error('Error in addOnlineBooking_v1: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['status' => false, 'message' => 'Unable to add the booking. '.$e->getMessage()], 500);
        }
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
        Log::info('add rating Requests:', ['Requests' => $request->all()]);
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
        Log::info('add rating Response:', ['Response' => $addRating]);
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
        Log::info('addManualBooking Requests:', ['Requests' => $request->all()]);
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

        // 4️⃣ Prevent overlapping bookings
        $overlappingBooking = Booking::where('pbb_vendor_id', $request->vendor_id)
                            ->where('pbb_booking_date', $request->booking_date)
                            ->whereIn('pbb_status', [1])
                            ->where(function ($q) use ($request) {
                                $q->where('pbb_booking_start_time', '<', $request->booking_end_time)
                                ->where('pbb_booking_end_time', '>', $request->booking_start_time);
                            })
                            ->first();

        if ($overlappingBooking) {
            return response()->json([
                'status' => false,
                'message' => 'The selected time slot is already booked by another customer. ('.$overlappingBooking->pbb_ref_no.')'
            ], 409);
        }

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
        Log::info('addManualBooking Response:', ['Response' => $addbooking]);
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
     * @OA\Post(
     *     path="/api/booking/mark-status",
     *     summary="Mark booking status",
     *     description="Update booking status as completed, rejected, or no customer.",
     *     operationId="markBookingStatus",
     *     tags={"Booking"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"booking_id","booking_status"},
     *             @OA\Property(
     *                 property="booking_id",
     *                 type="integer",
     *                 example=45,
     *                 description="Booking ID"
     *             ),
     *             @OA\Property(
     *                 property="booking_status",
     *                 type="integer",
     *                 enum={1,2,3},
     *                 example=1,
     *                 description="1 = Completed, 2 = Rejected, 3 = No Customer"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Booking status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Booking marked as completed successfully"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Booking not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Booking not found"
     *             )
     *         )
     *     )
     * )
    */

    public function markBookingStatus(Request $request,  OneSignalService $oneSignalService){
        Log::info('Mark Booking Status Requests:', ['Requests' => $request->all()]);
        $user = auth()->user();

        $request->validate(
            [
                'booking_id' => 'required|integer',
                'booking_status' => 'required|integer',
            ],
            [
                'booking_id.required' => 'Booking ID is required',
                'booking_id.integer' => 'Booking ID must be an integer',
            ]
        );

        $booking = booking::with('paymentTransections')->find($request->booking_id);
        var_dump('<pre>');
        var_dump($booking);
        var_dump('</pre>');
        die();
        if (!$booking) {
            return response()->json([
                'status' => false,
                'message' => 'Booking not found',
            ], 404);
        }

        if($request->booking_status == 4){
            // If booking is marked as 'No Customer', initiate notification.
            $customerUser = customer::find($booking->pbb_customer_id);
            
            Log::info('Customer User Details:', ['CustomerUser' => $customerUser]);
            // Send notification to CUSTOMER for successful payment
            if ($customerUser) {
                $customerNotificationTitle = 'Booking Cancelled!';
                $customerNotificationMessage = 'Booking Cancelled due to your absense. Your booking reference no: '. $booking->pbb_ref_no;
                $customerNotificationData = [
                    'booking_ref_no' => $booking->pbb_ref_no,
                    'booking_id' => $booking->pbb_id,
                    'status' => $booking->pbb_status,
                    'transaction_id' => $booking->pbb_ref_no,
                    'amount' => $booking->pbb_total_amount
                ];

                $customerNotificationResult = $oneSignalService->sendToUser(
                    $customerUser->pbc_user_id,
                    $customerNotificationTitle,
                    $customerNotificationMessage,
                    $customerNotificationData
                );
                    
                if ($customerNotificationResult) {
                    notification::create([
                        'pbn_user_id' => $customerUser->pbc_user_id,
                        'pbn_type' => 'Booking Cancelled',
                        'pbn_title' => $customerNotificationTitle,
                        'pbn_message' => $customerNotificationMessage,
                        'pbn_is_read' => 0,
                    ]);
                }
            }
        }

        $booking->pbb_status = $request->booking_status; // Assuming 3 is the status code for completed bookings
        $booking->save();

        $vendorPayout = vendorPayouts::firstOrCreate(
            ['pbvp_vendor_id' => $booking->pbb_vendor_id],
            ['pbvp_total_earned' => 0, 'pbvp_total_paid' => 0, 'pbvp_total_due' => 0]
        );

        Log::info('vendor payouts Response:', ['Response' => $vendorPayout]);

        $vendorPayout->increment('pbvp_total_earned', $vendor_amount);
        $vendorPayout->increment('pbvp_total_due', $vendor_amount);

        $payoutItem = vendorPayoutItems::create([
            'pbvpi_payout_id'   => $vendorPayout->pbvp_id,
            'pbvpi_booking_id'  => $getBooking->pbb_id,
            'pbvpi_payment_id'  => $payment->pbpt_id,
            'pbvpi_amount'      => $getBooking->pbb_total_amount,
            'pbvpi_platform_fee'=> $platform_fee,
            'pbvpi_vendor_amount'=> $vendor_amount,
            'pbvpi_status'      => '0'
        ]);
        Log::info('vendor payouts Response:', ['Response' => $payoutItem]);


        return response()->json([
            'status' => true,
            'message' => 'Booking status marked successfully',
        ], 200);
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

        if ($vendor->pbv_status != 2) {
            return response()->json(['message' => 'Your Profile is not verified'], 403);
        }

        if ($vendor->pbv_servicefor == null) {
            return response()->json(['message' => 'Your Profile is not verified'], 403);
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

            // Decode pbb_booking_details JSON
            $booking_details_json = json_decode($booking->pbb_booking_details, true);

            // Combine all key-value pairs into a readable string (if JSON exists)
            if (!empty($booking_details_json)) {
                $remarks = collect($booking_details_json)
                    ->map(function ($value, $key) {
                        return ucfirst($key) . ': ' . ($value ?? '-');
                    })
                    ->implode(', ');
            } else {
                $remarks = $booking->pbb_remarks ?? '-';
            }
            
            return [
                'pbb_id' => $booking->pbb_id,
                'pbb_promo_id' => $booking->pbb_promo_id,
                'pbb_booking_details' => $bookingDetails,
                'pbb_booking_date' => Carbon::parse($booking->pbb_booking_date)->format('Y-m-d'),
                'pbb_booking_duration' => $booking->pbb_booking_duration,
                'pbb_booking_start_time' => Carbon::parse($booking->pbb_booking_start_time)->format('H:i:s'),
                'pbb_booking_end_time' => Carbon::parse($booking->pbb_booking_end_time)->format('H:i:s'),
                'pbb_ref_no' => $booking->pbb_ref_no,
                'pbb_type' => $booking->pbb_type,
                'pbb_service_location' => $booking->pbb_service_location,
                'pbb_contact_no' => $booking->pbb_contact_no,
                'pbb_status' => $booking->pbb_status,
                'created_at' => Carbon::parse($booking->created_at)->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::parse($booking->updated_at)->format('Y-m-d H:i:s'),
                'deleted_at' => Carbon::parse($booking->deleted_at)->format('Y-m-d H:i:s'),
                'pbb_remarks' => $remarks,
                'pbbd_total_amount' => $booking->bookingDetails->sum('pbbd_amount'),
                'services' => $serviceNames,
            ];
        }); 
        Log::info('Get Bookings Response:', ['Response' => $generated_bookings]);
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
 *     tags={"Booking"},
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
            'pbb_booking_date' => Carbon::parse($bookings->pbb_booking_date)->format('Y-m-d'),
            'pbb_booking_duration' => $bookings->pbb_booking_duration,
            'pbb_booking_start_time' => Carbon::parse($bookings->pbb_booking_start_time)->format('H:i:s'),
            'pbb_booking_end_time' => Carbon::parse($bookings->pbb_booking_end_time)->format('H:i:s'),
            'pbb_ref_no' => $bookings->pbb_ref_no,
            'pbb_type' => $bookings->pbb_type,
            'pbb_service_location' => $bookings->pbb_service_location,
            'pbb_contact_no' => $bookings->pbb_contact_no,
            'pbb_status' => $bookings->pbb_status,
            'created_at' => Carbon::parse($bookings->created_at)->format('Y-m-d H:i:s'),
            'updated_at' => Carbon::parse($bookings->updated_at)->format('Y-m-d H:i:s'),
            'deleted_at' => Carbon::parse($bookings->deleted_at)->format('Y-m-d H:i:s'),
            'pbb_remarks' => $bookings->pbb_remarks,
            'pbbd_total_amount' => $bookings->bookingDetails->sum('pbbd_amount'),
            'pbb_vendor_id' => $bookings->pbb_vendor_id,
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
        Log::info('Get Booking Detailss Response:', ['Response' => $booking_details]);
        return response()->json([
            'status' => true,
            'message' => 'Booking Details retrieved successfully',
            'data' => $booking_details
        ], 200);
    }

    public function vendorPayouts(Request $request)
    {
        Log::info('Get Vendor Payouts Requests:', ['Requests' => $request->all()]);
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



    /**
 * @OA\Get(
 *     path="/api/bookings/payment-status",
 *     operationId="getBookingPaymentStatus",
 *     tags={"Booking"},
 *     summary="Get booking payment status",
 *     description="Returns the payment status of a booking using booking ID",
 *
 *     @OA\Parameter(
 *         name="booking_id",
 *         in="query",
 *         required=true,
 *         description="Booking ID",
 *         @OA\Schema(
 *             type="integer",
 *             example=8
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Payment status retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(
 *                     property="payment_status",
 *                     type="integer",
 *                     example=1,
 *                     description="0 = Pending / Failed, 1 = Success"
 *                 )
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Booking not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="No query results for model [Booking]")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=500,
 *         description="Server error"
 *     )
 * )
 */

    public function getBookingPaymentStatus(Request $request){
        $id = $request->input('booking_id');
        $paymentTransection = paymentTransection::where('pbpt_booking_id', $id)->first();

        if (!$paymentTransection) {
            return response()->json([
                'status' => false,
                'data' => [
                    'payment_status' => 0, // 0 = pending / not created
                    'message' => 'Payment not completed yet'
                ],
            ], 200);
        }

        $status_code = ($paymentTransection->pbpt_status ==  1) ? true : false;

        return response()->json([
            'status' => $status_code,
            'data' => ['payment_status' => $paymentTransection->pbpt_status],
        ], 200);
    }
}