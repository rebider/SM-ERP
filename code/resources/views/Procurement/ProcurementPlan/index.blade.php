@extends('layouts/new_main')

@section('content')
    <div class="kbmodel_full">
{{--    @include('layouts/shortcutMenus')--}}
        <div class="content-wrapper">
            <form class="layui-form multiSearch">
                <ul class="flexSearch flexquar fclear">
                    <li>
                        <div class="inputTxt">采购计划状态：</div>
                        <div class="inputBlock">
                            <div class="multLable">
                                {{--<em class="curr">全部</em><em>草稿</em><em>审核</em><em>转采购</em>--}}
                                <input type="radio" name="status" value="" title="全部" checked>
                                <input type="radio" name="status" value="1" title="草稿">
                                <input type="radio" name="status" value="2" title="审核">
                                <input type="radio" name="status" value="3" title="转采购">
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="inputTxt">采购计划编号：</div>
                        <div class="inputBlock">
                            <input type="text" name="procurement_no" placeholder=" 请输入采购计划编号" autocomplete="off"
                                   class="voin">
                        </div>
                    </li>
                    <li>
                        <div class="inputTxt">供应商：</div>
                        <div class="multLable">
                            <select name="supplier_id">
                                <option value="">请选择</option>
                                @foreach($suppliers as $re)
                                    <option value="{{ $re['id'] }}">{{ $re['name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </li>
                    <li>
                        <div class="inputTxt">目的仓库：</div>
                        <div class="inputBlock">
                            <div class="multLable">
                                <select name="warehouse_id" class="voin_select">
                                    <option value="">请选择</option>
                                    @foreach($warehouse as $re)
                                        <option value="{{ $re['id'] }}">{{ $re['warehouse_name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
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
                    <button type="button" class="layui-btn" id="addPlan">添加采购计划</button>
                    <button type="button" class="layui-btn" id="addProcurement">添加采购单</button>
                    {{--<button class="layui-btn" id="looklist">查看采购单</button>--}}
                </div>
            </div>

            <div class="edn-row table_index">
                <table class="" id="EDtable" lay-filter="EDtable"></table>
            </div>
        </div>
    </div>

    <script type="text/html" id="barDemo">
        @{{#  if(d.status === 1) { }}
        {{--<a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>--}}
        {{--<a class="layui-btn layui-btn-xs" lay-event="check">审核</a>--}}
        {{--<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>--}}

        <a class="layui-table-link" lay-event="edit">编辑</a>
        <a class="layui-table-link" lay-event="check">审核</a>
        <a class="layui-table-link" lay-event="del">删除</a>

        @{{# } else { }}
        {{--<a class="layui-btn layui-btn-xs" lay-event="detail">查看</a>--}}
        <a class="layui-table-link" lay-event="detail">查看</a>
        @{{#  } }}
    </script>

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
                    , url: '/procurement/procurementPlanIndexSearch'
                    , where: {data: info}
                    , cols: [[
                        {checkbox: true},
                        {field: '', title: '序号', width: 50, type: 'numbers'}

                        , {
                            field: 'procurement_no',
                            title: '采购计划编号',
                            event: 'getProcurementPlanDetails',
                            templet: function (d) {
                                return '<a href="javascript:void(0);" class="layui-table-link" >' + d.procurement_no + '</a>';
                            }
                        }
                        , {
                            field: 'warehouse', title: '目的仓库', templet: function (d) {
                                if (d.warehouse) {
                                    return d.warehouse.warehouse_name;
                                }
                                return  '';
                            }
                        }
                        , {
                            field: 'total_amount', title: '商品总数量', templet: function (d) {
                                return d.total_amount;
                            }
                        }
                        , {
                            field: 'total_price', title: '商品总金额（RMB）', templet: function (d) {
                                return d.total_price;
                            }
                        }
                        , {
                            field: 'username', title: '创建人', templet: function (d) {
                                return d.users.username;
                            }
                        }
                        , {
                            field: 'created_at', title: '创建时间', templet: function (d) {
                                return d.created_at;
                            }
                        }
                        , {
                            field: 'Dec', title: '采购备注', templet: function (d) {
                                if (!d.Dec) {
                                    return '';
                                }
                                return d.Dec;
                            }
                        }
                        , {
                            field: 'status', title: '采购计划状态', templet: function (d) {

                                switch (d.status) {
                                    case 1:
                                        return '草稿';
                                    case 2:
                                        return '审核';
                                    case 3:
                                        return '转采购';
                                }
                            }
                        }
                        , {
                            field: 'order_no', title: '采购单号', templet: function (d) {
                                return d.order_no;
                            }
                        }
                        , {field: '', title: '操作', toolbar: '#barDemo'}
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
                , url: '/procurement/procurementPlanIndexSearch'
                , cols: [[
                    {checkbox: true},
                    {field: '', title: '序号', width: 50, type: 'numbers'}

                    , {
                        field: 'procurement_no',
                        title: '采购计划编号',
                        event: 'getProcurementPlanDetails',
                        templet: function (d) {
                            return '<a href="javascript:void(0);" style="color: #01AAED" >' + d.procurement_no + '</a>';
                        }
                    }
                    , {
                        field: 'warehouse', title: '目的仓库', templet: function (d) {
                            if (d.warehouse) {
                                return d.warehouse.warehouse_name;
                            }
                            return  '';
                        }
                    }
                    , {
                        field: 'total_amount', title: '商品总数量', templet: function (d) {
                            return d.total_amount;
                        }
                    }
                    , {
                        field: 'total_price', title: '商品总金额（RMB）', templet: function (d) {
                            return d.total_price;
                        }
                    }
                    , {
                        field: 'username', title: '创建人', templet: function (d) {
                            return d.users.username;
                        }
                    }
                    , {
                        field: 'created_at', title: '创建时间', templet: function (d) {
                            return d.created_at;
                        }
                    }
                    , {
                        field: 'Dec', title: '采购备注', templet: function (d) {
                            if (!d.Dec) {
                                return ''
                            }
                            return d.Dec;
                        }
                    }
                    , {
                        field: 'status', title: '采购计划状态', templet: function (d) {
                            switch (d.status) {
                                case 1:
                                    return '草稿';
                                case 2:
                                    return '审核';
                                case 3:
                                    return '转采购';
                            }
                        }
                    },
                    {
                        field: 'order_no', title: '采购单号', templet: function (d) {
                            return d.order_no;
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
                var ids = '';
                //监听单元格事件
                table.on('tool(EDtable)', function (obj) {
                    var data = obj.data;
                    if (obj.event === 'detail' || obj.event === 'getProcurementPlanDetails') {
                        layer.open({
                            type: 2,
                            title: data.procurement_no + ' 采购计划详情',
                            fix: false,
                            maxmin: true,
                            resize: true,
                            shadeClose: true,
                            offset:'r',
                            area: ['80%', '90%'],
                            content: '{{ url('procurement/procurementDetail') }}' + '/' + data.id,
                            end: function (index) {
                                layer.close(index);
                            }
                        });
                    } else if (obj.event === 'del') {
                        layer.confirm('确认删除吗？', function (index) {
                            $.ajax({
                                type: 'POST',
                                data: {
                                    id: data.id,
                                    _token: "{{ csrf_token() }}"
                                },
                                url: '{{url('procurement/delProcurementPlan')}}',
                                success: function (data) {
                                    if (data.status === 0) {
                                        layer.msg('删除失败', {icon: 5})
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

                    } else if (obj.event === 'check') {
                        layer.open({
                            type: 1,
                            title: '审核采购计划',
                            content: '<div style="padding: 50px 40px;">' + '是否确认审核采购计划: ' + data.procurement_no + '</div>',
                            btn: ['确定', '取消'],
                            btnAlign: 'c', //按钮居中
                            shade: 0,      //不显示遮罩
                            yes: function () {
                                $.ajax({
                                    type: 'POST',
                                    data: {
                                        id: data.id,
                                        _token: "{{ csrf_token() }}"
                                    },
                                    url: "{{url('procurement/checkProcurementPlan')}}",
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
                                    error: function (e, x, t) {

                                    }
                                })
                            },
                            btn2: function () {
                                layer.closeAll();
                            }
                        });
                    } else if (obj.event === 'edit') {
                        layer.open({
                            type: 2,
                            title: '编辑采购计划',
                            fix: false,
                            maxmin: true,
                            resize: true,
                            shadeClose: true,
                            offset:'r',
                            area: ['80%', '90%'],
                            content: '{{ url('procurement/editProcurementPlan') }}' + '/' + data.id,
                            end: function (index) {
                                layer.close(index);
                            }
                        });
                    }
                });

                table.on('checkbox(EDtable)', function (obj) {
                    var checkStatus = table.checkStatus('EDtable');
//                    if (obj.data.status != 2) {
//                        layer.msg('非审核采购计划不允许增加采购单', {icon: 5});
//                        $(this).removeClass('layui-form-checked');
//                        return false
//                    }
                    var id_array = new Array();
                    var data = checkStatus.data;

                    if (data.length > 0) {
                        for (var i = 0; i < data.length; i++) {
                            id_array.push(data[i].id);
                        }
                    }
                    ids = id_array.join(',');
                });

                $('#addProcurement').click(function () {
                    if (ids.length === 0) {
                        layer.msg('请选择采购计划')
                    } else {
                        layer.open({
                            type: 2,
                            title: '添加采购单',
                            fix: false,
                            maxmin: true,
                            resize: true,
                            shadeClose: true,
                            area: ['900px', '500px'],
                            content: '/procurement/procurementPlanToOrder?ids=' + ids,  //no 不要滚动条
                            end: function (index) {
                                ids = '';
                                layer.close(index);
                            }
                        });
                    }
                })
            });
        });

        $('#addPlan').click(function () {
            layer.open({
                type: 2,
                title: '添加采购计划',
                fix: false,
                maxmin: true,
                resize: true,
                shadeClose: true,
                offset:'r',
                area: ['80%', '90%'],
                content: '{{ url('procurement/add') }}',
                end: function end() {

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