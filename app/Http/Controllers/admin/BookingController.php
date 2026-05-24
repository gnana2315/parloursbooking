<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Exception;

use App\Models\booking;

class BookingController extends Controller
{
    public function index()
    {
        return view('pages.admin.bookings.index');
    }

    public function list(Request $request)
    {
        if ($request->ajax()) {
            $bookings = booking::with(['customer', 'vendors']);
            
            if ($request->has('status') && $request->status !== '' && $request->status !== null) {
                $bookings->where('pbb_status', $request->status);
            }

            return DataTables::eloquent($bookings)
                // Add custom columns
                ->addColumn('customer_name', function($booking) {
                    return $booking->customer->pbc_name ?? 'N/A';
                })
                ->addColumn('vendor_name', function($booking) {                    
                    return $booking->vendors->pbv_business_name;
                })
                ->addColumn('booking_date', function($booking) {
                    return Carbon::parse($booking->pbb_booking_date)->format('d-M-y');
                })
                ->addColumn('total_amount', function($booking) {
                    return 'Rs. ' . number_format($booking->pbb_total_amount, 2);
                })
                ->addColumn('status', function($booking) {
                    $statuses = [
                        0 => ['class' => 'warning', 'text' => 'Cancelled By Admin'],
                        1 => ['class' => 'info', 'text' => 'Upcoming'],
                        2 => ['class' => 'success', 'text' => 'Completed'],
                        3 => ['class' => 'danger', 'text' => 'Payment Pending'],
                        4 => ['class' => 'secondary', 'text' => 'DNA'],
                        5 => ['class' => 'dark', 'text' => 'Payment Failure'],
                    ];
                    $status = $statuses[$booking->pbb_status] ?? ['class' => 'dark', 'text' => 'Unknown'];
                    return '<span class="badge badge-' . $status['class'] . '">' . $status['text'] . '</span>';
                })
                ->addColumn('actions', function($booking) {
                    $buttons = '<div class="btn-group" role="group">';
                    $buttons .= '                        
                        <button type="button" class="btn btn-primary btn-sm" id="showBookingBtn" data-id="' . $booking->pbb_id . '" title="View">
                            <i class="fas fa-eye"></i>
                        </button>';
                    // $buttons .= '
                    //     <button type="button" class="btn btn-info btn-sm" onclick="editBooking(' . $booking->pbb_id . ')" title="Edit">
                    //         <i class="fas fa-edit"></i>
                    //     </button>
                    // ';
                    // $buttons .= '
                    //     <button type="button" class="btn btn-success btn-sm" onclick="updateStatus(' . $booking->pbb_id . ')" title="Update Status">
                    //         <i class="fas fa-sync-alt"></i>
                    //     </button>
                    // ';
                    // $buttons .= '
                    //     <button type="button" class="btn btn-danger btn-sm" onclick="deleteBooking(' . $booking->pbb_id . ')" title="Delete">
                    //         <i class="fas fa-trash"></i>
                    //     </button>
                    // ';
                    // $buttons .= '
                    //     <a href="" target="_blank" class="btn btn-secondary btn-sm">
                    //         <i class="fas fa-file-invoice"></i> Invoice
                    //     </a>
                    // ';
                    $buttons .= '</div>';
                    return $buttons;
                })
                
                ->rawColumns(['status', 'actions'])
                ->setRowId('id')
                ->setRowClass(function($booking) {
                    return $booking->pbb_status == 3 ? 'bg-danger-light' : '';
                })
                ->setRowData([
                    'data-id' => function($booking) {
                        return $booking->pbb_id;
                    }
                ])
                ->setRowAttr([
                    'data-created' => function($booking) {
                        return $booking->created_at;
                    }
                ])
                ->with('total_records', booking::count())
                ->with('total_amount_sum', booking::sum('pbb_total_amount'))
                ->make(true);
        }
        
        // Only ONE return statement for the view
        return view('pages.admin.bookings.list');
    }

    public function getBookingDetails($booking_id)
    {
        try{
            $booking = booking::with(['customer', 'vendors', 'bookingDetails', 'bookingDetails.services', 'ratings', 'paymentTransections', 'promoCode'])->where('pbb_id', $booking_id)->firstOrFail();
            if(!$booking){
                return response()->json(['error' => 'Booking not found'], 404);
            }

            return view('pages.admin.bookings.booking_details', compact('booking'))->render();
        } catch (Exception $e) {
            return response()->json(['error' => 'An error occurred while fetching booking details: ' . $e->getMessage()], 500);
        }
    }
}
