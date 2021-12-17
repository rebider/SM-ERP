@extends('layouts.new_main')
@section('head')
    <style>
        .lay-select .layui-form-select {
            width: 300px;
        }
    </style>
@endsection
@section('content')
    <form class="layui-form" action="">
        <input type="hidden" name="_token" value="{{csrf_token()}}">
        <div class="produpage layui-form lay-select">
            <h3 style="color: #1E9FFF;font-weight: 700;">订单信息</h3>
            <table class="layui-table" lay-skin="nob">
                <colgroup>
                    <col width="130">
                    <col>
                </colgroup>
                <tbody>
                <tr>
                    <td><b><i style="color: red;">*</i>目的仓库：</b></td>
                    <td>
                        <select name="warehouse_id" id="warehouse_id" lay-filter="warehouse" lay-verify="required"
                                style="width: 40%">
                            <option value="">请选择</option>
                            @foreach($warehouse as $re)
                                <option value="{{ $re['id'] }}">{{ $re['warehouse_name'] }}</option>
                            @endforeach
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><b>采购备注：</b></td>
                    <td><input type="text" name="Dec" lay-verify="Dec" autocomplete="off" placeholder="请输入备注信息"
                               class="layui-input">
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="produpage layui-form textpage">
            <h3 style="float: left;">产品信息</h3>
            <div class="layui-input-block" style="width: 20%;margin-left: 5px;float: left;height: 46px;" id="checkSKU">
                <input type="text" name="" id="sku" class="layui-input" style="position:absolute;z-index:1;width:100%;"
                       lay-verify="" value="" onkeyup="search()" autocomplete="off">
                <select type="text" id="hc_select" lay-filter="hc_select" autocomplete="off" placeholder="sku"
                        lay-verify="" class="layui-select" lay-search>
                    @foreach($goods as $re)
                        <option value="{{ $re['sku'] }}">{{ $re['sku'] }}</option>
                    @endforeach
                </select>
                <button type="button" class="layui-btn layui-btn-sm"
                        style="float: right;position: relative;left: 100px;top: -35px;" id="addSKU">添加SKU
                </button>
            </div>
        </div>


        <div class="edn-row table_index" style="margin-left: 5px">
            <table class="" id="EDtable" lay-filter="EDtable"></table>
        </div>
        <div class="layui-elem-field layui-field-title">
            <div style="float: right;width: 35%;padding-top: 10px;">
                <span>总数量：</span><span id="total_amount"></span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <span>总采购金额：</span> <span id="total_price"></span>
            </div>
        </div>

        <div class="layui-elem-field layui-field-title" style="margin-top: 40px;">
            <div style="padding-top: 10px;text-align: center; width: 100%">
                <button type="button" class="layui-btn layui-btn-sm" lay-submit lay-filter="check">审核</button>
                <button type="button" style="border: 1px solid #dedede;background-color: #fff;color: #333;"
                        class="layui-btn layui-btn-sm" lay-submit lay-filter="save">确定</button>
                <button type="button" style="border: 1px solid #dedede;background-color: #fff;color: #333;"
                        class="layui-btn layui-btn-sm" id="back">取消</button>
            </div>
        </div>
    </form>

    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
    </script>
@endsection

