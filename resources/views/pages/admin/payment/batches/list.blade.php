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

<!-- Mark Batch Modal -->
<div class="modal fade" id="markBatchModal" tabindex="-1" role="dialog" aria-labelledby="markBatchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title" id="markBatchModalLabel">
                    <i class="fas fa-check-circle"></i> Mark Batch as Paid
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="markBatchForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="text-center py-5">
                        <div class="spinner-border text-success" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading batch details...</p>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success" id="submitMarkBatchBtn">
                        <i class="fas fa-check"></i> Mark as Paid
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
            

<!-- View Payment Proof Modal -->
<div class="modal fade" id="viewProofModal" tabindex="-1" role="dialog" aria-labelledby="viewProofModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h5 class="modal-title" id="viewProofModalLabel">
                    <i class="fas fa-file-invoice"></i> Payment Proof
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewProofContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-info" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading payment proof...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-info" id="downloadProofBtn">
                    <i class="fas fa-download"></i> Download
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Payment Receipt Modal -->
<div class="modal fade" id="viewReceiptModal" tabindex="-1" role="dialog" aria-labelledby="viewReceiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary">
                <h5 class="modal-title" id="viewReceiptModalLabel">
                    <i class="fas fa-receipt"></i> Payment Receipt
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewReceiptContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-secondary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading payment receipt...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Close
                </button>
                <button type="button" class="btn btn-secondary" id="downloadReceiptBtn">
                    <i class="fas fa-download"></i> Download
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

        // Mark Batch Button Click
        $(document).on('click', '.mark-batch-btn', function() {
            var batchId = $(this).data('id');
            
            // Show loading
            $('#markBatchModal .modal-body').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading batch details...</p>
                </div>
            `);
            
            // Open modal
            $('#markBatchModal').modal('show');
            
            // Load batch details
            $.ajax({
                url: '{{ route("admin-payouts-batches-get-batch") }}',
                method: 'GET',
                data: { batchId: batchId },
                success: function(response) {
                    // Reset form and update modal content
                    resetMarkBatchForm();
                    $('#markBatchModal .modal-body').html(response);
                    
                    // Re-attach file input change event
                    $('#payment_proof').on('change', function() {
                        var fileName = $(this).val().split('\\').pop();
                        $('.custom-file-label').text(fileName);
                        if (fileName) {
                            $('#fileName').text(fileName);
                            $('#filePreview').show();
                        } else {
                            $('#filePreview').hide();
                        }
                    });
                },
                error: function(xhr) {
                    $('#markBatchModal .modal-body').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> 
                            Failed to load batch details. Please try again.
                        </div>
                    `);
                }
            });
        });

        // Reset Mark Batch Form
        function resetMarkBatchForm() {
            $('#markBatchForm')[0].reset();
            $('#filePreview').hide();
            $('.custom-file-label').text('Choose file...');
            $('#submitMarkBatchBtn').prop('disabled', false).html('<i class="fas fa-check"></i> Mark as Paid');
        }

        // Handle Mark Batch Form Submission
        $('#markBatchForm').on('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            var paidDate = $('#paid_date').val();
            var paidRefNo = $('#paid_ref_no').val().trim();
            var paidBy = $('#paid_by').val().trim();
            var paymentProof = $('#payment_proof')[0].files[0];
            var batchId = $('#batch_id').val();
            
            if (!paidDate) {
                Swal.fire('Error', 'Please select a paid date.', 'error');
                return;
            }
            
            if (!paidRefNo) {
                Swal.fire('Error', 'Please enter a payment reference number.', 'error');
                return;
            }
            
            if (!paidBy) {
                Swal.fire('Error', 'Please enter who made the payment.', 'error');
                return;
            }
            
            if (!paymentProof) {
                Swal.fire('Error', 'Please upload a payment proof.', 'error');
                return;
            }
            
            // Show loading
            $('#submitMarkBatchBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
            
            // Create FormData
            var formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('batch_id', batchId);
            formData.append('paid_date', paidDate);
            formData.append('paid_ref_no', paidRefNo);
            formData.append('paid_by', paidBy);
            formData.append('payment_proof', paymentProof);
            formData.append('remarks', $('#remarks').val().trim());
            
            // Send AJAX request
            $.ajax({
                url: '{{ route("admin-payouts-batches-mark") }}',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message || 'Batch marked as paid successfully.',
                            confirmButtonColor: '#28a745'
                        }).then(() => {
                            // Close modal
                            $('#markBatchModal').modal('hide');
                            // Reload DataTable
                            $('#payoutBatchListTable').DataTable().ajax.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to mark batch as paid.'
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
                },
                complete: function() {
                    $('#submitMarkBatchBtn').prop('disabled', false).html('<i class="fas fa-check"></i> Mark as Paid');
                }
            });
        });

        // Reset modal when closed
        $('#markBatchModal').on('hidden.bs.modal', function() {
            resetMarkBatchForm();
        });

        // View Payment Proof
        $(document).on('click', '.view-proof-btn', function() {
            var batchId = $(this).data('id');
            
            // Show loading
            $('#viewProofContent').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-info" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading payment proof...</p>
                </div>
            `);
            
            // Open modal
            $('#viewProofModal').modal('show');
            
            // Load proof
            $.ajax({
                url: '{{ route("admin-payouts-batches-view-proof") }}',
                method: 'GET',
                data: { batchId: batchId },
                success: function(response) {
                    if (response.success) {
                        var html = '';
                        
                        if (response.file_type === 'pdf') {
                            html = `
                                <div class="text-center">
                                    <embed src="${response.file_url}" type="application/pdf" width="100%" height="600px" />
                                    <p class="mt-2 text-muted">File: ${response.file_name}</p>
                                </div>
                            `;
                        } else if (response.file_type === 'image') {
                            html = `
                                <div class="text-center">
                                    <img src="${response.file_url}" class="img-fluid" alt="Payment Proof" style="max-height: 600px;" />
                                    <p class="mt-2 text-muted">File: ${response.file_name}</p>
                                </div>
                            `;
                        } else {
                            html = `
                                <div class="text-center">
                                    <i class="fas fa-file fa-5x text-muted"></i>
                                    <p class="mt-3">File: ${response.file_name}</p>
                                    <a href="${response.file_url}" class="btn btn-info" download>
                                        <i class="fas fa-download"></i> Download File
                                    </a>
                                </div>
                            `;
                        }
                        
                        $('#viewProofContent').html(html);
                        $('#downloadProofBtn').data('file-url', response.file_url);
                        $('#downloadProofBtn').data('file-name', response.file_name);
                    } else {
                        $('#viewProofContent').html(`
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> 
                                ${response.message || 'No payment proof found for this batch.'}
                            </div>
                        `);
                        $('#downloadProofBtn').data('file-url', null);
                    }
                },
                error: function(xhr) {
                    $('#viewProofContent').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> 
                            Failed to load payment proof. Please try again.
                        </div>
                    `);
                    $('#downloadProofBtn').data('file-url', null);
                }
            });
        });

        // View Payment Receipt
        $(document).on('click', '.view-payment-receipt-btn', function() {
            var batchId = $(this).data('id');
            
            // Show loading
            $('#viewReceiptContent').html(`
                <div class="text-center py-5">
                    <div class="spinner-border text-secondary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2">Loading payment receipt...</p>
                </div>
            `);
            
            // Open modal
            $('#viewReceiptModal').modal('show');
            
            // Load receipt
            $.ajax({
                url: '{{ route("admin-payouts-batches-view-receipt") }}',
                method: 'GET',
                data: { batchId: batchId },
                success: function(response) {
                    if (response.success) {
                        // Build receipt HTML
                        var html = `
                            <div class="receipt-wrapper">
                                <div class="receipt-header text-center">
                                    <h4>PAYMENT RECEIPT</h4>
                                    <p class="text-muted">Batch #${response.batch_no}</p>
                                </div>
                                <div class="receipt-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Batch No:</strong> ${response.batch_no}</p>
                                            <p><strong>Batch Name:</strong> ${response.batch_name || 'N/A'}</p>
                                            <p><strong>Total Amount:</strong> Rs. ${response.total_amount}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Paid Date:</strong> ${response.paid_date}</p>
                                            <p><strong>Reference No:</strong> ${response.paid_ref_no || 'N/A'}</p>
                                            <p><strong>Paid By:</strong> ${response.paid_by || 'N/A'}</p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="vendor-details">
                                        <h6>Vendor Payout Details</h6>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Vendor Name</th>
                                                        <th>Amount (Rs.)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>`;
                        
                        if (response.vendors && response.vendors.length > 0) {
                            response.vendors.forEach(function(vendor, index) {
                                html += `
                                                    <tr>
                                                        <td>${index + 1}</td>
                                                        <td>${vendor.vendor_name}</td>
                                                        <td class="text-right">Rs. ${vendor.amount}</td>
                                                    </tr>`;
                            });
                        }
                        
                        html += `
                                                </tbody>
                                                <tfoot>
                                                    <tr class="font-weight-bold">
                                                        <td colspan="2" class="text-right">Total:</td>
                                                        <td class="text-right">Rs. ${response.total_amount}</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                    ${response.remarks ? `
                                    <div class="remarks mt-3">
                                        <p><strong>Remarks:</strong> ${response.remarks}</p>
                                    </div>` : ''}
                                </div>
                                <div class="receipt-footer text-center text-muted mt-4">
                                    <small>Generated on ${new Date().toLocaleString()}</small>
                                </div>
                            </div>
                        `;
                        
                        $('#viewReceiptContent').html(html);
                        $('#downloadReceiptBtn').data('batch-id', batchId);
                    } else {
                        $('#viewReceiptContent').html(`
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> 
                                ${response.message || 'No payment receipt found for this batch.'}
                            </div>
                        `);
                        $('#downloadReceiptBtn').data('batch-id', null);
                    }
                },
                error: function(xhr) {
                    $('#viewReceiptContent').html(`
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> 
                            Failed to load payment receipt. Please try again.
                        </div>
                    `);
                    $('#downloadReceiptBtn').data('batch-id', null);
                }
            });
        });

        // Download Proof
        $('#downloadProofBtn').on('click', function() {
            var fileUrl = $(this).data('file-url');
            var fileName = $(this).data('file-name') || 'payment-proof';
            
            if (fileUrl) {
                var a = document.createElement('a');
                a.href = fileUrl;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'No File',
                    text: 'No payment proof available to download.'
                });
            }
        });

        // Download Receipt
        $('#downloadReceiptBtn').on('click', function() {
            var batchId = $(this).data('batch-id');
            
            if (batchId) {
                window.open('{{ route("admin-payouts-batches-download-receipt") }}?batchId=' + batchId, '_blank');
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Receipt',
                    text: 'No payment receipt available to download.'
                });
            }
        });
    });
</script>