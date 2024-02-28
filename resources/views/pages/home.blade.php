@extends('layouts.frontend')
@section('content')
<link rel="stylesheet" type="text/css" href="{{ URL::asset('css/main_styles.css')}}">
<link rel="stylesheet" type="text/css" href="{{ URL::asset('css/custom.css')}}">
<link rel="stylesheet" type="text/css" href="{{ URL::asset('css/responsive.css')}}">
<div class="super_overlay"></div>
<div class="home">
    <div class="home_slider_container">
        <div class="owl-carousel owl-theme home_slider">
            <div class="slide">
                <div class="background_image" style="background-image:url(images/index2.jpg)"></div>
                <div class="home_container">
                    <div class="container">
                        <div class="row">
                            <div class="col-xl-8 offset-xl-2">
                                <div class="home_content text-center">
                                    <div class="search_form_container">
                                        <form action="#" class="search_form" id="search_form">
                                            <div class="d-flex flex-sm-row flex-column align-items-sm-start align-items-center justify-content-sm-between">
                                                <input type="text" class="search_input" placeholder="What are you looking for?" required="required">
                                                <button class="search_button">Search</button>
                                            </div>
                                        </form>
                                    </div>
                                    <!--div class="home_title"><h1>The Best City <span>Guide</span></h1></div-->
                                    <div class="categories">
                                        <div class="container">
                                            <div class="row">
                                                <div class="col">
                                                    <div class="categories_container d-flex flex-md-row flex-column align-items-center justify-content-center">
                                                        <div class="category text-center">
                                                            <a href="/mens">
                                                                <div class="d-flex flex-md-column flex-row align-items-md-center align-items-md-center align-items-center justify-content-center">
                                                                    <div class="cat_icon"><img src="images/masculine.png" class="svg" alt="hair-cut-tool"></div>
                                                                    <div class="cat_title">Male</div>
                                                                </div>
                                                            </a>
                                                        </div>
                                                        <div class="category text-center">
                                                            <a href="/women">
                                                                <div class="d-flex flex-md-column flex-row align-items-md-center align-items-md-center align-items-center justify-content-center">
                                                                    <div class="cat_icon"><img src="images/femenine.png" class="svg" alt="makeup"></div>
                                                                    <div class="cat_title">Female</div>
                                                                </div>
                                                            </a>
                                                        </div>
                                                        <div class="category text-center">
                                                            <a href="/unisex">
                                                                <div class="d-flex flex-md-column flex-row align-items-md-center align-items-md-center align-items-center justify-content-center">
                                                                    <div class="cat_icon"><img src="images/unisex.png" class="svg" alt="tatoo-machine"></div>
                                                                    <div class="cat_title">Unisex</div>
                                                                </div>
                                                            </a>
                                                        </div>
                                                        <div class="category text-center">
                                                            <a href="/homevisit">
                                                                <div class="d-flex flex-md-column flex-row align-items-md-center align-items-md-center align-items-center justify-content-center">
                                                                    <div class="cat_icon"><img src="images/home.png" class="svg" alt="tatoo-machine"></div>
                                                                    <div class="cat_title">Home Visit</div>
                                                                </div>
                                                            </a>
                                                        </div>
                                                        <!--div class="category text-center">
                                                            <a href="listings.html">
                                                                <div class="d-flex flex-md-column flex-row align-items-md-center align-items-md-start align-items-center justify-content-start">
                                                                    <div class="cat_icon"><img src="images/nail-polish.png" class="svg" alt="nail-polish"></div>
                                                                    <div class="cat_title">Manicure</div>
                                                                </div>
                                                            </a>
                                                        </div>
                                                        <div class="category text-center">
                                                            <a href="listings.html">
                                                                <div class="d-flex flex-md-column flex-row align-items-md-center align-items-md-start align-items-center justify-content-start">
                                                                    <div class="cat_icon"><img src="images/foot-massage.png" class="svg" alt="foot-massage"></div>
                                                                    <div class="cat_title">Pedicure</div>
                                                                </div>
                                                            </a>
                                                        </div-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--div class="search_form_container">
                                        <form action="#" class="search_form" id="search_form">
                                            <div class="d-flex flex-sm-row flex-column align-items-sm-start align-items-center justify-content-sm-between">
                                                <input type="text" class="search_input" placeholder="What are you looking for?" required="required">
                                                <button class="search_button">Search</button>
                                            </div>
                                        </form>
                                    </div-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--div class="slide">
                <div class="background_image" style="background-image:url(images/index.jpg)"></div>
                <div class="home_container">
                    <div class="container">
                        <div class="row">
                            <div class="col-xl-8 offset-xl-2">
                                <div class="home_content text-center">
                                    <div class="home_title"><h1>The Best City Guide</h1></div>
                                    <div class="search_form_container">
                                        <form action="#" class="search_form" id="search_form">
                                            <div class="d-flex flex-sm-row flex-column align-items-sm-start align-items-center justify-content-sm-between">
                                                <input type="text" class="search_input" placeholder="What are you looking for?" required="required">
                                                <button class="search_button">Search</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="slide">
                <div class="background_image" style="background-image:url(images/index.jpg)"></div>
                <div class="home_container">
                    <div class="container">
                        <div class="row">
                            <div class="col-xl-8 offset-xl-2">
                                <div class="home_content text-center">
                                    <div class="home_title"><h1>The Best City Guide</h1></div>
                                    <div class="search_form_container">
                                        <form action="#" class="search_form" id="search_form">
                                            <div class="d-flex flex-sm-row flex-column align-items-sm-start align-items-center justify-content-sm-between">
                                                <input type="text" class="search_input" placeholder="What are you looking for?" required="required">
                                                <button class="search_button">Search</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div-->
        </div>
        <!--div class="home_slider_dots">
            <ul id="home_slider_custom_dots" class="home_slider_custom_dots">
                <li class="home_slider_custom_dot active">01.</li>
                <li class="home_slider_custom_dot">02.</li>
                <li class="home_slider_custom_dot">03.</li>
            </ul>
        </div-->
    </div>
