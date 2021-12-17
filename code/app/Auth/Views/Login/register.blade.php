<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <title>速贸天下云仓平台</title>
    <meta name="description" content="">
    <meta name="keywords" content="">
    <link rel="stylesheet" href="{{asset('loginInfo/layui/css/layui.css')}}" media="all">
    <link href="{{asset('loginInfo/css/login.css?v1.1.2')}}" rel="stylesheet">
    <style>
        .regForm .ulflex_col li.tags{position: relative;}
        .regForm .ulflex_col li.tags::after{content: '*';display: block;color: red;position: absolute;left: -10px;top: 13px;}
    </style>
</head>
<body class="graybg">
<div class="mgorder_head">
    <div class="logo"><h1>速贸天下云仓平台</h1></div>
</div>
<div class="regMid">
    <form action="" class="regForm layui-form" method="post">
        <div class="reghead"><h2 class="curr">用户注册</h2><a class="golog" href="{{url('login')}}">账号登录></a></div>
        <ul class="ulflex_col">
            {{ csrf_field() }}
            <li class="tags"><div class="intro"><input class="name" readonly onfocus="this.removeAttribute('readonly');" lay-verify="user_code" name='user_code' placeholder="账号名" type="text" value="{{ old('user_code') ??'' }}" autocomplete="off" minlength="5" maxlength="50"/>
                </div></li>
            <li class="tags"><div class="intro"><input class="email" readonly onfocus="this.removeAttribute('readonly');" lay-verify="emailEmpty|email" name='email' placeholder="注册邮箱" type="text" value="{{ old('email') ??'' }}" autocomplete="off"/>
                </div></li>
            <li class="tags"><div class="intro"><input class="phone" readonly onfocus="this.removeAttribute('readonly');" lay-verify="mobileEmpty" name='mobile' maxlength="18" placeholder="注册手机号" type="text" value="{{ old('mobile') ??'' }}"  autocomplete="off"/>
                </div></li>
            <li class="tags"><div class="intro"><div class="pwtro"><input class="pw1" readonly onfocus="this.removeAttribute('readonly');" lay-verify="password" name='password' type="password" placeholder="密码8~50位，数字、字母、特殊字符组合" minlength="8" maxlength="50" value="{{ old('password') ??'' }}" autocomplete="off" /><em class="eyepw closeEye"><i></i></em></div>
                </div></li>
            <li class="tags"><div class="intro"><div class="pwtro"><input class="pw1" readonly onfocus="this.removeAttribute('readonly');" lay-verify="password" name='password_confirmation' type="password" placeholder="确认密码" value="" minlength="8" maxlength="50" autocomplete="off" /><em class="eyepw closeEye"><i></i></em></div>
                </div></li>
            <li class="tags"><div class="intro"><input class="name" lay-verify="username" readonly onfocus="this.removeAttribute('readonly');" placeholder="联系人" minlength="2" maxlength="50"  name='username' type="text" value="{{ old('username') ??'' }}" autocomplete="off"/>
                </div></li>
            <li><div class="intro"><input class="Bname" placeholder="公司名称" readonly onfocus="this.removeAttribute('readonly');" name='company_name' maxlength="50" lay-verify="company" type="text" value="{{ old('company_name') ??'' }}"  autocomplete="off"/>
                </div></li>

            {{--<li><div class="intro">--}}
                    {{--<select class="addr" placeholder="请选择省" name='address_province'>--}}
                            {{--<option value="">请选择省</option>--}}
                    {{--</select>--}}
                    {{--&nbsp;--}}
                    {{--<select class="addr" placeholder="请选择市" name='address_city'>--}}
                        {{--<option value="">请选择市</option>--}}
                    {{--</select>--}}
                {{--</div></li>--}}

            <li><div class="intro">
                    <input class="addr" placeholder="地址" readonly onfocus="this.removeAttribute('readonly');" name='address' maxlength="150"  type="text" value="{{ old('address') ??'' }}"  autocomplete="off"/>
                </div></li>

            <li><div class="intro"><input class="phone" readonly onfocus="this.removeAttribute('readonly');" placeholder="电话(区号-电话号-分机号)" name='phone' minlength="5" maxlength="50"  type="text" value="{{ old('phone') ??'' }}"  autocomplete="off"/>
                </div></li>

            <li><div class="intro"><button class="submitform" lay-filter="register" lay-submit>确认注册</button></div></li>
            {{--<li><input type="checkbox" lay-skin="primary" class="protocol" checked lay-verify="protocol" title="注册协议" /></li>--}}
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
//            protocol: function (value, item) {
//                if (!$(".protocol").is(":checked")) {
//                    return "请选择同意注册协议";
//                }
//            },
            password: function (value, item) {
                if (value == '') {
                    return "密码不能为空";
                }
            },
            user_code:function (value, item) {
                if (value == '') {
                    return "账号名不能为空";
                }else if (value.length < 5) {
                    return "账号名字符长度不能小于5位";
                }
            },
            mobileEmpty: function (value, item) {
                if (value == '') {
                    return "手机号不能为空";
                }
                if (value.length < 7 || value.length > 20) {
                    return "手机号长度为7-20个字符";
                }
            },
            emailEmpty: function (value, item) {
                if (value == '') {
                    return "邮箱不能为空";
                }
            },
            username: function (value, item) {
                if (value == '') {
                    return "联系人不能为空";
                }
            },
        });

        form.on('submit(register)', function(){
            var regForm = $('.regForm');
            var exception_mes = [];
            var password = regForm.find('input[name="password"]').val();
            var username = regForm.find('input[name="user_code"]').val();
            if (password == '') {
                exception_mes.push('密码不能为空')
            }
            var password_confirmation = regForm.find('input[name="password_confirmation"]').val();
            if (password_confirmation == '') {
                exception_mes.push('确认密码不能为空')
            }
            if (password != password_confirmation) {
                exception_mes.push('两次输入密码不一致')
            }
            /*
            1、长度：8 位≤长度≤50 位。
            2： 不能使用中文字符。
            3：等于 8 位时，必须包含数字、小写
            字母、大写字母或特殊字符。
            4、不能包含用户名。
            5、不能有连续 3 次的字符或一些顺序
            的字符。
            7、不能包含空格。
            */
            if(!(password.length>=8 && password.length<=50)){
                layer.msg('密码长度：8 位≤长度≤50 位。', {icon: 5});
                return false;
            }
            var chinese =/[\u4e00-\u9fa5]/;
            if(chinese.test(password)){
                layer.msg('密码不能使用中文字符', {icon: 5});
                return false;
            }
            if (password.length ==8) {
                var numberLetter =/[0-9a-z]+/,
                    Bigstr = /[A-Z]/,
                    str =/((?=[\x21-\x7e]+)[^A-Za-z0-9])/;
                if(!(numberLetter.test(password) && (str.test(password) || Bigstr.test(password)))){
                    layer.msg('密码必须包含数字、小写字母、大写字母或特殊字符。', {icon: 5});
                    return false;
                }
            }
            if(password.indexOf(username)>=0){
                layer.msg('密码不能包含用户名', {icon: 5});
                return false;
            }

           var LxStr = function(str){
                var arr = str.split('');
                var flag = false;
                for (var i = 1; i < arr.length-1; i++) {
                    var firstIndex = arr[i-1].charCodeAt();
                    var secondIndex = arr[i].charCodeAt();
                    var thirdIndex = arr[i+1].charCodeAt();
                    thirdIndex - secondIndex == 1;
                    secondIndex - firstIndex==1;
                    if((thirdIndex - secondIndex == 1)&&(secondIndex - firstIndex==1)){
                        flag = true;
                    }
                }
                return flag;
            }
           var  repectStr =/([0-9a-zA-Z])\1{2}/;
            if(LxStr(password) || repectStr.test(password)){
                layer.msg('密码不能有连续 3 次的字符或一些顺序的字符', {icon: 5});
                return false;
            }
            var blank =/\s/;
            if(blank.test(password)){
                layer.msg('密码不能包含空格', {icon: 5});
                return false;
            }

            if (exception_mes.length > 0 ) {
                var alertMessage = '';
                for (var i=0; i < exception_mes.length; i++) {
                    alertMessage += exception_mes[i] + '<br/>'
                }
                layer.msg(alertMessage, {icon: 5});
                return false;
            }

            $.ajax({
                type: 'post',
                dataType: 'json',
                url: '',
                data: regForm.serialize(),
                success: function(response) {
                    if (response.Status) {
                        layer.confirm(response.Message+'!<br/>是否现在登录',{
                            btn : [ '确定', '取消' ],
                            yes: function(){
                                window.location.href = '/login';
                            },
                            cancel:function () {
                                layer.closeAll();
                            }
                        })
//                        setTimeout(function () {
//                            layer.close(index);
//                        },1000);
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