@section('javascripts')

    <script>
        layui.config({base: '../../layui/lay/modules/'}).extend({formSelects: 'formSelects-v3'});
        layui.use(['layer', 'form', 'element', 'laydate', 'table', 'formSelects'], function () {
            var layer = layui.layer, form = layui.form, laydate = layui.laydate, table = layui.table,
                element = layui.element, formSelects = layui.formSelects;
            var tableData = [];
            table.render({
                elem: '#EDtable',
                // width: 1000,
                // height: 200,
                data: tableData,
                done: function (res, curr, count) {
                    layui.each($('.supplier'), function (index, item) {
                        var elem = $(item);
                        // elem.val(elem.data('value')).parents('div.layui-table-cell').css('overflow', 'visible');
                        elem.val(elem.data('value')).parents('div.layui-table-cell').css('display', 'block');
                    });
                    form.render();
                },
                cols: [[
                    {field: '', title: '序号', type: 'numbers'},
                    {field: 'sku', title: '自定义SKU'},
                    {field: 'in_transit_inventory', title: '在途库存'},
                    {field: 'available_in_stock', title: '可用库存'},
                    {
                        field: 'amount',
                        title: '采购数量',
                        align: 'center',
                        width: 200,
                        templet: function (d) {
                            return '<a href="javascript:void(0);" class="hide" lay-event="dec" id="dec' + d.id + '">' +
                                '<input type="text" placeholder=" -" style="width: 10%;" disabled>' +
                                '</a>' +
                                '<input lay-event="listenAmount" id="amount' + d.id + '" type="number" onkeyup="this.value=this.value.replace(/\\D/g,\'\')" value="' + d.amount + '" style="width: 50%;text-align: center;" onchange="changeAmount('+d.id+','+d.LAY_TABLE_INDEX+')">' +
                                '<a href="javascript:void(0)" class="hide" lay-event="sum" id="sum' + d.id + '">' +
                                '<input lay-event="sum" type="text" placeholder=" +" style="width: 10%;" disabled>' +
                                '</a>'
                        }
                    },
                    {
                        field: 'preferred_price',
                        title: '采购价(RMB)',
                        align: 'center',
                        width: 150,
                        templet: function (d) {
                            let price = d.preferred_price;
                            if (d.preferred_price == null) {
                                price = 0.00
                            }
                            return '<input lay-event="price" type="number" id="price' + d.id + '" value="' + price + '" min="0" class="layui-input" style="text-align: center;" lay-verify="required" autocomplete="off" onkeyup="num(this)" onchange="changePrice('+d.id+','+d.LAY_TABLE_INDEX+')">'
                        }

                    },
                    {
                        field: 'preferred_supplier_id',
                        title: '供应商',
                        align: 'center',
                        width: 200,
                        templet: function (d) {
                            return '<select style="display:block;width: 170px;" lay-event="supplier" class="supplier" id="supplier' + d.id + '" lay-verify="required" data-value="' + d.preferred_supplier_id + '" >' +
                                '<option value="">请选择</option>' +
                                '@foreach($suppliers as $re)' +
                                '<option value="{{ $re['id'] }}">{{ $re['name'] }}</option>' +
                                '@endforeach' +
                                '</select>';
                        }
                    },
                    {field: '', title: '操作', toolbar: '#barDemo'}

                ]]
                , limit: 10
                , page: true
                , limits: [10, 20, 30, 40, 50]
            });

            //监听仓库变化
            form.on('select(warehouse)', function (data) {
                var oldData = table.cache["EDtable"];
                if (oldData.length > 0) {
                    oldData = [];
                    table.reload('EDtable', {
                        data: oldData
                    });

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

            //添加商品信息
            $('#addSKU').click(function () {
                var oldData = table.cache["EDtable"];
                var sku = $('#sku').val();
                var warehouse_id = $('#warehouse_id').val();
                //判断商品是否存在
                if (oldData.length > 0) {
                    for (var i = 0; i < oldData.length; i++) {
                        if (sku === oldData[i]['sku']) {
                            layer.msg('该商品已经存在');
                            return false;
                        }
                    }
                }
                if (sku.length <= 0) {
                    layer.msg('请输入SKU', {icon: 5})
                } else if (warehouse_id.length <= 0) {
                    layer.msg('请先选择目的仓库', {icon: 5})
                } else {
                    $.ajax({
                        type: 'POST',
                        data: {
                            sku: sku,
                            warehouse_id: warehouse_id,
                            _token: "{{ csrf_token() }}"
                        },
                        url: "{{url('procurement/getGoodsBySku')}}",
                        success: function (data) {
                            if (data.status === 1) {
                                var oldData = table.cache["EDtable"];
                                var data1 = data.data;
                                oldData.push(data1);
                                table.reload('EDtable', {
                                    data: oldData
                                });
                                sum(oldData);
                            } else {
                                layer.msg(data.msg, {icon: 5});
                            }
                        },
                        error: function (e, x, t) {

                        }
                    })
                }
            });

            //监听商品数量变化
            window.changeAmount = function (id, LAY_TABLE_INDEX) {
                var oldData = table.cache["EDtable"];
                var amountHtml = '#amount' + id;

                oldData[LAY_TABLE_INDEX].amount = $(amountHtml).val();
                sum(oldData);
            };

            //监听价格变化
            window.changePrice = function (id, LAY_TABLE_INDEX) {
                var oldData = table.cache["EDtable"];
                var priceHtml = '#price' + id;

                oldData[LAY_TABLE_INDEX].preferred_price = $(priceHtml).val();
                sum(oldData);
            };

            //商品信息选择编辑规则
            table.on('tool(EDtable)', function (obj) {
                var data = obj.data;
                var oldData = table.cache["EDtable"];
                var amountHtml = '#amount' + data.id;
                var i = $(amountHtml).val();
                var supplierHtml = '#supplier' + data.id;

                if (obj.event === 'del') {                 //删除某一个商品
                    oldData.splice(obj.tr.data('index'), 1);
                    table.reload('EDtable', {
                        data: oldData
                    });
                    sum(oldData);
                } else if (obj.event === 'dec') {           //减少商品数量
                    i--;
                    if (i === 0) {
                        $(amountHtml).val('1')
                    } else {
                        $(amountHtml).val(i)
                    }
                    oldData[obj.tr.data('index')].amount = $(amountHtml).val();
                    sum(oldData);

                } else if (obj.event === 'sum') {           //增加商品数量
                    i++;
                    $(amountHtml).val(i);
                    oldData[obj.tr.data('index')].amount = $(amountHtml).val();
                    sum(oldData);

                } else if (obj.event === 'supplier') {      //监听供应商变化
                    $(supplierHtml).change(function () {
                        oldData[obj.tr.data('index')].preferred_supplier_id = $(supplierHtml).val();
                    });
                    sum(oldData);
                }
            });

            var sum = function (v) {
                var total_amount = 0;
                var total_price = 0;
                for (var i = 0; i < v.length; i++) {
                    var total = parseFloat(v[i]['preferred_price']) * parseInt(v[i]['amount']);
                    total_amount += parseInt(v[i]['amount']);
                    total_price += total;
                }
                $('#total_price').text(total_price.toFixed(2));
                $('#total_amount').text(total_amount)
            };

            //保存
            form.on('submit(save)', function (data) {
                var field = data.field; //获取提交的字段
                field['goods'] = table.cache["EDtable"];
                var re = /^[1-9]+[0-9]*]*$/;
                var reg = /^(-?\d+)(\.\d{1,2})?$/;
                for (var i = 0; i < field['goods'].length; i++){
                    if(!re.test(field['goods'][i].amount)){
                        layer.msg('采购数量必须为整数', {icon: 5});
                        i = 0;
                        return false;
                    }
                    if (!reg.test(field['goods'][i].preferred_price)){
                        layer.msg('采购价格仅为两位小数', {icon: 5});
                        i = 0;
                        return false;
                    }
                }

                field['total_amount'] = $('#total_amount').text();
                field['total_price'] = $('#total_price').text();
                field['status'] = 1;     //草稿状态
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                $.ajax({
                    type: 'POST',
                    url: '{{ url('procurement/create') }}',
                    data: field,
                    dataType: 'json',
                    success: function (data) {
                        if (data.status === 0) {
                            layer.msg(data.msg, {icon: 5});
                        } else {
                            layer.msg('添加成功', {icon: 1});
                            setTimeout(function () {
                                parent.layer.close(index); //再执行关闭
                                parent.layui.table.reload('EDtable'); //重载表格
                            }, 2000);
                        }
                    },
                    error: function (e, x, t) {

                    }
                });
                return false;
            });

            //审核
            form.on('submit(check)', function (data) {
                var field = data.field; //获取提交的字段
                field['goods'] = table.cache["EDtable"];
                var re = /^[1-9]+[0-9]*]*$/;
                var reg = /^(-?\d+)(\.\d{1,2})?$/;
                for (var i = 0; i < field['goods'].length; i++){
                    if(!re.test(field['goods'][i].amount)){
                        layer.msg('采购数量必须为整数', {icon: 5});
                        i = 0;
                        return false;
                    }
                    if (!reg.test(field['goods'][i].preferred_price)){
                        layer.msg('采购价格仅为两位小数', {icon: 5});
                        i = 0;
                        return false;
                    }
                }
                field['total_amount'] = $('#total_amount').text();
                field['total_price'] = $('#total_price').text();
                field['status'] = 2;     //审核状态
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                $.ajax({
                    type: 'POST',
                    url: '{{ url('procurement/create') }}',
                    data: field,
                    dataType: 'json',
                    success: function (data) {
                        if (data.status === 0) {
                            layer.msg(data.msg, {icon: 5});
                        } else {
                            layer.msg('审核成功', {icon: 1});
                            setTimeout(function () {
                                parent.layer.close(index); //再执行关闭
                                parent.layui.table.reload('EDtable'); //重载表格
                            }, 2000);
                        }
                    },
                    error: function (e, x, t) {

                    }
                });
                return false;
            })
        });

        //返回上一页
        $('#back').click(function () {
            var index = parent.layer.getFrameIndex(window.name);
            parent.layer.close(index);//关闭当前页
        });

        $("body").bind("keydown", function (event) {
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location = location;
            }
        });

        function num(obj){
            obj.value = obj.value.replace(/[^\d.]/g,""); //清除"数字"和"."以外的字符
            obj.value = obj.value.replace(/^\./g,""); //验证第一个字符是数字
            // obj.value = obj.value.replace(/\.{2,}/g,"."); //只保留第一个, 清除多余的
            obj.value = obj.value.replace(".","$#$").replace(/\./g,"").replace("$#$",".");
            obj.value = obj.value.replace(/^(\-)*(\d+)\.(\d\d).*$/,'$1$2.$3'); //只能输入两个小数
        }

    </script>
@endsection