</div>
<div class="listing">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="listing_content">
                    <div class="section_title"><h1>Vision & Mission</h1></div>
                    <div class="listing_text">
                        <p>Parloursbooking.com is mission is to bring people and beauty service businesses together by enabling them to easily reserve their services online no matter which, where they are or when they want to book! which provides customers with the best and most user-friendly software solutions.</p>
                        
                        <p>In addition to projects for its customers, the company provides all kind of beauty services.</p>
                        
                        <p>The newest product that unlocks the world of online beauty is Parloursbooking.com, with its unique salon / parlours  management solution for both salon owners and beauty clients.</p>
                        
                        <p>Because of the architecture of the system, clients and salon / parlours owners can use any device, including desktop, laptop, tablet, and mobile phones to access and manage the salon's / parlours backend at any time and from any location in the island.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 sidebar_col">
                <div class="sidebar">
                    <div class="work_hours d-flex flex-row align-items-center justify-content-start">
                        <div class="opened" style="text-align:center;">Open 24 Hours</div>
                        <!--div class="ml-auto">Open : 24 Hours</div-->
                    </div>
                    <div class="sidebar_info">
                        <ul>
                            <!--li class="d-flex flex-row align-items-start justify-content-start">
                                <div class="sidebar_info_icon"><img src="images/info_1.png" alt></div>
                                <div class="sidebar_info_content"><span>Address: </span>Main Str, no 253, New York, NY</div>
                            </li>
                            <li class="d-flex flex-row align-items-start justify-content-start">
                                <div class="sidebar_info_icon"><img src="images/info_2.png" alt></div>
                                <div class="sidebar_info_content"><span>Phone: </span>+546 990221 123</div>
                            </li-->
                            <li class="d-flex flex-row align-items-start justify-content-start">
                                <div class="sidebar_info_icon"><img src="images/info_3.png" alt></div>
                                <div class="sidebar_info_content"><span>E-mail: </span><a href="mail-to:info@parloursbooking.com" class="__cf_email__" data-cfemail="">info@parlours</a></div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--div class="categories">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="categories_container d-flex flex-md-row flex-column align-items-start justify-content-start">
                    <div class="category text-center">
                        <a href="listings.html">
                            <div class="d-flex flex-md-column flex-row align-items-md-center align-items-md-start align-items-center justify-content-start">
                                <div class="cat_icon"><img src="images/icon_1.svg" class="svg" alt="https://www.flaticon.com/authors/monkik"></div>
                                <div class="cat_title">Restaurants</div>
                            </div>
                        </a>
                    </div>
                    <div class="category text-center">
                        <a href="listings.html">
                            <div class="d-flex flex-md-column flex-row align-items-md-center align-items-md-start align-items-center justify-content-start">
                                <div class="cat_icon"><img src="images/icon_2.svg" class="svg" alt="https://www.flaticon.com/authors/monkik"></div>
                                <div class="cat_title">Hotels</div>
                            </div>
                        </a>
                    </div>
                    <div class="category text-center">
                        <a href="listings.html">
                            <div class="d-flex flex-md-column flex-row align-items-md-center align-items-md-start align-items-center justify-content-start">
                                <div class="cat_icon"><img src="images/icon_3.svg" class="svg" alt="https://www.flaticon.com/authors/monkik"></div>
                                <div class="cat_title">Nightlife</div>
                            </div>
                        </a>
                    </div>
                    <div class="category text-center">
                        <a href="listings.html">
                            <div class="d-flex flex-md-column flex-row align-items-md-center align-items-md-start align-items-center justify-content-start">
                                <div class="cat_icon"><img src="images/icon_4.svg" class="svg" alt="https://www.flaticon.com/authors/monkik"></div>
                                <div class="cat_title">Coffeeshops</div>
                            </div>
                        </a>
                    </div>
                    <div class="category text-center">
                        <a href="listings.html">
                            <div class="d-flex flex-md-column flex-row align-items-md-center align-items-md-start align-items-center justify-content-start">
                                <div class="cat_icon"><img src="images/icon_5.svg" class="svg" alt="https://www.flaticon.com/authors/monkik"></div>
                                <div class="cat_title">Culture</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div-->
