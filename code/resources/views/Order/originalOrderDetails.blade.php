@extends('layouts.new_dialog')
@section('css')
    <style>
        .productexts h3 {
            color: #1E9FFF;
            font-weight: 700;
        }

        .productexts .layui-table tbody tr:hover {
            background-color: #fff !important
        }

        .kbmodel_full .content-wrappers {
            background: #fff;
            padding: 20px;
        }
    </style>
@endsection
@section('content')
    <div class="kbmodel_full">
        <div class="content-wrappers">
            <div class="productexts" id="bianjitext">
                <form action="" class="layui-form">
                    {{csrf_field()}}
                    <input type="hidden" name="order_id" value="{{ $orderInfo['id'] ?? '' }}">
                    <!--订单信息-->
                    <div class="produpage layui-form lay-select">
                        <h3>订单信息</h3>
                        <table class="layui-table colora" lay-skin="nob">
                            <tbody>
                            <tr>
                                <td><b>订单号：</b>{{ $orderInfo['order_number'] ?? '' }}
                                    @if(!empty($orderInfo['order_id']))
                                        @if (isset($edit) && $edit == 1)
                                            <a href="/order/orderDetails/{{$orderInfo['order_id']}}">【查看系统订单】</a>
                                        @else
                                            <a href="javascript:void(0)">【查看系统订单】</a>
                                        @endif
                                    @endif
                                </td>
                                <td><b>来源平台：</b>{{ $orderInfo['platform_name'] ?? '' }}</td>
                                <td><b>支付时间：</b>{{ $orderInfo['payment_time'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <td><b>电商单号：</b>{{ $orderInfo['platform_order'] ?? '' }}</td>
                                <td><b>来源店铺：</b>{{ $orderInfo['shops']['shop_name'] ?? '' }}</td>
                                <td><b>创建时间：</b>{{ $orderInfo['created_at'] ?? '' }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--订单商品信息-->
                    <div class="produpage layui-form textpage">
                        <h3>产品信息</h3>
                        <table class="layui-table xianwidth" lay-skin="line">
                            <thead>
                            <tr>
                                <th>序号</th>
                                <th>商品图片</th>
                                <th>产品编码</th>
                                <th>产品名称</th>
                                <th>单价（本币）</th>
                                <th>币种</th>
                                <th>购买数量</th>
                                <th>购买金额</th>
                                <th>币种</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach ($orderInfo['original_order_products'] as $goodsKey => $goods)
                                <tr>
                                    <td>{{$goodsKey+1}}</td>
                                    <td>
                                        <img onerror="this.src='/img/imgNotFound.jpg'" src="{{url('showImage').'?path=' . $goods['goods_img']}}" style="max-width: 24px; max-height: 24px;cursor:zoom-in;" @if (isset($goods['goods_img'])) onclick="check_img(this.src)"@endif/>
                                    </td>
                                    <td>{{$goods['sku']}}</td>
                                    <td>{{$goods['goods_name']}}</td>
                                    <td>{{$goods['price']}}</td>
                                    <td>{{$orderInfo['currency']}}</td>
                                    <td>{{$goods['quantity']}}</td>
                                    <td>{{$goods['quantity'] * $goods['price']}}</td>
                                    <td>{{$orderInfo['currency']}}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td colspan="2">备注：{{$orderInfo['mark']}}</td>
                                <td colspan="2">汇率：{{$orderInfo['rate']}}</td>
                                <td colspan="2">客户运费：{{$orderInfo ['freight']}}&nbsp;{{$orderInfo ['currency_freight']}}
                                </td>
                                <td colspan="2">产品总金额：{{$orderInfo ['order_price'] - $orderInfo ['freight']}}&nbsp;{{$orderInfo['currency_freight']}}
                                </td>
                                <td colspan="2">订单总金额：{{$orderInfo ['order_price']}}&nbsp;{{$orderInfo['currency_freight']}}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--订单收货地址-->
                    <div class="produpage layui-form">
                        <h3>地址信息</h3>
                        <table class="layui-table seledis" lay-skin="nob">
                            <tbody>
                            <tr>
                                <td><b>收件人：</b>{{$orderInfo['addressee_name']}}</td>
                                <td><b>州/省：</b>{{$orderInfo['province']}}</td>
                                <td><b>电话：</b>{{$orderInfo['mobile_phone']}}</td>
                            </tr>
                            <tr>
                                <td><b>买家email：</b>{{$orderInfo['addressee_email']}}</td>
                                <td><b>地址1：</b>{{$orderInfo['addressee1']}}</td>
                                <td><b>邮编：</b>{{$orderInfo['zip_code']}}</td>
                            </tr>
                            <tr>
                                <td><b>国家：</b>{{$orderInfo['country']}}</td>
                                <td><b>地址2：</b>{{$orderInfo['addressee2']}}</td>
                                <td><b>城市：</b>{{$orderInfo['city']}}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>


                    <div class="produpage  layui-form">
                        <div class="layui-tab">
                            <ul class="layui-tab-title">
                                <li class="layui-this">付款单</li>
                            </ul>
                            <div class="layui-tab-content">
                                <!--配货单信息-->
                                <div class="layui-tab-item layui-show">
                                    <table class="layui-table textmid" lay-skin="line">
                                        <thead>
                                        <tr>
                                            <th>付款单号</th>
                                            <th>金额</th>
                                            <th>币种</th>
                                            <th>创建时间</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($orderInfo ['pay_bill'] as $orders_bill_payments)
                                            <tr>
                                                <td>{{$orders_bill_payments ['bill_code']}}</td>
                                                <td>{{$orders_bill_payments ['amount']}}</td>
                                                <td>{{$orders_bill_payments ['currency_code']}}</td>
                                                <td>{{$orders_bill_payments ['created_at']}}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

        </div>
    </div>
@endsection

@section('javascripts')
    <script>
        layui.use(['form', 'laydate', 'table', 'element', 'upload', 'laypage', 'layer'], function () {
            var laypage = layui.laypage,
                layer = layui.layer;
            var _order_id = $('input[name="order_id"]').val();

            // page完整功能
            laypage.render({
                elem: 'swiPaging'
                , count: 100
                , layout: ['count', 'prev', 'page', 'next', 'limit', 'refresh', 'skip']
                , jump: function (obj) {
                    console.log(obj)
                }
            });



        });

    </script>
@endsection