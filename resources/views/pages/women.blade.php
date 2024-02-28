@extends('layouts.frontend')
@section('content')
<link rel="stylesheet" type="text/css" href="{{ URL::asset('css/listings.css')}}">
<link rel="stylesheet" type="text/css" href="{{ URL::asset('css/listings_responsive.css')}}">
<div class="super_overlay"></div>
<div class="listings container_custom">
    <div class="container">
        <div class="row">
            <div class="col-xl-3">
                <div class="listing_filter">
                    <div class="section_title"><h1>Filter</h1></div>
                    <!--div class="listing_filter_container d-flex flex-row align-items-start justify-content-start flex-wrap"-->
                    <h5>Choose your Gender</h5>
                    <div class="listing_filter_container d-flex flex-row flex-wrap">
                        <div class="listing_checkbox">
                            <label>Men
                                <input type="checkbox" data-filter=".men">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                        <div class="listing_checkbox">
                            <label>Women
                                <input type="checkbox" data-filter=".women">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                    </div>
                    <!--h5>Price</h5>
                    <div class="listing_filter_container d-flex flex-row flex-wrap">
                        <div class="filterslidecontainer">
                            <input type="range" min="1" max="100" value="50" class="filterslider	" id="priceRange">
                        </div>
                    </div>
                    <div class="listing_filter_container d-flex flex-row flex-wrap">
                        <div class="listing_checkbox">
                            <label>Filter 3
                                <input type="checkbox" data-filter=".coffee">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                        <div class="listing_checkbox">
                            <label>Filter 4
                                <input type="checkbox" data-filter=".drinks">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                        <div class="listing_checkbox">
                            <label>Filter 5
                                <input type="checkbox" data-filter=".food">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                        <div class="listing_checkbox">
                            <label>Filter 6
                                <input type="checkbox" data-filter=".drinks">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                        <div class="listing_checkbox">
                            <label>Filter 7
                                <input type="checkbox" data-filter=".food">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                        <div class="listing_checkbox">
                            <label>Filter 8
                                <input type="checkbox" data-filter=".drinks">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                        <div class="listing_checkbox">
                            <label>Filter 9
                                <input type="checkbox" data-filter=".food">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                        <div class="listing_checkbox">
                            <label>Filter 10
                                <input type="checkbox" data-filter=".food">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                        <div class="listing_checkbox">
                            <label>Filter 11
                                <input type="checkbox" data-filter=".food">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                        <div class="listing_checkbox">
                            <label>Filter 12
                                <input type="checkbox" data-filter=".coffee">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                    </div-->
                </div>
            </div>
            <div class="col-xl-9">
                <div class="listings_content">
                    <!--div class="listing_search_container">
                        <form action="#" class="search_form" id="search_form">
                            <div class="d-flex flex-md-row flex-column align-items-start justify-content-start">
                                <div>
                                    <input type="text" class="search_input" placeholder="What are you looking for?" required="required">
                                    <button class="search_button">Search</button>
                                </div>
                                <div>
                                    <input type="text" class="search_input" placeholder="Your Location" required="required">
                                </div>
                            </div>
                        </form>
                    </div-->
                    <!--div class="listing_filter">
                        <div class="section_title"><h1>Filter</h1></div>
                        <div class="listing_filter_container d-flex flex-row align-items-start justify-content-start flex-wrap">
                            <div class="listing_checkbox">
                                <label>Filter 1
                                    <input type="checkbox" data-filter=".coffee">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="listing_checkbox">
                                <label>Filter 2
                                    <input type="checkbox" data-filter=".drinks">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="listing_checkbox">
                                <label>Filter 3
                                    <input type="checkbox" data-filter=".coffee">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="listing_checkbox">
                                <label>Filter 4
                                    <input type="checkbox" data-filter=".drinks">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="listing_checkbox">
                                <label>Filter 5
                                    <input type="checkbox" data-filter=".food">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="listing_checkbox">
                                <label>Filter 6
                                    <input type="checkbox" data-filter=".drinks">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="listing_checkbox">
                                <label>Filter 7
                                    <input type="checkbox" data-filter=".food">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="listing_checkbox">
                                <label>Filter 8
                                    <input type="checkbox" data-filter=".drinks">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="listing_checkbox">
                                <label>Filter 9
                                    <input type="checkbox" data-filter=".food">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="listing_checkbox">
                                <label>Filter 10
                                    <input type="checkbox" data-filter=".food">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="listing_checkbox">
                                <label>Filter 11
                                    <input type="checkbox" data-filter=".food">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="listing_checkbox">
                                <label>Filter 12
                                    <input type="checkbox" data-filter=".coffee">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                        </div>
                    </div-->
                    <div class="results_container">
                        <div class="section_title"><h1><span>Results for: </span>Female</h1></div>
                        <div class="results row">
                            <div class="col-xl-6 grid-item result">
                                <div class="listing">
                                    <div class="listing_image">
                                        <div class="listing_icon"><a href="#"><img src="images/loc.png" alt></a></div>
                                        <img src="images/index.jpg" alt>
                                    </div>
                                    <div class="listing_title_container">
                                        <div class="listing_title"><a href="/womensingle">Salon Dmesh</a></div>
                                        <div class="listing_info d-flex flex-row align-items-center justify-content-between">
                                            <div class="listing_rating">4.5</div>
                                            <div class="listing_divider">|</div>
                                            <div class="listing_type">Women Salon shop</div>
                                            <!--div class="listing_divider">|</div>
                                            <div class="listing_status">Open</div-->
                                        </div>
                                    </div>
                                    <!--div class="listing_testimonial">
                                        04th Feb
                                        <div class="d-flex flex-row align-items-center justify-content-start" style="padding-bottom: 10px;">
                                            <div class="testimonial_image"><div class="button cta_button"><a href="#">Select</a></div></div>
                                            <div class="testimonial_text">
                                                <p>8.00 A.M - 10.00 A.M</p>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-row align-items-center justify-content-start" style="padding-bottom: 10px;">
                                            <div class="testimonial_image"><div class="button cta_button"><a href="#">Select</a></div></div>
                                            <div class="testimonial_text">
                                                <p>11.00 A.M - 1.00 P.M</p>
                                            </div>
                                        </div>
                                    </div-->
                                </div>
                            </div>
                        <!--div class="results grid">
                            <div class="grid-item result coffee">
                                <div class="listing">
                                    <div class="listing_image">
                                        <div class="listing_icon"><a href="listing.html"><img src="images/loc.png" alt></a></div>
                                        <img src="images/listing_1.jpg" alt>
                                    </div>
                                    <div class="listing_title_container">
                                        <div class="listing_title"><a href="listing.html">The Meal</a></div>
                                        <div class="listing_info d-flex flex-row align-items-center justify-content-between">
                                            <div class="listing_rating">4,5</div>
                                            <div class="listing_price">$$$</div>
                                            <div class="listing_divider">|</div>
                                            <div class="listing_type">Restaurant</div>
                                            <div class="listing_divider">|</div>
                                            <div class="listing_status">Closed</div>
                                        </div>
                                    </div>
                                    <div class="listing_testimonial">
                                        <div class="d-flex flex-row align-items-center justify-content-start">
                                            <div class="testimonial_image"><img src="images/user_1.jpg" alt></div>
                                            <div class="testimonial_text">
                                                <p>Great place to visit, the food is awesome, I really love it.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="grid-item result food">
                                <div class="listing">
                                    <div class="listing_image">
                                        <div class="listing_icon"><a href="listing.html"><img src="images/loc.png" alt></a></div>
                                        <img src="images/listing_2.jpg" alt>
                                    </div>
                                    <div class="listing_title_container">
                                        <div class="listing_title"><a href="listing.html">Lunch Box</a></div>
                                        <div class="listing_info d-flex flex-row align-items-center justify-content-between">
                                            <div class="listing_rating">4,5</div>
                                            <div class="listing_price">$$$</div>
                                            <div class="listing_divider">|</div>
                                            <div class="listing_type">Restaurant</div>
                                            <div class="listing_divider">|</div>
                                            <div class="listing_status">Closed</div>
                                        </div>
                                    </div>
                                    <div class="listing_testimonial">
                                        <div class="d-flex flex-row align-items-center justify-content-start">
                                            <div class="testimonial_image"><img src="images/user_2.jpg" alt></div>
                                            <div class="testimonial_text">
                                                <p>Great place to visit, the food is awesome, I really love it.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="grid-item result drinks">
                                <div class="listing">
                                    <div class="listing_image">
                                        <div class="listing_icon"><a href="listing.html"><img src="images/loc.png" alt></a></div>
                                        <img src="images/listing_1.jpg" alt>
                                    </div>
                                    <div class="listing_title_container">
                                        <div class="listing_title"><a href="listing.html">The Lunch</a></div>
                                        <div class="listing_info d-flex flex-row align-items-center justify-content-between">
                                            <div class="listing_rating">4,5</div>
                                            <div class="listing_price">$$$</div>
                                            <div class="listing_divider">|</div>
                                            <div class="listing_type">Restaurant</div>
                                            <div class="listing_divider">|</div>
                                            <div class="listing_status">Closed</div>
                                        </div>
                                    </div>
                                    <div class="listing_testimonial">
                                        <div class="d-flex flex-row align-items-center justify-content-start">
                                            <div class="testimonial_image"><img src="images/user_1.jpg" alt></div>
                                            <div class="testimonial_text">
                                                <p>Great place to visit, the food is awesome, I really love it.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="grid-item result coffee">
                                <div class="listing">
                                    <div class="listing_image">
                                        <div class="listing_icon"><a href="listing.html"><img src="images/loc.png" alt></a></div>
                                        <img src="images/listing_2.jpg" alt>
                                    </div>
                                    <div class="listing_title_container">
                                        <div class="listing_title"><a href="listing.html">Vegan Space</a></div>
                                        <div class="listing_info d-flex flex-row align-items-center justify-content-between">
                                            <div class="listing_rating">4,5</div>
                                            <div class="listing_price">$$$</div>
                                            <div class="listing_divider">|</div>
                                            <div class="listing_type">Restaurant</div>
                                            <div class="listing_divider">|</div>
                                            <div class="listing_status">Closed</div>
                                        </div>
                                    </div>
                                    <div class="listing_testimonial">
                                        <div class="d-flex flex-row align-items-center justify-content-start">
                                            <div class="testimonial_image"><img src="images/user_2.jpg" alt></div>
                                            <div class="testimonial_text">
                                                <p>Great place to visit, the food is awesome, I really love it.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div-->
                        </div>
                    </div>
                </div>
            </div>
            <!--div class="col-xl-6">
                <div class="listings_map">
                    <div class="map">
                        <div id="google_map" class="google_map">
                            <div class="map_container">
                                <div id="map"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div-->
        </div>
    </div>
