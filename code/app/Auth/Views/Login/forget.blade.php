<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>速贸天下云仓平台</title>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <link rel="stylesheet" href="{{asset('loginInfo/layui/css/layui.css')}}" media="all">
    <link href="{{asset('loginInfo/css/login.css')}}" rel="stylesheet">
</head>
<body class="graybg">
<div class="mgorder_head">
    <div class="logo"><h1>速贸天下云仓平台</h1></div>
</div>
<div class="regMid">
    <form action="" class="forgetForm regForm layui-form">
        {{ csrf_field() }}
        <div class="reghead"><h2 class="curr">找回密码</h2><a class="golog" href="{{url('login')}}">账号登录></a></div>
        <ul class="ulflex_col">
            <li><div class="intro"><input class="email" placeholder="请输入注册邮箱"  lay-verify="email" type="email" name="email" autocomplete="off" /></div></li>
            <li><div class="intro"><button class="submitform" lay-filter="confirm" lay-submit>找回密码</button></div></li>
        </ul>
    </form>
</div>

<script type="text/javascript" src="{{asset('loginInfo/js/jquery-1.11.3.min.js')}}"></script>
<script type="text/javascript" src="{{asset('loginInfo/layui/layui.js')}}"></script>
<script type="text/javascript" src="{{asset('loginInfo/js/cyn.js')}}"></script>
<script>
    layui.use(['form', 'laydate','table'], function(){
        var form = layui.form,layer = layui.layer,table = layui.table,laydate = layui.laydate;

        form.verify({
            email: function (value, item) {
                if (value == '') {
                    return "邮箱不能为空";
                }
            },
        });

        form.on('submit(confirm)', function(data){
            $.ajax({
                type: 'post',
                dataType: 'json',
                url: '',
                data: $('.forgetForm').serialize(),
                success: function(response) {
                    if (response.Status) {
                        layer.msg(response.Message, {time:2000, icon: 1});
                        setTimeout(function () {
                        },1000);
                    } else {
                        var data = response.Data;
                        var alertMessage = response.Message;
                        if (data != null) {
                            for (var i=0; i < data.length; i++) {
                                alertMessage += data[i] + '<br/>';
                            }
                        }
                        layer.msg(alertMessage, {icon: 5});
                    }
                },
                error: function(e, x, d) {
                    layer.msg(d, {icon: 5})
                }
            });
            return false;
        });
        var validCode=true;
        $(".msgs").click (function  () {
            var time=60;
            var code=$(this);
            if (validCode) {
                validCode=false;
                code.addClass("contdw");
                var t=setInterval(function  () {
                    time--;
                    code.html(time+"秒");
                    if (time==0) {
                        clearInterval(t);
                        code.html("重新获取");
                        validCode=true;
                        code.removeClass("contdw");

                    }
                },1000)
            }
        });
    })
</script>
</body>
</html>