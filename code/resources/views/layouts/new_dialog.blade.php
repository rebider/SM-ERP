<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('layui/layui.css') }}">
    <link href="{{asset('layui/css/layui.css')}}" rel="stylesheet">
    <link href="{{asset('css/layout.css')}}" rel="stylesheet">
    <link href="{{asset('layui/mods/extend/step/step.css')}}" rel="stylesheet">
    @yield('css')
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>
</head>
<body>


@yield('content')


<script type="text/javascript" src="{{ asset('js/jquery-1.11.3.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('layui/layui.js') }}"></script>
<script type="text/javascript" src="{{asset('js/korbin.js')}}"></script>
<script type="text/javascript" src="{{asset('js/kbPulic.js')}}"></script>
<script type="text/javascript" src="{{asset('js/common.js')}}"></script>
@yield('javascripts')
</body>
</html>