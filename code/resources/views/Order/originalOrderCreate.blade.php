@extends('layouts.new_main')
@section('css')
    <style>
        .productexts h3 {
            color: #1E9FFF;
            font-weight: 700;
        }

        .productexts .layui-table tbody tr:hover {
            background-color: #fff !important
        }

    </style>
@endsection
@section('content')
{{--    @include('layouts/shortcutMenus')--}}
    <div class="content-wrappers" style="margin-left: 2rem;border: 1px solid #f0f0f0; margin-top: 4rem; background: #fff; margin-right: 2rem">
        <div class="productexts" id="bianjitext">
            <form action="" class="layui-form">
            {{csrf_field()}}
            <!--订单信息-->
                <div class="produpage layui-form lay-select">
                    <h3>订单信息</h3>
                    <table class="layui-table colora" lay-skin="nob">
                        <tbody>
                        <tr>
                            <td><b><i style="color: red">*</i>电商单号：</b>
                                <input type="text" name="platform_order" required lay-verify="required"
                                       placeholder="请输入电商单号" autocomplete="off" class="layui-input"  maxlength="30"
                                       style="width: 50%;display: inline-block;">
                            </td>
                            <td><b><i style="color: red">*</i>来源平台：</b>
                                <div class="layui-input-inline">
                                    <select name="plat_id" class="platform" id="platform" lay-filter="platform_select">
                                        <option value="">请选择来源平台</option>
                                        @if (isset($platforms))
                                            @foreach($platforms as $platform)
                                                <option value="{{$platform['id']}}">{{$platform['name_EN']}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </td>
                            <td><b><i style="color: red">*</i>来源店铺：</b>
                                <div class="layui-input-inline">
                                    <select name="shop_id" lay-verify="" id="shop_unselected">
                                        <option value="">请选择来源店铺</option>
                                    </select>
                                </div>

                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <!--订单商品信息-->
                <div class="produpage layui-form textpage">
                    <h3>商品信息</h3>
                    <div class="xiala layui-row">
                        <input type="text" placeholder="请输入SKU" autocomplete="off"
                               class="layui-input sku_search" style="width: 50%;display: inline-block;"  maxlength="30">
                        <button class="layui-btn layui-btn-sm search-product">添加SKU</button>
                    </div>
                    <table class="layui-table xianwidth add-product" lay-skin="line" style="margin-top: 30px;">
                        <thead>
                        <tr>
                            <th>商品图片</th>
                            <th>商品名称</th>
                            <th>SKU</th>
                            <th>属性</th>
                            <th>数量</th>
                            <th>单价</th>
                            <th>*币种</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>

                        {{--                            <tr>--}}
                        {{--                                <td>111</td>--}}
                        {{--                                <td>--}}
                        {{--                                    <img src="/" alt=""></td>--}}
                        {{--                                <td>SKJ347128947893</td>--}}
                        {{--                                <td>fdsjaklfjdkls</td>--}}
                        {{--                                <td><input type="number" name="goods_nums" value="fdsafdasfasd"/></td>--}}
                        {{--                                <td><input type="text" name="goods_price" value="fasdfdsa fasd fasd "></td>--}}
                        {{--                                <td colspan="2">--}}
                        {{--                                    <select name="currency" lay-verify="">--}}
                        {{--                                        <option value="USD">USD</option>--}}
                        {{--                                        <option value="CNY">CNY</option>--}}
                        {{--                                        <option value="JPY">JPY</option>--}}
                        {{--                                    </select>--}}
                        {{--                                </td>--}}
                        {{--                            </tr>--}}
                        <tr style="height: 50px; background: #f2f2f2" id="bottom-line">
                            <td >运费：<input type="number" name="currency_freight" autocomplete="off" value="0.00"
                                           class="layui-input currency_freight" style="width: 20%;display: inline-block;"  maxlength="10" min="0"
                                           onkeyup="this.value=(parseFloat(this.value) < 0) ? 0 : this.value" onafterpaste="this.value=this.value.replace(/\D/g,'')">
                                <div class="layui-input-inline", style="width: 100px">
                                    <select name="currency" class="layui-input-block currency" lay-verify="" lay-filter="currency">
                                       
                                    </select>
                                </div>
                            </td>
                            <td colspan="10">订单总金额：
                                <span class="amount-of-order">0.00</span>
                                <div class="layui-input-inline" style="width: 100px">
                                    <select name="currency" class="layui-input-block currency" lay-verify="" lay-filter="currency">
                                       
                                    </select>
                                </div>
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
                            <td><b><label style="color: red">*</label>收件人：</b><input type="text" name="address_name" autocomplete="off"
                                                  class="layui-input" style="width: 50%; display: inline-block" maxlength="30"></td>
                            <td><b><label style="color: red">*</label>省/州：</b><input type="text" name="province" autocomplete="off"
                                                   class="layui-input" style="width: 50%; display: inline-block" maxlength="30">
                            </td>

                            <td><b><label style="color: red">*</label>电话：</b><input type="text" name="mobile_phone" class="layui-input" style="width: 50%; display: inline-block" maxlength="30"></td>
                        </tr>
                        <tr>
                            <td><b>买家email：</b><input type="text" name="address_email" autocomplete="off"
                                                      class="layui-input" style="width: 50%; display: inline-block" maxlength="30"></td>
                            <td><b><label style="color: red">*</label>邮编：</b>
                                <input type="text" name="zip_code" autocomplete="off" lay-verify="required"
                                       class="layui-input" style="width: 50%; display: inline-block">
                            </td>

                            <td><b>指定仓库：</b>
                                <div class="layui-input-inline" style="width: 210px;">
                                    <select name="warehouse_id" lay-verify="" lay-filter="warehouse_select">
                                        <option value=""></option>
                                        @if (isset($warehouses))
                                            @foreach($warehouses as $warehouse)
                                                <option value="{{$warehouse['id']}}">{{$warehouse['warehouse_name']}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                            </td>
                        </tr>
                        <tr>
                            <td><b><label style="color: red">*</label>国家：</b>
                                <div class="layui-input-inline" style="width: 170px;">
                                    <select name="country_id" lay-verify="required">
                                        <option value=""></option>
                                        @if (isset($country))
                                            @foreach($country as $countryVal)
                                                <option value="{{$countryVal['id']}}">{{$countryVal['country_name']}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </td>

                            <td><b><label style="color: red">*</label>地址1：</b><input type="text" name="addressee1" autocomplete="off"
                                                   class="layui-input" style="width: 50%; display: inline-block" maxlength="80"></td>

                            <td><b>指定物流：</b>
                                <div class="layui-input-inline" style="width: 210px;">
                                    <select name="logistics_id" lay-verify="" id="unselectedLogistics">
                                        <option value=""></option>
                                        @if (isset($logistics))
                                            @foreach($logistics as $logistic)
                                                <option value="{{$logistic['id']}}">{{$logistic['logistic_name']}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                            </td>

                        </tr>
                        <tr>
                            <td><b><label style="color: red">*</label>城市：</b>
                                <input type="text" name="city" autocomplete="off" lay-verify="required"
                                       class="layui-input" style="width: 50%; display: inline-block" maxlength="30">
                            </td>
                            <td><b>地址2：</b>
                                <input type="text" name="addressee2" autocomplete="off"
                                       class="layui-input" style="width: 50%; display: inline-block" maxlength="80">
                            </td>
                            <td><b>备注：</b>
                                <input type="text" name="mark" autocomplete="off"
                                       class="layui-input" style="width: 50%; display: inline-block" maxlength="120">
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="layui-form-item" style="display: flex;justify-content: center;">
                    <button class="layui-btn" id="LAY-front-submit" lay-submit lay-filter="formSubmit">确定</button>&nbsp;&nbsp;&nbsp;
                    <a class="layui-btn layui-btn-primary" lay-submit="" lay-filter="" id="back">取消</a>
                </div>
            </form>
        </div>

    </div>
{{--    <div class="kbmodel_full">--}}
{{--        --}}
{{--    </div>--}}
@endsection

@section('javascripts')
    <script>
        let productList = {};
        let addedProduct = [];
        let currencyOption = { @if (isset($currency))
                @foreach($currency as $val)
        {{$val['currency_form_code']}} : '{{$val['currency_form_name']}}',
        @endforeach
        @endif}

        layui.use(['form', 'laydate', 'table', 'element', 'upload', 'laypage', 'layer'], function () {
            var laypage = layui.laypage,
                element = layui.element,
                form = layui.form,
                layer = layui.layer;

            $(".search-product").click(function () {
                let sku = $(".sku_search").val().trim();
                let platform = $(".platform").val();
                let currencyOption = $("[name=currency]").html();
                if (addedProduct.indexOf(sku) >= 0) {
                    layer.msg("SKU为：" + sku + "的商品已经存在，如需变更数量，请手动变更");
                    return false;
                }
                // if (!platform) {
                //     layer.msg("请选择来源平台");
                //     return false;
                // }
                $.ajax({
                    type: "GET",
                    url: "searchProductBySku",
                    data: {"sku" : sku, "platform": platform},
                    success: function (e) {
                        if (e.code != 0) {
                            layer.msg(e.msg);
                            return false;
                        }
                         var img_html = '';
                         if (e.data.goods_pictures) {
                             img_html = '<img onerror="this.src=\'/img/imgNotFound.jpg\'" src="'+"{{url('showImage')}}"+'?path='+e.data.goods_pictures +'" style="max-width: 24px; max-height: 24px;cursor:zoom-in;" onclick="(check_img(this.src))"'
                         } else {
                             img_html = '<img src="" alt="">';
                         }
                        let newRowElement = ' <tr>\n' +
                            '<td>\n' +
                            img_html+'</td>\n' +
                            '<td style="width: 30rem;" ">'+ e.data.goods_name +'</td>\n' +
                            '<td>'+ e.data.sku +'<input type="hidden" value="'+ e.data.sku +'" name="sku['+ addedProduct.length +']" class="sku-value"> </td>\n' +
                            '<td>'+ e.data.attribute_name +'</td>\n' +
                            '<td><input type="number" onkeyup="this.value=this.value.replace(/\\D/g,\'\')" onafterpaste="this.value=this.value.replace(/\\D/g,\'\')" name="goods_nums['+ addedProduct.length +']" value="1" class="good-quantity" min="0"/></td>\n' +
                            '<td><input type="number" name="goods_price['+ addedProduct.length +']" value="0" class="edit-price" onkeyup="this.value=this.value<0?0:this.value" step="0.01" min="0"></td>\n' +
                            '<td>\n' +
                            '<select name="currency" lay-verify="" class="currency" lay-filter="currency">\n' +
                            currencyOption +
                            '</select>\n' +
                            '</td>' + '<td>' +
                                '<button type="button" class="layui-btn layui-btn-xs layui-btn-danger goodsDel" data-sku="'+ e.data.sku +'" >删除' + '</button>' +
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

            form.on('submit(formSubmit)', function (data) {
                let formData = data.field;
                let index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                if (formData.warehouse_id && !formData.logistics_id) {
                    layer.msg('您已指定仓库，指定仓库下必须指定相应的物流', {icon: 5});
                    return false;
                }
                if (formData.logistics_id && !formData.warehouse_id) {
                    layer.msg('已选择物流方式,请选择仓库', {icon: 5});
                    return false;
                }
                $.ajax({
                    type: "POST",
                    url: "createOriginal",
                    data: formData,
                    success: function (e) {
                        if (e.code != 0) {
                            layer.msg(e.msg);
                            return false;
                        }

                        if (e.code != 0) {
                            parent.layer.msg(e.msg, {icon: 5});
                        } else {
                            parent.layer.msg(e.msg, {icon: 1});
                            parent.layui.table.reload('EDtable'); //重载表格
                            parent.layer.close(index); //再执行关闭
                        }

                        // layer.msg(e.msg, {icon:1});
                        // setTimeout(window.location.href='/order/originalOrder', 800)
                    },
                });
                return false;
            })

            form.on('select(currency)', function (data) {
                $(".currency").val(data.value);
                form.render('select');
                return false;
            })

            form.on('select(platform_select)', function (data) {
                var shopSelection = $("#shop_unselected");
                shopSelection.empty();

                $.ajax({
                    type: "GET",
                    url: "getPlatformShop",
                    data: {"platform" : data.value},
                    success: function (e) {
                        if (e.code != 0) {
                            layer.msg(e.msg)
                            return false;
                        }
                        let shopOption = "<option value = ''>请选择来源店铺</option>";
                        $.each(e.data, function (k, v) {
                            shopOption += '<option value="'+ v.id +'">'+ v.shop_name +'</option>'
                        })
                        shopSelection.append(shopOption);
                        form.render('select');
                        return false;
                    },
                    error: function (e) {
                        layer.msg("获取店铺时出现异常，请重试", {icon:16});
                    }
                });
                return false;
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
            
            $(document).ready(function () {
                let optionsElement = ""
                $.each(currencyOption, function (k,v) {
                    if (k == 'JPY') {
                        optionsElement += "<option value='"+ k +"' selected>" + k + "</option>"
                    } else {
                        optionsElement += "<option value='"+ k +"'>" + k + "</option>"
                    }
                })
                $("[name=currency]").empty().append(optionsElement);
                form.render('select');
            })

        });


        $('.add-product').on('change', function(e) {
            if ($(e.target).attr('name') == 'currency_freight') {
                return false;
            }
            let currency_freight = $('.currency_freight').val();
            let modifiedRow = $(e.target).parent().parent();
            let sku = modifiedRow.find('.sku-value').val();
            let price = modifiedRow.find('.edit-price').val();
            let quantity = modifiedRow.find('.good-quantity').val();
            if (!productList[sku]) {
                productList[sku] = {}
            }
            productList[sku].price = price;
            productList[sku].quantity = quantity;

            let orderAmount = parseFloat(currency_freight) + parseFloat(productAmount())

            $(".amount-of-order").text(Number(orderAmount).toFixed(2))
        });

        $(".currency_freight").on('change', function (e) {
            let _this = $(this);
            let freight = _this.val().trim();
            if (!freight) {
                freight = 0;
            }
            let orderAmountElement = $(".amount-of-order");
            let total = parseFloat(productAmount()) + parseFloat(freight);

            orderAmountElement.empty().text(Number(total).toFixed(2));
        })

        $(document).on('click', '.goodsDel', function () {
            var _this = $(this);
            var sku = _this.attr('data-sku');
            var currency_freight = $('.currency_freight').val();

            delete productList[sku];

            var orderAmount = parseFloat(currency_freight) + parseFloat(productAmount())
            $(".amount-of-order").text(Number(orderAmount).toFixed(2))

            addedProduct.splice(addedProduct.indexOf(sku), 1)
            _this.parent().parent().remove()
        })

        function productAmount()
        {
            var orderAmount = 0.00;
            $.each(productList, function (k, v) {
                let itemAmount = v.price * v.quantity;
                orderAmount += itemAmount;
            });
            return orderAmount;
        }

        $(document).on('click','#back',function () {
            var index = parent.layer.getFrameIndex(window.name);
            parent.layer.close(index);
        });
    </script>
@endsection