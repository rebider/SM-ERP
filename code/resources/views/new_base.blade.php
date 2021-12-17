<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>速贸云仓平台</title>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <link href="{{asset('css/layout.css')}}" rel="stylesheet">
    <link href="{{asset('css/tabstyle.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('layui/layui.css')}}" media="all">
    <style>body{overflow: hidden;}</style>
</head>

<body style="">
<div class="kbin_head fclear">
    <div class="logo"><a class="kba" href="/">速贸云仓平台</a></div>
    <div class="nav kbin-menu fclear">
        <ul>
            @if (isset($userNavigation) && !empty($userNavigation))
            @foreach($userNavigation as $key => $nav)
                <li class="vli">
                    <a href="{{$nav['url'] ? $nav['url'] : 'javascript:;'}}" class=" kbico
                    @if ($key == 0)
                    {{'kba homebtn'}}
                    @else
                    {{'ktico'.$key}}
                    @endif
                            ">
                        <span>{{ $nav['name'] }}</span>
                        @if(isset($nav['_child']))<i class="arr kbico"></i>@endif
                    </a>
                    @if(isset($nav['_child']))
                        <div class="down_menu">
                            @foreach($nav['_child'] as $child)
                                <div class="row">
                                    @if(isset($child['_child']))
                                        <h3 class="kbico">{{$child['name']}}</h3>
                                        @foreach($child['_child'] as $menu)
                                            @if ($menu['url'] == 'user/detail')
                                                <a class="kba jumpMenu" data-id="{{$nav['id']}}" data-parent-id="{{$nav['id']}}" href="{{$menu['url']}}/{{\App\Auth\Common\CurrentUser::getCurrentUser()->userId}}">{{$menu['name']}}</a>
                                            @else
                                                <a class="kba jumpMenu" data-id="{{$nav['id']}}" data-parent-id="{{$nav['id']}}" href="{{$menu['url']}}">{{$menu['name']}}</a>
                                            @endif
                                        @endforeach
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </li>
            @endforeach
            @endif
        </ul>
    </div>
    <div class="navbar-right">
        <ul>
            <li><div class="user">欢迎您，<em>{{ $userName }}</em></div></li>
            <li><a href="{{ url('logout') }}" class="logout">退出<i class="Exit kbico"></i></a></li>
        </ul>
    </div>
</div>
@include('layouts/shortcutMenus')

<!--iframe Start-->
<div id="page-tab">
    <button class="tab-btn" id="page-prev"></button>
    <nav id="page-tab-content">
        <div id="menu-list">
            <a href="javascript:void(0);" data-url="/home" data-value="首页" class="defaultTab homebtn active">首页</a>
        </div>
    </nav>
    <button class="tab-btn" id="page-next"></button>
    <div id="page-operation">
        <div id="menu-all">
            <ul id="menu-all-ul">
                <li class="closeOther">关闭其他页签</li>
                <li class="closeAll">关闭所有页签</li>
            </ul>
        </div>
    </div>
</div>

<div id="page-content" style="">
    {{--@if(Session::get('url'))--}}
        {{--<iframe  class="iframe-content defaultIframe active"  src="{{ Session::pull('url') }}"></iframe>--}}
    {{--@else--}}
        <iframe  class="iframe-content defaultIframe active"  src="{{ $home ? $home['url'] : '/home' }}"></iframe>
    {{--@endif--}}
</div>

<script type="text/javascript" src="{{asset('js/jquery-1.11.3.min.js')}}"></script>
<script type="text/javascript" src="{{asset('js/korbin.js')}}"></script>
<script type="text/javascript" src="{{asset('layui/layui.js')}}"></script>
<script type="text/javascript" src="{{asset('js/tab.js')}}"></script>
<script type="text/javascript" src="{{asset('js/leftBar.js')}}"></script>
<script>
    layui.use(['layer', 'form', 'element'], function () {
        var layer = layui.layer, form = layui.form, element = layui.element;
    });
    $(".kbin-menu .kba").tab();
    $(function(){
        $("body").bind("keydown",function(event){
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location=location;
            }
        })
    });
</script>
</body>
</html>