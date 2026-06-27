<style>
    /* Additional styling for the modal */
    #createBatchModal .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    #createBatchModal .modal-header .close {
        color: white;
        opacity: 1;
    }
    
    #createBatchModal .modal-header .close:hover {
        color: #f8f9fa;
    }
    
    #createBatchModal .alert-info {
        background: #e3f2fd;
        border-color: #90caf9;
    }
    
    #createBatchModal .form-group label {
        font-weight: 600;
    }
    
    #createBatchModal .text-danger {
        color: #dc3545;
    }
</style>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Payouts Filter</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" id="vendor_name_search" class="form-control form-control-sm" placeholder="Search by Vendor Name or Booking Ref No">
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="far fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control float-right" id="payout_date_range">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary btn-sm" id="createBatchBtn" style="width: 100%;">
                                    <i class="fas fa-plus-circle"></i> Create Batch
                                </button>
                            </div>
                            <div class="col-md-2">
                                <span class="badge badge-info" id="selectedCount" style="font-size: 14px; padding: 10px; width: 100%;">0 selected</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Payouts List</h3>
                    </div>
                    <div class="card-body">
                        <table id="payoutListTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAllPayouts"></th>
                                    <th>Booking Ref No</th>
                                    <th>Booking Date</th>
                                    <th>Status Updated Date</th>
                                    <th>Vendor Name</th>
                                    <th>Payment Reference</th>
                                    <th>Vendor Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="6" style="text-align:right">Total:</th>
                                    <th id="totalAmountFooter">Rs. 0.00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Create Batch Modal -->
