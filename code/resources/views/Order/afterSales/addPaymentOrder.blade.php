@extends('layouts.new_main')
{{--<style>--}}
{{--    input[type=number] {--}}
{{--        -moz-appearance:textfield;--}}
{{--    }--}}
{{--    input[type=number]::-webkit-inner-spin-button,--}}
{{--    input[type=number]::-webkit-outer-spin-button {--}}
{{--        -webkit-appearance: none;--}}
{{--        margin: 0;--}}
{{--    }--}}
{{--</style>--}}
@section('content')

    <div class="infortext">
        <div class="textpage">
            <table class="layui-table" lay-even lay-skin="row">
                <tbody>
                <tr>
                    <td><b>订单编号：{{$orderNum}}</b>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <form action="" class="layui-form">
            {{csrf_field()}}
            <input type="hidden" name="order_id" value="{{$orderId}}">
            <div class="textpage layui-form">
                <h3>售后产品</h3>
                <table class="layui-table chanp" lay-skin="line">
                    <colgroup>
                    </colgroup>
                    <thead>
                    <tr>
                        {{--                        <th align="center"><input type="checkbox" lay-skin="primary" lay-filter="oneChoose" name="" value=""></th>--}}
                        <th align="center">商品图片</th>
                        <th align="center">产品名称</th>
                        <th align="center">产品编号</th>
                        <th align="center">单价</th>
                        <th align="center">币种</th>
                        <th align="center">数量</th>
                        <th align="center">售后数量</th>
                    </tr>
                    </thead>

                    <tbody class="goods-lists" data-type="">
                    @if(!empty($afterProducts))
                        @foreach($afterProducts as $k=> $item)
                            <tr class="oneChoosePr">
                                <td  style="text-align: center;">
                                    {{--<img onerror="this.src=''" style="cursor: zoom-in;height: 50px;"--}}
                                    <img  style="cursor: zoom-in;height: 50px;"
                                         @if ($item['goods_pictures'])
                                         src="{{url('showImage').'?path='.$item['goods_pictures'] ?? ''}}"
                                         onclick="check_img(this.src)"
                                         @else
                                         src="" alt=""
                                            @endif
                                    />
                                {{--产品名称--}}
                                <td class="already_stocked_number_td">
                                       <span style="display: none;">
                                           <input class="already_stocked_number checkOne" type="hidden"
                                                  name="goods[{{$k}}][product_name]" value="{{$item['product_name']}}">
                                       </span>
                                    {{$item['product_name'] or 0}}
                                </td>

                                <td><span style="display: none;"><input type="hidden" name="goods[{{$k}}][sku]"
                                                                        class="checkOne"
                                                                        value="{{$item['sku']}}"></span>{{$item['sku']}}
                                </td>

                                <td class="already_stocked_number_td univalence checkOne"> <span style="display: none;"><input
                                                class="univalence checkOne"
                                                type="hidden"
                                                name="goods[{{$k}}][univalence]"
                                                value="{{$item['univalence']}}"></span>{{$item['univalence'] or 0}}
                                </td>

                                <td class="already_stocked_number_td checkOne"><span style="display: none;"><input
                                                class="already_stocked_number checkOne"
                                                type="hidden"
                                                name=""
                                                value="{{$item['currency_code'] ?? ''}}"></span>{{$item['currency_code'] or 0}}
                                </td>
                                <td class="already_stocked_number_td checkOne delivery_number"><span style="display: none;"><input
                                                class="already_stocked_number checkOne"
                                                type="hidden"
                                                name="goods[{{$k}}][already_stocked_number]"
                                                value="{{$item['delivery_number'] ?? ''}}"> </span> {{$item['delivery_number'] or 0}}
                                </td>
                                <td class="dispensable_number_td checkOne"><input type="number"
                                                                                  name="goods[{{$k}}][after_number]"
                                                                                  max="{{$item['delivery_number'] ?? ''}}"
                                                                                  min="0"
                                                                                  value="0"
                                                                                  onkeyup="this.value=this.value.replace(/\D/g,'')"
                                                                                  class="nub dispensable_number_{{$k}} checkOne after_product_number"
                                                                                  onclick="dispen_{{$k}}()"
                                                                                  autocomplete="off"/>
                                    <script>
                                        function dispen_{{$k}}() {
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
                            <input type="hidden" name="invoice_id" value="{{$item['invoice_id'] ?? ''}}">
                            <input type="hidden" name="rate" value="{{$item['rate'] ?? ''}}">
                            <input type="hidden" name="attribute" value="{{$item['attribute'] ?? ''}}">
                            <input type="hidden" name="cargo_distribution_number"
                                   value="{{$item['cargo_distribution_number'] ?? ''}}">

                            <input type="hidden" name="currency_code" value="{{$curencyInfo['currency_code'] ?? ''}}">
                            <input type="hidden" name="rate" value="{{$curencyInfo['rate'] ?? ''}}">
                            <input type="hidden" name="order_num" value="{{$orderNum ?? ''}}">
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
                <div class="layui-form-item xiala" style="width: 515px;">
                    <div class="layui-input-block" style="margin-left: -14px;">

                        <div class="layui-form-item xiala" style="width:260px;">
                            <div class="layui-input-block" style="margin-left:1px;">
                                <select name="operation" lay-verify="required" id="operation" lay-filter="warehouse">
                                    <option value="">请选择</option>
                                    <option value="3">退款</option>
                                    <option value="1">退货</option>
                                    <option value="2">换货</option>
                                </select>
                            </div>
                        </div>


                        <div class="layui-form-item xiala warehouse-element" style="width:260px;display: none;">
                            <div class="layui-input-block" style="margin-left:1px;">
                                <select name="warehouse_id" lay-verify="" id="warehouse">
                                    <option value="">请选择</option>
                                    @foreach($warehouse as $key => $val)
                                        <option value="{{$val['id']}}">{{$val['warehouse_name']}}</option>
                                    @endforeach;
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="textpage layui-form exchange-element" style="display: none">
                <h3>设置重发商品</h3>
                <input type="text" class="search-sku" value=""> <a class="layui-btn" id="search-sku-btn">添加SKU</a>
                <table class="layui-table chanp" lay-skin="line">
                    <colgroup>
                    </colgroup>
                    <thead>
                    <tr>
                        <th align="center">商品图片</th>
                        <th align="center">产品编号</th>
                        <th align="center">产品名称</th>
                        <th align="center">单价</th>
                        <th align="center">币种</th>
                        <th align="center">售后数量</th>
                    </tr>
                    </thead>

                    <tbody class="goods-lists" data-type="" id="exchange-goods-table">
                    {{--                    <tr class="oneChoosePr-temp">--}}
                    {{--                        <td><a href="" target="_blank"><img width="50" height="50" src="" alt=""/></a></td>--}}
                    {{--                        --}}{{--产品编号--}}
                    {{--                        <td class="already_stocked_number_td"><input type="hidden" value=""></td>--}}
                    {{--                        --}}{{--产品名称--}}
                    {{--                        <td></td>--}}
                    {{--                        --}}{{--单价--}}
                    {{--                        <td></td>--}}


                    {{--                        <td class="already_stocked_number_td checkOne"> {{$item['currency_code']}}</td>--}}
                    {{--                        --}}{{--                                <td class="already_stocked_number_td checkOne"></td>--}}
                    {{--                        <td class="dispensable_number_td checkOne"><input type="number"--}}
                    {{--                                                                          name=""--}}
                    {{--                                                                          max="{{$item['already_stocked_number']}}"--}}
                    {{--                                                                          min="0"--}}
                    {{--                                                                          value="0"--}}
                    {{--                                                                          class="nub dispensable_number_{{$k}} checkOne after_product_number"--}}
                    {{--                                                                          onclick="dispen_{{$k}}()"--}}
                    {{--                                                                          autocomplete="off"/>--}}

                    {{--                        </td>--}}
                    {{--                    </tr>--}}
                    </tbody>
                </table>
            </div>

            <div class="textpage layui-form">
                <div class="layui-input-block" style="margin-left: -1px;padding-top:-22px;margin-top:-13px;">
                    <input type="checkbox" lay-skin="primary" lay-filter="orderType" class="checkbox_one refund-checkbox"
                           name="after-sale-option" value="1">
                    <h3>退款金额</h3>  <input type="text" class="after_money_total" value="0" style="margin-left:-12px;padding-left:5px;background-color: grey;"
                                          name="after_money_total" onkeyup="this.value=(parseFloat(this.value)) < 0||!parseFloat(this.value) ? 0 : this.value" >
                    <div class="layui-form-item xiala" style="width:160px;">
                        <div class="layui-input-block" style="margin-left:20px;">
                            {{$curencyInfo['currency_code'] ??''}}
                        </div>
                    </div>
                </div>

                <div class="layui-input-block supplement-div" style="margin-left: -1px;padding-top:-22px;margin-top:-13px;display: none">
                    <input type="checkbox" lay-skin="primary" lay-filter="orderType" class="checkbox_one supplement-checkbox" name="after-sale-option"
                           value="2">
                    <h3>补款金额</h3>  <input type="text" class="supplement" style="margin-left:-12px; background: grey"
                                          name="supplement" onkeyup="this.value=(parseFloat(this.value)) < 0||!parseFloat(this.value) ? 0 : this.value" value="" disabled="disabled">
                    <div class="layui-form-item xiala" style="width:160px;">
                        <div class="layui-input-block" style="margin-left:20px;">
                            {{$curencyInfo['currency_code'] ?? ''}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-form-item l" style="text-align: center">
                <input type="button" class="layui-btn" lay-submit lay-filter="LAY-front-submit"
                       id="LAY-front-submit" value="添加">
                <a class="layui-btn layui-btn-primary" id="back">取消</a>
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

                var after_money = $('input[name="after_money_total"]').val();
                var index = parent.layer.getFrameIndex(window.name);
                var product_number = $('.after_product_number');
                var pro_length = product_number.length;
                //退款金额
                var after_total_money = $('input[name="after_money_total"]').val();
                var univalence = $('.univalence').text();
                var already_stock_number = $('.delivery_number').text();
                var checked_box = $('input[type="checkbox"]:checked');

                var i = 0;
                $.each(product_number, function () {
                    if ($(this).val() == 0) {
                        i++;
                    }
                });



                if(checked_box.length == 0 && $("#operation").val() != 3) {
                    layer.msg('请选择金额');
                    return false;
                }

                if (i == pro_length) {
                    layer.msg('请选择售后产品');
                    i = 0;
                    return;
                }

                if (after_money == '') {
                    layer.msg('已退款金额为必填项');
                    return;
                }

                if (($("#operation").val() == 1 || $("#operation").val() == 2) && !$("#warehouse").val()) {
                    layer.msg('请选择退回的仓库')
                    return false;
                }
                if(after_total_money > (univalence * already_stock_number)) {
                    layer.msg('退款金额大于订单总金额');
                    return false;
                }
                // if ($(".refund-checkbox:checked").val() == 1 && af)

                $.ajax({
                    type: 'post',
                    url: '{{ url('order/afterSales/createPaymentOrder')  }}',
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

            form.on('checkbox(oneChoose)', function (data) {
                var list = [];
                $(this).parents('.oneChoosePr').find(".checkOne").each(function () {
                    this.checked = data.elem.checked;
                });
                form.render('checkbox');
            });

            var check = true, sendCheck = true;
            var type = $('.goods-lists').attr('data-type');//类型

            //不能配货低于0
            $(document).on("change", ".after_product_number", function () {
                var totalMoney = 0;
                $('.goods-lists tr').each(function () {
                    var product_number = $(this).find('.after_product_number').val();//univalence
                    var univalence = $(this).find('.univalence').text();
                    totalMoney += product_number * univalence;
                });
                $('.after_money_total').val(totalMoney);
            });

            form.on('checkbox(orderType)', function (data) {
                if (data.value == 2) {
                    if(data.elem.checked) {
                        $(".supplement").attr("disabled", false).css('background', 'white');
                    } else {
                        $(".supplement").attr("disabled", true).css('background', 'grey');
                    }
                    $(".refund-checkbox").prop('checked', false);
                    $(".after_money_total").attr("disabled", true).css('background', 'grey');
                }
                if (data.value == 1) {
                    $(".supplement").attr("disabled", false).css('background', 'grey');
                    $(".supplement-checkbox").prop('checked', false);
                    if(data.elem.checked) {
                        $(".after_money_total").attr("disabled", false).css('background', 'white');
                    } else {
                        $(".after_money_total").attr("disabled", true).css('background', 'grey');
                    }

                }
                form.render('checkbox')
            });


            form.on('select(warehouse)', function (data) {
                if (data.value == 3 || data.value == '') {
                    $('.supplement-checkbox').prop('checked',false);
                    $(".warehouse-element").hide();
                    $(".exchange-element").hide();
                    $(".supplement-div").hide();
                    form.render('checkbox')
                    return false;
                }
                if (data.value == 2) {
                    $(".exchange-element").show();
                    $(".warehouse-element").show();
                    $(".supplement-div").show();
                    return false;
                } else {
                    $('.supplement-checkbox').prop('checked',false);
                    $(".exchange-element").hide();
                    $(".warehouse-element").show();
                    $(".supplement-div").hide();
                    form.render('checkbox')
                    return  false;
                }
                return false;
            });

            $("#search-sku-btn").click(function () {
                var sku = $(".search-sku").val().trim();
                var currency_code = "{{$curencyInfo['currency_code']}}";
                var index = $("#exchange-goods-table tr").length;
                $.get('/order/searchProductBySku', {'sku':sku}, function (e) {
                    if (e.code != 0) {
                        layer.msg(e.msg);
                        return false;
                    }

                    var template = ' <tr class="oneChoosePr-temp">\n' +
                        '<td style="text-align:center"><a href="" target="_blank"><img onerror="this.src=\'/img/imgNotFound.jpg\'" width="50" height="50" src="'+ e.data.goods_img +'" alt=""/></a></td>\n' +
                        '{{--产品编号--}}\n' +
                        '<td style="text-align:center" class="already_stocked_number_td"><input type="hidden" name="sku['+ index +']" value="'+ e.data.sku +'"> ' + e.data.sku+ '</td>\n' +
                        '{{--产品名称--}}\n' +
                        '<td style="text-align:center">'+ e.data.goods_name +'</td>\n' +
                        '{{--单价--}}\n' +
                        '<td style="text-align:center">0</td>\n' +
                        '\n' +
                        '<td style="text-align:center" class="already_stocked_number_td checkOne"> '+ currency_code +' </td>\n' +
                        '{{--                                <td class="already_stocked_number_td checkOne"></td>--}}\n' +
                        '<td style="text-align:center" class="dispensable_number_td checkOne"><input type="number"\n' +
                        'name="quantity['+ index +']"\n' +
                        'max="'+ e.data.already_in_stock_number +'"\n' +
                        'min="1"\n' +
                        'value="1" onkeyup="this.value=this.value.replace(/\\D/g,\'\')"\n' +
                        'autocomplete="off"/>\n' +
                        '\n' +
                        '</td>\n' +
                        '</tr>';

                    if (index == 0) {
                        $("#exchange-goods-table").append(template)
                    } else {
                        $("#exchange-goods-table tr:last").after(template)
                    }


                });

            })


        });

        $("body").bind("keydown", function (event) {
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location = location;
            }
        });

        $(document).on('click','#back',function () {
            var index = parent.layer.getFrameIndex(window.name);
            parent.layer.close(index);
        });
    </script>
@endsection