</div>
<div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" id="mensbooking" aria-labelledby="mensbooking" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Booking Form</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <form>
                            <div class="row">
                                <div class="col-lg-7 col-md-7">
                                    <div class="row">
                                        <div class="col">
                                            <label for="exampleFormControlInput1">Date <span class="requiredInput">*</span></label>
                                            <input type="date" class="form-control" id="exampleFormControlInput1">
                                        </div>
                                        <div class="col">
                                            <label for="exampleFormControlInput1">Time <span class="requiredInput">*</span></label>														
                                            <select class="form-control" id="timeSlot" name="timeSlot">
                                                <option value="09:00 - 10.00">9:00 AM - 10.00 AM</option>
                                                <option value="10:00 - 11:00">10:00 AM - 11:00 AM</option>
                                                <option value="11:00 - 12:00">11:00 AM - 12:00 PM</option>
                                                <option value="14:00 - 15:00">2:00 PM - 3:00 PM</option>
                                                <option value="15:00 - 16:00">3:00 PM - 4:00 PM</option>
                                                <!-- Add more options as needed -->
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col">
                                            <label for="exampleFormControlInput1">Email <span class="requiredInput">*</span></label>
                                            <input type="email" class="form-control" id="exampleFormControlInput1" placeholder="name@example.com">
                                        </div>
                                        <div class="col">
                                            <label for="exampleFormControlInput1">Contact No <span class="requiredInput">*</span></label>
                                            <input type="tel" class="form-control" id="exampleFormControlInput1" placeholder="0771 234 567">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleFormControlInput1">Name <span class="requiredInput">*</span></label>
                                        <input type="text" class="form-control" id="exampleFormControlInput1" placeholder="eg: John">
                                    </div>
                                    <div class="form-group">
                                        <label for="exampleFormControlTextarea1">Addional Notes</label>
                                        <textarea class="form-control" id="exampleFormControlTextarea1" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="col-lg-5 col-md-5">
                                    <h5>Services Cart</h5>
                                    Payment method: Pay at venue
                                    <table class="table" id="itemsTable">
                                        <thead>
                                            <th scope="col">Service</th>
                                            <th scope="col">Price</th>
                                        </thead>
                                        <tbody id="modalBody">												
                                        </tbody>
                                    </table>
                                    <h5>Cancellation policy</h5>
                                    Please avoid cancelling within <strong>2 hours</strong> of your appointment time												
                                </div>
                            </div>
                        </form>
                    </div>									
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary">Book</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@stop