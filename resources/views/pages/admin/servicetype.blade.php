@extends('layouts.backend')
@section('content')
<div class="content-wrapper">
	<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1 class="m-0">Service Type List</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
						<li class="breadcrumb-item active">Service Type</li>
					</ol>
				</div>
			</div>
		</div>
	</div>
    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @elseif ($message = Session::get('failed'))
        <div class="alert alert-danger">
            <p>{{ $message }}</p>
        </div>
    @endif
	<section class="content">
		<div class="container-fluid" id="viewServiceTypes">
			<div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Service Types</h3>
                            <button class="btn btn-warning float-right" type="button" name="addNewServiceType" id="addNewServiceType" data-toggle="modal" data-target="#modal-lg"><i class="fas fa-plus"></i> New Service Type </button>
                        </div>
                        <div class="card-body">
                            <table id="viewServiceTypesTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Service Types</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($serviceTypes as $serviceType)                                        
                                        <tr id="{{ $serviceType->pbst_id }}">
                                            <td>{{ $serviceType->pbst_id }}</td>
                                            <td>{{ $serviceType->pbst_name }}</td>
                                            @if( $serviceType->pbst_status == 1)
                                                <td><label class="ribbon bg-success">Active</label></td>
                                            @else
                                                <td><label class="ribbon bg-danger">Disabled</label></td>
                                            @endif
                                            @if( $serviceType->pbst_status == 1)
                                                <td>
                                                    <button class="btn btn-warning" type="button" name="serviceTypeEdit" id="serviceTypeEdit" data-toggle="modal" data-target="#editServiceType" value="{{ $serviceType->pbst_id }}"><i class="far fa-edit"></i></button>
                                                     | 
                                                    <a href="/delete_service_type/{{ $serviceType->pbst_id }}" class="btn btn-danger" type="button" name="serviceTypeDelete"><i class="fa fa-trash"></i></a>
                                                </td>                                            
                                            @else
                                            @endif 
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

    <div class="modal fade" id="modal-lg">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add New Service Type</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" name="addNewServiceTypeForm" id="addNewServiceTypeForm" action="/insertServiceType">
                        @csrf
                        <div class="form-group">
                            <label for="newServiceType" class="col-form-label">Service Type</label>
                            <input type="text" class="form-control" id="newServiceType" name="newServiceType" placeholder="Enter the Service Type" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-warning float-right" name="newServiceTypeSubmit" id="newServiceTypeSubmit">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editServiceType">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Service Type</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" name="editServiceTypeForm" id="editServiceTypeForm" action="/updateServiceType">
                        @csrf
                        <input type="hidden" class="form-control" id="editServiceTypeID" name="editServiceTypeID">
                        <div class="form-group">
                            <label for="editServiceType" class="col-form-label">Service Type</label>
                            <input type="text" class="form-control" id="editServiceType" name="editServiceType" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-warning float-right" name="editServiceTypeSubmit" id="editServiceTypeSubmit">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('body').on('click', '#serviceTypeEdit', function(e){
        var id = $(this).val();
        $.ajax({
            type: 'GET',
            url: '/get_service_type/' + id,
            dataType: 'json',
            success: function(data) {
                // console.log(data.pbsc_id);
                $('#editServiceType #editServiceTypeID').val(data.pbst_id);
                $('#editServiceType #editServiceType').val(data.pbst_name);
            }
        });
    });
</script>
@stop