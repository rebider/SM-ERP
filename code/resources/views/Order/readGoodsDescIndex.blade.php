@extends('layouts.new_main')
@section('content')
    <style>.textpage table tbody tr:last-child td{text-align: unset}.infortext [lay-skin="nob"] th{text-align: left !important}</style>
    <div class="infortext">
        <div class="textpage">
            <h3>订单信息</h3>
            <table class="layui-table" lay-even lay-skin="row">
                <tbody>
                <tr>
                    <td><b>订单号：</b>{{ $OrdersDesc['orders']['order_number'] or '' }} <a href="../orderDetails/{{ $OrdersDesc['orders']['id'] or '' }}" target="_blank" style="color: #1e9fff;">【查看系统订单】</a></td>
                    <td><b>来源平台：</b>{{ $OrdersDesc['orders']['platform_name'] or '无' }}</td>
                    <td><b>来源店铺：</b>{{ $OrdersDesc['orders']['source_shop_name'] or '无' }}</td>
                </tr>
                </tbody>
            </table>
        </div>
        <form action="" class="layui-form">
            {{csrf_field()}}
            <input type="hidden" name="order_id" value="{{ $OrdersDesc['orders']['id'] or '' }}">
            <div class="textpage layui-form">
                <h3>产品信息</h3>
                <table class="layui-table chanp" lay-skin="line">
                    <colgroup>
                    </colgroup>
                    <thead>
                    <tr>
                        <th align="center">序号</th>
                        <th align="center">商品图片</th>
                        <th align="center">产品编号</th>
                        <th align="center">产品名称</th>
                        <th align="center">购买数量</th>
                        <th align="center">配货数量</th>
                    </tr>
                    </thead>

                    <tbody class="goods-lists">
                    @if(!empty($OrdersDesc['orders_invoices_product']))
                        @foreach($OrdersDesc['orders_invoices_product'] as $k=> $item)

                            <tr>
                                {{--序号 --}}
                                <td><input type="hidden" name="goods[id][]" value="{{$item['id']}}">  {{$k+1}}
                                </td>
                                {{--商品图片--}}
                                <td>
                                    @if(isset($item ['goods'] ['goods_pictures']))
                                        <img onerror="this.src='/img/imgNotFound.jpg'"  src="{{url('showImage').'?path=' . $item ['goods'] ['goods_pictures']}}" style="max-width: 24px; max-height: 24px;cursor:zoom-in;" @if ($item['goods'] ['goods_pictures']) onclick="check_img(this.src)"@endif/>
                                    @endif
                                </td>
                                {{--产品编号--}}
                                <td><input type="hidden" name="goods[sku][]" value="{{$item['goods']['sku']}}">{{$item['goods']['sku']}}
                                </td>
                                {{--产品名称--}}
                                <td>{{$item['goods']['goods_name']}}</td>
                                {{--购买数量--}}
                                <td class="buy_number_td"><input class="buy_number" type="hidden"
                                                                 name="goods[buy_number][]"
                                                                 value="{{$item['buy_number']}}">{{$item['buy_number'] or 0}}
                                </td>
                                {{--已配货数量--}}
                                <td class="already_stocked_number_td"><input class="already_stocked_number"
                                                                             type="hidden"
                                                                             name="goods[already_stocked_number][]"
                                                                             value="{{$item['already_stocked_number']}}">{{$item['already_stocked_number'] or 0}}
                                </td>
                                <td style="display: none;" class="weight">{{$item['weight']}}</td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>

                <div class="textpage layui-form">
                    <h3>配货信息</h3>
                    <div class="layui-form-item xiala">
                        <label class="layui-form-label">发货仓库</label>
                            <div class="layui-input-block" style="line-height: 34px;">
                                {{$OrdersDesc['setting_warehouse']['warehouse_name'] or ''}}
                            </div>
                    </div>

                    <table class="layui-table" lay-skin="nob">
                        <colgroup>
                            <col width="80">
                            <col>
                            <col>
                        </colgroup>
                        <thead>
                        <thead>
                        <tr>
                            <th></th>
                            <th>物流方式</th>
                            <th>预估运费</th>
                        </tr>
                        </thead>
                        </thead>
                        <tbody>
                            <tr>
                                <td></td>
                                <td>{{$OrdersDesc['setting_logistics']['logistic_name'] or ''}}</td>
                                <td>{{$OrdersDesc['taotla_value'] ? $OrdersDesc['taotla_value'].'RMB':'无' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <div class="layui-form-item layui-hide">
                <input type="button" lay-submit lay-filter="LAY-front-submit" id="LAY-front-submit" value="确认">
            </div>
        </form>
    </div>


@endsection

@section('javascripts')
    <script>
        layui.use(['layer', 'form', 'element', 'table'], function () {
            var layer = layui.layer, form = layui.form, element = layui.element;

        });

        $("body").bind("keydown", function (event) {
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location = location;
            }
        });
    </script>
@endsection