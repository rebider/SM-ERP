@extends('layouts.new_main')
@section('head')
    <style>
        .titleTxt {
            width: 100px;
            text-align: right;
        }
        .layui-layer-btn {
            text-align: center!important;
        }
    </style>
@endsection
@section('content')
    <div class="content-wrapper">
        <form class="multiSearch layui-form">
            <div class="flexSearch flexquar fclear">
                <!--平台-->
                <div class="frist">
                    <div class="inputTxt titleTxt">平台：</div>
                    <div class="inputBlock">
                        <div class="multLable">
                            <input type="radio" name="platform_id" value="" title="全部" checked>
                            <input type="radio" name="platform_id" value="1" title="亚马逊">
                            <input type="radio" name="platform_id" value="2" title="乐天">
                        </div>
                    </div>
                </div>
                <div class="frist">
                    <div class="inputTxt titleTxt">映射状态：</div>
                    <div class="inputBlock">
                        <div class="multLable">
                            <input type="radio" name="status" value="" title="全部" checked>
                            <input type="radio" name="status" value="0" title="未映射">
                            <input type="radio" name="status" value="1" title="已映射">
                        </div>
                    </div>
                </div>
                <div class="second">
                    <!--搜索时间类型-->
                    <div class="inputBlock" style="margin-left: 0;">
                        <div class="inputTxt">
                            <div class="inputTxt titleTxt">产品编号：</div>
                            <div>
                                <select name="type" id="">
                                    <option value="">请选择</option>
                                    <option value="sku">自定义SKU</option>
                                    <option value="seller_sku">SellerSKU</option>
                                    <option value="upc">UPC</option>
                                    <option value="asin">ASIN</option>
                                    <option value="itemURL">商品管理番号</option>
                                    <option value="item_number">商品番号</option>
                                </select>
                            </div>
                            <div class="layui-input-inline">
                                <input type="text" name="type_name"
                                       autocomplete="off" class="layui-input writeinput">
                            </div>
                        </div>
                        <div class="inputBlock">
                            <div class="inputTxt titleTxt">店铺：</div>
                            <div class="layui-input-inline">
                                    <select name="setting_shops_id" lay-filter="setting_shops_id" lay-search="">
                                        <option class="tips" value=""></option>
                                        @if(isset($shops))
                                            @foreach($shops as $shop)
                                                <option value="{{$shop['id']}}">{{$shop['shop_name']}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="search" style="float: left;margin-left: 102px;">
                    <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="searBtn">搜索</button>
                    <button class="layui-btn layui-btn-primary" lay-submit="">重置</button>
                </div>
                <div class="search" style="float: right;">
                    {{--<div style="display: inline-block">最近同步时间：2019-01-01 18:00:55</div>--}}
                    <button type="button" class="layui-btn layuiadmin-btn-rules" data-type="exportGoodsMapping">导入映射
                    </button>
                    <button type="button" class="layui-btn layuiadmin-btn-rules" data-type="cancelMapping">取消映射</button>
                    <button type="button" class="layui-btn layuiadmin-btn-rules" data-type="export">导出</button>
                </div>
            </div>
        </form>
        <div class="edn-row table_index">
            <table class="" id="EDtable" lay-filter="EDtable"></table>
            <script type="text/html" id="table-edit">
                @{{# if(d.status==0){  }}
                {{--<a class="layui-btn layui-btn-xs" lay-event="edit" data-type="create">映射</a>--}}
                <a class="layui-table-link" lay-event="edit" data-type="create">映射</a>
                @{{# }else{ }}
                <a class="layui-table-link" lay-event="cancel">取消映射</a>
                {{--<a class="layui-btn layui-btn-xs" lay-event="cancel">取消映射</a>--}}
                {{--<a class="layui-btn layui-btn-xs" lay-event="edit" data-type="edit">编辑</a>--}}
                <a class="layui-table-link" lay-event="edit" data-type="edit">编辑</a>
                @{{# } }}
            </script>
        </div>
    </div>

    <div id="mapping">
        <div class="wrap">
            <form class="layui-form multiSearch" onsubmit="false">
                <input type="hidden" name="mapping_id" value="">
                <div class="top">
                    <div>自定义SKU</div>
                    <div style="width: 200px;height: 20px;">
                        <input type="hidden" class="goods_id" value="">
                        <select name="sku" lay-filter="sku" lay-search="">
                            <option class="tips" value=""></option>
                            @if(isset($sku))
                                @foreach($sku as $item_sku)
                                    <option value="{{$item_sku['id']}}">{{$item_sku['sku']}}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div>
                        <button type="button" class="layui-btn btn-sku">添加SKU</button>
                    </div>
                </div>
            </form>
            <div class="mask">
                <table id="mapping-table" lay-filter="mapping-table"></table>
            </div>
            <script type="text/html" id="mapping-edit">
                <a class="layui-btn layui-btn-xs" lay-event="del">删除</a>
            </script>

        </div>
    </div>
    {{--导入映射弹窗--}}
    <div id="uploadPop" class="hide">
        <div class="container">
            <div>xlsx文件：</div>
            <button type="button" class="layui-btn layui-btn-sm layui-btn-normal" id="selectFile">选择文件</button>
            <button type="button" class="layui-btn layui-btn-sm" id="uploadFile">开始上传</button>
            <div>
                <a href="/file/亚马逊映射商品导入模板.xlsx" download="商品映射导入（亚马逊）.xlsx"
                   style="color:#1D8DDE;">《导入亚马逊模板》</a>
                <a href="/file/乐天映射商品导入模板.xlsx" download="商品映射导入（乐天）.xlsx"
                   style="color:#1D8DDE;">《导入乐天模板》</a>
            </div>
        </div>
    </div>
    <style>
        #mapping, .mask {
            display: none;
        }

        #mapping .wrap {
            padding: 20px;
        }

        #mapping .wrap .top > div {
            display: inline-block;
        }

        #mapping .wrap .top > div:first-child {
            font-size: 16px;
            font-weight: bold;
        }

        #mapping .wrap .top > div:nth-child(2) {
            margin: 0 10px;
        }

        #mapping .wrap td, #mapping .wrap th {
            text-align: center;
        }

        #mapping .wrap tr td {
            border: none;
        }
    </style>
