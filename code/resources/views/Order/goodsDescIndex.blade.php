@extends('layouts.new_main')
@section('content')
    <style>
        .textpage .Logistic tr td, .textpage table tbody tr:last-child td {
            text-align: unset
        }

        .textpage table thead tr th {
            text-align: left;
        }

        .tright {
            text-align: right !important;
        }

        .textpage table tbody tr:last-child td span {
            margin: unset !important;
        }
    </style>
    <div class="infortext">
        <div class="textpage">
            <h3>订单信息</h3>
            <table class="layui-table" lay-even lay-skin="row">
                <tbody>
                <tr>
                    <td><b>订单号：</b>{{ $OrdersDesc['order_number'] or '' }} <a
                                href="../orderDetails/{{ $OrdersDesc['id'] or '' }}" target="_blank"
                                style="color: #1e9fff;">【查看系统订单】</a>
                    </td>
                    <td><b>来源平台：</b>{{ $OrdersDesc['platforms']['name_EN'] or '无' }}</td>
                    <td><b>来源店铺：</b>{{ $OrdersDesc['shops']['shop_name'] or '无' }}</td>
                </tr>
                </tbody>
            </table>
        </div>
        <form action="" class="layui-form">
            {{csrf_field()}}
            <input type="hidden" name="order_id" value="{{ $OrdersDesc['id'] or '' }}">
            <div class="textpage layui-form">
                <h3>产品信息</h3>
                <table class="layui-table chanp" lay-skin="line">
                    <colgroup>
                    </colgroup>
                    <thead>
                    <tr>
                        <th align="center" width="6%">序号</th>
                        <th align="center" width="19.5%">商品图片</th>
                        <th align="center" width="18.5%">产品编号</th>
                        <th align="center" width="18.5%">产品名称</th>
                        <th align="center" width="10%">购买数量</th>
                        <th align="center" width="10%">已配货数量</th>
                        <th align="center" width="10%">配货</th>
                        <th align="center" width="10%">可配货数量</th>
                    </tr>
                    </thead>

                    <tbody class="goods-lists" data-type="{{ $OrdersDesc['warehouse_type'] or '' }}">
                    @if(!empty($OrdersDesc['orders_products']))
                        @foreach($OrdersDesc['orders_products'] as $k=> $item)

                            <tr>
                                {{--序号 --}}
                                <td><input type="hidden" name="goods[id][]" value="{{$item['id']}}"> {{$k+1}}
                                </td>
                                {{--商品图片--}}
                                <td>
                                    @if(isset($item ['goods'] ['goods_pictures']))
                                        <img  onerror="this.src='/img/imgNotFound.jpg'" src="{{url('showImage').'?path=' . $item ['goods'] ['goods_pictures']}}" style="max-width: 24px; max-height: 24px;cursor:zoom-in;" @if ($item['goods'] ['goods_pictures']) onclick="check_img(this.src)"@endif/>
                                     @endif
                                </td>
                                {{--产品编号--}}
                                <td><input type="hidden" name="goods[sku][]"
                                           value="{{$item['goods']['sku']}}">{{$item['goods']['sku']}}
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


                                @if($item['buy_number'] == $item['already_stocked_number'])
                                    {{--配货--}}
                                    <td class="dispensable_number_td"><input type="number"  onkeyup="this.value=this.value.replace(/[^0-9]/g,'')"
                                                                             name="goods[dispensable_number][]"
                                                                             value="0"
                                                                             class="nub dispensable_number"
                                                                             lay-skin="switch" disabled/></td>
                                @else
                                    {{--配货--}}
                                    <td class="dispensable_number_td"><input type="number"  onkeyup="this.value=this.value.replace(/[^0-9]/g,'')"
                                                                             name="goods[dispensable_number][]"
                                                                             value=""
                                                                             class="nub dispensable_number"
                                                                             autocomplete="off"
                                                                             min="0"
                                                                             max=""/></td>
                                @endif
                                {{--可配货数量--}}
                                <td class="cargo_distribution_td cargo_distribution{{$item['id']}}"><input
                                            class="cargo_distribution" type="hidden"
                                            name="goods[cargo_distribution_number][]"
                                            value="0"><span
                                            style="color: #0C0C0C"> 0</span>
                                </td>
                                <td style="display: none;" class="weight">{{$item['goods']['goods_weight']}}</td>

                            </tr>
                        @endforeach
                    @endif

                    <tr>
                        <td colspan="9" class="tright"><b>合计重量：<span
                                        class="total_weight">{{ isset($OrdersDesc['total_weight'])?$OrdersDesc['total_weight']: '0.00' }}</span>
                                KG</b>
                        </td>
                    </tr>

                    </tbody>
                </table>
            </div>
            <div class="textpage layui-form">
                <h3>配货信息</h3>
                <div class="layui-form-item xiala">
                    <label class="layui-form-label">发货仓库</label>
                    <div class="layui-input-block">
                        <select name="warehouse_id" lay-verify="required" id="warehouse" lay-filter="warehouse">
                            <option value="">请选择</option>

                            @if(!empty($Warehouse))
                                @foreach($Warehouse as $item_Warehouse)
                                    @if($item_Warehouse['id'] == $OrdersDesc['warehouse_id'])
                                        <option selected
                                                value="{{$item_Warehouse['id']}}" data-type="{{$item_Warehouse['type']}}">{{$item_Warehouse['warehouse_name']}}</option>
                                    @else
                                        <option value="{{$item_Warehouse['id']}}" data-type="{{$item_Warehouse['type']}}">{{$item_Warehouse['warehouse_name']}}</option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
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
                        <th>运费</th>
                    </tr>
                    </thead>
                    </thead>
                    <tbody class="Logistic">

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
            var type = 1;//类型
            //监听提交
            form.on('submit(LAY-front-submit)', function (data) {
                var field = data.field; //获取提交的字段
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                if(field.logistic_id === undefined){
                    layer.msg("物流信息必须的", {icon: 5});
                    return false;
                }
                //loading层
                loading = layer.msg('配货单创建中', {
                    icon: 16
                    , shade: 0.01
                });
                $.ajax({
                    type: 'patch',
                    url: '{{ route('order.generate_distribution_order.update') }}',
                    data: field,
                    dataType: 'json',
                    success: function (data) {
                        layer.close(layer.loading);
                        if (data.code == 1) {
                            parent.layer.msg(data.msg, {icon: 1});
                            parent.layer.close(index); //再执行关闭
                            window.parent.location.reload();
                        } else {
                            parent.layer.msg(data.msg, {icon: 5});
                        }
                    }
                });
            });
            var check = true,disableTrage = true;
            //仓库联动 TODO 仓库待定
            form.on('select(warehouse)', function (data) {
                var _warehouse_id = data.value, _order_id = $("input[name='order_id']").val(),
                    _total_weight = $(".total_weight").text(),status = $(data.elem).find("option:selected").attr("data-type");
                $('.goods-lists').attr('data-type', status);
                var loading = layer.msg('获取运费中', {
                    icon: 16
                    , shade: 0.01
                });
                $.ajax({
                    type: 'get',
                    url: '{{ route('order.logistics.lists') }}',
                    data: {order_id: _order_id, warehouse_id: _warehouse_id, total_weight: _total_weight},
                    dataType: 'json',
                    success: function (res) {
                        layer.close(loading);
                        $(".Logistic").empty();
                        var item = res.data, goods = res.goods, tmp = '';
                        if (res.code == 1) {
                            //物流
                            for (var i in item) {
                                var info = '', state = '';
                                if (res.status == 1) {
                                    if (item[i]['totalFee']) {
                                        info = item[i]['totalFee'] + ' RMB';
                                    } else {
                                        info = item[i]['error'];
                                    }
                                } else {
                                    info = 0;
                                }
                                if ('{{$OrdersDesc['logistics_id'] or ''}}' == item[i]['id'] && check) {
                                    state = 'checked';
                                } else {
                                    state = '';
                                }
                                if(item[i]['ask']=='Failure'){
                                   _disable = 'disabled';
                                }else{
                                    _disable = '';
                                }

                                tmp += '<tr>' +
                                    '<td><input '+ _disable +' type="radio" ' + state + ' name="logistic_id" value="' + item[i]['logistic_id'] + '" lay-skin="primary"></td>' +
                                    '<td>' + item[i]['logistic_name'] + '</td>' +
                                    '<td>' + info + '</td>' +
                                    '</tr>';
                            }

                        } else {
                            $(".dispensable_number_td").find('input').val(0);
                            $(".cargo_distribution_td").find('input').val(0);
                            $(".cargo_distribution_td").find('span').text(0);
                            let distributeEle = $(".dispensable_number");
                            let total = 0;
                            $.each(distributeEle, function (k, v) {
                                total += parseInt($(this).val());
                            });
                            if (total > 0) {
                                layer.alert(res.msg, {icon: 5});
                            }
                            tmp += '<td colspan="9" style="text-align:center;color:#ccc">无</td>';
                        }

                        var total_weight = 0, total_already_stocked = 0;
                        //商品库存
                        for (var j in goods) {
                            var buy_number = parseInt(goods[j].buy_number);//购买数量
                            var weight = parseFloat(goods[j].goods.goods_weight);//重量
                            var already_stocked_number = parseInt(goods[j].already_stocked_number);//已配货数量
                            var cargo_distribution = parseInt(goods[j].cargo_distribution_number);//可配货数量
                            var dispensable_number = buy_number - already_stocked_number;//配货数量

                            if (cargo_distribution) {
                                $(".cargo_distribution" + goods[j].id + "").find('input').val(cargo_distribution);
                                $(".cargo_distribution" + goods[j].id + "").find('span').text(cargo_distribution);
                            } else {
                                $(".cargo_distribution" + goods[j].id + "").find('input').val(0);
                                $(".cargo_distribution" + goods[j].id + "").find('span').text(0);

                            }
                            if (cargo_distribution > 0 && cargo_distribution <= dispensable_number) {
                                total_already_stocked = cargo_distribution;
                                if(disableTrage) {
                                    $(".cargo_distribution" + goods[j].id + "").prev('.dispensable_number_td').find('input').val(cargo_distribution);
                                    $(".cargo_distribution" + goods[j].id + "").prev('.dispensable_number_td').find('input').prop('disabled', false);
                                }
                            }
                            if (cargo_distribution > 0 && cargo_distribution >= dispensable_number) {
                                total_already_stocked = dispensable_number;
                                if(disableTrage){
                                    $(".cargo_distribution" + goods[j].id + "").prev('.dispensable_number_td').find('input').val(dispensable_number);
                                    $(".cargo_distribution" + goods[j].id + "").prev('.dispensable_number_td').find('input').prop('disabled', false);
                                }
                            }

                            if (cargo_distribution <= 0) {
                                $(".cargo_distribution" + goods[j].id + "").prev('.dispensable_number_td').find('input').val(0);
                                $(".cargo_distribution" + goods[j].id + "").prev('.dispensable_number_td').find('input').prop('disabled', true);
                            }

                            if (total_already_stocked > 0) {
                                total_weight += total_already_stocked * weight;
                            }
                        }
                        if(disableTrage){
                            $(".total_weight").text(total_weight.toFixed(2));
                        }
                        check = false;
                        $(".Logistic").append(tmp);
                        form.render();
                        disableTrage = true;
                    }
                });
            });

            //不能配货低于0
            $(document).on("change", ".dispensable_number", function () {
                disableTrage = false;
                var type = $('.goods-lists').attr('data-type');//类型
                var _val = parseInt($(this).val());
                if (_val < 0) {
                    layer.msg('配货数量不能低于0');
                    $(this).val(0);
                    return false;
                }
                if (isNaN(_val)) {
                    $(this).val(0);
                    return false;
                }
                var buy_number = parseInt($(this).parent('td').siblings('.buy_number_td').find('input').val());//购买数量
                var already_stocked_number = parseInt($(this).parent('td').siblings('.already_stocked_number_td').find('input').val());//已配货数量
                var cargo_distribution = parseInt($(this).parent('td').siblings('.cargo_distribution_td').find('input').val());//可配货数量

                if ((_val + already_stocked_number) > buy_number) {
                    $(this).val((buy_number - already_stocked_number));
                    layer.msg('配货数量不能大于【购买数量-已配货数量】');
                    return false;
                }
                if (_val > cargo_distribution) {
                    $(this).val(cargo_distribution);
                    layer.msg('配货数量不能大于可配货数量');
                    return false;
                }
                if (cargo_distribution == 0) {
                    layer.msg('仓库无货，请及时补充');
                    $(this).val(0);
                    return false;
                }
                CalculatedWeight(type);
            });
            CalculatedWeight(type);
        });

        $("body").bind("keydown", function (event) {
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location = location;
            }
        });
        var CalculatedWeight = function (type) {
            var total_weight = 0;
            $(".goods-lists tr").not('tr:last-child').each(function (index, element) {
                var dispensable_number = $(this).find('.dispensable_number_td input').val(),
                    weight = parseFloat($(this).find('.weight').text());
                var total_already_stocked = parseInt(dispensable_number);
                total_weight += total_already_stocked * weight;
            });
            if (isNaN(total_weight)) {
                total_weight = 0;
            } else {
                total_weight = total_weight.toFixed(2);
            }
            $(".total_weight").text(total_weight);
            if (type == 1) {
                $('.layui-form-select dl dd.layui-this').trigger('click');
            }
        };
    </script>
@endsection