<div class="modal fade" id="createBatchModal" tabindex="-1" role="dialog" aria-labelledby="createBatchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title" id="createBatchModalLabel">
                    <i class="fas fa-plus-circle"></i> Create Payout Batch
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="createBatchForm">
                <div class="modal-body">
                    <!-- Summary Section -->
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Selected Payouts:</strong> <span id="modalSelectedCount">0</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Total Amount:</strong> <span id="modalTotalAmount">Rs. 0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Batch Name -->
                    <div class="form-group">
                        <label for="batch_name">Batch Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="batch_name" name="batch_name" 
                               placeholder="Enter batch name (e.g., Payout Batch - March 2024)" required>
                        <small class="text-muted">Give a descriptive name for this payout batch.</small>
                    </div>

                    <!-- Total Amount (Editable) -->
                    <div class="form-group">
                        <label for="total_amount">Total Amount (Rs.) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rs.</span>
                            </div>
                            <input type="number" class="form-control" id="total_amount" name="total_amount" 
                                   step="0.01" min="0" required>
                        </div>
                        <small class="text-muted">You can modify the total amount if needed.</small>
                    </div>

                    <!-- Batch Valid Date -->
                    <div class="form-group">
                        <label for="batch_valid_date">Batch Valid Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="batch_valid_date" name="batch_valid_date" required>
                        <small class="text-muted">Set the date until which this batch is valid.</small>
                    </div>

                    <!-- Notes -->
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" 
                                  placeholder="Add any additional notes about this batch..."></textarea>
                    </div>

                    <!-- Selected Items Preview (Hidden) -->
                    <input type="hidden" id="selected_items" name="selected_items">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBatchBtn">
                        <i class="fas fa-check"></i> Create Batch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Initialize date range picker
        $('#payout_date_range').daterangepicker({
            autoUpdateInput: false,
            autoApply: false,
            locale: {
                format: 'YYYY-MM-DD',
                separator: ' - ',
                applyLabel: 'Apply',
                cancelLabel: 'Clear',
                fromLabel: 'From',
                toLabel: 'To',
                customRangeLabel: 'Custom'
            }
        });

        var payoutListTable = $('#payoutListTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: '{{ route("admin-payouts-list") }}',
                data: function(d) {
                    // FIXED: Use the correct filter names that match the controller
                    d.vendor_name = $('#vendor_name_search').val();
                    d.payout_date_range = $('#payout_date_range').val();
                }
            },
            columns: [
                {
                    data: 'select_all',
                    name: 'select_all',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'booking_ref_no',
                    name: 'booking_ref_no',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'booking_date',
                    name: 'booking_date',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'booking_status_updated_date',
                    name: 'booking_status_updated_date',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'vendor_name',
                    name: 'vendor_name',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'payment_reference',
                    name: 'payment_reference',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'vendor_amount',
                    name: 'vendor_amount',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [[3, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                processing: '<i class="fas fa-spinner fa-spin"></i> Loading...',
                emptyTable: 'No payouts found'
            },
            drawCallback: function() {
                // Update total amount from server response
                var totalAmount = payoutListTable.ajax.json().total_amount_sum || 0;
                $('#totalAmountFooter').text('Rs. ' + totalAmount.toFixed(2));
                updateSelectedCount();
            }
        });

        // Filter handlers
        $('#vendor_name_search').on('input', function() {
            payoutListTable.ajax.reload();
        });

        $('#payout_date_range').on('apply.daterangepicker', function(ev, picker) {
            var startDate = picker.startDate.format('YYYY-MM-DD');
            var endDate = picker.endDate.format('YYYY-MM-DD');
            $(this).val(startDate + ' - ' + endDate);  
            payoutListTable.ajax.reload();
        });

        $('#payout_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            payoutListTable.ajax.reload();
        });

        // Select all functionality
        $('#selectAllPayouts').on('change', function() {
            $('.payout-item-checkbox').prop('checked', $(this).prop('checked'));
            updateSelectedCount();
        });

        // Handle payout item selection
        $('#payoutListTable').on('change', '.payout-item-checkbox', function() {
            var allChecked = $('.payout-item-checkbox:checked').length === $('.payout-item-checkbox').length;
            $('#selectAllPayouts').prop('checked', allChecked);
            updateSelectedCount();
        });

        // Update selected count
        function updateSelectedCount() {
            var count = $('.payout-item-checkbox:checked').length;
            $('#selectedCount').text(count + ' selected');
        }

        // Create Batch Button Click
        $('#createBatchBtn').on('click', function() {
            var selectedIds = [];
            var totalAmount = 0;

            // Get all selected checkbox values
            $('.payout-item-checkbox:checked').each(function() {
                var id = $(this).data('id');
                var amount = parseFloat($(this).data('amount')) || 0;
                selectedIds.push(id);
                totalAmount += amount;
            });

            // Check if at least one record is selected
            if (selectedIds.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Selection',
                    text: 'Please select at least one payout record to create a batch.',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Store selected data
            selectedBatchData = {
                ids: selectedIds,
                totalAmount: totalAmount,
                count: selectedIds.length
            };

            // Show the modal with pre-filled data
            showBatchModal(selectedIds, totalAmount);
        });

        // Function to show modal with data
        function showBatchModal(selectedIds, totalAmount) {
            // Update modal summary
            $('#modalSelectedCount').text(selectedIds.length);
            $('#modalTotalAmount').text('Rs. ' + totalAmount.toFixed(2));
            
            // Set total amount in input
            $('#total_amount').val(totalAmount.toFixed(2));
            
            // Set selected items hidden input
            $('#selected_items').val(selectedIds.join(','));
            
            // Set default batch name
            var today = new Date();
            var dateStr = today.getFullYear() + '-' + 
                         String(today.getMonth() + 1).padStart(2, '0') + '-' + 
                         String(today.getDate()).padStart(2, '0');
            $('#batch_name').val('Payout Batch - ' + dateStr);
            
            // Set default valid date (7 days from now)
            var validDate = new Date();
            validDate.setDate(validDate.getDate() + 7);
            var validDateStr = validDate.getFullYear() + '-' + 
                              String(validDate.getMonth() + 1).padStart(2, '0') + '-' + 
                              String(validDate.getDate()).padStart(2, '0');
            $('#batch_valid_date').val(validDateStr);
            
            // Clear notes
            $('#notes').val('');
            
            // Show the modal
            $('#createBatchModal').modal('show');
        }

        // Handle form submission
        $('#createBatchForm').on('submit', function(e) {
            e.preventDefault();

            // Get form data
            var batchName = $('#batch_name').val().trim();
            var totalAmount = parseFloat($('#total_amount').val());
            var batchValidDate = $('#batch_valid_date').val();
            var notes = $('#notes').val().trim();
            var selectedItems = $('#selected_items').val();

            // Validate
            if (!batchName) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please enter a batch name.',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            if (!totalAmount || totalAmount <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please enter a valid total amount.',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            if (!batchValidDate) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please select a valid date.',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            // Disable submit button
            $('#submitBatchBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');

            // Send AJAX request
            $.ajax({
                url: '{{ route("admin-payouts-create-batch") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    selected_items: selectedItems,
                    batch_name: batchName,
                    total_amount: totalAmount,
                    batch_valid_date: batchValidDate,
                    notes: notes
                },
                success: function(response) {
                    if (response.success) {
                        // Close modal
                        $('#createBatchModal').modal('hide');
                        
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Batch Created Successfully',
                            text: response.message || 'Batch has been created successfully.',
                            confirmButtonColor: '#28a745',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Reload the table
                            payoutListTable.ajax.reload();
                            // Uncheck all checkboxes
                            $('#selectAllPayouts').prop('checked', false);
                            updateSelectedCount();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to Create Batch',
                            text: response.message || 'An error occurred while creating the batch.',
                            confirmButtonColor: '#d33'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    
                    // Parse error response
                    var errorMessage = 'An error occurred while creating the batch. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        confirmButtonColor: '#d33'
                    });
                },
                complete: function() {
                    // Re-enable submit button
                    $('#submitBatchBtn').prop('disabled', false).html('<i class="fas fa-check"></i> Create Batch');
                }
            });
        });

        // Reset form when modal is closed
        $('#createBatchModal').on('hidden.bs.modal', function() {
            $('#createBatchForm')[0].reset();
            $('#submitBatchBtn').prop('disabled', false).html('<i class="fas fa-check"></i> Create Batch');
        });
    });
</script>