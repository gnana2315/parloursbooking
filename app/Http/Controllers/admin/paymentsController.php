<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\paymentTransection;
use App\Models\vendorPayouts;
use App\Models\vendorPayoutHistory;
use App\Models\vendorPayoutItems;

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

    public function payoutsHistoryByVendor($vendorId)
    {
        $payoutHistory = vendorPayoutHistory::with('payout', 'vendors')->where('pbvph_vendor_id', $vendorId)->get();
        return view('pages.admin.payment.payoutHistory', compact('payoutHistory'));
    }

    public function makePayoutView($vendorId)
    {
        $vendorPayoutItems = vendorPayoutItems::with('vendor', 'booking', 'payment')
                            ->where('pbvpi_vendor_id', $vendorId)
                            ->where('pbvpi_status', 0)
                            ->get();
        return view('pages.admin.payment.payoutMakeView', compact('vendorPayoutItems'));
    }
}
