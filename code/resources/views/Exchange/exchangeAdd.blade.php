@extends('layouts/new_main')
@section('content')
    {{--导出弹窗--}}
    <style>
        body{
            overflow-x: hidden !important;
        }
    </style>
    <div id="outputPop" class="" style="padding: 20px 30px;">
        <div class="wrap">
            <form class="layui-form" action="">
                <div class="layui-form-item source checked">
                    <div class="source-btn btn-checked" style="width: 120%;margin-bottom: 5px;">
                        <input type="checkbox" class="layuiadmin-btn-order" lay-skin="primary" lay-filter="menu" @if(!empty($currencyInfo)) checked @endif title="汇率管理">
                    </div>
                    <blockquote class="layui-elem-quote layui-quote-nm">
                        <ul class="layui-form-item" pane="">
                            @if(!empty($currencyInfo))
                            @foreach($currencyInfo as $k => $v)
                                <li><input type="checkbox" name="exchange[]"
                                           {{$v['is_show'] == 1 ? 'checked' : ''}}
                                           value="{{$v['currency_form_code']}}" lay-skin="primary" lay-filter="exchange"
                                           title="{{$v['currency_form_name']}}" class="exchange" ></li>
                            @endforeach
                            @endif
                        </ul>
                    </blockquote>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('javascripts')
    <script>
        layui.config({base: '../../layui/lay/modules/'}).extend({formSelects: 'formSelects-v3'});
        var callbackdata = function () {
            var obj = $('input:checkbox[name="exchange[]"]:checked');
            var exchange = '';
            for (var i = 0; i < obj.length; i++) {
                //利用三元运算符去点
                exchange += obj[i].value + (i == obj.length - 1 ? '' : ',');
            }
            var data = {
                exchange_name: exchange,
            };
            return data;
        };
        layui.use(['layer', 'form', 'element', 'upload'], function () {
            var layer = layui.layer, form = layui.form, upload = layui.upload, element = layui.element;
        });
        layui.use(['layer', 'form', 'element', 'laydate', 'upload', 'table', 'formSelects'], function () {
            var layer = layui.layer, form = layui.form, upload = layui.upload, laydate = layui.laydate,
                table = layui.table,
                element = layui.element, formSelects = layui.formSelects;
            // var index = layer.msg('数据请求中', {icon: 16});


            laydate.render({
                elem: '#EDdate',
                type: 'datetime'
            });
            laydate.render({
                elem: '#EDdate1',
                type: 'datetime'
            });

            form.on('checkbox(menu)', function(data){
                var source = $(this).parents(".checked"), _this = $(this);
                    source.find("li").each(function (index, element) {
                        if (_this.is(':checked')) {
                            $(this).find('input').prop('checked', true);
                        } else {
                            $(this).find('input').prop('checked', false);
                        }
                    });
                form.render();
            });
            
            form.on('checkbox(exchange)', function(data){
                var source = $(this).parents(".checked"), _this = $(this),i = 0;

                $(this).parents("li").each(function (index, element) {
                    console.log($(this).find('input').attr('title'));
                });

                source.find('.btn-checked input').prop('checked', true);
                /*if($(this).find('.layui-quote-nm input').is(':checked').length == 6){
                    $(this).parents(".checked").find('.btn-checked input').prop('checked', true);
                }else{
                    $(this).parents(".checked").find('.btn-checked input').prop('checked', false);
                }*/
                form.render();
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
