@extends('layouts.new_main')
@section('content')

    <div class="infortext">
        <div class="textpage">
            <table class="layui-table" lay-even lay-skin="row">
                <tbody>
                <tr>
                    <td style="width:50%;text-align:left;"><b>售后单号：{{$detailAfterOrder['after_sale_code'] ?? ''}}</b>
                    </td>

                    <td  style="width:50%;text-align:left;"><b>订单编号：{{$detailAfterOrder['orders']['order_number'] ?? ''}}</b>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <form action="" class="layui-form">
            {{csrf_field()}}
            <input type="hidden" name="order_id" value="{{$detailAfterOrder['after_sale_code'] ?? ''}}">
            <div class="textpage layui-form">
                <h3>售后产品</h3>
                <table class="layui-table chanp" lay-skin="line">
                    <colgroup>
                    </colgroup>
                    <thead>
                    <tr>
                        <th align="center">商品图片</th>
                        <th align="center">产品名称</th>
                        <th align="center">产品编号</th>
                        <th align="center">单价</th>
                        <th align="center">币种</th>
                        <th align="center">数量</th>
                    </tr>
                    </thead>

                    <tbody class="goods-lists" data-type="">
                    @if(!empty($detailAfterOrder))
                        @foreach($detailAfterOrder['orders_after_sales_products'] as $k=> $item)
                            <tr class="oneChoosePr">
                                {{--商品图片--}}
                                <td  style="text-align: center;">
                                    {{--<img onerror="this.src=''" src="{{url('showImage').'?path=' . $item['goods']['goods_pictures']}}" style="max-width: 24px; max-height: 24px;cursor:zoom-in;" @if (isset($item['goods']['goods_pictures'])) onclick="check_img(this.src)"@endif/>--}}
                                    <img onerror="this.src='/img/imgNotFound.jpg'" src="{{url('showImage').'?path=' . $item['goods']['goods_pictures']}}" style="max-width: 24px; max-height: 24px;cursor:zoom-in;" @if (isset($item['goods']['goods_pictures'])) onclick="check_img(this.src)"@endif/>
                                </td>
                                {{--产品名称--}}
                                <td class="already_stocked_number_td"  style="text-align: center;">
                                       <span style="display: none;">
                                           <input class="already_stocked_number checkOne" type="hidden" name="goods[{{$k}}][product_name]" value="{{$item['product_name'] ?? ''}}">
                                       </span>
                                    {{$item['product_name'] or ''}}
                                </td>

                                <td style="text-align: center;"><span style="display: none;"><input type="hidden"  name="goods[{{$k}}][sku]" class="checkOne" value="{{$item['sku'] ?? ''}}"></span>{{$item['sku'] ?? ''}}
                                </td>

                                <td class="already_stocked_number_td univalence checkOne"  style="text-align: center;"> <span style="display: none;"><input class="univalence checkOne"
                                                                                                                               type="hidden"
                                                                                                                               name="goods[{{$k}}][univalence]" value="{{$item['univalence'] ?? ''}}"></span>{{$item['univalence'] or 0}}
                                </td>

                                <td class="already_stocked_number_td checkOne"  style="text-align: center;"><span style="display: none;"><input class="already_stocked_number checkOne"
                                                                                                                   type="hidden"
                                                                                                                   name="" value="{{$detailAfterOrder['currency_code'] ?? ''}}"></span>{{$detailAfterOrder['currency_code'] or ''}}
                                </td>
                                <td style="text-align: center;">{{$item['number']}}</td>
                                    <script>
                                        function dispen_{{$k}}(){
                                            var dispen_number = $('.dispensable_number_{{$k}}').val();
                                            $('input[name="goods[{{$k}}][after_number_check]"]').val(dispen_number);

                                            layui.use(['layer', 'form', 'element', 'table'], function () {
                                                var layer = layui.layer, form = layui.form, element = layui.element;
                                                form.on('checkbox(checkbox_one)', function (data) {
                                                    var list = [];
                                                    this.checked = data.elem.checked;
                                                    form.render('checkbox');
                                                });
                                            });
                                        }

                                    </script>

                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr class="oneChoosePr">
                            <td>
                                暂无数据
                            </td>
                        </tr>
                    @endif
                </table>
            </div>
            <div class="textpage layui-form">
                <h3>处理方式</h3>
                <div class="layui-form-item xiala">
                    <div class="layui-input-block" style="margin-left: -14px;">

                        <div class="layui-form-item xiala" style="width:260px;">
                            <div class="layui-input-block" style="margin-left:1px;">
                                    @if(isset($detailAfterOrder['type']) && ($detailAfterOrder['type'] == 3)) 退款 @endif
                                    @if(isset($detailAfterOrder['type']) && ($detailAfterOrder['type'] == 1)) 退货 @endif
                                    @if(isset($detailAfterOrder['type']) && ($detailAfterOrder['type'] == 2)) 换货 @endif
                            </div>
                        </div>

                    </div>
                </div>
                <div class="layui-input-block" style="margin-left: -1px;padding-top:-22px;margin-top:-3%;">
                    <h3>退款金额</h3>  {{$detailAfterOrder['refund'] ?? ''}}
                    <div class="layui-form-item xiala" style="width:160px;">
                        <div class="layui-input-block" style="margin-left:20px;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-form-item l">
            </div>
        </form>
    </div>

@endsection

@section('javascripts')
    <script>
        layui.use(['layer', 'form', 'element', 'table'], function () {
            var layer = layui.layer, form = layui.form, element = layui.element;
            //监听提交
            form.on('submit(LAY-front-submit)', function (data) {
                var field = data.field;

                var index = parent.layer.getFrameIndex(window.name);
                var product_number = $('.after_product_number');
                var pro_length = product_number.length;
                var i = 0;
                $.each(product_number,function(){
                    if($(this).val() == 0){
                        i++;
                    }
                });
                if(i == pro_length){
                    layer.msg('请选择售后产品');
                    i = 0;
                    return;
                }

                if($('.after_money_total').val() == ''){
                    layer.msg('已退款金额为必填项');
                    return;
                }

                $.ajax({
                    type: 'post',
                    url: '{{ url('/order/afterSales/createPaymentOrder')  }}',
                    data: field,
                    dataType: 'json',
                    success: function (data) {
                        layer.close(layer.loading);
                        if (data.code == 200) {
                            parent.layer.msg(data.msg, {icon: 1});
                            parent.layer.close(index); //再执行关闭
                            window.parent.location.reload();
                        } else {
                            parent.layer.msg(data.msg, {icon: 5});
                        }
                    }
                });
            });

            var check = true, sendCheck = true;
            var type = $('.goods-lists').attr('data-type');
            $(document).on("change", ".after_product_number", function () {
                var totalMoney = 0;
                $('.goods-lists tr').each(function(){
                    var product_number = $(this).find('.after_product_number').val();//univalence
                    var univalence = $(this).find('.univalence').text();
                    totalMoney += product_number * univalence;
                });
                $('.after_money_total').val(totalMoney);
            });
        });

        $("body").bind("keydown", function (event) {
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location = location;
            }
        });
    </script>
@endsection