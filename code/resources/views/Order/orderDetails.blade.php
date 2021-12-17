@extends('layouts.new_dialog')
@section('css')
    <style>
        body{
            min-width: unset !important;
        }
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
        .oneChoosePr .layui-checkbox-disbaled .layui-icon{background-color:#ddd;}
        .checkboxAll .layui-checkbox-disbaled .layui-icon{background-color:#ddd;}
    </style>
@endsection
@section('content')
    <div class="kbmodel_full">
        <div class="content-wrappers">
            <div class="productexts" id="bianjitext">
                <form action="" class="layui-form">
                    {{csrf_field()}}
                    <input type="hidden" name="order_id" value="{{ $orderInfo['id'] ?? '' }}">
                    <!--订单操作-->
                    <div class="produpage" lay-filter="btn">
                        @if($data ['action'] ['edit'] == 1 && $data['action']['picking'] !=1)
                            <button onclick="event.preventDefault()" class="layui-btn layui-btn-normal" id="saveOrder">
                                保存
                            </button>
                        @else
                            <button onclick="event.preventDefault()" class="layui-btn layui-btn-normal layui-btn-disabled" id="saveOrder" disabled="disabled">
                                保存
                            </button>
                        @endif
                        &nbsp;&nbsp;
                        @if($data ['action'] ['intercept'] == 1 && $orderInfo['intercept_status'] == 1 &&$orderInfo['status']==1)
                            <button onclick="event.preventDefault()" class="layui-btn layui-btn-normal" id="lanjie">
                                拦截订单
                            </button>
                        @else
                            <button onclick="event.preventDefault()"
                                    class="layui-btn layui-btn-normal layui-btn-disabled" lay-filter="intercept"
                                    disabled="disabled" id="lanjie">拦截订单
                            </button>
                        @endif
                        &nbsp;&nbsp;
                        @if($data ['action'] ['refound'] == 1 && $orderInfo['status']==1)
                            <button onclick="event.preventDefault()" class="layui-btn layui-btn-normal" id="tuikuan"
                                    lay-filter="refound">部分退款
                            </button>
                        @else
                            <button onclick="event.preventDefault()"
                                    class="layui-btn layui-btn-normal layui-btn-disabled" lay-filter="refound"
                                    disabled="disabled" id="tuikuan">部分退款
                            </button>
                        @endif
                        &nbsp;&nbsp;
                        @if($data ['action'] ['aftersale'] == 1 && $orderInfo['status']==1)
                            <button onclick="event.preventDefault()" class="layui-btn layui-btn-normal"
                                    lay-filter="aftersale" id="shouhou">创建售后单
                            </button>
                        @else
                            <button onclick="event.preventDefault()"
                                    class="layui-btn layui-btn-normal layui-btn-disabled" lay-submit=""
                                    lay-filter="aftersale" disabled="disabled" id="shouhou">创建售后单
                            </button>
                        @endif
                        &nbsp;&nbsp;
                        @if($data ['action'] ['cancel'] == 1 &&$orderInfo['status']==1)
                            <button onclick="event.preventDefault()" class="layui-btn layui-btn-normal cancelOrderBtn"
                                    lay-filter="cancel">取消订单
                            </button>
                        @else
                            <button class="layui-btn layui-btn-normal layui-btn-disabled" disabled="disabled">取消订单
                            </button>
                        @endif
                    </div>
                    <!--订单状态-->
                    <div class="produpage layui-form lay-select">
                        <table class="layui-table colora" lay-skin="nob">
                            <tbody>
                            <tr>
                                <td>
                                    配货状态：@if($orderInfo['picking_status'] == 1)
                                        未配货
                                             @if (!empty($orderInfo ['problem'] ))
                                                <span style="color: red">({{$orderInfo ['problem']}})</span>
                                             @endif
                                    @elseif($orderInfo['picking_status'] == 2)
                                        配货成功
                                    @elseif($orderInfo['picking_status'] == 3)
                                        部分配货
                                    @elseif($orderInfo['picking_status'] == 4)
                                        配货失败
                                    @else
                                    @endif
                                </td>
                                <td>
                                    发货状态：@if($orderInfo['deliver_status'] == 1)
                                        未发货
                                    @elseif($orderInfo['deliver_status'] == 2)
                                        发货成功
                                    @elseif($orderInfo['deliver_status'] == 3)
                                        部分发货
                                    @else
                                    @endif
                                </td>
                                <td>
                                    拦截状态：@if($orderInfo['intercept_status'] == 1)
                                        未拦截
                                    @elseif($orderInfo['intercept_status'] == 2)
                                        拦截中 <a href="javascript:void(0);">取消拦截</a>
                                    @elseif($orderInfo['intercept_status'] == 3)
                                        拦截成功
                                    @elseif($orderInfo['intercept_status'] == 4)
                                        拦截失败
                                    @else
                                    @endif
                                </td>
                                <td>
                                    退款状态：@if($orderInfo['sales_status'] == 1)
                                        未申请部分退款
                                    @elseif($orderInfo['sales_status'] == 2)
                                        部分退款申请中
                                    @elseif($orderInfo['sales_status'] == 3)
                                        申请部分退款成功
                                    @elseif($orderInfo['sales_status'] == 4)
                                        申请部分退款失败
                                    @else
                                    @endif
                                </td>
                                <td>
                                    订单状态：@if($orderInfo['status'] == 1)
                                        未完结
                                    @elseif($orderInfo['status'] == 2)
                                        已完结
                                    @elseif($orderInfo['status'] == 3)
                                        已作废
                                    @else
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <!--最近配货时间-->
                                    @if($orderInfo['picking_status'] == 2 || $orderInfo['picking_status'] == 3 )
                                        {{!empty($orderInfo['orders_invoices_value']) ? $orderInfo['orders_invoices_value'][0]['created_at'] :'' }}
                                    @else
                                    @endif
                                </td>
                                <td>
                                    <!--最近发货时间-->
                                    @if($orderInfo['deliver_status'] == 2 || $orderInfo['deliver_status'] == 3 )
                                        {{!empty(strtotime($orderInfo['logistics_time'])) ? $orderInfo['logistics_time'] :'' }}
                                    @else
                                    @endif
                                </td>
                                <td></td>
                                <td></td>
                                <td>
                                    <!--完结时间-->
                                    @if($orderInfo['status'] == 2 || $orderInfo['status'] == 3 )
                                        {{$orderInfo['updated_at']}}
                                    @else
                                    @endif
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--订单信息-->
                    <div class="produpage layui-form lay-select">
                        <h3>订单信息</h3>
                        <table class="layui-table colora" lay-skin="nob">
                            <tbody>
                            <tr>
                                <td><b>订单号：</b>{{ $orderInfo['order_number'] ?? '' }}
                                    <a href="javascript:void(0)"
                                        @if (isset($edit) && $edit == 1)
                                        class="origOrderInfo"
                                        @endif
                                    >【查看原始订单】</a>
                                </td>
                                <td><b>来源平台：</b>{{ $orderInfo['platforms']['name_EN'] ?? '' }}</td>
                                <td><b>支付时间：</b>{{ $orderInfo['payment_time'] ?? '' }}</td>
                            </tr>
                            <tr>
                                <td><b>电商单号：</b>{{ $orderInfo['plat_order_number'] ?? '' }}</td>
                                <td><b>来源店铺：</b>{{ $orderInfo['shops']['shop_name'] ?? '' }}</td>
                                <td><b>创建时间：</b>{{ $orderInfo['created_at'] ?? '' }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--订单商品信息-->
                    <div class="produpage layui-form textpage">
                        <h3>产品信息</h3>
                        <div class="xiala layui-row">
                            @if($data ['action'] ['edit'] == 1)
                                <input type="text" placeholder="请输入SKU" autocomplete="off"
                                       class="layui-input sku_search" style="width: 50%;display: inline-block;" value="">
                                <button class="layui-btn layui-btn-sm search-product" type="button">添加SKU</button>
                            @endif
                        </div>
                        <table class="layui-table xianwidth add-product" lay-skin="line">
                            <thead>
                            <tr>
                                <th>序号</th>
                                <th>商品图片</th>
                                <th>产品编码</th>
                                <th>产品名称</th>
                                <th>购买数量</th>
                                <th>单价（本币）</th>
                                <th>币种</th>
                                <th>产品尺寸（CM）</th>
                                <th>产品重量（KG）</th>
                                @if($data ['action'] ['edit'] == 1)
                                    <th>操作</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>

                            @foreach ($data ['goods'] as $goodsKey => $goods)
                                <tr>
                                    <td>{{$goodsKey + 1}}
                                        <input name="orderGoodsId[]" hidden value="{{$goods['goods_id']}}">
                                        <input name="orderGoodsSKU[]" hidden value="{{$goods['goods']['sku']}}">
                                    </td>
                                    <td>
                                        @if (isset($goods ['goods'] ['goods_pictures']))
                                            <img onerror="this.src='/img/imgNotFound.jpg'" src="{{url('showImage').'?path=' . $goods ['goods'] ['goods_pictures']}}" style="max-width: 24px; max-height: 24px;cursor:zoom-in;"  onclick="check_img(this.src)"/>
                                        @else
                                            <img  onerror="this.src='/img/imgNotFound.jpg'" src="" style="max-width: 24px; max-height: 24px;" />
                                        @endif
                                    </td>
                                    <td>{{$goods['goods']['sku']}}
                                        <input type="hidden" value="{{$goods['goods']['sku']}}" name="sku[]" class="sku-value">
                                    </td>
                                    <td>{{$goods['goods']['goods_name']}}</td>
                                    <td><input type="number" name="goods_nums[]" value="{{$goods['buy_number']}}" min="1"  class="good-quantity"/></td>
                                    <td><input type="text" name="goods_price[]" value="{{$goods['univalence']}}" min="0" onkeyup="value=value.match(/\d+\.?\d{0,2}/,'')"  class="edit-price"></td>
                                    <td>{{$goods['currency']}}</td>
                                    <td>{{$goods['goods']['goods_length']??'0.00'}}*{{$goods['goods']['goods_width']??'0.00'}}
                                        *{{$goods['goods']['goods_height']??'0.00'}}</td>
                                    <td>{{$goods['goods']['goods_weight']??'0.00'}}</td>
                                    @if($data ['action'] ['edit'] == 1)
                                        <td>
                                            <button type="button" class="layui-btn layui-btn-xs layui-btn-danger goodsDel" >
                                                删除
                                            </button>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            <tr id="bottom-line">
                                <td colspan="2">备注：{{$orderInfo['mark']}}</td>
                                <td colspan="2">汇率：{{$orderInfo['rate']}}</td>
                                @if($data ['action'] ['edit'] == 1)
                                    <td colspan="2">客户运费：<input type="text"
                                                                value="{{$orderInfo ['freight']}}" class="currency_freight" name="freight" onkeyup="value=value.match(/\d+\.?\d{0,2}/,'')" >&nbsp;{{$orderInfo ['currency_freight']}}
                                        <input type="hidden"
                                               value="{{$orderInfo ['freight']}}" class="old_freight">
                                    </td>
                                    <td colspan="2">产品总金额：<input type="text" name="goods_total_price" min="0"
                                                                 value="{{$orderInfo ['order_price'] - $orderInfo ['freight']}}" readonly class="amount-of-goods">&nbsp;{{$orderInfo ['currency_code']}}
                                    </td>
                                    <td colspan="2">订单总金额：<input type="text" name="order_total_price"
                                                                 value="{{$orderInfo ['order_price']}}" readonly class="amount-of-order">&nbsp;{{$orderInfo ['currency_code']}}
                                    </td>
                                @else
                                    <td colspan="2">客户运费：{{$orderInfo ['freight']}}&nbsp;{{$orderInfo ['currency_freight']}}
                                    </td>
                                    <td colspan="2">产品总金额：{{$orderInfo ['order_price']}}&nbsp;{{$orderInfo ['currency_code']}}
                                    </td>
                                    <td colspan="2">订单总金额：{{$orderInfo ['order_price']}}&nbsp;{{$orderInfo ['currency_code']}}
                                    </td>
                                @endif

                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <!--订单问题-->
                    <div class="produpage  layui-form">
                        <div class="layui-tab">
                            <ul class="layui-tab-title">
                                <li class="layui-this">需处理问题</li>
                                <li>已处理问题</li>
                            </ul>
                            <div class="layui-tab-content">
                                <div class="layui-tab-item layui-show">
                                    <table class="layui-table textmid" lay-skin="line">
                                        <thead>
                                        <tr>
                                            <th>异常名称</th>
                                            <th>详细描述</th>
                                            <th>添加人</th>
                                            <th>处理人</th>
                                            <th>创建时间</th>
                                            <th>操作</th>
                                        </tr>
                                        </thead>
                                        <tbody id="waitingProblems">
                                        @if(isset($data ['waitingProblems']))
                                            @foreach ($data ['waitingProblems'] as $waitingProblems)
                                                <tr>
                                                    <td>{{$waitingProblems['trouble_name']}}</td>
                                                    @if($waitingProblems ['trouble_type_id'] == 4 && ($orderInfo['status'] == 1))
                                                        <td>{{$orderInfo['waitDesc']}}</td>
                                                    @else
                                                        <td>{{$waitingProblems['trouble_desc']}}</td>
                                                    @endif
                                                    <td>系统</td>
                                                    <td>系统</td>
                                                    <td>{{$waitingProblems['created_at']}}</td>
                                                    <td>
                                                        <input name="problemId" value="{{$waitingProblems['id']}}" type="hidden">
                                                        @if($waitingProblems ['trouble_type_id'] == 4 && ($orderInfo['status'] == 1))
                                                            <button onclick="event.preventDefault()" class="layui-btn layui-btn-xs layui-btn-danger" id="cancelProblem">
                                                                无须合并
                                                            </button>
                                                            <button onclick="event.preventDefault()" data-id="{{$waitingProblems['id']}}" class="layui-btn layui-btn-xs layui-btn-danger" id="mergeOrder">
                                                                合并订单
                                                            </button>
                                                        @else
                                                            <button onclick="event.preventDefault()" class="layui-btn layui-btn-xs layui-btn-danger" id="finishProblem">
                                                                结束问题
                                                            </button>
                                                        @endif

                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                                <div class="layui-tab-item">
                                    <table class="layui-table textmid" lay-skin="line">
                                        <thead>
                                        <tr>
                                            <th>异常名称</th>
                                            <th>详细描述</th>
                                            <th>添加人</th>
                                            <th>处理人</th>
                                            <th>创建时间</th>
                                            @if(isset($data ['finishedProblems']))
                                            @if(in_array(4,array_column($data ['finishedProblems'],'trouble_type_id')))
                                                <th>操作</th>
                                            @endif
                                            @endif
                                        </tr>
                                        </thead>
                                        <tbody id="finishedProblems">
                                        @if(isset($data ['finishedProblems']))
                                            @foreach ($data ['finishedProblems'] as $finishedProblems)
                                                <tr>
                                                    <td>{{$finishedProblems['trouble_name']}}</td>
                                                    @if($finishedProblems['trouble_type_id'] == 4)
                                                        <td>{{$finishedProblems['trouble_desc']}}</td>
                                                    @else
                                                        <td>{{$finishedProblems['trouble_desc']}}</td>
                                                    @endif
                                                    <td>系统</td>
                                                    <td>{{$finishedProblems['manage'] ? $finishedProblems['manage']['username'] : ''}}</td>
                                                    <td>{{$finishedProblems['created_at']}}</td>
                                                    @if(($finishedProblems['trouble_type_id'] == 4) && ($orderInfo ['status'] == 1))
                                                        <td>
                                                            <button onclick="event.preventDefault()" data-id="" class="layui-btn layui-btn-xs layui-btn-danger" id="remove_merge">
                                                                取消合并
                                                            </button>
                                                        </td>
                                                        {{--<td>
                                                            <button onclick="event.preventDefault()" class="layui-btn layui-btn-xs layui-btn-danger" id="checkMerge">
                                                                查看
                                                            </button>
                                                        </td>--}}
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--订单收货地址 未配货情况下才允许编辑地址-->
                    <div class="produpage layui-form">
                        <h3>地址信息</h3>
                        <table class="layui-table seledis" lay-skin="nob">
                            <tbody>
                            <tr>
                                <td><b><label style="color: red">*</label>收件人：</b>
                                    @if($orderInfo['picking_status'] == 1)
                                        <input type="text" value="{{$orderInfo['addressee_name']??''}}" name="addressee_name">
                                    @else
                                        {{$orderInfo['addressee_name']??''}}
                                    @endif
                                </td>
                                <td><b><label style="color: red">*</label>地址1：</b>
                                    @if($orderInfo['picking_status'] == 1)
                                        <input type="text" value="{{$orderInfo['addressee']??''}}" name="addressee">
                                    @else
                                        {{$orderInfo['addressee']??''}}
                                    @endif
                                </td>
                                <td><b>仓库：</b>
                                    @if($orderInfo['picking_status'] == 1)
                                        <select name="warehouse_id" id="warehouse_id" lay-filter="warehouse_select">
                                            <option value="0">请选择</option>
                                            @foreach($data ['warehouses'] as $warehouse)
                                                <option value="{{$warehouse['id']}}"
                                                        @if ($warehouse['id'] == $orderInfo ['warehouse_id'])
                                                        selected
                                                        @endif
                                                >{{$warehouse ['warehouse_name']}}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        {{$orderInfo['warehouse']}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><b>买家email：</b>
                                    @if($orderInfo['picking_status'] == 1)
                                        <input type="email" value="{{$orderInfo['addressee_email']??''}}" name="addressee_email">
                                    @else
                                        {{$orderInfo['addressee_email']??''}}
                                    @endif
                                </td>
                                <td><b>地址2：</b>
                                    @if($orderInfo['picking_status'] == 1)
                                        <input type="text" name="addressee1" value="{{$orderInfo['addressee1']??''}}">
                                    @else
                                        {{$orderInfo['addressee1']??''}}
                                    @endif
                                </td>
                                <td><b>是否指定仓库：</b>
                                    <input type="radio" name="warehouse_choose_status"  lay-skin="primary"
                                           title="是" value="1"
                                           @if ($orderInfo['picking_status'] != 1)
                                           disabled
                                           @endif
                                           @if ($orderInfo ['warehouse_choose_status'] == 1)
                                           checked
                                            @endif
                                    >
                                    <input type="radio" name="warehouse_choose_status"  title="否" lay-skin="primary"  value="0"
                                           @if ($orderInfo['picking_status'] != 1)
                                           disabled
                                           @endif
                                           @if ($orderInfo ['warehouse_choose_status'] == 0)
                                           checked
                                            @endif
                                    >
                                </td>
                            </tr>
                            <tr>
                                <td><b><label style="color: red">*</label>国家：</b>
                                    @if($orderInfo['picking_status'] == 1)
                                        <select name="country_id" >
                                            <option value="0">请选择</option>
                                            @foreach($data ['countrys'] as $country)
                                                <option value="{{$country['id']}}"
                                                        @if ($country['id'] == $orderInfo ['country_id'])
                                                        selected
                                                        @endif
                                                >{{$country ['country_name']}}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        {{$data['orderCountry']??''}}
                                    @endif
                                </td>
                                <td><b>手机：</b>
                                    @if($orderInfo['picking_status'] == 1)
                                        <input type="text" name="phone" value="{{$orderInfo['phone']??''}}">
                                    @else
                                        {{$orderInfo['phone']??''}}
                                    @endif
                                </td>
                                <td><b>物流方式：</b>
                                    @if($orderInfo['picking_status'] == 1)
                                        <select name="logistics_id" id="unselectedLogistics" >
                                            <option value="0">请选择</option>
                                            @foreach($data ['logistics'] as $logistic)
                                                <option value="{{$logistic['id']}}"
                                                        @if ($logistic['id'] == $orderInfo ['logistics_id'])
                                                        selected
                                                        @endif
                                                >{{$logistic ['logistic_name']}}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        {{$orderInfo['logistics']}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><b><label style="color: red">*</label>城市：</b>
                                    @if($orderInfo['picking_status'] == 1)
                                        <input type="text" name="city" value="{{$orderInfo['city']??''}}">
                                    @else
                                        {{$orderInfo['city']??''}}
                                    @endif
                                </td>
                                <td><b><label style="color: red">*</label>电话：</b>
                                    @if($orderInfo['picking_status'] == 1)
                                        <input type="text" name="mobile_phone" value="{{$orderInfo['mobile_phone']??''}}">
                                    @else
                                        {{$orderInfo['mobile_phone']??''}}
                                    @endif
                                </td>
                                <td><b>是否指定物流：</b>
                                    <input type="radio" name="logistics_choose_status"  lay-skin="primary"
                                           title="是" value="1"
                                           @if ($orderInfo['picking_status'] != 1)
                                           disabled
                                           @endif
                                           @if ($orderInfo ['logistics_choose_status'] == 1)
                                           checked
                                            @endif
                                    >
                                    <input type="radio" name="logistics_choose_status"  title="否" lay-skin="primary"  value="0"
                                           @if ($orderInfo['picking_status'] != 1)
                                           disabled
                                           @endif
                                           @if ($orderInfo ['logistics_choose_status'] == 0)
                                           checked
                                            @endif
                                    >
                                </td>
                            </tr>
                            <tr>
                                <td><b><label style="color: red">*</label>州/省：</b>
                                    @if($orderInfo['picking_status'] == 1)
                                        <input type="text" name="province" value="{{$orderInfo['province']??''}}">
                                    @else
                                        {{$orderInfo['province']??''}}
                                    @endif
                                </td>
                                <td><b><label style="color: red">*</label>邮编：</b>
                                    @if($orderInfo['picking_status'] == 1)
                                        <input type="text" name="postal_code" value="{{$orderInfo['postal_code']??''}}">
                                    @else
                                        {{$orderInfo['postal_code']??''}}
                                    @endif
                                </td>
                                <td><b>派送运费：</b>
                                    {{$orderInfo['invoices_freight']??''}}{{$orderInfo['invoices_freight_currency_code']??''}}
                                    <input type="hidden" name="invoices_freight" value="{{$orderInfo['invoices_freight']??''}}">
                                    <input type="hidden" name="invoices_freight_currency_code" value="{{$orderInfo['invoices_freight_currency_code']??''}}">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="produpage  layui-form">
                        <div class="layui-tab">
                            <ul class="layui-tab-title">
                                <li class="layui-this">配货单</li>
                                <li>售后单</li>
                                <li>付款单</li>
                                <li>日志</li>
                            </ul>
                            <div class="layui-tab-content">
                                <!--配货单信息-->
                                <div class="layui-tab-item layui-show">
                                    <table class="layui-table textmid" lay-skin="line">
                                        <thead>
                                        <tr>
                                            <th>配货单号</th>
                                            <th>币种</th>
                                            <th>创建时间</th>
                                            <th>状态</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($data ['invoices'] as $invoices)
                                            <tr>
                                                <td>{{$invoices['invoices_number']}}</td>
                                                <td>{{$invoices['currency_code']}}</td>
                                                <td>{{$invoices['created_at']}}</td>
                                                <td>@if($invoices['sync_status'] == 1)
                                                        未同步
                                                    @elseif ($invoices['sync_status'] == 2)
                                                        已同步
                                                    @else
                                                        同步失败
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <!--售后单信息-->
                                <div class="layui-tab-item">
                                    <table class="layui-table textmid" lay-skin="line">
                                        <thead>
                                        <tr>
                                            <th>售后单号</th>
                                            <th>售后类型</th>
                                            <th>创建时间</th>
                                            <th>状态</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($orderInfo ['orders_after_sales'] as $orders_after_sales)
                                            <tr>
                                                <td>{{$orders_after_sales['after_sale_code']}}</td>
                                                <td>@if($orders_after_sales['type'] == 1)
                                                        退货
                                                    @elseif($orders_after_sales['type'] == 2)
                                                        换货
                                                    @else
                                                        退款
                                                    @endif
                                                </td>
                                                <td>{{$orders_after_sales['created_at']}}</td>
                                                <td>@if($orders_after_sales['type'] == 1)
                                                        @if($orders_after_sales ['sales_return_status'] == 1)
                                                            未退回
                                                        @elseif($orders_after_sales['sales_return_status'] == 2)
                                                            已退回
                                                        @else
                                                            卖家确认收货
                                                        @endif
                                                    @elseif($orders_after_sales['type'] == 2)
                                                        @if($orders_after_sales ['again_deliver_status'] == 1)
                                                            未发货
                                                        @else
                                                            已发货
                                                        @endif
                                                    @else
                                                    @endif</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <!--付款单信息-->
                                <div class="layui-tab-item">
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
                                        @foreach($orderInfo ['orders_bill_payments'] as $orders_bill_payments)
                                            <tr>
                                                <td>{{$orders_bill_payments ['bill_code'] or ''}}</td>
                                                <td>{{$orders_bill_payments ['amount'] or ''}}</td>
                                                <td>{{$orders_bill_payments ['currency_code'] or ''}}</td>
                                                <td>{{$orders_bill_payments ['created_at'] or ''}}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <!--订单日志-->
                                <div class="layui-tab-item">
                                    <table class="layui-table textmid" lay-skin="line">
                                        <thead>
                                        <tr>
                                            <th width="100">操作</th>
                                            <th>描述</th>
                                            <th width="100">创建人</th>
                                            <th width="200">创建时间</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($data ['logs'] as $logs)
                                            <tr>
                                                <td>{{$logs['behavior_type_desc']}}</td>
                                                <td>{!! $logs['behavior_desc'] !!}</td>
                                                <td>{{$logs['users'] ? $logs['users'] ['username']: '系统' }}</td>
                                                <td>{{$logs['created_at']}}</td>
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
            <!-- 拦截订单 -->
            <div class="productext" id="lanjietext">
                <div class="produpage">
                    <textarea name="intercept_reason" placeholder="请填写拦截原因" class="layui-textarea"></textarea>
                </div>
            </div>
            <!-- 取消订单 -->
            <div class="productext" id="cancelOrderBox">
                <div class="produpage">
                    <textarea name="cancel_reason" placeholder="请填写取消原因" class="layui-textarea"></textarea>
                    <div class="layui-inline" style="margin-top: 15px;">
                        {{--<label class="layui-form-label">退款金额</label>--}}
                        <div class="layui-input-inline" style="width: 60px;">
                            <p>退款金额</p>
                        </div>
                        <div class="layui-input-inline" style="width: 100px;">
                            <input type="number" name="" autocomplete="off" class="layui-input"
                                   onkeyup="limitedNumberInput(this)"
                                   onblur="limitedNumberInput(this)"
                                   {{--onafterpaste="this.value=this.value.replace(/[^\d]/g,'')"--}}
                                   value="{{ (isset($orderInfo['orders_bill_payments'][0]) && $orderInfo['orders_bill_payments'][0]['amount'])? round($orderInfo['orders_bill_payments'][0]['amount'],2):'' }}">
                        </div>
                        <div class="layui-input-inline" style="width: 100px;">
                            <p>{{ $orderInfo['currency_code'] or '' }}</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- 部分退款 -->
            <div class="productext" id="tuikuantext">
                <div class="produpage">
                    <form action="" class="layui-form">
                        <input type="hidden" name="order_id" value="{{ $orderInfo['id'] ?? '' }}">
                        <table class="layui-table layui-form textmid" lay-skin="nob">
                            <thead>
                            <tr>
                                <th>商品图片</th>
                                <th>SKU</th>
                                <th>单价（本币 ）</th>
                                <th>币种</th>
                                <th>购买数量</th>
                                <th>已发货数量</th>
                                <th>部分退款数量</th>
                            </tr>
                            </thead>
                            <tbody class="goods">

                            @foreach ($data ['goods'] as $goodsKey => $goods)
                                <tr>
                                    <td>
                                        <input type="hidden" name="goods[id][]" value="{{$goods['id']}}">
                                        @if (isset($goods ['goods'] ['goods_pictures']))
                                            <img onerror="this.src='/img/imgNotFound.jpg'" src="{{url('showImage').'?path=' . $goods ['goods'] ['goods_pictures']}}" style="max-width: 24px; max-height: 24px;cursor:zoom-in;"  onclick="check_img(this.src)"/>
                                        @else
                                            <img onerror="this.src='/img/imgNotFound.jpg'" src="" style="max-width: 24px; max-height: 24px;" />
                                        @endif
                                    </td>
                                    <td><input type="hidden" name="goods[sku][]"
                                               value="{{$goods['goods']['sku']}}">{{$goods['goods']['sku']}}</td>
                                    <td class="rmb">{{$goods['univalence']}}</td>
                                    <td>{{$goods['currency']}}</td>
                                    <td>{{$goods['buy_number']}}</td>
                                    <td>{{$goods['delivery_number']}}</td>
                                    <td class="partial_refund_number"><input style="width: 50px;text-align: center"
                                                                             name="goods[partial_refund_number][]"
                                                                             type="number" min="0"
                                                                             value="{{$goods['buy_number']-$goods['delivery_number']}}"
                                                                             max="{{$goods['buy_number']-$goods['delivery_number']}}">
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        <div class="layui-form allre">
                            <span>退款金额：</span>
                            <input type="number" name="refundAmount" min="0"
                                   onkeyup="limitedNumberInput(this)"
                                   onblur="limitedNumberInput(this)"
                            >
                            <div class="layui-input-inline" style="width: 100px;">
                                <p>{{ $orderInfo['currency_code'] or '' }}</p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{--合并订单--}}
            <form action=""  class="layui-form" id="merge_form" >
                {{csrf_field()}}
                <div class="kbmodel_full">
                    <div class="content-wrapper">
                        <div class="productext " id="mergeOrderDetail">
                            <div class="produpage">
                                <table class="layui-table checkbox_table" id="orderMerge" lay-even="" lay-skin="nob">
                                    <thead>
                                    <tr>
                                        <th><input type="checkbox" lay-skin="primary" lay-filter="allChoose" class="checkboxAll" ></th>
                                        <th>订单号</th>
                                        <th>电商单号</th>
                                        <th>来源平台</th>
                                        <th>来源店铺</th>
                                        <th>计费重</th>
                                        <th>订单总金额</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(!empty($orderInfo['merge_order']))
                                        @foreach($orderInfo['merge_order'] as $k => $v)
                                            <tr class="oneChoosePr">
                                                <td><input type="checkbox" name="" class="checkOne check_zero" lay-filter="oneChoose" lay-skin="primary" /></td>
                                                <td><input type="hidden" class="check_number" value="{{$v['order_number']}}" name="order_number">{{$v['order_number']}}</td>
                                                <td><input type="hidden" class="check_ids" value="{{$v['id']}}" name="id">{{$v['plat_order_number']}}</td>
                                                <td >{{$v['platform_name'] ?? ''}}</td>
                                                <td>{{$v['source_shop_name'] ?? ''}}</td>
                                                <td >{{$v['weight'] ?? ''}}</td>
                                                <td >{{$v['order_price'] ?? ''}}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

        </div>
    </div>
@endsection

@section('javascripts')
    <script>

        function limitedNumberInput(obj){
            obj.value = obj.value.replace(/[^\d.]/g,""); //清除"数字"和"."以外的字符
            obj.value = obj.value.replace(/^\./g,""); //验证第一个字符是数字
            // obj.value = obj.value.replace(/\.{2,}/g,"."); //只保留第一个, 清除多余的
            obj.value = obj.value.replace(".","$#$").replace(/\./g,"").replace("$#$",".");
            obj.value = obj.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3'); //只能输入两个小数
        };

        layui.use(['form', 'laydate', 'table', 'element', 'upload', 'laypage', 'layer'], function () {
            var laypage = layui.laypage,
                layer = layui.layer,
                form = layui.form;
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
            let productList = {};
            let addedProduct = [];
            @foreach ($data ['goods'] as $goodsKey => $goods)
            addedProduct.push("{{$goods['goods']['sku']}}")
            productList["{{$goods['goods']['sku']}}"] = {'price': "{{$goods['univalence']}}", 'quantity': "{{$goods['buy_number']}}"};
            @endforeach

            let pickedProduct = {};
            @foreach ($data ['goods_already_picked'] as $pickedKey => $pickedVal)
            pickedProduct["{{$pickedKey}}"] = {'already_picked_quantity': "{{$pickedVal}}"};
            @endforeach


            function arrDeleteVal (arr,val) {
                var arr = arr;
                var index = arr.indexOf(val);
                if (index !== -1) {
                    arr.splice(index, 1);
                }
            }

            $(document).on('click', '.origOrderInfo', function () {
                var order_number = "{{$orderInfo["order_number"]}}";
                layer.open({
                    type: 2,
                    title: order_number + ' 原始订单详情',
                    fix: false,
                    maxmin: true,
                    shadeClose: true,
                    area: ['1500px', '760px'],
                    content: '{{ url('order/originalOrderDetail') }}' + '?order_number=' + order_number,
                    end: function (index) {
                        layer.close(index);
                    }
                });
            });


            //商品删除
            $(document).on('click', '.goodsDel', function () {
                var pickGoods = [];
                @if ($orderInfo['picking_status'] == 3)
                @foreach ($data ['invoices_already'] as $invoices_already)
                pickGoods.push("{{$invoices_already}}");
                @endforeach
                @endif
                var elect = $(this).parents('tr');
                var sku = elect.find('input[name="orderGoodsSKU[]"]').val();
                if (pickGoods.indexOf(sku) != -1) {
                    layer.msg("SKU为：" + sku + "的商品已配货,请勿删除");
                    return false;
                }
                let freight = $('input[name="freight"]').val();
                if (addedProduct.length == 1) {
                    layer.msg("最后一条商品信息,请勿删除");
                    return false;
                }
                layer.confirm('确认删除所选项？', {
                    btn: ['确定', '取消'],
                    yes: function (index) {
                        elect.remove();
                        delete productList[sku]
                        arrDeleteVal(addedProduct,sku)
                        var orderAmount = 0.00;
                        $.each(productList, function (k, v) {
                            let itemAmount = v.price * v.quantity;
                            orderAmount += itemAmount;
                        });
                        var totalAmount =  parseFloat(orderAmount) + parseFloat(freight);
                        $(".amount-of-goods").val(Number(orderAmount).toFixed(2));
                        $(".amount-of-order").val(Number(totalAmount).toFixed(2));
                        layer.close(index);
                        console.log(addedProduct);

                    }
                })
            });


            //添加sku
            $(".search-product").click(function () {
                let sku = $(".sku_search").val().trim();
                let platform = "{{$orderInfo['platforms_id']}}";
                let source_shop = "{{$orderInfo['source_shop']}}";
                var currency_code = "{{$orderInfo['currency_code']}}";
                if (addedProduct.indexOf(sku) != -1) {
                    layer.msg("SKU为：" + sku + "的商品已经存在，如需变更数量，请手动变更");
                    return false;
                }
                var length = addedProduct.length + 1;
                $.ajax({
                    type: "GET",
                    url: "/order/searchProductBySku",
                    data: {"sku" : sku, "platform": platform},
                    success: function (e) {
                        if (e.code != 0) {
                            layer.msg(e.msg);
                            return false;
                        }
                        var img_html = '';
                        if (e.data.goods_pictures) {
                            img_html = '<img onerror="this.src=\'/img/imgNotFound.jpg\'" src="'+"{{url('showImage')}}"+'?path='+e.data.goods_pictures +'" style="max-width: 24px; max-height: 24px;cursor:zoom-in;" onclick="check_img(this.src)"'
                        } else {
                            img_html = '<img src="" alt="">';
                        }
                        let newRowElement = ' <tr>\n' +
                            '<td>\n' +
                            length
                            +'<input name="orderGoodsId[]" hidden value="'+e.data.id+'">'
                            +'<input name="orderGoodsSKU[]" hidden value="'+e.data.sku+'">'
                            +
                            '</td>\n' +
                            '<td>\n' +
                            img_html+'</td>\n' +
                            '<td>'+ e.data.sku +'<input type="hidden" value="'+ e.data.sku +'" name="sku[]" class="sku-value"> </td>\n' +
                            '<td>'+ e.data.goods_name +'</td>\n' +
                            '<td><input type="number" name="goods_nums[]" value="1" min="1" class="good-quantity" /></td>\n' +
                            '<td><input type="text" name="goods_price[]" value="" min="0" onkeyup="value=value.match(/\\d+\\.?\\d{0,2}/,\'\')" class="edit-price" placeholder="0.00"></td>\n' +
                            '<td>\n' +
                            currency_code +
                            '</td>' +
                            '<td>\n' +
                            e.data.goods_length+'*'+e.data.goods_width+'*'+e.data.goods_height+
                            '</td>' +
                            '<td>\n' +
                            e.data.goods_weight+
                            '</td>'+
                            '<td>\n' +
                            '<button type="button" class="layui-btn layui-btn-xs layui-btn-danger goodsDel " > 删除 </button>'+
                            '</td>'+
                            '</tr>';
                        $("#bottom-line").before(newRowElement);
                        form.render('select');
                        addedProduct.push(sku)
                        productList[sku] =  {'price': 0, 'quantity': 1};
                        layer.msg("添加成功");
                    },
                });
                return false;
            });

            //商品表单change事件
            $(".add-product").change(function (e) {
                //客户运费表单change事件
                if ($(e.target).attr('name') == 'freight') {
                    let orderAmountElement = $(".amount-of-order");
                    let productAmount = orderAmountElement.val();
                    let freight = $(e.target).val().trim();
                    if (!freight) {
                        freight = 0.00;
                    }
                    var old_freight = $('.old_freight').val();
                    let total = parseFloat(productAmount) + parseFloat(freight) - parseFloat(old_freight);
                    orderAmountElement.val(Number(total).toFixed(2));
                    $('.old_freight').val(freight);
                    return false;
                }
                let modifiedRow = $(e.target).parent().parent();
                let sku = modifiedRow.find('.sku-value').val();
                let price = modifiedRow.find('.edit-price').val();
                let quantity = modifiedRow.find('.good-quantity').val();

                var preg = /^[0-9]*$/;
                if (!preg.test(quantity)) {
                    layer.msg("SKU为：" + sku + "的商品数量必须为整数");
                    return false;
                }
                let freight = $('input[name="freight"]').val();
                if (freight == '') {
                    freight = 0.00;
                }

                if (!productList[sku]) {
                    productList[sku] = {}
                }
                //已配货商品校验
                if (pickedProduct[sku]) {
                    if (quantity <　pickedProduct[sku].already_picked_quantity　) {
                        modifiedRow.find('.good-quantity').val(pickedProduct[sku].already_picked_quantity);
                        layer.msg("SKU为：" + sku + "的商品数量不能小于已配货数量");
                        return false;
                    }
                }
                productList[sku].price = price;
                productList[sku].quantity = quantity;
                var orderAmount = 0.00;
                $.each(productList, function (k, v) {
                    let itemAmount = v.price * v.quantity;
                    orderAmount += itemAmount;
                });
                var totalAmount =  parseFloat(orderAmount) + parseFloat(freight);
                $(".amount-of-goods").val(Number(orderAmount).toFixed(2));
                $(".amount-of-order").val(Number(totalAmount).toFixed(2));
                return false;
            });

            //增加采购计划弹窗
            $(document).on('click', '#increaseplan', function () {
                layer.open({
                    type: 1,
                    title: '增加采购计划',
                    area: ['1080px', '600px'],
                    content: $('#increaseplantext'),
                    btn: ['审核', '保存', '取消'],
                    yes: function () {
                    }
                });
            });

            //增加采购单弹窗
            $(document).on('click', '#addlist', function () {
                layer.open({
                    type: 1,
                    title: '添加采购单',
                    area: ['800px', '500px'],
                    content: $('#addlisttext'),
                    btn: ['确定', '取消'],
                    yes: function () {
                    }
                });
            });

            //采购计划信息查看弹窗
            $(document).on('click', '#planlook', function () {
                layer.open({
                    type: 1,
                    title: '采购计划信息查看',
                    area: ['1080px', '600px'],
                    content: $('#planlooktext'),
                    btn: '取消',
                    yes: function () {
                        layer.closeAll();
                    }
                });
            });

            //采购计划信息编辑弹窗
            $(document).on('click', '#edit', function () {
                layer.open({
                    type: 1,
                    title: '编辑',
                    area: ['1080px', '600px'],
                    content: $('#edittext'),
                    btn: ['确定', '取消'],
                    yes: function () {

                    },
                    btn2: function () {
                        layer.closeAll();
                        layer.close('#planlooktext');
                    }
                });
            });

            //查看采购单弹窗
            $(document).on('click', '#looklist', function () {
                layer.open({
                    type: 1,
                    title: '查看采购单',
                    area: ['1080px', '600px'],
                    content: $('#looklisttext'),
                    btn: '取消',
                    yes: function () {
                        layer.closeAll();
                    }

                });
            });

            //订单——编辑订单弹窗
            $(document).on('click', '#bianjibtn', function () {
                layer.open({
                    type: 1,
                    title: '编辑订单',
                    area: ['1100px', '800px'],
                    content: $('#bianjitext'),
                    btn: ['确定', '取消'],
                    yes: function () {
                        layer.closeAll();
                    }

                });
            });
            //编辑订单_保存订单
            $(document).on('click', '#saveOrder', function () {
                var param = $('.layui-form').serialize();
                var exception = false;
                $.each(productList, function (k, v) {
                    if (parseFloat(v.quantity) <= 0 ) {
                        layer.msg("SKU为：" + k + "的商品数量异常");
                        exception = true;
                        return false;
                    }
                    if (parseFloat(v.price) <= 0 ) {
                        layer.msg("SKU为：" + k + "的商品价格异常");
                        exception = true;
                        return false;
                    }
                    //已配货商品校验
                    if (pickedProduct[k]) {
                        if (v.quantity <　pickedProduct[k].already_picked_quantity　) {
                            layer.msg("SKU为：" + k + "的商品数量不能小于已配货数量");
                            exception = true;
                            return false;
                        }
                    }
                });
                if (exception) {
                    return false
                }
                var address_status = "{{$orderInfo ['picking_status']}}" == 1;
                if (address_status) {
                    //必填项校验
                    if ($('.layui-form').find('input[name="addressee_name"]').val().trim() == '') {
                        layer.msg('收件人为必填项', {icon: 5});
                        return false
                    }

                    if ($('.layui-form').find('select[name="country_id"]').val() == '' || $('.layui-form').find('select[name="country_id"]').val() == '0') {
                        layer.msg('国家为必选项', {icon: 5});
                        return false
                    }

                    if ($('.layui-form').find('input[name="city"]').val().trim() == '') {
                        layer.msg('城市为必填项', {icon: 5});
                        return false
                    }

                    if ($('.layui-form').find('input[name="province"]').val().trim() == '') {
                        layer.msg('州/省为必填项', {icon: 5});
                        return false
                    }

                    if ($('.layui-form').find('input[name="addressee"]').val().trim() == '') {
                        layer.msg('地址1为必填项', {icon: 5});
                        return false
                    }

                    if ($('.layui-form').find('input[name="mobile_phone"]').val().trim() == '') {
                        layer.msg('电话为必填项', {icon: 5});
                        return false
                    }

                    if ($('.layui-form').find('input[name="postal_code"]').val().trim() == '') {
                        layer.msg('邮编为必填项', {icon: 5});
                        return false
                    }
                }
                //选择物流方式 仓库则为必填项
                var logistics_id = $("#unselectedLogistics option:selected").val();
                var warehouse_id = $("#warehouse_id option:selected").val();
                if (logistics_id > 0 ) {
                    if (warehouse_id == 0 || warehouse_id == '') {
                        layer.msg('已选择物流方式,请选择仓库', {icon: 5});
                        return false
                    }
                }
                var logistics_choose_status = $('input[name="logistics_choose_status"]:checked').val();
                var warehouse_choose_status = $('input[name="warehouse_choose_status"]:checked').val();

                if (logistics_choose_status == 1 && (logistics_id == 0 || logistics_id == '' )) {
                    layer.msg('已指定物流,请选择物流方式', {icon: 5});
                    return false
                }
                if (warehouse_choose_status == 1 && (warehouse_id == 0 || warehouse_id == '' )) {
                    layer.msg('已指定仓库,请选择仓库', {icon: 5});
                    return false
                }

                $.ajax({
                    type: "POST",
                    url: "/order/saveOrder"+'/'+"{{$orderInfo['id']}}",
                    data: param,
                    success: function(response) {
                        if (response.Status) {
                            layer.msg(response.Message, {time:2000, icon: 1});
                            setTimeout(function () {
                                location.reload();
                            }, 2000)
                        } else {
                            var data = response.Data;
                            var alertMessage = response.Message;
                            if (data != null) {
                                for (var i=0; i < data.length; i++) {
                                    alertMessage += data[i] + '<br/>'
                                }
                            }
                            layer.msg(alertMessage, {icon: 5});
                        }
                    },
                    error:function(){
                        layer.alert('操作失败！！！',{icon:5});
                    }
                });
            });

            form.on('select(warehouse_select)', function (data) {
                var shopOption;
                var warehouseId = data.value;
                var unselectOption = $("#unselectedLogistics option:first");

                $.get("/order/getLogistics", {warehouseId: warehouseId}, function (e) {
                    if (e.code != 0) {
                        layer.msg(e.msg)
                        return false;
                    }
                    $("#unselectedLogistics").empty();
                    $("#unselectedLogistics").append(unselectOption);
                    $.each(e.data, function (k, v) {
                        shopOption += '<option value="'+ v.id +'">'+ v.logistic_name +'</option>'
                    })
                    unselectOption.after(shopOption);
                    form.render('select');
                })
            });

            //编辑订单_拦截弹窗
            $(document).on('click', '#lanjie', function () {
                var that = $(this);
                layer.open({
                    type: 1,
                    title: '拦截订单',
                    area: ['400px', '300px'],
                    content: $('#lanjietext'),
                    btn: ['确定', '取消'],
                    yes: function (index, layero) {
                        var _reason = layero.find('.layui-textarea')[0].value;
                        if (!_reason) {
                            layer.msg('拦截原因必填项', {icon: 5});
                            return false;
                        }
                        //loading层
                        loading = layer.msg('拦截中...', {
                            icon: 16
                            , shade: 0.01
                        });
                        $.ajax({
                            type: 'put',
                            url: '{{ url('order/intercept') }}',
                            data: {intercept_reason: _reason, order_id: _order_id},
                            dataType: 'json',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (res) {
                                layer.close(layer.loading);
                                if (res.code == 1) {
                                    layer.msg(res.msg, {icon: 1});
                                    setTimeout(function () {
                                        location.reload();
                                    }, 2000)
                                } else {
                                    layer.msg(res.msg, {icon: 5});
                                }
                            }
                        });
                    }

                });
            });
            //编辑订单_取消弹窗
            $(document).on('click', '.cancelOrderBtn', function () {
                layer.open({
                    type: 1,
                    title: '取消订单',
                    area: ['400px', '300px'],
                    content: $('#cancelOrderBox'),
                    btn: ['确定', '取消'],
                    yes: function (index, layero) {
                        var _reason = layero.find('.layui-textarea')[0].value,
                            _money = layero.find('input[type="number"]')[0].value;
                        if (!_reason) {
                            layer.msg('取消原因必填项', {icon: 5});
                            return false;
                        }
                        var max_refund = "{{$orderInfo ['order_price']}}";
                        if (parseInt(_money) > parseInt(max_refund)) {
                            layer.msg('超出最大退款金额', {icon: 5});
                            return false;
                        }
                        $.ajax({
                            type: 'put',
                            url: '{{ url('order/cancelOrder') }}',
                            data: {cancel_reason: _reason, money: _money, order_id: _order_id},
                            dataType: 'json',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (res) {
                                if (res.code == 1) {
                                    layer.msg(res.msg, {icon: 1});
                                    setTimeout(function () {
                                        location.reload();
                                    }, 2000)
                                } else {
                                    layer.msg(res.msg, {icon: 5});
                                }
                            }
                        });
                    }

                });
            });
            //编辑订单_部分退款弹窗
            $(document).on('click', '#tuikuan', function () {
                layer.open({
                    type: 1,
                    title: '部分退款',
                    area: ['1080px', '600px'],
                    content: $('#tuikuantext'),
                    btn: ['确定', '取消'],
                    yes: function (index, layero) {
                        var _data = layero.find('form').serializeArray(),price = layero.find('input[name="refundAmount"]').val();
                        if(price<0||price == ''||price==0){
                           layer.msg("退款金额必填", {icon: 5});
                           return false;
                       }
                        $.ajax({
                            type: 'put',
                            url: '{{ url('order/partialRefund') }}',
                            data: _data,
                            dataType: 'json',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (res) {
                                if (res.code == 1) {
                                    layer.msg(res.msg, {icon: 1});
                                    setTimeout(function () {
                                        location.reload();
                                    }, 2000)
                                } else {
                                    layer.msg(res.msg, {icon: 5});
                                }
                            }
                        });
                    }
                });
            });

            //结束问题
            $(document).on('click', '#finishProblem', function (e) {
                var problemId = $(this).parent('td').find('input').val();
                $.ajax({
                    url:"/{{'order/finishProblem'}}"+'/'+"{{$orderInfo['id']}}"+'?problem_id='+problemId,
                    success: function(response) {
                        if (response.Status) {
                            layer.msg(response.Message, {time:2000, icon: 1},function () {
                                $(e.target).parent('td').parent('tr').remove();
                            });
                            location.reload();
                        } else {
                            var data = response.Data;
                            var alertMessage = response.Message;
                            if (data != null) {
                                for (var i=0; i < data.length; i++) {
                                    alertMessage += data[i] + '<br/>'
                                }
                            }
                            layer.msg(alertMessage, {icon: 5});
                        }
                    },
                    error:function(){
                        layer.alert('操作失败！！！',{icon:5});
                    }
                });
            });
            //无需合并
            $(document).on('click','#cancelProblem',function(){
                $.ajax({
                    type: 'put',
                    url: '{{ url('order/cancelOrderMerge') }}',
                    data: {
                        cancel_order_id:{{$orderInfo['id']}},
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        if(res.msg == 'success'){
                            layer.msg('取消成功');
                            location.reload();
                            return false;
                        }
                        layer.msg('取消失败');
                    }
                });
            });
            //创建售后单
            $(document).on('click','#shouhou',function() {
                $.get('/order/afterSaleOrder/getOrderNumber', {order_num: '{{$orderInfo['order_number']}}'}, function (e) {
                    if(!e.code) {
                        layer.msg(e.msg);
                        return;
                    } else {
                        //存在打开售后单的产品页面
                        layer.open({
                            title:"添加",
                            type: 2,
                            area:["1200px","600px"],
                            content: '/order/afterSales/createPaymentOrder?order_num='+'{{$orderInfo['order_number']}}',
                            btn1: function(index, layero){
                                var info = window["layui-layer-iframe" + index].callbackdata();
                            }
                        });
                    }
                });
            });

            //合并订单
            $(document).on('click', '#mergeOrder', function () {
                layer.open({
                    type: 1,
                    title: '合并订单',
                    area: ['1080px', '600px'],
                    content: $('#mergeOrderDetail'),
                    btn: ['确定', '取消'],
                    yes: function (index) {
                        var problem_id = $('#mergeOrder').attr('data-id');
                        var order_ids = new Array();
                        var order_numbers = '';//已处理订单描述
                        var checked_box = $('.checkbox_table').find('.check_zero:checked');

                        $.each(checked_box,function(){
                            var td_one = $(this).parents().parents('.oneChoosePr').children('td').eq(2);
                            var td_num = $(this).parents().parents('.oneChoosePr').children('td').eq(1);
                            var id = td_one.children('.check_ids').val();
                            var number = td_num.children('.check_number').val();
                            order_ids.push(id);
                            order_numbers += number + ',';
                        });

                        if(order_ids.length <= 1 ){
                            layer.msg('至少选择两条订单');
                            return false;
                        }

                        $.ajax({
                            type: 'put',
                            url: '{{ url('order/orderTroublesMerge') }}',
                            data: {
                                order_id: order_ids,
                                cur_order_id:{{$orderInfo['id']}},
                                problem_id:problem_id
                            },
                            dataType: 'json',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (res) {
                                if(res.msg == 'success'){
                                    var html = '<tr>'+
                                        '<td>'+res.data.problem.trouble_name+'</td>' +
                                        '<td>'+order_numbers+'合并成了'+res.data.number+'</td>' +
                                        '<td>'+'系统'+'</td>' +
                                        '<td>'+'系统'+'</td>' +
                                        '<td></td>' +
                                        '</tr>';
                                    $('#finishedProblems').append(html);
                                    $.each(checked_box,function(){
                                        $(this).attr('disabled',true);
                                        $(this).prop('checked',false);
                                    });
                                    $('.checkboxAll').attr('disabled',true);
                                    $('.checkboxAll').attr('checked',false);
                                    $('#cancelProblem').attr('disabled',true);
                                    $('#cancelProblem').attr('disabled',true);
                                    $('#cancelProblem').css({'background-color':'#ddd'});
                                    form.render();
                                    layer.close(index);
                                    layer.msg('合并成功');
                                    return false;
                                }
                                if(res.msg == 'error'){
                                    layer.msg('合并异常');
                                    return false;
                                }
                                layer.msg('合并失败');
                            }
                        });
                    }
                });
            });

            //取消合并
            $(document).on('click','#remove_merge',function(){
                var this_ = $(this);
                $.ajax({
                    type: 'put',
                    url: '{{ url('order/removeOrderMerge') }}',
                    data: {
                        cur_order_id:{{$orderInfo['id']}}
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (res) {
                        if(res.msg == 'success'){
                            layer.msg('取消成功',{icon:6});
                            this_.parent().parent().remove();
                            return false;
                        }
                        layer.msg('取消失败',{icon:5});
                    }
                });
            });

            //删除
            $('#BatchDel').click(function () {
                var elect = $(this).parents('tr');
                layer.confirm('确认删除所选项？', {
                    btn: ['确定', '取消'],
                    yes: function (index) {
                        elect.remove();
                        layer.close(index);
                    }
                })
            });
            //删除2
            function BatchDel () {
                var elect = $(this).parents('tr');
                layer.confirm('确认删除所选项？', {
                    btn: ['确定', '取消'],
                    yes: function (index) {
                        elect.remove();
                        layer.close(index);
                    }
                })
            }

            //合单 复选
            form.on('checkbox(allChoose)', function (data) {
                $(this).parents('.layui-form').find('.oneChoosePr').find(".checkOne").each(function () {
                    this.checked = data.elem.checked;
                });
                form.render('checkbox');
                var list = [];
                $(this).parents('.layui-form').find(".checkOne").each(function () {
                    var checked_val = this.value;
                    if(this.value == 'on'){
                        return true;
                    }
                    list.push(checked_val);
                });
            });
            //合单 单选
            form.on('checkbox(oneChoose)', function (data) {
                var i = 0;
                var j = 0;
                $(this).parents('.layui-form').find(".check_zero").each(function () {
                    if( this.checked === true ){
                        i++;
                    }
                    j++;
                });
                if( i == j ){
                    $(this).parents('.layui-form').find(".checkboxAll").prop("checked",true);
                    form.render('checkbox');
                }else{
                    $(this).parents('.layui-form').find(".checkboxAll").removeAttr("checked");
                    form.render('checkbox');
                }
                var list = [];
                $(this).parents('.oneChoosePr').find(".checkOne").each(function(){
                    this.checked = data.elem.checked;
                });
                form.render('checkbox');
            });
            var CalculatedRMB = function () {
                var totalRMB = 0;
                $("#tuikuantext .goods tr").each(function (index, element) {
                    var rmb = parseFloat($(this).find('.rmb').text()),
                        partial_refund_number = parseInt($(this).find('.partial_refund_number input').val());
                    if(isNaN(partial_refund_number)){
                        partial_refund_number = 0;
                    }
                    totalRMB += rmb * partial_refund_number
                });
                $('input[name="refundAmount"]').val(parseInt(totalRMB).toFixed(2));
            };

            CalculatedRMB();
            $(document).on('change', '.partial_refund_number input', function () {
                CalculatedRMB();
                if($(this).val() =='' || $(this).val()<0){
                    $(this).val(0);
                }
            });
            $(document).on('blur', '#tuikuantext input[name="refundAmount"]', function () {
                if($(this).val() =='' || $(this).val()<0){
                    $(this).val(0);
                }
            });
        });

    </script>
@endsection