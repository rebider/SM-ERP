<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{asset('css/style.css')}}">
    <link rel="stylesheet" href="{{asset('layui/layui.css')}}" media="all">
    <link href="{{asset('css/layout.css')}}" rel="stylesheet">
    <link href="{{asset('layui/css/layui.css')}}" rel="stylesheet">
    <link href="{{asset('css/iframe-page.css')}}" rel="stylesheet">
    <link href="{{asset('layui/mods/extend/step/step.css')}}" rel="stylesheet">

    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>
    @yield('head')

</head>
<body>
<div class="kbmodel_full" id="app">
    @yield('content')
</div>
<script type="text/javascript" src="{{asset('js/jquery-1.11.3.min.js')}}"></script>
<script type="text/javascript" src="{{asset('layui/layui.js')}}"></script>
<script type="text/javascript" src="{{asset('js/korbin.js')}}"></script>
<script type="text/javascript" src="{{asset('js/kbPulic.js')}}"></script>
<script type="text/javascript" src="{{asset('js/common.js')}}?date={{time()}}"></script>
<script type="text/javascript" src="{{asset('js/leftBar.js')}}"></script>
    @yield('javascripts')
</body>

</html>