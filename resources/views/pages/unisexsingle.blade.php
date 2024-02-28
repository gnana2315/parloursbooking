@extends('layouts.frontend')
@section('content')
<link href="{{ URL::asset('plugins/colorbox/colorbox.css')}}" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="{{ URL::asset('css/listing.css')}}">
<link rel="stylesheet" type="text/css" href="{{ URL::asset('css/listing_responsive.css')}}">
<div class="super_overlay"></div>
<div class="home">
    <div class="container fill_height">
        <div class="row fill_height">
            <div class="col fill_height">
                <div class="listing_image">
                    <!--div class="listing_background_image_left">
                        <div class="background_image" style="background-image:url(images/listing_image_2.jpg)"></div>
                    </div>
                    <div class="listing_background_image_right">
                        <div class="background_image" style="background-image:url(images/listing_image_3.jpg)"></div>
                    </div-->
                    <div class="background_image" style="background-image:url(images/Suan-Styles.jpg)"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="home_container">
        <div class="container">
            <div class="row">
                <div class="col">
                    <div class="home_content">
                        <div class="listing_title"><h1>Suan Styles</h1></div>
                        <div class="listing_info_container d-flex flex-lg-row flex-column align-items-start justify-content-start">
                            <div class="listing_info d-flex flex-row align-items-center justify-content-start">
                                <div class="listing_rating">5.0</div>
                                <div class="listing_type">Unisex Salon</div>
                                <div class="listing_status">Opened</div>
                            </div>
                            <!--div class="listin_info_options d-flex flex-row align-items-start justify-content-start ml-lg-auto">
                                <div class="listing_info_button listing_info_button_1"><a href="#">Write a review</a></div>
                                <div class="listing_info_button listing_info_button_2"><a href="#">Book a table</a></div>
                            </div-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="listing">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="listing_content">
                    <div class="section_title"><h1>Services</h1></div>
                    <form class="listing_service_form" id="listing_service_form">
                        <label class="container listing_service_container">
                            <h3>Ladies Haircut</h3>
                            <span>LKR 3,500</span>
                            <input type="checkbox" class="listing_services" name="listedservices" data-name="Ladies Haircut" value="3500">
                        </label>
                        <label class="container listing_service_container">
                            <h3>Haircut</h3>
                            <span>LKR 1,500</span>
                            <input type="checkbox" class="listing_services" name="listedservices" data-name="Haircut" value="1500">
                        </label>
                        <label class="container listing_service_container">
                            <h3>Haircut & Beard</h3>
                            <span>LKR 2,200</span>
                            <input type="checkbox" class="listing_services" name="listedservices" data-name="Haircut & Beard" value="2200">
                        </label>
                        <label class="container listing_service_container">
                            <h3>Hair Straightening (Gents)</h3>
                            <span>LKR 5,000</span>
                            <input type="checkbox" class="listing_services" name="listedservices" data-name="Hair Straightening (Gents)" value="5000">
                        </label>
                        <button type="button" class="btn btn-info" id="servicesBookNow">Book Now</button>
                    </form>
                </div>
                <br><br>
                <div class="listing_content">
                    <div class="section_title"><h1>About The Salon</h1></div>
                    <div class="listing_text">
                        <p>This is a one of best Unisex salon and tattoo studio in srilanka . We have highly professional and experience staff. Beautifully designed salon located in Colombo Negombo main road. International experience tattoo artist Anton Sujee is tattooing at Suan Styles. Visit us and get experience of luxury treats.</p>
                    </div>
                    <!--div class="accordions">
                        <div class="accordion_container">
                            <div class="accordion d-flex flex-row align-items-center"><div>Maecenas velit ex, posuere vitae sapien eu, consectetur iaculis</div></div>
                            <div class="accordion_panel">
                                <div>
                                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam accumsan dolor id enim lacinia, sed feugiat ex suscipit. Nunc molestie malesuada pellentesque. Quisque mattis ante ut nisl tristique ornare. Aenean interdum dictum augue.</p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion_container">
                            <div class="accordion d-flex flex-row align-items-center"><div>Mauris diam augue, aliquam ut accumsan at, dignissim</div></div>
                            <div class="accordion_panel">
                                <div>
                                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam accumsan dolor id enim lacinia, sed feugiat ex suscipit. Nunc molestie malesuada pellentesque. Quisque mattis ante ut nisl tristique ornare. Aenean interdum dictum augue.</p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion_container">
                            <div class="accordion d-flex flex-row align-items-center"><div>Mauris diam augue, aliquam ut accumsan at, dignissim</div></div>
                            <div class="accordion_panel">
                                <div>
                                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam accumsan dolor id enim lacinia, sed feugiat ex suscipit. Nunc molestie malesuada pellentesque. Quisque mattis ante ut nisl tristique ornare. Aenean interdum dictum augue.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="reviews">
                        <div class="section_title"><h1>2 Reviews</h1></div>
                        <div class="reviews_container">
                            <div class="review d-flex flex-lg-row flex-column align-items-start justify-content-start">
                                <div class="review_user_container">
                                    <div>
                                        <div class="review_user d-flex flex-lg-column flex-row align-items-center justify-content-start">
                                            <div class="review_user_image"><img src="images/user_3.jpg" alt></div>
                                            <div class="text-lg-center">
                                                <div class="review_user_name"><a href="#">Maria Smith</a></div>
                                                <div class="review_count"><a href="#">5 reviews</a></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="review_content">
                                    <div class="review_title_container d-flex flex-row align-items-start justify-content-start">
                                        <div class="review_title">"I love it there"</div>
                                        <div class="review_rating ml-auto">4.5</div>
                                    </div>
                                    <div class="review_text">
                                        <p>Great place to visit, the food is awesome, I really love it. I would recommend it to other people.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="review d-flex flex-lg-row flex-column align-items-start justify-content-start">
                                <div class="review_user_container">
                                    <div>
                                        <div class="review_user d-flex flex-lg-column flex-row align-items-center justify-content-start">
                                            <div class="review_user_image"><img src="images/user_4.jpg" alt></div>
                                            <div class="text-lg-center">
                                                <div class="review_user_name"><a href="#">Maria Smith</a></div>
                                                <div class="review_count"><a href="#">5 reviews</a></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="review_content">
                                    <div class="review_title_container d-flex flex-row align-items-start justify-content-start">
                                        <div class="review_title">"Great place"</div>
                                        <div class="review_rating ml-auto">4.5</div>
                                    </div>
                                    <div class="review_text">
                                        <p>Great place to visit, the food is awesome, I really love it. I would recommend it to other people.</p>
                                    </div>
                                    <div class="review_images d-flex flex-row align-items-start justify-content-start flex-wrap">
                                        <div class="review_image"><a class="colorbox" href="images/review_1_large.jpg"><img src="images/review_1.jpg" alt></a></div>
                                        <div class="review_image"><a class="colorbox" href="images/review_2_large.jpg"><img src="images/review_2.jpg" alt></a></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div-->
                </div>
            </div>
            <div class="col-lg-4 sidebar_col">
                <div class="sidebar">
                    <div class="work_hours d-flex flex-row align-items-center justify-content-start">
                        <div class="opened">Open Now!</div>
                        <div class="ml-auto">09:00 A.M - 08:00 P.M</div>
                    </div>
                    <div class="sidebar_info">
                        <ul>
                            <li class="d-flex flex-row align-items-start justify-content-start">
                                <div class="sidebar_info_icon"><img src="images/info_1.png" alt></div>
                                <div class="sidebar_info_content"><span>Address: </span>No.551, 2nd Kurana, Negombo</div>
                            </li>
                            <li class="d-flex flex-row align-items-start justify-content-start">
                                <div class="sidebar_info_icon"><img src="images/info_2.png" alt></div>
                                <div class="sidebar_info_content"><span>Phone: </span>+94 76 560 5055</div>
                            </li>
                            <!--li class="d-flex flex-row align-items-start justify-content-start">
                                <div class="sidebar_info_icon"><img src="images/info_3.png" alt></div>
                                <div class="sidebar_info_content"><span>E-mail: </span><a href="mail-to:contact@dunstansbarbers.com">contact@dunstansbarbers.com</a></div>
                            </li-->
                        </ul>
                    </div>
                    <div class="location">
                        <div class="location_icon"><img src="images/loc.png" alt></div>
                        <div class="map">
                            <div id="google_map" class="google_map">
                                <div class="map_container">
                                    <div id="map"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--div class="similar_places">
                        <div class="section_title"><h1>Similar places</h1></div>
                        <div class="similar_places_container d-flex flex-lg-column flex-row align-items-start justify-content-between flex-wrap">
                            <div class="similar_place">
                                <div class="similar_place_image">
                                    <div class="location_icon"><a href="#"><img src="images/loc.png" alt></a></div>
                                    <div class="similar_place_rating">4,5</div>
                                    <img src="images/similar.jpg" alt>
                                </div>
                                <div class="similar_place_title"><a href="#">The Lunch</a></div>
                            </div>
                        </div>
                    </div-->
                </div>
            </div>
        </div>
    </div>
</div>
@stop