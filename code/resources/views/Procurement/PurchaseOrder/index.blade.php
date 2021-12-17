@extends('layouts/new_main')

@section('content')
    <div class="kbmodel_full">
{{--        @include('layouts/shortcutMenus')--}}
        <div class="content-wrapper">
            <form class="layui-form multiSearch">
                <ul class="flexSearch flexquar fclear">
                    <li style="width: 100%">
                        <div class="inputTxt">采购单状态：</div>
                        <div class="inputBlock">
                            <div class="multLable">
                                <input type="radio" name="status" value="" title="全部" checked>
                                <input type="radio" name="status" value="1" title="草稿">
                                <input type="radio" name="status" value="2" title="审核">
                                <input type="radio" name="status" value="3" title="在途">
                                <input type="radio" name="status" value="4" title="完成">
                                <input type="radio" name="status" value="5" title="作废">
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="inputTxt">采购单号：</div>
                        <div class="inputBlock">
                            <input type="text" name="order_no" placeholder=" " autocomplete="off"
                                   class="voin">
                        </div>
                    </li>
                    <li>
                        <div class="inputTxt">目的仓库：</div>
                        <div class="multLable" style="width: 79%;">
                            <select name="warehouse_id" class="voin_select">
                                <option value="">请选择</option>
                                @foreach($warehouse as $re)
                                    <option value="{{ $re['id'] }}">{{ $re['warehouse_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </li>
                    <li>
                        <div class="inputTxt">跟踪号：</div>
                        <div class="inputBlock">
                            <input type="text" name="tracking_no" placeholder=" " autocomplete="off"
                                   class="voin">
                        </div>
                    </li>
                    <li style="margin-right: 100px;width: 29%;">
                        <div class="inputTxt">物流方式：</div>
                        <div class="inputBlock">
                            <div class="multLable">
                                <select name="logistics_id" class="voin_select">
                                    <option value="">请选择</option>
                                    @foreach($logistics as $re)
                                        <option value="{{ $re['id'] }}">{{ $re['logistic_name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </li>
                    <li style="margin-right: 1000px;">
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
                    <button type="button" class="layui-btn layuiadmin-btn-order" id="exitOrder">导出</button>
                </div>
            </div>
            <div class="edn-row table_index">
                <table class="" id="EDtable" lay-filter="EDtable"></table>
            </div>
        </div>

        <script type="text/html" id="barDemo">
            @{{#  if(d.status === 1) { }}
            {{--<a class="layui-btn layui-btn-xs" lay-event="check">审核</a>--}}
            {{--<a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">作废</a>--}}
            <a class="layui-table-link" lay-event="check">审核</a>
            <a class="layui-table-link" lay-event="del">作废</a>
            @{{# } else if(d.status === 5) { }}
            {{--<a href="javascript:;" class="layui-btn layui-btn-xs" lay-event="">--</a>--}}
            @{{# } else { }}
            {{--<a class="layui-btn layui-btn-xs" lay-event="detail">查看</a>--}}
            <a class="layui-table-link" lay-event="detail">查看</a>
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

                    form.on('submit(searBtn)', function (data) {
                        var index = layer.msg('数据请求中', {icon: 16});
                        var info = data.field;
                        table.render({
                            elem: '#EDtable'
                            , url: '/purchase/purchaseOrderIndexSearch'
                            , where: {data: info}
                            , cols: [[
                                {checkbox: true},
                                {field: '', title: '序号', width: 50, type: 'numbers'}

                                , {
                                    field: 'order_no',
                                    title: '采购单号',
                                    event: 'getPurchaseOrderDetails',
                                    templet: function (d) {
                                        return '<a href="javascript:void(0);" style="color: #01AAED" >' + d.order_no + '</a>';
                                    }
                                }
                                , {
                                    field: 'warehouse', title: '目的仓库', templet: function (d) {
                                        if (d.warehouse) {
                                            return d.warehouse.warehouse_name;
                                        }
                                        return  '';
                                    }
                                }, {
                                    field: 'logistics', title: '物流方式', templet: function (d) {
                                        if (d.logistics) {
                                            return d.logistics.logistic_name;
                                        }
                                        return '';
                                    }
                                }, {
                                    field: 'tracking_no', title: '跟踪号', templet: function (d) {
                                        if (d.tracking_no){
                                            return d.tracking_no
                                        }
                                        return '';
                                    }
                                }
                                , {
                                    field: 'total_amount', title: '商品总数量', templet: function (d) {
                                        var total = 0;
                                        for (var i = 0; i < d.procurement_plan.length; i++) {
                                            total += parseInt(d.procurement_plan[i].total_amount)
                                        }
                                        return total;
                                    }
                                }
                                , {
                                    field: 'total_price', title: '商品总金额（RMB）', templet: function (d) {
                                        var price = 0;
                                        for (var i = 0; i < d.procurement_plan.length; i++) {
                                            price += parseFloat(d.procurement_plan[i].total_price)
                                        }
                                        return price.toFixed(2);
                                    }
                                }
                                , {
                                    field: 'freight', title: '运费（RMB）', templet: function (d) {
                                        return d.freight;
                                    }
                                }
                                , {
                                    field: 'status', title: '采购单状态', templet: function (d) {
                                        switch (d.status) {
                                            case 1:
                                                return '草稿';
                                            case 2:
                                                return '审核';
                                            case 3:
                                                return '在途';
                                            case 4:
                                                return '完成';
                                            case 5:
                                                return '作废';
                                        }
                                    }
                                },
                                {
                                    field: 'updated_at', title: '更新时间', templet: function (d) {
                                        return d.updated_at;
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
                        , url: '/purchase/purchaseOrderIndexSearch'
                        , cols: [[
                            {checkbox: true},
                            {field: '', title: '序号', width: 50, type: 'numbers'}

                            , {
                                field: 'order_no',
                                title: '采购单号',
                                event: 'getPurchaseOrderDetails',
                                templet: function (d) {
                                    return '<a href="javascript:void(0);" style="color: #01AAED" >' + d.order_no + '</a>';
                                }
                            }
                            , {
                                field: 'warehouse', title: '目的仓库', templet: function (d) {
                                    if (d.warehouse) {
                                        return d.warehouse.warehouse_name;
                                    }
                                    return  '';
                                }
                            }, {
                                field: 'logistics', title: '物流方式', templet: function (d) {
                                    if (d.logistics) {
                                        return d.logistics.logistic_name;
                                    }
                                    return '';
                                }
                            }, {
                                field: 'tracking_no', title: '跟踪号', templet: function (d) {
                                    if (d.tracking_no){
                                        return d.tracking_no
                                    }
                                    return '';
                                }
                            }
                            , {
                                field: 'total_amount', title: '商品总数量', templet: function (d) {
                                    var total = 0;
                                    for (var i = 0; i < d.procurement_plan.length; i++) {
                                        total += parseInt(d.procurement_plan[i].total_amount)
                                    }
                                    return total;
                                }
                            }
                            , {
                                field: 'total_price', title: '商品总金额（RMB）', templet: function (d) {
                                    var price = 0;
                                    for (var i = 0; i < d.procurement_plan.length; i++) {
                                        price += parseFloat(d.procurement_plan[i].total_price)
                                    }
                                    return price.toFixed(2);
                                }
                            }
                            , {
                                field: 'freight', title: '运费（RMB）', templet: function (d) {
                                    return d.freight;
                                }
                            }
                            , {
                                field: 'status', title: '采购单状态', templet: function (d) {
                                    switch (d.status) {
                                        case 1:
                                            return '草稿';
                                        case 2:
                                            return '审核';
                                        case 3:
                                            return '在途';
                                        case 4:
                                            return '完成';
                                        case 5:
                                            return '作废';
                                    }
                                }
                            },
                            {
                                field: 'updated_at', title: '更新时间', templet: function (d) {
                                    return d.updated_at;
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

                    form.on('submit(reset)', function (data) {
                        window.location.reload(true);
                        return false;
                    });

                    layui.use('table', function () {
                        var table = layui.table;
                        var ids = '';
                        //监听单元格事件
                        table.on('tool(EDtable)', function (obj) {
                            var data = obj.data;
                            if (obj.event === 'detail' || obj.event === 'getPurchaseOrderDetails') {
                                layer.open({
                                    type: 2,
                                    title: data.order_no + ' 采购单详情',
                                    fix: false,
                                    maxmin: true,
                                    resize: true,
                                    shadeClose: true,
                                    offset:'r',
                                    area: ['80%', '90%'],
                                    content: '{{ url('purchase/purchaseOrderDetail') }}' + '/' + data.id,
                                    end: function (index) {
                                        layer.close(index);
                                    }
                                });
                            } else if (obj.event === 'del') {
                                layer.confirm('是否确认作废采购单：' + data.order_no, function (index) {
                                    $.ajax({
                                        type: 'POST',
                                        data: {
                                            id: data.id,
                                            _token: "{{ csrf_token() }}"
                                        },
                                        url: '{{url('purchase/delPurchaseOrder')}}',
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

                            } else if (obj.event === 'check') { //审核采购单
                                $.ajax({
                                    type: "POST",
                                    data: {
                                        id: data.id,
                                        _token: "{{ csrf_token() }}"
                                    },
                                    url: "{{ url('purchase/checkPurchaseOrder') }}",
                                    success: function (data) {
                                        if (data.status > 0) {
                                            layer.msg(data.msg, {icon: 6});
                                            setTimeout(function () {
                                                table.reload('EDtable'); //重载表格
                                            }, 2000);
                                        } else {
                                            layer.msg(data.msg, {icon: 5})
                                        }
                                    },
                                    error: function (e, x, t) {

                                    }
                                })
                            }
                        });

                        table.on('checkbox(EDtable)', function (obj) {
                            var checkStatus = table.checkStatus('EDtable');
                            var id_array = new Array();
                            var data = checkStatus.data;

                            if (data.length > 0) {
                                for (var i = 0; i < data.length; i++) {
                                    id_array.push(data[i].id);
                                }
                            }
                            ids = id_array.join(',');
                        });

                        //导出
                        $('#exitOrder').click(function () {
                            if (ids.length === 0) {
                                layer.msg('请选择需要导出的记录！')
                            } else {
                                window.location.href = "/purchase/exportPurchaseOrder?ids=" + ids;
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

                $(document).ready(function () {
                    if (self === top) {
                        $(".content-wrapper").animate({margin: '2rem'}, 300)
                    }
                })

            </script>
@endsection