<div class="cta container_custom">
    <div class="parallax_background" data-image-src="images/cta.jpg"></div>
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="cta_content">
                    <div class="cta_title" style="text-align: center;"><h1>Find the best places in town!</h1></div>
                    <div class="cta_text" style="text-align: center;">
                        <p>Parlours booking Business is a platform for Connecting you to numerous salons and parlors over the island and lets you reserve your preferred services for beauty from any parlor with only a click.</p>
                    </div>
                    <!--div class="button cta_button"><a href="#">See the list</a></div-->
                </div>
            </div>
        </div>
    </div>
</div>
<div class="locations container_custom">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="section_title text-center"><h1>Explore Hot Saloons</h1></div>
                <div class="locations_container d-flex flex-row align-items-start justify-content-between flex-wrap">
                    <div class="location">
                        <img src="images/location_1.jpg" alt>
                        <div class="location_title text-center"><div><a href="#">Saloon 1<div>+</div></a></div></div>
                    </div>
                    <div class="location">
                        <img src="images/location_2.jpg" alt>
                        <div class="location_title text-center"><div><a href="#">Saloon 2<div>+</div></a></div></div>
                    </div>
                    <div class="location">
                        <img src="images/location_3.jpg" alt>
                        <div class="location_title text-center"><div><a href="#">Saloon 3<div>+</div></a></div></div>
                    </div>
                    <div class="location">
                        <img src="images/location_4.jpg" alt>
                        <div class="location_title text-center"><div><a href="#">Saloon 4<div>+</div></a></div></div>
                    </div>
                    <div class="location">
                        <img src="images/location_5.jpg" alt>
                        <div class="location_title text-center"><div><a href="#">saloon 5<div>+</div></a></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--div class="food container_custom">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="section_title text-center"><h1>Find the best services near you</h1></div>
                <div class="grid_container grid">
                    <div class="grid-item grid_large">
                        <div class="food_item">
                            <div class="food_item_tag"><a href="#">Hair Rebounding</a></div>
                            <div class="background_image" style="background-image:url(images/food_1.jpg)"></div>
                        </div>
                    </div>
                    <div class="grid-item grid_small">
                        <div class="food_item">
                            <div class="food_item_tag"><a href="#">Caratian treatment</a></div>
                            <div class="background_image" style="background-image:url(images/food_2.jpg)"></div>
                        </div>
                    </div>
                    <div class="grid-item grid_medium">
                        <div>
                            <div class="grid_half">
                                <div class="food_item">
                                    <div class="food_item_tag"><a href="#">Gold Facial</a></div>
                                    <div class="background_image" style="background-image:url(images/food_3.jpg)"></div>
                                </div>
                            </div>
                            <div class="grid_half">
                                <div class="food_item">
                                    <div class="food_item_tag"><a href="#">Pedicure</a></div>
                                    <div class="background_image" style="background-image:url(images/food_4.jpg)"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid-item grid_medium">
                        <div class="food_item">
                            <div class="food_item_tag"><a href="#">Manicure</a></div>
                            <div class="background_image" style="background-image:url(images/food_5.jpg)"></div>
                        </div>
                    </div>
                    <div class="grid-item grid_small">
                        <div class="food_item">
                            <div class="food_item_tag"><a href="#">Ash Tattoo</a></div>
                            <div class="background_image" style="background-image:url(images/food_6.jpg)"></div>
                        </div>
                    </div>
                    <div class="grid-item grid_large">
                        <div class="food_item">
                            <div class="food_item_tag"><a href="#">Something 1</a></div>
                            <div class="background_image" style="background-image:url(images/food_7.jpg)"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div-->
