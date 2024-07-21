@extends('layouts.backend')
@section('content')
<div class="content-wrapper">
	<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1 class="m-0">Vendors List</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
						<li class="breadcrumb-item active">Vendors List</li>
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
                            <h3 class="card-title">Registered Vendor List</h3>
                        </div>
                        <div class="card-body">
                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Business Type</th>
                                        <th>Business Name</th>
                                        <th>Owner Name</th>
                                        <th>Contact No</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($vendors as $vendor)
                                        <tr id="{{ $vendor->pbv_id }}">
                                            <td> {{ $vendor->pbv_id }}</td>
                                            <td> {{ $vendor->pbv_servicetype }}</td>
                                            <td> {{ $vendor->pbv_name }}</td>
                                            <td> {{ $vendor->pbp_intial }}. {{ $vendor->pbp_firstname }} {{ $vendor->pbp_lastname }}</td>
                                            @if($vendor->pbv_contactno != $vendor->pbp_contactno)
                                                <td> {{ $vendor->pbv_contactno }} | {{ $vendor->pbp_contactno }}</td>
                                            @else
                                                <td> {{ $vendor->pbv_contactno }} </td>
                                            @endif
                                            @if( $vendor->pbp_status == 1)
                                                <td><label class="ribbon bg-success">Active</label></td>
                                            @else
                                                <td><label class="ribbon bg-danger">Admin need to Verify</label></td>
                                            @endif
                                            <td>
                                                <a href="/viewVendor/{{ $vendor->pbv_id }}" class="btn btn-info" type="button" title="View Vendor Details" name="viewVendor" id="viewVendor" target="_blank"><i class="fa fa-eye"></i></a>
                                                |
                                                <button class="btn btn-success" type="button" title="Print Vendor Details" name="printVendor" id="printVendor" value="{{ $vendor->pbv_id }}"><i class="fa fa-print"></i></button>
                                                |
                                                @if( $vendor->pbu_status == 1)
                                                    <button class="btn btn-danger" type="button" title="Disable Vendor" name="disableVendor" id="disableVendor" value="{{ $vendor->pbu_id }}"><i class="fa fa-power-off"></i></button>
                                                @else
                                                    <button class="btn btn-success" type="button" title="Approve Vendor" name="enableVendor" id="enableVendor" value="{{ $vendor->pbu_id }}"><i class="fa fa-power-off"></i></button>
                                                    <!-- <form method="POST" action="/enable_vendor">@csrf<input type="hidden" name="enableuid" value="{{ $vendor->pbu_id }}" /><button type="submit" class="btn btn-success" title="Approve Vendor" name="enableVendor" id="enableVendor"><i class="fa fa-power-off"></i></button></form> -->
                                                @endif
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
@stop