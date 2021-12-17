@extends('layouts/new_main')

@section('content')
    <form class="layui-form" action="">
        <div class="produpage layui-form widthin">
            <table class="layui-table" lay-skin="nob">
                <input type="hidden" name="supplier_id" value="{{ $suppliers->id }}">
                <tbody>
                <tr>
                    <td><i style="color: red;">*</i>供应商名称：</td>
                    <td>
                        <input type="text" id="name" name="name" value="{{ $suppliers->name }}" lay-verify="required">
                    </td>
                    <td><i style="color: red;">*</i>联系人：</td>
                    <td>
                        <input type="text" id="linkman" name="linkman" value="{{ $suppliers->linkman }}" lay-verify="required">
                    </td>
                </tr>
                <tr>
                    <td><i style="color: red;">*</i>联系方式：</td>
                    <td>
                        <input name="tel_no" id="tel_no" type="text" value="{{ $suppliers->tel_no }}" lay-verify="required">
                    </td>
                    <td>详细地址：</td>
                    <td>
                        <input name="address" id="address" value="{{ $suppliers->address }}" type="text">
                    </td>
                </tr>
                <tr>
                    <td>邮箱：</td>
                    <td>
                        <input name="email" id="email" value="{{ $suppliers->email }}" type="text">
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
                var field = data.field;
                //验证必填字段
                if (field['name'] === '' || field['linkman'] === '' || field['tel_no'] === ''){
                    layer.msg('请填写必填信息',{icon: 5});
                    return false;
                }

                //手机号验证
                var isPhone = /^([0-9]{3,4}-)?[0-9]{7,8}$/;//手机号码
                var isMob= /^0?1[3|4|5|8][0-9]\d{8}$/;// 座机格式
                if (!isMob.test(field['tel_no']) && !isPhone.test(field['tel_no'])){
                    layer.msg('联系方式格式错误',{icon: 5});
                    return false;
                }

                //如果有邮箱 验证邮箱
                var reg = /^([a-zA-Z]|[0-9])(\w|\-)+@[a-zA-Z0-9]+\.([a-zA-Z]{2,4})$/;
                if (field['email'] && !reg.test(field['email'])){
                    layer.msg('邮箱格式错误',{icon: 5});
                    return false;
                }

                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                $.ajax({
                    type: 'POST',
                    data: {
                        params: field,
                        _token: "{{ csrf_token() }}"
                    },
                    url: "{{ url('supplier/updateSupplier') }}",
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