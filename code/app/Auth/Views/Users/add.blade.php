@extends('layouts/new_dialog')
@section('css')
    <link href="{{asset('auth/css/auth.common.css')}}" rel="stylesheet">
    <style>
        .layui-form-label {
            float: left;
            display: block;
            padding: 9px 15px;
            width: 100px;
            font-weight: 400;
            line-height: 20px;
            text-align: right;
        }
    </style>
@endsection
@section('content')
    <div class="openadvisory" style="padding-top: 20px;">
        <form action="" method="post" class="layui-form myForm" id="myForm">
            {{ csrf_field() }}
            <ul class="">
                <li class="layui-form-item">
                    <div class="layui-form-label"><b style="color:red;">*</b>账号名</div>
                    <div class="layui-input-inline">
                        <input type="text" name="user_code" class="layui-input name"  lay-verify="user_code" minlength="5" maxlength="50"
                               onkeyup="rmclass(this,'user_code','name')"
                               onblur="rmclass(this,'user_code','name')"
                        />
                    </div>
                    <div class="layui-form-mid layui-word-aux">请输入字母、下划线和数字组成，用于登录账号</div>
                </li>

                <li class="layui-form-item">
                    <div class="layui-form-label"><b style="color:red;">*</b>邮箱</div>
                    <div class="layui-input-inline">
                        <input type="email" name="email" class="layui-input email" lay-verify="emailEmpty|email" maxlength="50"
                               onkeyup="rmclass(this,'email','email')"
                               onblur="rmclass(this,'email','email')"/>
                    </div>
                </li>
                <li class="layui-form-item">
                    <div class="layui-form-label"><b style="color:red;">*</b>手机号</div>
                    <div class="layui-input-inline">
                        <input type="tel" name="mobile" class="layui-input phone"  maxlength="18"  lay-verify="mobileEmpty|phone"
                               onkeyup="rmclass(this,'mobile','phone')"
                               onblur="rmclass(this,'mobile','phone')"/>
                    </div>
                </li>

                <li class="layui-form-item">
                    <div class="layui-form-label"><b style="color:red;">*</b>密码</div>
                    <div class="layui-input-inline">
                        <input type="password" name="password" class="layui-input pwtro" lay-verify="password" minlength="8"  maxlength="50"
                               onkeyup="rmclass(this,'password','pwtro')"
                               onblur="rmclass(this,'password','pwtro')"
                        />
                    </div>
                    <div class="layui-form-mid layui-word-aux">请输入长度不超过50个字符</div>
                </li>
                <li class="layui-form-item">
                    <div class="layui-form-label"><b style="color:red;">*</b>确认密码</div>
                    <div class="layui-input-inline">
                        <input type="password" name="password_confirmation" class="layui-input pwtro" lay-verify="password"  minlength="8"  maxlength="50"
                               onkeyup="rmclass(this,'password_confirmation','pwtro')"
                               onblur="rmclass(this,'password_confirmation','pwtro')"
                        />
                    </div>
                </li>

                <li class="layui-form-item">
                    <div class="layui-form-label"><b style="color:red;">*</b>联系人</div>
                    <div class="layui-input-inline">
                        <input type="text" name="username" class="layui-input name" lay-verify="username" minlength="2" maxlength="50"
                               onkeyup="rmclass(this,'username','name')"
                               onblur="rmclass(this,'username','name')"
                        />
                    </div>
                    <div class="layui-form-mid layui-word-aux">请输入字母、数字或文字</div>
                </li>

                <li class="layui-form-item">
                    <div class="layui-form-label"><b style="color:red;"></b>公司名称</div>
                    <div class="layui-input-inline">
                        <input type="text" name="company_name" class="layui-input Bname" lay-verify="company_name" maxlength="50"
                               onkeyup="rmclass(this,'company_name','Bname')"
                               onblur="rmclass(this,'company_name','Bname')"
                        />
                    </div>
                    <div class="layui-form-mid layui-word-aux">请输入字母、数字或文字</div>
                </li>

                <li class="layui-form-item">
                    <div class="layui-form-label"><b style="color:red;"></b>地址</div>
                    <div class="layui-input-inline">
                        <input type="text" name="address" class="layui-input addr" lay-verify="address"  maxlength="150"
                               onkeyup="rmclass(this,'address','addr')"
                               onblur="rmclass(this,'address','addr')"
                        />
                    </div>
                </li>
                <li class="layui-form-item">
                    <div class="layui-form-label"><b style="color:red;"></b>电话</div>
                    <div class="layui-input-inline">
                        <input type="text"  name='phone' class="layui-input phone"  maxlength="50"
                               onkeyup="rmclass(this,'phone','phone')"
                               onblur="rmclass(this,'phone','phone')"
                        />
                    </div>
                    <div class="layui-form-mid layui-word-aux">请输入区号-电话号-分机号</div>
                </li>

                <li class="layui-form-item">
                    <div class="layui-form-label"><b style="color:red;">*</b>启用状态</div>
                    <div class="layui-input-inline">
                        <select name="state" >
                                <option value="">请选择</option>
                                <option value="0">不可用</option>
                                <option value="1" selected>可用</option>
                        </select>
                    </div>
                </li>
            </ul>
        </form>
    </div>
@endsection

@section('javascripts')
    <script type="text/javascript" src="{{ asset('js/user.js?'.time()) }}"></script>
@endsection
