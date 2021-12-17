@extends('layouts/new_main')
<style type="text/css">
    .shirt input {
        display: inline-block;
    }
    .textr table .spe input:last-child{
        width: 75%!important;
        display: inline-block;
    }
</style>
@section('content')
    <div class="kbmodel_full">
        <div class="content-wrapper" style="margin: 0">
            <form action="" method="" id="goods-form" class="layui-form">
                <input type="hidden" name="firstCategory"  id="firstCategory" value="">
                <input type="hidden" name="secondCategory"  id="secondCategory" value="">
                <input type="hidden" name="thirdCategory"  id="thirdCategory" value="">
                <input type="hidden" name="plat"       value="{{ $goodsInfo->plat??"" }}">
                <input type="hidden" name="from_url"   value="{{ $goodsInfo->url??"" }}">
                <input type="hidden" name="goods_collect_id"   value="{{ $goodsInfo->id??"" }}">
                <div class="productext" id="productext2" style="display: block">
                    <div class="produpage layui-form">
                        <h3>产品编码</h3>
                        <table class="layui-table" lay-skin="nob">
                            <colgroup>
                                <col width="130">
                                <col>
                            </colgroup>
                            <tbody>
                            <tr class="firsttd">
                                <td><b><i style="color: red;">*</i>自定义SKU：</b></td>
                                <td><input type="text" class="layui-input" name="sku" value="" style="width: 25%;" lay-verify="required"/></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="produpage layui-form lastbtn">
                        <h3>产品信息</h3>
                        <table class="layui-table" lay-skin="nob">
                            <colgroup>
                                <col width="120">
                                <col>
                                <col width="120">
                                <col>
                                <col width="120">
                                <col>
                            </colgroup>
                            <tbody>
                            <tr class="firsttd">
                                <td><b><i style="color: red;">*</i>产品名称：</b></td>
                                <td class="shirt"><input type="text" maxlength="100" class="layui-input" name="goods_name" value="" placeholder="" lay-verify="required"/></td>
                                <td><b><i style="color: red;">*</i>产品属性：</b></td>
                                <td>
                                    <select name="goods_attribute_id" lay-verify="required" class="selectdis">
                                        @if(isset($goods_attrs))
                                            @foreach($goods_attrs as $value)
                                                <option value="{{$value['id']}}">{{ $value['attribute_name'] }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </td>
                                <td><b><i style="color: red;">*</i>产品重量：</b></td>
                                <td class="shirt2"><input type="text" class="layui-input" maxlength="10" name="goods_weight" onkeyup='this.value=this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,"$1$2.$3")'  value="" placeholder="" lay-verify="required"/><span>KG</span>
                                </td>
                            </tr>
                            <tr class="firsttd">
                                <td><b><i style="color: red;">*</i>产品尺寸：</b></td>
                                <td class="shirt" colspan="5">
                                    <input type="text" class="layui-input" name="goods_length" onkeyup='this.value=this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,"$1$2.$3")' maxlength="10"  value="" placeholder="长" lay-verify="required"/><span>CM</span><i>×</i>
                                    <input type="text" class="layui-input" name="goods_width"  onkeyup='this.value=this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,"$1$2.$3")' maxlength="10"  value=""  placeholder="宽" lay-verify="required"/><span>CM</span><i>×</i>
                                    <input type="text" class="layui-input" name="goods_height" onkeyup='this.value=this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,"$1$2.$3")' maxlength="10"  value=""  placeholder="高" lay-verify="required"/><span>CM</span>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="6">
                                    <div class="selectedSort firsttd"><b><i style="color: red;">*</i>产品分类：</b><i id="rlselectedSort" class="inputi" disabled></i></div>
                                </td>
                            </tr>

                                <tr>
                                    <td colspan="6">
                                            <div class="wareSort clearfix">
                                                <ul id="rlsort1"></ul>
                                                <ul id="rlsort2" style="display: none;"></ul>
                                                <ul id="rlsort3" style="display: none;"></ul>
                                            </div>
                                    </td>
                                </tr>
                            <tr class="buttonqr firsttd">
                                <td><b><i style="color: red;">*</i>产品标题：</b></td>
                                <td colspan="4" class="protit">
                                    <input type="text" class="layui-input" maxlength="500" name="goods_title" value="{{ $goodsInfo->title??"" }}" lay-verify="required"/>
                                </td>
                                <td>
                                    {{--<button class="layui-btn layui-btn-normal">确定</button>--}}
                                    {{--<button class="layui-btn layui-btn-danger">重置</button>--}}
                                </td>
                            </tr>
                            <tr>
                                <td style="width: 130px;"><b><i style="color: red;">*</i>产品描述：</b></td>
                                <td colspan="5"><textarea name="description" placeholder="请输入文字"
                                    class="layui-textarea">{{$goodsInfo->description ?? ''}}</textarea>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="produpage layui-form textr">
                        <h3>申报信息</h3>
                        <table class="layui-table" lay-skin="nob">
                            <colgroup>
                                <col width="120">
                                <col>
                                <col width="120">
                                <col>
                                <col width="120">
                                <col>
                            </colgroup>
                            <tbody>
                            <tr>
                                <td style="width: 125px"><i style="color: red;">*</i>申报中文名：</td>
                                <td><input name="ch_name" class="layui-input" type="text" value="" lay-verify="required"/></td>
                                <td><i style="color: red;">*</i>申报英文名：</td>
                                <td><input name="eh_name" class="layui-input" type="text" value="" lay-verify="required"/></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td><i style="color: red;">*</i>申报价格：</td>
                                <td class="spe">
                                    <select name="currency_id" lay-verify="required" >
                                        @if(isset($currency))
                                            @foreach($currency as $value)
                                                <option value="{{ $value['id'] }}"
                                                        @if( isset($goods_detail['declares']) && ($goods_detail['declares']['currency_id'] == $value['id']))
                                                        selected
                                                        @elseif(empty($goods_detail['declares']) && $value['code'] == 'USD')
                                                        selected
                                                        @endif >{{$value['name']}}
                                                    -{{ $value['code'] }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <input type="text" class="layui-input" maxlength="20"  onkeyup='this.value=this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,"$1$2.$3")'  name="price" value="" class="shirtw2 layui-input"/>
                                </td>
                                <td><i style="color: red;">*</i>产品品牌：</td>
                                <td><input type="text" class="layui-input" maxlength="100"   name="goods_brand" value="" lay-verify="required"/></td>
                                <td><i style="color: red;">*</i>制造商：</td>
                                <td><input type="text" class="layui-input" maxlength="100"  name="manufacturers" value="" lay-verify="required"/></td>
                            </tr>
                            <tr>
                                <td><i style="color: red;">*</i>海关编码：</td>
                                <td><input type="text" class="layui-input" maxlength="20"  name="custom_code" value="" lay-verify="required"/></td>
                                <td>规格型号：</td>
                                <td><input type="text" class="layui-input" maxlength="100"  name="specifications" value="" /></td>
                                <td>申报单位：</td>
                                <td><input type="text" class="layui-input" maxlength="20"   name="company"  value="" /></td>
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
                                                <img
                                                        onerror="this.src=''"
                                                        @if(!empty($goodsInfo['id']) && !empty($goodsInfo ['goods_pictures']))
                                                        src="{{url('showImage').'?path='.$goodsInfo ['goods_pictures']??''}}"
                                                        @endif
                                                        class="layui-upload-img" id="rldemo1"
                                                >
                                                <input type="hidden" name="goods_pictures" value="{{$goodsInfo ['goods_pictures']??''}}">
                                                @if(!empty($goodsInfo['goods_pictures']))
                                                    @if ($goodsInfo['goods_pictures'])
                                                        <span class="rldelet"><i class="layui-icon">ဆ</i></span>
                                                    @endif
                                                @endif
                                            </div>
                                            <p id="rldemoText"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="layui-tab-item uploadoneimg">
                                    <div class="layui-upload">
                                        <i>本地上传：</i>
                                        <button type="button" class="layui-btn"  id="rltest2">多图片上传</button>
                                        <span>支持 <b>jpg 、jpeg 、gif 、png</b>图片格式</span>
                                        <blockquote class="layui-elem-quote layui-quote-nm" style="margin-top: 10px;">
                                            预览图：
                                            <div class="layui-upload-list" id="rldemo2">
                                            </div>
                                        </blockquote>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="produpage layui-form">
                        <h3>采购信息</h3>
                        <table class="layui-table" lay-skin="nob">
                            <colgroup>
                                <col width="130">
                                <col>
                                <col width="130">
                                <col>
                                <col width="130">
                                <col>
                            </colgroup>
                            <tbody>
                            <tr>
                                <td><b><i style="color: red;">*</i>首选供应商：</b></td>
                                <td>
                                    <select name="preferred_supplier_id"  lay-verify="required">
                                        <option value="">请选择</option>
                                        @if(!empty($suppliers))
                                            @foreach($suppliers as $value)
                                                <option value="{{ $value['id'] }}">{{$value['name']}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </td>
                                <td><b><i style="color: red;">*</i>采购价1：</b></td>
                                <td><input type="text" class="layui-input" maxlength="10" onkeyup='this.value=this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,"$1$2.$3")' name="preferred_price" value="" placeholder="0.00" /></td>
                                <td><b>采购链接1：</b></td>
                                <td><input type="text" class="layui-input" maxlength="500"   name="preferred_url" value=""/></td>
                            </tr>
                            <tr>
                                <td><b>备选供应商：</b></td>
                                <td>
                                    <select name="alternative_supplier_id"  >
                                        <option value="">请选择</option>
                                        @if(!empty($suppliers))
                                            @foreach($suppliers as $value)
                                                <option value="{{ $value['id'] }}" >{{$value['name']}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </td>
                                <td><b>采购价2：</b></td>
                                <td><input type="text" maxlength="10" class="layui-input" onkeyup='this.value=this.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,"$1$2.$3")' name="alternative_price" value="" placeholder="0.00" /></td>
                                <td><b>采购链接2：</b></td>
                                <td><input type="text" maxlength="500" class="layui-input" name="alternative_url" value="" /></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="produpage layui-form" style="text-align: center">
                            <button class="layui-btn" lay-submit="" lay-filter="searBtn">保存</button>
                            <a class="layui-btn layui-btn-primary" id="back" onclick="" lay-filter="">取消</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('javascripts')
    <!-- 商品认领采集三联选择 -->
    <script type="text/javascript" src="{{asset('js/jquery.rlsort.js')}} "></script>
    <script>
        layui.use(['form', 'laydate', 'table', 'element', 'upload'], function () {
            var layer = layui.layer, form = layui.form, laypage = layui.laypage, laydate = layui.laydate;
            var element = layui.element;
            var $ = layui.jquery;
            var upload = layui.upload;

            var img_import = ''; //主图

            //单张图片上传
            var uploadInst = upload.render({
                elem: '#rltest1'
                , url: '/photo/upload'
                , size:10240
                , data: {
                    '_token': "{{ csrf_token() }}"
                }
                , before: function (obj) {
                    $('.layui-upload-list .rlpicarea').find('.rldelet').remove();
                    $('.layui-upload-list .rlpicarea').find('.layui-upload-img').remove();
                    $('.layui-upload-list .rlpicarea').find('input[name="goods_pictures"]').remove();
                    //预读本地文件示例，不支持ie8
                    obj.preview(function (index, file, result) {
                        $('.rlpicarea').append('<img src="'+result+'" class="layui-upload-img" id="rldemo1">' +
                            '<span class="rldelet"><i class="layui-icon">&#x1006;</i></span>' +
                            '<input type="hidden" name="goods_pictures" value="">');
                    });
                }
                , done: function (res) {
                    //如果上传失败
                    if (res.code !== 200) {
                        return layer.msg('上传失败', {"icon": 5});
                    }
                    $('input[name="goods_pictures"]').val(res.data.src);
                    img_import = res.data.src;
                    layer.msg('图片上传成功!', {'icon': 6});
                    //上传成功
                }
                , error: function () {
                    //演示失败状态，并实现重传
                    var demoText = $('#rldemoText');
                    demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-xs demo-reload">重试</a>');
                    demoText.find('.demo-reload').on('click', function () {
                        uploadInst.upload();
                    });
                }
            });

            //多张图片上传
            upload.render({
                elem: '#rltest2'
                , url: '/photo/upload'
                , size:10240
                , data: {
                    '_token': "{{ csrf_token() }}"
                }
                , multiple: true
                , number: 8
                , before: function (obj) {
                    var file = $('.upload_imgs');
                    if(file.length > 8) {
                        layer.msg('最多上传9张图片', {icon: 2 });
                        return false;
                    }
                    //预读本地文件示例，不支持ie8
                    obj.preview(function (index, file, result) {
                        $('#rldemo2').append(
                            '<div class="rlpicarea1">' +
                            '<span class="rldelet1"><i class="layui-icon">&#x1006;</i></span><img src="' + result + '" alt="' + file.name + '" class="layui-upload-img">' +
                            '<input type="hidden" name="uploadimgs" class="upload_imgs '+index+'" value="">' +
                            '</div>');
                    });
                }
                , done: function (res,index, upload) {
                    //如果上传失败
                    if (res.code !== 200) {
                        return layer.msg('上传失败');
                    }
                    var input = $('.upload_imgs');
                    $.each(input,function(i,item){
                        if ($(item).hasClass(index)) {
                            $(this).val(res.data.src);
                        }
                    });
                    if(input.length > 8) {
                        return false;
                    }
                    layer.msg('图片上传成功!', {'icon': 6});
                }
            });

            //返回
            $(document).on('click','#back',function () {
                var index = parent.layer.getFrameIndex(window.name);
                parent.layer.close(index);
            });

            //单图删除
            $(document).on('click', '.rlpicarea .rldelet', function () {
                $(this).parents('.layui-upload-list').find('#rldemoText').html('');
                $(this).parent().find('.layui-upload-img').remove();
                $(this).parent().find('input[name="goods_pictures"]').remove();
                img_import = '';
                $(this).remove();

            });

            //多图片删除
            $(document).on('click', '.rlpicarea1 .rldelet1', function () {
                $(this).parent().find('.upload_imgs').remove();
                $(this).parent().find('.layui-upload-img').remove();
                $(this).remove();

            });



            form.on('submit(searBtn)', function (data) {
                var info = data.field;
                var index1 = parent.layer.getFrameIndex(window.name);
                var imgs = $('.upload_imgs');
                var img_sup = [];
                $.each(imgs,function(){
                    img_sup.push($(this).val());
                });
                //陈雪凝
                $.ajax({
                    url: "/Goods/claimByIdPost"
                    , type: "post"
                    , dataType: "json"
                    ,contentType:"application/x-www-form-urlencoded"
                    , data: {
                        'param': info,
                        'img_sup':img_sup,
                        '_token': "{{ csrf_token() }}",
                    }
                    , success: function (res) {
                        if (res.status) {
                            layer.msg(res.msg, {icon: 6});
                            setTimeout(function () {  //使用  setTimeout（）方法设定定时2000毫秒
                                parent.layer.close(index1); //再执行关闭
                                parent.layui.table.reload('EDtable'); //重载表格
                            }, 2000);
                        } else {
                            layer.msg(res.msg, {icon: 5});
                        }
                    }
                    , error: function (e, x, t) {
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

            $('#check_status').click(function(){
                var id = $('input[name="id"]').val();
                if(!id) {
                    layer.msg('审核异常');
                    return false;
                }
                layer.confirm('审核产品信息后将不能修改,请确认是否审核', {title: '提示'}, function () {
                    var index = parent.layer.getFrameIndex(window.name);
                    $.ajax({
                        url: "/Goods/local/localGoodsCheck"
                        , type: "get"
                        , dataType: "json"
                        , data: {
                            'id':id
                        }

                        , success: function (res) {
                            if (res) {
                                layer.msg('审核成功!', {icon: 6});
                                setTimeout(function () {
                                    parent.layer.close(index);
                                    window.parent.location.reload();
                                }, 1000);
                            } else {
                                layer.msg('审核失败!', {icon: 6});
                            }
                        }
                    });
                });
            });


            $(document).ready(function () {
                let hasCategory = $("#firstCategory").val();
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
        });
    </script>
@endsection

