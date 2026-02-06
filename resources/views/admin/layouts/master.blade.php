
@include('admin.layouts.header')

<meta name="csrf-token" content="{{ csrf_token() }}">

@yield('main_content')

@include('admin.layouts.footer')
