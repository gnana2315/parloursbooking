@extends('layouts.backend')
@section('content')
<div class="content-wrapper">
	<div class="content-header">
		<div class="container-fluid">
			<div class="row mb-2">
				<div class="col-sm-6">
					<h1 class="m-0">SEO Manager</h1>
				</div>
				<div class="col-sm-6">
					<ol class="breadcrumb float-sm-right">
						<li class="breadcrumb-item"><a href="/dashboard">Dashboard</a></li>
						<li class="breadcrumb-item active">SEO Manager</li>
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
		<div class="container-fluid" id="viewSEOWords">
			<div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">SEO Words</h3>
                            <button class="btn btn-warning float-right" type="button" name="addNewSEOWords" id="addNewSEOWords" data-toggle="modal" data-target="#modal-lg"><i class="fas fa-plus"></i> New SEO </button>
                        </div>
                        <div class="card-body">
                            <table id="viewSEOTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Page</th>
                                        <th>Words</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($seoWords as $seoWord)
                                        <tr id="{{ $seoWord->pbseo_id }}">
                                            <td>{{ $seoWord->pbseo_id }}</td>
                                            <td>{{ $seoWord->pbseo_page }}</td>
                                            <td>{{ $seoWord->pbseo_words }}</td>
                                            @if( $seoWord->pbseo_status == 1)
                                                <td><label class="ribbon bg-success">Active</label></td>
                                            @else
                                                <td><label class="ribbon bg-danger">Disabled</label></td>
                                            @endif
                                            @if( $seoWord->pbseo_status == 1)
                                                <td>
                                                    <button class="btn btn-warning" type="button" name="seoWordEdit" id="seoWordEdit" data-toggle="modal" data-target="#editSEOWord" value="{{ $seoWord->pbseo_id }}"><i class="far fa-edit"></i></button>
                                                     | 
                                                    <a href="/delete_seo/{{ $seoWord->pbseo_id }}" class="btn btn-danger" type="button" name="seoWordDelete"><i class="fa fa-trash"></i></a>
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
                    <h4 class="modal-title">Add New SEO</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" name="addNewSEOForm" id="addNewSEOForm" action="/insertSEO">
                        @csrf
                        <div class="form-group">
                            <label for="newSEOPage" class="col-form-label">SEO Page</label>
                            <select class="form-control" id="newSEOPage" name="newSEOPage" required>
                                <option>Home</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="newSEOWords" class="col-form-label">SEO Page</label>
                            <input type="text" class="form-control" id="newSEOWords" name="newSEOWords" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-warning float-right" name="newSEOSubmit" id="newSEOSubmit">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editSEOWord">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit SEO</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="POST" name="editSEOForm" id="editSEOForm" action="/updateSEO">
                        @csrf
                        <input type="hidden" class="form-control" id="editSEOID" name="editSEOID">
                        <!--div class="form-group">
                            <label for="editSEOPage" class="col-form-label">SEO Page</label>
                            <select class="form-control" id="editSEOPage" name="editSEOPage" required>
                                <option>Home</option>
                            </select>
                        </div-->
                        <div class="form-group">
                            <label for="editSEOWords" class="col-form-label">SEO Words</label>
                            <input type="text" class="form-control" id="editSEOWords" name="editSEOWords" required>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-warning float-right" name="editSEOSubmit" id="editSEOSubmit">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('body').on('click', '#seoWordEdit', function(e){
        var id = $(this).val();
        $.ajax({
            type: 'GET',
            url: '/get_SEO/' + id,
            dataType: 'json',
            success: function(data) {
                // console.log(data.pbsc_id);
                $('#editSEOWord #editSEOID').val(data.pbseo_id);
                $('#editSEOWord #editSEOPage').val(data.pbseo_page);
                $('#editSEOWord #editSEOWords').val(data.pbseo_words);
            }
        });
    });
</script>
@stop