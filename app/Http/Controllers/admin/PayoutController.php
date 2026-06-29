<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

use App\Models\vendorPayouts;
use App\Models\vendorPayoutHistory;
use App\Models\vendorPayoutItems;
use App\Models\vendors;
use App\Models\payoutsBatch;
use App\Models\payoutsBatchDetails;
use App\Exports\BatchPayoutExport;

class PayoutController extends Controller
{
    private vendorPayouts $vendorPayouts;
    private vendorPayoutHistory $vendorPayoutHistory;
    private vendorPayoutItems $vendorPayoutItems;
    private vendors $vendors;
    private payoutsBatch $payoutsBatch;
    private payoutsBatchDetails $payoutsBatchDetails;

    public function __construct(
        vendorPayouts $vendorPayouts,
        vendorPayoutHistory $vendorPayoutHistory,
        vendorPayoutItems $vendorPayoutItems,
        vendors $vendors,
        payoutsBatch $payoutsBatch,
        payoutsBatchDetails $payoutsBatchDetails
    ) {
        $this->vendorPayouts = $vendorPayouts;
        $this->vendorPayoutHistory = $vendorPayoutHistory;
        $this->vendorPayoutItems = $vendorPayoutItems;
        $this->vendors = $vendors;
        $this->payoutsBatch = $payoutsBatch;
        $this->payoutsBatchDetails = $payoutsBatchDetails;
    }

    public function index()
    {
        return view('pages.admin.payment.payouts.index');
    }

    public function list(Request $request)
    {
        if($request->all()){
            $payoutItems = $this->vendorPayoutItems->with('vendor', 'booking', 'payment')->where('pbvpi_status', 0);

            // FIXED: Use the correct parameter name from the DataTable
            if($request->has('vendor_name') && $request->vendor_name !== '' && $request->vendor_name !== null){
                $payoutItems->whereHas('vendor', function($q) use ($request) {
                    $q->where('pbv_business_name', 'like', '%' . $request->vendor_name . '%');
                })
                ->orWhereHas('booking', function($q) use ($request) {
                    $q->where('pbb_ref_no', 'like', '%' . $request->vendor_name . '%');
                });
            }

            // FIXED: Use the correct parameter name from the DataTable
            if ($request->has('payout_date_range') && $request->payout_date_range !== '' && $request->payout_date_range !== null) {
                $payoutDateRange = explode(' - ', $request->payout_date_range);
                
                if (count($payoutDateRange) == 2) {
                    $startDate = Carbon::createFromFormat('Y-m-d', trim($payoutDateRange[0]))->startOfDay();
                    $endDate = Carbon::createFromFormat('Y-m-d', trim($payoutDateRange[1]))->endOfDay();
                    $payoutItems->whereHas('booking', function($q) use ($startDate, $endDate) {
                        $q->whereBetween('pbb_booking_date', [$startDate, $endDate]);
                    });
                } else {
                    $payoutItems->whereHas('booking', function($q) use ($request) {
                        $q->whereDate('pbb_booking_date', Carbon::createFromFormat('Y-m-d', trim($request->payout_date_range))->format('Y-m-d'));
                    });
                }
            }

            return DataTables::eloquent($payoutItems)
                ->addColumn('select_all', function($payoutItem) {
                    if($payoutItem->pbvpi_batch_id == null) {
                        return '<input type="checkbox" class="payout-item-checkbox" data-amount="' . $payoutItem->pbvpi_vendor_amount . '" data-id="' . $payoutItem->pbvpi_id . '">';
                    } else {
                        return '';
                    }
                    // return '<input type="checkbox" class="payout-item-checkbox" data-amount="' . $payoutItem->pbvpi_vendor_amount . '" data-id="' . $payoutItem->pbvpi_id . '">';
                })
                ->addColumn('booking_ref_no', function($payoutItem) {                    
                    return $payoutItem->booking->pbb_ref_no ?? 'N/A';
                })
                ->addColumn('booking_date', function($payoutItem) {
                    return Carbon::parse($payoutItem->booking->pbb_booking_date)->format('d-M-y');
                })
                ->addColumn('booking_status_updated_date', function($payoutItem) {
                    return Carbon::parse($payoutItem->booking->pbb_status_updated_at)->format('d-M-y');
                })
                ->addColumn('vendor_name', function($payoutItem) {
                    return $payoutItem->vendor->pbv_business_name ?? 'N/A';
                })
                ->addColumn('payment_reference', function($payoutItem) {
                    return $payoutItem->payment->pbpt_ref_no ?? 'N/A';
                })
                ->addColumn('vendor_amount', function($payoutItem) {
                    return number_format($payoutItem->pbvpi_vendor_amount, 2);
                })
                ->addColumn('status', function($payoutItem) {
                    if ($payoutItem->pbvpi_status == 0 && !empty($payoutItem->pbvpi_batch_id)) {
                        return '<span class="badge badge-primary">Processing</span>';
                    }

                    $statuses = [
                        0 => ['class' => 'warning', 'text' => 'Unpaid'],
                        1 => ['class' => 'info', 'text' => 'Paid'],
                        2 => ['class' => 'success', 'text' => 'Failed']
                    ];

                    $status = $statuses[$payoutItem->pbvpi_status] ?? ['class' => 'dark', 'text' => 'Unknown'];
                    return '<span class="badge badge-' . $status['class'] . '">' . $status['text'] . '</span>';
                })
                ->rawColumns(['select_all', 'status'])
                ->setRowId('pbvpi_id')
                ->setRowClass(function($payoutItem) {
                    return $payoutItem->pbvpi_status == 3 ? 'bg-danger-light' : '';
                })
                ->setRowData([
                    'data-id' => function($payoutItem) {
                        return $payoutItem->pbvpi_id;
                    }
                ])
                ->setRowAttr([
                    'data-created' => function($payoutItem) {
                        return $payoutItem->created_at;
                    }
                ])
                ->with('total_records', $payoutItems->count())
                ->with('total_amount_sum', $payoutItems->sum('pbvpi_vendor_amount'))
                ->make(true);
        
        }
        return view('pages.admin.payment.payouts.list');
    }

