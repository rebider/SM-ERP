@extends('layouts/new_main')
@section('content')
    <div class="kbmodel_full">
        <div class="content-wrapper">
            <div class="multiSearch">
                <form name="search" class="layui-form layui-form-pane" action="">

                    <div class="layui-form-item" pane="">
                        <label class="layui-form-label">产品状态</label>
                        <div class="layui-input-block">
                            <input type="radio" name="status" value="0" title="全部" checked="">
                            <input type="radio" name="status" value="1" title="草稿">
                            <input type="radio" name="status" value="2" title="审核">
                        </div>
                    </div>
                    <div class="layui-form-item" pane="">
                        <label class="layui-form-label">同步状态</label>
                        <div class="layui-input-block">
                            <input type="radio" name="synchronization" value="0" title="全部" checked="">
                            <input type="radio" name="synchronization" value="1" title="未同步">
                            <input type="radio" name="synchronization" value="2" title="同步成功">
                            <input type="radio" name="synchronization" value="3" title="同步失败">
                        </div>
                    </div>

                    <div class="layui-form-item">

                        <div class="layui-inline">
                            <label class="layui-form-label">自定义SKU</label>
                            <div class="layui-input-inline" style="width: 100px;">
                                <input type="password" name="sku" autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">产品名称</label>
                            <div class="layui-input-inline" style="width: 100px;">
                                <input type="password" name="goods_name" autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">产品分类</label>
                            <div class="layui-input-block">
                                <select name="category_id_1" lay-verify="">
                                    <option value=""></option>
                                    <option value="0">北京</option>
                                    <option value="1">上海</option>
                                    <option value="2">广州</option>
                                    <option value="3">深圳</option>
                                    <option value="4">杭州</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <button class="layui-btn" lay-submit="" lay-filter="searBtn">搜索</button>
                    </div>
                </form>
            </div>
            <div class="toolsBtn fclear">
                <div class="operate fr"><button class="layui-btn" id="BatchDel">添加产品</button></div>
                <div class="operate fr"><button class="layui-btn" id="BatchDel">导入产品</button></div>
                <div class="operate fr"><button class="layui-btn" id="BatchDel">同步亚马逊</button></div>
                <div class="operate fr"><button class="layui-btn" id="BatchDel">同步乐天草稿箱</button></div>
                <div class="operate fr"><button class="layui-btn" id="BatchDel">导出</button></div>
            </div>
            <div class="vod_table layui-form">
                <table class="" id="EDtable" lay-filter="EDtable"></table>
            </div>
        </div>
    </div>
