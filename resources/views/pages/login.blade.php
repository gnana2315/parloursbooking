@extends('layouts.frontend')
@section('content')
<link rel="stylesheet" type="text/css" href="{{ URL::asset('css/listings.css')}}">
<link rel="stylesheet" type="text/css" href="{{ URL::asset('css/listings_responsive.css')}}">
<div class="super_overlay"></div>
<div class="listings container_custom">
    <div class="text-center"><h1>System Login</h1></div>
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
                    <form action="/userloging" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form_section_title"><h3>System Login</h3></div>
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-xl-6">
                            <div class="form-group">
                                    <label for="pbu_email">Email</label>
                                    <input type="email" name="pbu_email" id="pbu_email" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-xl-6">
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" name="password" id="password" class="form-control">
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