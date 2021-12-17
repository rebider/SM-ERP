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
                    <div class="layui-form-item">
                        <div class="second">
                            <!--订单号-->
                            <div class="form-search-group">
                                <div class="inputTxt element-display-inline">ASIN：</div>
                                <div class="inputBlock element-display-inline">
                                    <input type="text" name="ASIN" autocomplete="off" class="layui-input">
                                </div>
                            </div>

                            <div class="form-search-group">
                                <div class="inputTxt element-display-inline">SellerSKU：</div>
                                <div class="inputBlock element-display-inline">
                                    <input type="text" name="sku" autocomplete="off" class="layui-input">
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
                <div class="operate btn-3 fr">
                    <button class="layui-btn" id="export">导出</button>
                </div>
                <div class="operate btn-2 fr">
                    <button class="layui-btn" id="BatchDel">批量下架</button>
                </div>
                <div class="operate  btn-1 fr" style="display: none;">
                    <button class="layui-btn" id="BatchPutOn">批量上架</button>
                </div>
            </div>

            <div class="layui-tab" lay-filter="test1">
                <ul class="layui-tab-title">
                    <li class="layui-this" lay-id="111" data-field="on" >在线商品</li>
                    <li lay-id="222" data-field="off">下架商品</li>
                    <li lay-id="333" data-field="update">更新失败</li>
                </ul>

                <div class="layui-tab-content">
                    <div class="vod_table layui-form layui-tab-item layui-show">
                        <table class="" id="EDtable" lay-filter="EDtable"></table>
                    </div>
                    <div class="vod_table layui-form layui-tab-item">
                        <table class="" id="EDtable1" lay-filter="EDtable1"></table>
                    </div>
                    <div class="vod_table layui-form layui-tab-item">
                        <table class="" id="EDtable2" lay-filter="EDtable2"></table>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
