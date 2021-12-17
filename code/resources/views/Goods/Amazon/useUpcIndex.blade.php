@extends('layouts/new_main')

@section('content')
    <form class="layui-form" action="">
        <input type="hidden" name="upc_id" value="{{ $id }}">
        <table class="layui-table" lay-skin="nob">
            <tbody>
            <tr>
                <td>*SellerSKU:</td>
                <td>
                    <input type="text" id="SellerSKU" name="seller_sku" value="" lay-verify="required">
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
    </form>
@endsection
@section('javascripts')
    <script>
        //layui加载
        layui.config({base: '../../../layui/lay/modules/'}).extend({formSelects: 'formSelects-v3'});
        layui.use(['layer', 'form', 'element', 'laydate', 'table', 'formSelects'], function () {
            var layer = layui.layer, form = layui.form, laydate = layui.laydate, table = layui.table,
                element = layui.element, formSelects = layui.formSelects;

            //保存
            form.on('submit(save)', function (data) {
                var field = data.field; //获取提交的字段
                if (field['seller_sku'] === '') {
                    layer.msg('SellerSKU不能为空', {icon: 5});
                    return false
                }
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                $.ajax({
                    type: "POST",
                    data: {
                        data: field,
                        _token : "{{ csrf_token() }}"
                    },
                    url: "/Goods/amazon/useUpc",
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