@endsection
@section('javascripts')
    <script type="text/html" id="barDemo">
        @{{#  if(d.status === 1){ }}
        <a class="layui-table-link" href="javascript:;" lay-event="claim">审核</a>&nbsp;&nbsp;
        <a class="layui-table-link" href="javascript:;" lay-event="delete">删除</a>&nbsp;&nbsp;
        @{{#  } }}

        @{{#  if(d.status === 2){ }}
        <a href class="set-gray" href="javascript:;">查看</a>&nbsp;&nbsp;
        @{{#  } }}
    </script>
    <script>
        layui.use(['form', 'laydate','table'], function(){
            var layer = layui.layer,
                form = layui.form,
                laypage = layui.laypage,
                table = layui.table,
                laydate = layui.laydate;
            ids='';
            var index = layer.msg('数据请求中', {icon: 16});

            laydate.render({
                elem: '#date2'
            });
            laydate.render({
                elem: '#date1'
            });

            lay('.time-item').each(function(){
                laydate.render({
                    elem: this
                    ,trigger: 'click'
                });
            });


            /*table*/

            table.render({
                elem: '#EDtable'
                ,url:'/Mapping/ajaxGetAllLocaGoodsByParams'
                ,cols: [[
                    {checkbox: true}
                    , {field:'id' , title:'序号'}
                    , {field:'sku' , title:'自定义SKU'}
                    ,{field:'order_number',  title:'产品主图', height:"80px" ,templet: function (d) {
                        return '<img src="/'+ d.goods_pictures +'"alt=""/>' ;
                    }}

                    , {field:'goods_name' , title:'产品名称'}
                    ,{field:'plat', title:'产品分类', templet: function (d) {
                        return d.category_id_1 +'-->'+d.category_id_2 + '-->' +d.category_id_3 ;

                    }}

                    ,{field:'alternative_price', title:'采购价(RMB)' ,templet: function(d){
                        // if(d.procurement.alternative_price){
                        //     return 666 ;
                        // }else{
                        //     return 555 ;
                        // } ;
                        return 555 ;
                    }}
                    ,{field:'goods_weight', title:'产品重量(KG)'}
                    ,{field:'', title:'产品尺寸(CM)', templet: function(d){
                        return d.goods_length +'x'+d.goods_width + 'x'+ d.goods_height ;
                    }}
                    ,{field:'status', title:'产品状态', templet: function(d){
                        if (d.status === 1) { return '草稿'}
                        if (d.status === 2) { return '审核'}
                    }}
                    ,{fixed: 'right', title:'操作', toolbar: '#barDemo', width:200}
                ]]
                ,limit:20
                ,page: true
                ,limits:[20,30,40,50]
                ,done:function () {   //返回数据执行回调函数
                    layer.close(index);    //返回数据关闭loading
                }
            });


            form.on('submit(searBtn)', function(data){
                var index = layer.msg('数据请求中', {icon: 16});
                var info = data.field;
                table.render({
                    elem: '#EDtable'
                    ,url:'/Mapping/ajaxGetAllLocaGoodsByParams'
                    ,where:{data:info}
                    ,cols: [[
                        {checkbox: true}
                        , {field:'id' , title:'序号'}
                        , {field:'sku' , title:'自定义SKU'}
                        ,{field:'order_number',  title:'产品主图', height:"80px" ,templet: function (d) {
                            return '<img src="/'+ d.goods_pictures +'"alt=""/>' ;
                        }}

                        , {field:'goods_name' , title:'产品名称'}
                        ,{field:'plat', title:'产品分类', templet: function (d) {
                            return d.category_id_1 +'-->'+d.category_id_2 + '-->' +d.category_id_3 ;

                        }}

                        ,{field:'alternative_price', title:'采购价(RMB)' ,templet: function(d){
                            // if(d.procurement.alternative_price){
                            //     return 666 ;
                            // }else{
                            //     return 555 ;
                            // } ;
                            return 555 ;
                        }}
                        ,{field:'goods_weight', title:'产品重量(KG)'}
                        ,{field:'', title:'产品尺寸(CM)', templet: function(d){
                            return d.goods_length +'x'+d.goods_width + 'x'+ d.goods_height ;
                        }}
                        ,{field:'status', title:'产品状态', templet: function(d){
                            if (d.status === 1) { return '草稿'}
                            if (d.status === 2) { return '审核'}
                        }}
                        ,{fixed: 'right', title:'操作', toolbar: '#barDemo', width:200}
                    ]]
                    ,limit:20
                    ,page: true
                    ,limits:[20,30,40,50]
                    ,done:function () {   //返回数据执行回调函数
                        layer.close(index);    //返回数据关闭loading
                    }
                });




                return false;

            });
            table.on('tool(EDtable)', function(obj){
                var data = obj.data;
                //认领
                if(obj.event === 'delete'){
                    layer.confirm('确认删除', {title:'提示'}, function(index){
                        //ajax 删除采集商品  地址有误
                        $.ajax({
//                        url: "/Mapping/del"
                            url: ""
                            , type: "get"
                            , dataType: "json"
                            , data: {
                                'id':data.id
                            }

                            , success: function (res) {
                                if(res) {
                                    layer.msg('删除成功!' ,{icon:6});
                                    setTimeout(function(){  //使用  setTimeout（）方法设定定时2000毫秒
                                        window.location.reload();//页面刷新
                                    },1000);
                                }else {
                                    layer.msg('删除失败!' ,{icon:6});
                                }
                            }
                        });
                        layer.close(index);
                    });
                }

                //认领
                if(obj.event === 'claim'){
                    layer.confirm('确认认领', {title:'提示'}, function(index){

                        layer.open({
                            type:2,
                            title: ' 商品认领',
                            fix: false,
                            maxmin: true,
                            resize: true,
                            shadeClose: true,
                            offset: 'r',
                            area: ['80%', '90%'],
                            content:'/Mapping/claimById?id='+data.id,
                            end: function(index){
                                layer.close(index);
                            }
                        });

                        //先拿到id 在跳转页面

//                    window.open('/Mapping/claimById?id='+data.id, "_blank");

                        //ajax 认领采集商品
                        // $.ajax({
                        //     url: "/Mapping/claimById"
                        //     , type: "get"
                        //     , dataType: "json"
                        //     , data: {
                        //         'id':data.id
                        //     }
                        //
                        //     , success: function (res) {
                        //         if(res) {
                        //             layer.msg('认领成功!' ,{icon:6});
                        //             setTimeout(function(){  //使用  setTimeout（）方法设定定时2000毫秒
                        //                 window.location.reload();//页面刷新
                        //             },1000);
                        //         }else {
                        //             layer.msg('认领失败!' ,{icon:6});
                        //         }
                        //     }
                        // });
                        layer.close(index);
                    });
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
            /*table*/




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

            $('#BatchDel').click(function(){
                if(ids==''){
                    layer.msg('请选择商品');
                    return false;
                }else{
                    var elect = $('tbody .layui-form-checked').parents('tr');
                    layer.confirm('确认删除所选项？',{
                        btn : [ '确定', '取消' ],
                        yes: function(index){
                            $.ajax({
                                url: "/Mapping/del"
                                , type: "get"
                                , dataType: "json"
                                , data: {
                                    'id':ids
                                }

                                , success: function (res) {
                                    if(res.status) {
                                        layer.msg('删除成功!' ,{icon:6});
                                        setTimeout(function(){  //使用  setTimeout（）方法设定定时2000毫秒
                                            //window.location.reload();//页面刷新
                                            elect.remove();
                                        },1000);
                                    }else {
                                        layer.msg('删除失败!' ,{icon:6});
                                    }
                                }
                            });



                            layer.close(index);
                        }
                    })
                }
            });

        });

    </script>
@endsection
