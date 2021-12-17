@extends('layouts/new_main')

@section('content')
    <form class="layui-form" action="">
        <div class="produpage layui-form widthin">
            <table class="layui-table" lay-skin="nob">
                <tbody>
                <tr>
                    <td><i style="color: red">*</i>自定义SKU：</td>
                    <td>
                        <div class="layui-input-block" style="width: 91%;margin-left: 1px;float: left;height: 46px;"
                             id="checkSKU">
                            <input type="text" name="" id="sku" class="layui-input"
                                   style="position:absolute;z-index:1;width:100%;"
                                   lay-verify="" value="" onkeyup="search()" autocomplete="off">
                            <select type="text" id="hc_select" lay-filter="hc_select" autocomplete="off"
                                    placeholder="sku"
                                    lay-verify="" class="layui-select" lay-search>
                                @foreach($goods as $re)
                                    <option value="{{ $re['sku'] }}">{{ $re['sku'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{--<input type="text" id="Asku" >--}}
                    </td>
                    <td><i style="color: red">*</i>所在仓库：</td>
                    <td>
                        <select name="setting_warehouse_id" id="setting_warehouse_id">
                            <option value=""></option>
                            @foreach($warehouse as $re)
                                <option value="{{ $re['id'] }}">{{ $re['warehouse_name'] }}</option>
                            @endforeach
                        </select>
                    </td>

                </tr>
                <tr>
                    <td><i style="color: red">*</i>乐天平台上架比例：</td>
                    <td>
                        <div class="layui-input-block" style="width: 91%;margin-left: 1px;float: left;height: 46px;">
                            <input type="text" name="lotte" id="lotte" class="layui-input"
                                   style="position:absolute;z-index:1;width:100%;"
                                   autocomplete="off">
                        </div>

                    </td>
                    <td><i style="color: red">*</i>亚马逊平台上架比例：</td>
                    <td>
                        <div class="layui-input-block" style="width: 91%;margin-left: 1px;float: left;height: 46px;">
                            <input type="text" name="amazon" id="amazon" class="layui-input"
                                   style="position:absolute;z-index:1;width:100%;"
                                   autocomplete="off">
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="layui-elem-field layui-field-title" style="margin-top: 40px;">
                <div style="padding-top: 10px;text-align: center; width: 100%">
                    <button type="button" class="layui-btn layui-btn-sm" lay-submit lay-filter="save">确定</button>
                    <button type="button" style="border: 1px solid #dedede;background-color: #fff;color: #333;"
                            class="layui-btn layui-btn-sm" id="back">取消
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('javascripts')
    <script>
        //layui加载
        layui.config({base: '../../layui/lay/modules/'}).extend({formSelects: 'formSelects-v3'});
        layui.use(['layer', 'form', 'element', 'laydate', 'table', 'formSelects'], function () {
            var layer = layui.layer, form = layui.form, laydate = layui.laydate, table = layui.table,
                element = layui.element, formSelects = layui.formSelects;

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

            form.on('submit(save)', function (data) {
                var arr = {};
                var reg = /^[0-9]+([.]{1}[0-9]{1,2})?$/;
                arr['sku'] = $('#sku').val();
                arr['warehouse_id'] = $('#setting_warehouse_id').val();
                arr['lotte'] = $('#lotte').val();
                arr['amazon'] = $('#amazon').val();
                if (!arr['sku']) {
                    layer.msg('自定义SKU为必填项', {icon: 5});
                    return false;
                }

                if (!arr['warehouse_id']) {
                    layer.msg('所在仓库为必选项', {icon: 5});
                    return false;
                }

                if (!arr['lotte']) {
                    layer.msg('乐天平台上架比例为必填字段', {icon: 5});
                    return false;
                }

                if (!arr['amazon']) {
                    layer.msg('亚马逊平台上架比例为必填字段', {icon: 5});
                    return false;
                }


                if (!reg.test(arr['lotte']) || !reg.test(arr['amazon'])) {
                    layer.msg('请输入合法数字', {icon: 5});
                    return false;
                }

                if(parseInt(arr['lotte']) + parseInt(arr['amazon']) > 1){
                    layer.msg('分配比例之和不能大于1', {icon: 5});
                    return false;
                }

                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                $.ajax({
                    type: 'POST',
                    data: {
                        params: arr,
                        _token: "{{ csrf_token() }}"
                    },
                    url: "{{ url('inventory/addAllocation') }}",
                    success: function (data) {
                        if (data.status === 1) {
                            layer.msg(data.msg, {icon: 6});
                            setTimeout(function () {
                                parent.layer.close(index); //再执行关闭
                                parent.layui.table.reload('EDtable'); //重载表格
                            }, 2000);
                        }
                        if (data.status === 0) {
                            layer.msg(data.msg, {icon: 5});
                        }
                    },
                    error: function (e, x, t) {

                    }
                })
            });

            layui.use('table', function () {
                var table = layui.table;


            });
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