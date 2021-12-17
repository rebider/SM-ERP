<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>速贸天下云仓平台</title>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <link href="{{asset('css/layout.css')}}" rel="stylesheet">
    <link href="{{asset('css/tabstyle.css')}}" rel="stylesheet">
    <link href="{{asset('js/jquery-1.11.3.min.js')}}" rel="stylesheet">
    <link href="{{asset('js/korbin.js')}}" rel="stylesheet">
    <link href="{{asset('js/tab.js')}}" rel="stylesheet">
    <style>body{overflow-y: hidden;}</style>
</head>
<body>
<div class="kbin_head fclear">
    <div class="logo"><a class="kba" href="home.html">速贸天下云仓平台</a></div>
    <div class="nav kbin-menu fclear">
        <ul>
            <li class="vli"><a class="kba homebtn" href="home.html"><span>首页</span></a></li>
            <li class="vli"><a href="javascript:;"><span>商品</span><i class="arr kbico"></i></a>
                <div class="down_menu">
                    <div class="row">
                        <h3>商品管理</h3>
                        <a class="kba" href="goodsCollect">商品采集</a><a class="kba" href="stmenu12.html">本地商品</a>
                        <a class="kba" href="stmenu13.html">商品映射</a><a class="kba" href="stmenu14.html">商品分类</a>
                    </div>
                    <div class="row">
                        <h3>乐天</h3>
                        <a class="kba" href="stmenu15.html">草稿箱</a><a class="kba" href="stmenu16.html">在线商品</a>
                    </div>
                    <div class="row">
                        <h3>亚马逊</h3>
                        <a class="kba" href="stmenu17.html">草稿箱</a><a class="kba" href="stmenu18.html">在线商品</a>
                        <a class="kba" href="stmenu19.html">UPC码</a>
                    </div>
                </div>
            </li>
            <li class="vli"><a href="javascript:;"><span>采购</span><i class="arr kbico"></i></a>
                <div class="down_menu">
                    <div class="row">
                        <h3>采购管理</h3>
                        <a class="kba" href="stmenu21.html">采购计划</a><a class="kba" href="stmenu22.html">采购单</a>
                        <a class="kba" href="stmenu23.html">供应商管理</a>
                    </div>
                    <div class="row">
                        <h3>库存管理</h3>
                        <a class="kba" href="stmenu24.html">库存查询</a><a class="kba" href="stmenu25.html">库存分配</a>
                    </div>
                </div>
            </li>
            <li class="vli"><a href="javascript:;"><span>订单</span><i class="arr kbico"></i></a>
                <div class="down_menu">
                    <div class="row">
                        <h3>订单管理</h3>
                        <a class="kba" href="stmenu31.html">原始订单</a><a class="kba" href="stmenu32.html">原始订单</a>
                        <a class="kba" href="stmenu33.html">待配货订单</a><a class="kba" href="stmenu34.html">配货单</a>
                    </div>
                    <div class="row">
                        <h3>售后管理</h3>
                        <a class="kba" href="stmenu35.html">售后单</a>
                    </div>
                </div>
            </li>
            <li class="vli"><a href="javascript:;"><span>高级设置</span><i class="arr kbico"></i></a>
                <div class="down_menu">
                    <div class="row">
                        <h3>订单规则设置</h3>
                        <a class="kba" href="stmenu41.html">订单问题规则</a><a class="kba" href="stmenu42.html">仓库分派规则</a>
                        <a class="kba" href="stmenu43.html">物流分派规则</a><a class="kba" href="stmenu44.html">拆单/合单规则</a>
                    </div>
                    <div class="row">
                        <h3>基础信息</h3>
                        <a class="kba" href="stmenu45.html">店铺管理</a><a class="kba" href="stmenu46.html">仓库管理</a>
                        <a class="kba" href="stmenu47.html">物流管理</a><a class="kba" href="stmenu48.html">汇率管理</a>
                        <a class="kba" href="stmenu49.html">公告管理</a>
                    </div>
                </div>
            </li>
            <li class="vli"><a href="javascript:;"><span>基础设置</span><i class="arr kbico"></i></a>
                <div class="down_menu">
                    <div class="row">
                        <h3>个人中心</h3>
                        <a class="kba" href="stmenu51.html">子账号</a><a class="kba" href="stmenu52.html">个人信息</a>
                    </div>
                </div>
            </li>
        </ul>
    </div>
    <div class="navbar-right">
        <ul>
            <li><div class="user">欢迎您，<em>六千余</em></div></li>
            <li><a href="" class="logout">退出<i class="Exit kbico"></i></a></li>
        </ul>
    </div>
</div>

<!--iframe Start-->
<div id="page-tab">
    <button class="tab-btn" id="page-prev"></button>
    <nav id="page-tab-content">
        <div id="menu-list">
            <a href="javascript:void(0);" data-url="{{url('Goods/home')}}" data-value="首页" class="defaultTab homebtn active">首页</a>
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
    <iframe class="iframe-content defaultIframe active" data-url="home.html" data-value="首页" src="{{ url('Goods') }}"></iframe>
</div>


<script>
    $(".kbin-menu .kba").tab();
     	$(function(){
     		$("body").bind("keydown",function(event){
         if (event.keyCode == 116) {
                    event.preventDefault(); //阻止默认刷新
              $(".iframe-content").attr("src",window.frames["iframe-content"].src);

        }
    })
     	})
</script>
</body>
</html>