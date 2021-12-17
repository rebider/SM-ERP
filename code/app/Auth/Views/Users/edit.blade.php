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
            <input type="hidden" name="user_id" value="{{ $user->user_id }}">
            <ul class="">
                <li class="layui-form-item">
                    <div class="layui-form-label">账号名</div>
                    <div class="layui-input-inline">
                        <input type="text" name="user_code" value="{{ $user->user_code }}" class="layui-input layui-disabled" />
                    </div>
                </li>

                <li class="layui-form-item">
                    <div class="layui-form-label"><b style="color:red;">*</b>邮箱</div>
                    <div class="layui-input-inline">
                        <input type="text" name="email" value="{{ $user->email }}" class="layui-input @if($user->email == '') email @endif" maxlength="50" autocomplete="off"
                               lay-filter="required"
                               onkeyup="rmclass(this,'email','email')"
                               onblur="rmclass(this,'email','email')"/>
                    </div>
                </li>

                <li class="layui-form-item">
                    <div class="layui-form-label"><b style="color:red;">*</b>手机号</div>
                    <div class="layui-input-inline">
                        <input type="text" name="mobile" value="{{ $user->mobile }}" class="layui-input @if($user->mobile == '') phone @endif" max="18"
                               lay-filter="required"
                               onkeyup="rmclass(this,'mobile','phone')"
                               onblur="rmclass(this,'mobile','phone')" autocomplete="off"/>
                    </div>
                </li>

                <li class="layui-form-item">
                    <div class="layui-form-label"><b style="color:red;">*</b>联系人</div>
                    <div class="layui-input-inline">
                        <input type="text" name="username" value="{{ $user->username }}" class="layui-input @if($user->username == '') name @endif"                                 maxlength="20"
                               lay-filter="required"
                               onkeyup="rmclass(this,'username','name')"
                               onblur="rmclass(this,'username','name')" autocomplete="off"/>
                    </div>
                </li>

                <li class="layui-form-item">
                    <div class="layui-form-label"><b style="color:red;"></b>公司名称</div>
                    <div class="layui-input-inline">
                        <input type="text" name="company_name" value="{{ $user->company_name }}" class="layui-input @if($user->company_name == '') Bname @endif" lay-verify="company_name" maxlength="50"
                               onkeyup="rmclass(this,'company_name','Bname')"
                               onblur="rmclass(this,'company_name','Bname')"
                               autocomplete="off"
                        />
                    </div>
                </li>

                <li class="layui-form-item">
                    <div class="layui-form-label"><b style="color:red;"></b>地址</div>
                    <div class="layui-input-inline">
                        <input type="text" name="address" class="layui-input  @if($user->address == '') addr @endif" value="{{ $user->address }}" lay-verify="address"  maxlength="150"
                               onkeyup="rmclass(this,'address','addr')"
                               onblur="rmclass(this,'address','addr')"
                               autocomplete="off"
                        />
                    </div>
                </li>

                <li class="layui-form-item">
                    <div class="layui-form-label"><b style="color:red;"></b>电话</div>
                    <div class="layui-input-inline">
                        <input type="text" name="phone" value="{{ $user->phone }}" class="layui-input @if($user->phone == '') phone @endif" autocomplete="off"/>
                    </div>
                </li>

                <li class="layui-form-item">
                    <div class="layui-form-label"><b style="color:red;">*</b>启用状态</div>
                    <div class="layui-input-inline">
                        <select name="state" lay-filter="required">
                            <option value="">请选择</option>
                            <option value="0" @if ($user->state == 0 ) selected @endif >不可用</option>
                            <option value="1" @if ($user->state == 1 ) selected @endif >可用</option>
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