@extends('layouts/new_main')

@section('content')
    <div class="kbmodel_full">
        <div class="content-wrapper">
            <form class="layui-form multiSearch">
                <ul class="flexSearch flexquar fclear">
                    <li style="width: 100%;">
                        <div class="inputTxt">使用状态：</div>
                        <div class="inputBlock">
                            <div class="multLable">
                                <input type="radio" name="status" value="" title="全部" checked>
                                <input type="radio" name="status" value="1" title="未使用">
                                <input type="radio" name="status" value="2" title="已使用">
                            </div>
                        </div>
                    </li>
                    <li>
                        <div class="inputTxt">UPC：</div>
                        <div class="inputBlock">
                            <div class="layui-input-block" style="width: 80%;margin-left: 1px;height: 46px;" id="checkSKU">
                                <input type="text" name="upc" id="upc" class="layui-input" style="position:absolute;z-index:1;width:100%;"
                                       lay-verify="" value="" onkeyup="search()" autocomplete="off">
                                <select type="text" id="hc_select" lay-filter="hc_select" autocomplete="off" placeholder="upc"
                                        lay-verify="" class="layui-select" lay-search>
                                    @foreach($upc as $re)
                                        <option value="{{ $re['upc'] }}">{{ $re['upc'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </li>
                    <div class="layui-form-item" style="margin-left: 105px">
                        <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="searBtn" style="width: 60px;">搜索</button>
                        <button class="layui-btn layui-btn-primary" lay-submit="" lay-filter="reset" style="width: 60px;">重置</button>
                    </div>
                </ul>
            </form>
            <div class="toolsBtn fclear">
                <div class="infm">
                </div>
                <div class="operate fr">
                    <button type="button" class="layui-btn layuiadmin-btn-order" id="import">导入UPC</button>
                </div>
            </div>
            <div class="edn-row table_index">
                <table class="" id="EDtable" lay-filter="EDtable"></table>
            </div>
        </div>
    </div>

    <script type="text/html" id="barDemo">
        @{{#  if(d.status === 1) { }}
        <a class="layui-table-link" lay-event="useUPC">使用</a>
        {{--<a class="layui-btn layui-btn-xs" lay-event="useUPC">使用</a>--}}
        @{{# } else { }}
        <a class="layui-btn layui-btn-xs" lay-event="">-</a>
        @{{#  } }}
    </script>
@endsection

@section('javascripts')
    <script>
        //layui加载
        layui.config({base: '../../../layui/lay/modules/'}).extend({formSelects: 'formSelects-v3'});
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
                    , url: '/Goods/amazon/upcSearch'
                    , where: {data: info}
                    , cols: [[
//                        {checkbox: true},
                        {field: '', title: '序号', width: 50, type: 'numbers'}
                        , {
                            field: 'upc', title: 'UPC', templet: function (d) {
                                return d.upc;
                            }
                        }, {
                            field: 'seller_sku', title: 'SellerSKU', templet: function (d) {
                                if (d.seller_sku){
                                    return d.seller_sku;
                                }
                                return '--';
                            }
                        }, {
                            field: 'status', title: '使用状态', templet: function (d) {
                                switch (d.status){
                                    case 1:
                                        return '未使用';
                                        break;
                                    case 2:
                                        return '已使用';
                                        break;
                                }
                            }
                        }, {
                            field: 'updated_at', title: '使用时间', templet: function (d) {
                                if (d.status === 1){
                                    return '--'
                                }
                                return d.updated_at;
                            }
                        }, {
                            field: 'created_at', title: '导入时间', templet: function (d) {
                                return d.created_at;
                            }
                        }
                        , {
                            field: 'user_name', title: '导入人', templet: function (d) {
                                return d.users.username;
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
                , url: '/Goods/amazon/upcSearch'
                , cols: [[
//                    {checkbox: true},
                    {field: '', title: '序号', width: 50, type: 'numbers'}
                    , {
                        field: 'upc', title: 'UPC', templet: function (d) {
                            return d.upc;
                        }
                    }, {
                        field: 'seller_sku', title: 'SellerSKU', templet: function (d) {
                            if (d.seller_sku){
                                return d.seller_sku;
                            }
                            return '--';
                        }
                    }, {
                        field: 'status', title: '使用状态', templet: function (d) {
                            switch (d.status){
                                case 1:
                                    return '未使用';
                                    break;
                                case 2:
                                    return '已使用';
                                    break;
                            }
                        }
                    }, {
                        field: 'updated_at', title: '使用时间', templet: function (d) {
                            if (d.status === 1){
                                return '--'
                            }
                            return d.updated_at;
                        }
                    }, {
                        field: 'created_at', title: '导入时间', templet: function (d) {
                            return d.created_at;
                        }
                    }
                    , {
                        field: 'user_name', title: '导入人', templet: function (d) {
                            return d.users.username;
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
                $("#upc").val(data.value);
                $("#hc_select").next().find("dl").css({"display": "none"});
                form.render();
            });
            //添加商品下拉框联想-2
            window.search = function () {
                var value = $("#upc").val();
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
                    if (obj.event === 'useUPC'){   //使用upc
                        layer.open({
                            type: 2,
                            title: '使用UPC',
                            fix: false,
                            maxmin: false,
                            resize: true,
                            shadeClose: true,
                            area: ['350px', '200px'],
                            content: ['{{ url('Goods/amazon/useUpcIndex') }}' + '/' + data.id,'no'],
                            end: function end() {

                            }
                        })
                    }
                })
            });

            //导入
            $('#import').click(function () {
                layer.open({
                    type: 2,
                    title: '导入UPC',
                    fix: false,
                    resize: true,
                    shadeClose: true,
                    area: ['550px', '300px'],
                    content: ['{{ url('Goods/amazon/upcImportIndex') }}','no'],  //no 不要滚动条
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