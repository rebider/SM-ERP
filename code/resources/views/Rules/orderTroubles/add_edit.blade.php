@extends('layouts/dialog')

@section('content')
    <div id="OrderRules">
        <form action="" id="layui-form" class="layui-form">
            <input type="hidden" name="_token" value="{{csrf_token()}}">
            <div class="ruleName">
                <ul class="flexlayer">
                    <li>
                        <div class="inptxt">规则名称</div>
                        <div class="inpblock">
                            <input lay-verify="required" class="layui-input" type="text" placeholder="请输入规则名称"
                                   name="trouble_rules_name" value="{{$data ['trouble_rules_name']}}" autocomplete="off">
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
                                        @elseif ($key == 'deliver')
                                            {{'发货信息'}}
                                        @else
                                        @endif
                                    </h4>
                                    <ul class="list">
                                        @foreach($rule as $value)
                                            <li>
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
                <ul class="flexlayer">
                    <li>
                        <div class="inptxt">问题类型</div>
                        <div class="inpblock">
                            <select name="trouble_type_id" id="" lay-verify="required">
                                <option value="">请选择</option>
                                @if (isset($troubles))
                                    @foreach($troubles as $problem)
                                        <option value="{{$problem['id']}}"
                                                @if($problem['id'] == $data ['trouble_type_id']) selected @endif>{{$problem['trouble_type_name']}}</option>
                                    @endforeach
                                @endif
                            </select></div>
                    </li>
                    <li>
                        <div class="inptxt">问题描述</div>
                        <div class="inpblock"><input class="layui-input" type="text" name="trouble_desc"
                                                     value="{{$data['trouble_desc']}}" lay-verify="required" autocomplete="off"/></div>
                    </li>
                    <li>
                        <div class="inptxt">是否启用</div>
                        <div class="inpblock">
                            <input type="radio" name="opening_status" value="1" lay-skin="primary" title="是"
                                   @if($data ['opening_status'] == 1) checked @endif/>
                            <input type="radio" name="opening_status" value="2" lay-skin="primary" title="否"
                                   @if($data ['opening_status'] == 2) checked @endif/>
                        </div>
                    </li>

                    <li>
                        <div class="layui-form-item" style="width: 100%">
                            <div class="layui-input-block" style="width: 100%; margin: 0!important;text-align: center">
                                <button class="layui-btn" lay-submit lay-filter="formSubmit">保存</button>&nbsp;&nbsp;&nbsp;
                                <a class="layui-btn layui-btn-primary" lay-submit="" lay-filter="" id="back">取消</a>
                            </div>
                        </div>
                    </li>
                </ul>
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
                                @if(isset($checkedDatas ['platforms_id']) && in_array($platform['id'],$checkedDatas ['platforms_id']))
                                    {{--checked--}}
                                        @endif
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

    <!--排除邮编-->
    <div id="rulecont04" class="outWindow hide">
        <div class="pop_textArea">
            <textarea class="numTxtarea" placeholder="支持格式：10000、10001 请用 、分隔" name="exclude_post_code" id="" value=""></textarea>
        </div>
    </div>
    <!--指定邮编-->
    <div id="rulecont05" class="outWindow hide">
        <div class="pop_textArea">
            <textarea class="numTxtarea" placeholder="支持格式：10000、10001、10000~10008 请用 、分隔" name="post_code" id=""
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
                        <input class="kbinp layui-disabled" disabled type="number"
                               onkeyup="limitedNumberInput(this)"
                               onblur="limitedNumberInput(this)" name="goods_length" value=""/> CM
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
                        </select> <input class="kbinp layui-disabled" disabled type="number"
                                         onkeyup="limitedNumberInput(this)"
                                         onblur="limitedNumberInput(this)" name="goods_width" value=""/> CM
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
                        </select> <input class="kbinp layui-disabled" disabled type="number"
                                         onkeyup="limitedNumberInput(this)"
                                         onblur="limitedNumberInput(this)" name="goods_height" value=""/> CM
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
                                                    onkeyup="limitedNumberInput(this)"
                                                    onblur="limitedNumberInput(this)"
                                                    name="goods_weight_value_min"/> ~ <input
                                        placeholder="最大值" class="wval mx wmax" type="number"
                                        onkeyup="limitedNumberInput(this)"
                                        onblur="limitedNumberInput(this)"
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
                            <span class="tp"><input class="wval wet edgit" type="number" name="goods_weight_value"
                                                    onkeyup="limitedNumberInput(this)"
                                                    onblur="limitedNumberInput(this)"/></span>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <!--商品SKU-->
    <div id="rulecont10" class="outWindow hide">
        <div class="pop_textArea">
            <textarea class="numTxtarea" placeholder="支持多SKU：10000、10001 请用 、分隔" name="goods_sku" id=""></textarea>
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
                            <span class="tp"><input class="wval mx" placeholder="最小值" type="number"
                                                    onkeyup="limitedNumberInput(this)"
                                                    onblur="limitedNumberInput(this)" name="orders_price_min"/> ~ <input
                                        placeholder="最大值" class="wval mx" type="number"
                                        onkeyup="limitedNumberInput(this)"
                                        onblur="limitedNumberInput(this)" name="orders_price_max"/></span>
                        </div>
                        <div class="scope">
                            <span class="tp"><input type="radio" name="orders_price_sc" title="按边界：" value="2"/></span>
                            <span class="tp"><select name="orders_price_unit_type" id="">
                                    <option value=">=">大于等于(>=)</option>
                                    <option value="<=">小于等于(<=)</option>

                                </select></span>
                            <span class="tp"><input class="wval wet" type="number"
                                                    onkeyup="limitedNumberInput(this)"
                                                    onblur="limitedNumberInput(this)"
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
                            <span class="tp"><input class="wval mx" placeholder="最小值" type="number"
                                                    onkeyup="limitedNumberInput(this)"
                                                    onblur="limitedNumberInput(this)" name="goods_count_min"/> ~ <input
                                        placeholder="最大值" class="wval mx" type="number"
                                        onkeyup="limitedNumberInput(this)"
                                        onblur="limitedNumberInput(this)" name="goods_count_max"/></span>
                        </div>
                        <div class="scope">
                            <span class="tp"><input type="radio" name="goods_count_sc" title="按边界：" value="2"/></span>
                            <span class="tp"><select name="goods_count_unit_type" id=""><option
                                            value=">=">大于等于(>=)</option><option
                                            value="<=">小于等于(<=)</option></select></span>
                            <span class="tp"><input class="wval wet" type="number"
                                                    onkeyup="limitedNumberInput(this)"
                                                    onblur="limitedNumberInput(this)"
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
    <script type="text/javascript" src="{{ asset('js/trouble_condition.js?'.time()) }}"></script>
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
                excludePostCodeReload();
                goodsSkuReload();
                emptyFieldReload();
                goodsAttrReload();
                goodsSizeReload();
                goodsWeightReload();
                goodsCountsReload();
                orderPriceReload();
                deliverWareReload();
                deliverLogisReload();
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
                @foreach ($checkedDatas['post_code'] as $k=>$v)
                    @if($v)
                    @if (end($checkedDatas['post_code']) == $v)
                        postCode += "{{$v}}";
                    @else
                        postCode += "{{$v}}、";
                    @endif
                    @endif
                @endforeach
                $('#rulecont05').find('.numTxtarea').val(postCode);
                @endif
            }

            //排除邮编重载
            function excludePostCodeReload() {
                @if(isset ($checkedDatas['exclude_post_code']))
                var postCode = '';
                @foreach ($checkedDatas['exclude_post_code'] as $k=>$v)
                    @if (end($checkedDatas['exclude_post_code']) == $v)
                    postCode += "{{$v}}";
                    @else
                    postCode += "{{$v}}、";
                    @endif
                @endforeach
                $('#rulecont04').find('.numTxtarea').val(postCode);
                @endif
            }

            //goodsSku重载
            function goodsSkuReload() {
                @if(isset ($checkedDatas['goods_sku']))
                var sku = '';
                @foreach ($checkedDatas['goods_sku'] as $k=>$v)
                    @if($v)
                    @if (end($checkedDatas['goods_sku']) == $v)
                        sku += "{{$v}}";
                    @else
                        sku += "{{$v}}、";
                    @endif
                    @endif
                @endforeach
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
                var radio_value_name = 'goods_weight_sc';//区间 或者 运算
                if ("{{$checkedDatas['goods_weight_info']['operator']}}" == '~') {
                    var radio_value = 1;
                } else {
                    var radio_value = 2;
                }
                //单位选项
                var select = $('#rulecont09 ul li .inpblock').find('select[name="' + unit + '"]');
                select.each(function () {
                    $(this).find('option[value="{{$checkedDatas['goods_weight_info']['unit']}}"]').prop("selected", 'selected');
                });
                //重量单选 区间或范围
                var radio = $('#rulecont09').find('.inpblock input:radio[value="' + radio_value + '"]');
                radio.attr("checked", true);
                if (radio_value == 1) {
                    var goods_weight_value_min = '';
                    var goods_weight_value_max = '';
                    @if (isset($checkedDatas['goods_weight_info']['value'][0]))
                        goods_weight_value_min = "{{$checkedDatas['goods_weight_info']['value'][0]}}";
                    @endif
                    @if (isset($checkedDatas['goods_weight_info']['value'][1]))
                        goods_weight_value_max = "{{$checkedDatas['goods_weight_info']['value'][1]}}";
                    @endif
                    $('#rulecont09').find('.inpblock input[name="goods_weight_value_min"]').val(goods_weight_value_min);
                    $('#rulecont09').find('.inpblock input[name="goods_weight_value_max"]').val(goods_weight_value_max);
                } else {
                    //边界运算符
                    $('#rulecont09 ul li .inpblock').find('select[name="goods_weight_unit_type"]').each(function () {
                        $(this).find('option[value="{!! $checkedDatas['goods_weight_info']['operator'] !!}"]').prop("selected", 'selected');
                    });
                    var goods_weight_value = '';
                    @if (is_array($checkedDatas['goods_weight_info']['value']))
                        goods_weight_value = "{{$checkedDatas['goods_weight_info']['value'][0]}}";
                    @else
                        goods_weight_value = "{{$checkedDatas['goods_weight_info']['value']}}";
                    @endif
                    $('#rulecont09').find('.inpblock input[name="goods_weight_value"]').val(goods_weight_value);
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
                    var goods_count_min = '';
                    var goods_count_max = '';
                    @if (isset($checkedDatas['goods_count']['value'][0]))
                        goods_count_min = "{{$checkedDatas['goods_count']['value'][0]}}";
                    @endif
                    @if (isset($checkedDatas['goods_count']['value'][1]))
                        goods_count_max = "{{$checkedDatas['goods_count']['value'][1]}}";
                    @endif

                    $('#rulecont12').find('.inpblock input[name="goods_count_min"]').val(goods_count_min);
                    $('#rulecont12').find('.inpblock input[name="goods_count_max"]').val(goods_count_max);
                } else {
                    var select_unit = $('#rulecont12 ul li .inpblock').find('select[name="' + unit + '"]');
                    select_unit.each(function () {
                        $(this).find('option[value="{!! $checkedDatas['goods_count']['operator'] !!}"]').prop("selected", 'selected');
                    });
                    var goods_count = '';
                    @if(is_array($checkedDatas['goods_count']['value']))
                        goods_count = "{{$checkedDatas['goods_count']['value'][0]}}";
                    @else
                        goods_count = "{{$checkedDatas['goods_count']['value']}}";
                    @endif
                    $('#rulecont12').find('.inpblock input[name="goods_count"]').val(goods_count);
                }
                @endif
            }

            //订单金额数据重载
            function orderPriceReload() {
                @if(isset ($checkedDatas['orders_price']))
                var unit = 'orders_price_unit';
                var radio_value_name = 'orders_price_sc';//区间 或者 运算
                if ("{{$checkedDatas['orders_price']['operator']}}" == '~') {
                    var radio_value = 1;
                } else {
                    var radio_value = 2;
                }
                //单位选项
                var select = $('#rulecont11 ul li .inpblock').find('select[name="' + unit + '"]');
                select.each(function () {
                    $(this).find('option[value="{{$checkedDatas['orders_price']['unit']}}"]').prop("selected", 'selected');
                });
                var radio = $('#rulecont11').find('.inpblock input:radio[value="' + radio_value + '"]');
                radio.attr("checked", true);
                if (radio_value == 1) {
                    var orders_price_min = '';
                    var orders_price_max = '';
                    @if (isset($checkedDatas['orders_price']['value'][0]))
                        orders_price_min = "{{$checkedDatas['orders_price']['value'][0]}}";
                    @endif
                    @if (isset($checkedDatas['orders_price']['value'][1]))
                        orders_price_max = "{{$checkedDatas['orders_price']['value'][1]}}";
                    @endif
                    $('#rulecont11').find('.inpblock input[name="orders_price_min"]').val(orders_price_min);
                    $('#rulecont11').find('.inpblock input[name="orders_price_max"]').val(orders_price_max);
                } else {
                    $('#rulecont11 ul li .inpblock').find('select[name="orders_price_unit_type"]').each(function () {
                        $(this).find('option[value="{!! $checkedDatas['orders_price']['operator'] !!}"]').prop("selected", 'selected');
                    });
                    var orders_price = '';
                    @if(is_array($checkedDatas['orders_price']['value']))
                        orders_price = "{{$checkedDatas['orders_price']['value'][0]}}";
                    @else
                        orders_price = "{{$checkedDatas['orders_price']['value']}}";
                    @endif
                    $('#rulecont11').find('.inpblock input[name="orders_price"]').val(orders_price);
                }
                @endif
            }


            //仓库数据重载
            function deliverWareReload() {
                var settle = $('#rulecont13 .appLogis').find('.logis_settle .col');
                @if(isset ($checkedDatas['warehouse_id']))
                @foreach ($checkedDatas['warehouse_id'] as $k=>$v)
                var span = $('<span data-type="3" data-value="{{$v['id']}}" data-name="warehouse_id[]" class="">' + "{{$v['warehouse_name']}}" + '</span>');
                settle.append(span)
                @endforeach
                @endif
            }

            //物流数据重载
            function deliverLogisReload() {
                var settle = $('#rulecont14 .appLogis').find('.logis_settle .col');
                @if(isset ($checkedDatas['logistic_id']))
                @foreach ($checkedDatas['logistic_id'] as $k=>$v)
                var span = $('<span data-type="3" data-value="{{$v['id']}}" data-name="logistic_id[]" class="">' + "{{$v['logistic_name']}}" + '</span>');
                settle.append(span)
                @endforeach
                @endif
            }

        });

        function limitedNumberInput(obj){
            obj.value = obj.value.replace(/[^\d.]/g,""); //清除"数字"和"."以外的字符
            obj.value = obj.value.replace(/^\./g,""); //验证第一个字符是数字
            // obj.value = obj.value.replace(/\.{2,}/g,"."); //只保留第一个, 清除多余的
            obj.value = obj.value.replace(".","$#$").replace(/\./g,"").replace("$#$",".");
            obj.value = obj.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3'); //只能输入两个小数
        };

        $(document).on('click','#back',function () {
            var index = parent.layer.getFrameIndex(window.name);
            parent.layer.close(index);
        });
    </script>
@endsection