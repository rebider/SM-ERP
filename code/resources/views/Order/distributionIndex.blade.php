@extends('layouts.new_main')

@section('content')
{{--    @include('layouts/shortcutMenus')--}}
    <div class="content-wrapper">

        <form class="multiSearch layui-form">
            <div class="flexSearch flexquar fclear">
                <!--问题类型-->
                <div class="frist">
                    <div class="inputTxt">同步状态：</div>
                    <div class="inputBlock">
                        <div class="multLable">
                            <input type="radio" name="sync_status" value="0" title="全部" checked>
                            <input type="radio" name="sync_status" value="1" title="未同步">
                            <input type="radio" name="sync_status" value="2" title="已同步">
                        </div>
                    </div>
                </div>
                <div class="frist">
                    <div class="inputTxt">是否作废：</div>
                    <div class="inputBlock">
                        <div class="multLable">
                            <input type="radio" name="invoices_status" value="0" title="全部" checked>
                            <input type="radio" name="invoices_status" value="1" title="否">
                            <input type="radio" name="invoices_status" value="2" title="已作废">
                        </div>
                    </div>
                </div>

                <div class="second">
                    <!--规则名称-->
                    <div>
                        <div class="inputTxt"> 来源平台：</div>
                        <div class="inputBlock">
                            <select name="platforms_id" id="platforms" lay-filter="platforms">
                                <option value="">请选择</option>
                                @if(isset($platforms))
                                    @foreach($platforms as $item_pt)
                                        <option value="{{$item_pt['id']}}">{{$item_pt['name_EN']}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="inputTxt">来源店铺：</div>
                        <div class="inputBlock">
                            <select name="source_shop" id="sourceShop" lay-filter="sourceShop">
                                <option value="">请选择</option>
                                @if(isset($shops))
                                    @foreach($shops as $item_sd)
                                        <option value="{{$item_sd['id']}}">{{$item_sd['shop_name']}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <!--搜索时间类型-->
                    <div>
                        <div class="inputBlock">
                            <div class="inputTxt">下单时间:</div>
                            <div class="inputBlock">
                                <div class="layui-input-inline">
                                    <input type="text" name="place_an_order_start_time" id="EDdate" placeholder="起始时间"
                                           autocomplete="off" class="layui-input writeinput" readonly="">
                                </div>
                                <div class="layui-input-inline">
                                    <input type="text" name="place_an_order_end_time" id="EDdate1" placeholder="截止时间"
                                           autocomplete="off" class="layui-input writeinput" readonly="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="second">
                    <div class="inputTxt">配货单号：</div>
                    <div class="inputBlock">
                        <input type="text" name="invoices_number" placeholder="" autocomplete="off" class="voin">
                    </div>
                    <div class="inputTxt">订单单号：</div>
                    <div class="inputBlock">
                        <input type="text" name="order_number" placeholder="" autocomplete="off" class="voin">
                    </div>
                    <div class="inputTxt">电商单号：</div>
                    <div class="inputBlock">
                        <input type="text" name="plat_order_number" placeholder="" autocomplete="off" class="voin">
                    </div>
                </div>
                <div class="search">
                    <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="searBtn">搜索</button>
                    <button class="layui-btn layuiadmin-btn-order" data-type="uploadBtn">导入跟踪号</button>
                    <button class="layui-btn layuiadmin-btn-order fr" data-type="outputBtn">导出配货单</button>
                    <button class="layui-btn layui-btn-primary" lay-submit="" lay-filter="reset" >重置</button>
                </div>
            </div>
        </form>
        <div class="edn-row table_index">
            <table class="" id="EDtable" lay-filter="EDtable"></table>
            <script type="text/html" id="table-warehouse-edit">
                <a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="edit"><i
                            class="layui-icon layui-icon-edit"></i>配货</a>
            </script>
        </div>
    </div>



    {{--导入订单弹窗--}}
    <div id="uploadPop" class="hide">
        <div class="container">
            <div>XLS文件：</div>
            <button type="button" class="layui-btn layui-btn-sm layui-btn-normal" id="selectFile">选择文件</button>
            <button type="button" class="layui-btn layui-btn-sm" id="uploadFile">开始上传</button>
            <div>
                <a href="/file/跟踪号导入模板.xlsx" download="物流跟踪号模板.xlsx"
                   style="color:#1D8DDE;">《导入模板》</a>
            </div>
        </div>
    </div>
@endsection

@section('javascripts')
    <script>
        //layui加载
        layui.config({base: '../../layui/lay/modules/'}).extend({formSelects: 'formSelects-v3'});
        layui.use(['layer', 'form', 'element', 'laydate', 'upload', 'table', 'formSelects'], function () {
            var layer = layui.layer, form = layui.form, upload = layui.upload, laydate = layui.laydate,
                table = layui.table,
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
            $(document).on('click','#uploadFile',function(){
                if($('.layui-upload-choose').text()==''){
                    layer.alert('请选择需要上传的模版附件！');
                    return false;
                }
            });

            form.on('submit(reset)', function(data){
                window.location.reload(true);
                return false;
            });

            //选完文件后不自动上传
            upload.render({
                elem: '#selectFile'
                , url: '{{ route('order.distribution.import') }}'
                , auto: false
                , exts: 'xlsx|xls'
                , shade: 0.8
                , bindAction: '#uploadFile'
                , data: {'_token': $('meta[name="csrf-token"]').attr('content')}
                , before: function (obj) { //obj参数包含的信息，跟 choose回调完全一致，可参见上文。
                    layer.load(); //上传loading
                }
                , done: function (res) {
                    layer.closeAll('loading');
                    if (res.code == 1) {
                        layer.alert(res.msg);
                    } else {
                        layer.alert(res.err, {end: function () {
                                location.reload();
                            }});
                    }
                    layer.close(_index);
                }
            });

            $(document).on("click", ".btn-checked", function () {
                var source = $(this).parents(".checked"), _this = $(this);
                source.find("li").each(function (index, element) {
                    if (_this.find('input').is(':checked')) {
                        $(this).find('input').prop('checked', true);
                    } else {
                        $(this).find('input').prop('checked', false);
                    }
                });

                form.render();
            });


            $(document).on("click", ".checked li", function () {
                if ($(this).find('input').is(':checked')) {
                    $(this).parents(".checked").find('.btn-checked input').prop('checked', true);
                }
                form.render();
            });
            var _index, active = {
                uploadBtn: function () {
                    _index = layer.open({
                        type: 1
                        , offset: 'auto'
                        , title: '导入跟踪号'
                        , content: $('#uploadPop')
                        , area: ['600px', '180px']
                    });

                },
                outputBtn: function () {
                    layer.msg('加载中', {
                        icon: 16
                        , shade: 0.01
                    });
                    location.href = '{{ route('order.distribution.explode') }}';
                }
            };

            $('.layuiadmin-btn-order').on('click', function (e) {
                e.preventDefault();
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });


            table.on('tool(EDtable)', function (obj) {
                var _data = obj.data, _id = _data.tid;
                switch (obj.event) {
                    case'read':
                        layer.open({
                            type: 2
                            , title: _data.invoices_number+' 配货信息'
                            , content: '{{ route('order.read_goods_desc.index') }}' + '?id=' + _id
                            , area: ['80%', '90%']
                            , offset: 'r'
                            , maxmin: true
                        });
                        break;
                }

            });
            //店铺联动
            form.on('select(platforms)', function (data) {
                var platforms = data.value;
               loading = layer.msg('加载店铺中...', {
                    icon: 16
                    , shade: 0.01
                });
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
            form.on('submit(searBtn)', function (data) {

                var index = layer.msg('数据请求中', {icon: 16});
                var info = data.field;
                table.render({
                    elem: '#EDtable'
                    , url: '{{ route('order.distribution.lists') }}'
                    , where: {data: info}
                    , cols: [[
                        {field: '', title: '序号',width:90, type:'numbers'}
                        , {
                            field: 'invoices_number',
                            title: '配货单号',
                            style: 'cursor: pointer;',
                            width: 210,
                            templet: function (d) {
                                return '<a lay-event="read" href="javascript:void(0);" class="layui-table-link" lay-event="read">' + d.invoices_number + '</a>';
                            }
                        }
                        , {
                            field: 'order_number', title: '订单号', templet: function (d) {
                                return d.orders.order_number;
                            }
                        }
                        , {
                            field: 'plat_order_number', title: '电商单号', templet: function (d) {
                                return d.orders.plat_order_number;
                            }
                        }
                        , {
                            field: 'warehouse', title: '仓库', templet: function (d) {
                                return d.warehouse;
                            }
                        }
                        , {
                            field: 'logistics_way', title: '物流方式', templet: function (d) {
                                return d.logistics_way;
                            }
                        }
                        , {
                            field: 'taotla_value', title: '运费', templet: function (d) {
                                return d.taotla_value;
                            }
                        }
                        , {
                            field: 'tracking_no',width:100, title: '物流跟踪号', templet: function (d) {
                                return d.tracking_no?d.tracking_no:'无';
                            }
                        }, {
                            field: 'delivery_status', title: '发货状态', templet: function (d) {
                                var str ='';
                               switch (d.delivery_status) {
                                   case 1:
                                       str = '未发货';
                                       break;
                                   case 2:
                                       str = '已发货';
                                       break;

                               }
                                return str;
                            }
                        }
                        , {
                            field: 'invoices_status', title: '是否作废', templet: function (d) {
                                return d.invoices_status == 1 ? '否' : '是';
                            }
                        }
                        , {
                            field: 'sync_status', title: '同步状态', templet: function (d) {
                                var str = '无';
                                switch (d.sync_status) {
                                    case 1:
                                        str = '未同步';
                                        break;
                                    case 2:
                                        str = '已同步';
                                        break;
                                    case 3:
                                        str = '同步失败';
                                        break;
                                }

                                return str;
                            }
                        }
                        , {
                            field: 'created_at', title: '时间',width:200,  templet: function (d) {
                                return  '<p style="font-size:12px;">创建时间：'+d.created_at+'</p>';
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
                , url: '{{ route('order.distribution.lists') }}'
                , cols: [[
                    {field: '', title: '序号',width:90, type:'numbers'}
                    , {
                        field: 'invoices_number',
                        title: '配货单号',
                        style: 'cursor: pointer;',
                        width: 210,
                        templet: function (d) {
                            return '<a lay-event="read" href="javascript:void(0);" class="layui-table-link" lay-event="read">' + d.invoices_number + '</a>';
                        }
                    }
                    , {
                        field: 'order_number', title: '订单号', templet: function (d) {
                            return d.orders.order_number;
                        }
                    }
                    , {
                        field: 'plat_order_number', title: '电商单号', templet: function (d) {
                            return d.orders.plat_order_number;
                        }
                    }
                    , {
                        field: 'warehouse', title: '仓库', templet: function (d) {
                            return d.warehouse;
                        }
                    }
                    , {
                        field: 'logistics_way', title: '物流方式', templet: function (d) {
                            return d.logistics_way;
                        }
                    }
                    , {
                        field: 'taotla_value', title: '运费', templet: function (d) {
                            return d.taotla_value;
                        }
                    }
                    , {
                        field: 'tracking_no', width:100, title: '物流跟踪号', templet: function (d) {
                            return d.tracking_no?d.tracking_no:'无';
                        }
                    }, {
                        field: 'delivery_status', title: '发货状态', templet: function (d) {
                            var str ='';
                            switch (d.delivery_status) {
                                case 1:
                                    str = '未发货';
                                    break;
                                case 2:
                                    str = '已发货';
                                    break;

                            }
                            return str;
                        }
                    }, {
                        field: 'invoices_status', title: '是否作废', templet: function (d) {
                            return d.invoices_status == 1 ? '否' : '是';
                        }
                    }
                    , {
                        field: 'sync_status', title: '同步状态', templet: function (d) {
                            var str = '无';
                            switch (d.sync_status) {
                                case 1:
                                    str = '未同步';
                                    break;
                                case 2:
                                    str = '已同步';
                                    break;
                                case 3:
                                    str = '同步失败';
                                    break;
                            }

                            return str;
                        }
                    }
                    , {
                        field: 'created_at', title: '时间', width:200, templet: function (d) {
                            return  '<p style="font-size:12px;">创建时间：'+d.created_at+'</p>';
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


        });

        $("body").bind("keydown", function (event) {
            if (event.keyCode == 116) {
                event.preventDefault(); //阻止默认刷新
                location = location;
            }
        })


    </script>
@endsection