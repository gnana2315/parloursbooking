<!DOCTYPE html>
<html lang="en">
	<head>
        @include('includes.admin.head')
	</head>
	<body class="hold-transition sidebar-mini layout-fixed">
		<div class="wrapper">
            @include('includes.admin.preloader')
            
            @include('includes.admin.header')
            
            @include('includes.admin.sidebar')
            
            @yield('content')
            
            @include('includes.admin.footer')
		</div>
		<!-- ./wrapper -->
		
        @include('includes.admin.foot')
	</body>
</html>
