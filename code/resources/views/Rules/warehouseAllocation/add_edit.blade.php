@extends('layouts/dialog')

@section('content')
    <style>
        body, body {
            min-width: unset !important;
            padding: 3%;
        }
        .ruleBody{height: 270px !important;}
        .ruleBody .ruleSection{height: 236px !important;overflow-y: auto !important;}
        .ruleBody .choseTab{height: 270px;overflow-y: auto;}
        .appLogis .logisname{height: 245px !important;}
        .appLogis .logisname .col{height: 202px !important;}
    </style>

    <div id="OrderRules">
        <form action="" id="layui-form" class="layui-form">
            <input type="hidden" name="_token" value="{{csrf_token()}}">
            <div class="ruleName">
                <ul class="flexlayer">
                    <li>
                        <div class="inptxt"><b style="color: red;">* </b> 规则名称：</div>
                        <div class="inpblock">
                            <input lay-verify="required" maxlength="300" class="layui-input" type="text" placeholder="请输入规则名称"
                                   name="trouble_rules_name" value="{{$data ['trouble_rules_name']}}">
                        </div>
                    </li>
                </ul>
            </div>
            <div class="ruleBody">
                <div class="setRule">
                    <div class="ruletitle"><h3>已设置规则</h3></div>
                    <div class="ruleSection">
                        <ul class="flexlayer">
                            @if(isset($conditions))
                                @foreach ($conditions as $condition)
                                    <li relid="{{$condition['relid']}}" id="{{$condition['sertid']}}">
                                        {!!$condition['cond_name']!!}
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
                <div class="choseTab">
                    <div class="ruletitle">
                        <h3>选择条件</h3>
                    </div>
                    <div class="choselist liergodic">
                        @if (isset($rules))
                            @foreach($rules as $key => $rule)
                                <div class="row">
                                    <h4>@if ($key == 'orders')
                                            {{'订单来源'}}
                                        @elseif ($key == 'logistics')
                                            {{'物流信息'}}
                                        @elseif ($key == 'products')
                                            {{'商品信息'}}
                                        @else
                                        @endif
                                    </h4>
                                    <ul class="list">
                                        @foreach($rule as $value)
                                            @if(in_array($value['id'],[1,2,3,4,9]))
                                                <li>
                                            @else
                                                <li style="display: none;">
                                            @endif
                                                    <input type="checkbox" lay-skin="primary"
                                                           @if(in_array($value['id'],$conditionIds)) checked
                                                           @endif title="<div class='nm'>{{$value['condition_prefix']}}</div><b class='ws'>{{$value['condition_name']}}</b>"/>
                                                </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
            <div class="writelist">
                <div class="appLogis">
                    <div class="logisname logis_list">
                        <h3>选择仓库</h3>
                        <div class="col">
                            @if (isset($warehouses))
                                @foreach($warehouses as $warehouse)
                                    <span data-id="{{$warehouse['id']}}">{{$warehouse['warehouse_name']}}</span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="logis_toFro">
                        <div class="kpl"><span class="logTo kbico"></span><span class="logfro kbico"></span></div>
                    </div>
                    <div class="logisname logis_settle">
                        <h3>发货仓库（优先选择上面仓库）</h3>
                        <div class="col">
                            @if(!empty($selectWarehouse))
                                @foreach($selectWarehouse as $selectItem)
                                    <span data-id="{{$selectItem['id']}}"> {{$selectItem['warehouse_name']}}</span>
                                @endforeach
                            @endif

                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label" style="width: 100px">是否启用：</label>
                    <div class="layui-input-block">
                        <input type="radio" name="opening_status" value="1" lay-skin="primary" @if($data ['opening_status'] == 1) checked @endif title="是"/>
                        <input type="radio" name="opening_status" value="2" lay-skin="primary" @if($data ['opening_status'] == 2) checked @endif title="否"/>
                    </div>
                </div>
            </div>
            <div class="layui-form-item layui-hide">
                <input type="button" lay-submit lay-filter="formSubmit" id="formSubmit" value="确认">
            </div>
        </form>
    </div>

    <!-- 规格弹窗 -->
    <!--平台-->
    <div id="rulecont01" class="outWindow hide">
        <form action="" class="layui-form">
            <div class="colCheckbox checkboxGroup">
                <div class="boxall">
                    <h3>
                        <b class="ti">请选择</b>
                        <input type="checkbox" lay-filter="allsele" class="selectAll" lay-skin="primary" title="全选"
                               data-type="1"/>
                    </h3>
                </div>
                <div class="kb_chbox chebox">
                    @if(isset($platforms))
                        @foreach($platforms as $platform)
                            <div class="lip">
                                <input type="checkbox" class="chekid" name="check[]" lay-filter="oneCho"
                                       lay-skin="primary" title="{{$platform['name_EN']}}" value="{{$platform['id']}}"
                                       data-type="1" data-name="platforms_id[]"
                                {{--@if(isset($checkedDatas ['platforms_id']) && in_array($platform['id'],$checkedDatas ['platforms_id']))--}}
                                    {{--checked--}}
                                        {{--@endif--}}
                                />
                            </div>
                        @endforeach
                    @endif

                </div>
            </div>
            <div class="colCheckbox">
                <div class="boxall">
                    <h3><b class="ti">已选中项</b>
                        <em class="remvsele layui-btn layui-btn-xs">取消全部</em>
                    </h3>
                </div>
                <div class="kb_chbox kb_hadSelected">

                </div>
            </div>
            <button lay-filter="thes1" lay-submit id="thes1" class="hide"></button>
        </form>
    </div>
    <!--店铺-->
    <div id="rulecont02" class="outWindow hide">
        <form action="" class="layui-form">
            @if(isset($shops))
                @foreach($shops as $shopKey => $shop)
                    <div class="colCheckbox checkboxGroup {{$shopKey}}_checkboxGroup">
                        <div class="boxall">
                            <h3>
                                <input type="checkbox" lay-filter="allsele" class="{{$shopKey}}_selectAll"
                                       lay-skin="primary" title="
                        @if ($shopKey == 'rakuten')
                                {{'乐天'}}
                                @elseif ($shopKey == 'amazon')
                                {{'亚马逊'}}
                                @else
                                {{'其他'}}
                                @endif
                                        " data-plat="{{$shopKey}}" data-type="2"/>
                            </h3>
                        </div>
                        @if (!empty($shop))
                            <div class="kb_chbox chebox">
                                @foreach($shop as $shopVal)
                                    <div class="lip">
                                        <input type="checkbox" class="chekid" name="check[]" lay-filter="oneCho"
                                               lay-skin="primary" title="{{$shopVal['shop_name']}}"
                                               value="{{$shopVal['id']}}" data-type="2" data-name="shop_id[]"
                                               data-plat="{{$shopKey}}"/>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
            <div class="colCheckbox">
                <div class="boxall">
                    <h3><b class="ti">已选中项</b>
                        <em class="remvsele layui-btn layui-btn-xs">取消全部</em>
                    </h3>
                </div>
                <div class="kb_chbox kb_hadSelected"></div>
            </div>
            <button lay-filter="thes1" lay-submit id="thes2" class="hide"></button>
        </form>
    </div>

    <!--指定国家-->
    <div id="rulecont03" class="outWindow hide">
        <div class="appLogis">
            <div class="logisname logis_list">
                <h3>请选择</h3>
                <div class="col">
                    @if (isset($countrys))
                        @foreach($countrys as $country)
                            <span data-type="3" data-value="{{$country['id']}}"
                                  data-name="country_id[]">{{$country['country_name']}}</span>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="logis_toFro">
                <div class="kpl"><span class="logTo kbico"></span><span class="logfro kbico"></span></div>
            </div>
            <div class="logisname logis_settle">
                <h3>已选择</h3>
                <div class="col"></div>
            </div>
        </div>
    </div>
    <!--指定邮编-->
    <div id="rulecont05" class="outWindow hide">
        <div class="pop_textArea">
            <textarea class="numTxtarea" placeholder="支持格式：10000、10001~10008" name="post_code" id=""
                      value=""></textarea>
        </div>
    </div>
    <!--指定字段空-->
    <div id="rulecont06" class="outWindow hide">
        <div class="FieldEmpty layui-form">
            <div class="row">
                <input type="checkbox" lay-skin="primary"
                       title="<div class='ep'>收件人地址：</div><b class='imt' data-name='empty_field[]' data-value='0'>州省或城市为空</b>"/>
            </div>
            <div class="row">
                <input type="checkbox" lay-skin="primary"
                       title="<div class='ep'>收件人地址：</div><b class='imt' data-name='empty_field[]' data-value='1'>电话或手机为空</b>"/>
            </div>
            <div class="row">
                <input type="checkbox" lay-skin="primary"
                       title="<div class='ep'>收件人地址：</div><b class='imt' data-name='empty_field[]' data-value='2'>邮编为空</b>"/>
            </div>
        </div>
    </div>
    <!--商品尺寸-->
    <div id="rulecont07" class="outWindow hide">
        <div class="SizeRange layui-form">
            <ul class="flexlayer">
                <li>
                    <div class="inptxt">
                        <input type="checkbox" lay-skin="primary" title="商品尺寸(长)：" data-type="goods_length_type"
                               name="goods_length_type"/>
                    </div>
                    <div class="inpblock">
                        <select lay-filter="type" class="layui-disabled" disabled name="goods_length_unit" id="">
                            <option value=">=">大于等于(>=)</option>
                            <option value="=">等于(=)</option>
                            <option value="<="> 小于等于(<=)</option>
                        </select>
                        <input class="kbinp layui-disabled" disabled type="number" min="0"
                               onkeyup="value=value.replace(/[^\d]/g,'')"
                               onblur="value=value.replace(/[^\d]/g,'')" name="goods_length" value=""/> CM
                    </div>
                </li>
                <li>
                    <div class="inptxt"><input type="checkbox" lay-skin="primary" title="商品尺寸(宽)："
                                               data-type="goods_width_type" name="goods_width_type"/></div>
                    <div class="inpblock">
                        <select lay-filter="type" class="layui-disabled" disabled name="goods_width_unit" id="">
                            <option value=">=">大于等于(>=)</option>
                            <option value="=">等于(=)</option>
                            <option value="<="> 小于等于(<=)</option>
                        </select> <input class="kbinp layui-disabled" disabled type="number" min="0"
                                         onkeyup="value=value.replace(/[^\d]/g,'')"
                                         onblur="value=value.replace(/[^\d]/g,'')" name="goods_width" value=""/> CM
                    </div>
                </li>
                <li>
                    <div class="inptxt"><input type="checkbox" lay-skin="primary" title="商品尺寸(高)："
                                               data-type="goods_height_type" name="goods_height_type"/></div>
                    <div class="inpblock">
                        <select lay-filter="type" class="layui-disabled" disabled name="goods_height_unit" id="">
                            <option value=">=">大于等于(>=)</option>
                            <option value="=">等于(=)</option>
                            <option value="<="> 小于等于(<=)</option>
                        </select> <input class="kbinp layui-disabled" disabled type="number" min="0"
                                         onkeyup="value=value.replace(/[^\d]/g,'')"
                                         onblur="value=value.replace(/[^\d]/g,'')" name="goods_height" value=""/> CM
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <!--商品属性-->
    <div id="rulecont08" class="outWindow hide">
        <div class="proAttr layui-form">
            <ul class="flexlayer flexquar">
                @if($attrs)
                    @foreach($attrs as $attrArr)
                        <li><input type="checkbox" lay-skin="primary" title="{{$attrArr['attribute_name']}}"
                                   value="{{$attrArr['id']}}"/></li>
                    @endforeach
                @endif
            </ul>
        </div>
    </div>
    <!--商品重量-->
    <div id="rulecont09" class="outWindow hide">
        <div class="proWeight layui-form">
            <ul class="flexlayer flexhelf ">
                <li>
                    <div class="inptxt">计算方式：</div>
                    <div class="inpblock ngross">
                        <select name="goods_weight_type">
                            <option value="1">按商品单重计算</option>
                        </select>
                    </div>
                </li>
                <li>
                    <div class="inptxt">重量单位：</div>
                    <div class="inpblock nkg">
                        <select name="goods_weight_unit">
                            <option value="G">克</option>
                            <option value="KG">千克</option>
                        </select>
                    </div>
                </li>
                <li class="alone">
                    <div class="inptxt">设置范围：</div>
                    <div class="inpblock">
                        <div class="scope">
                            <span class="tp"><input type="radio" name="goods_weight_sc" title="按区间：" value="1"/></span>
                            <span class="tp"><input class="wval mx wmin" placeholder="最小值" type="number"
                                                    onkeyup="value=value.replace(/[^\d]/g,'')"
                                                    onblur="value=value.replace(/[^\d]/g,'')"
                                                    min="0"
                                                    name="goods_weight_value_min"/> ~
                                             <input type="number"
                                                    placeholder="最大值" class="wval mx wmax"
                                                    onkeyup="value=value.replace(/[^\d]/g,'')"
                                                    onblur="value=value.replace(/[^\d]/g,'')"
                                                    min="0"
                                                    name="goods_weight_value_max"/></span>
                        </div>
                        <div class="scope">
                            <span class="tp"><input type="radio" name="goods_weight_sc" title="按边界：" value="2"/></span>
                            <span class="tp">
                                <select lay-filter="type" class="layui-disabled" name="goods_weight_unit_type" id="">
                                    <option value=">=">大于等于(>=)</option>
                                    <option value="<=">小于等于(<=)</option>
                                </select>
                            </span>
                            <span class="tp"><input class="wval wet edgit" type="number" name="goods_weight_value" min="0"
                                                    onkeyup="value=value.replace(/[^\d]/g,'')"
                                                    onblur="value=value.replace(/[^\d]/g,'')"/></span>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <!--商品SKU-->
    <div id="rulecont10" class="outWindow hide">
        <div class="pop_textArea">
            <textarea class="numTxtarea" placeholder="支持多SKU：10000、10001，请用 、分隔" name="goods_sku" id=""></textarea>
        </div>
    </div>
    <!--商品金额-->
    <div id="rulecont11" class="outWindow hide">
        <div class="proWeight layui-form">
            <ul class="flexlayer flexhelf orders_price_class">
                <li>
                    <div class="inptxt">计算方式：</div>
                    <div class="inpblock ngross">
                        <select name="orders_price_type" id="">
                            <option value="1">按订单总金额计算</option>
                        </select>
                    </div>
                </li>
                <li>
                    <div class="inptxt">币种：</div>
                    <div class="inpblock">
                        <select name="orders_price_unit" id="">
                            @foreach($currencyInfos as $currencyInfo)
                                <option value="{{$currencyInfo ['code']}}" @if (isset($currency_unit) && $currencyInfo ['code'] == $currency_unit) selected
                                        @endif>{{$currencyInfo ['name']}} ({{$currencyInfo ['code']}})</option>
                            @endforeach
                        </select>
                    </div>
                </li>
                <li class="alone">
                    <div class="inptxt">设置范围：</div>
                    <div class="inpblock">
                        <div class="scope">
                            <span class="tp"><input type="radio" name="orders_price_sc" title="按区间：" value="1"/></span>
                            <span class="tp"><input class="wval mx" placeholder="最小值" type="number" min="0"
                                                    onkeyup="value=value.replace(/[^\d]/g,'')"
                                                    onblur="value=value.replace(/[^\d]/g,'')" name="orders_price_min"/> ~ <input
                                        placeholder="最大值" class="wval mx" type="number" min="0"
                                        onkeyup="value=value.replace(/[^\d]/g,'')"
                                        onblur="value=value.replace(/[^\d]/g,'')" name="orders_price_max"/></span>
                        </div>
                        <div class="scope">
                            <span class="tp"><input type="radio" name="orders_price_sc" title="按边界：" value="2"/></span>
                            <span class="tp"><select name="orders_price_unit_type" id="">
                                    <option value=">=">大于等于(>=)</option>
                                    <option value="<=">小于等于(<=)</option>

                                </select></span>
                            <span class="tp"><input class="wval wet" type="number" min="0"
                                                    onkeyup="value=value.replace(/[^\d]/g,'')"
                                                    onblur="value=value.replace(/[^\d]/g,'')"
                                                    name="orders_price"/></span>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <!--商品数量-->
    <div id="rulecont12" class="outWindow hide">
        <div class="proWeight layui-form">
            <ul class="flexlayer goods_count_class">
                <li>
                    <div class="inpblock">
                        <div class="scope">
                            <span class="tp"><input type="radio" name="goods_count_sc" title="按区间：" value="1"/></span>
                            <span class="tp"><input class="wval mx" placeholder="最小值" type="number" min="0"
                                                    onkeyup="value=value.replace(/[^\d]/g,'')"
                                                    onblur="value=value.replace(/[^\d]/g,'')" name="goods_count_min"/> ~ <input
                                        placeholder="最大值" class="wval mx" type="number" min="0"
                                        onkeyup="value=value.replace(/[^\d]/g,'')"
                                        onblur="value=value.replace(/[^\d]/g,'')" name="goods_count_max"/></span>
                        </div>
                        <div class="scope">
                            <span class="tp"><input type="radio" name="goods_count_sc" title="按边界：" value="2"/></span>
                            <span class="tp"><select name="goods_count_unit_type" id=""><option
                                            value=">=">大于等于(>=)</option><option
                                            value="<=">小于等于(<=)</option></select></span>
                            <span class="tp"><input class="wval wet" type="number" min="0"
                                                    onkeyup="value=value.replace(/[^\d]/g,'')"
                                                    onblur="value=value.replace(/[^\d]/g,'')"
                                                    name="goods_count"/></span>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <!--指定仓库-->
    <div id="rulecont13" class="outWindow hide">
        <div class="appLogis">
            <div class="logisname logis_list">
                <h3>请选择</h3>
                <div class="col">
                    @if (isset($warehouses))
                        @foreach($warehouses as $warehouse)
                            <span data-type="3" data-value="{{$warehouse['id']}}"
                                  data-name="warehouse_id[]">{{$warehouse['warehouse_name']}}</span>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="logis_toFro">
                <div class="kpl"><span class="logTo kbico"></span><span class="logfro kbico"></span></div>
            </div>
            <div class="logisname logis_settle">
                <h3>已选择</h3>
                <div class="col"></div>
            </div>
        </div>
    </div>
    <!--指定物流-->
    <div id="rulecont14" class="outWindow hide">
        <div class="appLogis">
            <div class="logisname logis_list">
                <h3>请选择</h3>
                <div class="col">
                    @if (isset($logistics))
                        @foreach($logistics as $logistic)
                            <span data-type="3" data-value="{{$logistic['id']}}"
                                  data-name="logistic_id[]">{{$logistic['logistic_name']}}</span>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="logis_toFro">
                <div class="kpl"><span class="logTo kbico"></span><span class="logfro kbico"></span></div>
            </div>
            <div class="logisname logis_settle">
                <h3>已选择</h3>
                <div class="col"></div>
            </div>
        </div>
    </div>
