<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>速贸天下云仓平台</title>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <link rel="stylesheet" href="{{ asset('loginInfo/layui/css/layui.css')}}">
    <link href="{{asset('loginInfo/css/login.css')}}" rel="stylesheet">
</head>
<body class="colorbg">
<div class="log_head">
    <div class="logo"><h1>速贸天下云仓平台</h1></div>
</div>
<div class="bodyMid">
    <div class="loginform">
        <div class="panelAdv">
            <div class="pic"><img src="{{asset('loginInfo/img/login1.png')}}" alt="" /></div>
        </div>
        <div class="logbody">
            <div class="logtitle"><h3>用户登录</h3></div>
            <form class="formList layui-form" action="{{ url('auth/doLogin') }}" method="post">
                {{ csrf_field() }}
                <ul>
                    <li style="color:red;">{{ empty(session('message')) ? "" : session('message') }}</li>
                    <li class="name" style="position: relative;">
                        <div class="userCode" style="width: 12px;height: 16px;position: absolute;top: 11px;left: 11px;background: url('{{asset('loginInfo/img/user.png')}}')"></div>
                        <input type="text" style="background: transparent;" lay-verify="userCode" name="userCode" id="userCode" placeholder="账号" value="{{ empty(session('userCode')) ? "": session('userCode') }}"/></li>
                    <li class="pw" style="position: relative;">
                        <div class="userCode" style="width: 12px;height: 16px;position: absolute;top: 11px;left: 11px;background: url('{{asset('loginInfo/img/pw.png')}}')"></div>
                        <input type="password" style="background: transparent;" lay-verify="password" name="password" id="password" placeholder="密码" /></li>
                    <li><div id="slidverify" class="slider">
                        </div><input type="text" style="display: none" lay-verify="sliverify" class="iptverify" name='sliverify' value=""/>
                    </li>
                    <li><button lay-submit id="confirm">登录</button></li>
                </ul>
            </form>
            <div class="logfoot"><div class="logfl">还没有账号？<a href="{{url('register')}}">注册账号</a></div><div class="logfr"><a href="{{url('forget')}}">找回密码</a></div></div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ asset('loginInfo/js/jquery-1.11.3.min.js')}}"></script>
<script type="text/javascript" src="{{ asset('loginInfo/layui/layui.js')}}"></script>
<script type="text/javascript" src="{{asset('loginInfo/js/ftslider.js')}}"></script>
<script type="text/javascript" src="{{asset('loginInfo/js/jquery.slider.min.js')}}"></script>
<script>
    layui.use(['form', 'laydate','table'], function(){
        var form = layui.form,layer = layui.layer,table = layui.table,laydate = layui.laydate;

        form.verify({
            userCode: function (value, item) {
                if (value == '') {
                    return "账号不能为空";
                }
            },
            password: function (value, item) {
                if (value == '') {
                    return "密码不能为空";
                }
            },
            sliverify: function (value, item) {
                if (value == '') {
                    return "请滑动验证";
                }
            }
        });

        $('.ui-slider-btn').mouseup(function(){
            if ($(this).hasClass('success')) {
                $('.iptverify').addClass('ewrw');
                $('input[name="sliverify"]').val('验证成功');
            };
        })

//        form.on('submit(confirm)', function(data){
//            layer.msg('登录成功');
//            return false;
//        });
    })

</script>
</body>
</html>