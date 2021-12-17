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

    #Rakuten-wrapper {
        display: none;
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
                        <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="searBtn" style="width: 60px;">
                            搜索
                        </button>
                        <button class="layui-btn layui-btn-primary" lay-submit="" lay-filter="reset" style="width: 60px;">重置</button>
                    </div>
                </form>


            </div>
            <div class="toolsBtn fclear">

                <div class="operate fr">
                    <button class="layui-btn click-btn" type="button" id="BatchAdd" data-type="BatchDel">上架商品</button>
                </div>
                <div class="operate fr">
                    <button class="layui-btn click-btn" type="button" data-type="BatchPutOn">批量上架</button>
                </div>
            </div>

            <div class="layui-tab" lay-filter="test1">
                <ul class="layui-tab-title">
                    <li class="layui-this" lay-id="111">草稿</li>
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
    <!--乐天上架-->
    <div id="Rakuten-wrapper">
        <div class="wrapper" style="padding: 20px">

        </div>
    </div>
@endsection
@section('javascripts')
    <script type="text/html" id="barDemo">
        @{{#  if(d.synchronize_status === 1){ }}
        {{--<a class="set-gray layui-btn layui-btn-primary layui-btn-xs" href="javascript:;"--}}
           {{--lay-event="putOnSale">上架</a>&nbsp;&nbsp;--}}
        {{--<a class="layui-btn layui-btn-normal layui-btn-xs" href="javascript:;" lay-event="edit"><i--}}
                    {{--class="layui-icon layui-icon-edit"></i>编辑</a>&nbsp;&nbsp;--}}
        {{--<a class="set-gray layui-btn layui-btn-danger layui-btn-xs" href="javascript:;" style="margin-right: -3%" lay-event="del"><i--}}
                    {{--class="layui-icon layui-icon-delete"></i>删除</a>&nbsp;&nbsp;--}}

        <a class="layui-table-link" href="javascript:;"
           lay-event="putOnSale">上架</a>&nbsp;&nbsp;
        <a class="layui-table-link" href="javascript:;" lay-event="edit">
            {{--<i class="layui-icon layui-icon-edit"></i>--}}
            编辑</a>&nbsp;&nbsp;
        <a class="layui-table-link" href="javascript:;" style="margin-right: -3%" lay-event="del">
            {{--<i class="layui-icon layui-icon-delete"></i>--}}
            删除</a>&nbsp;&nbsp;

        @{{#  } }}

        @{{#  if(d.synchronize_status === 2){ }}
        {{--<a class="set-gray layui-btn layui-btn-primary layui-btn-xs" href="javascript:;"--}}
           {{--lay-event="putOnSale">上架</a>&nbsp;&nbsp;--}}
        {{--<a class="layui-btn layui-btn-normal layui-btn-xs" href="javascript:;" lay-event="edit"><i--}}
                    {{--class="layui-icon layui-icon-edit"></i>编辑</a>&nbsp;&nbsp;--}}
        {{--<a class="set-gray layui-btn layui-btn-danger layui-btn-xs" href="javascript:;" style="margin-right: -3%" lay-event="del"><i--}}
                    {{--class="layui-icon layui-icon-delete"></i>删除</a>&nbsp;&nbsp;--}}

        <a class="layui-table-link" href="javascript:;"
           lay-event="putOnSale">上架</a>&nbsp;&nbsp;
        <a class="layui-table-link" href="javascript:;" lay-event="edit">
            {{--<i class="layui-icon layui-icon-edit"></i>--}}
            编辑</a>&nbsp;&nbsp;
        <a class="layui-table-link" href="javascript:;" style="margin-right: -3%" lay-event="del">
            {{--<i class="layui-icon layui-icon-delete"></i>--}}
            {{--删除</a>&nbsp;&nbsp;--}}
        @{{#  } }}

        @{{#  if(d.synchronize_status === 3){ }}
        {{--<a class="set-gray layui-btn layui-btn-primary layui-btn-xs" href="javascript:;"--}}
           {{--lay-event="putOnSale">上架</a>&nbsp;&nbsp;--}}
        {{--<a class="layui-btn layui-btn-normal layui-btn-xs" href="javascript:;" lay-event="edit"><i--}}
                    {{--class="layui-icon layui-icon-edit"></i>编辑</a>&nbsp;&nbsp;--}}
        {{--<a class="set-gray layui-btn layui-btn-danger layui-btn-xs" href="javascript:;" style="margin-right: -3%" lay-event="del"><i--}}
                    {{--class="layui-icon layui-icon-delete"></i>删除</a>&nbsp;&nbsp;--}}

            <a class="layui-table-link" href="javascript:;"
               lay-event="putOnSale">上架</a>&nbsp;&nbsp;
            <a class="layui-table-link" href="javascript:;" lay-event="edit">
                {{--<i class="layui-icon layui-icon-edit"></i>--}}
                编辑</a>&nbsp;&nbsp;
            <a class="layui-table-link" href="javascript:;" style="margin-right: -3%" lay-event="del">
{{--                <i class="layui-icon layui-icon-delete"></i>--}}
                删除</a>&nbsp;&nbsp;

        @{{#  } }}
    </script>
    <script>
        layui.use(['form', 'laydate', 'table'], function () {
            var layer = layui.layer,
                form = layui.form,
                laypage = layui.laypage,
                table = layui.table,
                element = layui.element,
                laydate = layui.laydate,
                ids;
            var index = layer.msg('数据请求中', {icon: 16});

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


            form.on('submit(reset)', function(data){
                window.location.reload(true);
                return false;
            });
            var active = {
                BatchPutOn: function () {
                    if (ids === undefined || ids == '') {
                        layer.msg('请选择需要上架的记录！', {icon: 5});
                        return false;
                    }
                    layer.open({
                        type: 1
                        , title: '批量上架'
                        , content: $("#Rakuten-wrapper")
                        , area: ['380px', '380px']
                        , btnAlign: 'c'
                        , anim: 0
                        , btn: ['确定', '取消']
                        , success: function () {
                            var index = layer.load(1, {
                                shade: [0.1, '#fff'] //0.1透明度的白色背景
                            });
                            $.ajax({
                                type: 'put',
                                url: '/Goods/lotte/putOnSale',
                                data: {ids: ids, type: 'local', _token: "{{ csrf_token() }}"},
                                dataType: 'json',
                                success: function (res) {
                                    layer.close(layer.index);
                                    var str = '';
                                    if (Array.isArray(res.errAll)) {
                                        if (res.errAll.length != 0) {
                                            (res.errAll.reverse()).forEach(function (item) {
                                                str += "<div>" + item + "</div>";
                                            })
                                        } else {
                                            if (res.msg !== '') {
                                                str = res.msg;
                                            } else {
                                                str = "<div> 批量操作成功，请等待系统同步。</div>";
                                            }
                                        }
                                    }
                                    $("#Rakuten-wrapper .wrapper").append(str);
                                }
                            });
                        },end: function () {
                            ids = '';
                            $("#Rakuten-wrapper .wrapper").empty();
                            table.reload('EDtable');
                        }
                    });
                }
            };

            $(".click-btn").click(function () {
                let type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });

            lay('.time-item').each(function () {
                laydate.render({
                    elem: this
                    , trigger: 'click'
                });
            });
            //---------------------------表格部分开始↓--------------------------
            //封装render
            let table1 = function (info, element, index) {

                let colsParamsWithoutReason = [[
                    {checkbox: true}
                    , {field: '', title: '序号', width: 50, type: 'numbers'}
                    , {field: 'local_sku', title: '自定义SKU'}
                    , {
                        field: 'img_url', title: '商品主图', height: "80px", templet: function (d) {
                            if (d.img_url) {
                                return '<img onerror="this.src=\'/img/imgNotFound.jpg\'" src="{{url('showImage').'?path='}}'+d.img_url+'" style="height:24px;width:24px;cursor:zoom-in;" alt=""/>';
                            }
                            return '';
                        },
                        event: 'check_img',
                    }
                    , {field: 'title', title: '商品名称'}

                    , {field: 'shops', title: '店铺',templet: function(d) {
                            return d.shops ? d.shops.shop_name : ''
                        }}

                    , {
                        field: 'preferred_price', title: '采购价(RMB)', templet: function (d) {
                            if (d.procurement == null) {
                                return '';
                            } else {
                                return d.procurement.preferred_price
                            }
                        }}
                    ,{field:'sale_price', title:'销售价格'}
                    ,{field:'currency_code', title:'币种'}
                    ,{field:'goods_weight', title:'商品重量(KG)'}
                    ,{field:'goods_length', title:'商品尺寸(CM)', templet: function(d){
                        return d.goods_length +'x'+d.goods_width + 'x'+ d.goods_height ;
                    }}
                    ,{field:'synchronize_status', title:'商品状态',templet: function(d){
                        if (d.synchronize_status === 1) { return '草稿'}
                        if (d.synchronize_status === 2) { return '已同步'}
                        if (d.synchronize_status === 3) { return '上架失败'}
                        if (d.synchronize_status === 4) { return '已下架'}
                        if (d.synchronize_status === 5) { return '更新失败'}
                        if (d.synchronize_status === 0) { return  ''}
                    }}
                    ,{fixed: 'right', title:'操作', toolbar: '#barDemo', width:200}
                ]];

                let colsParamsWithReason = [[
                    {checkbox: true}
                    , {field: '', title: '序号', width: 50, type: 'numbers'}
                    , {field: 'local_sku', title: '自定义SKU'}
                    , {
                        field: 'order_number', title: '商品主图', height: "80px", templet: function (d) {
                            if (d.img_url) {
                                return '<img onerror="this.src=\'/img/imgNotFound.jpg\'" src="{{url('showImage').'?path='}}'+d.img_url+'" style="height:24px;width:24px;cursor:zoom-in;" alt=""/>';
                            }
                            return '';
                        },
                        event: 'check_img',
                    }

                    , {field: 'title', title: '商品名称'}

                    , {field: 'shops', title: '店铺', templet: function (d) {
                            return d.shops ? d.shops.shop_name : ''
                        }}

                    , {
                        field: 'preferred_price', title: '采购价(RMB)', templet: function (d) {
                            if (d.procurement == null) {
                                return '';
                            } else {
                                return d.procurement.preferred_price
                            }
                        }
                    }
                    , {field: 'sale_price', title: '销售价格'}
                    , {field: 'currency_code', title: '币种'}
                    , {field: 'goods_weight', title: '商品重量(KG)'}
                    , {
                        field: '', title: '商品尺寸(CM)', templet: function (d) {
                            return d.goods_length + 'x' + d.goods_width + 'x' + d.goods_height;
                        }
                    }
                    , {
                        field: 'synchronize_status', title: '商品状态', templet: function (d) {
                            if (d.synchronize_status === 1) {
                                return '草稿'
                            }
                            if (d.synchronize_status === 2) {
                                return '已同步'
                            }
                            if (d.synchronize_status === 3) {
                                return '上架失败'
                            }
                            if (d.synchronize_status === 4) {
                                return '已下架'
                            }
                            if (d.synchronize_status === 5) {
                                return '更新失败'
                            }
                        }
                    }
                    , {field: 'synchronize_info', title: '原因'}
                    , {fixed: 'right', title: '操作', toolbar: '#barDemo', width: 200}
                ]];
                table.render({
                    elem: element
                    , url: '/Goods/lotte/ajaxGetAllByParams'
                    , parseData: function (res) {
                        return {
                            "code": res.code,
                            "data": res.data.data,
                            "message": res.msg,
                            "count": res.total
                        }
                    }
                    , where: {data: info}
                    , cols: element == '#EDtable' ? colsParamsWithoutReason : colsParamsWithReason
                    , limit: 20
                    , page: true
                    , limits: [20, 30, 40, 50]
                    , done: function () {   //返回数据执行回调函数
                        layer.close(index);    //返回数据关闭loading
                    }
                })
            };

            //document加载完成后响应表格渲染
            $(document).ready(function () {
                var index = layer.msg('数据请求中', {icon: 16});
                table1({}, '#EDtable', index);
                table1({synchronizeType: 3}, '#EDtable1', index);
            })

            //点击搜索框根据当前Tab类型执行相应的表格进行渲染
            form.on('submit(searBtn)', function (data) {
                var index = layer.msg('数据请求中', {icon: 16});
                var info = data.field;
                var layid = location.hash.replace(/^#test1=/, '');
                var element = '';
                if (layid == 222) {
                    element = '#EDtable1';
                    info['synchronizeType'] = 3;
                } else {
                    element = '#EDtable'
                }
                table1(info, element, index);
                return false;

            });
            //--------------------------表格初始化结束↑------------------------

            table.on('tool(EDtable)', function (obj) {
                var data = obj.data;
                //认领
                if(obj.event === 'del'){
                    layer.confirm('确认删除', {title:'提示'}, function(index){

                        //ajax 删除采集商品
                        $.ajax({
                            url: "/Goods/lotte/delete"
                            , type: "get"
                            , dataType: "json"
                            , data: {
                                'id': data.id
                            }

                            , success: function (res) {
                                if (res) {
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
                } else if (obj.event === 'putOnSale') {
                    layer.open({
                        type: 1
                        , title: '乐天上架'
                        , content: $("#Rakuten-wrapper")
                        , area: ['350px', '300px']
                        , btnAlign: 'c'
                        , anim: 0
                        , btn: ['确定', '取消']
                        , success: function () {
                            var index = layer.load(1, {
                                shade: [0.1, '#fff'] //0.1透明度的白色背景
                            });
                            $.ajax({
                                type: 'put',
                                url: '/Goods/lotte/putOnSale',
                                data: {ids: data.id, type: 'local', _token: "{{ csrf_token() }}"},
                                dataType: 'json',
                                success: function (res) {
                                    layer.close(layer.index)
                                    var str = '';
                                    if (Array.isArray(res.errAll)) {
                                        if (res.errAll.length != 0) {
                                            (res.errAll.reverse()).forEach(function (item) {
                                                str += "<div>" + item + "</div>";
                                            })
                                        } else {
                                            if (res.msg !== '') {
                                                str = res.msg;
                                            } else {
                                                str = "<div> 操作成功，请等待系统同步。</div>";
                                            }
                                        }
                                    }
                                    $("#Rakuten-wrapper .wrapper").append(str);
                                }
                            });
                        },
                        end: function () {
                            ids = '';
                            $("#Rakuten-wrapper .wrapper").empty();
                            table.reload('EDtable');
                        }
                    });
                }

                //认领
                if (obj.event === 'edit') {
                    layer.open({
                        type: 2,
                        title: '草稿编辑',
                        fix: false,
                        maxmin: true,
                        shadeClose: true,
                        offset: 'r',
                        area: ['80%', '90%'],
                        content: '{{ url('Goods/lotte/edit') }}' + '?id=' + data.id,
                        end: function (index) {
                            ids = '';
                            layer.close(index);
                        }
                    });
                }

                if(obj.event === 'check_img'){
                    if (data.img_url) {
                        check_img(data.img_url);
                    }
                }
            });

            table.on('tool(EDtable1)', function (obj) {
                var data = obj.data;
                //认领
                if(obj.event === 'del'){
                    layer.confirm('确认删除', {title:'提示'}, function(index){

                        //ajax 删除采集商品
                        $.ajax({
                            url: "/Goods/lotte/delete"
                            , type: "get"
                            , dataType: "json"
                            , data: {
                                'id': data.id
                            }

                            , success: function (res) {
                                if (res) {
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
                } else if (obj.event === 'putOnSale') {
                    layer.open({
                        type: 1
                        , title: '乐天上架'
                        , content: $("#Rakuten-wrapper")
                        , area: ['350px', '300px']
                        , btnAlign: 'c'
                        , anim: 0
                        , btn: ['确定', '取消']
                        , success: function () {
                            var index = layer.load(1, {
                                shade: [0.1, '#fff'] //0.1透明度的白色背景
                            });
                            $.ajax({
                                type: 'put',
                                url: '/Goods/lotte/putOnSale',
                                data: {ids: data.id, type: 'local', _token: "{{ csrf_token() }}"},
                                dataType: 'json',
                                success: function (res) {
                                    layer.close(layer.index)
                                    var str = '';
                                    if (Array.isArray(res.errAll)) {
                                        if (res.errAll.length != 0) {
                                            (res.errAll.reverse()).forEach(function (item) {
                                                str += "<div>" + item + "</div>";
                                            })
                                        } else {
                                            if (res.msg !== '') {
                                                str = res.msg;
                                            } else {
                                                str = "<div> 操作成功，请等待系统同步。</div>";
                                            }
                                        }
                                    }
                                    $("#Rakuten-wrapper .wrapper").append(str);
                                }
                            });
                        },
                        end: function () {
                            $("#Rakuten-wrapper .wrapper").empty();
                            table.reload('EDtable');
                        }
                    });
                }

                //认领
                if (obj.event === 'edit') {
                    layer.open({
                        type: 2,
                        title: '草稿编辑',
                        fix: false,
                        maxmin: true,
                        shadeClose: true,
                        offset: 'r',
                        area: ['80%', '90%'],
                        content: '{{ url('Goods/lotte/edit') }}' + '?id=' + data.id,
                        end: function (index) {
                            layer.close(index);
                        }
                    });
                }
                if(obj.event === 'check_img'){
                    if (data.img_url) {
                        check_img(data.img_url);
                    }
                }

            });
            var _checkbox = function(name){
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
            };

            _checkbox('EDtable');
            _checkbox('EDtable1');

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
                    if (this.checked === true) {
                        i++;
                    }
                    j++;
                });
                if (i == j) {
                    $(".checkboxAll").prop("checked", true);
                    form.render('checkbox');
                } else {
                    $(".checkboxAll").removeAttr("checked");
                    form.render('checkbox');
                }

            });
            $('#BatchAdd').click(function(){
                layer.open({
                    title:"上架商品",
                    type: 2,
                    area:["600px","260px"],
                    content: ['{{ url('Goods/lotte/add') }}','no'],
                    btn: ['确定', '取消'],
                    success: function(){

                    },
                    btn1: function(index, layero){
                        var info = window["layui-layer-iframe" + index].callbackdata();
                        if(!info.sku){
                            layer.msg('SKU不能为空');
                            return;
                        }

                        //判断输入的售后单是否存在
                        $.get('/Goods/lotte/checkGoods', {sku: info.sku}, function (e) {
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
                                    offset: 'r',
                                    area: ['80%', '90%'],
                                    content: '{{ url('Goods/lotte/edit') }}?sku='+info.sku,
                                    end: function (index) {
                                        ids = '';
                                        layer.close(index);
                                    }
                                });
                            }
                        });
                    }
                });
            });
        });

        layui.use('element', function () {
            var element = layui.element;

            //获取hash来切换选项卡，假设当前地址的hash为lay-id对应的值
            var layid = location.hash.replace(/^#test1=/, '');
            element.tabChange('test1', layid); //假设当前地址为：http://a.com#test1=222，那么选项卡会自动切换到“发送消息”这一项

            //监听Tab切换，以改变地址hash值
            element.on('tab(test1)', function () {
                location.hash = 'test1=' + this.getAttribute('lay-id');
            });
        });

    </script>
@endsection
