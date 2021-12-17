@extends('layouts/new_main')
@section('head')
    <style>
        .laytable-cell-7-currency_form_code {

            width: 212px;
        }
        .layui-layer-btn {
            text-align: center!important;
        }
    </style>
@endsection
@include('common/validate')
@section('content')
{{--    @include('layouts/shortcutMenus')--}}
    <div class="content-wrapper">
        <form class="multiSearch layui-form">
                <div class="frist">
                    <div class="inputTxt">货币名称：</div>
                    <div class="inputBlock">
                        <div class="multLable">
                            <input type="text" name="currency_form_name"  placeholder="货币名称" autocomplete="off" style="width:438px;height:38px;padding-left:10px;">
                        </div>
                    </div>
                </div>
                <!--启用仓库-->
                <div class="second" style="margin-top: 15px">
                    <div class="inputBlock">
                        <div class="multLable">
                            <button class="layui-btn layui-btn-danger" lay-submit="" lay-filter="searBtn">搜索</button>
                            <button class="layui-btn layuiadmin-btn-rules CurrencyExchange" >更新官方汇率</button>
                            <button class="layui-btn layuiadmin-btn-rules CurrencyExchangeMain" >批量更新官方汇率至我的汇率</button>
                            <button class="layui-btn layuiadmin-btn-rules WMGnew" >添加</button>
                            <button class="layui-btn layui-btn-primary" lay-submit="" lay-filter="reset" >重置</button>
                        </div>
                    </div>

                    <button class="layui-btn probRulesBtn" style="float: right;" type="button" lay-submit="" lay-filter="export">下载历史汇率</button>
                </div>
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
                elem: '#EDdate'
            });
            laydate.render({
                elem: '#EDdate1'
            });

            form.on('submit(reset)', function(data){
                window.location.reload(true);
                return false;
            });

            var active = {
                addsm: function () {
                    layer.open({
                        type: 2
                        ,title: '仓库授权'
                        ,content: ''
                        ,area: ['500px', '400px']
                        ,btn: ['确定', '取消']
                        ,maxmin: true
                        ,yes: function(index, layero){
                            //点击确认触发 iframe 内容中的按钮提交
                            var submit = layero.find('iframe').contents().find("#LAY-front-submit");
                            submit.click();
                        }
                    });
                },
            };

            $('.layuiadmin-btn-rules').on('click', function(e){
                e.preventDefault();
                var type = $(this).data('type');
                active[type] ? active[type].call(this) : '';
            });


            form.on('submit(searBtn)', function(data){
                var index = layer.msg('数据请求中', {icon: 16});
                var info = data.field;
                table.render({
                    elem: '#EDtable'
                    ,url:'/settingExchange/ajaxGetExchangeData'
                    ,where:{data:info}
                    ,cols: [[
                        {field: '', title: '序号', width:50, type:'numbers'},
                        {field:'currency_form_code', title:'货币Code',event: 'getOrderDetails', style:'cursor: pointer;',templet: function(d){
                                return d.currency_form_code;
                            }}
                        ,{field:'currency_form_code', title:'英文名称',width:212, templet: function (d) {
                                return d.currency_form_code;
                            }}
                        ,{field:'currency_form_name', width:212, title:'货币名称', templet: function (d) {
                                return d.currency_form_name;
                            }}
                        ,{field:'ident_fier', width:212, title:'标示符', templet: function (d) {
                                return d.ident_fier;
                            }}
                        ,{field:'exchange_rate',width:212, title:'官方汇率'}
                        ,{field:'maintain',width:212, title:'我的汇率',templet: function (d) {
                                if(!d.maintain || !d.maintain.exchange_rate){
                                    return '';
                                }
                                return d.maintain.exchange_rate;
                            }}
                        ,{field:'updated_at',width:562, title:'更新时间',templet: function (d) {
                                if(d.maintain && d.maintain.updated_at){
                                    return d.maintain.updated_at ;
                                }
                                return '';
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
                switch (obj.event) {
                    case'check':
                        layer.open({
                            type:2,
                            title: ' 店铺详情',
                            fix: false,
                            maxmin: false,
                            shadeClose: true,
                            area: ['850px', '500px'],
                            content: '{{ url('shopManage/checkDefinedShop') }}' + '?id=' + id,
                            end: function(index){
                                layer.close(index);
                            }
                        });
                        break;
                    case'edit':
                        layer.open({
                            title:"编辑",
                            type: 2,
                            area:["700px","600px"],
                            content: '/settingExchange/exchangeAdd/?id='+id,
                            btn:['确认','取消'],
                            btn1: function(index, layero){
                                var info = window["layui-layer-iframe" + index].callbackdata();
                                $.ajax({
                                    url:'/shopManage/addDefinedShop',
                                    type:'post',
                                    data: {
                                        '_token':"{{csrf_token()}}" ,
                                        'shop_name': info.shop_name ,
                                        'plat_id' : info.source_plat,
                                        'id' : id
                                    },
                                    dataType:'json',
                                    success:function(data){
                                        if(data.code == 200){
                                            layer.msg(data.msg);
                                            layer.close(index);
                                            table.reload('EDtable');
                                        }else {
                                            layer.msg(data.msg);
                                            layer.close(index);
                                            table.reload('EDtable');
                                        }
                                    },
                                });

                            }
                        });
                        break;
                    case'del':
                        layer.confirm("确定删除？", function (e) {
                            $.ajax({
                                type: 'delete',
                                url: '{{ url('shopManage/deleteDefinedShop') }}',
                                data: {id: _data.id},
                                dataType: 'json',
                                success: function (data) {
                                    if (data.code == 200) {
                                        layer.msg(data.msg);
                                        table.reload('EDtable');
                                    } else {
                                        layer.msg(data.msg);
                                    }
                                }
                            });
                        });
                        break;
                    case 'auth':
                        window.location.href='https://www.amazon.co.jp';
                        break;
                    case 'authLotle':
                        window.location.href='https://www.rakuten.co.jp';
                }

            });

            table.render({
                elem: '#EDtable'
                ,url: '/settingExchange/ajaxGetExchangeData'
                ,cols: [[
                    {field: '', title: '序号', width:50, type:'numbers'},
                    {field:'currency_form_code', title:'货币Code',event: 'getOrderDetails', style:'cursor: pointer;',templet: function(d){
                            return d.currency_form_code;
                        }}
                    ,{field:'currency_form_code', title:'英文名称',width:212, templet: function (d) {
                        return d.currency_form_code;
                        }}
                    ,{field:'currency_form_name', width:212, title:'货币名称', templet: function (d) {
                        return d.currency_form_name;
                        }}
                    ,{field:'ident_fier', width:212, title:'标示符', templet: function (d) {
                        return d.ident_fier;
                        }}
                    ,{field:'exchange_rate',width:212, title:'官方汇率'}
                    ,{field:'maintain',width:212, title:'我的汇率',templet: function (d) {
                        if(!d.maintain || !d.maintain.exchange_rate){
                            return '';
                        }
                        return d.maintain.exchange_rate;
                        }}
                    ,{field:'updated_at',width:562, title:'更新时间',templet: function (d) {
                        if(d.maintain && d.maintain.updated_at){
                            return d.maintain.updated_at ;
                        }
                        return '';
                        }}
                ]]
                ,limit:20
                ,page: true
                ,limits:[20,30,40,50]
                ,done:function () {   //返回数据执行回调函数
                    layer.close(index);    //返回数据关闭loading
                }
            });

            $(document).on('click','.WMGnew',function(){
                layer.open({
                    title:"添加汇率",
                    type: 2,
                    area:["38%","70%"],
                    content: '/settingExchange/exchangeAdd',
                    btn:['确定','取消'],
                    btn1: function(index, layero){
                        var info = window["layui-layer-iframe" + index].callbackdata();
                        $.ajax({
                            url:'/settingExchange/exchangeAdd',
                            type:'post',
                            data: {
                                '_token':"{{csrf_token()}}" ,
                                'exchange_name': info.exchange_name
                            },
                            dataType:'json',
                            success:function(data){
                                if(data.code == 200){
                                    layer.msg(data.msg);
                                    layer.close(index);
                                    table.reload('EDtable');
                                }else {
                                    layer.msg(data.msg);
                                    layer.close(index);
                                    table.reload('EDtable');
                                }
                            },
                        });
                    },
                    btn2:function(){}
                });
            });

            $(document).on('click','.CurrencyExchange',function(){
                $.ajax({
                    url:'/settingExchange/collection',
                    type:'get',
                    data: {
                        '_token':"{{csrf_token()}}",
                        'urls':'http://www.boc.cn/sourcedb/whpj/',
                    },
                    dataType:'json',
                    success:function(data){
                        if(data.code == 200){
                            // window.location.href='https://www.amazon.co.jp/ap/signin?_encoding=UTF8&ignoreAuthState=1&openid.assoc_handle=jpflex&openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.mode=checkid_setup&openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0&openid.ns.pape=http%3A%2F%2Fspecs.openid.net%2Fextensions%2Fpape%2F1.0&openid.pape.max_auth_age=0&openid.return_to=https%3A%2F%2Fwww.amazon.co.jp%2F%3Fref_%3Dnav_signin&switch_account=';
                            layer.msg(data.msg);
                            layer.close(index);
                            table.reload('EDtable');
                        }else {
                            layer.msg(data.msg);
                            layer.close(index);
                            table.reload('EDtable');
                        }
                    },
                });
            });

            //CurrencyExchangeMain
            $(document).on('click','.CurrencyExchangeMain',function(){
                $.ajax({
                    url:'/settingExchange/addSettingCurrencyExchangeMain',
                    type:'get',
                    data: {
                        '_token':"{{csrf_token()}}",
                        // 'urls':'http://www.boc.cn/sourcedb/whpj/',
                    },
                    dataType:'json',
                    success:function(data){
                        if(data.status){
                            // window.location.href='https://www.amazon.co.jp/ap/signin?_encoding=UTF8&ignoreAuthState=1&openid.assoc_handle=jpflex&openid.claimed_id=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.identity=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0%2Fidentifier_select&openid.mode=checkid_setup&openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0&openid.ns.pape=http%3A%2F%2Fspecs.openid.net%2Fextensions%2Fpape%2F1.0&openid.pape.max_auth_age=0&openid.return_to=https%3A%2F%2Fwww.amazon.co.jp%2F%3Fref_%3Dnav_signin&switch_account=';
                            layer.msg(data.msg);
                            layer.close(index);
                            table.reload('EDtable');
                        }else {
                            layer.msg(data.msg);
                            layer.close(index);
                            table.reload('EDtable');
                        }
                    },
                });
            });

            form.on('submit(export)', function(data){
                location.href = "{{url('settingExchange/exportCurrencyHistory')}}"+'?currency_form_name='+ data.field.currency_form_name;
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