@extends('layouts/new_main')
@section('head')
    <style type="text/css">
        .spe .layui-form-select {
            width: 155px !important;
        }
        .content-wrapper {
            margin: 0 !important;
        }
        .catachoose {
            margin: 20px 0;
        }
    </style>
@endsection
@section('content')
    <div class="kbmodel_full">
        <div class="content-wrapper">
            <form action="" method="" class="layui-form">
                {{--            <input type="hidden" name="firstCategory"  id="firstCategory" value="{{$goodsInfo['category_id_1']??''}}">--}}
                {{--            <input type="hidden" name="secondCategory"  id="secondCategory" value="{{$goodsInfo['category_id_2']??''}}">--}}
                {{--            <input type="hidden" name="thirdCategory"  id="thirdCategory" value="{{$goodsInfo['category_id_3']??''}}">--}}
                {{--            <input type="hidden" name="plat"       value="{{ $goodsInfo['plat']??"" }}">--}}
                {{--            <input type="hidden" name="from_url"   value="{{ $goodsInfo['url']??"" }}">--}}
                {{--            <input type="hidden" name="lotte_id"   value="{{ $goodsInfo['id']??"" }}">--}}
                {{--            <input type="hidden" name="goods_id"   value="{{ $goodsInfo['goods_id']??"" }}">--}}
                {{--            <input type="hidden" name="xxType"   value="2" id="xxType">--}}
                <input type="hidden" name="" value="{{$goodsInfo['rakuten_category_id']}}" id="categoryInArray">

                <div class="productext" id="productext" style="display: block;">
                    <div class="produpage layui-form lastbtn">
                        <h3>平台信息</h3>
                        <table class="layui-table" lay-skin="row">
                            <tbody>
                            <tr>
                                <td>
                                    <b><i style="color: red;">*</i>店铺：</b>
                                    <div class="layui-input-block" style="display: inline-block;margin-left: 26px!important;">
                                        <input type="text" class="layui-input layui-disabled" disabled value="{{$goodsInfo['shops']['shop_name']}}">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6"><div class="selectedSort firsttd"><b><i style="color: red;">*</i>乐天分类：</b>
                                        <i id="rlselectedSort" class="inputi" style="width: 800px!important;">
                                            {{$goodsInfo['rakuten_category_JP']??''}}
                                        </i>
                                    </div></td>
                            </tr>
                            <tr>
                                <input type="hidden" name="rakuten_category_id" value="{{$goodsInfo['rakuten_category_id']}}" id="rakuten_category_id">
                                <td colspan="6">
                                    @if(!empty($goodsInfo['rakuten_category_id']))
                                        <div class="wareSort clearfix">
                                            <ul id="rlsort1">
                                                @foreach($category['first'] as $key => $val)
                                                    <li
                                                            @if(in_array($val['genreId'], $goodsInfo['categoryInArray']))class="on"@endif
                                                    onclick="rakutenCategory(false, {{$val['genreId']}}, {{$val['categories_lv']}}, {{$key}});"
                                                            data-id="{{$val['genreId']}}"><a href="javascript:void(0)">{{$val['genreName']}}</a></li>
                                                @endforeach
                                            </ul>
                                            <ul id="rlsort2">
                                                @foreach($category['second'] as $key => $val)
                                                    <li
                                                            @if(in_array($val['genreId'], $goodsInfo['categoryInArray']))class="on"@endif
                                                    onclick="rakutenCategory(false, {{$val['genreId']}}, {{$val['categories_lv']}}, {{$key}});"
                                                            data-id="{{$val['genreId']}}"><a href="javascript:void(0)">{{$val['genreName']}}</a></li>
                                                @endforeach
                                            </ul>
                                            <ul id="rlsort3">
                                                @foreach($category['third'] as $key => $val)
                                                    <li
                                                            @if(in_array($val['genreId'], $goodsInfo['categoryInArray']))class="on"@endif
                                                    onclick="rakutenCategory(false, {{$val['genreId']}}, {{$val['categories_lv']}}, {{$key}});"
                                                            data-id="{{$val['genreId']}}"><a href="javascript:void(0)">{{$val['genreName']}}</a></li>
                                                @endforeach
                                            </ul>

                                            <ul id="rlsort4" @if(empty($category['four'])) style="display: none;" @endif >
                                                @if(!empty($category['four']))
                                                    @foreach($category['four'] as $key => $val)
                                                        <li
                                                                @if(in_array($val['genreId'], $goodsInfo['categoryInArray']))class="on"@endif
                                                        onclick="rakutenCategory(false, {{$val['genreId']}}, {{$val['categories_lv']}}, {{$key}});"
                                                                data-id="{{$val['genreId']}}"><a href="javascript:void(0)">{{$val['genreName']}}</a></li>
                                                    @endforeach
                                                @endif
                                            </ul>
                                        </div>
                                    @else
                                        <div class="wareSort clearfix">
                                            <ul id="rlsort1"></ul>
                                            <ul id="rlsort2" style="display: none;"></ul>
                                            <ul id="rlsort3" style="display: none;"></ul>
                                            <ul id="rlsort4" style="display: none;"></ul>
                                        </div>
                                    @endif

                                </td>
                            </tr>
                            {{--<tr>
                                <td><button class="layui-btn layui-btn-normal">确定</button><button class="layui-btn layui-btn-danger">重置</button></td>
                            </tr>--}}
                            </tbody>
                        </table>
                    </div>

                    <div class="produpage layui-form">
                        <table class="layui-table" lay-skin="nob">
                            <colgroup>
                                <col width="100">
                                <col>
                            </colgroup>
                            <tbody>
                            <tr>
                                <td><i style="color: red;">*</i>目录ID：</td>
                                <td>
                                    <div class="catachoose">
                                        <span><input type="radio" lay-filter="catelog" @if(isset($goodsInfo['catalogId']) && $goodsInfo['catalogId']) checked @endif name="catalog" value="1" title="JAN代码"></span>
                                        <input type="text" class="layui-input" name="catalogId" value="{{$goodsInfo['catalogId']??''}}" class="catainput" readonly="readonly" style="width: 30%; display: inline-block; margin-right: 1rem" />
                                        <label>(半角数字)</label>
                                    </div>
                                    <div class="catachoose">
                                        <span><input type="radio" lay-filter="catelog" name="catalog" value="2" @if(isset($goodsInfo['catalogIdExemptionReason'])) checked @endif title="没有目录ID的原因"></span>
                                        <div style="width: 200px; display: inline-block">
                                            <select class="cataselect" name="reason" @if(!isset($goodsInfo['catalogIdExemptionReason'])) disabled @endif>
                                                <option value=""></option>
                                                <option value="1" @if(isset($goodsInfo['catalogIdExemptionReason']) && ($goodsInfo['catalogIdExemptionReason'] == 1)) selected @endif>セット商品</option>
                                                <option value="2" @if(isset($goodsInfo['catalogIdExemptionReason']) && $goodsInfo['catalogIdExemptionReason'] == 2) selected @endif>サービス商品</option>
                                                <option value="3" @if(isset($goodsInfo['catalogIdExemptionReason']) && $goodsInfo['catalogIdExemptionReason'] == 3) selected @endif>店舗オリジナル商品</option>
                                                <option value="4" @if(isset($goodsInfo['catalogIdExemptionReason']) && $goodsInfo['catalogIdExemptionReason'] == 4) selected @endif>項目選択肢在庫商品</option>
                                                <option value="5" @if(isset($goodsInfo['catalogIdExemptionReason']) && $goodsInfo['catalogIdExemptionReason'] == 5) selected @endif>該当製品コードなし</option>
                                            </select>
                                        </div>
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

                                <td>商品管理番号：</td>
                                <td><input name="itemUrl" disabled class="layui-input layui-disabled " value="{{ $goodsInfo['cmn']??"" }}" type="text"  /></td>
                                <td>商品番号：</td>
                                <td colspan="4"><input name="sku" disabled class="layui-input layui-disabled " value="{{ $goodsInfo['sku']??"" }}" type="text"  /></td>
                            </tr>
                            <tr>
                                <td><b><label style="color: red;">*</label>销售价格：</b></td>
                                <td>
                                    <input name="sale_price" value="{{ $goodsInfo['sale_price']??"" }}" type="text" class="shirtw" />
                                </td>

                                <td><i style="color: red;">*</i>币种：</td>
                                <td>
                                    <select name="currency_code" lay-verify="required">
                                        <option value="">请选择</option>
                                        @foreach($currency as $key => $val)
                                            <option value="{{$val['currency_form_code']}}" @if (!empty($goodsInfo['currency_code']) && $goodsInfo['currency_code'] == $val['currency_form_code'])selected @endif
                                            @if (empty($goodsInfo['currency_code']) && $val['currency_form_code'] == 'JPY') selected @endif>{{$val['currency_form_name']}} - {{$val['currency_form_code']}}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>

                                <td><i style="color: red;">*</i>平台库存：</td>
                                <td colspan="2"><input  name="platform_in_stock" class="layui-input" value="{{ $goodsInfo['platform_in_stock']??"" }}"  type="number" min="1" max="99999999" autocomplete="off" lay-verify="required" /></td>
                            </tr>
                            <tr>
                                <td><i style="color: red;">*</i>商品名称：</td>
                                <td><input name="goods_name" type="text" class="layui-input" value="{{$goodsInfo['goods_name']??"" }}" /></td>
                                <td><i style="color: red;">*</i>商品标题：</td>
                                <td colspan="4"><input type="text" class="layui-input" style="width: 212px;" name="goods_title" value="{{$goodsInfo['title']??"" }}" /></td>
                            </tr>
                            <tr>
                                <td><i style="color: red;">*</i>商品描述：</td>
                                <td colspan="6"><textarea name="goods_description" placeholder="请输入文字" class="layui-textarea">{{$goodsInfo['goods_description']??"" }}</textarea></td>
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
                                                <img onerror="this.src='/img/imgNotFound.jpg'"  class="layui-upload-img" id="rldemo1" src="{{ url('showImage').'?path='.($goodsInfo['img_url']??'') }}">
                                                <input type="hidden" name="img_import" class="img_import" value="{{$goodsInfo['img_url']??''}}">
                                                @if ($goodsInfo['img_url'])
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
                                                @if(isset($goodsInfo['pictures']) && count($goodsInfo['pictures'])>0)
                                                    @foreach($goodsInfo['pictures'] as $key => $val)
                                                        <div class="rlpicarea1">
                                                    <span class="rldelet1" data-id="{{ $val['id']??'' }}"
                                                          {{--style="right: 10px;position: absolute;cursor: pointer;top: 0px;right: 0px;display: block;width: 30px;height: 30px;line-height: 30px;color: #fff;background: rgba(0,0,0,.6);text-align: center;"--}}
                                                    ><i class="layui-icon">ဆ</i></span>
                                                            <img onerror="this.src='/img/imgNotFound.jpg'"  src="{{ url('showImage').'?path='.$val['link']??'' }}" alt="" class="layui-upload-img">
                                                            <input type="hidden" name="imgsup[]" class="img_sup" value="{{$val['link']??''}}">
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
                    <button class="layui-btn sub" lay-submit="" lay-filter="searBtn">保存</button>
                    &nbsp;
                    {{--                <button class="layui-btn" lay-submit="" lay-filter="searBtn">上架</button>--}}
                    &nbsp;
                    <a class="layui-btn layui-btn-primary" lay-submit="" lay-filter="" id="back">取消</a>
                </div>

            </form>
        </div>
    </div>
