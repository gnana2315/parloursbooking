<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\paymentTransection;
use App\Models\vendorPayouts;

class paymentsController extends Controller
{
    public function paymentTransectionList()
    {
        $user = auth()->user();
        $paymentTransections = paymentTransection::with(['vendor', 'customer', 'booking', 'payoutItems'])->where('pbpt_status', 1)->get();
        return view('pages.admin.payment.paymentTransections', compact('paymentTransections'));
    }

    public function payoutsList()
    {
        $user = auth()->user();
        $payouts = vendorPayouts::with('vendors')->get();
        return view('pages.admin.payment.payouts', compact('payouts'));
    }
}