    public function createPayoutsBatch(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'selected_items' => 'required|string',
                'total_amount' => 'required|numeric|min:0',
                'batch_name' => 'required|string|max:255',
                'batch_valid_date' => 'required|date|after:today',
                'notes' => 'nullable|string|max:1000'
            ]);

            $user = auth()->user();
            $selectedItems = explode(',', $request->selected_items);
            $totalAmount = $request->input('total_amount');
            $batchName = $request->input('batch_name');
            $batchValidDate = $request->input('batch_valid_date');
            $notes = $request->input('notes');

            // Validate selected items
            if (empty($selectedItems) || !is_array($selectedItems)) {
                return response()->json([
                    'status' => false,
                    'success' => false,
                    'message' => 'No items selected for payout.'
                ], 400);
            }

            // Verify all items exist and are valid
            $validItems = vendorPayoutItems::whereIn('pbvpi_id', $selectedItems)
                ->where('pbvpi_status', 0) // Only unpaid items
                ->get();

            if ($validItems->count() !== count($selectedItems)) {
                return response()->json([
                    'status' => false,
                    'success' => false,
                    'message' => 'Some selected items are invalid or already processed.'
                ], 400);
            }

            // Use database transaction for data integrity
            DB::beginTransaction();

            try {
                // Generate batch number
                $batchNumber = $this->generateBatchNumber();

                // Create the batch
                $payoutsBatch = payoutsBatch::create([
                    'pbpb_batch_no' => $batchNumber,
                    'pbpb_batch_name' => $batchName,
                    'pbpb_total_amount' => $totalAmount,
                    'pbpb_total_payouts' => count($selectedItems),
                    'pbpb_batch_valid_date' => Carbon::parse($batchValidDate)->format('Y-m-d'),
                    'pbpb_notes' => $notes,
                    'pbpb_status' => 0, // 0 = Pending
                    'pbpb_created_by' => $user->id,
                    'pbpb_created_at' => now(),
                ]);

                if (!$payoutsBatch) {
                    throw new \Exception('Failed to create batch record');
                }

                // Create batch details for each selected item
                $batchDetails = [];
                foreach ($selectedItems as $itemId) {
                    $batchDetails[] = [
                        'pbpbi_btach_id' => $payoutsBatch->pbpb_id,
                        'pbpbi_vendor_payout_item_id' => $itemId,
                        'pbpbi_status' => 0, // 0 = Pending
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // Bulk insert for better performance
                payoutsBatchDetails::insert($batchDetails);

                // Update the selected payout items with batch ID
                vendorPayoutItems::whereIn('pbvpi_id', $selectedItems)
                    ->update([
                        'pbvpi_batch_id' => $payoutsBatch->pbpb_id,
                        'updated_at' => now(),
                    ]);

                // Log the activity
                Log::info('Payout batch created', [
                    'batch_id' => $payoutsBatch->pbpb_id,
                    'batch_no' => $batchNumber,
                    'created_by' => $user->id,
                    'total_items' => count($selectedItems),
                    'total_amount' => $totalAmount
                ]);

                // Commit the transaction - ALL OR NOTHING
                DB::commit();

                return response()->json([
                    'status' => true,
                    'success' => true,
                    'message' => "Payouts batch #{$batchNumber} created successfully.",
                    'data' => [
                        'batch_id' => $payoutsBatch->pbpb_id,
                        'batch_no' => $batchNumber,
                        'total_items' => count($selectedItems),
                        'total_amount' => $totalAmount
                    ]
                ]);

            } catch (\Exception $e) {
                // Rollback the transaction on error
                DB::rollBack();
                Log::error('Batch creation failed: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'selected_items' => $selectedItems
                ]);
                
                throw $e; // Re-throw to be caught by outer catch
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Failed to create payouts batch: ' . $e->getMessage()
            ], 500);
        }
    }

    public function payoutBatches()
    {
        return view('pages.admin.payment.batches.index');
    }

    public function payoutBatchesList(Request $request)
    {
        if($request->all()){
            $payoutBatches = $this->payoutsBatch->query();

            if($request->has('search') && $request->search !== '' && $request->search !== null){
                $payoutBatches->where('pbpb_batch_no', 'like', '%' . $request->search . '%')
                    ->orWhere('pbpb_batch_name', 'like', '%' . $request->search . '%');
            }

            if ($request->has('batches_date_range') && $request->batches_date_range !== '' && $request->batches_date_range !== null) {
                $dateRange = explode(' - ', $request->batches_date_range);
                
                if (count($dateRange) == 2) {
                    $startDate = Carbon::createFromFormat('Y-m-d', trim($dateRange[0]))->startOfDay();
                    $endDate = Carbon::createFromFormat('Y-m-d', trim($dateRange[1]))->endOfDay();
                    $payoutBatches->whereBetween('created_at', [$startDate, $endDate]);
                } else {
                    $payoutBatches->whereDate('created_at', Carbon::createFromFormat('Y-m-d', trim($request->batches_date_range))->format('Y-m-d'));
                }
            }

            return DataTables::eloquent($payoutBatches)
                ->addColumn('batch_no', function($batch) {
                    return $batch->pbpb_batch_no;
                })
                ->addColumn('batch_name', function($batch) {
                    return $batch->pbpb_batch_name ?? 'N/A';
                })
                ->addColumn('amount', function($batch) {
                    return number_format($batch->pbpb_total_amount, 2);
                })
                ->addColumn('batch_valid_date', function($batch) {
                    return Carbon::parse($batch->pbpb_batch_valid_date)->format('d-M-y');
                })
                ->addColumn('notes', function($batch) {
                    return $batch->pbpb_notes ?? 'N/A';
                })
                ->addColumn('paid_date', function($batch) {
                    return Carbon::parse($batch->pbpb_payout_date)->format('d-M-y');
                })
                ->addColumn('paid_ref_no', function($batch) {
                    return $batch->pbpb_payout_ref_no ?? 'N/A';
                })
                ->addColumn('paid_by', function($batch) {
                    return $batch->pbpb_paid_by ?? 'N/A';
                })
                ->addColumn('remarks', function($batch) {
                    return $batch->pbpb_remarks ?? 'N/A';
                })
                ->addColumn('status', function($batch) {
                    $statuses = [
                        0 => ['class' => 'warning', 'text' => 'Pending'],
                        1 => ['class' => 'success', 'text' => 'Paid'],
                        2 => ['class' => 'danger', 'text' => 'Failed']
                    ];

                    $status = $statuses[$batch->pbpb_status] ?? ['class' => 'dark', 'text' => 'Unknown'];
                    return '<span class="badge badge-' . $status['class'] . '">' . $status['text'] . '</span>';
                })
                ->addColumn('action', function($batch) {
                    $button = '
                        <div class="btn-group" role="group" style="min-width: 120px;">
                            <button class="btn btn-sm btn-primary view-details-btn" data-id="' . $batch->pbpb_id . '" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>';

                    if($batch->pbpb_status == 0) {
                        $button .= '
                            <button class="btn btn-sm btn-warning mark-batch-btn" data-id="' . $batch->pbpb_id . '" title="Mark as Paid">
                                <i class="fas fa-check"></i>
                            </button>';
                    } else {
                        $button .= '
                            <button class="btn btn-sm btn-info view-proof-btn" data-id="' . $batch->pbpb_id . '" title="View Payment Proof">
                                <i class="fas fa-file-invoice"></i>
                            </button>';
                        $button .= '
                            <button class="btn btn-sm btn-secondary view-payment-receipt-btn" data-id="' . $batch->pbpb_id . '" title="View Payment Receipt">                                
                                <i class="fas fa-receipt"></i>
                            </button>';
                    }

                    $button .= '
                        </div>';

                    return $button;
                })
                ->rawColumns(['status', 'action'])
                ->setRowId('pbpb_id')
                ->setRowClass(function($batch) {
                    return $batch->pbpb_status == 2 ? 'bg-danger-light' : '';
                })
                ->setRowData([
                    'data-id' => function($batch) {
                        return $batch->pbpb_id;
                    }
                ])
                ->setRowAttr([
                    'data-created' => function($batch) {
                        return $batch->created_at;
                    }
                ])
                ->with('total_records', $payoutBatches->count())
                ->with('total_amount_sum', $payoutBatches->sum('pbpb_total_amount'))
                ->make(true);
        }
        return view('pages.admin.payment.batches.list');
    }

    public function getBatchDetails(Request $request)
    {
        try {
            $batchId = $request->batchId;
            
            // Get batch with relationships
            $batch = payoutsBatch::findOrFail($batchId);
            
            // Get batch details with vendor, booking, and payment info
            $batchDetails = payoutsBatchDetails::with([
                'vendorPayoutItem.vendor',
                'vendorPayoutItem.vendor.bankInfo',
                'vendorPayoutItem.vendor.bankInfo.bank',
                'vendorPayoutItem.booking',
                'vendorPayoutItem.payment'
            ])
            ->where('pbpbi_btach_id', $batchId)
            ->get()
            ->map(function($detail) {
                // Map the data for easier access
                $payoutItem = $detail->vendorPayoutItem;
                $vendor = $payoutItem->vendor ?? null;
                $bankInfo = $vendor ? $vendor->bankInfo->first() : null;
                
                $bankName = null;
                if ($bankInfo && $bankInfo->bank) {
                    $bankName = $bankInfo->bank->pbb_name ?? null;
                }
                return (object) [
                    'pbvpi_id' => $payoutItem->pbvpi_id ?? null,
                    'pbvpi_vendor_amount' => $payoutItem->pbvpi_vendor_amount ?? 0,
                    'vendor' => $vendor,
                    'bankinfo' => $bankInfo,
                    'bankName' => $bankName,
                    'booking' => $payoutItem->booking ?? null,
                    'payment' => $payoutItem->payment ?? null,
                ];
            });

            return view('pages.admin.payment.batches.details', compact('batch', 'batchDetails'));
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load batch details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download Batch Payout PDF
     */
    public function downloadBatchPdf(Request $request)
    {
        try {
            $batchId = $request->batchId;
            
            // Get batch with details
            $batch = payoutsBatch::findOrFail($batchId);
            
            // Get detailed payout information
            $batchDetails = $this->getBatchPayoutDetails($batchId);
            
            // Generate PDF
            $pdf = Pdf::loadView('pages.admin.pdfs.payment.batch-payout', compact('batch', 'batchDetails'));
            $pdf->setPaper('a4', 'landscape');
            
            $filename = 'Batch_' . $batch->pbpb_batch_no . '_Payout_Details.pdf';
            
            // Return PDF as download with proper headers
            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length' => strlen($pdf->output()),
                'Cache-Control' => 'must-revalidate',
                'Pragma' => 'public',
                'Expires' => '0'
            ]);
            
        } catch (\Exception $e) {
            Log::error('PDF Generation failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download Batch Payout Excel
     */
    public function downloadBatchExcel(Request $request)
    {
        try {
            $batchId = $request->batchId;
            
            $batch = payoutsBatch::findOrFail($batchId);
            
            return Excel::download(
                new BatchPayoutExport($batchId),
                'Batch_' . $batch->pbpb_batch_no . '_Payout_Details.xlsx'
            );
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate Excel: ' . $e->getMessage());
        }
    }

    /**
     * Get batch data for marking
     */
    public function getBatchData(Request $request)
    {
        try {
            $batchId = $request->batchId;
            $batch = payoutsBatch::findOrFail($batchId);
            
            return response()->json([
                'batch_no' => $batch->pbpb_batch_no,
                'batch_name' => $batch->pbpb_batch_name,
                'total_amount' => number_format($batch->pbpb_total_amount, 2)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load batch data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark batch as paid
     */
    public function markBatchAsPaid(Request $request)
    {
        try {
            $request->validate([
                'batch_id' => 'required|exists:payouts_batch,pbpb_id',
                'paid_date' => 'required|date',
                'paid_ref_no' => 'required|string|max:255',
                'paid_by' => 'required|string|max:255',
                'payment_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
                'remarks' => 'nullable|string|max:1000'
            ]);
            
            $batchId = $request->batch_id;
            $batch = payoutsBatch::findOrFail($batchId);
            
            // Check if batch is already paid
            if ($batch->pbpb_status == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'This batch has already been marked as paid.'
                ], 400);
            }
            
            if ($request->hasFile('payment_proof')) {
                $file = $request->file('payment_proof');
                
                // Get vendor IDs from this batch
                $vendorIds = payoutsBatchDetails::where('pbpbi_btach_id', $batchId)
                    ->with('vendorPayoutItem.vendor')
                    ->get()
                    ->pluck('vendorPayoutItem.vendor.pbv_id')
                    ->unique()
                    ->toArray();
                
                if (count($vendorIds) === 1) {
                    $vendorId = $vendorIds[0];
                } else {
                    // For multiple vendors, use 'batch_' prefix
                    $vendorId = 'batch_' . $batchId;
                }
                
                // Generate unique filename
                $fileName = time() . '_' . $file->getClientOriginalName();
                
                // Create custom path: payouts/{vendor_id}/{batch_id}/
                $customPath = 'payouts/' . $vendorId . '/' . $batchId;
                
                // Store the file
                //$filePath = $file->storeAs($customPath, $fileName, 'public');
                // Option 3: Using Storage facade to create directories
                Storage::disk('public')->makeDirectory($customPath);
                $filePath = $file->storeAs($customPath, $fileName, 'public');
                
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment proof file is required.'
                ], 400);
            }
            
            DB::beginTransaction();
            
            try {
                // Update batch
                $batch->pbpb_status = 1; // Paid
                $batch->pbpb_payout_date = Carbon::parse($request->paid_date)->format('Y-m-d H:i:s');
                $batch->pbpb_paid_ref_no = $request->paid_ref_no;
                $batch->pbpb_paid_by = $request->paid_by;
                $batch->pbpb_paid_slip_url = $filePath;
                $batch->pbpb_remarks = $request->remarks ?? $batch->pbpb_remarks;
                $batch->pbpb_updated_by = auth()->id();
                $batch->updated_at = now();
                $batch->save();
                
                // Update all vendor payout items in this batch
                vendorPayoutItems::where('pbvpi_batch_id', $batchId)
                    ->update([
                        'pbvpi_status' => 1, // Paid
                        'updated_at' => now()
                    ]);
                
                // Create history record (optional)
                // payoutBatchHistory::create([...]);
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Batch #' . $batch->pbpb_batch_no . ' marked as paid successfully.'
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Mark batch as paid failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark batch as paid: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View payment proof
     */
    public function viewPaymentProof(Request $request)
    {
        try {
            $batchId = $request->batchId;
            $batch = payoutsBatch::findOrFail($batchId);
            
            if (empty($batch->pbpb_paid_slip_url)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No payment proof found for this batch.'
                ]);
            }
            
            // Check if file exists
            if (!Storage::disk('public')->exists($batch->pbpb_paid_slip_url)) {
                Log::error('File not found: ' . $batch->pbpb_paid_slip_url);
                return response()->json([
                    'success' => false,
                    'message' => 'Payment proof file not found.'
                ]);
            }
            
            // Get file URL
            $fileUrl = Storage::url($batch->pbpb_paid_slip_url);
            $fileName = basename($batch->pbpb_paid_slip_url);
            $fileSize = Storage::disk('public')->size($batch->pbpb_paid_slip_url);
            $fileExtension = strtolower(pathinfo($batch->pbpb_paid_slip_url, PATHINFO_EXTENSION));
            
            if (in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $fileType = 'image';
            } elseif (strtolower($fileExtension) == 'pdf') {
                $fileType = 'pdf';
            }
            
            return response()->json([
                'success' => true,
                'file_url' => $fileUrl,
                'file_name' => $fileName,
                'file_type' => $fileType
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load payment proof: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View payment receipt
     */
    public function viewPaymentReceipt(Request $request)
    {
        try {
            $batchId = $request->batchId;
            $batch = payoutsBatch::findOrFail($batchId);
            
            // Get vendors in this batch
            $vendors = payoutsBatchDetails::with([
                'vendorPayoutItem.vendor'
            ])
            ->where('pbpbi_btach_id', $batchId)
            ->get()
            ->map(function($detail) {
                $payoutItem = $detail->vendorPayoutItem;
                return [
                    'vendor_name' => $payoutItem->vendor->pbv_business_name ?? 'N/A',
                    'amount' => number_format($payoutItem->pbvpi_vendor_amount ?? 0, 2)
                ];
            });
            
            return response()->json([
                'success' => true,
                'batch_no' => $batch->pbpb_batch_no,
                'batch_name' => $batch->pbpb_batch_name,
                'total_amount' => number_format($batch->pbpb_total_amount, 2),
                'paid_date' => $batch->pbpb_payout_date ? Carbon::parse($batch->pbpb_payout_date)->format('d-M-Y H:i') : 'N/A',
                'paid_ref_no' => $batch->pbpb_payout_ref_no,
                'paid_by' => $batch->pbpb_paid_by,
                'remarks' => $batch->pbpb_remarks,
                'vendors' => $vendors
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load payment receipt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download payment receipt as PDF
     */
    public function downloadPaymentReceipt(Request $request)
    {
        try {
            $batchId = $request->batchId;
            $batch = payoutsBatch::findOrFail($batchId);
            
            $vendors = payoutsBatchDetails::with([
                'vendorPayoutItem.vendor'
            ])
            ->where('pbpbi_btach_id', $batchId)
            ->get()
            ->map(function($detail) {
                $payoutItem = $detail->vendorPayoutItem;
                return (object) [
                    'vendor_name' => $payoutItem->vendor->pbv_business_name ?? 'N/A',
                    'amount' => $payoutItem->pbvpi_vendor_amount ?? 0
                ];
            });
            
            $data = [
                'batch' => $batch,
                'vendors' => $vendors,
                'total_amount' => $batch->pbpb_total_amount,
                'paid_date' => $batch->pbpb_payout_date ? Carbon::parse($batch->pbpb_payout_date)->format('d-M-Y H:i') : 'N/A',
                'paid_ref_no' => $batch->pbpb_payout_ref_no,
                'paid_by' => $batch->pbpb_paid_by,
                'remarks' => $batch->pbpb_remarks
            ];
            
            $pdf = Pdf::loadView('pdf.payment-receipt', $data);
            $pdf->setPaper('a4', 'portrait');
            
            return $pdf->download('Payment_Receipt_' . $batch->pbpb_batch_no . '.pdf');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate receipt: ' . $e->getMessage());
        }
    }

    /**
     * Get batch payout details
     */
    private function getBatchPayoutDetails(int $batchId)
    {
        return payoutsBatchDetails::with([
            'vendorPayoutItem.vendor',
            'vendorPayoutItem.vendor.bankInfo',
            'vendorPayoutItem.vendor.bankInfo.bank',
            'vendorPayoutItem.booking',
            'vendorPayoutItem.payment'
        ])
        ->where('pbpbi_btach_id', $batchId)
        ->get()
        ->map(function($detail) {
            $payoutItem = $detail->vendorPayoutItem;
            $vendor = $payoutItem->vendor ?? null;
            $bankInfo = $vendor ? $vendor->bankInfo->first() : null;
            
            // Get bank name from the bank relationship
            $bankName = null;
            if ($bankInfo && $bankInfo->bank) {
                $bankName = $bankInfo->bank->pbb_name ?? null;
            }
            
            // Fallback to bank ID if bank name not found
            if (!$bankName && $bankInfo && !empty($bankInfo->pbvb_bankname)) {
                $bankName = $bankInfo->pbvb_bankname;
            }
            return (object) [
                'vendor_name' => $payoutItem->vendor->pbv_business_name ?? 'N/A',
                'vendor_contact' => $payoutItem->vendor->pbv_contact_no ?? 'N/A',
                'vendor_email' => $payoutItem->vendor->pbv_email ?? 'N/A',
                'bank_name' => $bankName ?? 'N/A',
                'bank_account_no' => $bankInfo->pbvb_accountno ?? 'N/A',
                'bank_account_name' => $bankInfo->pbvb_holder_name ?? 'N/A',
                'bank_branch' => $bankInfo->pbvb_branch ?? 'N/A',
                'booking_ref' => $payoutItem->booking->pbb_ref_no ?? 'N/A',
                'booking_date' => $payoutItem->booking ? Carbon::parse($payoutItem->booking->pbb_booking_date)->format('d-M-Y') : 'N/A',
                'payment_ref' => $payoutItem->payment->pbpt_ref_no ?? 'N/A',
                'amount' => $payoutItem->pbvpi_vendor_amount ?? 0,
            ];
        });
    }

    /**
     * Generate a unique batch number
     */
    private function generateBatchNumber()
    {
        $prefix = 'BATCH';
        $date = Carbon::now()->format('Ymd');
        $timestamp = Carbon::now()->timestamp;
        $random = strtoupper(substr(uniqid(), -4));
        
        return $prefix . '_' . $date . '_' . $random;
    }
}
