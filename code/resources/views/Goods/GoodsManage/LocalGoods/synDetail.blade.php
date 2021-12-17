@extends('layouts/new_main')
@section('head')
    <style>
        body{
            height:92%;
        }
        .shirt *{display: inline-block;}
        #productext2 td:first-child{width: 10% !important;text-align: right !important;}

        /*.textr table .spe input{*/
        /*    width: 50%!important;*/
        /*    display: inline-block;*/
        /*}*/
    </style>
@endsection
@section('content')
    <div class="kbmodel_full">
        <div class="content-wrapper" style="width:100%;margin-left:0px;">
            <form action="" method="" id="goods-form" class="layui-form">
                <input type="hidden" name="firstCategory"  id="firstCategory" value="">
                <input type="hidden" name="secondCategory"  id="secondCategory" value="">
                <input type="hidden" name="thirdCategory"  id="thirdCategory" value="">
                <input type="hidden" name="id" value="{{$goods_detail['id'] ?? ''}}">
                <div class="productext" id="productext2" style="display: block">
                    <div class="layui-form">
                        <table lay-skin="nob" class="nob" style="border-collapse: separate; border-spacing:80px 15px;">
                            <tbody>
                            <tr class="firsttd">
                                <td><b><i style="color: red;">*</i>自定义SKU：</b></td>
                                <td>{{$goods_detail['sku'] ?? ''}}</td>
                            </tr>
                            <tr>
                                <td><b><i style="color: red;">*</i>产品名称：</b></td>
                                <td>{{$goods_detail['warehouse_goods']['goods_name'] ?? ''}}</td>
                            </tr>
                            <tr>
                                <td><b><i style="color: red;">*</i>产品尺寸：</b></td>
                                <td class="shirt" colspan="5">
                                    {{$goods_detail['goods_length']  ?? ''}}<span>CM</span><i>×</i>
                                    {{$goods_detail['goods_width'] ?? ''}}<span>CM</span><i>×</i>
                                    {{$goods_detail['goods_height']  ?? ''}}<span>CM</span>
                                </td>
                            </tr>

                            <tr>
                                <td><b><i style="color: red;">*</i>产品重量：</b></td>
                                <td class="shirt2">{{$goods_detail['goods_weight'] ?? ''}}<span>KG</span>
                                </td>
                            </tr>
                            <tr>
                                <td><b><i style="color: red;">*</i>是否含电池：</b></td>
                                <td style="width:176.5px;">
                                    @if($goods_detail['warehouse_goods']['isset_battery'] == 1) 是 @endif
                                    @if(!$goods_detail['warehouse_goods']['isset_battery']) 否 @endif
                                </td>
                            </tr>
                            <tr>
                                <td>主要成分：</td>
                                <td>{{$goods_detail['warehouse_goods']['bases'] ?? ''}}</td>
                            </tr>
                            <tr>
                                <td><i style="color: red;">*</i>申报中文名：</td>
                                <td>{{$goods_detail['warehouse_goods']['ch_name'] ?? ''}}</td>
                            </tr>
                            <tr>
                                <td><i style="color: red;">*</i>申报英文名：</td>
                                <td>{{$goods_detail['warehouse_goods']['eh_name'] ?? ''}}</td>
                            </tr>

                            <tr>
                                <td><i style="color: red;">*</i>申报价格：</td>
                                <td class="spe">
                                    {{$goods_detail['declares']['price'] ?? ''}}
                                </td>
                            </tr>

                            <tr class="lasttd">
                                <td><i style="color: red;">*</i>仓库分类：</td>
                                <td>
                                    <div class="layui-input-inline ware_cate" style="margin-left:5px;">
                                        {{$goods_detail['warehouse_goods']['category1']['name'] ?? ''}}
                                    </div>
                                    <div class="layui-input-inline ware_cate1" style="margin-left:5px;">
                                        @if($goods_detail['warehouse_goods']['category2']) > @endif {{$goods_detail['warehouse_goods']['category2']['name'] ?? ''}}
                                    </div>
                                    <div class="layui-input-inline ware_cate2" style="margin-left:5px;">
                                        @if($goods_detail['warehouse_goods']['category3']) > @endif {{$goods_detail['warehouse_goods']['category3']['name'] ?? ''}}
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="produpage layui-form">
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
            var img_sup = []; //附图

            //单张图片上传
            var uploadInst = upload.render({
                elem: '#rltest1'
                , url: '/photo/upload'
                , data: {
                    '_token': "{{ csrf_token() }}"
                }
                , before: function (obj) {
                    //预读本地文件示例，不支持ie8
                    obj.preview(function (index, file, result) {
                        $('#rldemo1').attr('src', result); //图片链接（base64）
                        $('.rlpicarea').append('<span class="rldelet"><i class="layui-icon">&#x1006;</i></span><div class="rlpicname"></div>');
                        $('.rlpicname').text(file.name);
                    });
                }
                , done: function (res) {
                    //如果上传失败
                    if (res.code !== 200) {
                        return layer.msg('上传失败', {"icon": 5});
                    }

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
                , data: {
                    '_token': "{{ csrf_token() }}"
                }
                , multiple: true
                , before: function (obj) {
                    //预读本地文件示例，不支持ie8
                    obj.preview(function (index, file, result) {
                        $('#rldemo2').append(
                            '<div class="rlpicarea1">' +
                            '<span class="rldelet1"><i class="layui-icon">&#x1006;</i></span><img src="' + result + '" alt="' + file.name + '" class="layui-upload-img">' +
                            '<input type="hidden" name="uploadimgs[]" class="upload_imgs" value="">' +
//                            '<div class="rlpicname1"></div>' +
                            '</div>');
//                        $('.rlpicname1').text(file.name);
                    });
                }
                , done: function (res) {
                    //如果上传失败
                    if (res.code !== 200) {
                        return layer.msg('上传失败');
                    }
                    var imgs_up = $('.upload_imgs');
                    $.each(imgs_up,function(index,item){
                        if($(item).val() == ''){
                            $(this).val(res.data.src);
                        }
                    });
                    layer.msg('图片上传成功!', {'icon': 6});

                    //上传成功

                }
            });

            //单图删除
            $(document).on('click', '.rlpicarea .rldelet', function () {
                $(this).parents('.layui-upload-list').find('#rldemoText').html('');
                $(this).parent().find('#rldemo1').removeAttr('src');
                img_import = '';
                $(this).remove();

            });

            //多图片删除
            $(document).on('click', '.rlpicarea1 .rldelet1', function () {
//                $(this).parent().find('.rlpicname1').remove();
                $(this).parent().find('.upload_imgs').remove();
                $(this).parent().find('.layui-upload-img').remove();
                $(this).remove();

            });

            form.on('submit(searBtn)', function (data) {
                var info = data.field;
                var index1 = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                //陈雪凝
                $.ajax({
                    url: "/Goods/local/updatetbGoods"
                    , type: "get"
                    , dataType: "json"
                    , data: {
                        'param': info
                    }
                    , success: function (res) {
                        if (res.msg = 'success') {
                            layer.msg(res.msg, {icon: 6});
                            setTimeout(function () {  //使用  setTimeout（）方法设定定时2000毫秒
                                parent.layer.close(index1); //再执行关闭
                                parent.layui.table.reload('EDtable1'); //重载表格
                            }, 2000);
                        } else {
                            layer.msg('保存失败!', {icon: 5});
                        }
                    }
                    , error: function (e) {
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

            //同步
            form.on('submit(synchBtn)', function (data) {
                var info = data.field;
                var index1 = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                //陈雪凝
                $.ajax({
                    url: "/Goods/local/synchroGoods"
                    , type: "get"
                    , dataType: "json"
                    , data: {
                        'param': info
                    }
                    , success: function (res) {
                        console.log(res);
                        if (res.msg = 'success') {
                            layer.msg('产品信息已同步,请等待仓库审核信息!', {icon: 6});
                            setTimeout(function () {
                                parent.layer.close(index1);
                                parent.layui.table.reload('EDtable1');
                            }, 2000);
                        } else {
                            layer.msg('操作失败!', {icon: 5});
                        }
                    }
                    , error: function (e) {
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

            form.on('select(cat1)', function (data) {
                var cat_id = data.value;
                $.ajax({
                    url:"/Goods/local/selectlev"
                    , type: "get"
                    , dataType: "json"
                    ,data: {
                        'id': cat_id
                    }
                    , success: function (res) {

                        var html = '<select name="category2"  lay-filter="cat2" class="cat1">';
                        $.each(res,function(index,item){
                                html += '<option value="'+item.id+'">'+item.name+'</option>';
                        });
                        html += '</select>';
                        $('tbody tr:last td:nth-child(2) .ware_cate1').empty()
                        $('tbody tr:last td:nth-child(2) .ware_cate1').append(html);
                        form.render();
                    }
                });
                return false;
            });

            form.on('select(cat2)', function (data) {
                var cat_id = data.value;
                $.ajax({
                    url:"/Goods/local/selectlev"
                    , type: "get"
                    , dataType: "json"
                    ,data: {
                        'id': cat_id
                    }
                    , success: function (res) {

                        var html = '<select name="category3"  lay-filter="" class="">';
                        $.each(res,function(index,item){
                                html += '<option value="'+item.id+'">'+item.name+'</option>';
                        });
                        html += '</select>';
                        $('tbody tr:last td:nth-child(2) .ware_cate2').empty()
                        $('tbody tr:last td:nth-child(2) .ware_cate2').append(html);
                        form.render();
                    }
                });
                return false;
            });
        });
    </script>
@endsection

