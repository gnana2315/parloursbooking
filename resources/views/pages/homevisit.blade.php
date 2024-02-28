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
                        <div class="section_title"><h1><span>Results for: </span>Home Visit</h1></div>
                        <div class="results row">
                            <div class="col-xl-6 grid-item result">
                                <div class="listing">
                                    <div class="listing_image">
                                        <div class="listing_icon"><a href="#"><img src="images/loc.png" alt></a></div>
                                        <img src="images/homevisit.webp" alt>
                                    </div>
                                    <div class="listing_title_container">
                                        <!--div class="listing_title"><a href="#" data-toggle="modal" data-target="#bookingForm">Dunstans Barbers</a></div-->
                                        <div class="listing_title"><a href="/homevisitsingle">Menik Hair Care</a></div>
                                        <div class="listing_info d-flex flex-row align-items-center justify-content-between">
                                            <div class="listing_rating">5.0</div>
                                            <div class="listing_type">Women Salon</div>
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
@stop