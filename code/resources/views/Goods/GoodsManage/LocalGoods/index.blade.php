@extends('layouts/new_main')
<style>
    .input-upload {
        display: inline-block;
        width: 60%;
        line-height: 30px;
        border: 1px solid lightgray;
        border-radius: 5px;
    }
    .input-upload a{
        display: inline-block;
        line-height: 30px;
        width: 75px;
        text-align: center;
        background: #56a9fb;
        color: white;
    }
    .input-upload a:hover{
        color: white;
    }
    .input-upload span {
        display: inline-block;
        line-height: 30px;
        margin: 0 5px;
    }
    /*.layui-table tbody tr td  .layui-table-cell .laytable-cell-1-order_number img{*/
    /*!*height:auto !important;*!*/
    /*!*width: 20px;*!*/
    /*height:20px;*/
    /*}*/
</style>

@section('content')
    <div class="kbmodel_full">
        <div class="content-wrapper">
            <form class="layui-form multiSearch">
                <ul class="flexSearch flexquar fclear">
                    <li>
                        <div class="inputTxt">产品状态：</div>
                        <div class="inputBlock">
                            <div class="multLable">
                                <input type="radio" name="status" value="" title="全部" checked>
                                <input type="radio" name="status" value="1" title="草稿">
                                <input type="radio" name="status" value="2" title="审核">
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="inputTxt">同步状态：</div>
                        <div class="inputBlock">
                            <div class="multLable">
                                <input type="radio" @if(empty($is_sucess)) disabled @endif name="synchronization" value="" title="全部" checked="">
                                <input type="radio" @if(empty($is_sucess)) disabled @endif name="synchronization" value="0" title="未同步">
                                <input type="radio" @if(empty($is_sucess)) disabled @endif name="synchronization" value="2" title="同步中">
                                <input type="radio" @if(empty($is_sucess)) disabled @endif name="synchronization" value="1" title="同步成功">
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="inputTxt">自定义SKU：</div>
                        <div class="inputBlock">
                            <div class="layui-input-block" style="width: 80%;margin-left: 1px;height: 46px;"
                                 id="checkSKU">
                                <input type="text" name="sku" id="sku" class="layui-input"
                                       style="position:absolute;z-index:1;width:100%;"
                                       lay-verify="" value="" onkeyup="search()" autocomplete="off">
                                <select type="text" id="hc_select" lay-filter="hc_select" autocomplete="off"
                                        placeholder="sku"
                                        lay-verify="" class="layui-select" lay-search>
                                    @if(!empty($goods))
                                    @foreach($goods as $re)
                                        <option value="{{ $re['sku'] }}">{{ $re['sku'] }}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="inputTxt">产品名称：</div>
                        <div class="inputBlock">
                            <input type="text" name="goods_name" placeholder=" " autocomplete="off"
                                   class="voin">
                        </div>
                    </li>
                    <li style="margin-right: 10000px;">
                        <div class="groupBtns">
                            <button class="layui-btn layui-btn-danger"  lay-submit="" lay-filter="searBtn">搜索</button>
                            <button class="layui-btn layui-btn-primary" lay-submit="" lay-filter="reset" >重置</button>
                        </div>
                    </li>
                </ul>
            </form>


            <div class="toolsBtn fclear">
                <div class="infm">

                </div>
                <div class="operate fr">
                    <button class="layui-btn" id="addGoods">添加产品</button>
                    <button class="layui-btn" id="batch_claim" lay-submit="" lay-filter="batch_claim">批量审核</button>
                    <button class="layui-btn" id="import" lay-submit="" lay-filter="import">导入产品</button>
                    <button class="layui-btn synToDraft" lay-submit="" lay-filter="synToDraft" data-type="1">同步亚马逊草稿箱</button>
                    <button class="layui-btn synToDraft" lay-submit="" lay-filter="synToDraft" data-type="2">同步乐天草稿箱</button>
                    <button class="layui-btn export-btn" lay-submit="" lay-filter="export">导出</button>
                </div>
            </div>
            <div class="layui-tab" lay-filter="test1">
                <ul class="layui-tab-title">
                    <li class="layui-this" lay-id="local" data-field="local" class="local_product" >本地产品</li>
                    <li @if(empty($is_sucess)) style="display:none;"  @endif data-field="sync" lay-id="sync">同步到仓库</li>
                </ul>

                <div class="layui-tab-content">
                    <div  class="layui-tab-item layui-show edn-row table_index">
                        <table  id="EDtable" lay-filter="EDtable"></table>
                    </div>

                    <div class="edn-row table_index layui-tab-item">
                        <table id="EDtable1" lay-filter="EDtable1"></table>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <script type="text/html" id="barDemo">
        @{{#  if(d.status === 1){ }}
        {{--<a class="layui-btn layui-btn-xs" lay-event="claim">审核</a>--}}
        {{--<a class="layui-btn layui-btn-danger layui-btn-xs"  lay-event="delete">删除</a>--}}
        {{--<a class="layui-btn layui-btn-xs" lay-event="detail">编辑</a>--}}
        <a class="layui-table-link" lay-event="claim">审核</a>
        <a class="layui-table-link" lay-event="detail">编辑</a>
        <a class="layui-table-link"  lay-event="delete">删除</a>
        @{{#  } }}
        @{{#  if(d.status === 2){ }}
        <a class="layui-table-link" lay-event="detail">查看</a>
        {{--<a class="layui-btn layui-btn-xs" lay-event="detail">查看</a>--}}
        @{{#  } }}
    </script>
    <script type="text/html" id="barDemo1">
        @{{#  if((!d.warehouse_goods) || d.warehouse_goods.sync != 1){ }}
        <a class="layui-table-link" lay-event="edit">编辑</a>

        @{{# }   }}
        @{{#  if((!d.warehouse_goods) || d.warehouse_goods.sync == 0){ }}
        <a class="layui-table-link" lay-event="sync">同步</a>
        @{{# }   }}
        <a class="layui-table-link"  lay-event="detail">查看</a>
    </script>
@endsection

@section('javascripts')
    <script>
        let ids = null;
        let checkedValue = [];
        let checkedData = {};

        layui.config({base: '../../layui/lay/modules/'}).extend({formSelects: 'formSelects-v3'});
        layui.use(['layer', 'form', 'element', 'laydate', 'table', 'formSelects'], function () {
            var layer = layui.layer, form = layui.form, laydate = layui.laydate, table = layui.table,
                element = layui.element, formSelects = layui.formSelects;
            var index = layer.msg('数据请求中', {icon: 16});

            laydate.render({
                elem: '#date2'
            });
            laydate.render({
                elem: '#date1'
            });

            lay('.time-item').each(function () {
                laydate.render({
                    elem: this
                    , trigger: 'click'
                });
            });


            table.render({
                elem: '#EDtable'
                , url: '/Goods/local/LocalGoodsSearch'
                , cols: [[
                    {checkbox: true}
                    , {field: '', title: '序号', width: 50, type: 'numbers'}
                    , {
                        field: 'sku',
                        title: '自定义sku',
                        // event: 'detail',
                        height:100,
                        templet:function(d){
                            // return '<a href="javascript:void(0);" class="layui-table-link">'+d.sku+'</a> <input class="sku-label" type="hidden" value="'+ d.id +'" data-name="'+ d.goods_name +'" >';
                            return d.sku;
                        }
                    }
                    , {
                        field: 'goods_pictures', title: '产品主图', templet: function (d) {
                            if (d.goods_pictures) {
                                return '<img onerror="this.src=\'/img/imgNotFound.jpg\'" src="{{url('showImage').'?path='}}'+d.goods_pictures+'" style="height:24px;width:24px;cursor:zoom-in;" alt=""/>';
                            }
                            return '';
                        },
                        event: 'check_img',
                    }
                    , {field: 'goods_name', title: '产品名称'}
                    , {
                        field: 'plat', title: '产品分类', templet: function (d) {
                            if (d.category1 && d.category2 && d.category3){
                                return d.category1.name + '>' + d.category2.name + '>' + d.category3.name;
                            }
                            return '';
                        }
                    }

                    , {
                        field: 'alternative_price', title: '采购价(RMB)', templet: function (d) {
                            if(d.procurement){
                                return d.procurement.preferred_price ;
                            }else{
                                return '' ;
                            }
                        }
                    }
                    , {field: 'goods_weight', title: '产品重量(KG)'}
                    , {
                        field: '', title: '产品尺寸(CM)', templet: function (d) {
                            return d.goods_length + 'x' + d.goods_width + 'x' + d.goods_height;
                        }
                    }
                    , {
                        field: 'status', title: '产品状态', templet: function (d) {
                            if (d.status === 1) {
                                return '草稿'
                            }
                            if (d.status === 2) {
                                return '审核'
                            }
                        }
                    }
                    , {fixed: 'right', title: '操作', toolbar: '#barDemo', width: 200}
                ]]
                , limit: 20
                , page: true
                , limits: [20, 30, 40, 50]
                , done: function () {   //返回数据执行回调函数
                    layer.close(index);    //返回数据关闭loading
                }
            });

            //同步到仓库
            table.render({
                elem: '#EDtable1'
                , url: '/Goods/local/LocalGoodsSearch?sync=1'
                , cols: [[
//                    {checkbox: true},
                    {field: '', title: '序号', width: 50, type: 'numbers'}
                    , {
                        field: 'sku',
                        title: '自定义sku',
                        // event: 'detail',
                        templet:function(d){
                            // return '<a href="javascript:void(0);" class="layui-table-link">'+d.sku+'</a>';
                            return d.sku;
                        }
                    }
                    ,  {
                        field: 'goods_pictures', title: '产品主图', templet: function (d) {
                            if (d.goods_pictures) {
                                return '<img onerror="this.src=\'/img/imgNotFound.jpg\'" src="{{url('showImage').'?path='}}'+d.goods_pictures+'" style="height:24px;width:24px;cursor:zoom-in;" alt=""/>';
                            }
                            return '';
                        },
                        event: 'check_img',
                    }

                    , {field: 'goods_name', title: '产品名称',templet: function(d) {
                        if(d.warehouse_goods && (d.warehouse_goods.goods_name)) {
                            return d.warehouse_goods.goods_name;
                        }
                        return '';
                        }}
                    , {
                        field: 'plat', title: '仓库分类', templet: function (d) {
                            if(d.warehouse_goods && d.warehouse_goods.category1 && d.warehouse_goods.category2 && d.warehouse_goods.category3) {
                                return d.warehouse_goods.category1.name + '>' + d.warehouse_goods.category2.name + '>' + d.warehouse_goods.category3.name;
                            }
                            return '';
                        }
                    }

                    , {
                        field: 'alternative_price', title: '采购价(RMB)', templet: function (d) {
                            if(d.procurement){
                                return d.procurement.preferred_price ;
                            }else{
                                return '' ;
                            }
                        }
                    }
                    , {field: 'goods_weight', title: '产品重量(KG)'}
                    , {
                        field: '', title: '产品尺寸(CM)', templet: function (d) {
                            if(d.warehouse_goods) {
                                return d.warehouse_goods.goods_length + 'x' + d.warehouse_goods.goods_width + 'x' + d.warehouse_goods.goods_height;
                            }
                            return '';
                        }
                    }
                    , {
                        field: 'status', title: '产品状态', templet: function (d) {
                            if (d.warehouse_goods && (d.warehouse_goods.sync === 1)) {
                                return '同步成功'
                            }
                            if (d.warehouse_goods && (d.warehouse_goods.sync === 2)) {
                                return '同步中'
                            }
                            if((d.warehouse_goods && (!d.warehouse_goods.sync)) || (!d.warehouse_goods)) {
                                return '未同步';
                            }
                        }
                    }
                    , {fixed: 'right', title: '操作', toolbar: '#barDemo1', width: 200}
                ]]
                , limit: 20
                , page: true
                , limits: [20, 30, 40, 50]
                , done: function () {   //返回数据执行回调函数
                    layer.close(index);    //返回数据关闭loading
                }
            });

            form.on('submit(reset)', function (data) {
                window.location.reload(true);
                return false;
            });

            form.on('submit(searBtn)', function (data) {
                var index = layer.msg('数据请求中', {icon: 16});
                var info = data.field;
                var tab = $('.layui-this');
                var field = location.hash.replace(/^#test1=/, '');
                if(field == 'local' || !field) {
                    table.render({
                        elem: '#EDtable'
                        , url: '/Goods/local/LocalGoodsSearch'
                        , where: {data: info}
                        , cols: [[
                            {checkbox: true}
                            , {field: '', title: '序号', width: 50, type: 'numbers'}
                            , {field: 'sku', title: '自定义sku'}
                            , {
                                field: 'goods_pictures', title: '产品主图', templet: function (d) {
                                    if (d.goods_pictures) {
                                        return '<img onerror="this.src=\'/img/imgNotFound.jpg\'" src="{{url('showImage').'?path='}}'+d.goods_pictures+'" style="height:24px;width:24px;cursor:zoom-in;" alt=""/>';
                                    }
                                    return '';
                                },
                                event: 'check_img',
                            }

                            , {field: 'goods_name', title: '产品名称'}
                            , {
                                field: 'category_name', title: '产品分类', templet: function (d) {
                                    if (d.category1 && d.category2 && d.category3){
                                        return d.category1.name + '>' + d.category2.name + '>' + d.category3.name;
                                    }
                                    return '';

                                }
                            }

                            , {
                                field: 'alternative_price', title: '采购价(RMB)', templet: function (d) {
                                    if(d.procurement){
                                        return d.procurement.preferred_price ;
                                    }else{
                                        return '' ;
                                    }
                                }
                            }
                            , {field: 'goods_weight', title: '产品重量(KG)'}
                            , {
                                field: '', title: '产品尺寸(CM)', templet: function (d) {
                                    return d.goods_length + 'x' + d.goods_width + 'x' + d.goods_height;
                                }
                            }
                            , {
                                field: 'status', title: '产品状态', templet: function (d) {
                                    if (d.status === 1) {
                                        return '草稿'
                                    }
                                    if (d.status === 2) {
                                        return '审核'
                                    }
                                }
                            }
                            , {fixed: 'right', title: '操作', toolbar: '#barDemo', width: 200}
                        ]]
                        , limit: 20
                        , page: true
                        , limits: [20, 30, 40, 50]
                        , done: function () {   //返回数据执行回调函数
                            layer.close(index);    //返回数据关闭loading
                        }
                    });
                }

                if(field == 'sync') {
                    //同步到仓库
                    table.render({
                        elem: '#EDtable1'
                        , url: '/Goods/local/LocalGoodsSearch?sync=1'
                        ,where: {data:info}
                        , cols: [[
//                            {checkbox: true},
                            {field: '', title: '序号', width: 50, type: 'numbers'}
                            , {
                                field: 'sku',
                                title: '自定义sku',
                                // event: 'detail',
                                templet:function(d){
                                    // return '<a href="javascript:void(0);" class="layui-table-link">'+d.sku+'</a>';
                                    return d.sku;
                                }
                            }
                            , {
                                field: 'goods_pictures', title: '产品主图', templet: function (d) {
                                    if (d.goods_pictures) {
                                        return '<img onerror="this.src=\'/img/imgNotFound.jpg\'" src="{{url('showImage').'?path='}}'+d.goods_pictures+'" style="height:24px;width:24px;cursor:zoom-in;" alt=""/>';
                                    }
                                    return '';
                                },
                                event: 'check_img',
                            }

                            , {field: 'goods_name', title: '产品名称',templet: function(d) {
                                    if(d.warehouse_goods && (d.warehouse_goods.goods_name)) {
                                        return d.warehouse_goods.goods_name;
                                    }
                                    return '';
                                }}
                            , {
                                field: 'plat', title: '仓库分类', templet: function (d) {
                                    if(d.warehouse_goods && d.warehouse_goods.category1 && d.warehouse_goods.category2 && d.warehouse_goods.category3) {
                                        return d.warehouse_goods.category1.name + '>' + d.warehouse_goods.category2.name + '>' + d.warehouse_goods.category3.name;
                                    }
                                    return '';
                                }
                            }

                            , {
                                field: 'alternative_price', title: '采购价(RMB)', templet: function (d) {
                                    if(d.procurement){
                                        return d.procurement.preferred_price ;
                                    }else{
                                        return '' ;
                                    }
                                }
                            }
                            , {field: 'goods_weight', title: '产品重量(KG)'}
                            , {
                                field: '', title: '产品尺寸(CM)', templet: function (d) {
                                    return d.goods_length + 'x' + d.goods_width + 'x' + d.goods_height;
                                }
                            }
                            , {
                                field: 'status', title: '产品状态', templet: function (d) {
                                    if (d.warehouse_goods && (d.warehouse_goods.sync === 1)) {
                                        return '同步成功'
                                    }
                                    if (d.warehouse_goods && (d.warehouse_goods.sync === 2)) {
                                        return '同步中'
                                    }
                                    if((d.warehouse_goods && (!d.warehouse_goods.sync)) || (!d.warehouse_goods)) {
                                        return '未同步';
                                    }
                                }
                            }
                            , {fixed: 'right', title: '操作', toolbar: '#barDemo1', width: 200}
                        ]]
                        , limit: 20
                        , page: true
                        , limits: [20, 30, 40, 50]
                        , done: function () {   //返回数据执行回调函数
                            layer.close(index);    //返回数据关闭loading
                        }
                    });
                }
                return false;
            });

            //下拉框联想-1
            form.on('select(hc_select)', function (data) {   //选择sku 赋值给input框
                $("#sku").val(data.value);
                $("#hc_select").next().find("dl").css({"display": "none"});
                form.render();
            });

            //下拉框联想-2
            window.search = function () {
                var value = $("#sku").val();
                $("#hc_select").val(value);
                form.render();
                $("#hc_select").next().find("dl").css({"display": "block"});
                var dl = $("#hc_select").next().find("dl").children();
                var j = -1;
                for (var i = 0; i < dl.length; i++) {
                    if (dl[i].innerHTML.indexOf(value) <= -1) {
                        dl[i].style.display = "none";
                        j++;
                    }
                    if (j == dl.length - 1) {
                        $("#hc_select").next().find("dl").css({"display": "none"});
                    }
                }
                $(document).click(function () { //点击后隐藏下拉框元素
                    $("#hc_select").next().find("dl").css({"display": "none"});
                })
            };

            table.on('tool(EDtable)', function (obj) {
                var data = obj.data;
                if (obj.event === 'delete') {
                    layer.confirm('确认删除自定义SKU:'+data.sku, {title: '提示'}, function (index) {
                        $.ajax({
                            url: "/Goods/local/goodsDel"
                            , type: "get"
                            , dataType: "json"
                            , data: {
                                'id': data.id
                            }

                            , success: function (res) {
                                if (res.msg == 'success') {
                                    layer.msg('删除成功!', {icon: 6});
                                    setTimeout(function () {  //使用  setTimeout（）方法设定定时2000毫秒
                                        window.location.reload();//页面刷新
                                    }, 1000);
                                } else {
                                    layer.msg('删除失败!', {icon: 6});
                                }
                            }
                        });
                        layer.close(index);
                    });
                }else if(obj.event === 'claim'){
                    //审核
                    layer.confirm('审核产品信息后将不能修改,请确认是否审核', {title: '提示'}, function (index) {
                        $.ajax({
                            url: "/Goods/local/localGoodsCheck"
                            , type: "get"
                            , dataType: "json"
                            , data: {
                                'id': data.id
                            }

                            , success: function (res) {
                                if (res.msg == 'success') {
                                    layer.msg('审核成功!', {icon: 6});
                                    setTimeout(function () {  //使用  setTimeout（）方法设定定时2000毫秒
                                        window.location.reload();//页面刷新
                                    }, 1000);
                                } else {
                                    layer.msg('审核失败!', {icon: 6});
                                }
                            }
                        });
                        layer.close(index);
                    });
                }else if(obj.event === 'detail'){
                    layer.open({
                        type: 2,
                        title: '产品详情',
                        fix: false,
                        maxmin: true,
                        resize: true,
                        shadeClose: true,
                        offset: 'r',
                        area: ['80%', '90%'],
                        content: '{{ url('Goods/local/addGoodsIndex') }}?id='+data.id,
                        end: function end() {

                        }
                    });
                }else if(obj.event === 'check_img'){
                    if (data.goods_pictures) {
                        check_img(data.goods_pictures);
                    }
                }
            });

            //同步到仓库
            table.on('tool(EDtable1)', function (obj) {
                var data = obj.data;
                if (obj.event === 'edit') {
                    layer.open({
                        type: 2,
                        title: '编辑产品',
                        fix: false,
                        maxmin: true,
                        resize: true,
                        shadeClose: true,
                        offset: 'r',
                        area: ['80%', '90%'],
                        content: '{{ url('Goods/local/editbGoods')}}?id='+data.id,
                        end: function end() {

                        }
                    });

                }else if(obj.event === 'sync'){
                    $.ajax({
                        url: "/Goods/local/syncGoodsList"
                        , type: "get"
                        , dataType: "json"
                        , data: {
                            'id': data.id
                        }

                        , success: function (res) {
                            if (res.code == 1) {
                                layer.msg('产品信息已同步,请等待仓库审核信息!', {icon: 6});
                                setTimeout(function() {
                                    window.location.reload();
                                },1000);
                            } else {
                                if (res.msg == 'fail') {
                                    layer.msg('同步失败!', {icon: 5});
                                } else {
                                    layer.msg(res.msg, {icon: 5});
                                }
                            }
                        }
                    });
                }else if(obj.event === 'detail'){
                    layer.open({
                        type: 2,
                        title: '产品详情',
                        fix: false,
                        maxmin: true,
                        resize: true,
                        shadeClose: true,
                        offset: 'r',
                        area: ['80%', '90%'],
                        content: '{{ url('Goods/local/synDetail') }}?id='+data.id,
                        end: function end() {

                        }
                    });
                }else if(obj.event === 'check_img'){
                    if (data.goods_pictures) {
                        check_img(data.goods_pictures);
                    }
                }
            });

            table.on('checkbox(EDtable)', function (obj) {
                var checkStatus = table.checkStatus('EDtable');
                var id_array = new Array();
                var data = checkStatus.data;

                if (data.length > 0) {
                    for (var i = 0; i < data.length; i++) {
                        id_array.push(data[i].id);
                    }
                }
                ids = id_array.join(',');
            });

            //添加产品
            $('#addGoods').click(function () {
                layer.open({
                    type: 2,
                    title: '添加产品',
                    fix: false,
                    maxmin: true,
                    resize: true,
                    shadeClose: true,
                    offset: 'r',
                    area: ['80%', '90%'],
                    content: '{{ url('Goods/local/addGoodsIndex') }}',
                    end: function end() {
                    }
                });
            });

            $('#BatchDel').click(function () {
                if (ids == '') {
                    layer.msg('请选择产品');
                    return false;
                } else {
                    var elect = $('tbody .layui-form-checked').parents('tr');
                    layer.confirm('确认删除所选项？', {
                        btn: ['确定', '取消'],
                        yes: function (index) {
                            $.ajax({
                                url: "/Goods/del"
                                , type: "get"
                                , dataType: "json"
                                , data: {
                                    'id': ids
                                }

                                , success: function (res) {
                                    if (res.status) {
                                        layer.msg('删除成功!', {icon: 6});
                                        setTimeout(function () {  //使用  setTimeout（）方法设定定时2000毫秒
                                            //window.location.reload();//页面刷新
                                            elect.remove();
                                        }, 1000);
                                    } else {
                                        layer.msg('删除失败!', {icon: 6});
                                    }
                                }
                            });
                            layer.close(index);
                        }
                    })
                }
            });

            form.on('submit(import)', function (data) {
                layer.open({
                    title: "文件上传",
                    area: ['510px', '220px'],
                    content: ' XLS文件：' +
                        '<div class="input-upload"><a class="upload-btn" id="upload-btn">点击上传</a><span id="input-file-name">请选择...</span></div>' +
                        '<input style="display: none" id="excel-import" type="file" class="" formenctype="multipart/form-data"> <a href="/file/本地产品导入模板.xlsx" style="color: dodgerblue;">《产品模板下载》</a>',
                    yes: function (index, layero) {
                        var data = new FormData;
                        var files = document.getElementById('excel-import').files[0];
                        data.append('import', document.getElementById('excel-import').files[0]);
                        data.append('_token', '{{csrf_token()}}');
                        $.ajax({
                            type: 'post',
                            url: 'importGoods',
                            data: data,
                            cache: false,
                            processData: false,
                            contentType: false,
                            enctype: 'multipart/form-data',
                            dataType: 'json',
                            headers: {
                                'X-CSRF-TOKEN': '{{csrf_token()}}'
                            },
                            success: function (result) {
                                if (result.code == 0) {
                                    layer.alert(result.msg, {icon: 1, yes: function () {
                                            window.location.reload();
                                        }});
                                    return false;
                                } else {
                                    layer.alert(result.msg, {icon: 2});
                                    return false;
                                }


                            },
                            error: function (result) {
                                layer.alert(result.msg, {icon: 2})
                            }
                        })
                    }
                });
                return false;
            });

            // table.on('checkbox(EDtable)', function (e) {
            //     if (!e.checked) {
            //         if (e.type == 'one') {
            //             let index = checkedValue.indexOf(e.data.id)
            //             checkedValue.splice(index, 1)
            //             delete checkedData[e.data.id]
            //         }
            //         if (e.type == 'all') {
            //             checkedValue = [];
            //             checkedData = {};
            //         }
            //     } else {
            //         if (e.type == 'all') {
            //             let skuEle = $(".sku-label");
            //             $.each(skuEle, function (k, v) {
            //                 checkedValue.push($(this).val());
            //                 checkedData[$(this).val()] = $(this).attr('data-name')
            //             });
            //         }
            //         if (e.type == 'one') {
            //             checkedValue.push(e.data.id)
            //             checkedData[e.data.id] = e.data.goods_name
            //         }
            //     }
            // });

            form.on('submit(batch_claim)', function (e) {
                if (!ids) {
                    if (checkedValue.length == 0) {
                        layer.msg('请选择至少一条数据审核', {icon: 2});
                        return false;
                    }
                    layer.msg('请选择至少一条数据审核', {icon: 2});
                    return false;
                }

//                var ids = checkedValue.join(',');
                $.ajax({
                    url: "/Goods/local/localGoodsCheck"
                    , type: "get"
                    , dataType: "json"
                    , data: {
                        'id': ids
                    }
                    , success: function (res) {
                        if (res.msg == 'success') {
                            layer.msg('批量审核成功!', {icon: 6});
                            setTimeout(function () {  //使用  setTimeout（）方法设定定时2000毫秒
                                window.location.reload();//页面刷新
                            }, 1000);
                        } else {
                            layer.msg('批量审核失败!', {icon: 5});
                        }
                    }
                });
            })

            form.on('submit(export)', function (e) {
                if (ids == '') {
                    if (checkedValue.length == 0) {
                        layer.msg('请选择至少一条数据进行导出', {icon: 2});
                        return false;
                    }
                    layer.msg('请选择至少一条数据进行导出', {icon: 2});
                    return false;
                }
                // if (ids == '' || checkedValue.length == 0) {
                //     console.log (ids);
                //     layer.msg('请选择至少一条数据进行导出', {icon: 2});
                //     return false;
                // }
                // ids = checkedValue.join(',');
                $.ajax({
                    type: 'get',
                    url: '/Goods/local/exportGoods?ids=' +ids+'&check=1',
                    success: function (res) {
                        if (res.Status) {
                            layer.msg(res.Message, {icon: 1});
                            window.location.href = '/Goods/local/exportGoods?ids=' +ids
                        } else {
                            layer.msg(res.Message, {icon: 5});
                        }
                    }
                });
            })

            $(document).on('click', '.synToDraft', function (e) {
                let platform = $(this).attr('data-type');

                if (ids == '') {
                    if (checkedValue.length == 0) {
                        layer.msg('请选择至少一条数据进行同步', {icon: 2});
                        return false;
                    }
                    layer.msg('请选择至少一条数据进行同步', {icon: 2});
                    return false;
                }
                if (!ids) {
                    layer.msg('请选择至少一条数据进行同步', {icon: 2});
                    return false;
                }
                var check = [];
                if (ids) {
                    check = ids.split(',');
                }
                $.post('/Goods/local/synToDraft', {platform: platform, synchronizeData: check,
                    synchronizeGoodsName: checkedData, _token: '{{csrf_token()}}'}, function (res) {
                    if (res.code !== 0) {
                        layer.msg(res.msg, {icon: 2});
                        return false;
                    }
                    layer.msg(res.msg, {icon: 1});
                    setTimeout(function () {  //使用  setTimeout（）方法设定定时2000毫秒
                        window.location.reload();//页面刷新
                    }, 1000);
                    return true;
                })
            });

            $(document).on('click', '#upload-btn', function (e) {
                $("#excel-import").click();
            });

            $(document).on('change', '#excel-import', function (e) {
                let filename = $(this).val().split('\\');
                let cutLength = 20;
                filename = filename[filename.length - 1];

                if (filename.length > 13) {
                    filename = filename.slice(0, cutLength) + '...';
                }
                $("#input-file-name").text(filename)
            });
        });


        layui.use('element', function(){
            var element = layui.element;

            //获取hash来切换选项卡，假设当前地址的hash为lay-id对应的值
            var layid = location.hash.replace(/^#test1=/, '');
            element.tabChange('test1', layid);


            //监听Tab切换，以改变地址hash值
            element.on('tab(test1)', function(){
                let attrId = this.getAttribute('lay-id');
                if (attrId == 'sync') {
                    $(".export-btn").hide();
                } else {
                    $(".export-btn").show();
                }
                location.hash = 'test1='+ attrId;
            });
        });

    </script>
@endsection