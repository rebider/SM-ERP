<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>{{ __('auth.rdtSystem') }}</title>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <link rel="stylesheet" href="{{ asset('layui/layui.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
<div class="login-bg">
    <div class="logPanel">
        <h1><img src="images/logo.png" alt=""/></h1>
        <form class="form-item layui-form" action="{{ url('auth/doLogin') }}" method="post">
            {{ csrf_field() }}
            <ul>
                <li style="color:red;">{{ empty(session('message')) ? "" : session('message') }}</li>
                <li>
                    <input type="text" name="userCode" id="userCode" class="layui-input" placeholder="账号" value="{{ empty(session('userCode')) ? "": session('userCode') }}">
                </li>
                <li>
                    <input type="password" name="password" class="layui-input" placeholder="密码">
                </li>
                <li id="conVerifyCode" style="float:left;{{ session('requiredVCode') || $requiredVCode ? "" : "display:none;" }}">
                    <input type="text" name="verifyCode" class="layui-input" placeholder="验证码" style="width:180px;display: inline-block;">
                    <img src="/auth/verifyCode" onclick="refreshVCode()" id="VerifyCodeImg" style="margin-left: 10px;" />
                    <a href="javascript:void(0);" onclick="refreshVCode()" style="padding-left:10px;padding-top:12px;">换一张</a>
                </li>
                <li>
                    <button class="layui-btn layui-btn-normal" type="submit">登 录</button>
                </li>
            </ul>
        </form>
    </div>
</div>
<script type="text/javascript" src="{{ asset('js/jquery-1.11.3.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/login.js?'.time()) }}"></script>
</body>
</html>