@extends('layouts/new_main')

@section('content')
    <div class="kbmodel_full">
        <div class="content-wrapper">
            <form class="layui-form multiSearch">
                <ul class="flexSearch flexquar fclear">
                    <li>
                        <div class="inputTxt">所在仓库：</div>
                        <div class="multLable" style="width: 60%;">
                            <select name="warehouse_id" class="voin_select">
                                <option value="">请选择</option>
                                @foreach($warehouse as $re)
                                    <option value="{{ $re['id'] }}">{{ $re['warehouse_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </li>
                    <li>
                        <div class="inputTxt">自定义SKU：</div>
                        <div class="inputBlock">
                            <div class="layui-input-block" style="width: 80%;margin-left: 1px;height: 46px;" id="checkSKU">
                                <input type="text" name="sku" id="sku" class="layui-input" style="position:absolute;z-index:1;width:100%;"
                                       lay-verify="" value="" onkeyup="search()" autocomplete="off">
                                <select type="text" id="hc_select" lay-filter="hc_select" autocomplete="off" placeholder="sku"
                                        lay-verify="" class="layui-select" lay-search>
                                    @foreach($goods as $re)
                                        <option value="{{ $re['sku'] }}">{{ $re['sku'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="inputTxt">产品名称：</div>
                        <div class="inputBlock">
                            <input type="text" name="goods_name" placeholder=" " autocomplete="off"
                                   class="layui-input">
                        </div>
                    </li>
                    <li style="margin-right: 10000px">
                        <div class="groupBtns">
                            <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="searBtn">搜索</button>
                            <button class="layui-btn layui-btn-primary" lay-submit="" lay-filter="reset" >重置</button>
                        </div>
                    </li>
                </ul>
            </form>
            @include('common.validate')
            <div class="toolsBtn fclear">
                <div class="infm">
                </div>
                <div class="operate fr">
                    <button type="button" class="layui-btn layuiadmin-btn-order" id="add">添加</button>
                    <button type="button" class="layui-btn layuiadmin-btn-order" id="import">导入</button>
                </div>
            </div>
            <div class="edn-row table_index">
                <table class="" id="EDtable" lay-filter="EDtable"></table>
            </div>
        </div>
    </div>

    <script type="text/html" id="barDemo">
        {{--<a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>--}}
        <a class="layui-table-link" lay-event="edit">编辑</a>
    </script>
@endsection

@section('javascripts')
    <script>
        //layui加载
        layui.config({base: '../../layui/lay/modules/'}).extend({formSelects: 'formSelects-v3'});
        layui.use(['layer', 'form', 'element', 'laydate', 'table', 'formSelects'], function () {
            var layer = layui.layer, form = layui.form, laydate = layui.laydate, table = layui.table,
                element = layui.element, formSelects = layui.formSelects;
            var index = layer.msg('数据请求中', {icon: 16});

            laydate.render({
                elem: '#EDdate'
            });
            laydate.render({
                elem: '#EDdate1'
            });

            form.on('submit(reset)', function (data) {
                window.location.reload(true);
                return false;
            });

            form.on('submit(searBtn)', function (data) {
                var index = layer.msg('数据请求中', {icon: 16});
                var info = data.field;
                table.render({
                    elem: '#EDtable'
                    , url: '/inventory/inventoryAllocationSearch'
                    , where: {data: info}
                    , cols: [[
//                        {checkbox: true},
                        {field: '', title: '序号', width: 50, type: 'numbers'}
                        , {
                            field: 'sku', title: '自定义SKU', templet: function (d) {
                                return d.sku;
                            }
                        }, {
                            field: 'warehouse',
                            title: '所在仓库',
                            templet: function (d) {
                                return d.warehouse_name;
                            }
                        }, {
                            field: 'available_in_stock', title: '可售库存', templet: function (d) {
                                return d.available_in_stock ? d.available_in_stock : 0 ;
                            }
                        }, {
                            field: 'goods_name', title: '产品名称', templet: function (d) {
                                return d.goods_name;
                            }
                        }, {
                            field: 'goods_name', title: '产品分类', templet: function (d) {
                                return d.category1 + '>' + d.category2 + '>' + d.category3;
                            }
                        }
                        , {
                            field: 'lotte', title: '乐天平台上架比例', templet: function (d) {
                                if (d.lotte){
                                    return d.lotte;
                                }
                                return '';
                            }
                        }, {
                            field: 'amazon', title: '亚马逊平台上架比例', templet: function (d) {
                                if (d.amazon){
                                    return d.amazon;
                                }
                                return '';
                            }
                        },
                        {field: '', title: '操作', toolbar: '#barDemo'}
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
                , url: '/inventory/inventoryAllocationSearch'
                , cols: [[
//                    {checkbox: true},
                    {field: '', title: '序号', width: 50, type: 'numbers'}
                    , {
                        field: 'sku', title: '自定义SKU', templet: function (d) {
                            return d.sku;
                        }
                    }, {
                        field: 'warehouse',
                        title: '所在仓库',
                        templet: function (d) {
                            return d.warehouse_name;
                        }
                    }, {
                        field: 'available_in_stock', title: '可售库存', templet: function (d) {
                            return d.available_in_stock ? d.available_in_stock : 0 ;
                        }
                    }, {
                        field: 'goods_name', title: '产品名称', templet: function (d) {
                            return d.goods_name;
                        }
                    }, {
                        field: 'goods_name', title: '产品分类', templet: function (d) {
                            return d.category1 + '>' + d.category2 + '>' + d.category3;
                        }
                    }
                    , {
                        field: 'lotte', title: '乐天平台上架比例', templet: function (d) {
                            if (d.lotte){
                                return d.lotte;
                            }
                            return '';
                        }
                    }, {
                        field: 'amazon', title: '亚马逊平台上架比例', templet: function (d) {
                            if (d.amazon){
                                return d.amazon;
                            }
                            return '';
                        }
                    },
                    {field: '', title: '操作', toolbar: '#barDemo'}
                ]]
                , limit: 20
                , page: true
                , limits: [20, 30, 40, 50]
                , done: function () {   //返回数据执行回调函数
                    layer.close(index);    //返回数据关闭loading
                }
            });

            //添加商品下拉框联想-1
            form.on('select(hc_select)', function (data) {   //选择sku 赋值给input框
                $("#sku").val(data.value);
                $("#hc_select").next().find("dl").css({"display": "none"});
                form.render();
            });
            //添加商品下拉框联想-2
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

            layui.use('table', function () {
                var table = layui.table;

                table.on('tool(EDtable)', function (obj) {
                    var data = obj.data;
                    if (obj.event === 'edit'){
                        layer.open({
                            type: 2,
                            title: '编辑库存分配',
                            fix: false,
                            maxmin: true,
                            resize: true,
                            shadeClose: true,
                            area: ['800px', '350px'],
                            content: ['{{ url('inventory/editAllocationIndex') }}' + '/' + data.id,'no'],
                            end: function end() {

                            }
                        })
                    }
                })
            });

            //新增
            $('#add').click(function () {
                layer.open({
                    type: 2,
                    title: '添加库存分配',
                    fix: false,
                    maxmin: false,
                    resize: true,
                    shadeClose: true,
                    area: ['800px', '350px'],
                    content: ['{{ url('inventory/addAllocationIndex') }}','no'],
                    end: function end() {

                    }
                })
            });

            //导入
            $('#import').click(function () {
                layer.open({
                    type: 2,
                    title: '导入库存分配',
                    fix: false,
                    resize: true,
                    shadeClose: true,
                    area: ['550px', '300px'],
                    content: ['{{ url('inventory/importAllocationIndex') }}','no'],  //no 不要滚动条
                    end: function end() {

                    }
                })
            })
        });


        $("body").bind("keydown", function (event) {
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location = location;
            }
        })

    </script>
@endsection