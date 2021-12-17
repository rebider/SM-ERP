@extends('layouts/new_main')
@section('content')
    <div class="kbmodel_full">
        <div class="content-wrapper">
            <div class="multiSearch">
                <form class="linksearch " action="">
                <textarea class="textareaSearch layui-form layui-form-pane" id="plat" name="plat_urls"
                          placeholder="请输入采集链接，多个请用回车键换行，一次采集请求只支持同一个平台最多10笔链接"></textarea>
                    <div class="platfBtn">
                        <button class="layui-btn" lay-submit="" lay-filter="collectBtn">开始采集</button>
                        <div class="tips"><i class="layui-icon layui-icon-about"></i>
                            目前支持的平台有：Amazon.co.jp、楽天市場，其他平台后续会陆续开放，敬请期待
                        </div>
                    </div>
                </form>


                <form name="search" class="layui-form layui-form-pane" action="">

                    <div class="layui-form-item" pane="">
                        <label class="layui-form-label">平台</label>
                        <div class="layui-input-block">
                            <input type="radio" name="plat" value="0" title="全部" checked="" autocomplete="off">
                            <input type="radio" name="plat" value="1" title="乐天" autocomplete="off">
                            <input type="radio" name="plat" value="2" title="亚马逊" autocomplete="off">
                        </div>
                    </div>
                    <div class="layui-form-item" pane="">
                        <label class="layui-form-label">认领状态</label>
                        <div class="layui-input-block">
                            <input type="radio" name="status" value="0" title="全部" checked="" autocomplete="off">
                            <input type="radio" name="status" value="1" title="未认领" autocomplete="off">
                            <input type="radio" name="status" value="2" title="已认领" autocomplete="off">
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">采集时间</label>
                            <div class="layui-input-inline" style="width: 150px;">
                                <input type="text" name="start_time" id="date1" placeholder="" autocomplete="off" class="layui-input">
                            </div>
                            <div class="layui-form-mid">-</div>
                            <div class="layui-input-inline" style="width: 150px;">
                                <input type="text" name="end_time" id="date2" placeholder="" autocomplete="off" class="layui-input">
                            </div>
                        </div>
                    </div>

                    <div class="layui-form-item">
                        <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="searBtn">搜索</button>
                        <button class="layui-btn layui-btn-primary" lay-submit="" lay-filter="reset" style="width: 60px;">重置</button>
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
        <a  class="set-gray" href="javascript:void(0);">认领</a>&nbsp;&nbsp;
        <a class="layui-table-link" href="javascript:;" lay-event="delete">删除</a>&nbsp;&nbsp;
        @{{#  } }}
    </script>
    <script>
        layui.config({ base: '../../layui/lay/modules/'}).extend({ formSelects: 'formSelects-v3'});
        layui.use(['layer','form','element','laydate','table','formSelects'], function(){
            var layer = layui.layer,
                form = layui.form,
                laypage = layui.laypage,
                table = layui.table,
                laydate = layui.laydate;
                ids='';
            var index = layer.msg('数据请求中', {icon: 16});

            laydate.render({
                elem: '#date2',
                type: 'datetime',
            });
            laydate.render({
                elem: '#date1',
                type: 'datetime',
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
                    , {field:'id' , title:'序号', templet: function (d) {
                        return d.LAY_TABLE_INDEX+1;
                    }}
                    , {
                        field: 'goods_pictures', title: '产品主图', templet: function (d) {
                            if (d.goods_pictures) {
                                return '<img onerror="this.src=\'\'" src="{{url('showImage').'?path='}}'+d.goods_pictures+'" style="height:24px;width:24px;cursor:zoom-in;" alt=""/>';
                            }
                            return '';
                        },
                        event: 'check_img',
                    }
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
                    ,{field:'sku', title:'自定义SKU', templet: function(d){
                            if (d.goods) {
                                    return d.goods.sku
                            }
                            return ''
                        }
                    }
                    ,{fixed: 'right', title:'操作', toolbar: '#barDemo', width:200}
                ]]
                ,limit:20
                ,page: true
                ,limits:[20,30,40,50]
                ,done:function () {   //返回数据执行回调函数
                    layer.close(index);    //返回数据关闭loading
                }
            });

            form.on('submit(reset)', function(data){
                window.location.reload(true);
                return false;
            });

            //采集数据的
            form.on('submit(collectBtn)', function(data){
                var index = layer.msg('数据请求中', {icon: 16});
                var url = $('#plat').val() ;

                $.ajax({
                    url: "/Goods/collectionGoods"
                    , type: "post"
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
                        layer.alert('操作失败！！！',{icon:5});
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
                        , {field:'id' , title:'序号', templet: function (d) {
                            return d.LAY_TABLE_INDEX+1;
                        }}
                        , {
                            field: 'goods_pictures', title: '产品主图', templet: function (d) {
                                if (d.goods_pictures) {
                                    return '<img onerror="this.src=\'\'" src="{{url('showImage').'?path='}}'+d.goods_pictures+'" style="height:24px;width:24px;cursor:zoom-in;" alt=""/>';
                                }
                                return '';
                            },
                            event: 'check_img',
                        }
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
                        ,{field:'sku', title:'自定义SKU',templet: function(d){
                            if (d.goods) {
                                return d.goods.sku
                            }
                            return ''
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

                if(obj.event === 'check_img'){
                    if (data.goods_pictures) {
                        check_img(data.goods_pictures);
                    }
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
                            maxmin: true,
                            resize: true,
                            shadeClose: true,
                            offset: 'r',
                            area: ['80%', '90%'],
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
//                                        setTimeout(function(){  //使用  setTimeout（）方法设定定时2000毫秒
//                                            //window.location.reload();//页面刷新
//                                            elect.remove();
//                                        },1000);
                                        setTimeout(function(){  //使用  setTimeout（）方法设定定时2000毫秒
                                            window.location.reload();//页面刷新
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
