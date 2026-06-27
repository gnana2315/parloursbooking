<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Payout Batches Filter</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" id="search" class="form-control form-control-sm" placeholder="Search by Batch Name or Batch No">
                            </div>
                            <div class="col-md-4">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="far fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control float-right" id="batches_date_range">
                                </div>
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
                        <h3 class="card-title">Payout Batches List</h3>
                    </div>
                    <div class="card-body">
                        <table id="payoutBatchListTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Batch No</th>
                                    <th>Batch Name</th>
                                    <th>Amount</th>
                                    <th>Batch Valid Date</th>
                                    <th>Notes</th>
                                    <th>Paid Date</th>
                                    <th>Paid Ref No</th>
                                    <th>Paid By</th>
                                    <th>Remarks</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Create Batch Modal -->
<div class="modal fade" id="markBatchModal" tabindex="-1" role="dialog" aria-labelledby="markBatchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title" id="markBatchModalLabel">
                    <i class="fas fa-plus-circle"></i> Mark Payout Batch
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="markBatchForm">
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

                    <div class="form-group">
                        <label for="paid_date">Paid Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="paid_date" name="paid_date" required>
                    </div>

                    <div class="form-group">
                        <label for="paid_ref_no">Paid Ref No<span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="paid_ref_no" name="paid_ref_no" required>
                    </div>

                    <div class="form-group">
                        <label for="payment_slip">Payment Slip <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="payment_slip" name="payment_slip" accept=".jpg,.jpeg,.png,.pdf" required>
                    </div>

                    <div class="form-group">
                        <label for="remarks">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Add any additional notes about this batch payment..."></textarea>
                    </div>
                    <input type="hidden" id="selected_items" name="selected_items">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="submitBatchBtn">
                        <i class="fas fa-check"></i> Mark Batch
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Batch Details Modal -->
<div class="modal fade" id="batchDetailsModal" tabindex="-1" role="dialog" aria-labelledby="batchDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title" id="batchDetailsModalLabel">
                    <i class="fas fa-file-invoice"></i> Batch Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="batchDetailsContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-success" id="downloadPdfBtn">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </button>
                <button type="button" class="btn btn-info" id="downloadExcelBtn">
                    <i class="fas fa-file-excel"></i> Download Excel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Initialize date range picker
        $('#batches_date_range').daterangepicker({
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

        var payoutBatchListTable = $('#payoutBatchListTable').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                url: '{{ route("admin-payouts-batches-list") }}',
                data: function(d) {
                    d.search = $('#search').val();
                    d.batches_date_range = $('#batches_date_range').val();
                }
            },
            columns: [
                {
                    data: 'batch_no',
                    name: 'batch_no',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'batch_name',
                    name: 'batch_name',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'amount',
                    name: 'amount',
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'batch_valid_date',
                    name: 'batch_valid_date',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'notes',
                    name: 'notes',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'paid_date',
                    name: 'paid_date',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'paid_ref_no',
                    name: 'paid_ref_no',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'paid_by',
                    name: 'paid_by',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'remarks',
                    name: 'remarks',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [[3, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                processing: '<i class="fas fa-spinner fa-spin"></i> Loading...',
                emptyTable: 'No payout batches found'
            },
            drawCallback: function() {
            }
        });

        // Filter handlers
        $('#search').on('input', function() {
            payoutBatchListTable.ajax.reload();
        });

        $('#batches_date_range').on('apply.daterangepicker', function(ev, picker) {
            var startDate = picker.startDate.format('YYYY-MM-DD');
            var endDate = picker.endDate.format('YYYY-MM-DD');
            $(this).val(startDate + ' - ' + endDate);  
            payoutBatchListTable.ajax.reload();
        });

        $('#batches_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            payoutBatchListTable.ajax.reload();
        });

        // View Details Button Click
        $(document).on('click', '#payoutBatchListTable .view-details-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var batchId = $(this).data('id');
            console.log('Button clicked, Batch ID:', batchId); // Debug line
            
            // Show loading
            $('#batchDetailsContent').html(`
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            `);
            
            // Open modal
            $('#batchDetailsModal').modal('show');
            
            // Load details
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
                            <br><small>Error: ${xhr.responseJSON?.error || 'Unknown error'}</small>
                        </div>
                    `);
                }
            });
        });

        // Download PDF - Updated with AJAX
        $('#downloadPdfBtn').on('click', function() {
            var batchId = $(this).data('batch-id');
            if (batchId) {
                // Show loading state
                var $btn = $(this);
                var originalHtml = $btn.html();
                $btn.html('<i class="fas fa-spinner fa-spin"></i> Generating...');
                $btn.prop('disabled', true);
                
                // Use fetch API for better blob handling
                fetch('{{ route("admin-payouts-batches-download-pdf") }}?batchId=' + batchId)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok: ' + response.status);
                        }
                        return response.blob();
                    })
                    .then(blob => {
                        // Check if blob is valid
                        if (!blob || blob.size === 0) {
                            throw new Error('Empty response received');
                        }
                        
                        // Create download link
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = 'Batch_Payout_Details.pdf';
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);
                    })
                    .catch(error => {
                        console.error('Download error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Download Failed',
                            text: 'Failed to generate PDF. Please try again.',
                            confirmButtonColor: '#d33'
                        });
                    })
                    .finally(() => {
                        $btn.html(originalHtml);
                        $btn.prop('disabled', false);
                    });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Batch Selected',
                    text: 'Please load a batch first.'
                });
            }
        });

        // Download Excel
        $('#downloadExcelBtn').on('click', function() {
            var batchId = $(this).data('batch-id');
            if (batchId) {
                window.open('{{ route("admin-payouts-batches-download-excel") }}?batchId=' + batchId, '_blank');
            }
        });

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