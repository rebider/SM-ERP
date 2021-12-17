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

                    <div class="layui-form-item">

                        <div class="second">
                            <!--订单号-->
                            <div class="form-search-group">
                                <div class="inputTxt element-display-inline">商品管理番号：</div>
                                <div class="inputBlock element-display-inline">
                                    <input type="text" name="cmn" autocomplete="off" class="layui-input">
                                </div>
                            </div>

                            <div class="form-search-group">
                                <div class="inputTxt element-display-inline">商品番号：</div>
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
                <div class="operate btn-3  fr">
                    <button class="layui-btn" id="export">导出</button>
                </div>
                <div class="operate btn-2 fr">
                    <button class="layui-btn click-btn" data-type="BatchDown">批量下架</button>
                </div>
                <div class="operate btn-1 fr" style="display: none;">
                    <button class="layui-btn click-btn" data-type="BatchPutOn">批量上架</button>
                </div>
            </div>

            <div class="layui-tab" lay-filter="test1">
                <ul class="layui-tab-title">
                    <li class="layui-this" lay-id="111" data-total="0">在线商品</li>
                    <li lay-id="222" data-total="0">下架商品</li>
                    <li lay-id="333" data-total="0">更新失败</li>
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
    <!--乐天上架-->
    <div id="Rakuten-wrapper">
        <div class="wrapper" style="padding: 20px">

        </div>
    </div>
