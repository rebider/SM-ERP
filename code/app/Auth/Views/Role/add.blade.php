@extends('layouts/dialog')

@section('content')
    <div class="openadvisory">
        <form action="{{ url('role/store') }}" method="post" class="layui-form" id="myForm">
            {{ csrf_field() }}
            <input type="hidden" name="id" value="0">
            <ul class="">
                <li class="layui-form-item">
                    <div class="layui-form-label"><b style="color:red;">*</b>角色类型</div>
                    <div class="layui-input-inline">
                        <select name="role_id" lay-verify="required">
                            @foreach($roleAll as $id => $item)
                                <option value="{{ $id }}"> {{ $item }}</option>
                            @endforeach
                        </select>
                    </div>
                </li>
                <li class="layui-form-item">
                    <div class="layui-form-label">备注</div>
                    <div class="layui-input-block">
                        <textarea class="layui-textarea" name="remark"></textarea>
                    </div>
                </li>
                <li class="layui-form-item">
                    <div class="layui-form-label"><b style="color:red;">*</b>状态</div>
                    <div class="layui-input-inline">
                        <select name="state">
                            @foreach($stateAll as $id => $state)
                                <option value="{{ $id }}" {{ $id == 1 ? 'selected' : ''}}>{{ $state }}</option>
                            @endforeach
                        </select>
                    </div>
                </li>
            </ul>
        </form>
    </div>
@endsection

@section('javascripts')
    <script type="text/javascript" src="{{ asset('js/role.js?'.time()) }}"></script>
@endsection