@endsection
@section('javascripts')
    <script>
        //layui加载
        layui.config({base: '../../layui/lay/modules/'}).extend({formSelects: 'formSelects-v3'});
        layui.use(['layer', 'form', 'element', 'laydate', 'upload', 'table', 'formSelects'], function () {
            var layer = layui.layer, form = layui.form, laydate = layui.laydate, upload = layui.upload,
                table = layui.table,
                element = layui.element, formSelects = layui.formSelects;
            var index = layer.msg('数据请求中', {icon: 16});
            var tableData = [], mapping, ids;
            //商品映射模板
            var mappingTable = function(tableData){
                table.render({
                    elem: '#mapping-table'
                    , data: tableData//数据接口
                    , page: false //开启分页
                    , cols: [[
                        {field: '', title: '序号', width: 50, type: 'numbers'}
                        , {
                            field: 'goods_sku', title: '自定义SKU', templet: function (d) {
                                return d.goods_sku;
                            }
                        },
                        {
                            field: 'goods_number', title: '数量', templet: function (d) {
                                var goods_number = d.goods_number === undefined ? 0 : d.goods_number;
                                return '<input value="' + goods_number + '" onchange="mappingNumber(this,' + d.LAY_TABLE_INDEX + ')" onkeyup="value=value.replace(/[^\\d]/g,\'\')" onblur="value=value.replace(/[^\\d]/g,\'\')"  style="width:50px;height:28px;text-align: center;" type="number" name="goods_number" value="0" min="0" max="9999">';
                            }
                        }
                        , {title: '操作', width: 100, toolbar: "#mapping-edit"}
                    ]]
                });
            };


            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $(document).on('click', '#uploadFile', function () {
                if ($('.layui-upload-choose').text() == '') {
                    layer.alert('请选择需要上传的模版附件！');
                    return false;
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
            //选完文件后不自动上传
            upload.render({
                elem: '#selectFile'
                , url: '/Goods/mapping/importGoodsMapping'
                , auto: false
                , exts: 'xlsx|xls'
                , shade: 0.8
                , bindAction: '#uploadFile'
                , data: {'_token': $('meta[name="csrf-token"]').attr('content')}
                , before: function (obj) { //obj参数包含的信息，跟 choose回调完全一致，可参见上文。
                    layer.load(); //上传loading
                }
                , done: function (res) {
                    var icon;
                    layer.closeAll('loading');
                    let _err = '';
                    if (res.code == 1) {
                        layer.alert(res.msg);
                        table.reload('EDtable');
                    } else {
                        if (res.msg !== '') {
                            _err = res.msg;
                        } else {
                            _err = res.err;
                        }
                        layer.alert(_err, {end: function () {
                                location.reload();
                            }});
                    }
                    layer.close(_index);
                }
            });

            var active = {
                exportGoodsMapping: function () {
                    _index = layer.open({
                        type: 1
                        , offset: 'auto'
                        , title: '商品映射导入'
                        , content: $('#uploadPop')
                        , area: ['600px', '180px']
                    });
                },
                cancelMapping: function () {
                    if (ids === undefined) {
                        layer.msg('请选择需要修改的记录！', {icon: 5});
                        return false;
                    }
                    layer.confirm('是否确认取消映射。', {title: '取消映射'}, function (index) {
                        $.ajax({
                            type: 'put',
                            url: '/Goods/mapping/cancel',
                            data: {ids: ids, _token: "{{ csrf_token() }}"},
                            dataType: 'json',
                            success: function (res) {
                                if (res.code === 1) {
                                    layer.msg(res.msg, {icon: 1});
                                    layer.closeAll();
                                    table.reload('EDtable');
                                } else {
                                    layer.msg(res.msg, {icon: 5});
                                }
                            }
                        });
                    })
                },
                export: function () {
                    if (ids === undefined) {
                        layer.msg('请选择需要导出的记录！', {icon: 5});
                        return false;
                    }
                    window.location.href = "/Goods/mapping/export?ids=" + ids;
                },
            };

            $('.layuiadmin-btn-rules').on('click', function (e) {
                e.preventDefault();
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });
            //添加sku
            $(document).on('click', '.btn-sku', function () {
                var goods_id = $("#mapping .goods_id").val();
                if(!goods_id){
                    layer.msg('请填写sku', {icon: 5});
                    return false;
                }

                layer.msg('获取本地商品中...', {
                    icon: 16
                    , shade: 0.01
                });

                if (tableData.length > 0) {
                    for (var i = 0; i < tableData.length; i++) {
                        if (tableData[i].id == goods_id) {
                            layer.msg('该商品已添加', {icon: 5});
                            return false;
                        }
                    }
                }
                $.ajax({
                    type: 'get',
                    url: '/Goods/mapping/product' + '?goods_id=' + goods_id,
                    dataType: 'json',
                    success: function (res) {
                        if (res.code !== 0) {
                            layer.msg('数据加载错误', {icon: 5});
                            return false;
                        } else {

                            $("#mapping .goods_id").val('');
                            $("select[name='sku']").prop('selectedIndex', 0);
                            form.render('select');
                            $(".mask").show();
                            var data = res.data[0];
                            data['goods_number'] = 0;
                            tableData[tableData.length] = data;
                            mappingTable(tableData);
                        }
                    }
                });
            });
            //选择sku
            form.on('select(sku)', function (obj) {
                var data = obj.value;
                if (!data) {
                    return false;
                }
                $("#mapping .goods_id").val(data);
            });
            table.on('tool(EDtable)', function (obj) {
                var type = $(this).context.dataset.type;
                var _data = obj.data, id = _data.id;
                switch (obj.event) {
                    case'edit':
                        mapping = layer.open({
                            type: 1
                            , title: '商品映射'
                            , content: $("#mapping")
                            , area: ['800px', '500px']
                            , btnAlign: 'c'
                            , anim: 0
                            , btn: ['保存', '取消']
                            , maxmin: true
                            , success: function (layero, index) {
                                $("#mapping input[name=\"mapping_id\"]").val(id);
                                $("#mapping .layui-input").val('').focus();
                                if (type == 'create') {
                                    layer.msg('加载中...', {
                                        icon: 16
                                        , shade: 0.01
                                    });
                                } else {
                                   layer.msg('获取已映射商品中...', {
                                        icon: 16
                                        , shade: 0.01
                                    });
                                }
                                $.ajax({
                                    type: 'get',
                                    url: '/Goods/mapping/getGoodsMapping' + '?id=' + id,
                                    dataType: 'json',
                                    success: function (res) {
                                        $(".mask").show();
                                        tableData = res.data;
                                        mappingTable(tableData)
                                    }
                                });
                            }, yes: function () {
                                if (tableData.length === 0) {
                                    layer.msg('未添加映射关系', {icon: 5});
                                    return false;
                                }
                                var err = '';
                                tableData.forEach(function (val, i) {
                                    if (val.number <= 0) {
                                        err += "<div>序号" + (i + 1) + ": 未配置数量</div>";
                                    }
                                });
                                if (err != '') {
                                    layer.alert(err, {icon: 5});
                                    return false;
                                }
                                var _id = $("#mapping input[name=\"mapping_id\"]").val();
                                var params = {
                                    id: _id,
                                    data: tableData,
                                    _token: "{{ csrf_token() }}"
                                };
                                $.ajax({
                                    type: 'post',
                                    url: '/Goods/mapping/create',
                                    data: params,
                                    dataType: 'json',
                                    success: function (res) {
                                        if (res.code == 1) {
                                            layer.msg(res.msg, {icon: 1});
                                            layer.close(mapping);
                                            table.reload('EDtable');
                                        } else {
                                            layer.msg(res.msg, {icon: 5});
                                        }
                                    }
                                });
                            }, end: function (index, layero) {
                                $(".mask").hide();
                                tableData = [];
                                table.reload('mapping-table');
                                table.reload('EDtable');
                            }
                        });
                        break;
                    case 'cancel':
                        layer.open({
                            type: 1
                            ,
                            title: '取消映射'
                            ,
                            content: '<p style="width:100%;text-align: center;display: flex;justify-content: center;align-items: center;height: 100%;">是否确认取消映射。</p>'
                            ,
                            area: ['300px', '150px']
                            ,
                            btnAlign: 'c'
                            ,
                            anim: 0
                            ,
                            btn: ['确定', '取消']
                            ,
                            yes: function () {
                                $.ajax({
                                    type: 'put',
                                    url: '/Goods/mapping/cancel',
                                    data: {id: id, _token: "{{ csrf_token() }}"},
                                    dataType: 'json',
                                    success: function (res) {
                                        if (res.code == 1) {
                                            layer.msg(res.msg, {icon: 1});
                                            layer.closeAll();
                                            table.reload('EDtable');
                                        } else {
                                            layer.msg(res.msg, {icon: 5});
                                        }
                                    }
                                });
                            }
                        });
                        break;
                }
            });
            table.on('tool(mapping-table)', function (obj) {
                switch (obj.event) {
                    case'del':
                    {
                        var id = obj.data.id;
                        tableData.forEach(function (val, i) {
                            if (val.id == id) {
                                tableData.splice(i, 1);
                            }
                        });
                        obj.del();
                        layer.close(index);
                        mappingTable(tableData);
                    }
                        break;
                }
            });
            form.on('submit(searBtn)', function (data) {
                var index = layer.msg('数据请求中', {icon: 16});
                var info = data.field;
                table.render({
                    elem: '#EDtable'
                    , url: '/Goods/mapping/lists'
                    , where: {data: info}
                    , cols: [[
                        {checkbox: true}
                        , {field: '', title: '序号', width: 50, type: 'numbers'}
                        , {
                            field: 'platforms', title: '平台', templet: function (d) {
                                return d.platforms.name_CN;
                            }
                        }
                        , {
                            field: 'shop', title: '店铺', templet: function (d) {
                                return d.shop.shop_name;
                            }
                        }
                        , {
                            field: 'itemURL', title: '商品管理番号', templet: function (d) {
                                return d.itemURL ? d.itemURL : '--';
                            }
                        }
                        , {
                            field: 'item_number', title: '商品番号', templet: function (d) {
                                return d.item_number ? d.item_number : '--';
                            }
                        }
                        , {
                            field: 'seller_sku', title: 'SellerSKU', templet: function (d) {
                                return d.seller_sku ? d.seller_sku : '--';
                            }
                        }
                        , {
                            field: 'asin', title: 'ASIN', templet: function (d) {
                                return d.asin ? d.asin : '--';
                            }
                        }
                        , {
                            field: 'upc', title: 'UPC', templet: function (d) {
                                return d.upc ? d.upc : '--';
                            }
                        }
                        , {
                            field: 'sku', width: 120, title: '自定义SKU', templet: function (d) {
                                var str = '';
                                if (Array.isArray(d.mapping_goods)) {
                                    (d.mapping_goods).forEach(function (val, i) {
                                        str += val['goods_sku'] + ',';
                                    })
                                    str = str.substring(0, str.length - 1);
                                }
                                return str;
                            }
                        }
                        , {
                            field: 'goods_number', title: '映射数量', templet: function (d) {
                                return d.goods_number ? d.goods_number : '';
                            }
                        }
                        , {
                            field: 'status', title: '映射状态', templet: function (d) {
                                return d.status == 0 ? '未映射' : '已映射';
                            }

                        }, {
                            fixed: 'right', title: '操作', width: 150, toolbar: "#table-edit"
                        }
                    ]]
                    , limit: 20
                    , page: true
                    , limits: [20, 30, 40, 50]
                    , done: function () {   //返回数据执行回调函数
                        layer.close(index);    //返回数据关闭loading
                    }
                });
                return false;

            });

            table.render({
                elem: '#EDtable'
                , url: '/Goods/mapping/lists'
                , cols: [[
                    {checkbox: true}
                    , {field: '', title: '序号', width: 50, type: 'numbers'}
                    , {
                        field: 'platforms', title: '平台', templet: function (d) {
                            return d.platforms.name_CN;
                        }
                    }
                    , {
                        field: 'shop', title: '店铺', templet: function (d) {
                            return d.shop.shop_name;
                        }
                    }
                    , {
                        field: 'itemURL', title: '商品管理番号', templet: function (d) {
                            return d.itemURL ? d.itemURL : '--';
                        }
                    }
                    , {
                        field: 'item_number', title: '商品番号', templet: function (d) {
                            return d.item_number ? d.item_number : '--';
                        }
                    }
                    , {
                        field: 'seller_sku', title: 'SellerSKU', templet: function (d) {
                            return d.seller_sku ? d.seller_sku : '--';
                        }
                    }
                    , {
                        field: 'asin', title: 'ASIN', templet: function (d) {
                            return d.asin ? d.asin : '--';
                        }
                    }
                    , {
                        field: 'upc', title: 'UPC', templet: function (d) {
                            return d.upc ? d.upc : '--';
                        }
                    }
                    , {
                        field: 'sku', width: 120, title: '自定义SKU', templet: function (d) {
                            var str = '';
                            if (Array.isArray(d.mapping_goods)) {
                                (d.mapping_goods).forEach(function (val, i) {
                                    str += val['goods_sku'] + ',';
                                })
                                str = str.substring(0, str.length - 1);
                            }
                            return str;
                        }
                    }
                    , {
                        field: 'goods_number', title: '映射数量', templet: function (d) {
                            return d.goods_number ? d.goods_number : '';
                        }
                    }
                    , {
                        field: 'status', title: '映射状态', templet: function (d) {
                            return d.status == 0 ? '未映射' : '已映射';
                        }

                    }, {
                        fixed: 'right', title: '操作', width: 150, toolbar: "#table-edit"
                    }
                ]]
                , limit: 20
                , page: true
                , limits: [20, 30, 40, 50]
                , done: function () {   //返回数据执行回调函数
                    layer.close(index);    //返回数据关闭loading
                }
            });
            window.mappingNumber = function (obj, LAY_TABLE_INDEX) {
                tableData[LAY_TABLE_INDEX].goods_number = tableData[LAY_TABLE_INDEX]['goods_number'] = parseInt($(obj).val());
            };
        });


        $("body").bind("keydown", function (event) {
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location = location;
            }
        })

    </script>
@endsection