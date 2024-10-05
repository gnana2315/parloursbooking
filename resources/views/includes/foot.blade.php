<script src="{{ URL::asset('plugins/greensock/TweenMax.min.js')}}"></script>
<script src="{{ URL::asset('plugins/greensock/TimelineMax.min.js')}}"></script>
<script src="{{ URL::asset('plugins/scrollmagic/ScrollMagic.min.js')}}"></script>
<script src="{{ URL::asset('plugins/greensock/animation.gsap.min.js')}}"></script>
<script src="{{ URL::asset('plugins/greensock/ScrollToPlugin.min.js')}}"></script>
<script src="{{ URL::asset('plugins/Isotope/isotope.pkgd.min.js')}}"></script>
<script src="{{ URL::asset('plugins/OwlCarousel2-2.3.4/owl.carousel.js')}}"></script>
<script src="{{ URL::asset('plugins/easing/easing.js')}}"></script>
<script src="{{ URL::asset('plugins/progressbar/progressbar.min.js')}}"></script>
<script src="{{ URL::asset('plugins/parallax-js-master/parallax.min.js')}}"></script>
<script src="{{ URL::asset('js/custom.js')}}"></script>

<script async src="https://www.googletagmanager.com/gtag/js?id=UA-23581568-13"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'UA-23581568-13');
    
    function GFG_Fun() {
        let width = window.innerWidth;
        console.log(width + " pixels");
        if(width < 768){
            
        }
    }
    
    GFG_Fun();
</script>
<script>
	$(document).ready(function() {
        var path = window.location.pathname;
        if (path === '/' || path === '/index.php' || path === '/home.php') {
            $('.header').addClass('home_header');
        } else {
            $('.header').removeClass('home_header');
        }
	});

    $(window).on('load', function() {
        // Hide the loader and show the content
        $('#loader').fadeOut(50000, function() {
            $('#content').fadeIn(100);
        });
    });


    $("#servicesBookNow").on('click', function(){
        var items = [];
        var tableBody = '';
        
        // Iterate over selected checkboxes
        $('input[name="listedservices"]:checked').each(function () {
            var service = $(this).data('name');
            var value = $(this).val();
            
            // Create an object and push it to the array
            items.push({ service: service, value: value });
            
            var row = '<tr><td>' + service +'</td><td>LKR ' + value + '.00</td></tr>';
            $('#itemsTable tbody').append(row);
        });
        
        // Display form values in the modal
        //$('#modalBody').html(items);

        // Show the modal
        $('#booking').modal('show');
    });
</script>