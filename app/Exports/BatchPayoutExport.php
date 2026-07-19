<?php

namespace App\Exports;

use App\Models\payoutsBatch;
use App\Models\payoutsBatchDetails;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Carbon\Carbon;

class BatchPayoutExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $batchId;

    public function __construct($batchId)
    {
        $this->batchId = $batchId;
    }

    public function array(): array
    {
        $batch = payoutsBatch::findOrFail($this->batchId);
        
        $details = payoutsBatchDetails::with([
            'vendorPayoutItem.vendor',
            'vendorPayoutItem.vendor.bankInfo',
            'vendorPayoutItem.vendor.bankInfo.bank',
            'vendorPayoutItem.booking',
            'vendorPayoutItem.payment'
        ])
        ->where('pbpbi_batch_id', $this->batchId)
        ->get();

        $data = [];
        
        // Add header row with batch info
        $data[] = ['BATCH PAYOUT DETAILS'];
        $data[] = ['Batch No:', $batch->pbpb_batch_no];
        $data[] = ['Batch Name:', $batch->pbpb_batch_name ?? 'N/A'];
        $data[] = ['Total Amount:', 'Rs. ' . number_format($batch->pbpb_total_amount, 2)];
        $data[] = ['Total Payouts:', $batch->pbpb_total_payouts];
        $data[] = ['Valid Date:', Carbon::parse($batch->pbpb_batch_valid_date)->format('d-M-Y')];
        $data[] = ['Created Date:', Carbon::parse($batch->created_at)->format('d-M-Y H:i')];
        if ($batch->pbpb_notes) {
            $data[] = ['Notes:', $batch->pbpb_notes];
        }
        $data[] = []; // Empty row for spacing
        $data[] = []; // Empty row for spacing
        
        // Add column headers
        $data[] = [
            'S.No',
            'Vendor Name',
            'Contact No',
            'Email',
            'Bank Name',
            'Account No',
            'Account Name',
            'Branch',
            'Booking Ref',
            'Booking Date',
            'Payment Ref',
            'Amount (Rs.)'
        ];
        
        // Add data rows
        $index = 1;
        $totalAmount = 0;
        
        foreach ($details as $detail) {
            $payoutItem = $detail->vendorPayoutItem;
            $vendor = $payoutItem->vendor ?? null;
            $amount = $payoutItem->pbvpi_vendor_amount ?? 0;
            $totalAmount += $amount;
            
            // Get bank info
            $bankInfo = $vendor ? $vendor->bankInfo->first() : null;
            $bankName = null;
            
            // Get bank name from the bank relationship
            if ($bankInfo && $bankInfo->bank) {
                $bankName = $bankInfo->bank->pbb_name ?? null;
            }
            
            // Fallback to bank ID if bank name not found
            if (!$bankName && $bankInfo && !empty($bankInfo->pbvb_bankname)) {
                $bankName = $bankInfo->pbvb_bankname;
            }
            
            $data[] = [
                $index++,
                $payoutItem->vendor->pbv_business_name ?? 'N/A',
                $payoutItem->vendor->pbv_contact_no ?? 'N/A',
                $payoutItem->vendor->pbv_email ?? 'N/A',
                $bankName ?? 'N/A',
                $bankInfo->pbvb_accountno ?? 'N/A',
                $bankInfo->pbvb_holder_name ?? 'N/A',
                $bankInfo->pbvb_branch ?? 'N/A',
                $payoutItem->booking->pbb_ref_no ?? 'N/A',
                $payoutItem->booking ? Carbon::parse($payoutItem->booking->pbb_booking_date)->format('d-M-Y') : 'N/A',
                $payoutItem->payment->pbpt_ref_no ?? 'N/A',
                number_format($amount, 2)
            ];
        }
        
        // Add total row
        $data[] = [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'Total:',
            number_format($totalAmount, 2)
        ];
        
        return $data;
    }

    public function headings(): array
    {
        // This is not used as we're providing custom headers
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        // Merge cells for title
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setSize(16)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');
        
        // Style for batch info
        $sheet->getStyle('A2:L8')->getFont()->setSize(10);
        
        // Style for headers
        $sheet->getStyle('A11:L11')->getFont()->setBold(true);
        $sheet->getStyle('A11:L11')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FF2C3E50');
        $sheet->getStyle('A11:L11')->getFont()->setColor(
            new Color(Color::COLOR_WHITE)
        );
        
        // Style for total row
        $lastRow = $sheet->getHighestRow();
        $sheet->getStyle('A' . $lastRow . ':L' . $lastRow)->getFont()->setBold(true);
        $sheet->getStyle('A' . $lastRow . ':L' . $lastRow)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFE9ECEF');
        
        // Set column widths
        foreach (range('A', 'L') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        return [];
    }
}