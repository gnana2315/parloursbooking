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
                                        <th>BR No</th>
                                        <th>Vendor</th>
                                        <th>Owner Name</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($vendors as $vendor)
                                        <tr id="{{ $vendor->pbv_id }}">
                                            <td> {{ $vendor->pbv_brno ?? 'N/A' }}</td>
                                            <td> 
                                                {{ $vendor->pbv_business_name ?? 'N/A' }}
                                                <br>
                                                <small>
                                                    <b>Type: </b>{{ $vendor->vendorType->pbvt_name ?? 'N/A' }}
                                                    <br>
                                                    <b>Service For: </b>{{ $vendor->serviceFor->pbsf_name ?? 'N/A' }}
                                                    <br>
                                                    {{ $vendor->pbv_email}} | {{ $vendor->pbv_contactno}}
                                                </small>
                                            </td>
                                            <td> 
                                                {{ $vendor->pbv_first_name ?? '' }} {{ $vendor->pbv_last_name ?? '' }}
                                                <br>
                                                <small>
                                                    <b>Email: </b>{{ $vendor->user->pbu_email ?? '' }}
                                                    <br>
                                                    <b>Mobile No: </b>{{ $vendor->user->pbu_mobileno ?? '' }}
                                                </small>
                                            </td>
                                            <td>
                                                <select class="form-select vendor-service-for"
                                                        data-vendor-id="{{ $vendor->pbv_id }}"
                                                        data-old-value="{{ $vendor->pbv_servicefor }}"
                                                        {{ !$vendor->user ? 'disabled' : '' }}>

                                                    <option value="">Select Service For</option>

                                                    @foreach($serviceForList as $serviceFor)
                                                        <option value="{{ $serviceFor->pbsf_id }}"
                                                            {{ $vendor->pbv_servicefor == $serviceFor->pbsf_id ? 'selected' : '' }}>
                                                            {{ $serviceFor->pbsf_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                |
                                                <select class="form-select vendor-status"
                                                    data-vendor-id="{{ $vendor->pbv_id ?? '' }}"
                                                    data-old-value="{{ $vendor->pbv_status ?? '' }}"
                                                    @if(!$vendor->user) Disabled @endif>
                                                    <option value="2" {{ ($vendor->pbv_status == 2) ? 'selected' : '' }}>
                                                        Active
                                                    </option>
                                                    <option value="1" {{ ($vendor->pbv_status == 1) ? 'selected' : '' }}>
                                                        Admin need to Verify
                                                    </option>
                                                </select>
                                            </td>
                                            <td>
                                                <a href="{{ route('vendor.view', $vendor->pbv_id) }}" class="btn btn-info" type="button" title="View Vendor Details" name="viewVendor" id="viewVendor"><i class="fa fa-eye"></i></a>
                                                |
                                                <button class="btn btn-success" type="button" title="Print Vendor Details" name="printVendor" id="printVendor" value="{{ $vendor->pbv_id }}"><i class="fa fa-print"></i></button>
                                                |
                                                @if( $vendor->user->pbu_status == 1)
                                                    <button class="btn btn-danger" type="button" title="Disable Vendor" name="disableVendor" id="disableVendor" value="{{ $vendor->user->pbu_id }}"><i class="fa fa-power-off"></i></button>
                                                @else
                                                    <!--button class="btn btn-success" type="button" title="Approve Vendor" name="enableVendor" id="enableVendor" value="{{ $vendor->user->pbu_id }}"><i class="fa fa-power-off"></i></button-->
                                                    <form action="/vendor/activate" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="id" value="{{ $vendor->user->pbu_id }}" />
                                                        <button class="btn btn-success" type="submit" title="Approve Vendor"><i class="fa fa-power-off"></i></button>
                                                    </form>
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
<script>
    $(document).ready(function () {
        // Change Vendor Status
        $('.vendor-status').on('change', function () {
            let select   = $(this);
            let vendorId = select.data('vendor-id');
            let status   = select.val();
            let oldValue = select.data('old-value');
            if (!vendorId) return;

            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to change vendor status?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, change it',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    select.prop('disabled', true);
                    $.ajax({
                        url: "{{ route('vendor.updateStatus') }}",
                        type: "POST",
                        data: {
                            vendor_id: vendorId,
                            status: status,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (response) {
                            if (response.success) {
                                Swal.fire(
                                    'Updated!',
                                    response.message,
                                    'success'
                                );
                                select.data('old-value', status);
                            } else {
                                Swal.fire('Error', response.message, 'error');
                                select.val(oldValue); 
                            }
                        },
                        error: function (xhr) {
                            let message = 'An error occurred while updating the status.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }

                            Swal.fire('Error', message, 'error');
                            select.val(oldValue); 
                        },
                        complete: function () {
                            select.prop('disabled', false);
                        }
                    });
                } else {
                    select.val(oldValue);
                }
            });
        });

        // Change Vendor Service For Category
        $('.vendor-service-for').on('change', function () {
            let select   = $(this);
            let vendorId = select.data('vendor-id');
            let serviceFor = select.val();
            let oldValue = select.data('old-value');
            if (!vendorId) return;

            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to change Vendor Service For Category?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, change it',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    select.prop('disabled', true);
                    $.ajax({
                        url: "{{ route('vendor.updateServiceFor') }}",
                        type: "POST",
                        data: {
                            vendor_id: vendorId,
                            serviceFor: serviceFor,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (response) {
                            if (response.success) {
                                Swal.fire(
                                    'Updated!',
                                    response.message,
                                    'success'
                                );
                                select.data('old-value', serviceFor);
                            } else {
                                Swal.fire('Error', response.message, 'error');
                                select.val(oldValue); 
                            }
                        },
                        error: function (xhr) {
                            let message = 'An error occurred while updating the status.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }

                            Swal.fire('Error', message, 'error');
                            select.val(oldValue); 
                        },
                        complete: function () {
                            select.prop('disabled', false);
                        }
                    });
                } else {
                    select.val(oldValue);
                }
            });
        });

        $('#enableVendor').on('click', function(e){
            e.preventDefault();
            var vendorID = $(this).val();
            alert(vendorID);
            $.ajax({
                url: '/vendor/activate',
                type: 'POST',
                data: {
                    id: vendorID,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    console.log(response);
                },
                error: function(xhr, status, error) {
                    console.log('xhr: '+xhr);
                    console.log('status: '+status);
                    console.log('error: '+error);
                }
            });
        });

    });
</script>
@stop