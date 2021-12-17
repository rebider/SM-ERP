@extends('layouts.new_main')
@section('content')
    <form class="layui-form" action="">
        <input type="hidden" id="purchaseOrderId" value="{{ $purchaseOrder['id'] }}">
        <div class="produpage">
            <button type="button" class="layui-btn layui-btn-disabled">审核</button>
            <button type="button" class="layui-btn layui-btn-disabled">作废</button>
        </div>
        <div class="produpage layui-form">
            <h3>采购单状态</h3>
            <div id="zhuangtai"></div>
        </div>

        <div class="produpage layui-form lay-select">
            <h3>采购单信息</h3>
            <table class="layui-table" lay-skin="nob">
                <tbody>
                <tr>
                    <td>采购单号：{{ $purchaseOrder['order_no'] }}</td>
                    <td>创建人：{{ $purchaseOrder['users']['username'] }}</td>
                    @if ($purchaseOrder['check_user'])
                        <td>审核人：{{ $purchaseOrder['check_user']['username'] }}</td>
                    @else
                        <td>审核人：</td>
                    @endif
                </tr>
                </tbody>
            </table>
        </div>
        <div class="produpage layui-form lay-select">
            <h3>仓库信息</h3>
            <table class="layui-table" lay-skin="nob">
                <tbody>
                <tr>
                    <td>目的仓库：{{ $purchaseOrder['warehouse']['warehouse_name'] }}</td>
                    <td>物流方式：{{ $purchaseOrder['logistics']['logistic_name'] }}</td>
                    <td>跟踪号：{{ $purchaseOrder['tracking_no'] }}</td>
                </tr>
                <tr>
                    <td>商品总采购金额：{{ $totalPrice }} RMB</td>
                    <td>运费：{{ $purchaseOrder['freight'] }} RMB</td>
                    <td>预计到货日期：{{ $purchaseOrder['get_time'] }}</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="produpage  layui-form">
            <div class="layui-tab">
                <ul class="layui-tab-title">
                    <li class="layui-this">采购计划</li>
                    <li>产品明细</li>
                </ul>
                <div class="layui-tab-content">
                    <div class="layui-tab-item layui-show">
                        <table class="layui-table textmid" lay-skin="line">
                            <thead>
                            <tr>
                                <th>序号</th>
                                <th>采购计划编号</th>
                                <th>目的仓库</th>
                                <th>创建人</th>
                                <th>创建时间</th>
                                <th>采购备注</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($purchaseOrder['procurementPlan'] as $k => $re)
                                <tr>
                                    <td>{{ $k + 1 }}</td>
                                    <td>{{ $re['procurement_no'] }}</td>
                                    <td>{{ $re['warehouse']['warehouse_name'] }}</td>
                                    <td>{{ $re['users']['username'] }}</td>
                                    <td>{{ $re['created_at'] }}</td>
                                    @if($re['Dec'])
                                        <td>{{ $re['Dec'] }}</td>
                                    @else
                                        <td></td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="layui-tab-item">
                        <table class="layui-table textmid lasttd" lay-skin="line">
                            <thead>
                            <tr>
                                <th>序号</th>
                                <th>自定义SKU</th>
                                <th>供应商</th>
                                <th>采购数量</th>
                                <th>采购价</th>
                                <th>采购总金额</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($purchaseOrder['goods'] as $key => $goods)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $goods['sku'] }}</td>
                                <td>{{ $goods['supplier_name'] }}</td>
                                <td>{{ $goods['amount'] }}</td>
                                <td>{{ $goods['price'] }}</td>
                                <td>{{ $goods['amount']*$goods['price'] }}</td>
                            </tr>
                            @endforeach
                            <tr>
                                <td colspan="6" style="text-align: right;font-weight: bold;">
                                    <span style="display: inline-block; margin-right: 30px;">总数量：{{ $totalAmount }}</span>
                                    总采购金额：{{ $totalPrice }}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>


            <div style="padding-bottom: 10px;text-align: center; width: 100%">
                <button type="button" id="back" class="layui-btn layui-btn-danger">返回</button>
            </div>
    </form>
@endsection

@section('javascripts')
    <script>
        //采购单步骤
        layui.config({
            base: '../../layui/mods/extend/step/'
        }).use('step', function () {
            var step = layui.step
                , data0 = {
                steps: [{"title": "草稿", "time": "&nbsp;"},
                    {"title": "审核", "time": "&nbsp;"},
                    {"title": "在途", "time": "&nbsp;"},
                    {"title": "完成", "time": "&nbsp;"}]
                , current: "{{ $purchaseOrder['status'] === 5 ? 0 : $purchaseOrder['status'] }}"        //status = 5 --- 作废
            };
            step.ready({
                elem: '#zhuangtai',
                data: data0,
                width: '150px',
                color: {
                    success: 'green',
                    error: '#999'
                }
            })
        });

        layui.config({base: '../../layui/lay/modules/'}).extend({formSelects: 'formSelects-v3'});
        layui.use(['layer', 'form', 'element', 'laydate', 'table', 'formSelects'], function () {
            var layer = layui.layer, form = layui.form, laydate = layui.laydate, table = layui.table,
                element = layui.element, formSelects = layui.formSelects;


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