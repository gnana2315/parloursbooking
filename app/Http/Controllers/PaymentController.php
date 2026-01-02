<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Models\bookingDetail;
use App\Models\paymentTransection;
use App\Models\vendorPayouts;
use App\Models\vendorPayoutItems;
use App\Models\vendorPayoutHistory;
use App\Models\User;
use App\Models\booking;
use App\Models\notification;

use App\Services\WebXPayService;
use App\Services\OneSignalService;
use App\Services\DialogESMSService;

class PaymentController extends Controller
{
    public function __construct(DialogESMSService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function start(Request $request){
        $publickey = config('webxpay.public_key');
        $checkout_url = config('webxpay.checkout_url');
        $enc_method = config('webxpay.enc_method');
        $process_currency = config('webxpay.currency');
        $secret_key = config('webxpay.secret_key');
        $cms = config('webxpay.cms');

        if (!$publickey || !$secret_key || !$checkout_url || !$enc_method || !$process_currency || !$cms) {
            throw new \Exception('WebXPay not configured');
        }

        $booking = booking::with(['customer', 'vendors'])
                    ->where('pbb_ref_no', $request->ref)->firstOrFail();

        $customer = $booking->customer;
        $vendor = $booking->vendors->first();

        $amount = number_format($booking->pbb_total_amount, 2, '.', '');
        $webxpayService = new WebXPayService($publickey, $secret_key);
        $payment = $webxpayService->generatePaymentString($booking->pbb_ref_no, $amount, $publickey);

        $custom_fields = base64_encode(
            $booking->pbb_ref_no . '|' .
            $booking->pbb_id . '|' .
            $vendor->pbv_id . '|' .
            $customer->pbc_id
        );
        
        return response()->make('
            <html>
            <head>
                <title>Redirecting to Payment...</title>
            </head>
            <body onload="document.forms[0].submit()">
                <form method="POST" action="'.$checkout_url.'">
                    <input type="hidden" name="secret_key" value="'.$secret_key.'">
                    <input type="hidden" name="payment" value="'.$payment.'">
                    <input type="hidden" name="process_currency" value="'.$process_currency.'">
                    <input type="hidden" name="enc_method" value="'.$enc_method.'">
                    <input type="hidden" name="cms" value="'.$cms.'">
                    <input type="hidden" name="custom_fields" value="'.$custom_fields.'">
                    <input type="hidden" name="first_name" value="'.$customer->pbc_first_name.'">
                    <input type="hidden" name="last_name" value="'.($customer->pbc_last_name ?? '').'">
                    <input type="hidden" name="email" value="'.($customer->pbc_email ?? '').'">
                    <input type="hidden" name="contact_number" value="'.$customer->pbc_contact_no.'">
                    <input type="hidden" name="address_line_one" value="'.($customer->pbc_address ?? '').'">
                    <input type="hidden" name="city" value="'.($customer->pbc_city ?? 'Colombo').'">
                    <input type="hidden" name="country" value="'.($customer->pbc_country ?? 'Sri Lanka').'">
                </form>
                <p>Redirecting to payment...</p>
            </body>
            </html>
        ', 200, ['Content-Type' => 'text/html']);
    }

    public function callback(Request $request, OneSignalService $oneSignalService)
    {
        try {
            // 1️⃣ Decode POST parameters
            $payment = base64_decode($request->input('payment'));
            $signature = base64_decode($request->input('signature'));
            $custom_fields = base64_decode($request->input('custom_fields'));

            if (!$payment || !$signature) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid payment response data'
                ], 400);
            }

            // 2️⃣ Load WebXPay public key
            $publicKey = config('webxpay.public_key');
            if (!$publicKey) {
                throw new \Exception('WebXPay public key not configured');
            }

            // Fix escaped newlines
            $publicKey = str_replace('\n', "\n", $publicKey);

            // 3️⃣ Verify signature
            $verified = openssl_public_decrypt(
                $signature,
                $decryptedSignature,
                $publicKey
            );

            if (!$verified || $decryptedSignature !== $payment) {
                return response()->json([
                    'status' => false,
                    'message' => 'Signature validation failed'
                ], 400);
            }

            // 4️⃣ Parse payment response
            // Format:
            // order_id|order_reference|date_time|gateway|status_code|comment
            $paymentData = explode('|', $payment);
            Log::info('Payment from Gateway:', ['Response' => $paymentData]);
            [
                $orderId,
                $orderReference,
                $transactionDate,
                $gateway,
                $statusCode,
                $comment
            ] = array_pad($paymentData, 6, null);

            Log::info('Payment Data:', ['Response' => $paymentData]);
            // 5️⃣ Decode custom fields
            // Format: ref_no|booking_id|vendor_id|customer_id
            $customData = explode('|', $custom_fields);
            [
                $bookingRefNo,
                $bookingId,
                $vendorId,
                $customerId
            ] = array_pad($customData, 4, null);
            
            Log::info('Payment Status Code:', ['Response' => $statusCode]);

            // 6️⃣ Handle payment status
            if ($statusCode === '15') { //Failed
                // $getBooking = booking::with(['bookingDetails'])
                //             ->where('pbb_id', $bookingId)->first();
                // if ($getBooking) {
                //     $getBooking->bookingDetails()->delete();
                //     $getBooking->delete();
                // }
                $getBooking = booking::with(['customer', 'vendors', 'bookingDetails'])
                            ->where('pbb_id', $bookingId)->first();

                $platform_fee_percentage = 10; // example: 10% commission
                $platform_fee = ($getBooking->pbb_total_amount * $platform_fee_percentage) / 100;
                $vendor_amount = $getBooking->pbb_total_amount - $platform_fee;

                $payment = paymentTransection::create([
                    'pbpt_transaction_id'   => uniqid('TXN_'), // unique transaction ID
                    'pbpt_booking_id'       => $getBooking->pbb_id,
                    'pbpt_vendor_id'        => $vendorId,
                    'pbpt_customer_id'      => $customerId,
                    'pbpt_payment_method'   => 'Online', // fallback
                    'pbpt_total_amount'     => $getBooking->pbb_total_amount,
                    'pbpt_discount_amount'  => 0, // you can add logic if promo applied
                    'pbpt_final_amount'     => $getBooking->pbb_total_amount,
                    'pbpt_platform_fee'     => $platform_fee,
                    'pbpt_vendor_amount'    => $vendor_amount,
                    'pbpt_payment_response' => json_encode($paymentData), // store gateway response if online
                    'pbpt_payment_ref_no'   => $orderReference,
                    'pbpt_description'      => 'Payment for booking #' . $getBooking->pbb_ref_no,
                    'pbpt_status'           => 0, // 1 = success, 0 = pending, etc.
                    'pbpt_remarks'          => 'Auto-generated payment record'
                ]);
                 $getBooking->update(['pbb_status' => 5]);
                
                $status = false;
            }else{
                // SUCCESS
                // Update booking/payment tables here
                // Example:
                $getBooking = booking::with(['customer', 'vendors', 'bookingDetails'])
                            ->where('pbb_id', $bookingId)->first();
                $customer = $getBooking->customer;
                $vendor = $getBooking->vendors->first();
                $bookingDetails = $getBooking->bookingDetails;
                $someoneDetails = json_decode($getBooking->someone_details, true);

                $getBooking->update(['pbb_status' => 1]);

                $notification_title = 'Booking Confirmed!';
                $notification_message = 'Booking added successfully!. Your booking reference no:'. $bookingRefNo;
                $booking_details_for_notification = [
                    'booking_ref_no' => $bookingRefNo,
                    'booking_date' => $getBooking->pbb_booking_date,
                    'booking_start_time' => $getBooking->pbb_booking_start_time,
                    'booking_end_time' => $getBooking->pbb_booking_end_time,
                    'total_amount' => $getBooking->pbb_total_amount,
                ];

                $vendors_user = User::where('pbu_vid', $vendorId)->first();

                $booking_notification = $oneSignalService->sendToUser(
                    [$vendors_user->pbu_id],
                    $notification_title,
                    $notification_message,
                    $booking_details_for_notification
                );

                Log::info('booking_notification Response:', ['Response' => $booking_notification]);
                if($booking_notification){
                    notification::create([
                        'pbn_user_id' => $customer->pbc_user_id,
                        'pbn_type' => 'specific',
                        'pbn_title' => $notification_title,
                        'pbn_message' => $notification_message,
                        'pbn_is_read' => 0,
                    ]);
                }

                $sms_customer_name = !empty($someoneDetails['name'])
                                    ? $someoneDetails['name']
                                    : $customer->pbc_first_name;
                $sms_vendor_name = $vendor->pbv_business_name;
                $sms_booking_date = $getBooking->pbb_booking_date->format('d M Y');
                $sms_booking_start_time = $getBooking->pbb_booking_start_time->format('H:i A');
                $sms_booking_end_time = $getBooking->pbb_booking_end_time->format('H:i A');
                $sms_total_amount = $getBooking->pbb_total_amount;
                $sms_booking_ref_no = $getBooking->pbb_ref_no;
                $sms_phone_no = !empty($someoneDetails['contact_no'])
                                ? $someoneDetails['contact_no']
                                : $customer->pbc_contact_no;

                $apiKey = config('dialogesms.api_key');
                $sender = config('dialogesms.sender');

                $message = "Dear Customer,\n".
                        "Your booking is confirmed on {$sms_booking_date} at {$sms_booking_start_time} | Ref: {$sms_booking_ref_no}. Please arrive 10 mins early.\n" .
                        "Thank you for choosing Parlours Booking!";

                // Store OTP to DB/Cache if needed here
                //$smsEnable = filter_var($request->header('SMS_ENABLE', true), FILTER_VALIDATE_BOOLEAN);
                //if($smsEnable){
                    $booking_sms_result = $this->smsService->sendMessage($apiKey, [$sms_phone_no], $message, $sender);       
                //}

                Log::info('booking_sms_result Response:', ['Response' => $booking_sms_result]);

                $platform_fee_percentage = 10; // example: 10% commission
                $platform_fee = ($getBooking->pbb_total_amount * $platform_fee_percentage) / 100;
                $vendor_amount = $getBooking->pbb_total_amount - $platform_fee;

                $payment = paymentTransection::create([
                    'pbpt_transaction_id'   => uniqid('TXN_'), // unique transaction ID
                    'pbpt_booking_id'       => $getBooking->pbb_id,
                    'pbpt_vendor_id'        => $vendorId,
                    'pbpt_customer_id'      => $customerId,
                    'pbpt_payment_method'   => 'Online', // fallback
                    'pbpt_total_amount'     => $getBooking->pbb_total_amount,
                    'pbpt_discount_amount'  => 0, // you can add logic if promo applied
                    'pbpt_final_amount'     => $getBooking->pbb_total_amount,
                    'pbpt_platform_fee'     => $platform_fee,
                    'pbpt_vendor_amount'    => $vendor_amount,
                    'pbpt_payment_response' => json_encode($paymentData), // store gateway response if online
                    'pbpt_payment_ref_no'   => $orderReference,
                    'pbpt_description'      => 'Payment for booking #' . $getBooking->pbb_ref_no,
                    'pbpt_status'           => 1, // 1 = success, 0 = pending, etc.
                    'pbpt_remarks'          => 'Auto-generated payment record'
                ]);
                Log::info('payment transection Response:', ['Response' => $payment]);
                $vendorPayout = vendorPayouts::firstOrCreate(
                    ['pbvp_vendor_id' => $vendorId],
                    ['pbvp_total_earned' => 0, 'pbvp_total_paid' => 0, 'pbvp_total_due' => 0]
                );

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

                $status = true;
            }

            // FAILED PAYMENT
            // return response()->json([
            //     'status' => false,
            //     'message' => 'Payment failed',
            //     'data' => [
            //         'status_code' => $statusCode,
            //         'comment' => $comment,
            //     ]
            // ], 400);


        } catch (\Throwable $e) {
            Log::error('WebXPay paymentResponse error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
                
            $status = false;

            // return response()->json([
            //     'status' => false,
            //     'message' => 'Payment response processing failed'
            // ], 500);
        }

        $redirectUrl = "https://api.parloursbooking.com/c/bookings/payment-status?booking_id={$bookingId}&payment_status=" . ($status ? 'success' : 'failed');
        return redirect()->away($redirectUrl, 303);
    }



