@extends('layouts/new_main')
@section('head')
    <style type="text/css">
        .spe .layui-form-select {
            width: 155px !important;
        }
        .catachoose p{ display: inline-block; color: #999; margin-left: 10px; }
        .catachoose .layui-form-select{  width: 400px;    display: inline-block; }
        .catachoose{ margin: 10px 0; }
        .catachoose span .layui-form-radioed,.catachoose span .layui-form-radio{ width: 155px; }
        /*.catainput{ width: 400px; }*/
       
    </style>
@endsection
@section('content')
    <div class="kbmodel_full">
        <div class="content-wrapper" style="margin: 0;">
            <form action="" method="" class="layui-form">
                <input type="hidden" name="firstCategory"  id="firstCategory" value="{{$goodsInfo['selectedCategory']['category_id_1']??''}}">
                <input type="hidden" name="secondCategory"  id="secondCategory" value="{{$goodsInfo['selectedCategory']['category_id_2']??''}}">
                <input type="hidden" name="thirdCategory"  id="thirdCategory" value="{{$goodsInfo['selectedCategory']['category_id_3']??''}}">
                <input type="hidden" name="fourCategory"  id="fourCategory" value="{{$goodsInfo['selectedCategory']['category_id_4']??''}}">
                <input type="hidden" name="plat"       value="{{ $goodsInfo['plat']??"" }}">
                <input type="hidden" name="from_url"   value="{{ $goodsInfo['url']??"" }}">
                <input type="hidden" name="lotte_id"   value="{{ $goodsInfo['id']??"" }}">
                <input type="hidden" name="goods_id"   value="{{ $goodsInfo['goods_id']??"" }}">

                <input type="hidden" name="goods_length"   value="{{ $goodsInfo['goods_length']??"" }}">
                <input type="hidden" name="goods_width"   value="{{ $goodsInfo['goods_width']??"" }}">
                <input type="hidden" name="goods_height"   value="{{ $goodsInfo['goods_height']??"" }}">
                <input type="hidden" name="goods_weight"   value="{{ $goodsInfo['goods_weight']??"" }}">

                <input type="hidden" name="xxType"   value="2" id="xxType">
                <input type="hidden" name="sku"   value="{{$sku}}" id="">
                <input type="hidden" name="categoryInArray" id="categoryInArray" value="{{$goodsInfo['rakuten_category_id']??''}}">

                <div class="productext" id="productext" style="display: block;">
                    <div class="produpage layui-form lastbtn">
                        <h3>????????????</h3>
                        <table class="layui-table" lay-skin="row">
                            <tbody>
                            <tr>
                                <td>
                                    <b><i style="color: red;">*</i>?????????</b>
                                    <div class="layui-input-block dropd">
                                        <select name="store_id" lay-verify="required">
                                            <option value="">?????????</option>
                                            @foreach($shopsArr as $shop)
                                                <option value="{{$shop['id']}}"
                                                        @if(isset($goodsInfo['belongs_shop']) && ($goodsInfo['belongs_shop'] === $shop['id']))
                                                        selected
                                                        @endif
                                                >{{ $shop['shop_name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6"><div class="selectedSort firsttd"><b><i style="color: red;">*</i>???????????????</b>
                                        <i id="rlselectedSort" class="inputi" style="min-width:500px; width:auto!important;" data-id="{{$goodsInfo['rakuten_category_id']??''}}">
                                            {{$goodsInfo['category']??''}}
                                        </i>
                                    </div></td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    @if(!empty($goodsInfo['rakuten_category_id']))
                                        <div class="wareSort clearfix">
                                            <ul id="rlsort1" @if(empty($category['first']))style="display: none"@endif>
                                                @if(!empty($category['first']))
                                                @foreach($category['first'] as $key => $val)
                                                    <li
                                                            @if(in_array($val['genreId'], $goodsInfo['categoryInArray']))class="on"@endif
                                                    onclick="rakutenCategory(false, {{$val['genreId']}}, {{$val['categories_lv']}}, {{$key}});"
                                                            data-id="{{$val['genreId']}}"><a href="javascript:void(0)">{{$val['genreName']}}</a></li>
                                                @endforeach
                                                @endif
                                            </ul>
                                            <ul id="rlsort2" @if(empty($category['second']))style="display: none"@endif>
                                                @if(!empty($category['second']))
                                                @foreach($category['second'] as $key => $val)
                                                    <li
                                                            @if(in_array($val['genreId'], $goodsInfo['categoryInArray']))class="on"@endif
                                                    onclick="rakutenCategory(false, {{$val['genreId']}}, {{$val['categories_lv']}}, {{$key}});"
                                                            data-id="{{$val['genreId']}}"><a href="javascript:void(0)">{{$val['genreName']}}</a></li>
                                                @endforeach
                                                @endif
                                            </ul>
                                            <ul id="rlsort3" @if(empty($category['third'])) style="display: none" @endif>
                                                @if(!empty($category['third']))
                                                @foreach($category['third'] as $key => $val)
                                                    <li
                                                            @if(in_array($val['genreId'], $goodsInfo['categoryInArray']))class="on"@endif
                                                    onclick="rakutenCategory(false, {{$val['genreId']}}, {{$val['categories_lv']}}, {{$key}});"
                                                            data-id="{{$val['genreId']}}"><a href="javascript:void(0)">{{$val['genreName']}}</a></li>
                                                @endforeach
                                                @endif
                                            </ul>

                                            <ul id="rlsort4" @if(empty($category['four']))style="display: none"@endif>
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
                                <td><i style="color: red;">*</i>??????ID???</td>
                                <td>
                                    <div class="catachoose">
                                        <span><input type="radio" @if(isset($goodsInfo['catalogId']) && $goodsInfo['catalogId']) checked @endif name="catalog" value="JAN??????" title="JAN??????"></span>
                                        <input type="text" name="catalogId" value="{{$goodsInfo['catalogId']??''}}" class="catainput layui-input" style="margin-right: 1rem;width: 15rem;display: inline-block;" readonly="readonly" />
                                        <p style="margin-left: 0px;">(????????????)</p>
                                    </div>
                                    <div class="catachoose">
                                        <span><input type="radio" name="catalog" value="????????????ID?????????" title="????????????ID?????????"></span>
                                        <div style="width: 200px; display: inline-block">
                                        <select class="cataselect" name="reason" disabled >
                                            <option value=""></option>
                                            <option value="1" @if(isset($goodsInfo['catalogIdExemptionReason']) && ($goodsInfo['catalogIdExemptionReason'] == 1)) selected @endif>???????????????</option>
                                            <option value="2" @if(isset($goodsInfo['catalogIdExemptionReason']) && $goodsInfo['catalogIdExemptionReason'] == 2) selected @endif>??????????????????</option>
                                            <option value="3" @if(isset($goodsInfo['catalogIdExemptionReason']) && $goodsInfo['catalogIdExemptionReason'] == 3) selected @endif>???????????????????????????</option>
                                            <option value="4" @if(isset($goodsInfo['catalogIdExemptionReason']) && $goodsInfo['catalogIdExemptionReason'] == 4) selected @endif>???????????????????????????</option>
                                            <option value="5" @if(isset($goodsInfo['catalogIdExemptionReason']) && $goodsInfo['catalogIdExemptionReason'] == 5) selected @endif>???????????????????????????</option>
                                        </select>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="produpage layui-form textr">
                        <h3>????????????</h3>
                        <table class="layui-table shangjiainfor" lay-skin="nob">
                            <tbody>
                            <tr>
                                <td><i style="color: red;">*</i>?????????????????????</td>
                                <td><input name="itemUrl" class="layui-input" lay-verify="required" value="{{ $goodsInfo['cmn']??"" }}" type="text" /></td>
                                <td>???????????????</td>
                                <td><input name="itemNumber" class="layui-input" value="{{ $goodsInfo['sku']??"" }}" type="text" /></td>
                            </tr>
                            <tr>
                                <td><b><i style="color: red;">*</i>???????????????</b></td>
                                <td>
                                    <select name="currency_code" lay-verify="required" >
                                        <option value="">?????????</option>
                                        @foreach($currency as $key => $val)
                                            <option value="{{$val['code']}}" @if (!empty($goodsInfo['currency_code']) && $goodsInfo['currency_code'] == $val['code']) selected @endif @if (empty($goodsInfo['currency_code']) && $val['code'] == 'JPY') selected @endif>{{$val['name']}}</option>
                                        @endforeach
                                    </select>
                                    <input name="sale_price" value="{{ $goodsInfo['sale_price']??"" }}" onkeyup='this.value=this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,"$1$2.$3")' id="sale_price" type="text" class="shirtw" />
                                </td>
                                <td><i style="color: red;">*</i>???????????????</td>
                                <td>
                                    @if (isset($goodsInfo['platform_in_stock']) && isset($goods_quantity))
                                        <input  name="inventory" class="layui-input"  value="{{empty($goodsInfo['platform_in_stock'])?$goods_quantity:$goodsInfo['platform_in_stock']}}"  type="number" min="1" max="99999999" autocomplete="off" lay-verify="required" />
                                    @elseif (!isset($goodsInfo['platform_in_stock']) && isset($goods_quantity))
                                        <input  name="inventory" class="layui-input"  value="{{$goods_quantity}}"  type="number" min="1" max="99999999" autocomplete="off" lay-verify="required" />
                                    @else
                                        <input  name="inventory" class="layui-input"  value=""  type="number" min="1" max="99999999" autocomplete="off" lay-verify="required" />
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><i style="color: red;">*</i>???????????????</td>
                                <td><input name="goods_name" class="layui-input" lay-verify="required" type="text" value="{{$goodsInfo['title'] ?? ''}}" /></td>
                                <td><i style="color: red;">*</i>???????????????</td>
                                <td><input type="text" class="layui-input"  name="goods_title" lay-verify="required" value="{{ $goodsInfo['goods_name']??"" }}" /></td>
                            </tr>
                            <tr>
                                <td><i style="color: red;">*</i>???????????????</td>
                                <td colspan="1"><input type="text" class="layui-input" name="rakuten_category_id" id="rakuten_category_id" disabled style="width:385px;" value="{{ $goodsInfo['rakuten_category_id']??"" }}" /></td>
                            </tr>
                            {{--<tr>--}}
                            {{--<td>*????????????ID???</td>--}}
                            {{--<td colspan="3"><input type="text"  name="product_cat_id" value="{{ $goodsInfo['product_cat_id']??"" }}" /></td>--}}
                            {{--</tr>--}}
                            <tr>
                                <td><i style="color: red;">*</i>???????????????</td>
                                <td colspan="3"><textarea name="goods_desc" lay-verify="required" placeholder="???????????????" class="layui-textarea">@if(!$id) {{ $goodsInfo['description']??"" }} @else {{ $goodsInfo['goods_description']??"" }}@endif</textarea></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="produpage">
                        <h3>????????????</h3>
                        <div class="layui-tab martop">
                            <ul class="layui-tab-title">
                                <li class="layui-this">??????</li>

                                <li>??????</li>
                            </ul>
                            <div class="layui-tab-content">
                                <div class="layui-tab-item layui-show">
                                    <div class="layui-upload uploadoneimg">
                                        <i>???????????????</i>
                                        <button type="button" class="layui-btn" id="rltest1">????????????</button>
                                        <span>?????? <b>jpg ???jpeg ???gif ???png</b>????????????</span>
                                        <div class="layui-upload-list">
                                            <div class="rlpicarea">
                                                @if(!$id)
                                                    <img onerror="this.src='/img/imgNotFound.jpg'" class="layui-upload-img" id="rldemo1" @if(!empty($goodsInfo['goods_pictures'])) src="{{ url('showImage').'?path='.($goodsInfo['goods_pictures']??'') }}" @else src="" @endif>
                                                    <input type="hidden" name="img_import" class="img_import" value="{{$goodsInfo['goods_pictures']??''}}">
                                                    @if ($goodsInfo['goods_pictures'])
                                                        <span class="rldelet"><i class="layui-icon">???</i></span>
                                                    @endif
                                                @else
                                                    <img onerror="this.src='/img/imgNotFound.jpg'" class="layui-upload-img" id="rldemo1" @if(!empty($goodsInfo['img_url'])) src="{{ url('showImage').'?path='.($goodsInfo['img_url']??'') }}" @else src="" @endif>
                                                    <input type="hidden" name="img_import" class="img_import" value="{{$goodsInfo['img_url']??''}}">
                                                    @if ($goodsInfo['img_url'])
                                                        <span class="rldelet"><i class="layui-icon">???</i></span>
                                                    @endif
                                                @endif
                                            </div>
                                            <p id="rldemoText"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="layui-tab-item uploadoneimg">
                                    <div class="layui-upload">
                                        <i>???????????????</i>
                                        <button type="button" class="layui-btn" id="rltest2">???????????????</button>
                                        <span>?????? <b>jpg ???jpeg ???gif ???png</b>????????????</span>
                                        <blockquote class="layui-elem-quote layui-quote-nm" style="margin-top: 10px;">
                                            ????????????
                                            <div class="layui-upload-list" id="rldemo2">
                                                @if(isset($goodsInfo['pictures']) && count($goodsInfo['pictures'])>0)
                                                    @foreach($goodsInfo['pictures'] as $key => $val)
                                                        <div class="rlpicarea1">
                                                    {{--<span class="rldelet12" data-id="{{ $val['id']??'' }}"  style="right: 10px;position: absolute;cursor: pointer;top: 0px;right: 0px;display: block;width: 30px;height: 30px;line-height: 30px;color: #fff;background: rgba(0,0,0,.6);text-align: center;"--}}
                                                    {{-->--}}
                                                            <span class="rldelet1">
                                                        <i class="layui-icon">???</i></span>
                                                            <img src="{{ url('showImage').'?path='.$val['link']??'' }}" onerror="this.src='/img/imgNotFound.jpg'" alt="" class="layui-upload-img">
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
                    <button class="layui-btn sub" lay-submit="" lay-filter="searBtn">??????</button>
                    &nbsp;
                    <button class="layui-btn" lay-submit="" lay-filter="searBtn" data-type="putOnBatch">??????</button>
                    &nbsp;
                    <a class="layui-btn layui-btn-primary" lay-submit="" lay-filter="searBtn" id="back">??????</a>
                </div>

            </form>
        </div>
    </div>
@endsection
@section('javascripts')
    <script type="text/javascript" src="{{asset('js/jquery.rlsort.draft.js')}}?time={{time()}} "></script>
    <script>
        layui.use(['form', 'laydate','table','element','upload'], function(){
            var layer = layui.layer, form = layui.form, laypage = layui.laypage, laydate = layui.laydate;
            var element = layui.element;
            var $ = layui.jquery;
            upload = layui.upload;

            var img_import = '' ; //??????
            var import_name = '';

            //????????????????????????????????????
            var uploadInst = upload.render({
                elem: '#rltest1'
                ,url: '/photo/rakutenUpload'
                ,data:{
                    '_token':"{{ csrf_token() }}"
                }
                ,before: function(obj){
                    $('.sub').attr('disabled',true);
                    $('.layui-upload-list .rlpicarea').find('.rldelet').remove();
                    $('.layui-upload-list .rlpicarea').find('input[name="img_import"]').remove();
                    $('.layui-upload-list .rlpicarea').find('#rldemo1').removeAttr('src');
                    $('.layui-upload-list .rlpicarea').find('.layui-upload-img').remove();
                    //????????????????????????????????????ie8
                    obj.preview(function(index, file, result){
                        $('.rlpicarea').append('<img src="'+result+'" class="layui-upload-img" id="rldemo1">' +
                            '<span class="rldelet"><i class="layui-icon">&#x1006;</i></span>' +
                            '<input type="hidden" name="img_import" class="img_import" value="">');
                    });
                }
                ,done: function(res){
                    //??????????????????
                    if(res.code !== 200){
                        return layer.msg('????????????',{"icon":5});
                    }

                    img_import = res.data.src ;
                    $('.img_import').val(img_import);
                    layer.msg('??????????????????!' ,{'icon':6});
                    $('.sub').attr('disabled',false);
                }
                ,error: function(){
                    //????????????????????????????????????
                    var demoText = $('#rldemoText');
                    demoText.html('<span style="color: #FF5722;">????????????</span> <a class="layui-btn layui-btn-xs demo-reload">??????</a>');
                    demoText.find('.demo-reload').on('click', function(){
                        uploadInst.upload();
                    });
                }
            });

            //????????????????????????????????????
            upload.render({
                elem: '#rltest2'
                ,url: '/photo/rakutenUpload'
                ,data: {
                    '_token':"{{ csrf_token() }}"
                }
                ,multiple: true
                ,before: function(obj){
                    $('.sub').attr('disabled',true);
                    //????????????????????????????????????ie8
                    obj.preview(function(index, file, result){
                        $('#rldemo2').append('' +
                            '<div class="rlpicarea1"><span class="rldelet1"><i class="layui-icon">&#x1006;</i></span><img src="'+ result +'" alt="'+ file.name +'" class="layui-upload-img">' +
                            '<input type="hidden" class="img_sup '+index+'" name="imgsup[]" value=""><input type="hidden" class="sup_name '+index+'" name="sup_name[]" value=""></div>'
                        );
                    });
                }
                ,done: function(res,index, upload){
                    if(res.code !== 200){
                        return layer.msg('????????????');
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

            // ????????????????????????
            $(document).on('click','.rlpicarea .rldelet',function(){
                $(this).parents('.layui-upload-list').find('#rldemoText').html('');
                $(this).parent().find('#rldemo1').removeAttr('src');
                $(this).parent().find('input[name="img_import"]').remove();
                $(this).parent().find('.layui-upload-img').remove();
                img_import = '' ;
                $(this).remove();
            })

            // ???????????????????????????
            $(document).on('click','.rlpicarea1 .rldelet1',function(){
                $(this).parent().find('.layui-upload-img').remove();
                $(this).parent().find('.img_sup').remove();
                $(this).remove();

            });

            $(document).on('click','#back',function () {
                var index = parent.layer.getFrameIndex(window.name);
                parent.layer.close(index);
            });

            $(".catachoose span").click(function(){
                var index = $(this).parent().index();
                if(index==1){
                    $(".catainput").attr('readonly',true);
                    $(this).parent().find("select").removeAttr("disabled");
                    form.render();
                }else if (index==0) {
                    $(".cataselect").attr("disabled",true);
                    $(this).parent().find("input").removeAttr("readonly");
                    form.render();
                }
            });

            //??????????????????
            laydate.render({
                elem: '#test10'
                ,type: 'datetime'
                ,range: true
            });

            form.on('submit(searBtn)', function(data){
                var index =  parent.layer.getFrameIndex(window.name);;
                var info = data.field;
                var img_sup = [] ;
                var sub_name = [];
                var input = $(".img_sup");
                var sub_input = $(".sup_name");
                var rakuten_id = $('#rlselectedSort').attr('data-id');
                var count = 0;
                var type = data.elem.dataset.type || '';
                $.each(input,function(){
                    img_sup.push($(this).val());
                });
                $.each(sub_input,function(){
                    sub_name.push($(this).val());
                });
                if(type !== ''){
                    layer.msg('?????????...', {
                        icon: 16
                        ,shade: 0.01
                    });
                }
                //?????????
                $.ajax({
                    url: "/Goods/lotte/editSave"
                    , type: "post"
                    , dataType: "json"
                    , data: {
                        'param':info
                        ,'img_import':img_import
                        ,'img_sup':img_sup
                        ,'sub_name':sub_name
                        ,'rakuten_id':rakuten_id
                        ,'type':type
                        , '_token' : '{{ csrf_token() }}'

                    }

                    , success: function (res) {
                        layer.close(layer.index)
                        if(res.msg == 'success') {
                            layer.msg('????????????',{icon:6});
                            setTimeout(function(){  //??????  setTimeout????????????????????????2000??????
                                parent.layer.close(index);
                                parent.layui.table.reload('EDtable');
                            },1000);
                        }else {
                            layer.msg(res.msg ,{icon:5});
                        }
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
                                console.log(ex) ;
                            }
                        }
                    }
                });
                return false;

            });

            //?????????????????????????????????
            $(document).ready(function () {
                let hasCategory = $("#categoryInArray").val();
                if (!hasCategory) {
                    rakutenCategory();
                } else {
                    //????????????
                    let sortedPixel = 0;
                    //?????????????????????
                    let sortedElement = $(".wareSort").find('ul');
                    //??????????????????
                    let rekuten_str = $('#rakuten_category_id').val();
                    let rekuten_arr = rekuten_str.split(',');
                    if(rekuten_arr[3]) {
                        $('#fourCategory').val(rekuten_arr[3]);
                    }
                    //?????????????????????
                    $.each(sortedElement, function (k, v) {
                        //????????????????????????
                        sortedPixel = $(this).find('.on').prop('offsetTop');
                        // $(this).scrollTop(sortedPixel - 30);
                        //????????????
                        $(this).animate({scrollTop: sortedPixel - 30 +'px'}, 600);
                    });
                    //????????????
                    restoreNode();
                }
            });
        });
    </script>
@endsection