@endsection
@section('javascripts')
    <script type="text/html" id="barDemo">
        @{{#  if(d.synchronize_status === 1){ }}
        <a class="layui-table-link" href="javascript:;" lay-event="showDetail">查看</a>&nbsp;&nbsp;
        <a class="layui-table-link" href="javascript:;" lay-event="edit">编辑</a>&nbsp;&nbsp;
        {{--<a class="set-gray" href="javascript:;" lay-event="obtained">下架</a>&nbsp;&nbsp;--}}
        <a class="layui-table-link" href="javascript:;" lay-event="obtained">下架</a>&nbsp;&nbsp;
        @{{#  } }}

        @{{#  if(d.synchronize_status === 2){ }}
        <a class="layui-table-link" href="javascript:;" lay-event="showDetail">查看</a>&nbsp;&nbsp;
        <a class="layui-table-link" href="javascript:;" lay-event="edit">编辑</a>&nbsp;&nbsp;
        {{--<a class="set-gray" href="javascript:;" lay-event="putOnSale">上架</a>&nbsp;&nbsp;--}}
        <a class="layui-table-link" href="javascript:;" lay-event="putOnSale">上架</a>&nbsp;&nbsp;
        @{{#  } }}

        @{{#  if(d.synchronize_status === 3){ }}
        {{--<a class="set-gray" href="javascript:;" lay-event="showDetail">查看</a>&nbsp;&nbsp;--}}
        <a class="layui-table-link" href="javascript:;" lay-event="showDetail">查看</a>&nbsp;&nbsp;
        <a class="layui-table-link" href="javascript:;" lay-event="edit">编辑</a>&nbsp;&nbsp;
        @{{#  } }}
    </script>
    <script>
        let checkedValue = [];
        let checkedData = {};
        var indexCheck = 0;

        layui.use(['form', 'laydate', 'table','element'], function () {
            var layer = layui.layer,
                form = layui.form,
                laypage = layui.laypage,
                table = layui.table,
                laydate = layui.laydate,
                element = layui.element;
            let ids = '';
            var index = layer.msg('数据请求中', {icon: 16});

            lay('.time-item').each(function(){
                laydate.render({
                    elem: this
                    , trigger: 'click'
                });
            });
            $(".click-btn").click(function () {
                let type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
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
                                data: {ids: ids, type: 'online', _token: "{{ csrf_token() }}"},
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
                        },
                        end: function () {
                            ids = '';
                            $("#Rakuten-wrapper .wrapper").empty();
                            table.reload('EDtable');
                            table.reload('EDtable1');
                            table.reload('EDtable2');
                        }
                    });
                },
                BatchDown: function () {
                    if (ids === undefined || ids == '') {
                        layer.msg('请选择需要下架的记录！', {icon: 5});
                        return false;
                    }
                    layer.open({
                        type: 1
                        , title: '批量下架'
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
                                url: '/Goods/onlineRakuten/obtained',
                                data: {ids: ids, _token: "{{ csrf_token() }}"},
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
                                                if (res.msg !== '') {
                                                    str = res.msg;
                                                } else {
                                                    str = "<div> 批量操作成功，请等待系统同步。</div>";
                                                }
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
                            table.reload('EDtable1');
                            table.reload('EDtable2');
                        }
                    });

                }
            };

            //---------------------------表格部分开始↓--------------------------
            //封装render
            let table1 = function (info, element, index) {
                let colsParamsWithoutReason = [[
                    {checkbox: true}
                    , {field: '', title: '序号', width: 50, type: 'numbers'}
                    , {field: 'local_sku', title: '自定义sku', event: 'showDetail', style: 'cursor:pointer;color:#01AAED'}
                    , {
                        field: 'img_url', title: '商品主图', height: "80px", templet: function (d) {
                            if (d.img_url) {
                                return '<img onerror="this.src=\'/img/imgNotFound.jpg\'" src="{{url('showImage').'?path='}}'+d.img_url+'" style="height:24px;width:24px;cursor:zoom-in;" alt=""/>';
                            }
                            return '';
                        },
                        event: 'check_img',
                    }

                    , {field: 'goods_name', title: '商品名称'}

                    , {
                        field: 'shops', title: '店铺', templet: function (d) {
                            return d.shops.shop_name
                        }
                    }

                    , {field: 'sku', title: '商品番号'}
                    , {field: 'sale_price', title: '销售价格'}
                    , {field: 'currency_code', title: '销售币种'}
                    , {
                        field: 'synchronize_status', title: '商品状态', templet: function (d) {
                            if (d.synchronize_status === 1) {
                                return '已上架'
                            }
                            if (d.synchronize_status === 2) {
                                return '已下架'
                            }
                            if (d.synchronize_status === 3) {
                                return '更新失败'
                            }
                        }
                    }
                    , {fixed: 'right', title: '操作', toolbar: '#barDemo', width: 200}
                ]];
                let colsParamsWithReason = [[
                    {checkbox: true}
                    , {field: '', title: '序号', width: 50, type: 'numbers'}
                    , {field:'local_sku',  width: 150, title:'自定义sku', event: 'showDetail', style: 'cursor:pointer;color:#01AAED'}
                    , {
                        field: 'img_url', title: '商品主图', height: "80px", templet: function (d) {
                            if (d.img_url) {
                                return '<img onerror="this.src=\'/img/imgNotFound.jpg\'" src="{{url('showImage').'?path='}}'+d.img_url+'" style="height:24px;width:24px;cursor:zoom-in;" alt=""/>';
                            }
                            return '';
                        },
                        event: 'check_img',
                    }

                    , {field: 'goods_name', title: '商品名称'}

                    , {
                        field: 'shops', title: '店铺', templet: function (d) {
                            return d.shops.shop_name
                        }
                    }

                    , {field: 'sku', title: '商品番号'}
                    ,{field:'sale_price', title:'销售价格'}
                    ,{field:'currency_code', title:'销售币种'}
                    ,{field:'synchronize_status', title:'产品状态', templet: function(d){
                        if (d.synchronize_status === 1) { return '已上架'}
                        if (d.synchronize_status === 2) { return '已下架'}
                        if (d.synchronize_status === 3) { return '更新失败'}
                    }}
                    ,{field:'synchronize_info', width: 200, title:'原因'}
                    ,{fixed: 'right', title:'操作', toolbar: '#barDemo', width:200}
                ]];
                table.render({
                    elem: element
                    , url: '/Goods/onlineRakuten/ajaxGetAllByParams'
                    , parseData: function (res) {
                        return {
                            "code": res.code,
                            "data": res.data.data,
                            "message": res.msg,
                            "count": res.total
                        }
                    }
                    , where: {data: info}
                    , cols: element != '#EDtable2' ? colsParamsWithoutReason : colsParamsWithReason
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
                table1({synchronizeType: 1}, '#EDtable', index);
                table1({synchronizeType: 2}, '#EDtable1', index);
                table1({synchronizeType: 3}, '#EDtable2', index);
            })
            //点击搜索框根据当前Tab类型执行相应的表格进行渲染
            form.on('submit(searBtn)', function (data) {
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

            table.on('tool(EDtable)', function (obj) {
                var data = obj.data;
                //认领
                if (obj.event === 'delete') {
                    layer.confirm('确认删除', {title: '提示'}, function (index) {
                        //ajax 删除采集商品
                        $.ajax({
                            url: "/Mapping/del"
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
                }
                else if (obj.event === 'obtained') {
                    layer.open({
                        type: 1
                        , title: '乐天下架'
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
                                url: '/Goods/onlineRakuten/obtained',
                                data: {ids: data.id, _token: "{{ csrf_token() }}"},
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
                                                if (res.msg !== '') {
                                                    str = res.msg;
                                                } else {
                                                    str = "<div> 操作成功，请等待系统同步。</div>";
                                                }
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
                            table.reload('EDtable1');
                            table.reload('EDtable2');
                        }
                    });

                }
                //认领
                if (obj.event === 'edit') {
                    layer.open({
                        type: 2,
                        title:'编辑乐天商品',
                        fix: false,
                        maxmin: true,
                        resize: true,
                        shadeClose: true,
                        offset: 'r',
                        // btnAlign: 'c',
                        area: ['80%', '90%'],
                        // btn: ['保存', '取消'],
                        content: '{{ url('Goods/onlineRakuten/edit') }}' + '?id=' + obj.data.id,
                        yes: function (index, layero) {
                            //点击确认触发 iframe 内容中的按钮提交
                            var submit = layero.find('iframe').contents().find("#LAY-front-submit");
                            submit.click();
                        },
                        end: function (index) {
                            ids = '';
                            layer.close(index);
                        }
                    });
                }

                if (obj.event == 'showDetail') {
                    layer.open({
                        type: 2,
                        title: '查看乐天商品',
                        fix: false,
                        maxmin: true,
                        shadeClose: true,
                        offset: 'r',
                        area: ['80%', '90%'],
                        content: '{{ url('Goods/onlineRakuten/detail') }}' + '?id=' + obj.data.id,
                        end: function (index) {
                            ids = '';
                            layer.close(index);
                        }
                    });
                }

                if(obj.event === 'check_img') {
                    check_img(data.img_url);
                }
            });
            table.on('tool(EDtable1)', function (obj) {
                var data = obj.data;
                //认领
                if (obj.event === 'delete') {
                    layer.confirm('确认删除', {title: '提示'}, function (index) {
                        //ajax 删除采集商品
                        $.ajax({
                            url: "/Mapping/del"
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
                        , area: ['380px', '300px']
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
                                data: {ids: data.id, type: 'online', _token: "{{ csrf_token() }}"},
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
                            table.reload('EDtable1');
                            table.reload('EDtable2');
                        }
                    });
                }

                //认领
                if (obj.event === 'edit') {
                    layer.open({
                        type: 2,
                        title:'编辑乐天商品',
                        fix: false,
                        maxmin: true,
                        resize: true,
                        shadeClose: true,
                        offset: 'r',
                        // btnAlign: 'c',
                        area: ['80%', '90%'],
                        // btn: ['保存', '取消'],
                        content: '{{ url('Goods/onlineRakuten/edit') }}' + '?id=' + obj.data.id,
                        end: function (index) {
                            ids = '';
                            layer.close(index);
                        }
                    });
                }

                if (obj.event == 'showDetail') {
                    layer.open({
                        type: 2,
                        title: '查看乐天商品',
                        fix: false,
                        maxmin: true,
                        shadeClose: true,
                        offset: 'r',
                        area: ['80%', '90%'],
                        content: '{{ url('Goods/onlineRakuten/detail') }}' + '?id=' + obj.data.id,
                        end: function (index) {
                            ids = '';
                            layer.close(index);
                        }
                    });
                }

                if(obj.event === 'check_img') {
                    check_img(data.img_url);
                }
            });
            table.on('tool(EDtable2)', function (obj) {
                var data = obj.data;
                //认领
                if (obj.event === 'delete') {
                    layer.confirm('确认删除', {title: '提示'}, function (index) {
                        //ajax 删除采集商品
                        $.ajax({
                            url: "/Mapping/del"
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
                                }
                                else {
                                    layer.msg('删除失败!', {icon: 6});
                                }
                            }
                        });
                        layer.close(index);
                    });
                }

                //认领
                if (obj.event === 'edit') {
                    layer.open({
                        type: 2,
                        title:'编辑乐天商品',
                        fix: false,
                        maxmin: true,
                        resize: true,
                        shadeClose: true,
                        offset: 'r',
                        // btnAlign: 'c',
                        area: ['80%', '90%'],
                        // btn: ['保存', '取消'],
                        content: '{{ url('Goods/onlineRakuten/edit') }}' + '?id=' + obj.data.id,
                        end: function (index) {
                            ids = '';
                            layer.close(index);
                        }
                    });
                }

                if (obj.event == 'showDetail') {
                    layer.open({
                        type: 2,
                        title: '查看乐天商品',
                        fix: false,
                        maxmin: true,
                        shadeClose: true,
                        offset: 'r',
                        area: ['80%', '90%'],
                        content: '{{ url('Goods/onlineRakuten/detail') }}' + '?id=' + obj.data.id,
                        end: function (index) {
                            ids = '';
                            layer.close(index);
                        }
                    });
                }

                if(obj.event === 'check_img') {
                    check_img(data.img_url);
                    ids = id_array.join(',');
                }
            });
            function _checkbox(name){
                table.on('checkbox('+ name +')', function (e) {
                    //----------------zt8067----------------//
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

            _checkbox('EDtable1');
            _checkbox('EDtable2');


            // checkbox all
            // form.on('checkbox(allChoose)', function (data) {
            //     $("input[name='check[]']").each(function () {
            //         this.checked = data.elem.checked;
            //     });
            //     form.render('checkbox');
            // });
            // form.on('checkbox(oneChoose)', function (data) {
            //     var i = 0;
            //     var j = 0;
            //     $("input[name='check[]']").each(function () {
            //         if( this.checked === true ){
            //             i++;
            //         }
            //         j++;
            //     });
            //     if( i == j ){
            //         $(".checkboxAll").prop("checked",true);
            //         form.render('checkbox');
            //     }else{
            //         $(".checkboxAll").removeAttr("checked");
            //         form.render('checkbox');
            //     }
            //
            // });

            table.on('checkbox(EDtable)', function (e) {
                //----------------zt8067----------------//
                var checkStatus = table.checkStatus('EDtable');
                var id_array = new Array();
                var data = checkStatus.data;

                if (data.length > 0) {
                    for (var i = 0; i < data.length; i++) {
                        id_array.push(data[i].id);
                    }
                }
                ids = id_array.join(',');
                //------------------end-------------------//
                // if (!e.checked) {
                //     if (e.type == 'one') {
                //         let index = checkedValue.indexOf(e.data.id)
                //         checkedValue.splice(index, 1)
                //         delete checkedData[e.data.id]
                //     }
                //     if (e.type == 'all') {
                //         checkedValue = [];
                //         checkedData = {};
                //     }
                // } else {
                //     if (e.type == 'all') {
                //         let skuEle = $(".sku-label");
                //         $.each(skuEle, function (k, v) {
                //             checkedValue.push($(this).val());
                //             checkedData[$(this).val()] = $(this).attr('data-name')
                //         });
                //     }
                //     if (e.type == 'one') {
                //         checkedValue.push(e.data.id)
                //         checkedData[e.data.id] = e.data.goods_name
                //     }
                // }
                //
                // ids = checkedValue.join(',');
            });

            $('#BatchPutOn').click(function () {
                if (ids == '') {
                    layer.msg('请选择商品');
                    return false;
                } else {
                    var elect = $('tbody .layui-form-checked').parents('tr');
                    layer.confirm('确认删除所选项？', {
                        btn: ['确定', '取消'],
                        yes: function (index) {
                            $.ajax({
                                url: "/Mapping/lotte/PutOnSaleById"
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

            $("#export").click(function () {
                if (ids == '') {
                    layer.msg('请选择至少一条数据进行导出', {icon: 2});
                    return false;
                }
                window.location.href = '/Goods/onlineRakuten/export?ids=' + ids
            })

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
                        checkedValue = [];
                        checkedData = {};
                        break;
                    case 1:
                        $(".btn-1").show();
                        $(".btn-2").hide();
                        $("div[lay-filter='LAY-table-1'],div[lay-filter='LAY-table-3']").find("input[type='checkbox']").prop("checked", false);
                        checkedValue = [];
                        checkedData = {};
                        break;
                    case 2:
                        $(".btn-1,.btn-2").hide();
                        $("div[lay-filter='LAY-table-1'],div[lay-filter='LAY-table-2']").find("input[type='checkbox']").prop("checked", false);
                        checkedValue = [];
                        checkedData = {};
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
        });


    </script>
@endsection
