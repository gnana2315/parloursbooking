<!DOCTYPE html>
<html lang="en">
	<head>
        @include('includes.admin.head')
	</head>
	<body class="hold-transition sidebar-mini layout-fixed">
		<div class="wrapper">
                        <!--@include('includes.admin.preloader')-->
            
                        @include('includes.admin.header')
                        @auth
                                @if(auth()->user()->pbu_usertype == '2')
                                        @include('includes.admin.vendorsidebar')
                                @endif
                                @if(auth()->user()->pbu_usertype == '1')
                                        @include('includes.admin.sidebar')
                                @endif
                                @if(auth()->user()->pbu_usertype == '0')
                                        @include('includes.admin.sidebar')
                                @endif
                        @endauth
            
                        @yield('content')
            
                        @include('includes.admin.footer')
		</div>
		<!-- ./wrapper -->
		
                @include('includes.admin.foot')
	</body>
</html>
