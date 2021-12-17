@extends('layouts/new_main')
@section('head')
    <style type="text/css">
        .kbmodel_full .content-wrappers {
            background: #fff;
            padding: 20px;
        }
    </style>
@endsection
@section('content')
    <div class="kbmodel_full">
        <div class="content-wrappers">
            <form action="" method="" class="layui-form">
                <input type="hidden" name="online_amazon_goods_id"   value="{{ $goods['id']??"" }}">
                <input type="hidden" name="" value="{{$goods['amazon_category_id']}}" id="categoryInArray">

                <div class="productext" id="productext" style="display: block;">
                    <div class="produpage layui-form lastbtn">
                        <h3>平台信息</h3>
                        <table class="layui-table" lay-skin="row">
                            <tbody>
                            <tr>
                                <td>
                                    <b><i style="color: red;">*</i>店铺：</b>
                                    <div class="layui-input-block dropd">
                                        <select name="store_id" lay-verify="required" disabled>
                                            <option value="">请选择</option>
                                            @foreach($shops as $shop)
                                                <option value="{{$shop['id']}}"
                                                        @if(isset($goods['belongs_shop']) && ($goods['belongs_shop'] === $shop['id']))
                                                        selected
                                                        @endif

                                                >{{ $shop['shop_name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="10">
                                    <div class="selectedSort firsttd"><b><i style="color: red;">*</i>亚马逊分类：</b>
                                        <i id="rlselectedSort" name="rlselectedSort" class="inputi" style="margin-left: 10px;padding-left: 0px;min-width:900px; width:auto!important;">
                                            {{$goods['parentBrowseName']??''}}
                                        </i>
                                        <input type="hidden" id="rlselectedId" name="category_id" value="{{$goods['AmazonBrowseNodeID']??''}}">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <div class="wareSort clearfix">
                                        @if(isset($goods['amazon_category_id']) && !empty($goods['amazon_category_id']))
                                            <ul id="rlsort1">
                                                @if (isset($categoryInfo ['category']['first']))
                                                    @foreach($categoryInfo ['category']['first'] as $key => $val)
                                                        <li @if(in_array($val['BrowseNodeID'], $categoryInfo['categoryInArray']))class="on" @endif
                                                        onclick="rakutenCategory(false, {{$val['BrowseNodeID']}}, {{$val['categories_lv']}}, {{$key}});"
                                                            data-id="{{$val['BrowseNodeID']}}"><a href="javascript:void(0)">{{$val['BrowseNodeName']}}({{$val ['BrowseNodeName_CN']}})</a></li>
                                                    @endforeach
                                                @endif
                                            </ul>
                                            <ul id="rlsort2">
                                                @if (isset($categoryInfo ['category']['second']))
                                                    @foreach($categoryInfo ['category']['second'] as $key => $val)
                                                        <li @if(in_array($val['BrowseNodeID'], $categoryInfo['categoryInArray']))class="on"@endif
                                                        onclick="rakutenCategory(false, {{$val['BrowseNodeID']}}, {{$val['categories_lv']}}, {{$key}});"
                                                            data-id="{{$val['BrowseNodeID']}}"><a href="javascript:void(0)">{{$val['BrowseNodeName']}}({{$val ['BrowseNodeName_CN']}})</a></li>
                                                    @endforeach
                                                @endif
                                            </ul>
                                            <ul id="rlsort3">
                                                @if (isset($categoryInfo ['category']['third']))
                                                    @foreach($categoryInfo ['category']['third'] as $key => $val)
                                                        <li @if(in_array($val['BrowseNodeID'], $categoryInfo['categoryInArray']))class="on"@endif
                                                        onclick="rakutenCategory(false, {{$val['BrowseNodeID']}}, {{$val['categories_lv']}}, {{$key}});"
                                                            data-id="{{$val['BrowseNodeID']}}"><a href="javascript:void(0)">{{$val['BrowseNodeName']}}({{$val ['BrowseNodeName_CN']}})</a></li>
                                                    @endforeach
                                                @endif
                                            </ul>
                                        @else
                                            <ul id="rlsort1"></ul>
                                            <ul id="rlsort2" style="display: none;"></ul>
                                            <ul id="rlsort3" style="display: none;"></ul>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="produpage layui-form textr">
                        <h3>上架信息</h3>
                        <table class="layui-table shangjiainfor" lay-skin="nob">
                            <tbody>
                            <tr>
                                <td><b><i style="color: red;">*</i>SellerSKU：</b></td>
                                <td><input type="text" class="layui-disabled " autocomplete="off" disabled lay-verify="required" value="{{$goods['seller_sku']??''}}" /></td>
                                <td><b><i style="color: red;">*</i>商品编码：</b></td>
                                <td class="spe">
                                    <select disabled>
                                        <option value="">请选择</option>
                                        <option value="1" @if(!empty($goods['upc']) && !empty($goods['upc'])) selected @endif>UPC</option>
                                        <option value="2" @if(!empty($goods['ASIN']) && !empty($goods['ASIN'])) selected @endif>ASIN</option>
                                    </select>
                                    <input type="text" autocomplete="off" class="layui-disabled " disabled  value="{{!empty($goods['upc']) && !empty($goods['upc'])?$goods['upc']:$goods['ASIN']}}" style="display: inline-block; width:300px"/>
                                </td>
                                <td><b><i style="color: red;">*</i>物品状态：</b></td>
                                <td>
                                    <select name="goods_status" lay-verify="required">
                                        <option value="new">新品(new)</option>
                                        {{--<option value="new">中古 - ほぼ新品(new)</option>--}}
                                        {{--<option value="new">中古 - 良い(new)</option>--}}
                                        {{--<option value="再生品">再生品</option>--}}
                                        {{--<option value="中古 - 非常に良い">中古 - 非常に良い</option>--}}
                                        {{--<option value="中古 - 可">中古 - 可</option>--}}
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><b><i style="color: red;">*</i>商品标题：</b></td>
                                <td><input type="text" name="title" autocomplete="off" value="{{$goods['title']??''}}" /></td>
                                <td><b>关键词1：</b></td>
                                <td><input type="text" name="keywords" autocomplete="off" value="{{$goods['goods_keywords1']??''}}"/></td>
                                <td><b>商品标签1：</b></td>
                                <td><input type="text" autocomplete="off" value="{{$goods['goods_label1']??''}}" name="label"/></td>
                            </tr>
                            <tr>
                                <td><b><i style="color: red;">*</i>商品品牌：</b></td>
                                <td><input type="text" name="brand" autocomplete="off" value="{{$goods['brand']??''}}" /></td>
                                <td><b>关键词2：</b></td>
                                <td><input type="text" name="keywords" autocomplete="off" value="{{$goods['goods_keywords2']??''}}" /></td>
                                <td><b>商品标签2：</b></td>
                                <td><input type="text" autocomplete="off" value="{{$goods['goods_label2']??''}}" name="label"/></td>
                            </tr>
                            <tr>
                                <td><b><i style="color: red;">*</i>制造商：</b></td>
                                <td><input type="text" name="manufacturer" autocomplete="off" value="{{$goods['manufacturer']??''}}" /></td>
                                <td><b>关键词3：</b></td>
                                <td><input type="text" name="keywords" autocomplete="off" value="{{$goods['goods_keywords3']??''}}" /></td>
                                <td><b>商品标签3：</b></td>
                                <td><input type="text" autocomplete="off" value="{{$goods['goods_label3']??''}}" name="label"/></td>
                            </tr>
                            <tr>
                                <td><b>商品颜色：</b></td>
                                <td>
                                    <input type="text" autocomplete="off" maxlength="20" value="{{$goods['color']??''}}" name="color"/>
                                    {{--<select name="color"  id="goods_color">--}}
                                        {{--<option value="black">黑色</option>--}}
                                        {{--<option value="gray">灰色</option>--}}
                                        {{--<option value="white">白色</option>--}}
                                        {{--<option value="buff">米色</option>--}}
                                        {{--<option value="red">红色</option>--}}
                                        {{--<option value="pink">粉红色</option>--}}
                                        {{--<option value="orange">橙色</option>--}}
                                        {{--<option value="yellow">黄色</option>--}}
                                        {{--<option value="green">绿色</option>--}}
                                        {{--<option value="blue">蓝色</option>--}}
                                        {{--<option value="purple">紫色</option>--}}
                                        {{--<option value="silvery">银色</option>--}}
                                        {{--<option value="gold">金色</option>--}}
                                        {{--<option value="more">多种颜色</option>--}}
                                    {{--</select>--}}
                                </td>
                                <td><b>关键词4：</b></td>
                                <td><input type="text" name="keywords" autocomplete="off" value="{{$goods['goods_keywords4']??''}}"/></td>
                                <td><b>商品标签4：</b></td>
                                <td><input type="text" autocomplete="off" value="{{$goods['goods_label4']??''}}" name="label" /></td>
                            </tr>
                            <tr>
                                <td><b>商品型号：</b></td>
                                <td>
                                    <input type="text" autocomplete="off" maxlength="20" value="{{$goods['goods_size']??''}}" name="goods_size"/>
                                    {{--<select name="goods_size"  id="goods_size">--}}
                                        {{--<option value="Large">Large</option>--}}
                                        {{--<option value="Medium">Medium</option>--}}
                                        {{--<option value="Small">Small</option>--}}
                                        {{--<option value="X-Large">X-Large</option>--}}
                                        {{--<option value="X-Small">X-Small</option>--}}
                                        {{--<option value="XX-Large">XX-Large</option>--}}
                                        {{--<option value="XX-Small">XX-Small</option>--}}
                                        {{--<option value="XXX-Large">XXX-Large</option>--}}
                                        {{--<option value="XXXX-Small">XXX-Small</option>--}}
                                        {{--<option value="XXXX-Large">XXXX-Large</option>--}}
                                        {{--<option value="XXXX-Small">XXXX-Small</option>--}}
                                        {{--<option value="XXXXX-Large">XXXXX-Large</option>--}}
                                        {{--<option value="XXXXX-Small">XXXXX-Small</option>--}}
                                    {{--</select>--}}
                                </td>
                                <td><b>关键词5：</b></td>
                                <td><input type="text" autocomplete="off" name="keywords" value="{{$goods['goods_keywords5']??''}}"/></td>
                                <td><b>商品标签5：</b></td>
                                <td><input type="text" autocomplete="off" value="{{$goods['goods_label5']??''}}" name="label"/></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="produpage layui-form">
                        <h3>商品信息</h3>
                        <table class="layui-table shangpininfor" lay-skin="nob">
                            <tbody>
                            <tr>
                                <td><i style="color: red;">*</i>商品名称：</td>
                                <td class="shirt"><input type="text" autocomplete="off" name="goods_name"  value="{{$goods['goods_name']??''}}" /></td>
                                <td><i style="color: red;">*</i>商品属性：</td>
                                <td>
                                    <select name="goods_attribute_id" lay-verify="required">
                                        <option value="1" @if(isset($goods['goods_attribute_id']) && ($goods['goods_attribute_id'] == 1)) selected @endif>普货</option>
                                        <option value="2" @if(isset($goods['goods_attribute_id']) && ($goods['goods_attribute_id'] == 2)) selected @endif>含电池</option>
                                        <option value="3" @if(isset($goods['goods_attribute_id']) && ($goods['goods_attribute_id'] == 3)) selected @endif>纯电池</option>
                                    </select>
                                </td>
                                <td><i style="color: red;">*</i>商品重量：</td>
                                <td class="shirt"><input type="text" autocomplete="off" name="goods_weight" lay-verify="required" value="{{$goods['goods_weight']??''}}" /><span>KG</span></td>
                            </tr>
                            <tr>
                                <td><i style="color: red;">*</i>商品尺寸：</td>
                                <td colspan="5" class="shirt">
                                    <input type="text" autocomplete="off" placeholder="长" name="goods_length" lay-verify="required" value="{{$goods['goods_length']??''}}" />
                                    <span>CM</span><i>×</i>
                                    <input type="text" autocomplete="off" placeholder="宽" name="goods_width" lay-verify="required" value="{{$goods['goods_width']??''}}" />
                                    <span>CM</span><i>×</i>
                                    <input type="text" autocomplete="off" placeholder="高" name="goods_height" lay-verify="required" value="{{$goods['goods_height']??''}}" />
                                    <span>CM</span>
                                </td>
                            </tr>
                            <tr>
                                <td><i style="color: red;">*</i>商品描述：</td>
                                <td colspan="5">
                                    <textarea name="goods_description" lay-verify="required"  placeholder="请输入文字"  class="layui-textarea">{{$goods['goods_description']??''}}</textarea>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="produpage layui-form">
                        <h3>价格信息</h3>
                        <table class="layui-table shangpininfor priceinfor" lay-skin="nob">
                            <tbody>
                            <tr>
                                <td><b><i style="color: red;">*</i>销售价格：</b></td>
                                <td class="spe">
                                    <select name="currency_code" lay-verify="required">
                                        <option value="">请选择</option>
                                        @foreach($currency as $key => $val)
                                            {{--<option value="{{$val['currency_form_code']}}" @if (!empty($goods['currency_code']) && $goods['currency_code'] == $val['currency_form_code']) selected @endif @if (empty($goods['currency_code']) && $val['currency_form_code'] == 'JPY') selected @endif>{{$val['currency_form_name']}}-{{$val['currency_form_code']}}</option>--}}
                                            <option value="{{$val['currency_form_code']}}" @if (!empty($goods['currency_code']) && $goods['currency_code'] == $val['currency_form_code']) selected @endif @if (empty($goods['currency_code']) && $val['currency_form_code'] == 'JPY') selected @endif>{{$val['currency_form_name']}}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" name="sale_price" autocomplete="off" value="{{$goods['sale_price']??''}}" onkeyup="value=value.match(/\d+\.?\d{0,2}/,'')" />
                                </td>
                                <td><b><i style="color: red;">*</i>平台库存：</b></td>
                                <td><input type="number" min="1" max="99999999" lay-verify="required" name="platform_in_stock" autocomplete="off"  value="{{$goods['platform_in_stock']??''}}" onkeyup="value=value.match(/\d+/,'')"/></td>
                            </tr>
                            <tr>
                                <td><b>促销价格：</b></td>
                                <td><input type="text" autocomplete="off" name="promotion_price" value="{{empty($goods['promotion_price']) ? '' : $goods['promotion_price']}}" onkeyup="value=value.match(/\d+\.?\d{0,2}/,'')"/></td>
                                <td><b>促销时间：</b></td>
                                <td>
                                    <div class="layui-input-inline">
                                        <input type="text" autocomplete="off" name="promotion_time" @if(!empty($goods['promotion_start_time']) && !empty($goods['promotion_end_time'])) value="{{$goods['promotion_start_time'].' - '.$goods['promotion_end_time']}}" @endif  class="layui-input" id="test10" placeholder=" - ">
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="produpage">
                        <h3>商品图片</h3>
                        <div class="layui-tab martop">
                            <ul class="layui-tab-title">
                                <li class="layui-this">主图</li>
                                <li>附图</li>
                            </ul>
                            <div class="layui-tab-content">
                                <div class="layui-tab-item layui-show">
                                    <div class="layui-upload uploadoneimg">
                                        <i>本地上传：</i>
                                        <button type="button" class="layui-btn" id="rltest1">上传图片</button>
                                        <span>支持 <b>jpg 、jpeg 、gif 、png</b>图片格式</span>
                                        <div class="layui-upload-list">
                                            <div class="rlpicarea">
                                                <img class="layui-upload-img" onerror="this.src='/img/imgNotFound.jpg'" id="rldemo1" @if(!empty($goods['img_url'])) src="{{url('showImage').'?path='.$goods['img_url']}}" @else
                                                {{--src="" --}}
                                                        @endif >
                                                <input type="hidden" name="img_url" value="{{$goods['img_url']??''}}">
                                                @if ($goods['img_url'])
                                                    <span class="rldelet"><i class="layui-icon">ဆ</i></span>
                                                @endif
                                            </div>
                                            <p id="rldemoText"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="layui-tab-item uploadoneimg">
                                    <div class="layui-upload">
                                        <i>本地上传：</i>
                                        <button type="button" class="layui-btn" id="rltest2">多图片上传</button>
                                        <span>支持 <b>jpg 、jpeg 、gif 、png</b>图片格式</span>
                                        <blockquote class="layui-elem-quote layui-quote-nm" style="margin-top: 10px;">
                                            预览图：
                                            <div class="layui-upload-list" id="rldemo2">
                                                @if(isset($goods['goods_pics']) && count($goods['goods_pics'])>0)
                                                    @foreach($goods['goods_pics'] as $val)
                                                        <div class="rlpicarea1">
                                                    <span class="rldelet1" data-id="{{ $val['id'] }}"
                                                          {{--style="right: 10px;position: absolute;cursor: pointer;top: 0px;right: 0px;display: block;width: 30px;height: 30px;line-height: 30px;color: #fff;background: rgba(0,0,0,.6);text-align: center;"--}}
                                                    >
                                                        <i class="layui-icon">ဆ</i></span>
                                                            <img onerror="this.src='/img/imgNotFound.jpg'"  src="{{url('showImage').'?path='.$val['link']}}" alt="" class="layui-upload-img">
                                                            <input type="hidden" name="pics" value="{{$val['link']}}" />
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </blockquote>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="produpage layui-form" style="text-align: center">
                    <button class="layui-btn" lay-submit="" lay-filter="searBtn">保存</button>&nbsp;&nbsp;&nbsp;
                    <a class="layui-btn layui-btn-primary" lay-submit="" lay-filter="" id="back">取消</a>
                </div>

            </form>
        </div>
    </div>
@endsection
@section('javascripts')
    <!-- 亚马逊商品类目三联选择 -->
    <script type="text/javascript" src="{{asset('js/jquery.rlsort.amazon.js')}}?date={{time()}} "></script>
    <script>
        layui.use(['form', 'laydate','table','element','upload'], function(){
            var layer = layui.layer, form = layui.form, laypage = layui.laypage, laydate = layui.laydate;
            var $ = layui.jquery;
            upload = layui.upload;

            var img_import = '' ; //主图
            var img_sup = [] ; //附图

            //商品采集认领单张图片上传
            var uploadInst = upload.render({
                elem: '#rltest1'
                ,url: '/photo/upload'
                ,data:{
                    '_token':"{{ csrf_token() }}"
                }
                ,before: function(obj){
                    $('.layui-upload-list .rlpicarea').find('.rldelet').remove();
                    $('.layui-upload-list .rlpicarea').find('input[name="img_url"]').remove();
                    $('.layui-upload-list .rlpicarea').find('.layui-upload-img').remove();
                    //预读本地文件示例，不支持ie8
                    obj.preview(function(index, file, result){
                        $('.rlpicarea').append('<img onerror="this.src=\'/img/imgNotFound.jpg\'"  src="'+result+'" class="layui-upload-img" id="rldemo1">' +
                            '<span class="rldelet"><i class="layui-icon">&#x1006;</i></span>' +
                            '<input type="hidden" name="img_url" value="">');
                    });
                }
                ,done: function(res){
                    //如果上传失败
                    if(res.code !== 200){
                        return layer.msg('上传失败',{"icon":5});
                    }

                    // img_import = res.data.src ;
                    $('input[name="img_url"]').val(res.data.src);
                    layer.msg('图片上传成功!' ,{'icon':6});
                }
                ,error: function(){
                    //演示失败状态，并实现重传
                    var demoText = $('#rldemoText');
                    demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-xs demo-reload">重试</a>');
                    demoText.find('.demo-reload').on('click', function(){
                        uploadInst.upload();
                    });
                }
            });

            //商品采集认领多张图片上传
            upload.render({
                elem: '#rltest2'
                ,url: '/photo/upload'
                ,data: {
                    '_token':"{{ csrf_token() }}"
                }
                ,multiple: true
                ,before: function(obj){
                    //预读本地文件示例，不支持ie8
                    obj.preview(function(index, file, result){
//                        $('#rldemo2').append('<div class="rlpicarea1"><span class="rldelet1"><i class="layui-icon">&#x1006;</i></span><img src="'+ result +'" alt="'+ file.name +'" class="layui-upload-img"><input type="hidden" class="pics" name="pics" value=""><div class="rlpicname1"></div></div>');
                        $('#rldemo2').append('<div class="rlpicarea1"><span class="rldelet1"><i class="layui-icon">&#x1006;</i></span><img src="'+ result +'" alt="'+ file.name +'" class="layui-upload-img"><input type="hidden" class="pics '+index+'" name="pics" value=""></div>');
                    });
                }
                ,done: function(res,index, upload){
                    //上传完毕
                    //如果上传失败
                    if(res.code !== 200){
                        return layer.msg('上传失败');
                    }

                    var input_pics = $('input[name="pics"]');
                    //隐患 并未按对应顺序写入地址信息
                    $.each(input_pics,function(i,item){
                        if ($(item).hasClass(index)) {
                            $(this).val(res.data.src);
                        }
                    });
                    layer.msg('图片上传成功!' ,{'icon':6});
                }
            });

            // 商品采集认领单图
            $(document).on('click','.rlpicarea .rldelet',function(){
                $(this).parents('.layui-upload-list').find('#rldemoText').html('');
                $(this).parent().find('#rldemo1').removeAttr('src');
                $(this).parent().find('.layui-upload-img').remove();
                $(this).parent().find('input[name="img_url"]').remove();
                img_import = '' ;
                $(this).remove();

            })

            // 商品采集认领多图片
            $(document).on('click','.rlpicarea1 .rldelet1',function(){
                $(this).parent().find('.layui-upload-img').remove();
                $(this).parent().find('input[name="pics"]').remove();
                $(this).remove();
            });

            //日期时间范围
            laydate.render({
                elem: '#test10'
                ,type: 'datetime'
                ,range: true
            });

            form.on('submit(searBtn)', function(data){
                var info = data.field;
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                var inputs = $('input[name="keywords"]');
                var keywords = new Array();
                var label = new Array();
                var pics = new Array();
                var inputs_label = $('input[name="label"]');
                var inputs_pics = $('input[name="pics"]');
                //促销价格 和促销时间为一组参数
                if (info.promotion_price != '' ) {
                    if (parseInt(info.promotion_price) > 0) {
                        if (info.promotion_time == '') {
                            layer.msg('设置促销价格时请设置促销时间');
                            return false
                        }
                    }
                }
                if (info.promotion_time != '' ) {
                    if (info.promotion_price == '') {
                        layer.msg('设置促销时间时请设置促销价格');
                        return false
                    }
                }
                if (info.category_id == '') {
                    layer.msg('亚马逊分类信息为必选项');
                    return false
                }
                var category_info = $("#rlselectedSort").html();
                info.category_info = category_info;
                $.each(inputs,function() {
                    keywords.push($(this).val());
                });
                $.each(inputs_label,function() {
                    label.push($(this).val());
                });
                $.each(inputs_pics,function(){
                    pics.push($(this).val());
                });

                $.ajax({
                    url: "/Goods/onlineAmazon/editSave"
                    , type: "post"
                    , dataType: "json"
                    , data: {
                        '_token': "{{ csrf_token() }}"
                        ,'param':info
                        ,'pics':pics
                        ,'keywords':keywords
                        ,'label':label
                    }
                    , success: function (response) {
                        if (response.Status) {
                            layer.msg(response.Message, {time:2000, icon: 1});
                            setTimeout(function () {
                                parent.layer.close(index);
                                parent.layui.table.reload('EDtable');
                            },3000);
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
                    }

                    ,error: function (e, x, t) {
                        layer.alert('操作失败！！！',{icon:5});
                    }
                });
                return false;
            });

            function colorReload() {
                var select = $('#goods_color');
                @if(isset ($goods['color']))
                select.each(function () {
                    $(this).find('option[value="{{$goods['color']}}"]').prop("selected", 'selected');
                });
                @endif
            }

            function sizeReload() {
                var select = $('#goods_size');
                @if(isset ($goods['goods_size']))
                select.each(function () {
                    $(this).find('option[value="{{$goods['goods_size']}}"]').prop("selected", 'selected');
                });
                @endif
            }

            $(document).ready(function () {
//                colorReload();
//                sizeReload();
//                form.render();
                let hasCategory = $("#categoryInArray").val();
                if (!hasCategory) {
                    rakutenCategory();
                } else {
                    let sortedPixel = 0;
                    let sortedElement = $(".wareSort").find('ul');
                    $.each(sortedElement, function (k, v) {
                        sortedPixel = $(this).find('.on').prop('offsetTop');
                        $(this).scrollTop(sortedPixel - 30)
                    })
                }
                restoreNode();
            });

            $(document).on('click','#back',function () {
                var index = parent.layer.getFrameIndex(window.name);
                parent.layer.close(index);
            });
        });
    </script>
@endsection
