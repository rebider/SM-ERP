@extends('layouts/new_main')
<style type="text/css">
    .layui-layer-btn {
        text-align: center!important;
    }
</style>

@section('content')
{{--    @include('layouts/shortcutMenus')--}}

    <div class="content-wrapper">
        <form class="multiSearch layui-form">
            <div class="flexSearch flexquar fclear">
                <!--启用状态-->
                <div class="frist">
                    <div>
                    <div class="inputTxt">是否启用：</div>
                    <div class="inputBlock">
                        <div class="multLable">
                            <input type="radio" name="state" value="" title="全部" checked>
                            <input type="radio" name="state" value="1" title="是" >
                            <input type="radio" name="state" value="0" title="否" >
                        </div>
                    </div>
                    </div>
                </div>
                <!--规则名称-->
                <div class="second">
                    <div class="inputTxt">账号名称：</div>
                    <div class="inputBlock">
                        <input type="text" name="user_code"  placeholder="请输入账号名" autocomplete="off" class="voin">
                    </div>
                </div>
                <div class="search">
                    <div class="usebtn fl">
                        <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="searBtn">搜索</button>
                        <button class="layui-btn probRulesBtn" lay-submit=""  lay-filter="userNew">添加子账户</button>
                        <button class="layui-btn probRulesBtn userDel" type="button" data-type="userDel">删除</button>
                        <button class="layui-btn layui-btn-primary" lay-submit="" lay-filter="reset" >重置</button>
                    </div>
                </div>
            </div>
        </form>
        <div class="edn-row table_index">
            <table class="" id="EDtable" lay-filter="EDtable" lay-data="{width: 892, height:332,  page:true, id:'EDtable'}"></table>
        </div>
    </div>
    <script type="text/html" id="userAction">
        <a class="operating-btn" href="javascript:;" lay-event="editMenus">菜单权限</a>
        <a class="operating-btn" href="javascript:;" lay-event="editShops">店铺权限</a>
        <a class="operating-btn userEdit" href="javascript:;" lay-event="edit">编辑</a>
    </script>
@endsection

@section('javascripts')
    <script type="text/javascript" src="{{ asset('js/user.js?'.time()) }}"></script>
@endsection