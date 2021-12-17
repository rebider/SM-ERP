@extends('layouts/new_main')
@section ('head')
    <style>
        .form-control {
            /*margin-left: 15px;*/
            /*display: block;*/
            /*width: 100%;*/
            margin-top: 2px;
            height: 34px;
            display: inline-block;
            padding: 6px 12px;
            font-size: 14px;
            line-height: 1.42857143;
            color: #555;
            background-color: #fff;
            background-image: none;
            border: 1px solid #ccc;
            border-radius: 4px;
            -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
            box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
            -webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
            -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
            transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        }
    </style>
@endsection
@section('content')
    <form class="layui-form" action="">
        <input type="hidden" name="_token" id="token" value="{{ csrf_token() }}">
        <div style="text-align: center;margin-top: 50px;">
            <span>xlsx文件：</span>
            <input type="file" class="form-control" id="up_pci"
                   accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                   placeholder="未选择任何文件" name="up_pci">
        </div>
        <div style="text-align: center;margin-top: 50px;">
            <button type="button" class="layui-btn layui-btn-blue" lay-submit lay-filter="save">上传</button>
            <input type="button" class="layui-btn" value="下载模板" onclick="downloadFile()">
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

            //保存
            form.on('submit(save)', function (data) {
                var field = data.field; //获取提交的字段
                if (field['up_pci'] == ''){
                    layer.msg('请选择需要上传的模版附件！',{icon: 5});
                    return false
                }
                var formData = new FormData();
                formData.append("up_pci", document.getElementById("up_pci").files[0]);
                formData.append("_token", document.getElementById("token").value);
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                $.ajax({
                    type:"POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    url:"{{ url('/Goods/amazon/upcImport') }}",
                    success:function (data) {
                        if (data.status === 1) {
                            layer.msg(data.msg, {icon: 6});
                            setTimeout(function () {
                                parent.layer.close(index); //再执行关闭
                                parent.layui.table.reload('EDtable'); //重载表格
                            }, 3000);
                        }
                        if (data.status === 0) {
                            layer.msg(data.msg, {icon: 5});
                        }
                    },
                    error:function (e,x,t) {

                    }
                })
            });

            window.downloadFile = function() {
                var url = ['/downloads/导入UPC码表.xlsx'];
                for (var i = 0; i < url.length; i++) {
                    try {
                        var elemIF = document.createElement("iframe");
                        elemIF.src = url[i];
                        elemIF.style.display = "none";
                        document.body.appendChild(elemIF);
                    } catch (e) {
                    }
                }
            }
        });


        $("body").bind("keydown", function (event) {
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location = location;
            }
        })

    </script>
@endsection