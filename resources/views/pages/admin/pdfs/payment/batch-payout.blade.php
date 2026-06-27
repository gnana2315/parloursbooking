<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Batch Payout Details - {{ $batch->pbpb_batch_no }}</title>
    <style>
        /* Your styles here */
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .batch-info {
            margin-bottom: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th {
            background: #2c3e50;
            color: white;
            padding: 8px;
            text-align: left;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            background: #e9ecef !important;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 15px;
            color: #7f8c8d;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Batch Payout Details</h1>
        <p>Payment Batch - {{ $batch->pbpb_batch_no }}</p>
    </div>

    <div class="batch-info">
        <table>
            <tr>
                <td><strong>Batch No:</strong> {{ $batch->pbpb_batch_no }}</td>
                <td><strong>Created Date:</strong> {{ date('d-M-Y H:i', strtotime($batch->created_at)) }}</td>
            </tr>
            <tr>
                <td><strong>Batch Name:</strong> {{ $batch->pbpb_batch_name ?? 'N/A' }}</td>
                <td><strong>Valid Date:</strong> {{ date('d-M-Y', strtotime($batch->pbpb_batch_valid_date)) }}</td>
            </tr>
            <tr>
                <td><strong>Total Amount:</strong> Rs. {{ number_format($batch->pbpb_total_amount, 2) }}</td>
                <td><strong>Total Payouts:</strong> {{ $batch->pbpb_total_payouts }}</td>
            </tr>
            @if($batch->pbpb_notes)
            <tr>
                <td colspan="2"><strong>Notes:</strong> {{ $batch->pbpb_notes }}</td>
            </tr>
            @endif
        </table>
    </div>

    <h3>Vendor Payout Details</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Vendor Name</th>
                <th>Bank Name</th>
                <th>Account No</th>
                <th>Holder Name</th>
                <th>Branch</th>
                <th>Booking Ref</th>
                <th>Payment Ref</th>
                <th class="text-right">Amount (Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($batchDetails as $index => $detail)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $detail->vendor_name }}</td>
                <td>{{ $detail->bank_name }}</td>
                <td>{{ $detail->bank_account_no }}</td>
                <td>{{ $detail->bank_account_name }}</td>
                <td>{{ $detail->bank_branch }}</td>
                <td>{{ $detail->booking_ref }}</td>
                <td>{{ $detail->payment_ref }}</td>
                <td class="text-right">Rs. {{ number_format($detail->amount, 2) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">No details found</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="8" class="text-right">Total Amount:</td>
                <td class="text-right">Rs. {{ number_format($batch->pbpb_total_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Generated on {{ date('d-M-Y H:i:s') }}</p>
        <p>This is a system-generated document for batch payout reference.</p>
    </div>
</body>
</html>