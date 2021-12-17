@extends('layouts/new_main')
@section('content')
    <div class="kbmodel_full">
        <div class="content-wrapper">
            <div class="multiSearch">
                <form class="linksearch " action="">
                <textarea class="textareaSearch layui-form layui-form-pane" id="plat" name="plat_urls"
                          placeholder="请输入采集链接，多个请用回车键换行，一次采集请求只支持同一个平台最多10笔链接"></textarea>
                    <div class="platfBtn">
                        <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="collectBtn">开始采集</button>
                        <div class="tips"><i class="layui-icon layui-icon-about"></i>
                            目前支持的平台有：RakutenJP、AmazonJP，其他平台后续会陆续开放，敬请期待
                        </div>
                    </div>
                </form>


                <form name="search" class="layui-form layui-form-pane" action="">

                    <div class="layui-form-item" pane="">
                        <label class="layui-form-label">平台</label>
                        <div class="layui-input-block">
                            <input type="radio" name="plat" value="0" title="全部" checked="">
                            <input type="radio" name="plat" value="1" title="乐天">
                            <input type="radio" name="plat" value="2" title="亚马逊">
                        </div>
                    </div>
                    <div class="layui-form-item" pane="">
                        <label class="layui-form-label">认领状态</label>
                        <div class="layui-input-block">
                            <input type="radio" name="status" value="0" title="全部" checked="">
                            <input type="radio" name="status" value="1" title="未认领">
                            <input type="radio" name="status" value="2" title="已认领">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">采集日期</label>
                            <div class="layui-input-inline" style="width: 100px;">
                                <input type="text" name="start_time" id="date1" placeholder="" autocomplete="off" class="layui-input">
                            </div>
                            <div class="layui-form-mid">-</div>
                            <div class="layui-input-inline" style="width: 100px;">
                                <input type="text" name="end_time" id="date2" placeholder="" autocomplete="off" class="layui-input">
                            </div>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <button class="layui-btn" lay-submit="" lay-filter="searBtn">查询</button>
                    </div>
                </form>



            </div>
            <div class="toolsBtn fclear">
                <div class="operate fr"><button class="layui-btn" id="BatchDel">批量删除</button></div>
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
        <a class="layui-table-link" href="javascript:;" lay-event="claim">认领</a>&nbsp;&nbsp;
        <a class="layui-table-link" href="javascript:;" lay-event="delete">删除</a>&nbsp;&nbsp;
        @{{#  } }}

        @{{#  if(d.status === 2){ }}
        <a href class="set-gray" href="javascript:;">认领</a>&nbsp;&nbsp;
        <a class="layui-table-link" href="javascript:;" lay-event="delete">删除</a>&nbsp;&nbsp;
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
                ,url:'/Goods/ajaxGetAllGoodsByParams'
                ,cols: [[
                    {checkbox: true}
                    , {field:'id' , title:'序号'}
                    ,{field:'order_number',  title:'产品主图', height:"80px" ,templet: function (d) {
                        return '<img src="/'+ d.goods_pictures +'"alt=""/>' ;
                    }}
                    ,{field:'plat', title:'平台', templet: function (d) {
                        if (d.plat == 1){ return '乐天' }
                        if (d.plat == 2){ return '亚马逊' }

                    }}
                    ,{field:'title', title:'标题'}
                    ,{field:'url', title:'数据来源URL'}
                    ,{field:'created_at', title:'采集时间'}
                    ,{field:'status', title:'认领状态', templet: function(d){
                        if(d.status == 1) return '未认领' ;
                        if(d.status == 2) return '已认领' ;
                        if(d.status == 3) return '认领失败' ;
                    }}
                    ,{field:'sku', title:'自定义SKU'}
                    ,{fixed: 'right', title:'操作', toolbar: '#barDemo', width:200}
                ]]
                ,limit:20
                ,page: true
                ,limits:[20,30,40,50]
                ,done:function () {   //返回数据执行回调函数
                    layer.close(index);    //返回数据关闭loading
                }
            });

            //采集数据的
            form.on('submit(collectBtn)', function(data){
                var index = layer.msg('数据请求中', {icon: 16});
                var url = $('#plat').val() ;

                $.ajax({
                    url: "/Goods/collectionGoods"
                    , type: "get"
                    , dataType: "json"
                    , data: {
                        'urls':url ,
                        '_token':"{{csrf_token()}}"
                    }

                    , success: function (res) {
                        if(res.status) {
                            layer.msg(res.msg ,{icon:6});
                            setTimeout(function(){  //使用  setTimeout（）方法设定定时2000毫秒
                                window.location.reload();//页面刷新
                            },1000);
                        }else {
                            layer.msg(res.msg ,{icon:5});
                        }
                    }
                    ,error:function (e,x,t) {
                        loaction.reload() ;
                    }
                });

                return false;
            });

            form.on('submit(searBtn)', function(data){
                var index = layer.msg('数据请求中', {icon: 16});
                var info = data.field;
                table.render({
                    elem: '#EDtable'
                    ,url:'/Goods/ajaxGetAllGoodsByParams'
                    ,where:{data:info}
                    ,cols: [[
                        {checkbox: true}
                        , {field:'id' , title:'序号'}
                        ,{field:'order_number',  title:'产品主图', height:"80px" ,templet: function (d) {
                            return '<img src="/'+ d.goods_pictures +'"alt=""/>' ;
                        }}
                        ,{field:'plat', title:'平台', templet: function (d) {
                            if (d.plat == 1){ return '乐天' }
                            if (d.plat == 2){ return '亚马逊' }

                        }}
                        ,{field:'title', title:'标题'}
                        ,{field:'url', title:'数据来源URL'}
                        ,{field:'created_at', title:'采集时间'}
                        ,{field:'status', title:'认领状态', templet: function(d){
                            if(d.status == 1) return '未认领' ;
                            if(d.status == 2) return '已认领' ;
                            if(d.status == 3) return '认领失败' ;
                        }}
                        ,{field:'sku', title:'自定义SKU'}
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
                        //ajax 删除采集商品
                        $.ajax({
                            url: "/Goods/del"
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
                    layer.confirm('确认认领', {title: '提示'}, function (index) {
                        // layer.open({
                        //     type: 1,
                        //     title: '商品认领',
                        //     area: ['1080px', '800px'],
                        //     content: $('#productext2'),
                        //     btn: ['保存', '认领', '返回'],
                        //     yes: function () {
                        //     }
                        // });

                        layer.open({
                            type:2,
                            title: ' 商品认领',
                            fix: false,
                            maxmin: false,
                            shadeClose: true,
                            area: ['1600px', '1100px'],
                            content:'/Goods/claimById?id='+data.id,
                            end: function(index){
                                layer.close(index);
                            }
                        });
//                    window.open('/Goods/claimById?id='+data.id, "_blank");
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
                                url: "/Goods/del"
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