@endsection
@section('javascripts')
    <!-- 商品认领采集三联选择 -->
    <script type="text/javascript" src="{{asset('js/jquery.rlsort.rakuten.js')}}?date={{time()}} "></script>
    <script>
        layui.use(['form', 'laydate','table','element','upload'], function(){
            var layer = layui.layer, form = layui.form, laypage = layui.laypage, laydate = layui.laydate;
            var element = layui.element;
            var $ = layui.jquery;
            upload = layui.upload;

            var img_import = '' ; //主图
            var import_name = '';

            //商品采集认领单张图片上传
            var uploadInst = upload.render({
                elem: '#rltest1'
                ,url: '/photo/upload'
                ,data:{
                    '_token':"{{ csrf_token() }}"
                }
                ,before: function(obj){
                    $('.sub').attr('disabled',true);
                    $('.layui-upload-list .rlpicarea').find('.rldelet').remove();
                    $('.layui-upload-list .rlpicarea').find('input[name="img_import"]').remove();
                    $('.layui-upload-list .rlpicarea').find('#rldemo1').removeAttr('src');
                    $('.layui-upload-list .rlpicarea').find('.layui-upload-img').remove();
                    //预读本地文件示例，不支持ie8
                    obj.preview(function(index, file, result){
                        $('.rlpicarea').append('<img onerror="this.src=\'/img/imgNotFound.jpg\'"  src="'+result+'" class="layui-upload-img" id="rldemo1">' +
                            '<span class="rldelet"><i class="layui-icon">&#x1006;</i></span>' +
                            '<input type="hidden" name="img_import" class="img_import" value="">');
                    });
                }
                ,done: function(res){
                    //如果上传失败
                    if(res.code !== 200){
                        return layer.msg('上传失败',{"icon":5});
                    }

                    img_import = res.data.src ;
                    $('.img_import').val(img_import);
                    layer.msg('图片上传成功!' ,{'icon':6});
                    $('.sub').attr('disabled',false);
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
                    $('.sub').attr('disabled',true);
                    //预读本地文件示例，不支持ie8
                    obj.preview(function(index, file, result){
                        $('#rldemo2').append('' +
                            '<div class="rlpicarea1"><span class="rldelet1"><i class="layui-icon">&#x1006;</i></span><img  onerror="this.src=\'/img/imgNotFound.jpg\'" src="'+ result +'" alt="'+ file.name +'" class="layui-upload-img">' +
                            '<input type="hidden" class="img_sup '+index+'" name="imgsup[]" value=""><input type="hidden" class="sup_name '+index+'" name="sup_name[]" value=""></div>'
                        );
                    });
                }
                ,done: function(res,index, upload){
                    if(res.code !== 200){
                        return layer.msg('上传失败');
                    }
                    var sup = $('.sup_name');
                    var hide = $('.img_sup');
                    $.each(hide,function(i,item) {
                        if ($(item).hasClass(index)) {
                            $(this).val(res.data.src);
                        }
                    });
                    $.each(sup,function(i,item) {
                        var sub_url = res.data.src.split('/');
                        var sub_name = sub_url[1].split('.')[0];
                        if ($(item).hasClass(index)) {
                            $(this).val(sub_name);
                        }
                    })
                    $('.sub').attr('disabled',false);
                }
            });

            // 商品采集认领单图
            $(document).on('click','.rlpicarea .rldelet',function(){
                $(this).parents('.layui-upload-list').find('#rldemoText').html('');
                $(this).parent().find('#rldemo1').removeAttr('src');
                $(this).parent().find('.layui-upload-img').remove();
                $(this).parent().find('input[name="img_import"]').remove();
                img_import = '' ;
                $(this).remove();
            })

            // 商品采集认领多图片
            $(document).on('click','.rlpicarea1 .rldelet1',function(){
                $(this).parent().find('.layui-upload-img').remove();
                $(this).parent().find('.img_sup').remove();
                $(this).remove();

            });

            $(document).on('click','#back',function () {
                var index = parent.layer.getFrameIndex(window.name);
                parent.layer.close(index);
            });

            //日期时间范围
            laydate.render({
                elem: '#test10'
                ,type: 'datetime'
                ,range: true
            });


            form.on('submit(searBtn)', function(data){
                var index =  parent.layer.getFrameIndex(window.name);
                var info = data.field;
                var img_sup = [] ;
                var sub_name = [];
                var input = $(".img_sup");
                var sub_input = $(".sup_name");
                $.each(input,function(){
                    img_sup.push($(this).val());
                });
                $.each(sub_input,function(){
                    sub_name.push($(this).val());
                });
                var loadingMsg = layer.msg('数据请求中', {icon: 16,shade: 0.01});

                //陈雪凝
                $.ajax({
                    url: "/Goods/onlineRakuten/editSave"
                    , type: "get"
                    , dataType: "json"
                    , data: {
                        'param':info
                        ,'img_import':img_import
                        ,'img_sup':img_sup
                        ,'sub_name':sub_name
                        ,'id': "{{$goodsInfo['id']}}"
                    }

                    , success: function (res) {
                        if(res.code == 1) {
                            layer.msg(res.msg,{icon:1});
                            setTimeout(function(){  //使用  setTimeout（）方法设定定时2000毫秒
                                parent.layer.close(index);
                                parent.layui.table.reload('EDtable');
                                parent.layui.table.reload('EDtable1');
                                parent.layui.table.reload('EDtable2');
                            },1000);
                        }else {
                            layer.msg(res.msg ,{icon:5});
                            // parent.layer.close(index);
                            // parent.layui.table.reload('EDtable');
                            // parent.layui.table.reload('EDtable1');
                            // parent.layui.table.reload('EDtable2');
                        }
                        layer.close(loadingMsg);
                    }

                    ,error: function (e, x, t) {
                        if (e.responseText.length > 0) {
                            try {
                                var msg = JSON.parse(e.responseText);
                                var msgs = '';
                                $.each(msg, function (k, v) {
                                    msgs += v + '<br/>';
                                });
                                layer.msg(msgs);
                            } catch (ex) {
                            }
                        }
                    }
                });
                return false;

            });

            //初始化编辑商品的联动框
            $(document).ready(function () {
                let hasCategory = $("#categoryInArray").val();
                if (!hasCategory) {
                    rakutenCategory();
                } else {
                    //距顶变量
                    let sortedPixel = 0;
                    //所有联动框元素
                    let sortedElement = $(".wareSort").find('ul');
                    //遍历所有联动框
                    $.each(sortedElement, function (k, v) {
                        //找到高亮的选择点
                        sortedPixel = $(this).find('.on').prop('offsetTop');
                        // $(this).scrollTop(sortedPixel - 30);
                        //滑动定位
                        $(this).animate({scrollTop: sortedPixel - 30 +'px'}, 600);
                    });
                    //复原节点
                    restoreNode();
                }
            });

            form.on('radio', function (e) {
                if (e.value == 1) {
                    $("input[name=catalogId]").removeAttr("readOnly");
                    $(".cataselect").attr("disabled", true);
                    form.render('select');
                } else {
                    $(".cataselect").attr("disabled", false);
                    form.render('select');
                    $("input[name=catalogId]").attr("readOnly", true);
                }
            })
        });
    </script>
@endsection