<!--div class="how container_custom">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="section_title text-center"><h1>How Directory<span>Plus+</span> works</h1></div>
                <div class="icon_box_container d-flex flex-row align-items-start justify-content-between flex-wrap">
                    <div class="icon_box d-flex flex-column align-items-center justify-content-start text-center">
                        <div class="icon_box_num">01.</div>
                        <div class="icon_box_icon"><img src="images/icon_6.svg" alt="https://www.flaticon.com/authors/monkik"></div>
                        <div class="icon_box_title">Choose a category</div>
                        <div class="icon_box_text">
                            <p>Donec cursus, risus non fermentum lacinia, felis lectus interdum velit, in pulvinar enim justo at sem. Quisque ut semper neque. Suspendisse quam est</p>
                        </div>
                    </div>
                    <div class="icon_box d-flex flex-column align-items-center justify-content-start text-center">
                        <div class="icon_box_num">02.</div>
                        <div class="icon_box_icon"><img src="images/icon_7.svg" alt="https://www.flaticon.com/authors/monkik"></div>
                        <div class="icon_box_title">Find your pick</div>
                        <div class="icon_box_text">
                            <p>Donec cursus, risus non fermentum lacinia, felis lectus interdum velit, in pulvinar enim justo at sem. Quisque ut semper neque. Suspendisse quam est</p>
                        </div>
                    </div>
                    <div class="icon_box d-flex flex-column align-items-center justify-content-start text-center">
                        <div class="icon_box_num">03.</div>
                        <div class="icon_box_icon"><img src="images/icon_8.svg" alt="https://www.flaticon.com/authors/monkik"></div>
                        <div class="icon_box_title">Go & have fun</div>
                        <div class="icon_box_text">
                            <p>Donec cursus, risus non fermentum lacinia, felis lectus interdum velit, in pulvinar enim justo at sem. Quisque ut semper neque. Suspendisse quam est</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div-->
<!--div class="cta_2">
    <div class="container">
        <div class="row row-lg-eq-height">
            <div class="col-lg-9 cta_2_col">
                <div class="cta_2_content">
                    <div class="cta_2_title"><h1>Get the Directory<span>Plus+</span> App</h1></div>
                    <div class="cta_2_text">
                        <p>Donec cursus, risus non fermentum lacinia, felis lectus interdum velit, in pulvinar enim justo at sem. Quisque ut semper neque. Suspendisse quam est, consequat ullamcorper tellus et, fauci bus laoreet nibh.Donec cursus, risus non fermentum lacinia, felis lectus interdum velit, in pulvinar enim justo at sem.</p>
                    </div>
                    <div class="store_links d-flex flex-row align-items-start justify-content-start flex-wrap">
                        <div class="store_link"><a href="#"><img src="images/store_1.jpg" alt></a></div>
                        <div class="store_link"><a href="#"><img src="images/store_2.jpg" alt></a></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 cta_2_col">
                <div class="cta_2_image">
                    <img src="images/cta_2.jpg" alt>
                </div>
            </div>
        </div>
    </div>
</div-->
<!--div class="clients">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="clients_slider_container">
                    <div class="owl-carousel owl-theme clients_slider">
                        <div class="slide">
                            <div class="client_image"><a href="#"><img src="images/client_1.jpg" alt></a></div>
                        </div>
                        <div class="slide">
                            <div class="client_image"><a href="#"><img src="images/client_2.jpg" alt></a></div>
                        </div>
                        <div class="slide">
                            <div class="client_image"><a href="#"><img src="images/client_3.jpg" alt></a></div>
                        </div>
                        <div class="slide">
                            <div class="client_image"><a href="#"><img src="images/client_4.jpg" alt></a></div>
                        </div>
                        <div class="slide">
                            <div class="client_image"><a href="#"><img src="images/client_5.jpg" alt></a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div-->
@stop