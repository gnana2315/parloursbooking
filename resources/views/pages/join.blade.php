@extends('layouts.frontend')
@section('content')
<link rel="stylesheet" type="text/css" href="{{ URL::asset('css/listings.css')}}">
<link rel="stylesheet" type="text/css" href="{{ URL::asset('css/listings_responsive.css')}}">
<div class="super_overlay"></div>
<div class="listings container_custom">
    <div class="text-center"><h1>Vendor Registration</h1></div>
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="listing_filter">
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success">
                            <p>{{ $message }}</p>
                        </div>
                    @elseif ($message = Session::get('failed'))
                        <div class="alert alert-danger">
                            <p>{{ $message }}</p>
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Error!</strong> <br>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="/register" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form_section_title"><h3>Business Details</h3></div>
                        <div class="row">
                            <div class="col-lg-2 col-md-2 col-xl-2">
                                <div class="form-group">
                                    <label for="userreg_businesstype">Business Type</label>
                                    <select name="userreg_businesstype" id="userreg_businesstype" class="form-control">
                                        <option>Please select a business type</option>
                                        <option value="1">Men</option>
                                        <option value="2">Women</option>
                                        <option value="3">Unisex</option>
                                        <option value="4">Home Visit</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-5 col-md-5 col-xl-5">
                                <div class="form-group">
                                    <label for="userreg_businessname">Business Name</label>
                                    <input type="text" class="form-control" id="userreg_businessname" name="userreg_businessname" placeholder="Enter your Business Name">
                                </div>
                            </div>
                            <div class="col-lg-2 col-md-2 col-xl-2">
                                <div class="form-group">
                                    <label for="userreg_businesslogo">Business Logo (JPG/PNG - Below 2MB)</label>
                                    <input type="file" class="form-control" id="userreg_businesslogo" name="userreg_businesslogo">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-xl-3">
                                <div class="form-group">
                                    <label for="userreg_businessdoc">Parlour Certificate Document (PDF or JPG format)</label>
                                    <input type="file" class="form-control" id="userreg_businessdoc" name="userreg_businessdoc">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4 col-md-4 col-xl-4">
                                <div class="form-group">
                                    <label for="userreg_businessregno">Business Registration No</label>
                                    <input type="text" class="form-control" id="userreg_businessregno" name="userreg_businessregno" placeholder="Enter your Business Registration No">
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-xl-4">
                                <div class="form-group">
                                    <label for="userreg_businessregdoc">Business Registration Document (PDF or JPG format)</label>
                                    <input type="file" class="form-control" id="userreg_businessregdoc" name="userreg_businessregdoc">
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-xl-4">
                                <div class="form-group">
                                    <label for="userreg_businessregemail">Business Email Address</label>
                                    <input type="email" class="form-control" id="userreg_businessregemail" name="userreg_businessregemail" placeholder="Enter your Business Email">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-8 col-md-8 col-xl-8">
                                <label for="userreg_businessregaddress">Business Address</label>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="userreg_businessregaddressline1" name="userreg_businessregaddressline1" placeholder="Enter your Business Address Line 1">
                                    <br>
                                    <input type="text" class="form-control" id="userreg_businessregaddressline2" name="userreg_businessregaddressline2" placeholder="Enter your Business Address Line 2">
                                    <br>
                                    <input type="text" class="form-control" id="userreg_businessregaddresscity" name="userreg_businessregaddresscity" placeholder="Enter your Business Address City">
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-xl-4">
                                <div class="form-group">
                                    <label for="userreg_businessregcontactno">Business Contact No</label>
                                    <input type="tel" class="form-control" id="userreg_businessregcontactno" name="userreg_businessregcontactno" placeholder="Enter your Business Contact No">
                                </div>
                            </div>
                        </div>
                        <br>
                        <br>
                        <div class="form_section_title"><h3>Business Owner Details</h3></div>
                        <div class="row">
                            <div class="col-lg-2 col-md-2 col-xl-2">
                                <label for="userreg_businessownertitle">Business Owner Title</label>
                                <div class="form-group">
                                    <select name="userreg_businessownertitle" id="userreg_businessownertitle" class="form-control">
                                        <option>Please select a title</option>
                                        <option>Dr</option>
                                        <option>Prof</option>
                                        <option>Mr</option>
                                        <option>Mrs</option>
                                        <option>Ms</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-xl-3">
                                <label for="userreg_businessownerfirstname">Business Owner First Name</label>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="userreg_businessownerfirstname" name="userreg_businessownerfirstname" placeholder="Enter your First Name">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-xl-3">
                                <label for="userreg_businessownerlastname">Business Owner Last Name</label>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="userreg_businessownerlastname" name="userreg_businessownerlastname" placeholder="Enter your Last Name">
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-4 col-xl-4">
                                <div class="form-group">
                                    <label for="userreg_businessownernicno">Business Owner's NIC No</label>
                                    <input type="text" class="form-control" id="userreg_businessownernicno" name="userreg_businessownernicno" placeholder="Enter your NIC No">
                                </div>
                            </div>
                        </div>
                        <div class="row">                            
                            <div class="col-lg-6 col-md-6 col-xl-6">
                                <label for="userreg_businessowneraddress">Business Owner's Address</label>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="userreg_businessowneraddressline1" name="userreg_businessowneraddressline1" placeholder="Enter your Address Line 1">
                                    <br>
                                    <input type="text" class="form-control" id="userreg_businessowneraddressline2" name="userreg_businessowneraddressline2" placeholder="Enter your Address Line 2">
                                    <br>
                                    <input type="text" class="form-control" id="userreg_businessownercity" name="userreg_businessownercity" placeholder="Enter your City">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-xl-3">
                                <label for="userreg_businessownercontactno">Business Owner's Contact No</label>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="userreg_businessownercontactno" name="userreg_businessownercontactno" placeholder="Enter your Contact No">
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-3 col-xl-3">
                                <label for="userreg_businessowneremail">Business Owner's Email</label>
                                <div class="form-group">
                                    <input type="email" class="form-control" id="userreg_businessowneremail" name="userreg_businessowneremail" placeholder="Enter your Email">
                                </div>
                            </div>
                        </div>
                        <br>
                        <br>
                        <div class="form_section_title"><h3>Login Details</h3></div>
                        <div class="row">
                            <div class="col-lg-2 col-md-2 col-xl-2">
                                <label for="userreg_businessusertype">User Type</label>
                                <div class="form-group">
                                    <select name="userreg_businessusertype" id="userreg_businessusertype" class="form-control" readonly>
                                        <option selected>Vendor</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-5 col-md-5 col-xl-5">
                                <label for="userreg_businessusername">User Name</label>
                                <div class="form-group">
                                    <input type="text" name="userreg_businessusername" id="userreg_businessusername" class="form-control" placeholder="Enter a User Name">
                                </div>
                            </div>
                            <div class="col-lg-5 col-md-5 col-xl-5">
                                <label for="userreg_businessuserpassword">User Password</label>
                                <div class="form-group">
                                    <input type="password" name="userreg_businessuserpassword" id="userreg_businessuserpassword" class="form-control" placeholder="Enter your Password">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-xl-12">
                                <a href="/" class="btn back-to-home">Back to Home</a>
                                <button type="submit" class="btn submit_button">Submit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop