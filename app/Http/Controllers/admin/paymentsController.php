<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\paymentTransection;
use App\Models\vendorPayouts;
use App\Models\vendorPayoutHistory;
use App\Models\vendorPayoutItems;
use App\Models\vendors;

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
        $vendorName = $vendorPayoutItems->first()->vendor->pbv_business_name ?? 'Unknown Vendor';
        return view('pages.admin.payment.payoutMakeView', compact('vendorPayoutItems', 'vendorId', 'vendorName'));
    }

    public function processPayout(Request $request)
    {
        $user = auth()->user();

        // $request->validate(
        //     [
        //         'payout_item_ids' => 'required|array', // e.g., [1, 2, 3]
        //         'payout_item_ids.*' => 'integer|exists:vendor_payout_items,pbvpi_id',
        //         'paying_amount' => 'required|numeric|min:0',
        //         'payment_method' => 'required|string',
        //     ],
        //     [
        //         'payout_item_ids.required' => 'Please select at least one item for payout.',
        //         'payout_item_ids.array' => 'Invalid format for payout items.',
        //         'payout_item_ids.*.integer' => 'Each payout item ID must be an integer.',
        //         'payout_item_ids.*.exists' => 'One or more selected payout items do not exist.',
        //         'paying_amount.required' => 'Please enter the amount to be paid.',
        //         'paying_amount.numeric' => 'The paying amount must be a number.',
        //         'paying_amount.min' => 'The paying amount must be at least 0.',
        //         'payment_method.required' => 'Please select a payment method.',
        //         'payment_method.string' => 'Invalid format for payment method.',
        //     ]
        // )
        // ;
        $vendorId = $request->input('vendor_id');
        $selectedItems = explode(',', $request->selected_items); // This should be an array of item IDs
        $paymentMethod = $request->input('payment_method');
        $paymentReference = $request->input('payment_reference');
        $amountToPay = $request->input('total_amount');

        $payouts = vendorPayouts::where('pbvp_vendor_id', $vendorId)->first();

        // Validate the input
        if (!$selectedItems || !is_array($selectedItems) || count($selectedItems) === 0) {
            return response()->json(['status' => false, 'success' => false, 'message' => 'No items selected for payout.']);
        }

        if ($amountToPay > $payouts->pbvp_total_due) {
            return response()->json(['status' => false, 'success' => false, 'message' => 'Paying amount exceeds total due'], 422);
        }

        $payoutHistory = vendorPayoutHistory::create([
            'pbvph_vendor_id' => $vendorId,
            'pbvph_payout_id' => $payouts->pbvp_id,
            'pbvph_amount' => $amountToPay,
            'pbvph_payment_method' => $paymentMethod,
            'pbvph_reference' => 'PAYOUT_' . uniqid(),
            'pbvph_description' => $paymentReference,
            'pbvph_status' => '1'
        ]);

        if (!$payoutHistory) {
            return response()->json(['status' => false, 'success' => false, 'message' => 'Failed to create payout history.']);
        }

        // Update vendor payout totals
        $payouts->increment('pbvp_total_paid', $amountToPay);
        $payouts->decrement('pbvp_total_due', $amountToPay);

        // Mark related payout items as paid
        vendorPayoutItems::whereIn('pbvpi_id', $selectedItems)
            ->update(
                [
                    'pbvpi_status' => '1',
                    'updated_at' => now(),
                    'pbvpi_payout_history_id' => $payoutHistory->pbvph_id
                ]
            );

        return response()->json(['status' => true, 'success' => true, 'message' => 'Payout processed successfully.']);
    }

    public function viewPayoutReceipt($payoutHistoryId)
    {
        $payoutHistory = vendorPayoutHistory::with(
                            'vendors', 
                            'payout',
                            'payoutItems',
                            'payoutItems.booking',
                            'payoutItems.payment'
                        )->findOrFail($payoutHistoryId);
        // dd($payoutHistory); // Debugging line to check the data structure
        $pdf = Pdf::loadView('pages.admin.pdfs.payment.payoutReceiptPDF', compact('payoutHistory'));
        return $pdf->stream('payout_receipt_' . $payoutHistory->pbvph_reference . '.pdf');
    }
}
