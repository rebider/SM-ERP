<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <title>速贸天下云仓平台</title>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{asset('css/layout.css')}}" rel="stylesheet">
    <link href="{{asset('layui/css/layui.css')}}" rel="stylesheet">

    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>
    @yield('head')
</head>
<body>
<div class="container_full" id="app">
    @yield('content')
</div>
<script type="text/javascript" src="{{asset('js/jquery-1.11.3.min.js')}}"></script>
<script type="text/javascript" src="{{asset('js/korbin.js')}}"></script>
<script type="text/javascript" src="{{asset('layui/layui.js')}}"></script>
<script type="text/javascript" src="{{asset('js/highcharts-6.2.0.js')}}"></script>
<script type="text/javascript" src="{{asset('js/highcharts-cn.js')}}"></script>
<script type="text/javascript" src="{{asset('js/leftBar.js')}}"></script>
    @yield('javascripts')
</body>

</html>