@extends('layouts/new_main')

@section('content')
    <div class="content-wrapper">
        <form class="multiSearch layui-form">
            <div class="flexSearch flexquar fclear">
                <!--配货状态-->
                <div class="frist">
                    <div class="inputTxt">配货状态：</div>
                    <div class="inputBlock">
                        <div class="multLable">
                            <input type="radio" name="picking_status" value="0" title="全部" checked autocomplete="off">
                            <input type="radio" name="picking_status" value="1" title="未配货" autocomplete="off">
                            <input type="radio" name="picking_status" value="2" title="已配货" autocomplete="off">
                            <input type="radio" name="picking_status" value="3" title="部分配货" autocomplete="off">
                        </div>
                    </div>
                </div>

                <!--发货状态-->
                <div class="frist">
                    <div class="inputTxt">发货状态：</div>
                    <div class="inputBlock">
                        <div class="multLable">
                            <input type="radio" name="deliver_status" value="0" title="全部" checked autocomplete="off">
                            <input type="radio" name="deliver_status" value="1" title="未发货"  autocomplete="off">
                            <input type="radio" name="deliver_status" value="2" title="已发货"  autocomplete="off">
                            <input type="radio" name="deliver_status" value="3" title="部分发货"  autocomplete="off">
                        </div>
                    </div>
                </div>

                <!--问题类型-->
                <div class="second">
                    <div>
                        <div class="inputTxt">问题类型：</div>
                        <div class="inputBlock">
                            <div class="multLable">
                                <select  name="question_type" autocomplete="off">
                                    <option value="">请选择</option>
                                    @foreach($troubles as $trouble)
                                        <option value="{{$trouble['id']}}">{{$trouble['trouble_type_name']}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <!--来源平台-->
                    <div>
                        <div class="inputTxt">来源平台：</div>
                        <div class="inputBlock">
                            <div class="multLable">
                                <select name="platforms_id" id="platforms" lay-filter="platforms" autocomplete="off">
                                    <option value="">请选择</option>
                                    @if(isset($platforms))
                                        @foreach($platforms as $item_pt)
                                            <option value="{{$item_pt['id']}}">{{$item_pt['name_EN']}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <!--来源店铺-->
                        <div class="inputTxt">来源店铺：</div>
                        <div class="inputBlock">
                            <div class="multLable">
                                <select name="source_shop" id="sourceShop" lay-filter="sourceShop" autocomplete="off">
                                    <option value="">请选择</option>
                                    @if(isset($shops))
                                        @foreach($shops as $item_sd)
                                            <option value="{{$item_sd['id']}}">{{$item_sd['shop_name']}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <!--物流方式-->
                <div class="second">

                    <!--仓库-->
                    <div>
                        <div class="inputTxt">仓库：</div>
                        <div class="inputBlock">
                            <div class="multLable">
                                <select  name="warehouse_id" class="voin_select" lay-filter="warehouse_select" autocomplete="off">
                                    <option value="">请选择</option>
                                    @if (isset($warehouses))
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{$warehouse['id']}}">{{$warehouse['warehouse_name']}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="inputTxt">物流方式：</div>
                        <div class="inputBlock">
                            <div class="multLable">
                                <select  name="logistics_id" id="unselectedLogistics" autocomplete="off">
                                    <option value="">请选择</option>
                                    @if (isset($logistics))
                                        @foreach($logistics as $logistic)
                                            <option value="{{$logistic['id']}}">{{$logistic['logistic_name']}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    <!--订单号-->
                    <div>
                        <div class="inputTxt">订单号：</div>
                        <div class="inputBlock">
                            <input type="text" name="order_number"  placeholder="请输入订单号" autocomplete="off" class="voin" autocomplete="off">
                        </div>
                    </div>
                </div>


                <!--搜索类型-->
                <div class="second">
                    <div>
                        <div class="inputTxt">搜索类型：</div>
                        <div class="inputBlock">
                            <div class="multLable">
                                <select  name="search_type" autocomplete="off">
                                    <option value="1">电商单号</option>
                                    <option value="2">跟踪号</option>
                                    <option value="3">收件人</option>
                                    <option value="4">收件人邮箱</option>
                                </select>
                            </div>
                            <input type="text" name="search_type_code"  placeholder="请输入" autocomplete="off" class="voin" >
                        </div>
                    </div>

                    <!--搜索时间类型-->
                    <div>
                        <div class="inputBlock">
                            <div class="multLable">
                                <select  name="times_type" autocomplete="off">
                                    <option value="1">下单时间</option>
                                    <option value="2">创建时间</option>
                                    <option value="3">发货时间</option>
                                </select>
                            </div>
                            <div class="layui-input-inline">
                                <input type="text" name="start-date" id="EDdate" placeholder="起始时间" autocomplete="off" class="layui-input writeinput" readonly="">
                            </div>
                            <div class="layui-input-inline">
                                <input type="text" name="end-date" id="EDdate1" placeholder="截止时间" autocomplete="off" class="layui-input writeinput" readonly="">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="search">
                    <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="searBtn">搜索</button>
                    <button class="layui-btn layuiadmin-btn-order" lay-submit="" lay-filter="export">导出</button>
                    <button class="layui-btn layui-btn-primary" lay-submit="" lay-filter="reset" >重置</button>
                </div>
            </div>
        </form>
        <div class="edn-row table_index">
            <table class="" id="EDtable" lay-filter="EDtable"></table>
        </div>
    </div>

@endsection

@section('javascripts')

    <script>
        //layui加载
        layui.config({ base: '../../layui/lay/modules/'}).extend({ formSelects: 'formSelects-v3'});
        layui.use(['layer','form','element','laydate','table','formSelects'], function(){
            var layer = layui.layer,form = layui.form,laydate = layui.laydate,table = layui.table,element = layui.element,formSelects = layui.formSelects;
            var index = layer.msg('数据请求中', {icon: 16});

            laydate.render({
                elem: '#EDdate',
                type: 'datetime',
            });
            laydate.render({
                elem: '#EDdate1',
                type: 'datetime',
            });
            //店铺联动
            form.on('select(platforms)', function (data) {
                var platforms = data.value;
                loading = layer.msg('加载店铺中...', {
                    icon: 16
                    , shade: 0.01
                });
                if (platforms == '' ) {
                    var tmp = '';
                    @if(isset($shops))
                        tmp = '<option value="">请选择</option>';
                    @foreach($shops as $item_sd)
                        tmp += "<option value='{{$item_sd['id']}}'>{{$item_sd['shop_name']}}</option>";
                    @endforeach
                    @else
                        tmp = '<option value="">无</option>';
                    @endif
                    $("#sourceShop").append(tmp);
                    layer.close(loading);
                    form.render();
                    return false
                }
                $.ajax({
                    type: 'get',
                    url: '{{ route('order.shops.list') }}',
                    data: {plat_id: platforms},
                    dataType: 'json',
                    success: function (res) {
                        if (res.code) {
                            var item = res.data, tmp = '';
                            if (Array.isArray(item)) {
                                $("#sourceShop").empty();
                                tmp = '<option value="">请选择</option>';
                                for (var i = 0; i < item.length; i++) {
                                    tmp += "<option value='" + item[i].id + "'>" + item[i].shop_name + "</option>";
                                }
                            } else {
                                tmp = '<option value="">无</option>';
                            }
                            $("#sourceShop").append(tmp);
                            layer.close(loading);
                            form.render();
                        }
                    }
                });

            });
            form.on('submit(export)', function(data){
                var index = layer.msg('数据请求中', {icon: 16});
                var info = data.field;
                var url = '{{url('order/exportOrdersInfo')}}';
                var params = '';
                Object.keys(info).forEach(function(key){
                    params +=key+'='+info[key]+'&';
                });
                params = params.substr(0,params.length-1);
                url = url+'?'+params;
                location.href = url;
                return false;
            });

            form.on('submit(reset)', function(data){
                window.location.reload(true);
                return false;
            });

            form.on('submit(searBtn)', function(data){
                var index = layer.msg('数据请求中', {icon: 16});
                var info = data.field;
                var is_problem = "{{$is_problem}}";
                var unableToFindWarehouse = "{{$unableToFindWarehouse ?? ''}}";
                var unableToFindLogistics = "{{$unableToFindLogistics ?? ''}}";
                table.render({
                    elem: '#EDtable'
                    ,url:'/order/orderIndexSearch'
                    ,where:{data:info,is_problem:is_problem, unableToFindWarehouse: unableToFindWarehouse, unableToFindLogistics: unableToFindLogistics}
                    ,cols: [[
//                        {checkbox: true},
//                        {field: '', title: 'NO', width:50, type:'numbers'},
                        {field:'order_number', title:'订单号',event: 'getOrderDetails', style:'cursor: pointer;',templet: function(d){
//                        return '<a href="javascript:void(0);" class="layui-table-link" >'+ $.escapeHTML(d.order_number) +'</a>';
                            return '<a href="javascript:void(0);" class="layui-table-link" >'+ d.order_number +'</a>';
                        }}
                        ,{field:'plat_order_number', title:'电商单号', templet: function (d) {
//                        return $.escapeHTML(d.plat_order_number);
                            return d.plat_order_number;
                        }}
                        ,{field:'warehouse', title:'仓库', templet: function (d) {
//                        return d.orders_invoices ? $.escapeHTML(d.orders_invoices.warehouse) : '无法匹配仓库';
                            return d.warehouse ? d.warehouse : '';
                        }}
                        ,{field:'logistics_way', title:'物流方式', templet: function (d) {
//                        return d.orders_invoices ? $.escapeHTML(d.orders_invoices.logistics_way) : '无法匹配物流';
                            return d.logistics ? d.logistics : '';
                        }}
                        ,{field:'tracking_no', title:'物流跟踪号', templet: function (d) {
                            if (d.orders_invoices_many.length > 0 ) {
                                var tracking_no = '';
                                for (i = 0; i < d.orders_invoices_many.length; i++) {
                                    if(d.orders_invoices_many[i].tracking_no != ''){
                                        tracking_no = d.orders_invoices_many[i].tracking_no;
                                    }
                                }
                                return tracking_no;
                            }
                            return '';
                        }}
                        ,{field:'taotla_value', title:'派送运费',templet: function(d){
                            if (d.orders_invoices) {
                                var freight =  d.orders_invoices.taotla_value ? d.orders_invoices.taotla_value : '';
                                var currency = d.orders_invoices.currency_code ? d.orders_invoices.currency_code : 'RMB';
//                            return $.escapeHTML(freight+currency);
                                return freight+currency;
                            }
                            return '';
                        }}
                        ,{field:'addressee_name', title:'收件人名称',templet: function(d){
//                        return $.escapeHTML(d.addressee_name);
                            return d.addressee_name;
                        }}
                        ,{field:'platform_name', title:'来源平台',templet: function(d){
//                       return  $.escapeHTML(d.platform_name);
                            return  d.platform_name;
                        }}
                        ,{field:'source_shop_name', title:'来源店铺名称',templet: function(d){
//                        return $.escapeHTML(d.source_shop_name);
                            return d.source_shop_name;
                        }},
                        {field:'picking_status', title:'配货状态',templet: function(d){
                            if (d.picking_status == 1 ) {
                                return '未配货';
                            } else if (d.picking_status == 2) {
                                return '已配货';
                            } else if (d.picking_status == 3) {
                                return '部分配货';
                            }
                        }},
                        {field:'deliver_status', title:'发货状态',templet: function(d){
                            if (d.deliver_status == 1 ) {
                                return '未发货';
                            } else if (d.deliver_status == 2) {
                                return '已发货';
                            } else if (d.deliver_status == 3) {
                                return '部分发货';
                            }
                            return '未发货';
                        }}
                        ,{field:'payment_time', title:'付款时间',templet: function(d) {
                            return d.payment_time;
                        } }
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

            table.render({
                elem: '#EDtable'
                ,url:'/order/orderIndexSearch'
                ,where:{is_problem:"{{$is_problem}}", unableToFindWarehouse: "{{$unableToFindWarehouse ?? ''}}", unableToFindLogistics: "{{$unableToFindLogistics ?? ''}}"}
                ,cols: [[
//                    {checkbox: true},
//                    {field: '', title: 'NO', width:50, type:'numbers'},
                    {field:'order_number', title:'订单号',width:200,event: 'getOrderDetails', style:'cursor: pointer;',templet: function(d){
//                        return '<a href="javascript:void(0);" class="layui-table-link" >'+ $.escapeHTML(d.order_number) +'</a>';
                        return '<a href="javascript:void(0);" class="layui-table-link" >'+ d.order_number +'</a>';
                    }}
                    ,{field:'plat_order_number', title:'电商单号',width:200, templet: function (d) {
//                        return $.escapeHTML(d.plat_order_number);
                        return d.plat_order_number;
                    }}
                    ,{field:'warehouse', title:'仓库', templet: function (d) {
//                        return d.orders_invoices ? $.escapeHTML(d.orders_invoices.warehouse) : '无法匹配仓库';
                        return d.warehouse ? d.warehouse : '';
                    }}
                    ,{field:'logistics_way', title:'物流方式', templet: function (d) {
//                        return d.orders_invoices ? $.escapeHTML(d.orders_invoices.logistics_way) : '无法匹配物流';
                        return d.logistics ? d.logistics : '';
                    }}
                    ,{field:'tracking_no', title:'物流跟踪号', templet: function (d) {
                        if (d.orders_invoices_many.length > 0 ) {
                            var tracking_no = '';
                            for (i = 0; i < d.orders_invoices_many.length; i++) {
                                if(d.orders_invoices_many[i].tracking_no != ''){
                                    tracking_no = d.orders_invoices_many[i].tracking_no;
                                }
                            }
                            return tracking_no;
//                            return d.orders_invoices.logistics_number ? $.escapeHTML(d.orders_invoices.logistics_number) : '';
//                            return d.orders_invoices.tracking_no ? d.orders_invoices.tracking_no : '';
                        }
                        return '';
                    }}
                    ,{field:'taotla_value', title:'派送运费',templet: function(d){
                        if (d.orders_invoices) {
                            var freight =  d.orders_invoices.taotla_value ? d.orders_invoices.taotla_value : '';
                            var currency = d.orders_invoices.currency_code ? d.orders_invoices.currency_code : 'RMB';
//                            return $.escapeHTML(freight+currency);
                            return freight+currency;
                        }
                        return '';
                    }}
                    ,{field:'addressee_name', title:'收件人名称',templet: function(d){
//                        return $.escapeHTML(d.addressee_name);
                        return d.addressee_name;
                    }}
                    ,{field:'platform_name', title:'来源平台',templet: function(d){
//                       return  $.escapeHTML(d.platform_name);
                        return  d.platform_name;
                    }}
                    ,{field:'source_shop_name', title:'来源店铺名称',templet: function(d){
//                        return $.escapeHTML(d.source_shop_name);
                        return d.source_shop_name;
                    }},
                    {field:'picking_status', title:'配货状态',templet: function(d){
                        if (d.picking_status == 1 ) {
                            return '未配货';
                        } else if (d.picking_status == 2) {
                            return '已配货';
                        } else if (d.picking_status == 3) {
                            return '部分配货';
                        }
                    }},
                    {field:'deliver_status', title:'发货状态',templet: function(d){
                        if (d.deliver_status == 1 ) {
                            return '未发货';
                        } else if (d.deliver_status == 2) {
                            return '已发货';
                        } else if (d.deliver_status == 3) {
                            return '部分发货';
                        }
                        return '未发货';
                    }}
                    ,{field:'payment_time', title:'付款时间',templet: function(d) {
                        return d.payment_time;
                    } }
                ]]
                ,limit:20
                ,page: true
                ,limits:[20,30,40,50]
                ,done:function () {   //返回数据执行回调函数
                    layer.close(index);    //返回数据关闭loading
                }
            });

            layui.use('table', function(){
                var table = layui.table;
                //监听单元格事件
                table.on('tool(EDtable)', function(obj){
                    var data = obj.data;
                    if(obj.event === 'getOrderDetails'){
                        layer.open({
                            type:2,
                            title: data.order_number + ' 订单详情',
                            fix: false,
                            maxmin: true,
                            shadeClose: true,
                            offset:'r',
                            area: ['80%', '90%'],
                            content: '{{ url('order/orderDetails') }}' + '/' + data.id,
                            end: function(index){
                                layer.close(index);
                            }
                        });
                    }
                });
            });


            form.on('select(warehouse_select)', function (data) {
                var shopOption;
                var warehouseId = data.value;
                var unselectOption = $("#unselectedLogistics option:first");

                $.get("/order/getLogistics", {warehouseId: warehouseId}, function (e) {
                    if (e.code != 0) {
                        layer.msg(e.msg)
                        return false;
                    }
                    $("#unselectedLogistics").empty();
                    $("#unselectedLogistics").append(unselectOption);
                    $.each(e.data, function (k, v) {
                        shopOption += '<option value="'+ v.id +'">'+ v.logistic_name +'</option>'
                    })
                    unselectOption.after(shopOption);
                    form.render('select');
                })
            })
        });
        $("body").bind("keydown",function(event){
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location=location;
            }
        });
        
        $(document).ready(function (e) {
            if (top === self) {
                $(".content-wrapper").animate({margin: '2rem'}, 600)
                document.title = '问题订单 - 速贸云仓平台'
            }
        })
        
    </script>
@endsection