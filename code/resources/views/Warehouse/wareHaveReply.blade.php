@extends('layouts/main')

@section('content')
    <div class="sectionBody">
        <div class="location">
    		<span class="layui-breadcrumb">
			  <a href="">工单管理</a>
			  <a><cite>已回复工单</cite></a>
			</span>
        </div>
        <div class="edn-row">
            <form action="" class="layui-form">
                <ul class="condSearch">
                    <li class="layui-inline">
                        <div class="layui-input-inline">
                            <input type="text" name="number" placeholder="客诉单号/订单号/运单号" autocomplete="off" class="layui-input writeinput">
                        </div>
                    </li>
                    <li class="layui-inline">
                        <button class="layui-btn layui-btn-normal" lay-submit="" lay-filter="searBtn">查询</button>
                    </li>
                </ul>
            </form>
        </div>
        <div class="edn-row table_index">
            <table class="layui-hide" id="EDtable" lay-filter="EDtable"></table>
        </div>
    </div>
@endsection
@section('javascripts')
    <script>
        layui.use(['layer','form','element','laydate','table'], function(){
            var layer = layui.layer,form = layui.form,laydate = layui.laydate,table = layui.table,element = layui.element;
            var index = layer.msg('数据请求中', {icon: 16});

            //查询
            form.on('submit(searBtn)', function(data){
                var number = data.field.number;
                var index = layer.msg('数据请求中', {icon: 16});
                table.render({
                    elem: '#EDtable'
                    ,url:'/Warehouse/ware_have_reply_list'
                    ,where:{number:number}
                    ,cols: [[
                        {field:'cc_code', title:'客诉单号',templet: function(d){
                            return '<a href="{{ url('?url=').'/Warehouse/ware_detail/' }}'+$.escapeHTML(d.customer_complaint_id)+'" class="layui-table-link" target="_blank">'+ $.escapeHTML(d.cc_code) +'</a>'
                        }}
                        ,{field:'order_number', title:'订单号', templet: function (d) {
                            return $.escapeHTML(d.order_number);
                        }}
                        ,{field:'tracking_number', title:'运单号', templet: function (d) {
                            return $.escapeHTML(d.tracking_number);
                        }}
                        ,{field:'cc_type', title:'工单类型', templet:function(d){
                            switch(d.cc_type){
                                case 1:
                                    return '物流停滞';
                                    break;
                                case 2:
                                    return '破损';
                                    break;
                                case 3:
                                    return '丢件';
                                    break;
                                case 4:
                                    return '虚假签收';
                                    break;
                                case 5:
                                    return '多发或漏发';
                                    break;
                                case 6:
                                    return '特殊赔付';
                                    break;
                                default:
                                    return '其他';
                            }
                        }}
                        ,{field:'priority', title:'重要等级', templet:function(d){
                            switch(d.priority){
                                case 2:
                                    return '重要';
                                    break;
                                case 3:
                                    return '紧急';
                                    break;
                                default:
                                    return '普通';
                            }
                        }}
                        ,{field:'username', title:'咨询人'}
                        ,{field:'consult_time', title:'咨询时间'}
                        ,{fixed: 'right',  width:80,title:'操作',templet: function(d){
                            return '<a href="{{ url('?url=').'/Warehouse/ware_detail/' }}'+d.customer_complaint_id+'" target="_blank" class="layui-table-link">补充回复</a>'
                        }}
                    ]]
                    ,page: true
                    ,limits:[20,30,40,50]
                    ,limit: 20 //每页默认显示的数量
                    ,done:function () {   //返回数据执行回调函数
                        layer.close(index);    //返回数据关闭loading
                    }
                });
                return false;
            });

            table.render({
                elem: '#EDtable'
                ,url:'/Warehouse/ware_have_reply_list'
                ,cols: [[
                    {field:'cc_code', title:'客诉单号',templet: function(d){
                        return '<a href="{{ url('?url=').'/Warehouse/ware_detail/' }}'+$.escapeHTML(d.customer_complaint_id)+'" class="layui-table-link" target="_blank">'+ $.escapeHTML(d.cc_code) +'</a>'
                    }}
                    ,{field:'order_number', title:'订单号', templet: function (d) {
                        return $.escapeHTML(d.order_number);
                    }}
                    ,{field:'tracking_number', title:'运单号', templet: function (d) {
                        return $.escapeHTML(d.tracking_number);
                    }}
                    ,{field:'cc_type', title:'工单类型', templet:function(d){
                        switch(d.cc_type){
                            case 1:
                                return '物流停滞';
                                break;
                            case 2:
                                return '破损';
                                break;
                            case 3:
                                return '丢件';
                                break;
                            case 4:
                                return '虚假签收';
                                break;
                            case 5:
                                return '多发或漏发';
                                break;
                            case 6:
                                return '特殊赔付';
                                break;
                            default:
                                return '其他';
                        }
                    }}
                    ,{field:'priority', title:'重要等级', templet:function(d){
                        switch(d.priority){
                            case 2:
                                return '重要';
                                break;
                            case 3:
                                return '紧急';
                                break;
                            default:
                                return '普通';
                        }
                    }}
                    ,{field:'username', title:'咨询人'}
                    ,{field:'consult_time', title:'咨询时间'}
                    ,{fixed: 'right', width:80, title:'操作',templet: function(d){
                        return '<a href="{{ url('?url=').'/Warehouse/ware_detail/' }}'+d.customer_complaint_id+'" target="_blank" class="layui-table-link">补充回复</a>'
                    }}
                ]]
                ,page: true
                ,limits:[20,30,40,50]
                ,limit: 20 //每页默认显示的数量
                ,done:function () {   //返回数据执行回调函数
                    layer.close(index);    //返回数据关闭loading
                }
            });

        });


        $("body").bind("keydown",function(event){
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location=location;
            }
        })

    </script>
@endsection