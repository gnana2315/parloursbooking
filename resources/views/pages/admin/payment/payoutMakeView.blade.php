<table id="payoutMakeView" class="table table-bordered">
    <thead>
        <tr>
            <th>Select All</th>
            <th>Booking</th>
            <th>Transection</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        @forelse($vendorPayoutItems as $item)
            <tr id="payout-item-{{ $item->pbvpi_id }}">
                <td><input type="checkbox" class="payout-item-checkbox" data-amount="{{ $item->pbvpi_vendor_amount }}" data-id="{{ $item->pbvpi_id }}"></td>
                <td>{{ $item->booking->pbb_ref_no }}</td>
                <td>{{ $item->payment->pbpt_payment_ref_no   }}</td>
                <td>{{ 'Rs. ' . number_format($item->pbvpi_vendor_amount, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5">No payout items found</td>
            </tr>
        @endforelse
    </tbody>
</table>
<form id="makePayoutForm" method="POST" action="{{ route('payouts.process') }}">
    @csrf
    <input type="hidden" name="vendor_id" value="{{ $vendorId }}">
    <input type="hidden" name="selected_items" id="selectedItems" value="">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="totalAmount">Total Payout Amount:</label>
                <input type="text" class="form-control" name="total_amount" id="totalAmount" value="0" readonly>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="paymentMethod">Payment Method:</label>
                <select class="form-control" name="payment_method" id="paymentMethod" required>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="paymentReference">Payment Reference/Description:</label>
                <input type="text" class="form-control" name="payment_reference" id="paymentReference" placeholder="Payment Reference/Description">
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-success mt-3">Confirm Payout</button>
<script>
    $(document).ready(function () {
        function updatePayoutSummary() {
            let totalAmount = 0;
            let selectedIds = [];
            
            // Loop through all checked checkboxes
            $('.payout-item-checkbox:checked').each(function() {
                let amount = parseFloat($(this).data('amount'));
                let id = $(this).data('id');
                
                if (!isNaN(amount)) {
                    totalAmount += amount;
                }
                
                if (id) {
                    selectedIds.push(id);
                }
            });
            
            // Update total amount field with formatted value
            $('#totalAmount').val(totalAmount.toFixed(2));
            
            // Update selected items hidden field with comma-separated IDs
            $('#selectedItems').val(selectedIds.join(','));
            
            // Optional: Show count of selected items
            let selectedCount = selectedIds.length;
            if (selectedCount > 0) {
                $('#selectedCount').text(selectedCount);
            }
        }
        
        // Handle checkbox change event
        $(document).on('change', '.payout-item-checkbox', function() {
            updatePayoutSummary();
            
            // Optional: Highlight selected row
            if ($(this).is(':checked')) {
                $(this).closest('tr').addClass('table-active');
            } else {
                $(this).closest('tr').removeClass('table-active');
            }
        });
        
        // Handle Select All functionality
        $('#selectAllCheckbox').on('change', function() {
            let isChecked = $(this).is(':checked');
            $('.payout-item-checkbox').prop('checked', isChecked).trigger('change');
            
            // Trigger row highlighting
            if (isChecked) {
                $('.payout-item-checkbox').closest('tr').addClass('table-active');
            } else {
                $('.payout-item-checkbox').closest('tr').removeClass('table-active');
            }
        });

        // Form submission validation
        $('#makePayoutForm').on('submit', function(e) {
            e.preventDefault();
            
            let selectedItems = $('#selectedItems').val();
            let totalAmount = $('#totalAmount').val();
            let paymentMethod = $('#paymentMethod').val();
            let paymentReference = $('#paymentReference').val();
            let vendorId = $('input[name="vendor_id"]').val();
            
            // Validation
            if (!selectedItems || selectedItems.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Items Selected',
                    text: 'Please select at least one payout item',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                return false;
            }
            
            if (parseFloat(totalAmount) <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Amount',
                    text: 'Total amount must be greater than 0',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }
            
            if (!paymentMethod) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Payment Method Required',
                    text: 'Please select a payment method',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }
            
            let itemCount = selectedItems.split(',').length;
            
            // SweetAlert2 confirmation dialog
            Swal.fire({
                title: 'Confirm Payout',
                html: `
                    <div style="text-align: left;">
                        <p><strong>Are you sure you want to process this payout?</strong></p>
                        <hr>
                        <p><i class="fas fa-user"></i> <strong>Vendor:</strong> {{$vendorName}} (${vendorId})</p>
                        <p><i class="fas fa-money-bill-wave"></i> <strong>Amount:</strong> Rs. ${parseFloat(totalAmount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                        <p><i class="fas fa-receipt"></i> <strong>Items:</strong> ${itemCount} item(s)</p>
                        <p><i class="fas fa-credit-card"></i> <strong>Payment Method:</strong> ${paymentMethod.replace('_', ' ').toUpperCase()}</p>
                        ${paymentReference ? `<p><i class="fas fa-reference"></i> <strong>Reference:</strong> ${paymentReference}</p>` : ''}
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: '<i class="fas fa-check"></i> Yes, Process Payout',
                cancelButtonText: '<i class="fas fa-times"></i> Cancel',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve, reject) => {
                        // Get the submit button
                        let $submitBtn = $('button[type="submit"]');
                        
                        $.ajax({
                            url: $(this).attr('action'),
                            method: 'POST',
                            data: $(this).serialize(),
                            success: function(response) {
                                if (response.status === true || response.success === true) {
                                    resolve(response);
                                } else {
                                    reject(response.message || 'Failed to process payout');
                                }
                            },
                            error: function(xhr) {
                                let errorMsg = 'An error occurred while processing the payout.';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                }
                                reject(errorMsg);
                            }
                        });
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    // Success message
                    Swal.fire({
                        title: 'Success!',
                        text: 'Payout processed successfully!',
                        icon: 'success',
                        confirmButtonColor: '#28a745',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload page or redirect
                        location.reload();
                    });
                } else if (result.isDismissed) {
                    // User cancelled
                    Swal.fire({
                        title: 'Cancelled',
                        text: 'Payout processing was cancelled',
                        icon: 'info',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                }
            }).catch((error) => {
                // Error message
                console.log('Payout processing error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: error || 'Failed to process payout. Please try again.',
                    icon: 'error',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'OK'
                });
                
                // Re-enable submit button
                $('button[type="submit"]').prop('disabled', false).html('Confirm Payout');
            });
        });
        
        // Initialize on page load
        updatePayoutSummary();
        
        // Optional: Add a select/deselect all button functionality
        $('#btnSelectAll').on('click', function() {
            $('#selectAllCheckbox').prop('checked', true).trigger('change');
        });
        
        $('#btnDeselectAll').on('click', function() {
            $('#selectAllCheckbox').prop('checked', false).trigger('change');
        });
        
        // Optional: Display selected count badge
        if ($('#selectedCount').length === 0) {
            $('th:first-child').append('<span id="selectedCount" class="badge bg-info ms-2" style="display:none;">0</span>');
        }
    });
</script>