@section('javascripts')
    <script type="text/html" id="barDemo">
        @{{#  if(d.put_on_status === 1){ }}
        {{--<a  class="layui-table-link" href="javascript:;" lay-event="showDetail">查看</a>&nbsp;&nbsp;--}}
        {{--<a  class="layui-table-link" href="javascript:;" lay-event="edit">编辑</a>&nbsp;&nbsp;--}}
        {{--<a  class="set-gray" href="javascript:;" lay-event="delete">下架</a>&nbsp;&nbsp;--}}
        <a  class="layui-table-link" href="javascript:;" lay-event="showDetail">查看</a>&nbsp;&nbsp;
        <a  class="layui-table-link" href="javascript:;" lay-event="edit">编辑</a>&nbsp;&nbsp;
        <a  class="layui-table-link" href="javascript:;" lay-event="delete">下架</a>&nbsp;&nbsp;
        @{{#  } }}

        @{{#  if(d.put_off_status === 1){ }}
        {{--<a  class="layui-table-link" href="javascript:;" lay-event="showDetail">查看</a>&nbsp;&nbsp;--}}
        {{--<a  class="layui-table-link" href="javascript:;" lay-event="edit">编辑</a>&nbsp;&nbsp;--}}
        {{--<a  class="set-gray" href="javascript:;" lay-event="delete">上架</a>&nbsp;&nbsp;--}}

        <a  class="layui-table-link" href="javascript:;" lay-event="showDetail">查看</a>&nbsp;&nbsp;
        <a  class="layui-table-link" href="javascript:;" lay-event="edit">编辑</a>&nbsp;&nbsp;
        <a  class="layui-table-link" href="javascript:;" lay-event="delete">上架</a>&nbsp;&nbsp;
        @{{#  } }}

        @{{#  if(d.put_on_status === 2 || d.put_off_status === 2){ }}
        {{--<a  class="set-gray" href="javascript:;" lay-event="showDetail">查看</a>&nbsp;&nbsp;--}}
        {{--<a  class="layui-table-link" href="javascript:;" lay-event="edit">编辑</a>&nbsp;&nbsp;--}}
        <a  class="layui-table-link" href="javascript:;" lay-event="showDetail">查看</a>&nbsp;&nbsp;
        <a  class="layui-table-link" href="javascript:;" lay-event="edit">编辑</a>&nbsp;&nbsp;
        @{{#  } }}
    </script>
    <script>
        let checkedValue = [];
        let checkedData = {};

        layui.use(['form', 'laydate','table','element'], function(){
            var layer = layui.layer,
                form = layui.form,
                laypage = layui.laypage,
                element = layui.element,
                table = layui.table,
                laydate = layui.laydate;
                ids='';
            var index = layer.msg('数据请求中', {icon: 16});
            // -------start------- zt8067 2019/07/02修复tab清空ids BUG
            var indexCheck = 0;
            //获取hash来切换选项卡，假设当前地址的hash为lay-id对应的值
            var layid = location.hash.replace(/^#test1=/, '');
            if(layid==111){
                $(".btn-1").hide();
                $(".btn-2").show();
                indexCheck = 0;
            }else if(layid == 222){
                $(".btn-1").show();
                $(".btn-2").hide();
                indexCheck = 1;
            }else if(layid == 333){
                $(".btn-1,.btn-2").hide();
                indexCheck = 2;
            }
            element.tabChange('test1', layid); //假设当前地址为：http://a.com#test1=222，那么选项卡会自动切换到“发送消息”这一项
            //监听Tab切换，以改变地址hash值
            element.on('tab(test1)', function (data) {
                let form = layui.form
                location.hash = 'test1=' + this.getAttribute('lay-id');
                switch (data.index) {
                    case 0:
                        $(".btn-1").hide();
                        $(".btn-2").show();
                        $("div[lay-filter='LAY-table-2'],div[lay-filter='LAY-table-3']").find("input[type='checkbox']").prop("checked", false);
                        break;
                    case 1:
                        $(".btn-1").show();
                        $(".btn-2").hide();
                        $("div[lay-filter='LAY-table-1'],div[lay-filter='LAY-table-3']").find("input[type='checkbox']").prop("checked", false);
                        break;
                    case 2:
                        $(".btn-1,.btn-2").hide();
                        $("div[lay-filter='LAY-table-1'],div[lay-filter='LAY-table-2']").find("input[type='checkbox']").prop("checked", false);
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
            // -------end------- zt8067 2019/07/02修复tab清空ids BUG
            lay('.time-item').each(function(){
                laydate.render({
                    elem: this
                    ,trigger: 'click'
                });
            });



            //---------------------------表格部分开始↓--------------------------
            //封装render
            let table1 = function (info, element, index) {
                let colsParamsWithoutReason  = [[
                    {checkbox: true}
                    , {field: '', title: '序号', width: 50, type: 'numbers'}
                    , {field:'local_sku' , title:'自定义sku', event: 'showDetail', width: 200, style: 'cursor:pointer;color:#01AAED'}
                    , {
                        field: 'img_url', title: '商品主图', height: "80px", templet: function (d) {
                            if (d.img_url) {
                                return '<img onerror="this.src=\'/img/imgNotFound.jpg\'" src="{{url('showImage').'?path='}}'+d.img_url+'" style="height:24px;width:24px;cursor:zoom-in;" alt=""/>' +
                                    '<input class="sku-label" type="hidden" value="'+ d.id +'" data-name="'+ d.goods_name +'" >';
                            }
                            return '<input class="sku-label" type="hidden" value="'+ d.id +'" data-name="'+ d.goods_name +'" >';
                        },
                        event: 'check_img',
                    }
                    , {field:'shops' , title:'店铺', templet: function (d) {
                        return d.shops.shop_name
                    }}
                    , {field:'title' , title:'商品名称'}
                    , {field:'seller_sku' , title:'Seller SKU'}
                    , {field:'ASIN' , title:'ASIN'}
                    ,{field:'sale_price', title:'销售价格'}
                    ,{field:'currency_code', title:'币种'}
                    ,{field:'status', title:'商品状态', templet: function(d){
                        if (d.put_on_status === 1) {
                            return '已上架'
                        } else if (d.put_off_status === 1) {
                            return '已下架'
                        } else { return '更新失败'}
                    }}
                    ,{fixed: 'right', title:'操作', toolbar: '#barDemo', width:200}
                ]];
                let colsParamsWithReason = [[
//                    {checkbox: true}
                     {field: '', title: '序号', width: 50, type: 'numbers'}
                    , {field:'local_sku' , title:'自定义sku', width: 200, event: 'showDetail'}
                    , {
                        field: 'img_url', title: '商品主图', height: "80px", templet: function (d) {
                            if (d.img_url) {
                                return '<img onerror="this.src=\'/img/imgNotFound.jpg\'" src="{{url('showImage').'?path='}}'+d.img_url+'" style="height:24px;width:24px;cursor:zoom-in;" alt=""/>' +
                                    '<input class="sku-label" type="hidden" value="'+ d.id +'" data-name="'+ d.goods_name +'" >';
                            }
                            return '<input class="sku-label" type="hidden" value="'+ d.id +'" data-name="'+ d.goods_name +'" >';
                        },
                        event: 'check_img',
                    }
                    , {field:'shops' , title:'店铺', templet: function (d) {
                        return d.shops.shop_name
                    }}
                    , {field:'title' , title:'商品名称'}
                    , {field:'seller_sku' , title:'Seller SKU'}
                    , {field:'ASIN' , title:'ASIN'}
                    ,{field:'sale_price', title:'销售价格'}
                    ,{field:'currency_code', title:'币种'}
                    ,{field:'status', title:'商品状态', templet: function(d){
                        if (d.put_on_status === 1) {
                            return '已上架'
                        } else if (d.put_off_status === 1) {
                            return '已下架'
                        } else { return '更新失败'}
                    }}
                    ,{field:'synchronize_info', title:'原因', templet: function(d){
                        if (d.synchronize_info) {
                            return d.synchronize_info
                        }
                        return ''
                    }}
                    ,{fixed: 'right', title:'操作', toolbar: '#barDemo', width:200}
                ]];
                table.render({
                    elem: element
                    ,url:'/Goods/onlineAmazon/ajaxGetAllByParams'
                    ,parseData: function (res) {
                        return {
                            "code": res.code,
                            "data": res.data.data,
                            "message": res.msg,
                            "count": res.total
                        }
                    }
                    ,where:{data:info}
                    ,cols: element != '#EDtable2' ? colsParamsWithoutReason : colsParamsWithReason
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
                table1({synchronizeType:1}, '#EDtable', index);
                table1({synchronizeType:2}, '#EDtable1', index);
                table1({synchronizeType:3}, '#EDtable2', index);

            })

            form.on('submit(reset)', function(data){
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
                    info['synchronizeType'] = 2;
                } else if (layid == 333) {
                    element = '#EDtable2';
                    info['synchronizeType'] = 3;
                } else {
                    element = '#EDtable';
                    info['synchronizeType'] = 1;
                }
                table1(info, element, index);
                return false;

            });
            //--------------------------表格初始化结束↑------------------------

            table.on('tool(EDtable)', function(obj){
                var data = obj.data;
                //下架
                if(obj.event === 'delete'){
                    layer.confirm('确认下架', {title:'提示'}, function(index){
                        //ajax 删除采集商品
                        $.ajax({
                            url: "/Goods/onlineAmazon/PutOffSaleById"
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
                                    layer.msg('下架失败!' ,{icon:5});
                                }
                            }
                        });
                        layer.close(index);
                    });
                }

                //亚马逊在线商品编辑
                if(obj.event === 'edit'){
                    layer.open({
                        type: 2,
                        title: '编辑亚马逊在线商品',
                        fix: false,
                        maxmin: true,
                        shadeClose: true,
                        offset:'r',
                        area: ['80%', '90%'],
                        content: '{{ url('Goods/onlineAmazon/edit') }}' + '?id=' + data.id,
                        end: function (index) {
                            layer.close(index);
                        }
                    });
                    layer.close(index);
                }

                //查看
                if(obj.event == 'showDetail') {
                    layer.open({
                        type: 2,
                        title: '查看亚马逊在线商品',
                        fix: false,
                        maxmin: true,
                        shadeClose: true,
                        offset:'r',
                        area: ['80%', '90%'],
                        content: '{{ url('Goods/onlineAmazon/detail') }}' + '?id=' + obj.data.id,
                        end: function (index) {
                            layer.close(index);
                        }
                    });
                }

                if(obj.event === 'check_img') {
                    if (data.img_url) {
                        check_img(data.img_url);
                    }
                }
            });
            table.on('tool(EDtable1)', function(obj){
                var data = obj.data;
                //上架
                if(obj.event === 'delete'){
                    layer.confirm('确认上架', {title:'提示'}, function(index){
                        //ajax 删除采集商品
                        $.ajax({
                            url: "/Goods/onlineAmazon/PutOnSaleById"
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
                        layer.close(index);
                    });
                }

                //亚马逊在线商品编辑
                if(obj.event === 'edit'){
                    layer.open({
                        type: 2,
                        title: '编辑亚马逊在线商品',
                        fix: false,
                        maxmin: true,
                        shadeClose: true,
                        offset:'r',
                        area: ['80%', '90%'],
                        content: '{{ url('Goods/onlineAmazon/edit') }}' + '?id=' + data.id,
                        end: function (index) {
                            layer.close(index);
                        }
                    });
                    layer.close(index);
                }

                //查看
                if(obj.event == 'showDetail') {
                    layer.open({
                        type: 2,
                        title: '查看亚马逊在线商品',
                        fix: false,
                        maxmin: true,
                        shadeClose: true,
                        offset:'r',
                        area: ['80%', '90%'],
                        content: '{{ url('Goods/onlineAmazon/detail') }}' + '?id=' + obj.data.id,
                        end: function (index) {
                            layer.close(index);
                        }
                    });
                }

                if(obj.event === 'check_img') {
                    if (data.img_url) {
                        check_img(data.img_url);
                    }
                }
            });
            table.on('tool(EDtable2)', function(obj){
                var data = obj.data;
                //认领
                if(obj.event === 'delete'){
                    layer.confirm('确认删除', {title:'提示'}, function(index){
                        //ajax 删除采集商品
                        $.ajax({
                            url: "/Mapping/del"
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

                //亚马逊在线商品编辑
                if(obj.event === 'edit'){
                    layer.open({
                        type: 2,
                        title: '编辑亚马逊在线商品',
                        fix: false,
                        maxmin: true,
                        shadeClose: true,
                        offset:'r',
                        area: ['80%', '90%'],
                        content: '{{ url('Goods/onlineAmazon/edit') }}' + '?id=' + data.id,
                        end: function (index) {
                            layer.close(index);
                        }
                    });
                    layer.close(index);
                }

                //查看
                if(obj.event == '查看亚马逊在线商品') {
                    layer.open({
                        type: 2,
                        title: '',
                        fix: false,
                        maxmin: true,
                        shadeClose: true,
                        offset:'r',
                        area: ['80%', '90%'],
                        content: '{{ url('Goods/onlineAmazon/detail') }}' + '?id=' + obj.data.id,
                        end: function (index) {
                            layer.close(index);
                        }
                    });
                }

                if(obj.event === 'check_img') {
                    if (data.img_url) {
                        check_img(data.img_url);
                    }
                }
            });
            // zt8067 干掉 zt12779 bug代码
            // function checkedData(e, targetTable) {
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
            //             let skuEle =$("#"+targetTable).parent().find('.sku-label');
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
            //
            //     ids = checkedValue.join(',');
            // }
            //
            // table.on('checkbox(EDtable)', function (e) {
            //     checkedData(e, 'EDtable');
            // });
            // table.on('checkbox(EDtable1)', function (e) {
            //     checkedData(e, 'EDtable1');
            // });
            // table.on('checkbox(EDtable2)', function (e) {
            //     checkedData(e, 'EDtable2');
            // });
            /*table*/
           let _checkbox = function (name) {
               table.on('checkbox(' + name + ')', function (obj) {
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
           };
           _checkbox('EDtable');
           _checkbox('EDtable1');
           _checkbox('EDtable2');

            //批量上架
            $('#BatchPutOn').click(function(){
                if(ids==''){
                    layer.msg('请选择商品');
                    return false;
                }else{
                    var elect = $('tbody .layui-form-checked').parents('tr');
                    $.ajax({
                        url: "/Goods/onlineAmazon/PutOnSaleById"
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

            //批量下架
            $("#BatchDel").click(function () {
                if(ids==''){
                    layer.msg('请选择商品');
                    return false;
                }else{
                    var elect = $('tbody .layui-form-checked').parents('tr');
                    $.ajax({
                        url: "/Goods/onlineAmazon/PutOffSaleById"
                        , type: "get"
                        , dataType: "json"
                        , data: {
                            'id':ids
                        }
                        , success: function (res) {
                            if(res.status) {
                                layer.msg(res.msg ,{icon:1});
                                setTimeout(function(){  //使用  setTimeout（）方法设定定时2000毫秒
                                    window.location.reload();//页面刷新
//                                    elect.remove();
                                },1000);
                            }else {
                                layer.msg(res.msg ,{icon:5});
                            }
                        }
                    });
                }
            });

            $("#export").click(function () {
                if (!ids || ids.length == 0) {
                    layer.msg('请选择至少一条数据进行导出', {icon: 2});
                    return false;
                }
                window.location.href = '/Goods/onlineAmazon/export?ids=' + ids
            })

        });
        // zt8067 干掉 zt12779 bug代码
        // layui.use('element', function(){
        //     var element = layui.element;
        //     var form = layui.form;
        //
        //     //获取hash来切换选项卡，假设当前地址的hash为lay-id对应的值
        //     var layid = location.hash.replace(/^#test1=/, '');
        //     element.tabChange('test1', layid); //假设当前地址为：http://a.com#test1=222，那么选项卡会自动切换到“发送消息”这一项
        //
        //     //监听Tab切换，以改变地址hash值
        //     element.on('tab(test1)', function(data){
        //         location.hash = 'test1='+ this.getAttribute('lay-id');
        //
        //         // $("input[type=checkbox]").prop('checked', false);
        //         // layui.use('form', function() {
        //         let form = layui.form;
        //         //     form.render('checkbox');
        //         // });
        //         // checkedValue = [];
        //         // checkedData = {};
        //
        //         switch (data.index) {
        //             case 0:
        //                 $(".btn-1").hide();
        //                 $(".btn-2").show();
        //                 $("div[lay-filter='LAY-table-2']").find("input[type='checkbox']").prop("checked", false);
        //                 $("div[lay-filter='LAY-table-3']").find("input[type='checkbox']").prop("checked", false);
        //                 form.render('checkbox');
        //                 ids = '' ;
        //                 checkedValue = [];
        //                 checkedData = {};
        //                 break;
        //             case 1:
        //                 $(".btn-1").css('display','block');
        //                 $(".btn-2").hide();
        //                 $("div[lay-filter='LAY-table-1']").find("input[type='checkbox']").prop("checked", false);
        //                 $("div[lay-filter='LAY-table-3']").find("input[type='checkbox']").prop("checked", false);
        //                 form.render('checkbox');
        //                 ids = '' ;
        //                 checkedValue = [];
        //                 checkedData = {};
        //                 break;
        //             default:
        //                 $(".btn-1,.btn-2").hide();
        //                 $("div[lay-filter='LAY-table-1']").find("input[type='checkbox']").prop("checked", false);
        //                 $("div[lay-filter='LAY-table-2']").find("input[type='checkbox']").prop("checked", false);
        //                 form.render('checkbox');
        //                 ids = '' ;
        //                 checkedValue = [];
        //                 checkedData = {};
        //                 break;
        //         }
        //
        //     });
        // });

    </script>
@endsection