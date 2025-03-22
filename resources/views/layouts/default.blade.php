<!DOCTYPE html>
<html lang="en">
	<head>
        @include('includes.defaultHead')
	</head>
	<body class="hold-transition sidebar-mini layout-fixed">
		<div class="wrapper">
                        <!--@include('includes.admin.preloader')-->            
            
                        @yield('content')
            
                        @include('includes.defaultFooter')
		</div>
		<!-- ./wrapper -->
		
                @include('includes.defaultFoot')
	</body>
</html>
