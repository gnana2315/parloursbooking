@extends('layouts.backend')
@section('content')
<div class="content-wrapper">
	<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1 class="m-0">Service List</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
						<li class="breadcrumb-item active">Services</li>
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
    <?php 
        $services = $data['services'];
        $serviceCategories = $data['serviceCategories'];
        $serviceTypes = $data['serviceTypes'];
    ?>
	<section class="content">
		<div class="container-fluid" id="viewServices">
			<div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Service Lists</h3>
                            <button class="btn btn-warning float-right" type="button" name="addNewService" id="addNewService" data-toggle="modal" data-target="#modal-lg"><i class="fas fa-plus"></i> New Service </button>
                        </div>
                        <div class="card-body">
                            <span><strong>Note: </strong>Our commision(10%) will add with your price</span>
                            <table id="viewServicesTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Service For</th>
                                        <th>Service Type</th>
                                        <th>Services</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($services as $service)
                                        <tr id="{{ $service->pbs_id }}">
                                            <td>{{ $service->pbs_id }}</td>
                                            <td>{{ $service->pbsc_name }}</td>
                                            <td>{{ $service->pbst_name }}</td>
                                            <td>{{ $service->pbs_name }}</td>
                                            <td>{{ $service->pbs_charges }}</td>
                                            @if( $service->pbs_status == 1)
                                                <td><label class="ribbon bg-success">Active</label></td>
                                            @else
                                                <td><label class="ribbon bg-danger">Disabled</label></td>
                                            @endif
                                            @if( $service->pbs_status == 1)
                                                <td>
                                                    <button class="btn btn-warning" type="button" name="serviceEdit" id="serviceEdit" data-toggle="modal" data-target="#editService" value="{{ $service->pbs_id }}"><i class="far fa-edit"></i></button>
                                                     | 
                                                    <a href="/delete_service/{{ $service->pbs_id }}" class="btn btn-danger" type="button" name="serviceCategoryDelete"><i class="fa fa-trash"></i></a>
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
                    <h4 class="modal-title">Add New Service</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" name="addNewServiceForm" id="addNewServiceForm" action="/insertService">
                        @csrf
                        <div class="row">
                            <div class="col-lg-3 col-md-3 col-xl-3">
                                <div class="form-group">
                                    <label for="newServiceCategory" class="col-form-label">Service Category</label>
                                    <select class="form-control select" id="newServiceCategory" name="newServiceCategory" required>
                                        @foreach($serviceCategories as $serviceCategory)
                                            <option value='{{ $serviceCategory->pbsc_id }}'>{{ $serviceCategory->pbsc_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-xl-3">
                                <div class="form-group">
                                    <label for="newServiceType" class="col-form-label">Service Type</label>
                                    <select class="form-control select" id="newServiceType" name="newServiceType" required>
                                        @foreach($serviceTypes as $serviceType)
                                            <option value='{{ $serviceType->pbst_id }}'>{{ $serviceType->pbst_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label for="newServiceName" class="col-form-label">Service Name</label>
                                    <input type="text" class="form-control" id="newServiceName" name="newServiceName" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-8 col-md-8 col-xl-8">
                                <div class="form-group">
                                    <label for="newServiceDes" class="col-form-label">Service Description</label>
                                    <textarea class="form-control" id="newServiceDes" name="newServiceDes"></textarea>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-xl-4">
                                <div class="form-group">
                                    <label for="newServicePrice" class="col-form-label">Service Price</label>
                                    <input type="text" class="form-control" id="newServicePrice" name="newServicePrice" required/>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-warning float-right" name="newServiceSubmit" id="newServiceSubmit">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editService">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Service</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" name="editServiceForm" id="editServiceForm" action="/updateService">
                        @csrf
                        <input type="hidden" class="form-control" id="editServiceID" name="editServiceID">
                        <div class="row">
                            <div class="col-lg-3 col-md-3 col-xl-3">
                                <div class="form-group">
                                    <label for="editServiceCategory" class="col-form-label">Service Category</label>
                                    <select class="form-control select" id="editServiceCategory" name="editServiceCategory" required>
                                        @foreach($serviceCategories as $serviceCategory)
                                            <option value='{{ $serviceCategory->pbsc_id }}'>{{ $serviceCategory->pbsc_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-xl-3">
                                <div class="form-group">
                                    <label for="editServiceType" class="col-form-label">Service Type</label>
                                    <select class="form-control select" id="editServiceType" name="editServiceType" required>
                                        @foreach($serviceTypes as $serviceType)
                                            <option value='{{ $serviceType->pbst_id }}'>{{ $serviceType->pbst_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label for="editServiceName" class="col-form-label">Service Name</label>
                                    <input type="text" class="form-control" id="editServiceName" name="editServiceName" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-8 col-md-8 col-xl-8">
                                <div class="form-group">
                                    <label for="editServiceDes" class="col-form-label">Service Description</label>
                                    <textarea class="form-control" id="editServiceDes" name="editServiceDes"></textarea>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-xl-4">
                                <div class="form-group">
                                    <label for="editServicePrice" class="col-form-label">Service Price</label>
                                    <input type="text" class="form-control" id="editServicePrice" name="editServicePrice" required/>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-warning float-right" name="editServiceSubmit" id="editServiceSubmit">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('body').on('click', '#serviceEdit', function(e){
        var id = $(this).val();
        $.ajax({
            type: 'GET',
            url: '/get_service/' + id,
            dataType: 'json',
            success: function(data) {
                // console.log(data);
                $('#editService #editServiceID').val(data.pbs_id);
                $('#editService #editServiceCategory').val(data.pbs_servicefor_id);
                $('#editService #editServiceType').val(data.pbs_category_id);
                $('#editService #editServiceName').val(data.pbs_name);
                $('#editService #editServiceDes').val(data.pbs_description);
                $('#editService #editServicePrice').val(data.pbs_charges);
            }
        });
    });
</script>
@stop