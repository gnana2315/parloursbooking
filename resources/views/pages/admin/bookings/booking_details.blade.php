<div class="modal-header">
    <h5 class="modal-title">
        <i class="fas fa-calendar-check text-primary"></i> 
        Booking Details #{{ $booking->pbb_id }}
    </h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <!-- Status Banner -->
    <div class="alert alert-info mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-info-circle fa-2x mr-2"></i>
                <strong>Booking Status:</strong>
                @php
                    $statuses = [
                        0 => ['class' => 'warning', 'text' => 'Cancelled By Admin'],
                        1 => ['class' => 'info', 'text' => 'Upcoming'],
                        2 => ['class' => 'success', 'text' => 'Completed'],
                        3 => ['class' => 'danger', 'text' => 'Payment Pending'],
                        4 => ['class' => 'secondary', 'text' => 'DNA'],
                        5 => ['class' => 'dark', 'text' => 'Payment Failure'],
                    ];
                    $statusClass = $statuses[$booking->pbb_status]['class'] ?? 'dark';
                    $statusText = $statuses[$booking->pbb_status]['text'] ?? 'Unknown';
                @endphp
                <span class="badge badge-{{ $statusClass }} ml-2">{{ $statusText }}</span>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Customer Information -->
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h6 class="card-title">
                        <i class="fas fa-user"></i> Customer Information
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="35%">Name:</th>
                            <td>{{ $booking->customer->pbc_first_name ?? 'N/A' }} {{ $booking->customer->pbc_last_name }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $booking->customer->pbc_email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>{{ $booking->customer->pbc_contact_no ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Address:</th>
                            <td>{{ $booking->customer->pbc_address ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Booking Information -->
        <div class="col-md-6">
            <div class="card card-info card-outline">
                <div class="card-header">
                    <h6 class="card-title">
                        <i class="fas fa-info-circle"></i> Booking Information
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="40%">Booking Date:</th>
                            <td>{{ \Carbon\Carbon::parse($booking->pbb_booking_date)->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Total Amount:</th>
                            <td>
                                <span class="h5 text-success">Rs. {{ number_format($booking->pbb_total_amount, 2) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Created At:</th>
                            <td>{{ $booking->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>{{ $booking->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Promo Code Information -->
    @if($booking->promoCode)
    <div class="card card-success card-outline">
        <div class="card-header">
            <h6 class="card-title">
                <i class="fas fa-tags"></i> Promo Code Applied
            </h6>
        </div>
        <div class="card-body">
            <table class="table table-sm table-borderless">
                <tr>
                    <th width="30%">Promo Code:</th>
                    <td><strong>{{ $booking->promoCode->pbpc_name ?? 'N/A' }} ({{ $booking->promoCode->pbpc_code ?? 'N/A' }})</strong></td>
                </tr>
                <tr>
                    <th>Discount Type:</th>
                    <td>{{ ucfirst($booking->promoCode->pbpc_discount_type ?? 'N/A') }}</td>
                </tr>
                <tr>
                    <th>Discount Value:</th>
                    <td>
                        @if(($booking->promoCode->pbpc_discount_type ?? '') == 'percentage')
                            {{ $booking->promoCode->pbpc_value ?? 0 }}%
                        @else
                            Rs. {{ number_format($booking->promoCode->pbpc_value ?? 0, 2) }}
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endif
    
    <!-- Vendors Information -->
    @if($booking->vendors && $booking->vendors->count() > 0)
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h6 class="card-title">
                <i class="fas fa-store"></i> Vendor
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-2">
                    <div class="small">
                        <strong>{{ $booking->vendors->pbv_business_name }}</strong><br>
                        @if($booking->vendors->pbv_address)
                            <small>Email: {{ $booking->vendors->pbv_address ?? 'N/A' }}</small><br>
                        @endif
                        @if($booking->vendors->pbv_email)
                            <small>Email: {{ $booking->vendors->pbv_email ?? 'N/A' }}</small><br>
                        @endif
                        <small>Phone: {{ $booking->vendors->pbv_contactno?? 'N/A' }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Services Booked -->
    @if($booking->bookingDetails && $booking->bookingDetails->count() > 0)
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h6 class="card-title">
                <i class="fas fa-shopping-cart"></i> Services Booked ({{ $booking->bookingDetails->count() }})
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="40%">Service Name</th>
                            <th width="20%" class="text-right">Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($booking->bookingDetails as $detail)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $detail->services->pbs_name ?? 'N/A' }}</strong>
                                @if($detail->services->pbs_description)
                                    <br><small class="text-muted">{{ Str::limit($detail->services->pbs_description, 50) }}</small>
                                @endif
                            </td>
                            <td class="text-right">Rs. {{ number_format($detail->services->pbs_price ?? 0, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-active">
                            <th colspan="2" class="text-right">Sub Total:</th>
                            <th class="text-right">Rs. {{ number_format($booking->pbb_total_amount, 2) }}</th>
                        </tr>
                        @if($booking->promoCode)
                        <tr class="table-active">
                            <th colspan="4" class="text-right">Discount Applied:</th>
                            <th class="text-right text-success">
                                - Rs. {{ number_format(
                                    ($booking->promoCode->pbpc_discount_type == 'percentage') 
                                        ? ($booking->pbb_total_amount * $booking->promoCode->pbpc_discount_value / 100)
                                        : $booking->promoCode->pbpc_discount_value, 2) }}
                            </th>
                        </tr>
                        <tr class="table-active">
                            <th colspan="4" class="text-right">Grand Total:</th>
                            <th class="text-right text-success h5">
                                Rs. {{ number_format($booking->pbb_total_amount, 2) }}
                            </th>
                        </tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Payment Transactions -->
    @if($booking->paymentTransections && $booking->paymentTransections->count() > 0)
    <div class="card card-info card-outline">
        <div class="card-header">
            <h6 class="card-title">
                <i class="fas fa-credit-card"></i> Payment Transactions
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Transaction ID</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>{{ $booking->paymentTransections->pbpt_transaction_id ?? 'N/A' }}</code></td>
                            <td class="text-right">Rs. {{ number_format($booking->paymentTransections->pbpt_amount ?? 0, 2) }}</td>
                            <td>{{ ucfirst($booking->paymentTransections->pbpt_payment_method ?? 'N/A') }}</td>
                            <td>
                                @php
                                    $txnStatus = [
                                        '0' => 'warning',
                                        '1' => 'success'
                                    ];
                                    $txnClass = $txnStatus[$booking->paymentTransections->pbpt_status] ?? 'secondary';
                                    $txnStatusText = '';
                                    if($booking->paymentTransections->pbpt_status == 1){
                                        $txnStatusText = 'Paid';
                                    }else{
                                        $txnStatusText = 'Unpaid';
                                    }
                                @endphp
                                <span class="badge badge-{{ $txnClass }}">
                                    {{ $txnStatusText }}
                                </span>
                            </td>
                            <td>{{ isset($booking->paymentTransections->created_at) ? $booking->paymentTransections->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
    
    <!-- Ratings & Reviews -->
    @if($booking->status == 2)
        @if($booking->ratings && $booking->ratings->count() > 0)
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h6 class="card-title">
                    <i class="fas fa-star"></i> Customer Rating & Review
                </h6>
            </div>
            <div class="card-body">
                @foreach($booking->ratings as $rating)
                    <div class="border-bottom mb-2 pb-2">
                        <div class="d-flex justify-content-between">
                            <div>
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= ($rating->pbr_rating ?? 0))
                                        <i class="fas fa-star text-warning"></i>
                                    @else
                                        <i class="far fa-star text-muted"></i>
                                    @endif
                                @endfor
                                <strong class="ml-2">{{ $rating->pbr_rating ?? 0 }}/5</strong>
                            </div>
                            <small class="text-muted">{{ isset($rating->created_at) ? $rating->created_at->format('d/m/Y') : 'N/A' }}</small>
                        </div>
                        @if($rating->pbr_review)
                            <p class="mt-2 mb-0">{{ $rating->pbr_review }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    @endif
    
    <!-- Additional Notes -->
    @if($booking->pbb_notes)
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h6 class="card-title">
                <i class="fas fa-sticky-note"></i> Additional Notes
            </h6>
        </div>
        <div class="card-body">
            {{ $booking->pbb_notes }}
        </div>
    </div>
    @endif
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-dismiss="modal">
        <i class="fas fa-times"></i> Close
    </button>
    <button type="button" class="btn btn-primary" onclick="printModalDetails()">
        <i class="fas fa-print"></i> Print Details
    </button>
</div>

<script>
function printModalDetails() {
    var printContent = document.querySelector('.modal-body').cloneNode(true);
    var originalTitle = document.title;
    document.title = "Booking Details #{{ $booking->pbb_id }}";
    var printWindow = window.open('', '_blank');
    printWindow.document.write('<html><head><title>Booking Details</title>');
    printWindow.document.write('<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">');
    printWindow.document.write('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">');
    printWindow.document.write('<style>body { padding: 20px; } .btn, .modal-footer { display: none; }</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<div class="container">' + printContent.innerHTML + '</div>');
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    printWindow.print();
    document.title = originalTitle;
}
</script>