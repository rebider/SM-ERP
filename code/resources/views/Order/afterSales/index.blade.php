@extends('layouts/new_main')
<style type="text/css">
    .layui-layer-btn{
        text-align: center !important;
    }
    .layui-layer-btn a{
        border-radius: 0 !important;
    }
</style>
@include('common/validate')
@section('content')
{{--    @include('layouts/shortcutMenus')--}}
    <div class="content-wrapper">

        <form class="multiSearch layui-form">
            <ul class="flexSearch flexquar fclear">
                <!--发货状态-->
                <div class="frist">
                    <div class="inputTxt">是否取消：</div>
                    <div class="inputBlock">
                        <div class="multLable">
                            <input type="radio" name="is_cancel" value="0" title="全部" checked>
                            <input type="radio" name="is_cancel" value="1" title="否">
                            <input type="radio" name="is_cancel" value="2" title="是">
                        </div>
                    </div>
                </div>

                <div class="frist">
                    <div class="inputTxt">类型：</div>
                    <div class="inputBlock">
                        <div class="multLable">
                            <input type="radio" name="type" value="0" title="全部" checked>
                            <input type="radio" name="type" value="3" title="退款">
                            <input type="radio" name="type" value="1" title="退货">
                            <input type="radio" name="type" value="2" title="换货">
                        </div>
                    </div>
                </div>
                <li style="width:80%;">
                <div class="">
                    售后单号：&nbsp;&nbsp;&nbsp; <input type="text" name="after_sale_code"   autocomplete="off" placeholder="售后单号" style="width:338px;height:38px;padding-left:10px;">
                </div>

                <div class="">
                    &nbsp;&nbsp;&nbsp;订单号：&nbsp;&nbsp;&nbsp; <input type="text" name="order_number"   autocomplete="off" placeholder="订单号" style="width:338px;height:38px;padding-left:10px;">
                </div>

                <div class="">
                    &nbsp;&nbsp;&nbsp;创建时间：&nbsp;&nbsp;&nbsp;
                    <div class="layui-input-inline">
                        <input type="text" name="start_time" id="EDdate" placeholder="起始时间" autocomplete="off" class="layui-input writeinput" readonly="">
                    </div>
                    <div class="layui-input-inline">
                        <input type="text" name="end_time" id="EDdate1" placeholder="截止时间" autocomplete="off" class="layui-input writeinput" readonly="">
                    </div>
                </div>
                </li>

                <li>
                    <div class="groupBtns" style="padding-left:0px;">
                        <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="searBtn">搜索</button>
                        <button class="layui-btn layuiadmin-btn-rules createOrder" >添加</button>
                        <button class="layui-btn layui-btn-primary" lay-submit="" lay-filter="reset" >重置</button>
                    </div>
                </li>
            </ul>
        </form>
        <div class="edn-row table_index">
            <table class="" id="EDtable" lay-filter="EDtable"></table>
        </div>
    </div>

@endsection

