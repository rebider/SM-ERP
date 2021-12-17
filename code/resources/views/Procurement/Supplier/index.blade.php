@extends('layouts/new_main')

@section('content')
    <style>
        .layui-form-select{
            width: 17%;
        }
    </style>
    <div class="kbmodel_full">
{{--        @include('layouts/shortcutMenus')--}}
        <div class="content-wrapper">
            <form class="layui-form multiSearch">
                <ul class="flexSearch flexquar fclear">
                    <li>
                        <div class="inputTxt">供应商状态：</div>
                        <div class="inputBlock">
                            <div class="multLable">
                                <input type="radio" name="status" value="" title="全部" checked>
                                <input type="radio" name="status" value="2" title="启用">
                                <input type="radio" name="status" value="1" title="停用">
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="inputTxt">供应商名称：</div>
                        <div class="inputBlock">
                            <input type="text" name="name" id="name" class="layui-input" style="position:absolute;z-index:1;width:17%;"
                                   lay-verify="" value="" onkeyup="searchName()" autocomplete="off">
                            <select type="text" id="hc_select" lay-filter="hc_select" autocomplete="off" placeholder="name"
                                    lay-verify="" class="layui-select" lay-search>
                                @foreach($suppliers as $re)
                                    <option value="{{ $re['name'] }}">{{ $re['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </li>
                    <li>
                        <div class="inputTxt">联系人：</div>
                        <div class="inputBlock">
                            <div class="inputBlock">
                                <input type="text" name="linkman" id="linkman" class="layui-input" style="position:absolute;z-index:1;width:17%;"
                                       lay-verify="" value="" onkeyup="searchMan()" autocomplete="off">
                                <select type="text" id="hc_select1" lay-filter="hc_select1" autocomplete="off" placeholder="linkman"
                                        lay-verify="" class="layui-select" lay-search>
                                    @foreach($suppliers as $re)
                                        <option value="{{ $re['linkman'] }}">{{ $re['linkman'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="inputTxt">联系方式：</div>
                        <div class="inputBlock">
                            <input type="text" name="tel_no" id="tel_no" class="layui-input" style="position:absolute;z-index:1;width:17%;"
                                   lay-verify="" value="" onkeyup="searchTel()" autocomplete="off">
                            <select type="text" id="hc_select2" lay-filter="hc_select2" autocomplete="off" placeholder="tel_no"
                                    lay-verify="" class="layui-select" lay-search>
                                @foreach($suppliers as $re)
                                    <option value="{{ $re['tel_no'] }}">{{ $re['tel_no'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </li>
                    <li style="margin-right: 10000px;">
                        <div class="groupBtns">
                            <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="searBtn">搜索</button>
                            <button class="layui-btn layui-btn-primary" lay-submit="" lay-filter="reset">重置</button>
                        </div>
                    </li>
                </ul>
            </form>

            <div class="toolsBtn fclear">
                <div class="infm">
                </div>
                <div class="operate fr">
                    <button type="button" class="layui-btn layuiadmin-btn-order" id="addSupplier">添加供应商</button>
                </div>
            </div>
            <div class="edn-row table_index">
                <table class="" id="EDtable" lay-filter="EDtable"></table>
            </div>
        </div>

        <script type="text/html" id="barDemo">
            {{--<a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>--}}
            <a class="layui-table-link" lay-event="edit">编辑</a>
            @{{#  if(d.status === 2) { }}
            {{--<a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="changeStatus">停用</a>--}}
            <a class="layui-table-link" lay-event="changeStatus">停用</a>
            @{{# } else { }}
            {{--<a class="layui-btn layui-btn-xs" lay-event="changeStatus">启用</a>--}}
            <a class="layui-table-link" lay-event="changeStatus">启用</a>
            @{{#  } }}
        </script>
    </div>
        @endsection

        @section('javascripts')
            <script>
                //layui加载
                layui.config({base: '../../layui/lay/modules/'}).extend({formSelects: 'formSelects-v3'});
                layui.use(['layer', 'form', 'element', 'laydate', 'table', 'formSelects'], function () {
                    var layer = layui.layer, form = layui.form, laydate = layui.laydate, table = layui.table,
                        element = layui.element, formSelects = layui.formSelects;
                    var index = layer.msg('数据请求中', {icon: 16});

                    laydate.render({
                        elem: '#EDdate'
                    });
                    laydate.render({
                        elem: '#EDdate1'
                    });

                    form.on('submit(reset)', function (data) {
                        window.location.reload(true);
                        return false;
                    });

                    form.on('submit(searBtn)', function (data) {
                        var index = layer.msg('数据请求中', {icon: 16});
                        var info = data.field;
                        table.render({
                            elem: '#EDtable'
                            , url: '/supplier/supplierIndexSearch'
                            , where: {data: info}
                            , cols: [[
//                                {checkbox: true},
                                {field: '', title: '序号', width: 50, type: 'numbers'}

                                , {
                                    field: 'name',
                                    title: '供应商名称',
                                    templet: function (d) {
                                        return d.name;
                                    }
                                }, {
                                    field: 'tel_no', title: '联系人', templet: function (d) {
                                        return d.linkman;
                                    }
                                }
                                , {
                                    field: 'tel_no', title: '联系方式', templet: function (d) {
                                        return d.tel_no;
                                    }
                                }, {
                                    field: 'email', title: '邮箱', templet: function (d) {
                                        if (d.email){
                                            return d.email;
                                        }
                                        return '';
                                    }
                                }, {
                                    field: 'address', title: '详细地址', templet: function (d) {
                                        if (d.address){
                                            return d.address;
                                        }
                                        return '';
                                    }
                                }, {
                                    field: 'status', title: '供应商状态', templet: function (d) {
                                        switch (d.status) {
                                            case 1:
                                                return '已停用';
                                            case 2:
                                                return '启用中';
                                        }
                                    }
                                },
                                {field: '', title: '操作', toolbar: '#barDemo'}
                            ]]
                            , limit: 20
                            , page: true
                            , limits: [20, 30, 40, 50]
                            , done: function () {   //返回数据执行回调函数
                                layer.close(index);    //返回数据关闭loading
                            }
                        });
                        return false;

                    });

                    table.render({
                        elem: '#EDtable'
                        , url: '/supplier/supplierIndexSearch'
                        , cols: [[
//                            {checkbox: true},
                            {field: '', title: '序号', width: 50, type: 'numbers'}

                            , {
                                field: 'name',
                                title: '供应商名称',
                                templet: function (d) {
                                    return d.name;
                                }
                            }, {
                                field: 'tel_no', title: '联系人', templet: function (d) {
                                    return d.linkman;
                                }
                            }
                            , {
                                field: 'tel_no', title: '联系方式', templet: function (d) {
                                    return d.tel_no;
                                }
                            }, {
                                field: 'email', title: '邮箱', templet: function (d) {
                                    if (d.email){
                                        return d.email;
                                    }
                                    return '';
                                }
                            }, {
                                field: 'address', title: '详细地址', templet: function (d) {
                                    if (d.address){
                                        return d.address;
                                    }
                                    return '';
                                }
                            }, {
                                field: 'status', title: '供应商状态', templet: function (d) {
                                    switch (d.status) {
                                        case 1:
                                            return '已停用';
                                        case 2:
                                            return '启用中';
                                    }
                                }
                            },
                            {field: '', title: '操作', toolbar: '#barDemo'}
                        ]]
                        , limit: 20
                        , page: true
                        , limits: [20, 30, 40, 50]
                        , done: function () {   //返回数据执行回调函数
                            layer.close(index);    //返回数据关闭loading
                        }
                    });

                    layui.use('table', function () {
                        var table = layui.table;
                        //监听单元格事件
                        table.on('tool(EDtable)', function (obj) {
                            var data = obj.data;
                            if (obj.event === 'edit') {
                                layer.open({
                                    type: 2,
                                    title: '编辑供应商',
                                    fix: false,
                                    maxmin: false,
                                    resize: true,
                                    shadeClose: true,
                                    area: ['800px', '350px'],
                                    content: ['{{ url('supplier/editSupplier') }}' + '/' + data.id,'no'],
                                    end: function (index) {

                                    }
                                });
                            } else if (obj.event === 'changeStatus') {
                                var re = '';
                                if (data.status === 1){
                                    re = '是否确认启用 供应商名称：'+data.name
                                } else {
                                    re = '是否确认停用 供应商名称：'+data.name
                                }
                                layer.confirm(re, function (index) {
                                    $.ajax({
                                        type: 'POST',
                                        data: {
                                            id: data.id,
                                            _token: "{{ csrf_token() }}"
                                        },
                                        url: '{{url('supplier/changeStatus')}}',
                                        success: function (data) {
                                            if (data.status === 0) {
                                                layer.msg(data.msg, {icon: 5})
                                            } else {
                                                layer.closeAll();
                                                layer.msg(data.msg, {icon: 6});
                                                setTimeout(function () {
                                                    table.reload('EDtable'); //重载表格
                                                }, 2000);
                                            }
                                        },
                                        error: function () {

                                        }
                                    })
                                })
                            }
                        });
                    });

                    $('#addSupplier').click(function () {
                        layer.open({
                            type: 2,
                            title: '添加供应商',
                            fix: false,
                            maxmin: false,
                            resize: true,
                            shadeClose: true,
                            area: ['800px', '350px'],
                            content: ['{{ url('supplier/addSupplier') }}','no'],
                            end: function end() {

                            }
                        });
                    });

                    //下拉框联想-1   供应商名称
                    form.on('select(hc_select)', function (data) {   //选择sku 赋值给input框
                        $("#name").val(data.value);
                        $("#hc_select").next().find("dl").css({"display": "none"});
                        form.render();
                    });
                    //下拉框联想-2
                    window.searchName = function () {
                        var value = $("#name").val();
                        $("#hc_select").val(value);
                        form.render();
                        $("#hc_select").next().find("dl").css({"display": "block"});
                        var dl = $("#hc_select").next().find("dl").children();
                        var j = -1;
                        for (var i = 0; i < dl.length; i++) {
                            if (dl[i].innerHTML.indexOf(value) <= -1) {
                                dl[i].style.display = "none";
                                j++;
                            }
                            if (j == dl.length - 1) {
                                $("#hc_select").next().find("dl").css({"display": "none"});
                            }
                        }
                        $(document).click(function () { //点击后隐藏下拉框元素
                            $("#hc_select").next().find("dl").css({"display": "none"});
                        })

                    };

                    //下拉框联想-1   联系人
                    form.on('select(hc_select1)', function (data) {   //选择sku 赋值给input框
                        $("#linkman").val(data.value);
                        $("#hc_select1").next().find("dl").css({"display": "none"});
                        form.render();
                    });
                    //下拉框联想-2
                    window.searchMan = function () {
                        var value = $("#linkman").val();
                        $("#hc_select1").val(value);
                        form.render();
                        $("#hc_select1").next().find("dl").css({"display": "block"});
                        var dl = $("#hc_select1").next().find("dl").children();
                        var j = -1;
                        for (var i = 0; i < dl.length; i++) {
                            if (dl[i].innerHTML.indexOf(value) <= -1) {
                                dl[i].style.display = "none";
                                j++;
                            }
                            if (j == dl.length - 1) {
                                $("#hc_select1").next().find("dl").css({"display": "none"});
                            }
                        }
                        $(document).click(function () { //点击后隐藏下拉框元素
                            $("#hc_select1").next().find("dl").css({"display": "none"});
                        })

                    };

                    //下拉框联想-1   联系方式
                    form.on('select(hc_select2)', function (data) {   //选择sku 赋值给input框
                        $("#tel_no").val(data.value);
                        $("#hc_select2").next().find("dl").css({"display": "none"});
                        form.render();
                    });
                    //下拉框联想-2
                    window.searchTel = function () {
                        var value = $("#tel_no").val();
                        $("#hc_select2").val(value);
                        form.render();
                        $("#hc_select2").next().find("dl").css({"display": "block"});
                        var dl = $("#hc_select2").next().find("dl").children();
                        var j = -1;
                        for (var i = 0; i < dl.length; i++) {
                            if (dl[i].innerHTML.indexOf(value) <= -1) {
                                dl[i].style.display = "none";
                                j++;
                            }
                            if (j == dl.length - 1) {
                                $("#hc_select2").next().find("dl").css({"display": "none"});
                            }
                        }
                        $(document).click(function () { //点击后隐藏下拉框元素
                            $("#hc_select2").next().find("dl").css({"display": "none"});
                        })

                    };

                });

                $("body").bind("keydown", function (event) {
                    if (event.keyCode == 116) {
                        event.preventDefault(); //阻止默认刷新
                        location = location;
                    }
                })

            </script>
@endsection