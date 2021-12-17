<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    @if(Session::get('title'))
        <title>融达通工单系统-{{ Session::pull('title') }}</title>
    @else
        <title>融达通工单系统</title>
    @endif
    <meta name="description" content="">
    <meta name="keywords" content="">
    <link href="{{asset('layui/layui.css')}}" rel="stylesheet">
    <link href="{{asset('css/style.css')}}" rel="stylesheet">
</head>
<body class="bgGray">
<div class="head">
    <div class="logo">
        <h1><a href="/"><img onerror="this.src='/img/imgNotFound.jpg'"  src="../images/logo2.png" alt=""/></a></h1>
    </div>
    <div class="userside">
        <ul>
            <li>
                <div class="linkps headAnimat">
                    <span class="hd"><img onerror="this.src='/img/imgNotFound.jpg'"  src="../images/hd.jpg" alt=""/></span><em>{{ $userName }}<i class="moreArr"></i></em>
                    <div class="slide">
                        <a href="{{ url('logout') }}">退出</a>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</div>
<div class="sideMenu">
    <ul class="layui-nav layui-nav-tree layui-nav-side">
        @foreach($userNavigation as $key => $nav)
            <li class="layui-nav-item {{ $key == 0 ? 'layui-nav-itemed' : '' }}">
                <a href="javascript:;" {{ $nav['url'] ? 'data-url='.url($nav['url']) : '' }}><i class="icon {{ isset($nav['icon']) ? $nav['icon'] : '' }}"></i>{{ $nav['name'] }}</a>
                @if(isset($nav['_child']))
                    <dl class="layui-nav-child">
                        @foreach($nav['_child'] as $child)
                            <dd><a href="javascript:;" data-url="{{ url($child['url']) }}">{{ $child['name'] }}</a></dd>
                        @endforeach
                    </dl>
                @endif
            </li>
        @endforeach
    </ul>
</div>
<div id="container">
    @if(Session::get('url'))
        {{--<iframe id="main_iframe" src="{{ Session::pull('url') }}" frameborder="0" ></iframe>--}}
    @else
        {{--<iframe id="main_iframe" src="{{ $home ? url($home['url']) : '' }}" frameborder="0"></iframe>--}}
    @endif
</div>

<script type="text/javascript" src="{{asset('js/jquery-1.11.3.min.js')}}"></script>
<script type="text/javascript" src="{{asset('layui/layui.js')}}"></script>
<script type="text/javascript" src="{{asset('js/korbin.js')}}"></script>
<script>
    layui.use(['layer', 'form', 'element'], function () {
        var layer = layui.layer, form = layui.form, element = layui.element;
    });
    $("body").bind("keydown", function (event) {
        if (event.keyCode == 116) {
            event.preventDefault(); //阻止默认刷新
            $("#main_iframe").attr("src", window.frames["main_iframe"].src);
        }
    });
</script>
</body>
</html>