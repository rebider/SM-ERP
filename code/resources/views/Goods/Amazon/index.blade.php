@extends('layouts/new_main')
@section('head')
    <style type="text/css">
        .form-search-group {
            display: inline-block;
            margin: 10px 20px;
        }
        .element-display-inline {
            display: inline-block;
        }
    </style>
@endsection
@section('content')
    <div class="kbmodel_full">
        <div class="content-wrapper">
            <div class="multiSearch">
                <form name="search" class="layui-form layui-form-pane" action="">
                    <div class="layui-form-item">

                        <div class="second">
                            <!--订单号-->
                            <div class="form-search-group">
                                <div class="inputTxt element-display-inline">自定义SKU：</div>
                                <div class="inputBlock element-display-inline">
                                    <input type="text" name="local_sku" autocomplete="off" class="layui-input">
                                </div>
                            </div>

                            <div class="form-search-group">
                                <div class="inputTxt element-display-inline">商品名称：</div>
                                <div class="inputBlock element-display-inline">
                                    <input type="text" name="goods_name" autocomplete="off" class="layui-input">
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="layui-form-item" style="margin-left: 105px">
                        <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="searBtn" style="width: 60px;">搜索</button>
                        <button class="layui-btn layui-btn-primary" lay-submit="" lay-filter="reset" style="width: 60px;">重置</button>
                    </div>
                </form>

            </div>
            <div class="toolsBtn fclear">
                <div class="operate fr"><button class="layui-btn" id="PutOn">上架商品</button></div>
                <div class="operate fr"><button class="layui-btn" id="BatchPutOn">批量上架</button></div>
            </div>

            <div class="layui-tab" lay-filter="test1">
                <ul class="layui-tab-title">
                    <li class="layui-this" lay-id="111" >草稿</li>
                    <li lay-id="222">上架失败</li>
                </ul>

                <div class="layui-tab-content">
                    <div class="vod_table layui-form layui-tab-item layui-show">
                        <table class="" id="EDtable" lay-filter="EDtable"></table>
                    </div>
                    <div class="vod_table layui-form layui-tab-item">
                        <table class="" id="EDtable1" lay-filter="EDtable1"></table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
