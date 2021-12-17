@extends('layouts/new_main')

@section('content')
{{--    @include('layouts/shortcutMenus')--}}
    <style>
        .layui-table-tips-main{display:none}
        .layui-table-tips-c{display:none}
        .input-upload {
            display: inline-block;
            width: 70%;
            line-height: 30px;
            border: 1px solid lightgray;
            border-radius: 5px;
        }
        .input-upload a{
            display: inline-block;
            line-height: 30px;
            width: 75px;
            text-align: center;
            background: #56a9fb;
            color: white;
        }
        .input-upload a:hover{
            color: white;
        }
        .input-upload span {
            display: inline-block;
            line-height: 30px;
            margin: 0 5px;
        }
    </style>


    <div class="content-wrapper">
        @include('common.validate')
        <form class="multiSearch layui-form">
            <div class="flexSearch flexquar fclear">
                <!--配货状态-->
                <div class="frist">
                    <div class="inputTxt">匹配状态：</div>
                    <div class="inputBlock">
                        <div class="multLable">
                            <input type="radio" name="match_status" value="0" title="全部" checked>
                            <input type="radio" name="match_status" value="1" title="未匹配">
                            <input type="radio" name="match_status" value="2" title="已匹配">
                            @if (isset($mapping_status) && $mapping_status == 3)
                                <input type="radio" name="match_status" value="3" checked  title="匹配失败">
                            @else
                                <input type="radio" name="match_status" value="3" title="匹配失败">
                            @endif
                        </div>
                    </div>
                </div>

                <!--发货状态-->
                <div class="frist">
                    <div class="inputTxt">订单来源：</div>
                    <div class="inputBlock">
                        <div class="multLable">
                            <input type="radio" name="order_source" value="0" title="全部" checked>
                            <input type="radio" name="order_source" value="1" title="平台订单">
                            <input type="radio" name="order_source" value="2" title="手工订单">
                        </div>
                    </div>
                </div>

                <div class="second">
                    <!--来源平台-->
                    <div>
                        <div class="inputTxt">来源平台：</div>
                        <div class="inputBlock">
                            <div class="multLable">
                                <select name="platform_name" lay-filter="platform_select">
                                    <option value="">请选择</option>
                                    @if (isset($platforms))
                                        @foreach($platforms as $platform)
                                            <option value="{{$platform['id']}}">{{$platform['name_EN']}}</option>
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
                                <select name="source_shop" id="shop_unselected">
                                    <option value="">请选择</option>
                                    @if (isset($shops))
                                        @foreach($shops as $shop)
                                            <option value="{{$shop['id']}}">{{$shop['shop_name']}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    <!--搜索时间类型-->
                    <div>
                        <div class="inputBlock">
                            <div class="inputTxt">下单时间：</div>
                            <div class="layui-input-inline">
                                <input type="text" name="start-date" id="EDdate" placeholder="起始时间" autocomplete="off"
                                       class="layui-input writeinput" readonly="">
                            </div>
                            <div class="layui-input-inline">
                                <input type="text" name="end-date" id="EDdate1" placeholder="截止时间" autocomplete="off"
                                       class="layui-input writeinput" readonly="">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="second">
                    <!--订单号-->
                    <div>
                        <div class="inputTxt">订单号：</div>
                        <div class="inputBlock">
                            <input type="text" name="order_number" placeholder="请输入订单号" autocomplete="off" class="voin">
                        </div>
                    </div>

                    <div>
                        <div class="inputTxt">电商单号：</div>
                        <div class="inputBlock">
                            <input type="text" name="platform_order" placeholder="请输入电商订单号" autocomplete="off"
                                   class="voin">
                        </div>
                    </div>
                </div>

                <div class="search">
                    <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="searBtn">搜索</button>
                    <a class="layui-btn layuiadmin-btn-order create-order">创建订单</a>
                    <button class="layui-btn layuiadmin-btn-order" lay-submit="" lay-filter="import">导入订单</button>
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
        layui.config({base: '../../layui/lay/modules/'}).extend({formSelects: 'formSelects-v3'});
        layui.use(['layer', 'form', 'element', 'laydate', 'table', 'formSelects'], function () {
            var layer = layui.layer, form = layui.form, laydate = layui.laydate, table = layui.table,
                element = layui.element, formSelects = layui.formSelects;
            var index = layer.msg('数据请求中', {icon: 16});

            laydate.render({
                elem: '#EDdate',
                type: 'datetime',
            });
            laydate.render({
                elem: '#EDdate1',
                type: 'datetime',
            });

            {{--form.on('submit(import)', function(data){--}}
            {{--    var index = layer.msg('数据请求中', {icon: 16});--}}
            {{--    var info = data.field;--}}
            {{--    var url = '{{url('order/exportOrdersInfo')}}';--}}
            {{--    var params = '';--}}
            {{--    Object.keys(info).forEach(function(key){--}}
            {{--        params +=key+'='+info[key]+'&';--}}
            {{--    });--}}
            {{--    params = params.substr(0,params.length-1);--}}
            {{--    url = url+'?'+params;--}}
            {{--    location.href = url;--}}
            {{--    return false;--}}
            {{--});--}}


            form.on('submit(import)', function (data) {
                layer.open({
                    title: "文件上传",
                    area: ['500px', '220px'],
                    content: ' XLS文件：' +
                        '<div class="input-upload"><a class="upload-btn" id="upload-btn">点击上传</a><span id="input-file-name">请选择...</span></div>' +
                        '<input id="excel-import" style="display: none;" type="file" class="" formenctype="multipart/form-data"> <a href="/file/原始订单导入模板.xlsx" style="color: dodgerblue;">订单模板</a>',
                    yes: function (index, layero) {
                        var data = new FormData;
                        var files = document.getElementById('excel-import').files[0];
                        data.append('import', document.getElementById('excel-import').files[0]);
                        data.append('_token', '{{csrf_token()}}');
                        $.ajax({
                            type: 'post',
                            url: 'originalOrderImport',
                            data: data,
                            cache: false,
                            processData: false,
                            contentType: false,
                            enctype: 'multipart/form-data',
                            dataType: 'json',
                            headers: {
                                'X-CSRF-TOKEN': '{{csrf_token()}}'
                            },
                            success: function (result) {
                                if (result.code == 0) {
                                    layer.alert(result.msg, {icon: 1, yes: function () {
                                            window.location.reload();
                                        }});
                                    return false;
                                } else {
                                    layer.alert(result.msg, {icon: 2});
                                    return false;
                                }


                            },
                            error: function (result) {
                                layer.alert(result.msg, {icon: 2})
                            }
                        })
                    }
                });
                return false;
            })

            form.on('submit(reset)', function (data) {
                window.location.reload(true);
                return false;
            });

            form.on('submit(searBtn)', function (data) {
                var index = layer.msg('数据请求中', {icon: 16});
                var info = data.field;
                table.render({
                    elem: '#EDtable'
                    , url: '/order/originalOrderSearch'
                    , where: {data: info}
                    , cols: [[
                        {checkbox: true},
                        {field: '', title: '序号', width: 50, type: 'numbers'},
                        {
                            field: 'order_number',
                            title: '订单号',
                            event: 'getOrderDetails',
                            style: 'cursor: pointer;',
                            width: 215,
                            templet: function (d) {
//                        return '<a href="javascript:void(0);" class="layui-table-link" >'+ $.escapeHTML(d.order_number) +'</a>';
                                return '<a href="javascript:void(0);" class="layui-table-link" >' + d.order_number + '</a>';
                            }
                        }
                        , {
                            field: 'plat_order_number', title: '电商单号', width: 215, templet: function (d) {
//                        return $.escapeHTML(d.plat_order_number);
                                return d.platform_order;
                            }
                        }
                        , {
                            field: 'order_price', title: '付款金额', templet: function (d) {
//                        return d.orders_invoices ? $.escapeHTML(d.orders_invoices.logistics_way) : '无法匹配物流';
                                return d.order_price;
                            }
                        }
                        , {
                            field: 'currency', title: '币种', width: 80, templet: function (d) {
//                             if (d.orders_invoices) {
// //                            return d.orders_invoices.logistics_number ? $.escapeHTML(d.orders_invoices.logistics_number) : '';
//                                 return d.orders_invoices.logistics_number ? d.orders_invoices.logistics_number : '';
//                             }
                                return d.currency;
                            }
                        }
                        , {
                            field: 'taotla_value', title: '下单时间', templet: function (d) {
//                             if (d.orders_invoices) {
//                                 var freight =  d.orders_invoices.taotla_value ? d.orders_invoices.taotla_value : '';
//                                 var currency = d.orders_invoices.currency_code ? d.orders_invoices.currency_code : 'RMB';
// //                            return $.escapeHTML(freight+currency);
//                                 return freight+currency;
//                             }
                                return '<span title="'+ d.order_time +'">'+d.order_time+'</span>';
                            }
                        },
                        {
                            field: 'payment_time', title: '付款时间', templet: function (d) {
                                return '<span title="'+ d.payment_time +'">'+d.payment_time+'</span>';
                            }
                        }
                        , {
                            field: 'addressee_name', title: '抓单时间', templet: function (d) {
//                        return $.escapeHTML(d.addressee_name);
                                return '<span title="'+ d.grab_time +'">'+d.grab_time+'</span>';
                            }
                        },
                        {
                            field: 'match_status', title: '匹配状态', width: 100, templet: function (d) {
                                if (d.match_status == 1) {
                                    return '未匹配';
                                } else if (d.match_status == 2) {
                                    return '已匹配';
                                } else if (d.match_status == 3) {
                                    if (d.match_fail_reason) {
                                        return '匹配失败：' + d.match_fail_reason;
                                    }
                                    return '匹配失败';
                                }
                            }
                        },
                        {
                            field: 'source_shop_name', title: '来源平台', width:110,templet: function (d) {
//                        return $.escapeHTML(d.source_shop_name);
                                var platform = '';
                                if (d.platform == 1) {
                                    return platform = '亚马逊';
                                } else if (d.platform == 2) {
                                    return platform = '乐天';
                                } else if (d.platform == 3) {
//                                    return platform = '其他：';
                                    return platform = '其他';
                                }
                            }
                        },
                        {
                            field: 'mark', title: '订单备注',width:110, templet: function (d) {
//                                return '<span title="'+ d.mark +'">'+!d.mark ? '' : d.mark +'</span>'
                            return '<span title="'+ d.mark +'">'+ (d.mark === null ? '' : d.mark) +'</span>'
                            }
                        },
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
                , url: 'originalOrderSearch'
                ,where: {
                    @if (isset($mapping_status) && $mapping_status == 3)
                        match_status: 3
                    @endif
                }
                , cols: [[
                    {checkbox: true},
                    {field: '', title: '序号', width: 50, type: 'numbers'},
                    {
                        field: 'order_number',
                        title: '订单号',
                        event: 'getOrderDetails',
                        style: 'cursor: pointer;',
                        width: 215,
                        templet: function (d) {
//                        return '<a href="javascript:void(0);" class="layui-table-link" >'+ $.escapeHTML(d.order_number) +'</a>';
                            return '<a href="javascript:void(0);" class="layui-table-link" >' + d.order_number + '</a>';
                        }
                    }
                    , {
                        field: 'plat_order_number', title: '电商单号', width: 215, templet: function (d) {
//                        return $.escapeHTML(d.plat_order_number);
                            return d.platform_order;
                        }
                    }
                    , {
                        field: 'order_price', title: '付款金额', templet: function (d) {
//                        return d.orders_invoices ? $.escapeHTML(d.orders_invoices.logistics_way) : '无法匹配物流';
                            return d.order_price;
                        }
                    }
                    , {
                        field: 'currency', title: '币种', width: 80, templet: function (d) {
//                             if (d.orders_invoices) {
// //                            return d.orders_invoices.logistics_number ? $.escapeHTML(d.orders_invoices.logistics_number) : '';
//                                 return d.orders_invoices.logistics_number ? d.orders_invoices.logistics_number : '';
//                             }
                            return d.currency;
                        }
                    }
                    , {
                        field: 'taotla_value', title: '下单时间', templet: function (d) {
//                             if (d.orders_invoices) {
//                                 var freight =  d.orders_invoices.taotla_value ? d.orders_invoices.taotla_value : '';
//                                 var currency = d.orders_invoices.currency_code ? d.orders_invoices.currency_code : 'RMB';
// //                            return $.escapeHTML(freight+currency);
//                                 return freight+currency;
//                             }
                            return '<span title="'+ d.order_time +'">'+d.order_time+'</span>';
                        }
                    },
                    {
                        field: 'payment_time', title: '付款时间', templet: function (d) {
                            return '<span title="'+ d.payment_time +'">'+d.payment_time+'</span>';
                        }
                    }
                    , {
                        field: 'addressee_name', title: '抓单时间', templet: function (d) {
//                        return $.escapeHTML(d.addressee_name);
                            return '<span title="'+ d.grab_time +'">'+d.grab_time+'</span>';
                        }
                    },
                    {
                        field: 'match_status', title: '匹配状态', width: 100, templet: function (d) {
                            if (d.match_status == 1) {
                                return '未匹配';
                            } else if (d.match_status == 2) {
                                return '已匹配';
                            } else if (d.match_status == 3) {
                                if (d.match_fail_reason) {
                                    return '匹配失败：' + d.match_fail_reason;
                                }
                                return '匹配失败';
                            }
                        }
                    },
                    {
                        field: 'source_shop_name', title: '来源平台', width:110, templet: function (d) {
//                        return $.escapeHTML(d.source_shop_name);
                            var platform = '';
                            if (d.platform == 1) {
                                return platform = '亚马逊';
                            } else if (d.platform == 2) {
                                return platform = '乐天';
                            } else if (d.platform == 3) {
//                                return platform = '其他：';
                                return platform = '其他';
                            }
                            return '<span title="'+ d.source_shop_name +'">'+platform+'</span>'
                        }
                    },
                    {
                        field: 'picking_status', title: '订单备注', width:110, templet: function (d) {
                            // console.log(d.mark == null ? '' : d.mark)
                            return '<span title="'+ d.mark +'">'+ (d.mark === null ? '' : d.mark) +'</span>'
                        }
                    },
                ]]
                , limit: 20
                , page: true
                , limits: [20, 30, 40, 50]
                , done: function () {   //返回数据执行回调函数
                    layer.close(index);    //返回数据关闭loading
                },
            });

            layui.use('table', function () {
                var table = layui.table;
                //监听单元格事件
                table.on('tool(EDtable)', function (obj) {
                    var data = obj.data;
                    if (obj.event === 'getOrderDetails') {
                        layer.open({
                            type: 2,
                            title: data.order_number + ' 订单详情',
                            fix: false,
                            maxmin: true,
                            shadeClose: true,
                            offset:'r',
                            area: ['80%', '90%'],
                            content: '{{ url('order/originalOrderDetail') }}' + '?order_number=' + data.order_number,
                            end: function (index) {
                                layer.close(index);
                            }
                        });
                    }
                });
            });

            $(".create-order").click(function () {
                layer.open({
                    type: 2,
                    title: '添加原始订单',
                    fix: false,
                    maxmin: true,
                    shadeClose: true,
                    offset:'r',
                    area: ['80%', '90%'],
                    content: '{{ url('order/createOriginalOrder') }}',
                    yes: function(index, layero){
                    //点击确认触发 iframe 内容中的按钮提交
                    var submit = layero.find('iframe').contents().find("#LAY-front-submit");
                        submit.click();
                    },
                    end: function (index) {
                        layer.close(index);
                    }
                });
            });

            form.on('select(platform_select)', function (data) {
                var shopSelection = $("#shop_unselected");

                $.ajax({
                    type: "GET",
                    url: "getPlatformShop",
                    data: {"platform" : data.value},
                    success: function (e) {
                        if (e.code != 0) {
                            layer.msg(e.msg)
                            return false;
                        }
                        shopSelection.empty();
                        let shopOption = "<option value = ''>请选择店铺</option>";
                        $.each(e.data, function (k, v) {
                            shopOption += '<option value="'+ v.id +'">'+ v.shop_name +'</option>'
                        })
                        shopSelection.append(shopOption);
                        form.render('select');
                        return false;
                    },
                    error: function (e) {
                        layer.msg("获取店铺时出现异常，请重试", {icon:16});
                    }
                });
                return false;
            });

            $(document).on('click', '#upload-btn', function (e) {
                $("#excel-import").click();
            });

            $(document).on('change', '#excel-import', function (e) {
                let filename = $(this).val().split('\\');
                let cutLength = 20;
                filename = filename[filename.length - 1];

                if (filename.length > 13) {
                    filename = filename.slice(0, cutLength) + '...';
                }
                $("#input-file-name").text(filename)
            })

        });


        $("body").bind("keydown", function (event) {
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location = location;
            }
        })


    </script>
@endsection