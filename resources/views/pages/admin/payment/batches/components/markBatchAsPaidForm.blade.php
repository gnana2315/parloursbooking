<div class="row mb-3">
    <div class="col-md-12">
        <div class="alert alert-info">
            <div class="row">
                <div class="col-md-6">
                    <strong>Batch No:</strong> {{ $batch->pbpb_batch_no }}
                </div>
                <div class="col-md-6">
                    <strong>Batch Name:</strong> {{ $batch->pbpb_batch_name ?? 'N/A' }}
                </div>
                <div class="col-md-6">
                    <strong>Total Amount:</strong> <span class="font-weight-bold">Rs. {{ number_format($totalAmount, 2) }}</span>
                </div>
                <div class="col-md-6">
                    <strong>Total Vendors:</strong> {{ count($payoutBatchDetails) }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table -->
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Vendor Name</th>
            <th>Bank Details</th>
            <th>Amount</th>
            <th>Paid Date</th>
            <th>Paid Ref No</th>
            <th>Paid Slip</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @php $grandTotal = 0; @endphp
        @foreach($payoutBatchDetails as $vendorId => $details)
            @php
                $firstDetail = $details->first();
                $vendorAmount = $details->sum('pbpbi_vendor_amount');
                $grandTotal += $vendorAmount;
                $isPaid = $details->every(function($detail) {
                    return $detail->pbpbi_status == 1;
                });
                $vendor = $firstDetail->vendor;
                $bankInfo = $firstDetail->bank_info;
                $bankName = $firstDetail->bank_name ?? 'N/A';
            @endphp
            <tr class="{{ $isPaid ? 'table-success' : '' }}">
                <td>
                    <strong>{{ $firstDetail->vendor_name }}</strong>
                    @if(!empty($firstDetail->vendor_contact))
                    <br>
                    <small class="text-muted">
                        <i class="fas fa-phone"></i> {{ $firstDetail->vendor_contact }}
                    </small>
                    @endif
                    @if(!empty($firstDetail->vendor_email))
                    <br>
                    <small class="text-muted">
                        <i class="fas fa-envelope"></i> {{ $firstDetail->vendor_email }}
                    </small>
                    @endif
                </td>
                <td>
                    <strong>{{ $bankName }}</strong>
                    @if($bankInfo)
                    <br>
                    <small class="text-muted">
                        <i class="fas fa-university"></i> Account: {{ $bankInfo->pbvb_accountno ?? 'N/A' }}
                    </small>
                    <br>
                    <small class="text-muted">
                        <i class="fas fa-user"></i> Holder: {{ $bankInfo->pbvb_holder_name ?? 'N/A' }}
                    </small>
                    <br>
                    <small class="text-muted">
                        <i class="fas fa-location-dot"></i> Branch: {{ $bankInfo->pbvb_branch ?? 'N/A' }}
                    </small>
                    @else
                    <br>
                    <span class="text-danger">
                        <i class="fas fa-exclamation-circle"></i> No bank details
                    </span>
                    @endif
                </td>
                <td class="text-right">
                    <strong>Rs. {{ number_format($vendorAmount, 2) }}</strong>
                    <br>
                    <small class="text-muted">({{ $details->count() }} payouts)</small>
                </td>
                <td>
                    @if($isPaid)
                        <span class="text-success">
                            <i class="fas fa-check-circle"></i> 
                            {{ $firstDetail->pbpbi_paid_date ? date('d-M-Y', strtotime($firstDetail->pbpbi_paid_date)) : 'N/A' }}
                        </span>
                    @else
                        <input type="date" class="form-control form-control-sm" 
                               name="paid_date[{{ $vendorId }}]" 
                               id="paid_date_{{ $vendorId }}"
                               value="{{ date('Y-m-d') }}">
                    @endif
                </td>
                <td>
                    @if($isPaid)
                        <span class="text-success">
                            <i class="fas fa-check-circle"></i> 
                            {{ $firstDetail->pbpbi_paid_ref_no ?? 'N/A' }}
                        </span>
                    @else
                        <input type="text" class="form-control form-control-sm" 
                               name="paid_ref_no[{{ $vendorId }}]" 
                               id="paid_ref_no_{{ $vendorId }}" 
                               placeholder="Enter reference no">
                    @endif
                </td>
                <td>
                    @if($isPaid)
                        @if($firstDetail->pbpbi_paid_slip_url)
                            <a href="{{ asset('storage/' . $firstDetail->pbpbi_paid_slip_url) }}" 
                               target="_blank" class="btn btn-sm btn-info">
                                <i class="fas fa-eye"></i> View
                            </a>
                        @else
                            <span class="text-muted">No slip</span>
                        @endif
                    @else
                        <input type="file" class="form-control-file form-control-sm" 
                               name="paid_slip[{{ $vendorId }}]" 
                               id="paid_slip_{{ $vendorId }}" 
                               accept=".jpg,.jpeg,.png,.pdf"
                               style="font-size: 12px;">
                    @endif
                </td>
                <td>
                    @if(!$isPaid)
                        <button type="button" class="btn btn-sm btn-success markVendorPayoutBtn" 
                                data-vendor-id="{{ $vendorId }}" 
                                data-batch-id="{{ $firstDetail->pbpbi_batch_id }}"
                                data-vendor-name="{{ $firstDetail->vendor_name }}"
                                data-total-amount="{{ $vendorAmount }}">
                            <i class="fas fa-check"></i> Mark Paid
                        </button>
                    @else
                        <span class="badge badge-success">Paid</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="table-secondary font-weight-bold">
            <td colspan="2" class="text-right">GRAND TOTAL:</td>
            <td class="text-right">Rs. {{ number_format($grandTotal, 2) }}</td>
            <td colspan="4"></td>
        </tr>
    </tfoot>
</table>

<!-- Footer Summary -->
<div class="row mt-3">
    <div class="col-md-12">
        <div class="alert alert-secondary">
            <div class="row">
                <div class="col-md-4">
                    <strong>Total Pending:</strong> 
                    <span class="text-warning">
                        {{ $payoutBatchDetails->filter(function($details) {
                            return $details->every(function($detail) {
                                return $detail->pbpbi_status == 0;
                            });
                        })->count() }} vendors
                    </span>
                </div>
                <div class="col-md-4">
                    <strong>Total Paid:</strong> 
                    <span class="text-success">
                        {{ $payoutBatchDetails->filter(function($details) {
                            return $details->every(function($detail) {
                                return $detail->pbpbi_status == 1;
                            });
                        })->count() }} vendors
                    </span>
                </div>
                <div class="col-md-4">
                    <strong>Total Amount:</strong> 
                    <span class="text-primary">Rs. {{ number_format($grandTotal, 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    // Mark Vendor Payout Button Click
    $(document).on('click', '.markVendorPayoutBtn', function() {
        var vendorId = $(this).data('vendor-id');
        var batchId = $(this).data('batch-id');
        var vendorName = $(this).data('vendor-name');
        var totalAmount = $(this).data('total-amount');
        
        // Get form values
        var paidDate = $('#paid_date_' + vendorId).val();
        var paidRefNo = $('#paid_ref_no_' + vendorId).val().trim();
        var paymentSlip = $('#paid_slip_' + vendorId)[0].files[0];
        
        // Validate
        if (!paidDate) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please select a paid date.',
                confirmButtonColor: '#d33'
            });
            return;
        }
        
        if (!paidRefNo) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please enter a payment reference number.',
                confirmButtonColor: '#d33'
            });
            return;
        }
        
        if (!paymentSlip) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Please upload a payment slip.',
                confirmButtonColor: '#d33'
            });
            return;
        }
        
        // Show confirmation dialog
        Swal.fire({
            title: 'Confirm Payment',
            html: `
                <div class="text-left">
                    <p><strong>Vendor:</strong> ${vendorName}</p>
                    <p><strong>Amount:</strong> Rs. ${totalAmount.toFixed(2)}</p>
                    <p><strong>Reference No:</strong> ${paidRefNo}</p>
                    <p><strong>Paid Date:</strong> ${paidDate}</p>
                    <p><strong>Slip:</strong> ${paymentSlip.name}</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Mark as Paid',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create FormData
                var formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('vendor_id', vendorId);
                formData.append('batch_id', batchId);
                formData.append('paid_date', paidDate);
                formData.append('paid_ref_no', paidRefNo);
                formData.append('payment_slip', paymentSlip);
                
                // Show loading
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we process the payment.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Send AJAX request
                $.ajax({
                    url: '{{ route("admin-payouts-mark-vendor-payout") }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message || 'Vendor marked as paid successfully.',
                                confirmButtonColor: '#28a745'
                            }).then(() => {
                                // Reload the modal content
                                var batchId = $('#downloadPdfBtn').data('batch-id');
                                if (batchId) {
                                    loadBatchDetails(batchId);
                                } else {
                                    location.reload();
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to mark vendor as paid.'
                            });
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = 'An error occurred. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage
                        });
                    }
                });
            }
        });
    });
    
    // Function to reload batch details
    function loadBatchDetails(batchId) {
        $('#batchDetailsContent').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Loading batch details...</p>
            </div>
        `);
        
        $.ajax({
            url: '{{ route("admin-payouts-batches-details") }}',
            method: 'GET',
            data: { batchId: batchId },
            success: function(response) {
                $('#batchDetailsContent').html(response);
                // Store batch data for download
                $('#downloadPdfBtn').data('batch-id', batchId);
                $('#downloadExcelBtn').data('batch-id', batchId);
            },
            error: function(xhr) {
                $('#batchDetailsContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> 
                        Failed to load batch details. Please try again.
                    </div>
                `);
            }
        });
    }
});
</script>