@extends('layouts.new_main')
@section('content')
    <form class="layui-form" action="">
        <div class="produpage">
            <button type="button" class="layui-btn layui-btn-disabled">编辑</button>
            <button type="button" class="layui-btn layui-btn-disabled">审核</button>
            <button type="button" class="layui-btn layui-btn-disabled">删除</button>
        </div>
        <input type="hidden" id="procurementId" value="{{ $procurementPlan['id'] }}">
        <div class="produpage layui-form lay-select">
            <h3 style="color: #1E9FFF;font-weight: 700;">采购计划信息</h3>
            <table class="layui-table" lay-skin="nob">
                <tbody>
                <tr>
                    <td>采购计划编号：{{ $procurementPlan['procurement_no'] }}</td>
                    <td>目的仓库：{{ $procurementPlan['warehouse']['warehouse_name'] }}</td>
                    <td>采购备注：{{ $procurementPlan['Dec'] }}</td>
                </tr>
                <tr>
                    <td>创建人：{{ $procurementPlan['users']['username'] }}</td>
                    @if($procurementPlan['check_user'])
                        <td>审核人：{{ $procurementPlan['check_user']['username'] }}</td>
                    @else
                        <td>审核人：</td>
                    @endif
                </tr>
                <tr>
                    <td>创建时间：{{ $procurementPlan['created_at'] }}</td>
                    <td>审核时间：{{ $procurementPlan['check_time'] }}</td>
                    <td></td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="produpage layui-form textpage">
            <h3 style="float: left;">产品信息</h3>
        </div>

        <div class="edn-row table_index" style="margin-left: 5px">
            <table class="" id="EDtable" lay-filter="EDtable"></table>
        </div>
        <div class="layui-elem-field layui-field-title">
            <div style="float: right;width: 35%;padding-top: 10px;" >
                <span>总数量：</span> <span>{{ $procurementPlan['total_amount'] }}</span> &nbsp;&nbsp;&nbsp;&nbsp;
                <span>总采购金额：</span> <span>{{ $procurementPlan['total_price'] }}</span>
            </div>

        </div>

        <div class="layui-elem-field layui-field-title" style="margin-top: 40px;">
            <div style="padding-top: 10px;text-align: center; width: 90%">
                <button type="button" id="back" class="layui-btn layui-btn-danger">返回</button>
            </div>
        </div>
    </form>
@endsection

@section('javascripts')

    <script>
        layui.config({base: '../../layui/lay/modules/'}).extend({formSelects: 'formSelects-v3'});
        layui.use(['layer', 'form', 'element', 'laydate', 'table', 'formSelects'], function () {
            var layer = layui.layer, form = layui.form, laydate = layui.laydate, table = layui.table,
                element = layui.element, formSelects = layui.formSelects;
            table.render({
                elem: '#EDtable'
                , url: '/procurement/procurementPlanGoods/'+ $('#procurementId').val()
                , cols: [[
                    {field: '', title: 'NO', width: 50, type: 'numbers'}

                    , {
                        field: 'goods_pictures', title: '产品图片', templet: function (d) {
                            return '<img src="{{url('showImage')}}'+'?path='+d.goods_pictures+'" onerror="this.src=\'/img/imgNotFound.jpg\'" >';
                        }
                    }
                    , {
                        field: 'goods_sku', title: '自定义SKU', templet: function (d) {
                            return d.goods_sku;
                        }
                    }
                    , {
                        field: 'goods_name', title: '产品名称', templet: function (d) {
                            return d.goods_name;
                        }
                    }
                    , {
                        field: 'amount', title: '购买数量', templet: function (d) {
                            return d.amount;
                        }
                    }
                    , {
                        field: 'price', title: '采购价', templet: function (d) {
                            return d.price;
                        }
                    }
                    , {
                        field: 'supplier_name', title: '供应商', templet: function (d) {
                            return d.supplier_name;
                        }
                    }
                    , {
                        field: '', title: '总采购金额', templet: function (d) {
                            return (d.amount*d.price).toFixed(2);
                        }
                    }
                ]]
                , limit: 20
                , page: true
                , limits: [20, 30, 40, 50]
                , done: function () {   //返回数据执行回调函数

                }
            });

            $('#back').click(function () {
                var index = parent.layer.getFrameIndex(window.name);
                parent.layer.close(index);//关闭当前页
            })
        });

        $("body").bind("keydown", function (event) {
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location = location;
            }
        })

    </script>
@endsection