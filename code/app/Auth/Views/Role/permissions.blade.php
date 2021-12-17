@extends('layouts/main')

@section('content')
    <div class="sectionBody">
        <div class="location">
    		<span class="layui-breadcrumb">
			  <a href="javascript:;">用户管理</a>
			  <a href="javascript:;">角色管理</a>
			  <a href="javascript:;"><cite>分配权限</cite></a>
			</span>
        </div>
        <div class="edn-row">
            <ul class="RoleAuthor">
                <li class="layui-inline">
                    <label class="layui-form-label">角色：</label>
                    <div class="layui-input-inline"><p>{{ $role->role_name }}</p></div>
                </li>
                <li class="layui-inline">
                    <label class="layui-form-label">状态：</label>
                    <div class="layui-input-inline"><p>{{ $role->state == 1 ? '启用' : '禁用' }}</p></div>
                </li>
                <li class="layui-inline">
                    <label class="layui-form-label">备注：</label>
                    <div class="layui-input-inline"><p>{{ $role->remark }}</p></div>
                </li>
                <li class="layui-inline" style="float: right;">
                    <button class="layui-btn layui-btn-normal stroePermissions">保存</button>
                </li>
            </ul>
        </div>
        <div class="edn-row layui-form">
            <form action="#" class="layui-form" method="post" id="myForm">
            {{ csrf_field() }}
            <input type="hidden" name="role_id" value="{{ $role->role_id }}">
            <table class="layui-table">
                <thead>
                <tr>
                    <th width="40px"></th>
                    <th>菜单</th>
                    <th>功能</th>
                </tr>
                </thead>
                @foreach($permissions as $p1)
                    <tbody class="Authlimits">
                    @foreach($p1 as $key1 => $item1)
                        @if($key1 == 0)
                            <tr>
                                <td><span class="Separator"></span></td>
                                <td>
                                    <input type="checkbox" name="permissions[]" class="eauth ecom" lay-filter="owner_all" lay-skin="primary" title="{{ $item1['name'] }}" value="{{ $item1['id'] }}" {{ in_array($item1['id'], $hasPermissions) ? 'checked':'' }}>
                                </td>
                                <td></td>
                            </tr>
                        @else
                            <tr class="menu_child">
                                <td></td>
                                <td>
                                    <div class="auth-child">
                                        <input type="checkbox" name="permissions[]" class="eType Echild" lay-filter="owner_three" lay-skin="primary" title="{{ $item1['name'] }}" value="{{ $item1['id'] }}" {{ in_array($item1['id'], $hasPermissions) ? 'checked':'' }}>
                                    </div>
                                </td>
                                <td></td>
                            </tr>
                        @endif
                        @if(isset($item1['child']))
                            @foreach($item1['child'] as $p2)
                                <tr class="three_tab">
                                    <td></td>
                                    <td>
                                        <div class="auth-grand">
                                            <input type="checkbox" name="permissions[]" class="eType Egrand" lay-filter="owner_four" lay-skin="primary" title="{{ $p2['name'] }}" value="{{ $p2['id'] }}" {{ in_array($p2['id'], $hasPermissions) ? 'checked':'' }}>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="tree_Features">
                                            @if(isset($p2['child']))
                                                @foreach($p2['child'] as $p3)
                                                    <input type="checkbox" name="permissions[]" class="eType" lay-filter="owner_end" lay-skin="primary" title="{{ $p3['name'] }}" value="{{ $p3['id'] }}" {{ in_array($p3['id'], $hasPermissions) ? 'checked':'' }}>
                                                @endforeach
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                    </tbody>
                @endforeach
            </table>
            <ul style="margin-top: 10px;margin-bottom:10px;text-align:center;">
                <li class="layui-inline">
                    <button type="button" class="layui-btn layui-btn-normal stroePermissions">保存</button>
                </li>
            </ul>
            </form>
        </div>
    </div>
@endsection

@section('javascripts')
    <script type="text/javascript" src="{{ asset('js/permissions.js?'.time()) }}"></script>
@endsection