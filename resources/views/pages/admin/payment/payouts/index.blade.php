@extends('layouts.backend')
@section('content')
<div class="content-wrapper">
	<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1 class="m-0">Payouts List</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="#">Home</a></li>
						<li class="breadcrumb-item">Payment Dashboard</li>
						<li class="breadcrumb-item active">Payouts</li>
					</ol>
				</div>
			</div>
		</div>
	</div>

	@include('pages.admin.payment.payouts.list');
</div>
@stop