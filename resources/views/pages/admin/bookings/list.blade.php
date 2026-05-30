@extends('layouts.backend')
@section('content')
<div class="content-wrapper">
	<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1 class="m-0">Booking List</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
						<li class="breadcrumb-item active">Booking List</li>
					</ol>
				</div>
			</div>
		</div>
	</div>

	<section class="content">
		<div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                Booing List Filters
                            </h3>
                        </div>
                        <dib class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" id="booking_search" class="form-control form-control-sm" placeholder="Search by Booking Ref, Customer first Name or Vendor Name, Customer or Vendor Contact No">
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="far fa-calendar-alt"></i>
                                        </span>
                                        </div>
                                        <input type="text" class="form-control float-right" id="booking_date_range">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group input-group-sm">
                                        <select id="statusFilter" class="form-control">
                                            <option value="">All Status</option>
                                            <option value="0">Cancelled By Admin</option>
                                            <option value="1">Upcoming</option>
                                            <option value="2">Completed</option>
                                            <option value="3">Payment Pending</option>
                                            <option value="4">DNA</option>
                                            <option value="5">Payment Failure</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </dib>
                    </div>
                </div>
            </div>
			<div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Booking List</h3>
                        </div>
                        <div class="card-body">
                            <table id="bookingsTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Booking Ref</th>
                                        <th>Customer Name</th>
                                        <th>Vendor Name</th>
                                        <th>Booking Date</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfooter>
                                    <tr>
                                        <th colspan="4" style="text-align:right">Total:</th>
                                        <th id="totalAmountFooter"></th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfooter>                    
                            </table>
                        </div>
                    </div>
                </div>
			</div>
		</div>
        <!-- Booking Details Modal -->
        <div class="modal fade" id="bookingDetailsModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content" id="bookingDetailsContent">
                    <div class="modal-body text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	</section>
</div>
<script>
    $(document).ready(function() {
        $(function () {
            $('#booking_date_range').daterangepicker({
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
        });

        var bookingsListTable = $('#bookingsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("admin-bookings-list") }}',
                data: function(d) {
                    d.booking_search = $('#booking_search').val();
                    d.booking_date_range = $('#booking_date_range').val();
                    d.status = $('#statusFilter').val();
                }
            },
            columns: [
                {
                    data: 'pbb_ref_no',
                    name: 'pbb_ref_no',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'customer_name',
                    name: 'customer_name',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'vendor_name',
                    name: 'vendor_name',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'booking_date',
                    name: 'booking_date',
                    orderable: true,
                    searchable: true
                },
                {
                    data: 'total_amount',
                    name: 'total_amount',
                    orderable: true
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [[3, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                processing: '<i class="fas fa-spinner fa-spin"></i> Loading...',
                emptyTable: 'No bookings found'
            },
            drawCallback: function() {
                // Update total amount in footer
                var totalAmount = 0;
                var data = bookingsListTable.rows().data();
                for (var i = 0; i < data.length; i++) {
                    var amountText = data[i].total_amount || 'Rs. 0.00';
                    var amount = parseFloat(amountText.replace('Rs. ', '').replace(/,/g, '')) || 0;
                    totalAmount += amount; 
                }
                $('#totalAmountFooter').text('Rs. ' + totalAmount.toFixed(2));
            }
        });

        $('#booking_search').on('input', function() {
            bookingsListTable.ajax.reload();
        });

        $('#booking_date_range').on('apply.daterangepicker', function(ev, picker) {
            var startDate = picker.startDate.format('YYYY-MM-DD');
            var endDate = picker.endDate.format('YYYY-MM-DD');
            $(this).val(startDate + ' - ' + endDate);  
            bookingsListTable.ajax.reload();
        });


        $('#booking_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            bookingsListTable.ajax.reload();
        });

        $('#statusFilter').change(function() {
            bookingsListTable.ajax.reload();
        });

        $('#bookingsTable').on('click', '#showBookingBtn', function() {
            var bookingId = $(this).data('id');
            $.ajax({
                url: '/bookings/' + bookingId + '/details',
                method: 'GET',
                success: function(response) {
                    $('#bookingDetailsContent').html(response);
                    $('#bookingDetailsModal').modal({
                        backdrop: 'static',
                        keyboard: true
                    }).modal('show');
                },
                error: function(xhr, error) {
                    console.log(xhr);
                    console.log(error);
                    alert('Failed to fetch booking details. Please try again.');
                }
            });
        });

        // function showBooking(booking_id){
        //     // Implement the logic to show booking details in a modal which show the details
        //     $.ajax({
        //         url: '/bookings/' + booking_id + '/details',
        //         method: 'GET',
        //         success: function(response) {
        //             $('#bookingDetailsContent').html(response);
        //             $('#bookingDetailsModal').modal({
        //                 backdrop: 'static',
        //                 keyboard: true
        //             }).modal('show');
        //         },
        //         error: function(xhr) {
        //             alert('Failed to fetch booking details. Please try again.');
        //         }
        //     });
        // }
    });
</script>
@stop