@endsection

@section('javascripts')
    <script type="text/javascript" src="{{ asset('js/warehouse_condition.js?'.time()) }}"></script>
    <script>
        //layui加载
        layui.config({base: '../../layui/lay/modules/'});
        layui.use(['layer', 'form', 'element', 'laydate', 'table'], function () {
            var layer = layui.layer, form = layui.form;
            //自动渲染初始化事件
            $(document).ready(function () {
                platReload();
                shopReload();
                countryReload();
                postCodeReload();
                goodsSkuReload();
                emptyFieldReload();
                goodsAttrReload();
                goodsSizeReload();
                goodsWeightReload();
                goodsCountsReload();
                orderPriceReload();
                form.render();
            });

            //平台数据重载
            function platReload() {
                var platforms_id = new Array();
                var nums = 0;
                @if(isset ($checkedDatas['platforms_id']))
                        @foreach ($checkedDatas['platforms_id'] as $k=>$v)
                    platforms_id ['{{$k}}'] = '{{$v}}'
                nums++;
                @endforeach
                        @endif
                if (nums > 0) {
                    for (var num = 0; num < nums; num++) {
                        var i = 0;
                        var j = 0;
                        $('#rulecont01').find('input[value="' + platforms_id[num] + '"]').attr('checked', 'true');
                        var ckk = $('#rulecont01').find('input[value="' + platforms_id[num] + '"]').attr('title');
                        var kid = $('#rulecont01').find('input[value="' + platforms_id[num] + '"]').attr('che-id');
                        var name = $('#rulecont01').find('input[value="' + platforms_id[num] + '"]').attr('data-name');
                        var type = $('#rulecont01').find('input[value="' + platforms_id[num] + '"]').attr('data-type');
                        var value = platforms_id[num];
                        var hiddenInput = '<input type="hidden" name="' + name + '" value = ' + value + ' class="hided">';
                        var edhtml = $('<span che-id="' + kid + '" class="ed">' + ckk + '<em class="remv kbico"></em>' + hiddenInput + '</span>');
                        var checked = $('#rulecont01').find('input[value="' + platforms_id[num] + '"]').attr('checked');
                        $('#rulecont01 .checkboxGroup').find("input[name='check[]']").each(function () {
                            if (this.checked === true) {
                                i++;
                            }
                            j++;
                        });
                        if (checked == true || checked == 'checked') {
                            $('#rulecont01').find('.kb_hadSelected').append(edhtml);
                        } else {
                            $('#rulecont01').find('.kb_hadSelected .ed').each(function () {
                                if ($(this).attr('che-id') == kid) {
                                    $(this).remove();
                                }
                            })
                        }
                        if (i == j) {
                            $(".selectAll").prop("checked", true);
                        } else {
                            $(".selectAll").removeAttr("checked");
                        }
                    }
                }
            }

            //店铺数据重载
            function shopReload() {
                var shop_id = new Array();
                var nums = 0;
                @if(isset ($checkedDatas['shop_id']))
                        @foreach ($checkedDatas['shop_id'] as $k=>$v)
                    shop_id ['{{$k}}'] = '{{$v}}'
                nums++;
                @endforeach
                        @endif
                if (nums > 0) {
                    for (var num = 0; num < nums; num++) {
                        var i = 0;
                        var j = 0;
                        $('#rulecont02').find('input[value="' + shop_id[num] + '"]').attr('checked', 'true');
                        var ckk = $('#rulecont02').find('input[value="' + shop_id[num] + '"]').attr('title');
                        var kid = $('#rulecont02').find('input[value="' + shop_id[num] + '"]').attr('che-id');
                        var name = $('#rulecont02').find('input[value="' + shop_id[num] + '"]').attr('data-name');
                        var type = $('#rulecont02').find('input[value="' + shop_id[num] + '"]').attr('data-type');
                        var value = shop_id[num];
                        var plat = $('#rulecont02').find('input[value="' + shop_id[num] + '"]').attr('data-plat');
                        var plat_class = plat + '_class';
                        var platCheckboxGroup = plat + '_checkboxGroup';
                        //新增隐藏域
                        var hiddenInput = '<input type="hidden" name="' + name + '" value = "' + value + '" class="hided" data-plat="' + plat + '">';
                        var edhtml = $('<span che-id="' + kid + '" class="ed ' + plat_class + '" >' + ckk + '<em class="remv kbico"></em>' + hiddenInput + '</span>');

                        var checked = $('#rulecont02').find('input[value="' + shop_id[num] + '"]').attr('checked');
                        $('#rulecont02 .' + platCheckboxGroup).find("input[name='check[]']").each(function () {
                            if (this.checked === true) {
                                i++;
                            }
                            j++;
                        });
                        if (checked == true || checked == 'checked') {
                            $('#rulecont02').find('.kb_hadSelected').append(edhtml);
                        } else {
                            $('#rulecont02').find('.kb_hadSelected .ed').each(function () {
                                if ($(this).attr('che-id') == kid) {
                                    $(this).remove();
                                }
                            })
                        }
                        if (i == j) {
                            $("." + plat + "_selectAll").prop("checked", true);
                        } else {
                            $("." + plat + "_selectAll").removeAttr("checked");
                        }
                    }
                }
            }

            //国家数据重载
            function countryReload() {
                var settle = $('#rulecont03 .appLogis').find('.logis_settle .col');
                        @if(isset ($checkedDatas['country_id']))
                        @foreach ($checkedDatas['country_id'] as $k=>$v)
                var span = $('<span data-type="3" data-value="{{$v['id']}}" data-name="country_id[]" class="">' + "{{$v['country_name']}}" + '</span>');
                settle.append(span)
                @endforeach
                @endif
            }

            //邮编数据重载
            function postCodeReload() {
                @if(isset ($checkedDatas['post_code']))
                var postCode = '';
                    postCode += '{{$checkedDatas['post_code']}}';
                $('#rulecont05').find('.numTxtarea').val(postCode);
                @endif
            }

            //goodsSku重载
            function goodsSkuReload() {
               @if(isset ($checkedDatas['goods_sku']))
                var sku = '';
                    sku += '{{$checkedDatas['goods_sku']}}';
                $('#rulecont10').find('.numTxtarea').val(sku);
                @endif
            }

            //空字段数据重载
            function emptyFieldReload() {
                        @if(isset ($checkedDatas['empty_field']))
                var types = new Array();
                @foreach ($checkedDatas['empty_field'] as $k=>$v)
                    types ['{{$k}}'] = '{{$v}}'
                @endforeach
                $('#rulecont06').find('.FieldEmpty .imt').each(function () {
                    type = $(this).attr('data-value');
                    if ($.in_array(type, types)) {
                        $(this).parents('.row').find('input:checkbox').prop("checked", 'checked');
                    }
                });
                @endif
            }

            //商品属性数据重载
            function goodsAttrReload() {
                var attrs = new Array();
                var nums = 0;
                @if(isset ($checkedDatas['goods_attr']))
                        @foreach ($checkedDatas['goods_attr'] as $k=>$v)
                    attrs ['{{$k}}'] = '{{$v}}'
                nums++;
                @endforeach
                if (nums > 0) {
                    for (var num = 0; num < nums; num++) {
                        $('#rulecont08').find('input[value="' + attrs[num] + '"]').prop("checked", true);
                    }
                }
                @endif
            }

            //商品尺寸数据重构
            function goodsSizeReload() {
                        @if(isset ($checkedDatas['goods_size_info']))
                        @foreach ($checkedDatas['goods_size_info'] as $k=>$v)
                var type = 'goods_' + "{{$k}}" + '_type';
                var unit = 'goods_' + "{{$k}}" + '_unit';
                var value = 'goods_' + "{{$k}}";
                var input = $('#rulecont07 ul li .inptxt').find('input[name="' + type + '"]');
                input.prop("checked", true);
                var select = $('#rulecont07 ul li .inpblock').find('select[name="' + unit + '"]');
                select.removeAttr("disabled");
                var inputVal = $('#rulecont07 ul li .inpblock').find('input[name="' + value + '"]');
                inputVal.val("{{$v['value']}}");
                select.each(function () {
                    $(this).find('option[value="{{$v['operator']}}"]').prop("selected", 'selected');
                });
                select.removeClass('layui-disabled');
                inputVal.removeClass('layui-disabled');
                @endforeach
                @endif
            }

            //商品重量数据重载
            function goodsWeightReload() {
                        @if(isset ($checkedDatas['goods_weight_info']))
                var unit = 'goods_weight_unit';
                var radio_value = 'goods_weight_sc';//区间 或者 运算
                if ("{{$checkedDatas['goods_weight_info']['operator']}}" == '~') {
                    radio_value = 1;
                } else {
                    radio_value = 2;
                }
                //单位选项
                var select = $('#rulecont09 ul li .inpblock').find('select[name="' + unit + '"]');
                select.each(function () {
                    $(this).find('option[value="{{$checkedDatas['goods_weight_info']['unit']}}"]').prop("selected", 'selected');
                });
                var radio = $('#rulecont09').find('.inpblock input:radio[value="' + radio_value + '"]');
                radio.attr("checked", true);
                if (radio_value == 1) {
                    $('#rulecont09').find('.inpblock input[name="goods_weight_value_min"]').val('{{$checkedDatas['goods_weight_info']['value'][0]}}');
                    $('#rulecont09').find('.inpblock input[name="goods_weight_value_max"]').val('{{$checkedDatas['goods_weight_info']['value'][1]}}');
                } else {
                    var select = $('#rulecont09 ul li .inpblock').find('select[name="goods_weight_unit_type"]').each(function () {
                        $(this).find('option[value="{!! $checkedDatas['goods_weight_info']['operator'] !!}"]').prop("selected", 'selected');
                    });

                    $('#rulecont09').find('.inpblock input[name="goods_weight_value"]').val('@if (is_array($checkedDatas['goods_weight_info']['value'])) {{$checkedDatas['goods_weight_info']['value'][0]}} @else {{$checkedDatas['goods_weight_info']['value']}} @endif');

                }
                @endif
            }

            //指定商品数量数据重载
            function goodsCountsReload() {
                        @if(isset ($checkedDatas['goods_count']))
                var unit = 'goods_count_unit_type';
                var radio_value = 'goods_count_sc';//区间 或者 运算
                if ("{{$checkedDatas['goods_count']['operator']}}" == '~') {
                    radio_value = 1;
                } else {
                    radio_value = 2;
                }

                var radio = $('#rulecont12').find('.inpblock input:radio[value="' + radio_value + '"]');
                radio.attr("checked", true);
                if (radio_value == 1) {
                    $('#rulecont12').find('.inpblock input[name="goods_count_min"]').val('{{$checkedDatas['goods_count']['value'][0]}}');
                    $('#rulecont12').find('.inpblock input[name="goods_count_max"]').val('{{$checkedDatas['goods_count']['value'][1]}}');
                } else {
                    var select = $('#rulecont12 ul li .inpblock').find('select[name="' + unit + '"]');
                    select.each(function () {
                        $(this).find('option[value="{!! $checkedDatas['goods_count']['operator'] !!}"]').prop("selected", 'selected');
                    });
                    $('#rulecont12').find('.inpblock input[name="goods_count"]').val('@if (is_array($checkedDatas['goods_count']['value'])) {{$checkedDatas['goods_count']['value'][0]}} @else {{$checkedDatas['goods_count']['value']}} @endif');
                }
                @endif
            }

            //订单金额数据重载
            function orderPriceReload() {
                        @if(isset ($checkedDatas['orders_price']))
                var unit = 'orders_price_unit';
                var radio_value = 'orders_price_sc';//区间 或者 运算
                if ("{{$checkedDatas['orders_price']['operator']}}" == '~') {
                    radio_value = 1;
                } else {
                    radio_value = 2;
                }
                //单位选项
                var select = $('#rulecont11 ul li .inpblock').find('select[name="' + unit + '"]');
                select.each(function () {
                    $(this).find('option[value="{{$checkedDatas['orders_price']['unit']}}"]').prop("selected", 'selected');
                });
                var radio = $('#rulecont11').find('.inpblock input:radio[value="' + radio_value + '"]');
                radio.attr("checked", true);
                if (radio_value == 1) {
                    $('#rulecont11').find('.inpblock input[name="orders_price_min"]').val('{{$checkedDatas['orders_price']['value'][0]}}');
                    $('#rulecont11').find('.inpblock input[name="orders_price_max"]').val('{{$checkedDatas['orders_price']['value'][1]}}');
                } else {
                    var select = $('#rulecont11 ul li .inpblock').find('select[name="orders_price_unit_type"]').each(function () {
                        $(this).find('option[value="{!! $checkedDatas['orders_price']['operator'] !!}"]').prop("selected", 'selected');
                    });

                    $('#rulecont11').find('.inpblock input[name="orders_price"]').val('@if (is_array($checkedDatas['orders_price']['value'])){{$checkedDatas['orders_price']['value'][0]}}@else{{$checkedDatas['orders_price']['value']}}@endif');
                }
                @endif
            }
        });
    </script>
@endsection