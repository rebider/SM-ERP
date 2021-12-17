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

            <div class="toolsBtn fclear">
                <div class="infm">
                </div>
                <div class="operate fr">
                    <button type="button" class="layui-btn layuiadmin-btn-order" id="exitInventory">导出</button>
                </div>
            </div>
            <div class="edn-row table_index">
                <table class="" id="EDtable" lay-filter="EDtable"></table>
            </div>
        </div>
    </div>
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
                    , url: '/inventory/inventoryIndexSearch'
                    , where: {data: info}
                    , cols: [[
                        {checkbox: true},
                        {field: '', title: '序号', width: 50, type: 'numbers'}

                        , {
                            field: 'warehouse',
                            title: '所在仓库',
                            templet: function (d) {
                                return d.warehouse_name;
                            }
                        }
                        , {
                            field: 'sku', title: '自定义SKU', templet: function (d) {
                                return d.sku;
                            }
                        }, {
                            field: 'goods_pictures', title: '产品主图', templet: function (d) {
                                if (d.goods_pictures) {
                                    return '<img onerror="this.src=\'/img/imgNotFound.jpg\'" src="{{url('showImage').'?path='}}'+d.goods_pictures+'" style="height:24px;width:24px;cursor:zoom-in;" alt=""/>';
                                }
                                return '';
                            },
                            event: 'check_img',
                        }, {
                            field: 'goods_name', title: '产品名称', templet: function (d) {
                                return d.goods_name;
                            }
                        }, {
                            field: 'category_name', title: '产品分类', templet: function (d) {
                                var category = '';
                                if (d.category1) {
                                    category = category+d.category1 + '>';
                                }
                                if (d.category2) {
                                    category = category+d.category2 + '>';
                                }
                                if (d.category3) {
                                    category = category+d.category3 + '>';
                                }
                                return category;
                            }
                        }
                        , {
                            field: 'purchase_inventory', title: '采购库存', templet: function (d) {
                                return d.purchase_inventory;
                            }
                        }
                        , {
                            field: 'in_transit_inventory', title: '在途库存', templet: function (d) {
                                return d.in_transit_inventory;
                            }
                        }
                        , {
                            field: 'drop_shipping', title: '待发货', templet: function (d) {
                                return d.drop_shipping;
                            }
                        }
                        , {
                            field: 'available_in_stock', title: '可用库存', templet: function (d) {
                                return d.available_in_stock ? d.available_in_stock : 0 ;
                            }
                        }, {
                            field: 'sell_inv', title: '可售库存', templet: function (d) {
                                var sell_inv = parseInt(d.available_in_stock) - parseInt(d.drop_shipping);
                                return sell_inv ? sell_inv : 0;
                            }
                        },
                        {
                            field: 'updated_at', title: '最后入库时间', templet: function (d) {
                                if (d.updated_at) {
                                    return d.updated_at
                                }
                                return d.created_at;
                            }
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
                , url: '/inventory/inventoryIndexSearch'
                , cols: [[
                    {checkbox: true},
                    {field: '', title: '序号', width: 50, type: 'numbers'}

                    , {
                        field: 'warehouse',
                        title: '所在仓库',
                        templet: function (d) {
                            return d.warehouse_name;
                        }
                    }
                    , {
                        field: 'sku', title: '自定义SKU', templet: function (d) {
                            return d.sku;
                        }
                    }, {
                        field: 'goods_pictures', title: '产品主图', templet: function (d) {
                            if (d.goods_pictures) {
                                return '<img onerror="this.src=\'/img/imgNotFound.jpg\'"  src="{{url('showImage').'?path='}}'+d.goods_pictures+'" style="height:24px;width:24px;cursor:zoom-in;" alt=""/>';
                            }
                            return '';
                        },
                        event: 'check_img',
                    }, {
                        field: 'goods_name', title: '产品名称', templet: function (d) {
                            return d.goods_name;
                        }
                    }, {
                        field: 'category_name', title: '产品分类', templet: function (d) {
                            var category = '';
                            if (d.category1) {
                                category = category+d.category1 + '>';
                            }
                            if (d.category2) {
                                category = category+d.category2 + '>';
                            }
                            if (d.category3) {
                                category = category+d.category3 + '>';
                            }
                            return category;
                        }
                    }
                    , {
                        field: 'purchase_inventory', title: '采购库存', templet: function (d) {
                            return d.purchase_inventory;
                        }
                    }
                    , {
                        field: 'in_transit_inventory', title: '在途库存', templet: function (d) {
                            return d.in_transit_inventory;
                        }
                    }
                    , {
                        field: 'drop_shipping', title: '待发货', templet: function (d) {
                            return d.drop_shipping;
                        }
                    }
                    , {
                        field: 'available_in_stock', title: '可用库存', templet: function (d) {
                            return d.available_in_stock ? d.available_in_stock : 0 ;
                        }
                    }, {
                        field: 'sell_inv', title: '可售库存', templet: function (d) {
                            var sell_inv = parseInt(d.available_in_stock) - parseInt(d.drop_shipping);
                            return sell_inv ? sell_inv : 0;
                        }
                    },
                    {
                        field: 'updated_at', title: '最后入库时间', templet: function (d) {
                            if (d.updated_at) {
                                return d.updated_at
                            }
                            return d.created_at;
                        }
                    }
                ]]
                , limit: 20
                , page: true
                , limits: [20, 30, 40, 50]
                , done: function () {   //返回数据执行回调函数
                    layer.close(index);    //返回数据关闭loading
                }
            });

            table.on('tool(EDtable)', function(obj){
                var data = obj.data;
                if(obj.event === 'check_img') {
                    check_img(data.goods_pictures);
                }
            });
            //下拉框联想-1
            form.on('select(hc_select)', function (data) {   //选择sku 赋值给input框
                $("#sku").val(data.value);
                $("#hc_select").next().find("dl").css({"display": "none"});
                form.render();
            });
            //下拉框联想-2
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
                var ids = '';

                table.on('checkbox(EDtable)', function (obj) {
                    var checkStatus = table.checkStatus('EDtable');
                    var id_array = new Array(); //仓库id
                    var data = checkStatus.data;

                    if (data.length > 0) {
                        for (var i = 0; i < data.length; i++) {
                            id_array.push(data[i].id);
                        }
                    }
                    ids = id_array.join(',');
                });

                //导出
                $('#exitInventory').click(function () {
                    if (ids.length === 0) {
                        layer.msg('请选择需要导出的记录！')
                    } else {
                        window.location.href = "/inventory/exportInventory?ids=" + ids;
                    }
                })
            });
        });

        $("body").bind("keydown", function (event) {
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location = location;
            }
        })
    </script>
@endsection