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
        <div class="reghead"><h2 class="curr">重置密码</h2><a class="golog" href="{{url('login')}}">账号登录></a></div>
        <ul class="ulflex_col">
            <li><div class="intro"><div class="pwtro"><input class="pw1" lay-verify="password" name='password' type="password" minlength="8"  maxlength="16" placeholder="密码8~16位，数字、字母、特殊字符等组合" value="{{ old('password') ??'' }}" autocomplete="off" /><em class="eyepw closeEye"><i></i></em></div>
                </div></li>
            <li><div class="intro"><div class="pwtro"><input class="pw1" lay-verify="password" name='password_confirmation' minlength="8"  maxlength="16" type="password" placeholder="确认密码" value="" autocomplete="off" /><em class="eyepw closeEye"><i></i></em></div>
                </div></li>
            <li><div class="intro"><button class="submitform" lay-filter="confirm" lay-submit>重置密码</button></div></li>
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
            password: function (value, item) {
                if (value == '') {
                    return "密码不能为空";
                }
            },
            password_confirmation : function (value, item) {
                if (value == '') {
                    return "确认密码不能为空";
                }
            },
        });

        form.on('submit(confirm)', function(data){
            var password = $('input[name="password"]').val();
            var password_confirmation = $('input[name="password_confirmation"]').val();
            if (password != password_confirmation) {
                layer.msg('两次密码不一致', {icon: 5});
                return false
            }
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
    })
</script>
</body>
</html>