    /**
 * @OA\Post(
 *     path="/api/bookings/payment-status",
 *     summary="Update booking payment status",
 *     description="Updates payment status of a booking based on payment result",
 *     operationId="paymentStatus",
 *     tags={"Payment"},
 *     security={{"bearerAuth":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"booking_id","payment_status"},
 *             @OA\Property(
 *                 property="booking_id",
 *                 type="integer",
 *                 example=123,
 *                 description="Booking ID"
 *             ),
 *             @OA\Property(
 *                 property="payment_status",
 *                 type="string",
 *                 example="success",
 *                 description="Payment status (success | failed)"
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Payment status updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="boolean", example=true),
 *             @OA\Property(
 *                 property="data",
 *                 type="object",
 *                 @OA\Property(property="payment_status", type="string", example="Success")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=404,
 *         description="Booking not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="No query results for model")
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized"
 *     )
 * )
 */

    public function paymentStatus(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|integer',
            'payment_status' => 'required|in:success,failed'
        ]);
        
        $id = $request->input('booking_id');
        $status = $request->input('payment_status');
        // $booking = booking::findOrFail($id);

        // $pay_status = ($status == 'success') ? '2' : '0';

        // $booking_update = $booking->update([
        //     'pbb_status' => $pay_status,
        // ]);

        if($status == 'success'){
            $stat = true;
            $payment_status = 'Success';
        }else{
            $stat = false;
            $payment_status = 'Failed';
        }

        return response()->json([
            'status' => $stat,
            'data' => ['payment_status' => $payment_status],
        ]);
    }
}
