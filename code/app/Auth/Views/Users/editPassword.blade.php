@extends('layouts/main')

@section('content')
    <div class="sectionBody">
        <div class="location">
    		<span class="layui-breadcrumb">
			  <a href="javascript:;">用户管理</a>
			  <a href="{{ url('user/editPassword') }}"><cite>修改密码</cite></a>
			</span>
        </div>
        <div class="edn-row EDbar">
            <div class="edTit"><h3 class="le">修改密码</h3></div>
            <form action="{{ url('user/editPassword') }}" class="layui-form" id="myForm">
                {{ csrf_field() }}
                @if($id)
                    <input type="hidden" name="user_id" value="{{ $id }}">
                @endif
                <ul class="form-uldef">
                    <li class="layui-form-item">
                        <label class="layui-form-label"><b style="color:red;">*</b>新密码</label>
                        <div class="layui-input-inline">
                            <input type="password" name="password" lay-verify="required" class="layui-input writeinput">
                        </div>
                        <div class="layui-form-mid layui-word-aux" style="margin-left: 10px;">请输入长度不超过16个字符</div>
                    </li>
                    <li class="layui-form-item">
                        <label class="layui-form-label"><b style="color:red;">*</b>确认密码</label>
                        <div class="layui-input-block">
                            <input type="password" name="password_confirmation" lay-verify="required" class="layui-input writeinput">
                        </div>
                    </li>
                    <li class="layui-form-item">
                        <div class="layui-input-block">
                            <button type="submit" class="layui-btn layui-btn-normal" lay-submit="" id="editPassword" lay-filter="editPassword">保存</button>
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
