@extends('layouts.new_main')
@section('head')
    <style>
        .layui-form-label {
            width: 140px;
        }
        .layui-anim-upbit dd {
            text-align: left;
        }
    </style>
@endsection
@section('content')
    <form class="layui-form" action="" style="width: 800px">
        <input type="hidden" name="_token" value="{{csrf_token()}}">
        <input type="hidden" name="ids" id="ids" value="{{$ids}}">
        <div class="produpage layui-form widthin">
            <table class="layui-table" lay-skin="nob">
                <tbody>
                <tr>
                    <td><i style="color: red">*</i>目的仓库：</td>
                    <td>
                        <select name="warehouse_id" lay-filter="warehouse_id" lay-verify="required">
                            <option value="">请选择</option>
                            @foreach($warehouses as $re)
                                <option value="{{ $re['id'] }}">{{$re['warehouse_name']}}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><i style="color: red">*</i>预计到达日期：</td>
                    <td>
                        <input type="text" name="get_time" id="test1" style="width: 172px" lay-verify="required" autocomplete="off">
                    </td>
                </tr>
                <tr>
                    <td><i style="color: red">*</i>物流方式：</td>
                    <td>
                        <select id="logistics_id" name="logistics_id" lay-filter="logistics_id" lay-verify="required">
                            <option value="">请选择</option>
                            @foreach($logistics as $re)
                                <option value="{{ $re['id'] }}">{{ $re['logistic_name'] }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><i style="color: red">*</i>箱数：</td>
                    <td><input type="number" name="box_number" lay-verify="required"                                                                                       onkeyup="value=value.replace(/[^\d]/g,'')"
                                    onblur="value=value.replace(/[^\d]/g,'')"></td>
                </tr>
                <tr>
                    <td>跟踪号：</td>
                    <td><input type="text" name="tracking_no"></td>
                    <td>运费：</td>
                    <td><input type="text" name="freight"></td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="edn-row table_index" style="margin-left: 5px">
            <table class="" id="EDtable" lay-filter="EDtable"></table>
        </div>
        <div style="padding-top: 18px;text-align: center; width: 100%">
            <button type="button"
                    class="layui-btn layui-btn-blue" lay-submit lay-filter="save">确定
            </button>
            <button type="button" style="border: 1px solid #dedede;background-color: #fff;color: #333;"
                    class="layui-btn" id="back">取消
            </button>
        </div>
    </form>

    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn layui-btn-xs" lay-event="binning">分箱</a>
        <a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="tanks">合箱</a>
    </script>
@endsection

@section('javascripts')

    <script>
        layui.use(['layer', 'form', 'element', 'table', 'laydate'], function () {
            var layer = layui.layer, form = layui.form, laydate = layui.laydate, table = layui.table,
                element = layui.element, formSelects = layui.formSelects;
            var tableData = [];
            $(function () {
                $.ajax({
                    type: 'POST',
                    data: {
                        ids: $('#ids').val(),
                        _token: "{{ csrf_token() }}"
                    },
                    url: '/procurement/getProcurementGoods',
                    success: function (data) {
                        tableData = data.data;
                        table.render({
                            elem: '#EDtable',
                            data: tableData,
                            cols: [[
                                {field: '', title: '序号', type: 'numbers'},
                                {field: 'sku', title: '自定义SKU'},
                                {
                                    field: 'amount',
                                    title: '装箱数量',
                                    align: 'center',
                                    width: 150,
                                    templet: function (d) {
                                        return '<input lay-event="amount" type="text" id="amount' + d.LAY_TABLE_INDEX + '" value="' + d.amount + '" class="layui-input ' + d.sku + '" style="text-align: center;background: #f8f8f8" lay-verify="required" autocomplete="off" onchange="changeAmount('+d.LAY_TABLE_INDEX+')" readonly>'
                                    }

                                },
                                {
                                    field: 'box_no',
                                    title: '箱号',
                                    align: 'center',
                                    width: 200,
                                    templet: function (d) {
                                        return '<a href="javascript:void(0);" class="hide" lay-event="dec" id="dec' + d.LAY_TABLE_INDEX + '">' +
                                            '<input type="text" placeholder=" -" style="width: 10%;" disabled>' +
                                            '</a>' +
                                            '<input  lay-event="box_no" id="box_no' + d.LAY_TABLE_INDEX + '" type="text" value="' + d.box_no + '" style="width: 50%;text-align: center;" onchange="changeBoxNo('+d.LAY_TABLE_INDEX+')">' +
                                            '<a href="javascript:void(0)" class="hide" lay-event="sum" id="sum' + d.LAY_TABLE_INDEX + '">' +
                                            '<input lay-event="sum" type="text" placeholder=" +" style="width: 10%;" disabled>' +
                                            '</a>'
                                    }
                                },
                                {field: '', title: '操作', toolbar: '#barDemo'}

                            ]]
                            , limit: 10
                            , page: true
                            , limits: [10, 20, 30, 40, 50]
                            , done: function () {   //返回数据执行回调函数
                                var count1 = 0;
                                var data1 = table.cache["EDtable"];
                                for (var i = 0; i < data1.length; i++) {
                                    var sku = data1[i].sku;
                                    count1 = 0;
                                    for (var j = 0; j < data1.length; j++) {
                                        if (sku === data1[j].sku) {
                                            count1++;
                                            if (count1 > 1) {
                                                var sku1 = '.' + data1[j].sku;
                                                $(sku1).removeAttr('readOnly');
                                                $(sku1).css({'background': '#fff'});
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    },
                    error: function (e, x, t) {

                    }
                })
            });

            // 监听装箱数量
            window.changeAmount = function (LAY_TABLE_INDEX) {
                var oldData = table.cache["EDtable"];
                var amountHtml = '#amount' + LAY_TABLE_INDEX;

                oldData[LAY_TABLE_INDEX].amount = $(amountHtml).val();
            };

            // 监听箱号
            window.changeBoxNo = function (LAY_TABLE_INDEX) {
                var oldData = table.cache["EDtable"];
                var boxNoHtml = '#box_no' + LAY_TABLE_INDEX;

                oldData[LAY_TABLE_INDEX].box_no = $(boxNoHtml).val();
            };

            //表单规则
            table.on('tool(EDtable)', function (obj) {
                var data = obj.data;
                var oldData = table.cache["EDtable"];
                var boxNoHtml = '#box_no' + oldData[obj.tr.data('index')].LAY_TABLE_INDEX;
                var i = $(boxNoHtml).val();

                //在输入框值变化触发，不用等到鼠标点击别的地方
                // $(boxNoHtml).on('input propertychange', function () {
                //     oldData[obj.tr.data('index')].box_no = $(boxNoHtml).val();
                //     // layer.msg(oldData[obj.tr.data('index')].box_no)
                // });

                // $(amountHtml).on('input propertychange', function () {
                //     oldData[obj.tr.data('index')].amount = $(amountHtml).val();
                //     // layer.msg(oldData[obj.tr.data('index')].amount)
                // });

                if (obj.event === 'dec') {           //减少商品数量
                    i--;
                    if (i === 0) {
                        $(boxNoHtml).val('1')
                    } else {
                        $(boxNoHtml).val(i)
                    }
                    oldData[obj.tr.data('index')].box_no = $(boxNoHtml).val();

                } else if (obj.event === 'sum') {          //增加商品数量
                    i++;
                    $(boxNoHtml).val(i);
                    oldData[obj.tr.data('index')].box_no = $(boxNoHtml).val();
                } else if (obj.event === 'binning') {      //分箱
                    let data1 = {};
                    data1.sku = data.sku;
                    data1.amount = 0;
                    data1.box_no = 1;
                    oldData.splice(oldData[obj.tr.data('index')].LAY_TABLE_INDEX + 1, 0, data1);   //运用splice函数插入指定位置
                    table.reload('EDtable', {
                        data: oldData
                    });
                } else if (obj.event === 'tanks') {       //合箱
                    //判断sku个数。相同的只有一个的时候不能合箱
                    var sku = oldData[obj.tr.data('index')].sku;
                    var count = 0;
                    var totalAmount = 0;
                    for (var j = 0; j < oldData.length; j++) {
                        if (sku === oldData[j].sku) {
                            count++;
                            totalAmount += parseInt(oldData[j].amount)
                        }
                    }
                    if (count === 1) { //相同sku只有一个
                        return false;
                    } else if (count === 2) {  //相同sku有两个
                        var sku1 = oldData[obj.tr.data('index')].sku;
                        oldData.splice(obj.tr.data('index'), 1);
                        for (var z = 0; z < oldData.length; z++) {
                            if (sku1 === oldData[z].sku) {
                                oldData[z].amount = totalAmount
                            }
                        }
                    } else { //相同sku2个以上
                        oldData.splice(obj.tr.data('index'), 1);
                    }
                    table.reload('EDtable', {
                        data: oldData
                    });
                }
            });

            //监听提交
            form.on('submit(save)', function (data) {
                var field = data.field; //获取提交的字段
                var arr = table.cache["EDtable"];
                var re = /^[1-9]+[0-9]*]*$/;

                for (var i = 0; i < arr.length; i++) {  //验证数据
                    if (parseInt(arr[i].box_no) > parseInt(field.box_number)) {
                        layer.msg('请输入正确的箱号！', {icon: 5});
                        i = 0;
                        return false;
                    }
                    if (!re.test(arr[i].box_no) || !re.test(arr[i].amount)) {
                        layer.msg('装箱数量、箱号必须为整数', {icon: 5});
                        i = 0;
                        return false;
                    }
                }
                if (!re.test(field.box_number)) {
                    layer.msg('箱数格式错误！', {icon: 5});
                    return false;
                }
                field['box'] = table.cache["EDtable"];
                var index = parent.layer.getFrameIndex(window.name);  //先得到当前iframe层的索引
                $.ajax({
                    type: 'POST',
                    url: '{{ url('procurement/createProcurementOrder') }}',
                    data: field,
                    dataType: 'json',
                    success: function (data) {
                        if (data.status === 1) {
                            var msg = data.msg;
                            var msgs = '';
                            $.each(msg, function (k, v) {
                                msgs += v[0] + "<br/>";
                            });
                            layer.msg(msgs, {icon: 5});
                        } else if (data.status === 0) {
                            layer.msg(data.msg, {icon: 5})
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

            laydate.render({
                elem: '#test1'
            });

            form.on('select(warehouse_id)', function (e) {
                let warehouse = e.value;
                $.get('/procurement/getLogistics?id=' + warehouse, {}, function (res) {
                    if (res.code != 0) {
                        layer.msg('请求失败', {icon: 2});
                        return false;
                    }
                    let optionString = "<option value=''>请选择</option>";
                    $.each(res.data, function(k,v) {
                        optionString += "<option value='"+ v.id +"'>"+ v.logistic_name +"</option>";
                    });
                    $('#logistics_id').empty().append(optionString);
                    form.render('select');
                })
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
        })

    </script>
@endsection