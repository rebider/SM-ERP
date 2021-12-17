@extends('layouts.new_main')

@section('content')
{{--    @include('layouts/shortcutMenus')--}}
    <style>
        .inputTxt {
            width: 100px;
            text-align: right;
        }
        .inputBlock{margin-left: unset !important;}
        td[data-field=time]>div{height: 50px;}
        .layui-layer-btn {
            text-align: center!important;
        }
    </style>

    <div class="content-wrapper">
        <form class="multiSearch layui-form">
            <div class="flexSearch flexquar fclear">
                <!--来源平台-->
                <div class="frist">
                    <div class="inputTxt">来源：</div>
                    <div class="inputBlock">
                        <div class="multLable">
                            <input type="radio" name="type" value="0" title="全部" checked>
                            <input type="radio" name="type" value="1" title="速贸仓储">
                            <input type="radio" name="type" value="2" title="自定义">
                        </div>
                    </div>
                </div>
                <!--启用仓库-->
                <div class="second">
                    <div class="inputTxt">是否启用：</div>
                    <div class="inputBlock">
                        <div class="multLable">
                            <input type="radio" name="disable" value="0" title="全部" checked>
                            <input type="radio" name="disable" value="1" title="是">
                            <input type="radio" name="disable" value="2" title="否">
                        </div>
                    </div>
                </div>

                <div class="third second">
                    <!--订单号-->
                    <div class="inputTxt">仓库名称：</div>
                    <div class="inputBlock">
                        <input type="text" name="name" placeholder="请输入仓库名称" autocomplete="off" class="voin">
                    </div>
                </div>
                <div class="search">
                    <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="searBtn">搜索</button>
                    <button class="layui-btn layuiadmin-btn-rules" data-type="addsm">添加速贸仓库</button>
                    <button class="layui-btn layuiadmin-btn-rules" data-type="addcu">添加自定义仓库</button>
                    <button class="layui-btn layui-btn-primary" onclick="window.location.reload();" lay-submit="" lay-filter="reset">重置</button>
                </div>
            </div>
        </form>
        <div class="edn-row table_index">
            <table class="" id="EDtable" lay-filter="EDtable"></table>
            <script type="text/html" id="table-warehouse-edit">
                <a class="layui-table-link" href="javascript:void(0)" lay-event="edit">编辑</a>
{{--                <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit"><i--}}
{{--                            class="layui-icon layui-icon-edit"></i>编辑</a>--}}
{{--                <a class="layui-btn layui-btn-warm layui-btn-xs" lay-event=""><i class="layui-icon layui-icon-reload"></i>重新授权</a>--}}
{{--                <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event=""><i class="layui-icon layui-icon-close"></i>取消授权</a>--}}
            </script>

            <script type="text/html" id="table-warehouse-time">
               <span style="display: list-item;font-size: 12px;">创建时间： @{{ d.created_at }}</span>
               <span style="display: list-item;font-size: 12px;">更新时间： @{{ d.updated_at }}</span>
            </script>
        </div>
    </div>

@endsection

@section('javascripts')

    <script>
        //layui加载
        layui.config({base: '../../layui/lay/modules/'}).extend({formSelects: 'formSelects-v3'});
        layui.use(['layer', 'form', 'element', 'laydate', 'table', 'formSelects'], function () {
            var layer = layui.layer, form = layui.form, laydate = layui.laydate, table = layui.table;
            var index = layer.msg('数据请求中', {icon: 16});

            laydate.render({
                elem: '#EDdate'
            });
            laydate.render({
                elem: '#EDdate1'
            });

            form.on('submit(reset)', function(data){
                window.location.reload(true);
                return false;
            });

            var sumao = 1, customize = 2;
            var active = {
                addsm: function () {
                    layer.open({
                        type: 2
                        , title: '仓库授权'
                        , content: '{{ route('base_info.warehouse.create.index') }}' + '?type=' + sumao
                        , area: ['500px', '400px']
                        , btn: ['保存', '取消']
                        , maxmin: true
                        , yes: function (index, layero) {
                            //点击确认触发 iframe 内容中的按钮提交
                            var submit = layero.find('iframe').contents().find("#LAY-front-submit");
                            submit.click();
                        }
                    });
                },
                addcu: function () {
                    layer.open({
                        type: 2
                        , title: '添加自定义仓库'
                        , content: '{{ route('base_info.warehouse.create.index') }}' + '?type=' + customize
                        , area: ['500px', '550px']
                        , btn: ['保存', '取消']
                        , maxmin: true
                        , yes: function (index, layero) {
                            //点击确认触发 iframe 内容中的按钮提交
                            var submit = layero.find('iframe').contents().find("#LAY-front-submit");
                            submit.click();
                        }
                    });
                }
            };

            $('.layuiadmin-btn-rules').on('click', function (e) {
                e.preventDefault();
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });

            table.on('tool(EDtable)', function (obj) {
                var _data = obj.data;
                switch (obj.event) {
                    case'read':
                        layer.open({
                            type: 2
                            , title: '查看仓库分派规则'
                            , content: '{{route('base_info.warehouse.createOrUpdate')}}'
                            , area: ['800px', '600px']
                            , btn: ['保存', '取消']
                            , maxmin: true
                            , yes: function (index, layero) {
                                //点击确认触发 iframe 内容中的按钮提交
                                var submit = layero.find('iframe').contents().find("#layuiadmin-article-form-submit");
                                submit.click();
                            }

                        });
                        break;
                    case'edit':
                        let title = _data.type == 1 ? '速贸' : '自定义';
                        layer.open({
                            type: 2
                            , title: '编辑'+ title +'仓库'
                            , content: '{{ route('base_info.warehouse.create.index') }}'+'?type='+ _data.type +'&id='+ _data.id + '&edit=1'
                            , area: ['500px', _data.type == 1 ? '300px' : '550px']
                            , btn: ['保存', '取消']
                            , maxmin: true
                            , yes: function (index, layero) {
                                //点击确认触发 iframe 内容中的按钮提交
                                var submit = layero.find('iframe').contents().find("#LAY-front-submit");
                                submit.click();
                            }
                        });
                        break;
                    case'del':
                        layer.confirm("保存删除？", function (e) {
                            $.ajax({
                                type: 'delete',
                                url: '{{ route('base_info.warehouse.del') }}',
                                data: {id: _data.id},
                                dataType: 'json',
                                success: function (data) {
                                    if (data.code) {
                                        layer.msg(data.msg);
                                        table.reload('EDtable');
                                    } else {
                                        layer.msg(data.msg);
                                    }
                                }
                            });
                        })
                }

            });

            form.on('submit(searBtn)', function (data) {

                var index = layer.msg('数据请求中', {icon: 16});
                var info = data.field;
                table.render({
                    elem: '#EDtable'
                    , url: '{{ route('base_info.warehouse.lists') }}'
                    , where: {data: info}
                    , cols: [[
                        {field: '', title: '序号',width:90, type:'numbers'},
                        {
                            field: 'facilitator',
                            title: '服务商名称',
                            event: 'getOrderDetails',
                            style: 'cursor: pointer;',
                            templet: function (d) {
                                return d.facilitator;
                            }
                        }
                        , {
                            field: 'warehouse_name', title: '仓库名称', templet: function (d) {
                                return d.warehouse_name;
                            }
                        }
                        , {
                            field: 'type', title: '来源', templet: function (d) {
                                var str = '';
                                switch (d.type) {
                                    case 1:
                                        str = "速贸仓储";
                                        break;
                                    case 2:
                                        str = "自定义";
                                        break;
                                }
                                return str;
                            }
                        }
                        , {
                            field: 'disable', title: '状态', templet: function (d) {
                                var str = '';
                                switch (d.disable) {
                                    case 1:
                                        str = "是";
                                        break;
                                    case 2:
                                        str = "否";
                                        break;
                                }
                                return str;
                            }
                        }
                        , {
                            field: 'time', title: '时间', width:200,toolbar: "#table-warehouse-time",
                        }
                        , {field: 'logistics_number', title: '操作', toolbar: "#table-warehouse-edit"}
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
                , url: '{{ route('base_info.warehouse.lists') }}'
                , cols: [[
                    {field: '', title: '序号',width:90, type:'numbers'},
                    {
                        field: 'facilitator',
                        title: '服务商名称',
                        event: 'getOrderDetails',
                        style: 'cursor: pointer;',
                        templet: function (d) {
                            return d.facilitator;
                        }
                    }
                    , {
                        field: 'warehouse_name', title: '仓库名称', templet: function (d) {
                            return d.warehouse_name;
                        }
                    }
                    , {
                        field: 'type', title: '来源', templet: function (d) {
                            var str = '';
                            switch (d.type) {
                                case 1:
                                    str = "速贸仓储";
                                    break;
                                case 2:
                                    str = "自定义";
                                    break;
                            }
                            return str;
                        }
                    }
                    , {
                        field: 'disable', title: '状态', templet: function (d) {
                            var str = '';
                            switch (d.disable) {
                                case 1:
                                    str = "是";
                                    break;
                                case 2:
                                    str = "否";
                                    break;
                            }
                            return str;

                        }
                    }
                    , {
                        field: 'time', title: '时间',  width:200,toolbar: "#table-warehouse-time",
                    }
                    , {field: 'logistics_number', title: '操作', toolbar: "#table-warehouse-edit"}
                ]]
                , limit: 20
                , page: true
                , limits: [20, 30, 40, 50]
                , done: function () {   //返回数据执行回调函数
                    layer.close(index);    //返回数据关闭loading
                }
            });

        });


        $("body").bind("keydown", function (event) {
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location = location;
            }
        })

    </script>
@endsection