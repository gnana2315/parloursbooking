@extends('layouts.default')
@section('content')
<div class="content-wrapper" style="margin-left:0!important;">
	<div class="content-header">
		<div class="container-fluid">
            @if ($message = Session::get('success'))
                <div class="alert alert-success">
                    <p>{{ $message }}</p>
                </div>
            @elseif ($message = Session::get('failed'))
                <div class="alert alert-danger">
                    <p>{{ $message }}</p>
                </div>
            @endif
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-default">
                        <div class="card-header">
                            <h3 class="card-title">Vendor Registration Form Wizard</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="bs-stepper">
                                <div class="bs-stepper-header" role="tablist">
                                    <!-- your steps here -->
                                    <div class="step" data-target="#businesstype">
                                    <button type="button" class="step-trigger" role="tab" aria-controls="businesstype" id="businesstype-trigger">
                                        <span class="bs-stepper-circle">1</span>
                                        <span class="bs-stepper-label">Business Type</span>
                                    </button>
                                    </div>
                                    <div class="line"></div>
                                    <div class="step" data-target="#vendorInformation">
                                    <button type="button" class="step-trigger" role="tab" aria-controls="vendorInformation" id="vendorInformation-trigger">
                                        <span class="bs-stepper-circle">2</span>
                                        <span class="bs-stepper-label">Vendor information</span>
                                    </button>
                                    </div>
                                </div>
                                <div class="bs-stepper-content">
                                    <!-- your steps content here -->
                                    <div id="businesstype" class="content" role="tabpanel" aria-labelledby="businesstype-trigger">
                                        <h4>You have to select the what type of business that you doing!</h4>
                                        <p><strong>Business:</strong> These type have to do the business under specific registered BR and location.</p>
                                        <p><strong>Individual:</strong> These type don't have BR and they have to visit to customer place</p>
                                        <div class="form-group">
                                            <label for="businessType">Business Type</label>
                                            <select class="form-control" id="businessType" name="businessType">
                                                <option seleted readonly>Please select an option</option>
                                                <option value="1">Busniess</option>
                                                <option value="2">Individual</option>
                                            </select>
                                        </div>
                                        <button class="btn btn-primary" onclick="stepper.next()">Next</button>
                                    </div>
                                    <div id="vendorInformation" class="content" role="tabpanel" aria-labelledby="vendorInformation-trigger">
                                        <div class="form-group">
                                            <label for="exampleInputFile">File input</label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" id="exampleInputFile">
                                                    <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                                </div>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">Upload</span>
                                                </div>
                                            </div>
                                        </div>
                                        <button class="btn btn-primary" onclick="stepper.previous()">Previous</button>
                                        <button type="submit" class="btn btn-primary">Submit</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
            </div>
		</div>
	</div>
</div>
@stop