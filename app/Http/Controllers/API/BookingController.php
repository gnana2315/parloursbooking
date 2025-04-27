<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Booking;
use App\Models\BookingDetails;
use App\Models\vendorStandardAvailability;
use App\Models\vendorSpecialCloses;
use App\Models\vendorServices;
use Validator;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/getBookingSlots",
     *      operationId="getBookingSlots",
     *      tags={"Bookings"},
     *      security={{"bearerAuth": {}}},
     *      summary="Booking Slots",
     *      description="Booking slots are generated according to selected services duration, data and previous booking. The service duration will be the total duration of selected services",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"vendor_id","customer_id", "booking_date", "service_total_duration"},
     *              @OA\Property(property="vendor_id", type="number", example="1"),
     *              @OA\Property(property="customer_id", type="number", example="1"),
     *              @OA\Property(property="booking_date", type="date", example="2025-04-29"),
     *              @OA\Property(property="service_total_duration", type="time", example="02:00:00")
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
    public function getBookingSlots(Request $request){
        $user = auth()->user();        

        $request->validate(
            [
                'vendor_id' => 'required',
                'customer_id' => 'required',
                'booking_date' => 'required',
                'service_total_duration' => 'required|date_format:H:i:s',
            ],
            [
                'vendor_id.required' => 'Vendor ID is required',
                'customer_id.required' => 'Customer ID is required',
                'booking_date.required' => 'Booking date is required',
                'service_total_duration.required' => 'Service duration is required',
                
            ]
        );
        
        $availableSlots = [];
        $finalSlots = [];

        $selected_services_duration = Carbon::createFromFormat('H:i:s', $request->service_total_duration);
        $selected_services_duration_Minutes = $selected_services_duration->hour * 60 + $selected_services_duration->minute;

        $getStandardOpenTime = vendorStandardAvailability::where('pbvsa_vendor_id', $request->vendor_id)
            ->where('pbvsa_day', date('l', strtotime($request->booking_date)))
            ->first();
        
        $currentStart = Carbon::createFromTimeString($getStandardOpenTime->pbvsa_start_time);
        
        $previousBookings = booking::where('pbb_vendor_id', $request->vendor_id)
            ->where('pbb_booking_date', $request->booking_date)
            ->where('pbb_status', '=', '1')
            ->get();

        if ($previousBookings->isEmpty()) {
            
            $start = Carbon::createFromTimeString($getStandardOpenTime->pbvsa_start_time);
            $end = Carbon::createFromTimeString($getStandardOpenTime->pbvsa_end_time);
            
            $slots = [];

            while ($start->lt($end)) {
                $slotStart = $start->format('H:i:s');
                $slotEnd = $start->copy()->addMinutes($selected_services_duration_Minutes)->format('H:i:s');

                // Only add if the end time is not past closing
                if (Carbon::createFromTimeString($slotEnd)->lte(Carbon::createFromTimeString($getStandardOpenTime->pbvsa_end_time))) {
                    $slots[] = [
                        'start' => $slotStart,
                        'end' => $slotEnd
                    ];
                }

                $start->addMinutes($selected_services_duration_Minutes);
            }

            return response()->json([
                'status' => true,
                'message' => 'Available slots',
                'data' => $slots
            ], 200);
        }else{
            $sortedBookings = collect($previousBookings)->sortBy('pbb_booking_start_time')->values();

            foreach ($sortedBookings as $booking) {
                $bookingStart = Carbon::createFromTimeString($booking['pbb_booking_start_time']);
                $bookingEnd = Carbon::createFromTimeString($booking['pbb_booking_end_time']);
            
                // If there is a gap between currentStart and bookingStart
                if ($currentStart->lt($bookingStart)) {
                    $availableSlots[] = [
                        'start' => $currentStart->format('H:i:s'),
                        'end' => $bookingStart->format('H:i:s'),
                    ];
                }
            
                // Move current start to after this booking
                if ($currentStart->lt($bookingEnd)) {
                    $currentStart = $bookingEnd;
                }
            }

            // After last booking, if there is still time left before close time
            $shopClose = Carbon::createFromTimeString($getStandardOpenTime->pbvsa_end_time);

            if ($currentStart->lt($shopClose)) {
                $availableSlots[] = [
                    'start' => $currentStart->format('H:i:s'),
                    'end' => $shopClose->format('H:i:s'),
                ];
            }

            foreach($availableSlots as $slot){            
                // Convert start and end times into Carbon instances
                $startTime = Carbon::createFromFormat('H:i:s', $slot['start']);
                $endTime = Carbon::createFromFormat('H:i:s', $slot['end']);

                // Break the time into 2-hour slots
                while ($startTime < $endTime) {
                    // Create the new slot
                    $slotEndTime = $startTime->copy()->addMinutes($selected_services_duration_Minutes);

                    // Check if the slot goes beyond the original end time
                    if ($slotEndTime > $endTime) {
                        $slotEndTime = $endTime;
                    }

                    //get slots difference in mintues
                    $slot_difference = $slotEndTime->diffInMinutes($startTime);

                    if($slot_difference == $selected_services_duration_Minutes){                    
                        // Store the slot as an array of start and end times
                        $finalSlots[] = [
                            'start' => $startTime->format('H:i:s'),
                            'end' => $slotEndTime->format('H:i:s')
                        ];
                    }

                    // Move the start time by 2 hours for the next slot
                    $startTime->addMinutes($selected_services_duration_Minutes);
                }
            }

            return response()->json([
                'status' => true,
                'message' => 'Available slots',
                'data' => $finalSlots
            ], 200);
        }
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
     *              required={"vendor_id","customer_id", "promocode_id", "booking_details", "booking_date", "booking_duration", "booking_start_time", "booking_end_time", "service_location"},
     *              @OA\Property(property="vendor_id", type="number", example="1"),
     *              @OA\Property(property="customer_id", type="number", example="1"),
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

    public function addBooking(Request $request){
        $user = auth()->user();

        $request->validate(
            [
                'vendor_id' => 'required',
                'customer_id' => 'required',
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
                'customer_id.required' => 'Customer ID is required',
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

        $vendor = Vendor::find($request->vendor_id);
        if (!$vendor) {
            return response()->json([
                'status' => false,
                'message' => 'Vendor not found',
            ], 404);
        }
        $customer = Customer::find($request->customer_id);
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
            'pbb_customer_id' => $request->customer_id,
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
                $service = vendorServices::where('pbvs_id', $value['service_id'])->first();
                if($service){
                    $total_amount += $service->pbvs_price;
                    BookingDetails::create([
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