@section('javascripts')
    <script type="text/html" id="barDemo">
        @{{#  if(d.synchronize_status === 1){ }}
        {{--<a  class="set-gray" href="javascript:;" lay-event="putOnSale">上架</a>&nbsp;&nbsp;--}}
        {{--<a  class="layui-table-link" href="javascript:;" lay-event="edit">编辑</a>&nbsp;&nbsp;--}}
        {{--<a  class="set-gray" href="javascript:;" lay-event="del">删除</a>&nbsp;&nbsp;--}}

        <a  class="layui-table-link" href="javascript:;" lay-event="putOnSale">上架</a>&nbsp;&nbsp;
        <a  class="layui-table-link" href="javascript:;" lay-event="edit">编辑</a>&nbsp;&nbsp;
        <a  class="layui-table-link" href="javascript:;" lay-event="del">删除</a>&nbsp;&nbsp;

        @{{#  } }}

        @{{#  if(d.synchronize_status === 2){ }}
        {{--<a  class="set-gray" href="javascript:;" lay-event="putOnSale">上架</a>&nbsp;&nbsp;--}}
        {{--<a  class="layui-table-link" href="javascript:;" lay-event="edit">编辑</a>&nbsp;&nbsp;--}}
        {{--<a  class="set-gray" href="javascript:;" lay-event="del">删除</a>&nbsp;&nbsp;--}}
        <a  class="layui-table-link" href="javascript:;" lay-event="putOnSale">上架</a>&nbsp;&nbsp;
        <a  class="layui-table-link" href="javascript:;" lay-event="edit">编辑</a>&nbsp;&nbsp;
        <a  class="layui-table-link" href="javascript:;" lay-event="del">删除</a>&nbsp;&nbsp;
        @{{#  } }}

        @{{#  if(d.synchronize_status === 3){ }}
        {{--<a  class="set-gray" href="javascript:;" lay-event="putOnSale">上架</a>&nbsp;&nbsp;--}}
        {{--<a  class="layui-table-link" href="javascript:;" lay-event="edit">编辑</a>&nbsp;&nbsp;--}}
        {{--<a  class="set-gray" href="javascript:;" lay-event="del">删除</a>&nbsp;&nbsp;--}}
        <a  class="layui-table-link" href="javascript:;" lay-event="putOnSale">上架</a>&nbsp;&nbsp;
        <a  class="layui-table-link" href="javascript:;" lay-event="edit">编辑</a>&nbsp;&nbsp;
        <a  class="layui-table-link" href="javascript:;" lay-event="del">删除</a>&nbsp;&nbsp;
        @{{#  } }}
    </script>
    <script>
        layui.use(['form', 'laydate','table','element'], function(){
            var layer = layui.layer,
                form = layui.form,
                laypage = layui.laypage,
                element = layui.element,
                table = layui.table,
                laydate = layui.laydate;
            ids='';
            var index = layer.msg('数据请求中', {icon: 16});
            // -------start------- zt8067 2019/07/02修复tab重复ids BUG
            var indexCheck = 0;
            //获取hash来切换选项卡，假设当前地址的hash为lay-id对应的值
            var layid = location.hash.replace(/^#test1=/, '');
            element.tabChange('test1', layid);
            if(layid==111){
                indexCheck = 0;
            }else if(layid == 222){
                indexCheck = 1;
            }
            element.on('tab(test1)', function(data){
                console.log(indexCheck,data.index,ids)
                location.hash = 'test1=' + this.getAttribute('lay-id');
                switch (data.index) {
                    case 0:
                        $("div[lay-filter='LAY-table-2']").find("input[type='checkbox']").prop("checked", false);
                        break;
                    case 1:
                        $("div[lay-filter='LAY-table-1']").find("input[type='checkbox']").prop("checked", false);
                        break;
                }
                if(indexCheck === data.index)
                {
                    return false;
                }
                indexCheck = data.index;
                form.render('checkbox');
                ids = '' ;
            });
            lay('.time-item').each(function(){
                laydate.render({
                    elem: this
                    ,trigger: 'click'
                });
            });
            //-------end-------zt8067 2019/07/02修复tab重复ids BUG
            //---------------------------表格部分开始↓--------------------------
            //封装render
            let table1 = function (info, element, index) {
                let colsParamsWithoutReason  = [[
                    {checkbox: true}
                    , {field: '', title: '序号', width: 50, type: 'numbers'}
                    , {field:'local_sku' , title:'自定义sku'}
                    {{--,{field:'order_number',  title:'商品主图', height:"80px" ,templet: function (d) {--}}
                            {{--return '<img src="{{url('showImage').'?path='}}'+d.img_url+'" style="height:24px;width:24px;" alt=""/>';--}}
                    {{--},--}}
                        {{--event: 'check_img',--}}
                    {{--}--}}
                    , {
                        field: 'img_url', title: '商品主图', height: "80px", templet: function (d) {
                            if (d.img_url) {
                                return '<img onerror="this.src=\'/img/imgNotFound.jpg\'" src="{{url('showImage').'?path='}}'+d.img_url+'" style="height:24px;width:24px;cursor:zoom-in;" alt=""/>';
                            }
                            return '';
                        },
                        event: 'check_img',
                    }

                    , {field:'goods_name' , title:'商品名称' , templet: function (d) {
                        if (d.goods_name == null) {
                            return '';
                        } else {
                            return d.goods_name;
                        }
                    }}

                    , {field:'shop_name' , title:'店铺' , templet: function (d) {
                        if (d.shops == null) {
                            return '';
                        } else {
                            return d.shops.shop_name
                        }
                    }}
                    ,{field:'preferred_price', title:'采购价(RMB)', templet: function (d) {
                        if (d.procurement == null) {
                            return '';
                        } else {
                            return d.procurement.preferred_price
                        }
                    }}
                    ,{field:'sale_price', title:'销售价格'}
                    ,{field:'currency_code', title:'币种'}
                    ,{field:'goods_weight', title:'商品重量(KG)'}
                    ,{field:'', title:'商品尺寸(CM)', templet: function(d){
                        return d.goods_length +'x'+d.goods_width + 'x'+ d.goods_height ;
                    }}
                    ,{field:'synchronize_status', title:'商品状态', templet: function(d){
                        if (d.synchronize_status === 1) { return '草稿'}
                        if (d.synchronize_status === 2) { return '上架失败'}
                        if (d.synchronize_status === 3) { return '已上架'}
                        if (d.synchronize_status === 4) { return '已下架'}
                        if (d.synchronize_status === 5) { return '更新失败'}
                    }}
                    ,{fixed: 'right', title:'操作', toolbar: '#barDemo', width:200}
                ]];
                let colsParamsWithReason = [[
                    {checkbox: true}
                    , {field: '', title: '序号', width: 50, type: 'numbers'}
                    , {field:'local_sku' , title:'自定义sku'}
                    , {
                        field: 'img_url', title: '商品主图', height: "80px", templet: function (d) {
                            if (d.img_url) {
                                return '<img onerror="this.src=\'\'" src="{{url('showImage').'?path='}}'+d.img_url+'" style="height:24px;width:24px;cursor:zoom-in;" alt=""/>';
                            }
                            return '';
                        },
                        event: 'check_img',
                    }

                    , {field:'title' , title:'商品名称'}

                    , {field:'shop_name' , title:'店铺' , templet: function (d) {
                        if (d.shops == null) {
                            return '';
                        } else {
                            return d.shops.shop_name
                        }
                    }}
                    ,{field:'preferred_price', title:'采购价(RMB)', templet: function (d) {
                        if (d.procurement == null) {
                            return '';
                        } else {
                            return d.procurement.preferred_price
                        }
                    }}
                    ,{field:'sale_price', title:'销售价格'}
                    ,{field:'currency_code', title:'币种'}
                    ,{field:'goods_weight', title:'商品重量(KG)'}
                    ,{field:'', title:'商品尺寸(CM)', templet: function(d){
                        return d.goods_length +'x'+d.goods_width + 'x'+ d.goods_height ;
                    }}
                    ,{field:'synchronize_status', title:'商品状态', templet: function(d){
                        return '上架失败';
//                        if (d.synchronize_status === 1) { return '草稿'}
//                        if (d.synchronize_status === 2) { return '上架失败'}
//                        if (d.synchronize_status === 3) { return '已上架'}
//                        if (d.synchronize_status === 4) { return '已下架'}
//                        if (d.synchronize_status === 5) { return '更新失败'}
                    }}
                    ,{field:'synchronize_info', title:'原因'}
                    ,{fixed: 'right', title:'操作', toolbar: '#barDemo', width:200}
                ]];
                table.render({
                    elem: element
                    ,url:'/Goods/amazon/ajaxGetAllByParams'
                    ,parseData: function (res) {
                        return {
                            "code": res.code,
                            "data": res.data.data,
                            "message": res.msg,
                            "count": res.total
                        }
                    }
                    ,where:{data:info}
                    ,cols: element == '#EDtable' ? colsParamsWithoutReason : colsParamsWithReason
                    ,limit:20
                    ,page: true
                    ,limits:[20,30,40,50]
                    ,done:function () {   //返回数据执行回调函数
                        layer.close(index);    //返回数据关闭loading
                    }
                })
            };
            //document加载完成后响应表格渲染
            $(document).ready(function () {
                var index = layer.msg('数据请求中', {icon: 16});
                table1({}, '#EDtable', index);
                table1({synchronizeType:3}, '#EDtable1', index);
            })

            form.on('submit(reset)', function (data) {
                window.location.reload(true);
                return false;
            });

            //点击搜索框根据当前Tab类型执行相应的表格进行渲染
            form.on('submit(searBtn)', function(data){
                var index = layer.msg('数据请求中', {icon: 16});
                var info = data.field;
                var layid = location.hash.replace(/^#test1=/, '');
                var element = '';
                if (layid == 222) {
                    element = '#EDtable1';
                    info['synchronizeType'] = 3;
                    console.log(info)
                } else {
                    element = '#EDtable'
                }
                table1(info, element, index);
                return false;

            });
            //--------------------------表格初始化结束↑------------------------



            table.on('tool(EDtable1)', function(obj){
                var data = obj.data;
                if(obj.event === 'check_img') {
                    check_img(data.img_url)
                }
                if(obj.event === 'del'){
                    layer.confirm('确认删除', {title:'提示'}, function(index){
                        //ajax 删除采集商品
                        $.ajax({
                            url: "/Goods/amazon/delete"
                            , type: "get"
                            , dataType: "json"
                            , data: {
                                'id':data.id
                            }

                            , success: function (res) {
                                if(res.code) {
                                    layer.msg(res.msg ,{icon:1});
                                    setTimeout(function(){  //使用  setTimeout（）方法设定定时2000毫秒
                                        window.location.reload();
                                    },1000);
                                }else {
                                    layer.msg(res.msg ,{icon:5});
                                }
                            }
                        });
                        layer.close(index);
                    });
                }

                //草稿箱编辑
                if(obj.event === 'edit'){
                    layer.open({
                        type: 2,
                        title: '草稿编辑',
                        fix: false,
                        maxmin: true,
                        shadeClose: true,
                        offset:'r',
                        area: ['80%', '90%'],
                        content: '{{ url('Goods/amazon/editGoods') }}' + '?id=' + data.id,
                        end: function (index) {
                            $.ajax({
                                url: ""
                                , type: "get"
                                , dataType: "json"
                                , data: {
                                    'id':data.id
                                }
                                , success: function (res) {
                                    if(res) {
                                        layer.msg(res.msg ,{icon:1});
                                        return false
//                                        setTimeout(function(){  //使用  setTimeout（）方法设定定时2000毫秒
//                                            window.location.reload();//页面刷新
//                                        },1000);
                                    }else {
                                        layer.msg(res.msg ,{icon:5});
                                    }
                                }
                            });
                        }
                    });
                    layer.close(index);
                }

                //上架
                if(obj.event === 'putOnSale'){
                    layer.confirm('确认上架', {title:'提示'}, function(index){
                        //ajax 删除采集商品
                        $.ajax({
                            url: "/Goods/amazon/PutOnSaleById"
                            , type: "get"
                            , dataType: "json"
                            , data: {
                                'id':data.id
                            }

                            , success: function (res) {
                                if(res.status) {
                                    layer.msg(res.msg ,{icon:1});
                                    setTimeout(function(){  //使用  setTimeout（）方法设定定时2000毫秒
                                        window.location.reload();//页面刷新
                                    },1000);
                                }else {
                                    layer.msg(res.msg ,{icon:5});
                                }
                            }
                        });
                        layer.close(index);
                    });
                }
            });



            table.on('tool(EDtable)', function(obj){
                var data = obj.data;
                if(obj.event === 'del'){
                    layer.confirm('确认删除', {title:'提示'}, function(index){
                        //ajax 删除采集商品
                        $.ajax({
                            url: "/Goods/amazon/delete"
                            , type: "get"
                            , dataType: "json"
                            , data: {
                                'id':data.id
                            }

                            , success: function (res) {
                                if(res.code) {
                                    layer.msg(res.msg ,{icon:1});
                                    setTimeout(function(){  //使用  setTimeout（）方法设定定时2000毫秒
                                        window.location.reload();
                                    },1000);
                                }else {
                                    layer.msg(res.msg ,{icon:5});
                                }
                            }
                        });
                        layer.close(index);
                    });
                }

                //草稿箱编辑
                if(obj.event === 'edit'){
                    layer.open({
                        type: 2,
                        title: '草稿编辑',
                        fix: false,
                        maxmin: true,
                        shadeClose: true,
                        offset:'r',
                        area: ['80%', '90%'],
                        content: '{{ url('Goods/amazon/editGoods') }}' + '?id=' + data.id,
                        end: function (index) {
                            $.ajax({
                                url: ""
                                , type: "get"
                                , dataType: "json"
                                , data: {
                                    'id':data.id
                                }
                                , success: function (res) {
                                    if(res) {
                                        layer.msg(res.msg ,{icon:1});
                                        setTimeout(function(){  //使用  setTimeout（）方法设定定时2000毫秒
                                            window.location.reload();//页面刷新
                                        },1000);
                                    }else {
                                        layer.msg(res.msg ,{icon:5});
                                    }
                                }
                            });
                        }
                    });
                    layer.close(index);
                }

                //上架
                if(obj.event === 'putOnSale'){
                    layer.confirm('确认上架', {title:'提示'}, function(index){
                        //ajax 删除采集商品
                        $.ajax({
                            url: "/Goods/amazon/PutOnSaleById"
                            , type: "get"
                            , dataType: "json"
                            , data: {
                                'id':data.id
                            }

                            , success: function (res) {
                                if(res.status) {
                                    layer.msg(res.msg ,{icon:1});
                                    setTimeout(function(){  //使用  setTimeout（）方法设定定时2000毫秒
                                        window.location.reload();//页面刷新
                                    },1000);
                                }else {
                                    layer.msg(res.msg ,{icon:5});
                                }
                            }
                        });
                        layer.close(index);
                    });
                }

                if(obj.event === 'check_img') {
                    if (data.img_url) {
                        check_img(data.img_url)
                    }
                }
            });

            //-------start-------zt8067 2019/07/02修复tab重复ids BUG
            function _checkbox(name){
                table.on('checkbox('+ name +')', function (obj) {
                    var checkStatus = table.checkStatus(name);
                    var id_array = new Array();
                    var data = checkStatus.data;

                    if (data.length > 0) {
                        for (var i = 0; i < data.length; i++) {
                            id_array.push(data[i].id);
                        }
                    }
                    ids = id_array.join(',');
                });
            }
            _checkbox('EDtable');
            _checkbox('EDtable1');
            //-------end-------zt8067 2019/07/02修复tab重复ids BUG

            // checkbox all
            form.on('checkbox(allChoose)', function (data) {
                $("input[name='check[]']").each(function () {
                    this.checked = data.elem.checked;
                });
                form.render('checkbox');
            });
            form.on('checkbox(oneChoose)', function (data) {
                var i = 0;
                var j = 0;
                $("input[name='check[]']").each(function () {
                    if( this.checked === true ){
                        i++;
                    }
                    j++;
                });
                if( i == j ){
                    $(".checkboxAll").prop("checked",true);
                    form.render('checkbox');
                }else{
                    $(".checkboxAll").removeAttr("checked");
                    form.render('checkbox');
                }

            });

            $('#BatchPutOn').click(function(){
                if(ids==''){
                    layer.msg('请选择商品');
                    return false;
                }else{
                    var elect = $('tbody .layui-form-checked').parents('tr');
                    $.ajax({
                        url: "/Goods/amazon/PutOnSaleById"
                        , type: "get"
                        , dataType: "json"
                        , data: {
                            'id':ids
                        }
                        , success: function (res) {
                            if(res.status) {
                                layer.msg(res.msg ,{icon:1});
                                setTimeout(function(){  //使用  setTimeout（）方法设定定时2000毫秒
                                    //window.location.reload();//页面刷新
//                                        elect.remove();
                                },1000);
                            }else {
                                layer.msg(res.msg ,{icon:5});
                            }
                        }
                    });
                }
            });

            $('#PutOn').click(function(){
                layer.open({
                    title:"上架商品",
                    type: 2,
                    area:["600px","260px"],
                    content: ['{{ url('Goods/amazon/add') }}','no'],
                    btn:['确定','取消'],
                    btn1: function(index){
                        var info = window["layui-layer-iframe" + index].callbackdata();
                        if(!info.sku){
                            layer.msg('sku不能为空');
                            return;
                        }
                        //判断输入的售后单是否存在
                        $.get('/Goods/amazon/checkAmazonGoods', {sku: info.sku}, function (e) {
                            if(e.code < 0) {
                                layer.msg(e.msg);
                                return;
                            } else {
                                layer.close(index);
                                layer.open({
                                    type: 2,
                                    title: '商品上架',
                                    fix: false,
                                    maxmin: true,
                                    shadeClose: true,
                                    area: ['1400px', '800px'],
                                    content: '{{ url('Goods/amazon/amazonGoodsSaleOn') }}?sku='+info.sku,
                                    end: function (index) {
                                        layer.close(index);
                                    }
                                });
                            }
                        });
                    }
                });
            });



        });
         // zt8067 干掉 zt12779 bug代码
        // layui.use('element', function(){
        //     var element = layui.element;
        //
        //     //获取hash来切换选项卡，假设当前地址的hash为lay-id对应的值
        //     var layid = location.hash.replace(/^#test1=/, '');
        //     element.tabChange('test1', layid); //假设当前地址为：http://a.com#test1=222，那么选项卡会自动切换到“发送消息”这一项
        //
        //     //监听Tab切换，以改变地址hash值
        //     element.on('tab(test1)', function(){
        //         location.hash = 'test1='+ this.getAttribute('lay-id');
        //     });
        //
        // });


    </script>
@endsection
