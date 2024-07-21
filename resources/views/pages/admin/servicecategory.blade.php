@extends('layouts.backend')
@section('content')
<div class="content-wrapper">
	<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1 class="m-0">Service Categories List</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
						<li class="breadcrumb-item active">Service Categories</li>
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
		<div class="container-fluid" id="viewServiceCategories">
			<div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Service Categories</h3>
                            <button class="btn btn-warning float-right" type="button" name="addNewServiceCategory" id="addNewServiceCategory" data-toggle="modal" data-target="#modal-lg"><i class="fas fa-plus"></i> New Service Category </button>
                        </div>
                        <div class="card-body">
                            <table id="viewServiceCategoriesTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Service Categories</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($serviceCategories as $serviceCategory)                                        
                                        <tr id="{{ $serviceCategory->pbsc_id }}">
                                            <td>{{ $serviceCategory->pbsc_id }}</td>
                                            <td>{{ $serviceCategory->pbsc_name }}</td>
                                            @if( $serviceCategory->pbsc_status == 1)
                                                <td><label class="ribbon bg-success">Active</label></td>
                                            @else
                                                <td><label class="ribbon bg-danger">Disabled</label></td>
                                            @endif
                                            @if( $serviceCategory->pbsc_status == 1)
                                                <td>
                                                    <button class="btn btn-warning" type="button" name="serviceCategoryEdit" id="serviceCategoryEdit" data-toggle="modal" data-target="#editServiceCategory" value="{{ $serviceCategory->pbsc_id }}"><i class="far fa-edit"></i></button>
                                                     | 
                                                    <a href="/delete_service_category/{{ $serviceCategory->pbsc_id }}" class="btn btn-danger" type="button" name="serviceCategoryDelete"><i class="fa fa-trash"></i></a>
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
                    <h4 class="modal-title">Add New Service Category</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" name="addNewServiceCategoryForm" id="addNewServiceCategoryForm" action="/insertServiceCategory">
                        @csrf
                        <div class="form-group">
                            <label for="newServiceCategory" class="col-form-label">Service Category</label>
                            <input type="text" class="form-control" id="newServiceCategory" name="newServiceCategory" placeholder="Enter the Service Category" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-warning float-right" name="newServiceCategorySubmit" id="newServiceCategorySubmit">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editServiceCategory">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Service Category</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" name="editServiceCategoryForm" id="editServiceCategoryForm" action="/updateServiceCategory">
                        @csrf
                        <input type="hidden" class="form-control" id="editServiceCatID" name="editServiceCatID">
                        <div class="form-group">
                            <label for="editServiceCategory" class="col-form-label">Service Category</label>
                            <input type="text" class="form-control" id="editServiceCategory" name="editServiceCategory" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-warning float-right" name="editServiceCatSubmit" id="editServiceCatSubmit">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('body').on('click', '#serviceCategoryEdit', function(e){
        var id = $(this).val();
        $.ajax({
            type: 'GET',
            url: '/get_service_category/' + id,
            dataType: 'json',
            success: function(data) {
                // console.log(data.pbsc_id);
                $('#editServiceCategory #editServiceCatID').val(data.pbsc_id);
                $('#editServiceCategory #editServiceCategory').val(data.pbsc_name);
            }
        });
    });
</script>
@stop