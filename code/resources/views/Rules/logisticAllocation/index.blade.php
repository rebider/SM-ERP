@extends('layouts/new_main')
@section('content')
{{--    @include('layouts/shortcutMenus')--}}
    <div class="content-wrapper">
        <form class="multiSearch layui-form">
            <div class="flexSearch flexquar fclear">
                <!--启用状态-->
                <div class="frist">
                    <div class="inputTxt">是否启用：</div>
                    <div class="inputBlock">
                        <div class="multLable">
                            <input type="radio" name="opening_status" value="0" title="全部" checked>
                            <input type="radio" name="opening_status" value="1" title="是">
                            <input type="radio" name="opening_status" value="2" title="否">
                        </div>
                    </div>
                </div>
                <div class="second">
                    <!--规则名称-->
                    <div>
                        <div class="inputTxt">规则名称：</div>
                        <div class="inputBlock">
                            <input type="text" name="trouble_rules_name" placeholder="请输入规则名" autocomplete="off"
                                   class="voin">
                        </div>
                    </div>
                    <!--创建时间-->
                    <div>
                        <div class="inputTxt">创建时间：</div>
                        <div class="inputBlock">
                            <div class="time_StartEnd">
                                <input type="text" name="start_date" id="EDdate" style="width: 150px;"
                                       placeholder="起始时间" autocomplete="off" class="layui-input writeinput time-item "
                                       readonly="">
                                <i class="dash">-</i>
                                <input type="text" name="end_date" id="EDdate1" style="width: 150px;" placeholder="截止时间"
                                       autocomplete="off" class="layui-input writeinput time-item" readonly="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="search">
                    <div class="usebtn fl">
                        <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="searBtn" type="submit">搜索
                        </button>
                        <button class="layui-btn probRulesBtn" type="button">添加规则</button>
                        <button class="layui-btn layui-btn-primary" lay-submit="" lay-filter="reset">重置</button>
                    </div>
                </div>
            </div>
        </form>
        <div class="edn-row table_index">
            <table class="" id="EDtable" lay-filter="EDtable"></table>
        </div>
    </div>
@endsection
@section('javascripts')
    <script type="text/javascript" src="{{ asset('js/logistic_index.js?'.time()) }}"></script>
@endsection