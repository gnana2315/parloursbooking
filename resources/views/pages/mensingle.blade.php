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
                    <div class="background_image" style="background-image:url(images/db.jpg)"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="home_container">
        <div class="container">
            <div class="row">
                <div class="col">
                    <div class="home_content">
                        <div class="listing_title"><h1>Dunstans Barbers</h1></div>
                        <div class="listing_info_container d-flex flex-lg-row flex-column align-items-start justify-content-start">
                            <div class="listing_info d-flex flex-row align-items-center justify-content-start">
                                <div class="listing_rating">4.5</div>
                                <div class="listing_type">Mens Beauty Salon</div>
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
                            <h3>Haircut</h3>
                            <span>LKR 2,500</span>
                            <p>Following a full and detailed consultation with one of our qualified barbers, we will identify all of your needs and requirements and address any concerns or questions you may have. Your service will then be a combination of precision cutting using traditional and contemporary techniques, shampooing & conditioning, and with a styling to suit your overall look to finish. All using top quality American Crew products that represent you, and compliment your hair type and style!</p>
                            <input type="checkbox" class="listing_services" name="listedservices" data-name="Haircut" value="2500">
                        </label>
                        <label class="container listing_service_container">
                            <h3>Eye Rejuvenation Treatment</h3>
                            <span>LKR 3,500</span>
                            <p>This quick and effective treatment is a must-have for all our busy clients. With a combination of the effective vitamin C eye contour patches and antiox booster system, along with specialised massage techniques - it rejuvenates the sensitive eye area. It eliminates signs of fatigue, and revitalises tired eyes by diminishing fine lines and dark circles re-establishing skin luminosity.</p>
                            <input type="checkbox" class="listing_services" name="listedservices" data-name="Eye Rejuvenation Treatment" value="3500">
                        </label>
                        <label class="container listing_service_container">
                            <h3>Dunstans Shave</h3>
                            <span>LKR 4,000</span>
                            <p>Your shave will start with your barber giving you a face mapping to determine your skin type, growth pattern and any further requirements you may have. We then exfoliate your skin removing all impurities from the beard area. A pre-shave cream/oil is applied to soften the beard and protect the skin. Shaving soap is applied with a badger brush using a special technique to soften the beard further for a closer and comfortable shave. We then finish off with a soothing shaving balm, hydro-soothing mask & vitamin C eye cream. This service is carried out using a combination of lavender-scented hot & cold towels, Proraso shaving products, and Sothys Homme face products.</p>
                            <input type="checkbox" class="listing_services" name="listedservices" data-name="Dunstans Shave" value="4000">
                        </label>
                        <label class="container listing_service_container">
                            <h3>Hair Colour</h3>
                            <span>LKR 4,000</span>
                            <p>If you’re a gentlemen looking to tone down those greys to obtain a natural and fresh look, then this is the service for you! We use American Crew Precision Blend natural grey coverage. This ammonia-free product will last up to 30 washes, and gradually fade out with each wash, leaving you with a natural and organic look.</p>
                            <input type="checkbox" class="listing_services" name="listedservices" data-name="Hair Colour" value="4000">
                        </label>
                        <button type="button" class="btn btn-info" id="servicesBookNow">Book Now</button>
                    </form>
                </div>
                <br><br>
                <div class="listing_content">
                    <div class="section_title"><h1>About The Salon</h1></div>
                    <div class="listing_text">
                        <p>Professional barbers for modern, style-conscious men.The most prestigious men's grooming in Sri Lanka.</p>
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
                                <div class="sidebar_info_content"><span>Address: </span>Shop G13, Crescat Boulevard, Galle Road, Colombo 3</div>
                            </li>
                            <li class="d-flex flex-row align-items-start justify-content-start">
                                <div class="sidebar_info_icon"><img src="images/info_2.png" alt></div>
                                <div class="sidebar_info_content"><span>Phone: </span>+94 112 440 434</div>
                            </li>
                            <li class="d-flex flex-row align-items-start justify-content-start">
                                <div class="sidebar_info_icon"><img src="images/info_3.png" alt></div>
                                <div class="sidebar_info_content"><span>E-mail: </span><a href="mail-to:contact@dunstansbarbers.com">contact@dunstansbarbers.com</a></div>
                            </li>
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