<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\WebXPayService;

use App\Models\booking;

class PaymentController extends Controller
{
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
}
