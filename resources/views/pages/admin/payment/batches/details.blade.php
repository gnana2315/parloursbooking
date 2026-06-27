<div id="batchDetailsWrapper">
    <!-- Batch Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-hashtag"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Batch No</span>
                    <span class="info-box-number">{{ $batch->pbpb_batch_no }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-money-bill"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Amount</span>
                    <span class="info-box-number">Rs. {{ number_format($batch->pbpb_total_amount, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-calendar"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Valid Date</span>
                    <span class="info-box-number">{{ Carbon\Carbon::parse($batch->pbpb_batch_valid_date)->format('d-M-Y') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-list"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Payouts</span>
                    <span class="info-box-number">{{ $batch->pbpb_total_payouts }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Notes -->
    @if($batch->pbpb_notes)
    <div class="alert alert-info">
        <strong><i class="fas fa-sticky-note"></i> Notes:</strong> {{ $batch->pbpb_notes }}
    </div>
    @endif

    <!-- Vendor Details Table -->
    <h5 class="mt-4 mb-3"><i class="fas fa-users"></i> Vendor Details</h5>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Vendor Name</th>
                    <th>Booking Ref</th>
                    <th>Booking Date</th>
                    <th>Payment Reference</th>
                    <th>Amount (Rs.)</th>
                    <th>Bank Name</th>
                    <th>Account No</th>
                    <th>Branch</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batchDetails as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $detail->vendor->pbv_business_name ?? 'N/A' }}</strong><br>
                        <small class="text-muted">Contact: {{ $detail->vendor->pbv_contact_no ?? 'N/A' }}</small>
                    </td>
                    <td>{{ $detail->booking->pbb_ref_no ?? 'N/A' }}</td>
                    <td>{{ $detail->booking ? Carbon\Carbon::parse($detail->booking->pbb_booking_date)->format('d-M-Y') : 'N/A' }}</td>
                    <td>{{ $detail->payment->pbpt_ref_no ?? 'N/A' }}</td>
                    <td class="text-right"><strong>Rs. {{ number_format($detail->pbvpi_vendor_amount, 2) }}</strong></td>
                    <td>{{ $detail->bankName ?? 'N/A' }}</td>
                    <td>{{ $detail->bankinfo->pbvb_accountno ?? 'N/A' }}</td>
                    <td>{{ $detail->bankinfo->pbvb_branch ?? 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center">No details found for this batch.</td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-light">
                    <th colspan="5" class="text-right">Total:</th>
                    <th class="text-right">Rs. {{ number_format($batch->pbpb_total_amount, 2) }}</th>
                    <th colspan="3"></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<style>
    .info-box {
        background: #fff;
        border-radius: 0.25rem;
        box-shadow: 0 0 1px rgba(0,0,0,0.125), 0 1px 3px rgba(0,0,0,0.2);
        display: flex;
        padding: 1rem;
        margin-bottom: 0;
    }
    .info-box .info-box-icon {
        border-radius: 0.25rem;
        align-items: center;
        display: flex;
        font-size: 1.875rem;
        justify-content: center;
        text-align: center;
        width: 70px;
        height: 70px;
    }
    .info-box .info-box-content {
        padding: 5px 10px;
        flex: 1;
    }
    .info-box .info-box-text {
        display: block;
        font-size: 14px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        text-transform: uppercase;
        font-weight: 600;
        color: #6c757d;
    }
    .info-box .info-box-number {
        display: block;
        font-size: 1.5rem;
        font-weight: 700;
    }
</style>