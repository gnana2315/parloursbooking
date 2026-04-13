@extends('layouts.backend')
@section('content')
<div class="content-wrapper">
	<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1 class="m-0">Payouts</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
						<li class="breadcrumb-item active">Payouts</li>
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
                            <h3 class="card-title">Payouts</h3>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Vendor</th>
                                        <th>Total Earned</th>
                                        <th>Total Paid</th>
                                        <th>Total Due</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payouts as $payout)
                                        <tr id="payout-{{ $payout->vendors->pbv_id }}">
                                            <td>
                                                {{ $payout->vendors->pbv_business_name }}
                                            </td>
                                            <td>{{ 'Rs. ' . number_format($payout->pbvp_total_earned, 2) }}</td>
                                            <td>{{ 'Rs. ' . number_format($payout->pbvp_total_paid, 2) }}</td>
                                            <td>{{ 'Rs. ' . number_format($payout->pbvp_total_due, 2) }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary view_payout_history" title="View Payout History" data-vendor-id="{{$payout->vendors->pbv_id}}"><i class="fas fa-eye"></i></button>
                                                <button type="button" class="btn btn-sm btn-success make_payout" title="Make Payout" data-vendor-id="{{ $payout->vendors->pbv_id }}"><i class="fa fa-money-bill"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
			</div>
		</div>

        <div class="modal fade" id="payoutHistoryModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Payout History</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body" id="payoutHistoryModalBody">
                        <!-- AJAX content loads here -->
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="makePayoutViewModel" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5>Make Payout</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body" id="makePayoutViewModelBody">
                        <!-- AJAX content loads here -->
                    </div>
                </div>
            </div>
        </div>
	</section>
</div>
<script>
    $(document).ready(function () {
        $(function () {
            $("#example1").DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": true,
                "paging": true, 
                "buttons": ["csv", "excel", "pdf", "print"]
            }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
        });

        //View payout history
        $('.view_payout_history').on('click', function() {
            var vendorId = $(this).data('vendor-id');
            // Make an AJAX request to fetch payout history for the selected vendor
            $.ajax({
                url: '{{ route("payouts.history", "") }}/' + vendorId, 
                method: 'GET',
                success: function(response) {
                    $('#payoutHistoryModalBody').html(response);
                    $('#payoutHistoryModal').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching payout history:', error);
                    // Optionally, show an error message to the user
                }
            });
        });

        $('.make_payout').on('click', function() {
            var vendorId = $(this).data('vendor-id');
            $.ajax({
                url: '{{ route("payouts.make", "") }}/' + vendorId, 
                method: 'GET',
                success: function(response) {
                    $('#makePayoutViewModelBody').html(response);
                    $('#makePayoutViewModel').modal('show');
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching payout details:', error);
                    // Optionally, show an error message to the user
                }
            });
        });
        
    });
</script>
@stop