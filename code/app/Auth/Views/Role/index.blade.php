@extends('layouts/main')

@section('content')
    <div class="sectionBody">
        <div class="location">
    		<span class="layui-breadcrumb">
			  <a href="javascript:;">用户管理</a>
			  <a href="{{ url('role/index') }}"><cite>角色管理</cite></a>
			</span>
        </div>
        <div class="edn-row">
            <form class="layui-form">
                <ul class="condSearch">
                    <li class="layui-inline">
                        <div class="layui-input-inline">
                            <input type="text" name="role_name" placeholder="请输入角色" autocomplete="off" class="layui-input writeinput role_name">
                        </div>
                    </li>
                    <li class="layui-inline">
                        <button class="layui-btn layui-btn-normal" lay-submit="" lay-filter="searBtn">查询</button>
                        <a href="javascript:;" class="layui-btn layui-btn-normal roleNew">添加</a>
                    </li>
                </ul>
            </form>
        </div>
        <div class="edn-row table_index">
            <table class="layui-hide" id="EDtable" lay-filter="EDtable"></table>
        </div>
    </div>
    <script type="text/html" id="barDemo">
        <a class="roleEdit operating-btn" href="javascript:;" lay-event="edit">编辑</a>
        <a class="operating-btn" href="javascript:;" lay-event="rolePermissions">分配权限</a>
    </script>
@endsection

@section('javascripts')
    <script type="text/javascript" src="{{ asset('js/role.js?'.time()) }}"></script>
@endsection