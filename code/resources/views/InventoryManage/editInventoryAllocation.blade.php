@extends('layouts/new_main')

@section('content')
    <form class="layui-form" action="">
        <input type="hidden" name="id" id="id" value="{{ $allocation['id'] }}">
        <div class="produpage layui-form widthin">
            <table class="layui-table" lay-skin="nob">
                <tbody>
                <tr>
                    <td><i style="color: red">*</i>自定义SKU：</td>
                    <td>
                        <input type="text" id="sku" value="{{ $allocation['goods']['sku'] }}"  disabled style="background:#EEEEEE">
                    </td>
                    <td><i style="color: red">*</i>所在仓库：</td>
                    <td>
                        <input type="text" id="warehouse" value="{{ $allocation['warehouse']['warehouse_name'] }}"  disabled style="background: #EEEEEE">
                    </td>

                </tr>
                <tr>
                    <td><i style="color: red">*</i>乐天平台上架比例：</td>
                    <td>
                        <div class="layui-input-block" style="width: 91%;margin-left: 1px;float: left;height: 46px;">
                            <input type="text" name="lotte" id="lotte" class="layui-input"
                                   style="position:absolute;z-index:1;width:100%;" value="{{ $allocation['lotte'] }}"
                                   autocomplete="off">
                        </div>
                    </td>
                    <td><i style="color: red">*</i>亚马逊平台上架比例：</td>
                    <td>
                        <div class="layui-input-block" style="width: 91%;margin-left: 1px;float: left;height: 46px;">
                            <input type="text" name="amazon" id="amazon" class="layui-input"
                                   style="position:absolute;z-index:1;width:100%;" value="{{ $allocation['amazon'] }}"
                                   autocomplete="off">
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="layui-elem-field layui-field-title" style="margin-top: 40px;">
                <div style="padding-top: 10px;text-align: center; width: 100%">
                    <button type="button" class="layui-btn layui-btn-sm" lay-submit lay-filter="save">保存</button>
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

            form.on('submit(save)', function (data) {
                var arr = {};
                var reg = /^[0-9]+([.]{1}[0-9]{1,2})?$/;
                arr['id'] = $('#id').val();
                arr['lotte'] = $('#lotte').val();
                arr['amazon'] = $('#amazon').val();
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
                    url: "{{ url('inventory/editAllocation') }}",
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