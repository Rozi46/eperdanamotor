<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>@yield('title') | PerdanaMotor</title>
        <link rel="shortcut icon" href="{{ url('/themes/admin/AdminOne/image/public/icon.png') }}"/>

        {{-- CSS --}}
        <link rel="stylesheet" href="{{ url('/themes/admin/AdminOne/lib/fontawesome/css/font-awesome.min.css') }}"/>
        <link rel="stylesheet" href="{{ url('/themes/admin/AdminOne/lib/bootstrap-4.5.0/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="{{ url('/themes/admin/AdminOne/lib/selectron.min.css') }}">
        <link rel="stylesheet" href="{{ url('/themes/admin/AdminOne/lib/bootstrap-datepicker3.min.css') }}">
        <link rel="stylesheet" href="{{ url('/themes/admin/AdminOne/lib/loopslider.css') }}">
        <link rel="stylesheet" href="{{ url('/themes/admin/AdminOne/css/header.css') }}">
        <link rel="stylesheet" href="{{ url('/themes/admin/AdminOne/css/sidebar.css') }}">
        <link rel="stylesheet" href="{{ url('/themes/admin/AdminOne/css/style.css') }}">
        <link rel="stylesheet" href="{{ url('/themes/admin/AdminOne/css/home.css') }}">
        <link rel="stylesheet" href="{{ url('/themes/admin/AdminOne/js/ui/jquery-ui.css') }}">
        <link rel="stylesheet" href="{{ url('/themes/admin/AdminOne/select/css/select2.min.css') }}"/>

        {{-- Tambahan CSS untuk login jika belum login --}}
        @if (!session('admin_login_perdana'))  
            <link rel="stylesheet" href="{{ url('/themes/admin/AdminOne/css/login.css') }}">
        @endif

        {{-- JS --}}
        <script src="{{ url('/themes/admin/AdminOne/lib/jquery-3.5.1.min.js') }}"></script>
        <script src="{{ url('/themes/admin/AdminOne/lib/selectron.min.js') }}"></script>
        <script src="{{ url('/themes/admin/AdminOne/lib/jquery.loopslider.js') }}"></script>
        <script src="{{ url('/themes/admin/AdminOne/lib/popper.min.js') }}"></script>
        <script src="{{ url('/themes/admin/AdminOne/lib/bootstrap-4.5.0/js/bootstrap.min.js') }}"></script>
        <script src="{{ url('/themes/admin/AdminOne/lib/bootstrap-datepicker.js') }}"></script>
        <script src="{{ url('/themes/admin/AdminOne/lib/bootstrap-datepicker.id.min.js') }}"></script>
        <script src="{{ url('/themes/admin/AdminOne/lib/ckeditor/ckeditor.js') }}"></script>
        <script src="{{ url('/themes/admin/AdminOne/lib/highcharts.js') }}"></script>
        <script src="{{ url('/themes/admin/AdminOne/js/ui/jquery-ui.js') }}"></script>
        <script src="{{ url('/themes/admin/AdminOne/js/system.min.js') }}"></script>
        <script src="{{ url('/themes/admin/AdminOne/js/sweetalert2.all.min.js') }}"></script>
        <script src="{{url('/themes/admin/AdminOne/select/js/select2.min.js')}}"></script>
    </head>
    <body line="linebody" class="linebody">
        @if (session('admin_login_perdana'))
            @if (!isset($request['tipe_page']) || $request['tipe_page'] != 'full')
                @include('admin.AdminOne.layout.header')
                @include('admin.AdminOne.layout.sidebar')
            @else
                @yield('content')
            @endif
        @endif

        @yield('login')
        @yield('script')
    </body>
</html>
