@extends('layouts/new_main')

@section('content')
{{--    @include('layouts/shortcutMenus')--}}

    <div class="content-wrapper">
        <div class="edn-row EDbar">
            <div class="edTit"><h3 class="le">个人信息</h3></div>
            <ul class="form-uldef">
                <li class="layui-form-item">
                    <label class="layui-form-label">账号名：</label>
                    <div class="layui-input-inline">
                        <span>{{$userInfo['user_code']}}</span>
                    </div>
                    <label class="layui-form-label">邮箱：</label>
                    <div class="layui-input-inline">
                        <span>{{$userInfo['email']}}</span>
                    </div>
                    <label class="layui-form-label">手机号：</label>
                    <div class="layui-input-inline">
                        <span>{{$userInfo['mobile']}}</span>
                    </div>
                </li>

                <li class="layui-form-item">
                    <label class="layui-form-label">联系人：</label>
                    <div class="layui-input-inline">
                        <span>{{$userInfo['username']}}</span>
                    </div>
                    <label class="layui-form-label">公司名称：</label>
                    <div class="layui-input-inline">
                        <span>{{$userInfo['company_name']}}</span>
                    </div>
                    <label class="layui-form-label">电话：</label>
                    <div class="layui-input-inline">
                        <span>{{$userInfo['phone']}}</span>
                    </div>
                </li>

                <li class="layui-form-item">
                    <label class="layui-form-label">地址：</label>
                    <div class="layui-input-inline">
                        <span>{{$userInfo['address']}}</span>
                    </div>
                </li>
            </ul>
        </div>
        <div class="edn-row EDbar">
            <div class="edTit"><h3 class="le">修改密码</h3></div>
            <form action="" class="layui-form" id="myForm">
                {{ csrf_field() }}
                @if($userInfo)
                <input type="hidden" name="user_id" value="{{ $userInfo['user_id'] }}">
                @endif
                <ul class="form-uldef">
                    <li class="layui-form-item">
                        <label class="layui-form-label"><b style="color:red;">*</b>新密码</label>
                        <div class="layui-input-inline">
                            <input type="password" name="password" lay-verify="required" class="layui-input writeinput" autocomplete="off" maxlength="16">
                        </div>
                        <div class="layui-form-mid layui-word-aux" style="margin-left: 10px;">请输入长度不超过16个字符</div>
                    </li>
                    <li class="layui-form-item">
                        <label class="layui-form-label"><b style="color:red;">*</b>确认密码</label>
                        <div class="layui-input-block">
                            <input type="password" name="password_confirmation" autocomplete="off" lay-verify="required" class="layui-input writeinput">
                        </div>
                    </li>
                    <li class="layui-form-item">
                        <div class="layui-input-block">
                            <button  class="layui-btn layui-btn-normal" lay-submit="" id="editPassword" lay-filter="editPassword">保存</button>
                        </div>
                    </li>
                </ul>
            </form>
        </div>
    </div>

@endsection

@section('javascripts')
    <script type="text/javascript" src="{{ asset('js/user.js?'.time()) }}"></script>
@endsection
