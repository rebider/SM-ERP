@extends('layouts.new_main')
<style type="text/css">
    .layui-layer-btn {
        text-align: center !important;
    }
    .layui-layer-btn a{
        border-radius: 0 !important;
    }
</style>

@section('content')
    <div class="content-wrapper">
        <form class="multiSearch layui-form">
            <div class="flexSearch flexquar fclear">
                <!--问题类型-->
                <div class="frist">
                    <div class="inputTxt">问题类型：</div>
                    <div class="inputBlock">
                        <div class="multLable">
                            <input type="radio" name="problem" value="0" title="全部" checked>
                            <input type="radio" name="problem" value="1" title="部分缺货">
                            {{--<input type="radio" name="problem" value="2" title="超重需拆包">--}}
                            @if (isset($problem) && $problem == 3)
                                <input type="radio" name="problem" value="3" checked title="无法找到仓库">
                            @else
                                <input type="radio" name="problem" value="3" title="无法找到仓库">
                            @endif
                            @if (isset($problem) && $problem == 4)
                                <input type="radio" name="problem" value="4" checked title="无法找到物流">
                            @else
                                <input type="radio" name="problem" value="4" title="无法找到物流">
                            @endif
                        </div>
                    </div>
                </div>
                <div class="frist">
                    <div class="inputTxt">可配货比：</div>
                    <div class="inputBlock">
                        <div class="multLable">
                            <input type="radio" name="radio" value="0" title="全部" checked>
                            <input type="radio" name="radio" value="1" title="0%">
                            <input type="radio" name="radio" value="2" title="0%&lt; n &lt;100%">
                            <input type="radio" name="radio" value="3" title="100%">
                        </div>
                    </div>
                </div>

                <div class="second">
                    <div>
                        <div class="inputTxt">订单号：</div>
                        <div class="inputBlock">
                            <input type="text" name="order_number" placeholder="" autocomplete="off" class="voin">
                        </div>
                        <div class="inputTxt">电商单号：</div>
                        <div class="inputBlock">
                            <input type="text" name="plat_order_number" placeholder="" autocomplete="off" class="voin">
                        </div>
                    </div>


                    <!--搜索时间类型-->
                    <div>
                        <div class="inputBlock">
                            <div class="inputTxt">
                                <select name="time_type" id="">
                                    <option value="order_time">下单时间</option>
                                    <option value="created_at">创建时间</option>
                                    <option value="payment_time">付款时间</option>
                                    <option value="logistics_time">发货时间</option>
                                </select>
                            </div>
                            <div class="inputBlock">
                                <div class="layui-input-inline">
                                    <input type="text" name="start_time" id="EDdate" placeholder="起始时间"
                                           autocomplete="off" class="layui-input writeinput" readonly="">
                                </div>
                                <div class="layui-input-inline">
                                    <input type="text" name="end_time" id="EDdate1" placeholder="截止时间"
                                           autocomplete="off" class="layui-input writeinput" readonly="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="search">
                    <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="searBtn">搜索</button>
                    <button class="layui-btn layui-btn-primary" lay-submit="" lay-filter="reset" >重置</button>
                </div>
            </div>
        </form>
        <div class="edn-row table_index">
            <table class="" id="EDtable" lay-filter="EDtable"></table>
            <script type="text/html" id="table-warehouse-edit">
                <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit"><i
                            class="layui-icon layui-icon-edit"></i>配货</a>
            </script>
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
                elem: '#EDdate',
                type: 'datetime'
            });
            laydate.render({
                elem: '#EDdate1',
                type: 'datetime'
            });

            form.on('submit(reset)', function(data){
                window.location.reload(true);
                return false;
            });

            table.on('tool(EDtable)', function (obj) {
                var _data = obj.data,id= _data.id;
                switch (obj.event) {
                    case'edit':
                        layer.open({
                            type: 2
                            , title: '配货信息'
                            , content: '{{ route('order.goods_desc.index') }}'+'?order_id='+id
                            , area: ['80%', '90%']
                            , offset: 'r'
                            , anim: 0
                            , btn: ['确定', '取消']
                            , maxmin: true
                            , yes: function (index, layero) {
                                //点击确认触发 iframe 内容中的按钮提交
                                var submit = layero.find('iframe').contents().find("#LAY-front-submit");
                                submit.click();
                            }
                        });
                        break;
                }
            });

            form.on('submit(searBtn)', function (data) {
                var index = layer.msg('数据请求中', {icon: 16});
                var info = data.field;
                table.render({
                    elem: '#EDtable'
                    , url: '{{ route('order.pending.lists') }}'
                    , where: {data: info}
                    , cols: [[
                        {fixed: 'left', title: '操作', width: 100, toolbar: "#table-warehouse-edit"}
                        , {
                            field: 'order_number',
                            title: '订单号',
                            style: 'cursor: context-menu;',
                            templet: function (d) {
                                return d.order_number;
                            }
                        }
                        , {
                            field: 'plat_order_number', title: '电商单号', templet: function (d) {
                                return d.plat_order_number;
                            }
                        }
                        , {
                            field: 'radio', title: '可配货比例',templet: function (d) {
                                return d.radio+'%';
                            }
                        }
                        , {
                            field: 'problem', title: '问题', templet: function (d) {
                                return d.problem? d.problem: '无';
                            }
                        }
                        , {
                            field: 'picking_status', title: '配货状态', templet: function (d) {
                                var str = '无';
                                switch (d.picking_status) {
                                    case 1:
                                        str = '未配货';
                                        break;
                                    case 2:
                                        str = '完全配货';
                                        break;
                                    case 3:
                                        str = '部分配货';
                                        break;
                                }
                                return str;
                            }
                        }
                        , {
                            field: 'deliver_status', title: '发货状态', templet: function (d) {
                                var str = '无';
                                switch (d.deliver_status) {
                                    case 1:
                                        str = '未发货';
                                        break;
                                    case 2:
                                        str = '完全发货';
                                        break;
                                    case 3:
                                        str = '部分发货';
                                        break;
                                }
                                return str;
                            }
                        }
                        , {
                            field: 'payment_time', title: '付款时间', templet: function (d) {
                                return d.payment_time;
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
                ,where: {
                    @if (isset($problem) && $problem)
                    problem: "{{$problem}}"
                    @endif
                }
                , url: '{{ route('order.pending.lists') }}'
                , cols: [[
                    {fixed: 'left', title: '操作', width: 100, toolbar: "#table-warehouse-edit"}
                    , {
                        field: 'order_number',
                        title: '订单号',
                        style: 'cursor: context-menu;',
                        templet: function (d) {
                            return d.order_number;
                        }
                    }
                    , {
                        field: 'plat_order_number', title: '电商单号', templet: function (d) {
                            return d.plat_order_number;
                        }
                    }
                    , {
                        field: 'radio', title: '可配货比例',templet: function (d) {
                            return d.radio+'%';
                        }
                    }
                    , {
                        field: 'problem', title: '问题', templet: function (d) {
                            return d.problem? d.problem: '无';
                        }
                    }
                    , {
                        field: 'picking_status', title: '配货状态', templet: function (d) {
                            var str = '无';
                            switch (d.picking_status) {
                                case 1:
                                    str = '未配货';
                                    break;
                                case 2:
                                    str = '完全配货';
                                    break;
                                case 3:
                                    str = '部分配货';
                                    break;
                            }
                            return str;
                        }
                    }
                    , {
                        field: 'deliver_status', title: '发货状态', templet: function (d) {
                            var str = '无';
                            switch (d.deliver_status) {
                                case 1:
                                    str = '未发货';
                                    break;
                                case 2:
                                    str = '完全发货';
                                    break;
                                case 3:
                                    str = '部分发货';
                                    break;
                            }
                            return str;
                        }
                    }
                    , {
                        field: 'payment_time', title: '付款时间', templet: function (d) {
                            return d.payment_time;
                        }
                    }                ]]
                , limit: 20
                , page: true
                , limits: [20, 30, 40, 50]
                , done: function () {   //返回数据执行回调函数
                    layer.close(index);    //返回数据关闭loading
                }
            });

        });


        $("body").bind("keydown", function (event) {
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location = location;
            }
        });

        $(document).ready(function (e) {
            if (top === self) {
                $(".content-wrapper").animate({margin: '2rem'}, 600);
                let problemName = $("input[name=problem]:checked").attr('title');
                if (problemName === '无法找到仓库') {
                    document.title = '配货无仓库 - 速贸云仓平台';
                }
                if (problemName === '无法找到物流') {
                    document.title = '配货无物流 - 速贸云仓平台';
                }
            }
        })

    </script>
@endsection