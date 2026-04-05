@extends('layouts.backend')
@section('content')
<div class="content-wrapper">
	<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1 class="m-0">Payment Transactions</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
						<li class="breadcrumb-item active">Payment Transactions</li>
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
                            <h3 class="card-title">Payment Transactions</h3>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Transection</th>
                                        <th>Amount</th>
                                        <th>Discount</th>
                                        <th>Total</th>
                                        <th>Platform fee</th>
                                        <th>Vendor payable</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paymentTransections as $transection)
                                        <tr id="transection-{{ $transection->pbpt_id }}">
                                            <td>
                                                {{ $transection->pbpt_transaction_id }}
                                                <br>
                                                <small>{{ $transection->created_at }}</small>
                                                <br>
                                                <span class="badge badge-primary">{{ $transection->booking->pbb_ref_no }}</span> 
                                                | 
                                                <span class="badge badge-secondary">{{ $transection->vendor->pbv_business_name }}</span> 
                                                | 
                                                <span class="badge badge-info">{{ $transection->customer->pbc_first_name }} {{ $transection->customer->pbc_last_name }}</span>
                                            </td>
                                            <td>{{ 'Rs. ' . number_format($transection->pbpt_total_amount, 2) }}</td>
                                            <td>
                                                @if(@$transection->pbpt_platform_share != 0)
                                                    {{ 'Rs. ' . number_format($transection->pbpt_platform_discount_amount, 2) }} 
                                                    |
                                                    {{ 'Rs. ' . number_format($transection->pbpt_discount_amount, 2) }}
                                                @else
                                                    {{ 'Rs. ' . number_format($transection->pbpt_discount_amount, 2) }} 
                                                @endif
                                            </td>
                                            <td>{{ 'Rs. ' . number_format($transection->pbpt_final_amount, 2) }}</td>
                                            <td>{{ 'Rs. ' . number_format($transection->pbpt_platform_fee, 2) }}</td>
                                            <td>{{ 'Rs. ' . number_format($transection->pbpt_vendor_amount, 2) }}</td>
                                            <td>
                                                <button class="btn btn-warning" title="Account Summary"><i class="fa fa-clipboard-list"></i></button>
                                                <button class="btn btn-success" title="Make Payment"><i class="fa fa-money-bill"></i></button>
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