@section('javascripts')
    <script type="text/html" id="barDemo">
        @{{#  if(d.shop_type === 3){ }}
        <a class="layui-table-link" href="javascript:void(0)" lay-event="del">删除</a>
        <a class="layui-table-link" href="javascript:void(0)" lay-event="edit">编辑</a>
        <a class="layui-table-link" href="javascript:void(0)" lay-event="check" >查看</a>
        @{{#  } }}

        @{{#  if(d.shop_type == 1){ }}
        <a class="layui-table-link" href="javascript:void(0)" lay-event="auth">授权</a>
        <a class="layui-table-link" href="javascript:void(0)" lay-event="auth">取消授权</a>
        <a class="layui-table-link" href="javascript:void(0)" lay-event="auth">重新授权</a>
        @{{#  } }}
        @{{#  if(d.shop_type == 2){ }}
        <a class="layui-table-link" href="javascript:void(0)" lay-event="authLotle">授权</a>
        <a class="layui-table-link" href="javascript:void(0)" lay-event="authLotle">取消授权</a>
        <a class="layui-table-link" href="javascript:void(0)" lay-event="authLotle">重新授权</a>
        @{{#  } }}
    </script>
    <script>
        //layui加载
        layui.config({ base: '../../layui/lay/modules/'}).extend({ formSelects: 'formSelects-v3'});
        layui.use(['layer','form','element','laydate','table','formSelects'], function(){
            var layer = layui.layer,form = layui.form,laydate = layui.laydate,table = layui.table,element = layui.element,formSelects = layui.formSelects;
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

            $('.layuiadmin-btn-rules').on('click', function(e){
                e.preventDefault();
                var type = $(this).data('type');
            });


            form.on('submit(searBtn)', function(data){
                var index = layer.msg('数据请求中', {icon: 16});
                var info = data.field;
                table.render({
                    elem: '#EDtable'
                    ,url:'/order/afterSales/ajaxGetAfterInfo'
                    ,where:{info}
                    ,cols: [[
                        {field: '', title: '序号', width:50, type:'numbers'},
                        {
                            field:'after_sale_code',
                            title:'售后单号',
                            event: 'getOrderDetails',
                            style:'cursor: pointer;',
                            templet: function(d){
                                return '<a href="javascript:void(0);" class="layui-table-link" >' + d.after_sale_code + '</a>';

                            }}
                        ,{field:'order_id', title:'关联订单号',width:212, templet: function (d) {
                                return d.orders.order_number;
                            }}
                        ,{field:'currency_form_name', width:212, title:'款项处理方式', templet: function (d) {
                                switch (d.type) {
                                    case 1:
                                        return '退货';
                                    case 2:
                                        return '换货';
                                    case 3:
                                        return '退款';
                                }
                            }}
                        ,{field:'ident_fier', width:212, title:'商品是否退回', templet: function (d) {
                                return d.ident_fier ? d.ident_fier : '';
                            }}
                        ,{field:'exchange_rate',width:212, title:'是否重新发货', templet: function (d) {
                                if (d.type == 2) {
                                    return '是';
                                } else {
                                    return '否';
                                }
                            }}
                        ,{field:'updated_at',width:212, title:'更新时间',templet: function (d) {
                                return d.updated_at ;
                            }}
                        ,{field:'updated_at',width:212, title:'操作',templet: function (d) {
                                var confirmPackage = '';
                                var cancelOrder = '';
                                if (d.is_cancel != 2) {
                                    if (d.type == 2 && d.sales_return_status == 1) {
                                        confirmPackage = '<a data-id="'+d.id+'" class="confirm-receive">确认收货</a>&nbsp;&nbsp;'
                                    }
                                    if (d.sales_return_status == 1) {
                                        cancelOrder = '<a data-id="'+ d.id +'" class="cancel-order" style="margin: 0 10px; color: #01AAED"">取消</a>';
                                    }
                                    return confirmPackage + cancelOrder ;
                                } else {
                                    return '已取消';
                                }
                            }}
                    ]]
                    ,limit:20
                    ,page: true
                    ,limits:[20,30,40,50]
                    ,done:function () {   //返回数据执行回调函数
                        layer.close(index);    //返回数据关闭loading
                    }
                });
                return false;

            });

            table.on('tool(EDtable)', function (obj) {
                var _data = obj.data,id= _data.id;
            });

            table.render({
                elem: '#EDtable'
                ,url: '/order/afterSales/ajaxGetAfterInfo'
                ,cols: [[
                    {field: '', title: '序号', width:50, type:'numbers'},
                    {
                        field:'after_sale_code',
                        title:'售后单号',
                        event: 'getOrderDetails',
                        style:'cursor: pointer;',
                        templet: function(d){
                            return '<a href="javascript:void(0);" class="layui-table-link" >' + d.after_sale_code + '</a>';

                        }}
                    ,{field:'order_number', title:'关联订单号',width:212, templet: function (d) {
                            return d.orders.order_number;
                        }}
                    ,{field:'currency_form_name', width:212, title:'款项处理方式', templet: function (d) {
                            switch (d.type) {
                                case 1:
                                    return '退货';
                                case 2:
                                    return '换货';
                                case 3:
                                    return '退款';

                            }
                        }}
                    ,{field:'ident_fier', width:212, title:'商品是否退回', templet: function (d) {
                            switch (d.sales_return_status) {
                                case 1:
                                    return '<span class="return-status">未退回</span>';
                                case 2:
                                    return '<span class="return-status">已退回</span>';
                                case 3:
                                    return '<span class="return-status">卖家确认收货</span>';
                            }
                        }}
                    ,{field:'exchange_rate',width:212, title:'是否重新发货', templet: function (d) {
                            if (d.type == 2) {
                                return '是';
                            } else {
                                return '否';
                            }
                        }}
                    ,{field:'updated_at',width:212, title:'更新时间',templet: function (d) {
                                return d.updated_at ;
                        }}
                    ,{field:'updated_at',width:212, title:'操作',templet: function (d) {
                            var confirmPackage = '';
                            var cancelOrder = '';
                            if (d.is_cancel != 2) {
                                if (d.type == 2 && d.sales_return_status == 1) {
                                    confirmPackage = '<a data-id="'+d.id+'" class="confirm-receive" style="margin: 0 10px; color: #01AAED">确认收货</a>'
                                }
                                if (d.sales_return_status == 1) {
                                    cancelOrder = '<a data-id="'+ d.id +'" class="cancel-order" style="margin: 0 10px; color: #01AAED">取消</a>';
                                }
                                return confirmPackage + cancelOrder ;
                            } else {
                                return '已取消';
                            }

                        }}
                ]]
                ,limit:20
                ,page: true
                ,limits:[20,30,40,50]
                ,done:function () {   //返回数据执行回调函数
                    layer.close(index);    //返回数据关闭loading
                }
            });

            $(document).on('click','.createOrder',function(){
                layer.open({
                    title:"添加",
                    type: 2,
                    area:["500px","260px"],
                    content: ['/order/afterSales/addOrder','no'],
                    btn:['确定','取消'],
                    btn1: function(index, layero){
                        var info = window["layui-layer-iframe" + index].callbackdata();
                        if(!info.order_num){
                            layer.msg('订单号不能为空');
                            return;
                        }
                        //判断输入的售后单是否存在
                        $.get('/order/afterSaleOrder/getOrderNumber', {order_num: info.order_num}, function (e) {
                            if(e.code == '200') {

                                //存在打开售后单的产品页面
                                layer.close(index);
                                layer.open({
                                    title:"添加",
                                    type: 2,
                                    area:["1200px","600px"],
                                    content: '/order/afterSales/createPaymentOrder?order_num='+info.order_num,
                                    btn1: function(index, layero){
                                    }
                                });
                            } else {
                                layer.msg(e.msg, {icon:5});
                                return false;
                            }
                        });
                    }
                });
            });

            form.on('submit(export)', function(data){
                location.href = "{{url('settingExchange/exportCurrencyHistory')}}"+'?currency_form_name='+ data.field.currency_form_name;
            });

            layui.use('table', function () {
                var table = layui.table;
                table.on('tool(EDtable)', function (obj) {
                    var data = obj.data;
                    if (obj.event === 'getOrderDetails') {
                        layer.open({
                            type: 2,
                            title: data.after_sale_code + '售后单详情',
                            fix: false,
                            maxmin: true,
                            shadeClose: true,
                            offset:'r',
                            area: ['80%', '90%'],
                            content: '{{url('/order/afterSales/Detail?id=')}}' + data.id,
                            end: function (index) {
                                layer.close(index);
                            }
                        })
                    }
                })
            })

        });
        $("body").bind("keydown",function(event){
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location=location;
            }
        });

        $(document).on('click', '.confirm-receive', function () {
            var _this = $(this);
            var id = $(this).attr('data-id');
            $.get('/order/afterSaleOrder/confirmReceive', {id: id}, function (e) {
                if (e.code != 0) {
                    layer.msg(e.msg);
                    return false;
                }
                layer.msg(e.msg);
                _this.hide();
                _this.parent().parent().parent().find('.return-status').text('卖家确认收货');
                return false;
            })
        });
        
        $(document).on('click', '.cancel-order', function () {
            var _this = $(this);
            var id = _this.attr('data-id');
            $.get('/order/afterSaleOrder/cancelOrder', {id: id}, function (e) {
                if (e.code != 200) {
                    layer.msg(e.msg, {icon:5});
                    return false;
                }
                layer.msg(e.msg);
                _this.parent().empty().text('已取消');
                return false;
            })
        })

    </script>
@endsection