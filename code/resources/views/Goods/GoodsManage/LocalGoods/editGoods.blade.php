@extends('layouts/new_main')
@section('content')
    <style>
        .shirt *{display: inline-block;}
        #productext2 td:first-child{width: 10% !important;text-align: right !important;}
        /*.textr table .spe input{*/
        /*    width: 50%!important;*/
        /*    display: inline-block;*/
        /*}*/
    </style>
    <div class="kbmodel_full">
        <div class="content-wrapper" style="width:100%;margin-left:0px;">
            <form action="" method="" id="goods-form" class="layui-form">
                <input type="hidden" name="firstCategory"  id="firstCategory" value="">
                <input type="hidden" name="secondCategory"  id="secondCategory" value="">
                <input type="hidden" name="thirdCategory"  id="thirdCategory" value="">
                <input type="hidden" name="id" value="{{$goods_detail['id'] ?? ''}}">
                <div class="productext" id="productext2" style="display: block">
                    <div class="layui-form">
                        <table lay-skin="nob" class="nob" style="border-collapse: separate; border-spacing:10px 12px;">
                            <tbody>
                            <tr class="firsttd">
                                <td><b><i style="color: red;">*</i>自定义SKU：</b></td>
                                <td>
                                    <div class="layui-input-inline">
                                     <input type="text" class="layui-input layui-disabled layui-unselect" name="sku" disabled value="{{$goods_detail['sku'] ?? ''}}"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><b><i style="color: red;">*</i>产品名称：</b></td>
                                <td>
                                    <div class="layui-input-inline">
                                     <input type="text" class="layui-input" name="goods_name" value="{{$goods_detail['warehouse_goods']['goods_name'] ?? ''}}"/>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><b><i style="color: red;">*</i>产品尺寸：</b></td>
                                <td class="shirt" colspan="5">
                                    <input type="text" class="layui-input" name="goods_length"  value="{{$goods_detail['warehouse_goods']['goods_length']  ?? ''}}" placeholder="长"/><span>CM</span><i>×</i>
                                    <input type="text" class="layui-input" name="goods_width"   value="{{$goods_detail['warehouse_goods']['goods_width'] ?? ''}}" placeholder="宽"/><span>CM</span><i>×</i>
                                    <input type="text" class="layui-input" name="goods_height"  value="{{$goods_detail['warehouse_goods']['goods_height']  ?? ''}}" placeholder="高"/><span>CM</span>
                                </td>
                            </tr>
                            <tr>
                                <td><b><i style="color: red;">*</i>产品重量：</b></td>
                                <td class="shirt2"><input type="text" class="layui-input" name="goods_weight" value="{{$goods_detail['warehouse_goods']['goods_weight'] ?? ''}}" placeholder=""/><span>KG</span>
                                </td>
                            </tr>
                            <tr>
                                <td><b><i style="color: red;">*</i>是否含电池：</b></td>
                                <td>
                                    <div class="layui-input-inline">
                                    <select name="isset_battery" lay-verify="required">
                                        <option @if(!empty($goods_detail['warehouse_goods']) && ($goods_detail['warehouse_goods']['isset_battery'] == 1)) selected @endif value="1">是</option>
                                        <option @if(!empty($goods_detail['warehouse_goods']) && !$goods_detail['warehouse_goods']['isset_battery']) selected @endif value="0">否</option>
                                    </select>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td>主要成分：</td>
                                <td>
                                    <div class="layui-input-inline">
                                        <input name="bases" type="text" maxlength="500" class="layui-input" value="{{$goods_detail['warehouse_goods']['bases'] ?? ''}}" />
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><i style="color: red;">*</i>申报中文名：</td>
                                <td>
                                    <div class="layui-input-inline">
                                      <input name="ch_name" type="text" class="layui-input" value="{{$goods_detail['warehouse_goods']['ch_name'] ?? ''}}" />
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><i style="color: red;">*</i>申报英文名：</td>
                                <td>
                                    <div class="layui-input-inline">
                                        <input name="eh_name" type="text" class="layui-input" value="{{$goods_detail['warehouse_goods']['eh_name'] ?? ''}}" />
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><i style="color: red;">*</i>申报价格：</td>
                                <td class="spe">
                                    <div class="layui-input-inline">
                                        <select name="currency_id" lay-verify="required" >
                                        @foreach($currency as $value)
                                            <option value="{{ $value['id'] }}"
                                                    @if((isset($goods_detail['warehouse_goods']) && !empty($goods_detail['warehouse_goods']) && ($goods_detail['warehouse_goods']['currency_id'] == $value['id'])))
                                                    selected
                                                    @endif
                                                    @if(empty($goods_detail['warehouse_goods']) || (empty($goods_detail['warehouse_goods']['currency_id']) && ($value['code'] == 'USD')))
                                                    selected
                                                    @endif
                                            >{{$value['name']}}
                                                -{{ $value['code'] }}</option>
                                        @endforeach
                                        </select>
                                    </div>
                                    <div class="layui-input-inline">
                                        <input type="text" name="price" class="layui-input" value="{{$goods_detail['warehouse_goods']['price'] ?? ''}}" class="shirtw2"/>
                                    </div>
                                </td>
                            </tr>

                            <tr class="lasttd">
                                <td><i style="color: red;">*</i>仓库商品分类：</td>
                                <td>
                                    <div class="layui-input-inline ware_cate">
                                        {!! $select['cat1']??'' !!}
                                    </div>

                                    <div class="layui-input-inline ware_cate1">
                                        {!! $select['cat2']??'' !!}
                                    </div>

                                    <div class="layui-input-inline ware_cate2">
                                        {!! $select['cat3']??'' !!}

                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="produpage layui-form" style="text-align: center">
                        <button class="layui-btn sync_save" lay-submit="" lay-filter="searBtn1" @if(!empty($goods_detail['warehouse_goods']) && $goods_detail['warehouse_goods']['sync'] == 2) disabled style="background-color:#A9A9A9;" @endif>保存</button>
                        &nbsp;
                        <button class="layui-btn" lay-submit="" @if(!empty($goods_detail['warehouse_goods']) && ($goods_detail['warehouse_goods']['sync'] == 2)) disabled style="background-color:#A9A9A9;" @endif lay-filter="searBtn">同步</button>
                        &nbsp;
                        <a class="layui-btn layui-btn-primary" id="back" onclick="" lay-filter="">取消</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('javascripts')
    <script>
        layui.use(['form', 'laydate', 'table', 'element', 'upload'], function () {
            var layer = layui.layer, form = layui.form, laypage = layui.laypage, laydate = layui.laydate;
            var $ = layui.jquery;

            //返回
            $(document).on('click','#back',function () {
                var index = parent.layer.getFrameIndex(window.name);
                parent.layer.close(index);
            });

            //保存
            form.on('submit(searBtn1)', function (data) {
                var info = data.field;
                var index1 = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                //陈雪凝
                $.ajax({
                    url: "/Goods/local/syncGoodsEdit"
                    , type: "get"
                    , dataType: "json"
                    , data: {
                        'param': info
                    }
                    , success: function (res) {
                        if (res.msg == 'success') {
                            layer.msg('保存成功', {icon: 6});
                            setTimeout(function () {
                                parent.layer.close(index1);
                                parent.layui.table.reload('EDtable1');
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
            form.on('submit(searBtn)', function (data) {
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
                        if (res.msg == 'success') {
                            layer.msg('产品信息已同步,请等待仓库审核信息!', {icon: 6});
                            $('.sync_save').attr('disabled',true).css('background-color','#A9A9A9');
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
                            html += '<option value="'+item.category_id+'" selected>'+item.name+'</option>';
                        });
                        html += '</select>';
                        $('tbody tr:last td:nth-child(2) .ware_cate1').empty();
                        $('tbody tr:last td:nth-child(2) .ware_cate1').append(html);
                        $('tbody tr:last td:nth-child(2) .ware_cate2').empty();
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
                            html += '<option value="'+item.category_id+'">'+item.name+'</option>';
                        });
                        html += '</select>';
                        $('tbody tr:last td:nth-child(2) .ware_cate2').empty();
                        $('tbody tr:last td:nth-child(2) .ware_cate2').append(html);
                        form.render();
                    }
                });
                return false;
            });

        });
    </script>
@endsection

