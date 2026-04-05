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
                                        <tr id="payout-{{ $payout->pbv_id }}">
                                            <td>
                                                {{ $payout->vendors->pbv_business_name }}
                                            </td>
                                            <td>{{ 'Rs. ' . number_format($payout->pbvp_total_earned, 2) }}</td>
                                            <td>{{ 'Rs. ' . number_format($payout->pbvp_total_paid, 2) }}</td>
                                            <td>{{ 'Rs. ' . number_format($payout->pbvp_total_due, 2) }}</td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-primary">View Details</a>
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
    });
</script>
@stop